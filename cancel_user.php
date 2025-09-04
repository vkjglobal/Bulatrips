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
    include_once('includes/common_const.php');
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
                                <strong>Note:</strong> Only ticketed passengers can be selected for cancellation. 
                                Non-ticketed passengers will be disabled until tickets are issued.
                                <br><strong>Important:</strong> For bookings with multiple passengers, void operations may require PNR splitting. If void fails, try the refund option instead.
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
                                        $departureDate = date('Y-m-d', strtotime($passenger['dep_date']));
                                        
                                        // Status flags
                                        $voidStatus = $passenger['void_status'] ?? null;
                                        $isVoidInProcess = ($voidStatus === 'InProcess');
                                        // Prefer passenger-level ticket status if provided by query alias
                                        $passengerTicketStatus = $passenger['pass_ticket_status'] ?? ($passenger['status'] ?? ($passenger['ticket_status'] ?? null));
                                        $cbCancel = isset($passenger['cb_cancel_status']) ? intval($passenger['cb_cancel_status']) : null;
                                        $cbPtr = $passenger['cb_ptr_status'] ?? null;
                                        $isCancelled = ($passengerTicketStatus && strtolower($passengerTicketStatus) === 'cancelled')
                                            || ($voidStatus === 'Completed')
                                            || ($cbCancel === 1 || ($cbPtr && strtolower($cbPtr) === 'completed'));
                                        
                                        // Derive display ticket status
                                        if ($isCancelled) {
                                            $ticketStatus = 'Cancelled';
                                        } elseif (!empty($passenger['e_ticket_number'])) {
                                            $ticketStatus = 'Ticketed';
                                        } else {
                                            $ticketStatus = 'Not Ticketed';
                                        }
                                        
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
                                                    <?php if (!empty($passenger['e_ticket_number']) && !$isVoidInProcess && !$isCancelled): ?>
                                                    <input type="checkbox" name="passengers[]" value="<?php echo $passenger['id']; ?>" id="passenger_<?php echo $passenger['id']; ?>">
                                                    <label for="passenger_<?php echo $passenger['id']; ?>" class="mb-0"></label>
                                                    <?php elseif ($isVoidInProcess || $isCancelled): ?>
                                                        <input type="checkbox" disabled>
                                                        <label class="mb-0" style="opacity: 0.5;" title="<?php echo $isCancelled ? 'Cancelled' : 'Void in progress'; ?>"></label>
                                                    <?php else: ?>
                                                        <input type="checkbox" disabled>
                                                        <label class="mb-0" style="opacity: 0.5;"></label>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($passenger['title'] . ' ' . $passenger['first_name'] . ' ' . $passenger['last_name']); ?></strong>
                                                <br><small class="text-muted">Passenger Type: <?php echo ucfirst($passenger['passenger_type']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($isVoidInProcess): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        Void In Progress
                                                    </span>
                                                    <br><small class="text-muted">PTR ID: <?php echo htmlspecialchars($passenger['ptr_id'] ?? 'N/A'); ?></small>
                                                <?php else: ?>
                                                    <span class="badge <?php echo $isCancelled ? 'bg-danger' : (!empty($passenger['e_ticket_number']) ? 'bg-success' : 'bg-warning text-dark'); ?>">
                                                    <?php echo $ticketStatus; ?>
                                                </span>
                                                <?php if (!empty($passenger['e_ticket_number'])): ?>
                                                    <br><small class="text-muted">Ticket: <?php echo htmlspecialchars($passenger['e_ticket_number']); ?></small>
                                                    <?php endif; ?>
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

                        <!-- Smart Cancellation Action Button -->
                        <div class="cancellation-actions mb-3">
                            <?php
                            // Smart logic: Check if any passengers are ticketed
                            $hasTicketedPassengers = false;
                            $allPassengersNotTicketed = true;
                            
                            foreach ($bookCanusers as $passenger) {
                                if (!empty($passenger['e_ticket_number'])) {
                                    $hasTicketedPassengers = true;
                                    $allPassengersNotTicketed = false;
                                    break;
                                }
                            }
                            
                            // Smart button logic
                            if ($allPassengersNotTicketed) {
                                // No tickets issued yet - show message
                                ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Tickets Not Issued Yet</strong><br>
                                    Your tickets have not been issued yet. Please wait for ticket issuance before requesting cancellation.
                                </div>
                                <?php
                            } elseif ($hasTicketedPassengers) {
                                // Check void window for smart button
                                $firstTicketedPassenger = null;
                                foreach ($bookCanusers as $passenger) {
                                    if (!empty($passenger['e_ticket_number'])) {
                                        $firstTicketedPassenger = $passenger;
                                        break;
                                    }
                                }
                                
                                if ($firstTicketedPassenger) {
                                    $voidWindow = $firstTicketedPassenger['void_window'];
                                    $currentDateTime = new DateTime();
                                    $voidWindowActive = false;
                                    
                                    if (!empty($voidWindow)) {
                                        $voidWindowDateTime = new DateTime($voidWindow);
                                        $voidWindowActive = ($currentDateTime <= $voidWindowDateTime);
                                    }
                                    
                                    // Check if any passenger has void in progress
                                    $hasVoidInProgress = false;
                                    foreach ($bookCanusers as $passenger) {
                                        if (isset($passenger['void_status']) && $passenger['void_status'] === 'InProcess') {
                                            $hasVoidInProgress = true;
                                            break;
                                        }
                                    }
                                    
                                    // Smart button based on void status and window
                                    if ($hasVoidInProgress) {
                                        // Show void in progress message - hide all buttons
                                        ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Void Request In Progress</strong><br>
                                            Your void request is currently being processed. Please wait for completion before making any additional requests.
                                </div>
                                        <?php
                                    } elseif (MOCK_MODE || $voidWindowActive) {
                                        // In MOCK_MODE, always show both buttons for testing
                                        ?>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-danger btn-lg me-3" onclick="submitCancellation('void')">
                                        <i class="fas fa-ban mr-2"></i>Void Ticket
                                    </button>
                                            <button type="button" class="btn btn-info btn-lg" onclick="submitCancellation('refund')">
                                        <i class="fas fa-money-bill-wave mr-2"></i>Request Refund
                                    </button>
                                            <?php if (MOCK_MODE): ?>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-flask mr-1"></i>
                                                MOCK MODE: Both options available for testing
                                            </small>
                                            <?php else: ?>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-clock mr-1"></i>
                                                Void window active - minimal charges apply
                                            </small>
                                <?php endif; ?>
                            </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-info btn-lg" onclick="submitCancellation('refund')">
                                                <i class="fas fa-money-bill-wave mr-2"></i>Request Refund
                                            </button>
                                            <small class="text-muted d-block mt-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Void window expired - refund quote will be provided
                                            </small>
                            </div>
                                        <?php
                                    }
                                }
                            }
                            ?>
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

// Select all ticketed passengers checkbox functionality
document.getElementById('changeDateAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="passengers[]"]:not([disabled])');
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
        case 'void':
            confirmMessage = 'Are you sure you want to void the tickets for selected passengers? This action cannot be undone.';
            processVoidQuote(bookingId, passengerIds);
            break;
        case 'refund':
            confirmMessage = 'Would you like to get a refund quote for the selected passengers?';
            processRefundQuote(bookingId, passengerIds);
            break;
        default:
            Swal.fire({
                icon: 'error',
                title: 'Invalid Action',
                text: 'Please select a valid cancellation option.',
                confirmButtonColor: '#3085d6'
            });
            return;
    }
}

