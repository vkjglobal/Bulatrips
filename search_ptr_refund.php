<?php
 error_reporting(0);
ini_set('display_errors', 0); 
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();
   if (!isset($_POST['MFnum'])){
       echo "Err1";exit;

   }
   else{

 $mfreNum   =  trim( $_POST['MFnum']);
 $ptr_id    =    trim( $_POST['ptr_id']);
  $TotalRefundAmount   =   trim($_POST['refundAmount']);
//echo $TotalRefundAmount;exit;
 // $cancel_booking_Id =   trim($_POST['cancel_booking_Id']);
 
    $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);

 //    $void_eligible   =   trim($_POST['void_eligible']);
     //=======
     
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
   // $mfreNum    =   "MF23731023";
//    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
 //   $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
 //   $void_eligible = filter_var($void_eligible, FILTER_SANITIZE_NUMBER_INT);
     //=======
   // echo "LLL".$mfreNum;exit;

  /*   $bookCanusers_req      =   $objCancel->BookCancelUsers($bookingId,$userId); 
     $childpsnger        = $bookCanusers_req[0]['child_count'];
    if($childpsnger === 0){
        $allow_child    =   false;
    }
    elseif($childpsnger > 0){
        $allow_child    =   true;
    }
    
    
     //============request body for entire booking cancel============================================
     // Initialize the main passengers array
$passengersArray = array();

foreach ($bookCanusers_req as $k => $val) {
    $firstname = $val['first_name'];
    $lastname = $val['last_name'];
    $title = $val['title'];
    $eticket = $val['e_ticket_number'];
    $passenger_type = $val['passenger_type'];

    // Create the passenger array for the current passenger
    $passenger = array(
        "firstName" => $firstname,
        "lastName" => $lastname,
        "title" => $title,
        "eTicket" => $eticket,
        "passengerType" => $passenger_type
    );

    // Add the passenger array to the main passengers array
    $passengersArray[] = $passenger;
}
*/
//print_r($passengersArray);exit;
// Now you have the complete passengers array containing all passenger information
// You can create the main request body array using this passengers array

// For example, creating the main request body array
$requestData = array(
    'ptrType' => 'Refund',
    'MFRef' => $mfreNum,
    'PTRId' => $ptr_id,
    'Page' => 1
);
// print_r($requestData);exit;



//$mfreNum = "MF23709123";
//===================test api request==========


        $endpoint   =   'Search/PostTicketingRequest';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
   //  echo $apiEndpoint."\n".BEARER;
//print_r($result);  exit;
       
        // Send the API request
    //****************************************************************
                                                              /*             $response    =     '{
                                                                                                         "Data": {
                                "PTRDetail": [
                                  {
                                    "PTRId": 10680,
                                    "PTRType": "Refund",
                                    "MFRef": "MF23731023",
                                    "BookingStatus": "Ticketed",
                                    "PTRStatus": "InProcess",
                                    "CreatedBy": "XPay Global",
                                    "Resolution": "RefundRequested",
                                    "ProcessingMethod": "Manual",
                                    "pTRPaxDetails": [
                                      {
                                        "Id": 11986,
                                        "PTRId": 10680,
                                        "PaxId": 365617,
                                        "TicketNumber": "2289699426651",
                                        "TicketStatus": 0,
                                        "IsActive": true,
                                        "PassengerType": "ADT",
                                        "Tittle": "MR",
                                        "FirstName": "AVIKASH",
                                        "LastName": "AVIKASH"
                                      }
                                    ],
                                    "PTRTypeStatus": 10
                                  }
                                ]
                              },
                              "Success": true
                                                                                                                                }';     */
