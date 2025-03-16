<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level2');

// Initialize
$admin = new Admin();
$logger = new Logger();
$player = new Player();

// Process form submission
$message = '';
$messageType = '';
$importResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid form submission. Please try again.');
        redirect('import_all_players.php');
    }
    
    // Get game database connection
    $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
    
    if ($gameDb->connect_error) {
        $message = "Failed to connect to game database: " . $gameDb->connect_error;
        $messageType = 'danger';
    } else {
        // Get all players from game database
        $gamePlayers = [];
        $result = $gameDb->query("SELECT * FROM players");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $gamePlayers[] = $row;
            }
            
            // Initialize counters
            $importedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            // Process players
            foreach ($gamePlayers as $gamePlayer) {
                // Check if player already exists in website database
                $existingPlayer = $player->getPlayerByCitizenId($gamePlayer['citizenid']);
                
                if ($existingPlayer) {
                    // Player exists, update if needed
                    $updated = $player->updatePlayer($existingPlayer['id'], [
                        'license' => $gamePlayer['license'] ?? '',
                        'name' => $gamePlayer['charinfo'] ? json_decode($gamePlayer['charinfo'], true)['firstname'] . ' ' . json_decode($gamePlayer['charinfo'], true)['lastname'] : $gamePlayer['name'] ?? 'Unknown',
                        'steam_id' => $gamePlayer['steam'] ?? ''
                    ]);
                    
                    if ($updated) {
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // Player doesn't exist, import
                    $charInfo = json_decode($gamePlayer['charinfo'] ?? '{}', true);
                    $name = isset($charInfo['firstname']) && isset($charInfo['lastname']) 
                        ? $charInfo['firstname'] . ' ' . $charInfo['lastname'] 
                        : ($gamePlayer['name'] ?? 'Unknown');
                    
                    $playerId = $player->createPlayer([
                        'citizenid' => $gamePlayer['citizenid'],
                        'license' => $gamePlayer['license'] ?? '',
                        'name' => $name,
                        'job' => $gamePlayer['job'] ?? 'unemployed',
                        'steam_id' => $gamePlayer['steam'] ?? ''
                    ]);
                    
                    if ($playerId) {
                        $importedCount++;
                    } else {
                        $errorCount++;
                    }
                }
            }
            
            // Close game database connection
            $gameDb->close();
            
            // Set import results
            $importResults = [
                'total' => count($gamePlayers),
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount
            ];
            
            // Log the action
            $logger->logAction(
                $_SESSION['user_id'],
                'import_all_players',
                "Imported players from game database: {$importedCount} imported, {$updatedCount} updated, {$skippedCount} skipped, {$errorCount} errors"
            );
            
            $message = "Import completed. {$importedCount} players imported, {$updatedCount} updated, {$skippedCount} skipped, {$errorCount} errors.";
            $messageType = 'success';
        } else {
            $message = "Failed to get players from game database: " . $gameDb->error;
            $messageType = 'danger';
        }
    }
}

// Page title
$pageTitle = 'Import All Players - Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-panel">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Import All Players</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php $flashMessage = getFlashMessage(); ?>
    <?php if ($flashMessage): ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?>" role="alert">
        <?php echo $flashMessage['message']; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Players from Game Database</h5>
        </div>
        <div class="card-body">
            <p>This tool will import all players from the game database (elapsed2_0) into the website database. It will:</p>
            <ul>
                <li>Import new players that don't exist in the website database</li>
                <li>Update existing players with the latest information</li>
                <li>Skip players that don't need updates</li>
            </ul>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Make sure you have a backup of your website database before proceeding.
            </div>
            
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="d-grid gap-2">
                    <button type="submit" name="import" class="btn btn-primary">
                        <i class="fas fa-database"></i> Start Import
                    </button>
                </div>
            </form>
            
            <?php if (!empty($importResults)): ?>
            <div class="mt-4">
                <h5>Import Results</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th>Total Players</th>
                            <td><?php echo $importResults['total']; ?></td>
                        </tr>
                        <tr>
                            <th>Imported</th>
                            <td><?php echo $importResults['imported']; ?></td>
                        </tr>
                        <tr>
                            <th>Updated</th>
                            <td><?php echo $importResults['updated']; ?></td>
                        </tr>
                        <tr>
                            <th>Skipped</th>
                            <td><?php echo $importResults['skipped']; ?></td>
                        </tr>
                        <tr>
                            <th>Errors</th>
                            <td><?php echo $importResults['errors']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Setup Automatic Sync</h5>
        </div>
        <div class="card-body">
            <p>You can set up automatic synchronization using a cron job. Add the following line to your crontab:</p>
            
            <div class="bg-dark text-light p-3 mb-3 rounded">
                <code>0 0 * * * php <?php echo realpath(__DIR__); ?>/cron_sync_players.php</code>
            </div>
            
            <p>This will run the sync process every day at midnight.</p>
            
            <div class="d-grid gap-2">
                <a href="sync_players.php" class="btn btn-info">
                    <i class="fas fa-sync"></i> Manual Sync Options
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 