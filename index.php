<?php
// 1. Start Session securely by loading our security config FIRST
require_once __DIR__ . '/includes/security.php';

// Force PHP to load the translation engine for the homepage
require_once __DIR__ . '/includes/language_manager.php';

// 2. Include database configuration using an ABSOLUTE path
$db_path = __DIR__ . '/api/db_config.php';
if (!file_exists($db_path)) {
    die("SYSTEM ERROR: Cannot find the database configuration file at: " . $db_path);
}
require_once $db_path;

// 3. Verify PDO was successfully created
if (!isset($pdo)) {
    die("SYSTEM ERROR: The database file loaded, but the \$pdo connection failed to initialize.");
}

// 4. Helper function to cache database queries
function getCachedData($pdo, $cacheKey, $query, $ttl = 3600) {
    $cacheFile = __DIR__ . "/cache/{$cacheKey}.json";
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    try {
        $stmt = $pdo->query($query);
        $data = $stmt->fetchAll();
        
        if (!is_dir(__DIR__ . '/cache')) {
            mkdir(__DIR__ . '/cache', 0755, true);
        }
        file_put_contents($cacheFile, json_encode($data));
        
        return $data;
    } catch (PDOException $e) {
        error_log("Database Error for {$cacheKey}: " . $e->getMessage());
        return [];
    }
}

// 5. Fetch all dynamic data using the cache system
$dynamic_data = getCachedData($pdo, 'school_results', "SELECT * FROM web_school_results ORDER BY year ASC, id ASC");
$dynamic_horizons = getCachedData($pdo, 'horizons_active', "SELECT * FROM web_horizons WHERE is_active = 1 ORDER BY display_order ASC");
$highlights = getCachedData($pdo, 'gallery_active', "SELECT * FROM web_gallery_highlights WHERE is_active = 1 ORDER BY display_order ASC");

// Detect current language for Dynamic Database Fallbacks
$current_lang = $_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en';

// --- FETCH PROJECT DONATION DATA FOR HOMEPAGE WIDGETS ---
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

