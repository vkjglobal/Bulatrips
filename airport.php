<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Airport Autocomplete</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
</head>
<body>

<h2>Airport Autocomplete</h2>

<div>
    <label for="airport-input">Select an airport:</label>
    <input type="text" id="airport-input">
</div>

<script>
$.noConflict();
jQuery(document).ready(function($) {
    // Fetch the airport data from the airports.dat file
    $.get('airports.dat', function(data) {
        var airports = [];

        // Split the file content into lines
        var lines = data.split('\n');

        // Parse each line and extract the airport name and code
        for (var i = 0; i < lines.length; i++) {
            var fields = lines[i].split(',');

            // Check if the line contains the required number of fields
            if (fields.length >= 4) {
                // Extract the airport name and code (e.g., index 1 and 4 in the fields array)
                var name = fields[1].replace(/"/g, '');
                var code = fields[4].replace(/"/g, '');

                // Create an object with the airport code as the label and value
                var airport = {
                    label: code + ' - ' + name,
                    value: code
                };

                // Add the airport object to the airports array
                airports.push(airport);
            }
        }

        // Get the input element
        var inputElement = $('#airport-input');

        // Initialize the input element as an autocomplete
        inputElement.autocomplete({
            source: airports,
            minLength: 2
        });
    });
});
</script>

</body>
</html>
