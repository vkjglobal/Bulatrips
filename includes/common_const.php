<?php
// STAGING CREDENTIALS STARTS
define("BEARER", "18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560");
define("APIENDPOINT","https://restapidemo.myfarebox.com/api/");
define("TARGET", "Test");
// STAGING CREDENTIALS ENDS

// LIVE CREDENTIALS STARTS
// define("BEARER", "CBCF85FE-2C4D-4BCA-AD93-AB9509AB9254-54061");
// define("APIENDPOINT","https://restapi.myfarebox.com/api/");
// define("TARGET", "Production");
// LIVE CREDENTIALS ENDS

// WINDCAVE CREDENTIALS STARTS
define("WC_URL", "https://uat.windcave.com/api/v1/");
define("WC_USERNAME","VKJGlobal_Dev_REST");
define("WC_PASSWORD", "cc581f7b69e7817ee13f232b187560639236f29e8f2a969f871225178aa6a74d");
// WINDCAVE CREDENTIALS ENDS


$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];
$fullUrl = $protocol . '://' . $domain."/";

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define("ENVIRONMENT_VAR",$fullUrl."bulatrips/");
} else {
    define("ENVIRONMENT_VAR",$fullUrl);
}




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