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
        $lastLogin = $player->getLastLoginTime($citizenid);
        $vehicles = $player->getPlayerVehicles($citizenid);
    } else {
        $hasPlayerData = false;
    }
}

// Page title
$pageTitle = 'User Dashboard - ' . getSetting('site_name', 'FiveM Server Dashboard');
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
        .stat-card {
            background-color: #1e1e1e;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #4f46e5;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #a0aec0;
            font-size: 1rem;
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
        .profile-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 50%;
            border: 3px solid #4f46e5;
            background-color: rgba(30, 30, 46, 0.8);
            padding: 3px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
        .alert-no-data {
            background-color: #2d2d2d;
            border-color: #4f46e5;
            color: #f8f9fa;
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
                        <a href="index.php" class="sidebar-nav-link active">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="player_info.php" class="sidebar-nav-link">
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
                    <h1>Dashboard</h1>
                    <p class="text-muted">Welcome back, <?php echo $_SESSION['username']; ?>!</p>
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
                <div class="alert alert-info alert-no-data" role="alert">
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
                    <div class="card-header">
                        <i class="fas fa-user me-2"></i> Character Information
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-center">
                            <img src="https://i.imgur.com/4H2c5AB.gif" alt="Character Avatar" class="profile-image">
                            <div class="character-info mt-3 mt-md-0">
                                <h2><?php echo isset($charInfo['firstname']) ? $charInfo['firstname'] . ' ' . $charInfo['lastname'] : 'Unknown Character'; ?></h2>
                                <p><strong>Citizen ID:</strong> <?php echo $citizenid; ?></p>
                                <p><strong>Phone:</strong> <?php echo isset($charInfo['phone']) ? $charInfo['phone'] : 'N/A'; ?></p>
                                <p><strong>Gender:</strong> <?php echo isset($charInfo['gender']) ? ($charInfo['gender'] == 0 ? 'Male' : 'Female') : 'Unknown'; ?></p>
                                <p>
                                    <strong>Job:</strong> 
                                    <?php if (isset($job['label'])): ?>
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
                    <div class="card-header">
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
                                        <?php echo isset($money['cash']) ? formatMoney($money['cash']) : '$0.00'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="money-card money-bank">
                                    <div class="money-label">
                                        <i class="fas fa-university me-1"></i> Bank
                                    </div>
                                    <div class="money-value">
                                        <?php echo isset($money['bank']) ? formatMoney($money['bank']) : '$0.00'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="money-card money-crypto">
                                    <div class="money-label">
                                        <i class="fas fa-coins me-1"></i> Crypto
                                    </div>
                                    <div class="money-value">
                                        <?php echo isset($money['crypto']) ? formatMoney($money['crypto'], 'Ͼ') : 'Ͼ0.00'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicles -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-car me-2"></i> Your Vehicles
                    </div>
                    <div class="card-body">
                        <?php if ($vehicles && count($vehicles) > 0): ?>
                            <div class="row">
                                <?php foreach ($vehicles as $vehicle): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="vehicle-card">
                                        <div class="vehicle-name">
                                            <?php echo isset($vehicle['name']) ? $vehicle['name'] : 'Unknown Vehicle'; ?>
                                        </div>
                                        <?php if (isset($vehicle['plate'])): ?>
                                        <div class="vehicle-plate">
                                            <?php echo $vehicle['plate']; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($vehicle['state'])): ?>
                                        <span class="vehicle-state <?php echo $vehicle['state'] == 'out' ? 'vehicle-state-out' : 'vehicle-state-garage'; ?>">
                                            <?php echo $vehicle['state'] == 'out' ? 'Out' : 'In Garage'; ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if (isset($vehicle['garage'])): ?>
                                        <div class="vehicle-details">
                                            Garage: <?php echo $vehicle['garage']; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You don't have any vehicles yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="fas fa-link me-2"></i> Quick Links
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="player_info.php" class="btn btn-outline-light w-100">
                                    <i class="fas fa-info-circle me-2"></i> Player Details
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="profile.php" class="btn btn-outline-light w-100">
                                    <i class="fas fa-user-edit me-2"></i> Edit Profile
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="../index.php" class="btn btn-outline-light w-100">
                                    <i class="fas fa-home me-2"></i> Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 