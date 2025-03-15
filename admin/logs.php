<?php
require_once '../config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login.php');
    exit;
}

$admin = new Admin();
$logger = new Logger();

// Handle date range filter
$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Handle action type filter
$actionType = isset($_GET['action_type']) ? $_GET['action_type'] : '';

// Handle user filter
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Get logs with filters
$logs = $logger->getLogs($startDate, $endDate, $actionType, $userId);

// Get unique action types for filter dropdown
$actionTypes = $logger->getUniqueActionTypes();

// Get users for filter dropdown
$users = $admin->getAllUsers();

// Page title
$pageTitle = 'System Logs';

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="players.php">
                            <i class="fas fa-gamepad"></i> Players
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_changes.php">
                            <i class="fas fa-clock"></i> Pending Changes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="logs.php">
                            <i class="fas fa-list"></i> Logs
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filter Logs</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="log-date-range" class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="log-date-range" name="date_range" value="<?php echo $startDate . ' - ' . $endDate; ?>" readonly>
                            <input type="hidden" name="start" id="start-date" value="<?php echo $startDate; ?>">
                            <input type="hidden" name="end" id="end-date" value="<?php echo $endDate; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="action-type" class="form-label">Action Type</label>
                            <select class="form-select" id="action-type" name="action_type">
                                <option value="">All Actions</option>
                                <?php foreach ($actionTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $actionType === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="user-id" class="form-label">User</label>
                            <select class="form-select" id="user-id" name="user_id">
                                <option value="0">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $userId === (int)$user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">System Logs</h5>
                    <div>
                        <a href="logs.php?export=csv&start=<?php echo $startDate; ?>&end=<?php echo $endDate; ?>&action_type=<?php echo $actionType; ?>&user_id=<?php echo $userId; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download"></i> Export to CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-info">
                            No logs found for the selected filters.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Action Type</th>
                                        <th>Action</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log['id']; ?></td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['timestamp'])); ?></td>
                                            <td>
                                                <?php 
                                                if ($log['user_id'] > 0) {
                                                    echo htmlspecialchars($log['username']);
                                                } else {
                                                    echo 'System';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['action_type']); ?></td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 