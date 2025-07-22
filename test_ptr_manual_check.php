<?php
/**
 * Manual PTR Status Checker
 * Use this tool to manually check specific PTR status and debug issues
 * 
 * Usage: 
 * - Visit: test_ptr_manual_check.php?ptr_id=15513
 * - Or: test_ptr_manual_check.php?mf_ref=MF30750325
 */

include_once __DIR__ . '/includes/class.SearchPtrCron.php';
include_once __DIR__ . '/includes/common_const.php';

$objPTR = new SearchPtrCron();

// Get parameters
$ptrId = isset($_GET['ptr_id']) ? intval($_GET['ptr_id']) : null;
$mfRef = isset($_GET['mf_ref']) ? trim($_GET['mf_ref']) : null;
$testMode = isset($_GET['test']) ? true : false;

?>
<!DOCTYPE html>
<html>
<head>
    <title>PTR Manual Checker - Bulatrips</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; overflow-x: auto; }
        .form-container { background: #f8f9fa; padding: 20px; margin: 20px 0; border: 1px solid #dee2e6; }
    </style>
</head>
<body>

<h1>PTR Manual Status Checker</h1>

<div class="form-container">
    <h3>Check PTR Status</h3>
    <form method="GET">
        <p>
            <label>PTR ID:</label><br>
            <input type="number" name="ptr_id" value="<?php echo htmlspecialchars($ptrId ?? ''); ?>" placeholder="e.g., 15513">
        </p>
        <p>
            <label>MF Reference:</label><br>
            <input type="text" name="mf_ref" value="<?php echo htmlspecialchars($mfRef ?? ''); ?>" placeholder="e.g., MF30750325">
        </p>
        <p>
            <input type="checkbox" name="test" value="1" <?php echo $testMode ? 'checked' : ''; ?>>
            <label>Test Mode (Enhanced Debugging)</label>
        </p>
        <p>
            <button type="submit">Check PTR Status</button>
        </p>
    </form>
</div>

<?php if ($ptrId || $mfRef): ?>

<h2>PTR Status Results</h2>

<?php
// Get PTR details from database
$ptrDetails = $objPTR->getSpecificPTR($ptrId, $mfRef);

if (empty($ptrDetails)) {
    echo '<div class="error">❌ No PTR found in database with the given criteria.</div>';
} else {
    foreach ($ptrDetails as $ptr) {
        echo '<div class="info">';
        echo '<h3>Database Record Found</h3>';
        echo '<strong>PTR ID:</strong> ' . $ptr['ptr_id'] . '<br>';
        echo '<strong>MF Reference:</strong> ' . $ptr['mf_ref_num'] . '<br>';
        echo '<strong>PTR Type:</strong> ' . $ptr['ptr_type'] . '<br>';
        echo '<strong>Current Status:</strong> ' . $ptr['ptr_status'] . '<br>';
        echo '<strong>Created:</strong> ' . $ptr['created_date'] . '<br>';
        echo '<strong>SLA Minutes:</strong> ' . ($ptr['sla_minutes'] ?? 120) . '<br>';
        
        // Calculate elapsed time
        $createdTime = strtotime($ptr['created_date']);
        $currentTime = time();
        $elapsedMinutes = ($currentTime - $createdTime) / 60;
        $slaMinutes = $ptr['sla_minutes'] ?? 120;
        
        echo '<strong>Elapsed Time:</strong> ' . round($elapsedMinutes, 2) . ' minutes<br>';
        
        if ($elapsedMinutes > $slaMinutes) {
            echo '<strong style="color: red;">⚠️ SLA EXCEEDED by ' . round($elapsedMinutes - $slaMinutes, 2) . ' minutes</strong><br>';
        } else {
            echo '<strong style="color: green;">✅ Within SLA ('. round($slaMinutes - $elapsedMinutes, 2) .' minutes remaining)</strong><br>';
        }
        
        echo '</div>';
        
        // Now test API call
        echo '<h3>Testing API Call</h3>';
        
        $requestData = array(
            'ptrType' => ($ptr['ptr_type'] == 'Void') ? 'Void' : 'Refund',
            'MFRef' => $ptr['mf_ref_num'],
            'PTRId' => $ptr['ptr_id'],
            'Page' => 1
        );
        
        echo '<div class="info">';
        echo '<strong>API Request:</strong><br>';
        echo '<pre>' . json_encode($requestData, JSON_PRETTY_PRINT) . '</pre>';
        echo '</div>';
        
        // Make API call
        $result = $objPTR->callApi('Search/PostTicketingRequest', $requestData);
        
        echo '<div class="info">';
        echo '<strong>HTTP Status Code:</strong> ' . $result['httpCode'] . '<br>';
        if (!empty($result['curlError'])) {
            echo '<strong style="color: red;">CURL Error:</strong> ' . $result['curlError'] . '<br>';
        }
        echo '</div>';
        
        if ($result['responseData']) {
            $responseData = json_decode($result['responseData'], true);
            
            if (isset($responseData['Message']) && $responseData['Message'] == 'No records found.') {
                echo '<div class="error">';
                echo '<h4>❌ "No Records Found" Response</h4>';
                echo '<p>This PTR is returning "No records found" which indicates:</p>';
                echo '<ul>';
                echo '<li>PTR may be stuck in Mystifly system</li>';
                echo '<li>PTR may have been processed but not updated</li>';
                echo '<li>PTR may have expired from their search index</li>';
                echo '</ul>';
                
                if ($elapsedMinutes > ($slaMinutes + 30)) {
                    echo '<p style="color: red;"><strong>RECOMMENDATION:</strong> Mark this PTR as failed and contact Mystifly support.</p>';
                    
                    // Option to mark as failed
                    echo '<p><a href="?ptr_id=' . $ptr['ptr_id'] . '&mf_ref=' . $ptr['mf_ref_num'] . '&mark_failed=1" style="background: #dc3545; color: white; padding: 5px 10px; text-decoration: none;">Mark as Failed</a></p>';
                } else {
                    echo '<p style="color: orange;"><strong>RECOMMENDATION:</strong> Wait a bit more, PTR is still within acceptable timeframe.</p>';
                }
                echo '</div>';
            } else {
                echo '<div class="success">';
                echo '<h4>✅ API Response Received</h4>';
                echo '<pre>' . json_encode($responseData, JSON_PRETTY_PRINT) . '</pre>';
                echo '</div>';
                
                // Check PTR status
                if (isset($responseData['Data']['PTRDetail'][0]['PTRStatus'])) {
                    $apiStatus = $responseData['Data']['PTRDetail'][0]['PTRStatus'];
                    $resolution = $responseData['Data']['PTRDetail'][0]['Resolution'] ?? '';
                    
                    if ($apiStatus == 'Completed') {
                        echo '<div class="success"><strong>✅ PTR is COMPLETED!</strong> Resolution: ' . $resolution . '</div>';
                    } elseif ($apiStatus == 'InProcess') {
                        echo '<div class="warning"><strong>⏳ PTR is still IN PROCESS</strong></div>';
                    } else {
                        echo '<div class="info"><strong>Status:</strong> ' . $apiStatus . '</div>';
                    }
                }
            }
        } else {
            echo '<div class="error">❌ No response received from API</div>';
        }
        
        // Mark as failed if requested
        if (isset($_GET['mark_failed']) && $_GET['mark_failed'] == '1') {
            $isMarked = $objPTR->handleStuckPTR($ptr['id'], $ptr['ptr_id'], $ptr['mf_ref_num'], $ptr['ptr_type'], $slaMinutes);
            if ($isMarked) {
                echo '<div class="success">✅ PTR has been marked as failed in the database.</div>';
            } else {
                echo '<div class="error">❌ Could not mark PTR as failed.</div>';
            }
        }
    }
}
?>

<?php endif; ?>

<div class="info">
    <h3>Common PTR Issues & Solutions</h3>
    <ul>
        <li><strong>"No records found":</strong> PTR may be stuck in Mystifly system. Wait for SLA + 30 minutes, then mark as failed.</li>
        <li><strong>"InProcess" beyond SLA:</strong> Normal delay, but monitor closely. Contact Mystifly if beyond 4 hours.</li>
        <li><strong>HTTP 401/403:</strong> Check API credentials and bearer token.</li>
        <li><strong>Connection timeouts:</strong> Network issue or API overload.</li>
    </ul>
    
    <h3>Next Steps for Stuck PTRs:</h3>
    <ol>
        <li>Use this tool to verify PTR status</li>
        <li>If "No records found" beyond SLA + 30min, mark as failed</li>
        <li>Contact Mystifly support with PTR ID and MF Reference</li>
        <li>Provide customer with manual refund if necessary</li>
    </ol>
</div>

</body>
</html> 