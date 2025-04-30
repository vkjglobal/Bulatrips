<?php
session_start();
if (!isset($_SESSION['user_id'])) {
?>
    <script>
        // window.location = "index.php"
    </script>    
<?php
// exit;
}



$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : "";
//echo "<pre/>";print_r($_SESSION);exit;
error_reporting(0);
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once("includes/header.php");
require_once('includes/dbConnect.php');
include_once('includes/common_const.php');
include_once('sendmail.php');
include_once('includes/class.BookScript.php');

$objBook    =   new BookScript();
$endpoint   =   'v1.1/TripDetails/{MFRef}';
$apiEndpoint = APIENDPOINT.$endpoint;
$bearerToken   =   BEARER;

//echo 'helo';exit;

$bookingId = $_GET['booking_id'];
$stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid');

$stmtbookingid->execute(array('bookingid' => $bookingId));
$bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);


$cookie_name = $bookingId;
$cookie_exists = isset($_COOKIE[$cookie_name]) ? true : false;
$user_loggedin_status = isset($_SESSION['user_id']) ? true : false;


if( $cookie_exists || $user_loggedin_status ) {
    //userinfo recent added 
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
        $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(array('id' => $_SESSION['user_id']));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $user = array(
            "email" => $bookingData['contact_email'],
            "first_name" => $bookingData['contact_first_name'],
            "last_name" => $bookingData['contact_last_name']
        );
        $userEmail = $bookingData['contact_email'];
    }


    //echo "<pre/".$bookingId;print_r($bookingData);exit;
    $bookingStatus = $bookingData['booking_status'];
    $totalPaid =   $bookingData['total_paid'];
    $fromLoc =   $bookingData['dep_location'];
    $ToLoc =   $bookingData['arrival_location'];
    if (isset($bookingData['mf_reference'])) {
        //  $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1.1/TripDetails/{MFRef}';
        //  $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';

        // Set the MFRef value for the endpoint
        $mfRef = $bookingData['mf_reference'];
    
        $apiEndpoint = str_replace('{MFRef}', $mfRef, $apiEndpoint);

        // Send the API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//for testing 

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $bearerToken
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        
        // Process the API response
        if ($response === false) {
        
            // Error handling
        
            echo 'Error: ' . curl_error($ch);
            
            $responseData = json_decode($response, true);
        } else {
        // var_dump($response);exit;
            // Process the response data
            
            $responseData = json_decode($response, true);
            // Handle the response data as needed
            //var_dump($responseData);exit;
        }
        // Handle the API response
    
        if ($response) {
            $responseData = json_decode($response, true);
        }
        //=================log write for Tripetails api  after booking API ======
                    
                        $logRes =   print_r($responseData, true);
                    
                        $objBook->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','tripConfirm.txt');
                                $objBook->_writeLog('MF ref'.$mfRef,'tripConfirm.txt');
                                // $objBook->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'tripConfirm.txt');
                                // $objBook->_writeLog('Booking ID is '.$bookingID,'tripConfirm.txt');
                        

                    $objBook->_writeLog('REsponse Received\n'.$logRes,'tripConfirm.txt');   
                
        //============ END log write for trip API ==========  
        // echo '<pre/>';
        // print_r($responseData);
        
        // echo '</pre>';
        if((!empty($responseData)) && (($responseData['Success']))){
            $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
            $tripDetailsAtaInfo = $tripDetails['ATAinfoList']; //fare attributes
            $tripDetailsExtraServices = $tripDetails['ExtraServices']['Services']; //ExtraServices
            $itinerariesDetail = $tripDetails['Itineraries'][0]['ItineraryInfo']['ReservationItems'];
            $passengerDetail =  $tripDetails['PassengerInfos'];
            $ticketStatus = $tripDetails['TicketStatus'];
        // print_r($tripDetails);

            $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus,ticket_status = :ticketStatus,ticket_time_limit = :ticketTimeLimit,booking_date = :bookingDate,void_window =:voidWindow WHERE id = :id');

            // Set the values
                    //for webfrae type booking status is not getting from api response after ticketed ,though it didnt mentioninside doc ,so considered booking since Already Ticketed status getting 

            if(!isset($tripDetails['BookingStatus'])){
            
                if($ticketStatus == "Ticketed"){

                $bookingStatus = "booked";
                }
            }
            else{
                $bookingStatus = $tripDetails['BookingStatus'];
            }
            
        //  print_r( $bookingStatus );die();
            $ticketTimeLimit = $tripDetails['TicketingTimeLimit'];
            $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
        
            if(isset($tripDetails['VoidingWindow'])){
                $voidWindow = $tripDetails['VoidingWindow'];

            }else{
                $voidWindow=""; //because i didnt see this from testing but api doc said this will be available 
            }
        // $markup = $tripDetails['ClientMarkup']['Amount'];
            $id = $bookingId;

            // Bind the parameters
            $stmtupdate->bindParam(':ticketTimeLimit', $ticketTimeLimit);
            $stmtupdate->bindParam(':bookingStatus', $bookingStatus);
            $stmtupdate->bindParam(':bookingDate', $bookingDate);
            $stmtupdate->bindParam(':ticketStatus', $ticketStatus);
            $stmtupdate->bindParam(':voidWindow', $voidWindow);
        // $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':id', $id);



            // Execute the query
            $stmtupdate->execute();


            // $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus WHERE flight_booking_id  = :bookingId');
                // $ticketNumber = $passengerInfo['ETickets'][0]['ETicketNumber'];
            $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus,e_ticket_number=:ticketNumber WHERE flight_booking_id  = :bookingId and passport_number=:PassportNumber');

            // Set the values
            $ticketStatus = $tripDetails['TicketStatus'];
            $id = $bookingId;
            foreach ($passengerDetail as $passengerInfo) {
            
                if(isset($passengerInfo['ETickets'][0]['ETicketNumber'])){
                                $ticketNumber = $passengerInfo['ETickets'][0]['ETicketNumber'];
                                $ticketStatusType   =   $passengerInfo['ETickets'][0]['ETicketType'];
                            }elseif(!empty($ticketStatus)){
                                $ticketStatusType   = $ticketStatus;
                                $ticketNumber="";
                            }
                            else{
                                $ticketNumber="";
                                $ticketStatusType ="";
                            }
                $PassportNumber = $passengerInfo['Passenger']['PassportNumber'];
        
                // Bind the parameters and execute the update statement
                $stmtupdatetravellers->bindParam(':ticketNumber', $ticketNumber, PDO::PARAM_STR);
                $stmtupdatetravellers->bindParam(':PassportNumber', $PassportNumber, PDO::PARAM_STR);
                $stmtupdatetravellers->bindParam(':ticketStatus', $ticketStatus);
                $stmtupdatetravellers->bindParam(':bookingId', $id);
                $stmtupdatetravellers->execute();
            }

            // Bind the parameters
            // $stmtupdatetravellers->bindParam(':ticketStatus', $ticketStatus);
            // $stmtupdatetravellers->bindParam(':bookingId', $id);



            // Execute the query
            $stmtupdatetravellers->execute();

    



            $tripDetailsfare = $responseData['Data']['TripDetailsResult']['TravelItinerary']['TripDetailsPTC_FareBreakdowns'];
            // echo "<pre/>";print_r($tripDetailsfare);exit;
            foreach ($tripDetailsfare as $tripDetailsfares) {
                $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET basic_fare = :basicFare ,
                    total_pass_fare = :totalPassFare, tax = :tax ,free_checkin_baggage = :baggageInfo,free_cabin_baggage = :cabinBaggage WHERE flight_booking_id  = :bookingId and passenger_type =:passengerType');

                $basicFare = $tripDetailsfares['TripDetailsPassengerFare']['EquiFare']['Amount'];
                $tax = $tripDetailsfares['TripDetailsPassengerFare']['Tax']['Amount'];
                $totalPassFare = $tripDetailsfares['TripDetailsPassengerFare']['TotalFare']['Amount'];
                $passengerType = $tripDetailsfares['PassengerTypeQuantity']['Code'];
                $baggageInfo = $tripDetailsfares['BaggageInfo'][0];
                $cabinBaggage = $tripDetailsfares['CabinBaggageInfo'][0];
                $bookingId = $bookingId;

                // Bind the parameters
                $stmtupdatetravellers->bindParam(':bookingId', $bookingId);

                $stmtupdatetravellers->bindParam(':basicFare', $basicFare);
                $stmtupdatetravellers->bindParam(':tax', $tax);
                $stmtupdatetravellers->bindParam(':passengerType', $passengerType);
                $stmtupdatetravellers->bindParam(':totalPassFare', $totalPassFare);
                $stmtupdatetravellers->bindParam(':baggageInfo', $baggageInfo);
                $stmtupdatetravellers->bindParam(':cabinBaggage', $cabinBaggage);



                // Execute the query
                $stmtupdatetravellers->execute();
            }
        }// end of not empty response process
        // echo '<pre>';
        // print_r($tripDetails);
        // echo '</pre>';
    }
        //end of id mf number 
        //echo 'hhhhh<pre/>';
        // print_r($responseData);exit;
        $errStatus  =   0;
        
    if (!empty($responseData['Data']['Errors'])) { ?>
        <div class=" container">
            <?php echo $responseData['Message']; ?>
            <?php
                            $errStatus  =   1; // need to handle error case like repay mail etc
                    $Errmessage = $responseData['Message']; 

                        echo "<script>";
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "    var emptypop = document.getElementById('errorModal');";
            echo "    var errorMsgElement = document.getElementById('errorMessage');";
            echo "    if (emptypop && errorMsgElement) {";
            echo "        emptypop.classList.add('show');";
            echo "        emptypop.style.display = 'block';";
            echo "        errorMsgElement.textContent = '" . addslashes($Errmessage) . "';";
            echo "    }";
            echo "});";
            echo "</script>";

                // echo "update success ";
            ?>
        </div>
        <?php
    } else if((empty($responseData['Success'])) || (!$responseData['Success'])) {
        //  echo "yyyyy";
        //  var_dump($responseData['sucess']); exit;?>
        <div class=" container">

            <?php
                    $errStatus  =   1; // need to handle error case like repay mail etc
                    $Errmessage = "Error Received from airline :".$responseData['Message']. "No results received to show here .Please search again or check with your dashboard Booking details"; 

                        echo "<script>";
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "    var emptypop = document.getElementById('errorModal');";
            echo "    var errorMsgElement = document.getElementById('errorMessage');";
            echo "    if (emptypop && errorMsgElement) {";
            echo "        emptypop.classList.add('show');";
            echo "        emptypop.style.display = 'block';";
            echo "        errorMsgElement.textContent = '" . addslashes($Errmessage) . "';";
            echo "    }";
            echo "});";
            echo "</script>";
                // echo "update success ";
            ?>
        </div>
        <?php
    } elseif(empty($responseData)) {
        ?>
        <div class=" container">
                <?php
                    $errStatus  =   1; // need to handle error case like repay mail etc
                    $Errmessage = "No results received from airline to show here .Please search again or check with your dashboard Booking details"; 

                        echo "<script>";
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "    var emptypop = document.getElementById('errorModal');";
            echo "    var errorMsgElement = document.getElementById('errorMessage');";
            echo "    if (emptypop && errorMsgElement) {";
            echo "        emptypop.classList.add('show');";
            echo "        emptypop.style.display = 'block';";
            echo "        errorMsgElement.textContent = '" . addslashes($Errmessage) . "';";
            echo "    }";
            echo "});";
            echo "</script>";
                // echo "update success ";
            ?>
            
                </div><?php
    } elseif($bookingStatus == "NotBooked") {
        ?>
        <div class=" container">
                <?php
                    $errStatus  =   1; // need to handle error case like repay mail etc
                    $Errmessage = "Latest status from AirLine Shows NotBooked .Please search again or check with your dashboard Booking details"; 

                        echo "<script>";
            echo "document.addEventListener('DOMContentLoaded', function() {";
            echo "    var emptypop = document.getElementById('errorModal');";
            echo "    var errorMsgElement = document.getElementById('errorMessage');";
            echo "    if (emptypop && errorMsgElement) {";
            echo "        emptypop.classList.add('show');";
            echo "        emptypop.style.display = 'block';";
            echo "        errorMsgElement.textContent = '" . addslashes($Errmessage) . "';";
            echo "    }";
            echo "});";
            echo "</script>";
                // echo "update success ";
            ?>
            
                </div><?php
    } else { 
    
            $onewaysegment = [];
            $returnsegment = [];
        //  echo "<pre/>";print_r($itinerariesDetail);exit;
            foreach ($itinerariesDetail as $index => $flight) {
                if ($index < $bookingData['stops'] + 1) {
                    $onewaysegment[] = $flight;
                }
                if ($index > $bookingData['stops']) {
                    $returnsegment[] = $flight;
                }
            }
            $onewaysegmentLast = end($onewaysegment);
            $returnsegmentLast = end($returnsegment);
        $cabin_class_mail    =    $onewaysegment[0]['CabinClass'];
        if($cabin_class_mail == 'Y'){
            $cabin_class_text   ="Economy";
        }
        elseif($cabin_class_mail == 'S'){
            $cabin_class_text   ="Premium";
        }
        elseif($cabin_class_mail == 'C'){
            $cabin_class_text   ="Business";
        }
        elseif($cabin_class_mail == 'F'){
            $cabin_class_text   ="First";
        }
        ?>

        <section>
        
            <div class="container">
                <!-- <h2 class="title-typ2 my-4 text-center">Your booking status has been <?php //echo $bookingStatus; ?>.</h2> -->
                <h2 class="title-typ2 my-4 text-center">Booking Details</h2>
                <div class="text-left">
                    <hr>
                    <!-- <span>Return</Span> -->
                    <span class="h5 px-3">Departure</span>
                    <hr>
                </div>
                <div class="row my-4">
                    <!-- <div class="col-12 text-center fw-700">
                        Booking Details from airline  for the ordered ticket are as follows: 
                    </div> -->
                    <div class="col-12 text-center">
                        <div class="">
                            <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">

                                <?php
                                $segmentCount = count($itinerariesDetail);
                                $segmentCount -= 1;
                                // print_r( $segmentCount);
                                $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $itinerariesDetail[0]['DepartureDateTime']);
                                if ($segmentCount == "0") {
                                    $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $itinerariesDetail[0]['ArrivalDateTime']);
                                } else {
                                    $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $itinerariesDetail[$segmentCount]['ArrivalDateTime']);
                                }


                                $diff = $date1->diff($date2);
                                // Get the difference in hours and minutes
                                $hours = $diff->h;
                                $minutes = $diff->i;
                                //  echo $hours ." h ".$minutes." m ";

                                $datetime = $itinerariesDetail[0]['DepartureDateTime'];
                                list($date, $time) = explode("T", $datetime);
                                ?>
                                <div><?php echo $tripDetails['Origin']; ?> <span class="right-arrow-small arrow-000000"></span>
                                    <?php
                                    // print_r($onewaysegmentLast);
                                    echo $onewaysegmentLast['ArrivalAirportLocationCode'] . " " . date("d F Y", strtotime($date)); ?>
                                    <!-- echo $onewaysegmentLast['Destination'] . " " . date("d F Y", strtotime($date)); ?> -->
                                </div>
                                <!-- <div>Total Duration:<?php echo $hours . " hr " . $minutes . " m "; ?></div> -->
                            </div>

                            <?php
                            //  ($itinerariesDetail as $index => $itinerariesDetails) {
                            foreach ($onewaysegment as $index => $itinerariesDetails) {
                            // echo "<pre/>";print_r($itinerariesDetails['FlightNumber']) ;
                            //----------------
                                $ATAinfoRef    =    $itinerariesDetails['ATAinfoRef'];
                                $cabin_class_mail    =    $itinerariesDetails['CabinClass'];
                                if($cabin_class_mail == 'Y'){
                                    $cabin_class_text   ="Economy";
                                }
                                elseif($cabin_class_mail == 'S'){
                                    $cabin_class_text   ="Premium";
                                }
                                elseif($cabin_class_mail == 'C'){
                                    $cabin_class_text   ="Business";
                                }
                                elseif($cabin_class_mail == 'F'){
                                    $cabin_class_text   ="First";
                                }
                            //---------------
                                $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                $code = '%' . $itinerariesDetails['OperatingAirlineCode'] . '%';
                                $stmtairline->bindParam(':code', $code);
                                $stmtairline->execute();
                                $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                
                                ?>
                                <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">

                                    <div class="col-lg-3 mb-3">
                                        <div class="text-left">
                                            <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                            Flight No -<?php echo $itinerariesDetails['FlightNumber'] . " " . $cabin_class_text?>
                                        <div>   AirlinePNR -<?php echo $itinerariesDetails['AirlinePNR']; ?></div>
                                        </div>
                                        <div class="d-flex flex-lg-column flex-row align-items-start fs-12 fw-500 mt-2">
                                            <!--<strong class="fw-600">Baggage (per Adult/Child)-</strong> <?php echo "Ckeck-in:" . $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][$index] . " Cabin: " . $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index] ?> -->
                                        </div>
                                        
                                        

                                    </div>
                                    
                                    <div class="col-lg-7">
                                        <?php
                                        $datetime = $itinerariesDetails['DepartureDateTime'];
                                        list($date, $time) = explode("T", $datetime);
                                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                        $stmtlocation->execute(array('airport_code' => $itinerariesDetails['DepartureAirportLocationCode']));
                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <div class="d-flex row justify-content-between">
                                            <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                <strong class="fw-500 d-block"><?php echo $itinerariesDetails['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                <?php echo date("d F Y", strtotime($date)) . " " . $airportLocation['airport_name'] . " , " . $airportLocation['city_name'] ?>
                                            </div>
                                            <div class="col-md-2 mb-md-0 mb-2">
                                                <div class="d-flex flex-column align-items-center">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"></path>
                                                    </svg>
                                                    <?php
                                                    $minutes = $itinerariesDetails['JourneyDuration'];
                                                    $hours = floor($minutes / 60);
                                                    $remainingMinutes = $minutes % 60;
                                                    echo $hours . " h  " . $remainingMinutes . " m";
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-md-5 text-md-left">
                                                <?php
                                                $datetime = $itinerariesDetails['ArrivalDateTime'];
                                                list($date, $time) = explode("T", $datetime);
                                                // echo date("d F Y", strtotime($date));
                                                $stmtlocation->execute(array('airport_code' => $itinerariesDetails['ArrivalAirportLocationCode']));
                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                ?>
                                                <strong class="fw-500 d-block"><?php echo $time . " " . $itinerariesDetails['ArrivalAirportLocationCode']; ?></strong>
                                                <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    <?php
                                        //ATAinfoList not in api doc but received while executing api response ===
                                    foreach($tripDetailsAtaInfo as $k => $vals){
                                        if($vals['id'] == $ATAinfoRef){
                                            $fareAttributes =   $vals['fareAttributes'];
                                        
                                        ?>
                                    <!-- ========================= -->
                                            <div class="table-responsive">
                                        <h6 class="text-left fw-700">Fare Attributes</h6>
                                        <table class="table table-bordered white-bg text-left fs-14" style="min-width: 800px;">
                                            <thead>
                                                <tr class="dark-blue-bg white-txt">
                                                    <th>Name</th>
                                                    <th>Applicability</th>
                                                    <!-- <th>Age group</th> -->
                                                
                                                    <th>Message</th>
                                                <!--  <th>CostType</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                foreach($fareAttributes as $key => $farevals){
                                                    $farename = $farevals['name'];
                                                    $farecode = $farevals['code'];
                                                    $fareAppl = $farevals['applicability'];
                                                //  $faredescription = $farevals['industrySubCodes']['description1'];
                                                    $faremessage = $farevals['conditions'][0]['message'];
                                                    $farecostType = $farevals['conditions'][0]['properties']['costType'];
                                                    if(($farecode == "CBNBGG") || (($farecode == "CHKBGG"))){ //since current array doesnt give value on weighteetc using baggage info from another array of response
                                                        $fareweightPerPiece = $farevals['conditions'][1]['properties']['weightPerPiece'];
                                                        $fareweightPerPiece .= $farevals['conditions'][1]['properties']['weightUnit'];
                                                            
                                                    
                                                    if(empty($fareweightPerPiece)){
                                                            $fareweightPerPiece = "No accurate  Weight values from Airline";
                                                        }
                                                    
                                                        $farecostType   =  $fareweightPerPiece . $fareweightUnit;
                                                    }
                                                    
                                                    if(($farecode == "CHNGBL") || (($farecode == "REFUND"))){
                                                        $fareweightPerPiece= $farevals['conditions'][0]['properties']['fee'];
                                                    $fareweightPerPiece .= $farevals['conditions'][0]['properties']['currency'];

                                                        if(empty($fareweightPerPiece)){
                                                            $fareweightPerPiece = "No accurate Fee values from Airline";
                                                        }
                                                    // $farecostType   =  $fareweightPerPiece . $fareweightUnit;
                                                    }
                                                    
                                                ?>
                                                    <tr>
                                                    <td><?php echo $farename ;?></td>
                                                    <td><?php echo $fareAppl; ?></td>
                                                        
                                                        <td><?php echo $faremessage; ?></td>
                                                    <!--   <td><?php echo $farecostType ." ".$fareweightPerPiece; ?></td> -->
                                                    </tr>
                                                <?php
                                                } //eof farevals
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- =========================== -->
                                        
                                    <?php
                                        
                                    } //eof if

                                    } //eo foreach atainfo
                            


                                //=================================
                                ?>

                                <?php
                            }
                            
                                ?>
                            
                            

                                <?php

                                if ($returnsegment) {
                                ?>
                                    <div class="text-left">
                                        <hr>
                                        <!-- <span>Return</Span> -->
                                        <span class="h5 px-3">Return</span>
                                        <hr>
                                    </div>
                                    <?php
                                    //  ($itinerariesDetail as $index => $itinerariesDetails) {
                                    foreach ($returnsegment as $index => $itinerariesDetails) {
                                        //----------------
                                            $ATAinfoRef    =    $itinerariesDetails['ATAinfoRef'];
                                            $cabin_class_mail    =    $itinerariesDetails['CabinClass'];
                                            if($cabin_class_mail == 'Y'){
                                                $cabin_class_text   ="Economy";
                                            }
                                            elseif($cabin_class_mail == 'S'){
                                                $cabin_class_text   ="Premium";
                                            }
                                            elseif($cabin_class_mail == 'C'){
                                                $cabin_class_text   ="Business";
                                            }
                                            elseif($cabin_class_mail == 'F'){
                                                $cabin_class_text   ="First";
                                            }
                            //---------------
                                        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                        $code = '%' . $itinerariesDetails['OperatingAirlineCode'] . '%';
                                        $stmtairline->bindParam(':code', $code);
                                        $stmtairline->execute();
                                        $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">


                                            <div class="col-lg-3 mb-3">
                                                <div class="text-left">
                                                    <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                                    Flight No -<?php echo $itinerariesDetails['FlightNumber'] . " " . $cabin_class_text; ?>
                                                <span>AirlinePNR -<?php echo $itinerariesDetails['AirlinePNR']; ?></span>
                                                </div>
                                            <!--  <strong>Baggage (per Adult/Child)-</strong> <?php echo "Ckeck-in:" . $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][$index] . " Cabin: " . $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index] ?> -->


                                            </div>

                                            <div class="col-lg-7">
                                                <?php
                                                $datetime = $itinerariesDetails['DepartureDateTime'];
                                                list($date, $time) = explode("T", $datetime);
                                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                $stmtlocation->execute(array('airport_code' => $itinerariesDetails['DepartureAirportLocationCode']));
                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                ?>
                                                <div class="d-flex row justify-content-between">
                                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                        <strong class="fw-500 d-block"><?php echo $itinerariesDetails['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) . " " . $airportLocation['airport_name'] . " , " . $airportLocation['city_name'] ?>
                                                    </div>
                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"></path>
                                                            </svg>
                                                            <?php
                                                            $minutes = $itinerariesDetails['JourneyDuration'];
                                                            $hours = floor($minutes / 60);
                                                            $remainingMinutes = $minutes % 60;
                                                            echo $hours . " h  " . $remainingMinutes . " m";
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 text-md-left">
                                                        <?php
                                                        $datetime = $itinerariesDetails['ArrivalDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        // echo date("d F Y", strtotime($date));
                                                        $stmtlocation->execute(array('airport_code' => $itinerariesDetails['ArrivalAirportLocationCode']));
                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                        ?>
                                                        <strong class="fw-500 d-block"><?php echo $time . " " . $itinerariesDetails['ArrivalAirportLocationCode']; ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                            <!-- ======================= -->
                                            <?php
                                        //ATAinfoList not in api doc but received while executing api response ===
                                    foreach($tripDetailsAtaInfo as $k => $vals){
                                        if($vals['id'] == $ATAinfoRef){
                                            $fareAttributes =   $vals['fareAttributes'];
                                        
                                        ?>
                                    <!-- ========================= -->
                                            <div class="table-responsive">
                                        <h6 class="text-left fw-700">Fare Attributes</h6>
                                        <table class="table table-bordered white-bg text-left fs-14" style="min-width: 800px;">
                                            <thead>
                                                <tr class="dark-blue-bg white-txt">
                                                    <th>Name</th>
                                                    <th>Applicability</th>
                                                    <!-- <th>Age group</th> -->
                                                
                                                    <th>Message</th>
                                                <!--  <th>CostType</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                foreach($fareAttributes as $key => $farevals){
                                                    $farename = $farevals['name'];
                                                    $farecode = $farevals['code'];
                                                    $fareAppl = $farevals['applicability'];
                                                //  $faredescription = $farevals['industrySubCodes']['description1'];
                                                    $faremessage = $farevals['conditions'][0]['message'];
                                                    $farecostType = $farevals['conditions'][0]['properties']['costType'];
                                                    if(($farecode == "CBNBGG") || (($farecode == "CHKBGG"))){
                                                        $fareweightPerPiece = $farevals['conditions'][1]['properties']['weightPerPiece'];
                                                        $fareweightPerPiece .= $farevals['conditions'][1]['properties']['weightUnit'];
                                                        if(empty($fareweightPerPiece)){
                                                            $fareweightPerPiece = "No accurate  Weight values from Airline";
                                                        }
                                                    
                                                    // $farecostType   =  $fareweightPerPiece . $fareweightUnit;
                                                    }
                                                    if(($farecode == "CHNGBL") || (($farecode == "REFUND"))){
                                                        $fareweightPerPiece= $farevals['conditions'][0]['properties']['fee'];
                                                    $fareweightPerPiece .= $farevals['conditions'][0]['properties']['currency'];

                                                        if(empty($fareweightPerPiece)){
                                                            $fareweightPerPiece = "No accurate Fee values from Airline";
                                                        }
                                                    // $farecostType   =  $fareweightPerPiece . $fareweightUnit;
                                                    }
                                                    
                                                ?>
                                                    <tr>
                                                    <td><?php echo $farename ;?></td>
                                                    <td><?php echo $fareAppl; ?></td>
                                                        
                                                        <td><?php echo $faremessage; ?></td>
                                                    <!--  <td><?php echo $farecostType ." ".$fareweightPerPiece; ?></td> -->
                                                    </tr>
                                                <?php
                                                } //eof farevals
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- =========================== -->
                                        
                                    <?php
                                        
                                    } //eof if

                                    } //eo foreach atainfo
                            


                                //=================================
                                ?>
                                            <!-- ============================ -->
                                        <?php
                                    }
                                    
                                }
                                    ?>


                                    <!-- <div class="mb-3 bdr-b">
                                <h6 class="text-left fw-700">Baggage Details</h6>
                                <ul class="fs-13">
                                    <li class="">
                                        <ul class="row align-items-center pt-3 pb-3">
                                            <!-- <li class="col-md-1 mb-md-0 mb-2">
                                                <img src="images/emirates-logo.png" alt="">
                                            </li> -->
                                    <!-- <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                <strong>Emirates</strong>
                                                <span class="uppercase-txt">cok <span
                                                        class="right-arrow-small arrow-000000"></span> dxb</span>
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
                                        
                                            <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                <strong>Emirates</strong>
                                                <span class="uppercase-txt">cok <span
                                                        class="right-arrow-small arrow-000000"></span> dxb</span>
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
                            </div> -->
                                    <div class="table-responsive">
                                        <h6 class="text-left fw-700">Traveller List</h6>
                                        <table class="table table-bordered white-bg text-left fs-14" style="min-width: 800px;">
                                            <thead>
                                                <tr class="dark-blue-bg white-txt">
                                                    <th>Title</th>
                                                    <th>Traveller Name</th>
                                                    <!-- <th>Age group</th> -->
                                                    <th>Ticket No.</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($passengerDetail as  $passengerDetails) {
                                                    $pssenger = $passengerDetails['Passenger']
                                                ?>
                                                    <tr>
                                                        <td><?php echo $pssenger['PaxName']['PassengerTitle'] ?></td>
                                                        <td><?php echo $pssenger['PaxName']['PassengerFirstName'] . " " . $pssenger['PaxName']['PassengerLastName'] ?></td>

                                                        <td>
                                                            <?php echo ($pssenger['TicketStatus'] == "Ticketed") ? $passengerDetails['ETickets'][0]['ETicketNumber'] : "" ?>
                                                        </td>
                                                        <td><?php echo $pssenger['TicketStatus'] ?></td>

                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Extra services ===== -->
                                    <?php if(!empty($tripDetailsExtraServices)){ ?>
                                    <div class="table-responsive">
                                        <h6 class="text-left fw-700">Extra Services</h6>
                                        <table class="table table-bordered white-bg text-left fs-14" style="min-width: 800px;">
                                            <thead>
                                                <tr class="dark-blue-bg white-txt">
                                                    <th>Type</th>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                    <th>Eligibility</th>
                                                    <th>CheckInType</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($tripDetailsExtraServices as  $extraSerDetails) {
                                                    $extraType = $extraSerDetails['Type'];
                                                    $extraDescription = $extraSerDetails['Description'];
                                                    $Behavior = $extraSerDetails['Behavior'];
                                                    $extraAmnt = $extraSerDetails['ServiceCost']['Amount']." ".$extraSerDetails['ServiceCost']['CurrencyCode'];
                                                    //=======
                                                    if($Behavior == "PER_PAX"){
                                                        $eligibility    =   "Extra service applicable for each passenger for entire trip Oneway / Return.";
                                                    }
                                                    elseif($Behavior == "PER_PAX_INBOUND"){
                                                        $eligibility    =   "Extra service is applicable for each passenger for Inbound flights.";
                                                        
                                                    }
                                                    elseif($Behavior == "PER_PAX_OUTBOUND"){
                                                        $eligibility    =   "Extra service is applicable for each passenger for Oubound flights";
                                                        
                                                    }
                                                    elseif($Behavior == "GROUP_PAX"){
                                                        $eligibility    =   "Extra service applicable for all the passengers in a Booking for entire trip Oneway / Return.";
                                                        
                                                    }
                                                    elseif($Behavior == "GROUP_PAX_INBOUND"){
                                                        $eligibility    =   "Extra service applicable for all the passengers in a booking for Inbound flights.";
                                                        
                                                    }
                                                    elseif($Behavior == "GROUP_PAX_OUTBOUND"){
                                                        $eligibility    =   "Extra service applicable for all the passengers in a booking for Outbound flights.";
                                                        
                                                    }
                                                

                                                    //===========
                                                    $CheckInType = $extraSerDetails['CheckInType'];
                                                ?>
                                                    <tr>
                                                        <td><?php echo $extraType; ?></td>
                                                        <td><?php echo $extraDescription;?></td>
                                                        <td>
                                                            <?php echo $extraAmnt; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $eligibility; ?>
                                                        </td>
                                                        <td><?php echo $CheckInType;?></td>

                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php } ?>
                                    <!-- extra services end  ==================== -->
                                    <div class="row fs-13 mb-3">
                                        <div class="col-12">
                                            <h6 class="text-left fw-700">Fare Details</h6>
                                        </div>
                                        <div class="col-md-5 mb-md-0 mb-3">
                                            <ul>
                                                <!-- <li class="d-flex justify-content-between p-1 bdr-b">
                                            <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in ₹)</span></strong>
                                            <span>1 adult</span>
                                        </li>
                                        <li>
                                            <ul class="bdr-b">
                                                <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li>
                                                <li class="d-flex justify-content-between p-1"><span>Adult (38748x1)</span><span>38748</span></li>
                                                <li class="d-flex justify-content-between p-1"><span>Airline Charges &amp; Taxes</span><span>5070</span></li>
                                                <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li>
                                            </ul>
                                            <ul class="bdr-b">
                                                <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li>
                                                <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Thomas Cook Charges</span><span>0</span></li>
                                            </ul>
                                        </li> -->
                                                <li class="d-flex justify-content-between dark-blue-bg white-txt p-1 mt-1">
                                                    <?php
                                                    $total = 0;
                                                    foreach ($tripDetails['TripDetailsPTC_FareBreakdowns'] as $fareDetails) {
                                                        $total += $fareDetails['TripDetailsPassengerFare']['TotalFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                                                    }
                                                    $stmtmarkup = $conn->prepare('SELECT * FROM markup_commission WHERE role_id = :role_id');
                                                    $stmtmarkup->execute(array('role_id' => 1));
                                                    $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
                                                    $totalFareAPI = $total;
                                                    // $markupPercentage = (($markup['commission_percentage'] / $totalFareAPI)*100);
                                                    $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                    ?>
                                                    <strong class="fw-600">Total Itinerary  Fare(Including Tax)</strong><strong>&#36; <?php echo number_format(round($total + $markupPercentage, 2), 2) ?></strong>
                                                </li>
                                                <?php if(!empty($tripDetailsExtraServices)){ 
                                                    $airline_markup_amnt   = number_format(round($total + $markupPercentage, 2), 2);
                                                    $extra_service_amnt    =  $totalPaid - (number_format(round($total + $markupPercentage, 2), 2) );?>
                                                <li class="">
                                            <div class="d-flex row justify-content-between dark-blue-bg white-txt p-1 mt-1 no-gutters">
                                                <div class="col-lg-6 col-md-12 col-sm-6 text-left mb-lg-0 mb-2">
                                                    <strong class="fw-600">Total Extra Services Amount</strong>
                                                </div>
                                                <div class="col-lg-6 col-md-12 col-sm-6 text-right">
                                                    <strong class="fw-600">&#36;</strong><strong><?php echo $extra_service_amnt; ?></strong>
                                                </div>
                                            </div>
                                        </li> 
                                        <li class="">
                                            <div class="d-flex row justify-content-between dark-blue-bg white-txt p-1 mt-1 no-gutters">
                                                <div class="col-lg-6 col-md-12 col-sm-6 text-left mb-lg-0 mb-2">
                                                    <strong class="fw-600">Total Paid Amount</strong>
                                                </div>
                                                <div class="col-lg-6 col-md-12 col-sm-6 text-right">
                                                    <strong class="fw-600">&#36;</strong><strong><?php echo $totalPaid; ?></strong>
                                                </div>
                                            </div>
                                        </li> 
                                        <?php } ?>
                                            </ul>
                                        </div>
                                        <!--fare rules start-->
                                        <?php
                                        
                                        // echo '<pre/>';
                                        // print_r($tripDetails['TripDetailsPTC_FareBreakdowns'][0]);
                                        
                                        $penalityInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['IsPenaltyDetailsAvailable'];
                                        $cancellation = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['AirRefundCharges'];
                                        // echo '<pre/>';
                                        // print_r($cancellation);
                                        
                                        // -------------------cancellation amount---------------
                                        //adding markup start ====
                                            //get cancel and date change markeup of end user/agent  from marktable and calculate the % and this % add to penaltyfare
                                            //Since 3 diff scenarios forcance markup taking cancel_type = 4,  meanse cancel on day after ticket issuance day
                                            //status 1 means table entry for  for cancel markup,2 means date change entry
                                            $Penaltymarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =4 AND status=1 AND role_id = :role_id');
                                            $Penaltymarkup->execute(array('role_id' => $user['role']));
                                            $markupPenaltyInfo = $Penaltymarkup->fetch(PDO::FETCH_ASSOC);
                                        if($cancellation['IsRefundableBeforeDeparture'] == 'Yes'){
                                            // echo '<pre/>';
                                            // print_r($cancellation['RefundCharges']);
                                            $hours = $cancellation['RefundCharges'][0]['ChargesBeforeDeparture'][0]['HoursBeforeDeparture'];
                                            $amounta = $cancellation['RefundCharges'][0]['ChargesBeforeDeparture'][0]['Charges'];
                                            //=============markup add ====
                                            if(!empty($amounta)){
                                            $markupPenaltyPercentage = ($markupPenaltyInfo['commission_percentage'] / 100) * $amounta;
                                                                                    
                                            $markupPenaltyPercentage    =    number_format(round($markupPenaltyPercentage));
                                                $totRefundDisplay =   $amounta+$markupPenaltyPercentage;
                                            }
                                            else{
                                            $totRefundDisplay ="Refundable amount is 0 from Airline";
                                            }

                                            //adding markup ends =======
                                            $ptp = $cancellation['RefundCharges'][0]['PassengerType'];
                                            $curr = $cancellation['RefundCharges'][0]['Currency'];
                                        }
                                        
                                        if($cancellation['IsRefundableAfterDeparture'] == 'Yes'){
                                            $amountb = $cancellation['RefundCharges'][0]['ChargesAfterDeparture'];

                                            //=============markup add ====
                                            if(!empty($amountb)){
                                            $markupPenaltyPercentageb = ($markupPenaltyInfo['commission_percentage'] / 100) * $amountb;
                                                                                    
                                            $markupPenaltyPercentageb    =    number_format(round($markupPenaltyPercentageb));
                                                $totRefundDisplayb =   $amounta+$markupPenaltyPercentageb;
                                            }
                                            else{
                                            $totRefundDisplayb ="Refundable amount is 0 from Airline";
                                            }

                                            $ptp = $cancellation['RefundCharges'][0]['PassengerType'];
                                            $currr = $cancellation['RefundCharges'][0]['Currency'];
                                        }

                                        
                                        // ---------------------------exchange amount---------------
                                        //Date change 
                                        // date change markeup of end user/agent  from marktable and calculate the % and this % add to penaltyfare
                                        //markup taking cancel_type = 0,  meanse not cancel and Date change meant
                                                                                                                //status 1 means table entry for  for cancel markup,2 means date change entry
                                        $DateChangemarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =0 AND status=2 AND role_id = :role_id');
                                        $DateChangemarkup->execute(array('role_id' =>  $user['role']));
                                        $DateChangemarkupInfo = $DateChangemarkup->fetch(PDO::FETCH_ASSOC);
                                                                                                            
                                        $exchange = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['AirExchangeCharges'];
                                        // echo '<pre/>';
                                        // print_r($exchange);
                                        
                                        if($exchange['IsExchangeableBeforeDeparture'] == 'Yes'){
                                            $amount1 = $exchange['ExchangeCharges'][0]['ChargeBeforeDeparture'];
                                            $markupDatechangePercentage = ($DateChangemarkupInfo['commission_percentage'] / 100) * $amount1;
                                            $markupDatechangePercentage    =    number_format(round($markupDatechangePercentage));
                                            $totDisplayDate1 =   $amount1+$markupDatechangePercentage;
                                        }
                                        else{
                                            $totDisplayDate1 =  "No value from Airline";
                                        }
                                        if($exchange['IsExchangeableAfterDeparture'] == 'Yes'){
                                            $amount2 = $exchange['ExchangeCharges'][0]['ChargesAfterDeparture'];
                                            $markupDatechangePercentage = ($DateChangemarkupInfo['commission_percentage'] / 100) * $amount2;
                                            $markupDatechangePercentage    =    number_format(round($markupDatechangePercentage));
                                            $totDisplayDate2 =   $amount2+$markupDatechangePercentage;
                                        }
                                        else{
                                            $totDisplayDate2 =  "No value from Airline";
                                        }
                                        
                                        
                                            $typ = $exchange['ExchangeCharges'][0]['PassengerType'];
                                            $currr = $exchange['ExchangeCharges'][0]['Currency'];
                                        ?>
                                        <div class="col-md-7">
                                        <ul>
                                            <li class="d-flex align-items-baseline p-1 bdr-b">
                                                <strong class="fs-14 fw-600">Penality Informations </strong>
                                                <?php if($penalityInfo == '1'){
                                                    echo '<span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>';
                                                }else{
                                                    echo '<span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">NOT Refundable</span>';
                                                } ?>
                                            </li>
                                            <ul>
                                                <li class="d-flex justify-content-between p-1 mt-1">
                                                    <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                    <span class="uppercase-txt"><?php echo $tripDetails['Origin']. '-' .$onewaysegmentLast['ArrivalAirportLocationCode']; ?></span>
                                                </li>
                                                <li class="text-left">
                                                    <table class="w-100">
                                                        <tbody><tr class="bdr">
                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee - <?php echo $hours. ' Hours before departure'; ?> ( <?php print_r($curr); ?>)</td>
                                                            <td class="p-1"><?php 
                                                            if($curr == 'USD'){ echo $ptp . ': $' . $totRefundDisplay;}else{ echo '';  } ?>
                                                            </td>
                                                        </tr>
                                                        <?php  if($cancellation['IsRefundableAfterDeparture'] == 'Yes'){ ?>
                                                        <tr class="bdr">
                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee - After departure (<?php print_r($curr); ?>)</td>
                                                            <td class="p-1"><?php 
                                                            if($curr == 'USD'){ echo $ptp . ': $' . $totRefundDisplayb;}else{ echo '';  } ?>
                                                            </td>
                                                        </tr>
                                                        <?php } ?>
                                                        <!--<tr class="bdr">-->
                                                        <!--    <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>-->
                                                        <!--    <td class="p-1">₹ 500</td>-->
                                                        <!--</tr>-->
                                                    </tbody></table>
                                                </li>
                                            </ul>
                                        <ul>
                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                <span class="uppercase-txt"><?php echo $tripDetails['Origin']. '-' .$onewaysegmentLast['ArrivalAirportLocationCode']; ?></span>
                                            </li>
                                            <li class="text-left">
                                                <table class="w-100">
                                                    <tbody><tr class="bdr">
                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee - Before departure (<?php print_r($currr); ?>)</td>
                                                        <td class="p-1"><?php
                                                        if($currr == 'USD'){ echo $typ . ': $' . $totDisplayDate1;}else{ echo '';  } ?></td>
                                                    </tr>
                                                    <?php  if($exchange['IsExchangeableAfterDeparture'] == 'Yes'){ ?>
                                                    <tr>
                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee - After departure (<?php print_r($currr); ?>)</td>
                                                        <td class="p-1"><?php 
                                                        if($currr == 'USD'){ echo $typ . ': $' . $totDisplayDate2;}else{ echo '';  } ?>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    <!--<tr class="bdr">-->
                                                    <!--    <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>-->
                                                    <!--    <td class="p-1">₹ 500</td>-->
                                                    <!--</tr>-->
                                                </tbody></table>
                                            </li>
                                        </ul>
                                        <ul>
                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                <strong class="fs-13 fw-600">Baggage Information<span class="fw-400">(per passenger)</span></strong>
                                                <span class="uppercase-txt"><?php echo $tripDetails['Origin']. '-' .$onewaysegmentLast['ArrivalAirportLocationCode']; ?></span>
                                            </li>
                                            <li class="text-left">
                                                <table class="w-100">
                                                    <tbody><tr class="bdr">
                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">cabinBaggage</td>
                                                        <td class="p-1"><?php
                                                    echo $cabinBaggage; ?></td>
                                                    </tr>
                                                
                                                    <tr>
                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">CheckInBaggage</td>
                                                        <td class="p-1"><?php 
                                                    echo $baggageInfo; ?>
                                                        </td>
                                                    </tr>
                                                
                                                    <!--<tr class="bdr">-->
                                                    <!--    <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>-->
                                                    <!--    <td class="p-1">₹ 500</td>-->
                                                    <!--</tr>-->
                                                </tbody></table>
                                            </li>
                                        </ul>

                                        </ul>    
                                </div>
                                <!--fare rules end-->
                                    </div>
                                    <div class="row fs-13 mb-3">
                                <div class="col-12">
                                    <h6 class="text-left fw-700">Contact Details</h6>
                                </div>
                                
                                <div class="col-lg-7 col-12 text-left">
                                    <div class="row mb-2">
                                        <div class="col-3">Name:</div>
                                        <div class="col-9"><?php echo $bookingData['contact_first_name'] . " " . $bookingData['contact_last_name']; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-3">Phone:</div>
                                        <div class="col-9"><?php echo $bookingData['contact_phonecode'] . " " .  $bookingData['contact_number'] ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-3">Email:</div>
                                        <div class="col-9"><?php echo $bookingData['contact_email'];?></div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-row mb-3">
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <a href="cancel_user.php?booking_id=<?php echo $bookingId;?>" class="btn btn-typ3 fs-14 w-100">Void/Cancel </a>
                                    <small>Usually within 24 hours</small>
                                </div>
                                
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <a href="dashboard-flight-reschedule-details.html" class="btn btn-typ3 fs-14 w-100">Reschedule</a>
                                    <small>Anytime</small>
                                </div>
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <a href="cancel.php?booking_id=<?php echo $bookingId;?>" class="btn btn-typ3 fs-14 w-100">Refund Amount</a>
                                    <small>Usually after 24 hours</small>
                                </div>
                                <!-- <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <button id="downloadInvoice" class="btn btn-typ3 fs-14 w-100">Download Invoice</button>
                                </div> -->
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <input type="hidden" id="bookingid" value="<?php echo $bookingId?>">
                                    <button id="downloadButton" class="btn btn-typ3 fs-14 w-100">Download Ticket</button>
                                    <small>Anytime</small>
                                    <!-- <button id="downloadButton">Download Ticket</button> -->
                                </div>
                                <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                                
                            <!-- <form id="ticketForm" action="" method="POST"> -->
                                    <input type="hidden" id="bookingid" value="<?php echo $bookingId?>">
                                    <!-- <button type="submit" id="send-ticket-button" class="btn btn-typ3 fs-14 w-100">Send Ticket</button> -->
                                    <!-- <button id="downloadButton">Download Ticket</button> -->
                                </div>
                            <!-- </form> -->
                            </div>
                                    <!-- <div class="row mb-3">
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <button class="btn btn-typ3 fs-14 w-100">Cancel Flight</button>
                                </div>
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <a href="dashboard-flight-reschedule-details.html" class="btn btn-typ3 fs-14 w-100">Reschedule</a>
                                </div>
                                <div class="col-lg-3 col-sm-6 mb-lg-0 mb-2">
                                    <button class="btn btn-typ3 fs-14 w-100">Download Invoice</button>
                                </div>
                                <div class="col-lg-3 col-sm-6">
                                    <button class="btn btn-typ3 fs-14 w-100">Download Ticket</button>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Modal -->
    
        <div class="modal reg-log-modal" id="ForgotPasswordModal" tabindex="-1" role="dialog"
            aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row justify-content-center">
                            <div class="col-lg-7 col-12">
                                <form>
                                    <div class="form-title mb-3 fw-500 text-center">Forgot Password?</div>
                                    <p class="fs-13 fw-300 dark-blue-txt text-center">Enter the e-mail address associated
                                        with the account.
                                        We'll e-mail a link to reset your password.</p>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <input type="email" class="form-control" id="RegisterInputEmail1"
                                                    aria-describedby="emailHelp" placeholder="Email">
                                            </div>
                                            <div class="form-group">
                                                <button type="submit"
                                                    class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Sent Ticket Popup -->
        <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="responseModalLabel">Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backup();"></button>
                    </div>
                    <div class="modal-body" id="responseMessage">
                        <!-- The message will be injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="backup();">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="scrollToTop"><span>Go Up</span></div>
        
        <script>

            $(".text-below-button").click(function () {
                $(this).parents('.modal').modal('hide');
            });
            $(".forgot-passward > button").click(function () {
                $(this).parents('.modal').modal('hide');
            });

            $('#FlightSearchLoading').modal({
                show: false
            })


            /**************Scroll To Top*****************/
            $(window).on('scroll', function () {
                if (window.scrollY > window.innerHeight) {
                    $('#scrollToTop').addClass('active')
                } else {
                    $('#scrollToTop').removeClass('active')
                }
            })

            $('#scrollToTop').on('click', function () {
                $("html, body").animate({ scrollTop: 0 }, 500);
            })

        //Download ticket code
            // JavaScript code to handle the button click event
            document.getElementById("downloadButton").addEventListener("click", function() {
                // Redirect to the PHP script to initiate the download
                const inputValue = document.getElementById("bookingid").value;
                window.location.href = "ticket_testlatest.php?value=" + encodeURIComponent(inputValue);
            });
            //download Invoice
            document.getElementById("downloadInvoice").addEventListener("click", function() {
                // Redirect to the PHP script to initiate the download
                const inputValue = document.getElementById("bookingid").value;
                window.location.href = "invoice.php?value=" + encodeURIComponent(inputValue);
            });
            $("#send-ticket-button").click(function() {
                // Get the booking ID from the hidden input field
                var bookingId = document.getElementById("bookingid").value;
                // Make the AJAX request using jQuery
                $.ajax({
            url: "ticket_send.php", // Replace with the actual server endpoint URL
            type: "POST", // Or "GET" depending on your server setup
            data: { bookingId: bookingId }, // Data to send to the server
            success: function(response) {
                if (response == 'success') {
                    // Show the success message in the modal
                    $("#responseMessage").text("Ticket sent successfully!");
                } else {
                    // Show the error message in the modal
                    $("#responseMessage").text("There is some problem sending the Ticket!");
                }
                // Show the modal
                $("#responseModal").modal('show');
            },
            error: function(xhr, status, error) {
                console.log("An error occurred while sending the ticket:");
                console.log(xhr.responseText);
                // Show the error message in the modal
                $("#responseMessage").text("An unexpected error occurred. Please try again later.");
                // Show the modal
                $("#responseModal").modal('show');
            }
        });
            });
        
        function backup(){
            window.location.href="flight-booking-details";
        }
        </script>
        <?php
    }
} else {?>
    <style>
    .bodycontant {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 70vh;
        background: url('images/home-banner1.jpg') center center/cover no-repeat; /* Use your background image here */
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .content {
        position: relative;
        z-index: 2;
        max-width: 600px;
        padding: 20px;
        padding: 20px;
        background: #121e7e;
        border-radius: 10px;
    }
    .content h1 {
        font-size: 48px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    /* Subtext Styling */
    .content p {
        font-size: 18px;
        color: #fff;
        margin-bottom: 30px;
    }

    /* Button Styling */
    .content .btn {
        display: inline-block;
        padding: 15px 30px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    .content .btn:hover {
        background-color: #121E7E;
    }
    </style>

    <div class="container-jumbotron">
        <div class="bodycontant">
            <div class="content">
                <?php
                    $firstPart = substr($bookingData['contact_email'], 0, 5);
                    $lastPart = substr($bookingData['contact_email'], -7);
                    $maskedEmail = $firstPart . "********" . $lastPart;
                ?>
                <p>We have sent a token for booking management to your registered email(<?php echo $maskedEmail ;?>) address.</p>
                <form id="tokenForm" class="d-flex align-items-center w-100">
                    <input type="number" name="tokenManagement" required placeholder="Enter Token" class="form-control flex-grow-1" style="flex: 0 0 67%;">
                    <button type="submit" class="btn btn-typ7 p-2 ml-3" style="flex: 0 0 30%;">Submit</button>
                </form>

            </div>
        </div>
    </div>
    <?php
        
        if( $bookingData['manage_booking_token'] == "" || $bookingData['manage_booking_token'] == NULL ) {
            $token = substr(strval(random_int(1000000, 9999999)), 0, 7);
            $updateToken = $conn->prepare('UPDATE temp_booking SET manage_booking_token = :manage_booking_token WHERE id = :id');
            $updateToken->bindParam(':manage_booking_token', $token);
            $updateToken->bindParam(':id', $bookingData['id']);
            $updateToken->execute();

            $toEmail = $bookingData['contact_email'];
            $subject = "Your Booking Management Token - ".$token;
            $messageData = '<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    </head>
                    <body style="font-family: Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px;">
                    
                        <table width="100%" bgcolor="#ffffff" cellpadding="10" cellspacing="0" border="0" style="max-width: 600px; margin: auto; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="text-align:center;padding-bottom:15px;padding-top:15px">
                                    <h2 style="margin-top:0;margin-bottom:0">
                                        <img src="https://bulatrips.com/images/Image-Logo-vec.png" alt="Bulatrip" title="Bulatrip" style="height: 50px;margin-top: 20px;margin-bottom: 20px;" class="CToWUd" data-bit="iit">
                                    </h2>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="background-color: #0000ff; color: #ffffff; padding: 15px; font-size: 20px; font-weight: bold;">
                                    Booking Management Token
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px; color: #333333; font-size: 16px;">
                                    <p>Dear <strong>'.$bookingData['contact_first_name']." ".$bookingData['contact_last_name'].'</strong>,</p>
                                    <p>To manage your booking, please use the verification token provided below:</p>
                                    <p style="text-align: center; font-size: 22px; font-weight: bold; color: #007bff; background-color: #f1f1f1; padding: 10px; border-radius: 5px;">
                                        '.$token.'
                                    </p>
                                    <p>Please enter this token on the booking management page to proceed.</p>
                                    <p>If you did not request this, please ignore this email.</p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="background-color: #f8f9fa; color: #555555; font-size: 14px; padding: 10px;">
                                    Best regards, <br>
                                    <a href="https://bulatrips.com" style="color: #007bff; text-decoration: none;">Visit our website</a>
                                </td>
                            </tr>
                        </table>
                    </body>
                    </html>';
                    $headers="";
                    confirmationMail($toEmail, $subject, $messageData,$headers);
        }
}
require_once("includes/login-modal.php");
require_once("includes/footer.php");
?>

<script>
    $("#tokenForm").submit(function(event) {
            event.preventDefault();
            var tokenValue = $("input[name='tokenManagement']").val();
            if (tokenValue === "") {
                Swal.fire({
                    title: "Token is required!",
                    text: "please enter token which is shared by the registered email address.",
                    icon: "error",
                    confirmButtonText: "Close",
                    confirmButtonColor: "#f57c00",
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: "includes/ajax",
                data: { tokenManagement: tokenValue, cookieName:"<?php echo $cookie_name;?>" },
                success: function(response) {
                    if( response == 1 ) {
                        Swal.fire({
                            title: "Token Verified Successfully!",
                            text: "Your token has been verified successfully. You can now proceed with managing your booking automatically.",
                            icon: "success",
                            confirmButtonText: "Close",
                            confirmButtonColor: "#f57c00",
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            title: "Invalid Token Entered",
                            text: "The token you entered is incorrect. Please check your email and try again.",
                            icon: "error",
                            confirmButtonText: "Close",
                            confirmButtonColor: "#f57c00",
                        });
                    }
                }
            });
        });
</script>