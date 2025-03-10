
<?php
error_reporting(1);
session_start();

require_once("includes/header.php");
require_once('includes/dbConnect.php');
include_once('includes/common_const.php');

$airport_depart = getAirPortLocationsByAirportCode($_SESSION['search_values']['airport'], $conn);
$airport_arrival = getAirPortLocationsByAirportCode($_SESSION['search_values']['arrivalairport'], $conn);


$url = "https://v6.exchangerate-api.com/v6/82190c2eeaf28578f89f52d7/latest/INR";
$response = file_get_contents($url);
$usd_converion_rate = 1;
if ($response !== false) {
    $data = json_decode($response, true); // Decode JSON to associative array
    $usd_converion_rate = $data['conversion_rates']['USD'];
} else {
    echo "Failed to retrieve data.";
}

if (!isset($_SESSION['user_id'])) {
?>
    <script>
       // window.location = "index.php"
    </script>
<?php

}
//---------
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//-------
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();
// echo '<pre/>';
// print_r($_SESSION);
// ----------------------------------------------------
// fetch session values
$revalidData = $_SESSION['revalidationApi'];
// $revalidData = json_decode($revalidDatavalue, true);
//==================================
$TotExtraServiceAmount =   $objBook->getExtraserviceAmoount($revalidData);

$TotExtraServiceAmount  =   number_format(round($TotExtraServiceAmount ,2),2);


//=============================================
 //echo '<pre/>';
// print_r($ExtraServiceAmount);exit;
$pricedItinerariesData = $revalidData['pricedItineraries'];
$pricedItineraries = json_decode($pricedItinerariesData, true);
 //echo '<pre/>'; print_r($pricedItineraries);

 if( isset($revalidData['custId']) && $revalidData['custId'] != '' ) {
    $user['role'] = $revalidData['custId'];
 } else if( isset($_SESSION['customer_role-id']) ) {
    $user['role'] = $_SESSION['customer_role-id'];
}

$baggageService = '0';
$mealService = '0';
if(isset($revalidData['baggageService1'])){
    $baggageService = $revalidData['baggageService1'];

    // Split the string into an array using '/'
    if(isset($baggageService)){
        $baggageData = explode('/', $baggageService);

    // Access each value
    $baggageid = $baggageData[0]; // 2
    $baggagetitle = $baggageData[1]; // Check-in baggage - up to 5kg 
    $baggageamount = $baggageData[2]; // 30.34
}
if(isset($revalidData['mealService1'])){
    $mealService = $revalidData['mealService1'];

    // Split the string into an array using '/'
    $mealData = explode('/', $mealService);

    // Access each value
    $mealid = $mealData[0]; // 11
    $mealtitle = $mealData[1]; // Veg lacto meal and beverage 
    $mealamount = $mealData[2]; // 4.81
}
}
// ---------------------------------------------------
// $pricedItineraries = $revalidData['Data']['PricedItineraries'];

// $stmtData = $conn->prepare('SELECT td.* FROM travellers_details as td JOIN temp_booking as tb ON tb.id = td.flight_booking_id WHERE tb.fare_source_code = :fscValue AND tb.user_id = :userId');

// $stmtData->execute(array('fscValue' => $_SESSION['fsc'],'userId' => $_SESSION['user_id']));
// $Data = $stmtData->fetch(PDO::FETCH_ASSOC);
// echo '<pre/>';
// print_r($Data);

