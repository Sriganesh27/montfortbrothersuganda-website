<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/fetch_students_migration.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

$level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_STRING);
$class = filter_input(INPUT_GET, 'class', FILTER_SANITIZE_STRING);
$stream = filter_input(INPUT_GET, 'stream', FILTER_SANITIZE_STRING);
$year_raw = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);

// Allow fetching if Year is set, OR if Level/Class are set
if (empty($year_raw) && (empty($level) || empty($class))) {
    echo json_encode(['success' => false, 'message' => 'Please select at least an Academic Year.']);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Start Query
$sql = "
    SELECT S.AdmissionNo, S.Name, S.Surname, S.PhotoPath, E.Level, E.Class, E.Stream, E.AcademicYear
    FROM erp_students S
    JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo
    WHERE 1=1
";

$params = [];
$types = "";

// 1. Filter by Year (Primary)
if (!empty($year_raw)) {
    // Robust check: If year is already formatted (e.g. 2025-26), don't format it again.
    if (strpos($year_raw, '-') !== false) {
        $academic_year = $year_raw;
    } else {
        $academic_year = format_academic_year($year_raw);
    }

    $sql .= " AND E.AcademicYear = ?";
    $params[] = $academic_year;
    $types .= "s";
}

// 2. Filter by Level (Optional)
if (!empty($level)) {
    $sql .= " AND E.Level = ?";
    $params[] = $level;
    $types .= "s";
}

// 3. Filter by Class (Optional)
if (!empty($class)) {
    $sql .= " AND E.Class = ?";
    $params[] = $class;
    $types .= "s";
}

// 4. Filter by Stream (Optional)
if (!empty($stream)) {
    $sql .= " AND E.Stream = ?";
    $params[] = $stream;
    $types .= "s";
}

$sql .= " ORDER BY E.Class ASC, S.Name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode(['success' => true, 'data' => $students]);
$stmt->close();
$conn->close();
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
