<?php
$title = 'Verify Your Identity';
ob_start();
?>
<main class="auth-wrapper min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4">
    <div class="auth-card card border-0 shadow-sm w-100" style="max-width: 460px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width:56px;height:56px;">
                    <span class="material-symbols-outlined">shield_lock</span>
                </div>
                <h1 class="h4 fw-bold mb-2">Two-factor verification</h1>
                <p class="text-muted small mb-0">Enter the 6-digit code sent to your registered identity channel.</p>
            </div>

            <form id="twoFactorForm" class="d-flex flex-column gap-3" action="#" method="post">
                <label for="otp" class="form-label fw-medium small mb-1">Verification code</label>
                <input id="otp" name="otp" class="form-control form-control-lg text-center" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="••••••" required>

                <div id="twoFactorError" class="alert alert-danger d-none py-2 mb-0" role="alert"></div>
                <div id="twoFactorInfo" class="alert alert-info d-none py-2 mb-0" role="status"></div>

                <button type="submit" id="verifyBtn" class="btn btn-primary w-100">Verify and continue</button>
                <button type="button" id="resendBtn" class="btn btn-outline-secondary w-100">Resend code</button>
                <a href="/login" class="btn btn-link w-100">Back to login</a>
            </form>
        </div>
    </div>
</main>
<script type="module" src="/assets/js/pages/two-factor.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
