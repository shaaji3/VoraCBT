<?php
$title = 'Question Repository';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Question Repository</h1>
        <p class="text-secondary mb-0">Browse and manage question bank items.</p>
    </div>
    <a href="/teacher/questions/create-mcq" class="btn btn-primary btn-sm">Create Question</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <input id="question-search" type="search" class="form-control mb-3" placeholder="Search questions...">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead><tr><th>ID</th><th>Type</th><th>Prompt</th><th>Updated</th></tr></thead>
                <tbody id="question-table-body"><tr><td colspan="4" class="text-center text-secondary py-4">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script type="module" src="/assets/js/pages/question-manager.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
