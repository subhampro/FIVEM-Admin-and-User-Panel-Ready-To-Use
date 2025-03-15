<?php
// This file is included from api/index.php

// Initialize User class
$user = new User();

// Check request method
switch ($method) {
    case 'GET':
        // Handle GET requests
        handleUserGetRequest($segments, $params, $db, $user, $logger);
        break;
    
    case 'POST':
        // Handle POST requests (create/update)
        handleUserPostRequest($segments, $json, $db, $user, $logger);
        break;
    
    case 'PUT':
        // Handle PUT requests (update)
        handleUserPutRequest($segments, $json, $db, $user, $logger);
        break;
    
    case 'DELETE':
        // Handle DELETE requests
        handleUserDeleteRequest($segments, $db, $user, $logger);
        break;
    
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Handle GET requests for users
 */
function handleUserGetRequest($segments, $params, $db, $user, $logger) {
    // Require authentication
    $authUserId = requireAuth($db);
    
    // Check if requesting own user data
    if (isset($segments[1]) && $segments[1] === 'me') {
        // Get current user data
        $userData = $user->getUserById($authUserId);
        
        if ($userData) {
            // Remove sensitive data
            unset($userData['password']);
            echo json_encode(['success' => true, 'user' => $userData]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
        return;
    }
    
    // Check if user is admin for other operations
    $userData = $user->getUserById($authUserId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if user ID is provided
    if (isset($segments[1]) && is_numeric($segments[1])) {
        // Get specific user
        $userId = (int)$segments[1];
        $userData = $user->getUserById($userId);
        
        if ($userData) {
            // Remove sensitive data
            unset($userData['password']);
            echo json_encode(['success' => true, 'user' => $userData]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    } else {
        // Get all users or filter by parameters
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $search = isset($params['search']) ? $params['search'] : '';
        
        $users = $user->getUsers($page, $limit, $search);
        $total = $user->getTotalUsers($search);
        
        // Remove sensitive data
        foreach ($users as &$u) {
            unset($u['password']);
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
}

/**
 * Handle POST requests for users
 */
function handleUserPostRequest($segments, $json, $db, $user, $logger) {
    // Check if it's a registration request
    if (isset($segments[1]) && $segments[1] === 'register') {
        // Handle registration
        if (!isset($json['username']) || !isset($json['email']) || !isset($json['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email, and password are required']);
            return;
        }
        
        $username = $json['username'];
        $email = $json['email'];
        $password = $json['password'];
        
        // Validate inputs
        if (strlen($username) < 3) {
            http_response_code(400);
            echo json_encode(['error' => 'Username must be at least 3 characters']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }
        
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }
        
        // Check if username or email already exists
        if ($user->isUsernameTaken($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username already taken']);
            return;
        }
        
        if ($user->isEmailTaken($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already in use']);
            return;
        }
        
        // Register new user
        $userId = $user->register($username, $email, $password);
        
        if ($userId) {
            $logger->log('New user registered via API', 'api_registration', $userId);
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $userId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register user']);
        }
        
        return;
    }
    
    // For other operations, require authentication and admin role
    $authUserId = requireAuth($db);
    $userData = $user->getUserById($authUserId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if we're updating an existing user
    if (isset($segments[1]) && is_numeric($segments[1])) {
        $userId = (int)$segments[1];
        
        // Update user
        $result = $user->updateUser($userId, $json);
        
        if ($result) {
            $logger->log('Updated user via API (ID: ' . $userId . ')', 'api_action', $authUserId);
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update user']);
        }
    } else {
        // Creating a new user
        
        // Make sure all required fields are present
        if (!isset($json['username']) || !isset($json['email']) || !isset($json['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email, and password are required']);
            return;
        }
        
        $username = $json['username'];
        $email = $json['email'];
        $password = $json['password'];
        $isAdmin = isset($json['is_admin']) ? (int)$json['is_admin'] : 0;
        
        // Validate inputs
        if (strlen($username) < 3) {
            http_response_code(400);
            echo json_encode(['error' => 'Username must be at least 3 characters']);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            return;
        }
        
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }
        
        // Check if username or email already exists
        if ($user->isUsernameTaken($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username already taken']);
            return;
        }
        
        if ($user->isEmailTaken($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already in use']);
            return;
        }
        
        // Create new user
        $newUserId = $user->createUser($username, $email, $password, $isAdmin);
        
        if ($newUserId) {
            $logger->log('Created new user via API (ID: ' . $newUserId . ')', 'api_action', $authUserId);
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $newUserId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user']);
        }
    }
}

/**
 * Handle PUT requests for users
 */
function handleUserPutRequest($segments, $json, $db, $user, $logger) {
    // Require authentication
    $authUserId = requireAuth($db);
    
    // Check if updating own user data
    if (isset($segments[1]) && $segments[1] === 'me') {
        // Update current user data
        $result = $user->updateUser($authUserId, $json);
        
        if ($result) {
            $logger->log('User updated own profile via API', 'api_action', $authUserId);
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update profile']);
        }
        return;
    }
    
    // For other operations, require admin role
    $userData = $user->getUserById($authUserId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if user ID is provided
    if (!isset($segments[1]) || !is_numeric($segments[1])) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
        return;
    }
    
    $userId = (int)$segments[1];
    
    // Update user
    $result = $user->updateUser($userId, $json);
    
    if ($result) {
        $logger->log('Updated user via API (ID: ' . $userId . ')', 'api_action', $authUserId);
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update user']);
    }
}

/**
 * Handle DELETE requests for users
 */
function handleUserDeleteRequest($segments, $db, $user, $logger) {
    // Require authentication and admin role
    $authUserId = requireAuth($db);
    $userData = $user->getUserById($authUserId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if user ID is provided
    if (!isset($segments[1]) || !is_numeric($segments[1])) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required']);
        return;
    }
    
    $userId = (int)$segments[1];
    
    // Don't allow deleting yourself
    if ($userId === $authUserId) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete your own account']);
        return;
    }
    
    // Delete user
    $result = $user->deleteUser($userId);
    
    if ($result) {
        $logger->log('Deleted user via API (ID: ' . $userId . ')', 'api_action', $authUserId);
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user']);
    }
} 