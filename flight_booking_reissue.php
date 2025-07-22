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
                                foreach ($bookCanusers as $key => $val) {
                                    $i++;
                                    //*******************************************
                                    $pre_booking_status        =   $val['booking_status'];
                                    $pre_ticket_time_limit     =   $val['ticket_time_limit'];
                                    $pre_mf_reference          =  $val['mf_reference'];
                                    $pre_ticket_status         =   $val['ticket_status'];
                                    $fare_type                  =   $val['fare_type'];
                                    
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
                                ?>
                                    <tr>
                                        <td>
                                            <div class="chkbx">
                                                <input type="checkbox" name="passengers[]" value="<?php echo $val['id']; ?>" class="chkbox" id="<?php echo $checkboxId; ?>" data-firstname="<?php echo $val['first_name']; ?>" data-lastname="<?php echo $val['last_name']; ?>" data-title="<?php echo $val['title']; ?>" data-eticket="<?php echo $val['e_ticket_number']; ?>" data-passengertype="<?php echo $val['passenger_type']; ?>">
                                                <label for="<?php echo $checkboxId; ?>" class="mb-0"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($passenger_name); ?></strong>
                                            <br><small class="text-muted">Passenger Type: <?php echo ucfirst($val['passenger_type']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo !empty($val['e_ticket_number']) ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                <?php echo ucfirst($pre_ticket_status); ?>
                                            </span>
                                            <?php if (!empty($val['e_ticket_number'])): ?>
                                                <br><small class="text-muted">Ticket: <?php echo htmlspecialchars($val['e_ticket_number']); ?></small>
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
                
                <!-- Reissue Action Button -->
                <div class="reissue-actions mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button type="button" class="btn btn-primary btn-block" id="search-reissue-btn">
                                <i class="fas fa-search mr-2"></i>Search New Flights
                            </button>
                            <small class="text-muted d-block mt-1">Find alternative flights for selected passengers</small>
                        </div>
                    </div>
                </div>

                <!-- Hidden Search Form (shown when Search button is clicked) -->
                <div id="flight-search-form" style="display: none;" class="mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-plane mr-2"></i>Search New Flights</h6>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="precancelValue" name="mfreNum" value="<?php echo $pre_mf_reference; ?>">
                            <input type="hidden" name="bookingId" id="bookingId" value="<?php echo $bookingId; ?>">
                            <input type="hidden" name="userId" id="USerid" value="<?php echo $userId; ?>">
                            <input type="hidden" id="precancelsts" value="<?php echo $precancelsts; ?>">
                            <input type="hidden" name="flight_no_return" id="flight_no_return" value="<?php echo $flight_no_return; ?>">
                            <input type="hidden" name="selected-flights" id="flight-num" value="<?php echo isset($resultBooking[0]['flight_no']) ? $resultBooking[0]['flight_no'] : ''; ?>">
                            <input type="hidden" name="airline_code_return" id="airline_code_return" value="<?php echo $airline_code_return; ?>">
                            <input type="hidden" name="cabin_preference_return" id="cabin_preference_return" value="<?php echo $cabin_preference_return; ?>">
                            <input type="hidden" name="air_trip_type" id="air_trip_type" value="<?php echo $air_trip_type; ?>">
                            
                            <div class="flight-search-midbar mb-3">
                                <!-- Trip Type Selection -->
                                <div class="row px-3 mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label"><strong>Trip Type</strong></label>
                                        <div class="btn-group w-100" role="group" aria-label="Trip Type">
                                            <input type="radio" class="btn-check" name="trip_type_selection" id="oneWayTrip" value="OneWay" autocomplete="off" 
                                                   <?php echo $air_trip_type === 'OneWay' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary" for="oneWayTrip">
                                                <i class="fas fa-arrow-right mr-2"></i>One Way
                                            </label>

                                            <input type="radio" class="btn-check" name="trip_type_selection" id="roundTrip" value="Return" autocomplete="off" 
                                                   <?php echo $air_trip_type === 'Return' ? 'checked' : ''; ?>>
                                            <label class="btn btn-outline-primary" for="roundTrip">
                                                <i class="fas fa-exchange-alt mr-2"></i>Round Trip
                                            </label>
                                        </div>
                                        <small class="text-muted">You can change the trip type from your original booking</small>
                                    </div>
                                </div>

                                <!-- Route Information -->
                                <div class="row px-3 mb-3">
                                    <div class="col-md-6">
                                        <label>From</label>
                                        <input type="text" class="form-control" name="departure" value="<?php echo isset($dep_location) ? $dep_location : ''; ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label>To</label>
                                        <input type="text" class="form-control" name="arrival" value="<?php echo isset($arrivalairport) ? $arrivalairport : ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- Departure Date and Cabin Class -->
                                <div class="row px-3 mb-3">
                                    <div class="col-md-6">
                                        <label>New Departure Date</label>
                                        <input type="date" class="form-control" name="new_dep_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Cabin Class</label>
                                        <select class="form-control" name="cabin_class" id="unified_cabin_class">
                                            <option value="economy">Economy</option>
                                            <option value="premium">Premium Economy</option>
                                            <option value="business">Business Class</option>
                                            <option value="first">First Class</option>
                                        </select>
                                        <small class="text-muted">This will apply to all flight segments</small>
                                    </div>
                                </div>

                                <!-- Return Date (Initially Hidden) -->
                                <div class="row px-3 mb-3" id="return-date-section" style="display: <?php echo $air_trip_type === 'Return' ? 'flex' : 'none'; ?>;">
                                    <div class="col-md-6">
                                        <label>New Return Date</label>
                                        <input type="date" class="form-control" name="new_return_date" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info mb-0">
                                            <small><i class="fas fa-info-circle mr-1"></i>Same cabin class will be used for return flight</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <div class="row px-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success btn-block btn-lg">
                                            <i class="fas fa-search mr-2"></i>Search Available Flights
                                        </button>
                                    </div>
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
        // Select all checkbox functionality
        document.getElementById('changeDateAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.chkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = document.getElementById('changeDateAll').checked;
            });
        });

        // Individual checkbox change
        document.querySelectorAll('.chkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var allChecked = true;
                var checkboxes = document.querySelectorAll('.chkbox');
                checkboxes.forEach(function(cb) {
                    if (!cb.checked) {
                        allChecked = false;
                    }
                });
                document.getElementById('changeDateAll').checked = allChecked;
            });
        });

        // Search new flights button
        document.getElementById('search-reissue-btn').addEventListener('click', function() {
            var checkedBoxes = document.querySelectorAll('.chkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one passenger to proceed with reissue.');
                return;
            }
            document.getElementById('flight-search-form').style.display = 'block';
            // Scroll to the form
            document.getElementById('flight-search-form').scrollIntoView({ behavior: 'smooth' });
        });

        // Handle form submission with AJAX
        document.getElementById('flight-search_reissue').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected passengers
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

            console.log('Selected passengers:', selectedPassengers);

            if (selectedPassengers.length === 0) {
                alert('Please select at least one passenger.');
                return;
            }

            // Validate dates for round-trip
            var selectedTripType = document.querySelector('input[name="trip_type_selection"]:checked');
            var airTripType = selectedTripType ? selectedTripType.value : document.getElementById('air_trip_type').value;
            var newDepDate = document.querySelector('input[name="new_dep_date"]').value;
            var newReturnDate = document.querySelector('input[name="new_return_date"]');
            
            if (airTripType === 'Return' && newReturnDate) {
                var returnDateValue = newReturnDate.value;
                if (!returnDateValue) {
                    alert('Please select a return date for round-trip reissue.');
                    return;
                }
                
                // Check if return date is after departure date
                if (new Date(returnDateValue) <= new Date(newDepDate)) {
                    alert('Return date must be after departure date.');
                    return;
                }
            }

            // Get form data
            var formData = new FormData(this);
            formData.append('action', 'search_reissue_flights');
            formData.append('selected_passengers', JSON.stringify(selectedPassengers));

            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            // Show loading state
            var submitBtn = this.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Searching...';
            submitBtn.disabled = true;

            // Create results container if it doesn't exist
            var resultsContainer = document.getElementById('flight-search-results');
            if (!resultsContainer) {
                resultsContainer = document.createElement('div');
                resultsContainer.id = 'flight-search-results';
                resultsContainer.className = 'mt-4';
                document.getElementById('flight-search-form').after(resultsContainer);
            }

            // Make AJAX request
            fetch('ajax_reissue_search', {
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
                // Display results
                resultsContainer.innerHTML = data;
                resultsContainer.style.display = 'block';
                resultsContainer.scrollIntoView({ behavior: 'smooth' });
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                resultsContainer.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                resultsContainer.style.display = 'block';
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Redirect to previous page
        function redirectToPreviousPage() {
            window.history.back();
        }

        // Trip type selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            var oneWayRadio = document.getElementById('oneWayTrip');
            var roundTripRadio = document.getElementById('roundTrip');
            var returnDateSection = document.getElementById('return-date-section');
            var returnDateInput = document.querySelector('input[name="new_return_date"]');
            var airTripTypeHidden = document.getElementById('air_trip_type');

            // Show/hide return date section based on trip type
            function toggleReturnDateSection() {
                if (roundTripRadio.checked) {
                    returnDateSection.style.display = 'flex';
                    returnDateInput.setAttribute('required', 'required');
                    airTripTypeHidden.value = 'Return';
                } else {
                    returnDateSection.style.display = 'none';
                    returnDateInput.removeAttribute('required');
                    returnDateInput.value = '';
                    airTripTypeHidden.value = 'OneWay';
                }
            }

            // Event listeners for trip type change
            oneWayRadio.addEventListener('change', toggleReturnDateSection);
            roundTripRadio.addEventListener('change', toggleReturnDateSection);

            // Dynamic date validation for return trips
            var depDateInput = document.querySelector('input[name="new_dep_date"]');
            
            if (depDateInput && returnDateInput) {
                depDateInput.addEventListener('change', function() {
                    // Set minimum return date to be the day after departure date
                    var depDate = new Date(this.value);
                    var nextDay = new Date(depDate);
                    nextDay.setDate(depDate.getDate() + 1);
                    
                    var minReturnDate = nextDay.toISOString().split('T')[0];
                    returnDateInput.min = minReturnDate;
                    
                    // If current return date is before new minimum, clear it
                    if (returnDateInput.value && new Date(returnDateInput.value) <= depDate) {
                        returnDateInput.value = '';
                    }
                });
            }
        });
    </script>

    <?php require_once("includes/footer.php"); ?>
<?php } ?>