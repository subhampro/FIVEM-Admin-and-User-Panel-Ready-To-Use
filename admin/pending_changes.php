<?php
require_once '../config/init.php';

// Require admin level 3 privileges
requireAdmin('admin_level3');

$admin = new Admin();
$pendingChanges = new PendingChanges();
$logger = new Logger();

// Process approval or rejection
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $changeId = isset($_POST['change_id']) ? (int)$_POST['change_id'] : 0;
    $comments = trim($_POST['comments'] ?? '');
    
    if (empty($changeId)) {
        $message = 'Invalid request: Change ID is required.';
        $messageType = 'danger';
    } else {
        // Get the change details
        $changeDetails = $pendingChanges->getPendingChangeById($changeId);
        
        if (!$changeDetails) {
            $message = 'Pending change not found.';
            $messageType = 'danger';
        } elseif ($changeDetails['status'] !== 'pending') {
            $message = 'This change has already been processed.';
            $messageType = 'warning';
        } else {
            // Process based on action
            if ($action === 'approve') {
                try {
                    $result = $pendingChanges->approveChange($changeId, $_SESSION['user_id'], $comments);
                    
                    $logger->logAction(
                        $_SESSION['user_id'],
                        'approve_change',
                        "Approved change #{$changeId} for {$changeDetails['target_table']}.{$changeDetails['field_name']} on {$changeDetails['target_id']}"
                    );
                    
                    $message = 'Change has been approved and applied to the database.';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                    error_log("Error in pending_changes.php (approve): " . $e->getMessage());
                }
            } elseif ($action === 'reject') {
                try {
                    $result = $pendingChanges->rejectChange($changeId, $_SESSION['user_id'], $comments);
                    
                    if ($result) {
                        $logger->logAction(
                            $_SESSION['user_id'],
                            'reject_change',
                            "Rejected change #{$changeId} for {$changeDetails['target_table']}.{$changeDetails['field_name']} on {$changeDetails['target_id']}"
                        );
                        
                        $message = 'Change has been rejected and will not be applied to the database.';
                        $messageType = 'warning';
                    } else {
                        $message = 'Failed to reject the change. Please try again.';
                        $messageType = 'danger';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'danger';
                    error_log("Error in pending_changes.php (reject): " . $e->getMessage());
                }
            } else {
                $message = 'Invalid action. Please try again.';
                $messageType = 'danger';
            }
        }
    }
}

// Get current status filter
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
if (!in_array($status, ['pending', 'approved', 'rejected'])) {
    $status = 'pending';
}

// Get the changes for current status
$changes = $pendingChanges->getPendingChanges($status);

// Count stats
$pendingCount = $pendingChanges->countPendingChanges('pending');
$approvedCount = $pendingChanges->countPendingChanges('approved');
$rejectedCount = $pendingChanges->countPendingChanges('rejected');

// Page title
$pageTitle = 'Pending Changes - Admin Dashboard';

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 sidebar">
            <div class="d-flex flex-column flex-shrink-0 p-3">
                <a href="../index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
                    <span class="fs-4 text-white">Admin Panel</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-home me-2"></i> Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../user/index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i> User Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="players.php" class="nav-link">
                            <i class="fas fa-user me-2"></i> Player Management
                        </a>
                    </li>
                    <?php if (isAdmin('admin_level2')): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="fas fa-users me-2"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="database_status.php" class="nav-link">
                            <i class="fas fa-database me-2"></i> Database Management
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isAdmin('admin_level3')): ?>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog me-2"></i> System Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logs.php" class="nav-link">
                            <i class="fas fa-file-alt me-2"></i> System Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pending_changes.php" class="nav-link active">
                            <i class="fas fa-tasks me-2"></i> Pending Changes
                            <?php if ($pendingCount > 0): ?>
                            <span class="badge bg-danger badge-counter"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2 fs-5"></i>
                        <strong><?php echo $_SESSION['username']; ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="../user/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pending Changes</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Changes</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $pendingCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Approved Changes</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $approvedCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Rejected Changes</div>
                                    <div class="h5 mb-0 font-weight-bold"><?php echo $rejectedCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                        <i class="fas fa-clock me-1"></i> Pending
                        <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-light text-dark"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status === 'approved' ? 'active' : ''; ?>" href="?status=approved">
                        <i class="fas fa-check-circle me-1"></i> Approved
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status === 'rejected' ? 'active' : ''; ?>" href="?status=rejected">
                        <i class="fas fa-times-circle me-1"></i> Rejected
                    </a>
                </li>
            </ul>
            
            <!-- Changes Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?php if ($status === 'pending'): ?>
                            <i class="fas fa-clock me-1"></i> Pending Changes
                        <?php elseif ($status === 'approved'): ?>
                            <i class="fas fa-check-circle me-1"></i> Approved Changes
                        <?php else: ?>
                            <i class="fas fa-times-circle me-1"></i> Rejected Changes
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($changes)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i> No <?php echo $status; ?> changes found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Admin</th>
                                        <th width="10%">Table</th>
                                        <th width="15%">Target ID</th>
                                        <th width="15%">Field</th>
                                        <th width="15%">Requested</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($changes as $change): ?>
                                        <tr>
                                            <td><?php echo $change['id']; ?></td>
                                            <td><?php echo htmlspecialchars($change['admin_username'] ?? 'Unknown'); ?></td>
                                            <td><?php echo htmlspecialchars($change['target_table']); ?></td>
                                            <td><?php echo htmlspecialchars($change['target_id']); ?></td>
                                            <td><?php echo htmlspecialchars($change['field_name']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($change['created_at'])); ?></td>
                                            <td>
                                                <?php if ($change['status'] === 'pending'): ?>
                                                    <span class="badge pending-badge">Pending</span>
                                                <?php elseif ($change['status'] === 'approved'): ?>
                                                    <span class="badge approved-badge">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge rejected-badge">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-change" data-bs-toggle="modal" data-bs-target="#viewChangeModal" 
                                                        data-id="<?php echo $change['id']; ?>"
                                                        data-admin="<?php echo htmlspecialchars($change['admin_username'] ?? 'Unknown'); ?>"
                                                        data-table="<?php echo htmlspecialchars($change['target_table']); ?>"
                                                        data-target="<?php echo htmlspecialchars($change['target_id']); ?>"
                                                        data-field="<?php echo htmlspecialchars($change['field_name']); ?>"
                                                        data-old="<?php echo htmlspecialchars($change['old_value']); ?>"
                                                        data-new="<?php echo htmlspecialchars($change['new_value']); ?>"
                                                        data-status="<?php echo $change['status']; ?>"
                                                        data-created="<?php echo date('Y-m-d H:i:s', strtotime($change['created_at'])); ?>"
                                                        data-reviewer="<?php echo htmlspecialchars($change['reviewer_username'] ?? 'N/A'); ?>"
                                                        data-reviewed="<?php echo $change['reviewed_at'] ? date('Y-m-d H:i:s', strtotime($change['reviewed_at'])) : 'N/A'; ?>"
                                                        data-comments="<?php echo htmlspecialchars($change['review_comments'] ?? ''); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($change['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success approve-change" data-bs-toggle="modal" data-bs-target="#approveModal" data-id="<?php echo $change['id']; ?>">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-change" data-bs-toggle="modal" data-bs-target="#rejectModal" data-id="<?php echo $change['id']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Change Modal -->
