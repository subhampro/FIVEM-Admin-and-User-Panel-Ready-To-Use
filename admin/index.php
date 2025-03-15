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
    
    if (!$result || $result->rowCount() === 0) {
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

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Admin Panel</h1>
    <p>Welcome to the admin panel. Here you can manage users and system settings.</p>
    
    <div class="row mt-4">
        <!-- Users Management Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">User Management</h5>
                </div>
                <div class="card-body">
                    <p>Manage users, reset passwords, and handle user permissions.</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="users.php">View All Users</a></li>
                        <li class="list-group-item"><a href="add_user.php">Add New User</a></li>
                        <li class="list-group-item"><a href="pending_users.php">Pending Approvals</a></li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="users.php" class="btn btn-primary btn-sm">Manage Users</a>
                </div>
            </div>
        </div>
        
        <!-- Database Management Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Database Management</h5>
                </div>
                <div class="card-body">
                    <p>Manage database synchronization between game and website.</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="sync_players.php">Sync Players</a></li>
                        <li class="list-group-item"><a href="database_status.php">Database Status</a></li>
                        <li class="list-group-item"><a href="import_all_players.php">Import All Players</a></li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="sync_players.php" class="btn btn-primary btn-sm">Sync Data</a>
                </div>
            </div>
        </div>
        
        <!-- System Settings Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">System Settings</h5>
                </div>
                <div class="card-body">
                    <p>Configure system settings and view system logs.</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="settings.php">General Settings</a></li>
                        <li class="list-group-item"><a href="logs.php">System Logs</a></li>
                        <li class="list-group-item"><a href="backup.php">Database Backup</a></li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="settings.php" class="btn btn-primary btn-sm">System Settings</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <a href="sync_players.php" class="btn btn-success w-100">
                        <i class="fas fa-sync"></i> Sync Players
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="backup.php" class="btn btn-warning w-100">
                        <i class="fas fa-database"></i> Backup Database
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="logs.php" class="btn btn-info w-100">
                        <i class="fas fa-list"></i> View Logs
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="settings.php" class="btn btn-secondary w-100">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">System Status</h5>
        </div>
        <div class="card-body">
            <?php
            // Check database connections
            $webDbStatus = false;
            $gameDbStatus = false;
            
            try {
                $webDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'fivem_panel');
                $webDbStatus = !$webDb->connect_error;
                
                $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
                $gameDbStatus = !$gameDb->connect_error;
                
                // Close connections
                if ($webDbStatus) $webDb->close();
                if ($gameDbStatus) $gameDb->close();
            } catch (Exception $e) {
                // Connection error
            }
            ?>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Database Status</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Website Database
                            <?php if ($webDbStatus): ?>
                                <span class="badge bg-success">Connected</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Disconnected</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Game Database
                            <?php if ($gameDbStatus): ?>
                                <span class="badge bg-success">Connected</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Disconnected</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Recent Activity</h6>
                    <ul class="list-group">
                        <?php
                        // Get recent user registrations
                        if ($webDbStatus) {
                            $webDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'fivem_panel');
                            $result = $webDb->query("SELECT username, created_at FROM website_users ORDER BY created_at DESC LIMIT 3");
                            
                            if ($result && $result->num_rows > 0) {
                                while ($user = $result->fetch_assoc()) {
                                    echo '<li class="list-group-item">User ' . htmlspecialchars($user['username']) . ' registered on ' . date('M d, Y', strtotime($user['created_at'])) . '</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">No recent registrations</li>';
                            }
                            
                            $webDb->close();
                        } else {
                            echo '<li class="list-group-item">Database connection error</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 