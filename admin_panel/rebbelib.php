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
$select = "select * from rebbelib where id=1";
$res= mysqli_query($conn,$select)or die(mysql_error());
$row=mysqli_fetch_array($res);

if(isset($_GET['sf'])){
    if($_REQUEST['sf']=='delete')
    {
    $id=$_GET['id'];

    $del="update messages set status='inactive' where id='$id'";	
    mysqli_query($conn,$del);
    echo "<script type='text/javascript'> document.location ='rebbelib.php'; </script>";
    }

 }
?>
 <?php 
            include "includes/header.php";
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Update Rebbelib content</strong>
                                  <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="">title</label>
                                            <input type="text" class="form-control" name="title" value="<?php  echo $row['title'];?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="">Sub Title</label>
                                            <input type="text" class="form-control" name="sub-title" value="<?php  echo $row['sub_title'];?>">
                                        </div>
                                
                                        <div class="col-md-12 mb-3">
                                            <label for="">Vision Content</label>
                                            
                                            
                                            <textarea id="editor" name="editor" >
                                                <?php  echo $row['vision'];?>

                                            </textarea>
                                        </div>
                                       
                                       
                                        <div class="col-md-12 mb-3">
                                            <label for="">Rebbelib pdf link</label>
                                            <input type="text" class="form-control" name="pdf-link" value="<?php  echo $row['rebbelib_pdf_link'];?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="">Download Content</label>
                                            <input type="text" class="form-control" name="download-content" value="<?php  echo $row['download_content'];?>">
                                        </div>
                                        <!-- <div class="col-md-6 mb-3">
                                            <label for="">Department</label>
                                            <input type="text" class="form-control" name="department" value="<?php  echo $row['department'];?>">
                                        </div> -->
                                    </div>   
                                    
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" class="btn btn-primary">
                                
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Account Settings End -->
            <div class="container-fluid pt-4 px-4">
                <button type="button" class="btn text-center btn-primary" data-bs-toggle="modal" data-id=""  data-bs-target="#RebbelibModal">ADD Messages</button>
            </div>

            <?php
             include "add-rebbelib-message.php";

            ?>
             <?php
             include "list-rebbelib-message.php";

            ?>
<?php
include "includes/footer.php";
?>
<?php
}
if(isset($_POST['submit']))
        {
           
           
            $title=$_POST['title'];
            $subTitle=$_POST['sub-title'];
            $pdfLink=$_POST['pdf-link'];
            $doenloadContent=$_POST['download-content'];
            $content=$_POST['editor'];
            $currentDate = date('Y-m-d'); 
                    
                    $update="update rebbelib set title='$title',sub_title='$subTitle',rebbelib_pdf_link='$pdfLink',download_content='$doenloadContent',vision='$content',updted_at='$currentDate' where id=1";	

                    $query=mysqli_query($conn, $update);
                    if ($query) {
                    echo "<script>alert('You have successfully updated the data');</script>";
                    echo "<script type='text/javascript'> document.location ='rebbelib.php'; </script>";
                    } else{
                    echo "<script>alert('Something Went Wrong. Please try again');</script>";
                    }
                // }
                

           
         
        }
        

?>
