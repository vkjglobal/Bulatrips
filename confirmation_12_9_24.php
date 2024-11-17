<?php
session_start();
if (!isset($_SESSION['user_id'])) {
?>
    <script>
        window.location = "index.php"
    </script>    
<?php
exit;
}
$userEmail  =   $_SESSION['email'];
//echo "<pre/>";print_r($_SESSION);exit;
 error_reporting(0);
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
$bookingId = $_GET['bookingid'];
$stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid and user_id = :userid');

$stmtbookingid->execute(array('bookingid' => $bookingId, 'userid' => $_SESSION['user_id']));
$bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
//echo "<pre/".$bookingId;print_r($bookingData);exit;
$bookingStatus = $bookingData['booking_status'];
  $totalPaid =   $bookingData['total_paid'];
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
       // print_r($tripDetails);

        $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus,ticket_status = :ticketStatus,ticket_time_limit = :ticketTimeLimit,booking_date = :bookingDate,void_window =:voidWindow WHERE id = :id');

        // Set the values
        if(isset($tripDetails['BookingStatus'])){
            $bookingStatus = $tripDetails['BookingStatus'];
        }
        // print_r( $bookingStatus );die();
        $ticketTimeLimit = $tripDetails['TicketingTimeLimit'];
        $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
        $ticketStatus = $tripDetails['TicketStatus'];
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
if (!empty($responseData['Data']['Errors'])) { 
?>
    <div class=" container">
        <?php echo $responseData['Message']; ?>
        <?php
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
<?php }
else if((empty($responseData['Success'])) || (!$responseData['Success']))  { 
   //   echo "yyyyy";
    //  var_dump($responseData['sucess']); exit;?>
    <div class=" container">

        <?php
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
<?php }
elseif(empty($responseData)){
     ?>
    <div class=" container">
             <?php
                   $Errmessage = "No results received to show here .Please search again or check with your dashboard Booking details"; 

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
}
else { 

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
  //  echo "<pre/>";
  // print_r($onewaysegmentLast);
   // echo "***********************";
 //  print_r($returnsegmentLast); exit;
  // $toEmail = "no-reply@bulatrips.com";
    $toEmail = $userEmail;
   // echo $toEmail;exit;
    $subject = "Booking Confirmation";
    $messageData = '
        <html>
        <body>
        <table style="width:100%">
            <tbody>
                <tr>
                    <td>
                        <center>
                            <table style="width:80%;margin:0 auto">
    
                                <tbody>
                                    <tr>
                                        <td style="text-align:center;padding-bottom:15px;padding-top:15px">
                                            <h2 style="margin-top:0;margin-bottom:0">
                                                <img width="125" height="30"
                                                    src="https://bulatrips.com/images/bulatrips-logo.png"
                                                    alt="Bulatrip" title="Bulatrip"
                                                    style="height:30px;width:125px;display:inline-block;margin-top:0;margin-bottom:0"
                                                    class="CToWUd" data-bit="iit">
                                            </h2>
                                        </td>
                                    </tr>
    
    
                                    <tr>
                                        <td bgcolor="#ffffff" style="padding-top:20px;text-align:center">
    
                                            <div width="100%"
                                                style="max-width:480px;padding:5pt 0;background-color:#eff5fc;border-radius:10px;margin:0 auto;width:calc(100% - 32px);margin-bottom:24px">
                                                   </div>
    
    
    
                                            <div align="center" style="padding:0 10px;padding-bottom:5px">
                                                <h1
                                                    style="font-family:Arial,sans-serif;color:#000000;font-size:36px;letter-spacing:-0.5px;text-align:center;margin-top:0;margin-bottom:0">
                                                    Your booking is confirmed.
                                                </h1>
                                            </div>
    
                                            <table width="355" style="width:355px;margin-left:auto;margin-right:auto">
                                                <tbody>
                                                    <tr>
                                                        <td>
    
                                                            <p
                                                                style="font-family:Arial,sans-serif;color:#000000;font-size:20px;text-align:center;margin-bottom:0">
                                                                You are flying to '.$onewaysegmentLast['ArrivalAirportLocationCode'].'!
                                                            </p>
    
                                                            <p
                                                                style="font-family:Arial,sans-serif;font-size:15px;text-align:center;padding-bottom:20px;border-bottom:1px solid #cccccc;margin:0 auto;margin-top:20px;margin-bottom:0">
                                                                Trip ID:
                                                                   <a 
                                                                    href=""
                                                                    target="_blank"
                                                                    data-saferedirecturl="">
                                                                    <strong>'.$bookingId.'</strong></a>
    
                                                            </p>
    
    
                                                            <div align="center" style="padding:0 0 20px 0;margin-top:20px">
                                                                <p
                                                                    style="margin-top:0;margin-bottom:0;font-family:Arial,sans-serif;font-weight:bold;color:#333333;font-size:18px;line-height:22px">
                                                                    '.$tripDetails['Origin'] .'→ '.$onewaysegmentLast['ArrivalAirportLocationCode'].'</p>';
                                                                    $datetime = $itinerariesDetail[0]['DepartureDateTime'];
                                                                    list($date, $time) = explode("T", $datetime);
                                                                    $date=date("d F Y", strtotime($date));
                                                                    $arrivaldatetime=  $onewaysegmentLast['ArrivalDateTime'];
                                                                    list($datearri, $timearri) = explode("T", $arrivaldatetime);
                                                                    $datearri=date("d F Y", strtotime($datearri));

                                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                    $stmtlocation->execute(array('airport_code' => $onewaysegment[0]['DepartureAirportLocationCode']));
                                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                    $stmtlocationArrival = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                    $stmtlocationArrival ->execute(array('airport_code' => $onewaysegmentLast['ArrivalAirportLocationCode']));
                                                                    $airportLocationArrival  = $stmtlocationArrival ->fetch(PDO::FETCH_ASSOC);
                                                                    $baggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][0];
                                                                    $cabinbaggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][0];

                                                                    // Step 1: Convert datetime strings to DateTime objects
                                                                    $datetime1 = new DateTime($datetime);
                                                                    $datetime2 = new DateTime($arrivaldatetime);

                                                                    // Step 2: Calculate the time difference between the two DateTime objects
                                                                    $time_difference = $datetime1->diff($datetime2);

                                                                    // Step 3: Extract hours and minutes from the time difference

                                                                    $hours = $time_difference->days * 24 + $time_difference->h;

                                                                    // Step 4: Extract remaining minutes
                                                                    $minutes = $time_difference->i;


    
                              $messageData.=                                  '<p
                                                                    style="margin-top:0;margin-bottom:0;font-family:Arial,sans-serif;font-weight:normal;color:#9b9b9b;font-size:15px;line-height:20px;padding:0 0 10px 0">
                                                                   
                                                                    '.$date.'</p>
    
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0"
                                                                    style="padding:0 10px 0 10px;width:100%">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3" style="padding-bottom:15px">
                                                                                <table border="0" cellspacing="0"
                                                                                    cellpadding="0">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td align="left" rowspan="2"
                                                                                                style="padding-right:5px">
                                                                                              
                                                                                            </td>
    
                                                                                            <td align="left"
                                                                                                style="font-family:Arial,sans-serif;font-size:12px;line-height:12px;color:#444444">
                                                                                                '.$onewaysegment[0]['OperatingAirlineCode'].'
                                                                                            </td>
                                                                                            <td>
                                                                                                <span
                                                                                                    style="width:58px;font-size:10px;font-weight:bold;font-stretch:condensed;letter-spacing:normal;color:#fff;font-style:normal;font-family:HelveticaNeue;height:16px;margin:0 0 0 4px;padding:2px 8px;border-radius:10px;background-color:#37404e">'.$cabin_class_text.'
                                                                                                    </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td align="left"
                                                                                                style="font-family:Arial,sans-serif;font-size:12px;line-height:12px;color:#444444">
                                                                                                '.$onewaysegment[0]['FlightNumber'].'
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
    
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="margin:0;text-align:right;font-size:21px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                   '.$onewaysegment[0]['DepartureAirportLocationCode'].'<b>
                                                                                   '.$time.'</b></p>
                                                                            </td>
                                                                            <td width="20%" style="text-align:center">
                                                                                <img align="center" width="20"
                                                                                    src="images/confirm-dur.png"
                                                                                    title="Time" alt="Time"
                                                                                    style="vertical-align:baseline;display:inline-block;width:20px"
                                                                                    class="CToWUd" data-bit="iit">
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="text-align:left;font-size:22px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    <b>'.$timearri.'</b> '.$onewaysegmentLast['ArrivalAirportLocationCode'].'</p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="text-align:right;font-size:13px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    '.$date.'</p>
                                                                            </td>
                                                                            <td width="20%"
                                                                                style="text-align:center;font-size:11px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    '.$hours.'h '.$minutes.'m</p>
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="font-family:Arial,sans-serif;text-align:left;font-size:13px">
                                                                                <p style="margin-top:0;margin-bottom:0">
                                                                                '.$datearri.'</p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="text-align:right;vertical-align:top;font-size:10px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0;font-size:11px;line-height:16px">
                                                                                    '.$airportLocation['airport_name'].'</p>
                                                                            </td>
                                                                            <td width="20%" style="text-align:center">
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="text-align:left;vertical-align:top;font-size:10px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0;font-size:11px;line-height:16px">
                                                                                    '.$airportLocationArrival['airport_name'].'</p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
    
    
    
    
    
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0"
                                                                    style="margin-top:20px;padding:0 10px 0 10px;width:100%">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td
                                                                                style="font-family:Arial,sans-serif;font-size:11px;line-height:15px">
                                                                                <span
                                                                                    style="font-family:Arial,sans-serif;font-weight:normal;color:#666666">Baggage
                                                                                    (per Adult/Child) – </span>
                                                                                <span
                                                                                    style="font-family:Arial,sans-serif;font-weight:normal;padding-left:2px;color:#000000">
                                                                                    Check-in: '. $baggageInfo.',
                                                                                    Cabin: '. $cabinbaggageInfo.'
    
    
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                       
                                                                       
                                                                        
                                                                        <tr>
                                                                            <td><br>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>';

                                                            if ($returnsegment) {
                                                                $datetimereturn = $returnsegment[0]['DepartureDateTime'];
                                                                list($datereturn, $timereturn) = explode("T", $datetimereturn);
                                                                $datereturn=date("d F Y", strtotime($datereturn));
                                                                $arrivaldatetimereturn=  $returnsegmentLast['ArrivalDateTime'];
                                                                list($datearrireturn, $timearrireturn) = explode("T", $arrivaldatetimereturn);
                                                                $datearrireturn=date("d F Y", strtotime($datearrireturn));
                                        
                                        
                                                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                $stmtlocation->execute(array('airport_code' => $returnsegment[0]['DepartureAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                $stmtlocationArrival = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                $stmtlocationArrival ->execute(array('airport_code' => $returnsegmentLast['ArrivalAirportLocationCode']));
                                                                $airportLocationArrival  = $stmtlocationArrival ->fetch(PDO::FETCH_ASSOC);
                                                                $count=count($onewaysegment);
                                                                $baggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][$count];
                                                                $cabinbaggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][$count];
                                        
                                                                $datetime1 = new DateTime($datetimereturn);
                                                                $datetime2 = new DateTime($arrivaldatetimereturn);
                                        
                                                                // Step 2: Calculate the time difference between the two DateTime objects
                                                                $time_difference = $datetime1->diff($datetime2);
                                        
                                                                // Step 3: Extract hours and minutes from the time difference
                                        
                                                                $hours = $time_difference->days * 24 + $time_difference->h;
                                        
                                                                // Step 4: Extract remaining minutes
                                                                $minutes = $time_difference->i;

      $messageData.=                                        '<div align="center" style="padding:0 0 20px 0;margin-top:20px">
                                                                <p
                                                                    style="margin-top:0;margin-bottom:0;font-family:Arial,sans-serif;font-weight:bold;color:#333333;font-size:18px;line-height:22px">
                                                                    '.$returnsegment[0]['DepartureAirportLocationCode'].' → '.$returnsegmentLast['ArrivalAirportLocationCode'].'</p>
    
                                                                <p
                                                                    style="margin-top:0;margin-bottom:0;font-family:Arial,sans-serif;font-weight:normal;color:#9b9b9b;font-size:15px;line-height:20px;padding:0 0 10px 0">
                                                                    '. $datereturn.'</p>
    
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0"
                                                                    style="padding:0 10px 0 10px;width:100%">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="3" style="padding-bottom:15px">
                                                                                <table border="0" cellspacing="0"
                                                                                    cellpadding="0">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td align="left" rowspan="2"
                                                                                                style="padding-right:5px">
                                                                                                <img src="https://ci4.googleusercontent.com/proxy/-EGpQDC-pTRN3jU27z8y-Zzb0Yc-RgQRBcn7nWuLlJrJ6IrDN0AxHM2YxhQHBN866kfmuA9EGEc4al7vES_8IivrgRs=s0-d-e1-ft#https://ui.cltpstatic.com/images/air_logos/QP.gif"
                                                                                                    width="24px;"
                                                                                                    height="24px;"
                                                                                                    title="Akasa Air"
                                                                                                    alt="Akasa Air"
                                                                                                    style="vertical-align:top;display:block"
                                                                                                    class="CToWUd"
                                                                                                    data-bit="iit">
                                                                                            </td>
    
                                                                                            <td align="left"
                                                                                                style="font-family:Arial,sans-serif;font-size:12px;line-height:12px;color:#444444">
                                                                                                '.$returnsegment[0]['OperatingAirlineCode'].'
                                                                                            </td>
                                                                                            <td>
                                                                                                <span
                                                                                                    style="width:58px;font-size:10px;font-weight:bold;font-stretch:condensed;letter-spacing:normal;color:#fff;font-style:normal;font-family:HelveticaNeue;height:16px;margin:0 0 0 4px;padding:2px 8px;border-radius:10px;background-color:#37404e">'.$cabin_class_text.'</span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td align="left"
                                                                                                style="font-family:Arial,sans-serif;font-size:12px;line-height:12px;color:#444444">
                                                                                                '.$returnsegment[0]['FlightNumber'].'
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
    
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="margin:0;text-align:right;font-size:21px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    '.$returnsegment[0]['DepartureAirportLocationCode'].'<br>'.$timereturn.'</b></p>
                                                                            </td>
                                                                            <td width="20%" style="text-align:center">
                                                                                <img align="center" width="20"
                                                                                    src="https://ci4.googleusercontent.com/proxy/OE7EDmZd24DLDuW1U0ikd1gt64M5pJ8jKsxL_HjypasX2Aibil4jAil7WMvd7GnoWtPTM7olyV8uCWmf8aYfsx7-hwaZyEpk=s0-d-e1-ft#https://ui.cltpstatic.com/images/mailers/duration.png"
                                                                                    title="Time" alt="Time"
                                                                                    style="vertical-align:baseline;display:inline-block;width:20px"
                                                                                    class="CToWUd" data-bit="iit">
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="text-align:left;font-size:22px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    <b>'.$timearrireturn.'</b> '.$returnsegmentLast['ArrivalAirportLocationCode'].'</p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="text-align:right;font-size:13px">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    '.$datereturn.'</p>
                                                                            </td>
                                                                            <td width="20%"
                                                                                style="text-align:center;font-size:11px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0">
                                                                                    '.$hours.'h '.$minutes.'m</p>
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="font-family:Arial,sans-serif;text-align:left;font-size:13px">
                                                                                <p style="margin-top:0;margin-bottom:0"> '.$datearrireturn.'</p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="40%"
                                                                                style="text-align:right;vertical-align:top;font-size:10px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0;font-size:11px;line-height:16px">
                                                                                    '.$airportLocation['airport_name'].'</p>
                                                                            </td>
                                                                            <td width="20%" style="text-align:center">
                                                                            </td>
                                                                            <td width="40%"
                                                                                style="text-align:left;vertical-align:top;font-size:10px;color:#777777">
                                                                                <p
                                                                                    style="font-family:Arial,sans-serif;margin-top:0;margin-bottom:0;font-size:11px;line-height:16px">
                                                                                    '.$airportLocationArrival['airport_name'].'</p>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
    
    
    
    
    
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0"
                                                                    style="margin-top:20px;padding:0 10px 0 10px;width:100%">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td
                                                                                style="font-family:Arial,sans-serif;font-size:11px;line-height:15px">
                                                                                <span
                                                                                    style="font-family:Arial,sans-serif;font-weight:normal;color:#666666">Baggage
                                                                                    (per Adult/Child) – </span>
                                                                                <span
                                                                                    style="font-family:Arial,sans-serif;font-weight:normal;padding-left:2px;color:#000000">
                                                                                    Check-in: '. $baggageInfo.',
                                                                                    Cabin: '. $cabinbaggageInfo.'
    
    
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                       
                                                                       
                                                                        <tr>
                                                                            <td><br>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>';
                                                            }
    
    
    
                                $messageData .=             '<div width="100%" align="center"
                                                                style="width:100%;margin-bottom:20px;border-top:1px solid #cccccc">
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0"
                                                                    style="width:100%;margin-top:20px;padding:0 10px 0 10px">
    
                                                                    <tbody>
                                                                        <tr>
                                                                            <td width="70%"
                                                                                style="text-align:left;font-family:Arial,sans-serif;color:#666666;font-size:11px">
                                                                                TRAVELLERS</td>
                                                                            <td width="10%"> </td>
                                                                            <td width="20%"
                                                                                style="text-align:left;font-family:Arial,Verdana,sans-serif;color:#666666;font-size:11px">
                                                                                PNR</td>
                                                                        </tr>';

                                                                        foreach($passengerDetail as  $passengerDetails) {
    
                       $messageData.=                                  '<tr>
                                                                            <td
                                                                                style="padding-top:10px;text-align:left;font-family:Arial,Verdana,sans-serif;font-size:15px;margin-bottom:0;margin-top:0">
                                                                                
                                                                                <span style="padding-left:10px">'.$passengerDetails['Passenger']['PaxName']['PassengerFirstName'].' '.$passengerDetails['Passenger']['PaxName']['PassengerLastName'].'</span>
                                                                            </td>
                                                                            <td>&nbsp;</td>
    
                                                                            <td
                                                                                style="text-align:left;font-family:Arial,sans-serif;font-size:15px;vertical-align:bottom;margin-bottom:0;margin-top:0">
                                                                                '.$onewaysegment[0]['AirlinePNR'].'</td>
                                                                        </tr>';
                                                                        }
                                                                        $total = 0;
                                                                        foreach ($tripDetails['TripDetailsPTC_FareBreakdowns'] as $fareDetails) {
                                                                            $total += $fareDetails['TripDetailsPassengerFare']['TotalFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                                                                        }
                                                                       
                                                                
                                   $messageData.=                 '</tbody>
                                                                </table>
                                                            </div>
    
    
    
    
    
                                                            <div
                                                                style="padding:20px 10px 0 10px;margin-bottom:20px;border-top:1px solid #cccccc">
                                                                <h1
                                                                    style="font-family:Arial,sans-serif;font-size:18px;color:#333333;text-align:center;margin-bottom:0;margin-top:0">
                                                                    Amount paid $ '.$totalPaid.' </h1>
                                                                
                                                            </div>
    
    
    
    
    
    
                                                            <div
                                                                style="font-family:Arial,sans-serif;font-size:15px;margin:0 auto">
                                                                
                                                            </div>
    
    
    
    
    
    
                                                        </td>
                                                    </tr>
    
    
    
    
                                                    
    
    
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
        </html>
        ';
        // $headers  = 'MIME-Version: 1.0' . "\r\n"
        // .'Content-type: text/html; charset=utf-8' . "\r\n"
        // .'From: ' . $toEmail . "\r\n";
        $headers="";

    // mail($toEmail, $subject, $messageData, $headers);
  // echo $toEmail.$subject. $messageData;exit;
   confirmationMail($toEmail, $subject, $messageData,$headers,$bookingId);
    
    
    
    ?>

    




    <section>
      
        <div class="container">
            <h2 class="title-typ2 my-4 text-center">Your booking is <?php echo  $bookingStatus; ?>.</h2>
            <div class="row my-4">
                <div class="col-12 text-center fw-700">
                    Booking Details  from airline  for the ordered ticket are as follows: 
                </div>
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
                                                <!-- <th>Age group</th> -->
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
                                                if (isset($_SESSION['user_id'])) {

                                                    $id = $_SESSION['user_id'];
                                                    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
                                                    $stmt->execute(array('id' => $id));
                                                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                                    $stmtmarkup->execute(array('role_id' => $user['role']));
                                                    $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
                                                } else {
                                                    $stmtmarkup->execute(array('role_id' => 1));
                                                    $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
                                                }
                                                $totalFareAPI = $total;
                                                // $markupPercentage = (($markup['commission_percentage'] / $totalFareAPI)*100);
                                                $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                ?>
                                                <strong class="fw-600">Total Paid Fare(Including Tax)</strong><strong>&#36; <?php echo number_format(round($total + $markupPercentage, 2), 2) ?></strong>
                                            </li>
                                            <!-- <li class="">
                                        <div class="d-flex row justify-content-between dark-blue-bg white-txt p-1 mt-1 no-gutters">
                                            <div class="col-lg-6 col-md-12 col-sm-6 text-left mb-lg-0 mb-2">
                                                <strong class="fw-600">Paid via: <span>*************521</span></strong>
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-6 text-right">
                                                <strong class="fw-600">Total Fare:&nbsp;&nbsp;</strong><strong>₹ 43,818</strong>
                                            </div>
                                        </div>
                                    </li> -->
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
                                                echo '<span class="uppercase-txt white-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>';
                                            }else{
                                                echo '<span class="uppercase-txt white-txt green-bg border-radius-5 ml-2 pl-1 pr-1">NOT Refundable</span>';
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
                                            <div class="col-9"><?php echo $bookingData['contact_first_name'] . " " . $bookingData['contact_last_name'] ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-3">Phone:</div>
                                            <div class="col-9"><?php echo $pssenger['PhoneNumber'] ?></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-3">Email:</div>
                                            <div class="col-9"><?php echo $pssenger['EmailAddress'] ?></div>
                                        </div>
                                    </div>
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
    <?php
}
?>
 <!--Start -->
       <!-- Modal -->
        <div class="modal fade" id="errorModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeButton">
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center" id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button"  id="closeButton1" class="btn btn-secondary close" onclick="backhomepage()"  data-dismiss="modal">Close</button>
                       <!-- <button type="button" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Search Again</button> -->
                    </div>
                </div>
            </div>
        </div>
    <!--  End -->
    <!-- Modal -->
    <div class="modal reg-log-modal" id="LoginModal" tabindex="-1" role="dialog" aria-labelledby="LoginModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 fw-400" id="loginModalLongTitle">Welcome to the <strong class="fw-500">Travel website</strong></h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7 d-none d-lg-block">
                            <img src="images/login-bg.png" alt="">
                        </div>
                        <div class="col-lg-5 col-12">
                            <form>
                                <div class="form-title mb-3 fw-500">Login</div>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="loginInputEmail1" aria-describedby="emailHelp" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="loginInputPassword1" placeholder="Password">
                                    <div class="forgot-passward">
                                        <button type="button" class="fs-11" data-toggle="modal" data-target="#ForgotPasswordModal">Forgot password ?</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                                </div>
                                <button type="button" class="fs-14 text-below-button" data-toggle="modal" data-target="#RegisterModal">New User ? Click Here to <span class="fw-600">Register</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal reg-log-modal" id="RegisterModal" tabindex="-1" role="dialog" aria-labelledby="RegisterModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 fw-400" id="RegisterModalLongTitle">Welcome to the <strong class="fw-500">Travel website</strong></h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7 d-none d-lg-block">
                            <img src="images/register-bg.png" alt="">
                        </div>
                        <div class="col-lg-5 col-12">
                            <form>
                                <div class="form-title mb-3 fw-500">Let's get started!</div>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="RegisterInputEmail1" aria-describedby="emailHelp" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="RegisterInputPassword1" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="RegisterInputMobile" placeholder="+91  Mobile number">
                                </div>
                                <div class="form-group chkbx">
                                    <input type="checkbox" id="logintab" checked>
                                    <label for="logintab" class="fz-13 fw-400">
                                        <span class="chk-txt fs-13 fw-400">I Agree to <a href="">terms &
                                                conditions</a></span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Register</button>
                                </div>
                                <button type="button" class="fs-14 text-below-button" data-toggle="modal" data-target="#LoginModal">for existing user <span class="fw-600">Login</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <!--  <div class="modal reg-log-modal" id="ForgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
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
                                            <input type="email" class="form-control" id="RegisterInputEmail1" aria-describedby="emailHelp" placeholder="Email">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
   <!-- <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <form>
                                <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest
                                    fare for flights</div>
                                <div class="form-row flight-direction align-items-center justify-content-center mb-4">
                                    <strong class="col-md-5 text-md-right text-center mb-md-0 mb-2">Kochi</strong>
                                    <div class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                        <span class="oneway d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z" fill="#4756CB" />
                                            </svg>
                                        </span>
                                        <span class="return d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z" fill="#4756CB" />
                                            </svg>
                                        </span>
                                    </div>
                                    <strong class="col-md-5 text-md-left text-center mt-md-0 mt-2">Dubai</strong>
                                </div>
                                <div class="progress mb-5">
                                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="row justify-content-center mb-5">
                                    <div class="col-lg-8 col-md-10">
                                        <div class="row justify-content-between">
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z" fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Depart</div>
                                                    <div class="date">
                                                        <strong class="fw-500">11</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Friday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z" fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Return</div>
                                                    <div class="date">
                                                        <strong class="fw-500">19</strong>
                                                        <div>
                                                            Nov, 2022 <br>
                                                            Saturday
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="35" height="38" viewBox="0 0 35 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z" fill="#969696" />
                                                    </svg>
                                                </div>
                                                <div class="trvl-dtls text-center fw-300">
                                                    <div class="label">Traveller</div>
                                                    <div class="date">
                                                        <strong class="fw-500">01</strong>
                                                        <div>
                                                            1 Adult
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="fs-16 fw-300 text-center">
                                    This may take upto a minite
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
    <?php

    require_once("includes/footer.php");
    ?>
    <script>
    function backhomepage() {
            window.location = "index.php";
        }
    </script>