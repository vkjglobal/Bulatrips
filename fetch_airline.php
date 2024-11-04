<?php
// Database connection details
include('includes/dbConnect.php');

// Prepare and execute the query
$query = 'SELECT * FROM airline';
$stmt = $conn->query($query);

// Fetch the results as an associative array
$airline = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Return the results as JSON
// echo json_encode($airports);
echo json_encode($airline,JSON_INVALID_UTF8_IGNORE);
?>
