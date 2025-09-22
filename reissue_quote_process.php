<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if this is an AJAX request (more flexible detection)
$isAjax = false;
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
} elseif (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false) {
    $isAjax = true;
} elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $isAjax = true;
}

if (!$isAjax) {
    echo json_encode(['success' => false, 'message' => 'Invalid request - AJAX required']);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Debug logging
error_log("Raw input: " . $rawInput);
error_log("Decoded input: " . print_r($input, true));

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input: ' . json_last_error_msg()]);
    exit;
}

// Validate required fields
if (!isset($input['action']) || $input['action'] !== 'get_reissue_quote') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

if (!isset($input['bookingId']) || !isset($input['userId']) || !isset($input['mfRef']) || !isset($input['selectedPassengers'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    include_once('includes/common_const.php');
    include_once('includes/class.cancel.php');
    include_once('includes/mock_mystifly.php');
    $objCancel = new Cancel();
    
    // Log the request
    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
    $objCancel->_writeLog('ReissueQuote Request: '.json_encode($input),'reissueQuote.txt');
    
    $bookingId = $input['bookingId'];
    $userId = $input['userId'];
    $mfRef = $input['mfRef'];
    $selectedPassengers = $input['selectedPassengers'];
    $newDepartureDate = $input['newDepartureDate'] ?? null;
    $newCabinClass = $input['newCabinClass'];
    $newReturnDate = $input['newReturnDate'] ?? null;
    $segmentSelection = $input['segmentSelection'] ?? 'outbound'; // outbound | return | both
    
    // Validate ticket status for selected passengers
    $bookCanusers = $objCancel->BookCancelUsers($bookingId, $userId);
    $validPassengers = [];
    
    // Debug log
    $objCancel->_writeLog('Selected passengers from UI: ' . json_encode($selectedPassengers), 'reissueQuote.txt');
    $objCancel->_writeLog('DB passengers count: ' . count($bookCanusers), 'reissueQuote.txt');
    
    foreach ($selectedPassengers as $passenger) {
        foreach ($bookCanusers as $bookingPassenger) {
            if ($bookingPassenger['id'] == $passenger['id']) {
                // Use same logic as UI: check e_ticket_number presence and status
                $passengerTicketStatus = $bookingPassenger['pass_ticket_status'] ?? ($bookingPassenger['status'] ?? ($bookingPassenger['ticket_status'] ?? null));
                $voidStatus = $bookingPassenger['void_status'] ?? null;
                $cbCancel = isset($bookingPassenger['cb_cancel_status']) ? intval($bookingPassenger['cb_cancel_status']) : null;
                $cbPtr = $bookingPassenger['cb_ptr_status'] ?? null;
                $isCancelled = ($passengerTicketStatus && strtolower($passengerTicketStatus) === 'cancelled')
                    || ($voidStatus === 'Completed')
                    || ($cbCancel === 1 || ($cbPtr && strtolower($cbPtr) === 'completed'));
                
                $isTicketed = !empty($bookingPassenger['e_ticket_number']) && !$isCancelled;
                
                $objCancel->_writeLog('Passenger ID ' . $passenger['id'] . ': ticketed=' . ($isTicketed ? 'yes' : 'no') . ', e_ticket=' . ($bookingPassenger['e_ticket_number'] ?? 'null') . ', cancelled=' . ($isCancelled ? 'yes' : 'no'), 'reissueQuote.txt');
                
                if ($isTicketed) {
                    $validPassengers[] = [
                        'firstName' => $passenger['firstName'],
                        'lastName' => $passenger['lastName'],
                        'title' => $passenger['title'],
                        'eTicket' => $bookingPassenger['e_ticket_number'],
                        'passengerType' => $passenger['passengerType']
                    ];
                }
                break;
            }
        }
    }
    
    $objCancel->_writeLog('Valid passengers after filtering: ' . count($validPassengers), 'reissueQuote.txt');
    
    if (empty($validPassengers)) {
        echo json_encode(['success' => false, 'message' => 'No valid ticketed passengers found. Please select passengers with tickets who are not cancelled.']);
        exit;
    }
    
    // Get current flight details from booking
    include_once('includes/class.Booking.php');
    include_once('includes/dbConnect.php');
    $booking = new Booking($conn);
    $resultBooking = $booking->getBookingDetailsbyId($bookingId);
    
    if (empty($resultBooking)) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    // Prepare originDestinations for ReissueQuote API
    $originDestinations = [];

    $isReturnTrip = isset($resultBooking[0]['air_trip_type']) && $resultBooking[0]['air_trip_type'] === 'Return';

    // Outbound only or both
    if ($segmentSelection === 'outbound' || $segmentSelection === 'both' || !$isReturnTrip) {
        if (!empty($newDepartureDate)) {
            $originDestinations[] = [
                'originLocationCode' => $resultBooking[0]['dep_location'] ?? '',
                'destinationLocationCode' => $resultBooking[0]['arrival_location'] ?? '',
                'cabinPreference' => $newCabinClass,
                'departureDateTime' => $newDepartureDate,
                'flightNumber' => intval($resultBooking[0]['flight_no'] ?? 0),
                'airlineCode' => $resultBooking[0]['airline_code'] ?? ''
            ];
        }
    }

    // Return only or both
    if ($isReturnTrip && ($segmentSelection === 'return' || $segmentSelection === 'both')) {
        if (!empty($newReturnDate)) {
            // Get return flight details
            $returnDepDate = $objCancel->ReturnDepDate($bookingId, $userId, $resultBooking[0]['arrival_location']);
            if (!empty($returnDepDate)) {
                $originDestinations[] = [
                    'originLocationCode' => $resultBooking[0]['arrival_location'] ?? '',
                    'destinationLocationCode' => $resultBooking[0]['dep_location'] ?? '',
                    'cabinPreference' => $newCabinClass,
                    'departureDateTime' => $newReturnDate,
                    'flightNumber' => intval($returnDepDate[0]['flight_no'] ?? 0),
                    'airlineCode' => $returnDepDate[0]['airline_code'] ?? ''
                ];
            }
        }
    }
    
    // Prepare ReissueQuote request according to documentation
    $requestData = [
        'ptrType' => 'ReissueQuote',
        'mFRef' => $mfRef,
        'AllowChildPassenger' => false, // Will be set based on passenger types
        'reissueQuoteRequestType' => 'Segment',
        'passengers' => $validPassengers,
        'originDestinations' => $originDestinations
    ];
    
    // Check if there are child passengers
    foreach ($validPassengers as $passenger) {
        if ($passenger['passengerType'] === 'CHD') {
            $requestData['AllowChildPassenger'] = true;
            break;
        }
    }
    
    $objCancel->_writeLog('ReissueQuote API Request: '.json_encode($requestData),'reissueQuote.txt');
    
    // Check if we should use mock responses
    if (MOCK_MODE) {
        // Use mock response for development with actual form data
        $mockResponse = MockMystifly::getReissueQuoteResponse($validPassengers, $requestData);
        $response = json_encode($mockResponse);
        $httpCode = 200;
        
        // Log mock usage
        $objCancel->_writeLog('MOCK MODE: Using mock ReissueQuote response', 'reissueQuote.txt');
    } else {
        // Call real Mystifly ReissueQuote API
        $endpoint = 'PostTicketingRequest';
        $result = $objCancel->callApi($endpoint, $requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
    }
    
    if ($response) {
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $objCancel->_writeLog('JSON decode error: ' . json_last_error_msg(), 'reissueQuote.txt');
            echo json_encode(['success' => false, 'message' => 'Invalid response format from API']);
            exit;
        }
    } else {
        $objCancel->_writeLog('Empty response from API. HTTP Code: ' . $httpCode, 'reissueQuote.txt');
        echo json_encode(['success' => false, 'message' => 'No response received from airline system']);
        exit;
    }
    
    $objCancel->_writeLog('ReissueQuote API Response: '.json_encode($responseData),'reissueQuote.txt');
    
    // Process the response
    if (isset($responseData['Success']) && $responseData['Success']) {
        $ptrId = $responseData['Data']['PTRId'] ?? null;
        $ptrStatus = $responseData['Data']['PTRStatus'] ?? 'InProcess';
        $slaMinutes = $responseData['Data']['SLAInMinutes'] ?? 60;
        
        // Update database with reissue status
        foreach ($selectedPassengers as $passenger) {
            // Update travellers_details
            $updateData = [
                'reissue_status' => 'InProcess',
                'reissue_ptr_id' => $ptrId,
                'reissue_quote_id' => $ptrId,
                'cancel_type' => 'reissue'
            ];
            $condition = "id = " . intval($passenger['id']);
            $objCancel->update('travellers_details', $updateData, $condition);
            
            // Insert into cancel_booking for cron monitoring
            $travId = intval($passenger['id']);
            $eTicket = $passenger['eTicket'] ?? '';
            $objCancel->insCncelSts(
                $bookingId, $userId, 'post', '', $mfRef,
                '', 200, $ptrId, 'Reissue', $slaMinutes,
                $ptrStatus, '', $eTicket, 0, 0,
                0, 0, 'USD', 0, 'ReissueQuote request submitted', $travId
            );
        }
        
        // Send immediate confirmation email
        try {
            include_once('mail_send.php');
            $contactRows = $objCancel->getLisQuery("SELECT contact_email, contact_first_name, contact_last_name, mf_reference FROM temp_booking WHERE id = ".intval($bookingId)." LIMIT 1");
            $contact = !empty($contactRows) ? $contactRows[0] : [];
            $recipient = $contact['contact_email'] ?? '';
            $contactName = 'Customer';
            if (!empty($contact['contact_first_name']) || !empty($contact['contact_last_name'])) {
                $fn = trim($contact['contact_first_name'] ?? '');
                $ln = trim($contact['contact_last_name'] ?? '');
                $full = trim($fn.' '.$ln);
                if ($full !== '') { $contactName = $full; }
            }
            
            if (!empty($recipient)) {
                $subject = 'Reissue Request Submitted - Quote Processing';
                $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
                    .'<body style="margin:0;padding:20px;background-color:#f5f7fb;font-family:Arial,sans-serif;color:#333333;">'
                    .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin:0 auto;background-color:#ffffff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.08);">'
                    .'<tr><td align="center" style="padding:20px 0 10px 0;"><img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrips" style="height:50px;width:auto;display:block;margin:10px auto;"></td></tr>'
                    .'<tr><td align="center" style="background-color:#0029ff;color:#ffffff;font-size:18px;font-weight:bold;padding:14px;">Reissue Update</td></tr>'
                    .'<tr><td style="padding:22px;font-size:15px;line-height:1.6;color:#333333;">'
                    .'<p style="margin:0 0 12px 0;">Dear '.htmlspecialchars($contactName).',</p>'
                    .'<p style="margin:0 0 18px 0;">Your reissue request has been submitted and is being processed.</p>'
                    .'<div style="background:#f1f1f1;border-radius:6px;padding:14px;">'
                    .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">PTR ID:</span> <span>'.htmlspecialchars($ptrId).'</span></div>'
                    .'<div style="margin:0 0 6px 0;"><span style="font-weight:bold;">MFReference:</span> <span>'.htmlspecialchars($contact['mf_reference'] ?? $mfRef).'</span></div>'
                    .'<div style="margin:0;"><span style="font-weight:bold;">Processing Time:</span> <span>Up to '.htmlspecialchars($slaMinutes).' minutes</span></div>'
                    .'</div>'
                    .'<p style="margin:18px 0 0 0;color:#555555;">You will receive another email with quote options once the airline completes processing. The email will include direct links to accept or decline the reissue options.</p>'
                    .'</td></tr></table></body></html>';
                sendMail($recipient, $subject, $emailHtml);
                $objCancel->_writeLog('Reissue request confirmation email sent to: ' . $recipient, 'reissueQuote.txt');
            }
        } catch (Exception $e) {
            $objCancel->_writeLog('Reissue confirmation email failed: ' . $e->getMessage(), 'reissueQuote.txt');
        }

        // Return success response
        echo json_encode([
            'success' => true,
            'ptrId' => $ptrId,
            'status' => $ptrStatus,
            'slaMinutes' => $slaMinutes,
            'message' => 'Reissue quote request submitted successfully'
        ]);
        
    } else {
        $errorMessage = $responseData['Message'] ?? 'Unknown error occurred';
        echo json_encode(['success' => false, 'message' => $errorMessage]);
    }
    
} catch (Exception $e) {
    $objCancel->_writeLog('Exception: ' . $e->getMessage(), 'reissueQuote.txt');
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?> 