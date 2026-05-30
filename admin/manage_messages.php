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
require_once '../api/db_config.php';

// Updated to PDO
try {
    $inquiries = $pdo->query("SELECT * FROM web_contact_inquiries ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $inquiries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Inquiries | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <h1>Contact Inquiries</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Info</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inquiries && count($inquiries) > 0): ?>
                        <?php foreach ($inquiries as $row): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?><br><small><?php echo htmlspecialchars($row['phone_number']); ?></small></td>
                                <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
<button class="action-btn btn-delete" data-id="<?php echo $row['id']; ?>" data-table="web_contact_inquiries">Delete</button>                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No inquiries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="assets/js/admin_script.js"></script>
</body>
</html>