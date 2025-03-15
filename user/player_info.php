<?php
// Include initialization file
require_once '../config/init.php';

// Require login
requireLogin('../login.php');

// Get user's citizen ID from database
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);
if (!$userData || !isset($userData['player_id']) || empty($userData['player_id'])) {
    // If user has no linked player, show appropriate message
    $hasPlayerData = false;
    $playerData = false;
    $charInfo = false;
    $money = false;
    $job = false;
    $inventory = false;
    $metadata = false;
    $vehicles = false;
    $lastLogin = false;
} else {
    $hasPlayerData = true;
    
    // Get player data using player_id
    $player = new Player();
    $playerById = $player->getPlayerById($userData['player_id']);
    if ($playerById && isset($playerById['citizenid'])) {
        $citizenid = $playerById['citizenid'];
        $playerData = $player->getPlayerByCitizenId($citizenid);
        $charInfo = $player->getPlayerCharInfo($citizenid);
        $money = $player->getPlayerMoney($citizenid);
        $job = $player->getPlayerJob($citizenid);
        $inventory = $player->getPlayerInventory($citizenid);
        $metadata = $player->getPlayerMetadata($citizenid);
        $vehicles = $player->getPlayerVehicles($citizenid);
        $lastLogin = $player->getLastLoginTime($citizenid);
    } else {
        $hasPlayerData = false;
        $playerData = false;
        $charInfo = false;
        $money = false;
        $job = false;
        $inventory = false;
        $metadata = false;
        $vehicles = false;
        $lastLogin = false;
    }
}

// Format the player data for display
$formattedInventory = [];
if ($inventory && is_array($inventory)) {
    // For inventory items, check if they're in the standard QBCore format
    // Inventory might be in different formats: 
    // 1. {slot: {name, count, ...}} - Indexed by slot number
    // 2. [{ slot, name, count, ...}] - Array of items with slot property
    
    // Handle item formatting based on inventory structure
    foreach ($inventory as $slot => $item) {
        // Skip empty slots
        if (empty($item) || (is_array($item) && empty($item['name']))) {
            continue;
        }
        
        if (is_numeric($slot) && is_array($item)) {
            // Format 1: slot is the key, item is value
            $formattedItem = [
                'slot' => $slot,
                'name' => isset($item['name']) ? $item['name'] : 'Unknown Item',
                'amount' => isset($item['count']) ? $item['count'] : (isset($item['amount']) ? $item['amount'] : 1),
                'type' => isset($item['type']) ? $item['type'] : 'item',
                'weight' => isset($item['weight']) ? $item['weight'] : 0,
                'info' => isset($item['info']) ? $item['info'] : []
            ];
            $formattedInventory[] = $formattedItem;
        } else if (isset($item['slot']) && isset($item['name'])) {
            // Format 2: item has slot property
            $formattedItem = [
                'slot' => $item['slot'],
                'name' => $item['name'],
                'amount' => isset($item['count']) ? $item['count'] : (isset($item['amount']) ? $item['amount'] : 1),
                'type' => isset($item['type']) ? $item['type'] : 'item',
                'weight' => isset($item['weight']) ? $item['weight'] : 0,
                'info' => isset($item['info']) ? $item['info'] : []
            ];
            $formattedInventory[] = $formattedItem;
        } else if (isset($item['name'])) {
            // Fallback for other formats
            $formattedItem = [
                'slot' => $slot,
                'name' => $item['name'],
                'amount' => isset($item['count']) ? $item['count'] : (isset($item['amount']) ? $item['amount'] : 1),
                'type' => isset($item['type']) ? $item['type'] : 'item',
                'weight' => isset($item['weight']) ? $item['weight'] : 0,
                'info' => isset($item['info']) ? $item['info'] : []
            ];
            $formattedInventory[] = $formattedItem;
        }
    }
}

