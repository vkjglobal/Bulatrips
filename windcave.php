<?php
session_start();
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();
  include_once('mail_send.php');
header('Content-Type: application/json');

$currency   =   "USD";
$userId =   $_SESSION['user_id'];
$email  =   $_SESSION['email'];
$firstname  =   $_SESSION['first_name'];
$fsc       =   $_SESSION['fsc'];
$payStatus =1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Totalamount = $_POST['Totalamount'];
}
$_SESSION['Totalamount']    =   $Totalamount;
  //echo json_encode($fsc);exit;
  //.$userId.$payStatus.$Totalamount.$currency;
 //insert into paymentuser table
$insPay     =   $objBook->insPaySts($fsc,$userId,$payStatus,$Totalamount,$currency);
$responsePay = array(  "status" => "success",
                        "Totalamount" =>$Totalamount
                            );
                            
                         /*   $responsePay = array(  "status" => "Failed",
                            "message" => "Card Error"
                            );
                            */
//mail send to user .db insert into pay table

 //==========email to USer  about Payment======
 if($payStatus == 1){
                     $subject = "Bulatrips Payment Success Info for new booking ";                  

                            $email=   $email;
                            $name   =   $firstname;
                            $content    =   '<p>Hello '. $name .',</p>
                                                <p>Your Payment with Bulatrips is successful .Please proceed to Booking</p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                         // $email = "soumya.reubro@gmail.com"; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);

 }
                    //====================================email to agent ======

 //echo json_encode($_SESSION);exit;//NEED to change after payment integration
 echo json_encode($responsePay);exit;//NEED to change after payment integration

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
?>
