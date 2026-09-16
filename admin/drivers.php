<?php

// BookMyBus LK – Driver Management (admin/drivers.php)
// Fleet driver roster and bus assignments

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// Assign Bus to Driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_bus'])) {
    $driver_id = (int)($_POST['driver_id'] ?? 0);
    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $b_val = $bus_id > 0 ? $bus_id : null;

    $stmt = mysqli_prepare($conn, "UPDATE drivers SET assigned_bus_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $b_val, $driver_id);
    if (mysqli_stmt_execute($stmt)) {
        $msg = "Driver coach assignment updated successfully.";
    } else {
        $error = "Failed to update assignment.";
    }
    mysqli_stmt_close($stmt);
}

// Fetch all drivers
$res = mysqli_query($conn, "SELECT d.*, u.name, u.email, u.phone, b.bus_number, b.bus_name
                            FROM drivers d
                            JOIN users u ON d.user_id = u.id
                            LEFT JOIN buses b ON d.assigned_bus_id = b.id
                            ORDER BY d.id ASC");
$drivers = [];
while ($row = mysqli_fetch_assoc($res)) {
    $drivers[] = $row;
}

// Fetch buses for dropdown
$buses_res = mysqli_query($conn, "SELECT id, bus_number, bus_name FROM buses WHERE status = 'active'");
$buses_list = [];
while ($b = mysqli_fetch_assoc($buses_res)) {
    $buses_list[] = $b;
}

$page_title = "Driver Management";
$active_page = "admin_drivers";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-steering-wheel text-primary me-2"></i> Fleet Driver Management</h3>
            <small class="text-muted">Monitor licensed drivers, experience ratings, and active bus allocations</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card card-custom">
        <div class="card-header card-header-custom py-3">
            <span class="fw-bold">Roster of Registered Drivers (<?php echo count($drivers); ?> Drivers)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Driver Name</th>
                            <th>Contact & NIC</th>
                            <th>Heavy Vehicle License</th>
                            <th>Experience</th>
                            <th>Assigned Bus</th>
                            <th>Status</th>
                            <th class="text-end">Assign Bus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drivers as $d): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle p-2 text-center fw-bold" style="width: 36px; height: 36px;">
                                            <?php echo strtoupper(substr($d['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong class="text-dark"><?php echo e($d['name']); ?></strong><br>
                                            <small class="text-muted"><?php echo e($d['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone me-1"></i> <?php echo e($d['phone']); ?></div>
                                    <small class="text-muted">NIC: <?php echo e($d['nic']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo e($d['license_number']); ?></span>
                                </td>
                                <td><?php echo (int)$d['experience_years']; ?> Years</td>
                                <td>
                                    <?php if ($d['bus_name']): ?>
                                        <span class="fw-bold text-primary"><?php echo e($d['bus_name']); ?></span>
                                        <small class="text-muted d-block">(<?php echo e($d['bus_number']); ?>)</small>
                                    <?php else: ?>
                                        <span class="text-muted small">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success"><?php echo ucfirst($d['status']); ?></span></td>
                                <td class="text-end">
                                    <form method="POST" action="drivers.php" class="d-inline-flex gap-1">
                                        <input type="hidden" name="assign_bus" value="1">
                                        <input type="hidden" name="driver_id" value="<?php echo $d['id']; ?>">
                                        <select name="bus_id" class="form-select form-select-sm" style="width: 170px;">
                                            <option value="0">-- None --</option>
                                            <?php foreach ($buses_list as $b): ?>
                                                <option value="<?php echo $b['id']; ?>" <?php echo $d['assigned_bus_id'] == $b['id'] ? 'selected' : ''; ?>>
                                                    <?php echo e($b['bus_number']); ?> - <?php echo e($b['bus_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary-custom">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
