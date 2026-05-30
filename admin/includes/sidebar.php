<div class="sidebar">
    <div class="sidebar-header">
        <h3>Montfort Admin</h3>
    </div>
    <ul class="nav-links">
        <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
        <li><a href="manage_results.php"><i class="fa-solid fa-graduation-cap"></i> Results</a></li>
        <li><a href="manage_horizons.php"><i class="fa-solid fa-tree"></i> Horizons</a></li>
        <li><a href="manage_gallery.php"><i class="fa-solid fa-images"></i> Gallery</a></li>
        <li><a href="manage_mission.php"><i class="fa fa-line-chart"></i> Uganda Mission</a></li>
        <li>
            <a href="manage_intercessors.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_intercessors.php') ? 'active' : ''; ?>">
                <i class="fas fa-praying-hands"></i>
                <span>Prayer Community</span>
            </a>
        </li>
        <li><a href="manage_donations.php"><i class="fa-solid fa-hand-holding-dollar"></i> Donations</a></li>
        
        <!-- NEW: Fund Distribution Link -->
        <li><a href="fund_distribution.php"><i class="fa-solid fa-money-bill-transfer"></i> Fund Distribution</a></li>
        <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Users</a></li>
        <li><a href="manage_messages.php"><i class="fa-solid fa-envelope"></i> Messages</a></li>
        <li><a href="manage_volunteers.php"><i class="fa-solid fa-handshake-angle"></i> Volunteers</a></li>
        
        <li><a href="../api/logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>