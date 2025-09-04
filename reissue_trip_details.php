<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

include_once('includes/dbConnect.php');
include_once('includes/common_const.php');
include_once('includes/class.Booking.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');

$bookingId = isset($_POST['bookingId']) ? intval($_POST['bookingId']) : 0;
$mfreNum = isset($_POST['mfreNum']) ? trim($_POST['mfreNum']) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($bookingId) || empty($mfreNum)) {
	echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
	exit;
}

try {
	// Check if we should use mock responses
	if (MOCK_MODE) {
		// Use mock response for development
		$mockResponse = MockMystifly::getTripDetailsResponse($mfreNum);
		$response = json_encode($mockResponse);
		$httpCode = 200;
		
		// Log mock usage
		error_log('MOCK MODE: Using mock TripDetails response');
	} else {
		// Call real API
		$apiEndpoint = rtrim(APIENDPOINT, '/').'/TripDetails/'.urlencode($mfreNum);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer ' . BEARER
		));

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);
	}

	if ($response === false) {
		echo json_encode(['success' => false, 'message' => 'TripDetails request failed: '.$curlError, 'httpCode' => $httpCode]);
		exit;
	}

	$responseData = json_decode($response, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		echo json_encode(['success' => false, 'message' => 'Invalid TripDetails JSON']);
		exit;
	}

	$booking = new Booking($conn);

	if (empty($responseData['Success']) || empty($responseData['Data']['TripDetailsResult']['TravelItinerary'])) {
		echo json_encode(['success' => false, 'message' => $responseData['Message'] ?? 'TripDetails not ready']);
		exit;
	}

	$travelItinerary = $responseData['Data']['TripDetailsResult']['TravelItinerary'];
	$passengerInfos = isset($travelItinerary['PassengerInfos']) ? $travelItinerary['PassengerInfos'] : [];
	$ticketStatus = isset($travelItinerary['TicketStatus']) ? $travelItinerary['TicketStatus'] : '';

	$updated = [];
	if (!empty($passengerInfos)) {
		$updateStmt = $conn->prepare('UPDATE travellers_details SET ticket_status = :ticketStatus, e_ticket_number = :ticketNumber, reissue_status = :reissueStatus WHERE flight_booking_id = :bookingId AND passport_number = :passportNumber');
		foreach ($passengerInfos as $passengerInfo) {
			$passportNumber = $passengerInfo['Passenger']['PassportNumber'] ?? '';
			$eTickets = $passengerInfo['ETickets'] ?? [];
			$ticketNumber = '';
			if (!empty($eTickets)) {
				// Prefer ticket with type Reissued; fall back to first
				foreach ($eTickets as $et) {
					if (isset($et['ETicketType']) && strtolower($et['ETicketType']) === 'reissued' && !empty($et['ETicketNumber'])) {
						$ticketNumber = $et['ETicketNumber'];
						break;
					}
				}
				if (empty($ticketNumber) && !empty($eTickets[0]['ETicketNumber'])) {
					$ticketNumber = $eTickets[0]['ETicketNumber'];
				}
			}

			$updateStmt->bindValue(':ticketStatus', $ticketStatus ?: 'Ticketed');
			$updateStmt->bindValue(':ticketNumber', $ticketNumber);
			$updateStmt->bindValue(':reissueStatus', 'Completed');
			$updateStmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
			$updateStmt->bindValue(':passportNumber', $passportNumber);
			$updateStmt->execute();

			$updated[] = [
				'passportNumber' => $passportNumber,
				'ticketNumber' => $ticketNumber
			];
		}
	}

	echo json_encode([
		'success' => true,
		'updated' => $updated,
		'ticketStatus' => $ticketStatus
	]);
	exit;

} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage()]);
	exit;
}

?>


