<?php
 session_start();
if(!isset($_SESSION['adminid'])){
?>
<script>
    window.location="index.php"    </script>
    <?php
}
 include_once "includes/header_listing.php";
include_once "includes/class.booking.php";
// $listmarkup	=   $objBooking->markupList();
    ?>
    <!-- Flight Booking Details Start -->
<div class="container-fluid pt-4 px-4 w-100">
    <div class="border-primary text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Flight Booking Details</strong>
        </div>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="row justify-content-between">
                    <div class="col-xl-5 col-md-6 col-12 text-start">
                        <div class="row mb-2">
                            <div class="col-5">Name:</div>
                            <div class="col-7">Sreejith</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Email:</div>
                            <div class="col-7">sreejith.reubro@gmail.com</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Phone:</div>
                            <div class="col-7">9946666612</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Booking Status:</div>
                            <div class="col-7">Booked</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Booking Status:</div>
                            <div class="col-7">Booked</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 text-start">
                        <div class="row mb-2">
                            <div class="col-5">User Type:</div>
                            <div class="col-7">Agent</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Total Fare:</div>
                            <div class="col-7"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Payment Status:</div>
                            <div class="col-7"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Payment Status:</div>
                            <div class="col-7"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Air Trip Type:</div>
                            <div class="col-7 custom-radio">
                                <input type="radio" id="tab1" name="AirTripType" checked="">
                                <label for="tab1">Oneway</label>
                                <input type="radio" id="tab2" name="AirTripType">
                                <label for="tab2">Round Trip</label>
                                <input type="radio" id="tab3" name="AirTripType">
                                <label for="tab3">Multi City</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-start"><strong>Flight Details</strong></h6>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="">
                            <th scope="col" class="sl-number">Flight No.</th>
                            <th scope="col" class="">Origin/Destination</th>
                            <th scope="col" class="">Departure/Arrival</th>
                            <th scope="col" class="">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>G9449</td>
                            <td>
                                BLR  07:50 <br>
                                Fri, 18 Aug 2023 <br>
                                Bangalore - Kempegowda International Airport Terminal T1
                            </td>
                            <td>
                                12:05  DEL <br>
                                Fri, 18 Aug 2023 <br>
                                New Delhi - Indira Gandhi Airport Terminal T1
                            </td>
                            <td>Booked</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-start"><strong>Traveller List</strong></h6>
            <div class="table-responsive">
                <table class="table table-bordered white-bg text-start fs-14" style="min-width: 800px;">
                    <thead>
                        <tr class="dark-blue-bg white-txt">
                            <th>Passenger Name(s)</th>
                            <th>Ticket No.</th>
                            <th>Fare</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sreejith</td>
                            <td>U4F2RF|A7Y7YW</td>
                            <td>3,857</td>
                        </tr>
                        <tr>
                            <td>Sooraj</td>
                            <td>U4F2RF|A7Y7YW</td>
                            <td>3,857</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-start"><strong>Additional Service Details</strong></h6>
            <div class="table-responsive">
                <table class="table table-bordered white-bg text-start fs-14" style="min-width: 800px;">
                    <thead>
                        <tr class="dark-blue-bg white-txt">
                            <th>Passenger Name(s)</th>
                            <th colspan="2">Service</th>
                            <th>Fare</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sreejith</td>
                            <td>Baggage</td>
                            <td>30Kg</td>
                            <td>0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-start"><strong>Payment Details</strong></h6>
            <div class="text-start">
                <ul class="ps-0">
                    <li class="row">
                        <div class="col-6">Passenger Name:</div>
                        <div class="col-6">Sreejith</div>
                    </li>
                    <li class="row">
                        <div class="col-6">Auth ID:</div>
                        <div class="col-6">042567</div>
                    </li>
                    <li class="row">
                        <div class="col-6">Payment Type:</div>
                        <div class="col-6">Card Payment-Visa</div>
                    </li>
                    <li class="row">
                        <div class="col-6">Payment Date:</div>
                        <div class="col-6">Tue, 07 Jul 2023</div>
                    </li>
                </ul>
                <div class="mb-2"><strong>Fare breakup</strong></div>
                <ul class="ps-0">
                    <li class="row">
                        <div class="col-6">Base Fare:</div>
                        <div class="col-6">Rs.7,715</div>
                    </li>
                    <li class="row">
                        <div class="col-6">Other Charges:</div>
                        <div class="col-6">Rs.2,184</div>
                    </li>
                    <li class="row">
                        <div class="col-6">Discount And Cashbacks:</div>
                        <div class="col-6">Rs.-625</div>
                    </li>
                    <li class="row">
                        <div class="col-6 h4">Net fare (base + other charges):</div>
                        <div class="col-6 h4">Rs.9,274</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
  <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>