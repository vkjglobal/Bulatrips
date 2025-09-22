<?php
// Reissue Email Handler - Handle accept/decline from email links
include_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/mock_mystifly.php');
include_once('mail_send.php');

$objCancel = new Cancel();

// Get parameters from URL
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$ptrId = isset($_GET['ptr_id']) ? trim($_GET['ptr_id']) : '';
$option = isset($_GET['option']) ? intval($_GET['option']) : 0;
$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$paxCsv = isset($_GET['pax']) ? trim($_GET['pax']) : '';
$selectedPaxIds = [];
if ($paxCsv !== '') {
    foreach (explode(',', $paxCsv) as $pid) {
        $pid = intval($pid);
        if ($pid > 0) { $selectedPaxIds[] = $pid; }
    }
}
// Fallback: if no pax ids provided in link, derive from cancel_booking for this PTR
if (empty($selectedPaxIds) && $bookingId > 0 && !empty($ptrId)) {
    try {
        $fallbackPtr = is_numeric($ptrId) ? (string)intval($ptrId) : (preg_match('/(\d+)/', $ptrId, $mx) ? (string)intval($mx[1]) : (string)$ptrId);
        $rows = $objCancel->getLisQuery("SELECT traveller_id FROM cancel_booking WHERE booking_id = ".intval($bookingId)." AND ptr_id = '".addslashes($fallbackPtr)."' AND message = 'ReissueQuote request submitted' AND traveller_id > 0");
        if (!empty($rows)) {
            foreach ($rows as $r) { $selectedPaxIds[] = (int)$r['traveller_id']; }
            $selectedPaxIds = array_values(array_unique(array_filter($selectedPaxIds)));
        }
    } catch (Exception $e) {
        // ignore, keep empty to avoid updating all by mistake
    }
}

// Validate token (simple security)
$expectedToken = md5($ptrId . $bookingId . 'reissue_secret_key');
if ($token !== $expectedToken) {
    die('<div style="text-align:center;padding:50px;"><h3>Invalid or expired link</h3><p>Please contact support if you need assistance.</p></div>');
}

