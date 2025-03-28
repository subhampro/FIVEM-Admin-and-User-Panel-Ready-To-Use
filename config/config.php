<?php
// Database connection configuration
define('DB_HOST', '103.49.70.39');
define('DB_USER', 'elapsed');
define('DB_PASSWORD', 'ddosers sahi main randi hain bhai !!');
define('DB_NAME', 'elapsed');
define('DB_CHARSET', 'utf8mb4');

// Game database configuration
define('GAME_DB_NAME', 'elapsed'); // Database name for the game server

// Website configuration
define('SITE_URL', 'http://localhost');  // Change to your actual domain in production
define('SITE_NAME', 'FiveM Server Dashboard');
define('SITE_EMAIL', 'admin@example.com');

// Admin levels
define('ADMIN_LEVEL1', 'admin_level1');  // Can only view data
define('ADMIN_LEVEL2', 'admin_level2');  // Can view and edit data (changes need approval)
define('ADMIN_LEVEL3', 'admin_level3');  // Can view, edit, and approve changes

// Security
define('PASSWORD_PEPPER', 'g&*hj7Kl9$pQzW'); // Random string for additional password security
define('SESSION_LIFETIME', 1800); // Session lifetime in seconds (30 minutes)

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('UTC');

// Define CSRF token if not exists
// Note: session is managed in init.php
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?> 
