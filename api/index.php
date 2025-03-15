<?php
require_once '../config/init.php';

// Set content type to JSON
header('Content-Type: application/json');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request path
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$basePath = '/api/';
$path = trim(substr($requestUri, strpos($requestUri, $basePath) + strlen($basePath)), '/');
$segments = explode('/', $path);

// Get the API endpoint
$endpoint = isset($segments[0]) ? $segments[0] : '';

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get query parameters
$params = $_GET;

// Get request body
$body = file_get_contents('php://input');
$json = json_decode($body, true);

// Check if JSON is valid
if ($body && $json === null) {
    // Invalid JSON
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON provided']);
    exit;
}

// Initialize database
$db = new Database();

// Initialize logger
$logger = new Logger();

// Route to appropriate API endpoint
switch ($endpoint) {
    case 'players':
        require_once 'players.php';
        break;
    
    case 'users':
        require_once 'users.php';
        break;
    
    case 'auth':
        // Handle authentication
        handleAuthRequest($method, $params, $json, $db, $logger);
        break;
    
    default:
        // API endpoint not found
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
        break;
}

/**
 * Handle authentication requests
 */
function handleAuthRequest($method, $params, $json, $db, $logger) {
    if ($method === 'POST' && isset($json['action'])) {
        switch ($json['action']) {
            case 'login':
                // Handle login
                if (!isset($json['username']) || !isset($json['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username and password are required']);
                    return;
                }
                
                $username = $json['username'];
                $password = $json['password'];
                
                // Use User class for authentication
                $user = new User();
                $result = $user->login($username, $password);
                
                if ($result['success']) {
                    // Generate API token
                    $token = bin2hex(random_bytes(32));
                    $userId = $result['user_id'];
                    
                    // Store token in database
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $db->query('INSERT INTO api_tokens (user_id, token, expires_at) VALUES (?, ?, ?)', [$userId, $token, $expiresAt]);
                    
                    // Log login
                    $logger->log('User logged in via API', 'api_login', $userId);
                    
                    // Return token
                    echo json_encode([
                        'success' => true,
                        'token' => $token,
                        'expires_at' => $expiresAt,
                        'user_id' => $userId
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid username or password']);
                }
                break;
                
            case 'logout':
                // Handle logout
                $token = getAuthToken();
                
                if ($token) {
                    // Invalidate token
                    $db->query('DELETE FROM api_tokens WHERE token = ?', [$token]);
                    echo json_encode(['success' => true]);
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Not authenticated']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed or missing action parameter']);
    }
}

/**
 * Get auth token from request headers
 */
function getAuthToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return false;
}

/**
 * Verify API token and get user ID
 */
function verifyApiToken($db) {
    $token = getAuthToken();
    
    if (!$token) {
        return false;
    }
    
    // Check if token exists and is valid
    $result = $db->query('SELECT user_id FROM api_tokens WHERE token = ? AND expires_at > NOW()', [$token]);
    
    if ($result && count($result) > 0) {
        return $result[0]['user_id'];
    }
    
    return false;
}

/**
 * Require authentication
 */
function requireAuth($db) {
    $userId = verifyApiToken($db);
    
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    return $userId;
} 