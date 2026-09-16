<?php

// BookMyBus LK – Customer Dashboard & Bookings (my_bookings.php)
// Comprehensive Passenger Booking History, Reviews & Cancellation

require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = (int)($_POST['booking_id'] ?? 0);

        $chk_sql = "SELECT b.*, r.bus_id FROM bookings b JOIN routes r ON b.route_id = r.id WHERE b.id = ? AND b.user_id = ?";
    $chk_stmt = mysqli_prepare($conn, $chk_sql);
    mysqli_stmt_bind_param($chk_stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($chk_stmt);
    $b_row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk_stmt));
    mysqli_stmt_close($chk_stmt);

    if (!$b_row) {
        $error_msg = "Booking not found or unauthorized.";
    } elseif ($b_row['booking_status'] === 'cancelled') {
        $error_msg = "This booking has already been cancelled.";
    } else {
                mysqli_begin_transaction($conn);
        try {
                        $upd_b = mysqli_prepare($conn, "UPDATE bookings SET booking_status = 'cancelled', payment_status = 'refunded' WHERE id = ?");
            mysqli_stmt_bind_param($upd_b, "i", $booking_id);
            mysqli_stmt_execute($upd_b);
            mysqli_stmt_close($upd_b);

                        $upd_s = mysqli_prepare($conn, "UPDATE seats SET status = 'available' WHERE bus_id = ? AND seat_number = ?");
            mysqli_stmt_bind_param($upd_s, "ii", $b_row['bus_id'], $b_row['seat_number']);
            mysqli_stmt_execute($upd_s);
            mysqli_stmt_close($upd_s);

                        $notif = mysqli_prepare($conn, "INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Cancelled', ?, 'cancellation')");
            $msg = "Your booking " . $b_row['booking_ref'] . " (Seat #" . $b_row['seat_number'] . ") has been successfully cancelled and seat freed.";
            mysqli_stmt_bind_param($notif, "is", $user_id, $msg);
            mysqli_stmt_execute($notif);
            mysqli_stmt_close($notif);

            mysqli_commit($conn);
            $success_msg = "Booking " . $b_row['booking_ref'] . " has been successfully cancelled. Seat #" . $b_row['seat_number'] . " is now released.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_msg = "Cancellation could not be completed. Please try again.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rev_booking_id = (int)($_POST['booking_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 5);
    $driver_rating = (int)($_POST['driver_rating'] ?? 5);
    $comfort_rating = (int)($_POST['comfort_rating'] ?? 5);
    $cleanliness_rating = (int)($_POST['cleanliness_rating'] ?? 5);
    $punctuality_rating = (int)($_POST['punctuality_rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    // Get bus and route for booking
    $b_query = mysqli_prepare($conn, "SELECT route_id, (SELECT bus_id FROM routes WHERE id = bookings.route_id) AS bus_id FROM bookings WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($b_query, "ii", $rev_booking_id, $user_id);
    mysqli_stmt_execute($b_query);
    $b_info = mysqli_fetch_assoc(mysqli_stmt_get_result($b_query));
    mysqli_stmt_close($b_query);

    if ($b_info) {
        $rev_sql = "INSERT INTO reviews (user_id, route_id, bus_id, booking_id, rating, driver_rating, comfort_rating, cleanliness_rating, punctuality_rating, comment, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
        $rev_stmt = mysqli_prepare($conn, $rev_sql);
        mysqli_stmt_bind_param($rev_stmt, "iiiiiiiiis", $user_id, $b_info['route_id'], $b_info['bus_id'], $rev_booking_id, $rating, $driver_rating, $comfort_rating, $cleanliness_rating, $punctuality_rating, $comment);
        if (mysqli_stmt_execute($rev_stmt)) {
            $success_msg = "Thank you! Your trip review and ratings have been recorded.";
        } else {
            $error_msg = "Failed to submit review.";
        }
        mysqli_stmt_close($rev_stmt);
    }
}

$sql = "SELECT b.*, r.origin, r.destination, r.departure_time, r.fare,
               bu.bus_name, bu.bus_number, bu.bus_type, bu.operator,
               (SELECT COUNT(*) FROM reviews rev WHERE rev.booking_id = b.id) AS has_reviewed
        FROM bookings b
        JOIN routes r ON b.route_id = r.id
        JOIN buses bu ON r.bus_id = bu.id
        WHERE b.user_id = ?
        ORDER BY b.id DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$bookings = [];
$total_count = 0;
$upcoming_count = 0;
$completed_count = 0;
$cancelled_count = 0;
$next_trip = null;

while ($row = mysqli_fetch_assoc($res)) {
    $bookings[] = $row;
    $total_count++;
    if ($row['booking_status'] === 'confirmed') {
        $upcoming_count++;
        if (!$next_trip) $next_trip = $row;
    } elseif ($row['booking_status'] === 'completed') {
        $completed_count++;
    } elseif ($row['booking_status'] === 'cancelled') {
        $cancelled_count++;
    }
}
mysqli_stmt_close($stmt);

$tab = $_GET['tab'] ?? 'all';

$page_title = "My Bookings & Dashboard";
$active_page = "my_bookings";
include 'includes/header.php';
?>

<div class="container my-5">

    <!-- Welcome Greeting & Quick Booking CTA -->
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold mb-1 text-dark">
                <span class="section-label me-2"></span> <?php echo e($_SESSION['user_name']); ?>
            </h3>
            <p class="text-muted mb-0">Manage your bus reservations, e-tickets, and travel feedback.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="index.php#search-section" class="btn btn-qs-primary fw-bold shadow-sm">
                <i class="bi bi-search me-1"></i> Book New Bus Ticket
            </a>
        </div>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Booking Statistics Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="text-muted small fw-bold">TOTAL BOOKINGS</div>
                <div class="fs-3 fw-bold text-dark"><?php echo $total_count; ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card gold">
                <div class="text-muted small fw-bold">UPCOMING TRIPS</div>
                <div class="fs-3 fw-bold text-primary"><?php echo $upcoming_count; ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card green">
                <div class="text-muted small fw-bold">COMPLETED</div>
                <div class="fs-3 fw-bold text-success"><?php echo $completed_count; ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card maroon">
                <div class="text-muted small fw-bold">CANCELLED</div>
                <div class="fs-3 fw-bold text-danger"><?php echo $cancelled_count; ?></div>
            </div>
        </div>
    </div>

    <!-- Upcoming Journey Highlight Card (if any) -->
    <?php if ($next_trip): ?>
        <div class="card qs-card mb-5 border-start border-4 border-warning">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold">
                            <i class="bi bi-clock-history me-1"></i> NEXT UPCOMING JOURNEY
                        </span>
                        <h4 class="fw-bold text-dark mb-1">
                            <?php echo e($next_trip['origin']); ?> &rarr; <?php echo e($next_trip['destination']); ?>
                        </h4>
                        <div class="text-muted small mb-2">
                            <span><i class="bi bi-bus-front me-1"></i> <?php echo e($next_trip['bus_name']); ?> (<?php echo e($next_trip['bus_number']); ?>)</span>
                            <span class="mx-2">&bull;</span>
                            <span><i class="bi bi-clock me-1"></i> Departure: <strong><?php echo e($next_trip['departure_time']); ?></strong></span>
                            <span class="mx-2">&bull;</span>
                            <span class="badge bg-primary fs-6 px-2 py-1">Seat #<?php echo (int)$next_trip['seat_number']; ?></span>
                        </div>
                        <div class="small text-muted">
                            Ref: <strong><?php echo e($next_trip['booking_ref']); ?></strong> | Total Paid: <strong class="text-success"><?php echo format_lkr($next_trip['total_amount']); ?></strong>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="ticket.php?ref=<?php echo urlencode($next_trip['booking_ref']); ?>" class="btn btn-qs-primary fw-bold shadow-sm">
                            <i class="bi bi-ticket-detailed me-1"></i> View E-Ticket & QR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bookings Tabs -->
    <div class="card qs-card">
        <div class="card-header qs-card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="fw-bold fs-5"><i class="bi bi-journal-bookmark-fill me-2"></i> Booking History</div>
            <ul class="nav nav-pills small">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $tab === 'all' ? 'active bg-warning text-dark fw-bold' : ''; ?>" href="my_bookings.php?tab=all">All (<?php echo $total_count; ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $tab === 'upcoming' ? 'active bg-warning text-dark fw-bold' : ''; ?>" href="my_bookings.php?tab=upcoming">Upcoming (<?php echo $upcoming_count; ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $tab === 'completed' ? 'active bg-warning text-dark fw-bold' : ''; ?>" href="my_bookings.php?tab=completed">Completed (<?php echo $completed_count; ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $tab === 'cancelled' ? 'active bg-warning text-dark fw-bold' : ''; ?>" href="my_bookings.php?tab=cancelled">Cancelled (<?php echo $cancelled_count; ?>)</a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <?php
            $filtered_bookings = array_filter($bookings, function($b) use ($tab) {
                if ($tab === 'upcoming') return $b['booking_status'] === 'confirmed';
                if ($tab === 'completed') return $b['booking_status'] === 'completed';
                if ($tab === 'cancelled') return $b['booking_status'] === 'cancelled';
                return true;
            });
            ?>

            <?php if (empty($filtered_bookings)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-ticket-perforated fs-1 d-block mb-3 opacity-50"></i>
                    <h5>No bookings found in this category.</h5>
                    <p class="small mb-3">Ready to plan your next travel across Sri Lanka?</p>
                    <a href="index.php#search-section" class="btn btn-qs-primary btn-sm">Search Scheduled Buses</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Route</th>
                                <th>Bus Service</th>
                                <th>Seat</th>
                                <th>Fare</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_bookings as $b): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo e($b['booking_ref']); ?></span><br>
                                        <small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($b['booking_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo e($b['origin']); ?> &rarr; <?php echo e($b['destination']); ?></strong><br>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i> <?php echo e($b['departure_time']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo e($b['bus_name']); ?></div>
                                        <small class="text-muted"><?php echo e($b['bus_number']); ?> &bull; <span class="badge bg-light text-dark border"><?php echo e($b['bus_type']); ?></span></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6 px-3 py-1">Seat #<?php echo (int)$b['seat_number']; ?></span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?php echo format_lkr($b['total_amount']); ?><br>
                                        <small class="text-success"><?php echo e($b['payment_method']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($b['booking_status'] === 'confirmed'): ?>
                                            <span class="badge bg-success px-2 py-1"><i class="bi bi-check2-circle me-1"></i> Confirmed</span>
                                        <?php elseif ($b['booking_status'] === 'completed'): ?>
                                            <span class="badge bg-secondary px-2 py-1"><i class="bi bi-flag me-1"></i> Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="ticket.php?ref=<?php echo urlencode($b['booking_ref']); ?>" class="btn btn-sm btn-outline-primary" title="View Digital Ticket">
                                                <i class="bi bi-ticket-detailed"></i>
                                            </a>
                                            <?php if ($b['booking_status'] === 'confirmed'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Cancel Booking" onclick="confirmCancel(<?php echo $b['id']; ?>, '<?php echo e($b['booking_ref']); ?>', <?php echo $b['seat_number']; ?>)">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($b['booking_status'] === 'completed' && $b['has_reviewed'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark" title="Review Trip" onclick="openReviewModal(<?php echo $b['id']; ?>, '<?php echo e($b['origin']); ?> &rarr; <?php echo e($b['destination']); ?>')">
                                                    <i class="bi bi-star-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="my_bookings.php">
            <input type="hidden" name="cancel_booking" value="1">
            <input type="hidden" name="booking_id" id="cancel_booking_id" value="">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Cancel Bus Booking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel booking <strong id="cancel_ref"></strong>?</p>
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle me-1"></i> Seat #<span id="cancel_seat"></span> will be immediately released back to other passengers, and the ticket status marked as refunded.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Keep Booking</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold">Confirm Cancellation</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Review Trip Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="my_bookings.php">
            <input type="hidden" name="submit_review" value="1">
            <input type="hidden" name="booking_id" id="review_booking_id" value="">
            <div class="modal-content">
                <div class="modal-header qs-navbar text-white">
                    <h5 class="modal-title"><i class="bi bi-star-fill text-warning me-2"></i> Rate Your Journey</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold mb-3" id="review_route_title"></p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Overall Experience (1 to 5 Stars):</label>
                        <select class="form-select" name="rating" required>
                            <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
                            <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                            <option value="3">⭐⭐⭐ 3 - Average</option>
                            <option value="2">⭐⭐ 2 - Below Expectations</option>
                            <option value="1">⭐ 1 - Poor</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Driver Safety (1-5)</label>
                            <input type="number" class="form-control form-control-sm" name="driver_rating" min="1" max="5" value="5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Comfort & AC (1-5)</label>
                            <input type="number" class="form-control form-control-sm" name="comfort_rating" min="1" max="5" value="5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Bus Cleanliness (1-5)</label>
                            <input type="number" class="form-control form-control-sm" name="cleanliness_rating" min="1" max="5" value="5" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Punctuality (1-5)</label>
                            <input type="number" class="form-control form-control-sm" name="punctuality_rating" min="1" max="5" value="5" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Review / Comments:</label>
                        <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience regarding comfort, driver behavior, or timings..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold">Submit Review</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCancel(id, ref, seat) {
    document.getElementById('cancel_booking_id').value = id;
    document.getElementById('cancel_ref').innerText = ref;
    document.getElementById('cancel_seat').innerText = seat;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function openReviewModal(id, title) {
    document.getElementById('review_booking_id').value = id;
    document.getElementById('review_route_title').innerText = title;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
