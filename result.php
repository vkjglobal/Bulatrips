<?php
error_reporting(0);
session_start();
require_once("includes/header.php");
require_once('includes/dbConnect.php');
require_once('includes/common_const.php');
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$airport_depart = getAirPortLocationsByAirportCode($_SESSION['search_values']['airport'], $conn);
$airport_arrival = getAirPortLocationsByAirportCode($_SESSION['search_values']['arrivalairport'], $conn);

$usd_converion_rate = getConversionRate();

$searchValue = $_SESSION['search_values'];

$airTripType = $searchValue['tab'];
$cabinPreference = $searchValue['cabin-preference'];

if ($searchValue['adult'])
    $adultCount = $searchValue['adult'];
else
    $adultCount = 0;
if ($searchValue['child'])
    $childCount = $searchValue['child'];
else
    $childCount = 0;
if ($searchValue['infant'])
    $infantCount = $searchValue['infant'];
else
    $infantCount = 0;

    

$originLocation = $searchValue['airport'];
$originLocationCode = explode("-", $originLocation);

$destinationLocation = $searchValue['arrivalairport'];
$destinationLocationCode = explode("-", $destinationLocation);

$fromDate = $searchValue['from'];
$departureDate = date("Y-m-d", strtotime($fromDate));

$ToDate = $searchValue['to'];
$returndepartureDate = date("Y-m-d", strtotime($ToDate));


$responseData  = $_SESSION['response'];

// echo "<pre>";
    // print_r($searchValue);
    // print_r($responseData);
// echo "</pre>";


$pricedItineraries = $responseData['Data']['PricedItineraries'];

$totalFlights = count($pricedItineraries);
$flightsPerPage = 15;
$totalPages = ceil($totalFlights / $flightsPerPage);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startIndex = ($page - 1) * $flightsPerPage;
$currentPageFlights = array_slice($pricedItineraries, $startIndex, $flightsPerPage);


$stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
$stmtlocation->execute(array('airport_code' => $originLocationCode[0]));
$airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

$stmtlocation->execute(array('airport_code' => $destinationLocationCode[0]));
$airportDestinationLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

