<?php
// NewsletterSubscriber.php
include_once('class.MyConnection.php');

class Contact extends MyConnection
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }
    

    public function contact($email,$name,$subject,$message)
    {
        try {
            // Validate the email address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
           

            // Prepare and execute the SQL query to insert the email into the database
            $stmt = $this->conn->prepare("INSERT INTO contact (email,customer_name,subject,message) VALUES (:email,:name,:subject,:message)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            $stmt->execute();

            return "We will contact you soon.";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?>
