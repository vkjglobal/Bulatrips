<?php
/* error_reporting(0);
ini_set('display_errors', 0); */
session_start();
if (!isset($_SESSION['user_id'])) { //for test  environment 
?>
    <script>
        window.location = "index.php"
    </script>
<?php
} else {
    //=========================================================================================

    require_once("includes/header.php");
    ?>
    <link rel="stylesheet" href="css/airline.css">
    <link rel="stylesheet" href="css/reissue-style.css">
    <?php
    include_once('includes/class.cancel.php');
    include_once('includes/class.Booking.php');
    
    // Check if booking_id is passed
    if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
        ?>
        <section>
            <div class="container">
                <div class="alert alert-danger mt-4">
                    <h4><i class="fas fa-exclamation-triangle"></i> Booking ID Required</h4>
                    <p>Please provide a valid booking ID to access this page.</p>
                    <a href="user-dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            </div>
        </section>
        <?php
        require_once("includes/footer.php");
        exit;
    }
    
    $bookingId = $_GET['booking_id'];
    $bookingId = filter_var($bookingId, FILTER_SANITIZE_NUMBER_INT);
    $bookingId   =   trim($bookingId);
    $userId     =   $_SESSION['user_id'];
    $currentTimestamp = time();

    //    $bookingId  =   122;
     // $userId    =   9;

    $objCancel     =   new Cancel();
    $bookCanusers      =   $objCancel->BookCancelUsers($bookingId, $userId);
    
    // Check if any data is returned
    if (empty($bookCanusers)) {
        ?>
        <section>
            <div class="container">
                <div class="alert alert-warning mt-4">
                    <h4><i class="fas fa-info-circle"></i> No Passenger Data Found</h4>
                    <p>Unable to retrieve passenger information for this booking. This could be because:</p>
                    <ul>
                        <li>The booking does not exist or has been deleted</li>
                        <li>You don't have permission to access this booking</li>
                        <li>The booking has no associated passengers</li>
                    </ul>
                    <a href="user-dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </div>
        </section>
        <?php
        require_once("includes/footer.php");
        exit;
    }
    
    //  print_r($bookCanusers);
    $childpsnger        = isset($bookCanusers[0]['child_count']) ? $bookCanusers[0]['child_count'] : 0;
    $arrival_location        = isset($bookCanusers[0]['arrival_location']) ? $bookCanusers[0]['arrival_location'] : '';

    if($childpsnger === 0){
        $allow_child    =   false;
    }
    elseif($childpsnger > 0){
        $allow_child    =   true;
    }
    //var_dump($allow_child);exit;

    //preticketed cancel need only one row value to check ticketed or not 

    //Booking details fetch
    $booking = new Booking($conn);

    // Subscribe the user and get the result message
    $resultBooking = $booking->getBookingDetailsbyId($bookingId);
    $lastRecord = $resultBooking[count($resultBooking) - 1];
    
    // Get trip type from booking data
    $air_trip_type = isset($resultBooking[0]['air_trip_type']) ? $resultBooking[0]['air_trip_type'] : 'OneWay';
    
    $return_dep_date      =   $objCancel->ReturnDepDate($bookingId,$userId,$arrival_location);
    $dep_date_returnTrip    =   isset($return_dep_date[0]['dep_date']) ? $return_dep_date[0]['dep_date'] : '';
    $cabin_preference_return    =   isset($return_dep_date[0]['cabin_preference']) ? $return_dep_date[0]['cabin_preference'] : '';
    $airline_code_return    =   isset($return_dep_date[0]['airline_code']) ? $return_dep_date[0]['airline_code'] : '';
    $flight_no_return    =   isset($return_dep_date[0]['flight_no']) ? $return_dep_date[0]['flight_no'] : '';
     
     
    $airTripTypests = 0;   //oneway 
    $precancelsts = isset($bookCanusers[0]['ticket_status']) ? $bookCanusers[0]['ticket_status'] : '';
    $pre_mf_reference = isset($bookCanusers[0]['mf_reference']) ? $bookCanusers[0]['mf_reference'] : '';

    //==================================================================================

