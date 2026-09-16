<?php
require_once __DIR__ . '/auth.php';

$base_path   = $base_path ?? '';
$page_title  = isset($page_title) ? $page_title . ' — BookMyBus LK' : 'BookMyBus LK — Sri Lanka\'s Trusted Bus Booking Platform';
$active_page = $active_page ?? '';
$current_user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <meta name="description" content="BookMyBus LK — Search bus routes, choose your seat, and book intercity travel across Sri Lanka instantly. Real-time seat availability, QR e-tickets, and secure payment.">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <!-- BookMyBus LK Design System -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>style.css">
</head>
<body>

<!-- ── Top Info Strip ─────────────────────────────────────── -->
<div class="top-strip no-print">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="strip-badge">
                <i class="bi bi-bus-front-fill"></i> Sri Lanka Island-Wide Bus Reservations
            </span>
            <span class="d-none d-md-inline">Real-time seat availability &bull; Instant QR e-tickets</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-telephone-fill me-1" style="color:var(--qs-peach)"></i> +94 11 234 5678</span>
            <span class="d-none d-sm-inline" style="opacity:.3">|</span>
            <a href="<?php echo $base_path; ?>verify_ticket.php">
                <i class="bi bi-qr-code-scan me-1"></i> Verify Ticket
            </a>
        </div>
    </div>
</div>

<!-- ── Main Navigation ────────────────────────────────────── -->
<nav class="qs-navbar no-print navbar navbar-expand-lg">
    <div class="container">
        <div class="navbar-inner w-100 d-flex align-items-center justify-content-between gap-3">

            <!-- Brand -->
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6C4 4.9 4.9 4 6 4h12c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6zm2 0v8h12V6H6zM3 18h18v2H3v-2zm3-8h2v4H6v-4zm4 0h2v4h-2v-4zm4 0h2v4h-2v-4z"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-name">BookMy<span>Bus</span><sup class="brand-lk-chip">LK</sup></div>
                    <div class="brand-tagline">Sri Lanka's Trusted Bus Booking Platform</div>
                </div>
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#bmbNav" aria-controls="bmbNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-5"></i>
            </button>

            <!-- Collapsible nav -->
            <div class="collapse navbar-collapse" id="bmbNav">
                <ul class="navbar-nav mx-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'home' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'routes' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>routes.php">Routes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'schedules' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>schedules.php">Schedules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'about' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_page === 'contact' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>contact.php">Contact</a>
                    </li>
                </ul>

                <!-- Right: Auth & CTA -->
                <ul class="navbar-nav align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <?php if ($current_user): ?>

                        <?php if ($current_user['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="btn-qs-sm btn-qs-navy" href="<?php echo $base_path; ?>admin/index.php">
                                    <i class="bi bi-speedometer2"></i> Admin Portal
                                </a>
                            </li>
                        <?php elseif ($current_user['role'] === 'staff'): ?>
                            <li class="nav-item">
                                <a class="btn-qs-sm btn-qs-navy" href="<?php echo $base_path; ?>staff/index.php">
                                    <i class="bi bi-ticket-perforated"></i> Staff Portal
                                </a>
                            </li>
                        <?php elseif ($current_user['role'] === 'driver'): ?>
                            <li class="nav-item">
                                <a class="btn-qs-sm btn-qs-navy" href="<?php echo $base_path; ?>driver/index.php">
                                    <i class="bi bi-steering-wheel"></i> Driver Portal
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="btn-qs-sm btn-qs-secondary" href="<?php echo $base_path; ?>my_bookings.php">
                                    <i class="bi bi-ticket-detailed"></i> My Bookings
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:var(--qs-text)">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($current_user['name'], 0, 1)); ?>
                                </div>
                                <span class="d-none d-sm-inline fw-600" style="font-size:.88rem;font-weight:600;color:var(--qs-text)"><?php echo e($current_user['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="px-3 py-2">
                                    <div style="font-weight:700;font-size:.9rem;color:var(--qs-navy)"><?php echo e($current_user['name']); ?></div>
                                    <div style="font-size:.78rem;color:var(--qs-text-muted)"><?php echo e($current_user['email']); ?></div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($current_user['role'] === 'customer'): ?>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>my_bookings.php"><i class="bi bi-ticket-detailed me-2" style="color:var(--qs-orange)"></i>My Bookings</a></li>
                                <?php elseif ($current_user['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/index.php"><i class="bi bi-speedometer2 me-2" style="color:var(--qs-orange)"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/buses.php"><i class="bi bi-bus-front me-2" style="color:var(--qs-orange)"></i>Fleet Management</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/reports.php"><i class="bi bi-graph-up me-2" style="color:var(--qs-green)"></i>Reports</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>profile.php"><i class="bi bi-person-gear me-2" style="color:var(--qs-text-muted)"></i>Account Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>login.php" style="font-weight:500;color:var(--qs-text-secondary)">Sign In</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn-qs-primary btn-qs-sm" href="<?php echo $base_path; ?>index.php#search-section">
                                <i class="bi bi-search"></i> Find a Bus
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- ── Flash Messages ──────────────────────────────────────── -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="container mt-3 no-print">
        <div class="alert alert-success alert-dismissible fade show qs-flash" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="container mt-3 no-print">
        <div class="alert alert-danger alert-dismissible fade show qs-flash" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
