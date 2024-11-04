
<?php 
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
 //include_once "includes/header.php";
include_once "includes/class.booking.php";
$objBooking		= 	new Booking();
$searchQuery = isset($_POST['searchInput']) ? $_POST['searchInput'] : '';

// Add the search query to the SQL query if provided

$role   =   1;

 $listBooking	=   $objBooking->listBookings($role);
 $filteredData = [];
$i  =   0;
foreach ($listBooking as $row) {
   $i++;
    $filteredData[] = [$i,$row['userId'],$row['bookingId'],$row['agent_name'],$row['email'],$row['air_trip_type'],$row['booking_status'],$row['dep_date']];
    
}
 // print_r($filteredData);
?>
<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Custom CSS -->
        <link rel="stylesheet" href="style_search.css">
        <!-- DataTable CSS -->
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">  
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <!-- Relway Font link -->
        <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300&display=swap" rel="stylesheet">
        
        <title>DataTable Demo</title>

    </head>
    <body>
        <!-- Awesome HTML code -->
        <h1 id="heading">DataTable Demo</h1>
        <div class="container">
            <table class="table table-hover table-light table-bordered" id="myTable">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Name</th>
                        <th scope="col">email</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php  
                        if (count($listBooking)> 0) {
                            foreach($listBooking as $k => $row) {
                             //  echo "<pre/>"; print_r($row);
                    ?>
                        <tr>
                            <td><?php echo $row['bookingId'] ?></td>
                            <td><?php echo $row['agent_name'] ?></td>
                            <td><?php echo $row['email'] ?></td>
                            
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
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 
        <script>
            // Awesome JS Code Goes here
            $(document).ready( function () {
                $('#myTable').DataTable({responsive: true});
            } );
        </script>
    </body>
</html>
         