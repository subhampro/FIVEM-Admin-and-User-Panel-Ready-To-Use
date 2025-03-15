-- Database Schema for FiveM Server Website
-- Website User Management and Admin Panel

-- Create and use fivem_panel database
DROP DATABASE IF EXISTS `fivem_panel`;
CREATE DATABASE IF NOT EXISTS `fivem_panel` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `fivem_panel`;

-- Create players table first (simplified version of what's in the game database)
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

-- Table for website users (to integrate with game players)
CREATE TABLE IF NOT EXISTS `website_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL, -- Will store hashed passwords
  `email` varchar(255) NOT NULL,
  `player_id` int(11) DEFAULT NULL, -- Linked to players table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table to log all actions
CREATE TABLE IF NOT EXISTS `action_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_action_logs_website_users` FOREIGN KEY (`user_id`) REFERENCES `website_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for changes pending approval by admins
CREATE TABLE IF NOT EXISTS `pending_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `change_type` varchar(50) NOT NULL,
  `current_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reviewer_id` (`reviewer_id`),
  CONSTRAINT `fk_pending_changes_user` FOREIGN KEY (`user_id`) REFERENCES `website_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pending_changes_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `website_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for website settings
CREATE TABLE IF NOT EXISTS `website_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `fk_website_settings_website_users` FOREIGN KEY (`updated_by`) REFERENCES `website_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for API tokens
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_api_tokens_website_users` FOREIGN KEY (`user_id`) REFERENCES `website_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default website settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_description`) VALUES
('site_name', 'FiveM Server Dashboard', 'Name of the website'),
('site_description', 'Admin and User Dashboard for our FiveM Server', 'Description of the website'),
('allow_registration', '1', 'Whether new user registration is enabled'),
('maintenance_mode', '0', 'Whether the site is in maintenance mode'),
('admin_email', 'admin@example.com', 'Administrator contact email'),
('email_verification', '0', 'Whether email verification is required for new users');

-- Insert default admin user
INSERT INTO `players` (`citizenid`, `license`, `name`, `job`, `identifier`, `steam_id`) VALUES
('ADMIN123', 'license:admin', 'Admin User', 'admin', 'admin', 'steam:admin');

INSERT INTO `website_users` (`username`, `password`, `email`, `player_id`, `is_admin`, `role`, `is_active`) VALUES
('admin', '$2y$10$cFgzF8aU7BXya4OMeqHGH.IOdJXlQZHfKJsXl9XW/5sLewltQdIpC', 'admin@example.com', 1, 1, 'admin_level3', 1); 