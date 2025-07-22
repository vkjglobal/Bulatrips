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
    ?>
    <style>
        /* Custom responsive table styles */
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .table {
            margin-bottom: 0;
            min-width: 100%;
        }
        
        .table th {
            background-color: #343a40 !important;
            color: white !important;
            border: none !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 15px 10px;
        }
        
        .table td {
            padding: 15px 10px;
            vertical-align: middle;
            border-color: #dee2e6;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .route-info {
            min-width: 200px;
        }
        
        .badge {
            font-size: 11px;
            padding: 5px 8px;
        }
        
        /* Mobile specific styles */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 12px;
            }
            
            .table th, .table td {
                padding: 8px 5px;
            }
            
            .route-info {
                min-width: 150px;
            }
            
            .btn-sm {
                padding: 5px 8px;
                font-size: 11px;
            }
        }
        
        @media (max-width: 576px) {
            .table-responsive {
                border: none;
                box-shadow: none;
            }
            
            .table th:first-child,
            .table td:first-child {
                position: sticky;
                left: 0;
                background-color: white;
                z-index: 10;
            }
            
            .table th:first-child {
                background-color: #343a40 !important;
            }
        }
    </style>
    <?php
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
                            <h2 class="title-typ2 mb-0" style="    line-height: 30px;">Manage Bookings</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 sub-tabs-container">
                    <div class="">
                        <div id="parent" class="d-flex flex-column">
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped table-hover w-100">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-nowrap">MFReference No.</th>
                                            <th class="text-nowrap">Route</th>
                                            <th class="text-nowrap">Trip Type</th>
                                            <th class="text-nowrap">Travellers</th>
                                            <th class="text-nowrap">Total Amount</th>
                                            <th class="text-nowrap">Status</th>
                                            <th class="text-nowrap">Action</th>
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
                                                <td class="text-nowrap align-middle"> 
                                                    <strong><?php echo $bookingDatas['mf_reference']; ?></strong>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="route-info">
                                                        <strong><?php echo $bookingDatas['dep_location'] . " → " . $bookingDatas['arrival_location']; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $formattedDate; ?></small>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap align-middle">
                                                    <span class="badge badge-info"><?php echo $bookingDatas['air_trip_type']; ?></span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-secondary">
                                                        <?php echo ((int)$bookingDatas['adult_count'] + (int)$bookingDatas['child_count'] +  (int)$bookingDatas['infant_count']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-nowrap align-middle">
                                                    <strong class="text-success">$<?php echo number_format($bookingDatas['total_paid'], 2); ?></strong>
                                                </td>
                                                <td class="align-middle">
                                                    <?php 
                                                    $status = $bookingDatas['booking_status'];
                                                    $badgeClass = '';
                                                    switch(strtolower($status)) {
                                                        case 'booked':
                                                        case 'confirmed':
                                                            $badgeClass = 'badge-success';
                                                            break;
                                                        case 'pending':
                                                        case 'bookinginprocess':
                                                            $badgeClass = 'badge-warning';
                                                            break;
                                                        case 'cancelled':
                                                            $badgeClass = 'badge-danger';
                                                            break;
                                                        default:
                                                            $badgeClass = 'badge-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                                                </td>
                                                <td class="text-nowrap align-middle">
                                                    <a href="flight-booking-details.php?booking_id=<?php echo $bookingDatas['mf_reference']; ?>" 
                                                       class="btn btn-typ4 btn-sm">
                                                        <i class="fas fa-file-invoice"></i> 
                                                        <span class="d-none d-md-inline">Details</span>
                                                    </a>
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