<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="bg-body text-body min-vh-100 d-flex flex-column theme-login font-display">
    <?= $content ?? '' ?>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/pages/ui-shell.js"></script>
    <?php if (isset($scripts)) echo $scripts; ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
        <div id="ui-shell-toast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Action completed.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</body>
</html>
