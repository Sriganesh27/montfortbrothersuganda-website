<?php
// erp/app/views/admin/index.php

// 1. Start Session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Config using absolute path
require_once __DIR__ . '/../../../config/config.php';

// 3. Security Check: Redirect to the MVC login route if not authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /MBU_Website/Website/web/web/erp/login");
    exit();
}

// 4. Get Branch Context from Session
$branch_id = $_SESSION['branch_id'] ?? null;
$school_name = $_SESSION['school_name'] ?? 'Montfort School';
$total_students = 0;

// 5. Database Connection (Using constants from config.php)
$conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME); 

if ($conn) {
    // 6. Branch-Aware Count Query
    if ($_SESSION['role'] === 'Super User') {
        $sql = "SELECT COUNT(AdmissionNo) AS count FROM erp_students";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT COUNT(AdmissionNo) AS count FROM erp_students WHERE branch_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $branch_id);
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $total_students = $row['count'];
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="<?php echo BASE_URL; ?>/assets/Images/logo_MBSG_UG_8.webp">
  <title><?php echo htmlspecialchars($school_name); ?> - Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/styles/base.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/styles/student.css">
</head>
<body class="layout-wrapper light-theme">
  
  <?php 
  // Safely include Navbar
  $navbar_path = __DIR__ . '/../includes/navbar/index.php';
  if (file_exists($navbar_path)) { include $navbar_path; } 
  ?>

  <div class="layout">
    
    <?php 
    // Safely include Sidebar
    $sidebar_path = __DIR__ . '/../includes/sidebar/index.php';
    if (file_exists($sidebar_path)) { include $sidebar_path; } 
    ?>

    <div class="content" id="content">
      <div id="dashboard-module" class="module">
        <div class="dashboard-header" style="margin-bottom: 25px;">
            <h2 style="color: var(--primary-color);">Welcome to <?php echo htmlspecialchars($school_name); ?></h2>
            <p style="color: #666;">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
        </div>

        <div class="cards">
          <div class="card">
              <div class="card-icon"><i class="fa fa-users"></i></div>
              <div class="card-info">
                  <h3>Total Students</h3>
                  <p class="stat-number"><?php echo number_format($total_students); ?></p>
                  <p class="stat-label">Currently Enrolled</p>
              </div>
          </div>
          
          <div class="card"><i class="fa fa-user-tie"></i><h3>Faculty</h3><p>-</p></div>
          <div class="card"><i class="fa fa-bus"></i><h3>Transport</h3><p>-</p></div>
          <div class="card"><i class="fa fa-book"></i><h3>Library</h3><p>-</p></div>
          <div class="card"><i class="fa fa-computer"></i><h3>IT Lab</h3><p>-</p></div>
          <div class="card"><i class="fa fa-futbol"></i><h3>Sports</h3><p>-</p></div>
          <div class="card"><i class="fa fa-building"></i><h3>Buildings</h3><p>-</p></div>
          <div class="card"><i class="fa fa-hand-holding-heart"></i><h3>Charity</h3><p>-</p></div>
        </div>
      </div>
      
      <?php 
      // Safely include Student Management
      $student_mgmt_path = __DIR__ . '/../modules/students/studentmanagement/index.php';
      if (file_exists($student_mgmt_path)) { include $student_mgmt_path; } 
      ?>
      
      <div id="manage_applications-module" class="module" style="display:none;">
        <?php 
        // Safely include Manage Applications
        $manage_apps_path = __DIR__ . '/../modules/manage_applications/manage_applications/index.php';
        if (file_exists($manage_apps_path)) { include $manage_apps_path; } 
        ?>
      </div>
      
    </div> 
    
    <div class="overlay" id="overlay"></div>
    
    <div class="settings-panel" id="settings-panel">
      <div class="settings-header">
        <h3>Settings</h3>
        <button id="settings-close-btn" class="close-btn" aria-label="close settings">&times;</button>
      </div>
      <div class="settings-item theme-toggle">
        <strong>Theme</strong>
        <button id="theme-toggle-btn" class="btn btn-secondary">Switch to Dark</button>
      </div>
      <div class="settings-item">
        <strong>Language</strong>
        <span>English</span>
      </div>
      <div class="settings-item">
        <strong>Notifications</strong>
        <span>Enabled</span>
      </div>
    </div>
  </div>

  <script src="<?php echo BASE_URL; ?>/assets/scripts/base.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/scripts/student.js"></script>
</body>
</html>