function processRefundQuote(bookingId, passengerIds) {
    // Get passenger details from DOM
    const passengerDetails = getSelectedPassengers(passengerIds);
    
    // Show loading
    Swal.fire({
        title: 'Getting Refund Quote...',
        text: 'Please wait while we calculate your refund amount.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
        didOpen: () => {
                        Swal.showLoading();
                    }
                });
    
    // Call RefundQuote API
    fetch('refund_post_ticket', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengerDetails: passengerDetails
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show refund confirmation in the same style as void
            showRefundConfirmation(data, bookingId, passengerIds);
        } else if (data.status === 'manual_required') {
            // Handle manual refund required
            Swal.fire({
                icon: 'info',
                title: data.title || 'Manual Refund Required',
                html: `
                    <div class="text-left">
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
                title: 'Refund Quote Error',
                text: data.message || 'An error occurred while processing your request.',
                confirmButtonColor: '#3085d6'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to get refund quote. Please try again.',
            confirmButtonColor: '#3085d6'
        });
    });
}


function showRefundConfirmation(quoteData, bookingId, passengerIds) {
    // Build refund-style confirmation mirroring void
    const dd = quoteData.data || {};
    const currency = dd.currency || dd.Currency || 'USD';
    const baseRefund = Number(dd.base_refund_amount || dd.TotalRefundAmount || 0);
    const refundBaseFee = Number(dd.refund_base_fee || 0);
    const refundAdditional = Number(dd.refund_additional_markup || 0);
    const serviceTotal = Number(dd.service_total || (refundBaseFee + refundAdditional));
    const finalRefund = Number(dd.final_refund_amount || Math.max(0, baseRefund - serviceTotal));
    const ptrId = dd.ptrId || quoteData.ptr_id || null;

    Swal.fire({
        title: 'Refund Quote Received',
        html: `
            <div class="text-left" style="margin-top:10px">
                <div style="font-size: 20px; font-weight: bold; color: #28a745; margin: 10px 0; text-align:center;">
                    Final Refund Amount: ${currency} ${finalRefund.toLocaleString()}
                </div>
                <hr/>
                <div style="font-size: 14px;">
                    <div><strong>Base Refund:</strong> ${currency} ${baseRefund.toLocaleString()}</div>
                    <div><strong>Refund Base Fee:</strong> - ${currency} ${refundBaseFee.toLocaleString()}</div>
                    <div><strong>Refund Additional markup:</strong> - ${currency} ${refundAdditional.toLocaleString()}</div>
                    <div><strong>Total Deductions:</strong> - ${currency} ${serviceTotal.toLocaleString()}</div>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Proceed with Refund',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            const payload = {
                ptrId,
                currency,
                baseRefund,
                refundBaseFee,
                refundAdditional,
                finalRefundAmount: finalRefund
            };
            processRefundAccept(bookingId, passengerIds, payload);
        }
    });
}

