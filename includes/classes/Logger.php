<?php
/**
 * Logger class for logging admin actions
 */
class Logger {
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
     * Log an admin action
     * 
     * @param int $adminId Admin ID
     * @param string $actionType Type of action
     * @param string $actionDetails Details of the action
     * @param string $targetTable Target table (optional)
     * @param int $targetId Target ID (optional)
     * @return int|false ID of the log entry or false on failure
     */
    public function logAction($adminId, $actionType, $actionDetails, $targetTable = null, $targetId = null) {
        $data = [
            'admin_id' => $adminId,
            'action_type' => $actionType,
            'action_details' => $actionDetails,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'performed_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->getClientIp()
        ];
        
        return $this->db->insert('admin_actions_log', $data);
    }
    
    /**
     * Get all logs
     * 
     * @param int $limit Limit (optional)
     * @param int $offset Offset (optional)
     * @return array|false Array of logs or false on failure
     */
    public function getAllLogs($limit = null, $offset = null) {
        $query = "
            SELECT l.*, u.username
            FROM admin_actions_log l
            JOIN website_users u ON l.admin_id = u.id
            ORDER BY l.performed_at DESC
        ";
        
        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit);
            
            if ($offset !== null) {
                $query .= " OFFSET " . intval($offset);
            }
        }
        
        return $this->db->getAll($query);
    }
    
    /**
     * Get logs by admin
     * 
     * @param int $adminId Admin ID
     * @param int $limit Limit (optional)
     * @param int $offset Offset (optional)
     * @return array|false Array of logs or false on failure
     */
    public function getLogsByAdmin($adminId, $limit = null, $offset = null) {
        $query = "
            SELECT l.*, u.username
            FROM admin_actions_log l
            JOIN website_users u ON l.admin_id = u.id
            WHERE l.admin_id = ?
            ORDER BY l.performed_at DESC
        ";
        
        $params = [$adminId];
        
        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit);
            
            if ($offset !== null) {
                $query .= " OFFSET " . intval($offset);
            }
        }
        
        return $this->db->getAll($query, $params);
    }
    
    /**
     * Get logs by action type
     * 
     * @param string $actionType Action type
     * @param int $limit Limit (optional)
     * @param int $offset Offset (optional)
     * @return array|false Array of logs or false on failure
     */
    public function getLogsByActionType($actionType, $limit = null, $offset = null) {
        $query = "
            SELECT l.*, u.username
            FROM admin_actions_log l
            JOIN website_users u ON l.admin_id = u.id
            WHERE l.action_type = ?
            ORDER BY l.performed_at DESC
        ";
        
        $params = [$actionType];
        
        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit);
            
            if ($offset !== null) {
                $query .= " OFFSET " . intval($offset);
            }
        }
        
        return $this->db->getAll($query, $params);
    }
    
    /**
     * Get logs by target
     * 
     * @param string $targetTable Target table
     * @param int $targetId Target ID
     * @param int $limit Limit (optional)
     * @param int $offset Offset (optional)
     * @return array|false Array of logs or false on failure
     */
    public function getLogsByTarget($targetTable, $targetId, $limit = null, $offset = null) {
        $query = "
            SELECT l.*, u.username
            FROM admin_actions_log l
            JOIN website_users u ON l.admin_id = u.id
            WHERE l.target_table = ? AND l.target_id = ?
            ORDER BY l.performed_at DESC
        ";
        
        $params = [$targetTable, $targetId];
        
        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit);
            
            if ($offset !== null) {
                $query .= " OFFSET " . intval($offset);
            }
        }
        
        return $this->db->getAll($query, $params);
    }
    
    /**
     * Get logs by date range
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param int $limit Limit (optional)
     * @param int $offset Offset (optional)
     * @return array|false Array of logs or false on failure
     */
    public function getLogsByDateRange($startDate, $endDate, $limit = null, $offset = null) {
        $query = "
            SELECT l.*, u.username
            FROM admin_actions_log l
            JOIN website_users u ON l.admin_id = u.id
            WHERE DATE(l.performed_at) BETWEEN ? AND ?
            ORDER BY l.performed_at DESC
        ";
        
        $params = [$startDate, $endDate];
        
        if ($limit !== null) {
            $query .= " LIMIT " . intval($limit);
            
            if ($offset !== null) {
                $query .= " OFFSET " . intval($offset);
            }
        }
        
        return $this->db->getAll($query, $params);
    }
    
    /**
     * Count logs
     * 
     * @return int Number of logs
     */
    public function countLogs() {
        $query = "SELECT COUNT(*) as count FROM admin_actions_log";
        $result = $this->db->getSingle($query);
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP address
     */
    private function getClientIp() {
        $ipAddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = 'UNKNOWN';
        }
        
        return $ipAddress;
    }
    
    /**
     * Delete old logs
     * 
     * @param int $days Number of days to keep
     * @return int|false Number of deleted rows or false on failure
     */
    public function deleteOldLogs($days = 90) {
        $query = "DELETE FROM admin_actions_log WHERE performed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->query($query, [$days]);
        
        if ($stmt) {
            return $stmt->rowCount();
        }
        
        return false;
    }
}
?> 