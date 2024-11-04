<?php

// Include the NewsletterSubscriber class
include_once('includes/class.Contact.php');
require_once('includes/dbConnect.php');
include_once('includes/common_const.php');
include('sendmail.php');


if (isset($_POST)) {
    // Get the submitted email address

    $email = filter_var($_POST['contact-email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars($_POST['contact-name'], ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($_POST['contact-subject'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['contact-message'], ENT_QUOTES, 'UTF-8');


    // Database connection credentials

    
    $contact = new Contact($conn);

    $resultMessage = $contact->contact($email, $name, $subject, $message);
    if($resultMessage)
    {
        // Create a PDO database connection
        try {
            // print_r($resultMessage);exit();
        // mail content
            $toEmail = $email;
            $subject = "Contact Us - Bulatrips.com";
            $messageData = '
            <html>
                <body>
                    <table width="500" border="0" align="center" cellpadding="1" cellspacing="1">
                    <tbody>
                        <tr>
                            <td align="center"><img src="https://bulatrips.com/images/bulatrips-logo.png" width="209" height="70" alt="" class="CToWUd" data-bit="iit">
                                <hr>
                            </td>
                        </tr>
                        <tr>
                            <td align="center">
                                <p><strong>Customer Contact Info from Bulatrips.com</strong></p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Name: '.$name.' <br>
                                    Email: <a href="mailto:'.$email.'" target="_blank">'.$email.'l.<span class="il">com</span></a></p>
                                    Subject:'.$subject.'
                            </td>
                
                        </tr>
                        <tr>
                            <td> 
                            '.$message.'
                
                                <hr>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </body>
            </html>
            ';
            $headers="";
            // echo $messageData;

        // mail($toEmail, $subject, $messageData, $headers);
        contactUsMail($toEmail, $subject, $messageData);

            // Output the result message
            echo $resultMessage;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }else{
        echo 'Error in insertion';
    }
}
?>

