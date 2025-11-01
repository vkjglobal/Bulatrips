<?php
/**
 * Search PTR - Check RefundQuote Status
 * 
 * Purpose: Poll Mystifly Search PTR API to check if RefundQuote is ready
 * Usage: Frontend calls this repeatedly until Status = "Completed"
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');

$objCancel = new Cancel();

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['ptr_id']) || !isset($data['mf_ref'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters: ptr_id and mf_ref']);
    exit;
}

$ptrId = intval($data['ptr_id']);
$mfRef = trim($data['mf_ref']);
$ptrType = isset($data['ptr_type']) ? trim($data['ptr_type']) : 'RefundQuote';

$objCancel->_writeLog("=== Search PTR Request - PTR ID: $ptrId, MF Ref: $mfRef, Type: $ptrType ===", 'RefundQuote.txt');

// Prepare Search PTR request
$requestData = array(
    'ptrType' => $ptrType,
    'MFRef' => $mfRef,
    'PTRId' => $ptrId,
    'Page' => 1
);

$objCancel->_writeLog('Search PTR Request: ' . json_encode($requestData), 'RefundQuote.txt');

$endpoint = 'SearchPostTicketingRequest';

// Check if we should use mock responses
if (MOCK_MODE) {
    // Use mock response for development
    $mockResponse = MockMystifly::getSearchPTRResponse($ptrId, $ptrType);
    $response = json_encode($mockResponse);
    $httpCode = 200;
    $objCancel->_writeLog('MOCK MODE: Using mock Search PTR response', 'RefundQuote.txt');
} else {
    // Call real API
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];
}

$objCancel->_writeLog('Search PTR HTTP Code: ' . $httpCode, 'RefundQuote.txt');
$objCancel->_writeLog('Search PTR Response: ' . $response, 'RefundQuote.txt');

// Check for API errors
if ($httpCode !== 200 || empty($response)) {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Failed to check quote status. Please try again.',
        'http_code' => $httpCode
    ]);
    exit;
}

// Parse response
$responseData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Invalid response from server'
    ]);
    exit;
}

// Check if response is successful
if (!isset($responseData['Success']) || !$responseData['Success']) {
    $errorMessage = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error';
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => $errorMessage
    ]);
    exit;
}

// Extract PTR details
$ptrDetails = isset($responseData['Data']['PTRDetail'][0]) ? $responseData['Data']['PTRDetail'][0] : null;

if (!$ptrDetails) {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'PTR details not found'
    ]);
    exit;
}

$status = $ptrDetails['Status'] ?? 'Unknown';
$refundQuotes = isset($ptrDetails['RefundQuotes']) ? $ptrDetails['RefundQuotes'] : [];
$voidQuotes = isset($ptrDetails['VoidQuotes']) ? $ptrDetails['VoidQuotes'] : [];

$objCancel->_writeLog("PTR Status: $status", 'RefundQuote.txt');
$objCancel->_writeLog("RefundQuotes count: " . count($refundQuotes), 'RefundQuote.txt');
$objCancel->_writeLog("VoidQuotes count: " . count($voidQuotes), 'RefundQuote.txt');

// Check status
if ($status === 'Completed') {
    // Quote is ready
    if ($ptrType === 'RefundQuote' && !empty($refundQuotes)) {
        // Calculate totals
        $totalRefundAmount = 0;
        $currency = 'USD';
        $passengerRefunds = [];
        
        foreach ($refundQuotes as $quote) {
            $totalRefundAmount += (float)($quote['TotalRefundAmount'] ?? 0);
            if (!empty($quote['Currency'])) {
                $currency = $quote['Currency'];
            }
            
            $passengerRefunds[] = [
                'name' => trim(($quote['FirstName'] ?? '') . ' ' . ($quote['LastName'] ?? '')),
                'eTicket' => $quote['ETicket'] ?? '',
                'totalFare' => number_format((float)($quote['TotalFare'] ?? 0), 2),
                'unusedFare' => number_format((float)($quote['UnusedFare'] ?? 0), 2),
                'cancellationCharge' => number_format((float)($quote['CancellationCharge'] ?? 0), 2),
                'noShowCharge' => number_format((float)($quote['NoShowCharge'] ?? 0), 2),
                'refundAmount' => number_format((float)($quote['TotalRefundAmount'] ?? 0), 2)
            ];
        }
        
        // Get service fees
        $serviceFees = $objCancel->getServiceTransactionFees();
        $refundBaseFeePerPax = (float)($serviceFees['refund_fee'] ?? 0);
        $refundAdditionalPerPax = (float)($serviceFees['refund_addition'] ?? 0);
        $numPassengers = count($refundQuotes);
        
        // Get IPG percentage
        $ipgRow = $objCancel->getLisQuery("SELECT value FROM settings WHERE `key` = 'ipg_transaction_percentage' LIMIT 1");
        $ipgPercentage = isset($ipgRow[0]['value']) ? floatval($ipgRow[0]['value']) : 0.0;
        
        // Calculate fees
        $refundBaseFeeTotal = $refundBaseFeePerPax * max(1, $numPassengers);
        $refundAdditionalTotal = $refundAdditionalPerPax * max(1, $numPassengers);
        $ipgAmount = ($ipgPercentage > 0) ? ($ipgPercentage / 100.0) * $totalRefundAmount : 0.0;
        
        $serviceTotal = $refundBaseFeeTotal + $refundAdditionalTotal + $ipgAmount;
        $finalRefundAmount = max(0, $totalRefundAmount - $serviceTotal);
        
        echo json_encode([
            'success' => true,
            'status' => 'completed',
            'message' => 'Refund quote is ready',
            'data' => [
                'ptrId' => $ptrId,
                'ptrType' => $ptrType,
                'ptrStatus' => $status,
                'mfRef' => $mfRef,
                'currency' => $currency,
                'base_refund_amount' => $totalRefundAmount,
                'refund_base_fee' => $refundBaseFeePerPax,
                'refund_additional_markup' => $refundAdditionalPerPax,
                'ipg_percentage' => $ipgPercentage,
                'ipg_amount' => $ipgAmount,
                'service_total' => $serviceTotal,
                'final_refund_amount' => $finalRefundAmount,
                'passengerRefunds' => $passengerRefunds
            ]
        ]);
        
    } elseif ($ptrType === 'VoidQuote' && !empty($voidQuotes)) {
        // VoidQuote completed
        $totalRefundAmount = 0;
        $currency = 'USD';
        $voidQuotesArr = [];
        
        foreach ($voidQuotes as $quote) {
            $totalRefundAmount += (float)($quote['TotalRefundAmount'] ?? 0);
            if (!empty($quote['Currency'])) {
                $currency = $quote['Currency'];
            }
            
            $voidQuotesArr[] = [
                'name' => trim(($quote['FirstName'] ?? '') . ' ' . ($quote['LastName'] ?? '')),
                'eTicket' => $quote['ETicket'] ?? '',
                'totalFare' => number_format((float)($quote['TotalFare'] ?? 0), 2),
                'totalVoidingFee' => number_format((float)($quote['TotalVoidingFee'] ?? 0), 2),
                'refundAmount' => number_format((float)($quote['TotalRefundAmount'] ?? 0), 2)
            ];
        }
        
        echo json_encode([
            'success' => true,
            'status' => 'completed',
            'message' => 'Void quote is ready',
            'data' => [
                'ptrId' => $ptrId,
                'ptrType' => $ptrType,
                'ptrStatus' => $status,
                'mfRef' => $mfRef,
                'currency' => $currency,
                'totalRefundAmount' => $totalRefundAmount,
                'voidQuotes' => $voidQuotesArr
            ]
        ]);
        
    } else {
        // Completed but no quotes
        echo json_encode([
            'success' => true,
            'status' => 'completed_no_quotes',
            'message' => 'Quote processing completed but no quotes available',
            'data' => [
                'ptrId' => $ptrId,
                'ptrType' => $ptrType,
                'ptrStatus' => $status
            ]
        ]);
    }
    
} elseif ($status === 'InProcess') {
    // Still processing
    echo json_encode([
        'success' => true,
        'status' => 'in_process',
        'message' => 'Quote is being processed. Please wait...',
        'data' => [
            'ptrId' => $ptrId,
            'ptrType' => $ptrType,
            'ptrStatus' => $status
        ]
    ]);
    
} else {
    // Other status (rejected, failed, etc.)
    echo json_encode([
        'success' => false,
        'status' => strtolower($status),
        'message' => "Quote request status: $status",
        'data' => [
            'ptrId' => $ptrId,
            'ptrType' => $ptrType,
            'ptrStatus' => $status,
            'resolution' => $ptrDetails['Resolution'] ?? 'Unknown'
        ]
    ]);
}

exit;
