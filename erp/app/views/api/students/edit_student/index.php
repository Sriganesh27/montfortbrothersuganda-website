<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// school_administration1/api/students/edit_student.php

// Silence visual errors to prevent JSON corruption
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php'; 
header('Content-Type: application/json');

// --- MULTI-BRANCH SECURITY CHECK ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$branch_id = $_SESSION['branch_id'] ?? null;
if (!$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: No school branch identified.']);
    exit();
}

global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $UPLOAD_FOLDER_REL, $UPLOAD_DIR_BASE;

// Step up to base upload dir (removes default /students/)
$UPLOAD_DIR = dirname(rtrim($UPLOAD_DIR_BASE, '/')) . '/'; 
$REAL_UPLOAD_REL = dirname(rtrim($UPLOAD_FOLDER_REL, '/')) . '/';

$ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];
$MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
$ALLOWED_RELATIONS = ['Brother', 'Sister', 'Uncle', 'Aunt', 'Grandparent', 'Other'];

// --- HELPER FUNCTIONS ---
function allowed_file($filename, $allowed_extensions) {
    if (empty($filename)) return false;
    $parts = explode('.', $filename);
    $extension = strtolower(end($parts));
    return in_array($extension, $allowed_extensions);
}

// Fixed to require $branch_id to ensure data isolation & removed deprecated filters
function build_dynamic_update($table, $id_val, $branch_id, $field_map, $conn) {
    $updates = [];
    $types = "";
    $params = [];

    foreach ($field_map as $post_key => $db_col) {
        if (array_key_exists($post_key, $_POST)) {
            // FIX: Removed deprecated FILTER_SANITIZE_STRING
            $val = trim($_POST[$post_key]); 
            if ($post_key === 'AcademicYear') $val = format_academic_year($val);
            if ($val === '') $val = null;

            // Simple validation
            $mandatory_fields = ['Name', 'Surname', 'father_name', 'mother_name'];
            if (in_array($post_key, $mandatory_fields) && empty($val)) {
                throw new Exception("Field '$post_key' cannot be empty.");
            }

            $updates[] = "$db_col = ?";
            $types .= "s"; 
            $params[] = $val;
        }
    }

    if (empty($updates)) return true;

    // Execute update specifically for this branch and admission number
    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE AdmissionNo = ? AND branch_id = ?";
    $types .= "ii";
    $params[] = $id_val;
    $params[] = $branch_id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed for $table: " . $conn->error);
    
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception("Execute failed for $table: " . $stmt->error);
    $stmt->close();
    return true;
}

// --- MAIN EXECUTION ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

// 1. Get Basic Info
$Ad_no = isset($_POST['AdmissionNo']) ? (int)$_POST['AdmissionNo'] : 0;
$action = $_POST['action'] ?? 'update';

