<?php
session_start();
error_reporting(1);

if (!isset($_SESSION['user_id'])) {
?>
    <script>
        window.location = "index.php"
    </script>
<?php
} else {
    require_once("includes/header.php");
    include('includes/dbConnect.php');

    $id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(array('id' => $id));

    // Fetch the user details as an associative array
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $pimage = $user['image'];
    } else {
        $pimage = 'logo.png';
    }
    //nimmi - 23-05-2024

    $cDate = date('Y-m-d');
    //fetch completed booking details - booking status: booked , ticket status: ticketed
    // $comp = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND ticket_status = 'Ticketed' AND user_id = :id");
    $comp = $conn->prepare("SELECT count(id) FROM temp_booking WHERE dep_date >= :cDate AND user_id = :id AND mf_reference != ''");
    $result = $comp->execute(array('cDate' => $cDate, 'id' => $id));
    // $result = $comp->execute(array('id' => $id));
    $result = $comp->fetch();
    $completed = $result['count(id)'];

    // fetch confirmed flight count

    $conf = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE (booking_status = 'Confirmed' OR booking_status = 'Booked') AND mf_reference != NULL AND user_id = :id");
    $result = $conf->execute(array('id' => $id));
    $result = $conf->fetch();
    $confirm = $result['count'];

    //fetch pending booking details -case1
    $pend1 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND mf_reference != NULL AND user_id = :id");
    $pend1->execute(array('id' => $id));
    $result1 = $pend1->fetch();
    $pending1 = $result1['count'];

    //fetch pending booking details -case3
    $pend3 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE (booking_status = 'BookingInProcess' OR booking_status = 'Pending') AND mf_reference != NULL AND user_id = :id");
    $pend3->execute(array('id' => $id));
    $result3 = $pend3->fetch();
    $pending3 = $result3['count'];

    // add into one variable
    $pending = $pending1 + $pending3;
    // echo $pending;
    //fetch waiting booking details
    $wait = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'unconfirmed' AND mf_reference != NULL AND user_id = :id");
    $wait->execute(array('id' => $id));
    $result3 = $wait->fetch();
    $waiting = $result3['count'];

    //fetch not booked booking details
    $notbook = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'NotBooked' AND mf_reference != NULL AND user_id = :id");
    $notbook->execute(array('id' => $id));
    $result5 = $notbook->fetch();
    $notbook = $result5['count'];

    //fetch canceled flight details
    $cancel = $conn->prepare("SELECT count(id) as count FROM cancel_booking WHERE cancel_status = '1' AND user_agent_id = :id");
    $cancel->execute(array('id' => $id));
    $result4 = $cancel->fetch();
    $canceled = $result4['count'];
