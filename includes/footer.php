    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>VaultRate</h5>
                    <p>Track currency exchange rates and get notified when they reach your desired levels.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>index.php" class="text-white">Home</a></li>
                        <?php if (!$auth->isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>pages/login.php" class="text-white">Login</a></li>
                            <li><a href="<?php echo BASE_URL; ?>pages/register.php" class="text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>API</h5>
                    <p>Powered by <a href="https://frankfurter.dev/" class="text-white" target="_blank">Frankfurter API</a></p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> VaultRate. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>