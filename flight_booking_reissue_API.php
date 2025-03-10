<?php 
 /* error_reporting(0);
ini_set('display_errors', 0); */
session_start();
$_SESSION['user_id'] =9;
if(!isset($_SESSION['user_id'])){ //for test  environment 
?>
   <script>
   window.location="index.php"    </script>
   <?php
}
else {
    //=========================================================================================
     
    require_once("includes/header.php");   
    include_once('includes/class.cancel.php');
       $bookingId = $_GET['booking_id'];
           $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
           $bookingId   =   trim($bookingId);
            $userId     =   $_SESSION['user_id'];
         $currentTimestamp = time();

    //    $bookingId  =   122;
       //  $userId    =   9;
    
        $objCancel     =   new Cancel();
         $bookCanusers      =   $objCancel->BookCancelUsers($bookingId,$userId); 
          $childpsnger        = $bookCanusers[0]['child_count'];
            if($childpsnger === 0){
        $allow_child    =   false;
    }
    elseif($childpsnger > 0){
        $allow_child    =   true;
    }
    //var_dump( $allow_child);
    //   print_r($bookCanusers);exit;
      
     //preticketed cancel need only one row value to check ticketed or not 
    


//==================================================================================



    ?>
    <section>
        <div class="container">
            <h2 class="title-typ2 my-4"></h2>
            <div class="row my-4">
                <div class="col-12 text-center">
                    <form action="" class="">
                    <!-- pre ticket booking cancel starts
                    1.user under booked status and not in ticketed status 
                    2.within ticktime limit 
                    -->
                     <div class="mb-3">
                            <h6 class="text-left fw-700">Do you want to change your journey?</h6>
                      </div>
                  
                            </div>
                        </div>
                        <div class="table-responsive mb-3">
                            <h6 class="text-left fw-700">Select Travellers</h6>
                            <table class="table table-bordered white-bg text-left fs-14" style="min-width: 500px;">
                                <thead>
                                    <tr class="dark-blue-bg white-txt">
                                        <th style="width: 20px;">
                                            <div class="chkbx">
                                                <input type="checkbox" id="changeDateAll">
                                                <label for="changeDateAll" class="mb-0"></label>
                                            </div>
                                        </th>
                                         <th style="width: 33%;">Passenger Name</th>
                                        <th style="width: 33%;">Ticket Status</th>
                                       
                                        <th style="width: 33%;">Departure Date</th>
                                      
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i = 0;
                                foreach($bookCanusers as $key =>$val){
                                    $i++;
                                    //*******************************************
                                     $pre_booking_status        =   $val['booking_status'];
                                     $pre_ticket_time_limit     =   $val['ticket_time_limit'];
                                     $pre_mf_reference          =  $val['mf_reference'];
                                     $pre_ticket_status         =   $val['ticket_status'];
                                     $fare_type                  =   $val['fare_type'];
                                     $VoidingWindow             =    $val['void_window'];
                                     $precancelsts  =   0;
                                    //echo $pre_mf_reference;exit;
                                     //***************************
                                   // $pre_mf_reference  =   "MF23675423";
                                  // $VoidingWindow	    =  "2023-08-01T16:29:59.997";	
                                  $VoidingWindow_limit =   strtotime($VoidingWindow);

     //***************************

                             // Convert the given date to a timestamp
                             $pre_ticket_time_limit = strtotime($pre_ticket_time_limit);
                             // Get the current timestamp
                              //popn up for ticketnprocess
                           //echo $pre_ticket_status;
                           if(($fare_type == "Public") || ($fare_type == "Private")){
                             if($pre_booking_status ==  trim("Booked")){ 
                                 if($pre_ticket_status  ==  trim("TktInProcess")){
                                    $ticktinprocess_msg =   "Your Ticketing is in process .Cannot Go back .Once it finished you can move with  your cancellation ";
                                         echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                            echo '    $("#Ticketinprocess").modal("show");';
                            echo '});';
                            echo '</script>';
                                      //   echo "KK";exit;

                                     //need to wait till ticked state to get cancelled 
                                 }       
                              else if(($pre_ticket_status  !=  trim("Ticketed"))  &&  ($pre_ticket_status  !=  trim("TktInProcess")) &&  ($pre_ticket_status  !=  trim("cancelled"))) {
                               //      pre ticket cancel api          
                                     // Check if the _ticket_time_limit date is not expired
                                     if($pre_ticket_time_limit > $currentTimestamp) {
                                           $precancelsts  =   1;
                                            //  echo "The date is not expired.";
                                                //2023-07-09 09:39:00
                                     } //if tick time limit is over ,ie either ticket autocancelled or goes to ticketed state inbetween

                                 }
                                 else if($pre_ticket_status  ==  trim("Ticketed"))
                                 { //if under ticketed state void/refund apis 
             
                                     if($VoidingWindow_limit > $currentTimestamp) {
                                           $precancelsts  =   0;
                                       $void_eligible   =   1;
                                   }
                                   else{
                                       //refund api
                                        $void_eligible   =   0;
                                   }
                                     //code for ticketed cancel PTR apis
                                     //user cancelled on same day of ticket issuance (within voidwindow time)
                                 }
                                  else if($pre_ticket_status  ==  trim("cancelled")){
                                                 $ticktinprocess_msg =   "Your Ticket is Already Cancelled";
                                         echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                            echo '    $("#Ticketinprocess").modal("show");';
                            echo '});';
                            echo '</script>';
                                            }
                             }
                             else{
                                 //only booked "status" tickets can be cancelled 
                                 $ticktinprocess_msg =   "Your Ticket is Not Under Booked Status .Cannot Move for Cancellation ";
                                         echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                            echo '    $("#Ticketinprocess").modal("show");';
                            echo '});';
                            echo '</script>';

                             }

                           }
                       else if($fare_type == "WebFare"){
                           $ticktinprocess_msg =   "Your Ticket is WEb Fare Type .Cannot Move for Cancellation ";
                                         echo '<script>';
                            echo 'document.addEventListener("DOMContentLoaded", function() {';
                            echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                            echo '    $("#Ticketinprocess").modal("show");';
                            echo '});';
                            echo '</script>';
                           //webfare type not eligble for cancellation 
                       }
  // var_dump($void_eligible);exit;
 // echo  $void_eligible .$fare_type;exit;

   //rint_r($bookCanusers);echo "hi";exit;
$objCancel->closeConnection();
// This will close the database connection as well
                                    //******************************************
                                    $checkboxId =   "changeDate".$i;
                                     $passenger_name  =   $val['title']." ".$val['first_name']." ".$val['last_name'];
                                     $dep_date     =   $val['dep_date'];
                                     $dateTime      = new DateTime($dep_date);
                                    $formattedDate = $dateTime->format('d F Y, H:i');
                                     ?>
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <div class="chkbx">
                                                <input type="checkbox" class="chkbox" id="<?php echo $checkboxId; ?>"
                                                data-firstname="<?php echo $val['first_name']; ?>"
                                                data-lastname="<?php echo $val['last_name']; ?>"
                                                data-title="<?php echo $val['title']; ?>"                                                
                                                data-eticket="<?php echo $val['e_ticket_number']; ?>"
                                                data-passengertype="<?php echo $val['passenger_type']; ?>">
                                                <label for="<?php echo $checkboxId; ?>" class="mb-0"></label>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;"><?php echo  $passenger_name ;?></td>
                                        <td style="vertical-align: middle;"><?php echo  $pre_ticket_status;?></td>
                                        <td style="vertical-align: middle;"><?php echo $formattedDate;?></td>
                                       
                                    </tr>
                                    <?php } ?>
                                   
                                </tbody>
                            </table>
                        </div>
                         <input type="hidden" id="precancelValue" value="<?php echo $pre_mf_reference; ?>">
                       <input type="hidden" id="bookingId" value="<?php echo $bookingId; ?>">
                        <input type="hidden" id="USerid" value="<?php echo $userId; ?>">
                         <input type="hidden" id="allowchildP" value="<?php echo $allow_child; ?>">
                        
                     <button type="button" class="btn btn-typ3 mb-3"  id="search">Search alternate flight</button>  
                     
 <div class="row fs-13 mb-3 px-3">
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
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
    <div class="modal reg-log-modal" id="LoginModal" tabindex="-1" role="dialog" aria-labelledby="LoginModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 fw-400" id="loginModalLongTitle">Welcome to the <strong
                            class="fw-500">Bulatrips</strong></h5>
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
                                    <input type="email" class="form-control" id="loginInputEmail1"
                                        aria-describedby="emailHelp" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="loginInputPassword1"
                                        placeholder="Password">
                                    <div class="forgot-passward">
                                        <button type="button" class="fs-11" data-toggle="modal"
                                            data-target="#ForgotPasswordModal">Forgot password ?</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit"
                                        class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                                </div>
                                <button type="button" class="fs-14 text-below-button" data-toggle="modal"
                                    data-target="#RegisterModal">New User ? Click Here to <span
                                        class="fw-600">Register</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal reg-log-modal" id="RegisterModal" tabindex="-1" role="dialog" aria-labelledby="RegisterModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center w-100 fw-400" id="RegisterModalLongTitle">Welcome to the <strong
                            class="fw-500">Bulatrips</strong></h5>
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
                                    <input type="email" class="form-control" id="RegisterInputEmail1"
                                        aria-describedby="emailHelp" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="RegisterInputPassword1"
                                        placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="RegisterInputMobile"
                                        placeholder="+91  Mobile number">
                                </div>
                                <div class="form-group chkbx">
                                    <input type="checkbox" id="logintab" checked>
                                    <label for="logintab" class="fz-13 fw-400">
                                        <span class="chk-txt fs-13 fw-400">I Agree to <a href="">terms &
                                                conditions</a></span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <button type="submit"
                                        class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Register</button>
                                </div>
                                <button type="button" class="fs-14 text-below-button" data-toggle="modal"
                                    data-target="#LoginModal">for existing user <span
                                        class="fw-600">Login</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog"
        aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
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
                                    <div
                                        class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                        <span class="oneway d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z"
                                                    fill="#4756CB" />
                                            </svg>
                                        </span>
                                        <span class="return d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z"
                                                    fill="#4756CB" />
                                            </svg>
                                        </span>
                                    </div>
                                    <strong class="col-md-5 text-md-left text-center mt-md-0 mt-2">Dubai</strong>
                                </div>
                                <div class="progress mb-5">
                                    <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="row justify-content-center mb-5">
                                    <div class="col-lg-8 col-md-10">
                                        <div class="row justify-content-between">
                                            <div class="col-4 justify-content-center">
                                                <div class="mb-4 d-flex justify-content-center">
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z"
                                                            fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696"
                                                            stroke-width="2" />
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
                                                    <svg width="60" height="39" viewBox="0 0 60 39" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z"
                                                            fill="#969696" />
                                                        <line y1="38" x2="60" y2="38" stroke="#969696"
                                                            stroke-width="2" />
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
                                                    <svg width="35" height="38" viewBox="0 0 35 38" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z"
                                                            fill="#969696" />
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
   
}
?>
    <script>
      $(document).ready(function() {
           $("#search").on("click", function() { 
      const selectedPassengers = getSelectedPassengers();
      reissuequote_apicall(selectedPassengers);
    });
  });
    function reissuequote_apicall(selectedPassengers){
         
           var  allow_child = document.getElementById("allow_child").value;
           var mfreNum = document.getElementById("precancelValue").value;
           var bookingId = document.getElementById("bookingId").value;
           var userId = document.getElementById("USerid").value;
            alert(mfreNum);
           //************************************ */
         /*  var originDestinations = [
                            {
                              "originLocationCode": "JFK",
                              "destinationLocationCode": "IST",
                              "cabinPreference": "Y",
                              "departureDateTime": "2023-08-14",
                              "flightNumber": 12,
                              "airlineCode": "TK"
                            },
                            {
                              "originLocationCode": "IST",
                              "destinationLocationCode": "LHR",
                              "cabinPreference": "Y",
                              "departureDateTime": "2023-08-15",
                              "flightNumber": 1979,
                              "airlineCode": "TK"
                            }
                          ];
                          */

           //****************************************** */
            const jsonString = JSON.stringify(originDestinations);          
       alert(jsonString); // This will display the contents of selectedPassengers as a JSON string

        return false;
       
    }
    // Function to get selected passengers
    function getSelectedPassengers() {
       
       const selectedPassengers = [];
            $(".chkbox:checked").each(function() {
                const firstname = $(this).data("firstname");
                const lastname = $(this).data("lastname");
                const title = $(this).data("title");
                const eticket = $(this).data("eticket");
                const passengertype = $(this).data("passengertype");
              

                selectedPassengers.push({
                    firstname: firstname,
                    lastname: lastname,
                    title: title,
                    eticket: eticket,
                    passengertype: passengertype
                });
                 });
                 return selectedPassengers;
    }
    //===================================

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

        checkAll.addEventListener('change', function () {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = checkAll.checked;
            }
        });
        /*********************************************************/
    </script>
</body>

</html>