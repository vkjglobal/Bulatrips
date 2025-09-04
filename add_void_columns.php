<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include_once('includes/dbConnect.php');
    echo "✅ Database connection successful\n";
    
    // Add void_status column
    $sql1 = "ALTER TABLE travellers_details ADD COLUMN void_status VARCHAR(50) DEFAULT NULL COMMENT 'Void status: InProcess, Completed, Failed, NULL'";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute();
    echo "✅ Added void_status column\n";
    
    // Add ptr_id column
    $sql2 = "ALTER TABLE travellers_details ADD COLUMN ptr_id VARCHAR(50) DEFAULT NULL COMMENT 'PTR ID from Mystifly for void requests'";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    echo "✅ Added ptr_id column\n";
    
    // Add indexes for better performance
    $sql3 = "CREATE INDEX idx_void_status ON travellers_details(void_status)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute();
    echo "✅ Added void_status index\n";
    
    $sql4 = "CREATE INDEX idx_ptr_id ON travellers_details(ptr_id)";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->execute();
    echo "✅ Added ptr_id index\n";
    
    echo "\n🎉 Database columns added successfully!\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Columns already exist\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?> 