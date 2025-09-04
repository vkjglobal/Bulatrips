<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing service transaction fees function step by step...\n";

// Handle CLI execution where HTTP_HOST is not set
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'travelsite');
    
    echo "Database credentials set for localhost\n";
    
    try {
        echo "Attempting to connect to database...\n";
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        echo "Database connection successful!\n";
        
        // Test the specific query from getServiceTransactionFees step by step
        echo "Step 1: Testing refund_fee query...\n";
        $stmt1 = $conn->prepare("SELECT value FROM settings WHERE id = 9");
        $stmt1->execute();
        $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        echo "Refund fee result: " . json_encode($result1) . "\n";
        
        echo "Step 2: Testing refund_addition query...\n";
        $stmt2 = $conn->prepare("SELECT value FROM settings WHERE id = 10");
        $stmt2->execute();
        $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "Refund addition result: " . json_encode($result2) . "\n";
        
        echo "Step 3: Testing combined query...\n";
        $query = "SELECT 
                    (SELECT value FROM settings WHERE id = 9) as refund_fee,
                    (SELECT value FROM settings WHERE id = 10) as refund_addition";
        
        $stmt3 = $conn->prepare($query);
        $stmt3->execute();
        $result3 = $stmt3->fetch(PDO::FETCH_ASSOC);
        
        echo "Combined result: " . json_encode($result3) . "\n";
        
        // Test the exact logic from getServiceTransactionFees
        echo "Step 4: Testing exact logic...\n";
        $refund_fee = floatval($result3['refund_fee'] ?? 0);
        $refund_addition = floatval($result3['refund_addition'] ?? 0);
        
        echo "Refund fee (float): " . $refund_fee . "\n";
        echo "Refund addition (float): " . $refund_addition . "\n";
        
        $final_result = [
            'refund_fee' => $refund_fee,
            'refund_addition' => $refund_addition
        ];
        
        echo "Final result: " . json_encode($final_result) . "\n";
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "HTTP_HOST is not localhost: " . $_SERVER['HTTP_HOST'] . "\n";
}
?>
