<?php
/*******************
Project Name	::> Bulatrips
   Module 		::> Booking Cron on pending statuses from void and refund api
   Programmer	::> Asad
   Date			::> 22.11.2024
   
   DESCRIPTION::::>>>>
   Booking Cron on in process statuses of void and refund ptr requests


********************/

include_once __DIR__ . '/../includes/class.SearchPtrCron.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';

 $objBookCron     =   new SearchPtrCron();
  $adminToemail  =   "no-reply@bulatrips.com";

  //=================log write for book API ======
                   
                   //$logRes =   print_r($responseData, true);
                 // $logReQ =   print_r($requestData, true);
                 $logReQ =   "Successfully started";
   
                    $objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchPtrCron.txt');
                    $objBookCron->_writeLog('Request Received\n'.$logReQ,'searchPtrCron.txt');
                            
             // echo "Log updated successfully";exit; 
    //============ END log write for book API ==========

    // Subscribe the user and get the result message
    $resultBooking = $objBookCron->getBookCronIDs();

     // echo "<pre/>";print_r($resultBooking);exit;
    foreach($resultBooking as $resultBookingdata){

        // print_r($resultBookingdata);
        $userId =    $resultBookingdata['user_agent_id'];
        $ptr_id =   $resultBookingdata['ptr_id'];
        $mfreNum = $resultBookingdata['mf_ref_num'];
        $totalRefundAmount = $resultBookingdata['total_refund_amount'];
        if(isset($mfreNum)){
           // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1.1/TripDetails/{MFRef}';
           // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
           if(isset($resultBookingdata['ptr_type']) == "Refund"){
                    $requestData = array(
                        'ptrType' => 'Refund',
                        'MFRef' => $mfreNum,
                        'PTRId' => $ptr_id,
                        'Page' => 1
                   );
            }
            else{
                $requestData = array(
                    'ptrType' => 'Void',
                    'MFRef' => $mfreNum,
                    'PTRId' => $ptr_id,
                    'Page' => 1
               );
            }
            $endpoint   =   'Search/PostTicketingRequest';
            $result       =   $objCancel->callApi($endpoint,$requestData);
            $httpCode = $result['httpCode'];
            $response = $result['responseData'];

            if ($response) {
                $responseData = json_decode($response, true);
        
            }
            $logRes =   print_r($responseData, true);
            $logReQ =   print_r($requestData, true);
    
                $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','search.txt');
                            $objCancel->_writeLog('userId is '.$userId,'search.txt');
                          $objCancel->_writeLog('Booking ID is '.$bookingId,'search.txt');
                          $objCancel->_writeLog('Request Received\n'.$logReQ,'search.txt');
    
                        $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'search.txt');
    
            $objCancel->_writeLog('REsponse Received\n'.$logRes,'search.txt');
     
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
    if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
      //  print_r($responseData);exit;
                $cancel_status  =   0;
                 $PTRId    =   $responseData['Data']['PTRDetail'][0]['PTRId'];
                $PTRType    =   $responseData['Data']['PTRDetail'][0]['PTRType'];
                $BookingStatus   =   $responseData['Data']['PTRDetail'][0]['BookingStatus'];
                $PTRStatus      =   $responseData['Data']['PTRDetail'][0]['PTRStatus'];     
                
                $Resolution   =   $responseData['Data']['PTRDetail'][0]['Resolution'];
                $ProcessingMethod   =   $responseData['Data']['PTRDetail'][0]['ProcessingMethod'];
                $CreditNoteNumber   =   $responseData['Data']['PTRDetail'][0]['CreditNoteNumber'];
                $CreditNoteStatus   =   $responseData['Data']['PTRDetail'][0]['CreditNoteStatus'];
                 $TotalRefundAmount  +=   $responseData['Data']['PTRDetail'][0]['TotalRefundAmount'];
                 $Currency   =   $responseData['Data']['PTRDetail'][0]['Currency'];
    
                $objCancel->_writeLog('Step 1Success '.$PTRStatus,'search.txt');
                 $objCancel->_writeLog('Step 1 Resolution '.$Resolution,'search.txt');
    
                  $success_can_sts    =0;
                foreach($responseData['Data']['PTRDetail'][0]['pTRPaxDetails'] as $k => $val){
                     $pax_booking_id_transaction =   $val['Id'];
                     $PaxId =   $val['PaxId'];
                     $TicketStatus =   $val['TicketStatus'];
                     $is_active_booking_status =   $val['IsActive'];                
                    $ticket_num =   $val['TicketNumber']; 
                
                    if(($PTRStatus == "Completed") && ($Resolution == "Voided")){
                        //cancellation success
                         
                          $update_cancelBooking_status      =    $objCancel->updateInDB_cancelbooking('cancel_booking',$ticket_num);
                           $update_TravellerB_result      =    $objCancel->updateInDB_trav('travellers_details',$ticket_num);
                          $cancel_status    =1;
    
                    }
                   
                   $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$is_active_booking_status,$cancel_status);                                                   
                                      
                                 
                                              
                                                         
    
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
                    $message   =   "Your Canellation is :".$PTRStatus." Total Refundable Amount is : ". $Currency." ".$TotalRefundAmount;
                }
             
    
                                  $response_New = array(
                        'status' => 'success', // You can set this to 'error' in case of an error
                        'message' => $message,
                        'ptr_id' => $PTRId,
                        'refundamount' => $TotalRefundAmount
                    );
                   // print_r($response_New);exit;
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
    
             $objCancel->_writeLog('Step data of errors  '.$errorMessage,'search.txt');
     
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
             $objCancel->_writeLog('step httpcode not 200 '.$message,'search.txt');
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
                                          $objCancel->_writeLog('step data empty '.$message,'search.txt');
    
                }
    
    
            } 
          }
          
             $objCancel->_writeLog('step end of void  ========= '.$message,'search.txt');
            echo json_encode($response_New);
            exit;
    
           
          
        }
    }
    
?>