<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session cookie on the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the server-side session
session_destroy();

// 4. Force immediate redirection to login.php
header("Location: login.php");
exit;
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