//********************************************************************************
  /*        $response    =     '{
                                                                                                         "Data": {
    "PTRDetail": [
      {
        "PTRId": 10680,
        "PTRType": "Refund",
        "MFRef": "MF23731023",
        "BookingStatus": "Refunded",
        "PTRStatus": "Completed",
        "CreditNoteNumber": "3101373",
        "TotalRefundAmount": "0.89",
        "Currency": "USD",
        "CreatedBy": "XPay Global",
        "Resolution": "Refunded",
        "CreditNoteStatus": "Unpaid",
        "ProcessingMethod": "Manual",
        "pTRPaxDetails": [
          {
            "Id": 11986,
            "PTRId": 10680,
            "PaxId": 365617,
            "TicketNumber": "2289699426651",
            "TicketStatus": 0,
            "IsActive": true,
            "PassengerType": "ADT",
            "Tittle": "MR",
            "FirstName": "AVIKASH",
            "LastName": "AVIKASH"
          }
        ],
        "PTRTypeStatus": 7
      }
    ]
  },
  "Success": true                                                              }';    */
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
        $logReQ =   print_r($requestData, true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','search_refund.txt');
                        $objCancel->_writeLog('userId is '.$userId,'search_refund.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'search_refund.txt');
                                                    $objCancel->_writeLog('Request Received\n'.$logReQ,'search_refund.txt');

                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'search_refund.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'search_refund.txt');
 
                        //write log
    // print_r($responseData);exit;
        //=====================================
     // $traceId    =    $responseData['Data']['TraceId'];
      $precancelsts   =   'post';
     $message = ""; 
    
      $CreditNoteNumber ='';
      $CreditNoteStatus ='';
      $Resolution   =   '';
      if(isset($responseData['Success'])){
       //  echo    $PTRId    =   $responseData['Data']['PTRDetail'][0]['PTRId'];exit;
if(!empty($responseData['Data']['PTRDetail'])) {
   
 //   print_r($responseData);exit;
            $cancel_status  =   0;
             $PTRId    =   $responseData['Data']['PTRDetail'][0]['PTRId'];
            $PTRType    =   $responseData['Data']['PTRDetail'][0]['PTRType'];
            $BookingStatus   =   $responseData['Data']['PTRDetail'][0]['BookingStatus'];
            $PTRStatus      =   $responseData['Data']['PTRDetail'][0]['PTRStatus'];     
            
            $Resolution   =   $responseData['Data']['PTRDetail'][0]['Resolution'];
            $ProcessingMethod   =   $responseData['Data']['PTRDetail'][0]['ProcessingMethod'];
            $CreditNoteNumber   =   $responseData['Data']['PTRDetail'][0]['CreditNoteNumber'];
            $CreditNoteStatus   =   $responseData['Data']['PTRDetail'][0]['CreditNoteStatus'];
        //   $TotalRefundAmount  +=   $responseData['Data']['PTRDetail'][0]['TotalRefundAmount'];
             $Currency   =   $responseData['Data']['PTRDetail'][0]['Currency'];

            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'search_refund.txt');
             $objCancel->_writeLog('Step 1 Resolution '.$Resolution,'search_refund.txt');

              $success_can_sts    =0;
            foreach($responseData['Data']['PTRDetail'][0]['pTRPaxDetails'] as $k => $val){
                 $pax_booking_id_transaction =   $val['Id'];
                 $PaxId =   $val['PaxId'];
                 $TicketStatus =   $val['TicketStatus'];
                 $is_active_booking_status =   $val['IsActive'];                
                $ticket_num =   $val['TicketNumber']; 
            
                if(($PTRStatus == "Completed") && ($Resolution == "Refunded")){
                    //cancellation success
$cancel_status		=	1;
                                   $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$Currency, $is_active_booking_status,$cancel_status);                                                   

                     
                      $update_cancelBooking_status      =    $objCancel->updateInDB_cancelbooking('cancel_booking',$ticket_num);
                       $update_TravellerB_result      =    $objCancel->updateInDB_trav('travellers_details',$ticket_num);
                      $cancel_status    =1;

                }
                else{
               
                      $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber='',$PTRStatus,$CreditNoteStatus='', $ticket_num ,$pax_booking_id_transaction ='',$PaxId,$TicketStatus,$TotalRefundAmount='',$Currency='',$is_active_booking_status,$cancel_status);                                                   
                }               
                             
                                          
                                                     

            }
                   $count_ticketed_temp    =   $objCancel->count_ticketed__temp_book('travellers_details',$bookingId);
                    if($count_ticketed_temp == 0){
                        //  update tempbooking and traveller details tables with cancelled sts 
                   $update_tempB_result           =   $objCancel->updateInDB_temp_book('temp_booking',$mfreNum);
                   }
               //    echo "LLL";print_r($TotalRefundAmount)  ;exit;
//mail code
                                         
            //   var_dump($update_TravellerB_result);exit;
            if($cancel_status == 1){
                             $message   =   "Your Canellation is :".$PTRStatus." Total Refundable Amount is : ". $Currency." ".$TotalRefundAmount."Please note Your CreditNoteNumber: ".$CreditNoteNumber;

            }
            else{
                $message   =   "Your Canellation is :".$PTRStatus;
            }
         //***********************mail******
         include_once('mail_send.php');

  $subject = "Booking with BulaTrips cancellation message";
  $messageDatacontent ="Your Canellation is :".$PTRStatus." Total Refundable Amount is : ". $Currency." ".$TotalRefundAmount."Please note Your CreditNoteNumber: ".$CreditNoteNumber;
  $userDetails=$objCancel->getUSerDetails('users', $userId);

        $email=   $userDetails['email'];
        $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
        $content    =   '<p>Hello '. $name .',</p>
                            <p>Your Cancellation for Ticket Number: is <strong>'.$PTRStatus.'</strong></p>
                            <p>Total Refundable Amount is: <strong>'. $Currency.' '.$TotalRefundAmount.'</strong></p>
                            <p>Please note Your CreditNoteNumber: <strong>'.$CreditNoteNumber.'</strong></p>';
                        $messageData =   $objCancel->getEmailContent($content);
 // print_r($messageData);exit;
      $headers="";
      $email = "no-reply@bulatrips.com";
         
    $contacts= sendMail($email,$subject, $messageData,$headers);
  //echo $contacts ;exit;
      // echo 'success';exit
         //*************************mail ends *************

                              $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message,
                    'ptr_id' => $PTRId,
                    'refundamount' => $TotalRefundAmount
                );
              //  print_r($response_New);
      }
      else if(isset($responseData['Data']['Errors']) && is_array($responseData['Data']['Errors'])) {
     // print_r($responseData['Data']['Errors']);exit;
    foreach ($responseData['Data']['Errors'] as $error) {
        $errorCode = $error['Code'];
        $errorMessage = $error['Message'];
       $cancel_status = 0;
       $message_new = $errorMessage;
                         //    $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

         //  $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode,$mfreNum,$traceId,$httpCode,$cancel_status);
           //echo $errorCode;exit;
        $message    = "Problem in Cancellation";
          $cancel_status = 0;
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
                       $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   

         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'search_refund.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
              $cancel_status = 0;
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
            $message_new    = $message;
               $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   
         
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'search_refund.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){   // echo "uu";exit;
                           
                              $cancel_status = 0;
               
                        $message    =   $responseData['Message'];
                         $message_new    = $message;
               $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   

                        //may alreay cancelled 
                         $response_New = array(
                            'status' => 'error', // You can set this to 'error' in case of an error
                            'message' => $message
                        );
                                      $objCancel->_writeLog('step data empty '.$message,'search_refund.txt');

            }


        } 
      }
   //   print_r($response_New);exit;
    //  echo "jjj";exit;
         $objCancel->_writeLog('step end of void  ========= '.$message,'search_refund.txt');
echo json_encode($response_New);
exit;


   }
?>