?>
    <section>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center my-4">
                <h2 class="title-typ2 mb-0">Flight Reissue</h2>
                <button type="button" class="btn btn-outline-secondary" onclick="redirectToPreviousPage()">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Booking Details
                </button>
            </div>
            <div class="row my-4">
                <div class="col-12">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <h6 class="text-left fw-700">Do you want to change your journey?</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Note:</strong> Reissue eligibility depends on your ticket status and fare type. 
                            Select passengers below to proceed with reissue.
                        </div>
                    </div>
                </div>
            </div>
            
            <form class="" id="flight-search_reissue" method="post" action="">
                <div class="table-responsive mb-3">
                    <h6 class="text-left fw-700">Select Travellers</h6>
                    <table id="psngr" class="table table-bordered white-bg text-left fs-14" style="min-width: 500px;">
                        <thead>
                            <tr class="dark-blue-bg white-txt">
                                <th style="width: 20px;">
                                    <div class="chkbx">
                                        <input type="checkbox" id="changeDateAll">
                                        <label for="changeDateAll" class="mb-0"></label>
                                    </div>
                                </th>
                                <th style="width: 33%;">Passenger Name</th>
                                <th style="width: 33%;">Ticket Status</th>
                                <th style="width: 33%;">Departure Date</th>
                            </tr>
                        </thead>
                        <tbody>
                                                            <?php
                            if (!empty($bookCanusers)) {
                                $i = 0;
                                $hasReissueInProcess = false;
                                $hasNonTicketedPassengers = false;
                                
                                foreach ($bookCanusers as $key => $val) {
                                    $i++;
                                    //*******************************************
                                    $pre_booking_status        =   $val['booking_status'];
                                    $pre_ticket_time_limit     =   $val['ticket_time_limit'];
                                    $pre_mf_reference          =  $val['mf_reference'];
                                    $pre_ticket_status         =   $val['ticket_status'];
                                    $fare_type                  =   $val['fare_type'];
                                    
                                    // Reissue status checking (like void system)
                                    $reissueStatus = $val['reissue_status'] ?? null;
                                    $isReissueInProcess = ($reissueStatus === 'InProcess');
                                    $isTicketed = ($pre_ticket_status === 'Ticketed');
                                    
                                    if ($isReissueInProcess) {
                                        $hasReissueInProcess = true;
                                    }
                                    if (!$isTicketed) {
                                        $hasNonTicketedPassengers = true;
                                    }
                                    
                                    $objCancel->closeConnection();
                                    // This will close the database connection as well
                                    //******************************************
                                    $checkboxId =   "changeDate" . $i;
                                    $passenger_name  =   $val['title'] . " " . $val['first_name'] . " " . $val['last_name'];
                                    $dep_date     =   $val['dep_date'];
                                    $dateTime      = new DateTime($dep_date);
                                    $formattedDate = $dateTime->format('d F Y, H:i');
                                    if(isset($_POST['airport'])) {
                                        $dep_location = $_POST['airport'];
                                    } else {
                                        $dep_location = isset($resultBooking[0]['dep_location']) ? $resultBooking[0]['dep_location'] : ''; // Use the default value from the database
                                    }
                                    if(isset($_POST['arrivalairport'])) {
                                        $arrivalairport = $_POST['arrivalairport'];
                                    } else {
                                        $arrivalairport = $arrival_location; // Use the default value from the database
                                    }
                                    
                                    // Smart checkbox logic (like void system)
                                    $checkboxDisabled = !$isTicketed || $isReissueInProcess;
                                    $checkboxClass = $checkboxDisabled ? 'chkbox disabled' : 'chkbox';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="chkbx">
                                                <input type="checkbox" name="passengers[]" value="<?php echo $val['id']; ?>" 
                                                       class="<?php echo $checkboxClass; ?>" 
                                                       id="<?php echo $checkboxId; ?>" 
                                                       data-firstname="<?php echo $val['first_name']; ?>" 
                                                       data-lastname="<?php echo $val['last_name']; ?>" 
                                                       data-title="<?php echo $val['title']; ?>" 
                                                       data-eticket="<?php echo $val['e_ticket_number']; ?>" 
                                                       data-passengertype="<?php echo $val['passenger_type']; ?>"
                                                       <?php echo $checkboxDisabled ? 'disabled' : ''; ?>>
                                                <label for="<?php echo $checkboxId; ?>" class="mb-0"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($passenger_name); ?></strong>
                                            <br><small class="text-muted">Passenger Type: <?php echo ucfirst($val['passenger_type']); ?></small>
                                            <?php if ($isReissueInProcess): ?>
                                                <br><small class="text-warning"><i class="fas fa-clock"></i> Reissue In Progress</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $isTicketed ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo ucfirst($pre_ticket_status); ?>
                                            </span>
                                            <?php if (!empty($val['e_ticket_number'])): ?>
                                                <br><small class="text-muted">Ticket: <?php echo htmlspecialchars($val['e_ticket_number']); ?></small>
                                            <?php endif; ?>
                                            <?php if ($isReissueInProcess): ?>
                                                <br><small class="text-info">PTR ID: <?php echo htmlspecialchars($val['reissue_ptr_id'] ?? 'N/A'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $formattedDate; ?>
                                            <br><small class="text-muted">Departure</small>
                                        </td>
                                    </tr>
                                <?php }  
                            } else {
                                echo '<tr><td colspan="4" class="text-center">No passengers found for this booking.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Smart Messages (like void system) -->
                <?php if ($hasNonTicketedPassengers): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Note:</strong> Only ticketed passengers can be selected for rescheduling. Non-ticketed passengers will be disabled until tickets are issued.
                    </div>
                <?php endif; ?>
                
                <?php if ($hasReissueInProcess): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-clock mr-2"></i>
                        <strong>Reissue In Progress:</strong> Some passengers have reissue requests in process. These passengers cannot be selected for new reissue requests.
                    </div>
                <?php endif; ?>
                
                <!-- Reissue Action Button -->
                <div class="reissue-actions mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-primary btn-block" id="modify-flight-btn">
                                <i class="fas fa-edit mr-2"></i>Modify Flight Details
                            </button>
                            <small class="text-muted d-block mt-1">Change flight dates, cabin class, or other details</small>
                        </div>
                    </div>
                </div>

                <!-- Flight Modification Form (Documentation-Based) -->
                <div id="flight-modify-form" style="display: none;" class="mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-plane mr-2"></i>Modify Flight Details</h6>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="precancelValue" name="mfreNum" value="<?php echo $pre_mf_reference; ?>">
                            <input type="hidden" name="bookingId" id="bookingId" value="<?php echo $bookingId; ?>">
                            <input type="hidden" name="userId" id="USerid" value="<?php echo $userId; ?>">
                            
                            <!-- Current Flight Details (Read-Only) -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3"><i class="fas fa-info-circle mr-2"></i>Current Flight Details</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Route</th>
                                                    <th>Flight</th>
                                                    <th>Date & Time</th>
                                                    <th>Cabin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo isset($dep_location) ? $dep_location : ''; ?> → <?php echo isset($arrivalairport) ? $arrivalairport : ''; ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($resultBooking[0]['airline_code']) ? $resultBooking[0]['airline_code'] : ''; ?> 
                                                        <?php echo isset($resultBooking[0]['flight_no']) ? $resultBooking[0]['flight_no'] : ''; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $currentDepDate = isset($resultBooking[0]['dep_date']) ? $resultBooking[0]['dep_date'] : '';
                                                        if ($currentDepDate) {
                                                            $dateTime = new DateTime($currentDepDate);
                                                            echo $dateTime->format('d M Y, H:i');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($resultBooking[0]['cabin_preference']) ? ucfirst($resultBooking[0]['cabin_preference']) : 'Economy'; ?>
                                                    </td>
                                                </tr>
                                                <?php if ($air_trip_type === 'Return' && isset($dep_date_returnTrip)): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo isset($arrivalairport) ? $arrivalairport : ''; ?> → <?php echo isset($dep_location) ? $dep_location : ''; ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($airline_code_return) ? $airline_code_return : ''; ?> 
                                                        <?php echo isset($flight_no_return) ? $flight_no_return : ''; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if ($dep_date_returnTrip) {
                                                            $dateTime = new DateTime($dep_date_returnTrip);
                                                            echo $dateTime->format('d M Y, H:i');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo isset($cabin_preference_return) ? ucfirst($cabin_preference_return) : 'Economy'; ?>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Segment Selection (only for Return trips) -->
                            <?php if ($air_trip_type === 'Return'): ?>
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label d-block"><strong>Which dates do you want to change?</strong></label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="segment_selection" id="segment_outbound" value="outbound" checked>
                                        <label class="form-check-label" for="segment_outbound">Departure only</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="segment_selection" id="segment_return" value="return">
                                        <label class="form-check-label" for="segment_return">Return only</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="segment_selection" id="segment_both" value="both">
                                        <label class="form-check-label" for="segment_both">Both</label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Modification Options -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>New Departure Date</strong></label>
                                    <input type="date" class="form-control" name="new_dep_date" id="new_dep_date" 
                                           min="<?php echo date('Y-m-d'); ?>">
                                    <small class="text-muted">Select new departure date</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Cabin Class</strong></label>
                                    <select class="form-control" name="new_cabin_class" id="new_cabin_class">
                                        <option value="Y">Economy (Y)</option>
                                        <option value="S">Premium Economy (S)</option>
                                        <option value="C">Business Class (C)</option>
                                        <option value="F">First Class (F)</option>
                                    </select>
                                    <small class="text-muted">Select new cabin class (optional)</small>
                                </div>
                            </div>

                            <?php if ($air_trip_type === 'Return'): ?>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>New Return Date</strong></label>
                                    <input type="date" class="form-control" name="new_return_date" id="new_return_date" 
                                           min="<?php echo date('Y-m-d'); ?>">
                                    <small class="text-muted">Select new return date</small>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0">
                                        <small><i class="fas fa-info-circle mr-1"></i>Same cabin class will be applied to return flight</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success btn-block btn-lg" id="get-reissue-quote-btn">
                                        <i class="fas fa-calculator mr-2"></i>Get Reissue Quote
                                    </button>
                                    <small class="text-muted d-block mt-2">We'll check availability and show you the fare difference</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Container for AJAX search results -->
            <div id="flight-search-results" class="mt-4" style="display: none;"></div>
        </div>
    </section>

    <script>
        // Select all checkbox functionality (only enabled checkboxes)
        document.getElementById('changeDateAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.chkbox:not(:disabled)');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = document.getElementById('changeDateAll').checked;
            });
        });

        // Individual checkbox change
        document.querySelectorAll('.chkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var allChecked = true;
                var enabledCheckboxes = document.querySelectorAll('.chkbox:not(:disabled)');
                enabledCheckboxes.forEach(function(cb) {
                    if (!cb.checked) {
                        allChecked = false;
                    }
                });
                document.getElementById('changeDateAll').checked = allChecked;
            });
        });

        // Modify flight details button
        document.getElementById('modify-flight-btn').addEventListener('click', function() {
            var checkedBoxes = document.querySelectorAll('.chkbox:checked:not(:disabled)');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one ticketed passenger to proceed with reissue.');
                return;
            }
            document.getElementById('flight-modify-form').style.display = 'block';
            // Scroll to the form
            document.getElementById('flight-modify-form').scrollIntoView({ behavior: 'smooth' });
        });

        // Handle form submission with AJAX (Documentation-Based ReissueQuote)
        document.getElementById('flight-search_reissue').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected passengers (only enabled checkboxes)
            var selectedPassengers = [];
            document.querySelectorAll('.chkbox:checked:not(:disabled)').forEach(function(checkbox) {
                selectedPassengers.push({
                    id: checkbox.value,
                    firstName: checkbox.dataset.firstname,
                    lastName: checkbox.dataset.lastname,
                    title: checkbox.dataset.title,
                    eticket: checkbox.dataset.eticket,
                    passengerType: checkbox.dataset.passengertype
                });
            });

            console.log('Selected passengers:', selectedPassengers);

            if (selectedPassengers.length === 0) {
                alert('Please select at least one ticketed passenger.');
                return;
            }

            // Trip type from server
            var tripType = '<?php echo $air_trip_type; ?>';

            // Gather dates
            var newDepDateInput = document.getElementById('new_dep_date');
            var newReturnDateInput = document.getElementById('new_return_date');
            var newDepDate = newDepDateInput ? newDepDateInput.value : null;
            var newReturnDateVal = newReturnDateInput ? newReturnDateInput.value : null;

            // Segment selection (for return trips)
            var segmentSelection = 'outbound';
            var segmentRadio = document.querySelector('input[name="segment_selection"]:checked');
            if (segmentRadio) {
                segmentSelection = segmentRadio.value; // outbound | return | both
            }

            // Validation rules
            if (tripType !== 'Return') {
                // OneWay: require departure date
                if (!newDepDate) {
                    alert('Please select a new departure date.');
                    return;
                }
            } else {
                if (segmentSelection === 'outbound') {
                    if (!newDepDate) {
                        alert('Please select a new departure date.');
                        return;
                    }
                    // ignore return date
                    newReturnDateVal = null;
                } else if (segmentSelection === 'return') {
                    if (!newReturnDateVal) {
                        alert('Please select a new return date.');
                        return;
                    }
                    // new departure optional
                    newDepDate = null;
                } else if (segmentSelection === 'both') {
                    if (!newDepDate || !newReturnDateVal) {
                        alert('Please select both departure and return dates.');
                        return;
                    }
                    if (new Date(newReturnDateVal) <= new Date(newDepDate)) {
                        alert('Return date must be after departure date.');
                        return;
                    }
                }
            }

            // Prepare ReissueQuote request data (Documentation-based)
            var requestData = {
                action: 'get_reissue_quote',
                bookingId: document.getElementById('bookingId').value,
                userId: document.getElementById('USerid').value,
                mfRef: document.getElementById('precancelValue').value,
                selectedPassengers: selectedPassengers,
                newDepartureDate: newDepDate,
                newCabinClass: document.getElementById('new_cabin_class').value,
                newReturnDate: newReturnDateVal,
                segmentSelection: segmentSelection
            };

            console.log('ReissueQuote Request Data:', requestData);

            // Show loading state
            var submitBtn = document.getElementById('get-reissue-quote-btn');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Getting Quote...';
            submitBtn.disabled = true;

            // Make AJAX request to ReissueQuote API
            fetch('reissue_quote_process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('ReissueQuote Response:', data);
                
                if (data.success) {
                    // After PTR is created, show polling banner and poll GetExchangeQuote to fetch options
                    showPollingStatus(data.ptrId, data.slaMinutes || 60, 'Fetching reissue quote...');
                    fetchGetExchangeQuote(data.ptrId);
                } else {
                    // Show error message
                    alert('Error: ' + (data.message || 'Failed to get reissue quote'));
                }
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Poll/GetExchangeQuote → build modal with options
        var nextPollInterval = null;
        var nextPollSeconds = 0;

        function startCountdown(seconds, onTick) {
            if (nextPollInterval) clearInterval(nextPollInterval);
            nextPollSeconds = seconds;
            nextPollInterval = setInterval(function(){
                nextPollSeconds--;
                if (typeof onTick === 'function') onTick(nextPollSeconds);
                if (nextPollSeconds <= 0) {
                    clearInterval(nextPollInterval);
                }
            }, 1000);
        }

        function fetchGetExchangeQuote(ptrId) {
            var payload = {
                mfreNum: document.getElementById('precancelValue').value,
                ptrId: ptrId,
                bookingId: document.getElementById('bookingId').value,
                userId: document.getElementById('USerid').value
            };

            fetch('reissue_get_exchange.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    // If not ready, schedule retry and update countdown
                    updatePollingStatus('Waiting for quote (auto-refresh)...');
                    startCountdown(10, function(s){ updatePollingCountdown(s); });
                    setTimeout(function(){ fetchGetExchangeQuote(ptrId); }, 10000);
                    return;
                }
                if (res.status && res.status.toLowerCase() === 'completed' && res.resolution && res.resolution.toLowerCase() === 'quoteupdated') {
                    hidePollingStatus();
                    showOptionsModal(res.ptrId, res.requestedPreferences || []);
                } else {
                    // keep polling until completed+quoteupdated
                    updatePollingStatus('Fetching reissue quote...');
                    startCountdown(10, function(s){ updatePollingCountdown(s); });
                    setTimeout(function(){ fetchGetExchangeQuote(ptrId); }, 10000);
                }
            })
            .catch(err => alert('Error fetching quote: ' + err.message));
        }

        function showOptionsModal(ptrId, preferences) {
            var optionsHtml = '';
            if (!preferences || preferences.length === 0) {
                optionsHtml = '<p>No options returned yet. Please try again shortly.</p>';
            } else {
                optionsHtml = '<div class="list-group">' + preferences.map(function(p){
                    var fare = (p.QuotedFares && p.QuotedFares[0]) ? p.QuotedFares[0] : {};
                    var total = fare.TotalFareDifference || 0;
                    var currency = fare.Currency || 'USD';
                    return '<label class="list-group-item">' +
                        '<input type="radio" name="reissue_option" value="'+p.Option+'" class="mr-2" />' +
                        '<strong>Option '+p.Option+'</strong> — Total: '+total+' '+currency+
                        '</label>';
                }).join('') + '</div>';
            }

            var modalHtml = `
                <div class="modal fade" id="reissueQuoteModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-calculator mr-2"></i>Reissue Quote Options</h5>
                                <div class="ml-auto d-flex align-items-center">
                                    <small class="text-muted mr-3">Need new fares?</small>
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="window._refreshOptions('${ptrId}')">Refresh now</button>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                            </div>
                            <div class="modal-body">${optionsHtml}</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success" onclick="acceptSelectedOption('${ptrId}')">Accept Selected</button>
                            </div>
                        </div>
                    </div>
                </div>`;

            var existingModal = document.getElementById('reissueQuoteModal');
            if (existingModal) existingModal.remove();
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            $('#reissueQuoteModal').modal('show');
            window._refreshOptions = function(pid){ fetchGetExchangeQuote(pid); };
        }

        window.acceptSelectedOption = function(ptrId) {
            var selected = document.querySelector('input[name="reissue_option"]:checked');
            if (!selected) { alert('Please select an option'); return; }
            var option = parseInt(selected.value, 10);

            var payload = {
                mfreNum: document.getElementById('precancelValue').value,
                ptrId: ptrId,
                preferenceOption: option,
                acceptQuote: 'yes'
            };

            fetch('reissue_accept_quote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) { alert('Accept failed: ' + (res.message || '')); return; }
                $('#reissueQuoteModal').modal('hide');
                alert('Reissue accepted. PTR: ' + res.ptrId + '. We will process within ' + (res.slaMinutes || '60') + ' minutes.');
                // Start polling for completion and then fetch TripDetails to store new e-tickets
                showPollingStatus(res.ptrId, res.slaMinutes || 60, 'Processing reissue...');
                pollReissueCompletion(res.ptrId);
            })
            .catch(err => alert('Accept error: ' + err.message));
        }

        function pollReissueCompletion(ptrId) {
            var payload = {
                mfreNum: document.getElementById('precancelValue').value,
                ptrId: ptrId,
                bookingId: document.getElementById('bookingId').value,
                userId: document.getElementById('USerid').value
            };
            fetch('reissue_get_exchange.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.ptrType && res.ptrType.toLowerCase() === 'reissue' && res.status && res.status.toLowerCase() === 'completed' && res.resolution && res.resolution.toLowerCase() === 'reissued') {
                    // call TripDetails to persist ticket numbers
                    persistTripDetails();
                } else {
                    updatePollingStatus('Processing reissue...');
                    startCountdown(10, function(s){ updatePollingCountdown(s); });
                    setTimeout(function(){ pollReissueCompletion(ptrId); }, 10000);
                }
            })
            .catch(_ => setTimeout(function(){ pollReissueCompletion(ptrId); }, 15000));
        }

        function persistTripDetails() {
            var payload = {
                bookingId: document.getElementById('bookingId').value,
                mfreNum: document.getElementById('precancelValue').value
            };
            fetch('reissue_trip_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(payload).toString()
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    hidePollingStatus();
                    alert('Reissue completed and tickets updated.');
                } else {
                    hidePollingStatus();
                    alert('Reissue completed but failed to update tickets: ' + (res.message || ''));
                }
            })
            .catch(err => alert('TripDetails update error: ' + err.message));
        }

        // Polling status banner helpers
        function showPollingStatus(ptrId, slaMinutes, message) {
            var existing = document.getElementById('quote-poll-banner');
            if (existing) existing.remove();
            var html = '<div id="quote-poll-banner" class="alert alert-info d-flex align-items-center" style="position:sticky; top:0; z-index:1040;">'
                + '<i class="fas fa-sync fa-spin mr-2"></i>'
                + '<div><strong id="poll-message">'+ (message || 'Processing...') +'</strong><br><small>SLA ~ '+(slaMinutes||60)+' mins | Next refresh in <span id="poll-countdown">10</span>s</small></div>'
                + '<div class="ml-auto">'
                + '<button type="button" class="btn btn-sm btn-outline-primary" onclick="window._manualPoll('+ptrId+')">Refresh now</button>'
                + '</div>'
                + '</div>';
            var container = document.querySelector('.container');
            if (container) container.insertAdjacentHTML('afterbegin', html);
            window._manualPoll = function(pid){ fetchGetExchangeQuote(pid); };
        }

        function updatePollingStatus(message){
            var msg = document.getElementById('poll-message');
            if (msg) msg.textContent = message;
        }

        function updatePollingCountdown(sec){
            var el = document.getElementById('poll-countdown');
            if (el) el.textContent = sec;
        }

        function hidePollingStatus(){
            var existing = document.getElementById('quote-poll-banner');
            if (existing) existing.remove();
        }

        // Redirect to previous page
        function redirectToPreviousPage() {
            window.history.back();
        }

        // Dynamic date validation and enabling/disabling based on segment selection
        document.addEventListener('DOMContentLoaded', function() {
            var depDateInput = document.getElementById('new_dep_date');
            var returnDateInput = document.getElementById('new_return_date');
            var segmentRadios = document.querySelectorAll('input[name="segment_selection"]');
            var tripType = '<?php echo $air_trip_type; ?>';

            function updateDateInputs() {
                var selected = 'outbound';
                var checked = document.querySelector('input[name="segment_selection"]:checked');
                if (checked) { selected = checked.value; }

                // Reset disabled/required states
                if (depDateInput) {
                    depDateInput.disabled = false;
                }
                if (returnDateInput) {
                    returnDateInput.disabled = false;
                }

                if (tripType !== 'Return') {
                    // One-way: only departure is relevant
                    if (depDateInput) depDateInput.disabled = false;
                    if (returnDateInput) {
                        returnDateInput.disabled = true;
                        returnDateInput.value = '';
                    }
                    return;
                }

                if (selected === 'outbound') {
                    if (depDateInput) depDateInput.disabled = false;
                    if (returnDateInput) {
                        returnDateInput.disabled = true;
                        returnDateInput.value = '';
                    }
                } else if (selected === 'return') {
                    if (depDateInput) {
                        depDateInput.disabled = true;
                        depDateInput.value = '';
                    }
                    if (returnDateInput) returnDateInput.disabled = false;
                } else if (selected === 'both') {
                    if (depDateInput) depDateInput.disabled = false;
                    if (returnDateInput) returnDateInput.disabled = false;
                }
            }

            // Keep return date after departure when both are enabled
            if (depDateInput && returnDateInput) {
                depDateInput.addEventListener('change', function() {
                    var selected = document.querySelector('input[name="segment_selection"]:checked');
                    selected = selected ? selected.value : 'outbound';
                    if (selected !== 'both') return; // only enforce when both are changing

                    var depDate = new Date(this.value);
                    var nextDay = new Date(depDate);
                    nextDay.setDate(depDate.getDate() + 1);

                    var minReturnDate = nextDay.toISOString().split('T')[0];
                    returnDateInput.min = minReturnDate;

                    if (returnDateInput.value && new Date(returnDateInput.value) <= depDate) {
                        returnDateInput.value = '';
                    }
                });
            }

            // Wire up radio change events (if present)
            if (segmentRadios && segmentRadios.length > 0) {
                segmentRadios.forEach(function(radio) {
                    radio.addEventListener('change', updateDateInputs);
                });
            }

            // Initialize state on load
            updateDateInputs();
        });
    </script>

    <?php require_once("includes/footer.php"); ?>
<?php } ?>