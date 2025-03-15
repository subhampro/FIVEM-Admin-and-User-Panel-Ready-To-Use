<?php
// Include configuration
require_once 'config/config.php';

// Set error reporting to maximum
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Setup Script</h1>";

// Connect to the MySQL server
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
    echo "<p style='color:green;'>Connected to MySQL server successfully!</p>";
} catch (Exception $e) {
    die("<p style='color:red;'>Connection failed: " . $e->getMessage() . "</p>");
}

// Check if the fivem_panel database exists
$result = $conn->query("SHOW DATABASES LIKE 'fivem_panel'");
$databaseExists = ($result->num_rows > 0);

if ($databaseExists) {
    echo "<p>The fivem_panel database already exists.</p>";
    
    // Ask if the user wants to recreate the database
    echo "<form method='post' action=''>";
    echo "<p style='color:orange;'>Warning: Recreating the database will delete all existing data!</p>";
    echo "<input type='hidden' name='action' value='recreate'>";
    echo "<button type='submit' style='background-color: #f44336; color: white; padding: 10px 15px; border: none; cursor: pointer;'>Recreate Database</button>";
    echo "</form>";
    
    // Check if the form has been submitted
    if (isset($_POST['action']) && $_POST['action'] === 'recreate') {
        echo "<p>Recreating the database...</p>";
        
        // Drop the database
        if ($conn->query("DROP DATABASE IF EXISTS `fivem_panel`")) {
            echo "<p>Database dropped successfully.</p>";
            $databaseExists = false;
        } else {
            echo "<p style='color:red;'>Failed to drop database: " . $conn->error . "</p>";
        }
    }
}

