<?php
// 1. Securely start the session using an absolute path
require_once __DIR__ . '/includes/security.php';

// ⭐ Force PHP to load the translation engine
require_once __DIR__ . '/includes/language_manager.php';

// 2. Include database configuration using an ABSOLUTE path
$db_path = __DIR__ . '/api/db_config.php';
if (!file_exists($db_path)) {
    die("SYSTEM ERROR: Cannot find the database configuration file at: " . $db_path);
}
require_once $db_path; 

// 3. Fetch data safely
try {
    $mission_stmt = $pdo->query("SELECT * FROM web_uganda_mission WHERE is_active = 1 ORDER BY display_order ASC");
    $dynamic_missions = $mission_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Mission Fetch Error: " . $e->getMessage());
    $dynamic_missions = [];
}

// ⭐ Detect current language for Dynamic Database Fallbacks
$current_lang = $_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= t('site-title') ?>">
    <link rel="icon" href="assets/Images/logo_MBSG_UG_8.webp">
    <title><?= t('site-title') ?> | <?= t('about-hero-title') ?></title>
    <link rel="stylesheet" href="assets/styles/refstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <section class="sub-pages ">
            <div class="hero_overlap">
                <img src="assets/Images/campus3.webp" alt="image">
            </div>
            <div class="container sub_hero_content">
                <h1 class="title animate-up"><?= t('about-hero-title') ?></h1>
                <p class="sub-title animate-up"><?= t('about-hero-subtitle') ?></p>
            </div>
        </section>
        <section class="about_vision" id="vision">
            <div class="about_vision_grid">
                <div class="vision_content">
                    <div class="vision_text_container">
                        <div class="vision_heading scroll-animate">
                            <p><?= t('about-vision-heading') ?></p>
                        </div>
                        <div class="vision_title scroll-animate">
                           <h1><strong><?= t('index-vision-transforming') ?></strong><br>
                            <?= t('index-vision-through') ?> &nbsp;<strong><?= t('index-vision-faith') ?></strong><br>
                            <?= t('index-vision-for') ?> &nbsp;<strong><?= t('index-vision-just') ?></strong>&nbsp; <?= t('index-vision-and') ?>&nbsp;<strong> <?= t('index-vision-fraternal') ?> </strong>
                            </h1>                        
                        </div>
                        <div class="vision_caption scroll-animate">
                            <p><?= t('index-vision-caption') ?></p>
                        </div>
                    </div>
                    <div class="vision_statements scroll-animate">
                        <div class="statement_container reveal-slide-right">
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <div class="statement"><p><?= t('about-statement1-text') ?></p></div>
                        </div>
                        <div class="statement_container reveal-slide-right">
                            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <div class="statement"><p><?= t('about-statement2-text') ?></p></div>
                        </div>
                        <div class="statement_container reveal-slide-right">
                            <div class="icon"><i class="fas fa-fist-raised"></i></div>
                            <div class="statement"><p><?= t('about-statement3-text') ?></p></div>
                        </div>
                    </div>
                </div>
                <div class="vision_image reveal-slide-left">
                    <img src="assets/Images/class5.webp" alt="vision">
                    <div class="vision_overlap gradient-dark"></div>
                </div>
            </div>
        </section>
        <section class="about_heritage" id="heritage">
            <div class="about_heritage_content">
                <div class="about_heritage_grid">
                    <div class="founder_image">
                        <img src="assets/Images/montfort3.webp" alt="St.Montfort image">
                        <div class="heritage_overlap"></div>
                    </div>
                    <div class="heritage_text_container">
                        <div class="heritage-title">
                            <h1><?= t('about-heritage-title1') ?><br>
                                <strong><?= t('about-heritage-title2') ?></strong>
                            </h1>
                        </div>
                        <div class="heritage-caption">
                            <p><?= t('about-heritage-p1') ?> “<span><?= t('index-heritage-god-alone') ?></span>”.</p>
                            <p><?= t('about-heritage-p2') ?></p>
                        </div>
                    </div>
                </div>
                <div class="about_heritage_statement">
                    <div class="province_supierors">
                        <div class="superior_container">
                            <div class="superior_image">
                                <img src="assets/Images/superior_general.webp" alt="Bro. Dionigi Taffarello Image">
                            </div>
                            <div class="superior_text">
                                <h2><?= t('about-bro-dionigi') ?></h2>
                                <p><?= t('about-superior-gen') ?></p>
                            </div>
                        </div>
                        <div class="superior_container">
                            <div class="superior_image">
                                <img src="assets/Images/Bro sajan.webp" alt="Bro. Sajan Antony Image">
                            </div>
                            <div class="superior_text">
                                <h2><?= t('about-bro-shajan') ?></h2>
                                <p><?= t('about-provincial-sup') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="heritage_statement_containers">
                        <div class="heritage_statement_title">
                            <h1><?= t('about-congregation-title') ?></h1>
                        </div>
                        <div class="heritage_statement_container">
                            <div class="heritage_statement scroll-animate">
                                <h1><?= t('about-cong-h1') ?></h1>
                                <p><?= t('about-cong-p1') ?></p>
                            </div>
                            <div class="heritage_statement scroll-animate">
                                <h1><?= t('about-edu-h1') ?></h1>
                                <p><?= t('about-edu-p1') ?></p>                       
                            </div>
                            <div class="heritage_statement scroll-animate">
                                <h1><?= t('about-beyond-h1') ?></h1>
                                <p><?= t('about-beyond-p1') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="about_provinces">
    <div class="province_details">
        <div class="province_title">
            <h1><?= t('about-stats-title') ?></h1>
        </div>
        <div class="provice_container">
            <div class="province_content">
                <h1 class="number_count" data-target="34">0</h1>
                <p><?= t('about-stats-countries') ?></p>
            </div>
            <div class="province_content">
                <h1 class="number_count" data-target="16">0</h1>
                <p><?= t('about-stats-provinces') ?></p>
            </div>
            <div class="province_content">
                <h1 class="number_count" data-target="1">0</h1>
                <p><?= t('about-stats-vice') ?></p>
            </div>
            <div class="province_content">
                <h1><?= t('about-stats-rome') ?></h1>
                <p><?= t('about-stats-admin') ?></p>
            </div>
        </div>
    </div>
