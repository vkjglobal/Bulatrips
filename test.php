
<?php
// API endpoint URL
$url = 'https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat';

// Fetch airport data from API
$data = file_get_contents($url);
$airports = explode("\n", $data);

// Generate select box
echo '<select name="departure_airport">';

foreach ($airports as $airport) {
    $fields = explode(',', $airport);
    $airport_name = $fields[1];
    $airport_code = $fields[4];

    // Skip if airport has no IATA code
    if (empty($airport_code)) {
        continue;
    }

    echo '<option value="' . $airport_code . '">' . $airport_name . ' (' . $airport_code . ')' . '</option>';
}

echo '</select>';
?>


<?php
$accessKey = 'c7e2aa89b12fd49ee4c3e5c3f83b63dd';
$apiUrl = 'https://api.aviationstack.com/v1/airlines';

// Create a cURL session
$ch = curl_init();

// Set the cURL options
// Set the cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  'Accept: application/json',
  'Authorization: AccessKey: ' . $accessKey
));


// Execute the cURL request
$response = curl_exec($ch);

// Close the cURL session
curl_close($ch);

// Parse the JSON response
$data = json_decode($response, true);

// Display the complete API response
echo '<pre>';
print_r($data);
echo '</pre>';
?>


<?php
$apiUrl = 'https://opensky-network.org/api/airlines';

// Create a cURL session
$ch = curl_init();

// Set the cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request
$response = curl_exec($ch);

// Close the cURL session
curl_close($ch);

// Parse the JSON response
$data = json_decode($response, true);

// Check if the response was successful
if (is_array($data)) {
  // Iterate over the airlines and retrieve the name and code
  foreach ($data as $airline) {
    if (isset($airline['name'])) {
      $name = $airline['name'];
      $iataCode = isset($airline['iata']) ? $airline['iata'] : '';
      $icaoCode = isset($airline['icao']) ? $airline['icao'] : '';
      
      // Do whatever you want with the name, IATA code, and ICAO code (e.g., store in a database, display on the page)
      echo 'Airline: ' . $name . ' - IATA Code: ' . $iataCode . ' - ICAO Code: ' . $icaoCode . '<br>';
    }
  }
} else {
  // Handle the case when the API request was not successful
  echo 'Failed to retrieve airline data.';
}
?>




