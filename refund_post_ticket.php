<?php
  // Enable error reporting for debugging
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  include_once('includes/mock_mystifly.php');
  $objCancel     =   new Cancel();
  
  // Set JSON header for all responses
  header('Content-Type: application/json');
  
  // Check if this is an AJAX request
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
      // Handle AJAX JSON request
      $json = file_get_contents('php://input');
      $data = json_decode($json, true);
      
      if (!$data || !isset($data['booking_id'])) {
          echo json_encode(['success' => false, 'message' => 'Invalid request data']);
          exit;
      }
      
      $bookingId = intval($data['booking_id']);
      $passengerIds = isset($data['passengers']) ? $data['passengers'] : [];
      
      // Get booking details from database
      $bookingDetails = $objCancel->get_booking_details($bookingId);
      if (!$bookingDetails) {
          echo json_encode(['success' => false, 'message' => 'Booking not found']);
          exit;
      }
      
      $mfreNum = $bookingDetails['mf_reference'];
      $userId = $bookingDetails['user_id'];
      
      // Debug log for AJAX request
      $objCancel->_writeLog("AJAX Request - Booking ID: $bookingId, MF Ref: $mfreNum, User ID: $userId", 'RefundQuote.txt');
      $objCancel->_writeLog("AJAX Request - Raw input: " . $json, 'RefundQuote.txt');
      
  } else {
      // Handle traditional form request (backward compatibility)
      if (!isset($_POST['mfreNum'])){
          echo "Err1";exit;
      }
      
      $mfreNum   =  trim( $_POST['mfreNum']);
      $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);
  }
    // $void_eligible   =   trim($_POST['void_eligible']);
     //=======
     
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
   // $void_eligible = filter_var($void_eligible, FILTER_SANITIZE_NUMBER_INT);
     //=======
     //echo "LLL".$mfreNum;exit;

     $bookCanusers_req      =   $objCancel->BookCancelUsers($bookingId,$userId);
     
     // Debug: Log database query results
     $objCancel->_writeLog("Database query result: " . print_r($bookCanusers_req, true), 'RefundQuote.txt'); 
         
     //============request body for entire booking cancel============================================
     
     // Debug: Log received passengers data
     $objCancel->_writeLog('Received passengers data from DB: ' . print_r($bookCanusers_req, true), 'RefundQuote.txt');
     
     // Check if we got passenger data
     if (empty($bookCanusers_req)) {
         $response_New = array(
             'status' => 'error',
             'code' => 'NO_PASSENGER_DATA',
             'message' => 'No passenger data found for this booking. Please contact support.'
         );
         echo json_encode($response_New);
         exit;
     }
     
     // Check void window eligibility and choose best quote option
     $firstPassenger = $bookCanusers_req[0];
     $voidWindow = $firstPassenger['void_window'];
     $currentDateTime = new DateTime();
     $useVoidQuote = false;
     $useRefundQuote = false;
     
     if (!empty($voidWindow)) {
         $voidWindowDateTime = new DateTime($voidWindow);
         $objCancel->_writeLog('Void Window: ' . $voidWindow . ', Current Time: ' . $currentDateTime->format('Y-m-d H:i:s'), 'RefundQuote.txt');
         
         // Check if void window is still active
         if ($currentDateTime <= $voidWindowDateTime) {
             $objCancel->_writeLog('Void window active - using VoidQuote (best option for customer)', 'RefundQuote.txt');
             $useVoidQuote = true;
         } else {
             $objCancel->_writeLog('Void window expired - using RefundQuote (get quote first)', 'RefundQuote.txt');
             $useRefundQuote = true;
         }
     } else {
         // No void window info, default to RefundQuote
         $objCancel->_writeLog('No void window info - using RefundQuote', 'RefundQuote.txt');
         $useRefundQuote = true;
     }
     
     // Initialize the main passengers array
     $passengersArray = array();
     
     // Always get ALL ticketed passengers from database (not just selected ones)
     // This is required because Mystifly API doesn't support partial passenger refund
     // Note: We ignore frontend passenger data and use database data for consistency
     foreach ($bookCanusers_req as $k => $val) {
         // Only include ticketed passengers
         if (!empty($val['e_ticket_number'])) {
             // Normalize title format as requested by Mystifly
             $title = $val['title'];
             if (strtoupper($title) === 'MISS') {
                 $title = 'Ms';
             }
             
             $passengerData = array(
                 "firstName" => $val['first_name'],
                 "lastName" => $val['last_name'],
                 "title" => $title,
                 "eTicket" => $val['e_ticket_number'],
                 "passengerType" => $val['passenger_type']
             );
             $passengersArray[] = $passengerData;
         }
     }
     
     // Check if we have any ticketed passengers
     if (empty($passengersArray)) {
         $objCancel->_writeLog('No ticketed passengers found for refund request', 'RefundQuote.txt');
         echo json_encode([
             'status' => 'error',
             'message' => 'No ticketed passengers found in this booking. Refund cannot be processed.',
             'error_type' => 'no_ticketed_passengers'
         ]);
         exit;
     }
     
     // Duplicate request prevention - Check if RefundQuote already pending
     try {
         $duplicateCheck = $objCancel->getLisQuery("SELECT id, ptr_id, created_date FROM cancel_booking WHERE booking_id = ".(int)$bookingId." AND ptr_type = 'RefundQuote' AND ptr_status = 'InProcess' AND (message IS NULL OR message = '' OR message NOT LIKE '%Quote emailed%') ORDER BY id DESC LIMIT 1");
         
         if (!empty($duplicateCheck)) {
             $existingPtrId = $duplicateCheck[0]['ptr_id'];
             $createdTime = $duplicateCheck[0]['created_date'];
             $createdTimeFormatted = date('d M Y, H:i', strtotime($createdTime));
             
             $objCancel->_writeLog('Duplicate RefundQuote request blocked - Existing PTR: '.$existingPtrId, 'RefundQuote.txt');
             
             echo json_encode([
                 'success' => false,
                 'status' => 'duplicate_request',
                 'message' => 'A refund quote request is already in process for this booking.',
                 'error_type' => 'duplicate_refund_quote',
                 'existing_ptr_id' => $existingPtrId,
                 'submitted_at' => $createdTimeFormatted,
                 'note' => 'Please check your email or wait for the quote to be processed. You can also check your booking status.'
             ]);
             exit;
         }
     } catch (Exception $e) {
         $objCancel->_writeLog('Duplicate check error: '.$e->getMessage(), 'RefundQuote.txt');
         // Continue with request if check fails
     }
     
     // Debug: Log final passengers array
     $objCancel->_writeLog('Final passengers array: ' . print_r($passengersArray, true), 'RefundQuote.txt');

     // Check if any child passengers exist
     $hasChildPassenger = false;
     foreach ($passengersArray as $passenger) {
         if ($passenger['passengerType'] === 'CHD' || $passenger['passengerType'] === 'INF') {
             $hasChildPassenger = true;
             break;
         }
     }
     
    // For example, creating the main request body array (following exact API documentation format)
    
    // Use smart selection based on void window
    if ($useVoidQuote) {
        $ptrType = 'VoidQuote';
        $additionalNote = 'Void quote request - within void window';
    } else {
        $ptrType = 'RefundQuote';
        $additionalNote = 'Refund quote request - void window expired';
    }
     
    $requestData = array(
         'ptrType' => $ptrType,
         'mFRef' => $mfreNum,
         'AllowChildPassenger' => $hasChildPassenger,
         'passengers' => $passengersArray,
         'AdditionalNote' => $additionalNote
     );

    // Prepare a safe default response container to avoid returning null
    $response_New = array(
        'success' => false,
        'status' => 'error',
        'message' => '',
        'http_code' => null,
        'raw_request' => $requestData,
        'raw_response' => null
    );

     // Add debug logging
     $objCancel->_writeLog('Smart Selection: Using ' . $ptrType . ' API', 'RefundQuote.txt');
     $objCancel->_writeLog('Reason: User requested refund quote', 'RefundQuote.txt');
     $objCancel->_writeLog('API Request: ' . json_encode($requestData), 'RefundQuote.txt');
     $objCancel->_writeLog('Total passengers being sent: ' . count($passengersArray), 'RefundQuote.txt');

     $endpoint   =   'PostTicketingRequest';
     
     // Check if we should use mock responses
     if (MOCK_MODE) {
         // Use mock response for development
         if ($ptrType === 'VoidQuote') {
             $mockResponse = MockMystifly::getVoidQuoteResponse($passengersArray);
         } else {
             $mockResponse = MockMystifly::getRefundQuoteResponse($passengersArray);
         }
         $response = json_encode($mockResponse);
         $httpCode = 200;
         
         // Log mock usage
         $objCancel->_writeLog('MOCK MODE: Using mock ' . $ptrType . ' response', 'RefundQuote.txt');
     } else {
         // Call real API
         $result = $objCancel->callApi($endpoint,$requestData);
         $httpCode = $result['httpCode'];
         $response = $result['responseData'];
         $curlError = isset($result['curlError']) ? $result['curlError'] : '';
         
         // Check for Mystifly API 500 errors and handle gracefully
        if ($httpCode !== 200 || empty($response)) {
            $objCancel->_writeLog('API Error - HTTP Code: ' . $httpCode . ', Response: ' . $response, 'RefundQuote.txt');
            
            // Build error message
            $errorMessage = 'Mystifly API is currently unavailable (HTTP ' . $httpCode . ').';
            if (!empty($curlError)) {
                $errorMessage .= ' Connection Error: ' . $curlError;
            }
            $errorMessage .= ' Please try again later or contact support.';
            
            $response_New['message'] = $errorMessage;
            $response_New['error_type'] = 'api_unavailable';
            $response_New['http_code'] = $httpCode;
            $response_New['raw_response'] = $response;
            $response_New['curl_error'] = $curlError;
            echo json_encode($response_New);
            exit;
        }
         
         // Check for 500 error in response body
         $responseData = json_decode($response, true);
        if (isset($responseData['Message']) && strpos($responseData['Message'], '500') !== false) {
            $objCancel->_writeLog('Mystifly 500 Error: ' . $responseData['Message'], 'RefundQuote.txt');
            $response_New['message'] = $responseData['Message'];
            $response_New['error_type'] = 'mystifly_500_error';
            $response_New['http_code'] = $httpCode;
            $response_New['raw_response'] = $response;
            echo json_encode($response_New);
            exit;
        }
     }

     // Add detailed debug logging
     $objCancel->_writeLog('HTTP Code: ' . $httpCode, 'RefundQuote.txt');
     $objCancel->_writeLog('Raw API Response: ' . $response, 'RefundQuote.txt');
     
     // Log passenger data from DB for comparison
     $objCancel->_writeLog('DB Passenger Data: ' . print_r($bookCanusers_req[0], true), 'RefundQuote.txt');
  
        // Send the API request
    //****************************************************************
                                      /*              $response    =     '{
                                                        "Success": true,
                                                          "Data": {
                                                            "PTRId": 10669,
                                                            "PTRType": "RefundQuote",
                                                            "MFRef": "MF23731023",
                                                            "SLAInMinutes": 0,
                                                            "PTRStatus": "Completed",
                                                            "RefundQuotes": [
                                                              {
                                                                "FirstName": "AVIKASH",
                                                                "LastName": "AVIKASH",
                                                                "Title": "MR",
                                                                "PassengerType": "ADT",
                                                                "ETicket": "2289699426651",
                                                                "TotalFare": 43.44,
                                                                "UnusedFare": 43.44,
                                                                "CancellationCharge": 42.69,
                                                                "NoShowCharge": 0,
                                                                "Tax": 0,
                                                                "AdminCharges": 0,
                                                                "GSTCharge": 0,
                                                                "TotalRefundCharges": 42.69,
                                                                "TotalRefundAmount": 0.75,
                                                                "Currency": "USD",
                                                                "YQ_Tax": 0,
                                                                "YR_Tax": 0,
                                                                "OtherTaxesK3": 0,
                                                                "ExtraServiceCharge": 0
                                                              }
                                                            ],
                                                            "Message": "Request for refund quote has been submitted successfully. Your Request# is 10669."
                                                          }
                                                                                }';     */
                        
         
        if ($response) {
            $responseData = json_decode($response, true);
            
            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response_New['status'] = 'error';
                $response_New['code'] = 'INVALID_RESPONSE';
                $response_New['message'] = 'Invalid response from server. Please try again.';
                $response_New['http_code'] = isset($httpCode) ? $httpCode : null;
                $response_New['raw_response'] = $response;
                echo json_encode($response_New);
                exit;
            }
            
            // Check if response has error message indicating we should try RefundQuote as fallback
            if ($useVoidQuote && !empty($responseData['Message']) && 
                (strpos($responseData['Message'], 'voiding window expired') !== false || 
                 strpos($responseData['Message'], 'details are missing') !== false ||
                 strpos($responseData['Message'], 'not eligible') !== false)) {
                
                $objCancel->_writeLog('VoidQuote failed, trying RefundQuote as fallback', 'RefundQuote.txt');
                
                // Try RefundQuote as fallback
                $refundQuoteData = array(
                    'ptrType' => 'RefundQuote',
                    'mFRef' => $mfreNum,
                    'AllowChildPassenger' => $hasChildPassenger,
                    'passengers' => array(
                        array(
                            'firstName' => $passengersArray[0]['firstName'],
                            'lastName' => $passengersArray[0]['lastName'],
                            'title' => $passengersArray[0]['title'],
                            'eTicket' => $passengersArray[0]['eTicket'],
                            'passengerType' => $passengersArray[0]['passengerType']
                        )
                    ),
                    'AdditionalNote' => 'Refund quote request - VoidQuote failed'
                );
                
                $objCancel->_writeLog('Fallback RefundQuote Request: ' . json_encode($refundQuoteData), 'RefundQuote.txt');
                
                // Check if we should use mock responses for fallback
                if (MOCK_MODE) {
                    // Use mock response for development
                    $mockResponse = MockMystifly::getRefundQuoteResponse($passengersArray);
                    $fallbackResponse = json_encode($mockResponse);
                    $fallbackHttpCode = 200;
                    
                    // Log mock usage
                    $objCancel->_writeLog('MOCK MODE: Using mock RefundQuote fallback response', 'RefundQuote.txt');
                } else {
                    // Call real API
                    $fallbackResult = $objCancel->callApi($endpoint, $refundQuoteData);
                    $fallbackHttpCode = $fallbackResult['httpCode'];
                    $fallbackResponse = $fallbackResult['responseData'];
                }
                
                if ($fallbackResponse) {
                    $fallbackResponseData = json_decode($fallbackResponse, true);
                    $objCancel->_writeLog('Fallback RefundQuote Response: ' . $fallbackResponse, 'RefundQuote.txt');
                    
                    if (isset($fallbackResponseData['Success']) && $fallbackResponseData['Success']) {
                        // RefundQuote successful
                        $PTRId = $fallbackResponseData['Data']['PTRId'];
                        $PTRType = $fallbackResponseData['Data']['PTRType'];
                        $SLAInMinutes = $fallbackResponseData['Data']['SLAInMinutes'];
                        $PTRStatus = $fallbackResponseData['Data']['PTRStatus'];
                        
                        $hours = $objCancel->calculateHoursFromSLAMinutes($SLAInMinutes);
                        
                        $response_New = array(
                            'success' => true,
                            'message' => '📋 Refund quote requested successfully (Void was not available)',
                            'refund_type' => 'refund_quote_fallback',
                            'data' => [
                                'ptrId' => $PTRId,
                                'ptrType' => $PTRType,
                                'ptrStatus' => $PTRStatus,
                                'mfRef' => $mfreNum,
                                'slaMinutes' => $SLAInMinutes,
                                'slaHours' => $hours,
                                'note' => 'Refund quote process initiated. You will receive quote details to review and accept.',
                                'apiMessage' => isset($fallbackResponseData['Data']['Message']) ? $fallbackResponseData['Data']['Message'] : ''
                            ]
                        );
                        echo json_encode($response_New);
                        exit;
                    }
                }
            }
            
            // Only treat as error if API explicitly returned Success=false with a message
            if (isset($responseData['Success']) && $responseData['Success'] === false && !empty($responseData['Message'])) {
                $errorMessage = $responseData['Message'];
                
                // Check for specific error messages
                if (strpos($errorMessage, 'refund details are missing') !== false) {
                    $response_New = array(
                        'status' => 'manual_required',
                        'code' => 'REFUND_DETAILS_MISSING',
                        'title' => 'Manual Refund Required',
                        'message' => 'This booking requires manual refund processing due to expired void window and airline policies.',
                        'action_required' => 'We will process your refund manually within 2-3 business days.',
                        'next_steps' => [
                            'Your refund request has been submitted to our support team',
                            'We will contact the airline directly for refund processing',
                            'You will receive email updates on the refund status',
                            'Expected processing time: 2-3 business days'
                        ],
                        'support_info' => [
                            'email' => 'support@bulatrips.com',
                            'phone' => '+1-XXX-XXX-XXXX',
                            'reference' => $mfreNum
                        ],
                        'raw_request' => $requestData,
                        'raw_response' => $response,
                        'debug_info' => [
                            'api_message' => $errorMessage,
                            'mf_reference' => $mfreNum,
                            'booking_id' => $bookingId,
                            'ticket_status' => $bookCanusers_req[0]['ticket_status'],
                            'void_window' => $bookCanusers_req[0]['void_window'],
                            'reason' => 'Void window expired - Manual processing required'
                        ]
                    );
                    
                    // Store manual refund request in database for admin tracking
                    $objCancel->insCncelSts(
                        $bookingId,
                        $userId,
                        'manual', // pre_post_ticket_status
                        'REFUND_DETAILS_MISSING', // error code
                        $mfreNum,
                        '', // trace_id
                        $httpCode,
                        null, // PTR_ID
                        'ManualRefund', // PTR_Type
                        0, // SLA_Minutes
                        'Pending', // PTR_Status
                        $bookCanusers_req[0]['void_window'],
                        $passengersArray[0]['eTicket'],
                        '', // admin_charges
                        '', // gst_charge
                        '', // total_void_fee
                        '', // total_refund_amount
                        '', // currency
                        0, // cancel_status (pending)
                        'Manual refund required - Void window expired'
                    );
                } else if (strpos($errorMessage, 'already in process') !== false) {
                    $response_New = array(
                        'status' => 'error',
                        'code' => 'ALREADY_IN_PROCESS',
                        'message' => 'A refund request for this booking is already being processed. Please check your booking status or contact support for updates.',
                        'raw_request' => $requestData,
                        'raw_response' => $response,
                        'debug_info' => [
                            'api_message' => $errorMessage,
                            'mf_reference' => $mfreNum
                        ]
                    );
                } else {
                    $response_New = array(
                        'status' => 'error',
                        'code' => 'API_ERROR',
                        'message' => $errorMessage,
                        'raw_request' => $requestData,
                        'raw_response' => $response
                    );
                }
                echo json_encode($response_New);
                exit;
            }
        }
        $logRes =   print_r($responseData, true);
         $logReQ =   print_r($requestData, true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','RefundQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'RefundQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'RefundQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'RefundQuote.txt');
                              $objCancel->_writeLog('Request Received\n'.$logReQ,'RefundQuote.txt');
        $objCancel->_writeLog('REsponse Received\n'.$logRes,'RefundQuote.txt');
 
                        //write log
