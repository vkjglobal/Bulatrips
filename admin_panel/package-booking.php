
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
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Package Booking</strong>
                    <div class="d-flex align-items-center justify-content-end mb-4">
                        <form action="">
                            <div class="row">
                                <div class="col">
                                    <label for="" class="small d-block">Status</label>    
                                    <select name="" id="" class="form-control-sm border-1">
                                        <option value="">All</option>
                                        <option value="">Completed</option>
                                        <option value="">Incomplete</option>
                                        <option value="">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="" class="small d-block">Start date</label>
                                    <input type="date" class="form-control-sm border-1">
                                </div>
                                <div class="col">
                                    <label for="" class="small d-block">End date</label>
                                    <input type="date" class="form-control-sm border-1">
                                </div>
                                <div class="col">
                                    <label for="" class="small d-block invisible">&nbsp;</label>
                                    <input type="submit" name="Submit" class="btn btn-sm btn-secondary">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th scope="col">Sl No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Package</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Naveen</td>
                                    <td>naveenm123@gmail.com</td>
                                    <td>Abu Dhabi Fully Loaded</td>
                                    <td>10/06/2023</td>
                                    <td><span class="badge bg-danger">Cancelled</span></td>
                                    <td>
                                        <a href="package-booking-details.php"><i class="fa fa-eye">&nbsp;</i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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