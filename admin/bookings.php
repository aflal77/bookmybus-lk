<?php

// BookMyBus LK – Master Bookings Management (admin/bookings.php)
// Reservation search, status updates, and cancellation

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// Status Update Action
if (isset($_GET['set_status']) && isset($_GET['booking_id'])) {
    $b_id = (int)$_GET['booking_id'];
    $new_st = trim($_GET['set_status']);

    if ($new_st === 'cancelled') {
        // Free seat in transaction
        $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT b.*, r.bus_id FROM bookings b JOIN routes r ON b.route_id = r.id WHERE b.id = $b_id"));
        if ($chk) {
            mysqli_begin_transaction($conn);
            mysqli_query($conn, "UPDATE bookings SET booking_status = 'cancelled', payment_status = 'refunded' WHERE id = $b_id");
            mysqli_query($conn, "UPDATE seats SET status = 'available' WHERE bus_id = {$chk['bus_id']} AND seat_number = {$chk['seat_number']}");
            mysqli_commit($conn);
            $msg = "Booking #$b_id cancelled and Seat #{$chk['seat_number']} released.";
        }
    } elseif ($new_st === 'completed') {
        mysqli_query($conn, "UPDATE bookings SET booking_status = 'completed' WHERE id = $b_id");
        $msg = "Booking #$b_id marked as completed.";
    }
}

// Search filters
$search = trim($_GET['q'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$sql = "SELECT b.*, r.origin, r.destination, r.departure_time, bu.bus_name, bu.bus_number
        FROM bookings b
        JOIN routes r ON b.route_id = r.id
        JOIN buses bu ON r.bus_id = bu.id
        WHERE 1=1";

if (!empty($search)) {
    $s_esc = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (b.booking_ref LIKE '%$s_esc%' OR b.passenger_name LIKE '%$s_esc%' OR b.passenger_phone LIKE '%$s_esc%')";
}
if (!empty($status_filter)) {
    $st_esc = mysqli_real_escape_string($conn, $status_filter);
    $sql .= " AND b.booking_status = '$st_esc'";
}

$sql .= " ORDER BY b.id DESC";
$res = mysqli_query($conn, $sql);
$bookings = [];
while ($row = mysqli_fetch_assoc($res)) {
    $bookings[] = $row;
}

$page_title = "Master Bookings Management";
$active_page = "admin_bookings";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-ticket-detailed text-primary me-2"></i> Master Bookings & Manifests</h3>
            <small class="text-muted">Review passenger tickets, check payment status, and manage seat reservations</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <!-- Filter Bar -->
    <div class="card card-custom mb-4">
        <div class="card-body p-3">
            <form method="GET" action="bookings.php" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by Booking Ref (RL-...), Name, or Phone" value="<?php echo e($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- All Booking Statuses --</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card card-custom">
        <div class="card-header card-header-custom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Passenger Bookings (<?php echo count($bookings); ?> Records)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking Ref</th>
                            <th>Passenger Contact</th>
                            <th>Route & Departure</th>
                            <th>Bus Service</th>
                            <th>Seat</th>
                            <th>Fare & Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No reservations found matching query.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo e($b['booking_ref']); ?></span><br>
                                        <small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($b['booking_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($b['passenger_name']); ?></strong><br>
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i> <?php echo e($b['passenger_phone']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($b['origin']); ?> &rarr; <?php echo e($b['destination']); ?></strong><br>
                                        <small class="text-danger fw-semibold"><?php echo e($b['departure_time']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo e($b['bus_name']); ?><br>
                                        <small class="text-muted"><?php echo e($b['bus_number']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6 px-3 py-1">#<?php echo (int)$b['seat_number']; ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success"><?php echo format_lkr($b['total_amount']); ?></div>
                                        <small class="text-muted"><?php echo e($b['payment_method']); ?> (<?php echo e($b['payment_status']); ?>)</small>
                                    </td>
                                    <td>
                                        <?php if ($b['booking_status'] === 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                        <?php elseif ($b['booking_status'] === 'completed'): ?>
                                            <span class="badge bg-secondary">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="../ticket.php?ref=<?php echo urlencode($b['booking_ref']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View Ticket">
                                                <i class="bi bi-ticket-detailed"></i>
                                            </a>
                                            <?php if ($b['booking_status'] === 'confirmed'): ?>
                                                <a href="bookings.php?booking_id=<?php echo $b['id']; ?>&set_status=completed" class="btn btn-sm btn-outline-success" title="Mark Completed">
                                                    <i class="bi bi-check2"></i>
                                                </a>
                                                <a href="bookings.php?booking_id=<?php echo $b['id']; ?>&set_status=cancelled" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this booking and release seat?')" title="Cancel & Refund">
                                                    <i class="bi bi-x-lg"></i>
                                                </a>
                                            <?php endif; ?>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
