<?php 
// erp/app/views/modules/manage_applications/manage_applications/index.php
// Note: header.php and footer.php are deliberately omitted here because 
// this module is loaded dynamically inside the main admin dashboard.
?>

<link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/styles/admin_manage_apps.css">

<div class="manage-apps-container">
    <h2>Manage Applications</h2>
    
    <label><strong>Filter by Status: </strong></label>
    <select id="statusFilter" class="filter-select" onchange="loadApplications()">
        <option value="Pending">Pending</option>
        <option value="Shortlisted">Shortlisted</option>
        <option value="Selected">Selected</option>
        <option value="All">All Applications</option>
    </select>
    
    <table class="manage-apps-table">
        <thead>
            <tr>
                <th>Ref Number</th>
                <th>Applicant Name</th>
                <th>Class</th>
                <th>Status</th>
                <th>Scholarship</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="appBody">
            </tbody>
    </table>
</div>

<div id="appReviewModal" class="app-modal" style="display:none;">
    <div class="app-modal-content">
        <span class="app-close" onclick="document.getElementById('appReviewModal').style.display='none'">&times;</span>
        <h2>Application Details</h2>
        <div id="appModalBody">
            </div>
    </div>
</div>

<script src="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/scripts/admin_manage_apps.js?v=<?php echo time(); ?>"></script>