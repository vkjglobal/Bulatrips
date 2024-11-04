<?php
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();
  $formData = [];
  parse_str($_POST['formData'], $formData);
 
  if(isset($_POST['selectedPassengers'])){
      $passengers   =   $_POST['selectedPassengers'];
  }
    if(isset($_POST['type'])){
      $type   =   $_POST['type'];
  }
  //echo $type."LLLLLLLLLLLLLL";exit;
 // print_r($formData);exit;
   if (!isset($formData['mfreNum'])){
       echo "Err1";exit;

   }
   else{
     /*  var originDestinations = [
                            {
                              "originLocationCode": dep_loc,
                              "destinationLocationCode": "COK",
                              "cabinPreference": selectedCabinPreference,
                              "departureDateTime": dep_date,
                              "flightNumber": "1051",
                              "airlineCode": airline_code
                            },
                            {
                              "originLocationCode": "BLR",
                              "destinationLocationCode": dep_loc,
                              "cabinPreference": selectedCabinPreference,
                              "departureDateTime": dep_date_to,
                             
                              "airlineCode": ""
                            }
                          ];
                          */

   
 $mfreNum   =  trim( $formData['mfreNum']);
     $bookingId   =  trim($formData['bookingId']);
      $userId   =   trim($formData['userId']);
     $allow_child   =   trim($formData['allow_child']);
     $air_trip_Type =    trim($formData['air_trip_Type']);

     $dep_loc   =  trim($formData['airport']);
      $arrv_loc   =   trim($formData['arrivalairport']);
     $dep_date   =   trim($formData['from-reissue']);
     $flightNumber =    trim($formData['selected-flights']);
    
      $airlineCode =    trim($formData['airline-reissue']);
     $cabinPreference =    trim($formData['cabin-preference']);

     $originDestinations = array(
         array(
    'originLocationCode' => $dep_loc,
    'destinationLocationCode' => $arrv_loc,
    'cabinPreference' => $cabinPreference,
    'departureDateTime' => $dep_date,
    'flightNumber' => $flightNumber,
    'airlineCode' => $airlineCode
    )
     );

     if($air_trip_Type  == 1){
         //return so include return list of arrv and dep locn 
      $dep_loc_return   =  trim($formData['airport_return_dep']);
      $arrv_loc_return   =   trim($formData['arrivalairport_return']);
     $dep_date_return   =   trim($formData['from-reissue_return']);
     

     $flightNumber_return =   trim($formData['flight_no_return']);
     $cabinPreference_return    =trim($formData['cabin_preference_return']);
     $airlineCode_return    =trim($formData['airline_code_return']);


      $originDestinations = array(
          array(
    'originLocationCode' => $dep_loc,
    'destinationLocationCode' => $arrv_loc,
    'cabinPreference' => $cabinPreference,
    'departureDateTime' => $dep_date,
    'flightNumber' => $flightNumber,
    'airlineCode' => $airlineCode
    ),
     array(
    'originLocationCode' => $dep_loc_return,
    'destinationLocationCode' => $arrv_loc_return,
    'cabinPreference' => $cabinPreference_return,
    'departureDateTime' => $dep_date_return,
    'flightNumber' => $flightNumber_return,
    'airlineCode' => $airlineCode_return
    )
      );

     }

     //=======

  //    print_r($originDestinations);exit;
    // echo "hi";exit;
// Escape and sanitize the data before storing them in hidden input fields
    $mfreNum = htmlspecialchars($mfreNum, ENT_QUOTES, 'UTF-8');
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
  

//print_r($originDestinations);exit;
$requestData = array(
    'ptrType' => 'ReissueQuote',
    'mFRef' => $mfreNum,
    'AllowChildPassenger' => $allow_child,
    'reissueQuoteRequestType' => $type,
    'passengers' => $passengers,
    'originDestinations' => $originDestinations
);
//print_r($requestData);exit;

//var_dump($allow_child);exit;

//$mfreNum = "MF23709123";
//===================test api request==========


        $endpoint   =   'PostTicketingRequest';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
   //  echo $apiEndpoint."\n".BEARER;
   //print_r($result);  exit;
       
        // Send the API request
    //****************************************************************
                                     /*                $response    =     '{
                                                               
                        }';      
             //*****************************************           */
             $response  =   '{
                                          "Data": {
                                            "PTRId": 10889,
                                            "PTRType": "ReIssueQuote",
                                            "Status": "Completed",
                                            "MFRef": "MF23911723",
                                            "CreatedOn": "2023-08-18T17:59:49.697",
                                            "RequestCompletionTime": "2023-08-18T17:59:49.697",
                                            "CreatedByName": "XPay Global",
                                            "Resolution": "QuoteUpdated",
                                            "Passengers": [
                                              {
                                                "ETicket": "TKT369484",
                                                "PassengerType": "CHD",
                                                "Tittle": "Mr",
                                                "FirstName": "heera",
                                                "LastName": "heera"
                                              }
                                            ],
                                            "RequestedPreferences": [
                                              {
                                                "Option": 1,
                                                "CreatedOn": "2023-08-18T17:59:49.697",
                                                "QuotedSegments": [
                                                  {
                                                    "Origin": "MAA",
                                                    "Destination": "DEL",
                                                    "CabinClass": "Y",
                                                    "DepartureDatetime": "2023-08-26T21:05:00",
                                                    "ArrivalDateTime": "2023-08-27T00:05:00",
                                                    "AirlineCode": "UK",
                                                    "FlightNumber": 838,
                                                    "Duration": "180",
                                                    "Stops": 0,
                                                    "BookingClass": "Y",
                                                    "isReturn": true
                                                  },
                                                  {
                                                    "Origin": "DEL",
                                                    "Destination": "MAA",
                                                    "CabinClass": "Y",
                                                    "DepartureDatetime": "2023-09-01T07:20:00",
                                                    "ArrivalDateTime": "2023-09-01T10:10:00",
                                                    "AirlineCode": "UK",
                                                    "FlightNumber": 833,
                                                    "Duration": "170",
                                                    "Stops": 0,
                                                    "BookingClass": "Y",
                                                    "isReturn": false
                                                  }
                                                ],
                                                "QuotedFares": [
                                                  {
                                                    "PassengerType": "CHD",
                                                    "BaseFareDifference": 84.1,
                                                    "TaxDifference": 0,
                                                    "AdminFee": 0,
                                                    "GST": 0,
                                                    "NoShowPenalty": 0,
                                                    "Currency": "USD",
                                                    "Penalty": 0,
                                                    "OtherTaxesK3": 0,
                                                    "PassengerCount": 1,
                                                    "TotalFareDifference": 84.1
                                                  }
                                                ]
                                              }
                                            ]
                                          },
                                          "Success": true
                                        }';
             //****************************************
         
        if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
          $logReQ =   print_r(json_encode($requestData), true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'reissueQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'reissueQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'reissueQuote.txt');
        $objCancel->_writeLog('REquest Received\n'.$logReQ,'reissueQuote.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'reissueQuote.txt');
 
                        //write log
        print_r($responseData);
        exit;
        //=====================================
    
     $message = ""; 
    
if (isset($responseData['Success']) && $responseData['Success']) {
           // $reissue_status  =   1;

        $PTRType    =   $responseData['Data']['PTRType'];
        $PTRId    =   $responseData['Data']['PTRId'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $hours = $objCancel->calculateHoursFromSLAMinutes($SLAInMinutes);

            $objCancel->_writeLog('Step 1Success reissueQuote '.$PTRStatus,'reissueQuote.txt');
            // $PTRStatus = "InProcess";
              if( $PTRStatus == "InProcess"){
			             $message   =   "Your ReIssue Request  is :".$PTRStatus." This will update within ". $hours." Hours";
                         //*****************************************
                         //calling search api getexchange code


            //print_r($originDestinations);exit;
          
                    // Send the API request
    //*****************************************


                         //*****************************************
			}
            else{
                //ptr statu completed expected 
               // $message = "Goto NExt level Get Exchange Quote";
                 $requestData = array(
                'ptrType' => 'GetExchangeQuote',
                'mFRef' => $mfreNum,
                'PTRId' => $PTRId,
                'Page' => 1                
            );
            //print_r($requestData);exit;

            //var_dump($allow_child);exit;

            //$mfreNum = "MF23709123";
            //===================test api request==========


                    $endpoint   =   'Search/PostTicketingRequest';
                    $result       =   $objCancel->callApi($endpoint,$requestData);
                    $httpCode = $result['httpCode'];
                    $response = $result['responseData'];
               //  echo $apiEndpoint."\n".BEARER;
            //   print_r($result);  exit;
              if ($response) {
            $responseData = json_decode($response, true);
    
        }
        $logRes =   print_r($responseData, true);
          $logReQ =   print_r(json_encode($requestData), true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'reissueQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'reissueQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'reissueQuote.txt');
        $objCancel->_writeLog('REquest Received\n'.$logReQ,'reissueQuote.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'reissueQuote.txt');
       exit;



            }
           

                               $response_New = array(
                    'status' => 'success', // You can set this to 'error' in case of an error
                    'message' => $message,
                    'ptr_id' => $PTRId,
			 'ptr_status' => $PTRStatus
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

                             //  $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode, $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   
           //echo $errorCode;exit;
      //  $message    = "Problem in Cancellation";
          $cancel_status = 0;
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message_new
        );
         $objCancel->_writeLog('Step data of errors  '.$errorMessage,'reissueQuote.txt');
 
            }
           
        } //== end of if error ===
        else if($httpCode !=200)
        {
             
            // Handle other status codes like 404, 500, etc.
           // $message =  "API request failed with status code: " . $httpCode;
            $message    =   $responseData['Message'];
                             //  $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   
         
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
        );
         $objCancel->_writeLog('step httpcode not 200 '.$message,'reissueQuote.txt');
        }
        else if(empty($responseData['Data'])){
            if(!empty($responseData['Message'])){
                           
                              $cancel_status = 0;
               
                        $message    =   $responseData['Message'];
                        
                       // $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

                        //Booking is not eligible for voiding. -may be not under void window param
                         $response_New = array(
                            'status' => 'error', // You can set this to 'error' in case of an error
                            'message' => $message
                        );
                                      $objCancel->_writeLog('step data empty '.$message,'reissueQuote.txt');

            }


        }
         $objCancel->_writeLog('step end of reissue quote ========= '.$message,'reissueQuote.txt');
echo json_encode($response_New);
exit;


   }
?>