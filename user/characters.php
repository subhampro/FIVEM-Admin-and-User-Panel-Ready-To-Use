<?php
// Include initialization file
require_once '../config/init.php';

// Require login
requireLogin('../login.php');

// Initialize User class
$userObj = new User();
$currentUser = $userObj->getUserById($_SESSION['user_id']);

// Get player class for character information
$playerObj = new Player();

// Get all characters for this user
$characters = $userObj->getUserCharacters($_SESSION['user_id']);

// Determine active character
$activeCharacterId = isset($_SESSION['active_citizenid']) ? $_SESSION['active_citizenid'] : '';

// Handle character actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        $message = 'Invalid form submission. Please try again.';
        $messageType = 'danger';
    } else {
        // Handle different actions
        
        // Add new character
        if (isset($_POST['add_character'])) {
            $newCitizenId = sanitize($_POST['citizenid']);
            
            // Validate citizenid
            if (empty($newCitizenId)) {
                $message = 'Please enter a valid Citizen ID.';
                $messageType = 'danger';
            } else {
                // Check if this character exists in the game
                $newPlayer = $playerObj->getPlayerByCitizenId($newCitizenId);
                
                if (!$newPlayer) {
                    $message = 'Character with this Citizen ID does not exist in the game.';
                    $messageType = 'danger';
                } else {
                    // Check if character already registered to this user
                    $existingChar = $userObj->getUserCharacters($_SESSION['user_id']);
                    $alreadyAdded = false;
                    
                    if ($existingChar && is_array($existingChar)) {
                        foreach ($existingChar as $char) {
                            if ($char['citizenid'] == $newCitizenId) {
                                $alreadyAdded = true;
                                break;
                            }
                        }
                    }
                    
                    if ($alreadyAdded) {
                        $message = 'This character is already linked to your account.';
                        $messageType = 'danger';
                    } else {
                        // Check if character belongs to another user
                        $otherUser = $userObj->getUserByCitizenId($newCitizenId);
                        
                        if ($otherUser && $otherUser['id'] != $_SESSION['user_id']) {
                            $message = 'This character is already linked to another account.';
                            $messageType = 'danger';
                        } else {
                            // Verify this character belongs to the same player (check license)
                            // Skip license check if this is the first character
                            $existingCharCount = is_array($existingChar) ? count($existingChar) : 0;
                            $isFirstCharacter = $existingCharCount === 0;
                            
                            if ($isFirstCharacter) {
                                $isValid = true; // Skip license check for first character
                            } else {
                                $isValid = $userObj->verifyCharacterOwnership($_SESSION['user_id'], $newCitizenId);
                            }
                            
                            if (!$isValid) {
                                $message = 'This character does not appear to belong to you. Character licenses must match.';
                                $messageType = 'danger';
                                
                                // Add debug info for admins
                                if (isAdmin()) {
                                    // Get licenses for debugging
                                    $primaryChar = $userObj->getPrimaryCharacter($_SESSION['user_id']);
                                    if ($primaryChar) {
                                        $primaryCitizenId = $primaryChar['citizenid'];
                                        $primaryPlayer = $playerObj->getPlayerByCitizenId($primaryCitizenId);
                                        $newPlayer = $playerObj->getPlayerByCitizenId($newCitizenId);
                                        
                                        if ($primaryPlayer && isset($primaryPlayer['license']) && $newPlayer && isset($newPlayer['license'])) {
                                            $message .= '<br><br><strong>Debug Info (Admin Only):</strong><br>';
                                            $message .= 'Primary character license: ' . $primaryPlayer['license'] . '<br>';
                                            $message .= 'New character license: ' . $newPlayer['license'];
                                        }
                                    }
                                }
                            } else {
                                // Add the character
                                $result = $userObj->addCharacter($_SESSION['user_id'], $newCitizenId);
                                
                                if ($result) {
                                    $message = 'Character added successfully.';
                                    $messageType = 'success';
                                    
                                    // Refresh character list
                                    $characters = $userObj->getUserCharacters($_SESSION['user_id']);
                                } else {
                                    $message = 'Failed to add character. Please try again later.';
                                    $messageType = 'danger';
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Set primary character
        if (isset($_POST['set_primary'])) {
            $citizenId = sanitize($_POST['citizenid']);
            
            // Validate character belongs to user
            $valid = false;
            foreach ($characters as $char) {
                if ($char['citizenid'] == $citizenId) {
                    $valid = true;
                    break;
                }
            }
            
            if (!$valid) {
                $message = 'Invalid character selection.';
                $messageType = 'danger';
            } else {
                // Set as primary
                $result = $userObj->setPrimaryCharacter($_SESSION['user_id'], $citizenId);
                
                if ($result) {
                    $message = 'Primary character updated successfully.';
                    $messageType = 'success';
                    
                    // Set as active character in session
                    $_SESSION['active_citizenid'] = $citizenId;
                    $activeCharacterId = $citizenId;
                    
                    // Refresh character list
                    $characters = $userObj->getUserCharacters($_SESSION['user_id']);
                } else {
                    $message = 'Failed to update primary character. Please try again later.';
                    $messageType = 'danger';
                }
            }
        }
        
        // Remove character
        if (isset($_POST['remove_character'])) {
            $citizenId = sanitize($_POST['citizenid']);
            
            // Validate character belongs to user
            $valid = false;
            foreach ($characters as $char) {
                if ($char['citizenid'] == $citizenId) {
                    $valid = true;
                    break;
                }
            }
            
            if (!$valid) {
                $message = 'Invalid character selection.';
                $messageType = 'danger';
            } else {
                // Remove character
                $result = $userObj->removeCharacter($_SESSION['user_id'], $citizenId);
                
                if ($result) {
                    $message = 'Character removed successfully.';
                    $messageType = 'success';
                    
                    // Refresh character list
                    $characters = $userObj->getUserCharacters($_SESSION['user_id']);
                    
                    // If active character was removed, update session
                    if ($_SESSION['active_citizenid'] == $citizenId) {
                        // Get new primary character
                        $primaryChar = $userObj->getPrimaryCharacter($_SESSION['user_id']);
                        if ($primaryChar) {
                            $_SESSION['active_citizenid'] = $primaryChar['citizenid'];
                            $activeCharacterId = $primaryChar['citizenid'];
                        } elseif (!empty($characters)) {
                            $_SESSION['active_citizenid'] = $characters[0]['citizenid'];
                            $activeCharacterId = $characters[0]['citizenid'];
                        }
                    }
                } else {
                    $message = 'Failed to remove character. Please try again later.';
                    $messageType = 'danger';
                }
            }
        }
        
        // Switch active character
        if (isset($_POST['switch_character'])) {
            $citizenId = sanitize($_POST['citizenid']);
            
            // Validate character belongs to user
            $valid = false;
            foreach ($characters as $char) {
                if ($char['citizenid'] == $citizenId) {
                    $valid = true;
                    break;
                }
            }
            
            if (!$valid) {
                $message = 'Invalid character selection.';
                $messageType = 'danger';
            } else {
                // Update session
                $_SESSION['active_citizenid'] = $citizenId;
                $activeCharacterId = $citizenId;
                
                $message = 'Switched to character successfully.';
                $messageType = 'success';
                
                // Log character switch
                $logger = new Logger();
                $logger->logAction($_SESSION['user_id'], 'character_switch', 'User switched to character: ' . $citizenId);
            }
        }
    }
}

// Page title
$pageTitle = 'Manage Characters - ' . getSetting('site_name', 'FiveM Server Dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            background-color: #121212 !important;
            color: #f8f9fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background-color: #1e1e1e !important;
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
        main {
            background-color: #121212 !important;
            color: #f8f9fa !important;
            padding: 2rem;
        }
        .col-md-9, .col-lg-10 {
            background-color: #121212 !important;
        }
        h1, h2, h3, h4, h5, h6, p, span, div, th, td {
            color: #f8f9fa !important;
        }
        .border-bottom {
            border-color: #2d2d2d !important;
        }
        .table {
            color: #f8f9fa !important;
            background-color: transparent !important;
        }
        .table th, .table td {
            border-color: #2d2d2d !important;
            background-color: #1e1e1e !important;
            color: #f8f9fa !important;
        }
        .table tr {
            background-color: #1e1e1e !important;
            color: #f8f9fa !important;
        }
        .table tbody tr:hover {
            background-color: #2a2a2a !important;
        }
        .table-active, 
        .table-active > th, 
        .table-active > td,
        tr.table-active,
        .table tbody tr.table-active {
            background-color: rgba(79, 70, 229, 0.2) !important;
        }
        .table-hover tbody tr:hover {
            background-color: #2a2a2a !important;
        }
        .card {
            background-color: #1e1e1e !important;
            border: 1px solid #2d2d2d !important;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #1a1a1a !important;
            border-bottom: 1px solid #2d2d2d !important;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-body {
            background-color: #1e1e1e !important;
        }
        .alert {
            border: none;
        }
        .alert-info {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: #60a5fa !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }
        .dropdown-menu {
            background-color: #1e1e1e;
            border-color: #2d2d2d;
        }
        .dropdown-item {
            color: #e0e0e0;
        }
        .dropdown-item:hover {
            background-color: #2d2d2d;
            color: #f8f9fa;
        }
        .form-control {
            background-color: #2d2d2d !important;
            color: #f8f9fa !important;
            border: 1px solid #3d3d3d !important;
        }
        .form-control:focus {
            background-color: #2d2d2d;
            color: #f8f9fa;
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        .form-text {
            color: #a0aec0 !important;
        }
        .btn-primary {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            color: white !important;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .btn-outline-primary {
            color: #4f46e5;
            border-color: #4f46e5;
        }
        .btn-outline-primary:hover {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .btn-outline-danger {
            color: #f87171;
            border-color: #f87171;
        }
        .btn-outline-danger:hover {
            background-color: #f87171;
            border-color: #f87171;
        }
        .btn-outline-success {
            color: #34d399;
            border-color: #34d399;
        }
        .btn-outline-success:hover {
            background-color: #34d399;
            border-color: #34d399;
        }
        .badge {
            font-weight: 500;
            padding: 0.4em 0.65em;
        }
        code {
            color: #f472b6;
            background-color: rgba(244, 114, 182, 0.1);
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
        }
        label {
            color: #f8f9fa !important;
        }
        .table thead th {
            background-color: #1a1a1a !important;
            color: #f8f9fa !important;
            border-bottom: 2px solid #2d2d2d !important;
        }
        
        /* Override any other bootstrap table classes */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #1a1a1a !important;
        }
        
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #1e1e1e !important;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
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
                    <a href="player_info.php" class="sidebar-nav-link">
                        <i class="fas fa-info-circle"></i> Player Details
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="characters.php" class="sidebar-nav-link active">
                        <i class="fas fa-users"></i> Manage Characters
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
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Characters</h1>
            </div>
            
            <?php if (!empty($message)) : ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Current Characters -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Your Characters</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($characters)) : ?>
                                <div class="alert alert-info">
                                    You have no characters linked to your account. Please add a character using the form on the right.
                                </div>
                            <?php else : ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Citizen ID</th>
                                                <th>Name</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($characters as $character) : 
                                                $playerInfo = $playerObj->getPlayerByCitizenId($character['citizenid']);
                                                $charName = isset($playerInfo['charinfo']) ? json_decode($playerInfo['charinfo'], true) : null;
                                                $displayName = $charName && isset($charName['firstname'], $charName['lastname']) 
                                                    ? $charName['firstname'] . ' ' . $charName['lastname']
                                                    : 'Unknown';
                                            ?>
                                                <tr class="<?php echo ($character['citizenid'] == $activeCharacterId) ? 'table-active' : ''; ?>">
                                                    <td><?php echo htmlspecialchars($character['citizenid']); ?></td>
                                                    <td><?php echo htmlspecialchars($displayName); ?></td>
                                                    <td>
                                                        <?php if ($character['is_primary']) : ?>
                                                            <span class="badge bg-primary">Primary</span>
                                                        <?php endif; ?>
                                                        <?php if ($character['citizenid'] == $activeCharacterId) : ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($character['citizenid'] != $activeCharacterId) : ?>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($character['citizenid']); ?>">
                                                                <button type="submit" name="switch_character" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-box-arrow-in-right"></i> Switch
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!$character['is_primary']) : ?>
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($character['citizenid']); ?>">
                                                                <button type="submit" name="set_primary" class="btn btn-sm btn-outline-success">
                                                                    <i class="bi bi-star"></i> Set Primary
                                                                </button>
                                                            </form>
                                                            
                                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this character?');">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                                <input type="hidden" name="citizenid" value="<?php echo htmlspecialchars($character['citizenid']); ?>">
                                                                <button type="submit" name="remove_character" class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash"></i> Remove
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Add Character Form -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Add Character</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <?php if (empty($characters)): ?>
                                Add your first character by entering your Citizen ID (CSN) below.
                                <?php else: ?>
                                Add another character that belongs to you. The character must have the same license as your existing character.
                                <?php endif; ?>
                            </p>
                            
                            <div class="alert alert-info small">
                                <strong><i class="fas fa-info-circle me-1"></i> How to get your CSN ID:</strong>
                                <ol class="mb-0 ps-3 mt-1">
                                    <li>Join the Elapsed2.0 server in game</li>
                                    <li>Type <code>/csn</code> in the chat</li>
                                    <li>Copy the ID that appears</li>
                                    <li>Paste it in the field below</li>
                                </ol>
                            </div>
                            
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                
                                <div class="mb-3">
                                    <label for="citizenid" class="form-label">Citizen ID (CSN ID)</label>
                                    <input type="text" class="form-control" id="citizenid" name="citizenid" required 
                                           placeholder="Enter Citizen ID (use /csn in game)">
                                    <div class="form-text">You can get your CSN ID by typing /csn in game.</div>
                                </div>
                                
                                <button type="submit" name="add_character" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Character
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 