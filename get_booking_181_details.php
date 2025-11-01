<?php
/**
 * Get exact details for booking 181 to include in Mystifly email
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

echo "<h2>📧 Booking 181 Details for Mystifly Email</h2>";

try {
    // Get booking details
    $stmt = $conn->prepare('SELECT * FROM temp_booking WHERE id = 181');
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        echo "<h3>🎫 Booking Information:</h3>";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Booking ID:</strong> 181<br>";
        echo "<strong>MF Reference:</strong> " . $booking['mf_reference'] . "<br>";
        echo "<strong>Booking Status:</strong> " . $booking['booking_status'] . "<br>";
        echo "<strong>Ticket Status:</strong> " . $booking['ticket_status'] . "<br>";
        echo "<strong>Departure Date:</strong> " . $booking['dep_date'] . "<br>";
        echo "<strong>Route:</strong> " . $booking['dep_location'] . " → " . $booking['arrival_location'] . "<br>";
        echo "<strong>Trip Type:</strong> " . $booking['air_trip_type'] . "<br>";
        echo "<strong>Fare Type:</strong> " . $booking['fare_type'] . "<br>";
        echo "</div>";
        
        // Get passenger details
        $stmt2 = $conn->prepare('SELECT * FROM travellers_details WHERE flight_booking_id = 181');
        $stmt2->execute();
        $passengers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>👥 Passenger Details:</h3>";
        echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        foreach ($passengers as $p) {
            echo "<strong>Passenger " . $p['id'] . ":</strong><br>";
            echo "• Name: " . $p['title'] . " " . $p['first_name'] . " " . $p['last_name'] . "<br>";
            echo "• Type: " . $p['passenger_type'] . "<br>";
            echo "• E-Ticket: " . ($p['e_ticket_number'] ?: 'NULL') . "<br>";
            echo "• Ticket Status: " . ($p['ticket_status'] ?: 'NULL') . "<br>";
            echo "• Passport: " . $p['passport_number'] . "<br><br>";
        }
        echo "</div>";
        
        echo "<h3>📝 Email Content for Mystifly:</h3>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Copy this content for your email:</strong><br><br>";
        
        echo "<strong>Subject:</strong> API Error: 500 Internal Server Error on Staging Environment - PostTicketingRequest Endpoint<br><br>";
        
        echo "<strong>MF Reference:</strong> " . $booking['mf_reference'] . "<br>";
        echo "<strong>Booking ID:</strong> 181<br>";
        echo "<strong>Error:</strong> 500 Internal Server Error<br>";
        echo "<strong>Endpoint:</strong> https://restapidemo.myfarebox.com/api/PostTicketingRequest<br>";
        echo "<strong>Operation:</strong> RefundQuote<br><br>";
        
        echo "<strong>Request Payload:</strong><br>";
        echo "<pre style='background: #f1f1f1; padding: 10px; border-radius: 3px;'>";
        echo json_encode([
            "ptrType" => "RefundQuote",
            "mFRef" => $booking['mf_reference'],
            "AllowChildPassenger" => false,
            "passengers" => [
                [
                    "firstName" => $passengers[0]['first_name'],
                    "lastName" => $passengers[0]['last_name'], 
                    "title" => $passengers[0]['title'],
                    "eTicket" => $passengers[0]['e_ticket_number'],
                    "passengerType" => $passengers[0]['passenger_type']
                ]
            ],
            "AdditionalNote" => "Refund quote request - user requested refund"
        ], JSON_PRETTY_PRINT);
        echo "</pre>";
        
        echo "<strong>Response Received:</strong><br>";
        echo "<pre style='background: #f1f1f1; padding: 10px; border-radius: 3px;'>";
        echo json_encode([
            "Data" => null,
            "Success" => false,
            "Message" => "The remote server returned an error: (500) Internal Server Error."
        ], JSON_PRETTY_PRINT);
        echo "</pre>";
        
        echo "</div>";
        
    } else {
        echo "<span style='color: red;'>❌ Booking 181 not found</span>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span>";
}

echo "<h3>📧 Mystifly Support Contact:</h3>";
echo "<ul>";
echo "<li><strong>Email:</strong> support@mystifly.com</li>";
echo "<li><strong>Developer Portal:</strong> https://developer.mystifly.com</li>";
echo "<li><strong>Priority:</strong> HIGH (Production Impact)</li>";
echo "</ul>";

echo "<h3>🔄 Temporary Solution:</h3>";
echo "<p>Until Mystifly API is stable, you can continue testing with:</p>";
echo "<pre style='background: #d4edda; padding: 10px; border-radius: 3px;'>";
echo "define(\"MOCK_MODE\", true); // in includes/common_const.php";
echo "</pre>";

?>
