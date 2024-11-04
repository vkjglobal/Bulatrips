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
class Agents extends DbAction{
    private $db;
	public function __construct(){
       $this->db = new DbAction();
				
	}
    public function getDashboardAgents($offset=''){
        $tableName	= "users";        
		$id			=	"id";			
		$result		= $this->db->selectByLIMITAgents($tableName,$id,$offset);        
		return $result;
    }
	public function getAgentDetails($id){
        $tableName	= "users";      				
		$result		= $this->db->selectById($tableName,$id);        
		return $result;
    }
	public function getDashboardTravellers(){
        $tableName	= "travellers_details";      				
		$result		= $this->db->selectTravellers($tableName);        
		return $result;
    }
	public function getDashboardTotalBooking(){
        $tableName	= "temp_booking";      				
		$result		= $this->db->countTableRows($tableName);        
		return $result;
    }


}

?>