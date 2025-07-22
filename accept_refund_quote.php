<?php
error_reporting(0);
ini_set('display_errors', 0); 
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

$objCancel = new Cancel();

if (!isset($_POST['mfreNum']) || !isset($_POST['ptrId']) || !isset($_POST['acceptQuote'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$mfreNum = trim($_POST['mfreNum']);
$ptrId = trim($_POST['ptrId']);
$acceptQuote = trim($_POST['acceptQuote']); // 'yes' or 'no'
$bookingId = trim($_POST['bookingId']);
$userId = trim($_POST['userId']);
$preferenceOption = isset($_POST['preferenceOption']) ? (int)$_POST['preferenceOption'] : 1;

// Sanitize inputs
$mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
$acceptQuote = strtolower($acceptQuote);

if (!in_array($acceptQuote, ['yes', 'no'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid acceptance value'
    ]);
    exit;
}

// Prepare API request data
$requestData = array(
    'ptrType' => 'AcceptRefundQuote',
    'mFRef' => $mfreNum,
    'PTRId' => (int)$ptrId,
    'PreferenceOption' => $preferenceOption,
    'AcceptQuote' => $acceptQuote
);

try {
    $endpoint = 'PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];

    // Log the request and response
    $logRes = print_r($response, true);
    $logReq = print_r($requestData, true);
    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------', 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('userId is '.$userId, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('Booking ID is '.$bookingId, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REquest Received\n'.$logReq, 'acceptRefundQuote.txt');
    $objCancel->_writeLog('REsponse Received\n'.$logRes, 'acceptRefundQuote.txt');

    if ($response) {
        $responseData = json_decode($response, true);
    } else {
        throw new Exception('Empty response from API');
    }

    $precancelsts = 'post';
    $message = "";
    
    if (isset($responseData['Success']) && $responseData['Success']) {
        $cancel_status = 1;
        $PTRId = $responseData['Data']['PTRId'];
        $PTRType = $responseData['Data']['PTRType'];
        $PTRStatus = $responseData['Data']['PTRStatus'];
        $SLAInMinutes = isset($responseData['Data']['SLAInMinutes']) ? $responseData['Data']['SLAInMinutes'] : 0;
        
        $objCancel->_writeLog('Accept RefundQuote Success: '.$PTRStatus, 'acceptRefundQuote.txt');
        
        // Update database with acceptance status
        $bookCanIns = $objCancel->insCncelSts(
            $bookingId, $userId, $precancelsts, $errorCode = '', $mfreNum, 
            $traceId = '', $httpCode, $PTRId, $PTRType, $SLAInMinutes, 
            $PTRStatus, $VoidingWindow = '', $ticket_num = '', 
            $AdminCharges = '', $GSTCharge = '', $TotalVoidingFee = '', 
            $TotalRefundAmount = '', $Currency = '', $cancel_status, 
            'RefundQuote accepted by user'
        );

        if ($acceptQuote === 'yes') {
            $message = "Refund request has been accepted and is being processed. PTR ID: " . $PTRId;
        } else {
            $message = "Refund request has been declined successfully.";
        }

        $response_New = array(
            'status' => 'success',
            'message' => $message,
            'ptr_id' => $PTRId,
            'ptr_status' => $PTRStatus,
            'ptr_type' => $PTRType
        );

    } else {
        // Handle API errors
        $cancel_status = 0;
        $errorMessage = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error occurred';
        
        if (isset($responseData['Errors']) && is_array($responseData['Errors'])) {
            $errorMessage = implode(', ', $responseData['Errors']);
        }

        $objCancel->_writeLog('Accept RefundQuote Error: '.$errorMessage, 'acceptRefundQuote.txt');
        
        $bookCanIns = $objCancel->insCncelSts(
            $bookingId, $userId, $precancelsts, $errorCode = '', $mfreNum, 
            $traceId = '', $httpCode, $PTRId = '', $PTRType = 'AcceptRefundQuote', 
            $SLAInMinutes = '', $PTRStatus = '', $VoidingWindow = '', 
            $ticket_num = '', $AdminCharges = '', $GSTCharge = '', 
            $TotalVoidingFee = '', $TotalRefundAmount = '', $Currency = '', 
            $cancel_status, $errorMessage
        );

        $response_New = array(
            'status' => 'error',
            'message' => $errorMessage
        );
    }

} catch (Exception $e) {
    $objCancel->_writeLog('Accept RefundQuote Exception: '.$e->getMessage(), 'acceptRefundQuote.txt');
    
    $response_New = array(
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage()
    );
}

$objCancel->_writeLog('Accept RefundQuote Response: '.json_encode($response_New), 'acceptRefundQuote.txt');
echo json_encode($response_New);
exit;
?> 