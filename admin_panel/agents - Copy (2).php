<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin user page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for admin user page
*****************************************************************************/
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
    $filteredData[] = [$i,$row['first_name']." ".$row['last_name'],$row['email'],$row['mobile'],$row['agent_status'],$row['credit_balance'],$row['id']];
   
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
            <div class="modal fade" id="addMore" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Add More Balance</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row">
                                <div class="col-12 mb-2">
                                    <label for="">Add Amount</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-secondary">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
            <div id='success-message' class='alert alert-success alert-dismissible fade show' style="display: none;" role='alert'>

                    Status Updated successfully.

                        <button type='button' id="alertclose" class='close' data-dismiss='alert' aria-label='Close'>

                        <span aria-hidden='true'>&times;</span>

                        </div>
            
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
     //alert(row[6]);userid
      tableRows += "<tr>";
      tableRows += "<td>" + row[0] + "</td>";
      tableRows += "<td>" + row[1] + "</td>";
      tableRows += "<td>" + row[2] + "</td>";
      tableRows += "<td>" + row[3] + "</td>";
      tableRows += "<td> <span class='user-active'><input id='" + checkboxId + "' type='checkbox' " + row[4] + " onclick='toggleStatus(this, " + row[6] + ")'><label for='" + checkboxId + "' class='btn btn-sm'></label></span></td>";
      tableRows += "<td><strong class='me-2'><span>&#8377; "+ row[5] +"</span></strong><button class='btn btn-sm btn-secondary' data-bs-toggle='modal' data-bs-target='#addMore'>Add More</button></td>";
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
           $('#success-message').css('display', 'block');
        
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
  var successMessage = document.getElementById('success-message');

                            setTimeout(function() {

                              successMessage.remove();

                            }, 5000); // Remove message after 5 seconds

</script>
            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
