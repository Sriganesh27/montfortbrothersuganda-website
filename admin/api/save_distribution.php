<?php
// admin/api/save_distribution.php
require_once '../../api/db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['donation_id']) || !isset($data['allocations'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$donation_id = $data['donation_id'];
$allocations = $data['allocations'];
$students_benefited = isset($data['students_benefited']) ? (int)$data['students_benefited'] : 0;
// NEW: Catch the terms_benefited variable
$terms_benefited = isset($data['terms_benefited']) ? (int)$data['terms_benefited'] : 0;

try {
    $pdo->beginTransaction();

    // 1. Fetch actual amount_received for validation
    $stmt = $pdo->prepare("SELECT amount_received FROM web_donations WHERE id = ?");
    $stmt->execute([$donation_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_attempted = 0;
    foreach ($allocations as $alloc) {
        $total_attempted += (float)$alloc['amount'];
    }

    if (round($total_attempted, 2) > round((float)$donor['amount_received'], 2)) {
        throw new Exception("Financial error: Allocated amount exceeds the total received UGX.");
    }

    // 2. UPDATE THIS QUERY: Save both students and terms benefited
    $updateDonationStmt = $pdo->prepare("UPDATE web_donations SET students_benefited = ?, terms_benefited = ? WHERE id = ?");
    $updateDonationStmt->execute([$students_benefited, $terms_benefited, $donation_id]);

    // 3. Insert or Edit monetary distributions
    $updateDistStmt = $pdo->prepare("
        INSERT INTO web_donor_distributions (donation_id, category_name, amount_allocated) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE amount_allocated = VALUES(amount_allocated)
    ");

    foreach ($allocations as $alloc) {
        $updateDistStmt->execute([$donation_id, $alloc['category'], (float)$alloc['amount']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>