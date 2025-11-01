<?php
// Simple test script to debug the refund issue
header('Content-Type: application/json');

try {
    // Test basic functionality
    echo json_encode([
        'success' => true,
        'message' => 'Test script working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>