<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/update_quick_edit.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
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

    $name = trim(filter_input(INPUT_POST, 'Name', FILTER_SANITIZE_STRING));
    $surname = trim(filter_input(INPUT_POST, 'Surname', FILTER_SANITIZE_STRING));
    $gender = filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_STRING);
    $class = filter_input(INPUT_POST, 'Class', FILTER_SANITIZE_STRING);
    $stream = filter_input(INPUT_POST, 'Stream', FILTER_SANITIZE_STRING);
    $residence = filter_input(INPUT_POST, 'Residence', FILTER_SANITIZE_STRING);
    $entry = filter_input(INPUT_POST, 'EntryStatus', FILTER_SANITIZE_STRING);
    $contact = trim(filter_input(INPUT_POST, 'Contact', FILTER_SANITIZE_STRING));
    $lin = trim(filter_input(INPUT_POST, 'LIN', FILTER_SANITIZE_STRING));

    // 3. Lowercase table names & added branch_id to all WHERE clauses
    $stmt1 = $conn->prepare("UPDATE erp_students SET Name = ?, Surname = ?, Gender = ? WHERE AdmissionNo = ? AND branch_id = ?");
    $stmt1->bind_param("sssii", $name, $surname, $gender, $ad_no, $branch_id);
    $stmt1->execute();
    $stmt1->close();

    $stmt2 = $conn->prepare("UPDATE erp_enrollment SET Class = ?, Stream = ?, Residence = ?, EntryStatus = ? WHERE AdmissionNo = ? AND branch_id = ?");
    $stmt2->bind_param("ssssii", $class, $stream, $residence, $entry, $ad_no, $branch_id);
    $stmt2->execute();
    $stmt2->close();

    if (!empty($contact)) {
        $stmt3 = $conn->prepare("UPDATE erp_parents SET father_contact = ? WHERE AdmissionNo = ? AND branch_id = ?");
        $stmt3->bind_param("sii", $contact, $ad_no, $branch_id);
        $stmt3->execute();
        $stmt3->close();
    }

    if(isset($_POST['LIN'])) {
        $stmt4 = $conn->prepare("UPDATE erp_academichistory SET LIN = ? WHERE AdmissionNo = ? AND branch_id = ?");
        $stmt4->bind_param("sii", $lin, $ad_no, $branch_id);
        $stmt4->execute();
        $stmt4->close();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Record updated successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
