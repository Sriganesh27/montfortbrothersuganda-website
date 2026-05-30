<?php
// admin/fund_distribution.php
session_start();

// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); 
    exit;
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../api/db_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Fund Distribution | Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/styles/style.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="assets/css/fund_distribution.css"> 
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-flex">
            <h1><i class="fa-solid fa-money-bill-transfer" style="color: var(--primary-color); margin-right: 10px;"></i> Fund Distribution</h1>
        </div>

        <div class="controls-row">
            <div class="filter-group">
                <label for="projectSelect">Select Project:</label>
                <select id="projectSelect">
                    <option value="">-- Choose a Project --</option>
                    <option value="SSP001">SSP001 - Educate 100 Students</option>
                    <option value="CEP001">CEP001 - Child Health (Eggs)</option>
                    <option value="IDP001">IDP001 - Primary School Hostel</option>
                </select>
            </div>
            <div id="loadingSpinner" class="loading-spinner" style="display:none;">
                <i class="fas fa-spinner fa-spin"></i> Loading donors...
            </div>
        </div>

        <div class="table-container">
            <table id="distributionTable">
                <thead id="tableHead">
                    <tr>
                        <th style="padding: 20px;">Please select a project above to view donors.</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Donor rows dynamically injected here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Global Admin Scripts (For Toast Notifications) -->
    <script src="assets/js/admin_script.js"></script>
    <script src="assets/js/distribution.js"></script>

</body>
</html>