<?php

// BookMyBus LK – Reporting & Analytics Module (admin/reports.php)
// Financial reporting, occupancy metrics, and CSV Export

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$start_date = trim($_GET['start_date'] ?? date('Y-m-01'));
$end_date = trim($_GET['end_date'] ?? date('Y-m-d'));
$status_filter = trim($_GET['status'] ?? '');

// Build query
$sql = "SELECT b.booking_ref, b.passenger_name, b.passenger_phone, b.seat_number, b.total_amount,
               b.payment_method, b.payment_status, b.booking_status, b.booking_date,
               r.origin, r.destination, r.departure_time, bu.bus_name, bu.bus_number
        FROM bookings b
        JOIN routes r ON b.route_id = r.id
        JOIN buses bu ON r.bus_id = bu.id
        WHERE DATE(b.booking_date) BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

if (!empty($status_filter)) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY b.id DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$report_rows = [];
$total_revenue = 0;
$total_seats_booked = 0;
$cancelled_count = 0;

while ($r = mysqli_fetch_assoc($res)) {
    $report_rows[] = $r;
    if ($r['booking_status'] === 'confirmed' || $r['booking_status'] === 'completed') {
        $total_revenue += (float)$r['total_amount'];
        $total_seats_booked++;
    } elseif ($r['booking_status'] === 'cancelled') {
        $cancelled_count++;
    }
}
mysqli_stmt_close($stmt);

// Handle Live CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=BookMyBus LK_Transport_Report_' . date('Ymd_His') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Booking Ref', 'Passenger Name', 'Phone', 'Route', 'Bus Service', 'Seat', 'Amount (LKR)', 'Payment Method', 'Payment Status', 'Booking Status', 'Booking Date']);

    foreach ($report_rows as $row) {
        fputcsv($output, [
            $row['booking_ref'],
            $row['passenger_name'],
            $row['passenger_phone'],
            $row['origin'] . ' to ' . $row['destination'],
            $row['bus_name'] . ' (' . $row['bus_number'] . ')',
            $row['seat_number'],
            $row['total_amount'],
            $row['payment_method'],
            $row['payment_status'],
            $row['booking_status'],
            $row['booking_date']
        ]);
    }
    fclose($output);
    exit;
}

$page_title = "Transport Analytics & Reports";
$active_page = "admin_reports";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-bar-graph text-primary me-2"></i> Operations & Revenue Analytics</h3>
            <small class="text-muted">Generate certified passenger reports, route occupancies, and export financial spreadsheets</small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer me-1"></i> Print Report</button>
            <a href="reports.php?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&status=<?php echo urlencode($status_filter); ?>&export=csv" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
            </a>
        </div>
    </div>

    <!-- Date Range & Status Filter Card -->
    <div class="card card-custom mb-4 no-print">
        <div class="card-body p-3">
            <form method="GET" action="reports.php" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="small text-muted fw-semibold">Start Date:</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo e($start_date); ?>">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted fw-semibold">End Date:</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo e($end_date); ?>">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted fw-semibold">Status:</label>
                    <select name="status" class="form-select">
                        <option value="">-- All Statuses --</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Metrics Highlights -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card green">
                <div class="text-muted small fw-bold">REVENUE (PERIOD)</div>
                <div class="fs-3 fw-bold text-success"><?php echo format_lkr($total_revenue); ?></div>
                <small class="text-muted">Settled ticket sales</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="text-muted small fw-bold">TICKETS RESERVED</div>
                <div class="fs-3 fw-bold text-dark"><?php echo $total_seats_booked; ?> Confirmed Seats</div>
                <small class="text-muted">Occupancy volume</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card maroon">
                <div class="text-muted small fw-bold">CANCELLATIONS</div>
                <div class="fs-3 fw-bold text-danger"><?php echo $cancelled_count; ?> Cancelled / Refunded</div>
                <small class="text-muted">Released seats</small>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card card-custom">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Report Manifest: <?php echo date('d M Y', strtotime($start_date)); ?> to <?php echo date('d M Y', strtotime($end_date)); ?></span>
            <span class="badge bg-warning text-dark"><?php echo count($report_rows); ?> Rows Included</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref</th>
                            <th>Date</th>
                            <th>Passenger</th>
                            <th>Route</th>
                            <th>Bus Service</th>
                            <th>Seat</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_rows)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No records match the selected date parameters.</td></tr>
                        <?php else: ?>
                            <?php foreach ($report_rows as $rw): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo e($rw['booking_ref']); ?></td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($rw['booking_date'])); ?></td>
                                    <td>
                                        <strong><?php echo e($rw['passenger_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo e($rw['passenger_phone']); ?></small>
                                    </td>
                                    <td><?php echo e($rw['origin']); ?> &rarr; <?php echo e($rw['destination']); ?></td>
                                    <td><?php echo e($rw['bus_name']); ?> (<?php echo e($rw['bus_number']); ?>)</td>
                                    <td><span class="badge bg-primary">#<?php echo (int)$rw['seat_number']; ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo format_lkr($rw['total_amount']); ?></td>
                                    <td>
                                        <?php if ($rw['booking_status'] === 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                        <?php elseif ($rw['booking_status'] === 'completed'): ?>
                                            <span class="badge bg-secondary">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
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
