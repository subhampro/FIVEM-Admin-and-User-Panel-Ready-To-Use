<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges (minimum admin_level2)
requireAdmin('admin_level2');

// Initialize classes
$player = new Player();
$pendingChanges = new PendingChanges();
$logger = new Logger();

// Process edit form
$message = '';
$messageType = '';
$citizenid = '';
$playerDetails = null;

// Get citizenid from GET or POST
if (isset($_GET['citizenid']) && !empty($_GET['citizenid'])) {
    $citizenid = $_GET['citizenid'];
} elseif (isset($_POST['citizenid']) && !empty($_POST['citizenid'])) {
    $citizenid = $_POST['citizenid'];
} else {
    // Redirect back to players page if no citizenid provided
    header('Location: players.php');
    exit;
}

// Get player data
$playerData = $player->getPlayerByCitizenId($citizenid);
if (!$playerData) {
    // If player doesn't exist, redirect back to players page
    header('Location: players.php');
    exit;
}

// Load full player details
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

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Get field data
    $field = $_POST['field'] ?? '';
    $subfield = $_POST['subfield'] ?? '';
    $oldValue = $_POST['old_value'] ?? '';
    $newValue = $_POST['new_value'] ?? '';
    $targetTable = $_POST['target_table'] ?? 'players';
    
    if (empty($field)) {
        $message = 'Field name is required.';
        $messageType = 'danger';
    } elseif ($newValue === $oldValue) {
        $message = 'No changes were made to the value.';
        $messageType = 'warning';
    } else {
        // Add the change to pending_changes table
        $result = $pendingChanges->addPendingChange(
            $_SESSION['user_id'],
            $targetTable,
            $citizenid,
            $field . ($subfield ? '.' . $subfield : ''),
            $oldValue,
            $newValue
        );
        
        if ($result) {
            // Log the action
            $logger->logAction(
                $_SESSION['user_id'],
                'edit_player_request',
                "Requested edit for player {$citizenid}, field: {$field}" . ($subfield ? ".{$subfield}" : "") . ", old: {$oldValue}, new: {$newValue}"
            );
            
            $message = 'Edit request submitted for approval. The change will take effect after it has been approved.';
            $messageType = 'success';
        } else {
            $message = 'Failed to submit the edit request. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Page title
$playerName = isset($playerDetails['charinfo']['firstname']) ? 
    $playerDetails['charinfo']['firstname'] . ' ' . $playerDetails['charinfo']['lastname'] : 
    'Player';
$pageTitle = 'Edit ' . $playerName . ' - Admin Dashboard';
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
        
        .list-group-item {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #212529;
        }
        
        .form-control, .form-select {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #2a2e32;
            color: #e9ecef;
        }
        
        .table {
            color: #e9ecef;
        }
        
        .table-bordered {
            border-color: #2a2e32;
        }
        
        .table-bordered th,
        .table-bordered td {
            border-color: #2a2e32;
        }
        
        .alert-info {
            color: #e9ecef;
            background-color: #2a4059;
            border-color: #1e5f8e;
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
        }
        
        label.required:after {
            content: " *";
            color: #dc3545;
        }
        
        /* Fix dollar sign background */
        .input-group-text {
            background-color: #2a2e32;
            color: #e9ecef;
            border-color: #495057;
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
                <h1 class="h2">Edit Player: <?php echo htmlspecialchars($playerName); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="players.php?citizenid=<?php echo urlencode($citizenid); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Player Details
                    </a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i> All changes made here will require approval from a Level 3 administrator before they take effect in the game.
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Available Fields to Edit</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="editFieldsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="character-tab" data-bs-toggle="tab" data-bs-target="#character" type="button" role="tab" aria-controls="character" aria-selected="true">
                                Character Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="money-tab" data-bs-toggle="tab" data-bs-target="#money" type="button" role="tab" aria-controls="money" aria-selected="false">
                                Money
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="job-tab" data-bs-toggle="tab" data-bs-target="#job" type="button" role="tab" aria-controls="job" aria-selected="false">
                                Job
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="editFieldsTabContent">
                        <!-- Character Info Tab -->
                        <div class="tab-pane fade show active" id="character" role="tabpanel" aria-labelledby="character-tab">
                            <div class="list-group">
                                <!-- Full Name (First Name and Last Name) -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Full Name</h5>
                                    <p class="text-muted mb-3">Current: <?php echo htmlspecialchars($playerDetails['charinfo']['firstname'] ?? 'N/A'); ?> <?php echo htmlspecialchars($playerDetails['charinfo']['lastname'] ?? ''); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="charinfo">
                                        <input type="hidden" name="subfield" value="firstname">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['firstname'] ?? ''); ?>">
                                        
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="firstname" class="form-label required">First Name</label>
                                                <input type="text" class="form-control" id="firstname" name="new_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['firstname'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save First Name</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="charinfo">
                                        <input type="hidden" name="subfield" value="lastname">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['lastname'] ?? ''); ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="lastname" class="form-label required">Last Name</label>
                                                <input type="text" class="form-control" id="lastname" name="new_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['lastname'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Last Name</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Birth Date -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Birth Date</h5>
                                    <p class="text-muted mb-3">Current: <?php echo htmlspecialchars($playerDetails['charinfo']['birthdate'] ?? 'N/A'); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="charinfo">
                                        <input type="hidden" name="subfield" value="birthdate">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['birthdate'] ?? ''); ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="birthdate" class="form-label required">Birth Date (MM/DD/YYYY)</label>
                                                <input type="text" class="form-control" id="birthdate" name="new_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['birthdate'] ?? ''); ?>" required placeholder="MM/DD/YYYY">
                                                <div class="form-text text-light">Example: 01/15/1990</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Birth Date</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Gender -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Gender</h5>
                                    <p class="text-muted mb-3">Current: <?php echo isset($playerDetails['charinfo']['gender']) ? ($playerDetails['charinfo']['gender'] == 0 ? 'Male' : 'Female') : 'N/A'; ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="charinfo">
                                        <input type="hidden" name="subfield" value="gender">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['gender'] ?? ''); ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="gender" class="form-label required">Gender</label>
                                                <select class="form-select" id="gender" name="new_value" required>
                                                    <option value="0" <?php echo (isset($playerDetails['charinfo']['gender']) && $playerDetails['charinfo']['gender'] == 0) ? 'selected' : ''; ?>>Male</option>
                                                    <option value="1" <?php echo (isset($playerDetails['charinfo']['gender']) && $playerDetails['charinfo']['gender'] == 1) ? 'selected' : ''; ?>>Female</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Gender</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Phone Number -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Phone Number</h5>
                                    <p class="text-muted mb-3">Current: <?php echo htmlspecialchars($playerDetails['charinfo']['phone'] ?? 'N/A'); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="charinfo">
                                        <input type="hidden" name="subfield" value="phone">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['phone'] ?? ''); ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label required">Phone Number</label>
                                                <input type="text" class="form-control" id="phone" name="new_value" value="<?php echo htmlspecialchars($playerDetails['charinfo']['phone'] ?? ''); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Phone Number</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Money Tab -->
                        <div class="tab-pane fade" id="money" role="tabpanel" aria-labelledby="money-tab">
                            <div class="list-group">
                                <!-- Cash -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Cash</h5>
                                    <p class="text-muted mb-3">Current: $<?php echo number_format($playerDetails['money']['cash'] ?? 0, 2); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="money">
                                        <input type="hidden" name="subfield" value="cash">
                                        <input type="hidden" name="old_value" value="<?php echo $playerDetails['money']['cash'] ?? 0; ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="cash" class="form-label required">Cash Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="cash" name="new_value" value="<?php echo $playerDetails['money']['cash'] ?? 0; ?>" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Cash Amount</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Bank -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Bank Balance</h5>
                                    <p class="text-muted mb-3">Current: $<?php echo number_format($playerDetails['money']['bank'] ?? 0, 2); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="money">
                                        <input type="hidden" name="subfield" value="bank">
                                        <input type="hidden" name="old_value" value="<?php echo $playerDetails['money']['bank'] ?? 0; ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="bank" class="form-label required">Bank Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="bank" name="new_value" value="<?php echo $playerDetails['money']['bank'] ?? 0; ?>" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Bank Amount</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Crypto -->
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Crypto</h5>
                                    <p class="text-muted mb-3">Current: <?php echo number_format($playerDetails['money']['crypto'] ?? 0, 2); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="money">
                                        <input type="hidden" name="subfield" value="crypto">
                                        <input type="hidden" name="old_value" value="<?php echo $playerDetails['money']['crypto'] ?? 0; ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="crypto" class="form-label required">Crypto Amount</label>
                                                <input type="number" class="form-control" id="crypto" name="new_value" value="<?php echo $playerDetails['money']['crypto'] ?? 0; ?>" step="0.01" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Save Crypto Amount</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Job Tab -->
                        <div class="tab-pane fade" id="job" role="tabpanel" aria-labelledby="job-tab">
                            <div class="list-group">
                                <div class="list-group-item">
                                    <h5 class="mb-1">Edit Job</h5>
                                    <p class="text-muted mb-3">Current: <?php echo htmlspecialchars($playerDetails['job']['label'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($playerDetails['job']['grade']['name'] ?? 'N/A'); ?></p>
                                    
                                    <form method="post" action="">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($citizenid); ?>">
                                        <input type="hidden" name="target_table" value="players">
                                        <input type="hidden" name="field" value="job">
                                        <input type="hidden" name="old_value" value="<?php echo htmlspecialchars(json_encode($playerDetails['job']) ?? '{}'); ?>">
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="job_name" class="form-label required">Job Name</label>
                                                <select class="form-select" id="job_name" name="job_name" required>
                                                    <option value="unemployed" <?php echo ($playerDetails['job']['name'] ?? '') === 'unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                                                    <option value="police" <?php echo ($playerDetails['job']['name'] ?? '') === 'police' ? 'selected' : ''; ?>>Police</option>
                                                    <option value="ambulance" <?php echo ($playerDetails['job']['name'] ?? '') === 'ambulance' ? 'selected' : ''; ?>>EMS</option>
                                                    <option value="mechanic" <?php echo ($playerDetails['job']['name'] ?? '') === 'mechanic' ? 'selected' : ''; ?>>Mechanic</option>
                                                    <option value="taxi" <?php echo ($playerDetails['job']['name'] ?? '') === 'taxi' ? 'selected' : ''; ?>>Taxi</option>
                                                    <option value="cardealer" <?php echo ($playerDetails['job']['name'] ?? '') === 'cardealer' ? 'selected' : ''; ?>>Car Dealer</option>
                                                    <option value="realestate" <?php echo ($playerDetails['job']['name'] ?? '') === 'realestate' ? 'selected' : ''; ?>>Real Estate</option>
                                                    <option value="garbage" <?php echo ($playerDetails['job']['name'] ?? '') === 'garbage' ? 'selected' : ''; ?>>Garbage</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="job_grade" class="form-label required">Job Grade</label>
                                                <input type="number" class="form-control" id="job_grade" name="job_grade" value="<?php echo $playerDetails['job']['grade']['level'] ?? 0; ?>" min="0" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-primary" id="jobSubmitBtn">Save Job</button>
                                                <input type="hidden" name="new_value" id="newJobData">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle job form submission
    $('#jobSubmitBtn').click(function() {
        const jobName = $('#job_name').val();
        const jobGrade = parseInt($('#job_grade').val());
        
        // Create job data object
        const jobData = {
            name: jobName,
            label: $('#job_name option:selected').text(),
            onduty: true,
            payment: 0,
            grade: {
                level: jobGrade,
                name: 'Grade ' + jobGrade
            }
        };
        
        // Set the JSON data in the hidden field
        $('#newJobData').val(JSON.stringify(jobData));
        
        // Submit the form
        $(this).closest('form').submit();
    });
});
</script>
</body>
</html> 