function processRefundAccept(bookingId, passengerIds, refundPayload = {}) {
    Swal.fire({
        title: 'Processing Refund Request...',
        text: 'Please wait while we process your refund request.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const selectedPassengers = getSelectedPassengers(passengerIds);

    fetch('accept_refund_quote', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengerDetails: selectedPassengers,
            ptr_id: refundPayload.ptrId || null,
            currency_from_quote: refundPayload.currency || 'USD',
            final_refund_amount: refundPayload.finalRefundAmount || 0
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'Refund Request Successful!',
                text: data.message || 'Your refund request has been submitted.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => location.reload());
        } else {
            Swal.fire({
                title: 'Refund Error',
                text: data.message || 'Unable to process refund.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(() => {
        Swal.fire({
            title: 'Network Error',
            text: 'Failed to submit the refund request. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
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

function processVoidQuote(bookingId, passengerIds) {
    // Show loading
    Swal.fire({
        title: 'Getting Void Quote...',
        text: 'Please wait while we get the void quote.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Get passenger details
    const selectedPassengers = getSelectedPassengers(passengerIds);
    
            fetch('cancel_post_ticket_process_VoidQuote', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengerDetails: selectedPassengers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show confirmation modal with quote details
            showVoidConfirmation(data, bookingId, passengerIds);
        } else {
            // Handle specific Split PNR error
            if (data.message && data.message.includes('Split PNR')) {
                Swal.fire({
                    title: 'Split PNR Required',
                    html: `
                        <div class="text-left">
                            <p><strong>This booking contains multiple passengers and needs to be split before voiding.</strong></p>
                            <p>Mystifly requires each passenger to be in a separate PNR for void operations.</p>
                            <p><strong>Options:</strong></p>
                            <ul>
                                <li>Contact support to split the PNR</li>
                                <li>Try voiding individual passengers</li>
                                <li>Use refund option instead</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    showCancelButton: true,
                    cancelButtonText: 'Try Refund Instead',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#28a745'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        // User chose to try refund instead
                        processRefundQuote(bookingId, passengerIds);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while getting the void quote.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function showVoidConfirmation(quoteData, bookingId, passengerIds) {
    // Handle both old and new response formats
    let refundAmount, currency;
    let baseRefund = 0, refundBaseFee = 0, refundAdditional = 0, serviceTotal = 0;
    
    if (quoteData.data) {
        // New response format (from mock_mystifly.php)
        // New structure returns detailed breakdown
        baseRefund = Number(quoteData.data.TotalRefundAmount || quoteData.data.base_refund_amount || 0);
        refundBaseFee = Number(quoteData.data.refund_base_fee || 0);
        refundAdditional = Number(quoteData.data.refund_additional_markup || 0);
        serviceTotal = Number(quoteData.data.service_total || (refundBaseFee + refundAdditional));
        const finalAmountNew = Number(quoteData.data.final_refund_amount || (baseRefund - serviceTotal));
        refundAmount = finalAmountNew;
        currency = quoteData.data.Currency || quoteData.data.currency || 'USD';
    } else {
        // Old response format (backward compatibility)
        baseRefund = Number(quoteData.base_refund_amount || 0);
        refundBaseFee = Number(quoteData.refund_base_fee || 0);
        refundAdditional = Number(quoteData.refund_additional_markup || 0);
        serviceTotal = Number(quoteData.service_total || (refundBaseFee + refundAdditional));
        const finalAmountOld = Number(quoteData.final_refund_amount || quoteData.refundamount || 0);
        refundAmount = finalAmountOld;
        currency = quoteData.currency || 'USD';
    }

    Swal.fire({
        title: 'Void Quote Received',
        html: `
            <div class="text-left" style="margin-top:10px">
                <div style="font-size: 20px; font-weight: bold; color: #28a745; margin: 10px 0; text-align:center;">
                    Final Refund Amount: ${currency} ${Number(refundAmount).toLocaleString()}
                </div>
                <hr/>
                <div style="font-size: 14px;">
                    <div><strong>Base Refund:</strong> ${currency} ${Number(baseRefund).toLocaleString()}</div>
                    <div><strong>Refund Base Fee:</strong> - ${currency} ${Number(refundBaseFee).toLocaleString()}</div>
                    <div><strong>Refund Additional markup:</strong> - ${currency} ${Number(refundAdditional).toLocaleString()}</div>
                    <div><strong>Total Deductions:</strong> - ${currency} ${Number(serviceTotal).toLocaleString()}</div>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Proceed with Void',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with actual void
            const payload = {
                ptrId: quoteData.ptr_id || null,
                currency,
                baseRefund,
                refundBaseFee,
                refundAdditional,
                finalRefundAmount: refundAmount
            };
            processVoidRequest(bookingId, passengerIds, payload);
        }
    });
}

function processVoidRequest(bookingId, passengerIds, voidPayload = {}) {
    // Show loading
    Swal.fire({
        title: 'Processing Void Request...',
        text: 'Please wait while we process your void request.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Get passenger details
    const selectedPassengers = getSelectedPassengers(passengerIds);
    
            fetch('cancel_post_ticket_process_Void', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            passengerDetails: selectedPassengers,
            ptr_id: voidPayload.ptrId || null,
            currency: voidPayload.currency || 'USD',
            base_refund_amount: voidPayload.baseRefund || 0,
            refund_base_fee: voidPayload.refundBaseFee || 0,
            refund_additional_markup: voidPayload.refundAdditional || 0,
            final_refund_amount: voidPayload.finalRefundAmount || 0
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'Void Request Successful!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            // Handle specific Split PNR error
            if (data.message && data.message.includes('Split PNR')) {
                Swal.fire({
                    title: 'Split PNR Required',
                    html: `
                        <div class="text-left">
                            <p><strong>This booking contains multiple passengers and needs to be split before voiding.</strong></p>
                            <p>Mystifly requires each passenger to be in a separate PNR for void operations.</p>
                            <p><strong>Options:</strong></p>
                            <ul>
                                <li>Contact support to split the PNR</li>
                                <li>Try voiding individual passengers</li>
                                <li>Use refund option instead</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    showCancelButton: true,
                    cancelButtonText: 'Try Refund Instead',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#28a745'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        // User chose to try refund instead
                        processRefundQuote(bookingId, passengerIds);
                    }
            });
        } else {
            Swal.fire({
                    title: 'Error',
                    text: data.message,
                icon: 'error',
                    confirmButtonText: 'OK'
            });
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing your request.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function getSelectedPassengers(passengerIds) {
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
    return passengerDetails;
}



</script>

<?php require_once("includes/footer.php"); ?>