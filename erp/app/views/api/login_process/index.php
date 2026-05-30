<?php
// erp/app/views/api/login_process/index.php

// 1. Force JSON output even if errors occur
header('Content-Type: application/json');

// 2. Custom Error Handler: Convert PHP Errors to JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['status' => 'error', 'message' => "PHP Error: $errstr in $errfile:$errline"]);
    exit;
});

// 3. Load config
require_once __DIR__ . '/../../../config/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 4. Use Constants from config.php directly
    $conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Check your config.php.']);
        exit;
    }

    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $branch_id = $_POST['branch_id'] ?? '';

        // ... [Insert your query logic here] ...
        // Ensure ALL responses are echoed as json_encode()
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
    } finally {
        if (isset($conn)) { $conn->close(); }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
exit; // Stop everything
?>