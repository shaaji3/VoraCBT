<?php
$title = 'Bulk Question Import';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Bulk Question Import</h1>
        <p class="text-secondary mb-0">Upload CSV/Excel to preview and import questions (including learning objective metadata).</p>
    </div>
</div>

<div id="question-import-status" class="alert alert-secondary" role="status">Ready to upload.</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form id="question-import-form" class="row g-3">
            <div class="col-md-8">
                <label for="question-import-file" class="form-label">CSV or Excel file (.csv, .xlsx)</label>
                <input type="file" id="question-import-file" class="form-control" accept=".csv,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="button" id="question-import-preview" class="btn btn-outline-primary w-100">Preview</button>
                <button type="button" id="question-import-commit" class="btn btn-primary w-100">Commit</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h2 class="h6">Expected columns</h2>
        <code>type,prompt,options,correct_options,difficulty,tags,learning_objective</code>
        <p class="text-secondary small mb-0 mt-2">Use JSON in <code>options</code> and <code>correct_options</code> where required by the type.</p>
    </div>
</div>

<script type="module" src="/assets/js/pages/question-import.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
