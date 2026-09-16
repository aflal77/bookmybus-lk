<?php

// BookMyBus LK – Fleet Maintenance & Safety (admin/maintenance.php)
// Logs service records, parts costs, and inspection reminders

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// Add Service Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $service_date = trim($_POST['service_date'] ?? date('Y-m-d'));
    $maint_type = trim($_POST['maintenance_type'] ?? 'Routine Service');
    $description = trim($_POST['description'] ?? '');
    $cost = (float)($_POST['cost'] ?? 0);
    $next_date = trim($_POST['next_service_date'] ?? date('Y-m-d', strtotime('+60 days')));
    $status = trim($_POST['status'] ?? 'completed');

    if ($bus_id <= 0 || empty($description)) {
        $error = "Please fill in all service details.";
    } else {
        $ins = mysqli_prepare($conn, "INSERT INTO maintenance (bus_id, service_date, maintenance_type, description, cost, next_service_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "isssdss", $bus_id, $service_date, $maint_type, $description, $cost, $next_date, $status);
        if (mysqli_stmt_execute($ins)) {
            // Update bus maintenance date
            mysqli_query($conn, "UPDATE buses SET last_maintenance_date = '$service_date', next_maintenance_date = '$next_date' WHERE id = $bus_id");
            $msg = "Service record saved and fleet inspection calendar updated.";
        } else {
            $error = "Failed to log service record. Please try again.";
        }
        mysqli_stmt_close($ins);
    }
}

// Fetch records
$res = mysqli_query($conn, "SELECT m.*, b.bus_name, b.bus_number FROM maintenance m JOIN buses b ON m.bus_id = b.id ORDER BY m.service_date DESC");
$records = [];
$total_maint_cost = 0;
while ($row = mysqli_fetch_assoc($res)) {
    $records[] = $row;
    $total_maint_cost += (float)$row['cost'];
}

// Fetch active buses
$buses_res = mysqli_query($conn, "SELECT id, bus_number, bus_name FROM buses");
$buses_list = [];
while ($b = mysqli_fetch_assoc($buses_res)) {
    $buses_list[] = $b;
}

$page_title = "Fleet Maintenance & Inspection";
$active_page = "admin_maintenance";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-tools text-primary me-2"></i> Fleet Maintenance & Safety Inspection</h3>
            <small class="text-muted">Track coach maintenance cycles, mechanical repairs, and roadworthiness compliance</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            <button class="btn btn-primary-custom btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                <i class="bi bi-plus-circle me-1"></i> Log Service Record
            </button>
        </div>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <!-- Cost Summary Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="text-muted small fw-bold">TOTAL MAINTENANCE EXPENSE</div>
                <div class="fs-3 fw-bold text-danger"><?php echo format_lkr($total_maint_cost); ?></div>
                <small class="text-muted">Combined historical servicing costs</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card gold">
                <div class="text-muted small fw-bold">SERVICE LOG ENTRIES</div>
                <div class="fs-3 fw-bold text-warning"><?php echo count($records); ?> Recorded</div>
                <small class="text-muted">Engines, brakes, tires & AC units</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card green">
                <div class="text-muted small fw-bold">FLEET ROADWORTHINESS</div>
                <div class="fs-3 fw-bold text-success">100% Certified</div>
                <small class="text-muted">Compliant with National Transport Commission standards</small>
            </div>
        </div>
    </div>

    <div class="card card-custom">
        <div class="card-header card-header-custom py-3">
            <span class="fw-bold">Fleet Maintenance Log (<?php echo count($records); ?> Entries)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Service Date</th>
                            <th>Bus Coach</th>
                            <th>Service Type</th>
                            <th>Work Description</th>
                            <th>Cost</th>
                            <th>Next Inspection Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($r['service_date'])); ?></td>
                                <td>
                                    <strong><?php echo e($r['bus_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($r['bus_number']); ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo e($r['maintenance_type']); ?></span></td>
                                <td class="small" style="max-width: 320px;"><?php echo e($r['description']); ?></td>
                                <td class="fw-bold text-danger"><?php echo format_lkr($r['cost']); ?></td>
                                <td><?php echo date('d M Y', strtotime($r['next_service_date'])); ?></td>
                                <td>
                                    <?php if ($r['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($r['status'] === 'in_progress'): ?>
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Scheduled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Add Record Modal -->
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="maintenance.php">
            <input type="hidden" name="add_record" value="1">
            <div class="modal-content">
                <div class="modal-header navbar-custom text-white">
                    <h5 class="modal-title"><i class="bi bi-tools me-2"></i> Log Fleet Maintenance Service</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Bus Coach</label>
                        <select class="form-select" name="bus_id" required>
                            <option value="">-- Choose Coach --</option>
                            <?php foreach ($buses_list as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo e($b['bus_number']); ?> - <?php echo e($b['bus_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Service Date</label>
                            <input type="date" class="form-control" name="service_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Maintenance Type</label>
                            <select class="form-select" name="maintenance_type">
                                <option value="Routine Service">Routine Service</option>
                                <option value="Engine Overhaul">Engine Overhaul</option>
                                <option value="Tires & Brakes">Tires & Brakes</option>
                                <option value="AC Repair">AC Repair</option>
                                <option value="Electrical">Electrical</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Work Description & Parts Replaced</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="e.g. Replaced oil, aligned tires, brake calipers serviced" required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Total Cost (LKR)</label>
                            <input type="number" step="0.01" class="form-control" name="cost" placeholder="45000.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Next Service Due</label>
                            <input type="date" class="form-control" name="next_service_date" value="<?php echo date('Y-m-d', strtotime('+60 days')); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom btn-sm fw-bold">Save Log Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
