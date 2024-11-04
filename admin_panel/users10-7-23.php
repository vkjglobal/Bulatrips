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
include_once "includes/header.php";
include_once "includes/class.users.php";
$objUsers		= 	new Users();
if(!isset($_GET['sortId']))
{
    $sortId =  'id' ;
}
else{
    $sortId  =   $_GET['sortId'];
}
$datas	=   $objUsers->getUsersList();
//echo "<pre/>";  print_r($datas);

$filteredData = [];
$i  =   0;
foreach ($datas as $row) {
   $i++;
    $filteredData[] = [$i,$row['first_name']." ".$row['last_name'],$row['email'],$row['mobile'],$row['username'],$row['status'],$row['id']];
   
}
//echo json_encode($filteredData);
//exit;
//echo "<pre/>";
//print_r($filteredData);exit;
//echo json_encode($data);
?>
    <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Users</strong>
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
                                    <th onclick="sortTable(4)" scope="col">Username</th>
                                    <th onclick="sortTable(5)" scope="col">Status</th>
                                    <th scope="col">Action</th>
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
     //alert(row[6]);userid
      tableRows += "<tr>";
      tableRows += "<td>" + row[0] + "</td>";
      tableRows += "<td>" + row[1] + "</td>";
      tableRows += "<td>" + row[2] + "</td>";
      tableRows += "<td>" + row[3] + "</td>";
      tableRows += "<td>" + row[4] + "</td>";
      tableRows += "<td>" + row[5] + "</td>";
      tableRows += '<td><a href="user-details.php?id=' + row[6] + '"><i class="fa fa-eye">&nbsp;</i></a><a class="btn text-secondary delete"  onclick="return confirm("are you sure you want to delete?")" href="user-details.php?action=delete&amp;uid=' + row[6] + '" ><i class="fa fa-trash">&nbsp;</i></a></td>';
      tableRows += "</tr>";
    }
    
    tbody.innerHTML = tableRows;
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
</script>
 
            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
