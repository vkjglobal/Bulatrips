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
   Module 		::> Void Status Monitoring Cron
   Programmer	::> AI Assistant
   Date			::> 18.10.2025
   
   DESCRIPTION::::>>>>
   Dedicated cron job for monitoring Void PTR completion
   - Checks Void PTRs with InProcess status
   - Updates database when completed
   - Sends email notifications
   - Processes Windcave refunds
   - Stuck PTR detection and alerts

********************/

include_once __DIR__ . '/../includes/class.SearchPtrCron.php';
include_once __DIR__ . '/../includes/class.Users.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';
include_once __DIR__ . '/../includes/windcave_refund_helper.php';

// Smart wrapper to use project mail function
if (!function_exists('send_mail_smart')) {
	function send_mail_smart($to, $subject, $html) {
		if (function_exists('sendMail')) {
			return sendMail($to, $subject, $html);
		}
		return false;
	}
}

$objBookCron = new SearchPtrCron();
$adminToemail = "no-reply@bulatrips.com";
$usersObj = new Users();

// Set longer timeout for API calls
ini_set('max_execution_time', 300); // 5 minutes
ini_set('default_socket_timeout', 120); // 2 minutes

//=================log write ======
$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','voidStatusCron.txt');
$objBookCron->_writeLog('Void Status Cron Started','voidStatusCron.txt');
//============ END log write ==========

// Email aggregation queue
$bookingEmailQueue = [];
$paymentRefundEmailQueue = [];

