<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/includes/db.php

// 1. Centralized Session Handling (Fixes warning logs about active sessions)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// 2. Import your existing config configurations safely
require_once __DIR__ . '/../config/config.php';

global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

// 3. Establish a single secure database connection lifecycle
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    // Reuses the exact credentials working in your config file
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("<h3>Global Connection Error:</h3><p>" . $e->getMessage() . "</p><p>Verify that MySQL is running in your XAMPP Control Panel and check the settings inside erp/config/config.php.</p>");
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
