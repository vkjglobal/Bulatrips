<?php
/**
 * Cron Job: Update Ticket Status from Mystifly TripDetails API
 * 
 * Purpose: 
 * - Calls TripDetails API for each booking with MF reference
 * - Updates ticket_status and e_ticket_number for passengers
 * - Stores complete passenger response data as JSON
 * - Handles multiple passengers per booking
 * 
 * Usage: Run every 15-30 minutes to keep ticket data synchronized
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
include_once __DIR__ . '/../includes/common_const.php';
include_once __DIR__ . '/../includes/class.cancel.php';
include_once __DIR__ . '/../includes/class.Booking.php';
include_once __DIR__ . '/../includes/dbConnect.php';
include_once __DIR__ . '/../includes/mock_mystifly.php';

// Set execution timeout for long-running cron
ini_set('max_execution_time', 600); // 10 minutes
ini_set('default_socket_timeout', 120); // 2 minutes for API calls

$objCancel = new Cancel();
$booking = new Booking($conn);

// Log start
$objCancel->_writeLog('=== TripDetails Update Cron Started at ' . date('Y-m-d H:i:s') . ' ===', 'tripDetailsUpdate.txt');

// Get all bookings that need ticket status updates
$query = "
    SELECT DISTINCT tb.id as booking_id, tb.mf_reference, tb.ticket_status as booking_ticket_status,
           COUNT(td.id) as passenger_count,
           MAX(td.created_at) as last_passenger_update
    FROM temp_booking tb
    INNER JOIN travellers_details td ON tb.id = td.flight_booking_id
    WHERE tb.mf_reference IS NOT NULL 
      AND tb.mf_reference != ''
      AND tb.ticket_status IN ('TktInProcess', 'Ticketed', 'CONFIRMED', 'BOOKINGINPROCESS')
      AND (td.ticket_status IS NULL OR td.ticket_status = '' OR td.ticket_status IN ('TktInProcess', 'Ticketed'))
      AND (td.last_api_update IS NULL OR td.last_api_update < DATE_SUB(NOW(), INTERVAL 30 MINUTE))
    GROUP BY tb.id, tb.mf_reference
    ORDER BY ISNULL(last_passenger_update), last_passenger_update ASC
    LIMIT 20
";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $objCancel->_writeLog('Found ' . count($bookings) . ' bookings to update', 'tripDetailsUpdate.txt');
    
    if (empty($bookings)) {
        $objCancel->_writeLog('No bookings need updates at this time', 'tripDetailsUpdate.txt');
        echo "<h3>No bookings need updates</h3>";
        exit;
    }
    
} catch (Exception $e) {
    $objCancel->_writeLog('Database error: ' . $e->getMessage(), 'tripDetailsUpdate.txt');
    echo "<h3>Database Error: " . $e->getMessage() . "</h3>";
    exit;
}

echo "<h2>🔄 TripDetails Update Cron Job</h2>";
echo "<p>Processing " . count($bookings) . " bookings...</p>";

$successCount = 0;
$errorCount = 0;
$updatedPassengers = 0;

foreach ($bookings as $bookingData) {
    $bookingId = $bookingData['booking_id'];
    $mfRef = $bookingData['mf_reference'];
    $passengerCount = $bookingData['passenger_count'];
    
    $objCancel->_writeLog("Processing booking $bookingId (MF: $mfRef) with $passengerCount passengers", 'tripDetailsUpdate.txt');
    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
    echo "<h4>📋 Booking $bookingId - $mfRef ($passengerCount passengers)</h4>";
    
    try {
        // Call TripDetails API
        $endpoint = 'v1.1/TripDetails/' . urlencode($mfRef);
        $apiEndpoint = APIENDPOINT . $endpoint;
        
        if (MOCK_MODE) {
            // Use mock response
            $responseData = MockMystifly::getTripDetailsResponse($mfRef);
            $response = json_encode($responseData);
            $httpCode = 200;
            $objCancel->_writeLog("MOCK MODE: Using mock TripDetails for $mfRef", 'tripDetailsUpdate.txt');
        } else {
            // Call real API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . BEARER
            ));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($response === false) {
                throw new Exception("API call failed: " . $curlError);
            }
            
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
        }
        
        $objCancel->_writeLog("API Response for $mfRef: " . substr($response, 0, 500) . '...', 'tripDetailsUpdate.txt');
        
        // Process successful response
        if (!empty($responseData) && isset($responseData['Success']) && $responseData['Success']) {
            $travelItinerary = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
            $passengerInfos = $travelItinerary['PassengerInfos'] ?? [];
            $overallTicketStatus = $travelItinerary['TicketStatus'] ?? '';
            
            // Extract additional booking details (same as confirmation page)
            $bookingStatus = $travelItinerary['BookingStatus'] ?? '';
            $ticketTimeLimit = $travelItinerary['TicketingTimeLimit'] ?? '';
            $bookingDate = $responseData['Data']['TripDetailsResult']['BookingCreatedOn'] ?? '';
            $voidWindow = $travelItinerary['VoidingWindow'] ?? '';
            
            // Apply same booking status logic as confirmation page
            if (empty($bookingStatus)) {
                if ($overallTicketStatus == "Ticketed") {
                    $bookingStatus = "booked";
                }
            }
            
            echo "<p>✅ API Success - Overall Status: <strong>$overallTicketStatus</strong></p>";
            echo "<p>📋 Booking Status: <strong>$bookingStatus</strong></p>";
            echo "<p>⏰ Ticket Time Limit: <strong>$ticketTimeLimit</strong></p>";
            echo "<p>🗓️ Booking Date: <strong>$bookingDate</strong></p>";
            echo "<p>🔄 Void Window: <strong>$voidWindow</strong></p>";
            echo "<p>Found " . count($passengerInfos) . " passengers in API response</p>";
            
            if (!empty($passengerInfos)) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
                echo "<tr><th>Passenger</th><th>Passport</th><th>E-Ticket</th><th>Status</th><th>Update Result</th></tr>";
                
                foreach ($passengerInfos as $index => $passengerInfo) {
                    $passenger = $passengerInfo['Passenger'];
                    $eTickets = $passengerInfo['ETickets'] ?? [];
                    
                    // Extract passenger details
                    $passportNumber = $passenger['PassportNumber'] ?? '';
                    $firstName = $passenger['PaxName']['PassengerFirstName'] ?? '';
                    $lastName = $passenger['PaxName']['PassengerLastName'] ?? '';
                    $passengerName = trim($firstName . ' ' . $lastName);
                    
                    // Extract ticket information (same logic as confirmation page)
                    $ticketNumber = '';
                    $ticketStatusType = '';
                    
                    // Debug: Log the passenger info structure
                    $objCancel->_writeLog("Passenger $passengerName ETickets structure: " . json_encode($passengerInfo['ETickets'] ?? []), 'tripDetailsUpdate.txt');
                    
                    // Check if this passenger has individual tickets
                    if (isset($passengerInfo['ETickets']) && !empty($passengerInfo['ETickets'])) {
                        // Find the ticket for this specific passenger
                        $passengerTicket = null;
                        foreach ($passengerInfo['ETickets'] as $ticket) {
                            if (isset($ticket['ETicketNumber'])) {
                                $passengerTicket = $ticket;
                                break; // Use the first valid ticket for this passenger
                            }
                        }
                        
                        if ($passengerTicket) {
                            $ticketNumber = $passengerTicket['ETicketNumber'];
                            $ticketStatusType = $passengerTicket['ETicketType'] ?? $overallTicketStatus;
                            $objCancel->_writeLog("Passenger $passengerName - Individual ticket: $ticketNumber, status: $ticketStatusType", 'tripDetailsUpdate.txt');
                        } else {
                            $ticketNumber = "";
                            $ticketStatusType = $overallTicketStatus;
                            $objCancel->_writeLog("Passenger $passengerName - No individual ticket, using overall status: $ticketStatusType", 'tripDetailsUpdate.txt');
                        }
                    } else {
                        // Alternative approach: Check if there's a separate tickets array in trip details
                        // that might be indexed by passenger order
                        if (isset($travelItinerary['ETickets']) && is_array($travelItinerary['ETickets']) && isset($travelItinerary['ETickets'][$index])) {
                            $ticketData = $travelItinerary['ETickets'][$index];
                            if (isset($ticketData['ETicketNumber'])) {
                                $ticketNumber = $ticketData['ETicketNumber'];
                                $ticketStatusType = $ticketData['ETicketType'] ?? $overallTicketStatus;
                                $objCancel->_writeLog("Passenger $passengerName - Ticket from trip details index $index: $ticketNumber, status: $ticketStatusType", 'tripDetailsUpdate.txt');
                            } else {
                                $ticketNumber = "";
                                $ticketStatusType = $overallTicketStatus;
                                $objCancel->_writeLog("Passenger $passengerName - No ticket at index $index, using overall status: $ticketStatusType", 'tripDetailsUpdate.txt');
                            }
                        } elseif (!empty($overallTicketStatus)) {
                            $ticketStatusType = $overallTicketStatus;
                            $ticketNumber = "";
                            $objCancel->_writeLog("Passenger $passengerName - Using overall status: $ticketStatusType", 'tripDetailsUpdate.txt');
                        } else {
                            $ticketNumber = "";
                            $ticketStatusType = "";
                            $objCancel->_writeLog("Passenger $passengerName - No ticket info available", 'tripDetailsUpdate.txt');
                        }
                    }
                    
                    echo "<tr>";
                    echo "<td>$passengerName</td>";
                    echo "<td>$passportNumber</td>";
                    echo "<td>$ticketNumber</td>";
                    echo "<td>$ticketStatusType</td>";
                    
                    // Update database for this passenger (same logic as confirmation page)
                    try {
                        // Use the same update approach as confirmation page - direct update by passport number
                        $updateStmt = $conn->prepare(
                            'UPDATE travellers_details 
                             SET ticket_status = :ticketStatus, 
                                 e_ticket_number = :ticketNumber,
                                 last_api_update = :last_api_update,
                                 mystifly_response_json = :mystifly_response_json
                             WHERE flight_booking_id = :bookingId AND passport_number = :PassportNumber'
                        );
                        
                        $updateResult = $updateStmt->execute([
                            'ticketStatus' => $ticketStatusType,
                            'ticketNumber' => $ticketNumber,
                            'last_api_update' => date('Y-m-d H:i:s'),
                            'mystifly_response_json' => json_encode($passengerInfo),
                            'bookingId' => $bookingId,
                            'PassportNumber' => $passportNumber
                        ]);
                        
                        if ($updateResult && $updateStmt->rowCount() > 0) {
                            echo "<td style='color: green;'>✅ Updated</td>";
                            $updatedPassengers++;
                            $objCancel->_writeLog("Updated passenger $passengerName (Passport: $passportNumber) with ticket $ticketNumber, status $ticketStatusType", 'tripDetailsUpdate.txt');
                        } else {
                            echo "<td style='color: orange;'>⚠️ Not Found in DB</td>";
                            $objCancel->_writeLog("Passenger not found in DB: $passengerName (Passport: $passportNumber)", 'tripDetailsUpdate.txt');
                        }
                        
                    } catch (Exception $e) {
                        echo "<td style='color: red;'>❌ Error: " . $e->getMessage() . "</td>";
                        $objCancel->_writeLog("Error updating passenger $passengerName: " . $e->getMessage(), 'tripDetailsUpdate.txt');
                    }
                    
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            // Update booking-level details (same as confirmation page)
            try {
                $bookingUpdateStmt = $conn->prepare(
                    "UPDATE temp_booking 
                     SET booking_status = :bookingStatus,
                         ticket_status = :ticketStatus,
                         ticket_time_limit = :ticketTimeLimit,
                         booking_date = :bookingDate,
                         void_window = :voidWindow
                     WHERE id = :booking_id"
                );
                $bookingUpdateStmt->execute([
                    'bookingStatus' => $bookingStatus,
                    'ticketStatus' => $overallTicketStatus,
                    'ticketTimeLimit' => $ticketTimeLimit,
                    'bookingDate' => $bookingDate,
                    'voidWindow' => $voidWindow,
                    'booking_id' => $bookingId
                ]);
                
                echo "<p>✅ Updated booking details:</p>";
                echo "<ul>";
                echo "<li>Booking Status: <strong>$bookingStatus</strong></li>";
                echo "<li>Ticket Status: <strong>$overallTicketStatus</strong></li>";
                echo "<li>Ticket Time Limit: <strong>$ticketTimeLimit</strong></li>";
                echo "<li>Booking Date: <strong>$bookingDate</strong></li>";
                echo "<li>Void Window: <strong>$voidWindow</strong></li>";
                echo "</ul>";
                
            } catch (Exception $e) {
                $objCancel->_writeLog("Error updating booking details: " . $e->getMessage(), 'tripDetailsUpdate.txt');
            }
            
            $successCount++;
            
        } else {
            // Handle API errors
            $errorMessage = $responseData['Message'] ?? 'Unknown API error';
            echo "<p style='color: red;'>❌ API Error: $errorMessage</p>";
            $objCancel->_writeLog("API Error for $mfRef: $errorMessage", 'tripDetailsUpdate.txt');
            $errorCount++;
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
        $objCancel->_writeLog("Exception processing $mfRef: " . $e->getMessage(), 'tripDetailsUpdate.txt');
        $errorCount++;
    }
    
    echo "</div>";
    
    // Small delay between API calls to avoid rate limiting
    if (!MOCK_MODE) {
        sleep(2);
    }
}

// Summary
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Cron Job Summary</h3>";
echo "<ul>";
echo "<li><strong>Processed Bookings:</strong> " . count($bookings) . "</li>";
echo "<li><strong>Successful Updates:</strong> $successCount</li>";
echo "<li><strong>Errors:</strong> $errorCount</li>";
echo "<li><strong>Updated Passengers:</strong> $updatedPassengers</li>";
echo "<li><strong>Mode:</strong> " . (MOCK_MODE ? 'MOCK' : 'LIVE') . "</li>";
echo "</ul>";
echo "</div>";

// Log completion
$objCancel->_writeLog("=== TripDetails Update Cron Completed at " . date('Y-m-d H:i:s') . " ===", 'tripDetailsUpdate.txt');
$objCancel->_writeLog("Summary: $successCount successful, $errorCount errors, $updatedPassengers passengers updated", 'tripDetailsUpdate.txt');

// Optional: Clean up old JSON data (keep last 30 days)
try {
    $cleanupStmt = $conn->prepare(
        "UPDATE travellers_details 
         SET mystifly_response_json = NULL 
         WHERE last_api_update < DATE_SUB(NOW(), INTERVAL 30 DAY) 
         AND mystifly_response_json IS NOT NULL"
    );
    $cleanupStmt->execute();
    $cleanedRows = $cleanupStmt->rowCount();
    
    if ($cleanedRows > 0) {
        $objCancel->_writeLog("Cleaned up $cleanedRows old JSON records", 'tripDetailsUpdate.txt');
    }
    
} catch (Exception $e) {
    $objCancel->_writeLog("Cleanup error: " . $e->getMessage(), 'tripDetailsUpdate.txt');
}

echo "<h3>✅ Cron Job Completed</h3>";
echo "<p>Check logs at: <code>uploads/logFiles/tripDetailsUpdate.txt</code></p>";

?>
