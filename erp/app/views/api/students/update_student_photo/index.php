<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// school_administration1/api/students/update_student_photo.php

// Silence visual errors to prevent JSON corruption, but log them for debugging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$branch_id = $_SESSION['branch_id'] ?? null;

// Securely grab POST variables without triggering PHP 8.1+ deprecation warnings
$id = isset($_POST['AdmissionNo']) ? (int)$_POST['AdmissionNo'] : null;
$temp_file = isset($_POST['temp_photo_filename']) ? trim($_POST['temp_photo_filename']) : null;

if (!$id || !$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Admission No or Branch ID']);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Generate the custom dynamic folder structure
$custom_folder = get_custom_student_folder($conn, $id, $branch_id);

if (!$custom_folder) {
    echo json_encode(['success' => false, 'message' => 'Student not found in this branch']);
    $conn->close();
    exit;
}

// Remove the default "/students/" from the global config path
$UPLOAD_DIR = dirname(rtrim($UPLOAD_DIR_BASE, '/')) . '/'; 
$REAL_UPLOAD_REL = dirname(rtrim($UPLOAD_FOLDER_REL, '/')) . '/';
$studentDir = $UPLOAD_DIR . $custom_folder;

if (!is_dir($studentDir)) {
    mkdir($studentDir, 0755, true);
}

$newPath = null;

// SCENARIO A: Moving the temp file to a permanent file
if (!empty($temp_file)) {
    $temp_file = basename($temp_file); // Security: prevent directory traversal
    $sourcePath = $studentDir . $temp_file; 
    
    if (file_exists($sourcePath)) {
        $ext = strtolower(pathinfo($temp_file, PATHINFO_EXTENSION));
        $finalName = $id . '_' . uniqid() . '.' . $ext;
        $destPath = $studentDir . $finalName;
        
        if (rename($sourcePath, $destPath)) {
            $newPath = $REAL_UPLOAD_REL . $custom_folder . $finalName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to rename temp file. Check folder permissions.']);
            $conn->close();
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Temp file not found on the server.']);
        $conn->close();
        exit;
    }
} 
// SCENARIO B: Direct Upload fallback
else if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['photo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finalName = $id . '_' . uniqid() . '.' . $ext;
    $destPath = $studentDir . $finalName;
    
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $newPath = $REAL_UPLOAD_REL . $custom_folder . $finalName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        $conn->close();
        exit;
    }
}

// Update Database
if ($newPath) {
    $stmt = $conn->prepare("UPDATE erp_students SET PhotoPath = ? WHERE AdmissionNo = ? AND branch_id = ?");
    if ($stmt) {
        $stmt->bind_param("sii", $newPath, $id, $branch_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'newPath' => $newPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No valid photo file was provided.']);
}

$conn->close();
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
