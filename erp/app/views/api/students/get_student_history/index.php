<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// school_administration1/api/students/get_student_history.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

$Ad_no = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$Ad_no) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch history records (Snapshots of previous enrollment data)
$sql = "SELECT AcademicYear, Term, Class, Stream, Level, Residence, EntryStatus, DateMoved 
        FROM erp_enrollmenthistory 
        WHERE AdmissionNo = ? 
        ORDER BY DateMoved DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Ad_no);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    // Format the date/time for the frontend
    $row['DateMoved'] = date('d M Y, h:i A', strtotime($row['DateMoved']));
    $history[] = $row;
}

echo json_encode(['success' => true, 'data' => $history]);

$stmt->close();
$conn->close();
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
