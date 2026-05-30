// erp/public/assets/scripts/admin_manage_apps.js

// 1. SMART DYNAMIC BASE URL FOR JAVASCRIPT
// This automatically figures out if you are on localhost or a live server!
function getSystemBaseUrl() {
    const baseUrl = window.location.origin;
    const pathname = window.location.pathname;
    const erpPos = pathname.indexOf('/erp');
    
    if (erpPos !== -1) {
        return baseUrl + pathname.substring(0, erpPos + 4) + '/public';
    }
    return baseUrl; // Fallback for live server where /public is the root
}

// 2. SMART MEDIA URL CLEANER
// This chops off any messy database strings and guarantees a perfect image/doc link every time
function getCleanMediaUrl(dbPath) {
    if (!dbPath || dbPath.trim() === '') return '';
    if (dbPath.startsWith('http')) return dbPath; // Already a full URL
    
    // Find exactly where 'assets/' starts and slice it perfectly
    const assetsIndex = dbPath.indexOf('assets/');
    if (assetsIndex !== -1) {
        return getSystemBaseUrl() + '/' + dbPath.substring(assetsIndex);
    }
    
    // Fallback if 'assets/' is missing
    return getSystemBaseUrl() + '/' + dbPath.replace(/^\/+/, '');
}


function loadApplications() {
    const status = document.getElementById('statusFilter').value;
    
    // Use dynamic API URL instead of hardcoded localhost
    const apiUrl = `${getSystemBaseUrl()}/api/manage_applications?action=fetch_list&status=${status}`;
    
    fetch(apiUrl)
    .then(r => r.json())
    .then(d => {
        let html = '';
        d.data.forEach(app => {
            let actions = `<button class="btn-action" style="background:#17a2b8;" onclick="window.open('${getSystemBaseUrl()}/admin/application/view?id=${app.app_id}', '_blank')">Review Details</button> `;            
            
            if (app.status === 'Pending') {
                actions += `<button class="btn-action btn-shortlist" onclick="updateAppStatus(${app.app_id}, 'Shortlisted')">Shortlist</button>`;
            } else if (app.status === 'Shortlisted') {
                actions += `<button class="btn-action btn-select" onclick="updateAppStatus(${app.app_id}, 'Selected')">Select</button>`;
            } else if (app.status === 'Selected') {
                // FIX: Removed .php from the admin link so it works perfectly with the new Router!
                actions += `<button class="btn-action btn-admit" onclick="window.location.href='${getSystemBaseUrl()}/admin?prefill_app_id=${app.app_id}#admission'">Admit</button>`;
            }

            const sNone = app.scholarship_status === 'None' ? 'selected' : '';
            const sApp = app.scholarship_status === 'Applied' ? 'selected' : '';
            const sConf = app.scholarship_status === 'Confirmed' ? 'selected' : '';

            html += `<tr>
                <td>${app.ref_number}</td>
                <td>${app.student_name} ${app.student_surname}</td>
                <td>${app.applied_class}</td>
                <td><strong>${app.status}</strong></td>
                <td>
                    <select class="schol-select" onchange="updateAppScholarship(${app.app_id}, this.value)">
                        <option value="None" ${sNone}>No Aid</option>
                        <option value="Applied" ${sApp}>Applied</option>
                        <option value="Confirmed" ${sConf}>Confirmed</option>
                    </select>
                </td>
                <td>${actions}</td>
            </tr>`;
        });
        document.getElementById('appBody').innerHTML = html;
    })
    .catch(error => console.error("Fetch Error:", error));
}

