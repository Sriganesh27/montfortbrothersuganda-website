<?php
session_start();

// 1. Verify Authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); 
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Include the PDO configuration
require_once '../api/db_config.php';

// Initialize defaults
$user_count = 0;
$donation_count = 0;
$message_count = 0;
$volunteer_count = 0;
$revenue = 0;
$total_spent = 0;
$total_allocated = 0;
$totalIntercessors = 0;
$totalPrayers = 0;

// Array to catch and display specific errors for debugging (optional)
$db_errors = []; 

// 3. Run queries individually so one failure doesn't break everything
try {
    $user_count = $pdo->query("SELECT COUNT(*) FROM web_users")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Users: " . $e->getMessage();
}

try {
    $donation_count = $pdo->query("SELECT COUNT(*) FROM web_donations WHERE payment_status = 'success'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Donations: " . $e->getMessage();
}

try {
    // CORRECTED: Now using web_contact_inquiries
    $message_count = $pdo->query("SELECT COUNT(*) FROM web_contact_inquiries")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Inquiries: " . $e->getMessage();
}

try {
    // CORRECTED: Using web_volunteer_form
    $volunteer_count = $pdo->query("SELECT COUNT(*) FROM web_volunteer_form")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Volunteers: " . $e->getMessage();
}

try {
    $revenue = $pdo->query("SELECT SUM(amount_received) FROM web_donations WHERE payment_status = 'success'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Revenue: " . $e->getMessage();
}

try {
    $total_spent = $pdo->query("SELECT SUM(amount_spent) FROM web_donations WHERE payment_status = 'success'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Spent: " . $e->getMessage();
}

try {
    // Check if table exists before querying
    $tableExists = $pdo->query("SHOW TABLES LIKE 'web_project_expenses'")->rowCount() > 0;
    if ($tableExists) {
        $total_allocated = $pdo->query("SELECT SUM(amount_allocated) FROM web_project_expenses")->fetchColumn() ?: 0;
    }
} catch (PDOException $e) {
    $db_errors[] = "Allocated: " . $e->getMessage();
}

try {
    $totalIntercessors = $pdo->query("SELECT COUNT(*) FROM web_prayer_users")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Intercessors: " . $e->getMessage();
}

try {
    $totalPrayers = $pdo->query("SELECT SUM(total_prayers) FROM web_prayer_stats")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $db_errors[] = "Prayers: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Montfort Brothers</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2>Welcome to Admin Dashboard</h2>
            <div class="user-info">
                <span>Admin</span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
        
        <?php if (!empty($db_errors)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <strong>Database Warnings (For Admin Eyes Only):</strong>
                <ul style="margin: 5px 0 0 20px;">
                    <?php foreach ($db_errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="dashboard-cards">
            <div class="card">
                <h4><i class="fas fa-users" style="color: var(--text-color2); margin-right: 8px;"></i> Registered Users</h4>
                <h2><?php echo number_format($user_count); ?></h2>
            </div>
            
            <div class="card">
                <h4><i class="fas fa-hand-holding-usd" style="color: #27ae60; margin-right: 8px;"></i> Total UGX Received</h4>
                <h2 style="color: #27ae60;"><?php echo number_format($revenue); ?></h2>
            </div>

            <div class="card">
                <h4><i class="fas fa-envelope-open-text" style="color: var(--text-color2); margin-right: 8px;"></i> Total Inquiries</h4>
                <h2><?php echo number_format($message_count); ?></h2>
            </div>
            
            <div class="card">
                <h4><i class="fas fa-hands-helping" style="color: var(--text-color2); margin-right: 8px;"></i> Volunteer Applications</h4>
                <h2><?php echo number_format($volunteer_count); ?></h2>
            </div>
            
            <div class="card" style="border-left: 4px solid #007bff;">
                <h4><i class="fas fa-praying-hands" style="color: #007bff; margin-right: 8px;"></i> Total Intercessors</h4>
                <h2><?php echo number_format($totalIntercessors); ?> <span style="font-size:0.9rem; font-weight:normal; color:#666;">Users</span></h2>
            </div>
            
            <div class="card" style="border-left: 4px solid #28a745;">
                <h4><i class="fas fa-globe" style="color: #28a745; margin-right: 8px;"></i> Global Prayers</h4>
                <h2><?php echo number_format($totalPrayers); ?> <span style="font-size:0.9rem; font-weight:normal; color:#666;">Amens</span></h2>
            </div>
        </div>
    </div>

</body>
</html>