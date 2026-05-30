<?php
// web/api/db_config.php

$env_path = __DIR__ . '/../.env'; 
$env = [];

if (file_exists($env_path)) {
    $env = @parse_ini_file($env_path);
}

if ($env && isset($env['DB_HOST'])) {
    define('DB_HOST', $env['DB_HOST']);
    define('DB_NAME', $env['DB_NAME']);
    define('DB_USER', $env['DB_USER']);
    define('DB_PASS', $env['DB_PASS']);
} else {
    error_log("CRITICAL: .env file is missing or database credentials are not set.");
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['status' => 'error', 'message' => 'System configuration error.']));
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepared statements (Crucial for security)
        PDO::ATTR_STRINGIFY_FETCHES  => false                   // Keep int as int, float as float
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Database Connection failed: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}
?>