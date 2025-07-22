<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

$objCancel = new Cancel();

echo "<h1>Direct Refund API Test</h1>";

// Test with booking 126 - First check what user owns this booking
$bookingId = 126;

// Check the actual owner of booking 126
$sql = "SELECT id, user_id, mf_reference, ticket_status, void_window FROM temp_booking WHERE id = " . intval($bookingId);
$conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$stmt = $conn->prepare($sql);
$stmt->execute();
$bookingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bookingInfo) {
    echo "❌ Booking 126 not found in database!";
    exit;
}

echo "<h3>Booking 126 Info:</h3>";
echo "<pre>" . print_r($bookingInfo, true) . "</pre>";

$userId = $bookingInfo['user_id'];
echo "<h3>Using User ID: $userId</h3>";

// Get booking details
$bookingDetails = $objCancel->get_booking_details($bookingId);
if (!$bookingDetails) {
    echo "❌ Booking not found!";
    exit;
}

echo "<h3>Booking Details (via Cancel class):</h3>";
echo "<pre>" . print_r($bookingDetails, true) . "</pre>";

// Get passenger data
$passengerData = $objCancel->BookCancelUsers($bookingId, $userId);
if (empty($passengerData)) {
    echo "❌ No passenger data found!";
    exit;
}

echo "<h3>Passenger Data:</h3>";
echo "<pre>" . print_r($passengerData, true) . "</pre>";

// Check void window
$firstPassenger = $passengerData[0];
$voidWindow = $firstPassenger['void_window'];
$currentDateTime = new DateTime();

echo "<h3>Void Window Check:</h3>";
if (!empty($voidWindow)) {
    $voidWindowDateTime = new DateTime($voidWindow);
    echo "Void Window: " . $voidWindow . "<br>";
    echo "Current Time: " . $currentDateTime->format('Y-m-d H:i:s') . "<br>";
    
    if ($currentDateTime > $voidWindowDateTime) {
        echo "❌ <strong>VOID WINDOW EXPIRED!</strong> - Using RefundQuote<br>";
        $useRefundQuote = true;
        $useVoidQuote = false;
    } else {
        echo "✅ Void window is still active - Using VoidQuote<br>";
        $useVoidQuote = true;
        $useRefundQuote = false;
    }
} else {
    echo "⚠️ No void window info - Using RefundQuote<br>";
    $useRefundQuote = true;
    $useVoidQuote = false;
}

// Prepare API request
$mfreNum = $bookingDetails['mf_reference'];
$passengersArray = [];

foreach ($passengerData as $passenger) {
    $passengersArray[] = array(
        "firstName" => $passenger['first_name'],
        "lastName" => $passenger['last_name'],
        "title" => $passenger['title'],
        "eTicket" => $passenger['e_ticket_number'],
        "passengerType" => $passenger['passenger_type']
    );
}

// Choose API type
if ($useVoidQuote) {
    $ptrType = 'VoidQuote';
    $additionalNote = 'Void quote request - best option for customer (full refund)';
} else {
    $ptrType = 'RefundQuote';
    $additionalNote = 'Refund quote request - void window expired';
}

$requestData = array(
    'ptrType' => $ptrType,
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => false,
    'passengers' => array(
        array(
            'firstName' => $passengersArray[0]['firstName'],
            'lastName' => $passengersArray[0]['lastName'],
            'title' => $passengersArray[0]['title'],
            'eTicket' => $passengersArray[0]['eTicket'],
            'passengerType' => $passengersArray[0]['passengerType']
        )
    ),
    'AdditionalNote' => $additionalNote
);

echo "<h3>API Request:</h3>";
echo "<pre>" . json_encode($requestData, JSON_PRETTY_PRINT) . "</pre>";

// Make API call
echo "<h3>Making API Call...</h3>";
$endpoint = 'PostTicketingRequest';
$result = $objCancel->callApi($endpoint, $requestData);
$httpCode = $result['httpCode'];
$response = $result['responseData'];

echo "<h3>API Response:</h3>";
echo "HTTP Code: " . $httpCode . "<br>";
echo "<pre>" . $response . "</pre>";

// Parse response
if ($response) {
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Parsed Response:</h3>";
        echo "<pre>" . print_r($responseData, true) . "</pre>";
        
        if (isset($responseData['Success']) && $responseData['Success']) {
            echo "<h3>✅ SUCCESS!</h3>";
            echo "PTR ID: " . $responseData['Data']['PTRId'] . "<br>";
            echo "PTR Type: " . $responseData['Data']['PTRType'] . "<br>";
            echo "Status: " . $responseData['Data']['PTRStatus'] . "<br>";
        } else {
            echo "<h3>❌ FAILED!</h3>";
            if (!empty($responseData['Message'])) {
                echo "Error: " . $responseData['Message'] . "<br>";
                echo "<h4>🔧 SOLUTION: Manual Refund Process</h4>";
                echo "<p>Since void window is expired, this booking needs manual refund processing.</p>";
                echo "<p>Your system will now handle this as a manual refund request.</p>";
            }
        }
    } else {
        echo "<h3>❌ JSON Parse Error!</h3>";
        echo "Error: " . json_last_error_msg() . "<br>";
    }
} else {
    echo "<h3>❌ No Response!</h3>";
}
?> 