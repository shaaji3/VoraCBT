<?php
$title = 'Question Repository';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Question Repository</h1>
        <p class="text-secondary mb-0">Search, review and archive questions.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/teacher/questions/create-mcq" class="btn btn-primary btn-sm">MCQ</a>
        <a href="/teacher/questions/create-true-false" class="btn btn-outline-primary btn-sm">True/False</a>
        <a href="/teacher/questions/create-fill-blank" class="btn btn-outline-primary btn-sm">Fill Blank</a>
        <a href="/teacher/questions/create-matching" class="btn btn-outline-primary btn-sm">Matching</a>
        <a href="/teacher/questions/create-drag-drop" class="btn btn-outline-primary btn-sm">Drag & Drop</a>
        <a href="/teacher/questions/create-case-study" class="btn btn-outline-primary btn-sm">Case Study</a>
        <a href="/teacher/questions/import" class="btn btn-outline-secondary btn-sm">Import CSV</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <input id="question-search" type="search" class="form-control mb-3" placeholder="Search prompt or type...">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th>ID</th><th>Type</th><th>Prompt</th><th>Version</th><th>Updated</th><th>Actions</th></tr></thead>
                <tbody id="question-table-body"><tr><td colspan="6" class="text-center text-secondary py-4">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script type="module" src="/assets/js/pages/question-manager.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
