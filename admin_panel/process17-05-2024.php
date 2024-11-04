<?php
//echo "success";exit;
 if(isset($_POST['rowId'])) {
     include_once "includes/class.DbAction.php";
$objDB		= 	new DbAction();
     $rowid     =   $_POST['rowId'];
     $status    =   $_POST['status'];
    //cho   $rowid ."jjjjjjjjj".$status;exit;
     if($status  ==  0){
         $status    =   'inactive';
     }else{
          $status    =   'active';
     }
     $updateStatus  =   $objDB->updateReviewActivateStatus($rowid,$status);
     if($updateStatus  == 1){
         echo "success";exit;
     }
     else{
         echo "err1";exit;
     }
     echo "err2";exit;
 }
?>