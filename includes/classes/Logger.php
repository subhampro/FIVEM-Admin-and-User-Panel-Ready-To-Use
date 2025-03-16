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
     * Log an action
     * 
     * @param int $userId User ID
     * @param string $actionType Type of action
     * @param string $action Description of action
     * @param string $ipAddress IP address (optional)
     * @return int|bool ID of the log entry or false on failure
     */
    public function logAction($userId, $actionType, $action, $ipAddress = '') {
        if (empty($ipAddress)) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '::1';
        }
        
        $timestamp = date('Y-m-d H:i:s');
        
        $query = "INSERT INTO action_logs (user_id, action_type, action, ip_address, timestamp) VALUES (?, ?, ?, ?, ?)";
        $params = [$userId, $actionType, $action, $ipAddress, $timestamp];
        $types = 'issss';
        
        error_log("Executing insert query: $query with data: " . print_r($params, true));
        error_log("Bound parameters with types: $types");
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Execute statement failed: " . $stmt->error);
            return false;
        }
        
        $id = $stmt->insert_id;
        $stmt->close();
        
        error_log("Insert successful. Last ID: $id");
        
        return $id;
    }
    
    /**
     * Get activity logs with pagination and search
     * 
     * @param string $search Search term
     * @param string $searchField Field to search in
     * @param int $limit Items per page
     * @param int $offset Offset for pagination
     * @return array Array of activity logs
     */
    public function getActivities($search = '', $searchField = 'all', $limit = 100, $offset = 0) {
        $query = "SELECT al.*, wu.username 
                FROM action_logs al 
                LEFT JOIN website_users wu ON al.user_id = wu.id";
        $params = [];
        
        // Add search conditions if search term provided
        if (!empty($search)) {
            if ($searchField === 'all') {
                $query .= " WHERE (wu.username LIKE ? OR al.action_type LIKE ? OR al.action LIKE ? OR al.timestamp LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            } else {
                switch ($searchField) {
                    case 'user':
                        $query .= " WHERE wu.username LIKE ?";
                        break;
                    case 'action_type':
                        $query .= " WHERE al.action_type LIKE ?";
                        break;
                    case 'action':
                        $query .= " WHERE al.action LIKE ?";
                        break;
                    case 'timestamp':
                        $query .= " WHERE al.timestamp LIKE ?";
                        break;
                }
                $params = ['%' . $search . '%'];
            }
        }
        
        // Add order by and limit
        $query .= " ORDER BY al.timestamp DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . $this->conn->error);
            return [];
        }
        
        // Bind parameters
        if (!empty($params)) {
            $types = str_repeat('s', count($params) - 2) . 'ii'; // All string params + 2 integers for limit/offset
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        $stmt->close();
        
        return $activities;
    }
    
    /**
     * Count total activities matching search criteria
     * 
     * @param string $search Search term
     * @param string $searchField Field to search in
     * @return int Total number of activities
     */
    public function countActivities($search = '', $searchField = 'all') {
        $query = "SELECT COUNT(*) as total 
                FROM action_logs al 
                LEFT JOIN website_users wu ON al.user_id = wu.id";
        $params = [];
        
        // Add search conditions if search term provided
        if (!empty($search)) {
            if ($searchField === 'all') {
                $query .= " WHERE (wu.username LIKE ? OR al.action_type LIKE ? OR al.action LIKE ? OR al.timestamp LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            } else {
                switch ($searchField) {
                    case 'user':
                        $query .= " WHERE wu.username LIKE ?";
                        break;
                    case 'action_type':
                        $query .= " WHERE al.action_type LIKE ?";
                        break;
                    case 'action':
                        $query .= " WHERE al.action LIKE ?";
                        break;
                    case 'timestamp':
                        $query .= " WHERE al.timestamp LIKE ?";
                        break;
                }
                $params = ['%' . $search . '%'];
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . $this->conn->error);
            return 0;
        }
        
        // Bind parameters
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // All string params
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        
        return $row ? $row['total'] : 0;
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
     * @param string $actionType Action type (optional)
     * @param int $userId User ID (optional)
     * @return array Logs filtered by date range, action type and user
     */
    public function getLogs($startDate = '', $endDate = '', $actionType = '', $userId = 0) {
        $query = "SELECT al.*, wu.username 
                FROM action_logs al 
                LEFT JOIN website_users wu ON al.user_id = wu.id
                WHERE 1=1";
        $params = [];
        $types = '';
        
        // Add date range filter
        if (!empty($startDate)) {
            $query .= " AND DATE(al.timestamp) >= ?";
            $params[] = $startDate;
            $types .= 's';
        }
        
        if (!empty($endDate)) {
            $query .= " AND DATE(al.timestamp) <= ?";
            $params[] = $endDate;
            $types .= 's';
        }
        
        // Add action type filter
        if (!empty($actionType)) {
            $query .= " AND al.action_type = ?";
            $params[] = $actionType;
            $types .= 's';
        }
        
        // Add user filter
        if (!empty($userId) && $userId > 0) {
            $query .= " AND al.user_id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        
        // Order by timestamp
        $query .= " ORDER BY al.timestamp DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare statement failed: " . $this->conn->error);
            return [];
        }
        
        // Bind parameters if we have any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = [];
        
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        $stmt->close();
        
        return $logs;
    }
    
    /**
     * Get unique action types for filtering
     * 
     * @return array Array of unique action types
     */
    public function getUniqueActionTypes() {
        $query = "SELECT DISTINCT action_type FROM action_logs ORDER BY action_type ASC";
        $result = $this->db->query($query);
        
        if (!$result) {
            return [];
        }
        
        $types = [];
        while ($row = $result->fetch_assoc()) {
            $types[] = $row['action_type'];
        }
        
        return $types;
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