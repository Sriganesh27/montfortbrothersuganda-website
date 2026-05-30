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

// Updated to PDO
try {
    $volunteers = $pdo->query("SELECT * FROM web_volunteer_form ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $volunteers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteers | Admin</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <h1>Volunteer Applications</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Info</th>
                        <th>Skill/Qualification</th>
                        <th>Experience</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($volunteers && count($volunteers) > 0): ?>
                        <?php foreach ($volunteers as $row): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?><br><small><?php echo htmlspecialchars($row['phone_number']); ?></small></td>
                                <td><?php echo ucfirst(htmlspecialchars($row['skill'])); ?><br><small><?php echo htmlspecialchars($row['qualification']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['experience']); ?><br><small>Years: <?php echo htmlspecialchars($row['exp_years']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td>
                                    <button class="action-btn btn-delete" data-id="<?php echo $row['id']; ?>" data-table="web_volunteer_form">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No applications found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="assets/js/admin_script.js"></script>
</body>
</html>