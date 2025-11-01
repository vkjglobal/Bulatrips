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
                                <strong>Note:</strong> Cancellation will apply to all ticketed passengers in this booking. 
                                Non-ticketed passengers will be excluded automatically.
                                
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <h6 class="text-left fw-700">Travellers in this Booking</h6>
                            <table id="psngr" class="table table-bordered white-bg text-left fs-14" style="min-width: 500px;">
                                <thead>
                                    <tr class="dark-blue-bg white-txt">
                                        <th style="width: 40%;">Passenger Name</th>
                                        <th style="width: 30%;">Ticket Status</th>
                                        <th style="width: 30%;">Departure Date</th>
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
                                                <strong><?php echo htmlspecialchars($passenger['title'] . ' ' . $passenger['first_name'] . ' ' . $passenger['last_name']); ?></strong>
                                                <br><small class="text-muted">Passenger Type: <?php echo ucfirst($passenger['passenger_type']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($isVoidInProcess): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        Void/Cancel In Progress
                                                    </span>
                                                    <?php if (!empty($passenger['ptr_id'])): ?>
                                                    <br><small class="text-muted">PTR ID: <?php echo htmlspecialchars($passenger['ptr_id']); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge <?php echo $isCancelled ? 'bg-danger text-white' : (!empty($passenger['e_ticket_number']) ? 'bg-success' : 'bg-danger text-white'); ?>">
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
                            // Determine if any passenger is ticketed AND still eligible (not cancelled and not in-process)
                            $hasAvailableTicketed = false;
                            $firstTicketedPassenger = null;
                            foreach ($bookCanusers as $passenger) {
                                $voidStatusIter = $passenger['void_status'] ?? null;
                                $passengerTicketStatusIter = $passenger['pass_ticket_status'] ?? ($passenger['status'] ?? ($passenger['ticket_status'] ?? null));
                                $cbCancelIter = isset($passenger['cb_cancel_status']) ? intval($passenger['cb_cancel_status']) : null;
                                $cbPtrIter = $passenger['cb_ptr_status'] ?? null;
                                $isCancelledIter = ($passengerTicketStatusIter && strtolower($passengerTicketStatusIter) === 'cancelled')
                                    || ($voidStatusIter === 'Completed')
                                    || ($cbCancelIter === 1 || ($cbPtrIter && strtolower($cbPtrIter) === 'completed'));
                                if (!empty($passenger['e_ticket_number']) && !$isCancelledIter && $voidStatusIter !== 'InProcess') {
                                    $hasAvailableTicketed = true;
                                    $firstTicketedPassenger = $passenger;
                                    break;
                                }
                            }
                            
                            // Smart button logic
                            if (!$hasAvailableTicketed) {
                                // No eligible ticketed passengers → hide buttons
                                ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>No Eligible Tickets</strong><br>
                                    All passengers are either not ticketed or already cancelled.
                                </div>
                                <?php
                            } else {
                                // Check void window for smart button
                                
                                if ($firstTicketedPassenger) {
                                    $voidWindow = $firstTicketedPassenger['void_window'];
                                    $voidWindowActive = false;
                                    
                                    if (!empty($voidWindow)) {
                                        // Force UTC timezone for consistent worldwide calculations
                                        $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
                                        $voidWindowDateTime = new DateTime($voidWindow, new DateTimeZone('UTC'));
                                        $voidWindowActive = ($currentDateTime <= $voidWindowDateTime);
                                        
                                        // Debug log (can be removed in production)
                                        error_log("Booking {$bookingData['id']} - Void Window: $voidWindow");
                                        error_log("Booking {$bookingData['id']} - Current UTC: " . $currentDateTime->format('Y-m-d H:i:s'));
                                        error_log("Booking {$bookingData['id']} - Void Window UTC: " . $voidWindowDateTime->format('Y-m-d H:i:s'));
                                        error_log("Booking {$bookingData['id']} - Void Window Active: " . ($voidWindowActive ? 'YES' : 'NO'));
                                    }
                                    
                                    // Determine if there is at least one selectable ticketed passenger
                                    $hasVoidInProgress = false;
                                    $hasAvailableTicketed = false;
                                    foreach ($bookCanusers as $passenger) {
                                        $voidStatusIter = $passenger['void_status'] ?? null;
                                        if ($voidStatusIter === 'InProcess') {
                                            $hasVoidInProgress = true;
                                        }
                                        $passengerTicketStatusIter = $passenger['pass_ticket_status'] ?? ($passenger['status'] ?? ($passenger['ticket_status'] ?? null));
                                        $cbCancelIter = isset($passenger['cb_cancel_status']) ? intval($passenger['cb_cancel_status']) : null;
                                        $cbPtrIter = $passenger['cb_ptr_status'] ?? null;
                                        $isCancelledIter = ($passengerTicketStatusIter && strtolower($passengerTicketStatusIter) === 'cancelled')
                                            || ($voidStatusIter === 'Completed')
                                            || ($cbCancelIter === 1 || ($cbPtrIter && strtolower($cbPtrIter) === 'completed'));
                                        if (!empty($passenger['e_ticket_number']) && !$isCancelledIter && $voidStatusIter !== 'InProcess') {
                                            $hasAvailableTicketed = true;
                                            break;
                                        }
                                    }

                                    // Smart button based on availability, void status, and window
                                    if (!$hasAvailableTicketed && $hasVoidInProgress) {
                                        ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-clock mr-2"></i>
                                            <strong>Void/Cancel Request In Progress</strong><br>
                                            Your void/cancel request is currently being processed. Please wait for completion before making any additional requests.
                                        </div>
                                        <?php
                    } elseif ($voidWindowActive) {
                        // Void window is active - show only Void button
                        ?>
                        <div class="text-center">
                            <button type="button" class="btn btn-danger btn-lg" onclick="submitCancellation('void')">
                                <i class="fas fa-ban mr-2"></i>Void/Cancel Ticket
                            </button>
                            <div class="void-window-timer mt-2">
                                <small class="text-success d-block">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Void window active</strong> - Minimal or no cancellation charges
                                </small>
                                <div class="countdown-timer mt-1">
                                    <small class="text-warning fw-bold">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        Time remaining: <span id="voidCountdown" class="countdown-display">Calculating...</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        // Void window expired - show only Refund button
                        ?>
                        <div class="text-center">
                            <button type="button" class="btn btn-info btn-lg" onclick="submitCancellation('refund')">
                                <i class="fas fa-money-bill-wave mr-2"></i>Request Refund/Cancel
                            </button>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Void window expired - Standard refund charges will apply
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

<style>
.countdown-timer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 8px;
}

.countdown-display {
    font-family: 'Courier New', monospace;
    font-size: 14px;
    letter-spacing: 1px;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(0, 0, 0, 0.1);
    display: inline-block;
    min-width: 80px;
    text-align: center;
}

.void-window-timer {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
}

.void-window-timer .text-warning {
    color: #856404 !important;
}

.void-window-timer .text-danger {
    color: #721c24 !important;
}

.void-window-timer .text-success {
    color: #155724 !important;
}

/* Animation for countdown */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.countdown-display.text-danger {
    animation: pulse 1s infinite;
}

.urgent-warning {
    animation: pulse 1.5s infinite;
    border-left: 4px solid #ffc107;
}
</style>

<script>
function redirectToPreviousPage() {
    window.history.back();
}

// Function to get all ticketed passengers from the table
function getAllTicketedPassengers() {
    const passengers = [];
    const rows = document.querySelectorAll('#psngr tbody tr');
    
    rows.forEach((row, index) => {
        const nameCell = row.querySelector('td:nth-child(1)');
        const statusCell = row.querySelector('td:nth-child(2)');
        
        if (nameCell && statusCell) {
            const nameText = nameCell.querySelector('strong').textContent;
            const passengerType = nameCell.querySelector('small').textContent.replace('Passenger Type: ', '');
            
            // Check if passenger is ticketed (not cancelled and not in process)
            const statusBadge = statusCell.querySelector('.badge');
            const isTicketed = statusBadge && 
                              statusBadge.textContent.trim() === 'Ticketed' && 
                              !statusBadge.classList.contains('bg-danger') &&
                              !statusBadge.classList.contains('bg-warning');
            
            if (isTicketed) {
                // Get ticket number
                const ticketSmall = statusCell.querySelector('small');
                const eTicket = ticketSmall ? ticketSmall.textContent.replace('Ticket: ', '') : '';
                
                // Parse name
                const nameParts = nameText.split(' ');
                const title = nameParts[0];
                const firstName = nameParts[1];
                const lastName = nameParts.slice(2).join(' ');
                
                passengers.push({
                    firstname: firstName,
                    lastname: lastName,
                    title: title,
                    eticket: eTicket,
                    passengertype: passengerType,
                    id: index + 1 // Use row index as ID
                });
            }
        }
    });
    
    return passengers;
}

// No checkbox functionality needed - all passengers will be included automatically

function submitCancellation(type) {
    const bookingId = '<?php echo $bookingId; ?>';
    
    // Get all ticketed passengers from the table
    const allPassengers = getAllTicketedPassengers();
    
    if (allPassengers.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Ticketed Passengers',
            text: 'No ticketed passengers found in this booking. Cancellation cannot be processed.',
            confirmButtonColor: '#3085d6'
        });
        return;
    }
    
    let confirmMessage = '';
    
    switch(type) {
        case 'void':
            confirmMessage = `Are you sure you want to void the tickets for all ${allPassengers.length} ticketed passenger(s)? This action cannot be undone.`;
            Swal.fire({
                title: 'Confirm Void Request',
                text: confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Void All Tickets',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processVoidQuote(bookingId, allPassengers);
                }
            });
            break;
        case 'refund':
            confirmMessage = `Would you like to get a refund quote for all ${allPassengers.length} ticketed passenger(s)?`;
            Swal.fire({
                title: 'Confirm Refund Request',
                text: confirmMessage,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Get Refund Quote',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    processRefundQuote(bookingId, allPassengers);
                }
            });
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
            // Check workflow type
            const refundType = data.refund_type || '';
            const workflow = data.data?.workflow || '';
            const expectedEmailTime = data.data?.expected_email_time_utc || '';
            const slaHours = data.data?.slaHours || '1';
            const ptrId = data.data?.ptrId || '';
            
            if (workflow === 'email_based' || refundType === 'refund_quote_submitted') {
                // Email-based workflow - Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Refund Quote Requested!',
                    html: `
                        <div style="text-align: left; padding: 15px;">
                            <p style="margin-bottom: 15px;">${data.message}</p>
                            
                            <div style="background:#e7f3ff; border-left:4px solid #0d6efd; padding:12px; margin:15px 0; border-radius:4px;">
                                <p style="margin:0 0 8px 0; font-weight:bold; color:#084298;">
                                    <i class="fas fa-envelope"></i> What's Next?
                                </p>
                                <ul style="margin:0; padding-left:20px; font-size:14px; color:#084298;">
                                    <li>You will receive an email with refund quote details</li>
                                    <li>Email will include accept/decline options</li>
                                    <li>Review the breakdown and choose your action</li>
                                </ul>
                            </div>
                            
                            ${expectedEmailTime ? `
                            <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:12px; margin:15px 0; border-radius:4px;">
                                <p style="margin:0 0 6px 0; font-weight:bold; color:#856404;">
                                    ⏰ Expected Email Time
                                </p>
                                <div style="font-size:13px; color:#856404;">${expectedEmailTime}</div>
                                <p style="margin:8px 0 0 0; font-size:12px; color:#666; font-style:italic;">
                                    Processing usually takes ${slaHours} hour(s)
                                </p>
                            </div>
                            ` : ''}
                            
                            <div style="margin-top:20px; padding:12px; background:#f8f9fa; border-radius:4px;">
                                <p style="margin:0; font-size:13px; color:#666;">
                                    <strong>PTR ID:</strong> ${ptrId}<br>
                                    <small>Save this for your reference</small>
                                </p>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#0029ff'
                }).then(() => {
                    location.reload();
                });
            } else {
                // Instant quote (VoidQuote or fallback)
                showRefundConfirmation(data, bookingId, passengerIds);
            }
        } else if (data.status === 'duplicate_request') {
            // Handle duplicate request
            Swal.fire({
                icon: 'warning',
                title: 'Request Already Pending',
                html: `
                    <div style="text-align: left; padding: 15px;">
                        <p>${data.message}</p>
                        <div style="background:#fff3cd; padding:12px; margin:15px 0; border-radius:4px;">
                            <p style="margin:0 0 6px 0;"><strong>Existing PTR ID:</strong> ${data.existing_ptr_id || 'N/A'}</p>
                            <p style="margin:0;"><strong>Submitted:</strong> ${data.submitted_at || 'N/A'}</p>
                        </div>
                        <p style="font-size:14px; color:#666;">${data.note || 'Please check your email or wait for the quote to be processed.'}</p>
                    </div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0029ff'
            });
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
            // Show raw Mystifly error message with both request and response
            let errorHtml = `<div class="text-left">`;
            errorHtml += `<p><strong>Mystifly API Error:</strong></p>`;
            errorHtml += `<p style="color: #d33; font-weight: bold;">${data.message || 'An error occurred while processing your request.'}</p>`;
            
            if (data.raw_request) {
                errorHtml += `<details style="margin-top: 15px;">`;
                errorHtml += `<summary style="cursor: pointer; color: #666;">📤 View Raw Request Sent to Mystifly</summary>`;
                errorHtml += `<pre style="background: #e3f2fd; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #2196f3;">${JSON.stringify(data.raw_request, null, 2)}</pre>`;
                errorHtml += `</details>`;
            }
            
            if (data.raw_response) {
                errorHtml += `<details style="margin-top: 15px;">`;
                errorHtml += `<summary style="cursor: pointer; color: #666;">📥 View Raw Response from Mystifly</summary>`;
                errorHtml += `<pre style="background: #ffebee; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #f44336;">${JSON.stringify(data.raw_response, null, 2)}</pre>`;
                errorHtml += `</details>`;
            }
            
            errorHtml += `</div>`;
            
            Swal.fire({
                title: 'Mystifly API Error',
                html: errorHtml,
                icon: 'error',
                confirmButtonText: 'OK',
                width: '700px'
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

// ===============================================================================
// FRONTEND POLLING REMOVED - Now using email-based workflow via cron jobs
// RefundQuote status is checked by CronJob/cronRefundQuoteStatus.php
// Customer receives email when quote is ready with accept/decline links
// This provides better UX as user doesn't need to wait on page
// ===============================================================================


function showRefundConfirmation(quoteData, bookingId, passengerIds) {
    // Build refund-style confirmation mirroring void
    const dd = quoteData.data || {};
    const currency = dd.currency || dd.Currency || 'USD';
    const baseRefund = Number(dd.base_refund_amount || dd.totalRefundAmount || dd.TotalRefundAmount || 0);
    const refundBaseFee = Number(dd.refund_base_fee || 0);
    const refundAdditional = Number(dd.refund_additional_markup || 0);
    const ipgAmount = Number(dd.ipg_amount || 0);
    const serviceTotal = Number(dd.service_total || (refundBaseFee + refundAdditional + ipgAmount));
    const finalRefund = Number(dd.final_refund_amount || dd.totalRefundAmount || Math.max(0, baseRefund - serviceTotal));
    const ptrId = dd.ptrId || quoteData.ptr_id || null;

    Swal.fire({
        title: 'Refund Quote Received',
        html: `
            <div class="text-left" style="margin-top:10px">
                <div style="font-size: 20px; font-weight: bold; color: #28a745; margin: 10px 0; text-align:center;">
                    Final Refund Amount: ${currency} ${finalRefund.toFixed(2)}
                </div>
                <hr/>
                <div style="font-size: 14px;">
                    <div><strong>Base Refund:</strong> ${currency} ${baseRefund.toFixed(2)}</div>
                    <div><strong>Refund Base Fee:</strong> - ${currency} ${refundBaseFee.toFixed(2)}</div>
                    <div><strong>Refund Additional markup:</strong> - ${currency} ${refundAdditional.toFixed(2)}</div>
                    <div><strong>IPG Transaction Fee:</strong> - ${currency} ${ipgAmount.toFixed(2)}</div>
                    <div><strong>Total Deductions:</strong> - ${currency} ${serviceTotal.toFixed(2)}</div>
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
            const statusText = data.ptr_status || 'InProcess';
            const currencyDisp = refundPayload.currency || 'USD';
            const amountDisp = Number(refundPayload.finalRefundAmount || 0).toFixed(2);
            Swal.fire({
                title: `Your Refund is: ${statusText}`,
                html: `<div style="text-align:left;">`
                    + `<div><strong>Total Refundable Amount is:</strong> ${currencyDisp} ${amountDisp}</div>`
                    + `<div style="margin-top:8px;">Please check your email for further details.</div>`
                    + `</div>`,
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
            user_id: '<?php echo $userId; ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show confirmation modal with quote details
            showVoidConfirmation(data, bookingId, passengerIds);
        } else if (data.error_type === 'void_window_expired') {
            // Handle expired void window with option to use refund
            Swal.fire({
                icon: 'warning',
                title: 'Void Window Expired',
                html: `
                    <div class="text-left" style="padding: 15px;">
                        <p style="margin-bottom: 15px; font-size: 15px;">${data.message}</p>
                        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; border-radius: 4px;">
                            <p style="margin: 0 0 8px 0; font-weight: bold; color: #856404;">
                                <i class="fas fa-exclamation-triangle"></i> Timeline Details
                            </p>
                            <div style="margin: 4px 0; font-size: 14px; color: #856404;">
                                <strong>Void Deadline Was:</strong> ${data.void_window_deadline || 'N/A'}
                            </div>
                            <div style="margin: 4px 0; font-size: 14px; color: #856404;">
                                <strong>Current Time:</strong> ${data.current_time || 'N/A'}
                            </div>
                            ${data.expired_hours_ago ? `<div style="margin: 8px 0 0 0; font-size: 13px; color: #dc3545;">
                                Expired ${data.expired_hours_ago} hours ago
                            </div>` : ''}
                        </div>
                        <p style="margin-top: 15px; font-size: 14px;">
                            <strong>Alternative Option:</strong> You can request a refund instead of void.
                        </p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-undo"></i> Request Refund Instead',
                cancelButtonText: 'Close',
                confirmButtonColor: '#0029ff',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to refund flow
                    processRefundQuote(bookingId, passengerIds);
                }
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
                // Show raw Mystifly error message with both request and response
                let errorHtml = `<div class="text-left">`;
                errorHtml += `<p><strong>Mystifly API Error:</strong></p>`;
                errorHtml += `<p style="color: #d33; font-weight: bold;">${data.message}</p>`;
                
                if (data.raw_request) {
                    errorHtml += `<details style="margin-top: 15px;">`;
                    errorHtml += `<summary style="cursor: pointer; color: #666;">📤 View Raw Request Sent to Mystifly</summary>`;
                    errorHtml += `<pre style="background: #e3f2fd; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #2196f3;">${JSON.stringify(data.raw_request, null, 2)}</pre>`;
                    errorHtml += `</details>`;
                }
                
                if (data.raw_response) {
                    errorHtml += `<details style="margin-top: 15px;">`;
                    errorHtml += `<summary style="cursor: pointer; color: #666;">📥 View Raw Response from Mystifly</summary>`;
                    errorHtml += `<pre style="background: #ffebee; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #f44336;">${JSON.stringify(data.raw_response, null, 2)}</pre>`;
                    errorHtml += `</details>`;
                }
                
                errorHtml += `</div>`;
                
                Swal.fire({
                    title: 'Mystifly API Error',
                    html: errorHtml,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    width: '700px'
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
    let baseRefund = 0, refundBaseFee = 0, refundAdditional = 0, ipgAmount = 0, serviceTotal = 0;
    let voidWindowUTC = '', remainingVoidTime = '', voidDeadline = '';
    
    if (quoteData.data) {
        // New response format (from mock_mystifly.php)
        // New structure returns detailed breakdown
        baseRefund = Number(quoteData.data.TotalRefundAmount || quoteData.data.base_refund_amount || 0);
        refundBaseFee = Number(quoteData.data.refund_base_fee || 0);
        refundAdditional = Number(quoteData.data.refund_additional_markup || 0);
        ipgAmount = Number(quoteData.data.ipg_amount || 0);
        serviceTotal = Number(quoteData.data.service_total || (refundBaseFee + refundAdditional + ipgAmount));
        const finalAmountNew = Number(quoteData.data.final_refund_amount || (baseRefund - serviceTotal));
        refundAmount = finalAmountNew;
        currency = quoteData.data.Currency || quoteData.data.currency || 'USD';
        
        // Extract void window information
        voidWindowUTC = quoteData.data.voiding_window_utc || '';
        remainingVoidTime = quoteData.data.remaining_void_time || '';
        voidDeadline = quoteData.data.void_deadline_formatted || quoteData.data.voiding_window || '';
    } else {
        // Old response format (backward compatibility)
        baseRefund = Number(quoteData.base_refund_amount || 0);
        refundBaseFee = Number(quoteData.refund_base_fee || 0);
        refundAdditional = Number(quoteData.refund_additional_markup || 0);
        ipgAmount = Number(quoteData.ipg_amount || 0);
        serviceTotal = Number(quoteData.service_total || (refundBaseFee + refundAdditional + ipgAmount));
        const finalAmountOld = Number(quoteData.final_refund_amount || quoteData.refundamount || 0);
        refundAmount = finalAmountOld;
        currency = quoteData.currency || 'USD';
        
        voidWindowUTC = quoteData.voiding_window_utc || '';
        remainingVoidTime = quoteData.remaining_void_time || '';
        voidDeadline = quoteData.voiding_window || '';
    }
    
    // Build void window info section if available
    let voidWindowSection = '';
    if (voidWindowUTC || remainingVoidTime) {
        voidWindowSection = `
            <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:12px; margin:15px 0; border-radius:4px;">
                <h6 style="margin:0 0 8px 0; color:#856404; font-weight:bold;">
                    <i class="fas fa-clock"></i> Void Window Information
                </h6>
                ${voidWindowUTC ? `<div style="margin:4px 0; font-size:13px; color:#856404;">
                    <strong>Void Deadline:</strong> ${voidWindowUTC}
                </div>` : ''}
                ${remainingVoidTime ? `<div style="margin:4px 0; font-size:13px; color:#856404;">
                    <strong>Time Remaining:</strong> ${remainingVoidTime}
                </div>` : ''}
                <div style="margin:8px 0 0 0; font-size:12px; color:#666; font-style:italic;">
                    ⚡ VoidQuote is instant (No SLA wait time)
                </div>
            </div>
        `;
    }

    Swal.fire({
        title: 'Void Quote Received',
        html: `
            <div class="text-left" style="margin-top:10px">
                ${voidWindowSection}
                <div style="font-size: 20px; font-weight: bold; color: #28a745; margin: 10px 0; text-align:center;">
                    Final Refund Amount: ${currency} ${Number(refundAmount).toFixed(2)}
                </div>
                <hr/>
                <div style="font-size: 14px;">
                    <div><strong>Base Refund:</strong> ${currency} ${Number(baseRefund).toFixed(2)}</div>
                    <div><strong>Refund Base Fee:</strong> - ${currency} ${Number(refundBaseFee).toFixed(2)}</div>
                    <div><strong>Refund Additional markup:</strong> - ${currency} ${Number(refundAdditional).toFixed(2)}</div>
                    <div><strong>IPG Transaction Fee:</strong> - ${currency} ${Number(ipgAmount).toFixed(2)}</div>
                    <div><strong>Total Deductions:</strong> - ${currency} ${Number(serviceTotal).toFixed(2)}</div>
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
            // Build SLA timeline information
            const slaMinutes = data.sla_minutes || 120;
            const slaHours = (slaMinutes / 60).toFixed(1);
            const expectedCompletionUTC = data.expected_completion_utc || 'Processing...';
            const processingStartedUTC = data.processing_started_utc || '';
            
            let timelineHtml = '';
            if (expectedCompletionUTC && processingStartedUTC) {
                timelineHtml = `
                    <div style="background:#e7f3ff; border-left:4px solid #0d6efd; padding:12px; margin:15px 0; border-radius:4px; text-align:left;">
                        <p style="margin:0 0 8px 0; font-weight:bold; color:#084298;">
                            <i class="fas fa-clock"></i> Processing Timeline
                        </p>
                        <div style="margin:4px 0; font-size:13px; color:#084298;">
                            <strong>Started:</strong> ${processingStartedUTC}
                        </div>
                        <div style="margin:4px 0; font-size:13px; color:#084298;">
                            <strong>Expected Completion:</strong> ${expectedCompletionUTC}
                        </div>
                        <div style="margin:4px 0; font-size:13px; color:#084298;">
                            <strong>Processing Time:</strong> Up to ${slaHours} hours
                        </div>
                        <p style="margin:8px 0 0 0; font-size:12px; color:#666; font-style:italic;">
                            Times shown in UTC. We'll email you when complete.
                        </p>
                    </div>
                `;
            }
            
            Swal.fire({
                title: 'Void Request Successful!',
                html: `
                    <div>
                        <p style="font-size: 15px; margin: 10px 0;">${data.message}</p>
                        ${timelineHtml}
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            // Show raw Mystifly error message with both request and response
            let errorHtml = `<div class="text-left">`;
            errorHtml += `<p><strong>Mystifly API Error:</strong></p>`;
            errorHtml += `<p style="color: #d33; font-weight: bold;">${data.message}</p>`;
            
            if (data.raw_request) {
                errorHtml += `<details style="margin-top: 15px;">`;
                errorHtml += `<summary style="cursor: pointer; color: #666;">📤 View Raw Request Sent to Mystifly</summary>`;
                errorHtml += `<pre style="background: #e3f2fd; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #2196f3;">${JSON.stringify(data.raw_request, null, 2)}</pre>`;
                errorHtml += `</details>`;
            }
            
            if (data.raw_response) {
                errorHtml += `<details style="margin-top: 15px;">`;
                errorHtml += `<summary style="cursor: pointer; color: #666;">📥 View Raw Response from Mystifly</summary>`;
                errorHtml += `<pre style="background: #ffebee; padding: 10px; margin-top: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; border-left: 4px solid #f44336;">${JSON.stringify(data.raw_response, null, 2)}</pre>`;
                errorHtml += `</details>`;
            }
            
            errorHtml += `</div>`;
            
            Swal.fire({
                title: 'Mystifly API Error',
                html: errorHtml,
                icon: 'error',
                confirmButtonText: 'OK',
                width: '700px'
            });
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

function getSelectedPassengers(passengerData) {
    // passengerData is already in the correct format from getAllTicketedPassengers
    return passengerData.map(passenger => ({
        firstname: passenger.firstname,
        lastname: passenger.lastname,
        title: passenger.title,
        eticket: passenger.eticket,
        passengertype: passenger.passengertype
    }));
}

// Void Window Countdown Timer
function startVoidCountdown() {
    const countdownElement = document.getElementById('voidCountdown');
    if (!countdownElement) return;
    
    // Get void window from PHP
    const voidWindow = '<?php echo $firstTicketedPassenger ? $firstTicketedPassenger['void_window'] : ''; ?>';
    if (!voidWindow || voidWindow === '') {
        countdownElement.textContent = 'Not available';
        countdownElement.className = 'countdown-display text-muted';
        return;
    }
    
    // Parse void window datetime - force UTC interpretation for worldwide consistency
    // Mystifly sends ISO 8601 format without timezone (e.g., 2025-10-17T16:29:59.997)
    // Adding 'Z' suffix forces UTC interpretation
    const voidWindowDate = new Date(voidWindow + 'Z');
    const now = new Date();
    
    // Debug: Log timezone info (can be removed in production)
    console.log('Void Window (Raw):', voidWindow);
    console.log('Void Window (UTC):', voidWindowDate.toUTCString());
    console.log('Current Time (Local):', now.toString());
    console.log('Difference (hours):', (voidWindowDate - now) / (1000 * 60 * 60));
    
    // Check if void window is already expired on page load
    if (voidWindowDate <= now) {
        console.log('Void window already expired on page load');
        countdownElement.innerHTML = '<span class="text-danger">EXPIRED</span>';
        // Don't start countdown or show popup - server-side already shows refund button
        return;
    }
    
    function updateCountdown() {
        const currentTime = new Date();
        const timeLeft = voidWindowDate - currentTime;
        
        if (timeLeft <= 0) {
            countdownElement.innerHTML = '<span class="text-danger">EXPIRED</span>';
            countdownElement.parentElement.innerHTML = '<small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Void window has expired</small>';
            
            // Show notification and refresh page after 3 seconds
            setTimeout(() => {
                Swal.fire({
                    title: 'Void Window Expired',
                    text: 'The void window has expired. The page will refresh to show updated options.',
                    icon: 'warning',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }, 1000);
            return;
        }
        
        // Calculate time components
        const hours = Math.floor(timeLeft / (1000 * 60 * 60));
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
        
        // Format countdown display
        let countdownText = '';
        if (hours > 0) {
            countdownText = `${hours}h ${minutes}m ${seconds}s`;
        } else if (minutes > 0) {
            countdownText = `${minutes}m ${seconds}s`;
        } else {
            countdownText = `${seconds}s`;
        }
        
        // Add warning colors based on time remaining
        if (hours < 1) {
            countdownElement.className = 'countdown-display text-danger fw-bold';
        } else if (hours < 2) {
            countdownElement.className = 'countdown-display text-warning fw-bold';
        } else {
            countdownElement.className = 'countdown-display text-success fw-bold';
        }
        
        // Show urgent warning if less than 5 minutes remaining
        if (timeLeft < 5 * 60 * 1000 && timeLeft > 0) {
            if (!document.querySelector('.urgent-warning')) {
                const urgentWarning = document.createElement('div');
                urgentWarning.className = 'urgent-warning alert alert-warning mt-2';
                urgentWarning.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i><strong>Urgent:</strong> Void window expires in less than 5 minutes!';
                countdownElement.parentElement.appendChild(urgentWarning);
            }
        }
        
        countdownElement.textContent = countdownText;
    }
    
    // Update immediately and then every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
}

// Start countdown when page loads
document.addEventListener('DOMContentLoaded', function() {
    startVoidCountdown();
});



</script>

<?php require_once("includes/footer.php"); ?>