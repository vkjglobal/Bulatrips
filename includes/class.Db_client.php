<?php
 include_once('includes/dbConnect.php');
 
class Db_client {
   protected $conn;

    public function __construct() {
        global $conn; // Use the global connection object from dbconnect.php
        
        // If global connection doesn't exist, create a new one
        if (!isset($conn) || !$conn) {
            // Handle CLI execution where HTTP_HOST is not set
            if (!isset($_SERVER['HTTP_HOST'])) {
                $_SERVER['HTTP_HOST'] = 'localhost';
            }
            
            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                define('DB_HOST', 'localhost');
                define('DB_USER', 'root');
                define('DB_PASS', '');
                define('DB_NAME', 'travelsite');
                
                try {
                    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                } catch (PDOException $e) {
                    throw new Exception("Database connection failed: " . $e->getMessage());
                }
            } else {
                throw new Exception("Database connection not available for host: " . $_SERVER['HTTP_HOST']);
            }
        }
        
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

            // IMPORTANT: use bindValue here instead of bindParam to avoid by-reference
            // binding bugs when iterating. bindParam would bind the same variable
            // reference repeatedly causing all placeholders to take the last value.
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
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
        try {
            // Filter out NULL values and prepare columns/values
            $filteredData = array_filter($data, function($value) {
                return $value !== null;
            });
            
            $columns = implode(', ', array_keys($filteredData));
            $placeholders = ':' . implode(', :', array_keys($filteredData));
            
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            
            // Debug log
            $this->_writeLog("SQL Query: " . $query, 'debug.txt');
            $this->_writeLog("Data: " . print_r($filteredData, true), 'debug.txt');
            
            $stmt = $this->conn->prepare($query);
            
            // Bind each value, handling NULL values properly
            foreach ($filteredData as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->execute();
            return $this->conn->lastInsertId();
            
        } catch (PDOException $e) {
            // Log the error
            $this->_writeLog("Database Error: " . $e->getMessage(), 'debug.txt');
            throw $e;
        }
    }

    // Helper function to write logs
    private function _writeLog($content = "", $filename = "log.txt") {
        $logPath = 'uploads/logFiles/' . $filename;
        $fp = fopen($logPath, "a+");
        fputs($fp, date('[Y-m-d H:i:s] ') . $content);
        fputs($fp, "\r\n");
        fclose($fp);
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
         // echo "<pre/>";
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
        //  echo "<pre/>";
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
            // Debug log the input parameters
            $this->_writeLog("Executing passenger query with bookingId: $bookingId, userId: $userId", 'debug.txt');
           
            // Security: Only booking owner can cancel their booking
            $sql = 'SELECT  
                                tb.booking_status,
                                tb.ticket_time_limit,
                                tb.mf_reference,
                                tb.ticket_status AS booking_ticket_status,
                                tb.fare_type,
                                tb.child_count,
                                tb.void_window,
                                tb.dep_date,
                                tb.arrival_location,
                                td.id,
                                td.first_name,
                                td.last_name,
                                td.title,
                                td.passenger_type,
                                td.e_ticket_number,
                                td.ticket_status AS pass_ticket_status,
                                td.void_status,
                                td.ptr_id,
                                td.reissue_status,
                                td.reissue_ptr_id,
                                td.reissue_quote_id,
                                cb.cancel_status AS cb_cancel_status,
                                cb.ptr_status AS cb_ptr_status
                        FROM travellers_details td
                        LEFT JOIN temp_booking tb ON td.flight_booking_id = tb.id
                        LEFT JOIN cancel_booking cb ON cb.ticket_number = td.e_ticket_number AND cb.booking_id = tb.id
                        WHERE td.flight_booking_id = :bookingId and tb.user_id = :userId';
            
            // Debug log the SQL query
            $this->_writeLog("SQL Query: " . $sql, 'debug.txt');
            
            $stmtpassenger = $this->conn->prepare($sql);
            $stmtpassenger->execute(array('bookingId' => $bookingId, 'userId' => $userId));

            $result = $stmtpassenger->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug log the result
            $this->_writeLog("Query result: " . print_r($result, true), 'debug.txt');
            
            return $result;
        } catch (PDOException $e) {
            // Log the error
            $this->_writeLog("Database Error: " . $e->getMessage(), 'debug.txt');
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