<?php
/**************************************************************************** 
   Project Name	::> TravelSite
   Module 	::> Class for User details fetch
   Programmer	::> Arya
   Date		::> 24-07-2023
   DESCRIPTION::::>>>>
   This code used to mdisplay the user details in client side
*****************************************************************************/
// class.Airport.php
include_once('class.MyConnection.php');

class Users extends MyConnection {
    public function getUserDetails($user_id) {
        $result = $this->getUsersbyId($user_id);
        return $result;
    }
}
