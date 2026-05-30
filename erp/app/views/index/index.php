<?php
// erp/app/views/index/index.php

// 1. Start session BEFORE any HTML output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Safely load the configuration for BASE_URL
require_once __DIR__ . '/../../../config/config.php';

// 3. If user is already logged in, redirect to their specific dashboard
if (isset($_SESSION['role'])) {
    $redirects = [
        'Super User' => '/super_admin',
        'Branch Admin' => '/admin',
        'Faculty' => '/faculty_dashboard',
        'Parents' => '/parents_dashboard'
    ];
    
    $role = $_SESSION['role'];
    if (array_key_exists($role, $redirects)) {
        // Redirect to the MVC route
        header("Location: /MBU_Website/Website/web/web/erp" . $redirects[$role]);
        exit();
    }
}

// 4. Default action: Redirect to the clean Login route
header("Location: /MBU_Website/Website/web/web/erp/login");
exit();
?>