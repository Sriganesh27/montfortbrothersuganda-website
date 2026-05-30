<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
/**
 * ERP GLOBAL CONFIGURATION - MULTI-BRANCH SYSTEM
 * Locations: 1-St. Kizito (Kyebando), 2-St. Montfort (Mpala), 3-Pere Achte (Isunga)
 */

define('CONFIG_LOADED', true);

// --- 1. Database Configuration ---
$DB_HOST = '68.178.237.26';
$DB_USER = 'montfortu'; 
$DB_PASS = 'montfort@123'; 
$DB_NAME = 'montfortug';    

// --- 2. Session & Branch Security ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Branch Context Variables
 * These are set during login and used by every API to filter data.
 */
$current_branch_id   = $_SESSION['branch_id'] ?? null;
$current_role        = $_SESSION['role'] ?? null;
$current_branch_type = $_SESSION['branch_type'] ?? null; // 'Primary' or 'Secondary'
$current_school_name = $_SESSION['school_name'] ?? 'ERP System';

// --- 3. File Handling & Global Constants ---
$UPLOAD_FOLDER_REL = 'assets/uploads/students/'; 
$UPLOAD_DIR_BASE   = __DIR__ . '/../assets/uploads/students/';
$MAX_FILE_SIZE     = 1 * 1024 * 1024; // 1MB

define('MAX_STUDENTS_PER_STREAM', 50); 
define('MAX_STUDENTS_PER_CLASS', 250); 

// --- 4. Core Helper Functions ---

/**
 * Standard Connection Helper
 */
function get_db_connection($host, $user, $pass, $db_name) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli($host, $user, $pass, $db_name);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null; 
    }
}

/**
 * Independent Admission Number Generator
 * Fetches the next ID for the specific branch without conflicting with other schools.
 */
function get_next_admission_no($conn, $branch_id) {
    $sql = "SELECT MAX(AdmissionNo) as last_no FROM erp_students WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return ($result['last_no']) ? (int)$result['last_no'] + 1 : 1;
}

/**
 * Full Student Data Fetcher (Branch Aware)
 * Uses the Composite Key (branch_id + AdmissionNo) to find the correct student.
 */
function fetch_full_student_data($conn, $Ad_no, $branch_id) {
    $sql = "
        SELECT 
            S.*, 
            E.Class, E.Level, E.Term, E.AcademicYear, E.Stream, E.Residence, E.EntryStatus, 
            SA.username,
            P.father_name, P.father_contact, P.father_email, P.father_age, P.father_occupation, P.father_education,
            P.mother_name, P.mother_contact, P.mother_email, P.mother_age, P.mother_occupation, P.mother_education,
            P.guardian_name, P.guardian_contact, P.guardian_email, P.guardian_relation, P.guardian_age, P.guardian_occupation, P.guardian_education, P.guardian_address, 
            P.MoreInformation AS GuardianNotes,
            A.FormerSchool, A.PLEIndexNumber, A.PLEAggregate, A.UCEIndexNumber, A.UCEResult
        FROM erp_students S
        LEFT JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo AND S.branch_id = E.branch_id
        LEFT JOIN erp_student_accounts SA ON S.AdmissionNo = SA.AdmissionNo AND S.branch_id = SA.branch_id
        LEFT JOIN erp_parents P ON S.AdmissionNo = P.AdmissionNo AND S.branch_id = P.branch_id
        LEFT JOIN erp_academichistory A ON S.AdmissionNo = A.AdmissionNo AND S.branch_id = A.branch_id
        WHERE S.AdmissionNo = ? AND S.branch_id = ?
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $Ad_no, $branch_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

// --- 5. Utilities ---
function format_academic_year($year) {
    if (empty($year) || !is_numeric($year)) return $year;
    $next_year_short = substr((int)$year + 1, -2);
    return $year . '-' . $next_year_short;
}

function getStreamOptions() {
    return ['A', 'B', 'C', 'D', 'E'];
}

// --- 6. FILE SYSTEM HELPERS (NEWLY ADDED) ---
function sanitize_for_filename($string) {
    $string = str_replace(' ', '_', trim($string)); 
    return preg_replace('/[^A-Za-z0-9_]/', '', $string); 
}

function get_custom_student_folder($conn, $ad_no, $branch_id) {
    $sql = "SELECT s.Name, s.Surname, b.branch_name, b.branch_location 
            FROM erp_students s 
            JOIN erp_branches b ON s.branch_id = b.branch_id 
            WHERE s.AdmissionNo = ? AND s.branch_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    
    $stmt->bind_param("ii", $ad_no, $branch_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$res) return null;

    $safe_branch   = sanitize_for_filename($res['branch_name']);
    $safe_location = sanitize_for_filename($res['branch_location']);
    $safe_student  = sanitize_for_filename($res['Name'] . '_' . $res['Surname']);

    return $safe_branch . '(' . $safe_location . '-' . $branch_id . ')/students/' . $ad_no . '_' . $safe_student . '/';
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
