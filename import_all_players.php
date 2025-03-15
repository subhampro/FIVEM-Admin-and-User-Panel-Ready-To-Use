<?php
// Include configuration
require_once 'config/config.php';

// Set error reporting to maximum
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Import All Players</h1>";
echo "<p>This script will import all players from the game database to the website database.</p>";

// Add confirmation step
if (!isset($_POST['confirm'])) {
    echo "<form method='post'>";
    echo "<input type='hidden' name='confirm' value='1'>";
    echo "<button type='submit' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer;'>Start Import</button>";
    echo "</form>";
    exit;
}

// Connect to databases
try {
    // Connect to game database
    $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
    if ($gameDb->connect_error) {
        die("<p style='color:red;'>Failed to connect to game database: " . $gameDb->connect_error . "</p>");
    }
    echo "<p style='color:green;'>Connected to game database (elapsed2_0)!</p>";
    
    // Connect to website database
    $webDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'fivem_panel');
    if ($webDb->connect_error) {
        die("<p style='color:red;'>Failed to connect to website database: " . $webDb->connect_error . "</p>");
    }
    echo "<p style='color:green;'>Connected to website database (fivem_panel)!</p>";
    
    // Set batch size for processing
    $batchSize = 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = $batchSize;
    
    // Get total count first
    $countResult = $gameDb->query("SELECT COUNT(*) as total FROM players");
    $totalPlayers = $countResult->fetch_assoc()['total'];
    
    echo "<p>Total players in game database: " . $totalPlayers . "</p>";
    echo "<p>Processing batch: " . ($offset + 1) . " to " . min($offset + $limit, $totalPlayers) . "</p>";
    
    // Get players from game database
    $result = $gameDb->query("SELECT * FROM players LIMIT $offset, $limit");
    
    if (!$result) {
        die("<p style='color:red;'>Failed to get players from game database: " . $gameDb->error . "</p>");
    }
    
    // Disable foreign key checks for faster import
    $webDb->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Count statistics
    $importedCount = 0;
    $updatedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    // Process each player
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    while ($player = $result->fetch_assoc()) {
        $citizenid = $player['citizenid'];
        
        // Check if player already exists in website database
        $checkResult = $webDb->query("SELECT id FROM players WHERE citizenid = '$citizenid'");
        
        if ($checkResult && $checkResult->num_rows > 0) {
            // Player already exists, update the ID if needed
            $existingPlayer = $checkResult->fetch_assoc();
            
            if ($existingPlayer['id'] != $player['id']) {
                // IDs don't match, update the ID
                $updateResult = $webDb->query("UPDATE players SET id = {$player['id']} WHERE id = {$existingPlayer['id']} AND citizenid = '$citizenid'");
                
                if ($updateResult) {
                    echo "<p style='color:orange;'>⚠️ Updated ID for player $citizenid from {$existingPlayer['id']} to {$player['id']}</p>";
                    $updatedCount++;
                } else {
                    echo "<p style='color:red;'>❌ Failed to update ID for player $citizenid: " . $webDb->error . "</p>";
                    $errorCount++;
                }
            } else {
                echo "<p style='color:blue;'>ℹ️ Player $citizenid already exists with matching ID</p>";
                $skippedCount++;
            }
        } else {
            // Player doesn't exist, import it
            // Only include specific columns to avoid errors
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
                echo "<p style='color:green;'>✅ Imported player $citizenid (ID: {$player['id']})</p>";
                $importedCount++;
            } else {
                echo "<p style='color:red;'>❌ Failed to import player $citizenid: " . $webDb->error . "</p>";
                $errorCount++;
            }
        }
    }
    echo "</div>";
    
    // Re-enable foreign key checks
    $webDb->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Show summary
    echo "<h2>Import Summary</h2>";
    echo "<ul>";
    echo "<li>Total players processed: " . ($importedCount + $updatedCount + $skippedCount + $errorCount) . "</li>";
    echo "<li>Players imported: $importedCount</li>";
    echo "<li>Player IDs updated: $updatedCount</li>";
    echo "<li>Players skipped (already exist): $skippedCount</li>";
    echo "<li>Errors: $errorCount</li>";
    echo "</ul>";
    
    // Show pagination if there are more players to process
    if ($offset + $limit < $totalPlayers) {
        $nextOffset = $offset + $limit;
        echo "<p>There are more players to process.</p>";
        echo "<a href='import_all_players.php?offset=$nextOffset' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; display: inline-block;'>Process Next Batch</a>";
    } else {
        echo "<p style='color:green;'>All players have been processed!</p>";
    }
    
    // Add button to update auto increment values
    echo "<h2>Maintenance Options</h2>";
    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='action' value='update_auto_increment'>";
    echo "<button type='submit' style='background-color: #ff9800; color: white; padding: 10px 15px; border: none; cursor: pointer;'>Update Auto Increment Values</button>";
    echo "</form>";
    
    // Handle maintenance actions
    if (isset($_POST['action']) && $_POST['action'] === 'update_auto_increment') {
        // Find the highest ID in the players table
        $maxIdResult = $webDb->query("SELECT MAX(id) as max_id FROM players");
        if ($maxIdResult && $maxIdResult->num_rows > 0) {
            $maxId = $maxIdResult->fetch_assoc()['max_id'];
            $newAutoIncrement = $maxId + 1;
            
            // Update the auto increment value
            $updateResult = $webDb->query("ALTER TABLE players AUTO_INCREMENT = $newAutoIncrement");
            
            if ($updateResult) {
                echo "<p style='color:green;'>✅ Auto increment value updated to $newAutoIncrement</p>";
            } else {
                echo "<p style='color:red;'>❌ Failed to update auto increment value: " . $webDb->error . "</p>";
            }
        }
    }
    
    // Close database connections
    $gameDb->close();
    $webDb->close();
    
    // Add links to other pages
    echo "<hr>";
    echo "<p><a href='register.php'>Go to Registration Page</a></p>";
    
    // Add option to add this as a cron job
    echo "<h2>Setup Automatic Sync</h2>";
    echo "<p>To have this script run automatically every day to keep the databases in sync, add the following cron job:</p>";
    echo "<pre>0 0 * * * php " . __DIR__ . "/import_all_players.php > /dev/null 2>&1</pre>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?> 