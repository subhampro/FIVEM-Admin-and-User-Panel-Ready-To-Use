<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges (minimum admin_level1)
requireAdmin('admin_level1');

// Initialize classes
$admin = new Admin();
$player = new Player();
$logger = new Logger();

// Process search form
$searchResults = [];
$searchPerformed = false;
$searchTerm = '';
$searchField = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $searchField = isset($_GET['field']) ? $_GET['field'] : 'all';
    
    if (empty($searchTerm)) {
        $errorMessage = 'Please enter a search term.';
    } else {
        $searchPerformed = true;
        
        // Log the search
        $logger->logAction(
            $_SESSION['user_id'], 
            'player_search', 
            "Searched for players with {$searchField}: {$searchTerm}"
        );
        
        // Perform search
        $searchResults = $player->searchPlayers($searchTerm, $searchField);
    }
}

// Get player details if ID is provided
$playerDetails = null;
$citizenid = null;

if (isset($_GET['citizenid']) && !empty($_GET['citizenid'])) {
    $citizenid = $_GET['citizenid'];
    
    // Get player data using citizenid
    $playerData = $player->getPlayerByCitizenId($citizenid);
    
    if ($playerData) {
        $playerDetails = [
            'basic' => $playerData,
            'charinfo' => $player->getPlayerCharInfo($citizenid),
            'money' => $player->getPlayerMoney($citizenid),
            'job' => $player->getPlayerJob($citizenid),
            'inventory' => $player->getPlayerInventory($citizenid),
            'metadata' => $player->getPlayerMetadata($citizenid),
            'vehicles' => $player->getPlayerVehicles($citizenid),
            'lastLogin' => $player->getLastLoginTime($citizenid)
        ];
        
        // Log the view
        $logger->logAction(
            $_SESSION['user_id'], 
            'view_player_details', 
            "Viewed details for player with citizenid: {$citizenid}"
        );
    }
}

