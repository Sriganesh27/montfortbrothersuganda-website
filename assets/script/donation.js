/**
 * MONTFORT BROTHERS OF ST. GABRIEL - UGANDA
 * DONATION DIALOG SYSTEM (With Translation Bridge)
 */
(function() {
    'use strict';

    // Translation Helper Function
    function t(key, fallback) {
        return (window.jsTrans && window.jsTrans[key] && window.jsTrans[key] !== key) ? window.jsTrans[key] : fallback;
    }

    const modal = document.getElementById('community-donation-overlay');
    const form = document.getElementById('mbsg-donation-form');
    const nextBtn = document.getElementById('submit-next-btn');
    const prevBtn = document.getElementById('back-to-edit');
    const cancelBtn = document.getElementById('modal-cancel-btn');
    const stepContents = document.querySelectorAll('.mbsg-step-content');
    const stepTitle = document.getElementById('modal-step-title');
    const stepSubtitle = document.getElementById('modal-step-subtitle');
    const currencySwitchers = document.querySelectorAll('.mbsg-switcher-link');
    const currencyInput = document.getElementById('selected-currency');
    const statusRadios = document.querySelectorAll('input[name="payment_status"]');
    const successFields = document.getElementById('mbsg-success-fields');
    const failedFields = document.getElementById('mbsg-failed-fields');
    const printBtn = document.getElementById('print-receipt');
    const downloadBtn = document.getElementById('download-receipt');
    
    if (!modal) return;

    let currentStep = 1;
    let isSubmitting = false;
    let lastFocusedButton = null;
    let lastSubmissionId = null;
    let lastReceiptData = null;

    const bankInfo = {
        'USD': { swift: 'CERBUGKA', bank: 'Centenary Bank', accNo: '3100110331', accName: 'MONTFORT BROTHERS S.G. FOUNDATION LIMITED' },
        'EURO_GBP': { swift: 'CERBUGKA', bank: 'Centenary Bank', accNo: '3100110332', accName: 'MONTFORT BROTHERS S.G. FOUNDATION LIMITED' },
        'UGX': { swift: 'CERBUGKA', bank: 'Centenary Bank,Kawuku', accNo: '3100121919', accName: 'MONTFORT BROTHERS S.G. FOUNDATION LIMITED' }
    };

    function showStep(step) {
        if (step < 1 || step > 4) return;
        stepContents.forEach(el => el.style.display = 'none');
        
        const target = document.getElementById(`step-${step}`);
        if (target) target.style.display = 'block';

        const titles = [t('title1', 'Donor Information'), t('title2', 'Bank Details'), t('title3', 'Transaction Status'), t('title4', 'Donation Receipt')];
        if (stepTitle) stepTitle.textContent = titles[step - 1];
        if (stepSubtitle) stepSubtitle.textContent = t('step', 'Step') + ` ${step} ` + t('of', 'of') + ` 4`;

        if (step === 1) {
            if (nextBtn) { nextBtn.style.display = 'none'; nextBtn.disabled = false; }
            if (prevBtn) prevBtn.style.display = 'none';
            if (cancelBtn) cancelBtn.style.display = 'inline-block';
        } else if (step === 4) {
            if (nextBtn) { nextBtn.style.display = 'inline-block'; nextBtn.textContent = t('btnClose', 'Close'); nextBtn.disabled = false; }
            if (prevBtn) prevBtn.style.display = 'none';
            if (cancelBtn) cancelBtn.style.display = 'none';
        } else {
            if (nextBtn) { nextBtn.style.display = 'inline-block'; nextBtn.textContent = (step === 3) ? t('btnFinish', 'Confirm & Finish') : t('btnNext', 'Next'); nextBtn.disabled = false; }
            if (prevBtn) prevBtn.style.display = 'inline-block';
            if (cancelBtn) cancelBtn.style.display = 'inline-block';
        }

        if (step === 2) {
            if (!currencyInput.value) { alert(t('errCurrency', 'Please select a currency first.')); showStep(1); return; }
            updateMiniSummary();
            loadBankDetails();
        } else if (step === 3) {
            updateMiniSummary();
            toggleStatusFields();
        }
        currentStep = step;
    }

    function validateStep1() {
        const requiredFields = form.querySelectorAll('#step-1 [required]');
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                showCustomAlert(t('errDetails', 'Please enter your details.'));
                field.focus();
                return false;
            }
        }
        
        if (!form.elements['contribution_purpose'].value) {
            showCustomAlert(t('errPurpose', 'Please select a contribution purpose from the support page.'));
            return false;
        }
        
        const email = form.elements['email'].value;
        const phone = form.elements['contact_number'].value;
        
        if (!email.trim() && !phone.trim()) {
            showCustomAlert(t('errContact', 'Please provide either an email address or a phone number.'));
            return false;
        }
        
        if (email.trim() && !/^\S+@\S+\.\S+$/.test(email)) {
            showCustomAlert(t('errEmail', 'Please enter a valid email address.'));
            form.elements['email'].focus();
            return false;
        }
        
        if (phone.trim() && phone.trim().length < 6) {
            showCustomAlert(t('errPhone', 'Please enter a valid phone number.'));
            form.elements['contact_number'].focus();
            return false;
        }
        
        return true;
    }

    function validateStep3() {
        const status = document.querySelector('input[name="payment_status"]:checked')?.value;
        if (!status) return false;
        
        if (status === 'success') {
            const amount = form.elements['amount'].value;
            const txId = form.elements['transaction_id'].value;
            if (!amount || parseFloat(amount) <= 0) {
                showCustomAlert(t('errAmount', 'Please enter a valid amount.'));
                form.elements['amount'].focus();
                return false;
            }
            if (!txId.trim()) {
                showCustomAlert(t('errTxid', 'Please enter the transaction ID.'));
                form.elements['transaction_id'].focus();
                return false;
            }
        } else {
            const reason = form.elements['failure_reason']?.value;
            if (!reason) {
                showCustomAlert(t('errReason', 'Please select a failure reason.'));
                return false;
            }
        }
        return true;
    }

    function updateMiniSummary() {
        const summaryDivs = document.querySelectorAll('.mbsg-donor-mini-summary');
        if (!summaryDivs.length) return;

        const getVal = (name) => {
            const field = form.elements[name];
            return field ? field.value.trim() || t('notProvided', 'Not provided') : t('notProvided', 'Not provided');
        };

        const summaryHTML = `
            <div class="summary-grid">
                <span><strong>${t('lblDonor', 'Donor:')}</strong> ${escapeHtml(getVal('full_name'))}</span>
                <span><strong>${t('lblEmail', 'Email:')}</strong> ${escapeHtml(getVal('email'))}</span>
                <span><strong>${t('lblPhone', 'Phone:')}</strong> ${escapeHtml(getVal('country_code') + ' ' + getVal('contact_number'))}</span>
                <span><strong>${t('lblLocation', 'Location:')}</strong> ${escapeHtml(getVal('location'))}</span>
                <span><strong>${t('lblPurpose', 'Purpose:')}</strong> ${escapeHtml(getVal('contribution_purpose'))}</span>
                <span><strong>${t('lblProject', 'Project ID:')}</strong> ${escapeHtml(getVal('project_id') || t('notSelected', 'Not selected'))}</span>
                <span><strong>${t('lblCurrency', 'Currency:')}</strong> ${escapeHtml(getVal('currency') || t('notSelected', 'Not selected'))}</span>
            </div>
        `;

        summaryDivs.forEach(div => div.innerHTML = summaryHTML);
    }

    function escapeHtml(unsafe) {
        if (!unsafe || unsafe === t('notProvided', 'Not provided')) return unsafe;
        return String(unsafe).replace(/[&<>"']/g, function(m) {
            if(m === '&') return '&amp;';
            if(m === '<') return '&lt;';
            if(m === '>') return '&gt;';
            if(m === '"') return '&quot;';
            if(m === "'") return '&#039;';
            return m;
        });
    }

    function loadBankDetails() {
        const board = document.getElementById('mbsg-bank-data-board');
        if (!board) return;

        const currency = currencyInput?.value;
        if (!currency) { board.innerHTML = `<p style="color: red;">${t('errCurrency', 'Please select a currency first.')}</p>`; return; }
        
        const data = bankInfo[currency] || bankInfo['USD'];

        board.innerHTML = `
            <div class="mbsg-bank-info">
                <i class="fas fa-info-circle" style="font-size: 1.2rem; color: #007bff;"></i>
                <p style="margin: 0; font-size: 0.95rem; line-height: 1.5;">
                    ${t('bankMsg', 'Please transfer the amount to the following bank account for donation.')} 
                    <br><strong>${t('bankNote', 'Note:')}</strong> ${t('bankNoteDesc', 'Copy the transaction ID for the next step.')}
                </p>
            </div>
            <table class="mbsg-bank-table">
                <tr><td>${t('bankSwift', 'Swift Code')}</td><td><strong>${data.swift}</strong></td></tr>
                <tr><td>${t('bankName', 'Bank Name')}</td><td><strong>${data.bank}</strong></td></tr>
                <tr><td>${t('bankAccNo', 'Account No.')}</td><td><strong>${data.accNo}</strong></td></tr>
                <tr><td>${t('bankAccName', 'Account Name')}</td><td>${data.accName}</td></tr>
            </table>
        `;
    }

    function toggleStatusFields() {
        const status = document.querySelector('input[name="payment_status"]:checked')?.value;
        const failureReasonSelect = form.elements['failure_reason'];
        const otherDescField = form.elements['other_desc'];
        
        if (status === 'success') {
            successFields.style.display = 'grid';
            failedFields.style.display = 'none';
            if (failureReasonSelect) failureReasonSelect.required = false;
            if (otherDescField) { otherDescField.required = false; otherDescField.style.display = 'none'; }
            form.elements['amount'].required = true;
            form.elements['transaction_id'].required = true;
        } else {
            successFields.style.display = 'none';
            failedFields.style.display = 'grid';
            form.elements['amount'].required = false;
            form.elements['transaction_id'].required = false;
            failureReasonSelect.required = true;
            
            if (otherDescField) { otherDescField.style.display = 'none'; otherDescField.required = false; }
        }
    }

    function handleFailureReasonChange() {
        const reason = form.elements['failure_reason'].value;
        const otherDescField = form.elements['other_desc'];
        
        if (reason === 'Other') {
            otherDescField.style.display = 'block';
            otherDescField.required = true;
        } else {
            otherDescField.style.display = 'none';
            otherDescField.required = false;
            otherDescField.value = '';
        }
    }

    function resetModal() {
        form.reset();
        if (currencyInput) currencyInput.value = '';
        currentStep = 1;
        isSubmitting = false;
        lastSubmissionId = null;
        lastReceiptData = null;
        if (nextBtn) nextBtn.disabled = false;
        
        currencySwitchers.forEach(btn => btn.classList.remove('active'));
        
        const otherDescField = form.elements['other_desc'];
        if (otherDescField) { otherDescField.style.display = 'none'; otherDescField.required = false; }
        
        const purposeValue = document.getElementById('purpose-value');
        const purposeHidden = document.getElementById('contribution_purpose');
        if (purposeValue) purposeValue.textContent = t('notSelected', 'Not selected');
        if (purposeHidden) purposeHidden.value = '';
        
        const projectValue = document.getElementById('project-value');
        const projectHidden = document.getElementById('project_id');
        if (projectValue) projectValue.textContent = t('notSelected', 'Not selected');
        if (projectHidden) projectHidden.value = '';
        
        showStep(1);
    }

    function closeModal() {
        if (modal.open) {
            modal.close();
            document.body.classList.remove('mbsg-modal-locked');
            resetModal();
            if (lastFocusedButton) lastFocusedButton.focus();
        }
    }

    function displayFinalMessage(data) {
        const msgDiv = document.getElementById('final-status-message');
        const status = document.querySelector('input[name="payment_status"]:checked')?.value;
        
        const getVal = (name) => {
            const field = form.elements[name];
            return field ? field.value.trim() || t('notProvided', 'Not provided') : t('notProvided', 'Not provided');
        };
        
        const isAnonymous = form.elements['is_anonymous']?.checked ? t('yes', 'Yes') : t('no', 'No');
        
        const receiptData = {
            id: data.id || 'N/A',
            receipt_number: data.receipt_number || 'N/A',
            date: new Date().toLocaleDateString(),
            full_name: getVal('full_name'),
            email: getVal('email'),
            phone: getVal('country_code') + ' ' + getVal('contact_number'),
            location: getVal('location'),
            purpose: getVal('contribution_purpose'),
            project_id: getVal('project_id'),
            currency: getVal('currency'),
            payment_status: status,
            amount: getVal('amount'),
            transaction_id: getVal('transaction_id'),
            failure_reason: getVal('failure_reason'),
            is_anonymous: isAnonymous
        };
        
        lastSubmissionId = data.id;
        lastReceiptData = receiptData;
        
        let message = '';
        let messageClass = '';
        
        if (status === 'success') {
            messageClass = 'success';
            message = `<h3>${t('msgSuccessTitle', 'Thank You for Your Donation!')}</h3><p>${t('msgSuccessP1', 'We shall inform you as we receive.')}</p><p>${t('msgSuccessP2', 'We shall remain united.')}</p>`;
        } else {
            messageClass = 'failed';
            message = `<h3>${t('msgFailTitle', 'Transaction Not Completed')}</h3><p>${t('msgFailP1', "We've recorded the issue with your transaction.")}</p><p>${t('msgFailP2', 'Our team will contact you shortly to assist with the process.')}</p>`;
        }
        
        message += `
            <div class="donor-summary">
                <h4>${t('summaryTitle', 'Donation Summary')}</h4>
                <div class="summary-row"><span class="summary-label">${t('lblReceiptNum', 'Receipt Number:')}</span><span class="summary-value"><strong>${receiptData.receipt_number}</strong></span></div>
                <div class="summary-row"><span class="summary-label">${t('lblProject', 'Project ID:')}</span><span class="summary-value">${escapeHtml(receiptData.project_id)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblDonor', 'Donor Name:')}</span><span class="summary-value">${escapeHtml(receiptData.full_name)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblEmail', 'Email:')}</span><span class="summary-value">${escapeHtml(receiptData.email)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblPhone', 'Phone:')}</span><span class="summary-value">${escapeHtml(receiptData.phone)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblLocation', 'Location:')}</span><span class="summary-value">${escapeHtml(receiptData.location)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblAnon', 'Anonymous:')}</span><span class="summary-value">${receiptData.is_anonymous}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblPurpose', 'Purpose:')}</span><span class="summary-value">${escapeHtml(receiptData.purpose)}</span></div>
                ${status === 'success' ? `
                <div class="summary-row"><span class="summary-label">${t('lblAmount', 'Amount:')}</span><span class="summary-value">${escapeHtml(receiptData.currency)} ${escapeHtml(receiptData.amount)}</span></div>
                <div class="summary-row"><span class="summary-label">${t('lblTxid', 'Transaction ID:')}</span><span class="summary-value">${escapeHtml(receiptData.transaction_id)}</span></div>
                ` : `
                <div class="summary-row"><span class="summary-label">${t('lblStatus', 'Status:')}</span><span class="summary-value">${t('failed', 'Failed')} - ${escapeHtml(receiptData.failure_reason)}</span></div>
                `}
                <div class="summary-row"><span class="summary-label">${t('lblDate', 'Date:')}</span><span class="summary-value">${receiptData.date}</span></div>
            </div>
        `;
        
        if (msgDiv) {
            msgDiv.className = `mbsg-final-message ${messageClass}`;
            msgDiv.innerHTML = message;
        }
        preparePrintReceipt(receiptData);
    }

    function preparePrintReceipt(data) {
        const printDetails = document.getElementById('print-receipt-details');
        if (!printDetails) return;
        
        const receiptNumber = data.receipt_number || 'N/A';
        let detailsHTML = `
            <div class="print-row"><span class="print-label">${t('lblReceiptNum', 'Receipt Number:')}</span><span class="print-value"><strong>${receiptNumber}</strong></span></div>
            <div class="print-row"><span class="print-label">${t('lblProject', 'Project ID:')}</span><span class="print-value">${escapeHtml(data.project_id)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblDate', 'Date:')}</span><span class="print-value">${data.date}</span></div>
            <div class="print-row"><span class="print-label">${t('lblDonor', 'Donor Name:')}</span><span class="print-value">${escapeHtml(data.full_name)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblEmail', 'Email:')}</span><span class="print-value">${escapeHtml(data.email)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblPhone', 'Phone:')}</span><span class="print-value">${escapeHtml(data.phone)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblLocation', 'Location:')}</span><span class="print-value">${escapeHtml(data.location)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblAnon', 'Anonymous:')}</span><span class="print-value">${data.is_anonymous}</span></div>
            <div class="print-row"><span class="print-label">${t('lblPurpose', 'Purpose:')}</span><span class="print-value">${escapeHtml(data.purpose)}</span></div>
            <div class="print-row"><span class="print-label">${t('lblCurrency', 'Currency:')}</span><span class="print-value">${escapeHtml(data.currency)}</span></div>
        `;
        
        if (data.payment_status === 'success') {
            detailsHTML += `
                <div class="print-row"><span class="print-label">${t('lblAmount', 'Amount:')}</span><span class="print-value">${escapeHtml(data.currency)} ${escapeHtml(data.amount)}</span></div>
                <div class="print-row"><span class="print-label">${t('lblTxid', 'Transaction ID:')}</span><span class="print-value">${escapeHtml(data.transaction_id)}</span></div>
                <div class="print-row"><span class="print-label">${t('lblStatus', 'Status:')}</span><span class="print-value">${t('success', 'Successful')}</span></div>
            `;
        } else {
            detailsHTML += `
                <div class="print-row"><span class="print-label">${t('lblStatus', 'Status:')}</span><span class="print-value">${t('failed', 'Failed')}</span></div>
                <div class="print-row"><span class="print-label">${t('lblReason', 'Reason:')}</span><span class="print-value">${escapeHtml(data.failure_reason)}</span></div>
            `;
        }
        printDetails.innerHTML = detailsHTML;
    }

    function printReceipt() {
        if (!lastReceiptData) { alert(t('errPrint', 'No receipt data available to print.')); return; }
        
        const receiptNumber = lastReceiptData.receipt_number || 'N/A';
        const printWindow = window.open('', '_blank');
        const receiptContent = document.getElementById('receipt-print-content')?.innerHTML;
        
        if (!receiptContent) { alert(t('errReceiptNotFound', 'Receipt content not found.')); return; }
        
        const docTitle = t('printTitle', 'Donation Receipt');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html><head><title>${docTitle} ${receiptNumber}</title><style>
                body { font-family: 'Courier New', monospace; padding: 20px; }
                .print-receipt { max-width: 800px; margin: 0 auto; padding: 30px; border: 2px solid #333; }
                .print-header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #333; }
                .print-row { display: flex; padding: 8px 0; border-bottom: 1px dotted #ccc; }
                .print-label { font-weight: bold; width: 150px; }
                .print-value { flex: 1; }
            </style></head><body>${receiptContent}</body></html>
        `);
        
        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.print();
            printWindow.onafterprint = function() { printWindow.close(); };
        };
    }

    function downloadReceipt() {
        if (!lastReceiptData) { alert(t('errDownload', 'No receipt data available to download.')); return; }
        const data = lastReceiptData;
        const receiptNumber = data.receipt_number || 'N/A';
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        const soonMsg = t('downloadSoon', 'RECEIPT DOWNLOAD FEATURE COMING SOON');
        
        doc.setFont("courier", "normal");
        doc.text(soonMsg, 105, 50, { align: "center" });
        doc.save(`${receiptNumber}_receipt.pdf`);
    }

    function refreshProjectSection(projectId) {
        fetch(`api/get_project_section?project=${projectId}`)
            .then(response => response.text())
            .then(html => {
                const container = document.querySelector(`#${projectId}-section .projectContainer`);
                if (container) container.outerHTML = html;
            })
            .catch(err => console.error('Failed to refresh project data:', err));
    }

    function handleNext(e) {
        e.preventDefault(); e.stopPropagation();
        if (currentStep === 1) {
            if (!validateStep1()) return;
            if (!currencyInput.value) { alert(t('errCurrency', 'Please select a currency before proceeding.')); return; }
            showStep(2);
        } 
        else if (currentStep === 2) { showStep(3); } 
        else if (currentStep === 3) {
            if (!validateStep3() || isSubmitting) return;
            isSubmitting = true;
            if (nextBtn) nextBtn.disabled = true;
            submitDonation();
        } 
        else if (currentStep === 4) { closeModal(); }
    }

    function handlePrev(e) {
        e.preventDefault(); e.stopPropagation();
        if (currentStep > 1 && currentStep < 4) showStep(currentStep - 1);
    }

    function handleCurrencySwitch(e) {
        e.preventDefault(); e.stopPropagation();
        const btn = e.currentTarget;
        const currency = btn.dataset.currency;
        
        currencySwitchers.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (currencyInput) currencyInput.value = currency;

        if (currentStep === 1) {
            if (validateStep1()) showStep(2);
            else { btn.classList.remove('active'); if (currencyInput) currencyInput.value = ''; }
        } else if (currentStep === 2) {
            loadBankDetails();
        }
    }

    function submitDonation() {
        const confirmOverlay = document.getElementById('mbsg-confirm-overlay');
        const namePreview = document.getElementById('confirm-preview-name');
        const amountPreview = document.getElementById('confirm-preview-amount');

        const getVal = (name) => form.elements[name]?.value?.trim() || t('notProvided', 'Not provided');
        const fullName = getVal('full_name');
        const email = getVal('email');
        const phone = getVal('country_code') + ' ' + getVal('contact_number');
        const location = getVal('location');
        const currency = currencyInput?.value || '';
        const isAnon = form.elements['is_anonymous']?.checked ? t('yes', 'Yes') : t('no', 'No');
        const status = document.querySelector('input[name="payment_status"]:checked')?.value;

        namePreview.innerHTML = `
            <div style="text-align: left; font-size: 0.9rem; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                <p><strong>${t('lblDonor', 'Donor:')}</strong> ${fullName}</p><p><strong>${t('lblEmail', 'Email:')}</strong> ${email}</p>
                <p><strong>${t('lblPhone', 'Phone:')}</strong> ${phone}</p><p><strong>${t('lblLocation', 'Address:')}</strong> ${location}</p>
                <p><strong>${t('lblAnon', 'Anonymous:')}</strong> ${isAnon}</p>
            </div>
        `;

        if (status === 'success') {
            const amount = getVal('amount');
            const txId = getVal('transaction_id');
            amountPreview.innerHTML = `
                <div style="text-align: left; font-size: 0.9rem; color: #28a745;">
                    <p><strong>${t('lblStatus', 'Status:')}</strong> ${t('success', 'SUCCESSFUL').toUpperCase()}</p>
                    <p style="color: #333;"><strong>${t('lblAmount', 'Amount:')}</strong> ${currency} ${amount}</p>
                    <p style="color: #333;"><strong>${t('lblTxid', 'Transaction ID:')}</strong> <code>${txId}</code></p>
                </div>
            `;
        } else {
            const reason = getVal('failure_reason');
            const finalReason = (reason === 'Other') ? getVal('other_desc') : reason;
            amountPreview.innerHTML = `
                <div style="text-align: left; font-size: 0.9rem; color: #dc3545;">
                    <p><strong>${t('lblStatus', 'Status:')}</strong> ${t('failed', 'FAILED').toUpperCase()}</p><p style="color: #333;"><strong>${t('lblReason', 'Reason:')}</strong> ${finalReason}</p>
                </div>
            `;
        }

        confirmOverlay.style.display = 'flex';

        document.getElementById('confirm-no').onclick = () => {
            confirmOverlay.style.display = 'none';
            isSubmitting = false;
            if (nextBtn) nextBtn.disabled = false;
        };

        const confirmBtn = document.getElementById('confirm-yes');
        const originalText = confirmBtn.innerHTML;

        confirmBtn.onclick = () => {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = `<span class="mbsg-spinner"></span> ${t('processing', 'Processing...')}`;
            executeSubmission(confirmBtn, originalText);
        };
    }

    function executeSubmission(btn, originalText) {
        const formData = new FormData(form);

        fetch('api/donation_process', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('mbsg-confirm-overlay').style.display = 'none';
                displayFinalMessage(data);
                showStep(4);
                
                const projectId = form.elements['project_id']?.value;
                if (projectId) refreshProjectSection(projectId);
            } else {
                document.getElementById('mbsg-confirm-overlay').style.display = 'none';
                if (data.isDuplicate) showCustomAlert(t('errDup', 'This transaction ID has already been recorded.'));
                else showCustomAlert(data.message || t('errOccurred', 'An error occurred.'));
                resetSubmitState(btn, originalText);
            }
        })
        .catch(error => {
            document.getElementById('mbsg-confirm-overlay').style.display = 'none';
            showCustomAlert(t('errNet', 'Network error: Unable to connect to the server.'));
            resetSubmitState(btn, originalText);
        });
    }

    function resetSubmitState(btn, originalText) {
        if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
        isSubmitting = false;
        if (nextBtn) nextBtn.disabled = false;
    } 

    if (nextBtn) nextBtn.addEventListener('click', handleNext);
    if (prevBtn) prevBtn.addEventListener('click', handlePrev);

    if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
            e.preventDefault(); e.stopPropagation();
            if (currentStep > 1) {
                const exitOverlay = document.getElementById('mbsg-exit-confirm-overlay');
                exitOverlay.style.display = 'flex';
                document.getElementById('exit-confirm-yes').onclick = () => { exitOverlay.style.display = 'none'; closeModal(); };
                document.getElementById('exit-confirm-no').onclick = () => { exitOverlay.style.display = 'none'; };
            } else {
                closeModal();
            }
        });
    }

    currencySwitchers.forEach(btn => btn.addEventListener('click', handleCurrencySwitch));
    statusRadios.forEach(radio => radio.addEventListener('change', toggleStatusFields));

    const failureReasonSelect = form?.elements['failure_reason'];
    if (failureReasonSelect) failureReasonSelect.addEventListener('change', handleFailureReasonChange);

    if (printBtn) printBtn.addEventListener('click', printReceipt);
    if (downloadBtn) downloadBtn.addEventListener('click', downloadReceipt);

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-donate-window');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation();
        
        lastFocusedButton = btn; 
        const purpose = btn.dataset.purpose;
        const projectId = btn.dataset.projectId;
        const projectName = btn.dataset.projectName;
        
        const modal = document.getElementById('community-donation-overlay');
        if (modal) {
            modal.showModal();
            document.body.classList.add('mbsg-modal-locked');
            resetModal();
            
            if (purpose) {
                const purposeHidden = document.getElementById('contribution_purpose');
                const purposeValue = document.getElementById('purpose-value');
                if (purposeHidden) purposeHidden.value = purpose;
                if (purposeValue) purposeValue.textContent = purpose;
            }
            if (projectId) {
                const projectHidden = document.getElementById('project_id');
                const projectValue = document.getElementById('project-value');
                if (projectHidden) projectHidden.value = projectId;
                if (projectValue) projectValue.textContent = projectName ? `${projectName} (${projectId})` : projectId;
            }
            setTimeout(() => {
                const firstField = document.querySelector('#step-1 input');
                if (firstField) firstField.focus();
            }, 100);
        }
    });

    function showCustomAlert(message) {
        const existing = modal.querySelector('.mbsg-form-alert');
        if (existing) existing.remove();

        const alertBox = document.createElement('div');
        alertBox.className = 'mbsg-form-alert';
        alertBox.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

        const inner = modal.querySelector('.mbsg-modal-inner');
        inner.appendChild(alertBox);

        setTimeout(() => {
            alertBox.style.opacity = '0';
            alertBox.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    form.addEventListener('submit', (e) => e.preventDefault());
    toggleStatusFields();
})();