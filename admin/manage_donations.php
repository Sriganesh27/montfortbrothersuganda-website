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

// 2. CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Handle Status Update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security validation failed.");
    }

    $donation_id = intval($_POST['id']);
    $new_status = $_POST['status'];
    
    // Safely capture amount_received. This allows "0" to be saved if fees take everything, but makes empty strings NULL.
    $amount_received = (isset($_POST['amount_received']) && trim($_POST['amount_received']) !== '') ? floatval($_POST['amount_received']) : null;

    try {
        $stmt = $pdo->prepare("UPDATE web_donations SET payment_status = ?, amount_received = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $amount_received, $donation_id])) { 
            $_SESSION['flash_msg'] = "Record #$donation_id updated successfully."; 
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Update failed: " . $e->getMessage();
    }

    $query_params = $_GET;
    header("Location: " . $_SERVER['PHP_SELF'] . ($query_params ? '?' . http_build_query($query_params) : ''));
    exit;
}

// 4. Filters & Search Logic
$selected_project = $_GET['project'] ?? '';
$search_query = $_GET['search'] ?? '';

try {
    $proj_stmt = $pdo->query("SELECT DISTINCT project_id FROM web_donations WHERE project_id IS NOT NULL ORDER BY project_id ASC");
    $project_list = $proj_stmt->fetchAll(PDO::FETCH_COLUMN);

    $query = "SELECT * FROM web_donations WHERE 1=1";
    $params = [];

    if ($selected_project !== '') {
        $query .= " AND project_id = ?";
        $params[] = $selected_project;
    }

    if ($search_query !== '') {
        $query .= " AND (transaction_id LIKE ? OR receipt_number LIKE ? OR full_name LIKE ?)";
        $search_param = "%$search_query%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $query .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle Flash Messages
$msg = $_SESSION['flash_msg'] ?? null;
unset($_SESSION['flash_msg']);

$error_msg = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Donations | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        /* A few helper styles to ensure the update form looks clean */
        .update-form { display: flex; align-items: center; gap: 5px; }
        .amount-input, .status-select { padding: 4px 6px; border: 1px solid #ccc; border-radius: 4px; }
        .amount-input { width: 90px; }
        .btn-update { padding: 4px 8px; border: none; background: #2563eb; color: white; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background: #1d4ed8; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header-flex">
            <h1>Donation Management</h1>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="controls-row">
            <div class="search-group">
                <input type="text" name="search" placeholder="Search Txn ID, Receipt, or Name..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn-search"><i class="fa fa-search"></i></button>
            </div>
            
            <div class="filter-group">
                <label>Project:</label>
                <select name="project" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    <?php foreach($project_list as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $selected_project == $p ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if($selected_project || $search_query): ?>
                <a href="?" class="btn-clear">Reset All</a>
            <?php endif; ?>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Transaction ID</th>
                        <th>Receipt</th>
                        <th>Donor</th>
                        <th>Pledged</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Received(UGX)</th>
                        <th>Utilised</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($donations as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><span class="txn-id"><?php echo htmlspecialchars($row['transaction_id'] ?: 'N/A'); ?></span></td>
                        <td><?php echo htmlspecialchars($row['receipt_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['currency'])." ".number_format($row['amount']); ?></strong></td>
                        <td><span class="project-tag"><?php echo htmlspecialchars($row['project_id']); ?></span></td>
                        
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($row['payment_status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?>
                            </span>
                        </td>

                        <td>
                            <?php if(!empty($row['amount_received']) || $row['amount_received'] === '0.00'): ?>
                                <strong style="color: #059669;"><?php echo number_format((float)$row['amount_received']); ?></strong>
                            <?php else: ?>
                                <span style="color: #6b7280; font-size: 0.9em;">Pending</span>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <form method="POST" class="update-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                
                                <input type="number" step="0.01" name="amount_received" class="amount-input" placeholder="Final Amt" value="<?php echo htmlspecialchars($row['amount_received'] ?? ''); ?>">
                                
                                <select name="status" class="status-select">
                                    <option value="pending" <?php if($row['payment_status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="success" <?php if($row['payment_status']=='success') echo 'selected'; ?>>Success</option>
                                    <option value="failed" <?php if($row['payment_status']=='failed') echo 'selected'; ?>>Failed</option>
                                </select>
                                
                                <button type="submit" class="btn-update" title="Save Changes"><i class="fa fa-save"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($donations)): ?>
                        <tr>
                            <td colspan="9" class="text-center mt-3">No transactions match your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>