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
require_once '../api/db_config.php';

// Fetch all horizons
try {
    $stmt = $pdo->query("SELECT * FROM web_horizons ORDER BY display_order ASC, id DESC");
    $horizons = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Horizons Fetch Error: " . $e->getMessage());
    $horizons = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Horizons | Admin</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="header-flex">
            <h1>Manage Horizons</h1>
            <button class="action-btn btn-edit" onclick="document.getElementById('addModal').style.display='block'">
                <i class="fas fa-plus"></i> New Horizon
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order</th> <th>Image</th>
                        <th>Title</th>
                        <th>Label</th>
                        <th>Caption</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-table="web_horizons"> <?php foreach ($horizons as $h): ?>
                    <tr data-id="<?php echo $h['id']; ?>"> <td>
                            <i class="fas fa-grip-vertical drag-handle" style="cursor: grab; margin-right: 10px; color: #888;"></i>
                            <input type="number" class="manual-order-input" value="<?php echo isset($h['display_order']) ? $h['display_order'] : 0; ?>" 
                                   data-id="<?php echo $h['id']; ?>" style="width: 50px; text-align: center;">
                        </td>
                        <td><img src="../assets/Images/horizon/<?php echo htmlspecialchars($h['image']); ?>" width="80" style="border-radius:5px;"></td>
                        <td><strong><?php echo htmlspecialchars($h['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($h['label']); ?></td>
                        <td><?php echo substr(htmlspecialchars($h['caption']), 0, 50) . '...'; ?></td>
    <td>
    <button class="status-toggle <?php echo $h['is_active'] ? 'active' : 'inactive'; ?>" 
            data-id="<?php echo $h['id']; ?>" 
            data-table="web_horizons"> <?php echo $h['is_active'] ? '<i class="fas fa-eye"></i> Active' : '<i class="fas fa-eye-slash"></i> Hidden' ?>
    </button>
</td>
    <td>
        <button class="action-btn btn-edit" onclick='openEditModal(<?php echo json_encode($h); ?>)'>
            <i class="fas fa-edit"></i>
        </button>
        <button class="action-btn btn-delete" data-id="<?php echo $h['id']; ?>" data-table="web_horizons">
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
            <h3><i class="fas fa-mountain-sun" style="color: var(--text-color2); margin-right: 10px;"></i> Add New Horizon</h3>
            <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="horizonForm" action="api/save_horizon.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Horizon Title</label>
                    <input type="text" name="title" placeholder="e.g., Skill Development" required>
                </div>

                <div class="form-group">
                    <label>Image Label</label>
                    <input type="text" name="label" placeholder="e.g., Striving">
                </div>

                <div class="form-group">
                    <label>Short Caption</label>
                    <textarea name="caption" rows="4" placeholder="Enter description..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Featured Image</label>
                    <div class="file-upload-wrapper">
                        <img id="horizon_preview" src="#" alt="Preview" style="display:none; width:100%; max-height:200px; object-fit:cover; border-radius:8px; margin-bottom:10px; border: 1px solid var(--border-color2);">
                        <input type="file" name="horizon_image" accept="image/*" required onchange="previewImage(event)">
                        <p class="file-hint">Use 1200x800px for best results.</p>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-header" style="background: var(--bg-white); border-top: 1px solid var(--border-color2); border-bottom: none; padding: 15px 30px;">
            <div class="modal-footer" style="width: 100%; margin: 0;">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" form="horizonForm" class="btn-save">Create Horizon</button>
            </div>
        </div>
    </div>
</div>
<div id="editModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fas fa-edit" style="color: var(--text-color2); margin-right: 10px;"></i> Edit Horizon</h3>
            <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editHorizonForm" action="api/save_horizon.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Horizon Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Image Label</label>
                    <input type="text" name="label" id="edit_label">
                </div>
                <div class="form-group">
                    <label>Short Caption</label>
                    <textarea name="caption" id="edit_caption" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Current/New Image</label>
                    <div class="file-upload-wrapper">
                        <img id="edit_preview" src="#" alt="Preview" style="width:100%; max-height:200px; object-fit:cover; border-radius:8px; margin-bottom:10px; border: 1px solid var(--border-color2);">
                        <input type="file" name="horizon_image" accept="image/*" onchange="previewEditImage(event)">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-header" style="background: var(--bg-white); border-top: 1px solid var(--border-color2); border-bottom: none; padding: 15px 30px;">
            <div class="modal-footer" style="width: 100%; margin: 0;">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" form="editHorizonForm" class="btn-save">Update Horizon</button>
            </div>
        </div>
    </div>
</div>
    <script src="assets/js/admin_script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
</body>
</html>