<div class="modal fade" id="viewChangeModal" tabindex="-1" aria-labelledby="viewChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewChangeModalLabel">Change Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>ID</th>
                                <td id="viewId"></td>
                            </tr>
                            <tr>
                                <th>Admin</th>
                                <td id="viewAdmin"></td>
                            </tr>
                            <tr>
                                <th>Table</th>
                                <td id="viewTable"></td>
                            </tr>
                            <tr>
                                <th>Target ID</th>
                                <td id="viewTarget"></td>
                            </tr>
                            <tr>
                                <th>Field</th>
                                <td id="viewField"></td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td id="viewCreated"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="viewStatus"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Review Information</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Reviewer</th>
                                <td id="viewReviewer"></td>
                            </tr>
                            <tr>
                                <th>Reviewed</th>
                                <td id="viewReviewed"></td>
                            </tr>
                            <tr>
                                <th>Comments</th>
                                <td id="viewComments"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Old Value</h6>
                        <pre id="viewOldValue" class="mb-0"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>New Value</h6>
                        <pre id="viewNewValue" class="mb-0"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Approve Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <p>Are you sure you want to approve this change? This will apply the change to the database.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Approving this change will immediately update the game database with the new value.
                    </div>
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="change_id" id="approveChangeId">
                    
                    <div class="mb-3">
                        <label for="approveComments" class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="approveComments" name="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <p>Are you sure you want to reject this change?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> Rejecting this change will permanently cancel it. The change will not be applied to the database.
                    </div>
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="change_id" id="rejectChangeId">
                    
                    <div class="mb-3">
                        <label for="rejectComments" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="rejectComments" name="comments" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // View change modal
    $('.view-change').click(function() {
        const id = $(this).data('id');
        const admin = $(this).data('admin');
        const table = $(this).data('table');
        const target = $(this).data('target');
        const field = $(this).data('field');
        const oldValue = $(this).data('old');
        const newValue = $(this).data('new');
        const status = $(this).data('status');
        const created = $(this).data('created');
        const reviewer = $(this).data('reviewer');
        const reviewed = $(this).data('reviewed');
        const comments = $(this).data('comments');
        
        // Set modal values
        $('#viewId').text(id);
        $('#viewAdmin').text(admin);
        $('#viewTable').text(table);
        $('#viewTarget').text(target);
        $('#viewField').text(field);
        $('#viewCreated').text(created);
        
        // Format status with badge
        let statusHtml = '';
        if (status === 'pending') {
            statusHtml = '<span class="badge pending-badge">Pending</span>';
        } else if (status === 'approved') {
            statusHtml = '<span class="badge approved-badge">Approved</span>';
        } else {
            statusHtml = '<span class="badge rejected-badge">Rejected</span>';
        }
        $('#viewStatus').html(statusHtml);
        
        // Set review info
        $('#viewReviewer').text(reviewer);
        $('#viewReviewed').text(reviewed);
        $('#viewComments').text(comments);
        
        // Set values
        try {
            // Try to parse as JSON for pretty display
            const oldJson = JSON.parse(oldValue);
            $('#viewOldValue').text(JSON.stringify(oldJson, null, 2));
        } catch (e) {
            $('#viewOldValue').text(oldValue);
        }
        
        try {
            const newJson = JSON.parse(newValue);
            $('#viewNewValue').text(JSON.stringify(newJson, null, 2));
        } catch (e) {
            $('#viewNewValue').text(newValue);
        }
    });
    
    // Approve change modal
    $('.approve-change').click(function() {
        const id = $(this).data('id');
        $('#approveChangeId').val(id);
    });
    
    // Reject change modal
    $('.reject-change').click(function() {
        const id = $(this).data('id');
        $('#rejectChangeId').val(id);
    });
});
</script>
</body>
</html> 