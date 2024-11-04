
<?php 
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
 include_once "includes/header.php";
include_once "includes/class.booking.php";
$objBooking		= 	new Booking();
$searchQuery = isset($_POST['searchInput']) ? $_POST['searchInput'] : '';

// Add the search query to the SQL query if provided

$role   =   2;

 $listBooking	=   $objBooking->listBookings($role);
 $filteredData = [];
$i  =   0;
foreach ($listBooking as $row) {
   $i++;
    $filteredData[] = [$i,$row['userId'],$row['bookingId'],$row['agent_name'],$row['email'],$row['air_trip_type'],$row['booking_status'],$row['dep_date']];
    
}
  print_r($filteredData);
?>

            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Agent Flight Booking</strong>
                    <div class="d-flex align-items-center justify-content-end mb-4">
                         <!-- Search Bar -->
                        <div class="row mt-3 mb-3">
                        <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search">
                      </div>
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th  onclick="sortTable(0)" scope="col">Sl No.</th>
                                    <th  onclick="sortTable(3)" scope="col">Agent Name</th>
                                    <th  onclick="sortTable(4)" scope="col">Email</th>
                                    <th   onclick="sortTable(5)"scope="col">Air Trip Type</th>
                                    <th  onclick="sortTable(6)" scope="col">Booking Status</th>
                                    <th  onclick="sortTable(7)" scope="col">Departure Date</th>                                    
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Ram</td>
                                    <td>ram123@gmail.com</td>
                                    <td>Oneway</td>      
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>26/05/2023</td>
                                    <td>
                                        <a href="agent-flight-booking-details.php"><i class="fa fa-eye">&nbsp;</i></a>
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