<?php
$title = 'Privacy Policy';
ob_start();
?>
<div class="container py-5" style="max-width: 900px;">
    <h1 class="h3 mb-3">Privacy Policy</h1>
    <p class="text-secondary">This CBT platform processes exam and profile data to deliver secure assessments, proctoring, grading, and audit reporting.</p>
    <h2 class="h6 mt-4">Data We Process</h2>
    <ul class="text-secondary">
        <li>Identity and enrollment records.</li>
        <li>Exam responses, session telemetry, and grading outcomes.</li>
        <li>Security and audit logs required for integrity reviews.</li>
    </ul>
    <h2 class="h6 mt-4">Retention</h2>
    <p class="text-secondary">Data is retained according to school policy and compliance requirements for academic records and dispute resolution.</p>
    <a href="/login" class="btn btn-outline-secondary mt-3">Back to Login</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
