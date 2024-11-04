<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin edit profile page
   Programmer	::> Soumya
   Date		::> 28-06-2023
   DESCRIPTION::::>>>>
   This  code used for admin profile management
*****************************************************************************/
 session_start();
 //print_r($_SESSION);exit;
if(!isset($_SESSION['adminid'])){
   // echo "pppp";exit;
    header('Location:index.php');exit;
}

       include "includes/class.member.php";
        $objMember		= 	new Member();
        $id=$_SESSION['adminid'];
        $adminProfile	= $objMember->getAdminProfile($id);
         //print_r($adminProfile);exit;
         $image   =   $adminProfile[0]['image'];
                     // Check if the profile image exists
          $profileImageURL =     "uploads/profile/".$image;
               
            if ((empty($image)) || (!file_exists($profileImageURL))) {
                $dummyImageURL = "uploads/profile/test1.jpg"; // URL of the dummy image
                $profileImageURL = $dummyImageURL;
            }    
            
            //===============================
        if(isset($_POST['submit'])){  //collecting new form data
              $haserror    =   true;
//print_r($_POST);          
        $fname    = $objMember->sanitizeInput($_POST['firstName']);// print_r($fname);
        $lname    = $objMember->sanitizeInput($_POST['lastName']);
        $address  = $objMember->sanitizeInput($_POST['address']);
        $email    = $objMember->sanitizeInput($_POST['email']);
        $phone    = $objMember->sanitizeInput($_POST['phone']);
        
        //print_r($image);
        //==============================            
            $fname_res  = $objMember->validateInputStrings($fname);
            $lname_res  = $objMember->validateInputStrings($lname);
            $address_res  = $objMember->validateInputStrings($address);
            $email_res  = $objMember->validateInputStrings($email);
            $phone_res  = $objMember->validateInputStrings($phone);
            $email1_res  = $objMember->validateInputEmail($email);
            $email2_res  = $objMember->validateInputEmailExist($email,$id);//check whether same email exist for another user
            $phone_res1  = $objMember->validatePhoneNumber($phone) ;
            //================
            $imageErr   =   "";
            if(count($email2_res)>0){
                //email id exist for another user
                $email2_res =   false;
            }
            else{
                $email2_res =   true;
            }
                //  $image_res_size    = $objMember->validateImage($_FILES['p-image']['size'],$_FILES['p-image']['name'],$_FILES['p-image']['type']);
              //  echo "PPPPPPPPPPPPPP";
   if(!empty($_FILES['p-image']['name'])){
       $image    = $_FILES['p-image']; 
        $image    =   $image['name'];
                $maxFileSize = 1000000; // 2MB (example maximum file size)
            if ($_FILES['p-image']['size'] > $maxFileSize) {
            //echo "Invalid file size";
            $imageErr   =   "Invalid file size";
            $haserror    =   false;
            } 
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
              $haserror    =   false;
        } 
    }
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Example allowed MIME types
        $fileMimeType = $_FILES['p-image']['type'];
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $imageErr = "Invalid MIME type";
            $haserror    =   false;
        }
     }
     if($imageErr   ==  ""){
                        move_uploaded_file($_FILES["p-image"]["tmp_name"],"uploads/profile/".$image);
                         $haserror    =   true;
     }

