<?php
require_once __DIR__ . '/../config/config.php';
?>
<!-- Footer -->
<footer class="mt-auto py-4 bg-white border-top text-center text-muted fs-6">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div>
                &copy; <?php echo date('Y'); ?> <strong><?php echo APP_NAME; ?></strong>. Production Ready Placement Portal.
            </div>
            <div class="d-flex gap-3">
                <a href="#" class="text-muted text-decoration-none small">Privacy Policy</a>
                <a href="#" class="text-muted text-decoration-none small">Terms of Service</a>
                <a href="#" class="text-muted text-decoration-none small">Support</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main App Custom JS -->
<script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/dashboard.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/search.ajax.js"></script>

</body>
</html>
