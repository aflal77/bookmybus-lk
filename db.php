<?php
/**
 * BookMyBus LK – Database Connection (db.php)
 *
 * Secure connection handler that loads credentials from a git-ignored
 * local configuration file (config.local.php), environment variables,
 * or standard local development defaults.
 */

// Disable default PHP 8.1+ exception throwing to handle connection gracefully
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Default local XAMPP configuration (safe placeholders)
$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'quickseat';
$port     = (int)(getenv('DB_PORT') ?: 3306);

// 2. Load private local configuration if available (ignored by Git)
$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $host     = $localConfig['db_host'] ?? $host;
        $user     = $localConfig['db_user'] ?? $user;
        $password = $localConfig['db_pass'] ?? $password;
        $database = $localConfig['db_name'] ?? $database;
        $port     = (int)($localConfig['db_port'] ?? $port);
    }
}

// 3. Establish MySQL connection
$conn = @mysqli_connect($host, $user, $password, $database, $port);

// 4. Fallback attempt for alternative local database naming
if (!$conn && mysqli_connect_errno() === 1049) {
    $fallbackDb = ($database === 'quickseat') ? 'bookmybus' : 'quickseat';
    $conn = @mysqli_connect($host, $user, $password, $fallbackDb, $port);
}

// 5. Connection health check
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px; max-width:600px; margin:40px auto;'>
            <h3 style='margin-top:0;'>Database Connection Failed</h3>
            <p>" . htmlspecialchars(mysqli_connect_error()) . "</p>
            <p style='margin-bottom:0;'>Please verify database settings in <code>config.local.php</code> (or copy from <code>config.example.php</code>) and ensure MySQL is running.</p>
         </div>");
}

// 6. Set charset to UTF-8
mysqli_set_charset($conn, 'utf8mb4');
?>
