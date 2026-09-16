<?php

// BookMyBus LK – Booking Checkout & ACID Transaction (confirm.php)
// Prevents Double-Booking with Row-Level Locking (FOR UPDATE)

require_once 'includes/auth.php';

$error_message   = '';
$booking_success = false;
$ticket_data     = null;

$route_id    = isset($_POST['route_id'])    ? (int)$_POST['route_id']    : (isset($_GET['route_id'])    ? (int)$_GET['route_id']    : 0);
$schedule_id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : (isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0);
$seat_number = isset($_POST['seat_number']) ? (int)$_POST['seat_number'] : (isset($_GET['seat_number']) ? (int)$_GET['seat_number'] : 0);
$travel_date = trim($_POST['travel_date'] ?? date('Y-m-d'));

if ($route_id <= 0 || $seat_number <= 0) {
    header("Location: index.php");
    exit;
}

$route_sql = "SELECT r.*, b.id AS bus_id, b.bus_name, b.bus_number, b.bus_type, b.operator, b.ac_type
              FROM routes r
              JOIN buses b ON r.bus_id = b.id
              WHERE r.id = ?";
$stmt = mysqli_prepare($conn, $route_sql);
mysqli_stmt_bind_param($stmt, "i", $route_id);
mysqli_stmt_execute($stmt);
$route = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$route) {
    header("Location: index.php");
    exit;
}

$bus_id    = (int)$route['bus_id'];
$base_fare = (float)$route['fare'];

$logged_user    = current_user();
$default_name   = $logged_user['name']  ?? '';
$default_phone  = $logged_user['phone'] ?? '';
$default_nic    = $logged_user['nic']   ?? '';

$passenger_name  = trim($_POST['passenger_name']  ?? $default_name);
$passenger_phone = trim($_POST['passenger_phone'] ?? $default_phone);
$passenger_nic   = trim($_POST['passenger_nic']   ?? $default_nic);
$payment_method  = trim($_POST['payment_method']  ?? 'Card');
$promo_code      = strtoupper(trim($_POST['promo_code'] ?? ''));

$discount   = 0.00;
if ($promo_code === 'RIDE10' || $promo_code === 'STUDENT') {
    $discount = $base_fare * 0.10;
}
$final_fare = max(0, $base_fare - $discount);

$is_confirm_action = isset($_POST['confirm_booking']);

