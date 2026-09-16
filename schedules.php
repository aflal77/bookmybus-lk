<?php

// BookMyBus LK – Real-Time Bus Schedules (schedules.php)
// Timetable and live dispatch trip statuses

require_once 'includes/auth.php';

$date_filter = trim($_GET['date'] ?? date('Y-m-d'));
$search_query = trim($_GET['q'] ?? '');

$sql = "SELECT s.*, r.origin, r.destination, r.distance_km, r.estimated_duration,
               bu.bus_name, bu.bus_number, bu.bus_type, bu.operator, bu.ac_type,
               (SELECT COUNT(*) FROM seats se WHERE se.bus_id = bu.id AND se.status = 'available') AS available_seats
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses bu ON s.bus_id = bu.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($date_filter)) {
    $sql .= " AND s.travel_date = ?";
    $params[] = $date_filter;
    $types .= 's';
}
if (!empty($search_query)) {
    $sql .= " AND (r.origin LIKE ? OR r.destination LIKE ? OR bu.bus_name LIKE ?)";
    $like = "%$search_query%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

$sql .= " ORDER BY s.travel_date ASC, s.departure_time ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$schedules = [];
while ($row = mysqli_fetch_assoc($res)) {
    $schedules[] = $row;
}
mysqli_stmt_close($stmt);

$page_title = "Daily Bus Schedules & Live Departures";
$active_page = "schedules";
include 'includes/header.php';
?>

<div class="container my-5">

    <!-- Header Banner -->
    <div class="p-4 mb-4 bg-white rounded-3 shadow-sm border-start border-4 border-primary">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="section-label mb-2">  </span>
                <h2 class="fw-bold text-dark mb-1">Sri Lanka Intercity Schedules & Timetable</h2>
                <p class="text-muted mb-0">Track real-time departures, platform boarding statuses, and seat availability.</p>
            </div>
            <div>
                <span class="badge bg-light text-dark border p-2">
                    <i class="bi bi-clock me-1 text-primary"></i> Current Time: <?php echo date('h:i A'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card qs-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="schedules.php" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="small text-muted fw-semibold">Select Travel Date:</label>
                    <input type="date" name="date" class="form-control" value="<?php echo e($date_filter); ?>">
                </div>
                <div class="col-md-6">
                    <label class="small text-muted fw-semibold">Filter City / Bus Name:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="e.g. Colombo, Kandy, Southern Express" value="<?php echo e($search_query); ?>">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-qs-primary w-100 fw-bold">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Timetable Card -->
    <div class="card qs-card">
        <div class="card-header qs-card-header py-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold"><i class="bi bi-calendar3 me-2"></i> Scheduled Trips for <?php echo date('l, d F Y', strtotime($date_filter)); ?></div>
            <span class="badge bg-warning text-dark"><?php echo count($schedules); ?> Trips Listed</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($schedules)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-50"></i>
                    <h5>No bus schedules listed for this date.</h5>
                    <p class="small">Try picking another date or check our permanent route catalog.</p>
                    <a href="routes.php" class="btn btn-outline-primary btn-sm mt-2">View All Active Routes</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Departure & Arrival</th>
                                <th>Route</th>
                                <th>Bus Service</th>
                                <th>Trip Status</th>
                                <th>Seats Left</th>
                                <th>Fare</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold fs-6 text-danger"><i class="bi bi-clock me-1"></i> <?php echo e($s['departure_time']); ?></div>
                                        <small class="text-muted">Est. Arrival: <?php echo e($s['arrival_time']); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?php echo e($s['origin']); ?> &rarr; <?php echo e($s['destination']); ?></strong><br>
                                        <small class="text-muted"><?php echo (int)$s['distance_km']; ?> km &bull; <?php echo e($s['estimated_duration']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo e($s['bus_name']); ?></div>
                                        <small class="text-muted"><?php echo e($s['bus_number']); ?> &bull; <span class="badge bg-light text-dark border"><?php echo e($s['bus_type']); ?></span></small>
                                    </td>
                                    <td>
                                        <?php if ($s['status'] === 'Boarding'): ?>
                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-person-walking me-1"></i> Boarding</span>
                                        <?php elseif ($s['status'] === 'On Route'): ?>
                                            <span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-bus-front me-1"></i> On Route</span>
                                        <?php elseif ($s['status'] === 'Departed'): ?>
                                            <span class="badge bg-secondary px-2 py-1"><i class="bi bi-arrow-right-short me-1"></i> Departed</span>
                                        <?php elseif ($s['status'] === 'Arrived'): ?>
                                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Arrived</span>
                                        <?php elseif ($s['status'] === 'Cancelled'): ?>
                                            <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary px-2 py-1"><i class="bi bi-calendar-check me-1"></i> Scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success px-2 py-1"><?php echo (int)$s['available_seats']; ?> Seats</span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?php echo format_lkr($s['fare']); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($s['status'] !== 'Cancelled' && $s['status'] !== 'Departed' && $s['available_seats'] > 0): ?>
                                            <a href="book.php?route_id=<?php echo (int)$s['route_id']; ?>&schedule_id=<?php echo (int)$s['id']; ?>" class="btn btn-qs-primary btn-sm fw-bold">
                                                Book Seat
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Closed</button>
                                        <?php endif; ?>
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

<?php include 'includes/footer.php'; ?>
