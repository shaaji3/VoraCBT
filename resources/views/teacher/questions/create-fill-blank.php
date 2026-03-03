<?php
$title = 'Create Fill In The Blank Question';
$questionType = 'fill_in_the_blank';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4"><div><h1 class="h4 mb-1">Create Question (fill_in_the_blank)</h1></div></div>
<?php include __DIR__ . '/_question_form.php'; ?>
<script type="module" src="/assets/js/pages/question-create.js"></script>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/app.php';
