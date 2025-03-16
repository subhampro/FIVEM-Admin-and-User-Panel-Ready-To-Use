<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level2');

// Initialize User class
$userObj = new User();
$logger = new Logger();

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid form submission. Please try again.');
        redirect('add_user.php');
    }
    
    // Get form data
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
        $messageType = 'danger';
    } else {
        // Check if username or email already exists
        if ($userObj->getUserByUsername($username)) {
            $message = 'Username already exists.';
            $messageType = 'danger';
        } else if ($userObj->getUserByEmail($email)) {
            $message = 'Email already exists.';
            $messageType = 'danger';
        } else {
            // Register the user
            $userId = $userObj->registerUser($username, $password, $email);
            
            if ($userId) {
                // Update user role and status
                $userObj->updateUser($userId, [
                    'role' => $role,
                    'is_active' => $isActive,
                    'is_admin' => ($role !== 'user') ? 1 : 0
                ]);
                
                // Log the action
                $logger->logAction($_SESSION['user_id'], 'create_user', "Created new user: $username");
                
                // Set success message
                setFlashMessage('success', 'User added successfully.');
                redirect('users.php');
            } else {
                $message = 'Failed to create user.';
                $messageType = 'danger';
            }
        }
    }
}

// Page title
$pageTitle = 'Add New User - Admin Dashboard';
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
</head>
<body class="admin-panel">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add New User</h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php $flashMessage = getFlashMessage(); ?>
    <?php if ($flashMessage): ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?>" role="alert">
        <?php echo $flashMessage['message']; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">User Information</h5>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="user">Regular User</option>
                        <option value="admin_level1">Admin Level 1</option>
                        <option value="admin_level2">Admin Level 2</option>
                        <option value="admin_level3">Admin Level 3</option>
                    </select>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                    <label class="form-check-label" for="is_active">Active Account</label>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 