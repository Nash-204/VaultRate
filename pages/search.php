<?php
require_once '../includes/header.php';

if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$error = '';
$success = '';
$results = [];

// Get list of available currencies from API
$available_currencies = json_decode(file_get_contents('https://api.frankfurter.app/currencies'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_currency = strtoupper(trim($_POST['base_currency']));
    $target_currency = strtoupper(trim($_POST['target_currency']));
    
    if (empty($base_currency) || empty($target_currency)) {
        $error = 'Please select both base and target currencies';
    } elseif ($base_currency === $target_currency) {
        $error = 'Base and target currencies cannot be the same';
    } else {
        // Get latest rates from API
        $api_url = "https://api.frankfurter.app/latest?from={$base_currency}&to={$target_currency}";
        $response = file_get_contents($api_url);
        
        if ($response === false) {
            $error = 'Failed to fetch currency data';
        } else {
            $data = json_decode($response, true);
            $results = [
                'base' => $base_currency,
                'target' => $target_currency,
                'rate' => $data['rates'][$target_currency],
                'date' => $data['date']
            ];
        }
    }
}
?>


<div class="row container-fluid overflow-x-hidden min-vh-100 ">
    <div class="col-md-8 mx-auto">
        <div class="card mb-4 card-hover-effect">
            <div class="card-header">
                <h5>Search Currency Rates</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="base_currency" class="form-label">Base Currency</label>
                            <select class="form-select" id="base_currency" name="base_currency" required>
                                <option value="">Select currency</option>
                                <?php foreach ($available_currencies as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo isset($_POST['base_currency']) && $_POST['base_currency'] === $code ? 'selected' : ''; ?>>
                                        <?php echo "{$code} - {$name}"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="target_currency" class="form-label">Target Currency</label>
                            <select class="form-select" id="target_currency" name="target_currency" required>
                                <option value="">Select currency</option>
                                <?php foreach ($available_currencies as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo isset($_POST['target_currency']) && $_POST['target_currency'] === $code ? 'selected' : ''; ?>>
                                        <?php echo "{$code} - {$name}"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="card mb-4 card-hover-effect">
                <div class="card-header">
                    <h5>Search Results</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h4 class="text-center">
                                    1 <?php echo $results['base']; ?> = 
                                    <?php echo number_format($results['rate'], 6); ?> <?php echo $results['target']; ?>
                                </h4>
                                <p class="text-center text-muted mb-0">
                                    Last updated: <?php echo $results['date']; ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column justify-content-center">
                                <h5 class="text-center mb-3">Track This Currency</h5>
                                <div class="text-center">
                                    <a href="track.php?base=<?php echo $results['base']; ?>&target=<?php echo $results['target']; ?>&rate=<?php echo $results['rate']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Track Rate
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>