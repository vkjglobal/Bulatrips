<?php
// download_ticket.php

// Include the database configuration file

// Include the dompdf autoload file
session_start();
require_once('includes/dbConnect.php');
require_once 'vendor/autoload.php';
include_once('includes/class.Airport.php');

use Dompdf\Dompdf;

// Check if the booking ID is provided as a parameter in the URL
// $booking_id= 238;
$booking_id= $_GET['value'];
// $value = $_GET['value'];
// Function to get ticket content based on booking ID
function getTicketContent($booking_id,$conn ) {
    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid and user_id = :userid');

    $stmtbookingid->execute(array('bookingid' => $booking_id, 'userid' => $_SESSION['user_id']));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    if (isset($bookingData['mf_reference'])) {

        $apiEndpoint = 'https://restapidemo.myfarebox.com/api/v1.1/TripDetails/{MFRef}';
        $bearerToken = '18AEA8F0-5B21-41ED-9993-DD7A8123B0D2-1560';
    
        // Set the MFRef value for the endpoint
        $mfRef = $bookingData['mf_reference'];
        $apiEndpoint = str_replace('{MFRef}', $mfRef, $apiEndpoint);
    
        // Send the API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $bearerToken
        ));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        // Process the API response
        if ($response === false) {
            // Error handling
            echo 'Error: ' . curl_error($ch);
        } else {
            // Process the response data
            $responseData = json_decode($response, true);
            // Handle the response data as needed
        }
        // Handle the API response
    
        if ($response) {
            $responseData = json_decode($response, true);
        }
    
       
        $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
        $itinerariesDetail = $tripDetails['Itineraries'][0]['ItineraryInfo']['ReservationItems'];
        $passengerDetail =  $tripDetails['PassengerInfos'];
        
        $onewaysegment = [];
        $returnsegment = [];

        foreach ($itinerariesDetail as $index => $flight) {
            if ($index < $bookingData['stops'] + 1) {
                $onewaysegment[] = $flight;
            }
            if ($index > $bookingData['stops']) {
                $returnsegment[] = $flight;
            }
        }
        $onewaysegmentLast = end($onewaysegment);
        $returnsegmentLast = end($returnsegment);
    }
    // Modify this function to fetch data from your database based on the booking ID
    // For this example, we'll use a dummy ticket content
    // Replace this with your actual database query to retrieve the ticket content
    // <span style="font-size: 18px; line-height: 1.2; font-weight: 400;">Trip ID&nbsp;:&nbsp;<strong style="font-size: 18px; line-height: 1.2;">'.$bookingData['id'].'</strong></span>
                                    
    $ticket_content = '<table style="width: 100%; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.2;">
    <tr>
        <td>
            <table style="width: 700px; margin: 0 auto; border: 1px solid #000000; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border-bottom: 1px solid #000000; padding-top: 20px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="text-align: left; vertical-align: bottom;">
                                        <strong style="font-size: 30px; line-height: 1.2;">Ticket &nbsp;&nbsp;</strong>
                                    </td>
                                    <td style="text-align: right;">
                                        <img src="https://bulatrips.com/images/Image-Logo-vec.png" style="width:140px;">
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="text-align: left;">
                                        <strong style="font-size: 14px; line-height: 1.2;">'.$tripDetails['Origin'] .' to '.$onewaysegmentLast['ArrivalAirportLocationCode'].'</strong>';
                                        $datetime = $itinerariesDetail[0]['DepartureDateTime'];
                                        list($date, $time) = explode("T", $datetime);
                                        $date=date("d F Y", strtotime($date));
                                        $arrivaldatetime=  $onewaysegmentLast['ArrivalDateTime'];
                                        list($datearri, $timearri) = explode("T", $arrivaldatetime);
                                        $datearri=date("d F Y", strtotime($datearri));
                                       
                                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                        $stmtlocation->execute(array('airport_code' => $onewaysegment[0]['DepartureAirportLocationCode']));
                                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                        $stmtlocationArrival = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                        $stmtlocationArrival ->execute(array('airport_code' => $onewaysegmentLast['ArrivalAirportLocationCode']));
                                        $airportLocationArrival  = $stmtlocationArrival ->fetch(PDO::FETCH_ASSOC);
                                        $baggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][0];
                                        $cabinbaggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][0];

                                        // Step 1: Convert datetime strings to DateTime objects
                                        $datetime1 = new DateTime($datetime);
                                        $datetime2 = new DateTime($arrivaldatetime);

                                        // Step 2: Calculate the time difference between the two DateTime objects
                                        $time_difference = $datetime1->diff($datetime2);

                                        // Step 3: Extract hours and minutes from the time difference

                                        $hours = $time_difference->days * 24 + $time_difference->h;

                                        // Step 4: Extract remaining minutes
                                        $minutes = $time_difference->i;

      $ticket_content .=                    '<span style="font-size: 14px; line-height: 1.2;"> '.$date.'</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%;">
                                <tr>
                                    <td>
                                        <table style="width: 100%;">
                                            <tr>
                                                <td>
                                                    <table style="width: 100%;">
                                                        <tr>
                                                            <td style="text-align: left;"><strong>'.$onewaysegment[0]['OperatingAirlineCode'].'</strong></td>
                                                        </tr>
                                                       
                                                        <tr>
                                                            <td style="text-align: left; font-size: 14px;">Fare type: <strong>'.$onewaysegment[0]['CabinClassType'].'</strong></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="text-align: right; width: 43%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td style="font-size: 16px;">'.$tripDetails['Origin'] .' &nbsp;<strong>'.$time.'</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">
                                                                        '.$date.' <br>
                                                                        '.$airportLocation['airport_name'].' - '.$airportLocation['city_name'].' , '. $airportLocation['country_name'].'<br>
                                                                        Terminal '.$onewaysegment[0]['DepartureTerminal'].'
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="text-align: center; width: 14%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td>
                                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <circle cx="7" cy="7" r="6.5" stroke="black"/>
                                                                            <path d="M3.5 3.872L6.91686 7.30582L10.7039 3.5" stroke="black"/>
                                                                            </svg>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">'.$hours.'h '.$minutes.'min <br> </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="text-align: left; width: 43%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td style="font-size: 16px;"><strong>'.$timearri.'</strong>&nbsp; '.$onewaysegmentLast['ArrivalAirportLocationCode'].'</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">
                                                                        '.$datearri.' <br>
                                                                            '.$airportLocationArrival['airport_name'].' -  '.$airportLocationArrival['city_name'].', '.$airportLocationArrival['country_name'].'
                                                                            <br>Terminal '.$onewaysegmentLast['ArrivalTerminal'].'
                                                                            </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 5px;">
                                        <table style="width: 100%; font-size: 14px;">
                                            <tr>
                                                <td style="text-align: left;">
                                                    Baggage (per Adult/Child) - Check-in: '. $baggageInfo.', Cabin: '. $cabinbaggageInfo.'
                                                </td>
                                                <td style="text-align: right;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
                    if ($returnsegment) {
                        $datetimereturn = $returnsegment[0]['DepartureDateTime'];
                        list($datereturn, $timereturn) = explode("T", $datetimereturn);
                        $datereturn=date("d F Y", strtotime($datereturn));
                        $arrivaldatetimereturn=  $returnsegmentLast['ArrivalDateTime'];
                        list($datearrireturn, $timearrireturn) = explode("T", $arrivaldatetimereturn);
                        $datearrireturn=date("d F Y", strtotime($datearrireturn));


                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                        $stmtlocation->execute(array('airport_code' => $returnsegment[0]['DepartureAirportLocationCode']));
                        $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                        $stmtlocationArrival = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                        $stmtlocationArrival ->execute(array('airport_code' => $returnsegmentLast['ArrivalAirportLocationCode']));
                        $airportLocationArrival  = $stmtlocationArrival ->fetch(PDO::FETCH_ASSOC);
                        $count=count($onewaysegment);
                        $baggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['BaggageInfo'][$count];
                        $cabinbaggageInfo = $tripDetails['TripDetailsPTC_FareBreakdowns'][0]['CabinBaggageInfo'][$count];

                        $datetime1 = new DateTime($datetimereturn);
                        $datetime2 = new DateTime($arrivaldatetimereturn);

                        // Step 2: Calculate the time difference between the two DateTime objects
                        $time_difference = $datetime1->diff($datetime2);

                        // Step 3: Extract hours and minutes from the time difference

                        $hours = $time_difference->days * 24 + $time_difference->h;

                        // Step 4: Extract remaining minutes
                        $minutes = $time_difference->i;

 $ticket_content .=             ' <tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="text-align: left;">
                                        <strong style="font-size: 14px; line-height: 1.2;">'.$returnsegment[0]['DepartureAirportLocationCode'].' to '.$returnsegmentLast['ArrivalAirportLocationCode'].'</strong>
                                        <span style="font-size: 14px; line-height: 1.2;">'.$datereturn.'</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%;">
                                <tr>
                                    <td>
                                        <table style="width: 100%;">
                                            <tr>
                                                <td>
                                                    <table style="width: 100%;">
                                                        <tr>
                                                            <td style="text-align: left;"><strong>'.$returnsegment[0]['OperatingAirlineCode'].'</strong></td>
                                                        </tr>
                                                       
                                                        <tr>
                                                            <td style="text-align: left; font-size: 14px;">Fare type: <strong>'.$returnsegment[0]['CabinClassType'].'</strong></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td style="text-align: right; width: 43%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td style="font-size: 16px;">'.$returnsegment[0]['DepartureAirportLocationCode'].'<strong> &nbsp; '.$timereturn.'</strong></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">
                                                                        '.$datereturn.' <br>
                                                                        '.$airportLocation['airport_name'].' - '.$airportLocation['city_name'].' , '. $airportLocation['country_name'].'
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="text-align: center; width: 14%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td> 
                                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <circle cx="7" cy="7" r="6.5" stroke="black"/>
                                                                            <path d="M3.5 3.872L6.91686 7.30582L10.7039 3.5" stroke="black"/>
                                                                            </svg>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">'.$hours.'h '.$minutes.'min <br> </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                            <td style="text-align: left; width: 43%;">
                                                                <table style="width: 100%;">
                                                                    <tr>
                                                                        <td style="font-size: 16px;"><strong>'.$timearrireturn.'</strong> &nbsp; '.$returnsegmentLast['ArrivalAirportLocationCode'].'</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="font-size: 14px;">
                                                                        '.$datearrireturn.'<br>
                                                                        '.$airportLocationArrival['airport_name'].' -  '.$airportLocationArrival['city_name'].', '.$airportLocationArrival['country_name'].'
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 5px;">
                                        <table style="width: 100%; font-size: 14px;">
                                            <tr>
                                                <td style="text-align: left;">
                                                    Baggage (per Adult/Child) - Check-in:'. $baggageInfo.', Cabin: '. $cabinbaggageInfo.'
                                                </td>
                                                
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
                    }


 $ticket_content .=             '<tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%; border: 1px solid #000000; border-collapse: collapse;">
                                <tr>
                                    <td style="border: 1px solid #000000; padding: 10px 5px; text-align: left;"><strong>Travellers</strong></td>
                                    <td style="border: 1px solid #000000; padding: 10px 5px;"><strong>Airline PNR</strong></td>
                                    <td style="border: 1px solid #000000; padding: 10px 5px;"><strong>Ticket No.</strong></td>
                                </tr>';



                                foreach($passengerDetail as  $passengerDetails) {
                                  //  $pssenger = $passengerDetails['Passenger'];
                                  if(isset($passengerDetails['ETickets'][0]['ETicketNumber'])){
                                    $ticket=$passengerDetails['ETickets'][0]['ETicketNumber'];
                                  }else{
                                    $ticket='';
                                  }
                                  

                                    

 $ticket_content .=                '<tr>
                                    <td style="border: 1px solid #000000; padding: 5px; text-align: left;"><b>'.$passengerDetails['Passenger']['PaxName']['PassengerTitle'] .'</b>&nbsp;&nbsp;'. $passengerDetails['Passenger']['PaxName']['PassengerFirstName'].' '.$passengerDetails['Passenger']['PaxName']['PassengerLastName'].'</td>
                                    <td style="border: 1px solid #000000; padding: 5px;">'.$onewaysegment[0]['AirlinePNR'].'</td>
                                    <td style="border: 1px solid #000000; padding: 5px;">'.$ticket.'</td>
                                </tr>';
                                }

                                $basetotal = 0;
                                $tax = 0;
                                $total = 0;
                                // echo '<pre/>';
                                // print_r($tripDetails);
                                // print_r($tripDetails['TripDetailsPTC_FareBreakdowns']);
                                foreach ($tripDetails['TripDetailsPTC_FareBreakdowns'] as $fareDetails) {
                                    $basetotal += $fareDetails['TripDetailsPassengerFare']['EquiFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                                    $tax += $fareDetails['TripDetailsPassengerFare']['Tax']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                                    $total += $fareDetails['TripDetailsPassengerFare']['TotalFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                                
                                }
                               $extraServiceAmount = $tripDetails['ExtraServices']['Services'][0]['ServiceCost']['Amount'];
                                // echo '<pre/>';echo $basetotal;
 $ticket_content .=                            '</table>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #000000;">
                            <table style="width: 100%;">
                               
                               
                               
                                <tr>
                                    <td style="text-align: left;"><strong>Fare breakup</strong></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;">
                                        <table style="width: 40%; border-spacing: 15px;">
                                            <tr>
                                                <td>Base Fare:</td>
                                                <td><span style="display: inline-block; width: 50%;">$</span><span style="display: inline-block; width: 50%; text-align: right;">'.$basetotal+$tripDetails['ClientMarkup']['Amount'].'</span></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>Total Extra service Amount :</td>
                                                <td><span style="display: inline-block; width: 50%;">$</span><span style="display: inline-block; width: 50%; text-align: right;">'.$extraServiceAmount.'</span></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>Other Charges and Taxes:</td>
                                                <td><span style="display: inline-block; width: 50%;">$</span><span style="display: inline-block; width: 50%; text-align: right;">'.$tax.'</span></td>
                                            </tr>
                                            
                                            <tr>
                                                <td><strong>Total Fare:</strong></td>
                                                <td><strong><span style="display: inline-block; width: 50%;">$</span><span style="display: inline-block; width: 50%; text-align: right;">'.$total+$tripDetails['ClientMarkup']['Amount']+$extraServiceAmount.'</span></strong></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="width: 100%; border-spacing: 10px;">
                                <tr>
                                    <td><a href="#" style="color: #000000; font-weight: bold; text-decoration: none;">Airline helpline</a></td>
                                    <td>Need Help? Call <a href="tel:+91 9595 333 333" style="color: #000000; font-weight: bold; text-decoration: none;">+91 9595 333 333</a></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>';

    return $ticket_content;
}

if (isset($booking_id)) {
    $booking_id = $booking_id;

    // Retrieve ticket content from the database
    $ticket_content = getTicketContent($booking_id,$conn);

    // Create a new Dompdf instance
    $dompdf = new Dompdf();

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);

    // Load the ticket content into Dompdf
    //  var_dump($ticket_content);
    $dompdf->loadHtml($ticket_content);

    // (Optional) Set PDF options, e.g., paper size, orientation, etc.
    $dompdf->setPaper('A4', 'portrait');

    // Render the PDF content
    $dompdf->render();

    // Set appropriate headers for download
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=ticket_$booking_id.pdf");

    // Output the PDF to the browser
    $dompdf->stream();
    exit;
}
