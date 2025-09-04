<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...\n";

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
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    
    try {
        echo "Attempting to connect to database...\n";
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        echo "Database connection successful!\n";
        
        // Test a simple query
        echo "Testing simple query...\n";
        $stmt = $conn->query("SELECT COUNT(*) as count FROM settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Settings table count: " . $result['count'] . "\n";
        
        // Test the specific query from getServiceTransactionFees
        echo "Testing service transaction fees query...\n";
        $query = "SELECT 
                    (SELECT value FROM settings WHERE id = 9) as refund_fee,
                    (SELECT value FROM settings WHERE id = 10) as refund_addition";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Service transaction fees: " . json_encode($result) . "\n";
        
    } catch (PDOException $e) {
        echo "Database connection error: " . $e->getMessage() . "\n";
    }
} else {
    echo "HTTP_HOST is not localhost: " . $_SERVER['HTTP_HOST'] . "\n";
}
?>
