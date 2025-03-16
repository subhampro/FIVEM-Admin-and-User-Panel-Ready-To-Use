<?php
/**
 * User management class
 */
class User {
    private $db;
    private $conn;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Register a new user
     * 
     * @param string $username Username
     * @param string $password Plain password
     * @param string $email Email address
     * @param string $citizenid CitizenID from game (must exist in players table)
     * @return int|false User ID on success, false on failure
     */
    public function register($username, $password, $email, $citizenid) {
        // Enable error logging
        error_log("Starting registration for username: $username, email: $email, citizenid: $citizenid");
        
        // First check if the citizenid exists in the players table
        $player = new Player();
        $playerData = $player->getPlayerByCitizenId($citizenid);
        
        if (!$playerData) {
            // CitizenID doesn't exist in players table
            error_log("Registration failed: CitizenID $citizenid doesn't exist in players table");
            return false;
        }
        
        // Log player data for debugging
        error_log("Found player data: " . print_r($playerData, true));
        
        // Check if citizenid already registered
        $existingUser = $this->getUserByCitizenId($citizenid);
        
        if ($existingUser) {
            // CitizenID already registered
            error_log("Registration failed: CitizenID $citizenid already registered");
            return false;
        }
        
        // Check if username already exists
        $query = "SELECT id FROM website_users WHERE username = ?";
        $existingUser = $this->db->getSingle($query, [$username]);
        
        if ($existingUser) {
            // Username already exists
            error_log("Registration failed: Username $username already exists");
            return false;
        }
        
        // Check if email already exists
        $query = "SELECT id FROM website_users WHERE email = ?";
        $existingEmail = $this->db->getSingle($query, [$email]);
        
        if ($existingEmail) {
            // Email already exists
            error_log("Registration failed: Email $email already exists");
            return false;
        }
        
        // Hash password with bcrypt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Create user
        $userData = [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'is_admin' => 0, // Default non-admin
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add player_id 
        $userData['player_id'] = $playerData['id'];
        
        // Log the data we're about to insert
        error_log("Attempting to insert user with data: " . print_r($userData, true));
        
        // Try to insert and log any errors
        try {
            $result = $this->db->insert('website_users', $userData);
            if ($result) {
                error_log("Registration successful: User ID $result created");
                
                // Add the character to user_characters table
                try {
                    $this->addCharacter($result, $citizenid, true);
                    error_log("Added citizenid $citizenid as primary character for user $result");
                } catch (Exception $e) {
                    error_log("Failed to add character during registration: " . $e->getMessage());
                    // Continue anyway since the user account was created
                }
            } else {
                error_log("Registration failed: Database insert returned false");
            }
            return $result;
        } catch (Exception $e) {
            error_log("Registration exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * User login
     * 
     * @param string $username Username
     * @param string $password Plain password
     * @return array|false User data on success, false on failure
     */
    public function login($username, $password) {
        $query = "SELECT * FROM website_users WHERE username = ? AND is_active = 1";
        $user = $this->db->getSingle($query, [$username]);
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Update last login
            $this->db->update('website_users', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$user['id']]
            );
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getUserById($id) {
        $query = "SELECT * FROM website_users WHERE id = ?";
        return $this->db->getSingle($query, [$id]);
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|false User data or false if not found
     */
    public function getUserByUsername($username) {
        $query = "SELECT * FROM website_users WHERE username = ?";
        return $this->db->getSingle($query, [$username]);
    }
    
    /**
     * Get user by citizenid
     * 
     * @param string $citizenid CitizenID
     * @return array|false User data or false if not found
     */
    public function getUserByCitizenId($citizenid) {
        // Try two approaches:
        
        // 1. First try with Player class (connects to game database)
        try {
            $player = new Player();
            $playerData = $player->getPlayerByCitizenId($citizenid);
            
            if (!$playerData) {
                error_log("getUserByCitizenId: Player not found in game database for citizenid: $citizenid");
                return false;
            }
            
            error_log("getUserByCitizenId: Found player in game database with ID: " . $playerData['id']);
            
            // Then get website user with this player_id
            $query = "SELECT * FROM website_users WHERE player_id = ?";
            $user = $this->db->getSingle($query, [$playerData['id']]);
            
            if ($user) {
                error_log("getUserByCitizenId: Found user with player_id: " . $playerData['id']);
            } else {
                error_log("getUserByCitizenId: No user found with player_id: " . $playerData['id']);
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("getUserByCitizenId exception (approach 1): " . $e->getMessage());
            
            // Fallback to approach 2
        }
        
        // 2. Try with direct join as fallback
        try {
            // Direct join with players table in same database
            $query = "SELECT u.* FROM website_users u 
                      JOIN players p ON u.player_id = p.id 
                      WHERE p.citizenid = ?";
            
            return $this->db->getSingle($query, [$citizenid]);
        } catch (Exception $e) {
            error_log("getUserByCitizenId exception (approach 2): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile
     * 
     * @param int $id User ID
     * @param array $data Data to update
     * @return int|false Number of affected rows or false on failure
     */
    public function updateProfile($id, $data) {
        return $this->db->update('website_users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param string $newPassword New plain password
     * @return int|false Number of affected rows or false on failure
     */
    public function updatePassword($id, $newPassword) {
        // Hash password with bcrypt - no pepper, just like in register method
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        return $this->db->update('website_users', 
            ['password' => $hashedPassword], 
            'id = ?', 
            [$id]
        );
    }
    
    /**
     * Check if password is correct for a user
     * 
     * @param int $id User ID
     * @param string $password Plain password
     * @return bool True if password is correct, false otherwise
     */
    public function verifyPassword($id, $password) {
        $query = "SELECT password FROM website_users WHERE id = ?";
        $user = $this->db->getSingle($query, [$id]);
        
        if (!$user) {
            return false;
        }
        
        // No pepper, just like in login method
        return password_verify($password, $user['password']);
    }
    
    /**
     * Get all users
     * 
     * @return array|false Array of users or false on failure
     */
    public function getAllUsers() {
        $query = "SELECT id, username, email, role, citizenid, last_login, created_at, is_active FROM website_users";
        return $this->db->getAll($query);
    }
    
    /**
     * Update user role (admin only)
     * 
     * @param int $id User ID
     * @param string $role New role
     * @return int|false Number of affected rows or false on failure
     */
    public function updateRole($id, $role) {
        // Validate role
        $validRoles = ['user', 'admin_level1', 'admin_level2', 'admin_level3'];
        
        if (!in_array($role, $validRoles)) {
            return false;
        }
        
        return $this->db->update('website_users', 
            ['role' => $role], 
            'id = ?', 
            [$id]
        );
    }
    
    /**
     * Check if user is admin (any level)
     * 
     * @param int $id User ID
     * @return bool True if admin, false otherwise
     */
    public function isAdmin($id) {
        $query = "SELECT role FROM website_users WHERE id = ?";
        $user = $this->db->getSingle($query, [$id]);
        
        if (!$user) {
            return false;
        }
        
        return $user['role'] !== 'user';
    }
    
    /**
     * Check if user has specific admin level or higher
     * 
     * @param int $id User ID
     * @param string $level Admin level to check (admin_level1, admin_level2, admin_level3)
     * @return bool True if has level or higher, false otherwise
     */
    public function hasAdminLevel($id, $level) {
        $query = "SELECT role FROM website_users WHERE id = ?";
        $user = $this->db->getSingle($query, [$id]);
        
        if (!$user) {
            return false;
        }
        
        $role = $user['role'];
        
        // Define admin levels hierarchy
        $levels = [
            'user' => 0,
            'admin_level1' => 1,
            'admin_level2' => 2,
            'admin_level3' => 3
        ];
        
        return $levels[$role] >= $levels[$level];
    }
    
    /**
     * Activate/deactivate user account
     * 
     * @param int $id User ID
     * @param bool $active Active status
     * @return int|false Number of affected rows or false on failure
     */
    public function setActiveStatus($id, $active) {
        return $this->db->update('website_users', 
            ['is_active' => $active ? 1 : 0], 
            'id = ?', 
            [$id]
        );
    }
    
    /**
     * Delete user account
     * 
     * @param int $id User ID
     * @return int|false Number of affected rows or false on failure
     */
    public function deleteUser($id) {
        return $this->db->delete('website_users', 'id = ?', [$id]);
    }
    
    /**
     * Update user's last login time
     * 
     * @param int $id User ID
     * @return int|false Number of affected rows or false on failure
     */
    public function updateLastLogin($id) {
        return $this->db->update('website_users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$id]
        );
    }
    
    /**
     * Get all characters linked to a user
     * 
     * @param int $userId The user ID
     * @return array|false Array of character data or false on failure
     */
    public function getUserCharacters($userId) {
        try {
            $query = "SELECT uc.*, p.license FROM user_characters uc 
                    LEFT JOIN players p ON uc.citizenid = p.citizenid 
                    WHERE uc.user_id = ? 
                    ORDER BY uc.is_primary DESC, uc.added_at ASC";
            
            return $this->db->getAll($query, [$userId]);
        } catch (Exception $e) {
            error_log("Error in getUserCharacters: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a new character to a user
     * 
     * @param int $userId The user ID
     * @param string $citizenid The character's citizen ID
     * @param bool $isPrimary Whether this is the primary character
     * @return bool True on success, false on failure
     */
    public function addCharacter($userId, $citizenid, $isPrimary = false) {
        try {
            // Check if character already exists for this user
            $existingChar = $this->db->getSingle(
                "SELECT id FROM user_characters WHERE user_id = ? AND citizenid = ?", 
                [$userId, $citizenid]
            );
            
            if ($existingChar) {
                error_log("Character already exists for this user");
                return false;
            }
            
            // Check if primary is set and update other characters if needed
            if ($isPrimary) {
                $this->db->update(
                    'user_characters', 
                    ['is_primary' => 0], 
                    'user_id = ?', 
                    [$userId]
                );
            }
            
            // Default to primary if this is the first character
            if (!$isPrimary) {
                $charCount = $this->db->getSingle(
                    "SELECT COUNT(*) as count FROM user_characters WHERE user_id = ?", 
                    [$userId]
                );
                
                if ($charCount && $charCount['count'] == 0) {
                    $isPrimary = true;
                }
            }
            
            // Add the character
            return $this->db->insert('user_characters', [
                'user_id' => $userId,
                'citizenid' => $citizenid,
                'is_primary' => $isPrimary ? 1 : 0
            ]);
        } catch (Exception $e) {
            error_log("Error in addCharacter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a character from a user
     * 
     * @param int $userId The user ID
     * @param string $citizenid The character's citizen ID
     * @return bool True on success, false on failure
     */
    public function removeCharacter($userId, $citizenid) {
        try {
            // Check if this is the primary character
            $charInfo = $this->db->getSingle(
                "SELECT is_primary FROM user_characters WHERE user_id = ? AND citizenid = ?", 
                [$userId, $citizenid]
            );
            
            if (!$charInfo) {
                error_log("Character not found for deletion");
                return false;
            }
            
            // If this is primary character, don't allow deletion if it's the only one
            if ($charInfo['is_primary']) {
                $charCount = $this->db->getSingle(
                    "SELECT COUNT(*) as count FROM user_characters WHERE user_id = ?", 
                    [$userId]
                );
                
                if ($charCount && $charCount['count'] <= 1) {
                    error_log("Cannot delete the only character of a user");
                    return false;
                }
            }
            
            // Delete the character
            $result = $this->db->delete(
                'user_characters', 
                'user_id = ? AND citizenid = ?', 
                [$userId, $citizenid]
            );
            
            // If this was the primary character, set another as primary
            if ($charInfo['is_primary'] && $result) {
                $firstChar = $this->db->getSingle(
                    "SELECT citizenid FROM user_characters WHERE user_id = ? ORDER BY added_at ASC LIMIT 1", 
                    [$userId]
                );
                
                if ($firstChar) {
                    $this->db->update(
                        'user_characters', 
                        ['is_primary' => 1], 
                        'user_id = ? AND citizenid = ?', 
                        [$userId, $firstChar['citizenid']]
                    );
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in removeCharacter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set a character as primary for a user
     * 
     * @param int $userId The user ID
     * @param string $citizenid The character's citizen ID
     * @return bool True on success, false on failure
     */
    public function setPrimaryCharacter($userId, $citizenid) {
        try {
            // First make sure this character exists for the user
            $charExists = $this->db->getSingle(
                "SELECT id FROM user_characters WHERE user_id = ? AND citizenid = ?", 
                [$userId, $citizenid]
            );
            
            if (!$charExists) {
                error_log("Character not found for setting as primary");
                return false;
            }
            
            // Set all characters to non-primary
            $this->db->update(
                'user_characters', 
                ['is_primary' => 0], 
                'user_id = ?', 
                [$userId]
            );
            
            // Set the specified character as primary
            return $this->db->update(
                'user_characters', 
                ['is_primary' => 1], 
                'user_id = ? AND citizenid = ?', 
                [$userId, $citizenid]
            );
        } catch (Exception $e) {
            error_log("Error in setPrimaryCharacter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the primary character for a user
     * 
     * @param int $userId The user ID
     * @return array|false The primary character data or false if none found
     */
    public function getPrimaryCharacter($userId) {
        try {
            return $this->db->getSingle(
                "SELECT * FROM user_characters 
                WHERE user_id = ? AND is_primary = 1 
                LIMIT 1", 
                [$userId]
            );
        } catch (Exception $e) {
            error_log("Error in getPrimaryCharacter: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify if a new citizenid belongs to the same player by checking license
     * 
     * @param int $userId The user ID
     * @param string $newCitizenId The new character's citizen ID
     * @return bool True if valid, false otherwise
     */
    public function verifyCharacterOwnership($userId, $newCitizenId) {
        try {
            // Get user's primary character's license
            $primaryChar = $this->getPrimaryCharacter($userId);
            
            if (!$primaryChar) {
                // Try to get any character of this user
                $anyChar = $this->db->getSingle(
                    "SELECT citizenid FROM user_characters WHERE user_id = ? LIMIT 1", 
                    [$userId]
                );
                
                if (!$anyChar) {
                    error_log("No existing character found for user");
                    return false;
                }
                
                $primaryCitizenId = $anyChar['citizenid'];
            } else {
                $primaryCitizenId = $primaryChar['citizenid'];
            }
            
            // Get license of primary character
            $player = new Player();
            $primaryPlayer = $player->getPlayerByCitizenId($primaryCitizenId);
            
            if (!$primaryPlayer || !isset($primaryPlayer['license'])) {
                error_log("Could not find license for primary character");
                error_log("Primary character data: " . print_r($primaryPlayer, true));
                return false;
            }
            
            $primaryLicense = $primaryPlayer['license'];
            error_log("Primary character license: " . $primaryLicense);
            
            // Get license of new character
            $newPlayer = $player->getPlayerByCitizenId($newCitizenId);
            
            if (!$newPlayer || !isset($newPlayer['license'])) {
                error_log("Could not find license for new character");
                error_log("New character data: " . print_r($newPlayer, true));
                return false;
            }
            
            $newLicense = $newPlayer['license'];
            error_log("New character license: " . $newLicense);
            
            // Compare licenses
            $licenseMatch = $primaryLicense === $newLicense;
            error_log("License match result: " . ($licenseMatch ? "TRUE" : "FALSE"));
            return $licenseMatch;
        } catch (Exception $e) {
            error_log("Error in verifyCharacterOwnership: " . $e->getMessage());
            return false;
        }
    }
}
?> 