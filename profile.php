<?php

require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $nic = trim($_POST['nic'] ?? '');

    if (empty($name) || empty($phone)) {
        $error_msg = "Name and Phone Number are required.";
    } elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
        $error_msg = "Phone number must be a valid 10-digit Sri Lankan number (07XXXXXXXX).";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, phone = ?, nic = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $nic, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_nic'] = $nic;
            $success_msg = "Your profile details have been updated successfully.";
        } else {
            $error_msg = "Profile update failed. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = "All password fields are required.";
    } elseif (strlen($new_pass) < 6) {
        $error_msg = "New password must be at least 6 characters long.";
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = "New password and confirmation do not match.";
    } else {
        // Fetch current hashed password
        $fetch_stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($fetch_stmt, "i", $user_id);
        mysqli_stmt_execute($fetch_stmt);
        $res = mysqli_stmt_get_result($fetch_stmt);
        $u = mysqli_fetch_assoc($res);
        mysqli_stmt_close($fetch_stmt);

        if ($u && password_verify($current_pass, $u['password'])) {
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $upd_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd_stmt, "si", $new_hash, $user_id);
            if (mysqli_stmt_execute($upd_stmt)) {
                $success_msg = "Password updated successfully.";
            } else {
                $error_msg = "Failed to update password.";
            }
            mysqli_stmt_close($upd_stmt);
        } else {
            $error_msg = "Current password is incorrect.";
        }
    }
}

// Fetch fresh user data
$stmt = mysqli_prepare($conn, "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$page_title = "Account Profile";
$active_page = "profile";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card qs-card text-center p-4">
                <div class="mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
                </div>
                <h5 class="fw-bold mb-1"><?php echo e($user_data['name']); ?></h5>
                <p class="text-muted small mb-2"><?php echo e($user_data['email']); ?></p>
                <div>
                    <span class="badge bg-warning text-dark text-uppercase px-3 py-2 fw-bold">
                        <i class="bi bi-shield-check me-1"></i> <?php echo e($user_data['role_name']); ?>
                    </span>
                </div>
                <hr class="my-3">
                <ul class="list-unstyled text-start small mb-0">
                    <li class="mb-2"><strong>Phone:</strong> <?php echo e($user_data['phone']); ?></li>
                    <li class="mb-2"><strong>NIC:</strong> <?php echo e($user_data['nic'] ?: 'Not Provided'); ?></li>
                    <li class="mb-2"><strong>Status:</strong> <span class="badge bg-success"><?php echo ucfirst($user_data['status']); ?></span></li>
                    <li><strong>Member Since:</strong> <?php echo date('d M Y', strtotime($user_data['created_at'])); ?></li>
                </ul>
            </div>
        </div>

        
        <div class="col-lg-8">

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

            
            <div class="card qs-card mb-4">
                <div class="card-header qs-card-header py-3">
                    <i class="bi bi-person-lines-fill me-2"></i> Edit Personal Details
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="profile.php">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address (Immutable)</label>
                            <input type="email" class="form-control bg-light" value="<?php echo e($user_data['email']); ?>" disabled>
                            <small class="text-muted">Contact administrator to modify registered email.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo e($user_data['name']); ?>" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo e($user_data['phone']); ?>" pattern="07[0-9]{8}" maxlength="10" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIC Number</label>
                                <input type="text" class="form-control" name="nic" value="<?php echo e($user_data['nic']); ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-qs-primary fw-bold">
                            <i class="bi bi-check2 me-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            
            <div class="card qs-card">
                <div class="card-header qs-card-header py-3">
                    <i class="bi bi-key-fill me-2"></i> Change Password
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="profile.php">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" name="new_password" minlength="6" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-danger fw-bold">
                            <i class="bi bi-shield-lock me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
