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
   Module 		::> Booking Cron on pending statuses from void and refund api
   Programmer	::> Asad
   Date			::> 22.11.2024
   Updated      ::> Enhanced for better delay handling and email notifications
   
   DESCRIPTION::::>>>>
   Booking Cron on in process statuses of void and refund ptr requests
   - Handles API delays and retries
   - Sends email notifications on completion


********************/

include_once __DIR__ . '/../includes/class.SearchPtrCron.php';
include_once __DIR__ . '/../includes/class.Users.php';
include_once __DIR__ . '/../includes/common_const.php';
// Load a single email helper to avoid function redeclarations
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

$objBookCron     =   new SearchPtrCron();
$adminToemail  =   "no-reply@bulatrips.com";
$usersObj = new Users();

// Set longer timeout for API calls to handle delays
ini_set('max_execution_time', 300); // 5 minutes
ini_set('default_socket_timeout', 120); // 2 minutes for individual calls

//=================log write for book API ======
$logReQ =   "Successfully started - Enhanced version with delay handling";
$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchPtrCron.txt');
$objBookCron->_writeLog('Request Received\n'.$logReQ,'searchPtrCron.txt');
//============ END log write for book API ==========

// Aggregation queue: one email per booking
$bookingEmailQueue = [];
$paymentRefundEmailQueue = [];

// Check if specific PTR ID is requested via URL parameter
$specificPtrId = isset($_GET['ptr_id']) ? intval($_GET['ptr_id']) : null;
$specificMfRef = isset($_GET['mf_ref']) ? trim($_GET['mf_ref']) : null;

if ($specificPtrId || $specificMfRef) {
	echo "<h3>Manual PTR Check Mode</h3>";
	if ($specificPtrId) {
		echo "<p>Checking specific PTR ID: <strong>$specificPtrId</strong></p>";
	}
	if ($specificMfRef) {
		echo "<p>Checking specific MF Reference: <strong>$specificMfRef</strong></p>";
	}
	echo "<hr>";
}

// Get PTRs that are in InProcess status and haven't been checked recently (avoid spam)
if ($specificPtrId || $specificMfRef) {
	// Get specific PTR
	$resultBooking = $objBookCron->getSpecificPTR($specificPtrId, $specificMfRef);
} else {
	// Get all pending PTRs
	$resultBooking = $objBookCron->getBookCronIDs();
}

echo "<pre>";
	print_r($resultBooking);

echo "</pre>";

