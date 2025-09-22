<?php
/**
 * Test the protection logic for ticket updates
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

echo "<h2>🛡️ Ticket Update Protection Logic - Test</h2>";

echo "<h3>✅ Protection Implemented:</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>The system now checks before updating:</strong><br>";
echo "1. ✅ <strong>ticket_status</strong> - Skip if already has value<br>";
echo "2. ✅ <strong>e_ticket_number</strong> - Skip if already has value<br>";
echo "3. ✅ <strong>void_status</strong> - Skip if PTR void in process<br>";
echo "4. ✅ <strong>reissue_status</strong> - Skip if PTR reissue in process<br>";
echo "</div>";

echo "<h3>📊 Current Database State - Booking 180:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, ticket_status, e_ticket_number, void_status, reissue_status FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
    $stmt->execute();
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Ticket Status</th><th>E-Ticket</th><th>Void Status</th><th>Reissue Status</th><th>Protection</th></tr>";
    
    foreach ($passengers as $p) {
        $ticketStatus = !empty($p['ticket_status']) ? $p['ticket_status'] : '<span style="color: #999;">NULL</span>';
        $eTicket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: #999;">NULL</span>';
        $voidStatus = !empty($p['void_status']) ? $p['void_status'] : '<span style="color: #999;">NULL</span>';
        $reissueStatus = !empty($p['reissue_status']) ? $p['reissue_status'] : '<span style="color: #999;">NULL</span>';
        
        // Determine protection status
        $protected = false;
        $protectionReason = '';
        
        if (!empty($p['ticket_status'])) {
            $protected = true;
            $protectionReason = 'Has ticket_status';
        } elseif (!empty($p['e_ticket_number'])) {
            $protected = true;
            $protectionReason = 'Has e_ticket_number';
        } elseif (!empty($p['void_status'])) {
            $protected = true;
            $protectionReason = 'Void in process';
        } elseif (!empty($p['reissue_status'])) {
            $protected = true;
            $protectionReason = 'Reissue in process';
        } else {
            $protectionReason = 'Can be updated';
        }
        
        $protectionDisplay = $protected ? 
            "<span style='color: #dc3545; font-weight: bold;'>🛡️ PROTECTED</span><br><small>$protectionReason</small>" : 
            "<span style='color: #28a745; font-weight: bold;'>✅ UPDATEABLE</span><br><small>$protectionReason</small>";
        
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['first_name']} {$p['last_name']}</td>";
        echo "<td>$ticketStatus</td>";
        echo "<td>$eTicket</td>";
        echo "<td>$voidStatus</td>";
        echo "<td>$reissueStatus</td>";
        echo "<td>$protectionDisplay</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>🧪 How It Works:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Before Update Logic:</strong><br>";
echo "<code>";
echo "1. Check if passenger has ticket_status → Skip if YES<br>";
echo "2. Check if passenger has e_ticket_number → Skip if YES<br>";
echo "3. Check if passenger has void_status → Skip if YES<br>";
echo "4. Check if passenger has reissue_status → Skip if YES<br>";
echo "5. Only update if ALL above are empty/null";
echo "</code>";
echo "</div>";

echo "<h3>📝 Log Messages You'll See:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Protection Messages in tripConfirm.txt:</strong><br>";
echo "<code>";
echo "SKIPPED: Passenger ID 179 (Leah Acevedo) already has ticket_status='Ticketed' or e_ticket_number='TKT475560' - NOT UPDATING<br>";
echo "SKIPPED: Passenger ID 180 has PTR in process (void_status='InProcess', reissue_status='') - NOT UPDATING<br>";
echo "SUCCESS: Updated passenger ID 181 (New Passenger) with ticket TKT475562";
echo "</code>";
echo "</div>";

echo "<h3>🎯 Test the Protection:</h3>";
echo "<p>Now when you refresh the booking details page, passengers with existing ticket data won't be overwritten:</p>";
echo "<a href='http://localhost/bulatrips/flight-booking-details?booking_id=MF31554025' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Protection Logic</a>";

echo "<h3>✅ Benefits:</h3>";
echo "<ul>";
echo "<li>🛡️ <strong>Protects Cancelled Tickets:</strong> Won't overwrite void/refund status</li>";
echo "<li>🔄 <strong>Protects Reissue Process:</strong> Won't interfere with ongoing reissue</li>";
echo "<li>📋 <strong>Preserves Existing Data:</strong> Won't overwrite manually set ticket numbers</li>";
echo "<li>📊 <strong>Clear Logging:</strong> Shows exactly why updates were skipped</li>";
echo "</ul>";

?>
