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
        <div class="header-info-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-between">
                        <ul class="top-info-left" id="dynamicInfo">
                        </ul>
                        <ul class="top-info-right">
                            <li>
                                <ul class="d-flex">
                                    <li>
                                        <a href="#">
                                            <svg width="9" height="15" viewBox="0 0 9 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path id="Vector" d="M7.50732 8.4375L7.92393 5.72285H5.31914V3.96123C5.31914 3.21855 5.68301 2.49463 6.84961 2.49463H8.03379V0.183398C8.03379 0.183398 6.95918 0 5.93174 0C3.78662 0 2.38447 1.3002 2.38447 3.65391V5.72285H0V8.4375H2.38447V15H5.31914V8.4375H7.50732Z" fill="white"/>
                                            </svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.9914 1.54204C15.4033 1.80262 14.7716 1.97869 14.1075 2.05828C14.7927 1.64824 15.3054 1.00289 15.5498 0.242638C14.906 0.625055 14.2014 0.894236 13.4666 1.03848C12.9724 0.510874 12.3179 0.161171 11.6047 0.0436601C10.8914 -0.0738508 10.1593 0.0474051 9.52205 0.388602C8.88478 0.729799 8.37798 1.27185 8.08033 1.93059C7.78269 2.58934 7.71085 3.32792 7.87598 4.03167C6.57143 3.96617 5.29523 3.6271 4.13021 3.03646C2.96518 2.44581 1.93737 1.6168 1.11347 0.60323C0.831756 1.08918 0.669772 1.65261 0.669772 2.25266C0.669457 2.79284 0.802481 3.32474 1.05704 3.80118C1.3116 4.27762 1.67983 4.68386 2.12904 4.98386C1.60807 4.96728 1.09859 4.82651 0.643009 4.57327V4.61552C0.642957 5.37315 0.905025 6.10746 1.38475 6.69385C1.86447 7.28025 2.53229 7.68262 3.27491 7.83268C2.79162 7.96348 2.28493 7.98274 1.7931 7.88902C2.00262 8.54092 2.41075 9.11097 2.96035 9.51938C3.50996 9.9278 4.17351 10.1541 4.85813 10.1667C3.69595 11.079 2.26066 11.5739 0.783161 11.5717C0.521437 11.5718 0.259934 11.5565 0 11.5259C1.49975 12.4902 3.24557 13.002 5.02857 13C11.0643 13 14.3638 8.001 14.3638 3.66545C14.3638 3.52459 14.3603 3.38232 14.354 3.24147C14.9958 2.77733 15.5498 2.20258 15.99 1.54415L15.9914 1.54204Z" fill="white"/>
                                            </svg>    
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.82023 0.039C4.51336 0.0070909 4.73436 0 6.5 0C8.26564 0 8.48664 0.00768181 9.17918 0.039C9.87173 0.0703182 10.3445 0.180818 10.7581 0.340955C11.1912 0.504636 11.5842 0.7605 11.9092 1.09141C12.2401 1.41582 12.4954 1.80818 12.6585 2.24191C12.8192 2.65555 12.9291 3.12827 12.961 3.81964C12.9929 4.51395 13 4.73495 13 6.5C13 8.26564 12.9923 8.48664 12.961 9.17977C12.9297 9.87114 12.8192 10.3439 12.6585 10.7575C12.4954 11.1913 12.2397 11.5843 11.9092 11.9092C11.5842 12.2401 11.1912 12.4954 10.7581 12.6585C10.3445 12.8192 9.87173 12.9291 9.18036 12.961C8.48664 12.9929 8.26564 13 6.5 13C4.73436 13 4.51336 12.9923 3.82023 12.961C3.12886 12.9297 2.65614 12.8192 2.2425 12.6585C1.80873 12.4953 1.41571 12.2396 1.09082 11.9092C0.760134 11.5846 0.504231 11.1917 0.340955 10.7581C0.180818 10.3445 0.0709091 9.87173 0.039 9.18036C0.0070909 8.48604 0 8.26505 0 6.5C0 4.73436 0.00768181 4.51336 0.039 3.82082C0.0703182 3.12827 0.180818 2.65555 0.340955 2.24191C0.504473 1.80823 0.76057 1.41541 1.09141 1.09082C1.41584 0.760205 1.80846 0.504307 2.24191 0.340955C2.65555 0.180818 3.12827 0.0709091 3.81964 0.039H3.82023ZM9.12659 1.209C8.44114 1.17768 8.2355 1.17118 6.5 1.17118C4.7645 1.17118 4.55886 1.17768 3.87341 1.209C3.23936 1.23795 2.89545 1.34373 2.66618 1.43295C2.36305 1.55114 2.14618 1.69118 1.91868 1.91868C1.70303 2.12848 1.53706 2.38389 1.43295 2.66618C1.34373 2.89545 1.23795 3.23936 1.209 3.87341C1.17768 4.55886 1.17118 4.7645 1.17118 6.5C1.17118 8.2355 1.17768 8.44114 1.209 9.12659C1.23795 9.76064 1.34373 10.1045 1.43295 10.3338C1.53695 10.6157 1.703 10.8715 1.91868 11.0813C2.12845 11.297 2.38432 11.463 2.66618 11.567C2.89545 11.6563 3.23936 11.762 3.87341 11.791C4.55886 11.8223 4.76391 11.8288 6.5 11.8288C8.23609 11.8288 8.44114 11.8223 9.12659 11.791C9.76064 11.762 10.1045 11.6563 10.3338 11.567C10.637 11.4489 10.8538 11.3088 11.0813 11.0813C11.297 10.8715 11.463 10.6157 11.567 10.3338C11.6563 10.1045 11.762 9.76064 11.791 9.12659C11.8223 8.44114 11.8288 8.2355 11.8288 6.5C11.8288 4.7645 11.8223 4.55886 11.791 3.87341C11.762 3.23936 11.6563 2.89545 11.567 2.66618C11.4489 2.36305 11.3088 2.14618 11.0813 1.91868C10.8715 1.70304 10.6161 1.53708 10.3338 1.43295C10.1045 1.34373 9.76064 1.23795 9.12659 1.209ZM5.66977 8.50377C6.13343 8.69678 6.64972 8.72283 7.13046 8.57747C7.61119 8.43211 8.02655 8.12436 8.30559 7.70678C8.58463 7.2892 8.71003 6.7877 8.66039 6.28793C8.61075 5.78815 8.38914 5.32112 8.03341 4.96659C7.80664 4.73996 7.53244 4.56643 7.23056 4.4585C6.92868 4.35056 6.60662 4.31089 6.28757 4.34236C5.96851 4.37383 5.66041 4.47565 5.38543 4.64049C5.11045 4.80532 4.87544 5.02908 4.69732 5.29564C4.51919 5.5622 4.40238 5.86494 4.3553 6.18207C4.30823 6.49919 4.33204 6.82281 4.42505 7.12962C4.51805 7.43644 4.67792 7.71881 4.89315 7.95643C5.10839 8.19404 5.37362 8.38097 5.66977 8.50377ZM4.13755 4.13755C4.44779 3.8273 4.8161 3.58121 5.22145 3.4133C5.6268 3.2454 6.06125 3.15898 6.5 3.15898C6.93875 3.15898 7.3732 3.2454 7.77855 3.4133C8.1839 3.58121 8.55221 3.8273 8.86245 4.13755C9.1727 4.44779 9.41879 4.8161 9.58669 5.22145C9.7546 5.6268 9.84101 6.06125 9.84101 6.5C9.84101 6.93875 9.7546 7.3732 9.58669 7.77855C9.41879 8.1839 9.1727 8.55221 8.86245 8.86245C8.23589 9.48902 7.38609 9.84101 6.5 9.84101C5.61391 9.84101 4.76411 9.48902 4.13755 8.86245C3.51098 8.23589 3.15898 7.38609 3.15898 6.5C3.15898 5.61391 3.51098 4.76411 4.13755 4.13755ZM10.582 3.65655C10.6589 3.58402 10.7204 3.49681 10.763 3.40008C10.8056 3.30334 10.8283 3.19904 10.8298 3.09337C10.8314 2.98769 10.8117 2.88278 10.772 2.78484C10.7322 2.68691 10.6733 2.59794 10.5985 2.5232C10.5238 2.44847 10.4348 2.38949 10.3369 2.34976C10.2389 2.31003 10.134 2.29035 10.0284 2.29189C9.92268 2.29343 9.81839 2.31616 9.72165 2.35873C9.62492 2.4013 9.5377 2.46285 9.46518 2.53973C9.32414 2.68924 9.24692 2.88784 9.24991 3.09337C9.25291 3.29889 9.33589 3.49516 9.48123 3.6405C9.62657 3.78584 9.82283 3.86882 10.0284 3.87181C10.2339 3.87481 10.4325 3.79759 10.582 3.65655Z" fill="white"/>
                                            </svg>    
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.28125 15H3.09375V4.59375H0.28125V15ZM1.6875 0C0.75 0 0 0.75 0 1.6875C0 2.625 0.75 3.375 1.6875 3.375C2.625 3.375 3.375 2.625 3.375 1.6875C3.375 0.75 2.625 0 1.6875 0ZM7.875 6.1875V4.59375H5.0625V15H7.875V9.65625C7.875 6.65625 11.7188 6.46875 11.7188 9.65625V15H14.5313V8.625C14.5313 3.5625 9.1875 3.75 7.875 6.1875Z" fill="white"/>
                                            </svg>    
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- <li>                  
                                <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.5 0C8.22391 0 9.87721 0.684819 11.0962 1.90381C12.3152 3.12279 13 4.77609 13 6.5C13 8.22391 12.3152 9.87721 11.0962 11.0962C9.87721 12.3152 8.22391 13 6.5 13C4.77609 13 3.12279 12.3152 1.90381 11.0962C0.684819 9.87721 0 8.22391 0 6.5C0 4.77609 0.684819 3.12279 1.90381 1.90381C3.12279 0.684819 4.77609 0 6.5 0ZM11.394 4C10.7526 2.75301 9.66128 1.79641 8.341 1.324C8.785 2.164 9.106 3.064 9.294 4H11.394ZM11.976 6.995C11.992 6.83049 12 6.66529 12 6.5C11.999 5.99253 11.9273 5.48768 11.787 5H9.445C9.477 5.331 9.5 5.664 9.5 6C9.49793 6.67185 9.42892 7.34183 9.294 8H11.787C11.882 7.671 11.945 7.335 11.977 6.995H11.976ZM8.441 6.995L8.447 6.944C8.48112 6.63047 8.49881 6.31538 8.5 6C8.49329 5.66551 8.46791 5.33166 8.424 5H4.576C4.53172 5.33162 4.50635 5.66549 4.5 6C4.50244 6.67329 4.5806 7.34418 4.733 8H8.267C8.344 7.668 8.402 7.333 8.441 6.995ZM8.249 4C8.0134 2.94235 7.58821 1.93611 6.994 1.03C6.83 1.016 6.666 1 6.5 1C6.39583 1.00051 6.29174 1.00551 6.188 1.015L6.006 1.03L6 1.04C5.40893 1.94346 4.98581 2.94621 4.751 4H8.249ZM3.706 4C3.89781 3.06465 4.22314 2.16174 4.672 1.319C3.34598 1.7898 2.24946 2.74863 1.606 4H3.706ZM1.213 5C1.07289 5.48773 1.00121 5.99255 1 6.5C1.00121 7.00745 1.07289 7.51227 1.213 8H3.706C3.57043 7.34193 3.50141 6.67189 3.5 6C3.5 5.664 3.523 5.331 3.555 5H1.213ZM3.967 9H1.607C1.98501 9.73838 2.52472 10.382 3.18594 10.8829C3.84716 11.3838 4.61285 11.729 5.426 11.893C4.78599 11.0142 4.29322 10.0371 3.967 9ZM6.5 11.644C7.13944 10.8531 7.63928 9.95897 7.978 9H5.022C5.36074 9.95896 5.86057 10.8531 6.5 11.644ZM9.033 9C8.70677 10.0371 8.214 11.0142 7.574 11.893C8.3871 11.7289 9.15273 11.3836 9.81393 10.8827C10.4751 10.3818 11.0149 9.73829 11.393 9H9.033Z" fill="white"/>
                                </svg>    
                                English(US)
                            </li> -->
                            <?php
