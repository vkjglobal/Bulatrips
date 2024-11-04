<?php
// Start the session (assuming you already have a logged-in user session)
session_start();
require_once('includes/dbConnect.php');
include_once('includes/class.Users.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you have retrieved the user ID and password from the session or database
    $userId = $_SESSION['user_id']; // Replace 'user_id' with the appropriate session variable or database retrieval
    $objUser = new Users(); // Create an object of the Airport class


    $userDetails = $objUser->getUserDetails($userId);
    // Assuming you have retrieved the current password from the user profile (session or database)
    $currentPasswordFromDB = trim($userDetails['password']); // Replace this with the hashed password from the database

    // Retrieve the current and new passwords from the AJAX request
    // $currentPassword = $_POST['currentPassword'];
    // $newPassword = $_POST['newPassword'];
    // Sanitize and validate the input data
    $currentPassword = filter_input(INPUT_POST, 'currentPassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $newPassword = filter_input(INPUT_POST, 'newPassword', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $currentPasswordHash=hash('sha256',$currentPassword);
    // print_r($currentPasswordHash);
    // print_r("test");
    // print_r($currentPasswordFromDB);

    // Verify the current password using password_verify()
    // if (password_verify($currentPasswordHash, $currentPasswordFromDB)) {
    if ($currentPasswordHash === $currentPasswordFromDB) {
        // Hash the new password using password_hash()
        // $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $newPasswordHash=hash('sha256',$newPassword);
        $stmtupdate = $conn->prepare('UPDATE users SET password = :password WHERE id = :id');
        // print_r("test");die();
   
    $stmtupdate->bindParam(':password', $newPasswordHash);
    $stmtupdate->bindParam(':id', $userId);
    $stmtupdate->execute();

        // Save the hashed new password in the database (assuming you have an update query)
        // Your database update code goes here
        // For example:
        // $updateQuery = "UPDATE users SET password = :newPassword WHERE id = :userId";
        // Execute the update query with the new hashed password and user ID

        // Password change successful
        echo 'success';
    } else {
        // Current password is incorrect
        echo 'error';
    }
}
?>
