<?php
  include_once('includes/common_const.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();

// Check if this is an AJAX request for reissue acceptance
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    if (isset($_POST['action']) && $_POST['action'] === 'processReissueAcceptance') {
        processReissueAcceptance();
        exit;
    }
}

function processReissueAcceptance() {
    global $objCancel;
    
    try {
        // Get JSON input for AJAX requests
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        // Validate required fields
        if (!isset($input['mfreNum']) || !isset($input['PTRId']) || !isset($input['PreferenceOption'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields for reissue acceptance'
            ]);
            return;
        }
        
        $mfreNum = trim($input['mfreNum']);
        $bookingId = trim($input['bookingId']);
        $userId = trim($input['userId']);
        $PreferenceOption = trim($input['PreferenceOption']);
        $PTRId = trim($input['PTRId']);
        
        // Log the reissue acceptance request
        $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
        $objCancel->_writeLog('AJAX Reissue Acceptance Request for MF: '.$mfreNum,'reissueQuote.txt');
        $objCancel->_writeLog('PTR ID: '.$PTRId,'reissueQuote.txt');
        $objCancel->_writeLog('Preference Option: '.$PreferenceOption,'reissueQuote.txt');
        
        // Prepare API request for reissue acceptance
        $requestData = array(
            'ptrType' => 'ReIssueQuote',
            'mFRef' => $mfreNum,
            'PTRId' => $PTRId,
            'PreferenceOption' => $PreferenceOption,
            'AcceptQuote' => "yes",
            'AdditionalNote' => "Please Reissue as for quoted fare"
        );
        
        $objCancel->_writeLog('Reissue Acceptance Request: '.json_encode($requestData),'reissueQuote.txt');
        
        // Call the API
        $endpoint = 'PostTicketingRequest';
        $result = $objCancel->callApi($endpoint, $requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
        
        if ($response) {
            $responseData = json_decode($response, true);
            
            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $objCancel->_writeLog('JSON decode error: ' . json_last_error_msg(), 'reissueQuote.txt');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid response format from API'
                ]);
                return;
            }
        } else {
            $objCancel->_writeLog('Empty response from API. HTTP Code: ' . $httpCode, 'reissueQuote.txt');
            echo json_encode([
                'success' => false,
                'message' => 'No response received from airline system'
            ]);
            return;
        }
        
        $objCancel->_writeLog('Reissue Acceptance Response: '.json_encode($responseData),'reissueQuote.txt');
        
        // Process the response
        if (isset($responseData['Success']) && $responseData['Success']) {
            $message = "Your reissue request has been accepted successfully. You will receive confirmation shortly.";
            
            if (isset($responseData['Data']['PTRStatus'])) {
                $PTRStatus = $responseData['Data']['PTRStatus'];
                $objCancel->_writeLog('Reissue Acceptance PTR Status: '.$PTRStatus,'reissueQuote.txt');
                
                if ($PTRStatus === "InProcess") {
                    $message = "Your reissue request is being processed. You will receive confirmation within the estimated time.";
                } elseif ($PTRStatus === "Completed") {
                    $message = "Your flight has been successfully reissued. Please check your email for the new ticket details.";
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'data' => $responseData['Data'] ?? null
            ]);
        } else {
            $errorMessage = $responseData['Message'] ?? 'Failed to process reissue acceptance';
            $objCancel->_writeLog('Reissue Acceptance Error: '.$errorMessage,'reissueQuote.txt');
            
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
        
    } catch (Exception $e) {
        $objCancel->_writeLog('Reissue Acceptance Exception: '.$e->getMessage(),'reissueQuote.txt');
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while processing your reissue request'
        ]);
    }
}

  $formData = [];
  parse_str($_POST['formData'], $formData);
 
  if(isset($_POST['selectedPassengers'])){
      $passengers   =   $_POST['selectedPassengers'];
  }


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
    'reissueQuoteRequestType' => "OND",
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
  // print_r($result);  exit;
       
        // Send the API request
    //****************************************************************
                                     /*                $response    =     '{
                                                               
                        }';      
                        */
         
        if ($response) {
            $responseData = json_decode($response, true);
            
            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                $objCancel->_writeLog('JSON decode error: ' . json_last_error_msg(), 'reissueQuote.txt');
                $responseData = array(
                    'Success' => false,
                    'Message' => 'Invalid response format from API'
                );
            }
        } else {
            $objCancel->_writeLog('Empty response from API. HTTP Code: ' . $httpCode, 'reissueQuote.txt');
            $responseData = array(
                'Success' => false,
                'Message' => 'No response received from airline system'
            );
        }
        $logRes =   print_r($responseData, true);
          $logReQ =   print_r(json_encode($requestData), true);
            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
                    $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'reissueQuote.txt');
                       $objCancel->_writeLog('userId is '.$userId,'reissueQuote.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'reissueQuote.txt');
        
        // Debug: Log form data changes
        $objCancel->_writeLog('FORM DATA DEBUG:','reissueQuote.txt');
        $objCancel->_writeLog('Original Route: ' . $dep_loc . ' → ' . $arrv_loc,'reissueQuote.txt');
        $objCancel->_writeLog('Original Date: ' . $dep_date,'reissueQuote.txt');
        $objCancel->_writeLog('Flight Number: ' . $flightNumber,'reissueQuote.txt');
        $objCancel->_writeLog('Airline Code: ' . $airlineCode,'reissueQuote.txt');
        $objCancel->_writeLog('Trip Type: ' . $air_trip_Type,'reissueQuote.txt');
        
        $objCancel->_writeLog('REquest Received\n'.$logReQ,'reissueQuote.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'reissueQuote.txt');
 
                        //write log
        //print_r($responseData);
        //exit;
        //=====================================
    
     $message = ""; 
    //*************************************************

   // Use actual API response instead of hardcoded test data
   // Test data commented out for production use


    //***************************************************
if (isset($responseData['Success']) && $responseData['Success']) {
           // $reissue_status  =   1;
        $PTRType    =   $responseData['Data']['PTRType'];
        $PTRId    =   $responseData['Data']['PTRId'];
            $SLAInMinutes   =   $responseData['Data']['SLAInMinutes'];
            $PTRStatus      =   $responseData['Data']['PTRStatus'];
            $hours = $objCancel->calculateHoursFromSLAMinutes($SLAInMinutes);

            $objCancel->_writeLog('Step 1Success reissueQuote OND Type '.$PTRStatus,'reissueQuote.txt');
            // $PTRStatus = "InProcess";
                                                              if( $PTRStatus == "InProcess"){
			                                                             $message   =   "Your ReIssue Request  is :".$PTRStatus." This will update within ". $hours." Hours";
                                                                         //*****************************************
                                                                         //calling search api getexchange code


                                                            //print_r($originDestinations);exit;
          
                                                                    // Send the API request
                                                    //*****************************************
                                                                             $response_New = array(
                                                                                'status' => 'success', // You can set this to 'error' in case of an error
                                                                                'message' => $message,
                                                                                'ptr_id' => $PTRId,
			                                                             'ptr_status' => $PTRStatus
                                                                            );

                                                                         //*****************************************
			                                                }
            else{
                //ptr statu completed expected 
                             $message = "Goto NExt level Get Exchange Quote"; 
                            $requestData = array(
                'ptrType' => 'GetExchangeQuote',
                'MFRef' => $mfreNum,
                'PTRId' => $PTRId,
                'Page' => 1
            );
          //  print_r($requestData);
             $endpoint   =   'Search/PostTicketingRequest';
        $result       =   $objCancel->callApi($endpoint,$requestData);
        $httpCode = $result['httpCode'];
        $response = $result['responseData'];
         if ($response) {
            $responseData = json_decode($response, true);
    
        }
        // print_r($responseData);exit;
        $logRes =   print_r($responseData, true);
        $logReQ =   print_r($requestData, true);

            $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchreissue.txt');
                        $objCancel->_writeLog('userId is '.$userId,'searchreissue.txt');
                      $objCancel->_writeLog('Booking ID is '.$bookingId,'searchreissue.txt');
                      $objCancel->_writeLog('Request Received OND GET EXCHANGE\n'.$logReQ,'searchreissue.txt');

                    $objCancel->_writeLog('REsponse Received for OND Type Search MF:\n'.$mfreNum,'searchreissue.txt');

        $objCancel->_writeLog('REsponse Received\n'.$logRes,'searchreissue.txt');
 

           // print_r($responseData);
           
            $PTRStatus      =   $responseData['Data']['Status'];     
            
            $Resolution   =   $responseData['Data']['Resolution'];
            
          //  print_r($PTRStatus);
            $objCancel->_writeLog('Step 1Success '.$PTRStatus,'searchreissue.txt');
             $objCancel->_writeLog('Step 1 Resolution '.$Resolution,'searchreissue.txt');

              if(isset($responseData['Success'])){
        //  echo  $responseData['Data']['PTRDetail']['PTRId'];
            if (isset($responseData['Data']['RequestedPreferences']) && (!empty($responseData['Data']['RequestedPreferences']))) {
            
                                                                                                    if($Resolution == "QuoteUpdated"){
                                                                                                        $PTRId      =  $responseData['Data']['PTRId'];
                                                                                                       
                                                                                                       // echo "PPPPPPPPPPP";
                                                                                                                             //   $htmlContent = '<div class="row fs-13 mb-3 px-3">AAAAAAAAAAAAAA.</div>'; 
                                                                                                                             foreach($responseData['Data']['RequestedPreferences'] as $key => $val){
                                                                                                                          
                                                                                                                             $PreferenceOption    = $val['Option'];
                                                                                                                             $quotedSegments    =   $val['QuotedSegments'];
                                                                                                                             $firstSegment = reset($quotedSegments); // Get the first element
                                                                                                                             $lastSegment = end($quotedSegments); 
                                                                                                                             //dep details flight
                                                                                                                             $departure_loc     =  $firstSegment['Origin']; 
                                                                                                                             $departure_time     =  $firstSegment['DepartureDatetime'];
                                                                                                                             $departure_airline     =  $firstSegment['AirlineCode'];
                                                                                                                             $departure_flightNumber     =  $firstSegment['FlightNumber'];
                                                                                                                            $departure_class     =  $firstSegment['BookingClass'];
                                                                                                                             $departure_duration     =  $firstSegment['Duration'];
                                                                                                                             //arrival details flight 
                                                                                                                             $arrv_resch_loc     =  $lastSegment['Origin']; 
                                                                                                                             $arrv_resch_time     =  $lastSegment['ArrivalDateTime'];
                                                                                                                            $arrv_resch_airline     =  $lastSegment['AirlineCode'];
                                                                                                                             $arrv_resch_flightNumber     =  $lastSegment['FlightNumber'];
                                                                                                                            $arrv_resch_class     =  $lastSegment['BookingClass'];
                                                                                                                             $arrv_resch_duration     =  $lastSegment['Duration'];
                                                                                                                            
                                                                                                                             $dateTimeStr = $departure_time;
                                                                                                                                $dateTime = new DateTime($dateTimeStr);

                                                                                                                                $formattedDate = $dateTime->format('d F Y'); // Format date as '31 August 2023'
                                                                                                                                $formattedTime = $dateTime->format('H:i:s'); // Format time as '16:30:00'
                                                                                                                                $stops  =   count($quotedSegments)-1;
                                                                                                                                $dateTime_arrv = new DateTime($arrv_resch_time);

                                                                                                                                $formattedDate_arrv = $dateTime_arrv->format('d F Y'); // Format date as '31 August 2023'
                                                                                                                                $formattedTime_arrv = $dateTime_arrv->format('H:i:s');
                                                                                                                                $totalfare_diff     =   reset($val['QuotedFares']);
                                                                                                                               $totalfare_diff_value     =   $totalfare_diff['TotalFareDifference'];
                                                                                                                                $Currency           =   $totalfare_diff['Currency']; 
                                                                                                                            //    print_r($val['QuotedFares']) ;
                                                                                                                             
                                                                                                                             
                                                                                                                             
                                                                                                                                //########################div with flight options after getexchane starts here ##########
                                                                                                                                 $htmlContent = '<div class="col-12 px-0">
                    <h6 class="text-left fw-700">Available Flights</h6>
                </div>
                <div class="col-12 light-border" >
               
                  <ul class="flight-list">
                        <li>
                            <ul class="row titlebar">
                                <li class="col-md-2 text-center">Airline</li>
                                <li class="col-md-1">Depart</li>
                                <li class="col-md-2">Stops</li>
                                <li class="col-md-2">Arrive</li>
                                <li class="col-md-3">Duration</li>
                                <li class="col-md-2 text-center">Price Difference</li>
                            </ul>
                        </li>
                       
                        <li class="pt-4 contentbar">
                            <ul class="row mb-lg-5 mb-3">
                                <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2"><img src="images/emirates-logo.png" alt=""></li>
                                <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                    <div class="">
                                        '. $departure_loc.'<br> '. $formattedDate . '<br> '. $formattedTime.' 
                                    </div>
                                    <div class="">
                                       
                                    </div>
                                </li>
                                <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        '.$stops.' Stop
                                        HND 9hr 30min
                                    </div>
                                    <div>
                                       
                                    </div>
                                </li>
                                <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        '. $arrv_resch_loc.'<br> '. $formattedDate_arrv . '<br> '. $formattedTime_arrv.'  
                                    </div>
                                    <div>
                                        
                                    </div>
                                </li>
                                <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                    <div>'.$arrv_resch_duration.'</div>
                                    <div>
                                        
                                    </div>
                                </li>
                                
                                <li data-th="Price" class="main-dtls col-md-2 d-flex flex-column align-items-md-center mb-md-0 mb-2">
                                    <div class="price-dtls mb-md-0 mb-2">'.$Currency.' '.'<strong>'.$totalfare_diff_value.'</strong></div>
                                    <button class="btn btn-typ3 w-100 reissue-accept-btn" 
                                            data-mfref="'.$mfreNum.'" 
                                            data-booking-id="'.$bookingId.'" 
                                            data-user-id="'.$userId.'" 
                                            data-preference-option="'.$PreferenceOption.'" 
                                            data-ptr-id="'.$PTRId.'">
                                        Yes, Proceed
                                    </button>
                                </li>
                               
                            </ul>
                            <div class="row panel flight-details-tab-wrap">
                                <ul class="nav nav-tabs d-flex justify-content-around w-100 pb-3">
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Flight Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Fare Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Baggage Details
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content text-center">
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane1">
                                        <button class="close"><span>&times;</span></button>
                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                            <div>Kochi <span class="right-arrow-small arrow-000000"></span> Dubai Friday, 18 Nov, 2022 Reaches next day</div>
                                            <div>Total Duration: 22hr 45m</div>
                                        </div>
                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                            <ul class="col-lg-3 mb-3">
                                                <div class="text-left">
                                                    <strong class="fw-500 d-block">Japan Airlines</strong>
                                                    Flight No - JL 494 Economy Boeing 73H
                                                </div>
                                            </ul>
                                            <div class="col-lg-7">
                                                <div class="d-flex row justify-content-between">
                                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                        <strong class="fw-500 d-block">KCZ 11:45</strong>
                                                        Fri, 18 Nov, 2022 Kma, Kochi
                                                    </div>
                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                            </svg>
                                                            1hr 15m
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 text-md-left">
                                                        <strong class="fw-500 d-block">13:00 HND</strong>
                                                        Fri, 18 Nov, 2022 Tokyo International, Tokyo Haneda Terminal 1
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="fs-15 fw-300 mb-4 text-left">
                                            Note: You will have to change Airport while travelling
                                        </div>
                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                            <div>Dubai <span class="right-arrow-small arrow-000000"></span> Kochi Saturday, 26 Nov, 2022 Arrives next day</div>
                                            <div>Total Duration: 24hr 5m</div>
                                        </div>
                                    </div>
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane2 ">
                                        <button class="close"><span>&times;</span></button>
                                        <div class="row fs-13 mb-3">
                                            <div class="col-md-5 mb-md-0 mb-3">
                                                <ul>
                                                    <li class="d-flex justify-content-between p-1 bdr-b">
                                                        <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in &#8377;)</span></strong>
                                                        <span>1 adult</span>
                                                    </li>
                                                    <li>
                                                        <ul class="bdr-b">
                                                            <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li>
                                                            <li class="d-flex justify-content-between p-1"><span>Adult (38748x1)</span><span>38748</span></li>
                                                            <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span>5070</span></li>
                                                            <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li>
                                                        </ul>
                                                        <ul class="bdr-b">
                                                            <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li>
                                                            <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Thomas Cook Charges</span><span>0</span></li>
                                                        </ul>
                                                    </li>
                                                    <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                        <strong class="fw-600">Total Fare</strong><strong>&#8377; 43,818</strong>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-7">
                                                <ul>
                                                    <li class="d-flex align-items-baseline p-1 bdr-b">
                                                        <strong class="fs-14 fw-600">Fare Rules </strong>
                                                        <span class="uppercase-txt white-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>
                                                    </li>
                                                    <li>
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                <span class="uppercase-txt">cok-dxb</span>
                                                            </li>
                                                            <li class="text-left">
                                                                <table class="w-100">
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr>
                                                                </table>
                                                            </li>
                                                        </ul>
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                                <span class="uppercase-txt">cok-dxb</span>
                                                            </li>
                                                            <li class="text-left">
                                                                <table class="w-100">
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr>
                                                                </table>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>Cancellation/Flight change charges are indicative. Thomas Cook does not guarantee the accuracy of this information. Airlines stop accepting cancellation/change requests 4 - 72 hours before departure of the flight, depending on the airline, in such scenarios airline will have to be contacted directly for cancellation/change. For "Non-Refundable" fares, certain Airline Tax components are also Non-Refundable. For exact cancellation/change fee, please call us on our Toll Free Number 1800 2099 100.</p>
                                    </div>
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane3">
                                        <button class="close"><span>&times;</span></button>
                                        <ul class="fs-13">
                                            <li class="text-left p-1 bdr-b">
                                                Cochin <span class="right-arrow-small arrow-000000"></span> Cochin
                                            </li>
                                            <li class="">
                                                <ul class="row align-items-center pt-3 pb-3">
                                                    <li class="col-md-1 mb-md-0 mb-2">
                                                        <img src="images/emirates-logo.png" alt="">
                                                    </li>
                                                    <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                        <strong>Emirates</strong>
                                                        <span class="uppercase-txt">cok <span class="right-arrow-small arrow-000000"></span> dxb</span>
                                                    </li>
                                                    <li class="col-md-7">
                                                        <ul class="row bdr-b">
                                                            <li class="col-4">Checkin</li>
                                                            <li class="col-4">1 pcs/person</li>
                                                            <li class="col-4">20 kgs/1pcs</li>
                                                        </ul>
                                                        <ul class="row">
                                                            <li class="col-4">Cabin</li>
                                                            <li class="col-4">1 pcs/person</li>
                                                            <li class="col-4">7 kgs/1pcs</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <ul class="row align-items-center pt-3 pb-3">
                                                    <li class="col-md-1 mb-md-0 mb-2">
                                                        <img src="images/emirates-logo.png" alt="">
                                                    </li>
                                                    <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                        <strong>Emirates</strong>
                                                        <span class="uppercase-txt">cok <span class="right-arrow-small arrow-000000"></span> dxb</span>
                                                    </li>
                                                    <li class="col-md-7">
                                                        <ul class="row bdr-b">
                                                            <li class="col-4">Checkin</li>
                                                            <li class="col-4">1 pcs/person</li>
                                                            <li class="col-4">20 kgs/1pcs</li>
                                                        </ul>
                                                        <ul class="row">
                                                            <li class="col-4">Cabin</li>
                                                            <li class="col-4">1 pcs/person</li>
                                                            <li class="col-4">7 kgs/1pcs</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>The information provided above is as retrieved from the airline reservation system. Thomas Cook does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure.</p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul> 
                  
                </div>';
                                                                                                    }

                                                                                                                                
                                                                                                                                //###################flight options from getexchange ends here ###################
                                                                                                                                $response_New = array(
                                                                                                        'status' => 'success', // You can set this to 'error' in case of an error
                                                                                                        'response_preference' => $responseData['Data']['RequestedPreferences'],
                                                                                                        'ptr_id' => $PTRId,
			                                                                                     'ptr_status' => $PTRStatus,
                                                                                                 'html_div' => $htmlContent
                                                                                                    );
                                                                                                     $logRes_new =   print_r($responseData['Data']['RequestedPreferences'], true);
                                                                                                      $objCancel->_writeLog('RequestedPreferences Received\n'.$logRes_new,'searchreissue.txt');

                                                                                                    // how to handle inprocess ?
                                                                                                   // need to show quoted fares ?
                                                                                                echo json_encode($response_New);exit;
                                                                                                       

                                                                                                    }
                                                                                                 //  echo "LLLLLLLLLLL";exit;
                                                                                                   //No quoted segments from search get exchange response 
                                                                                                   $message = "No quoted segments from search get exchange response";
                                                                                                    $response_New = array(
                                                                                                    'status' => 'error', // You can set this to 'error' in case of an error
                                                                                                    'message' => $message
                                                                                                );
                                                                                                echo json_encode($response_New);exit;                             
                                      
           //=========================   
            }
           }//SUCCESS IF ends FOR GETEXCHANGE
           else if($httpCode !=200)
            {
                 $message    =   $responseData['Message'];
                // Handle other status codes like 404, 500, etc.
                $message .=  "API request failed with status code: " . $httpCode;
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
                                      $objCancel->_writeLog('step data empty FROM SEARCH GETEXCHANGE'.$message,'searchreissue.txt');

            }


        } 
      
               $objCancel->_writeLog('step end of OND SEARCH  GETEXCHANGE RESPONSE  ========= '.$message,'searchreissue.txt');
echo json_encode($response_New);
exit;
        //========================end of search get exhange quote for OND===========
            }// end of else ptr completed 
           

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
        $message    = "Problem in reissue";
          $cancel_status = 0;
                         $response_New = array(
            'status' => 'error', // You can set this to 'error' in case of an error
            'message' => $message
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
                         //  echo "KK";exit;
                         //in OND type no flight data then call segment type
                         //************************************************************
                          $message    =   $responseData['Message'];
                          //################
                           $message = "No flights found for your search criteria";
                           //####################
                          $searchTerm = "No flights found for your search criteria";//need to call segment type reissue
                          if (strpos($message, $searchTerm) !== false) {
                             // echo "IIIII";exit;
                             $requestData = array(
                                        'ptrType' => 'ReissueQuote',
                                        'mFRef' => $mfreNum,
                                        'AllowChildPassenger' => $allow_child,
                                        'reissueQuoteRequestType' => "Segment",
                                        'passengers' => $passengers,
                                        'originDestinations' => $originDestinations
                                    );
                                      $endpoint   =   'PostTicketingRequest';
                                        $result       =   $objCancel->callApi($endpoint,$requestData);
                                        $httpCode = $result['httpCode'];
                                        $response = $result['responseData'];
                                        if ($response) {
                                                $responseData = json_decode($response, true);
    
                                                }
                                                $logRes =   print_r($responseData, true);
                                                  $logReQ =   print_r(json_encode($requestData), true);
                                                    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
                                                            $objCancel->_writeLog('REsponse Received for Segment Type MF:\n'.$mfreNum,'reissueQuote.txt');
                                                               $objCancel->_writeLog('userId is '.$userId,'reissueQuote.txt');
                                                              $objCancel->_writeLog('Booking ID is '.$bookingId,'reissueQuote.txt');
                                                $objCancel->_writeLog('REquest Received\n'.$logReQ,'reissueQuote.txt');

                                                $objCancel->_writeLog('REsponse Received\n'.$logRes,'reissueQuote.txt');
 
                                                                //write log
                                               // print_r($responseData);
                                               // exit;
                                               $message = ""; 
                                                //##################################################
                                                     $responseData = array(
                                                    'Success' => 1,
                                                    'Data' => array(
                                                        'PTRId' => 10816,
                                                        'PTRType' => 'ReIssueQuote',
                                                        'MFRef' => 'MF23829623',
                                                        'SLAInMinutes' => 0,
                                                        'PTRStatus' => 'Completed'
                                                    )
                                                );
                                                $mfreNum    =   "MF23829623";
                                                $PTRId="10816";
                                          //  echo "NEWWWWWW";
                                          //  print_r($responseData);exit;
                                                //###############################################
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
                                                                             $response_New = array(
                                                                                'status' => 'success', // You can set this to 'error' in case of an error
                                                                                'message' => $message,
                                                                                'ptr_id' => $PTRId,
			                                                             'ptr_status' => $PTRStatus
                                                                            );

                                                                         //*****************************************
			                                                }
                                                            else{
                                                                //PTR Segment type Reissue Quote completed
                                                               // echo "Call getexchange api";exit;
                                                                $requestData = array(
                                                                'ptrType' => 'GetExchangeQuote',
                                                                'MFRef' => $mfreNum,
                                                                'PTRId' => $PTRId,
                                                                'Page' => 1
                                                            );
                                                          //  print_r($requestData);
                                                                    $endpoint   =   'Search/PostTicketingRequest';
                                                                    $result       =   $objCancel->callApi($endpoint,$requestData);
                                                                    $httpCode = $result['httpCode'];
                                                                    $response = $result['responseData'];
                                                                     if ($response) {
                                                                        $responseData = json_decode($response, true);
                                                                        }
                                                             //    print_r($responseData);
                                                                $logRes =   print_r($responseData, true);
                                                                $logReQ =   print_r($requestData, true);

                                                                    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','searchreissue.txt');
                                                                                $objCancel->_writeLog('userId is '.$userId,'searchreissue.txt');
                                                                              $objCancel->_writeLog('Booking ID is '.$bookingId,'searchreissue.txt');
                                                                              $objCancel->_writeLog('Request Received from search ptr getexchange for SEgment \n'.$logReQ,'searchreissue.txt');

                                                                            $objCancel->_writeLog('REsponse Received for MF:\n'.$mfreNum,'searchreissue.txt');

                                                                $objCancel->_writeLog('REsponse Received\n'.$logRes,'searchreissue.txt');
 

                                                                   // print_r($responseData);
           
                                                                    $PTRStatus      =   $responseData['Data']['Status'];     
            
                                                                    $Resolution   =   $responseData['Data']['Resolution'];
            
                                                                   // print_r($PTRStatus);exit;
                                                                    $objCancel->_writeLog('Step 1Success getexchange '.$PTRStatus,'searchreissue.txt');
                                                                     $objCancel->_writeLog('Step 1 Resolution '.$Resolution,'searchreissue.txt');
                                                            //success from search get exchange 
                                                            if(isset($responseData['Success'])){
                                                                                                if (isset($responseData['Data']['RequestedPreferences']) && (!empty($responseData['Data']['RequestedPreferences']))) {
            
                                                                                                    if($Resolution == "QuoteUpdated"){
                                                                                                       // echo "PPPPPPPPPPP";
                                                                                                                                                                                                                             $response_New = array(
                                                                                                        'status' => 'success', // You can set this to 'error' in case of an error
                                                                                                        'response_preference' => $responseData['Data']['RequestedPreferences'],
                                                                                                        'ptr_id' => $PTRId,
			                                                                                     'ptr_status' => $PTRStatus
                                                                                                    );
                                                                                                    // how to handle inprocess ?
                                                                                                   // need to show quoted fares ?
                                                                                                echo json_encode($response_New);exit;
                                                                                                       

                                                                                                    }
                                                                                                 //  echo "LLLLLLLLLLL";exit;
                                                                                                   //No quoted segments from search get exchange response 
                                                                                                   $message = "No quoted segments from search get exchange response";
                                                                                                    $response_New = array(
                                                                                                    'status' => 'error', // You can set this to 'error' in case of an error
                                                                                                    'message' => $message
                                                                                                );
                                                                                                echo json_encode($response_New);exit;
                              
              
                                                                                                }
                                                                                               }

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
      
                                                                                                   $objCancel->_writeLog('step end of search reissue  ========= '.$message,'searchreissue.txt');
                                                                                    echo json_encode($response_New);
                                                                                    exit;
                                                            
                                                            
                                                            
                                                            
                                                            
                                                            
                                                                             $response_New = array(
                                                                        'status' => 'success', // You can set this to 'error' in case of an error
                                                                        'message' => $message,
                                                                        'ptr_id' => $PTRId,
			                                                     'ptr_status' => $PTRStatus
                                                                    );
                                                            }

                                                            
                                                }//success response from segment reissuequote ends
                                                    else if(empty($responseData['Data'])){
                                                        if(!empty($responseData['Message'])){   // echo "uu";exit;                          
               
                                                                    $message    =   $responseData['Message'];
                                                                     $message_new    = $message;
                                                       //    $bookCanIns      =   $objCancel->insCncelSts_Search($bookingId,$userId,$BookingStatus='',$Resolution='', $mfreNum,$ProcessingMethod='',$PTRId='',$PTRType='',$CreditNoteNumber='',$PTRStatus='',$CreditNoteStatus='', $ticket_num='' ,$pax_booking_id_transaction='' ,$PaxId='',$TicketStatus='',$TotalRefundAmount='',$is_active_booking_status='',$cancel_status='',$message);                                                   

                                                                    //may alreay cancelled 
                                                                     $response_New = array(
                                                                        'status' => 'error', // You can set this to 'error' in case of an error
                                                                        'message' => $message
                                                                    );
                                                                    $objCancel->_writeLog('step Data empty  from segment type reissue quote'.$message,'searchreissue.txt');

                                                        }
                                                    }

                        }
                              else {//error case of no succss of responsedata
                                 // console.log("Message does not contain the search term.");
                                // echo "err";exit;
                                 $message   ="Error in Reissue Request";
                                 
                                           $response_New = array(
                                    'status' => 'error', // You can set this to 'error' in case of an error
                                    'message' => $message
                                );
                                 $objCancel->_writeLog('step data empty '.$message,'reissueQuote.txt');
                                             
                                     }


                              
               //************************************************************************
                       
                        
                       // $bookCanIns      =   $objCancel->insCncelSts($bookingId,$userId,$precancelsts,$errorCode ='', $mfreNum,$traceId='',$httpCode,$PTRType='voidQuote',$SLAInMinutes='',$PTRStatus='',$VoidingWindow='', $ticket_num=''  ,$AdminCharges='' ,$GSTCharge='',$TotalVoidingFee='',$TotalRefundAmount='',$Currency='',$cancel_status,$message_new);                                                   

                        //Booking is not eligible for voiding. -may be not under void window param
                        
                                     

            }


        }
         $objCancel->_writeLog('step end of reissue quote ========= '.$message,'reissueQuote.txt');
