<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php 
// erp/modules/students/partial/student_marks_view.php
?>
<div id="marks-module" class="module" style="display:none;">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><i class="fa fa-graduation-cap"></i> External Exam Marks</h3>
            <div class="header-filters">
                <select id="examLevelSelect" class="form-control" onchange="switchMarksView(this.value)">
                    <option value="PLE">PLE (Primary)</option>
                    <option value="UCE">UCE (O-Level)</option>
                    <option value="UACE">UACE (A-Level)</option>
                </select>
            </div>
            <div class="header-actions">
                <button class="btn-export" onclick="exportMarks()"><i class="fa fa-file-excel"></i> Export</button>
                <button class="btn-import" onclick="document.getElementById('marksImport').click()"><i class="fa fa-upload"></i> Import</button>
                <input type="file" id="marksImport" style="display:none" onchange="handleMarksImport(this)">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead id="marksTableHeader"></thead>
                    <tbody id="marksDataBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
