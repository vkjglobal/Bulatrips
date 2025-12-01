<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

// Handle filter removal from homepage (session-based filters) - BEFORE header output
if (isset($_GET['remove_checked_baggage']) && $_GET['remove_checked_baggage'] == '1') {
    if (isset($_SESSION['search_values']['checked_baggage_filter'])) {
        unset($_SESSION['search_values']['checked_baggage_filter']);
    }
    // Remove the parameter from URL to avoid redirect loop
    $params = $_GET;
    unset($params['remove_checked_baggage']);
    $redirectUrl = '?' . http_build_query($params);
    header('Location: ' . $redirectUrl);
    exit;
}

require_once("includes/header.php");
require_once('includes/dbConnect.php');
require_once('includes/common_const.php');

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<!-- Fix z-index for datepicker calendar to appear above sticky banner and modify search section -->
<style>
    /* Ensure datepicker appears above everything */
    .ui-datepicker {
        z-index: 99999 !important;
        box-shadow: 0 3px 15px rgba(0,0,0,0.3) !important;
    }

    /* Modify/Search interaction styles */
    #modify-search-result-btn.modify-btn-active {
        background-color: #6c757d !important; /* dull gray */
        border-color: #6c757d !important;
        color: #ffffff !important;
    }

    #modify-search-submit.search-highlight {
        background-color: #ff9800 !important; /* bright orange */
        border-color: #ff9800 !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.4);
    }
    
    /* Lower z-index for blue bar so datepicker appears above it */
    .midbar-wrapper-inner {
        z-index: 10 !important;
    }
    
    #modify-search-result {
        z-index: 5 !important;
    }
    
    /* Fix for mobile view - ensure calendar appears above modify search section */
    @media (max-width: 767px) {
        /* Remove extra space at top on mobile */
        .midbar-wrapper-inner {
            position: sticky !important;
            top: 48px !important;
            z-index: 10 !important;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            margin-top: 0 !important;
            margin-bottom: 10px !important;
            max-height: calc(100vh - 60px) !important;
            overflow-y: visible !important;
        }
        #modify-search-result {
            position: relative !important;
            z-index: 5 !important;
            background: rgba(18, 30, 126, 0.95) !important;
            margin-top: 0 !important;
            padding-top: 10px !important;
            padding-bottom: 20px !important;
            max-height: calc(100vh - 140px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        /* Remove extra spacing from form elements on mobile */
        #modify-search-result .flight-search {
            padding-top: 0 !important;
            padding-bottom: 15px !important;
        }
        #modify-search-result .d-flex {
            margin-top: 0 !important;
        }
        /* Ensure form fields have proper spacing on mobile */
        #modify-search-result .form-fields {
            margin-bottom: 10px !important;
        }
        /* Smooth scrolling for modify search section */
        #modify-search-result {
            -webkit-overflow-scrolling: touch !important;
            scroll-behavior: smooth !important;
        }
        /* Style scrollbar for better UX */
        #modify-search-result::-webkit-scrollbar {
            width: 4px;
        }
        #modify-search-result::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        #modify-search-result::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 2px;
        }
        /* Very high z-index for calendar - position handled by JS */
        .ui-datepicker {
            z-index: 99999 !important;
            max-width: 95vw !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5) !important;
        }
    }
</style>

<?php
$airport_depart = getAirPortLocationsByAirportCode($_SESSION['search_values']['airport'], $conn);
$airport_arrival = getAirPortLocationsByAirportCode($_SESSION['search_values']['arrivalairport'], $conn);

$usd_converion_rate = getConversionRate();

$searchValue = $_SESSION['search_values'];

$airTripType = $searchValue['tab'];
$cabinPreference = $searchValue['cabin-preference'];

$query = "SELECT airport_code,airport_name,city_name,country_name FROM airportlocations";
$stmt = $conn->prepare($query);
$stmt->execute();
$airports = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($searchValue['adult'])
    $adultCount = $searchValue['adult'];
else
    $adultCount = 0;
if ($searchValue['child'])
    $childCount = $searchValue['child'];
else
    $childCount = 0;
if ($searchValue['infant'])
    $infantCount = $searchValue['infant'];
else
    $infantCount = 0;



$originLocation = $searchValue['airport'];
$originLocationCode = explode("-", $originLocation);

$destinationLocation = $searchValue['arrivalairport'];
$destinationLocationCode = explode("-", $destinationLocation);

$fromDate = $searchValue['from'];
$departureDate = date("m/d/Y", strtotime($fromDate));
$ToDate = $searchValue['to'];
$returndepartureDate = date("m/d/Y", strtotime($ToDate));


$responseData  = $_SESSION['response'];

// echo "<pre>";
// print_r($searchValue);
// print_r($responseData);
// echo "</pre>";

$pricedItineraries = $responseData['Data']['PricedItineraries'];

// ============================================
// ============================================
// STEP 2: FLIGHT GROUPING LOGIC (BRANDED FARES)
// ============================================

// Get other necessary data arrays from API response
$segmentList = $responseData['Data']['FlightSegmentList'] ?? [];
$itineraryRefList = $responseData['Data']['ItineraryReferenceList'] ?? [];
$faresList = $responseData['Data']['FlightFaresList'] ?? [];
$penaltiesList = $responseData['Data']['PenaltiesInfoList'] ?? [];

// Initialize grouped flights array
$groupedFlights = [];

// GROUPING LOGIC: Group flights by segment combination
foreach ($pricedItineraries as $index => $itinerary) {
    
    // Create Unique Flight Key based on segments (not fare)
    $flightKey = '';
    $segmentDetails = []; // Store for display later
    
    foreach ($itinerary['OriginDestinations'] as $od) {
        $segmentRef = $od['SegmentRef'];
        $segment = $segmentList[$segmentRef] ?? null;
        
        if ($segment) {
            // Build unique key using flight number, date, and route
            $flightKey .= $segment['MarketingCarriercode'] . 
                          $segment['MarketingFlightNumber'] . '_' .
                          $segment['DepartureDateTime'] . '_' .
                          $segment['DepartureAirportLocationCode'] . 
                          $segment['ArrivalAirportLocationCode'] . '|';
            
            // Store segment for later use
            $segmentDetails[] = $segment;
        }
    }
    
    // Skip if no valid segments found
    if (empty($flightKey)) continue;
    
    // Initialize Group if Not Exists
    if (!isset($groupedFlights[$flightKey])) {
        $groupedFlights[$flightKey] = [
            'master_itinerary' => $itinerary,  // Will use for display
            'segments' => $segmentDetails,      // Flight segments
            'fare_options' => [],               // All fare variants
            'flight_key' => $flightKey          // For debugging
        ];
    }
    
    // Extract Fare Family Information
    // For return trips, we need to identify which leg this fare belongs to
    // Check if this itinerary has both departure (LegIndicator=0) and return (LegIndicator=1) legs
    $departureOD = null;
    $returnOD = null;
    
    foreach ($itinerary['OriginDestinations'] as $od) {
        $legIndicator = $od['LegIndicator'] ?? -1;
        if ($legIndicator == 0) {
            $departureOD = $od;
        } elseif ($legIndicator == 1) {
            $returnOD = $od;
        }
    }
    
    // Use departure leg by default (for one-way or to determine primary fare family)
    // In return trips, the fare family might be for the whole trip, but we'll use departure leg's fare family
    $targetOD = $departureOD ?? $itinerary['OriginDestinations'][0] ?? null;
    if (!$targetOD) continue;
    
    $itineraryRef = $targetOD['ItineraryRef'] ?? null;
    if ($itineraryRef === null) continue;
    
    // Get fare family details
    $itineraryRefData = $itineraryRefList[$itineraryRef] ?? [];
    $fareFamily = $itineraryRefData['FareFamily'] ?? '';
    
    // If empty, try to get from return leg if available
    if (empty($fareFamily) && $returnOD) {
        $returnItineraryRef = $returnOD['ItineraryRef'] ?? null;
        if ($returnItineraryRef !== null) {
            $returnItineraryRefData = $itineraryRefList[$returnItineraryRef] ?? [];
            $fareFamily = $returnItineraryRefData['FareFamily'] ?? '';
        }
    }
    
    // If empty, use a default name
    if (empty($fareFamily)) {
        $fareFamily = 'Standard';
    }
    
    // Get fare details
    $fareRef = $itinerary['FareRef'] ?? null;
    if ($fareRef === null) continue;
    
    $fareDetails = $faresList[$fareRef] ?? [];
    
    // Get price
    $totalTripPrice = $fareDetails['PassengerFare'][0]['TotalFare'] ?? 0;
    
    // Get refundability info from PenaltiesInfoList
    $penaltiesInfoRef = $itinerary['PenaltiesInfoRef'] ?? null;
    $isRefundable = false;
    $refundPenaltyAmount = '';
    if ($penaltiesInfoRef !== null && isset($penaltiesList[$penaltiesInfoRef])) {
        $penaltiesInfo = $penaltiesList[$penaltiesInfoRef];
        if (isset($penaltiesInfo['Penaltydetails'][0]['RefundAllowed'])) {
            $isRefundable = $penaltiesInfo['Penaltydetails'][0]['RefundAllowed'];
            $refundPenaltyAmount = $penaltiesInfo['Penaltydetails'][0]['RefundPenaltyAmount'] ?? '';
        }
    }
    
    // Get return leg fare family if available
    $returnFareFamily = '';
    $returnItineraryRef = null;
    if ($returnOD) {
        $returnItineraryRef = $returnOD['ItineraryRef'] ?? null;
        if ($returnItineraryRef !== null) {
            $returnItineraryRefData = $itineraryRefList[$returnItineraryRef] ?? [];
            $returnFareFamily = $returnItineraryRefData['FareFamily'] ?? '';
            if (empty($returnFareFamily)) {
                $returnFareFamily = 'Standard';
            }
        }
    }
    
    // Calculate per-leg price
    // For return trips: API provides total for both legs, so divide by 2
    // For one-way trips: use the total price as-is
    $isReturnTrip = ($returnOD !== null && $returnItineraryRef !== null);
    $perLegPrice = $isReturnTrip ? ($totalTripPrice / 2) : $totalTripPrice;
    
    // Add Fare Option to Group
    $groupedFlights[$flightKey]['fare_options'][] = [
        // Basic Info
        'fare_family' => $fareFamily,
        'fare_source_code' => $itinerary['FareSourceCode'] ?? '',
        'fare_ref' => $fareRef,
        'itinerary_ref' => $itineraryRef,
        
        // Leg Information
        'leg_type' => 'departure', // This fare option belongs to departure leg
        'departure_itinerary_ref' => $itineraryRef,
        'departure_fare_family' => $fareFamily,
        'return_itinerary_ref' => $returnItineraryRef,
        'return_fare_family' => $returnFareFamily,
        
        // Pricing
        'price' => floatval($perLegPrice),
        'total_trip_price' => floatval($totalTripPrice),
        'is_return_trip' => $isReturnTrip,
        'currency' => $fareDetails['Currency'] ?? 'USD',
        'fare_type' => $fareDetails['FareType'] ?? 'Public',
        
        // Refundability Info
        'is_refundable' => $isRefundable,
        'refund_penalty_amount' => $refundPenaltyAmount,
        
        // Baggage Info (from departure leg)
        'checked_baggage' => $itineraryRefData['CheckinBaggage'] ?? [],
        'cabin_baggage' => $itineraryRefData['CabinBaggage'] ?? [],
        
        // Additional Details
        'fare_basis_code' => $itineraryRefData['FareBasisCodes'] ?? '',
        'seats_remaining' => $itineraryRefData['SeatsRemaining'] ?? 0,
        'rbd' => $itineraryRefData['RBD'] ?? '',
        'original_index' => $index,
        
        // Store full itinerary reference for return leg lookup
        'master_itinerary' => $itinerary // Store for return leg extraction
    ];
    
    // Store full itinerary separately (not in button data)
    $groupedFlights[$flightKey]['fare_options_full'][$fareFamily] = $itinerary;
}