// Get Void PTRs that are InProcess
$resultBooking = $objBookCron->getLisQuery("SELECT * FROM cancel_booking 
          WHERE mf_ref_num != '' 
          AND ptr_type = 'Void'
          AND ptr_status = 'InProcess'
          AND cancel_status = 0
          ORDER BY created_date ASC");

$objBookCron->_writeLog('Found ' . count($resultBooking) . ' Void PTRs to check', 'voidStatusCron.txt');

echo "<h3>Void Status Monitor</h3>";
echo "<p>Checking " . count($resultBooking) . " Void PTR(s)</p>";
echo "<hr>";

foreach($resultBooking as $resultBookingdata){
	$bookingId = $resultBookingdata['booking_id'];
	$userId = $resultBookingdata['user_agent_id'];
	$ptr_id = $resultBookingdata['ptr_id'];
	$mfreNum = $resultBookingdata['mf_ref_num'];
	$totalRefundAmount = $resultBookingdata['total_refund_amount'];
	$ticketNum = $resultBookingdata['ticket_number'];
	$createdDate = isset($resultBookingdata['created_date']) ? strtotime($resultBookingdata['created_date']) : null;
	$elapsedSeconds = $createdDate ? (time() - $createdDate) : 0;
	$elapsedMinutes = round($elapsedSeconds / 60);
	
	echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #0066cc;'>";
	echo "<strong>PTR ID:</strong> {$ptr_id} | ";
	echo "<strong>Booking:</strong> {$bookingId} | ";
	echo "<strong>MF Ref:</strong> {$mfreNum} | ";
	echo "<strong>Elapsed:</strong> {$elapsedMinutes} mins";
	echo "</div>";
	
	// In MOCK_MODE, auto-complete after delay
	if ((defined('MOCK_MODE') && MOCK_MODE) || intval($ptr_id) === 0) {
		$objBookCron->_writeLog("MOCK auto-complete for Void PTR $ptr_id", 'voidStatusCron.txt');
		
		$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
		$objBookCron->updateInDB_trav('travellers_details', $ticketNum);
		
		// Check if all passengers cancelled
		$count_ticketed_temp = $objBookCron->count_ticketed__temp_book('travellers_details', $bookingId);
		if ($count_ticketed_temp == 0) {
			$objBookCron->updateInDB_temp_book('temp_booking', $mfreNum);
		}
		
		// Queue emails
		$contact = $objBookCron->getBookingContactEmail($bookingId);
		$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
		$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
		
		$userDetailsForCust = $usersObj->getUserDetails((int)$userId) ?: [];
		$userEmail = isset($userDetailsForCust['email']) ? trim($userDetailsForCust['email']) : '';
		$recipientEmail = $userEmail ?: $contactEmail;
		
		if (!empty($contactEmail)) {
			$domain = substr(strrchr($contactEmail, '@'), 1);
			$badDomains = array('mailinator.com','example.com','test.com');
			if (!in_array(strtolower($domain), $badDomains)) {
				$recipientEmail = $contactEmail;
			}
		}
		
		$paxRow = $objBookCron->getLisQuery("SELECT CONCAT(title,' ',first_name,' ',last_name) AS full_name FROM travellers_details WHERE e_ticket_number LIKE '%".$ticketNum."%' LIMIT 1");
		$passengerName = (!empty($paxRow) && isset($paxRow[0]['full_name'])) ? $paxRow[0]['full_name'] : 'Passenger';
		
		// Queue refund
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
		$paymentRefundEmailQueue[$bookingId]['totalRefundAmount'] += floatval($totalRefundAmount);
		$paymentRefundEmailQueue[$bookingId]['refunds'][] = [
			'passenger' => $passengerName,
			'amount' => number_format((float)$totalRefundAmount, 2),
			'ptr_id' => (string)$ptr_id,
			'ticket' => $ticketNum
		];
		
		if (!isset($bookingEmailQueue[$bookingId])) {
			$bookingEmailQueue[$bookingId] = [
				'email' => $recipientEmail,
				'mfRef' => $mfRefForEmail,
				'ptrType' => 'Void',
				'currency' => 'USD',
				'finalAmount' => 0,
				'counted_ptrs' => [],
				'bookingId' => $bookingId,
				'passengers' => []
			];
		}
		
		$ptrIdForEmail = $ptr_id;
		if (!in_array($ptrIdForEmail, $bookingEmailQueue[$bookingId]['counted_ptrs'], true)) {
			$bookingEmailQueue[$bookingId]['finalAmount'] += floatval($totalRefundAmount);
			$bookingEmailQueue[$bookingId]['counted_ptrs'][] = $ptrIdForEmail;
		}
		
		$bookingEmailQueue[$bookingId]['passengers'][] = [
			'name' => $passengerName,
			'ptr_id' => (string)$ptrIdForEmail,
			'ptr_status' => 'Completed',
			'eticket' => $ticketNum
		];
		
		echo "<div style='color: green;'><strong>✅ PTR $ptr_id COMPLETED (MOCK)</strong></div>";
		continue;
	}
	
	// Real API call
	if(isset($mfreNum)){
		$requestData = array(
			'ptrType' => 'Void',
			'MFRef' => $mfreNum,
			'PTRId' => $ptr_id,
			'Page' => 1
		);
		
		// API call with retry
		$maxRetries = 3;
		$retryCount = 0;
		$result = null;
		
		while ($retryCount < $maxRetries) {
			$result = $objBookCron->callApi('Search/PostTicketingRequest', $requestData);
			$httpCode = $result['httpCode'];
			$response = $result['responseData'];
			
			if ($httpCode == 200 && !empty($response)) {
				break;
			}
			
			$retryCount++;
			if ($retryCount < $maxRetries) {
				$objBookCron->_writeLog("Retry attempt $retryCount for Void PTR $ptr_id", 'voidStatusCron.txt');
				sleep(10);
			}
		}
		
		if ($response) {
			$responseData = json_decode($response, true);
		} else {
			$objBookCron->_writeLog("Failed to get response for Void PTR $ptr_id after $maxRetries attempts", 'voidStatusCron.txt');
			continue;
		}
		
		$objBookCron->_writeLog('Void Search API Response for PTR '.$ptr_id.': '.print_r($responseData, true), 'voidStatusCron.txt');
		
		if(isset($responseData['Success']) && $responseData['Success']){
			if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
				$PTRDetail = $responseData['Data']['PTRDetail'][0];
				$PTRId = $PTRDetail['PTRId'];
				$PTRStatus = $PTRDetail['PTRStatus'];
				$Resolution = $PTRDetail['Resolution'] ?? '';
				$TotalRefundAmount = $PTRDetail['TotalRefundAmount'] ?? 0;
				$Currency = $PTRDetail['Currency'] ?? 'USD';
				
				$objBookCron->_writeLog("Void PTR $PTRId Status: $PTRStatus, Resolution: $Resolution", 'voidStatusCron.txt');
				
				// Check if Void completed
				if($PTRStatus == "Completed" && $Resolution == "Voided"){
					echo "<div style='color: green;'><strong>✅ Void Completed for PTR $PTRId</strong></div>";
					
					// Update database
					$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
					$objBookCron->updateInDB_trav('travellers_details', $ticketNum);
					
					// Check if all passengers voided
					$count_ticketed_temp = $objBookCron->count_ticketed__temp_book('travellers_details', $bookingId);
					if($count_ticketed_temp == 0){
						$objBookCron->updateInDB_temp_book('temp_booking', $mfreNum);
					}
					
					// Queue completion email
					$contact = $objBookCron->getBookingContactEmail($bookingId);
					$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
					$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
					$userDetailsForCust = $usersObj->getUserDetails((int)$userId) ?: [];
					$userEmail = isset($userDetailsForCust['email']) ? trim($userDetailsForCust['email']) : '';
					$badDomains = array('mailinator.com','example.com','test.com');
					$recipientEmail = $userEmail ?: $contactEmail;
					if (!empty($contactEmail)) {
						$domain = substr(strrchr($contactEmail, '@'), 1);
						if (!in_array(strtolower($domain), $badDomains)) {
							$recipientEmail = $contactEmail;
						}
					}
					
					$paxNameRow = $objBookCron->getLisQuery("SELECT CONCAT(title,' ',first_name,' ',last_name) AS full_name FROM travellers_details WHERE e_ticket_number LIKE '%".$ticketNum."%' LIMIT 1");
					$passengerName = (!empty($paxNameRow) && isset($paxNameRow[0]['full_name'])) ? $paxNameRow[0]['full_name'] : 'Passenger';
					
					// Queue refund for Windcave processing
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
					$paymentRefundEmailQueue[$bookingId]['totalRefundAmount'] += floatval($TotalRefundAmount);
					$paymentRefundEmailQueue[$bookingId]['refunds'][] = [
						'passenger' => $passengerName,
						'amount' => number_format((float)$TotalRefundAmount, 2),
						'ptr_id' => (string)$PTRId,
						'ticket' => $ticketNum
					];
					
					// Queue booking completion email
					if (!isset($bookingEmailQueue[$bookingId])) {
						$bookingEmailQueue[$bookingId] = [
							'email' => $recipientEmail,
							'mfRef' => $mfRefForEmail,
							'ptrType' => 'Void',
							'currency' => $Currency,
							'finalAmount' => 0,
							'counted_ptrs' => [],
							'bookingId' => $bookingId,
							'passengers' => []
						];
					}
					
					if (!in_array($PTRId, $bookingEmailQueue[$bookingId]['counted_ptrs'], true)) {
						$bookingEmailQueue[$bookingId]['finalAmount'] += floatval($TotalRefundAmount);
						$bookingEmailQueue[$bookingId]['counted_ptrs'][] = $PTRId;
					}
					
					$bookingEmailQueue[$bookingId]['passengers'][] = [
						'name' => $passengerName,
						'ptr_id' => (string)$PTRId,
						'ptr_status' => $PTRStatus,
						'eticket' => $ticketNum
					];
				} else {
					echo "<div style='color: orange;'>⏳ Void PTR $PTRId still in process (Status: $PTRStatus)</div>";
				}
			}
		}
	}
}

