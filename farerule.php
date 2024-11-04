<?php

// Start session
 include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
        $value=htmlspecialchars($_POST['value']);
        $count=$_POST['count'];

      //  $value  =   "RWwrUkFsTkxaQkh1WHd1Uk5QTVpRZzNOYitIVTk1c01VWmUwWk9IRklUUU9iR0VvWjFKQjdEUG9FU2UwQ2hROGYraHFmSjM3UG95a1dHMWQvcUVlM3diUU1LelptT1RWbktxOU9la1FVY0loNE9UaUR3VEpGeGZnczR6VkR1emF6d0xpQnFSWHNOS05ISGh1b2xETnhBPT0";

       // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/FlightFareRules';
       // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
             $endpoint   =   'v1/FlightFareRules';
         $apiEndpoint = APIENDPOINT.$endpoint;
         $bearerToken   =   BEARER;
    
        // Construct the API request payload
        $requestData = array(          
            'FareSourceCode' => $value,
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
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        // Handle the API response
       
        if ($response) {
            $responseData = json_decode($response, true);
               
        } 
      
         $fareRules = $responseData['Data']['FareRules'];
         $data = [
            'count' => $count,
            'fareRules' => $fareRules,
        ];
        // echo $responseData;
        echo json_encode($data);
       

?>