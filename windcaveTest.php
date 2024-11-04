<!-- response tested -->
<?php
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $cardNumber = $_POST['CardNumber'];
//     $cardHolderName = $_POST['CardHolderName'];
//     $expiryMonth = $_POST['ExpiryMonth'];
//     $expiryYear = $_POST['ExpiryYear'];
//     $cvc2 = $_POST['Cvc2'];

//     $url = "https://sec.windcave.com/api/v1/sessions";
//     $headers = [
//         "Content-Type: application/json",
//         "Authorization: Basic ABC123"  // Replace with your actual authorization token
//     ];

//     $data = [
//         "type" => "purchase",
//         "amount" => "1.00",
//         "currency" => "NZD",
//         "merchantReference" => "1234ABC",
//         "language" => "en",
//         "methods" => ["card"],
//         "callbackUrls" => [
//             "approved" => "https://bulatrips.com/windcaveTest.php/success",
//             "declined" => "https://bulatrips.com/windcaveTest.php/fail",
//             "cancelled" => "https://bulatrips.com/windcaveTest.php/cancel"
//         ],
//         "notificationUrl" => "https://bulatrips.com/windcaveTest.php?123",
//         "card" => [
//             "number" => $cardNumber,
//             "name" => $cardHolderName,
//             "expiryMonth" => $expiryMonth,
//             "expiryYear" => $expiryYear,
//             "cvc2" => $cvc2
//         ]
//     ];

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//     $response = curl_exec($ch);
//     curl_close($ch);

//     echo $response;
// }

// response checking
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cardNumber = $_POST['CardNumber'];
    $cardHolderName = $_POST['CardHolderName'];
    $expiryMonth = $_POST['ExpiryMonth'];
    $expiryYear = $_POST['ExpiryYear'];
    $cvc2 = $_POST['Cvc2'];

    $url = "https://sec.windcave.com/api/v1/sessions/00001200030240010c9e7ceadd26a6d8";
    $headers = [
        "Content-Type: application/json",
        "Authorization: Basic ABC123"  // Replace with your actual authorization token
    ];
    

    $data = [
        "type" => "purchase",
        "amount" => "1.00",
        "currency" => "NZD",
        "merchantReference" => "1234ABC",
        "language" => "en",
        "methods" => ["card"],
        "callbackUrls" => [
            "approved" => "https://bulatrips.com/windcaveHTML.php?success",
            "declined" => "https://bulatrips.com/windcaveHTML.php?fail",
            "cancelled" => "https://bulatrips.com/windcaveHTML.php?cancel"
        ],
        "notificationUrl" => "https://bulatrips.com/windcaveHTML.php?123",
        "card" => [
            "number" => $cardNumber,
            "name" => $cardHolderName,
            "expiryMonth" => $expiryMonth,
            "expiryYear" => $expiryYear,
            "cvc2" => $cvc2
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    // Handle the response based on the HTTP code and response content
    if ($httpCode == 200) {
        if (!empty($decodedResponse['errors'])) {
            // Display errors
            echo "Errors: <br>";
            foreach ($decodedResponse['errors'] as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
        } else {
            // Handle success response
            echo "Transaction successful!<br>";
            echo "Request ID: " . htmlspecialchars($decodedResponse['requestId']) . "<br>";
            echo "Timestamp: " . htmlspecialchars($decodedResponse['timestampUtc']) . "<br>";

            // Optionally redirect to the success page
            header("Location: https://example.com/success");
            exit;
        }
    } else {
        // Handle other HTTP response codes
        echo "HTTP Response Code: " . htmlspecialchars($httpCode) . "<br>";
        echo "Response Body: <br>";
        echo "<pre>" . htmlspecialchars(print_r($decodedResponse, true)) . "</pre>";
    }
}
?>
<!-- display response with http response and data -->
<!-- <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cardNumber = $_POST['CardNumber'];
    $cardHolderName = $_POST['CardHolderName'];
    $expiryMonth = $_POST['ExpiryMonth'];
    $expiryYear = $_POST['ExpiryYear'];
    $cvc2 = $_POST['Cvc2'];

    $url = "https://sec.windcave.com/api/v1/sessions";
    $headers = [
        "Content-Type: application/json",
        "Authorization: Basic ABC123"  // Replace with your actual authorization token
    ];

    $data = [
        "type" => "purchase",
        "amount" => "1.00",
        "currency" => "NZD",
        "merchantReference" => "1234ABC",
        "language" => "en",
        "methods" => ["card"],
        "callbackUrls" => [
            "approved" => "https://example.com/success",
            "declined" => "https://example.com/fail",
            "cancelled" => "https://example.com/cancel"
        ],
        "notificationUrl" => "https://example.com/txn_result?123",
        "card" => [
            "number" => $cardNumber,
            "name" => $cardHolderName,
            "expiryMonth" => $expiryMonth,
            "expiryYear" => $expiryYear,
            "cvc2" => $cvc2
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Response Code: " . $httpCode . "<br>";
    echo "Response Body: " . $response . "<br>";

    // Optionally, you can decode the JSON response to see it in a more readable format
    $decodedResponse = json_decode($response, true);
    echo "<pre>";
    print_r($decodedResponse);
    echo "</pre>";
}
?> -->

