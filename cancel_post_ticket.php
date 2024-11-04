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
     $void_eligible   =   trim($_POST['void_eligible']);

     //=======
     
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
    $void_eligible = filter_var($void_eligible, FILTER_SANITIZE_NUMBER_INT);
     //=======
     //echo "LLL".$mfreNum;exit;

     $bookCanusers_req      =   $objCancel->BookCancelUsers($bookingId,$userId); 
     $childpsnger        = $bookCanusers_req[0]['child_count'];
    if($childpsnger === 0){
        $allow_child    =   false;
    }
    elseif($childpsnger > 0){
        $allow_child    =   true;
    }

    
     //============request body for entire booking cancel============================================
     if (isset($_POST['passengers']) && is_array($_POST['passengers'])) {
    $passengers = $_POST['passengers'];
     }

    // Now you can use $passengers array as needed
    // For example, you can loop through the passengers and access individual elements
    /*
    foreach ($passengers as $passenger) {
        $firstname = $passenger['firstname'];
        $lastname = $passenger['lastname'];
        $title = $passenger['title'];
        $eticket = $passenger['eticket'];
        $passengertype = $passenger['passengertype'];

        $passenger = array(
        "firstName" => $firstname,
        "lastName" => $lastname,
        "title" => $title,
        "eTicket" => $eticket,
        "passengerType" => $passengertype
    );
    }

}
    
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
    'ptrType' => 'VoidQuote',
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => $allow_child,
    'passengers' => $passengers
);
//print_r($requestData);exit;



//$mfreNum = "MF23709123";
//===================test api request==========

 // Construct the API request payload
   /*     $requestData = array(   
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
  // print_r($result);  exit;
       
        // Send the API request
    //****************************************************************
                                     /*                $response    =     '{
                                                                "Success": true,
                          "Data": {
                            "PTRId": 0,
                            "PTRType": "VoidQuote",
                            "MFRef": "MF23720823",
                            "SLAInMinutes": 0,
                            "PTRStatus": "Completed",
                            "VoidingWindow": "2023-07-28T16:29:59.997",
                            "VoidQuotes": [
                            {
                                "FirstName": "William",
                                "LastName": "William",
                                "Title": "Mr",
                                "PassengerType": "ADT",
                                "ETicket": "TKT365420",
                                "AdminCharges": "0.00",
                                "GSTCharge": "0.00",
                                "TotalVoidingFee": "0.00",
                                "TotalRefundAmount": "65.18",
                                "Currency": "USD"
                                },
                              
                              {
                                "FirstName": "jack",
                                "LastName": "jack",
                                "Title": "Mr",
                                "PassengerType": "ADT",
                                "ETicket": "TKT365419",
                                "AdminCharges": "0.00",
                                "GSTCharge": "0.00",
                                "TotalVoidingFee": "0.00",
                                "TotalRefundAmount": "65.18",
                                "Currency": "USD"
                              }
                             
                            ]
                          }
                        }';      
                        */
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
          $logReQ =   print_r($requestData, true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','voidQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'voidQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'voidQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'voidQuote.txt');
        $objCancel->_writeLog('REquest Received\n'.$logReQ,'voidQuote.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'voidQuote.txt');
 
                        //write log
    //    print_r($responseData);exit;
        //=====================================
     // $traceId    =    $responseData['Data']['TraceId'];
      $precancelsts   =   'post';
     $message = ""; 
      $TotalRefundAmount =0;
if (isset($responseData['Success']) && $responseData['Success']) {
            $cancel_status  =   1;
        $PTRType    =   $responseData['Data']['PTRType'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $VoidingWindow  =   $responseData['Data']['VoidingWindow'];
            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'voidQuote.txt');
             // $objCancel->_writeLog('TotalVoidingFee '.$TotalVoidingFee.,'voidQuote.txt');
             // $objCancel->_writeLog('TotalRefundAmount '.$TotalRefundAmount.,'voidQuote.txt');
           
            foreach($responseData['Data']['VoidQuotes'] as $k => $val){
                $ticket_num =   $val['ETicket'];
                $AdminCharges =   $val['AdminCharges'];
                $GSTCharge =   $val['GSTCharge'];
                $TotalVoidingFee =   $val['TotalVoidingFee'];
                $TotalRefundAmount +=   $val['TotalRefundAmount'];
               $Currency =   $val['Currency'];
           //    $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType,$SLAInMinutes,$PTRStatus,$VoidingWindow, $ticket_num  ,$AdminCharges ,$GSTCharge,$TotalVoidingFee,$TotalRefundAmount,$Currency,$cancel_status,$message='');                                                   
                                  
                               //    $update_TravellerB_result      =    $objCancel->updateInDB_trav('travellers_details',$ticket_num);
                                                     

            }
                 //   $count_ticketed_temp    =   $objCancel->count_ticketed__temp_book('travellers_details',$bookingId);
                   // if($count_ticketed_temp == 0){
                        //   update tempbooking and traveller details tables with cancelled sts 
                    //   $update_tempB_result           =   $objCancel->updateInDB_temp_book('temp_booking',$mfreNum);
                //    }
                //    echo "LLL";print_r($TotalRefundAmount)  ;
//mail code
                                         
            //   var_dump($update_TravellerB_result);exit;
             $message   =   "Successfully called voidquote for  Your Booking";
                              $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message,
                    'refundamount' => $TotalRefundAmount
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

                               $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode, $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   
           //echo $errorCode;exit;
        $message    = "Problem in Cancellation";
          $cancel_status = 0;
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'voidQuote.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
              $cancel_status = 0;
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
            $message_new    = $message;
                               $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   
         
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'voidQuote.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){
                           
                              $cancel_status = 0;
               
                        $message    =   $responseData['Message'];
                         $message_new    = $message;
                        $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

                        //Booking is not eligible for voiding. -may be not under void window param
                         $response_New = array(
                            'status' => 'error', // You can set this to 'error' in case of an error
                            'message' => $message
                        );
                                      $objCancel->_writeLog('step data empty '.$message,'voidQuote.txt');

            }


        }
         $objCancel->_writeLog('step end of void quote ========= '.$message,'voidQuote.txt');
echo json_encode($response_New);
exit;


   }
?>