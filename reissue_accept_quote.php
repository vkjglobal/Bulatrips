<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');

$objCancel = new Cancel();
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$input = [];
if (!empty($rawInput)) {
	$decoded = json_decode($rawInput, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		$input = $decoded;
	}
}
if (empty($input)) {
	$input = $_POST; // fallback
}

$mfreNum = isset($input['mfreNum']) ? trim($input['mfreNum']) : '';
$ptrId = isset($input['ptrId']) ? trim($input['ptrId']) : '';
$preferenceOption = isset($input['preferenceOption']) ? intval($input['preferenceOption']) : 1;
$acceptQuote = isset($input['acceptQuote']) ? strtolower(trim($input['acceptQuote'])) : 'yes';
$bookingId = isset($input['bookingId']) ? intval($input['bookingId']) : 0;
$userId = isset($input['userId']) ? intval($input['userId']) : 0;
$passengerIds = isset($input['passengerIds']) && is_array($input['passengerIds']) ? $input['passengerIds'] : [];

// Extract numeric PTR for API call
$numericPtrId = 0;
if (!empty($ptrId)) {
    if (is_numeric($ptrId)) {
        $numericPtrId = intval($ptrId);
    } elseif (preg_match('/(\d+)/', $ptrId, $m)) {
        $numericPtrId = intval($m[1]);
    }
}

if (empty($mfreNum) || empty($ptrId)) {
	echo json_encode(['success' => false, 'message' => 'Missing required parameters: mfreNum=' . $mfreNum . ', ptrId=' . $ptrId]);
	exit;
}

if (!in_array($acceptQuote, ['yes', 'no'])) {
	echo json_encode(['success' => false, 'message' => 'Invalid acceptQuote value']);
	exit;
}

$acceptedItineraryJson = '';

$requestData = array(
	'ptrType' => 'ReIssueQuote',
	'mFRef' => $mfreNum,
	'PTRId' => $numericPtrId,
	'PreferenceOption' => $preferenceOption,
	'AcceptQuote' => $acceptQuote
);

$objCancel->_writeLog('-------------'.date('l jS \of F Y h:i:s A').'-------------','reissueQuote.txt');
$objCancel->_writeLog('Accept ReissueQuote Request: '.json_encode($requestData),'reissueQuote.txt');

// Fetch the quoted segments for the selected option
if (MOCK_MODE) {
	try {
		$quote = MockMystifly::getGetExchangeQuoteResponse($numericPtrId ?: $ptrId);
		if (!empty($quote['Data']['RequestedPreferences'])) {
			$opts = $quote['Data']['RequestedPreferences'];
			foreach ($opts as $pref) {
				if (intval($pref['Option'] ?? 0) === intval($preferenceOption)) {
					$legs = $pref['QuotedSegments'] ?? [];
					$itinerary = ['outbound_segments' => [], 'return_segments' => []];
					foreach ($legs as $lg) {
						$cabinCode = strtoupper($lg['CabinClass'] ?? 'Y');
						$map = ['Y' => 'Economy', 'W' => 'PremiumEconomy', 'S' => 'PremiumEconomy', 'C' => 'Business', 'J' => 'Business', 'F' => 'First'];
						$cabinName = $map[$cabinCode] ?? 'Economy';
						$one = [
							'origin' => $lg['Origin'] ?? '',
							'destination' => $lg['Destination'] ?? '',
							'dep_date' => isset($lg['DepartureDatetime']) ? date('Y-m-d H:i:s', strtotime($lg['DepartureDatetime'])) : '',
							'arrival_date' => isset($lg['ArrivalDateTime']) ? date('Y-m-d H:i:s', strtotime($lg['ArrivalDateTime'])) : '',
							'airline_code' => $lg['AirlineCode'] ?? '',
							'flight_no' => strval($lg['FlightNumber'] ?? ''),
							'cabin_preference' => $cabinName,
							'cabin_code' => $cabinCode,
							'booking_class' => $lg['BookingClass'] ?? '',
							'duration' => $lg['Duration'] ?? '',
							'stops' => isset($lg['Stops']) ? intval($lg['Stops']) : 0
						];
						if (!empty($lg['isReturn'])) { $itinerary['return_segments'][] = $one; } else { $itinerary['outbound_segments'][] = $one; }
					}
					if (!empty($itinerary['outbound_segments']) || !empty($itinerary['return_segments'])) { $acceptedItineraryJson = json_encode($itinerary); }
					break;
				}
			}
		}
	} catch (Exception $e) {
		$objCancel->_writeLog('Itinerary capture failed (MOCK): '.$e->getMessage(), 'reissueQuote.txt');
	}
} else {
	try {
		// Live: call GetExchangeQuote to read the quoted segments
		$geqReq = ['PTRId' => $numericPtrId];
		$geq = $objCancel->callApi('GetExchangeQuote', $geqReq);
		$geqData = json_decode($geq['responseData'] ?? '{}', true);
		if (!empty($geqData['Data']['RequestedPreferences'])) {
			foreach ($geqData['Data']['RequestedPreferences'] as $pref) {
				if (intval($pref['Option'] ?? 0) === intval($preferenceOption)) {
					$legs = $pref['QuotedSegments'] ?? [];
					$itinerary = ['outbound_segments' => [], 'return_segments' => []];
					foreach ($legs as $lg) {
						$cabinCode = strtoupper($lg['CabinClass'] ?? 'Y');
						$map = ['Y' => 'Economy', 'W' => 'PremiumEconomy', 'S' => 'PremiumEconomy', 'C' => 'Business', 'J' => 'Business', 'F' => 'First'];
						$cabinName = $map[$cabinCode] ?? 'Economy';
						$one = [
							'origin' => $lg['Origin'] ?? '',
							'destination' => $lg['Destination'] ?? '',
							'dep_date' => isset($lg['DepartureDatetime']) ? date('Y-m-d H:i:s', strtotime($lg['DepartureDatetime'])) : '',
							'arrival_date' => isset($lg['ArrivalDateTime']) ? date('Y-m-d H:i:s', strtotime($lg['ArrivalDateTime'])) : '',
							'airline_code' => $lg['AirlineCode'] ?? '',
							'flight_no' => strval($lg['FlightNumber'] ?? ''),
							'cabin_preference' => $cabinName,
							'cabin_code' => $cabinCode,
							'booking_class' => $lg['BookingClass'] ?? '',
							'duration' => $lg['Duration'] ?? '',
							'stops' => isset($lg['Stops']) ? intval($lg['Stops']) : 0
						];
						if (!empty($lg['isReturn'])) { $itinerary['return_segments'][] = $one; } else { $itinerary['outbound_segments'][] = $one; }
					}
					if (!empty($itinerary['outbound_segments']) || !empty($itinerary['return_segments'])) { $acceptedItineraryJson = json_encode($itinerary); }
					break;
				}
			}
		}
	} catch (Exception $e) {
		$objCancel->_writeLog('Itinerary capture failed (LIVE): '.$e->getMessage(), 'reissueQuote.txt');
	}
}

