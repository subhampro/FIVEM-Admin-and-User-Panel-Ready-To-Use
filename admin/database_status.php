<?php
// Include initialization file
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level1');

// Initialize
$admin = new Admin();
$logger = new Logger();

// Get database status
function checkDatabaseConnection($host, $user, $password, $db) {
    $conn = @new mysqli($host, $user, $password, $db);
    $status = [
        'connected' => !$conn->connect_error,
        'error' => $conn->connect_error,
        'version' => null,
        'tables' => [],
        'size' => null
    ];
    
    if (!$conn->connect_error) {
        // Get version
        $result = $conn->query("SELECT VERSION() as version");
        if ($result) {
            $row = $result->fetch_assoc();
            $status['version'] = $row['version'];
        }
        
        // Get tables
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $table = $row[0];
                
                // Get table info
                $tableResult = $conn->query("SHOW TABLE STATUS LIKE '$table'");
                if ($tableResult && $tableRow = $tableResult->fetch_assoc()) {
                    $status['tables'][$table] = [
                        'rows' => $tableRow['Rows'],
                        'size' => round(($tableRow['Data_length'] + $tableRow['Index_length']) / 1024, 2),
                        'engine' => $tableRow['Engine'],
                        'collation' => $tableRow['Collation']
                    ];
                }
            }
        }
        
        // Get total size
        $result = $conn->query("SELECT SUM(data_length + index_length) / 1024 AS size FROM information_schema.TABLES WHERE table_schema = '$db'");
        if ($result) {
            $row = $result->fetch_assoc();
            $status['size'] = round($row['size'], 2);
        }
        
        $conn->close();
    }
    
    return $status;
}

// Check both databases
$webDbStatus = checkDatabaseConnection(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$gameDbStatus = checkDatabaseConnection(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');

// Log this action
$logger->logAction($_SESSION['user_id'], 'view_db_status', 'Viewed database status');

// Page title
$pageTitle = 'Database Status - Admin Dashboard';
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
        <h1>Database Status</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php $flashMessage = getFlashMessage(); ?>
    <?php if ($flashMessage): ?>
    <div class="alert alert-<?php echo $flashMessage['type']; ?>" role="alert">
        <?php echo $flashMessage['message']; ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Website Database Status -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Website Database (<?php echo DB_NAME; ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if ($webDbStatus['connected']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Connected
                        </div>
                        
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>MySQL Version</span>
                                <span><?php echo $webDbStatus['version']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Size</span>
                                <span><?php echo $webDbStatus['size']; ?> KB</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Tables</span>
                                <span><?php echo count($webDbStatus['tables']); ?></span>
                            </li>
                        </ul>
                        
                        <h6>Tables</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Rows</th>
                                        <th>Size (KB)</th>
                                        <th>Engine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($webDbStatus['tables'] as $table => $info): ?>
                                        <tr>
                                            <td><?php echo $table; ?></td>
                                            <td><?php echo $info['rows']; ?></td>
                                            <td><?php echo $info['size']; ?></td>
                                            <td><?php echo $info['engine']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Connection Failed
                            <p class="mt-2 mb-0">Error: <?php echo $webDbStatus['error']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Game Database Status -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Game Database (elapsed2_0)</h5>
                </div>
                <div class="card-body">
                    <?php if ($gameDbStatus['connected']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Connected
                        </div>
                        
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>MySQL Version</span>
                                <span><?php echo $gameDbStatus['version']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Size</span>
                                <span><?php echo $gameDbStatus['size']; ?> KB</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Tables</span>
                                <span><?php echo count($gameDbStatus['tables']); ?></span>
                            </li>
                        </ul>
                        
                        <h6>Tables</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Rows</th>
                                        <th>Size (KB)</th>
                                        <th>Engine</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gameDbStatus['tables'] as $table => $info): ?>
                                        <tr>
                                            <td><?php echo $table; ?></td>
                                            <td><?php echo $info['rows']; ?></td>
                                            <td><?php echo $info['size']; ?></td>
                                            <td><?php echo $info['engine']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Connection Failed
                            <p class="mt-2 mb-0">Error: <?php echo $gameDbStatus['error']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 