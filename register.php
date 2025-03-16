<?php
// Include initialization file
require_once 'config/init.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('user/index.php');
    }
}

// Check if registration is enabled
if (getSetting('registration_enabled', '1') !== '1') {
    setFlashMessage('error', 'Registration is currently disabled. Please contact an administrator.');
    redirect('login.php');
}

// Process registration form
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        // Get form data
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $citizenid = isset($_POST['citizenid']) ? trim($_POST['citizenid']) : '';
        
        // Validate passwords match
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Debug mode - enable error display
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            
            // Check if Player class can find the player
            $player = new Player();
            $playerData = $player->getPlayerByCitizenId($citizenid);
            
            if (!$playerData) {
                $error = 'Debug: CitizenID does not exist in game database. CSN: ' . $citizenid;
            } else {
                // Attempt registration
                $result = registerUser($username, $password, $email, $citizenid);
                
                if ($result['status'] === 'success') {
                    $success = $result['message'];
                    header("Refresh: 3; URL=login.php");
                } else {
                    $error = $result['message'];
                    // Add debug info
                    $error .= ' Debug info: ' . (isset($result['debug']) ? $result['debug'] : 'No debug info available.');
                }
            }
        }
    }
}

// Page title
$pageTitle = 'Register - ' . getSetting('site_name', 'FiveM Server Dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .register-card {
            background-color: #1e1e1e;
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
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
        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            width: 100%;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .footer {
            background-color: #121212;
            padding: 1rem 0;
            color: #a0aec0;
        }
        .csn-info {
            background-color: #2d2d2d;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php echo getSetting('site_name', 'FiveM Server Dashboard'); ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Register</h1>
                <p>Create an account to access the dashboard</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="register.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="citizenid" class="form-label">Citizen ID (CSN)</label>
                    <input type="text" class="form-control" id="citizenid" name="citizenid" required>
                    <div class="csn-info">
                        <i class="fas fa-info-circle"></i> 
                        You must provide your CSN to register. Use the /csn command in-game to get your CSN (Citizen Serial Number).
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Password must be at least 8 characters long.</small>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name', 'FiveM Server Dashboard'); ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Powered by PROHOSTVPS.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 