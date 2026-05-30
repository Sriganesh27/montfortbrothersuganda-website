<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// web/includes/security.php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); 
    ini_set('session.cookie_secure', 1);   
    ini_set('session.use_strict_mode', 1);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict' 
    ]);
    
    session_start();
    
    header("X-Frame-Options: SAMEORIGIN"); 
    header("X-Content-Type-Options: nosniff"); 
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains"); 
    
    // ⭐ FIX: Added 'unsafe-inline' and explicitly whitelisted cdnjs so jsPDF and Translations work perfectly.
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://img1.wsimg.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://img1.wsimg.com;");
    
    // ⭐ ADVANCED SEC ADDITIONS: Restrict referrers and browser features
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

// ⭐ SMART SESSION CONTROL: 10 mins for Admins, 2 Hours for Public Users
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$timeout_duration = $is_admin ? 600 : 7200; 

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout_duration) {
        session_unset();
        session_destroy();
        
        // Check if the request is expecting JSON (AJAX fetch request)
        $is_ajax = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            header("Location: ../index.php?status=security_error");
            exit;
        } elseif (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            if ($is_ajax) {
                // Return JSON for JS Fetch calls
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired due to inactivity. Please refresh.']);
                exit;
            } else {
                header("Location: ../index.php?status=security_error");
                exit;
            }
        }
    }
}
$_SESSION['last_activity'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
