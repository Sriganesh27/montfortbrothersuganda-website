<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/api/students/update_mark.php
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $field = $_POST['field']; // e.g., 'english', 'mathematics', 'school_name'
    $value = $_POST['value'];

    $sql = "UPDATE erp_external_exam_results SET $field = ? WHERE result_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $value, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
