<?php
$title = 'Exam Results';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Exam Results</h1>
        <p class="text-secondary mb-0">Your submitted sessions and scores.</p>
    </div>
    <a href="/student/dashboard" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
</div>

<div id="results-status" class="alert alert-secondary" role="status">Loading results…</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr><th>Exam</th><th>Subject</th><th>Score</th><th>Percent</th><th>Completed</th></tr>
            </thead>
            <tbody id="results-table-body">
            <tr><td colspan="5" class="text-center text-secondary py-4">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script type="module" src="/assets/js/pages/student-results.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/student.php';