if (!$Ad_no) {
    echo json_encode(['success' => false, 'message' => 'Missing required Admission No.']);
    exit();
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// MATCH THE CUSTOM FOLDER STRUCTURE CREATED IN ADMISSION.PHP
$custom_folder = get_custom_student_folder($conn, $Ad_no, $branch_id);
if (!$custom_folder) {
    echo json_encode(['success' => false, 'message' => 'Student record missing or unauthorized.']);
    exit();
}

// =========================================================
// ACTION: DELETE TEMP FILE (Cleanup on Cancel)
// =========================================================
if ($action === 'delete_temp') {
    $fileName = isset($_POST['fileName']) ? basename(trim($_POST['fileName'])) : '';
    
    if (!$fileName) {
        echo json_encode(['success' => false, 'message' => 'No filename provided']);
        exit;
    }

    if (strpos($fileName, 'temp_') !== 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid file selection']);
        exit;
    }

    $targetPath = $UPLOAD_DIR . $custom_folder . $fileName;

    if (file_exists($targetPath)) {
        if (unlink($targetPath)) {
            echo json_encode(['success' => true, 'message' => 'Temp file deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not delete file']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'File already gone']);
    }
    exit;
}

// =========================================================
// ACTION: UPDATE PROFILE (Save Changes)
// =========================================================

// 2. Manually start the transaction
$conn->begin_transaction();

try {
    // --- PHOTO HANDLING ---
    $new_photo_path = null;
    $photo_updated = false;

    // A. Commit Temp File (Rename temp_xxx.jpg -> xxx.jpg)
    $temp_filename = isset($_POST['temp_photo_filename']) ? trim($_POST['temp_photo_filename']) : '';
    
    // Create folder if missing
    if (!is_dir($UPLOAD_DIR . $custom_folder)) mkdir($UPLOAD_DIR . $custom_folder, 0755, true);

    if (!empty($temp_filename)) {
        $temp_abs_path = $UPLOAD_DIR . $custom_folder . basename($temp_filename);
        
        if (file_exists($temp_abs_path)) {
            $ext = pathinfo($temp_filename, PATHINFO_EXTENSION);
            $perm_filename = $Ad_no . '_' . uniqid() . '.' . $ext;
            $perm_abs_path = $UPLOAD_DIR . $custom_folder . $perm_filename;
            
            if (rename($temp_abs_path, $perm_abs_path)) {
                $new_photo_path = $REAL_UPLOAD_REL . $custom_folder . $perm_filename;
                $photo_updated = true;
            }
        }
    }
    // B. Direct Upload (Fallback)
    else if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        $filename = basename($file['name']);
        
        if ($file['size'] <= $MAX_FILE_SIZE && allowed_file($filename, $ALLOWED_EXTENSIONS)) {
            
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $safe_filename = $Ad_no . '_' . uniqid() . '.' . $extension; 
            $file_path = $UPLOAD_DIR . $custom_folder . $safe_filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $new_photo_path = $REAL_UPLOAD_REL . $custom_folder . $safe_filename;
                $photo_updated = true;
            }
        }
    }

    if ($photo_updated) {
        $stmt_p = $conn->prepare("UPDATE erp_students SET PhotoPath = ? WHERE AdmissionNo = ? AND branch_id = ?");
        $stmt_p->bind_param("sii", $new_photo_path, $Ad_no, $branch_id);
        $stmt_p->execute();
        $stmt_p->close();
    }

    // --- DATA UPDATES (PostalCode ADDED HERE!) ---
    $student_map = ['Name'=>'Name','Surname'=>'Surname','DateOfBirth'=>'DateOfBirth','Gender'=>'Gender','HouseNo'=>'HouseNo','Street'=>'Street','Village'=>'Village','Town'=>'Town','District'=>'District','State'=>'State','Country'=>'Country', 'PostalCode'=>'PostalCode'];
    $parents_map = ['father_name'=>'father_name','father_contact'=>'father_contact','father_email'=>'father_email','father_age'=>'father_age','father_occupation'=>'father_occupation','father_education'=>'father_education','mother_name'=>'mother_name','mother_contact'=>'mother_contact','mother_email'=>'mother_email','mother_age'=>'mother_age','mother_occupation'=>'mother_occupation','mother_education'=>'mother_education','guardian_name'=>'guardian_name','guardian_contact'=>'guardian_contact','guardian_email'=>'guardian_email','guardian_relation'=>'guardian_relation','guardian_age'=>'guardian_age','guardian_occupation'=>'guardian_occupation','guardian_education'=>'guardian_education','guardian_address'=>'guardian_address','GuardianNotes'=>'MoreInformation'];
    $enrollment_map = ['Level'=>'Level','Class'=>'Class','Stream'=>'Stream','Term'=>'Term','Residence'=>'Residence','EntryStatus'=>'EntryStatus','AcademicYear'=>'AcademicYear'];
    $academic_map = ['FormerSchool'=>'FormerSchool','PLEIndexNumber'=>'PLEIndexNumber','PLEAggregate'=>'PLEAggregate','UCEIndexNumber'=>'UCEIndexNumber','UCEResult'=>'UCEResult'];

    build_dynamic_update('erp_students', $Ad_no, $branch_id, $student_map, $conn);
    build_dynamic_update('erp_parents', $Ad_no, $branch_id, $parents_map, $conn);
    build_dynamic_update('erp_enrollment', $Ad_no, $branch_id, $enrollment_map, $conn);
    build_dynamic_update('erp_academichistory', $Ad_no, $branch_id, $academic_map, $conn);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Student record updated successfully.']);

} catch (Exception $e) {
    if ($conn) $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
