<?php

// BookMyBus LK – Route Management (admin/routes.php)
// Full CRUD for intercity routes and intermediate waypoints

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// Add Route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_route'])) {
    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $route_number = trim($_POST['route_number'] ?? 'EX-01');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $departure_time = trim($_POST['departure_time'] ?? '');
    $fare = (float)($_POST['fare'] ?? 0);
    $distance_km = (int)($_POST['distance_km'] ?? 100);
    $estimated_duration = trim($_POST['estimated_duration'] ?? '2h 30m');
    $intermediate_stops = trim($_POST['intermediate_stops'] ?? '');
    $start_location = trim($_POST['start_location'] ?? 'Central Bus Stand');
    $end_location = trim($_POST['end_location'] ?? 'City Bus Stand');

    if ($bus_id <= 0 || empty($origin) || empty($destination) || empty($departure_time) || $fare <= 0) {
        $error = "Please fill in all required fields properly.";
    } else {
        $ins = mysqli_prepare($conn, "INSERT INTO routes (bus_id, route_number, origin, destination, departure_time, fare, distance_km, estimated_duration, intermediate_stops, start_location, end_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "issssdisss", $bus_id, $route_number, $origin, $destination, $departure_time, $fare, $distance_km, $estimated_duration, $intermediate_stops, $start_location, $end_location);
        if (mysqli_stmt_execute($ins)) {
            $msg = "Route $origin to $destination ($route_number) added successfully.";
        } else {
            $error = "Failed to add route. Please check all required fields.";
        }
        mysqli_stmt_close($ins);
    }
}

// Delete Route
if (isset($_GET['del_id'])) {
    $del_id = (int)$_GET['del_id'];
    mysqli_query($conn, "DELETE FROM routes WHERE id = $del_id");
    $msg = "Route has been deleted.";
}

// Fetch all routes
$res = mysqli_query($conn, "SELECT r.*, bu.bus_name, bu.bus_number, bu.bus_type FROM routes r JOIN buses bu ON r.bus_id = bu.id ORDER BY r.origin ASC, r.departure_time ASC");
$routes = [];
while ($row = mysqli_fetch_assoc($res)) {
    $routes[] = $row;
}

// Fetch active buses for dropdown
$buses_res = mysqli_query($conn, "SELECT id, bus_number, bus_name FROM buses WHERE status = 'active'");
$active_buses = [];
while ($b = mysqli_fetch_assoc($buses_res)) {
    $active_buses[] = $b;
}

$page_title = "Route Management";
$active_page = "admin_routes";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-signpost-2 text-primary me-2"></i> Route Network Management</h3>
            <small class="text-muted">Configure origin-destination pairs, intermediate stops, distances, and fares</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            <button class="btn btn-primary-custom btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                <i class="bi bi-plus-circle me-1"></i> Create New Route
            </button>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card card-custom">
        <div class="card-header card-header-custom py-3">
            <span class="fw-bold">Scheduled Intercity Routes (<?php echo count($routes); ?> Routes)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Route No.</th>
                            <th>Origin &rarr; Destination</th>
                            <th>Assigned Bus</th>
                            <th>Departure & Duration</th>
                            <th>Distance</th>
                            <th>Fare</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $r): ?>
                            <tr>
                                <td><span class="badge bg-warning text-dark fw-bold"><?php echo e($r['route_number']); ?></span></td>
                                <td>
                                    <strong><?php echo e($r['origin']); ?> &rarr; <?php echo e($r['destination']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($r['start_location']); ?> to <?php echo e($r['end_location']); ?></small>
                                </td>
                                <td>
                                    <?php echo e($r['bus_name']); ?><br>
                                    <small class="text-muted"><?php echo e($r['bus_number']); ?> &bull; <?php echo e($r['bus_type']); ?></small>
                                </td>
                                <td>
                                    <strong class="text-danger"><?php echo e($r['departure_time']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($r['estimated_duration']); ?></small>
                                </td>
                                <td><?php echo (int)$r['distance_km']; ?> km</td>
                                <td class="fw-bold text-success fs-6"><?php echo format_lkr($r['fare']); ?></td>
                                <td class="text-end">
                                    <a href="../book.php?route_id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Test Booking">
                                        <i class="bi bi-arrow-up-right-square"></i>
                                    </a>
                                    <a href="routes.php?del_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this route?')" title="Delete Route">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="routes.php">
            <input type="hidden" name="add_route" value="1">
            <div class="modal-content">
                <div class="modal-header navbar-custom text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Add Intercity Bus Route</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Route Number</label>
                            <input type="text" class="form-control" name="route_number" value="EX-01" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Assign Fleet Bus</label>
                            <select class="form-select" name="bus_id" required>
                                <option value="">-- Choose Bus --</option>
                                <?php foreach ($active_buses as $b): ?>
                                    <option value="<?php echo $b['id']; ?>"><?php echo e($b['bus_number']); ?> - <?php echo e($b['bus_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Origin City</label>
                            <input type="text" class="form-control" name="origin" placeholder="e.g. Colombo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Destination City</label>
                            <input type="text" class="form-control" name="destination" placeholder="e.g. Kandy" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Departure Time</label>
                            <input type="text" class="form-control" name="departure_time" placeholder="08:30 AM" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fare (LKR)</label>
                            <input type="number" step="0.01" class="form-control" name="fare" placeholder="2500.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Distance (km)</label>
                            <input type="number" class="form-control" name="distance_km" placeholder="115" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estimated Duration</label>
                        <input type="text" class="form-control" name="estimated_duration" placeholder="e.g. 3h 15m" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Intermediate Transit Stops (comma-separated)</label>
                        <input type="text" class="form-control" name="intermediate_stops" placeholder="Kadawatha, Nittambuwa, Warakapola, Kegalle">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Starting Terminal Location</label>
                            <input type="text" class="form-control" name="start_location" placeholder="Colombo Bastian Mawatha Private Stand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ending Terminal Location</label>
                            <input type="text" class="form-control" name="end_location" placeholder="Kandy Goods Shed Terminal">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm fw-bold">Save Route</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
