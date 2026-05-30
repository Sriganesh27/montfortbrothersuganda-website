<?php
// erp/app/controllers/LoginProcessController.php

class LoginProcessController
{
    public function index()
    {
        // 1. Force JSON output and hide HTML errors
        header('Content-Type: application/json');
        error_reporting(0);
        ini_set('display_errors', 0);

        // 2. Start session
        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        }

        // 3. Load Database Config (Go up two levels to reach config folder)
        require_once __DIR__ . '/../../config/config.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 4. Connect to Database
            $conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if (!$conn) {
                echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
                exit;
            }

            // 5. Get POST data from the form
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $branch_id = $_POST['branch_id'] ?? '';

            $needs_branch = in_array($role, ['Branch Admin', 'Faculty', 'Parents']);
            
            // Validate empty fields
            if (empty($username) || empty($password) || empty($role) || ($needs_branch && empty($branch_id))) {
                echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
                exit;
            }

            // 6. Database Validation Logic
            try {
                if ($role === 'Parents') {
                    $sql = "SELECT account_id, username, password, AdmissionNo, branch_id 
                            FROM erp_student_accounts WHERE username = ? AND branch_id = ? AND is_active = 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $username, $branch_id);
                    
                } elseif ($role === 'Super User') {
                    $sql = "SELECT * FROM erp_users WHERE username = ? AND role = ? AND is_active = 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $username, $role);
                    
                } else {
                    $sql = "SELECT u.*, b.branch_name, b.branch_type 
                            FROM erp_users u 
                            JOIN erp_branches b ON u.assigned_branch = b.branch_id
                            WHERE u.username = ? AND u.role = ? AND u.assigned_branch = ? AND u.is_active = 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $username, $role, $branch_id);
                }

                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();

                // Check password (Assuming you are using password_hash() in your database)
                if ($user && password_verify($password, $user['password'])) {
                    
                    // 7. Set Session Variables
                    $_SESSION['user_id'] = ($role === 'Parents') ? $user['account_id'] : $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $role;
                    $_SESSION['branch_id'] = $needs_branch ? (int)$branch_id : (int)($user['assigned_branch'] ?? 0);
                    $_SESSION['school_name'] = $user['branch_name'] ?? 'Main Office';
                    $_SESSION['branch_type'] = $user['branch_type'] ?? 'Management';

                    if ($role === 'Parents') { 
                        $_SESSION['AdmissionNo'] = $user['AdmissionNo']; 
                    }

                    // 8. Determine Dashboard Route
                    $redirects = [
                        'Super User' => '/super_admin',
                        'Branch Admin' => '/admin',
                        'Faculty' => '/faculty_dashboard',
                        'Parents' => '/parents_dashboard'
                    ];
                    
                    $destination = $redirects[$role] ?? '/';

                    // 9. Send the explicit redirect URL back to login.js
                    echo json_encode([
                        'status' => 'success',
                        'redirect' => '/MBU_Website/Website/web/web/erp' . $destination
                    ]);
                    
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
            } finally {
                if (isset($conn)) { $conn->close(); }
            }
            exit; 
        }
    }
}
?>