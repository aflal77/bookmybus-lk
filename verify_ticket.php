<?php

// BookMyBus LK – Ticket Verification & Conductor Scanner (verify_ticket.php)
// Validates booking references and QR tokens

require_once 'includes/auth.php';

$ref = trim($_GET['ref'] ?? '');
$ticket = null;
$searched = !empty($ref);

if ($searched) {
    $sql = "SELECT b.*, r.origin, r.destination, r.departure_time,
                   bu.bus_name, bu.bus_number, bu.bus_type, bu.operator
            FROM bookings b
            JOIN routes r ON b.route_id = r.id
            JOIN buses bu ON r.bus_id = bu.id
            WHERE b.booking_ref = ? OR b.qr_token = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $ref, $ref);
    mysqli_stmt_execute($stmt);
    $ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$page_title = "Ticket Verification";
$active_page = "verify";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <!-- Search Card -->
            <div class="card qs-card mb-4">
                <div class="card-header qs-card-header text-center py-4">
                    <h4 class="fw-bold mb-1"><i class="bi bi-qr-code-scan me-2"></i> Ticket Verification Portal</h4>
                    <p class="small text-light opacity-75 mb-0">Validate digital passenger tickets and boarding authorization</p>
                </div>
                <div class="card-body p-4">
                    <form method="GET" action="verify_ticket.php">
                        <label for="ref" class="form-label fw-semibold">Enter Booking Reference or QR Token:</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light"><i class="bi bi-ticket-detailed"></i></span>
                            <input type="text" class="form-control" id="ref" name="ref" value="<?php echo e($ref); ?>" placeholder="e.g. RL-2026-0001" required autofocus>
                            <button type="submit" class="btn btn-qs-primary px-4 fw-bold">
                                <i class="bi bi-search me-1"></i> Verify
                            </button>
                        </div>
                        <small class="text-muted">You can enter the booking reference printed on top of the ticket or scan the QR code.</small>
                    </form>
                </div>
            </div>

            <!-- Verification Result Display -->
            <?php if ($searched): ?>
                <?php if ($ticket): ?>
                    <?php if ($ticket['booking_status'] === 'confirmed'): ?>
                        <!-- Valid Confirmed Ticket -->
                        <div class="card qs-card border-success mb-4 shadow">
                            <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                                <div class="fw-bold fs-5"><i class="bi bi-check-circle-fill me-2"></i> VALID PASSENGER TICKET</div>
                                <span class="badge bg-white text-success fw-bold px-3 py-1">BOARDING APPROVED</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="text-center mb-3">
                                    <h4 class="fw-bold text-dark mb-1"><?php echo e($ticket['passenger_name']); ?></h4>
                                    <div class="text-muted small">Reference: <strong><?php echo e($ticket['booking_ref']); ?></strong></div>
                                </div>
                                <table class="table table-bordered align-middle">
                                    <tr>
                                        <th class="bg-light" style="width: 40%;">Route:</th>
                                        <td class="fw-bold text-primary"><?php echo e($ticket['origin']); ?> &rarr; <?php echo e($ticket['destination']); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Departure Time:</th>
                                        <td class="fw-bold text-danger"><?php echo e($ticket['departure_time']); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Assigned Seat:</th>
                                        <td>
                                            <span class="badge bg-primary fs-5 px-3 py-1">
                                                Seat #<?php echo (int)$ticket['seat_number']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Bus Details:</th>
                                        <td><?php echo e($ticket['bus_name']); ?> (<?php echo e($ticket['bus_number']); ?>) &bull; <?php echo e($ticket['operator']); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Payment Status:</th>
                                        <td><span class="badge bg-success"><?php echo strtoupper($ticket['payment_status']); ?></span> (<?php echo format_lkr($ticket['total_amount']); ?>)</td>
                                    </tr>
                                </table>
                                <div class="text-center mt-3">
                                    <a href="ticket.php?ref=<?php echo urlencode($ticket['booking_ref']); ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-printer me-1"></i> Open Full Boarding Pass
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($ticket['booking_status'] === 'completed'): ?>
                        <!-- Completed Ticket -->
                        <div class="card qs-card border-secondary mb-4 shadow">
                            <div class="card-header bg-secondary text-white py-3">
                                <div class="fw-bold fs-5"><i class="bi bi-flag-fill me-2"></i> COMPLETED JOURNEY</div>
                            </div>
                            <div class="card-body p-4">
                                <p class="mb-1">This ticket (<strong><?php echo e($ticket['booking_ref']); ?></strong>) for <strong><?php echo e($ticket['passenger_name']); ?></strong> was successfully completed.</p>
                                <p class="text-muted small">Route: <?php echo e($ticket['origin']); ?> &rarr; <?php echo e($ticket['destination']); ?> | Seat #<?php echo (int)$ticket['seat_number']; ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Cancelled Ticket -->
                        <div class="card qs-card border-danger mb-4 shadow">
                            <div class="card-header bg-danger text-white py-3">
                                <div class="fw-bold fs-5"><i class="bi bi-x-circle-fill me-2"></i> CANCELLED TICKET</div>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-danger mb-3">
                                    <strong>BOARDING DENIED:</strong> This ticket (<strong><?php echo e($ticket['booking_ref']); ?></strong>) was cancelled and refunded. Seat #<?php echo (int)$ticket['seat_number']; ?> is not valid for travel.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Ticket Not Found -->
                    <div class="alert alert-danger p-4 text-center shadow-sm">
                        <i class="bi bi-shield-x fs-1 text-danger d-block mb-2"></i>
                        <h5 class="fw-bold">Invalid or Unknown Ticket!</h5>
                        <p class="text-muted mb-0">No booking was found matching reference "<strong><?php echo e($ref); ?></strong>". Please check the number or contact the BookMyBus LK terminal desk.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
