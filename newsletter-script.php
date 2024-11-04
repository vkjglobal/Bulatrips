<?php



// Include the NewsletterSubscriber class
include_once('includes/class.Newsletter.php');
require_once('includes/dbConnect.php');


if (isset($_POST)) {
    // Get the submitted email address
    $email = $_POST['newsletter-email'];
    $name = $_POST['newsletter-name'];

    // Database connection credentials
   
    // Create a PDO database connection
    try {
      
        

        // Create an instance of the NewsletterSubscriber class
        $subscriber = new Newsletter($conn);

        // Subscribe the user and get the result message
        $resultMessage = $subscriber->subscribe($email,$name);

        // Output the result message
        echo $resultMessage;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

