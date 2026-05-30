<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="viewer-container">
    <h2>Old Student Debts</h2>
    <form id="debts-filter-form" class="filter-form">
        <div class="field-group">
            <label>Adm No</label>
            <input type="text" name="search_id" placeholder="1001...">
        </div>
        <div class="field-group">
            <label>Name</label>
            <input type="text" name="search_name" placeholder="Search name...">
        </div>
        
        <div class="field-group">
            <label>Completed Year</label>
            <select name="completed_year" id="completed_year_select" style="padding: 8px; width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">All Years</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
            </select>
        </div>

        <div class="button-wrapper align-self-end">
            <button type="submit" class="btn-primary">Search</button>
        </div>
    </form>
    
    <div class="table-controls">
        <div class="rows-per-page">
            <label>Rows per page:</label>
            <select id="debts-limit" onchange="loadAlumniDebtsData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="debts-pagination-info">Showing 0-0 of 0</div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Adm No</th>
                    <th>Student Name</th>
                    <th>Year of Completion</th>
                    <th>Parent Name</th>
                    <th>Contact</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody id="debts-table-body">
                </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="debts-prev-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-left"></i></button>
        <div id="debts-page-numbers"></div>
        <button id="debts-next-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-right"></i></button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
