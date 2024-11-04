<?php
// use PhpOffice\PhpSpreadsheet\IOFactory;
require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';

if (isset($_POST['searchTerm'])) {
    $searchTerm = $_POST['searchTerm'];
    
    $spreadsheet = IOFactory::load('Airport Codes List.xlsx');
    $sheet = $spreadsheet->getActiveSheet();
    
    $airportData = [];
    
    foreach ($sheet->getRowIterator() as $row) {
        $rowData = [];
        
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue();
        }
        
        $airportData[] = $rowData;
    }
    
    $results = [];
    
    foreach ($airportData as $row) {
        $code = $row[0];
        $name = $row[1];
        $country = $row[2];
        
        if (
            stripos($code, $searchTerm) !== false ||
            stripos($name, $searchTerm) !== false ||
            stripos($country, $searchTerm) !== false
        ) {
            $results[] = "<p>Code: $code, Name: $name, Country: $country</p>";
        }
    }
    
    if (empty($results)) {
        echo '<p>No results found.</p>';
    } else {
        echo implode('', $results);
    }
}
