<?php
session_start();
include_once('includes/common_const.php');
require_once('includes/dbConnect.php');
// Store all POST values in a single session variable

$total_fare_price = 0;
$_POST['total_extra_service_fee'] = getExtraserviceAmoount($_POST);
$_SESSION['totalService'] = $_POST['total_extra_service_fee'];
$total_updated_price = $_POST['total_extra_service_fee'] + $_SESSION['session_total_amount'];

// IPG PRICE INCLUDING STARTS
    $ipg_trasaction_percentage = 0;
    $stmt = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
    $stmt->bindValue(':key', "ipg_transaction_percentage");
    $stmt->execute();
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);

    $ipg_percentage = 0;
    if( isset($setting['value']) && $setting['value'] != '' ) {
        $ipg_percentage = $setting['value'];
    }
    $ipg_trasaction_percentage = ($ipg_percentage / 100) * ($_POST['total_extra_service_fee'] + $_SESSION['session_total_amount']);
// IPG PRICE INCLUDING ENDS

$total_updated_price += $ipg_trasaction_percentage;



$_SESSION['revalidationApi'] = $_POST;
if(isset($_SESSION['revalidationApi'])){
    // Return success response
    $response = array("success" => true);
}else{
    // session error
    $response = array("success" => false);
}
$response['total_updated_price'] =  "$".number_format($total_updated_price,2);
$response['total_updated_price_without_ipg'] =  "$".number_format($_POST['total_extra_service_fee'] + $_SESSION['session_total_amount'],2);

header('Content-Type: application/json');
echo json_encode($response);



