<?php

// Programmer	::> Soumya's mail part
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

 
require 'vendor/autoload.php';
require_once('includes/dbConnect.php');

function confirmationMail($to, $subject, $content, $headers = "")
{
    // print_r($content);
   /* $alreadySent = checkIfEmailSentForBooking($bookingId);
    if ($alreadySent==1) {
        return; // Email has already been sent for this booking, do nothing
    }
    */
    $mail = new PHPMailer(true);
    // $from = "no-reply@bulatrips.com";
   
    try {
        //Server settings
       $mail = new PHPMailer;
        // $mail->SMTPDebug = 2; 
        // Set the mail configuration server details
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.bulatrips.com';                   // Specify the SMTP server
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'no-reply@bulatrips.com';           // SMTP username
        $mail->Password = 'Reubro@2023';                      // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; 

        //Recipients
        $mail->setFrom('no-reply@bulatrips.com', 'bulatrips.com');
        $mail->addAddress($to, '');     // Add a recipient
        // $mail->addAddress('ellen@example.com');               // Name is optional
        // $mail->addReplyTo('no-reply@bulatrips.com', 'bulatrips.com');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
      //  markEmailSentForBooking($bookingId);
        // die("here");
        return true;
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

}

function checkIfEmailSentForBooking($bookingId)
{
    global $conn;
    $sql = "SELECT confirm_mail_status FROM temp_booking WHERE id = :bookingID";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bookingID', $bookingId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['confirm_mail_status'];
}

function markEmailSentForBooking($bookingId)
{
    global $conn;
    $sql = "UPDATE temp_booking SET confirm_mail_status = 1 WHERE id = :bookingID";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':bookingID', $bookingId);
    $stmt->execute();
}



function ticketSendMail1($to, $subject, $content, $pdfcontent )
{
    // print_r($content);
  
    $mail = new PHPMailer(true);
    // $from = "no-reply@bulatrips.com";
    $mail->addStringAttachment($pdfcontent, 'ticket.pdf');
    try {
        //Server settings
       $mail = new PHPMailer;
        // $mail->SMTPDebug = 2; 
        // Set the mail configuration server details
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.bulatrips.com';                   // Specify the SMTP server
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'no-reply@bulatrips.com';           // SMTP username
        $mail->Password = 'Reubro@2023';                      // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; 

        //Recipients
        $mail->setFrom('no-reply@bulatrips.com', 'bulatrips.com');
        $mail->addAddress($to, '');     // Add a recipient
       

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        // die("here");
        return true;
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

}

function ticketSendMail($to, $subject, $content, $pdfcontent)
{
    // print_r($content);

    $mail = new PHPMailer(true);
    // $from = "no-reply@bulatrips.com";
    $mail->addStringAttachment($pdfcontent, 'ticket.pdf');
    try {
        // Set the mail configuration server details
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.bulatrips.com';                   // Specify the SMTP server
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'no-reply@bulatrips.com';           // SMTP username
        $mail->Password = 'Reubro@2023';                      // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; 

        //Recipients
        $mail->setFrom('no-reply@bulatrips.com', 'bulatrips.com');
        $mail->addAddress($to, '');     // Add a recipient

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;

        // Send the email
        $mail->send();
        // die("here");
        return true;
        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

//new mail cancel

function sendMail($to, $subject, $content, $headers = "")
{
    // print_r($content);
   /* $alreadySent = checkIfEmailSentForBooking($bookingId);
    if ($alreadySent==1) {
        return; // Email has already been sent for this booking, do nothing
    }
    */

        // Set the headers for HTML content
 // $headers .= "MIME-Version: 1.0" . "\r\n";
  //$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $mail = new PHPMailer(true);
    // $from = "no-reply@bulatrips.com";
   
    try {
        //Server settings
       $mail = new PHPMailer;
     //  $mail->SMTPDebug = 2; 
        // Set the mail configuration server details
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.bulatrips.com';                   // Specify the SMTP server
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'no-reply@bulatrips.com';           // SMTP username
        $mail->Password = 'Reubro@2023';                      // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; 

        //Recipients
        $mail->setFrom('no-reply@bulatrips.com', 'bulatrips.com');
        $mail->addAddress($to, '');     // Add a recipient
        // $mail->addAddress('ellen@example.com');               // Name is optional
        // $mail->addReplyTo('no-reply@bulatrips.com', 'bulatrips.com');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        
       if ($mail->send()) {
            // Email sent successfully
            $isSent = true;
        }

        // echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    return $isSent; 

}

?>