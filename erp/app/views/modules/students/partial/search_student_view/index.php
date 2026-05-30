<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="viewer-container search-module-container">
    <div class="search-header-section">
        <div class="form-container search-box-wrapper">
            <h2 class="search-title">Quick Student Search</h2>
            
            <label for="global-student-search" class="search-label">Search Student</label>
            <input type="text" id="global-student-search" 
                   class="global-search-input"
                   placeholder="Enter Name, Surname or Parent Name..." 
                   autocomplete="off">
            <p class="search-hint">Results will appear automatically below.</p>
        </div>
    </div>

    <div id="global-search-results" class="search-results-grid">
        </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
