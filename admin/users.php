<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level1');

// Initialize classes
$admin = new Admin();
$userObj = new User();
$logger = new Logger();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid form submission. Please try again.');
        redirect('users.php');
    }
    
    $action = $_POST['action'];
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($action === 'update_role' && $userId > 0 && isAdmin('admin_level3')) {
        $role = sanitize($_POST['role']);
        
        // Create pending change if not admin level 3
        if (!isAdmin('admin_level3')) {
            $currentUser = $userObj->getUserById($userId);
            
            if ($currentUser) {
                $pendingChanges = new PendingChanges();
                $changeId = $pendingChanges->createPendingChange(
                    $_SESSION['user_id'],
                    'website_users',
                    $userId,
                    'role',
                    $currentUser['role'],
                    $role
                );
                
                if ($changeId) {
                    // Log the action
                    $logger->logAction(
                        $_SESSION['user_id'],
                        'create_pending_change',
                        "Created pending change for user role update: User ID {$userId}, Role: {$role}",
                        'website_users',
                        $userId
                    );
                    
                    setFlashMessage('info', 'Role change has been submitted for approval.');
                    redirect('users.php');
                } else {
                    setFlashMessage('danger', 'Failed to create pending change.');
                    redirect('users.php');
                }
            } else {
                setFlashMessage('danger', 'User not found.');
                redirect('users.php');
            }
        } else {
            // Direct update for admin level 3
            $result = $userObj->updateRole($userId, $role);
            
            if ($result) {
                // Log the action
                $logger->logAction(
                    $_SESSION['user_id'],
                    'update_user_role',
                    "Updated user role: User ID {$userId}, Role: {$role}",
                    'website_users',
                    $userId
                );
                
                setFlashMessage('success', 'User role updated successfully.');
                redirect('users.php');
            } else {
                setFlashMessage('danger', 'Failed to update user role.');
                redirect('users.php');
            }
        }
    } elseif ($action === 'toggle_status' && $userId > 0 && isAdmin('admin_level2')) {
        $currentUser = $userObj->getUserById($userId);
        
        if ($currentUser) {
            $newStatus = $currentUser['is_active'] ? 0 : 1;
            
            // Create pending change if not admin level 3
            if (!isAdmin('admin_level3')) {
                $pendingChanges = new PendingChanges();
                $changeId = $pendingChanges->createPendingChange(
                    $_SESSION['user_id'],
                    'website_users',
                    $userId,
                    'is_active',
                    $currentUser['is_active'],
                    $newStatus
                );
                
                if ($changeId) {
                    // Log the action
                    $logger->logAction(
                        $_SESSION['user_id'],
                        'create_pending_change',
                        "Created pending change for user status update: User ID {$userId}, Status: " . ($newStatus ? 'Active' : 'Inactive'),
                        'website_users',
                        $userId
                    );
                    
                    setFlashMessage('info', 'Status change has been submitted for approval.');
                    redirect('users.php');
                } else {
                    setFlashMessage('danger', 'Failed to create pending change.');
                    redirect('users.php');
                }
            } else {
                // Direct update for admin level 3
                $result = $userObj->setActiveStatus($userId, $newStatus);
                
                if ($result) {
                    // Log the action
                    $logger->logAction(
                        $_SESSION['user_id'],
                        'update_user_status',
                        "Updated user status: User ID {$userId}, Status: " . ($newStatus ? 'Active' : 'Inactive'),
                        'website_users',
                        $userId
                    );
                    
                    setFlashMessage('success', 'User status updated successfully.');
                    redirect('users.php');
                } else {
                    setFlashMessage('danger', 'Failed to update user status.');
                    redirect('users.php');
                }
            }
        } else {
            setFlashMessage('danger', 'User not found.');
            redirect('users.php');
        }
    }
}

// Get users list
$users = $userObj->getAllUsers();