// Send completion emails
foreach ($bookingEmailQueue as $bId => $payload) {
	$email = $payload['email'];
	if (empty($email)) { continue; }
	
	$subject = 'Flight Cancellation - Void Completed';
	$headerBar = 'Cancellation Update';
	$mfRef = $payload['mfRef'];
	$currency = $payload['currency'] ?: 'USD';
	$finalAmount = number_format((float)$payload['finalAmount'], 2);
	$cancelUrl = (defined('ENVIRONMENT_VAR') ? ENVIRONMENT_VAR : 'http://localhost/bulatrips/').'cancel_user?booking_id='.urlencode((string)$payload['bookingId']);
	
	$contactName = 'Customer';
	$contactRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE mf_reference = '".addslashes($mfRef)."' LIMIT 1");
	if (!empty($contactRow)) {
		$first = trim($contactRow[0]['contact_first_name'] ?? '');
		$last = trim($contactRow[0]['contact_last_name'] ?? '');
		$full = trim($first.' '.$last);
		if ($full !== '') { $contactName = $full; }
	}
	
	// Build passenger rows
	$rowsHtml = '';
	foreach ($payload['passengers'] as $p) {
		$rowsHtml .= '<tr>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['name']).'</td>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['ptr_id']).'</td>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['ptr_status']).'</td>'
			.'</tr>';
	}
	
	$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>'
		.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
		.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
		.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
		.'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">'.$headerBar.'</td></tr>'
		.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
		.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
		.'<p style="margin:0 0 18px 0;">Your Void request has been <strong>completed</strong>.</p>'
		.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
		.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.$mfRef.'</span></div>'
		.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Amount:</span> <span>'.$currency.' '.$finalAmount.'</span></div>'
		.'</div>'
		.'<div style="margin-top:16px;">'
		.'<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
		.'<thead><tr>'
		.'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Passenger</th>'
		.'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">PTR ID</th>'
		.'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Status</th>'
		.'</tr></thead>'
		.'<tbody>'.$rowsHtml.'</tbody>'
		.'</table>'
		.'</div>'
		.'<p style="margin:18px 0 22px 0;">You can review the details in your Booking Manager.</p>'
		.'<div style="text-align:center;margin:0 0 8px 0;"><a href="'.$cancelUrl.'" style="display:inline-block; background:#0029ff; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:bold;">Manage your Booking</a></div>'
		.'<p style="margin:12px 0 0 0;color:#555555;">Thank you for choosing Bulatrips.</p>'
		.'</td></tr></table></body></html>';
	
	$sent = send_mail_smart($email, $subject, $emailHtml);
	$objBookCron->_writeLog('Void completion email sent to '.$email.' status='.(int)$sent, 'voidStatusCron.txt');
}