</section>

        <section class="about_uganda" id="uganda_mission">
            <div class="uganda_mission_title">
                <h1><?= t('about-mission-title') ?> <span><?= t('about-mission-span') ?></span></h1>
                <p class="subtitle"><?= t('about-mission-sub') ?></p>
            </div>
            <div class="uganda_mission">
                <?php if (empty($dynamic_missions)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <h3><?= t('about-mission-empty') ?></h3>
                    </div>
                <?php else: ?>
                    <?php 
                    $counter = 0;
                    foreach ($dynamic_missions as $m): 
                        // Safely pull translated content or fallback to English
                        $db_mission_content = !empty($m['content_' . $current_lang]) ? $m['content_' . $current_lang] : $m['content'];
                        
                        $raw_images = isset($m['images']) && $m['images'] ? $m['images'] : '';
                        $images = array_values(array_filter(array_map('trim', explode(',', $raw_images))));
                        $is_even = ($counter % 2 == 0); 
                    ?>
                        <div class="um_grid scroll-animate">
                            <?php if ($is_even && count($images) > 0): ?>
                                <div class="um_img">
                                    <?php foreach ($images as $idx => $img): ?>
                                        <img src="assets/Images/mission/<?= htmlspecialchars($img) ?>" 
                                             class="img-slide <?= $idx === 0 ? 'active' : '' ?>" alt="mission">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="um_text" <?= count($images) === 0 ? 'style="width: 100%; text-align: center;"' : '' ?>>
                                <p><?= nl2br(htmlspecialchars($db_mission_content)) ?></p>
                            </div>

                            <?php if (!$is_even && count($images) > 0): ?>
                                <div class="um_img">
                                    <?php foreach ($images as $idx => $img): ?>
                                        <img src="assets/Images/mission/<?= htmlspecialchars($img) ?>" 
                                             class="img-slide <?= $idx === 0 ? 'active' : '' ?>" alt="mission">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        $counter++;
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="about_aim" id="aim">
            <div class="about_aim_grid">
                <div class="aim_image reveal-slide-left">
                    <img src="assets/Images/class4.webp" alt="aim">
                    <div class="aim_overlap gradient-dark"></div>
                </div>
                <div class="aim_content reveal-slide-right">
                    <div class="aim-title ">
                        <h2><?= t('about-aim-title') ?></h2>
                    </div>
                    <div class="aim-cards">
                        <div class="card scroll-animate">
                            <p><?= t('about-aim-p1') ?></p>
                        </div>
                        <div class="card scroll-animate">
                            <p><?= t('about-aim-p2') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="about_characters" id="characters">
            <div class="characters_title">
                <h1><?= t('about-char-title') ?></h1>
                <p><?= t('about-char-sub') ?></p>
            </div>
            <div class="characters_grid">
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-place-of-worship"></i></div>
                        <div class="card_title"><?= t('about-char1-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char1-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-seedling"></i></div>
                        <div class="card_title"><?= t('about-char2-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char2-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fas fa-dove"></i></div>
                        <div class="card_title"><?= t('about-char3-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char3-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-users-gear"></i></div>
                        <div class="card_title"><?= t('about-char4-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char4-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-landmark"></i></div>
                        <div class="card_title"><?= t('about-char5-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char5-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-earth-africa"></i></div>
                        <div class="card_title"><?= t('about-char6-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char6-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-hand-holding-heart"></i></div>
                        <div class="card_title"><?= t('about-char7-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char7-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fas fa-leaf"></i></div>
                        <div class="card_title"><?= t('about-char8-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char8-desc') ?></p>
                    </div>
                </div>
                <div class="character_card">
                    <div class="card_header">
                        <div class="card_badger"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="card_title"><?= t('about-char9-title') ?></div>
                    </div>
                    <div class="character-card_content">
                        <p><?= t('about-char9-desc') ?></p>
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
                    <h2><span><?= t('about-support-hands-1') ?></span> <?= t('about-support-hands-2') ?></h2>
                </div>
                <div class="button scroll-animate"><a href="support.php" class="primary-fir"><?= t('about-btn-support') ?></a></div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer-diag.php'; ?>
    <script src="assets/script/script.js"></script>
</body>
</html>