<dialog id="authDialog" class="loginpage">
  <div class="login-container">
    <button class="close-auth" id="closeAuthBtn">&times;</button>
    <div id="authNotification" class="auth-notification"></div>

    <div id="registerSection" class="auth-step" style="display:none;">
      <h3><?= t('auth-reg-title') ?></h3>
      <form id="registrationForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <div class="form-row">
          <input type="text" name="first_name" placeholder="<?= t('auth-reg-fname') ?>" required>
          <input type="text" name="last_name" placeholder="<?= t('auth-reg-lname') ?>" required>
        </div>
        <input type="email" name="email" placeholder="<?= t('auth-reg-email') ?>" required>
        <input type="tel" name="phone" placeholder="<?= t('auth-reg-phone') ?>">
        <select name="gender">
          <option value=""><?= t('auth-reg-gender-sel') ?></option>
          <option value="Male"><?= t('auth-reg-male') ?></option>
          <option value="Female"><?= t('auth-reg-female') ?></option>
        </select>
        <input type="date" name="dob">
        <button type="button" id="requestOtpBtn" class="primary-btn"><?= t('auth-reg-btn') ?></button>
      </form>
      <p class="toggle-text"><?= t('auth-reg-already') ?> <a href="#" class="auth-step-link" data-step="loginSection"><?= t('auth-reg-login-link') ?></a></p>
    </div>

    <div id="otpSection" class="auth-step" style="display:none;">
      <h3><?= t('auth-otp-title') ?></h3>
      <form id="otpForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="text" name="otp_code" placeholder="<?= t('auth-otp-desc') ?>" maxlength="6" required>
        <button type="button" id="verifyOtpBtn" class="primary-btn"><?= t('auth-otp-btn') ?></button>
        <p class="toggle-text"><a href="#" id="resendOtpLink">Resend OTP</a> <span id="otpTimer"></span></p>
      </form>
    </div>
    
    <div id="passwordSection" class="auth-step" style="display:none;">
      <h3 id="passHeader"><?= t('auth-newpass-title') ?></h3>
      <form id="finalPasswordForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="hidden" name="auth_flow" id="authFlowInput" value="">
        
        <div class="password-wrapper">
          <input type="password" name="new_password" id="newPassword" placeholder="<?= t('auth-newpass-input1') ?>" minlength="8" required>
          <i class="fas fa-eye toggle-password" data-target="newPassword"></i>
        </div>

        <div class="password-wrapper">
          <input type="password" name="confirm_password" id="confirmPassword" placeholder="<?= t('auth-newpass-input2') ?>" minlength="8" required>
          <i class="fas fa-eye toggle-password" data-target="confirmPassword"></i>
        </div>

        <button type="button" id="completeAuthBtn" class="primary-btn"><?= t('auth-newpass-btn') ?></button>
      </form>
    </div>

    <div id="loginSection" class="auth-step">
      <h3><?= t('auth-login-title') ?></h3>
      <form id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="text" name="username" placeholder="<?= t('auth-login-email') ?>" required>
        
        <div class="password-wrapper">
          <input type="password" name="password" id="loginPassword" placeholder="<?= t('auth-login-pass') ?>" required>
          <i class="fas fa-eye toggle-password" data-target="loginPassword"></i>
        </div>
        
        <button type="button" id="loginBtn" class="primary-btn"><?= t('auth-login-btn') ?></button>
      </form>
      
      <?php /* <p class="toggle-text"><a href="#" class="auth-step-link" data-step="forgotSection"><?= t('auth-login-forgot') ?></a></p>
      <p class="toggle-text"><?= t('auth-login-new') ?> <a href="#" class="auth-step-link" data-step="registerSection"><?= t('auth-login-reg-link') ?></a></p>
      */ ?>
    </div>

    <div id="forgotSection" class="auth-step" style="display:none;">
      <h3><?= t('auth-forgot-title') ?></h3>
      <p><?= t('auth-forgot-desc') ?></p>
      <form id="forgotForm">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="email" name="email" placeholder="<?= t('auth-forgot-email') ?>" required>
        <button type="button" id="requestResetBtn" class="primary-btn"><?= t('auth-forgot-btn') ?></button>
      </form>
      <p class="toggle-text"><a href="#" class="auth-step-link" data-step="loginSection"><?= t('auth-forgot-back') ?></a></p>
    </div>
  </div>
</dialog>