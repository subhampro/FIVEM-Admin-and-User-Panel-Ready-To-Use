-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for elapsed2_0
DROP DATABASE IF EXISTS `elapsed2_0`;
CREATE DATABASE IF NOT EXISTS `elapsed2_0` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;
USE `elapsed2_0`;

-- Dumping structure for table elapsed2_0.0r_crafting_queue
DROP TABLE IF EXISTS `0r_crafting_queue`;
CREATE TABLE IF NOT EXISTS `0r_crafting_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `name` varchar(64) NOT NULL,
  `label` varchar(64) NOT NULL,
  `count` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `image` text DEFAULT NULL,
  `ingredients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ingredients`)),
  `propModel` varchar(64) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `canItBeCraftable` tinyint(1) DEFAULT 0,
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.0r_crafting_queue: ~0 rows (approximately)
DELETE FROM `0r_crafting_queue`;

-- Dumping structure for table elapsed2_0.ak4y_garbage
DROP TABLE IF EXISTS `ak4y_garbage`;
CREATE TABLE IF NOT EXISTS `ak4y_garbage` (
  `citizenid` varchar(255) DEFAULT NULL,
  `currentXP` int(11) DEFAULT NULL,
  `Tasks` longtext DEFAULT NULL,
  `EarnedMoney` int(11) DEFAULT 0,
  `DrawedMoney` int(11) DEFAULT 0,
  `TaskResetTime` int(11) DEFAULT 0,
  `MoneyResetTime` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.ak4y_garbage: ~0 rows (approximately)
DELETE FROM `ak4y_garbage`;

-- Dumping structure for table elapsed2_0.ap_appointments
DROP TABLE IF EXISTS `ap_appointments`;
CREATE TABLE IF NOT EXISTS `ap_appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(100) DEFAULT NULL,
  `name` tinytext DEFAULT NULL,
  `appData` longtext DEFAULT NULL,
  `type` varchar(60) DEFAULT NULL,
  `state` int(11) DEFAULT 0,
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.ap_appointments: ~0 rows (approximately)
DELETE FROM `ap_appointments`;

-- Dumping structure for table elapsed2_0.ap_bar
DROP TABLE IF EXISTS `ap_bar`;
CREATE TABLE IF NOT EXISTS `ap_bar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` varchar(100) NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Name',
  `mobile` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Phone Number',
  `job` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Job',
  `bar_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0000000',
  `bar_date` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '01 JAN 2022',
  `bar_r_reason` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Reason',
  `bar_state` int(11) NOT NULL DEFAULT 0,
  `app_state` int(11) NOT NULL DEFAULT 0,
  `app_reason` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Reason',
  `app_date` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Date',
  `jury_state` int(11) NOT NULL DEFAULT 0,
  `jury_pay` int(11) NOT NULL DEFAULT 0,
  `jury_case` int(11) NOT NULL DEFAULT 0,
  `jury_v_state` int(11) NOT NULL DEFAULT 0,
  `jury_case_date` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Court Date',
  `jury_removal` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Removal Reason',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_bar: ~0 rows (approximately)
DELETE FROM `ap_bar`;

-- Dumping structure for table elapsed2_0.ap_cases
DROP TABLE IF EXISTS `ap_cases`;
CREATE TABLE IF NOT EXISTS `ap_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judgeid` varchar(100) NOT NULL DEFAULT 'Judge ID',
  `judgen` varchar(35) NOT NULL DEFAULT 'Add To Case',
  `defendantid` varchar(100) NOT NULL DEFAULT 'Defendant ID',
  `defendantn` varchar(35) NOT NULL DEFAULT 'Add To Case',
  `defenseid` varchar(100) NOT NULL DEFAULT 'Defense ID',
  `defensen` varchar(35) NOT NULL DEFAULT 'Add To Case',
  `casename` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Case Name',
  `job_request` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Job Name',
  `courtfees` int(11) DEFAULT 0,
  `courtdate` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Date & Time',
  `outcome` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Court Outcome',
  `guilty` int(11) DEFAULT 0,
  `not_guilty` int(11) DEFAULT 0,
  `no_show_state` int(11) DEFAULT 0,
  `settlement_state` int(11) DEFAULT 0,
  `state` int(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_cases: ~0 rows (approximately)
DELETE FROM `ap_cases`;

-- Dumping structure for table elapsed2_0.ap_criminalarchives
DROP TABLE IF EXISTS `ap_criminalarchives`;
CREATE TABLE IF NOT EXISTS `ap_criminalarchives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(50) DEFAULT NULL,
  `name` varchar(75) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_criminalarchives: ~0 rows (approximately)
DELETE FROM `ap_criminalarchives`;

