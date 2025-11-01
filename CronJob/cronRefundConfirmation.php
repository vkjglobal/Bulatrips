<?php
/******************* enable error output for debugging 500s *******************/
error_reporting(E_ALL);
ini_set('display_errors', 1);
register_shutdown_function(function () {
	$e = error_get_last();
	if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
		echo "<pre>FATAL: ";
		echo htmlspecialchars(print_r($e, true));
		echo "</pre>";
	}
});
/*******************
Project Name	::> Bulatrips
   Module 		::> Refund Confirmation Monitoring Cron
   Programmer	::> AI Assistant
   Date			::> 18.10.2025
   
   DESCRIPTION::::>>>>
   Monitors final Refund completion (after user accepts RefundQuote)
   - Checks Refund PTRs with InProcess status
   - Updates database when completed
   - Sends completion email
   - Processes Windcave refunds with service fee deducted amount
   - Stuck PTR detection

********************/

include_once __DIR__ . '/../includes/class.SearchPtrCron.php';
include_once __DIR__ . '/../includes/class.Users.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';
include_once __DIR__ . '/../includes/windcave_refund_helper.php';

// Smart wrapper
if (!function_exists('send_mail_smart')) {
	function send_mail_smart($to, $subject, $html) {
		if (function_exists('sendMail')) {
			return sendMail($to, $subject, $html);
		}
		return false;
	}
}

$objBookCron = new SearchPtrCron();
$usersObj = new Users();

// Set timeouts
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 120);

//=================log write ======
$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','refundConfirmCron.txt');
$objBookCron->_writeLog('Refund Confirmation Cron Started','refundConfirmCron.txt');
//============ END log write ==========

// Email queues
$bookingEmailQueue = [];
$paymentRefundEmailQueue = [];

