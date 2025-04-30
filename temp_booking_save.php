<?php
error_reporting();
//User booking call 
include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
include_once('includes/class.Markup.php');
include_once('includes/class.Users.php');
include_once('includes/class.BookScript.php');
$objBook    =   new BookScript();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');


    $data   =   $_SESSION['revalidationApi'];

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
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "";
    
    
    $extra_service_total = $_SESSION['totalService'] ?? '';
    $total_paid = $_SESSION['session_total_amount'] ?? '';
    $total_paid = str_replace(',', '', $total_paid);
    
    $adminToemail   =   "no-reply@bulatrips.com";
    $pricedItineraries = json_decode($data['pricedItineraries'], true);
    $errors = [];

    $sqlbooking = "INSERT INTO temp_booking (dep_location, arrival_location, dep_date,airline,air_trip_type,adult_count,child_count,infant_count, fare_source_code,ticket_type,contact_first_name,contact_last_name,contact_email,contact_number,user_id,total_fare,stops,fare_type, contact_phonecode,contact_postcode,markup,total_paid,extra_service_total) VALUES (:dep_location, :arrival_location, :dep_date,:airline,:air_trip_type,:adult_count,:child_count,:infant_count,:fare_source_code,:ticket_type, :contact_first_name,:contact_last_name,:contact_email,:contact_number,:user_id,:total_fare,:stops,:fareType,:contactPhonecode,:contactPostcode,:markup,:total_paid,:extra_service_total)";
    $stmtboking = $conn->prepare($sqlbooking);
    foreach ($pricedItineraries as $pricedItinerary) {
        $originDestinations = $pricedItinerary['OriginDestinationOptions'];
        $stops = 0;
        $stops = count($originDestinations[0]['FlightSegments']);
        $stops -= 1;
        $originData = $originDestinations[0]['FlightSegments'][0]['DepartureAirportLocationCode'];
        $segmentCount = count($originDestinations[0]['FlightSegments']);
        $segmentCount -= 1;
        $destinationData = $originDestinations[0]['FlightSegments'][$segmentCount]['ArrivalAirportLocationCode'];
        $datetime = $originDestinations[0]['FlightSegments'][0]['DepartureDateTime'];
        $direction = $pricedItinerary['DirectionInd'];
        $airline = $pricedItinerary['ValidatingAirlineCode'];
        $faresourcecode = $pricedItinerary['AirItineraryPricingInfo']['FareSourceCode'];
        $fareType = $pricedItinerary['AirItineraryPricingInfo']['FareType'];
        $ticketType = $pricedItinerary['TicketType'];
        $totalFare = $pricedItinerary['AirItineraryPricingInfo']['ItinTotalFare']['TotalFare']['Amount'];
        //-----------
        $objUser = new Users();
        // 67 LINE STARTED
        // $userDetails = $objUser->getUserDetails($userId);
        $userDetails = array(
            "email" => $contactEmail,
            "first_name" => $contactFirstName,
            "last_name" => $contactLastName
        );
        
        $objMarkup = new Markup();
        $markupDetails = $objMarkup->getMarkupDetails(1);
        $markupIPGDetails = $objMarkup->getSettingDetails();

        $ipg_percentage = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
        $ipg_percentage->bindValue(':key', "ipg_transaction_percentage");
        $ipg_percentage->execute();
        $ipg_percentage_setting = $ipg_percentage->fetch(PDO::FETCH_ASSOC);

        $ticketing_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
        $ticketing_fee->bindValue(':key', "ticketing_fee");
        $ticketing_fee->execute();
        $ticketing_fee_setting = $ticketing_fee->fetch(PDO::FETCH_ASSOC);

        $markupPercentage = ($markupDetails['commission_percentage'] / 100) * $totalFare;
        $markupPercentage += $ticketing_fee_setting['value'];
        $total_price = $markupPercentage + $totalFare;
        $ipg_trasaction_percentage = ($ipg_percentage_setting['value'] / 100) * $total_price;
        $markupPercentage += $ipg_trasaction_percentage;
        
        $markup =  $markupPercentage;
    
        // $markup =  ($markupDetails['commission_percentage'] / 100) * $totalFare;

        //-----------
        if (empty($originData) || empty($destinationData) || empty($datetime) || empty($direction) || empty($airline) || empty($faresourcecode) || empty($fareType) || empty($totalFare)) {
            $errorMessage = 'Flight details not get ';
            $errors = $errorMessage;
            $response = array(
                'errors' => $errors
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

        $stmtupdate = $conn->prepare('UPDATE payment_user SET booking_id = :booking_id WHERE fsc = :fsc');
        $stmtupdate->bindParam(':booking_id', $tempBookingId);
        $stmtupdate->bindParam(':fsc', $faresourcecode);
        $stmtupdate->execute();

        $amountToDebit  =   $total_paid;
        

        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :booking_id');
        $stmtbookingid->execute(array('booking_id' => $tempBookingId));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);

        // ALL ABOUT TRAVELLERS DETAILS STARTS FROM HERE
            if ($tempBookingId) {

                foreach ($originDestinations as $originDestination) {
                    $flightSegments = $originDestination['FlightSegments'];
                    foreach ($flightSegments as $index => $flightSegment) {
                        $flightNo = $flightSegment['FlightNumber'];
                        $bookingId = $tempBookingId;
                        $cabinfarefamilyPreference = $flightSegment['CabinClassText'];
                        $depLocation = $flightSegment['DepartureAirportLocationCode'];
                        $depDate = $flightSegment['DepartureDateTime'];
                        $arrivalLocation = $flightSegment['ArrivalAirportLocationCode'];
                        $arrivalDate = $flightSegment['ArrivalDateTime'];
                        $journeyduration = $flightSegment['JourneyDuration'];
                        $airline = $pricedItinerary['ValidatingAirlineCode'];
                        $eticket = $flightSegment['Eticket'];

                        //========
                        $cabin_class_mail    =    $flightSegment['CabinClassCode'];
                        if ($cabin_class_mail == 'Y') {
                            $cabinPreference   = "Economy";
                        } elseif ($cabin_class_mail == 'S') {
                            $cabinPreference   = "Premium";
                        } elseif ($cabin_class_mail == 'C') {
                            $cabinPreference   = "Business";
                        } elseif ($cabin_class_mail == 'F') {
                            $cabinPreference   = "First";
                        }
                        //==============
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
                $TotBaggage =   0;
                $Totmeals   =   0;
                // Bind parameters and execute the statement for each adult
                for ($i = 1; $i <= $adultCount; $i++) {
                    $firstName = $data['firstName' . $i];
                    $lastName = $data['lastName' . $i];
                    $dob = $data['adultDOB' . $i];
                    $parsedDOB = DateTime::createFromFormat('Y-m-d', $dob);
                    $formattedDOB = $parsedDOB ? $parsedDOB->format('Y-m-d') : null;
                    $passportNo =  $data['passportNo' . $i];
                    $passpostExp = $data['pasprtExp' . $i];
                    $bookingID = $bookingData['id'];
                    $title = $data['sirLable' . $i];
                    $gender = $data['gender' . $i];
                    $issuingCountry = $data['issuingCountry' . $i];
                    $passengerType = "ADT";
                    $nationality = $data['nationality' . $i];

                    if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                        $errorMessage = 'Please fill in all mandatory fields for adult';
                        header('Content-Type: application/json');
                        $errors = $errorMessage;
                        $response = array(
                            'errors' => $errors
                        );
                        echo json_encode($response);
                        exit();
                    }

                    /*
                    if (isset($data['baggageService' . $i])) {
                        $baggageServiceData = explode('/', $data['baggageService' . $i]);
                        if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                            $baggageID = $baggageServiceData[0];
                            $baggageDescription = $baggageServiceData[1];
                            $baggageAmount = $baggageServiceData[2];
                            $TotBaggage +=  $baggageAmount;
                        } else {
                            $baggageID = "";
                            $baggageDescription = "";
                            $baggageAmount = "";
                            $TotBaggage +=  $baggageAmount;
                        }
                    } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                        $TotBaggage +=  $baggageAmount;
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
                    
                    */
                    //============================= nafees
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
                        } else {
                            $mealId = "";
                            $mealDescription = "";
                            $mealAmount = "";
                        }
                    } else {
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount = "";
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
                        } else {
                            $mealReturnId = "";
                            $mealReturnDescription = "";
                            $mealReturnAmount = "";
                        }
                    } else {
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount = "";
                    }




                    //===============================
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
                    $bookingID = $bookingData['id'];
                    $title = $data['sirLableChild' . $i];
                    $gender = $data['genderChild' . $i];
                    $issuingCountry = $data['issuingcountryChild' . $i];
                    $passengerType = "CHD";
                    $nationality = $data['nationalityChild' . $i];
                    if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                        // Handle the validation error (e.g., show an error message or redirect back with an error)
                        // For example, you can redirect back to the form page with an error message
                        $errorMessage = 'Please fill in all mandatory fields for child';

                        $errors = $errorMessage;
                        $response = array(

                            'errors' => $errors
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
                        } else {
                            $mealId = "";
                            $mealDescription = "";
                            $mealAmount = "";
                        }
                    } else {
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount = "";
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
                        } else {
                            $mealReturnId = "";
                            $mealReturnDescription = "";
                            $mealReturnAmount = "";
                        }
                    } else {
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount = "";
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
                    $bookingID = $bookingData['id'];
                    $title = $data['sirLableInfant' . $i];
                    $gender = $data['genderInfant' . $i];
                    $issuingCountry = $data['issuingcountryInfant' . $i];
                    $nationality = $data['nationalityinfant' . $i];
                    $passengerType = "INF";


                    if (empty($firstName) || empty($lastName) || empty($dob) || empty($passportNo) || empty($passpostExp) || empty($title) || empty($gender) || empty($issuingCountry) || empty($nationality) || empty($bookingID)) {
                        // Handle the validation error (e.g., show an error message or redirect back with an error)
                        // For example, you can redirect back to the form page with an error message
                        $errorMessage = 'Please fill in all mandatory fields for infant ';

                        header('Content-Type: application/json');
                        $errors = $errorMessage;
                        $response = array(

                            'errors' => $errors
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
        // ALL ABOUT TRAVELLERS DETAILS ENDS FROM HERE

        // }
    }

    //---------------------------------booking API--------------------------------
    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and id= :id');

    $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'], 'id' => $tempBookingId));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    $fsc = $bookingData['fare_source_code'];


    if( $bookingData['fare_type'] != 'WebFare' ) {
        if (isset($fsc)) {
            $codeWithoutPlus = substr($bookingData['contact_phonecode'], 1);
            $stmt = $conn->prepare("SELECT * FROM travellers_details Where flight_booking_id = :bookingId");
            $stmt->execute(array('bookingId' => $bookingData['id']));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $endpoint   =   'v1/Book/Flight';
            $apiEndpoint = APIENDPOINT . $endpoint;
            $bearerToken   =   BEARER;
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
                    ),
                    "PassengerNationality" => $row['nationality'],
                );
                if (!empty($extraServices) &&  $bookingData['fare_type'] == "WebFare") {
                    $passenger["ExtraServices1_1"] = $extraServices;
                }
                $passengerDetails[] = $passenger;
            }
            $requestData = array(
                "FareSourceCode" =>  $fsc,
                "ClientMarkup" => $markup,
                "TravelerInfo" => array(
                    "AirTravelers" => $passengerDetails,
                    "CountryCode" => $codeWithoutPlus,
                    "PhoneNumber" => $bookingData['contact_number'],
                    "Email" => $bookingData['contact_email'],
                    "PostCode" => $bookingData['contact_postcode']
                ),
                "Target" => TARGET,
            );
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
            if ($response) {
                $responseData = json_decode($response, true);
                $objBook->_writeLog('-------------Public/Private Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                $objBook->_writeLog("API URL Response: ".$apiEndpoint, 'temp_booking_save.txt');
                $objBook->_writeLog(print_r($responseData, true), 'temp_booking_save.txt');
                $objBook->_writeLog("", 'temp_booking_save.txt');
                $objBook->_writeLog('-------------Public/Private Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');

            }
        }


        $resSuccess  = $responseData['Data']['Success'];
        $fairtype = $bookingData['fare_type'];
        if (!empty($responseData['Data']['Errors'])) {
            $errMsg = $responseData['Data']['Errors'][0]['Message'];
            $errCDE = $responseData['Data']['Errors'][0]['Code'];
            if (empty($errMsg)) {
                $errMsg = $responseData['Data']['Errors']['Message'];
                $errCDE = $responseData['Data']['Errors']['Code'];
            }
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
            $err_code = $errCDE;
            $fairtype = $bookingData['fare_type'];
            $booking_status = $responseData['Data']['Status'];
            $ticket_status = $responseData['Data']['Status'];
            $id = $bookingData['id'];
            $stmtInsert->bindParam(':book_id', $id);
            $stmtInsert->bindParam(':err_code', $err_code);
            $stmtInsert->bindParam(':err_msg', $errMsg);
            $stmtInsert->bindParam(':fare_type', $fairtype);
            $stmtInsert->bindParam(':book_status', $booking_status);
            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
            $stmtInsert->execute();
            
            $response = array(
                'bookingid' => $tempBookingId,
                'BookStatus' => "Failed",
                'ticketstatus' => "Failed",
                'faretype' => $fairtype,
                'errors' => $errMsg,
                'errCde' => $errCDE
            );
            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            echo json_encode($response);
            exit;
        } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "CONFIRMED")) {
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
    
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
    
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
    
            $orderstatus = $responseData['Data']['Success'];
            $response = array(
                'BookStatus' => $booking_status,
                'faretype' => $fairtype,
                'bookingid' => $tempBookingId,
            );
            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            $objBook->_writeLog("Booking Confirmed: ", 'temp_booking_save.txt');
            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            echo json_encode($response);
            exit;
            // $logResSus =   $booking_status;
            // $objBook->_writeLog('Success Received\n' . $logResSus, 'booking.txt');
        } elseif (($responseData['Data']['Success']) && ($responseData['Data']['Status'] == "BOOKINGINPROCESS")) {
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
    
            $orderstatus = $responseData['Data']['Success'];
            $response = array(
                'BookStatus' => $booking_status,
                'faretype' => $fairtype,
                'bookingid' => $tempBookingId,
            );
            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            $objBook->_writeLog("Booking BOOKINGINPROCESS: ", 'temp_booking_save.txt');
            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
        
            echo json_encode($response);
            exit;
            
        } elseif (empty($responseData['Data']['Success'])) {
            $errCDE = '';
            $errMsg = '';
            if (!empty($responseData['Data']['Errors'])) {
                $errMsg = $responseData['Data']['Errors'][0]['Message'];
                $errCDE = $responseData['Data']['Errors'][0]['Code'];
                if (empty($errMsg)) {
                    $errMsg = $responseData['Data']['Message'];
                }
            }
            $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date , ticket_time_limit = :ticketTimeLimit WHERE id = :id');
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
    
            $stmtupdate->bindParam(':mfreference', $mfreference);
            $stmtupdate->bindParam(':traceId', $traceId);
            $stmtupdate->bindParam(':booking_status', $booking_status);
            $stmtupdate->bindParam(':booking_date', $booking_date);
            
            $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
            $stmtupdate->bindParam(':id', $id);
            $stmtupdate->execute();
            $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
    
            $err_code = $errCDE;
            $fairtype = $bookingData['fare_type'];
            $booking_status = $responseData['Data']['Status'];
            $ticket_status = $responseData['Data']['Status'];
    
            $id = $bookingData['id'];
            if (empty($errMsg)) {
                $errMsg = "Null Status Received from Airline";
                $errCDE = "000";
            }
            $stmtInsert->bindParam(':book_id', $id);
            $stmtInsert->bindParam(':err_code', $err_code);
            $stmtInsert->bindParam(':err_msg', $errMsg);
            $stmtInsert->bindParam(':fare_type', $fairtype);
            $stmtInsert->bindParam(':book_status', $booking_status);
            $stmtInsert->bindParam(':ticket_sts', $ticket_status);
            $stmtInsert->execute();
    
            $response = array(
                'bookingid' => $tempBookingId,
                'BookStatus' => "Failed",
                'ticketstatus' => "Failed",
                'faretype' => $fairtype,
                'errors' => $errMsg,
                'status' => $responseData['Data']['Status'],
                'errCde' => $errCDE
            );
            
            $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
            $objBook->_writeLog("", 'temp_booking_save.txt');
            $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;       
        } else {
            $booking_date = date('Y-m-d H:i:s');
            $mfreference = $responseData['Data']['UniqueID'];
            $traceId = $responseData['Data']['TraceId'];
            $booking_status = $responseData['Data']['Status'];
    
            $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
            $id = $bookingData['id'];
    
            if (empty($mfreference) && ($booking_status == "PENDING")) {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , booking_status = :booking_status WHERE id = :id');
    
                $stmtupdate->bindParam(':booking_date', $booking_date);
                
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = "Pending without MF number Direct failure as per api Received";
                    $errCDE = "001";
                }
                $stmtupdate->execute();
                $response = array(
                    'bookingid' => $tempBookingId,
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                
                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
    
                $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                $objBook->_writeLog("Empty Reference Number + status PENDING", 'temp_booking_save.txt');
                $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                $objBook->_writeLog("", 'temp_booking_save.txt');
                $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }else if (empty($mfreference) && ($booking_status == "NotBooked")) {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date , booking_status = :booking_status WHERE id = :id');
    
                $stmtupdate->bindParam(':booking_date', $booking_date);
                
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = "Not Booked, Direct failure as per api Received";
                    $errCDE = "002";
                }
                $stmtupdate->execute();
                $response = array(
                    'bookingid' => $tempBookingId,
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                
                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
    
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];
    
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
                
                $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                $objBook->_writeLog("", 'temp_booking_save.txt');
                $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');

                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }elseif (($responseData['Data']['Success']) && in_array($responseData['Data']['Status'], ["Booked", "Ticketed", "Ticket-In Process", "Pending"])) {
    
                // echo $responseData['Data']['Status'];  
                //log write ,booking sts update ,booking date ,markup value
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET mf_reference = :mfreference, trace_id = :traceId ,booking_status = :booking_status, booking_date = :booking_date ,ticket_time_limit = :ticketTimeLimit WHERE id = :id');
    
                // Set the values
                // Set the current datetime for booking_date
                $booking_date = date('Y-m-d H:i:s');
    
    
    
                $mfreference = $responseData['Data']['UniqueID'];
                $traceId = $responseData['Data']['TraceId'];
                $booking_status = $responseData['Data']['Status'];
    
                $TktTimeLimit = $responseData['Data']['TktTimeLimit'];
                $id = $bookingData['id'];
    
                // Bind the parameters
                $stmtupdate->bindParam(':mfreference', $mfreference);
                $stmtupdate->bindParam(':traceId', $traceId);
                $stmtupdate->bindParam(':booking_status', $booking_status); //
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':ticketTimeLimit', $TktTimeLimit);
                $stmtupdate->bindParam(':id', $id);
    
    
    
                // Execute the query
                $stmtupdate->execute();
                //$orderstatus = "order success"; //but cron needed for finalised status
                $orderstatus = $responseData['Data']['Success'];
                //=============
                $logResSus =   $booking_status;
                $objBook->_writeLog('Success in process Received\n' . $logResSus, 'booking.txt');
                $response = array(
                        'BookStatus' => $booking_status,
                        'faretype' => $fairtype,
                        'bookingid' => $tempBookingId,
                    );
                    $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                    $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                    $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                    $objBook->_writeLog("", 'temp_booking_save.txt');
                    $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                    echo json_encode($response);
                    exit;
    
            }else {
                $stmtupdate = $conn->prepare('UPDATE temp_booking SET booking_date = :booking_date ,booking_status = :booking_status WHERE id = :id');
                $stmtupdate->bindParam(':booking_date', $booking_date);
                $stmtupdate->bindParam(':booking_status', $booking_status);
                $stmtupdate->bindParam(':id', $id);
                if (empty($errMsg)) {
                    $errMsg = $booking_status;
                    $errCDE = "003"; //custom error codes
                }
                // Execute the query
                $stmtupdate->execute();
                $response = array(
                    'bookingid' => $tempBookingId,
                    'BookStatus' => "Failed",
                    'ticketstatus' => "Failed",
                    'faretype' => $fairtype,
                    'errors' => $errMsg,
                    'errCde' => $errCDE
                );
                $stmtInsert = $conn->prepare('INSERT INTO `booking_errors` (`id`, `booking_Id`, `err_code`, `err_msg`, `fare_type`, `book_status`, `ticket_sts`, `created_date`, `update_at`) VALUES (NULL, :book_id, :err_code, :err_msg,:fare_type ,:book_status, :ticket_sts, current_timestamp(), current_timestamp());');
    
                $err_code = $errCDE;
                $fairtype = $bookingData['fare_type'];
                $booking_status = $responseData['Data']['Status'];
                $ticket_status = $responseData['Data']['Status'];
                $id = $bookingData['id'];
                
                $stmtInsert->bindParam(':book_id', $id);
                $stmtInsert->bindParam(':err_code', $err_code);
                $stmtInsert->bindParam(':err_msg', $errMsg);
                $stmtInsert->bindParam(':fare_type', $fairtype);
                $stmtInsert->bindParam(':book_status', $booking_status);
                $stmtInsert->bindParam(':ticket_sts', $ticket_status);
                $stmtInsert->execute();
                
                $objBook->_writeLog('-------------If there are errors Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                $objBook->_writeLog("Empty Reference Number + status NotBooked", 'temp_booking_save.txt');
                $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
                $objBook->_writeLog("", 'temp_booking_save.txt');
                $objBook->_writeLog('-------------If there are errors Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
            
        }
    } else {
        $response = array(
            'bookingid' => $tempBookingId,
            'BookStatus' => "CONFIRMED",
            'ticketstatus' => "Failed",
            'errors' => ""
        );
        $objBook->_writeLog('-------------Webfare holding temp Open ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
        $objBook->_writeLog(" ", 'temp_booking_save.txt');
        $objBook->_writeLog(print_r($response, true), 'temp_booking_save.txt');
        $objBook->_writeLog("", 'temp_booking_save.txt');
        $objBook->_writeLog('-------------Webfare holding temp Close ' . date('l jS \of F Y h:i:s A') . '-------------', 'temp_booking_save.txt');
        echo json_encode($response);
        exit;
    }

    // $response = array(
    //     'BookStatus' => $booking_status,
    //     'faretype' => $fairtype,
    //     'bookingid' => $tempBookingId,
    // );

    // echo json_encode($response);
    // exit;

    // unset($_SESSION['search_values']);
    // unset($_SESSION['response']);
    // unset($_SESSION['Revalidateresponse']);
    // unset($_SESSION['name-character-count']);
    // unset($_SESSION['travel-depdate']);
    // unset($_SESSION['fsc']);
    // unset($_SESSION['totalService']);
    // unset($_SESSION['travel-return-depdate']);
    // unset($_SESSION['adultCount']);
    // unset($_SESSION['revalidationApi']);

    // header('Content-Type: application/json');
    // $response = array(
    //     'booking_id' => $tempBookingId
    // );
    // echo json_encode($response);
    // exit;
}