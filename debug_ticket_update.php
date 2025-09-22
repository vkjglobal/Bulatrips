<?php
/**
 * Debug ticket number update process for booking 180
 */

// Set HTTP_HOST for CLI execution
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

include_once('includes/dbConnect.php');
include_once('includes/common_const.php');
include_once('includes/class.BookScript.php');

$objBook = new BookScript();
$endpoint = 'v1.1/TripDetails/{MFRef}';
$apiEndpoint = APIENDPOINT.$endpoint;
$bearerToken = BEARER;

echo "<h2>🔍 Debug Ticket Update Process for Booking 180</h2>";

// Get booking data
$mfRef = 'MF31554025';
$bookingId = 180;

echo "<h3>📋 Step 1: Database Passengers</h3>";
$stmt = $conn->prepare('SELECT id, first_name, last_name, e_ticket_number, passport_number FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
$stmt->execute();
$dbPassengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>ID</th><th>Name</th><th>Current Ticket</th><th>Passport</th></tr>";
foreach ($dbPassengers as $p) {
    $ticket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: red;">NULL</span>';
    echo "<tr><td>{$p['id']}</td><td>{$p['first_name']} {$p['last_name']}</td><td>$ticket</td><td>{$p['passport_number']}</td></tr>";
}
echo "</table>";

echo "<h3>🌐 Step 2: API Response Passengers</h3>";

// Get API response
$apiEndpoint = str_replace('{MFRef}', $mfRef, $apiEndpoint);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $bearerToken
));

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $responseData = json_decode($response, true);
    
    if (!empty($responseData) && $responseData['Success']) {
        $tripDetails = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
        $passengerDetail = $tripDetails['PassengerInfos'];
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Name</th><th>Ticket Number</th><th>Passport</th><th>Match Status</th></tr>";
        
        foreach ($passengerDetail as $passengerInfo) {
            $apiPassport = $passengerInfo['Passenger']['PassportNumber'];
            $apiTicket = isset($passengerInfo['ETickets'][0]['ETicketNumber']) ? $passengerInfo['ETickets'][0]['ETicketNumber'] : 'No ticket';
            $apiName = $passengerInfo['Passenger']['PaxName']['PassengerFirstName'] . ' ' . $passengerInfo['Passenger']['PaxName']['PassengerLastName'];
            
            // Check if this passport exists in database
            $matchFound = false;
            foreach ($dbPassengers as $dbP) {
                if ($dbP['passport_number'] === $apiPassport) {
                    $matchFound = true;
                    break;
                }
            }
            
            $matchStatus = $matchFound ? '<span style="color: green;">✅ Match</span>' : '<span style="color: red;">❌ No Match</span>';
            
            echo "<tr><td>$apiName</td><td><strong>$apiTicket</strong></td><td>$apiPassport</td><td>$matchStatus</td></tr>";
        }
        echo "</table>";
        
        echo "<h3>🔧 Step 3: Manual Update (Simulate Database Update)</h3>";
        echo "<form method='post'>";
        echo "<input type='submit' name='force_update' value='Force Update Tickets Now' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
        echo "</form>";
        
        // Handle force update
        if (isset($_POST['force_update'])) {
            echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>🔄 Executing Update Process:</h4>";
            
            $ticketStatus = $tripDetails['TicketStatus'];
            $updatedCount = 0;
            
            foreach ($passengerDetail as $passengerInfo) {
                $ticketNumber = isset($passengerInfo['ETickets'][0]['ETicketNumber']) ? $passengerInfo['ETickets'][0]['ETicketNumber'] : '';
                $passportNumber = $passengerInfo['Passenger']['PassportNumber'];
                $passengerName = $passengerInfo['Passenger']['PaxName']['PassengerFirstName'] . ' ' . $passengerInfo['Passenger']['PaxName']['PassengerLastName'];
                
                if (!empty($ticketNumber)) {
                    // Try the original update method (by passport)
                    $stmt = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus, e_ticket_number = :ticketNumber WHERE flight_booking_id = :bookingId AND passport_number = :PassportNumber');
                    $result = $stmt->execute([
                        'ticketStatus' => $ticketStatus,
                        'ticketNumber' => $ticketNumber,
                        'bookingId' => $bookingId,
                        'PassportNumber' => $passportNumber
                    ]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        echo "✅ Updated $passengerName (Passport: $passportNumber) → $ticketNumber<br>";
                        $updatedCount++;
                    } else {
                        echo "❌ Failed to update $passengerName (Passport: $passportNumber) - No matching record<br>";
                        
                        // Try alternative update by name
                        $nameParts = explode(' ', $passengerName);
                        $firstName = $nameParts[0];
                        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
                        
                        $stmt2 = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus, e_ticket_number = :ticketNumber WHERE flight_booking_id = :bookingId AND first_name = :firstName AND last_name = :lastName');
                        $result2 = $stmt2->execute([
                            'ticketStatus' => $ticketStatus,
                            'ticketNumber' => $ticketNumber,
                            'bookingId' => $bookingId,
                            'firstName' => $firstName,
                            'lastName' => $lastName
                        ]);
                        
                        if ($result2 && $stmt2->rowCount() > 0) {
                            echo "✅ Updated $passengerName (by name) → $ticketNumber<br>";
                            $updatedCount++;
                        } else {
                            echo "❌ Failed to update $passengerName (by name) - No matching record<br>";
                        }
                    }
                }
            }
            
            echo "<br><strong>Total Updated: $updatedCount passengers</strong>";
            echo "</div>";
            
            // Show updated database state
            echo "<h4>📊 Updated Database State:</h4>";
            $stmt = $conn->prepare('SELECT first_name, last_name, e_ticket_number, passport_number FROM travellers_details WHERE flight_booking_id = 180 ORDER BY id');
            $stmt->execute();
            $updatedPassengers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Name</th><th>Updated Ticket</th><th>Passport</th></tr>";
            foreach ($updatedPassengers as $p) {
                $ticket = !empty($p['e_ticket_number']) ? $p['e_ticket_number'] : '<span style="color: red;">NULL</span>';
                echo "<tr><td>{$p['first_name']} {$p['last_name']}</td><td>$ticket</td><td>{$p['passport_number']}</td></tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<span style='color: red;'>❌ API Error or No Success Response</span>";
    }
} else {
    echo "<span style='color: red;'>❌ Failed to get API response</span>";
}

echo "<h3>💡 Analysis:</h3>";
echo "<ul>";
echo "<li><strong>The issue:</strong> Database update depends on passport number matching between API and database</li>";
echo "<li><strong>The solution:</strong> If passport numbers don't match, update by passenger name instead</li>";
echo "<li><strong>Current status:</strong> Ticket numbers are showing on page but may not be saved to database</li>";
echo "</ul>";

?>
