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

            include "includes/header.php";
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add Package</strong>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="">Title</label>
                                            <input type="text" class="form-control" name="name" id="name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="">Category</label>
                                            <select class="form-control" id="position" name="position">
                                                <option value="">Select.....</option>
                                                <option value="">Category 1</option>
                                                <option value="">Category 2</option>
                                                <option value="">Category 3</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Strick Price</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Price per adult (12+ years)</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Price per child (2 - 12 years)</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Price per infant (0 - 2 years)</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Number of Days</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Number of Nights</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Destination</label>
                                            <input type="text" class="form-control" name="" id="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Overview</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Include/Exclude</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Itinerary</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Payment Terms</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Cancellation Policy</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Terms and Conditions</label>
                                            <textarea id="" name="" rows="4" cols="50" class="form-control"> </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Upload Package Image</label>
                                            <label class="uploadFile form-control" id="imageerror">
                                                <span class="filename"></span>
                                                <input type="file" class="inputfile" name="member-image" id="member-image">
                                            </label>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" id="submit" class="btn btn-primary btn-typ3">
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    <a href="packages-list.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Footer Start -->
            <?php 
            include "includes/footer.php";
             
}
if(isset($_POST['submit']))
        {
            // echo "<script>alert('test');</script>";

            $name=$_POST['name'];
            $description=trim($_POST['description']);
            $position=$_POST['position'];
            $boardMember = isset($_POST['board-member']) ? 1 : null;
            $teamMember = isset($_POST['team-member']) ? 1 : null;
           
                $ppic=$_FILES["member-image"]["name"];
                // get the image extension
                $extension = substr($ppic,strlen($ppic)-4,strlen($ppic));
                // allowed extensions
                $allowed_extensions = array(".jpg","jpeg",".png");
                if($ppic){
                    if(!in_array($extension,$allowed_extensions))
                    {
                        echo "<script>alert('Invalid format. Only jpg / jpeg/ png /gif format allowed');</script>";
                    }
                    $imgnewfile=md5($ppic).time().$extension;
                    // Code for move image into directory
                     move_uploaded_file($_FILES["member-image"]["tmp_name"],"uploads/about/members/".$imgnewfile);
                }else{
                    $imgnewfile='';
                }
                
                
                    $query=mysqli_query($conn, "insert into members(name,position,image,description,board_member,team_member) value('$name','$position','$imgnewfile' ,'$description',' $boardMember',' $teamMember')");
                    if ($query) {
                    echo "<script>alert('You have successfully inserted the data');</script>";
                    echo "<script type='text/javascript'> document.location ='packages-list.php'; </script>";
                    } else{
                    echo "<script>alert('Something Went Wrong. Please try again');</script>";
                    }
                // }
                
         
        }
?>

<script>
    $(document).ready(function(){

        $("#submit").click(function () {
            
            valid = true;
           
            $(".errortext").remove();
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Title cannot be blank.</span>')	       
            valid = false;
            }
            if($('#image').val() == '') {
            $('#imageerror').after('<span class="errortext" style="color:red">Image cannot be blank.</span>')	       
            valid = false;
            }
        
           
            // if($('#sub_category').val() == 0) {
            // $('#sub_category').after('<span class="errortext" style="color:red">Sub Category cannot be blank.</span>')	       
            // valid = false;
            // }
           
            
            if( !valid ){       
            return valid;
            }	
        });
    });
</script>