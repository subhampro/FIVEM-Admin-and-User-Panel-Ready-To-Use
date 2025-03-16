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
            
            $result = $this->db->query($query, $params);
            if ($result) {
                error_log("Successfully added pending change for {$targetTable}.{$fieldName}");
                return $this->conn->insert_id;
            }
            
            error_log("Failed to add pending change for {$targetTable}.{$fieldName}");
            return false;
        } catch (Exception $e) {
            error_log("Exception in addPendingChange: " . $e->getMessage());
            throw $e;
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
            $this->db->beginTransaction();
            
            // Check if the pending change exists and is pending
            $stmtCheck = $this->db->prepare("SELECT * FROM pending_changes WHERE id = :id AND status = 'pending'");
            $stmtCheck->bindParam(':id', $changeId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $change = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$change) {
                $logger->logError("Failed to approve change: Change ID {$changeId} does not exist or is not pending");
                $this->db->rollBack();
                return false;
            }
            
            // Apply the change to the database
            $applied = $this->applyChange($change);
            
            if (!$applied) {
                $logger->logError("Failed to apply change ID {$changeId}");
                $this->db->rollBack();
                return false;
            }
            
            // Update the pending change status to approved
            $reviewedAt = date('Y-m-d H:i:s');
            $updateStmt = $this->db->prepare("
                UPDATE pending_changes 
                SET status = 'approved', 
                    reviewer_id = :reviewer_id, 
                    reviewed_at = :reviewed_at, 
                    review_comments = :comments 
                WHERE id = :id
            ");
            
            $updateStmt->bindParam(':reviewer_id', $reviewerId, PDO::PARAM_INT);
            $updateStmt->bindParam(':reviewed_at', $reviewedAt, PDO::PARAM_STR);
            $updateStmt->bindParam(':comments', $comments, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $changeId, PDO::PARAM_INT);
            
            $result = $updateStmt->execute();
            
            if (!$result) {
                $logger->logError("Failed to update pending change status for change #{$changeId}");
                $this->db->rollBack();
                return false;
            }
            
            // Log the approval
            $logger->logAction(
                $reviewerId, 
                'pending_change', 
                "Approved change request #{$changeId}: {$change['target_table']}.{$change['field_name']} for {$change['target_id']}"
            );
            
            // Commit the transaction
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            $logger->logError("Database error in approveChange: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            $logger->logError("Error in approveChange: " . $e->getMessage());
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
            
            $result = $this->db->query($query, $params);
            if (!$result) {
                error_log("Failed to reject change #{$id}");
                return false;
            }
            
            error_log("Successfully rejected change #{$id}");
            return true;
        } catch (Exception $e) {
            error_log("Exception in rejectChange: " . $e->getMessage());
            throw $e;
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