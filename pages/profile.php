<?php
// Include the header file that contains common page elements and initialization
require_once '../includes/header.php';

// Check if user is logged in, if not redirect to login page
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize database connection and get current user ID from session
$db = new Database();
$user_id = $_SESSION['user_id'];

// Initialize variables for error handling and success messages
$errors = [];
$success = '';

// Get current user data from authentication system
$user = $auth->getUser($user_id);

// Handle form submission when POST request is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all password inputs by trimming whitespace
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Only validate password change if new password was provided
    if (!empty($new_password)) {
        // Validate current password
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        } else {
            // Verify current password against database
            $db_user = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$user_id]);
            if (!password_verify($current_password, $db_user['password'])) {
                $errors['current_password'] = 'Current password is incorrect';
            }
        }

        // Comprehensive new password validation
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($new_password) < 8) {
            $errors['new_password'] = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/[A-Z]/', $new_password)) {
            $errors['new_password'] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[a-z]/', $new_password)) {
            $errors['new_password'] = 'Password must contain at least one lowercase letter';
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $errors['new_password'] = 'Password must contain at least one number';
        } elseif (!preg_match('/[\W]/', $new_password)) {
            $errors['new_password'] = 'Password must contain at least one special character';
        } elseif (strcmp($new_password, $current_password) === 0) {
            $errors['new_password'] = 'New password must be different from current password';
        }

        // Confirm password matches new password
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your new password';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // If no validation errors, update password in database
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashed_password, $user_id]);
            
            // Set success message and clear form data
            $success = 'Password updated successfully';
            $_POST = []; // Clear the form
        }
    } else {
        // No password change was requested
        $success = 'Profile information updated';
    }
    
    // Redirect on success to prevent form resubmission
    if (empty($errors)) {
        $_SESSION['profile_update_success'] = $success;
        header("Location: profile.php");
        exit();
    }
}

// Check for success message from redirect (after form submission)
if (isset($_SESSION['profile_update_success'])) {
    $success = $_SESSION['profile_update_success'];
    unset($_SESSION['profile_update_success']); // Clear the session message
}
?>

<!-- Profile Page Layout -->
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <!-- Main profile card -->
        <div class="card shadow-sm">
            <!-- Card header with title -->
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Profile</h5>
            </div>
            <div class="card-body">
                <!-- Display success message if present -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Account Information Section -->
                <div class="mb-4">
                    <h6 class="fw-bold">Account Information</h6>
                    <hr class="my-2">
                    <div class="row g-3">
                        <!-- Username display -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Username</small>
                                <span class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                        </div>
                        <!-- Email display -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Email</small>
                                <span class="fw-bold"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                        <!-- Member since date -->
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Member Since</small>
                                <span class="fw-bold"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password Change Form Section -->
                <div class="mb-3">
                    <h6 class="fw-bold">Change Password</h6>
                    <hr class="my-2">
                    <form method="POST" action="" novalidate>
                        <!-- Current password field -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                   id="current_password" name="current_password" required>
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['current_password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- New password field with requirements -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password">
                            <div class="form-text">Minimum 8 characters with uppercase, lowercase, number, and special character</div>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['new_password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Confirm new password field -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer file that contains common page elements
require_once '../includes/footer.php';
?>