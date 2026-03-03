<?php
$title = 'Terms of Service';
ob_start();
?>
<div class="container py-5" style="max-width: 900px;">
    <h1 class="h3 mb-3">Terms of Service</h1>
    <p class="text-secondary">Use of this platform is governed by your institution's exam and acceptable-use policies.</p>
    <h2 class="h6 mt-4">User Responsibilities</h2>
    <ul class="text-secondary">
        <li>Keep credentials private and complete 2FA checks.</li>
        <li>Do not attempt to bypass proctoring or exam controls.</li>
        <li>Use the system only for authorized academic activities.</li>
    </ul>
    <h2 class="h6 mt-4">Enforcement</h2>
    <p class="text-secondary">Policy violations may result in account suspension and academic disciplinary action.</p>
    <a href="/login" class="btn btn-outline-secondary mt-3">Back to Login</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
