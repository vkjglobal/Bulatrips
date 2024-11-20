<?php
// error_reporting(0);
require_once("includes/header.php");
require_once('includes/dbConnect.php');
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $airTripType = $_POST['tab'];
//     $cabinPreference = $_POST['cabin-preference'];
//     if($_POST['adult'])
//     $adultCount = $_POST['adult'];
//     else
//     $adultCount = 0;
//     if($_POST['child'])
//     $childCount = $_POST['child'];
//     else
//     $childCount=0;
//     if($_POST['infant'])
//     $infantCount = $_POST['infant'];
//     else
//     $infantCount=0;

//     $originLocation = $_POST['airport'];
//     $originLocationCode = explode("-", $originLocation);

//     $destinationLocation = $_POST['arrivalairport'];
//     $destinationLocationCode = explode("-", $destinationLocation);
//     // print_r($originLocationCode[0]);
//     // print_r($destinationLocationCode[0]);
//     $fromDate = $_POST['from'];
//     $departureDate = date("Y-m-d", strtotime($fromDate));
//     if ($adultCount < 1 || !is_numeric($adultCount)) {
//         // Display an error message
//         echo 'Please enter a valid number of adult passengers.';
//         exit;
//     }
//     if ($airTripType === 'OneWay') {
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


        // Construct the API request payload
        $requestData = array(

            'OriginDestinationInformations' => array(
                array(
                    // 'DepartureDateTime' => $departureDate,
                    // 'OriginLocationCode' =>  trim($originLocationCode[0]),
                    // 'DestinationLocationCode' => trim($destinationLocationCode[0])
                    // 'OriginLocationCode' =>  'COK',
                    // 'DestinationLocationCode' => 'DXB'
                    'DepartureDateTime' => '2023-06-10 T00:00:00',
                    'OriginLocationCode' =>  'COK',
                    'DestinationLocationCode' => 'DXB'
                )
            ),
            'TravelPreferences' => array(
                //  'MaxStopsQuantity' => 'Direct',
                'MaxStopsQuantity' => 'OneStop',
                // 'MaxStopsQuantity' => 'All',
                // 'CabinPreference' => $cabinPreference,
                // 'AirTripType' => $airTripType
                'CabinPreference' => 'Y',
                'AirTripType' => 'OneWay'
            ),
            'PricingSourceType' => 'Public',
            'PricingSourceType' => 'All',
            'IsRefundable' => true,
            'PassengerTypeQuantities' => array(
                array(
                    'Code' => 'ADT',
                    // 'Quantity' => $adultCount
                    'Quantity' => '1'
                )
                // ,
                // array(
                //     'Code' => 'CHD',
                //     'Quantity' => $childCount
                // ),
                // array(
                //     'Code' => 'INF',
                //     'Quantity' => $infantCount
                // )
            ),
            // 'RequestOptions' => 'Fifty',
            'RequestOptions' => 'Fifty',
            'NearByAirports' => true,
            'Nationality' => 'string',
            'Target' => 'Test'
            

            // 'ConversationId' => 'string',
        );
        $childCount=0;
        if ($childCount > 0) {
            $childDetails = array(
                'Code' => 'CHD',
                'Quantity' => $childCount
            );
            array_push($requestData['PassengerTypeQuantities'], $childDetails);
        }
        $infantCount=0;
        if ($infantCount > 0) {
            $infantDetails = array(
                'Code' => 'INF',
                'Quantity' => $infantCount
            );
            array_push($requestData['PassengerTypeQuantities'], $infantDetails);
        }
        // Send the API request

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $bearerToken
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        // Handle the API response
        if ($response) {
            $responseData = json_decode($response, true);
            echo '<pre>';
            print_r($responseData);
            echo '</pre>';


            if ($responseData['Success'] == 1) {
                if (isset($responseData['Data']['PricedItineraries'])) {
                    $pricedItineraries = $responseData['Data']['PricedItineraries'];
                } else {
                    echo "PricedItineraries key is missing in the API response.";
                }
            } else {
                echo "API response indicates an error.";
            }
        }
//     }
// }
?>


