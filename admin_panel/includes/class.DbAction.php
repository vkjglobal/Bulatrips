<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for DB Actions
   Programmer	::> Soumya
   Date		::> 27-06-2023
   DESCRIPTION::::>>>>
   This is a Class code used to manage db actions
*****************************************************************************/
class DbAction {
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
            $dbname = "travelsite";
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
    public function selectBystatus($tableName, $status) {
        $query = "SELECT * FROM $tableName WHERE status = ?";
        $params = [$status];
        return $this->executeQuery($query, $params);
    }
    public function selectAllRecords($tableName) {
        $query = "SELECT * FROM $tableName";
        return $this->executeQuery($query);
    }
    public function countTableRows($tableName) {
        $query = "SELECT COUNT(id) AS recordCount FROM $tableName";
               return $this->executeQuery($query);
    }
     public function selectListPagination($tableName,$searchsql='') {
        $query = "SELECT * FROM $tableName $searchsql";
        return $this->executeQuery($query);
    }
     public function selectTravellers($tableName) {         
        $query = "SELECT * FROM $tableName ";    
        return $this->executeQuery($query);
    }
    public function selectByLIMITAll($tableName,$id,$offset='') {
        $query = "SELECT * FROM $tableName ORDER BY $id DESC LIMIT 2";  
       // echo $query;exit;
        return $this->executeQuery($query);
    }
    public function selectByLIMITAgents($tableName,$id,$offset='') {
        $role   =   2;// agents only        
        $query = "SELECT  `id`,`first_name`,`last_name`,`email`,`mobile`,`agent_status`,`credit_balance`,`credit_request`,`request_amount`,`image`  FROM $tableName WHERE role = ? ORDER BY $id DESC $offset";  
         $params = [$role];
        return $this->executeQuery($query,$params);
    }
     public function getUsersListDB($tableName,$role,$sortId='',$offset='') {      
         if( $sortId   ==  ''){
              $sortId   =   'id';
         } 
       
        $query = "SELECT  `id`,`first_name`,`last_name`,`image`,`email`,`mobile`,`username`,`status` FROM $tableName WHERE role = ? ORDER BY $sortId DESC $offset";  
         $params = [$role];
        return $this->executeQuery($query,$params);
    }
    public function selectByEmail($tableName, $email) {
        $query = "SELECT * FROM $tableName WHERE email = ?";
        $params = [$email];
        return $this->executeQuery($query, $params);
    }
     public function selectByEmailExist($tableName,$email,$adminid) {         
        $query = "SELECT * FROM $tableName WHERE email = ? AND id != ?";
        $params = [$email,$adminid];
        return $this->executeQuery($query, $params);
    }
     public function selectByToken($tableName, $token) {
        $query = "SELECT * FROM $tableName WHERE tocken = ?";
        $params = [$token];
        return $this->executeQuery($query, $params);
    }
    
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Query execution failed: " . $e->getMessage());
        }
    }
    public function updatePassword($admin_userId, $newPassword) {
        $newPassword = md5($newPassword);
        $query = "UPDATE admin_users SET password = ? WHERE id = ?";
        $params = [$newPassword, $admin_userId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function updateProfile($adminid,$email,$fname,$lname,$phone,$image,$address){
         $query = "UPDATE admin_users SET email = ? ,first_name= ?,last_name= ?,phone = ?,image= ?,address= ? WHERE id = ?";
        $params = [$email, $fname,$lname,$phone,$image,$address,$adminid];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function updateDBCategory($catTitle,$parentId,$catId){
         $query = "UPDATE packages_category SET title = ? ,parent= ?  WHERE id = ?";
        $params = [$catTitle,$parentId,$catId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
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
    public function deleteData($table,$id) {
        try {
             
            $stmt = $this->conn->prepare("DELETE FROM $table WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
     public function updateAgentActivateStatus($agentId,$status){
         //echo $status;exit;
         $query = "UPDATE users SET agent_status = ? WHERE id = ?";
        $params = [$status,$agentId];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //update credit balance agent
public function updateAgentBalance($agentId,$amountnew){
        // echo $agentId;exit;
         $query = "UPDATE users SET credit_balance = ?, credit_request = '0', request_amount = Null WHERE id = ?";
        $params = [$amountnew,$agentId];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    public function selectListBooking($tableName1,$tableName2,$role,$sql='') {
        $query = "SELECT $tableName1.id AS userId,$tableName2.id AS bookingId, CONCAT($tableName1.first_name, ' ', $tableName1.last_name) AS agent_name ,$tableName1.email,$tableName1.credit_balance,$tableName2.air_trip_type,$tableName2.booking_status,$tableName2.dep_date FROM $tableName2 INNER JOIN $tableName1 ON $tableName2.user_id  =   $tableName1.id WHERE $tableName1.role = ? $sql";
        $params = [$role];
        return $this->executeQuery($query, $params);
    }
    

    public function updatesettingpvalue($tableName1,$markupId,$param,$paramval){
       
        $query = "UPDATE $tableName1 SET $param = ? WHERE id = ?";
        $params = [$paramval,$markupId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    
    public function updateMarkupvalue($tableName1,$markupId,$param,$paramval){
       
        $query = "UPDATE $tableName1 SET $param = ? WHERE id = ?";
        $params = [$paramval,$markupId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; //  updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
        public function sqlExec($sql){
            //echo $sql;
        return $this->executeQuery($sql);
    }
    //review status disable
    public function updateReviewActivateStatus($reviewId,$status){
         //echo $status;exit;
         $query = "UPDATE reviews SET status = ? WHERE review_id = ?";
        $params = [$status,$reviewId];
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // status updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //aboutus edit
    public function updateAboutDb($tableName,$id,$title,$imgnewfile,$content){
       
        $query = "UPDATE $tableName SET title = ? ,content = ?,imagefile = ?  WHERE id = ?";
        $params = [$title,$content,$imgnewfile,$id];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; //  updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //update newsletter
     public function updateNewsDb($tableName,$id,$title,$subject,$description){
       
        $query = "UPDATE $tableName SET title = ? ,subject = ?,content = ?  WHERE id = ?";
        $params = [$title,$subject,$description,$id];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; //  updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //homebanner edit
    public function updateHomeBanner($tableName,$homeId,$firstEdit,$secondEdit,$bannerImageEdit){
   
        $query = "UPDATE $tableName SET first_title = ? ,second_title = ?,image = ?  WHERE id = ?";
        $params = [$firstEdit,$secondEdit,$bannerImageEdit,$homeId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; //  updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //airline edit
    public function updateDBAirline($airline_code,$airlinename,$image,$id ){
     $query = "UPDATE airline SET name = ? ,code= ? ,image= ? WHERE id = ?";
        $params = [$airlinename,$airline_code,$image,$id];
   //   print_r($params);
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
    //airport edit
    public function updateDBAirport($airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode,$id){
     $query = "UPDATE airportlocations SET airport_code = ? ,airport_name= ? ,city_code= ? ,city_name= ? ,country_name= ?  ,country_code= ? WHERE id = ?";
        $params = [$airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode,$id];
   //   print_r($params);
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; // Password updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
     //Video edit
    public function updateHomeVideo($tableName,$homeId,$firstEdit,$secondEdit,$bannerImageEdit){
   
        $query = "UPDATE $tableName SET title = ? ,description = ?,video = ?  WHERE id = ?";
        $params = [$firstEdit,$secondEdit,$bannerImageEdit,$homeId];

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true; //  updated successfully
        } catch (PDOException $e) {
            die("Error executing query: " . $e->getMessage());
        }
    }
}
?>