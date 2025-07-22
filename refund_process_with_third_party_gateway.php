<?php
echo "<h1>🔄 Direct Refund with Third-Party Payment Gateway</h1>";
echo "<p><em>آپ Windcave payment gateway استعمال کر رہے ہیں، Mystifly payment نہیں</em></p>";

echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo "<h3>⚠️ Important Understanding:</h3>";
echo "<p><strong>Your Setup:</strong></p>";
echo "<ul>";
echo "<li>✈️ <strong>Flight Booking:</strong> Mystifly API</li>";
echo "<li>💳 <strong>Payment Processing:</strong> Windcave Gateway</li>";
echo "<li>🔄 <strong>Refund Process:</strong> Two-Stage Process</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🎯 Two-Stage Refund Process</h2>";

echo "<div style='display: flex; gap: 20px; margin: 20px 0;'>";

// Stage 1
echo "<div style='flex: 1; background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
echo "<h3>🎫 Stage 1: Mystifly Direct Refund</h3>";
echo "<h4>What Happens:</h4>";
echo "<ul>";
echo "<li>✅ Ticket gets cancelled</li>";
echo "<li>✅ Credit Note generated</li>";
echo "<li>✅ Booking status → 'Refunded'</li>";
echo "<li>❌ <strong>NO money refund</strong> (کیونکہ payment Mystifly سے نہیں گئی)</li>";
echo "</ul>";
echo "<h4>API Response:</h4>";
echo "<pre style='font-size: 12px; background: #f0f0f0; padding: 10px;'>";
echo json_encode([
    "Success" => true,
    "Data" => [
        "PTRId" => 5285,
        "PTRType" => "Refund",
        "PTRStatus" => "Completed",
        "CreditNoteNumber" => "CN3100207",
        "TotalRefundAmount" => "605.05",
        "Currency" => "USD",
        "Note" => "Credit note only - manual refund needed"
    ]
], JSON_PRETTY_PRINT);
echo "</pre>";
echo "</div>";

// Stage 2  
echo "<div style='flex: 1; background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
echo "<h3>💰 Stage 2: Windcave Manual Refund</h3>";
echo "<h4>What You Need to Do:</h4>";
echo "<ul>";
echo "<li>🔍 Check Mystifly credit note amount</li>";
echo "<li>💳 Process manual refund via Windcave</li>";
echo "<li>💰 Money goes back to customer's card</li>";
echo "<li>✅ Complete refund process</li>";
echo "</ul>";
echo "<h4>Windcave Refund Process:</h4>";
echo "<pre style='font-size: 12px; background: #f0f0f0; padding: 10px;'>";
echo htmlspecialchars('
// Windcave Refund API
curl -X POST "https://uat.windcave.com/api/v1/refunds" \
  -H "Authorization: Basic YOUR_AUTH" \
  -d \'{
    "transactionId": "original_transaction_id",
    "amount": "605.05",
    "currency": "USD",
    "merchantReference": "refund_booking_126"
  }\'
');
echo "</pre>";
echo "</div>";

echo "</div>";

echo "<h2>📊 Complete Workflow Diagram</h2>";

echo "<div style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;'>";
echo "<h3>🔄 Step-by-Step Process:</h3>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th width='10%'>Step</th><th width='30%'>Action</th><th width='30%'>System</th><th width='30%'>Result</th>";
echo "</tr>";

$steps = [
    ['1', 'Customer requests refund', 'Your Website', 'Refund form submitted'],
    ['2', 'Smart system checks void window', 'Your Backend', 'Decides: VoidQuote or Direct Refund'],
    ['3', 'API call to Mystifly', 'Mystifly API', 'Ticket cancelled + Credit Note'],
    ['4', 'Store credit note details', 'Your Database', 'Refund record saved'],
    ['5', 'Manual Windcave refund', 'Windcave API', 'Money refunded to card'],
    ['6', 'Update refund status', 'Your Database', 'Status: Completed']
];

