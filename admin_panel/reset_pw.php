<?php
    session_start();    
    // Check if the user is already logged in
if(isset(($_SESSION['adminid']))) {
    header('Location:home.php'); // Redirect to the dashboard page
    exit;
}
if (!isset($_GET['token']))
{
    header('Location:index.php'); // Redirect to the dashboard page
    exit;
    // Redirect the user to an error page or display an error message
   // die("Invalid or expired token");
}
else
{
// Retrieve the token from the URL
$token = $_GET['token'];
//echo $token;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Bulatrips.com</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sign In Start -->
        <div class="container-fluid bg-light">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <form action="�  method="POST" id="reset_pw">
                        <div class="bg-light box-shadow rounded p-4 p-sm-5 my-4 mx-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <a href="index.php" class="">
                                    <h3 class="text-primary admin-logo-wrp-login mb-0">
                                        <img src="../images/bulatrips-logo.png" alt="" class="img-fluid">
                                    </h3>
                                </a>
                                <h3><a href="index.php" >Sign In</a></h3>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="new_pw" placeholder="Enter New Password" name="new_pw">
                                <!-- <input type="text" class="form-control" id="username" placeholder="name@example.com" name="username"> -->

                                <label for="email">Enter New Password</label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="confirm_password" placeholder="Confirm Password" name="confirm_password" value="">
                                <label for="password">Confirm Password</label>
                            </div>
                            <input type="submit" name="submit" value="Send" id="submit1" class="btn btn-primary py-3 w-100 mb-4">
                            <input type="hidden" id="token_pw" name="token_pw" value="<?php echo $token; ?>" />
                            <!-- <button type="submit" name="btn_login" class="btn btn-primary py-3 w-100 mb-4">Sign In</button> -->
                            <!-- <p class="text-center mb-0">Don't have an Account? <a href="">Sign Up</a></p> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Sign In End -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="js/general.js"></script>
</body>

</html>
