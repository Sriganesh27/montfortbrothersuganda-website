<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/migrate_backend.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$student_ids = $input['student_ids'] ?? [];
$target_year = $input['target_year'] ?? '';
$source_year = $input['source_year'] ?? ''; 
$target_term = $input['target_term'] ?? '';
$target_level = $input['target_level'] ?? '';
// Class and stream are optional for Alumni
$target_class = $input['target_class'] ?? '';
$target_stream = $input['target_stream'] ?? '';

// VALIDATION
if (empty($student_ids) || empty($target_year) || empty($target_term) || empty($target_level)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
if ($target_level !== 'alumni' && empty($target_class)) {
    echo json_encode(['success' => false, 'message' => 'Target Class is required for promotion.']);
    exit;
}

function safe_format_year($y) {
    if (empty($y)) return '';
    if (strpos($y, '-') !== false) return $y;
    return format_academic_year($y);
}

// Helper: Extract single completion year (e.g. "2025-26" -> "2026")
function extract_end_year($year_input) {
    if (empty($year_input)) return date('Y');
    $parts = explode('-', $year_input);
    $start_year = intval($parts[0]);
    if ($start_year > 0) {
        return (string)($start_year + 1);
    }
    return $year_input;
}

$target_academic_year = safe_format_year($target_year);

$conn = get_db_connection_transactional($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    $success_count = 0;

    // =========================================================
    // SCENARIO A: MOVE TO ALUMNI
    // =========================================================
    if ($target_level === 'alumni') {
        
        // 1. Determine Completion Year
        $raw_year_basis = !empty($source_year) ? $source_year : $target_year;
        $completion_year = extract_end_year($raw_year_basis);

        // 2. Archive to EnrollmentHistory
        $sql_hist = "INSERT INTO erp_enrollmenthistory (AdmissionNo, AcademicYear, Level, Class, Term, Stream, Residence, EntryStatus)
                     SELECT AdmissionNo, AcademicYear, Level, Class, Term, Stream, Residence, EntryStatus
                     FROM erp_enrollment WHERE AdmissionNo = ?";
        $stmt_hist = $conn->prepare($sql_hist);

        // 3. Insert INTO erp_alumni Table
        $sql_alum = "INSERT INTO erp_alumni (AdmissionNo, CompletionYear, Notes) VALUES (?, ?, ?)";
        $stmt_alum = $conn->prepare($sql_alum);

        // 4. Delete FROM erp_enrollment
        $sql_del = "DELETE FROM erp_enrollment WHERE AdmissionNo = ?";
        $stmt_del = $conn->prepare($sql_del);

        // 5. Deactivate Account
        $sql_acc = "UPDATE erp_student_accounts SET is_active = 0 WHERE AdmissionNo = ?";
        $stmt_acc = $conn->prepare($sql_acc);

        $note = "Completed in " . $target_term;

        foreach ($student_ids as $id) {
            $stmt_hist->bind_param("i", $id);
            $stmt_hist->execute();

            $stmt_alum->bind_param("iss", $id, $completion_year, $note);
            $stmt_alum->execute();

            $stmt_acc->bind_param("i", $id);
            $stmt_acc->execute();

            $stmt_del->bind_param("i", $id);
            if (!$stmt_del->execute()) {
                throw new Exception("Failed to remove Student ID $id FROM erp_enrollment.");
            }
            $success_count++;
        }
        
        // UPDATED: Overwrite target details for the success response
        $target_class = "Alumni List"; 
        $target_year = $completion_year; // Send back the single year (e.g. 2026)
        
    } 
    // =========================================================
    // SCENARIO B: NORMAL PROMOTION
    // =========================================================
    else {
        // Check Capacity
        $sql_check = "SELECT COUNT(*) as count FROM erp_enrollment 
                      WHERE AcademicYear = ? AND Class = ? AND Stream = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sss", $target_academic_year, $target_class, $target_stream);
        $stmt_check->execute();
        $curr_count_result = $stmt_check->get_result()->fetch_assoc();
        $current_count = $curr_count_result['count'];
        $stmt_check->close();

        $incoming_count = count($student_ids);
        
        if (defined('MAX_STUDENTS_PER_STREAM') && ($current_count + $incoming_count) > MAX_STUDENTS_PER_STREAM) {
            throw new Exception("Migration Blocked: Class is full ($current_count students). Limit: " . MAX_STUDENTS_PER_STREAM);
        }

        $sql_hist = "INSERT INTO erp_enrollmenthistory (AdmissionNo, AcademicYear, Level, Class, Term, Stream, Residence, EntryStatus)
                     SELECT AdmissionNo, AcademicYear, Level, Class, Term, Stream, Residence, EntryStatus
                     FROM erp_enrollment WHERE AdmissionNo = ?";
        $stmt_hist = $conn->prepare($sql_hist);

        $sql_update = "UPDATE erp_enrollment SET AcademicYear = ?, Term = ?, Level = ?, Class = ?, Stream = ? WHERE AdmissionNo = ?";
        $stmt_update = $conn->prepare($sql_update);

        foreach ($student_ids as $id) {
            $stmt_hist->bind_param("i", $id);
            $stmt_hist->execute(); 

            $stmt_update->bind_param("sssssi", $target_academic_year, $target_term, $target_level, $target_class, $target_stream, $id);
            if (!$stmt_update->execute()) {
                throw new Exception("Failed to migrate Student ID: $id");
            }
            $success_count++;
        }
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'count' => $success_count,
        'target_class' => $target_class,
        'target_stream' => $target_stream,
        'target_year' => $target_year,
        'message' => "Successfully processed $success_count student(s)."
    ]);

} catch (Exception $e) {
    if ($conn) $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