function getExtraserviceAmoount($data)
    {
        $adultCount =   $data['adultCount'];
        $childCount =   $data['childCount'];
        //$adultCount =   $revalidData['adultCount'];
        $TotserviceAmnt =   0;
        for ($i = 1; $i <= $adultCount; $i++) {
            if (isset($data['baggageService' . $i])) {
                $baggageServiceData = explode('/', $data['baggageService' . $i]);
                if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                    $baggageID = $baggageServiceData[0];
                    $baggageDescription = $baggageServiceData[1];
                    $baggageAmount = $baggageServiceData[2];
                    $TotserviceAmnt += isset($baggageServiceData[2]) ? (float) $baggageServiceData[2] : 0;
                } else {
                    $baggageID = "";
                    $baggageDescription = "";
                    $baggageAmount = "";
                    $TotserviceAmnt +=  0;
                }
            } else {
                $baggageID = "";
                $baggageDescription = "";
                $baggageAmount = "";
                $TotserviceAmnt +=  0;
            }


            if (isset($data['mealService' . $i])) {
                $mealServiceData = explode('/', $data['mealService' . $i]);
                if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                    $mealId = $mealServiceData[0];
                    $mealDescription = $mealServiceData[1];
                    $mealAmount = $mealServiceData[2];
                    $TotserviceAmnt += isset($mealServiceData[2]) ? (float) $mealServiceData[2] : 0;
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount = "";
                    $TotserviceAmnt +=   0;
                }
            } else {
                $mealId = "";
                $mealDescription = "";
                $mealAmount = "";
                $TotserviceAmnt +=   0;
            }

            //return extra services
            if (isset($data['baggageServiceReturn' . $i])) {
                $baggageServiceDataReturn = explode('/', $data['baggageServiceReturn' . $i]);
                if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                    $baggageReturnID = $baggageServiceDataReturn[0];
                    $baggageReturnDescription = $baggageServiceDataReturn[1];
                    $baggageReturnAmount = $baggageServiceDataReturn[2];
                    $TotserviceAmnt += isset($baggageServiceDataReturn[2]) ? (float) $baggageServiceDataReturn[2] : 0;
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                    $TotserviceAmnt +=   0;
                }
            } else {
                $baggageReturnID = "";
                $baggageReturnDescription = "";
                $baggageReturnAmount = "";
                $TotserviceAmnt +=   0;
            }


            if (isset($data['mealServiceReturn' . $i])) {
                $mealServiceDataReturn = explode('/', $data['mealServiceReturn' . $i]);
                if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                    $mealReturnId = $mealServiceDataReturn[0];
                    $mealReturnDescription = $mealServiceDataReturn[1];
                    $mealReturnAmount = $mealServiceDataReturn[2];
                    $TotserviceAmnt += isset($mealServiceDataReturn[2]) ? (float) $mealServiceDataReturn[2] : 0;
                } else {
                    $mealReturnId = "";
                    $mealReturnDescription = "";
                    $mealReturnAmount = "";
                    $TotserviceAmnt +=   0;
                }
            } else {
                $mealReturnId = "";
                $mealReturnDescription = "";
                $mealReturnAmount = "";
                $TotserviceAmnt +=   0;
            }
        } //close of adult for loop
        //childservice amnt
        if ($childCount > 0) {
            for ($i = 1; $i <= $childCount; $i++) {
                if (isset($data['baggageServiceChild' . $i])) {
                    $baggageServiceData = explode('/', $data['baggageServiceChild' . $i]);
                    if (isset($baggageServiceData[0]) && isset($baggageServiceData[1]) && isset($baggageServiceData[2])) {
                        $baggageID = $baggageServiceData[0];
                        $baggageDescription = $baggageServiceData[1];
                        $baggageAmount = $baggageServiceData[2];
                        $TotserviceAmnt += isset($baggageServiceData[2]) ? (float) $baggageServiceData[2] : 0;
                    } else {
                        $baggageID = "";
                        $baggageDescription = "";
                        $baggageAmount = "";
                        $TotserviceAmnt +=  0;
                    }
                } else {
                    $baggageID = "";
                    $baggageDescription = "";
                    $baggageAmount = "";
                    $TotserviceAmnt +=  0;
                }


                if (isset($data['mealServiceChild' . $i])) {
                    $mealServiceData = explode('/', $data['mealServiceChild' . $i]);
                    if (isset($mealServiceData[0]) && isset($mealServiceData[1]) && isset($mealServiceData[2])) {

                        $mealId = $mealServiceData[0];
                        $mealDescription = $mealServiceData[1];
                        $mealAmount = $mealServiceData[2];
                        $TotserviceAmnt += isset($mealServiceData[2]) ? (float) $mealServiceData[2] : 0;
                    } else {
                        $mealId = "";
                        $mealDescription = "";
                        $mealAmount = "";
                        $TotserviceAmnt +=  0;
                    }
                } else {
                    $mealId = "";
                    $mealDescription = "";
                    $mealAmount = "";
                    $TotserviceAmnt +=  0;
                }
                //Extra service return

                if (isset($data['baggageServiceChildReturn' . $i])) {
                    $baggageServiceDataReturn = explode('/', $data['baggageServiceChildReturn' . $i]);
                    if (isset($baggageServiceDataReturn[0]) && isset($baggageServiceDataReturn[1]) && isset($baggageServiceDataReturn[2])) {
                        $baggageReturnID = $baggageServiceDataReturn[0];
                        $baggageReturnDescription = $baggageServiceDataReturn[1];
                        $baggageReturnAmount = $baggageServiceDataReturn[2];
                        $TotserviceAmnt += isset($baggageServiceDataReturn[2]) ? (float) $baggageServiceDataReturn[2] : 0;
                    } else {
                        $baggageReturnID = "";
                        $baggageReturnDescription = "";
                        $baggageReturnAmount = "";
                        $TotserviceAmnt +=  0;
                    }
                } else {
                    $baggageReturnID = "";
                    $baggageReturnDescription = "";
                    $baggageReturnAmount = "";
                    $TotserviceAmnt +=  0;
                }


                if (isset($data['mealServiceChildReturn' . $i])) {
                    $mealServiceDataReturn = explode('/', $data['mealServiceChildReturn' . $i]);
                    if (isset($mealServiceDataReturn[0]) && isset($mealServiceDataReturn[1]) && isset($mealServiceDataReturn[2])) {

                        $mealReturnId = $mealServiceDataReturn[0];
                        $mealReturnDescription = $mealServiceDataReturn[1];
                        $mealReturnAmount = $mealServiceDataReturn[2];
                        $TotserviceAmnt += isset($mealServiceDataReturn[2]) ? (float) $mealServiceDataReturn[2] : 0;
                    } else {
                        $mealReturnId = "";
                        $mealReturnDescription = "";
                        $mealReturnAmount = "";
                        $TotserviceAmnt +=  0;
                    }
                } else {
                    $mealReturnId = "";
                    $mealReturnDescription = "";
                    $mealReturnAmount = "";
                    $TotserviceAmnt +=  0;
                }
            }
        } //eod child for loop
        //$amountToDebit = str_replace(',', '', $amountToDebit);
        return  $TotserviceAmnt;
        // echo "here";exit;
    }
?>