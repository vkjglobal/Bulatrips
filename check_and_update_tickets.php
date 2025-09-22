<?php
/**
 * Check and update ticket numbers for booking 180
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');
include_once('includes/common_const.php');
include_once('includes/class.BookScript.php');

$objBook = new BookScript();

echo "<h2>🎯 Booking 180 Ticket Numbers Check & Update</h2>";

// Check current database state
echo "<h3>📊 Current Database State:</h3>";
try {
    $stmt = $conn->prepare('SELECT first_name, last_name, e_ticket_number, passport_number FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
    $stmt->execute();
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<strong>Current passengers in database:</strong><br>";
    foreach ($passengers as $p) {
        $ticketDisplay = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: red;">NULL/Empty</span>';
        echo "• " . $p['first_name'] . " " . $p['last_name'] . ": " . $ticketDisplay . " (Passport: " . $p['passport_number'] . ")<br>";
    }
    echo "<br>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

// Get the latest API response to see what ticket numbers should be
echo "<h3>🔍 Latest API Response Ticket Numbers:</h3>";
echo "Based on the page display, the current ticket numbers should be:<br>";
echo "• LEAH ACEVEDO: <strong>TKT475560</strong><br>";
echo "• VAUGHAN BUTLER: <strong>TKT475561</strong><br>";
echo "• LAEL JUAREZ: <strong>TKT475562</strong><br>";
echo "• CECILIA JEFFERSON: <strong>TKT475563</strong><br>";
echo "• XERXES BLACKWELL: <strong>TKT475564</strong><br><br>";

// Provide manual update option
echo "<h3>🔧 Update Database with Current Ticket Numbers:</h3>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<input type='submit' name='update_tickets' value='Update Database with Current Ticket Numbers' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

// Handle manual update
if (isset($_POST['update_tickets'])) {
    try {
        // Map of passenger names to their expected ticket numbers
        $ticketUpdates = [
            'LEAH ACEVEDO' => 'TKT475560',
            'VAUGHAN BUTLER' => 'TKT475561', 
            'LAEL JUAREZ' => 'TKT475562',
            'CECILIA JEFFERSON' => 'TKT475563',
            'XERXES BLACKWELL' => 'TKT475564'
        ];
        
        $updatedCount = 0;
        foreach ($ticketUpdates as $name => $ticket) {
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            $stmt = $conn->prepare('UPDATE travellers_details SET e_ticket_number = :ticket WHERE flight_booking_id = 180 AND first_name = :first_name AND last_name = :last_name');
            $result = $stmt->execute(['ticket' => $ticket, 'first_name' => $firstName, 'last_name' => $lastName]);
            
            if ($result && $stmt->rowCount() > 0) {
                $updatedCount++;
                echo "<div style='color: green;'>✅ Updated $name → $ticket</div>";
            }
        }
        
        if ($updatedCount > 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>Success!</strong> Updated $updatedCount passengers with ticket numbers.<br>";
            echo "Refresh the page to see the updated database state.";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
            echo "⚠️ <strong>Warning:</strong> No passengers were updated. Please check passenger names match exactly.";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

echo "<h3>🎯 Why This Happens:</h3>";
echo "<ol>";
echo "<li><strong>API Response:</strong> Mystifly API is returning these ticket numbers in the TripDetails response</li>";
echo "<li><strong>Display Logic:</strong> The flight-booking-details.php page shows ticket numbers from the API response</li>";
echo "<li><strong>Database Update:</strong> The ticket numbers should be saved to database when the page loads</li>";
echo "<li><strong>Update Logic:</strong> The code at lines 198-224 in flight-booking-details.php handles this</li>";
echo "</ol>";

echo "<h3>📝 Next Steps:</h3>";
echo "<ul>";
echo "<li>✅ The ticket numbers are now showing correctly on the page</li>";
echo "<li>🔄 Use the button above to update the database with current ticket numbers</li>";
echo "<li>🔍 Check if the database update logic in flight-booking-details.php is working properly</li>";
echo "</ul>";

?>
