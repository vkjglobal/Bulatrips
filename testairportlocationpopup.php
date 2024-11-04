<!-- <input type="text" id="searchInput" placeholder="Search by code, name, or country">
<div id="searchResult"></div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#searchInput').on('input', function() {
            var searchTerm = $(this).val();

            if (searchTerm.length > 0) {
                $.ajax({
                    url: 'testsearch_airport.php', // PHP script to handle the request
                    method: 'POST',
                    data: {
                        searchTerm: searchTerm
                    },
                    success: function(response) {
                        $('#searchResult').html(response);
                    }
                });
            } else {
                $('#searchResult').empty();
            }
        });
    });
</script> -->

<?php
// require 'PHPExcel/PHPExcel.php';
// require_once 'PHPExcel/Classes/PHPExcel.php';

// $excelFile = 'Airport Codes List.xlsx';
// $reader = PHPExcel_IOFactory::createReaderForFile($excelFile);
// $reader->setReadDataOnly(true);
// $excelObj = $reader->load($excelFile);

// $sheet = $excelObj->getActiveSheet();
// $highestRow = $sheet->getHighestRow();
// $highestColumn = $sheet->getHighestColumn();

// $airportNames = [];

// for ($row = 1; $row <= $highestRow; $row++) {
//     $airportCode = $sheet->getCell('A' . $row)->getValue();
//     $airportName = $sheet->getCell('B' . $row)->getValue();
//     $country = $sheet->getCell('C' . $row)->getValue();

//     // Store the airport name in the array
//     $airportNames[] = $airportName;
// }
// ?>
<?php
require 'PHPExcel/Classes/PHPExcel.php';

$inputFileName = 'PHPExcel/Classes/PHPExcel.php';

// Load the Excel file
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
$worksheet = $objPHPExcel->getActiveSheet();

// Retrieve the airport data from the Excel sheet
$airportData = array();
foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    
    $rowData = array();
    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }
    
    $airportData[] = $rowData;
}

?>
<input type="text" id="airportInput" placeholder="Enter airport name or code">
<script>
    const airportInput = document.getElementById('airportInput');

airportInput.addEventListener('input', function() {
    const inputValue = airportInput.value.toLowerCase();
    const matchingAirports = airportData.filter(function(airport) {
        const airportName = airport[0].toLowerCase();
        const airportCode = airport[1].toLowerCase();
        return airportName.includes(inputValue) || airportCode.includes(inputValue);
    });
    
    // Display the matching airport suggestions
    // You can update the UI to show the suggestions in a dropdown, for example
    // Here's a simple example of logging the matching airports to the console
    console.log(matchingAirports);
});

</script>