<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php 
// modules/students/partial/academic_edit_view.php
$current_year = date('Y');
?>
<div class="viewer-container">
    <h2>Academic Records Edit</h2>
    
    <form id="academic-edit-filter-form" class="filter-form" onsubmit="event.preventDefault(); loadAcademicEditData(1);">
        <div class="field-group">
            <label>Academic Year</label>
            <select name="year" id="ae-year">
                <option value="">All</option>
                <?php 
                $cy = date('Y');
                for ($y = $cy; $y >= $cy - 2; $y--) {
                    $ay = format_academic_year($y);
                    echo "<option value='$ay'>$ay</option>";
                }
                ?>
            </select>
        </div>
        <div class="field-group">
            <label>Level</label>
            <select name="level" id="ae-level">
                <option value="">All Levels</option>
                <option value="pre-primary">Pre-Primary</option>
                <option value="primary">Primary</option>
                <option value="secondary">Secondary</option> 
            </select>
        </div>
        <div class="field-group">
            <label>Class</label>
            <select name="class" id="ae-class"><option value="">All</option></select>
        </div>
        <div class="field-group">
            <label>Stream</label>
            <select name="stream" id="ae-stream">
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
            <select id="ae-limit" onchange="loadAcademicEditData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="ae-pagination-info">Showing 0-0 of 0</div>
    </div>
    <div class="table-container">
        <table class="data-table" id="academic-edit-table">
            <thead>
                <tr>
                    <th>Adm-no</th>
                    <th>Name</th>
                    <th>LIN Number</th>
                    <th>Stream</th>
                    <th>Combination</th>
                    <th>PLE Index No</th>
                    <th>PLE Agg</th>
                    <th>UCE Index No</th>
                    <th>UCE Result</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="ae-table-body">
                <tr><td colspan="10" class="text-center">Select filters and search...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="ae-prev-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-left"></i></button>
        <div id="ae-page-numbers"></div>
        <button id="ae-next-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-right"></i></button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
