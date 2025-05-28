<?php
require_once 'includes/header.php';

// Only redirect to dashboard if coming from root URL without index.php
$request_uri = $_SERVER['REQUEST_URI'];
$is_root_request = ($request_uri === '/vaultrate/' || $request_uri === '/vaultrate');

if (isset($_SESSION['user_id']) && $is_root_request) {
    header("Location: pages/dashboard.php");
    exit();
}
?>

<!-- Hero Section -->
<section class="hero-section slide-up">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Track Currency Exchange Rates with Ease</h1>
                <p class="lead mb-4">VaultRate helps you monitor exchange rates and notifies you when they reach your desired levels, so you never miss an opportunity.</p>
                <div class="d-flex gap-3">
                    <a href="pages/register.php" class="btn btn-light btn-lg px-4">Get Started - It's Free</a>
                    <a href="pages/login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
                    alt="Currency Exchange" 
                    class="img-fluid rounded-3 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="container mb-5 fade-in">
    <div class="text-center mb-5 pop-in">
        <h2 class="fw-bold mt-5">Powerful Features</h2>
        <p class="text-muted">Everything you need to track currency rates effectively</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4 pop-in" style="animation-delay: 0.5s;">
            <div class="card feature-card h-100">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4 class="card-title">Real-time Alerts</h4>
                    <p class="card-text">Get instant notifications when your target exchange rates are reached via email.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 pop-in" style="animation-delay: 1.0s;">
            <div class="card feature-card h-100">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="card-title">Comprehensive Tracking</h4>
                    <p class="card-text">Monitor multiple currency pairs with historical data and trends visualization.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 pop-in" style="animation-delay: 1.5s;">
            <div class="card feature-card h-100">
                <div class="card-body text-center p-4">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="card-title">Secure & Private</h4>
                    <p class="card-text">Your data is encrypted and we never share your information with third parties.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="container mb-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">How VaultRate Works</h2>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3">
            <div class="text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <span class="h4 mb-0">1</span>
                </div>
                <h5>Create Account</h5>
                <p class="text-muted">Sign up for a free account in less than a minute.</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <span class="h4 mb-0">2</span>
                </div>
                <h5>Set Targets</h5>
                <p class="text-muted">Choose currency pairs and set your target rates.</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <span class="h4 mb-0">3</span>
                </div>
                <h5>Track Rates</h5>
                <p class="text-muted">We monitor rates continuously using Frankfurter API.</p>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <span class="h4 mb-0">4</span>
                </div>
                <h5>Get Notified</h5>
                <p class="text-muted">Receive alerts when your targets are reached.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Ready to Start Tracking Currencies?</h2>
        <p class="lead mb-4">Join thousands of users who are already making smarter currency decisions.</p>
        <a href="pages/register.php" class="btn btn-primary btn-lg px-4">Sign Up Free</a>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>