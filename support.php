<?php
// 1. Start Session securely by loading our security config FIRST
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/language_manager.php';
// Include database configuration
require_once __DIR__ . '/api/db_config.php';

// --- FETCH PROJECT DONATION DATA (Matching index.php) ---
function fetchProjectDonationsSummary($pdo, $project_id) {
    $total_ugx = 0;
    if (!isset($pdo)) return 0;
    try {
        $stmt = $pdo->prepare("SELECT amount_received FROM web_donations WHERE project_id = ? AND payment_status = 'success' AND amount_received IS NOT NULL AND amount_received > 0");
        $stmt->execute([$project_id]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_ugx += $row['amount_received'];
        }
    } catch (PDOException $e) { }
    return $total_ugx;
}

// --- FETCH DISTRIBUTED FUNDS FOR PROGRESS BARS (UPDATED TO EXCLUDE RESERVE) ---
function fetchProjectDistributionsSummary($pdo, $project_id) {
    $total_distributed = 0;
    if (!isset($pdo)) return 0;
    try {
        // Exclude BOTH Students Benefited and Sustainable Reserve
        $stmt = $pdo->prepare("
            SELECT SUM(d.amount_allocated) as total_allocated 
            FROM web_donor_distributions d
            JOIN web_donations dn ON d.donation_id = dn.id
            WHERE dn.project_id = ? 
            AND dn.payment_status = 'success' 
            AND d.category_name NOT IN ('Students Benefited', 'Sustainable Reserve')
        ");
        $stmt->execute([$project_id]);
        $total_distributed = $stmt->fetchColumn();
    } catch (PDOException $e) { }
    return $total_distributed ? $total_distributed : 0;
}

// Get Totals (Received)
$ssp_total = fetchProjectDonationsSummary($pdo, 'SSP001');
$idp_total = fetchProjectDonationsSummary($pdo, 'IDP001');
$cep_total = fetchProjectDonationsSummary($pdo, 'CEP001');

// Get Totals (Spent/Distributed)
$ssp_spent = fetchProjectDistributionsSummary($pdo, 'SSP001');
$idp_spent = fetchProjectDistributionsSummary($pdo, 'IDP001');
$cep_spent = fetchProjectDistributionsSummary($pdo, 'CEP001');

// Helper to compute percentages
function calcPercentSummary($total, $target) {
    return ($target > 0) ? min(100, round(($total / $target) * 100)) : 0;
}

// Calculate Received Percentages based on targets
$ssp_perc = calcPercentSummary($ssp_total, 50000000);
$idp_perc = calcPercentSummary($idp_total, 175000000);
$cep_perc = calcPercentSummary($cep_total, 22000000);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= t('site-title') ?>">
    <link rel="icon" href="assets/Images/logo_MBSG_UG_8.webp">
    <title><?= t('site-title') ?></title>
    <link rel="stylesheet" href="assets/styles/refstyle.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <section class="sub-pages">
            <div class="hero_overlap">
                <img src="assets/Images/suppurot-sub.webp" alt="image" class="support-hero-img">
            </div>
            <div class="container sub_hero_content">
                <h1 class="title animate-up"><?= t('support-hero-title') ?></h1>
                <p class="sub-title animate-up"><?= t('support-hero-subtitle') ?></p>
            </div>
        </section>

        <section class="support-container">
            <h2 class="title"><?= t('support-section-title') ?></h2>
            <div class="support-cards-grid">
                
                <div class="support-card">
                    <div class="support-image project-image-wrapper">
                        <img src="assets/Images/support-student.webp" alt="<?= t('support-card1-title') ?>">
                        <span class="project-badge-overlay"><?= t('support-badge-id') ?>SSP001</span>
                    </div>
                    <div class="support-body">
                        <h3 class="project-card-title"><?= t('support-card1-title') ?></h3>
                        <p class="project-name-text"><?= t('support-card1-project') ?></p>
                        <p><?= t('support-card1-desc') ?></p>
                        
                        <div class="mini-progress-wrapper" style="margin-top: 15px;">
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($ssp_total); ?> UGX</strong></span>
                                <span><?= $ssp_perc ?>%</span>
                            </div>
                            <div class="mini-progress-container" style="margin-bottom: 10px;">
                                <div class="mini-progress-fill received-fill" style="width: <?php echo $ssp_perc; ?>%;"></div>
                            </div>
                            
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-spent') ?> <strong><?php echo number_format($ssp_spent); ?> UGX</strong></span>
                                <span><?php echo ($ssp_total > 0) ? calcPercentSummary($ssp_spent, $ssp_total) : '0'; ?>%</span>
                            </div>
                            <div class="mini-progress-container">
                                <div class="mini-progress-fill spent-fill" style="width: <?php echo ($ssp_total > 0) ? calcPercentSummary($ssp_spent, $ssp_total) : '0'; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="support-footer">
                        <button class="support-button open-donate-window" 
                                data-purpose="<?= t('support-card1-title') ?>"
                                data-project-id="SSP001"
                                data-project-name="<?= t('support-card1-project') ?>">
                            <?= t('support-btn-donate') ?>
                        </button>
                        <a href="projects.php?project=SSP001" class="learn-more-link"><?= t('support-btn-learn-more') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="support-card">
                    <div class="support-image project-image-wrapper">
                        <img src="assets/Images/support-infrastructure.webp" alt="<?= t('support-card2-title') ?>">
                        <span class="project-badge-overlay"><?= t('support-badge-id') ?>IDP001</span>
                    </div>
                    <div class="support-body">
                        <h3 class="project-card-title"><?= t('support-card2-title') ?></h3>
                        <p class="project-name-text"><?= t('support-card2-project') ?></p>
                        <p><?= t('support-card2-desc') ?></p>
                        
                        <div class="mini-progress-wrapper" style="margin-top: 15px;">
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($idp_total); ?> UGX</strong></span>
                                <span><?= $idp_perc ?>%</span>
                            </div>
                            <div class="mini-progress-container" style="margin-bottom: 10px;">
                                <div class="mini-progress-fill received-fill" style="width: <?php echo $idp_perc; ?>%;"></div>
                            </div>
                            
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-spent') ?> <strong><?php echo number_format($idp_spent); ?> UGX</strong></span>
                                <span><?php echo ($idp_total > 0) ? calcPercentSummary($idp_spent, $idp_total) : '0'; ?>%</span>
                            </div>
                            <div class="mini-progress-container">
                                <div class="mini-progress-fill spent-fill" style="width: <?php echo ($idp_total > 0) ? calcPercentSummary($idp_spent, $idp_total) : '0'; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="support-footer">
                        <button class="support-button open-donate-window" 
                                data-purpose="<?= t('support-card2-title') ?>"
                                data-project-id="IDP001"
                                data-project-name="<?= t('support-card2-project') ?>">
                            <?= t('support-btn-donate') ?>
                        </button>
                        <a href="projects.php?project=IDP001" class="learn-more-link"><?= t('support-btn-learn-more') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="support-card">
                    <div class="support-image project-image-wrapper">
                        <img src="assets/Images/support-community.webp" alt="<?= t('support-card3-title') ?>">
                        <span class="project-badge-overlay"><?= t('support-badge-id') ?>CEP001</span>
                    </div>
                    <div class="support-body">
                        <h3 class="project-card-title"><?= t('support-card3-title') ?></h3>
                        <p class="project-name-text"><?= t('support-card3-project') ?></p>
                        <p><?= t('support-card3-desc') ?></p>
                        
                        <div class="mini-progress-wrapper" style="margin-top: 15px;">
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($cep_total); ?> UGX</strong></span>
                                <span><?= $cep_perc ?>%</span>
                            </div>
                            <div class="mini-progress-container" style="margin-bottom: 10px;">
                                <div class="mini-progress-fill received-fill" style="width: <?php echo $cep_perc; ?>%;"></div>
                            </div>
                            
                            <div class="mini-progress-labels">
                                <span><?= t('proj-bar-spent') ?> <strong><?php echo number_format($cep_spent); ?> UGX</strong></span>
                                <span><?php echo ($cep_total > 0) ? calcPercentSummary($cep_spent, $cep_total) : '0'; ?>%</span>
                            </div>
                            <div class="mini-progress-container">
                                <div class="mini-progress-fill spent-fill" style="width: <?php echo ($cep_total > 0) ? calcPercentSummary($cep_spent, $cep_total) : '0'; ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="support-footer">
                        <button class="support-button open-donate-window" 
                                data-purpose="<?= t('support-card3-title') ?>"
                                data-project-id="CEP001"
                                data-project-name="<?= t('support-card3-project') ?>">
                            <?= t('support-btn-donate') ?>
                        </button>
                        <a href="projects.php?project=CEP001" class="learn-more-link"><?= t('support-btn-learn-more') ?> <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
            </div>
        </section>

        <section class="support-hands">
            <div class="support-hand-container">
                <div class="icon-container scroll-animate">
                    <i class="fa fa-hands-helping"></i>
                </div>
                <div class="text-box scroll-animate">
                    <h2><span><?= t('support-hands-1') ?></span> <?= t('support-hands-2') ?></h2>
                </div>
            </div>
        </section>
        
    </main>
    <?php include 'includes/footer-diag.php'; ?>
    <script src="assets/script/script.js"></script>
    <script src="assets/script/donation.js"></script>
</body>
</html>