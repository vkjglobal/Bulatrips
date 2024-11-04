<?php
require_once('includes/dbConnect.php');
include_once('includes/class.Markup.php');
include_once('includes/class.Users.php');
session_start();

// Get the form data
// $adultCount = $_POST['adultCount'];
$adultCount =$_POST['adultCount'];
$childCount =$_POST['childCount'];
$infantCount =$_POST['infantCount'];
$contactFirstName=$_POST['contactfirstname'];
$contacLastName=$_POST['contactlastname'];
$contactPhone=$_POST['contactnumber'];
$contactEmail=$_POST['contactemail'];
$contactPhonecode=$_POST['contactcountry'];
$contactPostcode=$_POST['contactpostcode'];
$userId = $_SESSION['user_id'];
// Validate the form data
$errors = []; // Initialize an array to store validation errors
// $pricedItineraries= $_POST['pricedItineraries'];
$pricedItineraries = json_decode($_POST['pricedItineraries'], true);
// echo '<pre>';
// print_r($pricedItineraries);
// echo '</pre>';
// // print_r($pricedItineraries);
// die();

$sqlbooking = "INSERT INTO temp_booking (dep_location, arrival_location, dep_date,airline,air_trip_type,adult_count,child_count,infant_count,fare_source_code,ticket_type,
contact_first_name,contact_last_name,contact_email,contact_number,user_id,total_fare,stops,fare_type,contact_phonecode,contact_postcode) 
VALUES (:dep_location, :arrival_location, :dep_date,:airline,:air_trip_type,:adult_count,:child_count,:infant_count,:fare_source_code,:ticket_type,
:contact_first_name,:contact_last_name,:contact_email,:contact_number,:user_id,:total_fare,:stops,:fareType,:contactPhonecode,:contactPostcode)";
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
        $stmtboking->bindValue(':contact_last_name', $contacLastName, PDO::PARAM_STR);
        $stmtboking->bindValue(':contact_email', $contactEmail, PDO::PARAM_STR);
        $stmtboking->bindValue(':contact_number', $contactPhone, PDO::PARAM_STR);
        $stmtboking->bindValue(':contactPhonecode', $contactPhonecode, PDO::PARAM_STR);
        $stmtboking->bindValue(':contactPostcode', $contactPostcode, PDO::PARAM_STR);
        // $stmtboking->bindValue(':contact_country', $contactCountry, PDO::PARAM_STR);
        $stmtboking->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmtboking->bindValue(':total_fare', $totalFare, PDO::PARAM_STR);
        $stmtboking->bindValue(':fareType', $fareType, PDO::PARAM_STR);
        $stmtboking->bindValue(':stops', $stops, PDO::PARAM_INT);
        // Execute the statement
        $stmtboking->execute();

        $tempBookingId = $conn->lastInsertId();
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
                $firstName = $_POST['firstName' . $i];
                $lastName = $_POST['lastName' . $i];
                $dob = $_POST['adultDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $_POST['passportNo' . $i];
                $passpostExp = $_POST['pasprtExp' . $i];
                $bookingID= $bookingData['id'];
                $title = $_POST['sirLable' . $i];
                $gender = $_POST['gender' . $i];
                $issuingCountry = $_POST['issuingCountry' . $i];
                $passengerType ="ADT";
                $nationality = $_POST['nationality' . $i];

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

                


                if (isset($_POST['baggageService' . $i])) {
                    $baggageServiceData = explode('/', $_POST['baggageService' . $i]);
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
                

                if (isset($_POST['mealService' . $i])) {
                $mealServiceData = explode('/', $_POST['mealService' . $i]);
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
                if (isset($_POST['baggageServiceReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $_POST['baggageServiceReturn' . $i]);
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
                

                if (isset($_POST['mealServiceReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $_POST['mealServiceReturn' . $i]);
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
                $firstName = $_POST['firstNameChild' . $i];
                $lastName = $_POST['lastNameChild' . $i];
                $dob = $_POST['childDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $_POST['passportNoChild' . $i];
                $passpostExp = $_POST['pasprtExpChild' . $i];
                $bookingID= $bookingData['id'];
                $title = $_POST['sirLableChild' . $i];
                $gender = $_POST['genderChild' . $i];
                $issuingCountry = $_POST['issuingcountryChild' . $i];
                $passengerType ="CHD";
                $nationality = $_POST['nationalityChild' . $i];
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

                if (isset($_POST['baggageServiceChild' . $i])) {
                    $baggageServiceData = explode('/', $_POST['baggageServiceChild' . $i]);
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
                

                if (isset($_POST['mealServiceChild' . $i])) {
                $mealServiceData = explode('/', $_POST['mealServiceChild' . $i]);
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

                if (isset($_POST['baggageServiceChildReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $_POST['baggageServiceChildReturn' . $i]);
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
                

                if (isset($_POST['mealServiceChildReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $_POST['mealServiceChildReturn' . $i]);
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

            $sqltravelerinfant = "INSERT INTO travellers_details (first_name, last_name, flight_booking_id,dob,passport_number,passport_expiry_date,title,passenger_type,gender,issuing_country,nationality) 
            VALUES (:firstName, :lastName, :bookingID,:dob,:passportNo,:passpostExp,:title,:passengerType,:gender,:issuingCountry,:nationality)";
          
            $stmttravelerinfant = $conn->prepare($sqltravelerinfant);
            for ($i = 1; $i <= $infantCount; $i++) {
                $firstName = $_POST['firstNameInfant' . $i];
                $lastName = $_POST['lastNameInfant' . $i];
                $dob = $_POST['infantDOB' . $i];
                $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                $passportNo =  $_POST['passportNoInfant' . $i];
                $passpostExp = $_POST['pasprtExpInfant' . $i];
                $bookingID= $bookingData['id'];
                $title = $_POST['sirLableInfant' . $i];
                $gender = $_POST['genderInfant' . $i];
                $issuingCountry = $_POST['issuingcountryInfant' . $i];
                $nationality = $_POST['nationalityinfant' . $i];
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



// Close the statement and database connection


// Send a success response
//  $response = ['success' => true, 'faresource' => $bookingData['id']];
// echo json_encode(['success' => true]);

//---------------------------------booking API--------------------------------
$stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

$stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
$bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
$fsc=$bookingData['fare_source_code'];
$objUser = new Users();
$userDetails = $objUser->getUserDetails($userId);
$objMarkup = new Markup();
$markupDetails = $objMarkup->getMarkupDetails($userDetails['role']);
$markup =  ($markupDetails['commission_percentage'] / 100) * $totalFare;
if (isset($fsc)) {
    // $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

    // $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
    // $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    $codeWithoutPlus = substr($bookingData['contact_phonecode'], 1);

    $stmt = $conn->prepare("SELECT * FROM travellers_details Where flight_booking_id = :bookingId");
    $stmt->execute(array('bookingId' => $bookingData['id']));
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Book/Flight';
    $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


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
        "Target" => "Test",
        // "ConversationId" => "sai",
        // "LccHoldBooking" => true
    );

    // print_r($requestData);die();
    
   
    
  
   
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

    // $responseData  = $_SESSION['validateresponse'];
    // echo '<pre>';
    // print_r($responseData);
    // echo '</pre>';
    if(isset($responseData['Data']['Errors'][0])){
        $orderstatus = $responseData['Data']['Errors'][0];
        //  print_r($responseData['Data']['Errors']);

    }else if($responseData['Data']['Success'] == "CONFIRMED"){
        $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId WHERE id = :id');

        // Set the values
        $mfreference =$responseData['Data']['UniqueID'] ;
        $traceId = $responseData['Data']['TraceId'];
        $id = $bookingData['id'];
        
            // Bind the parameters
        $stmtupdate->bindParam(':mfreference', $mfreference);
        $stmtupdate->bindParam(':traceId', $traceId);
        $stmtupdate->bindParam(':id', $id);

        

        // Execute the query
        $stmtupdate->execute();
        $orderstatus = "order success";

    }
    else{
        $orderstatus = "search again";
    }
    
    if($bookingData['fare_type'] != "WebFare"){
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

        $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/OrderTicket';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';

          // Construct the API request payload
          $requestData = array(          
            'UniqueID' => $bookingData['mf_reference'],
            'Target' => 'Test',
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
        if(isset($responseTicketData['Data']['Errors'][0])){
            $ticketstatus =$responseTicketData['Data']['Errors'][0];
    
        }else{
            $ticketstatus ="ticket sucess";
        }
        // echo '<pre>';
        // print_r($responseTicketData);
        // echo '</pre>';

    }else{
        $ticketstatus ="ticket sucess";
    }

    // if($responseData['Data']['Success']==1){
        // echo json_encode(['success' => true]);
    // }
    $fairtype=$bookingData['fare_type'];
    $response = array(
        'orderstatus' => $orderstatus,
        'ticketstatus' => $ticketstatus,
        'faretype' =>$fairtype,
        'errors'=> $errors
        // 'value3' => $value3
      );
}
// echo json_encode(['success' => true]);
$response = array(
    'orderstatus' => $orderstatus,
    'ticketstatus' => $ticketstatus,
    'faretype' =>$fairtype,
    'errors'=> $errors
    // 'value3' => $value3
  );
  echo json_encode($response);
?>
