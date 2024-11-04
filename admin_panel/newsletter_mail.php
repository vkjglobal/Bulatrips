<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for agent  details page
*****************************************************************************/
//echo "<pre/>";print_r($_POST);exit;
if ( !isset($_POST['id'])) {
	  echo 'error';exit;
}
if(isset($_POST['id'])){

 include_once "includes/class.contents.php";
 include_once('../mail_send_bulk_newsletter.php');

 $objContent     =   new Contents();
  $id =   $_POST['id'];
  $datas	=   $objContent->getListNews($id);
  $email_bulk   =   $objContent->getListNewsletters();
    // echo "<pre/>";print_r($email_bulk);exit;
     
     $subject         =    trim($datas[0]['subject']);
     $message         =   trim($datas[0]['content']);
     $message           = html_entity_decode($message);

      $headers="";
     // $toEmail = "soumya.reubro@gmail.com";
      $newsletter= confirmationMail($email_bulk,$subject, $message,$headers);
      echo 'success'. $newsletter;exit;
      // echo 'success';exit; //email sent successfully

         
 }
 
?>
