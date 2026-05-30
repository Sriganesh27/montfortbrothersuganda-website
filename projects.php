<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/includes/language_manager.php';

// Database connection
$config_path = 'api/db_config.php'; 
if (!file_exists($config_path)) {
    die("Database configuration file not found at: " . realpath(dirname(__FILE__)) . '/' . $config_path);
}
require_once $config_path;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$default_project = 'SSP001';
$active_project = isset($_GET['project']) ? strtoupper(trim($_GET['project'])) : $default_project;
if (!in_array($active_project, ['SSP001', 'CEP001', 'IDP001'])) {
    $active_project = $default_project;
}

$conversion_rates = [
    'USD' => 3700,
    'EURO_GBP' => 4300,
    'UGX' => 1
];

function convertToUGX($amount, $currency, $rates) {
    return isset($rates[$currency]) ? $amount * $rates[$currency] : $amount;
}

function fetchProjectDonations($pdo, $project_id, $conversion_rates) {
    $donations = [];
    $total_ugx = 0;
    $total_student_terms = 0;
    $error = null;

    if (!isset($pdo)) {
        $error = "Database connection not available.";
        return [$donations, $total_ugx, 0, $error];
    }

    try {
        // UPDATED: Added terms_benefited to the SQL query
        $stmt = $pdo->prepare("SELECT receipt_number, full_name, is_anonymous, amount_received, currency, students_benefited, terms_benefited, created_at 
                               FROM web_donations 
                               WHERE project_id = ? 
                               AND payment_status = 'success' 
                               AND amount_received IS NOT NULL 
                               AND amount_received > 0
                               ORDER BY created_at ASC");
        $stmt->execute([$project_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $amount_ugx = $row['amount_received']; 
            $row['amount_ugx'] = $amount_ugx;
            $donations[] = $row;
            
            $total_ugx += $amount_ugx;
            
            // NEW LOGIC: Calculate students based on terms funded
            $students = (int)$row['students_benefited'];
            $terms = isset($row['terms_benefited']) ? (int)$row['terms_benefited'] : 0;
            
            // Fallback: If students are entered but terms are 0, assume at least 1 term 
            if ($students > 0 && $terms === 0) {
                $terms = 1;
            }
            
            $total_student_terms += ($students * $terms);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }

    // NEW LOGIC: Divide total student-terms by 3 to get fully funded students
    $effective_students = round($total_student_terms / 3, 1);
    
    // Clean up formatting (e.g. show 6 instead of 6.0)
    if (floor($effective_students) == $effective_students) {
        $effective_students = (int)$effective_students;
    }

    return [$donations, $total_ugx, $effective_students, $error];
}

// Fetch Dynamic Distributions for the Financial Tables and Progress Bars
function fetchProjectDistributions($pdo, $project_id) {
    $distributions = [];
    $total_allocated = 0; // Everything including reserve (for table display)
    $actual_spent = 0;    // Excludes reserve (for progress bar logic)
    
    if (!isset($pdo)) return [$distributions, $total_allocated, $actual_spent];

    try {
        // Sum all allocations grouped by category, excluding the non-monetary "Students Benefited" count
        $stmt = $pdo->prepare("
            SELECT d.category_name, SUM(d.amount_allocated) as total_allocated 
            FROM web_donor_distributions d
            JOIN web_donations dn ON d.donation_id = dn.id
            WHERE dn.project_id = ? 
            AND dn.payment_status = 'success' 
            AND d.category_name != 'Students Benefited'
            GROUP BY d.category_name
        ");
        $stmt->execute([$project_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $distributions[] = $row;
            $total_allocated += $row['total_allocated'];
            
            // Exclude Sustainable Reserve from actual spent calculation
            if ($row['category_name'] !== 'Sustainable Reserve') {
                $actual_spent += $row['total_allocated'];
            }
        }
    } catch (PDOException $e) {
        error_log("Distribution Fetch Error: " . $e->getMessage());
    }
    
    return [$distributions, $total_allocated, $actual_spent];
}

function calcPercent($total, $target) {
    return ($target > 0) ? min(100, round(($total / $target) * 100)) : 0;
}

// =======================
// SSP001 Logic
// =======================
list($ssp_donations, $ssp_total_ugx, $ssp_students_helped, $ssp_error) = fetchProjectDonations($pdo, 'SSP001', $conversion_rates);
list($ssp_distributions, $ssp_total_allocated, $ssp_actual_spent) = fetchProjectDistributions($pdo, 'SSP001');

$ssp_target_ugx = 50000000;
$ssp_percent = calcPercent($ssp_total_ugx, $ssp_target_ugx);
$ssp_students_target = 100;
$ssp_students_percent = calcPercent($ssp_students_helped, $ssp_students_target);
$ssp_spent_percent = calcPercent($ssp_actual_spent, $ssp_total_ugx); // Percent spent relative to what is received

// =======================
// CEP001 Logic
// =======================
list($cep_donations, $cep_total_ugx, $cep_students_helped, $cep_error) = fetchProjectDonations($pdo, 'CEP001', $conversion_rates);
list($cep_distributions, $cep_total_allocated, $cep_actual_spent) = fetchProjectDistributions($pdo, 'CEP001');

$cep_target_ugx = 22000000;
$cep_percent = calcPercent($cep_total_ugx, $cep_target_ugx);
$cep_students_target = 1000;
$cep_students_percent = calcPercent($cep_students_helped, $cep_students_target);
$cep_spent_percent = calcPercent($cep_actual_spent, $cep_total_ugx);

// =======================
// IDP001 Logic
// =======================
list($idp_donations, $idp_total_ugx, $idp_students_helped, $idp_error) = fetchProjectDonations($pdo, 'IDP001', $conversion_rates);
list($idp_distributions, $idp_total_allocated, $idp_actual_spent) = fetchProjectDistributions($pdo, 'IDP001');

$idp_target_ugx = 175000000; 
$idp_startup_threshold = 50000000; 
$idp_percent = calcPercent($idp_total_ugx, $idp_target_ugx);

// Determine Work Status
if ($idp_total_ugx >= $idp_startup_threshold) {
    $idp_work_status = t('proj-status-progress');
} else {
    $idp_work_status = t('proj-idp-bar-startup'); 
}

$beneficiary_schools = []; 
?>
<?php
require_once "includes/security.php";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <div class="back-link-container">
            <a href="support.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= t('proj-back-link') ?></a>
        </div>

        <section class="projectSection <?php echo ($active_project == 'SSP001') ? 'active' : ''; ?>" id="SSP001-section">
            <div class="projectContainer" id="SSP001">
                <header class="projectTitle">
                    <h4 class="contributeTitle"><?= t('proj-ssp-tag') ?></h4>
                    <span class="badge"><?= t('proj-id-label') ?> SSP001</span>
                    <h4><?= t('proj-ssp-title') ?></h4>
                </header>
                <div class="projectcontent">
                    <div class="projectOverview">
                        <div class="image">
                            <img src="assets/Images/support-student.webp" alt="Support Student">
                        </div>
                        <div class="projectDesc">
                            <p><?= t('proj-ssp-desc1') ?></p>
                            <span><?= t('proj-ssp-desc2') ?></span>
                            <div class="mission-box">
                                <p><strong><?= t('proj-ssp-mission-prefix') ?></strong> <?= t('proj-ssp-mission-text1') ?> <strong><?= t('proj-ssp-mission-text2') ?></strong> <?= t('proj-ssp-mission-text3') ?></p>
                            </div>
                            <button class="donate-btn open-donate-window" 
                                    data-purpose="<?= t('proj-ssp-tag') ?>"
                                    data-project-id="SSP001"
                                    data-project-name="<?= t('proj-ssp-title') ?>">
                                <?= t('proj-btn-contribute') ?>
                            </button>
                        </div>
                        <div class="targetBar horizontal-bars">
                            <div class="progress-wrapper" data-target="<?php echo $ssp_target_ugx; ?>">
                                <h5 class="bar-title"><?= t('proj-bar-funding-title') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($ssp_total_ugx); ?> UGX</strong></span>
                                    <span><?= t('proj-bar-target') ?> <strong><?php echo number_format($ssp_target_ugx); ?> UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill received-fill" style="width: <?php echo $ssp_percent; ?>%;">
                                        <span class="progress-text"><?php echo $ssp_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress-wrapper">
                                <h5 class="bar-title"><?= t('proj-bar-student-impact') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-students-helped') ?> <strong><?php echo $ssp_students_helped; ?></strong></span>
                                    <span><?= t('proj-bar-target') ?> <strong><?php echo $ssp_students_target; ?> <?= t('proj-bar-students-target') ?></strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill students-fill" style="width: <?php echo $ssp_students_percent; ?>%;">
                                        <span class="progress-text"><?php echo $ssp_students_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="progress-wrapper">
                                <h5 class="bar-title"><?= t('proj-bar-transforming') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-spent') ?> <strong class="js-spent-amount"><?php echo number_format($ssp_actual_spent); ?> UGX</strong></span>
                                    <span><?= t('proj-bar-allocated') ?> <strong class="js-helped-amount"><?php echo number_format($ssp_total_ugx); ?> UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill spent-fill" style="width: <?php echo $ssp_spent_percent; ?>%;">
                                        <span class="progress-text"><?php echo $ssp_spent_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="projectDetails">
                        <div class="projectdata">
                            <h3><?= t('proj-desc-title') ?></h3>
                            <div class="projecttext">
                                <p><?= t('proj-ssp-desc-sub') ?></p>
                            </div>
                            <div class="content-flex-column">
                                <div class="fund-card">
    <h4><?= t('proj-ssp-scholarship-title') ?></h4>
    <p style="margin-bottom: 10px; color: var(--primary-blue);"><strong><?= t('proj-ssp-objective') ?></strong></p>
    <p><?= t('proj-ssp-scholarship-desc') ?></p>
    <table class="currency-table">
        <thead>
            <tr><th><?= t('proj-ssp-th-level') ?></th><th>UGX</th><th>USD</th><th>EUR</th></tr>
        </thead>
        <tbody>
            <tr><td><?= t('proj-ssp-lvl-primary') ?></td><td>300,000</td><td>$100</td><td>€90</td></tr>
            <tr><td><?= t('proj-ssp-lvl-secondary') ?></td><td>450,000</td><td>$150</td><td>€135</td></tr>
            <tr><td><?= t('proj-ssp-lvl-senior') ?></td><td>600,000</td><td>$200</td><td>€180</td></tr>
            <tr><td><?= t('proj-ssp-lvl-tertiary') ?></td><td>750,000</td><td>$250</td><td>€225</td></tr>
        </tbody>
    </table>
</div>
                                <div class="fund-card">
                                    <h4><?= t('proj-budget-title') ?></h4>
                                    <table class="currency-table">
                                        <thead>
                                            <tr><th><?= t('proj-ssp-th-level') ?></th><th>UGX</th><th>USD</th><th>EUR</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><?= t('proj-ssp-lvl-primary') ?></td><td>10,000,000</td><td>$2700</td><td>€2500</td></tr>
                                            <tr><td><?= t('proj-ssp-lvl-secondary') ?></td><td>12,000,000</td><td>$3250</td><td>€3000</td></tr>
                                            <tr><td><?= t('proj-ssp-lvl-senior') ?></td><td>13,000,000</td><td>$3500</td><td>€3250</td></tr>
                                            <tr><td><?= t('proj-ssp-lvl-tertiary') ?></td><td>15,000,000</td><td>$4050</td><td>€3750</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="impact-card">
                                    <h4><?= t('proj-impact-title') ?></h4>
                                    <ul class="impact-stats">
                                        <li class="stat-item"><strong>90%</strong> <span><?= t('proj-ssp-impact1') ?></span></li>
                                        <li class="stat-item"><strong>1%</strong> <span><?= t('proj-ssp-impact2') ?></span></li>
                                        <li class="stat-item"><strong>9%</strong> <span><?= t('proj-ssp-impact3') ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="projectreport">
                            <h3><?= t('proj-fin-report-title') ?></h3>
                            
                            <div class="projecttable fundman">
                                <div class="totalFund">
                                    <p><?= t('proj-fin-total-received') ?></p>
                                    <span><?php echo number_format($ssp_total_ugx); ?> UGX</span>
                                </div>
                                <table>
                                    <thead>
                                        <tr><th colspan="2"><?= t('proj-fin-dist-title') ?></th></tr>
                                        <tr>
                                            <th><?= t('proj-fin-th-category') ?></th>
                                            <th style="text-align: right;"><?= t('proj-fin-th-spent') ?> (UGX)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($ssp_distributions)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted" style="padding: 20px;">
                                                    Allocations are currently being processed.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($ssp_distributions as $dist): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dist['category_name']); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($dist['total_allocated']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <tr class="total-row" style="background-color: #f8f9fa;">
                                            <td><strong>Total Distributed</strong></td>
                                            <td style="text-align: right; color: #e67e22;">
                                                <strong><?php echo number_format($ssp_total_allocated); ?></strong>
                                            </td>
                                        </tr>
                                    
                                    </tbody>
                                </table>
                            </div>

                            <div class="projecttable donors">
                                <?php if ($ssp_error): ?>
                                    <div class="db-error-msg">
                                        <strong><?= t('proj-db-error') ?></strong> <?php echo htmlspecialchars($ssp_error); ?>
                                    </div>
                                <?php endif; ?>
                                <table>
                                    <thead><tr><th colspan="3"><?= t('proj-donors-title') ?></th></tr>
                                    <tr><th><?= t('proj-donors-th-code') ?></th><th><?= t('proj-donors-th-name') ?></th><th><?= t('proj-donors-th-contributed') ?></th></tr></thead>
                                    <tbody>
                                        <?php if (count($ssp_donations) > 0): ?>
                                            <?php foreach ($ssp_donations as $donation): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($donation['receipt_number'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $donation['is_anonymous'] ? t('proj-donors-anonymous') : htmlspecialchars($donation['full_name']); ?></td>
                                                    <td><?php echo number_format($donation['amount_ugx']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center"><?= t('proj-donors-empty') ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="projectSection <?php echo ($active_project == 'CEP001') ? 'active' : ''; ?>" id="CEP001-section">
            <div class="projectContainer" id="CEP001">
                <header class="projectTitle">
                    <h4 class="contributeTitle"><?= t('proj-cep-tag') ?></h4>
                    <span class="badge"><?= t('proj-id-label') ?> CEP001</span>
                    <h4><?= t('proj-cep-title') ?></h4>
                </header>
                <div class="projectcontent">
                    <div class="projectOverview">
                        <div class="image">
                            <img src="assets/Images/support-community.webp" alt="Support Community">
                        </div>
                        <div class="projectDesc">
                            <p><?= t('proj-cep-desc1') ?></p>
                            <span><?= t('proj-cep-desc2') ?></span>
                            <div class="mission-box">
                                <p><strong><?= t('proj-ssp-mission-prefix') ?></strong> <?= t('proj-cep-mission1') ?> <strong><?= t('proj-cep-mission2') ?></strong> <?= t('proj-cep-mission3') ?> <strong>UGX 270,000</strong>.</p>
                            </div>
                            <button class="donate-btn open-donate-window" 
                                    data-purpose="<?= t('proj-cep-tag') ?>"
                                    data-project-id="CEP001"
                                    data-project-name="<?= t('proj-cep-title') ?>">
                                <?= t('proj-btn-contribute') ?>
                            </button>
                        </div>
                        <div class="targetBar horizontal-bars">
                            <div class="progress-wrapper" data-target="<?php echo $cep_target_ugx; ?>">
                                <h5 class="bar-title"><?= t('proj-cep-bar-nutrition') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($cep_total_ugx); ?> UGX</strong></span>
                                    <span><?= t('proj-bar-target') ?> <strong><?php echo number_format($cep_target_ugx); ?> UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill received-fill" style="width: <?php echo $cep_percent; ?>%;">
                                        <span class="progress-text"><?php echo $cep_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="progress-wrapper">
                                <h5 class="bar-title"><?= t('proj-cep-bar-reach') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-cep-bar-beneficiaries') ?> <strong><?php echo $cep_students_helped; ?></strong></span>
                                    <span><?= t('proj-bar-target') ?> <strong><?php echo $cep_students_target; ?> <?= t('proj-bar-students-target') ?></strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill students-fill" style="width: <?php echo $cep_students_percent; ?>%;">
                                        <span class="progress-text"><?php echo $cep_students_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="progress-wrapper">
                                <h5 class="bar-title"><?= t('proj-cep-bar-food-support') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-spent') ?> <strong class="js-spent-amount"><?php echo number_format($cep_actual_spent); ?> UGX</strong></span>
                                    <span><?= t('proj-bar-allocated') ?> <strong class="js-helped-amount"><?php echo number_format($cep_total_ugx); ?> UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill spent-fill" style="width: <?php echo $cep_spent_percent; ?>%;">
                                        <span class="progress-text"><?php echo $cep_spent_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="projectDetails">
                        <div class="projectdata">
                            <h3><?= t('proj-desc-title') ?></h3>
                            <div class="projecttext">
                                <p><?= t('proj-cep-desc-sub') ?></p>
                            </div>
                            <div class="content-flex-column">
                                <div class="goal-card">
                                    <h4><?= t('proj-ssp-target-title') ?></h4>
                                    <p><?= t('proj-cep-target1') ?> <strong><?= t('proj-cep-target2') ?></strong> <?= t('proj-cep-target3') ?> <strong><?= t('proj-cep-target4') ?></strong> <?= t('proj-cep-target5') ?> <strong>UGX 270,000</strong>.</p>
                                    <ul class="impact-stats impact-stats-bold">
                                        <li class="stat-item"><strong>44</strong> <span><?= t('proj-cep-impact1') ?></span></li>
                                        <li class="stat-item"><strong>44,000</strong> <span><?= t('proj-cep-impact2') ?></span></li>
                                    </ul>
                                    <span class="highlight-box"><?= t('proj-cep-highlight1') ?> <strong>UGX 22,000</strong> <?= t('proj-cep-highlight2') ?></span>
                                </div>
                                <div class="fund-card">
                                    <h4><?= t('proj-budget-title') ?></h4>
                                    <p><?= t('proj-cep-budget-desc') ?></p>
                                    <table class="currency-table">
                                        <thead>
                                            <tr><th><?= t('proj-cep-th-metric') ?></th><th>UGX</th><th>USD</th><th>EUR</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><?= t('proj-cep-lvl-cost') ?></td><td>500</td><td>$0.14</td><td>€0.12</td></tr>
                                            <tr><td><strong><?= t('proj-cep-lvl-total') ?></strong></td><td><strong>22,000,000</strong></td><td><strong>$5,950</strong></td><td><strong>€5,500</strong></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="goal-card">
                                    <h4><?= t('proj-cep-income-title') ?></h4>
                                    <p><?= t('proj-cep-income-desc1') ?></p>
                                    <p><?= t('proj-cep-income-desc2') ?></p>
                                    <ul class="impact-stats impact-stats-bold">
                                        <li class="stat-item"><span><?= t('proj-cep-income-list1') ?></span></li>
                                        <li class="stat-item"><span><?= t('proj-cep-income-list2') ?></span></li>
                                    </ul>
                                    <p class="italic-note"><?= t('proj-cep-income-note') ?></p>
                                </div>
                                <div class="impact-card">
                                    <h4><?= t('proj-impact-title') ?></h4>
                                    <ul class="impact-stats">
                                        <li class="stat-item"><strong>90%</strong> <span><?= t('proj-cep-trans1') ?></span></li>
                                        <li class="stat-item"><strong>3%</strong> <span><?= t('proj-cep-trans2') ?></span></li>
                                        <li class="stat-item"><strong>7%</strong> <span><?= t('proj-cep-trans3') ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="projectreport">
                            <h3><?= t('proj-fin-report-title') ?></h3>
                            
                            <div class="projecttable fundman">
                                <div class="totalFund">
                                    <p><?= t('proj-fin-total-received') ?></p>
                                    <span><?php echo number_format($cep_total_ugx); ?> UGX</span>
                                </div>
                                <table>
                                    <thead>
                                        <tr><th colspan="2"><?= t('proj-fin-dist-title') ?></th></tr>
                                        <tr>
                                            <th><?= t('proj-fin-th-category') ?></th>
                                            <th style="text-align: right;"><?= t('proj-fin-th-spent') ?> (UGX)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($cep_distributions)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted" style="padding: 20px;">
                                                    Allocations are currently being processed.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($cep_distributions as $dist): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dist['category_name']); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($dist['total_allocated']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <tr class="total-row" style="background-color: #f8f9fa;">
                                            <td><strong>Total Distributed</strong></td>
                                            <td style="text-align: right; color: #e67e22;">
                                                <strong><?php echo number_format($cep_total_allocated); ?></strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="projecttable schools">
                                <table>
                                    <thead><tr><th colspan="3"><?= t('proj-cep-schools-title') ?></th></tr>
                                    <tr><th><?= t('proj-cep-schools-th-code') ?></th><th><?= t('proj-cep-schools-th-name') ?></th><th><?= t('proj-cep-schools-th-eggs') ?></th></tr></thead>
                                    <tbody>
                                        <?php if (count($beneficiary_schools) > 0): ?>
                                            <?php foreach ($beneficiary_schools as $school): ?>
                                                <tr><td><?php echo $school['code']; ?></td><td><?php echo $school['name']; ?></td><td><?php echo $school['eggs']; ?></td></tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center"><?= t('proj-cep-schools-empty') ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="projecttable donors">
                                <?php if ($cep_error): ?>
                                    <div class="db-error-msg">
                                        <strong><?= t('proj-db-error') ?></strong> <?php echo htmlspecialchars($cep_error); ?>
                                    </div>
                                <?php endif; ?>
                                <table>
                                    <thead><tr><th colspan="3"><?= t('proj-donors-title') ?></th></tr>
                                    <tr><th><?= t('proj-donors-th-code') ?></th><th><?= t('proj-donors-th-name') ?></th><th><?= t('proj-donors-th-contributed') ?></th></tr></thead>
                                    <tbody>
                                        <?php if (count($cep_donations) > 0): ?>
                                            <?php foreach ($cep_donations as $donation): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($donation['receipt_number'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $donation['is_anonymous'] ? t('proj-donors-anonymous') : htmlspecialchars($donation['full_name']); ?></td>
                                                    <td><?php echo number_format($donation['amount_ugx']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center"><?= t('proj-donors-empty') ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="projectSection <?php echo ($active_project == 'IDP001') ? 'active' : ''; ?>" id="IDP001-section">
            <div class="projectContainer" id="IDP001">
                <header class="projectTitle">
                    <h4 class="contributeTitle"><?= t('proj-idp-tag') ?></h4>
                    <span class="badge"><?= t('proj-id-label') ?> IDP001</span>
                    <h4><?= t('proj-idp-title') ?></h4>
                </header>

                <div class="projectcontent">
                    <div class="projectOverview">
                        <div class="image">
                            <img src="assets/Images/support-infrastructure.webp" alt="Hostel Construction Kyebando,Mayuge">
                        </div>
                        <div class="projectDesc">
                            <p><?= t('proj-idp-desc1') ?></p>
                            <span><?= t('proj-idp-desc2') ?></span>
                            <div class="mission-box">
                                <p><strong><?= t('proj-ssp-mission-prefix') ?></strong> <?= t('proj-idp-mission-text') ?></p>
                            </div>
                            <button class="donate-btn open-donate-window" 
                                    data-purpose="<?= t('proj-idp-tag') ?>"
                                    data-project-id="IDP001"
                                    data-project-name="<?= t('proj-idp-title') ?>">
                                <?= t('proj-btn-contribute') ?>
                            </button>
                        </div>

                        <div class="targetBar horizontal-bars">
                            <div class="progress-wrapper" data-target="175000000">
                                <h5 class="bar-title"><?= t('proj-idp-bar-funding') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-bar-received') ?> <strong><?php echo number_format($idp_total_ugx); ?> UGX</strong></span>
                                    <span><?= t('proj-bar-target') ?> <strong>175,000,000 UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill received-fill" style="width: <?php echo $idp_percent; ?>%;">
                                        <span class="progress-text"><?php echo $idp_percent; ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="progress-wrapper">
                                <h5 class="bar-title"><?= t('proj-idp-bar-status') ?></h5>
                                <div class="progress-labels">
                                    <span><?= t('proj-idp-bar-work') ?> <strong><?php echo $idp_work_status; ?></strong></span>
                                    <span><?= t('proj-idp-bar-activation') ?> <strong>50,000,000 UGX</strong></span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-fill spent-fill" style="width: <?php echo min(100, ($idp_total_ugx / 50000000) * 100); ?>%;">
                                        <span class="progress-text"><?= t('proj-idp-bar-startup') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="projectDetails">
                        <div class="projectdata">
                            <h3><?= t('proj-desc-title') ?></h3>
                            <div class="projecttext">
                                <p><?= t('proj-idp-desc-sub') ?></p>
                            </div>
                            
                            <div class="content-flex-column">
                                <div class="goal-card">
                                    <h4><?= t('proj-idp-plan-title') ?></h4>
                                    <p><?= t('proj-idp-plan1') ?> <strong><?= t('proj-idp-plan2') ?></strong> <?= t('proj-idp-plan3') ?></p>
                                    <ul class="impact-stats impact-stats-indented">
                                        <li class="stat-item"><span><?= t('proj-idp-plan-list1') ?></span></li>
                                        <li class="stat-item"><span><?= t('proj-idp-plan-list2') ?></span></li>
                                        <li class="stat-item"><span><?= t('proj-idp-plan-list3') ?></span></li>
                                        <li class="stat-item"><span><?= t('proj-idp-plan-list4') ?></span></li>
                                    </ul>
                                </div>

                                <div class="fund-card">
                                    <h4><?= t('proj-idp-budget-title') ?></h4>
                                    <p><?= t('proj-idp-budget-desc') ?></p>
                                    <table class="currency-table">
                                        <thead>
                                            <tr><th><?= t('proj-cep-th-metric') ?></th><th>UGX</th><th>USD</th><th>EUR</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><strong><?= t('proj-idp-lvl-goal') ?></strong></td><td><strong>175,000,000</strong></td><td><strong>$53,000</strong></td><td><strong>€51500</strong></td></tr>
                                            <tr><td><?= t('proj-idp-lvl-startup') ?></td><td>50,000,000</td><td>$13,514</td><td>€11,628</td></tr>
                                        </tbody>
                                    </table>
                                    <p class="fine-print"><?= t('proj-idp-budget-note') ?></p>
                                </div>

                                <div class="impact-card">
                                    <h4><?= t('proj-idp-impact-title') ?></h4>
                                    <ul class="impact-stats">
                                        <li class="stat-item"><strong><?= t('proj-idp-impact1-title') ?></strong> <span><?= t('proj-idp-impact1-desc') ?></span></li>
                                        <li class="stat-item"><strong><?= t('proj-idp-impact2-title') ?></strong> <span><?= t('proj-idp-impact2-desc') ?></span></li>
                                        <li class="stat-item"><strong><?= t('proj-idp-impact3-title') ?></strong> <span><?= t('proj-idp-impact3-desc') ?></span></li>
                                        <li class="stat-item"><strong><?= t('proj-idp-impact4-title') ?></strong> <span><?= t('proj-idp-impact4-desc') ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="projectreport">
                            <h3><?= t('proj-fin-report-title') ?></h3>
                            
                            <div class="projecttable fundman">
                                <div class="totalFund">
                                    <p><?= t('proj-fin-total-received') ?></p>
                                    <span><?php echo number_format($idp_total_ugx); ?> UGX</span>
                                </div>
                                <table>
                                    <thead>
                                        <tr><th colspan="2"><?= t('proj-idp-fin-breakdown') ?></th></tr>
                                        <tr>
                                            <th><?= t('proj-fin-th-category') ?></th>
                                            <th style="text-align: right;"><?= t('proj-fin-th-spent') ?> (UGX)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($idp_distributions)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted" style="padding: 20px;">
                                                    Allocations are currently being processed.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($idp_distributions as $dist): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dist['category_name']); ?></td>
                                                    <td style="text-align: right;"><?php echo number_format($dist['total_allocated']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <tr class="total-row" style="background-color: #f8f9fa;">
                                            <td><strong>Total Distributed</strong></td>
                                            <td style="text-align: right; color: #e67e22;">
                                                <strong><?php echo number_format($idp_total_allocated); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 15px;"><strong><?= t('proj-idp-cat-status') ?></strong></td>
                                            <td style="text-align: right; padding-top: 15px;"><strong><?php echo $idp_work_status; ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="projecttable donors">
                                <table>
                                    <thead>
                                        <tr><th colspan="3"><?= t('proj-idp-donors-title') ?></th></tr>
                                        <tr><th><?= t('proj-idp-donors-th-receipt') ?></th><th><?= t('proj-donors-th-name') ?></th><th><?= t('proj-donors-th-contributed') ?></th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($idp_donations) > 0): ?>
                                            <?php foreach ($idp_donations as $donation): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($donation['receipt_number'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $donation['is_anonymous'] ? t('proj-donors-anonymous') : htmlspecialchars($donation['full_name']); ?></td>
                                                    <td><?php echo number_format($donation['amount_ugx']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center"><?= t('proj-idp-donors-empty') ?></td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
<?php 
$pdo = null; 
?>