<script>

deleteUserDataCookie("infantData");
deleteUserDataCookie("contactDetailsData");
deleteUserDataCookie("childData");
deleteUserDataCookie("adultsData");

function deleteUserDataCookie(cookieName) {
    document.cookie =
    cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}
</script>
<?php
require_once("includes/header.php");
include_once('includes/common_const.php');
include_once('includes/class.BookScript.php');
include_once('mail_send.php');

?>
<style>
    .bodycontant {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 70vh;
        background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .content {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        padding: 20px;
        padding: 20px;
        background: #121e7e;
        border-radius: 10px;
    }
    .content_cancel {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        padding: 20px;
        padding: 20px;
        background: #ffffffe8;
        border-radius: 10px;
    }
    .content_cancel h1 {
        font-size: 48px;
        font-weight: bold;
        margin-bottom: 20px;
        color: red;
    }

    /* Subtext Styling */
    .content_cancel p {
        font-size: 18px;
        color: #000;
        margin-bottom: 30px;
    }



    .content_success {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        padding: 20px;
        padding: 20px;
        background: #ffffffe8;
        border-radius: 10px;
    }
    .content_success h1 {
        font-size: 48px;
        font-weight: bold;
        margin-bottom: 20px;
        color: green;
    }

    /* Subtext Styling */
    .content_success p {
        font-size: 18px;
        color: #000;
        margin-bottom: 30px;
    }

    /* Button Styling */
    .content .btn {
        display: inline-block;
        padding: 15px 30px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    .content .btn:hover {
        background-color: #121E7E;
    }

    /* Common Styling */
    .icon-container {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        height: 100px;
        background-color: #f9f9f9;
    }

    /* Success Tick Animation */
    .success {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #4CAF50;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        padding-bottom: 10px;
    }

    .success::after {
        content: "";
        position: absolute;
        width: 28px;
        height: 58px;
        border: solid white;
        border-width: 0 5px 5px 0;
        transform: rotate(45deg);
        opacity: 0;
        animation: tick 0.5s ease-in-out forwards;
    }

    /* Error Cross Animation */
    .error {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #E74C3C;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        padding-bottom: 10px;
    }

    .error::before, .error::after {
        content: "";
        position: absolute;
        width: 28px;
        height: 52px;
        background-color: white;
        opacity: 0;
        /* animation: cross 0.5s ease-in-out forwards; */
    }

    .error::before {
        transform: rotate(45deg);
    }

    .error::after {
        transform: rotate(-45deg);
    }

    /* Tick Animation */
    @keyframes tick {
        from {
            opacity: 0;
            transform: rotate(45deg) scale(0);
        }
        to {
            opacity: 1;
            transform: rotate(45deg) scale(1);
        }
    }

    /* Cross Animation */
    @keyframes cross {
        from {
            opacity: 0;
            transform: scale(0);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<?php
if( !isset($_GET['sessionId']) || $_GET['sessionId'] == '' ) {
    ?>
	<script>
        window.location="index"
    </script>
    <?php
} 
$url = WC_URL."sessions/".$_GET['sessionId'];
$username = WC_USERNAME;
$password = WC_PASSWORD;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode("$username:$password")
]);

$response = curl_exec($ch);
curl_close($ch);


$objBook    =   new BookScript();

$objBook->_writeLog('-------------Windcave Response Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
$objBook->_writeLog("", 'WindcavePaymentResponse.txt');
$objBook->_writeLog(print_r($response, true), 'WindcavePaymentResponse.txt');
$objBook->_writeLog("", 'WindcavePaymentResponse.txt');
$objBook->_writeLog('-------------Windcave Response Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');

if( isset($response) && $response != '') { 
    $responseArray = json_decode($response, true);
  
    if( is_array($responseArray) && count($responseArray) > 0 && isset($responseArray['transactions'][0]['id']) ) {        
        $check_trans = $conn->prepare('SELECT * FROM payment_user WHERE trn_id LIKE :trn_id AND trn_session_id LIKE :trn_ses_id');
        $check_trans->bindParam(':trn_id', $responseArray['transactions'][0]['id']);
        $check_trans->bindParam(':trn_ses_id', $responseArray['id']);
        $check_trans->execute();
        $rowCount = $check_trans->rowCount();
        
        if( $rowCount == 0 ) {
            $data = array(
                "currency" => "usd",
                "amount" => $responseArray['amount'],
                "booking_id" => $responseArray['merchantReference'],
                "merchantReference" => $responseArray['merchantReference'],
                "user_id" => isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0,
                "fsc" => "",
                "payment_status" => $responseArray['transactions'][0]['responseText'],
                "trn_id" => $responseArray['transactions'][0]['id'],
                "transaction_code" => $responseArray['transactions'][0]['reCo'],
                "trn_session_id" => $responseArray['id'],
                "data" => json_encode($responseArray),
                "is_email_sent" => "1",
            );
            $insPay = $objBook->insertUserPayment($data);

            if( isset($insPay) && $insPay != '' ) {
                $airport_country = $conn->prepare('SELECT * FROM temp_booking WHERE id LIKE :id');
                $airport_country->bindParam(':id', $responseArray['merchantReference']);
                $airport_country->execute();
                $AP_country_name_fetch = $airport_country->fetch(PDO::FETCH_ASSOC);
                $toEmail = $AP_country_name_fetch['contact_email'];
                $subject = ($responseArray['transactions'][0]['responseText'] == 'APPROVED' ? "Payment Confirmation - Your Transaction Was Successful!" : 'For Failed Payment: "Payment Failed - Please Try Again');
                $logoUrl = "https://bulatrips.com/images/Image-Logo-vec.png";
                $backgroundColor = "#f8f9fa";
                $containerBgColor = "#ffffff";
                $headerBgColor = "#0000ff";
                $approvedColor = "#0000ff";
                $failedColor = "#f57c00";
                $statusColor = ($responseArray['transactions'][0]['responseText'] == 'APPROVED' ? $approvedColor : $failedColor);
                $transactionData = '<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    </head>
                    <body style="font-family: Arial, sans-serif; background-color: '.$backgroundColor.'; margin: 0; padding: 20px;">
                        <table width="100%" bgcolor="'.$containerBgColor.'" cellpadding="10" cellspacing="0" border="0" style="max-width: 600px; margin: auto; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="text-align:center; padding-bottom:15px; padding-top:15px">
                                    <img src="'.$logoUrl.'" alt="Bulatrip" title="Bulatrip" style="height: 50px; margin-top: 20px; margin-bottom: 20px;">
                                </td>
                            </tr>
                            
                            <tr>
                                <td align="center" style="padding: 15px; font-size: 20px; font-weight: bold; color: #ffffff; background-color: '.$statusColor.';">
                                    Payment Transaction '.$responseArray['transactions'][0]['responseText'].'
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="padding: 20px; color: #333333; font-size: 16px;">
                                    <p>We would like to inform you about your recent payment transaction:</p>
                                    <div style="background-color: #f1f1f1; padding: 15px; border-radius: 5px; font-size: 16px;">
                                        <p><strong>Transaction ID:</strong> '.$responseArray['transactions'][0]['id'].'</p>
                                        <p><strong>Amount:</strong> USD '.$responseArray['amount'].'</p>
                                        <p><strong>Status:</strong> '.$responseArray['transactions'][0]['responseText'].'</p>
                                    </div>
                                    <p style="margin-top:12px;">'.($responseArray['transactions'][0]['responseText'] == 'APPROVED' ? 'Thank you for your payment. Your transaction was successful.' : 'Unfortunately, your payment was '.strtolower($responseArray['transactions'][0]['responseText']).'. Please check with your payment provider or try again.').'</p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="background-color: '.$backgroundColor.'; color: #555555; font-size: 14px; padding: 10px;">
                                    Best regards, <br>
                                    <a href="https://bulatrips.com" style="color: #007bff; text-decoration: none;">Visit our website</a>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';

                $headers="";
                
                // PROCEED TO ORDER TICKET STARTS
                $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :id');
                $stmtbookingid->execute(array('id' => $responseArray['merchantReference']));
                $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);

                if ($bookingData['fare_type'] != "WebFare") {
                    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode');
                    $stmtbookingid->execute(array('farecode' => $bookingData['fare_source_code']));
                    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
            
                    $endpoint   =   'v1/OrderTicket';
                    $apiEndpoint = APIENDPOINT . $endpoint;
                    $bearerToken   =   BEARER;
                    $requestData = array(
                        'UniqueID' => $bookingData['mf_reference'],
                        'Target' => TARGET,
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $bearerToken
                    ));
                    $responseTicket = curl_exec($ch);
                    curl_close($ch);
                    if ($response) {
                        $responseTicketData = json_decode($responseTicket, true);
                        $objBook->_writeLog('-------------Order Ticket Api Response Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("API CALLED ".$apiEndpoint, 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog(print_r($responseTicketData, true), 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog('-------------Order Ticket Api Response Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
                    }
                    if (!empty($responseData['Data']['Errors'])) {
                        $errMsg = $responseData['Data']['Errors'][0]['Message'];
                        $errCDE = $responseData['Data']['Errors'][0]['Code'];
                        if (empty($errMsg)) {
                            $errMsg = $responseData['Data']['Errors']['Message'];
                            $errCDE = $responseData['Data']['Errors']['Code'];
                        }
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
                        $ticket_status = $responseTicketData['Data']['Success'];
                        $id = $bookingData['id'];
                        $stmtupdate->bindParam(':ticket_status', $ticket_status);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                        $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                        $err_code = $errCDE;
                        $fairtype = $bookingData['fare_type'];
                        $booking_status = $booking_status;
                        $id = $bookingData['id'];
                        $stmtInsert->bindParam(':book_id', $id);
                        $stmtInsert->bindParam(':err_code', $err_code);
                        $stmtInsert->bindParam(':err_msg', $errMsg);
                        $stmtInsert->bindParam(':fare_type', $fairtype);
                        $stmtInsert->bindParam(':book_status', $booking_status);
                        $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                        $stmtInsert->execute();
                        $response = array(
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' => $fairtype,
                            'errors' => $errMsg,
                            'errCde' => $errCDE
                        );
                        
                        $objBook->_writeLog('-------------In case of Order ticket api error Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog(print_r($response, true), 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog('-------------In case of Order ticket api error Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');

                        $url = WC_URL."transactions";
                        $username = WC_USERNAME;
                        $password = WC_PASSWORD;
                        $data = [
                            "type" => "refund",
                            "amount" => $bookingData['total_paid'],
                            "transactionId" => $responseArray['transactions'][0]['id']
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
                        $refund_response = json_decode($response, true);
                        
                        $objBook->_writeLog('-------------Transaction Refunded Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog(print_r($refund_response, true), 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog('-------------Transaction Refunded Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
            
                    } else {
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
                        $ticket_status = $responseTicketData['Data']['Success'];
                        $id = $bookingData['id'];
                        $stmtupdate->bindParam(':ticket_status', $ticket_status);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                        $ticketstatus = "ticket sucess";
            
                        $url = WC_URL."transactions";
                        $username = WC_USERNAME;
                        $password = WC_PASSWORD;
                        $data = [
                            "type" => "complete",
                            "amount" => $bookingData['total_paid'],
                            "currency" => "USD",
                            "merchantReference" => $responseArray['merchantReference'],
                            "mode" => "internet",
                            "storedCardIndicator" => "credentialonfile",
                            "transactionId" => $responseArray['transactions'][0]['id']
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
                        $payment_transaction_complete = json_decode($response, true);
                        
                        $objBook->_writeLog('-------------Transaction Completed Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog(print_r($payment_transaction_complete, true), 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog("", 'WindcavePaymentResponse.txt');
                        $objBook->_writeLog('-------------Transaction Completed Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'WindcavePaymentResponse.txt');

                        // confirmationMail($toEmail, $subject, $transactionData,$headers);
                        ?>
                        <script>
                            window.location.href ="confirmation?booking_id=<?php echo $AP_country_name_fetch['mf_reference'];?>";
                        </script>
                        <?php
                    }
                } else if ($bookingData['fare_type'] == "WebFare") {
                    






                    // WEBFARE BOOKING STARTS HERE
                    if (isset($bookingData['fare_source_code'])) {
                        $codeWithoutPlus = substr($bookingData['contact_phonecode'], 1);
                        $stmt = $conn->prepare("SELECT * FROM travellers_details Where flight_booking_id = :bookingId");
                        $stmt->execute(array('bookingId' => $bookingData['id']));
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                        $endpoint   =   'v1/Book/Flight';
                        $apiEndpoint = APIENDPOINT . $endpoint;
                        $bearerToken   =   BEARER;
                        foreach ($result as $row) {
                            $extraMealId = $row['extrameal_id'];
                            $extraBaggageId = $row['extrabaggage_id'];
                            $extraMealReturnId = $row['extrameal_return_id'];
                            $extraBaggageReturnId = $row['extrabaggage_return_id'];
                            $extraServices = [];
                            if ($extraMealId != 0) {
                                $extraServices[] = array(
                                    "ExtraServiceId" => $extraMealId,
                                    "Quantity" => 1,
                                    "Key" => "string"
                                );
                            }
                
                            if ($extraBaggageId != 0) {
                                $extraServices[] = array(
                                    "ExtraServiceId" => $extraBaggageId,
                                    "Quantity" => 1,
                                    "Key" => "string"
                                );
                            }
                            if ($extraMealReturnId != 0) {
                                $extraServices[] = array(
                                    "ExtraServiceId" => $extraMealReturnId,
                                    "Quantity" => 1,
                                    "Key" => "string"
                                );
                            }
                            if ($extraBaggageReturnId != 0) {
                                $extraServices[] = array(
                                    "ExtraServiceId" => $extraBaggageReturnId,
                                    "Quantity" => 1,
                                    "Key" => "string"
                                );
                            }
                            $passenger = array(
                                "PassengerType" => $row['passenger_type'],
                                "Gender" => $row['gender'],
                                "PassengerName" => array(
                                    "PassengerTitle" => $row['title'],
                                    "PassengerFirstName" => $row['first_name'],
                                    "PassengerLastName" => $row['last_name']
                                ),
                                "DateOfBirth" => $row['dob'],
                                "Passport" => array(
                                    "PassportNumber" => $row['passport_number'],
                                    "ExpiryDate" => $row['passport_expiry_date'],
                                    "Country" => $row['issuing_country'],
                                ),
                                "PassengerNationality" => $row['nationality'],
                            );
                            if (!empty($extraServices) &&  $bookingData['fare_type'] == "WebFare") {
                                $passenger["ExtraServices1_1"] = $extraServices;
                            }
                            $passengerDetails[] = $passenger;
                        }
                        $requestData = array(
                            "FareSourceCode" =>  $bookingData['fare_source_code'],
                            "ClientMarkup" => $markup,
                            "TravelerInfo" => array(
                                "AirTravelers" => $passengerDetails,
                                "CountryCode" => $codeWithoutPlus,
                                "PhoneNumber" => $bookingData['contact_number'],
                                "Email" => $bookingData['contact_email'],
                                "PostCode" => $bookingData['contact_postcode']
                            ),
                            "Target" => TARGET,
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $bearerToken
                        ));
                        $response = curl_exec($ch);
                        curl_close($ch);
                        if ($response) {
                            $responseData = json_decode($response, true);
                            $objBook->_writeLog('-------------Public/Private Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            $objBook->_writeLog("API URL Response: ".$apiEndpoint, 'temp_booking_save.txt');
                            $objBook->_writeLog(print_r($responseData, true), 'temp_booking_save.txt');
                            $objBook->_writeLog("", 'temp_booking_save.txt');
                            $objBook->_writeLog('-------------Public/Private Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            
                        }
                    }
            
            
                    $resSuccess  = $responseData['Data']['Success'];
                    $fairtype = $bookingData['fare_type'];
                    if (!empty($responseData['Data']['Errors'])) {
                        $errMsg = $responseData['Data']['Errors'][0]['Message'];
                        $errCDE = $responseData['Data']['Errors'][0]['Code'];
                        if (empty($errMsg)) {
                            $errMsg = $responseData['Data']['Errors']['Message'];
                            $errCDE = $responseData['Data']['Errors']['Code'];
                        }
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference = $responseData['Data']['UniqueID'];
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
                        $stmtupdate->bindParam(':mfreference', $mfreference);
                        $stmtupdate->bindParam(':traceId', $traceId);
                        $stmtupdate->bindParam(':booking_status', $booking_status);
                        $stmtupdate->bindParam(':booking_date', $booking_date);
                        $stmtupdate->bindParam(':markup', $markup);
                        $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                        $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                        $err_code = $errCDE;
                        $fairtype = $bookingData['fare_type'];
                        $booking_status = $responseData['Data']['Status'];
                        $ticket_status = $responseData['Data']['Status'];
                        $id = $bookingData['id'];
                        $stmtInsert->bindParam(':book_id', $id);
                        $stmtInsert->bindParam(':err_code', $err_code);
                        $stmtInsert->bindParam(':err_msg', $errMsg);
                        $stmtInsert->bindParam(':fare_type', $fairtype);
                        $stmtInsert->bindParam(':book_status', $booking_status);
                        $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                        $stmtInsert->execute();
                        
                        $response = array(
                            'bookingid' => $tempBookingId,
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' => $fairtype,
                            'errors' => $errMsg,
                            'errCde' => $errCDE
                        );
                        $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        echo json_encode($response);
                        exit;
                    } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "CONFIRMED")) {
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference = $responseData['Data']['UniqueID'];
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
                
                        $stmtupdate->bindParam(':mfreference', $mfreference);
                        $stmtupdate->bindParam(':traceId', $traceId);
                        $stmtupdate->bindParam(':booking_status', $booking_status);
                        $stmtupdate->bindParam(':booking_date', $booking_date);
                        $stmtupdate->bindParam(':markup', $markup);
                        $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                
                        $orderstatus = $responseData['Data']['Success'];
                        $response = array(
                            'BookStatus' => $booking_status,
                            'faretype' => $fairtype,
                            'bookingid' => $tempBookingId,
                        );
                        $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        $objBook->_writeLog("Booking Confirmed: ", 'temp_booking_save.txt');
                        $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        echo json_encode($response);
                        exit;
                        // $logResSus =   $booking_status;
                        // $objBook->_writeLog('Success Received\n' . $logResSus, 'booking.txt');
                    } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "BOOKINGINPROCESS")) {
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference = $responseData['Data']['UniqueID'];
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
                        $stmtupdate->bindParam(':mfreference', $mfreference);
                        $stmtupdate->bindParam(':traceId', $traceId);
                        $stmtupdate->bindParam(':booking_status', $booking_status);
                        $stmtupdate->bindParam(':booking_date', $booking_date);
                        $stmtupdate->bindParam(':markup', $markup);
                        $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                
                        $orderstatus = $responseData['Data']['Success'];
                        $response = array(
                            'BookStatus' => $booking_status,
                            'faretype' => $fairtype,
                            'bookingid' => $tempBookingId,
                        );
                        $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        $objBook->_writeLog("Booking BOOKINGINPROCESS: ", 'temp_booking_save.txt');
                        $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                    
                        echo json_encode($response);
                        exit;
                        
                    } elseif (empty($responseData['Data']['Success'])) {
                        $errCDE = '';
                        $errMsg = '';
                        if (!empty($responseData['Data']['Errors'])) {
                            $errMsg = $responseData['Data']['Errors'][0]['Message'];
                            $errCDE = $responseData['Data']['Errors'][0]['Code'];
                            if (empty($errMsg)) {
                                $errMsg = $responseData['Data']['Message'];
                            }
                        }
                        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference = $responseData['Data']['UniqueID'];
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
                
                        $stmtupdate->bindParam(':mfreference', $mfreference);
                        $stmtupdate->bindParam(':traceId', $traceId);
                        $stmtupdate->bindParam(':booking_status', $booking_status);
                        $stmtupdate->bindParam(':booking_date', $booking_date);
                        $stmtupdate->bindParam(':markup', $markup);
                        $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                        $stmtupdate->bindParam(':id', $id);
                        $stmtupdate->execute();
                        $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                
                        $err_code = $errCDE;
                        $fairtype = $bookingData['fare_type'];
                        $booking_status = $responseData['Data']['Status'];
                        $ticket_status = $responseData['Data']['Status'];
                
                        $id = $bookingData['id'];
                        if (empty($errMsg)) {
                            $errMsg = "Null Status Received from Airline";
                            $errCDE = "000";
                        }
                        $stmtInsert->bindParam(':book_id', $id);
                        $stmtInsert->bindParam(':err_code', $err_code);
                        $stmtInsert->bindParam(':err_msg', $errMsg);
                        $stmtInsert->bindParam(':fare_type', $fairtype);
                        $stmtInsert->bindParam(':book_status', $booking_status);
                        $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                        $stmtInsert->execute();
                
                        $response = array(
                            'bookingid' => $tempBookingId,
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' => $fairtype,
                            'errors' => $errMsg,
                            'status' => $responseData['Data']['Status'],
                            'errCde' => $errCDE
                        );
                        
                        $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                        $objBook->_writeLog("", 'temp_booking_save.txt');
                        $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;       
                    } else {
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference = $responseData['Data']['UniqueID'];
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
                
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
                
                        if (empty($mfreference) && ($booking_status == "PENDING")) {
                            $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                
                            $stmtupdate->bindParam(':booking_date', $booking_date);
                            $stmtupdate->bindParam(':markup', $markup);
                            $stmtupdate->bindParam(':booking_status', $booking_status);
                            $stmtupdate->bindParam(':id', $id);
                            if (empty($errMsg)) {
                                $errMsg = "Pending without MF number Direct failure as per api Received";
                                $errCDE = "001";
                            }
                            $stmtupdate->execute();
                            $response = array(
                                'bookingid' => $tempBookingId,
                                'BookStatus' => "Failed",
                                'ticketstatus' => "Failed",
                                'faretype' => $fairtype,
                                'errors' => $errMsg,
                                'errCde' => $errCDE
                            );
                            
                            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                            $err_code = $errCDE;
                            $fairtype = $bookingData['fare_type'];
                            $booking_status = $responseData['Data']['Status'];
                            $ticket_status = $responseData['Data']['Status'];
                            $id = $bookingData['id'];
                            $stmtInsert->bindParam(':book_id', $id);
                            $stmtInsert->bindParam(':err_code', $err_code);
                            $stmtInsert->bindParam(':err_msg', $errMsg);
                            $stmtInsert->bindParam(':fare_type', $fairtype);
                            $stmtInsert->bindParam(':book_status', $booking_status);
                            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                            $stmtInsert->execute();
                
                            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            $objBook->_writeLog("Empty Reference Number + status PENDING", 'temp_booking_save.txt');
                            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                            $objBook->_writeLog("", 'temp_booking_save.txt');
                            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }else if (empty($mfreference) && ($booking_status == "NotBooked")) {
                            $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                
                            $stmtupdate->bindParam(':booking_date', $booking_date);
                            $stmtupdate->bindParam(':markup', $markup);
                            $stmtupdate->bindParam(':booking_status', $booking_status);
                            $stmtupdate->bindParam(':id', $id);
                            if (empty($errMsg)) {
                                $errMsg = "Not Booked, Direct failure as per api Received";
                                $errCDE = "002";
                            }
                            $stmtupdate->execute();
                            $response = array(
                                'bookingid' => $tempBookingId,
                                'BookStatus' => "Failed",
                                'ticketstatus' => "Failed",
                                'faretype' => $fairtype,
                                'errors' => $errMsg,
                                'errCde' => $errCDE
                            );
                            
                            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                
                            $err_code = $errCDE;
                            $fairtype = $bookingData['fare_type'];
                            $booking_status = $responseData['Data']['Status'];
                            $ticket_status = $responseData['Data']['Status'];
                            $id = $bookingData['id'];
                
                            $stmtInsert->bindParam(':book_id', $id);
                            $stmtInsert->bindParam(':err_code', $err_code);
                            $stmtInsert->bindParam(':err_msg', $errMsg);
                            $stmtInsert->bindParam(':fare_type', $fairtype);
                            $stmtInsert->bindParam(':book_status', $booking_status);
                            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                            $stmtInsert->execute();
                            
                            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                            $objBook->_writeLog("", 'temp_booking_save.txt');
                            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }elseif (($responseData['Data']['Success']) && in_array($responseData['Data']['Status'], ["Booked", "Ticketed", "Ticket-In Process", "Pending"])) {
                
                            // echo $responseData['Data']['Status'];  
                            //log write ,booking sts update ,booking date ,markup value
                            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
                
                            // Set the values
                            // Set the current datetime for booking_date
                            $booking_date = date('Y-m-d H:i:s');
                
                
                
                            $mfreference = $responseData['Data']['UniqueID'];
                            $traceId = $responseData['Data']['TraceId'];
                            $booking_status = $responseData['Data']['Status'];
                
                            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                            $id = $bookingData['id'];
                
                            // Bind the parameters
                            $stmtupdate->bindParam(':mfreference', $mfreference);
                            $stmtupdate->bindParam(':traceId', $traceId);
                            $stmtupdate->bindParam(':booking_status', $booking_status); //
                            $stmtupdate->bindParam(':booking_date', $booking_date);
                            $stmtupdate->bindParam(':markup', $markup);
                            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                            $stmtupdate->bindParam(':id', $id);
                
                
                
                            // Execute the query
                            $stmtupdate->execute();
                            //$orderstatus = "order success"; //but cron needed for finalised status
                            $orderstatus = $responseData['Data']['Success'];
                            //=============
                            $logResSus =   $booking_status;
                            $objBook->_writeLog('Success in process Received\n' . $logResSus, 'booking.txt');
                            $response = array(
                                    'BookStatus' => $booking_status,
                                    'faretype' => $fairtype,
                                    'bookingid' => $tempBookingId,
                                );
                                $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                                $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                                $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                                $objBook->_writeLog("", 'temp_booking_save.txt');
                                $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                                echo json_encode($response);
                                exit;
                
                        }else {
                            $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                            $stmtupdate->bindParam(':booking_date', $booking_date);
                            $stmtupdate->bindParam(':markup', $markup);
                            $stmtupdate->bindParam(':booking_status', $booking_status);
                            $stmtupdate->bindParam(':id', $id);
                            if (empty($errMsg)) {
                                $errMsg = $booking_status;
                                $errCDE = "003"; //custom error codes
                            }
                            // Execute the query
                            $stmtupdate->execute();
                            $response = array(
                                'bookingid' => $tempBookingId,
                                'BookStatus' => "Failed",
                                'ticketstatus' => "Failed",
                                'faretype' => $fairtype,
                                'errors' => $errMsg,
                                'errCde' => $errCDE
                            );
                            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                
                            $err_code = $errCDE;
                            $fairtype = $bookingData['fare_type'];
                            $booking_status = $responseData['Data']['Status'];
                            $ticket_status = $responseData['Data']['Status'];
                            $id = $bookingData['id'];
                            
                            $stmtInsert->bindParam(':book_id', $id);
                            $stmtInsert->bindParam(':err_code', $err_code);
                            $stmtInsert->bindParam(':err_msg', $errMsg);
                            $stmtInsert->bindParam(':fare_type', $fairtype);
                            $stmtInsert->bindParam(':book_status', $booking_status);
                            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                            $stmtInsert->execute();
                            
                            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                            $objBook->_writeLog("", 'temp_booking_save.txt');
                            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                        
                    }
                    // WEBFARE BOOKING ENDS HERE


                    
                    




                }
                // PROCEED TO ORDER TICKET ENDS
                
                
            }
        }
        $title = ucfirst("Payment ".$responseArray['transactions'][0]['responseText']);
        $transaction_id = $responseArray['transactions'][0]['id'];
        $session_id = $responseArray['id'];
        ?>

            <div class="container-jumbotron">
                <div class="bodycontant">
                    <div class="<?php if( $responseArray['transactions'][0]['responseText'] == "APPROVED" ) {echo "content_success";} else {echo "content_cancel";}?>">
                        <?php
                        if( $responseArray['transactions'][0]['responseText'] == "APPROVED" ) {?>
                            <div class="icon-container">
                                <div class="success"></div>
                            </div>
                            <?php
                        } else {?>
                            <div class="icon-container">
                                <div class="error"></div>
                                
                            </div>
                            <?php
                        }?>

                        <h1><?php echo $title;?></h1>
                        <?php
                            if( $responseArray['transactions'][0]['responseText'] == "APPROVED" ) {
                                $text_1 = "";
                                $text_2 = "Your payment has been successfully approved, and we are now processing your booking. This may take a few moments. Once confirmed, you will receive a booking confirmation email with all the details. <br /><br />You can check your booking status in your account or manage booking section. Thank you for booking with us, and we wish you a great journey ahead!";
                                $button_text = "Manage Bookings";

                                if (isset($_SESSION['user_id'])) {
                                    $button_url = "user-dashboard";
                                    $modal_show = "";
                                } else{
                                    $button_url = "javascript:void(0);";
                                    $modal_show = "data-toggle='modal' data-target='#LoginModal'";
                                }

                            } else {
                                $text_1 = "Your payment was declined. Your booking is not confirmed.";
                                $text_2 = "Unfortunately, your payment was declined by the payment provider. This could be due to insufficient funds, incorrect payment details, or bank restriction. Please verify your payment information and try again. <br /><br />    If the issue persists, consider using a different payment method or contact your bank for further assistance. Your booking has not been confirmed, so you will need to complete the payment to proceed.";
                                $button_text = "Search Again";
                                $button_url = "index";
                                $modal_show = "";
                                // if (isset($_SESSION['user_id'])) {
                                //     $button_url = "user-dashboard";
                                //     $modal_show = "";
                                // } else{
                                //     $button_url = "javascript:void(0);";
                                //     $modal_show = "data-toggle='modal' data-target='#LoginModal'";
                                // }
                            }
                        ?>
                        <p><?php echo $text_1;?></p>
                        <p><?php echo $text_2;?></p>
                        <a href="<?php echo $button_url;?>" <?php echo $modal_show;?> class="btn btn-typ7 ml-3 btn-primary"><?php echo $button_text;?></a>
                    </div>
                </div>
            </div>
        <?php

    } else {
        ?>
        <script>
            Swal.fire({
                title: "Payment Status Uncertain",
                text: "We did not receive a response from the payment gateway. Your booking is not confirmed, and no payment has been received.",
                icon: "error",
                confirmButtonText: "Close",
                confirmButtonColor: "#f57c00", 
                allowOutsideClick: false, 
            }).then((result) => {
                    window.location.href = "index";
            });
        </script>
        <?php
    }
} else {
    ?>
	<script>
        Swal.fire({
            title: "Payment Status Uncertain",
            text: "We did not receive a response from the payment gateway. Your booking is not confirmed, and no payment has been received.",
            icon: "error",
            confirmButtonText: "Close",
            confirmButtonColor: "#f57c00", 
            allowOutsideClick: false, 
        }).then((result) => {
                window.location.href = "index";
        });
    </script>
    <?php
}
require_once("includes/footer.php");
require_once("includes/login-modal.php");
?>