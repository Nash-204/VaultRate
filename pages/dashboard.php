<?php
// Include the header file that contains common page elements and initialization
require_once '../includes/header.php';

// Check if user is logged in, if not redirect to login page
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize database connection
$db = new Database();
// Get current user's ID from session
$user_id = $_SESSION['user_id'];

// Fetch all currencies tracked by the user along with their current rates
$tracked_currencies = $db->fetchAll("
    SELECT tc.*, 
        (SELECT ch.current_rate FROM currency_history ch WHERE ch.tracked_id = tc.id ORDER BY ch.checked_at DESC LIMIT 1) as current_rate
    FROM tracked_currencies tc
    WHERE tc.user_id = ?
    ORDER BY tc.created_at DESC
", [$user_id]);

// Check for threshold breaches to generate notifications
$notifications = [];
foreach ($tracked_currencies as $currency) {
    // Check if current rate is above upper threshold
    if ($currency['current_rate'] >= $currency['upper_threshold']) {
        $notifications[] = [
            'message' => "{$currency['base_currency']}/{$currency['target_currency']} has risen above your upper threshold of {$currency['upper_threshold']}",
            'currency_pair' => "{$currency['base_currency']}/{$currency['target_currency']}"
        ];
    } 
    // Check if current rate is below lower threshold
    elseif ($currency['current_rate'] <= $currency['lower_threshold']) {
        $notifications[] = [
            'message' => "{$currency['base_currency']}/{$currency['target_currency']} has fallen below your lower threshold of {$currency['lower_threshold']}",
            'currency_pair' => "{$currency['base_currency']}/{$currency['target_currency']}"
        ];
    }
}
?>

<!-- Main dashboard layout -->
<div class="dashboard-wrapper">
        <div class="dashboard-content">
            <div class="row m-2">
                <!-- Left column (1/3 width) for quick actions and notifications -->
                <div class="col-md-4">
                    <!-- Quick Actions Card -->
                    <div class="card mb-4 card-hover-effect">
                        <div class="card-header">
                            <h5>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <!-- Button to search for currencies -->
                                <a href="search.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i> Search Currencies
                                </a>
                                <!-- Button to view user profile -->
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifications Section - only shown if there are notifications -->
                    <?php if (!empty($notifications)): ?>
                        <div class="card mb-4 border-warning card-hover-effect notifications-card card-scrollable">
                            <div class="card-header bg-warning text-dark">
                                <h5><i class="fas fa-bell me-2"></i>Notifications</h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <!-- Loop through each notification and display it -->
                                    <?php foreach ($notifications as $notification): ?>
                                        <li class="list-group-item">
                                            <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                            <?php echo htmlspecialchars($notification['message']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Right column (2/3 width) for tracked currencies -->
                <div class="col-md-8">
                    <!-- Tracked Currencies Card -->
                    <div class="card mb-4 card-hover-effect tracked-currencies-card card-scrollable">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">My Tracked Currencies</h5>
                                <!-- Display count of tracked currencies -->
                                <span class="badge bg-primary rounded-pill"><?php echo count($tracked_currencies); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Message shown if user isn't tracking any currencies -->
                            <?php if (empty($tracked_currencies)): ?>
                                <div class="alert alert-info m-3">
                                    You're not tracking any currencies yet. <a href="search.php">Search for currencies</a> to start tracking.
                                </div>
                            <?php else: ?>
                                <!-- Table of tracked currencies -->
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Currency Pair</th>
                                                <th>Target Rate</th>
                                                <th>Current Rate</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loop through each tracked currency and display its details -->
                                            <?php foreach ($tracked_currencies as $currency): ?>
                                                <?php
                                                // Determine status and styling based on current rate
                                                $status = '';
                                                $status_class = '';
                                                if ($currency['current_rate'] >= $currency['upper_threshold']) {
                                                    $status = 'Above Upper Threshold';
                                                    $status_class = 'text-success';
                                                } elseif ($currency['current_rate'] <= $currency['lower_threshold']) {
                                                    $status = 'Below Lower Threshold';
                                                    $status_class = 'text-danger';
                                                } elseif ($currency['current_rate'] >= $currency['target_rate']) {
                                                    $status = 'Above Target';
                                                    $status_class = 'text-success';
                                                } else {
                                                    $status = 'Below Target';
                                                    $status_class = 'text-secondary';
                                                }
                                                ?>
                                                <tr>
                                                    <!-- Display currency pair -->
                                                    <td><?php echo "{$currency['base_currency']}/{$currency['target_currency']}"; ?></td>
                                                    <!-- Display target rate with formatting -->
                                                    <td><?php echo number_format($currency['target_rate'], 6); ?></td>
                                                    <!-- Display current rate with formatting -->
                                                    <td><?php echo number_format($currency['current_rate'], 6); ?></td>
                                                    <!-- Display status with appropriate styling -->
                                                    <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
                                                    <td>
                                                        <!-- Edit button for this currency tracking -->
                                                        <a href="track.php?action=edit&id=<?php echo $currency['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <!-- Delete button with confirmation dialog -->
                                                        <a href="track.php?action=delete&id=<?php echo $currency['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this tracking?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<!-- Include the footer file that contains common page elements -->
<?php require_once '../includes/footer.php'; ?>