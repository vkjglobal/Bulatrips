<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for Markup fetch
   Programmer	::> Arya
   Date		::> 24-07-2023
   DESCRIPTION::::>>>>
   This code used to mdisplay the airport details in client side
*****************************************************************************/
// class.Airport.php
include_once('class.MyConnection.php');

class Markup extends MyConnection {
    public function getMarkupDetails($role) {
        $result = $this->getMarkbyUserid($role);
        return $result;
    }
}
