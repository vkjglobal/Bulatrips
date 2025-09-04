<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include_once('includes/dbConnect.php');
    echo "✅ Database connection successful\n";
    
    // Test query
    $stmt = $conn->prepare("SELECT COUNT(*) FROM travellers_details");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "✅ Found $count records in travellers_details table\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 