<?php
// Include initialization file
require_once '../config/init.php';

// Require login
requireLogin('../login.php');

// Initialize User class
$userObj = new User();
$currentUser = $userObj->getUserById($_SESSION['user_id']);

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        $message = 'Invalid form submission. Please try again.';
        $messageType = 'danger';
    } else {
        // Determine which form was submitted
        if (isset($_POST['update_profile'])) {
            // Update profile information
            $email = sanitize($_POST['email']);
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Invalid email address.';
                $messageType = 'danger';
            } else {
                // Check if email is already in use by another user
                $checkEmail = $userObj->getUserByEmail($email);
                if ($checkEmail && $checkEmail['id'] != $_SESSION['user_id']) {
                    $message = 'Email is already in use by another account.';
                    $messageType = 'danger';
                } else {
                    // Update the profile
                    $result = $userObj->updateProfile($_SESSION['user_id'], [
                        'email' => $email
                    ]);
                    
                    if ($result) {
                        $message = 'Profile updated successfully.';
                        $messageType = 'success';
                        // Refresh user data
                        $currentUser = $userObj->getUserById($_SESSION['user_id']);
                    } else {
                        $message = 'Failed to update profile. Please try again later.';
                        $messageType = 'danger';
                    }
                }
            }
        } elseif (isset($_POST['change_password'])) {
            // Change password
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate passwords
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $message = 'All password fields are required.';
                $messageType = 'danger';
            } elseif ($new_password !== $confirm_password) {
                $message = 'New passwords do not match.';
                $messageType = 'danger';
            } elseif (strlen($new_password) < 8) {
                $message = 'New password must be at least 8 characters long.';
                $messageType = 'danger';
            } else {
                // Verify current password
                if ($userObj->verifyPassword($_SESSION['user_id'], $current_password)) {
                    // Update the password
                    $result = $userObj->updatePassword($_SESSION['user_id'], $new_password);
                    
                    if ($result) {
                        $message = 'Password changed successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to change password. Please try again later.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Current password is incorrect.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Edit Profile - ' . getSetting('site_name', 'FiveM Server Dashboard');
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
        .form-control {
            background-color: #2d2d2d;
            border: none;
            color: #f8f9fa;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        .form-control:focus {
            background-color: #333;
            color: #f8f9fa;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        .form-control:disabled {
            background-color: #252525;
            color: #a0aec0;
        }
        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .admin-level {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .admin-level-1 {
            background-color: #3b82f6;
            color: white;
        }
        .admin-level-2 {
            background-color: #8b5cf6;
            color: white;
        }
        .admin-level-3 {
            background-color: #ec4899;
            color: white;
        }
        .alert {
            border-radius: 0.5rem;
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
                        <a href="player_info.php" class="sidebar-nav-link">
                            <i class="fas fa-info-circle"></i> Player Details
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="characters.php" class="sidebar-nav-link">
                            <i class="fas fa-users"></i> Manage Characters
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="profile.php" class="sidebar-nav-link active">
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
                    <h1>Edit Profile</h1>
                    <p class="text-muted">Manage your account settings</p>
                </div>

                <!-- Flash Messages -->
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Account Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-user me-2"></i> Account Information
                            </div>
                            <div class="card-body">
                                <form method="post" action="profile.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="update_profile" value="1">
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" value="<?php echo $currentUser['username']; ?>" disabled>
                                        <div class="form-text text-muted">Username cannot be changed.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="citizenid" class="form-label">Citizen ID</label>
                                        <input type="text" class="form-control" id="citizenid" value="<?php 
                                        if (isset($currentUser['player_id']) && !empty($currentUser['player_id'])) {
                                            $player = new Player();
                                            $playerData = $player->getPlayerById($currentUser['player_id']);
                                            echo isset($playerData['citizenid']) ? htmlspecialchars($playerData['citizenid']) : 'Not linked';
                                        } else {
                                            echo 'Not linked';
                                        }
                                        ?>" disabled>
                                        <div class="form-text">Citizen ID cannot be changed.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" id="role" value="<?php 
                                        echo isset($currentUser['role']) ? htmlspecialchars($currentUser['role']) : 'User'; 
                                        ?>" disabled>
                                        <div class="form-text">Your account role determines what features you can access.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="created_at" class="form-label">Account Created</label>
                                        <input type="text" class="form-control" id="created_at" value="<?php echo formatDatetime($currentUser['created_at'], 'F j, Y, g:i a'); ?>" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="last_login" class="form-label">Last Login</label>
                                        <input type="text" class="form-control" id="last_login" value="<?php echo $currentUser['last_login'] ? formatDatetime($currentUser['last_login'], 'F j, Y, g:i a') : 'Never'; ?>" disabled>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-key me-2"></i> Change Password
                            </div>
                            <div class="card-body">
                                <form method="post" action="profile.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="change_password" value="1">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text text-muted">Password must be at least 8 characters long.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>

                        <!-- Account Security Tips -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-shield-alt me-2"></i> Security Tips
                            </div>
                            <div class="card-body">
                                <ul class="mb-0 ps-3">
                                    <li class="mb-2">Use a strong, unique password for your account.</li>
                                    <li class="mb-2">Never share your password with anyone.</li>
                                    <li class="mb-2">Change your password regularly.</li>
                                    <li class="mb-2">Make sure your email address is secure and up to date.</li>
                                    <li class="mb-2">Log out when using public computers.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html> 