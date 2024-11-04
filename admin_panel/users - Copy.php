<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 29-06-2023
   DESCRIPTION::::>>>>
   This is  code used for admin dashboard page
*****************************************************************************/
include_once "includes/header.php";
include_once "includes/class.users.php";
$objUsers		= 	new Users();
$UsersList	=   $objUsers->getUsersList();
//print_r($UsersList);exit;

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
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th scope="col">Sl No.</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($UsersList as $key => $val) {
                                $SNO    =   $key+1;                                
                            ?>
                                <tr>
                                    <td><?php echo $SNO; ?></td>
                                    <td><?php echo $val['first_name']." ".$val['last_name']; ?></td>
                                    <td><?php echo $val['email']; ?></td>
                                    <td><?php echo $val['mobile']; ?></td>
                                    <td><?php echo $val['username']; ?></td>
                                    <td><span class="badge bg-success"><?php echo ucfirst($val['status']); ?></span></td>
                                    <td>
                                        <a href="user-details.php"><i class="fa fa-eye">&nbsp;</i></a>
                                        <a class="btn text-secondary delete"
                                            onclick="return confirm('are you sure you want to delete?')"
                                            href="members-list.php?sf=delete&amp;id=1">
                                            <i class="fa fa-trash">&nbsp;</i>
                                        </a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
              <script src="includes/custom.js"></script>
            <!-- Product List End -->


            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
