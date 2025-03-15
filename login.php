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

// Process login form
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
        $remember = isset($_POST['remember']) ? true : false;
        
        // Attempt login
        $result = loginUser($username, $password, $remember);
        
        if ($result['status'] === 'success') {
            $success = $result['message'];
            
            // Redirect after successful login
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
            
            if (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                // Only redirect to internal URLs
                $redirectUrl = parse_url($redirect);
                if (!isset($redirectUrl['host']) || $redirectUrl['host'] === $_SERVER['HTTP_HOST']) {
                    header("Refresh: 1; URL=" . $redirect);
                    exit;
                }
            }
            
            // Default redirects based on role
            if (isAdmin()) {
                header("Refresh: 1; URL=admin/index.php");
            } else {
                header("Refresh: 1; URL=user/index.php");
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Page title
$pageTitle = 'Login - ' . getSetting('site_name', 'FiveM Server Dashboard');
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
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-card {
            background-color: #1e1e1e;
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
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
        .form-check-input {
            background-color: #2d2d2d;
            border-color: #6c757d;
        }
        .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
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
                        <a class="nav-link active" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Login</h1>
                <p>Enter your credentials to access the dashboard</p>
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
            
            <?php if (isset($_GET['expired']) && $_GET['expired'] == 1): ?>
                <div class="alert alert-warning" role="alert">
                    Your session has expired. Please login again.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
                <div class="alert alert-info" role="alert">
                    You have been successfully logged out.
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
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
                    <p>Powered by FiveM</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 