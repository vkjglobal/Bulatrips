<?php
//agent booking call 
  include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
include_once('includes/class.Markup.php');
include_once('includes/class.Users.php');
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

   // echo json_encode($_POST);

   // exit;

    // Assuming your logic here processes the POST data
    // and you want to return a JSON response.
    //*******************************************************
     // Retrieve JSON encoded data from hidden input
    $data = json_decode($_POST['data'], true);

    // Extracting values from the decoded data
    $contactFirstName = $data['contactfirstname'] ?? '';
    $contactLastName = $data['contactlastname'] ?? '';
    $contactPhone = $data['contactnumber'] ?? '';
    $contactEmail = $data['contactemail'] ?? '';
    $contactPhonecode = $data['contactcountry'] ?? '';
    $contactPostcode = $data['contactpostcode'] ?? '';
    $adultCount = $data['adultCount'] ?? '';
    $childCount = $data['childCount'] ?? '';
    $infantCount = $data['infantCount'] ?? '';
     $pricedItineraries = $data['pricedItineraries'];
    $userId = $_SESSION['user_id'] ?? '';
    //===============
    $extra_service_total = $_POST['extraServiceAmount'] ?? ''; //$extraServiceAmount from mybooking34.php page
    $total_paid = $_POST['Totalamount'] ?? ''; //the amount totally paid prior to booking
      
    $adminToemail   =   "no-reply@bulatrips.com";
    //==============
        // Validate the form data
        $errors = []; // Initialize an array to store validation errors
        // $pricedItineraries= $_POST['pricedItineraries'];
        $pricedItineraries = json_decode($data['pricedItineraries'], true); 
        $sqlbooking = "INSERT INTO temp_booking (dep_location, arrival_location, dep_date,airline,air_trip_type,adult_count,child_count,infant_count,