-- Dumping structure for table elapsed2_0.ap_dlcsettings
DROP TABLE IF EXISTS `ap_dlcsettings`;
CREATE TABLE IF NOT EXISTS `ap_dlcsettings` (
  `script` varchar(60) DEFAULT 'SCRIPT',
  `settings` longtext DEFAULT '{}',
  `other` longtext DEFAULT '{}',
  `id_storage` longtext NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_dlcsettings: ~1 rows (approximately)
DELETE FROM `ap_dlcsettings`;
INSERT INTO `ap_dlcsettings` (`script`, `settings`, `other`, `id_storage`) VALUES
	('ap_voting', '{"currentType":0,"voteState":0,"funds":10000}', '{"Item":0.10,"Vehicle":0.15,"Business":0.15,"Housing":0.20,"Income":0.10}', '{}');

-- Dumping structure for table elapsed2_0.ap_documents
DROP TABLE IF EXISTS `ap_documents`;
CREATE TABLE IF NOT EXISTS `ap_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentid` varchar(50) DEFAULT NULL,
  `identifier` varchar(100) DEFAULT NULL,
  `document` longtext DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `catergory` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.ap_documents: ~0 rows (approximately)
DELETE FROM `ap_documents`;

-- Dumping structure for table elapsed2_0.ap_judgeexam
DROP TABLE IF EXISTS `ap_judgeexam`;
CREATE TABLE IF NOT EXISTS `ap_judgeexam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` longtext DEFAULT 'Question',
  `a` longtext DEFAULT 'Answer A',
  `b` longtext DEFAULT 'Answer B',
  `c` longtext DEFAULT 'Answer C',
  `d` longtext DEFAULT 'Answer D',
  `answer` longtext DEFAULT 'Correct Answer',
  `last_changed_by` varchar(70) DEFAULT 'Judge Name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_judgeexam: ~6 rows (approximately)
DELETE FROM `ap_judgeexam`;
INSERT INTO `ap_judgeexam` (`id`, `question`, `a`, `b`, `c`, `d`, `answer`, `last_changed_by`) VALUES
	(1, 'Question Example #1', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name'),
	(2, 'Question Example #2', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name'),
	(3, 'Question Example #3', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name'),
	(4, 'Question Example #4', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name'),
	(5, 'Question Example #5', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name'),
	(6, 'Question Example #6', 'Answer A Example', 'Answer B Example', 'Answer C Example', 'Answer D Example', 'a', 'Judge Name');

-- Dumping structure for table elapsed2_0.ap_tax
DROP TABLE IF EXISTS `ap_tax`;
CREATE TABLE IF NOT EXISTS `ap_tax` (
  `business` varchar(60) DEFAULT 'JOB NAME',
  `label` varchar(60) DEFAULT 'JOB LABEL',
  `amount_owed` int(11) DEFAULT 0,
  `total_tax_paid` int(11) DEFAULT 0,
  `pay_timer` int(11) DEFAULT 0,
  `owner` varchar(60) DEFAULT 'COMPANY OWNER',
  `base_tax` int(11) DEFAULT NULL,
  `grants` longtext DEFAULT 'nil'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_tax: ~0 rows (approximately)
DELETE FROM `ap_tax`;

-- Dumping structure for table elapsed2_0.ap_voting
DROP TABLE IF EXISTS `ap_voting`;
CREATE TABLE IF NOT EXISTS `ap_voting` (
  `identifier` varchar(100) DEFAULT NULL,
  `name` varchar(70) DEFAULT NULL,
  `age` text DEFAULT NULL,
  `shortDescription` mediumtext DEFAULT NULL,
  `whyDoYouWantToBeACandidate` longtext DEFAULT NULL,
  `WhatYoullBringToTheCity` longtext DEFAULT NULL,
  `denied` longtext DEFAULT 'N/A',
  `votes` int(10) DEFAULT NULL,
  `state` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ap_voting: ~0 rows (approximately)
DELETE FROM `ap_voting`;

-- Dumping structure for table elapsed2_0.bank_accounts_new
DROP TABLE IF EXISTS `bank_accounts_new`;
CREATE TABLE IF NOT EXISTS `bank_accounts_new` (
  `id` varchar(50) NOT NULL,
  `amount` int(11) DEFAULT 0,
  `transactions` longtext DEFAULT NULL,
  `auth` longtext DEFAULT NULL,
  `isFrozen` int(11) DEFAULT 0,
  `creator` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.bank_accounts_new: ~25 rows (approximately)
DELETE FROM `bank_accounts_new`;
INSERT INTO `bank_accounts_new` (`id`, `amount`, `transactions`, `auth`, `isFrozen`, `creator`) VALUES
	('ambulance', 0, '[]', '[]', 0, NULL),
	('ballas', 0, '[]', '[]', 0, NULL),
	('bcso', 0, '[]', '[]', 0, NULL),
	('bus', 0, '[]', '[]', 0, NULL),
	('cardealer', 0, '[]', '[]', 0, NULL),
	('cartel', 0, '[]', '[]', 0, NULL),
	('families', 0, '[]', '[]', 0, NULL),
	('garbage', 0, '[]', '[]', 0, NULL),
	('hotdog', 0, '[]', '[]', 0, NULL),
	('judge', 0, '[]', '[]', 0, NULL),
	('lawyer', 0, '[]', '[]', 0, NULL),
	('lostmc', 0, '[]', '[]', 0, NULL),
	('mechanic', 0, '[]', '[]', 0, NULL),
	('none', 0, '[]', '[]', 0, NULL),
	('police', 0, '[]', '[]', 0, NULL),
	('realestate', 0, '[]', '[]', 0, NULL),
	('reporter', 0, '[]', '[]', 0, NULL),
	('sasp', 0, '[]', '[]', 0, NULL),
	('taxi', 0, '[]', '[]', 0, NULL),
	('tow', 0, '[]', '[]', 0, NULL),
	('triads', 0, '[]', '[]', 0, NULL),
	('trucker', 0, '[]', '[]', 0, NULL),
	('unemployed', 0, '[]', '[]', 0, NULL),
	('vagos', 0, '[]', '[]', 0, NULL),
	('vineyard', 0, '[]', '[]', 0, NULL);

-- Dumping structure for table elapsed2_0.bans
DROP TABLE IF EXISTS `bans`;
CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `license` varchar(50) DEFAULT NULL,
  `discord` varchar(50) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `expire` int(11) DEFAULT NULL,
  `bannedby` varchar(255) NOT NULL DEFAULT 'LeBanhammer',
  PRIMARY KEY (`id`),
  KEY `license` (`license`),
  KEY `discord` (`discord`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.bans: ~0 rows (approximately)
DELETE FROM `bans`;

-- Dumping structure for table elapsed2_0.codem_bank_data
DROP TABLE IF EXISTS `codem_bank_data`;
CREATE TABLE IF NOT EXISTS `codem_bank_data` (
  `identifier` varchar(50) DEFAULT NULL,
  `loans` longtext DEFAULT NULL,
  `spendData` longtext DEFAULT NULL,
  `transaction` longtext DEFAULT NULL,
  `notify` longtext DEFAULT NULL,
  `blockAccount` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.codem_bank_data: ~0 rows (approximately)
DELETE FROM `codem_bank_data`;

-- Dumping structure for table elapsed2_0.codem_bank_society_log
DROP TABLE IF EXISTS `codem_bank_society_log`;
CREATE TABLE IF NOT EXISTS `codem_bank_society_log` (
  `accountname` varchar(50) DEFAULT NULL,
  `identifiers` longtext DEFAULT NULL,
  `spendData` longtext DEFAULT NULL,
  `notify` longtext DEFAULT NULL,
  `transaction` longtext DEFAULT NULL,
  UNIQUE KEY `accountname` (`accountname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.codem_bank_society_log: ~0 rows (approximately)
DELETE FROM `codem_bank_society_log`;

-- Dumping structure for table elapsed2_0.dealers
DROP TABLE IF EXISTS `dealers`;
CREATE TABLE IF NOT EXISTS `dealers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `coords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `time` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `createdby` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.dealers: ~0 rows (approximately)
DELETE FROM `dealers`;

-- Dumping structure for table elapsed2_0.drc_gardener
DROP TABLE IF EXISTS `drc_gardener`;
CREATE TABLE IF NOT EXISTS `drc_gardener` (
  `identifier` varchar(255) NOT NULL,
  `experience` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.drc_gardener: ~0 rows (approximately)
DELETE FROM `drc_gardener`;

-- Dumping structure for table elapsed2_0.gksphone_advertising
DROP TABLE IF EXISTS `gksphone_advertising`;
CREATE TABLE IF NOT EXISTS `gksphone_advertising` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) NOT NULL,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `firstname` varchar(256) NOT NULL,
  `message` longtext NOT NULL,
  `image` longtext DEFAULT NULL,
  `filter` varchar(255) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_advertising_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_advertising: ~0 rows (approximately)
DELETE FROM `gksphone_advertising`;

-- Dumping structure for table elapsed2_0.gksphone_bank_history
DROP TABLE IF EXISTS `gksphone_bank_history`;
CREATE TABLE IF NOT EXISTS `gksphone_bank_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `description` longtext NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_bank_histroy_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_bank_history: ~0 rows (approximately)
DELETE FROM `gksphone_bank_history`;

-- Dumping structure for table elapsed2_0.gksphone_billing
DROP TABLE IF EXISTS `gksphone_billing`;
CREATE TABLE IF NOT EXISTS `gksphone_billing` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `society` varchar(50) NOT NULL DEFAULT '',
  `societylabel` varchar(50) NOT NULL DEFAULT '',
  `sender` varchar(50) NOT NULL,
  `sendercitizenid` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT '',
  `bill_holder` varchar(50) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_billing: ~0 rows (approximately)
DELETE FROM `gksphone_billing`;

-- Dumping structure for table elapsed2_0.gksphone_calls
DROP TABLE IF EXISTS `gksphone_calls`;
CREATE TABLE IF NOT EXISTS `gksphone_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) NOT NULL DEFAULT '',
  `caller` varchar(50) NOT NULL,
  `receiver` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL,
  `hidden` int(11) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_calls_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_calls: ~0 rows (approximately)
DELETE FROM `gksphone_calls`;

-- Dumping structure for table elapsed2_0.gksphone_contacts
DROP TABLE IF EXISTS `gksphone_contacts`;
CREATE TABLE IF NOT EXISTS `gksphone_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `display` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `gksphone_contacts_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_contacts: ~0 rows (approximately)
DELETE FROM `gksphone_contacts`;

-- Dumping structure for table elapsed2_0.gksphone_darkchat_channels
DROP TABLE IF EXISTS `gksphone_darkchat_channels`;
CREATE TABLE IF NOT EXISTS `gksphone_darkchat_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(50) NOT NULL,
  `channel_created` tinyint(4) NOT NULL,
  `phone_id` varchar(50) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `phone_uniq` (`phone_id`) USING BTREE,
  CONSTRAINT `FK_gksphone_darkchat_channels_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_darkchat_channels: ~0 rows (approximately)
DELETE FROM `gksphone_darkchat_channels`;

-- Dumping structure for table elapsed2_0.gksphone_darkchat_message
DROP TABLE IF EXISTS `gksphone_darkchat_message`;
CREATE TABLE IF NOT EXISTS `gksphone_darkchat_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `phone_id` varchar(50) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `phone_uniq` (`phone_id`) USING BTREE,
  CONSTRAINT `FK_gksphone_darkchat_message_gksphone_darkchat_channels` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_darkchat_channels` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_darkchat_message: ~0 rows (approximately)
DELETE FROM `gksphone_darkchat_message`;

-- Dumping structure for table elapsed2_0.gksphone_esim
DROP TABLE IF EXISTS `gksphone_esim`;
CREATE TABLE IF NOT EXISTS `gksphone_esim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pin` int(11) NOT NULL DEFAULT 0,
  `operator` varchar(50) NOT NULL DEFAULT '0',
  `package_id` int(11) NOT NULL DEFAULT 0,
  `phone_number` varchar(50) NOT NULL DEFAULT '0',
  `phone_id` varchar(50) NOT NULL DEFAULT '',
  `is_active` tinyint(4) NOT NULL DEFAULT 0,
  `package_sms` int(11) NOT NULL DEFAULT 0,
  `package_call` int(11) NOT NULL DEFAULT 0,
  `package_internet` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone_number` (`phone_number`),
  KEY `idx_phone_id` (`phone_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_esim: ~0 rows (approximately)
DELETE FROM `gksphone_esim`;

-- Dumping structure for table elapsed2_0.gksphone_gallery
DROP TABLE IF EXISTS `gksphone_gallery`;
CREATE TABLE IF NOT EXISTS `gksphone_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `image` longtext NOT NULL,
  `favorite` tinyint(4) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_gallery_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_gallery: ~0 rows (approximately)
DELETE FROM `gksphone_gallery`;

-- Dumping structure for table elapsed2_0.gksphone_gameleaderboard
DROP TABLE IF EXISTS `gksphone_gameleaderboard`;
CREATE TABLE IF NOT EXISTS `gksphone_gameleaderboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tetris` int(11) NOT NULL DEFAULT 0,
  `snake` int(11) NOT NULL DEFAULT 0,
  `twenty` int(11) NOT NULL DEFAULT 0,
  `phone_id` varchar(500) NOT NULL,
  `name` varchar(500) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_gameleaderboard_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_gameleaderboard: ~0 rows (approximately)
DELETE FROM `gksphone_gameleaderboard`;

-- Dumping structure for table elapsed2_0.gksphone_gps
DROP TABLE IF EXISTS `gksphone_gps`;
CREATE TABLE IF NOT EXISTS `gksphone_gps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` longtext NOT NULL,
  `gps_name` longtext NOT NULL,
  `gps_coord` longtext NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_gps_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_gps: ~0 rows (approximately)
DELETE FROM `gksphone_gps`;

-- Dumping structure for table elapsed2_0.gksphone_mails
DROP TABLE IF EXISTS `gksphone_mails`;
CREATE TABLE IF NOT EXISTS `gksphone_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(255) NOT NULL DEFAULT '0',
  `sender` varchar(255) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '0',
  `image` varchar(250) DEFAULT NULL,
  `message` text NOT NULL,
  `button` longtext DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_mails: ~0 rows (approximately)
DELETE FROM `gksphone_mails`;

-- Dumping structure for table elapsed2_0.gksphone_messages
DROP TABLE IF EXISTS `gksphone_messages`;
CREATE TABLE IF NOT EXISTS `gksphone_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone_id` varchar(50) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_editable` tinyint(1) NOT NULL DEFAULT 0,
  `is_sender` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_messages_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_messages: ~0 rows (approximately)
DELETE FROM `gksphone_messages`;

-- Dumping structure for table elapsed2_0.gksphone_messages_groups
DROP TABLE IF EXISTS `gksphone_messages_groups`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `phone_number` varchar(50) NOT NULL,
  `iscreator` tinyint(4) NOT NULL DEFAULT 0,
  `group_name` longtext NOT NULL,
  `group_about` longtext NOT NULL,
  `group_image` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`),
  CONSTRAINT `FK_gksphone_messages_group_gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_messages_groups: ~0 rows (approximately)
DELETE FROM `gksphone_messages_groups`;

-- Dumping structure for table elapsed2_0.gksphone_messages_groups_members
DROP TABLE IF EXISTS `gksphone_messages_groups_members`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_groups_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE,
  CONSTRAINT `FK_gksphone_messages_groups_members_gksphone_messages_groups` FOREIGN KEY (`group_id`) REFERENCES `gksphone_messages_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gksphone_messages_groups_members_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table elapsed2_0.gksphone_messages_groups_members: ~0 rows (approximately)
DELETE FROM `gksphone_messages_groups_members`;

-- Dumping structure for table elapsed2_0.gksphone_messages_groups_messages
DROP TABLE IF EXISTS `gksphone_messages_groups_messages`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_groups_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sender` varchar(50) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE,
  CONSTRAINT `gksphone_messages_groups_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `gksphone_messages_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gksphone_messages_groups_messages_ibfk_2` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table elapsed2_0.gksphone_messages_groups_messages: ~0 rows (approximately)
DELETE FROM `gksphone_messages_groups_messages`;

-- Dumping structure for table elapsed2_0.gksphone_messages_post
DROP TABLE IF EXISTS `gksphone_messages_post`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_number` (`phone_number`) USING BTREE,
  CONSTRAINT `gksphone_messages_post_ibfk_1` FOREIGN KEY (`phone_number`) REFERENCES `gksphone_esim` (`phone_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table elapsed2_0.gksphone_messages_post: ~0 rows (approximately)
DELETE FROM `gksphone_messages_post`;

-- Dumping structure for table elapsed2_0.gksphone_messages_stories
DROP TABLE IF EXISTS `gksphone_messages_stories`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 24 hour),
  PRIMARY KEY (`id`),
  KEY `phone_number` (`phone_number`),
  CONSTRAINT `FK_gksphone_messages_stories_gksphone_esim` FOREIGN KEY (`phone_number`) REFERENCES `gksphone_esim` (`phone_number`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_messages_stories: ~0 rows (approximately)
DELETE FROM `gksphone_messages_stories`;

-- Dumping structure for table elapsed2_0.gksphone_messages_viewed_stories
DROP TABLE IF EXISTS `gksphone_messages_viewed_stories`;
CREATE TABLE IF NOT EXISTS `gksphone_messages_viewed_stories` (
  `phone_number` varchar(50) NOT NULL DEFAULT '',
  `story_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `phone_number` (`phone_number`),
  KEY `story_id` (`story_id`),
  CONSTRAINT `FK_gksphone_messages_viewed_stories_gksphone_esim` FOREIGN KEY (`phone_number`) REFERENCES `gksphone_esim` (`phone_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_gksphone_messages_viewed_stories_gksphone_messages_stories` FOREIGN KEY (`story_id`) REFERENCES `gksphone_messages_stories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_messages_viewed_stories: ~0 rows (approximately)
DELETE FROM `gksphone_messages_viewed_stories`;

-- Dumping structure for table elapsed2_0.gksphone_music
DROP TABLE IF EXISTS `gksphone_music`;
CREATE TABLE IF NOT EXISTS `gksphone_music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_name` longtext NOT NULL,
  `playlist_img` longtext NOT NULL,
  `details` longtext NOT NULL,
  `phone_id` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_music: ~0 rows (approximately)
DELETE FROM `gksphone_music`;

-- Dumping structure for table elapsed2_0.gksphone_music_like
DROP TABLE IF EXISTS `gksphone_music_like`;
CREATE TABLE IF NOT EXISTS `gksphone_music_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) NOT NULL,
  `songid` varchar(50) NOT NULL,
  `title` varchar(250) NOT NULL,
  `artist` varchar(250) NOT NULL,
  `img` longtext NOT NULL,
  `seconds` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK__gksphone_esim` (`phone_id`) USING BTREE,
  CONSTRAINT `FK__gksphone_esim` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_music_like: ~0 rows (approximately)
DELETE FROM `gksphone_music_like`;

-- Dumping structure for table elapsed2_0.gksphone_news
DROP TABLE IF EXISTS `gksphone_news`;
CREATE TABLE IF NOT EXISTS `gksphone_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_text` longtext NOT NULL,
  `news_title` longtext NOT NULL,
  `news_image` longtext DEFAULT NULL,
  `news_video` longtext DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_news: ~0 rows (approximately)
DELETE FROM `gksphone_news`;

-- Dumping structure for table elapsed2_0.gksphone_notes
DROP TABLE IF EXISTS `gksphone_notes`;
CREATE TABLE IF NOT EXISTS `gksphone_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `title` longtext NOT NULL,
  `image` longtext DEFAULT NULL,
  `note` longtext NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `phone_id` (`phone_id`) USING BTREE,
  CONSTRAINT `gksphone_notes_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table elapsed2_0.gksphone_notes: ~0 rows (approximately)
DELETE FROM `gksphone_notes`;

-- Dumping structure for table elapsed2_0.gksphone_settings
DROP TABLE IF EXISTS `gksphone_settings`;
CREATE TABLE IF NOT EXISTS `gksphone_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(50) NOT NULL DEFAULT '',
  `identifier` longtext NOT NULL,
  `setup_owner` longtext NOT NULL,
  `model_type` varchar(50) NOT NULL DEFAULT '0',
  `mail_address` longtext NOT NULL,
  `mail_password` longtext NOT NULL,
  `phone_lang` varchar(50) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `setup_status` tinyint(4) NOT NULL DEFAULT 0,
  `phone_password` varchar(50) NOT NULL DEFAULT '0',
  `look_id` tinyint(4) NOT NULL DEFAULT 0,
  `security_question` varchar(50) NOT NULL DEFAULT '0',
  `security_answer` longtext NOT NULL,
  `phone_settings` longtext NOT NULL,
  `social_accounts` text NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.gksphone_settings: ~0 rows (approximately)
DELETE FROM `gksphone_settings`;

-- Dumping structure for table elapsed2_0.gksphone_stockmarket
DROP TABLE IF EXISTS `gksphone_stockmarket`;
CREATE TABLE IF NOT EXISTS `gksphone_stockmarket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `stock_market` longtext NOT NULL,
  `stock_histroy` longtext NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `phone_id` (`phone_id`),
  CONSTRAINT `gksphone_stockmarket_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `gksphone_esim` (`phone_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- Dumping data for table elapsed2_0.gksphone_stockmarket: ~0 rows (approximately)
DELETE FROM `gksphone_stockmarket`;

-- Dumping structure for table elapsed2_0.gksphone_twt_followers
DROP TABLE IF EXISTS `gksphone_twt_followers`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_followers` (
  `follow_id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `followid` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`follow_id`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `FK_gksphone_twt_follower_gksphone_twt_users` (`followid`) USING BTREE,
  CONSTRAINT `FK_gksphone_twt_follower_gksphone_twt_users` FOREIGN KEY (`followid`) REFERENCES `gksphone_twt_users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_gksphone_twt_followers_gksphone_twt_users` FOREIGN KEY (`userid`) REFERENCES `gksphone_twt_users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_followers: ~0 rows (approximately)
DELETE FROM `gksphone_twt_followers`;

-- Dumping structure for table elapsed2_0.gksphone_twt_hastags
DROP TABLE IF EXISTS `gksphone_twt_hastags`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_hastags` (
  `hastag_id` int(11) NOT NULL AUTO_INCREMENT,
  `hastag` varchar(250) NOT NULL DEFAULT '',
  `postid` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`hastag_id`) USING BTREE,
  KEY `postid` (`postid`) USING BTREE,
  CONSTRAINT `FK_gksphone_twt_hastags_gksphone_twt_posts` FOREIGN KEY (`postid`) REFERENCES `gksphone_twt_posts` (`post_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_hastags: ~0 rows (approximately)
DELETE FROM `gksphone_twt_hastags`;

-- Dumping structure for table elapsed2_0.gksphone_twt_likepost
DROP TABLE IF EXISTS `gksphone_twt_likepost`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_likepost` (
  `like_id` int(11) NOT NULL AUTO_INCREMENT,
  `postid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`like_id`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `postid` (`postid`) USING BTREE,
  CONSTRAINT `FK_gksphone_twt_likepost_gksphone_twt_posts` FOREIGN KEY (`postid`) REFERENCES `gksphone_twt_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_gksphone_twt_likepost_gksphone_twt_users` FOREIGN KEY (`userid`) REFERENCES `gksphone_twt_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_likepost: ~0 rows (approximately)
DELETE FROM `gksphone_twt_likepost`;

-- Dumping structure for table elapsed2_0.gksphone_twt_posts
DROP TABLE IF EXISTS `gksphone_twt_posts`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` longtext NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `media` longtext DEFAULT NULL,
  `poll_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `comment` int(11) NOT NULL DEFAULT 0,
  `commentid` int(11) NOT NULL DEFAULT 0,
  `pinned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `gksphone_twt_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `gksphone_twt_users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `poll_options` CHECK (json_valid(`poll_options`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_posts: ~0 rows (approximately)
DELETE FROM `gksphone_twt_posts`;

-- Dumping structure for table elapsed2_0.gksphone_twt_retweet
DROP TABLE IF EXISTS `gksphone_twt_retweet`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_retweet` (
  `retwettsid` int(11) NOT NULL AUTO_INCREMENT,
  `postid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`retwettsid`) USING BTREE,
  KEY `userid` (`userid`) USING BTREE,
  KEY `postid` (`postid`) USING BTREE,
  CONSTRAINT `FK_gksphone_twt_retweet_gksphone_twt_posts` FOREIGN KEY (`postid`) REFERENCES `gksphone_twt_posts` (`post_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_gksphone_twt_retweet_gksphone_twt_users` FOREIGN KEY (`userid`) REFERENCES `gksphone_twt_users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_retweet: ~0 rows (approximately)
DELETE FROM `gksphone_twt_retweet`;

-- Dumping structure for table elapsed2_0.gksphone_twt_users
DROP TABLE IF EXISTS `gksphone_twt_users`;
CREATE TABLE IF NOT EXISTS `gksphone_twt_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` longtext NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `displayname` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `is_verified` int(11) NOT NULL DEFAULT 0,
  `verifedbuytime` timestamp NULL DEFAULT current_timestamp(),
  `banned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE KEY `unique_username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_twt_users: ~0 rows (approximately)
DELETE FROM `gksphone_twt_users`;

-- Dumping structure for table elapsed2_0.gksphone_vehicle_sales
DROP TABLE IF EXISTS `gksphone_vehicle_sales`;
CREATE TABLE IF NOT EXISTS `gksphone_vehicle_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` longtext NOT NULL,
  `player_name` varchar(50) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `plate` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `image` longtext NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_vehicle_sales: ~0 rows (approximately)
DELETE FROM `gksphone_vehicle_sales`;

-- Dumping structure for table elapsed2_0.gksphone_wanted
DROP TABLE IF EXISTS `gksphone_wanted`;
CREATE TABLE IF NOT EXISTS `gksphone_wanted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `reason` varchar(250) DEFAULT NULL,
  `appearance` varchar(250) DEFAULT NULL,
  `lastseen` varchar(250) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gksphone_wanted: ~0 rows (approximately)
DELETE FROM `gksphone_wanted`;

-- Dumping structure for table elapsed2_0.gym_tebex
DROP TABLE IF EXISTS `gym_tebex`;
CREATE TABLE IF NOT EXISTS `gym_tebex` (
  `tbx` longtext DEFAULT NULL,
  `active` tinyint(2) DEFAULT NULL,
  `packname` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.gym_tebex: ~0 rows (approximately)
DELETE FROM `gym_tebex`;

-- Dumping structure for table elapsed2_0.izzy_radio
DROP TABLE IF EXISTS `izzy_radio`;
CREATE TABLE IF NOT EXISTS `izzy_radio` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `player` varchar(255) NOT NULL DEFAULT '0',
  `data` longtext NOT NULL DEFAULT '[]',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.izzy_radio: ~0 rows (approximately)
DELETE FROM `izzy_radio`;

-- Dumping structure for table elapsed2_0.lapraces
DROP TABLE IF EXISTS `lapraces`;
CREATE TABLE IF NOT EXISTS `lapraces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `checkpoints` text DEFAULT NULL,
  `records` text DEFAULT NULL,
  `creator` varchar(50) DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  `raceid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raceid` (`raceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.lapraces: ~0 rows (approximately)
DELETE FROM `lapraces`;

-- Dumping structure for table elapsed2_0.lation_mining
DROP TABLE IF EXISTS `lation_mining`;
CREATE TABLE IF NOT EXISTS `lation_mining` (
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `exp` int(11) NOT NULL DEFAULT 0,
  `mined` int(11) NOT NULL DEFAULT 0,
  `smelted` int(11) NOT NULL DEFAULT 0,
  `earned` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.lation_mining: ~0 rows (approximately)
DELETE FROM `lation_mining`;

-- Dumping structure for table elapsed2_0.lgf_alldeliveries
DROP TABLE IF EXISTS `lgf_alldeliveries`;
CREATE TABLE IF NOT EXISTS `lgf_alldeliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ZoneName` varchar(255) NOT NULL,
  `AllDeliveries` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.lgf_alldeliveries: ~0 rows (approximately)
DELETE FROM `lgf_alldeliveries`;

-- Dumping structure for table elapsed2_0.lgf_logistic
DROP TABLE IF EXISTS `lgf_logistic`;
CREATE TABLE IF NOT EXISTS `lgf_logistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Player` varchar(255) NOT NULL,
  `CurrentLevel` int(11) NOT NULL,
  `reward_redeemed` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.lgf_logistic: ~0 rows (approximately)
DELETE FROM `lgf_logistic`;

-- Dumping structure for table elapsed2_0.management_funds
DROP TABLE IF EXISTS `management_funds`;
CREATE TABLE IF NOT EXISTS `management_funds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(50) NOT NULL,
  `amount` int(100) NOT NULL,
  `type` enum('boss','gang') NOT NULL DEFAULT 'boss',
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_name` (`job_name`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=141469 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.management_funds: ~31 rows (approximately)
DELETE FROM `management_funds`;
INSERT INTO `management_funds` (`id`, `job_name`, `amount`, `type`) VALUES
	(1, 'bcso', 0, 'boss'),
	(51, 'ems', 0, 'boss'),
	(53, 'catcafe', 0, 'boss'),
	(85, '6str', 0, 'boss'),
	(94, 'police', 0, 'boss'),
	(296, 'ammunation_vespucci', 0, 'boss'),
	(299, 'ammunation', 0, 'boss'),
	(323, 'ammunation_beretta', 0, 'boss'),
	(1188, 'mechanic', 0, 'boss'),
	(33359, 'redline', 0, 'boss'),
	(35069, 'ammove', 0, 'boss'),
	(35240, 'wwcc', 0, 'boss'),
	(35622, 'luxautos', 0, 'boss'),
	(37236, 'ammov', 0, 'boss'),
	(38507, 'ambulance', 0, 'boss'),
	(38782, 'ccafe', 0, 'boss'),
	(47750, 'fastlane', 0, 'boss'),
	(55893, 'ammom', 0, 'boss'),
	(59204, 'reporter', 0, 'boss'),
	(67579, 'benny', 0, 'boss'),
	(68125, 'unicorn', 0, 'boss'),
	(68536, 'realestate', 0, 'boss'),
	(70906, 'gxy', 0, 'boss'),
	(72972, 'wolfs', 0, 'gang'),
	(74004, 'oniels', 0, 'gang'),
	(79975, 'esmg', 0, 'gang'),
	(83454, 'vagos', 0, 'gang'),
	(103099, 'lawyer', 0, 'boss'),
	(126095, 'unemployed', 0, 'boss'),
	(137848, 'legion-kfc', 0, 'boss'),
	(138004, 'legion-starbucks', 0, 'boss');

-- Dumping structure for table elapsed2_0.management_outfits
DROP TABLE IF EXISTS `management_outfits`;
CREATE TABLE IF NOT EXISTS `management_outfits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `minrank` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT 'Cool Outfit',
  `gender` varchar(50) NOT NULL DEFAULT 'male',
  `model` varchar(50) DEFAULT NULL,
  `props` text DEFAULT NULL,
  `components` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.management_outfits: ~0 rows (approximately)
DELETE FROM `management_outfits`;

-- Dumping structure for table elapsed2_0.occasion_vehicles
DROP TABLE IF EXISTS `occasion_vehicles`;
CREATE TABLE IF NOT EXISTS `occasion_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller` varchar(50) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `plate` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `mods` text DEFAULT NULL,
  `occasionid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `occasionId` (`occasionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.occasion_vehicles: ~0 rows (approximately)
DELETE FROM `occasion_vehicles`;

-- Dumping structure for table elapsed2_0.origen_metadata
DROP TABLE IF EXISTS `origen_metadata`;
CREATE TABLE IF NOT EXISTS `origen_metadata` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_metadata: ~0 rows (approximately)
DELETE FROM `origen_metadata`;

-- Dumping structure for table elapsed2_0.origen_police_alerts
DROP TABLE IF EXISTS `origen_police_alerts`;
CREATE TABLE IF NOT EXISTS `origen_police_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `job` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_alerts: ~0 rows (approximately)
DELETE FROM `origen_police_alerts`;

-- Dumping structure for table elapsed2_0.origen_police_ankle
DROP TABLE IF EXISTS `origen_police_ankle`;
CREATE TABLE IF NOT EXISTS `origen_police_ankle` (
  `citizenid` varchar(50) NOT NULL,
  `policeOwner` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastShock` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`citizenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_ankle: ~0 rows (approximately)
DELETE FROM `origen_police_ankle`;

-- Dumping structure for table elapsed2_0.origen_police_bills
DROP TABLE IF EXISTS `origen_police_bills`;
CREATE TABLE IF NOT EXISTS `origen_police_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT '',
  `concepts` text DEFAULT '[]',
  `price` int(11) DEFAULT 0,
  `job` varchar(50) DEFAULT '',
  `author` varchar(255) DEFAULT '',
  `payed` int(1) DEFAULT 0,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `months` int(11) DEFAULT 0,
  `reportid` int(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_bills: ~0 rows (approximately)
DELETE FROM `origen_police_bills`;

-- Dumping structure for table elapsed2_0.origen_police_clocks
DROP TABLE IF EXISTS `origen_police_clocks`;
CREATE TABLE IF NOT EXISTS `origen_police_clocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `clockin` varchar(50) NOT NULL,
  `clockout` varchar(50) NOT NULL,
  `minutes` int(11) NOT NULL DEFAULT 0,
  `job` varchar(50) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_clocks: ~0 rows (approximately)
DELETE FROM `origen_police_clocks`;

-- Dumping structure for table elapsed2_0.origen_police_federal
DROP TABLE IF EXISTS `origen_police_federal`;
CREATE TABLE IF NOT EXISTS `origen_police_federal` (
  `citizenid` varchar(50) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `initial` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `danger` varchar(50) DEFAULT 'NP',
  `joinedfrom` varchar(50) DEFAULT 'Mission Row',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_federal: ~0 rows (approximately)
DELETE FROM `origen_police_federal`;

-- Dumping structure for table elapsed2_0.origen_police_notes
DROP TABLE IF EXISTS `origen_police_notes`;
CREATE TABLE IF NOT EXISTS `origen_police_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(60) NOT NULL,
  `title` varchar(255) DEFAULT '',
  `description` text DEFAULT '',
  `author` varchar(255) DEFAULT '',
  `date` timestamp NULL DEFAULT current_timestamp(),
  `fixed` int(1) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_notes: ~0 rows (approximately)
DELETE FROM `origen_police_notes`;

-- Dumping structure for table elapsed2_0.origen_police_penalc
DROP TABLE IF EXISTS `origen_police_penalc`;
CREATE TABLE IF NOT EXISTS `origen_police_penalc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT 'Error',
  `description` varchar(255) NOT NULL DEFAULT 'Error',
  `price` int(11) NOT NULL DEFAULT 0,
  `month` int(11) NOT NULL DEFAULT 0,
  `cap` int(1) NOT NULL DEFAULT 0,
  `job` varchar(50) DEFAULT 'police',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_penalc: ~0 rows (approximately)
DELETE FROM `origen_police_penalc`;

-- Dumping structure for table elapsed2_0.origen_police_reports
DROP TABLE IF EXISTS `origen_police_reports`;
CREATE TABLE IF NOT EXISTS `origen_police_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `description` text DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `cops` text DEFAULT '[]',
  `implicated` text DEFAULT '[]',
  `date` timestamp NULL DEFAULT current_timestamp(),
  `evidences` text DEFAULT '[]',
  `tags` text DEFAULT '["Caso Abierto"]',
  `location` varchar(255) NOT NULL DEFAULT 'Sin ubicacion asignada',
  `victims` mediumtext DEFAULT '[]',
  `vehicles` mediumtext DEFAULT '[]',
  `job` varchar(50) DEFAULT 'police',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.origen_police_reports: ~0 rows (approximately)
DELETE FROM `origen_police_reports`;

-- Dumping structure for table elapsed2_0.origen_police_shapes
DROP TABLE IF EXISTS `origen_police_shapes`;
CREATE TABLE IF NOT EXISTS `origen_police_shapes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `data` text NOT NULL,
  `radius` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.origen_police_shapes: ~0 rows (approximately)
DELETE FROM `origen_police_shapes`;

-- Dumping structure for table elapsed2_0.ox_doorlock
DROP TABLE IF EXISTS `ox_doorlock`;
CREATE TABLE IF NOT EXISTS `ox_doorlock` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.ox_doorlock: ~100 rows (approximately)
DELETE FROM `ox_doorlock`;
INSERT INTO `ox_doorlock` (`id`, `name`, `data`) VALUES
	(1, 'vangelico_jewellery', '{"maxDistance":2,"groups":{"police":0},"doors":[{"model":1425919976,"coords":{"x":-631.9553833007813,"y":-236.33326721191407,"z":38.2065315246582},"heading":306},{"model":9467943,"coords":{"x":-630.426513671875,"y":-238.4375457763672,"z":38.2065315246582},"heading":306}],"state":1,"coords":{"x":-631.19091796875,"y":-237.38540649414063,"z":38.2065315246582},"hideUi":true}'),
	(2, 'BigBankThermite1', '{"heading":160,"doors":false,"maxDistance":2,"hideUi":true,"groups":{"police":0},"coords":{"x":251.85757446289063,"y":221.0654754638672,"z":101.83240509033203},"model":-1508355822,"state":1,"autolock":1800}'),
	(3, 'BigBankThermite2', '{"coords":{"x":261.3004150390625,"y":214.50514221191407,"z":101.83240509033203},"autolock":1800,"maxDistance":2,"groups":{"police":0},"model":-1508355822,"doors":false,"hideUi":true,"heading":250,"state":1}'),
	(4, 'BigBankLPDoor', '{"coords":{"x":256.3115539550781,"y":220.65785217285157,"z":106.42955780029297},"autolock":1800,"maxDistance":2,"model":-222270721,"doors":false,"lockpick":true,"hideUi":true,"heading":340,"state":1,"lockpickDifficulty":["hard"]}'),
	(5, 'PaletoThermiteDoor', '{"coords":{"x":-106.47130584716797,"y":6476.15771484375,"z":31.95479965209961},"autolock":1800,"maxDistance":2,"groups":{"police":0},"model":1309269072,"doors":false,"hideUi":true,"heading":315,"state":1}'),
	(6, 'BigBankRedCardDoor', '{"coords":{"x":262.1980895996094,"y":222.518798828125,"z":106.42955780029297},"autolock":1800,"maxDistance":2,"groups":{"police":0},"model":746855201,"doors":false,"hideUi":true,"heading":250,"state":1}'),
	(8, 'lockup 1', '{"coords":{"x":473.68243408203127,"y":-985.7403564453125,"z":21.71523857116699},"groups":{"police":0},"heading":180,"state":1,"model":-562300705,"doors":false,"auto":true,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(9, 'lockup 2', '{"coords":{"x":473.68243408203127,"y":-981.6856079101563,"z":21.71523857116699},"groups":{"police":0},"heading":180,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(10, 'lockup 3', '{"coords":{"x":473.68243408203127,"y":-977.6312255859375,"z":21.71523857116699},"groups":{"police":0},"heading":180,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(11, 'lockup 4', '{"coords":{"x":473.68243408203127,"y":-973.576904296875,"z":21.71523857116699},"groups":{"police":0},"heading":180,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(12, 'lockup 5', '{"coords":{"x":469.530517578125,"y":-972.2761840820313,"z":21.70853996276855},"groups":{"police":0},"heading":0,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(13, 'lockup 6', '{"coords":{"x":469.5332946777344,"y":-976.3301391601563,"z":21.71711158752441},"groups":{"police":0},"heading":0,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(14, 'lockup 7', '{"coords":{"x":469.5332946777344,"y":-980.3849487304688,"z":21.71711158752441},"groups":{"police":0},"heading":0,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(15, 'lockup 8', '{"coords":{"x":469.5332946777344,"y":-984.4390869140625,"z":21.71711158752441},"groups":{"police":0},"heading":0,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(16, 'main lockup', '{"coords":{"x":470.9623107910156,"y":-987.5265502929688,"z":21.70896911621093},"groups":{"police":0},"heading":90,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(17, 'main lockup 2', '{"coords":{"x":465.72845458984377,"y":-988.8992309570313,"z":21.70981788635254},"groups":{"police":0},"heading":0,"state":1,"model":-562300705,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(18, 'muagshot', '{"coords":{"x":479.7725524902344,"y":-987.5143432617188,"z":21.71345138549804},"groups":{"police":0},"heading":270,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(19, 'fingerprint', '{"coords":{"x":481.10308837890627,"y":-990.203125,"z":21.7109088897705},"groups":{"police":0},"heading":180,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(21, 'vec3(457.499451, -987.424927, 21.712408)', '{"coords":{"x":457.49945068359377,"y":-987.4249267578125,"z":21.7124080657959},"groups":{"police":0},"state":1,"doors":[{"coords":{"x":456.19879150390627,"y":-987.4249267578125,"z":21.7124080657959},"heading":270,"model":687225737},{"coords":{"x":458.8000793457031,"y":-987.4249267578125,"z":21.7124080657959},"heading":90,"model":687225737}],"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(22, 'interrogation1', '{"coords":{"x":455.48883056640627,"y":-985.4404907226563,"z":21.71943283081054},"groups":{"police":0},"heading":180,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(23, 'interrogation1a', '{"coords":{"x":455.4916687011719,"y":-978.09814453125,"z":21.71943283081054},"groups":{"police":0},"heading":0,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(24, 'interrogation2', '{"coords":{"x":459.5076904296875,"y":-985.4470825195313,"z":21.71943283081054},"groups":{"police":0},"heading":180,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(25, 'interrogation2a', '{"coords":{"x":459.5075988769531,"y":-978.09814453125,"z":21.71943283081054},"groups":{"police":0},"heading":0,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(26, 'cell', '{"coords":{"x":449.7950744628906,"y":-989.5516357421875,"z":21.7096939086914},"groups":{"police":0},"state":1,"doors":[{"coords":{"x":449.7950744628906,"y":-988.2511596679688,"z":21.7096939086914},"heading":180,"model":687225737},{"coords":{"x":449.7950744628906,"y":-990.85205078125,"z":21.7096939086914},"heading":0,"model":687225737}],"unlockSound":"button_remote","maxDistance":2,"lockSound":"button_remote"}'),
	(28, 'staffonly', '{"coords":{"x":438.5079040527344,"y":-984.1529541015625,"z":21.71675491333007},"groups":{"police":0},"state":1,"doors":[{"coords":{"x":438.5079040527344,"y":-982.8523559570313,"z":21.71675491333007},"heading":180,"model":687225737},{"coords":{"x":438.5079040527344,"y":-985.45361328125,"z":21.71675491333007},"heading":0,"model":687225737}],"unlockSound":"button_remote","maxDistance":4,"doorRate":5,"lockSound":"button_remote"}'),
	(29, 'evidance room', '{"coords":{"x":435.33331298828127,"y":-982.0339965820313,"z":21.71096801757812},"groups":{"police":0},"heading":0,"state":1,"model":2147170473,"doors":false,"unlockSound":"button_remote","maxDistance":3,"doorRate":3,"lockSound":"button_remote"}'),
	(30, 'armoury', '{"coords":{"x":430.0379333496094,"y":-982.0364379882813,"z":21.71142387390136},"groups":{"police":0},"heading":0,"state":1,"model":2147170473,"doors":false,"unlockSound":"button_remote","maxDistance":3,"doorRate":3,"lockSound":"button_remote"}'),
	(31, 'clock room', '{"coords":{"x":423.5245056152344,"y":-983.518310546875,"z":21.70879745483398},"groups":{"police":0},"heading":0,"state":1,"model":-217337579,"doors":false,"unlockSound":"button_remote","maxDistance":3,"doorRate":3,"lockSound":"button_remote"}'),
	(32, 'steair', '{"coords":{"x":444.2144470214844,"y":-981.0554809570313,"z":21.70384979248047},"groups":{"police":0},"heading":90,"state":1,"model":687225737,"doors":false,"unlockSound":"button_remote","maxDistance":3,"doorRate":3,"lockSound":"button_remote"}'),
	(33, 'garage', '{"coords":{"x":447.83404541015627,"y":-992.4240112304688,"z":21.70754051208496},"groups":{"police":0},"heading":0,"state":1,"model":2147170473,"doors":false,"unlockSound":"button_remote","maxDistance":3,"doorRate":3,"lockSound":"button_remote"}'),
	(34, 'shuter', '{"autolock":10,"coords":{"x":461.28875732421877,"y":-998.1915283203125,"z":22.78145980834961},"groups":{"police":0},"heading":90,"state":1,"model":-1095264088,"doors":false,"unlockSound":"button_remote","maxDistance":10,"doorRate":2,"lockSound":"button_remote"}'),
	(35, 'shuter2', '{"autolock":10,"coords":{"x":461.2879638671875,"y":-1006.2894897460938,"z":22.78145980834961},"groups":{"police":0},"heading":90,"state":1,"model":-1095264088,"doors":false,"unlockSound":"button_remote","maxDistance":10,"doorRate":2,"lockSound":"button_remote"}'),
	(36, 'floor1 gate', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":541222087,"doorRate":6,"state":1,"coords":{"x":447.36669921875,"y":-980.6348876953125,"z":31.14924430847168},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(37, 'conference room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":90,"model":670041543,"doorRate":3,"state":1,"coords":{"x":442.3177490234375,"y":-982.778564453125,"z":31.14492797851562},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(38, 'conference room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":670041543,"doorRate":3,"state":1,"coords":{"x":436.0976867675781,"y":-978.4169311523438,"z":31.14685821533203},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(39, 'meeting room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":-884650166,"doorRate":3,"state":1,"coords":{"x":448.66961669921877,"y":-998.307373046875,"z":31.14835739135742},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(40, 'conference room3', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":444.7171936035156,"y":-998.3400268554688,"z":31.14699554443359},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":443.47747802734377,"y":-998.3400268554688,"z":31.14699554443359},"heading":180,"model":-1710985036},{"coords":{"x":445.9569091796875,"y":-998.3400268554688,"z":31.14699554443359},"heading":0,"model":-1710985036}]}'),
	(41, 'conference room4', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":3,"state":1,"coords":{"x":442.26123046875,"y":-1003.1151123046875,"z":31.14484024047851},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":442.26123046875,"y":-1001.8749389648438,"z":31.14484024047851},"heading":90,"model":-1710985036},{"coords":{"x":442.26123046875,"y":-1004.355224609375,"z":31.14484024047851},"heading":270,"model":-1710985036}]}'),
	(42, 'classroom', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":458.56103515625,"y":-998.3572387695313,"z":31.14579010009765},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":457.321044921875,"y":-998.3572387695313,"z":31.14579010009765},"heading":180,"model":-1710985036},{"coords":{"x":459.8009948730469,"y":-998.3572387695313,"z":31.14579010009765},"heading":0,"model":-1710985036}]}'),
	(43, 'floor2 main', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":541222087,"doorRate":5,"state":1,"coords":{"x":447.3690185546875,"y":-980.622314453125,"z":35.91880798339844},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(44, 'floor 2 main2', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":456.2967529296875,"y":-978.31640625,"z":35.9182243347168},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":456.2967529296875,"y":-979.556640625,"z":35.9182243347168},"heading":270,"model":-1710985036},{"coords":{"x":456.2967529296875,"y":-977.0762329101563,"z":35.9182243347168},"heading":90,"model":-1710985036}]}'),
	(45, 'breking room', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":461.87261962890627,"y":-976.4444580078125,"z":35.91540145874023},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":463.11279296875,"y":-976.4444580078125,"z":35.91540145874023},"heading":0,"model":-1710985036},{"coords":{"x":460.6324768066406,"y":-976.4444580078125,"z":35.91540145874023},"heading":180,"model":-1710985036}]}'),
	(48, 'swat room', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":469.81640625,"y":-998.2294921875,"z":35.91805267333984},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":471.0567626953125,"y":-998.2294921875,"z":35.91805267333984},"heading":0,"model":-1710985036},{"coords":{"x":468.5760803222656,"y":-998.2294921875,"z":35.91805267333984},"heading":180,"model":-1710985036}]}'),
	(49, 'swat1', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":270,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":481.5445556640625,"y":-986.5164794921875,"z":35.9206314086914},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(50, 'swat2', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":90,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":481.54144287109377,"y":-985.0382080078125,"z":35.91591644287109},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(51, 'dispach room', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":471.51263427734377,"y":-1001.9805908203125,"z":35.91805267333984},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":470.2723083496094,"y":-1001.9805908203125,"z":35.91805267333984},"heading":180,"model":-1710985036},{"coords":{"x":472.75299072265627,"y":-1001.9805908203125,"z":35.91805267333984},"heading":0,"model":-1710985036}]}'),
	(52, 'dispach room 2', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":471.5130615234375,"y":-1010.3869018554688,"z":35.91797637939453},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":470.27301025390627,"y":-1010.3869018554688,"z":35.91797637939453},"heading":180,"model":-1710985036},{"coords":{"x":472.7530822753906,"y":-1010.3869018554688,"z":35.91797637939453},"heading":0,"model":-1710985036}]}'),
	(53, 'breafing room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":90,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":473.4119873046875,"y":-1009.5767822265625,"z":35.91945266723633},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(54, 'meeting room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":463.20892333984377,"y":-1001.9990234375,"z":35.91835021972656},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(55, 'lab', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":180,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":456.12542724609377,"y":-1001.9979248046875,"z":35.91554260253906},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(56, 'command room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":180,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":442.0221862792969,"y":-1001.9996337890625,"z":35.91801452636719},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(57, 'detective room', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":438.71478271484377,"y":-1000.0863037109375,"z":35.91791915893555},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":438.71478271484377,"y":-998.845947265625,"z":35.91791915893555},"heading":90,"model":-1710985036},{"coords":{"x":438.71478271484377,"y":-1001.32666015625,"z":35.91791915893555},"heading":270,"model":-1710985036}]}'),
	(58, 'detective office room', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":2129513405,"doorRate":5,"state":1,"coords":{"x":433.1057434082031,"y":-1007.9656372070313,"z":36.05329513549805},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(59, 'dining room', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":438.718017578125,"y":-989.76953125,"z":35.91866302490234},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":438.718017578125,"y":-988.5297241210938,"z":35.91866302490234},"heading":90,"model":-1710985036},{"coords":{"x":438.718017578125,"y":-991.0093994140625,"z":35.91866302490234},"heading":270,"model":-1710985036}]}'),
	(60, 'captain office', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":438.7413024902344,"y":-982.9742431640625,"z":35.91949844360351},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":438.7413024902344,"y":-981.7339477539063,"z":35.91949844360351},"heading":90,"model":-1710985036},{"coords":{"x":438.7413024902344,"y":-984.2145385742188,"z":35.91949844360351},"heading":270,"model":-1710985036}]}'),
	(61, 'cheif1', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":90,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":435.1790771484375,"y":-981.9949951171875,"z":35.92065048217773},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(62, 'cheif meeting', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":0,"model":-884650166,"doorRate":5,"state":1,"coords":{"x":428.0302429199219,"y":-976.2927856445313,"z":35.91849517822265},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(63, 'sswat main', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":5,"state":1,"coords":{"x":468.17352294921877,"y":-1000.0914306640625,"z":35.91957855224609},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":468.17352294921877,"y":-1001.3314208984375,"z":35.91957855224609},"heading":270,"model":-1710985036},{"coords":{"x":468.17352294921877,"y":-998.8515014648438,"z":35.91957855224609},"heading":90,"model":-1710985036}]}'),
	(64, 'roof', '{"unlockSound":"button_remote","lockSound":"button_remote","heading":180,"model":-340230128,"doorRate":5,"state":1,"coords":{"x":451.1134948730469,"y":-981.132568359375,"z":45.1192741394043},"maxDistance":3,"groups":{"police":0},"doors":false}'),
	(65, 'main police door', '{"unlockSound":"button_remote","lockSound":"button_remote","doorRate":6,"state":1,"coords":{"x":442.36480712890627,"y":-989.7694702148438,"z":31.29042625427246},"maxDistance":3,"groups":{"police":0},"doors":[{"coords":{"x":442.36480712890627,"y":-990.8058471679688,"z":31.29042625427246},"heading":180,"model":-955193725},{"coords":{"x":442.36480712890627,"y":-988.7330932617188,"z":31.29042625427246},"heading":0,"model":-955193725}]}'),
	(68, 'ems1', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":377.3698425292969,"y":-1402.6949462890626,"z":32.64838027954101},"model":2115166766},{"heading":230,"coords":{"x":379.04083251953127,"y":-1400.7034912109376,"z":32.64838027954101},"model":2115166766}],"state":1,"coords":{"x":378.205322265625,"y":-1401.69921875,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(69, 'ems2', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":385.8922424316406,"y":-1404.4669189453126,"z":32.64838027954101},"model":1884112547},{"heading":230,"coords":{"x":387.563232421875,"y":-1402.4754638671876,"z":32.64838027954101},"model":1884112547}],"state":1,"coords":{"x":386.72772216796877,"y":-1403.47119140625,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":5}'),
	(70, 'vec3(374.711304, -1398.767334, 32.648380)', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":230,"coords":{"x":375.54681396484377,"y":-1397.7716064453126,"z":32.64838027954101},"model":2115166766},{"heading":50,"coords":{"x":373.8758239746094,"y":-1399.7630615234376,"z":32.64838027954101},"model":2115166766}],"state":1,"coords":{"x":374.7113037109375,"y":-1398.767333984375,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":5}'),
	(71, 'ems4', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":378.311279296875,"y":-1412.356689453125,"z":32.64838027954101},"model":1884112547},{"heading":230,"coords":{"x":379.9822692871094,"y":-1410.3653564453126,"z":32.64838027954101},"model":1884112547}],"state":1,"coords":{"x":379.14678955078127,"y":-1411.361083984375,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(72, 'ems5', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":382.39959716796877,"y":-1417.068603515625,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":140}'),
	(73, 'ems6', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":385.33428955078127,"y":-1413.5711669921876,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":140}'),
	(74, 'ems7', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":230,"coords":{"x":373.02667236328127,"y":-1411.5516357421876,"z":32.64838027954101},"model":1884112547},{"heading":50,"coords":{"x":371.3556823730469,"y":-1413.5430908203126,"z":32.64838027954101},"model":1884112547}],"state":1,"coords":{"x":372.191162109375,"y":-1412.54736328125,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(75, 'ems8', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":359.7016906738281,"y":-1395.02978515625,"z":32.64838027954101},"model":1884112547},{"heading":230,"coords":{"x":361.3727111816406,"y":-1393.038330078125,"z":32.64838027954101},"model":1884112547}],"state":1,"coords":{"x":360.5372009277344,"y":-1394.0340576171876,"z":32.64838027954101},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(76, 'ems9', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":361.1322021484375,"y":-1397.8912353515626,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":230}'),
	(77, 'ems10', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":366.3210754394531,"y":-1419.3497314453126,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(78, 'ems11', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":362.2115783691406,"y":-1415.9013671875,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(79, 'ems12', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":358.10064697265627,"y":-1412.451904296875,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(80, 'ems13', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":354.91729736328127,"y":-1409.78076171875,"z":32.65479278564453},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(81, 'ems14', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":351.0970764160156,"y":-1405.284423828125,"z":36.65951156616211},"model":1884112547},{"heading":230,"coords":{"x":352.76806640625,"y":-1403.293212890625,"z":36.65951156616211},"model":1884112547}],"state":1,"coords":{"x":351.93255615234377,"y":-1404.288818359375,"z":36.65951156616211},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(82, 'ems15', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":352.2806701660156,"y":-1401.39990234375,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":50}'),
	(83, 'ems16', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":140,"coords":{"x":359.7545471191406,"y":-1389.3533935546876,"z":36.65951156616211},"model":1884112547},{"heading":320,"coords":{"x":357.7631530761719,"y":-1387.682373046875,"z":36.65951156616211},"model":1884112547}],"state":1,"coords":{"x":358.75885009765627,"y":-1388.517822265625,"z":36.65951156616211},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":5}'),
	(84, 'ems17', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":364.2158508300781,"y":-1388.6405029296876,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":139}'),
	(85, 'ems18', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":359.706298828125,"y":-1395.0244140625,"z":36.65951156616211},"model":1884112547},{"heading":230,"coords":{"x":361.3773193359375,"y":-1393.032958984375,"z":36.65951156616211},"model":1884112547}],"state":1,"coords":{"x":360.54180908203127,"y":-1394.0286865234376,"z":36.65951156616211},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(86, 'ems20', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":357.1465148925781,"y":-1411.6514892578126,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(87, 'ems21', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":361.6905822753906,"y":-1415.46435546875,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(88, 'ems22', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":366.3019714355469,"y":-1419.3338623046876,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(89, 'ems23', '{"unlockSound":"button_remote","maxDistance":3,"doors":[{"heading":50,"coords":{"x":376.2379455566406,"y":-1414.82763671875,"z":36.65951156616211},"model":1884112547},{"heading":230,"coords":{"x":377.908935546875,"y":-1412.836181640625,"z":36.65951156616211},"model":1884112547}],"state":1,"coords":{"x":377.07342529296877,"y":-1413.8319091796876,"z":36.65951156616211},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3}'),
	(90, 'ems24', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":385.190673828125,"y":-1417.648193359375,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":140}'),
	(91, 'ems25', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":382.2559509277344,"y":-1421.1456298828126,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":140}'),
	(92, 'ems26', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":377.5380554199219,"y":-1405.32275390625,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(93, 'ems27', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":371.95452880859377,"y":-1400.63427734375,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":140}'),
	(94, 'ems28', '{"model":2115166766,"unlockSound":"button_remote","maxDistance":3,"doors":false,"state":1,"coords":{"x":365.0321044921875,"y":-1400.7862548828126,"z":36.66592025756836},"groups":{"ambulance":0,"police":0},"lockSound":"button_remote","doorRate":3,"heading":320}'),
	(95, 'mechanic', '{"model":-124639392,"unlockSound":"button_remote","maxDistance":5,"doors":false,"state":1,"coords":{"x":-355.9569396972656,"y":-133.3466033935547,"z":40.20771789550781},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":7,"heading":250}'),
	(96, 'mach2', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":2,"doors":false,"state":1,"coords":{"x":-354.22906494140627,"y":-128.6981964111328,"z":39.22188186645508},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":250}'),
	(97, 'mech3', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-346.3946533203125,"y":-142.71571350097657,"z":39.21831130981445},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":160}'),
	(98, 'mech4', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-346.4902038574219,"y":-146.0205078125,"z":39.21889114379883},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":250}'),
	(99, 'mech5', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-350.2377624511719,"y":-149.30177307128907,"z":39.22274017333984},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":340}'),
	(100, 'mech6', '{"model":-1033001619,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-337.47607421875,"y":-159.07696533203126,"z":39.2225112915039},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":89}'),
	(101, 'mech7', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-355.0136413574219,"y":-168.23672485351563,"z":39.21992111206055},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":17}'),
	(102, 'mech8', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-334.64007568359377,"y":-146.83619689941407,"z":39.21915054321289},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":70}'),
	(103, 'mech9', '{"model":-2051651622,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-334.6165466308594,"y":-146.84683227539063,"z":45.95814514160156},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":250}'),
	(104, 'mech10', '{"model":-2051651622,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-346.4585266113281,"y":-142.71119689941407,"z":45.95502853393555},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":160}'),
	(105, 'mech11', '{"model":-2051651622,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-345.1433410644531,"y":-149.97923278808595,"z":45.95829391479492},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":250}'),
	(106, 'mechanic2', '{"model":1589558367,"unlockSound":"button_remote","maxDistance":5,"doors":false,"state":1,"coords":{"x":-345.1508483886719,"y":-104.400390625,"z":46.65939712524414},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":7,"heading":250}'),
	(107, 'mech12', '{"model":-626684119,"unlockSound":"button_remote","maxDistance":4,"doors":false,"state":1,"coords":{"x":-343.4731750488281,"y":-99.68124389648438,"z":45.95930862426758},"groups":{"mechanic":0},"lockSound":"button_remote","doorRate":4,"heading":250}');

-- Dumping structure for table elapsed2_0.ox_inventory
DROP TABLE IF EXISTS `ox_inventory`;
CREATE TABLE IF NOT EXISTS `ox_inventory` (
  `owner` varchar(60) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `data` longtext DEFAULT NULL,
  `lastupdated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY `owner` (`owner`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.ox_inventory: ~0 rows (approximately)
DELETE FROM `ox_inventory`;

-- Dumping structure for table elapsed2_0.phone_invoices
DROP TABLE IF EXISTS `phone_invoices`;
CREATE TABLE IF NOT EXISTS `phone_invoices` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(11) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `society` tinytext DEFAULT NULL,
  `sender` varchar(50) DEFAULT NULL,
  `sendercitizenid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citizenid` (`citizenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.phone_invoices: ~0 rows (approximately)
DELETE FROM `phone_invoices`;

-- Dumping structure for table elapsed2_0.players
DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned DEFAULT NULL,
  `citizenid` varchar(50) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  `license` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `money` text NOT NULL,
  `charinfo` text DEFAULT NULL,
  `job` text NOT NULL,
  `gang` text DEFAULT NULL,
  `position` text NOT NULL,
  `metadata` text NOT NULL,
  `inventory` longtext DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_logged_out` timestamp NULL DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `dangerous` tinyint(4) DEFAULT 0,
  `wanted` tinyint(4) unsigned DEFAULT 0,
  `skills` longtext DEFAULT NULL,
  `skillpoint` varchar(50) DEFAULT '0',
  PRIMARY KEY (`citizenid`),
  KEY `id` (`id`),
  KEY `last_updated` (`last_updated`),
  KEY `license` (`license`)
) ENGINE=InnoDB AUTO_INCREMENT=6713 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.players: ~2 rows (approximately)
DELETE FROM `players`;
INSERT INTO `players` (`id`, `userId`, `citizenid`, `cid`, `license`, `name`, `money`, `charinfo`, `job`, `gang`, `position`, `metadata`, `inventory`, `phone_number`, `last_updated`, `last_logged_out`, `image`, `dangerous`, `wanted`, `skills`, `skillpoint`) VALUES
	(6697, 26, 'HH19AN14', 1, 'license2:054ebaa3303875cd871c1a93577323ecafec719c', 'Fault', '{"bank":5020,"cash":22450,"crypto":0}', '{"gender":0,"account":"US08QBX5303536689","birthdate":"04/11/2000","backstory":"placeholder backstory","phone":"1908977146","iban":493710,"firstname":"VAYU","lastname":"G","nationality":"India","cid":1,"height":170}', '{"grade":{"name":"Freelancer","level":0},"onduty":true,"name":"unemployed","payment":10,"isboss":false,"label":"Civilian"}', '{"grade":{"name":"Unaffiliated","level":0},"isboss":false,"name":"none","label":"No Gang"}', '{"x":391.8197937011719,"y":-1630.5626220703126,"z":29.2799072265625,"w":325.9842529296875}', '{"armor":0,"tracker":false,"carheistxp":0,"licences":{"id":true,"driver":false,"weapon":false},"status":[],"thirst":88.60000000000001,"dealerrep":0,"phonedata":{"SerialNumber":69735304,"InstalledApps":[]},"craftingrep":0,"inlaststand":false,"jobrep":{"trucker":0,"hotdog":0,"taxi":0,"tow":0},"hunger":87.39999999999999,"attachmentcraftingrep":0,"criminalrecord":{"hasRecord":false},"walletid":"QB-81930912","jailitems":[],"callsign":"NO CALLSIGN","phone":[],"ishandcuffed":false,"inside":{"apartment":[]},"health":200,"stress":0,"isdead":false,"injail":0,"fingerprint":"3C5LQEOME87V9EK","bloodtype":"B-"}', '[{"count":22450,"slot":1,"name":"money"},{"count":3,"slot":2,"name":"water"}]', NULL, '2025-01-05 04:54:34', '2025-01-05 04:54:34', NULL, 0, 0, '{"Strength":{"Stat":"MP0_STRENGTH","RemoveAmount":-0.3,"sellAmount":5,"requiredPoints":15000,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":10,"skillName":"Strength"},"Driving":{"Stat":"MP0_DRIVING_ABILITY","RemoveAmount":-0.5,"sellAmount":5,"requiredPoints":15000,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":16.39999999999997,"skillName":"Driving"},"Stamina":{"Stat":"MP0_STAMINA","RemoveAmount":-0.3,"sellAmount":5,"requiredPoints":15000,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":20,"skillName":"Stamina"},"Shooting":{"Stat":"MP0_SHOOTING_ABILITY","RemoveAmount":-0.1,"sellAmount":5,"requiredPoints":15000,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":0,"skillName":"Shooting"},"Lung Capacity":{"Stat":"MP0_LUNG_CAPACITY","RemoveAmount":-0.1,"sellAmount":5,"requiredPoints":15000,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":0,"skillName":"Lung Capacity"}}', '0'),
	(6708, 27, 'WA6HE99T', 1, 'license2:18295990e72df0f763cc82e393947e41923ffb45', 'FullStack_Dev', '{"cash":500,"bank":5000,"crypto":0}', '{"birthdate":"03/08/1998","nationality":"Albania","iban":518619,"lastname":"as","phone":"3731480950","gender":0,"firstname":"ghfg","cid":1,"backstory":"placeholder backstory","account":"US06QBX8184904971","height":200}', '{"label":"Civilian","isboss":false,"payment":10,"grade":{"name":"Freelancer","level":0},"name":"unemployed","onduty":true}', '{"grade":{"name":"Unaffiliated","level":0},"label":"No Gang","name":"none","isboss":false}', '{"x":-1023.2571411132813,"y":-2719.054931640625,"z":13.811767578125,"w":82.20472717285156}', '{"fingerprint":"S9061P9AD6J91TN","criminalrecord":{"hasRecord":false},"tracker":false,"phone":[],"dealerrep":0,"health":200,"armor":0,"status":[],"isdead":false,"phonedata":{"SerialNumber":93427022,"InstalledApps":[]},"inside":{"apartment":[]},"carheistxp":0,"jailitems":[],"inlaststand":false,"craftingrep":0,"bloodtype":"O+","attachmentcraftingrep":0,"ishandcuffed":false,"callsign":"NO CALLSIGN","jobrep":{"hotdog":0,"tow":0,"taxi":0,"trucker":0},"injail":0,"walletid":"QB-66213399","stress":0,"thirst":100,"hunger":100,"licences":{"weapon":false,"id":true,"driver":false}}', '[{"name":"money","slot":1,"count":500},{"name":"water","slot":2,"count":3}]', NULL, '2025-01-05 06:45:48', '2025-01-05 06:45:48', NULL, 0, 0, '{"Strength":{"RemoveAmount":-0.3,"sellAmount":5,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":11.0,"requiredPoints":15000,"skillName":"Strength","Stat":"MP0_STRENGTH"},"Stamina":{"RemoveAmount":-0.3,"sellAmount":5,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":20,"requiredPoints":15000,"skillName":"Stamina","Stat":"MP0_STAMINA"},"Driving":{"RemoveAmount":-0.5,"sellAmount":5,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":0,"requiredPoints":15000,"skillName":"Driving","Stat":"MP0_DRIVING_ABILITY"},"Lung Capacity":{"RemoveAmount":-0.1,"sellAmount":5,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":0,"requiredPoints":15000,"skillName":"Lung Capacity","Stat":"MP0_LUNG_CAPACITY"},"Shooting":{"RemoveAmount":-0.1,"sellAmount":5,"skillDescription":"You run out of breath more slowly and can cover more distance","Current":0,"requiredPoints":15000,"skillName":"Shooting","Stat":"MP0_SHOOTING_ABILITY"}}', '0');

-- Dumping structure for table elapsed2_0.playerskins
DROP TABLE IF EXISTS `playerskins`;
CREATE TABLE IF NOT EXISTS `playerskins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `skin` text NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `citizenid` (`citizenid`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.playerskins: ~0 rows (approximately)
DELETE FROM `playerskins`;

-- Dumping structure for table elapsed2_0.player_groups
DROP TABLE IF EXISTS `player_groups`;
CREATE TABLE IF NOT EXISTS `player_groups` (
  `citizenid` varchar(50) NOT NULL,
  `group` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `grade` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`citizenid`,`type`,`group`),
  CONSTRAINT `fk_citizenid` FOREIGN KEY (`citizenid`) REFERENCES `players` (`citizenid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_groups: ~0 rows (approximately)
DELETE FROM `player_groups`;

-- Dumping structure for table elapsed2_0.player_jobs_activity
DROP TABLE IF EXISTS `player_jobs_activity`;
CREATE TABLE IF NOT EXISTS `player_jobs_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job` varchar(255) NOT NULL,
  `last_checkin` int(11) NOT NULL,
  `last_checkout` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE,
  KEY `last_checkout` (`last_checkout`) USING BTREE,
  KEY `citizenid_job` (`citizenid`,`job`) USING BTREE,
  CONSTRAINT `player_jobs_activity_ibfk_1` FOREIGN KEY (`citizenid`) REFERENCES `players` (`citizenid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.player_jobs_activity: ~0 rows (approximately)
DELETE FROM `player_jobs_activity`;

-- Dumping structure for table elapsed2_0.player_mails
DROP TABLE IF EXISTS `player_mails`;
CREATE TABLE IF NOT EXISTS `player_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) DEFAULT NULL,
  `sender` varchar(50) DEFAULT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `read` tinyint(4) DEFAULT 0,
  `mailid` int(11) DEFAULT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `button` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citizenid` (`citizenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_mails: ~0 rows (approximately)
DELETE FROM `player_mails`;

-- Dumping structure for table elapsed2_0.player_outfits
DROP TABLE IF EXISTS `player_outfits`;
CREATE TABLE IF NOT EXISTS `player_outfits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `citizenid` varchar(50) DEFAULT NULL,
  `outfitname` varchar(50) NOT NULL DEFAULT '0',
  `model` varchar(50) DEFAULT NULL,
  `props` text DEFAULT NULL,
  `components` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizenid_outfitname_model` (`citizenid`,`outfitname`,`model`),
  KEY `citizenid` (`citizenid`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_outfits: ~0 rows (approximately)
DELETE FROM `player_outfits`;

-- Dumping structure for table elapsed2_0.player_outfit_codes
DROP TABLE IF EXISTS `player_outfit_codes`;
CREATE TABLE IF NOT EXISTS `player_outfit_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outfitid` int(11) NOT NULL,
  `code` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_player_outfit_codes_player_outfits` (`outfitid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_outfit_codes: ~0 rows (approximately)
DELETE FROM `player_outfit_codes`;

-- Dumping structure for table elapsed2_0.player_transactions
DROP TABLE IF EXISTS `player_transactions`;
CREATE TABLE IF NOT EXISTS `player_transactions` (
  `id` varchar(50) NOT NULL,
  `isFrozen` int(11) DEFAULT 0,
  `transactions` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_transactions: ~0 rows (approximately)
DELETE FROM `player_transactions`;

-- Dumping structure for table elapsed2_0.player_vehicles
DROP TABLE IF EXISTS `player_vehicles`;
CREATE TABLE IF NOT EXISTS `player_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license` varchar(50) DEFAULT NULL,
  `citizenid` varchar(50) DEFAULT NULL,
  `vehicle` varchar(50) DEFAULT NULL,
  `hash` varchar(50) DEFAULT NULL,
  `mods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `plate` varchar(15) NOT NULL,
  `fakeplate` varchar(50) DEFAULT NULL,
  `garage` varchar(50) DEFAULT NULL,
  `fuel` int(11) DEFAULT 100,
  `engine` float DEFAULT 1000,
  `body` float DEFAULT 1000,
  `state` int(11) DEFAULT 1,
  `depotprice` int(11) NOT NULL DEFAULT 0,
  `drivingdistance` int(50) DEFAULT NULL,
  `status` text DEFAULT NULL,
  `glovebox` longtext DEFAULT NULL,
  `trunk` longtext DEFAULT NULL,
  `carseller` int(11) DEFAULT 0,
  `damage` text DEFAULT NULL,
  `nosColour` text DEFAULT NULL,
  `traveldistance` int(50) DEFAULT 0,
  `noslevel` int(10) DEFAULT 0,
  `hasnitro` tinyint(4) DEFAULT 0,
  `wanted` int(1) DEFAULT 0,
  `billPrice` int(6) DEFAULT 0,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`plate`),
  KEY `citizenid` (`citizenid`),
  KEY `license` (`license`),
  CONSTRAINT `player_vehicles_ibfk_1` FOREIGN KEY (`citizenid`) REFERENCES `players` (`citizenid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `player_vehicles_ibfk_2` FOREIGN KEY (`license`) REFERENCES `players` (`license`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.player_vehicles: ~0 rows (approximately)
DELETE FROM `player_vehicles`;

-- Dumping structure for table elapsed2_0.properties
DROP TABLE IF EXISTS `properties`;
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) NOT NULL,
  `coords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`coords`)),
  `price` int(11) NOT NULL DEFAULT 0,
  `owner` varchar(50) DEFAULT NULL,
  `interior` varchar(255) NOT NULL,
  `keyholders` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object() CHECK (json_valid(`keyholders`)),
  `rent_interval` int(11) DEFAULT NULL,
  `interact_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object() CHECK (json_valid(`interact_options`)),
  `stash_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT json_object() CHECK (json_valid(`stash_options`)),
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `players` (`citizenid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.properties: ~0 rows (approximately)
DELETE FROM `properties`;

-- Dumping structure for table elapsed2_0.properties_decorations
DROP TABLE IF EXISTS `properties_decorations`;
CREATE TABLE IF NOT EXISTS `properties_decorations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `coords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`coords`)),
  `rotation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rotation`)),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `properties_decorations_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.properties_decorations: ~0 rows (approximately)
DELETE FROM `properties_decorations`;

-- Dumping structure for table elapsed2_0.spy_bodycam
DROP TABLE IF EXISTS `spy_bodycam`;
CREATE TABLE IF NOT EXISTS `spy_bodycam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` varchar(255) NOT NULL,
  `videolink` longtext NOT NULL,
  `street` varchar(255) NOT NULL,
  `date` varchar(255) NOT NULL,
  `playername` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.spy_bodycam: ~0 rows (approximately)
DELETE FROM `spy_bodycam`;

-- Dumping structure for table elapsed2_0.stevo_vineyard_processing
DROP TABLE IF EXISTS `stevo_vineyard_processing`;
CREATE TABLE IF NOT EXISTS `stevo_vineyard_processing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(50) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.stevo_vineyard_processing: ~0 rows (approximately)
DELETE FROM `stevo_vineyard_processing`;

-- Dumping structure for table elapsed2_0.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `license` varchar(50) DEFAULT NULL,
  `license2` varchar(50) DEFAULT NULL,
  `fivem` varchar(20) DEFAULT NULL,
  `discord` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.users: ~2 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`userId`, `username`, `license`, `license2`, `fivem`, `discord`) VALUES
	(26, 'Fault', 'license:3e1e02645b52298bc4353fef1ba87cc233122a39', 'license2:054ebaa3303875cd871c1a93577323ecafec719c', 'fivem:9787068', NULL),
	(27, 'FullStack_Dev', 'license:18295990e72df0f763cc82e393947e41923ffb45', 'license2:18295990e72df0f763cc82e393947e41923ffb45', NULL, 'discord:451231838229495828'),
	(28, 'Drona Flicks', 'license:2721696a3bc4494901e661570076b4b4392280f2', 'license2:9118e7ffc41c61e6836289a8e37f9c2f28f9f4de', 'fivem:4299186', 'discord:319370915412705290');

-- Dumping structure for table elapsed2_0.vehicle_financing
DROP TABLE IF EXISTS `vehicle_financing`;
CREATE TABLE IF NOT EXISTS `vehicle_financing` (
  `vehicleId` int(11) NOT NULL,
  `balance` int(11) DEFAULT NULL,
  `paymentamount` int(11) DEFAULT NULL,
  `paymentsleft` int(11) DEFAULT NULL,
  `financetime` int(11) DEFAULT NULL,
  PRIMARY KEY (`vehicleId`),
  CONSTRAINT `vehicleId` FOREIGN KEY (`vehicleId`) REFERENCES `player_vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table elapsed2_0.vehicle_financing: ~0 rows (approximately)
DELETE FROM `vehicle_financing`;

-- Dumping structure for table elapsed2_0.weed_plants
DROP TABLE IF EXISTS `weed_plants`;
CREATE TABLE IF NOT EXISTS `weed_plants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property` varchar(30) DEFAULT NULL,
  `stage` tinyint(4) NOT NULL DEFAULT 1,
  `sort` varchar(30) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `food` tinyint(4) NOT NULL DEFAULT 100,
  `health` tinyint(4) NOT NULL DEFAULT 100,
  `stageProgress` tinyint(4) NOT NULL DEFAULT 0,
  `coords` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table elapsed2_0.weed_plants: ~0 rows (approximately)
DELETE FROM `weed_plants`;

-- Dumping structure for table elapsed2_0.xt_prison
DROP TABLE IF EXISTS `xt_prison`;
CREATE TABLE IF NOT EXISTS `xt_prison` (
  `identifier` varchar(100) NOT NULL,
  `jailtime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`identifier`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.xt_prison: ~1 rows (approximately)
DELETE FROM `xt_prison`;
INSERT INTO `xt_prison` (`identifier`, `jailtime`) VALUES
	('HH19AN14', 0),
	('WA6HE99T', 0);

-- Dumping structure for table elapsed2_0.xt_prison_items
DROP TABLE IF EXISTS `xt_prison_items`;
CREATE TABLE IF NOT EXISTS `xt_prison_items` (
  `owner` varchar(60) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  UNIQUE KEY `owner` (`owner`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.xt_prison_items: ~0 rows (approximately)
DELETE FROM `xt_prison_items`;

-- Dumping structure for table elapsed2_0.zsx_multicharacter_slots
DROP TABLE IF EXISTS `zsx_multicharacter_slots`;
CREATE TABLE IF NOT EXISTS `zsx_multicharacter_slots` (
  `identifier` varchar(255) NOT NULL,
  `amount` int(1) NOT NULL,
  PRIMARY KEY (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table elapsed2_0.zsx_multicharacter_slots: ~2 rows (approximately)
DELETE FROM `zsx_multicharacter_slots`;
INSERT INTO `zsx_multicharacter_slots` (`identifier`, `amount`) VALUES
	('license2:054ebaa3303875cd871c1a93577323ecafec719c', 1),
	('license2:18295990e72df0f763cc82e393947e41923ffb45', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
