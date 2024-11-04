<?php 
 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
} 

include_once "includes/header.php";   
include_once "includes/class.general.php";
$objGeneral =	new General();
//$datas	=   $objGeneral->getAirportLocs();
 $imageErr   =   "";

  //======
  if((isset($_GET['uid']))  && (isset($_GET['action'] )))
{
      if($_GET['action'] === 'delete'){
        
        $pid =  $_GET['uid'] ;      
     // echo $pid;exit;
   $homedel=   $objGeneral->DelAirlinees($pid);
        if($homedel){
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
          
 // print_r($_POST);exit;
        $airline_code   =   trim($_POST['name']);
        $airlinename   =   trim($_POST['airportname']);
    //  $image          =$_POST['member-image'];
        //============================
                   // Check if a file was uploaded
            if(!empty($_FILES['member-image']['name'])){
                   $image    = $_FILES['member-image']; 
                    $image    =   $image['name'];
  
                            $allowedTypes = array(
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_GIF
                );

    if ($_FILES['member-image']['error'] === UPLOAD_ERR_OK) {
        $imageType = exif_imagetype($_FILES['member-image']['tmp_name']);

        if (!in_array($imageType, $allowedTypes)) {
            // Invalid image file type
             $imageErr   =  "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
              //return false;
        } 
    }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $_FILES['member-image']['type'];
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $imageErr = "Invalid MIME type";
            //return false;
        }
     // print_r($imageErr);
      //<span class="errortext" style="color:red">Title cannot be blank.</span>
        if($imageErr   ==  ""){
                        move_uploaded_file($_FILES["member-image"]["tmp_name"],"uploads/Airline/".$image);
                        //===
                         $insArr   =   $objGeneral->insAirline($airline_code,$airlinename,$image);
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
                
         
 }//


        //=======================

     

        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add Airline</strong>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="">Airline Code</label>
                                            <input type="text" class="form-control" name="name" id="name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="">Airline Name</label>
                                            <input type="text" class="form-control" name="airportname" id="airportname">
                                        </div>
                                       
                                         <div class="col-md-6 mb-3">
                                            <label for="">Upload Airline Image</label>
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
                                    <a href="airline-list.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

             <!--Start -->
             <div class="modal fade" id="addsuccesspop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                aria-labelledby="addMore" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Airline Added</h1>
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
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Airline Code cannot be blank.</span>')	       
            valid = false;
            }
              if($('#airportname').val() == '') {
            $('#airportname').after('<span class="errortext" style="color:red">Airline name cannot be blank.</span>')	       
            valid = false;
            }                        
            
            if( !valid ){       
            return valid;
            }	
        });
    });
     function backuserpage() {
     window.location = "airline-list.php";
    }
</script>