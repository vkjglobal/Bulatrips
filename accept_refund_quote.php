<?php
error_reporting(E_ALL);
// Do not echo notices/warnings in JSON responses
ini_set('display_errors', 0);
// Ensure we return JSON and avoid stray output
if (!headers_sent()) {
    header('Content-Type: application/json');
}
ob_start();
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('mail_send.php');

$objCancel = new Cancel();

// Support both JSON AJAX and form POST
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$data = null;
if ($isAjax) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
}

// Initialize inputs
$bookingId = '';
$userId = '';
$mfreNum = '';
$ptrId = '';
$acceptQuote = 'yes';
$preferenceOption = 1;
$passengerDetails = [];

if (is_array($data) && isset($data['booking_id'])) {
    // From AJAX JSON
    $bookingId = trim($data['booking_id']);
    $ptrId = isset($data['ptr_id']) ? trim($data['ptr_id']) : '';
    if (isset($data['passengerDetails']) && is_array($data['passengerDetails'])) {
        $passengerDetails = $data['passengerDetails'];
    }
    // Derive mf reference and user id from DB
    $bookingDetails = $objCancel->get_booking_details((int)$bookingId);
    if ($bookingDetails) {
        $mfreNum = $bookingDetails['mf_reference'];
        $userId = $bookingDetails['user_id'];
    }
    $acceptQuote = 'yes';
} else if (isset($_POST['mfreNum']) && isset($_POST['ptrId']) && isset($_POST['acceptQuote'])) {
    // Backward-compatible form POST
    $mfreNum = trim($_POST['mfreNum']);
    $ptrId = trim($_POST['ptrId']);
    $acceptQuote = trim($_POST['acceptQuote']);
    $bookingId = isset($_POST['bookingId']) ? trim($_POST['bookingId']) : '';
    $userId = isset($_POST['userId']) ? trim($_POST['userId']) : '';
    $preferenceOption = isset($_POST['preferenceOption']) ? (int)$_POST['preferenceOption'] : 1;
} else {
    $out = ['status' => 'error', 'message' => 'Missing required parameters'];
    // Clean any previous output before sending JSON
    @ob_clean();
    echo json_encode($out);
    exit;
}

// Sanitize inputs
$mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
$acceptQuote = strtolower($acceptQuote);

if (!in_array($acceptQuote, ['yes', 'no'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid acceptance value'
    ]);
    exit;
}

// Build passengers array per Mystifly docs
$passengersArray = [];
$hasChildPassenger = false;
if (!empty($passengerDetails) && is_array($passengerDetails)) {
    foreach ($passengerDetails as $p) {
        $passengersArray[] = [
            'firstName' => $p['firstname'] ?? '',
            'lastName' => $p['lastname'] ?? '',
            'title' => $p['title'] ?? '',
            'eTicket' => $p['eticket'] ?? '',
            'passengerType' => $p['passengertype'] ?? ''
        ];
        $pt = strtoupper(trim($p['passengertype'] ?? ''));
        if ($pt === 'CHD' || $pt === 'INF') { $hasChildPassenger = true; }
    }
}

// Extract numeric portion of PTR ID for live API
$numericPtrId = 0;
if (is_numeric($ptrId)) {
    $numericPtrId = (int)$ptrId;
} elseif (preg_match('/(\d+)/', (string)$ptrId, $m)) {
    $numericPtrId = (int)$m[1];
}

// Prepare API request data (Refund acceptance)
$requestData = array(
    'ptrType' => 'Refund',
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => $hasChildPassenger,
    'passengers' => $passengersArray,
    'PtrId' => $numericPtrId,
    'PreferenceOption' => $preferenceOption,
    'AcceptQuote' => $acceptQuote,
    'AdditionalNote' => 'Cancel and refund earliest'
);

