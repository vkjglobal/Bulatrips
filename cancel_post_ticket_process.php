<?php
session_start();
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');

// Set JSON header for AJAX response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$objCancel = new Cancel();

// Get JSON input for AJAX request
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Also support GET parameters for backward compatibility
$bookingId = isset($input['booking_id']) ? $input['booking_id'] : (isset($_GET['booking_id']) ? $_GET['booking_id'] : '');
$passengers = isset($input['passengers']) ? $input['passengers'] : (isset($_GET['passengers']) ? explode(',', $_GET['passengers']) : []);
$type = isset($input['type']) ? $input['type'] : (isset($_GET['type']) ? $_GET['type'] : '');

// Validate inputs
if (empty($bookingId) || empty($passengers) || $type !== 'precancel') {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get booking data
    if (is_numeric($bookingId)) {
        $stmt = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid AND user_id = :userid');
    } else {
        $stmt = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid AND user_id = :userid');
    }
    
    $stmt->execute([
        'bookingid' => $bookingId,
        'userid' => $userId
    ]);
    
    $bookingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$bookingData) {
        throw new Exception("Booking not found or you don't have permission to cancel this booking.");
    }
    
    // Get passenger details
    $bookCanusers = $objCancel->BookCancelUsers($bookingData['id'], $userId);
    
    if (empty($bookCanusers)) {
        throw new Exception("No passengers found for this booking.");
    }
    
    // Filter selected passengers and validate they are not ticketed
    $selectedPassengers = [];
    foreach ($bookCanusers as $passenger) {
        if (in_array($passenger['id'], $passengers)) {
            // Check if passenger is not ticketed (pre-cancellation only for non-ticketed)
            if (!empty($passenger['e_ticket_number'])) {
                throw new Exception("Cannot pre-cancel ticketed passengers. Please use void or refund options.");
            }
            $selectedPassengers[] = $passenger;
        }
    }
    
    if (empty($selectedPassengers)) {
        throw new Exception("No valid passengers selected for pre-cancellation.");
    }
    
    // Prepare passengers array for MystiFly API
    $apiPassengers = [];
    foreach ($selectedPassengers as $passenger) {
        $apiPassengers[] = [
            'firstName' => $passenger['firstName'],
            'lastName' => $passenger['lastName'],
            'title' => $passenger['title'],
            'eTicket' => $passenger['ticketNumber'] ?? '',
            'passengerType' => $passenger['passengerType']
        ];
    }
    
    // Check if child passengers are allowed
    $allow_child = false;
    foreach ($selectedPassengers as $passenger) {
        if (strtolower($passenger['passengerType']) === 'chd') {
            $allow_child = true;
            break;
        }
    }
    
    // Pre-cancellation logic (no MystiFly API call needed for non-ticketed passengers)
    $objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','precancel.txt');
    $objCancel->_writeLog('Pre-cancel processing for MF: '.$bookingData['mf_reference'],'precancel.txt');
    $objCancel->_writeLog('Non-ticketed passengers - local database operation only','precancel.txt');
        
    $cancelledCount = 0;
    $errors = [];
    
    // Update passenger status in database
    foreach ($selectedPassengers as $passenger) {
        try {
            // Update passenger status to cancelled
            $updateStmt = $conn->prepare("
                UPDATE travellers_details 
                SET ticket_status = 'cancelled'
                WHERE id = :passenger_id AND flight_booking_id = :booking_id
            ");
            
            $updateResult = $updateStmt->execute([
                'passenger_id' => $passenger['id'],
                'booking_id' => $bookingData['id']
            ]);
            
            if ($updateResult) {
                // Insert cancellation record for tracking (no PTR ID for pre-cancellation)
                $objCancel->insCncelSts(
                    $bookingData['id'],
                    $userId,
                    'precancel',
                    '',
                    $bookingData['mf_reference'],
                    '',
                    200,
                    '',
                    'PreCancel',
                    0,
                    'Completed',
                    '',
                    '',
                    0,
                    0,
                    0,
                    0,
                    'USD',
                    1,
                    'Pre-cancellation completed successfully'
                );
                
                $cancelledCount++;
                
                $objCancel->_writeLog('Pre-cancellation successful for passenger: ' . $passenger['first_name'] . ' ' . $passenger['last_name'], 'precancel.txt');
            } else {
                $errors[] = "Failed to update passenger status: " . $passenger['first_name'] . ' ' . $passenger['last_name'];
            }
            
        } catch (Exception $e) {
            $errors[] = "Error processing passenger " . $passenger['first_name'] . ' ' . $passenger['last_name'] . ": " . $e->getMessage();
        }
    }
    
    $responseMessage = "Pre-cancellation completed successfully for $cancelledCount passenger(s).";
    
    if (!empty($errors)) {
        $responseMessage .= " Errors: " . implode(", ", $errors);
    }
    
    // Check if all passengers in booking are now cancelled
    $remainingStmt = $conn->prepare("
        SELECT COUNT(*) as remaining 
        FROM travellers_details 
        WHERE flight_booking_id = :booking_id AND (ticket_status != 'cancelled' OR ticket_status IS NULL)
    ");
    $remainingStmt->execute(['booking_id' => $bookingData['id']]);
    $remainingCount = $remainingStmt->fetch(PDO::FETCH_ASSOC)['remaining'];
    
    // If no passengers remain, mark entire booking as cancelled
    if ($remainingCount == 0) {
        $bookingUpdateStmt = $conn->prepare("
            UPDATE temp_booking 
            SET booking_status = 'cancelled' 
            WHERE id = :booking_id
        ");
        $bookingUpdateStmt->execute(['booking_id' => $bookingData['id']]);
    }
    
    if ($remainingCount == 0) {
        $responseMessage .= " Entire booking has been cancelled as no passengers remain.";
    }
    
    // Return JSON response for AJAX
    echo json_encode([
        'success' => true,
        'message' => $responseMessage,
        'data' => [
            'cancelledCount' => $cancelledCount,
            'remainingPassengers' => $remainingCount,
            'type' => 'precancel'
        ]
    ]);
    
} catch (Exception $e) {
    // Log error
    $objCancel->_writeLog('Pre-cancellation error: ' . $e->getMessage(), 'precancel.txt');
    $objCancel->_writeLog('Booking ID: ' . $bookingId, 'precancel.txt');
    $objCancel->_writeLog('User ID: ' . $userId, 'precancel.txt');
    
    // Return JSON error response for AJAX
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true
    ]);
}
?> 