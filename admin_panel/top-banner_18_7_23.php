<?php 
 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
include_once "includes/header.php";
  include_once "includes/class.contents.php";
$objContent     =   new Contents();

 $imageErr   =   "";
if(isset($_POST['submit']))
        {
            // echo "<script>alert('test');</script>";
       //   echo "hlll";exit;
            $first_title    =   trim($_POST['first']);
            $second_title   =   trim($_POST['second']);
          //$banner_image   =   $_POST['banner-image'];

             //============================
                   // Check if a file was uploaded
            if(!empty($_FILES['banner-image']['name'])){
                   $image    = $_FILES['banner-image']; 
                    $image    =   $image['name'];
  
                            $allowedTypes = array(
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_GIF
                );

    if ($_FILES['banner-image']['error'] === UPLOAD_ERR_OK) {
        $imageType = exif_imagetype($_FILES['banner-image']['tmp_name']);

        if (!in_array($imageType, $allowedTypes)) {
            // Invalid image file type
             $imageErr   =  "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
              //return false;
        } 
    }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $_FILES['banner-image']['type'];
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $imageErr = "Invalid MIME type";
            //return false;
        }
     // print_r($imageErr);
      //<span class="errortext" style="color:red">Title cannot be blank.</span>
        if($imageErr   ==  ""){
                        move_uploaded_file($_FILES["banner-image"]["tmp_name"],"uploads/Home/banner/".$image);
                        //===
                         $insArr   =   $objContent->insHomeBanner($first_title,$second_title,$image);
               //var_dump($insArr);
                 // print_r($imageErr);
                  if((!empty ($insArr))&& ($imageErr == ""))
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
                    echo "error in insertion";
                }

                        //==
       }
       else{
                       echo "<script>";
                     echo  '<sapan class="errortext" style="color:red">'.$imageErr.'</span>';
                        echo "</script>";

                      //cho "kkk";
                   //  echo '<script><span class="errortext" style="color:red">Title cannot be blank.</span>return false;</script>';
            }
                   
     }           
                
         
 }//==========add button ends===
                 
     else{ //listing starts

                 $Homebanners       =       $objContent->getListHomeBanner();
       //print_r( $Homebanners);

     }    


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
                                               $profileImageURL =     "uploads/Home/banner/".$val['image'];
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
                                                
                                                    <button class="btn text-secondary edit" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>"><i class="fa fa-pen">Edit</i></button>

                                                    <a class="btn text-secondary delete" onclick="return confirm('are you sure you want to delete?')" href="members-list.php?sf=delete&amp;id=1">
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
                            <form action="" class="row g-2" enctype="multipart/form-data" id="<?php echo $id; ?>"  onsubmit="myFunction(event,<?php echo $id;?>,<?php echo $old_image;?>)">
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
     function myFunction(event,formId,oldImage){

           alert("jjjjjjj");return false;
                  event.preventDefault(); // Prevent form submission
                  var homeId = parseInt(formId);
                
                  //=====
                              var form = document.getElementById(formId);

              // Create a new FormData object
              var formData = new FormData(form);
                formData.append('old_image', oldImage);

                 
                  //=====

         // alert(homeId); return false;
            var firstEdit = document.getElementById(formId).elements["firstEdit"].value;
            var firstEditError = document.getElementById(formId).querySelector('#firstEditError');


            var secondEdit = document.getElementById(formId).elements["secondEdit"].value;
            var secondEditError = document.getElementById(formId).querySelector('#secondEditError');


            var bannerImageEdit = document.getElementById(formId).elements["bannerImageEdit"].value;
            var bannerImageEditError = document.getElementById(formId).querySelector('#bannerImageEditError');

            var filename = bannerImageEdit.replace(/^.*[\\\/]/, '');

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
                url: 'home_banner_edit.php', // Replace with your form processing script
                type: 'POST',
               //data: { firstEdit: firstEdit, secondEdit: secondEdit,bannerImageEdit: filename ,homeId: homeId,oldImage: oldImage },
                //data: $(this).serialize()+ '&amount=' +amountprev,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
              alert(response);return false;
                    // Handle the response here
                    if (response === 'success') {
                        alert('kkkkk');
                          messageError.textContent = 'Successfully sent';
                         
                          // Refresh the form on modal close
                      $(modalId).on('hidden.bs.modal', function() {
                        location.reload();
                      });

                         
                    } else {
                        alert('hhh');
                       messageError.textContent = 'Failed to send';
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            
        });
}

</script>