// --- UPDATED: FETCH DISTRIBUTED FUNDS FOR PROGRESS BARS ---
function fetchProjectDistributionsSummary($pdo, $project_id) {
    $total_distributed = 0;
    if (!isset($pdo)) return 0;
    try {
        // Exclude BOTH Students Benefited and Sustainable Reserve to fix the 100% bug
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

// --- FETCH RECENT DONORS FOR HOMEPAGE TABLE ---
$recent_donors = [];
try {
    // Dynamic subquery updated to exclude Reserve & grab students/terms
    $donor_stmt = $pdo->query("
        SELECT dn.full_name, dn.is_anonymous, dn.project_id, dn.currency, dn.amount, dn.amount_received, dn.students_benefited, dn.benefit_year, dn.terms_benefited,
               (SELECT SUM(amount_allocated) FROM web_donor_distributions WHERE donation_id = dn.id AND category_name NOT IN ('Students Benefited', 'Sustainable Reserve')) as total_spent
        FROM web_donations dn
        WHERE dn.payment_status = 'success' 
        AND dn.amount_received IS NOT NULL 
        AND dn.amount_received > 0 
        ORDER BY dn.created_at DESC LIMIT 10
    "); 
    $recent_donors = $donor_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { }
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= t('site-title') ?>">
    <link rel="icon" href="assets/Images/logo_MBSG_UG_8.webp">
    <title><?= t('site-title') ?></title>
    <link rel="stylesheet" href="assets/styles/refstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main style="overflow: visible;">
        <section class="hero">
            <div class="hero_overlap"></div>
            <div class="container hero_content">
                <div class="hero_grid">
                    <div class="hero-card hero-card--left reveal-slide-right">
                        <div class="hero-card-bg">
                            <img src="assets/Images/our1.7.webp" alt="boat">
                            <div class="image_overlay gradient-dark"></div>
                        </div>
                        <div class="text_container1">
                            <div class="text_top"><p><?= t('index-hero-challenge') ?></p></div>
                             <div class="text_middle"><p><?= t('index-hero-why') ?></p></div>
                            <div class="text_msg"><p><?= t('index-hero-angels') ?></p></div>
                        </div>
                    </div>

                    <div class="hero_grid_overlap">
                        <div class="montfort animate-up">
                            <div class="montfort-image">
                                <img src="assets/Images/Montfort_Statue3.webp" alt="montfort">
                                <div class="image_overlay shade"></div>
                            </div>
                            <div class="montfort-text">
                                <div class="mf-name"><?= t('index-hero-st-montfort') ?></div>
                                <div class="mf-caption"><p><?= t('index-hero-founder-insp') ?></p></div>
                            </div>
                        </div>
                        <div class="hero-motto animate-up">
                            <div class="motto-text">
                                <p>
                                    <strong><?= t('index-hero-unite') ?></strong><br>
                                    <span><?= t('index-hero-for') ?></span><br>
                                    <strong><?= t('index-hero-peace') ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="hero-card hero-card--right reveal-slide-left">
                        <div class="hero-card-bg">
                            <img src="assets/Images/our2.1.webp" alt="empower">
                            <div class="image_overlay gradient-dark"></div>
                        </div>
                        <div class="text_container2">
                            <div class="text_top"><p><?= t('index-hero-empower') ?></p></div>
                            <div class="text_middle"><p><?= t('index-hero-why-not') ?></p></div>
                        </div>
                    </div>
                </div> 
            </div>
        </section>

        <div class="top-right-marquee">
            <div class="marquee-track">
                <span><?= t('index-marquee-kyebando') ?></span>
                <span><?= t('index-marquee-mpala') ?></span>
                <span><?= t('index-marquee-isunga') ?></span>
                <span><?= t('index-marquee-kyebando') ?></span>
                <span><?= t('index-marquee-mpala') ?></span>
                <span><?= t('index-marquee-isunga') ?></span>
            </div>
        </div>

        <section class="vision" id="vision">
            <div class="vision_grid">
                <div class="vision-container">
                    <div class="vision-text reveal-slide-left">
                        <div class="vision-title">
                            <h1>
                                <strong><?= t('index-vision-transforming') ?></strong><br>
                                <?= t('index-vision-through') ?> <strong><?= t('index-vision-faith') ?></strong><br>
                                <?= t('index-vision-for') ?> <strong><?= t('index-vision-just') ?></strong> <?= t('index-vision-and') ?> <strong><?= t('index-vision-fraternal') ?></strong>
                            </h1>
                        </div>
                        <div class="vision-caption">
                            <p><?= t('index-vision-caption') ?></p>
                        </div>
                    </div>
                    <div class="vision-link reveal-slide-right">
                        <a href="about.php#vision"><?= t('index-know-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
                <div class="vision-container-image reveal-slide-right">
                    <img src="assets/Images/vision.webp" alt="vision">
                </div>
            </div>
        </section>

        <section class="heritage" id="heritage">
            <div class="heritage_grid">
                <div class="heritage-container-image reveal-slide-left">
                    <img src="assets/Images/heritage.webp" alt="vision">
                </div>
                <div class="heritage-container">
                    <div class="heritage-text reveal-slide-right">
                        <div class="heritage-title">
                            <h1><?= t('index-heritage-title1') ?><br>
                                <strong><?= t('index-heritage-title2') ?></strong>
                            </h1>
                        </div>
                        <div class="heritage-caption">
                            <p><?= t('index-heritage-caption') ?> “<span><?= t('index-heritage-god-alone') ?></span>”.</p>
                        </div>
                    </div>
                    <div class="heritage-link reveal-slide-left">
                        <a href="about.php#heritage"><?= t('index-know-more') ?><i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="location" id="location">
            <div class="location_grid">
                <div class="location-title">
                    <div class="title"><h1><?= t('location') ?></h1></div>
                    <div class="caption"><p><?= t('index-location-caption') ?></p></div>
                </div>
                <div class="location-container">
                    <div class="location-card reveal-slide-left">
                        <div class="location-content">
                            <div class="image-container">
                                <img src="assets/Images/Mpala/2023/Mpala foundation stone.webp" alt="image" class="img-slide active" data-year="2023">
                                <div class="year-overlap">2023</div>
                            </div>
                            <div class="location-text">
                                <div class="place"><?= t('entebbe-mpala') ?></div>
                                <div class="schoolname">
                                    <p><?= t('index-loc-mpala-desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="location-link">
                            <a href="location.php#mpala-section" class="location-navigation"><?= t('index-view-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                    <div class="location-card scroll-animate">
                        <div class="location-content">
                            <div class="image-container">
                                <img src="assets/Images/kyebando/2023/Meeting with students and staff of St. Kizito School before taking over the school.webp" alt="image" class="img-slide active" data-year="2023">
                                <img src="assets/Images/kyebando/2024/Inauguration_Montfort Home_Superior general.webp" alt="image" class="img-slide " data-year="2024">
                                <div class="year-overlap">2023</div>
                            </div>
                            <div class="location-text ">
                                <div class="place"><?= t('jinja-kyebando') ?></div>
                                <div class="schoolname">
                                    <p><?= t('index-loc-kyebando-desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="location-link">
                            <a href="location.php#kyebando-section" class="location-navigation"><?= t('index-view-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                    <div class="location-card reveal-slide-right">
                        <div class="location-content">
                            <div class="image-container">
                                <img src="assets/Images/Fort Portal 2022.jpg.webp" alt="image" class="img-slide active" data-year="2022">
                                <div class="year-overlap">2022</div>
                            </div>
                            <div class="location-text">
                                <div class="place"><?= t('fort-isunga') ?></div>
                                <div class="schoolname">
                                    <p><?= t('index-loc-isunga-desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="location-link">
                            <a href="location.php#isunga-section" class="location-navigation"><?= t('index-view-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="location-link">
                    <a href="location.php" class="primary-btn"><?= t('index-view-more') ?></a>
                </div>
            </div>
        </section>

        <section class="results">
            <div class="results-container">
                <div class="section-title">
                    <h2><?= t('index-results-title') ?></h2>
                    <p><?= t('index-results-caption') ?></p>
                </div>

                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th rowspan="2"><?= t('table-year') ?></th>
                                <th rowspan="2"><?= t('table-institution') ?></th>
                                <th colspan="2" class="text-center"><?= t('table-training') ?></th>
                                <th rowspan="2"><?= t('table-course') ?></th>
                                <th rowspan="2"><?= t('table-certificate') ?></th>
                                <th colspan="2" class="text-center"><?= t('table-graduated') ?></th>
                            </tr>
                            <tr>
                                <th class="text-center sub-th"><?= t('table-male') ?></th>
                                <th class="text-center sub-th"><?= t('table-female') ?></th>
                                <th class="text-center sub-th"><?= t('table-male') ?></th>
                                <th class="text-center sub-th"><?= t('table-female') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dynamic_data)): ?>
                                <tr>
                                    <td colspan="8" class="text-center"><?= t('table-empty') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dynamic_data as $row): 
                                    // Language Fallback Logic
                                    $db_course = !empty($row['course_' . $current_lang]) ? $row['course_' . $current_lang] : $row['course'];
                                ?>
                                    <tr>
                                        <td class="year-cell"><?php echo htmlspecialchars($row['year']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['institution']); ?></strong></td>
                                        <td class="text-center"><?php echo $row['train_male'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $row['train_female'] ?: '-'; ?></td>
                                        <td><?php echo htmlspecialchars($db_course); ?></td>
                                        <td><?php echo htmlspecialchars($row['certificate']); ?></td>
                                        <td class="text-center"><?php echo $row['grad_male'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $row['grad_female'] ?: '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="graduation-full-form">
                    <div class="full-form"> 
                        <i class="fab fa-diaspora"></i>
                        <span><?= t('index-results-ple') ?></span>
                    </div>
                    <div class="full-form"> 
                        <i class="fab fa-diaspora"></i>
                        <span><?= t('index-results-uce') ?></span>
                    </div>
                    <div class="full-form"> 
                        <i class="fab fa-diaspora"></i>
                        <span><?= t('index-results-uace') ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="unite-message" id="unite-message">
            <div class="unite-image">
                <img src="assets/Images/unite-section.webp" alt="unite-image">
            </div>
            <div class="unite-container">
                <h2 class="animate-up"><?= t('unite-msg-title') ?></h2>
                <p class="reveal-slide-right">
                    <?= t('unite-msg-p1-1') ?><strong><?= t('unite-msg-p1-2') ?></strong>&nbsp; 
                    <?= t('unite-msg-p1-3') ?><strong><?= t('unite-msg-p1-4') ?></strong>
                    <?= t('unite-msg-p1-5') ?><strong><?= t('unite-msg-p1-6') ?></strong>
                    <?= t('unite-msg-p1-7') ?>
                </p>
                <p class="reveal-slide-right">
                    <?= t('unite-msg-p2-1') ?>
                    <strong><a href="unite.php#unite-prayer"> <?= t('unite-msg-p2-2') ?> </a></strong>
                    <?= t('unite-msg-p2-3') ?><strong><?= t('unite-msg-p2-4') ?></strong><?= t('unite-msg-p2-5') ?> <?= t('unite-msg-p2-6') ?>
                    <strong><a href="unite.php#unite-volunteer"> <?= t('unite-msg-p2-7') ?> </a></strong>
                    <?= t('unite-msg-p2-8') ?>
                    <strong><a href="support.php"> <?= t('unite-msg-p2-9') ?> </a></strong>
                </p>
            </div>
            <div class="unite-btn-wrapper animate-up">
                <a href="unite.php" class="primary-btn"><?= t('index-unite-btn') ?></a>
            </div>
        </section>

        <section class="horizons" id="horizons">
            <div class="horizons-overlap">
                <?php foreach ($dynamic_horizons as $index => $h): ?>
                    <img class="slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                         src="assets/Images/horizon/<?php echo htmlspecialchars($h['image']); ?>">
                <?php endforeach; ?>
            </div>
            
            <div class="horizons-grid">
                <div class="horizons-title">
                    <h1 class="title"><?= t('section_horizons') ?></h1>
                    <p class="caption"><?= t('section_horizons_cap') ?></p>
                </div>
                <div class="horizons-sliderwindow">
                    <button class="nav-control prev" id="horizon-prev"><i class="fa fa-chevron-left"></i></button>
                    <button class="nav-control next" id="horizon-next"><i class="fa fa-chevron-right"></i></button>
                    
                    <div class="slider-track" id="slider-track">
                        <?php foreach ($dynamic_horizons as $index => $h): 
                            $db_title   = !empty($h['title_' . $current_lang])   ? $h['title_' . $current_lang]   : $h['title'];
                            $db_caption = !empty($h['caption_' . $current_lang]) ? $h['caption_' . $current_lang] : $h['caption'];
                            $db_label   = !empty($h['label_' . $current_lang])   ? $h['label_' . $current_lang]   : ($h['label'] ?? '');
                        ?>
                        <div class="slide <?= $index === 0 ? 'active' : ''; ?>">
                            <div class="image-wrapper">
                                <img src="assets/Images/horizon/<?= htmlspecialchars($h['image']); ?>" alt="<?= htmlspecialchars($db_title); ?>">
                                <div class="himage-overlap"></div>
                                <?php if (!empty($db_label)): ?>
                                    <div class="image-label"><span><?= htmlspecialchars($db_label); ?></span></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-container">
                                <div class="text-title"><h2><?= htmlspecialchars($db_title); ?></h2></div>
                                <div class="text-content">
                                    <p><?= htmlspecialchars($db_caption); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="slider-dots" id="horizon-dots"></div>
                </div>
            </div>
        </section>

        <section class="projects-overview" id="projects-overview">
            <div class="section-title text-center animate-up">
                <h2><?= t('index-projects-title') ?></h2>
                <p><?= t('index-projects-subtitle') ?></p>
            </div>
            <div class="projects-grid">
                
                <div class="project-card animate-up">
                    <div class="project-image-wrapper">
                        <img src="assets/Images/support-student.webp" alt="Scholarships" class="project-img">
                        <span class="project-badge-overlay">ID: SSP001</span>
                    </div>
                    <div class="project-info">
                        <h3 class="project-card-title"><?= t('index-proj1-title') ?></h3>
                        <p><?= t('index-proj1-text') ?></p>
                        
                        <div class="mini-progress-wrapper">
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
                        <a href="projects.php?project=SSP001" class="learn-more-link"><?= t('index-proj-learn-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
                
                <div class="project-card animate-up" style="transition-delay: 0.2s">
                    <div class="project-image-wrapper">
                        <img src="assets/Images/support-infrastructure.webp" alt="Infrastructure" class="project-img">
                        <span class="project-badge-overlay">ID: IDP001</span>
                    </div>
                    <div class="project-info">
                        <h3 class="project-card-title"><?= t('index-proj2-title') ?></h3>
                        <p><?= t('index-proj2-text') ?></p>

                        <div class="mini-progress-wrapper">
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
                        <a href="projects.php?project=IDP001" class="learn-more-link"><?= t('index-proj-learn-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
                
                <div class="project-card animate-up" style="transition-delay: 0.4s">
                    <div class="project-image-wrapper">
                        <img src="assets/Images/support-community.webp" alt="Community Outreach" class="project-img">
                        <span class="project-badge-overlay">ID: CEP001</span>
                    </div>
                    <div class="project-info">
                        <h3 class="project-card-title"><?= t('index-proj3-title') ?></h3>
                        <p><?= t('index-proj3-text') ?></p>

                        <div class="mini-progress-wrapper">
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
                        <a href="projects.php?project=CEP001" class="learn-more-link"><?= t('index-proj-learn-more') ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
                
            </div>
            <div class="section-bottom-btn animate-up">
                <a href="projects.php" class="primary-btn"><?= t('index-proj-view-all') ?></a>
            </div>
        </section>

        <section class="donors-overview results" id="donors-overview">
            <div class="results-container">
                <div class="section-title text-center animate-up">
                    <h2><?= t('index-donors-title') ?></h2>
                    <p><?= t('index-donors-subtitle') ?></p>
                </div>

                <div class="table-responsive animate-up">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th rowspan="2"><?= t('table-donor-name') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-project-id') ?></th>
                                <th colspan="3" class="text-center"><?= t('table-donated') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-received-ugx') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-beneficiary-students') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-benefit-year') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-terms') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-spent') ?></th>
                                <th rowspan="2" class="text-center"><?= t('table-reserved-dev') ?></th>
                            </tr>
                            <tr>
                                <th class="text-center sub-th"><?= t('table-euro') ?></th>
                                <th class="text-center sub-th"><?= t('table-usd') ?></th>
                                <th class="text-center sub-th"><?= t('table-ugx') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_donors)): ?>
                                <tr>
                                    <td colspan="11" class="text-center"><?= t('table-empty') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_donors as $donor): 
                                    $donor_name = $donor['is_anonymous'] ? t('table-anonymous') : htmlspecialchars($donor['full_name']);
                                    $amt_euro = ($donor['currency'] == 'EURO_GBP') ? number_format($donor['amount'], 2) : '-';
                                    $amt_usd  = ($donor['currency'] == 'USD') ? number_format($donor['amount'], 2) : '-';
                                    $amt_ugx  = ($donor['currency'] == 'UGX') ? number_format($donor['amount']) : '-';
                                    
                                    $received_ugx = $donor['amount_received'];
                                    
                                    // NEW: Variables for students, terms and calculations
                                    $students_val = isset($donor['students_benefited'])
                                        && $donor['students_benefited'] !== null
                                        && $donor['students_benefited'] !== ''
                                        ? (int) $donor['students_benefited']
                                        : '-';

                                    $benefit_year_val = isset($donor['benefit_year'])
                                        && $donor['benefit_year'] !== null
                                        && $donor['benefit_year'] !== ''
                                        ? (int) $donor['benefit_year']
                                        : '-';

                                    $terms_val = isset($donor['terms_benefited'])
                                        && $donor['terms_benefited'] !== null
                                        && $donor['terms_benefited'] !== ''
                                        ? (int) $donor['terms_benefited'] . ' / 3'
                                        : '-';

                                    // DYNAMIC: Actual Spent Amount based on Subquery (excluding Reserve & Students Count)
                                    $spent_ugx = $donor['total_spent'] ? $donor['total_spent'] : 0;
                                    $spent_percent = ($received_ugx > 0) ? min(100, round(($spent_ugx / $received_ugx) * 100)) : 0;
                                    
                                    // NEW: Show remaining percentage 
                                    $reserved_percent = 100 - $spent_percent; 
                                ?>
                                    <tr>
                                        <td><strong><?= $donor_name ?></strong></td>
                                        <td class="text-center" style="color: var(--primary-blue); font-weight: 700;"><?= htmlspecialchars($donor['project_id']) ?></td>
                                        <td class="text-center"><?= $amt_euro ?></td>
                                        <td class="text-center"><?= $amt_usd ?></td>
                                        <td class="text-center"><?= $amt_ugx ?></td>
                                        <td class="text-center" style="color: #27ae60; font-weight: 700;"><?= number_format($received_ugx) ?></td>
                                        
                                        <td class="text-center"><?= $students_val ?></td>
                                        <td class="text-center"><?= $benefit_year_val ?></td>
                                        <td class="text-center"><?= $terms_val ?></td>
                                        <td class="text-center" style="color: #e67e22; font-weight: 700;"><?= $spent_percent ?>%</td>
                                        <td class="text-center" style="color: #8e44ad; font-weight: 700;"><?= $reserved_percent ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="volunteer-highlight">
            <div class="volunteer-box animate-up">
                <i class="fas fa-hand-holding-heart"></i>
                <h2><?= t('index-volunteer-title') ?></h2>
                <p><?= t('index-volunteer-text') ?></p>
                <a href="unite.php#unite-volunteer" class="primary-fir volunteer-btn"><?= t('index-volunteer-btn') ?></a>
            </div>
        </section>

        <section class="gallery-highlight" id="galleryContainer">
            <div class="gallery-highlight_grid">
                <div class="gallery-highlight-title">
                    <div class="title"><h1><?= t('navbar-highlights') ?></h1></div>
                    <div class="caption"><p><?= t('index-highlights-caption') ?></p></div>
                </div>
                
                <button class="nav-control prev" id="gallery-prev"><i class="fas fa-chevron-circle-left"></i></button>
                <button class="nav-control next" id="gallery-next"><i class="fas fa-chevron-circle-right"></i></button>
                
                <div class="gallery-track" id="galleryTrack">
                    <div class="gallery-strip">
                        <?php if (!empty($highlights)): ?>
                            <?php foreach ($highlights as $h): 
                                $db_gallery_caption = !empty($h['caption_' . $current_lang]) ? $h['caption_' . $current_lang] : $h['caption'];
                            ?>
                                <div class="gallery-item">
                                    <img src="assets/Images/gallery_highlights/<?= htmlspecialchars($h['image']); ?>" alt="<?= htmlspecialchars($db_gallery_caption); ?>" loading="lazy" decoding="async">
                                    <span><?= htmlspecialchars($db_gallery_caption); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="gallery-strip">
                        <?php if (!empty($highlights)): ?>
                            <?php foreach ($highlights as $h): 
                                $db_gallery_caption = !empty($h['caption_' . $current_lang]) ? $h['caption_' . $current_lang] : $h['caption'];
                            ?>
                                <div class="gallery-item">
                                    <img src="assets/Images/gallery_highlights/<?= htmlspecialchars($h['image']); ?>" alt="<?= htmlspecialchars($db_gallery_caption); ?>" loading="lazy" decoding="async">
                                    <span><?= htmlspecialchars($db_gallery_caption); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    <h2><span><?= t('index-support-hands-1') ?></span> <?= t('index-support-hands-2') ?></h2>
                </div>
                <div class="button scroll-animate"><a href="support.php" class="primary-fir"><?= t('navbar-support') ?></a></div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer-diag.php'; ?>
    <script src="assets/script/script.js"></script>
</body>
</html>