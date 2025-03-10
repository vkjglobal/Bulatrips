<?php
// download_ticket.php
// Include the database configuration file
// Include the dompdf autoload file
session_start();
error_reporting(0);
require_once('includes/dbConnect.php');
require_once 'vendor/autoload.php';
include_once('includes/class.Airport.php');
include_once('includes/class.Booking.php');

use Dompdf\Dompdf;
// date_default_timezone_set('Pacific/Fiji');
$booking_id= $_GET['value'];

$stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid');
$stmtbookingid->execute(array('bookingid' => $booking_id));
$bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);

function getInvoiceContent($booking_id,$conn ) {
    $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid');

    $stmtbookingid->execute(array('bookingid' => $booking_id));
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

    $bookingdate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'];
    list($date, $time) = explode("T", $bookingdate);
    $date=date("d F Y", strtotime($date));

    $datetime = $itinerariesDetail[0]['DepartureDateTime'];
    list($dateTravel, $time) = explode("T", $datetime);
    $dateTravel=date("d F Y", strtotime($dateTravel));
    //booking details
    $booking = new Booking($conn);
    $resultBooking = $booking->getBookingDetailsbyId($bookingData['id']);
    
    $ticket_content = '<table style="width: 100%; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.2;">
        <tr>
            <td>
                <table style="width: 700px; margin: 0 auto; border: 1px solid #000000; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border-bottom: 1px solid #000000; padding-top: 20px;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="text-align: left;">
                                        <img src="https://bulatrips.com/images/Image-Logo-vec.png" style="width:140px;">

                                            </svg>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 5px solid #000000;">
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td style="text-align: left; font-weight: normal; font-size: 14px; width: 33%;">
                                                        Xpay Global Pte. Ltd.<br>
                                                        Email Id: contact@bulatrips.com<br>
                                                        Tel No: 1 562 867 5309 <br>
                                                        Website : www.bulatrips.com
                                                    </td>
                                                    <td style="text-align: right; vertical-align: bottom; font-weight: normal;">
                                                        <strong>Date: </strong>'. date("l jS \of F Y h:i:s A") .' <br>
                                                        <strong>Description Of Service:</strong> Itinerary invoice Information for airtrip
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right; border-bottom: 2px solid #000000;"><strong>Invoice No. '.$booking_id.'</strong></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border-bottom: 1px solid #000000;">
                                <table style="width: 50%;">
                                    <tr>
                                        <td style="text-align: left; font-weight: bold;">Booking Date</td>
                                        <td style="text-align: right;">'.$date.'</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: bold;">Travel Date</td>
                                        <td style="text-align: right;">'.$dateTravel.'</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: bold;">Trip ID</td>
                                        <td style="text-align: right;">'.$booking_id.'</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; font-weight: bold;">Booked by</td>
                                        <td style="text-align: right;">'.$resultBooking[0]['contact_email'].'</td>
                                    </tr>
                                   
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #000000;">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="font-size: 16px; font-weight: bold; text-align: center;" colspan="2">Flight booking</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table style="width: 100%; border: 1px solid #000000; border-collapse: collapse;">
                                                <tr>
                                                    <td style="text-align: left; border: 1px solid #000000; padding: 10px 5px; width: 50%;"><strong>Pax Details</strong></td>
                                                    <td style="text-align: right; border: 1px solid #000000; padding: 10px 5px; width: 50%;"><strong>Ticket No.</strong></td>
                                                </tr>';
                                                foreach($passengerDetail as  $passengerDetails) {
                                                    //  $pssenger = $passengerDetails['Passenger'];
                                                    if(isset($passengerDetails['ETickets'][0]['ETicketNumber'])){
                                                      $ticket=$passengerDetails['ETickets'][0]['ETicketNumber'];
                                                    }else{
                                                      $ticket='';
                                                    }

                                            $ticket_content .=          '<tr>
                                                    <td style="text-align: left; border: 1px solid #000000; padding: 5px;">'.$passengerDetails['Passenger']['PaxName']['PassengerTitle'] .'</b>&nbsp;&nbsp;'. $passengerDetails['Passenger']['PaxName']['PassengerFirstName'].' '.$passengerDetails['Passenger']['PaxName']['PassengerLastName'].'</td>
                                                    <td style="text-align: right; border: 1px solid #000000; padding: 5px;">'.$onewaysegment[0]['AirlinePNR'].'|'.$ticket.'</td>
                                                </tr>';
                                                }
                                                if ($returnsegment) {
                                                    $type="Return";
                                                }else{
                                                    $type="One Way";
                                                }
                                               

                                 $ticket_content .=          '<tr>
                                                    <td colspan="2" style="padding: 10px;">
                                                    '.$onewaysegment[0]['OperatingAirlineCode'].'
                                                        '.$tripDetails['Origin'] .' to '.$onewaysegmentLast['ArrivalAirportLocationCode'].'
                                                        '.$onewaysegment[0]['CabinClassType'].' '.$type.'
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    
                                </table>
                            </td>
                        </tr>';
                        $extraservices=0;
                        if ($tripDetails['ExtraServices']) {

                            $ticket_content .= '<tr>
                                <td style="border-bottom: 1px solid #000000;">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td style="font-size: 16px; font-weight: bold; text-align: center;" colspan="2">Extra Services</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table style="width: 100%; border: 1px solid #000000; border-collapse: collapse;">
                                                    <tr>
                                                        <td style="text-align: left; border: 1px solid #000000; padding: 10px 5px; width: 50%;"><strong>Services</strong></td>
                                                        <td style="text-align: right; border: 1px solid #000000; padding: 10px 5px; width: 50%;"><strong>Amount</strong></td>
                                                    </tr>';
                                                    foreach($tripDetails['ExtraServices']['Services'] as  $services) {
                                                        //  $pssenger = $passengerDetails['Passenger'];
                                                        // if(isset($passengerDetails['ETickets'][0]['ETicketNumber'])){
                                                        // $ticket=$passengerDetails['ETickets'][0]['ETicketNumber'];
                                                        // }else{
                                                        // $ticket='';
                                                        // }
                                                        $extraservices +=$services['ServiceCost']['Amount'];

                                                $ticket_content .=          '<tr>
                                                        <td style="text-align: left; border: 1px solid #000000; padding: 5px;">'. $services['Description'].'</td>
                                                        <td style="text-align: right; border: 1px solid #000000; padding: 5px;">'.$services['ServiceCost']['CurrencyCode'].' '.$services['ServiceCost']['Amount'].'</td>
                                                    </tr>';
                                                    }
                                                

                                    $ticket_content .=  '</table>
                                            </td>
                                        </tr>
                                        
                                    </table>
                                </td>
                            </tr>';
                        }
                        $basetotal = 0;
                        $tax = 0;
                        $total = 0;
                        foreach ($tripDetails['TripDetailsPTC_FareBreakdowns'] as $fareDetails) {
                            $basetotal += $fareDetails['TripDetailsPassengerFare']['EquiFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                            $tax += $fareDetails['TripDetailsPassengerFare']['Tax']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                            $total += $fareDetails['TripDetailsPassengerFare']['TotalFare']['Amount'] * $fareDetails['PassengerTypeQuantity']['Quantity'];
                        
                        }
                        $ticket_content .= '<tr>
                            <td style="border-bottom: 1px solid #000000;">
                                <table style="width:100%; border-collapse: collapse;">
                                    <tr>
                                        <th style="font-size: 16px; text-align: left; padding: 10px 5px; width: 50%; border-bottom: 1px solid #000000;"><strong>Description</strong></th>
                                        <th style="font-size: 16px; text-align: right; padding: 10px 5px; width: 50%; border-bottom: 1px solid #000000;"><strong>Amount USD</strong></th>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding: 10px 5px; width: 50%;">Base Fare:</td>
                                        <td style="text-align: right; padding: 10px 5px; width: 50%;">'.$basetotal+$tripDetails['ClientMarkup']['Amount'].'</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding: 10px 5px; width: 50%;">Other Charges and Taxes:</td>
                                        <td style="text-align: right; padding: 10px 5px; width: 50%;">'.$tax.'</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding: 10px 5px; width: 50%;">Extra Services Charges:</td>
                                        <td style="text-align: right; padding: 10px 5px; width: 50%;">'.$extraservices.'</td>
                                    </tr>
                                   
                                    <tr>
                                        <td style="text-align: left; padding: 10px 5px; width: 50%;">Total Fare:</td>
                                        <td style="text-align: right; padding: 10px 5px; width: 50%;">'.$total+$tripDetails['ClientMarkup']['Amount']+$extraservices.'</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table style="width:100%">
                                    <tr>
                                        <td style="text-align: left;">
                                            <strong>Note:</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;">
                                            <ul>
                                               
                                                <li>
                                                    This is an electronically generated invoice and does not require a physical signature.
                                                </li>
                                            </ul>
                                        </td>
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

if (isset($bookingData['id'])) {
    
    $booking_id = $bookingData['id'];
    $reference_id = $bookingData['mf_reference'];

    // Retrieve ticket content from the database
    $ticket_content = getInvoiceContent($reference_id,$conn);

    // Create a new Dompdf instance
    $dompdf = new Dompdf();

    $dompdf = new Dompdf(['isRemoteEnabled' => true]);
    
    // var_dump($ticket_content);
    $dompdf->loadHtml($ticket_content);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=invoice_$reference_id.pdf");

    // Output the PDF to the browser
    $dompdf->stream();
    exit;
}