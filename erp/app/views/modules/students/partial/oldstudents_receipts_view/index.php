<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="viewer-container">
    <h2>Old Student Receipts</h2>
    <form id="receipts-filter-form" class="filter-form">
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
        <div class="field-group">
            <label>Date from</label>
            <input type="date" name="date_from" id="dob">
        </div>
        <div class="field-group">
            <label>Date To</label>
            <input type="date" name="date_to" id="dob">
        </div>

        <div class="button-wrapper align-self-end">
            <button type="submit" class="btn-primary">Search</button>
        </div>
    </form>
    
    <div class="table-controls">
        <div class="rows-per-page">
            <label>Rows per page:</label>
            <select id="receipts-limit" onchange="loadAlumniReceiptsData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="receipts-pagination-info">Showing 0-0 of 0</div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Adm No</th>
                    <th>Student Name</th>
                    <th>Year of Completion</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Receipt No</th>
                    <th>Paid by</th>
                    <th>Payment Mode</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody id="receipts-table-body">
                </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="receipts-prev-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-left"></i></button>
        <div id="receipts-page-numbers"></div>
        <button id="receipts-next-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-right"></i></button>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
