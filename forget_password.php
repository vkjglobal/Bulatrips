<?php
// echo 'helo';exit;
error_reporting(E_ALL);
include_once('includes/common_const.php');
header("Access-Control-Allow-Origin: *");
// Define allowed methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Define allowed headers
header("Access-Control-Allow-Headers: Content-Type");
    if(!isset($_POST['forgot_email'])) {
        echo 'error11';
        exit;
        // Could not get the data that should have been sent.
        //exit('Please fill both the username and password fields!');
    } else{
        $email = $_POST['forgot_email'];
        //   echo $email;
    }
    // Validate the email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //  $error = "Invalid email address";
    echo 'error12';exit;
    }
    else {
        include_once('includes/class.Data.php');//forget password 

        include_once('mail_send.php');

        $forgetObj = new Data();//new object for forget

        $tableName = 'users';

        $user_data = $forgetObj->selectDatabyEmail($tableName,$email);
        // print_r($user_data);

        if(count($user_data)>0) {  

            // Generate a unique token and store it in the database
            $token = bin2hex(random_bytes(32));
            // insert token
            $reset_tocken_id = $forgetObj->insertPassword_ResetToken($user_data[0]['id'], $token);
            
            // $resetLink = "https://localhost/Travelsite/Travelsite/reset_password.php?token=".$token;            
            $resetLink = ENVIRONMENT_VAR."reset_password?token=".$token;
            // Send the email using a library or your preferred method
            //==============================================
            
            $toEmail = $email;
            $subject = "BulaTrips User Password Reset";
            $messageDatacontent = "Please click the following link to reset your password: " . $resetLink;
            //****************************
            $backgroundColor = "#f8f9fa";
                $containerBgColor = "#ffffff";
                $headerBgColor = "#0000ff";
                $approvedColor = "#0000ff";
                $failedColor = "#f57c00";
                $logoUrl = "https://bulatrips.com/images/Image-Logo-vec.png";

            $messageData = $messageData = '<!DOCTYPE html>
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
                        <td align="center" style="padding: 15px; font-size: 20px; font-weight: bold; color: #ffffff; background-color: #007bff;">
                            Forgot Password Request
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 20px; color: #333333; font-size: 16px;">
                            <p>We received a request to reset your password for your Bulatrip account.</p>
                            <div style="background-color: #f1f1f1; padding: 15px; border-radius: 5px; font-size: 16px; text-align: center;">
                                <p><strong>If you requested this password reset, please click the button below:</strong></p>
                                <a href="'.$resetLink.'" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px;">Reset Password</a>
                            </div>
                            <p style="margin-top:12px;">If you did not request a password reset, please ignore this email or contact support if you have any concerns.</p>
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
            

            //***************************
           
           $headers="";
           $toEmail = $email;
           
               confirmationMail($toEmail, $subject, $messageData,$headers);

          /*  if (mail($to, $subject, $message, $headers)) {
             echo 'Email sent successfully.';
            } else {
             echo 'Failed to send email.';
            }
            */
           // echo 'error14';exit; //failed to send email
             echo 'error15';exit; //femail sent successfully
            //=================================================
            // Display a success message to the user
            $success = "Password reset instructions have been sent to your email address.";
        } else {
            // Display an error message if the email does not exist
          echo 'error13';exit;
        }
    }
?>