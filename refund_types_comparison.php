<?php
echo "<h1>💰 Complete Refund Types Guide</h1>";
echo "<h2>🔍 What is Direct Refund?</h2>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>📝 Direct Refund Definition:</h3>";
echo "<p><strong>Direct Refund</strong> یہ ایک <em>'بغیر quote لیے فوری refund'</em> کا process ہے۔</p>";
echo "<p>📌 <strong>خصوصیت:</strong> پہلے کوئی estimate نہیں مانگتا، direct cancel کر کے refund شروع کر دیتا ہے۔</p>";
echo "</div>";

echo "<h3>📊 Complete Comparison Table:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th width='15%'>API Type</th>";
echo "<th width='15%'>Process</th>";
echo "<th width='15%'>Timing</th>";
echo "<th width='20%'>How it Works</th>";
echo "<th width='15%'>Refund Amount</th>";
echo "<th width='20%'>Best For</th>";
echo "</tr>";

echo "<tr style='background: #e8f5e8;'>";
echo "<td><strong>🟢 VoidQuote</strong></td>";
echo "<td>Quote first<br>→ Accept<br>→ Void</td>";
echo "<td>Same day<br>(24 hours)</td>";
echo "<td>1. Quote لو<br>2. Amount check کرو<br>3. Accept کرو<br>4. Void ہو جائے</td>";
echo "<td><strong>90-100%</strong><br>(minimal fees)</td>";
echo "<td>Fresh bookings<br>Same day cancel</td>";
echo "</tr>";

echo "<tr style='background: #d1ecf1;'>";
echo "<td><strong>🔵 RefundQuote</strong></td>";
echo "<td>Quote first<br>→ Accept<br>→ Refund</td>";
echo "<td>Anytime before flight</td>";
echo "<td>1. Quote لو<br>2. Penalties check کرو<br>3. Accept کرو<br>4. Refund process</td>";
echo "<td><strong>60-90%</strong><br>(depends on airline)</td>";
echo "<td>Planning cancellation<br>Want to know exact amount</td>";
echo "</tr>";

echo "<tr style='background: #fff3cd;'>";
echo "<td><strong>🟡 Direct Refund</strong></td>";
echo "<td>Direct cancel<br>→ Immediate refund</td>";
echo "<td>Anytime before flight</td>";
echo "<td>1. Direct submit<br>2. Automatic cancel<br>3. Refund according to airline rules</td>";
echo "<td><strong>Unknown until processed</strong><br>(airline decides)</td>";
echo "<td>Emergency cancellation<br>Don't need quote first</td>";
echo "</tr>";

echo "</table>";

echo "<h3>🎯 Practical Examples:</h3>";

echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4CAF50; margin: 10px 0;'>";
echo "<h4>🟢 VoidQuote Scenario:</h4>";
echo "<p><strong>حالت:</strong> آج صبح ticket book کیا، شام کو cancel کرنا ہے</p>";
echo "<p><strong>Process:</strong></p>";
echo "<ol>";
echo "<li><strong>VoidQuote Request:</strong> \"Kitna paisa wapis milega?\"</li>";
echo "<li><strong>Response:</strong> \"$450 out of $500 wapis milega (صرف $50 fee)\"</li>";
echo "<li><strong>Decision:</strong> \"OK, proceed with void\"</li>";
echo "<li><strong>Result:</strong> ✅ $450 refund confirmed</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 10px 0;'>";
echo "<h4>🔵 RefundQuote Scenario:</h4>";
echo "<p><strong>حالت:</strong> 1 ہفتہ پہلے ticket book کیا، اب cancel کرنا ہے</p>";
echo "<p><strong>Process:</strong></p>";
echo "<ol>";
echo "<li><strong>RefundQuote Request:</strong> \"Cancel کیا تو کتنا refund ہوگا?\"</li>";
echo "<li><strong>Response:</strong> \"$320 out of $500 wapis milega (penalties $180)\"</li>";
echo "<li><strong>Decision:</strong> \"Think کرتے ہیں... OK proceed\"</li>";
echo "<li><strong>Result:</strong> ✅ $320 refund confirmed</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
echo "<h4>🟡 Direct Refund Scenario:</h4>";
echo "<p><strong>حالت:</strong> Emergency! فوراً cancel کرنا ہے، amount بعد میں دیکھیں گے</p>";
echo "<p><strong>Process:</strong></p>";
echo "<ol>";
echo "<li><strong>Direct Refund Request:</strong> \"Just cancel karo, jo milna hai wo milo\"</li>";
echo "<li><strong>Response:</strong> \"Request submitted, refund processing started\"</li>";
echo "<li><strong>Wait:</strong> 24-48 hours</li>";
echo "<li><strong>Result:</strong> ✅ $280 refund processed (amount surprise thi)</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🤔 کب کون سا استعمال کریں؟</h3>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 15px 0;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th>Situation</th><th>Best Choice</th><th>Why?</th>";
echo "</tr>";