fare_source_code,ticket_type,contact_first_name,contact_last_name,contact_email,contact_number,user_id,total_fare,stops,fare_type,
contact_phonecode,contact_postcode,markup,total_paid,extra_service_total) 
VALUES (:dep_location, :arrival_location, :dep_date,:airline,:air_trip_type,:adult_count,:child_count,:infant_count,:fare_source_code,:ticket_type,
:contact_first_name,:contact_last_name,:contact_email,:contact_number,:user_id,:total_fare,:stops,:fareType,:contactPhonecode,:contactPostcode,:markup,:total_paid,:extra_service_total)";
$stmtboking = $conn->prepare($sqlbooking);
foreach ($pricedItineraries as $pricedItinerary) {
    $originDestinations = $pricedItinerary['OriginDestinationOptions'];
    $stops=0;
    $stops = count($originDestinations[0]['FlightSegments']);
    $stops-=1;
    $originData = $originDestinations[0]['FlightSegments'][0]['DepartureAirportLocationCode'];
    $segmentCount= count($originDestinations[0]['FlightSegments']);
    $segmentCount-=1;
    $destinationData = $originDestinations[0]['FlightSegments'][ $segmentCount]['ArrivalAirportLocationCode'];
    $datetime = $originDestinations[0]['FlightSegments'][0]['DepartureDateTime'];
    $direction=$pricedItinerary['DirectionInd'];
    $airline=$pricedItinerary['ValidatingAirlineCode'];
    $faresourcecode=$pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'];
    $fareType = $pricedItinerary['AirItineraryPricingInfo']['FareType'];
    $ticketType =$pricedItinerary['TicketType'];
    $totalFare = $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
    //-----------
      $objUser = new Users();
    $userDetails = $objUser->getUserDetails($userId);
    $objMarkup = new Markup();
    $markupDetails = $objMarkup->getMarkupDetails($userDetails['role']);
    $markup =  ($markupDetails['commission_percentage'] / 100) * $totalFare;

   
   
    //-----------
    if (empty($originData) || empty($destinationData) || empty($datetime) || empty($direction) || empty($airline) || empty($faresourcecode) || empty($fareType) || empty($totalFare)) {
        // Handle the validation error (e.g., show an error message or redirect back with an error)
        // For example, you can redirect back to the form page with an error message
        $errorMessage = 'Flight details not get ';

        $errors=$errorMessage;
        $response = array(
           
            'errors'=> $errors
            // 'value3' => $value3
          );
          echo json_encode($response);
         exit();
    }
    // foreach ($originDestinations as $originDestination) {

                    // list($date, $time) = explode("T", $datetime);

        // Bind the parameters to the statement
        $stmtboking->bindValue(':dep_location', $originData, PDO::PARAM_STR);
        $stmtboking->bindValue(':arrival_location', $destinationData, PDO::PARAM_STR);
        $stmtboking->bindValue(':dep_date', $datetime, PDO::PARAM_STR);
        $stmtboking->bindValue(':airline', $airline, PDO::PARAM_STR);
        $stmtboking->bindValue(':air_trip_type', $direction, PDO::PARAM_STR);
        $stmtboking->bindValue(':adult_count', $adultCount, PDO::PARAM_INT);
        $stmtboking->bindValue(':child_count', $childCount, PDO::PARAM_INT);
        $stmtboking->bindValue(':infant_count', $infantCount, PDO::PARAM_INT);
        $stmtboking->bindValue(':fare_source_code', $faresourcecode, PDO::PARAM_STR);
        $stmtboking->bindValue(':ticket_type', $ticketType, PDO::PARAM_STR);

        $stmtboking->bindValue(':contact_first_name', $contactFirstName, PDO::PARAM_STR);
        $stmtboking->bindValue(':contact_last_name', $contactLastName, PDO::PARAM_STR);
        $stmtboking->bindValue(':contact_email', $contactEmail, PDO::PARAM_STR);
        $stmtboking->bindValue(':contact_number', $contactPhone, PDO::PARAM_STR);
        $stmtboking->bindValue(':contactPhonecode', $contactPhonecode, PDO::PARAM_STR);
        $stmtboking->bindValue(':contactPostcode', $contactPostcode, PDO::PARAM_STR);
        // $stmtboking->bindValue(':contact_country', $contactCountry, PDO::PARAM_STR);
        $stmtboking->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmtboking->bindValue(':total_fare', $totalFare, PDO::PARAM_STR);
        $stmtboking->bindValue(':fareType', $fareType, PDO::PARAM_STR);
        $stmtboking->bindValue(':stops', $stops, PDO::PARAM_INT);

         $stmtboking->bindValue(':markup', $markup, PDO::PARAM_STR);
        $stmtboking->bindValue(':total_paid', $total_paid, PDO::PARAM_STR); //amount paid totally by agent totalfare from api+markup+extra service amnt
        $stmtboking->bindValue(':extra_service_total', $extra_service_total, PDO::PARAM_STR); // extra services amount totla
        // Execute the statement
        $stmtboking->execute();
         $tempBookingId = $conn->lastInsertId();
         //debit booking amount from agent's credit balance
            $cur_balance    =   $userDetails['credit_balance'];
            $amountToDebit  =   $total_paid;
            //======================balance check once more after adding extra services price==========
                if($total_paid > $cur_balance)
                {
                     $errorMessage = 'Your balance is insufficient. Please add funds to your account and try booking the flight again. ';

                    $errors=$errorMessage;
                    $response = array(
           
                        'balance'=> $errors
                        // 'value3' => $value3
                      );
                      echo json_encode($response);
                     exit();
                }

            //=================================================================================
            $new_credit_agent   =   $objBook->debitAmount($cur_balance, $amountToDebit); //assuming minimum balance for booking is alerady checked in previous pages
            $agentUpdateBalnace =   $objBook->updateInUserAgentCredit($userId,$new_credit_agent);
    
                //====================email send code to admin regarding credit balance deduction for a booking======

                      include_once('mail_send.php');

                      $subject = "Bulatrips Agent Booking and Credit Balance Debit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' has a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>This accounts earlier balance:'.$cur_balance.' now updated to <strong>$'.$new_credit_agent.'</strong></p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
                    //==========email to agent about debit amount======
                     $subject = "Bulatrips Credit Balance Debit Info for new booking ";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello '. $name .',</p>
                                                <p>Your Agent Account on Bulatrips had  a Debit transaction for booking id:'.base64_encode($tempBookingId).'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>This accounts earlier balance:'.$cur_balance.' now updated to <strong>$'.$new_credit_agent.'</strong></p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                         // $email = "no-reply@bulatrips.com"; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);


                    //====================================email to agent ======
       
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :booking_id');

        $stmtbookingid->execute(array('booking_id' => $tempBookingId));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        
        If( $tempBookingId){

            foreach ($originDestinations as $originDestination) {
                $flightSegments = $originDestination['FlightSegments'];
                foreach ($flightSegments as $index => $flightSegment) {
                    $flightNo=$flightSegment['FlightNumber'];
                    $bookingId = $tempBookingId ;
                    $cabinPreference = $flightSegment['CabinClassText'];
                    $depLocation=$flightSegment['DepartureAirportLocationCode'];
                    $depDate=$flightSegment['DepartureDateTime'];
                    $arrivalLocation=$flightSegment['ArrivalAirportLocationCode'];
                    $arrivalDate=$flightSegment['ArrivalDateTime'];
                    $journeyduration=$flightSegment['JourneyDuration'];
                    $airline=$pricedItinerary['ValidatingAirlineCode'];
                    $eticket=$flightSegment['Eticket'];

                    $sqlsegment = "INSERT INTO flight_segment (booking_id, dep_location,arrival_location,
                    dep_date,arrival_date,flight_no,journey_duration,airline_code,cabin_preference,eticket) 
                    VALUES (:booking_id, :dep_location,:arrival_location,:dep_date,:arrival_date,:flight_no,:journey_duration,
                    :airline_code,:cabin_preference,:eticket)";
                    $stmtsegment = $conn->prepare($sqlsegment);



                $stmtsegment->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
                $stmtsegment->bindValue(':dep_location', $depLocation, PDO::PARAM_STR);
                $stmtsegment->bindValue(':arrival_location', $arrivalLocation, PDO::PARAM_STR);
                $stmtsegment->bindValue(':dep_date', $depDate, PDO::PARAM_STR);
                $stmtsegment->bindValue(':arrival_date', $arrivalDate, PDO::PARAM_STR);
                $stmtsegment->bindValue(':flight_no', $flightNo, PDO::PARAM_STR);
                $stmtsegment->bindValue(':journey_duration', $journeyduration, PDO::PARAM_STR);
                $stmtsegment->bindValue(':airline_code', $airline, PDO::PARAM_STR);
                $stmtsegment->bindValue(':cabin_preference', $cabinPreference, PDO::PARAM_STR);
                $stmtsegment->bindValue(':eticket', $eticket, PDO::PARAM_STR);

                $stmtsegment->execute();
                


                }

            }

            $sqltraveler = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,dob,
            passport_number,passport_expiry_date,title,passenger_type,extrabaggage_id,extrabaggage_description,
            extrabaggage_amount,extrameal_id,extrameal_description,extrameal_amount,gender,issuing_country,
            nationality,extrameal_return_id,extrameal_return_description,extrameal_return_amount,extrabaggage_return_id,
            extrabaggage_return_description,extrabaggage_return_amount) 
            VALUES (:firstName, :lastName, :bookingID,:dob,:passportNo,:passpostExp,:title,:passengerType,
            :baggageId,:baggageDescription,:baggageAmount,:mealId,:mealDescription,:mealAmount,:gender,
            :issuingCountry,:nationality,:mealReturnId,:mealReturnDescription,:mealReturnAmount,:baggageReturnId,
            :baggageReturnDescription,:baggageReturnAmount)";
            // $sqltraveler = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,passport_number,title) 
            // VALUES (:firstName, :lastName, :bookingID,:passportNo,:title)";


           
            // Prepare the statement
            $stmttraveler = $conn->prepare($sqltraveler);

            // Bind parameters and execute the statement for each adult
            for ($i = 1; $i <= $adultCount; $i++) {
                $firstName = $data['firstName' . $i];
                $lastName = $data['lastName' . $i];
                $dob = $data['adultDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $data['passportNo' . $i];
                $passpostExp = $data['pasprtExp' . $i];
                $bookingID= $bookingData['id'];
                $title = $data['sirLable' . $i];
                $gender = $data['gender' . $i];
                $issuingCountry = $data['issuingCountry' . $i];
                $passengerType ="ADT";
                $nationality = $data['nationality' . $i];

                if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                    // Handle the validation error (e.g., show an error message or redirect back with an error)
                    // For example, you can redirect back to the form page with an error message
                    // header('Location: form.php?error=Please fill in all mandatory fields');
                    $errorMessage = 'Please fill in all mandatory fields for adult';

                    header('Content-Type: application/json');
                    $errors=$errorMessage;
                    $response = array(
           
                        'errors'=> $errors
                        // 'value3' => $value3
                      );
                      echo json_encode($response);
                     exit();
                   
                }

                


                if (isset($data['baggageService' . $i])) {
                    $baggageServiceData = explode('/', $data['baggageService' . $i]);
                    if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                        $baggageID = $baggageServiceData[0];
                        $baggageDescription = $baggageServiceData[1];
                        $baggageAmount = $baggageServiceData[2];
                    } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                    }
                } else {
                    $baggageID = "";
                    $baggageDescription = "";
                    $baggageAmount = "";
                }
                

                if (isset($data['mealService' . $i])) {
                $mealServiceData = explode('/', $data['mealService' . $i]);
                if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                    $mealId = $mealServiceData[0];
                    $mealDescription = $mealServiceData[1];
                    $mealAmount = $mealServiceData[2];
                    } else{
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount ="";
                    }
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount ="";
                }

                //return extra services
                if (isset($data['baggageServiceReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $data['baggageServiceReturn' . $i]);
                    if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                        $baggageReturnID = $baggageServiceDataReturn[0];
                        $baggageReturnDescription = $baggageServiceDataReturn[1];
                        $baggageReturnAmount = $baggageServiceDataReturn[2];
                    } else {
                        $baggageReturnID = "";
                        $baggageReturnDescription = "";
                        $baggageReturnAmount = "";
                    }
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                }
                

                if (isset($data['mealServiceReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $data['mealServiceReturn' . $i]);
                if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                    $mealReturnId = $mealServiceDataReturn[0];
                    $mealReturnDescription = $mealServiceDataReturn[1];
                    $mealReturnAmount = $mealServiceDataReturn[2];
                    } else{
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount ="";
                    }
                } else {
                    $mealReturnId = "";
                    $mealReturnDescription = "";
                    $mealReturnAmount ="";
                }
                   
                    // die($baggageAmount);

                // Bind the parameters to the statement
                $stmttraveler->bindValue(':firstName', $firstName, PDO::PARAM_STR);
                $stmttraveler->bindValue(':lastName', $lastName, PDO::PARAM_STR);
                 $stmttraveler->bindValue(':bookingID', $bookingID, PDO::PARAM_INT);
                $stmttraveler->bindValue(':dob', $formattedDOB, PDO::PARAM_STR);
                $stmttraveler->bindValue(':passportNo', $passportNo, PDO::PARAM_STR);
                $stmttraveler->bindValue(':passpostExp', $passpostExp, PDO::PARAM_STR);
                $stmttraveler->bindValue(':title', $title, PDO::PARAM_STR);
                $stmttraveler->bindValue(':passengerType', $passengerType, PDO::PARAM_STR);
                $stmttraveler->bindValue(':baggageId', $baggageID, PDO::PARAM_INT);
                $stmttraveler->bindValue(':baggageDescription', $baggageDescription, PDO::PARAM_STR);
                $stmttraveler->bindValue(':baggageAmount', $baggageAmount, PDO::PARAM_STR);
                $stmttraveler->bindValue(':mealId', $mealId, PDO::PARAM_INT);
                $stmttraveler->bindValue(':mealDescription', $mealDescription, PDO::PARAM_STR);
                $stmttraveler->bindValue(':mealAmount', $mealAmount, PDO::PARAM_STR);
                $stmttraveler->bindValue(':gender', $gender, PDO::PARAM_STR);
                $stmttraveler->bindValue(':issuingCountry', $issuingCountry, PDO::PARAM_STR);
                $stmttraveler->bindValue(':nationality', $nationality, PDO::PARAM_STR);

                $stmttraveler->bindValue(':baggageReturnId', $baggageReturnID, PDO::PARAM_INT);
                $stmttraveler->bindValue(':baggageReturnDescription', $baggageReturnDescription, PDO::PARAM_STR);
                $stmttraveler->bindValue(':baggageReturnAmount', $baggageReturnAmount, PDO::PARAM_STR);
                $stmttraveler->bindValue(':mealReturnId', $mealReturnId, PDO::PARAM_INT);
                $stmttraveler->bindValue(':mealReturnDescription', $mealReturnDescription, PDO::PARAM_STR);
                $stmttraveler->bindValue(':mealReturnAmount', $mealReturnAmount, PDO::PARAM_STR);

                // Execute the statement
                $stmttraveler->execute();
            }


            $sqltravelerchild = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,
            dob,passport_number,passport_expiry_date,title,passenger_type,extrabaggage_id,extrabaggage_description,
            extrabaggage_amount,extrameal_id,extrameal_description,extrameal_amount,gender,issuing_country,
            nationality,extrameal_return_id,extrameal_return_description,extrameal_return_amount,extrabaggage_return_id,
            extrabaggage_return_description,extrabaggage_return_amount) 
            VALUES (:firstName, :lastName, :bookingID,:dob,:passportNo,:passpostExp,:title,:passengerType,:baggageId,
            :baggageDescription,:baggageAmount,:mealId,:mealDescription,:mealAmount,:gender,:issuingCountry,:nationality,
            :mealReturnId,:mealReturnDescription,:mealReturnAmount,:baggageReturnId,:baggageReturnDescription,:baggageReturnAmount)";
            // $sqltraveler = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,passport_number,title) 
            // VALUES (:firstName, :lastName, :bookingID,:passportNo,:title)";

            // Prepare the statement
            $stmttravelerchild = $conn->prepare($sqltravelerchild);

            for ($i = 1; $i <= $childCount; $i++) {
                // print_r($bookingData['id']);die();
                $firstName = $data['firstNameChild' . $i];
                $lastName = $data['lastNameChild' . $i];
                $dob = $data['childDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $data['passportNoChild' . $i];
                $passpostExp = $data['pasprtExpChild' . $i];
                $bookingID= $bookingData['id'];
                $title = $data['sirLableChild' . $i];
                $gender = $data['genderChild' . $i];
                $issuingCountry = $data['issuingcountryChild' . $i];
                $passengerType ="CHD";
                $nationality = $data['nationalityChild' . $i];
                if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                    // Handle the validation error (e.g., show an error message or redirect back with an error)
                    // For example, you can redirect back to the form page with an error message
                    $errorMessage = 'Please fill in all mandatory fields for child';

                    $errors=$errorMessage;
                    $response = array(
           
                        'errors'=> $errors
                        // 'value3' => $value3
                      );
                      echo json_encode($response);
                     exit();
                }

                if (isset($data['baggageServiceChild' . $i])) {
                    $baggageServiceData = explode('/', $data['baggageServiceChild' . $i]);
                    if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                        $baggageID = $baggageServiceData[0];
                        $baggageDescription = $baggageServiceData[1];
                        $baggageAmount = $baggageServiceData[2];
                    } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                    }
                } else {
                    $baggageID = "";
                    $baggageDescription = "";
                    $baggageAmount = "";
                }
                

                if (isset($data['mealServiceChild' . $i])) {
                $mealServiceData = explode('/', $data['mealServiceChild' . $i]);
                if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                    $mealId = $mealServiceData[0];
                    $mealDescription = $mealServiceData[1];
                    $mealAmount = $mealServiceData[2];
                    } else{
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount ="";
                    }
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount ="";
                }
                //Extra service return

                if (isset($data['baggageServiceChildReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $data['baggageServiceChildReturn' . $i]);
                    if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                        $baggageReturnID = $baggageServiceDataReturn[0];
                        $baggageReturnDescription = $baggageServiceDataReturn[1];
                        $baggageReturnAmount = $baggageServiceDataReturn[2];
                    } else {
                        $baggageReturnID = "";
                        $baggageReturnDescription = "";
                        $baggageReturnAmount = "";
                    }
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                }
                

                if (isset($data['mealServiceChildReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $data['mealServiceChildReturn' . $i]);
                if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                    $mealReturnId = $mealServiceDataReturn[0];
                    $mealReturnDescription = $mealServiceDataReturn[1];
                    $mealReturnAmount = $mealServiceDataReturn[2];
                    } else{
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount ="";
                    }
                } else {
                    $mealReturnId = "";
                    $mealReturnDescription = "";
                    $mealReturnAmount ="";
                }

                   

                // Bind the parameters to the statement
                $stmttravelerchild->bindValue(':firstName', $firstName, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':lastName', $lastName, PDO::PARAM_STR);
                 $stmttravelerchild->bindValue(':bookingID', $bookingID, PDO::PARAM_INT);
                $stmttravelerchild->bindValue(':dob', $formattedDOB, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':passportNo', $passportNo, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':passpostExp', $passpostExp, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':title', $title, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':passengerType', $passengerType, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':baggageId', $baggageID, PDO::PARAM_INT);
                $stmttravelerchild->bindValue(':baggageDescription', $baggageDescription, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':baggageAmount', $baggageAmount, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':mealId', $mealId, PDO::PARAM_INT);
                $stmttravelerchild->bindValue(':mealDescription', $mealDescription, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':mealAmount', $mealAmount, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':gender', $gender, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':issuingCountry', $issuingCountry, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':nationality', $nationality, PDO::PARAM_STR);

                $stmttravelerchild->bindValue(':baggageReturnId', $baggageReturnID, PDO::PARAM_INT);
                $stmttravelerchild->bindValue(':baggageReturnDescription', $baggageReturnDescription, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':baggageReturnAmount', $baggageReturnAmount, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':mealReturnId', $mealReturnId, PDO::PARAM_INT);
                $stmttravelerchild->bindValue(':mealReturnDescription', $mealReturnDescription, PDO::PARAM_STR);
                $stmttravelerchild->bindValue(':mealReturnAmount', $mealReturnAmount, PDO::PARAM_STR);

                // Execute the statement
                $stmttravelerchild->execute();
            }

            $sqltravelerinfant = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,dob,passport_number,
            passport_expiry_date,title,passenger_type,gender,issuing_country,nationality) 
            VALUES (:firstName, :lastName, :bookingID,:dob,:passportNo,:passpostExp,:title,:passengerType,:gender,:issuingCountry,:nationality)";
          
            $stmttravelerinfant = $conn->prepare($sqltravelerinfant);
            for ($i = 1; $i <= $infantCount; $i++) {
                $firstName = $data['firstNameInfant' . $i];
                $lastName = $data['lastNameInfant' . $i];
                $dob = $data['infantDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $data['passportNoInfant' . $i];
                $passpostExp = $data['pasprtExpInfant' . $i];
                $bookingID= $bookingData['id'];
                $title = $data['sirLableInfant' . $i];
                $gender = $data['genderInfant' . $i];
                $issuingCountry = $data['issuingcountryInfant' . $i];
                $nationality = $data['nationalityinfant' . $i];
                $passengerType ="INF";


                if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                    // Handle the validation error (e.g., show an error message or redirect back with an error)
                    // For example, you can redirect back to the form page with an error message
                    $errorMessage = 'Please fill in all mandatory fields for infant ';

                    header('Content-Type: application/json');
                    $errors=$errorMessage;
                    $response = array(
           
                        'errors'=> $errors
                        // 'value3' => $value3
                      );
                      echo json_encode($response);
                     exit();
                }

               

               

                // Bind the parameters to the statement
                $stmttravelerinfant->bindValue(':firstName', $firstName, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':lastName', $lastName, PDO::PARAM_STR);
                 $stmttravelerinfant->bindValue(':bookingID', $bookingID, PDO::PARAM_INT);
                $stmttravelerinfant->bindValue(':dob', $formattedDOB, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':passportNo', $passportNo, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':passpostExp', $passpostExp, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':title', $title, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':passengerType', $passengerType, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':gender', $gender, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':issuingCountry', $issuingCountry, PDO::PARAM_STR);
                $stmttravelerinfant->bindValue(':nationality', $nationality, PDO::PARAM_STR);
               

                // Execute the statement
                $stmttravelerinfant->execute();
            }
            


        }
       
    // }
}
//---------------------------------booking API--------------------------------
$stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid AND id= :id');

$stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id'],'id' =>$tempBookingId));
$bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
$fsc=$bookingData['fare_source_code'];

if (isset($fsc)) {
    // $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

    // $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
    // $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    $codeWithoutPlus = substr($bookingData['contact_phonecode'], 1);

    $stmt = $conn->prepare("SELECT * FROM travellers_details Where flight_booking_id = :bookingId");
    $stmt->execute(array('bookingId' => $bookingData['id']));
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
   // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Book/Flight';
    //$bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
    $endpoint   =   'v1/Book/Flight';
     $apiEndpoint = APIENDPOINT.$endpoint;
     $bearerToken   =   BEARER;

    // $requestData = array(          
    //     'FareSourceCode' => $fsc,
    //     'PassengerTypeQuantities' => array(
    //         'AirTravelers' => array(
    //             array(
    //             'Code' => 'ADT',
    //             'Quantity' => $adultCount
    //             )
    //         )
    //     ),
    //     'Target' => 'Test',
    // );

    
    foreach ($result as $row) {

        $extraMealId = $row['extrameal_id'];
        $extraBaggageId = $row['extrabaggage_id'];
        $extraMealReturnId = $row['extrameal_return_id'];
        $extraBaggageReturnId = $row['extrabaggage_return_id'];
        $extraServices = [];

        if ($extraMealId != 0) {
            $extraServices[] = array(
                "ExtraServiceId" => $extraMealId,
                "Quantity" => 1,
                "Key" => "string"
            );
        }

        if ($extraBaggageId != 0) {
            $extraServices[] = array(
                "ExtraServiceId" => $extraBaggageId,
                "Quantity" => 1,
                "Key" => "string"
            );
        }
        if ($extraMealReturnId != 0) {
            $extraServices[] = array(
                "ExtraServiceId" => $extraMealReturnId,
                "Quantity" => 1,
                "Key" => "string"
            );
        }
        if ($extraBaggageReturnId != 0) {
            $extraServices[] = array(
                "ExtraServiceId" => $extraBaggageReturnId,
                "Quantity" => 1,
                "Key" => "string"
            );
        }

        $passenger = array(
            "PassengerType" => $row['passenger_type'],
            "Gender" => $row['gender'],
            // "Gender" => "F",
            "PassengerName" => array(
                "PassengerTitle" => $row['title'],
                "PassengerFirstName" => $row['first_name'],
                "PassengerLastName" => $row['last_name']
            ),
            "DateOfBirth" => $row['dob'],
            "Passport" => array(
                "PassportNumber" => $row['passport_number'],
                "ExpiryDate" => $row['passport_expiry_date'],
                "Country" => $row['issuing_country'],
                // "Country" => "IN",
            ),
            // "ExtraServices1_1"=> array(
            //     array(
            //       "ExtraServiceId"=> 11,
            //       "Quantity"=> 1,
            //       "Key"=> "string"
            //     )
            // ),
           
            // "ExtraServices1_1" => $extraServices,
            // "PassengerNationality" => "IN",
            "PassengerNationality" => $row['nationality'],
        );
        if (!empty($extraServices) &&  $bookingData['fare_type'] == "WebFare") {
            $passenger["ExtraServices1_1"] = $extraServices;
        }
        $passengerDetails[] = $passenger;
    }

    ////////////////////////////////////


    $requestData = array(
        "FareSourceCode" =>  $fsc,
        "ClientMarkup" => $markup,
        "TravelerInfo" => array(
            "AirTravelers" => $passengerDetails,
            "CountryCode" => $codeWithoutPlus,
            // "AreaCode" => "080",
            "PhoneNumber" => $bookingData['contact_number'],
            "Email" => $bookingData['contact_email'],
            "PostCode" => $bookingData['contact_postcode']
        ),
        
        // "ExtraServices1_1" => $extraServices,
        "Target" => TARGET,
        // "ConversationId" => "sai",
        // "LccHoldBooking" => true
    );
    // echo "<pre/>";
    //  print_r($requestData);
    
  
   
    // Send the API request

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $bearerToken
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    // Handle the API response
   
    if ($response) {
        $responseData = json_decode($response, true);

    }

}
 //=================log write for book API ======
                   
                    $logRes =   print_r($responseData, true);
                  $logReQ =   print_r($requestData, true);
                    $objBook->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','booking.txt');
                    $objBook->_writeLog('Request Received\n'.$logReQ,'booking.txt');
                            $objBook->_writeLog('REsponse Received for MF:\n'.$responseData['Data']['UniqueID']. 'OR USERID='.$userId,'booking.txt');
                              $objBook->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'booking.txt');
                              $objBook->_writeLog('Booking ID is '.$bookingID,'booking.txt');
                     

                $objBook->_writeLog('REsponse Received\n'.$logRes,'booking.txt');   
               
 //============ END log write for book API ==========   
   $resSuccess  = $responseData['Data']['Success'];
   //handling error responses 
   $fairtype=$bookingData['fare_type'];
    if(!empty($responseData['Data']['Errors'])){
       $errMsg = $responseData['Data']['Errors'][0]['Message'];
        $errCDE = $responseData['Data']['Errors'][0]['Code'];
          if(empty($errMsg)){
              $errMsg = $responseData['Data']['Errors']['Message'];
                $errCDE = $responseData['Data']['Errors']['Code'];
          }  

        // db update
        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
        // Set the values
        // Set the current datetime for booking_date
        $booking_date = date('Y-m-d H:i:s');  
        $mfreference =$responseData['Data']['UniqueID'] ;
        $traceId = $responseData['Data']['TraceId'];
        $booking_status = $responseData['Data']['Status'];
        
        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
        $id = $bookingData['id'];
        
            // Bind the parameters
        $stmtupdate->bindParam(':mfreference', $mfreference);
        $stmtupdate->bindParam(':traceId', $traceId);
        $stmtupdate->bindParam(':booking_status', $booking_status);
        $stmtupdate->bindParam(':booking_date', $booking_date);
        $stmtupdate->bindParam(':markup', $markup);
        $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
        $stmtupdate->bindParam(':id', $id);

        

        // Execute the query
        $stmtupdate->execute();
        //=====================insrt into booking err table======
       $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
        // Set the values
        // Set the current datetime for booking_date
       
        $err_code =$errCDE;
       $fairtype=$bookingData['fare_type'];

        $booking_status = $responseData['Data']['Status'];
        $ticket_status = $responseData['Data']['Status'];
        
        $id = $bookingData['id'];
        
            // Bind the parameters
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
               // Execute the query
                $stmtInsert->execute();
                //====================email send code to admin regrding booking failure and amount need to repay======

                    //  include_once('mail_send.php');

                      $subject = "Bulatrips Agent Booking attempt Failure and Balance need to credit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
            //====================================================
       
            $response = array(
        'BookStatus' => "Failed",
        'ticketstatus' => "Failed",
        'faretype' =>$fairtype,
        'errors'=> $errMsg,
         'errCde' => $errCDE
      );
      //========
        $logResErr =   print_r($response, true);
       $objBook->_writeLog('Error 1 Received\n'.$logResErr,'booking.txt');
      //echo json_encode("yyy");exit;
       // header('Content-Type: application/json');
        echo json_encode($response);exit;
         //===========  

        }//END OF ERROR RESPONSE
        //*************************
        elseif(($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "CONFIRMED")){ //"Confirmed" with MF Number: Please consider it as a successful transaction
            // echo $responseData['Data']['Status'];  
            //log write ,booking sts update ,booking date ,markup value
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
            // Set the values
            // Set the current datetime for booking_date
            $booking_date = date('Y-m-d H:i:s');
        


            $mfreference =$responseData['Data']['UniqueID'] ;
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
        
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
        
                // Bind the parameters
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);

        

            // Execute the query
            $stmtupdate->execute();
           // $orderstatus = "order success";
           $orderstatus = $responseData['Data']['Success'];//hope this will pass true always 
           //========
            $logResSus =   $booking_status;
           $objBook->_writeLog('Success Received\n'.$logResSus,'booking.txt');
           
          //echo "jjj";exit;
          //============
        }
        elseif(($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "BOOKINGINPROCESS")){ //"BOOKINGINPROCESS" and the booking might get conformed or unconfirmed based on the Airline's confirmation
           // echo $responseData['Data']['Status'];  
            //log write ,booking sts update ,booking date ,markup value
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
                // Set the values
                // Set the current datetime for booking_date
                $booking_date = date('Y-m-d H:i:s');
        


                $mfreference =$responseData['Data']['UniqueID'] ;
                $traceId = $responseData['Data']['TraceId'];
                $booking_status = $responseData['Data']['Status'];
        
                $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                $id = $bookingData['id'];
        
                    // Bind the parameters
                $stmtupdate->bindParam(':mfreference', $mfreference);
                $stmtupdate->bindParam(':traceId', $traceId);
                $stmtupdate->bindParam(':booking_status', $booking_status);//BOOKINGINPROCESS
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':markup', $markup);
                $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                $stmtupdate->bindParam(':id', $id);

        

                // Execute the query
                $stmtupdate->execute();
                //$orderstatus = "order success"; //but cron needed for finalised status
                $orderstatus = $responseData['Data']['Success'];
                //=============
                $logResSus =   $booking_status;
               $objBook->_writeLog('Success in process Received\n'.$logResSus,'booking.txt');
                //echo "yyy";exit;
                //==============

        }
        elseif(empty($responseData['Data']['Success'])){ //failure case
            $errCDE ='';
            $errMsg ='';
            if(!empty($responseData['Data']['Errors'])){
                $errMsg = $responseData['Data']['Errors'][0]['Message'];
                $errCDE = $responseData['Data']['Errors'][0]['Code'];
                if(empty($errMsg)){
                 $errMsg = $responseData['Data']['Message'];
                 }
            }
            
            // db update
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
            // Set the values
            // Set the current datetime for booking_date
            $booking_date = date('Y-m-d H:i:s');
        


            $mfreference =$responseData['Data']['UniqueID'] ;
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
        
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
        
                // Bind the parameters
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':markup', $markup);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);

        

            // Execute the query
            $stmtupdate->execute();
            //=====================insrt into booking err table======
           $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
            // Set the values
            // Set the current datetime for booking_date
       
            $err_code =$errCDE;
           $fairtype=$bookingData['fare_type'];

            $booking_status = $responseData['Data']['Status'];
            $ticket_status = $responseData['Data']['Status'];
        
            $id = $bookingData['id'];
             if(empty($errMsg)){
                $errMsg = "Null Status Received from Airline";
                $errCDE ="000";
            }
        
                // Bind the parameters
                    $stmtInsert->bindParam(':book_id', $id);
                    $stmtInsert->bindParam(':err_code', $err_code);
                    $stmtInsert->bindParam(':err_msg', $errMsg);
                    $stmtInsert->bindParam(':fare_type', $fairtype);
                    $stmtInsert->bindParam(':book_status', $booking_status);
                    $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                   // Execute the query
                    $stmtInsert->execute();

            //====================================================
           
                    $response = array(
                'BookStatus' => "Failed",
                'ticketstatus' => "Failed",
                'faretype' =>$fairtype,
                'errors'=> $errMsg,
                 'errCde' => $errCDE
              );
              //========
                    $logResErr =   print_r($response, true);
                   $objBook->_writeLog('Error NULL Success Received\n'.$logResErr,'booking.txt');
                   //====================email send code to admin regrding booking failure and amount need to repay======                     

                      $subject = "Bulatrips Agent Booking attempt Failure and Balance need to credit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
            //====================================================
                 // echo "tttt";exit;
                    header('Content-Type: application/json');
                    echo json_encode($response);exit;
                     //===========          
         
        }
            else{        
                //whatever response from api call update it into db 
                        // Set the values
                        // Set the current datetime for booking_date
                        $booking_date = date('Y-m-d H:i:s');
                        $mfreference =$responseData['Data']['UniqueID'] ;
                        $traceId = $responseData['Data']['TraceId'];
                        $booking_status = $responseData['Data']['Status'];
        
                        $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                        $id = $bookingData['id'];
        
                    //=======Pending without MF number Direct failure as per api doc===
                    if(empty($mfreference) && ($booking_status == "PENDING")){
                       // $booking_status = "Failed";
                            // db update to failed 
                             $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                            
                             $stmtupdate->bindParam(':booking_date', $booking_date);
                             $stmtupdate->bindParam(':markup', $markup);
                             $stmtupdate->bindParam(':booking_status',$booking_status);
                             $stmtupdate->bindParam(':id', $id);
                             if(empty($errMsg)){
                                    $errMsg = "Pending without MF number Direct failure as per api Received";
                                    $errCDE ="001";
                                }
                                                 // Execute the query
                                $stmtupdate->execute();
                              $response = array(
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' =>$fairtype,
                            'errors'=> $errMsg,
                             'errCde' => $errCDE
                            );
                            //==========
                            //=====================insrt into booking err table======
                                   $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
                                    // Set the values
                                    // Set the current datetime for booking_date
       
                                    $err_code =$errCDE;
                                   $fairtype=$bookingData['fare_type'];

                                    $booking_status = $responseData['Data']['Status'];
                                    $ticket_status = $responseData['Data']['Status'];
        
                                    $id = $bookingData['id'];
        
                                        // Bind the parameters
                                            $stmtInsert->bindParam(':book_id', $id);
                                            $stmtInsert->bindParam(':err_code', $err_code);
                                            $stmtInsert->bindParam(':err_msg', $errMsg);
                                            $stmtInsert->bindParam(':fare_type', $fairtype);
                                            $stmtInsert->bindParam(':book_status', $booking_status);
                                            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                                           // Execute the query
                                            $stmtInsert->execute();
                                            //====================email send code to admin regrding booking failure and amount need to repay======

                    //  include_once('mail_send.php');

                      $subject = "Bulatrips Agent Booking attempt Failure and Balance need to credit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
                          //========
                                $logResErr =   print_r($response, true);
                               $objBook->_writeLog(' Pending without MF number Direct failure as per api Received\n'.$logResErr,'booking.txt');
                             // echo "tttt";exit;
                                header('Content-Type: application/json');
                                echo json_encode($response);exit;

                     }//close of pending without mf
                     //==========================Not Booked status -failure====
                     else if(empty($mfreference) && ($booking_status == "NotBooked")){
                       // $booking_status = "Failed";
                            // db update to failed 
                             $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                            
                             $stmtupdate->bindParam(':booking_date', $booking_date);
                             $stmtupdate->bindParam(':markup', $markup);
                             $stmtupdate->bindParam(':booking_status',$booking_status);
                             $stmtupdate->bindParam(':id', $id);
                             if(empty($errMsg)){
                                    $errMsg = "Not Booked, Direct failure as per api Received";
                                    $errCDE ="002";//custom error codes
                                }
                                                 // Execute the query
                                $stmtupdate->execute();
                              $response = array(
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' =>$fairtype,
                            'errors'=> $errMsg,
                             'errCde' => $errCDE
                            );
                            //==========
                                                    //=====================insrt into booking err table======
                               $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
                                // Set the values
                                // Set the current datetime for booking_date
       
                                $err_code =$errCDE;
                               $fairtype=$bookingData['fare_type'];

                                $booking_status = $responseData['Data']['Status'];
                                $ticket_status = $responseData['Data']['Status'];
        
                                $id = $bookingData['id'];
        
            // Bind the parameters
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
               // Execute the query
                $stmtInsert->execute();
                //====================email send code to admin regrding booking failure and amount need to repay======

                    //  include_once('mail_send.php');

                      $subject = "Bulatrips Agent Booking attempt Failure and Balance need to credit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
                          //========
                                $logResErr =   print_r($response, true);
                               $objBook->_writeLog(' Pending without MF number Direct failure as per api Received\n'.$logResErr,'booking.txt');
                             // echo "tttt";exit;
                                header('Content-Type: application/json');
                                echo json_encode($response);exit;

                     }//==========================End of Not Booked Failure=========
                     //==============Ticketed,booked,Ticketin process statuses may need cron =======
                     elseif (($responseData['Data']['Success']) && in_array($responseData['Data']['Status'], ["Booked", "Ticketed", "Ticket-In Process","Pending"])) {

                                          // echo $responseData['Data']['Status'];  
                                //log write ,booking sts update ,booking date ,markup value
                                $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
                                    // Set the values
                                    // Set the current datetime for booking_date
                                    $booking_date = date('Y-m-d H:i:s');
        


                                    $mfreference =$responseData['Data']['UniqueID'] ;
                                    $traceId = $responseData['Data']['TraceId'];
                                    $booking_status = $responseData['Data']['Status'];
        
                                    $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                                    $id = $bookingData['id'];
        
                                        // Bind the parameters
                                    $stmtupdate->bindParam(':mfreference', $mfreference);
                                    $stmtupdate->bindParam(':traceId', $traceId);
                                    $stmtupdate->bindParam(':booking_status', $booking_status);//
                                    $stmtupdate->bindParam(':booking_date', $booking_date);
                                    $stmtupdate->bindParam(':markup', $markup);
                                    $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                                    $stmtupdate->bindParam(':id', $id);

        

                                    // Execute the query
                                    $stmtupdate->execute();
                                    //$orderstatus = "order success"; //but cron needed for finalised status
                                    $orderstatus = $responseData['Data']['Success'];
                                    //=============
                                    $logResSus =   $booking_status;
                                   $objBook->_writeLog('Success in process Received\n'.$logResSus,'booking.txt');
                                    //echo "yyy";exit;
                                    //==============

                            }//==================end of ticketed,booked,ticketinprocess statuses=====
                     else{
                        //============consider failure booking on else 
                        // $booking_status = "Failed";
                            // db update to failed 
                             $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , markup = :markup, booking_status = :booking_status WHERE id = :id');
                            
                             $stmtupdate->bindParam(':booking_date', $booking_date);
                             $stmtupdate->bindParam(':markup', $markup);
                             $stmtupdate->bindParam(':booking_status',$booking_status);
                             $stmtupdate->bindParam(':id', $id);
                             if(empty($errMsg)){
                                    $errMsg = $booking_status;
                                    $errCDE ="003";//custom error codes
                                }
                                                 // Execute the query
                                $stmtupdate->execute();
                              $response = array(
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' =>$fairtype,
                            'errors'=> $errMsg,
                             'errCde' => $errCDE
                            );
                            //==========
                                                    //=====================insrt into booking err table======
                               $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
                                // Set the values
                                // Set the current datetime for booking_date
       
                                $err_code =$errCDE;
                               $fairtype=$bookingData['fare_type'];

                                $booking_status = $responseData['Data']['Status'];
                                $ticket_status = $responseData['Data']['Status'];
        
                                $id = $bookingData['id'];
        
            // Bind the parameters
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
               // Execute the query
                $stmtInsert->execute();
                //====================email send code to admin regrding booking failure and amount need to repay======

                    //  include_once('mail_send.php');

                      $subject = "Bulatrips Agent Booking attempt Failure and Balance need to credit Info";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello,</p>
                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                          $email = $adminToemail; //Need ADMIN email here
         
                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
                     $logResSus =   $booking_status;
                          //========
                                $logResErr =   print_r($response, true);
                               $objBook->_writeLog(' status else case with booking status\n'.$logResSus,'booking.txt');
                             // echo "tttt";exit;
                                header('Content-Type: application/json');
                                echo json_encode($response);exit;

                        //=================end of else case========
                             /*  $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , markup = :markup ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
       
        
                                    // Bind the parameters
                                $stmtupdate->bindParam(':mfreference', $mfreference);
                                $stmtupdate->bindParam(':traceId', $traceId);
                                $stmtupdate->bindParam(':booking_status', $booking_status);
                                $stmtupdate->bindParam(':booking_date', $booking_date);
                                $stmtupdate->bindParam(':markup', $markup);
                                $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                                $stmtupdate->bindParam(':id', $id);

        

                                // Execute the query
                                $stmtupdate->execute();
                                $orderstatus = $responseData['Data']['Success'];
        
                               //========
                                $logResSus =   $booking_status;
                               $objBook->_writeLog('status else  Received\n'.$logResSus,'booking.txt');
                             // echo "ppp";exit;
                              //============
                                 // $orderstatus = "search again"; ///need to recheck this 
                                 */
                     }//close of else of  pending without mf
            }      
            //echo "HERE";exit;
            //======End of  Book APi handling =======
            if($bookingData['fare_type'] != "WebFare"){
                $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

                $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
                $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
                //$apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/OrderTicket';
               // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
                $endpoint   =   'v1/OrderTicket';
                 $apiEndpoint = APIENDPOINT.$endpoint;
                 $bearerToken   =   BEARER;
     

                  // Construct the API request payload
                  $requestData = array(          
                    'UniqueID' => $bookingData['mf_reference'],
                    'Target' => TARGET,
                    // 'ConversationId' => 'string',
                );
       
        
      
       
                // Send the API request
    
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $bearerToken
                ));
    
                $responseTicket = curl_exec($ch);
                curl_close($ch);
    
                // Handle the API response
       
                if ($response) {
                    $responseTicketData = json_decode($responseTicket, true);
    
                }

        
                 //=================log write for OrderTicket API ======
                    $logRes =   print_r($responseTicketData, true);
                  $logReQ =   print_r($requestData, true);
                    $objBook->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','OrderTicket.txt');
                    $objBook->_writeLog('Request Received\n'.$logReQ,'OrderTicket.txt');
                            $objBook->_writeLog('REsponse Received for MF:\n'.$responseData['Data']['UniqueID']. 'OR USERID='.$userId,'OrderTicket.txt');
                              $objBook->_writeLog('userId is '.$userId. 'BOOKING STATUS IS '.$responseData['Data']['Status'],'OrderTicket.txt');
                              $objBook->_writeLog('Booking ID is '.$bookingID,'OrderTicket.txt');
                     

                $objBook->_writeLog('REsponse Received\n'.$logRes,'OrderTicket.txt');
                //$responseTicketData['Data']['Success'] = 1 means orderticket success ,empty means failurre

                    //============ END log write for  API ==========
                   //  header('Content-Type: application/json');
                   // echo json_encode($responseTicketData); //alert 
                   // exit;
                   //
                   if(!empty($responseData['Data']['Errors'])){ //if orderticket failure ,it means direct failure of booking as per api team 
                           $errMsg = $responseData['Data']['Errors'][0]['Message'];
                            $errCDE = $responseData['Data']['Errors'][0]['Code'];
                              if(empty($errMsg)){
                                  $errMsg = $responseData['Data']['Errors']['Message'];
                                    $errCDE = $responseData['Data']['Errors']['Code'];
                              }  
       
                            // db update
                            $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
       
                            // Set the values
                            // Set the current datetime for booking_date
        
                            $ticket_status = $responseTicketData['Data']['Success'];
        
                            $id = $bookingData['id'];
        
                                // Bind the parameters
       
                            $stmtupdate->bindParam(':ticket_status', $ticket_status);
        
                            $stmtupdate->bindParam(':id', $id);

        

                            // Execute the query
                            $stmtupdate->execute();
                            //=====================insrt into booking err table======
                           $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
       
                            // Set the values
                            // Set the current datetime for booking_date
       
                            $err_code =$errCDE;
                           $fairtype=$bookingData['fare_type'];

                            $booking_status = $booking_status;
       
        
                            $id = $bookingData['id'];
        
                                // Bind the parameters
                                    $stmtInsert->bindParam(':book_id', $id);
                                    $stmtInsert->bindParam(':err_code', $err_code);
                                    $stmtInsert->bindParam(':err_msg', $errMsg);
                                    $stmtInsert->bindParam(':fare_type', $fairtype);
                                    $stmtInsert->bindParam(':book_status', $booking_status);
                                    $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                                   // Execute the query
                                    $stmtInsert->execute();
                                    //====================email send code to admin regrding booking failure and amount need to repay======

                                    //  include_once('mail_send.php');

                                      $subject = "Bulatrips Agent Booking attempt Failure due to OrderTicket api failure and Balance need to credit Info";                  

                                            $email=   $userDetails['email'];
                                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                                            $content    =   '<p>Hello,</p>
                                                                <p>This agent , '. $name .', with email '.$userDetails['email'].' had a Debit transaction for booking id:'.$tempBookingId.'.The amount used is :$'.$amountToDebit.'</p>
                                                                <p>Since this booking attempt failed due to :'. $errMsg.',Please credit back the same amount </p>';
                                                            $messageData =   $objBook->getEmailContent($content);
                                     // print_r($messageData);exit;
                                          $headers="";
                                          $email = $adminToemail; //Need ADMIN email here
         
                                        $contacts= sendMail($email,$subject, $messageData,$headers);



                    //=====================email ends for admin=======
                             $response = array(
                            'BookStatus' => "Failed",
                            'ticketstatus' => "Failed",
                            'faretype' =>$fairtype,
                            'errors'=> $errMsg,
                             'errCde' => $errCDE
                            );
                            //====================================================
       
                                    /*    $response = array(
                                    'BookStatus' => $booking_status,
                                    'ticketstatus' => "Failed",
                                    'faretype' =>$fairtype,
                                    'Ticketerrors'=> $errMsg,
                                     'TickerrCde' => $errCDE
                                  );*/
                                    header('Content-Type: application/json');
                                  echo json_encode($response);exit;
                           }else{
                              // db update
                                $stmtupdate = $conn->prepare('UPDATE temp_booking SET ticket_status = :ticket_status WHERE id = :id');
       
                                // Set the values
                                // Set the current datetime for booking_date
                                $ticket_status = $responseTicketData['Data']['Success'];      //may be 1  on success        
        
                                $id = $bookingData['id'];
        
                                    // Bind the parameters
       
                                $stmtupdate->bindParam(':ticket_status', $ticket_status);
        
                                $stmtupdate->bindParam(':id', $id);

        

                                // Execute the query
                                $stmtupdate->execute();
                                $ticketstatus ="ticket sucess";
                                   /* $response = array(
                                'BookStatus' => $booking_status,
                                'ticketstatus' => $ticketstatus,
                                'faretype' =>$fairtype,
                                'errors'=> $errMsg,
                                 'errCde' => $errCDE
                              );
                              echo json_encode($response);exit;
                              */          
                           }
                // echo '<pre>';
                // print_r($responseTicketData);
                // echo '</pre>';

            }else{
                $ticketstatus ="ticket sucess"; //means like webfare type always assuming success status for tickets instant generation 
                //mail to agent on confirmation sts
                //==========email to agent about debit amount======
                     $subject = "Bulatrips booking Success ";                  

                            $email=   $userDetails['email'];
                            $name   =   $userDetails['first_name']." ".$userDetails['last_name'];
                            $content    =   '<p>Hello '. $name .',</p>
                                                <p>Your Booking on Bulatrips from:'.$depLocation.' to '.$arrivalLocation.' is '.$booking_status.'</p>';
                                            $messageData =   $objBook->getEmailContent($content);
                     // print_r($messageData);exit;
                          $headers="";
                         // $email = "no-reply@bulatrips.com"; //Need ADMIN email here
         
                       // $contacts= sendMail($email,$subject, $messageData,$headers);


                    //====================================email to agent ======
            }

    // if($responseData['Data']['Success']==1){
        // echo json_encode(['success' => true]);
    // }
    $fairtype=$bookingData['fare_type'];
    /* $response = array(
        'orderstatus' => $orderstatus,
        'ticketstatus' => $ticketstatus,
        'faretype' =>$fairtype,
        'errors'=> $errors
        // 'value3' => $value3
      );*/
  // } //close of if(fsc)code //close of if(fsc)code ,may need to integrate on later time
// echo json_encode(['success' => true]);
//call tripdetails to get updated status on booking and ticket generation

$messageNew ='';

    $response = array(
        'BookStatus' => $booking_status,
        'ticketstatus' => $ticketstatus,
        'faretype' =>$fairtype,
        'bookingid' => $tempBookingId,
         'messageNew' => $messageNew
      );

          echo json_encode($response);exit;

        //****************************


   //===========
    // Mock data for demonstration
   /* $response = array(
        'success' => true,
        'message' => 'Booking confirmed successfully.',
        'data'   => json_encode($responseData)
    );

    // Encode the response array to JSON and echo it
    echo json_encode($response);
    exit;*/
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}
exit;

?>