if ($is_confirm_action) {
    if (empty($passenger_name)) {
        $error_message = "Passenger full name is required.";
    } elseif (strlen($passenger_name) < 2) {
        $error_message = "Passenger name must be at least 2 characters.";
    } elseif (empty($passenger_phone)) {
        $error_message = "Mobile phone number is required.";
    } elseif (!preg_match('/^07[0-9]{8}$/', $passenger_phone)) {
        $error_message = "Please enter a valid 10-digit Sri Lankan mobile number (e.g., 0771234567).";
    } else {
                mysqli_begin_transaction($conn);
        try {
            $check_sql  = "SELECT status FROM seats WHERE bus_id = ? AND seat_number = ? FOR UPDATE";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "ii", $bus_id, $seat_number);
            mysqli_stmt_execute($check_stmt);
            $seat_row = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));
            mysqli_stmt_close($check_stmt);

            if (!$seat_row || $seat_row['status'] !== 'available') {
                mysqli_rollback($conn);
                $error_message = "Seat #" . $seat_number . " has just been booked by another passenger. Please go back and choose a different seat.";
            } else {
                // Generate booking reference & QR token
                $booking_ref  = 'BMB-' . date('Y') . '-' . str_pad((string)mt_rand(1000, 99999), 5, '0', STR_PAD_LEFT);
                $qr_token     = hash('sha256', $booking_ref . microtime());
                $user_id_val  = $logged_user ? (int)$logged_user['id'] : null;
                $sched_id_val = $schedule_id > 0 ? $schedule_id : null;

                                $ins_sql  = "INSERT INTO bookings (booking_ref, user_id, route_id, schedule_id, passenger_name, passenger_phone, passenger_nic, seat_number, total_amount, payment_method, payment_status, booking_status, qr_token)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'confirmed', ?)";
                $ins_stmt = mysqli_prepare($conn, $ins_sql);
                mysqli_stmt_bind_param($ins_stmt, "siiissssdss", $booking_ref, $user_id_val, $route_id, $sched_id_val, $passenger_name, $passenger_phone, $passenger_nic, $seat_number, $final_fare, $payment_method, $qr_token);
                mysqli_stmt_execute($ins_stmt);
                $new_booking_id = mysqli_insert_id($conn);
                mysqli_stmt_close($ins_stmt);

                                $txn_ref  = 'TXN-BMB-' . strtoupper(substr(md5(uniqid()), 0, 10));
                $pay_sql  = "INSERT INTO payments (booking_id, amount, payment_method, transaction_ref, status) VALUES (?, ?, ?, ?, 'paid')";
                $pay_stmt = mysqli_prepare($conn, $pay_sql);
                mysqli_stmt_bind_param($pay_stmt, "idss", $new_booking_id, $final_fare, $payment_method, $txn_ref);
                mysqli_stmt_execute($pay_stmt);
                mysqli_stmt_close($pay_stmt);

                                $upd_sql  = "UPDATE seats SET status = 'booked' WHERE bus_id = ? AND seat_number = ?";
                $upd_stmt = mysqli_prepare($conn, $upd_sql);
                mysqli_stmt_bind_param($upd_stmt, "ii", $bus_id, $seat_number);
                mysqli_stmt_execute($upd_stmt);
                mysqli_stmt_close($upd_stmt);

                                if ($user_id_val) {
                    $notif_sql   = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'booking')";
                    $notif_title = "Booking Confirmed (" . $booking_ref . ")";
                    $notif_msg   = "Seat #" . $seat_number . " on " . $route['origin'] . " to " . $route['destination'] . " has been successfully reserved.";
                    $notif_stmt  = mysqli_prepare($conn, $notif_sql);
                    mysqli_stmt_bind_param($notif_stmt, "iss", $user_id_val, $notif_title, $notif_msg);
                    mysqli_stmt_execute($notif_stmt);
                    mysqli_stmt_close($notif_stmt);
                }

                mysqli_commit($conn);
                header("Location: ticket.php?ref=" . urlencode($booking_ref));
                exit;
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_message = "Your booking could not be processed. Please try again or contact support.";
        }
    }
}

$page_title  = "Confirm Booking — " . $route['origin'] . " to " . $route['destination'];
$active_page = "";
include 'includes/header.php';
?>

