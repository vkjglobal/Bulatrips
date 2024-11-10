<?php
// NewsletterSubscriber.php
include_once('class.MyConnection.php');

class Newsletter extends MyConnection
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }
    

    public function subscribe($email,$name="")
    {
        try {
            // Validate the email address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
            // Check if the email is already subscribed
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM newsletter WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                return "Email is already subscribed";
            }

            // Prepare and execute the SQL query to insert the email into the database
            $stmt = $this->conn->prepare("INSERT INTO newsletter (email) VALUES (:email)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return "Subscription successful. Thank you for subscribing!";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?>
