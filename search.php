<?php
// error_reporting(0);
session_start();
// include('loading-popup.php');
include_once('includes/common_const.php');
require_once("includes/header.php");
require_once('includes/dbConnect.php');
// echo '<script>
//     document.addEventListener("DOMContentLoaded", function() {
//         showLoadingPopup();
//     });
// </script>';
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//    print_r($_POST['tab']);die();

    $_SESSION['search_values'] = $_POST;
   
    $airTripType = $_POST['tab'];
    $cabinPreference = $_POST['cabin-preference'];
    if($_POST['adult'])
    $adultCount = $_POST['adult'];
    else
    $adultCount = 0;
    if($_POST['child'])
    $childCount = $_POST['child'];
    else
    $childCount=0;
    if($_POST['infant'])
    $infantCount = $_POST['infant'];
    else
    $infantCount=0;

    $originLocation = $_POST['airport'];
    $originLocationCode = explode("-", $originLocation);

    $destinationLocation = $_POST['arrivalairport'];
    $destinationLocationCode = explode("-", $destinationLocation);
    // print_r($originLocationCode[0]);
    // print_r($destinationLocationCode[0]);
    $fromDate = $_POST['from'];
    $departureDate = date("Y-m-d", strtotime($fromDate));
     if($_POST['to']){
        $toDate = $_POST['to'];
        $ReturnDate = date("Y-m-d", strtotime($toDate));

     }
    if ($adultCount < 1 || !is_numeric($adultCount)) {
        // Display an error message
        echo 'Please enter a valid number of adult passengers.';
        exit;
    }
    // if ($airTripType === 'OneWay') {
       // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
       // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
            $endpoint   =   'v2/Search/Flight';
         $apiEndpoint = APIENDPOINT.$endpoint;
         $bearerToken   =   BEARER;

        // Construct the API request payload
        $requestData = array(

            'OriginDestinationInformations' => array(
                array(
                    'DepartureDateTime' => $departureDate,
                    'OriginLocationCode' =>  trim($originLocationCode[0]),
                    'DestinationLocationCode' => trim($destinationLocationCode[0])
                    // 'OriginLocationCode' =>  'COK',
                    // 'DestinationLocationCode' => 'DXB'
                )
            ),
            'TravelPreferences' => array(
                //   'MaxStopsQuantity' => 'Direct',
                // 'MaxStopsQuantity' => 'OneStop',
                  'MaxStopsQuantity' => 'All',
                'CabinPreference' => $cabinPreference,
                'AirTripType' => $airTripType
            ),
            //    'PricingSourceType' => 'Public',
               'PricingSourceType' => 'All',
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
            'Target' => TARGET,
            'page_size'=>2,
            'page_number'=>1

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
        if ($airTripType === 'Return') {
            $returnDetails = array(
               
                'DepartureDateTime' => $ReturnDate,
                'OriginLocationCode' => trim($destinationLocationCode[0]),
                'DestinationLocationCode' =>  trim($originLocationCode[0])            
            );
            array_push($requestData['OriginDestinationInformations'], $returnDetails);
        }
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
            $_SESSION['response'] = $responseData;
            // echo '<pre>';
            // print_r($responseData);
            // echo '</pre>';


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
    // }

   



    // echo '<pre>';
    // print_r($responseData);
    // echo '</pre>';


// Determine the total number of flights
$totalFlights = count($pricedItineraries);

// Set the number of flights to display per page
$flightsPerPage = 15;

// Calculate the total number of pages
$totalPages = ceil($totalFlights / $flightsPerPage);

// Retrieve the current page number from the request or use a default value
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting index of the flights to display on the current page
$startIndex = ($page - 1) * $flightsPerPage;

// Get the flights for the current page
$currentPageFlights = array_slice($pricedItineraries, $startIndex, $flightsPerPage);
// print_r($_SESSION['search_values']);
$stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
$stmtlocation->execute(array('airport_code' =>$originLocationCode[0] ));
$airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);


$stmtlocation->execute(array('airport_code' =>$destinationLocationCode[0] ));
$airportDestinationLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
 
?>




