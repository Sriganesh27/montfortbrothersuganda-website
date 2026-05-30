<?php
// web/api/donation_process.php

require_once __DIR__ . '/../includes/security.php';
require_once 'db_config.php';
require_once 'rate_limiter.php'; // ⭐ Added Rate Limiter

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function generateReceiptNumber($pdo, $project_id, $purpose) {
    if (!empty($project_id)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_donations WHERE receipt_number LIKE ?");
        $stmt->execute([$project_id . '-%']);
        $count = $stmt->fetchColumn();
        return $project_id . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $prefixes = [
            'Scholarships for student' => 'SSP',
            'Infrastructure Development' => 'IFD',
            'Community Empowerment' => 'CDP'
        ];
        $prefix = $prefixes[$purpose] ?? 'DON';
        $stmt = $pdo->prepare("SELECT receipt_number FROM web_donations WHERE receipt_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();
        $newNumber = $last ? intval(substr($last, 3)) + 1 : 1;
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// ⭐ Rate Limiting (Max 5 donation intents per minute per IP)
if (!check_rate_limit($pdo, 5, 1)) {
    $response['message'] = 'Too many requests. Please try again later.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $response['message'] = 'Invalid security token. Please refresh the page.';
    echo json_encode($response);
    exit;
}

// Sanitize & Cap Lengths
$full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$country_code = preg_replace('/[^0-9+]/', '', $_POST['country_code'] ?? '');
$raw_phone = preg_replace('/[^0-9\s-]/', '', $_POST['contact_number'] ?? '');
$contact = substr($country_code . ' ' . $raw_phone, 0, 30); // Max 30 chars
$location = htmlspecialchars(trim($_POST['location'] ?? ''), ENT_QUOTES, 'UTF-8');
$is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
$purpose = htmlspecialchars(trim($_POST['contribution_purpose'] ?? ''), ENT_QUOTES, 'UTF-8');
$project_id = htmlspecialchars(trim($_POST['project_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$currency = substr(trim($_POST['currency'] ?? ''), 0, 5); // Limit to 5 chars (e.g. USD, UGX)
$status = htmlspecialchars(trim($_POST['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8');
$tx_id = htmlspecialchars(trim($_POST['transaction_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$failure_reason = htmlspecialchars(trim($_POST['failure_reason'] ?? ''), ENT_QUOTES, 'UTF-8');

if ($failure_reason === 'Other' && !empty($_POST['other_desc'])) {
    $failure_reason = 'Other: ' . htmlspecialchars(trim($_POST['other_desc']), ENT_QUOTES, 'UTF-8');
}

// ⭐ Bounds & Logic Validation
if (empty($full_name) || empty($location) || empty($purpose) || empty($currency) || empty($status)) {
    $response['message'] = 'All required fields must be filled.';
    echo json_encode($response);
    exit;
}

if (mb_strlen($full_name) > 150 || mb_strlen($location) > 150) {
    $response['message'] = 'Input fields exceed maximum allowed length.';
    echo json_encode($response);
    exit;
}

if ($amount <= 0 && $status === 'success') {
    $response['message'] = 'Donation amount must be greater than zero.';
    echo json_encode($response);
    exit;
}

try {
    if ($status === 'success' && !empty($tx_id)) {
        $checkStmt = $pdo->prepare("SELECT id FROM web_donations WHERE transaction_id = ? LIMIT 1");
        $checkStmt->execute([$tx_id]);
        if ($checkStmt->fetch()) {
            $response['message'] = 'This transaction ID has already been recorded.';
            echo json_encode($response);
            exit;
        }
    }

    $receipt_number = generateReceiptNumber($pdo, $project_id, $purpose);

    $sql = "INSERT INTO web_donations (receipt_number, full_name, email, contact_number, location, is_anonymous, contribution_purpose, project_id, currency, payment_status, transaction_id, amount, failure_reason) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $receipt_number, $full_name, $email, $contact, $location, 
        $is_anonymous, $purpose, $project_id, $currency, 
        $status, $tx_id, $amount, $failure_reason
    ]);

    $response['success'] = true;
    $response['message'] = 'Donation record saved successfully.';
    $response['receipt_number'] = $receipt_number;

} catch (PDOException $e) {
    error_log("Donation Process Error: " . $e->getMessage());
    $response['message'] = 'A database error occurred.';
}

echo json_encode($response);
?>