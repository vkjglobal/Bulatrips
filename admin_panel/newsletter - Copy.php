<?php 
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
$newsletter= $objContent->getListNewsletters();
  //print_r($newsletter);
  $i  =   0;
        ?>


            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Newsletters</strong>
                    <div class="d-flex align-items-center justify-content-end mb-4">
                        
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0 contact-list-table"  id="myTable">
                            <thead>
                                <tr class="">
                                    <th scope="col">Sl No.</th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                 foreach($newsletter as $k => $row) {
                                $i++;
                                $mid    =   "sendMessage".$row['id'];
                            ?>
                                <tr>
                                    <td><?php echo  $i;?></td>
                                   
                                    <td><?php  echo $row['name'];?></td>
                                    <td><?php  echo $row['email'];?></td>
                                    <td>
                                <!--  <button class="btn text-secondary view" data-bs-toggle="modal" data-bs-target="#viewMessage">  
                                            <i class="fa fa-eye">&nbsp;</i>
                                        </button>  -->
                                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#<?php echo $mid;?>">Send Reply</button>
                                    </td>
                                    
                                </tr>
                                <?php 
                               
                                }  ?>
                                
                                
                            </tbody>
                        </table>

                    
                    
                    </div>
                </div>
            </div>
            <!-- Product List End -->

            <!-- View Message -->
          
            <div class="modal fade" id="viewMessage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="viewMessage" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Message</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row"  >
                                <div class="col-12 mb-2">
                                    <strong class="d-block">Test</strong>
                                    <span>test@gmail.com</span>
                                </div>
                                <div class="col-12 mb-2">
                                    <p>
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum </p>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- End View Message -->
            <!-- Send Message -->
              <?php  foreach($newsletter as $k => $row) {
                $uid=$row['id'];
                $email  = $row['email'];
                $mid    =   "sendMessage".$uid;
                ?>
            <div class="modal fade" id="<?php echo $mid;?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="sendMessage" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Send Reply</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row" id="<?php echo $uid; ?>"  onsubmit="myFunction(event,<?php echo $uid ?>,'<?php echo $email ?>')">
                                <div class="col-12 mb-2">
                                    <label for="">Subject</label>
                                    <input type="text" class="form-control" id="subject">
                                     <span class="errortext" id="subjectError" style="color:red"></span>
                                </div>
                                <div class="col-12 mb-2">
                                    <label for="">Message</label>
                                    <textarea name="" id="message" cols="30" rows="6" class="form-control"></textarea>
                                     <span class="errortext" id="messageError" style="color:red"></span>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-secondary">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
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

            function myFunction(event, formId,email){
                  event.preventDefault(); // Prevent form submission
                  var newsId = parseInt(formId);
           // alert(newsId); return false;
            var message = document.getElementById(formId).elements["message"].value;
            var messageError = document.getElementById(formId).querySelector('#messageError');


            var subject = document.getElementById(formId).elements["subject"].value;
            var subjectError = document.getElementById(formId).querySelector('#subjectError');

             var modalId    =   "#sendMessage"+newsId;
// var addMoreButton = document.querySelector('[data-bs-target="'+modalId+'"]');
    
  //alert(email);

     // Validate form data
           
            if (subject.trim() === '') {
                //alert("kkkkk");
            subjectError.textContent = 'Enter subject';     
                return false;
            }
              else if (message.trim() === '') {
                        //alert("kkkkk");
                    messageError.textContent = 'Enter Message';     
                        return false;
                    }        
      // return false;
    
        $.ajax({
                url: 'newsletter_mail.php', // Replace with your form processing script
                type: 'POST',
                 data: { subject: subject, message: message,email: email },
                //data: $(this).serialize()+ '&amount=' +amountprev,
                success: function(response) {
              //alert(response);return false;
                    // Handle the response here
                    if (response === 'success') {
                        //alert('kkkkk');
                          messageError.textContent = 'Successfully sent';
                         
                          // Refresh the form on modal close
                      $(modalId).on('hidden.bs.modal', function() {
                        location.reload();
                      });

                         
                    } else {
                        // alert('hhh');
                       messageError.textContent = 'Failed to send';
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            
        });
}

        </script>
            <!-- End Send Message -->
            
            <?php 
            include "includes/footer.php";
        ?>
         <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 