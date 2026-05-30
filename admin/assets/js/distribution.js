// admin/assets/js/distribution.js

document.addEventListener("DOMContentLoaded", function() {
    const projectSelect = document.getElementById('projectSelect');
    const tableHead = document.getElementById('tableHead');
    const tableBody = document.getElementById('tableBody');
    const loadingSpinner = document.getElementById('loadingSpinner');

    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        if (!projectId) {
            tableHead.innerHTML = '<tr><th style="padding: 20px;">Please select a project above to view donors.</th></tr>';
            tableBody.innerHTML = '';
            return;
        }

        loadingSpinner.style.display = 'flex';
        tableBody.innerHTML = ''; 

        fetch(`api/get_donors.php?project_id=${projectId}`)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none';
                if(data.error) {
                    showToast(data.error, 'error');
                    return;
                }
                renderTable(data, projectId);
            })
            .catch(err => {
                loadingSpinner.style.display = 'none';
                console.error("Error fetching data: ", err);
                showToast("Failed to load data. Check network.", "error");
            });
    });

    function renderTable(data, projectId) {
        let headHTML = `<tr>
            <th>Donor ID</th>
            <th>Name</th>
            <th>Donated (Orig)</th>
            <th>Received (UGX)</th>`;
        
        // Render Students Benefited column if the project requires it
        if (data.requires_students) {
            headHTML += `<th>Students Benefited</th>`;
        }

        // NEW: Render Terms Benefited column
        if (data.requires_terms) {
            headHTML += `<th>Terms (Total 3)</th>`;
        }

        // Render Monetary Categories
        data.categories.forEach(cat => {
            headHTML += `<th>${cat} (UGX)</th>`;
        });
        headHTML += `<th>Action</th></tr>`;
        tableHead.innerHTML = headHTML;

        if(data.donors.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding: 30px;">No successful donations found.</td></tr>`;
            return;
        }

        let bodyHTML = '';
        data.donors.forEach(donor => {
            let receivedUgx = Number(donor.amount_received);
            
            bodyHTML += `<tr data-donation-id="${donor.id}">
                <td><span class="txn-id">${donor.receipt_number || 'N/A'}</span></td>
                <td><strong>${donor.full_name}</strong></td>
                <td><span style="color: var(--text-muted);">${donor.amount} ${donor.currency}</span></td>
                <td class="received-amt" data-val="${receivedUgx}"><strong>${receivedUgx.toLocaleString()}</strong></td>`;
            
            // PRE-FILL EXISTING DATA
            if (data.requires_students) {
                bodyHTML += `<td>
                    <input type="number" class="dist-input student-input" value="${donor.students_benefited || 0}" min="0">
                </td>`;
            }

            // NEW: Render input for terms_benefited (restricted to max 3)
            if (data.requires_terms) {
                bodyHTML += `<td>
                    <input type="number" class="dist-input term-input" value="${donor.terms_benefited || 0}" min="0" max="3">
                </td>`;
            }

            data.categories.forEach(cat => {
                let existingVal = 0;
                if(data.distributions && data.distributions[donor.id]) {
                    let match = data.distributions[donor.id].find(d => d.category_name === cat);
                    if(match) existingVal = Number(match.amount_allocated);
                }
                bodyHTML += `<td>
                    <input type="number" class="dist-input money-input" data-category="${cat}" value="${existingVal}" min="0">
                </td>`;
            });

            bodyHTML += `<td><button class="save-btn"><i class="fas fa-save"></i> Save</button></td></tr>`;
        });
        
        tableBody.innerHTML = bodyHTML;
        attachSaveListeners();
    }

    function attachSaveListeners() {
        document.querySelectorAll('.save-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const donationId = row.dataset.donationId;
                const receivedUGX = parseFloat(row.querySelector('.received-amt').dataset.val);
                
                let allocations = [];
                let totalMonetaryAllocated = 0;
                const btnOriginalHTML = this.innerHTML;

                // Gather Monetary Data
                row.querySelectorAll('.money-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    totalMonetaryAllocated += val;
                    allocations.push({
                        category: input.dataset.category,
                        amount: val
                    });
                });

                // Gather Student Data
                const studentInput = row.querySelector('.student-input');
                const studentsBenefited = studentInput ? (parseInt(studentInput.value) || 0) : 0;

                // NEW: Gather Term Data
                const termInput = row.querySelector('.term-input');
                const termsBenefited = termInput ? (parseInt(termInput.value) || 0) : 0;
                
                // NEW: Client-side Validation for Terms
                if (termsBenefited > 3) {
                    showToast('Terms cannot exceed 3!', 'error');
                    return;
                }

                // Validation Check
                if (totalMonetaryAllocated > receivedUGX) {
                    showToast(`Financial Error! Allocated (${totalMonetaryAllocated.toLocaleString()}) exceeds received amount (${receivedUGX.toLocaleString()})!`, 'error');
                    return;
                }

                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving';
                this.disabled = true;

                // Send to Server
                fetch('api/save_distribution.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        donation_id: donationId,
                        allocations: allocations,
                        students_benefited: studentsBenefited,
                        terms_benefited: termsBenefited // <-- Appended here
                    })
                })
                .then(res => res.json())
                .then(resData => {
                    this.innerHTML = btnOriginalHTML;
                    this.disabled = false;
                    
                    if(resData.success) {
                        this.classList.add('success-state');
                        this.innerHTML = '<i class="fas fa-check"></i> Saved';
                        showToast('Record updated successfully!', 'success');
                        setTimeout(() => {
                            this.classList.remove('success-state');
                            this.innerHTML = btnOriginalHTML;
                        }, 2000);
                    } else {
                        showToast('Error saving data: ' + resData.message, 'error');
                    }
                })
                .catch(err => {
                    this.innerHTML = btnOriginalHTML;
                    this.disabled = false;
                    showToast('Network error occurred.', 'error');
                });
            });
        });
    }
});