<?php
/*******************
Project Name	::> Bulatrips
   Module 		::> Booking Cron on pending statuses from booking api
   Programmer	::> Soumya
   Date			::> 26.5.2024
   
   DESCRIPTION::::>>>>
   Booking Cron on pending statuses from booking api


********************/
//chdir('/home/bulatrips/public_html/CronJob/');

include_once __DIR__ . '/../includes/class.BookingCron.php';
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../mail_send.php';

 $objBookCron     =   new BookingCron();
  $adminToemail  =   "no-reply@bulatrips.com";

  //=================log write for book API ======
                   
                   //$logRes =   print_r($responseData, true);
                 // $logReQ =   print_r($requestData, true);
                 $logReQ =   "Successfully started";
   
                    $objBookCron->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','bookingCron.txt');
                    $objBookCron->_writeLog('Request Received\n'.$logReQ,'bookingCron.txt');
                            
             // echo "Log updated successfully";exit; 
    //============ END log write for book API ==========

    // Subscribe the user and get the result message
    $resultBooking = $objBookCron->getBookCronIDs();

    //  echo "<pre/>";print_r($resultBooking);exit;
    foreach($resultBooking as $resultBookingdata){
        $userId =    $resultBookingdata['user_id'];
        $bookingID  =   $resultBookingdata['id'];
        $bookingStatus  =   $resultBookingdata['booking_status'];
        $amountToDebit  =   $resultBookingdata['total_paid'];
        if(isset($resultBookingdata['mf_reference'])){
           // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1.1/TripDetails/{MFRef}';
           // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
            $endpoint   =   'v1.1/TripDetails/{MFRef}';
             $apiEndpoint = APIENDPOINT.$endpoint;
             $bearerToken   =   BEARER;


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
                 
                            $objBookCron->_writeLog('REsponse Received for MF:\n'.$mfRef. 'OR USERID='.$userId,'bookingCron.txt');
                             // $objBookCron->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'bookingCron.txt');
                              $objBookCron->_writeLog('Booking ID is '.$bookingID,'bookingCron.txt');
                             
                     

                $objBookCron->_writeLog('REsponse Received\n'.$logRes,'bookingCron.txt');


                    //============ END log write for  API ==========
           
            $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
            $itinerariesDetail = $tripDetails['Itineraries'][0]['ItineraryInfo']['ReservationItems'];
            $passengerDetail =  $tripDetails['PassengerInfos'];
            //db updates===========
           
            $bookSts    =   $tripDetails['BookingStatus']; //for webfare type this bookstatus not see on response
              $objBookCron->_writeLog('Booking status is '.$bookSts,'bookingCron.txt');
           //$bookSts = 'NotBooked';

           
            $userDetails = array(
                "email" => $resultBookingdata['contact_email'],
                "first_name" => $resultBookingdata['contact_first_name'],
                "last_name" => $resultBookingdata['contact_last_name']
            );
            $role="user";

            //================

            //if($tripDetails['TicketStatus'] == 'Ticketed'){
               
                
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus,ticket_status = :ticketStatus,ticket_time_limit = :ticketTimeLimit,booking_date = :bookingDate,void_window =:voidWindow WHERE id = :id');

                // Set the values
                if(isset($bookSts)){
                    $bookingStatus = $tripDetails['BookingStatus'];
                }

                $ticketTimeLimit = $tripDetails['TicketingTimeLimit'];
                $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
                $ticketStatus = @$tripDetails['TicketStatus'];
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
                    }elseif(!empty($ticketStatus)){
                        $ticketStatusType   = $ticketStatus;
                         $ticketNumber="";
                    }
                    else{
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
           //============
           if(trim($ticketStatusType) == 'Ticketed'){
           //if(trim($ticketStatusType) == 'TktInProcess'){
               //==========email to agent about Ticketed status of Booking======
               
                     $subject = "Your Booking on Bulatrips get Confirmed now";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello '. $name .',</p>
                                                <p>Your  Account on Bulatrips had  a booking  for booking id:'.base64_encode($bookingID).'.This get Ticketed now and you can login to site for further details</p>';
                                            $messageData =   $objBookCron->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                         // $email = "no-reply@bulatrips.com"; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);

           }
           elseif(trim($bookingStatus) == 'NotBooked'){
           
               //==========email to agent/user about Failure status of Booking======
              
                     $subject = "Your Booking on Bulatrips Failed as per Airline Reply";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello '. $name .',</p>
                                                <p>Your  Account on Bulatrips had  a booking attempt for booking id:'.base64_encode($bookingID).'.This get failed now and your payment will repay back in 7 days </p>';
                                            $messageData =   $objBookCron->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                         // $email = "no-reply@bulatrips.com"; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);

                        //==============
                        //====================email send code to admin regrding booking failure and amount need to repay======

                    //  include_once('mail_send.php');

                      $subject_Admin = "Bulatrips agent/User Booking attempt Failure and Balance need to credit Info";                  

                          
                            $content_admin    =   '<p>Hello,</p>
                                                <p>This account for , '. $role.":". $name .', with email '.$userDetails['email'].' had a  transaction for booking id:'.$bookingID.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed as per Airline status,Please credit back the same amount </p>';
                                            $messageData =   $objBookCron->getEmailContent($content_admin);
                     // print_r($messageData);exit;
                         
                         // $email = $adminToemail; //Need ADMIN email here
         
                        $contacts_Admin= sendMail($adminToemail,$subject_Admin, $messageData,$headers);



                    //=====================email ends for admin=======
           }

           //========
            
                          $logRes1 =   "Successfully executed";
                           $objBookCron->_writeLog('Request Received\n'.$logRes1,'bookingCron.txt');

            // $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus WHERE flight_booking_id  = :bookingId');
            
          
        }
    }
    
?>