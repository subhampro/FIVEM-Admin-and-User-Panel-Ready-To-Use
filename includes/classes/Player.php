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
     * Get player by citizenid from game database
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player data or false if not found
     */
    public function getPlayerByCitizenId($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            return false;
        }
        
        $stmt = $this->gameDb->prepare("SELECT * FROM players WHERE citizenid = ?");
        $stmt->bind_param("s", $citizenid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
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
     * Search players by various criteria
     * 
     * @param string $searchTerm Search term
     * @return array|false Array of matching players or false on failure
     */
    public function searchPlayers($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        
        $query = "
            SELECT * FROM players 
            WHERE citizenid LIKE ? 
            OR name LIKE ? 
            OR JSON_EXTRACT(charinfo, '$.firstname') LIKE ? 
            OR JSON_EXTRACT(charinfo, '$.lastname') LIKE ? 
            OR phone_number LIKE ?
        ";
        
        return $this->db->getAll($query, [
            $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm
        ]);
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
     * Get player's job info
     * 
     * @param string $citizenid Citizen ID
     * @return array|false Player job info or false if not found
     */
    public function getPlayerJob($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerJob: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT job FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getPlayerJob: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (!isset($data['job']) || $data['job'] === null) {
                error_log("getPlayerJob: Job info is null for citizenid: $citizenid");
                return false;
            }
            
            $job = json_decode($data['job'], true);
            if ($job === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("getPlayerJob: JSON decode error: " . json_last_error_msg() . " for citizenid: $citizenid");
                return [];
            }
            
            return $job ?: [];
        } catch (Exception $e) {
            error_log("getPlayerJob error: " . $e->getMessage() . " for citizenid: $citizenid");
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
     * @param string $citizenid Citizen ID
     * @return array|false Player vehicles or false if not found
     */
    public function getPlayerVehicles($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getPlayerVehicles: Game database connection error");
            return false;
        }
        
        try {
            // First check if player_vehicles table exists in game database
            $result = $this->gameDb->query("SHOW TABLES LIKE 'player_vehicles'");
            if ($result && $result->num_rows > 0) {
                // player_vehicles table exists, query it
                $stmt = $this->gameDb->prepare("SELECT * FROM player_vehicles WHERE citizenid = ?");
                $stmt->bind_param("s", $citizenid);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if (!$result) {
                    error_log("getPlayerVehicles: Query error for citizenid: $citizenid");
                    return false;
                }
                
                $vehicles = [];
                while ($vehicle = $result->fetch_assoc()) {
                    // Format vehicle data for display
                    $vehicles[] = [
                        'name' => isset($vehicle['vehicle']) ? $vehicle['vehicle'] : 'Unknown Vehicle',
                        'plate' => isset($vehicle['plate']) ? $vehicle['plate'] : '',
                        'garage' => isset($vehicle['garage']) ? $vehicle['garage'] : 'Unknown',
                        'state' => isset($vehicle['state']) ? $vehicle['state'] : 0,
                        'fuel' => isset($vehicle['fuel']) ? $vehicle['fuel'] : 100,
                        'engine' => isset($vehicle['engine']) ? $vehicle['engine'] : 1000,
                        'body' => isset($vehicle['body']) ? $vehicle['body'] : 1000
                    ];
                }
                
                return $vehicles;
            } else {
                // Try checking player data for vehicles in metadata
                $metadata = $this->getPlayerMetadata($citizenid);
                if ($metadata && isset($metadata['vehicles']) && is_array($metadata['vehicles'])) {
                    return $metadata['vehicles'];
                }
                
                return [];
            }
        } catch (Exception $e) {
            error_log("getPlayerVehicles error: " . $e->getMessage() . " for citizenid: $citizenid");
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
     * @param string $citizenid Citizen ID
     * @return string|false Last login time or false if not found
     */
    public function getLastLoginTime($citizenid) {
        if (!$this->gameDb || $this->gameDb->connect_error) {
            error_log("getLastLoginTime: Game database connection error");
            return false;
        }
        
        try {
            $stmt = $this->gameDb->prepare("SELECT last_updated FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                error_log("getLastLoginTime: No data found for citizenid: $citizenid");
                return false;
            }
            
            $data = $result->fetch_assoc();
            if (isset($data['last_updated'])) {
                return $data['last_updated'];
            }
            
            // Try alternative column names that might contain last login time
            $stmt = $this->gameDb->prepare("SELECT lastlogin FROM players WHERE citizenid = ?");
            $stmt->bind_param("s", $citizenid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                if (isset($data['lastlogin'])) {
                    return $data['lastlogin'];
                }
            }
            
            // As a last resort, try checking metadata
            $metadata = $this->getPlayerMetadata($citizenid);
            if ($metadata && isset($metadata['lastLogin'])) {
                return $metadata['lastLogin'];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("getLastLoginTime error: " . $e->getMessage() . " for citizenid: $citizenid");
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
}
?> 