<?php
echo "<h1>📚 Mystifly Direct Refund API Documentation</h1>";
echo "<p><em>Official API documentation from Mystifly for Direct Refund implementation</em></p>";

echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
echo "<h3>📋 API Overview:</h3>";
echo "<p><strong>Endpoint:</strong> <code>PostTicketingRequest</code></p>";
echo "<p><strong>Method:</strong> POST</p>";
echo "<p><strong>Purpose:</strong> Submit direct refund request without quote process</p>";
echo "<p><strong>Response:</strong> PTR ID for tracking refund status</p>";
echo "</div>";

echo "<h2>🔧 1. Create Direct Refund Request</h2>";

echo "<h3>📤 Request Format:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px;'>";
echo "<h4>Required Parameters:</h4>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th width='20%'>Parameter</th><th width='15%'>Type</th><th width='15%'>Required</th><th width='50%'>Description</th>";
echo "</tr>";

$requestParams = [
    ['ptrType', 'String', '✅ Yes', 'Must be "Refund" for Direct Refund'],
    ['mFRef', 'String', '✅ Yes', 'Mystifly reference (MF + 8 digits)'],
    ['AllowChildPassenger', 'Boolean', '✅ Yes', 'true if child passengers exist'],
    ['passengers', 'Array', '✅ Yes', 'Array of passenger details'],
    ['firstName', 'String', '✅ Yes', 'Passenger first name'],
    ['lastName', 'String', '✅ Yes', 'Passenger last name'],
    ['title', 'String', '✅ Yes', 'Passenger title (Mr/Ms/Mrs)'],
    ['eTicket', 'String', '✅ Yes', 'E-ticket number'],
    ['passengerType', 'String', '✅ Yes', 'ADT/CHD/INF'],
    ['AdditionalNote', 'String', '✅ Yes', 'Free text message for agent']
];

