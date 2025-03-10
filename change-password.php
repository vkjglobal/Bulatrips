<?php
session_start();
error_reporting(0);

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
    $conf = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND ticket_status = 'Ticketed' AND dep_date >= :cDate AND user_id = :id");
    $result = $conf->execute(array('cDate' => $cDate, 'id' => $id));
    $result = $conf->fetch();
    $confirm = $result['count'];

    //fetch pending booking details -case1
    $pend1 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'Booked' AND mf_reference != NULL AND ticket_status != NULL AND user_id = :id");
    $pend1->execute(array('id' => $id));
    $result1 = $pend1->fetch();
    $pending1 = $result1['count'];

    //fetch pending booking details -case2
    $pend2 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = :booked AND ticket_status = :ticket AND user_id = :id");
    $pend2->execute(array('booked' => 'Booked', 'ticket' => 'TktInProcess', 'id' => $id));
    $result2 = $pend2->fetch();
    $pending2 = $result2['count'];

    //fetch pending booking details -case3
    $pend3 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = 'BookingInProcess' AND dep_date >= :cDate AND user_id = :id");
    $pend3->execute(array('cDate' => $cDate, 'id' => $id));
    $result3 = $pend3->fetch();
    $pending3 = $result3['count'];

    //fetch pending booking details -case4
    $pend4 = $conn->prepare("SELECT count(id) as count FROM temp_booking WHERE booking_status = ' Pending' AND mf_reference != NULL AND user_id = :id");
    $pend4->execute(array('id' => $id));
    $result4 = $pend4->fetch();
    $pending4 = $result4['count'];

    // add into one variable
    $pending = $pending1 + $pending2 + $pending3 + $pending4;
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
            <div class="tab-content col-md-9 pl-md-0">
                <div class="row my-4">
                    <div class="col-12">
                        <h2 class="title-typ2 mb-0">Settings</h2>
                    </div>
                </div>
                <form action="" method="POST">
                    <div class="box-border custom-radio p-4">
                        <article class="sub-tabs-container mt-3">
                            <div class="row justify-content-between">
                                <div class="col-md-5 mb-3">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-current-password" id="user-current-password"
                                                class="form-control" placeholder="Enter Current Password">
                                            <span id="current-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-new-password" id="user-new-password"
                                                class="form-control" placeholder="Enter New Password">
                                            <span id="new-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" name="user-new-varify-password"
                                                id="user-new-varify-password" class="form-control"
                                                placeholder="Confirm New Password">
                                            <span id="verify-password-error" style="color:red;"></span>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" id="change-user-password"
                                                class="btn btn-typ7 pl-5 pr-5">SAVE</button>
                                            <div id="login_message"></div>
                                            <!-- <button type="button" class="btn btn-typ3 pl-5 pr-5">SAVE</button> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <ul class="password-features-list">
                                            <li>1- Contains at least one lowercase letter</li>
                                            <li>2- Contains at least one uppercase letter</li>
                                            <li>3- Contains at least one digit</li>
                                            <li>4- Contains at least one special character</li>
                                            <li>5- Must be at least 6 characters long</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <!-- <article class="sub-tabs-container password-change mt-3">
                            <div class="row justify-content-between">
                                <div class="col-md-5 mb-3">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control" placeholder="Enter New Password">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <input type="password" class="form-control" placeholder="Confirmr New Password">
                                        </div>
                                        <div class="col-12 mb-3">
                                            OTP has been sent to your Mobile Number
                                        </div>
                                        <div class="col-12 d-flex">
                                            <button type="button" class="btn btn-typ3 pl-5 pr-5">SAVE</button>
                                            <button type="button" class="btn light-blue-txt pl-0 ml-3">Resend OTP</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <ul class="password-features-list">
                                                <li>Contains between X- XX characters</li>
                                                <li>Contains at least X- mixed case letter</li>
                                                <li>Contains at least X number</li>
                                                <li>Contains at least X special character</li>
                                                <li>Not contain white space</li>
                                            </ul>
                                        </div>
                                    </div>    
                                </div>
                            </div>
                        </article> -->
                    </div>
                </form>
            </div>
            <?php
            include_once 'includes/class.Data.php';
            $reviewObj = new Data();
            $review = $reviewObj->select_review($id);
            // print_r($review);
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
   
    <!--model popup nimmi review success and fail-->
    <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="redirectToReviewPage()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body justify-content-center" id="responseMessage">
                    <!-- Response message will be inserted here -->
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                        onclick="redirectToReviewPage()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end-->
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

        /******************TAB WITHOUT ID*******************************/
        $(document).ready(function () {
            $(".maintab-label").click(function () {
                $(this).addClass("active").siblings().removeClass("active");
            });

            var $btns = $('.subtab-label').click(function () {
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

            $(".profile-edit-button").click(function () {
                $("#editprofile").css("display", "flex");
                $(this).parents(".profileinfo").hide();
            })

            $("input[type=file]").change(function (e) {
                $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
            });
        })
        /***************************************************************/
        $('[name=password-change]').each(function (i, d) {
            var p = $(this).prop('checked');
            //   console.log(p);
            if (p) {
                $('.password-change').eq(i)
                    .addClass('on');
            }
        });

        $('[name=password-change]').on('change', function () {
            var p = $(this).prop('checked');

            // $(type).index(this) == nth-of-type
            var i = $('[name=password-change]').index(this);

            $('.password-change').removeClass('on');
            $('.password-change').eq(i).addClass('on');
        });

        /**************Scroll To Top*****************/
        $(window).on('scroll', function () {
            if (window.scrollY > window.innerHeight) {
                $('#scrollToTop').addClass('active')
            } else {
                $('#scrollToTop').removeClass('active')
            }
        })

        $('#scrollToTop').on('click', function () {
            $("html, body").animate({
                scrollTop: 0
            }, 500);
        })
        /**********************************************/
        $(document).ready(function () {
            $(".select-tab-item li .btn").click(function () {
                $(this).parent().addClass("active-btn").siblings().removeClass("active-btn");
                $(this).parents(".select-tab-item").removeClass("open");
            });
            $(".downbtn").click(function () {
                // $(this).parents(".select-tab-item").children().addClass("active-btn");
                $(this).parents(".select-tab-item").toggleClass("open");
            });
            $('#filterOptionsFlight li a').click(function () {
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
            $('#filterOptionsPackage li a').click(function () {
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

            $(".filterOptionsFlight").click(function () {
                $("#filterOptionsFlight").addClass("d-flex").removeClass("d-none");
                $("#filterOptionsPackage").removeClass("d-flex").addClass("d-none");
                $("#FlightCancellationList").addClass("d-block").removeClass("d-none");
                $("#PackageCancellationList").removeClass("d-block").addClass("d-none");
            })
            $(".filterOptionsPackage").click(function () {
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
    // if(isset($_POST['userprofileupdate']))
    // {
    //      echo "<script>alert('test');</script>";

    //     $firstName=$_POST['fname'];
    //     $lastName=$_POST['lname'];
    //     $phone=$_POST['mobile'];
    //     $address=$_POST['address'];
    //     $country=$_POST['country'];
    //     $state=$_POST['state'];
    //     $city=$_POST['city'];
    //     $postal=$_POST['zipcode'];
    //     $image=$_POST['p-image'];
    //     // $image=$_FILES["p-image"]["tmp_name"];



    //         $ppic=$_FILES["p-image"]["name"];
    //         // get the image extension
    //         $extension = substr($ppic,strlen($ppic)-4,strlen($ppic));
    //         // allowed extensions
    //         $allowed_extensions = array(".jpg","jpeg",".png");
    //         // $allowed_extensions = array(".png");
    //         // Validation for allowed extensions .in_array() function searches an array for a specific value.
    //         if($ppic){
    //             if(!in_array($extension,$allowed_extensions))
    //             {
    //                 echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
    //             }

    //         }



    //         if($ppic){
    //             $imgnewfile=md5($imgfile).time().$extension;
    //             move_uploaded_file($_FILES["p-image"]["tmp_name"],"uploads/profile/".$imgnewfile);

    //         }else{
    //             $imgnewfile=$row['image'];
    //         }

    //         $stmt = $conn->prepare('UPDATE users SET first_name = :fname, last_name = :lname,mobile = :mobile,image = :image,contact_address = :address,country = :country,state = :state,city = :city,zip_code = :zip_code WHERE id = :id');

    //         // Bind parameters to the statement
    //         $stmt->bindParam(':fname', $firstName);
    //         $stmt->bindParam(':lname', $lastName);
    //         $stmt->bindParam(':mobile', $phone);
    //         $stmt->bindParam(':image', $$imgnewfile);
    //         $stmt->bindParam(':address', $address);
    //         $stmt->bindParam(':country', $country);
    //         $stmt->bindParam(':state', $state);
    //         $stmt->bindParam(':city', $city);
    //         $stmt->bindParam(':zip_code', $postal);
    //         $stmt->bindParam(':id', $id);

    //         // Set the parameters


    //         // Execute the statement
    //         $stmt->execute();
    //         if ($stmt->rowCount() > 0) {
    //             echo "<script>alert('You have successfully updated the data');</script>";
    //           } else {
    //             echo "<script>alert('Something Went Wrong. Please try again');</script>";
    //           }

    //         // $update="update login set first_name='$firstName',last_name='$lastName',image='$imgnewfile',phone='$phone',email='$email',address='$address',country='$country',city='$city',zipcode='$postal' where id='$id'";	

    //         // $query=mysqli_query($conn, $update);
    //         // if ($query) {
    //         // echo "<script>alert('You have successfully updated the data');</script>";
    //         // echo "<script type='text/javascript'> document.location ='home.php'; </script>";
    //         // } else{
    //         // echo "<script>alert('Something Went Wrong. Please try again');</script>";
    //         // }



    // }
}
?>