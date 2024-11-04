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
                <!-- <div class="d-flex align-items-center justify-content-between mb-4">
                </div> -->
                <div class="d-flex align-items-center justify-content-end mb-4">
                    <select id="filter" class="form-select">
                        <option value="all">All</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="page">Current Page</option>
                    </select>
                    <button type="button" id="download" class="btn btn-primary ml-2">Download</button>
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

        <!-- <script>
            // Awesome JS Code Goes here
            $(document).ready( function () {
                $('#myTable').DataTable({responsive: true});
            } );
        </script> -->
    <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        
        <script>
            $(document).ready(function () {
                $('#myTable').DataTable({responsive: true});
            });

            document.getElementById('download').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                const filterOption = document.getElementById('filter').value;

                // Add title
                doc.setFontSize(18);
                doc.text('Users Book - Flight List', 14, 22);

                var columns = [];
                var rows = [];

                // Get column names
                // $('#myTable thead tr th').each(function() {
                //     columns.push($(this).text());
                // });

                 // Get column names, excluding the "Action" column
                 $('#myTable thead tr th').each(function(index) {
                    if (index !== 7) { // Assuming the "Action" column is the last column
                        columns.push($(this).text());
                    }
                });

                // Get all data rows from DataTable
                var table = $('#myTable').DataTable();
                var allData = table.rows({search: 'applied'}).data();

                // Filter data based on the selected option
                var currentDate = new Date().toISOString().split('T')[0];

                function filterRow(rowData) {
                    return rowData.slice(0, 7); // Exclude the last column (Action)
                }

                switch(filterOption) {
                    case 'today':
                        allData.each(function(rowData) {
                            var paymentDate = new Date(rowData[6]); // Assuming the payment date is in the 6th column
                            if (isToday(paymentDate)) {
                                rows.push(rowData);
                            }
                        });
                        break;
                    case 'week':
                        allData.each(function(rowData) {
                            var paymentDate = new Date(rowData[6]); // Assuming the payment date is in the 6th column
                            if (isThisWeek(paymentDate)) {
                                rows.push(rowData);
                            }
                        });
                        break;
                    case 'month':
                        allData.each(function(rowData) {
                            var paymentDate = new Date(rowData[6]); // Assuming the payment date is in the 6th column
                            if (isThisMonth(paymentDate)) {
                                rows.push(rowData);
                            }
                        });
                        break;
                    case 'page':
                        var pageData = table.rows({page: 'current'}).data();
                        pageData.each(function(rowData) {
                            rows.push(rowData);
                        });
                        break;
                    case 'all':
                        rows = allData.toArray();
                        break;
                }

                // // Convert data to autoTable format
                // var autoTableData = [];
                // rows.forEach(function(row) {
                //     autoTableData.push(row);
                // });
                const autoTableData = rows.map(row => {
                    return row.map((cell, index) => {
                        if (index === 4) { // The "Booking Status" column
                            const tempDiv = document.createElement("div");
                            tempDiv.innerHTML = cell;
                            return tempDiv.textContent || tempDiv.innerText || "";
                        }
                        return cell;
                    });
                });

                // Generate autoTable
                doc.autoTable({
                    head: [columns],
                    body: autoTableData,
                    startY: 30,
                    headStyles: {
                        halign: 'center'
                    }
                });

                // Save PDF
                const fileName = `payment-list_${currentDate}.pdf`;
                doc.save(fileName);
            });

            // Helper functions to check if a date is today, this week, or this month
            function isToday(date) {
                const today = new Date();
                return date.getDate() === today.getDate() &&
                       date.getMonth() === today.getMonth() &&
                       date.getFullYear() === today.getFullYear();
            }

            function isThisWeek(date) {
                const today = new Date();
                const weekStart = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay());
                const weekEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay() + 6);
                return date >= weekStart && date <= weekEnd;
            }

            function isThisMonth(date) {
                const today = new Date();
                return date.getMonth() === today.getMonth() &&
                       date.getFullYear() === today.getFullYear();
            }
        </script>