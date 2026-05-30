<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/search_global.php

// 1. CRITICAL: Disable on-screen error reporting to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

// 2. Validate Input
$query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);

if (empty($query)) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    // Send a JSON error instead of a PHP warning
    echo json_encode(['success' => false, 'message' => 'Database Error']);
    exit;
}

try {
    $searchTerm = "%" . $query . "%";

    // 3. Search Query
    // Searches Name, Surname, or Parent Names
    $sql = "
        SELECT 
            S.AdmissionNo, S.Name, S.Surname, S.PhotoPath,
            S.HouseNo, S.Street, S.Village, S.Town, S.District,
            E.Class, E.Stream, E.Term,
            P.father_name, P.father_contact,
            P.mother_name, P.mother_contact,
            P.guardian_name, P.guardian_contact
        FROM erp_students S
        JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo
        LEFT JOIN erp_parents P ON S.AdmissionNo = P.AdmissionNo
        WHERE 
            S.Name LIKE ? OR 
            S.Surname LIKE ? OR 
            P.father_name LIKE ? OR 
            P.mother_name LIKE ? OR 
            P.guardian_name LIKE ?
        LIMIT 15
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query preparation failed");
    }

    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format Address
        $addr_parts = array_filter([
            $row['HouseNo'] ?? '', 
            $row['Street'] ?? '', 
            $row['Village'] ?? '', 
            $row['Town'] ?? ''
        ]);
        
        $row['FormattedAddress'] = !empty($addr_parts) ? implode(', ', $addr_parts) : 'N/A';

        // Prioritize Parent Info
        $parentName = $row['father_name'] ?: ($row['mother_name'] ?: $row['guardian_name']);
        $parentContact = $row['father_contact'] ?: ($row['mother_contact'] ?: $row['guardian_contact']);
        
        $row['ParentDisplay'] = $parentName ?: '-';
        $row['ContactDisplay'] = $parentContact ?: '-';
        
        // Photo Fallback
        if (empty($row['PhotoPath'])) {
            $row['PhotoPath'] = 'assets/uploads/students/default_profile.png';
        }

        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);
    $stmt->close();

} catch (Exception $e) {
    // Catch any DB errors and send them as JSON
    echo json_encode(['success' => false, 'message' => 'Search Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