echo "<tr>";
echo "<td>📅 Same day cancellation</td>";
echo "<td>🟢 <strong>VoidQuote</strong></td>";
echo "<td>Maximum refund with minimal fees</td>";
echo "</tr>";

echo "<tr>";
echo "<td>🔍 Want to know exact amount first</td>";
echo "<td>🔵 <strong>RefundQuote</strong></td>";
echo "<td>Get quote, then decide</td>";
echo "</tr>";

echo "<tr>";
echo "<td>🚨 Emergency cancellation</td>";
echo "<td>🟡 <strong>Direct Refund</strong></td>";
echo "<td>Quick process, amount later</td>";
echo "</tr>";

echo "<tr>";
echo "<td>❌ VoidQuote failed</td>";
echo "<td>🔵 <strong>RefundQuote</strong></td>";
echo "<td>Fallback option</td>";
echo "</tr>";

echo "<tr>";
echo "<td>❌ RefundQuote failed</td>";
echo "<td>🟡 <strong>Direct Refund</strong></td>";
echo "<td>Last resort</td>";
echo "</tr>";

echo "</table>";

echo "<h3>⚡ API Request Examples:</h3>";

echo "<div style='display: flex; gap: 20px; margin: 20px 0;'>";

// VoidQuote Example
echo "<div style='flex: 1; background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<h4>🟢 VoidQuote Request:</h4>";
echo "<pre style='font-size: 12px; background: #f0f0f0; padding: 10px;'>";
echo json_encode([
    "ptrType" => "VoidQuote",
    "mFRef" => "MF12345678",
    "passengers" => [[
        "firstName" => "Ahmed",
        "lastName" => "Khan", 
        "title" => "Mr",
        "eTicket" => "1234567890",
        "passengerType" => "ADT"
    ]],
    "AdditionalNote" => "Quote for void - same day"
], JSON_PRETTY_PRINT);
echo "</pre>";
echo "</div>";

// RefundQuote Example  
echo "<div style='flex: 1; background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔵 RefundQuote Request:</h4>";
echo "<pre style='font-size: 12px; background: #f0f0f0; padding: 10px;'>";
echo json_encode([
    "ptrType" => "RefundQuote",
    "mFRef" => "MF12345678",
    "passengers" => [[
        "firstName" => "Ahmed",
        "lastName" => "Khan",
        "title" => "Mr", 
        "eTicket" => "1234567890",
        "passengerType" => "ADT"
    ]],
    "AdditionalNote" => "Quote for refund please"
], JSON_PRETTY_PRINT);
echo "</pre>";
echo "</div>";

// Direct Refund Example
echo "<div style='flex: 1; background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>🟡 Direct Refund Request:</h4>";
echo "<pre style='font-size: 12px; background: #f0f0f0; padding: 10px;'>";
echo json_encode([
    "ptrType" => "Refund",
    "mFRef" => "MF12345678", 
    "passengers" => [[
        "firstName" => "Ahmed",
        "lastName" => "Khan",
        "title" => "Mr",
        "eTicket" => "1234567890", 
        "passengerType" => "ADT"
    ]],
    "AdditionalNote" => "Direct refund - emergency"
], JSON_PRETTY_PRINT);
echo "</pre>";
echo "</div>";

echo "</div>";

echo "<h3>🎯 خلاصہ - Direct Refund:</h3>";
echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745; border-radius: 5px;'>";
echo "<h4>✅ Direct Refund کے فوائد:</h4>";
echo "<ul>";
echo "<li>⚡ <strong>Fast Process:</strong> No quote step, direct submission</li>";
echo "<li>🔄 <strong>Simple:</strong> One API call, done</li>";
echo "<li>🚨 <strong>Emergency Ready:</strong> When you need quick cancellation</li>";
echo "<li>🛡️ <strong>Reliable:</strong> Works when other methods fail</li>";
echo "</ul>";

echo "<h4>❌ Direct Refund کے نقصانات:</h4>";
echo "<ul>";
echo "<li>❓ <strong>Unknown Amount:</strong> Don't know refund amount until processed</li>";
echo "<li>⏰ <strong>No Control:</strong> Can't see/decide penalties first</li>";
echo "<li>💰 <strong>Airline Decides:</strong> Refund amount according to their rules</li>";
echo "<li>⚠️ <strong>Risk:</strong> Might get less than expected</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🚀 Your Smart System:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>";
echo "<p><strong>✅ Perfect Logic Implementation:</strong></p>";
echo "<p>1. 🟢 <strong>Void Window Active</strong> → VoidQuote (best refund)</p>";
echo "<p>2. 🔴 <strong>Void Window Expired</strong> → Direct Refund (still possible)</p>";
echo "<p>3. ❌ <strong>Any Method Fails</strong> → Automatic fallback to next best</p>";
echo "<p><em>Result: Maximum possible refund for customer!</em></p>";
echo "</div>";
?> 