if (isset($_SESSION['response']) && isset($_SESSION['search_values'])) {
    if (isset($responseData['Data']['Errors'])) {
        require_once('includes/no_result_found.php');
    } else {?>

        <!-- TOP BAR DETAILED AND SEARCH AGAIN SECTION STARTS -->
            <section class="midbar-wrapper-inner pt-3 pb-3" style="border-bottom: 2px solid #FFF;margin-bottom: 15px;position: sticky;top: 140px;z-index: 1;">
                <div class="flight-search-midbar container">
                    
                <div class="d-flex white-txt justify-content-center">
                        <div class="d-flex align-items-center">
                            <span class="mr-3">
                                <?php echo $airportLocation['city_name']; ?> To <?php echo $airportDestinationLocation['city_name']; ?> |
                                <?php echo date("D, d M", strtotime($fromDate)); ?> | 
                                <?php echo "Type: ".$airTripType; ?> |
                                <?php echo "Traveller: ". $adultCount + $childCount + $infantCount; ?> | 
                                <?php echo "Class: ". $searchValue['selected_cabin_text']; ?> 
                            </span>
                            <button class="btn btn-typ7 ml-3 btn-primary" id="modify-search-result-btn">Modify Search</button>
                        </div>
                    </div>

                    <div class="row" id="modify-search-result" style="display: none;">
                        <form class="flight-search col-12" id="flight-search" method="post" action="search.php">

                            <input type="radio" id="return" name="tab" value="Return" <?php if( isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "Return" ) {echo "checked";} ?>>
                            <label for="return">Round-trip</label>
                            <input type="radio" id="one-way" name="tab" value="OneWay" <?php if( isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "OneWay" ) {echo "checked";} ?>>
                            <label for="one-way">One-way</label>
                            
                            <div class="select-class-wrp">
                                <select name="cabin-preference" class="select-class" id="cabin-preference">
                                    <option value="Y" <?php echo $cabinPreference == 'Y' ? 'selected' : ''; ?>>Economy</option>
                                    <option value="S" <?php echo $cabinPreference == 'S' ? 'selected' : ''; ?>>Premium</option>
                                    <option value="C" <?php echo $cabinPreference == 'C' ? 'selected' : ''; ?>>Business</option>
                                    <option value="F" <?php echo $cabinPreference == 'F' ? 'selected' : ''; ?>>First</option>
                                </select>
                            </div>
                            <input type="hidden" id="selected_cabin_text" name="selected_cabin_text" value="Economy">
                            <span class="person-select" onclick="return fetchAndAlert()">
                            
                                <label for="" class="select-lbl">Traveller <span class="count"><?php echo $adultCount + $childCount + $infantCount  ?></span><span class="downarrow"></span></label>
                                <div class='select-dropbox'>
                                    <span class="selectbox d-flex justify-content-between">
                                        <label class="fs-13 fw-600" for="">Adults
                                            <span class="fs-11">12 years and above</span>
                                        </label>
                                        <span class="selec-wrp d-inline-flex align-items-center">
                                            <input type="number" id="adult_count" name="adult" min="1" value=<?php echo $adultCount ?>>
                                            <span class='minus'>-</span>
                                            <span class='add'>+</span>
                                        </span>
                                    </span>
                                    <span class="selectbox d-flex justify-content-between">
                                        <label class="fs-13 fw-600" for="">Children
                                            <span class="fs-11">2 - 11 years</span>
                                        </label>
                                        <span class="selec-wrp d-inline-flex align-items-center">
                                            <input type='number' id="child-count" name="child" min=0 value=<?php echo $childCount ?>>
                                            <span class='minus'>-</span>
                                            <span class='add'>+</span>
                                        </span>
                                    </span>
                                    <span class="selectbox d-flex justify-content-between">
                                        <label class="fs-13 fw-600" for="">Infants
                                            <span class="fs-11">Under 2 years</span>
                                        </label>
                                        <span class="selec-wrp d-inline-flex align-items-center">
                                            <input type='number' id="infant-count"  name="infant" min=0 value=<?php echo $infantCount ?>>
                                            <span class='minus'>-</span>
                                            <span class='add'>+</span>
                                        </span>
                                    </span>
                                </div>
                            </span>

                            <div style="display: inline-block;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="Direct" id="direct_flights" name="direct_flights" <?php if( $searchValue['direct_flights'] == "Direct" ) {echo "checked";}?> style="width: 19px;height: 19px; margin-top: 3px;">
                                    <label class="form-check-label" for="direct_flights" style="margin-left: 5px;"> Direct Flights</label>
                                </div>
                            </div>
                            
                            <div class="srch-fld">
                                <div class="search-box on row">
                                    <div class="form-fields col-md-3">
                                        <input type="text" id="airport-input" name="airport" class="form-control" placeholder="Departing From" value="<?php echo $originLocationCode[0] ?>">
                                    </div>
                                    <div class="form-fields col-md-3">
                                        <input type="text" id="arrivalairport-input" name="arrivalairport" class="form-control" placeholder="Going To" value="<?php echo $destinationLocationCode[0] ?>">
                                    </div>
                                    <div class="form-fields col-md-2 calndr-icon">
                                        <input type="text" class="form-control" id="from" name="from" value=<?php echo $departureDate ?>>
                                    </div>
                                    <div class="form-fields col-md-2 calndr-icon">
                                        <input type="text" class="form-control" id="to" name="to" value=<?php echo $returndepartureDate ?>>
                                    </div>
                                    <div class="form-fields col-md-2">
                                        <input type="submit" name="go" class="btn btn-typ1 w-100 form-control" value="Search">
                                    </div>
                                </div>

                                <div class="search-box row multi-city-search">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="form-fields col-md-4">
                                                <input type="text" class="form-control" placeholder="Departing From">
                                            </div>
                                            <div class="form-fields col-md-4">
                                                <input type="text" class="form-control" placeholder="Going To">
                                            </div>
                                            <div class="form-fields col-md-2 calndr-icon">
                                                <input type="text" class="form-control date-multy-city">
                                            </div>
                                        </div>
                                        <div class="row mt-md-2">
                                            <div class="form-fields col-md-4">
                                                <input type="text" class="form-control" placeholder="Departing From">
                                            </div>
                                            <div class="form-fields col-md-4">
                                                <input type="text" class="form-control" placeholder="Going To">
                                            </div>
                                            <div class="form-fields col-md-2 calndr-icon">
                                                <input type="text" class="form-control date-multy-city">
                                            </div>
                                            <div class="form-fields">
                                            <!--  <button class="btn add-trip fw-500 dark-blue-txt">Add Trip +</button> -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-fields">
                                        <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </form>

                    </div>

                </div>
            </section>
        <!-- TOP BAR DETAILED AND SEARCH AGAIN SECTION ENDS -->

        <!-- BREADCRUMB STARTS HERE -->
        <section style="margin-bottom: 10px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumbs">
                            <li><a href="index.php" style="text-decoration: underline !important;">Home</a></li>
                            <li> <?php echo $airportLocation['city_name'] . ' to ' . $airportDestinationLocation['city_name'] . ' ' . $airTripType ?> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- BREADCRUMB STARTS HERE -->
       
        <!-- FILTERATION PART STARTS -->
            <!-- <section style="margin-bottom:20px;" class="d-none">
                <div class="container">
                    <div class="form-row">
                        <div class="col-12">
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

                        <div class="col-12 light-border" style="position: sticky;top: 155px;z-index: 99;">
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
                            </ul>
                        </div>


                    </div>
                </div>
            </section> -->
        <!-- FILTERATION PART ENDS -->



        <section style="margin-bottom:20px;">
            <div class="container">
                <div class="form-row">
                    <?php foreach ($currentPageFlights as $pricedItinerary) {?>
                        <div class="col-12 light-border mb-3 p-0">
                            <?php
                            $totalstop = 0;
                            foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                if ($originDestination['LegIndicator'] == 0) {
                                    $totalstop = $totalstop + 1;
                                }
                            }
                            $totalstop = $totalstop - 1;

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
                            ?>
                        
                        
                            <p style="padding:10px; font-size: 20px; color:#070f4e; text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center; border-bottom: 2px solid #ccc">
                                <img class="flight_icon_small" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg"/>
                                <strong>Departure</strong>
                                <span style="display: block;float: right;position: absolute;right: 18px;text-transform: capitalize;font-size: 15px;"><?php echo $fareListRef['FareType']; ?></span>
                            </p>
                            <ul class="flight-list">
                            <?php
                                if ($totalstop > 0) {
                                    $originDestinationsstops = $pricedItinerary['OriginDestinations'][$totalstop];
                                    $segmentRefstop = $originDestinationsstops['SegmentRef'];

                                    $segmentstop = $flightSegmentList[$segmentRefstop];
                                    $duration = $segmentstop['JourneyDuration'];
                                    $arrival = $segmentstop['ArrivalAirportLocationCode'];
                                    $artime = $segmentstop['ArrivalDateTime'];
                                    $deptime = $segmentstop['DepartureDateTime'];
                                }

                                $segment = $flightSegmentList[$segmentRef];
                                
                                //-------find total return stop and get return details information----
                                $totalReturnStop = 0;
                                $filteredSegments = [];
                                foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                    if ($originDestination['LegIndicator'] == 1) {
                                        $filteredSegments[] = $originDestination['SegmentRef'];
                                    }
                                }
                                $totalReturnStop = count($filteredSegments) - 1;
                                $totalDurationReturn = 0;

                                foreach ($filteredSegments as $segmentRef) {
                                    foreach ($flightSegmentList as $flightSegment) {
                                        if ($flightSegment['SegmentRef'] == $segmentRef) {
                                            $totalDurationReturn += $flightSegment['JourneyDuration'];
                                        }
                                    }
                                }

                                if ($totalReturnStop >= 0) {

                                    $originDestinationsstops = $pricedItinerary['OriginDestinations'][$totalReturnStop];
                                    $segmentRefstop = $originDestinationsstops['SegmentRef'];

                                    $segmentstop = $flightSegmentList[$segmentRefstop];
                                    $duration = $segmentstop['JourneyDuration'];
                                    $arrival = $segmentstop['ArrivalAirportLocationCode'];
                                    $artimereturn = $segmentstop['ArrivalDateTime'];
                                    $deptime = $segmentstop['DepartureDateTime'];

                                    $segmentRef = $filteredSegments[0];
                                    $segmentReturn = $flightSegmentList[$segmentRef];
                                    $segmentRefArrival = $filteredSegments[$totalReturnStop];
                                    $segmentReturnArrival = $flightSegmentList[$segmentRefArrival];
                                }

                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
                                $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                $stmtairline->bindParam(':code', $code);
                                $stmtairline->execute();
                                $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <li class="contentbar">
                                    <ul class="form-row mb-lg-2">
                                        <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2 "><span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span></li>
                                        <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                            <div class="">
                                                <strong style="font-size:16px;"><?php echo $segment['DepartureAirportLocationCode']; ?></strong>
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
                                        <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                            <div>


                                                <?php

                                                if ($totalstop > 0) {
                                                    $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                                    $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                                    $interval =  $date1->diff($date2);


                                                    $hours = $interval->h;
                                                    $minutes = $interval->i;
                                                    
                                                    echo $totalstop . " Stop";

                                                    // . "<br>" . $segment['ArrivalAirportLocationCode'] . "|" . $hours . "h " . $minutes . "m";
                                                } else
                                                    echo "Direct";

                                                ?>

                                            </div>

                                        </li>
                                        <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                            <div>
                                                <?php
                                                if ($totalstop > 0) {
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
                                                <strong style="font-size:16px;"><?php echo $arrivallocation; ?></strong>

                                                <br>
                                                <?php echo date("d F Y", strtotime($date)); ?><br>
                                                <?php echo $time; ?>
                                            </div>

                                        </li>
                                        <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                            <div>
                                                <strong class="fw-500">
                                                    Total Duration:<br />
                                                    <?php
                                                    $origin_total_duration = 0;
                                                    foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                        $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                                        if ($origins['LegIndicator'] == 0) {
                                                            $origin_total_duration += $originSegment['JourneyDuration'];
                                                        }
                                                    }
                                                    echo convertMinutesToTimeFormat($origin_total_duration);
                                                    ?>
                                                </strong>
                                            </div>

                                        </li>
                                    </ul>
                                    <?php
                                    if ($totalReturnStop >= 0) {
                                    ?>
                                            <p style="border-top:1px solid #CCC;border-bottom:1px solid #CCC;padding:10px; font-size: 20px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;">
                                                <img class="flight_icon_small" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg"/>
                                                <strong>Return</strong>
                                            </p>
                                            
                                        <ul class="form-row mb-lg-2">
                                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2">
                                            <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span>
                                           
                                            </li>
                                            <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                                <div class="">
                                                    <strong style="font-size:16px;"><?php echo $segmentReturn['DepartureAirportLocationCode']; ?></strong>
                                                    <br>
                                                    <?php
                                                    $datetime = $segmentReturn['DepartureDateTime'];
                                                    list($date, $time) = explode("T", $datetime);
                                                    echo date("d F Y", strtotime($date)); ?>
                                                    <br>
                                                    <?php
                                                    echo $time;
                                                    ?>
                                                </div>

                                            </li>
                                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                                <div>
                                                    <?php
                                                    if ($totalReturnStop > 0) {
                                                        echo $totalReturnStop . " Stop";
                                                    } else
                                                        echo "Direct";
                                                    ?>
                                                </div>
                                            </li>
                                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    <?php
                                                        $datetime = $segmentReturnArrival['ArrivalDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                    ?>
                                                    <strong style="font-size:16px;"><?php echo $segmentReturnArrival['ArrivalAirportLocationCode']; ?></strong>
                                                    <br>
                                                    <?php echo date("d F Y", strtotime($date)); ?><br>
                                                    <?php echo $time; ?>
                                                </div>
                                            </li>
                                            <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    <strong class="fw-500">
                                                        Total Duration:<br />
                                                        <?php
                                                        $origin_total_duration = 0;
                                                        foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                            $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                                            if ($origins['LegIndicator'] == 1) {
                                                                $origin_total_duration += $originSegment['JourneyDuration'];
                                                            }
                                                        }
                                                        echo convertMinutesToTimeFormat($origin_total_duration);
                                                        ?>
                                                    </strong>
                                                </div>
                                            </li>
                                        </ul>
                                    <?php
                                    }
                                    ?>

                                    <div class="form-row panel flight-details-tab-wrap">
                                        <ul class="nav nav-tabs d-flex justify-content-around w-100" style="background: #070f4e;margin: 0px 5px -5px 4px;border-bottom-right-radius: 10px;border-bottom-left-radius: 10px;padding: 10px 0 5px 0;text-align: center;">
                                            <li class="nav-item">
                                                <a class="nav-link">
                                                <span class="detail-icon" style="font-size: 18px;">✈️</span>Flight Details
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link">
                                                    <span class="detail-icon" style="font-size: 18px;">💼</span> Fare Details
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link">
                                                <span class="detail-icon" style="font-size: 18px;">📦</span>Baggage Details
                                                </a>
                                            </li>
                                            <li>
                                            <?php
                                                $totalAdultfare = 0;
                                                $totalChildfare = 0;
                                                $totalInfantfare = 0;
                                                if (isset($adultCount) && $adultCount > 0) {

                                                    $totalAdultfare += $fareListRef['PassengerFare'][0]['TotalFare'] * $adultCount;
                                                }
                                                if (isset($childCount) && $childCount > 0) {
                                                    $totalChildfare += $fareListRef['PassengerFare'][1]['TotalFare'] * $childCount;
                                                }
                                                if (isset($infantCount) && $infantCount > 0) {
                                                    $totalInfantfare += $fareListRef['PassengerFare'][2]['TotalFare'] * $infantCount;
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
                                                $totalFareAPI = $totalAdultfare + $totalChildfare + $totalInfantfare;
                                                $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                ?>
                                                <form action="my-booking-step1.php" method="post">
                                                    <input type="hidden" id="fscode" name="fscode" value="<?php echo $pricedItinerary['FareSourceCode']; ?>">
                                                    <button type="submit" class="btn btn-typ7 w-100" style="font-weight: bold;font-size: 16px;">
                                                    $ <?php echo number_format(round($totalAdultfare + $totalChildfare + $totalInfantfare + $markupPercentage, 2), 2); ?> <br />   
                                                    BOOK NOW
                                                </button>
                                                </form>
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
                                                    <div>
                                                        <strong class="fw-500">
                                                            Total Duration:
                                                            <?php
                                                            $origin_total_duration = 0;
                                                            foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                                $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                                                if ($origins['LegIndicator'] == 0) {
                                                                    $origin_total_duration += $originSegment['JourneyDuration'];
                                                                }
                                                            }
                                                            echo convertMinutesToTimeFormat($origin_total_duration);
                                                            ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                                <?php
                                                foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                    $originRef = $origins['SegmentRef'];
                                                    $originSegment = $flightSegmentList[$originRef];
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                    $itineryRef = $origins['ItineraryRef'];
                                                    $itinerySegment = $FlightItineraryList[$itineryRef];
                                                    if ($origins['LegIndicator'] == 0) {
                                                ?>

                                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                                            <ul class="col-lg-3 mb-3">
                                                                <div class="text-left">
                                                                    <strong class="fw-500 d-block">
                                                                        <?php
                                                                        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
                                                                        $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                                        $stmtairline->bindParam(':code', $code);
                                                                        $stmtairline->execute();
                                                                        $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                                        echo $airlineLocation['name'];
                                                                        ?>

                                                                    </strong>
                                                                    Flight No - <?php echo $originSegment['OperatingFlightNumber']; ?>
                                                                    <br>
                                                                    <?php echo $itinerySegment['CabinClassType'] ?>
                                                                </div>
                                                            </ul>

                                                            <div class="col-lg-7">

                                                                <div class="d-flex row justify-content-between">
                                                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                                        <?php
                                                                        $datetime = $originSegment['DepartureDateTime'];
                                                                        list($date, $time) = explode("T", $datetime);
                                                                        $stmtlocation->execute(array('airport_code' => $originSegment['DepartureAirportLocationCode']));
                                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                        ?>
                                                                        <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                        <?php echo date("d F Y", strtotime($date)) . " ," . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                    </div>
                                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                                        <div class="d-flex flex-column align-items-center">
                                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                                            </svg>
                                                                            <?php echo convertMinutesToTimeFormat($originSegment['JourneyDuration']); ?>
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
                                                                        <strong class="fw-500 d-block"> <?php echo $time . " " . $originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                        <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                    </div>
                                                                </div>

                                                            </div>


                                                        </div>
                                                <?php
                                                    }
                                                }

                                                ?>
                                                <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                                    <span><?php echo $airTripType; ?></span>
                                                    <strong  class="fw-500">
                                                        Total Duration: 
                                                        <?php
                                                            $return_total_duration = 0;
                                                            foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                                $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                                                if ($origins['LegIndicator'] == 1) {
                                                                    $return_total_duration += $originSegment['JourneyDuration'];
                                                                }
                                                            }
                                                            echo convertMinutesToTimeFormat($return_total_duration);
                                                        ?>
                                                    </strong>
                                                </div>
                                                <?php

                                                foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                    $originRef = $origins['SegmentRef'];
                                                    $originSegment = $flightSegmentList[$originRef];
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                    $itineryRef = $origins['ItineraryRef'];
                                                    $itinerySegment = $FlightItineraryList[$itineryRef];
                                                    if ($origins['LegIndicator'] == 1) {
                                                ?>

                                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                                            <ul class="col-lg-3 mb-3">
                                                                <div class="text-left">
                                                                    <strong class="fw-500 d-block">
                                                                        <?php
                                                                        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                                        $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                                        $stmtairline->bindParam(':code', $code);
                                                                        $stmtairline->execute();
                                                                        $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                                        // echo $pricedItinerary['ValidatingCarrier'];
                                                                        echo $airlineLocation['name'];
                                                                        ?>

                                                                    </strong>
                                                                    Flight No - <?php echo $originSegment['OperatingFlightNumber']; ?>
                                                                    <br>
                                                                    <?php echo $itinerySegment['CabinClassType'] ?>
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
                                                                        <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                        <?php echo date("d F Y", strtotime($date)) . " ," . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                    </div>
                                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                                        <div class="d-flex flex-column align-items-center">
                                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                                            </svg>
                                                                            <?php echo convertMinutesToTimeFormat($originSegment['JourneyDuration']); ?>
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
                                                                        <strong class="fw-500 d-block"> <?php echo $time . " " . $originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                        <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                    </div>
                                                                </div>

                                                            </div>


                                                        </div>
                                                <?php
                                                    }
                                                }

                                                ?>

                                            </div>
                                            <div class="tab-pane p-lg-5 pt-5 p-3 pane2 ">
                                                <button class="close"><span>&times;</span></button>
                                                <div class="row fs-13 mb-3">
                                                    <div class="col-md-5 mb-md-0 mb-3">
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 bdr-b">
                                                                <!-- <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in &#8377;)</span></strong> -->
                                                                <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in USD)</span></strong>
                                                                <?php if (isset($adultCount) && $adultCount > 0) { ?>
                                                                    <span><?php echo $adultCount; ?> adult</span><?php } ?>
                                                                <?php if (isset($childCount) && $childCount > 0) { ?>
                                                                    <span><?php echo $childCount; ?> child</span><?php } ?>
                                                                <?php if (isset($infantCount) && $infantCount > 0) { ?>
                                                                    <span><?php echo $infantCount; ?> infant</span><?php } ?>
                                                            </li>
                                                            <!-- <li> -->
                                                            <!-- <ul class="bdr-b"> -->

                                                            <!-- <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li> -->
                                                            <?php
                                                            $totalTax = 0;
                                                            $totalAdultfare = 0;
                                                            $totalChildfare = 0;
                                                            $totalInfantfare = 0;
                                                            if (isset($adultCount) && $adultCount > 0) {
                                                                foreach ($fareListRef['PassengerFare'][0]['TaxBreakUp'] as $taxdata) {
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                // $totalAdultfare=$fareListRef['PassengerFare'][0]['BaseFare']* $adultCount;
                                                            ?>
                                                                <!-- <li class="d-flex justify-content-between p-1"><span>Adult (<?php echo $fareListRef['PassengerFare'][0]['BaseFare'] . 'x' . $adultCount; ?>)</span><span><?php echo $totalAdultfare;  ?></span></li><?php } ?> -->
                                                                <?php if (isset($childCount) && $childCount > 0) {
                                                                    foreach ($fareListRef['PassengerFare'][1]['TaxBreakUp'] as $taxdata) {
                                                                        $totalTax +=  $taxdata['Amount'];
                                                                    }
                                                                    // $totalChildfare=$fareListRef['PassengerFare'][1]['BaseFare']* $childCount;
                                                                ?>
                                                                    <!-- <li class="d-flex justify-content-between p-1"><span>Child (<?php echo $fareListRef['PassengerFare'][1]['BaseFare'] . 'x' . $childCount; ?>)</span><span><?php echo $totalChildfare; ?></span></li><?php } ?> -->
                                                                    <?php if (isset($infantCount) && $infantCount > 0) {
                                                                        foreach ($fareListRef['PassengerFare'][2]['TaxBreakUp'] as $taxdata) {
                                                                            $totalTax +=  $taxdata['Amount'];
                                                                        }
                                                                        // $totalinfantfare=$fareListRef['PassengerFare'][2]['BaseFare']* $infantCount;
                                                                    ?>
                                                                        <!-- <li class="d-flex justify-content-between p-1"><span>Infant (<?php echo $fareListRef['PassengerFare'][2]['BaseFare'] . 'x' . $infantCount; ?>)</span><span><?php echo $totalinfantfare; ?></span></li><?php } ?> -->
                                                                        <!-- tax calculation  -->

                                                                        <!-- <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span><?Php echo $totalTax; ?></span></li> -->
                                                                        <!-- <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li> -->
                                                                        <!-- </ul> -->

                                                                        <!-- </li> -->
                                                                        <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                                            <?php
                                                                            if (isset($adultCount) && $adultCount > 0) {

                                                                                $totalAdultfare += $fareListRef['PassengerFare'][0]['TotalFare'] * $adultCount;
                                                                            }
                                                                            if (isset($childCount) && $childCount > 0) {
                                                                                $totalChildfare += $fareListRef['PassengerFare'][1]['TotalFare'] * $childCount;
                                                                            }
                                                                            if (isset($infantCount) && $infantCount > 0) {

                                                                                $totalInfantfare += $fareListRef['PassengerFare'][2]['TotalFare'] * $infantCount;
                                                                            }
                                                                            //get markeup of end user from marktable and calculate the % and this % add to total fare
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


                                                                            // $stmtmarkup->execute(array('role_id' => 1));
                                                                            // $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
                                                                            // $totalFareAPI=$totalAdultfare+$totalChildfare+$totalinfantfare+$totalTax;
                                                                            $totalFareAPI = $totalAdultfare + $totalChildfare + $totalInfantfare;
                                                                            // $markupPercentage = (($markup['commission_percentage'] / $totalFareAPI)*100);
                                                                            $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;

                                                                            ?>

                                                                            <!-- <strong class="fw-600">Total Fare</strong><strong>&#36; 
                                                            <?php
                                                            // echo $totalAdultfare+$totalChildfare+$totalinfantfare+$totalTax+$markupPercentage;
                                                            ?>
                                                        </strong> -->
                                                                            <strong class="fw-600">Total Fare</strong><strong>&#36; <?php echo number_format(round($totalAdultfare + $totalChildfare + $totalInfantfare + $markupPercentage, 2), 2); ?></strong>
                                                                        </li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <ul>
                                                            <li class="d-flex align-items-baseline p-1 bdr-b">
                                                                <strong class="fs-14 fw-600">Fare Rules </strong>
                                                                <?php
                                                                //   echo '<pre/>';
                                                                //   print_r($penaltyListRef);
                                                                $refundAllowed = $penaltyListRef['Penaltydetails'][0]['RefundAllowed'];
                                                                if ($refundAllowed == 1) {
                                                                ?>
                                                                    <span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <span class="uppercase-txt dark-black-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Not Refundable</span>
                                                                <?php
                                                                }
                                                                //DAte change allow or not 
                                                                $DateChangeAllowed = $penaltyListRef['Penaltydetails'][0]['ChangeAllowed'];
                                                                if ($DateChangeAllowed == 1) {
                                                                ?>
                                                                    <span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Date Change Allowed</span>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <span class="uppercase-txt dark-black-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Date Change Not Allowed</span>
                                                                <?php
                                                                }
                                                                ?>

                                                            </li>
                                                            <li>
                                                                <ul>
                                                                <!-- Start of cancelation/REfund Penalty fee ---- -->
                                                                    <li class="d-flex justify-content-between p-1 mt-1">
                                                                        <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                        </li>
                                                                        <li class="text-left">
                                                                        <table class="w-100">       
                                                                            <tr class="bdr" id="firstRow">
                                                                                <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee+Site Fee</td>
                                                                                                                                            
                                                                        <?php
                                                                        $penalityList = $penaltyListRef['Penaltydetails'];
                                                                        
                                                                        if (isset($_SESSION['user_id'])) {
                                                                            $roleId         =   $user['role'];
                                                                        }
                                                                        else{
                                                                             $roleId         =    1;
                                                                        }
                                                                        
                                                                        if(count($penalityList)>0){
                                                                            
                                                                            foreach($penalityList as $k => $val){
                                                                                if($val['PaxType'] == 'ADT'){

                                                                                    $passengerType = "Adult";
                                                                                
                                                                                }
                                                                                if($val['PaxType'] == 'CHD'){
                                                                                     $passengerType = "Children";
                                                                               
                                                                                }
                                                                                if($val['PaxType'] == 'INF'){
                                                                                     $passengerType = "Infant";
                                                                                
                                                                                }
                                                                                $Penaltymarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE cancel_type = 4 AND status=1 AND role_id = :role_id');
                                                                                $Penaltymarkup->execute(array('role_id' => $roleId));
                                                                                $markupPenaltyInfo = $Penaltymarkup->fetch(PDO::FETCH_ASSOC);
                                                                                if(!empty($val['RefundPenaltyAmount'])){
                                                                                     $markupPenaltyPercentage = ($markupPenaltyInfo['commission_percentage'] / 100) * $val['RefundPenaltyAmount'];
                                                                                   
                                                                                    $markupPenaltyPercentage    =    number_format(round($markupPenaltyPercentage));
                                                                                    $totDisplay =   $val['RefundPenaltyAmount']+$markupPenaltyPercentage;
                                                                                    ?>
                                                                                    <td><?php echo $passengerType.": $ ".round(($totDisplay*$usd_converion_rate),2); ?></td>
                                                                                    <?php
                                                                                }
                                                                                else{
                                                                                           ?>
                                                                                             <td>Refundable amount is 0 from Airline</td>
                                                                                            <?php
                                                                                }
                                                                            }
                                                                        }

                                                                        ?>

                                                                        </tr>
                                                                        </table>
                                                                        </li>                                                                    
                                                                        <!-- end of cancelation/REfund Penalty fee ---- -->
                                                                    
                                                                </ul>
                                                                <ul>
                                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                                         <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                                                         <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                                                     </li>
                                                                                     <li class="text-left">
                                                                                         <table class="w-100">
        
                                                                                             <tr class="bdr">
                                                                                                 <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee + Site Fee </td>
                                                                                                  <!-- start of date change Penalty fee ---- -->
                                                                                             <?php
                                                                                             foreach($penalityList as $k => $val){
                                                                                                     if($val['PaxType'] == 'ADT'){

                                                                                                         $passengerType = "Adult";
             
                                                                                                     }
                                                                                                     if($val['PaxType'] == 'CHD'){
                                                                                                          $passengerType = "Children";
            
                                                                                                     }
                                                                                                     if($val['PaxType'] == 'INF'){
                                                                                                          $passengerType = "Infant";
             
                                                                                                     }
                                                                                                         $DateChangemarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =0 AND status=2 AND role_id = :role_id');
                                                                                                         $DateChangemarkup->execute(array('role_id' => $roleId));
                                                                                                         $DateChangemarkupInfo = $DateChangemarkup->fetch(PDO::FETCH_ASSOC);
                                                                                                         

                                                                                                         if(!empty($val['ChangePenaltyAmount'])){
                                                                                                               $markupDatechangePercentage = ($DateChangemarkupInfo['commission_percentage'] / 100) * $val['ChangePenaltyAmount'];
                                                                                                                $markupDatechangePercentage    =    number_format(round($markupDatechangePercentage));
                                                                                                                 $totDisplayDate =   $val['ChangePenaltyAmount']+$markupDatechangePercentage;
                                                                                                                ?>
                                                                                                                <td><?php echo $passengerType.": $ ".round(($totDisplayDate*$usd_converion_rate),2); ?></td>
                                                                                                                <?php
                                                                                                         }
                                                                                                         else{
                                                                                                                         ?>
                                                                                                                         <td>Not Applicable</td>
                                                                                                                        <?php
                                                                                                         }
                                                                                                        
                                                                                             }

                                                                                             ?>

                                                                                             </tr>
        
                                                                                         </table>
                                                                                     </li>
                                                                                     <!-- end of date change penalty info ----- -->
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="fs-13 fw-500 text-left"><strong>Note: </strong>Cancellation and flight change fees are approximate and may vary depending on the Airlines .We cannot guarantee the accuracy of this information.</p>
                                            </div>
                                            <!-- ----------------baggage details-------------------- -->
                                            <div class="tab-pane p-lg-5 pt-5 p-3 pane3">
                                                <button class="close"><span>&times;</span></button>
                                                <ul class="fs-13">
                                                    <li class="text-left p-1 bdr-b">
                                                        <?php
                                                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                        $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                                                        $airportLocationdep = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                                        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                        $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                        $stmtairline->bindParam(':code', $code);
                                                        $stmtairline->execute();
                                                        $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                        if ($onestop) {
                                                            $stmtlocation->execute(array('airport_code' => $arrival));
                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                        } else {
                                                            $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                        }
                                                        ?>

                                                        <?php echo $airportLocationdep['city_name'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $airportLocation['city_name'] ?>
                                                    </li>
                                                    <?php
                                                    //fetching baggage information
                                                    foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                                        $baggageRef = $baggages['ItineraryRef'];
                                                        $baggageSegment = $FlightItineraryList[$baggageRef];
                                                        $originRef = $baggages['SegmentRef'];
                                                        $originSegment = $flightSegmentList[$originRef];
                                                        if ($baggages['LegIndicator'] == 0) {
                                                    ?>
                                                            <li class="">
                                                                <ul class="row align-items-center pt-3 pb-3">
                                                                    <li class="col-md-1 mb-md-0 mb-2">
                                                                    <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier'] ?>"></span>
                                                                    <!-- <li class="col-md-1 mb-md-0 mb-2 airImg airline-<?php echo $pricedItinerary['ValidatingCarrier'] ?>">
                                                                    <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span> -->



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
                                                                            <li class="col-4"><?php if(strtolower($baggageSegment['CheckinBaggage'][0]['Value']) == "sb") { echo "Standard Baggage";} else { echo $baggageSegment['CheckinBaggage'][0]['Value'];} ?></li>
                                                                        </ul>
                                                                        <ul class="row">
                                                                            <li class="col-4">Cabin</li>
                                                                            <li class="col-4">1 pcs/person</li>
                                                                            <li class="col-4"><?php if(strtolower($baggageSegment['CabinBaggage'][0]['Value']) == "sb") { echo "Standard Baggage";} else { echo $baggageSegment['CabinBaggage'][0]['Value'];} ?></li>
                                                                        </ul>
                                                                    </li>
                                                                </ul>

                                                            </li>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                    <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                                       <?php  if(!empty($searchValue['to'])){ ?>
                                                            <span>Return</span>
                                                     <?php   } ?>
                                                    </div>
                                                    <?php
                                                    foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                                        $baggageRef = $baggages['ItineraryRef'];
                                                        $baggageSegment = $FlightItineraryList[$baggageRef];
                                                        $originRef = $baggages['SegmentRef'];
                                                        $originSegment = $flightSegmentList[$originRef];
                                                        if ($baggages['LegIndicator'] == 1) {
                                                    ?>
                                                            <li class="">
                                                                <ul class="row align-items-center pt-3 pb-3">
                                                                    <li class="col-md-1 mb-md-0 mb-2">
                                                                    <span class="airImg airline-<?php echo $airlineLocation['code'] ?>"></span>



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
                                                                            <li class="col-4"><?php if(strtolower($baggageSegment['CheckinBaggage'][0]['Value']) == "sb") { echo "Standard Baggage";} else { echo $baggageSegment['CheckinBaggage'][0]['Value'];} ?></li>
                                                                        </ul>
                                                                        <ul class="row">
                                                                            <li class="col-4">Cabin</li>
                                                                            <li class="col-4">1 pcs/person</li>
                                                                            <li class="col-4"><?php if(strtolower($baggageSegment['CabinBaggage'][0]['Value']) == "sb") { echo "Standard Baggage";} else { echo $baggageSegment['CabinBaggage'][0]['Value'];} ?></li>
                                                                        </ul>
                                                                    </li>
                                                                </ul>

                                                            </li>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                                <p class="fs-13 fw-500 text-left"><strong>Note: </strong>The information provided above is as retrieved from the airline reservation system. Bulatrips does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure.</p>
                                            </div>

                                        </div>
                                    </div>
                                </li>


                            </ul>

                        </div>
                    <?php
                    }
                    
                    ?>
                </div>
                <div class="pagination-bottom w-100 p-4">
                    <?php
                    
                    for ($i = 1; $i < $totalPages; $i++) {
                        $activeClass = ($i == $page) ? 'active' : ''; // Apply 'active' class to the selected page
                        echo '<a href="?page=' . $i . '" class="' . $activeClass . ' mx-1">' . $i . '</a>';
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" id="errorModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeButton">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center" id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button"  id="closeButton1" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                       <!-- <button type="button" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Search Again</button> -->
                    </div>
                </div>
            </div>
        </div>
        <!--  Login Modal -->
        <?php
        require_once("includes/login-modal.php");
        ?>
        <!--  forgot Modal -->
        <?php
        require_once("includes/forgot-modal.php");
        include_once('loading-popup.php');
        ?>

<?php
    }
}function minutesToHoursMinutes($minutes) {
    // Calculate the hours
    $hours = floor($minutes / 60);
    
    // Calculate the remaining minutes
    $remainingMinutes = $minutes % 60;
    
    // Return the result as a string
    return sprintf("%d:%02d", $hours, $remainingMinutes);
}
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
        const airTripType = "<?php echo $airTripType; ?>";
        if (airTripType === 'Return') {
            $('#return').prop('checked', true);
        } else if (airTripType === 'OneWay') {
            $('#one-way').prop('checked', true);
        }

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

            // Check if the clicked tab is already active
            if (tab.hasClass('active')) {
                // If it's already active, hide it by removing the 'active' class
                tab.removeClass('active');
                tabPane.removeClass('active');
            } else {
                // If it's not active, show it by adding the 'active' class
                tabPanel.find('.active').removeClass('active'); // Remove 'active' class from all tabs and panels
                tab.addClass('active');
                tabPane.addClass('active');
            }
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


    // Define the showLoadingPopup() and hideLoadingPopup() functions
    // function showLoadingPopup() {
    //     $('#FlightSearchLoading').modal({
    //             show:true
    //         })
    // }
    // $('#FlightSearchLoading').modal('show');
    // window.addEventListener('load', function() {
    //   // Display the loading popup
    //   alert("hiiiiiii tesssss");
    //   $('#FlightSearchLoading').modal('show');

    //   // Add a delay before hiding the loading popup
    //   setTimeout(function() {
    //     $('#FlightSearchLoading').modal('hide');
    //   }, 3000); // 3000 milliseconds = 3 seconds (adjust the delay time as needed)
    // });

    $(document).ready(function() {
        <?php
        // if(empty($responseData['Data']['IsValid'])) {
            if(isset($responseData['Data']['Errors'])){
            //  echo '$("#errorMessage").text("' . $responseData['Message'] . '");';
            // echo "$('#errorModal').modal('show');";
            ?>
            // var errorMessage = <?php echo json_encode($responseData['Message']); ?>;
            // window.location.href = '404.php?error=' + encodeURIComponent(errorMessage);
            <?php
        }

        ?>
        var errorMessage = <?php echo json_encode($responseData['Message']); ?>; // Encode PHP message to JavaScript variable

        function redirectToErrorPage(message) {
            $('#errorModal').modal('hide');
            window.location.href = '404.php?error=' + encodeURIComponent(errorMessage);
        }

        $('#closeButton, #closeButton1').click(redirectToErrorPage);
    
    });

    // When button with ID 'modify-search-result-btn' is clicked
    $('#modify-search-result-btn').click(function() {
        $('#modify-search-result').slideToggle();
    });

 
</script>
<!-- ============ To remove cickable behaviour of radio buttons for airtrip type selection on top ==== -->
<style>
    /* input[type="radio"]:not(:checked) + label {
        pointer-events: none;
    } */
</style>
</body>

</html>