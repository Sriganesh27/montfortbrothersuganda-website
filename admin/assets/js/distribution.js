// admin/assets/js/distribution.js

document.addEventListener("DOMContentLoaded", function() {
    const projectSelect = document.getElementById('projectSelect');
    const tableHead = document.getElementById('tableHead');
    const tableBody = document.getElementById('tableBody');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        if (!projectId) {
            tableHead.innerHTML = '<tr><th style="padding: 20px;">Please select a project above to view donors.</th></tr>';
            tableBody.innerHTML = '';
            return;
        }

        loadingSpinner.style.display = 'flex';
        tableBody.innerHTML = '';

        fetch(`api/get_donors.php?project_id=${encodeURIComponent(projectId)}`)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none';
                if (data.error) {
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

        // Render Benefit Year after Students Benefited
        if (data.requires_benefit_year) {
            headHTML += `<th>Benefit Year</th>`;
        }

        // Render Terms Benefited column
        if (data.requires_terms) {
            headHTML += `<th>Terms (Total 3)</th>`;
        }

        // Render Monetary Categories
        data.categories.forEach(cat => {
            headHTML += `<th>${cat} (UGX)</th>`;
        });
        headHTML += `<th>Action</th></tr>`;
        tableHead.innerHTML = headHTML;

        if (data.donors.length === 0) {
            const totalColumns = 5
                + (data.requires_students ? 1 : 0)
                + (data.requires_benefit_year ? 1 : 0)
                + (data.requires_terms ? 1 : 0)
                + data.categories.length;

            tableBody.innerHTML = `<tr><td colspan="${totalColumns}" style="text-align:center; padding: 30px;">No successful donations found.</td></tr>`;
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

            // Pre-fill existing students_benefited value
            if (data.requires_students) {
                bodyHTML += `<td>
                    <input type="number" class="dist-input student-input" value="${donor.students_benefited || 0}" min="0" step="1">
                </td>`;
            }

            // Pre-fill existing benefit_year value. Keep blank when no year is saved.
            if (data.requires_benefit_year) {
                const benefitYear = donor.benefit_year ?? '';
                bodyHTML += `<td>
                    <input type="number" class="dist-input benefit-year-input" value="${benefitYear}" min="1900" max="2100" step="1" placeholder="YYYY">
                </td>`;
            }

            // Pre-fill existing terms_benefited value
            if (data.requires_terms) {
                bodyHTML += `<td>
                    <input type="number" class="dist-input term-input" value="${donor.terms_benefited || 0}" min="0" max="3" step="1">
                </td>`;
            }

            data.categories.forEach(cat => {
                let existingVal = 0;
                if (data.distributions && data.distributions[donor.id]) {
                    let match = data.distributions[donor.id].find(d => d.category_name === cat);
                    if (match) existingVal = Number(match.amount_allocated);
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

                // Gather Benefit Year Data
                const benefitYearInput = row.querySelector('.benefit-year-input');
                let benefitYear = null;

                if (benefitYearInput && benefitYearInput.value.trim() !== '') {
                    const rawBenefitYear = benefitYearInput.value.trim();

                    if (!/^\d{4}$/.test(rawBenefitYear)) {
                        showToast('Benefit Year must contain exactly 4 digits.', 'error');
                        benefitYearInput.focus();
                        return;
                    }

                    benefitYear = parseInt(rawBenefitYear, 10);

                    if (benefitYear < 1900 || benefitYear > 2100) {
                        showToast('Benefit Year must be between 1900 and 2100.', 'error');
                        benefitYearInput.focus();
                        return;
                    }
                }

                // Gather Term Data
                const termInput = row.querySelector('.term-input');
                const termsBenefited = termInput ? (parseInt(termInput.value) || 0) : 0;

                // Client-side Validation for Terms
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

                const headers = {
                    'Content-Type': 'application/json'
                };

                if (csrfToken) {
                    headers['X-CSRF-Token'] = csrfToken;
                }

                // Send to Server
                fetch('api/save_distribution.php', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        donation_id: donationId,
                        allocations: allocations,
                        students_benefited: studentsBenefited,
                        benefit_year: benefitYear,
                        terms_benefited: termsBenefited
                    })
                })
                .then(res => res.json())
                .then(resData => {
                    this.innerHTML = btnOriginalHTML;
                    this.disabled = false;

                    if (resData.success) {
                        this.classList.add('success-state');
                        this.innerHTML = '<i class="fas fa-check"></i> Saved';
                        showToast('Record updated successfully!', 'success');
                        setTimeout(() => {
                            this.classList.remove('success-state');
                            this.innerHTML = btnOriginalHTML;
                        }, 2000);
                    } else {
                        showToast('Error saving data: ' + (resData.message || resData.error || 'Unknown error'), 'error');
                    }
                })
                .catch(err => {
                    console.error('Save distribution error:', err);
                    this.innerHTML = btnOriginalHTML;
                    this.disabled = false;
                    showToast('Network error occurred.', 'error');
                });
            });
        });
    }
});
