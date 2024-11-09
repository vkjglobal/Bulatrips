<?php
    session_start();
    error_reporting(0);
    require_once('includes/dbConnect.php');

     $bookingId = $_POST['booking-id'];
    if(isset($bookingId)){
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid and user_id = :userid');
        $stmtbookingid->execute(array('bookingid' => $bookingId,'userid' => $_SESSION['user_id']));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        
        // echo "<pre>";
        //     print_r($bookingData);
        // die;

        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Booking/Cancel';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
    
    
        // Construct the API request payload
        $requestData = array(          
            'UniqueID' => $bookingData['mf_reference'],
            'Target' => 'Test',
            // 'ConversationId' => '',
        );

        // echo "<pre>";
        //     print_r($requestData);
        // die;
      
       
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
        // $pricedItineraries = $responseData['Data']['PricedItineraries'];

    }
    