// Page title
$pageTitle = 'Player Management - Admin Dashboard';
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
        .search-container {
            background-color: var(--dark-card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
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
        
        .item-card {
            background-color: #2a2e32;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--dark-border);
        }
        
        .item-name {
            font-weight: 600;
            color: #fff;
        }
        
        .item-count {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: #0d6efd;
            color: white;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        
        .nav-tabs .nav-link {
            color: #e9ecef;
        }
        
        .nav-tabs .nav-link.active {
            background-color: #2a2e32;
            color: #0d6efd;
            border-color: #212529 #212529 #2a2e32;
        }
        
        .tab-content {
            background-color: #2a2e32;
            border: 1px solid #212529;
            border-top: none;
            padding: 1.5rem;
            border-radius: 0 0 0.5rem 0.5rem;
            color: #e9ecef;
        }
        
        .vehicle-plate {
            font-family: monospace;
            background-color: #212529;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .table {
            color: #e9ecef;
        }
        
        .table td, .table th {
            border-color: #212529;
            background-color: #2a2e32 !important; /* Force background color for table cells */
        }
        
        /* Make sure all text is readable */
        .text-muted {
            color: #adb5bd !important;
        }
        
        /* Dark mode card for player details */
        .card {
            background-color: #212529;
            color: #e9ecef;
            border-color: #2a2e32;
        }
        
        .card-header {
            background-color: #2a2e32;
            border-color: #212529;
        }
        
        /* Fix for form select dropdown */
        .form-select {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
        }
        
        .form-select option {
            background-color: #212529;
            color: #e9ecef;
        }
        
        /* Fix for input fields */
        .form-control {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
        }
        
        .form-control:focus {
            background-color: #2a2e32;
            color: #e9ecef;
        }
        
        /* Fix background colors for alerts */
        .alert-info {
            color: #e9ecef;
            background-color: #2a4059;
            border-color: #1e5f8e;
        }
        
        .alert-warning {
            color: #e9ecef;
            background-color: #584c2a;
            border-color: #8e771e;
        }
        
        .alert-danger {
            color: #e9ecef;
            background-color: #592a2a;
            border-color: #8e1e1e;
        }
        
        /* Dropdown menu styling */
        .dropdown-menu {
            background-color: #212529;
            color: #e9ecef;
            border-color: #2a2e32;
        }
        
        .dropdown-item {
            color: #e9ecef;
        }
        
        .dropdown-item:hover {
            background-color: #2a2e32;
            color: #e9ecef;
        }
        
        /* Fix table and detailed view backgrounds */
        .table-bordered {
            border-color: #2a2e32;
        }
        
        .table-bordered th,
        .table-bordered td {
            border-color: #2a2e32;
            color: #e9ecef;
            background-color: #212529 !important;
        }
        
        /* Fix for character information specific issues */
        #basic .table-bordered th,
        #basic .table-bordered td,
        #finance .table-bordered th,
        #finance .table-bordered td,
        #inventory .table-bordered th,
        #inventory .table-bordered td,
        #vehicles .table-bordered th,
        #vehicles .table-bordered td,
        #metadata .table-bordered th,
        #metadata .table-bordered td {
            background-color: #212529 !important;
            color: #e9ecef !important;
            border-color: #2a2e32 !important;
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
                            <i class="fas fa-home me-2"></i> Dashboard
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
                <h1 class="h2">Player Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <!-- Search Form -->
            <div class="search-container">
                <h5 class="mb-3"><i class="fas fa-search me-2"></i> Search Players</h5>
                <form method="get" action="" class="mb-0">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search for players...">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="field" class="form-select">
                                <option value="name" <?php echo $searchField === 'name' ? 'selected' : ''; ?>>Name</option>
                                <option value="citizenid" <?php echo $searchField === 'citizenid' ? 'selected' : ''; ?>>Citizen ID</option>
                                <option value="license" <?php echo $searchField === 'license' ? 'selected' : ''; ?>>License</option>
                                <option value="phone" <?php echo $searchField === 'phone' ? 'selected' : ''; ?>>Phone</option>
                                <option value="steam" <?php echo $searchField === 'steam' ? 'selected' : ''; ?>>Steam ID</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="players.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($searchPerformed): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Found <?php echo count($searchResults); ?> player(s) matching "<?php echo htmlspecialchars($searchTerm); ?>"
                </div>
                
                <?php if (empty($searchResults)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> No players found matching your search criteria.
                    </div>
                <?php else: ?>
                    <!-- Search Results -->
                    <div class="row">
                        <?php foreach ($searchResults as $result): ?>
                            <div class="col-md-6">
                                <div class="player-card">
                                    <div class="player-header">
                                        <div>
                                            <div class="player-name"><?php echo htmlspecialchars($result['name'] ?? 'Unknown'); ?></div>
                                            <div class="player-citizenid"><?php echo htmlspecialchars($result['citizenid']); ?></div>
                                            <?php if (isset($result['name']) && !empty($result['name'])): ?>
                                                <div class="player-fivem-name text-info small">
                                                    <i class="fas fa-gamepad me-1"></i> FiveM Name: <?php echo htmlspecialchars($result['name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <a href="?citizenid=<?php echo urlencode($result['citizenid']); ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> View Details
                                        </a>
                                    </div>
                                    <div class="player-body">
                                        <div class="row">
                                            <?php if (isset($result['license'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="player-detail-label">License</div>
                                                    <div class="player-detail-value"><?php echo htmlspecialchars($result['license']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($result['phone'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="player-detail-label">Phone</div>
                                                    <div class="player-detail-value"><?php echo htmlspecialchars($result['phone']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($result['job'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="player-detail-label">Job</div>
                                                    <div class="player-detail-value"><?php echo htmlspecialchars($result['job']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($result['last_login'])): ?>
                                                <div class="col-md-6 mb-2">
                                                    <div class="player-detail-label">Last Login</div>
                                                    <div class="player-detail-value"><?php echo htmlspecialchars($result['last_login']); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($playerDetails): ?>
                <!-- Player Details -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i> 
                            <?php 
                            $charInfo = $playerDetails['charinfo'];
                            echo ($charInfo && isset($charInfo['firstname'])) 
                                ? htmlspecialchars($charInfo['firstname'] . ' ' . $charInfo['lastname']) 
                                : 'Player Details'; 
                            ?> 
                            <span class="text-muted">(<?php echo htmlspecialchars($citizenid); ?>)</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="playerDetailsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">
                                    Basic Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#finance" type="button" role="tab" aria-controls="finance" aria-selected="false">
                                    Finances
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="false">
                                    Inventory
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-controls="vehicles" aria-selected="false">
                                    Vehicles
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" data-bs-target="#metadata" type="button" role="tab" aria-controls="metadata" aria-selected="false">
                                    Metadata
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="playerDetailsTabContent">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Character Information</h6>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Full Name</th>
                                                    <td>
                                                        <?php 
                                                        echo ($charInfo && isset($charInfo['firstname'])) 
                                                            ? htmlspecialchars($charInfo['firstname'] . ' ' . $charInfo['lastname']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Birth Date</th>
                                                    <td>
                                                        <?php 
                                                        echo ($charInfo && isset($charInfo['birthdate'])) 
                                                            ? htmlspecialchars($charInfo['birthdate']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Gender</th>
                                                    <td>
                                                        <?php 
                                                        if ($charInfo && isset($charInfo['gender'])) {
                                                            echo $charInfo['gender'] == 0 ? 'Male' : 'Female';
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Phone</th>
                                                    <td>
                                                        <?php 
                                                        echo ($charInfo && isset($charInfo['phone'])) 
                                                            ? htmlspecialchars($charInfo['phone']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Account Information</h6>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Citizen ID</th>
                                                    <td><?php echo htmlspecialchars($citizenid); ?></td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">FiveM Name</th>
                                                    <td>
                                                        <?php 
                                                        echo isset($playerDetails['basic']['name']) 
                                                            ? htmlspecialchars($playerDetails['basic']['name']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">License</th>
                                                    <td>
                                                        <?php 
                                                        echo isset($playerDetails['basic']['license']) 
                                                            ? htmlspecialchars($playerDetails['basic']['license']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Job</th>
                                                    <td>
                                                        <?php 
                                                        if ($playerDetails['job'] && isset($playerDetails['job']['label'])) {
                                                            echo htmlspecialchars($playerDetails['job']['label']);
                                                            if (isset($playerDetails['job']['grade']['name'])) {
                                                                echo ' - ' . htmlspecialchars($playerDetails['job']['grade']['name']);
                                                            }
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Last Login</th>
                                                    <td>
                                                        <?php 
                                                        echo $playerDetails['lastLogin'] 
                                                            ? htmlspecialchars($playerDetails['lastLogin']) 
                                                            : 'N/A'; 
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Finances Tab -->
                            <div class="tab-pane fade" id="finance" role="tabpanel" aria-labelledby="finance-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6 class="mb-3">Financial Information</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card bg-dark mb-3">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><i class="fas fa-wallet me-2"></i> Cash</h5>
                                                        <h3 class="text-success">
                                                            $<?php 
                                                            echo ($playerDetails['money'] && isset($playerDetails['money']['cash'])) 
                                                                ? number_format($playerDetails['money']['cash'], 2) 
                                                                : '0.00'; 
                                                            ?>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-dark mb-3">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><i class="fas fa-university me-2"></i> Bank</h5>
                                                        <h3 class="text-info">
                                                            $<?php 
                                                            echo ($playerDetails['money'] && isset($playerDetails['money']['bank'])) 
                                                                ? number_format($playerDetails['money']['bank'], 2) 
                                                                : '0.00'; 
                                                            ?>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-dark mb-3">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><i class="fas fa-coins me-2"></i> Crypto</h5>
                                                        <h3 class="text-warning">
                                                            <?php 
                                                            echo ($playerDetails['money'] && isset($playerDetails['money']['crypto'])) 
                                                                ? number_format($playerDetails['money']['crypto'], 2) 
                                                                : '0.00'; 
                                                            ?>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Inventory Tab -->
                            <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                <h6 class="mb-3">Player Inventory</h6>
                                <?php if ($playerDetails['inventory'] && is_array($playerDetails['inventory']) && !empty($playerDetails['inventory'])): ?>
                                    <div class="row">
                                        <?php foreach ($playerDetails['inventory'] as $slot => $item): ?>
                                            <?php 
                                            // Skip empty items
                                            if (empty($item) || !isset($item['name']) || empty($item['name'])) {
                                                continue;
                                            }
                                            
                                            // Determine item count
                                            $itemCount = isset($item['count']) ? $item['count'] : (isset($item['amount']) ? $item['amount'] : 1);
                                            ?>
                                            <div class="col-md-4">
                                                <div class="item-card">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                        <span class="item-count">x<?php echo $itemCount; ?></span>
                                                    </div>
                                                    <?php if (isset($item['type'])): ?>
                                                        <div class="text-muted small">Type: <?php echo htmlspecialchars($item['type']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($item['slot'])): ?>
                                                        <div class="text-muted small">Slot: <?php echo htmlspecialchars($item['slot']); ?></div>
                                                    <?php endif; ?>
                                                    <?php if (isset($item['info']) && is_array($item['info']) && !empty($item['info'])): ?>
                                                        <div class="mt-2">
                                                            <div class="text-muted small">Item Info:</div>
                                                            <div class="text-muted small ps-2">
                                                                <?php foreach ($item['info'] as $key => $value): ?>
                                                                    <?php if (!is_array($value)): ?>
                                                                        <div>- <?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?></div>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No inventory items found for this player.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Vehicles Tab -->
                            <div class="tab-pane fade" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab">
                                <h6 class="mb-3">Player Vehicles</h6>
                                <?php if ($playerDetails['vehicles'] && is_array($playerDetails['vehicles']) && !empty($playerDetails['vehicles'])): ?>
                                    <div class="row">
                                        <?php foreach ($playerDetails['vehicles'] as $vehicle): ?>
                                            <div class="col-md-6">
                                                <div class="item-card">
                                                    <h6><?php echo isset($vehicle['name']) ? htmlspecialchars($vehicle['name']) : 'Unknown Vehicle'; ?></h6>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <span class="vehicle-plate me-2"><?php echo isset($vehicle['plate']) ? htmlspecialchars($vehicle['plate']) : 'N/A'; ?></span>
                                                        <?php if (isset($vehicle['state'])): ?>
                                                            <span class="badge <?php echo $vehicle['state'] == 1 ? 'bg-danger' : 'bg-success'; ?>">
                                                                <?php echo $vehicle['state'] == 1 ? 'Out' : 'In Garage'; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (isset($vehicle['garage'])): ?>
                                                        <div class="text-muted small">Garage: <?php echo htmlspecialchars($vehicle['garage']); ?></div>
                                                    <?php endif; ?>
                                                    <div class="row mt-2">
                                                        <?php if (isset($vehicle['fuel'])): ?>
                                                            <div class="col-4">
                                                                <div class="text-muted small">Fuel</div>
                                                                <div class="progress" style="height: 5px;">
                                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $vehicle['fuel']; ?>%" aria-valuenow="<?php echo $vehicle['fuel']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <div class="small"><?php echo $vehicle['fuel']; ?>%</div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (isset($vehicle['engine'])): ?>
                                                            <div class="col-4">
                                                                <div class="text-muted small">Engine</div>
                                                                <div class="progress" style="height: 5px;">
                                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $vehicle['engine']; ?>%" aria-valuenow="<?php echo $vehicle['engine']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <div class="small"><?php echo $vehicle['engine']; ?>%</div>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (isset($vehicle['body'])): ?>
                                                            <div class="col-4">
                                                                <div class="text-muted small">Body</div>
                                                                <div class="progress" style="height: 5px;">
                                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $vehicle['body']; ?>%" aria-valuenow="<?php echo $vehicle['body']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                                <div class="small"><?php echo $vehicle['body']; ?>%</div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No vehicles found for this player.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Metadata Tab -->
                            <div class="tab-pane fade" id="metadata" role="tabpanel" aria-labelledby="metadata-tab">
                                <h6 class="mb-3">Player Metadata</h6>
                                <?php if ($playerDetails['metadata'] && is_array($playerDetails['metadata']) && !empty($playerDetails['metadata'])): ?>
                                    <div class="row">
                                        <?php foreach ($playerDetails['metadata'] as $key => $value): ?>
                                            <?php if (is_array($value)): ?>
                                                <div class="col-md-12 mb-3">
                                                    <div class="card bg-dark">
                                                        <div class="card-header">
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($key); ?></h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <pre class="bg-dark text-light p-3 rounded"><?php echo json_encode($value, JSON_PRETTY_PRINT); ?></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="item-card">
                                                        <div class="text-muted small"><?php echo htmlspecialchars($key); ?></div>
                                                        <div><?php echo is_string($value) ? htmlspecialchars($value) : (is_bool($value) ? ($value ? 'true' : 'false') : $value); ?></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No metadata found for this player.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