?>
    <section class="">
        <div class="container">

            <div class="row user-dash-main-tab">
                <?php
                /*
                <div class="col-md-3 pr-md-3 mb-3">
                    <div class="top-tabs-container">
                        <div class="d-flex flex-column align-items-center mb-3">
                            <span class="usr-pic">
                                <!-- <img src="images/Ellipse-37.png" alt=""> -->
                                <img src="uploads/profile/<?php echo $pimage; ?>" alt="">

                            </span>
                            <span class="usr-nm fw-500 light-blue-txt"><?php echo $user['first_name'] ?></span>
                            <!--<button type="button" class="btn btn-typ4 fs-14 fw-500 pl-lg-4 pr-lg-4 pl-3 pr-3">My Account</button>-->
                        </div>
                        <label for="main-tab-1" class="maintab-label w-100 active">Manage Booking</label>
                        <!-- <label for="main-tab-2" class="maintab-label w-100">Cancellation</label> -->
                        <label for="main-tab-3" class="maintab-label w-100">Profile</label>
                        <label for="main-tab-5" class="maintab-label w-100">Reviews</label>
                        <label for="main-tab-4" class="maintab-label w-100">Settings</label>
                        <!-- <button type="button" class="btn maintab-label w-100 text-left justify-content-start">Logout</button> -->
                        <a href="logout.php" class="btn maintab-label w-100 text-left justify-content-start">Logout</a>

                    </div>
                </div>
                */ ?>

                <!-- Tab Container 1 -->

                <input class="tab-radio" id="main-tab-1" name="main-group" type="radio" checked="checked" />

                <div class="tab-content col-md-12 pl-md-0">
                    <div class="row my-4">
                        <div class="col-12">
                            <h2 class="title-typ2 mb-0">Manage Bookings</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 sub-tabs-container">
                    <div class="subtab-content">
                        <div id="parent" class="d-flex flex-column">
                            <div class="d-md-block w-100">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>MFReference No.</th>
                                            <th>Route</th>
                                            <th>Trip Type</th>
                                            <th>Travellers</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $today = date('Y-m-d');

                                        // $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE user_id = :userid and booking_status = :bookingStatus and mf_reference != NULL and dep_date >= :today');
                                        $stmtbookingid = $conn->prepare("SELECT * FROM temp_booking WHERE user_id = :id ORDER BY id DESC ");
                                        $stmtbookingid->execute(array('id' => $id));
                                        $bookingData = $stmtbookingid->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($bookingData as $bookingDatas) {
                                            $dateTime = new DateTime($bookingDatas['dep_date']);
                                            $formattedDate = $dateTime->format('d F Y, H:i'); ?>

                                            <tr>
                                                <td> <?php echo $bookingDatas['mf_reference']; ?></td>
                                                <td>
                                                    <?php echo $bookingDatas['dep_location'] . " -> " . $bookingDatas['arrival_location'] . "<br />" . $formattedDate ?>
                                                </td>

                                                <td><?php echo $bookingDatas['air_trip_type']; ?></td>
                                                <td><?php echo ((int)$bookingDatas['adult_count'] + (int)$bookingDatas['child_count'] +  (int)$bookingDatas['infant_count']); ?></td>
                                                <td><?php echo $bookingDatas['total_paid']; ?></td>

                                                <td><?php echo $bookingDatas['booking_status']; ?></td>
                                                <td>
                                                    <a href="flight-booking-details.php?booking_id=<?php echo $bookingDatas['mf_reference']; ?>" class="btn btn-typ4">
                                                        <i class="fas fa-file-invoice"></i> Booking Details
                                                    </a>
                                                    <!-- <a href="cancel_user.php?booking_id=<?php //echo $bookingDatas['id']; ?>" class="btn btn-info mb-2 mt-2 btn-primary">
                                                        Void/Refund Booking
                                                    </a>
                                                    <a href="cancel.php?booking_id=<?php //echo $bookingDatas['id']; ?>" class="btn btn-danger mb-2">Cancel Booking
                                                    </a> -->
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include_once 'includes/class.Data.php';
            $reviewObj = new Data();
            $review = $reviewObj->select_review($id);
            ?>
        </div>
        </div>
        </div>
    </section>
    <!--  Login Modal -->
    <?php
    require_once("includes/login-modal.php");
    ?>
    <!--  forgot Modal -->
    <?php
    require_once("includes/forgot-modal.php");
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
            $(".maintab-label").click(function() {
                $(this).addClass("active").siblings().removeClass("active");
            });

            var $btns = $('.subtab-label').click(function() {
                var $el = $('.' + this.id).show();
                $('#parent > div').show();
                $('#parent > div').not($el).hide();
                $btns.removeClass('active');
                $(this).addClass('active');
            });
            // var $btns = $('.subtab-label').click(function() {
            //     if (this.id == 'all') {
            //         $('#parent > div').show();
            //         $(".more-btn").show();
            //     } else {
            //         var $el = $('.' + this.id).show();
            //         $('#parent > div').not($el).hide();
            //         $(".more-btn").hide();
            //     }
            //     $btns.removeClass('active');
            //     $(this).addClass('active');
            // });

            $(".profile-edit-button").click(function() {
                $("#editprofile").css("display", "flex");
                $(this).parents(".profileinfo").hide();
            })

            $("input[type=file]").change(function(e) {
                $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
            });
        })
        /***************************************************************/
        $('[name=password-change]').each(function(i, d) {
            var p = $(this).prop('checked');
            //   console.log(p);
            if (p) {
                $('.password-change').eq(i)
                    .addClass('on');
            }
        });

        $('[name=password-change]').on('change', function() {
            var p = $(this).prop('checked');

            // $(type).index(this) == nth-of-type
            var i = $('[name=password-change]').index(this);

            $('.password-change').removeClass('on');
            $('.password-change').eq(i).addClass('on');
        });

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
        $(document).ready(function() {
            $(".select-tab-item li .btn").click(function() {
                $(this).parent().addClass("active-btn").siblings().removeClass("active-btn");
                $(this).parents(".select-tab-item").removeClass("open");
            });
            $(".downbtn").click(function() {
                // $(this).parents(".select-tab-item").children().addClass("active-btn");
                $(this).parents(".select-tab-item").toggleClass("open");
            });
            $('#filterOptionsFlight li a').click(function() {
                // fetch the class of the clicked item
                var ourClass = $(this).attr('class');

                // reset the active class on all the buttons
                $('#filterOptionsFlight li').removeClass('active');
                // update the active state on our clicked button
                $(this).parent().addClass('active');

                if (ourClass == 'all') {
                    // show all our items
                    $('#FlightCancellationList').children('div.item').show();
                } else {
                    // hide all elements that don't share ourClass
                    $('#FlightCancellationList').children('div:not(.' + ourClass + ')').hide();
                    // show all elements that do share ourClass
                    $('#FlightCancellationList').children('div.' + ourClass).show();
                }
                return false;
            });
            $('#filterOptionsPackage li a').click(function() {
                // fetch the class of the clicked item
                var ourClass = $(this).attr('class');

                // reset the active class on all the buttons
                $('#filterOptionsPackage li').removeClass('active');
                // update the active state on our clicked button
                $(this).parent().addClass('active');

                if (ourClass == 'all') {
                    // show all our items
                    $('#PackageCancellationList').children('div.item').show();
                } else {
                    // hide all elements that don't share ourClass
                    $('#PackageCancellationList').children('div:not(.' + ourClass + ')').hide();
                    // show all elements that do share ourClass
                    $('#PackageCancellationList').children('div.' + ourClass).show();
                }
                return false;
            });

            $(".filterOptionsFlight").click(function() {
                $("#filterOptionsFlight").addClass("d-flex").removeClass("d-none");
                $("#filterOptionsPackage").removeClass("d-flex").addClass("d-none");
                $("#FlightCancellationList").addClass("d-block").removeClass("d-none");
                $("#PackageCancellationList").removeClass("d-block").addClass("d-none");
            })
            $(".filterOptionsPackage").click(function() {
                $("#filterOptionsPackage").addClass("d-flex").removeClass("d-none");
                $("#filterOptionsFlight").removeClass("d-flex").addClass("d-none");
                $("#PackageCancellationList").addClass("d-block").removeClass("d-none");
                $("#FlightCancellationList").removeClass("d-block").addClass("d-none");
            })
        });
        // nimmi - review model popup 15-05-2024
        function redirectToReviewPage() {
            window.location.href = 'user-dashboard';
        }

        function handleClose() {
            window.location.href = 'logout';
        }
    </script>




<?php
}
?>