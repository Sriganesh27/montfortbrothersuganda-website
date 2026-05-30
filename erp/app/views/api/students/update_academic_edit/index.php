<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/update_academic_edit.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// 1. Secure the branch session
if (session_status() === PHP_SESSION_NONE) session_start();
$branch_id = $_SESSION['branch_id'] ?? null;
if (!$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: No school branch identified.']);
    exit;
}

// 2. Standard DB Connection & Manual Transaction
$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$conn->begin_transaction();

try {
    $ad_no = filter_input(INPUT_POST, 'AdmissionNo', FILTER_SANITIZE_NUMBER_INT);
    if (!$ad_no) throw new Exception("Admission No is missing.");

    // Inputs
    $stream = filter_input(INPUT_POST, 'Stream', FILTER_SANITIZE_STRING);
    $lin = filter_input(INPUT_POST, 'LIN', FILTER_SANITIZE_STRING);
    $combination = filter_input(INPUT_POST, 'Combination', FILTER_SANITIZE_STRING);
    $ple_idx = filter_input(INPUT_POST, 'PLEIndexNumber', FILTER_SANITIZE_STRING);
    $ple_agg = filter_input(INPUT_POST, 'PLEAggregate', FILTER_SANITIZE_NUMBER_INT);
    $uce_idx = filter_input(INPUT_POST, 'UCEIndexNumber', FILTER_SANITIZE_STRING);
    $uce_res = filter_input(INPUT_POST, 'UCEResult', FILTER_SANITIZE_STRING);

    // 3. UPDATE erp_enrollment (Lowercase table, added branch_id)
    if (isset($_POST['Stream'])) {
        $stmt_e = $conn->prepare("UPDATE erp_enrollment SET Stream = ? WHERE AdmissionNo = ? AND branch_id = ?");
        $stmt_e->bind_param("sii", $stream, $ad_no, $branch_id);
        $stmt_e->execute();
        $stmt_e->close();
    }

    // 4. UPDATE erp_academichistory (Lowercase table, added branch_id check)
    $stmt_check = $conn->prepare("SELECT HistoryID FROM erp_academichistory WHERE AdmissionNo = ? AND branch_id = ?");
    $stmt_check->bind_param("ii", $ad_no, $branch_id);
    $stmt_check->execute();
    $exists = $stmt_check->get_result()->num_rows > 0;
    $stmt_check->close();

    if ($exists) {
        $sql = "UPDATE erp_academichistory SET 
                LIN = ?, Combination = ?, 
                PLEIndexNumber = ?, PLEAggregate = ?, 
                UCEIndexNumber = ?, UCEResult = ? 
                WHERE AdmissionNo = ? AND branch_id = ?";
        $stmt_a = $conn->prepare($sql);
        // Bind types: String, String, String, Integer, String, String, Integer, Integer
        $stmt_a->bind_param("sssissii", $lin, $combination, $ple_idx, $ple_agg, $uce_idx, $uce_res, $ad_no, $branch_id);        
        $stmt_a->execute();
        $stmt_a->close();
    } else {
        // Insert if missing (Now strictly requires branch_id)
        $sql = "INSERT INTO erp_academichistory (AdmissionNo, branch_id, LIN, Combination, PLEIndexNumber, PLEAggregate, UCEIndexNumber, UCEResult) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_a = $conn->prepare($sql);
        $stmt_a->bind_param("iisssiss", $ad_no, $branch_id, $lin, $combination, $ple_idx, $ple_agg, $uce_idx, $uce_res);        
        $stmt_a->execute();
        $stmt_a->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Academic details updated.']);

} catch (Exception $e) {
    if ($conn) $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
