<?php
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();
   if (!isset($_POST['mfreNum'])){
       echo "Err1";exit;

   }
   else{

 $mfreNum   =  trim( $_POST['mfreNum']);
     $bookingId   =  trim($_POST['bookingId']);
      $userId   =   trim($_POST['userId']);
    // $void_eligible   =   trim($_POST['void_eligible']);
     //=======
     
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
   // $void_eligible = filter_var($void_eligible, FILTER_SANITIZE_NUMBER_INT);
     //=======
     //echo "LLL".$mfreNum;exit;

     $bookCanusers_req      =   $objCancel->BookCancelUsers($bookingId,$userId); 
         
     //============request body for entire booking cancel============================================
       if (isset($_POST['passengers']) && is_array($_POST['passengers'])) {
    $passengers = $_POST['passengers'];

     }
     // Initialize the main passengers array
     /*
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
    'ptrType' => 'RefundQuote',
    'mFRef' => $mfreNum,   
    'passengers' => $passengers
);
//print_r($requestData);exit;



//$mfreNum = "MF23709123";
//===================test api request==========

 // Construct the API request payload
    /*    $requestData = array(   
             'ptrType' =>  'VoidQuote',
              'mFRef' => $mfreNum,
            'AllowChildPassenger' => false,
             'passengers' => array(
                 array(
             'firstName' =>  'JAN',
              'lastName' => 'JAN',
            'title'      => 'Mr',
             'eTicket' => 'TKT365204',
            'passengerType'      => 'ADT'
             )
             ),
            // 'ConversationId' => 'string',
        );
*/
     // print_r($requestData); exit;

        $endpoint   =   'PostTicketingRequest';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
   //  echo $apiEndpoint."\n".BEARER;
  //print_r($result);  exit;
       
        // Send the API request
    //****************************************************************
                                      /*              $response    =     '{
                                                        "Success": true,
                                                          "Data": {
                                                            "PTRId": 10669,
                                                            "PTRType": "RefundQuote",
                                                            "MFRef": "MF23731023",
                                                            "SLAInMinutes": 0,
                                                            "PTRStatus": "Completed",
                                                            "RefundQuotes": [
                                                              {
                                                                "FirstName": "AVIKASH",
                                                                "LastName": "AVIKASH",
                                                                "Title": "MR",
                                                                "PassengerType": "ADT",
                                                                "ETicket": "2289699426651",
                                                                "TotalFare": 43.44,
                                                                "UnusedFare": 43.44,
                                                                "CancellationCharge": 42.69,
                                                                "NoShowCharge": 0,
                                                                "Tax": 0,
                                                                "AdminCharges": 0,
                                                                "GSTCharge": 0,
                                                                "TotalRefundCharges": 42.69,
                                                                "TotalRefundAmount": 0.75,
                                                                "Currency": "USD",
                                                                "YQ_Tax": 0,
                                                                "YR_Tax": 0,
                                                                "OtherTaxesK3": 0,
                                                                "ExtraServiceCharge": 0
                                                              }
                                                            ],
                                                            "Message": "Request for refund quote has been submitted successfully. Your Request# is 10669."
                                                          }
                                                                                }';     */
                        
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
         $logReQ =   print_r($requestData, true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','RefundQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'RefundQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'RefundQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'RefundQuote.txt');
                              $objCancel->_writeLog('Request Received\n'.$logReQ,'RefundQuote.txt');
        $objCancel->_writeLog('REsponse Received\n'.$logRes,'RefundQuote.txt');
 
                        //write log
//    print_r($responseData);exit;
        //=====================================
     // $traceId    =    $responseData['Data']['TraceId'];
      $precancelsts   =   'post';
     $message = ""; 
      $TotalRefundAmount =0;
      $markupFee_percentage_val   =0;
if (isset($responseData['Success']) && $responseData['Success']) {
            $cancel_status  =   1;
             $PTRId    =   $responseData['Data']['PTRId'];
        $PTRType    =   $responseData['Data']['PTRType'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'RefundQuote.txt');
             // $objCancel->_writeLog('TotalVoidingFee '.$TotalVoidingFee.,'voidQuote.txt');
             // $objCancel->_writeLog('TotalRefundAmount '.$TotalRefundAmount.,'voidQuote.txt');
           
            foreach($responseData['Data']['RefundQuotes'] as $k => $val){
                $ticket_num =   $val['ETicket'];
                $TotalFare =   $val['TotalFare'];                
                 $UnusedFare =   $val['UnusedFare'];
                 $CancellationCharge =   $val['CancellationCharge'];
                $NoShowCharge =   $val['NoShowCharge'];
                 $TotalRefundAmount +=   $val['TotalRefundAmount'];
                //====================================
                if($NoShowCharge !=0){
                //  $markupFee_percentage_NoshowFee  =  $objCancel->MArkup_percentage_value(5);
                  //$markupFee_percentage_NoshowFee =   $val['TotalRefundAmount'] * ($markupFee_percentage_NoshowFee/100);
                }
                
               
               //markupFee  = $TotalRefundAmount - $markupFee_percentage_val;
               $Currency =   $val['Currency'];
         //      $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId,$PTRType,$SLAInMinutes,$PTRStatus,$VoidingWindow, $ticket_num  ,$AdminCharges ,$GSTCharge,$TotalVoidingFee,$TotalRefundAmount,$Currency,$cancel_status,$message='');                                                   
                                  
                               //    $update_TravellerB_result      =    $objCancel->updateInDB_trav('travellers_details',$ticket_num);
                                                     

            }
         //rint_r($markupFee_percentage);
                 $markupFee_percentage = $objCancel->MArkup_percentage_value(4);
               $markupFee_percentage_val  = $markupFee_percentage[0]['commission_percentage'];
               $markupFee_percentage_val    =   $TotalRefundAmount * ($markupFee_percentage_val/100);
               $formattedRefund = number_format($markupFee_percentage_val, 2); // Format with two decimal places

             //eho $markupFee_percentage_val;
                //=====================================
             $message   =   "Successfully called Refundquote for  Your Booking";
                              $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message,
                    'refundamount' => $formattedRefund,
                    'total_refund_api' => $TotalRefundAmount
                );
      }
      else if(isset($responseData['Data']['Errors']) && is_array($responseData['Data']['Errors'])) {
     // print_r($responseData['Data']['Errors']);exit;
    foreach ($responseData['Data']['Errors'] as $error) {
        $errorCode = $error['Code'];
        $errorMessage = $error['Message'];
       $cancel_status = 0;
       $message_new = $errorMessage;
                         //    $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

   $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   
           //echo $errorCode;exit;
        $message    = "Problem in Cancellation";
          $cancel_status = 0;
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'RefundQuote.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
              $cancel_status = 0;
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
            $message_new    = $message;
          $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   
         
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'RefundQuote.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){
                           
                              $cancel_status = 0;
               
                        $message    =   $responseData['Message'];
                        
                       
                                                       $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRId='',$PTRType='refundQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message);                                                   

                        //Booking is not eligible for voiding. -may be not under void window param
                         $response_New = array(
                            'status' => 'error', // You can set this to 'error' in case of an error
                            'message' => $message
                        );
                                      $objCancel->_writeLog('step data empty '.$message,'RefundQuote.txt');

            }


        }
         $objCancel->_writeLog('step end of refund quote ========= '.$message,'RefundQuote.txt');
echo json_encode($response_New);
exit;


   }
?>