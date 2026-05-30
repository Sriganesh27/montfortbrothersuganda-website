<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-title"><?= t('footer-contact') ?></div>
            <div class="content">
                <h1><?= t('navbar-school') ?></h1>
                <p>
                    <span><?= t('footer-address-line1') ?></span><br>
                    <span><?= t('footer-address-line2') ?></span><br>
                    <span><?= t('footer-address-line3') ?></span><br>
                    <span><?= t('footer-address-line4') ?></span><br>
                    <span><?= t('footer-email') ?></span><br>
                    <span><?= t('footer-phone') ?></span><br>
                </p>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-title"><?= t('footer-about') ?></div>
            <div class="links">
                <ul>
                    <li><a href="about.php#vision"><span><?= t('our-vision') ?></span></a></li>
                    <li><a href="about.php#heritage"><span><?= t('our-heritage') ?></span></a></li>
                    <li><a href="about.php#uganda_mission"><span><?= t('navbar-umission') ?></span></a></li>
                    <li><a href="about.php#aim"><span><?= t('navbar-aim') ?></span></a></li>
                    <li><a href="about.php#characters"><span><?= t('our-characteristics') ?></span></a></li>
                </ul>
            </div>
            <div class="footer-title"><?= t('footer-where') ?></div>
            <div class="links">
                <ul>
                    <li><a href="location.php#mpala-section" class="location-navigation"><span><?= t('entebbe-mpala') ?></span></a></li>
                    <li><a href="location.php#kyebando-section" class="location-navigation"><span><?= t('jinja-kyebando') ?></span></a></li>
                    <li><a href="location.php#isunga-section" class="location-navigation"><span><?= t('fort-isunga') ?></span></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-title"><?= t('footer-unite') ?></div>
            <div class="links">
                <ul>
                    <li><a href="unite.php#unite-prayer"><span><?= t('unite-prayer') ?></span></a></li>
                    <li><a href="unite.php#unite-volunteer"><span><?= t('unite-volunteer') ?></span></a></li>
                </ul>
            </div>
            <div class="footer-title"><?= t('footer-quicklinks') ?></div>
            <div class="links">
                <ul>
                    <li><a href="index.php#horizons"><span><?= t('horizons-title') ?></span></a></li>
                    <li><a href="index.php#galleryContainer"><span><?= t('navbar-highlights') ?></span></a></li>
                    <li><a href="index.php#"><span><?= t('privacy') ?></span></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-content">
            <div class="footer-title"><?= t('footer-support') ?></div>
            <div class="links">
                <ul>
                    <li><a href="support.php"><span><?= t('support-scholarships') ?></span></a></li>
                    <li><a href="support.php"><span><?= t('support-infrastructure') ?></span></a></li>
                    <li><a href="support.php"><span><?= t('support-community') ?></span></a></li>
                </ul>
            </div>
            
            <div class="footer-title"><?= t('navbar-login') ?></div>
            <div class="links">
                <ul>
                    <?php if (isset($_SESSION['web_user_id'])): ?>
                        <li><a href="user_dashboard.php"><span><?= t('navbar-dashboard') ?></span></a></li>
                        <li><a href="api/logout.php"><span><?= t('navbar-logout') ?></span></a></li>
                    <?php else: ?>
                        <li><a href="#" id="navLoginTrigger"><span><?= t('navbar-login') ?></span></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="footer-content">
            <div class="footer-title"><?= t('footer-stayconnected') ?></div>
            <div class="caption"><p><?= t('footer-email-intro') ?></p></div>
            <div class="contact-form">
                <form action="api/contact_process.php" method="POST">
                    <input type="text" name="website_url" style="display:none;" tabindex="-1" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    
                    <input type="text" name="name" placeholder="<?= t('form-name') ?>" required>
                    <input type="email" name="email" placeholder="<?= t('form-email') ?>" required>
                    <input type="text" name="number" placeholder="<?= t('form-phone') ?>">
                    <textarea name="message" placeholder="<?= t('form-message') ?>" required></textarea>
                    
                    <div class="form-actions">
                        <button type="reset" class="btn subtle"><?= t('form-reset') ?></button>
                        <button type="submit" class="btn primary"><?= t('form-send') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="copyrigts" style="padding: 8px 4px; opacity: 0.7; font-size: clamp(0.6rem,3vw,1rem); font-weight: 400;">
        <p><?= t('copyright') ?></p>
    </div>
