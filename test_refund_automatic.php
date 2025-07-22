<?php
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

// Test smart quote selection logic (No Direct Refund)
$objCancel = new Cancel();

// Test booking details
$bookingId = 126;
$userId = 73;

echo "<h1>🎯 Smart Quote Selection System</h1>";
echo "<h2>Booking ID: $bookingId</h2>";
echo "<p><em>One CTA → Smart Choice: VoidQuote or RefundQuote (No Direct Refund)</em></p>";

// Get passenger data
$passengerData = $objCancel->BookCancelUsers($bookingId, $userId);
if (empty($passengerData)) {
    echo "❌ No passenger data found!";
    exit;
}

$firstPassenger = $passengerData[0];
$voidWindow = $firstPassenger['void_window'];
$currentDateTime = new DateTime();

echo "<h3>📊 Smart Analysis:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><td><strong>MF Reference:</strong></td><td>" . $firstPassenger['mf_reference'] . "</td></tr>";
echo "<tr><td><strong>Ticket Status:</strong></td><td>" . $firstPassenger['ticket_status'] . "</td></tr>";
echo "<tr><td><strong>Void Window:</strong></td><td>" . $voidWindow . "</td></tr>";
echo "<tr><td><strong>Current Time:</strong></td><td>" . $currentDateTime->format('Y-m-d H:i:s') . "</td></tr>";

$useVoidQuote = false;
$useRefundQuote = false;
$selectedAPI = 'RefundQuote'; // Default

if (!empty($voidWindow)) {
    $voidWindowDateTime = new DateTime($voidWindow);
    
    if ($currentDateTime <= $voidWindowDateTime) {
        $useVoidQuote = true;
        $selectedAPI = 'VoidQuote';
        $timeDiff = $currentDateTime->diff($voidWindowDateTime);
        echo "<tr><td><strong>Void Status:</strong></td><td style='color: green;'>✅ ACTIVE</td></tr>";
        echo "<tr><td><strong>Time Remaining:</strong></td><td style='color: green;'>" . $timeDiff->format('%h hours %i minutes') . "</td></tr>";
        echo "<tr><td><strong>Smart Choice:</strong></td><td style='color: green; font-weight: bold;'>🟢 VOID QUOTE (Maximum refund)</td></tr>";
        echo "<tr><td><strong>Why Best:</strong></td><td>Get quote first, then minimal charges if accepted</td></tr>";
    } else {
        $useRefundQuote = true;
        $selectedAPI = 'RefundQuote';
        $timeDiff = $voidWindowDateTime->diff($currentDateTime);
        echo "<tr><td><strong>Void Status:</strong></td><td style='color: red;'>❌ EXPIRED</td></tr>";
        echo "<tr><td><strong>Expired Since:</strong></td><td style='color: red;'>" . $timeDiff->format('%h hours %i minutes ago') . "</td></tr>";
        echo "<tr><td><strong>Smart Choice:</strong></td><td style='color: blue; font-weight: bold;'>🔵 REFUND QUOTE (Preview first)</td></tr>";
        echo "<tr><td><strong>Why This:</strong></td><td>Get quote with penalties, decide after seeing amount</td></tr>";
    }
} else {
    $useRefundQuote = true;
    echo "<tr><td><strong>Void Status:</strong></td><td style='color: gray;'>❓ NO INFO</td></tr>";
    echo "<tr><td><strong>Default Choice:</strong></td><td style='color: blue;'>🔵 REFUND QUOTE</td></tr>";
}
echo "</table>";

echo "<h3>🎯 New Smart Logic (No Direct Refund):</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4CAF50; margin: 10px 0;'>";
echo "<h4>🧠 One Button, Smart Choice:</h4>";
echo "<ol>";
echo "<li><strong>Priority 1:</strong> 🟢 <strong>VoidQuote</strong> - If within void window (preview + minimal charges)</li>";
echo "<li><strong>Priority 2:</strong> 🔵 <strong>RefundQuote</strong> - If void expired (preview + user decision)</li>";
echo "<li><strong>Fallback:</strong> If VoidQuote fails → automatically try RefundQuote</li>";
echo "<li><strong>❌ Removed:</strong> Direct Refund (risky unknown amounts)</li>";
echo "</ol>";
echo "</div>";

