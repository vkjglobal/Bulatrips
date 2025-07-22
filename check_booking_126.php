<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    echo "<h1>Booking 126 Analysis</h1>";
    
    // Get booking details
    $sql = "SELECT * FROM temp_booking WHERE id = 126";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo "❌ Booking 126 not found!";
        exit;
    }
    
    echo "<h3>📋 Booking Details:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Value</th><th>Analysis</th></tr>";
    
    // Key fields analysis
    $fields = [
        'mf_reference' => 'MF Reference',
        'booking_status' => 'Booking Status',
        'ticket_status' => 'Ticket Status',
        'fare_type' => 'Fare Type',
        'dep_date' => 'Departure Date',
        'void_window' => 'Void Window',
        'total_amount' => 'Total Amount',
        'booking_date' => 'Booking Date'
    ];
    
    $currentTime = time();
    
    foreach ($fields as $field => $label) {
        $value = $booking[$field] ?? 'N/A';
        $analysis = '';
        
        switch ($field) {
            case 'ticket_status':
                $analysis = ($value == 'Ticketed') ? '✅ Good for refund' : '❌ May block refund';
                break;
            case 'fare_type':
                $analysis = ($value == 'Non-Refundable') ? '❌ NON-REFUNDABLE!' : '✅ Refundable';
                break;
            case 'dep_date':
                $depTime = strtotime($value);
                $daysUntil = ceil(($depTime - $currentTime) / (60 * 60 * 24));
                $analysis = ($daysUntil > 0) ? "✅ $daysUntil days left" : "❌ Departed";
                break;
            case 'void_window':
                if ($value) {
                    $voidTime = strtotime($value);
                    $analysis = ($currentTime > $voidTime) ? '❌ EXPIRED' : '✅ Active';
                } else {
                    $analysis = '⚠️ No void window';
                }
                break;
            case 'booking_status':
                $analysis = ($value == 'Booked') ? '✅ Valid' : '❌ Invalid';
                break;
            default:
                $analysis = '📝 Info';
        }
        
        echo "<tr>";
        echo "<td><strong>$label</strong></td>";
        echo "<td>$value</td>";
        echo "<td>$analysis</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Get passenger details
    echo "<h3>👥 Passenger Details:</h3>";
    $sql = "SELECT * FROM travellers_details WHERE flight_booking_id = 126";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($passengers)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Name</th><th>Type</th><th>E-Ticket</th><th>Status</th></tr>";
        
        foreach ($passengers as $passenger) {
            echo "<tr>";
            echo "<td>{$passenger['first_name']} {$passenger['last_name']}</td>";
            echo "<td>{$passenger['passenger_type']}</td>";
            echo "<td>{$passenger['e_ticket_number']}</td>";
            echo "<td>" . (!empty($passenger['e_ticket_number']) ? '✅ Ticketed' : '❌ Not Ticketed') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Analysis conclusion
    echo "<h3>🔍 RefundQuote Failure Analysis:</h3>";
    echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    
    $blockers = [];
    
    if ($booking['fare_type'] == 'Non-Refundable') {
        $blockers[] = "❌ <strong>FARE TYPE: Non-Refundable</strong> - This is the main issue!";
    }
    
    if ($booking['ticket_status'] != 'Ticketed') {
        $blockers[] = "❌ Ticket Status: " . $booking['ticket_status'];
    }
    
    if (strtotime($booking['dep_date']) < $currentTime) {
        $blockers[] = "❌ Flight has already departed";
    }
    
    if (empty($blockers)) {
        echo "✅ No obvious blockers found. API issue or airline policy.";
    } else {
        echo "<h4>🚫 Refund Blockers Found:</h4>";
        foreach ($blockers as $blocker) {
            echo "<p>$blocker</p>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 