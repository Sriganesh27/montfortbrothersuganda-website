<?php
session_start();
// Security: Kick out non-admins back to the main site home
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php"); 
    exit;
}

// ⭐ 1. ADD THIS: Generate CSRF token for this page
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../api/db_config.php';
require_once '../api/db_config.php'; //

// Fetch all statistics ordered by year
try {
    $results = $pdo->query("SELECT * FROM web_school_results ORDER BY year ASC, id ASC")->fetchAll();
} catch (PDOException $e) {
    error_log("Results Fetch Error: " . $e->getMessage());
    $results = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Statistics | Admin</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header-flex">
            <h1><i class="fas fa-chart-line"></i> School Statistics</h1>
            <button class="action-btn btn-edit" onclick="openModal()">
                <i class="fas fa-plus"></i> Add New Record
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Institution</th>
                        <th>Training (M/F)</th>
                        <th>Course & Cert</th>
                        <th>Graduated (M/F)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['year']) ?></strong></td>
                        <td><?= htmlspecialchars($r['institution']) ?></td>
                        <td><span class="text-muted"><?= $r['train_male'] ?>M</span> / <span class="text-muted"><?= $r['train_female'] ?>F</span></td>
                        <td>
                            <div style="font-size: 0.9rem; font-weight: 600;"><?= htmlspecialchars($r['course']) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($r['certificate']) ?></div>
                        </td>
                        <td><span class="accent-text"><?= $r['grad_male'] ?>M</span> / <span class="accent-text"><?= $r['grad_female'] ?>F</span></td>
                        <td>
                            <button class="action-btn btn-edit" onclick='openEditModal(<?= json_encode($r) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" data-id="<?= $r['id'] ?>" data-table="web_school_results">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle" style="color: var(--text-color2);"></i> Add Statistic</h3>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addResultForm" action="api/save_result.php" method="POST">
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Year</label>
                            <input type="text" name="year" placeholder="e.g. 2024" required>
                        </div>
                        <div class="form-group">
                            <label>Institution</label>
                            <input type="text" name="institution" placeholder="e.g. St. Kizito" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="course" placeholder="e.g. Primary Leaving Exam">
                    </div>
                    <div class="form-group">
                        <label>Certificate Type</label>
                        <input type="text" name="certificate" placeholder="e.g. PLE">
                    </div>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group"><label>Train (M)</label><input type="number" name="train_male" value="0"></div>
                        <div class="form-group"><label>Train (F)</label><input type="number" name="train_female" value="0"></div>
                        <div class="form-group"><label>Grad (M)</label><input type="number" name="grad_male" value="0"></div>
                        <div class="form-group"><label>Grad (F)</label><input type="number" name="grad_female" value="0"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="padding: 20px 30px; border-top: 1px solid var(--border-color2); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" form="addResultForm" class="btn-save">Save Record</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: var(--text-color2);"></i> Edit Statistic</h3>
                <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editResultForm" action="api/save_result.php" method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group"><label>Year</label><input type="text" name="year" id="edit_year" required></div>
                        <div class="form-group"><label>Institution</label><input type="text" name="institution" id="edit_institution" required></div>
                    </div>
                    <div class="form-group"><label>Course</label><input type="text" name="course" id="edit_course"></div>
                    <div class="form-group"><label>Certificate</label><input type="text" name="certificate" id="edit_certificate"></div>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group"><label>Train (M)</label><input type="number" name="train_male" id="edit_tm"></div>
                        <div class="form-group"><label>Train (F)</label><input type="number" name="train_female" id="edit_tf"></div>
                        <div class="form-group"><label>Grad (M)</label><input type="number" name="grad_male" id="edit_gm"></div>
                        <div class="form-group"><label>Grad (F)</label><input type="number" name="grad_female" id="edit_gf"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="padding: 20px 30px; border-top: 1px solid var(--border-color2); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" form="editResultForm" class="btn-save">Update Record</button>
            </div>
        </div>
    </div>

    <script src="assets/js/admin_script.js"></script>
    <script>
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_year').value = data.year;
            document.getElementById('edit_institution').value = data.institution;
            document.getElementById('edit_course').value = data.course;
            document.getElementById('edit_certificate').value = data.certificate;
            document.getElementById('edit_tm').value = data.train_male;
            document.getElementById('edit_tf').value = data.train_female;
            document.getElementById('edit_gm').value = data.grad_male;
            document.getElementById('edit_gf').value = data.grad_female;
            
            document.getElementById('editModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html>