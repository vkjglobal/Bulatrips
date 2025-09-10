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
        } else {
            $result = $objCancel->callApi('PostTicketingRequest', $requestData);
            $responseData = json_decode($result['responseData'], true);
        }

        if (isset($responseData['Success']) && $responseData['Success']) {
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
