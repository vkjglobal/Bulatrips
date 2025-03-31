<?php
if(!isset($_SESSION)){        
    session_start();
    }
    require_once('includes/dbConnect.php');
?>
 

<div class="modal reg-log-modal" id="LoginModal" tabindex="-1" role="dialog" aria-labelledby="LoginModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center w-100 fw-400" id="loginModalLongTitle">Welcome to <strong class="fw-500">Bulatrips</strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7 d-none d-lg-block">
                        <img src="images/login-bg.png" alt="">
                    </div>
                    <div class="col-lg-5 col-12">
                        <form method="post" action="" id="user-login">
                            <div class="form-title mb-3 fw-500">Login</div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="loginemail" id="loginemail" aria-describedby="emailHelp" placeholder="Email">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" name="loginpassword" id="loginpassword" placeholder="Password">
                                <div class="forgot-passward">
                                    <button type="button" class="fs-11" data-toggle="modal" data-target="#ForgotPasswordModal">Forgot password ?</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="userlogin" class="btn border-radius-5 btn-typ1 fs-15 fw-400 w-100">Submit</button>
                            </div>
                            <!-- <a href="registration.php" class="fs-14 text-below-button" data-toggle="modal" data-target="#RegisterModal">New User ? Click Here to <span class="fw-600">Register</span></a> -->
                        </form>
                            <a href="registration.php" class="fs-14 text-below-button"  >New User ? Click Here to <span class="fw-600">Register</span></a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>