<?php
     $payentstatus="sucess";
     $bookingId =$_POST['bookingid'];

    $response = array(
        'paymentstatus' => $payentstatus,
        'bookingid' => $bookingId
      
        // 'value3' => $value3
    );
    echo json_encode($response);
?>