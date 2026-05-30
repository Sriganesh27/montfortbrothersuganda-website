<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/fetch_student_accounts.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

try {
    $conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // --- 1. Get Filters ---
    $year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);
    $class = filter_input(INPUT_GET, 'class', FILTER_SANITIZE_STRING);
    $stream = filter_input(INPUT_GET, 'stream', FILTER_SANITIZE_STRING);
    $level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_STRING);
    
    // NEW: Get Search Inputs
    $search_id = filter_input(INPUT_GET, 'search_id', FILTER_SANITIZE_STRING);
    $search_name = filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_STRING);

    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 25;
    $offset = ($page - 1) * $limit;

    // --- 2. Build Query Conditions ---
    $where = ["1=1"];
    $params = [];
    $types = "";

    // A. Search by Admission No (matches anywhere in string)
    if (!empty($search_id)) {
        $where[] = "S.AdmissionNo LIKE ?";
        $params[] = "%$search_id%";
        $types .= "s";
    }

    // B. Search by Name (matches Name OR Surname)
    if (!empty($search_name)) {
        $where[] = "(S.Name LIKE ? OR S.Surname LIKE ?)";
        $params[] = "%$search_name%";
        $params[] = "%$search_name%";
        $types .= "ss";
    }

    // C. Filter by Academic Year
    if (!empty($year)) {
        // Check if hyphen exists to avoid double-formatting
        $academic_year = (strpos($year, '-') !== false) ? $year : format_academic_year($year);
        $where[] = "E.AcademicYear = ?";
        $params[] = $academic_year;
        $types .= "s";
    }

    // D. Filter by Level
    if (!empty($level) && $level !== 'All') {
        $where[] = "E.Level = ?";
        $params[] = $level;
        $types .= "s";
    }

    // E. Filter by Class
    if (!empty($class) && $class !== 'All') {
        $where[] = "E.Class = ?";
        $params[] = $class;
        $types .= "s";
    }

    // F. Filter by Stream
    if (!empty($stream) && $stream !== 'All') {
        $where[] = "E.Stream = ?";
        $params[] = $stream;
        $types .= "s";
    }

    $whereSql = implode(" AND ", $where);

    // --- 3. Get Total Count ---
    $countSql = "SELECT COUNT(*) as total 
                 FROM erp_enrollment E 
                 JOIN erp_students S ON E.AdmissionNo = S.AdmissionNo
                 WHERE $whereSql";
    
    $stmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $totalRows = $result->fetch_assoc()['total'] ?? 0;
    $totalPages = ceil($totalRows / $limit);

    // --- 4. Get Data ---
    $sql = "SELECT 
                S.AdmissionNo,
                CONCAT(S.Name, ' ', S.Surname) AS FullName,
                E.Class,
                E.Stream,
                SA.username AS Username,
                SA.password AS Password,
                COALESCE(SA.is_active, 0) AS IsActive,
                CASE 
                    WHEN SA.account_id IS NULL THEN 'Not Created'
                    WHEN SA.is_active = 1 THEN 'Active' 
                    ELSE 'Inactive' 
                END AS LoginStatus
            FROM erp_enrollment E
            JOIN erp_students S ON E.AdmissionNo = S.AdmissionNo
            LEFT JOIN erp_student_accounts SA ON S.AdmissionNo = SA.AdmissionNo
            WHERE $whereSql
            ORDER BY S.AdmissionNo Asc
            LIMIT ? OFFSET ?";

    // Add pagination params
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => $totalRows,
        'page' => $page,
        'total_pages' => $totalPages
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
