
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
                                <strong class="col-8">Naveen</strong>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Email:</strong>
                                <span class="col-8">naveenm123@gmail.com</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Start Date:</strong>
                                <span class="col-8">10/06/2023</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-2">
                                <strong class="col-4">Package Category:</strong>
                                <span class="col-8">Category 1</span>
                            </div>
                        </div>
                        <div class="col-12 my-3">
                            <strong class="mb-2 d-block">Package Overview</strong>
                            <h4 class="mb-0">Abu Dhabi Fully Loaded</h4>
                            <span class="small d-block mb-3">(7D/6N)</span>
                            <p>
                            Malesuada incidunt excepturi proident quo eros? Id interdum praesent magnis, eius cumque? Integer aptent officiis recusandae habitasse iure, quisque culpa! Nemo et? Vel excepteur pellentesque morbi ducimus porro commodo sollicitudin, quidem, cupiditate ligula doloribus recusandae non, hac, ullam per, natus parturient sollicitudin! Facilis vestibulum accumsan quisquam excepturi explicabo.
                            <br>
                            Quam aut, luctus hendrerit, laborum, dolor, consectetur scelerisque quisque feugiat sequi, ea ipsa consequat atque consectetur. Litora aute error eos.Placerat habitasse nascetur sit voluptatem ea sint facilisis! Esse sed lacus! Sociosqu ullamcorper venenatis in.
                            </p>
                            <strong class="mb-2 d-block small">Fare details</strong>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-6">Adult <span class="mx-3">&#10006;</span> 2</div>
                                <strong class="col-lg-3 col-md-4 col-6 text-end">&#8377; 107494</strong>
                            </div>
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-6">Child <span class="mx-3">&#10006;</span> 1</div>
                                <strong class="col-lg-3 col-md-4 col-6 text-end">&#8377; 30000</strong>
                            </div>
                            <div class="row">
                                <div class="col-12"><hr></div>
                            </div>
                            <div class="row">
                                <strong class="col-lg-3 col-md-4 col-6">Total Fare</strong>
                                <strong class="col-lg-3 col-md-4 col-6 text-end">&#8377; 137,494</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-2">
                                <strong class="col-5">Payment Status:</strong>
                                <strong class="col-7"><span class="badge bg-warning">Pending</span></strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row mb-2">
                                <strong class="col-5">Booking Status:</strong>
                                <strong class="col-7"><span class="badge bg-danger">Cancelled</span></strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <a href="package-booking.php" class="btn btn-primary d-inline-flex btn-typ3 w-auto">Back</a>
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