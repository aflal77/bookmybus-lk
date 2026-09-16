<?php

// BookMyBus LK – User Logout (logout.php)
// Secure Session Clearance

require_once 'includes/auth.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

session_start();
$_SESSION['flash_success'] = "You have been logged out successfully. Safe travels!";
header("Location: index.php");
exit;
