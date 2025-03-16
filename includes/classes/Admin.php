<?php
/**
 * Admin class for admin panel functionalities
 */
class Admin {
    private $db;
    private $conn;
    private $logger;
    
    /**
     * Constructor - Initialize database connection and logger
     */
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->logger = new Logger();
    }
    
    /**
     * Get admin dashboard statistics
     * 
     * @return array Dashboard statistics
     */
    public function getDashboardStats() {
        $player = new Player();
        $pendingChanges = new PendingChanges();
        
        $stats = [
            'player_count' => $player->getPlayerCount(),
            'user_count' => $this->getUserCount(),
            'pending_changes' => $pendingChanges->countPendingChanges('pending'),
            'admin_count' => $this->getAdminCount(),
            'last_login' => $this->getLastAdminLogin()
        ];
        
        return $stats;
    }
    
    /**
     * Get user count
     * 
     * @return int Number of website users
     */
    public function getUserCount() {
        $query = "SELECT COUNT(*) as count FROM website_users";
        $result = $this->db->getSingle($query);
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Get admin count
     * 
     * @return int Number of admin users
     */
    public function getAdminCount() {
        $query = "SELECT COUNT(*) as count FROM website_users WHERE role != 'user'";
        $result = $this->db->getSingle($query);
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Get last admin login
     * 
     * @return string|null Last admin login time or null if none
     */
    public function getLastAdminLogin() {
        $query = "
            SELECT last_login
            FROM website_users
            WHERE role != 'user' AND last_login IS NOT NULL
            ORDER BY last_login DESC
            LIMIT 1
        ";
        $result = $this->db->getSingle($query);
        
        if (!$result) {
            return null;
        }
        
        return $result['last_login'];
    }
    
    /**
     * Get all admin users
     * 
     * @return array|false Array of admin users or false on failure
     */
    public function getAllAdmins() {
        $query = "
            SELECT id, username, email, role, last_login, created_at
            FROM website_users
            WHERE role != 'user'
            ORDER BY role DESC, username ASC
        ";
        return $this->db->getAll($query);
    }
    
    /**
     * Create a new admin user
     * 
     * @param string $username Username
     * @param string $password Plain password
     * @param string $email Email address
     * @param string $citizenid CitizenID from game (must exist in players table)
     * @param string $role Admin role
     * @param int $createdBy ID of admin creating this user
     * @return int|false New admin ID or false on failure
     */
    public function createAdmin($username, $password, $email, $citizenid, $role, $createdBy) {
        $user = new User();
        
        // Create user with admin role
        $userId = $user->register($username, $password, $email, $citizenid);
        
        if (!$userId) {
            return false;
        }
        
        // Update role to admin role
        $updated = $user->updateRole($userId, $role);
        
        if (!$updated) {
            return false;
        }
        
        // Log the action
        $this->logger->logAction(
            $createdBy,
            'create_admin',
            "Created new admin user: {$username} with role: {$role}",
            'website_users',
            $userId
        );
        
        return $userId;
    }
    
    /**
     * Update admin settings
     * 
     * @param array $settings Array of setting key => value pairs
     * @param int $adminId ID of admin updating settings
     * @return bool True on success, false on failure
     */
    public function updateSettings($settings, $adminId) {
        $success = true;
        
        foreach ($settings as $key => $value) {
            // Check if setting exists
            $query = "SELECT id FROM website_settings WHERE setting_key = ?";
            $existingSetting = $this->db->getSingle($query, [$key]);
            
            if ($existingSetting) {
                // Update existing setting
                $updated = $this->db->update(
                    'website_settings',
                    [
                        'setting_value' => $value,
                        'updated_by' => $adminId,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    'setting_key = ?',
                    [$key]
                );
                
                if (!$updated) {
                    $success = false;
                }
            } else {
                // Create new setting
                $inserted = $this->db->insert('website_settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'updated_by' => $adminId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$inserted) {
                    $success = false;
                }
            }
            
            // Log the action
            $this->logger->logAction(
                $adminId,
                'update_setting',
                "Updated setting: {$key} to: {$value}",
                'website_settings',
                null
            );
        }
        
        return $success;
    }
    
    /**
     * Get all settings
     * 
     * @return array|false Array of settings or false on failure
     */
    public function getAllSettings() {
        $query = "
            SELECT ws.*, wu.username as updated_by_username
            FROM website_settings ws
            LEFT JOIN website_users wu ON ws.updated_by = wu.id
            ORDER BY ws.setting_key ASC
        ";
        return $this->db->getAll($query);
    }
    
    /**
     * Get a setting by key
     * 
     * @param string $key Setting key
     * @return string|null Setting value or null if not found
     */
    public function getSetting($key) {
        $query = "SELECT setting_value FROM website_settings WHERE setting_key = ?";
        $result = $this->db->getSingle($query, [$key]);
        
        if (!$result) {
            return null;
        }
        
        return $result['setting_value'];
    }
    
    /**
     * Create a notification for admin(s)
     * 
     * @param int $userId User ID to notify (0 for all admins)
     * @param string $message Notification message
     * @return bool True on success, false on failure
     */
    public function createNotification($userId, $message) {
        if ($userId === 0) {
            // Create notifications for all admins
            $query = "SELECT id FROM website_users WHERE role != 'user'";
            $admins = $this->db->getAll($query);
            
            if (!$admins) {
                return false;
            }
            
            $success = true;
            
            foreach ($admins as $admin) {
                $inserted = $this->db->insert('admin_notifications', [
                    'user_id' => $admin['id'],
                    'message' => $message,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$inserted) {
                    $success = false;
                }
            }
            
            return $success;
        } else {
            // Create notification for a specific admin
            return $this->db->insert('admin_notifications', [
                'user_id' => $userId,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Get notifications for a user
     * 
     * @param int $userId User ID
     * @param bool $unreadOnly Get only unread notifications
     * @return array|false Array of notifications or false on failure
     */
    public function getNotifications($userId, $unreadOnly = false) {
        $query = "
            SELECT *
            FROM admin_notifications
            WHERE user_id = ?
        ";
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        return $this->db->getAll($query, [$userId]);
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markNotificationRead($notificationId, $userId) {
        return $this->db->update(
            'admin_notifications',
            ['is_read' => 1],
            'id = ? AND user_id = ?',
            [$notificationId, $userId]
        );
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function markAllNotificationsRead($userId) {
        return $this->db->update(
            'admin_notifications',
            ['is_read' => 1],
            'user_id = ?',
            [$userId]
        );
    }
    
    /**
     * Count unread notifications for a user
     * 
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public function countUnreadNotifications($userId) {
        $query = "SELECT COUNT(*) as count FROM admin_notifications WHERE user_id = ? AND is_read = 0";
        $result = $this->db->getSingle($query, [$userId]);
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Get recent activity logs
     * 
     * @param int $limit Number of logs to retrieve
     * @return array|false Array of logs or false on failure
     */
    public function getRecentLogs($limit = 10) {
        $query = "SELECT al.*, wu.username 
                 FROM action_logs al 
                 LEFT JOIN website_users wu ON al.user_id = wu.id 
                 ORDER BY al.timestamp DESC 
                 LIMIT ?";
        
        return $this->db->getAll($query, [$limit]);
    }
}
?> 