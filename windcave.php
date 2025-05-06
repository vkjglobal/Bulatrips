<?php
session_start();
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();
include_once('mail_send.php');
include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
header('Content-Type: application/json');


$booking_id = $_POST['booking_id'];

$url = WC_URL."sessions";
$username = WC_USERNAME;
$password = WC_PASSWORD;

$total_paid = $_SESSION['session_total_amount']+$_SESSION['totalService'];

// IPG PRICE INCLUDING STARTS
$ipg_trasaction_percentage = 0;
$stmt = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$stmt->bindValue(':key', "ipg_transaction_percentage");
$stmt->execute();
$setting = $stmt->fetch(PDO::FETCH_ASSOC);

$ipg_percentage = 0;
if( isset($setting['value']) && $setting['value'] != '' ) {
    $ipg_percentage = $setting['value'];
}
$ipg_trasaction_percentage = ($ipg_percentage / 100) * ($total_paid);
// IPG PRICE INCLUDING ENDS

$total_paid += $ipg_trasaction_percentage;

$data = [
    "type" => "auth",
    "amount" => $total_paid,
    "currency" => "USD",
    "merchantReference" => $booking_id,
    "callbackUrls" => [
        "approved" => ENVIRONMENT_VAR."WindcavePaymentResponse",
        "declined" => ENVIRONMENT_VAR."WindcavePaymentResponse",
        "cancelled" => ENVIRONMENT_VAR."fligtsRulesRevalidation"
    ]
    // ,"notificationUrl" => ENVIRONMENT_VAR."WindcavePaymentResponse?123"
];

$jsonData = json_encode($data);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode("$username:$password")
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseArray = json_decode($response, true);
$secondHref = null;
if (isset($responseArray['links']) && is_array($responseArray['links']) && count($responseArray['links']) > 1) {
    $secondLink = $responseArray['links'][1];
    if (isset($secondLink['href']) && !empty($secondLink['href'])) {
        $secondHref=$secondLink['href'];
    }
}
$responsePay = array("status" => "success", "url" => $secondHref);
echo json_encode($responsePay);
exit;

// echo "<pre>";
//     print_r($_POST);
    // print_r($_SESSION);
// echo "</pre>";
// die;

$currency   =   "USD";
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "";
$email  =   $_SESSION['revalidationApi']['contactemail'];
// $email  =   $_SESSION['email'];
// $firstname  =   $_SESSION['first_name'];
$firstname  =   $_SESSION['revalidationApi']['contactfirstname']." ".$_SESSION['revalidationApi']['contactlastname'];
$fsc       =   $_SESSION['fsc'];
$payStatus = 1;

// $_SESSION['revalidationApi']
// $_SESSION['fsc']
// $_SESSION['session_total_amount']

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Totalamount = $_SESSION['session_total_amount'];
}
$_SESSION['Totalamount']    =   $Totalamount;

$insPay     =   $objBook->insPaySts($fsc, $payStatus, $Totalamount, $currency);
$responsePay = array("status" => "success", "Totalamount" => $Totalamount);


//echo json_encode($_SESSION);exit;//NEED to change after payment integration
echo json_encode($responsePay);
exit; //NEED to change after payment integration


//==========email to USer  about Payment======
if ($payStatus == 1) {
    $subject = "Bulatrips Payment Success Info for new booking ";
    $email =   $email;
    $name   =   $firstname;
    $content    =   '<p>Hello ' . $name . ',</p><p>Your Payment with Bulatrips is successful.</p>';
    $messageData =   $objBook->getEmailContent($content);
    $headers = "";
    $contacts = sendMail($email, $subject, $messageData, $headers);
}
//====================================email to agent ======


/*try {
    // Decode the JSON input
    $inputData = json_decode(file_get_contents('php://input'), true);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Extract data from the decoded input
        $cardNumber = isset($inputData['cardNumber']) ? $inputData['cardNumber'] : null;
        $cardHolderName = isset($inputData['Name']) ? $inputData['Name'] : null;
        $expiryMonth = isset($inputData['cardExpm']) ? $inputData['cardExpm'] : null;
        $expiryYear = isset($inputData['cardExpy']) ? $inputData['cardExpy'] : null;
        $cvc2 = isset($inputData['cvv']) ? $inputData['cvv'] : null;

        // Validate the input
        if (!$cardNumber || !$cardHolderName || !$expiryMonth || !$expiryYear || !$cvc2) {
            throw new Exception("Missing required fields");
        }

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
                "approved" => "http://localhost/Travelsite/wind_payment.php/success",
                "declined" => "http://localhost/Travelsite/wind_payment.php/fail",
                "cancelled" => "http://localhost/Travelsite/wind_payment.php/cancel"
            ],
            "notificationUrl" => "http://localhost/Travelsite/wind_payment.php/123",
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

        if ($httpCode >= 200 && $httpCode < 300) {
            if (!empty($decodedResponse['errors'])) {
                echo json_encode([
                    "status" => "error",
                    "errors" => $decodedResponse['errors']
                ]);
            } else {
                echo json_encode([
                    "status" => "success",
                    "requestId" => $decodedResponse['requestId'],
                    "timestamp" => $decodedResponse['timestampUtc']
                ]);
            }
        } else {
            // Handle non-2xx HTTP responses
            if ($httpCode == 400) {
                throw new Exception("Bad Request");
            } elseif ($httpCode == 401) {
                throw new Exception("Unauthorized");
            } elseif ($httpCode == 403) {
                throw new Exception("Forbidden");
            } elseif ($httpCode == 404) {
                throw new Exception("Not Found");
            } elseif ($httpCode == 500) {
                throw new Exception("Internal Server Error");
            } else {
                throw new Exception("HTTP Error $httpCode");
            }
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
*/
