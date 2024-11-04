<?php
// Database connection details
include('includes/dbConnect.php');

// Prepare and execute the query
$query = 'SELECT * FROM airportlocations';
$stmt = $conn->query($query);

// Fetch the results as an associative array
$airports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the results as JSON
// echo json_encode($airports);
echo json_encode($airports,JSON_INVALID_UTF8_IGNORE);
?>
