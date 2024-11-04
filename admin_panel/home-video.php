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
 //======
  if((isset($_GET['id']))  && (isset($_GET['sf'] )))
{
      if($_GET['sf'] === 'delete'){
        
        $pid =  $_GET['id'] ;      
     // echo $pid;exit;
   $homedel=   $objContent->DelHomeVideo($pid);

        if($homedel){
        $videoFilePath = "uploads/Home/video/".$_GET['video'];
                // Check if the video file exists before attempting to delete it
        if (file_exists($videoFilePath)) {
              // Delete the video file from the server's file system
              unlink($videoFilePath);
        }
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
 //=======

if(isset($_POST['submit']))
        {
            
            $title    =   trim($_POST['first']);
            $description   =   trim($_POST['desc']);
          //================================================
         // Handle file upload
    if (!empty($_FILES['banner-image']['name'])) {

    $videoErr   =   "";
        $targetDir = "uploads/Home/video/";
// Replace with your desired upload directory
        $targetFile = $targetDir . basename($_FILES["banner-image"]["name"]);
        $uploadOk = 1;
        $videoFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
 
      //echo  $videoFileType;
  // echo $_FILES["banner-image"]["size"]  ;exit;
        // Check if file is a video   
    //  if(($videoFileType != "mp4")  || ($videoFileType != "avi")  || ($videoFileType != "mov")  ||  ($videoFileType != "wmv")){
    if ($videoFileType !== "mp4" && $videoFileType !== "avi" && $videoFileType !== "mov" && $videoFileType !== "wmv") {
        
         $videoErr   = "Only MP4, AVI, MOV, and WMV video files are allowed.";
                         $uploadOk = 0;
        }

        // Check file size (Limit: 100 MB)
        if ($_FILES["banner-image"]["size"] > 40* 1024 * 1024) {
             $videoErr   = "Sorry, your video file is too large. Max file size is 40MB.";
            $uploadOk = 0;
        }

        // Generate a unique name to prevent overwriting existing files with the same name
        $uniqueFileName = uniqid() . '_' . $_FILES["banner-image"]["name"];
        $targetFile = $targetDir . $uniqueFileName;

        // Check if the file already exists (unlikely due to the unique filename, but just in case)
        if (file_exists($targetFile)) {
             $videoErr   = "Sorry, a file with that name already exists.";
            $uploadOk = 0;
        }

        // If everything is ok, try to upload the file
        if(empty($videoErr)) {
            if ($uploadOk == 1 && move_uploaded_file($_FILES["banner-image"]["tmp_name"], $targetFile)) {
               $insArr   =   $objContent->insHomeVideo($title,$description,$uniqueFileName);
               //var_dump($insArr);
                 // print_r($imageErr);
                  if(!empty ($insArr))
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

              //echo "The video file " . $uniqueFileName . " has been uploaded.";
                // Now $targetFile contains the path to the uploaded video file.
                // You can store this path in the database along with other video details.
            }
        }

        

    } // end of !empty video

             

    // Perform database insert or update based on whether 'id' is provided


          //*********************************************









         
 }//==========add button ends===         
                 
     else{ //listing starts

                 $Homebanners       =       $objContent->getListHomeVideo();
     //print_r( $Homebanners);

     }    
     

            //========================
            
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">                       
                            
                            <div class="row" style="display: flex;">
                                <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add/edit Video</strong>
                                <form class="" method="POST" action="" enctype="multipart/form-data">
                                <div class="table-responsive">
                                    <table class="table text-start align-middle table-bordered table-hover mb-4">
                                        <thead>
                                            <tr>
                                                <th class="w-25">
                                                    video
                                                </th>
                                                <th>Title</th>
                                                <th>
                                                    Description
                                                </th>                                               

                                                <th class="w-25">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($Homebanners as $k=>$val){

                                            $id  =   $val['id'];
                                             $title    =   $val['title'];
                                              $description   =   $val['description'];
                                               $videoFileURL =     "uploads/Home/video/".$val['video'];
                                               $modalID =   "editBanner".$id;
                                            ?>
                                            <tr>
                                                <td class="video-cell">
                                                <div class="video-thumbnail">
                                                    <video controls>
                                                        <source src="<?php echo $videoFileURL; ?>" type="video/mp4">
                                                        <!-- You can add additional source elements for other video formats -->
                                                        <!-- For example, if you have a WebM version of the video: -->
                                                        <!-- <source src="path/to/your_video.webm" type="video/webm"> -->
                                                        <!-- If you have an Ogg version of the video: -->
                                                        <!-- <source src="path/to/your_video.ogg" type="video/ogg"> -->
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                                </td>
                                                 <td><div class="banner-text"> <h2><?php echo $title; ?></h2></td>

                                                <td>
                                                    <div class="banner-text">
                                                        <h2><?php echo $description; ?></h2>
                                                    </div>  
                                                </td>
                                                <td>
                                                <div class="d-flex action">
                                                
                                                    <button class="btn text-secondary edit" type="button" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>"><i class="fa fa-pen">Edit</i></button>

                                                    <a class="btn text-secondary delete" onclick="return confirm('are you sure you want to delete?')" href="home-video.php?sf=delete&amp;id=<?php echo $id; ?>&amp;video=<?php echo $val['video']; ?>">
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
                                            <label for="">Title</label>
                                            <input type="text" class="form-control" id="first" name="first">
                                        </div>
                                          <div class="col-md-6">
                                            <label for="">Description</label>
                                            <input type="text" class="form-control" id="desc" name="desc">
                                        </div>
                                       
                                        <div class="col-md-6">
                                            <label for="">Upload Video </label>
                                            <label class="uploadFile form-control" id="imageerror">
                                                <span class="filename"></span>
                                                <input type="file" class="inputfile" name="banner-image" id="banner-image">
                                            </label>
                                             <?php if(!empty($videoErr)){ ?>
                                            <sapan class="errortext" style="color:red"><?php echo $videoErr; ?></span>
                                            <?php } ?>
                                        </div>
                                        <div class="col-12 d-flex">
                                            <input type="submit" name="submit" value="Add" id="submit" class="btn btn-primary btn-typ3" >
                                            <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                            <a href="home-video.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
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
                  $old_image        =   $val['video'];
                 $modalID =   "editBanner".$id;
                 $title    =   $val['title'];
                   $description   =   $val['description'];
                
            ?>
            <div class="modal fade" id="<?php echo $modalID; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="editBanner" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Edit Video</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="row g-2" enctype="multipart/form-data" id="<?php echo $id; ?>"  onsubmit="myFunction(event,<?php echo $id ?>,'<?php echo $old_image ?>')">
                                <div class="col-12">
                                    <label for="">First Title</label>
                                    <input type="text" class="form-control"  id="firstEdit" name="firstEdit" value="<?php echo $title ?>">
                                     <span class="errortext" id="firstEditError" style="color:red"></span>
                                </div>
                                <div class="col-12">
                                    <label for="">Second Title</label>
                                    <input type="text" class="form-control" id="secondEdit" name="secondEdit" value=" <?php echo $description;?>">
                                     <span class="errortext" id="secondEditError" style="color:red"></span>

                                </div>
                                <div class="col-12">
                                    <label for="bannerImageEdit">Upload Video</label>
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
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">The video file <?php echo  $uniqueFileName ; ?>  has been uploaded</h1>
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
            $('#first').after('<span class="errortext" style="color:red"> Title cannot be blank.</span>')	       
            valid = false;
            }
             if($('#desc').val() == '') {
            $('#desc').after('<span class="errortext" style="color:red">Description cannot be blank.</span>')	       
            valid = false;
            }




            if($('#banner-image').val() == '') {
            $('#imageerror').after('<span class="errortext" style="color:red">Video cannot be blank.</span>')	       
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
     window.location= "home-video.php";

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
                    secondEditError.textContent = 'Enter Description';
                        return false;
            }  
             else if (bannerImageEdit.trim() === '') {
                        //alert("kkkkk");
                   bannerImageEditError.textContent = 'Enter Video';
                        return false;
                    }  
      //    alert(filename);                  
// return false;
    
        $.ajax({
                url: 'home_video_edit.php', // Replace with your form processing script
                type: 'POST',
             //data: { firstEdit: firstEdit, secondEdit: secondEdit,bannerImageEdit: filename ,homeId: homeId,oldImage: oldImage },
              data: formData,
              dataType: 'text',           // what to expect back from the PHP script, if anything
                cache : false,
                contentType: false,
                processData: false,
                success: function(response) {
     // alert(response);
     // return false;
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
                        bannerImageEditError.textContent = 'Error in Video Type ';
                         
                          // Refresh the form on modal close
                      $(modalId).on('hidden.bs.modal', function() {
                        location.reload();
                      });
                    }
                    else{
                         bannerImageEditError.textContent = 'eroor in updation';
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error); // Log any AJAX errors
                }
            
        });
}

</script>
<style>

.video-cell {
  width: 25%; /* Adjust this value to control the width of each video thumbnail */
  padding: 10px;
}

.video-thumbnail {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 aspect ratio (height / width) for responsive layout */
}

video {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
}
</style>