<?php
session_start();
if (!isset($_SESSION['user_id'])) {
?>
    <script>
        window.location = "index"
    </script>
<?php
} else {
    require_once("includes/header.php");
    include_once('includes/class.cancel.php');
    $void_eligible = 2;
    $precancelsts = 0;
    $bookingId = $_GET['booking_id'];
    
    if (is_numeric($bookingId)) {
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE id = :bookingid');
    } else {
        $stmtbookingid = $conn->prepare('SELECT * FROM temp_booking WHERE mf_reference = :bookingid');
    }
    
    $stmtbookingid->execute(array('bookingid' => $bookingId));
    $bookingData = $stmtbookingid->fetch(PDO::FETCH_ASSOC);
    
    if (!$bookingData) {
        echo '<div class="alert alert-danger">Booking not found. Please check your booking reference and try again.</div>';
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $currentTimestamp = time();
    $objCancel = new Cancel();
    $bookCanusers = $objCancel->BookCancelUsers($bookingData['id'], $userId);
    
    if (empty($bookCanusers)) {
        echo '<div class="alert alert-warning">No passengers found for this booking.</div>';
        exit;
    }
}
?>
    <section>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center my-4">
                <h2 class="title-typ2 mb-0">Flight Cancellation</h2>
                <button type="button" class="btn btn-outline-secondary" onclick="redirectToPreviousPage()">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Booking Details
                </button>
            </div>
            <div class="row my-4">
                <div class="col-12">
                    <form action="" class="">
                        <div class="mb-3">
                            <h6 class="text-left fw-700">Do you want to cancel your Booking?</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Note:</strong> Cancellation eligibility depends on your ticket status and fare type. 
                                Select passengers below to proceed with cancellation.
                            </div>
                        </div>

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
                                    $hasTicketedPassengers = false;
                                    $hasNonRefundablePassengers = false;
                                    $allPassengersVoidEligible = true;
                                    $currentDate = date('Y-m-d');
                                    
                                    foreach ($bookCanusers as $passenger) {
                                        $ticketStatus = !empty($passenger['e_ticket_number']) ? 'Ticketed' : 'Not Ticketed';
                                        $departureDate = date('Y-m-d', strtotime($passenger['dep_date']));
                                        
                                        // Check if passenger is ticketed
                                        if (!empty($passenger['e_ticket_number'])) {
                                            $hasTicketedPassengers = true;
                                            
                                            // Check void eligibility (same day as booking)
                                            $bookingDate = date('Y-m-d', strtotime($bookingData['booking_date']));
                                            if ($currentDate != $bookingDate) {
                                                $allPassengersVoidEligible = false;
                                            }
                                        }
                                        
                                        // Check fare rules for refundability
                                        if (isset($passenger['fare_type']) && $passenger['fare_type'] == 'Non-Refundable') {
                                            $hasNonRefundablePassengers = true;
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="chkbx">
                                                    <input type="checkbox" name="passengers[]" value="<?php echo $passenger['id']; ?>" id="passenger_<?php echo $passenger['id']; ?>">
                                                    <label for="passenger_<?php echo $passenger['id']; ?>" class="mb-0"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($passenger['title'] . ' ' . $passenger['first_name'] . ' ' . $passenger['last_name']); ?></strong>
                                                <br><small class="text-muted">Passenger Type: <?php echo ucfirst($passenger['passenger_type']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo !empty($passenger['e_ticket_number']) ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                    <?php echo $ticketStatus; ?>
                                                </span>
                                                <?php if (!empty($passenger['e_ticket_number'])): ?>
                                                    <br><small class="text-muted">Ticket: <?php echo htmlspecialchars($passenger['e_ticket_number']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($passenger['dep_date'])); ?>
                                                <br><small class="text-muted">Departure</small>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Cancellation Action Buttons -->
                        <div class="cancellation-actions mb-3">
                            <?php
                            // Determine which buttons to show based on passenger status
                            $showPreCancel = false;
                            $showVoid = false;
                            $showRefund = false;
                            
                            // Check for non-ticketed passengers (pre-cancellation)
                            foreach ($bookCanusers as $passenger) {
                                if (empty($passenger['e_ticket_number'])) {
                                    $showPreCancel = true;
                                    break;
                                }
                            }
                            
                            // Check for ticketed passengers
                            if ($hasTicketedPassengers) {
                                $bookingDate = date('Y-m-d', strtotime($bookingData['booking_date']));
                                $currentDate = date('Y-m-d');
                                
                                // Show void button if booking was made today
                                if ($currentDate == $bookingDate) {
                                    $showVoid = true;
                                }
                                
                                // Always show refund option for ticketed passengers
                                $showRefund = true;
                            }
                            ?>
                            
                            <div class="row">
                                <?php if ($showPreCancel): ?>
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-warning btn-block" onclick="submitCancellation('precancel')">
                                        <i class="fas fa-times-circle mr-2"></i>Pre-Cancel Booking
                                    </button>
                                    <small class="text-muted d-block mt-1">For non-ticketed passengers</small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($showVoid): ?>
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-danger btn-block" onclick="submitCancellation('void')">
                                        <i class="fas fa-ban mr-2"></i>Void Ticket
                                    </button>
                                    <small class="text-muted d-block mt-1">Same day cancellation</small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($showRefund): ?>
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-info btn-block" onclick="submitCancellation('refund')">
                                        <i class="fas fa-money-bill-wave mr-2"></i>Request Refund
                                    </button>
                                    <small class="text-muted d-block mt-1">Get quote for refund</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!$showPreCancel && !$showVoid && !$showRefund): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No cancellation options available for this booking at this time.
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

<script>
function redirectToPreviousPage() {
    window.history.back();
}

// Select all passengers checkbox functionality
document.getElementById('changeDateAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="passengers[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function submitCancellation(type) {
    const selectedPassengers = document.querySelectorAll('input[name="passengers[]"]:checked');
    
    if (selectedPassengers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Passengers Selected',
            text: 'Please select at least one passenger to proceed with cancellation.',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    const passengerIds = Array.from(selectedPassengers).map(cb => cb.value);
    const bookingId = '<?php echo $bookingId; ?>';
    
    let confirmMessage = '';
    let processUrl = '';
    
    switch(type) {
        case 'precancel':
            confirmMessage = 'Are you sure you want to pre-cancel the selected passengers? This will send a request to MystiFly API.';
            processUrl = 'cancel_post_ticket_process.php';
            break;
        case 'void':
            confirmMessage = 'Are you sure you want to void the tickets for selected passengers? This action cannot be undone.';
            processUrl = `cancel_post_ticket_process_Void.php?booking_id=${bookingId}&passengers=${passengerIds.join(',')}&type=void`;
            break;
        case 'refund':
            confirmMessage = 'Would you like to get a refund quote for the selected passengers?';
            processUrl = `refund_post_ticket.php?booking_id=${bookingId}&passengers=${passengerIds.join(',')}&type=refund`;
            break;
    }
    
    Swal.fire({
        title: 'Confirm Cancellation',
        text: confirmMessage,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            if (type === 'precancel') {
                // Use AJAX for pre-cancellation
                processPreCancellation(bookingId, passengerIds);
            } else if (type === 'refund') {
                // Use AJAX for refund quote request
                processRefundQuote(bookingId, passengerIds);
            } else {
                // For void, use existing redirect method
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process your cancellation request.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                window.location.href = processUrl;
            }
        }
    });
}

function processPreCancellation(bookingId, passengerIds) {
    // Show loading
    Swal.fire({
        title: 'Processing Pre-Cancellation...',
        text: 'Sending request to MystiFly API. Please wait...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // AJAX call to pre-cancellation processor  
    fetch('cancel_post_ticket_process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengers: passengerIds,
            type: 'precancel'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Pre-Cancellation Successful!',
                html: `
                    <p>${data.message}</p>
                    <p><strong>Cancelled Passengers:</strong> ${data.data.cancelledCount}</p>
                    ${data.data.remainingPassengers === 0 ? '<p><strong>Entire booking has been cancelled.</strong></p>' : `<p><strong>Remaining Passengers:</strong> ${data.data.remainingPassengers}</p>`}
                `,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Go to Dashboard'
            }).then(() => {
                window.location.href = 'user-dashboard.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Pre-Cancellation Failed',
                text: data.message || 'An error occurred while processing your request.',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            confirmButtonColor: '#d33'
        });
    });
}

function processRefundQuote(bookingId, passengerIds) {
    // Show loading
    Swal.fire({
        title: 'Processing Refund Quote...',
        text: 'Sending request to MystiFly API. Please wait...',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Get passenger details from the table
    const passengerDetails = [];
    passengerIds.forEach(id => {
        const row = document.querySelector(`input[name="passengers[]"][value="${id}"]`).closest('tr');
        const nameCell = row.querySelector('td:nth-child(2)');
        const nameText = nameCell.querySelector('strong').textContent;
        const passengerType = nameCell.querySelector('small').textContent.replace('Passenger Type: ', '');
        
        // Parse name
        const nameParts = nameText.split(' ');
        const title = nameParts[0];
        const firstName = nameParts[1];
        const lastName = nameParts.slice(2).join(' ');
        
        // Get ticket number from the third column
        const ticketCell = row.querySelector('td:nth-child(3)');
        const ticketSmall = ticketCell.querySelector('small');
        const eTicket = ticketSmall ? ticketSmall.textContent.replace('Ticket: ', '') : '';
        
        passengerDetails.push({
            firstname: firstName,
            lastname: lastName,
            title: title,
            eticket: eTicket,
            passengertype: passengerType
        });
    });
    
    // AJAX call to refund processor  
    fetch('refund_post_ticket', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengers: passengerDetails,
            type: 'refund'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Refund Quote Successful!',
                html: `
                    <p>${data.message}</p>
                    ${data.data.ptrId ? `<p><strong>PTR ID:</strong> ${data.data.ptrId}</p>` : ''}
                    ${data.data.totalRefundAmount ? `<p><strong>Total Refund Amount:</strong> ${data.data.currency} ${data.data.totalRefundAmount}</p>` : ''}
                    ${data.data.ptrStatus ? `<p><strong>Status:</strong> ${data.data.ptrStatus}</p>` : ''}
                    ${data.data.slaMinutes > 0 ? `<p><strong>Expected completion:</strong> ${Math.ceil(data.data.slaMinutes/60)} hours</p>` : ''}
                `,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Go to Dashboard'
            }).then(() => {
                window.location.href = 'user-dashboard.php';
            });
        } else if (data.status === 'manual_required') {
            Swal.fire({
                icon: 'info',
                title: data.title || 'Manual Refund Required',
                html: `
                    <div style="text-align: left;">
                        <p><strong>${data.message}</strong></p>
                        <p style="color: #28a745;"><strong>${data.action_required}</strong></p>
                        <hr>
                        <h6>Next Steps:</h6>
                        <ul style="text-align: left; padding-left: 20px;">
                            ${data.next_steps ? data.next_steps.map(step => `<li>${step}</li>`).join('') : ''}
                        </ul>
                        <hr>
                        <p><strong>Reference:</strong> ${data.support_info.reference}</p>
                        <p><strong>Support:</strong> ${data.support_info.email}</p>
                    </div>
                `,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Go to Dashboard',
                showCancelButton: true,
                cancelButtonText: 'Contact Support',
                cancelButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'user-dashboard.php';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location.href = 'mailto:support@bulatrips.com?subject=Refund Request - ' + data.support_info.reference;
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Refund Quote Failed',
                text: data.message || 'An error occurred while processing your request.',
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            confirmButtonColor: '#d33'
        });
    });
}
</script>

<?php require_once("includes/footer.php"); ?>