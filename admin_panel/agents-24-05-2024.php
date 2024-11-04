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
//print_r($_SESSION);
if((!isset($_SESSION['adminid'])) ||(empty($_SESSION['adminid']))){
ob_end_clean();
header("Location: index.php");
 //header('Locatiion: index.php'); // Redirect to the login page
    exit;
}
include_once "includes/header_listing.php";
include_once "includes/class.agents.php";
$objAgents		= 	new Agents();

$datas	=   $objAgents->getDashboardAgents($offset='');
//echo "<pre/>";  print_r($datas);exit;

$filteredData = [];
$i  =   0;

$balancemore    =   true;
if(isset($_POST['addbalance']))
{  
    if(empty($_POST['addbalance'])){
                                  $balancemore    =   false;
    }
}
//echo json_encode($filteredData);
//exit;
?>


            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Agents</strong>
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
                                    <th scope="col">Action</th>
                                    <th scope="col">Balance</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                foreach($datas as $k => $row) { 
                                    //========================
                                            $i++;
                                         
                                           if($row['agent_status'] == 'active'){
                                               $row['agent_status'] =   'checked';

                                           }
                                           else{
                                               $row['agent_status'] =   '';
                                           }
                                           if(($row['credit_balance'] ===   NULL)  || ($row['credit_balance'] == "")) {
                                               $row['credit_balance']   = 0;
                                           }
                                            $filteredData[] = [$i,$row['first_name']." ".$row['last_name'],$row['email'],$row['mobile'],$row['agent_status'],$row['credit_balance'],$row['id']];
   
                                    //=========================

                                $id =   $row['id'];                              
                               $checkboxId = "checkbox".$i; // Generate unique checkbox ID
                                 $buttonId = "button".$id;
                                  $modalId = "#addMore".$id;


                             //  echo "<pre/>"; print_r($row);
                             
                    ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
                            <td><?php echo $row['email'] ?></td>
                              <td><?php echo $row['mobile']; ?></td>
                             <td> <span class='user-active'><input id="<?php echo $checkboxId; ?>"  type="checkbox" <?php echo $row['agent_status']; ?> onclick='toggleStatus(this,<?php echo $row['id']; ?>)'><label for='<?php echo $checkboxId; ?>' class='btn btn-sm'></label></span></td>
                             <td><strong class='me-2'><span>&#8377; <?php echo $row['credit_balance']; ?></span></strong><button name='addbtn' id= '<?php echo $buttonId; ?>' class='btn btn-sm btn-secondary' data-bs-toggle='modal' data-bs-target='<?php echo $modalId; ?>' data-amount="<?php echo $row['credit_balance']; ?>" data-uid="<?php echo $row['id']; ?>">Add More</button></td>
      <td> <a href="agent-details_main.php?id=<?php echo $row['id']; ?>"><i class="fa fa-eye">&nbsp;</i></a></td>
      </tr>
                                    <?php }  ?>
                            </tbody>
                        </table>
                        
                    
                </div>
            </div>
            <!-- Product List End -->

            <!-- Add More Balance Start -->
            <?php foreach($filteredData as $k =>$v){ 
               // print_r($v);
                $mid    =   "addMore".$v[6];
                $uid    =   $v[6];
               // echo $mid."********************************************";
                ?>
            <div class="modal fade" id="<?php echo $mid;?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Add More Balance</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row" id="<?php echo $uid; ?>"  onsubmit="myFunction(event,<?php echo strval($uid); ?>)">
                                <div class="col-12 mb-2">
                                    <label for="">Add Amount</label>
                                    <input type="text" id="amount" name="amount" class="form-control">                                 
                                            <span class="errortext" id="amountError" style="color:red"></span>
                                            
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button name="addbalance"  id="addBalanceButton" type="submit"  class="btn btn-secondary">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <!-- Add More Balance End -->
            <!------ success alert message pop up ----->
             <!--Start -->
            <div class="modal fade" id="successpop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">successfully updated</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->
            <!-- pop up ends -->
      <!--  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>  -->
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
  function toggleStatus(checkbox, rowId) {
  // Get the status based on the checkbox checked property
  var status = checkbox.checked ? 1 : 0;

  // Perform an AJAX request or any other method to update the database with the new status
  // You can use the rowId to identify the specific row in the database

  // Example AJAX request using jQuery
  $.ajax({
    url: 'agent-details_main.php',
    method: 'POST',
    data: { rowId: rowId, status: status },
    success: function(response) {
        //console.log(response);
       // alert(response);
       if ($.trim(response) == 'success') {
        $('#successpop').modal('show');
        return false;
        }
        else if(($.trim(response) == 'err1') || ($.trim(response) == 'err2')){
            alert("error");
            }
      // Handle the success response
      // For example, you can show a success message or update the UI accordingly
      //showSuccessMessage('Status updated successfully');
    },
    error: function(xhr, status, error) {
      // Handle the error response
      // For example, you can show an error message or handle the error condition
      console.log('Error updating status:', error);
    }
  });
}

  
  //=====
  function myFunction(event, formId) {
  event.preventDefault(); // Prevent form submission
  var userid    =   parseInt(formId);
  //alert(userid);
    var amount = document.getElementById(formId).elements["amount"].value;
    var amountError = document.getElementById(formId).querySelector('#amountError');

   // alert(amount);
   var modalId    =   "#addMore"+userid;
   var addMoreButton = document.querySelector('[data-bs-target="'+modalId+'"]');

    var amountprev = addMoreButton.dataset.amount;
    var reg = /^\d+(\.\d{1,2})?$/;
     // Validate form data
            if (amount.trim() === '') {
                //alert("kkkkk");
            amountError.textContent = 'Enter Amount';           
                return false;
            }
            else if(!reg.test(amount)){
               // alert("Enter real amount!");
                  amountError.textContent = 'Enter real amount!'; 
      //alert("Enter real amount!");
                  return false;
            }    
    //alert(amountprev);return false;
  // Access input values using their id attributes
  //var input1Value = form.querySelector('#amount').value;alert(input1Value);
  //var input2Value = form.querySelector('#input2').value;
        $.ajax({
                url: 'agent-details_main.php', // Replace with your form processing script
                type: 'POST',
                 data: { amount: amount, balanceamount: amountprev,userid: userid },
                //data: $(this).serialize()+ '&amount=' +amountprev,
                success: function(response) {
                    //alert(response);
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

  //=================================
  function addmore(mid){
            var uid    =    mid;//alert(uid);
           //  var amountval = document.getElementById("amount");
            var amount      = $('#amount').val(); alert(amount);
            // Store the uid value as a data attribute on the form
  $('#addBalanceForm').data('uid', uid);
  $('#addBalanceForm').data('amount',amount);
  // Handle form submission using event delegation
  $(document).on('submit', '#addBalanceForm', function(event) {
    event.preventDefault(); // Prevent form submission
   
    // Retrieve the uid value from the form's data attribute
    var uid = $('#addBalanceForm').data('uid');
    alert(uid);
    var amount = $('#addBalanceForm').data('amount');
           // var amount = $('#amount').val();
             
            var reg = /^\d+(\.\d{1,2})?$/;
            alert(amount);
            // Validate form data
            if (amount.trim() === '') {
                $('#amountError').text('Enter Amount');
                return false;
            }
            else if(!reg.test(amount)){
               // alert("Enter real amount!");
                  $('#amountError').text('Enter real amount!');
      //alert("Enter real amount!");
                  return false;
            }         alert("uu")   ;
            var addMoreButton = document.querySelector('[data-bs-target="#addMore"]');
            
            var amountprev = addMoreButton.dataset.amount;
            var uid         = addMoreButton.dataset.uid;
           alert(amountprev);
          
            // Send form data via AJAX
            $.ajax({
                url: 'agent-details_main.php', // Replace with your form processing script
                type: 'POST',
                 data: { amount: amount, balanceamount: amountprev,userid: uid },
                //data: $(this).serialize()+ '&amount=' +amountprev,
                success: function(response) {
                    alert(response);
                    // Handle the response here
                    if (response === 'success') {
                        //alert('kkkkk');
                         $('#amountError').text('Successfully Added');
                        // Show success message or perform other actions
                    } else {
                        // alert('hhh');
                        $('#amountError').text('Error in Adding Balance');
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            });
        });
    }
    
</script>

            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
 <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script> 
