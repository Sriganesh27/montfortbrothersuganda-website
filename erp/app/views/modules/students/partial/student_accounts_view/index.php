<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="viewer-container">
    <h2>Student Accounts</h2>
    
    <form id="accounts-filter-form" class="filter-form">
        <div class="field-group">
            <label>Adm No</label>
            <input type="text" name="search_id" placeholder="1001...">
        </div>
        <div class="field-group">
            <label>Name</label>
            <input type="text" name="search_name" placeholder="Search name...">
        </div>
        <div class="field-group">
            <label>Level</label>
            <select id="acc-level" name="level">
                <option value="">All</option>
                <option value="pre-primary">Pre-Primary</option>
                <option value="primary">Primary</option>
                <option value="secondary">Secondary</option>
            </select>
        </div>
        <div class="field-group">
            <label>Class</label>
            <select id="acc-class" name="class"><option value="">All</option></select>
        </div>
        <div class="field-group">
            <label>Stream</label>
            <select name="stream" id="acc-stream">
                <option value="">All Streams</option>
                <?php foreach(['A','B','C','D','E'] as $s) echo "<option value='$s'>$s</option>"; ?>
            </select>
        </div>
        <div class="button-wrapper align-self-end">
            <button type="submit" class="btn-primary">Search</button>
        </div>
    
    </form>
    
    <div class="table-controls">
        <div class="rows-per-page">
            <label>Rows per page:</label>
            <select id="acc-limit" onchange="loadAccountsData(1)">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="pagination-info" id="acc-pagination-info">Showing 0-0 of 0</div>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Adm No</th>
                    <th>Student Name</th>
                    <th>Class/Stream</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Login Status</th>
                    <th>Action</th>
                    <th>Activate</th>
                </tr>
            </thead>
            <tbody id="accounts-table-body">
                </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <button id="acc-prev-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-left"></i></button>
        <div id="acc-page-numbers"></div>
        <button id="acc-next-btn" class="btn-primary btn-gray" disabled><i class="fa fa-chevron-right"></i></button>
    </div>
</div>




<?php include __DIR__ . '/../../layouts/footer.php'; ?>
