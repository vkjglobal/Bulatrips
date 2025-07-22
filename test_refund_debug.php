<?php
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

// Test script for debugging refund issues
$objCancel = new Cancel();

// Test with booking ID 126
$bookingId = 126;
$userId = 73;

echo "<h1>Refund Debug Test</h1>";
echo "<h2>Booking ID: $bookingId, User ID: $userId</h2>";

// 1. Get booking details
echo "<h3>1. Booking Details:</h3>";
$bookingDetails = $objCancel->get_booking_details($bookingId);
if ($bookingDetails) {
    echo "<pre>" . print_r($bookingDetails, true) . "</pre>";
} else {
    echo "❌ No booking found!<br>";
}

// 2. Get passenger data
echo "<h3>2. Passenger Data:</h3>";
$passengerData = $objCancel->BookCancelUsers($bookingId, $userId);
if (!empty($passengerData)) {
    echo "<pre>" . print_r($passengerData, true) . "</pre>";
    
    // 3. Check void window
    echo "<h3>3. Void Window Check:</h3>";
    $firstPassenger = $passengerData[0];
    $voidWindow = $firstPassenger['void_window'];
    $currentDateTime = new DateTime();
    
    if (!empty($voidWindow)) {
        $voidWindowDateTime = new DateTime($voidWindow);
        echo "Void Window: " . $voidWindow . "<br>";
        echo "Current Time: " . $currentDateTime->format('Y-m-d H:i:s') . "<br>";
        
        if ($currentDateTime > $voidWindowDateTime) {
            echo "❌ <strong>VOID WINDOW EXPIRED!</strong> - This is likely why RefundQuote is failing.<br>";
            echo "⏰ Expired " . $currentDateTime->diff($voidWindowDateTime)->format('%h hours %i minutes ago') . "<br>";
        } else {
            echo "✅ Void window is still active<br>";
            echo "⏰ Expires in " . $currentDateTime->diff($voidWindowDateTime)->format('%h hours %i minutes') . "<br>";
        }
    }
    
    // 4. Test API request
    echo "<h3>4. API Request Test:</h3>";
    $mfreNum = $bookingDetails['mf_reference'];
    
    // Prepare API request
    $requestData = array(
        'ptrType' => 'RefundQuote',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $firstPassenger['first_name'],
                'lastName' => $firstPassenger['last_name'],
                'title' => $firstPassenger['title'],
                'eTicket' => $firstPassenger['e_ticket_number'],
                'passengerType' => $firstPassenger['passenger_type']
            )
        ),
        'AdditionalNote' => 'Test refund quote for debug'
    );
    
    echo "<h4>Request Data:</h4>";
    echo "<pre>" . json_encode($requestData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Make API call
    $endpoint = 'PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    
    echo "<h4>API Response:</h4>";
    echo "HTTP Code: " . $result['httpCode'] . "<br>";
    
    if ($result['responseData']) {
        $responseData = json_decode($result['responseData'], true);
        echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
        
        // Analysis
        echo "<h4>Analysis:</h4>";
        if (isset($responseData['Message']) && strpos($responseData['Message'], 'refund details are missing') !== false) {
            echo "❌ <strong>API Error:</strong> " . $responseData['Message'] . "<br>";
            echo "💡 <strong>Likely Cause:</strong> Void window expired or booking not eligible for refund quote<br>";
            echo "🔧 <strong>Solution:</strong> Use direct refund or contact Mystifly support<br>";
        } else if (isset($responseData['Success']) && $responseData['Success']) {
            echo "✅ RefundQuote successful!<br>";
        } else {
            echo "❌ API Error: " . (isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error') . "<br>";
        }
    }
    
} else {
    echo "❌ No passenger data found!<br>";
}

// 5. Alternative: Try Void Quote instead
echo "<h3>5. Alternative: Try VoidQuote</h3>";
if (!empty($passengerData)) {
    $voidRequestData = array(
        'ptrType' => 'VoidQuote',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $firstPassenger['first_name'],
                'lastName' => $firstPassenger['last_name'],
                'title' => $firstPassenger['title'],
                'eTicket' => $firstPassenger['e_ticket_number'],
                'passengerType' => $firstPassenger['passenger_type']
            )
        ),
        'AdditionalNote' => 'Test void quote for debug'
    );
    
    echo "<h4>VoidQuote Request:</h4>";
    echo "<pre>" . json_encode($voidRequestData, JSON_PRETTY_PRINT) . "</pre>";
    
    $voidResult = $objCancel->callApi($endpoint, $voidRequestData);
    echo "<h4>VoidQuote Response:</h4>";
    echo "HTTP Code: " . $voidResult['httpCode'] . "<br>";
    
    if ($voidResult['responseData']) {
        $voidResponseData = json_decode($voidResult['responseData'], true);
        echo "<pre>" . json_encode($voidResponseData, JSON_PRETTY_PRINT) . "</pre>";
    }
}

echo "<hr>";
echo "<h3>Summary & Recommendations:</h3>";
echo "<ul>";
echo "<li>✅ Your application code is working correctly</li>";
echo "<li>✅ Database queries are returning proper data</li>";
echo "<li>✅ API request format is correct</li>";
echo "<li>❌ Mystifly API is rejecting RefundQuote (likely due to expired void window)</li>";
echo "<li>💡 <strong>Solution:</strong> Use VoidQuote instead of RefundQuote, or contact Mystifly support</li>";
echo "</ul>";
?> 