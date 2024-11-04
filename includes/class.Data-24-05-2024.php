<?php
/**************************************************************************** 
   Project Name	::> Bulatrips
   Module 	::> Class for Data management
   Programmer	::> Nimmi E V
   Date		::> 18-12-2023,13-05-2024
   DESCRIPTION::::>>>>
   This is a Class code used to manage all nimmi data
*****************************************************************************/
include_once "class.DBAction.php";

class Data extends DBAction{
    private $conn;
 
    public function __construct()
    {
       $this->conn = new DBAction();
				
	}
    public function selectDatabyEmail($tableName,$email)
    {
        return $this->conn->selectbyEmail($tableName,$email);
    }
    public function insertPassword_ResetToken($id, $token)
    {
        // Calculate the expiration time for the token (e.g., 1 hour from the current time)
        $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));   
        
        // current time
        $time = date('Y-m-d H:i:s');
        // Store the token and its expiration time in the database  
        $tableName = "forget_reset_tocken";    
        $params = ['user_id'=>$id, 'tocken'=>$token, 'expiry_time'=>$expiryTime, 'created_at'=>$time];
        //print_r($params);exit;
        
        $result =   $this->conn->insertInto($tableName, $params) ;
        return $result;
    }
    public function isTokenValid($token)
    {
        // Retrieve the token and its expiration time from the database	
        $tableName = "forget_reset_tocken";	
        $result = $this->conn->selectByToken($tableName, $token);	
        //return $result;  	
        //echo count($result);exit;	
        // Check if the token exists in the database	
        if (count($result) > 0) {	
            $tokenData = $result[0];	
            $expirationTime = strtotime($tokenData['expiry_time']);	
            $currentTime = time();	
            // Check if the token has expired	
            if ($currentTime <= $expirationTime) {	
                return $tokenData['user_id']; // Token is valid and has not expired	
            }else{	
                return false; // Token is invalid or has expired	
            }	
        }	
        return false; // Token is invalid or has expired	
    }
    public function updateDataById($userID, $new_pw)
    {
        $result = $this->conn->updatePassword($userID, $new_pw);	
        return $result;
    }
    public function add_review($id, $title,$description,$name,$image,$rating)
    {
        $tableName = 'reviews';
        $params = ['user_id'=>$id, 'title'=>$title, 'description'=>$description, 'author'=>$name, 'image'=>$image, 'rating'=>$rating, 'status'=>'1'];
        $result =   $this->conn->insertInto($tableName, $params) ;
        return $result;
    }
    public function select_author($id)
    {
        $tableName = 'users';
        return $this->conn->selectbyId($tableName,$id);
    }
    public function select_review($id)
    {
        $tableName = 'reviews';
        return $this->conn->selectreview($tableName,$id);
    }
    public function update_review($id, $title, $description, $name, $file_name, $rating)
    {
        $tableName = 'reviews';
        return $this->conn->Updatereview($tableName,$id, $title, $description, $name, $file_name, $rating);
    }
    public function delete_review($id)
    {
        $tableName = 'reviews';
        return $this->conn->deleteReview($tableName,$id);
    }
    public function get_about()
    {
        $tableName = 'about';
        return $this->conn->selectTable($tableName);
    }
    public function get_banner()
    {
        $tableName = 'home_banner';
        return $this->conn->selectTable($tableName);
    }
    public function get_video()
    {
        $tableName = 'videos';
        return $this->conn->selectTable($tableName);
    }
}
?>