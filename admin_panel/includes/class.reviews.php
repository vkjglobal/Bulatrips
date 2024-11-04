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
class Reviews extends DbAction{
	public function __construct(){
       $this->db = new DbAction();
	}
	public function getDashboardReviews($offset=''){
        $tableName = "reviews";        
		$id			=	"review_id";	
		$result = $this->db->selectByLIMITAll($tableName,$id,$offset='');        
		return $result;
    }
		public function getListReviews(){
        $tableName = "reviews";        
		$id			=	"review_id";	
		$result = $this->db->selectTravellers($tableName);        
		return $result;
    }
				
	}
?>