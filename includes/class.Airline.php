<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for airline
   Programmer	::> Arya
   Date		::> 24-07-2023
   DESCRIPTION::::>>>>
   This code used to mdisplay the airline details in client side
*****************************************************************************/
// class.Airport.php
include_once('class.MyConnection.php');

class Airline extends MyConnection {
    public function getAirlineDetails($code) {
        $result = $this->getAirlinebyCode($code);
        return $result;
    }
}
