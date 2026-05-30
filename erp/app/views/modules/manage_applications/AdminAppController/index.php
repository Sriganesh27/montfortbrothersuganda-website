<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
session_start();
require_once '../../config/config.php'; 
require_once '../../includes/db.php'; 
header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'] ?? exit(json_encode(['success'=>false, 'message'=>'Unauthorized']));
global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME; 
$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'fetch_list') {
        $status_filter = $_GET['status'] ?? 'All';
        $sql = "SELECT app_id, ref_number, student_name, student_surname, applied_class, status, scholarship_status, created_at FROM erp_applications WHERE branch_id = ?";
        $params = [$branch_id]; $types = "i";
        if ($status_filter !== 'All') { $sql .= " AND status = ?"; $params[] = $status_filter; $types .= "s"; }
        $stmt = $conn->prepare($sql . " ORDER BY created_at DESC"); $stmt->bind_param($types, ...$params); $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }
    elseif ($action === 'fetch_single') {
        $app_id = (int)$_GET['app_id'];
        $stmt = $conn->prepare("SELECT * FROM erp_applications WHERE app_id = ? AND branch_id = ?");
        $stmt->bind_param("ii", $app_id, $branch_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Application not found.']);
        }
    }
    elseif ($action === 'update_status') { 
        $stmt = $conn->prepare("UPDATE erp_applications SET status = ? WHERE app_id = ? AND branch_id = ?");
        $stmt->bind_param("sii", $_POST['status'], $_POST['app_id'], $branch_id); $stmt->execute();
        echo json_encode(['success'=>true, 'message'=>"Updated to " . $_POST['status']]); 
    }
    elseif ($action === 'update_scholarship') { 
        $stmt = $conn->prepare("UPDATE erp_applications SET scholarship_status = ? WHERE app_id = ? AND branch_id = ?");
        $stmt->bind_param("sii", $_POST['scholarship'], $_POST['app_id'], $branch_id); $stmt->execute();
        echo json_encode(['success'=>true, 'message'=>"Scholarship updated"]); 
    }
} catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
