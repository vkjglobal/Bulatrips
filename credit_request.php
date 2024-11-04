<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'includes/class.Data.php';

$newObj = new Data();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = isset($_POST['Amount']) ? $_POST['Amount'] : null;
    $id = isset($_POST['userId']) ? $_POST['userId'] : null;

    error_log("POST data: " . print_r($_POST, true));

    if ($amount && $id) {
        
        // check the request exist or not
        $verify = $newObj->verify_request($id);
        
        if($verify)
        {
            echo 'already_submitted';
            exit();
        }else{
    
            $request = $newObj->credit_request($amount, $id);
            if ($request) {
                echo 'success';
                exit();
            } else {
                error_log('Credit request failed: ' . print_r($_POST, true));
                echo 'error';
                exit();
            }
        }
    } else {
        error_log('Invalid input: ' . print_r($_POST, true));
        echo 'error: invalid input';
        exit();
    }
} else {
    error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    echo 'error: invalid request method';
    exit();
}
