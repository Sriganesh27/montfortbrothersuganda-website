<?php
session_start();
// Security: Kick out non-admins back to the main site home
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); 
    exit;
}

// ⭐ 1. ADD THIS: Generate CSRF token for this page
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../api/db_config.php';

$msg = '';

// ==================== HANDLE ADD NEW USER ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $username = $email; // Use email as username

    // Check if email already exists using PDO
    $check = $pdo->prepare("SELECT id FROM web_users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $msg = "<div style='color:red; margin-bottom:15px;'>Error: That email is already registered.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user using PDO
        $insert = $pdo->prepare("INSERT INTO web_users (first_name, email, username, password, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        
        if ($insert->execute([$first_name, $email, $username, $hashed_password, $role])) {
            $msg = "<div style='color:green; margin-bottom:15px;'>Success: New $role added!</div>";
        } else {
            $msg = "<div style='color:red; margin-bottom:15px;'>Error adding user.</div>";
        }
    }
}

// Fetch all users using PDO
$query = "SELECT id, first_name, last_name, email, role, created_at FROM web_users ORDER BY id DESC";
$users = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Users | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        .add-user-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .add-user-box input, .add-user-box select { padding: 8px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="header-flex">
            <h1>Manage Users & Admins</h1>
        </div>

        <?php echo $msg; ?>

        <div class="add-user-box">
            <h3 style="margin-top:0; font-size:1rem; color:#333;">Quick Add User / Admin</h3>
            <form method="POST" action="">
                <input type="hidden" name="add_user" value="1">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="password" placeholder="Temporary Password" required>
                <select name="role">
                    <option value="User">Standard User</option>
                    <option value="Admin">Administrator</option>
                </select>
                <button type="submit" class="action-btn btn-edit" style="padding: 9px 15px;"><i class="fas fa-plus"></i> Add Account</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && count($users) > 0): ?>
                        <?php foreach ($users as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                        if(isset($row['role']) && $row['role'] === 'Admin') {
                                            echo '<strong style="color: #28a745;">Admin</strong>';
                                        } else {
                                            echo htmlspecialchars($row['role'] ?? 'User'); 
                                        }
                                    ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <button class="action-btn btn-delete" data-id="<?php echo $row['id']; ?>" data-table="web_users">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<script src="assets/js/admin_script.js"></script>
</body>
</html>