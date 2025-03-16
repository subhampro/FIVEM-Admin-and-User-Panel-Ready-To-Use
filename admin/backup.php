<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level3');

// Initialize
$admin = new Admin();
$logger = new Logger();

// Process form submission
$message = '';
$messageType = '';
$backupFile = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !checkCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid form submission. Please try again.');
        redirect('backup.php');
    }
    
    // Determine which database to backup
    $database = isset($_POST['database']) ? $_POST['database'] : '';
    
    if ($database == 'website' || $database == 'game' || $database == 'both') {
        // Create backup directory if it doesn't exist
        $backupDir = __DIR__ . '/../backups';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Set backup filenames
        $timestamp = date('Y-m-d_H-i-s');
        
        $backupFiles = [];
        
        if ($database == 'website' || $database == 'both') {
            $websiteBackupFile = $backupDir . '/fivem_panel_' . $timestamp . '.sql';
            $backupFiles['website'] = $websiteBackupFile;
        }
        
        if ($database == 'game' || $database == 'both') {
            $gameBackupFile = $backupDir . '/elapsed2_0_' . $timestamp . '.sql';
            $backupFiles['game'] = $gameBackupFile;
        }
        
        // Create backup files
        $errors = [];
        
        foreach ($backupFiles as $dbType => $file) {
            $dbName = ($dbType == 'website') ? DB_NAME : 'elapsed2_0';
            
            // Use mysqldump to backup the database
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASSWORD),
                escapeshellarg($dbName),
                escapeshellarg($file)
            );
            
            // Execute the command
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                $errors[] = "Failed to backup $dbType database.";
            }
        }
        
        if (empty($errors)) {
            // Log the action
            $dbNames = implode(' and ', array_keys($backupFiles));
            $logger->logAction(
                $_SESSION['user_id'],
                'backup_database',
                "Created backup of $dbNames database(s)"
            );
            
            $message = "Backup completed successfully for " . $dbNames . " database(s).";
            $messageType = 'success';
            
            // Create a zip file if there are multiple backup files
            if (count($backupFiles) > 1) {
                $zipFile = $backupDir . '/backup_' . $timestamp . '.zip';
                $zip = new ZipArchive();
                
                if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                    foreach ($backupFiles as $type => $file) {
                        $zip->addFile($file, basename($file));
                    }
                    $zip->close();
                    
                    $backupFile = $zipFile;
                    $message .= " A zip file containing all backups has been created.";
                }
            } else {
                $backupFile = reset($backupFiles);
            }
        } else {
            $message = implode(' ', $errors);
            $messageType = 'danger';
        }
    } else {
        $message = "Please select a database to backup.";
        $messageType = 'danger';
    }
}

// Get existing backups
$backups = [];
$backupDir = __DIR__ . '/../backups';

if (file_exists($backupDir)) {
    $files = scandir($backupDir);
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && (strpos($file, '.sql') !== false || strpos($file, '.zip') !== false)) {
            $filePath = $backupDir . '/' . $file;
            $backups[] = [
                'name' => $file,
                'size' => round(filesize($filePath) / 1024 / 1024, 2), // size in MB
                'date' => date('Y-m-d H:i:s', filemtime($filePath)),
                'path' => $filePath
            ];
        }
    }
    
    // Sort backups by date (newest first)
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Page title
$pageTitle = 'Database Backup - Admin Dashboard';
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
        <h1>Database Backup</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
        <?php echo $message; ?>
        
        <?php if (!empty($backupFile) && file_exists($backupFile)): ?>
        <div class="mt-3">
            <a href="download_backup.php?file=<?php echo urlencode(basename($backupFile)); ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Download Backup
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php $flashMessage = getFlashMessage(); ?>
    <?php if ($flashMessage): ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?>" role="alert">
        <?php echo $flashMessage['message']; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Create New Backup</h5>
                </div>
                <div class="card-body">
                    <p>Create a backup of the database(s). This may take a while depending on the size of the database.</p>
                    
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Select Database</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="database" id="website" value="website" checked>
                                <label class="form-check-label" for="website">
                                    Website Database (<?php echo DB_NAME; ?>)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="database" id="game" value="game">
                                <label class="form-check-label" for="game">
                                    Game Database (elapsed2_0)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="database" id="both" value="both">
                                <label class="form-check-label" for="both">
                                    Both Databases
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="backup" class="btn btn-primary">
                                <i class="fas fa-download"></i> Create Backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Backup Information</h5>
                </div>
                <div class="card-body">
                    <p>This tool allows you to create backups of your databases. You can backup:</p>
                    <ul>
                        <li>Website Database (<?php echo DB_NAME; ?>): Contains all website users, settings, and website-related data.</li>
                        <li>Game Database (elapsed2_0): Contains all player data from the game server.</li>
                        <li>Both Databases: Creates backups of both databases and packages them in a ZIP file.</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Backups are stored in the <code>/backups</code> directory on the server.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($backups)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Existing Backups</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo $backup['name']; ?></td>
                                <td><?php echo $backup['size']; ?> MB</td>
                                <td><?php echo $backup['date']; ?></td>
                                <td>
                                    <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="delete_backup.php?file=<?php echo urlencode($backup['name']); ?>&csrf_token=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this backup?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 