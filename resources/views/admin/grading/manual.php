<?php
$title = 'Manual Grading';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Manual Grading</h1>
        <p class="text-secondary mb-0">Review pending answers and submit scores.</p>
    </div>
</div>

<div id="grading-status" class="alert alert-secondary" role="status">Loading pending grading queue…</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Session</th><th>Student</th><th>Exam</th><th>Submitted</th></tr></thead>
            <tbody id="grading-sessions-body"><tr><td colspan="4" class="text-center text-secondary py-4">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h6">Score selected answer</h2>
        <form id="grading-form" class="row g-3">
            <div class="col-md-4"><label class="form-label" for="answer-id">Answer ID</label><input id="answer-id" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label" for="score">Score</label><input id="score" type="number" min="0" step="0.01" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label" for="feedback">Feedback</label><input id="feedback" class="form-control"></div>
            <div class="col-md-4"><label class="form-label" for="moderation-status">Moderation Status</label><select id="moderation-status" class="form-select"><option value="reviewed">Reviewed</option><option value="needs_review">Needs Review</option><option value="escalated">Escalated</option></select></div>
            <div class="col-md-8"><label class="form-label" for="moderation-note">Moderation Note</label><input id="moderation-note" class="form-control"></div>
            <input id="expected-updated-at" type="hidden">
            <div class="col-12"><button class="btn btn-primary" type="submit">Save score</button></div>
        </form>
    </div>
</div>

<script type="module" src="/assets/js/pages/manual-grading.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
