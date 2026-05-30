<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/super_admin.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

// --- 1. Security Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Super User') {
    header("Location: login.php");
    exit;
}

// --- 2. Branch Context Switching Logic ---
if (isset($_GET['switch_branch'])) {
    $new_branch = (int)$_GET['switch_branch'];
    // Fetching branch_location from the database
    $stmt = $conn->prepare("SELECT branch_name, branch_type, branch_location FROM erp_branches WHERE branch_id = ?");
    $stmt->bind_param("i", $new_branch);
    $stmt->execute();
    $branch_data = $stmt->get_result()->fetch_assoc();
    
    if ($branch_data) {
        $_SESSION['branch_id'] = $new_branch;
        $_SESSION['school_name'] = $branch_data['branch_name'];
        $_SESSION['branch_type'] = $branch_data['branch_type'];
        $_SESSION['branch_location'] = $branch_data['branch_location']; // Store location in session
        header("Location: super_admin.php");
        exit;
    }
}

// --- 3. Data Queries ---
$total_students = 0;
// Global Count
$res_global = $conn->query("SELECT COUNT(AdmissionNo) AS count FROM erp_students");
if ($res_global) $total_students = $res_global->fetch_assoc()['count'];

// Branch-wise Count including branch_location
$branch_stats_res = $conn->query("
    SELECT b.branch_id, b.branch_name, b.branch_type, b.branch_location, COUNT(s.AdmissionNo) as student_count 
    FROM erp_branches b 
    LEFT JOIN erp_students s ON b.branch_id = s.branch_id 
    GROUP BY b.branch_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="../assets/Images/logo_MBSG_UG_8.webp">
  <title>Super Admin | Montfort ERP</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/styles/base.css">
  <link rel="stylesheet" href="assets/styles/student.css">
</head>
<body class="layout-wrapper light-theme">
  
  <?php include 'includes/navbar.php'; ?>

  <div class="layout">
    
    <?php include 'includes/sidebar.php'; ?>

    <div class="content" id="content">
      <div id="dashboard-module" class="module">
        
        <div class="super-header">
            <h1>Super User Control Panel</h1>
            <p class="subtitle">
                Current Management Context: 
                <strong>
                    <?php 
                        echo htmlspecialchars($_SESSION['school_name'] ?? 'Global View'); 
                        if(isset($_SESSION['branch_location'])) { echo " - " . htmlspecialchars($_SESSION['branch_location']); }
                    ?>
                </strong>
            </p>
            
            <div class="branch-selector">
                <span>Switch Management View:</span>
                <a href="?switch_branch=1" class="branch-btn <?php echo ($_SESSION['branch_id'] == 1) ? 'active' : 'inactive'; ?>">St. Kizito (Kyebando)</a>
                <a href="?switch_branch=2" class="branch-btn <?php echo ($_SESSION['branch_id'] == 2) ? 'active' : 'inactive'; ?>">St. Montfort (Mpala)</a>
                <a href="?switch_branch=3" class="branch-btn <?php echo ($_SESSION['branch_id'] == 3) ? 'active' : 'inactive'; ?>">Pere Achte (Isunga)</a>
            </div>
        </div>

        <h2 style="margin-top: 30px; margin-bottom: 15px; font-size: 1.1rem; color: var(--primary-color-dark);">Global Institutional Overview</h2>
        <div class="cards">
            <div class="card" style="border-top: 4px solid var(--primary-color-dark);">
                <i class="fa fa-globe-africa"></i>
                <h3>Global Students</h3>
                <p><?php echo number_format($total_students); ?> Enrolled</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--primary-color-dark);">
                <i class="fa fa-user-tie"></i>
                <h3>Global Faculty</h3>
                <p>- Total</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--primary-color-dark);">
                <i class="fa fa-bus"></i>
                <h3>Global Fleet</h3>
                <p>- Vehicles</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--primary-color-dark);">
                <i class="fa fa-building"></i>
                <h3>Total Campuses</h3>
                <p>3 Locations</p>
            </div>
        </div>

        <h2 style="margin-top: 40px; margin-bottom: 15px; font-size: 1.1rem; color: var(--primary-color-dark);">Branch Enrollment Breakdown</h2>
        <div class="cards">
          <?php 
          if ($branch_stats_res) {
              $branch_stats_res->data_seek(0); 
              while($row = $branch_stats_res->fetch_assoc()): 
          ?>
          <div class="card" style="border-top: 4px solid var(--primary-color);">
              <i class="fa fa-school"></i>
              <h3><?php echo htmlspecialchars($row['branch_name']); ?></h3>
              <p style="font-size: 0.85rem; color: var(--text-color-light); margin-bottom: 5px;">
                  <i class="fa fa-map-marker-alt" style="font-size: 0.8rem; margin: 0; display: inline;"></i> 
                  <?php echo htmlspecialchars($row['branch_location']); ?> 
                  <span style="opacity: 0.7;">| <?php echo htmlspecialchars($row['branch_type']); ?></span>
              </p>
              <p><?php echo number_format($row['student_count']); ?> Registered</p>
          </div>
          <?php endwhile; } ?>
        </div>

        <h2 style="margin-top: 40px; margin-bottom: 15px; font-size: 1.1rem; color: var(--primary-color-dark);">
            Resources for: 
            <?php 
                echo htmlspecialchars($_SESSION['school_name'] ?? 'Selected School'); 
                if(isset($_SESSION['branch_location'])) { echo " (" . htmlspecialchars($_SESSION['branch_location']) . ")"; }
            ?>
        </h2>
        <div class="cards">
          <div class="card"><i class="fa fa-computer"></i><h3>Computers</h3><p>-</p></div>
          <div class="card"><i class="fa fa-book"></i><h3>Library Books</h3><p>-</p></div>
          <div class="card"><i class="fa fa-futbol"></i><h3>Sports Kits</h3><p>-</p></div>
          <div class="card"><i class="fa fa-hand-holding-heart"></i><h3>Charity Projects</h3><p>-</p></div>
        </div>
      </div>
      <?php include 'modules/super_admin/school_management.php'; ?>
    <?php include 'modules/super_admin/credential_manager.php'; ?>
      <?php include 'modules/students/studentmanagement.php'; ?>
      
    </div> 
    
    <div class="overlay" id="overlay"></div>
    <div class="settings-panel" id="settings-panel">
      <div class="settings-header">
        <h3>Super Admin Settings</h3>
        <button id="settings-close-btn" class="close-btn">&times;</button>
      </div>
      <div class="settings-item theme-toggle">
        <strong>Theme</strong>
        <button id="theme-toggle-btn" class="btn btn-secondary">Switch Theme</button>
      </div>
    </div>
  </div>

  <script src="assets/scripts/base.js"></script>
  <script src="assets/scripts/student.js"></script>
</body>
</html>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
