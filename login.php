<?php
require_once 'includes/auth.php';

if (is_logged_in()) {
    $u = current_user();
    if ($u['role'] === 'admin')       header('Location: admin/index.php');
    elseif ($u['role'] === 'staff')   header('Location: staff/index.php');
    elseif ($u['role'] === 'driver')  header('Location: driver/index.php');
    else                              header('Location: my_bookings.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $sql  = 'SELECT u.id, u.name, u.email, u.phone, u.nic, u.password, u.status, r.name AS role_name
                 FROM users u
                 JOIN roles r ON u.role_id = r.id
                 WHERE u.email = ? LIMIT 1';
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Your account is not active. Please contact our support team for assistance.';
                } else {
                    $_SESSION['user_id']    = (int)$user['id'];
                    $_SESSION['user_name']  = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'];
                    $_SESSION['user_nic']   = $user['nic'];
                    $_SESSION['user_role']  = $user['role_name'];

                    $_SESSION['flash_success'] = 'Welcome back, ' . $user['name'] . '!';

                    $redirect = $_SESSION['intended_url'] ?? null;
                    unset($_SESSION['intended_url']);

                    if ($redirect)                          header('Location: ' . $redirect);
                    elseif ($user['role_name'] === 'admin') header('Location: admin/index.php');
                    elseif ($user['role_name'] === 'staff') header('Location: staff/index.php');
                    elseif ($user['role_name'] === 'driver')header('Location: driver/index.php');
                    else                                    header('Location: my_bookings.php');
                    exit;
                }
            } else {
                $error = 'Invalid email address or password.';
            }
        } else {
            $error = 'Authentication error. Please try again.';
        }
    }
}

$page_title  = 'Sign In';
$active_page = 'login';
include 'includes/header.php';
?>

<div class="container" style="padding-top:48px;padding-bottom:72px">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="qs-card">
                <div class="qs-card-header text-center py-4">
                    <div style="font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:-0.3px">
                        <i class="bi bi-shield-lock me-2" style="color:var(--qs-peach)"></i>Sign In
                    </div>
                    <div style="font-size:.82rem;color:rgba(255,255,255,.6);margin-top:4px">
                        Access your bookings and digital tickets
                    </div>
                </div>
                <div style="padding:32px 36px">
                    <form method="POST" action="login.php" id="loginForm" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="email" class="qs-label">Email Address</label>
                            <div class="qs-input-group">
                                <i class="bi bi-envelope qs-input-icon"></i>
                                <input type="email" class="qs-input" id="email" name="email"
                                       value="<?php echo e($email); ?>"
                                       placeholder="Enter your email address"
                                       required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="qs-label mb-0">Password</label>
                                <a href="faq.php" style="font-size:.78rem;color:var(--qs-text-muted);text-decoration:none">Forgot password?</a>
                            </div>
                            <div class="qs-input-group">
                                <i class="bi bi-lock qs-input-icon"></i>
                                <input type="password" class="qs-input" id="password" name="password"
                                       placeholder="Enter your password" required>
                                <button type="button" class="qs-input-toggle" id="togglePwd" aria-label="Show/hide password" style="background:none;border:none;padding:0 12px;cursor:pointer;color:var(--qs-text-muted)">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-qs-primary w-100" style="justify-content:center;padding:.8rem;font-size:.95rem;border-radius:var(--radius-md)">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </button>
                    </form>

                    <div style="text-align:center;margin-top:24px;font-size:.85rem;color:var(--qs-text-muted)">
                        New to BookMyBus LK?
                        <a href="register.php" style="font-weight:700;color:var(--qs-orange);text-decoration:none;margin-left:4px">Create an account</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function () {
    var input = document.getElementById('password');
    var icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type  = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type  = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
