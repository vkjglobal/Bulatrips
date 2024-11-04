<?php
// error_reporting(0);
session_start();
// include('loading-popup.php');
require_once('includes/dbConnect.php');


    // $_SESSION['search_values'] = $_POST;
    $fsCode = $_POST['fsc'];
    
    // if ($airTripType === 'OneWay') {
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Revalidate/Flight';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


        // Construct the API request payload
        $requestData = array(          
            'FareSourceCode' => $fsCode,
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

        $response = curl_exec($ch);
        curl_close($ch);

        // Handle the API response
       
        if ($response) {
            $responseData = json_decode($response, true);
            $_SESSION['validateresponse'] = $responseData;
            // echo '<pre>';
            // print_r($responseData);
            // echo '</pre>';

        }
    // }
?>




