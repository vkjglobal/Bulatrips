<?php
// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

$bookingId = 176;

try {
    // Update e_ticket_number to make passenger appear as "Ticketed"
    $stmt = $conn->prepare('UPDATE travellers_details SET e_ticket_number = "TEST123456789" WHERE flight_booking_id = ?');
    $stmt->execute([$bookingId]);
    
    echo "Successfully updated e_ticket_number for booking ID: " . $bookingId . "\n";
    echo "Updated " . $stmt->rowCount() . " passengers\n\n";
    
    // Verify the update
    $stmt = $conn->prepare('SELECT id, first_name, last_name, e_ticket_number FROM travellers_details WHERE flight_booking_id = ?');
    $stmt->execute([$bookingId]);
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Updated passengers:\n";
    foreach ($passengers as $passenger) {
        echo "- " . $passenger['first_name'] . " " . $passenger['last_name'] . ": e_ticket_number = " . $passenger['e_ticket_number'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

