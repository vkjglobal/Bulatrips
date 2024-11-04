<?php
// error_reporting(0);
session_start();
require_once("includes/header.php");
require_once('includes/dbConnect.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['search_values'] = $_POST;
    $airTripType = $_POST['tab'];
    $cabinPreference = $_POST['cabin-preference'];
    if($_POST['adult'])
    $adultCount = $_POST['adult'];
    else
    $adultCount = 0;
    if($_POST['child'])
    $childCount = $_POST['child'];
    else
    $childCount=0;
    if($_POST['infant'])
    $infantCount = $_POST['infant'];
    else
    $infantCount=0;

    $originLocation = $_POST['airport'];
    $originLocationCode = explode("-", $originLocation);

    $destinationLocation = $_POST['arrivalairport'];
    $destinationLocationCode = explode("-", $destinationLocation);
    // print_r($originLocationCode[0]);
    // print_r($destinationLocationCode[0]);
    $fromDate = $_POST['from'];
    $departureDate = date("Y-m-d", strtotime($fromDate));
    if ($adultCount < 1 || !is_numeric($adultCount)) {
        // Display an error message
        echo 'Please enter a valid number of adult passengers.';
        exit;
    }
    if ($airTripType === 'OneWay') {
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


        // Construct the API request payload
        $requestData = array(

            'OriginDestinationInformations' => array(
                array(
                    'DepartureDateTime' => $departureDate,
                    'OriginLocationCode' =>  trim($originLocationCode[0]),
                    'DestinationLocationCode' => trim($destinationLocationCode[0])
                    // 'OriginLocationCode' =>  'COK',
                    // 'DestinationLocationCode' => 'DXB'
                )
            ),
            'TravelPreferences' => array(
                //  'MaxStopsQuantity' => 'Direct',
                'MaxStopsQuantity' => 'OneStop',
                // 'MaxStopsQuantity' => 'All',
                'CabinPreference' => $cabinPreference,
                'AirTripType' => $airTripType
            ),
            'PricingSourceType' => 'Public',
            'PricingSourceType' => 'All',
            'IsRefundable' => true,
            'PassengerTypeQuantities' => array(
                array(
                    'Code' => 'ADT',
                    'Quantity' => $adultCount
                )
                // ,
                // array(
                //     'Code' => 'CHD',
                //     'Quantity' => $childCount
                // ),
                // array(
                //     'Code' => 'INF',
                //     'Quantity' => $infantCount
                // )
            ),
            // 'RequestOptions' => 'Fifty',
            'RequestOptions' => 'Fifty',
            'NearByAirports' => true,
            'Nationality' => 'string',
            'Target' => 'Test',
            'page_size'=>2,
            'page_number'=>1

            // 'ConversationId' => 'string',
        );
        if ($childCount > 0) {
            $childDetails = array(
                'Code' => 'CHD',
                'Quantity' => $childCount
            );
            array_push($requestData['PassengerTypeQuantities'], $childDetails);
        }
        
        if ($infantCount > 0) {
            $infantDetails = array(
                'Code' => 'INF',
                'Quantity' => $infantCount
            );
            array_push($requestData['PassengerTypeQuantities'], $infantDetails);
        }
        // Send the API request

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

        // Handle the API response
        if ($response) {
            $responseData = json_decode($response, true);
            // echo '<pre>';
            // print_r($responseData);
            // echo '</pre>';


            if ($responseData['Success'] == 1) {
                if (isset($responseData['Data']['PricedItineraries'])) {
                    $pricedItineraries = $responseData['Data']['PricedItineraries'];
                } else {
                    echo "PricedItineraries key is missing in the API response.";
                }
            } else {
                echo "API response indicates an error.";
            }
        }
    }
}else{
    $searchValue = $_SESSION['search_values'];

    $airTripType = $searchValue['tab'];
    $cabinPreference = $searchValue['cabin-preference'];
    if($searchValue['adult'])
    $adultCount = $searchValue['adult'];
    else
    $adultCount = 0;
    if($searchValue['child'])
    $childCount = $searchValue['child'];
    else
    $childCount=0;
    if($searchValue['infant'])
    $infantCount = $searchValue['infant'];
    else
    $infantCount=0;

    $originLocation = $searchValue['airport'];
    $originLocationCode = explode("-", $originLocation);

    $destinationLocation = $searchValue['arrivalairport'];
    $destinationLocationCode = explode("-", $destinationLocation);
    // print_r($originLocationCode[0]);
    // print_r($destinationLocationCode[0]);
    $fromDate = $searchValue['from'];
    $departureDate = date("Y-m-d", strtotime($fromDate));
    if ($adultCount < 1 || !is_numeric($adultCount)) {
        // Display an error message
        echo 'Please enter a valid number of adult passengers.';
        exit;
    }
    if ($airTripType === 'OneWay') {
        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v2/Search/Flight';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';


        // Construct the API request payload
        $requestData = array(

            'OriginDestinationInformations' => array(
                array(
                    'DepartureDateTime' => $departureDate,
                    'OriginLocationCode' =>  trim($originLocationCode[0]),
                    'DestinationLocationCode' => trim($destinationLocationCode[0])
                    // 'OriginLocationCode' =>  'COK',
                    // 'DestinationLocationCode' => 'DXB'
                )
            ),
            'TravelPreferences' => array(
                //  'MaxStopsQuantity' => 'Direct',
                'MaxStopsQuantity' => 'OneStop',
                // 'MaxStopsQuantity' => 'All',
                'CabinPreference' => $cabinPreference,
                'AirTripType' => $airTripType
            ),
            'PricingSourceType' => 'Public',
            'PricingSourceType' => 'All',
            'IsRefundable' => true,
            'PassengerTypeQuantities' => array(
                array(
                    'Code' => 'ADT',
                    'Quantity' => $adultCount
                )
                // ,
                // array(
                //     'Code' => 'CHD',
                //     'Quantity' => $childCount
                // ),
                // array(
                //     'Code' => 'INF',
                //     'Quantity' => $infantCount
                // )
            ),
            // 'RequestOptions' => 'Fifty',
            'RequestOptions' => 'Fifty',
            'NearByAirports' => true,
            'Nationality' => 'string',
            'Target' => 'Test',
            'page_size'=>2,
            'page_number'=>1

            // 'ConversationId' => 'string',
        );
        if ($childCount > 0) {
            $childDetails = array(
                'Code' => 'CHD',
                'Quantity' => $childCount
            );
            array_push($requestData['PassengerTypeQuantities'], $childDetails);
        }
        
        if ($infantCount > 0) {
            $infantDetails = array(
                'Code' => 'INF',
                'Quantity' => $infantCount
            );
            array_push($requestData['PassengerTypeQuantities'], $infantDetails);
        }
        // Send the API request

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

        // Handle the API response
        if ($response) {
            $responseData = json_decode($response, true);
            // echo '<pre>';
            // print_r($responseData);
            // echo '</pre>';


            if ($responseData['Success'] == 1) {
                if (isset($responseData['Data']['PricedItineraries'])) {
                    $pricedItineraries = $responseData['Data']['PricedItineraries'];
                } else {
                    echo "PricedItineraries key is missing in the API response.";
                }
            } else {
                echo "API response indicates an error.";
            }
        }
    }
   

}
// print_r($_SESSION['search_values']);
$stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
$stmtlocation->execute(array('airport_code' =>$originLocationCode[0] ));
$airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);


$stmtlocation->execute(array('airport_code' =>$destinationLocationCode[0] ));
$airportDestinationLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
 