// Get Refund PTRs (after user accepted RefundQuote)
$resultBooking = $objBookCron->getLisQuery("SELECT * FROM cancel_booking 
          WHERE mf_ref_num != '' 
          AND ptr_type = 'Refund'
          AND ptr_status = 'InProcess'
          AND cancel_status = 0
          ORDER BY created_date ASC");

$objBookCron->_writeLog('Found ' . count($resultBooking) . ' Refund PTRs to check', 'refundConfirmCron.txt');

echo "<h3>Refund Confirmation Monitor</h3>";
echo "<p>Checking " . count($resultBooking) . " Refund PTR(s)</p>";
echo "<hr>";

foreach($resultBooking as $resultBookingdata){
	$bookingId = $resultBookingdata['booking_id'];
	$userId = $resultBookingdata['user_agent_id'];
	$ptr_id = $resultBookingdata['ptr_id'];
	$mfreNum = $resultBookingdata['mf_ref_num'];
	$totalRefundAmount = $resultBookingdata['total_refund_amount'];
	$ticketNum = $resultBookingdata['ticket_number'];
	$elapsedMinutes = round((time() - strtotime($resultBookingdata['created_date'])) / 60);
	
	echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0; border-left: 4px solid #4caf50;'>";
	echo "<strong>PTR ID:</strong> {$ptr_id} | ";
	echo "<strong>Booking:</strong> {$bookingId} | ";
	echo "<strong>Elapsed:</strong> {$elapsedMinutes} mins";
	echo "</div>";
	
	// Prepare API request
	$requestData = array(
		'ptrType' => 'Refund',
		'MFRef' => $mfreNum,
		'PTRId' => $ptr_id,
		'Page' => 1
	);
	
	// API call with retry
	$maxRetries = 3;
	$retryCount = 0;
	
	while ($retryCount < $maxRetries) {
		$result = $objBookCron->callApi('Search/PostTicketingRequest', $requestData);
		$httpCode = $result['httpCode'];
		$response = $result['responseData'];
		
		if ($httpCode == 200 && !empty($response)) {
			break;
		}
		
		$retryCount++;
		if ($retryCount < $maxRetries) {
			sleep(10);
		}
	}
	
	if (!$response) {
		$objBookCron->_writeLog("No response for Refund PTR $ptr_id", 'refundConfirmCron.txt');
		continue;
	}
	
	$responseData = json_decode($response, true);
	$objBookCron->_writeLog('Refund Search API Response: '.print_r($responseData, true), 'refundConfirmCron.txt');
	
	if(isset($responseData['Success']) && $responseData['Success']){
		if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
			$PTRDetail = $responseData['Data']['PTRDetail'][0];
			$PTRId = $PTRDetail['PTRId'];
			$PTRStatus = $PTRDetail['PTRStatus'];
			$Resolution = $PTRDetail['Resolution'] ?? '';
			$TotalRefundAmount = $PTRDetail['TotalRefundAmount'] ?? $totalRefundAmount;
			$Currency = $PTRDetail['Currency'] ?? 'USD';
			
			$objBookCron->_writeLog("Refund PTR $PTRId Status: $PTRStatus, Resolution: $Resolution", 'refundConfirmCron.txt');
			
			// Check if Refund completed
			if($PTRStatus == "Completed" && $Resolution == "Refunded"){
				echo "<div style='color: green;'><strong>✅ Refund Completed for PTR $PTRId</strong></div>";
				
				// Update database
				$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
				$objBookCron->updateInDB_trav('travellers_details', $ticketNum);
				
				$count_ticketed_temp = $objBookCron->count_ticketed__temp_book('travellers_details', $bookingId);
				if($count_ticketed_temp == 0){
					$objBookCron->updateInDB_temp_book('temp_booking', $mfreNum);
				}
				
				// Get customer details
				$contact = $objBookCron->getBookingContactEmail($bookingId);
				$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
				$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
				
				$userDetailsForCust = $usersObj->getUserDetails((int)$userId) ?: [];
				$userEmail = isset($userDetailsForCust['email']) ? trim($userDetailsForCust['email']) : '';
				$recipientEmail = $userEmail ?: $contactEmail;
				
				$paxNameRow = $objBookCron->getLisQuery("SELECT CONCAT(title,' ',first_name,' ',last_name) AS full_name FROM travellers_details WHERE e_ticket_number LIKE '%".$ticketNum."%' LIMIT 1");
				$passengerName = (!empty($paxNameRow) && isset($paxNameRow[0]['full_name'])) ? $paxNameRow[0]['full_name'] : 'Passenger';
				
				// Queue refund for Windcave (use amount from cancel_booking which has service fees deducted)
				if (!isset($paymentRefundEmailQueue[$bookingId])) {
					$paymentRefundEmailQueue[$bookingId] = [
						'email' => $recipientEmail,
						'mfRef' => $mfRefForEmail,
						'bookingId' => $bookingId,
						'totalRefundAmount' => 0,
						'refunds' => [],
						'processed' => false
					];
				}
				
				// Use the total_refund_amount from cancel_booking (already has service fees deducted)
				$paymentRefundEmailQueue[$bookingId]['totalRefundAmount'] += floatval($totalRefundAmount);
				$paymentRefundEmailQueue[$bookingId]['refunds'][] = [
					'passenger' => $passengerName,
					'amount' => number_format((float)$totalRefundAmount, 2),
					'ptr_id' => (string)$PTRId,
					'ticket' => $ticketNum
				];
				
				// Queue completion email
				if (!isset($bookingEmailQueue[$bookingId])) {
					$bookingEmailQueue[$bookingId] = [
						'email' => $recipientEmail,
						'mfRef' => $mfRefForEmail,
						'ptrType' => 'Refund',
						'currency' => $Currency,
						'finalAmount' => 0,
						'counted_ptrs' => [],
						'bookingId' => $bookingId,
						'passengers' => []
					];
				}
				
				if (!in_array($PTRId, $bookingEmailQueue[$bookingId]['counted_ptrs'], true)) {
					$bookingEmailQueue[$bookingId]['finalAmount'] += floatval($totalRefundAmount);
					$bookingEmailQueue[$bookingId]['counted_ptrs'][] = $PTRId;
				}
				
				$bookingEmailQueue[$bookingId]['passengers'][] = [
					'name' => $passengerName,
					'ptr_id' => (string)$PTRId,
					'ptr_status' => $PTRStatus,
					'eticket' => $ticketNum
				];
			} else {
				echo "<div style='color: orange;'>⏳ Refund PTR $PTRId still processing</div>";
			}
		}
	}
}

