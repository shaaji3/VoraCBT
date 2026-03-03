<?php
$title = 'Bulk Upload';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Bulk Upload</h1>
        <p class="text-secondary mb-0">Upload student/staff CSV files for preview and commit.</p>
    </div>
</div>

<div id="import-status" class="alert alert-secondary" role="status">Ready to upload.</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form id="import-form" class="row g-3">
            <div class="col-md-4">
                <label for="import-type" class="form-label">Import type</label>
                <select id="import-type" class="form-select">
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="col-md-8">
                <label for="import-file" class="form-label">CSV file</label>
                <input type="file" id="import-file" class="form-control" accept=".csv" required>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="button" id="preview-import-btn" class="btn btn-outline-primary">Preview</button>
                <button type="button" id="commit-import-btn" class="btn btn-primary">Commit</button>
            </div>
        </form>
    </div>
</div>

<script type="module" src="/assets/js/pages/bulk-upload.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
