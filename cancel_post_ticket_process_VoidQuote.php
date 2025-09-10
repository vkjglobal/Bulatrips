<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);



include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');
$objCancel = new Cancel();

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    // Parse JSON input for AJAX requests
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $bookingId = $data['booking_id'] ?? null;
    $passengerDetails = $data['passengerDetails'] ?? [];
    
    // For AJAX requests, use the provided passenger details
    $passengersArray = array();
    foreach ($passengerDetails as $passenger) {
        $passengersArray[] = array(
            "firstName" => $passenger['firstname'],
            "lastName" => $passenger['lastname'],
            "title" => $passenger['title'],
            "eTicket" => $passenger['eticket'],
            "passengerType" => $passenger['passengertype']
        );
    }
    
    // Get booking details from database
    $bookingDetails = $objCancel->get_booking_details($bookingId);
    if (!$bookingDetails) {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        exit;
    }
    $mfreNum = $bookingDetails['mf_reference'];
    
} else {
    // Traditional form submission
    $bookingId = $_POST['booking_id'] ?? null;
    $userId = $_POST['user_id'] ?? null;
    
    // Fetch passenger details from database
    $bookCanusers_req = $objCancel->BookCancelUsers($bookingId, $userId);
    
    $passengersArray = array();
    foreach ($bookCanusers_req as $val) {
        $passengersArray[] = array(
            "firstName" => $val['first_name'],
            "lastName" => $val['last_name'],
            "title" => $val['title'],
            "eTicket" => $val['e_ticket_number'],
            "passengerType" => $val['passenger_type']
        );
    }
    
    // Get booking details
    $bookingDetails = $objCancel->get_booking_details($bookingId);
    $mfreNum = $bookingDetails['mf_reference'] ?? '';
}

// Escape and sanitize the data
$mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
$bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);

// Determine passenger count
$numPassengers = count($passengersArray);

// Helper: get IPG percentage
$ipgRow = $objCancel->getLisQuery("SELECT value FROM settings WHERE `key` = 'ipg_transaction_percentage' LIMIT 1");
$ipgPercentage = isset($ipgRow[0]['value']) ? floatval($ipgRow[0]['value']) : 0.0;

// Check for child passengers
$childpsnger = 0;
if ($isAjax && isset($passengerDetails[0]['child_count'])) {
    $childpsnger = $passengerDetails[0]['child_count'];
} elseif (!$isAjax && isset($bookCanusers_req[0]['child_count'])) {
    $childpsnger = $bookCanusers_req[0]['child_count'];
}
$allow_child = ($childpsnger > 0);

// Create request data for VoidQuote API
$requestData = array(
    'ptrType' => 'VoidQuote',
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => $allow_child,
    'passengers' => $passengersArray,
    'AdditionalNote' => 'Void quote request'
);

