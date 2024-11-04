<?php
include_once('includes/class.Airport.php');
include_once('includes/class.Airline.php');
include_once('includes/class.Markup.php');


$objAirport = new Airport(); // Create an object of the Airport class

$airportCode = 'TRV'; // Replace this with the actual airport code you want to fetch

$airportDetails = $objAirport->getAirportDetails($airportCode);
// print_r($airportDetails);

$objAirline = new Airline(); // Create an object of the Airport class

$code = "AI"; // Replace this with the actual airport code you want to fetch

$airlineDetails = $objAirline->getAirlineDetails($code);
// print_r($airlineDetails);
// var_dump($code);

$objMarkup = new Markup();
$markupDetails = $objMarkup->getMarkupDetails(1);
print_r($markupDetails);




?>