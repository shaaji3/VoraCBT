<?php
$title = 'System Settings';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">System Settings</h1>
        <p class="text-secondary mb-0">Configure core platform and exam delivery defaults.</p>
    </div>
</div>

<div id="settings-status" class="alert alert-secondary" role="status">Loading settings…</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form id="settings-form" class="row g-3">
            <div class="col-md-6">
                <label for="platform_name" class="form-label">Platform name</label>
                <input type="text" id="platform_name" name="platform_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="support_email" class="form-label">Support email</label>
                <input type="email" id="support_email" name="support_email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="session_timeout_minutes" class="form-label">Session timeout (minutes)</label>
                <input type="number" min="15" id="session_timeout_minutes" name="session_timeout_minutes" class="form-control" required>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" id="allow_result_download" name="allow_result_download" class="form-check-input">
                    <label class="form-check-label" for="allow_result_download">Allow result download for students</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
</div>

<script type="module" src="/assets/js/pages/system-settings.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
