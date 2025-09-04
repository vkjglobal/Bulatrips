<?php
// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');

$bookingId = 176;

try {
    // Check current ticket status
    $stmt = $conn->prepare('SELECT id, first_name, last_name, ticket_status, flight_booking_id FROM travellers_details WHERE flight_booking_id = ?');
    $stmt->execute([$bookingId]);
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current ticket status for booking ID: " . $bookingId . "\n";
    echo "Found " . count($passengers) . " passengers\n\n";
    
    foreach ($passengers as $passenger) {
        echo "- ID: " . $passenger['id'] . "\n";
        echo "  Name: " . $passenger['first_name'] . " " . $passenger['last_name'] . "\n";
        echo "  Ticket Status: " . $passenger['ticket_status'] . "\n";
        echo "  Flight Booking ID: " . $passenger['flight_booking_id'] . "\n\n";
    }
    
    // Also check if there are any passengers with different flight_booking_id
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM travellers_details WHERE ticket_status = "Ticketed"');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total passengers with Ticketed status: " . $result['total'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