<section>
    <div class="container">
        <div class="form-row">
            <div class="col-12">
                <h2 class="title-typ2 mb-3 mb-lg-5">All Flights</h2>
            </div>
            <div class="col-12 d-none">
                <ul class="filter-left">
                    <li>3 of 3 flights</li>
                    <li>
                        <select name="" class="stops-select" id="">
                            <option value="">Stops</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="price-select" id="">
                            <option value="">One way price</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="opt-select" id="">
                            <option value="">Refundable</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="airline-select" id="">
                            <option value="">Airline</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="dep-time-select" id="">
                            <option value="">Departure Time</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="ret-time-select" id="">
                            <option value="">Return Time</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                </ul>
            </div>
            <div class="col-12 light-border">
                <ul class="flight-list">
                    <li>
                        <ul class="form-row titlebar">
                            <li class="col-md-2 text-center">Airline</li>
                            <li class="col-md-1">Depart</li>
                            <li class="col-md-2">Stops</li>
                            <li class="col-md-2">Arrive</li>
                            <li class="col-md-3">Duration</li>
                            <li class="col-md-2 text-center">Price</li>
                        </ul>
                    </li>
                    <?php
                    // $pageNumber = 1; // Current page number
                    // $resultsPerPage = 2; // Number of results per page
                    // $totalResults = count($pricedItineraries);
                    // $totalPages = ceil($totalResults / $resultsPerPage);
                    
                    // $startIndex = ($pageNumber - 1) * $resultsPerPage;
                    // $endIndex = min($startIndex + $resultsPerPage, $totalResults);
                    
                    // for ($i = $startIndex; $i < $endIndex; $i++) {
                    //     $pricedItinerary = $pricedItineraries[$i];

                     foreach ($pricedItineraries as $pricedItinerary) {

                        $originDestinations = $pricedItinerary['OriginDestinations'][0];
                        $segmentRef = $originDestinations['SegmentRef'];
                        $flightSegmentList = $responseData['Data']['FlightSegmentList'];
                        $FlightFaresList = $responseData['Data']['FlightFaresList'];
                        $FlightItineraryList = $responseData['Data']['ItineraryReferenceList'];
                        $fareListRefid = $pricedItinerary['FareRef'];
                        $fareListRef = $FlightFaresList[$fareListRefid];
                        $FlightPenaltyList = $responseData['Data']['PenaltiesInfoList'];
                        $penaltyListRefid = $pricedItinerary['PenaltiesInfoRef'];
                        $penaltyListRef = $FlightPenaltyList[$penaltyListRefid];
                        $onestop = false;

                        if(isset($pricedItinerary['OriginDestinations'][1])) {
                            $onestop = true;
                            $originDestinationsstops = $pricedItinerary['OriginDestinations'][1];
                            $segmentRefstop = $originDestinationsstops['SegmentRef'];

                            $segmentstop = $flightSegmentList[$segmentRefstop];
                            $duration = $segmentstop['JourneyDuration'];
                            $arrival = $segmentstop['ArrivalAirportLocationCode'];
                            $artime = $segmentstop['ArrivalDateTime'];
                            $deptime = $segmentstop['DepartureDateTime'];
                        }
                        $segment = $flightSegmentList[$segmentRef];
                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                        $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                        // print_r( $airportLocation);die();
                        // $airportLocation = 


                     ?>
                        <li class="pt-4 contentbar">
                            <ul class="form-row mb-lg-5 mb-3">
                                <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2"><img src="images/emirates-logo.png" alt=""><?php echo  $pricedItinerary['ValidatingCarrier']; ?></li>
                                <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                    <div class="">
                                        <!-- 11:45 KCZ  -->
                                        <?php echo $segment['DepartureAirportLocationCode']; ?>
                                        <br>
                                        <?php
                                        $datetime = $segment['DepartureDateTime'];
                                        list($date, $time) = explode("T", $datetime);
                                        echo date("d F Y", strtotime($date)); ?>
                                        <br>
                                        <?php
                                        echo $time;
                                        ?>
                                    </div>

                                </li>
                                <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2">
                                    <div>


                                        <?php
                                        if ($onestop) {
                                            $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                            $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                            $interval =  $date1->diff($date2);

                                            // Get the difference in hours and minutes
                                            $hours = $interval->h;
                                            $minutes = $interval->i;

                                            // echo "1 Stop";
                                            // echo $segment['DepartureAirportLocationCode'];
                                            echo "1 Stop" . "<br>" . $segment['ArrivalAirportLocationCode'] . "|" . $hours . "h " . $minutes . "m";
                                        } else
                                            echo "Direct";
                                        ?>

                                    </div>

                                </li>
                                <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        <?php
                                        if ($onestop) {
                                            $arrivallocation = $arrival;
                                            $arrivaltime = $artime;
                                            $datetime = $arrivaltime;
                                            list($date, $time) = explode("T", $datetime);
                                        } else {
                                            $arrivallocation = $segment['ArrivalAirportLocationCode'];
                                            $arrivaltime = $segment['ArrivalDateTime'];
                                            $datetime = $arrivaltime;
                                            list($date, $time) = explode("T", $datetime);
                                        }




                                        ?>
                                        <?php echo  $arrivallocation; ?><br>
                                        <?php echo date("d F Y", strtotime($date)); ?><br>
                                        <?php echo $time; ?>
                                    </div>

                                </li>
                                <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        <?php
                                        if ($onestop) {
                                            $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                            $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                            $interval =  $date1->diff($date2);
                                            $totalMinutes = ($interval->h * 60) + $interval->i;
                                            $minutes = $segment['JourneyDuration'] + $duration + $totalMinutes;
                                        } else
                                            $minutes = $segment['JourneyDuration'];
                                        $hours = floor($minutes / 60);
                                        $remainingMinutes = $minutes % 60;
                                        echo $hours . " h  " . $remainingMinutes . " m";
                                        ?>
                                    </div>

                                </li>
                                <li data-th="Price" class="main-dtls col-md-2 d-flex flex-column align-items-md-center mb-md-0 mb-2">
                                    <?php 
                                    $totalAdultfare=0;
                                    $totalChildfare=0;
                                    $totalInfantfare=0;
                                    if(isset($adultCount) && $adultCount > 0){
                                        $totalAdultfare +=$fareListRef['PassengerFare'][0]['TotalFare']* $adultCount;
                                    }
                                    if(isset($childCount) && $childCount > 0){
                                        $totalChildfare +=$fareListRef['PassengerFare'][1]['TotalFare']* $childCount;
                                    }
                                    if(isset($infantCount) && $infantCount > 0){
                                        $totalInfantfare +=$fareListRef['PassengerFare'][2]['TotalFare']* $infantCount;
                                    }
                                    ?>
                                    <div class="price-dtls mb-md-0 mb-2">&#36; <strong><?php echo $totalAdultfare+$totalChildfare+$totalInfantfare; ?></strong></div>
                                    <button class="btn btn-typ3 w-100">BOOK</button>
                                </li>
                            </ul>
                            <div class="form-row panel flight-details-tab-wrap">
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
                                            <div><?php echo $airportLocation['city_name']; ?>
                                                <span class="right-arrow-small arrow-000000"></span>
                                                <?php if ($onestop) {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                    $stmtlocation->execute(array('airport_code' => $arrival));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                } else {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                    $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                }
                                                $datetime = $segment['DepartureDateTime'];
                                                list($date, $time) = explode("T", $datetime);
                                                echo $airportLocation['city_name'] . " , " . date("d F Y", strtotime($date));
                                                ?>
                                            </div>
                                            <div>Total Duration: <?php

                                                                    if ($onestop) {
                                                                        $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                                                        $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                                                        $interval =  $date1->diff($date2);
                                                                        $totalMinutes = ($interval->h * 60) + $interval->i;
                                                                        $minutes = $segment['JourneyDuration'] + $duration + $totalMinutes;
                                                                    } else
                                                                        $minutes = $segment['JourneyDuration'];
                                                                    $hours = floor($minutes / 60);
                                                                    $remainingMinutes = $minutes % 60;
                                                                    echo $hours . " h  " . $remainingMinutes . " m";

                                                                    ?></div>
                                        </div>
                                        <?php
                                        foreach($pricedItinerary['OriginDestinations'] as $origins) {
                                            $originRef = $origins['SegmentRef'];
                                            $originSegment = $flightSegmentList[$originRef];
                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                        ?>

                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                            <ul class="col-lg-3 mb-3">
                                                <div class="text-left">
                                                    <strong class="fw-500 d-block">
                                                        <?php
                                                          $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                          // $stmtairline->execute(array('code' => $pricedItinerary['ValidatingCarrier']));
                                                          // $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                          $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                          $stmtairline->bindParam(':code', $code);
                                                          $stmtairline->execute();
                                                          $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                          // echo $pricedItinerary['ValidatingCarrier'];
                                                          echo $airlineLocation['name'];
                                                        ?>

                                                    </strong>
                                                    Flight No - <?php echo $originSegment['OperatingFlightNumber']; ?>
                                                </div>
                                            </ul>

                                            <div class="col-lg-7">

                                                <div class="d-flex row justify-content-between">
                                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                    <?php
                                                        $datetime = $originSegment['DepartureDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        // echo date("d F Y", strtotime($date));
                                                        $stmtlocation->execute(array('airport_code' => $originSegment['DepartureAirportLocationCode']));
                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                    ?>
                                                        <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode']." ".$time ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) ." ,".$airportLocation['airport_name']."," .$airportLocation['city_name'].",".$airportLocation['country_name']?> 
                                                    </div>
                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                            </svg>
                                                            <?php
                                                             $minutes =$originSegment['JourneyDuration'];
                                                             $hours = floor($minutes / 60);
                                                             $remainingMinutes = $minutes % 60;
                                                             echo $hours . " h  " . $remainingMinutes . " m";
                                                            ?>
                                                           
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 text-md-left">
                                                    <?php
                                                        $datetime = $originSegment['ArrivalDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        // echo date("d F Y", strtotime($date));
                                                        $stmtlocation->execute(array('airport_code' => $originSegment['ArrivalAirportLocationCode']));
                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                    ?>
                                                        <strong class="fw-500 d-block"> <?php echo $time." ".$originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) .", ".$airportLocation['airport_name']."," .$airportLocation['city_name'].",".$airportLocation['country_name']?>
                                                    </div>
                                                </div>

                                            </div>


                                        </div>
                                        <?php
                                        }



                                       ?>


                                        <!-- <div class="fs-15 fw-300 mb-4 text-left">
                                            Note: You will have to change Airport while travelling
                                        </div>
                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                            <div>Dubai <span class="right-arrow-small arrow-000000"></span> Kochi Saturday, 26 Nov, 2022 Arrives next day</div>
                                            <div>Total Duration: 24hr 5m</div>
                                        </div> -->
                                    </div>
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane2 ">
                                        <button class="close"><span>&times;</span></button>
                                        <div class="row fs-13 mb-3">
                                            <div class="col-md-5 mb-md-0 mb-3">
                                                <ul>
                                                    <li class="d-flex justify-content-between p-1 bdr-b">
                                                        <!-- <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in &#8377;)</span></strong> -->
                                                        <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in USD)</span></strong>
                                                        <?php if(isset($adultCount) && $adultCount > 0){ ?>
                                                        <span><?php echo $adultCount; ?> adult</span><?php }?>
                                                        <?php if(isset($childCount) && $childCount > 0) {?>
                                                        <span><?php echo $childCount; ?> child</span><?php }?>
                                                        <?php if(isset($infantCount) && $infantCount>0) {?>
                                                        <span><?php echo $infantCount; ?> infant</span><?php }?>
                                                    </li>
                                                    <li>
                                                        <ul class="bdr-b">
                                                            
                                                            <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li>
                                                            <?php 
                                                            $totalTax =0;
                                                            $totalAdultfare=0;
                                                            $totalChildfare=0;
                                                            $totalinfantfare =0;
                                                            if(isset($adultCount) && $adultCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][0]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalAdultfare=$fareListRef['PassengerFare'][0]['BaseFare']* $adultCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Adult (<?php echo $fareListRef['PassengerFare'][0]['BaseFare'] .'x'. $adultCount; ?>)</span><span><?php echo $totalAdultfare;  ?></span></li><?php } ?>
                                                            <?php if(isset($childCount) && $childCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][1]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalChildfare=$fareListRef['PassengerFare'][1]['BaseFare']* $childCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Child (<?php echo $fareListRef['PassengerFare'][1]['BaseFare'] .'x'. $childCount; ?>)</span><span><?php echo $totalChildfare; ?></span></li><?php } ?>
                                                            <?php if(isset($infantCount) && $infantCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][2]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalinfantfare=$fareListRef['PassengerFare'][2]['BaseFare']* $infantCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Infant (<?php echo $fareListRef['PassengerFare'][2]['BaseFare'] .'x'. $infantCount; ?>)</span><span><?php echo $totalinfantfare; ?></span></li><?php } ?>
                                                           <!-- tax calculation  -->

                                                            <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span><?Php echo $totalTax; ?></span></li>
                                                            <!-- <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li> -->
                                                        </ul>
                                                        <!-- <ul class="bdr-b">
                                                            <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li>
                                                            <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Thomas Cook Charges</span><span>0</span></li>
                                                        </ul> -->
                                                    </li>
                                                    <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                        <strong class="fw-600">Total Fare</strong><strong>&#36; <?php echo $totalAdultfare+$totalChildfare+$totalinfantfare+$totalTax; ?></strong>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-7">
                                                <ul>
                                                    <li class="d-flex align-items-baseline p-1 bdr-b">
                                                        <strong class="fs-14 fw-600">Fare Rules </strong>
                                                        <?php 
                                                        $refundAllowed=$penaltyListRef['Penaltydetails'][0]['RefundAllowed'];
                                                        if($refundAllowed == 1){
                                                        ?>
                                                        <span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>
                                                        <?php 
                                                        }else{
                                                        ?>
                                                        <span class="uppercase-txt dark-black-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Not Refundable</span>
                                                        <?php 
                                                        }
                                                        ?>
                                                    </li>
                                                    <li>
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                            </li>
                                                            <li class="text-left">
                                                                <table class="w-100">
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#36; <?php echo $penaltyListRef['Penaltydetails'][0]['RefundPenaltyAmount'] ?></td>
                                                                    </tr>
                                                                    <!-- <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr> -->
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
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#36; <?php echo $penaltyListRef['Penaltydetails'][0]['ChangePenaltyAmount'] ?></td>
                                                                    </tr>
                                                                    <!-- <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr> -->
                                                                </table>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>Cancellation/Flight change charges are indicative. Thomas Cook does not guarantee the accuracy of this information. Airlines stop accepting cancellation/change requests 4 - 72 hours before departure of the flight, depending on the airline, in such scenarios airline will have to be contacted directly for cancellation/change. For "Non-Refundable" fares, certain Airline Tax components are also Non-Refundable. For exact cancellation/change fee, please call us on our Toll Free Number 1800 2099 100.</p>
                                    </div>
                                    <!-- ----------------baggage details-------------------- -->
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane3">
                                        <button class="close"><span>&times;</span></button>
                                        <ul class="fs-13">
                                            <li class="text-left p-1 bdr-b">
                                            <?php 
                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                            $stmtlocation->execute(array('airport_code' =>$segment['DepartureAirportLocationCode']));
                                            $airportLocationdep = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                            $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                            $stmtairline->bindParam(':code', $code);
                                            $stmtairline->execute();
                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                            if($onestop){
                                                    $stmtlocation->execute(array('airport_code' => $arrival));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                            }else{
                                                    $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                            }
                                            ?>

                                               <?php echo $airportLocationdep['city_name'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $airportLocation['city_name'] ?>
                                            </li>
                                            <?php
                                            foreach($pricedItinerary['OriginDestinations'] as $baggages) {
                                            $baggageRef = $baggages['ItineraryRef'];
                                            $baggageSegment = $FlightItineraryList[$baggageRef];
                                            $originRef = $baggages['SegmentRef'];
                                            $originSegment = $flightSegmentList[$originRef];

                                            ?>
                                                <li class="">
                                                    <ul class="row align-items-center pt-3 pb-3">
                                                        <li class="col-md-1 mb-md-0 mb-2">
                                                            <?php if($airlineLocation['image']){
                                                                ?>
                                                                <img src="images/emirates-logo.png" alt="">
                                                                <?php }else{ ?>
                                                                <img src="images/no-image-icon-1.jpg" alt="">
                                                            <?php
                                                            }
                                                            ?>
                                                            
                                                            
                                                        </li>
                                                        <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                            <strong>
                                                                <?php 
                                                                echo $airlineLocation['name']; 
                                                                ?>
                                                            </strong>
                                                            <span class="uppercase-txt"><?php echo $originSegment['DepartureAirportLocationCode'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $originSegment['ArrivalAirportLocationCode'] ?></span>
                                                        </li>
                                                        <li class="col-md-7">
                                                            <ul class="row bdr-b">
                                                                <li class="col-4">Checkin</li>
                                                                <li class="col-4">1 pcs/person</li>
                                                                <li class="col-4"><?php echo $baggageSegment['CheckinBaggage'][0]['Value']?> </li>
                                                            </ul>
                                                            <ul class="row">
                                                                <li class="col-4">Cabin</li>
                                                                <li class="col-4">1 pcs/person</li>
                                                                <li class="col-4"><?php echo $baggageSegment['CabinBaggage'][0]['Value']?></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                    
                                                </li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>The information provided above is as retrieved from the airline reservation system. Travel Site does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure.</p>
                                    </div>

                                </div>
                            </div>
                        </li>
                    <?php
                        // }
                    }


                    echo '<div class="pagination">';
                    if ($pageNumber > 1) {
                        echo '<a href="?page=' . ($pageNumber - 1) . '">Previous</a>';
                    }

                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo '<a href="?page=' . $i . '">' . $i . '</a>';
                    }

                    if ($pageNumber < $totalPages) {
                        echo '<a href="?page=' . ($pageNumber + 1) . '">Next</a>';
                    }
                    echo '</div>';
                    ?>
                    ?>

                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!--  Login Modal -->
<?php
require_once("includes/login-modal.php");
?>
<!--  forgot Modal -->
<?php
require_once("includes/forgot-modal.php");
?>
<div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <form>
                            <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest fare for flights</div>
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
</div>
<?php
require_once("includes/footer.php");
?>
<script>
    /************Datepicker******************/
    $(function() {
        var dateFormat = "mm/dd/yy",
            from = $("#from")
            .datepicker({
                //defaultDate: "+1w",
                changeMonth: true,
                minDate: 0,
            })
            .on("change", function() {
                to.datepicker("option", "minDate", getDate(this));
            }),
            to = $("#to").datepicker({
                //defaultDate: "+1w",
                changeMonth: true
            })
            .on("change", function() {
                from.datepicker("option", "maxDate", getDate(this));
            });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
    });


    $(function() {
        $(".date-multy-city").datepicker({
            dateFormat: "D, M d",
            minDate: 0
        });
    });
    /*****************************************/

    $(document).ready(function() {


        $('.select-class').select2();
        $('.stops-select').select2();
        $('.price-select').select2();
        $('.opt-select').select2();
        $('.airline-select').select2();
        $('.dep-time-select').select2();
        $('.ret-time-select').select2();

        // $('[name=tab]').each(function(i,d){
        //     var p = $(this).prop('checked');
        //     //   console.log(p);
        //     if(p){
        //         $('.search-box').eq(i)
        //         .addClass('on');
        //     }    
        // });  

        // $('[name=tab]').on('change', function(){
        //     var p = $(this).prop('checked');

        //     // $(type).index(this) == nth-of-type
        //     var i = $('[name=tab]').index(this);

        //     $('.search-box').removeClass('on');
        //     $('.search-box').eq(i).addClass('on');
        // });

        $('.flight-search > input').click(function() {
            if ($('#return').is(':checked')) {
                $("#to").show().next(".icon").show()
            } else(
                $("#to").hide().next(".icon").hide()
            )
            if ($('#multi-city').is(':checked')) {
                $(".search-box.multi-city-search").css("display", "flex").siblings().hide()

            } else(
                $(".search-box.multi-city-search").hide().siblings().show()
            )
        })

        // $('#multi-city').click(function() {
        //     $(".search-box.multi-city-search").show()
        //     $(".multi-city-search").siblings(".search-box").hide();
        // });


        $(".select-lbl").click(function() {
            $(this).parent(".person-select").toggleClass("open");
            $(".select-dropbox").toggle();
        })


        $('.add').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepUp();
        })
        $('.minus').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepDown();
        })

        /******************TAB WITHOUT ID*******************************/
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
        /***************************************************************/
    });

    $(".text-below-button").click(function() {
        $(this).parents('.modal').modal('hide');
    });
    $(".forgot-passward > button").click(function() {
        $(this).parents('.modal').modal('hide');
    });

    $('#FlightSearchLoading').modal({
        show: false
    })
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
</body>

</html>