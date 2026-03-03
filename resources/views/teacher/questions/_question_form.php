<?php
$questionType = $questionType ?? strtolower((string) preg_replace('/[^a-z_]/i', '_', $title ?? 'mcq'));
?>
<div id="question-create-status" class="alert alert-secondary" role="status">Ready to create question.</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form id="question-create-form" data-question-type="<?= htmlspecialchars($questionType, ENT_QUOTES) ?>" class="row g-3">
            <div class="col-12">
                <label class="form-label" for="question-prompt">Prompt</label>
                <textarea id="question-prompt" class="form-control" rows="4" required></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="question-difficulty">Difficulty</label>
                <select id="question-difficulty" class="form-select">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="question-tags">Tags (comma separated)</label>
                <input id="question-tags" class="form-control" placeholder="algebra, grade-10">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="question-learning-objective">Learning objective</label>
                <input id="question-learning-objective" class="form-control" placeholder="LO-ALG-01">
            </div>
            <div class="col-12">
                <label class="form-label" for="question-options">Type-specific payload (JSON or lines)</label>
                <textarea id="question-options" class="form-control" rows="6" placeholder='Examples:\nmcq/true_false: [{"id":"a","text":"Option"}]\ndrag_drop: {"items":[...],"targets":[...],"correct_mapping":{...}}\ncase_study: {"case_text":"...","questions":[...]}\nfill_in_the_blank: {"text":"... [[1]]","blanks":{"1":{"correct":["x"]}}}'></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save Question</button>
                <a href="/teacher/questions" class="btn btn-outline-secondary">Back to Repository</a>
            </div>
        </form>
    </div>
</div>
