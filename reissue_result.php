<?php
session_start();
//$_SESSION['user_id'] =36;
if (!isset($_SESSION['user_id'])) { //for test  environment 
?>
    <script>
        window.location = "index.php"
    </script>
  <?php }
 if(!isset($_POST['mfreNum'])){
	 echo "Err1";exit;

 }
 else {
       require_once("includes/header.php");
	  include_once('includes/common_const.php');
      include_once('includes/class.cancel.php');
      $objCancel     =   new Cancel();
      $mfreNum   =  trim( $_POST['mfreNum']);
     $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);
     $PreferenceOption =   trim($_POST['PreferenceOption']);
     $PTRId         =   trim($_POST['PTRId']);

   //Reissue Accept api
   $requestData = array(
    'ptrType' => 'ReIssueQuote',
    'mFRef' => $mfreNum,
    'PTRId' => $PTRId,
    'PreferenceOption' => $PreferenceOption,
    'AcceptQuote' => "yes",
    'AdditionalNote' => "Please Reissue as for quoted fare"
);
//print_r($requestData);
 $endpoint   =   'PostTicketingRequest';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
  
  // print_r($result);  exit;
  if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
          $logReQ =   print_r(json_encode($requestData), true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
                    $objCancel->_writeLog('REsponse Received for  Reissue Quote Accept MF:\n'.$mfreNum,'reissueQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'reissueQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'reissueQuote.txt');
        $objCancel->_writeLog('REquest Received\n'.$logReQ,'reissueQuote.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'reissueQuote.txt');

        //=====================
      /*  {
  "Success": true,
  "Data": {
    "PTRId": 5274,
    "PTRType": "ReIssue",
    "SLAInMinutes": 60,
    "PTRStatus": "InProcess",
    "Message": "Your request for reissue has been submitted successfully. Our team will process it within 60 minutes."
  }
} */
        //======================
        if (isset($responseData['Success']) && $responseData['Success']) {
            echo "kkk";exit;
           // $reissue_status  =   1;
        $PTRType    =   $responseData['Data']['PTRType'];
        $PTRId    =   $responseData['Data']['PTRId'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $hours = $objCancel->calculateHoursFromSLAMinutes($SLAInMinutes);

            $objCancel->_writeLog('Step 1Success reissueQuote Accept '.$PTRStatus,'reissueQuote.txt');
            // $PTRStatus = "InProcess";
                                                              if( $PTRStatus == "InProcess"){
			                                                             $message   =   "Your ReIssue for NEw Flight Option Request  is :".$PTRStatus." This will update within ". $hours." Hours";
                                                                         //*****************************************
                                                                         

                                                                         //*****************************************
			                                                }
            else{
                //ptr statu completed expected 
                             $message = "Goto NExt level Get Exchange Quote for Reissue confirmation"; 
                         /*   $requestData = array(
                'ptrType' => 'GetExchangeQuote',
                'MFRef' => $mfreNum,
                'PTRId' => $PTRId,
                'Page' => 1
            );*/
            }
            //table insertions after success reissue response





        }//success response ends
        else if(empty($responseData['Data'])){
            
        if(!empty($responseData['Message'])){    echo "uu";exit;                       
            $message    =   $responseData['Message'];
            $message_new    = $message;
            //    $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   
        //may alreay cancelled 
                         $response_New = array(
                                                                                                                'status' => 'error', // You can set this to 'error' in case of an error
                                                                                                                'message' => $message
                                                                                                            );
                                                                                                                          $objCancel->_writeLog('step data empty '.$message,'searchreissue.txt');
     }


                                 } 
           $objCancel->_writeLog('step end of reissue accept  ========= '.$message,'reissueQuote.txt');
                                                   echo json_encode($response_New);
exit;
                                                            
                                                            






        //==============================

}
exit;
?>