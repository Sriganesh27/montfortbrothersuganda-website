<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/fetch_academic_edit.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

// 1. Secure the branch session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$branch_id = $_SESSION['branch_id'] ?? null;
if (!$branch_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: No branch selected']);
    exit;
}

try {
    $conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (!$conn) throw new Exception("Database connection failed.");

    // Filters
    $year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);
    $level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_STRING);
    $class = filter_input(INPUT_GET, 'class', FILTER_SANITIZE_STRING);
    $stream = filter_input(INPUT_GET, 'stream', FILTER_SANITIZE_STRING);

    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 25;
    $offset = ($page - 1) * $limit;

    // Conditions (Forcing branch_id)
    $where = ["S.branch_id = ?"];
    $params = [$branch_id];
    $types = "i";

    if (!empty($year)) { $where[] = "E.AcademicYear = ?"; $params[] = $year; $types .= "s"; }
    if (!empty($level)) { $where[] = "E.Level = ?"; $params[] = $level; $types .= "s"; }
    if (!empty($class)) { $where[] = "E.Class = ?"; $params[] = $class; $types .= "s"; }
    if (!empty($stream)) { $where[] = "E.Stream = ?"; $params[] = $stream; $types .= "s"; }

    $where_sql = implode(" AND ", $where);

    // Total Count (Fixed JOINS)
    $count_sql = "SELECT COUNT(*) as total 
                  FROM erp_students S 
                  JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo AND S.branch_id = E.branch_id
                  WHERE $where_sql";
    $stmt = $conn->prepare($count_sql);
    if (!$stmt) throw new Exception("Count Prepare failed: " . $conn->error);
    
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_records = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Data Query (Fixed Case Sensitivity and JOINS)
    $sql = "SELECT 
                S.AdmissionNo, S.Name, S.Surname,
                E.Class, E.Stream, 
                A.LIN, A.Combination, 
                A.PLEIndexNumber, A.PLEAggregate, 
                A.UCEIndexNumber, A.UCEResult
            FROM erp_students S 
            JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo AND S.branch_id = E.branch_id
            LEFT JOIN erp_academichistory A ON S.AdmissionNo = A.AdmissionNo AND S.branch_id = A.branch_id
            WHERE $where_sql
            ORDER BY S.AdmissionNo ASC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Data Prepare failed: " . $conn->error);
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => $total_records,
        'page' => $page,
        'total_pages' => ceil($total_records / $limit)
    ]);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
