<?php
$title = 'Roles & Permissions';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Roles & Permissions</h1>
        <p class="text-secondary mb-0">Manage role access matrix and permission scopes.</p>
    </div>
    <button id="save-roles-btn" class="btn btn-primary btn-sm">Save changes</button>
</div>

<div id="roles-status" class="alert alert-secondary" role="status">Loading roles and permissions…</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="roles-permissions-table">
            <thead>
            <tr>
                <th>Permission</th>
                <th>Role assignment</th>
            </tr>
            </thead>
            <tbody id="roles-permissions-body">
            <tr><td colspan="2" class="text-center text-secondary py-4">Loading matrix…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script type="module" src="/assets/js/pages/roles-permissions.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