//    print_r($responseData);exit;
        //=====================================
     // $traceId    =    $responseData['Data']['TraceId'];
      $precancelsts   =   'post';
     $message = ""; 
      $TotalRefundAmount =0;
      $markupFee_percentage_val   =0;
if (isset($responseData['Success']) && $responseData['Success']) {
            $cancel_status  =   1;
             $PTRId    =   $responseData['Data']['PTRId'];
        $PTRType    =   $responseData['Data']['PTRType'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'RefundQuote.txt');
            
            $hours = $objCancel->calculateHoursFromSLAMinutes($SLAInMinutes);
            
            // Check if this is a Direct Refund or RefundQuote response
            if ($useVoidQuote || $PTRType === 'VoidQuote') {
                // VoidQuote Response - similar to RefundQuote but with different benefits
                $TotalRefundAmount = 0;
                $Currency = '';
                
                if (isset($responseData['Data']['VoidQuotes']) && !empty($responseData['Data']['VoidQuotes'])) {
                    // Process VoidQuotes (similar structure to RefundQuotes)
                    $voidQuotes = [];
                    foreach($responseData['Data']['VoidQuotes'] as $k => $val){
                        $TotalRefundAmount += $val['TotalRefundAmount'];
                        $Currency = $val['Currency'];
                        
                        $voidQuotes[] = [
                            'name' => $val['FirstName'] . ' ' . $val['LastName'],
                            'eTicket' => $val['ETicket'],
                            'totalFare' => number_format($val['TotalFare'], 2),
                            'totalVoidingFee' => number_format($val['TotalVoidingFee'], 2),
                            'refundAmount' => number_format($val['TotalRefundAmount'], 2)
                        ];
                    }
                    
                    // Calculate service fees (same as VoidQuote button)
                    $serviceFees = $objCancel->getServiceTransactionFees();
                    $refundBaseFeePerPax = (float)($serviceFees['refund_fee'] ?? 0);
                    $refundAdditionalPerPax = (float)($serviceFees['refund_addition'] ?? 0);
                    $numPassengers = count($passengersArray);
                    
                    // Get IPG percentage
                    $ipgRow = $objCancel->getLisQuery("SELECT value FROM settings WHERE `key` = 'ipg_transaction_percentage' LIMIT 1");
                    $ipgPercentage = isset($ipgRow[0]['value']) ? floatval($ipgRow[0]['value']) : 0.0;
                    
                    // Calculate fees
                    $refundBaseFeeTotal = $refundBaseFeePerPax * max(1, $numPassengers);
                    $refundAdditionalTotal = $refundAdditionalPerPax * max(1, $numPassengers);
                    
                    // Apply IPG percentage on base amount (before service fees)
                    $ipgAmount = ($ipgPercentage > 0) ? ($ipgPercentage / 100.0) * $TotalRefundAmount : 0.0;
                    
                    // Final calculation
                    $serviceTotal = $refundBaseFeeTotal + $refundAdditionalTotal + $ipgAmount;
                    $finalRefundAmount = max(0, $TotalRefundAmount - $serviceTotal);
                    
                    $message = "✅ Void Quote successful - Best option for you (minimal charges)";
                    $response_New = array(
                        'success' => true,
                        'message' => $message,
                        'refund_type' => 'void_quote',
                        'total_refund_api' => $TotalRefundAmount,
                        'data' => [
                            'ptrId' => $PTRId,
                            'ptrType' => $PTRType,
                            'ptrStatus' => $PTRStatus,
                            'mfRef' => $mfreNum,
                            'slaMinutes' => $SLAInMinutes,
                            'slaHours' => $hours,
                            'totalRefundAmount' => $TotalRefundAmount,
                            'currency' => $Currency,
                            'base_refund_amount' => $TotalRefundAmount,
                            'refund_base_fee' => $refundBaseFeeTotal,
                            'refund_additional_markup' => $refundAdditionalTotal,
                            'ipg_percentage' => $ipgPercentage,
                            'ipg_amount' => $ipgAmount,
                            'service_total' => $serviceTotal,
                            'final_refund_amount' => $finalRefundAmount,
                            'voidQuotes' => $voidQuotes,
                            'note' => 'Void process offers minimal charges as you are within the void window',
                            'apiMessage' => isset($responseData['Data']['Message']) ? $responseData['Data']['Message'] : ''
                        ]
                    );
                } else {
                    // VoidQuote request successful but no quotes yet
                    $message = "✅ Void Quote request submitted successfully";
                    $response_New = array(
                        'success' => true,
                        'message' => $message,
                        'refund_type' => 'void_quote_pending',
                        'data' => [
                            'ptrId' => $PTRId,
                            'ptrType' => $PTRType,
                            'ptrStatus' => $PTRStatus,
                            'mfRef' => $mfreNum,
                            'slaMinutes' => $SLAInMinutes,
                            'slaHours' => $hours,
                            'note' => 'Void quote will be processed shortly. This is the best option as you are within void window.',
                            'apiMessage' => isset($responseData['Data']['Message']) ? $responseData['Data']['Message'] : ''
                        ]
                    );
                }
            } elseif ($useRefundQuote || $PTRType === 'RefundQuote') {
                // RefundQuote Response (mirror void-style breakdown)
                $refundQuotesArr = isset($responseData['Data']['RefundQuotes']) && is_array($responseData['Data']['RefundQuotes'])
                    ? $responseData['Data']['RefundQuotes'] : [];

                $Currency = 'USD';
                foreach ($refundQuotesArr as $k => $val) {
                    $TotalRefundAmount += (float)($val['TotalRefundAmount'] ?? 0);
                    if (!empty($val['Currency'])) { $Currency = $val['Currency']; }
                }
                // Fallback to top-level fields if per-passenger quotes not present
                if (empty($refundQuotesArr)) {
                    $TotalRefundAmount = (float)($responseData['Data']['TotalRefundAmount'] ?? 0);
                    $Currency = $responseData['Data']['Currency'] ?? 'USD';
                }

                // Service transaction fees from settings (per passenger)
                $serviceFees = $objCancel->getServiceTransactionFees();
                $refundBaseFeePerPax = (float)($serviceFees['refund_fee'] ?? 0);
                $refundAdditionalPerPax = (float)($serviceFees['refund_addition'] ?? 0);
                $numPassengers = count($passengersArray);
                
                // Get IPG percentage
                $ipgRow = $objCancel->getLisQuery("SELECT value FROM settings WHERE `key` = 'ipg_transaction_percentage' LIMIT 1");
                $ipgPercentage = isset($ipgRow[0]['value']) ? floatval($ipgRow[0]['value']) : 0.0;
                
                // Calculate fees (IPG on net amount after service fees)
                $refundBaseFeeTotal = $refundBaseFeePerPax * max(1, $numPassengers);
                $refundAdditionalTotal = $refundAdditionalPerPax * max(1, $numPassengers);
                
                // Apply IPG percentage on base refund amount (before service fees, like void)
                $ipgAmount = ($ipgPercentage > 0) ? ($ipgPercentage / 100.0) * $TotalRefundAmount : 0.0;
                
                // Final calculation
                $serviceTotal = $refundBaseFeeTotal + $refundAdditionalTotal + $ipgAmount;
                $finalRefundAmount = max(0, $TotalRefundAmount - $serviceTotal);

                // Prepare passenger refund details for UI
                $passengerRefunds = [];
                foreach ($refundQuotesArr as $k => $val) {
                    $passengerRefunds[] = [
                        'name' => trim(($val['FirstName'] ?? '').' '.($val['LastName'] ?? '')),
                        'eTicket' => $val['ETicket'] ?? '',
                        'totalFare' => number_format((float)($val['TotalFare'] ?? 0), 2),
                        'unusedFare' => number_format((float)($val['UnusedFare'] ?? 0), 2),
                        'cancellationCharge' => number_format((float)($val['CancellationCharge'] ?? 0), 2),
                        'noShowCharge' => number_format((float)($val['NoShowCharge'] ?? 0), 2),
                        'refundAmount' => number_format((float)($val['TotalRefundAmount'] ?? 0), 2)
                    ];
                }

                // Store RefundQuote PTR in database for cron monitoring
                $cancel_status = 0; // InProcess - cron will check and email when ready
                
                foreach ($passengersArray as $passenger) {
                    // Get traveller_id
                    $travellerId = 0;
                    try {
                        $travRow = $objCancel->getLisQuery("SELECT id FROM travellers_details WHERE e_ticket_number = '".addslashes($passenger['eTicket'])."' AND flight_booking_id = ".(int)$bookingId." LIMIT 1");
                        if (!empty($travRow)) {
                            $travellerId = intval($travRow[0]['id']);
                        }
                    } catch (Exception $e) {
                        $objCancel->_writeLog("Error getting traveller_id: " . $e->getMessage(), 'RefundQuote.txt');
                    }
                    
                    // Store in cancel_booking for cron to monitor
                    $objCancel->insCncelSts(
                        $bookingId,
                        $userId,
                        $precancelsts,
                        '', // error_code
                        $mfreNum,
                        '', // trace_id
                        200, // http_code
                        $PTRId,
                        'RefundQuote', // ptr_type
                        $SLAInMinutes,
                        $PTRStatus, // InProcess
                        '', // void_window
                        $passenger['eTicket'],
                        0, // admin_charges
                        0, // gst_charge
                        0, // total_void_fee
                        0, // total_refund_amount (will be calculated by cron)
                        $Currency,
                        $cancel_status,
                        'RefundQuote request submitted - awaiting quote', // message
                        $travellerId
                    );
                }
                
                // Calculate expected email time
                $expectedEmailTimeUTC = gmdate('d M Y, H:i', time() + ($SLAInMinutes * 60)) . ' UTC';
                
                $message = "Refund quote request submitted successfully. You will receive the quote details via email within " . $hours . " hour(s).";
                $response_New = array(
                    'success' => true,
                    'message' => $message,
                    'refund_type' => 'refund_quote_submitted', // Changed to indicate email-based flow
                    'data' => [
                        'ptrId' => $PTRId,
                        'ptrType' => $PTRType,
                        'ptrStatus' => $PTRStatus,
                        'mfRef' => $mfreNum,
                        'slaMinutes' => $SLAInMinutes,
                        'slaHours' => $hours,
                        'expected_email_time_utc' => $expectedEmailTimeUTC,
                        'workflow' => 'email_based',
                        'note' => 'Please check your email for the refund quote. You will receive detailed breakdown and accept/decline options.',
                        'apiMessage' => isset($responseData['Data']['Message']) ? $responseData['Data']['Message'] : ''
                    ]
                );
            }
      }
      else if(isset($responseData['Data']['Errors']) && is_array($responseData['Data']['Errors'])) {
     // print_r($responseData['Data']['Errors']);exit;
    foreach ($responseData['Data']['Errors'] as $error) {
        $errorCode = $error['Code'];
        $errorMessage = $error['Message'];
       $cancel_status = 0;
       $message_new = $errorMessage;
                         //    $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

   $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   
           //echo $errorCode;exit;
        $message    = "Problem in Cancellation";
          $cancel_status = 0;
                         $response_New = array(
            'success' => false, // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'RefundQuote.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
            $objCancel->_writeLog('HTTP Code not 200: ' . $httpCode, 'RefundQuote.txt');
            $objCancel->_writeLog('Raw Response: ' . $response, 'RefundQuote.txt');
              $cancel_status = 0;
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
            $message_new    = $message;
          $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   
         
                        $response_New = array(
           'success' => false,
           'message' => $message,
           'http_code' => $httpCode,
           'raw_request' => $requestData,
           'raw_response' => $response
       );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'RefundQuote.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){
                           
                              $cancel_status = 0;
               
                        $message    =   $responseData['Message'];
                        
                        // Check for specific error messages and provide better user feedback
                        if (strpos($message, 'refund details are missing') !== false) {
                            $message = "Unable to process refund quote. This may be due to booking restrictions or timing limitations. Please contact customer support for assistance.";
                        } elseif (strpos($message, 'already in process') !== false) {
                            $message = "A refund request for this booking is already being processed. Please check your booking status or contact support for updates.";
                        }
                        
                                                       $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   

                        //Booking is not eligible for voiding. -may be not under void window param
                         $response_New = array(
                            'success' => false,
                            'code' => 'API_ERROR',
                            'message' => $message,
                            'raw_request' => $requestData,
                            'raw_response' => $response,
                            'debug_info' => [
                                'original_message' => $responseData['Message'],
                                'mf_reference' => $mfreNum,
                                'booking_id' => $bookingId
                            ]
                        );
                                      $objCancel->_writeLog('step data empty '.$message,'RefundQuote.txt');
                    }
            } else {
                // No message provided, generic error
                $message = "Unable to process refund request. Please try again later or contact customer support.";
                $response_New = array(
                    'success' => false,
                    'code' => 'UNKNOWN_ERROR', 
                    'message' => $message,
                    'raw_request' => $requestData,
                    'raw_response' => $response
                );
                $objCancel->_writeLog('Empty response data with no message', 'RefundQuote.txt');
            }
        $objCancel->_writeLog('step end of refund quote ========= '.$message,'RefundQuote.txt');
        // Ensure we never return null to the client
        if (!isset($response_New) || empty($response_New)) {
            $response_New = array(
                'success' => false,
                'status' => 'error',
                'message' => 'No structured response was generated. Please try again.',
                'http_code' => isset($httpCode) ? $httpCode : null,
                'raw_request' => $requestData,
                'raw_response' => isset($response) ? $response : null
            );
        }
        echo json_encode($response_New);
        exit;