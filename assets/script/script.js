/** * GLOBAL UTILITIES & FUNCTIONS */
async function apiCall(url, formData, buttonElement = null) {
    let originalText = '';
    if (buttonElement) {
        originalText = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    try {
        const response = await fetch(url, { 
            method: 'POST', 
            body: formData, 
            headers: {'Accept': 'application/json'} 
        });

        const result = await response.json().catch(() => null);

        if (!response.ok) {
            return { success: false, message: result?.message || `Server Error: ${response.status} ${response.statusText}` };
        }

        return result; 
    } catch (error) {
        console.error('API Connection Error:', error);
        return { success: false, message: 'Could not connect to server. Please check your internet.' };
    } finally {
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalText;
        }
    }
}

function updateAllBars(data) {
    const totalReceived = data.total_ugx || 0;
    const spentAmount = data.amount_spent || 0;

    // 1. Scope to the CURRENTLY ACTIVE project section only!
    const activeProject = document.querySelector('.projectSection.active');
    if (!activeProject) return;

    // 2. Find the wrappers inside THIS specific project
    const receivedWrapper = activeProject.querySelector('.received-fill')?.closest('.progress-wrapper');
    const spentWrapper = activeProject.querySelector('.spent-fill')?.closest('.progress-wrapper');

    // 3. Update the "Received" Bar dynamically
    if (receivedWrapper) {
        // Read the exact target for this specific project from the HTML
        const target = parseFloat(receivedWrapper.getAttribute('data-target')) || 0;
        
        if (target > 0) {
            const recPercent = Math.min(100, Math.round((totalReceived / target) * 100));
            const receivedBar = receivedWrapper.querySelector('.received-fill');
            
            receivedBar.style.width = recPercent + '%';
            const textEl = receivedBar.querySelector('.progress-text');
            if (textEl) textEl.innerText = recPercent + '%';
        }
    }
    
    // 4. Update the "Spent" Bar dynamically
    if (spentWrapper) {
        // Read the exact allocation rate (e.g., 0.90) for this project from the HTML
        const allocationRate = parseFloat(spentWrapper.getAttribute('data-allocation')) || 1.0;
        const helpedFund = totalReceived * allocationRate;
        
        const spentPercent = helpedFund > 0 ? Math.min(100, Math.round((spentAmount / helpedFund) * 100)) : 0;
        const spentBar = spentWrapper.querySelector('.spent-fill');
        
        spentBar.style.width = spentPercent + '%';
        const textEl = spentBar.querySelector('.progress-text');
        if (textEl) textEl.innerText = spentPercent + '%';
        
        // Safely update the numbers without overwriting your PHP language translations
        const spentTarget = spentWrapper.querySelector('.js-spent-amount');
        const helpedTarget = spentWrapper.querySelector('.js-helped-amount');
        
        if (spentTarget) spentTarget.innerHTML = `${spentAmount.toLocaleString()} UGX`;
        if (helpedTarget) helpedTarget.innerHTML = `${helpedFund.toLocaleString()} UGX`;
    }
}

window.authFlow = 'signup'; 

window.openAuthDialog = function(stepId = 'loginSection') {
    const dialog = document.getElementById('authDialog');
    if (!dialog) return;
    
    document.querySelectorAll('.auth-step').forEach(div => div.style.display = 'none');
    
    const targetStep = document.getElementById(stepId);
    if (targetStep) targetStep.style.display = 'block';

    if (stepId === 'passwordSection') {
        const header = document.getElementById('passHeader');
        if (header) header.textContent = window.authFlow === 'reset' ? 'Reset Password' : 'Set Password';
    }
    
    if (!dialog.open) dialog.showModal();
};

