<?php
require_once '../config/init.php';

// Require admin privileges
requireAdmin('admin_level1');

$admin = new Admin();
$pendingChanges = new PendingChanges();
$logger = new Logger();

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'approve' && $id > 0) {
        // Approve the change
        $result = $pendingChanges->approvePendingChange($id, $_SESSION['user_id']);
        if ($result) {
            $logger->logAction($_SESSION['user_id'], 'approve_change', 'Approved pending change (ID: ' . $id . ')');
            setFlashMessage('success', 'Change approved successfully.');
        } else {
            setFlashMessage('danger', 'Failed to approve change.');
        }
        redirect('pending_changes.php');
        exit;
    } elseif ($action === 'reject' && $id > 0) {
        // Reject the change
        $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
        $result = $pendingChanges->rejectPendingChange($id, $_SESSION['user_id'], $reason);
        if ($result) {
            $logger->logAction($_SESSION['user_id'], 'reject_change', 'Rejected pending change (ID: ' . $id . ')');
            setFlashMessage('success', 'Change rejected successfully.');
        } else {
            setFlashMessage('danger', 'Failed to reject change.');
        }
        redirect('pending_changes.php');
        exit;
    }
}

// Get all pending changes
$changes = $pendingChanges->getAllPendingChanges();

// Page title
$pageTitle = 'Pending Changes';

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="players.php">
                            <i class="fas fa-gamepad"></i> Players
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pending_changes.php">
                            <i class="fas fa-clock"></i> Pending Changes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logs.php">
                            <i class="fas fa-list"></i> Logs
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pending Changes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($changes)): ?>
                        <div class="alert alert-info">
                            No pending changes found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Data</th>
                                        <th>Requested At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($changes as $change): ?>
                                        <tr>
                                            <td><?php echo $change['id']; ?></td>
                                            <td><?php echo htmlspecialchars($change['username']); ?></td>
                                            <td><?php echo htmlspecialchars($change['change_type']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#changeDataModal<?php echo $change['id']; ?>">
                                                    View Data
                                                </button>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($change['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success approve-change-btn" data-change-id="<?php echo $change['id']; ?>">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger reject-change-btn" data-change-id="<?php echo $change['id']; ?>">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Change Data Modal -->
                                        <div class="modal fade" id="changeDataModal<?php echo $change['id']; ?>" tabindex="-1" aria-labelledby="changeDataModalLabel<?php echo $change['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="changeDataModalLabel<?php echo $change['id']; ?>">Change Data</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>Current Data:</h6>
                                                        <pre><?php echo htmlspecialchars($change['current_data']); ?></pre>
                                                        <hr>
                                                        <h6>Requested Changes:</h6>
                                                        <pre><?php echo htmlspecialchars($change['new_data']); ?></pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Approve Change Modal -->
<div class="modal fade" id="approveChangeModal" tabindex="-1" aria-labelledby="approveChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveChangeModalLabel">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to approve this change? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveChange">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Change Modal -->
<div class="modal fade" id="rejectChangeModal" tabindex="-1" aria-labelledby="rejectChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectChangeModalLabel">Confirm Rejection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this change? This action cannot be undone.</p>
                <div class="form-group">
                    <label for="rejectionReason">Reason for rejection (optional):</label>
                    <textarea class="form-control" id="rejectionReason" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRejectChange">Reject</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?> 