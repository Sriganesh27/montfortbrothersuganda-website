<?php
// weblangu/api/update_prayer.php

require_once __DIR__ . '/../includes/security.php'; 
require_once 'db_config.php';
require_once 'rate_limiter.php'; 
require_once 'ResponseHelper.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // --- ACTION 1: Global Count Increment (Thumb Click) ---
        if ($action === 'global_pray') {
            if (!check_rate_limit($pdo, 5, 1)) {
                ResponseHelper::error("Too many clicks. Please wait a minute.", 429);
            }

            if (isset($_COOKIE['mbsg_global_prayed_today'])) {
                ResponseHelper::error("Already recorded for today.");
            }

            $pdo->beginTransaction();
            $pdo->query("UPDATE web_prayer_stats SET total_prayers = total_prayers + 1 WHERE id = 1");
            $total = $pdo->query("SELECT total_prayers FROM web_prayer_stats WHERE id = 1")->fetchColumn();
            $pdo->commit();
            
            setcookie('mbsg_global_prayed_today', '1', strtotime('tomorrow'), '/', '', true, true);
            ResponseHelper::success("Count updated", ['total' => (int)$total]);
        }

        // --- ACTION 2: User Form Submission (Registration) ---
        if ($action === 'submit_form') {
            // Validate CSRF
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                ResponseHelper::error("Security session expired. Please refresh the page.", 403);
            }

            // IP Rate Limit (Max 3 registrations per minute)
            if (!check_rate_limit($pdo, 3, 1)) {
                ResponseHelper::error("Too many attempts. Please try again later.", 429);
            }

            $name = htmlspecialchars(strip_tags(trim($_POST['name'] ?? '')), ENT_QUOTES, 'UTF-8');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $phone = substr(preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? ''), 0, 20);

            if (empty($name) || (empty($email) && empty($phone))) {
                ResponseHelper::error("Full Name and either Email or Phone are required.");
            }

            $checkStmt = $pdo->prepare("SELECT id FROM web_prayer_users WHERE (email = ? AND email != '') OR (phone = ? AND phone != '')");
            $checkStmt->execute([$email, $phone]);
            
            if ($checkStmt->fetch()) {
                ResponseHelper::success("You are already registered! Thank you for your continued prayer.");
            }

            $stmt = $pdo->prepare("INSERT INTO web_prayer_users (full_name, email, phone, last_ip, last_prayed, prayer_count) VALUES (?, ?, ?, ?, NOW(), 1)");
            $stmt->execute([$name, $email ?: null, $phone ?: null, $_SERVER['REMOTE_ADDR']]);

            ResponseHelper::success("Registration successful! Thank you for joining us in prayer.");
        }
    }

    // GET Request: Just return current count
    $total = $pdo->query("SELECT total_prayers FROM web_prayer_stats WHERE id = 1")->fetchColumn();
    echo json_encode(['total' => (int)($total ?? 0)]);

} catch (Exception $e) {
    error_log("Prayer API Error: " . $e->getMessage());
    ResponseHelper::error("An internal error occurred. Please try again.");
}