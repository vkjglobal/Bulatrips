<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Autocomplete Airport Search</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
  <script>
    $(function() {
      $.ajax({
        url: "https://raw.githubusercontent.com/jpatokal/openflights/master/data/airports.dat",
        dataType: "text",
        success: function(data) {
          var airports = data.split("\n");
          var airportNames = [];
          for (var i = 0; i < airports.length; i++) {
            var fields = airports[i].split(",");
            var airportName = fields[1];
            var airportCode = fields[4];
            if (airportCode) {
              airportNames.push({
                label: airportName + " (" + airportCode + ")",
                value: airportCode
              });
            }
          }
          $("#airportSearch").autocomplete({
            source: airportNames,
            minLength: 3,
            select: function(event, ui) {
              $("#airportSearch").val(ui.item.label);
              return false;
            }
          });
        }
      });
    });
  </script>
</head>
<body>
  <label for="airportSearch">Departure Airport:</label>
  <input type="text" id="airportSearch" name="departure_airport">
</body>
</html>
