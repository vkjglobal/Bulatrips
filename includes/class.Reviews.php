<?php
// NewsletterSubscriber.php
include_once('class.MyConnection.php');

class Reviews extends MyConnection
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }
    

    public function getReviesDetails()
    {
       
            // Validate the email address
            try {
                $query = "SELECT * FROM reviews WHERE status = 1";
                $stmt = $this->conn->prepare($query);
                // $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_STR);
                $stmt->execute();
    
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (PDOException $e) {
                // Handle the exception (e.g., log the error)
                return null;
            }
    }
}
?>
