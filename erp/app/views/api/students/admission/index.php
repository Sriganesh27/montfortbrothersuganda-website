<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// web/erp/api/erp_students/admission.php

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

// Enable strict error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);

function send_json_error($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Method Not Allowed');
}

// Global variables from config.php
global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $UPLOAD_DIR_BASE, $UPLOAD_FOLDER_REL;

// --- CONFIGURATION ---
$ALLOWED_RELATIONS = ['Brother', 'Sister', 'Uncle', 'Aunt', 'Stepfather', 'Stepmother', 'Grandparent', 'Other'];
$ALLOWED_RESIDENCE = ['Day', 'Boarding'];
$ALLOWED_ENTRY     = ['New', 'Continuing'];
$ALLOWED_STREAMS   = ['A', 'B', 'C', 'D', 'E']; 
$ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];
$ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif']; 
$MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB

// Access current branch session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$branch_id = $_SESSION['branch_id'] ?? null;
if (!$branch_id) {
    send_json_error("Unauthorized: No school branch identified. Please re-login.");
}

// Step up one directory to remove the default "/erp_students/" from the config path
$UPLOAD_DIR = dirname(rtrim($UPLOAD_DIR_BASE, '/')) . '/'; 
$REAL_UPLOAD_REL = dirname(rtrim($UPLOAD_FOLDER_REL, '/')) . '/';

$uploaded_file_path = null; 
$conn = null;

