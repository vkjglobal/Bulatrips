<?php
     require_once("includes/header.php");
     include('includes/dbConnect.php');
     error_reporting(0);
     if(isset($_POST['usersignup']))
    {
        //Getting Post Values
        $fname=$_POST['userfname'];  
        $lname=$_POST['userlname']; 
        $email=$_POST['useremail']; 
        $mobile=$_POST['userphone'];
        $dialcode=$_POST['country_code'];
        $password=$_POST['userpassword'];
        $userType=$_POST['userType'];
        $agency=$_POST['agency']; 
        $address=$_POST['agencyaddress']; 
        $country=$_POST['agencycountry']; 
        $state=$_POST['agencystate']; 
        $city=$_POST['agencycity']; 
        if($userType == 'agent'){
            $role=2;
           
        }else{
            $role=1;
        }
        
        $hasedpassword=hash('sha256',$password);
     
            if($role==1){
                $sql="INSERT INTO users(first_name,last_name,email,dial_code,mobile,password,role) VALUES(:fname,:lname,:email,:dialcode,:mobile,:password,:role)";

            }else{
                $sql="INSERT INTO users(first_name,last_name,email,dial_code,mobile,password,role,agency_name,agency_address,agency_country,agency_state,agency_city) VALUES(:fname,:lname,:email,:dialcode,:mobile,:password,:role,:agency,:address,:country,:state,:city)";
            }
            $query = $conn->prepare($sql);
            // Binding Post Values
            $query->bindParam(':fname',$fname,PDO::PARAM_STR);
            $query->bindParam(':lname',$lname,PDO::PARAM_STR);
            $query->bindParam(':email',$email,PDO::PARAM_STR);
            $query->bindParam(':mobile',$mobile,PDO::PARAM_INT);
            $query->bindParam(':dialcode',$dialcode,PDO::PARAM_STR);
            $query->bindParam(':role',$role,PDO::PARAM_INT);
            $query->bindParam(':password',$hasedpassword,PDO::PARAM_STR);
            if($role==2){
                $query->bindParam(':agency',$agency,PDO::PARAM_STR);
                $query->bindParam(':address',$address,PDO::PARAM_STR);
                $query->bindParam(':country',$country,PDO::PARAM_STR);
                $query->bindParam(':state',$state,PDO::PARAM_STR);
                $query->bindParam(':city',$city,PDO::PARAM_STR);
            }
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            if($lastInsertId)
            {
                if($role==1){
                    // echo "<script>alert('You have signup  Scuccessfully.');</script>";
                    // echo "<div id='success-message'>You have signed up successfully.</div>";
                    echo "<div id='success-message' class='alert alert-success alert-dismissible fade show' role='alert'>
                    You have signed up successfully.
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                        </div>";
                    echo "<script>
                            var successMessage = document.getElementById('success-message');
                            setTimeout(function() {
                              successMessage.remove();
                            }, 5000); // Remove message after 5 seconds
                          </script>";
                    

                }else{
                    // echo "<script>alert('You have signup  Scuccessfully.After admin approval login with crendtials.');</script>";
                    // echo "<script type='text/javascript'> document.location ='registration.php'; </script>";
                    // echo "<div id='success-message'>You have signup  Scuccessfully.After admin approval login with crendtials.</div>";
                    echo "<div id='success-message' class='alert alert-success alert-dismissible fade show' role='alert'>
                    You have signup  Scuccessfully.After admin approval login with crendtials.
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                        </div>";
                    
                    echo "<script>
                            var successMessage = document.getElementById('success-message');
                            setTimeout(function() {
                              successMessage.remove();
                            }, 5000); // Remove message after 5 seconds
                          </script>";
                }
            // $msg="You have signup  Scuccessfully";
            }
            else 
            {
            $error="Something went wrong.Please try again";
            }
        // }
        // else
        // {
        // $error="Username or Email-id already exist. Please try again";
        // }

    }
    
