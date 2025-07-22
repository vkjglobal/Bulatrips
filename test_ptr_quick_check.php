<?php
/**
 * Quick PTR Test Script
 * Tests database connectivity and PTR functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PTR Quick Check - " . date('Y-m-d H:i:s') . "</h2>";

try {
    include_once __DIR__ . '/includes/class.SearchPtrCron.php';
    echo "✅ SearchPtrCron class loaded successfully<br>";
    
    $objPTR = new SearchPtrCron();
    echo "✅ SearchPtrCron object created successfully<br>";
    
    // Test database connectivity
    echo "<h3>Database Connectivity Test:</h3>";
    $inProcessPTRs = $objPTR->getBookCronIDs();
    echo "📊 Found " . count($inProcessPTRs) . " PTRs in InProcess status<br>";
    
    if (!empty($inProcessPTRs)) {
        echo "<h3>InProcess PTRs:</h3>";
        foreach ($inProcessPTRs as $ptr) {
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0;'>";
            echo "<strong>PTR ID:</strong> " . $ptr['ptr_id'] . " | ";
            echo "<strong>MF Ref:</strong> " . $ptr['mf_ref_num'] . " | ";
            echo "<strong>Type:</strong> " . $ptr['ptr_type'] . " | ";
            echo "<strong>Status:</strong> " . $ptr['ptr_status'] . " | ";
            echo "<strong>Created:</strong> " . $ptr['created_date'];
            echo "</div>";
        }
    } else {
        echo "ℹ️ No PTRs currently in InProcess status<br>";
    }
    
    // Test overdue PTRs
    echo "<h3>Overdue PTRs Check:</h3>";
    $overduePTRs = $objPTR->getOverduePTRs();
    echo "⚠️ Found " . count($overduePTRs) . " overdue PTRs<br>";
    
    if (!empty($overduePTRs)) {
        foreach ($overduePTRs as $ptr) {
            echo "<div style='background: #ffe6e6; padding: 10px; margin: 5px 0; border: 1px solid red;'>";
            echo "<strong>PTR ID:</strong> " . $ptr['ptr_id'] . " | ";
            echo "<strong>MF Ref:</strong> " . $ptr['mf_ref_num'] . " | ";
            echo "<strong>Elapsed:</strong> " . $ptr['elapsed_minutes'] . " minutes | ";
            echo "<strong>SLA:</strong> " . $ptr['sla_minutes'] . " minutes";
            echo "</div>";
        }
    }
    
    // Test specific PTR (your problem case)
    echo "<h3>Testing Specific PTR (15513):</h3>";
    $specificPTR = $objPTR->getSpecificPTR(15513, 'MF30750325');
    
    if (!empty($specificPTR)) {
        foreach ($specificPTR as $ptr) {
            echo "<div style='background: #e6f3ff; padding: 10px; margin: 5px 0;'>";
            echo "<strong>Found PTR in Database:</strong><br>";
            echo "PTR ID: " . $ptr['ptr_id'] . "<br>";
            echo "MF Ref: " . $ptr['mf_ref_num'] . "<br>";
            echo "Status: " . $ptr['ptr_status'] . "<br>";
            echo "Created: " . $ptr['created_date'] . "<br>";
            
            // Calculate elapsed time
            $created = strtotime($ptr['created_date']);
            $now = time();
            $elapsed = ($now - $created) / 60;
            echo "Elapsed: " . round($elapsed, 2) . " minutes<br>";
            
            if ($elapsed > 120) {
                echo "<strong style='color: red;'>⚠️ This PTR is beyond SLA!</strong><br>";
            }
            echo "</div>";
        }
    } else {
        echo "❌ PTR 15513 not found in database<br>";
    }
    
    // Test API connectivity
    echo "<h3>API Connectivity Test:</h3>";
    $testData = [
        'ptrType' => 'Void',
        'MFRef' => 'MF30750325',
        'PTRId' => 15513,
        'Page' => 1
    ];
    
    $result = $objPTR->callApi('Search/PostTicketingRequest', $testData);
    echo "HTTP Code: " . $result['httpCode'] . "<br>";
    
    if ($result['responseData']) {
        $response = json_decode($result['responseData'], true);
        if (isset($response['Message'])) {
            echo "API Message: " . $response['Message'] . "<br>";
        }
        echo "<details><summary>Full Response</summary><pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre></details>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Run database update: <code>mysql < update_ptr_database.sql</code></li>";
echo "<li>Use manual checker: <a href='test_ptr_manual_check.php?ptr_id=15513'>test_ptr_manual_check.php?ptr_id=15513</a></li>";
echo "<li>Contact Mystifly support with PTR ID 15513</li>";
echo "</ol>";
?> 