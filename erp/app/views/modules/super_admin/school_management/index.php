<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div id="schools-module" class="module" style="display: none;">
    <div class="super-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Institutional Management</h1>
                <p class="subtitle">Add, update, or remove school branches from the ERP system.</p>
            </div>
            <button class="branch-btn active" onclick="openBranchModal()">
                <i class="fa fa-plus-circle"></i> Add New School
            </button>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; border-top: 4px solid var(--primary-color);">
        <table class="branch-table" style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--primary-color-dark); color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Branch Name</th>
                    <th style="padding: 15px; text-align: left;">Location</th>
                    <th style="padding: 15px; text-align: left;">Type</th>
                    <th style="padding: 15px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="branch-list-results">
                </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
