<?php
 session_start();
if(!isset($_SESSION['adminid'])){
?>
<script>
    window.location="index.php"    </script>
    <?php
}
 include_once "includes/header.php";
include_once "includes/class.booking.php";
$objBooking		= 	new Booking();
if(isset($_GET['id'])){
   $bid             =   trim($_GET['id']);   
 $getBooking	=   $objBooking->getBookingInfo($bid);
// print_r($getBooking);
 $agent_name    =   $getBooking[0]['agentname'];
 $agent_email   =   $getBooking[0]['email'];
 $agent_mobile   = $getBooking[0]['dial_code']." " . $getBooking[0]['mobile'];
 $air_trip_type   =   $getBooking[0]['air_trip_type'];
 $total_fare   =   $getBooking[0]['total_fare'];

 $markup_amount   =   $getBooking[0]['markup'];
 $stops   =   $getBooking[0]['stops'];
  $booking_status   =   $getBooking[0]['booking_status'];
  $contactname   =   $getBooking[0]['contactname'];
  $contact_email   =   $getBooking[0]['contact_email'];
  $contact_number   =   $getBooking[0]['contact_number'];
  $contact_phonecode    =  $getBooking[0]['contact_phonecode'];
   $contact_number  =   $contact_phonecode." ". $contact_number;
  $booking_date   =   $getBooking[0]['booking_date'];
  $timestamp = strtotime($booking_date);

// Convert the timestamp to the desired date format
$formattedDate = date("D F jS Y", $timestamp);
  $travellerscount    =   count($getBooking);
  //Flight details 
 $getFlightsInfo	=   $objBooking->getFlightInfo($bid);
 $flightCount       =   count($getFlightsInfo);
  //echo "<pre/>";print_r( $flightCount);
} 



//====


/* ------
SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,contact_first_name,contact_last_name,contact_email, contact_phonecode,contact_number,b.created_at FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id WHERE b.id=125;
==============================================
SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,contact_first_name,contact_last_name,contact_email, contact_phonecode,contact_number,b.created_at,
 f.dep_location,f.arrival_location,f.dep_date,f.arrival_date,f.flight_no,f.airline_code,f.cabin_preference,a.name 
 FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id
 JOIN flight_segment AS f ON b.id =f.booking_id 
 JOIN airline AS a ON f.airline_code LIKE CONCAT('%', a.code, '%')
WHERE b.id=125;
====================================================

SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,CONCAT(b.contact_first_name, ' ', b.contact_last_name),b.contact_email,b.contact_phonecode,b.contact_number,b.created_at,
 f.dep_location,f.arrival_location,f.dep_date,f.arrival_date,f.flight_no,f.airline_code,f.cabin_preference,t.passenger_type,t.title	,CONCAT(t.first_name, ' ', t.last_name),t.ticket_no,t.extrameal_amount,t.extrabaggage_amount
 FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id
 JOIN flight_segment AS f ON b.id =f.booking_id 
 JOIN travellers_details AS t ON b.id = t.flight_booking_id   
WHERE b.id=125;

======================================================================

SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,CONCAT(b.contact_first_name, ' ', b.contact_last_name) AS contactname,b.contact_email,b.contact_phonecode,b.contact_number,b.created_at, f.dep_location,f.arrival_location,f.dep_date,f.arrival_date,f.flight_no,f.airline_code,f.cabin_preference,t.passenger_type,t.title ,CONCAT(t.first_name, ' ', t.last_name),t.ticket_no,t.extrameal_amount,t.extrabaggage_amount FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id INNER JOIN travellers_details AS t ON b.id = t.flight_booking_id JOIN flight_segment AS f ON b.id =f.booking_id  WHERE b.id=11;
===============================
SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,CONCAT(b.contact_first_name, ' ', b.contact_last_name) AS contactname,b.contact_email,b.contact_phonecode,b.contact_number,b.created_at,t.id AS trvId,t.passenger_type,t.title ,CONCAT(t.first_name, ' ', t.last_name),t.e_ticket_number,t.extrameal_amount,t.extrabaggage_amount FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id LEFT JOIN travellers_details AS t ON b.id = t.flight_booking_id WHERE b.id=11;

=====================================

, f.id AS flightsegId,f.dep_location,f.arrival_location,f.dep_date,f.arrival_date,f.flight_no,f.airline_code,f.cabin_preference,
--------- */

// $listmarkup	=   $objBooking->markupList();
    ?>
    <!-- Flight Booking Details Start -->