// Check if we should use mock responses
file_put_contents('debug_void_quote.txt', "About to check MOCK_MODE at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if (MOCK_MODE) {
    // Debug log
    file_put_contents('debug_void_quote.txt', "MOCK_MODE is TRUE at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    
    // Use mock response for development
    $mockResponse = MockMystifly::getVoidQuoteResponse($passengersArray);
    file_put_contents('debug_void_quote.txt', "Mock response generated: " . json_encode($mockResponse) . "\n", FILE_APPEND);
    
    $response = json_encode($mockResponse);
    $httpCode = 200;
    
    file_put_contents('debug_void_quote.txt', "Response encoded and httpCode set at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    
    // Log mock usage and response details
    $objCancel->_writeLog('MOCK MODE: Using mock VoidQuote response', 'voidquote.txt');
    $objCancel->_writeLog('MOCK MODE: Mock response data: ' . json_encode($mockResponse), 'voidquote.txt');
    
    // Get service transaction fees
    file_put_contents('debug_void_quote.txt', "Getting service transaction fees at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    
    $serviceFees = $objCancel->getServiceTransactionFees();
    file_put_contents('debug_void_quote.txt', "Service fees fetched: " . json_encode($serviceFees) . "\n", FILE_APPEND);
    
    $refundBaseFeePerPax = floatval($serviceFees['refund_fee']);
    $refundAdditionalPerPax = floatval($serviceFees['refund_addition']);
    
            // Calculate final refund amount after deducting fees (IPG on net amount)
            $baseRefundAmount = floatval($mockResponse['Data']['TotalRefundAmount']);
            $refundBaseFeeTotal = $refundBaseFeePerPax * max(1, $numPassengers);
            $refundAdditionalTotal = $refundAdditionalPerPax * max(1, $numPassengers);
            
            // First subtract service fees from base
            $amountAfterServiceFees = max(0, $baseRefundAmount - $refundBaseFeeTotal - $refundAdditionalTotal);
            
            // Then apply IPG percentage on the net amount (after service fees)
            $ipgAmount = ($ipgPercentage > 0) ? ($ipgPercentage / 100.0) * $amountAfterServiceFees : 0.0;
            
            // Final calculation
            $serviceTotal = $refundBaseFeeTotal + $refundAdditionalTotal + $ipgAmount;
            $finalRefundAmount = max(0, $baseRefundAmount - $serviceTotal);
    
    $objCancel->_writeLog('MOCK MODE: Base amount: ' . $baseRefundAmount . ', Fees: base(' . $refundBaseFeeTotal . '), add(' . $refundAdditionalTotal . '), ipg(' . $ipgAmount . ') => Final: ' . $finalRefundAmount, 'voidquote.txt');
    
    // Build response for UI
    $response_New = array(
        'status' => 'success',
        'message' => 'Void Quote Received: InProcess. Final Refundable Amount is: USD ' . $finalRefundAmount,
        'ptr_id' => $mockResponse['Data']['PTRId'],
        'ptr_status' => $mockResponse['Data']['PTRStatus'],
        'refundamount' => $finalRefundAmount,
        'currency' => 'USD',
        // breakdown fields for UI
        'data' => array(
            'TotalRefundAmount' => $baseRefundAmount,
            'refund_base_fee' => $refundBaseFeeTotal,
            'refund_additional_markup' => $refundAdditionalTotal,
            'ipg_percentage' => $ipgPercentage,
            'ipg_amount' => $ipgAmount,
            'service_total' => $serviceTotal,
            'final_refund_amount' => $finalRefundAmount,
            'Currency' => 'USD'
        ),
        'admin_charges' => 0,
        'gst_charge' => 0,
        'voiding_fee' => 0,
        'voiding_window' => '',
        'sla_minutes' => 0,
        'booking_id' => $bookingId,
        'passenger_details' => $passengerDetails
    );
    
    $objCancel->_writeLog('MOCK MODE: Final response: ' . json_encode($response_New), 'voidquote.txt');
    // Return mock response directly
    if ($isAjax) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($response_New);
        exit;
    }
} else {
    // Call real VoidQuote API
    $endpoint = 'PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];
    
    // Log the request and response
    $objCancel->_writeLog('VoidQuote Request Data: ' . json_encode($requestData), 'voidquote.txt');
    $objCancel->_writeLog('VoidQuote HTTP Code: ' . $httpCode, 'voidquote.txt');
    $objCancel->_writeLog('VoidQuote Raw API Response: ' . $response, 'voidquote.txt');
    
    if ($response) {
        $responseData = json_decode($response, true);
        
        if (isset($responseData['Success']) && $responseData['Success']) {
            $PTRId = $responseData['Data']['PTRId'] ?? '';
            $PTRType = $responseData['Data']['PTRType'] ?? '';
            $SLAInMinutes = $responseData['Data']['SLAInMinutes'] ?? '';
            $PTRStatus = $responseData['Data']['PTRStatus'] ?? '';
            $VoidingWindow = $responseData['Data']['VoidingWindow'] ?? '';
            
            $objCancel->_writeLog('VoidQuote Success - PTRStatus: ' . $PTRStatus, 'voidquote.txt');
            
            // Aggregate base refund from API quotes
            $TotalRefundAmount = 0.0;
            $Currency = '';
            $AdminCharges = 0;
            $GSTCharge = 0;
            $TotalVoidingFee = 0;
            
            if (isset($responseData['Data']['VoidQuotes']) && !empty($responseData['Data']['VoidQuotes'])) {
                foreach($responseData['Data']['VoidQuotes'] as $val) {
                    $TotalRefundAmount += floatval($val['TotalRefundAmount']);
                    $Currency = $val['Currency'];
                    $AdminCharges += $val['AdminCharges'];
                    $GSTCharge += $val['GSTCharge'];
                    $TotalVoidingFee += $val['TotalVoidingFee'];
                }
            }
            
            // Get service transaction fees (per passenger)
            $serviceFees = $objCancel->getServiceTransactionFees();
            $refundBaseFeePerPax = floatval($serviceFees['refund_fee']);
            $refundAdditionalPerPax = floatval($serviceFees['refund_addition']);
            
            // Calculate totals (IPG on net amount after service fees)
            $baseRefundAmount = $TotalRefundAmount;
            $refundBaseFeeTotal = $refundBaseFeePerPax * max(1, $numPassengers);
            $refundAdditionalTotal = $refundAdditionalPerPax * max(1, $numPassengers);
            
            // First subtract service fees from base
            $amountAfterServiceFees = max(0, $baseRefundAmount - $refundBaseFeeTotal - $refundAdditionalTotal);
            
            // Then apply IPG percentage on the net amount (after service fees)
            $ipgAmount = ($ipgPercentage > 0) ? ($ipgPercentage / 100.0) * $amountAfterServiceFees : 0.0;
            
            // Final calculation
            $serviceTotal = $refundBaseFeeTotal + $refundAdditionalTotal + $ipgAmount;
            $finalRefundAmount = max(0, $baseRefundAmount - $serviceTotal);
            
            $response_New = array(
                'status' => 'success',
                'message' => 'Void Quote Received: ' . $PTRStatus . ' Final Refundable Amount is: ' . $Currency . ' ' . $finalRefundAmount,
                'ptr_id' => $PTRId,
                'ptr_status' => $PTRStatus,
                'refundamount' => $finalRefundAmount,
                'currency' => $Currency,
                // Provide breakdown for UI (old style keys for fallback)
                'base_refund_amount' => $baseRefundAmount,
                'refund_base_fee' => $refundBaseFeeTotal,
                'refund_additional_markup' => $refundAdditionalTotal,
                'ipg_percentage' => $ipgPercentage,
                'ipg_amount' => $ipgAmount,
                'service_total' => $serviceTotal,
                'final_refund_amount' => $finalRefundAmount,
                'admin_charges' => 0,
                'gst_charge' => 0,
                'voiding_fee' => 0,
                'voiding_window' => $VoidingWindow,
                'sla_minutes' => 0,
                'booking_id' => $bookingId,
                'passenger_details' => $passengerDetails
            );
            
        } else {
            // Handle errors
            $message = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error occurred';
            $response_New = array(
                'status' => 'error',
                'message' => $message
            );
        }
    } else {
        $response_New = array(
            'status' => 'error',
            'message' => 'No response from VoidQuote API'
        );
    }
}

$objCancel->_writeLog('VoidQuote Final response: ' . json_encode($response_New), 'voidquote.txt');

// Return JSON response for AJAX requests
if ($isAjax) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($response_New);
    exit;
} else {
    // For traditional form submissions, redirect or show result
    if ($response_New['status'] === 'success') {
        // Store quote details in session for step 2
        session_start();
        $_SESSION['void_quote_data'] = $response_New;
        header('Location: cancel_user.php?booking_id=' . $bookingId . '&step=confirm_void');
        exit;
    } else {
        // Show error
        echo "<script>alert('Error: " . $response_New['message'] . "'); window.history.back();</script>";
    }
}
?> 