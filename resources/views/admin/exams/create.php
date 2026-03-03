<?php
$title = "Exam Template Configuration";
ob_start();
?>
    <!-- Navbar / Header area -->
    <header class="sticky-top bg-surface bg-opacity-75 backdrop-blur-md border-bottom z-3">
        <div class="container-xxl d-flex align-items-center justify-content-between h-16" style="height: 64px;">
            <!-- Breadcrumbs -->
            <nav class="d-flex align-items-center gap-2 small text-secondary fw-medium">
                <a href="/admin/dashboard" class="text-decoration-none text-secondary hover-text-primary">Home</a>
                <span class="text-secondary">/</span>
                <a href="/admin/dashboard" class="text-decoration-none text-secondary hover-text-primary">Exams</a>
                <span class="text-secondary">/</span>
                <span class="text-body">Create Template</span>
            </nav>
            <!-- User / Profile -->
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-soft text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <span class="material-symbols-outlined fs-5">notifications</span>
                </div>
                <div class="avatar-circle bg-secondary" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAbfW6tl2Ji3wyL548Vh9Z-hRwhmpPDaZUMe6jPBuPI0O2Bq0boOxMR_lils_JTucTWokasZDiuzjR82dkaSjTsf_HwzLirHgnKxDS6QegEvgW8J6C1D22PRyIg7AIT5v8sSSWHjwj_aqwswkq8zCavz_i8V0jnocFcWJVwcTp1nPyNeYV9qQLaZrrzaMm36D0hoG5vF7wA3Wx1Z4DPfzWd_VJk_DmryTfKB9PbCU6cVSZBgnK_gT5arQJ6jYdU9NXNneAGDSth-24');"></div>
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main class="flex-grow-1 py-4 py-lg-5">
        <div class="container-xxl px-4">

            <div id="exam-template-status" class="alert alert-secondary" role="status">Edit template fields and save draft without leaving page.</div>

            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-4 mb-5">
                <div>
                    <h1 class="h2 fw-bold text-body mb-2 tracking-tight">Create Exam Template</h1>
                    <p class="text-secondary mb-0">Configure settings, set behaviors, and curate questions for the new exam.</p>
                </div>
                <div class="d-flex gap-3">
                    <button id="exam-template-cancel" class="btn btn-white border shadow-sm btn-sm fw-bold text-secondary hover-bg-body" type="button">Cancel</button>
                    <button id="exam-template-save" class="btn btn-primary btn-sm fw-bold shadow-sm d-flex align-items-center gap-2 px-3 hover-scale" type="button">
                        <span class="material-symbols-outlined fs-5">save</span> Save Template
                    </button>
                </div>
            </div>

            <div class="row g-5">

                <!-- Left Column: Settings -->
                <div class="col-lg-8 d-flex flex-column gap-4">

                    <!-- Card 1: Basic Details -->
                    <div class="card border rounded-3 shadow-sm">
                        <div class="card-header bg-surface border-bottom p-4">
                            <h6 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary">edit_document</span>
                                Basic Details
                            </h6>
                        </div>
                        <div class="card-body p-4 d-flex flex-column gap-4">
                            <!-- Title -->
                            <div>
                                <label for="exam-title" class="form-label small fw-bold text-secondary">Exam Title</label>
                                <input type="text" class="form-control bg-body-secondary" id="exam-title" placeholder="e.g., Mid-Term Physics 2024">
                            </div>
                            <div class="row g-4">
                                <!-- Subject -->
                                <div class="col-md-6">
                                    <label for="subject" class="form-label small fw-bold text-secondary">Subject</label>
                                    <select class="form-select bg-body" id="subject">
                                        <option selected disabled>Select Subject</option>
                                        <option>Mathematics</option>
                                        <option>Physics</option>
                                        <option>Chemistry</option>
                                        <option>English Literature</option>
                                    </select>
                                </div>
                                <!-- Classes -->
                                <div class="col-md-6">
                                    <label for="classes" class="form-label small fw-bold text-secondary">Assigned Classes</label>
                                    <select class="form-select bg-body" id="classes">
                                        <option selected disabled>Select Classes</option>
                                        <option>Class 10-A</option>
                                        <option>Class 10-B</option>
                                        <option>Class 11-A</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Duration -->
                            <div>
                                <label for="duration" class="form-label small fw-bold text-secondary">Duration (Minutes)</label>
                                <div class="position-relative">
                                    <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary fs-5">timer</span>
                                    <input type="number" class="form-control bg-body-secondary ps-5" id="duration" placeholder="e.g., 60">
                                </div>
                                <div class="form-text text-secondary x-small mt-1">Total time allocated for students to complete the exam.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Exam Behavior -->
                    <div class="card border rounded-3 shadow-sm overflow-hidden">
                        <div class="card-header bg-surface border-bottom p-4">
                            <h6 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary">tune</span>
                                Exam Behavior
                            </h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <!-- Toggle 1 -->
                            <div class="list-group-item p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block fw-bold text-body small">Randomize Questions</span>
                                    <span class="d-block text-secondary x-small">Questions will appear in a different order for each student.</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="randomizeQuestions" checked style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <!-- Toggle 2 -->
                            <div class="list-group-item p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block fw-bold text-body small">Shuffle Answer Options</span>
                                    <span class="d-block text-secondary x-small">Multiple choice options will be shuffled automatically.</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="shuffleAnswers">
                                </div>
                            </div>
                            <!-- Toggle 3 -->
                            <div class="list-group-item p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block fw-bold text-body small">Show Results Immediately</span>
                                    <span class="d-block text-secondary x-small">Students can view their score right after submission.</span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="showResults">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Question Repository -->
                <div class="col-lg-4">
                    <div class="sticky-top d-flex flex-column gap-4" style="top: 100px;">

                        <div class="card border rounded-3 shadow-sm overflow-hidden d-flex flex-column" style="max-height: calc(100vh - 120px);">
                            <div class="card-header bg-surface border-bottom p-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-body mb-0">Questions</h6>
                                <span class="badge bg-primary-soft text-primary rounded-pill">4 Added</span>
                            </div>
                            <div id="exam-template-question-list" class="card-body p-3 overflow-auto custom-scrollbar d-flex flex-column gap-2">
                                <!-- Question Item 1 -->
                                <div class="card border bg-body p-3 hover-border-primary transition-colors group" data-question-id="seed-1" data-marks="5">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <p class="mb-0 small fw-medium text-body text-truncate-2">Calculate the velocity of a falling object after 3 seconds...</p>
                                        <button class="btn btn-link p-0 text-secondary hover-text-danger lh-1" type="button" data-action="remove-question-card"><span class="material-symbols-outlined fs-6">delete</span></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary bg-opacity-25 text-body fw-medium" style="font-size: 0.65rem;">Physics</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium" style="font-size: 0.65rem;">Multiple Choice</span>
                                        <span class="ms-auto x-small fw-bold text-secondary">5 pts</span>
                                    </div>
                                </div>
                                <!-- Question Item 2 -->
                                <div class="card border bg-body p-3 hover-border-primary transition-colors group" data-question-id="seed-2" data-marks="10">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <p class="mb-0 small fw-medium text-body text-truncate-2">Define Newton's First Law of Motion in your own words.</p>
                                        <button class="btn btn-link p-0 text-secondary hover-text-danger lh-1" type="button" data-action="remove-question-card"><span class="material-symbols-outlined fs-6">delete</span></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary bg-opacity-25 text-body fw-medium" style="font-size: 0.65rem;">Physics</span>
                                        <span class="badge bg-purple-subtle text-purple border border-purple-subtle fw-medium" style="font-size: 0.65rem; color: #7e22ce; background-color: #f3e8ff; border-color: #e9d5ff;">Short Answer</span>
                                        <span class="ms-auto x-small fw-bold text-secondary">10 pts</span>
                                    </div>
                                </div>
                                <!-- Question Item 3 -->
                                <div class="card border bg-body p-3 hover-border-primary transition-colors group" data-question-id="seed-3" data-marks="2">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <p class="mb-0 small fw-medium text-body text-truncate-2">Which of the following is a scalar quantity?</p>
                                        <button class="btn btn-link p-0 text-secondary hover-text-danger lh-1" type="button" data-action="remove-question-card"><span class="material-symbols-outlined fs-6">delete</span></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary bg-opacity-25 text-body fw-medium" style="font-size: 0.65rem;">Physics</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium" style="font-size: 0.65rem;">Multiple Choice</span>
                                        <span class="ms-auto x-small fw-bold text-secondary">2 pts</span>
                                    </div>
                                </div>
                                <!-- Question Item 4 -->
                                <div class="card border bg-body p-3 hover-border-primary transition-colors group" data-question-id="seed-4" data-marks="15">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <p class="mb-0 small fw-medium text-body text-truncate-2">Describe the relationship between force, mass, and acceleration.</p>
                                        <button class="btn btn-link p-0 text-secondary hover-text-danger lh-1" type="button" data-action="remove-question-card"><span class="material-symbols-outlined fs-6">delete</span></button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary bg-opacity-25 text-body fw-medium" style="font-size: 0.65rem;">Physics</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-medium" style="font-size: 0.65rem;">Essay</span>
                                        <span class="ms-auto x-small fw-bold text-secondary">15 pts</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-body border-top p-4">
                                <button id="exam-template-add-question" class="btn btn-outline-primary border-dashed w-100 fw-bold d-flex align-items-center justify-content-center gap-2 py-2 mb-3 bg-primary-soft hover-bg-primary-soft-darker" type="button">
                                    <span class="material-symbols-outlined fs-5">add_circle</span> Add Questions from Repository
                                </button>
                                <div class="d-flex justify-content-between align-items-center text-sm">
                                    <span class="text-secondary small">Total Points:</span>
                                    <span id="exam-template-total-points" class="fw-bold text-body">32 pts</span>
                                </div>
                            </div>
                        </div>

                        <!-- Summary/Hint Card -->
                        <div class="card border-0 shadow-sm text-white overflow-hidden" style="background: linear-gradient(135deg, var(--bs-primary), #1d4ed8);">
                            <div class="card-body p-4 d-flex align-items-start gap-3">
                                <div class="rounded-circle bg-surface bg-opacity-25 p-2 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <span class="material-symbols-outlined fs-5">lightbulb</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Pro Tip</h6>
                                    <p class="small opacity-75 mb-0 lh-sm">Mixing Multiple Choice with Short Answer questions improves assessment quality by 25%.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>



<div class="modal fade" id="question-repo-modal" tabindex="-1" aria-labelledby="question-repo-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="question-repo-modal-title" class="modal-title">Question Repository</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="question-repo-modal-body" class="modal-body">
                <p class="text-secondary mb-0">Loading repository questions…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="question-repo-apply" type="button" class="btn btn-primary">Add Selected Questions</button>
            </div>
        </div>
    </div>
</div>

<?php
$scripts = '<script type="module" src="/assets/js/pages/exam-template.js"></script>';
$content = ob_get_clean();
include __DIR__ . '/../../layouts/focus.php';
?>
