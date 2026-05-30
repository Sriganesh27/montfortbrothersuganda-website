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

try {
    // 🌟 UPDATED: Sort by display_order first
    $highlights = $pdo->query("SELECT * FROM web_gallery_highlights ORDER BY display_order ASC, id ASC")->fetchAll();
} catch (PDOException $e) {
    $highlights = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Gallery | Admin</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="header-flex">
            <h1>Manage Gallery Highlights</h1>
            <button class="action-btn btn-edit" onclick="openModal()">
                <i class="fas fa-plus"></i> Add New Highlight
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Image</th>
                        <th>Caption</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-table="web_gallery_highlights">
                    <?php foreach ($highlights as $h): ?>
                    <tr data-id="<?= $h['id'] ?>">
                        <td>
                            <i class="fas fa-grip-vertical drag-handle" style="cursor: grab; margin-right: 10px; color: #888;"></i>
                            <input type="number" class="manual-order-input" value="<?= isset($h['display_order']) ? $h['display_order'] : 0; ?>" 
                                   data-id="<?= $h['id'] ?>" style="width: 50px; text-align: center;">
                        </td>
                        <td><img src="../assets/Images/gallery_highlights/<?= $h['image'] ?>" width="80" style="border-radius:5px;"></td>
                        <td><strong><?= htmlspecialchars($h['caption']) ?></strong></td>
                        <td>
                            <button class="status-toggle <?= $h['is_active'] ? 'active' : 'inactive' ?>" 
                                    data-id="<?= $h['id'] ?>" 
                                    data-table="web_gallery_highlights">
                                <?= $h['is_active'] ? '<i class="fas fa-eye"></i> Active' : '<i class="fas fa-eye-slash"></i> Hidden' ?>
                            </button>
                        </td>
                        <td>
                            <button class="action-btn btn-edit" onclick='openEditModal(<?= json_encode($h) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" data-id="<?= $h['id'] ?>" data-table="web_gallery_highlights">
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
                <h3>Add New Highlight</h3>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="api/save_highlight.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Caption</label>
                        <input type="text" name="caption" required>
                    </div>
                    <div class="form-group">
                        <label>Gallery Image</label>
                        <input type="file" name="gallery_image" accept="image/*" required onchange="previewImage(event)">
                        <img id="horizon_preview" src="#" style="display:none; width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-top:10px;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-save">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit Highlight</h3>
                <button type="button" class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="api/save_highlight.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Caption</label>
                        <input type="text" name="caption" id="edit_caption" required>
                    </div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="gallery_image" accept="image/*" onchange="previewEditImage(event)">
                        <img id="edit_preview" src="#" style="width:100%; max-height:150px; object-fit:cover; border-radius:8px; margin-top:10px;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn-save">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script src="assets/js/admin_script.js"></script>
    <script>
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_caption').value = data.caption;
            const preview = document.getElementById('edit_preview');
            preview.src = '../assets/Images/gallery_highlights/' + data.image;
            preview.style.display = 'block';
            document.getElementById('editModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html>