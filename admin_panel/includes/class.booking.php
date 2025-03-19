<?php

/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for BOOKING
   Programmer	::> Soumya
   Date		::> 6-07-2023
   DESCRIPTION::::>>>>
   This code used to manage Flight Bookings
 *****************************************************************************/
include_once "class.DbAction.php";
class Booking extends DbAction
{
   private $db;
   public function __construct()
   {
      $this->db = new DbAction();
   }
   public function listBookings($role)
   {
      $tableName1 = "users";
      $tableName2 = "temp_booking";
      if ($role == 2) {
         $sql = " AND $tableName1.`agent_status` = 'active' ";
      } else {
         $sql =   "";
      }
      $result =   $this->db->selectListBooking($tableName1, $tableName2, $role, $sql);
      return $result;
   }
   public function markupList()
   {
      $tableName1 = "markup_commission";
      $result =   $this->db->selectBystatus($tableName1, 0);
      return $result;
   }
   public function markupList_cancel()
   {
      $tableName1 = "markup_commission";
      $result =   $this->db->selectBystatus($tableName1, 1);
      return $result;
   }

   public function getSettings($id)
   {
      $tableName1 = "settings";
      $result =   $this->db->selectBystatus($tableName1, $id);
      return $result;
   }


   public function getsettingsInfo($markupId)
   {
      $tableName1 = "settings";
      $result =   $this->db->selectById($tableName1, $markupId);
      return $result;
   }
   
   public function getmarkupInfo($markupId)
   {
      $tableName1 = "markup_commission";
      $result =   $this->db->selectById($tableName1, $markupId);
      return $result;
   }


   public function updateMarkup($markupId, $paramval)
   {
      $tableName1 = "markup_commission";
      $param  =   "commission_percentage";
      //$tableName = "packages_category";           
      $result =   $this->db->updateMarkupvalue($tableName1, $markupId, $param, $paramval);
      return $result;
   }
   public function updateIPGSettings($markupId, $paramval) {
      $tableName1 = "settings";
      $param  =   "value";
      $result =   $this->db->updatesettingpvalue($tableName1, $markupId, $param, $paramval);
      return $result;
   }

   public function getBookingInfo($bid)
   {
      $sql    =   "SELECT CONCAT(u.first_name, ' ', u.last_name) AS agentname, u.email,u.dial_code,u.mobile,u.role,b.air_trip_type,b.total_fare,b.stops,b.adult_count,b.child_count,b.infant_Count,b.booking_status,CONCAT(b.contact_first_name, ' ', b.contact_last_name) AS contactname,b.contact_email,b.contact_phonecode,b.contact_number,b.markup,b.booking_date,t.id AS trvId,t.passenger_type,t.title ,CONCAT(t.first_name, ' ', t.last_name) AS traveller_name,t.e_ticket_number,t.extrameal_amount,t.extrameal_description,t.extrabaggage_description,t.extrabaggage_amount,t.free_checkin_baggage,t.free_cabin_baggage,t.basic_fare ,t.tax,t.total_pass_fare FROM temp_booking AS b INNER JOIN users AS u ON u.id = b.user_id LEFT JOIN travellers_details AS t ON b.id = t.flight_booking_id WHERE b.id=" . $bid;

      $result =   $this->db->sqlExec($sql);
      return $result;
   }
   public function getFlightInfo($bid)
   {
      $sql    =   "SELECT f.id AS flightsegId,f.dep_location,f.arrival_location,f.dep_date,f.arrival_date,f.flight_no,f.airline_code,f.cabin_preference FROM flight_segment AS f WHERE f.`booking_id`=" . $bid . " ORDER BY f.dep_date ASC";

      $result =   $this->db->sqlExec($sql);
      return $result;
   }
   public function getAirportInfo($fromloc)
   {
      $sql    =   "SELECT `city_name`,`airport_name` FROM `airportlocations` WHERE `airport_code` LIKE '%" . $fromloc . "%'";
      $result =   $this->db->sqlExec($sql);
      return $result;
   }
   public function getAirlinenfo($flightname)
   {
      $sql    =   "SELECT name FROM airline WHERE code LIKE '%" . $flightname . "%'";
      $result =   $this->db->sqlExec($sql);
      return $result;
   }
}