?>
<section class="midbar-wrapper-inner pt-3 pb-3">
    <div class="flight-search-midbar container">
        <div class="d-flex white-txt justify-content-center">
        <?php echo $airportLocation['city_name'];?> To <?php echo $airportDestinationLocation['city_name'].' '.$airTripType .' '. date("D, d M", strtotime($fromDate)) . ' | '.$adultCount+$childCount+$infantCount?>  passenger 
        </div>
        <div class="row">
            <form class="flight-search col-12" id="flight-search" method="POST" action="search.php">
                <span class="lbl">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <mask id="mask0_69_1529" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="30" height="30">
                            <rect width="30" height="30" fill="url(#pattern0)" />
                        </mask>
                        <g mask="url(#mask0_69_1529)">
                            <rect x="-13" y="-11" width="51" height="49" fill="white" />
                        </g>
                        <defs>
                            <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                                <use xlink:href="#image0_69_1529" transform="scale(0.00195312)" />
                            </pattern>
                            <image id="image0_69_1529" width="512" height="512" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAgAAAAIACAYAAAD0eNT6AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAPXwAAD18B14rayQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAACAASURBVHic7d15vO7luPjxz7WbU5JQKYTiOIY4KUoJkYwNRELmsaJjHuKXQ4555pBjqBBN5nNKIRlORZkihFLo1GnSPO7r98f93altrb3X2uv53vfzPN/P+/Var7XV7ntdldZ9Pff3vq8rMhNJkjQsi1onIEmS6rMAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBWrl1AtIoRcRqwB2BDYFVewiRwOXABcAFmXldDzEkqXeRma1zkFZIRKwCPAzYGdgW2Ai4XeU0LgXOpxQEN//+Z+B7mfmHyvlI0pxYAGjiRMQ2wL7AY4B1GqezPGcDxwPHAd/JzAvbpiNJhQWAJkZE3BP4d2DX1rmsoAR+RikGjgd+kJlXt01J0lBZAGjsRcSawLuBFzJd51auBg4G3puZv2+djKRhsQDQWIuIOwNfAR7QOpceLQaOBt6dmae0TkbSMFgAaGxFxLbAUcAdWudS0feAd2Xmf7VORNJ0swDQWIqIRwNfo5+rfJPgdMprj8My8/rWyUiaPhYAGjsRcQ/gZOA2rXMZA78D9srMk1snImm62AlQYyUi1qF88nfxL+4B/DAi/q3reyBJI+EOgMZKRHwdeHzrPMbUqcAzM/OM1olImnzuAGhsRMRjcPFfli2A0yLi5RERrZORNNncAdBYiIhFlCY5922dy4T4NvCczDy3dSKSJpM7ABoXz8TFfz52AH4ZEbu3TkTSZHIHQGMhIs4ENm2dx4Q6EHhT+h+zpHmwAFBzEXFf4Bet85hwXwWekZlXtE5E0mTwFYDGwRNbJzAFdgZ+FBF3bZ2IpMlgAaBxYAEwGvcFTomI7VsnImn8+QpATUXEesD/AV5rG53rgX0z8xOtE5E0vtwBUGt3wcV/1FYBPh4RH4mIaRqfLGmELADU2katE5hiewPHRsRtWyciafxYAKg1C4B+PYJyLuCfWyciaby4PajW+ioAFlPehfdhtZ6e25e7AydFxJ6Z+Y3WyUgaDxYAaq2v9/+XAVtk5h9H/eBuKt8dlvraENgaeBjjOclwbeCrEfGGzHxn62QktWcBoNYu6Om5twGOjoitM/PqUT44M68H/tJ93UJErAQ8EHhk97UNsOoo4y/AIuAdXeOl52fmNa0TktSOZwDUWl8FAMDmwH/0+Px/kJk3ZubJmXlgZj4c2AR4F2VHYlw8HTgxIu7YOhFJ7VgAqLXzen7+syLixT3HmFVmnpeZrwXuDLwe+N9WuSxlS+DHEbFl60QktWEjIDUVEWsBF1PurvflOmC7zDylxxhzEhGrAS+hDPBZs3E6ANdQXgd8vnUikupyB0BNdcNrftxzmFWBIyPidj3HWa7MvDYzPwDcHzipdT7A6sDnIuIdEeHPA2lA/A9e4+C7FWLcCThsXBa5zDwT2BZ4I2WHorXXUm4JrN06EUl1jMUPQw3eVyrFeSTwtkqxlqs7MPh2YCvgjNb5AI+n9Au4e+tEJPXPMwAaCxHxA+AhFUIlsEtmfq1CrDnrPnl/gbIIt3YxsHtmfqd1IpL64w6AxsX7K8UJ4JCI2LRSvDnJzMuBnYFxaNJzW8oMgb1bJyKpP+4AaCx0DXTOADarFPKXwIMz86pK8eYsIp4O/CflgF5rn6CMFu6rrbKkRtwB0FjIzBuBF1K26Gu4L2VxGzvdlbztgb+2zgV4EXDcONygkDRaVXYAImJd4F+ALbqvzZi5+LgGOB34KXAa8PNx/ISm/kTEQcALKobcJzM/WjHenHWd+r5CadrT2lnAzpn5y9aJSBqNXgqAbljKrsCTKX3R77qCj1oM/BY4BTgE+G76zmKqRcQ6lO35O1UKeT3w0Mwchzv5/yAiVgc+BezZOhfgCuAZmfnV1olIWriRFgARsQllG/e5wPoje/DfnUnZtj04My/s4fkaAxHxIOBE6g3R+QvwL5nZ51yCBYmI11G6B7Z+bZfAmzLzwMZ5SFqgkRQAEfE44KXATtT5AXUtcBTwgczsu4ucGoiIlwAfqxjyu8CjurMIYykingB8njLat7UvAs8d9aRFSfUsqACIiA2Bg2h3d3kx8BHgDZl5ZaMc1JOIOAR4ZsWQ7+oG94ytiLg38DXgbq1zAU6l9FT4c+tEJM3fChcAEfEM4EPAuiPNaMWcBbwgM7/dOhGNTkSsQemXf7+KYXfLzC9XjDdvEbEecATw8Na5UKYb7jquZygkzW7e2/URsX5EfBk4lPFY/KEcMjw+Ij7ZHSLTFOi2l3cDLq0Y9rMRcY+K8eYtMy8CdqTuK5LZbACcEBF7tU5E0vzMawcgInaktCtdr7eMFu4c4JHdsBVNge7d91cpXfxq+BXwoEl4rRQRL6bsxPU5Tnmu3gO8NjMXt05E0vLNeQeg+yH8NcZ78Qe4M+UTyT1bJ6LRyMyvA2+vGPLewCcrxlthmflxym7ARa1zAV4FfMNdOGkyzGkHICJ2o5z6HYdPGXP1v8DDM/M3rRPRwnVjfI8BHlUx7Msz80MV462wiLgrpUC/T+tcgN8AT3QXThpvyy0AIuKpwOeAlatkNFrnA4/IzF+3TkQL1x1+O42yy1PD9ZQi8oeV4i1IRKxFuSb4xNa5AJcAT83M41onImlmy3wFEBGPofxAmcTFH0ozohMiYhw+FWmBusNvT6b0gahhFeDwiOijqdXIZeYVwC7UfV0ym3WB/46Il7VORNLMZt0B6O74/xy4fdWM+nEusGVmnt86ES1cRLyQuoN8vkc5WHpDxZgLEhF7AJ8G1midC2Wy4d6ZeV3rRCT93Yw7AN371kOZjsUfSl/5L0fEaq0T0cJl5kHAZyqG3B54R8V4C5aZXwQeSmlz3NrzgW9HxLT8PJGmwmyvAF4L7FAzkQq2ZkzHv2qFvJQyNbKWV0bEkyvGW7DM/AllkuDJrXMBtgV+HBGbt05EUvEPrwAiYgtK97VJfe+/PK/OzPe0TkIL1518P5V6DakuB7aatJsl3c7XJ6nbVnk2VwJ7ZebRrRORhm6mHYADmd7FH+Cd3fAiTbjMPAt4BmVCXQ1rA0d3p+0nRmZem5l7Aa+hzM9o6VbAkRHx5oio1dhJ0gxusQMQEVsDP2qXTjWXAVt7PXA6RMQBwP+rGPLwzHxqxXgjExGPBQ4Dbt06F+BI4FmZeVXrRKQhWroA+BZ1G6209AfKdu7FrRPRwnSHVr9JGUddyysy8/0V441MRNyL0jRo09a5AD+jNA06t3Ui0tDcVABExEOAH7RNp7rvAjtO0vUuzSwibks5D7BJpZA3UJpMfb9SvJGKiHUpEwXH4bDvBZQpjBPRcEmaFjc/A/CqZlm083Dgw62T0MJ1OzlPAq6pFHJlSpOgDSvFG6nMvISyYzIO//+/A/CdiHhO60SkIVkEEBG3Ah7dOJdWXhwRL22dhBYuM08D9q4YcgNKETCRh2Yz84bMfBnwQkrb45ZWBT4dEe+PiJUa5yINQmTmkmE/R1WMezpwHHAKsCblGtftgV2BFrPYbwB2ysxvN4itEYuIT1Kaz9Tygcz814rxRi4itqP8DBiHZj3foswRuLR1ItI0W1IAHEq5TtW3I4D9MvOvsyYU8TBKk5fdK+RzcxdTZsD/vnJcjVh37/2HwBYVw+6RmV+qGG/kIuIulMOB92udC/A7yuHA37ZORJpWQXmXeQH9NlO5mrLwHzTXvyAiXgx8BKi5HXgG5Xrg3yrGVA+6xexUYL1KIa+k3CqZ6Kul3evAQym7ca39jbITcGzrRKRptIjyKanPxf8vlB+Mc178ATLz45TJZlf2ktXM7gUc1l0r0wTLzD8BT6de45tbUZoErV0pXi8y80rKYcq3ts4FWAf4ZkS8onUi0jRaBNyz5xgvz8zTV+QvzMxvAE+hbveyxwDvrhhPPek+OR5QMeQ9qTukqBdZvBl4KtC6Sc9KwHsj4jMRsWrjXKSpsgjYrMfnH5OZCzpcmJn/RWlhWtMrvJI0Nd5GaRJUy5MiYiqu1Gbm4cB2lHHarT0b+G5ErN86EWlaBPBFSqU/atcC987MP4ziYRHxGcoPgVquozR6sTnJhIuI21DOA9ytUsgbgUdm5gmV4vWqW3S/TJmo2dq5wM6ZWXMSpDSV+twBOGlUi3/nRdSdU7Aq5Z3unSvGVA+662RPohxGrWEl4IsRccdK8XqVmedTmmZ9tnEqAHcCfhART2mdiDTpFgF9LXBnjPJhmXkd5WTyOaN87nLcAfhadzJaEywzfwa8pGLI9YEjImKVijF7000UfA7wSsoOR0trAl+KiH9zoqC04vo87T7SAgAgMy8AdqbuzYDNgUP9QTP5MvNg4OMVQ24DvLdivN5l5vuAx1Ou6LX2JuAoC3RpxSyivxagv+njod0nub2oNwMeys7Dv1WMp/68nNKBspZ9I2LPivF6l5nHAA+iNOtpbVfgR13fB0nzsIjSBrcPG/X0XDLzaOrOfwfYPyL2qBxTI9a9SnoycGHFsJ+MiPtUjNe7rkPfgyhte1u7H/Djrp2xpDnqcweg1/8YM/OtQO3Wq5+OiAdWjqkR62bPP416/SXWpBwovXWleFV0hysfC3ygdS6UGQbfjogXtE5EmhSLKNfd+lCjGn8O5XpXLWsAX5nUEbD6u8w8Hti/YsjNgIOn7SxJZt7YDUJ6Hv39LJmrVYCDIuJDkzqhUappEXBmT8/etO9rUJl5NeVQ4Hl9xlnKRpQiYPWKMdWPd1CG39SyC/DaivGqycxPA4+gzBVpbV/gmIjos8W5NPEWAT/v8fkv7PHZAGTmXyg/WK/pO9bNbAV8qmI89SAzk3KgtOYEyLdFxCMqxquma5q1JfCz1rkAOwCnRMS9WicijatF9Psf6z4RsWaPzwcgM0+h7vx3gD0j4vWVY2rEusmPu1Gv5/2SJkEbV4pXVWaeAzwEOLJ1LsCmwEkR8djWiUjjqO8dgPWA5/b4/Jtk5ueBf68R62YOjIidK8fUiGXmLymdJmu5PXDktA63ycyrKEO8DqDudd2Z3Br4ekS8unEe0tiJ7usyYK2eYpwNbJqZvXcP6w5YfQV4Yt+xbuYKYJtuEdEEi4iPAHtXDPmxzKwZr7qIeBJwMGVccmuHAi/IzGtbJyKNg0Xde9BjeoyxCeXTQO+6v5enAzUX47Uo7YJvXzGm+vGvwEkV4700Ip5ZMV513TTQbanbwns2zwS+5y0eqVjSCviQnuNU237LzCsoOwA1G71sQmlJOpVbukORmddTmgTVPMn+iYi4X8V41XXdO7cExmGy5oMoTYPs56HBW1IAHAP8X49xHhARj+rx+beQmWdTDnb11eRoJtsBH6sYTz3obpXsQb2BN2tQmgTdplK8Jro5Ho9gPG7PbAScGBFPa52I1NIiuOmTzxd7jvWanp9/C5n5feClNWMCz4uI/SrH1Ihl5neBN1QMeXfgkGlrErS0zLwuM58P7Ef7iYJrAF+IiLdP+z93aTZRXptDtyX2457jbZGZp/Uc4xYi4gOUATC13Ag8LjOPrRhTPYiIoynDZmrZPzMPrBivmW5H8HBgHHY+vgY8IzMvb52IVNNNBQBARPwE2KLHeF/MzKrbbhGxEvBfwI4Vw14KPLgbmKIJ1fXu/zFwj0ohFwM7ZeZxleI1FRGbAV8H7tk6F+B04ImZeVbrRKRali4AnkK/A3ZuBDar/R9Z9371JOr+oDkTeFBmXlIxpkYsIu4NnEy9a2wXUnbKxuHUfO8iYh3K68edWucCXAQ8OTNPaJ2IVMOipf73UcAfeoy3EvDKHp8/o25q2RMpn8xr2Qz4UrcDoQmVmb+ibpfJ21GaBK1WMWYzXSfGxwPva50LpXHZcRHxktaJSDXcogDomvW8t+eYz4mI2/Uc4x9k5u8o/QhqHj56FPD+ivHUg8z8IvChiiG3rByvqW6i4CuBZwOtm/SsDHwsIj7mREFNu6V3AAA+S79XAtcE9unx+bPq3q3+a+Ww+057s5eBeBV177G/MCKeXTFec5l5MPBw4PzWuQAvAb4VEeu1TkTqyy3OANz0ByPeBPxbj3EvAu7c9QyvLiI+QYVJhTdzGbB5159AE6rrIPdTYP1KIa8Btu4a6QxGNyjpq8C/tM4F+CPlcOCvWicijdpMOwAAHwWu7DFutSFBs9gH+F7FeLcGPu+W4mTLzPMor5FuqBRydUqToEHNtc/MP1Maax3eOhfgbsD/RETN+SJSFTMWAJl5Mf137HplqwNyN2v5WvM2wjbA/6sYTz3IzBOB11UMeVfgc0NrVpOZV2XmU4E30X6i4NrAlx3/rWkz4ysAgIi4C/B7yqGYvuyZmYf1+Pxlioj7AD+i/Adew2LgMZn5rUrx1JOIOBzYvWLI/5eZfb6WG1sRsStlkt84TBQ8DHhuZl7TOhFpoWYtAAAi4vPAnj3G/2lmNn3PFxFPoIwQnu11yKhdD7wsMz9eKZ56EBFrUZoE/VOlkIspHSb7nNw5trqBSV+lDN5q7SfALt3cCGliLW/Re1fP8asOCZpJZn6dun3fVwH+IyIOiojVK8bVCHVTJ3cDrqgUchHlHMkmleKNlcz8BbAVcGLrXIAHUiYKPqh1ItJCLLMAyMyfA333tK86JGgmmflOyhZjTS8Azo2It0XERpVjawQy8wzqHma9LaVJ0CALx8z8P+CRwCdb5wJsCJwQEc9onYi0opb5CgAgIh4BfLvnPKoPCVpa13ntBODBDcLfAHwX+DXwW+B3/P2TZS71faY/5u9p+3veA+xNPZ/qpuoNVkTsQ2myNQ43a94FvD4zF7dORJqP5RYAMJ1DgmYSERtQ3utu3DoXaR6WVbzM9uuF/vlxiLsusCrj4RxgSV+TuRSTy/tjo3rOtPyxccjhBkpPl791X5dR2sufDfw2My9iwsy1AJjKIUEziYh/Ab5P6VgoSdJcXEjZwf0N5YPkcZn5x7YpLdtcC4CVKNvSd+sxl49mZpMWwUuLiN0pBc+g7l5Lkkbqj8Bx3de3MvPyxvncwpwKAICIeCmlQ2BfrgLukpkX9hhjziLiAGzcI0kajauAI4BPdw3FmptPAbAG8Cfg9j3m85bMPKDH589Z13ntCOBJrXORJE2VM4HPAJ9s+aF3zgUAQES8GXhLf+m0HRK0tIi4DfAL4E6tc5EkTZ0rKDvr72lRCMy3+920Dwm6hcy8lDKjfO5VkiRJc7MW8FrgrIh4R0TcrmbweRUA3TWHqR0SNJPM/A7wwdZ5SJKm1pJC4A8R8eJaw7/m9QoAhjEkaGld57X/Ae7fOhdJ0tT7AfCCzPxNn0HmPQAnM/9E/3O6X93z8+elm/z1WErDB0mS+rQt8LOI2D8ievuwPe8dAICIuD/w09Gncws7ZuZxPceYl4i4J/BDylkFSZL6dgLwlG4Wxkit0AjczPwZ0PdM++ZDgpaWmb8FHsffW35KktSnhwE/iYiRt+NfoQKg0/eo4Ed2bXnHSmaeDDwcOL91LpKkQbgz8IOI2GuUD13hAiAzvw2cOsJcZjJWZwGWyMxTgAcBp7fORZI0CKsDB0fE/qN64EJ2AADePZIsZrd7RNy15xgrpDsM+RDgmNa5SJIG460R8bZRPGihBcCRlGEHfVkJeGWPz1+QzLyMcjtgH0pHJ0mS+vbGiHjvQh+yoAIgM28EFpzEcjyndnek+cjio8B9geNb5yNJGoRXRMSHF/KAhe4AQBlo0GcP4zUpn7DHWmaenZmPAp5JmQktSVKf9omIFb4xt+ACIDOvBhZUhczBPhGxZs8xRiIzPwf8M/AU4GeN05EkTbd3RMRuK/IXrlAjoH94SMR6wDmUT+t92TczP9Lj83sREY8E9gR2AdZtnI4kafpcBWyfmT+Zz180kgIAICI+BOw7kofN7Gxg0+7cwcSJiFWARwK7A9sAmzGaVzCSJJ0HbJGZ5831LxhlAbAJcCYDGhK0EBGxFmW40BaUYmBdYB3gNt33VW7+25f+y1fgz43iGS2f3zL2uP69SdLNfQvYKee4sI+sAACIiC8ATxvZA//RTzNz7LoDSuNgqRGik17cTMLzW8b2761+7NWBjYCNgTt13zcGNqGM8x0X+2XmnEbYj7oAGOSQIEnSMHXT+nYA9gB2pezgtnQN8MDM/NXyfuNICwCAiDgW2HGkD72l47vrdpIkjY2IWBXYiVIM7Ey/B+OX5efAVpl53bJ+Ux+H0AY5JEiSNGyZeV1mfi0z9wTuAXypUSqbAy9f3m8a+Q4AQEScCvS5SH8xM/s8ayBJ0oJFxA7AR4B/qhz6b5Sbc7M26uvrGlrfuwBjOyRIkqQlusm59wNeB1xZMfQ6wAHL+g197QCsBPwOuNvIH/53H83MsW8RLEkSQETci3JVb+NKIW8A7puZv5npT/ayA9A163lfH8++mbEeEiRJ0s1l5hmUMfIzLsg9WBl4x2x/ss9OdJ/GIUGSJN0kM88BtgN+XCnkEyNixvMHvRUA3ZCgvnv3T8yQIEmSALqDeY8AavS0CWC/Gf9EH2cAbnq4Q4IkSZpR1zfg+8BWPYe6Grjz0jcCeh1Gk5kXAZ/qMwbwyu7QoSRJE6Nr1LMH5cpen9YAXrz0H6wxje59QJ8T/DYBntLj8yVJ6kVmngU8v0KovZf+sNx7AZCZZwOH9xzm1T0/X5KkXmTmkcDHew6zAfCwm/+BWvPo+24M9ICIcD6AJGlS/Svwi55jPPXm/6NKAZCZP6P/046v6fn5kiT1IjOvAZ7bc5jduumFQL0dAHBIkCRJs8rMU4FjegyxHuX6IVCxAMjM44HTeg7jWQBJ0iQ7sOfn777kFzV3AMAhQZIkzSozf0DpDdCXhy/5Re0C4EjgrB6fvxLwyh6fL0lS3/rcBbh7RGwIlQuAbkjQe3sO45AgSdLEysxjgVN7DLEt1N8BAPgMDgmSJGlZDu7x2W0KgMy8CocESZK0LN/q8dnbQZsdACgFwFU9Pn89+r9PKUlSLzLzt5Rhen24b0Ss3KQA6IYEfbrnMA4JkiRNsr52AVYG7tJqBwDKYUCHBEmSNLM+XwPcvVkB4JAgSZKW6dvA4p6evWnLHQCAd/f8fIcESZImUmZeDJze0+Pb7QAAZOZPcUiQJEmzOa+n57YtADo1hgRt3nMMSZL6cEFPz71t8wKgGxL0057DHNDz8yVJ6kNfBcBazQuATt+7ALtExNY9x5AkadSmvgA4gn6HBEH/RYYkSaPWVwGw9lgUAN2QoPf1HGbbiPj3nmNIkjRKU78DAKUzYJ9DggBeFxGv7TmGJEmjckVPz11jbAqASkOCAN4REftHxMoVYkmStBDr9fTcq8emAOh8lH6HBC3xVuAnEfHgCrEkSVpRd+jpuZePVQGQmRfS/5CgJTYHfhgRX4qIp0XEupXiSpI0V+v39NwrIjN7evaKiYhNgDMp04pquhE4CTgD+DPwl5t/z8xLKucjSRq4iPgIsHcPjz5t7N6DZ+bZEfEFYK/KoVcCHtJ9/YOIuIqlioKbfV/y6/Mzs6/BDZKk4enrFcD47QAARMQ/Ab8CxuoVxRzcQOnbPFNxsOT7XzLzumYZSpImRkScAGzfw6OPHssCACAiDgd2b51HD5Jy3XFZRcKfM/PyZhlKksZCRPwV2LCHR797nAuAzYHTmLxdgFG5nFmKg5t9vzDH9V+gJGlBIuK+wC96evyLxu4MwBKZ+fOI+ADwita5NLI2cK/uazbXdtXhss4lnJeZN/ScqyRp9Hbq8dm/H9sdAICIWINS/WzaOpcJthg4n2UXCX/pGjFJksZERBwP7NDT4+8y1gUAQEQ8FDgBiMapTLtLWP65BK9CSlIFEXEr4GJg1R4efy2w5ti+AlgiM0+MiA8C+7XOZcqt233dd7bfsJyrkEu+exVSkhbu4fSz+AOcmpmLx74A6LwKuAfw2NaJDNyawGbd12xuiIilr0LO9MrBq5CSNLvH9/js7wGM/SuAJSJiLeD7wP1b56IFm+kq5D+8evAqpKQhiog7AGcDa/QUYqfMPHZiCgCAiLgjcDKwcetcVMWSq5CznkvAq5CSpkxEvBN4TU+PvxFYNzMvn6gCACAiNgWOAe7eOheNhWuB2a5CLvnuVUhJEyEibgv8CVirpxA/zsytoP7AnQXLzN9HxDbAN4EHts5Hza0G3LX7ms3iiJjtKuRN370KKWkM7Ed/iz/AsUt+MXE7AEt0VySOpN9GCRqWpa9CznQuwauQknoREetQ3v3fpscw98nMX8EEFwAAEbEy8CbgDUzgboYm0pKrkMs6l+BVSEnz1vO7f4DTM/Omq94TXQAsEREP5gIWKgAAGChJREFUBA5h2W1zpVpmmgq59Pe/Zua1zTKUNFYi4pGU7fk+59/sn5kH3hRzGgoAgIhYHXgr8DL6a54gjcqyrkLetLvgVUhp+kXE+sDPgfV7DrVpZv7hprjTUgAsERF3AfYHno2vBTT5ZroKeSrwbYsDafJFxCLKJ/9H9hzqh5m57S1iT1sBsERE3I1SCOxJOSkuTZMbgB9RXn192l4I0mSKiDcABy73Ny7ckzLz6FvEnvafGxFxG+BJwNOB7en3/YrUwneB52XmWa0TkTR3EfF44Mv0v1v9e+CeSx9OnvoC4OYiYiNgZ2Cr7uueWBBoOlwJ7JGZ32idiKTli4g9gYOp86p678z82D/kMKQCYGkRsTawBaUQ2BjYaKnvt26XnTRvlwD3z8xzWiciaXYR8VLgI9QZc38RcOeZGp0NugBYnq5AWLooWPr77anzL1Gaix8B29v6WBpPEfFG4G0VQ74xM98+Yy4WAAsTEasCd2TZRcKGwCqtctTgvD0z39g6CUl/FxFrAO8C9qkY9mzgXpl5zYw5WQD0r7vmcQf+XhDMVizcqlWOmipXAbfJzOtbJyIJIuJxwIdZ9sySPjwlM4+Y7U9aAIyR7sbC0oXB0kXCes0S1CTZOjNPap2ENGRdX5oPUg6f13ZiZm6/rN9go5wxkpmXApcCv5rt93QdD2d71bDk1xsAK/Wdr8badoAFgNRA92HuJZReNGs2SGExZargMrkDMIUiYiVKEbCsImEjYPVWOap338jMJ7ROYhpExCbAQ4B/7r7uTjn4e333dcNS3+fy6/n83pHFcEhVf7p3/E+gNJ97DG1b0r8pM5d70NACYMAiYj2WfXhxY2CdZglqIU7OzAe3TmKSRcTtgQOAFzI9u6WLGaOCZJS/NzNvHOU/qOWJiLWAOwH3oDSb2wVYu2YOs/gm8IS5dAedlv9TawVk5kWUO6I/n+33RMStWHaRsBFlgIVXIcfL71onMKm6HbTXAK9nPH6gj9IiyifTqRuYFhFJv0XGIsrPuzt3X7et83c2L2cBz5xra3ALAC1TZl4J/Lb7mlFErEK5CrmsIuGOTOEPnTFmAbACImJd4IvAjq1z0bwF5br1UK9cX0Pp93/JXP8CCwAtWHfd7E/d14wiIihNk5Z1DXJjYK2+8x0IC4B5ioh/Br4KbNo6F2kF7JOZP53PX+AZAI2ViLg1y74GuTFwu2YJToZrgHtk5rmtE5kUEbEzcCjTt+WvYfhUZj5/vn+RBYAmTkSsxt9vMsxWLGzAcHe43pKZB7ROYhJ0O1Nvohz28xyLJtFPgW1m6/a3LBYAmkpd98UNWH7PhDVa5diTPwL3XpEfBkPTneI+GNitdS7SCroE2GJFR4FbAGjQIuK2LL9IWLdZgvP3+Mz8Zuskxl1E3I3yvv8+rXORVtCVwOMy83sr+gALAGk5ImJNlj8Vcn3KNaGWPpiZy+3+NXQR8UjgS4znNS5pLi4DHpuZP1zIQywApBGIiJUpUx/fC+zeIIXPM4/7v0MVEf8KvBtbZWtyXQI8OjN/vNAHDfWQlDRSmXlDROxEm8X/GOA5Lv6z62ZofALYq3Uu0gJcAOyYmbM2b5sPdwCkEYiI3YDDqf/J8iRgh8y8qnLciRERGwFfBrZsnYu0AKcBu2bmOaN6YOt3ltLEi4iHAV+g/uL/a8ohIBf/WUTE1sBPcPHXZDsU2HaUiz9YAEgLEhEPoJwmX61y6HMo7wEvrhx3YkTE84ATKNdBpUl0A7BfZu6VmVeP+uG+ApBWUERsCvwQuEPl0BdSPg3MOp9hyLoDmR8A9m6di7QApwPPy8xT+grgIUBpBUTEhsC3qL/4X0G5/uPiP4OIuB1wJLB961ykFXQd8Hbg3zPzuj4DWQBI8xQRtwGOBe5aOfR1lENAC77+M40i4v7AV4C7tM5FWkEnAc/PzF/VCOYZAGkeImIN4OvAfSuHXky553985bgTISKeSnkd4+KvSXQ68CRKT/8qiz9YAEhz1r1b/hKwbYPw+2bm4Q3ijrWIWBQRbwe+CKzZOh9pnn4D7AHcLzOPrt3Lw1cA0hx0U+P+E3hCg/AHZObHGsQdaxGxDuX65WNb5yLNw2LgeOAg4MuZubhVIhYA0ty8C3hWg7gfy8y3NIg71iLinpTrl/dsnYs0R38BPg18KjP/1DoZ8BqgtFwR8WpKAVDbl4A9W35CGEcR8TjK7IN1WucCnAl8nPJhapWbfe/j1zP9MV/jjq/FlCZU3wKOA36YmTe2TemWLACkZYiIZwOfaRD6OMpo316vAU2aiHg98DbGY+E7BnhaZl7aKoGIWES/RcfyCpA+fz0O/47n46+U7pynUw6kfjszL2mb0rJZAEiziIgnAkdTv8Xvj4FHZOYVleOOrW4k82eAp7TOpfNu4HXuzvSnK27GpRhZBUjg8u7rsu773yi7QL8e98V+JhYA0gwiYjvK1t3qlUP/ltLl78LKccdWRNyFcr///q1zAa6m3NP+QutEpIWyAJCWEhH3A06k/jvmPwMPGfXAj0nWDVo6Arhd41QAzgV2yczTWicijcKkvWORehURd6V0+au9+F9MGe7j4t+JiH0oZyHGYfH/PvBAF39NEwsAqRMR61O2/WtPj7uKMtb315XjjqWIWDUi/hP4MONxVfkTwA6ZeUHrRKRRGof/uKTmIuLWwH8Dm1YOfT3wpMw8qXLcsRQRG1AOXm7dOhfKv5t9M/MTrROR+mABoMGLiNUoTWUeUDl0As/OzGMqxx1LEbEVZfHfqHUuwAWUwuwHrROR+uIrAA1aRKwEHAY8rEH4/TxNXkTEXpSDl+Ow+J9Ged/v4q+pZgGgofs4sGuDuAdm5ocaxB0rEbFSRLwfOBhYrXU+lNkC22bmua0TkfrmKwANVjdF7vkNQh+Umfs3iDtWIuK2wOHADq1zobRtfV1mvrt1IlIt9gHQIEXEfsD7G4Q+CnjK0DvIRcR9KOcu7tY6F+BSYI/MPLZ1IlJNFgAanIh4BnAIEJVDfxd4TGZeWznuWImI3Shb/mu1zgU4A9g5M89snYhUm2cANCgR8VhKT/nai/9plIVmsIt/FG8BjmQ8Fv+vAQ9y8ddQuQOgwYiIrYHjgTUrhz6TcrBssI1kImJt4FBg59a5UK5fHgi8Of0BqAGzANAgRMS9Ke1c160c+q+U/v5nV447NiJiU8r7/n9unQtwJfCszDyqdSJSa94C0NTrpskdS/3F/1Jgp4Ev/o+m9Fmo/c9+JmdRXsP8snUi0jjwDICmWkTcntLfv3aDmauBxw95sYmIVwHfZDwW/+8AWw7534e0NAsATa2IWAv4L+AelUPfQLnq98PKccdCRKwREZ8D3g2s1Dof4EOUSYsXtU5EGie+AtBUiohVga8AD6wcOoHnZeY3KscdCxFxJ+DLwBatcwGuBV6cmZ9tnYg0jiwANHUiYhHwOdp0mHt1Zh7SIG5zEbEtpdHRHVrnQjl8uVtmntw6EWlc+QpA0+gjwO4N4r4zM9/bIG5zEfEiynv2cVj8T6IM83Hxl5bBAkBTpWs085IGoT+dma9rELepiFglIv6DMlRpldb5UJo8PSwzz2udiDTu7AOgqRERe1M+/df2Vcrs+BsbxG4mIu5A6eq3XetcKAcvX+mERWnuLAA0FSJiD+Dz1N/VOpFywvyaynGbioh/oRyyvFPrXICLKLcuvtM6EWmSWABo4kXEjsA3qL8F/XNg+8z8W+W4TUXEnsB/Amu0zgX4BbBLZp7VOhFp0ngGQBMtIrYCjqb+4v9HSpe/wSz+EbEoIt5F2WkZh8X/SGAbF39pxbgDoIkVEf8E/ABYr3Lo8yn9/f9QOW4zEXEbSkvfnVrnQum18ObMfFvrRKRJZh8ATaSI2JjS4rf24v83yif/IS3+96IcdNysdS7AZcAzMvPrrRORJp2vADRxImI9yuJf+wDaNcATM/NnleM2ExFPBE5mPBb/M4EHu/hLo2EBoIkSEbeiDJi5V+XQNwJ7ZOaJleM2EcX+lJP+a7fOBzgG2Cozz2idiDQtfAWgiRERq1BazT6oQfgXZuZXG8StriuyDgae1DqXzruA12fm4taJSNPEAkATISKCsig9ukH412XmpxvErS4i7kp533/f1rlQRio/LzMPa52INI0sADQpPgg8rUHc92XmOxvErS4iHgEcTv2DlTM5l3K//7TWiUjTyjMAGnvdu+h9G4Q+BHhVg7jVRcTLgWMZj8X/+5RhPi7+Uo/sA6Cx1k2Z+3iD0N+kfAK9oUHsaiJiNco/32c3TmWJjwMvy8zrWyciTTsLAI2tiHgSZUu69k7VD4FHZebVleNWFRF3pHRRbHGocmnXA/tm5idaJyINhQWAxlL3Pvq/gNUqhz4deGhmXlI5blUR8WDK4r9h61yACyjTFH/QOhFpSDwDoLETEVtQ7p/XXvzPpkz2m/bF/7nACYzH4n8q5X2/i79UmQWAxkpEbAb8N/Wbz/wfsGNm/rVy3GoiYuWI+BDwKeoXVzP5ArBdZp7bOhFpiLwGqLHRvZP+FnD7yqEvBx6TmWdWjltNRNyOcp7i4a1zARZTeiu8u3Ui0pBZAGgsRMS6lGtom1QOfS3ltP+pleNWExGbU16pbNI4FYBLKS2Vj22diDR0vgJQcxGxBvB14D6VQy8Gnp6Z36kct5qI2B34EeOx+P8a2NLFXxoPFgBqKiJWBo4AHtIg/Esz86gGcXsXEYsi4kDKtv+arfMBvkaZ5Pf71olIKiwA1EzX3/9TwOMahH/TtN45j4hbU/r5v6F1LkACb6W8Zrm8dTKS/s4zAGrpPcBeDeJ+KDPf1iBu7yLiHpTF/59a5wJcCTxrWndZpElnIyA1ERGvBd7RIPRhlPf+U/d//Ih4DOXvb53WuQBnATtn5i9bJyJpZr4CUHVdI5oWi/+xlE+k07j4vxb4BuOx+H+HctjPxV8aY+4AqKqI2Bk4ClipcuiTgR0y88rKcXsVEWtSzlHs0TqXzgeBV037ECVpGlgAqJqIeCjlU/jqlUOfAWybmRdXjturiLgz5X7/A1rnQumn8OLM/GzrRCTNjQWAquia0XyP+lvU5wLbZOafK8ftVURsT7k+Wbtr4kz+CuyWmSe3TkTS3HkGQL2LiLsDx1B/8b+I0t9/2hb/lwLHMR6L/0mUYT4u/tKEsQBQryJiA0p//w0qh74CeGxm/qZy3N5ExKoRcRDwUWCV1vkAnwEelpnntU5E0vzZB0C9iYh1KJ/871Y59HWULelTKsftTVdIHQVs0zoX4AbgFZn54daJSFpxFgDqRUSsTmn/unnl0IuBvTLzuMpxexMRWwJfBjZqnQvltcrumfnd1olIWhhfAWjkImIl4IvAQxuEf1lmfqlB3F5ExDOBExmPxf8XlPf9Lv7SFLAAUB8OAnZuEPctmfnRBnFHLiJWioj3AodQ/9rkTI6k3KY4u3UikkbDVwAaqYh4B/DcBqH/IzMPaBB35CJiXeBLwKNa50IZ5vOmzDywdSKSRss+ABqZiHgF8N4GoY8A9sjMxQ1ij1RE3JsyzOfurXMBLgOekZlfb52IpNGzANBIRMRewGeBqBz6eOBxmXld5bgjFxG7Urb812qdC3AmZZjPGa0TkdQPzwBowSLicZR+9LUX/58Au0764h/FAZRrfuOw+B8DbOXiL003dwC0IBHxEEpXujUqh/4tpb//hZXjjlRErAUcCuzSOpfOu4DXT8PrFEnLZgGgFRYR96FcUVu3cui/UE6kn1M57kh1LZK/Cty7dS7A1cDzMvOw1olIqsNbAFohEbEJZbJf7cX/YuDRU7D4P4py0r/2P7+ZnEN5lXJa60Qk1eMZAM1bRNye0t//jpVDXwU8PjN/VTnuSHW3Jf6b8Vj8vw9s6eIvDY8FgOYlIlYFvglsVjn09cCTM/N/KscdmYhYPSIOpVyVXKl1PsDHgR0y84LWiUiqz1cAmq+3AltWjpnAczLzvyvHHZmI2JjSz/+BrXOhFFP7ZOZBrROR1I6HADVnEbE98B3q7xztl5kfrBxzZLqbEkcB67fOBTifspPyg9aJSGrLVwCak2607yHU///M2yd88X8BpWgah8X/VMowHxd/SRYAmrM3A3euHPOTmfnGyjFHIiJWiYiPUQYjrdo6H+DzwHaZ+efWiUgaD74C0HJFxNrAn4FbVwx7NPCUzLyxYsyR6G5JHEmbcchLuxF4XWa+p3UiksaLhwA1F8+h7uJ/ArDnhC7+96c096m9WzKTS4CnZeaxrRORNH7cAdAyRcQi4HfUm073U+BhmXlZpXgjExEvoVzxq90WeSa/pgzz+X3rRCSNJ3cAtDxbU2/x/z2w06Qt/hFxO8owpCe2zqXzNcoY38tbJyJpfHkIUMuzTaU45wE7TlpTmq6l7y8Yj8U/KX0adnHxl7Q87gBoebauEONSSn//syrEGomusc8bgBdTfwzyTK4EnpWZR7VORNJksADQ8vRdAFwNPCEzf9lznJGIiM2A1wLPZDyu9wGcRXnfPxH/DCWNBwsAzaq7zrZBjyFuAJ46CY1putP9rwN2Z7xenR1P+Wd4cetEJE0WCwAtS5+n2RN4fmZ+vccYK6zrffBwYEfg0cCmbTOa0fuBV0/idUlJ7VkAaFn63OJ+TWYe3OPzlysi1gLuQGnTu+RrY0oDn62BVdplt0zXAC/KzENaJyJpctkHQLOKiHtR7pP34dqenjtXwfi8w5+PvwC7ZeYprRORNNncAdCy9Pmue7Uenz2t/oey+P9v60QkTb5xOsyk8XM25V292vsUpUOii7+kkbAA0Kwy80rgnNZ5DNwNwL6Z+fzMvK51MpKmhwWAlqevMwBavguBR2XmR1onImn6WABoeSwA2vg5sGVmntA6EUnTyQJAy/OV1gkM0BHANpl5dutEJE0vCwAtU9el73et8xiIxcD+mfmUzLyqdTKSppsFgObiM60TGIDLKP38D2ydiKRhsBGQlisi7gj8CftG9OVHwDMmaRqipMnnDoCWKzP/CnywdR5T6EbgAOChLv6SanMHQHPS9c3/NXCn1rlMibOAp2fm/7RORNIwuQOgOcnMK4CXt85jShwK3N/FX1JL7gBoXiLiCODJrfOYUH8DXpKZh7VORJIsADQvEbE68E3gEa1zmTDfBPbOzD+1TkSSwFcAmqfMvAbYGTi5dS4TYDHwJeABmfl4F39J48QdAK2QiFgX+A5w/9a5jKHrgEOAd2bm71snI0kzcQdAKyQzLwEeAhzUOpcxciXwPuBumfkCF39J48wdAC1YROwK/Cdw29a5NPJn4FPAhzLz4tbJSNJcWABoJCJifeDVwIuBWzVOp29XA98DvgUcm5lOTJQ0cSwANFIRsR6wH/Ai4PaN0xmlXwDHUhb972fmtY3zkaQFsQBQLyIigAcAOwKPAjanvCKIlnnN4kbg/4ALgPNv9nUBcA5wQmb+b7v0JGn0LABUTUSsAqwPbEj71wSLgYspC/1Fmbm4cT6SVJUFgCRJA+Q1QEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIGyAJAkqQBsgCQJGmALAAkSRogCwBJkgbIAkCSpAGyAJAkaYAsACRJGiALAEmSBsgCQJKkAbIAkCRpgCwAJEkaIAsASZIG6P8DFPZPSF+Mp/QAAAAASUVORK5CYII=" />
                        </defs>
                    </svg>
                    FLIGHTS
                </span>
                <!-- <input type="radio" id="return" name="tab" checked="checked">
                <label for="return">Round-trip</label>
                <input type="radio" id="one-way" name="tab">
                <label for="one-way">One-way</label>
                <input type="radio" id="multi-city" name="tab">
                <label for="multi-city">Multi-city</label> -->

                <input type="radio" value="Return" id="return" name="tab" <?php if ($airTripType === 'Return') echo 'checked'; ?>>
                <label for="return">Round-trip</label>
                <input type="radio" id="one-way" value="OneWay" name="tab" <?php if ($airTripType === 'OneWay') echo 'checked'; ?>>
                <label for="one-way">One-way</label>
                <input type="radio" id="multi-city" value="Circle" name="tab" <?php if ($airTripType === 'Circle') echo 'checked'; ?>>
                <label for="multi-city">Multi-city</label>


                <div class="select-class-wrp">
                    <select name="cabin-preference" class="select-class" id="cabin-preference">
                        <option value="Y" <?php echo $cabinPreference == 'Y' ? 'selected' : ''; ?>>Economy</option>
                        <option value="S" <?php echo $cabinPreference == 'S' ? 'selected' : ''; ?>>Premium</option>
                        <option value="C" <?php echo $cabinPreference == 'C' ? 'selected' : ''; ?>>Business</option>
                        <option value="F" <?php echo $cabinPreference == 'F' ? 'selected' : ''; ?>>First</option>
                    </select>
                </div>
                <span class="person-select">
                    <label for="" class="select-lbl">Traveller <span class="count"><?php echo $adultCount ?></span><span class="downarrow"></span></label>
                    <div class='select-dropbox'>
                        <span class="selectbox d-flex justify-content-between">
                            <label class="fs-13 fw-600" for="">Adults
                                <span class="fs-11">12 years and above</span>
                            </label>
                            <span class="selec-wrp d-inline-flex align-items-center">
                                <!-- <input type='number' min=0 value=0> -->
                                <input type="number" id="adult_count" name="adult" min="1" value=<?php echo $adultCount ?>>
                                <span class='minus'>-</span>
                                <span class='add'>+</span>
                            </span>
                        </span>
                        <span class="selectbox d-flex justify-content-between">
                            <label class="fs-13 fw-600" for="">Children
                                <span class="fs-11">2 - 11 years</span>
                            </label>
                            <span class="selec-wrp d-inline-flex align-items-center">
                                <!-- <input type='number' min=0 value=0> -->
                                <input type='number' id="child-count" name="child" min=0 value=<?php echo $childCount ?>>
                                <span class='minus'>-</span>
                                <span class='add'>+</span>
                            </span>
                        </span>
                        <span class="selectbox d-flex justify-content-between">
                            <label class="fs-13 fw-600" for="">Infants
                                <span class="fs-11">Under 2 years</span>
                            </label>
                            <span class="selec-wrp d-inline-flex align-items-center">
                                <!-- <input type='number' min=0 value=0> -->
                                <input type='number' name="infant" min=0 value=<?php echo $infantCount ?>>
                                <span class='minus'>-</span>
                                <span class='add'>+</span>
                            </span>
                        </span>
                    </div>
                </span>

                <div class="srch-fld">
                    <div class="search-box on row">
                        <div class="form-fields col-md-3">
                            <!-- <input type="text" class="form-control" placeholder="Departing From"> -->
                            <input type="text" id="airport-input-search" name="airport" class="form-control" placeholder="Departing From">
                            <input type="hidden" id="hiddenorigin" value="<?php echo $originLocationCode[0]?>">
                        </div>
                        <div class="form-fields col-md-3">
                            <!-- <input type="text" class="form-control" placeholder="Going To"> -->
                            <input type="text" id="arrivalairport-input-search" name="arrivalairport" class="form-control" placeholder="Going To">
                            <input type="hidden" id="hiddendestination" value="<?php echo $destinationLocationCode[0]?>">

                        </div>
                        <div class="form-fields col-md-2 calndr-icon">
                            <!-- <input type="text" class="form-control" id="from" name="from"> -->
                            <input type="text" class="form-control" id="from" name="from" value=<?php echo $departureDate ?>>
                            <span class="icon">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                </svg>
                            </span>
                        </div>
                        <div class="form-fields col-md-2 calndr-icon">
                            <input type="text" class="form-control" id="to" name="to">
                            <span class="icon">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                </svg>
                            </span>
                        </div>
                        <div class="form-fields col-md-2">
                            <button class="btn btn-typ1 w-100 form-control">Search</button>
                        </div>

                    </div>
                    <div class="search-box row multi-city-search">
                        <div class="col-md-10">
                            <div class="row">
                                <div class="form-fields col-md-4">
                                    <input type="text" class="form-control" placeholder="Departing From">
                                </div>
                                <div class="form-fields col-md-4">
                                    <input type="text" class="form-control" placeholder="Going To">
                                </div>
                                <div class="form-fields col-md-2 calndr-icon">
                                    <input type="text" class="form-control date-multy-city">
                                    <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div class="row mt-md-2">
                                <div class="form-fields col-md-4">
                                    <input type="text" class="form-control" placeholder="Departing From">
                                </div>
                                <div class="form-fields col-md-4">
                                    <input type="text" class="form-control" placeholder="Going To">
                                </div>
                                <div class="form-fields col-md-2 calndr-icon">
                                    <input type="text" class="form-control date-multy-city">
                                    <span class="icon">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="form-fields">
                                    <button class="btn add-trip fw-500 dark-blue-txt">Add Trip +</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-fields">
                                <button class="btn btn-typ1 w-100 form-control">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>
