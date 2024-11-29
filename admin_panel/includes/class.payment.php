<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for Reviews
   Programmer	::> Nimmi
   Date		::> 06-06-2024
   DESCRIPTION::::>>>>
   This code used to manage payment data and all functions of nimmi
*****************************************************************************/
include_once "class.Dbconnect.php";
class Payment extends Dbconnect{
    private $db;
	public function __construct(){
       $this->db = new Dbconnect();
				
	}
    public function agent_payment($role){
        $sql ="SELECT p.*,u.* FROM payment AS p JOIN users AS u ON u.id = p.user_id WHERE u.role = $role";
        $result = $this->db->selectCMSDB($sql) ;
        return $result;
    }
    public function user_payment($role){
        $sql ="SELECT t.*,u.* FROM temp_booking AS t JOIN users AS u ON u.id = t.user_id WHERE u.role = $role ORDER BY t.id DESC";
        $result = $this->db->selectCMSDB($sql) ;
        return $result;
    }
    public function canceled_flight_list(){
        $sql = 'SELECT c.*,u.* FROM cancel_booking as c JOIN users AS u ON u.id = c.user_agent_id ORDER BY c.id ASC';
        $result = $this->db->selectCMSDB($sql) ;
        return $result;
    }
}