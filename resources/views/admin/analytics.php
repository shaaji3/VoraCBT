<?php
$title = 'Exam Analytics';
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h4 mb-1">Exam Analytics</h1>
        <p class="text-secondary mb-0">Session health, status distribution, and performance summary.</p>
    </div>
    <button id="analytics-refresh" class="btn btn-outline-primary btn-sm">Refresh</button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-3">
            <label for="analytics-from" class="form-label">From</label>
            <input type="date" id="analytics-from" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="analytics-to" class="form-label">To</label>
            <input type="date" id="analytics-to" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="analytics-status" class="form-label">Status</label>
            <select id="analytics-status" class="form-select">
                <option value="">All</option>
                <option value="started">Started</option>
                <option value="in_progress">In Progress</option>
                <option value="submitted">Submitted</option>
                <option value="completed">Completed</option>
                <option value="graded">Graded</option>
            </select>
        </div>
        <div class="col-md-3">
            <button id="analytics-apply" class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </div>
</div>

<div id="analytics-status-banner" class="alert alert-secondary" role="status">Loading analytics…</div>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-secondary">Sessions</div><div id="analytics-total-sessions" class="h4 mb-0">0</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-secondary">Average Score</div><div id="analytics-average-score" class="h4 mb-0">-</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-secondary">Pass Rate</div><div id="analytics-pass-rate" class="h4 mb-0">-</div></div></div></div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
            <tbody id="analytics-table-body"><tr><td colspan="3" class="text-center text-secondary py-4">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<script type="module" src="/assets/js/pages/exam-analytics.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
