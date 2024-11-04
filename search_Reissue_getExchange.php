<?php
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();
   if (!isset($_POST['MFnum'])){
       echo "Err1";exit;

   }
   else{

 $mfreNum   =  trim( $_POST['MFnum']);
 $ptr_id    =    trim( $_POST['ptr_id']);

    $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);

    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
   // $mfreNum    =   "MF23731023";
$bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
     //=======
    // echo "LLL".$mfreNum;exit;


// For example, creating the main request body array
$requestData = array(
    'ptrType' => 'GetExchangeQuote',
    'MFRef' => $mfreNum,
    'PTRId' => $ptr_id,
    'Page' => 1
);
// print_r($requestData);
 //exit;



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
                                   /*            $response    =     '{
                                                                                                    }';     */
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
        $logReQ =   print_r($requestData, true);

            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchreissue.txt');
                        $objCancel->_writeLog('userId is '.$userId,'searchreissue.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'searchreissue.txt');
                      $objCancel->_writeLog('Request Received\n'.$logReQ,'searchreissue.txt');

                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'searchreissue.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'searchreissue.txt');
 
                        //write log
    // print_r($responseData);exit;
        //=====================================
     // $traceId    =    $responseData['Data']['TraceId'];
      $precancelsts   =   'post';
     $message = ""; 
      $TotalRefundAmount =0;
      $CreditNoteNumber ='';
      $CreditNoteStatus ='';
      $Resolution   =   '';
      if(isset($responseData['Success'])){
        //  echo  $responseData['Data']['PTRDetail']['PTRId'];
if (isset($responseData['Data']['RequestedPreferences']) && (!empty($responseData['Data']['RequestedPreferences']))) {
   // print_r($responseData);
           
            $PTRStatus      =   $responseData['Data']['Status'];     
            
            $Resolution   =   $responseData['Data']['Resolution'];
            
            print_r($PTRStatus);
            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'searchreissue.txt');
             $objCancel->_writeLog('Step 1 Resolution '.$Resolution,'searchreissue.txt');

             
            foreach($responseData['Data']['RequestedPreferences'] as $k => $val){
                $option_Num =   $val['Option'];
               print_r($val['QuotedSegments']);exit;
              //  print_r($val);
             /*    $pax_booking_id_transaction =   $val['Id'];
                 $PaxId =   $val['PaxId'];
                 $TicketStatus =   $val['TicketStatus'];
                 $is_active_booking_status =   $val['IsActive'];                
                $ticket_num =   $val['TicketNumber']; 
            */
                if(($PTRStatus == "Completed") && ($Resolution == "QuoteUpdated")){
                    echo "PPPPPPPPPPP";exit;
                    //cancellation success
                     
                     // $update_cancelBooking_status      =    $objCancel->updateInDB_cancelbooking('cancel_booking',$ticket_num);
                     //  $update_TravellerB_result      =    $objCancel->updateInDB_trav('travellers_details',$ticket_num);
                    //  $cancel_status    =1;

                }
               echo "LLLLLLLLLLL";exit;
             //  $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$is_active_booking_status,$cancel_status);                                                   
                                  
                             
                                          
                                                     

            }
                 //  $count_ticketed_temp    =   $objCancel->count_ticketed__temp_book('travellers_details',$bookingId);
                   // if($count_ticketed_temp == 0){
                        //  update tempbooking and traveller details tables with cancelled sts 
                    // $update_tempB_result           =   $objCancel->updateInDB_temp_book('temp_booking',$mfreNum);
                 //  }
               //    echo "LLL";print_r($TotalRefundAmount)  ;exit;
//mail code
                                         
            //   var_dump($update_TravellerB_result);exit;
        /*    if($cancel_status == 1){
                             $message   =   "Your Canellation is :".$PTRStatus." Total Refundable Amount is : ". $Currency." ".$TotalRefundAmount."Please note Your CreditNoteNumber: ".$CreditNoteNumber;

            }
            else{
                $message   =   "Your Canellation is :".$PTRStatus." Total Refundable Amount is : ". $Currency." ".$TotalRefundAmount;
            }
         

                              $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message,
                    'ptr_id' => $PTRId,
                    'refundamount' => $TotalRefundAmount
                );
               // print_r($response_New);exit;
               */
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
                  //     $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   

         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'searchreissue.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
              $cancel_status = 0;
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
            $message_new    = $message;
          //     $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   
         
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'searchreissue.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){   // echo "uu";exit;
                           
                              $cancel_status = 0;
               
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
      }
   //   print_r($response_New);exit;
      
         $objCancel->_writeLog('step end of void  ========= '.$message,'searchreissue.txt');
echo json_encode($response_New);
exit;


   }
?>