<?php 
// Turn off error reporting
//ror_reporting(0);
//ini_set('display_errors', 0);

 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
} include_once "includes/header.php";
  include_once "includes/class.contents.php";
$objContent     =   new Contents();
$id =   1;
$content        =   $objContent->getListAboutUs($id);
  $image   =    $content[0]['imagefile'];
  
  // Check if the  image exists
          $profileImageURL =     "uploads/about/".$image;
          $imageErr   =   "";
if(isset($_POST['submit']))
        {         
           
            $title=$_POST['name'];
            $description   =$_POST['editor'];
            $description    =  htmlentities($description);            
            $image=$_POST['p-image'];
             
        //print_r($_POST);
         
                   //============================
                   // Check if a file was uploaded
if(!empty($_FILES['p-image']['name'])){
       $image    = $_FILES['p-image']; 
        $image    =   $image['name'];
  
                $allowedTypes = array(
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF
    );

    if ($_FILES['p-image']['error'] === UPLOAD_ERR_OK) {
        $imageType = exif_imagetype($_FILES['p-image']['tmp_name']);

        if (!in_array($imageType, $allowedTypes)) {
            // Invalid image file type
             $imageErr   =  "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
              //return false;
        } 
    }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $_FILES['p-image']['type'];
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $imageErr = "Invalid MIME type";
            //return false;
        }
        if($imageErr   ==  ""){
                        move_uploaded_file($_FILES["p-image"]["tmp_name"],"uploads/about/".$image);
                   }
     }
      else
        {
             $image   =    $content[0]['imagefile']; //if no image newly posted use existing img
        }                  //============================
                  
                   
                 
                  
                    $upateArr =   $objContent->updateAbout($title,$image,$description,$id);
                   
                  if(($upateArr === true) && ($imageErr == ""))
                { 
                     echo "<script>";
                echo "document.addEventListener('DOMContentLoaded', function() {";
                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                echo "    if (addsuccesspop) {";
                echo "        addsuccesspop.classList.add('show');";
                echo "        addsuccesspop.style.display = 'block';";
                echo "    }";
                echo "});";
                echo "</script>";
               // echo "update success ";
                }
                else{
                    echo "error in updation";
                }
 
  }
      
                    else{
            $title          =   $content[0]['title'];
            $description    =   $content[0]['content'];        
           //imgnewfile          =     $content[0]['imagefile'];       //print_r($content);
                    }
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Update About Us content</strong>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="">title</label>
                                            <input type="text" class="form-control" value="<?php  echo $title; ?>" name="name" id="name">
                                        </div>
                                
                                        <div class="col-md-12 mb-3">
                                            <label for="">Content</label>
                                            
                                            
                                            <textarea id="editor" name="editor" >
                                                <?php  echo html_entity_decode($description); ?>

                                            </textarea>
                                        </div>
                                        <div class="col-12">
                                            <div class="row align-items-center">
                                                <div class="col-md-6 mb-3">
                                                    <img src="<?php  echo  $profileImageURL;?>" border="0" width="200" height="200" id="image"/>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="">Upload Image</label>
                                                    <label class="uploadFile form-control">
                                                        <span class="filename"></span>
                                                        <input type="file" class="inputfile" name="p-image" id="p-image">
                                                    </label>
                                                     <?php if(!empty($imageErr)){ ?>
                                            <sapan class="errortext" style="color:red"><?php echo $imageErr; ?></span>
                                            <?php } ?>
                                                </div>
                                            </div>
                                        </div>

                                    </div>   
                                    
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" class="btn btn-primary" id="submit">
                                   
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Account Settings End -->
             <!--Start -->
             <div class="modal fade" id="addsuccesspop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Successfully Updated</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backuserpage()" ></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->


<?php
include "includes/footer.php";       

?>

<script>
     $(document).ready(function(){        
        $("#submit").click(function () {
            
            valid = true;
                       var regex = /^[a-zA-Z]+$/;
            $(".errortext").remove();
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Title cannot be blank.</span>')	       
            valid = false;
            }
            else if(!regex.test($('#name').val())){
                $('#name').after('<span class="errortext" style="color:red">Title cannot  include Numbers or Special Characters</span>')	       
            valid = false;
            }
         /*else if(($('#image').val() == '') && ($('#p-image').val() == '')){             
          //lert("KKKK");
            $('#image').after('<span class="errortext" style="color:red">Image cannot be blank.</span>')	       
            valid = false;
            }
        */
           
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
     window.location= "about-us.php";

    }
</script> 