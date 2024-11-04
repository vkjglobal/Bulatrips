<?php 

// print_r($_POST);
include_once 'includes/class.Data.php';

$newObj = new Data();

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $id = $_POST['uid'];
    $total = $_POST['total'];
    //check user id
    $user = $newObj->select_author($id);
    // print_r($user);
    if($total <= $user[0]['credit_balance'])
    {
        echo 'success';
    }else{
        echo 'fail';
    }
}
?>