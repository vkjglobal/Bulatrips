
<?php 
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
else {
include "includes/dbConnect.php";
if(isset($_GET['sf'])){
    if($_REQUEST['sf']=='delete')
    {
    $id=$_GET['id'];

    $del="update members set status='inactive' where id='$id'";	

    mysqli_query($conn,$del);
    }

 }

?>

        <?php 
            include "includes/header.php";
        ?>


            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Booking Details</strong>
                    
                    <div class="row g-2 user-details">
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Name:</strong>
                                <strong class="col-8">Ram</strong>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Email:</strong>
                                <span class="col-8">ram123@gmail.com</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Phone:</strong>
                                <span class="col-8">+91 9946654321</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Air Trip type:</strong>
                                <span class="col-8">Round trip</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Source:</strong>
                                <span class="col-8">Kochi</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Destination:</strong>
                                <span class="col-8">Delhi</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Departure Date:</strong>
                                <span class="col-8">26/05/2023</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Return Date:</strong>
                                <span class="col-8">28/05/2023</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Flight No:</strong>
                                <span class="col-8">245754655</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Flight Name:</strong>
                                <span class="col-8">Indigo</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Stop:</strong>
                                <span class="col-8">0</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Total Fare:</strong>
                                <strong class="col-8">&#8377; 8950</strong>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Payment Status:</strong>
                                <strong class="col-8"><span class="badge bg-success">Paid</span></strong>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Booking Status:</strong>
                                <strong class="col-8"><span class="badge bg-success">Completed</span></strong>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Adults:</strong>
                                <span class="col-8">1</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Child:</strong>
                                <span class="col-8">0</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Infant:</strong>
                                <span class="col-8">0</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Cabin Preference:</strong>
                                <span class="col-8">Economy</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <a href="agent-flight-booking.php" class="btn btn-primary d-inline-flex btn-typ3 w-auto">Back</a>
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- Product List End -->


            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
<?php
}
?>