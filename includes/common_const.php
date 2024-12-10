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
?>