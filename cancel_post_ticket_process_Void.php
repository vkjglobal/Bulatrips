<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  include_once('includes/dbConnect.php');
  include_once('includes/mock_mystifly.php');
  include_once('mail_send.php');
  $objCancel     =   new Cancel();
  
  // Check if this is an AJAX request
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  
  if ($isAjax) {
      // Handle AJAX JSON request
      $json = file_get_contents('php://input');
      $data = json_decode($json, true);
      
      if (!$data || !isset($data['booking_id'])) {
          echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
          exit;
      }
      
      $bookingId = intval($data['booking_id']);
      $passengerDetails = isset($data['passengerDetails']) ? $data['passengerDetails'] : [];
      // capture values from quote if present
      $finalRefundAmount = isset($data['final_refund_amount']) ? floatval($data['final_refund_amount']) : null;
      $currencyFromQuote = isset($data['currency']) ? $data['currency'] : 'USD';
      
      // Get booking details from database
      $bookingDetails = $objCancel->get_booking_details($bookingId);
      if (!$bookingDetails) {
          echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
          exit;
      }
      
      $mfreNum = $bookingDetails['mf_reference'];
      $userId = isset($bookingDetails['user_id']) ? intval($bookingDetails['user_id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0);
      
      // Debug log for AJAX request
      $objCancel->_writeLog("AJAX Void Request - Booking ID: $bookingId, MF Ref: $mfreNum, User ID: $userId", 'void.txt');
      $objCancel->_writeLog("Passenger Details: " . json_encode($passengerDetails), 'void.txt');
      
      // Get ALL passengers from database for AJAX request (not just selected ones)
      // This is required because Mystifly API doesn't support partial passenger void
      $bookCanusers_req = $objCancel->BookCancelUsers($bookingId, $userId);
      $passengersArray = array();
      foreach ($bookCanusers_req as $val) {
          // Only include ticketed passengers
          if (!empty($val['e_ticket_number'])) {
              // Normalize title format as requested by Mystifly
              $title = $val['title'];
              if (strtoupper($title) === 'MISS') {
                  $title = 'Ms';
              }
              
              $passengersArray[] = array(
                  "firstName" => $val['first_name'],
                  "lastName" => $val['last_name'],
                  "title" => $title,
                  "eTicket" => $val['e_ticket_number'],
                  "passengerType" => $val['passenger_type']
              );
          }
      }
      
  } else {
      // Handle traditional form request (backward compatibility)
   if (!isset($_POST['mfreNum'])){
       echo "Err1";exit;
   }

 $mfreNum   =  trim( $_POST['mfreNum']);
     $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);
     $void_eligible   =   trim($_POST['void_eligible']);
      
      // Process passengers for traditional request
      $bookCanusers_req = $objCancel->BookCancelUsers($bookingId, $userId);
      $passengersArray = array();
      foreach ($bookCanusers_req as $val) {
          // Only include ticketed passengers
          if (!empty($val['e_ticket_number'])) {
              // Normalize title format as requested by Mystifly
              $title = $val['title'];
              if (strtoupper($title) === 'MISS') {
                  $title = 'Ms';
              }
              
              $passengersArray[] = array(
                  "firstName" => $val['first_name'],
                  "lastName" => $val['last_name'],
                  "title" => $title,
                  "eTicket" => $val['e_ticket_number'],
                  "passengerType" => $val['passenger_type']
              );
          }
      }
  }
     
  // Escape and sanitize the data
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
  
  // Check if we have any ticketed passengers
  if (empty($passengersArray)) {
      $objCancel->_writeLog('No ticketed passengers found for void request', 'void.txt');
      echo json_encode([
          'status' => 'error',
          'message' => 'No ticketed passengers found in this booking. Void cannot be processed.',
          'error_type' => 'no_ticketed_passengers'
      ]);
      exit;
  }
  
  // Check for child passengers
  $childpsnger = isset($bookCanusers_req[0]['child_count']) ? $bookCanusers_req[0]['child_count'] : 0;
  $allow_child = ($childpsnger > 0);
  
  // Create request data for Void API
$requestData = array(
      'ptrType' => 'Void',
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => $allow_child,
      'passengers' => $passengersArray,
      'AdditionalNote' => 'Kindly void booking'
);

// Log the complete request data that will be sent to Mystifly
$objCancel->_writeLog('Void Request - Complete request data: ' . json_encode($requestData), 'void.txt');
  
  // Note: We don't include PTR ID in Void API call
  // PTR ID is only used for VoidQuote, not for actual Void
  
  // Check if we should use mock responses
  if (MOCK_MODE) {
      // Use mock response for development
      $mockResponse = MockMystifly::getVoidResponse('');
      $response = json_encode($mockResponse);
      $httpCode = 200;
      
      // Log mock usage
      $objCancel->_writeLog('MOCK MODE: Using mock Void response', 'void.txt');
  } else {
      // Call real Void API
      $endpoint = 'PostTicketingRequest';
      $result = $objCancel->callApi($endpoint, $requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
        
        // Check for Mystifly API errors
        if ($httpCode !== 200 || empty($response)) {
            $objCancel->_writeLog('Void API Error - HTTP Code: ' . $httpCode . ', Response: ' . $response, 'void.txt');
            echo json_encode([
                'status' => 'error', 
                'message' => 'Mystifly API is currently unavailable (HTTP ' . $httpCode . '). Please try again later.',
                'error_type' => 'api_unavailable'
            ]);
            exit;
        }
        
        // Check for 500 error in response body
        $tempData = json_decode($response, true);
        if (isset($tempData['Message']) && strpos($tempData['Message'], '500') !== false) {
            $objCancel->_writeLog('Mystifly Void 500 Error: ' . $tempData['Message'], 'void.txt');
            echo json_encode([
                'status' => 'error',
                'message' => 'Mystifly API is experiencing issues. Please try again in a few minutes.',
                'error_type' => 'mystifly_500_error'
            ]);
            exit;
        }
  }
  
  // Log the request and response
  $objCancel->_writeLog('Void Request Data: ' . json_encode($requestData), 'void.txt');
  $objCancel->_writeLog('Void HTTP Code: ' . $httpCode, 'void.txt');
  $objCancel->_writeLog('Void Raw API Response: ' . $response, 'void.txt');
         
        if ($response) {
            $responseData = json_decode($response, true);
    
      if (isset($responseData['Success']) && $responseData['Success']) {
          $PTRId = $responseData['Data']['PTRId'] ?? '';
          $PTRStatus = $responseData['Data']['PTRStatus'] ?? '';
          $SLAInMinutes = $responseData['Data']['SLAInMinutes'] ?? 120; // Default 2 hours if not provided
          
          // Calculate expected completion time in UTC
          $processingStartedUTC = gmdate('d M Y, H:i') . ' UTC';
          $expectedCompletionUTC = gmdate('d M Y, H:i', time() + ($SLAInMinutes * 60)) . ' UTC';
          
          // build message using quote amount if we have it
          $amountText = '';
          if (!is_null($finalRefundAmount)) {
              $amountText = $currencyFromQuote . ' ' . number_format($finalRefundAmount, 2);
          } elseif (isset($responseData['Data']['TotalRefundAmount'])) {
              $amountText = ($responseData['Data']['Currency'] ?? 'USD') . ' ' . number_format($responseData['Data']['TotalRefundAmount'], 2);
          }
          $message = "Your Cancellation is: " . $PTRStatus . (strlen($amountText) ? " Total Refundable Amount is: " . $amountText : '');
          
          // Record PTR in cancel_booking for each passenger so cron can pick it up
          foreach ($passengerDetails as $passenger) {
              // Get traveller_id from the passenger's eticket
              $travellerId = 0;
              try {
                  $travellerStmt = $conn->prepare("SELECT id FROM travellers_details WHERE e_ticket_number = ? AND flight_booking_id = ?");
                  $travellerStmt->execute([$passenger['eticket'], $bookingId]);
                  $travellerRow = $travellerStmt->fetch(PDO::FETCH_ASSOC);
                  if ($travellerRow) {
                      $travellerId = intval($travellerRow['id']);
                  }
              } catch (Exception $e) {
                  $objCancel->_writeLog("Error getting traveller_id for eticket {$passenger['eticket']}: " . $e->getMessage(), 'void.txt');
              }
              
              $objCancel->_writeLog("Attempting to insert into cancel_booking for booking ID: $bookingId, User ID: $userId, Traveller ID: $travellerId, Eticket: {$passenger['eticket']}", 'void.txt');
              
              try {
                  // sanitize values to match DB types
                  $userId = intval($userId);
                  $ptrIdForDb = (string)$PTRId; // ptr_id column is VARCHAR(64), keep as string
                  $precancelsts = 'post';
                  $errorCode = '';
                  $traceId = '';
                  $ptrType = 'Void';
                  $SLAInMinutesForDb = $SLAInMinutes; // Use actual SLA from API response
                  $VoidingWindow = '';
                  $AdminCharges = 0;
                  $GSTCharge = 0;
                  $TotalVoidingFee = 0.00;
                  $TotalRefundAmount = !is_null($finalRefundAmount) ? $finalRefundAmount : floatval($responseData['Data']['TotalRefundAmount'] ?? 0);
                  $Currency = !empty($currencyFromQuote) ? $currencyFromQuote : ($responseData['Data']['Currency'] ?? 'USD');
                  $cancel_status = 0;

                  $insertId = $objCancel->insCncelSts(
                      $bookingId,
                      $userId,
                      $precancelsts,
                      $errorCode,
                      $mfreNum,
                      $traceId,
                      $httpCode,
                      $ptrIdForDb,
                      $ptrType,
                      $SLAInMinutesForDb, // Use actual SLA from API response
                      $PTRStatus,
                      $VoidingWindow,
                      $passenger['eticket'], // ticket_num for this specific passenger
                      $AdminCharges,
                      $GSTCharge,
                      $TotalVoidingFee,
                      $TotalRefundAmount,
                      $Currency,
                      $cancel_status,
                      $message,
                      $travellerId // Add traveller_id parameter
                  );

                  $objCancel->_writeLog("Insert into cancel_booking result for traveller $travellerId: " . var_export($insertId, true), 'void.txt');

                  // Fallback minimal insert if helper failed
                  if (!$insertId) {
                      $sql = "INSERT INTO cancel_booking (user_agent_id, booking_id, pre_post_ticket_status, mf_ref_num, err_code, trace_id, http_code_response, ptr_id, ptr_type, ptr_status, traveller_id, ticket_number, total_void_fee, total_refund_amount, cancel_status, created_date, void_window, sla_minutes, admin_charge, gst_charge, currency, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";
                      $stmt = $conn->prepare($sql);
                      $stmt->execute([
                          $userId,
                          $bookingId,
                          $precancelsts,
                          $mfreNum,
                          '', // err_code
                          '', // trace_id
                          $httpCode,
                          $ptrIdForDb,
                          $ptrType,
                          $PTRStatus,
                          $travellerId,
                          $passenger['eticket'],
                          0.00, // total_void_fee
                          $TotalRefundAmount,
                          $Currency,
                          $cancel_status,
                          '', // created_date handled by NOW()
                          '', // void_window
                          $SLAInMinutesForDb, // Use actual SLA from API response
                          0, // admin_charge
                          0, // gst_charge
                          $Currency,
                          $message
                      ]);
                      $objCancel->_writeLog('Fallback insert executed for traveller ' . $travellerId . '; rows: '.$stmt->rowCount(), 'void.txt');
                  }
              } catch (Exception $e) {
                  $objCancel->_writeLog('insert cancel_booking failed for traveller ' . $travellerId . ': '.$e->getMessage(), 'void.txt');
              }
          }

          // Update void status in database for selected passengers
          if ($isAjax && !empty($passengerDetails)) {
              $objCancel->_writeLog("Updating void status for " . count($passengerDetails) . " passengers", 'void.txt');
              foreach ($passengerDetails as $passenger) {
                  $updateQuery = 'UPDATE travellers_details SET void_status = :void_status, ptr_id = :ptr_id WHERE e_ticket_number = :eticket AND flight_booking_id = :booking_id';
                  $stmt = $conn->prepare($updateQuery);
                  $updateData = [
                      'void_status' => 'InProcess',
                      'ptr_id' => $PTRId,
                      'eticket' => $passenger['eticket'],
                      'booking_id' => $bookingId
                  ];
                  $objCancel->_writeLog("Update query: " . $updateQuery, 'void.txt');
                  $objCancel->_writeLog("Update data: " . json_encode($updateData), 'void.txt');
                  $result = $stmt->execute($updateData);
                  $objCancel->_writeLog("Update result: " . ($result ? 'success' : 'failed') . ", rows affected: " . $stmt->rowCount(), 'void.txt');
              }
          }
          // Send immediate IN-PROCESS email (informational)
          try {
              // Fetch contact email and MF reference directly
              $contactStmt = $conn->prepare("SELECT contact_email, mf_reference, contact_first_name, contact_last_name FROM temp_booking WHERE id = :id");
              $contactStmt->execute(['id' => (int)$bookingId]);
              $contact = $contactStmt->fetch(PDO::FETCH_ASSOC) ?: [];
              $recipient = $contact['contact_email'] ?? '';
              $mfRef = $contact['mf_reference'] ?? $mfreNum;
              $nameRow = $objCancel->getLisQuery("SELECT contact_first_name, contact_last_name FROM temp_booking WHERE id = ".(int)$bookingId." LIMIT 1");
              $contactName = 'Customer';
              if (!empty($nameRow)) {
                  $fn = trim($nameRow[0]['contact_first_name'] ?? '');
                  $ln = trim($nameRow[0]['contact_last_name'] ?? '');
                  $full = trim($fn.' '.$ln);
                  if ($full !== '') { $contactName = $full; }
              }
              $subject = 'Flight Cancellation - In Process';
              $headerBar = 'Cancellation Update';
              // Build rows for selected passengers
              $rows = '';
              foreach ($passengerDetails as $passenger) {
                  $rows .= '<tr>'
                      .'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($passenger['title'].' '.$passenger['firstname'].' '.$passenger['lastname']).'</td>'
                      .'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars((string)$PTRId).'</td>'
                      .'<td style="padding:8px 12px;border-bottom:1px solid #eee;">'.htmlspecialchars($PTRStatus).'</td>'
                      .'</tr>';
              }
              $amountDisp = !is_null($finalRefundAmount) ? ($currencyFromQuote.' '.number_format((float)$finalRefundAmount,2)) : '';
              $amountLine = $amountDisp !== '' ? '<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">Total Amount:</span> <span>'.$amountDisp.'</span></div>' : '';
              
              // Add SLA timeline information
              $slaHours = round($SLAInMinutes / 60, 1);
              $slaInfoBox = '<div style="background:#e7f3ff; border-left:4px solid #0d6efd; padding:15px; margin:15px 0; border-radius:4px;">'
                  .'<p style="margin:0 0 8px 0; font-weight:bold; color:#084298;">⏰ Processing Timeline</p>'
                  .'<div style="margin:0 0 6px 0; font-size:14px; color:#084298;"><strong>Processing Started:</strong> '.htmlspecialchars($processingStartedUTC).'</div>'
                  .'<div style="margin:0 0 6px 0; font-size:14px; color:#084298;"><strong>Expected Completion:</strong> '.htmlspecialchars($expectedCompletionUTC).'</div>'
                  .'<div style="margin:0 0 6px 0; font-size:14px; color:#084298;"><strong>Processing Time:</strong> Up to '.$slaHours.' hours</div>'
                  .'<p style="margin:8px 0 0 0; font-size:13px; color:#666; font-style:italic;">Note: Times shown in UTC (Universal Time). Please adjust for your local timezone.</p>'
                  .'</div>';
              
              $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
                .'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
                .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
                .'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
                .'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">'.$headerBar.'</td></tr>'
                .'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
                .'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
                .'<p style="margin:0 0 18px 0;">Your Void request has been <strong>'.htmlspecialchars($PTRStatus).'</strong>.</p>'
                .'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
                .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($mfRef).'</span></div>'
                .$amountLine
                .'</div>'
                .$slaInfoBox
                .'<div style="margin-top:16px;"><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
                .'<thead><tr><th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Passenger</th><th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">PTR ID</th><th align="left" style="padding:8px 12px;border-bottom:2px solid #0029ff;">Status</th></tr></thead>'
                .'<tbody>'.$rows.'</tbody></table></div>'
                .'<p style="margin:18px 0 0 0;color:#555555;">We will notify you by email once the airline completes processing.</p>'
                .'</td></tr></table></body></html>';
              if (!empty($recipient)) {
                  sendMail($recipient, $subject, $emailHtml);
              }
          } catch (Exception $e) {
              $objCancel->_writeLog('In-process email failed: '.$e->getMessage(), 'void.txt');
          }
 
                               $response_New = array(
              'status' => 'success',
                    'message' => $message,
                    'ptr_id' => $PTRId,
			 'ptr_status' => $PTRStatus,
              'mf_ref' => $mfreNum,
              'sla_minutes' => $SLAInMinutes,
              'expected_completion_utc' => $expectedCompletionUTC,
              'processing_started_utc' => $processingStartedUTC,
              'estimated_completion_hours' => round($SLAInMinutes / 60, 1)
          );
          
      } else {
          // Handle errors - show raw Mystifly error message
          $message = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error occurred';
          $response_New = array(
              'status' => 'error',
              'message' => $message,
              'raw_response' => $responseData, // Include full response for debugging
              'raw_request' => $requestData // Include full request for debugging
          );
      }
  } else {
                         $response_New = array(
          'status' => 'error',
          'message' => 'No response from Void API'
                        );
            }

  $objCancel->_writeLog('Final response: ' . json_encode($response_New), 'void.txt');

  // Return JSON response for AJAX requests
  if ($isAjax) {
echo json_encode($response_New);
exit;
  }
  
  // For traditional form requests, redirect or show message
  if (isset($response_New['status']) && $response_New['status'] === 'success') {
      header('Location: user-dashboard.php?message=void_success');
      exit;
  } else {
      echo "Error: " . ($response_New['message'] ?? 'Unknown error occurred');
      exit;
   }
?>