<?php
session_start();
ini_set('display_errors', 0);

include_once('includes/common_const.php');
require_once("includes/header.php");
require_once('includes/dbConnect.php');
$airport_depart = getAirPortLocationsByAirportCode($_SESSION['search_values']['airport'], $conn);
$airport_arrival = getAirPortLocationsByAirportCode($_SESSION['search_values']['arrivalairport'], $conn);

// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$url = "https://v6.exchangerate-api.com/v6/82190c2eeaf28578f89f52d7/latest/INR";
$response = file_get_contents($url);
$usd_converion_rate = 1;
if ($response !== false) {
    $data = json_decode($response, true); // Decode JSON to associative array
    $usd_converion_rate = $data['conversion_rates']['USD'];
} else {
    echo "Failed to retrieve data.";
}

include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();

$airport_country = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code LIKE :code');
$airport_country->bindParam(':code', $_SESSION['search_values']['airport']);
$airport_country->execute();
$AP_country_name_fetch = $airport_country->fetch(PDO::FETCH_ASSOC);
$AP_country_id = $AP_country_name_fetch['id'];


if (isset($_SESSION['Revalidateresponse']) && $_SESSION['Revalidateresponse'] != '') {

    $responseData = $_SESSION['Revalidateresponse'];
    $logRes =   print_r($responseData, true);
    $logReQ =   print_r($requestData, true);
    $objBook->_writeLog('-------------' . date('l jS \of F Y h:i:s A') . '-------------', 'revalidate.txt');
    $objBook->_writeLog('Request Received\n' . $logReQ, 'revalidate.txt');
    $objBook->_writeLog('fsCode Received for MF:\nfsc' . $fsCode, 'revalidate.txt');
    $objBook->_writeLog('REsponse Received\n' . $logRes, 'revalidate.txt');
    $pricedItineraries = $responseData['Data']['PricedItineraries'];
    if (isset($responseData['Data']['Errors']) && !empty($responseData['Data']['Errors'])) {
        require_once('includes/error_found.php');
    } else if (empty($responseData['Data']['PricedItineraries'])) {
        require_once('includes/price_itinary_empty.php');
    } else {?>
        <section class="bg-070F4E" style="margin-bottom: 18px;">
            <div class="container p-3">

                <input type="hidden" name="api_country_id" id="ap_country_id" value="<?php echo $AP_country_id; ?>" />

                <div class="row justify-content-center">
                    <div class="col-md-9">
                        <div class="steps-bar-title white-txt fw-500 text-md-center">Book your Flight in 3 Simple Steps</div>
                        <div class="process-wrap active-step1">
                            <div class="process-main">
                                <div class="row justify-content-center">
                                    <div class="col-md-3 position-relative">
                                        <div class="process-step-cont">
                                            <div class="process-step step-1"></div>
                                            <!-- <span class="process-label"><span class="position-relative">Review Booking<button>(Edit)</button></span></span> -->
                                            <span class="process-label"><span class="position-relative">Flight Details</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 position-relative">
                                        <div class="process-step-cont">
                                            <div class="process-step step-2"></div>
                                            <span class="process-label"><span class="position-relative">Traveller Details<button>(Edit)</button></span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 position-relative">
                                        <div class="process-step-cont">
                                            <div class="process-step step-3"></div>
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
                                <li><a href="index" style="text-decoration: underline !important;">Home</a></li>
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
                    <div class="col-md-8">
                        <div class="booking-step-container booking-step-1">
                            <!-- <h2 class="title-typ2 mb-3 text-center">Flight Details</h2> -->
                            <?php
                            foreach ($pricedItineraries as $pricedItinerary) {
                                $originDestinations = $pricedItinerary['OriginDestinationOptions'];
                                $_SESSION['name-character-count'] = $pricedItinerary['PaxNameCharacterLimit'];
                            ?>

                                <div class="booking-step mb-4">
                                    <div class="col-md-12 dark-blue-txt fw-500 p-0">
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

                                        <p style="color: #000000;font-size: 14px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;padding: 5px 10px;background-color: #f57c0078;padding: 12px; flex-wrap: wrap;">
                                            <img class="flight_icon_small" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg" />
                                            <strong>Departure</strong>
                                            <span class="ml-3">
                                                <?php echo $originData; ?>
                                                <span class="right-arrow-small arrow-121E7E mr-1"></span>
                                                <?php echo $destinationData . " " . date("d F Y", strtotime($date)); ?>
                                            </span>

                                            <span class="fw-500 " style="display: block;float: right;right: 18px;text-transform: capitalize;font-size: 15px; text-align:right; width:100%;">
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
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-right fs-15 fw-300"></div>
                                    <div class="">
                                        <?php

                                        foreach ($originDestinations as $index => $originDestination) {
                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                            $code = '%' . $pricedItinerary['ValidatingAirlineCode'] . '%';
                                            $stmtairline->bindParam(':code', $code);
                                            $stmtairline->execute();
                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                            $flightSegments = $originDestination['FlightSegments'];

                                            if ($index == 0) {
                                                foreach ($flightSegments as $index => $flightSegment) {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code'); ?>
                                                    <ul class="form-row fs-15" style="justify-content: space-around;align-items: center;display: flex;">
                                                        <li class="col-md-3 mb-md-0 mb-2">
                                                            <span class="airImg airline-<?php echo $pricedItinerary['ValidatingAirlineCode']; ?>" style="display:block;"></span>
                                                            <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                                            Flight No - <?php echo $flightSegment['FlightNumber'] . " " . $flightSegment['CabinClassText'] ?>
                                                        </li>
                                                        <li class="col-xl-8 col-md-8">
                                                            <div class="d-flex form-row justify-content-between align-items-center" style="    word-break: break-word;">
                                                                <div class="col-md-4 mb-md-0 mb-2 text-md-right">
                                                                    <?php
                                                                    $datetime = $flightSegment['DepartureDateTime'];
                                                                    list($date, $time) = explode("T", $datetime);
                                                                    $stmtlocation->execute(array('airport_code' => $flightSegment['DepartureAirportLocationCode']));
                                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <strong class="fw-500 d-block"><?php echo $flightSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                    <?php echo date("d F Y", strtotime($date)) . " <br />  " . $airportLocation['airport_name'] . ", " . $airportLocation['city_name'] . " " . $airportLocation['country_name'] ?>
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
                                                                    <strong class="fw-500 d-block"><?php echo $flightSegment['ArrivalAirportLocationCode'] . " " . $time; ?></strong>
                                                                    <?php echo date("d F Y", strtotime($date)) . " <br />  " . $airportLocation['airport_name'] . ", " . $airportLocation['city_name'] . " " . $airportLocation['country_name'] ?>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <?php
                                                    if (isset($flightSegments[$index + 1])) {
                                                    ?>
                                                        <ul class="form-row fs-15" style="justify-content: center;align-items: center;display: flex;">
                                                            <li class="col-md-3"></li>
                                                            <li class="col-xl-8 col-md-8">
                                                                <div class="d-flex form-row justify-content-center align-items-center layover-duration pt-3 pb-3">
                                                                    <div class="col-xl-8 col-md-8">
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
                                                <?php
                                                }
                                            } else {
                                                ?>

                                                <!-- <div class="h5 mb-3">Return</div> -->

                                                <div class="col-md-12 dark-blue-txt fw-500 p-0 mt-3">
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

                                                    <p style="color: #000000;font-size: 14px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;padding: 5px 10px;background-color: #f57c0078;padding: 12px; flex-wrap: wrap;">
                                                        <img class="flight_icon_small_return" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg" />
                                                        <strong>Return</strong>
                                                        <span class="ml-3">
                                                            <?php echo $originData; ?>
                                                            <span class="right-arrow-small arrow-121E7E mr-1"></span>
                                                            <?php echo $destinationData . " " . date("d F Y", strtotime($date)); ?>
                                                        </span>

                                                        <span class="fw-500 " style="display: block;float: right;right: 18px;text-transform: capitalize;font-size: 15px; text-align:right; width:100%;">
                                                            Total Duration:
                                                            <?php
                                                            $origin_total_duration = 0;
                                                            foreach ($flightSegments as $index => $flightSegment) {
                                                                $origin_total_duration += $flightSegment['JourneyDuration'];
                                                            }
                                                            echo convertMinutesToTimeFormat($origin_total_duration);
                                                            ?>
                                                        </span>
                                                    </p>


                                                </div>
                                                <?php
                                                foreach ($flightSegments as $index => $flightSegment) {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');


                                                ?>

                                                    <ul class="form-row fs-15" style="justify-content: space-around;align-items: center;display: flex;">
                                                        <li class="col-md-3">
                                                            <span class="airImg airline-<?php echo $pricedItinerary['ValidatingAirlineCode']; ?>" style="display:block;"></span>
                                                            <strong class="fw-500 d-block"><?php echo $airlineLocation['name'] ?></strong>
                                                            Flight No - <?php echo $flightSegment['FlightNumber'] . " " . $flightSegment['CabinClassText'] ?>
                                                        </li>
                                                        <li class="col-xl-8 col-md-8">
                                                            <div class="d-flex form-row justify-content-between align-items-center" style="word-break: break-word;">
                                                                <div class="col-md-4 mb-md-0 mb-2 text-md-right">
                                                                    <?php
                                                                    $datetime = $flightSegment['DepartureDateTime'];
                                                                    list($date, $time) = explode("T", $datetime);
                                                                    $stmtlocation->execute(array('airport_code' => $flightSegment['DepartureAirportLocationCode']));
                                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <strong class="fw-500 d-block"><?php echo $flightSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                    <?php echo date("d F Y", strtotime($date)) . " <br />  " . $airportLocation['airport_name'] . ", " . $airportLocation['city_name'] . " " . $airportLocation['country_name'] ?>
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
                                                                    <?php echo date("d F Y", strtotime($date)) . " <br />  " . $airportLocation['airport_name'] . ", " . $airportLocation['city_name'] . " " . $airportLocation['country_name'] ?>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <?php
                                                    if (isset($flightSegments[$index + 1])) {
                                                    ?>
                                                        <ul class="form-row fs-15" style="justify-content: center;align-items: center;display: flex;">
                                                            <li class="col-md-3"></li>
                                                            <li class="col-xl-8 col-md-8">
                                                                <div class="d-flex form-row justify-content-center align-items-center layover-duration pt-3 pb-3">
                                                                    <div class="col-xl-8 col-md-8">
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
                                    <p class="fs-14 fw-300 mt-2">
                                        <!-- Note: </strong>You will have to change Airport while travelling -->
                                    </p>
                                </div>
                            <?php
                            }
                            ?>

                        </div>
                    </div>
                    <div class="col-md-4 p-0" style="border: 1px solid #fac187;">
                        <div class="details-popup-section booking-step-1" style="display: flex;justify-content: space-around;align-items: center;">
                            <!-- Button trigger modal -->

                            <button type="button" id="fareRuleApi" class="btn fs-10 text-decoration" data-toggle="modal" data-target="#FareRules" data-value="<?php echo $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'] ?>" data-count-value="<?php echo count($originDestinations[0]['FlightSegments']) ?>" style="font-weight: bold;">
                                <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 5.01808L7.30882 0.145527C7.45104 0.0506388 7.61818 0 7.78915 0C7.96012 0 8.12726 0.0506388 8.26949 0.145527L15.5783 5.01808V17.1345C15.5783 17.3641 15.4871 17.5842 15.3248 17.7465C15.1625 17.9088 14.9424 18 14.7128 18H0.865462C0.635927 18 0.415793 17.9088 0.253488 17.7465C0.0911823 17.5842 0 17.3641 0 17.1345V5.01808ZM7.78915 8.47992C8.24822 8.47992 8.68849 8.29756 9.0131 7.97295C9.33771 7.64834 9.52008 7.20807 9.52008 6.749C9.52008 6.28993 9.33771 5.84966 9.0131 5.52505C8.68849 5.20044 8.24822 5.01808 7.78915 5.01808C7.33008 5.01808 6.88982 5.20044 6.56521 5.52505C6.2406 5.84966 6.05823 6.28993 6.05823 6.749C6.05823 7.20807 6.2406 7.64834 6.56521 7.97295C6.88982 8.29756 7.33008 8.47992 7.78915 8.47992ZM4.32731 12.8072V14.5382H11.251V12.8072H4.32731ZM4.32731 10.2108V11.9418H11.251V10.2108H4.32731Z" fill="#000" />
                                </svg>
                                <span class="ml-2">Fare Rules</span>
                            </button>
                            <button type="button" class="btn fs-10 text-decoration" data-toggle="modal" data-target="#BaggageDetails" style="font-weight: bold;">
                                <svg width="14" height="20" viewBox="0 0 14 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0.833496C9.50458 0.83335 9.99057 1.00804 10.3605 1.32254C10.7305 1.63703 10.9572 2.0681 10.995 2.52933L11 2.66683V4.50016H12C12.5046 4.50002 12.9906 4.6747 13.3605 4.9892C13.7305 5.3037 13.9572 5.73477 13.995 6.196L14 6.3335V16.4168C14 16.9031 13.7893 17.3694 13.4142 17.7132C13.0391 18.057 12.5304 18.2502 12 18.2502C11.9997 18.4838 11.9021 18.7085 11.7272 18.8784C11.5522 19.0483 11.313 19.1505 11.0586 19.1642C10.8042 19.1779 10.5536 19.102 10.3582 18.9521C10.1627 18.8021 10.0371 18.5894 10.007 18.3574L10 18.2502H4C4 18.4933 3.89464 18.7264 3.70711 18.8983C3.51957 19.0703 3.26522 19.1668 3 19.1668C2.73478 19.1668 2.48043 19.0703 2.29289 18.8983C2.10536 18.7264 2 18.4933 2 18.2502C1.49542 18.2503 1.00943 18.0756 0.639452 17.7611C0.269471 17.4466 0.0428434 17.0156 0.00500021 16.5543L1.00268e-07 16.4168V6.3335C-0.000159579 5.87097 0.190406 5.42548 0.533497 5.08633C0.876588 4.74718 1.34684 4.53944 1.85 4.50475L2 4.50016H3V2.66683C2.99984 2.2043 3.19041 1.75881 3.5335 1.41966C3.87659 1.08051 4.34685 0.872769 4.85 0.83808L5 0.833496H9ZM5 8.16683C4.73478 8.16683 4.48043 8.26341 4.29289 8.43532C4.10536 8.60722 4 8.84038 4 9.0835V13.6668C4 13.9099 4.10536 14.1431 4.29289 14.315C4.48043 14.4869 4.73478 14.5835 5 14.5835C5.26522 14.5835 5.51957 14.4869 5.70711 14.315C5.89464 14.1431 6 13.9099 6 13.6668V9.0835C6 8.84038 5.89464 8.60722 5.70711 8.43532C5.51957 8.26341 5.26522 8.16683 5 8.16683ZM9 8.16683C8.75507 8.16686 8.51866 8.24929 8.33563 8.39849C8.15259 8.54768 8.03566 8.75327 8.007 8.97625L8 9.0835V13.6668C8.00028 13.9005 8.09788 14.1252 8.27285 14.2951C8.44782 14.465 8.68695 14.5672 8.94139 14.5809C9.19584 14.5946 9.44638 14.5187 9.64183 14.3688C9.83729 14.2188 9.9629 14.0061 9.993 13.7741L10 13.6668V9.0835C10 8.84038 9.89464 8.60722 9.70711 8.43532C9.51957 8.26341 9.26522 8.16683 9 8.16683ZM9 2.66683H5V4.50016H9V2.66683Z" fill="#000" />
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

                                                                        $roleId         =   $user['role']; // taken from earlier set user array 



                                                                    } else {

                                                                        $roleId         =   1; // if not logged in now ,taking user's role markup'

                                                                    }
                                                                    //===============
                                                                    // echo '<pre/>';
                                                                    // print_r($pricedItinerary);
                                                                    $penaltyCancel = $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['PenaltiesInfo'][0];
                                                                    if ($penaltyCancel['Allowed'] == 1) {
                                                                        $Penaltymarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE 	cancel_type =4 AND status=1 AND role_id = :role_id');

                                                                        $Penaltymarkup->execute(array('role_id' => $roleId));

                                                                        $markupPenaltyInfo = $Penaltymarkup->fetch(PDO::FETCH_ASSOC);

                                                                        if (!empty($penaltyCancel['Amount'])) {

                                                                            $markupPenaltyPercentage = (($markupPenaltyInfo['commission_percentage'] / 100) *  $penaltyCancel['Amount']) * $usd_converion_rate;
                                                                        }

                                                                        //===============

                                                                        $penaltyAmount = $penaltyCancel['Amount'] * $usd_converion_rate;
                                                                        $totDisplay =   $penaltyAmount + $markupPenaltyPercentage;
                                                                        $penaltyCurrency = $penaltyCancel['CurrencyCode'];
                                                                    } else {

                                                                        $penaltyAmount  =   "Not Refundable";
                                                                    }
                                                                    ?>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td> <?php
                                                                                if (is_numeric($penaltyAmount)) {
                                                                                    echo "$ " . round($penaltyAmount, 2);
                                                                                } else {
                                                                                    echo $penaltyAmount;
                                                                                }
                                                                                ?></td>
                                                                    </tr>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Bulatrips Fee</td>
                                                                        <?php if ($penaltyCancel['Allowed'] == 1) { ?>
                                                                            <td class="p-1"><?php echo "$ " . round($markupPenaltyPercentage, 2); ?>
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
                                                                        $penaltyChangeAmount = $penaltyChange['Amount'] * $usd_converion_rate;
                                                                        $penaltyChangeCurrency = $penaltyChange['CurrencyCode'];
                                                                    } else {

                                                                        $penaltyChangeAmount    =   "Date Change Not Allowed";
                                                                    }



                                                                    //===================

                                                                    $DateChangemarkup = $conn->prepare('SELECT * FROM markup_commission_refund WHERE cancel_type =0 AND status=2 AND role_id = :role_id');

                                                                    $DateChangemarkup->execute(array('role_id' => $roleId));

                                                                    $DateChangemarkupInfo = $DateChangemarkup->fetch(PDO::FETCH_ASSOC);




                                                                    $markupDatechangePercentage = 0;
                                                                    if (!empty($penaltyChange['Amount'])) {

                                                                        $markupDatechangePercentage = ($DateChangemarkupInfo['commission_percentage'] / 100) * $penaltyChange['Amount'];
                                                                    }



                                                                    //===============

                                                                    ?>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td> <?php
                                                                                if (is_numeric($penaltyChangeAmount)) {
                                                                                    echo "$ " . round($penaltyChangeAmount, 2);
                                                                                } else {
                                                                                    echo $penaltyChangeAmount;
                                                                                }


                                                                                ?></td>
                                                                    </tr>
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Bulatrips Fee</td>
                                                                        <td> <?php echo "$ " . round($markupDatechangePercentage * $usd_converion_rate, 2); ?></td>
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

                                                                    <li class="col-4"><?php echo  $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['BaggageInfo'][$index] ?></li>

                                                                </ul>
                                                                <ul class="row">
                                                                    <li class="col-4">Cabin</li>
                                                                    <!-- <li class="col-4">1 pcs/person</li> -->
                                                                    <li class="col-4">
                                                                        <?php if (strtolower($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index]) == "sb") {
                                                                            echo "Standard Baggage";
                                                                        } else {
                                                                            echo $pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'][0]['CabinBaggageInfo'][$index];
                                                                        } ?>
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


                        <div class="p-2" style="font-size: 14px;">
                            <ul class="bdr-b" style="font-weight: normal;">
                                <!-- <li class="d-flex justify-content-between bdr-b pt-2 pb-2" style="font-weight: bold;">
                                    <strong class="fs-14 fw-600">Flight fare breakdown</strong>
                                </li> -->
                                <!-- <b><span><?php //echo $_SESSION['adultCount'] +  $_SESSION['childCount'] + $_SESSION['infantCount'];  ?> Passenger(s)</span></b> -->
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

                                foreach ($pricedItinerary['AirItineraryPricingInfo']['PTC_FareBreakdowns'] as $fareInformation) {
                                    $code_abb = "";
                                    if ($fareInformation['PassengerTypeQuantity']['Code'] === 'ADT') {
                                        $_SESSION['adultCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                        $code_abb = "Adult(s)";
                                    } elseif ($fareInformation['PassengerTypeQuantity']['Code'] === 'CHD') {
                                        $_SESSION['childCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                        $code_abb = "Child(s)";
                                    } else {
                                        $_SESSION['infantCount'] = $fareInformation['PassengerTypeQuantity']['Quantity'];
                                        $code_abb = "Infant(s)";
                                    }
                                    /* ?>
                                    <li class="d-flex justify-content-between">
                                        <span style="font-weight:normal;"><?php echo $code_abb; ?></span>
                                        <span><?php echo "($" . $fareInformation['PassengerFare']['EquivFare']['Amount'] . " x " . $fareInformation['PassengerTypeQuantity']['Quantity'] . ")"; ?></span>
                                        <span><?php echo "$" . number_format($fareInformation['PassengerFare']['EquivFare']['Amount'] * $fareInformation['PassengerTypeQuantity']['Quantity'], 2) ?></span>
                                    </li>
                                <?php */} ?>

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
                                <!-- <li class="d-flex justify-content-between"><span>Airline Taxes & Charges</span><span><?php //echo "$" . $totalTaxesdata; ?></span></li> -->





                                <!-- <li class="d-flex justify-content-between bdr-t pt-2 pb-2" style="font-weight: bold;"><span>Total Base Airline Fare</span><span>
                                        <?php
                                        //echo "$" . $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                        ?>
                                    </span>
                                <li class="d-flex justify-content-between bdr-t pt-2 pb-2" style="font-weight: bold;"><span>Bulatrips Platform Discount</span><span>-$0.00
                                    </span>
                                <li class="d-flex justify-content-between bdr-t bdr-b pt-2 pb-2" style="font-weight: bold;"> -->
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
                                    $markupPercentage = $markup['commission_percentage']?>

                                    <!-- <span>Bulatrips Platform fee(<?php //echo $markup['commission_percentage'] . "%"; ?>)</span><span>
                                        <?php
                                        //echo "$" . number_format($markupPercentage, 2);
                                        ?>

                                    </span> -->
                                </li>


                                <?php
                                    $stmt = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
                                    $stmt->bindValue(':key', "ipg_transaction_percentage");
                                    $stmt->execute();
                                    $setting = $stmt->fetch(PDO::FETCH_ASSOC);

                                    $ipg_percentage = 0;
                                    if( isset($setting['value']) && $setting['value'] != '' ) {
                                        $ipg_percentage = $setting['value'];
                                    }
                                    
                                    $ticketing_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
                                    $ticketing_fee->bindValue(':key', "ticketing_fee");
                                    $ticketing_fee->execute();
                                    $ticketing_fee_setting = $ticketing_fee->fetch(PDO::FETCH_ASSOC);

                                    $ticketing_fee = 0;
                                    if( isset($ticketing_fee_setting['value']) && $ticketing_fee_setting['value'] != '' ) {
                                        $ticketing_fee = $ticketing_fee_setting['value'];
                                    }
                                    
                                    ?>

                                <!-- <li class="d-flex justify-content-between bdr-t bdr-b pt-2 pb-2" style="font-weight: bold;"><span>Credit Card bank transaction fee(<?php //echo $ipg_percentage;?>%)</span><span> -->
                                        <?php
                                        $totalFareAPI = $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                        
                                        echo "Base: ". $totalFareAPI."<br />";

                                        $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                        $markupPercentage += $ticketing_fee;
                                        $total_price = $markupPercentage + $totalFareAPI;
                                        $ipg_trasaction_percentage = ($ipg_percentage / 100) * $total_price;

                                        echo "Commission + Ticketing + IPG Transaction fee: ". $ipg_trasaction_percentage + $markupPercentage;
                                        echo "<br />";

                                        $total_price += $ipg_trasaction_percentage;
                                        echo $total_price;
                                        echo "<br />";

                                        ?>

                                    <!-- </span></li> -->

                            </ul>

                            <!-- display: flex;justify-content: space-between;padding: 15px 10px; -->
                            <ul style="background-color: #2c3e50;color: #FFF;font-size: 25px;">
                                <li class="d-flex justify-content-between mt-1" style="justify-content: center !important;">
                                    <strong class="fw-600">Total Fare Price</strong>
                                </li>
                                <li class="d-flex justify-content-between mt-1" style="justify-content: center !important;">
                                    <strong class="fw-600 main_price_view">
                                        <?php
                                        // echo $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
                                        $_SESSION['session_total_amount'] = $total_price;
                                        echo "$" . number_format(round($total_price, 2), 2);
                                        ?>
                                    </strong>
                                    <br />
                                </li>
                                <li><p style="font-size: 14px;text-align: center;padding: 12px;">Total Price in USD (Including all taxes & fees)</p></li>
                            </ul>
                            


                            <?php
                            /*
                            if (isset($_SESSION['user_id'])) { ?>
                                <button id="travellerContinueButton" style="visibility: hidden;" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4" data-value="<?php echo $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'] ?>"
                                    data-adult="<?php echo $_SESSION['adultCount'] ?>" data-child="<?php echo $_SESSION['childCount'] ?>" data-infant="<?php echo $_SESSION['infantCount'] ?>"
                                    <?php if ($userData['role'] == 2) {
                                        echo ' data-uid="' . $_SESSION['user_id'] . '&&total=' . $Totalamount . '"';
                                    } ?>>Continue to Traveller Details</button>
                            <?php
                            } else { ?>
                                <button type="button" data-toggle="modal" data-target="#LoginModal" class="btn btn-typ3 fs-14 fw-500 pl-4 pr-4" style="display: block;">Login to continue</button>
                            <?php
                            }
                            */
                            ?>
                            <button id="travellerContinueButton" style="visibility: hidden;" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4" data-value="<?php echo $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'] ?>" data-adult="<?php echo $_SESSION['adultCount'] ?>" data-child="<?php echo $_SESSION['childCount'] ?>" data-infant="<?php echo $_SESSION['infantCount'] ?>">Continue to Traveller Details</button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="container">
                <div id="loginbookflight">
                    <!-- <strong class="d-block fw-500 pt-md-4 pb-md-4 pt-3 pb-3">Before Booking! Sign in</strong> -->

                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <div id="logindiv">
                            <?php
                            /*
                            <div class="mb-4 fw-300">
                                You are loged in with below id. <br>
                                <?php
                                $stmtuser = $conn->prepare('SELECT * FROM users WHERE id = :user_id');

                                $stmtuser->execute(array('user_id' => $_SESSION['user_id']));
                                $userData = $stmtuser->fetch(PDO::FETCH_ASSOC);

                                $_SESSION['customer_role-id'] = $userData['role'];
                                ?>
                                <strong class="fw-500"><?php echo $userData['email'] ?></strong>
                            </div>
                            <button id="travellerContinueButton" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4" data-value="<?php echo $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'] ?>" 
                                data-adult="<?php echo $_SESSION['adultCount'] ?>" data-child="<?php echo $_SESSION['childCount'] ?>" data-infant="<?php echo $_SESSION['infantCount'] ?>"
                                <?php if ($userData['role'] == 2) { echo ' data-uid="' . $_SESSION['user_id'] . '&&total=' . $Totalamount . '"'; } ?> >CONTINUE</button>
                                */
                            ?>
                        </div>
                    <?php } else { ?>

                        <!-- <div id="login-form" style="display: block;"> -->
                            <!-- <form method="post" action="" id="booking-user-login"> -->
                                <!-- <div class="form-row pb-lg-3 pb-2 "> -->
                                    <!-- <div class="col-md-3 mb-md-0 mb-2">
                                        <input type="text" name="loginemail" id="loginemail" class="form-control" placeholder="Email">
                                    </div>
                                    <div class="col-md-3 mb-md-0 mb-2">
                                        <input type="password" name="loginpassword" id="loginpassword" class="form-control" placeholder="Password">
                                    </div>
                                    <button type="submit" name="bookinguserlogin" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Login to Continue</button> -->

                                    <!-- <button id="loginButton" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">CONTINUE</button> -->
                                    <!-- <button id="travellerLoginButton" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">CONTINUE</button> -->

                                <!-- </div> -->
                            <!-- </form> -->
                        <!-- </div> -->
                        <!-- <a href="registration?searchFlights=true" class="fs-14 text-below-button" target="_blank">New User ? Click Here to <span class="fw-600">Register</span></a> -->
                    <?php } ?>

                    <input type="hidden" value="<?php echo @$_SESSION['user_id'];?>" name="user_id_loggedin" id="user_id_loggedin" />
                    
                    <div class="mt-2"></div>

                    <div class="traveller-details row" id="travellerDetails" <?php //if (isset($_SESSION['user_id'])) {echo 'style="display: block;"';} else {echo 'style="display: none;"';} ?>>

                        <?php $extraSrvice = $responseData['Data']['ExtraServices1_1']['Services']; ?>
                        <input type="hidden" id="extraSrviceData" value="<?php echo htmlentities(json_encode($extraSrvice)); ?>">

                        

                        <form action="" method="post" id="booking-submit" style="margin-bottom: 150px;">
                            <div class="col-lg-12">
                                <div class="col-lg-12 p-0" style="border: 1px solid #fac187;">
                                    <p style="color: #000000;font-size: 18px;text-transform: capitalize;background-color: #f57c0078;padding: 10px;    margin-bottom: 0;">
                                        <strong>Traveller Details: <span style="font-weight: normal; font-size:12px;"><br />Please enter the details exactly as they appear in the passport:</span></strong>
                                    </p>
                                    <div id="adultcontainermain">
                                        <div id="adultcontainer">
                                        </div>
                                        <div id="childcontainer">
                                        </div>
                                        <div id="infantcontainer">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="title-typ3 dark-blue-txt fw-500 mt-2"></div>
                                <div class="col-lg-12">
                                    <div class="col-lg-12 p-0" style="border: 1px solid #fac187;">
                                        <p style="color: #000000;font-size: 18px;text-transform: capitalize;background-color: #f57c0078;padding: 10px;    margin-bottom: 0;"><strong>Contact Details</strong></p>

                                        <div class="form-row pb-lg-3 pb-2 align-items-center p-2">

                                        <?php
                                        if (isset($_SESSION['user_id'])) {
                                            $id = $_SESSION['user_id'];
                                            $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
                                            $stmt->execute(array('id' => $id));
                                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                                            $first_name = $user['first_name'];
                                            $last_name = $user['last_name'];
                                            $country = $user['country'];
                                            $mobile = $user['mobile'];
                                            $email = $user['email'];
                                            $zip_code = $user['zip_code'];
                                        }
                                        ?>

                                            <!-- <div class="col mb-md-0 mb-2">
                                                <label for="" class="m-0 fw-500">Contact Details</label>
                                            </div> -->
                                            <div class="col-md-2 mb-4">
                                                <input type="text" name="contactfirstname" id="contactfirstname" class="form-control" placeholder="First Name" value="<?php echo $first_name;?>">
                                            </div>
                                            <div class="col-md-2 mb-4">
                                                <input type="text" name="contactlastname" id="contactlastname" class="form-control" placeholder="Last Name" value="<?php echo $last_name;?>">
                                            </div>
                                            <div class="col-md-2 mb-4">
                                                <?php
                                                $jsonData = file_get_contents('CountryCodes.json');

                                                // Parse the JSON data into an array
                                                $data = json_decode($jsonData, true);

                                                // Check if the JSON data was parsed successfully
                                                if ($data !== null) {
                                                    // Start creating the select box HTML
                                                    $selectBox = '<select name="contactcountry" id="contactcountry" class="form-control">';

                                                    // Iterate over the data array and create options
                                                    foreach ($data as $item) {
                                                        $name = $item['name'];
                                                        $dialCode = $item['dial_code'];
                                                        $code = $item['code'];
                                                        if( $country == $code ) {
                                                            $option = "<option selected value=\"$dialCode\">$name ($dialCode)</option>";
                                                        } else {
                                                            // Create an option element with the country name and dial code
                                                            $option = "<option value=\"$dialCode\">$name ($dialCode)</option>";
                                                        }

                                                        // Append the option to the select box HTML
                                                        $selectBox .= $option;
                                                    }

                                                    // Close the select box HTML
                                                    $selectBox .= '</select>';

                                                    // Output the select box
                                                    echo $selectBox;
                                                } else {
                                                    // Handle the case when the JSON data couldn't be parsed
                                                    echo 'Error parsing JSON data.';
                                                }
                                                ?>
                                            </div>
                                            <div class="col-md-2 mb-4">
                                                <input type="text" name="contactnumber" id="contactnumber" class="form-control" placeholder="Mobile Number" value="<?php echo $mobile;?>">
                                            </div>
                                            <div class="col-md-2 mb-4">
                                                <input type="text" name="contactemail" id="contactemail" class="form-control" placeholder="Email Address" value="<?php echo $email;?>">
                                            </div>
                                            <div class="col-md-2 mb-4">
                                                <input type="text" name="contactpostcode" id="contactpostcode" class="form-control" placeholder="Postcode" value="<?php echo $zip_code;?>">
                                            </div>
                                            <?php

                                            ?>
                                            <!-- <div class="col mb-md-0 mb-2">
                                                    <select name="contactcountry" id="" class="form-control select-location">
                                                        <option value="India">India</option>
                                                    
                                                    </select>
                                                </div> -->
                                        </div>
                                        <?php $_SESSION['fsc'] = $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'];?>
                                        <input type="hidden" name="adultCount" value="<?php echo $_SESSION['adultCount'] ?>">
                                        <input type="hidden" name="childCount" value="<?php echo $_SESSION['childCount'] ?>">
                                        <input type="hidden" name="infantCount" value="<?php echo $_SESSION['infantCount'] ?>">
                                        <input type="hidden" name="depdate" value="<?php echo $_SESSION['travel-depdate'] ?>">
                                        <input type="hidden" name="returndate" value="<?php echo $_SESSION['travel-return-depdate'] ?>">
                                        <input type="hidden" name="nameCharacterCount" value="<?php echo  $_SESSION['name-character-count'] ?>">
                                        <input type="hidden" name="pricedItineraries" value="<?php echo htmlspecialchars(json_encode($responseData['Data']['PricedItineraries'])); ?>">
                                        <input type="hidden" name="custId" value="<?php echo $_SESSION['customer_role-id']; ?>">

                                        <?php
                                        /*
                                            <input type="hidden" name="data" value="<?php echo htmlspecialchars(json_encode($_SESSION['revalidationApi'])); ?>">
                                            <input type="hidden" name="extraServiceAmount" value="<?php echo htmlspecialchars(json_encode($_SESSION['totalService'])); ?>">
                                            <input type="hidden" name="Totalamount" value="<?php echo $Totalamount; ?>">
                                        */
                                        ?>

                                    </div>
                                </div>

                                <button class="btn btn-typ1" type="submit" style="margin-top: 20px; margin-right:20px; float:right;">Proceed to Payment</button>

                        </form>
                    </div>
                </div>
            </div>

        </section>

        <?php
        require_once("includes/login-modal.php");
        require_once("includes/forgot-modal.php");
    }
} else {
    require_once('includes/session_expired.php');
}
require_once("includes/footer.php");
?>
<input type="hidden" name="searchFlights" id="searchFlights" value="true" />

<div class="modal fade" id="how_to_proceed_login" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">How would you like to proceed?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Create an account to track your bookings, or continue as a guest.</p>

                <!-- Continue as Guest -->
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-outline-secondary btn-lg btn-block" id="continue_as_guest">Continue as Guest</button>
                    <small class="text-muted">You can book your flight without an account.</small>
                </div>

                <!-- Login -->
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-typ1 btn-primary btn-lg btn-block" id="continue_as_login">Login</button>
                    <small class="text-muted">Sign in to access your saved bookings and faster checkout.</small>
                    
                    <!-- <form method="post" action="" id="continue_as_login_form">
                        <div class="form-group">
                            <input type="email" class="form-control" name="loginemail" id="loginemail" aria-describedby="emailHelp" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="loginpassword" id="loginpassword" placeholder="Password">
                            <div class="forgot-passward">
                                <button type="button" class="fs-11" data-toggle="modal" data-target="#ForgotPasswordModal" style="border:none;background:none;color: #121E7E;">Forgot password ?</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="userlogin" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                        </div>
                    </form> -->

                </div>

                <!-- Register -->
                <div class="d-grid gap-2">
                    <button class="btn btn-typ7 btn-primary btn-lg btn-block" id="continue_as_register">Register</button>
                    <small class="text-muted">Create an account to manage your future bookings on the website and faster checkout.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<style>
.input-group-text {
    padding: .290rem .75rem;
}
</style>

<div class="modal flight-search-loading" id="payment_modal" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <form class="col-lg-12" method="post" action="" id="paymentFormCC">
                            <div class="form-row panel payment-options-tab">
                                <div class="tab-content col-md-12 fs-14 p-0">
                                    <div class="tab-pane pane1 active p-5" style="padding-top: 5px !important;">
                                        
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <center>
                                                    <table style="width:100%;margin:15px auto">
                                                        <tbody>
                                                            <tr>
                                                                <td style="text-align:center;">
                                                                    <h2 style="margin-top:0;margin-bottom:0">
                                                                        <img src="https://bulatrips.com/images/Bulatrips_logo_white.png" title="Bulatrip" style="height: 75px;">
                                                                    </h2>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <center>
                                                </div>

                                                <div class="col-12">
                                                    <ul style="background-color: #2c3e50;color: #FFF;font-size: 25px;border: 1px solid #FFF;border-radius: 5px;padding-top: 15px;">
                                                        <li class="d-flex justify-content-between mt-1" style="justify-content: center !important;">
                                                            <strong class="fw-600">Total Fare Price</strong>
                                                        </li>
                                                        <li class="d-flex justify-content-between mt-1" style="justify-content: center !important;">
                                                            <strong class="fw-600 main_price_view">
                                                                <?php echo "$" . number_format($_SESSION['session_total_amount'],2);?>
                                                            </strong>
                                                            <br />
                                                        </li>
                                                        <li><p style="font-size: 14px;text-align: center;padding: 12px;">Total Price in USD (Including all taxes & fees)</p></li>
                                                    </ul>
                                                </div>
                                            
                                                <!-- <div class="col-12">
                                                    <label for="username" class="mb-0">Card Holder name (exactly as on the card)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" id="custName" name="CardHolderName" maxlength="64" placeholder="Card Holder Name" value="Nafees">
                                                    </div>
                                                    <p class="error_paymentcust_name error_payment"></p>
                                                </div>

                                                <div class="col-12 mt-3">
                                                    <label for="cardNumber" class="mb-0">Card number</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="cardNo" name="CardNumber" placeholder="Card Number" value="4242424242424242">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text text-muted">
                                                                <i class="fab fa-cc-visa"></i> &nbsp; <i class="fab fa-cc-amex"></i> &nbsp; 
                                                                <i class="fab fa-cc-mastercard"></i> 
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="error_paymentcard_no error_payment"></p>
                                                </div>

                                                <div class="row col-12 m-0 mt-3">
                                                    <div class="col-sm-8 p-0 pr-3">
                                                        <div class="form-group">
                                                            <label><span class="hidden-xs">Expiration</span> </label>
                                                            <div class="form-inline">
                                                                <select class="form-control" style="width: 45%;" id="cardExpmon" name="ExpiryMonth" >
                                                                    <option value="">MM</option>
                                                                    <option value="01">01 - January</option>
                                                                    <option value="02">02 - February</option>
                                                                    <option value="03">03 - March</option>
                                                                    <option value="04">04 - April</option>
                                                                    <option value="05">05 - May</option>
                                                                    <option value="06">06 - June</option>
                                                                    <option value="07">07 - July</option>
                                                                    <option value="08">08 - August</option>
                                                                    <option value="09">09 - September</option>
                                                                    <option value="10">10 - October</option>
                                                                    <option value="11">11 - November</option>
                                                                    <option value="12">12 - December</option>
                                                                </select>                                    
                                                                
                                                                <span style="width:10%; text-align: center"> / </span>
                                                                
                                                                <select class="form-control" style="width: 45%;" id="cardExpyear" name="ExpiryYear" >
                                                                    <option value="">YYYY</option>
                                                                    <?php
                                                                    /*$currentYear = date("Y");
                                                                    for ($i = 0; $i <= 10; $i++) {
                                                                        $year = $currentYear + $i;
                                                                        echo "<option value=\"$year\">$year</option>";
                                                                    }*/
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <p class="error_paymentcardexpmon error_payment"></p>
                                                            <p class="error_paymentcard_expiry error_payment"></p>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 p-0">
                                                        <div class="form-group">
                                                            <label data-toggle="tooltip" title="" data-original-title="3 digits code on back side of the card">CVV</label>
                                                            <input class="form-control" type="text" id="cvv" name="Cvc2" >
                                                        </div>
                                                        <p class="error_paymentcvc error_payment"></p>
                                                    </div>
                                                </div> -->

                                            <div class="row col-12 m-0 mt-3 d-flex flex-column fs-15 mb-lg-4 mb-3 text-center">
                                                <!-- <strong class="fs-21 fw-700 mb-2">$<?php //echo number_format($_SESSION['session_total_amount'],2);?></strong> -->
                                                 <input type="hidden" name="Totalamount" value="<?php echo $_SESSION['session_total_amount']; ?>">
                                                <button class="btn btn-typ7 fs-15 fw-600 pt-2 pb-2 pb-1 pl-4 pr-4 submit_payment_btn" type="submit">
                                                    Proceed to Payment
                                                </button>
                                                <button type="button" class="btn close_payment_btn close mt-2 fs-15" data-dismiss="modal">Close</button>
                                                <p id="errorMessagePayment"></p>
                                            </div>
                                            <div id="loaderIcon"></div>

                                            <div class="row col-12 m-0 mt-3">
                                                <div class="col-md-12 text-center">
                                                    <img src="images/pay-icon-list.png" alt="">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            
                            <!-- <input type="hidden" name="bookingid" value="<?php echo $bookingData['id']; ?>"> -->
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <button type="button" id="closeButton1" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                <!--<button type="button" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Search Again</button> -->
            </div>
        </div>
    </div>
</div>


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

    ///==============Fare Rule load code =====================
    var buttonFare = document.getElementById("fareRuleApi");
    var value = buttonFare.getAttribute("data-value");
    var count = buttonFare.getAttribute("data-count-value");



    var formData = new FormData();
    formData.append('value', value);
    formData.append('count', count);

    $('#login_message').text("");
    $.ajax({
        url: 'farerule',
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response) {
                var fareRules = response.fareRules;
                fareRules.forEach(function(key, index) {
                    key['RuleDetails'].forEach(function(rule, index_inner) {
                        var category = rule.Category;
                        var rules = rule.Rules;
                        if (category === "PENALTIES") {
                            var rulesParagraph = rules;
                            $('#fareresult').append(rulesParagraph);
                        }
                    });

                });
            }
        }
    });
</script>
</body>
</html>