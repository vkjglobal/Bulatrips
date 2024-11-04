<?php 
    session_start(); 
    if(!isset($_SESSION['adminid'])){
?>
    <script>
    window.location="index.php"    </script>
<?php
    }
    include_once "includes/header_listing.php";
    include_once "includes/class.payment.php";
    
    $objBooking = new Payment();

    $cancel = $objBooking->canceled_flight_list();
    // print_r($cancel);
    $i = 0;
?>
    <div class="container-fluid pt-4 px-4">
        <div class="border-primary rounded p-4">
            <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">List of canceled flight details</strong>    
            <!-- <div class="d-flex align-items-center justify-content-end mb-4">
                <button type="button" id="download">Download</button>
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

            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0" id="myTable">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col">Id</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Ticket Number</th>
                            <th scope="col">Refund Amount</th>
                            <th scope="col">Currency</th>
                            <th scope="col">Payment Date and Time</th>
                            <th scope="col">Refund status</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php  
                            foreach($cancel as $list){
                        ?>
                            <tr>
                                <td><?php echo ++$i; ?></td>
                                <td><?php echo $list['first_name'].' '.$list['last_name']; ?></td>
                                <td><?php echo $list['email']; ?></td>
                                <td><?php echo $list['mobile']; ?></td>
                                <td><?php echo $list['ticket_number']; ?></td>
                                <td><?php echo $list['total_refund_amount']; ?></td>
                                <td><?php echo $list['currency']; ?></td>
                                <td><?php echo $list['created_at']; ?></td>
                                <td><?php echo ''; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody> 
                </table> 
            </div>
    <?php include "includes/footer.php"; ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 
        <!-- <script>
            // tableapi code
            // Awesome JS Code Goes here
            $(document).ready( function () {
                $('#myTable').DataTable({responsive: true});
            } );

            document.getElementById('download').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Add title
            doc.setFontSize(18);
            doc.text('Payment List of Agents', 14, 22);

            var columns = [];
            var rows = [];

            $('#myTable thead tr th').each(function() {
                columns.push($(this).text());
            });

            $('#myTable tbody tr').each(function() {
                var rowData = [];
                $(this).find('td').each(function() {
                    rowData.push($(this).text());
                });
                rows.push(rowData);
            });

            doc.autoTable({
                head: [columns],
                body: rows,
                startY: 30,  // Adjust starting point to avoid overlap with title
                headStyles: {
                    halign: 'center'  // Center align the header text
                }
            });

            doc.save('table.pdf');
        });
            
        </script> -->
    
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
                doc.text('Canceled Book - Flight List', 14, 22);

                var columns = [];
                var rows = [];

                // Get column names
                $('#myTable thead tr th').each(function() {
                    columns.push($(this).text());
                });

                // Get all data rows from DataTable
                var table = $('#myTable').DataTable();
                var allData = table.rows({search: 'applied'}).data();

                // Filter data based on the selected option
                var currentDate = new Date().toISOString().split('T')[0];
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

                // Convert data to autoTable format
                var autoTableData = [];
                rows.forEach(function(row) {
                    autoTableData.push(row);
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