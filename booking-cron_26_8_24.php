<?php
 include_once('includes/class.Booking.php');
 $booking = new Booking($conn);

    // Subscribe the user and get the result message
    $resultBooking = $booking->getBookingDetailsbyTicketstatus();
    //  print_r($resultBooking);
    foreach($resultBooking as $resultBookingdata){
        // print_r($resultBookingdata);
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
        
           
            $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
            $itinerariesDetail = $tripDetails['Itineraries'][0]['ItineraryInfo']['ReservationItems'];
            $passengerDetail =  $tripDetails['PassengerInfos'];

            if($tripDetails['TicketStatus'] == 'Ticketed'){
               
                
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_status = :bookingStatus,ticket_status = :ticketStatus,ticket_time_limit = :ticketTimeLimit,booking_date = :bookingDate,void_window =:voidWindow,markup =:markup WHERE id = :id');

                // Set the values
                if(isset($tripDetails['BookingStatus'])){
                    $bookingStatus = $tripDetails['BookingStatus'];
                }else{
                    $bookingStatus = "Booked";

                }
                $ticketTimeLimit = $tripDetails['TicketingTimeLimit'];
                $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
                $ticketStatus = $tripDetails['TicketStatus'];
                if(isset($tripDetails['VoidingWindow'])){
                    $voidWindow = $tripDetails['VoidingWindow'];

                }else{
                    $voidWindow="";
                }
                $markup = $tripDetails['ClientMarkup']['Amount'];
                $id = $resultBookingdata['id'];

                // Bind the parameters
                $stmtupdate->bindParam(':ticketTimeLimit', $ticketTimeLimit);
                $stmtupdate->bindParam(':bookingStatus', $bookingStatus);
                $stmtupdate->bindParam(':bookingDate', $bookingDate);
                $stmtupdate->bindParam(':ticketStatus', $ticketStatus);
                $stmtupdate->bindParam(':voidWindow', $voidWindow);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':id', $id);



                // Execute the query
                $stmtupdate->execute();
               

                $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus,e_ticket_number=:ticketNumber WHERE flight_booking_id  = :bookingId and passport_number=:PassportNumber');
                foreach ($passengerDetail as $passengerInfo) {
                    if(isset($passengerInfo['ETickets'][0]['ETicketNumber'])){
                        $ticketNumber = $passengerInfo['ETickets'][0]['ETicketNumber'];
                    }else{
                        $ticketNumber="";
                    }
                    $PassportNumber = $passengerInfo['Passenger']['PassportNumber'];
                
                    // Bind the parameters and execute the update statement
                    $stmtupdatetravellers->bindParam(':ticketNumber', $ticketNumber, PDO::PARAM_STR);
                    $stmtupdatetravellers->bindParam(':PassportNumber', $PassportNumber, PDO::PARAM_STR);
                    $stmtupdatetravellers->bindParam(':ticketStatus', $ticketStatus);
                    $stmtupdatetravellers->bindParam(':bookingId', $resultBookingdata['id']);
                    $stmtupdatetravellers->execute();
                }
                $stmtupdatetravellers->execute();
                // print_r( $resultBookingdata['id']);die();
              
            }
            


            // $stmtupdatetravellers = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus WHERE flight_booking_id  = :bookingId');
          
        }
    }
?>