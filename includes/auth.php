<?php
/**
 * Authentication functions
 */

/**
 * Register a new user
 * 
 * @param string $username Username
 * @param string $password Password
 * @param string $email Email
 * @param string $citizenid Citizen ID
 * @return array Result array with status and message
 */
function registerUser($username, $password, $email, $citizenid) {
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($citizenid)) {
        return [
            'status' => 'error',
            'message' => 'All fields are required.'
        ];
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'status' => 'error',
            'message' => 'Invalid email address.'
        ];
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        return [
            'status' => 'error',
            'message' => 'Password must be at least 8 characters long.'
        ];
    }
    
    // Initialize User class
    $user = new User();
    
    // Check if player exists
    $player = new Player();
    if (!$player->playerExists($citizenid)) {
        return [
            'status' => 'error',
            'message' => 'Player with this Citizen ID does not exist. Please enter your correct CSN (you can get your CSN by /csn command in game).'
        ];
    }
    
    // Get player data to verify it exists and has required fields
    $playerData = $player->getPlayerByCitizenId($citizenid);
    
    if (!$playerData) {
        return [
            'status' => 'error',
            'message' => 'Could not retrieve player data for this CSN.',
            'debug' => 'No player data returned for citizenid: ' . $citizenid
        ];
    }
    
    if (!isset($playerData['id'])) {
        return [
            'status' => 'error',
            'message' => 'Player record is incomplete.',
            'debug' => 'Player data missing ID field: ' . print_r($playerData, true)
        ];
    }
    
    // Check if citizenid already registered
    if ($user->getUserByCitizenId($citizenid)) {
        return [
            'status' => 'error',
            'message' => 'This Citizen ID is already registered. Please contact an administrator if you believe this is an error.'
        ];
    }
    
    // Check if username already exists
    if ($user->getUserByUsername($username)) {
        return [
            'status' => 'error',
            'message' => 'Username already exists. Please choose a different username.'
        ];
    }
    
    // Register user
    $result = $user->register($username, $password, $email, $citizenid);
    
    if ($result) {
        return [
            'status' => 'success',
            'message' => 'Registration successful. You can now login.',
            'user_id' => $result
        ];
    } else {
        // Try to get more debug info
        $lastError = error_get_last();
        $debugInfo = $lastError ? $lastError['message'] : 'No specific error information available';
        
        return [
            'status' => 'error',
            'message' => 'Registration failed. Please try again later.',
            'debug' => 'User registration failed. Debug: ' . $debugInfo
        ];
    }
}

/**
 * Login user
 * 
 * @param string $username Username
 * @param string $password Password
 * @param bool $remember Remember me
 * @return array Result array with status and message
 */
function loginUser($username, $password, $remember = false) {
    // Validate input
    if (empty($username) || empty($password)) {
        return [
            'status' => 'error',
            'message' => 'Please enter both username and password.'
        ];
    }
    
    // Initialize User class
    $user = new User();
    
    // Attempt login
    $userData = $user->login($username, $password);
    
    if ($userData) {
        // Set session variables
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['last_active'] = time();
        
        // Set is_admin flag based on role
        $_SESSION['is_admin'] = ($userData['role'] !== 'user') ? 1 : 0;
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = generateRandomString(32);
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store token in database (you might want to implement this)
            // $user->storeRememberToken($userData['id'], $token, $expiry);
            
            // Set cookie
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
        }
        
        // Log successful login
        logToFile("User {$username} logged in successfully", 'auth');
        
        return [
            'status' => 'success',
            'message' => 'Login successful. Redirecting...',
            'user' => $userData
        ];
    } else {
        // Log failed login attempt
        logToFile("Failed login attempt for username: {$username}", 'auth');
        
        return [
            'status' => 'error',
            'message' => 'Invalid username or password.'
        ];
    }
}

/**
 * Logout user
 * 
 * @return void
 */
function logoutUser() {
    // Log the logout
    if (isset($_SESSION['username'])) {
        logToFile("User {$_SESSION['username']} logged out", 'auth');
    }
    
    // Clear session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear remember me cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Check if session has expired
 * 
 * @return bool True if session has expired, false otherwise
 */
function isSessionExpired() {
    if (!isset($_SESSION['last_active'])) {
        return true;
    }
    
    $sessionLifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 1800; // 30 minutes default
    
    return (time() - $_SESSION['last_active']) > $sessionLifetime;
}

/**
 * Update session last active time
 * 
 * @return void
 */
function updateSessionActivity() {
    $_SESSION['last_active'] = time();
}

/**
 * Require user to be logged in
 * 
 * @param string $redirect URL to redirect to if not logged in
 * @return void
 */
function requireLogin($redirect = '/login.php') {
    if (!isLoggedIn() || isSessionExpired()) {
        // If session expired, log out properly
        if (isSessionExpired() && isLoggedIn()) {
            logoutUser();
            $redirect .= '?expired=1';
        }
        
        redirect($redirect);
    }
    
    // Update last activity time
    updateSessionActivity();
}

/**
 * Require user to be admin
 * 
 * @param string $level Admin level to check (admin_level1, admin_level2, admin_level3)
 * @param string $redirect URL to redirect to if not admin
 * @return void
 */
function requireAdmin($level = 'admin_level1', $redirect = '/index.php') {
    requireLogin('/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    
    if (!isAdmin($level)) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect($redirect);
    }
}

/**
 * Get admin level name
 * 
 * @param string $level Admin level
 * @return string Admin level name
 */
function getAdminLevelName($level) {
    $levels = [
        'admin_level1' => 'View Only Admin',
        'admin_level2' => 'Edit Admin',
        'admin_level3' => 'Super Admin'
    ];
    
    return isset($levels[$level]) ? $levels[$level] : 'Unknown';
}
?> 