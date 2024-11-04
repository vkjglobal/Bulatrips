<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin user page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for admin user page
*****************************************************************************/
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
include_once "includes/header_listing.php";
include_once "includes/class.users.php";
$objUsers		= 	new Users();
$datas	=   $objUsers->getUsersList();
//echo "<pre/>";  print_r($datas);

$i  =   0;

?>
    <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Users</strong>
                    <div class="d-flex align-items-center justify-content-end mb-4">
                              </div>
                    <div class="table-responsive">
                        <table id="myTable" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th  scope="col">Sl No.</th>
                                    <th  scope="col">Name</th>
                                    <th  scope="col">Email</th>
                                    <th  scope="col">Phone</th>
                                    <th  scope="col">Username</th>
                                    <th  scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                             <?php  
                        if (count($datas)> 0) {
                            foreach($datas as $k => $row) {
                                $i++;
                             //  echo "<pre/>"; print_r($row);
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
                            <td><?php echo $row['email'] ?></td>
                             <td><?php echo $row['mobile']; ?></td>
                              <td><?php echo $row['username'] ?></td>
                              <td><span class="badge bg-success"><?php echo $row['status']; ?></span></td>                              
                               <td><a href="user-details.php?id=<?php echo $row['id']; ?>"><i class="fa fa-eye">&nbsp;</i></a><a class="btn text-secondary delete"  onclick="return confirm("are you sure you want to delete?")" href="user-details.php?action=delete&amp;uid=<?php echo $row['id']; ?>" ><i class="fa fa-trash">&nbsp;</i></a></td>
                                 
                        </tr>
                    <?php } } ?>
                            
                            </tbody>
                        </table>                        
            </div>
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
           