<?php
include_once 'includes/class.Data.php';

$reviewObj = new Data();

if (isset($_POST['userID'])) {
    $userID = $_POST['userID'];
    // error_log('UserID: ' . $userID);  // Log the userID to the server error log for debugging

    // Uncomment for debugging purposes
    // print_r($userID);exit;

    // delete review
    $delete = $reviewObj->delete_review($userID);
    if ($delete) {
        echo 'Review deleted successfully';
    } else {
        echo 'Error in Review Deletion';
    }
} else {
    echo 'UserID not provided';
}
?>
