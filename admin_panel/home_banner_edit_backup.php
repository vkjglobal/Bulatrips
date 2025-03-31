<?php
include_once "includes/class.contents.php";
if (isset($_POST['firstEdit']) && ($_POST['firstEdit'] != "")) {

    $firstEdit         =    trim($_POST['firstEdit']);
    if (isset($_POST['secondEdit'])) {
        $secondEdit         =   trim($_POST['secondEdit']);
    }
    if (isset($_POST['bannerImageEdit']) && isset($_POST['bannerImageEdit']) != "") {
        $bannerImageEdit    =   trim($_POST['bannerImageEdit']);
    }
    if (isset($_POST['homeId']) && isset($_POST['homeId']) != "") {
        $homeId    =   trim($_POST['homeId']);
    }
    if (isset($_POST['old_image']) && isset($_POST['old_image']) != "") {
        $old_image    =   trim($_POST['old_image']);
    }
    
    $objContent     =   new Contents();

    $imageErr   = "";
    if (!empty($_FILES['banner-image']['name'])) {
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
        $allowedMimeTypes = ['image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $_FILES['banner-image']['type'];

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $imageErr = "Invalid MIME type";
            //return false;
        }
        //print_r($imageErr);exit;
        //<span class="errortext" style="color:red">Title cannot be blank.</span>
        if ($imageErr   ==  "") {
            move_uploaded_file($_FILES["banner-image"]["tmp_name"], "../images/homepage_banner/" . $image);
        } else {
            //  echo $fileMimeType;
            // echo $imageErr;
            echo "err1";
            exit;

            // $('#imageerror').after('<span class="errortext" style="color:red">Image cannot be blank.</span>')
        }
    } // end of not empty image file
    else {
        $image         =    $old_image;                 //if no image newly posted use existing img

    }

    //============================


echo $firstEdit;
echo $secondEdit;
echo $image;
echo $homeId;
die;
    $upateArr =   $objContent->updateBannerHome($firstEdit, $secondEdit, $image, $homeId);

    //    if(($upateArr === true) && ($imageErr == ""))
    if (($upateArr === true)) {
        echo "success";
        exit;
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
    } else {
        echo "error in updation";
    }


    //==========================================



}