foreach ($steps as $step) {
    echo "<tr>";
    echo "<td style='text-align: center; font-weight: bold;'>" . $step[0] . "</td>";
    echo "<td>" . $step[1] . "</td>";
    echo "<td>" . $step[2] . "</td>";
    echo "<td>" . $step[3] . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h2>💻 Implementation Code</h2>";

echo "<h3>🔧 Modified refund_post_ticket.php (Enhanced):</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars('
// After successful Mystifly Direct Refund
if (isset($responseData["Success"]) && $responseData["Success"]) {
    $PTRId = $responseData["Data"]["PTRId"];
    $creditNoteNumber = $responseData["Data"]["CreditNoteNumber"];
    $totalRefundAmount = $responseData["Data"]["TotalRefundAmount"];
    
    // Store refund details for manual processing
    $refundData = [
        "booking_id" => $bookingId,
        "mystifly_ptr_id" => $PTRId,
        "credit_note_number" => $creditNoteNumber,
        "refund_amount" => $totalRefundAmount,
        "currency" => "USD",
        "mystifly_status" => "completed",
        "windcave_status" => "pending",
        "created_at" => date("Y-m-d H:i:s")
    ];
    
    // Insert into refund_processing table
    $objCancel->insertRefundForManualProcessing($refundData);
    
    $response_New = array(
        "success" => true,
        "message" => "Ticket cancelled successfully. Manual refund will be processed within 24 hours.",
        "refund_type" => "two_stage_refund",
        "data" => [
            "mystifly_ptr_id" => $PTRId,
            "credit_note" => $creditNoteNumber,
            "refund_amount" => $totalRefundAmount,
            "note" => "Credit note generated. Payment refund will be processed separately."
        ]
    );
}
');
echo "</pre>";

echo "<h3>🎯 Manual Windcave Refund Function:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars('
function processWindcaveRefund($originalTransactionId, $amount, $bookingId) {
    $url = WC_URL . "refunds";
    $username = WC_USERNAME;
    $password = WC_PASSWORD;
    
    $data = [
        "type" => "refund",
        "transactionId" => $originalTransactionId,
        "amount" => $amount,
        "currency" => "USD",
        "merchantReference" => "refund_" . $bookingId
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode("$username:$password")
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        "httpCode" => $httpCode,
        "response" => json_decode($response, true)
    ];
}
');
echo "</pre>";

echo "<h2>🗃️ Database Schema</h2>";

echo "<h3>📋 Required Table: refund_processing</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars('
CREATE TABLE refund_processing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    mystifly_ptr_id INT,
    credit_note_number VARCHAR(50),
    refund_amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT "USD",
    mystifly_status ENUM("pending", "completed", "failed") DEFAULT "pending",
    windcave_status ENUM("pending", "completed", "failed") DEFAULT "pending",
    windcave_transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mystifly_completed_at TIMESTAMP NULL,
    windcave_completed_at TIMESTAMP NULL,
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (mystifly_status, windcave_status)
);
');
echo "</pre>";

echo "<h2>📱 User Experience</h2>";

echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745; border-radius: 5px;'>";
echo "<h3>✅ Customer Journey:</h3>";
echo "<ol>";
echo "<li><strong>Request Refund:</strong> Customer clicks refund button</li>";
echo "<li><strong>Instant Response:</strong> \"Refund request submitted successfully\"</li>";
echo "<li><strong>Stage 1 Complete:</strong> \"Ticket cancelled. Credit note: CN3100207\"</li>";
echo "<li><strong>Stage 2 Pending:</strong> \"Payment refund will be processed within 24 hours\"</li>";
echo "<li><strong>Final Confirmation:</strong> \"$605.05 refunded to your card ending 1111\"</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📧 Email Notifications:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<p><strong>Email 1 (Immediate):</strong> Refund request received</p>";
echo "<p><strong>Email 2 (After Mystifly):</strong> Ticket cancelled successfully</p>";
echo "<p><strong>Email 3 (After Windcave):</strong> Payment refunded to your card</p>";
echo "</div>";

echo "<h2>⚡ Admin Dashboard</h2>";

echo "<h3>🎛️ Manual Refund Processing Panel:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<h4>Pending Refunds List:</h4>";
echo "<ul>";
echo "<li>📋 Booking ID: 126 | Amount: $605.05 | Credit Note: CN3100207</li>";
echo "<li>🔘 <strong>Action:</strong> [Process Windcave Refund] button</li>";
echo "<li>✅ Auto-updates status when completed</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h2>🎯 Summary: Direct Refund with Windcave</h2>";

echo "<div style='background: #d1ecf1; padding: 20px; border-left: 4px solid #bee5eb; border-radius: 5px;'>";
echo "<h3>✅ کیا کام کرے گا:</h3>";
echo "<ul>";
echo "<li>🎫 <strong>Mystifly Direct Refund:</strong> Ticket cancel + Credit Note</li>";
echo "<li>🤖 <strong>Smart Detection:</strong> Auto-chooses best API</li>";
echo "<li>📊 <strong>Two-Stage Process:</strong> Clear workflow</li>";
echo "<li>💳 <strong>Manual Windcave Refund:</strong> Actual money return</li>";
echo "</ul>";

echo "<h3>⚠️ کیا نہیں کرے گا:</h3>";
echo "<ul>";
echo "<li>❌ <strong>Automatic Money Refund:</strong> کیونکہ payment Mystifly سے نہیں</li>";
echo "<li>❌ <strong>Direct Integration:</strong> Manual step required</li>";
echo "</ul>";

echo "<h3>🔧 Solution:</h3>";
echo "<p><strong>Perfect for your setup!</strong> Two-stage process ensures:</p>";
echo "<ul>";
echo "<li>✅ Ticket properly cancelled via Mystifly</li>";
echo "<li>✅ Money safely refunded via Windcave</li>";
echo "<li>✅ Complete audit trail</li>";
echo "<li>✅ Professional customer experience</li>";
echo "</ul>";
echo "</div>";
?> 