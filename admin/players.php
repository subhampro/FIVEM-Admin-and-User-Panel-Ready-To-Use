<?php
require_once '../config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page
    header('Location: ../login.php');
    exit;
}

$admin = new Admin();
$player = new Player();
$logger = new Logger();

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'delete' && $id > 0) {
        // Delete player
        $result = $player->deletePlayer($id);
        if ($result) {
            $logger->log('Admin deleted player (ID: ' . $id . ')', 'admin_action', $_SESSION['user_id']);
            $_SESSION['success_message'] = 'Player deleted successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to delete player.';
        }
        header('Location: players.php');
        exit;
    }
}

// Handle form submission for adding/editing player
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerId = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
    $steamId = isset($_POST['steam_id']) ? trim($_POST['steam_id']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $money = isset($_POST['money']) ? (float)$_POST['money'] : 0;
    $bank = isset($_POST['bank']) ? (float)$_POST['bank'] : 0;
    $job = isset($_POST['job']) ? trim($_POST['job']) : '';
    $jobGrade = isset($_POST['job_grade']) ? (int)$_POST['job_grade'] : 0;
    
    // Validate inputs
    $errors = [];
    
    if (empty($steamId)) {
        $errors[] = 'Steam ID is required.';
    }
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($identifier)) {
        $errors[] = 'Identifier is required.';
    }
    
    if (empty($errors)) {
        $playerData = [
            'steam_id' => $steamId,
            'name' => $name,
            'identifier' => $identifier,
            'money' => $money,
            'bank' => $bank,
            'job' => $job,
            'job_grade' => $jobGrade
        ];
        
        if ($playerId > 0) {
            // Update existing player
            $result = $player->updatePlayer($playerId, $playerData);
            if ($result) {
                $logger->log('Admin updated player (ID: ' . $playerId . ')', 'admin_action', $_SESSION['user_id']);
                $_SESSION['success_message'] = 'Player updated successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to update player.';
            }
        } else {
            // Create new player
            $newPlayerId = $player->createPlayer($playerData);
            if ($newPlayerId) {
                $logger->log('Admin created new player (ID: ' . $newPlayerId . ')', 'admin_action', $_SESSION['user_id']);
                $_SESSION['success_message'] = 'Player created successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to create player.';
            }
        }
        
        header('Location: players.php');
        exit;
    }
}

// Get player data if editing
$editPlayer = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editPlayerId = (int)$_GET['edit'];
    $editPlayer = $player->getPlayerById($editPlayerId);
}

// Get all players with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$players = $player->getPlayers($page, $limit, $search);
$totalPlayers = $player->getTotalPlayers($search);
$totalPages = ceil($totalPlayers / $limit);

// Page title
$pageTitle = 'Player Management';

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
                        <a class="nav-link active" href="players.php">
                            <i class="fas fa-gamepad"></i> Players
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_changes.php">
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
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                        <i class="fas fa-plus"></i> Add New Player
                    </button>
                </div>
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

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Search form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, steam ID, or identifier" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Players table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Players</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($players)): ?>
                        <div class="alert alert-info">
                            No players found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Steam ID</th>
                                        <th>Identifier</th>
                                        <th>Money</th>
                                        <th>Bank</th>
                                        <th>Job</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($players as $p): ?>
                                        <tr>
                                            <td><?php echo $p['id']; ?></td>
                                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                                            <td><?php echo htmlspecialchars($p['steam_id']); ?></td>
                                            <td><?php echo htmlspecialchars($p['identifier']); ?></td>
                                            <td>$<?php echo number_format($p['money'], 2); ?></td>
                                            <td>$<?php echo number_format($p['bank'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($p['job']) . ' (Grade ' . $p['job_grade'] . ')'; ?></td>
                                            <td>
                                                <a href="players.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger delete-player-btn" data-player-id="<?php echo $p['id']; ?>" data-player-name="<?php echo htmlspecialchars($p['name']); ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="players.php?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Previous</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="players.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="players.php?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Next</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Player Modal -->
<div class="modal fade" id="<?php echo $editPlayer ? 'editPlayerModal' : 'addPlayerModal'; ?>" tabindex="-1" aria-labelledby="<?php echo $editPlayer ? 'editPlayerModalLabel' : 'addPlayerModalLabel'; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?php echo $editPlayer ? 'editPlayerModalLabel' : 'addPlayerModalLabel'; ?>">
                    <?php echo $editPlayer ? 'Edit Player' : 'Add New Player'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="playerForm" method="post" action="">
                    <?php if ($editPlayer): ?>
                        <input type="hidden" name="player_id" value="<?php echo $editPlayer['id']; ?>">
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="steam_id" class="form-label">Steam ID</label>
                            <input type="text" class="form-control" id="steam_id" name="steam_id" value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['steam_id']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="identifier" class="form-label">Identifier</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['identifier']) : ''; ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="money" class="form-label">Money</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="money" name="money" step="0.01" value="<?php echo $editPlayer ? $editPlayer['money'] : '0.00'; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="bank" class="form-label">Bank</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="bank" name="bank" step="0.01" value="<?php echo $editPlayer ? $editPlayer['bank'] : '0.00'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="job" class="form-label">Job</label>
                            <input type="text" class="form-control" id="job" name="job" value="<?php echo $editPlayer ? htmlspecialchars($editPlayer['job']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="job_grade" class="form-label">Job Grade</label>
                            <input type="number" class="form-control" id="job_grade" name="job_grade" value="<?php echo $editPlayer ? $editPlayer['job_grade'] : '0'; ?>">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editPlayer ? 'Update Player' : 'Add Player'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Player Confirmation Modal -->
<div class="modal fade" id="deletePlayerModal" tabindex="-1" aria-labelledby="deletePlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlayerModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the player <span id="playerNameToDelete" class="fw-bold"></span>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeletePlayer" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Show edit modal if editing
    <?php if ($editPlayer): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = new bootstrap.Modal(document.getElementById('editPlayerModal'));
        editModal.show();
    });
    <?php endif; ?>

    // Handle delete player button click
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-player-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const playerId = this.getAttribute('data-player-id');
                const playerName = this.getAttribute('data-player-name');
                
                document.getElementById('playerNameToDelete').textContent = playerName;
                document.getElementById('confirmDeletePlayer').href = 'players.php?action=delete&id=' + playerId;
                
                var deleteModal = new bootstrap.Modal(document.getElementById('deletePlayerModal'));
                deleteModal.show();
            });
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?>
