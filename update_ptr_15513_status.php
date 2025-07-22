<?php
// Database connection
include_once __DIR__ . '/includes/class.Dbconnect.php';

try {
    $db = new Dbconnect();
    $conn = $db->getConnection();

    // Update PTR status to 'Completed'
    $query = "UPDATE cancel_booking SET ptr_status = 'Completed', failure_reason = NULL, failed_at = NULL WHERE ptr_id = 15513";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    echo "PTR 15513 status updated to 'Completed'.";
} catch (Exception $e) {
    echo "Error updating PTR status: " . $e->getMessage();
}
?> 