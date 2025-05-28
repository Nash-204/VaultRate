<?php
// Include the header file that contains common page elements and initialization
require_once '../includes/header.php';

// Redirect to dashboard if user is already logged in
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables for form handling
$errors = [];       // Array to store validation errors
$username = '';     // Variable to store username input (for sticky form)

// Handle form submission when POST request is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim input values
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) > 50) {
        $errors['username'] = 'Username must be less than 50 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) > 255) {
        $errors['password'] = 'Password must be less than 255 characters';
    }
    
    // Only attempt login if there are no validation errors
    if (empty($errors)) {
        // Initialize database connection
        $db = new Database();
        // Check if username exists in database
        $user_exists = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        
        if ($user_exists) {
            // Username exists - attempt login with provided credentials
            if ($auth->login($username, $password)) {
                // Login successful - regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Password is incorrect
                $errors['form'] = 'Invalid password';
                $errors['password'] = 'Invalid password';
            }
        } else {
            // Username doesn't exist in database
            $errors['form'] = 'Invalid username';
            $errors['username'] = 'Invalid username';
            
            // If password was also provided (though username was wrong)
            if (!empty($password)) {
                $errors['form'] = 'Invalid username and password';
            }
        }
    }
}
?>

<!-- Login Form UI -->
<div class="row justify-content-center mt-2">
    <div class="col-md-6 col-lg-5">
        <!-- Card container for login form -->
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <!-- Form header -->
                <div class="text-center mb-4">
                    <h2 class="card-title">Sign in to your account</h2>
                    <p class="text-muted">Track and manage your currency rates</p>
                </div>
                
                <!-- Display form-level errors if any -->
                <?php if (!empty($errors['form'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($errors['form']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Login form -->
                <form method="POST" action="" novalidate>
                    <!-- Username field -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required
                               autocomplete="username" autofocus>
                        <!-- Display username-specific errors -->
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password field with toggle visibility -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" required autocomplete="current-password">
                            <!-- Password visibility toggle button -->
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Display password-specific errors -->
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Remember me checkbox -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <!-- Submit button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign in
                        </button>
                    </div>
                    
                    <!-- Forgot password link -->
                    <div class="text-center">
                        <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                    </div>
                </form>
                
                <!-- Separator -->
                <hr class="my-4">
                
                <!-- Registration prompt -->
                <div class="text-center">
                    <p class="mb-0">Don't have an account? <a href="register.php" class="fw-bold">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to toggle password visibility -->
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    // Toggle between password and text type
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

<!-- Include the footer file that contains common page elements -->
<?php
require_once '../includes/footer.php';
?>