<?php
/**
 * Test reissue status display logic
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');
include_once('includes/class.cancel.php');

$objCancel = new Cancel();
$bookingId = 180;
$userId = 73; // Assuming this is the user ID

echo "<h2>🔄 Reissue Status Display Test</h2>";

echo "<h3>📊 Current Database State:</h3>";
try {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, ticket_status, e_ticket_number, reissue_status, reissue_ptr_id, cancel_type FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
    $stmt->execute();
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Ticket Status</th><th>E-Ticket</th><th>Reissue Status</th><th>Expected Display</th></tr>";
    
    foreach ($passengers as $p) {
        $reissueStatus = $p['reissue_status'] ?? null;
        $isReissueInProcess = ($reissueStatus === 'InProcess');
        
        // Determine expected display status
        if ($isReissueInProcess) {
            $expectedDisplay = '<span style="color: #ffc107; font-weight: bold;">Reissue In Progress</span>';
            $shouldBeDisabled = 'YES (Disabled)';
        } elseif (!empty($p['e_ticket_number'])) {
            $expectedDisplay = '<span style="color: #28a745; font-weight: bold;">Ticketed</span>';
            $shouldBeDisabled = 'NO (Selectable)';
        } else {
            $expectedDisplay = '<span style="color: #dc3545; font-weight: bold;">Not Ticketed</span>';
            $shouldBeDisabled = 'YES (Disabled)';
        }
        
        $ticketStatus = !empty($p['ticket_status']) ? $p['ticket_status'] : '<span style="color: #999;">NULL</span>';
        $eTicket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: #999;">NULL</span>';
        $reissueStatusDisplay = !empty($p['reissue_status']) ? $p['reissue_status'] : '<span style="color: #999;">NULL</span>';
        
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['first_name']} {$p['last_name']}</td>";
        echo "<td>$ticketStatus</td>";
        echo "<td>$eTicket</td>";
        echo "<td>$reissueStatusDisplay</td>";
        echo "<td>$expectedDisplay<br><small>($shouldBeDisabled)</small></td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database Error: " . $e->getMessage() . "</span><br>";
}

echo "<h3>🎯 Logic Explanation:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Status Priority (Top to Bottom):</strong><br>";
echo "1. 🚫 <strong>Cancelled</strong> - If passenger is cancelled<br>";
echo "2. 🔄 <strong>Reissue In Progress</strong> - If reissue_status = 'InProcess'<br>";
echo "3. ✅ <strong>Ticketed</strong> - If has e_ticket_number<br>";
echo "4. ❌ <strong>Not Ticketed</strong> - Default case<br><br>";
echo "<strong>Checkbox Logic:</strong><br>";
echo "• <strong>Disabled</strong> if: Cancelled OR Not Ticketed OR Reissue In Progress<br>";
echo "• <strong>Enabled</strong> only if: Ticketed AND No active PTR process";
echo "</div>";

echo "<h3>🔧 Manual Status Update (For Testing):</h3>";
echo "<form method='post' style='margin: 10px 0;'>";
echo "<label>Set Reissue Status for Passenger ID: </label>";
echo "<select name='passenger_id'>";
foreach ($passengers as $p) {
    echo "<option value='{$p['id']}'>{$p['first_name']} {$p['last_name']} (ID: {$p['id']})</option>";
}
echo "</select><br><br>";
echo "<label>Status: </label>";
echo "<select name='status'>";
echo "<option value='InProcess'>InProcess</option>";
echo "<option value='Completed'>Completed</option>";
echo "<option value=''>Clear Status</option>";
echo "</select><br><br>";
echo "<input type='submit' name='update_status' value='Update Status' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

// Handle status update
if (isset($_POST['update_status'])) {
    try {
        $passengerId = intval($_POST['passenger_id']);
        $status = $_POST['status'];
        
        $stmt = $conn->prepare('UPDATE travellers_details SET reissue_status = :status, cancel_type = :cancelType WHERE id = :id');
        $result = $stmt->execute([
            'status' => $status,
            'cancelType' => $status ? 'reissue' : null,
            'id' => $passengerId
        ]);
        
        if ($result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>Success!</strong> Updated passenger ID $passengerId with reissue_status = '$status'<br>";
            echo "Refresh the reissue page to see the changes.";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error:</strong> " . $e->getMessage();
        echo "</div>";
    }
}

echo "<h3>🧪 Test the Display:</h3>";
echo "<ol>";
echo "<li><strong>Update Status:</strong> Use the form above to set a passenger to 'InProcess'</li>";
echo "<li><strong>Visit Reissue Page:</strong> <a href='http://localhost/bulatrips/flight_booking_reissue?booking_id=180' target='_blank'>Reissue Page</a></li>";
echo "<li><strong>Check Display:</strong> Should show 'Reissue In Progress' with yellow badge</li>";
echo "<li><strong>Verify Checkbox:</strong> Should be disabled for passengers with reissue in progress</li>";
echo "</ol>";

?>
