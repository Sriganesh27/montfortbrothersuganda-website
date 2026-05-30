<?php
// web/api/auth_process.php

require_once __DIR__ . '/../includes/security.php';
require_once 'db_config.php';
require_once 'mail_helper.php';
require_once 'rate_limiter.php'; // ⭐ Added Rate Limiter

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function respond($success, $message = '', $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function is_strong_password($password) {
    // Min 8 chars, 1 letter, 1 number, Max 128 chars (prevent bcrypt DoS)
    return strlen($password) >= 8 && strlen($password) <= 128 && preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
}

// ⭐ Global Auth IP Rate Limit (Max 15 auth actions per minute per IP)
if (!check_rate_limit($pdo, 15, 1)) {
    respond(false, 'Too many requests from your network. Please try again later.');
}

$action = $_GET['action'] ?? '';

// ==================== SEND OTP ====================
if ($action === 'send_otp') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email || mb_strlen($email) > 150) respond(false, 'A valid email address is required (max 150 chars).');

    // Rate Limiting by Email (Max 3 requests per minute)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_otp_verifications WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() >= 3) respond(false, 'Too many requests for this email. Please wait a moment.');

    $otp = random_int(100000, 999999);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // ⭐ Strict Length Constraints
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    if (mb_strlen($first_name) > 100 || mb_strlen($last_name) > 100) respond(false, 'Name exceeds maximum length.');

    $_SESSION['temp_reg'] = [
        'email'      => $email,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'phone'      => substr(preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? ''), 0, 20),
        'gender'     => in_array($_POST['gender'] ?? '', ['Male', 'Female']) ? $_POST['gender'] : 'Other',
        'dob'        => preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'] ?? '') ? $_POST['dob'] : null
    ];
    
    $_SESSION['otp_attempts'] = 0; 
    $pdo->prepare("UPDATE web_otp_verifications SET is_verified = 1 WHERE email = ? AND is_verified = 0")->execute([$email]);

    $stmt = $pdo->prepare("INSERT INTO web_otp_verifications (email, otp_hash, expiry) VALUES (?, ?, ?)");
    if ($stmt->execute([$email, $otp_hash, $expiry])) {
        // ⭐ FIX: Send actual email instead of echoing the OTP
        $subject = "Your Registration OTP Code";
        $message = "Your verification code is: <strong style='font-size:24px; letter-spacing: 2px;'>$otp</strong><br><br>This code will expire in 10 minutes.";
        
        if (send_custom_mail($email, $subject, $message)) {
            respond(true, "A verification code has been sent to your email.");
        } else {
            respond(false, "Failed to send the verification email. Please check the system mail configuration.");
        }
    } else {
        respond(false, 'A system error occurred. Please try again.');
    }
}

// ==================== VERIFY OTP ====================
if ($action === 'verify_otp') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');

    $email = $_SESSION['temp_reg']['email'] ?? $_SESSION['reset_email'] ?? '';
    $otp_input = trim($_POST['otp_code'] ?? '');

    if (!$email) respond(false, 'Session expired. Please restart the process.');

    $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
    if ($_SESSION['otp_attempts'] > 5) {
        unset($_SESSION['temp_reg'], $_SESSION['reset_email'], $_SESSION['otp_attempts']);
        respond(false, 'Too many failed attempts. Session locked. Request a new code.');
    }

    $stmt = $pdo->prepare("SELECT id, otp_hash FROM web_otp_verifications WHERE email = ? AND expiry > NOW() AND is_verified = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$email]);
    $record = $stmt->fetch(); 

    if ($record && password_verify($otp_input, $record['otp_hash'])) {
        $pdo->prepare("UPDATE web_otp_verifications SET is_verified = 1 WHERE id = ?")->execute([$record['id']]);
        $_SESSION['otp_verified'] = true;
        $_SESSION['otp_verified_for'] = $email; 
        unset($_SESSION['otp_attempts']); 
        respond(true, 'OTP verified successfully.');
    } else {
        $attempts_left = 5 - $_SESSION['otp_attempts'];
        respond(false, "Invalid or expired OTP. ($attempts_left attempts remaining)");
    }
}

// ==================== COMPLETE SIGNUP ====================
if ($action === 'save_user') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || $_SESSION['otp_verified_for'] !== ($_SESSION['temp_reg']['email'] ?? '')) {
        respond(false, 'Unauthorized. Please verify your OTP first.');
    }

    $data = $_SESSION['temp_reg'];
    $password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) respond(false, 'Passwords do not match.');
    if (!is_strong_password($password)) respond(false, 'Password must be 8-128 chars and include a number and letter.');

    $pdo->beginTransaction();
    try {
        $check = $pdo->prepare("SELECT id FROM web_users WHERE email = ? FOR UPDATE");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $pdo->rollBack();
            respond(false, 'This email is already registered.');
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO web_users (username, password, first_name, last_name, email, phone, gender, dob, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'User', 1)");
        $stmt->execute([$data['email'], $password_hash, $data['first_name'], $data['last_name'], $data['email'], $data['phone'], $data['gender'], $data['dob']]);

        $pdo->prepare("DELETE FROM web_otp_verifications WHERE email = ?")->execute([$data['email']]);
        $pdo->commit();
        
        unset($_SESSION['temp_reg'], $_SESSION['otp_verified'], $_SESSION['otp_verified_for']);
        respond(true, 'Registration successful. You may now log in.');
    } catch (Exception $e) {
        $pdo->rollBack();
        respond(false, 'Registration failed due to a system error.');
    }
}

