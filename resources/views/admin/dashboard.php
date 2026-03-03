<?php
$title = 'Proctor Dashboard';
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Proctor Dashboard</h1>
            <p class="text-secondary mb-0">Real-time monitoring of active exam sessions.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="small text-secondary">Auto-refresh: manual</span>
            <button id="btn-refresh-sessions" class="btn btn-outline-secondary btn-sm" type="button">Refresh now</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-2">
            <input id="dashboard-filter-user" class="form-control form-control-sm w-auto" placeholder="Search student/user" aria-label="Search student">
            <select id="dashboard-filter-exam" class="form-select form-select-sm w-auto" aria-label="Filter exams"><option value="">All Exams</option></select>
            <select id="dashboard-filter-source" class="form-select form-select-sm w-auto" aria-label="Filter sources"><option value="">All Sources</option></select>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student/User</th>
                        <th>Source</th>
                        <th>Exam</th>
                        <th>Event Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="session-table-body">
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">Loading recent events…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="dashboard-status" class="alert alert-light border mt-3 mb-0" role="status">
        Drill-down view includes timeline, event history, device fingerprint, and supervisor notes.
    </div>
</div>

<div class="modal fade" id="session-log-modal" tabindex="-1" aria-labelledby="session-log-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="session-log-modal-title" class="modal-title">Session Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="session-log-modal-body" class="modal-body">
                <p class="text-secondary mb-0">Select a row to inspect timeline details.</p>
            </div>
        </div>
    </div>
</div>
<?php
$scripts = '<script type="module" src="/assets/js/pages/admin-dashboard.js"></script>';
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