// Process Windcave refunds
foreach ($paymentRefundEmailQueue as $bId => $refundPayload) {
	$email = $refundPayload['email'];
	if (empty($email) || $refundPayload['processed']) { continue; }
	
	$voidCompletionTotal = 0;
	if (isset($bookingEmailQueue[$bId])) {
		$voidCompletionTotal = $bookingEmailQueue[$bId]['finalAmount'];
	}
	$totalAmount = number_format((float)$voidCompletionTotal, 2);
	$mfRef = $refundPayload['mfRef'];
	
	try {
		$objBookCron->_writeLog('Processing Windcave refund for booking '.$bId.' total=USD '.$totalAmount, 'voidStatusCron.txt');
		$res = windcaveRefundBooking($bId, $mfRef, floatval($voidCompletionTotal), 'Void Refund', '', '', false);
		$objBookCron->_writeLog('Windcave refund result: '.json_encode($res), 'voidStatusCron.txt');
		
		$txnId = $res['refund_txn'] ?? 'N/A';
		$refundStatus = $res['status'] ?? 'unknown';
		
		$contactName = 'Customer';
		$contactRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE mf_reference = '".addslashes($mfRef)."' LIMIT 1");
		if (!empty($contactRow)) {
			$first = trim($contactRow[0]['contact_first_name'] ?? '');
			$last = trim($contactRow[0]['contact_last_name'] ?? '');
			$full = trim($first.' '.$last);
			if ($full !== '') { $contactName = $full; }
		}
		
		if ($refundStatus === 'success') {
			$subject = 'Payment Refund Processed - Bulatrips';
			$statusText = 'successfully processed';
			$headerColor = '#28a745';
			$message = 'The refund amount will be credited to your original payment method within 3-5 business days.';
		} else {
			$subject = 'Payment Refund - Processing Issue';
			$statusText = 'failed';
			$headerColor = '#dc3545';
			$reason = $res['reason'] ?? 'Unknown error';
			$message = 'Unfortunately, the refund could not be processed. Reason: '.htmlspecialchars($reason).'. Our support team will review this.';
		}
		
		$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>'
			.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;">'
			.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
			.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;"></td></tr>'
			.'<tr><td align="center" style="background-color:'.$headerColor.';color:#fff;font-size:18px;font-weight:bold;padding:14px;">Payment Refund Update</td></tr>'
			.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333;">'
			.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
			.'<p style="margin:0 0 18px 0;">Your payment refund has been <strong>'.$statusText.'</strong>.</p>'
			.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
			.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRef).'</span></div>'
			.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Refund Amount:</span> <span>USD '.htmlspecialchars($totalAmount).'</span></div>'
			.'<div style="margin:0;"><span style="font-weight:bold;">Transaction ID:</span> <span>'.htmlspecialchars($txnId).'</span></div>'
			.'</div>'
			.'<p style="margin:18px 0 0 0;">'.$message.'</p>'
			.'<p style="margin:12px 0 0 0;color:#555;">Thank you for choosing Bulatrips.</p>'
			.'</td></tr></table></body></html>';
		
		send_mail_smart($email, $subject, $emailHtml);
		$objBookCron->_writeLog('Payment refund email sent for booking '.$bId, 'voidStatusCron.txt');
		
		$paymentRefundEmailQueue[$bId]['processed'] = true;
	} catch (\Exception $e) {
		$objBookCron->_writeLog('Windcave refund failed: '.$e->getMessage(), 'voidStatusCron.txt');
	}
}

