<?php
/**
 * Check reissue status for booking 180
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

echo "<h2>🔄 Reissue Status Check - Booking 180</h2>";

echo "<h3>📊 Current Database State:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, ticket_status, e_ticket_number, reissue_status, reissue_ptr_id, cancel_type FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
    $stmt->execute();
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Ticket Status</th><th>E-Ticket</th><th>Reissue Status</th><th>PTR ID</th><th>Cancel Type</th><th>Display Status</th></tr>";
    
    foreach ($passengers as $p) {
        $ticketStatus = !empty($p['ticket_status']) ? $p['ticket_status'] : '<span style="color: #999;">NULL</span>';
        $eTicket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: #999;">NULL</span>';
        $reissueStatus = !empty($p['reissue_status']) ? $p['reissue_status'] : '<span style="color: #999;">NULL</span>';
        $reissuePtrId = !empty($p['reissue_ptr_id']) ? $p['reissue_ptr_id'] : '<span style="color: #999;">NULL</span>';
        $cancelType = !empty($p['cancel_type']) ? $p['cancel_type'] : '<span style="color: #999;">NULL</span>';
        
        // Determine what should be displayed
        $isReissueInProcess = ($p['reissue_status'] === 'InProcess');
        $isCancelled = (strtolower($p['ticket_status'] ?? '') === 'cancelled');
        
        if ($isCancelled) {
            $displayStatus = '<span style="color: #dc3545; font-weight: bold;">Cancelled</span>';
        } elseif ($isReissueInProcess) {
            $displayStatus = '<span style="color: #ffc107; font-weight: bold;">Reissue In Progress</span>';
        } elseif (!empty($p['e_ticket_number'])) {
            $displayStatus = '<span style="color: #28a745; font-weight: bold;">Ticketed</span>';
        } else {
            $displayStatus = '<span style="color: #dc3545; font-weight: bold;">Not Ticketed</span>';
        }
        
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['first_name']} {$p['last_name']}</td>";
        echo "<td>$ticketStatus</td>";
        echo "<td>$eTicket</td>";
        echo "<td>$reissueStatus</td>";
        echo "<td>$reissuePtrId</td>";
        echo "<td>$cancelType</td>";
        echo "<td>$displayStatus</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>🔍 Check Cancel Booking Records:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, ptr_type, ptr_status, cancel_status, message, created_date FROM cancel_booking WHERE booking_id = 180 AND ptr_type = "Reissue" ORDER BY id DESC LIMIT 5');
    $stmt->execute();
    $cancelRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($cancelRecords)) {
        echo "<strong>Recent Reissue Records in cancel_booking:</strong><br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr><th>ID</th><th>PTR Type</th><th>PTR Status</th><th>Cancel Status</th><th>Message</th><th>Created</th></tr>";
        foreach ($cancelRecords as $record) {
            $cancelStatusDisplay = $record['cancel_status'] === '0' ? '0 (In Process)' : '1 (Success)';
            echo "<tr>";
            echo "<td>{$record['id']}</td>";
            echo "<td>{$record['ptr_type']}</td>";
            echo "<td>{$record['ptr_status']}</td>";
            echo "<td>$cancelStatusDisplay</td>";
            echo "<td>{$record['message']}</td>";
            echo "<td>{$record['created_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<em>No reissue records found in cancel_booking table.</em>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>✅ What Should Happen:</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>After Reissue Request Submitted:</strong><br>";
echo "1. ✅ <strong>travellers_details.reissue_status</strong> = 'InProcess'<br>";
echo "2. ✅ <strong>travellers_details.cancel_type</strong> = 'reissue'<br>";
echo "3. ✅ <strong>cancel_booking record</strong> created with ptr_type = 'Reissue'<br>";
echo "4. ✅ <strong>Page display</strong> shows 'Reissue In Progress' status<br>";
echo "5. ✅ <strong>Email sent</strong> to customer with confirmation";
echo "</div>";

echo "<h3>🧪 Test the Fix:</h3>";
echo "<ol>";
echo "<li><strong>Visit Reissue Page:</strong> <a href='http://localhost/bulatrips/flight_booking_reissue?booking_id=180' target='_blank'>Reissue Page</a></li>";
echo "<li><strong>Check Status Display:</strong> Should show 'Reissue In Progress' for submitted passengers</li>";
echo "<li><strong>Verify Database:</strong> Refresh this page to see updated records</li>";
echo "</ol>";

echo "<h3>🎯 Next Steps:</h3>";
echo "<ul>";
echo "<li>🔄 <strong>Cron Job:</strong> Run cronSearchPtr.php to process the reissue quotes</li>";
echo "<li>📧 <strong>Email Check:</strong> Customer should receive quote options email</li>";
echo "<li>✅ <strong>Status Updates:</strong> Reissue status will change from 'InProcess' to 'Completed'</li>";
echo "</ul>";

?>
