<?php
if ($_SERVER['HTTP_HOST'] == 'localhost:8080') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "travelsite";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password,$db);
    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }
    //  echo "Connected successfully";

}
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db = "travelsite";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password,$db);
    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }
    //  echo "Connected successfully";

}
if ($_SERVER['HTTP_HOST'] == 'travelsite.reubrosample.tk') {
    $servername = "localhost";
    $username = "reubrode_travelsite";
    $password = "Reubro@2023";
    $db = "reubrode_travelsite";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password,$db);
    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }
    //  echo "Connected successfully";

}

if ($_SERVER['HTTP_HOST'] == 'travelsite.reubro.com') {
    $servername = "localhost";
    $username = "reubroco_travelsite";
    $password = "Reubro@2023";
    $db = "reubroco_travelsite";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password,$db);
    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }
    //  echo "Connected successfully";

}

if ($_SERVER['HTTP_HOST'] == 'bulatrips.com') {
    $servername = "localhost";
    $username = "bulatrips_db";
    $password = "Reubro@2023";
    $db = "bulatrips_db";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password,$db);
    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }
    //  echo "Connected successfully";

}

?>
