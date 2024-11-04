<?php

// Get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];
echo $user_ip;
// Create the API URL
$api_url = "http://ip-api.com/json/{$user_ip}";

// Send a request to the API
$response = file_get_contents($api_url);

// Decode the JSON response
$location_data = json_decode($response);

// Check if the request was successful and the location data is available
if ($location_data && $location_data->status == "success") {
    $user_country = $location_data->country;
    $user_region = $location_data->regionName;
    $user_city = $location_data->city;

    // You can use $user_country, $user_region, and $user_city as needed
    echo "User is located in $user_city, $user_region, $user_country.";
} else {
    // Unable to determine the user's location
    echo "Unable to determine the user's location.";
}
?>
