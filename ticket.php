<?php

// BookMyBus LK – Digital E-Ticket & Boarding Pass (ticket.php)
// Printable ticket with QR code — "Sunset Highway" theme

require_once 'includes/auth.php';

$ref        = trim($_GET['ref'] ?? '');
$booking_id = (int)($_GET['id'] ?? 0);

if (empty($ref) && $booking_id <= 0) {
    header("Location: index.php");
    exit;
}

if (!empty($ref)) {
    $sql  = "SELECT b.*, r.origin, r.destination, r.departure_time, r.estimated_duration, r.distance_km,
                    r.start_location, r.end_location,
                    bu.bus_name, bu.bus_number, bu.bus_type, bu.operator, bu.ac_type
             FROM bookings b
             JOIN routes r ON b.route_id = r.id
             JOIN buses bu ON r.bus_id = bu.id
             WHERE b.booking_ref = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $ref);
} else {
    $sql  = "SELECT b.*, r.origin, r.destination, r.departure_time, r.estimated_duration, r.distance_km,
                    r.start_location, r.end_location,
                    bu.bus_name, bu.bus_number, bu.bus_type, bu.operator, bu.ac_type
             FROM bookings b
             JOIN routes r ON b.route_id = r.id
             JOIN buses bu ON r.bus_id = bu.id
             WHERE b.id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
}

mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$ticket = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$ticket) {
    die("<div class='container my-5 text-center'>
            <div class='alert alert-danger'>
                <h4>Ticket not found</h4>
                <p>The booking reference does not exist or has been removed.</p>
                <a href='index.php' class='btn-qs-primary btn-qs-sm mt-2'>Back to Home</a>
            </div>
         </div>");
}

$page_title = "E-Ticket — " . $ticket['booking_ref'];
include 'includes/header.php';
?>

<div class="container" style="padding-top:36px;padding-bottom:60px">

    <!-- Action Buttons (no-print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print flex-wrap gap-2">
        <a href="javascript:history.back()" class="btn-qs-secondary btn-qs-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn-qs-primary btn-qs-sm">
                <i class="bi bi-printer"></i> Print / Save PDF
            </button>
            <a href="verify_ticket.php?ref=<?php echo urlencode($ticket['booking_ref']); ?>"
               target="_blank" class="btn-qs-secondary btn-qs-sm">
                <i class="bi bi-check2-circle"></i> Verify Ticket
            </a>
            <?php if ($ticket['booking_status'] === 'confirmed'): ?>
                <a href="my_bookings.php" class="btn-qs-secondary btn-qs-sm">
                    <i class="bi bi-ticket-detailed"></i> My Bookings
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Banner (no-print) -->
    <?php if ($ticket['booking_status'] === 'confirmed'): ?>
    <div class="no-print" style="background:var(--qs-green-light);border:1.5px solid rgba(39,174,96,.25);border-radius:var(--radius-lg);padding:18px 22px;margin-bottom:24px;display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;background:var(--qs-green);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-check-lg" style="color:#fff;font-size:1.2rem"></i>
        </div>
        <div>
            <div style="font-weight:700;color:#166534;font-size:.95rem">Booking Confirmed!</div>
            <div style="font-size:.83rem;color:#15803d">
                Your seat has been reserved. Reference: <strong><?php echo e($ticket['booking_ref']); ?></strong>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- E-Ticket Card -->
    <div class="qs-ticket">

        <!-- Ticket Header -->
        <div class="ticket-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ticket-logo">Quick<span>Seat</span></div>
                    <div style="font-size:.65rem;color:rgba(255,255,255,.5);font-weight:500;letter-spacing:1px;text-transform:uppercase;margin-top:1px">
                        Sri Lanka Intercity Bus Ticket
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:.65rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:0.5px;text-transform:uppercase">Booking Ref.</div>
                    <div style="font-size:1rem;font-weight:700;color:#fff;font-family:monospace;letter-spacing:1px"><?php echo e($ticket['booking_ref']); ?></div>
                </div>
            </div>

            <!-- Route display -->
            <div style="margin-top:22px;display:flex;align-items:center;justify-content:center;gap:0">
                <div style="text-align:center">
                    <div style="font-size:.6rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:3px">From</div>
                    <div class="ticket-route"><?php echo e($ticket['origin']); ?></div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-top:2px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($ticket['start_location']); ?></div>
                </div>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;padding:0 16px">
                    <i class="bi bi-bus-front-fill" style="color:var(--qs-orange);font-size:1.2rem;margin-bottom:3px"></i>
                    <div style="width:100%;height:1.5px;background:linear-gradient(90deg,transparent,var(--qs-orange),transparent)"></div>
                    <div style="font-size:.68rem;color:rgba(255,255,255,.4);margin-top:3px"><?php echo (int)$ticket['distance_km']; ?> km</div>
                </div>
                <div style="text-align:center">
                    <div style="font-size:.6rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:3px">To</div>
                    <div class="ticket-route"><?php echo e($ticket['destination']); ?></div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-top:2px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($ticket['end_location']); ?></div>
                </div>
            </div>

            <!-- Status badge -->
            <div style="text-align:center;margin-top:16px">
                <?php if ($ticket['booking_status'] === 'confirmed'): ?>
                    <span class="ticket-confirmed-badge">
                        <i class="bi bi-check-circle-fill"></i> CONFIRMED &amp; READY TO BOARD
                    </span>
                <?php elseif ($ticket['booking_status'] === 'completed'): ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);color:rgba(255,255,255,.7);padding:4px 14px;border-radius:var(--radius-full);font-size:.78rem;font-weight:700">
                        <i class="bi bi-flag-fill"></i> JOURNEY COMPLETED
                    </span>
                <?php else: ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(226,85,85,.25);color:#fca5a5;padding:4px 14px;border-radius:var(--radius-full);font-size:.78rem;font-weight:700">
                        <i class="bi bi-x-circle-fill"></i> CANCELLED
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tear divider -->
        <div class="ticket-tear">
            <div class="ticket-tear-line"></div>
        </div>

        
        <div class="ticket-body">
            <div class="row g-4">
                <div class="col-md-8">

                    <div class="ticket-row">
                        <div class="t-label">Passenger</div>
                        <div class="t-value" style="font-size:1rem;font-weight:700"><?php echo e($ticket['passenger_name']); ?></div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Contact</div>
                        <div class="t-value"><?php echo e($ticket['passenger_phone']); ?></div>
                    </div>

                    <?php if (!empty($ticket['passenger_nic'])): ?>
                    <div class="ticket-row">
                        <div class="t-label">NIC</div>
                        <div class="t-value"><?php echo e($ticket['passenger_nic']); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="ticket-row">
                        <div class="t-label">Service</div>
                        <div class="t-value">
                            <?php echo e($ticket['bus_name']); ?> (<?php echo e($ticket['bus_number']); ?>)<br>
                            <span style="font-size:.78rem;font-weight:400;color:var(--qs-text-muted)"><?php echo e($ticket['operator']); ?> &bull; <?php echo e($ticket['bus_type']); ?></span>
                        </div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Departure</div>
                        <div class="t-value" style="color:var(--qs-orange)"><?php echo date('h:i A', strtotime($ticket['departure_time'])); ?></div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Duration</div>
                        <div class="t-value"><?php echo e($ticket['estimated_duration']); ?></div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Seat</div>
                        <div class="t-value">
                            <span class="ticket-seat-big">SEAT <?php echo (int)$ticket['seat_number']; ?></span>
                        </div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Fare Paid</div>
                        <div class="t-value" style="color:var(--qs-green);font-size:1.05rem;font-weight:800"><?php echo format_lkr($ticket['total_amount']); ?></div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Payment</div>
                        <div class="t-value">
                            <span class="qs-badge qs-badge-green"><i class="bi bi-check-circle-fill"></i> <?php echo strtoupper(e($ticket['payment_status'])); ?></span>
                            <span style="font-size:.78rem;color:var(--qs-text-muted);margin-left:6px"><?php echo e($ticket['payment_method']); ?></span>
                        </div>
                    </div>

                    <div class="ticket-row">
                        <div class="t-label">Issued</div>
                        <div class="t-value" style="font-weight:400;color:var(--qs-text-muted);font-size:.82rem">
                            <?php echo date('d F Y, h:i A', strtotime($ticket['booking_date'])); ?>
                        </div>
                    </div>

                </div>

                <!-- QR Code Column -->
                <div class="col-md-4 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="qr-box mb-3">
                        <?php
                        $qrProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                        $qrHost     = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $qrDir      = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                        $qrVerifyUrl = $qrProtocol . '://' . $qrHost . ($qrDir ? $qrDir : '') . '/verify_ticket.php?ref=' . $ticket['booking_ref'];
                        ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($qrVerifyUrl); ?>"
                             alt="QR Code"
                             style="width:140px;height:140px;display:block">
                    </div>
                    <div style="font-size:.72rem;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--qs-navy);margin-bottom:3px">Scan to Verify</div>
                    <div style="font-size:.7rem;color:var(--qs-text-muted)">Conductor validation</div>
                    <div style="font-family:monospace;font-size:.65rem;color:var(--qs-border);margin-top:6px;word-break:break-all">
                        <?php echo substr($ticket['qr_token'], 0, 16); ?>...
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Footer -->
        <div class="ticket-footer">
            <div style="font-size:.8rem;font-weight:700;color:var(--qs-navy);margin-bottom:10px;display:flex;align-items:center;gap:6px">
                <i class="bi bi-info-circle" style="color:var(--qs-orange)"></i> Boarding Instructions
            </div>
            <div style="text-align:left;font-size:.78rem;color:var(--qs-text-muted);line-height:1.8">
                &bull; Please arrive at the terminal at least 15 minutes before departure.<br>
                &bull; Present this digital or printed ticket to the conductor upon boarding.<br>
                &bull; For support, call <strong>+94 11 234 5678</strong> (24/7 help desk).
            </div>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--qs-border);display:flex;justify-content:center">
                <span style="font-size:.78rem;font-weight:600;color:var(--qs-orange)">BookMyBus LK &mdash; Sri Lanka's Trusted Bus Booking Platform</span>
            </div>
        </div>

    </div><!-- /.qs-ticket -->

    <!-- Post-ticket actions (no-print) -->
    <div class="d-flex justify-content-center gap-3 mt-4 no-print flex-wrap">
        <a href="index.php" class="btn-qs-secondary btn-qs-sm">
            <i class="bi bi-house"></i> Back to Home
        </a>
        <?php if (is_logged_in()): ?>
        <a href="my_bookings.php" class="btn-qs-secondary btn-qs-sm">
            <i class="bi bi-ticket-detailed"></i> View All Bookings
        </a>
        <?php endif; ?>
        <button onclick="window.print()" class="btn-qs-primary btn-qs-sm">
            <i class="bi bi-printer"></i> Print Ticket
        </button>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