foreach($resultBooking as $resultBookingdata){
		
		$bookingId =    (isset($resultBookingdata['booking_id']) ? $resultBookingdata['booking_id'] : $resultBookingdata['id']);
		$userId =    $resultBookingdata['user_agent_id'];
		$ptr_id =   $resultBookingdata['ptr_id'];
		$mfreNum = $resultBookingdata['mf_ref_num'];
		$totalRefundAmount = $resultBookingdata['total_refund_amount'];
		$ticketNum = $resultBookingdata['ticket_number'];
		$createdDate = isset($resultBookingdata['created_date']) ? strtotime($resultBookingdata['created_date']) : null;
		$elapsedSeconds = $createdDate ? (time() - $createdDate) : 0;
	   
		// In MOCK_MODE (or when PTRId is 0), auto-complete after a short delay to simulate Mystifly fulfillment
		if ((defined('MOCK_MODE') && MOCK_MODE) || intval($ptr_id) === 0) {
			$ptrType = $resultBookingdata['ptr_type'];
			$objBookCron->_writeLog("MOCK auto-complete for PTR $ptr_id (ticket $ticketNum, MF $mfreNum, type $ptrType)", 'searchPtrCron.txt');
			
			// Handle different PTR types in MOCK mode
			if ($ptrType === 'Reissue') {
				// Check if this is initial quote request or acceptance processing
				$message = $resultBookingdata['message'] ?? '';
				if (strpos($message, 'ReissueQuote request submitted') !== false) {
					// This is initial quote request - send quote options email
				$contact = $objBookCron->getBookingContactEmail($bookingId);
				$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
				$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
				$contactName = 'Customer';
				$nrow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
				if (!empty($nrow)) {
					$f = trim($nrow[0]['contact_first_name'] ?? '');
					$l = trim($nrow[0]['contact_last_name'] ?? '');
					$full = trim($f.' '.$l);
					if ($full !== '') { $contactName = $full; }
				}
				
				// Collect selected traveller ids for this PTR to keep acceptance scoped
				$passengerIdsCsv = '';
				try {
					$paxRows = $objBookCron->getLisQuery("SELECT traveller_id FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ptr_id = '".addslashes((string)$ptr_id)."' AND message = 'ReissueQuote request submitted' AND traveller_id > 0");
					if (!empty($paxRows)) {
						$ids = [];
						foreach ($paxRows as $r) { $ids[] = (int)$r['traveller_id']; }
						$passengerIdsCsv = implode(',', array_unique($ids));
					}
				} catch (Exception $e) {
					$objBookCron->_writeLog('Collect pax ids failed: '.$e->getMessage(), 'searchPtrCron.txt');
				}
				
				if (!empty($contactEmail)) {
					// Generate secure tokens for accept/decline links
					$acceptToken1 = md5($ptr_id . $bookingId . 'reissue_secret_key');
					$acceptToken2 = md5($ptr_id . $bookingId . 'reissue_secret_key');
					$declineToken = md5($ptr_id . $bookingId . 'decline_secret_key');
					
					$baseUrl = ENVIRONMENT_VAR;
					$extraPax = $passengerIdsCsv !== '' ? ('&pax=' . urlencode($passengerIdsCsv)) : '';
					$acceptUrl1 = $baseUrl . "reissue_email_handler.php?action=accept&ptr_id=" . urlencode($ptr_id) . "&option=1&booking_id=" . $bookingId . "&token=" . $acceptToken1 . $extraPax;
					$acceptUrl2 = $baseUrl . "reissue_email_handler.php?action=accept&ptr_id=" . urlencode($ptr_id) . "&option=2&booking_id=" . $bookingId . "&token=" . $acceptToken2 . $extraPax;
					$declineUrl = $baseUrl . "reissue_email_handler.php?action=decline&ptr_id=" . urlencode($ptr_id) . "&booking_id=" . $bookingId . "&token=" . $declineToken;
					
					// Build enriched options from MockMystifly GetExchangeQuote
					if (!class_exists('MockMystifly')) { include_once(__DIR__.'/../includes/mock_mystifly.php'); }
					$mockQuote = MockMystifly::getGetExchangeQuoteResponse($ptr_id);
					$prefs = $mockQuote['Data']['RequestedPreferences'] ?? [];
					$optionsHtml = '';
					foreach ($prefs as $pref) {
						$option = intval($pref['Option'] ?? 0);
						$legs = $pref['QuotedSegments'] ?? [];
						$fare = ($pref['QuotedFares'][0] ?? []);
						if (empty($legs)) { continue; }
						$legsHtml = '';
						foreach ($legs as $idx => $seg) {
							$cabinCode = strtoupper($seg['CabinClass'] ?? 'Y');
							$cabinName = ($cabinCode === 'C') ? 'Business' : (($cabinCode === 'F') ? 'First' : (($cabinCode === 'W' || $cabinCode === 'S') ? 'Premium Economy' : 'Economy'));
							$depFmt = isset($seg['DepartureDatetime']) ? date('d M Y, H:i', strtotime($seg['DepartureDatetime'])) : '';
							$arrFmt = isset($seg['ArrivalDateTime']) ? date('d M Y, H:i', strtotime($seg['ArrivalDateTime'])) : '';
							$duration = $seg['Duration'] ?? '';
							$stops = isset($seg['Stops']) ? intval($seg['Stops']) : 0;
							$flightDetails = ($seg['AirlineCode'] ?? '').' '.($seg['FlightNumber'] ?? '');
							$bookingClass = $seg['BookingClass'] ?? '';
							$legsHtml .= '<div style="background:#f9fbff;border-radius:6px;padding:12px 14px;margin:10px 0;border:1px solid #e6ecff;">'
								.'<div style="font-weight:bold;margin:0 0 6px 0;">'.($idx === 0 ? 'Departure' : 'Return').' Details</div>'
								.'<div style="margin:0 0 6px 0;"><strong>Route:</strong> '.htmlspecialchars(($seg['Origin'] ?? '').' → '.($seg['Destination'] ?? '')).'</div>'
								.'<div style="margin:0 0 6px 0;"><strong>Departure:</strong> '.htmlspecialchars($depFmt).' &nbsp; <strong>Arrival:</strong> '.htmlspecialchars($arrFmt).'</div>'
								.'<div style="margin:0 0 0 0;"><strong>Flight:</strong> '.htmlspecialchars($flightDetails).' &nbsp; <strong>Cabin:</strong> '.htmlspecialchars($cabinName.' ('.$cabinCode.')').' &nbsp; <strong>Booking Class:</strong> '.htmlspecialchars($bookingClass).'</div>'
								.'<div style="margin:6px 0 0 0;color:#555;"><strong>Duration:</strong> '.htmlspecialchars($duration).' &nbsp; <strong>Stops:</strong> '.intval($stops).'</div>'
							.'</div>';
						}
						$totalCost = $fare['TotalFareDifference'] ?? 0;
						$currency = $fare['Currency'] ?? 'USD';
						$baseFare = $fare['BaseFareDifference'] ?? 0;
						$taxDiff = $fare['TaxDifference'] ?? 0;
						$penalty = $fare['Penalty'] ?? 0;
						$acceptUrl = ($option === 1) ? $acceptUrl1 : $acceptUrl2;
						$borderColor = ($option == 1) ? '#28a745' : '#007bff';
						$bgColor = ($option == 1) ? '#f8fff8' : '#f8f9ff';
						$optionsHtml .= '<div style="border:2px solid '.$borderColor.';border-radius:8px;padding:20px;margin:15px 0;background:'.$bgColor.';">'
							.'<h4 style="color:'.$borderColor.';margin:0 0 10px 0;">Option '.$option.'</h4>'
							.$legsHtml
							.'<p style="margin:8px 0 6px 0;"><strong>Total Cost:</strong> <span style="font-size:18px;color:'.$borderColor.';">$'.number_format((float)$totalCost, 2).' '.htmlspecialchars($currency).'</span></p>'
							.'<p style="margin:0 0 12px 0;font-size:13px;color:#555;">Base: $'.number_format((float)$baseFare, 2).' + Tax: $'.number_format((float)$taxDiff, 2).' + Penalty: $'.number_format((float)$penalty, 2).'</p>'
							.'<div style="text-align:center;"><a href="'.$acceptUrl.'" style="display:inline-block;background:'.$borderColor.';color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Accept Option '.$option.'</a></div>'
						.'</div>';
					}

					$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
						.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
						.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
						.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
						.'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">Reissue Quote Ready</td></tr>'
						.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
						.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
						.'<p style="margin:0 0 18px 0;">Great news! Your reissue quote options are now available.</p>'
						.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;margin:0 0 20px 0;">'
						.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($ptr_id).'</span></div>'
						.'<div style="margin:0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRefForEmail).'</span></div>'
						.'</div>'
						.$optionsHtml
						.'<div style="text-align:center;margin:20px 0;">'
						.'<a href="'.$declineUrl.'" style="display:inline-block;background:#6c757d;color:#ffffff;padding:10px 20px;text-decoration:none;border-radius:6px;">Decline Reissue</a>'
						.'</div>'
						.'<p style="margin:18px 0 0 0;color:#555555;font-size:13px;">This link will expire in 24 hours. If you need assistance, please contact our support team.</p>'
						.'</td></tr></table></body></html>';
					
					send_mail_smart($contactEmail, 'Reissue Quote Ready - Action Required', $emailHtml);
					$objBookCron->_writeLog('MOCK MODE: Reissue quote options email sent to: ' . $contactEmail, 'searchPtrCron.txt');
					// Mark processed so this row is not reprocessed on next cron run
					try {
						// Use the cron DB helper update() instead of accessing the connection directly
						$objBookCron->update('cancel_booking', [
							'message' => 'ReissueQuote email sent',
							'cancel_status' => 1
						], "id = ".(int)$resultBookingdata['id']);
					} catch (Exception $e) {
						$objBookCron->_writeLog('Failed to mark cancel_booking row as emailed: '.$e->getMessage(), 'searchPtrCron.txt');
					}
				}
				
				echo "<div style='color: blue;'><strong>PTR $ptr_id REISSUE QUOTE READY (MOCK):</strong> Quote options email sent.</div>";
				} else {
					// This is acceptance processing - simulate completion and send final email
					$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
					
					// Update travellers_details with new ticket number and completion status
					$newTicketNumber = 'TKT' . rand(100000, 999999); // Mock new ticket
					if (!empty($ticketNum)) {
						$updateData = [
							'e_ticket_number' => $newTicketNumber,
							'reissue_status' => 'Completed',
							'ticket_status' => 'Ticketed'
						];
						$condition = "`e_ticket_number` = '".addslashes($ticketNum)."' AND `flight_booking_id` = ".intval($bookingId);
						$objBookCron->update('travellers_details', $updateData, $condition);
						$objBookCron->_writeLog('MOCK MODE: Updated ticket '.$ticketNum.' to new ticket '.$newTicketNumber, 'searchPtrCron.txt');
					} else {
						// Fallback: update by traveller_id if ticket number not recorded in cancel_booking
						$travIdFallback = isset($resultBookingdata['traveller_id']) ? (int)$resultBookingdata['traveller_id'] : 0;
						if ($travIdFallback > 0) {
							$updateData = [
								'e_ticket_number' => $newTicketNumber,
								'reissue_status' => 'Completed',
								'ticket_status' => 'Ticketed'
							];
							$condition = "id = ".$travIdFallback;
							$objBookCron->update('travellers_details', $updateData, $condition);
							$objBookCron->_writeLog('MOCK MODE: Updated by traveller_id '.$travIdFallback.' to new ticket '.$newTicketNumber, 'searchPtrCron.txt');
						}
					}
					
					// Send reissue completion email
					$contact = $objBookCron->getBookingContactEmail($bookingId);
					$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
					$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
					$contactName = 'Customer';
					$nrow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
					if (!empty($nrow)) {
						$f = trim($nrow[0]['contact_first_name'] ?? '');
						$l = trim($nrow[0]['contact_last_name'] ?? '');
						$full = trim($f.' '.$l);
						if ($full !== '') { $contactName = $full; }
					}
					
					if (!empty($contactEmail)) {
						$subject = 'Flight Reissue Completed - New Tickets Issued';
						// Try to load accepted itinerary for email
						$itineraryHtml = '';
						try {
							$ptrNumeric = is_numeric($ptr_id) ? (string)intval($ptr_id) : (preg_match('/(\d{6,})/', (string)$ptr_id, $mx) ? (string)intval($mx[1]) : (string)$ptr_id);
							$rIti = $objBookCron->getLisQuery("SELECT message FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ptr_id = '".addslashes($ptrNumeric)."' AND message IS NOT NULL AND message <> '' ORDER BY id DESC LIMIT 1");
							if (!empty($rIti)) {
								$js = json_decode($rIti[0]['message'], true);
								if (json_last_error() === JSON_ERROR_NONE && !empty($js['outbound'])) {
									$ob = $js['outbound'];
									$depFmt = !empty($ob['dep_date']) ? date('d M Y, H:i', strtotime($ob['dep_date'])) : '';
									$arrFmt = !empty($ob['arrival_date']) ? date('d M Y, H:i', strtotime($ob['arrival_date'])) : '';
									$itineraryHtml = '<div style="background:#f9fbff;border-radius:6px;padding:12px 14px;margin:0 0 14px 0;border:1px solid #e6ecff;">'
										.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Route:</span> '.htmlspecialchars(($ob['origin'] ?? '').' → '.($ob['destination'] ?? '')).'</div>'
										.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Departure:</span> '.htmlspecialchars($depFmt).'</div>'
										.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Arrival:</span> '.htmlspecialchars($arrFmt).'</div>'
										.'<div style="margin:0 0 0 0;"><span style="font-weight:bold;">Cabin:</span> '.htmlspecialchars($ob['cabin_preference'] ?? '').' &nbsp; <span style="font-weight:bold;">Flight:</span> '.htmlspecialchars(($ob['airline_code'] ?? '').' '.($ob['flight_no'] ?? '')).'</div>'
									.'</div>';
								}
							}
						} catch (\Exception $e) { /* ignore */ }
						// Use the SAME $newTicketNumber generated above for DB update
						$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
							.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
							.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
							.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
							.'<tr><td align="center" style="background-color:#28a745;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">Reissue Completed</td></tr>'
							.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
							.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
							.'<p style="margin:0 0 18px 0;">Great news! Your flight reissue has been completed successfully.</p>'
							.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;margin:0 0 20px 0;">'
							.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($ptr_id).'</span></div>'
							.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRefForEmail).'</span></div>'
							.'<div style="margin:0;"><span style="font-weight:bold;">New Ticket Number:</span> <span style="color:#28a745;font-weight:bold;">'.htmlspecialchars($newTicketNumber).'</span></div>'
							.'</div>'
							.(!empty($itineraryHtml) ? $itineraryHtml : '')
							.((function() use ($objBookCron, $bookingId, $ptr_id) {
								$ptrNumeric = is_numeric($ptr_id) ? (string)intval($ptr_id) : (preg_match('/(\d{6,})/', (string)$ptr_id, $mx) ? (string)intval($mx[1]) : (string)$ptr_id);
								$rows = $objBookCron->getLisQuery("SELECT message FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ptr_id = '".addslashes($ptrNumeric)."' AND message IS NOT NULL AND message <> '' ORDER BY id DESC LIMIT 1");
								if (empty($rows)) { return ''; }
								$js = json_decode($rows[0]['message'], true);
								if (json_last_error() !== JSON_ERROR_NONE) { return ''; }
								$blocks = '';
								foreach ([['key' => 'outbound_segments', 'label' => 'Departure'], ['key' => 'return_segments', 'label' => 'Return']] as $group) {
									$key = $group['key']; $label = $group['label'];
									if (empty($js[$key]) || !is_array($js[$key])) { continue; }
									foreach ($js[$key] as $idx => $seg) {
										$depFmt = !empty($seg['dep_date']) ? date('d M Y, H:i', strtotime($seg['dep_date'])) : '';
										$arrFmt = !empty($seg['arrival_date']) ? date('d M Y, H:i', strtotime($seg['arrival_date'])) : '';
										$route = htmlspecialchars(($seg['origin'] ?? '').' → '.($seg['destination'] ?? ''));
										$blocks .= '<div style="background:#f9fbff;border-radius:6px;padding:12px 14px;margin:0 0 14px 0;border:1px solid #e6ecff;">'
											.'<div style="font-weight:bold;margin:0 0 6px 0;">'.$label.' Segment '.($idx+1).'</div>'
											.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Route:</span> '.$route.'</div>'
											.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Departure:</span> '.htmlspecialchars($depFmt).' &nbsp; <span style="font-weight:bold;">Arrival:</span> '.htmlspecialchars($arrFmt).'</div>'
											.'<div style="margin:0 0 0 0;"><span style="font-weight:bold;">Cabin:</span> '.htmlspecialchars($seg['cabin_preference'] ?? '').' &nbsp; <span style="font-weight:bold;">Flight:</span> '.htmlspecialchars(($seg['airline_code'] ?? '').' '.($seg['flight_no'] ?? '')).'</div>'
										.'</div>';
									}
								}
								return $blocks;
							})())
							.'<p style="margin:18px 0 22px 0;">Your old ticket has been cancelled and replaced with the new ticket above. Please save this information for your records.</p>'
							.'<div style="text-align:center;margin:0 0 8px 0;"><a href="'.ENVIRONMENT_VAR.'cancel_user?booking_id='.$bookingId.'" style="display:inline-block; background:#0029ff; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:bold;">View Booking Details</a></div>'
							.'<p style="margin:12px 0 0 0;color:#555555;">Thank you for choosing Bulatrips.</p>'
							.'</td></tr></table></body></html>';
						
						send_mail_smart($contactEmail, $subject, $emailHtml);
						$objBookCron->_writeLog('MOCK MODE: Reissue completion email sent to: ' . $contactEmail, 'searchPtrCron.txt');
					}
					
					echo "<div style='color: green;'><strong>PTR $ptr_id REISSUE COMPLETED (MOCK):</strong> New tickets issued, completion email sent.</div>";
				}
				// Skip the rest of processing for reissue in mock mode
				continue;
			} else {
				// For void/refund: use existing logic
			$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
			$objBookCron->updateInDB_trav('travellers_details', $ticketNum);

			// If no more ticketed passengers for this booking, close the booking
			$count_ticketed_temp = $objBookCron->count_ticketed__temp_book('travellers_details', $bookingId);
			if ($count_ticketed_temp == 0) {
				$objBookCron->updateInDB_temp_book('temp_booking', $mfreNum);
			}

			// Queue completion email data instead of sending per passenger
			$contact = $objBookCron->getBookingContactEmail($bookingId);
			$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
			$userDetailsForCust = $usersObj->getUserDetails((int)$userId) ?: [];
			$userEmail = isset($userDetailsForCust['email']) ? trim($userDetailsForCust['email']) : '';
			$badDomains = array('mailinator.com','example.com','test.com');
			$recipientEmail = $userEmail ?: $contactEmail;
			if (!empty($contactEmail)) { $domain = substr(strrchr($contactEmail, '@'), 1); if (!in_array(strtolower($domain), $badDomains)) { $recipientEmail = $contactEmail; }}
			$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
			$paxRow = $objBookCron->getLisQuery("SELECT CONCAT(title,' ',first_name,' ',last_name) AS full_name FROM travellers_details WHERE e_ticket_number LIKE '%".$ticketNum."%' LIMIT 1");
			$passengerName = (!empty($paxRow) && isset($paxRow[0]['full_name'])) ? $paxRow[0]['full_name'] : 'Passenger';
			
			// Queue refund for consolidated processing (don't process individual refunds)
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
					'ptrType' => $resultBookingdata['ptr_type'],
					'currency' => 'USD',
					'finalAmount' => 0,
					'counted_ptrs' => [],
					'bookingId' => $bookingId,
					'passengers' => []
				];
			}
			// Try to get a real PTR ID from DB for this ticket (MOCK path may have 0)
			$ptrIdForEmail = $ptr_id;
			$ptrRow = $objBookCron->getLisQuery("SELECT ptr_id FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ticket_number LIKE '%".$ticketNum."%' ORDER BY id DESC LIMIT 1");
			if (!empty($ptrRow) && !empty($ptrRow[0]['ptr_id'])) {
				$ptrIdForEmail = $ptrRow[0]['ptr_id'];
			} else {
				// fallback to search_cancel_ptr
				$ptrRow2 = $objBookCron->getLisQuery("SELECT PTRId FROM search_cancel_ptr WHERE booking_id = ".(int)$bookingId." AND ticket_num LIKE '%".$ticketNum."%' ORDER BY id DESC LIMIT 1");
				if (!empty($ptrRow2) && !empty($ptrRow2[0]['PTRId'])) {
					$ptrIdForEmail = $ptrRow2[0]['PTRId'];
				}
			}
			// Only count amount once per unique ptr id
			if (!empty($ptrIdForEmail)) {
				if (!in_array($ptrIdForEmail, $bookingEmailQueue[$bookingId]['counted_ptrs'], true)) {
					$bookingEmailQueue[$bookingId]['finalAmount'] += floatval($totalRefundAmount);
					$bookingEmailQueue[$bookingId]['counted_ptrs'][] = $ptrIdForEmail;
				}
			} else {
				// If no PTR, count once if not already counted any
				if (empty($bookingEmailQueue[$bookingId]['counted_ptrs'])) {
					$bookingEmailQueue[$bookingId]['finalAmount'] += floatval($totalRefundAmount);
				}
			}
			$bookingEmailQueue[$bookingId]['passengers'][] = [
				'name' => $passengerName,
				'ptr_id' => (string)$ptrIdForEmail,
				'ptr_status' => 'Completed',
				'eticket' => $ticketNum
			];

			echo "<div style='color: green;'><strong>PTR $ptr_id COMPLETED (MOCK):</strong> Updated traveller, cancel_booking and booking status.</div>";
			// Skip real API call for mock
			continue;
			}
		}

		if(isset($mfreNum)){
			if(isset($resultBookingdata['ptr_type']) && $resultBookingdata['ptr_type'] == "Refund"){
					$requestData = array(
						'ptrType' => 'Refund',
						'MFRef' => $mfreNum,
						'PTRId' => $ptr_id,
						'Page' => 1
					);
			}
			elseif(isset($resultBookingdata['ptr_type']) && $resultBookingdata['ptr_type'] == "Reissue"){
					$requestData = array(
						'ptrType' => 'GetExchangeQuote',
						'MFRef' => $mfreNum,
						'PTRId' => $ptr_id,
						'Page' => 1
					);
			}
			else{
				$requestData = array(
					'ptrType' => 'Void',
					'MFRef' => $mfreNum,
					'PTRId' => $ptr_id,
					'Page' => 1
				);
			}
		   
			$endpoint   =   'Search/PostTicketingRequest';
			
			// Add retry mechanism for delayed responses
			$maxRetries = 3;
			$retryCount = 0;
			$result = null;
			
			while ($retryCount < $maxRetries) {
				$result = $objBookCron->callApi($endpoint, $requestData);
				$httpCode = $result['httpCode'];
				$response = $result['responseData'];
				
				if ($httpCode == 200 && !empty($response)) {
					break; // Success, exit retry loop
				}
				
				$retryCount++;
				if ($retryCount < $maxRetries) {
					$objBookCron->_writeLog("Retry attempt $retryCount for booking $bookingId", 'searchPtrCron.txt');
					sleep(10); // Wait 10 seconds before retry
				}
			}
	
			if ($response) {
				$responseData = json_decode($response, true);
			} else {
				$objBookCron->_writeLog("Failed to get response after $maxRetries attempts for booking $bookingId", 'searchPtrCron.txt');
				continue; // Skip this booking
			}
			
			$logRes =   print_r($responseData, true);
			$logReQ =   print_r($requestData, true);
		
			$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','search.txt');
			$objBookCron->_writeLog('userId is '.$userId,'search.txt');
			$objBookCron->_writeLog('Booking ID is '.$bookingId,'search.txt');
			$objBookCron->_writeLog('Request Received\n'.$logReQ,'search.txt');
		
			$objBookCron->_writeLog('REsponse Received for MF:\n'.$mfreNum,'search.txt');
		
			$objBookCron->_writeLog('REsponse Received\n'.$logRes,'search.txt');
			$message = ""; 
			$TotalRefundAmount =0;
			$CreditNoteNumber ='';
			$CreditNoteStatus ='';
			$Resolution   =   '';

			if(isset($responseData['Success'])){
				//  echo  $responseData['Data']['PTRDetail']['PTRId'];
				if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
					$cancel_status  =   0;
					$PTRId    =   $responseData['Data']['PTRDetail'][0]['PTRId'];
				$PTRType    =   $responseData['Data']['PTRDetail'][0]['PTRType'];
				$BookingStatus   =   $responseData['Data']['PTRDetail'][0]['BookingStatus'];
				$PTRStatus      =   $responseData['Data']['PTRDetail'][0]['PTRStatus'];     
				
				$Resolution   =   $responseData['Data']['PTRDetail'][0]['Resolution'];
				$ProcessingMethod   =   isset($responseData['Data']['PTRDetail'][0]['ProcessingMethod']) ? $responseData['Data']['PTRDetail'][0]['ProcessingMethod'] : '';
				$CreditNoteNumber   =   isset($responseData['Data']['PTRDetail'][0]['CreditNoteNumber']) ? $responseData['Data']['PTRDetail'][0]['CreditNoteNumber'] : '';
				$CreditNoteStatus   =   isset($responseData['Data']['PTRDetail'][0]['CreditNoteStatus']) ? $responseData['Data']['PTRDetail'][0]['CreditNoteStatus'] : '';
				$TotalRefundAmount  =   isset($responseData['Data']['PTRDetail'][0]['TotalRefundAmount']) ? $responseData['Data']['PTRDetail'][0]['TotalRefundAmount'] : 0;
				$Currency   =   isset($responseData['Data']['PTRDetail'][0]['Currency']) ? $responseData['Data']['PTRDetail'][0]['Currency'] : 'USD';
				
					$objBookCron->_writeLog('Step 1Success '.$PTRStatus,'search.txt');
					$objBookCron->_writeLog('Step 1 Resolution '.$Resolution,'search.txt');
                
                    // If the status at this stage is not Completed, send a single informative email now (in process)
                    if (strtolower($PTRStatus) !== 'completed') {
                        $contactPre = $objBookCron->getBookingContactEmail($bookingId);
                        $contactEmailPre = isset($contactPre['contact_email']) ? trim($contactPre['contact_email']) : '';
                        $mfRefPre = isset($contactPre['mf_reference']) ? $contactPre['mf_reference'] : $mfreNum;
                        $userDetailsPre = $usersObj->getUserDetails((int)$userId) ?: [];
                        $userEmailPre = isset($userDetailsPre['email']) ? trim($userDetailsPre['email']) : '';
                        $recipientPre = $userEmailPre ?: $contactEmailPre;
                        $contactNamePre = 'Customer';
                        $nrow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
                        if (!empty($nrow)) {
                            $f = trim($nrow[0]['contact_first_name'] ?? '');
                            $l = trim($nrow[0]['contact_last_name'] ?? '');
                            $full = trim($f.' '.$l);
                            if ($full !== '') { $contactNamePre = $full; }
                        }
                        $subjectPre = ($resultBookingdata['ptr_type'] == 'Refund') ? 'Flight Refund - In Process' : 'Flight Cancellation - In Process';
                        $headerPre = ($resultBookingdata['ptr_type'] == 'Refund') ? 'Refund Update' : 'Cancellation Update';
                        // one row placeholder; details list is added later when completed
                        $emailPre = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
                            .'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
                            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
                            .'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
                            .'<tr><td align="center" style="padding:14px; font-size:18px; font-weight:bold; color:#ffffff; background-color:#0029ff;">'.$headerPre.'</td></tr>'
                            .'<tr><td style="padding:22px; font-size:15px; line-height:1.6; color:#333333;">'
                            .'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactNamePre).',</p>'
                            .'<p style="margin:0 0 18px 0;">Your '.$resultBookingdata['ptr_type'].' request has been <strong>'.htmlspecialchars($PTRStatus).'</strong>.</p>'
                            .'<div style="background:#f1f1f1; padding:14px; border-radius:6px;">'
                            .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRefPre).'</span></div>'
                            .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Amount:</span> <span>'.htmlspecialchars($Currency).' '.number_format((float)$TotalRefundAmount,2).'</span></div>'
                            .'</div>'
                            .'<p style="margin:18px 0 0 0; color:#555555;">We will notify you by email once the airline completes processing.</p>'
                            .'</td></tr></table></body></html>';
                        if (!empty($recipientPre)) {
                            send_mail_smart($recipientPre, $subjectPre, $emailPre);
                        }
                    }
				
					$success_can_sts    =0;
					foreach($responseData['Data']['PTRDetail'][0]['pTRPaxDetails'] as $k => $val){
						$pax_booking_id_transaction =   $val['Id'];
						$PaxId =   $val['PaxId'];
						$TicketStatus =   $val['TicketStatus'];
						$is_active_booking_status =   $val['IsActive'];                
						$ticket_num =   $val['TicketNumber']; 
					   
						// Handle different completion scenarios
					$isVoidRefundCompleted = ($PTRStatus == "Completed") && ($Resolution == $resultBookingdata['ptr_type']."ed");
					$isReissueQuoteReady = ($PTRStatus == "Completed") && ($Resolution == "QuoteUpdated") && ($resultBookingdata['ptr_type'] == "Reissue");
					$isReissueCompleted = ($PTRStatus == "Completed") && ($Resolution == "Reissued") && ($resultBookingdata['ptr_type'] == "Reissue");
					
					if($isVoidRefundCompleted){
							//cancellation success
							
							$update_cancelBooking_status      =    $objBookCron->updateInDB_cancelbooking('cancel_booking',$ticket_num);
							$update_TravellerB_result      =    $objBookCron->updateInDB_trav('travellers_details',$ticket_num);
							$cancel_status    =1;

							// Queue email per booking
							$contact = $objBookCron->getBookingContactEmail($bookingId);
							$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
							$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
							$userDetailsForCust = $usersObj->getUserDetails((int)$userId) ?: [];
							$userEmail = isset($userDetailsForCust['email']) ? trim($userDetailsForCust['email']) : '';
							$badDomains = array('mailinator.com','example.com','test.com');
							$recipientEmail = $userEmail ?: $contactEmail;
							if (!empty($contactEmail)) { $domain = substr(strrchr($contactEmail, '@'), 1); if (!in_array(strtolower($domain), $badDomains)) { $recipientEmail = $contactEmail; }}

							$paxNameRow = $objBookCron->getLisQuery("SELECT CONCAT(title,' ',first_name,' ',last_name) AS full_name FROM travellers_details WHERE e_ticket_number LIKE '%".$ticket_num."%' LIMIT 1");
							$passengerName = (!empty($paxNameRow) && isset($paxNameRow[0]['full_name'])) ? $paxNameRow[0]['full_name'] : 'Passenger';

							// Queue refund for consolidated processing (don't process individual refunds)
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
								'ticket' => $ticket_num
							];

							if (!isset($bookingEmailQueue[$bookingId])) {
								$bookingEmailQueue[$bookingId] = [
									'email' => $recipientEmail,
									'mfRef' => $mfRefForEmail,
									'ptrType' => $resultBookingdata['ptr_type'],
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
                                // Send IN-PROCESS email immediately after user submission (no manage button)
                                $subjectInit = ($resultBookingdata['ptr_type'] == 'Refund') ? 'Flight Refund - In Process' : 'Flight Cancellation - In Process';
                                $headerBarInit = ($resultBookingdata['ptr_type'] == 'Refund') ? 'Refund Update' : 'Cancellation Update';
                                $contactRowInit = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
                                $contactNameInit = 'Customer';
                                if (!empty($contactRowInit)) {
                                    $first = trim($contactRowInit[0]['contact_first_name'] ?? '');
                                    $last = trim($contactRowInit[0]['contact_last_name'] ?? '');
                                    $full = trim($first.' '.$last);
                                    if ($full !== '') { $contactNameInit = $full; }
                                }
                                $emailInit = $bookingEmailQueue[$bookingId]['email'];
                                $mfRefInit = $bookingEmailQueue[$bookingId]['mfRef'];
                                $currencyInit = $Currency;
                                $amountInit = number_format((float)$TotalRefundAmount, 2);
                                $rowsInit = '<tr><td style="padding:8px 12px;border-bottom:1px solid #eee;>'
                                             .htmlspecialchars($passengerName).'</td>'
                                             .'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars((string)$PTRId).'</td>'
                                             .'<td style="padding:8px 12px;border-bottom:1px solid #eee;">In Process</td></tr>';
                                $emailHtmlInit = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
                                    .'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
                                    .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
                                    .'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
                                    .'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">'.$headerBarInit.'</td></tr>'
                                    .'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
                                    .'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactNameInit).',</p>'
                                    .'<p style="margin:0 0 18px 0;">Your '.$resultBookingdata['ptr_type'].' request has been <strong>in process</strong>.</p>'
                                    .'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
                                    .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.$mfRefInit.'</span></div>'
                                    .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Amount:</span> <span>'.$currencyInit.' '.$amountInit.'</span></div>'
                                    .'</div>'
                                    .'<div style="margin-top:16px;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
                                    .'<thead><tr>'
                                    .'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Passenger</th>'
                                    .'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">PTR ID</th>'
                                    .'<th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Status</th>'
                                    .'</tr></thead><tbody>'.$rowsInit.'</tbody></table></div>'
                                    .'<p style="margin:18px 0 0 0;color:#555555;">We will notify you by email once the airline completes processing.</p>'
                                    .'</td></tr></table></body></html>';
                                sendMail($emailInit, $subjectInit, $emailHtmlInit);
                            }
                            $bookingEmailQueue[$bookingId]['passengers'][] = [
                                'name' => $passengerName,
                                'ptr_id' => (string)$PTRId,
                                'ptr_status' => $PTRStatus,
                                'eticket' => $ticket_num
							];
						}
						elseif($isReissueQuoteReady){
							// Reissue quote options are ready - send email with options
							$contact = $objBookCron->getBookingContactEmail($bookingId);
							$contactEmail = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
							$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
							$contactName = 'Customer';
							$nrow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
							if (!empty($nrow)) {
								$f = trim($nrow[0]['contact_first_name'] ?? '');
								$l = trim($nrow[0]['contact_last_name'] ?? '');
								$full = trim($f.' '.$l);
								if ($full !== '') { $contactName = $full; }
							}
							
							if (!empty($contactEmail)) {
								// Parse real GetExchangeQuote response for live mode
								$optionsHtml = '';
								if (defined('MOCK_MODE') && MOCK_MODE) {
									// Use static mock options for testing
									$acceptToken1 = md5($PTRId . $bookingId . 'reissue_secret_key');
									$acceptToken2 = md5($PTRId . $bookingId . 'reissue_secret_key');
									$baseUrl = ENVIRONMENT_VAR;
									$acceptUrl1 = $baseUrl . "reissue_email_handler.php?action=accept&ptr_id=" . urlencode($PTRId) . "&option=1&booking_id=" . $bookingId . "&token=" . $acceptToken1;
									$acceptUrl2 = $baseUrl . "reissue_email_handler.php?action=accept&ptr_id=" . urlencode($PTRId) . "&option=2&booking_id=" . $bookingId . "&token=" . $acceptToken2;
									
									$optionsHtml = '<div style="border:2px solid #28a745;border-radius:8px;padding:20px;margin:15px 0;background:#f8fff8;">'
										.'<h4 style="color:#28a745;margin:0 0 10px 0;">Option 1 - Economy Class</h4>'
										.'<p style="margin:0 0 10px 0;"><strong>Total Cost:</strong> <span style="font-size:18px;color:#28a745;">$78.75 USD</span></p>'
										.'<p style="margin:0 0 15px 0;">2 days later departure, Economy class</p>'
										.'<div style="text-align:center;"><a href="'.$acceptUrl1.'" style="display:inline-block;background:#28a745;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Accept Option 1</a></div>'
										.'</div>'
										.'<div style="border:2px solid #007bff;border-radius:8px;padding:20px;margin:15px 0;background:#f8f9ff;">'
										.'<h4 style="color:#007bff;margin:0 0 10px 0;">Option 2 - Business Class</h4>'
										.'<p style="margin:0 0 10px 0;"><strong>Total Cost:</strong> <span style="font-size:18px;color:#007bff;">$165.50 USD</span></p>'
										.'<p style="margin:0 0 15px 0;">3 days later departure, Business class</p>'
										.'<div style="text-align:center;"><a href="'.$acceptUrl2.'" style="display:inline-block;background:#007bff;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Accept Option 2</a></div>'
										.'</div>';
								} else {
									// Parse live GetExchangeQuote response
									$requestedPreferences = isset($responseData['Data']['PTRDetail'][0]['RequestedPreferences']) ? $responseData['Data']['PTRDetail'][0]['RequestedPreferences'] : [];
									if (!empty($requestedPreferences)) {
										foreach ($requestedPreferences as $pref) {
											$option = $pref['Option'] ?? 1;
											$quotedFares = $pref['QuotedFares'] ?? [];
											$quotedSegments = $pref['QuotedSegments'] ?? [];
											
											if (!empty($quotedFares)) {
												$fare = $quotedFares[0];
												$totalCost = $fare['TotalFareDifference'] ?? 0;
												$currency = $fare['Currency'] ?? 'USD';
												$baseFare = $fare['BaseFareDifference'] ?? 0;
												$taxDiff = $fare['TaxDifference'] ?? 0;
												$penalty = $fare['Penalty'] ?? 0;
												
												// Get flight details
												$segment = !empty($quotedSegments) ? $quotedSegments[0] : [];
												$cabinClass = $segment['CabinClass'] ?? 'Y';
												$cabinName = ($cabinClass === 'C') ? 'Business' : (($cabinClass === 'F') ? 'First' : (($cabinClass === 'W' || $cabinClass === 'S') ? 'Premium Economy' : 'Economy'));
												$flightDetails = ($segment['AirlineCode'] ?? '') . ' ' . ($segment['FlightNumber'] ?? '');
												$depDate = isset($segment['DepartureDatetime']) ? date('M d, Y', strtotime($segment['DepartureDatetime'])) : '';
												$arrDate = isset($segment['ArrivalDateTime']) ? date('d M Y, H:i', strtotime($segment['ArrivalDateTime'])) : '';
												$duration = $segment['Duration'] ?? '';
												$stops = isset($segment['Stops']) ? intval($segment['Stops']) : 0;
												
												$acceptToken = md5($PTRId . $bookingId . $option . 'reissue_secret_key');
												$acceptUrl = $baseUrl . "reissue_email_handler.php?action=accept&ptr_id=" . urlencode($PTRId) . "&option=" . $option . "&booking_id=" . $bookingId . "&token=" . $acceptToken;
												
												$borderColor = ($option == 1) ? '#28a745' : '#007bff';
												$bgColor = ($option == 1) ? '#f8fff8' : '#f8f9ff';
												
												$optionsHtml .= '<div style="border:2px solid '.$borderColor.';border-radius:8px;padding:20px;margin:15px 0;background:'.$bgColor.';">'
													.'<h4 style="color:'.$borderColor.';margin:0 0 10px 0;">Option '.$option.' - '.$cabinName.' Class</h4>'
													.'<p style="margin:0 0 6px 0;"><strong>Route:</strong> '.htmlspecialchars(($segment['Origin'] ?? '').' → '.($segment['Destination'] ?? '')).'</p>'
													.'<p style="margin:0 0 6px 0;"><strong>Departure:</strong> '.htmlspecialchars($depDate).' &nbsp; <strong>Arrival:</strong> '.htmlspecialchars($arrDate).'</p>'
													.'<p style="margin:0 0 6px 0;"><strong>Flight:</strong> '.htmlspecialchars($flightDetails).' &nbsp; <strong>Duration:</strong> '.htmlspecialchars($duration).' &nbsp; <strong>Stops:</strong> '.intval($stops).'</p>'
													.'<p style="margin:0 0 10px 0;"><strong>Total Cost:</strong> <span style="font-size:18px;color:'.$borderColor.';">$'.number_format((float)$totalCost, 2).' '.htmlspecialchars($currency).'</span></p>'
													.'<div style="text-align:center;"><a href="'.$acceptUrl.'" style="display:inline-block;background:'.$borderColor.';color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">Accept Option '.$option.'</a></div>'
												.'</div>';
											}
										}
									}
									
									if (empty($optionsHtml)) {
										$optionsHtml = '<p style="color:#dc3545;">No reissue options available at this time. Please contact support.</p>';
									}
								}
								
								$declineToken = md5($PTRId . $bookingId . 'decline_secret_key');
								$declineUrl = $baseUrl . "reissue_email_handler.php?action=decline&ptr_id=" . urlencode($PTRId) . "&booking_id=" . $bookingId . "&token=" . $declineToken;
								
								$subject = 'Reissue Quote Ready - Action Required';
								$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
									.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
									.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
									.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
									.'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">Reissue Quote Ready</td></tr>'
									.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
									.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
									.'<p style="margin:0 0 18px 0;">Great news! Your reissue quote options are now available.</p>'
									.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;margin:0 0 20px 0;">'
									.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($PTRId).'</span></div>'
									.'<div style="margin:0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRefForEmail).'</span></div>'
									.'</div>'
									.$optionsHtml
									.'<div style="text-align:center;margin:20px 0;">'
									.'<a href="'.$declineUrl.'" style="display:inline-block;background:#6c757d;color:#ffffff;padding:10px 20px;text-decoration:none;border-radius:6px;">Decline Reissue</a>'
									.'</div>'
									.'<p style="margin:18px 0 0 0;color:#555555;font-size:13px;">This link will expire in 24 hours. If you need assistance, please contact our support team.</p>'
									.'</td></tr></table></body></html>';
								
								send_mail_smart($contactEmail, $subject, $emailHtml);
								$objBookCron->_writeLog('Reissue quote options email sent to: ' . $contactEmail, 'searchPtrCron.txt');
							}
						}
						elseif($isReissueCompleted){
							// Reissue completed - get new ticket numbers from TripDetails API
							if (defined('MOCK_MODE') && MOCK_MODE) {
								// MOCK: Generate new ticket number
								$newTicketNumber = 'TKT' . rand(100000, 999999);
								$objBookCron->_writeLog('MOCK MODE: Generated new ticket: ' . $newTicketNumber, 'searchPtrCron.txt');
							} else {
								// LIVE: Call TripDetails API to get actual new ticket numbers
								include_once(__DIR__ . '/../reissue_trip_details.php');
								// This will call TripDetails API and update database
							}
							
							// Read accepted itinerary (dates/class) stored at accept time and update DB
							try {
								$ptrNumeric = is_numeric($PTRId) ? (string)intval($PTRId) : (preg_match('/(\d{6,})/', (string)$PTRId, $mx) ? (string)intval($mx[1]) : (string)$PTRId);
								$rows = $objBookCron->getLisQuery("SELECT message FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ptr_id = '".addslashes($ptrNumeric)."' AND message IS NOT NULL AND message <> '' ORDER BY id DESC LIMIT 1");
								if (!empty($rows)) {
									$msg = $rows[0]['message'];
									$data = json_decode($msg, true);
									if (json_last_error() === JSON_ERROR_NONE && !empty($data['outbound'])) {
										$ob = $data['outbound'];
										$depDate = $ob['dep_date'] ?? '';
										$arrDate = $ob['arrival_date'] ?? '';
										$airline = $ob['airline_code'] ?? '';
										$flightNo = $ob['flight_no'] ?? '';
										$cabin = $ob['cabin_preference'] ?? '';
										// Update temp_booking (top card) and primary flight_segment row for this booking
										if (!empty($depDate)) {
											$objBookCron->update('temp_booking', ['dep_date' => $depDate], "id = ".(int)$bookingId);
										}
										$segRow = $objBookCron->getLisQuery("SELECT id FROM flight_segment WHERE booking_id = ".(int)$bookingId." ORDER BY id ASC LIMIT 1");
										if (!empty($segRow)) {
											$segId = (int)$segRow[0]['id'];
											$updates = [];
											if (!empty($depDate)) { $updates['dep_date'] = $depDate; }
											if (!empty($arrDate)) { $updates['arrival_date'] = $arrDate; }
											if (!empty($cabin)) { $updates['cabin_preference'] = $cabin; }
											if (!empty($airline)) { $updates['airline_code'] = $airline; }
											if (!empty($flightNo)) { $updates['flight_no'] = $flightNo; }
											if (!empty($updates)) {
												$objBookCron->update('flight_segment', $updates, "id = ".$segId);
												$objBookCron->_writeLog('Updated itinerary for booking '.$bookingId.' from accept cache: '.json_encode($updates), 'searchPtrCron.txt');
											}
										}
									}
								}
							} catch (\Exception $e) {
								$objBookCron->_writeLog('Failed to apply accepted itinerary: '.$e->getMessage(), 'searchPtrCron.txt');
							}

							$objBookCron->_writeLog('Reissue completed for PTR: ' . $PTRId, 'searchPtrCron.txt');
						}
					
					$bookCanIns = $objBookCron->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$Currency,$is_active_booking_status,$cancel_status);																									   
					}
					$count_ticketed_temp    =   $objBookCron->count_ticketed__temp_book('travellers_details',$bookingId);
					if($count_ticketed_temp == 0){
							//  update tempbooking and traveller details tables with cancelled sts 
						$update_tempB_result           =   $objBookCron->updateInDB_temp_book('temp_booking',$mfreNum);
					}
				}
			}
			
			$objBookCron->_writeLog('step end of void  ========= '.$message,'search.txt');
		
			
			
		}
	
}

