<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for airportlocation
   Programmer	::> Arya
   Date		::> 24-07-2023
   DESCRIPTION::::>>>>
   This code used to mdisplay the airport details in client side
*****************************************************************************/
// class.Airport.php
include_once('class.MyConnection.php');

class Airport extends MyConnection {
    public function getAirportDetails($code) {
        $result = $this->getAirportbyCode($code);
        return $result;
    }
}