?>
    <section class="bg-070F4E" style="margin-bottom: 18px;">
        <div class="container p-3">
            <div class="row justify-content-center">
                <div class="col-md-9">
                    <div class="steps-bar-title white-txt fw-500 text-md-center">Book your Flight in 4 Simple Steps</div>
                    <div class="process-wrap active-step4">
                        <div class="process-main">
                            <div class="row justify-content-center">
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-1"></div>
                                        <span class="process-label"><span class="position-relative">Flight Details<button></button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-2"></div>
                                        <span class="process-label"><span class="position-relative">User Registration<button></button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-3"></div>
                                        <span class="process-label"><span class="position-relative">Traveller Details<button></button></span></span>
                                    </div>
                                </div>
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-4"></div>
                                        <span class="process-label"><span class="position-relative">Payment</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section>
        
    <!-- BREADCRUMB STARTS HERE -->
    <section style="margin-bottom: 10px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumbs">
                            <li><a href="index.php" style="text-decoration: underline !important;">Home</a></li>
                            <li><a href="flights" style="text-decoration: underline !important;">Search Flights</a></li>
                            <li> Flight Details</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- BREADCRUMB STARTS HERE -->

        <div class="container">
            <div class="form-row">
                <div class="col-12">
                    <!-- <h2 class="title-typ2 mb-3 mb-lg-5">My Booking</h2> -->
                    <h2 class="title-typ2 mb-3 text-center">Flight Details</h2>
                    <div class="booking-step-container booking-step-1">
                        <?php
                        foreach ($pricedItineraries as $pricedItinerary) {
                            $originDestinations = $pricedItinerary['OriginDestinationOptions'];
                            $_SESSION['name-character-count'] = $pricedItinerary['PaxNameCharacterLimit'];
                        ?>

                            <div class="booking-step mb-4">
                                <div class="d-flex row justify-content-between pb-3 bdr-b">
                                    <div class="col-md-8 dark-blue-txt fw-500 mb-md-0 mb-2">
                                        <svg width="21" height="17" viewBox="0 0 21 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.012 7.59L4.502 0.518L6.433 0L13.384 6.42L18.646 5.01C19.0303 4.9071 19.4397 4.96107 19.7842 5.16004C20.1287 5.35902 20.3801 5.6867 20.483 6.071C20.5859 6.4553 20.5319 6.86474 20.333 7.20924C20.134 7.55375 19.8063 7.8051 19.422 7.908L4.45 11.918L3.674 9.02L3.915 8.955L6.382 11.4L3.756 12.104C3.54067 12.1617 3.31221 12.1459 3.10693 12.0589C2.90165 11.9719 2.73132 11.8189 2.623 11.624L0 6.898L1.449 6.51L3.915 8.955L9.012 7.589V7.59ZM2.534 14.958H18.534V16.958H2.534V14.958Z" fill="#A0A0A0" />
                                        </svg>
                                        <?php
                                        $originData = $originDestinations[0]['FlightSegments'][0]['DepartureAirportLocationCode'];
                                        $segmentCount = count($originDestinations[0]['FlightSegments']);
                                        $segmentCount -= 1;
                                        $destinationData = $originDestinations[0]['FlightSegments'][$segmentCount]['ArrivalAirportLocationCode'];
                                        $datetime = $originDestinations[0]['FlightSegments'][0]['DepartureDateTime'];
                                        //session set for passport expiry check
                                        
                                        $_SESSION['travel-depdate'] = $originDestinations[0]['FlightSegments'][0]['DepartureDateTime'];
                                        
                                        list($date, $time) = explode("T", $datetime);
                                        ?>
                                        <span class="ml-3"><?php echo $originData; ?> <span class="right-arrow-small arrow-121E7E mr-1">

                                            </span><?php echo $destinationData . " " . date("d F Y", strtotime($date)); ?> </span>
                                    </div>
                                    <div class="col-md-4 text-md-right fs-15 fw-300">
                                        <strong class="fw-500">
                                            Total Duration:
                                            <?php
                                            $origin_total_duration = 0;
                                            foreach ($originDestinations[0]['FlightSegments'] as $key => $origins) {
                                                if ($origins['LegIndicator'] == 0) {
                                                    $origin_total_duration += $origins['JourneyDuration'];
                                                }
                                            }
                                            echo convertMinutesToTimeFormat($origin_total_duration);
                                            ?>
                                        </strong>

                                    </div>
                                </div>
                                <div class="bdr-b">
                                    <?php

                                    foreach ($originDestinations as $index => $originDestination) {
                                        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                        $code = '%' . $pricedItinerary['ValidatingAirlineCode'] . '%';
                                        $stmtairline->bindParam(':code', $code);
                                        $stmtairline->execute();
                                        $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                        $flightSegments = $originDestination['FlightSegments'];
                                        // foreach ($flightSegments as $flightSegment) {
                                        if ($index == 0) {
                                            foreach ($flightSegments as $index => $flightSegment) {
                                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');


                                    ?>

                                                <ul class="form-row fs-15 mt-md-3 mt-2 mb-md-3 mb-2">
                                                    <li class="col-xl-1 col-md-2 text-center mb-md-0 mb-2">
                                                        <?php if ($airlineLocation['image']) { ?>
                                                            <img src="images/emirates-logo.png" alt="">
                                                        <?php

                                                        } else { ?>
                                                            <img src="images/no-image-icon-1.jpg" alt="" style="max-height: 75px;">
                                                        <?php

                                                        } ?>

                                                    </li>
                                                    <li class="col-md-2 mb-md-0 mb-2">
                                                        <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                                        Flight No - <?php echo $flightSegment['FlightNumber'] . " " . $flightSegment['CabinClassText'] ?>
                                                    </li>
                                                    <li class="col-xl-2 col-md-1"></li>
                                                    <li class="col-xl-6 col-md-7">
                                                        <div class="d-flex form-row justify-content-between align-items-center">
                                                            <div class="col-md-4 mb-md-0 mb-2 text-md-right">
                                                                <?php
                                                                $datetime = $flightSegment['DepartureDateTime'];
                                                                list($date, $time) = explode("T", $datetime);
                                                                $stmtlocation->execute(array('airport_code' => $flightSegment['DepartureAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                ?>
                                                                <strong class="fw-500 d-block"><?php echo $flightSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                <?php echo date("d F Y", strtotime($date)) . " " . $airportLocation['airport_name'] . " , " . $airportLocation['city_name'] ?>
                                                            </div>
                                                            <div class="col-md-3 mb-md-0 mb-2">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"></path>
                                                                    </svg>
                                                                    <?php
                                                                    $minutes = $flightSegment['JourneyDuration'];
                                                                    $hours = floor($minutes / 60);
                                                                    $remainingMinutes = $minutes % 60;
                                                                    echo $hours . " h  " . $remainingMinutes . " m";
                                                                    ?>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4 text-md-left">
                                                                <?php
                                                                $datetime = $flightSegment['ArrivalDateTime'];
                                                                list($date, $time) = explode("T", $datetime);
                                                                // echo date("d F Y", strtotime($date));
                                                                $stmtlocation->execute(array('airport_code' => $flightSegment['ArrivalAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                ?>
                                                                <strong class="fw-500 d-block"><?php echo $time . " " . $flightSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <?php
                                                if (isset($flightSegments[$index + 1])) {
                                                ?>
                                                    <ul class="form-row fs-15 mt-md-3 mt-2 mb-md-3 mb-2">
                                                        <li class="col-md-5"></li>
                                                        <li class="col-xl-6 col-md-7">
                                                            <div class="d-flex form-row justify-content-center align-items-center layover-duration pt-3 pb-3">
                                                                <div class="col-xl-4 col-md-5">
                                                                    <span>Layover :
                                                                        <?php
                                                                        $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $flightSegment['ArrivalDateTime']);
                                                                        $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $flightSegments[$index + 1]['DepartureDateTime']);
                                                                        $diff = $date1->diff($date2);

                                                                        // Get the difference in hours and minutes
                                                                        $hours = $diff->h;
                                                                        $minutes = $diff->i;
                                                                        echo $hours . " hr " . $minutes . " m ";
                                                                        ?>

                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                <?php
                                                }
                                                ?>
                                                <!-- <ul class="form-row fs-15 mt-md-3 mt-2 mb-md-3 mb-2">
                                        <li class="col-xl-1 col-md-2 text-center mb-md-0 mb-2">
                                            <img src="images/emirates-small-logo.png" alt="">
                                        </li>
                                        <li class="col-md-2 mb-md-0 mb-2">
                                            <strong class="fw-500 d-block">Emirates Airline</strong>
                                            Flight No. EK 317 Economy Boeing 77W
                                        </li>
                                        <li class="col-xl-2 col-md-1"></li>
                                        <li class="col-md-6">
                                            <div class="d-flex form-row justify-content-between align-items-center">
                                                <div class="col-md-4 mb-md-0 mb-2 text-md-right">
                                                    <strong class="fw-500 d-block">KCZ 11:45</strong> 
                                                    Fri, 18 Nov, 2022 Kma, Kochi
                                                </div>
                                                <div class="col-md-3 mb-md-0 mb-2">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"></path>
                                                        </svg> 
                                                        11hr 15m                                                       
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-md-left">
                                                    <strong class="fw-500 d-block">13:00 HND</strong>
                                                    Mon, 14 Nov, 2022 Dubai International, Dubai Terminal 3
                                                </div>
                                            </div>
                                        </li>
                                    </ul> -->
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            
                                            <hr> <div class="h5 mb-3">Return</div>
                                            <div class="d-flex row justify-content-between pb-3 bdr-b">
                                                <div class="col-md-8 dark-blue-txt fw-500 mb-md-0 mb-2">
                                                    <svg width="21" height="17" viewBox="0 0 21 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M9.012 7.59L4.502 0.518L6.433 0L13.384 6.42L18.646 5.01C19.0303 4.9071 19.4397 4.96107 19.7842 5.16004C20.1287 5.35902 20.3801 5.6867 20.483 6.071C20.5859 6.4553 20.5319 6.86474 20.333 7.20924C20.134 7.55375 19.8063 7.8051 19.422 7.908L4.45 11.918L3.674 9.02L3.915 8.955L6.382 11.4L3.756 12.104C3.54067 12.1617 3.31221 12.1459 3.10693 12.0589C2.90165 11.9719 2.73132 11.8189 2.623 11.624L0 6.898L1.449 6.51L3.915 8.955L9.012 7.589V7.59ZM2.534 14.958H18.534V16.958H2.534V14.958Z" fill="#A0A0A0" />
                                                    </svg>
                                                    <?php
                                                    $originData = $originDestinations[$index]['FlightSegments'][0]['DepartureAirportLocationCode'];
                                                    $segmentCount = count($originDestinations[$index]['FlightSegments']);
                                                    $segmentCount -= 1;
                                                    $destinationData = $originDestinations[$index]['FlightSegments'][$segmentCount]['ArrivalAirportLocationCode'];
                                                    $datetime = $originDestinations[$index]['FlightSegments'][0]['DepartureDateTime'];
                                                    //session set for expiry validation in traveller details
                                                    $_SESSION['travel-return-depdate'] = $originDestinations[$index]['FlightSegments'][0]['DepartureDateTime'];

                                                    list($date, $time) = explode("T", $datetime);
                                                    ?>
                                                    <span class="ml-3"><?php echo $originData; ?> <span class="right-arrow-small arrow-121E7E mr-1">

                                                        </span><?php echo $destinationData . " " . date("d F Y", strtotime($date)); ?> </span>
                                                </div>
                                                <div class="col-md-4 text-md-right fs-15 fw-300">
                                                    <strong class="fw-500">
                                                        Total Duration:
                                                        <?php
                                                        $origin_total_duration = 0;
                                                        foreach ($flightSegments as $index => $flightSegment) {
                                                            $origin_total_duration += $flightSegment['JourneyDuration'];
                                                        }
                                                        echo convertMinutesToTimeFormat($origin_total_duration);
                                                        ?>
                                                    </strong>
                                                </div>
                                            </div>
                                            
                                                <?php
                                                    foreach ($flightSegments as $index => $flightSegment) {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');?>

                                                <ul class="form-row fs-15 mt-md-3 mt-2 mb-md-3 mb-2">
                                                    <li class="col-xl-1 col-md-2 text-center mb-md-0 mb-2">
                                                        <?php if ($airlineLocation['image']) { ?>
                                                            <img src="images/emirates-logo.png" alt="">
                                                        <?php

                                                        } else { ?>
                                                            <img src="images/no-image-icon-1.jpg" alt="" style="max-height: 75px;">
                                                        <?php

                                                        } ?>

                                                    </li>
                                                    <li class="col-md-2 mb-md-0 mb-2">
                                                        <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                                        Flight No - <?php echo $flightSegment['FlightNumber'] . " " . $flightSegment['CabinClassText'] ?>
                                                    </li>
                                                    <li class="col-xl-2 col-md-1"></li>
                                                    <li class="col-xl-6 col-md-7">
                                                        <div class="d-flex form-row justify-content-between align-items-center">
                                                            <div class="col-md-4 mb-md-0 mb-2 text-md-right">
                                                                <?php
                                                                $datetime = $flightSegment['DepartureDateTime'];
                                                                list($date, $time) = explode("T", $datetime);
                                                                $stmtlocation->execute(array('airport_code' => $flightSegment['DepartureAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                ?>
                                                                <strong class="fw-500 d-block"><?php echo $flightSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                <?php echo date("d F Y", strtotime($date)) . " " . $airportLocation['airport_name'] . " , " . $airportLocation['city_name'] ?>
                                                            </div>
                                                            <div class="col-md-3 mb-md-0 mb-2">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"></path>
                                                                    </svg>
                                                                    <?php
                                                                    $minutes = $flightSegment['JourneyDuration'];
                                                                    $hours = floor($minutes / 60);
                                                                    $remainingMinutes = $minutes % 60;
                                                                    echo $hours . " h  " . $remainingMinutes . " m";
                                                                    ?>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4 text-md-left">
                                                                <?php
                                                                $datetime = $flightSegment['ArrivalDateTime'];
                                                                list($date, $time) = explode("T", $datetime);
                                                                // echo date("d F Y", strtotime($date));
                                                                $stmtlocation->execute(array('airport_code' => $flightSegment['ArrivalAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                ?>
                                                                <strong class="fw-500 d-block"><?php echo $time . " " . $flightSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <?php
                                                if (isset($flightSegments[$index + 1])) {
                                                ?>
                                                    <ul class="form-row fs-15 mt-md-3 mt-2 mb-md-3 mb-2">
                                                        <li class="col-md-5"></li>
                                                        <li class="col-xl-6 col-md-7">
                                                            <div class="d-flex form-row justify-content-center align-items-center layover-duration pt-3 pb-3">
                                                                <div class="col-xl-4 col-md-5">
                                                                    <span>Layover :
                                                                        <?php
                                                                        $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $flightSegment['ArrivalDateTime']);
                                                                        $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $flightSegments[$index + 1]['DepartureDateTime']);
                                                                        $diff = $date1->diff($date2);

                                                                        // Get the difference in hours and minutes
                                                                        $hours = $diff->h;
                                                                        $minutes = $diff->i;
                                                                        echo $hours . " hr " . $minutes . " m ";
                                                                        ?>

                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                    <?php
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                                <p class="fs-14 fw-300 mt-2"><strong class="fw-500">
                                        <!-- Note: </strong>You will have to change Airport while travelling -->
                                </p>
                            </div>
                            <div class="details-popup-section row">
                                <!-- Button trigger modal -->

                                <button type="button" id="fareRuleApi" class="btn btn-typ5 col-md-2" data-toggle="modal" data-target="#FareRules" data-value="<?php echo $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'] ?>" data-count-value="<?php echo count($originDestinations[0]['FlightSegments']) ?>">
                                    <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 5.01808L7.30882 0.145527C7.45104 0.0506388 7.61818 0 7.78915 0C7.96012 0 8.12726 0.0506388 8.26949 0.145527L15.5783 5.01808V17.1345C15.5783 17.3641 15.4871 17.5842 15.3248 17.7465C15.1625 17.9088 14.9424 18 14.7128 18H0.865462C0.635927 18 0.415793 17.9088 0.253488 17.7465C0.0911823 17.5842 0 17.3641 0 17.1345V5.01808ZM7.78915 8.47992C8.24822 8.47992 8.68849 8.29756 9.0131 7.97295C9.33771 7.64834 9.52008 7.20807 9.52008 6.749C9.52008 6.28993 9.33771 5.84966 9.0131 5.52505C8.68849 5.20044 8.24822 5.01808 7.78915 5.01808C7.33008 5.01808 6.88982 5.20044 6.56521 5.52505C6.2406 5.84966 6.05823 6.28993 6.05823 6.749C6.05823 7.20807 6.2406 7.64834 6.56521 7.97295C6.88982 8.29756 7.33008 8.47992 7.78915 8.47992ZM4.32731 12.8072V14.5382H11.251V12.8072H4.32731ZM4.32731 10.2108V11.9418H11.251V10.2108H4.32731Z" fill="#7078BA" />
                                    </svg>
                                    <span class="ml-2">Fare Rules</span>
                                </button>
                                <button type="button" class="btn btn-typ5 col-md-2" data-toggle="modal" data-target="#BaggageDetails">
                                    <svg width="14" height="20" viewBox="0 0 14 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0.833496C9.50458 0.83335 9.99057 1.00804 10.3605 1.32254C10.7305 1.63703 10.9572 2.0681 10.995 2.52933L11 2.66683V4.50016H12C12.5046 4.50002 12.9906 4.6747 13.3605 4.9892C13.7305 5.3037 13.9572 5.73477 13.995 6.196L14 6.3335V16.4168C14 16.9031 13.7893 17.3694 13.4142 17.7132C13.0391 18.057 12.5304 18.2502 12 18.2502C11.9997 18.4838 11.9021 18.7085 11.7272 18.8784C11.5522 19.0483 11.313 19.1505 11.0586 19.1642C10.8042 19.1779 10.5536 19.102 10.3582 18.9521C10.1627 18.8021 10.0371 18.5894 10.007 18.3574L10 18.2502H4C4 18.4933 3.89464 18.7264 3.70711 18.8983C3.51957 19.0703 3.26522 19.1668 3 19.1668C2.73478 19.1668 2.48043 19.0703 2.29289 18.8983C2.10536 18.7264 2 18.4933 2 18.2502C1.49542 18.2503 1.00943 18.0756 0.639452 17.7611C0.269471 17.4466 0.0428434 17.0156 0.00500021 16.5543L1.00268e-07 16.4168V6.3335C-0.000159579 5.87097 0.190406 5.42548 0.533497 5.08633C0.876588 4.74718 1.34684 4.53944 1.85 4.50475L2 4.50016H3V2.66683C2.99984 2.2043 3.19041 1.75881 3.5335 1.41966C3.87659 1.08051 4.34685 0.872769 4.85 0.83808L5 0.833496H9ZM5 8.16683C4.73478 8.16683 4.48043 8.26341 4.29289 8.43532C4.10536 8.60722 4 8.84038 4 9.0835V13.6668C4 13.9099 4.10536 14.1431 4.29289 14.315C4.48043 14.4869 4.73478 14.5835 5 14.5835C5.26522 14.5835 5.51957 14.4869 5.70711 14.315C5.89464 14.1431 6 13.9099 6 13.6668V9.0835C6 8.84038 5.89464 8.60722 5.70711 8.43532C5.51957 8.26341 5.26522 8.16683 5 8.16683ZM9 8.16683C8.75507 8.16686 8.51866 8.24929 8.33563 8.39849C8.15259 8.54768 8.03566 8.75327 8.007 8.97625L8 9.0835V13.6668C8.00028 13.9005 8.09788 14.1252 8.27285 14.2951C8.44782 14.465 8.68695 14.5672 8.94139 14.5809C9.19584 14.5946 9.44638 14.5187 9.64183 14.3688C9.83729 14.2188 9.9629 14.0061 9.993 13.7741L10 13.6668V9.0835C10 8.84038 9.89464 8.60722 9.70711 8.43532C9.51957 8.26341 9.26522 8.16683 9 8.16683ZM9 2.66683H5V4.50016H9V2.66683Z" fill="#7078BA" />
                                    </svg>
                                    <span class="ml-2">Baggage Details</span>
                                </button>


                                <!-- Modal -->
                                <div class="modal fade" id="FareRules" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header pt-2 pb-2">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Fare Rules</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="">
                                                    <ul class="mb-3">
                                                        <li>
                                                            <ul>
                                                                <li class="d-flex justify-content-between p-1 mt-1">
                                                                    <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                    <span class="uppercase-txt"><?php echo $originData . " - " . $destinationData ?></span>
                                                                </li>
                                                                <li class="text-left">
                                                                    <table class="w-100">
                                                                        <?php
                                                                        //===============

                                                                            

                                                                        if (isset($_SESSION['user_id'])) {



                                                                            $id = $_SESSION['user_id'];

                                                                            $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');

                                                                            $stmt->execute(array('id' => $id));

                                                                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                                                            $roleId         =   $user['role'];// taken from earlier set user array 

                                                                            

                                                                        } else {

                                                                            $roleId         =   1;// if not logged in now ,taking user's role markup'

                                                                        }
                                                                    //===============
                                                                    // echo '<pre/>';
                                                                    // print_r($pricedItinerary);
                                                                        $penaltyCancel = $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['PenaltiesInfo'][0];
                                                                        if ($penaltyCancel['Allowed'] == 1) {
                                                                            $Penaltymarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =4 AND status=1 AND role_id = :role_id');

                                                                            $Penaltymarkup->execute(array('role_id' => $roleId));

                                                                            $markupPenaltyInfo = $Penaltymarkup->fetch(PDO::FETCH_ASSOC);                                         

                                                                            if(!empty($penaltyCancel['Amount'])){

                                                                                $markupPenaltyPercentage = ($markupPenaltyInfo['commission_percentage'] / 100) *  $penaltyCancel['Amount'];
                                                                                $markupPenaltyPercentage    =    round($markupPenaltyPercentage*$usd_converion_rate,2);

                                                                            }

                                                                        //===============
                                                                            $penaltyAmount = $penaltyCancel['Amount']*$usd_converion_rate;
                                                                            $penaltyCurrency = $penaltyCancel['CurrencyCode'];
                                                                        }else{
                                                                            $penaltyCurrency = '';
                                                                            $penaltyAmount  =   "Not Refundable";

                                                                        }
                                                                        ?>
                                                                        <tr class="bdr">
                                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                            <td> <?php
                                                                                if( is_numeric($penaltyAmount) ) {
                                                                                    echo "$ ".round($penaltyAmount,2);
                                                                                } else {
                                                                                    echo $penaltyAmount;
                                                                                }
                                                                             ?></td>
                                                                        </tr>
                                                                        <tr class="bdr">
                                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Bulatrips Fee</td>
                                                                            <?php if ($penaltyCancel['Allowed'] == 1) { ?>
                                                                                <td class="p-1"><?php echo "$ ".round($markupPenaltyPercentage,2);?>
                                                                                </td>
                                                                            <?php } ?>
                                                                        </tr>
                                                                    </table>
                                                                </li>
                                                            </ul>
                                                            <ul>
                                                                <li class="d-flex justify-content-between p-1 mt-1">
                                                                    <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                                    <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                                </li>
                                                                <li class="text-left">
                                                                    <table class="w-100">
                                                                        <?php
                                                                        // echo '<pre/>';
                                                                        // print_r($pricedItinerary);
                                                                        $penaltyChange = $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['PenaltiesInfo'][1];
                                                                        if ($penaltyChange['Allowed'] == 1) {
                                                                            $penaltyChangeAmount = $penaltyChange['Amount']*$usd_converion_rate;
                                                                            $penaltyChangeCurrency = $penaltyChange['CurrencyCode'];
                                                                        }else{
                                                                            $penaltyChangeCurrency = '';
                                                                            $penaltyChangeAmount    =   "Date Change Not Allowed";

                                                                        }

                                                                        

                                                                        //===================

                                                                        $DateChangemarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =0 AND status=2 AND role_id = :role_id');

                                                                        $DateChangemarkup->execute(array('role_id' => $roleId));

                                                                        $DateChangemarkupInfo = $DateChangemarkup->fetch(PDO::FETCH_ASSOC);


                                                                        $markupDatechangePercentage = '';

                                                                        if(!empty($penaltyChange['Amount'])){

                                                                            $markupDatechangePercentage = ($DateChangemarkupInfo['commission_percentage'] / 100) * $penaltyChange['Amount'];
                                                                            $markupDatechangePercentage    =    $markupDatechangePercentage*$usd_converion_rate;
                                                                        }     



                                                                        //===============
                                                                        
                                                                        ?>
                                                                        <tr class="bdr">
                                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                            <td> <?php
                                                                                if( is_numeric($penaltyChangeAmount) ) {
                                                                                    echo "$ ".round($penaltyChangeAmount,2);
                                                                                } else {
                                                                                    echo $penaltyChangeAmount;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="bdr">
                                                                            <td class="bg-f0f3f5 p-1" style="width: 40%;">Bulatrips Fee</td>
                                                                            <td> <?php
                                                                                if( is_numeric($markupDatechangePercentage) ) {
                                                                                    echo "$ ".round($markupDatechangePercentage,2);
                                                                                } else {
                                                                                    echo $markupDatechangePercentage;
                                                                                }    
                                                                                ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                    <p class="fs-13 fw-500 text-left"><strong>Note: </strong>
                                                    <div id="fareresult">

                                                    </div>
                                                    <!-- Cancellation/Flight change charges are indicative. Thomas Cook does not guarantee the accuracy of this information. Airlines stop accepting cancellation/change requests 4 - 72 hours before departure of the flight, depending on the airline, in such scenarios airline will have to be contacted directly for cancellation/change. For "Non-Refundable" fares, certain Airline Tax components are also Non-Refundable. For exact cancellation/change fee, please call us on our Toll Free Number 1800 2099 100. -->
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="BaggageDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header pt-2 pb-2">
                                                <h5 class="modal-title" id="exampleModalLongTitle">Baggage Details</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="fs-13">
                                                    <li class="text-left p-1 bdr-b">
                                                        <?php
                                                        $originData = $originDestinations[0]['FlightSegments'][0]['DepartureAirportLocationCode'];
                                                        $segmentCount = count($originDestinations[0]['FlightSegments']);
                                                        $segmentCount -= 1;
                                                        $destinationData = $originDestinations[0]['FlightSegments'][$segmentCount]['ArrivalAirportLocationCode'];
                                                        ?>
                                                        <?php echo $originData ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $destinationData ?>
                                                    </li>
                                                    <li class="">
                                                        <?php
                                                        foreach ($flightSegments as $index => $flightSegment) {
                                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                        ?>
                                                            <ul class="row align-items-center pt-3 pb-3">
                                                                <li class="col-md-1 mb-md-0 mb-2">
                                                                    <?php if ($airlineLocation['image']) { ?>
                                                                        <img src="images/emirates-logo.png" alt="">
                                                                    <?php

                                                                    } else { ?>
                                                                        <img src="images/no-image-icon-1.jpg" alt="" style="max-height: 75px;">
                                                                    <?php

                                                                    } ?>

                                                                </li>
                                                                <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                                    <strong><?php echo $airlineLocation['name'] ?></strong>
                                                                    <span class="uppercase-txt"><?php echo $flightSegment['DepartureAirportLocationCode'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $flightSegment['ArrivalAirportLocationCode'] ?></span>
                                                                </li>
                                                                <li class="col-md-7">
                                                                    <?php
                                                                    // $baggageInfo = $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['BaggageInfo'];
                                                                    // foreach($baggageInfo as $baggageInformations){
                                                                    ?>
                                                                    <ul class="row bdr-b">
                                                                        <li class="col-4">Checkin</li>
                                                                        <!-- <li class="col-4">1 pcs/person</li> -->

                                                                        <li class="col-4">
                                                                            <?php
                                                                                if($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['BaggageInfo'][$index] == "sb") {
                                                                                    echo "Standard Baggage";
                                                                                } else {
                                                                                    echo $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['BaggageInfo'][$index]; 
                                                                                }
                                                                            ?>
                                                                        </li>

                                                                    </ul>
                                                                    <ul class="row">
                                                                        <li class="col-4">Cabin</li>
                                                                        <!-- <li class="col-4">1 pcs/person</li> -->
                                                                        <li class="col-4">
                                                                            <?php
                                                                                if(strtolower($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index]) == "sb") {
                                                                                    echo "Standard Baggage";
                                                                                } else {
                                                                                    echo $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index]; 
                                                                                }
                                                                            ?>
                                                                        </li>
                                                                    </ul>
                                                                    <?php
                                                                    // }
                                                                    ?>
                                                                </li>
                                                            </ul>
                                                        <?php } ?>

                                                    </li>
                                                </ul>
                                                <p class="fs-13 fw-500 text-left"><strong>Note: </strong>
                                                    <!-- The information provided above is as retrieved from the airline reservation system. Thomas Cook does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure. -->
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <?php echo'<pre/>';
                                print_r($pricedItinerary); ?> -->
                            <div class="booking-price-preview row pt-4 pb-4">
                                <div class="col-md-6 d-flex justify-content-end">
                                    <div class="mb-3">
                                        <div class="d-flex">
                                            Total Price: <strong class="light-blue-txt">
                                                &#36;
                                                <?php
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
                                                // print_r($markup);echo 'helo';
                                                $pricinfInfo = $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare'];
                                                $totalFareAPI = $pricinfInfo['TotalFare']['Amount'];
                                                // echo $totalFareAPI;
                                                $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                // echo $markupPercentage;echo 'helooo';
                                                // echo $pricinfInfo['TotalFare']['Amount'];echo 'heli';
                                                // extraservice total amount
                                               // $_SESSION['totalService'] = '0';
                                            //   echo $TotExtraServiceAmount;
                                                $_SESSION['totalService'] = $TotExtraServiceAmount;
                                                // echo $_SESSION['totalService'];
                                                 $Totalamount = number_format(round($totalFareAPI + $markupPercentage + $TotExtraServiceAmount, 2), 2);
                                               /* if($baggageService)
                                                {
                                                    $extraServiceAmount = $mealamount + $baggageamount;

                                                    $_SESSION['totalService'] = $extraServiceAmount;
                                                    // echo $extraServiceAmount;
                                                    $Totalamount = number_format(round($totalFareAPI + $markupPercentage + $extraServiceAmount, 2), 2);
                                                }else{
                                                    $Totalamount = number_format(round($totalFareAPI + $markupPercentage, 2), 2);
                                                }*/
                    
                                                echo $Totalamount;
                                                 $_SESSION['Totalamount']   =$Totalamount;
                                                ?>

                                            </strong>
                                        </div>
                                    <?php if($baggageService){ ?>
                                        <div class="fs-13 mb-3">(Including Taxes and Extra Services) </div>
                                    <?php }else{ ?>
                                        <div class="fs-13 mb-3">(Including Taxes) </div>
                                    <?php } ?>
                                    <form id="bookingForm" method="post" action="">
                                        <input type="hidden" name="data" value="<?php echo htmlspecialchars(json_encode($_SESSION['revalidationApi'])); ?>">
                                        <input type="hidden" name="extraServiceAmount" value="<?php echo htmlspecialchars(json_encode($_SESSION['totalService'])); ?>">
                                        <input type="hidden" name="Totalamount" value="<?php echo $Totalamount; ?>">

                                        <?php if ($user['role'] == '1') { ?>
                                            <button type="button" id="payButton" class="btn btn-typ3 fs-14 fw-500 pl-4 pr-4" style="display: block;" onclick="window.location.href='wind_payment.php';">PAY NOW</button>
                                        <?php } else if ($user['role'] == '2') { ?>
                                            <button type="button" id="confirm" class="btn btn-typ3 fs-14 fw-500 pl-4 pr-4" style="display: block;">CONFIRM</button>
                                            <!-- Note: call book api after that redirect to confirm page -->
                                        <?php } else { ?>
                                            <script>
                                            alert('Error in user role value');
                                            </script>
                                        <?php } ?>
                                        </form>
                                        <div id="loaderIcon"></div>
                                    </div>
                                </div>
                                <div class="col-md-6 fare-details d-flex justify-content-end align-items-baseline">
                                    <button type="button" class="btn fs-14 text-decoration" data-toggle="modal" data-target="#FareDetails">Fare Details</button>
                                    <div class="modal fade" id="FareDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header pt-2 pb-2">
                                                    <h5 class="modal-title" id="exampleModalLongTitle">Fare Details</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="">
                                                        <ul>
                                                            <?php
                                                            $totalTaxes = 0;

                                                            $totalTaxesdata = 0;

                                                           
                                                            if (isset($_SESSION['adultCount'])) {
                                                                unset($_SESSION['adultCount']);
                                                            }
                                                            if (isset($_SESSION['childCount'])) {
                                                                unset($_SESSION['childCount']);
                                                            }
                                                            if (isset($_SESSION['infantCount'])) {
                                                                unset($_SESSION['infantCount']);
                                                            }

                                                             // Initialize session variables to zero
$_SESSION['adultCount'] = 0;
$_SESSION['childCount'] = 0;
$_SESSION['infantCount'] = 0;
                                                            foreach ($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'] as $fareInformation) {

                                                            ?>
                                                                <?php
                                                                if ($fareInformation['PassengerTypeQuantity']['Code'] === 'ADT') {



                                                                    $_SESSION['adultCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                                                } else if ($fareInformation['PassengerTypeQuantity']['Code'] === 'CHD') {


                                                                    $_SESSION['childCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                                                } else{


                                                                    $_SESSION['infantCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                                                }
                                                                //  session_write_close();
                                                                ?>
                                                                <!-- <li class="d-flex justify-content-between p-1"><span><?php echo $fareInformation['PassengerTypeQuantity']['Code'] . " ( " . $fareInformation['PassengerFare']['EquivFare']['Amount'] . " x " . $fareInformation['PassengerTypeQuantity']['Quantity'] . " ) " ?> </span><span><?php echo $fareInformation['PassengerFare']['EquivFare']['Amount'] * $fareInformation['PassengerTypeQuantity']['Quantity'] ?></span></li> -->
                                                            <?php } ?>
                                                            <li class="d-flex justify-content-between p-1 bdr-b">
                                                                <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in USD)</span></strong>
                                                                <span><?php echo $_SESSION['adultCount'] +  $_SESSION['childCount'] + $_SESSION['infantCount'];  ?> Passengers</span>
                                                            </li>
                                                            <li>


                                                                <ul class="bdr-b">
                                                                    <!-- <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li> -->

                                                                    <?php

                                                                    foreach ($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'] as $fareInformation) {
                                                                        $totalTax = 0;
                                                                        foreach ($fareInformation['PassengerFare']['Taxes'] as $taxdata) {
                                                                            $totalTax +=  $taxdata['Amount'];
                                                                        }
                                                                        $totalTaxes = $totalTax * $fareInformation['PassengerTypeQuantity']['Quantity'];

                                                                        $totalTaxesdata += $totalTaxes;
                                                                    }

                                                                    ?>
                                                                    <!-- <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span><?php echo $totalTaxesdata; ?></span></li>
                                                                    <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span> -->
                                                                    <?php

                                                                    // echo $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                                                    ?>
                                                                    </span>
                                                            </li>

                                                        </ul>
                                                        <!-- <ul class="bdr-b">
                                                                    <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li>
                                                                    <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Travel Site Charge</span><span>0</span></li>
                                                                </ul> -->

                                                        </li>
                                                        <li class="d-flex justify-content-between">
                                                            <strong class="fw-600">Total Flight Fare</strong><strong>&#36;
                                                                    <?php
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

                                                                    $totalFareAPI = $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                                                $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                                
                                                                // echo $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                                                $totalFareAmount = number_format(round($totalFareAPI + $markupPercentage, 2), 2);
                                                                echo $totalFareAmount;
                                                                ?>
                                                            </strong>
                                                        </li>
                                                        <?php  if($baggageService){ ?>
                                                            <li class="d-flex justify-content-between">
                                                                <strong class="fw-600">Total Extra services Fare</strong><strong>&#36;
                                                                <?php 
                                                                    $extraServiceAmount = $_SESSION['totalService'];
                                                                    // $extraServiceAmount = $mealamount + $baggageamount;;
                                                                    echo $extraServiceAmount;
                                                                ?>
                                                                </strong>
                                                            </li>
                                                            <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                            <strong class="fw-600">Total Fare</strong><strong>&#36;
                                                                <?php 
                                                                    $fullAmount = number_format(round($extraServiceAmount + $totalFareAmount, 2), 2);
                                                                    echo $fullAmount;
                                                                ?>
                                                                </strong>
                                                            </li>
                                                        <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Modal
Login Modal -->
<!-- balance checked -->
<div class="modal" id="balanceissueModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Insufficient Balance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location.href='agent-dashboard.php'">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Your balance is insufficient. Please add funds to your account and try booking the flight again.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='agent-dashboard.php'">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
    require_once("includes/login-modal.php");
    ?>
    <!--  forgot Modal -->
    <?php
    require_once("includes/forgot-modal.php");
    ?>
<?php require_once("includes/footer.php"); ?>
<script>
    $(".text-below-button").click(function() {
        $(this).parents('.modal').modal('hide');
    });
    $(".forgot-passward > button").click(function() {
        $(this).parents('.modal').modal('hide');
    });

    $('#FlightSearchLoading').modal({
        show: false
    })

    /******************TAB WITHOUT ID*******************************/
    $(document).ready(function() {
        $('.panel .nav-tabs').on('click', 'a', function(e) {
            var tab = $(this).parent(),
                tabIndex = tab.index(),
                tabPanel = $(this).closest('.panel'),
                tabPane = tabPanel.find('.tab-pane').eq(tabIndex);
            tabPanel.find('.active').removeClass('active');
            tab.addClass('active');
            tabPane.addClass('active');
        });
        $('.tab-pane').on('click', 'button', function(e) {
            $(this).parent(".tab-pane").removeClass("active");
            $(this).parents(".tab-content").siblings(".nav-tabs").children(".nav-item").removeClass("active");
        });
    });
    /***************************************************************/
    /**************Scroll To Top*****************/
    $(window).on('scroll', function() {
        if (window.scrollY > window.innerHeight) {
            $('#scrollToTop').addClass('active')
        } else {
            $('#scrollToTop').removeClass('active')
        }
    })

    $('#scrollToTop').on('click', function() {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    })
    /**********************************************/
</script>
<style>
        #loaderIcon {
            display: none;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</body>

</html>