<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// api/students/manage_account.php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

// Helper to generate a default username if needed
function generate_username($admissionNo) {
    // Example: STU-001
    return 'STU-' . str_pad($admissionNo, 4, '0', STR_PAD_LEFT);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method Not Allowed");
    }

    $conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    $action = $_POST['action'] ?? '';
    $admissionNo = filter_input(INPUT_POST, 'admission_no', FILTER_VALIDATE_INT);

    if (!$admissionNo) {
        throw new Exception("Invalid Admission No.");
    }

    if ($action === 'reset_password' || $action === 'update_password') {
        // --- ACTION: CREATE OR UPDATE ACCOUNT ---
        
        // 1. Determine Password and Apply Hashing
        if ($action === 'update_password') {
            $rawPass = $_POST['new_password'] ?? '';
            if (empty(trim($rawPass))) throw new Exception("Password cannot be empty.");
            
            // HASHING THE NEW PASSWORD
            $passwordToStore = password_hash(trim($rawPass), PASSWORD_DEFAULT);
            $msgType = "Password updated successfully.";
        } else {
            // Default reset password
            $defaultRaw = "Student123"; 
            
            // HASHING THE DEFAULT PASSWORD
            $passwordToStore = password_hash($defaultRaw, PASSWORD_DEFAULT);
            $msgType = "Password reset to default ($defaultRaw).";
        }

        // 2. Check if account exists
        $checkSql = "SELECT account_id, username FROM erp_student_accounts WHERE AdmissionNo = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("i", $admissionNo);
        $stmt->execute();
        $res = $stmt->get_result();
        $account = $res->fetch_assoc();
        $stmt->close();

        if ($account) {
            // Update existing account with the hashed password
            $updateSql = "UPDATE erp_student_accounts SET password = ?, is_active = 1 WHERE AdmissionNo = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("si", $passwordToStore, $admissionNo);
            $stmt->execute();
            $msg = $msgType;
        } else {
            // Create new account with hashed password and generated username
            $username = generate_username($admissionNo);
            $insertSql = "INSERT INTO erp_student_accounts (AdmissionNo, username, password, is_active) VALUES (?, ?, ?, 1)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("iss", $admissionNo, $username, $passwordToStore);
            $stmt->execute();
            $msg = "Account created. Username: $username. $msgType";
        }

        echo json_encode(['success' => true, 'message' => $msg]);

    } elseif ($action === 'toggle_status') {
        // --- ACTION: TOGGLE STATUS ---
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
        
        // Ensure account exists first
        $checkSql = "SELECT account_id FROM erp_student_accounts WHERE AdmissionNo = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("i", $admissionNo);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Account does not exist. Please set a password to create one first.");
        }
        $stmt->close();

        $updateSql = "UPDATE erp_student_accounts SET is_active = ? WHERE AdmissionNo = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $status, $admissionNo);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            throw new Exception("Failed to update status.");
        }

    } else {
        throw new Exception("Invalid Action");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
