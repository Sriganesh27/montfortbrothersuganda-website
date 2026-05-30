<?php
// 1. Start Session securely by loading our security config FIRST
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/language_manager.php';
// Include database configuration
require_once __DIR__ . '/api/db_config.php';
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
                <img src="assets/Images/prayer.webp" alt="Unit for Peace">
            </div>
            <div class="container sub_hero_content">
                <h1 class="title animate-up"><?= t('unite-hero-title') ?></h1>
                <p class="sub-title animate-up"><?= t('unite-hero-subtitle') ?></p>
            </div>
        </section>

        <section class="unite-message" id="unite-message">
            <div class="unite-image">
                <img src="assets/Images/unite-section.webp" alt="unite-image">
            </div>
            <div class="unite-container">
                <h2 class="animate-up"><?= t('unite-msg-title') ?></h2>
                <p class="reveal-slide-right"><?= t('unite-msg-p1-1') ?><strong><?= t('unite-msg-p1-2') ?></strong>&nbsp; <?= t('unite-msg-p1-3') ?><strong><?= t('unite-msg-p1-4') ?></strong><?= t('unite-msg-p1-5') ?><strong><?= t('unite-msg-p1-6') ?></strong><?= t('unite-msg-p1-7') ?></p>
                <p class="reveal-slide-right"><?= t('unite-msg-p2-1') ?><strong><a href="#unite-prayer"> <?= t('unite-msg-p2-2') ?> </a></strong><?= t('unite-msg-p2-3') ?><strong><?= t('unite-msg-p2-4') ?></strong><?= t('unite-msg-p2-5') ?> <?= t('unite-msg-p2-6') ?><strong><a href="#unite-volunteer"> <?= t('unite-msg-p2-7') ?> </a></strong><?= t('unite-msg-p2-8') ?><strong><a href="support.php"> <?= t('unite-msg-p2-9') ?> </a></strong></p>
            </div>
        </section>

        <section class="unite-prayer" id="unite-prayer">
            <h2 class="animate-up"><?= t('unite-prayer-title') ?></h2>
            <p class="unite-scripture"><em><?= t('unite-prayer-scripture-1') ?></em> <?= t('unite-prayer-scripture-2') ?></p>
            
            <div class="unite-prayer-content">
                <div class="unite-prayer-text">
                    <h3><?= t('unite-prayer-st-francis') ?></h3>
                    <p>
                        <?= t('unite-prayer-l1') ?><br>
                        <?= t('unite-prayer-l2') ?><br>
                        <?= t('unite-prayer-l3') ?><br>
                        <?= t('unite-prayer-l4') ?><br>
                        <?= t('unite-prayer-l5') ?><br>
                        <?= t('unite-prayer-l6') ?><br>
                        <?= t('unite-prayer-l7') ?>
                    </p>
                    <p>
                        <?= t('unite-prayer-l8') ?><br>
                        <?= t('unite-prayer-l9') ?><br>
                        <?= t('unite-prayer-l10') ?><br>
                        <?= t('unite-prayer-l11') ?>
                    </p>
                    <p>
                        <?= t('unite-prayer-l12') ?><br>
                        <?= t('unite-prayer-l13') ?><br>
                        <?= t('unite-prayer-l14') ?>
                    </p>
                    
                    <div class="prayerRead">
                        <div class="caption">
                            <p class="fw-600"><?= t('unite-pray-silently') ?></p>
                        </div>
                        
                        <div class="prayer-action">
                            <button type="button" id="prayBtn" class="pray-button">
                                <i class="fas fa-thumbs-up"></i>
                            </button>
                            
                            <div id="thankYouMsg" class="prayerReadcount" style="display: none;">
                                <p><i class="fas fa-thumbs-up"></i> <?= t('unite-pray-thank-you') ?></p>
                                
                                <p class="count-wrapper">
                                    <span class="count">0</span> <?= t('unite-pray-count-post') ?>
                                </p>
                                
                                <p class="count-wrapper mb-15">
                                    <?= t('unite-pray-minute') ?>
                                </p>
                                
                                <button type="button" id="openPrayerFormBtn" class="primary-btn mt-10-btn">
                                    <?= t('unite-pray-register-btn') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <dialog id="prayerDialog" class="loginpage">
                        <div class="login-container">
                            <button type="button" class="close-auth" id="closePrayer">&times;</button>
                            <div class="auth-step">
                                <h3><?= t('unite-pray-reg-title') ?></h3>
                                <p class="text-center-sub"><?= t('unite-pray-reg-sub') ?></p>
                                
                                <form id="prayerUserForm" action="api/update_prayer.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="action" value="submit_form">
                                    
                                    <div id="newUserFields" class="auth-fields-wrapper">
                                        <input type="text" id="p_name" name="name" placeholder="<?= t('unite-form-fullname') ?>" required>
                                        <input type="email" id="p_email" name="email" placeholder="<?= t('unite-form-email') ?>">
                                        <input type="text" id="p_phone" name="phone" placeholder="<?= t('unite-form-phone') ?>">
                                    </div>

                                    <button type="submit" id="prayerSubmitBtn" class="primary-btn w-100-mt-15"><?= t('unite-form-reg-pray') ?></button>
                                </form>
                            </div>
                        </div>
                    </dialog>

                    <dialog id="prayerStatusDialog" class="loginpage z-10000">
                        <div class="login-container">
                            <button type="button" class="close-auth" id="closePrayerStatusBtnCross">&times;</button>
                            <div id="prayerStatusIcon" class="fs-35-mb-10"></div>
                            <h3 id="prayerStatusTitle" class="mb-10"></h3>
                            <p id="prayerStatusMessage" class="status-msg-text"></p>
                            
                            <div class="flex-gap-10-w-100">
                                <button type="button" class="primary-btn btn-outline-transparent" id="backToFormBtn" style="display: none;"><?= t('unite-status-back') ?></button>
                                <button type="button" class="primary-btn" id="closePrayerStatusBtn"><?= t('unite-status-amen') ?></button>
                            </div>
                        </div>
                    </dialog>
                </div>
                <div class="prayer-image">
                    <img src="assets/Images/St_Francis.webp" alt="St. Francis of Assisi">
                </div>
            </div>
        </section>

        <section class="unite-volunteer" id="unite-volunteer">
            <div class="unite-volunteer-text">
                <h2><?= t('unite-vol-title') ?></h2>
                <p><?= t('unite-vol-p1-1') ?></p>
                
                <ul class="skills-list"  style="padding-left: 20px; margin-top: 15px;">
                    <li style="margin-bottom: 10px;"><strong><?= t('unite-vol-cat-more') ?></strong></li>
                    <li><strong><?= t('unite-vol-cat-skill') ?></strong>
                        <ul class="sub-skills-list"  style=" list-style: none ;margin-left: 20px; margin-top: 5px; font-weight: normal;">
                            <li><?= t('unite-vol-skill-acad') ?></li>
                            <li><?= t('unite-vol-skill-music') ?></li>
                            <li><?= t('unite-vol-skill-sports') ?></li>
                            <li><?= t('unite-vol-skill-dance') ?></li>
                            <li><?= t('unite-vol-skill-life') ?></li>
                            <li><?= t('unite-vol-skill-tech') ?></li>
                        </ul>
                    </li>
                </ul>
                
            </div>
            <div class="volunteer-form">
                <form action="api/volunteer_form.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="text" name="name" placeholder="<?= t('unite-form-fullname') ?>" required>
                    <input type="email" name="email" placeholder="<?= t('unite-form-email') ?>" required>
                    
                    <input type="text" name="number" placeholder="<?= t('unite-form-phone') ?>" required />
                    <div class="form-flex">
                        
                        <select name="skill-category" required>
                            <option value="" disabled selected><?= t('unite-vol-form-skill-sel') ?></option>
                            <option value="MORE"><?= t('unite-vol-cat-more') ?></option>
                            <optgroup label="<?= t('unite-vol-cat-skill') ?>">
                                <option value="academics"><?= t('unite-vol-skill-acad') ?></option>
                                <option value="music"><?= t('unite-vol-skill-music') ?></option>
                                <option value="sports"><?= t('unite-vol-skill-sports') ?></option>
                                <option value="dance"><?= t('unite-vol-skill-dance') ?></option>
                                <option value="life_skills"><?= t('unite-vol-skill-life') ?></option>
                                <option value="technical_training"><?= t('unite-vol-skill-tech') ?></option>
                            </optgroup>
                        </select>
                        
                        <input type="text" name="qualification" placeholder="<?= t('unite-vol-form-qual') ?>" required />
                    </div>
                    <div class="form-flex">
                        <input type="text" name="experience" placeholder="<?= t('unite-vol-form-exp') ?>" required />
                        <select name="experience-years" required>
                            <option value="" disabled selected><?= t('unite-vol-form-exp-sel') ?></option>
                            <option value="Fresher"><?= t('unite-vol-form-fresher') ?></option>
                            <option value="0-1">0-1</option>
                            <option value="1-2">1-2</option>
                            <option value="2-3">2-3</option>
                            <option value="3+">3+</option>
                        </select>
                    </div>
                    <textarea name="intent" placeholder="<?= t('unite-vol-form-intent') ?>" rows="2"></textarea>
                    <button type="submit" class="submit-btn"><?= t('unite-vol-form-submit') ?></button>
                </form>
            </div>
        </section>
        
        <section class="support-hands">
            <div class="support-hand-container">
                <div class="icon-container scroll-animate">
                    <i class="fa fa-hands-helping"></i>
                </div>
                <div class="text-box scroll-animate">
                    <h2><span><?= t('unite-support-hands-1') ?></span> <?= t('unite-support-hands-2') ?></h2>
                </div>
                <div class="button scroll-animate"><a href="support.php" class="primary-fir"><?= t('unite-btn-support') ?></a></div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer-diag.php'; ?>
    <script src="assets/script/script.js"></script>
</body>
</html>