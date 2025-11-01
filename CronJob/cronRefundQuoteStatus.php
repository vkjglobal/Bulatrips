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
   Module 		::> RefundQuote Status Monitoring Cron
   Programmer	::> AI Assistant
   Date			::> 18.10.2025
   
   DESCRIPTION::::>>>>
   Monitors RefundQuote PTRs and sends email when quote is ready
   - Polls Mystifly Search API for RefundQuote status
   - When Completed: Sends email with quote breakdown
   - Includes service fee deduction (from settings)
   - Generates secure Accept/Decline links
   - Stuck PTR detection

********************/

include_once __DIR__ . '/../includes/class.SearchPtrCron.php';
include_once __DIR__ . '/../includes/class.Users.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';

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
$usersObj = new Users();

// Set longer timeout for API calls
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 120);

//=================log write ======
$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','refundQuoteCron.txt');
$objBookCron->_writeLog('RefundQuote Status Cron Started','refundQuoteCron.txt');
//============ END log write ==========

// Get RefundQuote PTRs that are InProcess and not yet emailed
$resultBooking = $objBookCron->getLisQuery("SELECT * FROM cancel_booking 
          WHERE mf_ref_num != '' 
          AND ptr_type = 'RefundQuote'
          AND ptr_status = 'InProcess'
          AND cancel_status = 0
          AND (message IS NULL OR message = '' OR message NOT LIKE '%Quote emailed%')
          ORDER BY created_date ASC");

$objBookCron->_writeLog('Found ' . count($resultBooking) . ' RefundQuote PTRs to check', 'refundQuoteCron.txt');

echo "<h3>RefundQuote Status Monitor</h3>";
echo "<p>Checking " . count($resultBooking) . " RefundQuote PTR(s)</p>";
echo "<hr>";

foreach($resultBooking as $resultBookingdata){
	$bookingId = $resultBookingdata['booking_id'];
	$userId = $resultBookingdata['user_agent_id'];
	$ptr_id = $resultBookingdata['ptr_id'];
	$mfreNum = $resultBookingdata['mf_ref_num'];
	$createdDate = isset($resultBookingdata['created_date']) ? strtotime($resultBookingdata['created_date']) : null;
	$elapsedSeconds = $createdDate ? (time() - $createdDate) : 0;
	$elapsedMinutes = round($elapsedSeconds / 60);
	
	echo "<div style='background: #fff3e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff9800;'>";
	echo "<strong>PTR ID:</strong> {$ptr_id} | ";
	echo "<strong>Booking:</strong> {$bookingId} | ";
	echo "<strong>MF Ref:</strong> {$mfreNum} | ";
	echo "<strong>Elapsed:</strong> {$elapsedMinutes} mins";
	echo "</div>";
	
	// Prepare Search API request
	$requestData = array(
		'ptrType' => 'RefundQuote',
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
			$objBookCron->_writeLog("Retry attempt $retryCount for RefundQuote PTR $ptr_id", 'refundQuoteCron.txt');
			sleep(10);
		}
	}
	
	if ($response) {
		$responseData = json_decode($response, true);
	} else {
		$objBookCron->_writeLog("Failed to get response for RefundQuote PTR $ptr_id", 'refundQuoteCron.txt');
		continue;
	}
	
	$objBookCron->_writeLog('RefundQuote Search API Response: '.print_r($responseData, true), 'refundQuoteCron.txt');
	
	if(isset($responseData['Success']) && $responseData['Success']){
		if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
			$PTRDetail = $responseData['Data']['PTRDetail'][0];
			$PTRId = $PTRDetail['PTRId'];
			$PTRStatus = $PTRDetail['PTRStatus'];
			$RefundQuotes = $PTRDetail['RefundQuotes'] ?? [];
			
			$objBookCron->_writeLog("RefundQuote PTR $PTRId Status: $PTRStatus", 'refundQuoteCron.txt');
			
			// Check if quote is ready
			if($PTRStatus == "Completed" && !empty($RefundQuotes)){
				echo "<div style='color: green;'><strong>✅ RefundQuote Ready for PTR $PTRId</strong></div>";
				
				// Calculate total from Mystifly
				$mystiflyTotalRefund = 0;
				$currency = 'USD';
				$passengerBreakdown = [];
				
				foreach($RefundQuotes as $quote){
					$mystiflyTotalRefund += floatval($quote['TotalRefundAmount'] ?? 0);
					$currency = $quote['Currency'] ?? 'USD';
					
					$passengerBreakdown[] = [
						'name' => trim(($quote['FirstName'] ?? '').' '.($quote['LastName'] ?? '')),
						'eticket' => $quote['ETicket'] ?? '',
						'total_fare' => $quote['TotalFare'] ?? 0,
						'unused_fare' => $quote['UnusedFare'] ?? 0,
						'cancellation_charge' => $quote['CancellationCharge'] ?? 0,
						'no_show_charge' => $quote['NoShowCharge'] ?? 0,
						'refund_amount' => $quote['TotalRefundAmount'] ?? 0
					];
				}
				
				// Get service fees from settings (per passenger)
				$settingsRows = $objBookCron->getLisQuery("SELECT `key`, `value` FROM settings WHERE `key` IN ('refund_fee', 'refund_addition')");
				$settings = [];
				foreach ($settingsRows as $row) {
					$settings[$row['key']] = $row['value'];
				}
				
				$refundFeePerPax = floatval($settings['refund_fee'] ?? 0);
				$refundAdditionPerPax = floatval($settings['refund_addition'] ?? 0);
				
				// Calculate total fees based on number of passengers
				$numPassengers = count($passengerBreakdown);
				$totalRefundFee = $refundFeePerPax * $numPassengers;
				$totalRefundAddition = $refundAdditionPerPax * $numPassengers;
				$totalServiceDeduction = $totalRefundFee + $totalRefundAddition;
				
				// Calculate final refund after service fee deduction
				$finalRefundToCustomer = max(0, $mystiflyTotalRefund - $totalServiceDeduction);
				
				$objBookCron->_writeLog("RefundQuote Calculation: Passengers=$numPassengers, Mystifly=$mystiflyTotalRefund, RefundFee=".($refundFeePerPax."x".$numPassengers."=".$totalRefundFee).", Addition=".($refundAdditionPerPax."x".$numPassengers."=".$totalRefundAddition).", Final=$finalRefundToCustomer", 'refundQuoteCron.txt');
				
				// Get customer details
				$contact = $objBookCron->getBookingContactEmail($bookingId);
				$customerEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
				$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
				
				if (empty($customerEmail)) {
					$objBookCron->_writeLog("No customer email for booking $bookingId", 'refundQuoteCron.txt');
					continue;
				}
				
				// Get customer name
				$contactName = 'Customer';
				$nameRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
				if (!empty($nameRow)) {
					$first = trim($nameRow[0]['contact_first_name'] ?? '');
					$last = trim($nameRow[0]['contact_last_name'] ?? '');
					$full = trim($first.' '.$last);
					if ($full !== '') { $contactName = $full; }
				}
				
				// Generate secure tokens
				$secret = 'bulatrips_refund_secret_2025_xyz';
				$acceptToken = hash('sha256', $ptr_id . $bookingId . 'accept' . $secret);
				$declineToken = hash('sha256', $ptr_id . $bookingId . 'decline' . $secret);
				
				$baseUrl = defined('ENVIRONMENT_VAR') ? ENVIRONMENT_VAR : 'http://localhost/bulatrips/';
				$acceptUrl = $baseUrl . "accept_refund_quote.php?ptr_id=" . urlencode($ptr_id) . "&booking_id=" . $bookingId . "&action=yes&token=" . $acceptToken;
				$declineUrl = $baseUrl . "accept_refund_quote.php?ptr_id=" . urlencode($ptr_id) . "&booking_id=" . $bookingId . "&action=no&token=" . $declineToken;
				
				// Build passenger breakdown HTML
				$passengerRows = '';
				foreach($passengerBreakdown as $pax){
					$passengerRows .= '<tr style="border-bottom: 1px solid #eee;">'
						.'<td style="padding: 8px;">'.htmlspecialchars($pax['name']).'</td>'
						.'<td style="padding: 8px; text-align: right;">'.htmlspecialchars($pax['eticket']).'</td>'
						.'<td style="padding: 8px; text-align: right;">$'.number_format($pax['refund_amount'], 2).'</td>'
						.'</tr>';
				}
				
				// Calculate UTC times
				$quoteReadyTimeUTC = gmdate('d M Y, H:i', time()) . ' UTC';
				$expectedCompletionUTC = gmdate('d M Y, H:i', time() + (1440 * 60)) . ' UTC'; // 24 hours for refund
				
				// Build email
				$subject = 'Refund Quote Ready - Action Required (Booking #'.$bookingId.')';
				$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
					.'<body style="margin:0; padding:20px; background-color:#f5f7fb; font-family:Arial,sans-serif;">'
					.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:650px; margin:0 auto; background-color:#ffffff; border-radius:8px; box-shadow:0 0 15px rgba(0,0,0,0.1);">'
					.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px; width:auto;"></td></tr>'
					.'<tr><td align="center" style="background-color:#0029ff; color:#ffffff; font-size:20px; font-weight:bold; padding:18px;">Refund Quote Ready</td></tr>'
					.'<tr><td style="padding:25px; font-size:15px; line-height:1.6; color:#333333;">'
					.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
					.'<p style="margin:0 0 20px 0;">Your refund quote is ready for review. Please find the details below:</p>'
					
					// Booking Info
					.'<div style="background:#f1f1f1; border-radius:6px; padding:15px; margin:0 0 20px 0;">'
					.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MF Reference:</span> <span>'.htmlspecialchars($mfRefForEmail).'</span></div>'
					.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($ptr_id).'</span></div>'
					.'<div style="margin:0;"><span style="font-weight:bold;">Booking ID:</span> <span>'.htmlspecialchars($bookingId).'</span></div>'
					.'</div>'
					
					// Passenger Breakdown
					.'<h3 style="color: #333; margin: 20px 0 10px 0;">Passenger Details:</h3>'
					.'<table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0;">'
					.'<thead><tr style="background: #f8f9fa; border-bottom: 2px solid #0029ff;">'
					.'<th style="padding: 10px; text-align: left;">Passenger</th>'
					.'<th style="padding: 10px; text-align: right;">Ticket</th>'
					.'<th style="padding: 10px; text-align: right;">Refund</th>'
					.'</tr></thead>'
					.'<tbody>'.$passengerRows.'</tbody>'
					.'</table>'
					
					// Refund Calculation
					.'<h3 style="color: #333; margin: 20px 0 10px 0;">Refund Calculation:</h3>'
					.'<table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0; border: 1px solid #ddd;">'
					.'<tr style="border-bottom: 1px solid #ddd;">'
					.'<td style="padding: 12px; font-size: 15px;">Mystifly Refund Amount:</td>'
					.'<td style="padding: 12px; text-align: right; font-size: 15px; font-weight: bold;">$'.number_format($mystiflyTotalRefund, 2).'</td>'
					.'</tr>'
					.'<tr style="border-bottom: 1px solid #ddd; background: #fff3cd;">'
					.'<td style="padding: 12px; color: #856404;">Refund Processing Fee ($'.number_format($refundFeePerPax, 2).' × '.$numPassengers.' passenger'.($numPassengers > 1 ? 's' : '').'):</td>'
					.'<td style="padding: 12px; text-align: right; color: #dc3545; font-weight: bold;">-$'.number_format($totalRefundFee, 2).'</td>'
					.'</tr>'
					.'<tr style="border-bottom: 1px solid #ddd; background: #fff3cd;">'
					.'<td style="padding: 12px; color: #856404;">Additional Service Fee ($'.number_format($refundAdditionPerPax, 2).' × '.$numPassengers.' passenger'.($numPassengers > 1 ? 's' : '').'):</td>'
					.'<td style="padding: 12px; text-align: right; color: #dc3545; font-weight: bold;">-$'.number_format($totalRefundAddition, 2).'</td>'
					.'</tr>'
					.'<tr style="background: #d4edda; border-top: 2px solid #28a745;">'
					.'<td style="padding: 15px; font-weight: bold; font-size: 17px; color: #155724;">Final Refund Amount:</td>'
					.'<td style="padding: 15px; text-align: right; font-weight: bold; font-size: 20px; color: #28a745;">$'.number_format($finalRefundToCustomer, 2).' '.$currency.'</td>'
					.'</tr>'
					.'</table>'
					
					.'<div style="background:#e7f3ff; border-left:4px solid #0d6efd; padding:15px; margin:15px 0; border-radius:4px;">'
					.'<p style="margin:0 0 10px 0; font-weight:bold; color:#084298; font-size: 16px;">💰 This amount will be credited to your original payment method.</p>'
					.'<p style="margin:0; font-size:13px; color:#666;">Processing time: 3-5 business days after acceptance</p>'
					.'</div>'
					
					// Timeline
					.'<div style="background:#fff3cd; border-left:4px solid #ffc107; padding:15px; margin:15px 0; border-radius:4px;">'
					.'<p style="margin:0 0 10px 0; font-weight:bold; color:#856404;">⏰ Processing Timeline</p>'
					.'<div style="font-size:13px; color:#856404; margin:4px 0;">Quote Generated: '.htmlspecialchars($quoteReadyTimeUTC).'</div>'
					.'<div style="font-size:13px; color:#856404; margin:4px 0;">Expected Refund Completion: '.htmlspecialchars($expectedCompletionUTC).'</div>'
					.'<p style="margin:10px 0 0 0; font-size:12px; color:#666; font-style:italic;">Times shown in UTC (Universal Time). Please adjust for your local timezone.</p>'
					.'</div>'
					
					// Action Buttons
					.'<div style="text-align:center; margin:30px 0 20px 0;">'
					.'<a href="'.htmlspecialchars($acceptUrl).'" style="background:#28a745; color:#ffffff; padding:15px 35px; text-decoration:none; border-radius:6px; font-weight:bold; font-size:16px; display:inline-block; margin:10px;">✅ Accept Refund</a>'
					.'<br>'
					.'<a href="'.htmlspecialchars($declineUrl).'" style="background:#6c757d; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:normal; font-size:14px; display:inline-block; margin:10px;">❌ Decline</a>'
					.'</div>'
					
					.'<p style="font-size:12px; color:#999; border-top:1px solid #ddd; padding-top:15px; margin-top:20px;">'
					.'This link will expire in 24 hours.<br>'
					.'Quote generated at: '.htmlspecialchars($quoteReadyTimeUTC).'<br>'
					.'If you have any questions, please contact our support team.'
					.'</p>'
					.'</td></tr></table></body></html>';
				
				// Send email
				$emailSent = send_mail_smart($customerEmail, $subject, $emailHtml);
				
				if($emailSent){
					$objBookCron->_writeLog("RefundQuote email sent to: $customerEmail for PTR: $ptr_id", 'refundQuoteCron.txt');
					
					// Mark as emailed to prevent duplicate sends
					$objBookCron->update('cancel_booking', [
						'message' => 'RefundQuote emailed to customer',
						'ptr_status' => 'Completed'
					], "id = " . (int)$resultBookingdata['id']);
					
					echo "<div style='color: green;'>📧 Quote email sent successfully</div>";
				} else {
					$objBookCron->_writeLog("Failed to send email to: $customerEmail", 'refundQuoteCron.txt');
					echo "<div style='color: red;'>❌ Email sending failed</div>";
				}
				
			} else {
				echo "<div style='color: orange;'>⏳ RefundQuote PTR $PTRId still processing (Status: $PTRStatus)</div>";
			}
		}
	}
}

