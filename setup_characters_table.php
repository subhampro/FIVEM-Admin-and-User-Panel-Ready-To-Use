<?php
// Include initialization file
require_once 'config/init.php';

// This script creates the user_characters table if it doesn't exist

// Connect to database
$db = new Database();
$conn = $db->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Character Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px;
        }
        .container {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        h2 {
            color: #4f46e5;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .success {
            color: #4ade80;
            background-color: rgba(74, 222, 128, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #f87171;
            background-color: rgba(248, 113, 113, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            color: #60a5fa;
            background-color: rgba(96, 165, 250, 0.1);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        a {
            color: #4f46e5;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #4338ca;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Setting up Character Management Tables</h2>
        
        <?php
        try {
            // Check if table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'user_characters'");
            if ($tableCheck->num_rows > 0) {
                echo '<div class="info">Table \'user_characters\' already exists. No action needed.</div>';
            } else {
                // Create table
                $createTable = "
                CREATE TABLE IF NOT EXISTS `user_characters` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `user_id` int(11) NOT NULL,
                  `citizenid` varchar(50) NOT NULL,
                  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
                  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `user_citizenid` (`user_id`, `citizenid`),
                  KEY `user_id` (`user_id`),
                  KEY `citizenid` (`citizenid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                if ($conn->query($createTable) === TRUE) {
                    echo '<div class="success">Table \'user_characters\' created successfully!</div>';
                } else {
                    echo '<div class="error">Error creating table: ' . $conn->error . '</div>';
                }
            }
            
        } catch (Exception $e) {
            echo '<div class="error">Error: ' . $e->getMessage() . '</div>';
        }
        ?>
        
        <p>Setup complete! You can now manage multiple characters in your account.</p>
        <a href="user/characters.php" class="btn">Go to Character Management</a>
    </div>
</body>
</html> 