echo "<h3>💰 Quote-First Benefits:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
echo "<tr style='background: #f0f0f0;'><th>API Type</th><th>Customer Gets</th><th>User Decision</th><th>Risk Level</th></tr>";
echo "<tr style='background: #e8f5e8;'><td><strong>🟢 VoidQuote</strong></td><td>Preview of minimal charges</td><td>Accept/Reject after seeing amount</td><td><strong>Low Risk</strong></td></tr>";
echo "<tr style='background: #d1ecf1;'><td><strong>🔵 RefundQuote</strong></td><td>Preview with penalties</td><td>Accept/Reject after seeing amount</td><td><strong>Low Risk</strong></td></tr>";
echo "<tr style='background: #ffebee;'><td><strong>❌ Direct Refund</strong></td><td>Unknown amount until processed</td><td>No choice, automatic</td><td><strong>High Risk</strong></td></tr>";
echo "</table>";

echo "<h3>🧪 Single CTA Preview:</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔘 Same Button, Different Smart Action:</h4>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<button style='background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
echo "💰 Request Refund";
echo "</button>";
echo "</div>";

echo "<h4>📤 Smart API Selection Result:</h4>";
echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";

$requestData = array(
    'ptrType' => $selectedAPI,
    'mFRef' => $firstPassenger['mf_reference'],
    'AllowChildPassenger' => false,
    'passengers' => array(
        array(
            'firstName' => $firstPassenger['first_name'],
            'lastName' => $firstPassenger['last_name'],
            'title' => $firstPassenger['title'],
            'eTicket' => $firstPassenger['e_ticket_number'],
            'passengerType' => $firstPassenger['passenger_type']
        )
    ),
    'AdditionalNote' => $useVoidQuote ? 'Void quote request - best option for customer' : 'Refund quote request - void window expired'
);

echo json_encode($requestData, JSON_PRETTY_PRINT);
echo "</pre>";
echo "</div>";

echo "<h3>🔄 Fallback Strategy:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<h4>If Primary Choice Fails:</h4>";
echo "<ol>";
echo "<li>If <strong>VoidQuote fails</strong> → Automatically try <strong>RefundQuote</strong></li>";
echo "<li>If <strong>RefundQuote fails</strong> → Show helpful error with support contact</li>";
echo "<li><strong>No Direct Refund fallback</strong> → Always maintain quote-first approach</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📱 Customer Experience Flow:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<h4>🎯 Perfect User Journey:</h4>";
echo "<ol>";
echo "<li><strong>Customer:</strong> Clicks \"Request Refund\" button</li>";
echo "<li><strong>System:</strong> Smart detection (void window check)</li>";
echo "<li><strong>API Call:</strong> " . ($useVoidQuote ? "VoidQuote" : "RefundQuote") . " automatically selected</li>";
echo "<li><strong>Response:</strong> Quote preview with amounts and charges</li>";
echo "<li><strong>Customer:</strong> Reviews and decides to Accept/Reject</li>";
echo "<li><strong>Result:</strong> Informed decision with known amounts</li>";
echo "</ol>";
echo "</div>";

echo "<h3>✅ Implementation Benefits:</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
echo "<ul>";
echo "<li>🎯 <strong>Single CTA:</strong> One \"Request Refund\" button handles everything</li>";
echo "<li>🧠 <strong>Smart Backend:</strong> Automatically chooses best option</li>";
echo "<li>👁️ <strong>Transparent:</strong> User always sees amounts before proceeding</li>";
echo "<li>🛡️ <strong>Safe:</strong> No surprise amounts or hidden charges</li>";
echo "<li>🔄 <strong>Fallback:</strong> Graceful handling if primary option fails</li>";
echo "<li>📱 <strong>User-Friendly:</strong> No technical complexity for customers</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🎉 Perfect Implementation:</h3>";
echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #bee5eb;'>";
echo "<h4>✅ Your Smart Approach:</h4>";
echo "<p><strong>Quote-First Strategy:</strong> Always show amounts before processing</p>";
echo "<p><strong>Single Interface:</strong> One button, smart backend selection</p>";
echo "<p><strong>Customer Trust:</strong> Transparent pricing, no surprises</p>";
echo "<p><strong>Risk Management:</strong> Eliminate unknown amount scenarios</p>";
echo "</div>";

echo "<hr>";
echo "<p><em>🚀 Your refund system now uses intelligent quote-first approach with single CTA!</em></p>";
?> 