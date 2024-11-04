
<?php 
 session_start(); 
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
else {
include "includes/dbConnect.php";
if(isset($_GET['sf'])){
    if($_REQUEST['sf']=='delete')
    {
    $id=$_GET['id'];

    $del="update users set status='inactive' where id='$id'";	

    mysqli_query($conn,$del);
    }

 }

?>

        <?php 
            include "includes/header.php";
        ?>


            <!-- Product List Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="border-primary rounded p-4">
                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">Package Discount List</strong>    
                <div class="d-flex align-items-center justify-content-between mb-4">
                        <a href="add-package-discount.php" class="btn btn-primary">Add Package Discount</a>
                        <a href="">Show All</a>
                    </div>
                    
                    <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="">
                                            <th scope="col">Sl No.</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Package</th>
                                            <th scope="col">Discount (%)</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $ret=mysqli_query($conn,"select * from users where status='active'");
                                        $cnt=1;
                                        $row=mysqli_num_rows($ret);
                                        if($row>0){
                                        while ($row=mysqli_fetch_array($ret)) {

                                    ?>
                                        <tr>
                                            <td><?php echo $cnt;?></td>
                                            <!-- <td><div class="prdct-img"> -->
                                                <!-- <img src="img/NOTEBOOK.png" alt=""> -->
                                            <!-- <img src="uploads/<?php  echo $row['image'];?>"
                                            </div></td> -->
                                            <td>Name 1</td>
                                            <td>Package 1</td>
                                            <td>30%</td>
                                            <td>
                                                <div class="d-flex action">
                                                
                                                    <a class="btn text-secondary edit" href="edit-package-discount.php">
                                                        <i class="fa fa-pen">Edit</i>
                                                    </a>
                                                    <a class="btn text-secondary delete" onClick="return confirm('are you sure you want to delete?')" href="members-list.php?sf=delete&id=<?php echo $row['id'];?>">
                                                    <i class="fa fa-trash">Delete</i>
                                                    </a>
                                                
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                        $cnt=$cnt+1;
                                        } } else {?>
                                        <tr>
                                        <th style="text-align:center; color:red;" colspan="6">No Record Found</th>
                                        </tr>
                                        <?php } ?>
                                        
                                        
                                    </tbody>
                                </table>
                            </div>
                    
                </div>
            </div>
            <!-- Product List End -->


            <!-- Footer Start -->
            <?php
include "includes/footer.php";
?>
<?php
}
?>