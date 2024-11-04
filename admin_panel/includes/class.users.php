<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for Reviews
   Programmer	::> Soumya
   Date		::> 29-06-2023
   DESCRIPTION::::>>>>
   This code used to manage user reviews
*****************************************************************************/
include_once "class.DbAction.php";
class Users extends DbAction{
	public function __construct(){
       $this->db = new DbAction();
				
	}
	public function getUsersList(){
        $tableName	= "users";  
		$role		=	1;
		$result		= $this->db->getUsersListDB($tableName,$role,$sortId='',$offset='');        
		return $result;
    }
	public function getUserDetails($userId){
        $tableName	= "users";  
		$result		= $this->db->selectById($tableName,$userId);        
		return $result;
    }
	public function DeleteUserDetails($userId){
		 $tableName	= "users";  
		$result		= $this->db->deleteData($tableName,$userId);        
		return $result;
	}


}

?>