<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin dashboard page
   Programmer	::> Soumya
   Date		::> 30-06-2023
   DESCRIPTION::::>>>>
   This is  code used for agent  details page
*****************************************************************************/
//echo "<pre/>";print_r($_POST);exit;
if(isset($_POST['firstEdit']) && ($_POST['firstEdit']!="")){
     
         $firstEdit         =    trim($_POST['firstEdit']);
         if(isset($_POST['secondEdit'])){
            $secondEdit         =   trim($_POST['secondEdit']);
         }
          if(isset($_POST['bannerImageEdit']) && isset($_POST['bannerImageEdit']) !=""){
              $bannerImageEdit    =   trim($_POST['bannerImageEdit']);
          }
          if(isset($_POST['homeId']) && isset($_POST['homeId']) !=""){
              $homeId    =   trim($_POST['homeId']);
          }
            if(isset($_POST['old_image']) && isset($_POST['old_image']) !=""){
              $old_image    =   trim($_POST['old_image']);
          }
          include_once "includes/class.contents.php";
       $objContent     =   new Contents();
     //echo  $old_image ;exit;
     // echo "UUUUUUUUUUUUU". $homeId;exit;

       // $bannerImageEdit = $_FILES['banner-image']['name'];
   //   $tempFilePath = $_FILES['banner-image']['tmp_name'];
         //cho $bannerImageEdit;
 //  echo $tempFilePath;
//exit;

    //========================================
    //============================
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
            $uploadOk = 1;
            move_uploaded_file($_FILES["banner-image"]["tmp_name"], $targetFile);
        }
             else{
            echo "err1";exit;
             }
             
    } // end of !empty video
    else{
             $uniqueFileName         =    $old_image  ;                 //if no image newly posted use existing img

        }
 
                          //============================
                  
                 
                  
                 $upateArr =   $objContent->updateVideoHome($firstEdit,$secondEdit,$uniqueFileName,$homeId);   
                   
            //    if(($upateArr === true) && ($imageErr == ""))
             if(($upateArr === true))
                { 
                    echo "success";exit;
                  /* echo "<script>";
                echo "document.addEventListener('DOMContentLoaded', function() {";
                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                echo "    if (addsuccesspop) {";
                echo "        addsuccesspop.classList.add('show');";
                echo "        addsuccesspop.style.display = 'block';";
                echo "    }";
                echo "});";
                echo "</script>"; */
               // echo "update success ";
                }
                else{
                    echo "error in updation";
                }
 

    //==========================================


   
 }
 
?>
