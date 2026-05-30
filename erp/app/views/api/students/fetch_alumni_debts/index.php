<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/fetch_alumni_debts.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

try {
    $conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (!$conn) throw new Exception("Database connection failed.");

    // --- 1. Get Filters ---
    $search_id = filter_input(INPUT_GET, 'search_id', FILTER_SANITIZE_STRING);
    $search_name = filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_STRING);
    $comp_year = filter_input(INPUT_GET, 'completed_year', FILTER_SANITIZE_STRING);

    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 25;
    $offset = ($page - 1) * $limit;

    // --- 2. Build Query Conditions ---
    $where = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search_id)) {
        $where[] = "S.AdmissionNo LIKE ?";
        $params[] = "%$search_id%";
        $types .= "s";
    }
    if (!empty($search_name)) {
        $where[] = "(S.Name LIKE ? OR S.Surname LIKE ?)";
        $params[] = "%$search_name%";
        $params[] = "%$search_name%";
        $types .= "ss";
    }
    if (!empty($comp_year)) {
        $where[] = "A.CompletionYear = ?";
        $params[] = $comp_year;
        $types .= "s";
    }

    $whereSql = implode(" AND ", $where);

    // --- 3. Get Total Count ---
    $countSql = "SELECT COUNT(*) as total 
                 FROM erp_alumni A 
                 JOIN erp_students S ON A.AdmissionNo = S.AdmissionNo
                 WHERE $whereSql";
    
    $stmt = $conn->prepare($countSql);
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $totalPages = ceil($totalRows / $limit);

    // --- 4. Fetch Data with Parent Info ---
    // We select parent details using COALESCE to prioritize Father > Mother > Guardian
    $sql = "SELECT 
                S.AdmissionNo,
                CONCAT(S.Name, ' ', S.Surname) AS FullName,
                A.CompletionYear,
                COALESCE(NULLIF(P.father_name, ''), NULLIF(P.mother_name, ''), P.guardian_name, '-') as ParentName,
                COALESCE(NULLIF(P.father_contact, ''), NULLIF(P.mother_contact, ''), P.guardian_contact, '-') as Contact,
                0 as TotalAmount,
                0 as AmountPaid,
                0 as Balance
            FROM erp_alumni A
            JOIN erp_students S ON A.AdmissionNo = S.AdmissionNo
            LEFT JOIN erp_parents P ON A.AdmissionNo = P.AdmissionNo
            WHERE $whereSql
            ORDER BY A.CompletionYear DESC, S.Name ASC
            LIMIT ? OFFSET ?";

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
