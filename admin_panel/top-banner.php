<?php 
error_reporting(1);
ini_set('display_errors', 1);
 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
include_once "includes/header.php";
  include_once "includes/class.contents.php";

  $objContent = new Contents();
  $imageErr = "";
  
  // ===== Delete Logic =====
  if (isset($_GET['id'], $_GET['sf']) && $_GET['sf'] === 'delete') {
      $pid = $_GET['id'];
      $homedel = $objContent->DelHomeImages($pid);
  }
  
  // ===== Form Submission Logic =====
  if (isset($_POST['submit'])) {
      $first_title = trim($_POST['first']);
      $second_title = trim($_POST['second']);
  
      // File upload handling
      if (!empty($_FILES['banner-image']['name'])) {
          $imageName = $_FILES['banner-image']['name'];
          $imageTmpName = $_FILES['banner-image']['tmp_name'];
          $imageType = $_FILES['banner-image']['type'];
          $imageError = $_FILES['banner-image']['error'];
          $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
  
          $newImageName = pathinfo($imageName, PATHINFO_FILENAME) . '_' . time() . '.' . $imageExtension;
          $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
          $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];
  
          if ($imageError === UPLOAD_ERR_OK) {
              $detectedType = exif_imagetype($imageTmpName);
  
              if (!in_array($detectedType, $allowedTypes)) {
                  $imageErr = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
              }
  
              if (!in_array($imageType, $allowedMimeTypes)) {
                  $imageErr = "Invalid MIME type.";
              }
  
              if (empty($imageErr)) {
                $uploadDir = "../images/homepage_banner/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $uploadPath = $uploadDir . $newImageName;
                if (move_uploaded_file($imageTmpName, $uploadPath)) {
                      $insert = $objContent->insHomeBanner($first_title, $second_title, $newImageName);
                  } else {
                      echo "Failed to upload image.";
                  }
              } else {
                  echo '<span class="errortext" style="color:red;">' . $imageErr . '</span>';
              }
          } else {
              echo "Upload error occurred.";
          }
      } else {
          echo '<span class="errortext" style="color:red;">Please select an image to upload.</span>';
      }
  } 


  $Homebanners = $objContent->getListHomeBanner();
  
            //========================
            
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">                       
                            
                            <div class="row" style="display: flex;">
                                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add/edit Banner</strong>
                                <form class="" method="POST" action="" enctype="multipart/form-data">
                                <div class="table-responsive">
                                    <table class="table text-start align-middle table-bordered table-hover mb-4">
                                        <thead>
                                            <tr>
                                                <th class="w-25">
                                                    Image
                                                </th>
                                                <th class="w-50">
                                                    Text
                                                </th>
                                                <th class="w-25">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($Homebanners as $k=>$val){

                                            $id  =   $val['id'];
                                             $first_title    =   $val['first_title'];
                                              $second_title   =   $val['second_title'];
                                               $profileImageURL =     "../images/homepage_banner/".$val['image'];
                                               $modalID =   "editBanner".$id;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="img-wrp">
                                                        <img src="<?php echo $profileImageURL; ?>" alt="">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="banner-text">
                                                        <h2><?php echo $first_title; ?></h2>
                                                        <h3><?php echo $second_title; ?></h3>
                                                    </div>  
                                                </td>
                                                <td>
                                                <div class="d-flex action">
                                                
                                                    <a href="edit_banner?id=<?php echo $id; ?>" class="btn text-secondary edit" type="button"><i class="fa fa-pen">Edit</i></button>

                                                    <a class="btn text-secondary delete" onclick="return confirm('are you sure you want to delete?')" href="top-banner.php?sf=delete&amp;id=<?php echo $id; ?>">
                                                        <i class="fa fa-trash">Delete</i>
                                                    </a>
                                                
                                                </div>
                                                </td>
                                            </tr>
                                           <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                </form>
                                 <form class="" method="POST" action="" enctype="multipart/form-data">
                                <div class="col-12">
                                    <div class="row g-3"> 
                                   
                                        <div class="col-md-6">
                                            <label for="">First Title</label>
                                            <input type="text" class="form-control" id="first" name="first">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Second Title</label>
                                            <input type="text" class="form-control" id="second" name="second">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="">Upload News Image</label>
                                            <label class="uploadFile form-control" id="imageerror">
                                                <span class="filename"></span>
                                                <input type="file" class="inputfile" name="banner-image" id="banner-image">
                                            </label>
                                             <?php if(!empty($imageErr)){ ?>
                                            <sapan class="errortext" style="color:red"><?php echo $imageErr; ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="col-12 d-flex">
                                            <input type="submit" name="submit" value="Add" id="submit" class="btn btn-primary btn-typ3" >
                                            <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                            <a href="home.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
                                        </div>
                                        </form>
                                    </div>
                                    
                                </div>
                                </form>
                            </div>
                        
                    </div>
                </div>
            </div>

            <!-- Start Edit Banner -->
            <?php foreach($Homebanners as $k=>$val){ 
                  $id  =   $val['id'];
                  $old_image        =   $val['image'];
                 $modalID =   "editBanner".$id;
            ?>
            <div class="modal fade" id="<?php echo $modalID; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="editBanner" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Edit Banner</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row g-2" enctype="multipart/form-data" id="<?php echo $id; ?>"  onsubmit="myFunction(event,<?php echo $id ?>,'<?php echo $old_image ?>')">
                                <div class="col-12">
                                    <label for="">First Title</label>
                                    <input type="text" class="form-control"  id="firstEdit" name="firstEdit">
                                     <span class="errortext" id="firstEditError" style="color:red"></span>
                                </div>
                                <div class="col-12">
                                    <label for="">Second Title</label>
                                    <input type="text" class="form-control" id="secondEdit" name="secondEdit">
                                     <span class="errortext" id="secondEditError" style="color:red"></span>

                                </div>
                                <div class="col-12">
                                    <label for="bannerImageEdit">Upload News Image</label>
                                    <label class="uploadFile form-control" id="imageerror2">
                                        <span class="filename"></span>
                                        <input type="file" class="inputfile" name="banner-image" id="bannerImageEdit">
                                          <span class="errortext" id="bannerImageEditError" style="color:red"></span>
                                    </label>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-secondary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <!-- End Edit Banner -->

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

            <!-- Footer Start -->
            <?php 
            include "includes/footer.php";
             
?>

<script>
    $(document).ready(function(){

        $("#submit").click(function () {
            
            valid = true;
           
            $(".errortext").remove();
            if($('#first').val() == '') {
            $('#first').after('<span class="errortext" style="color:red">First Title cannot be blank.</span>')	       
            valid = false;
            }
             if($('#second').val() == '') {
            $('#second').after('<span class="errortext" style="color:red">Second Title cannot be blank.</span>')	       
            valid = false;
            }




            if($('#banner-image').val() == '') {
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
     window.location= "top-banner.php";

    }
    //update function
     function myFunction(event, formId, oldImage){

       //alert("yyyyy");return false;
                  event.preventDefault(); // Prevent form submission
                  var homeId = parseInt(formId);
                  //=====
                 var form = document.getElementById(formId);

              // Create a new FormData object
              var formData = new FormData(form);
                formData.append('old_image', oldImage);

                var file_data = $('#banner-image').prop('files')[0];
                 formData.append('banner-image', file_data);
                  formData.append('homeId', homeId);


                  //=====

         // alert(homeId); return false;
            var firstEdit = document.getElementById(formId).elements["firstEdit"].value;
            var firstEditError = document.getElementById(formId).querySelector('#firstEditError');


            var secondEdit = document.getElementById(formId).elements["secondEdit"].value;
            var secondEditError = document.getElementById(formId).querySelector('#secondEditError');


            var bannerImageEdit = document.getElementById(formId).elements["bannerImageEdit"].value;
            var bannerImageEditError = document.getElementById(formId).querySelector('#bannerImageEditError');

            var filename = bannerImageEdit.replace(/^.*[\\\/]/, '');
          

            //====================
    // Get the file input element
/*var fileInput = document.getElementById('bannerImageEdit');

  // Check if a file was selected
  if (fileInput.files.length > 0) {
    // Append the file to the formData object
    formData.append('bannerImageEdit', fileInput.files[0]);
  }
  */
            //=======================

             var modalId    =   "#editBanner"+homeId;
// var addMoreButton = document.querySelector('[data-bs-target="'+modalId+'"]');


     // Validate form data
           
            if (firstEdit.trim() === '') {
                //alert("kkkkk");
            firstEditError.textContent = 'Enter First Title';
                return false;
            }
            else if (secondEdit.trim() === '') {
                        //alert("kkkkk");
                    secondEditError.textContent = 'Enter second Title';
                        return false;
            }  
             else if (bannerImageEdit.trim() === '') {
                        //alert("kkkkk");
                   bannerImageEditError.textContent = 'Enter Image';
                        return false;
                    }  
      //    alert(filename);                  
// return false;
    
        $.ajax({
                url: 'home_banner_edit', // Replace with your form processing script
                type: 'POST',
             //data: { firstEdit: firstEdit, secondEdit: secondEdit,bannerImageEdit: filename ,homeId: homeId,oldImage: oldImage },
              data: formData,
              dataType: 'text',           // what to expect back from the PHP script, if anything
                cache : false,
                contentType: false,
                processData: false,
                success: function(response) {
        alert(response);
        //return false;
                    // Handle the response here
                    if (response === 'success') {
                        //alert('kkkkk');
                          bannerImageEditError.textContent = 'Updated successfully';
                         
                          // Refresh the form on modal close
                      $(modalId).on('hidden.bs.modal', function() {
                        location.reload();
                      });

                         
                    } else if (response === 'err1') {
                    //  alert('hhh');
                        bannerImageEditError.textContent = 'Error in Image Type ';
                         
                          // Refresh the form on modal close
                      $(modalId).on('hidden.bs.modal', function() {
                        location.reload();
                      });
                    }
                    else{
                         bannerImageEditError.textContent = 'Error in updation';
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            
        });
}

</script>