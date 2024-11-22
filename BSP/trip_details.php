<?php
define("BEARER", "18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560");
define("TARGET", "Test");
define("APIENDPOINT","https://restapidemo.myfarebox.com/api/");

session_start();
error_reporting(1);
$endpoint   =   'v1.1/TripDetails/{MFRef}';
$apiEndpoint = APIENDPOINT.$endpoint;
$bearerToken   =   BEARER;

$mfRef = "MF28846424";
$apiEndpoint = str_replace('{MFRef}', $mfRef, $apiEndpoint);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $bearerToken
));

$response = curl_exec($ch);
curl_close($ch);

echo "<pre>";
    print_r(json_decode($response));
echo "</pre>";
?>