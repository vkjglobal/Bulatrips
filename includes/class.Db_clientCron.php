<?php
include_once __DIR__ . '/../includes/dbConnect.php';
class Db_clientCron {
   protected $conn;

    public function __construct() {
       
        global $conn; // Use the global connection object from dbconnect.php
        $this->conn = $conn;
        
    }

    // Function to insert data using prepared statement with PDO
    public function insertData($name, $email) {    // $outerObj->insertDataIntoDB("John Doe", "john@example.com");
        try {
            $stmt = $this->conn->prepare("INSERT INTO your_table (name, email) VALUES (:name, :email)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return false;
        }
    }
    //update====================
    public function update($table, $data, $condition) {
     
        try {
            $fields = array();
            foreach ($data as $key => $value) {
                $fields[] = "{$key} = :{$key}";
            }
            $fields = implode(', ', $fields);
       

            $sql = "UPDATE {$table} SET {$fields} WHERE {$condition}"; 
            $stmt = $this->conn->prepare($sql);

            foreach ($data as $key => $value) {
                $stmt->bindParam(":{$key}", $value);
               
            }
         //   echo $sql;exit;
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Handle the exception or log the error if needed
            die("Error executing query: " . $e->getMessage());
        }
    }
    //================
    public function insertInto($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
       // print_r($placeholders);exit;
       $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($data);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }

    // Function to get data using prepared statement with PDO
    public function getData($id) {
        try {
            $stmt = $this->conn->prepare("SELECT name, email FROM your_table WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
   
    public function getListData($tableName) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tableName");
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          echo "<pre/>";
       //rint_r($result);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }

     public function getLisQuery($query) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          echo "<pre/>";
       //rint_r($result);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
     // Method to execute a specific query
    public function executePassengerQuery($bookingId, $userId) {
        try {
           
            $stmtpassenger = $this->conn->prepare('SELECT  temp_booking.booking_status,temp_booking.ticket_time_limit,temp_booking.mf_reference ,temp_booking.ticket_status 
                                                    ,temp_booking.fare_type,temp_booking.child_count,temp_booking.void_window,temp_booking.dep_date,temp_booking.arrival_location ,travellers_details.id, travellers_details.first_name, travellers_details.last_name,travellers_details.title,
                                                    travellers_details.passenger_type,travellers_details.e_ticket_number FROM travellers_details 
                                            LEFT JOIN temp_booking ON travellers_details.flight_booking_id = temp_booking.id
                                            WHERE travellers_details.flight_booking_id = :bookingId and temp_booking.user_id = :userId');

            $stmtpassenger->execute(array('bookingId' => $bookingId, 'userId' => $userId));

            $result = $stmtpassenger->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
    //getting dep date for return trip
     public function executeDepDateQuery($bookingId, $userId,$arrival_location) {
        try {
         
            $stmtpassenger = $this->conn->prepare("SELECT fs.dep_date,fs.flight_no,fs.airline_code,fs.cabin_preference FROM temp_booking AS tb
                    LEFT JOIN flight_segment AS fs ON tb.id = fs.booking_id
                    WHERE tb.id = :bookingId AND  tb.user_id = :userId AND fs.dep_location LIKE '%".$arrival_location."%'");
                   
            $stmtpassenger->execute(array('bookingId' => $bookingId, 'userId' => $userId));
                      $result = $stmtpassenger->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
    //getting markup percentage for cancellation
    public function executeMarkupQuery($roleId){
        try {
         
            $stmtMarkup = $this->conn->prepare("SELECT commission_percentage FROM markup_commission 
                    WHERE  role_id= :roleId");                   
            $stmtMarkup->execute(array('roleId' => $roleId));
                      $result = $stmtMarkup->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
    public function getCount($table, $condition = '') {
        try {
            $sql = "SELECT COUNT(*) FROM {$table}";
            if (!empty($condition)) {
                $sql .= " WHERE {$condition}";
            }
           
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $count = $stmt->fetchColumn();
            return $count;
        } catch (PDOException $e) {
            // Handle the exception or log the error if needed
            die("Error executing query: " . $e->getMessage());
        }
    }
    // Add more functions for other database operations as needed
     public function getUserData($tblname,$id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tblname  WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }

    // Function to close the database connection
    public function closeConnection() {
        $this->conn = null;
    }

}
?>