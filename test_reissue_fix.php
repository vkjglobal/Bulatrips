<?php
/**
 * Test the reissue quote process fix
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

echo "<h2>🔧 Reissue Quote Process - Database Error Fix</h2>";

echo "<h3>❌ Previous Error:</h3>";
echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<code>SQLSTATE[HY000]: General error: 1366 Incorrect integer value: 'ReissueQuote request submitted' for column 'cancel_status' at row 1</code>";
echo "</div>";

echo "<h3>🔍 Root Cause:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Parameter Order Issue in insCncelSts() function call:</strong><br><br>";
echo "<strong>Function Signature:</strong><br>";
echo "<code>insCncelSts(\$bookingId, \$userId, \$precancelsts, \$errorCode, \$mfreNum, \$traceId, \$http_code_response, \$PTRId, \$PTRType, \$SLAInMinutes, \$PTRStatus, \$VoidingWindow, \$ticket_num, \$AdminCharges, \$GSTCharge, \$TotalVoidingFee, \$TotalRefundAmount, \$Currency, <strong style='color: red;'>\$cancel_status</strong>, \$meesage_new, \$traveller_id)</code><br><br>";
echo "<strong>Issue:</strong> The message string was being passed in the \$cancel_status position (which expects 0 or 1)";
echo "</div>";

echo "<h3>✅ Fix Applied:</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Files Fixed:</strong><br>";
echo "1. ✅ <strong>reissue_quote_process.php</strong> - Line 237-242<br>";
echo "2. ✅ <strong>reissue_accept_quote.php</strong> - Line 116-121 and 133-138<br><br>";
echo "<strong>Parameter Correction:</strong><br>";
echo "• Added missing \$AdminCharges = 0<br>";
echo "• Added missing \$GSTCharge = 0<br>";
echo "• Added missing \$TotalVoidingFee = 0<br>";
echo "• Added missing \$TotalRefundAmount = 0<br>";
echo "• Proper \$cancel_status = 0 (integer)<br>";
echo "• Message moved to correct \$meesage_new position";
echo "</div>";

echo "<h3>🧪 Test the Fix:</h3>";
echo "<ol>";
echo "<li><strong>Test Reissue Page:</strong> <a href='http://localhost/bulatrips/flight_booking_reissue?booking_id=180' target='_blank'>Reissue Booking 180</a></li>";
echo "<li><strong>Select passengers</strong> and submit reissue request</li>";
echo "<li><strong>Check for errors</strong> - should work without database errors now</li>";
echo "</ol>";

echo "<h3>📊 Expected Behavior:</h3>";
echo "<ul>";
echo "<li>✅ <strong>No SQL Errors:</strong> Database insert should work properly</li>";
echo "<li>✅ <strong>Proper Logging:</strong> Success messages in reissueQuote.txt</li>";
echo "<li>✅ <strong>Database Records:</strong> Entries in cancel_booking table</li>";
echo "<li>✅ <strong>Email Sent:</strong> Confirmation email to customer</li>";
echo "</ul>";

echo "<h3>🔍 Check Database After Test:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, booking_id, ptr_type, ptr_status, cancel_status, message FROM cancel_booking WHERE booking_id = 180 AND ptr_type = "Reissue" ORDER BY id DESC LIMIT 3');
    $stmt->execute();
    $reissueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($reissueRecords)) {
        echo "<strong>Recent Reissue Records:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Booking ID</th><th>PTR Type</th><th>PTR Status</th><th>Cancel Status</th><th>Message</th></tr>";
        foreach ($reissueRecords as $record) {
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['booking_id']}</td>";
            echo "<td>{$record['ptr_type']}</td>";
            echo "<td>{$record['ptr_status']}</td>";
            echo "<td>{$record['cancel_status']}</td>";
            echo "<td>{$record['message']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<em>No reissue records found yet. Test the reissue process to create records.</em>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>📝 What to Look For:</h3>";
echo "<ul>";
echo "<li><strong>cancel_status</strong> should be <strong>0</strong> (integer)</li>";
echo "<li><strong>message</strong> should be <strong>'ReissueQuote request submitted'</strong></li>";
echo "<li><strong>ptr_type</strong> should be <strong>'Reissue'</strong></li>";
echo "<li><strong>ptr_status</strong> should be <strong>'InProcess'</strong></li>";
echo "</ul>";

?>
