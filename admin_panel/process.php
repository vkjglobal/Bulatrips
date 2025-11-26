<?php
//echo "success";exit;

include_once "includes/class.DbAction.php";
$objDB = new DbAction();

// update review details from admin
if (isset($_POST['action']) && $_POST['action'] === 'update_review') {
    $reviewId    = isset($_POST['reviewId']) ? $_POST['reviewId'] : '';
    $title       = isset($_POST['title']) ? $_POST['title'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $author      = isset($_POST['author']) ? $_POST['author'] : '';
    $rating      = isset($_POST['rating']) ? $_POST['rating'] : '';

    if ($reviewId != '') {
        $updateReview = $objDB->updateReviewDetails($reviewId, $title, $description, $author, $rating);
        if ($updateReview) {
            echo "update_success";
            exit;
        } else {
            echo "update_error";
            exit;
        }
    } else {
        echo "update_error";
        exit;
    }
}

// existing: toggle review hide/unhide status
if (isset($_POST['rowId'])) {
    $rowid  = $_POST['rowId'];
    $status = $_POST['status'];
    // echo $rowid.' '.$status;
    if ($status == 0) {
        $statusid = '1';
    } else {
        $statusid = '0';
    }
    $updateStatus = $objDB->updateReviewActivateStatus($rowid, $statusid);
    if ($updateStatus) {
        echo "success";
        exit;
    } else {
        echo "err1";
        exit;
    }
    echo "err2";
    exit;
}
?>