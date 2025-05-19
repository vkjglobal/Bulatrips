<?php
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');

  $objCancel     =   new Cancel();
 $mfreNum   =   $_POST['mfreNum']; 
     $bookingId   =   $_POST['bookingId'];
      $userId   =   $_POST['userId'];
     $precancelsts   =   $_POST['precancelsts'];
     //=======
     
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
    $precancelsts = filter_var($precancelsts, FILTER_SANITIZE_NUMBER_INT);
     //=======

  $apiEndpoint = APIENDPOINT.'v1/Booking/Cancel';
//echo "LLL".$mfreNum;exit;

 // Construct the API request payload
        $requestData = array(          
            'UniqueID' => $mfreNum,
            'Target' => TARGET,
            // 'ConversationId' => 'string',
        );

        
        $endpoint   =   'v1/Booking/Cancel';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
   //  echo $apiEndpoint."\n".BEARER;
        
  //print_r($result);
  //exit;
       
        // Send the API request
    //****************************************************************
        /*             $response    =     '{
                                              "Data": {
                      "ConversationId": "Testsg",
                      "Errors": [
                        {
                          "Code": "string",
                          "Message": "string"
                        }
                      ],
                      "Success": true,
                      "Target": "Default",
                      "TraceId": "string",
                      "UniqueID": "string"
                            }
                                            }'; */
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
//print_r($responseData);exit;
        //=====================================
        $traceId    =    $responseData['Data']['TraceId'];
       
        if ($responseData['Data']['Success']) {
             $message   =   "Successfully Cancelled Your Booking";
            $cancel_status  =   1;
           //  $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$cancel_status);
             $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts='pre',$errorCode ='', $mfreNum,$traceId,$httpCode,$PTRId='',$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$per_psnger_refund_amnt='',$Currency='',$cancel_status=1,$message);                                                   
              $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message                   
                );
            
      }
      else if(isset($responseData['Data']['Errors']) && is_array($responseData['Data']['Errors'])) {
     // print_r($responseData['Data']['Errors']);exit;
    foreach ($responseData['Data']['Errors'] as $error) {
        $errorCode = $error['Code'];
        $errorMessage = $error['Message'];
       $cancel_status = 0;
            $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts='pre',$errorCode, $mfreNum,$traceId,$httpCode,$PTRId='',$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$per_psnger_refund_amnt='',$Currency='',$cancel_status,$errorMessage);                                                   

           //echo $errorCode;exit;
        
            $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => 'Problem in Cancellation with Error Code: '.$errorCode.' ' . $errorMessage
        );
        // Handle different error codes here
      /*  switch ($errorCode) {
            case 'ERCBK007':
                echo 'Booking in Tkt-in-process or Ticketed Status - Cancellation Denied! ';
         //     echo 'Error Code: ERRTT - ' . $errorMessage;
                break;
            case 'ANOTHER_ERROR':
                // Do something specific for another error code
                break;
            // Add more cases for other error codes if needed
            default:
                // Handle any other unspecified error code
                 echo 'Error Code: '.$errorCode.' ' . $errorMessage;
                break;
                 $message   =   "Problem in Cancellation  :".$errorMessage;
        }  */
        
            }
        } //== end of if error ===
        else if($httpCode !=200)
        {
            // Handle other status codes like 404, 500, etc.
            $message =  "API request failed with status code: " . $httpCode;
             $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts='pre',$errorCode='', $mfreNum,$traceId,$httpCode,$PTRId='',$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$per_psnger_refund_amnt='',$Currency='',$cancel_status,$message);                                                   

             $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){
                        $message    =   $responseData['Message'];
            }
                        $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts='pre',$errorCode='', $mfreNum,$traceId,$httpCode,$PTRId='',$PTRType='',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$per_psnger_refund_amnt='',$Currency='',$cancel_status,$message);                                                   
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
        }
echo json_encode($response_New);
               exit;
?>