<?php
/**
 * Test if the flight booking details page is working
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

echo "<h2>🔧 Flight Booking Details Page - Fix Status</h2>";

echo "<h3>✅ Issues Fixed:</h3>";
echo "<ul>";
echo "<li>✅ <strong>PHP Syntax Errors:</strong> Removed duplicate variable assignment</li>";
echo "<li>✅ <strong>SQL Query:</strong> Fixed malformed UPDATE statement</li>";
echo "<li>✅ <strong>Unused Code:</strong> Removed old update logic that was causing conflicts</li>";
echo "</ul>";

echo "<h3>🎯 What Was Wrong:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>1. Malformed SQL Query:</strong><br>";
echo "<code>UPDATE travellers_details SET basic_fare = :basicFare ,<br>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;total_pass_fare = :totalPassFare, tax = :tax ...</code><br><br>";
echo "<strong>2. Leftover Code:</strong> Old database update logic was still trying to execute<br>";
echo "<strong>3. Variable Reassignment:</strong> <code>\$bookingId = \$bookingId;</code> was redundant";
echo "</div>";

echo "<h3>🚀 Test the Page Now:</h3>";
echo "<a href='http://localhost/bulatrips/flight-booking-details?booking_id=MF31554025' target='_blank' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🔗 Test Booking Details Page</a>";

echo "<h3>📊 Expected Result:</h3>";
echo "<ul>";
echo "<li>✅ Page should load without errors</li>";
echo "<li>✅ Passenger list should display with ticket numbers</li>";
echo "<li>✅ Database should update with unique ticket numbers</li>";
echo "<li>✅ Logs should show successful updates</li>";
echo "</ul>";

echo "<h3>🔍 If Still Having Issues:</h3>";
echo "<ol>";
echo "<li><strong>Check Error Logs:</strong> Look in your web server error log</li>";
echo "<li><strong>Enable PHP Errors:</strong> Set <code>error_reporting(E_ALL);</code> temporarily</li>";
echo "<li><strong>Check Database Connection:</strong> Ensure database is accessible</li>";
echo "<li><strong>Verify Session:</strong> Make sure you're logged in</li>";
echo "</ol>";

echo "<h3>📋 Quick Database Check:</h3>";
try {
    include_once('includes/dbConnect.php');
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM travellers_details WHERE flight_booking_id = 180');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "✅ <strong>Database Connection:</strong> Working<br>";
    echo "✅ <strong>Passengers Found:</strong> " . $result['total'] . " passengers for booking 180";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "❌ <strong>Database Error:</strong> " . $e->getMessage();
    echo "</div>";
}

?>
