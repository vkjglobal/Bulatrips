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
include_once "includes/header.php";
include_once "includes/class.agents.php";
$objAgents		= 	new Agents();
if(!isset($_GET['sortId']))
{
    $sortId =  'id' ;
}
else{
    $sortId  =   $_GET['sortId'];
}
$datas	=   $objAgents->getDashboardAgents($offset='');
//echo "<pre/>";  print_r($datas);exit;

$filteredData = [];
$i  =   0;
foreach ($datas as $row) {
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
   
}
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
                                 <!-- Search Bar -->
                        <div class="row mt-3 mb-3">
      <div class="col-md-6">
        <input type="text" class="form-control" id="searchInput" placeholder="Search">
      </div>
    </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tableData" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th onclick="sortTable(0)" scope="col">Sl No.</th>
                                    <th onclick="sortTable(1)" scope="col">Name</th>
                                    <th onclick="sortTable(2)" scope="col">Email</th>
                                    <th onclick="sortTable(3)" scope="col">Phone</th>                                   
                                    <th scope="col">Action</th>
                                    <th scope="col">Balance</th>
                                    <th scope="col">View</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                            </tbody>
                        </table>
                        <ul id="pagination" class="pagination">
    <li class="page-item" id="prevButton" onclick="prevPage()">
      <a class="page-link" href="#">Previous</a>
    </li>
    <li class="page-item" id="nextButton" onclick="nextPage()">
      <a class="page-link" href="#">Next</a>
    </li>
  </ul>
                    </div>
                    
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
            <----pop up ends ----->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

  var data = <?php echo json_encode($filteredData); ?>; // Replace $data with your actual data from PHP
  
  var filteredData = data.slice(); // Make a copy of the original data for filtering
  var currentPage = 1;
  var itemsPerPage = 2; // Adjust this value based on the number of rows to display per page
  var sortColumn = -1;
  var sortDirection = 1;
  
  // Call the initial functions to populate the table and set up pagination
  renderTable();
  updatePagination();
  
  $("#searchInput").on("keyup", function() {
    filterTableData();
  });
  
  function filterTableData() {
  var input, filter;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();

  filteredData = data.filter(function(row) {
    return row.some(function(cell) {
      // Check if the cell value is not null or undefined before calling toString()
      if (cell !== null && cell !== undefined) {
        return cell.toString().toUpperCase().indexOf(filter) > -1;
      }
      return false;
    });
  });

  sortTable(sortColumn);
}

  function renderTable() {
    var table = document.getElementById("tableData");
    var tbody = table.getElementsByTagName("tbody")[0];
    
    var startIndex = (currentPage - 1) * itemsPerPage;
    var endIndex = startIndex + itemsPerPage;
    
    var tableRows = "";
    for (var i = startIndex; i < endIndex && i < filteredData.length; i++) {
      var row = filteredData[i];
          var checkboxId = "checkbox" + i; // Generate unique checkbox ID
          var buttonId = "button" + row[6];
          var modalId = "#addMore" + row[6];
     //alert(row[6]);userid
      tableRows += "<tr>";
      tableRows += "<td>" + row[0] + "</td>";
      tableRows += "<td>" + row[1] + "</td>";
      tableRows += "<td>" + row[2] + "</td>";
      tableRows += "<td>" + row[3] + "</td>";
      tableRows += "<td> <span class='user-active'><input id='" + checkboxId + "' type='checkbox' " + row[4] + " onclick='toggleStatus(this, " + row[6] + ")'><label for='" + checkboxId + "' class='btn btn-sm'></label></span></td>";
      tableRows += "<td><strong class='me-2'><span>	&#36 "+ row[5] +"</span></strong><button name='addbtn' id= '"+buttonId+"' class='btn btn-sm btn-secondary' data-bs-toggle='modal' data-bs-target='"+modalId+"' data-amount="+ row[5] +" data-uid="+ row[6] +">Add More</button></td>";
      tableRows += '<td> <a href="agent-details_main.php?id=' + row[6] + '"><i class="fa fa-eye">&nbsp;</i></a></td>';
      tableRows += "</tr>";
    }
    
    tbody.innerHTML = tableRows;
  }
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

  
  function sortTable(columnIndex) {
    if (sortColumn === columnIndex) {
      sortDirection *= -1;
    } else {
      sortColumn = columnIndex;
      sortDirection = 1;
    }
    
    filteredData.sort(function(a, b) {
      var valueA = a[columnIndex];
      var valueB = b[columnIndex];
      
      if (typeof valueA === "string") {
        valueA = valueA.toUpperCase();
        valueB = valueB.toUpperCase();
      }
      
      if (valueA < valueB) {
        return -1 * sortDirection;
      } else if (valueA > valueB) {
        return 1 * sortDirection;
      } else {
        return 0;
      }
    });
    
    renderTable();
    updatePagination();
  }
  
  function prevPage() {
    if (currentPage > 1) {
      currentPage--;
      renderTable();
      updatePagination();
    }
  }
  
  function nextPage() {
    var totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    if (currentPage < totalPages) {
      currentPage++;
      renderTable();
      updatePagination();
    }
  }
  
  function updatePagination() {
    var totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    var prevButton = $("#prevButton");
    var nextButton = $("#nextButton");
    
    if (currentPage === 1) {
      prevButton.addClass("disabled");
    } else {
      prevButton.removeClass("disabled");
    }
    
    if (currentPage === totalPages) {
      nextButton.addClass("disabled");
    } else {
      nextButton.removeClass("disabled");
    }
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