// After processing all PTRs, send ONE email per booking with all passenger details
foreach ($bookingEmailQueue as $bId => $payload) {
	$email = $payload['email'];
	if (empty($email)) { continue; }

	// Note: Partial refunds are now issued per passenger during completion above
	// No need to wait for all passengers to be cancelled

	$subject = ($payload['ptrType'] == 'Refund') ? 'Flight Refund - Completed' : 'Flight Cancellation - Void Completed';
	$headerBar = ($payload['ptrType'] == 'Refund') ? 'Refund Update' : 'Cancellation Update';
	$mfRef = $payload['mfRef'];
	$currency = $payload['currency'] ?: 'USD';
	$finalAmount = number_format((float)$payload['finalAmount'], 2);
	$cancelUrl = ENVIRONMENT_VAR.'cancel_user?booking_id='.urlencode((string)$payload['bookingId']);

	// Try to resolve a friendly contact name
	$contactName = 'Customer';
	$contactRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE mf_reference = '".addslashes($mfRef)."' LIMIT 1");
	if (!empty($contactRow)) {
		$first = trim($contactRow[0]['contact_first_name'] ?? '');
		$last = trim($contactRow[0]['contact_last_name'] ?? '');
		$full = trim($first.' '.$last);
		if ($full !== '') { $contactName = $full; }
	}

	// Build passengers table rows
	$rowsHtml = '';
	foreach ($payload['passengers'] as $p) {
		$ptrDisplay = $p['ptr_id'];
		if (strpos($ptrDisplay, 'PTR_') !== 0 && !empty($p['eticket'])) {
			$r = $objBookCron->getLisQuery("SELECT ptr_id FROM travellers_details WHERE e_ticket_number = '".addslashes($p['eticket'])."' LIMIT 1");
			if (!empty($r) && !empty($r[0]['ptr_id'])) {
				$ptrDisplay = $r[0]['ptr_id'];
			}
		}
		$rowsHtml .= '<tr>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($p['name']).'</td>'
			.'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($ptrDisplay).'</td>'
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
		.'<p style="margin:0 0 18px 0;">Your '.$payload['ptrType'].' request has been <strong>completed</strong>.</p>'
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
	$objBookCron->_writeLog('Aggregated email attempt to '.$email.' subject='.$subject.' sent='.(int)$sent.' passengers='.count($payload['passengers']), 'searchPtrCron.txt');
}

// Process consolidated Windcave refunds (one API call per booking)
foreach ($paymentRefundEmailQueue as $bId => $refundPayload) {
	$email = $refundPayload['email'];
	if (empty($email) || $refundPayload['processed']) { continue; }
	
	// Use the same total amount as shown in void completion email
	$voidCompletionTotal = 0;
	if (isset($bookingEmailQueue[$bId])) {
		$voidCompletionTotal = $bookingEmailQueue[$bId]['finalAmount'];
	}
	$totalAmount = number_format((float)$voidCompletionTotal, 2);
	$mfRef = $refundPayload['mfRef'];
	
	// Process ONE Windcave refund for the total amount
	try {
		$objBookCron->_writeLog('ConsolidatedRefund: Processing single Windcave refund for booking '.$bId.' total=USD '.$totalAmount, 'searchPtrCron.txt');
		$res = windcaveRefundBooking($bId, $mfRef, floatval($voidCompletionTotal), 'Multiple Passengers', '', '', false);
		$objBookCron->_writeLog('ConsolidatedRefund result: '.json_encode($res), 'searchPtrCron.txt');
		
		// Always send email and log, regardless of Windcave success/failure
		$txnId = $res['refund_txn'] ?? 'N/A';
		$refundStatus = $res['status'] ?? 'unknown';
		
		// Get contact name
		$contactName = 'Customer';
		$contactRow = $objBookCron->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE mf_reference = '".addslashes($mfRef)."' LIMIT 1");
		if (!empty($contactRow)) {
			$first = trim($contactRow[0]['contact_first_name'] ?? '');
			$last = trim($contactRow[0]['contact_last_name'] ?? '');
			$full = trim($first.' '.$last);
			if ($full !== '') { $contactName = $full; }
		}
		
		if ($refundStatus === 'success') {
			// Success email
			$subject = 'Payment Refund Processed - Bulatrips';
			$statusText = 'successfully processed';
			$headerColor = '#28a745';
			$message = 'The refund amount will be credited to your original payment method within 3-5 business days. You will receive a notification from your bank once the amount is credited.';
		} else {
			// Failure email
			$subject = 'Payment Refund - Processing Issue';
			$statusText = 'failed';
			$headerColor = '#dc3545';
			$reason = $res['reason'] ?? 'Unknown error';
			$message = 'Unfortunately, the refund could not be processed at this time. Reason: '.htmlspecialchars($reason).'. Our support team will review this and contact you within 24 hours.';
		}
		
		$emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
			.'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
			.'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
			.'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
			.'<tr><td align="center" style="background-color:'.$headerColor.';color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">Payment Refund Update</td></tr>'
			.'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
			.'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
			.'<p style="margin:0 0 18px 0;">Your payment refund has been <strong>'.$statusText.'</strong>.</p>'
			.'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
			.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRef).'</span></div>'
			.'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Refund Amount:</span> <span>USD '.htmlspecialchars($totalAmount).'</span></div>'
			.'<div style="margin:0;"><span style="font-weight:bold;">Transaction ID:</span> <span>'.htmlspecialchars($txnId).'</span></div>'
			.'</div>'
			.'<p style="margin:18px 0 0 0;">'.$message.'</p>'
			.'<p style="margin:12px 0 0 0;color:#555555;">Thank you for choosing Bulatrips.</p>'
			.'</td></tr></table></body></html>';
		
		$sent = send_mail_smart($email, $subject, $emailHtml);
		$objBookCron->_writeLog('Payment refund email sent to '.$email.' status='.$refundStatus.' total=USD '.$totalAmount.' txn='.$txnId, 'searchPtrCron.txt');
		
		// Send admin notification for payment refund
		try {
			$adminSubject = ($refundStatus === 'success') ? 'Payment Refund Completed - Booking #'.$bId : 'Payment Refund Failed - Booking #'.$bId;
			$statusIcon = ($refundStatus === 'success') ? '✅' : '❌';
			$statusColor = ($refundStatus === 'success') ? '#28a745' : '#dc3545';
			
			$adminContent = '<div style="font-family:Arial,sans-serif;">'
				.'<h3 style="color:'.$statusColor.';">'.$statusIcon.' Payment Refund '.(($refundStatus === 'success') ? 'Completed' : 'Failed').'</h3>'
				.'<p><strong>Booking Details:</strong></p>'
				.'<ul>'
				.'<li><strong>Booking ID:</strong> '.$bId.'</li>'
				.'<li><strong>MF Reference:</strong> '.$mfRef.'</li>'
				.'<li><strong>Customer:</strong> '.$contactName.'</li>'
				.'<li><strong>Customer Email:</strong> '.$email.'</li>'
				.'<li><strong>Refund Amount:</strong> USD '.$totalAmount.'</li>'
				.'<li><strong>Windcave Transaction:</strong> '.$txnId.'</li>'
				.'<li><strong>Status:</strong> '.ucfirst($refundStatus).'</li>';
			if ($refundStatus !== 'success') {
				$reason = $res['reason'] ?? 'Unknown';
				$adminContent .= '<li><strong>Failure Reason:</strong> '.$reason.'</li>';
			}
			$adminContent .= '</ul>';
			if ($refundStatus !== 'success') {
				$adminContent .= '<p style="color:#dc3545;"><strong>Action Required:</strong> Please review this failed refund and contact the customer or process manually.</p>';
			}
			$adminContent .= '</div>';
			
			$adminMessageData = $objBookCron->getEmailContent($adminContent);
			send_mail_smart($adminToemail, $adminSubject, $adminMessageData);
			$objBookCron->_writeLog('Admin payment refund notification sent to '.$adminToemail.' for booking '.$bId, 'searchPtrCron.txt');
		} catch (Exception $e) {
			$objBookCron->_writeLog('Admin notification failed: '.$e->getMessage(), 'searchPtrCron.txt');
		}
		
		// Mark as processed to avoid duplicate processing
		$paymentRefundEmailQueue[$bId]['processed'] = true;
	} catch (\Exception $e) {
		$objBookCron->_writeLog('ConsolidatedRefund failed: '.$e->getMessage(), 'searchPtrCron.txt');
	}
}

