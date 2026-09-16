<?php

// BookMyBus LK – Driver Operations Portal (driver/index.php)
// Duty roster, assigned coach telemetry, and trip status updater

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role(['driver', 'admin'], '../login.php');

$user_id = $_SESSION['user_id'];
$msg = '';

// Find driver record
$d_stmt = mysqli_prepare($conn, "SELECT d.*, b.bus_name, b.bus_number, b.bus_type, b.total_seats
                                 FROM drivers d
                                 LEFT JOIN buses b ON d.assigned_bus_id = b.id
                                 WHERE d.user_id = ?");
mysqli_stmt_bind_param($d_stmt, "i", $user_id);
mysqli_stmt_execute($d_stmt);
$driver = mysqli_fetch_assoc(mysqli_stmt_get_result($d_stmt));
mysqli_stmt_close($d_stmt);

// Handle Trip Status Update
if (isset($_GET['set_trip_status']) && isset($_GET['sched_id'])) {
    $sc_id = (int)$_GET['sched_id'];
    $new_st = trim($_GET['set_trip_status']);
    $allowed = ['Boarding', 'Departed', 'On Route', 'Arrived'];
    if (in_array($new_st, $allowed)) {
        mysqli_query($conn, "UPDATE schedules SET status = '$new_st' WHERE id = $sc_id");
        $msg = "Trip #$sc_id status updated to '$new_st'.";
    }
}

// Fetch assigned bus schedules
$assigned_bus_id = $driver['assigned_bus_id'] ?? 1;
$sched_res = mysqli_query($conn, "SELECT s.*, r.origin, r.destination, r.distance_km, r.estimated_duration
                                   FROM schedules s
                                   JOIN routes r ON s.route_id = r.id
                                   WHERE s.bus_id = $assigned_bus_id
                                   ORDER BY s.travel_date DESC, s.departure_time ASC LIMIT 5");
$trips = [];
while ($row = mysqli_fetch_assoc($sched_res)) {
    $trips[] = $row;
}

// Fetch passenger list for assigned bus
$passengers_res = mysqli_query($conn, "SELECT b.*, r.origin, r.destination
                                       FROM bookings b
                                       JOIN routes r ON b.route_id = r.id
                                       WHERE r.bus_id = $assigned_bus_id AND b.booking_status = 'confirmed'
                                       ORDER BY b.seat_number ASC");
$passengers = [];
while ($p = mysqli_fetch_assoc($passengers_res)) {
    $passengers[] = $p;
}

$page_title = "Driver Operations Portal";
$active_page = "driver";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <span class="badge bg-info text-dark text-uppercase px-3 py-1 mb-1 fw-bold">Fleet Driver Portal</span>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-steering-wheel text-primary me-2"></i> Driver Operations & Duty Log</h3>
            <small class="text-muted">Welcome, <?php echo e($_SESSION['user_name']); ?> &bull; License: <?php echo e($driver['license_number'] ?? 'B-893472-WP'); ?></small>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary btn-sm">Public Home</a>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>

    <!-- Assigned Bus Card -->
    <div class="card card-custom mb-4 border-start border-4 border-primary">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary px-3 py-1 mb-2">ASSIGNED COACH</span>
                    <h4 class="fw-bold text-dark mb-1">
                        <?php echo e($driver['bus_name'] ?? 'Kandy Royal Express'); ?>
                        <span class="badge bg-light text-dark border ms-1"><?php echo e($driver['bus_number'] ?? 'NB-1234'); ?></span>
                    </h4>
                    <div class="text-muted small">
                        <span><i class="bi bi-person-wheelchair me-1"></i> Class: <?php echo e($driver['bus_type'] ?? 'Super Luxury'); ?></span>
                        <span class="mx-2">&bull;</span>
                        <span><i class="bi bi-grid-3x3 me-1"></i> Total Capacity: <?php echo (int)($driver['total_seats'] ?? 30); ?> Seats</span>
                        <span class="mx-2">&bull;</span>
                        <span><i class="bi bi-speedometer2 me-1"></i> Experience: <?php echo (int)($driver['experience_years'] ?? 10); ?> Years</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-success fs-6 p-2"><i class="bi bi-check2-circle me-1"></i> Ready for Duty</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Assigned Trips -->
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-signpost-split me-2"></i> Scheduled Trips for Your Bus</span>
                    <span class="badge bg-warning text-dark">Live Dispatch</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Trip Route</th>
                                    <th>Departure</th>
                                    <th>Status</th>
                                    <th class="text-end">Update Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($trips)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No trips dispatched for this coach today.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($trips as $t): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($t['origin']); ?> &rarr; <?php echo e($t['destination']); ?></strong><br>
                                                <small class="text-muted"><?php echo (int)$t['distance_km']; ?> km &bull; <?php echo e($t['estimated_duration']); ?></small>
                                            </td>
                                            <td class="fw-bold text-danger"><?php echo e($t['departure_time']); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark"><?php echo e($t['status']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        Update
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                                        <li><a class="dropdown-item text-warning" href="index.php?sched_id=<?php echo $t['id']; ?>&set_trip_status=Boarding">Boarding</a></li>
                                                        <li><a class="dropdown-item text-secondary" href="index.php?sched_id=<?php echo $t['id']; ?>&set_trip_status=Departed">Departed</a></li>
                                                        <li><a class="dropdown-item text-info" href="index.php?sched_id=<?php echo $t['id']; ?>&set_trip_status=On Route">On Route</a></li>
                                                        <li><a class="dropdown-item text-success fw-bold" href="index.php?sched_id=<?php echo $t['id']; ?>&set_trip_status=Arrived">Arrived Terminal</a></li>
                                                    </ul>
                                                </div>
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

        <!-- Confirmed Passenger Headcount -->
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-people me-2"></i> Passenger Manifest Headcount</span>
                    <span class="badge bg-success"><?php echo count($passengers); ?> Confirmed Passengers</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Seat</th>
                                    <th>Passenger Full Name</th>
                                    <th>Contact Phone</th>
                                    <th>Booking Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($passengers)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No confirmed passenger bookings recorded.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($passengers as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-primary fs-6 px-3 py-1">#<?php echo (int)$p['seat_number']; ?></span></td>
                                            <td class="fw-bold text-dark"><?php echo e($p['passenger_name']); ?></td>
                                            <td><i class="bi bi-telephone me-1 text-muted"></i> <?php echo e($p['passenger_phone']); ?></td>
                                            <td class="text-muted small"><?php echo e($p['booking_ref']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
