<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges (minimum admin_level2)
requireAdmin('admin_level2');

// Initialize classes
$admin = new Admin();
$logger = new Logger();

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['field']) ? $_GET['field'] : 'all';

// Validate search field
$validFields = ['all', 'user', 'action_type', 'action', 'timestamp'];
if (!in_array($searchField, $validFields)) {
    $searchField = 'all';
}

// Get current page from query parameter, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Items per page
$limit = 100; // Show 100 activities per page
$offset = ($page - 1) * $limit;

// Get total activities count and activities for current page
$totalActivities = $logger->countActivities($search, $searchField);
$activities = $logger->getActivities($search, $searchField, $limit, $offset);

// Calculate total pages
$totalPages = ceil($totalActivities / $limit);

// Ensure page doesn't exceed total pages
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Log the action
$logger->logAction(
    $_SESSION['user_id'], 
    'view_all_activities', 
    "Viewed all activities page {$page} of {$totalPages}" . ($search ? " with search: {$search}" : "")
);

// Page title
$pageTitle = 'All Activities - Admin Dashboard';
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
        
        .table {
            color: #e9ecef;
            background-color: #212529;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
            color: #e9ecef;
        }
        
        .table-hover tbody tr {
            background-color: #212529;
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
        
        .table-responsive {
            background-color: #212529;
        }
        
        .form-control, .form-select {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
        }
        
        .pagination {
            --bs-pagination-bg: #212529;
            --bs-pagination-color: #e9ecef;
            --bs-pagination-border-color: #2a2e32;
            --bs-pagination-hover-bg: #2a2e32;
            --bs-pagination-hover-color: #e9ecef;
            --bs-pagination-hover-border-color: #495057;
            --bs-pagination-active-bg: #0d6efd;
            --bs-pagination-active-color: #fff;
            --bs-pagination-active-border-color: #0d6efd;
            --bs-pagination-disabled-bg: #212529;
            --bs-pagination-disabled-color: #6c757d;
            --bs-pagination-disabled-border-color: #2a2e32;
        }
        
        /* Fix for recent activity table */
        .activity-timestamp {
            white-space: nowrap;
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
                        <a href="index.php" class="nav-link">
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
                        <a href="logs.php" class="nav-link active">
                            <i class="fas fa-file-alt me-2"></i> System Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pending_changes.php" class="nav-link">
                            <i class="fas fa-tasks me-2"></i> Pending Changes
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
                <h1 class="h2">All Activities</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i> Search Activities</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="" class="mb-0">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="field" class="form-select">
                                    <option value="all" <?php echo $searchField === 'all' ? 'selected' : ''; ?>>All Fields</option>
                                    <option value="user" <?php echo $searchField === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="action_type" <?php echo $searchField === 'action_type' ? 'selected' : ''; ?>>Action Type</option>
                                    <option value="action" <?php echo $searchField === 'action' ? 'selected' : ''; ?>>Details</option>
                                    <option value="timestamp" <?php echo $searchField === 'timestamp' ? 'selected' : ''; ?>>Date/Time</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="all_activities.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i> Showing <?php echo count($activities); ?> of <?php echo $totalActivities; ?> total activities (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                <?php if (!empty($search)): ?>
                    | Search results for: "<?php echo htmlspecialchars($search); ?>" in <?php echo $searchField === 'all' ? 'all fields' : $searchField; ?>
                <?php endif; ?>
            </div>
            
            <!-- Activities Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Activity Log</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> No activities found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Time</th>
                                        <th width="15%">User</th>
                                        <th width="15%">Action</th>
                                        <th width="40%">Details</th>
                                        <th width="10%">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo $activity['id']; ?></td>
                                            <td class="activity-timestamp"><?php echo formatDatetime($activity['timestamp']); ?></td>
                                            <td>
                                                <?php
                                                echo isset($activity['username']) ? htmlspecialchars($activity['username']) : 'Unknown';
                                                ?>
                                            </td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $activity['action_type'])); ?></td>
                                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mb-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Page Button -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&field=<?php echo $searchField; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php
                        // Calculate range of page numbers to show
                        $range = 2; // Show 2 pages before and after current page
                        $startPage = max(1, $page - $range);
                        $endPage = min($totalPages, $page + $range);
                        
                        // Show first page if not in range
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '&field=' . $searchField . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                        }
                        
                        // Show page numbers in range
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '&field=' . $searchField . '">' . $i . '</a></li>';
                        }
                        
                        // Show last page if not in range
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&search=' . urlencode($search) . '&field=' . $searchField . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Next Page Button -->
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&field=<?php echo $searchField; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 