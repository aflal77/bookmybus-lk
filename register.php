<?php

// BookMyBus LK – Passenger Registration (register.php)
// Register a new customer account

require_once 'includes/auth.php';

if (is_logged_in()) {
    header("Location: my_bookings.php");
    exit;
}

$error = '';
$name = '';
$email = '';
$phone = '';
$nic = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $nic = trim($_POST['nic'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($name) < 3) {
        $error = "Full Name must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
        $error = "Please enter a valid 10-digit Sri Lankan phone number (e.g., 0771234567).";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please re-enter.";
    } else {
                $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        $exists = mysqli_stmt_num_rows($check_stmt) > 0;
        mysqli_stmt_close($check_stmt);

        if ($exists) {
            $error = "An account with this email address already exists. Please sign in instead.";
        } else {
                        $hashed_pass = password_hash($password, PASSWORD_BCRYPT);
            $role_id = 4; 
            $insert_sql = "INSERT INTO users (role_id, name, email, phone, nic, password, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            if ($insert_stmt) {
                mysqli_stmt_bind_param($insert_stmt, "isssss", $role_id, $name, $email, $phone, $nic, $hashed_pass);
                if (mysqli_stmt_execute($insert_stmt)) {
                    $new_user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($insert_stmt);

                                        $notif_sql = "INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Welcome to BookMyBus LK!', 'Your passenger account has been created. Start searching and booking bus seats across Sri Lanka.', 'booking')";
                    $notif_stmt = mysqli_prepare($conn, $notif_sql);
                    if ($notif_stmt) {
                        mysqli_stmt_bind_param($notif_stmt, "i", $new_user_id);
                        mysqli_stmt_execute($notif_stmt);
                        mysqli_stmt_close($notif_stmt);
                    }

                                        $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_nic'] = $nic;
                    $_SESSION['user_role'] = 'customer';

                    $_SESSION['flash_success'] = "Account created successfully! Welcome to BookMyBus LK, " . htmlspecialchars($name) . ".";
                    header("Location: my_bookings.php");
                    exit;
                } else {
                    $error = "Registration could not be completed. Please try again.";
                    mysqli_stmt_close($insert_stmt);
                }
            } else {
                $error = "Registration could not be completed. Please try again.";
            }
        }
    }
}

$page_title = "Create Passenger Account";
$active_page = "register";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-9">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            
            <div class="card qs-card">
                <div class="card-header qs-card-header text-center py-4">
                    <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill me-2"></i> Create Passenger Account</h4>
                    <p class="small text-light opacity-75 mb-0">Join BookMyBus LK for fast seat reservations and digital e-tickets</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="register.php">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" placeholder="e.g. Kasun Perera" required minlength="3">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" placeholder="kasun@example.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="0771234567" pattern="07[0-9]{8}" maxlength="10" required>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">10 digits starting with 07</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nic" class="form-label fw-semibold">National ID Card (NIC) <small class="text-muted fw-normal">(Optional)</small></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-heading"></i></span>
                                <input type="text" class="form-control" id="nic" name="nic" value="<?php echo e($nic); ?>" placeholder="e.g. 199812345678 or 981234567V">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 chars" minlength="6" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" minlength="6" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-qs-primary w-100 py-2 fs-6 fw-bold shadow-sm mb-3">
                            <i class="bi bi-person-check-fill me-1"></i> Complete Registration
                        </button>

                        <div class="text-center mt-3">
                            <span class="text-muted small">Already registered?</span>
                            <a href="login.php" class="fw-bold text-primary text-decoration-none small ms-1">Sign In Here</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
