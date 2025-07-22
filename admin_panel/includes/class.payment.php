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
        $sql ="SELECT p.*, t.dep_location, t.arrival_location, t.mf_reference, t.contact_first_name, t.contact_last_name, t.contact_email, t.total_paid FROM payment_user AS p LEFT JOIN temp_booking AS t ON t.id = p.booking_id ORDER BY p.id DESC";
        $result = $this->db->selectCMSDB($sql) ;
        return $result;
    }
    public function canceled_flight_list(){
        $sql = 'SELECT c.*,u.* FROM cancel_booking as c JOIN users AS u ON u.id = c.user_agent_id ORDER BY c.id ASC';
        $result = $this->db->selectCMSDB($sql) ;
        return $result;
    }
}