try {
    // =========================================================
    // INITIALIZE DATABASE CONNECTION FIRST
    // =========================================================
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // =========================================================
    // PHASE 1: STRICT PRE-VALIDATION
    // =========================================================
    
    // 1. Personal Information Validation
    $name    = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $dob     = $_POST['dob'] ?? '';
    $gender  = $_POST['gender'] ?? '';
    
    if (empty($name) || empty($surname) || empty($dob) || empty($gender)) {
        throw new Exception("Personal Information (Name, Surname, DOB, Gender) is incomplete.");
    }

    // 2. Parents Validation
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    
    if (empty($father_name) || empty($mother_name)) {
        throw new Exception("Father's and Mother's names are required.");
    }

    // 3. Guardian Validation
    $g_name     = trim($_POST['guardian_name'] ?? '');
    $g_relation = trim($_POST['guardian_relation'] ?? '');
    $g_contact  = trim($_POST['guardian_contact'] ?? '');
    $g_email    = trim($_POST['guardian_email'] ?? '');
    $g_age      = trim($_POST['guardian_age'] ?? '');
    $g_occ      = trim($_POST['guardian_occupation'] ?? '');
    $g_edu      = trim($_POST['guardian_education'] ?? '');
    $g_addr     = trim($_POST['guardian_address'] ?? '');

    $has_input = !empty($g_name) || !empty($g_relation) || !empty($g_contact) || 
                 !empty($g_email) || !empty($g_age) || !empty($g_occ) || 
                 !empty($g_edu) || !empty($g_addr);

    if ($has_input) {
        $missing = [];
        if (empty($g_name)) $missing[] = "Name";
        if (empty($g_relation)) $missing[] = "Relation";
        if (empty($g_contact)) $missing[] = "Contact";
        if (empty($g_email)) $missing[] = "Email";
        if (empty($g_addr)) $missing[] = "Address";

        if (!empty($missing)) {
            throw new Exception("Guardian details cannot be partial. Missing: " . implode(', ', $missing));
        }
        if (!in_array($g_relation, $ALLOWED_RELATIONS)) {
            throw new Exception("Invalid Guardian Relation. Allowed: " . implode(', ', $ALLOWED_RELATIONS));
        }
    }

    // 4. Enrollment Validation
    $residence    = $_POST['residence'] ?? '';
    $entry_status = $_POST['entry_status'] ?? '';
    
    // MASTER CLASS FORMATTING: Remove spaces, dots, and replace 'PP' with 'N'
    $raw_class    = $_POST['class'] ?? '';
    $class_grade  = str_replace([' ', '.', 'PP'], ['', '', 'N'], strtoupper($raw_class));
    
    $level        = $_POST['level'] ?? '';
    $term         = $_POST['term'] ?? '';
    $admission_year = $_POST['admission_year'] ?? date('Y');

    if (empty($class_grade) || empty($level) || empty($term)) {
        throw new Exception("Enrollment details (Class, Level, Term) are required.");
    }
    if (!in_array($residence, $ALLOWED_RESIDENCE)) {
        throw new Exception("Invalid Residence. Please select Day or Boarding.");
    }
    if (!in_array($entry_status, $ALLOWED_ENTRY)) {
        throw new Exception("Invalid Entry Status. Please select New or Continuing.");
    }

    // 5. Account Pre-Validation & Fetch Branch Data
    $stream = $_POST['stream'] ?? '';
    if (empty($stream) || $stream === 'Auto') {
        $stream = 'A'; 
    }
    if (!in_array($stream, $ALLOWED_STREAMS)) {
        throw new Exception("Invalid Stream selected. Allowed: " . implode(', ', $ALLOWED_STREAMS));
    }

    $plain_password = $surname . $admission_year; 
    $account_password_hash = password_hash($plain_password, PASSWORD_DEFAULT);

    // Fetch school code AND branch names
    $stmt_code = $conn->prepare("SELECT school_code, branch_name, branch_location FROM erp_branches WHERE branch_id = ?");
    $stmt_code->bind_param("i", $branch_id);
    $stmt_code->execute();
    $branch_data = $stmt_code->get_result()->fetch_assoc();
    
    $school_code = $branch_data['school_code'] ?? 'U011'; // Default fallback
    $branch_name = $branch_data['branch_name'] ?? 'School';
    $branch_location = $branch_data['branch_location'] ?? 'Location';
    $stmt_code->close();

    $account_is_active = 1;

    // 6. Strict Photo Validation
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        if ($file['size'] > $MAX_FILE_SIZE) {
            throw new Exception("Photo is too large. Max allowed size is 1MB.");
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
            throw new Exception("Invalid photo format. Allowed: " . implode(', ', $ALLOWED_EXTENSIONS));
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime_type, $ALLOWED_MIME_TYPES)) {
            throw new Exception("Security Error: Uploaded file is not a valid image.");
        }
    }

    // =========================================================
    // PHASE 2: DATABASE OPERATION
    // =========================================================

    $conn->begin_transaction();

    // Independent Admission Number Generator
    $ad_no = get_next_admission_no($conn, $branch_id);
    $academic_year_string = format_academic_year($admission_year);
    
    // Address Variables
    $house       = trim($_POST['house_no'] ?? '');
    $street      = trim($_POST['street'] ?? '');
    $village     = trim($_POST['village'] ?? '');
    $town        = trim($_POST['town'] ?? '');
    $district    = trim($_POST['district'] ?? '');
    $state       = trim($_POST['state'] ?? '');
    $country     = trim($_POST['country'] ?? 'Uganda');
    $postal_code = trim($_POST['postal_code'] ?? ''); 

    // Parents & Guardian Variables
    $father_contact = !empty($_POST['father_contact']) ? $_POST['father_contact'] : null;
    $mother_contact = !empty($_POST['mother_contact']) ? $_POST['mother_contact'] : null;
    $f_email        = !empty($_POST['father_email']) ? trim($_POST['father_email']) : null;
    $m_email        = !empty($_POST['mother_email']) ? trim($_POST['mother_email']) : null;
    
    $final_g_name     = $has_input ? $g_name : null;
    $final_g_relation = $has_input ? $g_relation : null;
    $final_g_contact  = $has_input ? $g_contact : null;
    $final_g_email    = $has_input ? $g_email : null;
    $final_g_age      = $has_input ? $g_age : null;
    $final_g_occ      = $has_input ? $g_occ : null;
    $final_g_edu      = $has_input ? $g_edu : null;
    $final_g_addr     = $has_input ? $g_addr : null;

    // --- INSERT STUDENT (Updated Fields) ---
    $middle_name = trim($_POST['middle_name'] ?? '');
    $nationality = trim($_POST['nationality'] ?? 'Ugandan');
    
    $sql_stu = "INSERT INTO erp_students (AdmissionNo, branch_id, AdmissionYear, Name, MiddleName, Surname, DateOfBirth, Gender, Nationality, HouseNo, Street, Village, Town, District, State, Country, PostalCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_stu = $conn->prepare($sql_stu);
    $stmt_stu->bind_param("iiissssssssssssss", $ad_no, $branch_id, $admission_year, $name, $middle_name, $surname, $dob, $gender, $nationality, $house, $street, $village, $town, $district, $state, $country, $postal_code);
    $stmt_stu->execute();
    $stmt_stu->close();

    // --- INSERT PARENTS ---
    $sql_par = "INSERT INTO erp_parents (AdmissionNo, branch_id, father_name, father_contact, father_email, father_age, father_occupation, father_education, mother_name, mother_contact, mother_email, mother_age, mother_occupation, mother_education, guardian_name, guardian_relation, guardian_contact, guardian_email, guardian_age, guardian_occupation, guardian_education, guardian_address, MoreInformation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_par = $conn->prepare($sql_par);
    $f_age = !empty($_POST['father_age']) ? $_POST['father_age'] : null;
    $f_occ = $_POST['father_occupation'] ?? '';
    $f_edu = $_POST['father_education'] ?? '';
    $m_age = !empty($_POST['mother_age']) ? $_POST['mother_age'] : null;
    $m_occ = $_POST['mother_occupation'] ?? '';
    $m_edu = $_POST['mother_education'] ?? '';
    $more_info = $_POST['more_info'] ?? '';
    $stmt_par->bind_param("iisssisssssissssssissss", $ad_no, $branch_id, $father_name, $father_contact, $f_email, $f_age, $f_occ, $f_edu, $mother_name, $mother_contact, $m_email, $m_age, $m_occ, $m_edu, $final_g_name, $final_g_relation, $final_g_contact, $final_g_email, $final_g_age, $final_g_occ, $final_g_edu, $final_g_addr, $more_info);
    $stmt_par->execute();
    $stmt_par->close();

    // --- INSERT ACADEMIC HISTORY (Updated Fields) ---
    $former_code = trim($_POST['former_school_code'] ?? '');
    $former_lin  = trim($_POST['former_school_lin'] ?? '');
    $sub_marks   = trim($_POST['subject_marks'] ?? '');
    $former      = trim($_POST['former_school'] ?? '');
    $ple_idx     = trim($_POST['ple_index'] ?? '');
    $ple_agg     = !empty($_POST['ple_agg']) ? $_POST['ple_agg'] : null;
    $uce_idx     = trim($_POST['uce_index'] ?? '');
    $uce_res     = trim($_POST['uce_result'] ?? '');
    
    $sql_hist = "INSERT INTO erp_academichistory (AdmissionNo, branch_id, FormerSchool, FormerSchoolCode, FormerSchoolLIN, PLEIndexNumber, PLEAggregate, UCEIndexNumber, UCEResult, SubjectMarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_hist = $conn->prepare($sql_hist);
    $stmt_hist->bind_param("iisssssiss", $ad_no, $branch_id, $former, $former_code, $former_lin, $ple_idx, $ple_agg, $uce_idx, $uce_res, $sub_marks);
    $stmt_hist->execute();
    $stmt_hist->close();

    // --- INSERT ENROLLMENT ---
    $sql_enr = "INSERT INTO erp_enrollment (AdmissionNo, branch_id, AcademicYear, Term, Class, Level, Stream, Residence, EntryStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_enr = $conn->prepare($sql_enr);
    $stmt_enr->bind_param("iisssssss", $ad_no, $branch_id, $academic_year_string, $term, $class_grade, $level, $stream, $residence, $entry_status);
    $stmt_enr->execute();
    $stmt_enr->close();
    
    // --- FINALIZE STUDENT ACCOUNT (Hyphenated ID Generation) ---
    // Target Format: U011-26-N1-0001
    $year_short = substr((string)$admission_year, -2);
    $formatted_ad_no = str_pad((string)$ad_no, 4, '0', STR_PAD_LEFT);
    $final_username = $school_code . "-" . $year_short . "-" . $class_grade . "-" . $formatted_ad_no;
    
    $sql_acc = "INSERT INTO erp_student_accounts(AdmissionNo, branch_id, username, password, is_active) VALUES (?, ?, ?, ?, ?)";
    $stmt_acc = $conn->prepare($sql_acc);
    $stmt_acc->bind_param("iissi", $ad_no, $branch_id, $final_username, $account_password_hash, $account_is_active);
    $stmt_acc->execute();
    $stmt_acc->close();

    // =========================================================
    // NEW: CLOSE THE APPLICATION LOOP
    // =========================================================
    $link_app_id = !empty($_POST['link_app_id']) ? (int)$_POST['link_app_id'] : null;
    if ($link_app_id) {
        // Mark the application as 'Admitted' so it clears from the dashboard
        $stmt_app = $conn->prepare("UPDATE erp_applications SET status = 'Admitted' WHERE app_id = ? AND branch_id = ?");
        if ($stmt_app) {
            $stmt_app->bind_param("ii", $link_app_id, $branch_id);
            $stmt_app->execute();
            $stmt_app->close();
        }
    }

    // --- PHOTO UPLOAD LOGIC ---
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        if ($file['size'] <= $MAX_FILE_SIZE) {
            $custom_folder = get_custom_student_folder($conn, $ad_no, $branch_id);
            if (!$custom_folder) {
                $safe_branch   = sanitize_for_filename($branch_name);
                $safe_location = sanitize_for_filename($branch_location);
                $safe_student  = sanitize_for_filename($name . '_' . $surname);
                $custom_folder = $safe_branch . '(' . $safe_location . '-' . $branch_id . ')/students/' . $ad_no . '_' . $safe_student . '/';
            }
            if (!is_dir($UPLOAD_DIR . $custom_folder)) {
                mkdir($UPLOAD_DIR . $custom_folder, 0755, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $safe_name = sanitize_for_filename($name . '_' . $surname) . '_' . $ad_no . '.' . $ext;
            
            if (move_uploaded_file($file['tmp_name'], $UPLOAD_DIR . $custom_folder . $safe_name)) {
                $photo_path = $REAL_UPLOAD_REL . $custom_folder . $safe_name;
                $uploaded_file_path = $UPLOAD_DIR . $custom_folder . $safe_name;
                $stmt_p = $conn->prepare("UPDATE erp_students SET PhotoPath = ? WHERE AdmissionNo = ? AND branch_id = ?");
                $stmt_p->bind_param("sii", $photo_path, $ad_no, $branch_id);
                $stmt_p->execute();
                $stmt_p->close();
            }
        }
    }

    $conn->commit();
    
    // Customize success message if it came from an application
    if ($link_app_id) {
        echo json_encode(['success' => true, 'message' => "Application Admitted Successfully!<br><strong>Adm-no: " . $formatted_ad_no . "</strong><br><strong> Student ID: $final_username</strong>"]);
    } else {
        echo json_encode(['success' => true, 'message' => "Admitted successfully.<br><strong>Adm-no: " . $formatted_ad_no . "</strong><br><strong> Student ID: $final_username</strong>"]);
    }

} catch (\Throwable $e) {
    if ($conn && $conn->ping()) {
        try { $conn->rollback(); } catch (Exception $ex) { error_log("Rollback Failed: " . $ex->getMessage()); }
    }
    if ($uploaded_file_path && file_exists($uploaded_file_path)) {
        unlink($uploaded_file_path);
    }
    send_json_error($e->getMessage());
} finally {
    if ($conn) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
