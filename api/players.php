<?php
// This file is included from api/index.php

// Initialize Player class
$player = new Player();

// Check request method
switch ($method) {
    case 'GET':
        // Handle GET requests
        handlePlayerGetRequest($segments, $params, $db, $player, $logger);
        break;
    
    case 'POST':
        // Handle POST requests (create/update)
        handlePlayerPostRequest($segments, $json, $db, $player, $logger);
        break;
    
    case 'PUT':
        // Handle PUT requests (update)
        handlePlayerPutRequest($segments, $json, $db, $player, $logger);
        break;
    
    case 'DELETE':
        // Handle DELETE requests
        handlePlayerDeleteRequest($segments, $db, $player, $logger);
        break;
    
    default:
        // Method not allowed
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

/**
 * Handle GET requests for players
 */
function handlePlayerGetRequest($segments, $params, $db, $player, $logger) {
    // Require authentication
    $userId = requireAuth($db);
    
    // Check if player ID is provided
    if (isset($segments[1]) && is_numeric($segments[1])) {
        // Get specific player
        $playerId = (int)$segments[1];
        $playerData = $player->getPlayerById($playerId);
        
        if ($playerData) {
            echo json_encode(['success' => true, 'player' => $playerData]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Player not found']);
        }
    } else {
        // Get all players or filter by parameters
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $search = isset($params['search']) ? $params['search'] : '';
        
        $players = $player->getPlayers($page, $limit, $search);
        $total = $player->getTotalPlayers($search);
        
        echo json_encode([
            'success' => true,
            'players' => $players,
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
 * Handle POST requests for players
 */
function handlePlayerPostRequest($segments, $json, $db, $player, $logger) {
    // Require authentication and admin role
    $userId = requireAuth($db);
    
    // Check if user is admin
    $user = new User();
    $userData = $user->getUserById($userId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if we're updating an existing player
    if (isset($segments[1]) && is_numeric($segments[1])) {
        $playerId = (int)$segments[1];
        
        // Make sure all required fields are present
        if (!isset($json['steam_id']) || !isset($json['name']) || !isset($json['identifier'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Update player
        $result = $player->updatePlayer($playerId, $json);
        
        if ($result) {
            $logger->log('Updated player via API (ID: ' . $playerId . ')', 'api_action', $userId);
            echo json_encode(['success' => true, 'message' => 'Player updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update player']);
        }
    } else {
        // Creating a new player
        
        // Make sure all required fields are present
        if (!isset($json['steam_id']) || !isset($json['name']) || !isset($json['identifier'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Create new player
        $newPlayerId = $player->createPlayer($json);
        
        if ($newPlayerId) {
            $logger->log('Created new player via API (ID: ' . $newPlayerId . ')', 'api_action', $userId);
            echo json_encode([
                'success' => true,
                'message' => 'Player created successfully',
                'player_id' => $newPlayerId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create player']);
        }
    }
}

/**
 * Handle PUT requests for players
 */
function handlePlayerPutRequest($segments, $json, $db, $player, $logger) {
    // Require authentication and admin role
    $userId = requireAuth($db);
    
    // Check if user is admin
    $user = new User();
    $userData = $user->getUserById($userId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if player ID is provided
    if (!isset($segments[1]) || !is_numeric($segments[1])) {
        http_response_code(400);
        echo json_encode(['error' => 'Player ID is required']);
        return;
    }
    
    $playerId = (int)$segments[1];
    
    // Update player
    $result = $player->updatePlayer($playerId, $json);
    
    if ($result) {
        $logger->log('Updated player via API (ID: ' . $playerId . ')', 'api_action', $userId);
        echo json_encode(['success' => true, 'message' => 'Player updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update player']);
    }
}

/**
 * Handle DELETE requests for players
 */
function handlePlayerDeleteRequest($segments, $db, $player, $logger) {
    // Require authentication and admin role
    $userId = requireAuth($db);
    
    // Check if user is admin
    $user = new User();
    $userData = $user->getUserById($userId);
    
    if (!$userData || $userData['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized. Admin access required.']);
        return;
    }
    
    // Check if player ID is provided
    if (!isset($segments[1]) || !is_numeric($segments[1])) {
        http_response_code(400);
        echo json_encode(['error' => 'Player ID is required']);
        return;
    }
    
    $playerId = (int)$segments[1];
    
    // Delete player
    $result = $player->deletePlayer($playerId);
    
    if ($result) {
        $logger->log('Deleted player via API (ID: ' . $playerId . ')', 'api_action', $userId);
        echo json_encode(['success' => true, 'message' => 'Player deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete player']);
    }
} 