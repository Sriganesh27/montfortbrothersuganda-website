<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/api/super_admin/manage_schools.php

// 1. Use __DIR__ to guarantee the correct file path regardless of how the script is called
require_once __DIR__ . '/../../includes/db.php';

// 2. Explicitly declare the global connection variable
global $conn;

header('Content-Type: application/json');

// --- 0. Security Check (CRITICAL) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow Super users to access this file
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Super User') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Super Admins only.']);
    exit;
}

// Ensure database connection exists
if (!$conn) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$action = $_GET['action'] ?? '';

// --- 1. Get All Schools ---
if ($action === 'list') {
    $res = $conn->query("SELECT * FROM erp_branches ORDER BY branch_id DESC");
    if ($res) {
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve schools.']);
    }
    exit;
}

// --- 2. Add or Update School ---
if ($action === 'save') {
    $id = $_POST['branch_id'] ?? null;
    $name = trim($_POST['branch_name'] ?? '');
    $loc = trim($_POST['branch_location'] ?? '');
    $type = trim($_POST['branch_type'] ?? '');

    // Basic Input Validation
    if (empty($name) || empty($loc) || empty($type)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE erp_branches SET branch_name=?, branch_location=?, branch_type=? WHERE branch_id=?");
        $stmt->bind_param("sssi", $name, $loc, $type, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO erp_branches (branch_name, branch_location, branch_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $loc, $type);
    }
    
    if ($stmt && $stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'School saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save school details.']);
    }
    if ($stmt) $stmt->close();
    exit;
}

// --- 3. Delete School ---
if ($action === 'delete') {
    $id = filter_input(INPUT_POST, 'branch_id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid branch ID provided.']);
        exit;
    }

    // Dependency Check: Prevent deleting a branch if it has active students
    $check_stmt = $conn->prepare("SELECT COUNT(*) as total FROM erp_students WHERE branch_id = ?");
    if ($check_stmt) {
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($result['total'] > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete: There are ' . $result['total'] . ' student(s) currently enrolled in this branch.']);
            exit;
        }
    }

    // Execute the deletion if the branch is completely empty
    $stmt = $conn->prepare("DELETE FROM erp_branches WHERE branch_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'School deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error while deleting.']);
        }
        $stmt->close();
    }
    exit;
}

// Fallback for invalid action routes
echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
