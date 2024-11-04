<?php 
// Turn off error reporting
//ror_reporting(0);
//ini_set('display_errors', 0);

 session_start();
if(!isset($_SESSION['adminid'])){
?>
	<script>
    window.location="index.php"    </script>
    <?php
} include_once "includes/header.php";
  include_once "includes/class.contents.php";


  //******************************************************
$objContent     =   new Contents();
$title          ="";
$description    ="";
$subject        ="";
$button_text    =   "Add";
  if(isset($_POST['Add']))
        {     
            $title=$_POST['name'];
            $description   =$_POST['editor'];
            $description    =  htmlentities($description);            
            $subject        =   $_POST['subject'];
           $addednews =   $objContent->insNewsLetter($title,$subject,$description);
           if(!empty($addednews))
           { 
                     echo "<script>";
                echo "document.addEventListener('DOMContentLoaded', function() {";
                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                echo "        var staticBackdropLabel = document.getElementById('staticBackdropLabel');"; // Select the dynamic text element
                echo " staticBackdropLabel.textContent = 'Successfully Added';";
                echo "    if (addsuccesspop) {";
                echo "        addsuccesspop.classList.add('show');";
                echo "        addsuccesspop.style.display = 'block';";
                echo "    }";
                echo "});";
                echo "</script>";
               // echo "update success ";
                }
                else{
                    echo "error in Insertion";
                }
                $button_text    =   "Add";
          // print_r($addednews);exit;
        }
        if(isset($_GET['id'])){
                    $id =   $_GET['id'];
                    $datas	=   $objContent->getListNews($id);

                  //  print_r($datas);
               
                if(isset($_POST['Update']))
                                {         
           
                                    $title=$_POST['name'];
                                    $description   =$_POST['editor'];
                                    $description    =  htmlentities($description);            
                                    $subject=$_POST['subject'];
             
                               //print_r($_POST);exit;
                  
                                            $upateArr =   $objContent->updateNews($title,$subject,$description,$id);
                   
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
                                   $title          =   $datas[0]['title'];
                                  $description    =   $datas[0]['content'];        
                                   $subject          =     $datas[0]['subject'];       //print_r($content);
                                            }
                                            $button_text    =   "Update";
                }//end of edit
                if((isset($_GET['uid']))  && (isset($_GET['action'] )))
{
                                      if($_GET['action'] === 'delete'){
        
                                        $pid =  $_GET['uid'] ;      
                                  //    echo $pid;exit;
                                   $homedel=   $objContent->DelNews($pid);
                                        if($homedel){
                                                                             echo "<script>";
                                                echo "document.addEventListener('DOMContentLoaded', function() {";
                                                echo "    var addsuccesspop = document.getElementById('addsuccesspop');";
                                                echo "        var staticBackdropLabel = document.getElementById('staticBackdropLabel');"; // Select the dynamic text element
                                                echo " staticBackdropLabel.textContent = 'Successfully Deleted';";
                                                echo "    if (addsuccesspop) {";
                                                echo "        addsuccesspop.classList.add('show');";
                                                echo "        addsuccesspop.style.display = 'block';";
                                                echo "    }";
                                                echo "});";
                                                echo "</script>";
                                               // echo "update success ";
                                                }
                                                else{
                                                    echo "error in Deletion";
                                                }
                                      }
                                    //  exit;
}
        ?>

            <!-- Account Settings Start -->
            <div class="container-fluid h-100 pt-4 px-4">
                <div class="row border-primary rounded mx-0 p-3">
                    <div class="col-12">
                        <form class="" method="POST" action="" enctype="multipart/form-data">

                            <div class="row" style="display: flex;">
                                <div class="col-12">
                                    <strong class="fs-16 fw-500 light-blue-txt mb-3 d-block">Add Newsletter</strong>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="">title</label>
                                            <input type="text" class="form-control" value="<?php echo $title;?>" name="name" id="name">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label for="">Subject</label>
                                            <input type="text" class="form-control" value="<?php echo $subject;?>" name="subject" id="subject">
                                        </div>
                                
                                        <div class="col-md-12 mb-3">
                                            <label for="">Message</label>
                                            
                                            
                                            <textarea id="editor" name="editor" >
                                                <?php  echo $description; ?>

                                            </textarea>
                                        </div>
                                       

                                    </div>   
                                    
                                </div>
                                <div class="col-12 d-flex">
                                <input type="submit" name="<?php echo $button_text; ?>" value="<?php echo $button_text; ?>" class="btn btn-primary" id="submit">
                                   
                                    <!-- <button type="button" class="btn btn-primary btn-typ3 ms-2">CANCEL</button> -->
                                    
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
                            <h1 class="modal-title fs-5 text-secondary" id="staticBackdropLabel">Successfully Updated</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="backuserpage()" ></button>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!--  End -->


<?php
include "includes/footer.php";       

?>

<script>
    $(document).ready(function(){        
        $("#submit").click(function () {
            
            valid = true;
                       var regex = /^[a-zA-Z]+$/;
            $(".errortext").remove();
            if($('#name').val() == '') {
            $('#name').after('<span class="errortext" style="color:red">Title cannot be blank.</span>')	       
            valid = false;
            }
            else if(!regex.test($('#name').val())){
                $('#name').after('<span class="errortext" style="color:red">Title cannot  include Numbers or Special Characters</span>')	       
            valid = false;
            }
            if($('#subject').val() == '') {
            $('#subject').after('<span class="errortext" style="color:red">subject cannot be blank.</span>')	       
            valid = false;
            }
         /*     if($('#editor').val() == '') {
            $('#editor').after('<span class="errortext" style="color:red">Message cannot be blank.</span>')	       
            valid = false;
            }
            */
         /*else if(($('#image').val() == '') && ($('#p-image').val() == '')){             
          //lert("KKKK");
            $('#image').after('<span class="errortext" style="color:red">Image cannot be blank.</span>')	       
            valid = false;
            }
        */
           
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
     window.location= "newsletter.php";

    }
</script> 
