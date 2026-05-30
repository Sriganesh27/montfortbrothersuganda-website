<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// web/erp/api/students/get_student_profile.php
require_once __DIR__ . '/../../config/config.php';

// 1. Get branch_id from session for multi-branch support
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$branch_id = $_SESSION['branch_id'] ?? null;

$Ad_no = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$Ad_no) {
    echo "<div class='error-message' style='color:var(--danger-color); padding:20px; text-align:center;'>Error: Admission No is required.</div>";
    exit;
}

if (!$branch_id) {
    echo "<div class='error-message' style='color:var(--danger-color); padding:20px; text-align:center;'>Error: Unauthorized. No branch selected.</div>";
    exit;
}

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$student_data = null;

if ($conn) {
    // 2. Pass $branch_id as the 3rd argument here!
    $student_data = fetch_full_student_data($conn, $Ad_no, $branch_id);
    $conn->close();
}

if (!$student_data) {
    echo "<div class='error-message' style='color:var(--danger-color); padding:20px; text-align:center;'>Error: Student not found in this branch.</div>";
    exit;
}

$format = fn($value) => htmlspecialchars($value ?? 'N/A');
$val = fn($value) => htmlspecialchars($value ?? ''); 
$photo_src = !empty($student_data['PhotoPath']) ? $student_data['PhotoPath'] : 'static/images/default_profile.png';
$full_name = $format($student_data['Name']) . ' ' . $format($student_data['Surname']);
?>

<div class="viewer-container">
    <div class="top-action-bar">
        <button id="edit-btn" onclick="togglePageEditMode('edit')" class="btn-primary"><i class="fa fa-edit"></i> Edit Details</button>
        
        <div id="save-cancel-group" style="display:none; gap: 10px;">
            <button onclick="submitEditForm()" class="btn-primary" style="background-color: var(--success-color);"><i class="fa fa-save"></i> Save Changes</button>
            <button onclick="togglePageEditMode('view')" class="btn-primary" style="background-color: #6c757d;"><i class="fa fa-times-circle"></i> Cancel</button>
        </div>
        
        <button onclick="printStudentProfile(<?php echo $val($Ad_no); ?>)" class="btn-primary" style="background-color: #17a2b8;">
            <i class="fa fa-print"></i> Print
        </button>
        
        <div style="flex-grow: 1;"></div>
        <button class="btn-primary" onclick="window.showDeleteConfirmModal('<?php echo $val($Ad_no); ?>', '<?php echo $full_name; ?>')" style="background-color: var(--danger-color);"><i class="fa fa-trash"></i> Delete</button>
    </div>

    <div class="unified-frame single-column-layout">
        <div class="profile-header-inline">
            <div class="header-info">
                <h3 id="display-name-header"><?php echo $full_name; ?></h3>
                <p class="id-badge">Adm-no: <strong><?php echo str_pad($Ad_no, 4, '0', STR_PAD_LEFT); ?></strong></p>
                <p class="status-badge">
                    <span class="badge"><?php echo $val($student_data['Level']); ?></span> 
                    <i class="fa fa-chevron-right" style="font-size: 0.8em; opacity: 0.5; margin: 0 5px;"></i>
                    <span class="badge"><?php echo $val($student_data['Class']); ?> <?php echo $val($student_data['Stream']); ?></span>
                </p>
            </div>
            <div class="header-photo">
                <div class="profile-image-container small-inline">
                    <img id="display-photo" src="<?php echo $photo_src; ?>" alt="Student Photo">
                    <div class="photo-edit-overlay" onclick="triggerPhotoUpload()"><i class="fa fa-camera"></i> Change</div>
                </div>
            </div>
        </div>

        <div class="profile-content-area">
            <div id="view-mode-content">
                <?php include __DIR__ . '/../../modules/students/partial/profile_data_view.php'; ?>
            </div>
            <?php include __DIR__ . '/../../modules/students/partial/profile_edit_form.php'; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
