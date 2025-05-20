<?php
error_reporting(0);
require_once("includes/header.php");
include('includes/dbConnect.php');

if( isset($_SESSION['user_id']) && $_SESSION['user_id'] != '' ) {
    ?>
    <script>
        document.location = "user-profile";
    </script>
    <?php
}

?>
<script>
    function checkEmailAvailability() {
        var email = $("#useremail").val();
        if (!isValidEmail(email)) {
            $("#email-availability-status").html('<span style="color: red;">Invalid email format</span>');
            return; // Stop execution if email is invalid
        }
        
        // $("#loaderIcon").show();
        jQuery.ajax({
        url: "check_availability",
            data: 'email=' + email,
            type: "POST",
            success: function(data) {
                $("#email-availability-status").html(data);
                // $("#loaderIcon").hide();
            },
            error: function() {
                $("#loaderIcon").hide();
                event.preventDefault();
            }
        });
    }

    function isValidEmail(email) {
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }

    function checkPasswords() {
        // Get the password and confirm password fields from the form
        var password = document.getElementById("password");
        var confirm_password = document.getElementById("confirmpassword");

        // Check if the password and confirm password fields match
        if (password.value != confirm_password.value) {
            confirm_password.setCustomValidity("Passwords do not match");
        } else {
            confirm_password.setCustomValidity(""); // Reset the error message
        }
    }
    window.onload = function() {
        // Add an event listener to the password field that checks if the passwords match when the focus changes
        var password = document.getElementById("confirmpassword");
        password.addEventListener("blur", checkPasswords);
    }
</script>



