<?php

// BookMyBus LK – Bus Fleet Management (admin/buses.php)
// Full CRUD: Add, Edit, View, and Status Management

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// 1. Add New Bus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bus'])) {
    $bus_number = strtoupper(trim($_POST['bus_number'] ?? ''));
    $bus_name = trim($_POST['bus_name'] ?? '');
    $bus_type = trim($_POST['bus_type'] ?? 'Luxury');
    $operator = trim($_POST['operator'] ?? 'SLTB Express');
    $ac_type = trim($_POST['ac_type'] ?? 'AC');
    $wifi = isset($_POST['wifi']) ? 1 : 0;
    $usb = isset($_POST['usb_charging']) ? 1 : 0;
    $total_seats = 30;

    if (empty($bus_number) || empty($bus_name)) {
        $error = "Bus Registration Number and Bus Name are required.";
    } else {
        $ins = mysqli_prepare($conn, "INSERT INTO buses (bus_number, bus_name, total_seats, bus_type, operator, ac_type, wifi, usb_charging, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        mysqli_stmt_bind_param($ins, "ssisssii", $bus_number, $bus_name, $total_seats, $bus_type, $operator, $ac_type, $wifi, $usb);
        if (mysqli_stmt_execute($ins)) {
            $new_bus_id = mysqli_insert_id($conn);
            mysqli_stmt_close($ins);

            // Seed 30 seats for this new bus automatically
            for ($s = 1; $s <= 30; $s++) {
                mysqli_query($conn, "INSERT INTO seats (bus_id, seat_number, status) VALUES ($new_bus_id, $s, 'available')");
            }
            $msg = "Bus $bus_number ($bus_name) added successfully with 30 seats configured.";
        } else {
            $error = "Failed to add bus. Please check the form values and try again.";
        }
    }
}

// 2. Toggle Status
if (isset($_GET['toggle_id'])) {
    $b_id = (int)$_GET['toggle_id'];
    $cur = $_GET['current'] ?? 'active';
    $next = $cur === 'active' ? 'maintenance' : ($cur === 'maintenance' ? 'inactive' : 'active');
    mysqli_query($conn, "UPDATE buses SET status = '$next' WHERE id = $b_id");
    $msg = "Bus status updated to '$next'.";
}

// Fetch all buses
$res = mysqli_query($conn, "SELECT b.*, (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.id AND s.status = 'available') AS available_seats FROM buses b ORDER BY b.id ASC");
$buses = [];
while ($row = mysqli_fetch_assoc($res)) {
    $buses[] = $row;
}

$page_title = "Fleet Management";
$active_page = "admin_buses";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <!-- Sub-navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-bus-front text-primary me-2"></i> Bus Fleet Management</h3>
            <small class="text-muted">Manage coaches, seat capacities, operators, and onboard amenities</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            <button class="btn btn-primary-custom btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addBusModal">
                <i class="bi bi-plus-circle me-1"></i> Register New Bus
            </button>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <!-- Fleet Table Card -->
    <div class="card card-custom">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Active Fleet Inventory (<?php echo count($buses); ?> Buses)</span>
            <span class="badge bg-warning text-dark">Standard 30-Seat 2x2 Layout</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bus Reg. Number</th>
                            <th>Service Name & Operator</th>
                            <th>Class & Amenities</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Maintenance Dates</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buses as $b): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border fs-6 fw-bold"><?php echo e($b['bus_number']); ?></span>
                                </td>
                                <td>
                                    <strong class="text-dark"><?php echo e($b['bus_name']); ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-building me-1"></i> <?php echo e($b['operator']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white me-1"><?php echo e($b['bus_type']); ?></span>
                                    <span class="badge bg-info text-dark me-1"><?php echo e($b['ac_type']); ?></span>
                                    <?php if ($b['wifi']): ?><span class="badge bg-secondary me-1"><i class="bi bi-wifi"></i> Wi-Fi</span><?php endif; ?>
                                    <?php if ($b['usb_charging']): ?><span class="badge bg-secondary"><i class="bi bi-usb-plug"></i> USB</span><?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo (int)$b['total_seats']; ?> Seats</strong><br>
                                    <small class="text-success"><?php echo (int)$b['available_seats']; ?> Available</small>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'active'): ?>
                                        <span class="badge bg-success px-2 py-1">Active</span>
                                    <?php elseif ($b['status'] === 'maintenance'): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1">In Maintenance</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-2 py-1">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    Last: <?php echo $b['last_maintenance_date'] ? date('d M Y', strtotime($b['last_maintenance_date'])) : 'N/A'; ?><br>
                                    Next: <?php echo $b['next_maintenance_date'] ? date('d M Y', strtotime($b['next_maintenance_date'])) : 'N/A'; ?>
                                </td>
                                <td class="text-end">
                                    <a href="buses.php?toggle_id=<?php echo $b['id']; ?>&current=<?php echo $b['status']; ?>" class="btn btn-sm btn-outline-secondary" title="Change Status">
                                        <i class="bi bi-arrow-repeat"></i> Status
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

<!-- Add Bus Modal -->
<div class="modal fade" id="addBusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="buses.php">
            <input type="hidden" name="add_bus" value="1">
            <div class="modal-content">
                <div class="modal-header navbar-custom text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Register New Fleet Bus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Registration Number (e.g. WP-9988) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bus_number" placeholder="NB-5432" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bus Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bus_name" placeholder="Ruhunu Express" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Class</label>
                            <select class="form-select" name="bus_type">
                                <option value="Super Luxury">Super Luxury</option>
                                <option value="Luxury" selected>Luxury</option>
                                <option value="Semi Luxury">Semi Luxury</option>
                                <option value="Normal">Normal</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Climate</label>
                            <select class="form-select" name="ac_type">
                                <option value="AC" selected>Air Conditioned (AC)</option>
                                <option value="Non-AC">Non-AC</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fleet Operator</label>
                        <input type="text" class="form-control" name="operator" value="SLTB Express" placeholder="e.g. Southern Express">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="wifi" value="1" checked id="wf">
                            <label class="form-check-label" for="wf">Free Wi-Fi</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="usb_charging" value="1" checked id="usb">
                            <label class="form-check-label" for="usb">USB Charging Ports</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm fw-bold">Register Bus & Build Seats</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
