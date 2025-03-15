        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'FiveM Server Dashboard'); ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end justify-content-center gap-3">
                        <a href="https://fivem.net/" target="_blank" class="text-decoration-none">
                            <span class="badge bg-primary">FiveM</span>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <span class="badge bg-secondary">Terms</span>
                        </a>
                        <a href="#" class="text-decoration-none">
                            <span class="badge bg-secondary">Privacy</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo str_replace('/includes', '', dirname($_SERVER['PHP_SELF'])); ?>/assets/js/main.js"></script>
    <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
    <script src="<?php echo str_replace('/includes', '', dirname($_SERVER['PHP_SELF'])); ?>/assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html> 