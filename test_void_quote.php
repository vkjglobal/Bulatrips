<?php
// Simple test script to debug VoidQuote
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...\n";

// Step 1: Check if files exist
echo "Step 1: Checking files...\n";
$files = [
    'includes/common_const.php',
    'includes/class.cancel.php',
    'includes/mock_mystifly.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file NOT FOUND\n";
        exit;
    }
}

// Step 2: Include files
echo "\nStep 2: Including files...\n";
try {
    include_once('includes/common_const.php');
    echo "✅ common_const.php included\n";
    echo "MOCK_MODE: " . (defined('MOCK_MODE') ? (MOCK_MODE ? 'true' : 'false') : 'NOT DEFINED') . "\n";
    
    include_once('includes/class.cancel.php');
    echo "✅ class.cancel.php included\n";
    
    include_once('includes/mock_mystifly.php');
    echo "✅ mock_mystifly.php included\n";
    
} catch (Exception $e) {
    echo "❌ Error including files: " . $e->getMessage() . "\n";
    exit;
}

// Step 3: Test MockMystifly class
echo "\nStep 3: Testing MockMystifly...\n";
try {
    $mockResponse = MockMystifly::getVoidQuoteResponse([]);
    echo "✅ Mock response generated: " . json_encode($mockResponse) . "\n";
} catch (Exception $e) {
    echo "❌ Error with MockMystifly: " . $e->getMessage() . "\n";
    exit;
}

// Step 4: Test Cancel class
echo "\nStep 4: Testing Cancel class...\n";
try {
    $objCancel = new Cancel();
    echo "✅ Cancel class instantiated\n";
    
    // Test service transaction fees
    $serviceFees = $objCancel->getServiceTransactionFees();
    echo "✅ Service fees: " . json_encode($serviceFees) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error with Cancel class: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit;
}

// Step 5: Test complete flow
echo "\nStep 5: Testing complete flow...\n";
try {
    $passengersArray = [
        [
            "firstName" => "Test",
            "lastName" => "User",
            "title" => "Mr",
            "eTicket" => "TEST123",
            "passengerType" => "ADT"
        ]
    ];
    
    $mockResponse = MockMystifly::getVoidQuoteResponse($passengersArray);
    $serviceFees = $objCancel->getServiceTransactionFees();
    
    $baseRefundAmount = $mockResponse['Data']['TotalRefundAmount'];
    $totalRefundAmount = $baseRefundAmount + $serviceFees['refund_fee'] + $serviceFees['refund_addition'];
    
    $response_New = array(
        'status' => 'success',
        'message' => 'Void Quote Received: InProcess Total Refundable Amount is: USD ' . $totalRefundAmount,
        'ptr_id' => $mockResponse['Data']['PTRId'],
        'ptr_status' => $mockResponse['Data']['PTRStatus'],
        'refundamount' => $totalRefundAmount,
        'currency' => 'USD',
        'admin_charges' => 0,
        'gst_charge' => 0,
        'voiding_fee' => 0,
        'voiding_window' => '',
        'sla_minutes' => 0,
        'booking_id' => '176',
        'passenger_details' => []
    );
    
    echo "✅ Complete response generated: " . json_encode($response_New) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error in complete flow: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit;
}

echo "\n🎉 All tests passed successfully!\n";
?>
