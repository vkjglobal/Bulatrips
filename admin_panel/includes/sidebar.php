<?php     
    $activePage = basename($_SERVER['PHP_SELF'], ".php");
    
?>
<div class="sidebar pe-4 pb-3">
            <nav class="navbar navbar-dark">
                <a href="home.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary admin-logo-wrp">
                       <img src="../images/bulatrips-logo.png" alt="" class="img-fluid">
                    </h3>
                </a>
                <!-- <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="uploads/profile/<?php // echo $rowsdmin['image'];?>" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3 admin-info">
                        <h6 class="mb-0">MCST </h6>
                        <span>Admin</span>
                
                    </div>
                </div> -->
                <div class="navbar-nav w-100">
                    <a href="home.php" class="nav-item nav-link <?= ($activePage == 'home' || $activePage =='agent-details')? 'active':''; ?>"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    
                    <div class="nav-item dropdown">                 
                        <a href="#" class="nav-link dropdown-toggle <?php if($activePage == 'about-us' || $activePage == 'terms-and-conditions' || $activePage == 'top-banner' || $activePage == 'home-video'){echo 'active';}?>" data-bs-toggle="dropdown">CMS</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="about-us.php" class="dropdown-item <?php if($activePage == 'about-us' ){echo 'active';}?>">About Us</a>
                            <a href="terms-conditions.php" class="dropdown-item <?= ($activePage == 'terms-and-conditions') ? 'active':''; ?>">Terms & Conditions</a>
                            <div class="dropdown-item dropdown <?php if($activePage == 'top-banner' || $activePage == 'home-video'){echo 'active';}?>">
                                <a href="#" class="dropdown-toggle">Home</a>
                                <div class="bg-transparent border-0 mt-2">
                                    <a href="top-banner.php" class="dropdown-item ps-3 <?= ($activePage == 'top-banner') ? 'active':''; ?>">Top Banner</a>
                                    <a href="home-video.php" class="dropdown-item ps-3 <?= ($activePage == 'home-video') ? 'active':''; ?>">Video</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="users.php" class="nav-item nav-link <?= ($activePage == 'users' || $activePage == 'user-details') ? 'active':''; ?>">Users</a>
                    <a href="agents.php" class="nav-item nav-link <?= ($activePage == 'agents' || $activePage == 'agent-details_main') ? 'active':''; ?>">Agents</a>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if($activePage == 'agent-flight-booking' || $activePage == 'agent-flight-booking-details' || $activePage == 'user-flight-booking' || $activePage == 'user-flight-details-admin' ){echo 'active';}?>" data-bs-toggle="dropdown">Flight Booking</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="agent-flight-booking.php" class="dropdown-item <?php if($activePage == 'agent-flight-booking' || $activePage == 'flight-details-admin'){echo 'active';}?>">Agent</a>
                            <a href="user-flight-booking.php" class="dropdown-item <?php if($activePage == 'user-flight-booking' || $activePage == 'user-flight-booking-details'){echo 'active';}?>">User</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if($activePage == 'packages-list' || $activePage == 'add-package' || $activePage == 'edit-package' || $activePage == 'package-category-list' || $activePage == 'add-package-category' || $activePage == 'edit-package-category' || $activePage == 'package-discount-list' || $activePage == 'add-package-discount' || $activePage == 'edit-package-discount' || $activePage == 'edit-sidebar-image' || $activePage == 'package-booking' || $activePage == 'package-booking-details'){echo 'active';}?>" data-bs-toggle="dropdown">Packages</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="package-category-list.php" class="dropdown-item <?php if($activePage == 'package-category-list' || $activePage == 'add-package-category' || $activePage == 'edit-package-category'){echo 'active';}?>">Category</a>
                            <a href="package-discount-list.php" class="dropdown-item <?php if($activePage == 'package-discount-list' || $activePage == 'add-package-discount' || $activePage == 'edit-package-discount'){echo 'active';}?>">Discount</a>
                            <a href="packages-list.php" class="dropdown-item <?php if($activePage == 'packages-list' || $activePage == 'add-package' || $activePage == 'edit-package' || $activePage == 'edit-sidebar-image'){echo 'active';}?>">Manage Packages</a>
                            <a href="package-booking.php" class="dropdown-item <?php if($activePage == 'package-booking' || $activePage == 'package-booking-details'){echo 'active';}?>">Booking</a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="markup-list.php" class="nav-link dropdown-toggle <?php if($activePage == 'markup-list' || $activePage == 'edit-markup'){echo 'active';}?>" data-bs-toggle="dropdown">Mark up</a>  
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="markup-list.php" class="dropdown-item <?php if($activePage == 'markup-list' || $activePage == 'edit-markup'){echo 'active';}?>">Booking Mark Up</a>   
                            <a href="cancel_markup-list.php" class="dropdown-item <?php if($activePage == 'cancel_markup-list' || $activePage == 'edit_cancel_markup-list'){echo 'active';}?>"> Cancel Mark Up </a>  
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="newsletter.php" class="nav-link <?= ($activePage == 'newsletter') ? 'active':''; ?>" ><i class="fa fa-laptop me-2"></i>Newsletter</a>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="reviews.php" class="nav-link <?= ($activePage == 'reviews') ? 'active':''; ?>" ><i class="fa fa-laptop me-2"></i>Reviews</a>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if($activePage == 'user_paymentlist' || $activePage == 'agent_paymentlist'){echo 'active';}?>" data-bs-toggle="dropdown">Payment List</a>  
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="user_paymentlist.php" class="dropdown-item <?php if($activePage == 'user_paymentlist'){echo 'active';}?>">Users</a>   
                            <a href="agent_paymentlist.php" class="dropdown-item <?php if($activePage == 'agent_paymentlist'){echo 'active';}?>"> Agent </a>
                        </div>
                    </div>                
                    
                    <a href="cancel_list.php" class="nav-item nav-link <?= ($activePage == 'cancel_list' || $activePage == 'view_cancel') ? 'active':''; ?>">Cancel List</a>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?php if($activePage == 'airline-list' || $activePage == 'add-airline' || $activePage == 'edit-airline' || $activePage == 'airport-loc-list' || $activePage == 'add-airport-loc' || $activePage == 'edit-airport-loc'){echo 'active';}?>" data-bs-toggle="dropdown">General</a>  
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="airline-list.php" class="dropdown-item <?php if($activePage == 'airline-list' || $activePage == 'add-airline' || $activePage == 'edit-airline'){echo 'active';}?>">Airline</a>   
                            <a href="airport-list.php" class="dropdown-item <?php if($activePage == 'airport-loc-list' || $activePage == 'add-airport-loc' || $activePage == 'edit-airport-loc' || $activePage == 'edit-sidebar-image'){echo 'active';}?>"> Airport Location </a>  
                        </div>
                    </div>
                
                    <div class="nav-item dropdown">
                        <a href="contact-list.php" class="nav-link <?= ($activePage == 'contact-list') ? 'active':''; ?>" ><i class="fa fa-laptop me-2"></i>Contact Us</a>    
                    </div>

                    <div class="nav-item dropdown">
                        <a href="settings.php" class="nav-link <?= ($activePage == 'settings') ? 'active':''; ?>" ><i class="fa fa-laptop me-2"></i>Settings</a>    
                    </div>

                </div>
            </nav>
        </div>
