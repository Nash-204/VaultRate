<?php
require_once '../includes/header.php';

if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Initialize variables with default values
$tracking_id = 0;
$base_currency = '';
$target_currency = '';
$current_rate = 0;
$target_rate = 0;
$lower_threshold = 0;
$upper_threshold = 0;
$is_edit = false;

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $tracking_id = (int)$_GET['id'];
    
    // Verify the tracking belongs to the user
    $tracking = $db->fetchOne("SELECT id FROM tracked_currencies WHERE id = ? AND user_id = ?", [$tracking_id, $user_id]);
    
    if ($tracking) {
        $db->query("DELETE FROM tracked_currencies WHERE id = ?", [$tracking_id]);
        $success = 'Currency tracking deleted successfully';
        header("Location: dashboard.php?success=" . urlencode($success));
        exit();
    } else {
        $error = 'Invalid tracking ID or you do not have permission to delete this tracking';
    }
}

// Handle edit or add new
if (isset($_GET['base']) && isset($_GET['target'])) {
    $base_currency = strtoupper(trim($_GET['base']));
    $target_currency = strtoupper(trim($_GET['target']));
    $current_rate = isset($_GET['rate']) ? (float)$_GET['rate'] : 0;
    
    // Check if already tracking this pair
    $existing = $db->fetchOne("
        SELECT id, target_rate, lower_threshold, upper_threshold 
        FROM tracked_currencies 
        WHERE user_id = ? AND base_currency = ? AND target_currency = ?
    ", [$user_id, $base_currency, $target_currency]);
    
    if ($existing) {
        // Edit existing
        $tracking_id = $existing['id'];
        $target_rate = $existing['target_rate'];
        $lower_threshold = $existing['lower_threshold'];
        $upper_threshold = $existing['upper_threshold'];
        $is_edit = true;
    } else {
        // New tracking
        $target_rate = $current_rate;
        $lower_threshold = $current_rate * 0.95; // 5% below current
        $upper_threshold = $current_rate * 1.05; // 5% above current
    }
} elseif (isset($_GET['id'])) {
    // Edit existing by ID
    $tracking_id = (int)$_GET['id'];
    $tracking = $db->fetchOne("
        SELECT id, base_currency, target_currency, target_rate, lower_threshold, upper_threshold
        FROM tracked_currencies 
        WHERE id = ? AND user_id = ?
    ", [$tracking_id, $user_id]);
    
    if (!$tracking) {
        $error = 'Invalid tracking ID or you do not have permission to edit this tracking';
    } else {
        $base_currency = $tracking['base_currency'];
        $target_currency = $tracking['target_currency'];
        $target_rate = $tracking['target_rate'];
        $lower_threshold = $tracking['lower_threshold'];
        $upper_threshold = $tracking['upper_threshold'];
        $is_edit = true;
        
        // Get current rate
        $api_url = "https://api.frankfurter.app/latest?from={$base_currency}&to={$target_currency}";
        $response = file_get_contents($api_url);
        $data = json_decode($response, true);
        $current_rate = $data['rates'][$target_currency];
    }
} else {
    header("Location: search.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_rate = (float)$_POST['target_rate'];
    $lower_threshold = (float)$_POST['lower_threshold'];
    $upper_threshold = (float)$_POST['upper_threshold'];
    
    if ($target_rate <= 0 || $lower_threshold <= 0 || $upper_threshold <= 0) {
        $error = 'Rates must be positive numbers';
    } elseif ($upper_threshold <= $lower_threshold) {
        $error = 'Upper threshold must be greater than lower threshold';
    } else {
        if ($is_edit) {
            $db->query("
                UPDATE tracked_currencies 
                SET target_rate = ?, lower_threshold = ?, upper_threshold = ?
                WHERE id = ? AND user_id = ?
            ", [$target_rate, $lower_threshold, $upper_threshold, $tracking_id, $user_id]);
            $success = 'Currency tracking updated successfully';
        } else {
            $db->query("
                INSERT INTO tracked_currencies 
                (user_id, base_currency, target_currency, target_rate, lower_threshold, upper_threshold)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$user_id, $base_currency, $target_currency, $target_rate, $lower_threshold, $upper_threshold]);
            
            $tracking_id = $db->getConnection()->insert_id;
            $success = 'Currency tracking added successfully';
        }
        
        // Log the current rate
        $db->query("
            INSERT INTO currency_history (tracked_id, current_rate)
            VALUES (?, ?)
        ", [$tracking_id, $current_rate]);
        
        header("Location: dashboard.php?success=" . urlencode($success));
        exit();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><?php echo $is_edit ? 'Edit' : 'Add'; ?> Currency Tracking</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="mb-4 p-3 bg-light rounded-3">
                    <h4 class="text-center">
                        1 <?php echo htmlspecialchars($base_currency); ?> = 
                        <?php echo number_format($current_rate, 6); ?> <?php echo htmlspecialchars($target_currency); ?>
                    </h4>
                    <p class="text-center text-muted mb-0">
                        Current exchange rate
                    </p>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="target_rate" class="form-label">Target Rate</label>
                        <input type="number" step="0.000001" class="form-control" id="target_rate" name="target_rate" 
                               value="<?php echo number_format($target_rate, 6, '.', ''); ?>" required>
                        <div class="form-text">
                            You'll be notified when the rate reaches this value or higher
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="lower_threshold" class="form-label">Lower Notification Threshold</label>
                        <input type="number" step="0.000001" class="form-control" id="lower_threshold" 
                            name="lower_threshold" value="<?php echo number_format($lower_threshold, 6, '.', ''); ?>" required>
                        <div class="form-text">
                            You'll be notified when the rate falls to or below this value
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="upper_threshold" class="form-label">Upper Notification Threshold</label>
                        <input type="number" step="0.000001" class="form-control" id="upper_threshold" 
                            name="upper_threshold" value="<?php echo number_format($upper_threshold, 6, '.', ''); ?>" required>
                        <div class="form-text">
                            You'll be notified when the rate rises to or above this value
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary me-md-2">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>