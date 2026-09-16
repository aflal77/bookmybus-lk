<?php

// BookMyBus LK – User & Role Management (admin/users.php)
// Manage accounts, assign roles, and toggle status

$base_path = '../';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin', '../login.php');

$msg = '';
$error = '';

// 1. Change Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $target_uid = (int)($_POST['user_id'] ?? 0);
    $new_role_id = (int)($_POST['role_id'] ?? 4);

    if ($target_uid === $_SESSION['user_id']) {
        $error = "You cannot modify your own administrator role.";
    } else {
        $upd = mysqli_prepare($conn, "UPDATE users SET role_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "ii", $new_role_id, $target_uid);
        if (mysqli_stmt_execute($upd)) {
            $msg = "User role updated successfully.";
        } else {
            $error = "Failed to update role.";
        }
        mysqli_stmt_close($upd);
    }
}

// 2. Toggle Status
if (isset($_GET['toggle_status']) && isset($_GET['uid'])) {
    $target_uid = (int)$_GET['uid'];
    $cur_status = $_GET['toggle_status'];
    $next_st = $cur_status === 'active' ? 'suspended' : 'active';

    if ($target_uid === $_SESSION['user_id']) {
        $error = "You cannot suspend your own account.";
    } else {
        mysqli_query($conn, "UPDATE users SET status = '$next_st' WHERE id = $target_uid");
        $msg = "User status changed to '$next_st'.";
    }
}

// Fetch all users with role names
$res = mysqli_query($conn, "SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC");
$users = [];
while ($row = mysqli_fetch_assoc($res)) {
    $users[] = $row;
}

// Fetch roles
$roles_res = mysqli_query($conn, "SELECT * FROM roles");
$roles_list = [];
while ($ro = mysqli_fetch_assoc($roles_res)) {
    $roles_list[] = $ro;
}

$page_title = "User & Role Management";
$active_page = "admin_users";
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid my-4 px-lg-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0"><i class="bi bi-people text-primary me-2"></i> User & Access Control (RBAC)</h3>
            <small class="text-muted">Manage system administrators, station staff, drivers, and registered customers</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if (!empty($msg)): ?><div class="alert alert-success py-2"><?php echo e($msg); ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card card-custom">
        <div class="card-header card-header-custom py-3">
            <span class="fw-bold">Platform User Accounts (<?php echo count($users); ?> Users)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User Name & Email</th>
                            <th>Phone & NIC</th>
                            <th>Assigned Role</th>
                            <th>Account Status</th>
                            <th>Registered On</th>
                            <th class="text-end">Role / Access Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <strong class="text-dark"><?php echo e($u['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($u['email']); ?></small>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone me-1"></i> <?php echo e($u['phone']); ?></div>
                                    <small class="text-muted">NIC: <?php echo e($u['nic'] ?: 'Not Provided'); ?></small>
                                </td>
                                <td>
                                    <?php if ($u['role_name'] === 'admin'): ?>
                                        <span class="badge bg-danger text-uppercase px-2 py-1">Admin</span>
                                    <?php elseif ($u['role_name'] === 'staff'): ?>
                                        <span class="badge bg-warning text-dark text-uppercase px-2 py-1">Staff</span>
                                    <?php elseif ($u['role_name'] === 'driver'): ?>
                                        <span class="badge bg-info text-dark text-uppercase px-2 py-1">Driver</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary text-uppercase px-2 py-1">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-end">
                                    <form method="POST" action="users.php" class="d-inline-flex gap-1 align-items-center">
                                        <input type="hidden" name="update_role" value="1">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="role_id" class="form-select form-select-sm" style="width: 120px;" <?php echo $u['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                            <?php foreach ($roles_list as $rl): ?>
                                                <option value="<?php echo $rl['id']; ?>" <?php echo $u['role_id'] == $rl['id'] ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($rl['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" <?php echo $u['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>Save</button>
                                    </form>

                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?uid=<?php echo $u['id']; ?>&toggle_status=<?php echo $u['status']; ?>" class="btn btn-sm <?php echo $u['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?> ms-1" title="Toggle Active/Suspended">
                                            <?php echo $u['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                                        </a>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
