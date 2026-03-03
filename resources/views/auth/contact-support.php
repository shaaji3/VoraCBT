<?php
$title = 'Contact Support';
ob_start();
?>
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
    <div class="card border-0 shadow-sm" style="max-width: 640px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-2">Support Contacts</h1>
            <p class="text-secondary mb-4">For onboarding, access, and account-recovery issues, use your institution-approved support channels.</p>
            <div class="list-group mb-4">
                <div class="list-group-item">
                    <strong>School IT Helpdesk</strong>
                    <div class="small text-secondary">helpdesk@school.example · +234 800 000 0000</div>
                </div>
                <div class="list-group-item">
                    <strong>CBT Operations Desk</strong>
                    <div class="small text-secondary">cbt-ops@school.example · Mon–Fri 8:00–18:00</div>
                </div>
            </div>
            <a href="/login" class="btn btn-outline-secondary">Back to Login</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
