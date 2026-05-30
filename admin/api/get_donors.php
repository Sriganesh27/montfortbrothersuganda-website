<?php
// admin/api/get_donors.php
require_once '../../api/db_config.php';
header('Content-Type: application/json');

$project_id = $_GET['project_id'] ?? '';

if (!$project_id) {
    echo json_encode(['error' => 'Project ID required']);
    exit;
}

// Define monetary categories and if the project tracks specific metrics
$categories = [];
$requires_students = false;
$requires_terms = false; // <-- ADD THIS

if ($project_id === 'SSP001') {
    $requires_students = true;
    $requires_terms = true; // <-- ADD THIS
    $categories = ['Scholarships', 'Sustainable Reserve'];
} elseif ($project_id === 'CEP001') {
    $requires_students = true;
    $requires_terms = true; // <-- ADD THIS
    $categories = ['Eggs Amount', 'Sustainable Reserve'];
} elseif ($project_id === 'IDP001') {
    $requires_students = false;
    $requires_terms = false; // <-- ADD THIS
    $categories = ['Materials & Labor', 'Solar & Water'];
}

try {
    // UPDATE THIS QUERY to include terms_benefited
    $stmt = $pdo->prepare("
        SELECT id, receipt_number, full_name, currency, amount, amount_received, students_benefited, terms_benefited 
        FROM web_donations 
        WHERE project_id = ? AND payment_status = 'success' AND amount_received > 0
    ");
    $stmt->execute([$project_id]);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch existing distributions
    $distStmt = $pdo->prepare("SELECT donation_id, category_name, amount_allocated FROM web_donor_distributions");
    $distStmt->execute();
    $distributions = $distStmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

    // Pass the new variable to the frontend
    echo json_encode([
        'requires_students' => $requires_students,
        'requires_terms' => $requires_terms, // <-- ADD THIS
        'categories' => $categories,
        'donors' => $donors,
        'distributions' => $distributions
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>