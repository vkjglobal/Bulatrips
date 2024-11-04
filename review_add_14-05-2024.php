<?php
echo 'helo';exit;
    session_start();
    // require_once("includes/header.php");
    include_once 'includes/class.Data.php';

    $reviewObj = new Data();

    $imageErr   =   "";
    //check form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// print_r($_POST);
        $title = $_POST['title'];
        $rating = $_POST['rating'];
        $description = $_POST['description'];
        // $image = $_POST['review_pic'];
        // $image = $_FILES['review_pic'];
        if(!isset($_FILES['review_pic'])){
            $file_name = 'logo.png';
        }
        if (isset($_FILES['review_pic']) && $_FILES['review_pic']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES['review_pic']['name'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            // Check if the file extension is allowed
            if (!in_array(strtolower($file_extension), $allowedExtensions)) {
                $errors[] = "Invalid file extension. Please upload a JPG, JPEG, PNG, or GIF file.";
            } else {
                // Move the uploaded file to a desired location
                $upload_directory = 'uploads/reviews/'; // Directory where you want to store the uploaded files
                $upload_path = $upload_directory . $file_name;

                if (move_uploaded_file($_FILES['review_pic']['tmp_name'], $upload_path)) {
                    // File uploaded successfully, you can proceed with further processing
                    // Access the file via $upload_path
                } else {
                    $errors[] = "Failed to move the uploaded file.";
                }
            }
        }


        $id = $_SESSION['user_id'];
        //select username 
        $author = $reviewObj->select_author($id);
        $name = $author[0]['first_name'].$author[0]['last_name'];

        // File uploaded successfully
        $addreview = $reviewObj->add_review($title, $description, $name, $file_name, $rating);
        if ($addreview) {
            echo "success";
        } else {
            echo "error";
        }
       
    } else {
        // Handle if it's not a POST request
        echo 'invalid request';
    }
        
?>