<?php
// NewsletterSubscriber.php
include_once('class.MyConnection.php');

class Booking extends MyConnection
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }
    

    public function getBookingDetailsbyId($bookingId)
    {
       
            // Validate the email address
            try {
                $query = "
                    SELECT tb.*, fs.*
                    FROM temp_booking AS tb
                    LEFT JOIN flight_segment AS fs ON tb.id = fs.booking_id
                    WHERE tb.id = :bookingId;
                ";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_STR);
                $stmt->execute();
    
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } catch (PDOException $e) {
                // Handle the exception (e.g., log the error)
                return null;
            }
    }
    public function getBookingDetailsbyTicketstatus()
    {
       
            // Validate the email address
            try {
                $query = "
                    SELECT *
                    FROM temp_booking                   
                    WHERE ticket_status = :ticketStatus OR ticket_status = :ticketStatus2;
                ";
                $stmt = $this->conn->prepare($query);

                $stmt->bindValue(':ticketStatus', 'TktInProcess', PDO::PARAM_STR);
                $stmt->bindValue(':ticketStatus2', 'BookingInProcess', PDO::PARAM_STR);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
                return $result;
            } catch (PDOException $e) {
                // Handle the exception (e.g., log the error)
                return null;
            }
    }
    public function getBookCronIDs()
    {
       
            // Validate the email address
            try {
                $query = "SELECT * 
                                FROM temp_booking 
                                WHERE mf_reference != '' 
                                  AND dep_date >= NOW() 
                                  AND (`ticket_status` NOT LIKE '%Ticketed%' OR `ticket_status` IS NULL)
                                    ";
                                   // AND ticket_time_limit >= NOW()
                $stmt = $this->conn->prepare($query);

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
