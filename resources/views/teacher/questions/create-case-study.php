<?php
$title = 'Create Case Study Question';
$questionType = 'case_study';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Create Question (case_study)</h1>
        <p class="text-secondary mb-0">Compose and save a new case study with nested questions.</p>
    </div>
</div>
<?php include __DIR__ . '/_question_form.php'; ?>
<script type="module" src="/assets/js/pages/question-create.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