window.showAuthStep = function(stepId) {
    if (window.otpTimerInterval) {
        clearInterval(window.otpTimerInterval);
        window.otpTimerInterval = null;
    }
    document.querySelectorAll('.auth-step').forEach(div => { div.style.display = 'none'; });
    const targetStep = document.getElementById(stepId);
    if (targetStep) targetStep.style.display = 'block';

    if (stepId === 'passwordSection') {
        const header = document.getElementById('passHeader');
        const flowInput = document.getElementById('authFlowInput');
        if (header && flowInput) {
            const flow = window.authFlow || 'signup'; 
            header.textContent = (flow === 'reset') ? 'Reset Password' : 'Set Password';
            flowInput.value = flow;
        }
    }
};

window.startOtpTimer = function(seconds = 60) {
    if (window.otpTimerInterval) clearInterval(window.otpTimerInterval);
    let cooldown = seconds;
    const timerSpan = document.getElementById('otpTimer');
    const resendLink = document.getElementById('resendOtpLink');

    if (resendLink) { resendLink.classList.add('disabled'); resendLink.style.pointerEvents = 'none'; }

    window.otpTimerInterval = setInterval(() => {
        cooldown--;
        if (timerSpan) timerSpan.textContent = `Resend in ${cooldown}s`;
        if (cooldown <= 0) {
            clearInterval(window.otpTimerInterval);
            window.otpTimerInterval = null;
            if (timerSpan) timerSpan.textContent = '';
            if (resendLink) { resendLink.classList.remove('disabled'); resendLink.style.pointerEvents = 'auto'; }
        }
    }, 1000);
};

/**
 * MASTER DOMContentLoaded LISTENER
 */
