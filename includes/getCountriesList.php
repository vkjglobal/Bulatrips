<?php
require_once('dbConnect.php');
$airport_country = $conn->prepare('SELECT * FROM countries');
$airport_country->execute();

$return_array = array();
$counter = 0;
while($data = $airport_country->fetch(PDO::FETCH_ASSOC)) {
    $return_array[$counter]['name'] = $data['name'];
    $return_array[$counter]['iso2'] = $data['iso2'];
    $counter++;
}
header('Content-Type: application/json');
echo json_encode($return_array);
?>