// ==================== FORGOT PASSWORD REQUEST ====================
if ($action === 'forgot_password_request') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');

    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email || mb_strlen($email) > 150) respond(false, 'A valid email address is required.');

    $rateStmt = $pdo->prepare("SELECT COUNT(*) FROM web_otp_verifications WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    $rateStmt->execute([$email]);
    if ($rateStmt->fetchColumn() >= 3) respond(false, 'Too many requests. Try again later.');

    $stmt = $pdo->prepare("SELECT id FROM web_users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        respond(true, 'If your email is registered, a reset code has been sent.');
    }

    $otp = random_int(100000, 999999);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
    $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $pdo->prepare("UPDATE web_otp_verifications SET is_verified = 1 WHERE email = ? AND is_verified = 0")->execute([$email]);
    $stmt = $pdo->prepare("INSERT INTO web_otp_verifications (email, otp_hash, expiry) VALUES (?, ?, ?)");
    if ($stmt->execute([$email, $otp_hash, $expiry])) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_attempts'] = 0;
        
        // ⭐ FIX: Send actual reset email
        $subject = "Password Reset Request";
        $message = "Your password recovery code is: <strong style='font-size:24px; letter-spacing: 2px;'>$otp</strong><br><br>This code will expire in 15 minutes. If you did not request this, please ignore this email.";
        
        if (send_custom_mail($email, $subject, $message)) {
            respond(true, "If your email is registered, a recovery code has been sent.");
        } else {
            respond(false, "Failed to send the recovery email. Please try again later.");
        }
    } else {
        respond(false, 'A system error occurred.');
    }
}

// ==================== RESET PASSWORD ====================
if ($action === 'reset_password') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || $_SESSION['otp_verified_for'] !== ($_SESSION['reset_email'] ?? '')) {
        respond(false, 'Unauthorized. Verify OTP first.');
    }

    $email = $_SESSION['reset_email'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($new_pass !== $confirm_pass) respond(false, 'Passwords do not match.');
    if (!is_strong_password($new_pass)) respond(false, 'Password must be 8-128 chars and include a number and a letter.');

    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE web_users SET password = ?, failed_attempts = 0, locked_until = NULL WHERE email = ?");

    if ($stmt->execute([$hash, $email])) {
        $pdo->prepare("DELETE FROM web_otp_verifications WHERE email = ?")->execute([$email]);
        unset($_SESSION['reset_email'], $_SESSION['otp_verified'], $_SESSION['otp_verified_for']);
        respond(true, 'Password updated successfully. You may now log in.');
    } else {
        respond(false, 'Failed to update password.');
    }
}

// ==================== LOGIN ====================
if ($action === 'login') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) respond(false, 'Invalid security token.');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password) || mb_strlen($username) > 150 || mb_strlen($password) > 128) {
        respond(false, 'Invalid login parameters.');
    }

    $stmt = $pdo->prepare("SELECT id, password, role, first_name, is_active, failed_attempts, locked_until FROM web_users WHERE (username = ? OR email = ?) LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes_left = ceil((strtotime($user['locked_until']) - time()) / 60);
            respond(false, "Account locked due to multiple failed attempts. Try again in $minutes_left minutes.");
        }

        if (password_verify($password, $user['password'])) {
            if ($user['is_active'] == 0) respond(false, 'Your account is deactivated. Please contact support.');

            $pdo->prepare("UPDATE web_users SET failed_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$user['id']]);

            session_regenerate_id(true); 
            $_SESSION['web_user_id'] = $user['id'];
            $_SESSION['web_role'] = $user['role'];
            $_SESSION['web_name'] = $user['first_name'];

            if ($user['role'] === 'Admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
            }
            
            respond(true, 'Login successful.', ['role' => $user['role']]);
        } else {
            $attempts = $user['failed_attempts'] + 1;
            $locked_until = null;
            $msg = 'Invalid credentials.';

            if ($attempts >= 5) {
                $locked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $msg = 'Too many failed attempts. Account locked for 15 minutes.';
            }

            $pdo->prepare("UPDATE web_users SET failed_attempts = ?, locked_until = ? WHERE id = ?")->execute([$attempts, $locked_until, $user['id']]);
            respond(false, $msg);
        }
    } else {
        password_verify($password, '$2y$10$abcdefghijklmnopqrstuv');
        respond(false, 'Invalid credentials.');
    }
}

// ==================== LOGOUT ====================
if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_regenerate_id(true); // Prevent session fixation on logout
    respond(true, 'Logged out successfully.');
}

respond(false, 'Invalid action request.');
?>