<?php
/**
 * Player class for accessing and managing game player data
 */
class Player {
    private $db;
    private $conn;
    private $gameDb; // Database connection for game database
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        // Set up error logging
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(dirname(__FILE__)) . '/logs/player_errors.log');
        
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        
        // Create connection to game database (elapsed2_0)
        try {
            $this->gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
            
            // Check connection
            if ($this->gameDb->connect_error) {
                error_log("Connection to game database failed: " . $this->gameDb->connect_error);
                throw new Exception("Failed to connect to game database: " . $this->gameDb->connect_error);
            }
        } catch (Exception $e) {
            error_log("Error connecting to game database: " . $e->getMessage());
            // Don't throw again - we'll handle errors in each method
        }
    }
    
    /**
     * Destructor - Close game database connection
     */
    public function __destruct() {
        if ($this->gameDb && !$this->gameDb->connect_error) {
            $this->gameDb->close();
        }
    }
    
    /**
     * Get the game database connection
     * 
     * @return mysqli|false Game database connection or false on failure
     */
    private function getGameDb() {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            try {
                $this->gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
                if ($this->gameDb->connect_error) {
                    $this->logError("Failed to connect to game database: " . $this->gameDb->connect_error);
                    return false;
                }
            } catch (Exception $e) {
                $this->logError("Exception connecting to game database: " . $e->getMessage());
                return false;
            }
        }
        return $this->gameDb;
    }
    
    /**
     * Get all players from game database
     * 
     * @return array|false Array of players or false on failure
     */
    public function getAllPlayers() {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            return false;
        }
        
        $result = $this->gameDb->query("SELECT * FROM players");
        $players = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $players[] = $row;
            }
            return $players;
        }
        
        return false;
    }
    
    /**
     * Search for players by various criteria
     * 
     * @param string $searchTerm The term to search for
     * @param string $searchField Specific field to search in, or 'all' for all fields
     * @return array Array of matching player records
     */
    public function searchPlayers($searchTerm, $searchField = 'all') {
        // Log search attempt
        $this->logError("Search attempt with term: '$searchTerm', field: '$searchField'");
        
        $gameDb = $this->getGameDb();
        if (!$gameDb) {
            $this->logError("Failed to get game database connection in searchPlayers()");
            return [];
        }
        
        $searchTerm = trim($searchTerm);
        if (empty($searchTerm)) {
            $this->logError("Empty search term provided");
            return [];
        }
        
        try {
            // Simple query for better reliability
            $query = "SELECT * FROM players WHERE citizenid LIKE ? OR charinfo LIKE ? OR license LIKE ? LIMIT 50";
            $likeParam = "%$searchTerm%";
            
            // Log the query
            $this->logError("Search query: $query with param: $likeParam");
            
            // Prepare and execute query
            $stmt = $gameDb->prepare($query);
            if (!$stmt) {
                $this->logError("Failed to prepare player search query: " . $gameDb->error);
                return [];
            }
            
            // Bind parameters
            $stmt->bind_param('sss', $likeParam, $likeParam, $likeParam);
            $stmt->execute();
            
            $result = $stmt->get_result();
            if (!$result) {
                $this->logError("Failed to get result from player search: " . $stmt->error);
                return [];
            }
            
            $players = [];
            while ($row = $result->fetch_assoc()) {
                $players[] = $row;
            }
            
            $this->logError("Search found " . count($players) . " results");
            return $players;
            
        } catch (Exception $e) {
            $this->logError("Exception in searchPlayers(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get player's money data
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player money data or false if not found
     */
    public function getPlayerMoney($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerMoney: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT money FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getPlayerMoney: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (!isset($data['money']) || $data['money'] === null) {
                error_log("getPlayerMoney: Money data is null for citizenid: $citizenid");
                return false;
            }
            
            $money = json_decode($data['money'], true);
            if ($money === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("getPlayerMoney: JSON decode error: " . json_last_error_msg() . " for citizenid: $citizenid");
                return [];
            }
            
            return $money ?: [];
        } catch (Exception $e) {
            error_log("getPlayerMoney error: " . $e->getMessage() . " for citizenid: $citizenid");
            return false;
        }
    }
    
    /**
     * Get player's character info
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player character info or false if not found
     */
    public function getPlayerCharInfo($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerCharInfo: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT charinfo FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getPlayerCharInfo: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (!isset($data['charinfo']) || $data['charinfo'] === null) {
                error_log("getPlayerCharInfo: Character info is null for citizenid: $citizenid");
                return false;
            }
            
            $charInfo = json_decode($data['charinfo'], true);
            if ($charInfo === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("getPlayerCharInfo: JSON decode error: " . json_last_error_msg() . " for citizenid: $citizenid");
                return [];
            }
            
            return $charInfo ?: [];
        } catch (Exception $e) {
            error_log("getPlayerCharInfo error: " . $e->getMessage() . " for citizenid: $citizenid");
            return false;
        }
    }
    
    /**
     * Get player's job information
     * 
     * @param string $citizenId The citizen ID
     * @return array|bool Job details as array or false on failure
     */
    public function getPlayerJob($citizenId) {
        if (empty($citizenId)) {
            return false;
        }
        
        $gameDb = $this->getGameDb();
        if (!$gameDb) {
            $this->logError("Failed to get game database connection in getPlayerJob()");
            return false;
        }
        
        try {
            $stmt = $gameDb->prepare("SELECT job FROM players WHERE citizenid = ?");
            if (!$stmt) {
                $this->logError("Failed to prepare getPlayerJob query: " . $gameDb->error);
                return false;
            }
            
            $stmt->bind_param('s', $citizenId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                $this->logError("Failed to get result for getPlayerJob: " . $stmt->error);
                return false;
            }
            
            $row = $result->fetch_assoc();
            if (!$row || !isset($row['job'])) {
                return false;
            }
            
            // Try to decode job information
            $jobData = json_decode($row['job'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError("Failed to decode job JSON for citizen ID: $citizenId");
                return false;
            }
            
            return $jobData;
        } catch (Exception $e) {
            $this->logError("Exception in getPlayerJob(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get player's inventory
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player inventory or false if not found
     */
    public function getPlayerInventory($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerInventory: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT inventory FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getPlayerInventory: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (!isset($data['inventory']) || $data['inventory'] === null) {
                error_log("getPlayerInventory: Inventory data is null for citizenid: $citizenid");
                return false;
            }
            
            $inventory = json_decode($data['inventory'], true);
            if ($inventory === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("getPlayerInventory: JSON decode error: " . json_last_error_msg() . " for citizenid: $citizenid");
                return [];
            }
            
            return $inventory ?: [];
        } catch (Exception $e) {
            error_log("getPlayerInventory error: " . $e->getMessage() . " for citizenid: $citizenid");
            return false;
        }
    }
    
    /**
     * Get player's metadata
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player metadata or false if not found
     */
    public function getPlayerMetadata($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerMetadata: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT metadata FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getPlayerMetadata: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (!isset($data['metadata']) || $data['metadata'] === null) {
                error_log("getPlayerMetadata: Metadata is null for citizenid: $citizenid");
                return false;
            }
            
            $metadata = json_decode($data['metadata'], true);
            if ($metadata === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("getPlayerMetadata: JSON decode error: " . json_last_error_msg() . " for citizenid: $citizenid");
                return [];
            }
            
            return $metadata ?: [];
        } catch (Exception $e) {
            error_log("getPlayerMetadata error: " . $e->getMessage() . " for citizenid: $citizenid");
            return false;
        }
    }
    
    /**
     * Update player's money (requires approval for admin_level2)
     * 
     * @param string $citizenid Citizen ID
     * @param array $moneyData New money data
     * @param int $adminId Admin ID making the change
     * @param bool $needsApproval Whether the change needs approval
     * @return bool True on success, false on failure
     */
    public function updatePlayerMoney($citizenid, $moneyData, $adminId, $needsApproval = true) {
        // Get current money data
        $currentMoney = $this->getPlayerMoney($citizenid);
        
        if (!$currentMoney) {
            return false;
        }
        
        if ($needsApproval) {
            // Create pending change
            $pendingChanges = new PendingChanges();
            return $pendingChanges->createPendingChange(
                $adminId,
                'players',
                $citizenid,
                'money',
                json_encode($currentMoney),
                json_encode($moneyData)
            );
        } else {
            // Directly update
            return $this->db->update('players',
                ['money' => json_encode($moneyData)],
                'citizenid = ?',
                [$citizenid]
            );
        }
    }
    
    /**
     * Update player's charinfo (requires approval for admin_level2)
     * 
     * @param string $citizenid Citizen ID
     * @param array $charInfo New character info
     * @param int $adminId Admin ID making the change
     * @param bool $needsApproval Whether the change needs approval
     * @return bool True on success, false on failure
     */
    public function updatePlayerCharInfo($citizenid, $charInfo, $adminId, $needsApproval = true) {
        // Get current charinfo
        $currentCharInfo = $this->getPlayerCharInfo($citizenid);
        
        if (!$currentCharInfo) {
            return false;
        }
        
        if ($needsApproval) {
            // Create pending change
            $pendingChanges = new PendingChanges();
            return $pendingChanges->createPendingChange(
                $adminId,
                'players',
                $citizenid,
                'charinfo',
                json_encode($currentCharInfo),
                json_encode($charInfo)
            );
        } else {
            // Directly update
            return $this->db->update('players',
                ['charinfo' => json_encode($charInfo)],
                'citizenid = ?',
                [$citizenid]
            );
        }
    }
    
    /**
     * Update player's job (requires approval for admin_level2)
     * 
     * @param string $citizenid Citizen ID
     * @param array $job New job info
     * @param int $adminId Admin ID making the change
     * @param bool $needsApproval Whether the change needs approval
     * @return bool True on success, false on failure
     */
    public function updatePlayerJob($citizenid, $job, $adminId, $needsApproval = true) {
        // Get current job
        $currentJob = $this->getPlayerJob($citizenid);
        
        if (!$currentJob) {
            return false;
        }
        
        if ($needsApproval) {
            // Create pending change
            $pendingChanges = new PendingChanges();
            return $pendingChanges->createPendingChange(
                $adminId,
                'players',
                $citizenid,
                'job',
                json_encode($currentJob),
                json_encode($job)
            );
        } else {
            // Directly update
            return $this->db->update('players',
                ['job' => json_encode($job)],
                'citizenid = ?',
                [$citizenid]
            );
        }
    }
    
    /**
     * Get player's vehicles
     * 
     * @param string $citizenId The citizen ID
     * @return array|bool Array of vehicles or false on failure
     */
    public function getPlayerVehicles($citizenId) {
        if (empty($citizenId)) {
            return false;
        }
        
        $gameDb = $this->getGameDb();
        if (!$gameDb) {
            $this->logError("Failed to get game database connection in getPlayerVehicles()");
            return false;
        }
        
        try {
            // Check if player_vehicles table exists
            $checkTable = $gameDb->query("SHOW TABLES LIKE 'player_vehicles'");
            $usePlayersVehicles = $checkTable && $checkTable->num_rows > 0;
            
            $tableName = $usePlayersVehicles ? 'player_vehicles' : 'owned_vehicles';
            $ownerField = $usePlayersVehicles ? 'citizenid' : 'owner';
            
            // Prepare query based on table structure
            $query = "SELECT * FROM $tableName WHERE $ownerField = ?";
            $stmt = $gameDb->prepare($query);
            
            if (!$stmt) {
                $this->logError("Failed to prepare getPlayerVehicles query: " . $gameDb->error);
                return false;
            }
            
            $stmt->bind_param('s', $citizenId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                $this->logError("Failed to get result for getPlayerVehicles: " . $stmt->error);
                return false;
            }
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                // Process vehicle data
                if (isset($row['mods']) && !is_array($row['mods'])) {
                    $row['mods'] = json_decode($row['mods'], true);
                }
                
                if (isset($row['vehicle']) && !is_array($row['vehicle'])) {
                    $row['vehicle'] = json_decode($row['vehicle'], true);
                }
                
                $vehicles[] = $row;
            }
            
            return $vehicles;
        } catch (Exception $e) {
            $this->logError("Exception in getPlayerVehicles(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all vehicles in the game
     * 
     * @return array|false All vehicles or false on failure
     */
    public function getAllVehicles() {
        $query = "SELECT * FROM player_vehicles";
        return $this->db->getAll($query);
    }
    
    /**
     * Get player count
     * 
     * @return int Number of players
     */
    public function getPlayerCount() {
        $query = "SELECT COUNT(*) as count FROM players";
        $result = $this->db->getSingle($query);
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Get player's last login time
     * 
     * @param string $citizenId The citizen ID
     * @return string|bool Last login timestamp or false on failure
     */
    public function getLastLoginTime($citizenId) {
        if (empty($citizenId)) {
            return false;
        }
        
        $gameDb = $this->getGameDb();
        if (!$gameDb) {
            $this->logError("Failed to get game database connection in getLastLoginTime()");
            return false;
        }
        
        try {
            // Try different column names used in different QBCore versions
            $columns = ['last_login', 'last_updated', 'lastupdated', 'lastlogin'];
            
            foreach ($columns as $column) {
                // Check if the column exists
                $checkColumn = $gameDb->query("SHOW COLUMNS FROM players LIKE '$column'");
                if ($checkColumn && $checkColumn->num_rows > 0) {
                    // Column exists, use it
                    $stmt = $gameDb->prepare("SELECT $column FROM players WHERE citizenid = ?");
                    if (!$stmt) {
                        continue; // Try next column
                    }
                    
                    $stmt->bind_param('s', $citizenId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $row = $result->fetch_assoc()) {
                        return isset($row[$column]) ? $row[$column] : false;
                    }
                }
            }
            
            // If we're here, none of the columns were found or usable
            return false;
        } catch (Exception $e) {
            $this->logError("Exception in getLastLoginTime(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if player exists in the game database
     * 
     * @param string $citizenid Citizen ID
     * @return bool True if player exists, false otherwise
     */
    public function playerExists($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("Cannot check player existence - no connection to game database");
            return false;
        }
        
        $stmt = $this->gameDb->prepare("SELECT citizenid FROM players WHERE citizenid = ?");
        $stmt->bind_param("s", $citizenid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Get player by ID
     * 
     * @param int $id Player ID
     * @return array|false Player data or false if not found
     */
    public function getPlayerById($id) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerById: Game database connection error");
            return false;
        }
        
        try {
            // Try to get from game database first
            $stmt = $this->gameDb->prepare("SELECT * FROM players WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            // Fallback to website database for backward compatibility
            $query = "SELECT * FROM players WHERE id = ?";
            return $this->db->getSingle($query, [$id]);
        } catch (Exception $e) {
            error_log("getPlayerById error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get players with pagination for website display
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $search Search term
     * @return array Array of players
     */
    public function getPlayers($page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query = "SELECT * FROM players WHERE name LIKE ? OR steam_id LIKE ? OR identifier LIKE ? LIMIT ?, ?";
            return $this->db->getAll($query, [$searchTerm, $searchTerm, $searchTerm, $offset, $limit]);
        } else {
            $query = "SELECT * FROM players LIMIT ?, ?";
            return $this->db->getAll($query, [$offset, $limit]);
        }
    }
    
    /**
     * Get total number of players
     * 
     * @param string $search Search term
     * @return int Total number of players
     */
    public function getTotalPlayers($search = '') {
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query = "SELECT COUNT(*) as total FROM players WHERE name LIKE ? OR steam_id LIKE ? OR identifier LIKE ?";
            $result = $this->db->getSingle($query, [$searchTerm, $searchTerm, $searchTerm]);
        } else {
            $query = "SELECT COUNT(*) as total FROM players";
            $result = $this->db->getSingle($query);
        }
        
        return $result ? $result['total'] : 0;
    }
    
    /**
     * Create a new player in website database
     * 
     * @param array $data Player data
     * @return int|false New player ID or false on failure
     */
    public function createPlayer($data) {
        return $this->db->insert('players', $data);
    }
    
    /**
     * Update player in website database
     * 
     * @param int $id Player ID
     * @param array $data Data to update
     * @return int|false Number of affected rows or false on failure
     */
    public function updatePlayer($id, $data) {
        return $this->db->update('players', $data, 'id = ?', [$id]);
    }
    
    /**
     * Delete player from website database
     * 
     * @param int $id Player ID
     * @return int|false Number of affected rows or false on failure
     */
    public function deletePlayer($id) {
        return $this->db->delete('players', 'id = ?', [$id]);
    }

    /**
     * Get a player by their citizen ID
     * 
     * @param string $citizenId The citizen ID to search for
     * @return array|bool Player record or false if not found
     */
    public function getPlayerByCitizenId($citizenId) {
        if (empty($citizenId)) {
            return false;
        }
        
        $gameDb = $this->getGameDb();
        if (!$gameDb) {
            $this->logError("Failed to get game database connection in getPlayerByCitizenId()");
            return false;
        }
        
        try {
            $stmt = $gameDb->prepare("SELECT * FROM players WHERE citizenid = ?");
            if (!$stmt) {
                $this->logError("Failed to prepare getPlayerByCitizenId query: " . $gameDb->error);
                return false;
            }
            
            $stmt->bind_param('s', $citizenId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result) {
                $this->logError("Failed to get result for getPlayerByCitizenId: " . $stmt->error);
                return false;
            }
            
            $player = $result->fetch_assoc();
            return $player ?: false;
        } catch (Exception $e) {
            $this->logError("Exception in getPlayerByCitizenId(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log an error message to the error log
     * 
     * @param string $message Error message to log
     * @return void
     */
    private function logError($message) {
        // Make sure logs directory exists
        if (!file_exists('../logs')) {
            mkdir('../logs', 0777, true);
        }
        
        // Log error to file
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, '../logs/player_errors.log');
    }
}
?> 