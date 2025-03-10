<?php
// echo 'helo';exit;
error_reporting(E_ALL);
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
            $resetLink = "reset_password?token=".$token;
            // Send the email using a library or your preferred method
            //==============================================
            
            $toEmail = $email;
            $subject = "BulaTrips User Password Reset";
            $messageDatacontent = "Please click the following link to reset your password: " . $resetLink;
            //****************************
            
             $messageData = '
            <html>
            <body>
            <table style="width:100%">
                <tbody>
                    <tr>
                        <td>
                            <center>
                                <table style="width:80%;margin:0 auto">
        
                                    <tbody>
                                        <tr>
                                            <td style="text-align:center;padding-bottom:15px;padding-top:15px">
                                                <h2 style="margin-top:0;margin-bottom:0">
                                                    <img width="125" height="30"
                                                        src="https://bulatrips.com/images/Image-Logo-vec.png"
                                                        alt="Bulatrip" title="Bulatrip"
                                                        style="height:30px;width:125px;display:inline-block;margin-top:0;margin-bottom:0"
                                                        class="CToWUd" data-bit="iit">
                                                </h2>
                                            </td>
                                        </tr>
        
        
                                        <tr>
                                            <td bgcolor="#ffffff" style="padding-top:20px;text-align:center">
        
                                                <div width="100%"
                                                    style="max-width:480px;padding:5pt 0;background-color:#eff5fc;border-radius:10px;margin:0 auto;width:calc(100% - 32px);margin-bottom:24px">
        
        
        
                                                
        
                                                </div>
        
        
        
                                                <div align="center" style="padding:0 10px;padding-bottom:5px">
                                                    <p
                                                        style="font-family:Arial,sans-serif;color:#000000;letter-spacing:-0.5px;text-align:center;margin-top:0;margin-bottom:0">
                                                        '.$messageDatacontent.'
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </center>
                        </td>
                    </tr>
                </tbody>
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