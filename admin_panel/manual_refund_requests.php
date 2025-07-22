<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

require_once("includes/header.php");
include_once("includes/class.Dbconnect.php");

$obj = new DbConnect();

// Get manual refund requests
$manualRefunds = $obj->executeQuery("
    SELECT 
        cb.*,
        tb.mf_reference,
        tb.booking_date,
        tb.dep_date,
        tb.total_amount,
        u.first_name,
        u.last_name,
        u.email,
        u.mobile
    FROM cancel_booking cb
    LEFT JOIN temp_booking tb ON cb.booking_id = tb.id
    LEFT JOIN users u ON cb.user_agent_id = u.id
    WHERE cb.ptr_type = 'ManualRefund'
    ORDER BY cb.id DESC
");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manual Refund Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Manual Refunds</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Manual Refund Requests Requiring Processing</h3>
                            <div class="card-tools">
                                <span class="badge badge-warning"><?php echo count($manualRefunds); ?> Pending</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($manualRefunds)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No manual refund requests found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="manualRefundsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Booking Ref</th>
                                                <th>Customer</th>
                                                <th>Ticket Number</th>
                                                <th>Booking Date</th>
                                                <th>Departure Date</th>
                                                <th>Total Amount</th>
                                                <th>Void Window</th>
                                                <th>Status</th>
                                                <th>Request Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($manualRefunds as $refund): ?>
                                                <tr>
                                                    <td><?php echo $refund['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($refund['mf_reference']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">Booking ID: <?php echo $refund['booking_id']; ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($refund['first_name'] . ' ' . $refund['last_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($refund['email']); ?></small>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($refund['mobile']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($refund['ticket_number']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($refund['booking_date'])); ?></td>
                                                    <td>
                                                        <?php echo date('M d, Y', strtotime($refund['dep_date'])); ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php 
                                                            $daysUntilDep = ceil((strtotime($refund['dep_date']) - time()) / (60 * 60 * 24));
                                                            if ($daysUntilDep > 0) {
                                                                echo "$daysUntilDep days until departure";
                                                            } elseif ($daysUntilDep == 0) {
                                                                echo "Departing today";
                                                            } else {
                                                                echo abs($daysUntilDep) . " days ago (departed)";
                                                            }
                                                            ?>
                                                        </small>
                                                    </td>
                                                    <td>$<?php echo number_format($refund['total_amount'], 2); ?></td>
                                                    <td>
                                                        <?php if (!empty($refund['void_window'])): ?>
                                                            <?php echo date('M d, Y H:i', strtotime($refund['void_window'])); ?>
                                                            <br>
                                                            <small class="text-danger">Expired</small>
                                                        <?php else: ?>
                                                            <small class="text-muted">N/A</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($refund['ptr_status'] == 'Pending'): ?>
                                                            <span class="badge badge-warning">Pending</span>
                                                        <?php elseif ($refund['ptr_status'] == 'Processing'): ?>
                                                            <span class="badge badge-info">Processing</span>
                                                        <?php elseif ($refund['ptr_status'] == 'Completed'): ?>
                                                            <span class="badge badge-success">Completed</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($refund['ptr_status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('M d, Y H:i', strtotime($refund['created_date'])); ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php 
                                                            $hoursAgo = ceil((time() - strtotime($refund['created_date'])) / (60 * 60));
                                                            echo "$hoursAgo hours ago";
                                                            ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-info" 
                                                                    onclick="viewDetails(<?php echo $refund['id']; ?>)">
                                                                <i class="fas fa-eye"></i> View
                                                            </button>
                                                            <?php if ($refund['ptr_status'] == 'Pending'): ?>
                                                                <button type="button" class="btn btn-sm btn-primary" 
                                                                        onclick="processRefund(<?php echo $refund['id']; ?>)">
                                                                    <i class="fas fa-cog"></i> Process
                                                                </button>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="contactCustomer('<?php echo htmlspecialchars($refund['email']); ?>', '<?php echo htmlspecialchars($refund['mf_reference']); ?>')">
                                                                <i class="fas fa-envelope"></i> Email
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#manualRefundsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[ 0, "desc" ]],
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#manualRefundsTable_wrapper .col-md-6:eq(0)');
});

function viewDetails(refundId) {
    // Add modal or redirect to view details
    window.location.href = 'manual_refund_detail.php?id=' + refundId;
}

function processRefund(refundId) {
    Swal.fire({
        title: 'Process Manual Refund',
        text: 'Mark this refund request as processing?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, mark as processing'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX call to update status
            $.ajax({
                url: 'ajax/update_refund_status.php',
                method: 'POST',
                data: {
                    refund_id: refundId,
                    status: 'Processing'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Updated!', 'Refund status updated to processing.', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to update status.', 'error');
                }
            });
        }
    });
}

function contactCustomer(email, mfRef) {
    const subject = encodeURIComponent('Refund Update - ' + mfRef);
    const body = encodeURIComponent('Dear Customer,\n\nRegarding your refund request for booking ' + mfRef + '...\n\nBest regards,\nBulatrips Support Team');
    window.location.href = 'mailto:' + email + '?subject=' + subject + '&body=' + body;
}
</script>

<?php require_once("includes/footer.php"); ?> 