<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for Reviews
   Programmer	::> Soumya
   Date		::> 6-07-2023
   DESCRIPTION::::>>>>
   This code used to manage packages
*****************************************************************************/
include_once "class.DbAction.php";
class Packages extends DbAction{
    private $db;
	public function __construct(){
       $this->db = new DbAction();
				
	}
    public function addCategory($title,$parentCat){
		 $tableName = "packages_category";    
        $params = ['title'=>$title, 'parent'=>$parentCat, 'status'=> 'active'];
        //print_r($params);exit;
        $result =   $this->db->insertInto($tableName, $params) ;
       return $result;		
       
    }
     public function listallCategory($searchsql=''){
		$tableName = "packages_category";           
        $result =   $this->db->selectListPagination($tableName, $searchsql) ;
       return $result;		
       
    }
     public function getCategoryinfo($catId){
		$tableName = "packages_category";           
        $result =   $this->db->selectById($tableName, $catId);
       return $result;		
       
    }
    public function updateCategory($catTitle,$parentId,$catId ){
		//$tableName = "packages_category";           
        $result =   $this->db->updateDBCategory($catTitle,$parentId,$catId );
       return $result;		
       
    }
    	public function DelCategory($catId){
		 $tableName	= "packages_category";  
		$result		= $this->db->deleteData($tableName,$catId);        
		return $result;
	}
     public function sanitizeInput($input) {
    // Trim whitespace from the beginning and end of the input
    $input = trim($input);

    // Remove backslashes
    $input = stripslashes($input);

    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES);
    return $input;
    }
    
   public function validateInputStrings($input) {
    // Perform your validation rules on the input
    // Return true if the input is valid, otherwise return false
    //  validation for a name field
    if($input == ""){
        return false;
    }
    if (strlen($input) < 2 || strlen($input) > 50) {
        // Invalid name length
        return false;
    }
    return true;
   }
    

}

?>