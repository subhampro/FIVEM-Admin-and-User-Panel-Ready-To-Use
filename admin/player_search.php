<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges (level 1 is enough for this page)
requireAdmin('admin_level1');

// Initialize
$logger = new Logger();
$player = new Player();

// Search results
$results = [];
$searchPerformed = false;
$searchTerm = '';
$searchField = 'all';
$playerData = null;
$selectedPlayer = null;

// Process search
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if search term is available
    if (isset($_POST['search_term'])) {
        // Check CSRF token
        if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid form submission. Please try again.');
            redirect('player_search.php');
        }
        
        $searchTerm = sanitize($_POST['search_term']);
        $searchField = isset($_POST['search_field']) ? sanitize($_POST['search_field']) : 'all';
        
        if (!empty($searchTerm)) {
            // Perform search based on field
            $results = $player->searchPlayers($searchTerm, $searchField);
            $searchPerformed = true;
            
            // Log the search
            $logger->logAction(
                $_SESSION['user_id'], 
                'player_search', 
                "Searched for players with term: '$searchTerm' in field: '$searchField'"
            );
        }
    }
}

// Direct search from URL parameter
else if (isset($_GET['direct_search']) && !empty($_GET['direct_search'])) {
    $searchTerm = sanitize($_GET['direct_search']);
    $searchField = 'all';
    
    // Perform search
    $results = $player->searchPlayers($searchTerm, $searchField);
    $searchPerformed = true;
    
    // Log the search
    $logger->logAction(
        $_SESSION['user_id'], 
        'player_search', 
        "Direct search for term: '$searchTerm'"
    );
}

// View detailed player data
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $citizenid = sanitize($_GET['id']);
    $selectedPlayer = $player->getPlayerByCitizenId($citizenid);
    
    if ($selectedPlayer) {
        // Get all player data
        $charInfo = $player->getPlayerCharInfo($citizenid);
        $money = $player->getPlayerMoney($citizenid);
        $job = $player->getPlayerJob($citizenid);
        $inventory = $player->getPlayerInventory($citizenid);
        $metadata = $player->getPlayerMetadata($citizenid);
        $vehicles = $player->getPlayerVehicles($citizenid);
        $lastLogin = $player->getLastLoginTime($citizenid);
        
        // Format player data for display
        $playerData = [
            'basic' => $selectedPlayer,
            'charInfo' => $charInfo,
            'money' => $money,
            'job' => $job,
            'inventory' => $inventory,
            'metadata' => $metadata,
            'vehicles' => $vehicles,
            'lastLogin' => $lastLogin
        ];
        
        // Log the view
        $logger->logAction(
            $_SESSION['user_id'], 
            'view_player', 
            "Viewed detailed player data for: '$citizenid'"
        );
    }
}

