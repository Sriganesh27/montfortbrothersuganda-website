<?php
// web/api/contact_process.php

require_once __DIR__ . '/../includes/security.php';
require_once 'db_config.php';
require_once 'rate_limiter.php'; 
require_once 'ResponseHelper.php';

// Ensure it's an AJAX/Fetch request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ResponseHelper::error("Method Not Allowed", 405);
}

// 1. Honeypot Check (Silently succeed to trick bots)
if (!empty($_POST['website_url'])) {
    ResponseHelper::success("Message sent successfully.");
}

// 2. Rate Limiting Check
if (!check_rate_limit($pdo, 5, 1)) {
    ResponseHelper::error("Too many requests. Please try again later.", 429);
}

// 3. CSRF Validation Check
$provided_token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $provided_token)) {
    ResponseHelper::error("Security token validation failed.", 403);
}

// 4. Strict Sanitization & Capture
$full_name = htmlspecialchars(strip_tags(trim($_POST["name"] ?? '')), ENT_QUOTES, 'UTF-8'); 
$email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
$number = htmlspecialchars(strip_tags(trim($_POST["number"] ?? '')), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(strip_tags(trim($_POST["message"] ?? '')), ENT_QUOTES, 'UTF-8');

// 5. Validation
if (empty($full_name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ResponseHelper::error("Invalid input data provided.", 422);
}

try {
    // 6. Secure Database Transaction
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO web_contact_inquiries (full_name, email, phone_number, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $number, $message]);

    $pdo->commit();

    /* --- SMTP TEMPORARILY DISABLED START ---
    require_once 'mail_helper.php';
    $admin_email = 'uniteallforpeace@gmail.com'; 
    $email_subject = "New Contact Inquiry from $full_name";
    
    $email_body = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <h2 style='color: #2b6cb0;'>New Contact Form Submission</h2>
        <p><strong>Name:</strong> $full_name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone Number:</strong> " . ($number ?: 'Not provided') . "</p>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
        <p><strong>Message:</strong></p>
        <div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>
            " . $message . "
        </div>
    </div>
    ";

    if (!send_custom_mail($admin_email, $email_subject, $email_body, $email, $full_name)) {
        error_log("Contact Form Email Failed. Check SMTP Credentials.");
    }
    --- SMTP TEMPORARILY DISABLED END --- */

    ResponseHelper::success("Thank you for contacting us. We will get back to you shortly.");
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Contact Form DB Error: " . $e->getMessage());
    ResponseHelper::error("An internal server error occurred.", 500);
}
?>