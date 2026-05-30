<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php 
// modules/students/partial/quick_edit_view.php
$current_year = date('Y');
?>
<div class="viewer-container">
    <h2>Quick Edit</h2>
    
    <form id="quick-edit-filter-form" class="filter-form">
        <div class="field-group">
            <label>Academic Year</label>
            <select id="qe-year" name="year">
                <option value="">All</option>
                <?php 
                for ($y = $current_year; $y >= $current_year - 2; $y--) {
                    $ay = format_academic_year($y);
                    echo "<option value='$ay'>$ay</option>";
                }
                ?>
            </select>
        </div>
        <div class="field-group">
            <label>Level</label>
            <select id="qe-level" name="level">
                <option value="">All Levels</option>
                <option value="pre-primary">Pre-Primary</option>
                <option value="primary">Primary</option>
                <option value="secondary">Secondary</option>
            </select>
        </div>
        <div class="field-group">
            <label>Class</label>
            <select id="qe-class" name="class">
                <option value="">All Classes</option>
            </select>
        </div>
        <div class="field-group">
            <label>Stream</label>
            <select id="qe-stream" name="stream">
                <option value="">All Streams</option>
                <?php foreach(['A','B','C','D','E'] as $s) echo "<option value='$s'>$s</option>"; ?>
            </select>
        </div>
        <div class="field-group align-bottom">
            <button type="submit" class="btn-primary">Search</button>
        </div>
    </form>

    <div class="table-controls">
        <div class="rows-per-page">
            <label>Rows per page:</label>
            <select id="qe-limit" onchange="loadQuickEditData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="qe-pagination-info">Showing 0-0 of 0</div>
    </div>

    <div class="table-container">
        <table class="data-table" id="quick-edit-table">
            <thead>
                <tr>
                    <th>Adm-no</th>
                    <th>Name</th>
                    <th>LIN Number</th>
                    <th>Gender</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Residence</th>
                    <th>Entry</th>
                    <th>Parent Contact</th>
                    <th>Pay Code</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="qe-table-body">
                <tr><td colspan="10" class="text-center">Loading data...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="qe-prev-btn" class="btn-primary btn-gray" disabled title="Previous">
            <i class="fa fa-chevron-left"></i>
        </button>
        
        <div id="qe-page-numbers"></div>
        
        <button id="qe-next-btn" class="btn-primary btn-gray" disabled title="Next">
            <i class="fa fa-chevron-right"></i>
        </button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
