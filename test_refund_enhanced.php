<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

echo "<h1>Enhanced RefundQuote Test</h1>";

try {
    $conn = new PDO("mysql:host=localhost;dbname=travelsite", 'root', '');
    $objCancel = new Cancel();
    
    $bookingId = 126;
    $sql = "SELECT user_id FROM temp_booking WHERE id = 126";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $bookingInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $userId = $bookingInfo['user_id'];
    $passengerData = $objCancel->BookCancelUsers($bookingId, $userId);
    
    if (empty($passengerData)) {
        echo "❌ No passenger data found!";
        exit;
    }
    
    $mfreNum = $passengerData[0]['mf_reference'];
    
    // Try multiple enhanced request formats
    $testCases = [];
    
    // Test Case 1: Basic RefundQuote (current format)
    $testCases['Basic RefundQuote'] = array(
        'ptrType' => 'RefundQuote',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $passengerData[0]['first_name'],
                'lastName' => $passengerData[0]['last_name'],
                'title' => $passengerData[0]['title'],
                'eTicket' => $passengerData[0]['e_ticket_number'],
                'passengerType' => $passengerData[0]['passenger_type']
            )
        ),
        'AdditionalNote' => 'Refund quote request'
    );
    
    // Test Case 2: RefundQuote with additional details
    $testCases['Enhanced RefundQuote'] = array(
        'ptrType' => 'RefundQuote',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $passengerData[0]['first_name'],
                'lastName' => $passengerData[0]['last_name'],
                'title' => $passengerData[0]['title'],
                'eTicket' => $passengerData[0]['e_ticket_number'],
                'passengerType' => $passengerData[0]['passenger_type']
            )
        ),
        'AdditionalNote' => 'Refund quote request - Customer initiated',
        'RefundType' => 'Full',
        'Reason' => 'Customer Request',
        'RefundDetails' => array(
            'RefundAmount' => 'ToBeDetermined',
            'RefundReason' => 'Customer Request',
            'RefundType' => 'Full'
        )
    );
    
    // Test Case 3: Try VoidQuote (even though window expired)
    $testCases['VoidQuote Test'] = array(
        'ptrType' => 'VoidQuote',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $passengerData[0]['first_name'],
                'lastName' => $passengerData[0]['last_name'],
                'title' => $passengerData[0]['title'],
                'eTicket' => $passengerData[0]['e_ticket_number'],
                'passengerType' => $passengerData[0]['passenger_type']
            )
        ),
        'AdditionalNote' => 'Void quote test'
    );
    
    // Test Case 4: DirectRefund (if available)
    $testCases['DirectRefund Test'] = array(
        'ptrType' => 'DirectRefund',
        'mFRef' => $mfreNum,
        'AllowChildPassenger' => false,
        'passengers' => array(
            array(
                'firstName' => $passengerData[0]['first_name'],
                'lastName' => $passengerData[0]['last_name'],
                'title' => $passengerData[0]['title'],
                'eTicket' => $passengerData[0]['e_ticket_number'],
                'passengerType' => $passengerData[0]['passenger_type']
            )
        ),
        'AdditionalNote' => 'Direct refund test'
    );
    
    $endpoint = 'PostTicketingRequest';
    
    foreach ($testCases as $testName => $requestData) {
        echo "<h3>🧪 Testing: $testName</h3>";
        echo "<details><summary>Request Data</summary>";
        echo "<pre>" . json_encode($requestData, JSON_PRETTY_PRINT) . "</pre>";
        echo "</details>";
        
        $result = $objCancel->callApi($endpoint, $requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
        
        echo "HTTP Code: $httpCode<br>";
        
        if ($response) {
            $responseData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($responseData['Success']) && $responseData['Success']) {
                    echo "<div style='background: green; color: white; padding: 10px; margin: 10px 0;'>";
                    echo "✅ <strong>SUCCESS!</strong> $testName worked!<br>";
                    echo "Message: " . ($responseData['Data']['Message'] ?? 'Request processed successfully');
                    echo "</div>";
                } else {
                    echo "<div style='background: red; color: white; padding: 5px; margin: 5px 0;'>";
                    echo "❌ <strong>FAILED:</strong> " . ($responseData['Message'] ?? 'Unknown error');
                    echo "</div>";
                }
            } else {
                echo "<div style='background: orange; color: white; padding: 5px; margin: 5px 0;'>";
                echo "⚠️ <strong>JSON ERROR:</strong> " . json_last_error_msg();
                echo "</div>";
            }
        } else {
            echo "<div style='background: gray; color: white; padding: 5px; margin: 5px 0;'>";
            echo "❌ <strong>NO RESPONSE</strong>";
            echo "</div>";
        }
        
        echo "<hr>";
    }
    
    echo "<h3>📋 Summary & Next Steps:</h3>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>If ALL tests failed:</strong></p>";
    echo "<ul>";
    echo "<li>✉️ Contact Mystifly Support</li>";
    echo "<li>📋 Include MF Reference: $mfreNum</li>";
    echo "<li>🎫 Include Ticket: {$passengerData[0]['e_ticket_number']}</li>";
    echo "<li>📅 Mention fare type: Public (refundable)</li>";
    echo "<li>⏰ Explain void window expired but refund should work</li>";
    echo "</ul>";
    echo "<p><strong>If VoidQuote worked:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Use VoidQuote API despite expired window</li>";
    echo "<li>🔄 Update your system logic</li>";
    echo "</ul>";
    echo "<p><strong>Contact Details:</strong></p>";
    echo "<ul>";
    echo "<li>📧 Email: support@mystifly.com</li>";
    echo "<li>🎫 Subject: RefundQuote API Issue - MF$mfreNum</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 