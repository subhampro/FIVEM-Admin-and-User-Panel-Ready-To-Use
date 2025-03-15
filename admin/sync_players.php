<?php
// Include configuration and admin authorization
require_once '../config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

// Set error reporting to maximum
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Sync Players from Game Database</h1>
    <p class="mb-4">This tool will synchronize all players from the game database (elapsed2_0) to the website database (fivem_panel).</p>
    
    <?php
    // Process form submission
    if (isset($_POST['sync'])) {
        try {
            // Connect to game database
            $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
            if ($gameDb->connect_error) {
                echo '<div class="alert alert-danger">Failed to connect to game database: ' . $gameDb->connect_error . '</div>';
                exit;
            }
            
            // Connect to website database
            $webDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'fivem_panel');
            if ($webDb->connect_error) {
                echo '<div class="alert alert-danger">Failed to connect to website database: ' . $webDb->connect_error . '</div>';
                exit;
            }
            
            echo '<div class="alert alert-success">Database connections established successfully!</div>';
            
            // Set batch size and get offset
            $batchSize = 50;
            $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
            
            // Get total count
            $countResult = $gameDb->query("SELECT COUNT(*) as total FROM players");
            $totalPlayers = $countResult->fetch_assoc()['total'];
            
            // Calculate progress
            $progress = min(100, ($offset / $totalPlayers) * 100);
            
            echo '<div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Progress</h5>
                        <p>Processed ' . $offset . ' of ' . $totalPlayers . ' players</p>
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: ' . $progress . '%" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                  </div>';
            
            // Get batch of players from game database
            $result = $gameDb->query("SELECT * FROM players LIMIT $offset, $batchSize");
            
            if (!$result) {
                echo '<div class="alert alert-danger">Failed to get players from game database: ' . $gameDb->error . '</div>';
                exit;
            }
            
            // Disable foreign key checks
            $webDb->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Initialize counters
            $importedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            // Process each player
            echo '<div class="card mb-4">
                    <div class="card-header">Processing Players</div>
                    <div class="card-body">
                        <div class="log-container" style="max-height: 300px; overflow-y: auto;">';
            
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
                            echo '<p class="text-warning">⚠️ Updated ID for player ' . $citizenid . ' from ' . $existingPlayer['id'] . ' to ' . $player['id'] . '</p>';
                            $updatedCount++;
                        } else {
                            echo '<p class="text-danger">❌ Failed to update ID for player ' . $citizenid . ': ' . $webDb->error . '</p>';
                            $errorCount++;
                        }
                    } else {
                        echo '<p class="text-info">ℹ️ Player ' . $citizenid . ' already exists with matching ID</p>';
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
                        echo '<p class="text-success">✅ Imported player ' . $citizenid . ' (ID: ' . $player['id'] . ')</p>';
                        $importedCount++;
                    } else {
                        echo '<p class="text-danger">❌ Failed to import player ' . $citizenid . ': ' . $webDb->error . '</p>';
                        $errorCount++;
                    }
                }
            }
            
            echo '</div></div></div>';
            
            // Re-enable foreign key checks
            $webDb->query("SET FOREIGN_KEY_CHECKS = 1");
            
            // Show summary
            echo '<div class="card mb-4">
                    <div class="card-header">Batch Summary</div>
                    <div class="card-body">
                        <ul>
                            <li>Players imported: ' . $importedCount . '</li>
                            <li>Player IDs updated: ' . $updatedCount . '</li>
                            <li>Players skipped (already exist): ' . $skippedCount . '</li>
                            <li>Errors: ' . $errorCount . '</li>
                        </ul>
                    </div>
                  </div>';
            
            // Check if there are more players to process
            $newOffset = $offset + $batchSize;
            if ($newOffset < $totalPlayers) {
                echo '<form method="post" class="mb-4">
                        <input type="hidden" name="sync" value="1">
                        <input type="hidden" name="offset" value="' . $newOffset . '">
                        <button type="submit" class="btn btn-primary">Process Next Batch</button>
                      </form>';
            } else {
                echo '<div class="alert alert-success">All players have been processed!</div>';
                
                // Update auto increment
                $maxIdResult = $webDb->query("SELECT MAX(id) as max_id FROM players");
                if ($maxIdResult && $maxIdResult->num_rows > 0) {
                    $maxId = $maxIdResult->fetch_assoc()['max_id'];
                    $newAutoIncrement = $maxId + 1;
                    
                    $updateResult = $webDb->query("ALTER TABLE players AUTO_INCREMENT = $newAutoIncrement");
                    
                    if ($updateResult) {
                        echo '<div class="alert alert-success">Auto increment value updated to ' . $newAutoIncrement . '</div>';
                    } else {
                        echo '<div class="alert alert-danger">Failed to update auto increment value: ' . $webDb->error . '</div>';
                    }
                }
            }
            
            // Close database connections
            $gameDb->close();
            $webDb->close();
            
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    } else {
        // Show initial form
        ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Sync Options</h5>
                <form method="post">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="backup" id="backup" checked>
                        <label class="form-check-label" for="backup">Create backup before syncing</label>
                    </div>
                    <input type="hidden" name="sync" value="1">
                    <button type="submit" class="btn btn-primary">Start Sync Process</button>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Schedule Automatic Sync</div>
            <div class="card-body">
                <p>To automatically sync players every day, add this cron job to your server:</p>
                <pre class="bg-light p-3">0 0 * * * php <?php echo __DIR__; ?>/cron_sync_players.php</pre>
                <a href="create_cron_script.php" class="btn btn-secondary mt-2">Create Cron Script</a>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?> 