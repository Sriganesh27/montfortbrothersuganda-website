<?php
// erp/config/config.php

// --- 1. UNIVERSAL DATABASE CREDENTIALS ---
// Since both local and live use the same database, we define these once for everyone.
define('DB_HOST', '68.178.237.26');
define('DB_NAME', 'montfortug');
define('DB_USER', 'montfortu');
define('DB_PASS', 'montfort@123');


// --- 2. DYNAMIC URL DETECTION (Deployment Ready) ---
// Automatically detect if the site is running on your laptop (XAMPP) or the Live Server
$is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

if ($is_localhost) {
    // 💻 LOCAL XAMPP PATH
    define('BASE_URL', 'http://localhost/MBU_Website/Website/web/web/erp/public'); 
} else {
    // 🌍 LIVE PRODUCTION SERVER PATH
    // Automatically calculates your live domain name (e.g., https://www.yourdomain.com)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $domain); 
}


// --- 3. DATABASE CLASS (Modern MVC) ---
class Database {
    public function connect() {
        try {
            return new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}


// --- 4. HELPER FUNCTION (For Legacy/Global scripts) ---
function get_db_connection($host = DB_HOST, $user = DB_USER, $pass = DB_PASS, $db = DB_NAME) {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        return false;
    }
    return $conn;
}
?>