<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// webpages/migrate_view.php
// Advanced Migration Module UI
$current_year = date('Y');
?>
<div class="viewer-container">
    <h2>Student Migration</h2>
    <p class="subtitle">Promote students to the next class or manage alumni transitions.</p>

    <div class="form-container migration-panel">
        <form id="migration-controls-form" onsubmit="return false;">
            
            <fieldset>
                <legend>1. Source Class</legend>
                <div class="input-group">
                    <div class="field">
                        <label for="mig-source-year">Academic Year</label>
                        <select id="mig-source-year" name="source_year">
                            <?php 
                            // Display formatted academic years for source
                            for($y=$current_year; $y>=$current_year-2; $y--) {
                                $ay = format_academic_year($y);
                                echo "<option value='$ay'>$ay</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-source-level">Level</label>
                        <select id="mig-source-level" name="source_level">
                            <option value="">Select Level</option>
                            <option value="pre-primary">Pre-Primary</option>
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-source-class">Class</label>
                        <select id="mig-source-class" name="source_class">
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-source-stream">Stream</label>
                        <select id="mig-source-stream" name="source_stream">
                            <option value="">All Streams</option>
                            <?php 
                            $streams = getStreamOptions();
                            foreach($streams as $st) echo "<option value='$st'>$st</option>";
                            ?>
                        </select>
                    </div>
                    
                </div>
                <div class="field fetch-action">
                        <button type="button" id="btn-fetch-students" class="btn-primary migrate-fetch-btn">
                            Search
                        </button>
                    </div>
            </fieldset>

            <fieldset class="destination-set">
                <legend>2. Destination</legend>
                <div class="input-group">
                    <div class="field">
                        <label for="mig-target-year" class="required">New Academic Year</label>
                        <select id="mig-target-year" name="target_year" required>
                            <?php 
                            // Display formatted academic years for source
                            for($y=$current_year + 1; $y>=$current_year-2; $y--) {
                                $ay = format_academic_year($y);
                                echo "<option value='$ay'>$ay</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-target-term" class="required">New Term</label>
                        <select id="mig-target-term" name="target_term" required>
                            <option value="Term 1" selected>Term 1</option>
                            <option value="Term 2">Term 2</option>
                            <option value="Term 3">Term 3</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-target-level" class="required">New Level</label>
                        <select id="mig-target-level" name="target_level" required>
                            <option value="">Select Level</option>
                            <option value="pre-primary">Pre-Primary</option>
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="alumni">Alumni / Completed</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-target-class" class="required">New Class</label>
                        <select id="mig-target-class" name="target_class" required>
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="mig-target-stream">New Stream</label>
                        <select id="mig-target-stream" name="target_stream">
                            <option value="">None/Same</option>
                            <?php 
                            foreach($streams as $st) echo "<option value='$st'>$st</option>";
                            ?>
                        </select>
                    </div>
                </div>
            </fieldset>
            
            <div class="migration-actions">
                <div class="selection-info">
                <span id="selection-count">0</span> students selected
                </div>
                <div class="migration-btn-group">
                    <button type="button" id="btn-migrate-selected" class="btn-primary btn-migrate-selected" style="background-color: #17a2b8;" >
                        Migrate Selected
                    </button>
                    <button type="button" id="btn-migrate-class" class="btn-primary btn-migrate-all" style="background-color: var(--success-color); ">
                        Entire Class
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="migration-results-area">
        <div class="table-container">
            <table class="data-table" id="migration-table">
                <thead>
                    <tr>
                        <th class="checkbox-column">
                            <input type="checkbox" id="mig-select-all">
                        </th>
                        <th>Photo</th>
                        <th>Student Details</th>
                        <th>Current Class/Stream</th>
                        <th>Academic Year</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    
    <div class="modal-backdrop" id="migration-confirm-modal">
        <div class="modal-content confirm-modal">
            <div class="success-header">
                <div class="confirm-icon-circle">
                    <i class="fa fa-question"></i>
                </div>
            </div>
            <h4>Confirm Migration</h4>
            <p id="mig-confirm-message"></p>
            <div class="migration-summary modal-details">
                 <div class="summary-item">
                    <span class="label">Destination:</span>
                    <span class="value highlight" id="mig-confirm-dest"></span>
                </div>
            </div>
            <div class="modal-actions center-actions">
                <button id="mig-confirm-cancel-btn" class="btn-primary btn-cancel">Cancel</button>
                <button id="mig-confirm-proceed-btn" class="btn-primary btn-confirm">Confirm</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="migration-success-modal">
        <div class="modal-content success-modal">
            <div class="success-header">
                <div class="success-icon-circle">
                    <i class="fa fa-check"></i>
                </div>
            </div>
            <h4>Migration Successful!</h4>
            <div class="migration-summary">
                <div class="summary-item"><span class="label">Total Students</span><span class="value" id="mig-success-count">0</span></div>
                <div class="arrow-divider"><i class="fa fa-arrow-down"></i></div>
                <div class="summary-item"><span class="label">Destination Class</span><span class="value" id="mig-success-target">Class Name</span></div>
                <div class="summary-item"><span class="label">Academic Period</span><span class="value" id="mig-success-period">Term X, 20XX-YY</span></div>
            </div>
            <button id="mig-success-close-btn" class="btn-primary btn-success-done">Done & Refresh</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
