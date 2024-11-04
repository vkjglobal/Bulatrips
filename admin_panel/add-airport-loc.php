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

if(isset($_POST['submit']))
        {
          
 //    print_r($_POST);exit;
        $airport_code   =   trim($_POST['name']);
        $airportname   =   trim($_POST['airportname']);
        $citycode   =   trim($_POST['citycode']);
        $cityname   =   trim($_POST['cityname']);
        $countryname   =   trim($_POST['countryname']);
        $countrycode   =   trim($_POST['countrycode']);
        $addAirports        =   $objGeneral->addAirports($airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode);
        if(!empty($addAirports)){
               echo "<script>";
                echo "document.addEventListener('DOMContentLoaded', function() {";
                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                echo "    if (addsuccesspop) {";
                echo "        addsuccesspop.classList.add('show');";
                echo "        addsuccesspop.style.display = 'block';";
                echo "    }";
                echo "});";
                echo "</script>";
        }
        else{
            echo "error in insertion";
        }

    }

        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add Airport Locations</strong>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="">Airport Code</label>
                                            <input type="text" class="form-control" name="name" id="name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="">Airport Name</label>
                                            <input type="text" class="form-control" name="airportname" id="airportname">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">city Code</label>
                                            <input type="text" class="form-control" name="citycode" id="citycode">
                                        </div>
                                      
                                        <div class="col-md-6 mb-3">
                                            <label for="">city Name</label>
                                            <input type="text" class="form-control" name="cityname" id="cityname">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Country Name</label>
                                            <input type="text" class="form-control" name="countryname" id="countryname">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="">Country Code</label>
                                            <input type="text" class="form-control" name="countrycode" id="countrycode">
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="submit" value="submit" id="submit" class="btn btn-primary btn-typ3">
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    <a href="airport-list.php" class="btn btn-primary btn-typ3 ms-2">Cancel</a>   
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
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Successfully Added</h1>
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
            $('#name').after('<span class="errortext" style="color:red">Airport Code cannot be blank.</span>')	       
            valid = false;
            }
              if($('#airportname').val() == '') {
            $('#airportname').after('<span class="errortext" style="color:red">airportname cannot be blank.</span>')	       
            valid = false;
            }
              if($('#citycode').val() == '') {
            $('#citycode').after('<span class="errortext" style="color:red">citycode cannot be blank.</span>')	       
            valid = false;
            }
              if($('#cityname').val() == '') {
            $('#cityname').after('<span class="errortext" style="color:red">cityname cannot be blank.</span>')	       
            valid = false;
            }
              if($('#countryname').val() == '') {
            $('#countryname').after('<span class="errortext" style="color:red">countryname cannot be blank.</span>')	       
            valid = false;
            }
              if($('#countrycode').val() == '') {
            $('#countrycode').after('<span class="errortext" style="color:red">countrycode cannot be blank.</span>')	       
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
     window.location = "airport-list.php";
    }
</script>