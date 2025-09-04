<?php
// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

$bookingId = 176;

try {
    $stmt = $conn->prepare('UPDATE travellers_details SET ticket_status = "Ticketed" WHERE flight_booking_id = ?');
    $stmt->execute([$bookingId]);
    
    echo "Successfully updated " . $stmt->rowCount() . " passengers to Ticketed status for booking ID: " . $bookingId;
    
    // Verify the update
    $stmt = $conn->prepare('SELECT id, first_name, last_name, ticket_status FROM travellers_details WHERE flight_booking_id = ?');
    $stmt->execute([$bookingId]);
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n\nUpdated passengers:\n";
    foreach ($passengers as $passenger) {
        echo "- " . $passenger['first_name'] . " " . $passenger['last_name'] . ": " . $passenger['ticket_status'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
