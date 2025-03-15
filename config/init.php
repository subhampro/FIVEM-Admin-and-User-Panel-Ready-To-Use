<?php
// Set session cookie params for better security before starting session
$currentCookieParams = session_get_cookie_params();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    true,  // Secure flag (use only with HTTPS)
    true   // HttpOnly flag
);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once __DIR__ . '/config.php';

// Set error reporting based on environment
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set up class autoloading
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/../includes/classes/';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
});

// Include common functions
require_once __DIR__ . '/../includes/functions.php';

// Include authentication functions
require_once __DIR__ . '/../includes/auth.php';

// Create database connection object
$db = new Database();
$conn = $db->getConnection();

// Check if database connection is successful
if (!$conn) {
    die("Database connection failed. Please check your connection settings.");
}

// Initialize user if logged in
if (isset($_SESSION['user_id'])) {
    $user = new User();
    $currentUser = $user->getUserById($_SESSION['user_id']);
    
    if (!$currentUser) {
        // Invalid user session, log out
        session_unset();
        session_destroy();
        header("Location: " . SITE_URL . "/login.php?error=invalid_session");
        exit;
    }
}

// Set default timezone
date_default_timezone_set('UTC');

// Mark the init file as included
define('INIT_INCLUDED', true);
?> 