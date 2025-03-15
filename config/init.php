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

// Database initialization - Make sure both website and game databases are configured
if (!isset($gameDbConfig) || !isset($gameDbConfig['hostname']) || empty($gameDbConfig['hostname'])) {
    // Set default game database config if not set
    $gameDbConfig = [
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'elapsed2_0', // Default game database name
        'port'     => 3306
    ];
}

// Ensure DB connection for admin panel
function ensureAdminDatabaseTables() {
    global $db;
    
    // Check if pending_changes table exists
    $query = "SHOW TABLES LIKE 'pending_changes'";
    $result = $db->query($query);
    
    if (!$result || $result->rowCount() === 0) {
        // Create the pending_changes table
        $createTableQuery = "
        CREATE TABLE IF NOT EXISTS `pending_changes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `admin_id` int(11) NOT NULL,
          `target_table` varchar(50) NOT NULL,
          `target_id` varchar(50) NOT NULL,
          `field_name` varchar(50) NOT NULL,
          `old_value` text DEFAULT NULL,
          `new_value` text DEFAULT NULL,
          `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
          `reviewer_id` int(11) DEFAULT NULL,
          `created_at` datetime NOT NULL,
          `reviewed_at` datetime DEFAULT NULL,
          `review_comments` text DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `admin_id` (`admin_id`),
          KEY `reviewer_id` (`reviewer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($createTableQuery);
    }
    
    // Make sure website_users table has is_admin field
    $query = "SHOW COLUMNS FROM `website_users` LIKE 'is_admin'";
    $result = $db->query($query);
    
    if (!$result || $result->rowCount() === 0) {
        // Add is_admin field if it doesn't exist
        $addFieldQuery = "ALTER TABLE `website_users` ADD COLUMN `is_admin` tinyint(1) NOT NULL DEFAULT 0 AFTER `player_id`";
        $db->query($addFieldQuery);
    }
    
    return true;
}

// Call the function to ensure admin database tables
ensureAdminDatabaseTables();
?> 