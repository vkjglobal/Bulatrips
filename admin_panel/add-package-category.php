<?php 
//print_r($_POST);
 session_start();
if(!isset($_SESSION['adminid'])){
    ob_end_clean();
    header("Location: index.php");exit;
} 

            include_once "includes/header.php";             
            include_once "includes/class.packages.php";
            $objPackage		= 	new Packages();
              $listparent	=   $objPackage->listallCategory();
            
        if(isset($_POST['submit']))
        {
             $title =   $_POST['name'];
            //$description=trim($_POST['description']);
            $parentCategory   =   $_POST['position'];
            //print_r($parentCategory );
            //$sql = " WHERE `parent` != 0 ";
          
           // print_r($listparent	);
            if(empty($parentCategory)){
                $parentCategory =   0;
            }
            $addCateg	=   $objPackage->addCategory($title,$parentCategory);
            if($addCateg){
                               
                                 echo "<script>";
                echo "document.addEventListener('DOMContentLoaded', function() {";
                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                echo "    if (addsuccesspop) {";
                echo "        addsuccesspop.classList.add('show');";
                echo "        addsuccesspop.style.display = 'block';";
                echo "    }";
                echo "});";
                echo "</script>";
                    

            /*
                echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
                echo '<script>';
                echo '$("#addsuccesspop").modal("show");';
                echo '</script>';
                header("Location: packages.php");exit; */
            }
            else{

            }

          //  echo $title.$addCateg;exit;
        }
       
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add Package Category</strong>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="">Title</label>
                                            <input type="text" class="form-control" name="name" id="name">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="">Parent</label>
                                            <select class="form-control" id="position" name="position">
                                            <option value="" selected>Select Category</option>
                                            <?php foreach($listparent as $k => $val){
                                               ?>
                                                <option id="<?php echo $val['id']; ?>" value="<?php echo $val['id']; ?>"><?php echo $val['title']; ?></option>                                               
                                                <?php } ?>
                                            </select>

                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" id="submit" class="btn btn-primary btn-typ3">
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    <a href="package-category-list.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!------ success alert message pop up ----->
             <!--Start -->
             <div class="modal fade" id="addsuccesspop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Category Added</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backuserpage()" ></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->

            <!-- Footer Start -->
            <?php 
            include_once "includes/footer.php";             
?>

<script>
    $(document).ready(function(){

        $("#submit").click(function () {
            
            valid = true;
            var regex = /^[a-zA-Z]+$/;
           var title      = $('#name').val();
            $(".errortext").remove();
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Title cannot be blank.</span>')	       
            valid = false;
            }            
           else if(!regex.test(title)){
                $('#name').after('<span class="errortext" style="color:red">Title cannot  include Numbers or Special Characters</span>')	       
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
    function backuserpage() {
     window.location = "package-category-list.php";
    }
</script>