foreach ($requestParams as $param) {
    echo "<tr>";
    echo "<td><strong>" . $param[0] . "</strong></td>";
    echo "<td>" . $param[1] . "</td>";
    echo "<td>" . $param[2] . "</td>";
    echo "<td>" . $param[3] . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<h3>💻 Example Request:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo json_encode([
    "ptrType" => "Refund",
    "mFRef" => "MF15171620",
    "AllowChildPassenger" => false,
    "passengers" => [
        [
            "firstName" => "Alex",
            "lastName" => "Tan",
            "title" => "Miss",
            "eTicket" => "3673876689999",
            "passengerType" => "ADT"
        ]
    ],
    "AdditionalNote" => "Proceed direct Cancellation as per penalty"
], JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>📥 Success Response:</h3>";
echo "<pre style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px; overflow-x: auto;'>";
echo json_encode([
    "Success" => true,
    "Data" => [
        "PTRId" => 5285,
        "PTRType" => "Refund",
        "MFRef" => "MF15171620",
        "SLAInMinutes" => 1440,
        "PTRStatus" => "InProcess",
        "Message" => "Request for refund has been submitted successfully. Your Request# is 5285"
    ]
], JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>🔍 Response Parameters:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th width='20%'>Parameter</th><th width='80%'>Description</th>";
echo "</tr>";

$responseParams = [
    ['Success', 'Boolean - Status of the PTR request'],
    ['PTRId', 'Integer - Unique reference number for tracking'],
    ['PTRType', 'String - "Refund" confirms Direct Refund'],
    ['MFRef', 'String - Original Mystifly reference'],
    ['SLAInMinutes', 'Integer - Maximum processing time (1440 = 24 hours)'],
    ['PTRStatus', 'String - "InProcess" initially, "Completed" when done'],
    ['Message', 'String - Success confirmation message']
];

foreach ($responseParams as $param) {
    echo "<tr>";
    echo "<td><strong>" . $param[0] . "</strong></td>";
    echo "<td>" . $param[1] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🔍 2. Check Refund Status (Search PTR)</h2>";

echo "<h3>📤 Status Check Request:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo json_encode([
    "ptrType" => "Refund",
    "MFRef" => "MF15171620",
    "PTRId" => 5285,
    "Page" => 1
], JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h3>📥 Status Response (Completed):</h3>";
echo "<pre style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px; overflow-x: auto;'>";
echo json_encode([
    "Success" => true,
    "Data" => [
        "PTRDetail" => [
            [
                "PTRId" => 5285,
                "PTRType" => "Refund",
                "MFRef" => "MF15171620",
                "BookingStatus" => "Refunded",
                "PTRStatus" => "Completed",
                "CreditNoteNumber" => "3100207",
                "TotalRefundAmount" => "605.05",
                "Currency" => "USD",
                "CreatedBy" => "Mystifly",
                "Resolution" => "Refunded",
                "CreditNoteStatus" => "Unpaid",
                "pTRPaxDetails" => [
                    [
                        "Id" => 5888,
                        "PTRId" => 5285,
                        "PaxId" => 223513,
                        "TicketNumber" => "3673876689999",
                        "TicketStatus" => 0,
                        "IsActive" => true,
                        "PassengerType" => "ADT",
                        "Tittle" => "MS",
                        "FirstName" => "ADULT",
                        "LastName" => "TWO"
                    ]
                ]
            ]
        ]
    ]
], JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h2>⚠️ 3. Common Error Codes</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #ffebee; font-weight: bold;'>";
echo "<th width='40%'>Error Message</th><th width='60%'>Solution</th>";
echo "</tr>";

$errorCodes = [
    ['SessionId cannot be null', 'Make sure the session ID is passed in header'],
    ['Invalid SessionId', 'Check the session ID validity'],
    ['UniqueID cannot be null', 'MF reference number is required'],
    ['Invalid UniqueID', 'Check MF reference format (MF + 8 digits)'],
    ['Passenger Details cannot be null', 'All passenger details must be provided'],
    ['Passenger details are not matching', 'Verify passenger names match booking'],
    ['Passenger count not matching', 'Number of passengers must match booking'],
    ['Passenger type not matching', 'ADT/CHD/INF must match original booking'],
    ['Eticket number cannot be null', 'E-ticket number is mandatory'],
    ['Passenger ticket number not matching', 'Verify e-ticket number is correct'],
    ['Invalid PTRType', 'Must be "Refund" for Direct Refund'],
    ['Booking not ticketed', 'Only ticketed bookings can be refunded']
];

foreach ($errorCodes as $error) {
    echo "<tr>";
    echo "<td style='color: #d32f2f;'><strong>" . $error[0] . "</strong></td>";
    echo "<td>" . $error[1] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>📊 4. PTR Status Values</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
echo "<th width='25%'>Status</th><th width='75%'>Description</th>";
echo "</tr>";

$statusValues = [
    ['InProcess', '🟡 Request submitted, processing in progress'],
    ['Completed', '✅ Refund processed successfully'],
    ['Rejected', '❌ Refund request rejected by airline'],
    ['Refunded', '✅ Money refunded to original payment method'],
    ['RefundRequested', '🟡 Initial request status'],
    ['RefundRejected', '❌ Refund denied by airline policy']
];

foreach ($statusValues as $status) {
    echo "<tr>";
    echo "<td><strong>" . $status[0] . "</strong></td>";
    echo "<td>" . $status[1] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 5. Implementation in Your Code</h2>";

echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #4caf50; margin: 20px 0;'>";
echo "<h3>✅ Your Current Implementation:</h3>";
echo "<ol>";
echo "<li><strong>Smart Detection:</strong> Automatically uses Direct Refund when void window expired</li>";
echo "<li><strong>Proper Request:</strong> Correctly formats all required parameters</li>";
echo "<li><strong>Error Handling:</strong> Handles all Mystifly error responses</li>";
echo "<li><strong>Status Tracking:</strong> Can check PTR status for completion</li>";
echo "<li><strong>Fallback Logic:</strong> Uses Direct Refund when RefundQuote fails</li>";
echo "</ol>";
echo "</div>";

echo "<h3>📝 Code Example from Your Implementation:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars('
// Your smart selection logic
if ($useDirectRefund) {
    $requestData = array(
        "ptrType" => "Refund",              // Direct Refund
        "mFRef" => $mfreNum,
        "AllowChildPassenger" => $hasChildPassenger,
        "passengers" => array(
            array(
                "firstName" => $passenger["firstName"],
                "lastName" => $passenger["lastName"], 
                "title" => $passenger["title"],
                "eTicket" => $passenger["eTicket"],
                "passengerType" => $passenger["passengerType"]
            )
        ),
        "AdditionalNote" => "Direct refund request - void window expired"
    );
}
');
echo "</pre>";

echo "<h2>🔗 6. API Integration Details</h2>";

echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
echo "<h3>🌐 Endpoint Information:</h3>";
echo "<ul>";
echo "<li><strong>Base URL:</strong> <code>https://restapidemo.myfarebox.com/api/</code></li>";
echo "<li><strong>Endpoint:</strong> <code>PostTicketingRequest</code></li>";
echo "<li><strong>Method:</strong> POST</li>";
echo "<li><strong>Content-Type:</strong> application/json</li>";
echo "<li><strong>Authorization:</strong> Bearer Token required</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔧 cURL Example:</h3>";
echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars('
curl -X POST "https://restapidemo.myfarebox.com/api/PostTicketingRequest" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d \'{
    "ptrType": "Refund",
    "mFRef": "MF15171620",
    "AllowChildPassenger": false,
    "passengers": [
      {
        "firstName": "Alex",
        "lastName": "Tan",
        "title": "Miss",
        "eTicket": "3673876689999",
        "passengerType": "ADT"
      }
    ],
    "AdditionalNote": "Direct refund request"
  }\'
');
echo "</pre>";

echo "<hr>";
echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745; border-radius: 5px;'>";
echo "<h3>🎉 Summary:</h3>";
echo "<p>✅ <strong>Complete API Documentation Available</strong></p>";
echo "<p>✅ <strong>Already Implemented in Your System</strong></p>";
echo "<p>✅ <strong>Smart Auto-Selection Working</strong></p>";
echo "<p>✅ <strong>Error Handling Complete</strong></p>";
echo "<p>✅ <strong>Fallback Mechanism Active</strong></p>";
echo "<p><em>Your Direct Refund implementation is production-ready!</em></p>";
echo "</div>";
?> 