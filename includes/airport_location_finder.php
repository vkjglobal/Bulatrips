<?php
include_once('dbConnect.php');
    $search = isset($_GET['q']) ? $_GET['q'] : '';    
    $query = "SELECT airport_code, airport_name, city_name, country_name 
    FROM airportlocations 
    WHERE city_name LIKE :search 
    OR country_name LIKE :search 
    OR airport_code LIKE :search 
    OR airport_name LIKE :search";
    $stmt = $conn->prepare($query);

    $searchTerm = "%$search%";
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);

    $stmt->execute();
    $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($airports as $key => $airport) {
        $data[] = [
            'id' => $airport['airport_code'],
            'text' => $airport['city_name']." - ".$airport['airport_code']." - ".$airport['airport_name']
        ];
    }
    echo json_encode($data);