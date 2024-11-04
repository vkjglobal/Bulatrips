<?php
session_start();

// Store all POST values in a single session variable
$_SESSION['revalidationApi'] = $_POST;
if(isset($_SESSION['revalidationApi'])){
    // Return success response
    $response = array("success" => true);
}else{
    // session error
     $response = array("success" => false);
}

header('Content-Type: application/json');
echo json_encode($response);
?>