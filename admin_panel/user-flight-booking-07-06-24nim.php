<?php 
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
 include_once "includes/header_listing.php";
include_once "includes/class.booking.php";
$objBooking		= 	new Booking();
//$searchQuery = isset($_POST['searchInput']) ? $_POST['searchInput'] : '';

// Add the search query to the SQL query if provided

$role   =   1;//users only

 $listBooking	=   $objBooking->listBookings($role);
$i  =   0;

?>
 <div class="container-fluid pt-4 px-4">
                <div class="border-primary rounded p-4">
                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Flight Booking List of Users</strong>    
                <div class="d-flex align-items-center justify-content-between mb-4">
                </div>
        <!-- Awesome HTML code -->
        
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0" id="myTable">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Id</th>
                        <th scope="col">Name</th>
                        <th scope="col">email</th>
                         <th scope="col">Air Trip Type</th>
                         <th  scope="col">Booking Status</th>
                         <th   scope="col">Departure Date</th> 
                         <th   scope="col">Total Fare</th> 
                         <th scope="col">Action</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php  
                        if (count($listBooking)> 0) {
                            foreach($listBooking as $k => $row) {
                                $i++;
                                 $getBooking	=   $objBooking->getBookingInfo($row['bookingId']);
                                 $extramealservAmount   =0;
                                  $extrabaggageAmount   =0;
                                  $serviceTotal =0;
                                  $extramealservAmountTotal =0;
                                  $extrabaggageAmountTotal  =0;
                                  foreach($getBooking as $key=>$values)
                                    {
                                         $extramealservAmount   =   $values['extrameal_amount'];
                                         $extrabaggageAmount    =   $values['extrabaggage_amount'];
                                         $extramealservAmountTotal += $extramealservAmount;
                                        $extrabaggageAmountTotal += $extrabaggageAmount;
                                    }
                                    $serviceTotal =   $extramealservAmountTotal+$extrabaggageAmountTotal;
                                     $total_fare   =   $getBooking[0]['total_fare'];
                                     $markup_amount   =   $getBooking[0]['markup'];

                                     $netfare      =   $total_fare + $markup_amount + $serviceTotal;
                                // print_r($getBooking);
                             //  echo "<pre/>"; print_r($row);
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $row['agent_name'] ?></td>
                            <td><?php echo $row['email'] ?></td>
                             <td><?php echo $row['air_trip_type'] ?></td>
                              <td><span class="badge bg-success"><?php echo $row['booking_status'] ?></span></td>
                               <td><?php echo $row['dep_date'] ?></td>
                                <td><?php echo $netfare;?></td>
                               <td><a href="user_flight-details-admin.php?id=<?php echo $row['bookingId']; ?>"><i class="fa fa-eye">&nbsp;</i></a></td>
     

                            
                        </tr>
                    <?php } } ?>
                </tbody> 
            </table> 
        </div>
        
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS, then DataTable, then script tag -->
        
        <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

        <script>
            // Awesome JS Code Goes here
            $(document).ready( function () {
                $('#myTable').DataTable({responsive: true});
            } );
        </script>
    <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 