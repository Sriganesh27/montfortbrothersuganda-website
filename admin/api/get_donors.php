<?php
// admin/api/get_donors.php

require_once '../../includes/security.php';

header('Content-Type: application/json; charset=UTF-8');

// Only authenticated administrators may read donor-distribution data.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed.'
    ]);
    exit;
}

require_once '../../api/db_config.php';

$projectId = trim((string) ($_GET['project_id'] ?? ''));

// Keep all project-specific distribution rules in one controlled map.
$projectConfig = [
    'SSP001' => [
        'requires_students' => true,
        'requires_benefit_year' => true,
        'requires_terms' => true,
        'categories' => ['Scholarships', 'Sustainable Reserve']
    ],
    'CEP001' => [
        'requires_students' => true,
        'requires_benefit_year' => true,
        'requires_terms' => true,
        'categories' => ['Eggs Amount', 'Sustainable Reserve']
    ],
    'IDP001' => [
        'requires_students' => false,
        'requires_benefit_year' => false,
        'requires_terms' => false,
        'categories' => ['Materials & Labor', 'Solar & Water']
    ]
];

if ($projectId === '' || !isset($projectConfig[$projectId])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'A valid project ID is required.'
    ]);
    exit;
}

$config = $projectConfig[$projectId];

try {
    $donorStmt = $pdo->prepare("
        SELECT
            id,
            receipt_number,
            full_name,
            currency,
            amount,
            amount_received,
            students_benefited,
            benefit_year,
            terms_benefited
        FROM web_donations
        WHERE project_id = ?
          AND payment_status = 'success'
          AND amount_received IS NOT NULL
          AND amount_received > 0
        ORDER BY created_at DESC, id DESC
    ");
    $donorStmt->execute([$projectId]);
    $donors = $donorStmt->fetchAll(PDO::FETCH_ASSOC);

    // Load distributions only for the selected project's successful donations.
    $distributionStmt = $pdo->prepare("
        SELECT
            d.donation_id,
            d.category_name,
            d.amount_allocated
        FROM web_donor_distributions d
        INNER JOIN web_donations dn ON dn.id = d.donation_id
        WHERE dn.project_id = ?
          AND dn.payment_status = 'success'
          AND dn.amount_received IS NOT NULL
          AND dn.amount_received > 0
    ");
    $distributionStmt->execute([$projectId]);
    $distributions = $distributionStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'requires_students' => $config['requires_students'],
        'requires_benefit_year' => $config['requires_benefit_year'],
        'requires_terms' => $config['requires_terms'],
        'categories' => $config['categories'],
        'donors' => $donors,
        'distributions' => $distributions
    ]);
} catch (PDOException $e) {
    error_log('Get donors API error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to load donor distribution data.'
    ]);
}