document.addEventListener("DOMContentLoaded", function () {
    
    // 0. INJECT TIME-BASED BOT TRAP INTO ALL FORMS
    const loadTime = Math.floor(Date.now() / 1000);
    document.querySelectorAll('form').forEach(form => {
        if (!form.querySelector('input[name="form_load_time"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'form_load_time';
            input.value = loadTime;
            form.appendChild(input);
        }
    });

    /** 1. NAVIGATION & MOBILE MENU */
    const toggleBtn = document.querySelector('.toggle_btn');
    const toggleBtnIcon = document.querySelector('.toggle_btn i');
    const dropDownMenu = document.querySelector('.navlinks');

    if (toggleBtn) {
        toggleBtn.onclick = function () {
            dropDownMenu.classList.toggle('active');
            const isOpen = dropDownMenu.classList.contains('active');
            toggleBtnIcon.className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        };
    }

    document.onclick = function (e) {
        if (dropDownMenu && !dropDownMenu.contains(e.target) && !toggleBtn.contains(e.target)) {
            if (dropDownMenu.classList.contains('active')) {
                dropDownMenu.classList.remove('active');
                if (toggleBtnIcon) toggleBtnIcon.className = 'fa-solid fa-bars';
            }
        }
    };

    /** 2. IMAGE SLIDERS */
    const slideContainers = new Set();
    document.querySelectorAll('.img-slide').forEach(img => slideContainers.add(img.parentElement));

    slideContainers.forEach(container => {
        const slides = container.querySelectorAll('.img-slide');
        if (slides.length <= 1) return;

        let currentIndex = Array.from(slides).findIndex(s => s.classList.contains('active'));
        if (currentIndex === -1) { currentIndex = 0; slides[0].classList.add('active'); }

        setInterval(() => {
            slides[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % slides.length;
            slides[currentIndex].classList.add('active');

            const yearBadge = container.querySelector('.year-overlap');
            if (yearBadge) {
                yearBadge.style.opacity = '0';
                setTimeout(() => {
                    const newYear = slides[currentIndex].getAttribute('data-year');
                    if (newYear) yearBadge.innerText = newYear;
                    yearBadge.style.opacity = '1';
                }, 500);
            }
        }, 3000);
    });

    /** 3. SCROLL ANIMATIONS */
    const scrollObserverOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                if (entry.target.classList.contains('um_grid')) entry.target.classList.add('reveal');
            } else {
                entry.target.classList.remove('active');
                if (entry.target.classList.contains('um_grid')) entry.target.classList.remove('reveal');
            }
        });
    }, scrollObserverOptions);

    document.querySelectorAll('.scroll-animate, .reveal-slide-right, .reveal-slide-left, .animate-up, .um_grid')
        .forEach(el => scrollObserver.observe(el));

    /** 4. INFINITE GALLERY */
    function setupInfiniteGallery() {
        const track = document.getElementById('galleryTrack');
        const originalStrip = document.querySelector('.gallery-strip');
        const prevBtn = document.getElementById('gallery-prev');
        const nextBtn = document.getElementById('gallery-next');

        if (!track || !originalStrip) return;

        const clone = originalStrip.cloneNode(true);
        track.appendChild(clone);

        let currentScroll = 0;
        const speed = 0.9;
        let isHovered = false;
        let stripWidth = 0;

        function updateMetrics() { stripWidth = originalStrip.getBoundingClientRect().width; }

        function animate() {
            if (!isHovered && stripWidth > 0) {
                currentScroll -= speed;
                if (Math.abs(currentScroll) >= stripWidth) currentScroll = 0;
                track.style.transform = `translateX(${currentScroll}px)`;
            }
            requestAnimationFrame(animate);
        }

        track.addEventListener('mouseenter', () => isHovered = true);
        track.addEventListener('mouseleave', () => isHovered = false);

        const moveAmount = 324;
        nextBtn?.addEventListener('click', () => {
            currentScroll -= moveAmount;
            if (Math.abs(currentScroll) >= stripWidth) currentScroll += stripWidth;
            track.style.transform = `translateX(${currentScroll}px)`;
        });

        prevBtn?.addEventListener('click', () => {
            currentScroll += moveAmount;
            if (currentScroll > 0) currentScroll -= stripWidth;
            track.style.transform = `translateX(${currentScroll}px)`;
        });

        window.addEventListener('load', () => { updateMetrics(); animate(); });
        window.addEventListener('resize', updateMetrics);
    }
    setupInfiniteGallery();

    /** 5. COUNTER ANIMATION */
    const counters = document.querySelectorAll('.province_content .number_count');

    const countObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                
                // Read the target from the HTML attribute, NOT the innerText
                const target = +counter.getAttribute('data-target');
                let count = 0;
                
                const updateCount = () => {
                    const inc = target / 100; // Adjust this divisor to change animation speed
                    
                    if (count < target) {
                        count += inc;
                        counter.innerText = Math.ceil(count);
                        // Browsers throttle 1ms to 4ms anyway, 10ms is much safer and smoother
                        setTimeout(updateCount, 10); 
                    } else {
                        counter.innerText = target; // Ensure it ends exactly on the target
                    }
                };
                
                updateCount();
                
                // Stop observing this specific counter so it doesn't run again if you scroll up/down
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => countObserver.observe(c));

    /** 6. HORIZON SLIDER */
    const horizonSection = document.querySelector('.horizons');
    if (horizonSection) {
        const bgSlides = horizonSection.querySelectorAll('.horizons-overlap .slide');
        const contentSlides = horizonSection.querySelectorAll('.slider-track .slide');
        const dotsContainer = document.getElementById('horizon-dots');
        const prevBtn = document.getElementById('horizon-prev');
        const nextBtn = document.getElementById('horizon-next');
        let currentHorizonIndex = 0;
        let slideInterval;

        function updateSlides() {
            const dots = dotsContainer.querySelectorAll('.dot');
            contentSlides.forEach((s, i) => {
                s.classList.toggle('active', i === currentHorizonIndex);
                if (bgSlides[i]) bgSlides[i].classList.toggle('active', i === currentHorizonIndex);
                if (dots[i]) dots[i].classList.toggle('active', i === currentHorizonIndex);
            });
        }

        if (contentSlides.length > 0) {
            dotsContainer.innerHTML = '';
            contentSlides.forEach((_, index) => {
                const dot = document.createElement('div');
                dot.classList.add('dot');
                if (index === 0) dot.classList.add('active');
                dot.addEventListener('click', () => { currentHorizonIndex = index; updateSlides(); resetTimer(); });
                dotsContainer.appendChild(dot);
            });

            function nextSlide() { currentHorizonIndex = (currentHorizonIndex + 1) % contentSlides.length; updateSlides(); }
            function startTimer() { slideInterval = setInterval(nextSlide, 5000); }
            function resetTimer() { clearInterval(slideInterval); startTimer(); }
            
            nextBtn?.addEventListener('click', () => { nextSlide(); resetTimer(); });
            prevBtn?.addEventListener('click', () => { currentHorizonIndex = (currentHorizonIndex - 1 + contentSlides.length) % contentSlides.length; updateSlides(); resetTimer(); });
            startTimer();
        }
    }

    /** 7. LOCATION & YEAR BUTTON SWITCHING */
    const locationSections = document.querySelectorAll('.location-containers');
    document.querySelectorAll('.year-btn').forEach(button => {
        button.addEventListener('click', function() {
            const parentSection = this.closest('.year-wise');
            const targetContentId = this.getAttribute('data-target');

            parentSection.querySelectorAll('.year-btn').forEach(btn => btn.classList.remove('active'));
            parentSection.querySelectorAll('.year-content').forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });

            this.classList.add('active');
            const targetContent = document.getElementById(targetContentId);
            if (targetContent) {
                targetContent.classList.add('active');
                targetContent.style.display = 'block'; 
            }

            const yearLabel = parentSection.querySelector('.year-label');
            if (yearLabel) yearLabel.innerText = this.innerText; 
        });
    });

    function highlightSidebar(targetId) {
        document.querySelectorAll('.location-navigation, .location-navbar a').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(`#${targetId}`)) link.classList.add('active');
        });
    }

    function showSection(targetId) {
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            locationSections.forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });

            targetSection.classList.add('active');
            targetSection.style.display = 'flex'; 
            highlightSidebar(targetId);

            const firstYearBtn = targetSection.querySelector('.year-btn');
            if (firstYearBtn && !targetSection.querySelector('.year-content.active')) firstYearBtn.click();
            if (window.location.hash) targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function handleLocationRouting() {
        if (locationSections.length > 0) {
            const initialHash = window.location.hash.substring(1);
            if (initialHash && document.getElementById(initialHash)) showSection(initialHash);
            else showSection(locationSections[0].id); 
        }
    }

    handleLocationRouting();
    window.addEventListener('hashchange', handleLocationRouting);

    /** 8. PRAYER COUNTER & CUSTOM DIALOG LOGIC */
    const prayerDialog = document.getElementById('prayerDialog');
    let prayBtn = document.getElementById('prayBtn');
    const openPrayerFormBtn = document.getElementById('openPrayerFormBtn');
    const prayerForm = document.getElementById('prayerUserForm');
    const countDisplay = document.querySelectorAll('.count');
    const thankYouMsg = document.getElementById('thankYouMsg');
    const backToFormBtn = document.getElementById('backToFormBtn');

    function showPrayerStatus(type, title, message, showBackBtn = false) {
        const statusDialog = document.getElementById('prayerStatusDialog');
        if (!statusDialog) return;
        document.getElementById('prayerStatusTitle').innerText = title;
        document.getElementById('prayerStatusMessage').innerText = message;
        const icon = document.getElementById('prayerStatusIcon');
        const titleEl = document.getElementById('prayerStatusTitle');

        if (type === 'success') {
            icon.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i>';
            titleEl.style.color = '#28a745';
        } else {
            icon.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>';
            titleEl.style.color = '#dc3545';
        }

        if (backToFormBtn) backToFormBtn.style.display = showBackBtn ? 'block' : 'none';
        statusDialog.showModal();
        document.body.classList.add('mbsg-modal-locked');
    }

    const checkDailyGlobalLock = () => { return localStorage.getItem('mbsg_global_pray_date') === new Date().toDateString(); };

    const showAlreadyPrayedUI = () => {
        if (prayBtn) prayBtn.style.display = 'none';
        if (thankYouMsg) { thankYouMsg.style.display = 'block'; thankYouMsg.style.opacity = '1'; }
    };

    fetch('api/update_prayer.php').then(res => res.json()).then(data => {
        if (countDisplay.length > 0 && data.total !== undefined) {
            document.querySelectorAll('.count').forEach(el => el.innerText = data.total.toLocaleString());
        }
        if (checkDailyGlobalLock() || document.cookie.includes('mbsg_global_prayed_today')) showAlreadyPrayedUI();
    }).catch(e => console.log('Not on prayer page'));

    if (prayBtn) {
        if (prayBtn.hasAttribute('onclick')) prayBtn.removeAttribute('onclick');
        const newPrayBtn = prayBtn.cloneNode(true);
        prayBtn.parentNode.replaceChild(newPrayBtn, prayBtn);
        prayBtn = newPrayBtn;

        prayBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            localStorage.setItem('mbsg_global_pray_date', new Date().toDateString());
            showAlreadyPrayedUI();

            const formData = new FormData(); formData.append('action', 'global_pray');
            try {
                const result = await apiCall('api/update_prayer.php', formData);
                if (result && result.data && result.data.total !== undefined) {
                    document.querySelectorAll('.count').forEach(el => el.innerText = result.data.total.toLocaleString());
                }
            } catch (err) { console.error("Prayer Click Error:", err); }
        });
    }

    if (openPrayerFormBtn) {
        if (openPrayerFormBtn.hasAttribute('onclick')) openPrayerFormBtn.removeAttribute('onclick');
        openPrayerFormBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (prayerDialog) {
                prayerDialog.showModal();
                document.body.classList.add('mbsg-modal-locked');
            }
        });
    }

    if (backToFormBtn) {
        if (backToFormBtn.hasAttribute('onclick')) backToFormBtn.removeAttribute('onclick');
        backToFormBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const statusDialog = document.getElementById('prayerStatusDialog');
            if (statusDialog) statusDialog.close();
            
            setTimeout(() => {
                if (prayerDialog) {
                    prayerDialog.showModal();
                    document.body.classList.add('mbsg-modal-locked');
                }
            }, 100);
        });
    }

    if (prayerForm) {
        prayerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('prayerSubmitBtn');
            const safeTargetUrl = 'api/update_prayer.php'; 
            
            const result = await apiCall(safeTargetUrl, new FormData(this), submitBtn);
            if (prayerDialog) prayerDialog.close();
            
            if (result && result.success) {
                showPrayerStatus('success', "United in Prayer", result.message, false);
            } else {
                showPrayerStatus('error', "Notice", result?.message || 'Submission failed.', true);
            }
        });
    }

    /** 9. AJAX FORM PROCESSING (Contact & Volunteer) */
    document.querySelectorAll('.contact-form form, .volunteer-form form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); 
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            
            let targetUrl = this.getAttribute('action');
            if (!targetUrl || targetUrl === '#' || targetUrl.includes('javascript')) {
                if (this.closest('.contact-form')) targetUrl = 'api/contact_process.php';
                else if (this.closest('.volunteer-form')) targetUrl = 'api/volunteer_form.php';
            }

            const successDialog = document.getElementById('success-dialog');
            const errorDialog = document.getElementById('error-dialog');
            const result = await apiCall(targetUrl, new FormData(this), submitBtn);

            if (result && result.success) {
                this.reset();
                if (successDialog) {
                    successDialog.showModal();
                    document.body.classList.add('mbsg-modal-locked');
                }
            } else {
                if (errorDialog) {
                    const errorMsgEl = errorDialog.querySelector('p') || errorDialog.querySelector('.donate-form p');
                    if (errorMsgEl) errorMsgEl.textContent = result?.message || 'Something went wrong.';
                    errorDialog.showModal();
                    document.body.classList.add('mbsg-modal-locked');
                }
            }
        });
    });

    /** 10. AUTHENTICATION SYSTEM CONTROLS & LOGIN TRIGGER */
    const notification = document.getElementById('authNotification');
    function showNotification(message, type = 'info') {
        if(!notification) return;
        notification.className = 'auth-notification ' + type;
        notification.textContent = message;
        notification.style.display = 'block';
        setTimeout(() => { notification.style.display = 'none'; }, 5000);
    }

    document.getElementById('loginBtn')?.addEventListener('click', async function(e) {
        e.preventDefault();
        const form = document.getElementById('loginForm');
        const data = await apiCall('api/auth_process.php?action=login', new FormData(form), this);
        if (data.success) {
            if (data.role === 'Admin') window.location.href = 'admin/admin_dashboard.php';
            else location.reload();
        } else { showNotification(data.message || 'Invalid credentials.', 'error'); }
    });

    const navLoginTrigger = document.getElementById('navLoginTrigger');
    if (navLoginTrigger) {
        navLoginTrigger.addEventListener('click', function(e) {
            e.preventDefault(); 
            if (typeof window.openAuthDialog === 'function') {
                window.openAuthDialog('loginSection');
                document.body.classList.add('mbsg-modal-locked'); 
            }
        });
    }

    /** --- PASSWORD VISIBILITY TOGGLE --- */
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput) {
                // Toggle the input type
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash'); // Change to closed eye
                } else {
                    passwordInput.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye'); // Change back to open eye
                }
            }
        });
    });

    /** 11. MASTER DIALOG & POPUP CONTROLLER */
    document.querySelectorAll('dialog').forEach(dialog => {
        dialog.addEventListener('close', () => { document.body.classList.remove('mbsg-modal-locked'); });
        dialog.addEventListener('click', function(e) { if (e.target === dialog) dialog.close(); });
    });

    const allCloseButtons = document.querySelectorAll('button.primary-fir, .close-auth, .mbsg-exit-btn, #closePrayerStatusBtn, #closePrayerStatusBtnCross, #closePrayer, #modal-cancel-btn');
    allCloseButtons.forEach(btn => {
        if (btn.hasAttribute('onclick')) btn.removeAttribute('onclick');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const closestDialog = this.closest('dialog');
            if (closestDialog) closestDialog.close();

            const closestOverlay = this.closest('.lang-modal-overlay, .mbsg-custom-confirm, .loginpage, .mbsg-donation-pop');
            if (closestOverlay && closestOverlay.tagName !== 'DIALOG') {
                closestOverlay.style.display = 'none';
                document.body.classList.remove('mbsg-modal-locked');
            }
        });
    });

    document.querySelectorAll('dialog form, .lang-modal-overlay form, .loginpage form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const isHandledByAjax = this.classList.contains('ajax-form') || this.closest('.contact-form') || this.closest('.volunteer-form') || this.id === 'prayerUserForm' || this.id === 'loginForm';
            if (!isHandledByAjax) e.preventDefault();
        });
    });

}); // <-- END OF MAIN DOMContentLoaded

/** * ==========================================
 * 12. LANGUAGE SELECTION LOGIC (CSP SAFE)
 * ==========================================
 */
document.addEventListener("DOMContentLoaded", function() {
    const langPopup = document.getElementById('lang-popup');
    const langSelector = document.getElementById('navbar-lang-selector');

    function setLanguageViaURL(lang) {
        if (langPopup) langPopup.style.display = 'none';
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('lang', lang);
        window.location.href = currentUrl.toString();
    }

    const hasLanguageCookie = document.cookie.includes('site_lang=');
    const hasSessionPrompt = sessionStorage.getItem('languagePrompted');

    if (langPopup && !hasLanguageCookie && !hasSessionPrompt) {
        langPopup.style.display = 'flex';
        sessionStorage.setItem('languagePrompted', 'true');
    }

    const langBtns = document.querySelectorAll('.lang-select-btn');
    langBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            setLanguageViaURL(this.getAttribute('data-lang'));
        });
    });

    if (langSelector) {
        const currentLang = document.documentElement.lang || 'en';
        langSelector.value = currentLang;

        langSelector.addEventListener('change', (e) => {
            setLanguageViaURL(e.target.value);
        });
    }
});