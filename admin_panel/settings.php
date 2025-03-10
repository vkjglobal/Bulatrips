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
$listmarkup	=   $objBooking->getSettings();
$i  =   0;
?>
            <!-- Product List Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="border-primary rounded p-4">
                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Mark Up Fee For Cancel</strong>    
                <div class="d-flex align-items-center justify-content-between mb-4">
                                    </div>
                    
                    <div class="table-responsive">
                                <table  id="" class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="">
                                            <th  scope="col">ID</th>
                                            <th  scope="col">Key name</th>
                                            <th  scope="col">key Value</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                     <?php  
                        if (count($listmarkup)> 0) {
                            foreach($listmarkup as $k => $row) {?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['key']; ?></td>
                            <td><?php echo $row['value']."%" ?></td>
                            <td><a href="setting_edit.php?id=<?php echo $row['id']; ?>"><i class="fa fa-pen">&nbsp;</i></a></td>
     

                            
                        </tr>
                    <?php }
                        } ?>
                                        
                                    </tbody>
                                </table>
                                </div>
                    
                </div>
            </div>
            <!-- Product List End -->
             <div class="modal fade" id="delsuccesspop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Deleted successfully</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backuserpage()" ></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->
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
            
 