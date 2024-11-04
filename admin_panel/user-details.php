<?php 
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> admin user page
   Programmer	::> Soumya
   Date		::> 3-07-2023
   DESCRIPTION::::>>>>
   This is  code used for admin user detail page
*****************************************************************************/
include_once "includes/header.php";
include_once "includes/class.users.php";
$objUsers		= 	new Users();

//exit;
if((isset($_GET['uid']))  && (isset($_GET['action'] )))
{
      if($_GET['action'] === 'delete'){
        
        $uid =  $_GET['uid'] ;       
        $userDel	=   $objUsers->DeleteUserDetails($uid);
        if($userDel){
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
if(isset($_GET['id']))
{
    $userId =  $_GET['id'] ;

$user	=   $objUsers->getUserDetails($userId);
 $name           =   $user[0]['first_name']." ".$user[0]['last_name'];
    $email           =   $user[0]['email'];
    $phone           =   $user[0]['mobile'];
    $uname          =   $user[0]['username'];
    $contact_address =   $user[0]['contact_address'];

   ?>


            <!-- Product List Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="border-primary rounded p-4">
                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block text-left">User Details</strong>
                    
                    <div class="user-details">
                        <div class="row mb-2">
                            <strong class="col-4">Name:</strong>
                            <strong class="col-8"><?php echo $name; ?></strong>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Email:</strong>
                            <span class="col-8"><?php echo $email; ?></span>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Phone:</strong>
                            <span class="col-8"><?php echo $phone; ?></span>
                        </div>
                        <div class="row mb-2">
                            <strong class="col-4">Username:</strong>
                            <span class="col-8"><?php echo $uname; ?></span>
                        </div>
                        <!---- 
                        <div class="row mb-2">
                            <strong class="col-4">Status:</strong>
                            <strong class="col-8"><span class="badge bg-success">Active</span></strong>
                        </div>
                        <!---- ---->
                        <div class="row">
                            <strong class="col-4">Address:</strong>
                            <Address class="col-8"><?php echo $contact_address; ?>
                            </Address>
                        </div>
                        <a href="users.php" class="btn btn-primary d-inline-flex btn-typ3 w-auto">Back</a>
                    </div>
                     <?php } ?>
                </div>
            </div>
           
           <!--Start -->
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
            <!-- Product List End -->
            <script>
             function backuserpage() {
     window.location = "users.php";
}</script>
            <!-- Footer Start -->
            <?php
           // echo "<script>showSuccessMessage();</script>";

include "includes/footer.php";
?>
