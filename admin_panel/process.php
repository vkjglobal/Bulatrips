<?php
//echo "success";exit;
 if(isset($_POST['rowId'])) {
     include_once "includes/class.DbAction.php";
$objDB		= 	new DbAction();
     $rowid     =   $_POST['rowId'];
     $status    =   $_POST['status'];
    //cho   $rowid ."jjjjjjjjj".$status;exit;
    // echo $rowid.' '.$status;
     if($status  ==  0){
         $statusid    =   '1';
     }else{
          $statusid    =   '0';
     }
     $updateStatus  =   $objDB->updateReviewActivateStatus($rowid,$statusid);
     if($updateStatus){
         echo "success";exit;
     }
     else{
         echo "err1";exit;
     }
     echo "err2";exit;
 }
?>