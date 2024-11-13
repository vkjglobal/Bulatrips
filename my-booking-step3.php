<?php
session_start();
require_once("includes/header.php");
require_once('includes/dbConnect.php');
if (isset($_SESSION['fsc'])) {
    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

    $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'], 'userid' => $_SESSION['user_id']));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);


    $stmtflightsegment = $conn->prepare('SELECT * FROM flight_segment WHERE booking_id = :bookingId');
    $stmtflightsegment->execute(array('bookingId' => $bookingData['id']));
    $flightsegmentData = $stmtflightsegment->fetchAll(PDO::FETCH_ASSOC);




?>
    <section class="bg-070F4E steps-indicator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-9">
                    <div class="steps-bar-title white-txt fw-500 text-md-center">Book your Flight in 4 Simple Steps</div>
                    <div class="process-wrap active-step4">
                        <div class="process-main">
                            <div class="row justify-content-center">
                                <div class="col-md-3 position-relative">
                                    <div class="process-step-cont">
                                        <div class="process-step step-1"></div>
                                        <span class="process-label"><span class="position-relative">Review Booking<button></button></span></span>
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
        <div class="container">
            <div class="form-row">
                <div class="col-12">
                    <h2 class="title-typ2 mb-3 mb-lg-5">My Booking</h2>
                    <div class="booking-step-container booking-step-3">
                        <?php
                        // foreach ($flightsegmentData as $flightsegmentDatas) {

                        $oneWaySegments = [];
                        $returnSegments = [];

                        foreach ($flightsegmentData as $flightsegmentDatas) {
                            if ($bookingData['air_trip_type'] === "OneWay") {
                                // Collect all segments for OneWay
                                $oneWaySegments[] = $flightsegmentDatas;
                            } elseif ($bookingData['air_trip_type'] === "Return") {
                                if (count($oneWaySegments) <= $bookingData['stops']) {
                                    // Collect the first 2 segments for Return based on stopsToDisplay
                                    $oneWaySegments[] = $flightsegmentDatas;
                                } else {
                                    // Collect the remaining segments for Return
                                    $returnSegments[] = $flightsegmentDatas;
                                }
                            }
                        }




                        foreach ($oneWaySegments as $oneWaySegmentsdata) {
                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                            $code = '%' . $oneWaySegmentsdata['airline_code'] . '%';
                            $stmtairline->bindParam(':code', $code);
                            $stmtairline->execute();
                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);



                        ?>
                            <div class="booking-step mb-4">
                                <ul class="form-row fs-15 mt-md-3 mt-2 pb-md-3 pb-2 bdr-b bdr-none">
                                    <li class="col-md-1 text-center mb-md-0 mb-2">
                                        
                                    <span class="airImg airline-<?php echo $oneWaySegmentsdata['airline_code']; ?>"></span>

                                    </li>
                                    <li class="col-md-3 mb-md-0 mb-2">
                                        <strong class="fw-500 d-block"><?php echo $airlineLocation['name']; ?></strong>
                                        Flight No - <span><?php echo $oneWaySegmentsdata['flight_no']; ?></span>
                                    </li>
                                    <li class="col-md-2 mb-md-0 mb-2">
                                        <strong class="fw-500"><?php echo $oneWaySegmentsdata['dep_location']; ?></strong> <?php echo $oneWaySegmentsdata['dep_date']; ?>
                                    </li>
                                    <li class="col-md-2 mb-md-0 mb-2">
                                        <strong class="fw-500"><?php echo $oneWaySegmentsdata['arrival_location']; ?></strong> <?php echo $oneWaySegmentsdata['arrival_date']; ?>
                                    </li>
                                    <li class="col-md-2 mb-md-0 mb-2">
                                        <div>
                                            <?php if ($bookingData['stops'] <= 0)
                                                echo "Direct";
                                            else
                                                echo $bookingData['stops'] . " " . "stop";
                                            ?>
                                        </div>
                                        <?php
                                        $date1 = $oneWaySegmentsdata['dep_date'];
                                        $date2 = $oneWaySegmentsdata['arrival_date'];
                                        $dateTime1 = new DateTime($date1);
                                        $dateTime2 = new DateTime($date2);

                                        // Calculate the difference between the two dates
                                        $interval = $dateTime1->diff($dateTime2);

                                        // Get the difference in hours and minutes
                                        $hours = $interval->h;
                                        $minutes = $interval->i;
                                        echo $hours . " hr " . $minutes . " m ";
                                        ?>
                                    </li>
                                    <li class="col-md-2 mb-md-0 mb-2">
                                        <!-- Extra service cost is added to the total fare here  -->
                                        <?php

                                        ?>
                                        <strong class="d-block light-blue-txt">&#36;<?php echo $bookingData['total_fare']; ?></strong>

                                    </li>
                                </ul>
                            </div>
                        <?php
                        }
                        ?>


                        <!-- Return -->
                        <span>Return</span>
                        <?php if ($bookingData['air_trip_type'] === "Return") {
                            foreach ($returnSegments as $returnSegmentsData) {
                                $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                $code = '%' . $returnSegmentsData['airline_code'] . '%';
                                $stmtairline->bindParam(':code', $code);
                                $stmtairline->execute();
                                $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                $stops = $bookingData['stops'];
                                $air_trip_type = $bookingData['air_trip_type'];


                        ?>
                                <div class="booking-step mb-4">
                                    <ul class="form-row fs-15 mt-md-3 mt-2 pb-md-3 pb-2 bdr-b bdr-none">
                                        <li class="col-md-1 text-center mb-md-0 mb-2">
                                            <?php if ($airlineLocation['image']) { ?>
                                                <img src="images/emirates-logo.png" alt="">
                                            <?php

                                            } else { ?>
                                                <img src="images/no-image-icon-1.jpg" alt="" style="max-height: 75px;">
                                            <?php

                                            } ?>
                                        </li>
                                        <li class="col-md-3 mb-md-0 mb-2">
                                            <strong class="fw-500 d-block"><?php echo $airlineLocation['name']; ?></strong>
                                            Flight No - <span><?php echo $returnSegmentsData['flight_no']; ?></span>
                                        </li>
                                        <li class="col-md-2 mb-md-0 mb-2">
                                            <strong class="fw-500"><?php echo $returnSegmentsData['dep_location']; ?></strong> <?php echo $returnSegmentsData['dep_date']; ?>
                                        </li>
                                        <li class="col-md-2 mb-md-0 mb-2">
                                            <strong class="fw-500"><?php echo $returnSegmentsData['arrival_location']; ?></strong> <?php echo $returnSegmentsData['arrival_date']; ?>
                                        </li>
                                        <li class="col-md-2 mb-md-0 mb-2">
                                            <div>
                                                <?php if ($bookingData['stops'] <= 0)
                                                    echo "Direct";
                                                else
                                                    echo $bookingData['stops'] . " " . "stop";
                                                ?>
                                            </div>
                                            <?php
                                            $date1 = $returnSegmentsData['dep_date'];
                                            $date2 = $returnSegmentsData['arrival_date'];
                                            $dateTime1 = new DateTime($date1);
                                            $dateTime2 = new DateTime($date2);

                                            // Calculate the difference between the two dates
                                            $interval = $dateTime1->diff($dateTime2);

                                            // Get the difference in hours and minutes
                                            $hours = $interval->h;
                                            $minutes = $interval->i;
                                            echo $hours . " hr " . $minutes . " m ";
                                            ?>
                                            <!-- 26hr 5m -->
                                        </li>
                                        <li class="col-md-2 mb-md-0 mb-2">
                                            <!-- Extra service cost is added to the total fare here  -->
                                            <?php

                                            ?>
                                            <strong class="d-block light-blue-txt">&#36;<?php echo $bookingData['total_fare']; ?></strong>

                                        </li>
                                    </ul>
                                </div>
                            <?php
                            }
                            ?>

                        <?php } ?>

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
                        $totalFareAPI = $bookingData['total_fare'];
                        // $markupPercentage = (($markup['commission_percentage'] / $totalFareAPI)*100);
                        $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                        ?>
                        <div class="d-flex justify-content-end h3">Total:&nbsp;<strong class="d-block light-blue-txt">&#36;<?php echo number_format(round($totalFareAPI + $markupPercentage, 2), 2); ?></strong></div>

                        <!-- <div class="booking-step">
                            <ul class="form-row fs-15 mt-md-3 mt-2">
                                <li class="col-md-1 text-center mb-md-0 mb-2">
                                    <img src="images/emirates-small-logo.png" alt="">
                                </li>
                                <li class="col-md-3 mb-md-0 mb-2">
                                    <strong class="fw-500 d-block">Emirates Airline</strong>
                                    Flight No - <span>EK 316</span>
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">DXB</strong> 13 Nov 08:40 
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <strong class="fw-500">KCZ</strong> 13 Nov 08:40
                                </li>
                                <li class="col-md-2 mb-md-0 mb-2">
                                    <div>1 stop</div>  
                                    24hr 5m
                                </li>
                              
                            </ul>
                        </div> -->
                    </div>
                    <div class="fs-18 fw-500 mb-3 mt-md-5 mt-4">Payment</div>
                    <div class="payment-options-box row no-gutters justify-content-center">
                        <!-- <form class="col-lg-11" method="post" id="payment-booking"> -->
                        <form class="col-lg-11" method="post" action="" id="payment-booking">
                            <!-- <div class="form-row justify-content-center align-items-center mb-3 mb-md-4">
                                <div class="col-md-4 text-md-right mb-md-0 mb-2">Avail Discounts & Offers</div>
                                <div class="col-md-4 mb-md-0 mb-2">
                                    <input type="text" class="form-control" placeholder="Promo Code">
                                </div>
                                <div class="col-md-4 mb-md-0 mb-2">
                                    <button class="btn btn-typ3 fs-15 fw-600 pt-1 pb-1 pl-4 pr-4">Apply Promo Code</button>
                                </div>
                            </div> -->
                            <div class="form-row panel payment-options-tab">
                                <ul class="nav nav-tabs col-md-3 d-flex flex-column w-100 p-0">
                                    <!-- <li class="nav-item active">
                                        <a class="nav-link">
                                            UPI
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 14L5 7L0 0H2.45L7.45 7L2.45 14H0ZM5.95 14L10.95 7L5.95 0H8.4L13.4 7L8.4 14H5.95Z" fill="#121E7E"/>
                                            </svg>    
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Net Banking
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 14L5 7L0 0H2.45L7.45 7L2.45 14H0ZM5.95 14L10.95 7L5.95 0H8.4L13.4 7L8.4 14H5.95Z" fill="#121E7E"/>
                                            </svg> 
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Debit Card
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 14L5 7L0 0H2.45L7.45 7L2.45 14H0ZM5.95 14L10.95 7L5.95 0H8.4L13.4 7L8.4 14H5.95Z" fill="#121E7E"/>
                                            </svg> 
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Credit Card
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 14L5 7L0 0H2.45L7.45 7L2.45 14H0ZM5.95 14L10.95 7L5.95 0H8.4L13.4 7L8.4 14H5.95Z" fill="#121E7E"/>
                                            </svg> 
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Gift Card
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 14L5 7L0 0H2.45L7.45 7L2.45 14H0ZM5.95 14L10.95 7L5.95 0H8.4L13.4 7L8.4 14H5.95Z" fill="#121E7E"/>
                                            </svg> 
                                        </a>
                                    </li> -->
                                </ul>
                                <div class="tab-content col-md-9 text-center fs-14 p-0">
                                    <div class="tab-pane p-lg-5 pt-4 p-3 pane1 active">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="d-flex flex-column flex-md-row align-items-center fs-14 mb-lg-4 mb-3">
                                                <!-- We Accept
                                                <img src="images/payment-icon-list.png" class="ml-3" alt=""> -->
                                            </div>
                                            <div class="d-flex align-items-center fs-14 mb-lg-4 mb-3">
                                                Card used for the payment needs to be of traveller.
                                            </div>
                                            <div class="row align-items-center mb-lg-4 mb-3">
                                                <!-- <div class="col-2 uppercase-txt">Card Holder name</div> -->
                                                <div class="col-10">
                                                    <label>Card Holder name</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <!-- <div class="col-2 uppercase-txt">Card Type</div> -->
                                                <div class="col-10">
                                                    <label>Card Type</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="col-10">
                                                    <label>Card Number</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="col-10">
                                                    <label>Card Expiry</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="col-10">
                                                    <label>csv/cvv Number</label>
                                                    <input type="text" class="form-control">
                                                </div>

                                            </div>
                                            <div class="form-row chkbx w-100 mb-lg-4 mb-3">
                                                <div class="col-12 text-left">
                                                    <input type="checkbox" id="checkTerms" checked="">
                                                    <label for="checkTerms" class="fz-13 fw-400 d-flex">
                                                        <span class="chk-txt fs-13 fw-400">I understand and agree with the rules and restrictions of this fare,the <a href="#" class="text-decoration">Booking Policy</a> and the <a href="#" class="text-decoration">Terms & Conditions</a> of Travel Site</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column fs-15 mb-lg-4 mb-3">
                                                Amount Due
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
                                                    $stmttraveller = $conn->prepare('SELECT * FROM travellers_details WHERE flight_booking_id = :bookingId');
                                                    $stmttraveller->execute(array('bookingId' => $bookingData['id']));
                                                    $travellerData = $stmttraveller->fetchAll(PDO::FETCH_ASSOC);
                                                    $extraService=0;
                                                    foreach($travellerData as $travellerDatas){
                                                        $totalextra =$travellerDatas['extrameal_amount']+$travellerDatas['extrabaggage_amount'];
                                                        $extraService +=$totalextra;
                                                    }
                                                    $totalFareAPI = $bookingData['total_fare'] ;
                                                    // $markupPercentage = (($markup['commission_percentage'] / $totalFareAPI)*100);
                                                    $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                ?>
                                                <strong class="fs-20 fw-700 light-blue-txt mb-2">&#36;<?php echo number_format(round($totalFareAPI+$markupPercentage+$extraService,2),2); ?></strong>
                                                <button class="btn btn-typ3 fs-15 fw-600 pt-1 pb-1 pl-4 pr-4">PAY NOW</button>
                                            </div>
                                        </div>
                                    </div>




                                </div>
                            </div>
                            <div class="row justify-content-end">
                                <div class="col-md-9 text-center mt-4">
                                    <img src="images/pay-icon-list.png" alt="">
                                </div>
                            </div>
                            <input type="hidden" name="bookingid" value="<?php echo $bookingData['id']; ?>">
                        </form>
                    </div>
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
} else {
?>
    <div>
        Something when wrong Search Again
    </div>
<?php
}
?>
<?php
require_once("includes/footer.php");
?>
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
</body>

</html>