<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="navbar">
    <div class="left">
        <button id="menu-toggle" aria-label="Toggle menu"><i class="fa fa-bars"></i></button>
        <button id="profile" aria-label="User profile"><i class="fa fa-user"></i></button>
    </div>
    <div class="middle">
        <a href="index.php">
            <h1>
                <?php 
                    // Use the session variable set during login
                    echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'Montfort School'; 
                ?>
            </h1>
        </a>
    </div>
    <div class="right">
        <button id="fullscreen-btn" aria-label="Toggle fullscreen" class="navbar-btn"><i class="fa fa-expand"></i></button>
        <button id="settings-btn" aria-label="Toggle settings" class="navbar-btn"><i class="fa fa-cog"></i></button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
