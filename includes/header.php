<?php
/**
 * Common header file for the website
 */

// Note: Session is already started in init.php
// No need to start it again here

// Include init if not already included
if (!defined('INIT_INCLUDED')) {
    require_once __DIR__ . '/../config/init.php';
}

// Get page title or use default
$pageTitle = isset($pageTitle) ? $pageTitle : getSetting('site_name', 'FiveM Server Dashboard');

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $user = new User();
    $currentUser = $user->getUserById($_SESSION['user_id']);
}

// Function to get correct base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    // Remove /includes or /admin or /user from path if present
    $base_path = preg_replace('/(\/includes|\/admin|\/user)$/', '', $script_name);
    if ($base_path === '\\') $base_path = '';
    return $protocol . "://" . $host . $base_path;
}

// Get base URL once for all links
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="FiveM Server Dashboard - Manage your game character and server data">
    <meta name="theme-color" content="#121212">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/main.css">
    <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/admin.css">
    <?php endif; ?>
    <style>
        :root {
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-input-bg: #2d2d2d;
            --dark-hover: #3d3d3d;
            --dark-border: #4d4d4d;
            --dark-text: #ffffff;
            --dark-text-secondary: #cccccc;
            --accent-color: #4f46e5;
            --accent-hover: #4338ca;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
        }
        
        /* Force text to be light on dark backgrounds */
        body {
            background-color: var(--dark-bg) !important;
            color: var(--dark-text) !important;
        }
        
        p, h1, h2, h3, h4, h5, h6, span, li, td, th, div:not(.btn):not(.badge) {
            color: var(--dark-text) !important;
        }
        
        /* Improved navbar */
        .navbar-dark {
            background-color: var(--dark-card-bg);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-dark .navbar-brand {
            color: var(--dark-text) !important;
        }
        
        .navbar-dark .nav-link {
            color: var(--dark-text-secondary) !important;
        }
        
        .navbar-dark .nav-link:hover,
        .navbar-dark .nav-link.active {
            color: var(--dark-text) !important;
        }
        
        /* Dropdown menu styling */
        .dropdown-menu {
            background-color: var(--dark-card-bg) !important;
            border: 1px solid var(--dark-border) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-item {
            color: var(--dark-text) !important;
        }
        
        .dropdown-item:hover {
            background-color: var(--dark-hover) !important;
            color: var(--dark-text) !important;
        }
        
        .dropdown-divider {
            border-top-color: var(--dark-border) !important;
        }
        
        /* Button styling */
        .btn-primary {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: white !important;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--accent-hover) !important;
            border-color: var(--accent-hover) !important;
            color: white !important;
        }
        
        /* Card styling for dark mode */
        .card {
            background-color: var(--dark-card-bg) !important;
            border: 1px solid var(--dark-border) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border-bottom: 1px solid var(--dark-border) !important;
            color: var(--dark-text) !important;
        }
        
        .card-body {
            color: var(--dark-text) !important;
        }
        
        /* Form controls */
        .form-control, .form-select {
            background-color: var(--dark-input-bg) !important;
            border: 1px solid var(--dark-border) !important;
            color: var(--dark-text) !important;
        }
        
        .form-control::placeholder {
            color: var(--dark-text-secondary) !important;
            opacity: 0.7;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--dark-input-bg) !important;
            border-color: var(--accent-color) !important;
            color: var(--dark-text) !important;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25) !important;
        }
        
        /* Table styling */
        .table {
            color: var(--dark-text) !important;
        }
        
        .table-dark {
            background-color: var(--dark-card-bg) !important;
        }
        
        .table tr {
            border-color: var(--dark-border) !important;
        }
        
        /* Alert styling */
        .alert {
            border: none !important;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: var(--success-color) !important;
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.2) !important;
            color: var(--warning-color) !important;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: var(--danger-color) !important;
        }
        
        .alert-info {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: var(--info-color) !important;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        /* Admin level badges */
        .admin-level {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 0.5rem;
            color: white !important;
        }
        
        .admin-level-1 {
            background-color: var(--info-color);
        }
        
        .admin-level-2 {
            background-color: #8b5cf6;
        }
        
        .admin-level-3 {
            background-color: #ec4899;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $baseUrl; ?>/index.php">
                <?php echo getSetting('site_name', 'FiveM Server Dashboard'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="<?php echo $baseUrl; ?>/register.php">Register</a>
                    </li>
                    <?php else: ?>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>/admin/index.php">Admin Dashboard</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $baseUrl; ?>/user/index.php">My Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
                            <?php if (isAdmin()): ?>
                            <span class="admin-level <?php echo 'admin-level-' . substr($_SESSION['role'], -1); ?>">
                                <?php echo getAdminLevelName($_SESSION['role']); ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/profile.php"><i class="fas fa-user-edit me-2"></i> Edit Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="main-content">
        <div class="container">
            <!-- Flash Messages -->
            <?php $flashMessage = getFlashMessage(); ?>
            <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flashMessage['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?> 