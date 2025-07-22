<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

echo "<h1>Refund API Test for Booking 126</h1>";

try {
    // Connect to correct database
    $conn = new PDO("mysql:host=localhost;dbname=travelsite", 'root', '');
    echo "✅ Connected to travelsite database<br>";
    
    $objCancel = new Cancel();
    
    // Get booking details
    $bookingId = 126;
    $sql = "SELECT user_id FROM temp_booking WHERE id = 126";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $bookingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bookingInfo) {
        echo "❌ Could not find user_id for booking 126";
        exit;
    }
    
    $userId = $bookingInfo['user_id'];
    echo "Using User ID: $userId<br>";
    
    // Get passenger data using correct method
    $passengerData = $objCancel->BookCancelUsers($bookingId, $userId);
    
    if (empty($passengerData)) {
        echo "❌ No passenger data found!<br>";
        echo "Let's check the database query directly...<br>";
        
        // Direct database query
        $sql = 'SELECT tb.mf_reference, tb.ticket_status, tb.fare_type, tb.void_window, tb.dep_date,
                       td.first_name, td.last_name, td.title, td.e_ticket_number, td.passenger_type
                FROM temp_booking tb
                LEFT JOIN travellers_details td ON tb.id = td.flight_booking_id  
                WHERE tb.id = ? AND tb.user_id = ?';
        $stmt = $conn->prepare($sql);
        $stmt->execute([$bookingId, $userId]);
        $directData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Direct Database Query Result:</h3>";
        echo "<pre>" . print_r($directData, true) . "</pre>";
        
        if (empty($directData)) {
            echo "❌ No data found with user_id $userId<br>";
            echo "Let's check without user_id filter...<br>";
            
            $sql = 'SELECT tb.*, td.first_name, td.last_name, td.title, td.e_ticket_number, td.passenger_type
                    FROM temp_booking tb
                    LEFT JOIN travellers_details td ON tb.id = td.flight_booking_id  
                    WHERE tb.id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$bookingId]);
            $allData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Data without user filter:</h3>";
            echo "<pre>" . print_r($allData, true) . "</pre>";
        }
        
        exit;
    }
    
    echo "<h3>✅ Passenger Data Found:</h3>";
    echo "<pre>" . print_r($passengerData, true) . "</pre>";
    
    // Prepare API request
    $mfreNum = $passengerData[0]['mf_reference'];
    $voidWindow = $passengerData[0]['void_window'];
    $currentDateTime = new DateTime();
    
    // Check void window
    if (!empty($voidWindow)) {
        $voidWindowDateTime = new DateTime($voidWindow);
        if ($currentDateTime > $voidWindowDateTime) {
            echo "⏰ Void window EXPIRED - Using RefundQuote<br>";
            $ptrType = 'RefundQuote';
        } else {
            echo "✅ Void window ACTIVE - Using VoidQuote<br>";
            $ptrType = 'VoidQuote';
        }
    } else {
        echo "⚠️ No void window - Using RefundQuote<br>";
        $ptrType = 'RefundQuote';
    }
    
    // Prepare passenger array
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
        'AdditionalNote' => "Refund quote request for {$ptrType}"
    );
    
    echo "<h3>📤 API Request:</h3>";
    echo "<pre>" . json_encode($requestData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Make API call
    echo "<h3>🚀 Making API Call...</h3>";
    $endpoint = 'PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];
    
    echo "<h3>📥 API Response:</h3>";
    echo "HTTP Code: $httpCode<br>";
    echo "<pre>" . $response . "</pre>";
    
    // Parse response
    if ($response) {
        $responseData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<h3>🔍 Parsed Response:</h3>";
            echo "<pre>" . print_r($responseData, true) . "</pre>";
            
            if (isset($responseData['Success']) && $responseData['Success']) {
                echo "<div style='background: green; color: white; padding: 10px;'>";
                echo "✅ SUCCESS! RefundQuote/VoidQuote worked!";
                echo "</div>";
            } else {
                echo "<div style='background: red; color: white; padding: 10px;'>";
                echo "❌ FAILED! This confirms the API issue.";
                if (!empty($responseData['Message'])) {
                    echo "<br>Message: " . $responseData['Message'];
                }
                echo "</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 