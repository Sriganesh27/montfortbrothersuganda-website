<?php
// erp/app/views/modules/students/studentmanagement/index.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTE: config.php, header.php, and footer.php are deliberately NOT included here 
// because this module is loaded *inside* the admin dashboard, which already has them.

function is_filter_or_search_active() {
    return isset($_GET['search_id']) && !empty($_GET['search_id']) ||
           isset($_GET['search_name']) && !empty($_GET['search_name']) ||
           isset($_GET['level']) && !empty($_GET['level']) ||
           isset($_GET['class']) && !empty($_GET['class']) ||
           isset($_GET['term']) && !empty($_GET['term']) ||
           isset($_GET['admission_year']) && !empty($_GET['admission_year']) ||
           isset($_GET['residence']) && !empty($_GET['residence']);
}

// FIXED: Replaced the deprecated FILTER_SANITIZE_STRING with modern PHP 8 standard
function get_summary_filter_value($key, $default) {
    $value = $_GET[$key] ?? $default;
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$current_year = date('Y');
$selected_summary_year = get_summary_filter_value('summary_year', $current_year);
$selected_summary_term = get_summary_filter_value('summary_term', 'Term 1'); 
?>
<div id="admission-module" class="module" style="display: none;">
    <div class="viewer-container" style="max-width: 1200px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        
        <div style="border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);"><i class="fa fa-user-plus"></i> Official Student Admission</h2>
        </div>
        
        <form id="admission-form"> 
            <input type="hidden" id="link_app_id" name="link_app_id" value="">
            
            <fieldset class="erp-fieldset">
                <legend><i class="fa fa-id-card"></i> 1. Personal & Contact Details</legend>
                <div class="erp-grid-row">
                    <div class="erp-field"><label>Registration / App Ref No.</label><input type="text" id="registration_number" name="registration_number" placeholder="e.g. U011-26-001"></div>
                    <div class="erp-field"><label class="required">First Name</label><input type="text" id="name" name="name" required></div>
                    <div class="erp-field"><label>Middle Name</label><input type="text" id="middle_name" name="middle_name"></div>
                    <div class="erp-field"><label class="required">Surname</label><input type="text" id="surname" name="surname" required></div>
                    <div class="erp-field"><label class="required">Gender</label>
                        <select id="gender" name="gender" required><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select>
                    </div>
                    <div class="erp-field"><label>Date Of Birth</label><input type="date" id="dob" name="dob"></div>
                    <div class="erp-field"><label>Nationality</label><input type="text" id="nationality" name="nationality" value="Ugandan"></div>
                </div>
                
                <h4 style="margin: 15px 0 10px; color: #555; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">Residential Address</h4>
                <div class="erp-grid-row">
                    <div class="erp-field"><label>PO BOX</label><input type="text" id="postal_code" name="postal_code"></div>
                    <div class="erp-field"><label>House/Block</label><input type="text" id="house_no" name="house_no"></div>
                    <div class="erp-field"><label>Street</label><input type="text" id="street" name="street"></div>
                    <div class="erp-field"><label>Village</label><input type="text" id="village" name="village"></div>
                    <div class="erp-field"><label>District</label><input type="text" id="district" name="district"></div>
                    <div class="erp-field"><label>State</label><input type="text" id="state" name="state"></div>
                </div>
            </fieldset>

            <fieldset class="erp-fieldset">
                <legend><i class="fa fa-school"></i> 2. Enrollment Target</legend>
                <div class="erp-grid-row">
                    <div class="erp-field"><label class="required">Admission Year</label><input type="text" id="admission_year" name="admission_year" value="<?php echo date('Y'); ?>" required></div>
                    <div class="erp-field"><label class="required">Term</label>
                        <select id="term" name="term" required><option value="Term 1">Term 1</option><option value="Term 2">Term 2</option><option value="Term 3">Term 3</option></select>
                    </div>
                    <div class="erp-field"><label class="required">Level</label>
                        <select id="level" name="level" required><option value="">Select</option><option value="pre-primary">Pre-Primary</option><option value="primary">Primary</option><option value="secondary">Secondary</option></select>
                    </div>
                    <div class="erp-field"><label class="required">Class</label><select id="class" name="class" required><option value="">Select</option></select></div>
                    <div class="erp-field"><label>Stream</label><select id="stream" name="stream"><option value="">Select</option><?php if (function_exists('getStreamOptions')) { foreach(getStreamOptions() as $st) echo "<option value='$st'>$st</option>"; } ?></select></div>
                    <div class="erp-field"><label class="required">Residence</label><select id="residence" name="residence" required><option value="Day">Day</option><option value="Boarding">Boarding</option></select></div>
                    <div class="erp-field"><label class="required">Entry Status</label><select id="entry_status" name="entry_status" required><option value="New">New</option><option value="Continuing">Continuing</option></select></div>
                </div>
            </fieldset>

            <fieldset class="erp-fieldset">
                <legend><i class="fa fa-users"></i> 3. Family Background</legend>
                <div class="erp-grid-row">
                    <div class="erp-field"><label class="required">Father's Name</label><input type="text" id="father_name" name="father_name" required></div>
                    <div class="erp-field"><label>Father's Contact</label><input type="text" id="father_contact" name="father_contact"></div>
                    <div class="erp-field"><label style="color:#d35400;">Father's NIN</label><input type="text" id="father_nin" name="father_nin" placeholder="National ID"></div>
                    <div class="erp-field"><label>Father's Email</label><input type="email" id="father_email" name="father_email"></div>
                </div>
                <div class="erp-grid-row" style="margin-top: 15px;">
                    <div class="erp-field"><label class="required">Mother's Name</label><input type="text" id="mother_name" name="mother_name" required></div>
                    <div class="erp-field"><label>Mother's Contact</label><input type="text" id="mother_contact" name="mother_contact"></div>
                    <div class="erp-field"><label style="color:#d35400;">Mother's NIN</label><input type="text" id="mother_nin" name="mother_nin" placeholder="National ID"></div>
                    <div class="erp-field"><label>Mother's Email</label><input type="email" id="mother_email" name="mother_email"></div>
                </div>
                <div class="erp-grid-row" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc;">
                    <div class="erp-field"><label>Guardian Name</label><input type="text" id="guardian_name" name="guardian_name"></div>
                    <div class="erp-field"><label>Guardian Contact</label><input type="text" id="guardian_contact" name="guardian_contact"></div>
                    <div class="erp-field"><label style="color:#d35400;">Guardian's NIN</label><input type="text" id="guardian_nin" name="guardian_nin" placeholder="National ID"></div>
                    <div class="erp-field"><label>Relation</label>
                        <select id="guardian_relation" name="guardian_relation">
                            <option value="">Select...</option><option value="Brother">Brother</option><option value="Sister">Sister</option><option value="Uncle">Uncle</option><option value="Aunt">Aunt</option><option value="Stepfather">Stepfather</option><option value="Stepmother">Stepmother</option><option value="Grandparent">Grandparent</option><option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="erp-fieldset">
                <legend><i class="fa fa-history"></i> 4. Academic History</legend>
                <div class="erp-grid-row">
                    <div class="erp-field" style="grid-column: span 2;"><label>Former School</label><input type="text" id="former_school" name="former_school"></div>
                    <div class="erp-field"><label>School Code</label><input type="text" id="former_school_code" name="former_school_code"></div>
                    <div class="erp-field"><label>Learner's LIN</label><input type="text" id="former_school_lin" name="former_school_lin"></div>
                </div>
                <div class="erp-grid-row" style="margin-top: 15px;">
                    <div class="erp-field"><label>PLE Index</label><input type="text" id="ple_index" name="ple_index"></div>
                    <div class="erp-field"><label>PLE Score</label><input type="number" id="ple_agg" name="ple_agg"></div>
                    <div class="erp-field"><label>UCE Index</label><input type="text" id="uce_index" name="uce_index"></div>
                    <div class="erp-field"><label>UCE Result</label><input type="text" id="uce_result" name="uce_result"></div>
                </div>
            </fieldset>

            <fieldset class="erp-fieldset">
                <legend><i class="fa fa-notes-medical"></i> 5. Medical, Documents & Photo</legend>
                <div class="erp-grid-row" style="grid-template-columns: 1.5fr 1fr 1fr;">
                    
                    <div class="erp-field">
                        <label>Medical Conditions / Extra Information</label>
                        <textarea id="more_info" name="more_info" rows="5" style="width:100%; border:1px solid #ccc; border-radius:4px; padding:10px;"></textarea>
                    </div>
                    
                    <div class="erp-field" style="border: 1px dashed #aaa; padding: 15px; border-radius: 6px; background: #fafafa; display: flex; flex-direction: column; justify-content: center;">
                        <label style="margin-bottom:10px; text-align:center;">Submitted Academic Doc</label>
                        
                        <a id="admission-old-doc-link" href="#" target="_blank" style="display:none; background:#17a2b8; color:white; padding:8px 15px; border-radius:4px; text-decoration:none; text-align:center; margin-bottom:15px;"><i class="fa fa-file-pdf"></i> View Old Document</a>
                        <span id="no-doc-text" style="color:#888; font-size:12px; text-align:center; display:block; margin-bottom:10px;">No document attached.</span>
                        
                        <label style="font-size:12px; color:#555; text-align:center; margin-bottom:5px;">Upload new to replace:</label>
                        <input type="file" id="prev_marks_doc" name="prev_marks_doc" accept=".pdf,image/*" style="font-size:11px;">
                        
                        <input type="hidden" id="existing_doc_path" name="existing_doc_path" value="">
                    </div>

                    <div class="erp-field" style="text-align: center; border: 1px dashed #aaa; padding: 15px; border-radius: 6px; background: #fff;">
                        <label>Student Photo</label>
                        <link rel="icon" type="image/webp" href="<?php echo getBaseUrl(); ?>/assets/Images/logo_MBSG_UG_8.webp?v=<?php echo time(); ?>">
                        <br>
                        <button type="button" class="btn-primary btn-gray" onclick="document.getElementById('photo').click()" style="padding: 5px 15px; font-size: 12px;"><i class="fa fa-camera"></i> Change Photo</button>
                        <input type="file" id="photo" name="photo" accept="image/*" style="display:none;">
                        
                        <input type="hidden" id="existing_photo_path" name="existing_photo_path" value="">
                    </div>

                </div>
            </fieldset>

            <div class="erp-action-bar">
                <button type="button" id="admission-reset-btn" class="btn-primary btn-gray" style="width: auto;">Clear Form</button>
                <button type="submit" class="btn-primary" style="width: auto; background: #27ae60;"><i class="fa fa-check-circle"></i> Save Student Admission</button>
            </div>
        </form>
    </div>
</div>

<div id="list-module" class="module" style="display: none;">
    <div class="viewer-container">
        <h2>View students</h2>
        <form id="student-filter-form" class="filter-form">
        <input type="hidden" name="module" value="list"> 
        
        <div class="field-group"><label for="search-id">Adm-no</label><input type="text" id="search-id" name="search_id" placeholder="Ad No..."></div>
        <div class="field-group"><label for="search-name">Name</label><input type="text" id="search-name" name="search_name" placeholder="Name..."></div>
        
        <div class="field-group"><label for="filter-level">Level</label><select id="filter-level" name="level"><option value="">All</option><option value="pre-primary">Pre-Primary</option><option value="primary">Primary</option><option value="secondary">Secondary</option></select></div>
        <div class="field-group">
            <label for="filter-class">Class</label>
            <select id="filter-class" name="class">
                <option value="">All</option>
            </select></div>
        
        <div class="field-group">
            <label for="filter-stream">Stream</label>
            <select id="filter-stream" name="stream">
                <option value="">All</option>
                <?php 
                    if (function_exists('getStreamOptions')) {
                        $streams = getStreamOptions();
                        foreach($streams as $st) echo "<option value='$st'>$st</option>"; 
                    }
                ?>
            </select>
        </div>
        <div class="field-group">
            <label for="filter-gender">Gender</label>
            <select id="filter-gender" name="gender">
                <option value="">All</option>
                <option value="Male">Male</option><option value="Female">Female</option>
            </select>
        </div>
        <div class="field-group">
            <label for="filter-residence">Residence</label>
            <select id="filter-residence" name="residence">
                <option value="">All</option>
                <option value="Day">Day</option><option value="Boarding">Boarding</option>
            </select>
        </div>

        <div class="field-group"><label for="filter-term">Term</label><select id="filter-term" name="term"><option value="">All</option><option value="Term 1">Term 1</option><option value="Term 2">Term 2</option><option value="Term 3">Term 3</option></select></div>
        <div class="field-group"><label for="filter-year">Year</label><select id="filter-year" name="admission_year"><option value="">All</option><option value="2025">2025</option><option value="2024">2024</option></select></div>
        
        <div class="button-wrapper align-self-end">
            <button type="submit" class="btn-primary">Search</button>
            <button type="button" id="reset-filter-btn" class="btn-primary btn-gray">Reset</button>
        </div>
    </form>

        <div id="student-list-results">
            <?php 
                if (isset($_GET['module']) && $_GET['module'] === 'list' && is_filter_or_search_active()) {
                    $view_path = __DIR__ . '/partial/viewstudents.php';
                    if (file_exists($view_path)) { include $view_path; }
                } else {
                    echo "<p style='text-align:center; padding: 40px; font-size: 1.1em; color: #555;'>Use the search options above to find students.</p>";
                }
            ?>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="details-modal">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h4 id="details-modal-title">Edit Student Details: <span id="modal-student-name"></span></h4>
            <button id="details-close-btn" class="close-btn" aria-label="close details">&times;</button>
        </div>
        <form id="editStudentForm" action="edit_student.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="AdmissionNo" id="modal-AdmissionNo">
                <div class="detail-section">
                    <div class="detail-photo">
                        <img id="detail-photo-img" src="" alt="Student Photo">
                        <p style="font-size: 0.8em; color: var(--text-color-light);">Photo (Read Only)</p>
                    </div>
                    <fieldset>
                        <legend>Personal & Enrollment</legend>
                        <div class="field half-width"><label for="modal-Name">Name</label><input type="text" id="modal-Name" name="Name" required></div>
                        <div class="field half-width"><label for="modal-Surname">Surname</label><input type="text" id="modal-Surname" name="Surname" required></div>
                        <div class="field half-width"><label>DOB (R/O)</label><input type="date" id="modal-DOB" name="DateOfBirth" readonly></div>
                        <div class="field half-width"><label>Gender (R/O)</label><input type="text" id="modal-Gender" name="Gender" readonly></div>
                        <div class="field full-width"><label for="modal-Address">Address</label><textarea id="modal-Address" name="Address" rows="2"></textarea></div>
                        <div class="field full-width"><p>Enrollment: <span id="detail-level"></span> / <span id="detail-class"></span></p></div>
                    </fieldset>
                    <fieldset>
                        <legend>Guardian Info</legend>
                        <div class="field full-width"><label for="modal-GuardianName">Guardian Name</label><input type="text" id="modal-GuardianName" name="guardian_name"></div>
                        <div class="field half-width"><label for="modal-ContactPrimary">Contact</label><input type="text" id="modal-ContactPrimary" name="guardian_contact"></div>
                    </fieldset>
                </div>
                <div class="button-wrapper" style="margin-top: 20px;">
                    <button type="submit" class="btn-primary" style="width: auto;">Save Changes</button>
                </div>
                <p id="editMessage" style="text-align: center; margin-top: 10px;"></p>
            </div>
        </form>
    </div>
</div>

<div id="summary-module" class="module" style="display: none;">
    <div class="viewer-container">
    <h2>Student Enrollment Summary</h2>
        <form id="summary-filter-form" class="filter-form">
            <input type="hidden" name="module" value="summary"> <div class="field-group">
                <label for="summary_term">Term</label>
                <select id="summary_term" name="summary_term" required>
                    <option value="Term 1" <?php if ($selected_summary_term == 'Term 1') echo 'selected'; ?>>Term 1</option>
                    <option value="Term 2" <?php if ($selected_summary_term == 'Term 2') echo 'selected'; ?>>Term 2</option>
                    <option value="Term 3" <?php if ($selected_summary_term == 'Term 3') echo 'selected'; ?>>Term 3</option>
                </select>
            </div>
            <div class="field-group">
                <label for="summary_year">Academic Year</label>
                <select id="summary_year" name="summary_year" required>
                    <?php 
                    for ($y = $current_year; $y >= $current_year - 5; $y--) {
                        if (function_exists('format_academic_year')) {
                            $ay = format_academic_year($y);
                            echo "<option value='$ay'>$ay</option>";
                        } else {
                            echo "<option value='$y'>$y</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="button-wrapper">
                <button type="submit" class="btn-primary">Generate Summary</button>
            </div>
        </form>
        
        <div id="summary-results">
            <?php
            if (isset($_GET['module']) && $_GET['module'] === 'summary') {
                $summary_path = __DIR__ . '/partial/studentsummary.php';
                if (file_exists($summary_path)) { include $summary_path; }
            } else {
                    echo "<p style='text-align:center; padding: 40px; color: #555;'>Select filters to generate report.</p>";
            }
            ?>
        </div>
    </div>
</div>
<div id="profile-module" class="module" style="display: none;">
</div>
<div id="migrate-module" class="module" style="display: none;">
    <?php $migrate_path = __DIR__ . '/partial/migrate_view.php'; if(file_exists($migrate_path)) include $migrate_path; ?>
</div>

<div id="quickedit-module" class="module" style="display: none;">
<?php $qe_path = __DIR__ . '/partial/quick_edit_view.php'; if(file_exists($qe_path)) include $qe_path; ?>
</div>
<div id="academicedit-module" class="module" style="display: none;">
<?php $ae_path = __DIR__ . '/partial/academic_edit_view.php'; if(file_exists($ae_path)) include $ae_path; ?>
</div>
<div id="searchstudent-module" class="module" style="display: none;">
    <?php $ss_path = __DIR__ . '/partial/search_student_view.php'; if(file_exists($ss_path)) include $ss_path; ?>
</div>
<div id="studentphotos-module" class="module" style="display: none;">
<?php $sp_path = __DIR__ . '/partial/student_photos_view.php'; if(file_exists($sp_path)) include $sp_path; ?>
</div>
<div id="studentaccounts-module" class="module" style="display: none;">
<?php $sa_path = __DIR__ . '/partial/student_accounts_view.php'; if(file_exists($sa_path)) include $sa_path; ?>
</div>      
<div id="oldstudentdebt-module" class="module" style="display: none;">
<?php $od_path = __DIR__ . '/partial/oldstudents_debts_view.php'; if(file_exists($od_path)) include $od_path; ?>
</div>
<div id="oldstudentrec-module" class="module" style="display: none;">
<?php $or_path = __DIR__ . '/partial/oldstudents_receipts_view.php'; if(file_exists($or_path)) include $or_path; ?>
</div>
<div id="studentcomments-module" class="module" style="display: none;"></div>
<div id="marks-module" class="module" style="display:none;">
<div class="card">
<div class="card-header">
    <h3><i class="fa fa-graduation-cap"></i> External Exam Results</h3>
    <div class="header-filters">
        <label>Select Level:</label>
        <select id="examLevelSelect" onchange="switchMarksView(this.value)">
            <option value="PLE">PLE (Primary Leaving Exam)</option>
            <option value="UCE">UCE (O-Level Results)</option>
            <option value="UACE">UACE (A-Level Results)</option>
        </select>
    </div>
    <div class="header-actions">
        <button class="btn btn-success" onclick="exportLevelMarks()"><i class="fa fa-file-export"></i> Export</button>
        <button class="btn btn-primary" onclick="document.getElementById('marksImport').click()"><i class="fa fa-file-import"></i> Import</button>
    </div>
</div>
<div class="card-body">
    <table class="table" id="dynamicMarksTable">
        <thead id="marksTableHeader"></thead>
        <tbody id="marksDataBody"></tbody>
    </table>
</div>
</div>
</div>