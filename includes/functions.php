<?php
/**
 * Common utility functions for the website
 */

/**
 * Sanitize input
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date/time
 * 
 * @param string $datetime Date/time string
 * @param string $format Format string
 * @return string Formatted date/time
 */
function formatDatetime($datetime, $format = 'Y-m-d H:i:s') {
    if (!$datetime) {
        return 'N/A';
    }
    
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Get time elapsed since a date
 * 
 * @param string $datetime Date/time string
 * @return string Time elapsed
 */
function timeElapsedSince($datetime) {
    if (!$datetime) {
        return 'N/A';
    }
    
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}

/**
 * Check if a string starts with a substring
 * 
 * @param string $haystack The string to search in
 * @param string $needle The substring to search for
 * @return bool True if haystack starts with needle, false otherwise
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if a string ends with a substring
 * 
 * @param string $haystack The string to search in
 * @param string $needle The substring to search for
 * @return bool True if haystack ends with needle, false otherwise
 */
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Format money with currency symbol
 * 
 * @param float|null $amount Amount to format
 * @param string $currencySymbol Currency symbol
 * @return string Formatted money string
 */
function formatMoney($amount, $currencySymbol = '$') {
    if ($amount === null) {
        return $currencySymbol . '0.00';
    }
    return $currencySymbol . number_format((float)$amount, 2, '.', ',');
}

/**
 * Get the current URL
 * 
 * @return string Current URL
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . $domainName;
}

/**
 * Check if a URL is active/current
 * 
 * @param string $url URL to check
 * @return bool True if URL is active, false otherwise
 */
function isActiveUrl($url) {
    $currentUrl = getCurrentUrl();
    $urlPath = parse_url($url, PHP_URL_PATH);
    $currentPath = parse_url($currentUrl, PHP_URL_PATH);
    
    return $urlPath === $currentPath;
}

/**
 * Add active class if URL is active
 * 
 * @param string $url URL to check
 * @param string $class Class to add if active
 * @return string Class string
 */
function activeClass($url, $class = 'active') {
    return isActiveUrl($url) ? $class : '';
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get a setting value
 * 
 * @param string $key Setting key
 * @param string $default Default value if setting is not found
 * @return string Setting value
 */
function getSetting($key, $default = '') {
    $admin = new Admin();
    $value = $admin->getSetting($key);
    
    return $value !== null ? $value : $default;
}

/**
 * Display a flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 * @return void
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null if no message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @param string $level Admin level to check (admin_level1, admin_level2, admin_level3)
 * @return bool True if user is admin, false otherwise
 */
function isAdmin($level = 'admin_level1') {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = new User();
    return $user->hasAdminLevel($_SESSION['user_id'], $level);
}

/**
 * Get current user
 * 
 * @return array|false User data or false if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = new User();
    return $user->getUserById($_SESSION['user_id']);
}

/**
 * Check CSRF token
 * 
 * @param string $token CSRF token
 * @return bool True if token is valid, false otherwise
 */
function checkCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}

/**
 * Convert JSON string to associative array
 * 
 * @param string $json JSON string
 * @return array Associative array
 */
function jsonToArray($json) {
    return json_decode($json, true) ?: [];
}

/**
 * Pretty print JSON
 * 
 * @param string $json JSON string
 * @return string Pretty printed JSON
 */
function prettyPrintJson($json) {
    $result = json_decode($json);
    return json_encode($result, JSON_PRETTY_PRINT);
}

/**
 * Log to file
 * 
 * @param string $message Message to log
 * @param string $type Log type
 * @return void
 */
function logToFile($message, $type = 'info') {
    $logDir = __DIR__ . '/../logs';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?> 