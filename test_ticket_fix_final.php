<?php
/**
 * Test the final ticket update fix
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

echo "<h2>🎯 Final Test - Booking 180 Ticket Fix</h2>";

echo "<h3>📊 Current Database State (Before Fix):</h3>";
$stmt = $conn->prepare('SELECT id, first_name, last_name, e_ticket_number FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
$stmt->execute();
$beforePassengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>ID</th><th>Name</th><th>Current Ticket</th></tr>";
foreach ($beforePassengers as $p) {
    $ticket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: red;">NULL</span>';
    echo "<tr><td>{$p['id']}</td><td>{$p['first_name']} {$p['last_name']}</td><td>$ticket</td></tr>";
}
echo "</table>";

echo "<h3>🔧 The Problem Identified:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Root Cause:</strong> Multiple passengers have the same passport number (123123)<br>";
echo "<strong>Issue:</strong> When updating by passport number, all passengers with the same passport get the same ticket number (the last one processed)<br>";
echo "<strong>Solution:</strong> Update by passenger index/ID instead of passport number";
echo "</div>";

echo "<h3>✅ New Logic Implemented:</h3>";
echo "<ol>";
echo "<li><strong>Index Matching:</strong> Match API passenger[0] → Database passenger ID 179, etc.</li>";
echo "<li><strong>Direct ID Update:</strong> Update by specific passenger ID, not passport</li>";
echo "<li><strong>Name Fallback:</strong> If index fails, try name matching</li>";
echo "<li><strong>Better Logging:</strong> Clear success/failure messages</li>";
echo "</ol>";

echo "<h3>🧪 Test the Fix:</h3>";
echo "<p>Now refresh the booking details page to see if tickets update correctly:</p>";
echo "<a href='http://localhost/bulatrips/flight-booking-details?booking_id=MF31554025' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Booking Page</a>";

echo "<h3>📋 Expected Result:</h3>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Passenger ID</th><th>Name</th><th>Expected Ticket</th></tr>";
echo "<tr><td>179</td><td>Leah Acevedo</td><td><strong>TKT475560</strong></td></tr>";
echo "<tr><td>180</td><td>Vaughan Butler</td><td><strong>TKT475561</strong></td></tr>";
echo "<tr><td>181</td><td>Lael Juarez</td><td><strong>TKT475562</strong></td></tr>";
echo "<tr><td>182</td><td>Cecilia Jefferson</td><td><strong>TKT475563</strong></td></tr>";
echo "<tr><td>183</td><td>Xerxes Blackwell</td><td><strong>TKT475564</strong></td></tr>";
echo "</table>";

echo "<h3>🔍 Check Logs:</h3>";
echo "<p>After refreshing the page, check <code>uploads/logFiles/tripConfirm.txt</code> for entries like:</p>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
echo "SUCCESS: Updated passenger ID 179 (Leah Acevedo) with ticket TKT475560\n";
echo "SUCCESS: Updated passenger ID 180 (Vaughan Butler) with ticket TKT475561\n";
echo "SUCCESS: Updated passenger ID 181 (Lael Juarez) with ticket TKT475562";
echo "</pre>";

?>