</footer>

<dialog id="success-dialog" class="donate-popup animate-up">
    <div class="donate-window">
        <div class="donate-title">
            <h1 style="color: #4CAF50;"><?= t('success-dialog-title') ?></h1>
        </div>
        <div class="donate-form" style="text-align: center;">
            <p><?= t('success-dialog-message') ?></p>
        </div>
        <button type="button" class="primary-fir"><?= t('close-button') ?></button>
    </div>
</dialog>

<dialog id="error-dialog" class="donate-popup animate-up">
    <div class="donate-window">
        <div class="donate-title">
            <h1 style="color: #dc3545;"><?= t('error-dialog-title') ?></h1>
        </div>
        <div class="donate-form" style="text-align: center;">
            <p><?= t('error-dialog-message') ?></p>
        </div>
        <button type="button" class="primary-fir"><?= t('close-button') ?></button>
    </div>
</dialog>

<div id="lang-popup" class="lang-modal-overlay" style="display: none;">
    <div class="lang-modal-content">
        <h2><?= t('popup_title') ?></h2>
        <div class="lang-options">
            <button type="button" class="lang-select-btn" data-lang="en">English</button>
            <button type="button" class="lang-select-btn" data-lang="fr">Français</button>
            <button type="button" class="lang-select-btn" data-lang="es">Español</button>
            <button type="button" class="lang-select-btn" data-lang="it">Italiano</button>
            <button type="button" class="lang-select-btn" data-lang="de">Deutsch</button>
        </div>
    </div>
</div>

