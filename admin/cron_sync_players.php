<?php
// Disable output buffering
ob_end_clean();

// Script should only be run from command line or cron
if (php_sapi_name() !== 'cli') {
    die("This script should only be run from command line or cron.");
}

// Include configuration
require_once dirname(__DIR__) . '/config/config.php';

// Set error reporting and logging
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/cron_sync_players.log');

// Log start of script
error_log("[" . date("Y-m-d H:i:s") . "] Starting player sync cron job");

try {
    // Connect to game database
    $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
    if ($gameDb->connect_error) {
        throw new Exception("Failed to connect to game database: " . $gameDb->connect_error);
    }
    
    // Connect to website database
    $webDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'fivem_panel');
    if ($webDb->connect_error) {
        throw new Exception("Failed to connect to website database: " . $webDb->connect_error);
    }
    
    error_log("[" . date("Y-m-d H:i:s") . "] Database connections established successfully");
    
    // Get players from game database
    $result = $gameDb->query("SELECT * FROM players");
    
    if (!$result) {
        throw new Exception("Failed to get players from game database: " . $gameDb->error);
    }
    
    // Disable foreign key checks
    $webDb->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Initialize counters
    $importedCount = 0;
    $updatedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    // Process each player
    while ($player = $result->fetch_assoc()) {
        $citizenid = $player['citizenid'];
        
        // Check if player already exists
        $checkResult = $webDb->query("SELECT id FROM players WHERE citizenid = '$citizenid'");
        
        if ($checkResult && $checkResult->num_rows > 0) {
            // Player exists
            $existingPlayer = $checkResult->fetch_assoc();
            
            if ($existingPlayer['id'] != $player['id']) {
                // IDs don't match, update
                $updateResult = $webDb->query("UPDATE players SET id = {$player['id']} WHERE id = {$existingPlayer['id']} AND citizenid = '$citizenid'");
                
                if ($updateResult) {
                    $updatedCount++;
                } else {
                    error_log("[" . date("Y-m-d H:i:s") . "] Failed to update ID for player $citizenid: " . $webDb->error);
                    $errorCount++;
                }
            } else {
                $skippedCount++;
            }
        } else {
            // Player doesn't exist, import
            $columns = ['id', 'citizenid', 'license', 'name'];
            $values = [];
            
            foreach ($columns as $column) {
                if (isset($player[$column]) && $player[$column] !== null) {
                    $values[] = "'" . $webDb->real_escape_string($player[$column]) . "'";
                } else {
                    $values[] = "NULL";
                }
            }
            
            $query = "INSERT INTO players (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
            $importResult = $webDb->query($query);
            
            if ($importResult) {
                $importedCount++;
            } else {
                error_log("[" . date("Y-m-d H:i:s") . "] Failed to import player $citizenid: " . $webDb->error);
                $errorCount++;
            }
        }
    }
    
    // Re-enable foreign key checks
    $webDb->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Update auto increment
    $maxIdResult = $webDb->query("SELECT MAX(id) as max_id FROM players");
    if ($maxIdResult && $maxIdResult->num_rows > 0) {
        $maxId = $maxIdResult->fetch_assoc()['max_id'];
        $newAutoIncrement = $maxId + 1;
        
        $webDb->query("ALTER TABLE players AUTO_INCREMENT = $newAutoIncrement");
    }
    
    // Log results
    error_log("[" . date("Y-m-d H:i:s") . "] Sync completed - Imported: $importedCount, Updated: $updatedCount, Skipped: $skippedCount, Errors: $errorCount");
    
    // Close database connections
    $gameDb->close();
    $webDb->close();
    
} catch (Exception $e) {
    error_log("[" . date("Y-m-d H:i:s") . "] Error: " . $e->getMessage());
}

// Log end of script
error_log("[" . date("Y-m-d H:i:s") . "] Player sync cron job completed");
?> 