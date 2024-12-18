<?php

define("BEARER", "18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560");
define("TARGET", "Test");
define("APIENDPOINT","https://restapidemo.myfarebox.com/api/");


function getAirPortLocationsByAirportCode($airPortCode,$conn) {
    $airport_country = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code LIKE :code');
    $airport_country->bindParam(':code', $airPortCode);
    $airport_country->execute();
    $data = $airport_country->fetch(PDO::FETCH_ASSOC);
    return $data;
}

function getConversionRate() {
    $url = "https://v6.exchangerate-api.com/v6/82190c2eeaf28578f89f52d7/latest/INR";
    $response = file_get_contents($url);
    $usd_converion_rate = 1;
    if ($response !== false) {
        $data = json_decode($response, true);
        $usd_converion_rate = $data['conversion_rates']['USD'];
    }
    return $usd_converion_rate;
}

function convertMinutesToTimeFormat($minutes) {
    // Calculate days, hours, and remaining minutes
    $days = floor($minutes / 1440);  // 1 day = 1440 minutes
    $hours = floor(($minutes % 1440) / 60); // Remaining hours after extracting days
    $remainingMinutes = $minutes % 60; // Remaining minutes after extracting hours

    $timeFormat = '';
    if ($days > 0) {
        $timeFormat .= $days . ' days ';
    }
    if ($hours > 0) {
        $timeFormat .= $hours . ' hours ';
    }
    if ($remainingMinutes > 0) {
        $timeFormat .= $remainingMinutes . ' minutes';
    }
    return $timeFormat;
}

?>