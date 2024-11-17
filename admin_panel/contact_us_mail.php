<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for agent  details page
*****************************************************************************/
//echo "lllllllllllllllllll<pre/>";
//print_r($_POST);//exit;
if ( !isset($_POST['email'])) {
	  echo 'error';exit;
}
if(isset($_POST['email'])){

 //include_once "includes/class.contents.php";
 include_once('../mail_send.php');

  $subject = trim($_POST['subject']);
  $messageDatacontent = trim($_POST['message']);
  $email    =   trim($_POST['email']);

  $email_flag = "";
  if ( isset($_POST['email_flag'])) {
    $email_flag    =   $_POST['email_flag'];
  }

  

      $headers="";
     // $toEmail = "no-reply@bulatrips.com";
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
                                                    src="https://bulatrips.com/images/bulatrips-logo.png"
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
    if( $email_flag == "yes"  ) {
        $contacts= confirmationMail($email,$subject, $messageDatacontent,$headers,$email_flag);
    } else {
        $contacts = true;
    }
      
      echo $contacts ;exit;
      // echo 'success';exit; //email sent successfully

         
 }
 
?>
