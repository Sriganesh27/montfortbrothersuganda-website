<?php
// SECURITY/SEO FIX: Ensure the server sends a strict 404 status code
http_response_code(404);

require_once __DIR__ . '/includes/language_manager.php'; // ADDED TRANSLATION MANAGER

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? $_COOKIE['site_lang'] ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= t('site-title') ?>">
    <link rel="icon" href="assets/Images/logo_MBSG_UG_8.webp">
    <title><?= t('site-title') ?> - 404</title>
    <link rel="stylesheet" href="assets/styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section class="error-section">
            <div class="error-container">
                <h1>404</h1>
                <h2><?= t('404-title') ?></h2>
                <p><?= t('404-desc') ?></p>
                <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;">
                    <a href="index.php" class="primary-fir"><?= t('404-btn-home') ?></a>
                    <a href="about.php" class="primary-fir" style="background: transparent; border: 1px solid var(--primary-color);"><?= t('404-btn-about') ?></a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer-diag.php'; ?>
    <script src="assets/script/script.js"></script>
</body>
</html>