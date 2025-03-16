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
        try {
            // Prepare the statement
            $stmt = $this->conn->prepare("INSERT INTO pending_changes (admin_id, target_table, target_id, field_name, old_value, new_value, status, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
            
            if (!$stmt) {
                error_log("Failed to prepare statement for pending change: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters
            $status = 'pending';
            $stmt->bind_param('isssss', $adminId, $targetTable, $targetId, $fieldName, $oldValue, $newValue);
            
            // Execute statement
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Database error while adding pending change for {$targetTable}.{$fieldName}: " . $stmt->error);
                return false;
            }
            
            // Get the insert ID
            $insertId = $this->conn->insert_id;
            
            if ($insertId) {
                error_log("Successfully added pending change #{$insertId} for {$targetTable}.{$fieldName}");
                return $insertId;
            } else {
                error_log("Warning: Pending change for {$targetTable}.{$fieldName} was inserted but no ID was returned");
                return true; // Still return success if no insert ID but execution was successful
            }
        } catch (Exception $e) {
            error_log("Exception in addPendingChange for {$targetTable}.{$fieldName}: " . $e->getMessage());
            return false;
        }
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
     * @param int $changeId The pending change ID
     * @param int $reviewerId The admin user ID who approved the change
     * @param string $comments Comments about the approval
     * @return bool True on success, false on failure
     */
    public function approveChange($changeId, $reviewerId, $comments = '') {
        global $logger;
        
        try {
            // Start a transaction
            $this->conn->begin_transaction();
            
            // Check if the pending change exists and is pending
            $stmt = $this->conn->prepare("SELECT * FROM pending_changes WHERE id = ? AND status = 'pending'");
            $stmt->bind_param('i', $changeId);
            $stmt->execute();
            $result = $stmt->get_result();
            $change = $result->fetch_assoc();
            
            if (!$change) {
                error_log("Failed to approve change: Change ID {$changeId} does not exist or is not pending");
                $this->conn->rollback();
                return false;
            }
            
            // Apply the change to the database
            $applied = $this->applyChange($change);
            
            if (!$applied) {
                error_log("Failed to apply change ID {$changeId}");
                $this->conn->rollback();
                return false;
            }
            
            // Update the pending change status to approved
            $reviewedAt = date('Y-m-d H:i:s');
            $updateStmt = $this->conn->prepare("
                UPDATE pending_changes 
                SET status = 'approved', 
                    reviewer_id = ?,
                    reviewed_at = ?,
                    review_comments = ? 
                WHERE id = ?
            ");
            
            $updateStmt->bind_param('issi', $reviewerId, $reviewedAt, $comments, $changeId);
            $result = $updateStmt->execute();
            
            if (!$result) {
                error_log("Failed to update pending change status for change #{$changeId}: " . $updateStmt->error);
                $this->conn->rollback();
                return false;
            }
            
            // Log the approval
            if (isset($logger) && method_exists($logger, 'logAction')) {
                $logger->logAction(
                    $reviewerId, 
                    'pending_change', 
                    "Approved change request #{$changeId}: {$change['target_table']}.{$change['field_name']} for {$change['target_id']}"
                );
            } else {
                error_log("Successfully approved change #{$changeId}");
            }
            
            // Commit the transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollback();
            error_log("Error in approveChange: " . $e->getMessage());
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
        try {
            // Check if the pending change exists and is pending
            $stmt = $this->conn->prepare("SELECT * FROM pending_changes WHERE id = ? AND status = 'pending'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $change = $result->fetch_assoc();
            
            if (!$change) {
                error_log("Failed to reject change: Change ID {$id} does not exist or is not pending");
                return false;
            }
            
            // Update the pending change status to rejected
            $reviewedAt = date('Y-m-d H:i:s');
            $updateStmt = $this->conn->prepare("
                UPDATE pending_changes SET 
                status = 'rejected', 
                reviewer_id = ?, 
                reviewed_at = ?, 
                review_comments = ? 
                WHERE id = ? AND status = 'pending'
            ");
            
            $updateStmt->bind_param('issi', $reviewerId, $reviewedAt, $comments, $id);
            $result = $updateStmt->execute();
            
            if (!$result) {
                error_log("Failed to reject change #{$id}: " . $updateStmt->error);
                return false;
            }
            
            error_log("Successfully rejected change #{$id}");
            return true;
        } catch (Exception $e) {
            error_log("Exception in rejectChange: " . $e->getMessage());
            return false;
        }
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
            
            error_log("Applying change to {$targetTable}.{$fieldName} for ID {$targetId}");
            
            // Check if this is a JSON field with subfield
            $parts = explode('.', $fieldName);
            $mainField = $parts[0];
            $subField = isset($parts[1]) ? $parts[1] : null;
            
            // Handle different tables
            switch ($targetTable) {
                case 'players':
                    // Connect to game database - Use the correct database name from config
                    $gameDbName = defined('GAME_DB_NAME') ? GAME_DB_NAME : 'elapsed2_0';
                    $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, $gameDbName);
                    
                    if ($gameDb->connect_error) {
                        throw new Exception("Failed to connect to game database: " . $gameDb->connect_error);
                    }
                    
                    error_log("Connected to game database {$gameDbName} for applying change");
                    
                    // Different handling for different fields
                    if ($subField) {
                        // This is a JSON field with subfield
                        // Get the current data
                        $query = "SELECT {$mainField} FROM {$targetTable} WHERE citizenid = ?";
                        $stmt = $gameDb->prepare($query);
                        if (!$stmt) {
                            throw new Exception("Failed to prepare query: " . $gameDb->error);
                        }
                        
                        $stmt->bind_param('s', $targetId);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to execute query: " . $stmt->error);
                        }
                        
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        
                        if (!$row) {
                            throw new Exception("Player with citizenid {$targetId} not found");
                        }
                        
                        error_log("Retrieved current data for {$mainField}");
                        
                        // Parse the JSON data
                        $jsonData = json_decode($row[$mainField], true);
                        if (!$jsonData && $row[$mainField]) {
                            // Try to handle different formats
                            if ($mainField === 'money' || $mainField === 'job' || $mainField === 'charinfo') {
                                // For these fields, ensure we have a proper array even if stored differently
                                try {
                                    $jsonData = is_array($row[$mainField]) ? $row[$mainField] : json_decode($row[$mainField], true);
                                    if (!is_array($jsonData)) {
                                        $jsonData = array();
                                    }
                                } catch (Exception $e) {
                                    error_log("Exception parsing JSON data: " . $e->getMessage());
                                    $jsonData = array();
                                }
                            } else {
                                $jsonData = $row[$mainField]; // Handle non-JSON data or invalid JSON
                                error_log("Warning: Field {$mainField} is not valid JSON, treating as raw data");
                            }
                        }
                        
                        // Update the subfield
                        if (is_array($jsonData)) {
                            $jsonData[$subField] = $newValue;
                            error_log("Updated JSON subfield {$subField} in {$mainField}");
                            
                            // Update the record
                            $updateQuery = "UPDATE {$targetTable} SET {$mainField} = ? WHERE citizenid = ?";
                            $stmt = $gameDb->prepare($updateQuery);
                            if (!$stmt) {
                                throw new Exception("Failed to prepare update query: " . $gameDb->error);
                            }
                            
                            $jsonString = json_encode($jsonData);
                            $stmt->bind_param('ss', $jsonString, $targetId);
                            $result = $stmt->execute();
                            
                            if (!$result) {
                                throw new Exception("Failed to update JSON field: " . $stmt->error);
                            }
                            
                            // Check affected rows
                            if ($gameDb->affected_rows <= 0) {
                                error_log("Warning: No rows affected when updating {$mainField}.{$subField}");
                            }
                            
                            error_log("Successfully updated JSON field {$mainField}.{$subField}");
                        } else {
                            // Special handling for money field which is stored as a string in DB but used as JSON in PHP
                            if ($mainField === 'money') {
                                // Assuming money is stored as JSON string
                                $moneyData = is_array($jsonData) ? $jsonData : array();
                                $moneyData[$subField] = (float)$newValue;
                                error_log("Updated money subfield {$subField}");
                                
                                $updateQuery = "UPDATE {$targetTable} SET {$mainField} = ? WHERE citizenid = ?";
                                $stmt = $gameDb->prepare($updateQuery);
                                if (!$stmt) {
                                    throw new Exception("Failed to prepare money update query: " . $gameDb->error);
                                }
                                
                                $jsonString = json_encode($moneyData);
                                $stmt->bind_param('ss', $jsonString, $targetId);
                                $result = $stmt->execute();
                                
                                if (!$result) {
                                    throw new Exception("Failed to update money field: " . $stmt->error);
                                }
                                
                                // Check affected rows
                                if ($gameDb->affected_rows <= 0) {
                                    error_log("Warning: No rows affected when updating money.{$subField}");
                                }
                                
                                error_log("Successfully updated money field {$mainField}.{$subField}");
                            } else {
                                throw new Exception("Field {$mainField} is not valid JSON and not a special case field");
                            }
                        }
                    } else {
                        // Direct field update
                        $updateQuery = "UPDATE {$targetTable} SET {$fieldName} = ? WHERE citizenid = ?";
                        $stmt = $gameDb->prepare($updateQuery);
                        if (!$stmt) {
                            throw new Exception("Failed to prepare direct update query: " . $gameDb->error);
                        }
                        
                        $stmt->bind_param('ss', $newValue, $targetId);
                        $result = $stmt->execute();
                        
                        if (!$result) {
                            throw new Exception("Failed to update field {$fieldName}: " . $stmt->error);
                        }
                        
                        // Check affected rows
                        if ($gameDb->affected_rows <= 0) {
                            error_log("Warning: No rows affected when updating {$fieldName}");
                        }
                        
                        error_log("Successfully updated direct field {$fieldName}");
                    }
                    
                    $gameDb->close();
                    break;
                    
                default:
                    throw new Exception("Unsupported target table: {$targetTable}");
            }
            
            error_log("Successfully applied change to {$targetTable}.{$fieldName} for ID {$targetId}");
            return true;
        } catch (Exception $e) {
            error_log("Error applying change: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 