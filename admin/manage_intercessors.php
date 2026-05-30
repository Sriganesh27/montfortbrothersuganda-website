<?php
session_start();

// 1. EXACT Auth Check (Matches your admin_dashboard.php perfectly)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); 
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Include Database Configuration
require_once '../api/db_config.php';

// 3. Fetch Statistics safely
$totalUsers = 0;
$totalPrayers = 0;
$prayersToday = 0;
$intercessors = [];

try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM web_prayer_users")->fetchColumn() ?: 0;
    $totalPrayers = $pdo->query("SELECT total_prayers FROM web_prayer_stats WHERE id = 1")->fetchColumn() ?: 0;
    $prayersToday = $pdo->query("SELECT COUNT(*) FROM web_prayer_users WHERE DATE(last_prayed) = CURDATE()")->fetchColumn() ?: 0;
    
    // Fetch All Intercessors
    $stmt = $pdo->query("SELECT * FROM web_prayer_users ORDER BY last_prayed DESC");
    $intercessors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_msg = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Prayer Community - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/styles/style.css">
    <link rel="stylesheet" href="assets/css/admin_style.css">
    
    <style>
        .admin-content { padding: 30px; width: 100%; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid var(--text-color2); display: flex; align-items: center; justify-content: space-between; }
        .stat-card h3 { font-size: 0.9rem; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .stat-card .value { font-size: 2rem; font-weight: bold; color: var(--primary-color); }
        .stat-card i { font-size: 2.5rem; color: rgba(2, 26, 63, 0.1); }
        
        .table-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x: auto; }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .export-btn { background: #28a745; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; cursor: pointer; border:none; transition: 0.3s; }
        .export-btn:hover { background: #218838; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: var(--primary-color); font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8f9fa; }
        
        .badge { padding: 4px 8px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .badge.active { background: #e3f2fd; color: #007bff; }
        .contact-info { display: flex; flex-direction: column; font-size: 0.85rem; color: #555; }

        /* Action Buttons */
        .btn-action { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: 0.2s; margin-right: 5px; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }

        /* Admin Modal Styles */
        .admin-modal { border: none; border-radius: 12px; padding: 30px; width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin: auto; }
        .admin-modal::backdrop { background: rgba(0,0,0,0.5); backdrop-filter: blur(3px); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: var(--primary-color); font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-save { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-cancel { background: #e0e0e0; color: #333; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body style="display: flex; background: #f4f7f6;">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-flex">
            <h1>Prayer Community Management</h1>
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['web_name'] ?? 'Admin'); ?></strong></span>
        </div>

        <?php if (isset($error_msg)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 5px solid #007bff;">
                <div>
                    <h3>Total Intercessors</h3>
                    <div class="value"><?= number_format($totalUsers) ?></div>
                </div>
                <i class="fas fa-users" style="color: rgba(0, 123, 255, 0.1);"></i>
            </div>
            <div class="stat-card" style="border-left: 5px solid #28a745;">
                <div>
                    <h3>Global Prayers Read</h3>
                    <div class="value"><?= number_format($totalPrayers) ?></div>
                </div>
                <i class="fas fa-globe" style="color: rgba(40, 167, 69, 0.1);"></i>
            </div>
            <div class="stat-card" style="border-left: 5px solid #ffc107;">
                <div>
                    <h3>Prayed Today</h3>
                    <div class="value"><?= number_format($prayersToday) ?></div>
                </div>
                <i class="fas fa-sun" style="color: rgba(255, 193, 7, 0.1);"></i>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3 style="color: var(--primary-color); margin: 0;">Registered Intercessors</h3>
                <button type="button" onclick="exportTableToCSV('prayer_community.csv')" class="export-btn"><i class="fas fa-download"></i> Export CSV</button>
            </div>
            
            <table id="prayerTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Details</th>
                        <th>Gender</th>
                        <th>Total Prayers</th>
                        <th>Last Prayed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($intercessors)): ?>
                        <tr><td colspan="6" style="text-align: center;">No intercessors registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($intercessors as $user): ?>
                        <tr id="row-<?= $user['id'] ?>">
                            <td class="td-name" style="font-weight: 600; color: var(--primary-color);">
                                <?= htmlspecialchars($user['full_name']) ?>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <?php if($user['email']): ?><span><i class="fas fa-envelope"></i> <span class="td-email"><?= htmlspecialchars($user['email']) ?></span></span><?php endif; ?>
                                    <?php if($user['phone']): ?><span><i class="fas fa-phone"></i> <span class="td-phone"><?= htmlspecialchars($user['phone']) ?></span></span><?php endif; ?>
                                </div>
                            </td>
                            <td class="td-gender"><?= htmlspecialchars($user['gender']) ?></td>
                            <td><span class="badge active"><span class="td-count"><?= $user['prayer_count'] ?></span> times</span></td>
                            <td style="font-size: 0.9rem;"><?= date('M j, Y h:i A', strtotime($user['last_prayed'])) ?></td>
                            <td>
                                <button type="button" class="btn-action btn-edit" onclick="openEditModal(<?= $user['id'] ?>)"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn-action btn-delete" onclick="deleteUser(<?= $user['id'] ?>)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="editPrayerModal" class="admin-modal">
        <h3 style="margin-bottom: 20px; color: var(--primary-color); border-bottom: 2px solid #eee; padding-bottom: 10px;">Edit Intercessor</h3>
        <form id="editPrayerForm">
            <input type="hidden" id="edit_id" name="id">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="edit_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="edit_email" name="email">
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="edit_phone" name="phone">
            </div>
            
            <div class="form-group">
                <label>Gender</label>
                <select id="edit_gender" name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Total Prayer Count</label>
                <input type="number" id="edit_count" name="prayer_count" min="1" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('editPrayerModal').close()">Cancel</button>
                <button type="submit" class="btn-save" id="saveEditBtn">Save Changes</button>
            </div>
        </form>
    </dialog>

    <script src="assets/js/admin_script.js"></script>
    <script>
    const modal = document.getElementById('editPrayerModal');
    const form = document.getElementById('editPrayerForm');

    // Fetch and Open Edit Modal
    async function openEditModal(id) {
        try {
            const response = await fetch(`api/prayer_actions.php?action=get_user&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                const user = result.data;
                document.getElementById('edit_id').value = user.id;
                document.getElementById('edit_name').value = user.full_name;
                document.getElementById('edit_email').value = user.email || '';
                document.getElementById('edit_phone').value = user.phone || '';
                document.getElementById('edit_gender').value = user.gender;
                document.getElementById('edit_count').value = user.prayer_count;
                
                modal.showModal();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Failed to fetch user data.');
        }
    }

    // Submit Edit Form via AJAX
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveEditBtn');
        const originalText = saveBtn.innerText;
        saveBtn.innerText = "Saving...";
        saveBtn.disabled = true;

        try {
            const formData = new FormData(this);
            const response = await fetch('api/prayer_actions.php?action=update_user', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                const id = document.getElementById('edit_id').value;
                const row = document.getElementById(`row-${id}`);
                
                row.querySelector('.td-name').innerText = document.getElementById('edit_name').value;
                row.querySelector('.td-gender').innerText = document.getElementById('edit_gender').value;
                row.querySelector('.td-count').innerText = document.getElementById('edit_count').value;
                
                const emailSpan = row.querySelector('.td-email');
                if(emailSpan) emailSpan.innerText = document.getElementById('edit_email').value;
                
                const phoneSpan = row.querySelector('.td-phone');
                if(phoneSpan) phoneSpan.innerText = document.getElementById('edit_phone').value;

                modal.close();
                alert('Record updated successfully!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('An error occurred while saving.');
        } finally {
            saveBtn.innerText = originalText;
            saveBtn.disabled = false;
        }
    });

    // Delete User Logic
    async function deleteUser(id) {
        if (!confirm("Are you sure you want to permanently delete this intercessor's record?")) return;

        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch('api/prayer_actions.php?action=delete_user', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById(`row-${id}`).remove();
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('An error occurred while deleting.');
        }
    }

    // CSV Export
    function exportTableToCSV(filename) {
        let csv = [];
        let rows = document.querySelectorAll("table tr");
        
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td:not(:last-child), th:not(:last-child)");
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""').trim();
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }
        
        let csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
        let downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }
    </script>
</body>
</html>