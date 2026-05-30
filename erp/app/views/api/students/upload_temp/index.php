<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// school_administration1/api/students/upload_temp.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();
$branch_id = $_SESSION['branch_id'] ?? null;

$Ad_no = filter_input(INPUT_POST, 'AdmissionNo', FILTER_SANITIZE_NUMBER_INT);
if (!$Ad_no || !$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Admission No and Branch ID required.']);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$custom_folder = get_custom_student_folder($conn, $Ad_no, $branch_id);
$conn->close();

if (!$custom_folder) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

// Step up to base upload dir
$UPLOAD_DIR = dirname(rtrim($UPLOAD_DIR_BASE, '/')) . '/'; 
$REAL_UPLOAD_REL = dirname(rtrim($UPLOAD_FOLDER_REL, '/')) . '/';

$TARGET_DIR = $UPLOAD_DIR . $custom_folder;

if (!is_dir($TARGET_DIR)) {
    mkdir($TARGET_DIR, 0755, true);
}

$ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];
$MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $MAX_FILE_SIZE || !in_array($ext, $ALLOWED_EXTENSIONS)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file or size']);
        exit;
    }

    $tempName = 'temp_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $TARGET_DIR . $tempName)) {
        echo json_encode([
            'success' => true,
            'tempFileName' => $tempName,
            'previewUrl' => $REAL_UPLOAD_REL . $custom_folder . $tempName
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move file']);
    }
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
