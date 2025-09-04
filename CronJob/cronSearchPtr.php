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
		
		$bookingId =    $resultBookingdata['id'];
		$userId =    $resultBookingdata['user_agent_id'];
		$ptr_id =   $resultBookingdata['ptr_id'];
		$mfreNum = $resultBookingdata['mf_ref_num'];
		$totalRefundAmount = $resultBookingdata['total_refund_amount'];
		$ticketNum = $resultBookingdata['ticket_number'];
		$createdDate = isset($resultBookingdata['created_date']) ? strtotime($resultBookingdata['created_date']) : null;
		$elapsedSeconds = $createdDate ? (time() - $createdDate) : 0;
	   
		// In MOCK_MODE (or when PTRId is 0), auto-complete after a short delay to simulate Mystifly fulfillment
		if ((defined('MOCK_MODE') && MOCK_MODE) || intval($ptr_id) === 0) {
			// Immediately mark as completed locally in mock or unknown-ptr scenarios
			$objBookCron->_writeLog("MOCK auto-complete for PTR $ptr_id (ticket $ticketNum, MF $mfreNum)", 'searchPtrCron.txt');
			$objBookCron->updateInDB_cancelbooking('cancel_booking', $ticketNum);
			$objBookCron->updateInDB_trav('travellers_details', $ticketNum);

			// If no more ticketed passengers for this booking, close the booking
			$count_ticketed_temp = $objBookCron->count_ticketed__temp_book('travellers_details', $bookingId);
			if ($count_ticketed_temp == 0) {
				$objBookCron->updateInDB_temp_book('temp_booking', $mfreNum);
			}

			echo "<div style='color: green;'><strong>PTR $ptr_id COMPLETED (MOCK):</strong> Updated traveller, cancel_booking and booking status.</div>";
			// Skip real API call for mock
			continue;
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

			// Handle "No records found" response - check if PTR is stuck
			if (isset($responseData['Message']) && $responseData['Message'] == 'No records found.') {
				$objBookCron->_writeLog("No records found for PTR $ptr_id - checking if stuck", 'searchPtrCron.txt');
				
				// Check if PTR is beyond SLA and mark as failed
				$slaMinutes = isset($resultBookingdata['sla_minutes']) ? $resultBookingdata['sla_minutes'] : 120;
				$isStuck = $objBookCron->handleStuckPTR($bookingId, $ptr_id, $mfreNum, $resultBookingdata['ptr_type'], $slaMinutes);
				
				if ($isStuck) {
					$objBookCron->_writeLog("PTR $ptr_id marked as failed due to being stuck beyond SLA", 'searchPtrCron.txt');
					echo "<div style='color: red;'><strong>PTR $ptr_id FAILED:</strong> Stuck beyond SLA - marked as failed</div>";
				} else {
					$objBookCron->_writeLog("PTR $ptr_id still within acceptable timeframe", 'searchPtrCron.txt');
					echo "<div style='color: orange;'><strong>PTR $ptr_id WAITING:</strong> Still within acceptable timeframe</div>";
				}
				
				continue; // Skip to next PTR
			}

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
				
					$success_can_sts    =0;
					foreach($responseData['Data']['PTRDetail'][0]['pTRPaxDetails'] as $k => $val){
						$pax_booking_id_transaction =   $val['Id'];
						$PaxId =   $val['PaxId'];
						$TicketStatus =   $val['TicketStatus'];
						$is_active_booking_status =   $val['IsActive'];                
						$ticket_num =   $val['TicketNumber']; 
					   
						if(($PTRStatus == "Completed") && ($Resolution == $resultBookingdata['ptr_type']."ed")){
							//cancellation success
							
							$update_cancelBooking_status      =    $objBookCron->updateInDB_cancelbooking('cancel_booking',$ticket_num);
							$update_TravellerB_result      =    $objBookCron->updateInDB_trav('travellers_details',$ticket_num);
							$cancel_status    =1;

							// Send completion email to booking contact
							$contact = $objBookCron->getBookingContactEmail($bookingId);
							$email = isset($contact['contact_email']) ? trim($contact['contact_email']) : '';
							$mfRefForEmail = isset($contact['mf_reference']) ? $contact['mf_reference'] : $mfreNum;
							if (!empty($email)) {
								$ptrTypeDisplay = ($resultBookingdata['ptr_type'] == 'Void') ? 'voided' : 'refunded';
								$subject = ($resultBookingdata['ptr_type'] == 'Void') ? 'Flight Cancellation – Void Completed' : 'Flight Refund – Completed';
								$content = '<p>Dear Customer,</p>'.
										   '<p>Your request has been successfully completed.</p>'.
										   '<p><strong>Booking Details:</strong></p>'.
										   '<ul>'.
										   '<li>Booking ID: '.$bookingId.'</li>'.
										   '<li>MyFareBox Reference: '.$mfRefForEmail.'</li>'.
										   '<li>Request: '.$resultBookingdata['ptr_type'].' ('.$ptrTypeDisplay.')</li>'.
										   '<li>Amount: '.$Currency.' '.$TotalRefundAmount.'</li>'.
										   '</ul>';
								if ($resultBookingdata['ptr_type'] == 'Refund') {
									$content .= '<p>The refund will be returned to your original payment method within 7–14 business days.</p>';
								}
								$content .= '<p>Thank you for choosing Bulatrips.</p>';

								$messageData = $objBookCron->getEmailContent($content);
								$sent = send_mail_smart($email, $subject, $messageData);
								$objBookCron->_writeLog('Email attempt to '.$email.' subject='.$subject.' sent='.(int)$sent, 'searchPtrCron.txt');
							}
							
							// Send notification to admin
							$adminSubject = "PTR Completed - Booking #".$bookingId;
							$userDetails = $usersObj->getUserDetails((int)$userId) ?: [];
							$name = '';
							if (!empty($userDetails)) {
								$first = isset($userDetails['first_name']) ? $userDetails['first_name'] : '';
								$last = isset($userDetails['last_name']) ? $userDetails['last_name'] : '';
								$name = trim($first.' '.$last);
							}
							if ($name === '') { $name = 'User #'.(int)$userId; }
							$userEmailForAdmin = isset($userDetails['email']) ? $userDetails['email'] : '';
							$adminContent = '<p>A PTR request has been completed:</p>
							               <p><strong>Details:</strong></p>
							               <ul>
							                   <li>Booking ID: '.$bookingId.'</li>
							                   <li>User: '.$name.' ('.$userEmailForAdmin.')</li>
							                   <li>PTR Type: '.$resultBookingdata['ptr_type'].'</li>
							                   <li>Status: Completed</li>
							                   <li>Refund Amount: '.$Currency.' '.$TotalRefundAmount.'</li>
							               </ul>';
							
							$adminMessageData = $objBookCron->getEmailContent($adminContent);
							sendMail($adminToemail, $adminSubject, $adminMessageData);
					
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