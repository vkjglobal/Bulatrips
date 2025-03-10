<?php
require_once('dbConnect.php');
require_once('common_const.php');
session_start();

if( isset($_POST['fs_code']) && $_POST['fs_code'] != '' ) {
    unset($_SESSION['Revalidateresponse']); 
    $_SESSION['fs_code_active'] = $_POST['fs_code'];

    $fsCode = $_SESSION['fs_code_active'];
    // $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1/Revalidate/Flight';
    // $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';

    $endpoint   =   'v1/Revalidate/Flight';
    $apiEndpoint = APIENDPOINT.$endpoint;
    $bearerToken   =   BEARER;
    $requestData = array(
        'FareSourceCode' => $fsCode,
        'Target' => TARGET,
        // 'ConversationId' => 'string',
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $bearerToken
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    if ($response) {
        $responseData = json_decode($response, true);
        $_SESSION['Revalidateresponse'] = $responseData;
    }
}

if( isset($_POST['user_redistratoion_mail']) && $_POST['user_redistratoion_mail'] != '' ) {
    $user_redistratoion_mail = $_POST['user_redistratoion_mail'];
    echo $user_redistratoion_mail;
}

if (isset($_POST['tokenManagement']) && $_POST['tokenManagement'] != '') {
    if (isset($_POST['cookieName']) && $_POST['cookieName'] != '') {

        $tokenManagement = $_POST['tokenManagement'];
        $cookieName = $_POST['cookieName'];

        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE manage_booking_token = :manage_booking_token AND mf_reference = :mf_reference');
        $stmtbookingid->execute(array('manage_booking_token' => $tokenManagement,'mf_reference' => $cookieName));
        $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
        if( isset($bookingData) && $bookingData != '' ) {
            if( isset($bookingData['id']) && $bookingData['id'] != '' ) {
                setcookie($cookieName, $tokenManagement, time() + (86400), "/");
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
}
?>