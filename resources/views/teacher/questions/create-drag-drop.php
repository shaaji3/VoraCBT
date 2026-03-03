<?php
$title = 'Create Drag & Drop Question';
$questionType = 'drag_drop';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Create Question (drag_drop)</h1>
        <p class="text-secondary mb-0">Compose and save a new drag & drop mapping question.</p>
    </div>
</div>
<?php include __DIR__ . '/_question_form.php'; ?>
<script type="module" src="/assets/js/pages/question-create.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
