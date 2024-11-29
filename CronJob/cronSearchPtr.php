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
include_once __DIR__ . '/../includes/class.Users.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';

$objBookCron     =   new SearchPtrCron();
$adminToemail  =   "no-reply@bulatrips.com";

  //=================log write for book API ======
$logReQ =   "Successfully started";
$objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchPtrCron.txt');
$objBookCron->_writeLog('Request Received\n'.$logReQ,'searchPtrCron.txt');
//============ END log write for book API ==========
$resultBooking = $objBookCron->getBookCronIDs();
foreach($resultBooking as $resultBookingdata){
    
        $bookingId =    $resultBookingdata['id'];
        $userId =    $resultBookingdata['user_agent_id'];
        $ptr_id =   $resultBookingdata['ptr_id'];
        $mfreNum = $resultBookingdata['mf_ref_num'];
        $totalRefundAmount = $resultBookingdata['total_refund_amount'];
       
        if(isset($mfreNum)){
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
            $result       =   $objBookCron->callApi($endpoint,$requestData);
            $httpCode = $result['httpCode'];
            $response = $result['responseData'];
    
            if ($response) {
                $responseData = json_decode($response, true);
        
            }
            $logRes =   print_r($responseData, true);
            $logReQ =   print_r($requestData, true);
    
            $objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','search.txt');
            $objBookCron->_writeLog('userId is '.$userId,'search.txt');
            $objBookCron->_writeLog('Booking ID is '.$bookingId,'search.txt');
            $objBookCron->_writeLog('Request Received\n'.$logReQ,'search.txt');
    
            $objBookCron->_writeLog('REsponse Received for MF:\n'.$mfreNum,'search.txt');
    
            $objBookCron->_writeLog('REsponse Received\n'.$logRes,'search.txt');
            $message = ""; 
            $TotalRefundAmount =0;
            $CreditNoteNumber ='';
            $CreditNoteStatus ='';
            $Resolution   =   '';

            if(isset($responseData['Success'])){
                //  echo  $responseData['Data']['PTRDetail']['PTRId'];
                if (isset($responseData['Data']['PTRDetail']) && (!empty($responseData['Data']['PTRDetail']))) {
                    $cancel_status  =   0;
                    $PTRId    =   $responseData['Data']['PTRDetail'][0]['PTRId'];
                $PTRType    =   $responseData['Data']['PTRDetail'][0]['PTRType'];
                $BookingStatus   =   $responseData['Data']['PTRDetail'][0]['BookingStatus'];
                $PTRStatus      =   $responseData['Data']['PTRDetail'][0]['PTRStatus'];     
                
                $Resolution   =   $responseData['Data']['PTRDetail'][0]['Resolution'];
                // $ProcessingMethod   =   $responseData['Data']['PTRDetail'][0]['ProcessingMethod'];
                // $CreditNoteNumber   =   $responseData['Data']['PTRDetail'][0]['CreditNoteNumber'];
                // $CreditNoteStatus   =   $responseData['Data']['PTRDetail'][0]['CreditNoteStatus'];
                //     $TotalRefundAmount  +=   $responseData['Data']['PTRDetail'][0]['TotalRefundAmount'];
                //     $Currency   =   $responseData['Data']['PTRDetail'][0]['Currency'];
                
                    $objBookCron->_writeLog('Step 1Success '.$PTRStatus,'search.txt');
                    $objBookCron->_writeLog('Step 1 Resolution '.$Resolution,'search.txt');
    
                    $success_can_sts    =0;
                    foreach($responseData['Data']['PTRDetail'][0]['pTRPaxDetails'] as $k => $val){
                        $pax_booking_id_transaction =   $val['Id'];
                        $PaxId =   $val['PaxId'];
                        $TicketStatus =   $val['TicketStatus'];
                        $is_active_booking_status =   $val['IsActive'];                
                        $ticket_num =   $val['TicketNumber']; 
                       
                        if(($PTRStatus == "Completed") && ($Resolution == $resultBookingdata['ptr_type']."ed")){
                            //cancellation success
                            
                            $update_cancelBooking_status      =    $objBookCron->updateInDB_cancelbooking('cancel_booking',$ticket_num);
                            $update_TravellerB_result      =    $objBookCron->updateInDB_trav('travellers_details',$ticket_num);
                            $cancel_status    =1;

                            $objUser = new Users();
                            $userDetails = $objUser->getUserDetails($userId);
                            $subject = "Bulatrips Agent Booking and Credit Balance Debit Info";                  
            
                                    $email=   $userDetails['email'];
                                    $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                                    $content    =   '<p>Hello,</p>
                                                        <p>This agent , '. $name .', with email '.$userDetails['email'].' ptr request against booking id:'.$bookingId.'.The booking is response has been return as completed</p>';
                                                    $messageData =   $objBookCron->getEmailContent($content);
                                // print_r($messageData);exit;
                                    $headers="";
                                    $email = $adminToemail; //Need ADMIN email here
                    
                                $contacts= sendMail($email,$subject, $messageData,$headers);
        
                        }
                    
                    // $bookCanIns      =   $objBookCron->insCncelSts_Search($bookingId,$userId,$BookingStatus,$Resolution, $mfreNum,$ProcessingMethod,$PTRId,$PTRType,$CreditNoteNumber,$PTRStatus,$CreditNoteStatus, $ticket_num ,$pax_booking_id_transaction ,$PaxId,$TicketStatus,$TotalRefundAmount,$is_active_booking_status,$cancel_status);                                                   
                    }
                    $count_ticketed_temp    =   $objBookCron->count_ticketed__temp_book('travellers_details',$bookingId);
                    if($count_ticketed_temp == 0){
                            //  update tempbooking and traveller details tables with cancelled sts 
                        $update_tempB_result           =   $objBookCron->updateInDB_temp_book('temp_booking',$mfreNum);
                    }
                }
            }
            
            $objBookCron->_writeLog('step end of void  ========= '.$message,'search.txt');
    
            
            
        }
    
}
    
?>