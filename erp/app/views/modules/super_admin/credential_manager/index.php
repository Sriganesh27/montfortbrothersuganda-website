<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div id="accounts-module" class="module" style="display: none;">
    <div class="super-header">
        <h1>Global User Oversight</h1>
        <p class="subtitle">Monitor and manage administrative credentials for all campus staff.</p>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; border-top: 4px solid var(--warning-color);">
        <table class="branch-table" style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--primary-color-dark); color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Full Name</th>
                    <th style="padding: 15px; text-align: left;">Username</th>
                    <th style="padding: 15px; text-align: left;">Role</th>
                    <th style="padding: 15px; text-align: left;">Assigned Branch</th>
                    <th style="padding: 15px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="credential-list-results">
                </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