<style>
    .bodycontant {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 70vh;
        background: url('images/home-banner1.jpg') center center/cover no-repeat;
        /* Use your background image here */
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .content {
        position: relative;
        z-index: 2;
        /* max-width: 600px; */
        padding: 20px;
        padding: 20px;
        background: #FFF;
        border-radius: 10px;
    }

    .content h1 {
        font-size: 48px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    /* Subtext Styling */
    .content p {
        font-size: 18px;
        color: #fff;
        margin-bottom: 30px;
    }

    /* Button Styling */
    .content .btn {
        display: inline-block;
        padding: 15px 30px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    .content .btn:hover {
        background-color: #121E7E;
    }
    .class_image_left_container {
        background: url(images/login-bg.png);
        background-repeat: no-repeat;
        background-position: center center;
        background-size: 100% 100%;
    }
</style>


    

<div class="container-jumbotron">
    
    <div class="bodycontant">
        
        <div class="content">
       
       
        <!-- BREADCRUMB STARTS HERE -->
    <section style="margin-bottom: 10px; text-align:left">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumbs">
                            <li><a href="index" style="text-decoration: underline !important;">Home</a></li>
                            <?php
                
                                if( isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"] != "" ) {
                                    $referrer = $_SERVER["HTTP_REFERER"];
                                    $fileName = basename(parse_url($referrer, PHP_URL_PATH));
                                    if( $fileName != "index" && $fileName != '' ) {?>
                                        <li><a href="<?php echo $fileName;?>" style="text-decoration: underline !important;"><?php echo ucfirst($fileName);?></a></li>
                                        <?php
                                    }
                                }
                            ?>
                            <li> Registration </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- BREADCRUMB STARTS HERE -->




            <div class="container">
                <div class="row justify-content-center">

                        

                        <div class="text-center mb-4">
                            <h1 class="fw-bold" style="color: rgb(0 0 255); font-size: 26px;">User Registration Form</h1>
                        </div>

                        <div class="row">
                            <div class="col-lg-5 d-none d-lg-block class_image_left_container"></div>
                            <div class="col-lg-7 col-7">
                                <div class="register-form">
                                    <form class="reg-wrp" method="post" action="" id="user-signup">

                                        <?php
                                        $redirect_to_flights = "";
                                        if( isset($_GET['searchFlights']) && $_GET['searchFlights'] != '' ) {
                                            $redirect_to_flights = $_GET['searchFlights'];
                                        }
                                        ?>

                                        <input type="hidden" class="form-control" name="searchFlights" id="searchFlights" value="<?php echo $redirect_to_flights;?>">
                                        <input type="hidden" class="form-control" name="usersignup" id="usersignup" value="usersignup">

                                        <div class="form-row">

                                            <div class="form-group col-md-6">
                                                <input type="text" class="form-control" name="userfname" id="userfname" aria-describedby="fname" pattern="[a-zA-Z\s]+" placeholder="First Name" autocomplete="off" >
                                            </div>
                                            <div class="form-group col-md-6">
                                                <input type="text" class="form-control" name="userlname" id="userlname" aria-describedby="lname" pattern="[a-zA-Z\s]+" placeholder="Last Name" autocomplete="off" >
                                            </div>
                                            <div class="form-group col-md-6">
                                                <input type="email" class="form-control" name="useremail" id="useremail" aria-describedby="emailHelp" onkeyup="checkEmailAvailability()" placeholder="Email" autocomplete="off" >
                                                <span id="email-availability-status" style="font-size:12px;"></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <div class="form-row">
                                                    <div class="col-6">
                                                        <?php
                                                        $jsonData = file_get_contents('CountryCodes.json');

                                                        // Parse the JSON data into an array
                                                        $data = json_decode($jsonData, true);

                                                        // Check if the JSON data was parsed successfully
                                                        if ($data !== null) {
                                                            // Start creating the select box HTML
                                                            $selectBox = '<select name="country_code" id="country_code" class="form-control">';
                                                                
                                                            // Iterate over the data array and create options
                                                            foreach ($data as $key => $item) {
                                                                
                                                                $name = $item['name'];
                                                                $dialCode = $item['dial_code'];
                                                                $code = $item['code'];

                                                                if( $key == 0 ) {
                                                                    $option = "<option value=''>Mobile Code</option>";
                                                                } else {
                                                                    // $option = "<option value=\"$dialCode\">$name ($dialCode)</option>";
                                                                    $option = "<option value=\"$dialCode\">$dialCode</option>";
                                                                }
                                                                $selectBox .= $option;
                                                            }

                                                            // Close the select box HTML
                                                            $selectBox .= '</select>';

                                                            // Output the select box
                                                            echo $selectBox;
                                                        } else {
                                                            // Handle the case when the JSON data couldn't be parsed
                                                            echo 'Error parsing JSON data.';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" class="form-control" name="userphone" id="userphone" placeholder=" Mobile number" autocomplete="off" >
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group col-12 mb-3">
                                                <input type="text" name="address" id="address" placeholder="Address" value="" class="form-control" autocomplete="off">
                                            </div>
                                            <div class="form-group col-6 mb-3">
                                                <select id="endusercountry" name="endusercountry" class="form-control">
                                                    <option value="<?php echo $user['country']; ?>"><?php echo $user['country']; ?>
                                                    </option>
                                                    <option value="">Loading countries...</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-6 mb-3">
                                                <select id="enduserstate" name="enduserstate" class="form-control">
                                                    <option value="">Select State</option>
                                                    <option value="<?php echo $user['state']; ?>"><?php echo $user['state']; ?>
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-6 mb-3">
                                                <input type="text" name="endusercity" placeholder="City" id="endusercity" class="form-control" value="" autocomplete="off">
                                            </div>
                                            <div class="form-group col-6 mb-3">
                                                <input type="text" name="zipcode" autocomplete="off" id="zipcode" class="form-control" value="<?php echo $user['zip_code']; ?>" placeholder="Zip Code">
                                            </div>

                                            <input type="hidden" name="uid" id="uid" class="form-control" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="uid" id="hcountry" class="form-control" value="<?php echo $user['country']; ?>">
                                            <input type="hidden" name="uid" id="hstate" class="form-control" value="<?php echo $user['state']; ?>">

                                            

                                            <div class="form-group col-md-6">
                                                <input type="password" class="form-control" name="userpassword" id="password" placeholder="Password" oninput="checkPasswords()" autocomplete="off" >
                                            </div>
                                            <div class="form-group col-md-6">
                                                <input type="password" class="form-control" name="userconfirm" id="confirmpassword" placeholder="Confirm Password" oninput="checkPasswords()" oninvalid="this.setCustomValidity('Passwords do not match')" autocomplete="off" >
                                            </div>

                                            <div class="form-group chkbx col-12">
                                                <input type="checkbox" id="logintab-user" checked>
                                                <label for="logintab-user" class="fz-13 fw-400" id="policyerror">
                                                    <span class="chk-txt fs-13 fw-400">By continuing, I agree to Bulatrips
                                                        <a href="privacy" target="_blank" class="text-primary">Privacy Policy</a> and 
                                                        <a href="terms" target="_blank" class="text-primary">Terms & Conditions</a></span>
                                                </label>
                                            </div>

                                            <div class="form-group col-md-12">
                                                <button type="submit" name="usersignup" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Register</button>
                                                <button type="button" class="fs-14 text-below-button mt-3" data-toggle="modal" data-target="#LoginModal">Already Registered? <span class="fw-600">Sign In</span></button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

<?php
require_once("includes/login-modal.php");
require_once("includes/forgot-modal.php");
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
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#pro-pic')
                    .attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>

</html>