<header class="d-flex align-items-center justify-content-between px-4 py-3 bg-surface border-bottom flex-shrink-0" style="height: 80px;">
    <div class="d-flex flex-column">
        <h5 class="fw-bold text-body mb-0"><?= $pageTitle ?? 'Exam Portal' ?></h5>
        <small class="text-secondary"><?= $pageSubtitle ?? 'Manage your upcoming assessments' ?></small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="position-relative d-none d-md-block">
            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary fs-5">search</span>
            <input type="text" class="form-control bg-body-secondary border-0 ps-5" placeholder="Search exams..." style="width: 260px;" data-ui-shell-search>
        </div>

        <div class="dropdown" data-ui-shell-notifications data-ui-shell-notification-role="student">
            <button class="btn btn-light btn-sm position-relative text-secondary p-2" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <span class="material-symbols-outlined fs-4">notifications</span>
                <span class="position-absolute top-0 end-0 m-2 p-1 bg-danger border border-light rounded-circle" data-ui-notification-dot></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0 ui-shell-menu" style="width: 300px;">
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                    <strong class="small">Notifications <span class="badge text-bg-secondary ms-1" data-ui-notification-count>0</span></strong>
                    <button class="btn btn-link btn-sm text-decoration-none p-0" type="button" data-ui-mark-read>Mark all read</button>
                </div>
                <div class="list-group list-group-flush" data-ui-notification-list>
                    <div class="list-group-item py-2 small">New exam available in your class.</div>
                    <div class="list-group-item py-2 small">Result published for Basic Science quiz.</div>
                </div>
            </div>
        </div>

        <div class="dropdown" data-ui-shell-profile>
            <button class="btn btn-light btn-sm p-1 rounded-circle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Profile menu">
                <div class="avatar-circle border border-2 border-white shadow-sm" style="width: 36px; height: 36px; background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDgrtrOg8gXtIYU9ne3uwU2quQCn4QEf6RstLHpuukpPkcDmIMdjhEQPM0t1byaDt2h0O8RWlPp1yLH7f7f15SbkPgwkP_NtMa7YZjqoD-oVEfKT6leGn7deSJY0N0Mb2joEo1GXcySDsX97bEF4hujwtyOttzk0qcDPdAilWTU81S_P5uRGM6pntfqTfu44aMMig1coLEoh-Hy6axIWCd9XMPdSh82E7Lw6DOTIihNjSAQIqcXGc2ujgiusStF5eQnjQFB2dkNaLs');"></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="/student/dashboard">Dashboard</a></li>
                <li><a class="dropdown-item" href="/student/results">Results</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/auth/logout" data-ui-shell-logout>Logout</a></li>
            </ul>
        </div>
    </div>
</header>
