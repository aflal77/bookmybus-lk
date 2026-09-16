<?php

// BookMyBus LK – Authentication & Session Helper (includes/auth.php)
// Role-Based Access Control (RBAC) & Security Utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

/**
 * Check if a user is currently logged in.
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user information array.
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer',
        'nic' => $_SESSION['user_nic'] ?? ''
    ];
}

/**
 * Check if the logged-in user matches a specific role or set of roles.
 */
function has_role($roles): bool {
    if (!is_logged_in()) {
        return false;
    }
    $current_role = $_SESSION['user_role'] ?? 'customer';
    if (is_array($roles)) {
        return in_array($current_role, $roles);
    }
    return $current_role === $roles;
}

/**
 * Enforce authentication. Redirects to login page if user is not authenticated.
 */
function require_login(string $redirect_url = 'login.php'): void {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        // Save intended URL for post-login redirect
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header("Location: " . $redirect_url);
        exit;
    }
}

/**
 * Enforce role-based access. Redirects with error if role condition fails.
 */
function require_role($roles, string $redirect_url = '../index.php'): void {
    require_login();
    if (!has_role($roles)) {
        $_SESSION['flash_error'] = "Access denied: You do not have permission to view this section.";
        header("Location: " . $redirect_url);
        exit;
    }
}

/**
 * Generate CSRF token and store in session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden input field with the CSRF token.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate incoming POST CSRF token.
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Safe output escaping wrapper.
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency in Sri Lankan Rupees (Rs. 2,500.00).
 */
function format_lkr($amount): string {
    return 'Rs. ' . number_format((float)$amount, 2);
}
