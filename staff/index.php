<?php

// BookMyBus LK – Station Staff Portal (staff/index.php)
// Ticket validation, passenger manifests, and boarding dispatch

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role(['staff', 'admin'], '../login.php');

$msg = '';

// Quick Boarding Status Toggle
if (isset($_GET['mark_boarded']) && isset($_GET['booking_id'])) {
    $b_id = (int)$_GET['booking_id'];
    mysqli_query($conn, "UPDATE bookings SET booking_status = 'completed' WHERE id = $b_id");
    $msg = "Passenger on booking #$b_id marked as onboarded.";
}

// Fetch Today's Scheduled Departures
$sched_res = mysqli_query($conn, "SELECT s.*, r.origin, r.destination, bu.bus_name, bu.bus_number, bu.bus_type
                                   FROM schedules s
                                   JOIN routes r ON s.route_id = r.id
                                   JOIN buses bu ON s.bus_id = bu.id
                                   WHERE s.travel_date = CURDATE()
                                   ORDER BY s.departure_time ASC");
$today_schedules = [];
while ($row = mysqli_fetch_assoc($sched_res)) {
    $today_schedules[] = $row;
}

// Fetch Today's Passenger Bookings Manifest
$manifest_res = mysqli_query($conn, "SELECT b.*, r.origin, r.destination, bu.bus_name, bu.bus_number
                                     FROM bookings b
                                     JOIN routes r ON b.route_id = r.id
                                     JOIN buses bu ON r.bus_id = bu.id
                                     WHERE b.booking_status = 'confirmed'
                                     ORDER BY b.id DESC LIMIT 15");
$manifest = [];
while ($m = mysqli_fetch_assoc($manifest_res)) {
    $manifest[] = $m;
}

$page_title = "Staff Boarding & Ticket Desk";
$active_page = "staff";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <span class="badge bg-warning text-dark text-uppercase px-3 py-1 mb-1 fw-bold">Station Staff Portal</span>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-primary me-2"></i> Terminal Passenger Manifest & Boarding</h3>
            <small class="text-muted">Validate tickets, verify conductor check-ins, and inspect live passenger manifests</small>
        </div>
        <div class="d-flex gap-2">
            <a href="../verify_ticket.php" class="btn btn-warning btn-sm text-dark fw-bold">
                <i class="bi bi-qr-code-scan me-1"></i> Open QR Ticket Scanner
            </a>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Public Home</a>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>

    <!-- Today's Platform Schedules -->
    <div class="card card-custom mb-4">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-calendar-event me-2"></i> Today's Terminal Coach Departures</span>
            <span class="badge bg-light text-dark"><?php echo date('l, d F Y'); ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Departure Time</th>
                            <th>Route</th>
                            <th>Bus Service</th>
                            <th>Current Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($today_schedules)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No scheduled departures today.</td></tr>
                        <?php else: ?>
                            <?php foreach ($today_schedules as $ts): ?>
                                <tr>
                                    <td class="fw-bold text-danger fs-6"><?php echo e($ts['departure_time']); ?></td>
                                    <td><strong><?php echo e($ts['origin']); ?> &rarr; <?php echo e($ts['destination']); ?></strong></td>
                                    <td><?php echo e($ts['bus_name']); ?> (<?php echo e($ts['bus_number']); ?>)</td>
                                    <td>
                                        <?php if ($ts['status'] === 'Boarding'): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1">Boarding Now</span>
                                        <?php elseif ($ts['status'] === 'On Route'): ?>
                                            <span class="badge bg-info text-dark px-2 py-1">On Route</span>
                                        <?php elseif ($ts['status'] === 'Departed'): ?>
                                            <span class="badge bg-secondary px-2 py-1">Departed</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary px-2 py-1">Scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="../verify_ticket.php" class="btn btn-sm btn-outline-primary">Scan Tickets</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Active Passenger Manifest -->
    <div class="card card-custom">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-people-fill me-2"></i> Confirmed Passenger Manifest</span>
            <span class="badge bg-success">Ready for Boarding</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref</th>
                            <th>Passenger Name & Contact</th>
                            <th>Route</th>
                            <th>Bus Service</th>
                            <th>Seat</th>
                            <th>Payment</th>
                            <th class="text-end">Boarding Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($manifest)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No confirmed passenger bookings currently pending boarding.</td></tr>
                        <?php else: ?>
                            <?php foreach ($manifest as $m): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo e($m['booking_ref']); ?></td>
                                    <td>
                                        <strong><?php echo e($m['passenger_name']); ?></strong><br>
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i> <?php echo e($m['passenger_phone']); ?></small>
                                    </td>
                                    <td><?php echo e($m['origin']); ?> &rarr; <?php echo e($m['destination']); ?></td>
                                    <td><?php echo e($m['bus_name']); ?> (<?php echo e($m['bus_number']); ?>)</td>
                                    <td><span class="badge bg-primary fs-5 px-3 py-1">Seat #<?php echo (int)$m['seat_number']; ?></span></td>
                                    <td><span class="badge bg-success"><?php echo strtoupper($m['payment_status']); ?></span></td>
                                    <td class="text-end">
                                        <a href="index.php?mark_boarded=1&booking_id=<?php echo $m['id']; ?>" class="btn btn-sm btn-success fw-bold">
                                            <i class="bi bi-check2 me-1"></i> Check-In / Boarded
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
