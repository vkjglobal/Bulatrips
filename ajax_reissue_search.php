<?php
// Set flag to bypass filterValidation for AJAX requests
define('BYPASS_FILTER_VALIDATION', true);

session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Session expired. Please login again.</div>';
    exit;
}

// Include necessary files
require_once('includes/dbConnect.php');
require_once('includes/common_const.php');
include_once('includes/class.cancel.php');
include_once('includes/class.Booking.php');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger">Invalid request method.</div>';
    exit;
}

// Get form data
$action = isset($_POST['action']) ? $_POST['action'] : '';
$bookingId = isset($_POST['bookingId']) ? filter_var($_POST['bookingId'], FILTER_SANITIZE_NUMBER_INT) : '';
$userId = $_SESSION['user_id'];
$selectedPassengers = isset($_POST['selected_passengers']) ? json_decode($_POST['selected_passengers'], true) : [];
$departure = isset($_POST['departure']) ? $_POST['departure'] : '';
$arrival = isset($_POST['arrival']) ? $_POST['arrival'] : '';
$newDepDate = isset($_POST['new_dep_date']) ? $_POST['new_dep_date'] : '';
$newReturnDate = isset($_POST['new_return_date']) ? $_POST['new_return_date'] : '';
$cabinClass = isset($_POST['cabin_class']) ? $_POST['cabin_class'] : '';
$tripTypeSelection = isset($_POST['trip_type_selection']) ? $_POST['trip_type_selection'] : '';
$airTripType = !empty($tripTypeSelection) ? $tripTypeSelection : (isset($_POST['air_trip_type']) ? $_POST['air_trip_type'] : 'OneWay');
$mfRefNum = isset($_POST['mfreNum']) ? $_POST['mfreNum'] : '';

// Pagination parameters
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$flightsPerPage = 10;

// Debug: Log all POST data to see what's being received
$postDebug = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'all_post_data' => $_POST,
    'selected_passengers_raw' => $_POST['selected_passengers'] ?? 'NOT_SET',
    'selected_passengers_decoded' => $selectedPassengers,
    'passenger_count' => count($selectedPassengers),
    'page' => $page,
    'air_trip_type' => $airTripType
);
file_put_contents('uploads/logFiles/reissue_post_debug.txt', print_r($postDebug, true) . "\n\n", FILE_APPEND);

// Validate required fields
if (empty($bookingId) || empty($selectedPassengers) || empty($newDepDate)) {
    echo '<div class="alert alert-danger">Please select passengers and provide a new departure date.</div>';
    exit;
}

// For round-trip, also validate return date
if ($airTripType === 'Return' && empty($newReturnDate)) {
    echo '<div class="alert alert-danger">Please provide a return date for round-trip reissue.</div>';
    exit;
}

// Parse departure and arrival airport codes
$departureCode = explode("-", $departure);
$arrivalCode = explode("-", $arrival);

// Handle different airport code formats
$departureAirportCode = trim($departureCode[0]);
$arrivalAirportCode = trim($arrivalCode[0]);

// If codes are longer than 3 characters, try to extract just the airport code
if (strlen($departureAirportCode) > 3) {
    // Look for 3-letter code pattern
    if (preg_match('/\b[A-Z]{3}\b/', $departure, $matches)) {
        $departureAirportCode = $matches[0];
    }
}
if (strlen($arrivalAirportCode) > 3) {
    // Look for 3-letter code pattern
    if (preg_match('/\b[A-Z]{3}\b/', $arrival, $matches)) {
        $arrivalAirportCode = $matches[0];
    }
}

// Debug airport codes
$airportDebug = array(
    'original_departure' => $departure,
    'original_arrival' => $arrival,
    'parsed_departure' => $departureAirportCode,
    'parsed_arrival' => $arrivalAirportCode,
    'departure_length' => strlen($departureAirportCode),
    'arrival_length' => strlen($arrivalAirportCode)
);
file_put_contents('uploads/logFiles/reissue_airport_debug.txt', print_r($airportDebug, true) . "\n\n", FILE_APPEND);

// Convert cabin class to API format (unified for both outbound and return)
$cabinPreference = strtolower($cabinClass);
if ($cabinPreference === 'economy') $cabinPreference = 'Y';
elseif ($cabinPreference === 'premium') $cabinPreference = 'S';
elseif ($cabinPreference === 'business') $cabinPreference = 'C';
elseif ($cabinPreference === 'first') $cabinPreference = 'F';
else $cabinPreference = 'Y'; // Default to Economy

