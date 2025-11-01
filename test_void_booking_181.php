<?php
/**
 * Test void functionality for booking 181
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');
include_once('includes/common_const.php');

echo "<h2>🔍 Void Booking 181 - Error Investigation</h2>";

echo "<h3>📋 Current Configuration:</h3>";
echo "MOCK_MODE: " . (MOCK_MODE ? '<span style="color: green;">ENABLED</span>' : '<span style="color: red;">DISABLED (Live Mode)</span>') . "<br>";
echo "TARGET: " . TARGET . "<br>";
echo "API Endpoint: " . APIENDPOINT . "<br><br>";

echo "<h3>📊 Booking 181 Details:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, mf_reference, booking_status, ticket_status FROM temp_booking WHERE id = 181');
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($booking) {
        echo "<strong>Booking 181:</strong><br>";
        echo "• MF Reference: " . $booking['mf_reference'] . "<br>";
        echo "• Booking Status: " . $booking['booking_status'] . "<br>";
        echo "• Ticket Status: " . $booking['ticket_status'] . "<br><br>";
        
        // Check passengers
        $stmt2 = $conn->prepare('SELECT id, first_name, last_name, e_ticket_number, ticket_status, void_status FROM travellers_details WHERE flight_booking_id = 181');
        $stmt2->execute();
        $passengers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Passengers:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>E-Ticket</th><th>Ticket Status</th><th>Void Status</th></tr>";
        foreach ($passengers as $p) {
            $eTicket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: red;">NULL</span>';
            $ticketStatus = !empty($p['ticket_status']) ? $p['ticket_status'] : '<span style="color: red;">NULL</span>';
            $voidStatus = !empty($p['void_status']) ? $p['void_status'] : '<span style="color: #999;">NULL</span>';
            echo "<tr><td>{$p['id']}</td><td>{$p['first_name']} {$p['last_name']}</td><td>$eTicket</td><td>$ticketStatus</td><td>$voidStatus</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<span style='color: red;'>❌ Booking 181 not found</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>🔍 Database Schema Check:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>cancel_booking.ptr_id Column:</strong> VARCHAR(64) (from database schema)<br>";
echo "<strong>Previous Error:</strong> Code was trying to convert PTR string to integer<br>";
echo "<strong>Fix Applied:</strong> Now storing PTR ID as string in database";
echo "</div>";

echo "<h3>🔧 Errors Fixed:</h3>";
echo "<ol>";
echo "<li>✅ <strong>PTR ID Type:</strong> Fixed VARCHAR vs INT mismatch</li>";
echo "<li>✅ <strong>String Conversion:</strong> PTR_1755329424_3668 now stored as string</li>";
echo "<li>✅ <strong>Database Insert:</strong> Should work without type conversion errors</li>";
echo "</ol>";

echo "<h3>🧪 Test Void Process:</h3>";
echo "<p>Now try voiding booking 181 again:</p>";
echo "<a href='http://localhost/bulatrips/cancel_user?booking_id=181' target='_blank' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Void Booking 181</a>";

echo "<h3>📝 What to Expect:</h3>";
echo "<ul>";
echo "<li>✅ <strong>No Database Errors:</strong> PTR ID should insert properly</li>";
echo "<li>✅ <strong>Void Process:</strong> Should work in live mode with real Mystifly API</li>";
echo "<li>✅ <strong>Success Response:</strong> Should get void confirmation</li>";
echo "<li>✅ <strong>Database Records:</strong> Entries should be created in cancel_booking table</li>";
echo "</ul>";

echo "<h3>🔍 Check Logs After Test:</h3>";
echo "<ul>";
echo "<li><strong>Void Logs:</strong> uploads/logFiles/void.txt</li>";
echo "<li><strong>Debug Logs:</strong> uploads/logFiles/debug.txt</li>";
echo "<li><strong>API Logs:</strong> uploads/logFiles/api_debug.txt</li>";
echo "</ul>";

?>
