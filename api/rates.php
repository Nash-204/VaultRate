<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$db = new Database();

// Get all tracked currencies that need checking
$trackings = $db->fetchAll("
    SELECT tc.id, tc.user_id, tc.base_currency, tc.target_currency, tc.target_rate, tc.notification_threshold, 
           u.email, u.username
    FROM tracked_currencies tc
    JOIN users u ON tc.user_id = u.id
");

$notifications = [];

foreach ($trackings as $tracking) {
    // Get current rate from Frankfurter API
    $api_url = "https://api.frankfurter.app/latest?from={$tracking['base_currency']}&to={$tracking['target_currency']}";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    
    if ($data && isset($data['rates'][$tracking['target_currency']])) {
        $current_rate = $data['rates'][$tracking['target_currency']];
        
        // Log the rate check
        $db->query("
            INSERT INTO currency_history (tracked_id, current_rate)
            VALUES (?, ?)
        ", [$tracking['id'], $current_rate]);
        
        // Check if notification should be sent
        if ($current_rate >= $tracking['upper_threshold'] || $current_rate <= $tracking['lower_threshold']) {
            $notifications[] = [
                'user_id' => $tracking['user_id'],
                'email' => $tracking['email'],
                'username' => $tracking['username'],
                'currency_pair' => "{$tracking['base_currency']}/{$tracking['target_currency']}",
                'target_rate' => $tracking['target_rate'],
                'current_rate' => $current_rate,
                'threshold_type' => $current_rate >= $tracking['upper_threshold'] ? 'upper' : 'lower'
            ];
        }
    }
}

// Process notifications
$result = [
    'status' => 'success',
    'checked' => count($trackings),
    'notifications' => count($notifications),
    'notification_details' => $notifications
];

echo json_encode($result);