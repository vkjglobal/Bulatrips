<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');

$objCancel = new Cancel();

header('Content-Type: application/json');

// Support JSON and form POST
$rawInput = file_get_contents('php://input');
$input = [];
if (!empty($rawInput)) {
	$decoded = json_decode($rawInput, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		$input = $decoded;
	}
}

if (empty($input)) {
	$input = $_POST; // fallback
}

$mfreNum = isset($input['mfreNum']) ? trim($input['mfreNum']) : '';
$ptrId = isset($input['ptrId']) ? trim($input['ptrId']) : '';
$bookingId = isset($input['bookingId']) ? intval($input['bookingId']) : 0;
$userId = isset($input['userId']) ? intval($input['userId']) : 0;

// Extract numeric PTR for API call
$numericPtrId = 0;
if (!empty($ptrId)) {
    if (is_numeric($ptrId)) {
        $numericPtrId = intval($ptrId);
    } elseif (preg_match('/(\d+)/', $ptrId, $m)) {
        $numericPtrId = intval($m[1]);
    }
}

if (empty($mfreNum) || empty($ptrId)) {
	echo json_encode([
		'success' => false,
		'message' => 'Missing required parameters: mfreNum=' . $mfreNum . ', ptrId=' . $ptrId
	]);
	exit;
}

$requestData = array(
	'ptrType' => 'GetExchangeQuote',
	'MFRef' => $mfreNum,
	'PTRId' => $numericPtrId,
	'Page' => 1
);

$objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
$objCancel->_writeLog('GetExchangeQuote Raw Input: ' . $rawInput, 'reissueQuote.txt');
$objCancel->_writeLog('GetExchangeQuote Parsed Input: ' . json_encode($input), 'reissueQuote.txt');
$objCancel->_writeLog('GetExchangeQuote Request: '.json_encode($requestData),'reissueQuote.txt');

// Check if we should use mock responses
if (MOCK_MODE) {
    // Use mock response for development
    $mockResponse = MockMystifly::getGetExchangeQuoteResponse($ptrId);
    $response = json_encode($mockResponse);
    $httpCode = 200;
    
    // Log mock usage
    $objCancel->_writeLog('MOCK MODE: Using mock GetExchangeQuote response', 'reissueQuote.txt');
    $objCancel->_writeLog('MOCK MODE: Mock response data: ' . json_encode($mockResponse), 'reissueQuote.txt');
} else {
    // Call real API
    $endpoint = 'Search/PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];
}

if (!$response) {
	echo json_encode(['success' => false, 'message' => 'No response from API', 'httpCode' => $httpCode]);
	exit;
}

$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
	echo json_encode(['success' => false, 'message' => 'Invalid response JSON', 'httpCode' => $httpCode]);
	exit;
}

$objCancel->_writeLog('GetExchangeQuote Response: '.json_encode($responseData),'reissueQuote.txt');

if (!isset($responseData['Success']) || !$responseData['Success']) {
	$message = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error';
	echo json_encode(['success' => false, 'message' => $message, 'httpCode' => $httpCode, 'raw' => $responseData]);
	exit;
}

$data = isset($responseData['Data']) ? $responseData['Data'] : [];

echo json_encode([
	'success' => true,
	'httpCode' => $httpCode,
	'ptrId' => isset($data['PTRId']) ? $data['PTRId'] : $ptrId,
	'ptrType' => isset($data['PTRType']) ? $data['PTRType'] : null,
	'status' => isset($data['Status']) ? $data['Status'] : null,
	'resolution' => isset($data['Resolution']) ? $data['Resolution'] : null,
	'createdOn' => isset($data['CreatedOn']) ? $data['CreatedOn'] : null,
	'requestedPreferences' => isset($data['RequestedPreferences']) ? $data['RequestedPreferences'] : [],
	'passengers' => isset($data['Passengers']) ? $data['Passengers'] : []
]);

exit;
?>