// Create the database if it doesn't exist
if (!$databaseExists) {
    echo "<p>Creating the fivem_panel database...</p>";
    
    if ($conn->query("CREATE DATABASE IF NOT EXISTS `fivem_panel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "<p style='color:green;'>Database created successfully!</p>";
        
        // Select the database
        $conn->select_db('fivem_panel');
        
        // Create tables
        echo "<p>Creating tables...</p>";
        
        // Create players table
        $createPlayersTable = "
            CREATE TABLE IF NOT EXISTS `players` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `citizenid` varchar(50) NOT NULL,
                `license` varchar(255) NOT NULL,
                `name` varchar(255) NOT NULL,
                `money` text DEFAULT NULL,
                `bank` float DEFAULT 0,
                `job` varchar(50) DEFAULT 'unemployed',
                `job_grade` int(11) DEFAULT 0,
                `identifier` varchar(255) DEFAULT NULL,
                `steam_id` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `citizenid` (`citizenid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        if ($conn->query($createPlayersTable)) {
            echo "<p style='color:green;'>Players table created successfully!</p>";
        } else {
            echo "<p style='color:red;'>Failed to create players table: " . $conn->error . "</p>";
        }
        
        // Create website_users table
        echo "<p>Creating website_users table...</p>";
        $sql = "CREATE TABLE IF NOT EXISTS `website_users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `player_id` int(11) DEFAULT NULL,
          `is_admin` tinyint(1) NOT NULL DEFAULT 0,
          `role` enum('user','admin_level1','admin_level2','admin_level3') NOT NULL DEFAULT 'user',
          `last_login` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `is_active` tinyint(1) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email` (`email`),
          KEY `player_id` (`player_id`),
          CONSTRAINT `fk_website_users_players` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>Website users table created successfully!</p>";
        } else {
            echo "<p style='color:red;'>Failed to create website_users table: " . $conn->error . "</p>";
        }
        
        // Check if the role column exists in website_users table
        echo "<p>Checking if role column exists in website_users table...</p>";
        $result = $conn->query("SHOW COLUMNS FROM `website_users` LIKE 'role'");
        $roleColumnExists = ($result && $result->num_rows > 0);

        if (!$roleColumnExists) {
            echo "<p>Adding role column to website_users table...</p>";
            $sql = "ALTER TABLE `website_users` ADD COLUMN `role` enum('user','admin_level1','admin_level2','admin_level3') NOT NULL DEFAULT 'user' AFTER `is_admin`";
            
            if ($conn->query($sql)) {
                echo "<p style='color:green;'>Role column added successfully!</p>";
                
                // Update existing admin users to have appropriate role
                $sql = "UPDATE `website_users` SET `role` = 'admin_level3' WHERE `is_admin` = 1";
                if ($conn->query($sql)) {
                    echo "<p style='color:green;'>Updated existing admin users with appropriate role!</p>";
                } else {
                    echo "<p style='color:red;'>Failed to update existing admins: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color:red;'>Failed to add role column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Role column already exists in website_users table.</p>";
        }
        
        // Insert default admin user
        $insertAdmin = "
            INSERT INTO `players` (`citizenid`, `license`, `name`, `job`, `identifier`, `steam_id`)
            VALUES ('ADMIN123', 'license:admin', 'Admin User', 'admin', 'admin', 'steam:admin');
        ";
        
        if ($conn->query($insertAdmin)) {
            echo "<p style='color:green;'>Default admin player created successfully!</p>";
            
            // Get the admin player ID
            $adminPlayerId = $conn->insert_id;
            
            // Create admin user
            $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
            
            $insertAdminUser = "
                INSERT INTO `website_users` (`username`, `password`, `email`, `player_id`, `is_admin`, `role`, `is_active`)
                VALUES ('admin', '{$hashedPassword}', 'admin@example.com', {$adminPlayerId}, 1, 'admin_level3', 1);
            ";
            
            if ($conn->query($insertAdminUser)) {
                echo "<p style='color:green;'>Default admin user created successfully!</p>";
                echo "<p>Username: admin</p>";
                echo "<p>Password: admin123</p>";
            } else {
                echo "<p style='color:red;'>Failed to create admin user: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Failed to create admin player: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Failed to create database: " . $conn->error . "</p>";
    }
}

// Check if the website_users table exists
$conn->select_db('fivem_panel');
$result = $conn->query("SHOW TABLES LIKE 'website_users'");
$tableExists = ($result->num_rows > 0);

if ($tableExists) {
    echo "<p style='color:green;'>The website_users table exists.</p>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE website_users");
    echo "<h2>website_users Table Structure</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] === NULL ? 'NULL' : $row['Default']) . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Show foreign keys
    echo "<h2>Foreign Keys</h2>";
    $foreignKeys = $conn->query("
        SELECT k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME
        FROM information_schema.TABLE_CONSTRAINTS i
        LEFT JOIN information_schema.KEY_COLUMN_USAGE k 
        ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME
        WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND i.TABLE_SCHEMA = DATABASE()
        AND i.TABLE_NAME = 'website_users'
    ");
    
    if ($foreignKeys->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Column</th><th>References Table</th><th>References Column</th></tr>";
        
        while ($row = $foreignKeys->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
            echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>No foreign keys found for website_users table!</p>";
    }
} else {
    echo "<p style='color:red;'>The website_users table does not exist!</p>";
    echo "<p>Please make sure table creation completed successfully.</p>";
}

// Copy players from game database
echo "<h2>Import Players from Game Database</h2>";
echo "<form method='post' action=''>";
echo "<input type='hidden' name='action' value='import_players'>";
echo "<button type='submit' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer;'>Import Players from Game Database</button>";
echo "</form>";

// Import players if requested
if (isset($_POST['action']) && $_POST['action'] === 'import_players') {
    try {
        // Connect to game database
        $gameDb = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'elapsed2_0');
        
        if ($gameDb->connect_error) {
            echo "<p style='color:red;'>Failed to connect to game database: " . $gameDb->connect_error . "</p>";
        } else {
            echo "<p style='color:green;'>Connected to game database successfully!</p>";
            
            // Get players from game database
            $result = $gameDb->query("SELECT citizenid, license, name FROM players LIMIT 100");
            
            if (!$result) {
                echo "<p style='color:red;'>Failed to get players from game database: " . $gameDb->error . "</p>";
            } else {
                $importedCount = 0;
                $skippedCount = 0;
                
                while ($player = $result->fetch_assoc()) {
                    // Check if player already exists in the fivem_panel database
                    $existsResult = $conn->query("SELECT id FROM players WHERE citizenid = '{$player['citizenid']}'");
                    
                    if ($existsResult->num_rows > 0) {
                        $skippedCount++;
                        continue;
                    }
                    
                    // Insert player into the fivem_panel database
                    $insertPlayer = "
                        INSERT INTO players (citizenid, license, name)
                        VALUES ('{$player['citizenid']}', '{$player['license']}', '{$player['name']}')
                    ";
                    
                    if ($conn->query($insertPlayer)) {
                        $importedCount++;
                    } else {
                        echo "<p style='color:red;'>Failed to import player {$player['citizenid']}: " . $conn->error . "</p>";
                    }
                }
                
                echo "<p style='color:green;'>Imported {$importedCount} players, skipped {$skippedCount} players (already exist).</p>";
            }
            
            $gameDb->close();
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Import players exception: " . $e->getMessage() . "</p>";
    }
}

// Close the connection
$conn->close();

echo "<h2>Next Steps</h2>";
echo "<p>1. Go to <a href='test_insert.php'>test_insert.php</a> to test database insertion.</p>";
echo "<p>2. Go to <a href='test_register.php'>test_register.php</a> to test the registration process.</p>";
echo "<p>3. Return to <a href='register.php'>register.php</a> to try the actual registration form.</p>";
?> 