<?php

// BookMyBus LK – Trip Schedule & Dispatch Management (admin/schedules.php)
// Timetable dispatcher and live trip status management

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $route_id = (int)($_POST['route_id'] ?? 0);
    $travel_date = trim($_POST['travel_date'] ?? date('Y-m-d'));
    $departure_time = trim($_POST['departure_time'] ?? '');
    $arrival_time = trim($_POST['arrival_time'] ?? '');
    $fare = (float)($_POST['fare'] ?? 0);
    $status = trim($_POST['status'] ?? 'Scheduled');

    if ($bus_id <= 0 || $route_id <= 0 || empty($departure_time) || $fare <= 0) {
        $error = "Please fill in all schedule fields.";
    } else {
        $ins = mysqli_prepare($conn, "INSERT INTO schedules (bus_id, route_id, travel_date, departure_time, arrival_time, fare, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "iisssds", $bus_id, $route_id, $travel_date, $departure_time, $arrival_time, $fare, $status);
        if (mysqli_stmt_execute($ins)) {
            $msg = "New trip schedule dispatched successfully.";
        } else {
            $error = "Failed to create schedule. Please verify route and bus selection.";
        }
        mysqli_stmt_close($ins);
    }
}

// Update Status
if (isset($_GET['status_id']) && isset($_GET['new_status'])) {
    $s_id = (int)$_GET['status_id'];
    $st = trim($_GET['new_status']);
    $valid = ['Scheduled', 'Boarding', 'Departed', 'On Route', 'Arrived', 'Cancelled'];
    if (in_array($st, $valid)) {
        mysqli_query($conn, "UPDATE schedules SET status = '$st' WHERE id = $s_id");
        $msg = "Schedule #$s_id status updated to '$st'.";
    }
}

// Fetch all schedules
$res = mysqli_query($conn, "SELECT s.*, r.origin, r.destination, bu.bus_name, bu.bus_number, bu.bus_type
                            FROM schedules s
                            JOIN routes r ON s.route_id = r.id
                            JOIN buses bu ON s.bus_id = bu.id
                            ORDER BY s.travel_date DESC, s.departure_time ASC");
$schedules = [];
while ($row = mysqli_fetch_assoc($res)) {
    $schedules[] = $row;
}

// Fetch buses & routes for modal
$buses_res = mysqli_query($conn, "SELECT id, bus_number, bus_name FROM buses WHERE status = 'active'");
$routes_res = mysqli_query($conn, "SELECT id, origin, destination, departure_time, fare FROM routes");

$page_title = "Schedule & Dispatch Management";
$active_page = "admin_schedules";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-calendar3 text-primary me-2"></i> Trip Dispatch & Schedules</h3>
            <small class="text-muted">Manage daily bus departures, platform boarding statuses, and trip arrivals</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            <button class="btn btn-primary-custom btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle me-1"></i> Dispatch New Trip
            </button>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card card-custom">
        <div class="card-header card-header-custom py-3">
            <span class="fw-bold">Active Timetable Schedules (<?php echo count($schedules); ?> Trips)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Trip ID</th>
                            <th>Travel Date</th>
                            <th>Departure & Arrival</th>
                            <th>Route</th>
                            <th>Assigned Bus</th>
                            <th>Status</th>
                            <th>Fare</th>
                            <th class="text-end">Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td><strong>#<?php echo $s['id']; ?></strong></td>
                                <td><?php echo date('d M Y', strtotime($s['travel_date'])); ?></td>
                                <td>
                                    <span class="fw-bold text-danger"><?php echo e($s['departure_time']); ?></span> &rarr;
                                    <span class="text-muted"><?php echo e($s['arrival_time']); ?></span>
                                </td>
                                <td><strong><?php echo e($s['origin']); ?> &rarr; <?php echo e($s['destination']); ?></strong></td>
                                <td>
                                    <?php echo e($s['bus_name']); ?><br>
                                    <small class="text-muted"><?php echo e($s['bus_number']); ?> &bull; <?php echo e($s['bus_type']); ?></small>
                                </td>
                                <td>
                                    <?php if ($s['status'] === 'Boarding'): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1">Boarding</span>
                                    <?php elseif ($s['status'] === 'On Route'): ?>
                                        <span class="badge bg-info text-dark px-2 py-1">On Route</span>
                                    <?php elseif ($s['status'] === 'Departed'): ?>
                                        <span class="badge bg-secondary px-2 py-1">Departed</span>
                                    <?php elseif ($s['status'] === 'Arrived'): ?>
                                        <span class="badge bg-success px-2 py-1">Arrived</span>
                                    <?php elseif ($s['status'] === 'Cancelled'): ?>
                                        <span class="badge bg-danger px-2 py-1">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary px-2 py-1">Scheduled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark"><?php echo format_lkr($s['fare']); ?></td>
                                <td class="text-end">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li><a class="dropdown-item" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=Scheduled">Scheduled</a></li>
                                            <li><a class="dropdown-item text-warning fw-bold" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=Boarding">Boarding</a></li>
                                            <li><a class="dropdown-item" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=Departed">Departed</a></li>
                                            <li><a class="dropdown-item text-info fw-bold" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=On Route">On Route</a></li>
                                            <li><a class="dropdown-item text-success fw-bold" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=Arrived">Arrived</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="schedules.php?status_id=<?php echo $s['id']; ?>&new_status=Cancelled">Cancelled</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="schedules.php">
            <input type="hidden" name="add_schedule" value="1">
            <div class="modal-content">
                <div class="modal-header navbar-custom text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Dispatch New Trip Schedule</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Route</label>
                        <select class="form-select" name="route_id" required>
                            <option value="">-- Choose Intercity Route --</option>
                            <?php while ($ro = mysqli_fetch_assoc($routes_res)): ?>
                                <option value="<?php echo $ro['id']; ?>"><?php echo e($ro['origin']); ?> &rarr; <?php echo e($ro['destination']); ?> (Default: <?php echo e($ro['departure_time']); ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Assigned Bus</label>
                        <select class="form-select" name="bus_id" required>
                            <option value="">-- Choose Coach --</option>
                            <?php while ($b = mysqli_fetch_assoc($buses_res)): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo e($b['bus_number']); ?> - <?php echo e($b['bus_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Travel Date</label>
                        <input type="date" class="form-control" name="travel_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Departure Time</label>
                            <input type="text" class="form-control" name="departure_time" placeholder="08:30 AM" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Est. Arrival Time</label>
                            <input type="text" class="form-control" name="arrival_time" placeholder="11:45 AM" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Fare (LKR)</label>
                            <input type="number" step="0.01" class="form-control" name="fare" placeholder="2500.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select class="form-select" name="status">
                                <option value="Scheduled" selected>Scheduled</option>
                                <option value="Boarding">Boarding</option>
                                <option value="Departed">Departed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm fw-bold">Dispatch Trip</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