//============
//var_dump($email2_res);
         
           if((!$fname_res)|| (!$lname_res)  || (!$email_res) || (!$phone_res) || (!$email1_res) || (!$phone_res1) || (!$email2_res)){
               $haserror    =   false;
           }
         // echo "******************************"; var_dump($haserror);
           if( $haserror){ 
               // no error messages and update into db
                $upateArr   =   false;//var used to check success status after db updation
               $upateArr    =   $objMember->updateAdminProfile($id,$email,$fname,$lname,$phone,$image,$address);
                if($upateArr === true)
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
               
           }       
        }
        
        else{ //display db data on form
            
            $fname  =   $adminProfile[0]['first_name'];
            $lname  =   $adminProfile[0]['last_name'];
            $phone  =   $adminProfile[0]['phone'];
            $email  =   $adminProfile[0]['email'];         
            $address  = $adminProfile[0]['address'];
            //========no need of validation since just show data from db ,so if needed make these variables to numm to avoid warnng error of undefined var on page loading===========
                        $fname_res  = $objMember->validateInputStrings($fname);
            $lname_res  = $objMember->validateInputStrings($lname);
            $address_res  = $objMember->validateInputStrings($address);
            $email_res  = $objMember->validateInputStrings($email);
            $phone_res  = $objMember->validateInputStrings($phone);
            $email1_res  = $objMember->validateInputEmail($email);
            $phone_res1  = $objMember->validatePhoneNumber($phone) ;
            $email2_res = true;
            //==================
         
        }
       include_once "includes/header.php";
       
     
        ?>
            <!-- Navbar End -->

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="row" action="" method="POST" enctype="multipart/form-data">

                            <div class="row" id="editprofile" style="display: flex;">
                                <div class="col-md-6">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Personal Infomation</strong>
                                        <div class="col-12 mb-3">
                                            <label for="">First Name</label>
                                            <input type="text" class="form-control" name="firstName" value="<?php  echo $fname;?>">
                                            <?php if(!$fname_res){ ?>
                                            <sapan class="errortext" style="color:red">Please fill your FirstName / Invalid name length </span>
                                            <?php } ?>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="">Last Name</label>
                                            <input type="text" class="form-control" name="lastName" value="<?php  echo $lname;?>">
                                            <?php if(!$lname_res){ ?>
                                            <sapan class="errortext" style="color:red">Please fill your LastName / Invalid name length </span>
                                            <?php } ?>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="">Phone Number</label>
                                            <input type="text" class="form-control" name="phone" value="<?php  echo $phone;?>">
                                            <?php if(!$phone_res){ ?>
                                            <sapan class="errortext" style="color:red">Enter Phone Number</span>
                                            <?php } else if(!$phone_res1){ ?>
                                            <sapan class="errortext" style="color:red">Enter Phone number in required  format(Expecting 7 digit Number)</span>
                                            <?php } ?>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="">Email</label>
                                            <input type="text" class="form-control" name="email" value="<?php  echo $email;?>">
                                            <?php if(!$email_res){ ?>
                                            <sapan class="errortext" style="color:red">Enter Email</span>
                                            <?php } else if(!$email1_res){ ?>
                                            <sapan class="errortext" style="color:red">Enter Email in required  format</span>
                                            <?php } else if(!$email2_res){ ?>
                                            <sapan class="errortext" style="color:red">Entered Email already Exist for another user</span>
                                            <?php } ?>

                                        </div>
                                    
                                        <div class="col-12 mb-3">
                                        <label for="">Upload profile Image</label>
                                            <label class="uploadFile form-control">
                                                <span class="filename"></span>
                                                <input type="file" class="inputfile" name="p-image">
                                            </label>
                                            <span class="d-block">Note : Maximum Image Size 1000 kb</span>
                                             <?php if(!empty($imageErr)){ ?>
                                            <sapan class="errortext" style="color:red"><?php echo $imageErr; ?></span>
                                            <?php } ?>

                                        
                                        </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Contact Details</strong>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="">Street Address</label>
                                            <textarea type="text" class="form-control" rows="1" name="address" >
                                            <?php  echo $address;?>
                                            </textarea>

                                        </div>
                                        <!-----no need for admin ------
                                        <div class="col-12 mb-3">
                                            <label for="">Country</label>
                                            <input type="text" class="form-control" name="country" value="<?php // echo $row['country'];?>">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="">City</label>
                                            <input type="text" class="form-control" name="city" value="<?php  //echo $row['city'];?>">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label for="">Zip Code / Postel Code</label>
                                            <input type="text" class="form-control" name="postalcode" value="<?php // echo $row['zipcode'];?>">
                                        </div>
                                        <!-------------------->
                                        <div class="col-md-6 mb-3">
                                        <img src="<?php  echo  $profileImageURL;?>" border="0" width="180" height="200" />
                                        </div>
                                    </div>    
                                </div>
                                <div class="col-12 d-flex">
                                    <!-- <button type="button" class="btn btn-primary btn-typ4">UPDATE PROFILE</button> -->
                                    <input type="submit" name="submit" value="UPDATE PROFILE" id="submit" class="btn btn-primary btn-typ4">

                                    <button type="button" class="btn btn-primary btn-typ3 ms-2" onclick="goBack()">CANCEL</button>
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
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Profile Updated</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backuserpage()" ></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->
            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-0 footer-section">
                <div class="bg-primary p-4">
                    <div class="row">
                        <div class="col-12 text-center text-lg-end small credit">
                            Designed By <a href="https://htmlcodex.com">HTML Codex</a>Reubro International
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
        // JavaScript function to display the success pop-up message
        function showSuccessPopup() {
            alert("Profile updated successfully!");
        }
        function goBack() {
    window.location = "home.php";
  }
   function backuserpage() {
     window.location = "home.php";
    }
    </script>
</body>

</html>
