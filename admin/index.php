<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level1');

// Get admin dashboard statistics
$admin = new Admin();
$stats = $admin->getDashboardStats();

// Get pending changes count
$pendingChanges = new PendingChanges();
$pendingCount = $pendingChanges->countPendingChanges('pending');

// Get unread notifications count
$unreadNotifications = $admin->countUnreadNotifications($_SESSION['user_id']);

// Page title
$pageTitle = 'Admin Dashboard - ' . getSetting('site_name', 'FiveM Server Dashboard');

// Remove the duplicate checks and session_start that are causing the redirect loop
// Include configuration 
require_once '../config/config.php';

// Create pending_changes table if it doesn't exist
function ensurePendingChangesTable() {
    $db = new Database();
    $query = "SHOW TABLES LIKE 'pending_changes'";
    $result = $db->query($query);
    
    if (!$result || $result->num_rows === 0) {
        // Table doesn't exist, create it
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
        return true;
    }
    
    return false;
}

// Ensure the pending_changes table exists
ensurePendingChangesTable();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Dark theme optimizations */
        .card {
            background-color: #212529;
            color: #e9ecef;
            border-color: #2a2e32;
        }
        
        .card-header {
            background-color: #2a2e32;
            border-color: #212529;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .font-weight-bold {
            font-weight: 600;
        }
        
        .text-gray-300 {
            color: #adb5bd !important;
        }
        
        .text-primary {
            color: #0d6efd !important;
        }
        
        .text-success {
            color: #198754 !important;
        }
        
        .text-info {
            color: #0dcaf0 !important;
        }
        
        .text-warning {
            color: #ffc107 !important;
        }
        
        .border-left-primary {
            border-left: 4px solid #0d6efd;
        }
        
        .border-left-success {
            border-left: 4px solid #198754;
        }
        
        .border-left-info {
            border-left: 4px solid #0dcaf0;
        }
        
        .border-left-warning {
            border-left: 4px solid #ffc107;
        }
        
        .table {
            color: #e9ecef;
        }
        
        .alert-info {
            color: #e9ecef;
            background-color: #2a4059;
            border-color: #1e5f8e;
        }
        
        /* Fix for dropdown menu */
        .dropdown-menu {
            background-color: #212529;
            border-color: #2a2e32;
        }
        
        .dropdown-item {
            color: #e9ecef;
        }
        
        .dropdown-item:hover {
            background-color: #2a2e32;
            color: #e9ecef;
        }
        
        /* Fix for form controls */
        .form-select, .form-control {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
        }
        
        .form-select option {
            background-color: #212529;
            color: #e9ecef;
        }
        
        /* Fix for recent activity table */
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
            color: #e9ecef;
        }
        
        .table-hover tbody tr {
            color: #e9ecef;
        }
        
        .table thead th {
            border-color: #495057;
            color: #e9ecef;
            background-color: #343a40;
        }
        
        .table tbody td {
            border-color: #495057;
            color: #e9ecef;
            background-color: #212529;
        }
        
        /* Additional fixes for table background */
        .table-hover tbody tr {
            background-color: #212529;
        }
        
        .table-responsive {
            background-color: #212529;
        }
        
        .table {
            background-color: #212529;
        }
    </style>
</head>
<body class="admin-panel">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="d-flex flex-column flex-shrink-0 p-3">
                <a href="../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
                    <span class="fs-4 text-white">Admin Panel</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">
                            <i class="fas fa-home me-2"></i> Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../user/index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i> User Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="players.php" class="nav-link">
                            <i class="fas fa-user me-2"></i> Player Management
                        </a>
                    </li>
                    <?php if (isAdmin('admin_level2')): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="fas fa-users me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="database_status.php" class="nav-link">
                            <i class="fas fa-database me-2"></i> Database Management
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isAdmin('admin_level3')): ?>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog me-2"></i> System Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logs.php" class="nav-link">
                            <i class="fas fa-file-alt me-2"></i> System Logs
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2 fs-5"></i>
                        <strong><?php echo $_SESSION['username']; ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="../user/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="../logout.php" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Search Players Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i> Quick Player Search</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="players.php" class="mb-0">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search for players...">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="field" class="form-select">
                                    <option value="name">Name</option>
                                    <option value="citizenid">Citizen ID</option>
                                    <option value="license">License</option>
                                    <option value="phone">Phone</option>
                                    <option value="steam">Steam ID</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="allplayers.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-users"></i> All Players
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Players</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $stats['player_count']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isAdmin('admin_level2')): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Users Registered</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $stats['user_count']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Pending Changes</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $pendingCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Admin Users</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $stats['admin_count']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bolt me-1"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="players.php" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Search Players
                            </a>
                        </div>
                        
                        <?php if (isAdmin('admin_level2')): ?>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="users.php" class="btn btn-info w-100">
                                <i class="fas fa-user-edit me-2"></i> Manage Users
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isAdmin('admin_level3')): ?>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="pending_changes.php" class="btn btn-warning w-100">
                                <i class="fas fa-clock me-2"></i> Review Pending Changes
                                <?php if ($pendingCount > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $pendingCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="settings.php" class="btn btn-secondary w-100">
                                <i class="fas fa-cog me-2"></i> System Settings
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="backup.php" class="btn btn-dark w-100">
                                <i class="fas fa-database me-2"></i> Backup Database
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="logs.php" class="btn btn-danger w-100">
                                <i class="fas fa-file-alt me-2"></i> View System Logs
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (isAdmin('admin_level2')): ?>
            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-history me-1"></i> Recent Activity</div>
                    <a href="all_activities.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i> All Activity
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $recentLogs = $admin->getRecentLogs(5);
                    if ($recentLogs): 
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td><?php echo formatDatetime($log['timestamp']); ?></td>
                                        <td>
                                            <?php
                                            echo isset($log['username']) ? htmlspecialchars($log['username']) : 'Unknown';
                                            ?>
                                        </td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $log['action_type'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        No recent activity found.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 