// Page title
$pageTitle = 'Player Details - ' . getSetting('site_name', 'FiveM Server Dashboard');
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
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: #1e1e1e;
            min-height: 100vh;
            padding: 1rem;
        }
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            padding: 1rem 0;
            border-bottom: 1px solid #2d2d2d;
            margin-bottom: 1rem;
        }
        .sidebar-nav {
            list-style: none;
            padding: 0;
        }
        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }
        .sidebar-nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: #a0aec0;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: #2d2d2d;
            color: #f8f9fa;
        }
        .sidebar-nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        .main-content {
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .card {
            background-color: #1e1e1e;
            border: none;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid #2d2d2d;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body {
            padding: 1.5rem;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4f46e5;
        }
        .character-info {
            margin-left: 2rem;
        }
        .badge-job {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
        }
        .money-card {
            border-left: 4px solid;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #2d2d2d;
        }
        .money-cash {
            border-color: #10b981;
        }
        .money-bank {
            border-color: #3b82f6;
        }
        .money-crypto {
            border-color: #f59e0b;
        }
        .money-label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            color: #a0aec0;
        }
        .money-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .tab-content {
            padding-top: 1.5rem;
        }
        .nav-tabs {
            border-bottom-color: #2d2d2d;
        }
        .nav-tabs .nav-link {
            color: #a0aec0;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            margin-right: 0.25rem;
        }
        .nav-tabs .nav-link:hover {
            color: #f8f9fa;
            background-color: #2d2d2d;
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #f8f9fa;
            background-color: #2d2d2d;
            border-color: transparent;
        }
        .table {
            color: #f8f9fa;
        }
        .table th {
            border-color: #2d2d2d;
        }
        .table td {
            border-color: #2d2d2d;
        }
        .inventory-item {
            background-color: #2d2d2d;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .inventory-item-icon {
            width: 48px;
            height: 48px;
            background-color: #4f46e5;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .inventory-item-details {
            flex: 1;
        }
        .inventory-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .inventory-item-slot {
            font-family: monospace;
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            background-color: #1e1e1e;
            border-radius: 0.25rem;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .inventory-item-amount {
            font-weight: 700;
            background-color: #4f46e5;
            color: white;
            padding: 0.125rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        .vehicle-card {
            background-color: #2d2d2d;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        .vehicle-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .vehicle-plate {
            font-family: monospace;
            font-size: 1rem;
            padding: 0.25rem 0.5rem;
            background-color: #4f46e5;
            border-radius: 0.25rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .vehicle-state {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
        .vehicle-state-out {
            background-color: #ef4444;
        }
        .vehicle-state-garage {
            background-color: #10b981;
        }
        .vehicle-details {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #a0aec0;
        }
        .progress {
            height: 0.5rem;
            background-color: #1e1e1e;
            margin-top: 0.25rem;
        }
        .metadata-item {
            background-color: #2d2d2d;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .metadata-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .metadata-value {
            font-family: monospace;
            background-color: #1e1e1e;
            padding: 0.5rem;
            border-radius: 0.25rem;
            overflow-x: auto;
            max-height: 200px;
            overflow-y: auto;
        }
        .badge-count {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            background-color: #4f46e5;
            color: white;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="sidebar-brand">
                    <a href="../index.php" class="text-decoration-none text-white">
                        <i class="fas fa-user-circle"></i> User Panel
                    </a>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="index.php" class="sidebar-nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="player_info.php" class="sidebar-nav-link active">
                            <i class="fas fa-info-circle"></i> Player Details
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="profile.php" class="sidebar-nav-link">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="sidebar-nav-item mt-4">
                        <a href="../admin/index.php" class="sidebar-nav-link">
                            <i class="fas fa-user-shield"></i> Admin Dashboard
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
                <!-- Page Header -->
                <div class="page-header">
                    <h1>Player Details</h1>
                    <p class="text-muted">View your character information and stats</p>
                </div>

                <!-- Flash Messages -->
                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!$hasPlayerData): ?>
                <!-- No Player Data Alert -->
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>No Player Data Found</h4>
                    <p>We couldn't find any game character associated with your account. This could be because:</p>
                    <ul>
                        <li>You have not played on the server yet</li>
                        <li>Your account hasn't been linked to your in-game character</li>
                    </ul>
                    <hr>
                    <p class="mb-0">Please join the server and create a character, or contact an administrator if you believe this is an error.</p>
                </div>
                <?php else: ?>
                <!-- Character Info -->
                <div class="card">
                    <div class="card-header section-header card-header-character">
                        <i class="fas fa-user me-2"></i> Character Information
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-center">
                            <img src="../assets/img/avatar-placeholder.jpg" alt="Character Avatar" class="profile-image">
                            <div class="character-info mt-3 mt-md-0">
                                <h2><?php echo ($charInfo && isset($charInfo['firstname'])) ? $charInfo['firstname'] . ' ' . $charInfo['lastname'] : 'Unknown Character'; ?></h2>
                                <p><strong>Citizen ID:</strong> <?php echo $citizenid ?? 'Unknown'; ?></p>
                                <p><strong>Phone:</strong> <?php echo ($charInfo && isset($charInfo['phone'])) ? $charInfo['phone'] : 'N/A'; ?></p>
                                <p><strong>Gender:</strong> <?php echo ($charInfo && isset($charInfo['gender'])) ? ($charInfo['gender'] == 0 ? 'Male' : 'Female') : 'Unknown'; ?></p>
                                <p><strong>Birth Date:</strong> <?php echo ($charInfo && isset($charInfo['birthdate'])) ? $charInfo['birthdate'] : 'N/A'; ?></p>
                                <p>
                                    <strong>Job:</strong> 
                                    <?php if ($job && isset($job['label'])): ?>
                                    <span class="badge badge-job" style="background-color: #4f46e5;">
                                        <?php echo $job['label'] . (isset($job['grade']['name']) ? ' - ' . $job['grade']['name'] : ''); ?>
                                    </span>
                                    <?php else: ?>
                                    <span>-</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Last Login:</strong> <?php echo $lastLogin ? formatDatetime($lastLogin, 'F j, Y, g:i a') : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Money Info -->
                <div class="card mt-4">
                    <div class="card-header section-header card-header-financial">
                        <i class="fas fa-money-bill-wave me-2"></i> Financial Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="money-card money-cash">
                                    <div class="money-label">
                                        <i class="fas fa-wallet me-1"></i> Cash
                                    </div>
                                    <div class="money-value">
                                        <?php echo ($money && isset($money['cash'])) ? formatMoney($money['cash']) : '$0.00'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="money-card money-bank">
                                    <div class="money-label">
                                        <i class="fas fa-university me-1"></i> Bank
                                    </div>
                                    <div class="money-value">
                                        <?php echo ($money && isset($money['bank'])) ? formatMoney($money['bank']) : '$0.00'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="money-card money-crypto">
                                    <div class="money-label">
                                        <i class="fas fa-coins me-1"></i> Crypto
                                    </div>
                                    <div class="money-value">
                                        <?php echo ($money && isset($money['crypto'])) ? formatMoney($money['crypto'], 'Ͼ') : 'Ͼ0.00'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Information Tabs -->
                <div class="card mt-4">
                    <div class="card-header section-header">
                        <ul class="nav nav-tabs card-header-tabs" id="playerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab" aria-controls="inventory" aria-selected="true">
                                    Inventory
                                    <span class="badge-count"><?php echo is_array($formattedInventory) ? count($formattedInventory) : 0; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-controls="vehicles" aria-selected="false">
                                    Vehicles
                                    <span class="badge-count"><?php echo is_array($vehicles) ? count($vehicles) : 0; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-controls="job" aria-selected="false">
                                    Job Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" data-bs-target="#metadata" type="button" role="tab" aria-controls="metadata" aria-selected="false">
                                    Metadata
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="playerTabContent">
                            <!-- Inventory Tab -->
                            <div class="tab-pane fade show active" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                <?php if (is_array($formattedInventory) && count($formattedInventory) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($formattedInventory as $item): ?>
                                        <div class="col-md-6">
                                            <div class="inventory-item">
                                                <div class="inventory-item-icon">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                                <div class="inventory-item-details">
                                                    <div>
                                                        <span class="inventory-item-slot"><?php echo isset($item['slot']) ? $item['slot'] : '?'; ?></span>
                                                        <span class="inventory-item-name"><?php echo isset($item['name']) ? $item['name'] : 'Unknown Item'; ?></span>
                                                        <span class="inventory-item-amount">x<?php echo isset($item['amount']) ? $item['amount'] : 1; ?></span>
                                                    </div>
                                                    <div class="text-muted mt-1">
                                                        <small>Type: <?php echo isset($item['type']) ? ucfirst($item['type']) : 'Item'; ?></small>
                                                        <?php if (isset($item['weight']) && $item['weight'] > 0): ?>
                                                        <small class="ms-2">Weight: <?php echo $item['weight']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (isset($item['info']) && !empty($item['info']) && is_array($item['info'])): ?>
                                                    <div class="mt-2">
                                                        <small class="d-block text-muted mb-1">Item info:</small>
                                                        <?php foreach ($item['info'] as $key => $value): ?>
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
                                        <i class="fas fa-info-circle me-2"></i> Your inventory is empty.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Vehicles Tab -->
                            <div class="tab-pane fade" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab">
                                <?php if (is_array($vehicles) && count($vehicles) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($vehicles as $vehicle): ?>
                                        <div class="col-md-6">
                                            <div class="vehicle-card">
                                                <div class="vehicle-name">
                                                    <?php echo isset($vehicle['name']) ? $vehicle['name'] : 'Unknown Vehicle'; ?>
                                                </div>
                                                <div>
                                                    <?php if (isset($vehicle['plate'])): ?>
                                                    <span class="vehicle-plate"><?php echo $vehicle['plate']; ?></span>
                                                    <?php endif; ?>
                                                    <?php if (isset($vehicle['state'])): ?>
                                                    <span class="vehicle-state <?php echo $vehicle['state'] == 1 ? 'vehicle-state-out' : 'vehicle-state-garage'; ?>">
                                                        <?php echo $vehicle['state'] == 1 ? 'Out' : 'In Garage'; ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (isset($vehicle['garage']) || isset($vehicle['fuel']) || isset($vehicle['engine']) || isset($vehicle['body'])): ?>
                                                <div class="vehicle-details mt-3">
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
                                        <i class="fas fa-info-circle me-2"></i> You don't have any vehicles yet.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Job Tab -->
                            <div class="tab-pane fade" id="job" role="tabpanel" aria-labelledby="job-tab">
                                <?php if ($job && isset($job['label'])): ?>
                                <div class="card bg-dark mb-4">
                                    <div class="card-body">
                                        <h4 class="card-title"><?php echo $job['label']; ?></h4>
                                        <?php if (isset($job['grade']) && isset($job['grade']['name'])): ?>
                                        <div class="mb-3">
                                            <span class="badge badge-job" style="background-color: #4f46e5;">
                                                <?php echo $job['grade']['name']; ?> 
                                                <?php if (isset($job['grade']['level'])): ?>
                                                (Level <?php echo $job['grade']['level']; ?>)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h5>Job Details</h5>
                                                <table class="table table-dark table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>Job Name:</th>
                                                            <td><?php echo isset($job['name']) ? $job['name'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Job Label:</th>
                                                            <td><?php echo $job['label']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Payment Account:</th>
                                                            <td><?php echo isset($job['payment']) ? $job['payment'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>On Duty:</th>
                                                            <td><?php echo isset($job['onduty']) && $job['onduty'] ? 'Yes' : 'No'; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php if (isset($job['grade'])): ?>
                                            <div class="col-md-6">
                                                <h5>Grade Details</h5>
                                                <table class="table table-dark table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>Grade Level:</th>
                                                            <td><?php echo isset($job['grade']['level']) ? $job['grade']['level'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Grade Name:</th>
                                                            <td><?php echo isset($job['grade']['name']) ? $job['grade']['name'] : 'N/A'; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Salary:</th>
                                                            <td><?php echo isset($job['grade']['payment']) ? formatMoney($job['grade']['payment']) : '$0.00'; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No job information available.
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Metadata Tab -->
                            <div class="tab-pane fade" id="metadata" role="tabpanel" aria-labelledby="metadata-tab">
                                <?php if ($metadata && is_array($metadata) && !empty($metadata)): ?>
                                    <div class="row">
                                        <?php foreach ($metadata as $key => $value): ?>
                                            <?php if (!empty($value) && !is_array($value)): ?>
                                            <div class="col-md-6">
                                                <div class="metadata-item">
                                                    <div class="metadata-label"><?php echo ucfirst(str_replace('_', ' ', $key)); ?></div>
                                                    <div class="metadata-value"><?php echo $value; ?></div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No metadata available.
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
    <script src="../assets/js/main.js"></script>
</body>
</html> 