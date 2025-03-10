<?php
session_start();
if (isset($_POST['usersignup'])) {
    require_once('includes/dbConnect.php');
    require_once('includes/common_const.php');
    include_once('mail_send.php');
    $fname = trim($_POST['userfname']);
    $lname = trim($_POST['userlname']);
    $email = trim($_POST['useremail']);
    $mobile = trim($_POST['userphone']);
    $dialcode = trim($_POST['country_code']);
    $password = trim($_POST['userpassword']);
    $searchFlights = trim($_POST['searchFlights']);
    $role = 1;
    $hasedpassword = hash('sha256', $password);
    
    $checkEmailSql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $checkEmailQuery = $conn->prepare($checkEmailSql);
    $checkEmailQuery->bindParam(':email', $email, PDO::PARAM_STR);
    $checkEmailQuery->execute();
    $emailExists = $checkEmailQuery->fetchColumn();
    $token = substr(strval(random_int(1000000000, 9999999999)), 0, 15);
    $hashedToken = substr(hash('sha512', $token), 0, 15);
    
    if ($emailExists > 0) {
        echo "email_error";
    } else {
        $sql = "INSERT INTO users(first_name,last_name,email,dial_code,mobile,st_token,password,role) VALUES(:fname,:lname,:email,:dialcode
        ,:mobile
        ,:st_token
        ,:password
        ,:role)";
        $query = $conn->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_INT);
        $query->bindParam(':dialcode', $dialcode, PDO::PARAM_STR);
        $query->bindParam(':st_token', $hashedToken, PDO::PARAM_STR);
        $query->bindParam(':password', $hasedpassword, PDO::PARAM_STR);
        $query->bindParam(':role', $role, PDO::PARAM_INT);
        
        $query->execute();
        $lastInsertId = $conn->lastInsertId();
        
        if ($lastInsertId) {

            $toEmail = $email;
            $subject = "Confirm Your Email to Activate Your Bulatrips Account";
            $messageData = '<!DOCTYPE html>
                            <html>
                            <head>
                                <title>Confirm Your Email</title>
                            </head>
                            <body style="margin: 0; padding: 30px; background-color: #f4f4f4;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
                                    <tr>
                                        <td align="center">
                                            <table role="presentation" cellspacing="10" cellpadding="50" border="0" width="600" style="background-color: #ffffff; padding: 20px; border-radius: 10px; text-align: center; font-family: Arial, sans-serif; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);">
                                                <tr>
                                                    <td>
                                                        <h2 style="color: #333333; margin-bottom: 10px;">Confirm Your Email</h2>
                                                        <p style="color: #555555; font-size: 16px;">Thank you for signing up for <strong>Bulatrips</strong>! We are excited to have you on board.</p>
                                                        <p style="color: #555555; font-size: 16px;">To activate your account and start booking flights, please confirm your email by clicking the button below:</p>
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 20px auto;">
                                                            <tr>
                                                                <td align="center" bgcolor="#007bff" style="border-radius: 5px;">
                                                                    <a href="'.ENVIRONMENT_VAR.'accountConfirmation?token='.$hashedToken.'"  target="_blank" style="display: inline-block; font-size: 18px; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; background-color: #007bff; font-weight: bold;">Confirm My Email</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <p style="color: #777777; font-size: 14px; margin-top: 20px;">If you didn’t sign up for an account, you can safely ignore this email.</p>
                                                        <p style="color: #777777; font-size: 14px;">Best Regards,<br><strong>Bulatrips Team</strong></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </body>
                        </html>
                        ';
                    $headers="";
                    confirmationMail($toEmail, $subject, $messageData,$headers);

            if( isset($searchFlights) && $searchFlights == "true" ) {
                echo "flights_redirectation";

                $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->bindParam(':id', $lastInsertId);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['customer_role-id'] = $user['role'];
            } else {
                echo "registered";
            }
            // if ($role == 1) {
            //         echo "<div id='success-message' class='alert alert-success alert-dismissible fade show' role='alert'> You have signed up successfully.
            //                 <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
            //                 <span aria-hidden='true'>&times;</span>
            //                 </div>";
            // }
        } else {
            echo "error";
        }
    }
} else {
    echo "error";
}