// Check for overdue RefundQuote PTRs
$overduePTRs = $objBookCron->getLisQuery("SELECT *, 
                 TIMESTAMPDIFF(MINUTE, created_date, NOW()) as elapsed_minutes,
                 sla_minutes
                 FROM cancel_booking 
                 WHERE ptr_type = 'RefundQuote'
                 AND ptr_status = 'InProcess' 
                 AND TIMESTAMPDIFF(MINUTE, created_date, NOW()) > (sla_minutes + 15)
                 AND (message IS NULL OR message NOT LIKE '%Quote emailed%')
                 ORDER BY created_date ASC");

if (!empty($overduePTRs)) {
	$objBookCron->_writeLog('Found ' . count($overduePTRs) . ' overdue RefundQuote PTRs', 'refundQuoteCron.txt');
	echo "<div style='background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid #ff0000;'>";
	echo "<h3 style='color: red;'>⚠️ OVERDUE REFUNDQUOTE PTRs</h3>";
	
	foreach ($overduePTRs as $overdue) {
		$elapsed = $overdue['elapsed_minutes'];
		$sla = $overdue['sla_minutes'];
		$delayMinutes = $elapsed - $sla;
		
		echo "<div style='margin: 5px 0; padding: 5px; background: white;'>";
		echo "<strong>PTR ID:</strong> {$overdue['ptr_id']} | ";
		echo "<strong>Delay:</strong> {$delayMinutes} mins past SLA";
		echo "</div>";
		
		// Customer notification (SLA + 45 mins)
		if ($delayMinutes > 45 && $delayMinutes <= 60) {
			$objBookCron->sendCustomerDelayNotification($overdue);
			echo "<div style='color: #ffc107;'>📧 Customer delay notification sent</div>";
		}
		
		// Admin alert (SLA + 60 mins)
		if ($delayMinutes > 60) {
			$objBookCron->sendStuckPTRAlert($overdue);
			echo "<div style='color: red;'>🚨 Admin alert sent</div>";
		}
	}
	echo "</div>";
}

$objBookCron->_writeLog('RefundQuote Status Cron completed at '.date('l jS \of F Y h:i:s A'),'refundQuoteCron.txt');
echo "<hr><p>✅ RefundQuote Status Cron completed</p>";
?>

