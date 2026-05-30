<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/deletestudent.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();
$branch_id = $_SESSION['branch_id'] ?? null;

$Ad_no = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$Ad_no || !$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or branch session missing.']);
    exit;
}

global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $UPLOAD_DIR_BASE;

function rmdir_recursive($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!rmdir_recursive($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

// Transactional connection workaround
$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    // 1. Get the custom folder path BEFORE deleting the student
    $custom_folder = get_custom_student_folder($conn, $Ad_no, $branch_id);
    
    if (!$custom_folder) {
        throw new Exception("Student not found in this branch.");
    }

    $conn->begin_transaction();

    // 2. Delete Dependents
    $tables = ['erp_enrollment', 'erp_parents', 'erp_academichistory', 'erp_enrollmenthistory', 'erp_student_accounts'];
    foreach ($tables as $tbl) {
        $stmt = $conn->prepare("DELETE FROM $tbl WHERE AdmissionNo = ? AND branch_id = ?");
        if($stmt) {
            $stmt->bind_param("ii", $Ad_no, $branch_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 3. Delete Student
    $stmt_del = $conn->prepare("DELETE FROM erp_students WHERE AdmissionNo = ? AND branch_id = ?");
    $stmt_del->bind_param("ii", $Ad_no, $branch_id);
    if (!$stmt_del->execute()) throw new Exception($stmt_del->error);
    $stmt_del->close();

    $conn->commit();

    // 4. File Cleanup
    $UPLOAD_DIR = dirname(rtrim($UPLOAD_DIR_BASE, '/')) . '/'; 
    $student_dir_absolute = $UPLOAD_DIR . $custom_folder;
    
    if (is_dir($student_dir_absolute)) {
        rmdir_recursive($student_dir_absolute);
    }

    echo json_encode(['success' => true, 'message' => "Student deleted successfully."]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
