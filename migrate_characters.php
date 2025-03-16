<?php
// Include initialization file
require_once 'config/init.php';

// This script migrates existing users to the new character system
// It should be run once after updating the database schema

// Initialize classes
$db = new Database();
$user = new User();

// Get all users
$allUsers = $db->getAll("SELECT id, player_id FROM website_users WHERE player_id IS NOT NULL");

if (empty($allUsers)) {
    echo "No users found with player_id. Nothing to migrate.\n";
    exit;
}

echo "Found " . count($allUsers) . " users with player_id to migrate.\n";

// Initialize Player class
$player = new Player();

// Start migration
$success = 0;
$failed = 0;

foreach ($allUsers as $userData) {
    echo "Processing user ID: " . $userData['id'] . " with player_id: " . $userData['player_id'] . "... ";
    
    // Get player data
    $playerData = $player->getPlayerById($userData['player_id']);
    
    if (!$playerData || !isset($playerData['citizenid'])) {
        echo "FAILED - Invalid player data\n";
        $failed++;
        continue;
    }
    
    $citizenId = $playerData['citizenid'];
    
    // Check if character already exists for this user
    $existingChar = $db->getSingle(
        "SELECT id FROM user_characters WHERE user_id = ? AND citizenid = ?", 
        [$userData['id'], $citizenId]
    );
    
    if ($existingChar) {
        echo "SKIPPED - Character already migrated\n";
        continue;
    }
    
    // Add character
    $result = $user->addCharacter($userData['id'], $citizenId, true);
    
    if ($result) {
        echo "SUCCESS - Added character: " . $citizenId . "\n";
        $success++;
    } else {
        echo "FAILED - Could not add character\n";
        $failed++;
    }
}

echo "\n===== Migration Complete =====\n";
echo "Successfully migrated: " . $success . " users\n";
echo "Failed migrations: " . $failed . " users\n";
echo "================================\n";
?> 