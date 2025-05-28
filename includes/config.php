<?php
ob_start();
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Session management
session_start();

// Base URL
define('SITE_NAME', 'VaultRate'); 
define('BASE_URL', 'http://localhost/vaultrate/');

// includes/config.php
define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vaultrate');

// Frankfurter API
define('API_BASE', 'https://api.frankfurter.app/');

// Email configuration (for notifications)
define('MAIL_FROM', 'anusemana24@gmail.com');
define('MAIL_FROM_NAME', 'VaultRate');