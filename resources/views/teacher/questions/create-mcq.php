<?php
$title = 'Create MCQ Question';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Create Question (mcq)</h1>
        <p class="text-secondary mb-0">Compose and save a new mcq question.</p>
    </div>
</div>

<div id="question-create-status" class="alert alert-secondary" role="status">Ready to create question.</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form id="question-create-form" data-question-type="mcq" class="row g-3">
            <div class="col-12">
                <label class="form-label" for="question-prompt">Prompt</label>
                <textarea id="question-prompt" class="form-control" rows="4" required></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="question-difficulty">Difficulty</label>
                <select id="question-difficulty" class="form-select">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="question-tags">Tags (comma separated)</label>
                <input id="question-tags" class="form-control" placeholder="algebra, grade-10">
            </div>
            <div class="col-12">
                <label class="form-label" for="question-options">Options / expected values (JSON optional)</label>
                <textarea id="question-options" class="form-control" rows="4" placeholder='["Option A","Option B"]'></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save Question</button>
                <a href="/teacher/questions" class="btn btn-outline-secondary">Back to Repository</a>
            </div>
        </form>
    </div>
</div>

<script type="module" src="/assets/js/pages/question-create.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
