<?php
/*******************
Project Name	::> Bulatrips
   Module 		::> Booking Cron on pending statuses from booking api
   Programmer	::> Soumya
   Date			::> 26.5.2024
   
   DESCRIPTION::::>>>>
   Booking Cron on pending statuses from booking api


********************/
//chdir('/home/sites_web/client/newdesign/crm/');

include_once('../includes/class.BookingCron.php');
  $objBookCron     =   new BookingCron();
  //=================log write for book API ======
                   
                   //$logRes =   print_r($responseData, true);
                 // $logReQ =   print_r($requestData, true);
                 $logRes =   "Successfully started";
                 $logReQ =   "Successfully finished";
                    $objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','bookingCron.txt');
                    $objBookCron->_writeLog('Request Received\n'.$logReQ,'bookingCron.txt');
                          //  $objBookCron->_writeLog('REsponse Received for MF:\n'.$responseData['Data']['UniqueID']. 'OR USERID='.$userId,'bookingCron.txt');
                            //  $objBookCron->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'bookingCron.txt');
                            //  $objBookCron->_writeLog('Booking ID is '.$bookingID,'bookingCron.txt');
                     

                $objBookCron->_writeLog('REsponse Received\n'.$logRes,'bookingCron.txt');   
              echo "Log updated successfully";exit; 
    //============ END log write for book API ==========
/*
 include_once('includes/class.Booking.php');
  include_once('includes/class.cancel.php');
  $objCancel     =   new Cancel();//for log write fun only
 $booking = new Booking($conn);

    // Subscribe the user and get the result message
    $resultBooking = $booking->getBookCronIDs();
    
      echo "<pre/>";print_r($resultBooking);exit;
    foreach($resultBooking as $resultBookingdata){
        // print_r($resultBookingdata);
        $userId =    $resultBookingdata['user_id'];
        $bookingID  =   $resultBookingdata['id'];
        if(isset($resultBookingdata['mf_reference'])){
            $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1.1/TripDetails/{MFRef}';
            $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
        
            // Set the MFRef value for the endpoint
            $mfRef = $resultBookingdata['mf_reference'];
            $apiEndpoint = str_replace('{MFRef}', $mfRef, $apiEndpoint);
        
            // Send the API request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $bearerToken
            ));
        
            $response = curl_exec($ch);
            curl_close($ch);
        
            // Process the API response
            if ($response === false) {
                // Error handling
                echo 'Error: ' . curl_error($ch);
            } else {
                // Process the response data
                $responseData = json_decode($response, true);
                // Handle the response data as needed
            }
            // Handle the API response
        
            if ($response) {
                $responseData = json_decode($response, true);
            }
                  //=================log write for Cron with Tripdetails API ======
                    $logRes =   print_r($responseData, true);
                 // $logReQ =   print_r($responseData, true);
                    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','TripCron.txt');
                    //$objCancel->_writeLog('Request Received\n'.$logReQ,'TripCron.txt');
                            $objCancel->_writeLog('REsponse Received for MF:\n'.$mfRef. 'OR USERID='.$userId,'TripCron.txt');
                             // $objCancel->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'TripCron.txt');
                              $objCancel->_writeLog('Booking ID is '.$bookingID,'TripCron.txt');
                     

                $objCancel->_writeLog('REsponse Received\n'.$logRes,'TripCron.txt');


                    //============ END log write for  API ==========
           
            $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
            $itinerariesDetail = $tripDetails['Itineraries'][0]['ItineraryInfo']['ReservationItems'];
            $passengerDetail =  $tripDetails['PassengerInfos'];
            //db updates===========
           
            $bookSts    =   $tripDetails['BookingStatus'];
           




            //================

            //if($tripDetails['TicketStatus'] == 'Ticketed'){
               
                
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus,ticket_status = :ticketStatus,ticket_time_limit = :ticketTimeLimit,booking_date = :bookingDate,void_window =:voidWindow WHERE id = :id');

                // Set the values
                if(isset($bookSts)){
                    $bookingStatus = $tripDetails['BookingStatus'];
                }
                $ticketTimeLimit = $tripDetails['TicketingTimeLimit'];
                $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
                $ticketStatus = $tripDetails['TicketStatus'];
                if(isset($tripDetails['VoidingWindow'])){
                    $voidWindow = $tripDetails['VoidingWindow'];

                }else{
                    $voidWindow="";
                }
                //$markup = $tripDetails['ClientMarkup']['Amount'];
               
                

                // Bind the parameters
                $stmtupdate->bindParam(':ticketTimeLimit', $ticketTimeLimit);
                $stmtupdate->bindParam(':bookingStatus', $bookingStatus);
                $stmtupdate->bindParam(':bookingDate', $bookingDate);
                $stmtupdate->bindParam(':ticketStatus', $ticketStatus);
                $stmtupdate->bindParam(':voidWindow', $voidWindow);
               
                $stmtupdate->bindParam(':id', $bookingID);



                // Execute the query
                $stmtupdate->execute();
               

                $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus,e_ticket_number=:ticketNumber WHERE flight_booking_id  = :bookingId and passport_number=:PassportNumber');
                foreach ($passengerDetail as $passengerInfo) {
                    if(isset($passengerInfo['ETickets'][0]['ETicketNumber'])){
                        $ticketNumber = $passengerInfo['ETickets'][0]['ETicketNumber'];
                        $ticketStatusType   =   $passengerInfo['ETickets'][0]['ETicketType'];
                    }else{
                        $ticketNumber="";
                        $ticketStatusType ="";
                    }
                    $PassportNumber = $passengerInfo['Passenger']['PassportNumber'];
                
                    // Bind the parameters and execute the update statement
                    $stmtupdatetravellers->bindParam(':ticketNumber', $ticketNumber, PDO::PARAM_STR);
                    $stmtupdatetravellers->bindParam(':PassportNumber', $PassportNumber, PDO::PARAM_STR);
                    $stmtupdatetravellers->bindParam(':ticketStatus', $ticketStatusType);
                    $stmtupdatetravellers->bindParam(':bookingId', $bookingID);
                    $stmtupdatetravellers->execute();
                }
                $stmtupdatetravellers->execute();
                // print_r( $resultBookingdata['id']);die();
              
           // }
            


            // $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus WHERE flight_booking_id  = :bookingId');
          
        }
    }
    */
?>