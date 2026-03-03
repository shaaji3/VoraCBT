<?php
$title = 'Forgot Password';
ob_start();
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
    <div class="card border-0 shadow-sm" style="max-width: 560px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-2">Forgot your password?</h1>
            <p class="text-secondary mb-4">Contact your school administrator to reset your account securely. Self-service reset is disabled for exam integrity.</p>
            <ul class="text-secondary small mb-4">
                <li>Include your full name and student/staff ID.</li>
                <li>Use your registered school email for faster verification.</li>
                <li>After reset, return to login and complete 2FA.</li>
            </ul>
            <div class="d-flex gap-2">
                <a href="/contact-support" class="btn btn-primary">Contact Support</a>
                <a href="/login" class="btn btn-outline-secondary">Back to Login</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
