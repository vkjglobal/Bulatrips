<?php
/* error_reporting(0);
ini_set('display_errors', 0); */
session_start();
//$_SESSION['user_id'] =36;
if (!isset($_SESSION['user_id'])) { //for test  environment 
?>
    <script>
        window.location = "index"
    </script>
<?php
} else {
    //=========================================================================================

    require_once("includes/header.php");
    include_once('includes/class.cancel.php');
    $void_eligible = 2;
    $bookingId = $_GET['booking_id'];
    
    // $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    // $bookingId   =   trim($bookingId);
    


    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid');
    $stmtbookingid->execute(array('bookingid' => $bookingId));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    
    $userId     =   $_SESSION['user_id'];
    $currentTimestamp = time();

    //    $bookingId  =   122;
    //  $userId    =   9;

    $objCancel     =   new Cancel();
    $bookCanusers      =   $objCancel->BookCancelUsers($bookingData['id'], $userId);
    // echo "<pre>";
    //   print_r($bookCanusers);
    // echo "</pre>";
    // exit;

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
                            <h6 class="text-left fw-700">Do you want to cancel your Booking?</h6>
                        </div>

                </div>
            </div>
            <div class="table-responsive mb-3">
                <h6 class="text-left fw-700">Select Travellers</h6>
                <table id="psngr" class="table table-bordered white-bg text-left fs-14" style="min-width: 500px;">
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
                            $VoidingWindow_limit =   @strtotime($VoidingWindow);

                            //***************************

                            // Convert the given date to a timestamp
                            $pre_ticket_time_limit = strtotime($pre_ticket_time_limit);
                            // Get the current timestamp
                            //popn up for ticketnprocess
                            
                            if (($fare_type == "Public") || ($fare_type == "Private")) {
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

                                } else if ($pre_ticket_status  ==  trim("Ticketed") || $pre_ticket_status  ==  1) {
                                    //if under ticketed state void/refund apis 

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
                            }
                            if ($fare_type == "WebFare") {
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
                                        <input type="checkbox" class="chkbox" id="<?php echo $checkboxId; ?>"
                                            data-firstname="<?php echo $val['first_name']; ?>"
                                            data-lastname="<?php echo $val['last_name']; ?>"
                                            data-title="<?php echo $val['title']; ?>"
                                            data-eticket="<?php echo $val['e_ticket_number']; ?>"
                                            data-passengertype="<?php echo $val['passenger_type']; ?>">
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
            <input type="hidden" id="bookingId" value="<?php echo $bookingData['id']; ?>">
            <input type="hidden" id="USerid" value="<?php echo $userId; ?>">
            <input type="hidden" id="precancelsts" value="<?php echo $precancelsts; ?>">
            <?php if ($precancelsts  ==  1) { ?>
                <button type="button" class="btn btn-typ3 mb-3" id="precancel" onclick="precancelApi()">Cancel Your Flight</button> <!-- pre ticket booking cancel ends  -->
            <?php } else if ($void_eligible != 2) { //post ticket issuance today cancel to know refund amount by voidquote appi
            ?>
                <?php if ($void_eligible == 1) { //post ticket issuance today cancel to know refund amount by voidquote appi
                ?>
                    <input type="hidden" id="void_eligible" value="<?php echo $void_eligible; ?>">
                    <button type="button" class="btn btn-typ3 mb-3" id="postcancel">
                        Void Request
                        <i class="fas fa-circle-notch fa-spin spinner d-none"></i>
                    </button>
                <?php } else if ($void_eligible == 0) { //post ticket issuance today cancel to know refund amount by voidquote appi
                ?>
                    <button type="button" class="btn btn-typ3 mb-3" id="refundApiId">
                        Refund Request
                        <i class="fas fa-circle-notch fa-spin spinner d-none"></i>
                    </button>

            <?php }
            } ?>

            <!-- end of post ticket booking cancel -->
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
    <!--     popup ----- -->
    <div class="modal fade" id="errorModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center" id="errorMessage">Your Refund amount is <span id="refundAmount"></span>.Do you want to continue with cancel ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close_errmodsal" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <!-- post ticket issuance today cancel -->
                    <input type="hidden" id="modal_precancelValue" value="<?php echo $pre_mf_reference; ?>">
                    <input type="hidden" id="modal_bookingId" value="<?php echo $bookingId; ?>">
                    <input type="hidden" id="modal_USerid" value="<?php echo $userId; ?>">
                    <input type="hidden" id="modal_void_eligible" value="<?php echo $void_eligible; ?>">
                    <input type="hidden" id="api_refundAmount" value="">


                    <?php if ($void_eligible == 1) { ?>
                        <button type="button" id="voidContinue" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4" onclick="postCancelVoidAPiCall()">Continue>></button>
                    <?php } else if ($void_eligible == 0) { ?>
                        <button type="button" id="refundContinue" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Continue>></button>

                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <!--     ============================================ -->
    <div class="modal fade" id="Ticketinprocess" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id="xclose" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- <p class="text-center" id="TicketinMessage">Your Refund amount is <span id="refundAmount"></span>.Do you want to continue with cancel ?</p> -->
                    <p class="text-center" id="TicketinMessage"></p>

                </div>
                <div class="modal-footer" id="TicketinMessage_div">
                    <button type="button" id="close_ticketinprocess" class="btn btn-secondary" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
    <!--   =========================== -->
    <div class="modal fade" id="TicketinprocessErr" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id="xcloseErr" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center" id="TicketinMessageErr"></p>
                </div>
                <div class="modal-footer" id="TicketinMessage_div">
                    <button type="button" id="close_ticketinprocessErr" class="btn btn-secondary" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
    <!-- void success pop up -->
    <!--   =========================== -->
    <div class="modal fade" id="voidsuccess" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" id="xclose_voidsuccess" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center" id="voidsuccess_text"></p>
                </div>
                <div class="modal-footer" id="TicketinMessage_div">
                    <button type="button" id="close_voidsuccess" class="btn btn-secondary" data-dismiss="modal">Close</button>

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
        // Function to handle close button click
        function handleModalClose() {
            // Redirect to the dashboard page
            window.location.href = 'index'; // Replace 'dashboard.php' with the actual URL of your dashboard page
        }

        // Add event listener to the close button
        $('#close_ticketinprocess').on('click', handleModalClose);
        $('#xclose').on('click', handleModalClose);
        //============================================
        // Event listener for button click
        /*  $("#postcancel").click(function() {
              // Collect the selected passenger details from checked checkboxes
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
              }); */

        //************************************************************* */



        $("#postcancel").on("click", function() {
            let button = $(this);
            button.attr('disabled', true);
            button.html('Void Request <br /> <i class="fas fa-circle-notch fa-spin spinner"></i>');

            event.preventDefault(); // Prevent the form from submitting normally
            if ($(".chkbox:checked").length === 0) {
                $('#psngr-error').remove();
                $('#psngr').after('<sapan id="psngr-error" class="" style="color:red">Please select at least one passenger.</span>')
                button.html('Void Request');
                button.attr('disabled', false);
                return false;
            }
            $('#psngr-error').remove();
            const selectedPassengers = getSelectedPassengers();
            postCancelApi(selectedPassengers);
            // button.html('Void Request');
        });

        $("#voidContinue").on("click", function() {
            event.preventDefault(); // Prevent the form from submitting normally
            if ($(".chkbox:checked").length === 0) {
                $('#psngr-error').remove();
                $('#psngr').after('<sapan id="psngr-error" class="" style="color:red">Please select at least one passenger.</span>')
                return false;
            }
            const selectedPassengers = getSelectedPassengers();
            postCancelVoidAPiCall(selectedPassengers);
        });
        $("#refundContinue").on("click", function() {
            const selectedPassengers = getSelectedPassengers();
            postCancelRefundprocessAPiCall(selectedPassengers);
        });
        $("#refundApiId").on("click", function() {
            // const selectedPassengers = getSelectedPassengers();

            //=============
            event.preventDefault(); // Prevent the form from submitting normally
            if ($(".chkbox:checked").length === 0) {
                $('#psngr-error').remove();
                $('#psngr').after('<sapan id="psngr-error" class="" style="color:red">Please select at least one passenger.</span>')

                //  alert("Please select at least one passenger.");
                return false; // Prevent further execution
            }
            $('#psngr-error').remove();
            const formData = $(this).serialize(); // Serialize the form data

            const selectedPassengers = getSelectedPassengers();
            // Remove error message when a checkbox is selected
            $(".chkbox").on("change", function() {
                if ($(".chkbox:checked").length > 0) {
                    $('#psngr-error').remove(); // Remove the error message
                }
            });

            //==============
            postCancelRefundApi(selectedPassengers);
        });


        // Add more click event handlers for other buttons if needed





        //************************************************************************ */

        // Call the function to handle the AJAX request with the selected passenger details
        //   postCancelApi(selectedPassengers);


    });
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

    //==========================================

    function precancelApi() {

        var mfreNum = document.getElementById("precancelValue").value;
        var bookingId = document.getElementById("bookingId").value;
        var userId = document.getElementById("USerid").value;
        var precancelsts = document.getElementById("precancelsts").value;

        // Use the hiddenValue in your function logic
        $.ajax({
            url: 'cancel_ajax',
            type: 'POST',
            data: {
                mfreNum: mfreNum,
                bookingId: bookingId,
                userId: userId,
                precancelsts: precancelsts
            },
            //data: $(this).serialize()+ '&amount=' +amountprev,
            success: function(response) {
                const responseData = JSON.parse(response);
                // alert(response);return false;
                // Handle the response here
                if (responseData.status === 'success') {
                    $('#TicketinprocessErr').modal('show');
                    //  $('#TicketinMessage').text('$' + errorMessage);
                    $('#TicketinMessageErr').html("Successfully Cancelled Your Booking");

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });

                } else {
                    const errorMessage = responseData.message;
                    //  alert(errorMessage);
                    var messageErr = "Error in cancellation";
                    $('#TicketinprocessErr').modal('show');
                    //  $('#TicketinMessage').text('$' + errorMessage);
                    $('#TicketinMessageErr').html(errorMessage);

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });

                }
            },
            error: function(xhr, status, error) {
                console.log(error); // Log any AJAX errors
            }

        });

    }

    //======================================================
    function postCancelApi(selectedPassengers) {
        //  $('#errorModal').modal('show');return false;
        var mfreNum = document.getElementById("precancelValue").value;
        var bookingId = document.getElementById("bookingId").value;
        var userId = document.getElementById("USerid").value;
        var void_eligible = document.getElementById("void_eligible").value;

        // Use the hiddenValue in your function logic
        // alert(mfreNum);
        $.ajax({
            url: 'cancel_post_ticket',
            type: 'POST',
            data: {
                mfreNum: mfreNum,
                bookingId: bookingId,
                userId: userId,
                void_eligible: void_eligible,
                passengers: selectedPassengers
            },
            //data: $(this).serialize()+ '&amount=' +amountprev,
            success: function(response) {
                //onsole.log(response);                   
                //   alert(response);
                //  return false;
                // Parse the JSON response
                const responseData = JSON.parse(response);

                // Check the status of the response
                if (responseData.status === 'success') {
                    $("#postcancel").html('Void Request');
                    $("#postcancel").attr('disabled', false);
                    // Show success popup
                    const successMessage = responseData.message;
                    const refundAmount = responseData.refundamount;

                    $('#errorModal').modal('show');
                    $('#refundAmount').text('$' + refundAmount);
                    $(".close").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_errmodsal").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });

                } else {
                    $("#postcancel").html('Void Request');
                    $("#postcancel").attr('disabled', false);
                    // Show error popup

                    const errorMessage = responseData.message;
                    // alert(errorMessage);
                    var messageErr = "Error in cancellation";
                    $('#TicketinprocessErr').modal('show');
                    //  $('#TicketinMessage').text('$' + errorMessage);
                    $('#TicketinMessageErr').html(errorMessage);

                    //  $('#TicketinMessage').html(`hiiihi`);

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle AJAX error, if needed
                alert("unexpected error");
            }
        });

    }

    function postCancelVoidAPiCall(selectedPassengers) {
        var button_continue = $("#voidContinue");
        button_continue.attr('disabled', true);
        button_continue.html('Continue>> <br /> <i class="fas fa-circle-notch fa-spin spinner"></i>');
    
        //calling void api after voidQuote 
        var mfreNum = document.getElementById("modal_precancelValue").value;
        var bookingId = document.getElementById("modal_bookingId").value;
        var userId = document.getElementById("modal_USerid").value;
        var void_eligible = document.getElementById("modal_void_eligible").value;

        // Use the hiddenValue in your function logic
        // alert(mfreNum);return false;
        $.ajax({
            url: 'cancel_post_ticket_process_Void',
            type: 'POST',
            data: {
                mfreNum: mfreNum,
                bookingId: bookingId,
                userId: userId,
                void_eligible: void_eligible,
                passengers: selectedPassengers
            },
            //data: $(this).serialize()+ '&amount=' +amountprev,
            success: function(response) {
                button_continue.html('Continue>>');
                button_continue.attr('disabled', false);
                //onsole.log(response);                   
                //  alert(response);
                //  return false;
                // Parse the JSON response
                const responseData = JSON.parse(response);

                // Check the status of the response
                if (responseData.status === 'success') {
                    // Show success popup
                    $('#errorModal').modal('hide');
                    if (responseData.ptr_status === 'InProcess') {

                        const successMessage = responseData.message;
                        //const refundAmount = responseData.refundamount;
                        $('#voidsuccess').modal('show');
                        $('#voidsuccess_text').text(successMessage);
                        $("#xclose_voidsuccess").click(function() {
                            $(this).parents('.modal').modal('hide');
                            window.location.href = "index";

                        });
                        $("#close_voidsuccess").click(function() {
                            $(this).parents('.modal').modal('hide');
                            window.location.href = "index";

                        });
                        return false;
                    }
                    //==============next search api ========
                    // Additional data to be passed to the third AJAX call
                    const ptr_id = responseData.ptr_id; // Replace 'responseData.ptr_id' with the actual property that holds the PTR ID

                    const cancel_booking_Id = responseData.cancel_booking_Id;

                    // Call the third AJAX request with additional data
                    $.ajax({
                        url: 'search_ptr_void', // Replace 'third_api_url' with the URL of your third API
                        type: 'POST', // Use 'POST' or 'GET' depending on your API endpoint
                        data: {
                            ptr_id: ptr_id,
                            MFnum: mfreNum,
                            bookingId: bookingId,
                            userId: userId
                        },
                        success: function(responseData_New) {
                            //   alert(responseData_New); return false;
                            const responseDataNew = JSON.parse(responseData_New);
                            //  alert('pppp');

                            // Handle the response from the third API
                            // This function will be executed when the third API call is successful
                            // You can process the response here
                            // Show success popup
                            if (responseDataNew.status === 'success') {
                                const successMessage = responseDataNew.message;
                                const refundAmount = responseDataNew.refundamount;
                                //  alert(successMessage);
                                //  alert(refundAmount);
                                $('#voidsuccess').modal('show');
                                $('#voidsuccess_text').text(successMessage);
                                $("#xclose_voidsuccess").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                                $("#close_voidsuccess").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                            } else {
                                // Show error popup
                                $('#errorModal').modal('hide');
                                //   alert("KK");return false;
                                const errorMessage = responseDataNew.message;
                                // alert(errorMessage);
                                var messageErr = "Error in cancellation";
                                $('#TicketinprocessErr').modal('show');
                                //  $('#TicketinMessage').text('$' + errorMessage);
                                $('#TicketinMessageErr').html(messageErr);

                                $("#xcloseErr").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                                $("#close_ticketinprocessErr").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                            }
                        },
                        error: function(error) {
                            // Handle errors from the third API
                            // This function will be executed if there is an error in the third API call
                            console.error('Error from third API:', error);
                        }
                    });
                    //=================================================================

                } else {
                    button_continue.html('Continue>>');
                button_continue.attr('disabled', false);
                    // Show error popup
                    $('#errorModal').modal('hide');
                    //   alert("KK");return false;
                    const errorMessage = responseData.message;
                    // alert(errorMessage);
                    var messageErr = "Error in cancellation";
                    $('#TicketinprocessErr').modal('show');
                     $('#TicketinMessageErr').text(errorMessage);
                    // $('#TicketinMessageErr').html(messageErr);

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                button_continue.html('Continue>>');
                button_continue.attr('disabled', false);
                // Handle AJAX error, if needed
                alert("unexpected error");
            }
        });

    }

    //==============================================================Refund api ================
    function postCancelRefundApi(selectedPassengers) {
        var mfreNum = document.getElementById("precancelValue").value;
        var bookingId = document.getElementById("bookingId").value;
        var userId = document.getElementById("USerid").value;
        // var void_eligible = document.getElementById("void_eligible").value;

        // Use the hiddenValue in your function logic
        //alert(mfreNum); return false;
        $.ajax({
            url: 'refund_post_ticket',
            type: 'POST',
            data: {
                mfreNum: mfreNum,
                bookingId: bookingId,
                userId: userId,
                passengers: selectedPassengers
            },
            //data: $(this).serialize()+ '&amount=' +amountprev,
            success: function(response) {
                //   alert(response);
                // return false;
                // Parse the JSON response
                const responseData = JSON.parse(response);

                // Check the status of the response
                if (responseData.status === 'success') {
                    // Show success popup
                    const successMessage = responseData.message;
                    const refundAmount = responseData.refundamount;
                    const api_refundAmount = responseData.total_refund_api;

                    $('#errorModal').modal('show');
                    $('#refundAmount').text('$' + refundAmount);
                    $('#api_refundAmount').text('$' + api_refundAmount);
                    $(".close").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_errmodsal").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });

                } else {
                    // Show error popup

                    const errorMessage = responseData.message;
                    // alert(errorMessage);
                    //  var messageErr   =   "Error in cancellation";
                    $('#TicketinprocessErr').modal('show');
                    //  $('#TicketinMessage').text('$' + errorMessage);
                    $('#TicketinMessageErr').html(errorMessage);

                    //  $('#TicketinMessage').html(`hiiihi`);

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle AJAX error, if needed
                alert("unexpected error");
            }
        });

    }
    //========================================================Refund operation 
    function postCancelRefundprocessAPiCall(selectedPassengers) {
        //    alert("ooo");
        var mfreNum = document.getElementById("modal_precancelValue").value;
        var bookingId = document.getElementById("modal_bookingId").value;
        var userId = document.getElementById("modal_USerid").value;
        //ar refundAmount = document.getElementById("refundAmount").value;
        //  var api_refundAmount = document.getElementById("api_refundAmount").value;

        var refundAmount = document.getElementById("refundAmount").textContent;
        refundAmount = refundAmount.replace('$', '');
        //var api_refundAmount = document.getElementById("api_refundAmount").value;

        $.ajax({
            url: 'refund_post_ticket_process',
            type: 'POST',
            data: {
                mfreNum: mfreNum,
                bookingId: bookingId,
                userId: userId,
                passengers: selectedPassengers,
                refundAmount: refundAmount
            },
            //data: $(this).serialize()+ '&amount=' +amountprev,
            success: function(response) {
                //onsole.log(response);                   
                //  alert(response);
                //return false;
                // Parse the JSON response
                const responseData = JSON.parse(response);

                // Check the status of the response
                if (responseData.status === 'success') {
                    const successMessage = responseData.message;
                    //    alert(message);
                    // Show success popup
                    $('#errorModal').modal('hide');
                    if (responseData.ptr_status === 'InProcess') {

                        const successMessage = responseData.message;
                        const refundAmount = responseData.refundamount;
                        $('#voidsuccess').modal('show');
                        $('#voidsuccess_text').text(successMessage);
                        $("#xclose_voidsuccess").click(function() {
                            $(this).parents('.modal').modal('hide');
                            window.location.href = "index";

                        });
                        $("#close_voidsuccess").click(function() {
                            $(this).parents('.modal').modal('hide');
                            window.location.href = "index";

                        });
                        return false;
                    }
                    //==============next search api ========
                    // Additional data to be passed to the third AJAX call
                    const ptr_id = responseData.ptr_id; // Replace 'responseData.ptr_id' with the actual property that holds the PTR ID

                    const cancel_booking_Id = responseData.cancel_booking_Id;

                    // Call the third AJAX request with additional data
                    $.ajax({
                        url: 'search_ptr_refund', // Replace 'third_api_url' with the URL of your third API
                        type: 'POST', // Use 'POST' or 'GET' depending on your API endpoint
                        data: {
                            ptr_id: ptr_id,
                            MFnum: mfreNum,
                            bookingId: bookingId,
                            userId: userId,
                            refundAmount: refundAmount
                        },
                        success: function(responseData_New) {
                            // alert(responseData_New); 

                            //return false;
                            const responseDataNew = JSON.parse(responseData_New);


                            // Handle the response from the third API
                            // This function will be executed when the third API call is successful
                            // You can process the response here
                            // Show success popup
                            if (responseDataNew.status === 'success') {
                                //  alert('pppp');
                                const successMessage = responseDataNew.message;
                                const refundAmount = responseDataNew.refundamount;
                                //  alert(successMessage);
                                //  alert(refundAmount);
                                $('#voidsuccess').modal('show');
                                $('#voidsuccess_text').text(successMessage);
                                $(".xclose_voidsuccess").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                                $("#close_voidsuccess").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                            } else {
                                // Show error popup
                                $('#errorModal').modal('hide');
                                //   alert("KK");return false;
                                const errorMessage = responseDataNew.message;
                                // alert(errorMessage);
                                var messageErr = "Error in cancellation";
                                $('#TicketinprocessErr').modal('show');
                                //  $('#TicketinMessage').text('$' + errorMessage);
                                $('#TicketinMessageErr').html(errorMessage);

                                $("#xcloseErr").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                                $("#close_ticketinprocessErr").click(function() {
                                    $(this).parents('.modal').modal('hide');
                                });
                            }
                        },
                        error: function(error) {
                            // Handle errors from the third API
                            // This function will be executed if there is an error in the third API call
                            console.error('Error from third API:', error);
                        }
                    });
                    //=================================================================

                } else {
                    // Show error popup
                    $('#errorModal').modal('hide');
                    //lert("KK");return false;
                    const errorMessage = responseData.message;
                    // alert(errorMessage);
                    var messageErr = "Error in cancellation";
                    $('#TicketinprocessErr').modal('show');
                    //  $('#TicketinMessage').text('$' + errorMessage);
                    $('#TicketinMessageErr').html(errorMessage);

                    $("#xcloseErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                    $("#close_ticketinprocessErr").click(function() {
                        $(this).parents('.modal').modal('hide');
                    });
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                // Handle AJAX error, if needed
                alert("unexpected error");
            }
        });

    }


    //=============old code==================================

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
</script>
</body>

</html>