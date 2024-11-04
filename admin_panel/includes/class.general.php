<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for General
   Programmer	::> Soumya
   Date		::> 18-7-2023
   DESCRIPTION::::>>>>
   This code used to manage general
*****************************************************************************/
include_once "class.DbAction.php";
class General extends DbAction{
	public function __construct(){
       $this->db = new DbAction();
				
	}
    public function addAirports($airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode){
		 $tableName = "airportlocations";    
        $params = ['airport_code'=>$airport_code, 'airport_name'=>$airportname, 'city_code'=>$citycode, 'city_name'=>$cityname, 'country_name'=>$countryname, 'country_code'=>$countrycode];
        //print_r($params);exit;
        $result =   $this->db->insertInto($tableName, $params) ;
       return $result;		
       
    }
       public function insAirline($airline_code,$airlinename,$image){
		 $tableName = "airline";    
        $params = ['name'=>$airlinename, 'code'=>$airline_code, 'image'=>$image];
        //print_r($params);exit;
        $result =   $this->db->insertInto($tableName, $params) ;
       return $result;		
       
    }
     public function getAirportLocs(){
		$tableName = "airportlocations";           
        $result =   $this->db->selectTravellers($tableName);
       return $result;		
       
    }
       public function getAirlines(){
		$tableName = "airline";           
        $result =   $this->db->selectTravellers($tableName);
       return $result;		
       
    }
     public function getAirlineData($id){

		$tableName = "airline";           
        $result =   $this->db->selectById($tableName, $id);
       return $result;		
       
    }
     public function getAirportData($id){

		$tableName = "airportlocations";           
        $result =   $this->db->selectById($tableName, $id);
       return $result;		
       
    }
     //update Airports
    public function updateAirports($airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode,$id){
       
		$tableName = "airportlocations";           
        $result =   $this->db->updateDBAirport($airport_code,$airportname,$citycode,$cityname,$countryname,$countrycode,$id);
       return $result;		
       
    }
   
     public function updateAirline($airline_code,$airlinename,$image,$id){
       
		$tableName = "airline";           
        $result =   $this->db->updateDBAirline($airline_code,$airlinename,$image,$id );
       return $result;		
       
    }
    	public function DelAirlinees($id){
		 $tableName	= "airline";  
		$result		= $this->db->deleteData($tableName,$id);        
		return $result;
	}
    public function DelAirport($id){
		 $tableName	= "airportlocations";  
		$result		= $this->db->deleteData($tableName,$id);        
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