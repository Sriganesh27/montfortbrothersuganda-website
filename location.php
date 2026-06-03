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
    <title><?= t('site-title') ?> | <?= t('loc-hero-title') ?></title>
    <link rel="stylesheet" href="assets/styles/refstyle.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <section class="sub-pages ">
                <div class="hero_overlap">
                    <img src="assets/Images/campus5.webp" alt="image">
                </div>
                <div class="container sub_hero_content">
                    <h1 class="title animate-up"><?= t('loc-hero-title') ?></h1>
                    <p class="sub-title animate-up"><?= t('loc-hero-subtitle') ?></p>
                </div>
            </section>
            
            <section class="location-containers kyebando-section" id="kyebando-section">
                            
                <div class="year-wise">
                    <div class="year-controls">
                        <section class="location-navbar" >
                            <ul>
                                <li class="dropdown">
                                    <a href="#kyebando-section" class="location-navigation"><?= t('jinja-kyebando') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#mpala-section" class="location-navigation"><?= t('entebbe-mpala') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#isunga-section" class="location-navigation"><?= t('fort-isunga') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                            </ul>
                        </section>
                        <button class="year-btn  " data-target="kyebando-2023">2023</button>
                        <button class="year-btn" data-target="kyebando-2024">2024</button>
                        <button class="year-btn" data-target="kyebando-2025">2025</button>
                    </div>
                    <div class="year-content-wrapper">
                        <div class="titleSection">
                            <h2><?= t('jinja-kyebando') ?> <span class="year-label">2023</span></h2>
                            <p><?= t('loc-journey-time') ?></p>
                        </div>
                    
                        <div class="year-content  " id="kyebando-2023">
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c">
                                        <img src="assets/Images/kyebando/2023/Meeting with students and staff of St. Kizito School before taking over the school.webp" alt="<?= t('loc-cap-kye-23-1') ?>">
                                    </div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/old black board.webp" alt="<?= t('loc-cap-kye-23-2') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-2') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/Old School Administration.webp" alt="<?= t('loc-cap-kye-23-3') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-3') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/Staff room old.webp" alt="<?= t('loc-cap-kye-23-4') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-4') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/NBR.webp" alt="<?= t('loc-cap-kye-23-5') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-5') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/P1-P3 BR.webp" alt="<?= t('loc-cap-kye-23-6') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-6') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/P4-P7 BR.webp" alt="<?= t('loc-cap-kye-23-7') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-7') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2023/kitchen old.webp" alt="<?= t('loc-cap-kye-23-8') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-23-8') ?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="year-content" id="kyebando-2024">
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c">
                                        <img src="assets/Images/kyebando/2024/Inauguration_Montfort Home_Superior general.webp" alt="<?= t('loc-cap-kye-24-1') ?>">
                                        <div class="date-overlap">
                                            <p>30-01-2024</p>
                                        </div>
                                    </div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/Inauguration_Montfort Home.webp" alt="<?= t('loc-cap-kye-24-2') ?>"><div class="date-overlap">
                                            <p>30-01-2024</p>
                                        </div></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-2') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/Inauguraton and bleesing of St. Montfort home Kyebando 1.webp" alt="<?= t('loc-cap-kye-24-3') ?>"><div class="date-overlap">
                                            <p>30-01-2024</p>
                                        </div></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-3') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/inagra34.webp" alt="<?= t('loc-cap-kye-24-3') ?>"><div class="date-overlap">
                                            <p>30-01-2024</p>
                                        </div></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-3') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/St. Kizito_Superior General with students.webp" alt="<?= t('loc-cap-kye-24-4') ?>">
                                        </div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-4') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/St. Kizito N&P Kyebando 2024.webp" alt="<?= t('loc-cap-kye-24-5') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-5') ?></span></div>
                                </div>
                                
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/games1.webp" alt="<?= t('loc-cap-kye-24-6') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-6') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/games ky 3.webp" alt="<?= t('loc-cap-kye-24-7') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-7') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2024/games ky 2.webp" alt="<?= t('loc-cap-kye-24-8') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-24-8') ?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="year-content" id="kyebando-2025">
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/class room new.webp" alt="<?= t('loc-cap-kye-25-1') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/Old School Administration new1.webp" alt="<?= t('loc-cap-kye-25-2') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-2') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/Staff room new.webp" alt="<?= t('loc-cap-kye-25-3') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-3') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/NAR.webp" alt="<?= t('loc-cap-kye-25-4') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-4') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/P1-P3 AR.webp" alt="<?= t('loc-cap-kye-25-5') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-5') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/P4-P7 AR.webp" style="filter: brightness(150%);" alt="<?= t('loc-cap-kye-25-6') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-6') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/NEW DINING HALL AND KITCHEN.webp" alt="<?= t('loc-cap-kye-25-7') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-7') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/NEW DINING HALL AND KITCHEN1.webp" alt="<?= t('loc-cap-kye-25-7') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-7') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/Nursery Graduation.webp" alt="<?= t('loc-cap-kye-25-8') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-8') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/kyebando/2025/Nango stella.webp" alt="<?= t('loc-cap-kye-25-9') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-kye-25-9') ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="location-containers mpala" id="mpala-section">
                <div class="year-wise">
                    <div class="year-controls">
                        <section class="location-navbar" >
                            <ul>
                                <li class="dropdown">
                                    <a href="#kyebando-section" class="location-navigation"><?= t('jinja-kyebando') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#mpala-section" class="location-navigation"><?= t('entebbe-mpala') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#isunga-section" class="location-navigation"><?= t('fort-isunga') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                            </ul>
                        </section>
                        <button class="year-btn  " data-target="mpala-2023">2023</button>
                        <button class="year-btn" data-target="mpala-2024">2024</button>
                        <button class="year-btn" data-target="mpala-2025">2025</button>
                    </div>
                    <div class="year-content-wrapper">
                        <div class="titleSection">
                    <h2><?= t('entebbe-mpala') ?> <span class="year-label">2023</span></h2>
                    <p><?= t('loc-journey-time') ?></p>
                </div>
                        <div class="year-content  " id="mpala-2023">
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2023/local dingitaries at Mpala.webp" alt="<?= t('loc-cap-mpa-23-1') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-23-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2023/Mpala foundation stone.webp" alt="<?= t('loc-cap-mpa-23-2') ?>">
                                <div class="date-overlap">
                                            <p>19-07-2023</p>
                                        </div>    
                                </div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-23-2') ?></span></div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="year-content" id="mpala-2024">
                            
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Superior General Br.John Kallarackal Felicitating Rev. Paul Ssemogere of the Archdiocese of Kampala.webp" alt="<?= t('loc-cap-mpa-24-1') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Visiting Brothers with  Fr. Joseph Kirumira Parish Priest Mpala.jpeg" alt="<?= t('loc-cap-mpa-24-2') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-2') ?></span></div>
                                </div>
                                
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Montfort Home, Mpala Grateful to Romain Landry Foundation for financial support.webp" alt="<?= t('loc-cap-mpa-24-3') ?>">
                                    <div class="date-overlap">
                                            <p>08-05-2024</p>
                                        </div> 
                                </div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-3') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Montfort Home, Mpala inauguration.webp" alt="<?= t('loc-cap-mpa-24-4') ?>">
                                    <div class="date-overlap">
                                            <p>08-05-2024</p>
                                        </div> 
                                </div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-4') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Mpala montfort home inauguration 1.webp" alt="<?= t('loc-cap-mpa-24-4') ?>">
                                    <div class="date-overlap">
                                            <p>08-05-2024</p>
                                        </div> 
                                </div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-4') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2024/Mpala Montfort Home Inauguration 3.webp" alt="<?= t('loc-cap-mpa-24-4') ?>">
                                    <div class="date-overlap">
                                            <p>08-05-2024</p>
                                        </div> 
                                </div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-24-4') ?></span></div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="year-content" id="mpala-2025">
                        
                            <div class="image-grid-container">
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2025/Arrival of Bro.Theophile.webp" alt="<?= t('loc-cap-mpa-25-1') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-25-1') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2025/Brother's Residence Mpala-2.webp" alt="<?= t('loc-cap-mpa-25-2') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-25-2') ?></span></div>
                                </div>
                                <div class="image-grid">
                                    <div class="image-c"><img src="assets/Images/Mpala/2025/Christmas 2025 Mpala.webp" alt="<?= t('loc-cap-mpa-25-3') ?>"></div>
                                    <div class="text-c"><span><?= t('loc-cap-mpa-25-3') ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="location-containers Isunga" id="isunga-section">
                <div class="year-wise">
                    <div class="year-controls">
                        <section class="location-navbar" >
                            <ul>
                                <li class="dropdown">
                                    <a href="#kyebando-section" class="location-navigation"><?= t('jinja-kyebando') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#mpala-section" class="location-navigation"><?= t('entebbe-mpala') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#isunga-section" class="location-navigation"><?= t('fort-isunga') ?><i class="fas fa-angle-down"></i></a>
                                </li>
                            </ul>
                        </section>
                                    
                        <button class="year-btn " data-target="isunga-2022">2022</button>
                        <button class="year-btn" data-target="isunga-2026">2026</button>
                    </div>
                    <div class="year-content-wrapper">
                        <div class="titleSection">
                            <h2><?= t('fort-isunga') ?> <span class="year-label">2022</span></h2>
                            <p><?= t('loc-journey-time') ?></p>
                            </div>
                            <div class="year-content   " id="isunga-2022">
                                    <div class="image-grid-container">
                                        <div class="image-grid">
                                            <div class="image-c"><img src="assets/Images/Fort Portal/2022/Brothers' First visit to Fort Portal _March 2022.webp" alt="<?= t('loc-cap-isu-22-1') ?>"></div>
                                            <div class="text-c"><span><?= t('loc-cap-isu-22-1') ?></span></div>
                                        </div>
                                        <div class="image-grid">
                                            <div class="image-c"><img src="assets/Images/Fort Portal/2022/Fort Portal 2022.jpg.webp" alt="<?= t('loc-cap-isu-22-2') ?>"></div>
                                            <div class="text-c"><span><?= t('loc-cap-isu-22-2') ?></span></div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="year-content " id="isunga-2026">
                                    <div class="image-grid-container">
                                        <div class="image-grid">
                                            <div class="image-c"><img src="assets/Images/Fort Portal/2026/Assistant General Fort Portal 20261.webp" alt="<?= t('loc-cap-isu-26-1') ?>">
                                        <div class="date-overlap">
                                            <p>10.02.2026</p>
                                        </div> </div>
                                            <div class="text-c"><span><?= t('loc-cap-isu-26-1') ?></span></div>
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