// Page title
$pageTitle = 'Player Search - Admin Dashboard';
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
        .inventory-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--dark-border);
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: var(--dark-card-bg);
        }
        .inventory-item-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark-hover);
            border-radius: 4px;
            margin-right: 15px;
        }
        .inventory-item-details {
            flex: 1;
        }
        .inventory-item-slot {
            background-color: var(--accent-color);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-right: 8px;
        }
        .inventory-item-name {
            font-weight: 600;
        }
        .inventory-item-amount {
            float: right;
            background-color: var(--success-color);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .vehicle-card {
            border: 1px solid var(--dark-border);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: var(--dark-card-bg);
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }
        .money-card {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            color: white;
        }
        .money-cash {
            background-color: #10b981;
        }
        .money-bank {
            background-color: #3b82f6;
        }
        .money-crypto {
            background-color: #8b5cf6;
        }
        .money-label {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .money-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .metadata-item {
            background-color: var(--dark-input-bg);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .metadata-value {
            background-color: var(--dark-card-bg);
            padding: 10px;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            font-family: monospace;
        }
        .search-header {
            background-color: var(--dark-card-bg);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
            z-index: 100;
        }
        .form-control, .form-select, .input-group {
            position: relative;
            z-index: 101;
        }
        .result-item {
            transition: all 0.3s ease;
        }
        .result-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .nav-tabs .nav-link {
            color: var(--dark-text) !important;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--accent-color) !important;
            color: white !important;
            border-color: var(--accent-color) !important;
        }
    </style>
</head>
<body class="admin-panel">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar for admin -->
        <div class="col-md-3 col-lg-2 px-0 sidebar" style="z-index: 10; position: relative;">
            <div class="sidebar-brand">
                <a href="index.php" class="text-decoration-none text-white">
                    <i class="fas fa-user-shield"></i> Admin Panel
                </a>
            </div>
            <ul class="sidebar-nav">
                <?php if (isAdmin('admin_level1')): ?>
                <li class="sidebar-nav-item">
                    <a href="player_search.php" class="sidebar-nav-link active">
                        <i class="fas fa-search"></i> Player Search
                    </a>
                </li>
                <?php endif; ?>

                <?php if (isAdmin('admin_level2')): ?>
                <!-- Level 2 Admin Options -->
                <li class="sidebar-nav-item">
                    <a href="players.php" class="sidebar-nav-link">
                        <i class="fas fa-users"></i> Manage Players
                    </a>
                </li>
                <?php endif; ?>

                <?php if (isAdmin('admin_level3')): ?>
                <!-- Level 3 Admin Options -->
                <li class="sidebar-nav-item">
                    <a href="users.php" class="sidebar-nav-link">
                        <i class="fas fa-user-cog"></i> User Management
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="settings.php" class="sidebar-nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <?php endif; ?>

                <li class="sidebar-nav-item">
                    <a href="../logout.php" class="sidebar-nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <div class="container mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Player Search</h1>
                    <div>
                        <a href="player_search.php?direct_search=L18" class="btn btn-info">
                            <i class="fas fa-search"></i> Quick Search "L18"
                        </a>
                    </div>
                </div>
                
                <!-- DEBUG MESSAGE -->
                <div class="alert alert-warning mb-4">
                    <h5><i class="fas fa-info-circle me-2"></i> Important Note</h5>
                    <p>Use the search form below to search for players. This is a new simplified search that should work properly.</p>
                    <p>Search for player name, CSN, license or any identifying information to find matching players.</p>
                </div>
                
                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?>" role="alert">
                    <?php echo $flashMessage['message']; ?>
                </div>
                <?php endif; ?>
                
                <!-- Search Form -->
                <div class="card search-header" style="display: none;"><!-- Hidden old form -->
                    <!-- Alternative simple search -->
                    <div class="mb-4">
                        <h5 class="text-center mb-3">Quick Search</h5>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="player_search.php?direct_search=L" class="btn btn-sm btn-info">Search "L"</a>
                            <a href="player_search.php?direct_search=CSN" class="btn btn-sm btn-info">Search "CSN"</a>
                            <a href="player_search.php?direct_search=John" class="btn btn-sm btn-info">Search "John"</a>
                            <a href="player_search.php?direct_search=license" class="btn btn-sm btn-info">Search "license"</a>
                        </div>
                    </div>
                    
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Search for players..." value="<?php echo $searchTerm; ?>" style="z-index: 9999 !important; position: relative; pointer-events: auto !important;" required>
                                    <button type="submit" name="search" class="btn btn-primary" style="z-index: 9999 !important; position: relative; pointer-events: auto !important;">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="search_field" name="search_field">
                                    <option value="all" <?php echo $searchField === 'all' ? 'selected' : ''; ?>>All Fields</option>
                                    <option value="citizenid" <?php echo $searchField === 'citizenid' ? 'selected' : ''; ?>>Citizen ID</option>
                                    <option value="name" <?php echo $searchField === 'name' ? 'selected' : ''; ?>>Character Name</option>
                                    <option value="license" <?php echo $searchField === 'license' ? 'selected' : ''; ?>>License</option>
                                    <option value="phone" <?php echo $searchField === 'phone' ? 'selected' : ''; ?>>Phone Number</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="player_search.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- GET Form Alternative (in case POST form isn't working) -->
                <div class="mt-3 mb-4 p-3 border border-warning rounded">
                    <h5 class="text-center mb-3">Alternative Search Method</h5>
                    <form method="get" action="">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="direct_search" name="direct_search" placeholder="Type search term here..." value="<?php echo isset($_GET['direct_search']) ? htmlspecialchars($_GET['direct_search']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-warning w-100">Search Now</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- SUPER SIMPLE SEARCH FORM - GUARANTEED TO WORK -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Search Players</h5>
                        
                        <!-- Simple GET form -->
                        <form method="get" action="" style="margin:0; padding:0;">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="direct_search" class="form-label">Enter search term (name, CSN, license, etc)</label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg" 
                                        id="direct_search" 
                                        name="direct_search" 
                                        placeholder="Type player name, CSN, license..." 
                                        value="<?php echo isset($_GET['direct_search']) ? htmlspecialchars($_GET['direct_search']) : ''; ?>"
                                    >
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-lg btn-primary w-100">
                                        <i class="fas fa-search me-2"></i> Search Players
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Quick search examples -->
                        <div class="mt-3">
                            <span class="me-2">Examples:</span>
                            <a href="player_search.php?direct_search=L" class="btn btn-sm btn-outline-info me-1">L</a>
                            <a href="player_search.php?direct_search=CSN" class="btn btn-sm btn-outline-info me-1">CSN</a>
                            <a href="player_search.php?direct_search=John" class="btn btn-sm btn-outline-info me-1">John</a>
                            <a href="player_search.php?direct_search=license" class="btn btn-sm btn-outline-info me-1">license</a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <?php if ($searchPerformed): ?>
                        <?php if (empty($results)): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No players found matching your search criteria.
                                </div>
                                <!-- Debug info -->
                                <div class="alert alert-secondary">
                                    <h5>Debug Info</h5>
                                    <p>Search term: <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong></p>
                                    <p>Search method: <?php echo isset($_GET['direct_search']) ? 'GET (direct_search)' : 'POST'; ?></p>
                                    <p>Check the error logs for more details.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i> Found <?php echo count($results); ?> players matching your search criteria.
                                </div>
                            </div>
                            
                            <?php foreach ($results as $result): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card result-item">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?php 
                                                $charinfo = json_decode($result['charinfo'] ?? '{}', true);
                                                $name = isset($charinfo['firstname']) && isset($charinfo['lastname']) 
                                                    ? $charinfo['firstname'] . ' ' . $charinfo['lastname'] 
                                                    : ($result['name'] ?? 'Unknown');
                                                echo htmlspecialchars($name);
                                                ?>
                                            </h5>
                                            <p class="card-text">
                                                <strong>Citizen ID:</strong> <?php echo htmlspecialchars($result['citizenid'] ?? 'N/A'); ?><br>
                                                <strong>Phone:</strong> <?php echo isset($charinfo['phone']) ? htmlspecialchars($charinfo['phone']) : 'N/A'; ?><br>
                                                <strong>Job:</strong> <?php 
                                                $job = json_decode($result['job'] ?? '{}', true);
                                                echo isset($job['label']) ? htmlspecialchars($job['label']) : 'N/A'; 
                                                ?>
                                            </p>
                                            <a href="player_search.php?id=<?php echo urlencode($result['citizenid']); ?>" class="btn btn-primary w-100">
                                                <i class="fas fa-info-circle"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php elseif ($selectedPlayer): ?>
                        <!-- Single Player View - Already loaded data above -->
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Enter search criteria and click Search to find players.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($selectedPlayer && $playerData): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2"></i> 
                                        Player Details: 
                                        <?php 
                                        $charInfo = $playerData['charInfo'];
                                        echo ($charInfo && isset($charInfo['firstname'])) 
                                            ? $charInfo['firstname'] . ' ' . $charInfo['lastname'] 
                                            : 'Unknown Character'; 
                                        ?>
                                    </h5>
                                    <a href="player_search.php" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Search
                                    </a>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="playerDetailsTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Info</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="false">Inventory</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-controls="vehicles" aria-selected="false">Vehicles</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" data-bs-target="#metadata" type="button" role="tab" aria-controls="metadata" aria-selected="false">Metadata</button>
                                        </li>
                                    </ul>
                                    
                                    <div class="tab-content mt-3" id="playerDetailsContent">
                                        <!-- Basic Info Tab -->
                                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex flex-column flex-md-row align-items-center">
                                                        <img src="../assets/img/avatar-placeholder.jpg" alt="Character Avatar" class="profile-image">
                                                        <div class="character-info mt-3 mt-md-0">
                                                            <h3><?php echo ($charInfo && isset($charInfo['firstname'])) ? $charInfo['firstname'] . ' ' . $charInfo['lastname'] : 'Unknown Character'; ?></h3>
                                                            <p><strong>Citizen ID:</strong> <?php echo $selectedPlayer['citizenid'] ?? 'Unknown'; ?></p>
                                                            <p><strong>License:</strong> <?php echo $selectedPlayer['license'] ?? 'N/A'; ?></p>
                                                            <p><strong>Phone:</strong> <?php echo ($charInfo && isset($charInfo['phone'])) ? $charInfo['phone'] : 'N/A'; ?></p>
                                                            <p><strong>Gender:</strong> <?php echo ($charInfo && isset($charInfo['gender'])) ? ($charInfo['gender'] == 0 ? 'Male' : 'Female') : 'Unknown'; ?></p>
                                                            <p><strong>Birth Date:</strong> <?php echo ($charInfo && isset($charInfo['birthdate'])) ? $charInfo['birthdate'] : 'N/A'; ?></p>
                                                            <p>
                                                                <strong>Job:</strong> 
                                                                <?php 
                                                                $job = $playerData['job'];
                                                                if ($job && isset($job['label'])): 
                                                                ?>
                                                                <span class="badge bg-primary">
                                                                    <?php echo $job['label'] . (isset($job['grade']['name']) ? ' - ' . $job['grade']['name'] : ''); ?>
                                                                </span>
                                                                <?php else: ?>
                                                                <span>-</span>
                                                                <?php endif; ?>
                                                            </p>
                                                            <p><strong>Last Login:</strong> <?php echo $playerData['lastLogin'] ? formatDatetime($playerData['lastLogin'], 'F j, Y, g:i a') : 'N/A'; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <h4 class="mb-3">Financial Information</h4>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="money-card money-cash">
                                                                <div class="money-label">
                                                                    <i class="fas fa-wallet me-1"></i> Cash
                                                                </div>
                                                                <div class="money-value">
                                                                    <?php echo ($playerData['money'] && isset($playerData['money']['cash'])) ? formatMoney($playerData['money']['cash']) : '$0.00'; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="money-card money-bank">
                                                                <div class="money-label">
                                                                    <i class="fas fa-university me-1"></i> Bank
                                                                </div>
                                                                <div class="money-value">
                                                                    <?php echo ($playerData['money'] && isset($playerData['money']['bank'])) ? formatMoney($playerData['money']['bank']) : '$0.00'; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="money-card money-crypto">
                                                                <div class="money-label">
                                                                    <i class="fas fa-coins me-1"></i> Crypto
                                                                </div>
                                                                <div class="money-value">
                                                                    <?php echo ($playerData['money'] && isset($playerData['money']['crypto'])) ? formatMoney($playerData['money']['crypto'], 'Ͼ') : 'Ͼ0.00'; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <h4 class="mb-3 mt-4">Database Info</h4>
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th style="width: 40%;">Database ID</th>
                                                            <td><?php echo $selectedPlayer['id'] ?? 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Citizen ID</th>
                                                            <td><?php echo $selectedPlayer['citizenid'] ?? 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>License</th>
                                                            <td class="text-break"><?php echo $selectedPlayer['license'] ?? 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Steam ID</th>
                                                            <td><?php echo $selectedPlayer['steam'] ?? 'N/A'; ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Inventory Tab -->
                                        <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                            <?php
                                            $inventory = $playerData['inventory'];
                                            if ($inventory && is_array($inventory) && !empty($inventory)):
                                            ?>
                                                <div class="row">
                                                    <?php foreach ($inventory as $slot => $item): ?>
                                                        <?php
                                                        // Skip empty slots
                                                        if (empty($item) || (is_array($item) && empty($item['name']))) {
                                                            continue;
                                                        }
                                                        
                                                        // Format item data
                                                        $itemName = isset($item['name']) ? $item['name'] : 'Unknown Item';
                                                        $itemAmount = isset($item['count']) ? $item['count'] : (isset($item['amount']) ? $item['amount'] : 1);
                                                        $itemType = isset($item['type']) ? $item['type'] : 'item';
                                                        $itemWeight = isset($item['weight']) ? $item['weight'] : 0;
                                                        $itemInfo = isset($item['info']) ? $item['info'] : [];
                                                        $itemSlot = is_numeric($slot) ? $slot : (isset($item['slot']) ? $item['slot'] : '?');
                                                        ?>
                                                        <div class="col-md-6">
                                                            <div class="inventory-item">
                                                                <div class="inventory-item-icon">
                                                                    <i class="fas fa-box"></i>
                                                                </div>
                                                                <div class="inventory-item-details">
                                                                    <div>
                                                                        <span class="inventory-item-slot"><?php echo $itemSlot; ?></span>
                                                                        <span class="inventory-item-name"><?php echo $itemName; ?></span>
                                                                        <span class="inventory-item-amount">x<?php echo $itemAmount; ?></span>
                                                                    </div>
                                                                    <div class="text-muted mt-1">
                                                                        <small>Type: <?php echo ucfirst($itemType); ?></small>
                                                                        <?php if ($itemWeight > 0): ?>
                                                                        <small class="ms-2">Weight: <?php echo $itemWeight; ?></small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php if (!empty($itemInfo) && is_array($itemInfo)): ?>
                                                                    <div class="mt-2">
                                                                        <small class="d-block text-muted mb-1">Item info:</small>
                                                                        <?php foreach ($itemInfo as $key => $value): ?>
                                                                            <?php if (!is_array($value)): ?>
                                                                            <small class="d-block text-muted ps-2">- <?php echo ucfirst($key); ?>: <?php echo $value; ?></small>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
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
                                            <?php
                                            $vehicles = $playerData['vehicles'];
                                            if ($vehicles && is_array($vehicles) && !empty($vehicles)):
                                            ?>
                                                <div class="row">
                                                    <?php foreach ($vehicles as $vehicle): ?>
                                                        <div class="col-md-6">
                                                            <div class="vehicle-card">
                                                                <div class="vehicle-name">
                                                                    <?php echo isset($vehicle['name']) ? $vehicle['name'] : 'Unknown Vehicle'; ?>
                                                                </div>
                                                                <div>
                                                                    <?php if (isset($vehicle['plate'])): ?>
                                                                    <span class="badge bg-primary"><?php echo $vehicle['plate']; ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($vehicle['state'])): ?>
                                                                    <span class="badge <?php echo $vehicle['state'] == 1 ? 'bg-danger' : 'bg-success'; ?>">
                                                                        <?php echo $vehicle['state'] == 1 ? 'Out' : 'In Garage'; ?>
                                                                    </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php if (isset($vehicle['garage']) || isset($vehicle['fuel']) || isset($vehicle['engine']) || isset($vehicle['body'])): ?>
                                                                <div class="mt-3">
                                                                    <?php if (isset($vehicle['garage'])): ?>
                                                                    <div class="mb-2">
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>Garage:</span>
                                                                            <span><?php echo $vehicle['garage']; ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($vehicle['fuel'])): ?>
                                                                    <div class="mb-2">
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>Fuel:</span>
                                                                            <span><?php echo $vehicle['fuel']; ?>%</span>
                                                                        </div>
                                                                        <div class="progress">
                                                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $vehicle['fuel']; ?>%" aria-valuenow="<?php echo $vehicle['fuel']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($vehicle['engine'])): ?>
                                                                    <div class="mb-2">
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>Engine:</span>
                                                                            <span><?php echo $vehicle['engine']; ?>%</span>
                                                                        </div>
                                                                        <div class="progress">
                                                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $vehicle['engine']; ?>%" aria-valuenow="<?php echo $vehicle['engine']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($vehicle['body'])): ?>
                                                                    <div>
                                                                        <div class="d-flex justify-content-between">
                                                                            <span>Body:</span>
                                                                            <span><?php echo $vehicle['body']; ?>%</span>
                                                                        </div>
                                                                        <div class="progress">
                                                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $vehicle['body']; ?>%" aria-valuenow="<?php echo $vehicle['body']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php endif; ?>
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
                                            <?php
                                            $metadata = $playerData['metadata'];
                                            if ($metadata && is_array($metadata) && !empty($metadata)):
                                            ?>
                                                <div class="row">
                                                    <?php foreach ($metadata as $key => $value): ?>
                                                        <?php
                                                        // Skip complex nested arrays for readability
                                                        if (is_array($value) && count($value) > 10) {
                                                            $value = '[Complex array with ' . count($value) . ' items]';
                                                        } else if (is_array($value)) {
                                                            $value = json_encode($value, JSON_PRETTY_PRINT);
                                                        }
                                                        ?>
                                                        <div class="col-md-6">
                                                            <div class="metadata-item">
                                                                <div class="metadata-label"><?php echo ucfirst($key); ?></div>
                                                                <div class="metadata-value">
                                                                    <?php echo is_string($value) ? htmlspecialchars($value) : print_r($value, true); ?>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 