function viewApplication(id) {
    const apiUrl = `${getSystemBaseUrl()}/api/manage_applications?action=fetch_single&app_id=${id}`;
    
    fetch(apiUrl)
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            const app = d.data;
            const fullName = `${app.student_name || ''} ${app.middle_name || ''} ${app.student_surname || ''}`.trim();
            const address = `${app.address_house || ''} ${app.address_street || ''}, ${app.address_village || ''}, ${app.address_district || ''}, ${app.address_country || ''} (PO Box: ${app.address_postal || 'N/A'})`.trim();
            
            // FIX: Use the smart URL cleaner for the photo
            let photoSrc = getCleanMediaUrl(app.photo_path);
            const fallbackAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=random&color=fff`;
            
            if (!photoSrc || photoSrc.includes('default_profile')) {
                photoSrc = fallbackAvatar;
            }

            // Format Subject Marks
            let subjectMarksHtml = '';
            if (app.subject_marks && app.subject_marks !== '[]') {
                try {
                    let marks = JSON.parse(app.subject_marks);
                    subjectMarksHtml = `<div class="grid-item full-width" style="margin-top: 10px; background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0;">
                        <strong>Submitted Subjects & Grades</strong>
                        <ul style="margin: 5px 0 0 20px; font-size: 14px;">`;
                    marks.forEach(m => {
                        subjectMarksHtml += `<li>${m.subject}: Mark: <strong>${m.mark || '-'}</strong> | Grade: <strong>${m.grade || '-'}</strong></li>`;
                    });
                    subjectMarksHtml += `</ul></div>`;
                } catch(e) {}
            }

            // Format Exams (PLE / UCE)
            let examsHtml = '';
            if(app.ple_ref) examsHtml += `<div class="grid-item"><strong>PLE Index / Score</strong> <p>${app.ple_ref} / ${app.ple_score || 'N/A'}</p></div>`;
            if(app.uce_ref) examsHtml += `<div class="grid-item"><strong>UCE Index / Score</strong> <p>${app.uce_ref} / ${app.uce_score || 'N/A'}</p></div>`;

            // FIX: Use the smart URL cleaner for the Document Link
            let docHtml = '';
            if (app.prev_marks_doc && app.prev_marks_doc.trim() !== '') {
                let docSrc = getCleanMediaUrl(app.prev_marks_doc);
                
                docHtml = `
                <div class="grid-item full-width" style="margin-top:15px; padding:15px; background: #eef9f0; border: 1px solid #28a745; border-radius: 8px;">
                    <p style="color:#155724; margin: 0 0 10px 0; font-weight:bold;">Submitted Academic Document:</p>
                    <a href="${docSrc}" target="_blank" style="display:inline-block; background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:4px; font-weight:bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        📄 View Document (Opens in new tab)
                    </a>
                </div>`;
            }

            let html = `
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 2px solid #eee;">
                    <div>
                        <h3 style="margin:0; font-size: 24px; color: var(--primary-color);">${fullName}</h3>
                        <p style="margin:5px 0; color: #666; font-size: 14px;">Reference: <strong>${app.ref_number || 'N/A'}</strong> | Registered: <strong>${app.date_of_registration || 'N/A'}</strong></p>
                        <span style="background:var(--primary-color); color:white; padding:4px 10px; border-radius:15px; font-size:12px;">${app.level || ''}</span>
                        <span style="background:var(--accent-color); color:white; padding:4px 10px; border-radius:15px; font-size:12px;">${app.applied_class || ''}</span>
                        <span style="background:#6c757d; color:white; padding:4px 10px; border-radius:15px; font-size:12px;">${app.academic_year || ''} - ${app.term || ''}</span>
                    </div>
                    <div>
                        <img src="${photoSrc}" onerror="this.src='${fallbackAvatar}'" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" alt="Student Photo">
                    </div>
                </div>

                <div class="profile-grid-system" style="max-height: 60vh; overflow-y: auto; padding-right: 10px;">
                    
                    <fieldset><legend>1. Personal Details</legend>
                        <div class="grid-row">
                            <div class="grid-item"><strong>Gender</strong> <p>${app.gender || 'N/A'}</p></div>
                            <div class="grid-item"><strong>Date of Birth</strong> <p>${app.dob || 'N/A'}</p></div>
                            <div class="grid-item"><strong>Nationality</strong> <p>${app.nationality || 'N/A'}</p></div>
                            <div class="grid-item full-width"><strong>Address</strong> <p>${address}</p></div>
                        </div>
                    </fieldset>

                    <fieldset><legend>2. Family & Guardian</legend>
                        <div class="grid-row">
                            <div class="grid-item"><strong>Father</strong> <p>${app.father_name || 'N/A'}<br>Phone: ${app.father_contact || 'N/A'}<br>Occ: ${app.father_occupation || 'N/A'}</p></div>
                            <div class="grid-item"><strong>Mother</strong> <p>${app.mother_name || 'N/A'}<br>Phone: ${app.mother_contact || 'N/A'}<br>Occ: ${app.mother_occupation || 'N/A'}</p></div>
                            <div class="grid-item"><strong>Guardian (${app.guardian_relation || 'N/A'})</strong> <p>${app.guardian_name || 'N/A'}<br>Phone: ${app.guardian_contact || 'N/A'}<br>Occ: ${app.guardian_occupation || 'N/A'}</p></div>
                        </div>
                    </fieldset>

                    <fieldset><legend>3. Academic History</legend>
                        <div class="grid-row">
                            <div class="grid-item"><strong>Former School</strong> <p>${app.former_school || 'N/A'} (LIN: ${app.former_school_lin || 'N/A'})</p></div>
                            ${examsHtml}
                            ${subjectMarksHtml}
                            ${docHtml}
                        </div>
                    </fieldset>

                    <fieldset><legend>4. Medical & Additional Information</legend>
                        <div class="grid-row">
                            <div class="grid-item full-width"><p style="color:#d35400; font-weight:bold;">${app.more_info || 'No medical conditions or additional information provided.'}</p></div>
                        </div>
                    </fieldset>

                </div>
            `;
            
            document.getElementById('appModalBody').innerHTML = html;
            document.getElementById('appReviewModal').style.display = 'flex';
        } else {
            alert(d.message);
        }
    });
}

function updateAppStatus(id, stat) { 
    const fd = new FormData(); 
    fd.append('action', 'update_status'); 
    fd.append('app_id', id); 
    fd.append('status', stat); 
    
    fetch(`${getSystemBaseUrl()}/api/manage_applications`, {method: 'POST', body: fd})
    .then(() => loadApplications()); 
}

function updateAppScholarship(id, stat) { 
    const fd = new FormData(); 
    fd.append('action', 'update_scholarship'); 
    fd.append('app_id', id); 
    fd.append('scholarship', stat); 
    
    fetch(`${getSystemBaseUrl()}/api/manage_applications`, {method: 'POST', body: fd})
    .then(r => r.json())
    .then(d => alert(d.message)); 
}

if (document.getElementById('statusFilter')) {
    loadApplications();
}