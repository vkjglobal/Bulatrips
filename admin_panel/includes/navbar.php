<?php 
 //session_start(); // Start the session
   //include "class.DbAction.php";
   /*if(!(isset($_SESSION['adminid']))) {
    header('Locatiion:index.php'); // Redirect to the login page
    exit;
}*/

   $id=$_SESSION['adminid'];
  // echo $id;
    include_once "class.member.php";
    $objMember		= 	new Member();  	  
    $res    =   $objMember->getAdminProfile($id);
  // print_r($res);exit;
?>
<nav class="navbar navbar-expand nav-top navbar-dark sticky-top px-4 py-0">
    <a href="home.php" class="navbar-brand d-flex d-lg-none me-4">
        <h3 class="text-primary admin-logo-wrp mb-0">
            <img src="img/logo.png" alt="">
        </h3>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>
    
    <div class="navbar-nav align-items-center ms-auto">
        
        
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <!-- <img class="rounded-circle me-lg-2" src="uploads/profile/<?php  //echo $rowsdmin['image'];?>" alt="" style="width: 40px; height: 40px;"> -->
                <span class="d-inline-flex"><?php echo $res[0]['username'];?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                <!-- <a href="my-profile.html" class="dropdown-item">My Profile</a> -->
                <a href="account-settings.php" class="dropdown-item">Settings</a>
                <a href="logout.php" class="dropdown-item">Log Out</a>
            </div>
        </div>
    </div>
</nav>