// For unified cabin class approach, use the same preference for return flights
$returnCabinPreference = $cabinPreference;

// Count passengers by type
$adultCount = 0;
$childCount = 0;
$infantCount = 0;

foreach ($selectedPassengers as $passenger) {
    $passengerType = strtoupper(trim($passenger['passengerType']));
    switch ($passengerType) {
        case 'ADT':
        case 'ADULT':
            $adultCount++;
            break;
        case 'CHD':
        case 'CHILD':
            $childCount++;
            break;
        case 'INF':
        case 'INFANT':
            $infantCount++;
            break;
    }
}

// Debug passenger counting
$passengerDebug = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'selected_passengers' => $selectedPassengers,
    'adult_count' => $adultCount,
    'child_count' => $childCount,
    'infant_count' => $infantCount,
    'total_count' => $adultCount + $childCount + $infantCount
);
file_put_contents('uploads/logFiles/reissue_passenger_debug.txt', print_r($passengerDebug, true) . "\n\n", FILE_APPEND);

// Prepare API request
$endpoint = 'v2/Search/Flight';
$apiEndpoint = APIENDPOINT . $endpoint;
$bearerToken = BEARER;

// Build OriginDestinationInformations array
$originDestinations = array(
    array(
        'DepartureDateTime' => $newDepDate,
        'OriginLocationCode' => trim($departureAirportCode),
        'DestinationLocationCode' => trim($arrivalAirportCode)
    )
);

// Add return leg for round-trip
if ($airTripType === 'Return' && !empty($newReturnDate)) {
    $originDestinations[] = array(
        'DepartureDateTime' => $newReturnDate,
        'OriginLocationCode' => trim($arrivalAirportCode), // Return trip goes back
        'DestinationLocationCode' => trim($departureAirportCode)
    );
}

// Debug dates and validate
$dateDebug = array(
    'new_dep_date' => $newDepDate,
    'new_return_date' => $newReturnDate,
    'air_trip_type' => $airTripType,
    'dep_date_valid' => (bool) strtotime($newDepDate),
    'return_date_valid' => (bool) strtotime($newReturnDate),
    'origin_destinations' => $originDestinations
);
file_put_contents('uploads/logFiles/reissue_date_debug.txt', print_r($dateDebug, true) . "\n\n", FILE_APPEND);

$requestData = array(
    'OriginDestinationInformations' => $originDestinations,
    'TravelPreferences' => array(
        'MaxStopsQuantity' => 'All',
        'CabinPreference' => $cabinPreference,
        'AirTripType' => $airTripType
    ),
    'PricingSourceType' => 'All',
    'PassengerTypeQuantities' => array(),
    'RequestOptions' => 'TwoHundred',
    'NearByAirports' => true,
    'Nationality' => 'string',
    'Target' => TARGET,
    'page_size' => 50, // Get more results for pagination
    'page_number' => 1
);

// Add passenger quantities
if ($adultCount > 0) {
    $requestData['PassengerTypeQuantities'][] = array(
        'Code' => 'ADT',
        'Quantity' => $adultCount
    );
}
if ($childCount > 0) {
    $requestData['PassengerTypeQuantities'][] = array(
        'Code' => 'CHD',
        'Quantity' => $childCount
    );
}
if ($infantCount > 0) {
    $requestData['PassengerTypeQuantities'][] = array(
        'Code' => 'INF',
        'Quantity' => $infantCount
    );
}

// Debug API request
$apiDebug = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoint' => $apiEndpoint,
    'request_data' => $requestData,
    'cabin_preference' => $cabinPreference,
    'air_trip_type' => $airTripType
);
file_put_contents('uploads/logFiles/reissue_api_debug.txt', print_r($apiDebug, true) . "\n\n", FILE_APPEND);

// Make API request
$headers = array(
    'Authorization: Bearer ' . $bearerToken,
    'Content-Type: application/json'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug API response
$responseDebug = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'http_code' => $httpCode,
    'response_length' => strlen($response),
    'response_excerpt' => substr($response, 0, 500) . '...'
);
file_put_contents('uploads/logFiles/reissue_response_debug.txt', print_r($responseDebug, true) . "\n\n", FILE_APPEND);

// convertMinutesToTimeFormat function is available from common_const.php

