<?php 
session_start();
error_reporting(0);

if(isset($_SESSION['user_id'])){
?>
   <script>
//    window.location="index.php"    </script>
   <?php
}
else {
    require_once("includes/header.php");
    include('includes/dbConnect.php');
    $bookingId = $_GET['booking_id'];

    ?>
    <section>
        <div class="container">
            <h2 class="title-typ2 my-4">Cancel Flight</h2>
            <div class="row my-4">
                <div class="col-12 text-center">
                    <form  class="" method="post" action="" id="cancel-booking">
                        <div class="mb-3">
                            <h6 class="text-left fw-700">Select Travellers</h6>
                            <div class="row">
                                <div class="col-lg-3 col-12 text-left mb-2">
                                    <strong class="fs-14">All Travellers:</strong>
                                </div>
                                <div class="col-lg-9 col-12">
                                    <ul class="form-row">
                                        <?php 
                                        
                                            // $stmtpassenger = $conn->prepare('SELECT * FROM travellers_details WHERE flight_booking_id = :bookingId');
                                            $stmtpassenger = $conn->prepare('SELECT travellers_details.id, travellers_details.first_name, travellers_details.last_name FROM travellers_details 
                                            LEFT JOIN temp_booking ON travellers_details.flight_booking_id = temp_booking.id
                                            WHERE travellers_details.flight_booking_id = :bookingId and temp_booking.user_id = :userId');
                                                        
                                            $stmtpassenger->execute(array('bookingId' => $bookingId,'userId' => $_SESSION['user_id']));
                                            $passengerDetail = $stmtpassenger->fetchAll(PDO::FETCH_ASSOC);
                                            foreach($passengerDetail as $index => $passengerDetails) { ?>
                                            <li class="col-lg-3 col-md-4 col-6 form-group chkbx mb-2">
                                                <input type="checkbox" id="traveller<?php echo $index + 1; ?>" name="passengers[]" value="<?php echo $passengerDetails['id']; ?>">
                                                <label for="traveller<?php echo $index + 1; ?>" class="fz-13 fw-400">
                                                    <span class="chk-txt fs-13 fw-400"><?php echo $passengerDetails['first_name'] . " " . $passengerDetails['last_name']; ?></span>
                                                </label>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="table-responsive mb-3">
                            <h6 class="text-left fw-700">Pick an alternate date</h6>
                            <table class="table table-bordered white-bg text-left fs-14" style="min-width: 500px;">
                                <thead>
                                    <tr class="dark-blue-bg white-txt">
                                        <th style="width: 20px;">
                                            <div class="chkbx">
                                                <input type="checkbox" id="changeDateAll">
                                                <label for="changeDateAll" class="mb-0"></label>
                                            </div>
                                        </th>
                                        <th style="width: 33%;"></th>
                                        <th style="width: 33%;">Booked Date</th>
                                        <th style="width: 33%;">New Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <div class="chkbx">
                                                <input type="checkbox" class="chkbox" id="changeDate1">
                                                <label for="changeDate1" class="mb-0"></label>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;">COK - DXB</td>
                                        <td style="vertical-align: middle;">18/08/2023</td>
                                        <td style="vertical-align: middle;">
                                            <input type="date" class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <div class="chkbx">
                                                <input type="checkbox" class="chkbox" id="changeDate2">
                                                <label for="changeDate2" class="mb-0"></label>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;">DXB - COK</td>
                                        <td style="vertical-align: middle;">28/08/2023</td>
                                        <td style="vertical-align: middle;">
                                            <input type="date" class="form-control">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> -->
                        <input type="hidden" value="<?php echo $bookingId;?>" name="booking-id">
                        <button type="submit" class="btn btn-typ3 mb-3">Cancel</button>
                        <!-- <div class="row fs-13 mb-3 px-3">
                            <div class="col-12 px-0">
                                <h6 class="text-left fw-700">Available Flights</h6>
                            </div>
                            <div class="col-12 light-border">
                                <ul class="flight-list">
                                    <li>
                                        <ul class="row titlebar">
                                            <li class="col-md-2 text-center">Airline</li>
                                            <li class="col-md-1">Depart</li>
                                            <li class="col-md-2">Stops</li>
                                            <li class="col-md-2">Arrive</li>
                                            <li class="col-md-3">Duration</li>
                                            <li class="col-md-2 text-center">Price</li>
                                        </ul>
                                    </li>
                                    <li class="pt-4 contentbar">
                                        <ul class="row mb-lg-5 mb-3">
                                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2"><img src="images/emirates-logo.png" alt=""></li>
                                            <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                                <div class="">
                                                    11:45 KCZ
                                                </div>
                                                <div class="">
                                                    11:45 KCZ
                                                </div>
                                            </li>
                                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    1 Stop
                                                    HND 9hr 30min
                                                </div>
                                                <div>
                                                    1 Stop
                                                    HND 9hr 30min
                                                </div>
                                            </li>
                                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    05:30 DXB
                                                </div>
                                                <div>
                                                    05:30 DXB
                                                </div>
                                            </li>
                                            <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    22hr 45m
                                                </div>
                                                <div>
                                                    22hr 45m
                                                </div>
                                            </li>
                                            <li data-th="Price" class="main-dtls col-md-2 d-flex flex-column align-items-md-center mb-md-0 mb-2">
                                                <div class="price-dtls mb-md-0 mb-2">Rs. <strong>154964</strong></div>
                                                <button class="btn btn-typ3 w-100">BOOK CHANGE</button>
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
                                                                            <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"/>
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
                                    <li class="pt-4 contentbar">
                                        <ul class="row mb-lg-5 mb-3">
                                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2"><img src="images/emirates-logo.png" alt=""></li>
                                            <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                                <div class="">
                                                    11:45 KCZ
                                                </div>
                                                <div class="">
                                                    11:45 KCZ
                                                </div>
                                            </li>
                                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    1 Stop
                                                    HND 9hr 30min
                                                </div>
                                                <div>
                                                    1 Stop
                                                    HND 9hr 30min
                                                </div>
                                            </li>
                                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    05:30 DXB
                                                </div>
                                                <div>
                                                    05:30 DXB
                                                </div>
                                            </li>
                                            <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                                <div>
                                                    22hr 45m
                                                </div>
                                                <div>
                                                    22hr 45m
                                                </div>
                                            </li>
                                            <li data-th="Price" class="main-dtls col-md-2 d-flex flex-column align-items-md-center mb-md-0 mb-2">
                                                <div class="price-dtls mb-md-0 mb-2">Rs. <strong>154964</strong></div>
                                                <button class="btn btn-typ3 w-100">BOOK CHANGE</button>
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
                                                                            <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595"/>
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
                                                <div class="tab-pane p-lg-5 pt-5 p-3 pane2">
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
                            </div>
                        </div> -->
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
  
    
    
   
  
    <div id="scrollToTop"><span>Go Up</span></div>
    <?php
        require_once("includes/footer.php");
   
}
   ?>
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

        $(document).ready(function(){
            /******************TAB WITHOUT ID*******************************/
            $('.panel .nav-tabs').on('click', 'a', function(e){
                var tab  = $(this).parent(),
                    tabIndex = tab.index(),
                    tabPanel = $(this).closest('.panel'),
                    tabPane = tabPanel.find('.tab-pane').eq(tabIndex);
                tabPanel.find('.active').removeClass('active');
                tab.addClass('active');
                tabPane.addClass('active');
            });
            $('.tab-pane').on('click', 'button', function(e){
                $(this).parent(".tab-pane").removeClass("active");
                $(this).parents(".tab-content").siblings(".nav-tabs").children(".nav-item").removeClass("active");
            });
            /***************************************************************/ 
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
        /**************************Check All***********************/
        var checkAll = document.getElementById('changeDateAll');
        var checkboxes = document.getElementsByClassName('chkbox');

        // checkAll.addEventListener('change', function () {
        //     for (var i = 0; i < checkboxes.length; i++) {
        //         checkboxes[i].checked = checkAll.checked;
        //     }
        // });
        /*********************************************************/
    </script>
</body>

</html>