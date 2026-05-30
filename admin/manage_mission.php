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

$missions = $pdo->query("SELECT * FROM web_uganda_mission ORDER BY display_order ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Manage Mission | Admin</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Specific UI Fixes to perfectly integrate with your theme */
        .modal-form-wrapper {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0; /* Forces the body to scroll instead of stretching the modal */
        }
        .img-thumbnail {
            width: 45px;
            height: 45px;
            object-fit: cover; /* Prevents squished images */
            border-radius: 6px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color2);
        }
        .photo-count-badge {
            background: var(--glass-blue);
            color: var(--primary-color);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: var(--weight-bold);
            margin-left: 10px;
            border: 1px solid var(--primary-light);
        }
        .table-img-cell {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header-flex">
            <h1>Uganda Mission Journal</h1>
            <button class="action-btn btn-edit" onclick="openModal()" style="background: var(--primary-color); color: white; padding: 10px 20px; border-radius: 8px;">
                <i class="fas fa-plus"></i> Add Entry
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order</th> 
                        <th>Gallery</th>
                        <th>Journal Content</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-table="web_uganda_mission"> 
                    <?php if (empty($missions)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 20px;">No mission entries found. Click 'Add Entry' to start.</td></tr>
                    <?php else: ?>
                        <?php foreach ($missions as $m): 
                            // Safely filter out empty strings and re-index the array
                            $imgs = array_filter(explode(',', $m['images']));
                            $first_img = !empty($imgs) ? trim(array_values($imgs)[0]) : '';
                        ?>
                        <tr data-id="<?= $m['id'] ?>"> 
                            
                            <td>
                                <i class="fas fa-grip-vertical drag-handle" style="cursor: grab; margin-right: 10px; color: #888;"></i>
                                <input type="number" class="manual-order-input" value="<?= isset($m['display_order']) ? $m['display_order'] : 0; ?>" 
                                       data-id="<?= $m['id'] ?>" style="width: 50px; text-align: center;">
                            </td>

                            <td>
                                <div class="table-img-cell">
                                    <?php if(!empty($first_img)): ?>
                                        <img src="../assets/Images/mission/<?= htmlspecialchars($first_img) ?>" class="img-thumbnail" alt="Mission">
                                    <?php else: ?>
                                        <div class="img-thumbnail" style="background: #eee; display:flex; align-items:center; justify-content:center;"><i class="fas fa-image" style="color:#aaa;"></i></div>
                                    <?php endif; ?>
                                    <span class="photo-count-badge"><i class="fas fa-images"></i> <?= count($imgs) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars(mb_strimwidth($m['content'], 0, 80, "...")) ?></td>
                            <td>
                                <button class="status-toggle <?= $m['is_active'] ? 'active' : 'inactive' ?>" 
                                        data-id="<?= $m['id'] ?>" data-table="web_uganda_mission">
                                    <?= $m['is_active'] ? '<i class="fas fa-eye"></i> Active' : '<i class="fas fa-eye-slash"></i> Hidden' ?>
                                </button>
                            </td>
                            <td>
                                <button class="action-btn btn-edit" onclick="openMissionEdit(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn btn-delete" data-id="<?= $m['id'] ?>" data-table="web_uganda_mission">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Add Mission Entry</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form action="api/save_mission.php" method="POST" enctype="multipart/form-data" class="modal-form-wrapper">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Journal Content</label>
                        <textarea name="content" required rows="5" placeholder="Write about the mission..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Upload Multiple Images</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="mission_images[]" multiple required accept="image/*" onchange="previewMultipleImages(event, 'add_preview_container')" style="border:none; background:transparent;">
                            <div class="file-hint">Select multiple files (JPG, PNG, WEBP).</div>
                        </div>
                        <div id="add_preview_container" class="preview-grid mt-2"></div>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="btn-save">Save Entry</button>
                </div>
                </div>
                
            </form>
        </div>
    </div>

    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit Mission Entry</h3>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form action="api/save_mission.php" method="POST" enctype="multipart/form-data" class="modal-form-wrapper">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Journal Content</label>
                        <textarea name="content" id="edit_content" required rows="5"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Manage Existing Images</label>
                        <div class="file-upload-wrapper" style="text-align: left;">
                            <div id="edit_existing_images" class="preview-grid"></div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Add More Images (Optional)</label>
                        <input type="file" name="mission_images[]" multiple accept="image/*" onchange="previewMultipleImages(event, 'edit_preview_container')">
                        <p class="help-text" style="color: var(--accent-green);">
                            <i class="fas fa-info-circle"></i> New files uploaded here will be added to the existing images above.
                        </p>
                        <div id="edit_preview_container" class="preview-grid mt-2"></div>
                    </div>
                    <div class="modal-footer">
                    <button type="submit" class="btn-save">Update Entry</button>
                </div>
                </div>
                
            </form>
        </div>
    </div>

    <script src="assets/js/admin_script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        function openMissionEdit(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_content').value = data.content;
            
            const existingContainer = document.getElementById('edit_existing_images');
            existingContainer.innerHTML = ''; 
            
            if (data.images && data.images.trim() !== '') {
                const imgArray = data.images.split(',');
                imgArray.forEach(img => {
                    const trimmedImg = img.trim();
                    if(trimmedImg !== '') {
                        const safeId = trimmedImg.replace(/\./g, '-');
                        existingContainer.innerHTML += `
                            <div class="preview-item existing-img" id="img-wrapper-${safeId}">
                                <img src="../assets/Images/mission/${trimmedImg}" alt="Mission">
                                <button type="button" class="btn-delete-single" onclick="deleteSingleImage(${data.id}, '${trimmedImg}')" title="Delete">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    }
                });
            } else {
                existingContainer.innerHTML = '<p class="text-muted" style="font-size: 0.85rem; margin:0;">No images attached.</p>';
            }

            document.getElementById('edit_preview_container').innerHTML = '';
            document.getElementById('editModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html>