if ($action === 'accept' && !empty($ptrId) && $option > 0) {
    // Accept reissue option from email
    try {
        // Call Accept ReissueQuote API
        $requestData = array(
            'ptrType' => 'ReIssueQuote',
            'mFRef' => '', // Will be fetched from DB
            'PTRId' => is_numeric($ptrId) ? intval($ptrId) : (preg_match('/(\d+)/', $ptrId, $m) ? intval($m[1]) : 0),
            'PreferenceOption' => $option,
            'AcceptQuote' => 'yes'
        );

        // Get MF reference from booking
        $bookingDetails = $objCancel->get_booking_details($bookingId);
        if ($bookingDetails) {
            $requestData['mFRef'] = $bookingDetails['mf_reference'];
        }

        if (MOCK_MODE) {
            $mockResponse = MockMystifly::getAcceptReissueQuoteResponse($ptrId);
            $responseData = $mockResponse;
            // Capture selected itinerary using GetExchangeQuote mock to persist dates/class
            $acceptedItineraryJson = '';
            try {
                $quote = MockMystifly::getGetExchangeQuoteResponse($ptrId);
                if (!empty($quote['Data']['RequestedPreferences'])) {
                    foreach ($quote['Data']['RequestedPreferences'] as $pref) {
                        if (intval($pref['Option'] ?? 0) === intval($option)) {
                            $seg = $pref['QuotedSegments'][0] ?? [];
                            if (!empty($seg)) {
                                $cabinCode = strtoupper($seg['CabinClass'] ?? 'Y');
                                $map = ['Y' => 'Economy', 'W' => 'PremiumEconomy', 'C' => 'Business', 'J' => 'Business', 'F' => 'First'];
                                $cabinName = $map[$cabinCode] ?? 'Economy';
                                $itinerary = [
                                    'outbound' => [
                                        'origin' => $seg['Origin'] ?? '',
                                        'destination' => $seg['Destination'] ?? '',
                                        'dep_date' => isset($seg['DepartureDatetime']) ? date('Y-m-d H:i:s', strtotime($seg['DepartureDatetime'])) : '',
                                        'arrival_date' => isset($seg['ArrivalDateTime']) ? date('Y-m-d H:i:s', strtotime($seg['ArrivalDateTime'])) : '',
                                        'airline_code' => $seg['AirlineCode'] ?? '',
                                        'flight_no' => strval($seg['FlightNumber'] ?? ''),
                                        'cabin_preference' => $cabinName
                                    ]
                                ];
                                $acceptedItineraryJson = json_encode($itinerary);
                            }
                            break;
                        }
                    }
                }
            } catch (Exception $e) { /* ignore */ }
        } else {
            $result = $objCancel->callApi('PostTicketingRequest', $requestData);
            $responseData = json_decode($result['responseData'], true);
            $acceptedItineraryJson = '';
        }

        if (isset($responseData['Success']) && $responseData['Success']) {
            // Persist acceptance markers so cron can take over (same as UI accept flow)
            try {
                include_once('includes/dbConnect.php');
                // Get booking owner and MFRef
                $bk = $objCancel->get_booking_details($bookingId);
                $mfRefPersist = $bk && isset($bk['mf_reference']) ? $bk['mf_reference'] : '';
                $userIdPersist = $bk && isset($bk['user_id']) ? intval($bk['user_id']) : 0;

                // Normalize PTR id as numeric string if possible
                $ptrString = is_numeric($ptrId) ? (string)intval($ptrId) : (preg_match('/(\d+)/', $ptrId, $m) ? (string)intval($m[1]) : (string)$ptrId);

                // Find travellers for this booking to mark InProcess (only ticketed rows)
                $travQuery = "SELECT id, e_ticket_number FROM travellers_details WHERE flight_booking_id = ".intval($bookingId)." AND e_ticket_number IS NOT NULL AND e_ticket_number <> ''";
                if (!empty($selectedPaxIds)) {
                    $travQuery .= " AND id IN (".implode(',', array_map('intval', $selectedPaxIds)).")";
                }
                $travRows = $objCancel->getLisQuery($travQuery);
                if (!empty($travRows)) {
                    foreach ($travRows as $tr) {
                        $travId = intval($tr['id']);
                        $ticketNum = $tr['e_ticket_number'];
                        // Insert into cancel_booking for monitoring
                        $objCancel->insCncelSts(
                            $bookingId, $userIdPersist, 'post', '', $mfRefPersist,
                            '', 200, $ptrString, 'Reissue', 0,
                            'InProcess', '', $ticketNum, 0, 0,
                            0, 0, 'USD', 0, ($acceptedItineraryJson ?? '') ?: 'ReissueQuote accepted by customer (email)', $travId
                        );
                        // Update travellers_details markers
                        $objCancel->update('travellers_details', array(
                            'reissue_status' => 'InProcess',
                            'reissue_ptr_id' => $ptrString,
                            'cancel_type' => 'reissue'
                        ), "id = ".$travId);
                    }
                }
            } catch (Exception $e) {
                // Best-effort; show page regardless
                $objCancel->_writeLog('Email accept persist failed: '.$e->getMessage(), 'reissueQuote.txt');
            }

            echo '<div style="text-align:center;padding:50px;font-family:Arial,sans-serif;">';
            echo '<div style="max-width:500px;margin:0 auto;background:#f8f9fa;padding:30px;border-radius:10px;">';
            echo '<i class="fas fa-check-circle" style="font-size:48px;color:#28a745;margin-bottom:20px;"></i>';
            echo '<h2 style="color:#28a745;margin-bottom:15px;">Reissue Accepted!</h2>';
            echo '<p style="font-size:16px;margin-bottom:10px;">Your reissue request has been accepted and is being processed.</p>';
            echo '<p style="color:#666;"><strong>PTR ID:</strong> ' . htmlspecialchars($responseData['Data']['PTRId']) . '</p>';
            echo '<p style="color:#666;">You will receive a confirmation email once the new tickets are issued.</p>';
            echo '<a href="user-dashboard.php" style="display:inline-block;background:#0029ff;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin-top:20px;">Go to Dashboard</a>';
            echo '</div></div>';
        } else {
            echo '<div style="text-align:center;padding:50px;"><h3>Error processing request</h3><p>Please contact support.</p></div>';
        }

    } catch (Exception $e) {
        echo '<div style="text-align:center;padding:50px;"><h3>System error</h3><p>Please try again later.</p></div>';
    }

} elseif ($action === 'decline') {
    // Decline reissue option
    echo '<div style="text-align:center;padding:50px;font-family:Arial,sans-serif;">';
    echo '<div style="max-width:500px;margin:0 auto;background:#f8f9fa;padding:30px;border-radius:10px;">';
    echo '<i class="fas fa-times-circle" style="font-size:48px;color:#dc3545;margin-bottom:20px;"></i>';
    echo '<h2 style="color:#dc3545;margin-bottom:15px;">Reissue Declined</h2>';
    echo '<p style="font-size:16px;">Your reissue request has been declined. No changes will be made to your booking.</p>';
    echo '<a href="user-dashboard.php" style="display:inline-block;background:#0029ff;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin-top:20px;">Go to Dashboard</a>';
    echo '</div></div>';

} else {
    echo '<div style="text-align:center;padding:50px;"><h3>Invalid request</h3><p>Please check your link and try again.</p></div>';
}
?>
