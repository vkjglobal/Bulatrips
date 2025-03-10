<?php
include_once("includes/filterValidation.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Favicon -->
    <link href="images/fav.svg" rel="icon">
    
    <title>Bulatrips.com</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Lexend+Deca:wght@700&family=Lobster&display=swap" rel="stylesheet">
    <!--<link rel="stylesheet" href="css/bootstrap.min.css">-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <!--<link rel="stylesheet" href="css/owl.carousel.min.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" integrity="sha512-sMXtMNL1zRzolHYKEujM2AqCLUR9F2C4/05cdbxjjLSRvMQIciEPCQZo++nk7go3BtSuK9kfa/s+a4f4i5pLkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--<link rel="stylesheet" href="css/owl.theme.default.min.css">-->
    <!--<link rel="stylesheet" href="css/select2.min.css">-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css -->

    <!-- <link rel="stylesheet" href="css/font-awesome.min.css"> -->

    <!--<link rel="stylesheet" href="css/jquery-ui.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" integrity="sha512-ELV+xyi8IhEApPS/pSj66+Jiw+sOT1Mqkzlh8ExXihe4zfqbWkxPRi8wptXIO9g73FSlhmquFlUOuMSoXz5IRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--<link rel="stylesheet" href="css/aos.css">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/airline.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <?php
    if(!isset($_SESSION)){        
        session_start();
    }
    $current_page = basename($_SERVER['PHP_SELF']);
    if($current_page === 'user-dashboard.php' || $current_page === 'agent-dashboard.php'){
        ?>
        <link rel="stylesheet" href="css/dash-style.css">
    <?php
    }
    ?>

    
</head>
<body>
    <header class="sticky-top">
        <div class="top-navigation">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <a class="navbar-brand" href="index" style="width: 200px;">
                                <img src="images/bulatrips-logo.png" alt="" class="img-fluid">                               
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarText">
                                <ul class="navbar-nav mr-auto">
                                    <li class="nav-item active"><a class="nav-link" href="index">Home</a></li>
                                    <li class="nav-item"><a class="nav-link" href="aboutUs">About Us</a></li>
                                    <!-- <li class="nav-item"><a class="nav-link" href="contact-us">Support</a></li> -->
                                    <?php
                                    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "") {?>
                                        <li class="nav-item"><a class="nav-link" href="registration">Register</a></li>
                                        <li class="nav-item"><a class="nav-link" href="javascript:void(0);"  data-toggle="modal"  data-target="#LoginModal">Login</a></li>
                                        <?php
                                    }
                                    
                                    if(isset($_SESSION['user_id'])) {?>
                                        <li class="nav-item">
                                            <div class="user-menu">
                                                <div class="dropdown">
                                                    <button type="button" class="btn dropdown-toggle" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Account </button>
                                                        <div class="dropdown-menu" aria-labelledby="userDropdown">
                                                            <a class="dropdown-item" href="user-profile">Profile</a>    
                                                            <a class="nav-link" href="user-dashboard">Manage Bookings</a>    
                                                            <a class="dropdown-item" href="change-password">Change Password</a>
                                                            <a class="dropdown-item" href="logout">Logout</a>
                                                        </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php
                                    }
                                    ?>

                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>