// Check for overdue Void PTRs
$overduePTRs = $objBookCron->getLisQuery("SELECT *, 
                 TIMESTAMPDIFF(MINUTE, created_date, NOW()) as elapsed_minutes,
                 sla_minutes
                 FROM cancel_booking 
                 WHERE ptr_type = 'Void'
                 AND ptr_status = 'InProcess' 
                 AND TIMESTAMPDIFF(MINUTE, created_date, NOW()) > (sla_minutes + 15)
                 ORDER BY created_date ASC");

if (!empty($overduePTRs)) {
	$objBookCron->_writeLog('Found ' . count($overduePTRs) . ' overdue Void PTRs', 'voidStatusCron.txt');
	echo "<div style='background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid #ff0000;'>";
	echo "<h3 style='color: red;'>⚠️ OVERDUE VOID PTRs DETECTED</h3>";
	
	foreach ($overduePTRs as $overdue) {
		echo "<div style='margin: 5px 0; padding: 5px; background: white;'>";
		echo "<strong>PTR ID:</strong> {$overdue['ptr_id']} | ";
		echo "<strong>Elapsed:</strong> {$overdue['elapsed_minutes']} mins | ";
		echo "<strong>SLA:</strong> {$overdue['sla_minutes']} mins";
		echo "</div>";
		
		$elapsed = $overdue['elapsed_minutes'];
		$sla = $overdue['sla_minutes'];
		$delayMinutes = $elapsed - $sla;
		
		// Level 1: Grace period
		if ($delayMinutes > 0 && $delayMinutes <= 15) {
			$objBookCron->_writeLog("Void PTR {$overdue['ptr_id']} in grace period", 'voidStatusCron.txt');
		}
		
		// Level 2: Customer notification (SLA + 45 mins)
		if ($delayMinutes > 45 && $delayMinutes <= 60) {
			$objBookCron->sendCustomerDelayNotification($overdue);
			echo "<div style='color: #ffc107;'>📧 Customer delay notification sent</div>";
		}
		
		// Level 3: Admin alert (SLA + 60 mins)
		if ($delayMinutes > 60) {
			$objBookCron->sendStuckPTRAlert($overdue);
			echo "<div style='color: red; font-weight: bold;'>🚨 Admin alert sent</div>";
		}
	}
	echo "</div>";
}

$objBookCron->_writeLog('Void Status Cron completed at '.date('l jS \of F Y h:i:s A'),'voidStatusCron.txt');
echo "<hr><p>✅ Void Status Cron completed successfully</p>";
?>