?>
<script>
    function checkEmailAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
        url: "check_availability.php",
        data:'email='+$("#useremail").val(),
        type: "POST",
        success:function(data){

        $("#email-availability-status").html(data);
        $("#loaderIcon").hide();
        },
        error:function (){
        event.preventDefault();
        }
        });
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

    <!-- <section class="midbar-banner-inner detail-page-banner" style="background-image:url('images/about-banner.jpg');">
        
    </section> -->
    <section class="pt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 mb-4">
                    <h1 class="title-typ4 w-100 fw-400 mb-5">Welcome to the <strong class="fw-500">Travel website</strong></h1>
                    <div class="row">
                        <div class="col-12">
                            <div class="register-form">
                                <div class="form-title mb-3 fw-500">Let's get started!</div>
                                <form class="reg-wrp mb-5" method="post" action="" id="user-signup">
                                      
                                        <!--Success Message-->
                                        <?php if($msg){ ?><div class="succWrap">
                                        <strong>Well Done </strong> : <?php echo htmlentities($msg);?></div>
                                        <?php } ?>
                                       
                                 
                                    <input type="radio" id="agent" name="userType" value="agent" checked="checked">
                                    <label for="agent" class="text-uppercase ml-0">Agent</label>
                                    <input type="radio" id="user" name="userType" value="user">
                                    <label for="user" class="text-uppercase">User</label>

                                   
                                    <div class="form-row mt-4">
                                       
                                        <div class="form-group col-md-6">
                                            <input type="text" class="form-control" name="userfname" id="userfname" aria-describedby="fname" pattern="[a-zA-Z\s]+" placeholder="First Name" >
                                        </div>
                                        <div class="form-group col-md-6">
                                            <input type="text" class="form-control" name="userlname" id="userlname" aria-describedby="lname" pattern="[a-zA-Z\s]+" placeholder="Last Name" >
                                        </div>
                                        <div class="form-group col-md-6">
                                            <input type="email" class="form-control" name="useremail" id="useremail" aria-describedby="emailHelp"  onBlur="checkEmailAvailability()" placeholder="Email" >
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
                                                        foreach ($data as $item) {
                                                            $name = $item['name'];
                                                            $dialCode = $item['dial_code'];
                                                            $code = $item['code'];
                                                            
                                                            // Create an option element with the country name and dial code
                                                            $option = "<option value=\"$dialCode\">$name ($dialCode)</option>";
                                                            
                                                            // Append the option to the select box HTML
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
                                            
                                                    <input type="text" class="form-control" name="userphone" id="userphone" placeholder="  Mobile number" >
                                            
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6" id="agency-field" >
                                            <input type="text" class="form-control" name="agency" id="agency" placeholder="Agency Name">
                                        </div>
                                        <div class="form-group col-md-6" id="agency-address" >
                                            <input type="textarea" class="form-control" name="agencyaddress" id="agencyaddress" placeholder="Agency Address">
                                        </div>
                                        <div class="form-group col-md-6" id="agency-country" >
                                            <!-- <input type="text" class="form-control" name="agencycountry" id="agencycountry" placeholder="Country"> -->
                                            <select id="agencycountry" name="agencycountry" class="form-control">
                                                <option value="">Loading countries...</option>
			                                </select>
                                        </div>
                                        <div class="form-group col-md-6" id="agency-state" >
                                            <!-- <input type="text" class="form-control" name="agencystate" id="agencystate" placeholder="State"> -->
                                            <select id="agencystate" name="agencystate" class="form-control">
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6" id="agency-city" >
                                            <input type="text" class="form-control" name="agencycity" id="agencycity" placeholder="City">
                                        </div>
                                       
                                        <div class="form-group col-md-6">
                                            <input type="password" class="form-control" name="userpassword" id="password" placeholder="Password" pattern="^\S{4,}$" oninput="checkPasswords()">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <input type="password" class="form-control" name="userconfirm" id="confirmpassword" placeholder="Confirm Password" oninput="checkPasswords()" oninvalid="this.setCustomValidity('Passwords do not match')">
                                        </div>
                                        
                                        <div class="form-group chkbx col-12">
                                            <input type="checkbox" id="logintab-user" checked>
                                            <label for="logintab-user" class="fz-13 fw-400" id="policyerror">
                                                <span class="chk-txt fs-13 fw-400">I Agree to <a href="terms-and-conditions.php" target="_blank" class="text-primary">terms & conditions</a></span>
                                            </label>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <button type="submit" name="usersignup"  class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Register</button> 
                                            <button type="button" class="fs-14 text-below-button mt-3" data-toggle="modal" data-target="#LoginModal">for existing user <span class="fw-600">Login</span></button>
                                        </div>
                                    </div>
                                </form>
                                <form class="form-row kyc-form" style="display: none;">
                                    <div class="form-title col-12  mb-4 fw-500">KYC</div>
                                    <div class="form-group col-md-6">
                                        <input type="text" class="form-control" id="" placeholder="Owner name">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="text" class="form-control" id="" placeholder="Date of birth">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="text" class="form-control" id="" placeholder="ID Proof">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="text" class="form-control" id="" placeholder="TAN No.">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="text" class="form-control" id="" placeholder="License Number">
                                    </div>
                                    <div class="col-12">
                                        <span for="" class="d-block mb-2 upload-lbl">Change your profile picture</span>
                                        <div class="row">
                                            <div class="col-md-6 d-flex">
                                                <!-- <span for="" class="d-block mb-1 upload-lbl">Upload profile Photo</span> -->
                                                <!-- <label class="uploadFile form-control">
                                                    <span class="filename"></span>
                                                    <input type="file" class="inputfile form-control" name="file" >
                                                </label>
                                                <span class="d-block bottom-txt">Note : Maximum Image Size 1000 kb</span> -->
                                                <div class="d-flex flex-column justify-content-between align-items-center custom-fileupload mr-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="img-wrp">
                                                            <img src="images/plus-icon.svg" alt="">
                                                        </div>
                                                        <span class="ml-3">Upload</span>
                                                    </div>
                                                    <div class="upload-btn-txt text-center">Drag image or click to select</div>
                                                    <input type='file' onchange="readURL(this);" />
                                                </div>
                                                <img id="pro-pic" src="images/pro-pic-icon.svg" alt="your image" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group chkbx col-12 mt-3">
                                        <input type="checkbox" id="logintab-agent" checked>
                                        <label for="logintab-agent" class="fz-13 fw-400">
                                            <span class="chk-txt fs-13 fw-400">I Agree to <a href="">terms & conditions</a></span>
                                        </label>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <button type="submit" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Register</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
    <div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <form>
                                <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest fare for flights</div>
                                <div class="form-row flight-direction align-items-center justify-content-center mb-4">
                                    <strong class="col-md-5 text-md-right text-center mb-md-0 mb-2">Kochi</strong>
                                    <div class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                        <span class="oneway d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z" fill="#4756CB"/>
                                            </svg>
                                        </span>
                                        <span class="return d-flex">
                                            <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z" fill="#4756CB"/>
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
                                                        <path d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z" fill="#969696"/>
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2"/>
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
                                                        <path d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z" fill="#969696"/>
                                                        <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2"/>
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
                                                        <path d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z" fill="#969696"/>
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
    ?>
    <script>
        $(document).ready(function(){
            /******************TAB**************/
            // $('[name=tab]').each(function(i,d){
            //     var p = $(this).prop('checked');
            //     //   console.log(p);
            //     if(p){
            //         $('.reg-box').eq(i)
            //         .addClass('on');
            //     }    
            // });  

            // $('[name=tab]').on('change', function(){
            //     var p = $(this).prop('checked');
                
            //     // $(type).index(this) == nth-of-type
            //     var i = $('[name=tab]').index(this);
                
            //     $('.reg-box').removeClass('on');
            //     $('.reg-box').eq(i).addClass('on');
            // });
            /************************************/
            /*************Image Upload with Preview***************/
            // $("input[type=file]").change(function (e) {
            //     $(this).parents(".uploadFile").find(".filename").text(e.target.files[0].name);
            // });
            /*****************************************************/
        });
        $(".text-below-button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        $(".forgot-passward > button").click(function(){
            $(this).parents('.modal').modal('hide');
        });
        
        $('#FlightSearchLoading').modal({
            show:false
        })
        /**************Scroll To Top*****************/
        $(window).on('scroll',function() {
            if (window.scrollY > window.innerHeight) {
                $('#scrollToTop').addClass('active')
            } else {
                $('#scrollToTop').removeClass('active')
            }
        })

        $('#scrollToTop').on('click',function() {
            $("html, body").animate({ scrollTop: 0 }, 500);
        })
        /**********************************************/
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#pro-pic')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>