<div class="container-fluid pt-4 px-4 w-100">
    <div class="border-primary text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Flight Booking Details of Agent</strong>
        </div>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="row justify-content-between">
                    <div class="col-xl-5 col-md-6 col-12 text-start">
                        <div class="row mb-2">
                            <div class="col-5">Agent Name:</div>
                            <div class="col-7"><?php echo $agent_name; ?></div>
                        </div>                       
                        <div class="row mb-2">
                          <div class="col-5"> Agent Email:</div>
                            <div class="col-7"><?php echo $agent_email; ?></div>
                        </div> 
                         <div class="row mb-2">                            
                            <div class="col-5"> Agent Phone:</div>
                            <div class="col-7"><?php echo $agent_mobile; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Contact Name:</div>
                            <div class="col-7"><?php echo $contactname; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Contact Email:</div>
                            <div class="col-7"><?php echo $contact_email;?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Contact Number:</div>
                            <div class="col-7"><?php echo $contact_number; ?></div>
                        </div>
                        </div>
                         <div class="col-md-6 col-12 text-start">                       
                      
                        <div class="row mb-2">
                            <div class="col-5">Booking Status:</div>
                            <div class="col-7"><?php echo $booking_status; ?></div>
                        </div>
                                       
                      
                   <div class="row mb-2">
                            <div class="col-5">Booked Date :</div>
                            <div class="col-7"><?php echo $formattedDate; ?></div>
                        </div> 
                        <div class="row mb-2">
                            <div class="col-5">Stops:</div>
                            <div class="col-7"><?php echo $stops; ?></div>
                        </div> 
                          <div class="row mb-2">
                            <div class="col-5">Total Fare:</div>
                            <div class="col-7"><?php echo $total_fare; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5">Air Trip Type:</div>
                            <div class="col-7 custom-radio">
                                <input type="radio" id="tab1" name="AirTripType"  <?php if ($air_trip_type === "OneWay") echo 'checked'; ?>>
                                <label for="tab1">Oneway</label>
                                <input type="radio" id="tab2" name="AirTripType"  <?php if ($air_trip_type === "Return") echo 'checked'; ?>>
                                <label for="tab2">Round Trip</label>
                                <input type="radio" id="tab3" name="AirTripType"  <?php if ($air_trip_type === "MultiCity") echo 'checked'; ?>>
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
                            <th scope="col" class="sl-number">Flight No & Name</th>
                            <th scope="col" class="">From</th>
                            <th scope="col" class="">To</th>
                            <th scope="col" class="">Cabin Preference</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($getFlightsInfo as $k =>$val){ 
                        $flightNO   =   $val['flight_no']; 
                        $flightname =    $val['airline_code'];
                        $cabin_preference   =   $val['cabin_preference'];
                        $fromloc   =   $val['dep_location'];
                        $dep_date   =   $val['dep_date'];
                         $timestamp1 = strtotime($dep_date);
// Convert the timestamp to the desired date format
                        $dep_date = date("D, F jS Y", $timestamp1);
                        $dep_time = date("H:i", $timestamp1);
                        //get airport from location
                         $getAirportInfo	=   $objBooking->getAirportInfo($fromloc);
                         $Airportcity       =   $getAirportInfo[0]['city_name'];
                         $AirportName       =   $getAirportInfo[0]['airport_name'];
                                  //get airport To  location
                                  $Toloc    =   $val['arrival_location'];
                        $arrival_date   =   $val['arrival_date'];
                         $timestamp2 = strtotime($arrival_date);
// Convert the timestamp to the desired date format
                        $arrival_date = date("D, F jS Y", $timestamp2);
                        $arrival_time = date("H:i", $timestamp2);
                        //get airport from location
                         $getAirportInfo	=   $objBooking->getAirportInfo($Toloc);
                         $AirportTocity       =   $getAirportInfo[0]['city_name'];
                         $AirportToName       =   $getAirportInfo[0]['airport_name'];

                          //get airline name
                         $getAirlinenfo	=   $objBooking->getAirlinenfo($flightname);
                         $AirlineName      =   $getAirlinenfo[0]['name'];
                                  //=====
                       //print_r($getAirlinenfo);
                       
                    
                 
                   ?>
                        <tr>
                            <td><?php echo $flightNO; ?><br>
                            <?php echo $AirlineName; ?></td>
                            <td>
                                <?php echo $fromloc." ".$dep_time; ?><br>
                               <?php echo $dep_date; ?><br>
                               <?php echo $Airportcity." - ".$AirportName; ?>
                            </td>
                            <td>
                                <?php echo $arrival_time." ".$Toloc; ?><br>
                                <?php echo $arrival_date;?><br>
                                <?php echo $AirportTocity." - ". $AirportToName;?>
                            </td>
                            <td><?php echo $cabin_preference;?></td>
                        </tr>
                        <?php } ?>
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
                             <th>Passenger Type</th>
                            <th>Ticket No.</th>
                             <th colspan="2">FreeBaggage (Check In + Cabin)</th>
                            <th colspan="3">Fare:(Basic+Tax)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($getBooking as $key=>$values)
                    {
                        $passengerName      =   $values['title']." ". $values['traveller_name'];
                          $passengerType    =   $values['passenger_type'];
                          $ticketNo         =   $values['e_ticket_number'];
                          if($passengerType == "ADT"){
                              $passengerType    =   "ADULT";

                          }
                          elseif($passengerType == "CHD"){
                              $passengerType    =   "CHILD";
                          }
                          elseif($passengerType =="INF"){
                              $passengerType    =   "INFANT";
                          }
                          $freeCheckinBag           =   $values['free_checkin_baggage'];
                          $free_cabin_baggage       =   $values['free_cabin_baggage'];
                          $basic_fare               =   $values['basic_fare'];
                          $tax                      =   $values['tax'];
                          $trv_fare   =   $values['total_pass_fare'];
                       // print_r($values);
                    ?>
                        <tr>
                            <td><?php  echo $passengerName; ?></td>
                             <td><?php  echo $passengerType; ?></td>
                            <td><?php  echo $ticketNo; ?></td>
                             <td><?php  echo $freeCheckinBag; ?></td>
                            <td><?php  echo $free_cabin_baggage; ?></td>
                            <td><?php  echo $basic_fare; ?></td>
                            <td><?php  echo $tax; ?></td>
                            <td><?php  echo $trv_fare; ?></td>
                        </tr>
                        <?php } ?>
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
                     <?php  
                     $extramealservAmount   =0;
                      $extrabaggageAmount   =0;
                      $serviceTotal =0;
                      $extramealservAmountTotal =0;
                      $extrabaggageAmountTotal  =0;
                     foreach($getBooking as $key=>$values)
                     { 
                       
                         
                          $passengerName      =   $values['title']." ". $values['traveller_name'];
                         $extramealservAmount   =   $values['extrameal_amount'];
                         $extrabaggageAmount    =   $values['extrabaggage_amount'];
                         $extrameal_description    =   $values['extrameal_description'];
                         $extrabaggage_description    =   $values['extrabaggage_description']; 
                          // Accumulate the amounts
                            $extramealservAmountTotal += $extramealservAmount;
                            $extrabaggageAmountTotal += $extrabaggageAmount;

                         if(!empty($extrabaggageAmount)) {
                         ?>
                        <tr>
                       
                            <td><?php echo  $passengerName; ?></td>
                           
                            <td>Baggage</td>
                            <td><?php echo $extrabaggage_description; ?></td>
                            <td><?php echo $extrabaggageAmount; ?></td>                           
                            
                        </tr>
                         <?php } ?>
                        <?php 
                         if(!empty($extramealservAmount)){?>
                             <tr>
                          <td><?php echo  $passengerName; ?></td>
                           
                            <td>Meals</td>
                            <td><?php echo $extrameal_description; ?></td>
                            <td><?php echo $extramealservAmount; ?></td>
                              </tr>
                             <?php }  ?>
                          <?php }
                          $serviceTotal =   $extramealservAmountTotal+$extrabaggageAmountTotal;
                          $netfare      =   $total_fare + $markup_amount + $serviceTotal;
                          ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-start"><strong>Payment Details</strong></h6>
            <div class="text-start">
                <ul class="ps-0">
                   
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
                        <div class="col-6">Total Booking Fare:</div>
                        <div class="col-6"><?php echo $total_fare; ?></div>
                    </li>
                    <li class="row">
                        <div class="col-6">Total Service Charges:</div>
                        <div class="col-6"><?php echo $serviceTotal; ?></div>
                    </li>
                    <li class="row">
                        <div class="col-6">Markup Fare for Total amount:</div>
                        <div class="col-6"><?php echo $markup_amount; ?></div>
                    </li>
                    <li class="row">
                        <div class="col-6 h4">Net fare (Total Fare + service charges + MArkup Fee):</div>
                        <div class="col-6 h4">$<?php echo  $netfare; ?></div>
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