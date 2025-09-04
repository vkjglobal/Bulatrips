<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set HTTP_HOST for database connection
$_SERVER['HTTP_HOST'] = 'localhost';

try {
    include_once('includes/dbConnect.php');
    echo "✅ Database connection successful\n";
    
    // Check if columns already exist
    $stmt = $conn->prepare("SHOW COLUMNS FROM travellers_details LIKE 'void_status'");
    $stmt->execute();
    $voidStatusExists = $stmt->rowCount() > 0;
    
    $stmt = $conn->prepare("SHOW COLUMNS FROM travellers_details LIKE 'ptr_id'");
    $stmt->execute();
    $ptrIdExists = $stmt->rowCount() > 0;
    
    if (!$voidStatusExists) {
        $sql1 = "ALTER TABLE travellers_details ADD COLUMN void_status VARCHAR(50) DEFAULT NULL";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute();
        echo "✅ Added void_status column\n";
    } else {
        echo "ℹ️ void_status column already exists\n";
    }
    
    if (!$ptrIdExists) {
        $sql2 = "ALTER TABLE travellers_details ADD COLUMN ptr_id VARCHAR(50) DEFAULT NULL";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        echo "✅ Added ptr_id column\n";
    } else {
        echo "ℹ️ ptr_id column already exists\n";
    }
    
    echo "\n🎉 Database setup completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}
?> 