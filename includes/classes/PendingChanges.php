<?php
/**
 * PendingChanges Class for managing changes that require approval
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
     * Add a new pending change request
     * 
     * @param int $adminId The admin user ID who made the change
     * @param string $targetTable The table being modified
     * @param string $targetId The ID of the record being modified
     * @param string $fieldName The field name being changed
     * @param mixed $oldValue The original value
     * @param mixed $newValue The new value
     * @return int|bool The ID of the new pending change or false on failure
     */
    public function addPendingChange($adminId, $targetTable, $targetId, $fieldName, $oldValue, $newValue) {
        $query = "INSERT INTO pending_changes (admin_id, target_table, target_id, field_name, old_value, new_value, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $params = [
            $adminId,
            $targetTable,
            $targetId,
            $fieldName,
            $oldValue,
            $newValue
        ];
        
        $result = $this->db->execute($query, $params);
        if ($result) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Get all pending changes
     * 
     * @param string $status The status to filter by ('pending', 'approved', 'rejected')
     * @param int $limit Limit the number of results
     * @param int $offset Offset for pagination
     * @return array List of pending changes
     */
    public function getPendingChanges($status = 'pending', $limit = 50, $offset = 0) {
        $query = "SELECT pc.*, 
                  admin.username as admin_username, 
                  reviewer.username as reviewer_username 
                  FROM pending_changes pc 
                  LEFT JOIN website_users admin ON pc.admin_id = admin.id 
                  LEFT JOIN website_users reviewer ON pc.reviewer_id = reviewer.id 
                  WHERE pc.status = ? 
                  ORDER BY pc.created_at DESC 
                  LIMIT ?, ?";
        
        return $this->db->getAll($query, [$status, $offset, $limit]);
    }
    
    /**
     * Count pending changes by status
     * 
     * @param string $status The status to count ('pending', 'approved', 'rejected')
     * @return int The count of pending changes
     */
    public function countPendingChanges($status = 'pending') {
        $query = "SELECT COUNT(*) as count FROM pending_changes WHERE status = ?";
        $result = $this->db->getSingle($query, [$status]);
        
        return $result ? $result['count'] : 0;
    }
    
    /**
     * Get a single pending change by ID
     * 
     * @param int $id The pending change ID
     * @return array|bool The pending change or false if not found
     */
    public function getPendingChangeById($id) {
        $query = "SELECT pc.*, 
                  admin.username as admin_username, 
                  reviewer.username as reviewer_username 
                  FROM pending_changes pc 
                  LEFT JOIN website_users admin ON pc.admin_id = admin.id 
                  LEFT JOIN website_users reviewer ON pc.reviewer_id = reviewer.id 
                  WHERE pc.id = ?";
        
        return $this->db->getSingle($query, [$id]);
    }
    
    /**
     * Approve a pending change and apply it to the database
     * 
     * @param int $id The pending change ID
     * @param int $reviewerId The admin user ID who approved the change
     * @param string $comments Comments about the approval
     * @return bool True on success, false on failure
     */
    public function approveChange($id, $reviewerId, $comments = '') {
        // Start transaction
        $this->conn->begin_transaction();
        
        try {
            // Get the pending change
            $change = $this->getPendingChangeById($id);
            
            if (!$change || $change['status'] !== 'pending') {
                throw new Exception("Pending change not found or already processed");
            }
            
            // Apply the change to the target table
            $success = $this->applyChange($change);
            
            if (!$success) {
                throw new Exception("Failed to apply change to database");
            }
            
            // Update the pending change status
            $updateQuery = "UPDATE pending_changes SET 
                            status = 'approved', 
                            reviewer_id = ?, 
                            reviewed_at = NOW(), 
                            review_comments = ? 
                            WHERE id = ?";
            
            $updateParams = [
                $reviewerId,
                $comments,
                $id
            ];
            
            $result = $this->db->execute($updateQuery, $updateParams);
            
            if (!$result) {
                throw new Exception("Failed to update pending change status");
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Error approving change: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reject a pending change
     * 
     * @param int $id The pending change ID
     * @param int $reviewerId The admin user ID who rejected the change
     * @param string $comments Reason for rejection
     * @return bool True on success, false on failure
     */
    public function rejectChange($id, $reviewerId, $comments = '') {
        $query = "UPDATE pending_changes SET 
                  status = 'rejected', 
                  reviewer_id = ?, 
                  reviewed_at = NOW(), 
                  review_comments = ? 
                  WHERE id = ? AND status = 'pending'";
        
        $params = [
            $reviewerId,
            $comments,
            $id
        ];
        
        return $this->db->execute($query, $params);
    }
    
    /**
     * Apply a change to the database
     * 
     * @param array $change The pending change data
     * @return bool True on success, false on failure
     */
    private function applyChange($change) {
        try {
            $targetTable = $change['target_table'];
            $targetId = $change['target_id'];
            $fieldName = $change['field_name'];
            $newValue = $change['new_value'];
            
            // Check if this is a JSON field with subfield
            $parts = explode('.', $fieldName);
            $mainField = $parts[0];
            $subField = isset($parts[1]) ? $parts[1] : null;
            
            // Handle different tables
            switch ($targetTable) {
                case 'players':
                    // Connect to game database
                    $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
                    if ($gameDb->connect_error) {
                        throw new Exception("Failed to connect to game database");
                    }
                    
                    // Different handling for different fields
                    if ($subField) {
                        // This is a JSON field with subfield
                        // Get the current data
                        $query = "SELECT {$mainField} FROM {$targetTable} WHERE citizenid = ?";
                        $stmt = $gameDb->prepare($query);
                        $stmt->bind_param('s', $targetId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        
                        if (!$row) {
                            throw new Exception("Player not found");
                        }
                        
                        // Parse the JSON data
                        $jsonData = json_decode($row[$mainField], true);
                        if (!$jsonData && $row[$mainField]) {
                            $jsonData = $row[$mainField]; // Handle non-JSON data or invalid JSON
                        }
                        
                        // Update the subfield
                        if (is_array($jsonData)) {
                            $jsonData[$subField] = $newValue;
                            
                            // Update the record
                            $updateQuery = "UPDATE {$targetTable} SET {$mainField} = ? WHERE citizenid = ?";
                            $stmt = $gameDb->prepare($updateQuery);
                            $jsonString = json_encode($jsonData);
                            $stmt->bind_param('ss', $jsonString, $targetId);
                            $result = $stmt->execute();
                            
                            if (!$result) {
                                throw new Exception("Failed to update JSON field");
                            }
                        } else {
                            // Special handling for money field which is stored as a string in DB but used as JSON in PHP
                            if ($mainField === 'money') {
                                // Assuming money is stored as JSON string
                                $moneyData = json_decode($row[$mainField], true) ?: [];
                                $moneyData[$subField] = (float)$newValue;
                                
                                $updateQuery = "UPDATE {$targetTable} SET {$mainField} = ? WHERE citizenid = ?";
                                $stmt = $gameDb->prepare($updateQuery);
                                $jsonString = json_encode($moneyData);
                                $stmt->bind_param('ss', $jsonString, $targetId);
                                $result = $stmt->execute();
                                
                                if (!$result) {
                                    throw new Exception("Failed to update money field");
                                }
                            } else {
                                throw new Exception("Field is not valid JSON");
                            }
                        }
                    } else {
                        // Direct field update
                        $updateQuery = "UPDATE {$targetTable} SET {$fieldName} = ? WHERE citizenid = ?";
                        $stmt = $gameDb->prepare($updateQuery);
                        $stmt->bind_param('ss', $newValue, $targetId);
                        $result = $stmt->execute();
                        
                        if (!$result) {
                            throw new Exception("Failed to update field");
                        }
                    }
                    
                    $gameDb->close();
                    break;
                    
                default:
                    throw new Exception("Unsupported target table: {$targetTable}");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error applying change: " . $e->getMessage());
            return false;
        }
    }
}
?> 