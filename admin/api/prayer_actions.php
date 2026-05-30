<?php
// web/admin/api/prayer_actions.php
session_start();
require_once '../../api/db_config.php';

header('Content-Type: application/json');

// Security Check: Only Admins
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    // FETCH SINGLE USER DATA FOR EDIT MODAL
    if ($action === 'get_user') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("Invalid ID.");

        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, gender, prayer_count FROM web_prayer_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) echo json_encode(['success' => true, 'data' => $user]);
        else echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // UPDATE USER DATA
    if ($action === 'update_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name = strip_tags($_POST['full_name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
        $gender = $_POST['gender'] ?? 'Other';
        $count = filter_input(INPUT_POST, 'prayer_count', FILTER_VALIDATE_INT);

        if (!$id || !$name) throw new Exception("Name and valid ID are required.");

        $stmt = $pdo->prepare("UPDATE web_prayer_users SET full_name = ?, email = ?, phone = ?, gender = ?, prayer_count = ? WHERE id = ?");
        $stmt->execute([$name, $email ?: null, $phone ?: null, $gender, $count, $id]);

        echo json_encode(['success' => true, 'message' => 'Intercessor updated successfully.']);
        exit;
    }

    // DELETE USER
    if ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("Invalid ID.");

        $stmt = $pdo->prepare("DELETE FROM web_prayer_users WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Intercessor removed successfully.']);
        exit;
    }

    throw new Exception("Invalid action.");

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>