<dialog id="community-donation-overlay" class="mbsg-donation-pop" aria-labelledby="modal-step-title" aria-describedby="modal-step-subtitle">
    <div class="mbsg-modal-inner">
        <div class="mbsg-header-area">
            <h1 id="modal-step-title"><?= t('donation-title') ?></h1>
            <p id="modal-step-subtitle"><?= t('donation-step') ?> 1 of 4</p>
        </div>

        <form id="mbsg-donation-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="mbsg-step-content" id="step-1">
                <div class="mbsg-form-grid">
                    <input type="text" name="full_name" placeholder="<?= t('donation-full-name') ?>" required>
                    <input type="email" name="email" placeholder="<?= t('donation-email') ?>">

                    <div class="mbsg-phone-input-wrapper">
                        <select name="country_code">
                            <option value="+256" selected>UG +256 (Uganda)</option>
                            <option value="+254">KE +254 (Kenya)</option>
                            <option value="+44">GB +44 (United Kingdom)</option>
                            <option value="+1">US +1 (USA)</option>
                        </select>
                        <input type="tel" name="contact_number" placeholder="<?= t('donation-phone') ?>">
                    </div>

                    <input type="text" name="location" placeholder="<?= t('donation-location') ?>" required>
                    <input type="hidden" name="contribution_purpose" id="contribution_purpose" value="">
                    
                    <div class="mbsg-purpose-display" id="purpose-display">
                        <span class="purpose-label"><?= t('donation-purpose-label') ?></span>
                        <span class="purpose-value" id="purpose-value"><?= t('donation-purpose-not-selected') ?></span>
                    </div>

                    <input type="hidden" name="project_id" id="project_id" value="">

                    <div class="mbsg-project-display" id="project-display" style="margin-top:10px;">
                        <span class="project-label"><?= t('donation-project-label') ?></span>
                        <span class="project-value" id="project-value"><?= t('donation-project-not-selected') ?></span>
                    </div>

                    <div class="mbsg-currency-toggle-group">
                        <button type="button" class="mbsg-switcher-link" data-currency="USD"><span><?= t('donation-currency-usd') ?></span></button>
                        <button type="button" class="mbsg-switcher-link" data-currency="EURO_GBP"><span><?= t('donation-currency-euro-gbp') ?></span></button>
                        <button type="button" class="mbsg-switcher-link" data-currency="UGX"><span><?= t('donation-currency-ugx') ?></span></button>
                        <input type="hidden" name="currency" id="selected-currency" value="">
                    </div>
                </div>
            </div>

            <div class="mbsg-step-content" id="step-2" style="display:none;">
                <div class="mbsg-donor-mini-summary"></div>
                <div id="mbsg-bank-data-board"></div>
            </div>

            <div class="mbsg-step-content" id="step-3" style="display:none;">
                <div class="mbsg-donor-mini-summary"></div>
                <div class="mbsg-status-toggle">
                    <label><input type="radio" name="payment_status" value="success" checked> <span><?= t('donation-status-success') ?></span></label>
                    <label><input type="radio" name="payment_status" value="failed"> <span><?= t('donation-status-failed') ?></span></label>
                </div>
                
                <div id="mbsg-success-fields">
                    <input type="number" name="amount" placeholder="<?= t('donation-amount') ?>" step="0.01" min="0">
                    <input type="text" name="transaction_id" placeholder="<?= t('donation-transaction-id') ?>">
                    <div class="mbsg-anonymous-option">
                        <label class="anonymous-checkbox">
                            <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1">
                            <span class="checkmark"></span>
                            <span class="anonymous-text"><?= t('donation-anonymous') ?></span>
                        </label>
                        <small class="anonymous-note"><?= t('donation-anonymous-note') ?></small>
                    </div>
                </div>

                <div id="mbsg-failed-fields" style="display:none;">
                    <select name="failure_reason">
                        <option value="" disabled selected><?= t('donation-failure-select') ?></option>
                        <option value="Network Issue"><?= t('donation-failure-network') ?></option>
                        <option value="Insufficient Funds"><?= t('donation-failure-funds') ?></option>
                        <option value="Other"><?= t('donation-failure-other') ?></option>
                    </select>
                    <textarea name="other_desc" placeholder="<?= t('donation-failure-describe') ?>" style="display:none;"></textarea>
                </div>
            </div>

            <div class="mbsg-step-content" id="step-4" style="display:none;">
                <div class="mbsg-final-message" id="final-status-message"></div>
                <div id="receipt-print-content" style="display: none;">
                    <div class="print-receipt">
                        <div class="print-header">
                            <h2><?= t('receipt-header') ?></h2>
                            <h3><?= t('receipt-subheader') ?></h3>
                            <p><?= t('receipt-title') ?></p>
                        </div>
                        <div class="print-body" id="print-receipt-details"></div>
                        <div class="print-footer">
                            <p><?= t('receipt-thanks') ?></p>
                            <p><?= t('receipt-note') ?></p>
                        </div>
                    </div>
                </div>
                <div class="mbsg-receipt-actions">
                    <button type="button" id="print-receipt" class="print-btn">
                        <i class="fas fa-print"></i> <span><?= t('donation-print') ?></span>
                    </button>
                    <button type="button" id="download-receipt" class="download-btn">
                        <i class="fas fa-download"></i> <span><?= t('donation-download') ?></span>
                    </button>
                </div>
            </div>
            
            <div class="mbsg-action-bottom">
                <button type="button" id="back-to-edit" class="btn subtle" style="display:none;"><?= t('donation-previous') ?></button>
                <button type="button" id="submit-next-btn" class="primary-btn" style="display:none;"><?= t('donation-next') ?></button>
                <button type="button" id="modal-cancel-btn" class="mbsg-exit-btn"><?= t('donation-cancel') ?></button>
            </div>
        </form>
    </div>
    
    <div id="mbsg-confirm-overlay" class="mbsg-custom-confirm" style="display: none;">
        <div class="confirm-card">
            <div class="confirm-icon"><i class="fas fa-question-circle"></i></div>
            <h3><?= t('donation-confirm-title') ?></h3>
            <p><?= t('donation-confirm-message') ?></p>
            <div class="confirm-summary-box">
                <div id="confirm-preview-name"></div>
                <div id="confirm-preview-amount"></div>
            </div>
            <div class="confirm-actions">
                <button type="button" id="confirm-yes" class="confirm-primary"><?= t('donation-confirm-yes') ?></button>
                <button type="button" id="confirm-no" class="confirm-subtle"><?= t('donation-confirm-no') ?></button>
            </div>
        </div>
    </div>
    <div id="mbsg-exit-confirm-overlay" class="mbsg-custom-confirm" style="display: none;">
        <div class="confirm-card exit-warning">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h3><?= t('donation-exit-title') ?></h3>
            <p><?= t('donation-exit-message') ?></p>
            <div class="confirm-actions">
                <button type="button" id="exit-confirm-yes" class="confirm-btn-danger"><?= t('donation-exit-yes') ?></button>
                <button type="button" id="exit-confirm-no" class="confirm-subtle"><?= t('donation-exit-no') ?></button>
            </div>
        </div>
    </div>
