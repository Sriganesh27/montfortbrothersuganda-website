<?php
// web/user_dashboard.php
require_once "includes/security.php";
require_once 'includes/language_manager.php'; // ADDED TRANSLATION MANAGER
require_once 'api/db_config.php'; 

// Security: Ensure only logged-in 'User' role can access
if (!isset($_SESSION['web_user_id']) || $_SESSION['web_role'] !== 'User') {
    header('Location: index.php'); 
    exit;
}

$user_id = $_SESSION['web_user_id'];

try {
    $stmt = $pdo->prepare("SELECT first_name, email, phone, created_at FROM web_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Dashboard Fetch Error: " . $e->getMessage());
    $user = null;
}

// Translated page title
$page_title = t('dashboard-title');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="assets/Images/logo_MBSG_UG_8.webp">
    <link rel="stylesheet" href="assets/styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container mt-5 pt-5 pb-5" style="min-height: 70vh;">
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 1.5rem;">
                            <?php echo strtoupper(substr($user['first_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <h4><?= t('dashboard-hello') ?> <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h4>
                        <p class="text-muted small"><?= t('dashboard-joined') ?> <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></p>
                        <hr>
                        <div class="text-start px-3">
                            <p class="mb-1 small text-muted"><?= t('dashboard-email') ?></p>
                            <p class="mb-3"><strong><?php echo htmlspecialchars($user['email'] ?? ''); ?></strong></p>
                            <p class="mb-1 small text-muted"><?= t('dashboard-phone') ?></p>
                            <p><strong><?php echo htmlspecialchars($user['phone'] ?? t('dashboard-not-provided')); ?></strong></p>
                        </div>
                        <a href="api/logout.php" class="btn btn-outline-danger btn-sm w-100 mt-2"><?= t('dashboard-logout') ?></a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h3 class="mb-4"><?= t('dashboard-nav-title') ?></h3>
                <div class="row g-3">
                    <div class="col-6 col-sm-4">
                        <a href="projects.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-0 shadow-sm py-3">
                                <i class="fas fa-tasks fa-2x text-primary mb-2"></i>
                                <h6 class="mb-0 text-dark"><?= t('dashboard-nav-projects') ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4">
                        <a href="support.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-0 shadow-sm py-3">
                                <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                                <h6 class="mb-0 text-dark"><?= t('dashboard-nav-support') ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4">
                        <a href="about.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-0 shadow-sm py-3">
                                <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                <h6 class="mb-0 text-dark"><?= t('dashboard-nav-about') ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4">
                        <a href="location.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-0 shadow-sm py-3">
                                <i class="fas fa-map-marker-alt fa-2x text-success mb-2"></i>
                                <h6 class="mb-0 text-dark"><?= t('dashboard-nav-locations') ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-sm-4">
                        <a href="unite.php" class="text-decoration-none">
                            <div class="card h-100 text-center border-0 shadow-sm py-3">
                                <i class="fas fa-users fa-2x text-warning mb-2"></i>
                                <h6 class="mb-0 text-dark"><?= t('dashboard-nav-connect') ?></h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer-diag.php'; ?>
</body>
</html>