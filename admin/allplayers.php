<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges (minimum admin_level1)
requireAdmin('admin_level1');

// Initialize classes
$player = new Player();
$logger = new Logger();

// Get current page from query parameter, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Items per page
$limit = 10; // Show 10 players per page

// Get total player count
$totalPlayers = $player->getTotalPlayers();

// Calculate total pages
$totalPages = ceil($totalPlayers / $limit);

// Ensure page doesn't exceed total pages
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Fetch players for current page
$players = $player->getPlayers($page, $limit);

// Log the action
$logger->logAction(
    $_SESSION['user_id'], 
    'view_all_players', 
    "Viewed all players page {$page} of {$totalPages}"
);

// Page title
$pageTitle = 'All Players - Admin Dashboard';
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
        .player-card {
            background-color: var(--dark-card-bg);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid var(--dark-border);
        }
        
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .player-header {
            padding: 1rem;
            border-bottom: 1px solid var(--dark-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .player-body {
            padding: 1rem;
        }
        
        .player-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #fff;
        }
        
        .player-citizenid {
            font-family: monospace;
            color: #adb5bd;
        }
        
        .player-detail-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #fff;
        }
        
        .player-detail-value {
            color: #adb5bd;
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
                        <a href="players.php" class="nav-link active">
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
                <h1 class="h2">All Players</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i> Showing <?php echo count($players); ?> of <?php echo $totalPlayers; ?> total players (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
            </div>
            
            <!-- Players List -->
            <div class="row">
                <?php if (empty($players)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> No players found.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($players as $player): ?>
                        <div class="col-md-6">
                            <div class="player-card">
                                <div class="player-header">
                                    <div>
                                        <img src="https://i.imgur.com/4H2c5AB.gif" alt="Player Avatar" class="img-fluid rounded mb-2" style="max-width: 50px; max-height: 50px; border: 2px solid #2a2e32;">
                                        <div class="player-name"><?php echo htmlspecialchars($player['name'] ?? 'Unknown'); ?></div>
                                        <div class="player-citizenid"><?php echo htmlspecialchars($player['citizenid'] ?? $player['id']); ?></div>
                                        <?php if (isset($player['name']) && !empty($player['name'])): ?>
                                            <div class="player-fivem-name text-info small">
                                                <i class="fas fa-gamepad me-1"></i> FiveM Name: <?php echo htmlspecialchars($player['name']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <a href="players.php?citizenid=<?php echo urlencode($player['citizenid'] ?? $player['id']); ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Show Details
                                    </a>
                                </div>
                                <div class="player-body">
                                    <div class="row">
                                        <?php if (isset($player['license'])): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="player-detail-label">License</div>
                                                <div class="player-detail-value"><?php echo htmlspecialchars($player['license']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($player['phone'])): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="player-detail-label">Phone</div>
                                                <div class="player-detail-value"><?php echo htmlspecialchars($player['phone']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($player['job'])): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="player-detail-label">Job</div>
                                                <div class="player-detail-value"><?php echo htmlspecialchars($player['job']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($player['last_login'])): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="player-detail-label">Last Login</div>
                                                <div class="player-detail-value"><?php echo htmlspecialchars($player['last_login']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Page Button -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
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
                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                        }
                        
                        // Show page numbers in range
                        for ($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                        }
                        
                        // Show last page if not in range
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        
                        <!-- Next Page Button -->
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
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