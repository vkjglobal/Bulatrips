
<?php 

session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"
  </script>
  <?php
}


 include_once "includes/header.php";
include_once "includes/class.packages.php";
$objPackage		= 	new Packages();
/*
include "includes/dbConnect.php";
if(isset($_GET['sf'])){
    if($_REQUEST['sf']=='delete')
    {
    $id=$_GET['id'];

    $del="update members set status='inactive' where id='$id'";	

    mysqli_query($conn,$del);
    }

 */
 //=================
 if((isset($_GET['id']))  && (isset($_GET['action'] )))
{
      if($_GET['action'] === 'delete'){
        
        $pid =  $_GET['id'] ;       
        $categoryDel	=   $objPackage->DelCategory($pid);
        if($categoryDel){
            echo "<script>";
echo "document.addEventListener('DOMContentLoaded', function() {";
echo "    var delSuccessPop = document.getElementById('delsuccesspop');";
echo "    if (delSuccessPop) {";
echo "        delSuccessPop.classList.add('show');";
echo "        delSuccessPop.style.display = 'block';";
echo "    }";
echo "});";
echo "</script>";
                     
                       // echo "<script>jQuery('#delsuccesspop').modal('show');</script>";
                      //  header('Locatiion:users.php'); exit;
                      // echo "deleted succesfully";
        }
        else{
            echo "error in deletion";
        }
      }
    //  exit;
}
 //=======================

$searchQuery = isset($_POST['searchInput']) ? $_POST['searchInput'] : '';

// Add the search query to the SQL query if provided
$sql = " WHERE `status` = 'active' ";
if (!empty($searchQuery)) {
    $sql.= " AND `title` LIKE '%".$searchQuery."%'";
}

 $listcategory	=   $objPackage->listallCategory($sql);
 //====================================
 /*if($parentId !=0){
      $getparentCategory	=   $objPackage->getCategoryinfo($parentId);
      $parentTitle          =   $getparentCategory[0]['title'];
 } */
 //======================================================
$filteredData = [];
$i  =   0;
foreach ($listcategory as $row) {
   $i++;
    $filteredData[] = [$i,$row['id'],$row['title'],$row['parent']];
    
}
 //echo json_encode($filteredData);
//print_r($filteredData);
?>
            <!-- Product List Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="border-primary rounded p-4">
                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Package Category List</strong>    
                <div class="d-flex align-items-center justify-content-between mb-4">
                 <a href="add-package-category.php" class="btn btn-primary">Add Package Category</a>
                         <!-- Search Bar -->
                        <div class="row mt-3 mb-3">
                        <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search">
                      </div>
                    </div>
                    </div>
                    
                    <div class="table-responsive">
                                <table  id="tableData" class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="">
                                            <th onclick="sortTable(0)" scope="col">Sl No.</th>
                                            <th onclick="sortTable(2)" scope="col">Title</th>
                                            <th onclick="sortTable(3)" scope="col">Parent</th>
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
             <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

  var data = <?php echo json_encode($filteredData); ?>; // Replace $data with your actual data from PHP
  //alert(data);
  var filteredData = data.slice(); // Make a copy of the original data for filtering
  var currentPage = 1;
  var itemsPerPage = 10; // Adjust this value based on the number of rows to display per page
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
   renderTable();
  updatePagination();
  sortTable(sortColumn);
}

  function renderTable() {
    var table = document.getElementById("tableData");
    var tbody = table.getElementsByTagName("tbody")[0];
    
    var startIndex = (currentPage - 1) * itemsPerPage;
    var endIndex = startIndex + itemsPerPage;
      // Adjust endIndex based on the length of the filteredData array
  if (endIndex > filteredData.length) {
    endIndex = filteredData.length;
  }
    var tableRows = "";
      if (filteredData.length === 0) {
    tableRows += "<tr>";
    tableRows += "<td colspan='4' class='text-center'>No records found</td>";
    tableRows += "</tr>";
  } else {

  for (var i = startIndex; i < endIndex; i++) {
      var row = filteredData[i];
     //alert(row[1]);userid
      tableRows += "<tr>";
      tableRows += "<td>" + row[0] + "</td>";
      tableRows += "<td>" + row[2] + "</td>";
      tableRows += "<td>" + row[3] + "</td>";
      tableRows += '<td><div class="d-flex action"><a class="btn text-secondary edit" href="edit-package-category.php?id=' + row[1] + '"><i class="fa fa-pen">Edit</i></a><a class="btn text-secondary delete" onClick="return confirm("are you sure you want to delete?")" href="package-category-list.php?action=delete&id='+row[1]+'"><i class="fa fa-trash">Delete</i></a></div></td>';
      tableRows += "</tr>";
    }
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
  function backuserpage() {
     window.location = "package-category-list.php";
}</script>
</script>

            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