<div class="container" style="padding-top:36px;padding-bottom:60px">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Breadcrumb -->
            <div style="font-size:.78rem;color:var(--qs-text-muted);margin-bottom:20px">
                <a href="index.php" style="color:var(--qs-text-muted);text-decoration:none">Home</a>
                <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i>
                <a href="book.php?route_id=<?php echo (int)$route_id; ?>" style="color:var(--qs-text-muted);text-decoration:none">Select Seat</a>
                <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i>
                <span style="color:var(--qs-navy);font-weight:600">Confirm Booking</span>
            </div>

            <!-- Error message -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4" role="alert">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i> <?php echo e($error_message); ?>
                    <div class="mt-2">
                        <a href="book.php?route_id=<?php echo (int)$route_id; ?>" class="btn-qs-secondary btn-qs-sm">
                            <i class="bi bi-arrow-left"></i> Choose Another Seat
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Page title -->
            <h2 style="font-size:1.5rem;font-weight:800;color:var(--qs-navy);letter-spacing:-0.5px;margin-bottom:6px">
                Confirm Your Booking
            </h2>
            <p style="color:var(--qs-text-muted);font-size:.9rem;margin-bottom:28px">
                Review your trip details and enter passenger information to complete the reservation.
            </p>

            <!-- Trip Summary Card -->
            <div class="qs-card mb-4">
                <div class="qs-card-header">
                    <i class="bi bi-bus-front-fill" style="color:var(--qs-orange)"></i>
                    Trip Summary
                </div>
                <div style="padding:20px">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted);margin-bottom:3px">Route</div>
                            <div style="font-size:1.15rem;font-weight:800;color:var(--qs-navy)">
                                <?php echo e($route['origin']); ?> &rarr; <?php echo e($route['destination']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted);margin-bottom:3px">Service</div>
                            <div style="font-weight:700;color:var(--qs-navy);font-size:.95rem">
                                <?php echo e($route['bus_name']); ?> (<?php echo e($route['bus_number']); ?>)
                            </div>
                            <div style="font-size:.8rem;color:var(--qs-text-muted)">
                                <?php echo e($route['operator']); ?> &bull; <?php echo e($route['bus_type']); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted);margin-bottom:3px">Departure</div>
                            <div style="font-weight:700;color:var(--qs-orange);font-size:.95rem"><?php echo date('h:i A', strtotime($route['departure_time'])); ?></div>
                        </div>
                        <div class="col-sm-4">
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted);margin-bottom:3px">Your Seat</div>
                            <div class="ticket-seat-big d-inline-block" style="font-size:1.1rem;padding:3px 12px">
                                Seat <?php echo (int)$seat_number; ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted);margin-bottom:3px">Fare</div>
                            <div style="font-size:1.15rem;font-weight:800;color:var(--qs-green)"><?php echo format_lkr($final_fare); ?></div>
                            <?php if ($discount > 0): ?>
                                <div style="font-size:.75rem;color:var(--qs-red)">
                                    <i class="bi bi-tag-fill me-1"></i>-<?php echo format_lkr($discount); ?> discount applied
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="qs-card">
                <div class="qs-card-header">
                    <i class="bi bi-person-check-fill" style="color:var(--qs-orange)"></i>
                    Passenger Details &amp; Payment
                </div>
                <div style="padding:24px">

                    <form action="confirm.php" method="POST">
                        <input type="hidden" name="route_id"    value="<?php echo (int)$route_id; ?>">
                        <input type="hidden" name="schedule_id" value="<?php echo (int)$schedule_id; ?>">
                        <input type="hidden" name="travel_date" value="<?php echo e($travel_date); ?>">
                        <input type="hidden" name="seat_number" value="<?php echo (int)$seat_number; ?>">
                        <input type="hidden" name="confirm_booking" value="1">

                        <!-- Section 1: Passenger -->
                        <div class="checkout-section-title">
                            <i class="bi bi-person me-1"></i> Passenger Information
                        </div>

                        <div class="mb-4">
                            <label class="qs-label" for="passenger_name">
                                Full Name <span style="color:var(--qs-red)">*</span>
                            </label>
                            <div class="qs-input-group">
                                <i class="bi bi-person qs-input-icon"></i>
                                <input type="text" class="qs-input" id="passenger_name" name="passenger_name"
                                       placeholder="e.g. Kasun Perera"
                                       value="<?php echo e($passenger_name); ?>"
                                       required minlength="2" maxlength="100">
                            </div>
                            <div style="font-size:.75rem;color:var(--qs-text-muted);margin-top:5px">
                                Enter the name exactly as it appears on your National ID or Driving License.
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="qs-label" for="passenger_phone">
                                    Mobile Number <span style="color:var(--qs-red)">*</span>
                                </label>
                                <div class="qs-input-group">
                                    <i class="bi bi-telephone qs-input-icon"></i>
                                    <input type="tel" class="qs-input" id="passenger_phone" name="passenger_phone"
                                           placeholder="0771234567"
                                           value="<?php echo e($passenger_phone); ?>"
                                           pattern="07[0-9]{8}" maxlength="10" required>
                                </div>
                                <div style="font-size:.75rem;color:var(--qs-text-muted);margin-top:5px">
                                    10 digits starting with 07
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="qs-label" for="passenger_nic">
                                    National ID (NIC) <span style="color:var(--qs-text-muted);font-weight:400;text-transform:none;letter-spacing:0">&mdash; Optional</span>
                                </label>
                                <div class="qs-input-group">
                                    <i class="bi bi-card-heading qs-input-icon"></i>
                                    <input type="text" class="qs-input" id="passenger_nic" name="passenger_nic"
                                           placeholder="e.g. 199812345678"
                                           value="<?php echo e($passenger_nic); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Payment -->
                        <div class="checkout-section-title">
                            <i class="bi bi-credit-card me-1"></i> Payment
                        </div>

                        <!-- Promo code -->
                        <div class="mb-4">
                            <label class="qs-label">Promo / Discount Code</label>
                            <div style="display:flex;gap:8px;max-width:340px">
                                <div class="qs-input-group" style="flex:1">
                                    <i class="bi bi-tag qs-input-icon"></i>
                                    <input type="text" class="qs-input" name="promo_code"
                                           placeholder="e.g. RIDE10"
                                           value="<?php echo e($promo_code); ?>">
                                </div>
                                <button type="submit" class="btn-qs-secondary btn-qs-sm" style="flex-shrink:0">
                                    Apply
                                </button>
                            </div>
                            <div style="font-size:.75rem;color:var(--qs-text-muted);margin-top:5px">
                                Enter a valid promo code to receive a discount.
                            </div>
                        </div>

                        <!-- Payment method selection -->
                        <div class="mb-5">
                            <label class="qs-label">Payment Method</label>
                            <div class="row g-2">
                                <?php
                                $methods = [
                                    ['value'=>'Card',      'icon'=>'bi-credit-card-2-front', 'label'=>'Visa / MasterCard', 'sub'=>'Secure card payment'],
                                    ['value'=>'eZ Cash / mCash', 'icon'=>'bi-phone',               'label'=>'eZ Cash / mCash',   'sub'=>'Mobile wallet'],
                                    ['value'=>'Cash at Counter',  'icon'=>'bi-cash-stack',           'label'=>'Cash at Counter',   'sub'=>'Pay at bus stand'],
                                ];
                                foreach ($methods as $m):
                                    $checked = ($payment_method === $m['value']) ? 'checked' : '';
                                ?>
                                <div class="col-md-4">
                                    <label style="display:block;border:1.5px solid var(--qs-border);border-radius:var(--radius-md);padding:14px;cursor:pointer;transition:var(--transition);"
                                           onmouseover="this.style.borderColor='var(--qs-orange)'"
                                           onmouseout="this.style.borderColor='var(--qs-border)'">
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px">
                                            <input type="radio" name="payment_method" value="<?php echo e($m['value']); ?>" <?php echo $checked; ?> style="accent-color:var(--qs-orange)">
                                            <i class="bi <?php echo $m['icon']; ?>" style="color:var(--qs-orange)"></i>
                                            <strong style="font-size:.88rem;color:var(--qs-navy)"><?php echo $m['label']; ?></strong>
                                        </div>
                                        <div style="font-size:.75rem;color:var(--qs-text-muted);padding-left:22px"><?php echo $m['sub']; ?></div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="font-size:.75rem;color:var(--qs-text-muted);margin-top:8px;display:flex;align-items:center;gap:5px">
                                <i class="bi bi-shield-lock-fill" style="color:var(--qs-green)"></i>
                                Payments are processed securely. All transactions are recorded.
                            </div>
                        </div>

                        <!-- Fare summary line -->
                        <div style="background:var(--qs-bg);border:1px solid var(--qs-border);border-radius:var(--radius-md);padding:16px 20px;margin-bottom:24px">
                            <div style="display:flex;justify-content:space-between;align-items:center">
                                <div>
                                    <div style="font-weight:700;color:var(--qs-navy);font-size:.95rem">Total to Pay</div>
                                    <div style="font-size:.8rem;color:var(--qs-text-muted)">Seat <?php echo (int)$seat_number; ?> &bull; <?php echo e($route['origin']); ?> &rarr; <?php echo e($route['destination']); ?></div>
                                </div>
                                <div style="font-size:1.5rem;font-weight:800;color:var(--qs-green);letter-spacing:-0.5px">
                                    <?php echo format_lkr($final_fare); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <a href="book.php?route_id=<?php echo (int)$route_id; ?>" class="btn-qs-secondary btn-qs-sm">
                                <i class="bi bi-arrow-left"></i> Back to Seat Selection
                            </a>
                            <button type="submit" class="btn-qs-primary" style="padding:.75rem 1.8rem;font-size:.95rem;border-radius:var(--radius-md)">
                                <i class="bi bi-check-circle-fill"></i>
                                Confirm &amp; Get My Ticket &mdash; <?php echo format_lkr($final_fare); ?>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