try {
    $endpoint = 'PostTicketingRequest';

    if (defined('MOCK_MODE') && MOCK_MODE) {
        // Mock accept refund response
        $mock = [
            'Success' => true,
            'Data' => [
                'PTRId' => $ptrId ?: ('PTR_' . time() . '_' . rand(1000,9999)),
                'PTRType' => 'Refund',
                'PTRStatus' => 'InProcess',
                'SLAInMinutes' => 120,
                'Message' => 'Refund request accepted and is being processed'
            ],
            'Message' => 'Refund request accepted and is being processed'
        ];
        $httpCode = 200;
        $response = json_encode($mock);
    } else {
        $result = $objCancel->callApi($endpoint, $requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
    }

    // Log the request and response
    $logRes = print_r($response, true);
    $logReq = print_r($requestData, true);
    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------', 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('userId is '.$userId, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('Booking ID is '.$bookingId, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REquest Received\n'.$logReq, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REsponse Received\n'.$logRes, 'acceptRefundQuote.txt');

    if ($response) {
        $responseData = json_decode($response, true);
    } else {
        throw new Exception('Empty response from API');
    }

    $precancelsts = 'post';
    $message = "";
    
    if (isset($responseData['Success']) && $responseData['Success']) {
        // New PTR inserted as InProcess; mark cancel_status=0 so cron picks it and flips to completed later
        $cancel_status = 0;
        $PTRId = $responseData['Data']['PTRId'];
        // Normalize PTR type so cron can pick it up
        $PTRType = 'Refund';
        $PTRStatus = $responseData['Data']['PTRStatus'];
        $SLAInMinutes = isset($responseData['Data']['SLAInMinutes']) ? $responseData['Data']['SLAInMinutes'] : 0;
        
        $objCancel->_writeLog('Accept RefundQuote Success: '.$PTRStatus, 'acceptRefundQuote.txt');
        
        // Update database with acceptance status
        $finalRefundFromQuote = isset($data['final_refund_amount']) ? floatval($data['final_refund_amount']) : 0.0;
        $currencyFromQuote = isset($data['currency_from_quote']) && $data['currency_from_quote'] !== '' ? $data['currency_from_quote'] : 'USD';
        if (!empty($passengerDetails)) {
            $numSel = max(1, count($passengerDetails));
            $perPaxAmount = $finalRefundFromQuote > 0 ? ($finalRefundFromQuote / $numSel) : 0.0;
            foreach ($passengerDetails as $pax) {
                $ticketNum = $pax['eticket'] ?? '';
                $travId = 0;
                if ($ticketNum !== '') {
                    $row = $objCancel->getLisQuery("SELECT id FROM travellers_details WHERE flight_booking_id = ".intval($bookingId)." AND e_ticket_number = '".addslashes($ticketNum)."' LIMIT 1");
                    if (!empty($row) && isset($row[0]['id'])) { $travId = intval($row[0]['id']); }
                }
                $objCancel->insCncelSts(
                    $bookingId, $userId, $precancelsts, $errorCode = '', $mfreNum,
                    $traceId = '', $httpCode, $PTRId, $PTRType, $SLAInMinutes,
                    $PTRStatus, $VoidingWindow = '', $ticketNum,
                    $AdminCharges = '', $GSTCharge = '', $TotalVoidingFee = '',
                    $TotalRefundAmount = $perPaxAmount, $Currency = $currencyFromQuote, $cancel_status,
                    'RefundQuote accepted by user', $travId
                );
                // Update travellers_details with cancel_type and ptr_id string
                if (!empty($ticketNum)) {
                    $cond = "`e_ticket_number` = '".addslashes($ticketNum)."' AND `flight_booking_id` = ".intval($bookingId);
                    $objCancel->update('travellers_details', array(
                        'cancel_type' => 'refund',
                        'ptr_id' => (string)$PTRId
                    ), $cond);
                }
            }
        } else {
            $objCancel->insCncelSts(
                $bookingId, $userId, $precancelsts, $errorCode = '', $mfreNum,
                $traceId = '', $httpCode, $PTRId, $PTRType, $SLAInMinutes,
                $PTRStatus, $VoidingWindow = '', $ticket_num = '',
                $AdminCharges = '', $GSTCharge = '', $TotalVoidingFee = '',
                $TotalRefundAmount = $finalRefundFromQuote, $Currency = $currencyFromQuote, $cancel_status,
                'RefundQuote accepted by user'
            );
        }

        // Mirror void flow: mark selected passengers as InProcess with PTR on travellers_details
        try {
            if (!empty($passengerDetails)) {
                foreach ($passengerDetails as $pax) {
                    if (!empty($pax['eticket'])) {
                        $condition = "`e_ticket_number` = '".addslashes($pax['eticket'])."' AND `flight_booking_id` = ".intval($bookingId);
                        $objCancel->update('travellers_details', array(
                            'void_status' => 'InProcess'
                            // Do not set ptr_id here for refund InProcess to keep UI clean
                        ), $condition);
                    }
                }
            }
        } catch (Exception $e) {
            $objCancel->_writeLog('Error updating travellers_details for refund InProcess: '.$e->getMessage(), 'acceptRefundQuote.txt');
        }

        if ($acceptQuote === 'yes') {
            $message = "Refund request has been accepted and is being processed. PTR ID: " . $PTRId;
            
            // Send immediate "in process" email for refund acceptance
            try {
                $rows = $objCancel->getLisQuery("SELECT contact_email, mf_reference, contact_first_name, contact_last_name FROM temp_booking WHERE id = " . intval($bookingId) . " LIMIT 1");
                $contact = is_array($rows) && isset($rows[0]) ? $rows[0] : [];
                $recipient = $contact['contact_email'] ?? '';
                $mfRef = $contact['mf_reference'] ?? $mfreNum;
                $contactName = 'Customer';
                if (!empty($contact['contact_first_name']) || !empty($contact['contact_last_name'])) {
                    $fn = trim($contact['contact_first_name'] ?? '');
                    $ln = trim($contact['contact_last_name'] ?? '');
                    $full = trim($fn.' '.$ln);
                    if ($full !== '') { $contactName = $full; }
                }
                
                if (!empty($recipient)) {
                    $subject = 'Flight Refund - In Process';
                    $headerBar = 'Refund Update';
                    $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
                        .'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
                        .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
                        .'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
                        .'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">'.$headerBar.'</td></tr>'
                        .'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
                        .'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
                        .'<p style="margin:0 0 18px 0;">Your Refund request has been <strong>'.htmlspecialchars($PTRStatus).'</strong>.</p>'
                        .'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
                        .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRef).'</span></div>'
                        .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($PTRId).'</span></div>'
                        .'<div style="margin:0;"><span style="font-weight:bold;">Status:</span> <span>'.htmlspecialchars($PTRStatus).'</span></div>'
                        .'</div>'
                        .'<p style="margin:18px 0 0 0;color:#555555;">We will notify you by email once the airline completes processing.</p>'
                        .'</td></tr></table></body></html>';
                    sendMail($recipient, $subject, $emailHtml);
                }
            } catch (Exception $e) {
                $objCancel->_writeLog('Refund in-process email failed: '.$e->getMessage(), 'acceptRefundQuote.txt');
            }
        } else {
            $message = "Refund request has been declined successfully.";
        }

        $response_New = array(
            'status' => 'success',
            'message' => $message,
            'ptr_id' => $PTRId,
            'ptr_status' => $PTRStatus,
            'ptr_type' => $PTRType
        );

    } else {
        // Handle API errors
        $cancel_status = 0;
        $errorMessage = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error occurred';
        
        if (isset($responseData['Errors']) && is_array($responseData['Errors'])) {
            $errorMessage = implode(', ', $responseData['Errors']);
        }

        $objCancel->_writeLog('Accept RefundQuote Error: '.$errorMessage, 'acceptRefundQuote.txt');
        
        $bookCanIns = $objCancel->insCncelSts(
            $bookingId, $userId, $precancelsts, $errorCode = '', $mfreNum, 
            $traceId = '', $httpCode, $PTRId = '', $PTRType = 'AcceptRefundQuote', 
            $SLAInMinutes = '', $PTRStatus = '', $VoidingWindow = '', 
            $ticket_num = '', $AdminCharges = '', $GSTCharge = '', 
            $TotalVoidingFee = '', $TotalRefundAmount = '', $Currency = '', 
            $cancel_status, $errorMessage
        );

        $response_New = array(
            'status' => 'error',
            'message' => $errorMessage
        );
    }

} catch (Exception $e) {
    $objCancel->_writeLog('Accept RefundQuote Exception: '.$e->getMessage(), 'acceptRefundQuote.txt');
    
    $response_New = array(
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    );
}

$objCancel->_writeLog('Accept RefundQuote Response: '.json_encode($response_New), 'acceptRefundQuote.txt');
// Clean buffer and output pure JSON
@ob_clean();
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode($response_New);
exit;
?> 