<section>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <ul class="breadcrumbs">
                    <li><a href="index.php">Home</a></li>
                    <!-- <li><a href="">International Flights Roundtrip</a></li> -->
                    <li> <?php echo $airportLocation['city_name'].' to '. $airportDestinationLocation['city_name'].' '.$airTripType ?> </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section>
    <div class="container">
        <div class="form-row">
            <div class="col-12">
                <h2 class="title-typ2 mb-3 mb-lg-5">All Flights</h2>
            </div>
            <div class="col-12 d-none">
                <ul class="filter-left">
                    <li>3 of 3 flights</li>
                    <li>
                        <select name="" class="stops-select" id="">
                            <option value="">Stops</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="price-select" id="">
                            <option value="">One way price</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="opt-select" id="">
                            <option value="">Refundable</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="airline-select" id="">
                            <option value="">Airline</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="dep-time-select" id="">
                            <option value="">Departure Time</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                    <li>
                        <select name="" class="ret-time-select" id="">
                            <option value="">Return Time</option>
                            <option value="">Stop1</option>
                            <option value="">Stop2</option>
                            <option value="">Stop3</option>
                        </select>
                    </li>
                </ul>
            </div>
            <div class="col-12 light-border">
                <ul class="flight-list">
                    <li>
                        <ul class="form-row titlebar">
                            <li class="col-md-2 text-center">Airline</li>
                            <li class="col-md-1">Depart</li>
                            <li class="col-md-2">Stops</li>
                            <li class="col-md-2">Arrive</li>
                            <li class="col-md-3">Duration</li>
                            <li class="col-md-2 text-center">Price</li>
                        </ul>
                    </li>
                    <?php

                    foreach ($pricedItineraries as $pricedItinerary) {

                        $originDestinations = $pricedItinerary['OriginDestinations'][0];
                        $segmentRef = $originDestinations['SegmentRef'];
                        $flightSegmentList = $responseData['Data']['FlightSegmentList'];
                        $FlightFaresList = $responseData['Data']['FlightFaresList'];
                        $FlightItineraryList = $responseData['Data']['ItineraryReferenceList'];
                        $fareListRefid = $pricedItinerary['FareRef'];
                        $fareListRef = $FlightFaresList[$fareListRefid];
                        $FlightPenaltyList = $responseData['Data']['PenaltiesInfoList'];
                        $penaltyListRefid = $pricedItinerary['PenaltiesInfoRef'];
                        $penaltyListRef = $FlightPenaltyList[$penaltyListRefid];
                        $onestop = false;

                        if(isset($pricedItinerary['OriginDestinations'][1])) {
                            $onestop = true;
                            $originDestinationsstops = $pricedItinerary['OriginDestinations'][1];
                            $segmentRefstop = $originDestinationsstops['SegmentRef'];

                            $segmentstop = $flightSegmentList[$segmentRefstop];
                            $duration = $segmentstop['JourneyDuration'];
                            $arrival = $segmentstop['ArrivalAirportLocationCode'];
                            $artime = $segmentstop['ArrivalDateTime'];
                            $deptime = $segmentstop['DepartureDateTime'];
                        }
                        $segment = $flightSegmentList[$segmentRef];
                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                        $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                        // print_r( $airportLocation);die();
                        // $airportLocation = 


                     ?>
                        <li class="pt-4 contentbar">
                            <ul class="form-row mb-lg-5 mb-3">
                                <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2"><img src="images/emirates-logo.png" alt=""><?php echo  $pricedItinerary['ValidatingCarrier']; ?></li>
                                <li data-th="Depart" class="main-dtls col-md-1 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2">
                                    <div class="">
                                        <!-- 11:45 KCZ  -->
                                        <?php echo $segment['DepartureAirportLocationCode']; ?>
                                        <br>
                                        <?php
                                        $datetime = $segment['DepartureDateTime'];
                                        list($date, $time) = explode("T", $datetime);
                                        echo date("d F Y", strtotime($date)); ?>
                                        <br>
                                        <?php
                                        echo $time;
                                        ?>
                                    </div>

                                </li>
                                <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2">
                                    <div>


                                        <?php
                                        if ($onestop) {
                                            $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                            $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                            $interval =  $date1->diff($date2);

                                            // Get the difference in hours and minutes
                                            $hours = $interval->h;
                                            $minutes = $interval->i;

                                            // echo "1 Stop";
                                            // echo $segment['DepartureAirportLocationCode'];
                                            echo "1 Stop" . "<br>" . $segment['ArrivalAirportLocationCode'] . "|" . $hours . "h " . $minutes . "m";
                                        } else
                                            echo "Direct";
                                        ?>

                                    </div>

                                </li>
                                <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between arrive-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        <?php
                                        if ($onestop) {
                                            $arrivallocation = $arrival;
                                            $arrivaltime = $artime;
                                            $datetime = $arrivaltime;
                                            list($date, $time) = explode("T", $datetime);
                                        } else {
                                            $arrivallocation = $segment['ArrivalAirportLocationCode'];
                                            $arrivaltime = $segment['ArrivalDateTime'];
                                            $datetime = $arrivaltime;
                                            list($date, $time) = explode("T", $datetime);
                                        }




                                        ?>
                                        <?php echo  $arrivallocation; ?><br>
                                        <?php echo date("d F Y", strtotime($date)); ?><br>
                                        <?php echo $time; ?>
                                    </div>

                                </li>
                                <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2">
                                    <div>
                                        <?php
                                        if ($onestop) {
                                            $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                            $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                            $interval =  $date1->diff($date2);
                                            $totalMinutes = ($interval->h * 60) + $interval->i;
                                            $minutes = $segment['JourneyDuration'] + $duration + $totalMinutes;
                                        } else
                                            $minutes = $segment['JourneyDuration'];
                                        $hours = floor($minutes / 60);
                                        $remainingMinutes = $minutes % 60;
                                        echo $hours . " h  " . $remainingMinutes . " m";
                                        ?>
                                    </div>

                                </li>
                                <li data-th="Price" class="main-dtls col-md-2 d-flex flex-column align-items-md-center mb-md-0 mb-2">
                                    <?php 
                                    $totalAdultfare=0;
                                    $totalChildfare=0;
                                    $totalInfantfare=0;
                                    if(isset($adultCount) && $adultCount > 0){
                                        $totalAdultfare +=$fareListRef['PassengerFare'][0]['TotalFare']* $adultCount;
                                    }
                                    if(isset($childCount) && $childCount > 0){
                                        $totalChildfare +=$fareListRef['PassengerFare'][1]['TotalFare']* $childCount;
                                    }
                                    if(isset($infantCount) && $infantCount > 0){
                                        $totalInfantfare +=$fareListRef['PassengerFare'][2]['TotalFare']* $infantCount;
                                    }
                                    ?>
                                    <div class="price-dtls mb-md-0 mb-2">&#36; <strong><?php echo $totalAdultfare+$totalChildfare+$totalInfantfare; ?></strong></div>
                                    <button class="btn btn-typ3 w-100">BOOK</button>
                                </li>
                            </ul>
                            <div class="form-row panel flight-details-tab-wrap">
                                <ul class="nav nav-tabs d-flex justify-content-around w-100 pb-3">
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Flight Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Fare Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link">
                                            Baggage Details
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content text-center">
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane1">
                                        <button class="close"><span>&times;</span></button>
                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                            <div><?php echo $airportLocation['city_name']; ?>
                                                <span class="right-arrow-small arrow-000000"></span>
                                                <?php if ($onestop) {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                    $stmtlocation->execute(array('airport_code' => $arrival));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                } else {
                                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                    $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                }
                                                $datetime = $segment['DepartureDateTime'];
                                                list($date, $time) = explode("T", $datetime);
                                                echo $airportLocation['city_name'] . " , " . date("d F Y", strtotime($date));
                                                ?>
                                            </div>
                                            <div>Total Duration: <?php

                                                                    if ($onestop) {
                                                                        $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                                                        $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                                                        $interval =  $date1->diff($date2);
                                                                        $totalMinutes = ($interval->h * 60) + $interval->i;
                                                                        $minutes = $segment['JourneyDuration'] + $duration + $totalMinutes;
                                                                    } else
                                                                        $minutes = $segment['JourneyDuration'];
                                                                    $hours = floor($minutes / 60);
                                                                    $remainingMinutes = $minutes % 60;
                                                                    echo $hours . " h  " . $remainingMinutes . " m";

                                                                    ?></div>
                                        </div>
                                        <?php
                                        foreach($pricedItinerary['OriginDestinations'] as $origins) {
                                            $originRef = $origins['SegmentRef'];
                                            $originSegment = $flightSegmentList[$originRef];
                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                        ?>

                                        <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                            <ul class="col-lg-3 mb-3">
                                                <div class="text-left">
                                                    <strong class="fw-500 d-block">
                                                        <?php
                                                          $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                          // $stmtairline->execute(array('code' => $pricedItinerary['ValidatingCarrier']));
                                                          // $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                          $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                          $stmtairline->bindParam(':code', $code);
                                                          $stmtairline->execute();
                                                          $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                          // echo $pricedItinerary['ValidatingCarrier'];
                                                          echo $airlineLocation['name'];
                                                        ?>

                                                    </strong>
                                                    Flight No - <?php echo $originSegment['OperatingFlightNumber']; ?>
                                                </div>
                                            </ul>

                                            <div class="col-lg-7">

                                                <div class="d-flex row justify-content-between">
                                                    <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                    <?php
                                                        $datetime = $originSegment['DepartureDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        // echo date("d F Y", strtotime($date));
                                                        $stmtlocation->execute(array('airport_code' => $originSegment['DepartureAirportLocationCode']));
                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                    ?>
                                                        <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode']." ".$time ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) ." ,".$airportLocation['airport_name']."," .$airportLocation['city_name'].",".$airportLocation['country_name']?> 
                                                    </div>
                                                    <div class="col-md-2 mb-md-0 mb-2">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                            </svg>
                                                            <?php
                                                             $minutes =$originSegment['JourneyDuration'];
                                                             $hours = floor($minutes / 60);
                                                             $remainingMinutes = $minutes % 60;
                                                             echo $hours . " h  " . $remainingMinutes . " m";
                                                            ?>
                                                           
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 text-md-left">
                                                    <?php
                                                        $datetime = $originSegment['ArrivalDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        // echo date("d F Y", strtotime($date));
                                                        $stmtlocation->execute(array('airport_code' => $originSegment['ArrivalAirportLocationCode']));
                                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                    ?>
                                                        <strong class="fw-500 d-block"> <?php echo $time." ".$originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                        <?php echo date("d F Y", strtotime($date)) .", ".$airportLocation['airport_name']."," .$airportLocation['city_name'].",".$airportLocation['country_name']?>
                                                    </div>
                                                </div>

                                            </div>


                                        </div>
                                        <?php
                                        }

                                       ?>


                                        <!-- <div class="fs-15 fw-300 mb-4 text-left">
                                            Note: You will have to change Airport while travelling
                                        </div>
                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                            <div>Dubai <span class="right-arrow-small arrow-000000"></span> Kochi Saturday, 26 Nov, 2022 Arrives next day</div>
                                            <div>Total Duration: 24hr 5m</div>
                                        </div> -->
                                    </div>
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane2 ">
                                        <button class="close"><span>&times;</span></button>
                                        <div class="row fs-13 mb-3">
                                            <div class="col-md-5 mb-md-0 mb-3">
                                                <ul>
                                                    <li class="d-flex justify-content-between p-1 bdr-b">
                                                        <!-- <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in &#8377;)</span></strong> -->
                                                        <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in USD)</span></strong>
                                                        <?php if(isset($adultCount) && $adultCount > 0){ ?>
                                                        <span><?php echo $adultCount; ?> adult</span><?php }?>
                                                        <?php if(isset($childCount) && $childCount > 0) {?>
                                                        <span><?php echo $childCount; ?> child</span><?php }?>
                                                        <?php if(isset($infantCount) && $infantCount>0) {?>
                                                        <span><?php echo $infantCount; ?> infant</span><?php }?>
                                                    </li>
                                                    <li>
                                                        <ul class="bdr-b">
                                                            
                                                            <li class="text-left p-1"><strong class="fw-500">Base Fare</strong></li>
                                                            <?php 
                                                            $totalTax =0;
                                                            $totalAdultfare=0;
                                                            $totalChildfare=0;
                                                            $totalinfantfare =0;
                                                            if(isset($adultCount) && $adultCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][0]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalAdultfare=$fareListRef['PassengerFare'][0]['BaseFare']* $adultCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Adult (<?php echo $fareListRef['PassengerFare'][0]['BaseFare'] .'x'. $adultCount; ?>)</span><span><?php echo $totalAdultfare;  ?></span></li><?php } ?>
                                                            <?php if(isset($childCount) && $childCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][1]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalChildfare=$fareListRef['PassengerFare'][1]['BaseFare']* $childCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Child (<?php echo $fareListRef['PassengerFare'][1]['BaseFare'] .'x'. $childCount; ?>)</span><span><?php echo $totalChildfare; ?></span></li><?php } ?>
                                                            <?php if(isset($infantCount) && $infantCount > 0){ 
                                                                foreach($fareListRef['PassengerFare'][2]['TaxBreakUp'] as $taxdata){
                                                                    $totalTax +=  $taxdata['Amount'];
                                                                }
                                                                $totalinfantfare=$fareListRef['PassengerFare'][2]['BaseFare']* $infantCount;
                                                                ?>
                                                            <li class="d-flex justify-content-between p-1"><span>Infant (<?php echo $fareListRef['PassengerFare'][2]['BaseFare'] .'x'. $infantCount; ?>)</span><span><?php echo $totalinfantfare; ?></span></li><?php } ?>
                                                           <!-- tax calculation  -->

                                                            <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span><?Php echo $totalTax; ?></span></li>
                                                            <!-- <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li> -->
                                                        </ul>
                                                        <!-- <ul class="bdr-b">
                                                            <li class="d-flex justify-content-between p-1"><span>Discount</span><span>(-)0</span></li>
                                                            <li class="d-flex justify-content-between pl-1 pr-1 bdr-t"><span>Net Thomas Cook Charges</span><span>0</span></li>
                                                        </ul> -->
                                                    </li>
                                                    <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                        <strong class="fw-600">Total Fare</strong><strong>&#36; <?php echo $totalAdultfare+$totalChildfare+$totalinfantfare+$totalTax; ?></strong>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-md-7">
                                                <ul>
                                                    <li class="d-flex align-items-baseline p-1 bdr-b">
                                                        <strong class="fs-14 fw-600">Fare Rules </strong>
                                                        <?php 
                                                        $refundAllowed=$penaltyListRef['Penaltydetails'][0]['RefundAllowed'];
                                                        if($refundAllowed == 1){
                                                        ?>
                                                        <span class="uppercase-txt white-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>
                                                        <?php 
                                                        }else{
                                                        ?>
                                                        <span class="uppercase-txt white-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Not Refundable</span>
                                                        <?php 
                                                        }
                                                        ?>
                                                    </li>
                                                    <li>
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                            </li>
                                                            <li class="text-left">
                                                                <table class="w-100">
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#36; <?php echo $penaltyListRef['Penaltydetails'][0]['RefundPenaltyAmount'] ?></td>
                                                                    </tr>
                                                                    <!-- <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr> -->
                                                                </table>
                                                            </li>
                                                        </ul>
                                                        <ul>
                                                            <li class="d-flex justify-content-between p-1 mt-1">
                                                                <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                                <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                            </li>
                                                            <li class="text-left">
                                                                <table class="w-100">
                                                                    <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee</td>
                                                                        <td class="p-1">&#36; <?php echo $penaltyListRef['Penaltydetails'][0]['ChangePenaltyAmount'] ?></td>
                                                                    </tr>
                                                                    <!-- <tr class="bdr">
                                                                        <td class="bg-f0f3f5 p-1" style="width: 40%;">Thomas Cook Fee</td>
                                                                        <td class="p-1">&#8377; 500</td>
                                                                    </tr> -->
                                                                </table>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>Cancellation/Flight change charges are indicative. Thomas Cook does not guarantee the accuracy of this information. Airlines stop accepting cancellation/change requests 4 - 72 hours before departure of the flight, depending on the airline, in such scenarios airline will have to be contacted directly for cancellation/change. For "Non-Refundable" fares, certain Airline Tax components are also Non-Refundable. For exact cancellation/change fee, please call us on our Toll Free Number 1800 2099 100.</p>
                                    </div>
                                    <!-- ----------------baggage details-------------------- -->
                                    <div class="tab-pane p-lg-5 pt-5 p-3 pane3">
                                        <button class="close"><span>&times;</span></button>
                                        <ul class="fs-13">
                                            <li class="text-left p-1 bdr-b">
                                            <?php 
                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                            $stmtlocation->execute(array('airport_code' =>$segment['DepartureAirportLocationCode']));
                                            $airportLocationdep = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                            $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                            $stmtairline->bindParam(':code', $code);
                                            $stmtairline->execute();
                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                            if($onestop){
                                                    $stmtlocation->execute(array('airport_code' => $arrival));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                            }else{
                                                    $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                            }
                                            ?>

                                               <?php echo $airportLocationdep['city_name'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $airportLocation['city_name'] ?>
                                            </li>
                                            <?php
                                            foreach($pricedItinerary['OriginDestinations'] as $baggages) {
                                            $baggageRef = $baggages['ItineraryRef'];
                                            $baggageSegment = $FlightItineraryList[$baggageRef];
                                            $originRef = $baggages['SegmentRef'];
                                            $originSegment = $flightSegmentList[$originRef];

                                            ?>
                                                <li class="">
                                                    <ul class="row align-items-center pt-3 pb-3">
                                                        <li class="col-md-1 mb-md-0 mb-2">
                                                            <?php if($airlineLocation['image']){
                                                                ?>
                                                                <img src="images/emirates-logo.png" alt="">
                                                                <?php }else{ ?>
                                                                <img src="images/no-image-icon-1.jpg" alt="">
                                                            <?php
                                                            }
                                                            ?>
                                                            
                                                            
                                                        </li>
                                                        <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                            <strong>
                                                                <?php 
                                                                echo $airlineLocation['name']; 
                                                                ?>
                                                            </strong>
                                                            <span class="uppercase-txt"><?php echo $originSegment['DepartureAirportLocationCode'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $originSegment['ArrivalAirportLocationCode'] ?></span>
                                                        </li>
                                                        <li class="col-md-7">
                                                            <ul class="row bdr-b">
                                                                <li class="col-4">Checkin</li>
                                                                <li class="col-4">1 pcs/person</li>
                                                                <li class="col-4"><?php echo $baggageSegment['CheckinBaggage'][0]['Value']?> </li>
                                                            </ul>
                                                            <ul class="row">
                                                                <li class="col-4">Cabin</li>
                                                                <li class="col-4">1 pcs/person</li>
                                                                <li class="col-4"><?php echo $baggageSegment['CabinBaggage'][0]['Value']?></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                    
                                                </li>
                                            <?php
                                            }
                                            ?>
                                        </ul>
                                        <p class="fs-13 fw-500 text-left"><strong>Note: </strong>The information provided above is as retrieved from the airline reservation system. Travel Site does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure.</p>
                                    </div>

                                </div>
                            </div>
                        </li>
                    <?php
                        // }
                    }
                    ?>

                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!--  Login Modal -->
<?php
require_once("includes/login-modal.php");
?>
<!--  forgot Modal -->
<?php
require_once("includes/forgot-modal.php");
?>
<div class="modal flight-search-loading" id="FlightSearchLoading" tabindex="-1" role="dialog" aria-labelledby="ForgotPasswordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <form>
                            <div class="fs-16 fw-300 text-center mb-4">Please wait are searching for the cheapest fare for flights</div>
                            <div class="form-row flight-direction align-items-center justify-content-center mb-4">
                                <strong class="col-md-5 text-md-right text-center mb-md-0 mb-2">Kochi</strong>
                                <div class="col-md-1 d-flex flex-md-column flex-column-reverse align-items-center direction-icon">
                                    <span class="oneway d-flex">
                                        <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M29 6L19 0.226497V11.7735L29 6ZM0 7L20 7V5L0 5L0 7Z" fill="#4756CB" />
                                        </svg>
                                    </span>
                                    <span class="return d-flex">
                                        <svg width="29" height="12" viewBox="0 0 29 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 6L10 11.7735L10 0.226497L0 6ZM9 7L29 7V5L9 5V7Z" fill="#4756CB" />
                                        </svg>
                                    </span>
                                </div>
                                <strong class="col-md-5 text-md-left text-center mt-md-0 mt-2">Dubai</strong>
                            </div>
                            <div class="progress mb-5">
                                <div class="progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="row justify-content-center mb-5">
                                <div class="col-lg-8 col-md-10">
                                    <div class="row justify-content-between">
                                        <div class="col-4 justify-content-center">
                                            <div class="mb-4 d-flex justify-content-center">
                                                <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M47.0925 0.984076C44.6576 2.07045 40.874 4.16828 36.6034 6.60326L16.1495 3.75621C15.0017 3.59067 13.8318 3.67241 12.7181 3.99596L8.27894 5.25466C7.90433 5.25466 7.90433 5.62927 8.27894 5.77912L25.3613 13.2714C19.5922 16.7178 14.46 19.8646 12.2124 21.2881C11.8113 21.54 11.3596 21.7002 10.8894 21.7572C10.4192 21.8142 9.9423 21.7666 9.49268 21.6177L5.01981 20.2017C4.30954 19.9484 3.53311 19.951 2.82458 20.2092L0 21.363L9.44024 29.2299C9.91481 29.628 10.4921 29.8842 11.1057 29.969C11.7193 30.0538 12.3444 29.9638 12.9091 29.7094C17.4195 27.694 29.2947 22.2246 38.1356 17.6169C57.7653 7.20265 59.938 5.4045 59.938 3.75621C59.938 1.17138 52.4458 -1.48837 47.0888 0.984076H47.0925Z" fill="#969696" />
                                                    <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="trvl-dtls text-center fw-300">
                                                <div class="label">Depart</div>
                                                <div class="date">
                                                    <strong class="fw-500">11</strong>
                                                    <div>
                                                        Nov, 2022 <br>
                                                        Friday
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 justify-content-center">
                                            <div class="mb-4 d-flex justify-content-center">
                                                <svg width="60" height="39" viewBox="0 0 60 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.5678 0.984076C15.9499 2.07045 19.6514 4.16828 23.8292 6.60326L43.8389 3.75621C44.9618 3.59067 46.1063 3.67241 47.1958 3.99596L51.5385 5.25466C51.905 5.25466 51.905 5.62927 51.5385 5.77912L34.8272 13.2714C40.4709 16.7178 45.4917 19.8646 47.6905 21.2881C48.0829 21.54 48.5248 21.7002 48.9848 21.7572C49.4447 21.8142 49.9113 21.7666 50.3512 21.6177L54.7269 20.2017C55.4217 19.9484 56.1813 19.951 56.8745 20.2092L59.6377 21.363L50.4025 29.2299C49.9382 29.628 49.3735 29.8842 48.7732 29.969C48.1729 30.0538 47.5614 29.9638 47.0089 29.7094C42.5965 27.694 30.9792 22.2246 22.3303 17.6169C3.1269 7.20265 1.00133 5.4045 1.00133 3.75621C1.00133 1.17138 8.33087 -1.48837 13.5715 0.984076H13.5678Z" fill="#969696" />
                                                    <line y1="38" x2="60" y2="38" stroke="#969696" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="trvl-dtls text-center fw-300">
                                                <div class="label">Return</div>
                                                <div class="date">
                                                    <strong class="fw-500">19</strong>
                                                    <div>
                                                        Nov, 2022 <br>
                                                        Saturday
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 justify-content-center">
                                            <div class="mb-4 d-flex justify-content-center">
                                                <svg width="35" height="38" viewBox="0 0 35 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M24.7 22.8C27.1481 22.8001 29.5016 23.7453 31.2698 25.4385C33.0379 27.1316 34.0843 29.4419 34.1905 31.8877L34.2 32.3V34.2C34.2003 35.1587 33.8382 36.0821 33.1864 36.785C32.5345 37.488 31.641 37.9186 30.685 37.9905L30.4 38H3.8C2.84131 38.0003 1.91792 37.6382 1.21496 36.9864C0.511994 36.3345 0.0814023 35.441 0.00950022 34.485L0 34.2V32.3C0.000141441 29.8519 0.945329 27.4984 2.63845 25.7302C4.33158 23.9621 6.64193 22.9157 9.0877 22.8095L9.5 22.8H24.7ZM17.1 0C19.6196 0 22.0359 1.00089 23.8175 2.78249C25.5991 4.56408 26.6 6.98044 26.6 9.5C26.6 12.0196 25.5991 14.4359 23.8175 16.2175C22.0359 17.9991 19.6196 19 17.1 19C14.5804 19 12.1641 17.9991 10.3825 16.2175C8.60089 14.4359 7.6 12.0196 7.6 9.5C7.6 6.98044 8.60089 4.56408 10.3825 2.78249C12.1641 1.00089 14.5804 0 17.1 0Z" fill="#969696" />
                                                </svg>
                                            </div>
                                            <div class="trvl-dtls text-center fw-300">
                                                <div class="label">Traveller</div>
                                                <div class="date">
                                                    <strong class="fw-500">01</strong>
                                                    <div>
                                                        1 Adult
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fs-16 fw-300 text-center">
                                This may take upto a minite
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once("includes/footer.php");
?>
<script>
    /************Datepicker******************/
    $(function() {
        var dateFormat = "mm/dd/yy",
            from = $("#from")
            .datepicker({
                //defaultDate: "+1w",
                changeMonth: true,
                minDate: 0,
            })
            .on("change", function() {
                to.datepicker("option", "minDate", getDate(this));
            }),
            to = $("#to").datepicker({
                //defaultDate: "+1w",
                changeMonth: true
            })
            .on("change", function() {
                from.datepicker("option", "maxDate", getDate(this));
            });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
    });


    $(function() {
        $(".date-multy-city").datepicker({
            dateFormat: "D, M d",
            minDate: 0
        });
    });
    /*****************************************/

    $(document).ready(function() {


        $('.select-class').select2();
        $('.stops-select').select2();
        $('.price-select').select2();
        $('.opt-select').select2();
        $('.airline-select').select2();
        $('.dep-time-select').select2();
        $('.ret-time-select').select2();

        // $('[name=tab]').each(function(i,d){
        //     var p = $(this).prop('checked');
        //     //   console.log(p);
        //     if(p){
        //         $('.search-box').eq(i)
        //         .addClass('on');
        //     }    
        // });  

        // $('[name=tab]').on('change', function(){
        //     var p = $(this).prop('checked');

        //     // $(type).index(this) == nth-of-type
        //     var i = $('[name=tab]').index(this);

        //     $('.search-box').removeClass('on');
        //     $('.search-box').eq(i).addClass('on');
        // });

        $('.flight-search > input').click(function() {
            if ($('#return').is(':checked')) {
                $("#to").show().next(".icon").show()
            } else(
                $("#to").hide().next(".icon").hide()
            )
            if ($('#multi-city').is(':checked')) {
                $(".search-box.multi-city-search").css("display", "flex").siblings().hide()

            } else(
                $(".search-box.multi-city-search").hide().siblings().show()
            )
        })
        if ($('#return').is(':checked')) {
                $("#to").show().next(".icon").show()
            } else(
                $("#to").hide().next(".icon").hide()
            )
            if ($('#multi-city').is(':checked')) {
                $(".search-box.multi-city-search").css("display", "flex").siblings().hide()

            } else(
                $(".search-box.multi-city-search").hide().siblings().show()
            )

        // $('#multi-city').click(function() {
        //     $(".search-box.multi-city-search").show()
        //     $(".multi-city-search").siblings(".search-box").hide();
        // });


        $(".select-lbl").click(function() {
            $(this).parent(".person-select").toggleClass("open");
            $(".select-dropbox").toggle();
        })


        $('.add').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepUp();
        })
        $('.minus').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepDown();
        })

        /******************TAB WITHOUT ID*******************************/
        $('.panel .nav-tabs').on('click', 'a', function(e) {
            var tab = $(this).parent(),
                tabIndex = tab.index(),
                tabPanel = $(this).closest('.panel'),
                tabPane = tabPanel.find('.tab-pane').eq(tabIndex);
            tabPanel.find('.active').removeClass('active');
            tab.addClass('active');
            tabPane.addClass('active');
        });
        $('.tab-pane').on('click', 'button', function(e) {
            $(this).parent(".tab-pane").removeClass("active");
            $(this).parents(".tab-content").siblings(".nav-tabs").children(".nav-item").removeClass("active");
        });
        /***************************************************************/
    });

    $(".text-below-button").click(function() {
        $(this).parents('.modal').modal('hide');
    });
    $(".forgot-passward > button").click(function() {
        $(this).parents('.modal').modal('hide');
    });

    $('#FlightSearchLoading').modal({
        show: false
    })
    /**************Scroll To Top*****************/
    $(window).on('scroll', function() {
        if (window.scrollY > window.innerHeight) {
            $('#scrollToTop').addClass('active')
        } else {
            $('#scrollToTop').removeClass('active')
        }
    })

    $('#scrollToTop').on('click', function() {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    })
    /**********************************************/
</script>
</body>

</html>