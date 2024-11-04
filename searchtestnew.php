<?php
// error_reporting(0);
session_start();
require_once("includes/header.php");
require_once('includes/dbConnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    if ($adultCount < 1 || !is_numeric($adultCount)) {
        // Display an error message
        echo 'Please enter a valid number of adult passengers.';
        exit;
    }
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


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
                //  'MaxStopsQuantity' => 'Direct',
                'MaxStopsQuantity' => 'OneStop',
                // 'MaxStopsQuantity' => 'All',
                'CabinPreference' => $cabinPreference,
                'AirTripType' => $airTripType
            ),
            'PricingSourceType' => 'Public',
            'PricingSourceType' => 'All',
            'IsRefundable' => true,
            'PassengerTypeQuantities' => array(
                array(
                    'Code' => 'ADT',
                    'Quantity' => $adultCount
                )
                // ,
                // array(
                //     'Code' => 'CHD',
                //     'Quantity' => $childCount
                // ),
                // array(
                //     'Code' => 'INF',
                //     'Quantity' => $infantCount
                // )
            ),
            // 'RequestOptions' => 'Fifty',
            'RequestOptions' => 'Fifty',
            'NearByAirports' => true,
            'Nationality' => 'string',
            'Target' => 'Test',
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
    
    
    
    $response = json_decode($response, true);
    $_SESSION['response'] = $response;
    }else{
        $response  = $_SESSION['response'];
    }
    // else{
        
    //     $searchValue = $_SESSION['search_values'];
    //     $airTripType = $searchValue['tab'];
    //     $cabinPreference = $searchValue['cabin-preference'];
    //     if($searchValue['adult'])
    //     $adultCount = $searchValue['adult'];
    //     else
    //     $adultCount = 0;
    //     if($searchValue['child'])
    //     $childCount = $searchValue['child'];
    //     else
    //     $childCount=0;
    //     if($searchValue['infant'])
    //     $infantCount = $searchValue['infant'];
    //     else
    //     $infantCount=0;
    
    //     $originLocation = $searchValue['airport'];
    //     $originLocationCode = explode("-", $originLocation);
    
    //     $destinationLocation = $searchValue['arrivalairport'];
    //     $destinationLocationCode = explode("-", $destinationLocation);
    //     // print_r($originLocationCode[0]);
    //     // print_r($destinationLocationCode[0]);
    //     $fromDate = $searchValue['from'];
    //     $departureDate = date("Y-m-d", strtotime($fromDate));
    //     if ($adultCount < 1 || !is_numeric($adultCount)) {
    //         // Display an error message
    //         echo 'Please enter a valid number of adult passengers.';
    //         exit;
    //     }
    //         $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
    //         $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
    
    
    //         // Construct the API request payload
    //         $requestData = array(
    
    //             'OriginDestinationInformations' => array(
    //                 array(
    //                     'DepartureDateTime' => $departureDate,
    //                     'OriginLocationCode' =>  trim($originLocationCode[0]),
    //                     'DestinationLocationCode' => trim($destinationLocationCode[0])
    //                     // 'OriginLocationCode' =>  'COK',
    //                     // 'DestinationLocationCode' => 'DXB'
    //                 )
    //             ),
    //             'TravelPreferences' => array(
    //                 //  'MaxStopsQuantity' => 'Direct',
    //                 'MaxStopsQuantity' => 'OneStop',
    //                 // 'MaxStopsQuantity' => 'All',
    //                 'CabinPreference' => $cabinPreference,
    //                 'AirTripType' => $airTripType
    //             ),
    //             'PricingSourceType' => 'Public',
    //             'PricingSourceType' => 'All',
    //             'IsRefundable' => true,
    //             'PassengerTypeQuantities' => array(
    //                 array(
    //                     'Code' => 'ADT',
    //                     'Quantity' => $adultCount
    //                 )
    //                 // ,
    //                 // array(
    //                 //     'Code' => 'CHD',
    //                 //     'Quantity' => $childCount
    //                 // ),
    //                 // array(
    //                 //     'Code' => 'INF',
    //                 //     'Quantity' => $infantCount
    //                 // )
    //             ),
    //             // 'RequestOptions' => 'Fifty',
    //             'RequestOptions' => 'Fifty',
    //             'NearByAirports' => true,
    //             'Nationality' => 'string',
    //             'Target' => 'Test',
    //             'page_size'=>2,
    //             'page_number'=>1
    
    //             // 'ConversationId' => 'string',
    //         );
    //         if ($childCount > 0) {
    //             $childDetails = array(
    //                 'Code' => 'CHD',
    //                 'Quantity' => $childCount
    //             );
    //             array_push($requestData['PassengerTypeQuantities'], $childDetails);
    //         }
            
    //         if ($infantCount > 0) {
    //             $infantDetails = array(
    //                 'Code' => 'INF',
    //                 'Quantity' => $infantCount
    //             );
    //             array_push($requestData['PassengerTypeQuantities'], $infantDetails);
    //         }
    //         // Send the API request
    
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //             'Content-Type: application/json',
    //             'Authorization: Bearer ' . $bearerToken
    //         ));


    // }

//     $response = curl_exec($ch);
//     curl_close($ch);



// $response = json_decode($response, true);
// $_SESSION['response'] = $response;
echo '<pre>';
print_r($response);
echo '</pre>';
$flightSegmentList = $response['Data']['FlightSegmentList'];

// Determine the total number of flights
$totalFlights = count($flightSegmentList);

// Set the number of flights to display per page
$flightsPerPage = 5;

// Calculate the total number of pages
$totalPages = ceil($totalFlights / $flightsPerPage);

// Retrieve the current page number from the request or use a default value
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting index of the flights to display on the current page
$startIndex = ($page - 1) * $flightsPerPage;

// Get the flights for the current page
$currentPageFlights = array_slice($flightSegmentList, $startIndex, $flightsPerPage);

// Display the flights
foreach ($currentPageFlights as $flight) {
    // Display flight details (e.g., DepartureAirportLocationCode, ArrivalAirportLocationCode, etc.)
    echo "Departure: " . $flight['DepartureAirportLocationCode'] . "<br>";
    echo "Arrival: " . $flight['ArrivalAirportLocationCode'] . "<br>";
    echo "Departure DateTime: " . $flight['DepartureDateTime'] . "<br>";
    echo "Arrival DateTime: " . $flight['ArrivalDateTime'] . "<br>";
    // ...
    echo "<br>";
}

// Display pagination links
for ($i = 1; $i <= $totalPages; $i++) {
    echo '<a href="?page=' . $i . '">' . $i . '</a> ';
}
?>
