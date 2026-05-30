/**
 * Global Toast Notification Generator
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'info') icon = 'fa-info-circle';

    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);

    // Animate In
    setTimeout(() => toast.classList.add('show'), 10);

    // Animate Out & Remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400); // Wait for transition
    }, 3000);
}

/**
 * Global Modal Functions
 */
function openModal() {
    const modal = document.getElementById('addModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; 
    }
}

function closeModal() {
    const modal = document.getElementById('addModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function openEditModal(data) {
    const modal = document.getElementById('editModal');
    if (modal) {
        if(document.getElementById('edit_id')) document.getElementById('edit_id').value = data.id;
        if(document.getElementById('edit_title')) document.getElementById('edit_title').value = data.title;
        if(document.getElementById('edit_label')) document.getElementById('edit_label').value = data.label;
        if(document.getElementById('edit_caption')) document.getElementById('edit_caption').value = data.caption;
        
        const preview = document.getElementById('edit_preview');
        if (preview && data.image) {
            let path = data.title ? '../assets/Images/horizon/' : '../assets/Images/gallery_highlights/';
            preview.src = path + data.image;
            preview.style.display = 'block';
        }
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('horizon_preview');
        if (preview) {
            preview.src = reader.result;
            preview.style.display = 'block';
        }
    }
    if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
}

function previewEditImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('edit_preview');
        if (preview) {
            preview.src = reader.result;
            preview.style.display = 'block';
        }
    }
    if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
}

function previewMultipleImages(event, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = ""; 
    
    if (event.target.files) {
        const filesArray = Array.from(event.target.files);
        
        filesArray.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const wrapper = document.createElement("div");
                wrapper.className = "preview-item";
                wrapper.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <span class="image-index">${index + 1}</span>
                `;
                container.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }
}

function deleteSingleImage(recordId, imageName) {
    if (!confirm("Are you sure you want to delete this specific image? This action cannot be undone.")) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const formData = new FormData();
    formData.append('action', 'delete_mission_image');
    formData.append('table', 'web_uganda_mission'); 
    formData.append('id', recordId);
    formData.append('image_name', imageName);
    formData.append('csrf_token', csrfToken);

    fetch('api/admin_actions.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const safeId = imageName.replace(/\./g, '-');
            const wrapper = document.getElementById('img-wrapper-' + safeId);
            if (wrapper) wrapper.remove();
            showToast('Image deleted successfully.', 'success');
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        showToast('A network error occurred.', 'error');
    });
} 

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target == addModal) closeModal();
    if (event.target == editModal) closeEditModal();
}

// ==========================================
// DOMContentLoaded Listeners
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Sidebar Highlighter
    const currentLocation = location.href.split('?')[0]; 
    const menuItems = document.querySelectorAll('.nav-links li a');
    menuItems.forEach(link => {
        if(link.href === currentLocation) link.classList.add('active');
    });

    // 2. Unified Status Toggle (Upgraded with Toast)
    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const table = this.getAttribute('data-table'); 
            const btn = this;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('table', table);
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);

            fetch('api/admin_actions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const isActive = btn.classList.contains('active');
                    const toggleText = 'Hidden';
                    
                    if (isActive) {
                        btn.classList.replace('active', 'inactive');
                        btn.innerHTML = `<i class="fas fa-eye-slash"></i> ${toggleText}`;
                    } else {
                        btn.classList.replace('inactive', 'active');
                        btn.innerHTML = '<i class="fas fa-eye"></i> Active';
                    }
                    showToast('Status updated successfully.', 'success');
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => console.error('Fetch error:', err));
        });
    });

    // 3. Delete Record (Upgraded with Toast)
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const table = this.getAttribute('data-table');
            if (!confirm('Are you sure you want to delete this record?')) return;

            const btn = this;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('table', table);
            formData.append('id', id);
            formData.append('csrf_token', csrfToken);

            fetch('api/admin_actions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Record deleted successfully.', 'success');
                    btn.closest('tr').remove();
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            });
        });
    });

    // 4. Attach Preview Listeners
    const addImgInput = document.querySelector('input[name="horizon_image"], input[name="gallery_image"]');
    if (addImgInput) {
        addImgInput.addEventListener('change', previewImage);
    }

    // ==========================================
    // --- 5. Advanced Display Order Logic ---
    // ==========================================

    async function saveTableOrder(tbody) {
        const tableName = tbody.getAttribute('data-table');
        let orderArray = [];

        // Lock table UI & Add visual highlight
        tbody.classList.add('is-saving');
        
        // Normalize the visual numbers (Force perfect 1, 2, 3... order)
        tbody.querySelectorAll('tr').forEach((row, index) => {
            const input = row.querySelector('.manual-order-input');
            if (input) {
                const newOrder = index + 1;
                input.value = newOrder; 
                
                orderArray.push({
                    id: input.getAttribute('data-id'),
                    display_order: newOrder
                });

                // Flash the row background slightly to indicate processing
                row.classList.add('row-saving-highlight');
            }
        });

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData = new FormData();
            formData.append('action', 'update_order');
            formData.append('table', tableName);
            formData.append('order_data', JSON.stringify(orderArray));
            formData.append('csrf_token', csrfToken);

            const response = await fetch('api/admin_actions.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                showToast('Display order updated successfully!', 'success');
            } else {
                showToast('Failed to save order: ' + data.message, 'error');
            }
        } catch (err) {
            console.error('Fetch error:', err);
            showToast('Network error while saving order.', 'error');
        } finally {
            // Remove locks and highlights
            tbody.classList.remove('is-saving');
            setTimeout(() => {
                tbody.querySelectorAll('tr').forEach(row => row.classList.remove('row-saving-highlight'));
            }, 600); 
        }
    }

    // 5A: Advanced Manual Input (Exact DOM Insertion)
    document.querySelectorAll('.manual-order-input').forEach(input => {
        input.addEventListener('change', function() {
            const tbody = this.closest('tbody');
            const changedRow = this.closest('tr');
            
            // Get requested position and constrain it to valid table limits
            let newPos = parseInt(this.value);
            const allRows = Array.from(tbody.querySelectorAll('tr'));
            const maxPos = allRows.length;
            
            if (isNaN(newPos) || newPos < 1) newPos = 1;
            if (newPos > maxPos) newPos = maxPos;
            
            showToast('Reordering elements...', 'info');

            // 1. Remove the changed row from the array
            const filteredRows = allRows.filter(row => row !== changedRow);
            
            // 2. Insert the row exactly at the requested index (newPos - 1 because arrays start at 0)
            filteredRows.splice(newPos - 1, 0, changedRow);

            // 3. Re-append to DOM to visually move the rows instantly
            filteredRows.forEach(row => tbody.appendChild(row));

            // 4. Run the save function (which normalizes the numbers perfectly)
            saveTableOrder(tbody);
        });
    });

    // 5B: Initialize Advanced Drag-and-Drop
    if (typeof Sortable !== 'undefined') {
        const sortableTables = document.querySelectorAll('tbody[data-table]');
        sortableTables.forEach(tbody => {
            new Sortable(tbody, {
                handle: '.drag-handle',
                animation: 250, 
                ghostClass: 'sortable-ghost',
                easing: "cubic-bezier(1, 0, 0, 1)",
                onEnd: function (evt) {
                    // Only save if the item actually moved to a new position
                    if (evt.oldIndex !== evt.newIndex) {
                        saveTableOrder(evt.to);
                    }
                }
            });
        });
    }
});