</dialog>

<script>
window.jsTrans = {
    "step": "<?= t('don-js-step') ?>",
    "of": "<?= t('don-js-of') ?>",
    "title1": "<?= t('don-js-title1') ?>",
    "title2": "<?= t('don-js-title2') ?>",
    "title3": "<?= t('don-js-title3') ?>",
    "title4": "<?= t('don-js-title4') ?>",
    "btnClose": "<?= t('don-js-btn-close') ?>",
    "btnFinish": "<?= t('don-js-btn-finish') ?>",
    "btnNext": "<?= t('don-js-btn-next') ?>",
    "errCurrency": "<?= t('don-js-err-currency') ?>",
    "errDetails": "<?= t('don-js-err-details') ?>",
    "errPurpose": "<?= t('don-js-err-purpose') ?>",
    "errContact": "<?= t('don-js-err-contact') ?>",
    "errEmail": "<?= t('don-js-err-email') ?>",
    "errPhone": "<?= t('don-js-err-phone') ?>",
    "errAmount": "<?= t('don-js-err-amount') ?>",
    "errTxid": "<?= t('don-js-err-txid') ?>",
    "errReason": "<?= t('don-js-err-reason') ?>",
    "notProvided": "<?= t('don-js-not-provided') ?>",
    "notSelected": "<?= t('don-js-not-selected') ?>",
    "lblDonor": "<?= t('don-js-lbl-donor') ?>",
    "lblEmail": "<?= t('don-js-lbl-email') ?>",
    "lblPhone": "<?= t('don-js-lbl-phone') ?>",
    "lblLocation": "<?= t('don-js-lbl-location') ?>",
    "lblPurpose": "<?= t('don-js-lbl-purpose') ?>",
    "lblProject": "<?= t('don-js-lbl-project') ?>",
    "lblCurrency": "<?= t('don-js-lbl-currency') ?>",
    "lblAmount": "<?= t('don-js-lbl-amount') ?>",
    "lblTxid": "<?= t('don-js-lbl-txid') ?>",
    "lblStatus": "<?= t('don-js-lbl-status') ?>",
    "lblDate": "<?= t('don-js-lbl-date') ?>",
    "lblReason": "<?= t('don-js-lbl-reason') ?>",
    "lblAnon": "<?= t('don-js-lbl-anonymous') ?>",
    "lblReceiptNum": "<?= t('don-js-lbl-receiptnum') ?>",
    "bankMsg": "<?= t('don-js-bank-msg') ?>",
    "bankNote": "<?= t('don-js-bank-note') ?>",
    "bankSwift": "<?= t('don-js-bank-swift') ?>",
    "bankName": "<?= t('don-js-bank-name') ?>",
    "bankAccNo": "<?= t('don-js-bank-accno') ?>",
    "bankAccName": "<?= t('don-js-bank-accname') ?>",
    "yes": "<?= t('don-js-yes') ?>",
    "no": "<?= t('don-js-no') ?>",
    "success": "<?= t('don-js-success') ?>",
    "failed": "<?= t('don-js-failed') ?>",
    "msgSuccessTitle": "<?= t('don-js-msg-success-title') ?>",
    "msgSuccessP1": "<?= t('don-js-msg-success-p1') ?>",
    "msgSuccessP2": "<?= t('don-js-msg-success-p2') ?>",
    "msgFailTitle": "<?= t('don-js-msg-fail-title') ?>",
    "msgFailP1": "<?= t('don-js-msg-fail-p1') ?>",
    "msgFailP2": "<?= t('don-js-msg-fail-p2') ?>",
    "summaryTitle": "<?= t('don-js-summary-title') ?>",
    "errPrint": "<?= t('don-js-err-print') ?>",
    "errDownload": "<?= t('don-js-err-download') ?>",
    "errDup": "<?= t('don-js-err-duplicate') ?>",
    "errNet": "<?= t('don-js-err-network') ?>",
    "errOccurred": "<?= t('don-js-err-occurred') ?>",
    "processing": "<?= t('don-js-processing') ?>"
};
</script>

<script src="assets/script/script.js?v=2.1" defer></script>