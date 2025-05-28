<?php
ob_start(); // Start output buffering

require_once 'config.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'functions.php';

$auth = new Auth();
$db = new Database();

// Set default theme if not set
if (!isset($_COOKIE['theme'])) {
    setcookie('theme', 'light', time() + (86400 * 30), "/");
}
?>

<script>
  // Check for saved theme preference or use light mode as default
  document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Update toggle button state
    if (savedTheme === 'dark') {
      document.getElementById('themeToggle').checked = true;
    }
  });
  
  function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
  }
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaultRate - Currency Tracking System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    <!-- Add this to includes/header.php before closing </head> -->
    <style>
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    </style>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentUrl = window.location.pathname.split('/').pop() || 'index.php';
    
    // Find all nav links
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    // Loop through each link
    navLinks.forEach(link => {
        const linkUrl = link.getAttribute('href').split('/').pop();
        
        // Check if link URL matches current page URL
        if (currentUrl === linkUrl || 
            (currentUrl === '' && linkUrl === 'index.php') ||
            (currentUrl.includes('dashboard.php') && linkUrl.includes('dashboard.php'))) {
            link.classList.add('active');
            link.setAttribute('aria-current', 'page');
            
            // If it's in a dropdown, highlight the parent dropdown item too
            const dropdown = link.closest('.dropdown-menu');
            if (dropdown) {
                const dropdownToggle = dropdown.previousElementSibling;
                if (dropdownToggle) {
                    dropdownToggle.classList.add('active');
                }
            }
        }
    });
});
</script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">VaultRate</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
                    </li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>pages/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>pages/search.php">Search Currencies</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="dropdown-item d-flex justify-content-between align-items-center">
                                        <span>Dark Mode</span>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="themeToggle" onclick="toggleTheme()">
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4"></main>