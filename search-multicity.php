<?php
// error_reporting(0);
session_start();
// include('loading-popup.php');
require_once("includes/header.php");
require_once('includes/dbConnect.php');
include_once('includes/common_const.php');
$apiEndpoint = APIENDPOINT.'v2/multiCityFaresBETA/Search/Flight';

if (isset($_POST['tripDetails']) && isset($_POST['formData'])) {
    // Retrieve the trip details and form data from the $_POST array
    print_r($_POST);
    $sanitizedTripDetailsJson = $_POST['tripDetails'];
    
    $formData = $_POST['formData'];
    parse_str($formData, $formDatas);

    $cabinPreference = $formDatas['cabin-preference'];
    $airTripType =  $formDatas['tab'];
    if( $formDatas['adult'])
    $adultCount = $formDatas['adult'];
    else
    $adultCount = 0;
    if( $formDatas['child'])
    $childCount = $formDatas['child'];
    else
    $childCount=0;
    if( $formDatas['infant'])
    $infantCount =  $formDatas['infant'];
    else
    $infantCount=0;

    // Decode the JSON data back into an associative array
    $tripDetails = json_decode(urldecode($sanitizedTripDetailsJson), true);
    print_r($tripDetails);

 
   
   


    // Construct the API request payload
    $requestData = array(

        // 'OriginDestinationInformations' => array(
        //     array(
        //         'DepartureDateTime' => $departureDate,
        //         'OriginLocationCode' =>  trim($originLocationCode[0]),
        //         'DestinationLocationCode' => trim($destinationLocationCode[0])
        //     )
        // ),

      
       
        'OriginDestinationInformations' => array(
            array(
                'DepartureDateTime' => '2023-07-31',
                'OriginLocationCode' =>  'COK',
                'DestinationLocationCode' => 'TRV'
            ),
            array(
                'DepartureDateTime' => '2023-08-01',
                'OriginLocationCode' =>  'TRV',
                'DestinationLocationCode' => 'DEL'
            ),
            array(
                'DepartureDateTime' => '2023-08-05',
                'OriginLocationCode' =>  'DEL',
                'DestinationLocationCode' => 'DXB'
            ),
            // Add more arrays here for additional OriginDestinationInformations if needed
        ),
      
        'TravelPreferences' => array(
            //   'MaxStopsQuantity' => 'Direct',
            // 'MaxStopsQuantity' => 'OneStop',
            'MaxStopsQuantity' => 'All',
            'CabinPreference' => $cabinPreference,
            'AirTripType' => 'OneWay'
        ),
        'PricingSourceType' => 'Public',
        //    'PricingSourceType' => 'All',
        // 'IsRefundable' => true,
        'PassengerTypeQuantities' => array(
            array(
                'Code' => 'ADT',
                'Quantity' => $adultCount
            )

        ),
        // 'RequestOptions' => 'Fifty',
        'RequestOptions' => 'TwoHundred',
        'NearByAirports' => true,
        'Nationality' => 'string',
        'Target' => 'Test',
       

        // 'ConversationId' => 'string',
    );
    if ($childCount > 0) {
        $childDetails = array(
            'Code' => 'CHD',
            'Quantity' => $childCount
        );
        array_push($requestData['PassengerTypeQuantities'], $childDetails);
    }

    if ($infantCount > 0) {
        $infantDetails = array(
            'Code' => 'INF',
            'Quantity' => $infantCount
        );
        array_push($requestData['PassengerTypeQuantities'], $infantDetails);
    }
    

    // foreach ($tripDetails as $trip) {
    //     // Initialize variables to store the trip detail values
    //     $tripDetail = array();
    
    //     // Loop through each key-value pair in the $trip array
    //     foreach ($trip as $key => $value) {
    //         // Extract the trip number (e.g., 1, 2, etc.) from the key
    //         $tripNum = substr($key, -1);
    
    //         // Create keys without the number suffix
    //         $baseKey = substr($key, 0, -1);
    
    //         // Store the trip detail value in the $tripDetail array with updated keys
    //         $tripDetail[$baseKey . $tripNum] = $value;
    //     }
   
    //     // Add the current trip detail to the 'OriginDestinationInformations' array
    //     $requestData['OriginDestinationInformationss'][] = array(
    //         'DepartureDateTime' => $tripDetail['departureDate' . $tripNum],
    //         'OriginLocationCode' => $tripDetail['departureFrom' . $tripNum],
    //         'DestinationLocationCode' => $tripDetail['arrivalTo' . $tripNum]
    //     );
    // }
    // Send the API request

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . BEARER
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    // Handle the API response

    if ($response) {
        $responseData = json_decode($response, true);
        $_SESSION['response'] = $responseData;
        echo '<pre>';
        print_r($responseData);
        echo '</pre>';


        if ($responseData['Success'] == 1) {
            if (isset($responseData['Data']['PricedItineraries'])) {
                $pricedItineraries = $responseData['Data']['PricedItineraries'];
            } else {
                echo "PricedItineraries key is missing in the API response.";
            }
        } else {
            echo "API response indicates an error.";
        }
    }
}
