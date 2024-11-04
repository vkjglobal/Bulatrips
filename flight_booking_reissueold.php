<?php
/* error_reporting(0);
ini_set('display_errors', 0); */
session_start();
$_SESSION['user_id'] = 9;
if (!isset($_SESSION['user_id'])) { //for test  environment 
?>
    <script>
        window.location = "index.php"
    </script>
<?php
} else {
    //=========================================================================================

    require_once("includes/header.php");
    include_once('includes/class.cancel.php');
    include_once('includes/class.Booking.php');
    $bookingId = $_GET['booking_id'];
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $bookingId   =   trim($bookingId);
    $userId     =   $_SESSION['user_id'];
    $currentTimestamp = time();

    //    $bookingId  =   122;
    //  $userId    =   9;

    $objCancel     =   new Cancel();
    $bookCanusers      =   $objCancel->BookCancelUsers($bookingId, $userId);
    //   print_r($bookCanusers);exit;

    //preticketed cancel need only one row value to check ticketed or not 

    //Booking details fetch
    $booking = new Booking($conn);

    // Subscribe the user and get the result message
    $resultBooking = $booking->getBookingDetailsbyId($bookingId);
    $lastRecord = $resultBooking[count($resultBooking) - 1];


   



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
                        foreach ($bookCanusers as $key => $val) {
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
                            if (($fare_type == "Public") || ($fare_type == "Private")) {
                                if ($pre_booking_status ==  trim("Booked")) {
                                    if ($pre_ticket_status  ==  trim("TktInProcess")) {
                                        $ticktinprocess_msg =   "Your Ticketing is in process .Cannot Go back .Once it finished you can move with  your cancellation ";
                                        echo '<script>';
                                        echo 'document.addEventListener("DOMContentLoaded", function() {';
                                        echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                                        echo '    $("#Ticketinprocess").modal("show");';
                                        echo '});';
                                        echo '</script>';
                                        //   echo "KK";exit;

                                        //need to wait till ticked state to get cancelled 
                                    } else if (($pre_ticket_status  !=  trim("Ticketed"))  &&  ($pre_ticket_status  !=  trim("TktInProcess")) &&  ($pre_ticket_status  !=  trim("cancelled"))) {
                                        //      pre ticket cancel api          
                                        // Check if the _ticket_time_limit date is not expired
                                        if ($pre_ticket_time_limit > $currentTimestamp) {
                                            $precancelsts  =   1;
                                            //  echo "The date is not expired.";
                                            //2023-07-09 09:39:00
                                        } //if tick time limit is over ,ie either ticket autocancelled or goes to ticketed state inbetween

                                    } else if ($pre_ticket_status  ==  trim("Ticketed")) { //if under ticketed state void/refund apis 

                                        if ($VoidingWindow_limit > $currentTimestamp) {
                                            $precancelsts  =   0;
                                            $void_eligible   =   1;
                                        } else {
                                            //refund api
                                            $void_eligible   =   0;
                                        }
                                        //code for ticketed cancel PTR apis
                                        //user cancelled on same day of ticket issuance (within voidwindow time)
                                    } else if ($pre_ticket_status  ==  trim("cancelled")) {
                                        $ticktinprocess_msg =   "Your Ticket is Already Cancelled";
                                        echo '<script>';
                                        echo 'document.addEventListener("DOMContentLoaded", function() {';
                                        echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                                        echo '    $("#Ticketinprocess").modal("show");';
                                        echo '});';
                                        echo '</script>';
                                    }
                                } else {
                                    //only booked "status" tickets can be cancelled 
                                    $ticktinprocess_msg =   "Your Ticket is Not Under Booked Status .Cannot Move for Cancellation ";
                                    echo '<script>';
                                    echo 'document.addEventListener("DOMContentLoaded", function() {';
                                    echo '    $("#TicketinMessage").text("' . $ticktinprocess_msg . '");';
                                    echo '    $("#Ticketinprocess").modal("show");';
                                    echo '});';
                                    echo '</script>';
                                }
                            } else if ($fare_type == "WebFare") {
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
                            $checkboxId =   "changeDate" . $i;
                            $passenger_name  =   $val['title'] . " " . $val['first_name'] . " " . $val['last_name'];
                            $dep_date     =   $val['dep_date'];
                            $dateTime      = new DateTime($dep_date);
                            $formattedDate = $dateTime->format('d F Y, H:i');
                        ?>
                            <tr>
                                <td style="vertical-align: middle;">
                                    <div class="chkbx">
                                        <input type="checkbox" class="chkbox" id="<?php echo $checkboxId; ?>" data-firstname="<?php echo $val['first_name']; ?>" data-lastname="<?php echo $val['last_name']; ?>" data-title="<?php echo $val['title']; ?>" data-eticket="<?php echo $val['e_ticket_number']; ?>" data-passengertype="<?php echo $val['passenger_type']; ?>">
                                        <label for="<?php echo $checkboxId; ?>" class="mb-0"></label>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;"><?php echo  $passenger_name; ?></td>
                                <td style="vertical-align: middle;"><?php echo  $pre_ticket_status; ?></td>
                                <td style="vertical-align: middle;"><?php echo $formattedDate; ?></td>

                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
            </div>
            <input type="hidden" id="precancelValue" value="<?php echo $pre_mf_reference; ?>">
            <input type="hidden" id="bookingId" value="<?php echo $bookingId; ?>">
            <input type="hidden" id="USerid" value="<?php echo $userId; ?>">
            <input type="hidden" id="precancelsts" value="<?php echo $precancelsts; ?>">
            <div class=" flight-search-midbar mb-3">
                <div class="row px-3">
                    <!-- <form class="flight-search col-12" id="flight-search" method="POST" action="search.php"> -->
                    <form class="flight-search col-12" id="flight-search" method="post" action="flight_booking_reissue.php">

                        <span class="lbl">
                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <rect width="30" height="30" fill="url(#pattern0)" />
                                <defs>
                                    <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                                        <use xlink:href="#image0_69_24" transform="scale(0.00195312)" />
                                    </pattern>
                                    <image id="image0_69_24" width="512" height="512" xlink:href="data:image/png;base64,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" />
                                </defs>
                            </svg>
                            FLIGHTS
                        </span>


                        <div class="srch-fld">
                            <div class="search-box on form-row">
                                <div class="col-12 mb-3">
                                    <div class="d-flex justify-content-start">
                                        <div class="">
                                            <select name="cabin-preference" class="select-class form-control shadow-none" id="cabin-preference">
                                                <option value="Y" <?php echo $resultBooking[0]['cabin_preference'] == 'ECONOMY STANDARD' ? 'selected' : ''; ?>>Economy</option>
                                                <option value="S" <?php echo $resultBooking[0]['cabin_preference'] == 'S' ? 'selected' : ''; ?>>Premium</option>
                                                <option value="C" <?php echo $resultBooking[0]['cabin_preference'] == 'C' ? 'selected' : ''; ?>>Business</option>
                                                <option value="F" <?php echo $resultBooking[0]['cabin_preference'] == 'F' ? 'selected' : ''; ?>>First</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center chkbx ml-3">
                                            <input type="checkbox" name="selected-flights[]" value="<?php echo $resultBooking[0]['flight_no']?>" id="flight-number-<?php echo $resultBooking[0]['flight_no']?>">
                                            <label class="mb-0" for="flight-number-<?php echo $resultBooking[0]['flight_no']?>"><span class="chk-txt fs-13 fw-400">Flight N0-<?php echo $resultBooking[0]['flight_no']?></span></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="hidden" id="airport-input-reissue-value" value="<?php echo $resultBooking[0]['dep_location'] ?>">
                                    <input type="text" id="airport-input-reissue" name="airport" class="form-control" placeholder="Departing From">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="hidden" id="arrivalairport-input-reissue-value" value="<?php echo $lastRecord['arrival_location'] ?>">

                                    <input type="text" id="arrivalairport-input-reissue" name="arrivalairport" class="form-control" placeholder="Going To">

                                </div>
                                <div class="col-md-2 mb-2">
                                    <!-- <input type="text" class="form-control" id="from" name="from"> -->
                                    <input type="text" class="form-control" id="from-reisue" name="from-reissue" value=<?php echo $resultBooking[0]['dep_date'] ?>>
                                    <!-- <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span> -->
                                </div>
                                <?php if(trim($resultBooking[0]['air_trip_type'])!='OneWay'){ 
                                    ?>
                                <div class="col-md-2 mb-2">
                                    <input type="text" class="form-control" id="to" name="to">
                                    <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span>
                                </div>
                                <?php } ?>
                                <!-- <div class="form-fields col-md-2 ">
                                    <div class="select-class-wrp">
                                        <select name="cabin-preference" class="select-class" id="cabin-preference">
                                            <option value="Y" <?php echo $resultBooking[0]['cabin_preference'] == 'ECONOMY STANDARD' ? 'selected' : ''; ?>>Economy</option>
                                            <option value="S" <?php echo $resultBooking[0]['cabin_preference'] == 'S' ? 'selected' : ''; ?>>Premium</option>
                                            <option value="C" <?php echo $resultBooking[0]['cabin_preference'] == 'C' ? 'selected' : ''; ?>>Business</option>
                                            <option value="F" <?php echo $resultBooking[0]['cabin_preference'] == 'F' ? 'selected' : ''; ?>>First</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-fields col-md-2 ">
                                   
                                    <input type="checkbox" name="selected-flights[]" value="<?php echo $resultBooking[0]['flight_no']?>" id="flight-number-<?php echo $resultBooking[0]['flight_no']?>">
                                    <label for="flight-number-<?php echo $resultBooking[0]['flight_no']?>">Flight N0-<?php echo $resultBooking[0]['flight_no']?></label>
                                </div> -->
                                <div class="col-md-2 mb-2">
                                    <input type="hidden" id="airline-reissue-value" value="<?php echo $resultBooking[0]['airline_code'] ?>">

                                    <input type="text" id="airline-reissue" name="airline-reissue" class="form-control" placeholder="Airline">

                               
                                </div>
                                <span id="errormessage"></span>
                                <div class="col-md-2">
                                    <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                                    <input type="submit" name="go" class="btn btn-typ1 w-100 form-control" value="Search">

                                </div>

                            </div>

                        </div>
                    </form>

                </div>
            </div>












            <!-- <button type="button" class="btn btn-typ3 mb-3" id="search">Search alternate flight</button> -->

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
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
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
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
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
    <div class="modal reg-log-modal" id="ForgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
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
    </div>
    <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
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
    </div>
<?php
    require_once("includes/footer.php");
}
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

    $(document).ready(function() {
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
    /**************************Check All***********************/
    var checkAll = document.getElementById('changeDateAll');
    var checkboxes = document.getElementsByClassName('chkbox');

    checkAll.addEventListener('change', function() {
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = checkAll.checked;
        }
    });
    /*********************************************************/
 
    /************Datepicker******************/
    $(function() {
        var dateFormat = "mm/dd/yy",
            from = $("#from-reisue")
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
</script>
</body>

</html>