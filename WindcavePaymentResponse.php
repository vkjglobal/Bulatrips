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

if( isset($response) && $response != '') { 
    $responseArray = json_decode($response, true);
    // echo "<pre>";
    //     print_r($responseArray);
    // die;
    
    if( is_array($responseArray) && count($responseArray) > 0 && isset($responseArray['transactions'][0]['id']) ) {    
        
        $check_trans = $conn->prepare('SELECT * FROM payment_user WHERE trn_id LIKE :trn_id AND trn_session_id LIKE :trn_ses_id');
        $check_trans->bindParam(':trn_id', $responseArray['transactions'][0]['id']);
        $check_trans->bindParam(':trn_ses_id', $responseArray['id']);
        $check_trans->execute();
        $rowCount = $check_trans->rowCount();
        
        // include('update_booking_after_payment.php');
        // die;
        
        if( $rowCount == 0 ) {
            $objBook    =   new BookScript();
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
                
                $messageData = '<!DOCTYPE html>
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
                confirmationMail($toEmail, $subject, $messageData,$headers);
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
                            } else {
                                $text_1 = "Your payment was declined. Your booking is not confirmed.";
                                $text_2 = "Unfortunately, your payment was declined by the payment provider. This could be due to insufficient funds, incorrect payment details, or bank restriction. Please verify your payment information and try again. <br /><br />    If the issue persists, consider using a different payment method or contact your bank for further assistance. Your booking has not been confirmed, so you will need to complete the payment to proceed.";
                                $button_text = "Search Again";
                                $button_url = "index";
                            }

                            if (!isset($_SESSION['user_id'])) {
                                $button_url = "javascript:void(0);";
                                $modal_show = "data-toggle='modal' data-target='#LoginModal'";?>
                            <?php
                            } else{
                                $button_url = "user-dashboard";
                                $modal_show = "";
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