echo json_encode($response_New);
exit;


   }
?>

<script>
$(document).ready(function() {
    // Handle reissue acceptance button clicks
    $(document).on('click', '.reissue-accept-btn', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const mfRef = button.data('mfref');
        const bookingId = button.data('booking-id');
        const userId = button.data('user-id');
        const preferenceOption = button.data('preference-option');
        const ptrId = button.data('ptr-id');
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Confirm Flight Change',
            text: 'Are you sure you want to proceed with this flight change? This action cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                processReissueAcceptance(mfRef, bookingId, userId, preferenceOption, ptrId, button);
            }
        });
    });
});

function processReissueAcceptance(mfRef, bookingId, userId, preferenceOption, ptrId, button) {
    // Disable button and show loading state
    const originalText = button.html();
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    // Show loading modal
    Swal.fire({
        title: 'Processing Request',
        text: 'Your flight change request is being processed...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const requestData = {
        action: 'processReissueAcceptance',
        mfreNum: mfRef,
        bookingId: bookingId,
        userId: userId,
        PreferenceOption: preferenceOption,
        PTRId: ptrId
    };
    
    console.log('Sending reissue acceptance request:', requestData);
    
    $.ajax({
        url: 'reissue_ticket.php',
        type: 'POST',
        dataType: 'json',
        data: requestData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        timeout: 30000, // 30 seconds timeout
        success: function(response) {
            console.log('Reissue acceptance response:', response);
            
            // Re-enable button
            button.prop('disabled', false).html(originalText);
            
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#007bff',
                    confirmButtonText: 'Go to Booking Details'
                }).then(() => {
                    // Redirect to booking details
                    window.location.href = `flight-booking-details.php?booking_id=${bookingId}`;
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to process reissue acceptance',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            
            // Re-enable button
            button.prop('disabled', false).html(originalText);
            
            let errorMessage = 'Network error occurred. Please try again.';
            
            if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Failed to parse error response:', e);
                }
            }
            
            Swal.fire({
                title: 'Connection Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
</script>