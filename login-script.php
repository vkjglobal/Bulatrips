<?php

// Start session
session_start();
require_once('includes/dbConnect.php');
// Get username and password from form submission
$email = $_POST['loginemail'];
$password = $_POST['loginpassword'];
$hasedpassword=hash('sha256',$password);
// Prepare and execute SQL statement
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hasedpassword);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify password
// if ($user && password_verify($hasedpassword, $user['password'])) {
  // Authentication successful, set session variables
if ($stmt->rowCount() == 1) {

  if( isset($user['status']) && $user['status'] != 'active' ) {
    echo "error";
  } else {

  
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['customer_role-id'] = $user['role'];
    echo 'endsuccess';

  // if($user['role'] == 1){
    
  // }else if($user['role'] == 2 && $user['agent_status'] == 'active' ){
  //   $_SESSION['user_id'] = $user['id'];
  //   $_SESSION['first_name'] = $user['first_name'];
  //   $_SESSION['email'] = $user['email'];
  //   $_SESSION['customer_role-id'] = $user['role'];
  //   echo 'agentsuccess';
  // }else if($user['role'] == 2 && $user['agent_status'] == 'inactive' ){
  //   $_SESSION['user_id'] = $user['id'];
  //   $_SESSION['first_name'] = $user['first_name'];
  //   $_SESSION['email'] = $user['email'];
  //   $_SESSION['customer_role-id'] = $user['role'];
  //   echo 'agenterror';
  // }
  }
} else {
  // Authentication failed
  // $response = [
  //   'success' => false
  // ];
  echo 'error';
}




// header('Content-Type: application/json');
// echo json_encode($response);

?>