// Check if we should use mock responses
if (MOCK_MODE) {
    // Use mock response for development
    $mockResponse = MockMystifly::getAcceptReissueQuoteResponse($ptrId);
    $response = json_encode($mockResponse);
    $httpCode = 200;
    
    // Log mock usage
    $objCancel->_writeLog('MOCK MODE: Using mock Accept ReissueQuote response', 'reissueQuote.txt');
} else {
    // Call real API
    $endpoint = 'PostTicketingRequest';
    $result = $objCancel->callApi($endpoint, $requestData);
    $httpCode = $result['httpCode'];
    $response = $result['responseData'];
}

if (!$response) {
	echo json_encode(['success' => false, 'message' => 'No response from API', 'httpCode' => $httpCode]);
	exit;
}

$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
	echo json_encode(['success' => false, 'message' => 'Invalid response JSON', 'httpCode' => $httpCode]);
	exit;
}

$objCancel->_writeLog('Accept ReissueQuote Response: '.json_encode($responseData),'reissueQuote.txt');

if (!isset($responseData['Success']) || !$responseData['Success']) {
	$message = isset($responseData['Message']) ? $responseData['Message'] : 'Unknown error';
	echo json_encode(['success' => false, 'message' => $message, 'httpCode' => $httpCode, 'raw' => $responseData]);
	exit;
}

$data = isset($responseData['Data']) ? $responseData['Data'] : [];

// Persist PTR acceptance similar to refund/void
try {
    if ($bookingId > 0 && $userId > 0) {
        $ptrString = isset($data['PTRId']) ? (string)$data['PTRId'] : (string)$ptrId;
        $sla = isset($data['SLAInMinutes']) ? intval($data['SLAInMinutes']) : 0;
        if (!empty($passengerIds)) {
            // Insert per passenger rows; also set travellers_details markers
            foreach ($passengerIds as $pid) {
                $pid = intval($pid);
                // Lookup e-ticket and traveller id
                $row = $objCancel->getLisQuery("SELECT id, e_ticket_number FROM travellers_details WHERE flight_booking_id = ".intval($bookingId)." AND id = ".$pid." LIMIT 1");
                $travId = 0; $ticketNum = '';
                if (!empty($row)) { $travId = intval($row[0]['id']); $ticketNum = $row[0]['e_ticket_number']; }
                // Insert into cancel_booking
                $objCancel->insCncelSts(
                    $bookingId, $userId, 'post', '', $mfreNum,
                    '', 200, $ptrString, 'Reissue', $sla,
                    'InProcess', '', $ticketNum, 0, 0,
                    0, 0, 'USD', 0, $acceptedItineraryJson ?: 'ReissueQuote accepted by user', $travId
                );
                // Update travellers_details
                if ($travId > 0) {
                    $objCancel->update('travellers_details', array(
                        'reissue_status' => 'InProcess',
                        'reissue_ptr_id' => $ptrString,
                        'cancel_type' => 'reissue'
                    ), "id = ".$travId);
                }
            }
        } else {
            // Booking-level fallback
            $objCancel->insCncelSts(
                $bookingId, $userId, 'post', '', $mfreNum,
                '', 200, $ptrString, 'Reissue', $sla,
                'InProcess', '', '', 0, 0,
                0, 0, 'USD', 0, $acceptedItineraryJson ?: 'ReissueQuote accepted by user'
            );
        }
    }
} catch (Exception $e) {
    $objCancel->_writeLog('Reissue accept persist error: '.$e->getMessage(), 'reissueQuote.txt');
}

echo json_encode([
	'success' => true,
	'ptrId' => isset($data['PTRId']) ? $data['PTRId'] : $ptrId,
	'ptrType' => isset($data['PTRType']) ? $data['PTRType'] : 'ReIssue',
	'status' => isset($data['PTRStatus']) ? $data['PTRStatus'] : 'InProcess',
	'slaMinutes' => isset($data['SLAInMinutes']) ? $data['SLAInMinutes'] : 60,
	'message' => isset($data['Message']) ? $data['Message'] : ($responseData['Message'] ?? 'Success')
]);

exit;
?>


