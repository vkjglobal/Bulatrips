<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for DB Actions
   Programmer	::> Nimmi
   Date		::> 06-06-2024
   DESCRIPTION::::>>>>
   This is a Class code used to manage db actions nimmi
*****************************************************************************/
class Dbconnect {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;

    public function __construct(){
        //=================
        if(($_SERVER['HTTP_HOST'] == 'localhost:8080')||($_SERVER['HTTP_HOST'] == 'localhost')) {
            $host = "localhost";
            $username = "root";
            $password = "";
            $dbname = "bulatrips_db";
        }
        else if($_SERVER['HTTP_HOST'] == 'travelsite.reubrosample.tk') {
            $host = "localhost";
            $username = "reubrode_travelsite";
            $password = "Reubro@2023";
            $dbname = "reubrode_travelsite";
        }
        else if($_SERVER['HTTP_HOST'] == 'travelsite.reubro.com') {
            $host = "localhost";
            $username = "reubroco_travelsite";
            $password = "Reubro@2023";
            $dbname = "reubroco_travelsite";
        }
        else if ($_SERVER['HTTP_HOST'] == 'bulatrips.com') {

            $host = "localhost";
            $username = "bulatrips_db";
            $password = "Reubro@2023";
            $dbname = "bulatrips_db";   
            
        } else if ($_SERVER['HTTP_HOST'] == 'staging.bulatrips.com') {
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

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    public function selectById($tableName, $id) {
        $query = "SELECT * FROM $tableName WHERE id = ?";
        $params = [$id];
        return $this->executeQuery($query, $params);
    }
    public function selectCMSDB($sql)
    {
        return $this->executeQuery($sql);
    }
    public function executeQuery($sql, $params = []) {
        // print_r($sql); print_r($params);
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $ss = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // print_r($ss);
            return $ss;
        } catch (PDOException $e) {
            die("Query execution failed: " . $e->getMessage());
        }
    }
}