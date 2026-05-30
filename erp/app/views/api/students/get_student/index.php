<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// webpages/get_student.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

$Ad_no = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$Ad_no) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Admission No is required']);
    exit;
}

// Safely start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$branch_id = $_SESSION['branch_id'] ?? null;

// Stop execution if no branch is found in session
if (!$branch_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: No school branch identified.']);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn) {
    // Select ALL fields for parents and guardian
    $sql = "
        SELECT
    S.AdmissionNo, S.AdmissionYear, S.Name, S.Surname, S.DateOfBirth, S.Gender, 
    CONCAT_WS(' ', S.HouseNo, S.Street, S.Village, S.Town, S.District) AS Address,
    S.HouseNo, S.Street, S.Village, S.Town, S.District, S.State, S.Country, S.PostalCode,
    S.PhotoPath,            
            -- Father Details
            P.father_name, P.father_age, P.father_contact, P.father_occupation, P.father_education,
            
            -- Mother Details
            P.mother_name, P.mother_age, P.mother_contact, P.mother_occupation, P.mother_education,
            
            -- Guardian Details
            P.guardian_name AS GuardianName, 
            P.guardian_relation, 
            P.guardian_age, 
            P.guardian_contact AS ContactPrimary, 
            P.guardian_occupation, 
            P.guardian_education, 
            P.guardian_address,
            P.MoreInformation AS GuardianNotes,
            
            -- Academic & Enrollment
            A.FormerSchool, A.PLEIndexNumber, A.PLEAggregate, A.UCEIndexNumber, A.UCEResult,
            E.Class, E.Level, E.Term, E.AcademicYear, E.Residence, E.EntryStatus, E.Stream
        FROM erp_students S
        JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo AND S.branch_id = E.branch_id
        LEFT JOIN erp_parents P ON S.AdmissionNo = P.AdmissionNo AND S.branch_id = P.branch_id       
        LEFT JOIN erp_academichistory A ON S.AdmissionNo = A.AdmissionNo AND S.branch_id = A.branch_id
        WHERE S.AdmissionNo = ? AND S.branch_id = ?
    ";
    
    // 1. Prepare FIRST
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // 2. Bind SECOND (Passing both parameters: "ii" for two integers)
        $stmt->bind_param("ii", $Ad_no, $branch_id);
        
        // 3. Execute
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found in this branch']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database query failed to prepare']);
    }
    $conn->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