if ($response !== false && $httpCode === 200) {
    $responseData = json_decode($response, true);
    
    if (isset($responseData['Data']['PricedItineraries']) && !empty($responseData['Data']['PricedItineraries'])) {
        $pricedItineraries = $responseData['Data']['PricedItineraries'];
        $flightSegmentList = $responseData['Data']['FlightSegmentList'];
        $flightItineraryList = $responseData['Data']['FlightItineraryList'];
        
        // Get airline information from database for proper icon display
        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
        
        // Apply pagination
        $totalFlights = count($pricedItineraries);
        $totalPages = ceil($totalFlights / $flightsPerPage);
        $startIndex = ($page - 1) * $flightsPerPage;
        $currentPageFlights = array_slice($pricedItineraries, $startIndex, $flightsPerPage);
        
        // Get markup for pricing
        $stmtmarkup = $conn->prepare('SELECT * FROM markup_commission WHERE role_id = :role_id');
        $stmtmarkup->execute(array('role_id' => 1));
        $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
        $markupPercentage = isset($markup['commission_percentage']) ? $markup['commission_percentage'] : 5; // Default 5%
        
        ?>
        <style>
        /* Ensure airline icons are displayed properly */
        .airImg {
            background-image: url('css/../images/airline.png');
            background-repeat: no-repeat;
            display: inline-block;
        }
        </style>
        
        <div class="flight-results-container">
            <!-- Results Header -->
            <div class="results-header mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-plane-departure mr-2"></i>Available Flights for Reissue</h5>
                        <p class="text-muted">Found <?php echo $totalFlights; ?> flight<?php echo $totalFlights !== 1 ? 's' : ''; ?> for your reissue (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)</p>
                    </div>
                </div>
            </div>
            
            <!-- Flight Listings -->
            <div class="flight-listings">
                <?php foreach ($currentPageFlights as $key => $pricedItinerary): 
                    $fareListRef = $responseData['Data']['FlightFaresList'][$pricedItinerary['FareRef']];
                    
                    // Get airline information
                    $validatingCarrier = $pricedItinerary['ValidatingCarrier'];
                    $code = '%' . $validatingCarrier . '%';
                    $stmtairline->bindParam(':code', $code);
                    $stmtairline->execute();
                    $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                    $airlineName = $airlineLocation ? $airlineLocation['name'] : $validatingCarrier;
                    
                    // Calculate total stops for outbound journey
                    $totalOutboundStop = 0;
                    $outboundSegments = [];
                    $returnSegments = [];
                    
                    foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                        $originSegment = $flightSegmentList[$origins['SegmentRef']];
                        if ($origins['LegIndicator'] == 0) {
                            $totalOutboundStop++;
                            $outboundSegments[] = $originSegment;
                        } else {
                            $returnSegments[] = $originSegment;
                        }
                    }
                    $totalOutboundStop = max(0, $totalOutboundStop - 1); // Stops = segments - 1
                    
                    $totalReturnStop = max(0, count($returnSegments) - 1);
                    
                    // Get first and last segments for display
                    $firstOutboundSegment = $outboundSegments[0];
                    $lastOutboundSegment = end($outboundSegments);
                    
                    // Calculate pricing
                    $totalFareAPI = $fareListRef['TotalFare'];
                    $markupAmount = ($markupPercentage / 100) * $totalFareAPI;
                    $total_price = $totalFareAPI + $markupAmount;
                ?>
                
                <div class="card flight-card mb-3">
                    <div class="card-body">
                        <!-- Outbound Flight -->
                        <ul class="form-row mb-lg-2" style="justify-content: center;align-items: center;">
                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2 text-center">
                                <span class="airImg airline-<?php echo $validatingCarrier; ?>"></span>
                                <strong style="font-size: 12px; margin-top: 5px;"><?php echo $airlineName; ?></strong>
                                <small style="font-size: 10px;">Flight: <?php echo $firstOutboundSegment['OperatingFlightNumber']; ?></small>
                            </li>
                            <li data-th="Depart" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div class="">
                                    <strong style="font-size:16px;"><?php echo $firstOutboundSegment['DepartureAirportLocationCode']; ?></strong>
                                    <br>
                                    <?php
                                    $datetime = $firstOutboundSegment['DepartureDateTime'];
                                    list($date, $time) = explode("T", $datetime);
                                    echo date("d M Y", strtotime($date)); 
                                    ?><br>
                                    <?php echo $time; ?>
                                </div>
                            </li>
                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <strong>
                                        <?php
                                        if ($totalOutboundStop > 0) {
                                            echo $totalOutboundStop . " Stop" . ($totalOutboundStop > 1 ? "s" : "");
                                        } else {
                                            echo "Direct";
                                        }
                                        ?>
                                    </strong>
                                </div>
                            </li>
                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <?php
                                    $datetime = $lastOutboundSegment['ArrivalDateTime'];
                                    list($date, $time) = explode("T", $datetime);
                                    ?>
                                    <strong style="font-size:16px;"><?php echo $lastOutboundSegment['ArrivalAirportLocationCode']; ?></strong>
                                    <br>
                                    <?php
                                    echo date("d M Y", strtotime($date)); 
                                    ?><br>
                                    <?php echo $time; ?>
                                </div>
                            </li>
                            <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <strong class="fw-500">
                                        Total Duration:<br />
                                        <?php
                                        $origin_total_duration = 0;
                                        foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                            $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                            if ($origins['LegIndicator'] == 0) {
                                                $origin_total_duration += $originSegment['JourneyDuration'];
                                            }
                                        }
                                        echo convertMinutesToTimeFormat($origin_total_duration);
                                        ?>
                                    </strong>
                                </div>
                            </li>
                        </ul>
                        
                        <?php if ($airTripType === 'Return' && !empty($returnSegments)): 
                            $firstReturnSegment = $returnSegments[0];
                            $lastReturnSegment = end($returnSegments);
                        ?>
                        <!-- Return Flight -->
                        <p style="font-size: 14px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;padding: 5px 10px;background-color: #ffe4cc;">
                            <img class="flight_icon_small_return" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg" />
                            <strong>Return</strong>
                        </p>
                        
                        <ul class="form-row mb-lg-2" style="justify-content: center;align-items: center;">
                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2 text-center">
                                <span class="airImg airline-<?php echo $validatingCarrier; ?>"></span>
                                <strong style="font-size: 12px; margin-top: 5px;"><?php echo $airlineName; ?></strong>
                                <small style="font-size: 10px;">Flight: <?php echo $firstReturnSegment['OperatingFlightNumber']; ?></small>
                            </li>
                            <li data-th="Depart" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div class="">
                                    <strong style="font-size:16px;"><?php echo $firstReturnSegment['DepartureAirportLocationCode']; ?></strong>
                                    <br>
                                    <?php
                                    $datetime = $firstReturnSegment['DepartureDateTime'];
                                    list($date, $time) = explode("T", $datetime);
                                    echo date("d M Y", strtotime($date)); ?>
                                    <br>
                                    <?php echo $time; ?>
                                </div>
                            </li>
                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <strong>
                                        <?php
                                        if ($totalReturnStop > 0) {
                                            echo $totalReturnStop . " Stop" . ($totalReturnStop > 1 ? "s" : "");
                                        } else {
                                            echo "Direct";
                                        }
                                        ?>
                                    </strong>
                                </div>
                            </li>
                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <?php
                                    $datetime = $lastReturnSegment['ArrivalDateTime'];
                                    list($date, $time) = explode("T", $datetime);
                                    ?>
                                    <strong style="font-size:16px;"><?php echo $lastReturnSegment['ArrivalAirportLocationCode']; ?></strong>
                                    <br>
                                    <?php echo date("d M Y", strtotime($date)); ?><br>
                                    <?php echo $time; ?>
                                </div>
                            </li>
                            <li data-th="Duration" class="main-dtls col-md-3 d-flex flex-column justify-content-between duration-dtls fs-13 mb-md-0 mb-2 text-center">
                                <div>
                                    <strong class="fw-500">
                                        Total Duration:<br />
                                        <?php
                                        $return_total_duration = 0;
                                        foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                            $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                            if ($origins['LegIndicator'] == 1) {
                                                $return_total_duration += $originSegment['JourneyDuration'];
                                            }
                                        }
                                        echo convertMinutesToTimeFormat($return_total_duration);
                                        ?>
                                    </strong>
                                </div>
                            </li>
                        </ul>
                        <?php endif; ?>
                        
                        <!-- Price and Action Section -->
                        <ul class="form-row" style="justify-content: space-between;align-items: center;border-top: 1px solid #eee;padding-top: 15px;">
                            <li class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <span class="mr-3">Cabin: <?php echo ucfirst($cabinClass); ?></span>
                                    <span class="mr-3">Trip: <?php echo $airTripType === 'Return' ? 'Round Trip' : 'One Way'; ?></span>
                                </div>
                            </li>
                            <li class="col-md-4 text-right">
                                <div class="d-flex align-items-center justify-content-end">
                                    <div class="mr-3">
                                        <strong style="font-size: 18px; color: #28a745;">
                                            $<?php echo number_format(round($total_price, 2), 2); ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">Total Price</small>
                                    </div>
                                    <button type="button" class="btn btn-success btn-select-flight" 
                                            data-fare-source="<?php echo $pricedItinerary['FareSourceCode']; ?>"
                                            data-price="<?php echo round($total_price, 2); ?>"
                                            data-flight-info='<?php echo json_encode($pricedItinerary); ?>'>
                                        Select Flight
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-bottom w-100 p-4 d-flex justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): 
                    $activeClass = ($i == $page) ? 'active' : '';
                ?>
                    <button class="btn <?php echo $activeClass; ?> mx-1 pagination-btn" 
                            data-page="<?php echo $i; ?>" 
                            <?php echo $i == $page ? 'disabled' : ''; ?>>
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            
            <?php if (count($currentPageFlights) === 0): ?>
            <div class="alert alert-warning text-center">
                <h5>No flights found for your search criteria</h5>
                <p>Please try different dates or modify your search parameters.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        // Handle flight selection
        document.querySelectorAll('.btn-select-flight').forEach(function(button) {
            button.addEventListener('click', function() {
                const fareSource = this.dataset.fareSource;
                const price = this.dataset.price;
                const flightInfo = JSON.parse(this.dataset.flightInfo);
                
                // Store the selected flight information
                const selectedFlight = {
                    fareSourceCode: fareSource,
                    price: price,
                    flightInfo: flightInfo,
                    bookingId: '<?php echo $bookingId; ?>',
                    selectedPassengers: <?php echo json_encode($selectedPassengers); ?>
                };
                
                // You can extend this to handle the reissue process
                alert('Flight selected! Price: $' + price + '\\nNext step: Process reissue request');
                
                // Here you would typically:
                // 1. Send the selection to server
                // 2. Process the reissue
                // 3. Show confirmation or next steps
                console.log('Selected flight:', selectedFlight);
            });
        });
        
        // Handle pagination
        document.querySelectorAll('.pagination-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                if (this.disabled) return; // Prevent clicks on current page
                
                const page = this.dataset.page;
                
                // Get the current form data from the main form
                const form = document.getElementById('flight-search_reissue');
                if (!form) {
                    console.error('Form not found');
                    return;
                }
                
                const formData = new FormData(form);
                formData.append('page', page);
                formData.append('action', 'search_reissue_flights');
                
                // Get selected passengers from the main form
                var selectedPassengers = [];
                document.querySelectorAll('.chkbox:checked').forEach(function(checkbox) {
                    selectedPassengers.push({
                        id: checkbox.value,
                        firstName: checkbox.dataset.firstname,
                        lastName: checkbox.dataset.lastname,
                        title: checkbox.dataset.title,
                        eticket: checkbox.dataset.eticket,
                        passengerType: checkbox.dataset.passengertype
                    });
                });
                formData.append('selected_passengers', JSON.stringify(selectedPassengers));
                
                // Show loading state
                var resultsContainer = document.getElementById('flight-search-results');
                if (resultsContainer) {
                    resultsContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading flights...</div>';
                }
                
                // Make AJAX request
                fetch('ajax_reissue_search.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.text();
                })
                .then(data => {
                    if (resultsContainer) {
                        resultsContainer.innerHTML = data;
                        // Scroll to top of results
                        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (resultsContainer) {
                        resultsContainer.innerHTML = '<div class="alert alert-danger">Error loading flights: ' + error.message + '</div>';
                    }
                });
            });
        });
        </script>
        
        <?php
    } else {
        echo '<div class="alert alert-danger">Error: ' . ($responseData['Message'] ?? 'No flights found') . '</div>';
    }
} else {
    $errorMessage = 'Failed to connect to flight search service. HTTP Code: ' . $httpCode;
    echo '<div class="alert alert-danger">' . $errorMessage . '</div>';
}

?> 