// Check for overdue PTRs and send alerts
$overduePTRs = $objBookCron->getOverduePTRs();
if (!empty($overduePTRs)) {
	$objBookCron->_writeLog('Found ' . count($overduePTRs) . ' overdue PTRs', 'searchPtrCron.txt');
	echo "<div style='background: #ffe6e6; padding: 10px; margin: 10px 0; border: 1px solid #ff0000;'>";
	echo "<h3 style='color: red;'>⚠️ OVERDUE PTRs DETECTED</h3>";
	
	foreach ($overduePTRs as $overdue) {
		echo "<div style='margin: 5px 0; padding: 5px; background: white;'>";
		echo "<strong>PTR ID:</strong> {$overdue['ptr_id']} | ";
		echo "<strong>MF Ref:</strong> {$overdue['mf_ref_num']} | ";
		echo "<strong>Type:</strong> {$overdue['ptr_type']} | ";
		echo "<strong>Elapsed:</strong> {$overdue['elapsed_minutes']} minutes | ";
		echo "<strong>SLA:</strong> {$overdue['sla_minutes']} minutes";
		echo "</div>";
		
		// Send alert for severely overdue PTRs (more than 4 hours)
		if ($overdue['elapsed_minutes'] > 240) {
			$objBookCron->sendStuckPTRAlert($overdue);
		}
	}
	echo "</div>";
}

$objBookCron->_writeLog('Cron job completed at '.date('l jS \of F Y h:i:s A'),'searchPtrCron.txt');
	
?>