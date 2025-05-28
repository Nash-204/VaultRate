<?php
// Include the header file which likely contains session start, database connection, and other initializations
require_once '../includes/header.php';

// Check if user is already logged in, redirect to dashboard if true
if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables for form handling
$errors = [];       // Array to store validation errors
$username = '';     // Variable to store username input
$email = '';        // Variable to store email input
$success = '';      // Variable to store success message

// Check if form was submitted via POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim form inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (strlen($username) > 30) {
        $errors['username'] = 'Username must be less than 30 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters';
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
        $errors['password'] = 'Password must contain uppercase, lowercase, number, and special character';
    }

    // Validate password confirmation
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    // Only attempt registration if no validation errors
    if (empty($errors)) {
        error_log("Attempting to register user: $username");
        // Call the authentication system's register method
        $result = $auth->register($username, $email, $password);
        
        if ($result === true) {
            error_log("Registration successful for: $username");
            $success = 'Registration successful! You can now login.';
            // Clear form on success
            $username = '';
            $email = '';
        } else {
            error_log("Registration failed. Result: " . print_r($result, true));
            // Handle specific registration failures
            if ($result === 'username') {
                $errors['username'] = 'Username already exists';
            } elseif ($result === 'email') {
                $errors['email'] = 'Email already exists';
            } else {
                $errors['form'] = 'Registration failed. Please try again.';
            }
        }
    } else {
        error_log("Form validation errors: " . print_r($errors, true));
    }
}
?>

<!-- HTML form begins here -->
<div class="row justify-content-center mt-2">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="card-title">Create Your Account</h2>
                    <p class="text-muted">Start tracking currency rates today</p>
                </div>
                
                <!-- Display form-wide errors if any -->
                <?php if (!empty($errors['form'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($errors['form']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Display success message if registration was successful -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-success">Continue to Login</a>
                    </div>
                <?php else: ?>
                    <!-- Registration form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" novalidate>
                        <!-- Username field -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                                required autocomplete="username" autofocus>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['username']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">3-30 characters, letters, numbers and underscores only</div>
                        </div>
                        
                        <!-- Email field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                                required autocomplete="email">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['email']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Password field with toggle visibility -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                    id="password" name="password" required autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Minimum 8 characters with uppercase, lowercase, number, and special character</div>
                        </div>
                        
                        <!-- Confirm Password field with toggle visibility -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                    id="confirm_password" name="confirm_password" required 
                                    autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <!-- Link to login page for existing users -->
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="login.php" class="fw-bold">Sign in</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for form enhancements -->
<script>
// Toggle password visibility for password field
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Toggle password visibility for confirm password field
document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const confirmInput = document.getElementById('confirm_password');
    const icon = this.querySelector('i');
    
    if (confirmInput.type === 'password') {
        confirmInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        confirmInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Client-side password validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    
    // Real-time password matching validation
    confirmInput.addEventListener('input', function() {
        if (passwordInput.value !== confirmInput.value) {
            confirmInput.classList.add('is-invalid');
            // Ensure we don't add multiple error messages
            if (!document.getElementById('confirm-password-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'confirm-password-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Passwords do not match';
                confirmInput.parentNode.appendChild(errorDiv);
            }
        } else {
            confirmInput.classList.remove('is-invalid');
            const errorDiv = document.getElementById('confirm-password-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    });
    
    // Password complexity validation on form submission
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/;
        
        if (!regex.test(password)) {
            e.preventDefault();
            passwordInput.classList.add('is-invalid');
            if (!document.getElementById('password-pattern-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'password-pattern-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Password must contain uppercase, lowercase, number, and special character';
                passwordInput.parentNode.appendChild(errorDiv);
            }
        }
    });
});
</script>

<?php
// Include the footer file which likely contains closing tags and scripts
require_once '../includes/footer.php';
?>