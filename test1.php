<!DOCTYPE html>
<html>
<head>
    <title>Worldwide Airport Locations</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<!-- <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css"> -->

</head>
<body>
    <h1>Select Airport</h1>

    <form>
        <label for="airport">Airport:</label>
        <select id="airport" name="airport">
            <?php
            // Set up AviationStack API credentials
            $apiKey = '024279ff9d4567f33c7f06e96a1c31a0'; // Replace with your AviationStack API key

            // Create a cURL handle
            $ch = curl_init();

            // Set the API endpoint URL
            $url = "http://api.aviationstack.com/v1/airports?access_key=$apiKey";

            // Set the cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the cURL request
            $response = curl_exec($ch);

            // Close the cURL handle
            curl_close($ch);

            // Decode the JSON response
            $airportData = json_decode($response, true);

            // Generate dropdown options
            if ($airportData && isset($airportData['data'])) {
                foreach ($airportData['data'] as $airport) {
                    echo '<option value="' . $airport['icao_code'] . '">' . $airport['airport_name'] . ' (' . $airport['icao_code'] . ')</option>';
                }
            }
            ?>
        </select>
        <?php
// $file = fopen('airports.dat', 'r');

// if ($file) {
//     // Create an empty array to store airport data
//     $airportData = array();

//     while (($line = fgets($file)) !== false) {
//         // Split the line by commas
//         $fields = explode(',', $line);

//         // Extract the desired fields (e.g., IATA code, airport name)
//         $iataCode = $fields[4];
//         $name = $fields[1];

//         // Create an array with the extracted data
//         $airport = array(
//             'iata_code' => $iataCode,
//             'name' => $name
//         );

//         // Add the airport data to the array
//         $airportData[] = $airport;
//     }

//     fclose($file);

//     // Display the retrieved airport data
//     foreach ($airportData as $airport) {
//         echo $airport['iata_code'] . ' - ' . $airport['name'] . '<br>';
//     }
// } else {
//     echo 'Unable to open the airports.dat file.';
// }
// ?>
<input type="text" id="airport-input" name="airport" placeholder="Search for an airport">

    </form>
</body>

<script>
$(document).ready(function() {
    // Fetch the airport data from the airports.dat file
    $.get('airports.dat', function(data) {
        var airports = [];

        // Split the file content into lines
        var lines = data.split('\n');

        // Parse each line and extract the airport name
        for (var i = 0; i < lines.length; i++) {
            var fields = lines[i].split(',');

            // Check if the line contains the required number of fields
            if (fields.length >= 2) {
                // Extract the airport name (e.g., index 1 in the fields array)
                var name = fields[1].replace(/"/g, '');

                // Add the airport name to the airports array
                airports.push(name);
            }
        }

        // Initialize the Autocomplete widget
        $('#airport-input').autocomplete({
            source: airports,
            minLength: 2, // Minimum number of characters to trigger the autocomplete suggestions
            autoFocus: true // Automatically focus on the first suggestion
        });
    });
});
</script>



</html>
