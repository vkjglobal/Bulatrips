<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing service transaction fees function...\n";

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

echo "Files included successfully\n";

try {
    $objCancel = new Cancel();
    echo "Cancel class instantiated successfully\n";
    
    echo "About to call getServiceTransactionFees()...\n";
    $serviceFees = $objCancel->getServiceTransactionFees();
    echo "Service fees fetched: " . json_encode($serviceFees) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
