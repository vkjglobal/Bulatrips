<?php
include_once __DIR__ . '/../includes/dbConnect.php'; 
class MyConnection {
   private $conn;

    public function __construct() {
        global $conn; // Use the global connection object from dbconnect.php
        $this->conn = $conn;
    }
    // public function getDatabyCode($code) {
    //     try {
    //         $stmt = $this->conn->prepare("SELECT * FROM airportlocations WHERE airport_code = :code");
    //         $stmt->bindParam(':code', $code, PDO::PARAM_STR);
    //         $stmt->execute();

    //         $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //         return $result;
    //     } catch (PDOException $e) {
    //         // Handle the exception (e.g., log the error)
    //         return null;
    //     }
    // }
    public function getAirportbyCode($code) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM airportlocations WHERE airport_code = :code");
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }

    public function getAirlinebyCode($code) {
        try {
            $codeWithWildcard = '%' . $code . '%';
            $stmt = $this->conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
            $stmt->bindParam(':code', $codeWithWildcard, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
    public function getMarkbyUserid($role) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM markup_commission WHERE role_id = :role");
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
    public function getUsersbyId($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // Handle the exception (e.g., log the error)
            return null;
        }
    }
}
