<?php
/**************************************************************************** 
   Project Name	::> Bulatrips
   Module 	::> Class for DB Actions
   Programmer	::> Nimmi E V
   Date		::> 18-12-2023
   DESCRIPTION::::>>>>
   This is a Class code used to manage db actions
*****************************************************************************/
/**
 * Summary of DbAction
 */
class DBAction
{
    /**
     * Summary of host
     * @var 
     */
    private $host;
    /**
     * Summary of dbname
     * @var 
     */
    private $dbname;
    /**
     * Summary of username
     * @var 
     */
    private $username;
    /**
     * Summary of password
     * @var 
     */
    private $password;
    /**
     * Summary of conn
     * @var 
     */
    private $conn;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        //=================
        if(($_SERVER['HTTP_HOST'] == 'localhost:8080')||($_SERVER['HTTP_HOST'] == 'localhost')) {
            $host = "localhost";
            $username = "root";
            $password = "";
            $dbname = "travelsite";
        }
        if($_SERVER['HTTP_HOST'] == 'bulatrips.com') {
            $host = "localhost";
            $username = "amhyywehvb";
            $password = "PJusRbyr72";
            $dbname = "amhyywehvb";   
        }
        if($_SERVER['HTTP_HOST'] == 'staging.bulatrips.com') {
            $host = "localhost";
            $username = "bulatrips_staging";
            $password = "@]9E~IwT7k%L";
            $dbname = "bulatrips_staging";
        }
            //===================
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }
    
    /**
     * Summary of connect
     * @return void
     */
    private function connect()
    {
        try
        {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e)
        {
            die("Connection failed: " . $e->getMessage());
        }
    }
    /**
     * Summary of executeQuery
     * @param mixed $sql
     * @param mixed $params
     * @return array
     */
    public function executeQuery($sql, $params = []) {
        //print_r($sql); print_r($params);
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            //print_r($ss);
            // return $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ss = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //print_r($ss);
            return $ss;
        } catch (PDOException $e) {
            die("Query execution failed: " . $e->getMessage());
        }
    }
    public function selectbyEmail($tableName,$email)
    {
        $sql = "SELECT * from $tableName WHERE email = ?";
        $params = [$email];
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function insertInto($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        // print_r($placeholders);exit;
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        //print_r($query );
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($data);
            //print_r($ss);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function selectByToken($tableName, $token)
    {
        $query = "SELECT * FROM $tableName WHERE tocken = ?";
        $params = [$token];
        return $this->executeQuery($query, $params);
    }
    public function updatePassword($userID, $new_pw)
    {
        $newPassword = hash('sha256',$new_pw);
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $params = [$newPassword, $userID];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function selectbyId($tableName,$id)
    {
        $sql = "SELECT * from $tableName WHERE id = ?";
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }
    public function selectreview($tableName,$id)
    {
        $sql = "SELECT * from $tableName WHERE user_id = ? AND status = 1";
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }
    public function selectDReview($tableName,$id)
    {
        $sql = "SELECT * from $tableName WHERE user_id = ?";
        $params = [$id];
        return $this->executeQuery($sql, $params);
    }
    public function Updatereview($tableName,$id, $title, $description, $name, $file_name, $rating)
    {
        $query = "UPDATE $tableName SET title = ?, description = ?, author = ?, image = ?, rating = ? WHERE user_id = ?";
        $params = [$title, $description, $name, $file_name, $rating, $id];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        } 
    }
    public function deleteReview($tableName,$id)
    {
         $query = "UPDATE $tableName SET status = '0' WHERE user_id = ?";
        $params = [$id];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        } 
    }
    Public function selectTable($tableName)
    {
        $sql = "SELECT * from $tableName";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function UpdateRequest($tableName,$amount,$id)
    {
        $query = "UPDATE $tableName SET request_amount = ?, credit_request = '1' WHERE id = ?";
        $params = [$amount,$id];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        } 
    }
    public function VerifyRequest($tableName,$id)
    {
         $sql = "SELECT * from $tableName WHERE credit_request = '1' AND id = $id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
}
?>