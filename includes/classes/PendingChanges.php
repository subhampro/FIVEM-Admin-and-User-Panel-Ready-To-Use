<?php
/**
 * PendingChanges class for managing changes that need approval
 */
class PendingChanges {
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
     * Create a new pending change
     * 
     * @param int $adminId Admin ID who made the change
     * @param string $targetTable Target table
     * @param string $targetId Target ID
     * @param string $fieldName Field name
     * @param string $oldValue Old value
     * @param string $newValue New value
     * @return int|false ID of the pending change or false on failure
     */
    public function createPendingChange($adminId, $targetTable, $targetId, $fieldName, $oldValue, $newValue) {
        $data = [
            'admin_id' => $adminId,
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('pending_changes', $data);
    }
    
    /**
     * Get a pending change by ID
     * 
     * @param int $id Pending change ID
     * @return array|false Pending change data or false if not found
     */
    public function getPendingChangeById($id) {
        $query = "SELECT * FROM pending_changes WHERE id = ?";
        return $this->db->getSingle($query, [$id]);
    }
    
    /**
     * Get all pending changes
     * 
     * @return array|false Array of pending changes or false on failure
     */
    public function getAllPendingChanges() {
        $query = "
            SELECT pc.*, wu.username as admin_username, 
                   (SELECT username FROM website_users WHERE id = pc.reviewer_id) as reviewer_username
            FROM pending_changes pc
            JOIN website_users wu ON pc.admin_id = wu.id
            ORDER BY pc.created_at DESC
        ";
        return $this->db->getAll($query);
    }
    
    /**
     * Get pending changes by status
     * 
     * @param string $status Status (pending, approved, rejected)
     * @return array|false Array of pending changes or false on failure
     */
    public function getPendingChangesByStatus($status) {
        $query = "
            SELECT pc.*, wu.username as admin_username, 
                   (SELECT username FROM website_users WHERE id = pc.reviewer_id) as reviewer_username
            FROM pending_changes pc
            JOIN website_users wu ON pc.admin_id = wu.id
            WHERE pc.status = ?
            ORDER BY pc.created_at DESC
        ";
        return $this->db->getAll($query, [$status]);
    }
    
    /**
     * Get pending changes by admin
     * 
     * @param int $adminId Admin ID
     * @return array|false Array of pending changes or false on failure
     */
    public function getPendingChangesByAdmin($adminId) {
        $query = "
            SELECT pc.*, wu.username as admin_username, 
                   (SELECT username FROM website_users WHERE id = pc.reviewer_id) as reviewer_username
            FROM pending_changes pc
            JOIN website_users wu ON pc.admin_id = wu.id
            WHERE pc.admin_id = ?
            ORDER BY pc.created_at DESC
        ";
        return $this->db->getAll($query, [$adminId]);
    }
    
    /**
     * Approve a pending change
     * 
     * @param int $id Pending change ID
     * @param int $reviewerId Reviewer ID
     * @param string $comments Review comments
     * @return bool True on success, false on failure
     */
    public function approvePendingChange($id, $reviewerId, $comments = '') {
        // Get the pending change
        $pendingChange = $this->getPendingChangeById($id);
        
        if (!$pendingChange) {
            return false;
        }
        
        // Update the status
        $updateData = [
            'status' => 'approved',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_comments' => $comments
        ];
        
        $updated = $this->db->update('pending_changes', $updateData, 'id = ?', [$id]);
        
        if (!$updated) {
            return false;
        }
        
        // Apply the change to the actual table
        return $this->applyChange($pendingChange);
    }
    
    /**
     * Reject a pending change
     * 
     * @param int $id Pending change ID
     * @param int $reviewerId Reviewer ID
     * @param string $comments Review comments
     * @return bool True on success, false on failure
     */
    public function rejectPendingChange($id, $reviewerId, $comments = '') {
        $updateData = [
            'status' => 'rejected',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'review_comments' => $comments
        ];
        
        return $this->db->update('pending_changes', $updateData, 'id = ?', [$id]);
    }
    
    /**
     * Apply a change to the actual table
     * 
     * @param array $pendingChange Pending change data
     * @return bool True on success, false on failure
     */
    private function applyChange($pendingChange) {
        $targetTable = $pendingChange['target_table'];
        $targetId = $pendingChange['target_id'];
        $fieldName = $pendingChange['field_name'];
        $newValue = $pendingChange['new_value'];
        
        // Determine primary key field
        $primaryKey = $this->getPrimaryKeyField($targetTable);
        
        if (!$primaryKey) {
            return false;
        }
        
        // Update the record
        return $this->db->update(
            $targetTable,
            [$fieldName => $newValue],
            $primaryKey . ' = ?',
            [$targetId]
        );
    }
    
    /**
     * Get the primary key field for a table
     * 
     * @param string $table Table name
     * @return string|false Primary key field or false if not found
     */
    private function getPrimaryKeyField($table) {
        // Common primary keys, add more if needed
        $primaryKeys = [
            'players' => 'citizenid',
            'website_users' => 'id',
            'player_vehicles' => 'plate'
        ];
        
        return isset($primaryKeys[$table]) ? $primaryKeys[$table] : false;
    }
    
    /**
     * Count pending changes
     * 
     * @param string $status Status to count (optional)
     * @return int Number of pending changes
     */
    public function countPendingChanges($status = null) {
        if ($status) {
            $query = "SELECT COUNT(*) as count FROM pending_changes WHERE status = ?";
            $result = $this->db->getSingle($query, [$status]);
        } else {
            $query = "SELECT COUNT(*) as count FROM pending_changes";
            $result = $this->db->getSingle($query);
        }
        
        if (!$result) {
            return 0;
        }
        
        return $result['count'];
    }
    
    /**
     * Delete old processed changes
     * 
     * @param int $days Number of days to keep
     * @return int|false Number of deleted rows or false on failure
     */
    public function deleteOldProcessedChanges($days = 30) {
        $query = "DELETE FROM pending_changes WHERE status != 'pending' AND reviewed_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->query($query, [$days]);
        
        if ($stmt) {
            return $stmt->rowCount();
        }
        
        return false;
    }
}
?> 