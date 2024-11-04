<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin Newsletter
   Programmer	::> Soumya
   Date		::> 18-8-23
   DESCRIPTION::::>>>>
   This is  code used for admin newsletter 
*****************************************************************************/
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
include_once "includes/header_listing.php";
include_once "includes/class.contents.php";
$objContent     =   new Contents();
$newsletter= $objContent->getListNewsletters_New();
//echo "<pre/>";  print_r($newsletter);

$i  =   0;

?>
    <!-- Product List Start -->

            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Newsletter Data </strong>
                        <a href="add-edit-newsletter.php" class="btn btn-primary">Add Newsletter</a>   
                    <div class="d-flex align-items-center justify-content-end mb-4">
          
                              </div>
                    <div class="table-responsive">
                        <table id="myTable" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th  scope="col">Sl No.</th>
                                    <th  scope="col">Title</th>
                                    <th  scope="col">Subject</th>  
                                    <th scope="col">Action</th> 
                                </tr>
                            </thead>
                            <tbody>
                             <?php  
                        
                            foreach($newsletter as $k => $row) {
                                $i++;
                             //  echo "<pre/>"; print_r($row);
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>                           
                            <td><?php echo $row['title'] ?></td>
                             <td><?php echo $row['subject']; ?></td>
                                                                            
                           <td><a href="add-edit-newsletter.php?id=<?php echo $row['id']; ?>"><i class="fa fa-pen">&nbsp;</i></a><a class="btn text-secondary delete"  onclick="return confirm("are you sure you want to delete?")" href="add-edit-newsletter.php?action=delete&amp;uid=<?php echo $row['id']; ?>" ><i class="fa fa-trash">&nbsp;</i></a>
                            <button class="btn btn-sm btn-secondary" id="bulkmail" onclick="sendMailBulk(<?php echo $row['id'];?>);">Send Bulk Mail</button>
</td> 
                                 
                        </tr>
                    <?php }  ?>
                            
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
            function sendMailBulk(id)
            {
              //  alert(id);return false;
                $.ajax({
                url: 'newsletter_mail.php', // Replace with your form processing script
                type: 'POST',
                 data: { id: id},
                //data: $(this).serialize()+ '&amount=' +amountprev,
                success: function(response) {
                    alert(response);return false;
                    // Handle the response here
                    if (response === 'success') {
                        //alert('kkkkk');
                          amountError.textContent = 'Successfully Added'; 
                          // Refresh the form on modal close
  $(modalId).on('hidden.bs.modal', function() {
    location.reload();
  });
                         
                    } else {
                        // alert('hhh');
                         amountError.textContent = 'Error in Adding Balance'; 
                        
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            
        });
            }
        </script>
    <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 
           