// Send completion emails
foreach ($bookingEmailQueue as $bId => $payload) {
	$email = $payload['email'];
	if (empty($email)) { continue; }
	
	$subject = 'Flight Refund - Completed';
	$mfRef = $payload['mfRef'];
	$currency = $payload['currency'] ?: 'USD';
	$finalAmount = number_format((float)$payload['finalAmount'], 2);
	
	$contactName = 'Customer';
	$contactRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE mf_reference = '".addslashes($mfRef)."' LIMIT 1");
	if (!empty($contactRow)) {
		$first = trim($contactRow[0]['contact_first_name'] ?? '');
		$last = trim($contactRow[0]['contact_last_name'] ?? '');
		$full = trim($first.' '.$last);
		if ($full !== '') { $contactName = $full; }
	}
	
	$rowsHtml = '';
	foreach ($payload['passengers'] as $p) {
		$rowsHtml .= '<tr>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['name']).'</td>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['ptr_id']).'</td>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">Completed</td>'
			.'</tr>';
	}
	
	$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>'
		.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;">'
		.'<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
		.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" style="height:50px;"></td></tr>'
		.'<tr><td align="center" style="background:#28a745;color:#fff;font-size:18px;font-weight:bold;padding:14px;">Refund Completed</td></tr>'
		.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333;">'
		.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
		.'<p style="margin:0 0 18px 0;">Your Refund request has been <strong>completed</strong> by the airline.</p>'
		.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
		.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> '.$mfRef.'</div>'
		.'<div style="margin:0;"><span style="font-weight:bold;">Total Amount:</span> '.$currency.' '.$finalAmount.'</div>'
		.'</div>'
		.'<div style="margin-top:16px;"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">'
		.'<thead><tr><th align="left" style="padding:8px 12px;border-bottom:2px solid #28a745;">Passenger</th><th align="left" style="padding:8px 12px;border-bottom:2px solid #28a745;">PTR ID</th><th align="left" style="padding:8px 12px;border-bottom:2px solid #28a745;">Status</th></tr></thead>'
		.'<tbody>'.$rowsHtml.'</tbody></table></div>'
		.'<p style="margin:18px 0;">Your refund has been processed. Please allow 3-5 business days for the amount to reflect in your account.</p>'
		.'<p style="margin:12px 0 0 0;color:#555;">Thank you for choosing Bulatrips.</p>'
		.'</td></tr></table></body></html>';
	
	send_mail_smart($email, $subject, $emailHtml);
	$objBookCron->_writeLog('Refund completion email sent to '.$email, 'refundConfirmCron.txt');
}

// Process Windcave refunds
foreach ($paymentRefundEmailQueue as $bId => $refundPayload) {
	if ($refundPayload['processed']) { continue; }
	
	$totalRefund = $refundPayload['totalRefundAmount'];
	$mfRef = $refundPayload['mfRef'];
	
	try {
		$objBookCron->_writeLog('Processing Windcave refund for booking '.$bId.' amount=USD '.$totalRefund, 'refundConfirmCron.txt');
		$res = windcaveRefundBooking($bId, $mfRef, floatval($totalRefund), 'Refund Processing', '', '', false);
		$objBookCron->_writeLog('Windcave result: '.json_encode($res), 'refundConfirmCron.txt');
		
		echo "<div style='color: green;'>💳 Windcave refund processed: $".number_format($totalRefund, 2)."</div>";
		$paymentRefundEmailQueue[$bId]['processed'] = true;
	} catch (\Exception $e) {
		$objBookCron->_writeLog('Windcave refund failed: '.$e->getMessage(), 'refundConfirmCron.txt');
		echo "<div style='color: red;'>❌ Windcave refund failed</div>";
	}
}

// Check for overdue Refund PTRs
$overduePTRs = $objBookCron->getLisQuery("SELECT *, 
                 TIMESTAMPDIFF(MINUTE, created_date, NOW()) as elapsed_minutes,
                 sla_minutes
                 FROM cancel_booking 
                 WHERE ptr_type = 'Refund'
                 AND ptr_status = 'InProcess' 
                 AND TIMESTAMPDIFF(MINUTE, created_date, NOW()) > (sla_minutes + 15)
                 ORDER BY created_date ASC");

if (!empty($overduePTRs)) {
	echo "<div style='background: #ffe6e6; padding: 10px; margin: 10px 0;'>";
	echo "<h3 style='color: red;'>⚠️ OVERDUE REFUND PTRs</h3>";
	
	foreach ($overduePTRs as $overdue) {
		$delayMinutes = $overdue['elapsed_minutes'] - $overdue['sla_minutes'];
		
		// Customer notification (SLA + 45 mins)
		if ($delayMinutes > 45 && $delayMinutes <= 60) {
			$objBookCron->sendCustomerDelayNotification($overdue);
		}
		
		// Admin alert (SLA + 60 mins)
		if ($delayMinutes > 60) {
			$objBookCron->sendStuckPTRAlert($overdue);
			echo "<div style='color: red;'>🚨 Admin alert sent for PTR {$overdue['ptr_id']}</div>";
		}
	}
	echo "</div>";
}

$objBookCron->_writeLog('Refund Confirmation Cron completed','refundConfirmCron.txt');
echo "<hr><p>✅ Refund Confirmation Cron completed</p>";
?>

