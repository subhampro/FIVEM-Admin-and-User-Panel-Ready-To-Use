<?php
/**
 * Database connection class
 */
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $charset;
    private $conn;
    
    /**
     * Constructor - Set database connection parameters
     */
    public function __construct() {
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;
        $this->database = 'fivem_panel'; // Hard-coded to use fivem_panel database
        $this->charset = DB_CHARSET;
        
        // Create connection
        $this->connect();
    }
    
    /**
     * Connect to the database
     */
    private function connect() {
        try {
            // Create connection using mysqli
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Check connection
            if ($this->conn->connect_error) {
                error_log("Database connection failed: " . $this->conn->connect_error);
                die("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set character set
            $this->conn->set_charset($this->charset);
            
            error_log("Database connection established successfully to {$this->database}");
        } catch (Exception $e) {
            error_log("Database connection exception: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get database connection
     * 
     * @return mysqli connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn->close();
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return mysqli_result|false
     */
    public function query($query, $params = []) {
        try {
            // For debugging
            error_log("Executing query: {$query} with params: " . print_r($params, true));
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            if (!empty($params)) {
                // Build types string for bind_param
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 's';
                    }
                }
                
                // Create reference array for bind_param
                $bindParams = array($types);
                foreach ($params as $key => $value) {
                    $bindParams[] = &$params[$key];
                }
                
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Database execute error: " . $stmt->error);
                return false;
            }
            
            return $stmt->get_result();
        } catch (Exception $e) {
            error_log("Database query exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a single record
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return array|false Single record or false if not found
     */
    public function getSingle($query, $params = []) {
        $stmt = $this->query($query, $params);
        
        if (!$stmt) {
            return false;
        }
        
        return $stmt->fetch_assoc();
    }
    
    /**
     * Get multiple records
     * 
     * @param string $query SQL query
     * @param array $params Parameters for the query
     * @return array|false Array of records or false if error
     */
    public function getAll($query, $params = []) {
        $stmt = $this->query($query, $params);
        
        if (!$stmt) {
            return false;
        }
        
        $results = [];
        while ($row = $stmt->fetch_assoc()) {
            $results[] = $row;
        }
        
        return $results;
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID or false if error
     */
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            // Log the query for debugging
            error_log("Executing insert query: {$query} with data: " . print_r(array_values($data), true));
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Generate types string for bind_param
            $types = '';
            $values = array_values($data);
            foreach ($values as $val) {
                if (is_int($val)) {
                    $types .= 'i';
                } elseif (is_float($val)) {
                    $types .= 'd';
                } elseif (is_string($val)) {
                    $types .= 's';
                } else {
                    $types .= 's';
                }
            }
            
            // Bind parameters dynamically
            if (!empty($values)) {
                $bindParams = array($types);
                for ($i = 0; $i < count($values); $i++) {
                    $bindParams[] = &$values[$i];
                }
                
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
                
                error_log("Bound parameters with types: {$types}");
            }
            
            // Execute the statement
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Database execute error: " . $stmt->error);
                return false;
            }
            
            $lastId = $this->conn->insert_id;
            error_log("Insert successful. Last ID: {$lastId}");
            
            $stmt->close();
            return $lastId;
        } catch (Exception $e) {
            error_log("Database insert exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a record
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return int|false Number of affected rows or false if error
     */
    public function update($table, $data, $where, $params = []) {
        try {
            $set = [];
            
            foreach ($data as $column => $value) {
                $set[] = "{$column} = ?";
            }
            
            $set = implode(', ', $set);
            
            $query = "UPDATE {$table} SET {$set} WHERE {$where}";
            
            $values = array_merge(array_values($data), $params);
            
            // Log the query for debugging
            error_log("Executing update query: {$query} with data: " . print_r($values, true));
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Generate types string for bind_param
            $types = '';
            foreach ($values as $val) {
                if (is_int($val)) {
                    $types .= 'i';
                } elseif (is_float($val)) {
                    $types .= 'd';
                } elseif (is_string($val)) {
                    $types .= 's';
                } else {
                    $types .= 's';
                }
            }
            
            // Bind parameters dynamically
            if (!empty($values)) {
                $bindParams = array($types);
                for ($i = 0; $i < count($values); $i++) {
                    $bindParams[] = &$values[$i];
                }
                
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
            }
            
            // Execute the statement
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Database execute error: " . $stmt->error);
                return false;
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            return $affectedRows;
        } catch (Exception $e) {
            error_log("Database update exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a record
     * 
     * @param string $table Table name
     * @param string $where Where clause
     * @param array $params Parameters for where clause
     * @return int|false Number of affected rows or false if error
     */
    public function delete($table, $where, $params = []) {
        try {
            $query = "DELETE FROM {$table} WHERE {$where}";
            
            // Log the query for debugging
            error_log("Executing delete query: {$query} with params: " . print_r($params, true));
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare error: " . $this->conn->error);
                return false;
            }
            
            // Generate types string for bind_param
            if (!empty($params)) {
                $types = '';
                foreach ($params as $val) {
                    if (is_int($val)) {
                        $types .= 'i';
                    } elseif (is_float($val)) {
                        $types .= 'd';
                    } elseif (is_string($val)) {
                        $types .= 's';
                    } else {
                        $types .= 's';
                    }
                }
                
                // Bind parameters dynamically
                $bindParams = array($types);
                for ($i = 0; $i < count($params); $i++) {
                    $bindParams[] = &$params[$i];
                }
                
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
            }
            
            // Execute the statement
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Database execute error: " . $stmt->error);
                return false;
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            return $affectedRows;
        } catch (Exception $e) {
            error_log("Database delete exception: " . $e->getMessage());
            return false;
        }
    }
}
?> 