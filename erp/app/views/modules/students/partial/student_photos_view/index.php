<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php 
$current_year = date('Y');
?>
<div class="viewer-container">
    <h2>Student Photos</h2>
    
    <form id="student-photo-edit" class="filter-form" onsubmit="event.preventDefault(); loadPhotoEditData(1);">
        <div class="field-group">
            <label>Academic Year</label>
            <select name="year" id="pe-year">
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
            <select name="level" id="pe-level">
                <option value="">All Levels</option>
                <option value="pre-primary">Pre-Primary</option>
                <option value="primary">Primary</option>
                <option value="secondary">Secondary</option> 
            </select>
        </div>
        <div class="field-group">
            <label>Class</label>
            <select name="class" id="pe-class"><option value="">All</option></select>
        </div>
        <div class="field-group">
            <label>Stream</label>
            <select name="stream" id="pe-stream">
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
            <select id="pe-limit" onchange="loadPhotoEditData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="pe-pagination-info">Showing 0-0 of 0</div>
    </div>
    <div class="table-container">
        <table class="data-table" id="academic-edit-table">
            <thead>
                <tr>
                    <th>Adm-no</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="pe-table-body">
                <tr><td colspan="10" class="text-center">Select filters and search...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="pe-prev-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-left"></i></button>
        <div id="pe-page-numbers"></div>
        <button id="pe-next-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-right"></i></button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
