<nav class="navbar">
    <div class="navleft">
        <div class="logo">
            <a href="index.php">
                <img src="assets/Images/logo_MBSG_UG_8.webp" alt="logo">
            </a>
        </div>
        <div class="mission">
            <a href="index.php">
                <h1><?= t('navbar-school') ?></h1>
                <p><?= t('navbar-place') ?></p>
            </a>
        </div>
    </div>
    
    <div class="navright">
        <div class="navlinks">
            <ul>
                <li><a href="index.php"><?= t('navbar-home') ?></a></li>
                <li class="dropdown">
                    <a href="about.php"><?= t('navbar-about') ?></a><i class="fa-solid fa-caret-down"></i>
                    <ul class="dropdown-content">
                        <li><a href="about.php#vision"><i class="fa fa-minus"></i> <span><?= t('our-vision') ?></span></a></li>
                        <li><a href="about.php#heritage"><i class="fa fa-minus"></i> <span><?= t('our-heritage') ?></span></a></li>
                        <li><a href="about.php#uganda_mission"><i class="fa fa-minus"></i> <span><?= t('navbar-umission') ?></span></a></li>
                        <li><a href="about.php#aim"><i class="fa fa-minus"></i> <span><?= t('navbar-aim') ?></span></a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="location.php"><?= t('location') ?></a><i class="fa-solid fa-caret-down"></i>
                    <ul class="dropdown-content">
                        <li><a href="location.php#mpala-section" class="location-navigation"><i class="fa fa-minus"></i> <span><?= t('entebbe-mpala') ?></span></a></li>
                        <li><a href="location.php#kyebando-section" class="location-navigation"><i class="fa fa-minus"></i> <span><?= t('jinja-kyebando') ?></span></a></li>
                        <li><a href="location.php#isunga-section" class="location-navigation"><i class="fa fa-minus"></i> <span><?= t('fort-isunga') ?></span></a></li>
                    </ul>
                </li>
                <li><a href="index.php#galleryContainer"><?= t('navbar-highlights') ?></a></li>
                <li class="dropdown">
                    <a href="unite.php"><?= t('navbar-unite') ?></a><i class="fa-solid fa-caret-down"></i>
                    <ul class="dropdown-content">
                        <li><a href="unite.php#unite-prayer"><i class="fa fa-minus"></i> <span><?= t('unite-prayer') ?></span></a></li>
                        <li><a href="unite.php#unite-volunteer"><i class="fa fa-minus"></i> <span><?= t('unite-volunteer') ?></span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
        
        <div class="donate">
            <a href="support.php" class="donate-button"><?= t('navbar-support') ?></a>
        </div>
        
        <div class="language-selection">
            <select id="navbar-lang-selector" class="login-button" style="background: transparent; border: 1px solid currentColor; cursor: pointer; border-radius: 4px; padding: 8px 12px; color: inherit;">
                <option value="en" <?= ($_SESSION['language'] ?? 'en') === 'en' ? 'selected' : '' ?> style="color: #000;">English</option>
                <option value="fr" <?= ($_SESSION['language'] ?? '') === 'fr' ? 'selected' : '' ?> style="color: #000;">Français</option>
                <option value="es" <?= ($_SESSION['language'] ?? '') === 'es' ? 'selected' : '' ?> style="color: #000;">Español</option>
                <option value="it" <?= ($_SESSION['language'] ?? '') === 'it' ? 'selected' : '' ?> style="color: #000;">Italiano</option>
                <option value="de" <?= ($_SESSION['language'] ?? '') === 'de' ? 'selected' : '' ?> style="color: #000;">Deutsch</option>
            </select>
        </div>

        <div class="toggle_btn">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
</nav>
<?php include_once 'login.php'; ?>