<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="font-display bg-body text-body overflow-hidden">
    <div class="d-flex vh-100 overflow-hidden bg-body">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        <main class="d-flex flex-column flex-grow-1 overflow-hidden position-relative">
            <?php include __DIR__ . '/../partials/topbar.php'; ?>
            <div class="flex-grow-1 overflow-auto p-4 p-lg-5 scroll-smooth">
                <div class="container-xl px-0 d-flex flex-column gap-5">
                    <?= $content ?? '' ?>
                    <?php include __DIR__ . '/../partials/footer.php'; ?>
                </div>
            </div>
        </main>
    </div>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
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