// Page title
$pageTitle = 'Manage Users - Admin Dashboard';
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
        .table {
            color: #f8f9fa;
        }
        .table th, .table td {
            border-color: #2d2d2d;
            vertical-align: middle;
        }
        .table-dark {
            background-color: #1e1e1e;
        }
        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .btn-outline-light:hover {
            color: #fff;
            background-color: #4f46e5;
            border-color: #4f46e5;
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
        .search-box {
            background-color: #2d2d2d;
            border: none;
            color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        .search-box:focus {
            background-color: #333;
            color: #f8f9fa;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        .modal-content {
            background-color: #1e1e1e;
            color: #f8f9fa;
            border: none;
            border-radius: 1rem;
        }
        .modal-header {
            border-bottom-color: #2d2d2d;
        }
        .modal-footer {
            border-top-color: #2d2d2d;
        }
        .form-control, .form-select {
            background-color: #2d2d2d;
            border: none;
            color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        .form-control:focus, .form-select:focus {
            background-color: #333;
            color: #f8f9fa;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23f8f9fa' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }
        .badge-active {
            background-color: #10b981;
            color: white;
        }
        .badge-inactive {
            background-color: #ef4444;
            color: white;
        }
    </style>
</head>
<body class="admin-panel">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="sidebar-brand">
                    <a href="index.php" class="text-decoration-none text-white">
                        <i class="fas fa-tachometer-alt"></i> Admin Panel
                    </a>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="index.php" class="sidebar-nav-link">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="players.php" class="sidebar-nav-link">
                            <i class="fas fa-users"></i> Players
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="users.php" class="sidebar-nav-link active">
                            <i class="fas fa-user-shield"></i> Users
                        </a>
                    </li>
                    <?php if (isAdmin('admin_level3')): ?>
                    <li class="sidebar-nav-item">
                        <a href="pending_changes.php" class="sidebar-nav-link">
                            <i class="fas fa-tasks"></i> Pending Changes
                            <?php 
                            $pendingChanges = new PendingChanges();
                            $pendingCount = $pendingChanges->countPendingChanges('pending');
                            if ($pendingCount > 0):
                            ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sidebar-nav-item">
                        <a href="logs.php" class="sidebar-nav-link">
                            <i class="fas fa-history"></i> Logs
                        </a>
                    </li>
                    <?php if (isAdmin('admin_level3')): ?>
                    <li class="sidebar-nav-item">
                        <a href="settings.php" class="sidebar-nav-link">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="sidebar-nav-item mt-4">
                        <a href="../user/index.php" class="sidebar-nav-link">
                            <i class="fas fa-user"></i> User Dashboard
                        </a>
                    </li>
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
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Manage Users</h1>
                        <p class="text-muted">View and manage website users</p>
                    </div>
                    <?php if (isAdmin('admin_level3')): ?>
                    <div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-plus me-2"></i> Create Admin
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Flash Messages -->
                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Users Card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-shield me-2"></i> Website Users
                        </div>
                        <div class="d-flex">
                            <input type="text" id="userSearch" class="search-box me-2" placeholder="Search users...">
                            <select id="roleFilter" class="form-select" style="width: auto;">
                                <option value="">All Roles</option>
                                <option value="user">Regular User</option>
                                <option value="admin_level1">View Only Admin</option>
                                <option value="admin_level2">Edit Admin</option>
                                <option value="admin_level3">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Citizen ID</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($users && count($users) > 0): ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['citizenid']; ?></td>
                                            <td>
                                                <?php if ($user['role'] !== 'user'): ?>
                                                <span class="admin-level <?php echo 'admin-level-' . substr($user['role'], -1); ?>">
                                                    <?php echo getAdminLevelName($user['role']); ?>
                                                </span>
                                                <?php else: ?>
                                                Regular User
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $user['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $user['last_login'] ? formatDatetime($user['last_login'], 'Y-m-d H:i') : 'Never'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if (isAdmin('admin_level2') && $user['id'] != $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-light edit-role-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editRoleModal" 
                                                        data-user-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo $user['username']; ?>"
                                                        data-role="<?php echo $user['role']; ?>">
                                                        <i class="fas fa-user-tag"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isAdmin('admin_level2') && $user['id'] != $_SESSION['user_id']): ?>
                                                    <form method="post" action="users.php" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-light" onclick="return confirm('Are you sure you want to <?php echo $user['is_active'] ? 'deactivate' : 'activate'; ?> this user?')">
                                                            <i class="fas <?php echo $user['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-light view-user-btn"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewUserModal"
                                                        data-user-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo $user['username']; ?>"
                                                        data-email="<?php echo $user['email']; ?>"
                                                        data-citizenid="<?php echo $user['citizenid']; ?>"
                                                        data-role="<?php echo $user['role']; ?>"
                                                        data-status="<?php echo $user['is_active']; ?>"
                                                        data-created="<?php echo formatDatetime($user['created_at'], 'Y-m-d H:i'); ?>"
                                                        data-last-login="<?php echo $user['last_login'] ? formatDatetime($user['last_login'], 'Y-m-d H:i') : 'Never'; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No users found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Edit User Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="users.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" id="editRoleUserId">
                        
                        <div class="mb-3">
                            <label for="editRoleUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editRoleUsername" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editRoleSelect" class="form-label">Role</label>
                            <select class="form-select" id="editRoleSelect" name="role" required>
                                <option value="user">Regular User</option>
                                <option value="admin_level1">View Only Admin</option>
                                <option value="admin_level2">Edit Admin</option>
                                <?php if (isAdmin('admin_level3')): ?>
                                <option value="admin_level3">Super Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <?php if (!isAdmin('admin_level3')): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Your role changes will require approval from a Super Admin.
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-dark">
                        <tr>
                            <th>User ID:</th>
                            <td id="viewUserId"></td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td id="viewUsername"></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td id="viewEmail"></td>
                        </tr>
                        <tr>
                            <th>Citizen ID:</th>
                            <td id="viewCitizenId"></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td id="viewRole"></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td id="viewStatus"></td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td id="viewCreated"></td>
                        </tr>
                        <tr>
                            <th>Last Login:</th>
                            <td id="viewLastLogin"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <?php if (isAdmin('admin_level3')): ?>
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create Admin User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="users.php">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="create_admin">
                        
                        <div class="mb-3">
                            <label for="createUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="createUsername" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="createEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="createEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="createCitizenId" class="form-label">Citizen ID</label>
                            <input type="text" class="form-control" id="createCitizenId" name="citizenid" required>
                            <div class="form-text text-muted">Must be an existing player's Citizen ID.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="createPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="createPassword" name="password" required>
                            <div class="form-text text-muted">Minimum 8 characters.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="createRole" class="form-label">Admin Role</label>
                            <select class="form-select" id="createRole" name="role" required>
                                <option value="admin_level1">View Only Admin</option>
                                <option value="admin_level2">Edit Admin</option>
                                <option value="admin_level3">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit role modal
            const editRoleModal = document.getElementById('editRoleModal');
            if (editRoleModal) {
                editRoleModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    const role = button.getAttribute('data-role');
                    
                    document.getElementById('editRoleUserId').value = userId;
                    document.getElementById('editRoleUsername').value = username;
                    document.getElementById('editRoleSelect').value = role;
                });
            }
            
            // View user modal
            const viewUserModal = document.getElementById('viewUserModal');
            if (viewUserModal) {
                viewUserModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userId = button.getAttribute('data-user-id');
                    const username = button.getAttribute('data-username');
                    const email = button.getAttribute('data-email');
                    const citizenid = button.getAttribute('data-citizenid');
                    const role = button.getAttribute('data-role');
                    const status = button.getAttribute('data-status') === '1' ? 'Active' : 'Inactive';
                    const created = button.getAttribute('data-created');
                    const lastLogin = button.getAttribute('data-last-login');
                    
                    document.getElementById('viewUserId').textContent = userId;
                    document.getElementById('viewUsername').textContent = username;
                    document.getElementById('viewEmail').textContent = email;
                    document.getElementById('viewCitizenId').textContent = citizenid;
                    
                    let roleText = 'Regular User';
                    let roleClass = '';
                    
                    if (role !== 'user') {
                        const level = role.substr(-1);
                        roleText = role === 'admin_level1' ? 'View Only Admin' : 
                                  role === 'admin_level2' ? 'Edit Admin' : 'Super Admin';
                        roleClass = `admin-level admin-level-${level}`;
                    }
                    
                    document.getElementById('viewRole').innerHTML = `<span class="${roleClass}">${roleText}</span>`;
                    document.getElementById('viewStatus').innerHTML = `<span class="badge ${status === 'Active' ? 'badge-active' : 'badge-inactive'}">${status}</span>`;
                    document.getElementById('viewCreated').textContent = created;
                    document.getElementById('viewLastLogin').textContent = lastLogin;
                });
            }
            
            // User search filter
            const userSearch = document.getElementById('userSearch');
            const roleFilter = document.getElementById('roleFilter');
            const usersTable = document.getElementById('usersTable');
            
            function filterTable() {
                const searchValue = userSearch.value.toLowerCase();
                const roleValue = roleFilter.value;
                const rows = usersTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const username = row.cells[1].textContent.toLowerCase();
                    const email = row.cells[2].textContent.toLowerCase();
                    const citizenId = row.cells[3].textContent.toLowerCase();
                    const role = row.querySelector('td:nth-child(5)').textContent.trim();
                    
                    const matchesSearch = username.includes(searchValue) || 
                                         email.includes(searchValue) || 
                                         citizenId.includes(searchValue);
                    
                    let matchesRole = true;
                    if (roleValue !== '') {
                        if (roleValue === 'user') {
                            matchesRole = role === 'Regular User';
                        } else if (roleValue === 'admin_level1') {
                            matchesRole = role.includes('View Only Admin');
                        } else if (roleValue === 'admin_level2') {
                            matchesRole = role.includes('Edit Admin');
                        } else if (roleValue === 'admin_level3') {
                            matchesRole = role.includes('Super Admin');
                        }
                    }
                    
                    if (matchesSearch && matchesRole) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            if (userSearch) {
                userSearch.addEventListener('input', filterTable);
            }
            
            if (roleFilter) {
                roleFilter.addEventListener('change', filterTable);
            }
        });
    </script>
</body>
</html> 