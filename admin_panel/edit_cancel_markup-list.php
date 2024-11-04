<?php 
 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
}
 include_once "includes/header.php";
include_once "includes/class.booking.php";
$objBooking		= 	new Booking();
if(isset($_GET['id']) && ($_GET['id'])!="")
{
    $markupId  =   trim($_GET['id']);
     $listmarkup	=   $objBooking->getmarkupInfo($markupId);
   //  print_r($listmarkup);
}
 if(isset($_POST['submit'])){  //collecting new form data
              $markupperc    =  trim($_POST['markupname']);

      $upateArr    =   $objBooking->updateMarkup($markupId,$markupperc);
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
                else{
                    echo "error in updation";
                }

 }
 else{
     $markupperc  =  $listmarkup[0]['commission_percentage'] ;
 }

          ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Edit Mark up  percentage</strong>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="">Mark up percentage</label>
                                            <input type="text" class="form-control" value="<?php  echo  $markupperc; ?>" name="markupname" id="name">
                                        </div>                                      
                                       
                                        
                                    </div>
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" id="submit" class="btn btn-primary btn-typ3">
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    <a href="markup-list.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
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
            var numberPattern = /^\d+$/;

            $(".errortext").remove();
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Markup % cannot be blank.</span>')	       
            valid = false;
            }
            else if(!numberPattern.test($('#name').val())){
                $('#name').after('<span class="errortext" style="color:red">cannot  include letters or Special Characters</span>')	       
            valid = false;
            }
           
            
            if( !valid ){       
            return valid;
            }	
        });
    });
    function backuserpage() {
     window.location = "cancel_markup-list.php";
    }
</script> 