// Sort Fare Options by Price (Cheapest First)
foreach ($groupedFlights as $key => &$group) {
    if (!empty($group['fare_options'])) {
        usort($group['fare_options'], function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
    }
}
unset($group); // Break reference

// Extract all unique airlines from flights for filter dropdown
$uniqueAirlines = [];
foreach ($groupedFlights as $flightGroup) {
    $pricedItinerary = $flightGroup['master_itinerary'];
    $airlineCode = $pricedItinerary['ValidatingCarrier'] ?? '';
    if (!empty($airlineCode) && !isset($uniqueAirlines[$airlineCode])) {
        // Get airline name from database
        $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
        $code = '%' . $airlineCode . '%';
        $stmtairline->bindParam(':code', $code);
        $stmtairline->execute();
        $airlineData = $stmtairline->fetch(PDO::FETCH_ASSOC);
        
        $uniqueAirlines[$airlineCode] = [
            'code' => $airlineCode,
            'name' => $airlineData ? $airlineData['name'] : $airlineCode
        ];
    }
}
// Sort airlines by name
uasort($uniqueAirlines, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Apply filters BEFORE pagination
$filteredFlights = [];

// Check if checked_baggage filter is set from homepage search or from filter panel
$filterCheckedBaggage = !empty($_GET['checked_baggage']) || !empty($searchValue['checked_baggage_filter']);

$filterCabinOnly = !empty($_GET['cabin_only']);
$filterRefundable = !empty($_GET['refundable']);
$filterDateChanges = !empty($_GET['date_changes']);
$filterAirlines = isset($_GET['airlines']) && is_array($_GET['airlines']) ? $_GET['airlines'] : [];

$hasAnyFilter = $filterCheckedBaggage || $filterCabinOnly || $filterRefundable || $filterDateChanges || !empty($filterAirlines);

if ($hasAnyFilter) {
    $flightIndex = 0;
    foreach ($groupedFlights as $flightKey => $flightGroup) {
        $pricedItinerary = $flightGroup['master_itinerary'];
        
        // Extract actual API data for filter logic
        // Get penalty info for refundable and date change checks
        $penaltyListRefid = $pricedItinerary['PenaltiesInfoRef'] ?? null;
        $penaltyListRef = null;
        if ($penaltyListRefid !== null && isset($responseData['Data']['PenaltiesInfoList'][$penaltyListRefid])) {
            $penaltyListRef = $responseData['Data']['PenaltiesInfoList'][$penaltyListRefid];
        }
        
        // Check refundable - Handle boolean, int, and string types
        $isRefundableFare = false;
        if ($penaltyListRef && isset($penaltyListRef['Penaltydetails'][0]['RefundAllowed'])) {
            $refundValue = $penaltyListRef['Penaltydetails'][0]['RefundAllowed'];
            $isRefundableFare = ($refundValue === true || $refundValue === 1 || $refundValue === '1');
        }
        
        // Check date change allowed - Handle boolean, int, and string types
        $isDateChangeAllowed = false;
        if ($penaltyListRef && isset($penaltyListRef['Penaltydetails'][0]['ChangeAllowed'])) {
            $changeValue = $penaltyListRef['Penaltydetails'][0]['ChangeAllowed'];
            $isDateChangeAllowed = ($changeValue === true || $changeValue === 1 || $changeValue === '1');
        }
        
        // Check baggage info - FIXED for roundtrip flights
        $hasCheckedBaggage = false;
        $hasCabinBaggage = false;
        $zeroCheckedBaggageValues = ['', '0', '0PC', '0KG', 'NO', 'NIL', 'NA', 'N/A', 'NOT APPLICABLE'];
        $zeroCabinBaggageValues = ['', '0', '0PC', '0KG', 'NO', 'NIL', 'NA', 'N/A', 'NOT APPLICABLE'];
        
        $FlightItineraryList = $responseData['Data']['ItineraryReferenceList'];
        
        // For roundtrip flights, track each leg separately
        $legBaggageStatus = [];
        $legIndex = 0;
        
        foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
            $baggageRef = $originDestination['ItineraryRef'] ?? null;
            if ($baggageRef === null || !isset($FlightItineraryList[$baggageRef])) {
                $legBaggageStatus[$legIndex] = ['checked' => false, 'cabin' => false];
                $legIndex++;
                continue;
            }
            
            $baggageInfo = $FlightItineraryList[$baggageRef];
            
            // Track for this specific leg
            $legHasCheckedBaggage = false;
            $legHasCabinBaggage = false;
            
            // CheckinBaggage: "SB" or "0PC" means NO checked baggage
            if (!empty($baggageInfo['CheckinBaggage'])) {
                foreach ((array) $baggageInfo['CheckinBaggage'] as $bagItem) {
                    $value = strtoupper(trim($bagItem['Value'] ?? ''));
                    if ($value !== '' && !in_array($value, $zeroCheckedBaggageValues, true)) {
                        $legHasCheckedBaggage = true;
                        break;
                    }
                }
            }
            
            // CabinBaggage: "SB" means YES cabin baggage available
            if (!empty($baggageInfo['CabinBaggage'])) {
                foreach ((array) $baggageInfo['CabinBaggage'] as $bagItem) {
                    $value = strtoupper(trim($bagItem['Value'] ?? ''));
                    if ($value !== '' && !in_array($value, $zeroCabinBaggageValues, true)) {
                        $legHasCabinBaggage = true;
                        break;
                    }
                }
            }
            
            $legBaggageStatus[$legIndex] = [
                'checked' => $legHasCheckedBaggage,
                'cabin' => $legHasCabinBaggage
            ];
            
            $legIndex++;
        }
        
        // Now determine overall baggage status
        // For checked baggage filter: ALL legs must have checked baggage
        $allLegsHaveCheckedBaggage = true;
        $anyLegHasCheckedBaggage = false;
        $anyLegHasCabinBaggage = false;
        
        foreach ($legBaggageStatus as $leg) {
            if (!$leg['checked']) {
                $allLegsHaveCheckedBaggage = false;
            } else {
                $anyLegHasCheckedBaggage = true;
            }
            
            if ($leg['cabin']) {
                $anyLegHasCabinBaggage = true;
            }
        }
        
        // For filter: Use allLegsHaveCheckedBaggage for roundtrip, or anyLegHasCheckedBaggage for one-way
        $totalLegs = count($legBaggageStatus);
        if ($totalLegs > 1) {
            // Roundtrip: ALL legs must have checked baggage
            $hasCheckedBaggage = $allLegsHaveCheckedBaggage;
        } else {
            // One-way: Just check if the single leg has it
            $hasCheckedBaggage = $anyLegHasCheckedBaggage;
        }
        
        $hasCabinBaggage = $anyLegHasCabinBaggage;
        
        // Strict cabin-only: must have some cabin baggage AND zero checked baggage on ALL legs
        $isCabinOnlyFare = $anyLegHasCabinBaggage && !$anyLegHasCheckedBaggage;
        
        $flightIndex++;
        
        // Apply filters
        $matches = true;
        
        // FIXED: Make checked baggage and cabin-only mutually exclusive
        // If both are selected, cabin-only takes precedence (show only cabin baggage flights)
        if ($filterCheckedBaggage && $filterCabinOnly) {
            // When both selected, treat as cabin-only
            if (!$isCabinOnlyFare) {
                $matches = false;
            }
        } elseif ($filterCheckedBaggage && !$hasCheckedBaggage) {
            $matches = false;
        } elseif ($filterCabinOnly && !$isCabinOnlyFare) {
            $matches = false;
        }
        
        if ($filterRefundable && !$isRefundableFare) {
            $matches = false;
        }
        
        if ($filterDateChanges && !$isDateChangeAllowed) {
            $matches = false;
        }
        
        // Check airline filter
        if (!empty($filterAirlines)) {
            $flightAirlineCode = $pricedItinerary['ValidatingCarrier'] ?? '';
            if (!in_array($flightAirlineCode, $filterAirlines)) {
                $matches = false;
            }
        }
        
        if ($matches) {
            $filteredFlights[$flightKey] = $flightGroup;
        }
    }
} else {
    $filteredFlights = $groupedFlights;
}

// Update pagination to use filtered flights
$totalFlights = count($filteredFlights);
$flightsPerPage = 20; // Reduced from 1600 since showing unique flights
$totalPages = ceil($totalFlights / $flightsPerPage);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$startIndex = ($page - 1) * $flightsPerPage;

// Convert filtered array to indexed array for slicing
$filteredFlightsArray = array_values($filteredFlights);
$currentPageFlights = array_slice($filteredFlightsArray, $startIndex, $flightsPerPage);


$stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
$stmtlocation->execute(array('airport_code' => $originLocationCode[0]));
$airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

$airport_code_chosesn = $airportLocation['airport_code'];

$stmtlocation->execute(array('airport_code' => $destinationLocationCode[0]));
$airportDestinationLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

$stmtmarkup = $conn->prepare('SELECT * FROM markup_commission WHERE role_id = :role_id');
$stmtmarkup->execute(array('role_id' => 1));
$markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$stmt->bindValue(':key', "ipg_transaction_percentage");
$stmt->execute();
$setting = $stmt->fetch(PDO::FETCH_ASSOC);
$ipg_percentage = 0;

$ticketing_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$ticketing_fee->bindValue(':key', "ticketing_fee");
$ticketing_fee->execute();
$ticketing_fee_setting = $ticketing_fee->fetch(PDO::FETCH_ASSOC);
$ticketing_fee = 0;

$reissue_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$reissue_fee->bindValue(':key', "reissue_fee");
$reissue_fee->execute();
$reissue_fee_setting = $reissue_fee->fetch(PDO::FETCH_ASSOC);
$reissue_fee = 0;

$reissue_addition_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$reissue_addition_fee->bindValue(':key', "reissue_addition");
$reissue_addition_fee->execute();
$reissue_addition_fee_setting = $reissue_addition_fee->fetch(PDO::FETCH_ASSOC);
$reissue_addition_fee = 0;

$refund_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$refund_fee->bindValue(':key', "refund_fee");
$refund_fee->execute();
$refund_fee_setting = $refund_fee->fetch(PDO::FETCH_ASSOC);

$refund_addition_fee = $conn->prepare("SELECT `value` FROM settings WHERE `key` = :key");
$refund_addition_fee->bindValue(':key', "refund_addition");
$refund_addition_fee->execute();
$refund_addition_fee_setting = $refund_addition_fee->fetch(PDO::FETCH_ASSOC);



if (isset($setting['value']) && $setting['value'] != '') {
    $ipg_percentage = $setting['value'];
}

if (isset($ticketing_fee_setting['value']) && $ticketing_fee_setting['value'] != '') {
    $ticketing_fee = $ticketing_fee_setting['value'];
}

if (isset($_SESSION['response']) && isset($_SESSION['search_values'])) {
    // If API returned errors, set empty flights array so page continues normally
    if (isset($responseData['Data']['Errors'])) {
        $groupedFlights = [];
        $pricedItineraries = [];
    }
    ?>


        <!-- TOP BAR DETAILED AND SEARCH AGAIN SECTION STARTS -->
        <section class="midbar-wrapper-inner pt-3 pb-3" style="border-bottom: 2px solid #FFF;margin-bottom: 15px;position: sticky;top: 85px;z-index: 10;">
            <div class="flight-search-midbar container">

                <div class="d-flex white-txt justify-content-center">
                    <div class="d-flex align-items-center">
                        <span class="mr-3">

                            <?php echo $airportLocation['city_name']; ?> To <?php echo $airportDestinationLocation['city_name']; ?> |
                            <?php echo date("d M", strtotime($fromDate)); ?>
                            <?php
                            if (strtolower($airTripType) != "OneWay") {
                                echo " - " . date("d M", strtotime($ToDate));
                            }
                            ?>
                            |
                            <?php
                            echo "Type: ";
                            if (strtolower($airTripType) == "OneWay") {
                                echo "One way";
                            } else {
                                echo $airTripType;
                            }
                            ?> |
                            <?php echo "Passenger(s): " . $adultCount + $childCount + $infantCount; ?> |
                            <?php echo "Cabin: " . $searchValue['selected_cabin_text']; ?>
                        </span>
                        <button class="btn btn-typ7 ml-3" id="modify-search-result-btn">Modify Search</button>
                    </div>
                </div>

                <div class="row" id="modify-search-result" style="display: none;">

                    <form class="flight-search col-12" id="flight-search" method="post" action="search">
                        <div class="d-flex flex-md-row flex-column">
                            <div class="d-flex align-items-center justify-content-center mb-md-0 mb-3">
                                <input type="radio" value="Return" id="return" name="tab" <?php if (isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "Return") {
                                                                                                echo "checked";
                                                                                            } ?>>
                                <label for="return">Round-trip</label>
                                <input type="radio" id="one-way" value="OneWay" name="tab" <?php if (isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "OneWay") {
                                                                                                echo "checked";
                                                                                            } ?>>
                                <label for="one-way">One-way</label>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="select-class-wrp">
                                    <select name="cabin-preference" id="cabin-preference" style="width: 97px;color: #121E7E;border: none;">
                                        <option value="Y" <?php echo $cabinPreference == 'Y' ? 'selected' : ''; ?>>Economy</option>
                                        <option value="S" <?php echo $cabinPreference == 'S' ? 'selected' : ''; ?>>Premium</option>
                                        <option value="C" <?php echo $cabinPreference == 'C' ? 'selected' : ''; ?>>Business</option>
                                        <option value="F" <?php echo $cabinPreference == 'F' ? 'selected' : ''; ?>>First</option>
                                    </select>
                                </div>
                                <input type="hidden" id="selected_cabin_text" name="selected_cabin_text" value="Economy">
                                <!-- <span class="person-select" onclick="return fetchAndAlert()"> -->
                                <span class="person-select">
                                    <!-- <label for="" class="select-lbl">Traveller <span id="totalCount" class="count">1</span><span class="downarrow"></span></label> -->
                                    <label for="" class="select-lbl">Traveller(s) <span class="count"><?php echo $adultCount + $childCount + $infantCount  ?></span> <span class="downarrow"></span></label>
                                    <div class='select-dropbox passenger_container'>
                                        <span class="selectbox d-flex justify-content-between">
                                            <label class="fs-13 fw-600" for="">Adults
                                                <span class="fs-11">12 years and above</span>
                                            </label>
                                            <span class="selec-wrp d-inline-flex align-items-center">
                                                <!-- <input type='number' name="adult" min=1 value=1> -->
                                                <input type="number" id="adult_count" name="adult" min="1" value='<?php echo $adultCount ?>' readonly class="disabled">
                                                <span class='minus'>-</span>
                                                <span class='add'>+</span>
                                            </span>
                                        </span>
                                        <span class="selectbox d-flex justify-content-between">
                                            <label class="fs-13 fw-600" for="">Children
                                                <span class="fs-11">2 - 11 years</span>
                                            </label>
                                            <span class="selec-wrp d-inline-flex align-items-center">
                                                <input type='number' id="child-count" name="child" min='0' value='<?php echo $childCount ?>' readonly class="disabled">
                                                <span class='minus'>-</span>
                                                <span class='add'>+</span>
                                            </span>
                                        </span>
                                        <span class="selectbox d-flex justify-content-between">
                                            <label class="fs-13 fw-600" for="">Infants
                                                <span class="fs-11">Under 2 years</span>
                                            </label>
                                            <span class="selec-wrp d-inline-flex align-items-center">
                                                <input type='number' id="infant-count" name="infant" min='0' value='<?php echo $infantCount ?>' readonly class="disabled">
                                                <span class='minus'>-</span>
                                                <span class='add'>+</span>
                                            </span>
                                        </span>
                                    </div>
                                </span>
                            </div>

                            <!-- NEW ROW FOR DIRECT FLIGHTS CHECKBOX - Mobile Responsive -->
                            <div class="d-flex align-items-center justify-content-start mt-3 mt-md-2">
                                <div class="form-check" style="margin: 0;">
                                    <input class="form-check-input" type="checkbox" value="Direct" id="direct_flights" name="direct_flights" style="width: 19px;height: 19px; margin-top: 3px;" <?php if ($searchValue['direct_flights'] == "Direct") {
                                                                                                                                                                                                    echo "checked";
                                                                                                                                                                                                } ?>>
                                    <label class="form-check-label" for="direct_flights" style="margin-left: 5px; font-size:15px; color: #FFF; white-space: nowrap;"> Direct Flights only</label>
                                </div>
                            </div>
                        </div>

                        <div class="srch-fld">
                            <div class="search-box on row">
                                <div class="form-fields departure_container col-md-3">

                                    <select id="airport-input" style="width: 100%;" name="airport" class="select-class airport_location_finder_depature form-control" placeholder="Departing From"></select>
                                    <p class="error_codes"></p>
                                    <!-- <input type="text" id="airport-input" name="airport" class="form-control" placeholder="Departing From"> -->
                                    <!-- <input type="text" id="airportInput" name="airport" class="form-control" autocomplete="off"> -->
                                </div>
                                <div class="form-fields arrival_container col-md-3">
                                    <select id="arrivalairport-input" style="width: 100%;" name="arrivalairport" class="select-class airport_location_finder_arrival form-control"></select>
                                    <p class="error_codes"></p>
                                    <!-- <input type="text" class="form-control" placeholder="Going To"> -->
                                    <!-- <input type="text" id="arrivalairport-input" name="arrivalairport" class="form-control" placeholder="Going To"> -->

                                </div>
                                <div class="form-fields col-md-2 calndr-icon from_container">
                                    <input type="text" class="form-control" id="from" name="from" autocomplete="off" readonly value="<?php echo $departureDate ?>">
                                    <p class="error_codes"></p>
                                </div>
                                <div class="form-fields col-md-2 calndr-icon to_container">
                                    <input type="text" class="form-control" id="to" name="to" autocomplete="off" readonly value="<?php echo $returndepartureDate ?>">
                                    <p class="error_codes"></p>
                                </div>
                                <span id="errormessage"></span>
                                <div class="form-fields col-md-2">
                                    <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                                    <input type="submit" id="modify-search-submit" name="go" class="btn btn-typ1 w-100 form-control" value="Search">
                                </div>
                            </div>

                            <div class="search-box row multi-city-search">
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="form-fields col-md-4">
                                            <!-- <input type="text" class="form-control" placeholder="Departing From"> -->
                                            <input type="text" id="departure_from_1" name="departure_from_1" class="form-control" placeholder="Departing From">

                                        </div>
                                        <div class="form-fields col-md-4">
                                            <input type="text" id="arrival_to_1" name="arrival_to_1" class="form-control" placeholder="Going To">

                                        </div>
                                        <div class="form-fields col-md-2 calndr-icon">

                                            <input type="date" class="form-control date-multy-city" id="departure_date_1" name="departure_date_1">
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mt-md-2">
                                        <div class="form-fields col-md-4">
                                            <!-- <input type="text" id="departure_from_2" name="departure_from_2" class="form-control" placeholder="Departing From"> -->
                                            <input type="text" id="departure_from_2" name="departure_from_2" class="form-control" placeholder="Departing From">
                                        </div>
                                        <div class="form-fields col-md-4">
                                            <input type="text" id="arrival_to_2" name="arrival_to_2" class="form-control" placeholder="Going To">
                                        </div>
                                        <div class="form-fields col-md-2 calndr-icon">
                                            <input type="date" class="form-control date-multy-city" id="departure_date_2" name="departure_date_2">
                                            <span class="icon">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path id="Vector" d="M3.25 0C2.38805 0 1.5614 0.34241 0.951903 0.951903C0.34241 1.5614 0 2.38805 0 3.25V14.75C0 15.612 0.34241 16.4386 0.951903 17.0481C1.5614 17.6576 2.38805 18 3.25 18H14.75C15.612 18 16.4386 17.6576 17.0481 17.0481C17.6576 16.4386 18 15.612 18 14.75V3.25C18 2.38805 17.6576 1.5614 17.0481 0.951903C16.4386 0.34241 15.612 0 14.75 0H3.25ZM1.5 5.5H16.5V14.75C16.5 15.2141 16.3156 15.6592 15.9874 15.9874C15.6592 16.3156 15.2141 16.5 14.75 16.5H3.25C2.78587 16.5 2.34075 16.3156 2.01256 15.9874C1.68437 15.6592 1.5 15.2141 1.5 14.75V5.5ZM13.25 11.5C12.9185 11.5 12.6005 11.6317 12.3661 11.8661C12.1317 12.1005 12 12.4185 12 12.75C12 13.0815 12.1317 13.3995 12.3661 13.6339C12.6005 13.8683 12.9185 14 13.25 14C13.5815 14 13.8995 13.8683 14.1339 13.6339C14.3683 13.3995 14.5 13.0815 14.5 12.75C14.5 12.4185 14.3683 12.1005 14.1339 11.8661C13.8995 11.6317 13.5815 11.5 13.25 11.5V11.5ZM9 11.5C8.66848 11.5 8.35054 11.6317 8.11612 11.8661C7.8817 12.1005 7.75 12.4185 7.75 12.75C7.75 13.0815 7.8817 13.3995 8.11612 13.6339C8.35054 13.8683 8.66848 14 9 14C9.33152 14 9.64946 13.8683 9.88388 13.6339C10.1183 13.3995 10.25 13.0815 10.25 12.75C10.25 12.4185 10.1183 12.1005 9.88388 11.8661C9.64946 11.6317 9.33152 11.5 9 11.5V11.5ZM13.25 7.5C12.9185 7.5 12.6005 7.6317 12.3661 7.86612C12.1317 8.10054 12 8.41848 12 8.75C12 9.08152 12.1317 9.39946 12.3661 9.63388C12.6005 9.8683 12.9185 10 13.25 10C13.5815 10 13.8995 9.8683 14.1339 9.63388C14.3683 9.39946 14.5 9.08152 14.5 8.75C14.5 8.41848 14.3683 8.10054 14.1339 7.86612C13.8995 7.6317 13.5815 7.5 13.25 7.5ZM9 7.5C8.66848 7.5 8.35054 7.6317 8.11612 7.86612C7.8817 8.10054 7.75 8.41848 7.75 8.75C7.75 9.08152 7.8817 9.39946 8.11612 9.63388C8.35054 9.8683 8.66848 10 9 10C9.33152 10 9.64946 9.8683 9.88388 9.63388C10.1183 9.39946 10.25 9.08152 10.25 8.75C10.25 8.41848 10.1183 8.10054 9.88388 7.86612C9.64946 7.6317 9.33152 7.5 9 7.5V7.5ZM4.75 7.5C4.41848 7.5 4.10054 7.6317 3.86612 7.86612C3.6317 8.10054 3.5 8.41848 3.5 8.75C3.5 9.08152 3.6317 9.39946 3.86612 9.63388C4.10054 9.8683 4.41848 10 4.75 10C5.08152 10 5.39946 9.8683 5.63388 9.63388C5.8683 9.39946 6 9.08152 6 8.75C6 8.41848 5.8683 8.10054 5.63388 7.86612C5.39946 7.6317 5.08152 7.5 4.75 7.5ZM3.25 1.5H14.75C15.716 1.5 16.5 2.284 16.5 3.25V4H1.5V3.25C1.5 2.284 2.284 1.5 3.25 1.5Z" fill="#6D759C" />
                                                </svg>
                                            </span>
                                        </div>


                                    </div>
                                    <div id="additional_trips">
                                    </div>
                                    <div class="form-fields">
                                        <button type="button" id="add_trip_button" class="btn add-trip fw-500 dark-blue-txt">Add Trip +</button>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-fields">
                                        <!-- <button class="btn btn-typ1 w-100 form-control">Search</button> -->
                                        <input type="submit" value="Search" class="btn btn-typ1 w-100 form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

            </div>
        </section>
        <!-- TOP BAR DETAILED AND SEARCH AGAIN SECTION ENDS -->

        <!-- BREADCRUMB STARTS HERE -->
        <section style="margin-bottom: 10px;">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="breadcrumbs">
                            <li><a href="index" style="text-decoration: underline !important;">Home</a></li>
                            <li> <?php echo $airportLocation['city_name'] . ' to ' . $airportDestinationLocation['city_name'] . ' ' . $airTripType ?> </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- BREADCRUMB STARTS HERE -->

        <!-- FILTERATION PART STARTS -->
        <style>
            /* Orange style for Select Fares & Book button */
            .select-fares-btn {
                background-color: #FF6C00 !important;
                border-color: #FF6C00 !important;
                color: #ffffff !important;
            }
            .select-fares-btn:hover,
            .select-fares-btn:focus,
            .select-fares-btn:active {
                background-color: #e65f00 !important;
                border-color: #e65f00 !important;
                color: #ffffff !important;
            }
        </style>
        <section class="filter-toggle-section mb-3" style="margin-bottom: 15px;">
            <div class="container">
                <div class="row">
                    <div class="col-12 d-flex justify-content-end align-items-center">
                        <!-- Filter Toggle Button -->
                        <button type="button" 
                                id="filter-toggle-btn" 
                                class="btn btn-outline-primary btn-lg"
                                style="display: flex; align-items: center; justify-content: center; padding: 8px 15px; font-size: 14px; font-weight: 600; border: 2px solid #007bff; background: transparent; color: #007bff; transition: all 0.3s ease; width: auto; min-width: 120px;">
                            <span style="font-size: 18px; margin-right: 8px;">☰</span>
                            <span id="filter-toggle-text">Filters</span>
                            <span id="filter-count-badge" style="margin-left: 6px; background: #007bff; color: white; border-radius: 12px; padding: 2px 8px; font-size: 12px; font-weight: 600; min-width: 20px; display: inline-block; text-align: center; display: none;">0</span>
                            <span id="filter-toggle-icon" style="margin-left: 6px; transition: transform 0.3s ease; font-size: 12px;">▼</span>
                        </button>
                        <!-- Flight Count (Plain Text) -->
                        <span id="flight-count" data-original="<?php echo count($groupedFlights); ?>" style="font-size: 15px; color: #495057; font-weight: 600; margin-left: 15px;">
                            Showing: <strong style="color: #007bff;"><?php echo $totalFlights; ?></strong> flights<?php echo $hasAnyFilter ? ' (filtered)' : ''; ?>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter Container (Initially Hidden) -->
        <section class="filter-section mb-4" id="filter-container" style="display: none; margin-bottom: 15px;">
            <div class="container">
                <div class="form-row" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #dee2e6;">
                    <div class="col-12">
                        <form id="flight-filters-form" method="get" action="">
                            <?php
                            // Preserve existing GET parameters except filters
                            foreach ($_GET as $key => $value) {
                                if (!in_array($key, ['checked_baggage', 'cabin_only', 'refundable', 'date_changes', 'airlines', 'page'])) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                }
                            }
                            ?>
                            <div class="row align-items-center">
                                <div class="col-md-12 mb-2">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                            <?php $isCheckedBaggageActive = (!empty($_GET['checked_baggage']) || !empty($searchValue['checked_baggage_filter'])); ?>
                                            <input class="filter-checkbox" type="checkbox" name="checked_baggage" id="filter-checked-baggage" value="1" <?php echo $isCheckedBaggageActive ? 'checked' : ''; ?> style="display: none;">
                                            <div class="filter-item" data-filter-id="filter-checked-baggage" style="background: <?php echo $isCheckedBaggageActive ? '#e7f3ff' : 'white'; ?>; padding: 12px 15px; border-radius: 8px; border: 2px solid <?php echo $isCheckedBaggageActive ? '#007bff' : '#e9ecef'; ?>; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;" onmouseover="if(!this.classList.contains('active')) { this.style.borderColor='#007bff'; this.style.boxShadow='0 2px 6px rgba(0,123,255,0.2)'; }" onmouseout="if(!this.classList.contains('active')) { this.style.borderColor='<?php echo $isCheckedBaggageActive ? '#007bff' : '#e9ecef'; ?>'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'; }">
                                                <span style="font-size: 14px; font-weight: 600; color: <?php echo $isCheckedBaggageActive ? '#007bff' : '#495057'; ?>;">
                                                    ✅ Checked baggage included
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                            <input class="filter-checkbox" type="checkbox" name="cabin_only" id="filter-cabin-only" value="1" <?php echo (!empty($_GET['cabin_only'])) ? 'checked' : ''; ?> style="display: none;">
                                            <div class="filter-item" data-filter-id="filter-cabin-only" style="background: <?php echo (!empty($_GET['cabin_only'])) ? '#e7f3ff' : 'white'; ?>; padding: 12px 15px; border-radius: 8px; border: 2px solid <?php echo (!empty($_GET['cabin_only'])) ? '#007bff' : '#e9ecef'; ?>; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;" onmouseover="if(!this.classList.contains('active')) { this.style.borderColor='#007bff'; this.style.boxShadow='0 2px 6px rgba(0,123,255,0.2)'; }" onmouseout="if(!this.classList.contains('active')) { this.style.borderColor='<?php echo (!empty($_GET['cabin_only'])) ? '#007bff' : '#e9ecef'; ?>'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'; }">
                                                <span style="font-size: 14px; font-weight: 600; color: <?php echo (!empty($_GET['cabin_only'])) ? '#007bff' : '#495057'; ?>;">
                                                    🎒 Cabin baggage only
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                            <input class="filter-checkbox" type="checkbox" name="refundable" id="filter-refundable" value="1" <?php echo (!empty($_GET['refundable'])) ? 'checked' : ''; ?> style="display: none;">
                                            <div class="filter-item" data-filter-id="filter-refundable" style="background: <?php echo (!empty($_GET['refundable'])) ? '#e7f3ff' : 'white'; ?>; padding: 12px 15px; border-radius: 8px; border: 2px solid <?php echo (!empty($_GET['refundable'])) ? '#007bff' : '#e9ecef'; ?>; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;" onmouseover="if(!this.classList.contains('active')) { this.style.borderColor='#007bff'; this.style.boxShadow='0 2px 6px rgba(0,123,255,0.2)'; }" onmouseout="if(!this.classList.contains('active')) { this.style.borderColor='<?php echo (!empty($_GET['refundable'])) ? '#007bff' : '#e9ecef'; ?>'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'; }">
                                                <span style="font-size: 14px; font-weight: 600; color: <?php echo (!empty($_GET['refundable'])) ? '#007bff' : '#495057'; ?>;">
                                                    💰 Refundable fares only
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-6 mb-2">
                                            <input class="filter-checkbox" type="checkbox" name="date_changes" id="filter-date-changes" value="1" <?php echo (!empty($_GET['date_changes'])) ? 'checked' : ''; ?> style="display: none;">
                                            <div class="filter-item" data-filter-id="filter-date-changes" style="background: <?php echo (!empty($_GET['date_changes'])) ? '#e7f3ff' : 'white'; ?>; padding: 12px 15px; border-radius: 8px; border: 2px solid <?php echo (!empty($_GET['date_changes'])) ? '#007bff' : '#e9ecef'; ?>; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-align: center;" onmouseover="if(!this.classList.contains('active')) { this.style.borderColor='#007bff'; this.style.boxShadow='0 2px 6px rgba(0,123,255,0.2)'; }" onmouseout="if(!this.classList.contains('active')) { this.style.borderColor='<?php echo (!empty($_GET['date_changes'])) ? '#007bff' : '#e9ecef'; ?>'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)'; }">
                                                <span style="font-size: 14px; font-weight: 600; color: <?php echo (!empty($_GET['date_changes'])) ? '#007bff' : '#495057'; ?>;">
                                                    📅 Date changes allowed
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Airlines Filter -->
                            <div class="row align-items-center mt-2">
                                <div class="col-md-12">
                                    <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 2px solid #e9ecef; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                        <label for="airlines-filter" style="font-size: 14px; font-weight: 600; color: #495057; margin-bottom: 8px; display: block;">
                                            ✈️ Filter by Airlines
                                        </label>
                                        <select name="airlines[]" id="airlines-filter" class="form-control" multiple="multiple" style="width: 100%;">
                                            <?php foreach ($uniqueAirlines as $airline): ?>
                                                <option value="<?php echo htmlspecialchars($airline['code']); ?>" 
                                                    <?php echo (in_array($airline['code'], $filterAirlines)) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($airline['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 d-flex align-items-center flex-wrap">
                                    <button type="submit" class="btn btn-primary mr-2 mb-2" id="apply-filters" style="padding: 8px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,123,255,0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,123,255,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,123,255,0.3)'">
                                        Apply Filters
                                    </button>
                                    <a href="?" class="btn btn-outline-secondary mr-2 mb-2" id="clear-filters" style="padding: 8px 20px; font-size: 14px; font-weight: 600; border-radius: 6px; border: 2px solid #6c757d; transition: all 0.3s ease; text-decoration: none; display: inline-block;" onmouseover="this.style.transform='translateY(-2px)'; this.style.backgroundColor='#6c757d'; this.style.color='white'" onmouseout="this.style.transform='translateY(0)'; this.style.backgroundColor='transparent'; this.style.color='#6c757d'">
                                        Clear All
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- FILTERATION PART ENDS -->

        <!-- ACTIVE FILTERS DISPLAY SECTION -->
        <?php if ($hasAnyFilter): ?>
        <section style="margin-bottom: 15px;">
            <div class="container">
                <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <span style="font-weight: 600; color: #495057; margin-right: 5px;">Active Filters:</span>
                        
                        <?php if ($filterCheckedBaggage): ?>
                        <div class="active-filter-tag" style="display: inline-flex; align-items: center; background: #e7f3ff; border: 1px solid #007bff; border-radius: 20px; padding: 6px 12px; font-size: 13px; color: #007bff;">
                            <span style="margin-right: 6px;">✅ Checked Baggage</span>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['checked_baggage']);
                                // If filter came from homepage (session), we need to clear it from session too
                                // Add a parameter to indicate filter removal
                                $params['remove_checked_baggage'] = '1';
                                echo http_build_query($params);
                            ?>" style="color: #007bff; text-decoration: none; font-weight: bold; margin-left: 4px;">×</a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($filterCabinOnly): ?>
                        <div class="active-filter-tag" style="display: inline-flex; align-items: center; background: #e7f3ff; border: 1px solid #007bff; border-radius: 20px; padding: 6px 12px; font-size: 13px; color: #007bff;">
                            <span style="margin-right: 6px;">🎒 Cabin Only</span>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['cabin_only']);
                                echo http_build_query($params);
                            ?>" style="color: #007bff; text-decoration: none; font-weight: bold; margin-left: 4px;">×</a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($filterRefundable): ?>
                        <div class="active-filter-tag" style="display: inline-flex; align-items: center; background: #e7f3ff; border: 1px solid #007bff; border-radius: 20px; padding: 6px 12px; font-size: 13px; color: #007bff;">
                            <span style="margin-right: 6px;">💰 Refundable</span>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['refundable']);
                                echo http_build_query($params);
                            ?>" style="color: #007bff; text-decoration: none; font-weight: bold; margin-left: 4px;">×</a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($filterDateChanges): ?>
                        <div class="active-filter-tag" style="display: inline-flex; align-items: center; background: #e7f3ff; border: 1px solid #007bff; border-radius: 20px; padding: 6px 12px; font-size: 13px; color: #007bff;">
                            <span style="margin-right: 6px;">📅 Date Changes</span>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['date_changes']);
                                echo http_build_query($params);
                            ?>" style="color: #007bff; text-decoration: none; font-weight: bold; margin-left: 4px;">×</a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($filterAirlines)): ?>
                        <div class="active-filter-tag" style="display: inline-flex; align-items: center; background: #e7f3ff; border: 1px solid #007bff; border-radius: 20px; padding: 6px 12px; font-size: 13px; color: #007bff;">
                            <span style="margin-right: 6px;">✈️ Airlines (<?php echo count($filterAirlines); ?>)</span>
                            <a href="?<?php 
                                $params = $_GET;
                                unset($params['airlines']);
                                echo http_build_query($params);
                            ?>" style="color: #007bff; text-decoration: none; font-weight: bold; margin-left: 4px;">×</a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Clear All Filters Button -->
                        <a href="?<?php 
                            $params = $_GET;
                            unset($params['checked_baggage'], $params['cabin_only'], $params['refundable'], $params['date_changes'], $params['airlines'], $params['page']);
                            // Clear session-based checked baggage filter too
                            $params['remove_checked_baggage'] = '1';
                            echo http_build_query($params);
                        ?>" style="display: inline-flex; align-items: center; background: #dc3545; color: white; border-radius: 20px; padding: 6px 14px; font-size: 13px; text-decoration: none; font-weight: 600; margin-left: 5px;">
                            Clear All Filters
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        <!-- ACTIVE FILTERS DISPLAY ENDS -->

        <section style="margin-bottom:20px;">
            <div class="container">
                <!-- No Results Message - Unified Design -->
                <?php if (count($currentPageFlights) == 0): ?>
                <div class="col-12" style="padding: 30px 10px; text-align: center; margin-bottom: 20px;">
                    <div style="background-color:#070F4E; padding: 28px 32px; border-radius: 15px; color: #fff; box-shadow: 0 4px 15px rgba(18, 30, 126, 0.2); max-width: 40%; margin: 0 auto;">
                        <h2 style="font-size: 34px; margin-bottom: 12px; font-weight: bold;">Sorry!</h2>
                        <p style="font-size: 16px; margin-bottom: 16px; line-height: 1.5;">
                            <?php 
                            if (isset($responseData['Data']['Errors'])) {
                                echo "We couldn't find any flights for the selected dates.";
                            } else {
                                echo "No flights match your selected filters.";
                            }
                            ?>
                        </p>
                        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 0;">
                            Please try different dates or adjust your search criteria using the <strong>"Modify Search"</strong> button above or clear the filters below.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-row  g-3">
                    <?php 
                    $flightLoopIndex = 0;
                    foreach ($currentPageFlights as $flightGroup) { 
                        // Extract master itinerary from grouped data
                        $pricedItinerary = $flightGroup['master_itinerary'];
                        
                        // Find the index in the full array
                        $fullIndex = -1;
                        foreach ($pricedItineraries as $idx => $itinerary) {
                            if ($itinerary['FareSourceCode'] === $pricedItinerary['FareSourceCode']) {
                                $fullIndex = $idx;
                                break;
                            }
                        }
                        if ($fullIndex === -1) {
                            $fullIndex = $flightLoopIndex;
                        }
                        $flightLoopIndex++;
                    ?>
                        <?php
                            // Extract actual API data for display - Get penalty reference
                            $penaltyListRefid = $pricedItinerary['PenaltiesInfoRef'] ?? null;
                            $penaltyListRef = null;
                            if ($penaltyListRefid !== null && isset($responseData['Data']['PenaltiesInfoList'][$penaltyListRefid])) {
                                $penaltyListRef = $responseData['Data']['PenaltiesInfoList'][$penaltyListRefid];
                            }
                            
                            $penaltyDetails = $penaltyListRef['Penaltydetails'][0] ?? [];
                            
                            // Check refundable - Handle boolean, int, and string types
                            $isRefundableFare = false;
                            if (isset($penaltyDetails['RefundAllowed'])) {
                                $refundValue = $penaltyDetails['RefundAllowed'];
                                $isRefundableFare = ($refundValue === true || $refundValue === 1 || $refundValue === '1');
                            }
                            
                            // Check date change allowed - Handle boolean, int, and string types
                            $isDateChangeAllowed = false;
                            if (isset($penaltyDetails['ChangeAllowed'])) {
                                $changeValue = $penaltyDetails['ChangeAllowed'];
                                $isDateChangeAllowed = ($changeValue === true || $changeValue === 1 || $changeValue === '1');
                            }

                            // Check baggage info
                            $hasCheckedBaggage = false;
                            $hasCabinBaggage = false;
                            $zeroCheckedBaggageValues = ['', '0', '0PC', '0KG', 'NO', 'NIL', 'NA', 'N/A', 'NOT APPLICABLE'];
                            $zeroCabinBaggageValues = ['', '0', '0PC', '0KG', 'NO', 'NIL', 'NA', 'N/A', 'NOT APPLICABLE'];

                            foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                $baggageRef = $originDestination['ItineraryRef'] ?? null;
                                if ($baggageRef === null || !isset($FlightItineraryList[$baggageRef])) {
                                    continue;
                                }

                                $baggageInfo = $FlightItineraryList[$baggageRef];

                                // CheckinBaggage: "SB" or "0PC" means NO checked baggage
                                if (!$hasCheckedBaggage && !empty($baggageInfo['CheckinBaggage'])) {
                                    foreach ((array) $baggageInfo['CheckinBaggage'] as $bagItem) {
                                        $value = strtoupper(trim($bagItem['Value'] ?? ''));
                                        if ($value !== '' && !in_array($value, $zeroCheckedBaggageValues, true)) {
                                            $hasCheckedBaggage = true;
                                            break;
                                        }
                                    }
                                }

                                // CabinBaggage: "SB" means YES cabin baggage available
                                if (!$hasCabinBaggage && !empty($baggageInfo['CabinBaggage'])) {
                                    foreach ((array) $baggageInfo['CabinBaggage'] as $bagItem) {
                                        $value = strtoupper(trim($bagItem['Value'] ?? ''));
                                        if ($value !== '' && !in_array($value, $zeroCabinBaggageValues, true)) {
                                            $hasCabinBaggage = true;
                                            break;
                                        }
                                    }
                                }

                                if ($hasCheckedBaggage && $hasCabinBaggage) {
                                    break;
                                }
                            }

                            // FIXED: Cabin-only means has cabin baggage BUT NO checked baggage
                            $isCabinOnlyFare = $hasCabinBaggage && !$hasCheckedBaggage;
                            
                            // Extract baggage values for data attributes - Use the global $FlightItineraryList
                            $FlightItineraryListGlobal = $responseData['Data']['ItineraryReferenceList'];
                            $checkedBagDisplay = '';
                            $cabinBagDisplay = '';
                            foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                $baggageRef = $baggages['ItineraryRef'];
                                if (isset($FlightItineraryListGlobal[$baggageRef])) {
                                    $baggageSegment = $FlightItineraryListGlobal[$baggageRef];
                                    
                                    // Only process departure leg (LegIndicator == 0)
                                    if ($baggages['LegIndicator'] == 0) {
                                        // Get checked baggage
                                        if (isset($baggageSegment['CheckinBaggage'][0]['Value'])) {
                                            $val = $baggageSegment['CheckinBaggage'][0]['Value'];
                                            $checkedBagDisplay = (strtolower($val) == "sb") ? "Standard Baggage" : $val;
                                        }
                                        
                                        // Get cabin baggage
                                        if (isset($baggageSegment['CabinBaggage'][0]['Value'])) {
                                            $val = $baggageSegment['CabinBaggage'][0]['Value'];
                                            $cabinBagDisplay = (strtolower($val) == "sb") ? "Standard Baggage" : $val;
                                        }
                                        
                                        break; // Only first departure leg
                                    }
                                }
                            }
                            
                            // Extract return leg baggage info
                            $hasReturnCheckedBaggage = false;
                            $hasReturnCabinBaggage = false;
                            $returnCheckedBagDisplay = '';
                            $returnCabinBagDisplay = '';
                            
                            // Check if this is a return trip
                            $isReturnTrip = false;
                            foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                if (isset($originDestination['LegIndicator']) && $originDestination['LegIndicator'] == 1) {
                                    $isReturnTrip = true;
                                    break;
                                }
                            }
                            
                            if ($isReturnTrip) {
                                foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                    $baggageRef = $baggages['ItineraryRef'];
                                    if (isset($FlightItineraryListGlobal[$baggageRef])) {
                                        $baggageSegment = $FlightItineraryListGlobal[$baggageRef];
                                        
                                        // Only process return leg (LegIndicator == 1)
                                        if ($baggages['LegIndicator'] == 1) {
                                            // Get checked baggage
                                            if (isset($baggageSegment['CheckinBaggage'][0]['Value'])) {
                                                $val = $baggageSegment['CheckinBaggage'][0]['Value'];
                                                $returnCheckedBagDisplay = (strtolower($val) == "sb") ? "Standard Baggage" : $val;
                                                $upperVal = strtoupper($val);
                                                if ($upperVal !== '0KG' && $upperVal !== '0PC' && $val !== '0') {
                                                    $hasReturnCheckedBaggage = true;
                                                }
                                            }
                                            
                                            // Get cabin baggage
                                            if (isset($baggageSegment['CabinBaggage'][0]['Value'])) {
                                                $val = $baggageSegment['CabinBaggage'][0]['Value'];
                                                $returnCabinBagDisplay = (strtolower($val) == "sb") ? "Standard Baggage" : $val;
                                                $hasReturnCabinBaggage = true;
                                            }
                                            
                                            break; // Only first return leg
                                        }
                                    }
                                }
                            }
                        ?>
                        <div class="flight-card col-xs-12 col-sm-12 col-md-12 col-lg-12"
                             data-refundable="<?php echo $isRefundableFare ? '1' : '0'; ?>"
                             data-date-change="<?php echo $isDateChangeAllowed ? '1' : '0'; ?>"
                             data-checked-baggage="<?php echo $hasCheckedBaggage ? '1' : '0'; ?>"
                             data-cabin-only="<?php echo $isCabinOnlyFare ? '1' : '0'; ?>"
                             data-checked-baggage-value="<?php echo htmlspecialchars($checkedBagDisplay); ?>"
                             data-cabin-baggage-value="<?php echo htmlspecialchars($cabinBagDisplay); ?>"
                             data-has-cabin="<?php echo $hasCabinBaggage ? '1' : '0'; ?>"
                             data-return-checked-baggage="<?php echo $hasReturnCheckedBaggage ? '1' : '0'; ?>"
                             data-return-checked-baggage-value="<?php echo htmlspecialchars($returnCheckedBagDisplay); ?>"
                             data-return-cabin-baggage-value="<?php echo htmlspecialchars($returnCabinBagDisplay); ?>"
                             data-return-has-cabin="<?php echo $hasReturnCabinBaggage ? '1' : '0'; ?>">
                            <div class="light-border mb-3 p-0 me-3">
                                <?php
                                $totalstop = 0;
                                foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                    if ($originDestination['LegIndicator'] == 0) {
                                        $totalstop = $totalstop + 1;
                                    }
                                }
                                $totalstop = $totalstop - 1;

                                $originDestinations = $pricedItinerary['OriginDestinations'][0];
                                $segmentRef = $originDestinations['SegmentRef'];
                                $flightSegmentList = $responseData['Data']['FlightSegmentList'];
                                $FlightFaresList = $responseData['Data']['FlightFaresList'];

                                $FlightItineraryList = $responseData['Data']['ItineraryReferenceList'];
                                $fareListRefid = $pricedItinerary['FareRef'];
                                $fareListRef = $FlightFaresList[$fareListRefid];

                                $FlightPenaltyList = $responseData['Data']['PenaltiesInfoList'];
                                $penaltyListRefid = $pricedItinerary['PenaltiesInfoRef'];
                                $penaltyListRef = $FlightPenaltyList[$penaltyListRefid];
                                $onestop = false;
                                ?>


                                <p style="color: #000000;font-size: 14px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;padding: 5px 10px;background-color: #ffe4cc;">
                                    <img class="flight_icon_small" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg" />
                                    <strong>Departure</strong>
                                    <span style="display: block;float: right;position: absolute;right: 18px;text-transform: capitalize;font-size: 15px;"><?php echo " Fare type: " . $fareListRef['FareType']; ?></span>
                                </p>

                                <ul class="flight-list">
                                    <?php
                                    if ($totalstop > 0) {
                                        $originDestinationsstops = $pricedItinerary['OriginDestinations'][$totalstop];
                                        $segmentRefstop = $originDestinationsstops['SegmentRef'];

                                        $segmentstop = $flightSegmentList[$segmentRefstop];
                                        $duration = $segmentstop['JourneyDuration'];
                                        $arrival = $segmentstop['ArrivalAirportLocationCode'];
                                        $artime = $segmentstop['ArrivalDateTime'];
                                        $deptime = $segmentstop['DepartureDateTime'];
                                    }

                                    $segment = $flightSegmentList[$segmentRef];

                                    //-------find total return stop and get return details information----
                                    $totalReturnStop = 0;
                                    $filteredSegments = [];
                                    foreach ($pricedItinerary['OriginDestinations'] as $originDestination) {
                                        if ($originDestination['LegIndicator'] == 1) {
                                            $filteredSegments[] = $originDestination['SegmentRef'];
                                        }
                                    }
                                    $totalReturnStop = count($filteredSegments) - 1;
                                    $totalDurationReturn = 0;

                                    foreach ($filteredSegments as $segmentRef) {
                                        foreach ($flightSegmentList as $flightSegment) {
                                            if ($flightSegment['SegmentRef'] == $segmentRef) {
                                                $totalDurationReturn += $flightSegment['JourneyDuration'];
                                            }
                                        }
                                    }

                                    if ($totalReturnStop >= 0) {

                                        $originDestinationsstops = $pricedItinerary['OriginDestinations'][$totalReturnStop];
                                        $segmentRefstop = $originDestinationsstops['SegmentRef'];

                                        $segmentstop = $flightSegmentList[$segmentRefstop];
                                        $duration = $segmentstop['JourneyDuration'];
                                        $arrival = $segmentstop['ArrivalAirportLocationCode'];
                                        $artimereturn = $segmentstop['ArrivalDateTime'];
                                        $deptime = $segmentstop['DepartureDateTime'];

                                        $segmentRef = $filteredSegments[0];
                                        $segmentReturn = $flightSegmentList[$segmentRef];
                                        $segmentRefArrival = $filteredSegments[$totalReturnStop];
                                        $segmentReturnArrival = $flightSegmentList[$segmentRefArrival];
                                    }

                                    $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                    $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                                    $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                    $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
                                    $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                    $stmtairline->bindParam(':code', $code);
                                    $stmtairline->execute();
                                    $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);

                                    $class_name = "contentbar_return";
                                    if (isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "Return") {
                                        $class_name = "contentbar_return";
                                    } elseif (isset($_SESSION['search_values']['tab']) && $_SESSION['search_values']['tab'] == "OneWay") {
                                        $class_name = "contentbar_one_way";
                                    }

                                    ?>

                                    <li class="<?php echo $class_name; ?>">
                                        <ul class="form-row mb-lg-2" style="justify-content: center;align-items: center;">
                                            <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2 text-center "><span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span>
                                                <strong><?php echo $airlineLocation['name'] ?></strong>
                                            </li>
                                            <li data-th="Depart" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                                <div class="">
                                                    <strong style="font-size:16px;"><?php echo $segment['DepartureAirportLocationCode']; ?></strong>
                                                    <br>
                                                    <?php
                                                    $datetime = $segment['DepartureDateTime'];
                                                    list($date, $time) = explode("T", $datetime);
                                                    echo date("d M Y", strtotime($date)); ?>
                                                    <br>
                                                    <?php
                                                    echo $time;
                                                    ?>
                                                </div>

                                            </li>
                                            <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                                <div>

                                                    <strong>
                                                        <?php

                                                        if ($totalstop > 0) {

                                                            // echo $segment['ArrivalDateTime'];
                                                            // echo "<br />";
                                                            // echo $deptime;
                                                            // echo "<br />";

                                                            $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segment['ArrivalDateTime']);
                                                            $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                                            $interval =  $date1->diff($date2);


                                                            $hours = $interval->h;
                                                            $minutes = $interval->i;

                                                            echo $totalstop . " Stop";
                                                            // echo "<br>" . $segment['ArrivalAirportLocationCode'] . "|" . $hours . "h " . $minutes . "m";
                                                        } else
                                                            echo "Direct";

                                                        ?>
                                                    </strong>
                                                </div>

                                            </li>
                                            <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                                <div>
                                                    <?php
                                                    if ($totalstop > 0) {
                                                        $arrivallocation = $arrival;
                                                        $arrivaltime = $artime;
                                                        $datetime = $arrivaltime;
                                                        list($date, $time) = explode("T", $datetime);
                                                    } else {
                                                        $arrivallocation = $segment['ArrivalAirportLocationCode'];
                                                        $arrivaltime = $segment['ArrivalDateTime'];
                                                        $datetime = $arrivaltime;
                                                        list($date, $time) = explode("T", $datetime);
                                                    }
                                                    ?>
                                                    <strong style="font-size:16px;"><?php echo $arrivallocation; ?></strong>

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
                                        <?php
                                        if ($totalReturnStop >= 0) {
                                        ?>
                                            <p style="font-size: 14px;text-transform: uppercase;display: flex;justify-content: flex-start;align-items: center;padding: 5px 10px;background-color: #ffe4cc;">
                                                <img class="flight_icon_small_return" src="https://www.worldairfares.com/flight-icon.c157d86342ac31faa6b0.svg" />
                                                <strong>Return</strong>
                                            </p>

                                            <ul class="form-row mb-lg-2" style="justify-content: center;align-items: center;">
                                                <li data-th="Airline" class="main-dtls col-md-2 d-flex flex-column align-items-md-center justify-content-center mb-md-0 mb-2 text-center">
                                                    <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span>
                                                    <strong><?php echo $airlineLocation['name'] ?></strong>
                                                </li>
                                                <li data-th="Depart" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                                    <div class="">
                                                        <strong style="font-size:16px;"><?php echo $segmentReturn['DepartureAirportLocationCode']; ?></strong>
                                                        <br>
                                                        <?php
                                                        $datetime = $segmentReturn['DepartureDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        echo date("d M Y", strtotime($date)); ?>
                                                        <br>
                                                        <?php
                                                        echo $time;
                                                        ?>
                                                    </div>

                                                </li>
                                                <li data-th="Stops" class="main-dtls col-md-2 d-flex flex-column justify-content-between stop-dtls fs-13 mb-md-0 mb-2 text-center">
                                                    <div>
                                                        <strong>
                                                            <?php
                                                            if ($totalReturnStop > 0) {
                                                                foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                                    $originSegment = $flightSegmentList[$origins['SegmentRef']];
                                                                    // if ($origins['LegIndicator'] == 0) {
                                                                    //     $origin_total_duration += $originSegment['JourneyDuration'];
                                                                    // }
                                                                }

                                                                // echo $originSegment['ArrivalDateTime'];
                                                                $date1 = DateTime::createFromFormat("Y-m-d\TH:i:s", $segmentReturn['DepartureDateTime']);
                                                                $date2 = DateTime::createFromFormat("Y-m-d\TH:i:s", $deptime);
                                                                $interval =  $date1->diff($date2);


                                                                $hours = $interval->h;
                                                                $minutes = $interval->i;

                                                                echo $totalReturnStop . " Stop";
                                                                // echo "<br>" . $segment['ArrivalAirportLocationCode'] . "|" . $hours . "h " . $minutes . "m";
                                                            } else
                                                                echo "Direct";
                                                            ?>
                                                        </strong>
                                                    </div>
                                                </li>
                                                <li data-th="Arrive" class="main-dtls col-md-2 d-flex flex-column justify-content-between depart-dtls fs-13 mb-md-0 mb-2 text-center">
                                                    <div>
                                                        <?php
                                                        $datetime = $segmentReturnArrival['ArrivalDateTime'];
                                                        list($date, $time) = explode("T", $datetime);
                                                        ?>
                                                        <strong style="font-size:16px;"><?php echo $segmentReturnArrival['ArrivalAirportLocationCode']; ?></strong>
                                                        <br>
                                                        <?php echo date("d m Y", strtotime($date)); ?><br>
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
                                                                if ($origins['LegIndicator'] == 1) {
                                                                    $origin_total_duration += $originSegment['JourneyDuration'];
                                                                }
                                                            }
                                                            echo convertMinutesToTimeFormat($origin_total_duration);
                                                            ?>
                                                        </strong>
                                                    </div>
                                                </li>
                                            </ul>
                                        <?php
                                        }
                                        ?>

                                        <div class="form-row panel flight-details-tab-wrap" style="margin: 0px;">


                                            <div class="tab-content text-center">
                                                <div class="tab-pane p-lg-5 pt-5 p-3 pane1">
                                                    <button class="close"><span>&times;</span></button>
                                                    <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                                        <div><?php echo $airportLocation['city_name']; ?>
                                                            <span class="right-arrow-small arrow-000000"></span>
                                                            <?php if ($onestop) {
                                                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                $stmtlocation->execute(array('airport_code' => $arrival));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                            } else {
                                                                $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                                $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                            }

                                                            $datetime = $segment['DepartureDateTime'];
                                                            list($date, $time) = explode("T", $datetime);
                                                            echo $airportLocation['city_name'] . " , " . date("d F Y", strtotime($date));
                                                            ?>
                                                        </div>
                                                        <div>
                                                            <strong class="fw-500">
                                                                Total Duration:
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
                                                    </div>
                                                    <?php
                                                    foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                        $originRef = $origins['SegmentRef'];
                                                        $originSegment = $flightSegmentList[$originRef];
                                                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                        $itineryRef = $origins['ItineraryRef'];
                                                        $itinerySegment = $FlightItineraryList[$itineryRef];
                                                        if ($origins['LegIndicator'] == 0) {
                                                    ?>

                                                            <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                                                <ul class="col-lg-3 mb-3">
                                                                    <div class="text-left">
                                                                        <strong class="fw-500 d-block">
                                                                            <?php
                                                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');
                                                                            $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                                            $stmtairline->bindParam(':code', $code);
                                                                            $stmtairline->execute();
                                                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                                            echo $airlineLocation['name'];
                                                                            ?>

                                                                        </strong>
                                                                        Flight No - <?php echo ($originSegment['MarketingCarriercode'] ?? '') . ' ' . ($originSegment['MarketingFlightNumber'] ?? $originSegment['OperatingFlightNumber'] ?? 'N/A'); ?>
                                                                        <br>
                                                                        <?php echo $itinerySegment['CabinClassType'] ?>
                                                                    </div>
                                                                </ul>

                                                                <div class="col-lg-7 mb-3">

                                                                    <div class="d-flex row justify-content-between">
                                                                        <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                                            <?php
                                                                            $datetime = $originSegment['DepartureDateTime'];
                                                                            list($date, $time) = explode("T", $datetime);
                                                                            $stmtlocation->execute(array('airport_code' => $originSegment['DepartureAirportLocationCode']));
                                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                            <?php echo date("d F Y", strtotime($date)) . " ," . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                        </div>
                                                                        <div class="col-md-2 mb-md-0 mb-2">
                                                                            <div class="d-flex flex-column align-items-center">
                                                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                                                </svg>
                                                                                <?php echo convertMinutesToTimeFormat($originSegment['JourneyDuration']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-5 text-md-left">
                                                                            <?php
                                                                            $datetime = $originSegment['ArrivalDateTime'];
                                                                            list($date, $time) = explode("T", $datetime);
                                                                            // echo date("d F Y", strtotime($date));
                                                                            $stmtlocation->execute(array('airport_code' => $originSegment['ArrivalAirportLocationCode']));
                                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <strong class="fw-500 d-block"> <?php echo $time . " " . $originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                            <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                        </div>
                                                                    </div>

                                                                </div>


                                                            </div>
                                                    <?php
                                                        }
                                                    }

                                                    ?>
                                                    <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                                        <span><?php echo $airTripType; ?></span>
                                                        <strong class="fw-500">
                                                            Total Duration:
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
                                                    <?php

                                                    foreach ($pricedItinerary['OriginDestinations'] as $origins) {
                                                        $originRef = $origins['SegmentRef'];
                                                        $originSegment = $flightSegmentList[$originRef];
                                                        $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');

                                                        $itineryRef = $origins['ItineraryRef'];
                                                        $itinerySegment = $FlightItineraryList[$itineryRef];
                                                        if ($origins['LegIndicator'] == 1) {
                                                    ?>

                                                            <div class="d-flex row justify-content-between fs-15 fw-300 mb-4">
                                                                <ul class="col-lg-3 mb-3">
                                                                    <div class="text-left">
                                                                        <strong class="fw-500 d-block">
                                                                            <?php
                                                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                                            $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                                            $stmtairline->bindParam(':code', $code);
                                                                            $stmtairline->execute();
                                                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                                            // echo $pricedItinerary['ValidatingCarrier'];
                                                                            echo $airlineLocation['name'];
                                                                            ?>

                                                                        </strong>
                                                                        Flight No - <?php echo ($originSegment['MarketingCarriercode'] ?? '') . ' ' . ($originSegment['MarketingFlightNumber'] ?? $originSegment['OperatingFlightNumber'] ?? 'N/A'); ?>
                                                                        <br>
                                                                        <?php echo $itinerySegment['CabinClassType'] ?>
                                                                    </div>
                                                                </ul>

                                                                <div class="col-lg-7 mb-3">

                                                                    <div class="d-flex row justify-content-between">
                                                                        <div class="col-md-5 mb-md-0 mb-2 text-md-left">
                                                                            <?php
                                                                            $datetime = $originSegment['DepartureDateTime'];
                                                                            list($date, $time) = explode("T", $datetime);
                                                                            // echo date("d F Y", strtotime($date));
                                                                            $stmtlocation->execute(array('airport_code' => $originSegment['DepartureAirportLocationCode']));
                                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <strong class="fw-500 d-block"><?php echo $originSegment['DepartureAirportLocationCode'] . " " . $time ?></strong>
                                                                            <?php echo date("d F Y", strtotime($date)) . " ," . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                        </div>
                                                                        <div class="col-md-2 mb-md-0 mb-2">
                                                                            <div class="d-flex flex-column align-items-center">
                                                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M10 0C4.486 0 0 4.486 0 10C0 15.514 4.486 20 10 20C15.514 20 20 15.514 20 10C20 4.486 15.514 0 10 0ZM13.293 14.707L9 10.414V4H11V9.586L14.707 13.293L13.293 14.707Z" fill="#959595" />
                                                                                </svg>
                                                                                <?php echo convertMinutesToTimeFormat($originSegment['JourneyDuration']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-5 text-md-left">
                                                                            <?php
                                                                            $datetime = $originSegment['ArrivalDateTime'];
                                                                            list($date, $time) = explode("T", $datetime);
                                                                            // echo date("d F Y", strtotime($date));
                                                                            $stmtlocation->execute(array('airport_code' => $originSegment['ArrivalAirportLocationCode']));
                                                                            $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <strong class="fw-500 d-block"> <?php echo $time . " " . $originSegment['ArrivalAirportLocationCode']; ?></strong>
                                                                            <?php echo date("d F Y", strtotime($date)) . ", " . $airportLocation['airport_name'] . "," . $airportLocation['city_name'] . "," . $airportLocation['country_name'] ?>
                                                                        </div>
                                                                    </div>

                                                                </div>


                                                            </div>
                                                    <?php
                                                        }
                                                    }

                                                    ?>

                                                </div>
                                                <div class="tab-pane p-lg-5 pt-5 p-3 pane2 ">
                                                    <button class="close"><span>&times;</span></button>
                                                    <div class="row fs-13 mb-3">
                                                        <div class="col-md-12 mb-md-0 mb-3">
                                                            <ul>
                                                                <li class="d-flex justify-content-between p-1 bdr-b">
                                                                    <strong class="fs-14 fw-600">Fare Breakup <span class="fw-400">(in USD)</span></strong>
                                                                    <?php if (isset($adultCount) && $adultCount > 0) { ?>
                                                                        <span><?php echo $adultCount; ?> adult</span><?php } ?>
                                                                    <?php if (isset($childCount) && $childCount > 0) { ?>
                                                                        <span><?php echo $childCount; ?> child</span><?php } ?>
                                                                    <?php if (isset($infantCount) && $infantCount > 0) { ?>
                                                                        <span><?php echo $infantCount; ?> infant</span><?php } ?>
                                                                </li>
                                                                <?php
                                                                $totalTax = 0;
                                                                $totalAdultfare = 0;
                                                                $totalChildfare = 0;
                                                                $totalInfantfare = 0;
                                                                if (isset($adultCount) && $adultCount > 0) {
                                                                    foreach ($fareListRef['PassengerFare'][0]['TaxBreakUp'] as $taxdata) {
                                                                        $totalTax +=  $taxdata['Amount'];
                                                                    }
                                                                    // $totalAdultfare=$fareListRef['PassengerFare'][0]['BaseFare']* $adultCount;
                                                                ?>
                                                                    <!-- <li class="d-flex justify-content-between p-1"><span>Adult (<?php echo $fareListRef['PassengerFare'][0]['BaseFare'] . 'x' . $adultCount; ?>)</span><span><?php echo $totalAdultfare;  ?></span></li><?php } ?> -->
                                                                    <?php if (isset($childCount) && $childCount > 0) {
                                                                        foreach ($fareListRef['PassengerFare'][1]['TaxBreakUp'] as $taxdata) {
                                                                            $totalTax +=  $taxdata['Amount'];
                                                                        }
                                                                        // $totalChildfare=$fareListRef['PassengerFare'][1]['BaseFare']* $childCount;
                                                                    ?>
                                                                        <!-- <li class="d-flex justify-content-between p-1"><span>Child (<?php echo $fareListRef['PassengerFare'][1]['BaseFare'] . 'x' . $childCount; ?>)</span><span><?php echo $totalChildfare; ?></span></li><?php } ?> 
                                                                        <?php if (isset($infantCount) && $infantCount > 0) {
                                                                            foreach ($fareListRef['PassengerFare'][2]['TaxBreakUp'] as $taxdata) {
                                                                                $totalTax +=  $taxdata['Amount'];
                                                                            }
                                                                            // $totalinfantfare=$fareListRef['PassengerFare'][2]['BaseFare']* $infantCount;
                                                                        ?>
                                                                            <li class="d-flex justify-content-between p-1"><span>Infant (<?php //echo $fareListRef['PassengerFare'][2]['BaseFare'] . 'x' . $infantCount; 
                                                                                                                                            ?>)</span><span><?php //echo $totalinfantfare; 
                                                                                                                                                            ?></span></li><?php
                                                                                                                                                                        } ?> 
                                                                           

                                                                            <!-- <li class="d-flex justify-content-between p-1"><span>Airline Charges & Taxes</span><span><?Php echo $totalTax; ?></span></li> -->
                                                                        <!-- <li class="d-flex justify-content-between pw-500 pl-1 pr-1 bdr-t"><span>Airline Fare</span><span>43818</span></li> -->
                                                                        <!-- </ul> -->

                                                                        <!-- </li> -->
                                                                        <li class="d-flex justify-content-between bg-b1b1b1 p-1 mt-1">
                                                                            <?php
                                                                            if (isset($adultCount) && $adultCount > 0) {

                                                                                $totalAdultfare += $fareListRef['PassengerFare'][0]['TotalFare'] * $adultCount;
                                                                            }
                                                                            if (isset($childCount) && $childCount > 0) {
                                                                                $totalChildfare += $fareListRef['PassengerFare'][1]['TotalFare'] * $childCount;
                                                                            }
                                                                            if (isset($infantCount) && $infantCount > 0) {

                                                                                $totalInfantfare += $fareListRef['PassengerFare'][2]['TotalFare'] * $infantCount;
                                                                            }
                                                                            $stmtmarkup = $conn->prepare('SELECT * FROM markup_commission WHERE role_id = :role_id');
                                                                            $stmtmarkup->execute(array('role_id' => 1));
                                                                            $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);


                                                                            // $stmtmarkup->execute(array('role_id' => 1));
                                                                            // $markup = $stmtmarkup->fetch(PDO::FETCH_ASSOC);
                                                                            // $totalFareAPI=$totalAdultfare+$totalChildfare+$totalinfantfare+$totalTax;
                                                                            $totalFareAPI = $totalAdultfare + $totalChildfare + $totalInfantfare;
                                                                            $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;

                                                                            $markupPercentage += $ticketing_fee;
                                                                            $total_price = $markupPercentage + $totalFareAPI;

                                                                            // $ipg_trasaction_percentage = ($ipg_percentage / 100) * $total_price;

                                                                            // $total_price += $ipg_trasaction_percentage;


                                                                            ?>


                                                                            <strong class="fw-600">Total Fare</strong><strong>&#36; <?php echo number_format(round($total_price, 2), 2); ?></strong>
                                                                        </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <ul>
                                                                <li class="d-flex align-items-baseline p-1 bdr-b">
                                                                    <strong class="fs-14 fw-600">Fare Rules </strong>
                                                                    <?php
                                                                    // Use actual API data
                                                                    $refundAllowed = 0;
                                                                    $DateChangeAllowed = 0;
                                                                    
                                                                    if (isset($penaltyListRef['Penaltydetails'][0]['RefundAllowed'])) {
                                                                        $refundValue = $penaltyListRef['Penaltydetails'][0]['RefundAllowed'];
                                                                        $refundAllowed = ($refundValue === true || $refundValue === 1 || $refundValue === '1') ? 1 : 0;
                                                                    }
                                                                    
                                                                    if (isset($penaltyListRef['Penaltydetails'][0]['ChangeAllowed'])) {
                                                                        $changeValue = $penaltyListRef['Penaltydetails'][0]['ChangeAllowed'];
                                                                        $DateChangeAllowed = ($changeValue === true || $changeValue === 1 || $changeValue === '1') ? 1 : 0;
                                                                    }
                                                                    
                                                                    if ($refundAllowed == 1) {
                                                                    ?>
                                                                        <span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Refundable</span>
                                                                    <?php
                                                                    } else {
                                                                    ?>
                                                                        <span class="uppercase-txt dark-black-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Not Refundable</span>
                                                                    <?php
                                                                    }
                                                                    //DAte change allow or not 
                                                                    if ($DateChangeAllowed == 1) {
                                                                    ?>
                                                                        <span class="uppercase-txt dark-black-txt green-bg border-radius-5 ml-2 pl-1 pr-1">Date Change Allowed</span>
                                                                    <?php
                                                                    } else {
                                                                    ?>
                                                                        <span class="uppercase-txt dark-black-txt red-bg border-radius-5 ml-2 pl-1 pr-1"> Date Change Not Allowed</span>
                                                                    <?php
                                                                    }
                                                                    ?>

                                                                </li>
                                                                <li>
                                                                    <ul>
                                                                        <!-- Start of cancelation/REfund Penalty fee ---- -->
                                                                        <li class="d-flex justify-content-between p-1 mt-1">
                                                                            <strong class="fs-13 fw-600">Cancellation fee<span class="fw-400">(per passenger)</span></strong>
                                                                        </li>
                                                                        <li class="text-left">
                                                                            <table class="w-100">
                                                                                <tr class="bdr" id="firstRow">
                                                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee + Bulatrips fee</td>

                                                                                    <?php
                                                                                    $penalityList = $penaltyListRef['Penaltydetails'];

                                                                                    if (isset($_SESSION['user_id'])) {
                                                                                        $roleId         =   $user['role'];
                                                                                    } else {
                                                                                        $roleId         =    1;
                                                                                    }

                                                                                    if (count($penalityList) > 0) {

                                                                                        foreach ($penalityList as $k => $val) {
                                                                                            if ($val['PaxType'] == 'ADT') {

                                                                                                $passengerType = "Adult";
                                                                                            }
                                                                                            if ($val['PaxType'] == 'CHD') {
                                                                                                $passengerType = "Children";
                                                                                            }
                                                                                            if ($val['PaxType'] == 'INF') {
                                                                                                $passengerType = "Infant";
                                                                                            }
                            
                                                                            
                                                                                            $total_refund = $refund_addition_fee_setting['value'] + $refund_fee_setting['value'];

                                                                                            if (!empty($val['RefundPenaltyAmount'])) {
                                                                                                $totDisplay =   (floatval($val['RefundPenaltyAmount']) * floatval($usd_converion_rate)) + $total_refund;
                                                                                    ?>
                                                                                                <td><?php echo $passengerType . ": $ " . number_format(round($totDisplay, 2), 2); ?></td>
                                                                                            <?php
                                                                                            } else {
                                                                                            ?>
                                                                                                <td>Refundable amount is 0 from Airline</td>
                                                                                    <?php
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    ?>

                                                                                </tr>
                                                                            </table>
                                                                        </li>
                                                                        <!-- end of cancelation/REfund Penalty fee ---- -->

                                                                    </ul>
                                                                    <ul>
                                                                        <li class="d-flex justify-content-between p-1 mt-1">
                                                                            <strong class="fs-13 fw-600">Date Change fee<span class="fw-400">(per passenger)</span></strong>
                                                                            <!-- <span class="uppercase-txt">cok-dxb</span> -->
                                                                        </li>
                                                                        <li class="text-left">
                                                                            <table class="w-100">

                                                                                <tr class="bdr">
                                                                                    <td class="bg-f0f3f5 p-1" style="width: 40%;">Airline fee + Bulatrips fee </td>
                                                                                    <!-- start of date change Penalty fee ---- -->
                                                                                    <?php
                                                                                    foreach ($penalityList as $k => $val) {
                                                                                        if ($val['PaxType'] == 'ADT') {

                                                                                            $passengerType = "Adult";
                                                                                        }
                                                                                        if ($val['PaxType'] == 'CHD') {
                                                                                            $passengerType = "Children";
                                                                                        }
                                                                                        if ($val['PaxType'] == 'INF') {
                                                                                            $passengerType = "Infant";
                                                                                        }


                                                                                        if (!empty($val['ChangePenaltyAmount'])) {
                                                                                            $total_refund = $reissue_addition_fee_setting['value'] + $reissue_fee_setting['value'];
                                                                                            $totDisplay =   (floatval($val['ChangePenaltyAmount']) * floatval($usd_converion_rate)) + $total_refund;

                                                                                    //         $ipg_trasaction_percentage = ($ipg_percentage / 100) * $totDisplay;
                                                                                    //         $totDisplay += $ipg_trasaction_percentage;
                                                                                    // ?>
                                                                                    

                                                                                            <td><?php echo $passengerType . ": $ " . number_format(round($totDisplay, 2), 2); ?></td>
                                                                                        <?php
                                                                                        } else {
                                                                                        ?>
                                                                                            <td>Not Applicable</td>
                                                                                    <?php
                                                                                        }
                                                                                    }

                                                                                    ?>

                                                                                </tr>

                                                                            </table>
                                                                        </li>
                                                                        <!-- end of date change penalty info ----- -->
                                                                    </ul>
                                                        </div>
                                                    </div>
                                                    <p class="fs-13 fw-500 text-left"><strong>Note: </strong>Cancellation and flight change fees are approximate and may vary depending on the Airlines .We cannot guarantee the accuracy of this information.</p>
                                                </div>
                                                <!-- ----------------baggage details-------------------- -->
                                                <div class="tab-pane p-lg-5 pt-5 p-3 pane3">
                                                    <button class="close"><span>&times;</span></button>
                                                    <ul class="fs-13">
                                                        <li class="text-left p-1 bdr-b">
                                                            <?php
                                                            $stmtlocation = $conn->prepare('SELECT * FROM airportlocations WHERE airport_code = :airport_code');
                                                            $stmtlocation->execute(array('airport_code' => $segment['DepartureAirportLocationCode']));
                                                            $airportLocationdep = $stmtlocation->fetch(PDO::FETCH_ASSOC);

                                                            $stmtairline = $conn->prepare('SELECT * FROM airline WHERE code LIKE :code');

                                                            $code = '%' . $pricedItinerary['ValidatingCarrier'] . '%';
                                                            $stmtairline->bindParam(':code', $code);
                                                            $stmtairline->execute();
                                                            $airlineLocation = $stmtairline->fetch(PDO::FETCH_ASSOC);
                                                            if ($onestop) {
                                                                $stmtlocation->execute(array('airport_code' => $arrival));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                            } else {
                                                                $stmtlocation->execute(array('airport_code' => $segment['ArrivalAirportLocationCode']));
                                                                $airportLocation = $stmtlocation->fetch(PDO::FETCH_ASSOC);
                                                            }
                                                            ?>

                                                            <?php echo $airportLocationdep['city_name'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $airportLocation['city_name'] ?>
                                                        </li>
                                                        <?php
                                                        //fetching baggage information
                                                        foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                                            $baggageRef = $baggages['ItineraryRef'];
                                                            $baggageSegment = $FlightItineraryList[$baggageRef];
                                                            $originRef = $baggages['SegmentRef'];
                                                            $originSegment = $flightSegmentList[$originRef];
                                                            if ($baggages['LegIndicator'] == 0) {
                                                        ?>
                                                                <li class="">
                                                                    <ul class="row align-items-center pt-3 pb-3">
                                                                        <li class="col-md-1 mb-md-0 mb-2">
                                                                            <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier'] ?>"></span>
                                                                            <!-- <li class="col-md-1 mb-md-0 mb-2 airImg airline-<?php echo $pricedItinerary['ValidatingCarrier'] ?>">
                                                                        <span class="airImg airline-<?php echo $pricedItinerary['ValidatingCarrier']; ?>"></span> -->



                                                                        </li>
                                                                        <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                                            <strong>
                                                                                <?php
                                                                                echo $airlineLocation['name'];
                                                                                ?>
                                                                            </strong>
                                                                            <span class="uppercase-txt"><?php echo $originSegment['DepartureAirportLocationCode'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $originSegment['ArrivalAirportLocationCode'] ?></span>
                                                                        </li>
                                                                        <li class="col-md-7">
                                                                            <ul class="row bdr-b">
                                                                                <li class="col-4">Checkin</li>
                                                                                <li class="col-4">1 pcs/person</li>
                                                                                <li class="col-4"><?php if (strtolower($baggageSegment['CheckinBaggage'][0]['Value']) == "sb") {
                                                                                                        echo "Standard Baggage";
                                                                                                    } else {
                                                                                                        echo $baggageSegment['CheckinBaggage'][0]['Value'];
                                                                                                    } ?></li>
                                                                            </ul>
                                                                            <ul class="row">
                                                                                <li class="col-4">Cabin</li>
                                                                                <li class="col-4">1 pcs/person</li>
                                                                                <li class="col-4"><?php if (strtolower($baggageSegment['CabinBaggage'][0]['Value']) == "sb") {
                                                                                                        echo "Standard Baggage";
                                                                                                    } else {
                                                                                                        echo $baggageSegment['CabinBaggage'][0]['Value'];
                                                                                                    } ?></li>
                                                                            </ul>
                                                                        </li>
                                                                    </ul>

                                                                </li>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                        <div class="d-flex justify-content-md-between flex-md-row flex-column fs-15 fw-300 mb-4">
                                                            <?php if (!empty($searchValue['to'])) { ?>
                                                                <span>Return</span>
                                                            <?php   } ?>
                                                        </div>
                                                        <?php
                                                        foreach ($pricedItinerary['OriginDestinations'] as $baggages) {
                                                            $baggageRef = $baggages['ItineraryRef'];
                                                            $baggageSegment = $FlightItineraryList[$baggageRef];
                                                            $originRef = $baggages['SegmentRef'];
                                                            $originSegment = $flightSegmentList[$originRef];
                                                            if ($baggages['LegIndicator'] == 1) {
                                                        ?>
                                                                <li class="">
                                                                    <ul class="row align-items-center pt-3 pb-3">
                                                                        <li class="col-md-1 mb-md-0 mb-2">
                                                                            <span class="airImg airline-<?php echo $airlineLocation['code'] ?>"></span>



                                                                        </li>
                                                                        <li class="col-md-2 flex-column text-left mb-md-0 mb-2">
                                                                            <strong>
                                                                                <?php
                                                                                echo $airlineLocation['name'];
                                                                                ?>
                                                                            </strong>
                                                                            <span class="uppercase-txt"><?php echo $originSegment['DepartureAirportLocationCode'] ?> <span class="right-arrow-small arrow-000000"></span> <?php echo $originSegment['ArrivalAirportLocationCode'] ?></span>
                                                                        </li>
                                                                        <li class="col-md-7">
                                                                            <ul class="row bdr-b">
                                                                                <li class="col-4">Checkin</li>
                                                                                <li class="col-4">1 pcs/person</li>
                                                                                <li class="col-4"><?php if (strtolower($baggageSegment['CheckinBaggage'][0]['Value']) == "sb") {
                                                                                                        echo "Standard Baggage";
                                                                                                    } else {
                                                                                                        echo $baggageSegment['CheckinBaggage'][0]['Value'];
                                                                                                    } ?></li>
                                                                            </ul>
                                                                            <ul class="row">
                                                                                <li class="col-4">Cabin</li>
                                                                                <li class="col-4">1 pcs/person</li>
                                                                                <li class="col-4"><?php if (strtolower($baggageSegment['CabinBaggage'][0]['Value']) == "sb") {
                                                                                                        echo "Standard Baggage";
                                                                                                    } else {
                                                                                                        echo $baggageSegment['CabinBaggage'][0]['Value'];
                                                                                                    } ?></li>
                                                                            </ul>
                                                                        </li>
                                                                    </ul>

                                                                </li>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                    <p class="fs-13 fw-500 text-left"><strong>Note: </strong>The information provided above is as retrieved from the airline reservation system. Bulatrips does not guarantee the authenticity of this information. The baggage allowance may vary according to stop-overs, connecting flights and changes in airline rules. Customer is adviced to verify the same from the airline directly before departure.</p>
                                                </div>

                                            </div>

                                            <ul class="nav nav-tabs d-flex w-100 main_flight_details_container" style="background: #6b7c93; border:0;align-items: center; z-index:9;justify-content: space-evenly;">
                                                <li class="nav-item">
                                                    <a class="nav-link">
                                                        <span class="detail-icon" style="font-size: 15px;">✈️</span>Flight Details
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link">
                                                        <span class="detail-icon" style="font-size: 15px;">💼</span> Fare Details
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link">
                                                        <span class="detail-icon" style="font-size: 15px;">📦</span>Baggage Details
                                                    </a>
                                                </li>

                                                <li class="nav-item">
                                                    <?php
                                                    $totalAdultfare = 0;
                                                    $totalChildfare = 0;
                                                    $totalInfantfare = 0;
                                                    if (isset($adultCount) && $adultCount > 0) {

                                                        $totalAdultfare += $fareListRef['PassengerFare'][0]['TotalFare'] * $adultCount;
                                                    }
                                                    if (isset($childCount) && $childCount > 0) {
                                                        $totalChildfare += $fareListRef['PassengerFare'][1]['TotalFare'] * $childCount;
                                                    }
                                                    if (isset($infantCount) && $infantCount > 0) {
                                                        $totalInfantfare += $fareListRef['PassengerFare'][2]['TotalFare'] * $infantCount;
                                                    }

                                                    $totalFareAPI = $totalAdultfare + $totalChildfare + $totalInfantfare;
                                                    $markupPercentage = ($markup['commission_percentage'] / 100) * $totalFareAPI;
                                                    $markupPercentage += $ticketing_fee;
                                                    $total_price = $markupPercentage + $totalFareAPI;
                                                    // $ipg_trasaction_percentage = ($ipg_percentage / 100) * $total_price;
                                                    // $total_price += $ipg_trasaction_percentage;
                                                    ?>


                                                    <form action="my-booking-step1" method="post" style="margin-top:4px;">
                                                        <input type="hidden" id="fscode" name="fscode" value="<?php echo $pricedItinerary['FareSourceCode']; ?>">
                                                        <button type="button" onclick="makeSessionFsCode(this,'<?php echo $pricedItinerary['FareSourceCode']; ?>')" class="btn btn-typ7 w-100 mb-2" style="font-weight: bold;font-size: 16px;">
                                                            $<?php echo number_format(round($total_price, 2), 2); ?> | <span class="book_now_text"> &nbsp;BOOK NOW </span>
                                                        </button>
                                                    </form>

                                                </li>

                                            </ul>

                                        </div>
                                    </li>


                                </ul>
                            </div>

                        </div>
                    <?php
                    }

                    ?>
                </div>

                <div class="pagination-bottom w-100 p-4">
                    <?php
                    // Build query string preserving filter parameters
                    $queryParams = [];
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'page') {
                            $queryParams[$key] = $value;
                        }
                    }
                    
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $activeClass = ($i == $page) ? 'active' : '';
                        if ($i == $page) {
                            $activeUrl = 'javascript:void(0);';
                        } else {
                            $queryParams['page'] = $i;
                            $activeUrl = '?' . http_build_query($queryParams);
                        }
                        echo '<a href="' . htmlspecialchars($activeUrl) . '" class="' . $activeClass . ' mx-1">' . $i . '</a>';
                    }
                    ?>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" id="errorModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeButton">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center" id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="closeButton1" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                        <!-- <button type="button" class="btn btn-typ3 fs-15 fw-600 pl-4 pr-4">Search Again</button> -->
                    </div>
                </div>
            </div>
        </div>
        <!--  Login Modal -->
        <?php
        require_once("includes/login-modal.php");
        ?>
        <!--  forgot Modal -->
        <?php
        require_once("includes/forgot-modal.php");
        include_once('loading-popup.php');
        ?>

        <!-- Fare Selection Modal -->
        <div class="modal fade" id="fareSelectionModal" tabindex="-1" role="dialog" aria-labelledby="fareSelectionModalLabel" aria-hidden="true" style="z-index: 9999;">
            <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
                <div class="modal-content">
                    <div class="modal-header" style="background: #007bff; color: white;">
                        <h5 class="modal-title" id="fareSelectionModalLabel">
                            ✈️ SELECT FARES FOR YOUR TRIP
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 30px;">

                        <!-- Departure Section -->
                        <div class="departure-section mb-4">
                            <h6 style="font-weight: 600; margin-bottom: 15px; color: #007bff;">
                                ✈️ DEPARTURE: <span id="dep-route"></span><span id="dep-date-wrapper"></span>
                            </h6>
                            <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                                <span id="dep-airline"></span> • <span id="dep-time"></span> • <span id="dep-duration"></span>
                            </p>

                            <div id="departure-fares" class="fare-options">
                                <!-- Fare options will be populated dynamically -->
                            </div>
                        </div>

                        <?php if (strtolower($airTripType) === 'return'): ?>
                        <!-- Divider for Return -->
                        <hr style="margin: 30px 0; border-top: 2px solid #ddd;">

                        <!-- Return Section (only for Return trips) -->
                        <div class="return-section mb-4">
                            <h6 style="font-weight: 600; margin-bottom: 15px; color: #007bff;">
                                ✈️ RETURN: <span id="ret-route"></span><span id="ret-date-wrapper"></span>
                            </h6>
                            <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
                                <span id="ret-airline"></span> • <span id="ret-time"></span> • <span id="ret-duration"></span>
                            </p>

                            <div id="return-fares" class="fare-options">
                                <!-- Fare options will be populated dynamically -->
                            </div>
                        </div>

                        <!-- Divider after Return -->
                        <hr style="margin: 30px 0; border-top: 2px solid #ddd;">
                        <?php endif; ?>

                        <!-- Total Price Section -->
                        <div class="total-price-section" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 style="margin: 0; font-weight: 600;">TOTAL PRICE:</h5>
                                <h4 style="margin: 0; color: #28a745; font-weight: bold;" id="total-price-display">$0.00</h4>
                            </div>
                            <p style="margin: 10px 0 0 0; font-size: 13px; color: #666;" id="selected-fares-text">
                                <?php echo (strtolower($airTripType) === 'return') ? '(Select fares for both legs)' : '(Select fare for departure)'; ?>
                            </p>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            ← Back to Results
                        </button>
                        <button type="button" class="btn btn-primary" id="continue-to-booking-btn" disabled>
                            Continue to Booking →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Popup Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true" style="z-index: 9999;">
            <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
                <div class="modal-content">
                    <div class="modal-header" style="background: #ffc107; color: #333;">
                        <h5 class="modal-title" id="confirmationModalLabel">
                            ⚠️ CONFIRM YOUR BOOKING
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #333;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 30px;">
                        
                        <!-- Total Price -->
                        <div class="text-center mb-4">
                            <h4 style="font-weight: bold; color: #28a745;">
                                TOTAL PRICE: <span id="conf-total-price">$0.00</span>
                            </h4>
                        </div>

                        <hr style="margin: 30px 0; border-top: 2px solid #ddd;">

                        <!-- Departure Section -->
                        <div class="departure-confirmation mb-4">
                            <h6 style="font-weight: 600; margin-bottom: 15px; color: #007bff;">
                                ✈️ DEPARTURE: <span id="conf-dep-route"></span> (<span id="conf-dep-date"></span>)
                            </h6>
                            <p style="margin-bottom: 10px; color: #666; font-size: 14px;">
                                <span id="conf-dep-airline"></span> • <span id="conf-dep-time"></span>
                            </p>
                            <p style="margin-bottom: 15px; font-size: 14px; color: #333;">
                                <strong>Fare:</strong> <span id="conf-dep-fare-name"></span> - $<span id="conf-dep-fare-price"></span> (<span id="conf-dep-fare-type"></span> - <span id="conf-dep-refundable-status"></span>)
                            </p>

                            <!-- Included Features -->
                            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <strong style="color: #155724;">✅ INCLUDED:</strong>
                                <ul id="conf-dep-included" style="margin: 10px 0 0 20px; padding: 0;">
                                    <!-- Will be populated dynamically -->
                                </ul>
                            </div>

                            <!-- Not Included Features -->
                            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <strong style="color: #721c24;">❌ NOT INCLUDED:</strong>
                                <ul id="conf-dep-not-included" style="margin: 10px 0 0 20px; padding: 0;">
                                    <!-- Will be populated dynamically -->
                                </ul>
                            </div>

                            <!-- Refundable/Change Info -->
                            <div style="background: #fff3cd; padding: 12px; border-radius: 5px; border-left: 4px solid #ffc107;">
                                <strong style="color: #856404;">⚠️</strong> 
                                <span id="conf-dep-refundable-warning"></span>
                                <span id="conf-dep-change-fee"></span>
                            </div>
                        </div>

                        <?php if (strtolower($airTripType) === 'return'): ?>
                        <hr style="margin: 30px 0; border-top: 2px solid #ddd;">

                        <!-- Return Section -->
                        <div class="return-confirmation mb-4">
                            <h6 style="font-weight: 600; margin-bottom: 15px; color: #007bff;">
                                ✈️ RETURN: <span id="conf-ret-route"></span> (<span id="conf-ret-date"></span>)
                            </h6>
                            <p style="margin-bottom: 10px; color: #666; font-size: 14px;">
                                <span id="conf-ret-airline"></span> • <span id="conf-ret-time"></span>
                            </p>
                            <p style="margin-bottom: 15px; font-size: 14px; color: #333;">
                                <strong>Fare:</strong> <span id="conf-ret-fare-name"></span> - $<span id="conf-ret-fare-price"></span> (<span id="conf-ret-fare-type"></span> - <span id="conf-ret-refundable-status"></span>)
                            </p>

                            <!-- Included Features -->
                            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <strong style="color: #155724;">✅ INCLUDED:</strong>
                                <ul id="conf-ret-included" style="margin: 10px 0 0 20px; padding: 0;">
                                    <!-- Will be populated dynamically -->
                                </ul>
                            </div>

                            <!-- Not Included Features -->
                            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                                <strong style="color: #721c24;">❌ NOT INCLUDED:</strong>
                                <ul id="conf-ret-not-included" style="margin: 10px 0 0 20px; padding: 0;">
                                    <!-- Will be populated dynamically -->
                                </ul>
                            </div>

                            <!-- Refundable/Change Info -->
                            <div style="background: #fff3cd; padding: 12px; border-radius: 5px; border-left: 4px solid #ffc107;">
                                <strong style="color: #856404;">⚠️</strong> 
                                <span id="conf-ret-refundable-warning"></span>
                                <span id="conf-ret-change-fee"></span>
                            </div>
                        </div>

                        <hr style="margin: 30px 0; border-top: 2px solid #ddd;">
                        <?php endif; ?>

                        <!-- Mandatory Checkbox -->
                        <div class="form-check mb-4" style="background: #e7f3ff; padding: 15px; border-radius: 5px; border: 2px solid #b3d9ff;">
                            <input class="form-check-input" type="checkbox" id="confirm-checkbox" style="margin-top: 5px; width: 20px; height: 20px;">
                            <label class="form-check-label" for="confirm-checkbox" style="margin-left: 10px; font-size: 15px; font-weight: 500;">
                                I understand each leg's fare conditions
                            </label>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="change-fares-btn">
                            ← Change Fares
                        </button>
                        <button type="button" class="btn btn-primary" id="continue-to-payment-btn" disabled>
                            Continue to Payment →
                        </button>
                    </div>
                </div>
            </div>
        </div>

<?php
}
function minutesToHoursMinutes($minutes)
{
    // Calculate the hours
    $hours = floor($minutes / 60);

    // Calculate the remaining minutes
    $remainingMinutes = $minutes % 60;

    // Return the result as a string
    return sprintf("%d:%02d", $hours, $remainingMinutes);
}
require_once("includes/footer.php");


?>
<script>
    /************Datepicker******************/
    $(function() {
        var dateFormat = "mm/dd/yy";
        
        // Function to properly position datepicker on both mobile and desktop
        function positionDatepicker(input, inst) {
            var isSmallScreen = window.innerWidth <= 767;
            
            setTimeout(function() {
                var $input = $(input);
                var $datepicker = $('#ui-datepicker-div');
                var inputOffset = $input.offset();
                var inputHeight = $input.outerHeight();
                var inputWidth = $input.outerWidth();
                var datepickerHeight = $datepicker.outerHeight();
                var datepickerWidth = $datepicker.outerWidth();
                var windowHeight = $(window).height();
                var windowWidth = $(window).width();
                var scrollTop = $(window).scrollTop();
                
                // Calculate position relative to viewport
                var inputTop = inputOffset.top - scrollTop;
                var inputLeft = inputOffset.left;
                var spaceBelow = windowHeight - (inputTop + inputHeight);
                var spaceAbove = inputTop;
                
                var top, left;
                
                if (isSmallScreen) {
                    // Mobile positioning - fixed and centered
                    if (spaceBelow >= datepickerHeight || spaceBelow > spaceAbove) {
                        top = inputTop + inputHeight + 5;
                    } else {
                        top = inputTop - datepickerHeight - 5;
                    }
                    
                    if (top < 10) top = 10;
                    if (top + datepickerHeight > windowHeight - 10) {
                        top = windowHeight - datepickerHeight - 10;
                    }
                    
                    left = (windowWidth - datepickerWidth) / 2;
                    if (left < 10) left = 10;
                    
                    $datepicker.css({
                        position: 'fixed',
                        top: top + 'px',
                        left: left + 'px',
                        right: 'auto',
                        bottom: 'auto'
                    });
                } else {
                    // Desktop positioning - absolute, aligned with input field
                    if (spaceBelow >= datepickerHeight) {
                        // Show below input
                        top = inputOffset.top + inputHeight + 2;
                    } else if (spaceAbove >= datepickerHeight) {
                        // Show above input
                        top = inputOffset.top - datepickerHeight - 2;
                    } else {
                        // Not enough space, show below anyway
                        top = inputOffset.top + inputHeight + 2;
                    }
                    
                    // Align with input field
                    left = inputLeft;
                    
                    // Ensure datepicker doesn't go off screen horizontally
                    if (left + datepickerWidth > windowWidth) {
                        left = windowWidth - datepickerWidth - 10;
                    }
                    if (left < 10) left = 10;
                    
                    $datepicker.css({
                        position: 'absolute',
                        top: top + 'px',
                        left: left + 'px',
                        right: 'auto',
                        bottom: 'auto'
                    });
                }
            }, 0);
        }
        
        var from = $("#from")
            .datepicker({
                //defaultDate: "+1w",
                changeMonth: true,
                minDate: 0,
                beforeShow: function(input, inst) {
                    positionDatepicker(input, inst);
                }
            })
            .on("change", function() {
                to.datepicker("option", "minDate", getDate(this));
            }),
            to = $("#to").datepicker({
                //defaultDate: "+1w",
                changeMonth: true,
                beforeShow: function(input, inst) {
                    positionDatepicker(input, inst);
                }
            })
            .on("change", function() {
                from.datepicker("option", "maxDate", getDate(this));
            });

        function getDate(element) {
            var date;
            try {
                date = $.datepicker.parseDate(dateFormat, element.value);
            } catch (error) {
                date = null;
            }

            return date;
        }
        
        // Re-position datepicker on window resize or scroll
        $(window).on('resize scroll', function() {
            if ($('#ui-datepicker-div').is(':visible')) {
                var $activeInput = $('#from, #to').filter(function() {
                    return $(this).is(':focus') || $(this).hasClass('hasDatepicker') && $('#ui-datepicker-div').is(':visible');
                });
                if ($activeInput.length) {
                    positionDatepicker($activeInput[0], null);
                }
            }
        });
    });


    $(function() {
        $(".date-multy-city").datepicker({
            dateFormat: "D, M d",
            minDate: 0
        });
    });
    /*****************************************/

    $(document).ready(function() {
        const airTripType = "<?php echo $airTripType; ?>";
        if (airTripType === 'Return') {
            $('#return').prop('checked', true);
        } else if (airTripType === 'OneWay') {
            $('#one-way').prop('checked', true);
        }

        const $flightCountElement = $('#flight-count');
        const originalFlightCount = Number($flightCountElement.data('original')) || $('.flight-card').length;
        $flightCountElement.data('original', originalFlightCount);
        
        // Auto-open filters and modify search when 0 flights
        const visibleFlights = $('.flight-card').length;
        if (visibleFlights === 0) {
            // Open modify search section
            //$('#modify-search-result').show();
            
            // Open filters panel
            $('#filter-panel').slideDown();
            $('#filter-toggle-icon').text('▲');
            
            // Scroll to modify search for better visibility
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $('.midbar-wrapper-inner').offset().top - 20
                }, 500);
            }, 300);
        }

        $('.select-class').select2();
        $('.stops-select').select2();
        $('.price-select').select2();
        $('.opt-select').select2();
        $('.airline-select').select2();
        $('.dep-time-select').select2();
        $('.ret-time-select').select2();
        
        // Initialize airlines filter Select2
        $('#airlines-filter').select2({
            placeholder: 'Select airlines...',
            allowClear: true,
            closeOnSelect: true,
            width: '100%'
        });
        
        // Update filter count when airlines selection changes
        $('#airlines-filter').on('change', function() {
            updateFilterCount();
        });

        // $('[name=tab]').each(function(i,d){
        //     var p = $(this).prop('checked');
        //     //   console.log(p);
        //     if(p){
        //         $('.search-box').eq(i)
        //         .addClass('on');
        //     }    
        // });  

        // $('[name=tab]').on('change', function(){
        //     var p = $(this).prop('checked');

        //     // $(type).index(this) == nth-of-type
        //     var i = $('[name=tab]').index(this);

        //     $('.search-box').removeClass('on');
        //     $('.search-box').eq(i).addClass('on');
        // });

        $('.flight-search > input').click(function() {
            if ($('#return').is(':checked')) {
                $("#to").show().next(".icon").show()
            } else(
                $("#to").hide().next(".icon").hide()
            )
            if ($('#multi-city').is(':checked')) {
                $(".search-box.multi-city-search").css("display", "flex").siblings().hide()

            } else(
                $(".search-box.multi-city-search").hide().siblings().show()
            )
        })
        if ($('#return').is(':checked')) {
            $("#to").show().next(".icon").show()
        } else(
            $("#to").hide().next(".icon").hide()
        )
        if ($('#multi-city').is(':checked')) {
            $(".search-box.multi-city-search").css("display", "flex").siblings().hide()

        } else(
            $(".search-box.multi-city-search").hide().siblings().show()
        )

        // $('#multi-city').click(function() {
        //     $(".search-box.multi-city-search").show()
        //     $(".multi-city-search").siblings(".search-box").hide();
        // });


        $(".select-lbl").click(function() {
            $(this).parent(".person-select").toggleClass("open");
            $(".select-dropbox").toggle();
        })


        $('.add').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepUp();
        })
        $('.minus').on('click', function() {
            this.parentNode.querySelector('input[type=number]').stepDown();
        })

        /******************TAB WITHOUT ID*******************************/
        $('.panel .nav-tabs').on('click', 'a', function(e) {
            var tab = $(this).parent(),
                tabIndex = tab.index(),
                tabPanel = $(this).closest('.panel'),
                tabPane = tabPanel.find('.tab-pane').eq(tabIndex);

            // Check if the clicked tab is already active
            if (tab.hasClass('active')) {
                // If it's already active, hide it by removing the 'active' class
                tab.removeClass('active');
                tabPane.removeClass('active');
            } else {
                // If it's not active, show it by adding the 'active' class
                tabPanel.find('.active').removeClass('active'); // Remove 'active' class from all tabs and panels
                tab.addClass('active');
                tabPane.addClass('active');
            }
        });



        $('.tab-pane').on('click', 'button', function(e) {
            $(this).parent(".tab-pane").removeClass("active");
            $(this).parents(".tab-content").siblings(".nav-tabs").children(".nav-item").removeClass("active");
        });
        /***************************************************************/
    });

    $(".text-below-button").click(function() {
        $(this).parents('.modal').modal('hide');
    });
    $(".forgot-passward > button").click(function() {
        $(this).parents('.modal').modal('hide');
    });

    // ============ FILTER TOGGLE FUNCTIONALITY ============
    $('#filter-toggle-btn').on('click', function() {
        const filterContainer = $('#filter-container');
        const toggleText = $('#filter-toggle-text');
        const toggleIcon = $('#filter-toggle-icon');
        
        if (filterContainer.is(':visible')) {
            // Hide filters
            filterContainer.slideUp(300);
            toggleIcon.text('▼');
            $(this).css({
                'background': 'transparent',
                'color': '#007bff'
            });
        } else {
            // Show filters
            filterContainer.slideDown(300);
            toggleIcon.text('▲');
            $(this).css({
                'background': '#007bff',
                'color': 'white'
            });
        }
    });

    // ============ MODERN FILTER ITEM TOGGLE ============
    // Function to update filter count badge
    function updateFilterCount() {
        let count = $('.filter-checkbox:checked').length;
        
        // Add airline filter count if any airlines are selected
        const airlinesSelected = $('#airlines-filter').val();
        if (airlinesSelected && airlinesSelected.length > 0) {
            count += 1; // Count as 1 filter even if multiple airlines selected
        }
        
        const badge = $('#filter-count-badge');
        if (count > 0) {
            badge.text(count).show();
        } else {
            badge.hide();
        }
    }

    // Handle filter item clicks (toggle without showing checkbox)
    $('.filter-item').on('click', function() {
        const filterId = $(this).data('filter-id');
        const checkbox = $('#' + filterId);
        
        // Toggle checkbox
        checkbox.prop('checked', !checkbox.prop('checked'));
        
        // FIXED: Mutual exclusion between checked baggage and cabin-only filters
        if (checkbox.is(':checked')) {
            if (filterId === 'filter-checked-baggage') {
                // If checked baggage is selected, uncheck cabin-only
                const cabinOnlyCheckbox = $('#filter-cabin-only');
                const cabinOnlyItem = $('[data-filter-id="filter-cabin-only"]');
                cabinOnlyCheckbox.prop('checked', false);
                cabinOnlyItem.removeClass('active');
                cabinOnlyItem.css({
                    'background': 'white',
                    'border-color': '#e9ecef',
                    'box-shadow': '0 1px 3px rgba(0,0,0,0.05)'
                });
                cabinOnlyItem.find('span').css('color', '#495057');
            } else if (filterId === 'filter-cabin-only') {
                // If cabin-only is selected, uncheck checked baggage
                const checkedBaggageCheckbox = $('#filter-checked-baggage');
                const checkedBaggageItem = $('[data-filter-id="filter-checked-baggage"]');
                checkedBaggageCheckbox.prop('checked', false);
                checkedBaggageItem.removeClass('active');
                checkedBaggageItem.css({
                    'background': 'white',
                    'border-color': '#e9ecef',
                    'box-shadow': '0 1px 3px rgba(0,0,0,0.05)'
                });
                checkedBaggageItem.find('span').css('color', '#495057');
            }
        }
        
        // Toggle active class for visual feedback
        if (checkbox.is(':checked')) {
            $(this).addClass('active');
            $(this).css({
                'background': '#e7f3ff',
                'border-color': '#007bff',
                'box-shadow': '0 2px 6px rgba(0,123,255,0.15)'
            });
            $(this).find('span').css('color', '#007bff');
        } else {
            $(this).removeClass('active');
            $(this).css({
                'background': 'white',
                'border-color': '#e9ecef',
                'box-shadow': '0 1px 3px rgba(0,0,0,0.05)'
            });
            $(this).find('span').css('color', '#495057');
        }
        
        // Don't update filter count badge here - only update when Apply Filters is clicked
    });

    // Update hover behavior for active items
    $('.filter-item').on('mouseenter', function() {
        if ($(this).hasClass('active')) {
            $(this).css({
                'background': '#d6ebff',
                'box-shadow': '0 3px 8px rgba(0,123,255,0.2)'
            });
        }
    }).on('mouseleave', function() {
        if ($(this).hasClass('active')) {
            $(this).css({
                'background': '#e7f3ff',
                'box-shadow': '0 2px 6px rgba(0,123,255,0.15)'
            });
        }
    });

    // Update filter count badge on page load
    updateFilterCount();
    
    // Form submit handler - filters will be applied via GET request
    $('#flight-filters-form').on('submit', function(e) {
        // Form will submit naturally with GET method
        // Reset page to 1 when filters change
        if ($(this).find('input[name="page"]').length === 0) {
            $('<input>').attr({
                type: 'hidden',
                name: 'page',
                value: '1'
            }).appendTo(this);
        } else {
            $(this).find('input[name="page"]').val('1');
        }
    });

    // ============ FARE SELECTION & CONFIRMATION MODAL JAVASCRIPT ============
    // Store flight data for modal - Make globally accessible (legacy support)
    window.flightData = <?php echo json_encode($pricedItineraries); ?>;
    window.flightSegments = <?php echo json_encode($responseData['Data']['FlightSegmentList']); ?>;
    window.flightFares = <?php echo json_encode($responseData['Data']['FlightFaresList']); ?>;
    window.penaltiesInfo = <?php echo json_encode($responseData['Data']['PenaltiesInfoList']); ?>;
    window.itineraryRefs = <?php echo json_encode($responseData['Data']['ItineraryReferenceList']); ?>;
    window.airTripType = "<?php echo $airTripType; ?>";
    
    // Keep track of last-used data so we can reopen the same selection
    window.lastFareOptions = null;
    window.lastSegments = null;
    window.lastFareButton = null;
    window.currentFlightIndex = null;

    // Backwards-compatible wrapper:
    // If called with a DOM element, delegate to the new implementation.
    // If called with an index (legacy), try to reuse last-known data.
    window.openFareSelectionModal = function(arg) {
        try {
            // New flow: button element with data attributes
            if (arg && typeof arg === 'object' && typeof arg.getAttribute === 'function') {
                window.lastFareButton = arg;
                // Delegate to new implementation defined later in the file
                if (typeof openFareSelectionModal === 'function') {
                    return openFareSelectionModal(arg);
                }
            }

            // Legacy flow support (index-based). If we have cached data, reuse it.
            if (window.lastFareOptions && window.lastSegments) {
                populateDepartureFares(window.lastFareOptions, window.lastSegments);
                $('#fareSelectionModal').modal('show');
                return;
            }

            // As a final fallback, we cannot safely reconstruct branded fares from the
            // legacy structures without heavy logic. Fail gracefully.
            console.warn('Fallback: no cached fare options available to reopen modal.');
            alert('Please re-open the fares from the results list.');
        } catch (e) {
            console.error('Error in legacy openFareSelectionModal wrapper:', e);
        }
    }
        // (legacy design block removed entirely)

    // Function to select fare (updates border styling) - Make globally accessible
    window.selectFare = function(leg, fareType, price, fareName) {
        // Check the radio button
        const radioId = leg + '-fare-' + fareType;
        $('#' + radioId).prop('checked', true).trigger('change');
        
        // Update border styling for selected fare
        $('.' + leg + '-fare-radio').closest('.fare-option').css('border-color', '#ddd');
        $('#' + radioId).closest('.fare-option').css('border-color', '#007bff');
        
        // Use new updateTotalPrice function defined later
        if (typeof updateTotalPrice === 'function') {
            updateTotalPrice();
        }
    };

    // Old updateTotalPrice function removed - using new one defined later in the file

    // Handle continue to booking button - Show Confirmation Modal
    $('#continue-to-booking-btn').off('click').on('click', function() {
        const depFare = $('input[name="dep-fare"]:checked');
        const retFare = window.airTripType === 'Return' ? $('input[name="ret-fare"]:checked') : null;
        
        // Get selected fare data (design only - will use real API data later)
        const selectedData = {
            dep: {
                fareName: depFare.data('name') || depFare.val().toUpperCase(),
                farePrice: depFare.data('price') || 160.00,
                fareType: 'Public',
                refundable: depFare.val() === 'flex' ? 'Refundable' : 'Non-Refundable',
                changeFee: '$50 fee'
            },
            ret: retFare ? {
                fareName: retFare.data('name') || retFare.val().toUpperCase(),
                farePrice: retFare.data('price') || 155.20,
                fareType: 'Public',
                refundable: retFare.val() === 'flex' ? 'Refundable' : 'Non-Refundable',
                changeFee: '$50 fee'
            } : null
        };

        // Calculate total
        let total = parseFloat(selectedData.dep.farePrice);
        if (selectedData.ret) {
            total += parseFloat(selectedData.ret.farePrice);
        }

        // Populate confirmation modal (design only - sample data)
        $('#conf-total-price').text('$' + total.toFixed(2));
        
        // Departure details
        $('#conf-dep-route').text(document.getElementById('dep-route').textContent);
        const depDateWrapper = document.getElementById('dep-date-wrapper');
        $('#conf-dep-date').text(depDateWrapper ? depDateWrapper.textContent : '');
        $('#conf-dep-airline').text(document.getElementById('dep-airline').textContent);
        $('#conf-dep-time').text(document.getElementById('dep-time').textContent);
        $('#conf-dep-fare-name').text(selectedData.dep.fareName);
        $('#conf-dep-fare-price').text(selectedData.dep.farePrice);
        $('#conf-dep-fare-type').text(selectedData.dep.fareType);
        $('#conf-dep-refundable-status').text(selectedData.dep.refundable);
        
        // Sample included/not included (will be from API later)
        const depIncluded = selectedData.dep.fareName === 'LITE' ? 
            '<li>Cabin bag (7kg)</li><li>Seat assignment at check-in</li>' : 
            '<li>1 Checked bag (23kg)</li><li>Cabin bag (7kg)</li><li>Seat assignment at check-in</li>';
        const depNotIncluded = selectedData.dep.fareName === 'LITE' ?
            '<li>Checked baggage</li><li>Advance seat selection</li><li>Meals</li>' :
            '<li>Advance seat selection</li><li>Meals</li>';
        $('#conf-dep-included').html(depIncluded);
        $('#conf-dep-not-included').html(depNotIncluded);
        $('#conf-dep-refundable-warning').text(selectedData.dep.refundable.toUpperCase());
        $('#conf-dep-change-fee').text(' • Changes: ' + selectedData.dep.changeFee);

        // Return details (if return trip)
        if (window.airTripType === 'Return' && selectedData.ret) {
            $('#conf-ret-route').text(document.getElementById('ret-route').textContent);
            const retDateWrapper = document.getElementById('ret-date-wrapper');
            $('#conf-ret-date').text(retDateWrapper ? retDateWrapper.textContent : '');
            $('#conf-ret-airline').text(document.getElementById('ret-airline').textContent);
            $('#conf-ret-time').text(document.getElementById('ret-time').textContent);
            $('#conf-ret-fare-name').text(selectedData.ret.fareName);
            $('#conf-ret-fare-price').text(selectedData.ret.farePrice);
            $('#conf-ret-fare-type').text(selectedData.ret.fareType);
            $('#conf-ret-refundable-status').text(selectedData.ret.refundable);
            
            const retIncluded = selectedData.ret.fareName === 'LITE' ? 
                '<li>Cabin bag (7kg)</li><li>Seat assignment at check-in</li>' : 
                '<li>1 Checked bag (23kg)</li><li>Cabin bag (7kg)</li><li>Seat assignment at check-in</li>';
            const retNotIncluded = selectedData.ret.fareName === 'LITE' ?
                '<li>Checked baggage</li><li>Advance seat selection</li><li>Meals</li>' :
                '<li>Advance seat selection</li><li>Meals</li>';
            $('#conf-ret-included').html(retIncluded);
            $('#conf-ret-not-included').html(retNotIncluded);
            $('#conf-ret-refundable-warning').text(selectedData.ret.refundable.toUpperCase());
            $('#conf-ret-change-fee').text(' • Changes: ' + selectedData.ret.changeFee);
        }

        // Hide fare selection modal and show confirmation modal
        $('#fareSelectionModal').modal('hide');
        
        // Reset checkbox
        $('#confirm-checkbox').prop('checked', false);
        $('#continue-to-payment-btn').prop('disabled', true);
        
        // Show confirmation modal after a short delay
        setTimeout(function() {
            $('#confirmationModal').modal('show');
        }, 300);
    });

    // Handle confirmation checkbox
    $('#confirm-checkbox').off('change').on('change', function() {
        if ($(this).is(':checked')) {
            $('#continue-to-payment-btn').prop('disabled', false);
        } else {
            $('#continue-to-payment-btn').prop('disabled', true);
        }
    });

    // Handle Change Fares button
    $('#change-fares-btn').off('click').on('click', function() {
        $('#confirmationModal').modal('hide');
        setTimeout(function() {
            // Prefer using the cached data from the last selection
            if (window.lastFareOptions && window.lastSegments) {
                try {
                    populateDepartureFares(window.lastFareOptions, window.lastSegments);
                    $('#fareSelectionModal').modal('show');
                    return;
                } catch (e) {
                    console.error('Error reopening fare selection with cached data:', e);
                }
            }
            // Fallback to last button or legacy index wrapper
            if (window.lastFareButton) {
                window.openFareSelectionModalV2(window.lastFareButton);
            } else if (window.currentFlightIndex !== null) {
                window.openFareSelectionModal(window.currentFlightIndex);
            }
        }, 300);
    });

    // Handle Continue to Payment button (design only)
    $('#continue-to-payment-btn').off('click').on('click', function() {
        // Store selected fare data in session/localStorage for payment page
        // Then redirect to payment page
        // For now, redirect to existing booking flow
        window.location.href = "fligtsRulesRevalidation";
    });
    // ============ END FARE SELECTION & CONFIRMATION MODAL JAVASCRIPT ============

    $('#FlightSearchLoading').modal({
        show: false
    })
    /**************Scroll To Top*****************/
    $(window).on('scroll', function() {
        if (window.scrollY > window.innerHeight) {
            $('#scrollToTop').addClass('active')
        } else {
            $('#scrollToTop').removeClass('active')
        }
    })

    $('#scrollToTop').on('click', function() {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    })
    /**********************************************/


    // Define the showLoadingPopup() and hideLoadingPopup() functions
    // function showLoadingPopup() {
    //     $('#FlightSearchLoading').modal({
    //             show:true
    //         })
    // }
    // $('#FlightSearchLoading').modal('show');
    // window.addEventListener('load', function() {
    //   // Display the loading popup
    //   alert("hiiiiiii tesssss");
    //   $('#FlightSearchLoading').modal('show');

    //   // Add a delay before hiding the loading popup
    //   setTimeout(function() {
    //     $('#FlightSearchLoading').modal('hide');
    //   }, 3000); // 3000 milliseconds = 3 seconds (adjust the delay time as needed)
    // });

    $(document).ready(function() {
        <?php
        // if(empty($responseData['Data']['IsValid'])) {
        if (isset($responseData['Data']['Errors'])) {
            //  echo '$("#errorMessage").text("' . $responseData['Message'] . '");';
            // echo "$('#errorModal').modal('show');";
        ?>
            // var errorMessage = <?php echo json_encode($responseData['Message']); ?>;
            // window.location.href = '404.php?error=' + encodeURIComponent(errorMessage);
        <?php
        }

        ?>
        var errorMessage = <?php echo json_encode($responseData['Message']); ?>; // Encode PHP message to JavaScript variable

        function redirectToErrorPage(message) {
            $('#errorModal').modal('hide');
            window.location.href = '404.php?error=' + encodeURIComponent(errorMessage);
        }

        $('#closeButton, #closeButton1').click(redirectToErrorPage);

    });

    // When button with ID 'modify-search-result-btn' is clicked
    $('#modify-search-result-btn').click(function() {
        $('#modify-search-result').slideToggle();
        // Toggle dull gray state while modify panel is open
        $(this).toggleClass('modify-btn-active');
    });

    // Helper: highlight Search button after any modification
    function markModifySearchChanged() {
        $('#modify-search-submit').addClass('search-highlight');
    }

    // Any change inside modify-search form should highlight Search button
    $('#modify-search-result').on('change', 'input, select', function (e) {
        // ignore the Search submit itself
        if (this.id === 'modify-search-submit') return;
        markModifySearchChanged();
    });

    // Plus/minus clicks for passenger counts
    $('#modify-search-result').on('click', '.add, .minus', function () {
        markModifySearchChanged();
    });

    $('.select-class').select2();
    $(document).ready(function() {
        preSelectedValue1 = "<?php echo $airport_code_chosesn; ?>";
        console.log(preSelectedValue1);
        var select2 = $('.airport_location_finder_depature').select2({
            placeholder: 'Search for an Airport Location',
            ajax: {
                url: 'includes/airport_location_finder',
                type: 'GET',
                dataType: 'json',
                delay: 500,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data
                }),
                cache: true
            }
        });

        $.getJSON('includes/airport_location_finder', {
            q: preSelectedValue1
        }, data => {
            const result = data.find(item => item.id === preSelectedValue1);
            if (result) {
                select2.append(new Option(result.text, result.id, true, true)).trigger('change');
            }
        });


        var preSelectedValue2 = "<?php echo $airportDestinationLocation['airport_code']; ?>";
        console.log(preSelectedValue2);
        var select = $('.airport_location_finder_arrival').select2({
            placeholder: 'Search for an Airport Location',
            ajax: {
                url: 'includes/airport_location_finder',
                type: 'GET',
                dataType: 'json',
                delay: 500,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data
                }),
                cache: true
            }
        });

        $.getJSON('includes/airport_location_finder', {
            q: preSelectedValue2
        }, data => {
            const result = data.find(item => item.id === preSelectedValue2);
            if (result) {
                select.append(new Option(result.text, result.id, true, true)).trigger('change');
            }
        });



    });

    function deleteUserDataCookie(cookieName) {
        document.cookie =
            cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }

    function makeSessionFsCode(_this, fs_code) {
        // Card se details extract karo
        const $card = $(_this).closest('.flight-card');
        const $flightContainer = $(_this).closest('.light-border');
        
        // Data attributes se filter info
        const isRefundable = $card.data('refundable') == '1' || $card.data('refundable') == 1;
        const isDateChangeAllowed = $card.data('date-change') == '1' || $card.data('date-change') == 1;
        const hasCheckedBaggage = $card.data('checked-baggage') == '1' || $card.data('checked-baggage') == 1;
        const isCabinOnly = $card.data('cabin-only') == '1' || $card.data('cabin-only') == 1;
        const hasCabin = $card.data('has-cabin') == '1' || $card.data('has-cabin') == 1;
        
        // Get actual baggage values (Departure) - using empty string as default
        let checkedBaggageValue = $card.data('checked-baggage-value');
        checkedBaggageValue = (checkedBaggageValue === undefined || checkedBaggageValue === null) ? '' : String(checkedBaggageValue);
        
        let cabinBaggageValue = $card.data('cabin-baggage-value');
        cabinBaggageValue = (cabinBaggageValue === undefined || cabinBaggageValue === null) ? '' : String(cabinBaggageValue);
        
        // Get return baggage values (if round trip)
        const hasReturnCheckedBaggage = $card.data('return-checked-baggage') == '1' || $card.data('return-checked-baggage') == 1;
        const hasReturnCabinBaggage = $card.data('return-has-cabin') == '1' || $card.data('return-has-cabin') == 1;
        
        let returnCheckedBaggageValue = $card.data('return-checked-baggage-value');
        returnCheckedBaggageValue = (returnCheckedBaggageValue === undefined || returnCheckedBaggageValue === null) ? '' : String(returnCheckedBaggageValue);
        
        let returnCabinBaggageValue = $card.data('return-cabin-baggage-value');
        returnCabinBaggageValue = (returnCabinBaggageValue === undefined || returnCabinBaggageValue === null) ? '' : String(returnCabinBaggageValue);
        
        // Debug logging
        console.log('Baggage Debug:', {
            card: $card,
            departure: {
                checked: checkedBaggageValue,
                cabin: cabinBaggageValue,
                hasChecked: hasCheckedBaggage,
                hasCabin: hasCabin
            },
            return: {
                checked: returnCheckedBaggageValue,
                cabin: returnCabinBaggageValue,
                hasChecked: hasReturnCheckedBaggage,
                hasCabin: hasReturnCabinBaggage
            },
            allDataAttributes: $card.data()
        });
        
        // Check if round trip - check if there's a return section in the DOM or if return baggage values exist
        const $returnSection = $flightContainer.find('p:contains("Return")');
        const isRoundTrip = $returnSection.length > 0 || returnCheckedBaggageValue !== '' || returnCabinBaggageValue !== '' || hasReturnCheckedBaggage || hasReturnCabinBaggage;
        
        // Flight details extract karo
        let airlineName = '';
        const $airlineElement = $flightContainer.find('li[data-th="Airline"] strong');
        if ($airlineElement.length > 0) {
            airlineName = $airlineElement.first().text().trim();
        }
        
        // Departure and Arrival info
        let departureInfo = '';
        let arrivalInfo = '';
        const $departElements = $flightContainer.find('li[data-th="Depart"]');
        const $arriveElements = $flightContainer.find('li[data-th="Arrive"]');
        
        if ($departElements.length > 0) {
            const depCode = $departElements.find('strong').first().text().trim();
            departureInfo = depCode;
        }
        
        if ($arriveElements.length > 0) {
            const arrCode = $arriveElements.find('strong').first().text().trim();
            arrivalInfo = arrCode;
        }
        
        // Total price extract karo
        const buttonText = $(_this).text();
        const priceMatch = buttonText.match(/\$[\d,]+\.?\d*/);
        const totalPrice = priceMatch ? priceMatch[0] : 'N/A';
        
        // Fare Rules extract karo
        let fareRules = [];
        if (isRefundable) {
            fareRules.push('✅ Refundable');
        } else {
            fareRules.push('❌ Not Refundable');
        }
        
        if (isDateChangeAllowed) {
            fareRules.push('✅ Date Change Allowed');
        } else {
            fareRules.push('❌ Date Change Not Allowed');
        }
        
        // Baggage info - Show actual values with departure and return sections
        let baggageInfoHTML = '';
        
        // Departure Baggage Section - Build as column
        let departureHTML = '<div style="flex: 1;"><strong style="color: #007bff; font-size: 13px; display: block; margin-bottom: 6px;">✈️ Departure</strong>';
        
        // Departure Checked Baggage - Always show value if available (even if 0KG)
        console.log('Departure Checked Value:', checkedBaggageValue, 'Type:', typeof checkedBaggageValue, 'Length:', checkedBaggageValue.length);
        
        let depCheckedBag = '';
        let depCheckedIcon = '❌';
        let depCheckedColor = '#dc3545';
        
        if (checkedBaggageValue !== null && checkedBaggageValue !== undefined && checkedBaggageValue !== '') {
            // Show actual value (including 0KG, 0PC, etc.)
            const upperValue = String(checkedBaggageValue).toUpperCase();
            const isZeroBaggage = upperValue === '0KG' || upperValue === '0PC' || upperValue === '0';
            
            if (isZeroBaggage) {
                // CLIENT REQUIREMENT: When 0KG/0PC, show "not Included"
                depCheckedBag = 'Checked Baggage not Included';
                depCheckedIcon = '❌';
                depCheckedColor = '#dc3545';
            } else {
                // Show actual value (Standard Baggage, 1PC, 20KG, etc.)
                depCheckedBag = 'Checked Baggage: ' + checkedBaggageValue;
                depCheckedIcon = '✅';
                depCheckedColor = '#28a745';
            }
            console.log('Dep Checked: Using value -', checkedBaggageValue, 'isZero:', isZeroBaggage);
        } else if (hasCheckedBaggage) {
            depCheckedBag = 'Checked Baggage Included';
            depCheckedIcon = '✅';
            depCheckedColor = '#28a745';
            console.log('Dep Checked: Using flag - hasCheckedBaggage:', hasCheckedBaggage);
        } else {
            depCheckedBag = 'Checked Baggage not Included';
            depCheckedIcon = '❌';
            depCheckedColor = '#dc3545';
            console.log('Dep Checked: No baggage');
        }
        departureHTML += '<div style="display: flex; align-items: center; margin-bottom: 4px; padding: 5px; background: white; border-radius: 5px;"><span style="font-size: 14px; margin-right: 6px;">' + depCheckedIcon + '</span><span style="color: ' + depCheckedColor + '; font-weight: 500; font-size: 12px;">' + depCheckedBag + '</span></div>';
        
        // Departure Cabin Baggage - Always show value if available
        console.log('Departure Cabin Value:', cabinBaggageValue, 'Type:', typeof cabinBaggageValue, 'Length:', cabinBaggageValue.length);
        
        let depCabinBag = '';
        let depCabinIcon = '❌';
        let depCabinColor = '#dc3545';
        
        if (cabinBaggageValue !== null && cabinBaggageValue !== undefined && cabinBaggageValue !== '') {
            const upperValue = String(cabinBaggageValue).toUpperCase();
            const isZeroBaggage = upperValue === '0KG' || upperValue === '0PC' || upperValue === '0';
            
            if (isZeroBaggage) {
                // CLIENT REQUIREMENT: When 0KG/0PC, show "not included"
                depCabinBag = 'Cabin Baggage not included';
                depCabinIcon = '❌';
                depCabinColor = '#dc3545';
            } else {
                // Show actual value (Standard Baggage, 7KG, etc.)
                depCabinBag = 'Cabin Baggage: ' + cabinBaggageValue;
                depCabinIcon = '🎒';
                depCabinColor = '#28a745';
            }
            console.log('Dep Cabin: Using value -', cabinBaggageValue, 'isZero:', isZeroBaggage);
        } else if (hasCabin) {
            depCabinBag = 'Cabin Baggage Available';
            depCabinIcon = '🎒';
            depCabinColor = '#28a745';
            console.log('Dep Cabin: Using flag - hasCabin:', hasCabin);
        } else {
            depCabinBag = 'Cabin Baggage not included';
            depCabinIcon = '❌';
            depCabinColor = '#dc3545';
            console.log('Dep Cabin: No baggage');
        }
        departureHTML += '<div style="display: flex; align-items: center; margin-bottom: 4px; padding: 5px; background: white; border-radius: 5px;"><span style="font-size: 14px; margin-right: 6px;">' + depCabinIcon + '</span><span style="color: ' + depCabinColor + '; font-weight: 500; font-size: 12px;">' + depCabinBag + '</span></div>';
        
        departureHTML += '</div>';
        
        // Return Baggage Section (if round trip) - Build as column
        let returnHTML = '';
        if (isRoundTrip) {
            console.log('IS ROUND TRIP - Showing return section');
            returnHTML = '<div style="flex: 1;"><strong style="color: #28a745; font-size: 13px; display: block; margin-bottom: 6px;">🔄 Return</strong>';
            
            // Return Checked Baggage - Always show value if available (even if 0KG)
            console.log('Return Checked Value:', returnCheckedBaggageValue, 'Type:', typeof returnCheckedBaggageValue, 'Length:', returnCheckedBaggageValue.length);
            
            let retCheckedBag = '';
            let retCheckedIcon = '❌';
            let retCheckedColor = '#dc3545';
            
            if (returnCheckedBaggageValue !== null && returnCheckedBaggageValue !== undefined && returnCheckedBaggageValue !== '') {
                // Show actual value (including 0KG, 20KG, etc.)
                const upperValue = String(returnCheckedBaggageValue).toUpperCase();
                const isZeroBaggage = upperValue === '0KG' || upperValue === '0PC' || upperValue === '0';
                
                if (isZeroBaggage) {
                    // CLIENT REQUIREMENT: When 0KG/0PC, show "not Included"
                    retCheckedBag = 'Checked Baggage not Included';
                    retCheckedIcon = '❌';
                    retCheckedColor = '#dc3545';
                } else {
                    // Show actual value (Standard Baggage, 1PC, 20KG, etc.)
                    retCheckedBag = 'Checked Baggage: ' + returnCheckedBaggageValue;
                    retCheckedIcon = '✅';
                    retCheckedColor = '#28a745';
                }
                console.log('Ret Checked: Using value -', returnCheckedBaggageValue, 'isZero:', isZeroBaggage);
            } else if (hasReturnCheckedBaggage) {
                retCheckedBag = 'Checked Baggage Included';
                retCheckedIcon = '✅';
                retCheckedColor = '#28a745';
                console.log('Ret Checked: Using flag - hasReturnCheckedBaggage:', hasReturnCheckedBaggage);
            } else {
                retCheckedBag = 'Checked Baggage not Included';
                retCheckedIcon = '❌';
                retCheckedColor = '#dc3545';
                console.log('Ret Checked: No baggage');
            }
            returnHTML += '<div style="display: flex; align-items: center; margin-bottom: 4px; padding: 5px; background: white; border-radius: 5px;"><span style="font-size: 14px; margin-right: 6px;">' + retCheckedIcon + '</span><span style="color: ' + retCheckedColor + '; font-weight: 500; font-size: 12px;">' + retCheckedBag + '</span></div>';
            
            // Return Cabin Baggage - Always show value if available
            console.log('Return Cabin Value:', returnCabinBaggageValue, 'Type:', typeof returnCabinBaggageValue, 'Length:', returnCabinBaggageValue.length);
            
            let retCabinBag = '';
            let retCabinIcon = '❌';
            let retCabinColor = '#dc3545';
            
            if (returnCabinBaggageValue !== null && returnCabinBaggageValue !== undefined && returnCabinBaggageValue !== '') {
                const upperValue = String(returnCabinBaggageValue).toUpperCase();
                const isZeroBaggage = upperValue === '0KG' || upperValue === '0PC' || upperValue === '0';
                
                if (isZeroBaggage) {
                    // CLIENT REQUIREMENT: When 0KG/0PC, show "not included"
                    retCabinBag = 'Cabin Baggage not included';
                    retCabinIcon = '❌';
                    retCabinColor = '#dc3545';
                } else {
                    // Show actual value (Standard Baggage, 7KG, etc.)
                    retCabinBag = 'Cabin Baggage: ' + returnCabinBaggageValue;
                    retCabinIcon = '🎒';
                    retCabinColor = '#28a745';
                }
                console.log('Ret Cabin: Using value -', returnCabinBaggageValue, 'isZero:', isZeroBaggage);
            } else if (hasReturnCabinBaggage) {
                retCabinBag = 'Cabin Baggage Available';
                retCabinIcon = '🎒';
                retCabinColor = '#28a745';
                console.log('Ret Cabin: Using flag - hasReturnCabinBaggage:', hasReturnCabinBaggage);
            } else {
                retCabinBag = 'Cabin Baggage not included';
                retCabinIcon = '❌';
                retCabinColor = '#dc3545';
                console.log('Ret Cabin: No baggage');
            }
            returnHTML += '<div style="display: flex; align-items: center; margin-bottom: 4px; padding: 5px; background: white; border-radius: 5px;"><span style="font-size: 14px; margin-right: 6px;">' + retCabinIcon + '</span><span style="color: ' + retCabinColor + '; font-weight: 500; font-size: 12px;">' + retCabinBag + '</span></div>';
            
            returnHTML += '</div>';
        } else {
            console.log('NOT ROUND TRIP - Hiding return section');
        }
        
        // Combine departure and return in side-by-side layout
        baggageInfoHTML = departureHTML + returnHTML;
        
        // SweetAlert confirmation dialog with improved GUI
        Swal.fire({
            title: '',
            html: `
                <div style="text-align: left; padding: 0;">
                    <!-- Header Section -->
                    <div style="background: #0000FF; padding: 10px 15px; border-radius: 15px 15px 0 0; margin: 0; color: white;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Booking Summary</h3>
                                <p style="margin: 2px 0 0 0; opacity: 0.9; font-size: 11px;">Please review your flight details</p>
                            </div>
                            <div style="background: rgba(255,255,255,0.2); padding: 6px; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 20px;">✈️</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Wrapper -->
                    <div style="padding: 12px;">
                    
                    <!-- Row 1: Flight Details & Fare Rules (Side by Side) -->
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <!-- Flight Details Card (50%) -->
                        <div style="flex: 1; background: #f8f9fa; border-left: 4px solid #007bff; padding: 8px; border-radius: 8px;">
                            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                <div style="background: #007bff; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 14px;">
                                    ✈️
                                </div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 700;">Flight Details</h4>
                            </div>
                            <div style="padding-left: 36px;">
                                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                    <span style="color: #6c757d; min-width: 70px; font-size: 12px;">Airline:</span>
                                    <span style="color: #2c3e50; font-weight: 600; font-size: 12px;">${airlineName || 'N/A'}</span>
                                </div>
                                ${departureInfo ? `
                                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                    <span style="color: #6c757d; min-width: 70px; font-size: 12px;">Departure:</span>
                                    <span style="color: #2c3e50; font-weight: 600; font-size: 12px;">${departureInfo}</span>
                                </div>
                                ` : ''}
                                ${arrivalInfo ? `
                                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                    <span style="color: #6c757d; min-width: 70px; font-size: 12px;">Arrival:</span>
                                    <span style="color: #2c3e50; font-weight: 600; font-size: 12px;">${arrivalInfo}</span>
                                </div>
                                ` : ''}
                                <div style="display: flex; align-items: center; margin-top: 6px; padding-top: 6px; border-top: 2px solid #dee2e6;">
                                    <span style="color: #6c757d; min-width: 70px; font-size: 12px;">Total Price:</span>
                                    <span style="color: #28a745; font-weight: 700; font-size: 16px;">${totalPrice}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fare Rules Card (50%) -->
                        <div style="flex: 1; background: #f8f9fa; border-left: 4px solid #6c757d; padding: 8px; border-radius: 8px;">
                            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                <div style="background: #6c757d; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 14px;">
                                    💼
                                </div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 700;">Fare Rules</h4>
                            </div>
                            <div style="padding-left: 36px;">
                                ${fareRules.map(rule => {
                                    const isPositive = rule.includes('✅');
                                    return `
                                    <div style="display: flex; align-items: center; margin-bottom: 4px; padding: 5px; background: white; border-radius: 5px;">
                                        <span style="font-size: 14px; margin-right: 6px;">${isPositive ? '✅' : '❌'}</span>
                                        <span style="color: ${isPositive ? '#28a745' : '#dc3545'}; font-weight: 500; font-size: 12px;">${rule.replace(/✅|❌/g, '').trim()}</span>
                                    </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                    
                    ${baggageInfoHTML ? `
                    <!-- Row 2: Baggage Information with Departure & Return Side by Side -->
                    <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 8px; border-radius: 8px; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <div style="background: #0ea5e9; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 14px;">
                                🎒
                            </div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700;">Baggage Information</h4>
                        </div>
                        <div style="display: flex; gap: 10px; padding-left: 36px;">
                            ${baggageInfoHTML}
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Warning Box -->
                    <div style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); padding: 8px 10px; border-radius: 8px; border: 2px solid #f39c12;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 18px; margin-right: 8px;">⚠️</span>
                            <p style="margin: 0; color: #856404; font-weight: 600; font-size: 12px;">
                                Please review all details before confirming your booking.
                            </p>
                        </div>
                    </div>
                    </div>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<span style="font-weight: 600; font-size: 14px;">Confirm, proceed to traveller details</span>',
            cancelButtonText: '<span style="font-weight: 600; font-size: 16px;">✕ Cancel</span>',
            confirmButtonColor: '#F57C00',
            cancelButtonColor: '#6c757d',
            width: '650px',
            padding: '0px',
            customClass: {
                popup: 'booking-confirmation-popup',
                title: 'swal-title-custom',
                confirmButton: 'swal-confirm-button-custom',
                cancelButton: 'swal-cancel-button-custom'
            },
            buttonsStyling: true,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // User ne confirm kiya, ab actual booking action perform karo
                $(".full-page-spinner").show();
                $(_this).attr("disabled", true);
                $(_this).find("span.book_now_text").html("&nbsp;<i class='fas fa-spinner fa-spin' style='font-size:20px;'></i>");

                if (fs_code) {
                    $.ajax({
                        url: "includes/ajax",
                        type: "POST",
                        data: {
                            "fs_code": fs_code,
                            // Persist summary/banner values from flights page into session
                            "banner_airline": airlineName || '',
                            "banner_dep": departureInfo || '',
                            "banner_arr": arrivalInfo || '',
                            "banner_refundable": isRefundable ? 1 : 0,
                            "banner_date_change": isDateChangeAllowed ? 1 : 0,
                            // Persist baggage values (dep/ret)
                            "dep_checked": checkedBaggageValue || '',
                            "dep_cabin": cabinBaggageValue || '',
                            "ret_checked": returnCheckedBaggageValue || '',
                            "ret_cabin": returnCabinBaggageValue || ''
                        },
                        success: function(response) {
                            $(_this).attr("disabled", false);
                            $(_this).find("span.book_now_text").html("&nbsp; BOOK NOW");

                            deleteUserDataCookie("infantData");
                            deleteUserDataCookie("contactDetailsData");
                            deleteUserDataCookie("childData");
                            deleteUserDataCookie("adultsData");
                            deleteUserDataCookie("step_traveller_details_added");

                            window.location.href = "fligtsRulesRevalidation";
                        },
                        error: function() {
                            $(_this).attr("disabled", false);
                            $(_this).find("span.book_now_text").html("&nbsp; BOOK NOW");
                            $(".full-page-spinner").hide();
                            Swal.fire({
                                title: "Error",
                                text: "An error occurred while processing your booking. Please try again.",
                                icon: "error",
                                confirmButtonText: "Close",
                                confirmButtonColor: "#f57c00",
                            });
                        }
                    });
                } else {
                    $(_this).attr("disabled", false);
                    $(_this).find("span.book_now_text").html("&nbsp; BOOK NOW");
                    $(".full-page-spinner").hide();
                    Swal.fire({
                        title: "Fare Source Code not found",
                        text: "Fare Source Code is required to proceed further.",
                        icon: "error",
                        confirmButtonText: "Close",
                        confirmButtonColor: "#f57c00",
                    });
                }
            } else {
                // User ne cancel kiya - kuch nahi karna, bas dialog close ho jayega
            }
        });
    }

    // ============================================
    // FARE SELECTION MODAL - DYNAMIC POPULATION
    // ============================================
    
    window.selectedFares = {
        departure: null,
        return: null
    };

    function openFareSelectionModalV2(button) {
        try {
            console.log('Button clicked:', button);
            
            // Get data from button
            const fareOptionsStr = button.getAttribute('data-fare-options');
            const segmentsStr = button.getAttribute('data-flight-segments');
            
            console.log('Raw data - Fare Options String:', fareOptionsStr);
            console.log('Raw data - Segments String:', segmentsStr);
            
            if (!fareOptionsStr || fareOptionsStr === 'null' || fareOptionsStr === '[]') {
                console.error('Missing or empty fare options data');
                alert('Error: No fare options available for this flight.');
                return;
            }
            
            if (!segmentsStr || segmentsStr === 'null' || segmentsStr === '[]') {
                console.error('Missing or empty segments data');
                alert('Error: Flight segment information is missing.');
                return;
            }
            
            let fareOptions, segments;
            
            try {
                fareOptions = JSON.parse(fareOptionsStr);
                segments = JSON.parse(segmentsStr);
                // Cache for reuse (Change Fares flow)
                window.lastFareOptions = fareOptions;
                window.lastSegments = segments;
                window.lastFareButton = button;
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Fare Options String:', fareOptionsStr);
                console.error('Segments String:', segmentsStr);
                alert('Error parsing flight data. Please refresh and try again.');
                return;
            }
            
            console.log('Parsed Fare Options:', fareOptions);
            console.log('Parsed Segments:', segments);
            
            // Validate data
            if (!Array.isArray(fareOptions) || fareOptions.length === 0) {
                console.error('Invalid fare options array');
                alert('No fare options available for this flight.');
                return;
            }
            
            if (!Array.isArray(segments) || segments.length === 0) {
                console.error('Invalid segments array');
                alert('Flight segment information is incomplete.');
                return;
            }
            
            // Separate departure and return fares
            const departureFares = fareOptions;
            
            // Extract return fare options if available
            const returnFares = [];
            const returnSegments = [];
            const airTripType = '<?php echo $airTripType; ?>';
            
            if (airTripType !== 'OneWay') {
                // Try to find return leg information from fare options
                // Each fare option should have return_fare_family if it's from a return trip
                const returnFareMap = {};
                
                // Collect unique return fare families with complete information
                fareOptions.forEach(function(fare) {
                    if (fare.return_itinerary_ref !== null && fare.return_itinerary_ref !== undefined) {
                        // Get return leg details from ItineraryReferenceList
                        // ItineraryReferenceList is an array, so we need to find by ItineraryRef
                        let returnItineraryRefData = null;
                        if (window.itineraryRefs && Array.isArray(window.itineraryRefs)) {
                            const found = window.itineraryRefs.find(function(ref) {
                                return ref && (ref.ItineraryRef === fare.return_itinerary_ref || ref.ItineraryRef == fare.return_itinerary_ref);
                            });
                            if (found) {
                                returnItineraryRefData = found;
                            }
                        }
                        
                        const returnFareFamilyKey = fare.return_fare_family || 'Standard';
                        
                        if (!returnFareMap[returnFareFamilyKey]) {
                            returnFareMap[returnFareFamilyKey] = {
                                fare_family: returnFareFamilyKey,
                                fare_source_code: fare.fare_source_code, // Use departure fare source code for now
                                return_itinerary_ref: fare.return_itinerary_ref,
                                price: fare.price, // Per-leg price (already divided by 2 in PHP for return trips)
                                currency: fare.currency,
                                fare_type: fare.fare_type,
                                checked_baggage: returnItineraryRefData ? (returnItineraryRefData.CheckinBaggage || []) : [],
                                cabin_baggage: returnItineraryRefData ? (returnItineraryRefData.CabinBaggage || []) : []
                            };
                        }
                    }
                });
                
                // Convert map to array
                Object.keys(returnFareMap).forEach(function(key) {
                    returnFares.push(returnFareMap[key]);
                });
                
                // Find return segments from fare options that have return leg info
                if (fareOptions.length > 0) {
                    // Try to get return leg info from the first fare option
                    const firstFare = fareOptions[0];
                    if (firstFare.return_leg_info && Array.isArray(firstFare.return_leg_info)) {
                        firstFare.return_leg_info.forEach(function(legInfo) {
                            if (legInfo.SegmentRef !== null && window.flightSegments && window.flightSegments[legInfo.SegmentRef]) {
                                returnSegments.push(window.flightSegments[legInfo.SegmentRef]);
                            }
                        });
                    }
                }
            }
            
            // Reset selections BEFORE populating
            window.selectedFares = { departure: null, return: null };
            
            // Build modal content
            populateDepartureFares(departureFares, segments);
            
            // Populate return section if return trip
            if (airTripType !== 'OneWay' && returnSegments.length > 0 && returnFares.length > 0) {
                populateReturnFares(returnFares, returnSegments);
            } else if (airTripType !== 'OneWay') {
                // Initialize return section with placeholder
                if ($('#ret-route').text().trim() === '' || $('#ret-route').text().trim() === 'Select return flight') {
                    $('#ret-route').text('Select return flight');
                    $('#ret-date-wrapper').hide();
                    $('#ret-airline').text('No return flight selected');
                    $('#ret-time').text('Select return flight from results');
                    $('#ret-duration').text('');
                }
            }
            
            // Initialize total price display
            updateTotalPrice();
            
            // Show modal
            $('#fareSelectionModal').modal('show');
            
            // Trigger change events for any pre-selected radio buttons (if any)
            setTimeout(function() {
                $('input[name="departureFare"]:checked').trigger('change');
                $('input[name="returnFare"]:checked').trigger('change');
            }, 100);
            
        } catch (error) {
            console.error('Error opening fare selection modal:', error);
            console.error('Error stack:', error.stack);
            alert('Error loading fare options: ' + error.message + '\n\nPlease refresh and try again.');
        }
    }
    
    function populateDepartureFares(fares, segments) {
        try {
            let html = '';
            
            // Update flight info with safe property access
            if (segments && Array.isArray(segments) && segments.length > 0) {
                const firstSeg = segments[0];
                
                if (firstSeg && typeof firstSeg === 'object') {
                    const depAirport = firstSeg.DepartureAirportLocationCode || firstSeg.departureAirportLocationCode || 'N/A';
                    const arrAirport = firstSeg.ArrivalAirportLocationCode || firstSeg.arrivalAirportLocationCode || 'N/A';
                    const airlineCode = firstSeg.MarketingCarriercode || firstSeg.marketingCarriercode || '';
                    const flightNum = firstSeg.MarketingFlightNumber || firstSeg.marketingFlightNumber || '';
                    const depDateTime = firstSeg.DepartureDateTime || firstSeg.departureDateTime || '';
                    const arrDateTime = firstSeg.ArrivalDateTime || firstSeg.arrivalDateTime || '';
                    const journeyDuration = firstSeg.JourneyDuration || firstSeg.journeyDuration || 0;
                    
                    $('#dep-route').text(depAirport + ' → ' + arrAirport);
                    $('#dep-airline').text(airlineCode + flightNum);
                    
                    // Format times and date
                    let depDateText = '';
                    if (depDateTime) {
                        try {
                            const depTime = new Date(depDateTime).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
                            const arrTime = new Date(arrDateTime).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
                            $('#dep-time').text(depTime + ' → ' + arrTime);
                            
                            depDateText = new Date(depDateTime).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                            if (depDateText) {
                                $('#dep-date-wrapper').text(' (' + depDateText + ')').show();
                            } else {
                                $('#dep-date-wrapper').hide();
                            }
                        } catch (dateError) {
                            console.error('Date parsing error:', dateError);
                            $('#dep-time').text('Time not available');
                            $('#dep-date-wrapper').hide();
                            depDateText = '';
                        }
                    } else {
                        $('#dep-time').text('Time not available');
                        $('#dep-date-wrapper').hide();
                        depDateText = '';
                    }
                    
                    if (journeyDuration && journeyDuration > 0) {
                        const hours = Math.floor(journeyDuration / 60);
                        const minutes = journeyDuration % 60;
                        $('#dep-duration').text(hours + 'h ' + (minutes > 0 ? minutes + 'm' : ''));
                    } else {
                        // DST-safe: sum segment JourneyDuration instead of browser date-diff
                        try {
                            const totalMins = (segments || []).reduce(function(total, seg) {
                                const jd = seg && (seg.JourneyDuration || seg.journeyDuration || 0);
                                return total + (Number.isFinite(jd) ? jd : 0);
                            }, 0);
                            if (totalMins > 0) {
                                const hours = Math.floor(totalMins / 60);
                                const minutes = totalMins % 60;
                                $('#dep-duration').text(hours + 'h ' + (minutes > 0 ? minutes + 'm' : ''));
                            } else {
                                $('#dep-duration').text('Duration not available');
                            }
                        } catch (e) {
                            $('#dep-duration').text('Duration not available');
                        }
                    }
                } else {
                    console.error('Invalid segment structure:', firstSeg);
                    $('#dep-route').text('N/A → N/A');
                    $('#dep-airline').text('N/A');
                    $('#dep-time').text('N/A');
                    $('#dep-duration').text('N/A');
                    $('#dep-date-wrapper').hide();
                }
            } else {
                console.error('No valid segments found');
                $('#dep-route').text('N/A → N/A');
                $('#dep-airline').text('N/A');
                $('#dep-time').text('N/A');
                $('#dep-duration').text('N/A');
                $('#dep-date-wrapper').hide();
            }
        
        // Build fare options DOM safely (no template literals)
        const $depContainer = $('#departure-fares');
        $depContainer.empty();
        
        // Get passenger counts
        const adultCount = <?php echo $adultCount; ?>;
        const childCount = <?php echo $childCount; ?>;
        const infantCount = <?php echo $infantCount; ?>;
        const totalPassengers = adultCount + childCount + infantCount;
        
        fares.forEach(function(fare){
            const baggageInfo = (fare.checked_baggage && fare.checked_baggage[0]) ? fare.checked_baggage[0].Value : 'No baggage';
            const cabinBaggageInfo = (fare.cabin_baggage && fare.cabin_baggage[0]) ? fare.cabin_baggage[0].Value : 'No cabin baggage';
            const isRefundable = (fare.is_refundable === true || fare.is_refundable === 'true' || fare.is_refundable === 1);
            const refundText = isRefundable ? '✅ Refundable' : '❌ Non-refundable';
            const refundPenalty = (isRefundable && fare.refund_penalty_amount && fare.refund_penalty_amount !== '') ? ' (Penalty: ' + fare.refund_penalty_amount + ')' : '';
            
            console.log('Computed:', {isRefundable, refundText, refundPenalty});
            
            // Calculate total price for all passengers
            const perPassengerPrice = parseFloat(fare.price || 0);
            const totalPrice = perPassengerPrice * totalPassengers;

            const $card = $('<div>').addClass('fare-option-card').css({
                border: '2px solid #e0e0e0',
                borderRadius: '8px',
                padding: '15px',
                marginBottom: '15px',
                cursor: 'pointer',
                transition: 'all 0.3s ease'
            });
            
            const $label = $('<label>').css({ display: 'flex', alignItems: 'flex-start', cursor: 'pointer', margin: 0 });
            const $input = $('<input>').attr({ type: 'radio', name: 'departureFare', value: fare.fare_source_code })
                .css({ marginRight: '15px', transform: 'scale(1.3)', marginTop: '3px' })
                .data('checked-baggage', baggageInfo)
                .data('cabin-baggage', cabinBaggageInfo)
                .on('change', function(e){ 
                    console.log('Departure fare radio changed:', fare);
                    handleFareSelection('departure', perPassengerPrice, fare.fare_source_code || '', fare.fare_family || 'Standard', e.target); 
                });
            
            // Make entire card clickable to select the radio
            $card.on('click', function(e) {
                if (e.target.type !== 'radio') {
                    $input.prop('checked', true).trigger('change');
                }
            });
            
            const $right = $('<div>').css({ flex: 1 });
            
            // Header row with fare name and price
            const $headerRow = $('<div>').css({ 
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center',
                marginBottom: '10px'
            });
            const $name = $('<strong>').text(fare.fare_family || 'Standard').css({ 
                fontSize: '18px', 
                color: '#007bff',
                fontWeight: '600'
            });
            const $priceContainer = $('<div>').css({ textAlign: 'right' });
            const $price = $('<div>').text('$' + totalPrice.toFixed(2)).css({ 
                fontSize: '24px', 
                color: '#28a745',
                fontWeight: 'bold',
                lineHeight: '1.2'
            });
            const $priceLabel = $('<div>').text('Total for ' + totalPassengers + ' passenger(s)').css({
                fontSize: '11px',
                color: '#999',
                marginTop: '2px'
            });
            $priceContainer.append($price).append($priceLabel);
            $headerRow.append($name).append($priceContainer);
            
            // Per passenger price detail
            const $perPassengerRow = $('<div>').css({
                fontSize: '13px',
                color: '#666',
                marginBottom: '8px',
                paddingBottom: '8px',
                borderBottom: '1px solid #f0f0f0'
            }).text('$' + perPassengerPrice.toFixed(2) + ' per passenger');
            
            // Baggage details
            const $baggageRow = $('<div>').css({
                fontSize: '14px',
                color: '#555',
                marginBottom: '5px',
                display: 'flex',
                alignItems: 'center'
            });
            const $baggageIcon = $('<span>').text('🎒').css({ marginRight: '8px', fontSize: '16px' });
            const $baggageText = $('<span>').html('<strong>Checked:</strong> ' + baggageInfo + ' | <strong>Cabin:</strong> ' + cabinBaggageInfo);
            $baggageRow.append($baggageIcon).append($baggageText);
            
            // Refundability row
            const $refundRow = $('<div>').css({
                fontSize: '14px',
                color: isRefundable ? '#28a745' : '#dc3545',
                marginBottom: '5px',
                fontWeight: '500'
            }).html(refundText + refundPenalty + ' • ' + (fare.fare_type || 'Public Fare'));
            
            // Seats remaining (if available)
            if (fare.seats_remaining && fare.seats_remaining > 0 && fare.seats_remaining < 10) {
                const $seatsRow = $('<div>').css({
                    fontSize: '12px',
                    color: '#ff9800',
                    marginTop: '8px',
                    fontWeight: '500'
                }).text('⚠️ Only ' + fare.seats_remaining + ' seat(s) remaining');
                $right.append($headerRow).append($perPassengerRow).append($baggageRow).append($refundRow).append($seatsRow);
            } else {
                $right.append($headerRow).append($perPassengerRow).append($baggageRow).append($refundRow);
            }
            
            $label.append($input).append($right);
            $card.append($label);
            $depContainer.append($card);
        });
        
        } catch (error) {
            console.error('Error in populateDepartureFares:', error);
            console.error('Error stack:', error.stack);
            alert('Error displaying fare options: ' + error.message);
        }
    }
    
    function populateReturnFares(fares, segments) {
        try {
            // Update return flight info with safe property access
            if (segments && Array.isArray(segments) && segments.length > 0) {
                const firstSeg = segments[0];
                
                if (firstSeg && typeof firstSeg === 'object') {
                    const depAirport = firstSeg.DepartureAirportLocationCode || firstSeg.departureAirportLocationCode || 'N/A';
                    const arrAirport = firstSeg.ArrivalAirportLocationCode || firstSeg.arrivalAirportLocationCode || 'N/A';
                    const airlineCode = firstSeg.MarketingCarriercode || firstSeg.marketingCarriercode || '';
                    const flightNum = firstSeg.MarketingFlightNumber || firstSeg.marketingFlightNumber || '';
                    const depDateTime = firstSeg.DepartureDateTime || firstSeg.departureDateTime || '';
                    const arrDateTime = firstSeg.ArrivalDateTime || firstSeg.arrivalDateTime || '';
                    const journeyDuration = firstSeg.JourneyDuration || firstSeg.journeyDuration || 0;
                    
                    $('#ret-route').text(depAirport + ' → ' + arrAirport);
                    $('#ret-airline').text(airlineCode + flightNum);
                    
                    // Format times and date
                    if (depDateTime) {
                        try {
                            const depTime = new Date(depDateTime).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
                            const arrTime = new Date(arrDateTime).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
                            $('#ret-time').text(depTime + ' → ' + arrTime);
                            
                            const retDateText = new Date(depDateTime).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                            if (retDateText) {
                                $('#ret-date-wrapper').text(' (' + retDateText + ')').show();
                            } else {
                                $('#ret-date-wrapper').hide();
                            }
                        } catch (dateError) {
                            console.error('Date parsing error:', dateError);
                            $('#ret-time').text('Time not available');
                            $('#ret-date-wrapper').hide();
                        }
                    } else {
                        $('#ret-time').text('Time not available');
                        $('#ret-date-wrapper').hide();
                    }
                    
                    if (journeyDuration && journeyDuration > 0) {
                        const hours = Math.floor(journeyDuration / 60);
                        const minutes = journeyDuration % 60;
                        $('#ret-duration').text(hours + 'h ' + (minutes > 0 ? minutes + 'm' : ''));
                    } else {
                        // DST-safe: sum segment JourneyDuration instead of browser date-diff
                        try {
                            const totalMins = (segments || []).reduce(function(total, seg) {
                                const jd = seg && (seg.JourneyDuration || seg.journeyDuration || 0);
                                return total + (Number.isFinite(jd) ? jd : 0);
                            }, 0);
                            if (totalMins > 0) {
                                const hours = Math.floor(totalMins / 60);
                                const minutes = totalMins % 60;
                                $('#ret-duration').text(hours + 'h ' + (minutes > 0 ? minutes + 'm' : ''));
                            } else {
                                $('#ret-duration').text('Duration not available');
                            }
                        } catch (e) {
                            $('#ret-duration').text('Duration not available');
                        }
                    }
                } else {
                    console.error('Invalid return segment structure:', firstSeg);
                    $('#ret-route').text('N/A → N/A');
                    $('#ret-airline').text('N/A');
                    $('#ret-time').text('N/A');
                    $('#ret-duration').text('N/A');
                    $('#ret-date-wrapper').hide();
                }
            } else {
                console.error('No valid return segments found');
                $('#ret-route').text('N/A → N/A');
                $('#ret-airline').text('N/A');
                $('#ret-time').text('N/A');
                $('#ret-duration').text('N/A');
                $('#ret-date-wrapper').hide();
            }
        
        // Build fare options DOM for return section
        const $retContainer = $('#return-fares');
        $retContainer.empty();
        
        if (!fares || fares.length === 0) {
            const $placeholder = $('<div>').css({
                padding: '20px',
                textAlign: 'center',
                color: '#666',
                fontSize: '14px',
                border: '2px dashed #e0e0e0',
                borderRadius: '8px'
            }).text('No return fare options available for this flight.');
            $retContainer.append($placeholder);
            return;
        }
        
        // Get passenger counts
        const adultCount = <?php echo $adultCount; ?>;
        const childCount = <?php echo $childCount; ?>;
        const infantCount = <?php echo $infantCount; ?>;
        const totalPassengers = adultCount + childCount + infantCount;
        
        fares.forEach(function(fare){
            const baggageInfo = (fare.checked_baggage && fare.checked_baggage[0]) ? fare.checked_baggage[0].Value : 'No baggage';
            const cabinBaggageInfo = (fare.cabin_baggage && fare.cabin_baggage[0]) ? fare.cabin_baggage[0].Value : 'No cabin baggage';
            const isRefundable = (fare.is_refundable === true || fare.is_refundable === 'true' || fare.is_refundable === 1);
            const refundText = isRefundable ? '✅ Refundable' : '❌ Non-refundable';
            const refundPenalty = (isRefundable && fare.refund_penalty_amount && fare.refund_penalty_amount !== '') ? ' (Penalty: ' + fare.refund_penalty_amount + ')' : '';
            
            console.log('Computed:', {isRefundable, refundText, refundPenalty});
            
            // Calculate total price for all passengers
            const perPassengerPrice = parseFloat(fare.price || 0);
            const totalPrice = perPassengerPrice * totalPassengers;

            const $card = $('<div>').addClass('fare-option-card').css({
                border: '2px solid #e0e0e0',
                borderRadius: '8px',
                padding: '15px',
                marginBottom: '15px',
                cursor: 'pointer',
                transition: 'all 0.3s ease'
            });
            
            const $label = $('<label>').css({ display: 'flex', alignItems: 'flex-start', cursor: 'pointer', margin: 0 });
            const $input = $('<input>').attr({ type: 'radio', name: 'returnFare', value: fare.fare_source_code || '' })
                .css({ marginRight: '15px', transform: 'scale(1.3)', marginTop: '3px' })
                .data('checked-baggage', baggageInfo)
                .data('cabin-baggage', cabinBaggageInfo)
                .on('change', function(e){ 
                    console.log('Return fare radio changed:', fare);
                    handleFareSelection('return', perPassengerPrice, fare.fare_source_code || '', fare.fare_family || 'Standard', e.target); 
                });
            
            // Make entire card clickable to select the radio
            $card.on('click', function(e) {
                if (e.target.type !== 'radio') {
                    $input.prop('checked', true).trigger('change');
                }
            });
            
            const $right = $('<div>').css({ flex: 1 });
            
            // Header row with fare name and price
            const $headerRow = $('<div>').css({ 
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center',
                marginBottom: '10px'
            });
            const $name = $('<strong>').text(fare.fare_family || 'Standard').css({ 
                fontSize: '18px', 
                color: '#007bff',
                fontWeight: '600'
            });
            const $priceContainer = $('<div>').css({ textAlign: 'right' });
            const $price = $('<div>').text('$' + totalPrice.toFixed(2)).css({ 
                fontSize: '24px', 
                color: '#28a745',
                fontWeight: 'bold',
                lineHeight: '1.2'
            });
            const $priceLabel = $('<div>').text('Total for ' + totalPassengers + ' passenger(s)').css({
                fontSize: '11px',
                color: '#999',
                marginTop: '2px'
            });
            $priceContainer.append($price).append($priceLabel);
            $headerRow.append($name).append($priceContainer);
            
            // Per passenger price detail
            const $perPassengerRow = $('<div>').css({
                fontSize: '13px',
                color: '#666',
                marginBottom: '8px',
                paddingBottom: '8px',
                borderBottom: '1px solid #f0f0f0'
            }).text('$' + perPassengerPrice.toFixed(2) + ' per passenger');
            
            // Baggage details
            const $baggageRow = $('<div>').css({
                fontSize: '14px',
                color: '#555',
                marginBottom: '5px',
                display: 'flex',
                alignItems: 'center'
            });
            const $baggageIcon = $('<span>').text('🎒').css({ marginRight: '8px', fontSize: '16px' });
            const $baggageText = $('<span>').html('<strong>Checked:</strong> ' + baggageInfo + ' | <strong>Cabin:</strong> ' + cabinBaggageInfo);
            $baggageRow.append($baggageIcon).append($baggageText);
            
            // Refundability row
            const $refundRow = $('<div>').css({
                fontSize: '14px',
                color: isRefundable ? '#28a745' : '#dc3545',
                marginBottom: '5px',
                fontWeight: '500'
            }).html(refundText + refundPenalty + ' • ' + (fare.fare_type || 'Public Fare'));
            
            // Seats remaining (if available)
            if (fare.seats_remaining && fare.seats_remaining > 0 && fare.seats_remaining < 10) {
                const $seatsRow = $('<div>').css({
                    fontSize: '12px',
                    color: '#ff9800',
                    marginTop: '8px',
                    fontWeight: '500'
                }).text('⚠️ Only ' + fare.seats_remaining + ' seat(s) remaining');
                $right.append($headerRow).append($perPassengerRow).append($baggageRow).append($refundRow).append($seatsRow);
            } else {
                $right.append($headerRow).append($perPassengerRow).append($baggageRow).append($refundRow);
            }
            
            $label.append($input).append($right);
            $card.append($label);
            $retContainer.append($card);
        });
        
        // Show return section
        $('.return-section').show();
        
        } catch (error) {
            console.error('Error in populateReturnFares:', error);
            console.error('Error stack:', error.stack);
            alert('Error displaying return fare options: ' + error.message);
        }
    }
    
    function handleFareSelection(leg, price, fareSourceCode, fareFamily, targetElement) {
        console.log('handleFareSelection called:', { leg, price, fareSourceCode, fareFamily, priceType: typeof price });
        
        // Ensure price is a valid number
        const parsedPrice = parseFloat(price);
        if (isNaN(parsedPrice) || parsedPrice <= 0) {
            console.error('Invalid price provided:', price);
            alert('Error: Invalid fare price. Please try selecting again.');
            return;
        }
        
        // Read baggage values from radio element (attached when building the card)
        var $radioEl = $(targetElement);
        var selectedCheckedBaggage = $radioEl.data('checked-baggage') || '';
        var selectedCabinBaggage = $radioEl.data('cabin-baggage') || '';

        window.selectedFares[leg] = {
            price: parsedPrice,
            fareSourceCode: fareSourceCode,
            fareFamily: fareFamily,
            checkedBaggage: selectedCheckedBaggage,
            cabinBaggage: selectedCabinBaggage
        };
        
        console.log('selectedFares after update:', window.selectedFares);
        console.log('Departure fare:', window.selectedFares.departure);
        console.log('Return fare:', window.selectedFares.return);
        
        updateTotalPrice();
        
        // Enable continue button if selections are valid
        const airTripType = '<?php echo $airTripType; ?>';
        const canContinue = selectedFares.departure !== null && 
                           (airTripType === 'OneWay' || selectedFares.return !== null);
        
        $('#continue-to-booking-btn').prop('disabled', !canContinue);
        
        // Highlight selected card
        if (targetElement) {
            $(targetElement).closest('.fare-option-card').css({
                'border-color': '#007bff',
                'background-color': '#f0f8ff'
            }).siblings().css({
                'border-color': '#e0e0e0',
                'background-color': 'white'
            });
        }
    }
    
    // Make updateTotalPrice globally accessible
    window.updateTotalPrice = function updateTotalPrice() {
        console.log('updateTotalPrice called');
        console.log('window.selectedFares:', window.selectedFares);
        
        let total = 0;
        if (window.selectedFares && window.selectedFares.departure && window.selectedFares.departure.price) {
            const depPrice = parseFloat(window.selectedFares.departure.price) || 0;
            console.log('Departure price:', depPrice);
            total += depPrice;
        }
        if (window.selectedFares && window.selectedFares.return && window.selectedFares.return.price) {
            const retPrice = parseFloat(window.selectedFares.return.price) || 0;
            console.log('Return price:', retPrice);
            total += retPrice;
        }
        
        console.log('Calculated total:', total);
        
        $('#total-price-display').text('$' + total.toFixed(2));
        console.log('Total price display updated to:', '$' + total.toFixed(2));
        
        // Update subtitle with helpful message
        const airTripType = '<?php echo $airTripType; ?>';
        let subtitle = '';
        
        if (airTripType === 'OneWay') {
            if (window.selectedFares && window.selectedFares.departure) {
                subtitle = window.selectedFares.departure.fareFamily;
                $('#selected-fares-text').text('(' + subtitle + ')');
            } else {
                $('#selected-fares-text').text('(Select fare for departure)');
            }
        } else {
            // Return trip
            if (window.selectedFares && window.selectedFares.departure) {
                subtitle += window.selectedFares.departure.fareFamily;
            }
            if (window.selectedFares && window.selectedFares.return) {
                if (subtitle) subtitle += ' + ';
                subtitle += window.selectedFares.return.fareFamily;
            }
            
            if (subtitle) {
                $('#selected-fares-text').text('(' + subtitle + ')');
            } else if (window.selectedFares && window.selectedFares.departure) {
                $('#selected-fares-text').text('(Departure: ' + window.selectedFares.departure.fareFamily + ' | Select return fare)');
            } else {
                $('#selected-fares-text').text('(Select fares for both legs)');
            }
        }
    }
    
    // Continue to booking function
    $(document).on('click', '#continue-to-booking-btn', function() {
        if (window.selectedFares.departure) {
            // Store selected fare source code in session via AJAX
            $.ajax({
                url: 'includes/ajax.php',
                type: 'POST',
                data: {
                    fs_code: window.selectedFares.departure.fareSourceCode,
                    dep_checked: (window.selectedFares.departure && window.selectedFares.departure.checkedBaggage) ? window.selectedFares.departure.checkedBaggage : '',
                    dep_cabin: (window.selectedFares.departure && window.selectedFares.departure.cabinBaggage) ? window.selectedFares.departure.cabinBaggage : '',
                    ret_checked: (window.selectedFares.return && window.selectedFares.return.checkedBaggage) ? window.selectedFares.return.checkedBaggage : '',
                    ret_cabin: (window.selectedFares.return && window.selectedFares.return.cabinBaggage) ? window.selectedFares.return.cabinBaggage : ''
                },
                success: function(response) {
                    // Redirect to revalidation page
                    window.location.href = 'fligtsRulesRevalidation';
                },
                error: function() {
                    alert('Error saving selection. Please try again.');
                }
            });
        }
    });
</script>
<!-- ============ To remove cickable behaviour of radio buttons for airtrip type selection on top ==== -->
<style>
    /* input[type="radio"]:not(:checked) + label {
        pointer-events: none;
    } */
    
    /* SweetAlert Booking Confirmation Dialog Custom Styles */
    .booking-confirmation-popup {
        border-radius: 15px !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important;
    }
    
    .swal-title-custom {
        padding: 0 !important;
        margin-bottom: 0 !important;
    }
    
    .swal-confirm-button-custom {
        padding: 12px 20px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3) !important;
        transition: all 0.3s ease !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        max-width: 100% !important;
        line-height: 1.4 !important;
    }
    
    .swal-confirm-button-custom:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4) !important;
    }
    
    .swal-cancel-button-custom {
        padding: 12px 30px !important;
        border-radius: 8px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }
    
    .swal-cancel-button-custom:hover {
        transform: translateY(-2px) !important;
        background-color: #5a6268 !important;
    }
    
    .swal2-popup {
        padding: 0 !important;
        margin-top: 40px !important;
        overflow-x: hidden !important;
        max-width: 650px !important;
    }
    
    .swal2-html-container {
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        word-wrap: break-word !important;
        max-width: 100% !important;
        max-height: 65vh !important;
    }
    
    /* Responsive adjustments for mobile */
    @media (max-width: 768px) {
        .swal2-popup {
            max-width: 95% !important;
            margin-top: 20px !important;
        }
        
        .swal2-html-container {
            max-height: 70vh !important;
        }
    }
    
    .booking-confirmation-popup * {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        max-width: 100% !important;
    }
</style>

<div class="full-page-spinner" style="display: none;">
    <video autoplay muted loop style="width: 200px;">
        <source src="images/video/airoplane_loading.mp4" type="video/mp4">
    </video>
    <h3>Reserving your seat....</h3>
</div>

</body>

</html>