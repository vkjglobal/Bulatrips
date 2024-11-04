<?php 
session_start();
error_reporting(0);
// $_SESSION['user_id'] =9;
if(!isset($_SESSION['user_id'])){
?>
   <script>
   window.location="index.php"    </script>
   <?php
}
else {
    require_once("includes/header.php");
    include('includes/dbConnect.php');

    // $id=$_SESSION['user_id'];
    // $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
    // $stmt->execute(array('id' => $id));

    // $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $bookingId = $_GET['booking_id'];

    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid and user_id = :userid');

        $stmtbookingid->execute(array('bookingid' => $bookingId,'userid' => $_SESSION['user_id']));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);

    //fetch booking details
   
?>
    <section>
        <div class="container">
            <h2 class="title-typ2 my-4">Booking Details</h2>
            <div class="row my-4">
                <div class="col-12 text-center">
                    <div class="">
                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                            <?php
                             $dateTime = new DateTime($bookingData['dep_date']);

                             $formattedDate = $dateTime->format('d F Y, H:i');
                            ?>
                        <div><?php echo $bookingData['dep_location']; ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $bookingData['arrival_location'] . " " . $formattedDate?> 
                                </div>
                            <!-- <div>Total Duration: 22hr 45m</div> -->
                        </div>
                        <?php
                             $stmtflightsegment = $conn->prepare('SELECT * FROM flight_segment WHERE booking_id = :bookingId');
                             $stmtflightsegment->execute(array('bookingId' => $bookingData['id']));
                             $flightsegmentData = $stmtflightsegment->fetchAll(PDO::FETCH_ASSOC);

                             $stmtpassenger = $conn->prepare('SELECT * FROM travellers_details WHERE flight_booking_id = :bookingId');
                             $stmtpassenger->execute(array('bookingId' => $bookingData['id']));
                             $passengerDetail = $stmtpassenger->fetchAll(PDO::FETCH_ASSOC); 
                        ?>
                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                            <?php 
                            foreach($flightsegmentData as $flightsegmentDatas){
                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                $stmtlocation->execute(array('airport_code' =>$flightsegmentDatas['dep_location'] ));
                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);


                                $stmtlocation->execute(array('airport_code' =>$flightsegmentDatas['arrival_location'] ));
                                $airportDestinationLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                $code = '%' . $flightsegmentDatas['airline_code'] . '%';
                                $stmtairline->bindParam(':code', $code);
                                $stmtairline->execute();
                                $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <ul class="col-lg-3 mb-3">
                                <div class="text-left">
                                    <strong class="fw-500 d-block"><?php echo  $airlineLocation['name']; ?></strong>
                                    Flight No -<?php echo $flightsegmentDatas['flight_no'] . " " .$flightsegmentDatas['cabin_prefernece']?>
                                </div>
                            </ul>
                            <div class="col-lg-7">
                                <div class="d-flex row justify-content-between">
                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                        <strong class="fw-500 d-block"><?php echo $airportLocation['city_name']. " " . date('H:i:s', strtotime($flightsegmentDatas['dep_date']))?> </strong>
                                       <?php echo date('d F Y', strtotime($flightsegmentDatas['dep_date'])). " " . $airportLocation['airport_name']."," .$airportLocation['city_name'].",".$airportLocation['country_name'] ?> 
                                    </div>
                                    <div class="col-md-2 mb-md-0 mb-2">
                                        <div class="d-flex flex-column align-items-center">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z"
                                                    fill="#959595"></path>
                                            </svg>
                                            <?php
                                            $minutes =$flightsegmentDatas['journey_duration'];
                                                            $hours = floor($minutes / 60);
                                                            $remainingMinutes = $minutes % 60;
                                                            echo $hours . " hr  " . $remainingMinutes . " m";
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-md-left">
                                        <strong class="fw-500 d-block"> <?php echo date('H:i:s', strtotime($flightsegmentDatas['arrival_date'])) . " " .$airportDestinationLocation['city_name']?></strong>
                                        <?php echo date('d F Y', strtotime($flightsegmentDatas['dep_date'])). " " . $airportDestinationLocation['airport_name']."," .$airportDestinationLocation['city_name'].",".$airportDestinationLocation['country_name'] ?> 
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <!-- <div class="mb-3 bdr-b">
                            <h6 class="text-left fw-700">Baggage Details</h6>
                            <ul class="fs-13">
                                <li class="">
                                    <ul class="row align-items-center pt-3 pb-3">
                                        <li class="col-md-1 mb-md-0 mb-2">
                                            <img src="images/emirates-logo.png" alt="">
                                        </li>
                                        <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                            <strong>Emirates</strong>
                                            <span class="uppercase-txt">cok <span
                                                    class="right-arrow-small arrow-000000"></span> dxb</span>
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
                                            <span class="uppercase-txt">cok <span
                                                    class="right-arrow-small arrow-000000"></span> dxb</span>
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
                        </div> -->
                        <div class="table-responsive">
                            <h6 class="text-left fw-700">Traveller List</h6>
                            <table class="table table-bordered white-bg text-left fs-14" style="min-width: 800px;">
                                <thead>
                                    <tr class="dark-blue-bg white-txt">
                                        <th>Traveller Name</th>
                                        <th>Age group</th>
                                        <th>Ticket No.</th>
                                        <th>Baggage</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                        foreach($passengerDetail as  $passengerDetails){
                                    ?>
                                    <tr>
                                        <td><?php echo $passengerDetails['title']." " . $passengerDetails['first_name'] . " " . $passengerDetails['last_name']?></td>
                                        <td><?php echo $passengerDetails['passenger_type'] ?></td>
                                        <td><?php echo $passengerDetails['e_ticket_number'] ?></td>
                                        <td><?php echo "Check-in: " .$passengerDetails['free_checkin_baggage']. " Cabin: ".$passengerDetails['free_cabin_baggage'] ?></td>
                                        <td><?php echo $passengerDetails['ticket_status']?></td>
                                    </tr>
                                <?php 
                                        }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row fs-13 mb-3">
                            <div class="col-12">
                                <h6 class="text-left fw-700">Fare Details</h6>
                            </div>
                            <div class="col-md-5 mb-md-0 mb-3">
                                <ul>
                                    <li class="d-flex justify-content-between p-1 bdr-b">
                                        <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in $)</span></strong>
                                        <!-- <span>1 adult</span> -->
                                    </li>
                                    <li>
                                        <ul class="bdr-b">
                                            <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li>
                                            <?php
                                              $countadult=0;
                                              $countchild=0;
                                              $totalFareadult=0;
                                              $totalFarechild=0;
                                              $countinfant=0;
                                              $totalFareinfant=0;
                                              $basefareadult=0;
                                              $basefarechild=0;
                                              $basefareinfant=0;
                                              $tax=0;

                                                foreach($passengerDetail as  $passengerDetails){
                                                    if ($passengerDetails['passenger_type'] == 'ADT') {
                                                        $countadult ++;
                                                        $totalFareadult += $passengerDetails['total_pass_fare'];
                                                        $basefareadult=$passengerDetails['basic_fare'];
                                                      } elseif($passengerDetails['passenger_type'] == 'CHD') {
                                                        $countchild ++;
                                                        $totalFarechild += $passengerDetails['total_pass_fare'];
                                                        $basefarechild=$passengerDetails['basic_fare'];
                                                      }elseif($passengerDetails['passenger_type'] == 'INF'){
                                                        $countinfant ++;
                                                        $totalFareinfant += $passengerDetails['total_pass_fare'];
                                                        $basefareinfant=$passengerDetails['basic_fare'];
                                                      }
                                                      $tax +=$passengerDetails['tax'];
                                           
                                           
                                                }
                                                if($countadult>0){
                                                    $totaladult=$basefareadult*$countadult;
                                                ?>
                                                <!-- <li class="d-flex justify-content-between p-1"><span><?php echo "Adult" . " ( " . $basefareadult ." x ". $countadult ." ) "?> </span><span><?php echo $basefareadult*$countadult?></span></li> -->
                                                 <?php }
                                                 if($countchild>0) {
                                                    $totalchild=$basefarechild*$countchild;
                                                    ?>         
    
                                                <!-- <li class="d-flex justify-content-between p-1"><span><?php echo "Child" . " ( " . $basefarechild ." x " .$countchild . " ) " ?> </span><span><?php echo $basefarechild*$countchild?></span></li> -->
                                                <?php }
                                                if($countinfant > 0) {
                                                    $totalinfant=$basefareinfant*$countinfant;
                                                    ?>
                                                <!-- <li class="d-flex justify-content-between p-1"><span><?php echo "Infant" . " ( " . $basefareinfant ." x " .$countinfant ." ) "?> </span><span><?php echo $basefareinfant*$countinfant?></span></li> -->
                                                    <?php }
                                                    
                                                    
                                                    ?>
                                                 <li class="d-flex justify-content-between p-1"><span> </span><span><?php echo $totaladult+$totalchild+$totalinfant+$bookingData['markup']?></span></li>


                                            <li class="d-flex justify-content-between p-1"><span>Airline Charges &amp; Taxes</span><span><?php echo $tax; ?></span></li>
                                            <!-- <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li> -->
                                        </ul>
                                        <ul class="bdr-b">
                                            <!-- <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li> -->
                                            <!-- <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Travel site Charges</span><span>0</span></li> -->
                                        </ul>
                                    </li>
                                    <li class="d-flex justify-content-between dark-blue-bg white-txt p-1 mt-1">
                                    <?php
                                                $total = $bookingData['total_fare'];
                                                $totalmeal=0;
                                                $totalbaggage=0;

                                                foreach($flightsegmentData as $flightsegmentDatas){
                                                    $totalmeal += $flightsegmentDatas['extrameal_amount'];
                                                    $totalbaggage += $flightsegmentDatas['extrabaggage_amount'];
                                                }
                                                $totalfare =$total+$totalmeal+ $totalbaggage;

                                                ?>
                                        <strong class="fw-600">Total Fare</strong><strong>&#36; <?php echo  $totaladult+$totalchild+$totalinfant+$totalmeal+ $totalbaggage+$tax+$bookingData['markup']; ?></strong>
                                    </li>
                                    <li class="">
                                        <div class="d-flex row justify-content-between dark-blue-bg white-txt p-1 mt-1 no-gutters">
                                            <div class="col-lg-6 col-md-12 col-sm-6 text-left mb-lg-0 mb-2">
                                                <strong class="fw-600">Paid via: <span>*************521</span></strong>
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-6 text-right">
                                               



                                                
                                                <strong class="fw-600">Total Fare:&nbsp;&nbsp;</strong><strong>&#36; <?php echo $totaladult+$totalchild+$totalinfant+$totalmeal+ $totalbaggage+$tax+$bookingData['markup'];?></strong>
                                            </div>
                                        </div>
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
                                                <tbody><tr class="bdr">
                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                    <td class="p-1">₹ 500</td>
                                                </tr>
                                                <tr class="bdr">
                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Travel Site Fee</td>
                                                    <td class="p-1">₹ 500</td>
                                                </tr>
                                            </tbody></table>
                                        </li>
                                    </ul>
                                    <ul>
                                        <li class="d-flex justify-content-between p-1 mt-1">
                                            <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                            <span class="uppercase-txt">cok-dxb</span>
                                        </li>
                                        <li class="text-left">
                                            <table class="w-100">
                                                <tbody><tr class="bdr">
                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                    <td class="p-1">₹ 500</td>
                                                </tr>
                                                <tr class="bdr">
                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Travel Site Fee</td>
                                                    <td class="p-1">₹ 500</td>
                                                </tr>
                                            </tbody></table>
                                        </li>
                                    </ul>

                                </ul>    
                            </div>
                        </div>
                        <div class="row fs-13 mb-3">
                            <div class="col-12">
                                <h6 class="text-left fw-700">Contact Details</h6>
                            </div>
                            <div class="col-lg-7 col-12 text-left">
                                <div class="row mb-2">
                                    <div class="col-3">Name:</div>
                                    <div class="col-9"><?php echo $bookingData['contact_first_name'] . " " . $bookingData['contact_last_name']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3">Phone:</div>
                                    <div class="col-9"><?php echo $bookingData['contact_phonecode'] . " " .  $bookingData['contact_number'] ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-3">Email:</div>
                                    <div class="col-9"><?php echo $bookingData['contact_email'];?></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row mb-3">
                            <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                                <button class="btn btn-typ3 fs-14 w-100">Cancel Flight</button>
                            </div>
                            <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                                <a href="dashboard-flight-reschedule-details.html" class="btn btn-typ3 fs-14 w-100">Reschedule</a>
                            </div>
                            <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                                <button id="downloadInvoice" class="btn btn-typ3 fs-14 w-100">Download Invoice</button>
                            </div>
                            <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                                <input type="hidden" id="bookingid" value="<?php echo $bookingId?>">
                                <button id="downloadButton" class="btn btn-typ3 fs-14 w-100">Download Ticket</button>
                                <!-- <button id="downloadButton">Download Ticket</button> -->
                            </div>
                            <div class="col-lg col-sm-6 mb-lg-0 mb-2">
                            
                        <!-- <form id="ticketForm" action="" method="POST"> -->
                                <input type="hidden" id="bookingid" value="<?php echo $bookingId?>">
                                <button type="submit" id="send-ticket-button" class="btn btn-typ3 fs-14 w-100">Send Ticket</button>
                                <!-- <button id="downloadButton">Download Ticket</button> -->
                            </div>
                        <!-- </form> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
   
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
   
  
    <div id="scrollToTop"><span>Go Up</span></div>
    <?php
        require_once("includes/footer.php");
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

       //Download ticket code
        // JavaScript code to handle the button click event
        document.getElementById("downloadButton").addEventListener("click", function() {
            // Redirect to the PHP script to initiate the download
            const inputValue = document.getElementById("bookingid").value;
            window.location.href = "ticket_testlatest.php?value=" + encodeURIComponent(inputValue);
        });
        //download Invoice
        document.getElementById("downloadInvoice").addEventListener("click", function() {
            // Redirect to the PHP script to initiate the download
            const inputValue = document.getElementById("bookingid").value;
            window.location.href = "invoice.php?value=" + encodeURIComponent(inputValue);
        });
        $("#send-ticket-button").click(function() {
            // Get the booking ID from the hidden input field
            var bookingId = document.getElementById("bookingid").value;
            // Make the AJAX request using jQuery
            $.ajax({
                url: "ticket_send.php", // Replace with the actual server endpoint URL
                type: "POST", // Or "GET" depending on your server setup
                data: { bookingId: bookingId }, // Data to send to the server
                // dataType: "json", // Expected data type from the server
                success: function(response) {
                    if (response == 'success') {
                    alert("Ticket sent successfully!");
            
                    }else{
                    alert("There is some problem sending Ticket!");
                    }
                    // Handle the response from the server on success
                   
                },
                error: function(xhr, status, error) {
                    // Handle errors if the AJAX request fails
                    console.log("An error occurred while sending the ticket:");
                    console.log(xhr.responseText); // This will contain the error message from the server
                }
            });
        });
       

    </script>
<?php
}
?>