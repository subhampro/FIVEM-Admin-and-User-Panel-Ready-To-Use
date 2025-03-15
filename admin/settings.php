<?php
require_once '../config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login.php');
    exit;
}

$admin = new Admin();
$logger = new Logger();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $adminEmail = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
    $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $allowRegistration = isset($_POST['allow_registration']) ? 1 : 0;
    $emailVerification = isset($_POST['email_verification']) ? 1 : 0;
    
    // Validate inputs
    $errors = [];
    
    if (empty($siteName)) {
        $errors[] = 'Site name is required.';
    }
    
    if (empty($adminEmail)) {
        $errors[] = 'Admin email is required.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($errors)) {
        // Update settings
        $result = $admin->updateSettings([
            'site_name' => $siteName,
            'admin_email' => $adminEmail,
            'maintenance_mode' => $maintenanceMode,
            'allow_registration' => $allowRegistration,
            'email_verification' => $emailVerification
        ]);
        
        if ($result) {
            $logger->log('Admin updated site settings', 'admin_action', $_SESSION['user_id']);
            $_SESSION['success_message'] = 'Settings updated successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to update settings.';
        }
        
        // Redirect to avoid form resubmission
        header('Location: settings.php');
        exit;
    }
}

// Get current settings
$settings = $admin->getSettings();

// Page title
$pageTitle = 'Settings';

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="players.php">
                            <i class="fas fa-gamepad"></i> Players
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_changes.php">
                            <i class="fas fa-clock"></i> Pending Changes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logs.php">
                            <i class="fas fa-list"></i> Logs
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Site Settings</h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm" method="post" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="siteName" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="siteName" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                <div id="siteNameFeedback" class="invalid-feedback">
                                    Please provide a site name.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="adminEmail" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="adminEmail" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" required>
                                <div id="adminEmailFeedback" class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenance_mode" <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                                </div>
                                <small class="text-muted">When enabled, only admins can access the site.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allowRegistration" name="allow_registration" <?php echo (isset($settings['allow_registration']) && $settings['allow_registration'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="allowRegistration">Allow User Registration</label>
                                </div>
                                <small class="text-muted">When disabled, new users cannot register on the site.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailVerification" name="email_verification" <?php echo (isset($settings['email_verification']) && $settings['email_verification'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="emailVerification">Require Email Verification</label>
                                </div>
                                <small class="text-muted">When enabled, new users must verify their email address before logging in.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 