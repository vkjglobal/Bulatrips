<?php
// Windcave Refund Helper
// Issues a refund for a booking using Windcave REST API

if (!function_exists('windcaveRefundBooking')) {
    /**
     * Issue a refund via Windcave.
     * @param int $bookingId
     * @param string $mfReference
     * @param float|null $overrideAmount If provided, refund this amount instead of payment_user.amount
     */
    function windcaveRefundBooking($bookingId, $mfReference, $overrideAmount = null, $passengerName = '', $eTicket = '', $ptrId = '', $skipRecord = false)
    {
        try {
            // Load dependencies
            include_once(__DIR__ . '/common_const.php');
            include_once(__DIR__ . '/dbConnect.php');

            global $conn;

            // Fetch payment row (trn_id is the transaction ID in this table)
            $stmt = $conn->prepare("SELECT id, booking_id, trn_id AS transaction_id, amount AS paid_amount, currency, IFNULL(refund_status,'') AS refund_status, IFNULL(refund_amount,0) AS refund_amount FROM payment_user WHERE booking_id = :bid ORDER BY id DESC LIMIT 1");
            $stmt->execute(['bid' => (int)$bookingId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] No payment row found for booking #$bookingId\n", FILE_APPEND);
                return ['skipped' => true, 'reason' => 'no_payment_row'];
            }

            // Skip if already refunded
            if (!empty($payment['refund_status']) && strtoupper($payment['refund_status']) === 'REFUNDED') {
                @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] Already refunded for booking #$bookingId\n", FILE_APPEND);
                return ['skipped' => true, 'reason' => 'already_refunded'];
            }

            $amount = ($overrideAmount !== null) ? floatval($overrideAmount) : floatval($payment['paid_amount'] ?? 0);
            $txnId  = trim($payment['transaction_id'] ?? '');

            if ($amount <= 0 || $txnId === '') {
                @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] Invalid payment data for booking #$bookingId\n", FILE_APPEND);
                return ['skipped' => true, 'reason' => 'invalid_payment_data'];
            }

            // Build request
            $url = rtrim(WC_URL, '/') . '/transactions';
            $data = [
                'type' => 'refund',
                'amount' => $amount,
                'transactionId' => $txnId
            ];
            $json = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode(WC_USERNAME . ':' . WC_PASSWORD)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "-------------Refund Start ".date('Y-m-d H:i:s')."-------------\n", FILE_APPEND);
            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "REQ: ".$json."\n", FILE_APPEND);
            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "HTTP: ".$httpCode." CURL: ".$curlErr."\n", FILE_APPEND);
            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "RES: ".$response."\n", FILE_APPEND);
            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "-------------Refund End  ".date('Y-m-d H:i:s')."-------------\n\n", FILE_APPEND);

            $resp = json_decode($response, true);

            $approved = false;
            // Windcave varies; check common fields
            if ($httpCode >= 200 && $httpCode < 300) {
                if (isset($resp['state']) && strtoupper($resp['state']) === 'APPROVED') { $approved = true; }
                if (isset($resp['responseText']) && strtoupper($resp['responseText']) === 'APPROVED') { $approved = true; }
            }

            // Always record the transaction regardless of approval status
            $refundTxn = $resp['id'] ?? ($resp['transactionId'] ?? 'N/A');
            $windcaveStatus = $approved ? 'APPROVED' : 'DECLINED';
            
            if ($approved) {
                // Update payment_user with refund info
                try {
                    $newRefundTotal = floatval($payment['refund_amount']) + $amount;
                    $finalStatus = ($newRefundTotal >= floatval($payment['paid_amount'])) ? 'REFUNDED' : 'REFUNDED_PARTIAL';
                    $upd = $conn->prepare("UPDATE payment_user SET refund_status = :st, refund_txn_id = :rtx, refund_amount = :amt WHERE id = :id");
                    $upd->execute(['st' => $finalStatus, 'rtx' => $refundTxn, 'amt' => $newRefundTotal, 'id' => (int)$payment['id']]);
                } catch (PDOException $e) {
                    @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] payment_user update failed: ".$e->getMessage()."\n", FILE_APPEND);
                }
            } else {
                // Update failed status
                try {
                    $upd = $conn->prepare("UPDATE payment_user SET refund_status = 'FAILED' WHERE id = :id");
                    $upd->execute(['id' => (int)$payment['id']]);
                } catch (PDOException $e) {
                    @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] payment_user failed update failed: ".$e->getMessage()."\n", FILE_APPEND);
                }
            }
            
            // ALWAYS record transaction (success or failure) if not skipped
            if (!$skipRecord) {
                try {
                    $refundRecord = $conn->prepare("INSERT INTO refund_transactions 
                        (booking_id, user_id, mf_reference, passenger_name, e_ticket_number, 
                         refund_amount, final_refund_amount, currency, windcave_refund_txn_id, 
                         windcave_status, windcave_response_json, refund_reason, processed_at, windcave_processed_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Void/Cancel completed', NOW(), NOW())");
                    $refundRecord->execute([
                        (int)$bookingId, 
                        $payment['user_id'] ?? null,
                        $mfReference,
                        $passengerName,
                        $eTicket,
                        $amount,
                        $amount,
                        $payment['currency'] ?? 'USD',
                        $refundTxn,
                        $windcaveStatus,
                        json_encode($resp, JSON_PRETTY_PRINT) // Store complete Windcave response
                    ]);
                    @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] Refund record saved: booking=$bookingId, amount=$amount, status=$windcaveStatus, txn=$refundTxn\n", FILE_APPEND);
                } catch (PDOException $e) {
                    @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] Refund record insert failed: ".$e->getMessage()."\n", FILE_APPEND);
                }
            }
            
            if ($approved) {
                return ['status' => 'success', 'refund_txn' => $refundTxn];
            }

            // Return failure details
            $reason = $resp['responseText'] ?? ($resp['message'] ?? 'unknown');
            return ['status' => 'failed', 'reason' => $reason, 'http' => $httpCode, 'refund_txn' => $refundTxn];

        } catch (Exception $e) {
            @file_put_contents('uploads/logFiles/WindcavePaymentResponse.txt', "[".date('Y-m-d H:i:s')."] Refund exception: ".$e->getMessage()."\n", FILE_APPEND);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

?>

