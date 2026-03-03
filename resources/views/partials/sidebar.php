<aside class="offcanvas-md offcanvas-start border-end bg-surface h-100 overflow-y-auto flex-column" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel" style="width: 280px;">

    <div class="offcanvas-header d-md-none border-bottom">
        <h5 class="offcanvas-title fw-bold text-body" id="sidebarMenuLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>

    <div class="d-flex flex-column justify-content-between h-100 p-3">
        <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-center gap-3 px-2 mt-2">
                <div class="d-flex align-items-center justify-content-center rounded bg-primary text-white" style="width: 40px; height: 40px;">
                    <span class="material-symbols-outlined fs-4">school</span>
                </div>
                <div class="d-flex flex-column lh-1">
                    <h6 class="mb-0 fw-bold text-body">EduTest Pro</h6>
                    <small class="text-secondary" style="font-size: 0.75rem;">Enterprise Admin</small>
                </div>
            </div>

            <nav class="nav flex-column gap-1" data-ui-shell-nav>
                <a href="/admin/dashboard" data-route-prefix="/admin/dashboard" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">dashboard</span> Overview
                </a>
                <a href="/teacher/questions" data-route-prefix="/teacher/questions" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">quiz</span> Questions
                </a>
                <a href="/admin/exams/create" data-route-prefix="/admin/exams" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">assignment</span> Exams
                </a>
                <a href="/admin/analytics" data-route-prefix="/admin/analytics" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">monitoring</span> Analytics
                </a>
                <a href="/teacher/grading" data-route-prefix="/teacher/grading" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">grade</span> Grading
                </a>
                <a href="/admin/settings" data-route-prefix="/admin/settings" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                    <span class="material-symbols-outlined fs-5">settings</span> Settings
                </a>
            </nav>
        </div>

        <div class="d-flex flex-column gap-1 border-top pt-3">
            <a href="/admin/settings" class="nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-secondary hover-bg-light hover-text-dark transition-colors">
                <span class="material-symbols-outlined fs-5">help</span> Help & Support
            </a>
            <a href="/auth/logout" class="btn btn-link text-decoration-none nav-link d-flex align-items-center gap-3 px-3 py-2 rounded-3 text-danger hover-bg-danger-soft transition-colors w-100 text-start" data-ui-shell-logout>
                <span class="material-symbols-outlined fs-5">logout</span> Log Out
            </a>
        </div>
    </div>
</aside>
