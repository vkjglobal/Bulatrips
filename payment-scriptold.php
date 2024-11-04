<?php
require_once('includes/dbConnect.php');
session_start();

// Get the form data
// $adultCount = $_POST['adultCount'];
$fsc =$_POST['fsc'];
if (isset($fsc )) {
    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE fare_source_code = :farecode and user_id = :userid');

    $stmtbookingid->execute(array('farecode' => $_SESSION['fsc'],'userid' => $_SESSION['user_id']));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
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
    $extraServices = [];

    if ($extraMealId !== 0) {
        $extraServices[] = array(
            "ExtraServiceId" => $extraMealId,
            "Quantity" => 1,
            "Key" => "string"
        );
    }

    if ($extraBaggageId !== 0) {
        $extraServices[] = array(
            "ExtraServiceId" => $extraBaggageId,
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
        if (!empty($extraServices) &&  $bookingData['fare_type'] == "webfare") {
            $passenger["ExtraServices1_1"] = $extraServices;
        }
        $passengerDetails[] = $passenger;
    }

    ////////////////////////////////////


    $requestData = array(
        "FareSourceCode" =>  $fsc,
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
        "LccHoldBooking" => true
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

    $response = curl_exec($ch);
    curl_close($ch);

    // Handle the API response
   
    if ($response) {
        $responseData = json_decode($response, true);

    }

    // $responseData  = $_SESSION['validateresponse'];
    echo '<pre>';
    print_r($responseData);
    echo '</pre>';
    if(isset($responseData['Data']['Errors'][0])){
        echo "search again";

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

    }
    else{
        echo "search again";
    }
    
    if($bookingData['fare_type'] != "webfare"){
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
            echo "search again";
    
        }else{
            echo "ticket sucess";
        }
        echo '<pre>';
        print_r($responseTicketData);
        echo '</pre>';

    }

    // if($responseData['Data']['Success']==1){
        echo json_encode(['success' => true]);
    // }
    

}


// Send a success response
//  $response = ['success' => true, 'faresource' => $bookingData['id']];
// echo json_encode(['success' => true]);

?>