// echo 'helo';
// print_r($_SESSION);
// Check if the user is logged in
if(isset($_SESSION['user_id'])) {
    // User is logged in, display their name and dropdown menu
    $id = $_SESSION['user_id'];
    include_once 'class.Data.php';

    $newObj = new Data();
    //fetch user id role value
    $role = $newObj->select_author($id);
    // print_r($role);
    // User is logged in, display their name and dropdown menu
    echo '<div class="user-menu">
              
              <div class="dropdown">
                  <button type="button" class="btn dropdown-toggle" id="userDropdown" data-toggle="dropdown" style="color: white;" aria-haspopup="true" aria-expanded="false">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                  <path d="M7.10526 0C6.32455 0 5.56136 0.231509 4.91222 0.665251C4.26308 1.09899 3.75714 1.71549 3.45837 2.43678C3.1596 3.15806 3.08143 3.95175 3.23374 4.71746C3.38605 5.48318 3.762 6.18653 4.31405 6.73858C4.8661 7.29063 5.56946 7.66658 6.33517 7.81889C7.10088 7.9712 7.89457 7.89303 8.61586 7.59426C9.33714 7.2955 9.95364 6.78955 10.3874 6.14041C10.8211 5.49127 11.0526 4.72808 11.0526 3.94737C11.0526 2.90046 10.6367 1.89643 9.89647 1.15616C9.1562 0.415882 8.15217 0 7.10526 0ZM7.10526 6.31579C6.63683 6.31579 6.17892 6.17688 5.78944 5.91664C5.39995 5.65639 5.09639 5.2865 4.91713 4.85372C4.73787 4.42095 4.69096 3.94474 4.78235 3.48531C4.87374 3.02588 5.09931 2.60387 5.43054 2.27264C5.76177 1.94141 6.18378 1.71584 6.64321 1.62446C7.10264 1.53307 7.57885 1.57997 8.01162 1.75923C8.44439 1.93849 8.81429 2.24206 9.07453 2.63154C9.33478 3.02103 9.47368 3.47894 9.47368 3.94737C9.47368 4.57551 9.22416 5.17793 8.77999 5.6221C8.33582 6.06626 7.73341 6.31579 7.10526 6.31579ZM14.2105 15V14.2105C14.2105 12.7449 13.6283 11.3392 12.5919 10.3028C11.5555 9.26645 10.1499 8.68421 8.68421 8.68421H5.52632C4.06065 8.68421 2.65501 9.26645 1.61862 10.3028C0.582235 11.3392 0 12.7449 0 14.2105V15H1.57895V14.2105C1.57895 13.1636 1.99483 12.1596 2.7351 11.4193C3.47538 10.679 4.47941 10.2632 5.52632 10.2632H8.68421C9.73112 10.2632 10.7351 10.679 11.4754 11.4193C12.2157 12.1596 12.6316 13.1636 12.6316 14.2105V15H14.2105Z" fill="white"/>
              </svg>
                  &nbsp;Hi ' . @$_SESSION['first_name'] . ' &nbsp;
                  </button>
                  <div class="dropdown-menu" aria-labelledby="userDropdown">';
                  if($role[0]['role'] == '1')
                  {
                      $pagename_my  =   "user-dashboard.php";
                    echo    '<a class="dropdown-item" href="user-dashboard.php">User Dashboard</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>';
                  }else{
                      $pagename_my  =   "agent-dashboard.php";
                    echo    '<a class="dropdown-item" href="agent-dashboard.php">Agent Dashboard</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>';
                  }
                    //   <a class="dropdown-item" href="user-dashboard.php">User Dashboard</a>
                    //   <a class="dropdown-item" href="logout.php">Logout</a>
                echo ' </div>
              </div>
          </div>';
} else {
    // User is not logged in, show login button
      $pagename_my = "#LoginModal"; // This triggers the login modal
    echo '<li class="login">
                <button type="button" class="btn" data-toggle="modal" data-target="#LoginModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M7.10526 0C6.32455 0 5.56136 0.231509 4.91222 0.665251C4.26308 1.09899 3.75714 1.71549 3.45837 2.43678C3.1596 3.15806 3.08143 3.95175 3.23374 4.71746C3.38605 5.48318 3.762 6.18653 4.31405 6.73858C4.8661 7.29063 5.56946 7.66658 6.33517 7.81889C7.10088 7.9712 7.89457 7.89303 8.61586 7.59426C9.33714 7.2955 9.95364 6.78955 10.3874 6.14041C10.8211 5.49127 11.0526 4.72808 11.0526 3.94737C11.0526 2.90046 10.6367 1.89643 9.89647 1.15616C9.1562 0.415882 8.15217 0 7.10526 0ZM7.10526 6.31579C6.63683 6.31579 6.17892 6.17688 5.78944 5.91664C5.39995 5.65639 5.09639 5.2865 4.91713 4.85372C4.73787 4.42095 4.69096 3.94474 4.78235 3.48531C4.87374 3.02588 5.09931 2.60387 5.43054 2.27264C5.76177 1.94141 6.18378 1.71584 6.64321 1.62446C7.10264 1.53307 7.57885 1.57997 8.01162 1.75923C8.44439 1.93849 8.81429 2.24206 9.07453 2.63154C9.33478 3.02103 9.47368 3.47894 9.47368 3.94737C9.47368 4.57551 9.22416 5.17793 8.77999 5.6221C8.33582 6.06626 7.73341 6.31579 7.10526 6.31579ZM14.2105 15V14.2105C14.2105 12.7449 13.6283 11.3392 12.5919 10.3028C11.5555 9.26645 10.1499 8.68421 8.68421 8.68421H5.52632C4.06065 8.68421 2.65501 9.26645 1.61862 10.3028C0.582235 11.3392 0 12.7449 0 14.2105V15H1.57895V14.2105C1.57895 13.1636 1.99483 12.1596 2.7351 11.4193C3.47538 10.679 4.47941 10.2632 5.52632 10.2632H8.68421C9.73112 10.2632 10.7351 10.679 11.4754 11.4193C12.2157 12.1596 12.6316 13.1636 12.6316 14.2105V15H14.2105Z" fill="white"/>
                    </svg>
                    Login&nbsp;
                    <span></span>
                </button>
            </li>';
}
?>

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="top-navigation">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <a class="navbar-brand" href="index.php" style="width: 200px;">
                                <img src="images/bulatrips-logo.png" alt="" class="img-fluid">                               
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarText">
                                <ul class="navbar-nav mr-auto">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                                    </li>
                                    <!-- <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Explore by Services
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Holidays</a>
                                        <a class="dropdown-item" href="#">Flight Tickets</a>
                                        <a class="dropdown-item" href="#">FAQ</a>
                                        </div>
                                    </li> -->
                                    <li class="nav-item">
                                       <!-- <a class="nav-link" href="<?php echo $pagename_my; ?>">My Booking</a> -->
                                        <a class="nav-link" href="<?php echo $pagename_my; ?>" <?php if(!isset($_SESSION['user_id'])) echo 'data-toggle="modal" data-target="#LoginModal"'; ?>>My Booking</a>
</li>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="about-us.php">About Us</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="contact-us.php">Booking Support</a>
                                    </li>
                                 <!--li class="nav-item">
                                        <a class="nav-link" data-toggle="modal" data-target="#LoginModal">Agent Login</a>
                                    </li> -->
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>