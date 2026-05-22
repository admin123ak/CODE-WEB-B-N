-- =============================================
-- DATABASE SCHEMA - HCLOU SERVER
-- =============================================
-- File này dùng cho installer (install.php) khi setup hosting mới.
-- Chỉ chứa schema + seed data tối thiểu (KHÔNG có data thật của khách).
--
-- Cải tiến so với dump gốc:
--   - bank_transactions.tx_date: VARCHAR -> DATETIME
--   - Thêm FK: keys.order_id, free_key_claims.*
--   - Thêm index cover: keys (status, game_id, package_id, id)
--   - Thêm index orders (user_id, created_at DESC)
--   - Đồng nhất collation utf8mb4_unicode_ci
-- =============================================

SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- =============================================
-- TABLE: admins
-- =============================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` BIGINT(20) NOT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('superadmin','admin') DEFAULT 'admin',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admins_telegram` (`telegram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: admin_config_logs
-- =============================================
DROP TABLE IF EXISTS `admin_config_logs`;
CREATE TABLE `admin_config_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin` VARCHAR(100) NOT NULL,
  `config_key` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_config_logs_created` (`created_at`),
  KEY `idx_config_logs_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: users
-- =============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` BIGINT(20) NOT NULL,
  `telegram_username` VARCHAR(100) DEFAULT NULL,
  `full_name` VARCHAR(200) DEFAULT NULL,
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `balance` DECIMAL(12,0) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_telegram` (`telegram_id`),
  KEY `idx_users_username` (`telegram_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: games
-- =============================================
DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `package_name` VARCHAR(200) NOT NULL,
  `icon_url` VARCHAR(500) DEFAULT NULL,
  `type` ENUM('VIP','NORMAL') DEFAULT 'NORMAL',
  `root_type` VARCHAR(50) DEFAULT 'Only Root',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_games_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: packages
-- =============================================
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `days` INT(11) NOT NULL,
  `price` DECIMAL(12,0) NOT NULL,
  `key_type` ENUM('Normal','VIP') DEFAULT 'Normal',
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_packages_game` (`game_id`),
  KEY `idx_packages_active` (`game_id`, `is_active`),
  CONSTRAINT `fk_packages_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: orders
-- =============================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_code` VARCHAR(50) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `game_id` INT(11) NOT NULL,
  `package_id` INT(11) NOT NULL,
  `amount` DECIMAL(12,0) NOT NULL,
  `payment_method` ENUM('mbbank','binance') NOT NULL DEFAULT 'mbbank',
  `crypto_amount` DECIMAL(18,6) DEFAULT NULL,
  `usdt_vnd_rate` DECIMAL(12,2) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `payment_proof` TEXT DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `approved_by` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_orders_code` (`order_code`),
  KEY `idx_orders_user_created` (`user_id`, `created_at` DESC),
  KEY `idx_orders_status_created` (`status`, `created_at`),
  KEY `idx_orders_game` (`game_id`),
  KEY `idx_orders_package` (`package_id`),
  KEY `idx_orders_crypto_amount` (`crypto_amount`),
  KEY `idx_orders_payment_method` (`payment_method`, `status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_orders_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `fk_orders_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: keys
-- =============================================
DROP TABLE IF EXISTS `keys`;
CREATE TABLE `keys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key_code` VARCHAR(100) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `game_id` INT(11) NOT NULL,
  `package_id` INT(11) NOT NULL,
  `order_id` INT(11) DEFAULT NULL,
  `status` ENUM('available','pending','active','expired','locked') DEFAULT 'available',
  `days` INT(11) NOT NULL,
  `reset_count` INT(11) DEFAULT 0,
  `max_reset` INT(11) DEFAULT 3,
  `start_at` TIMESTAMP NULL DEFAULT NULL,
  `expire_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- UNIQUE (key_code, user_id) cho phép multi-claim free key:
  -- nhiều user có thể có cùng key_code (mỗi user 1 row).
  UNIQUE KEY `uniq_keys_code_user` (`key_code`, `user_id`),
  KEY `idx_keys_pool` (`status`, `game_id`, `package_id`, `id`),
  KEY `idx_keys_user` (`user_id`),
  KEY `idx_keys_order` (`order_id`),
  KEY `idx_keys_expire` (`expire_at`),
  CONSTRAINT `fk_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_keys_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `fk_keys_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  CONSTRAINT `fk_keys_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: bank_transactions
-- (sửa tx_date từ VARCHAR sang DATETIME)
-- =============================================
DROP TABLE IF EXISTS `bank_transactions`;
CREATE TABLE `bank_transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tx_hash` CHAR(64) NOT NULL,
  `tx_date` DATETIME NOT NULL,
  `amount` DECIMAL(18,6) NOT NULL,
  `source` ENUM('mbbank','binance') NOT NULL DEFAULT 'mbbank',
  `description` TEXT NOT NULL,
  `order_code` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('seen','matched','approved','ignored','error') DEFAULT 'seen',
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tx_hash` (`tx_hash`),
  KEY `idx_tx_order_code` (`order_code`),
  KEY `idx_tx_status` (`status`),
  KEY `idx_tx_created` (`created_at`),
  KEY `idx_tx_date` (`tx_date`),
  KEY `idx_tx_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: free_keys
-- =============================================
DROP TABLE IF EXISTS `free_keys`;
CREATE TABLE `free_keys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key_code` VARCHAR(100) NOT NULL,
  `game_id` INT(11) NOT NULL,
  `package_id` INT(11) NOT NULL,
  `days` INT(11) NOT NULL,
  `key_type` ENUM('Normal','VIP') DEFAULT 'VIP',
  `is_active` TINYINT(1) DEFAULT 1,
  `start_at` DATETIME NOT NULL,
  `expire_at` DATETIME NOT NULL,
  `claim_token` VARCHAR(80) NOT NULL,
  `short_url` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_freekey_token` (`claim_token`),
  KEY `idx_freekey_game` (`game_id`),
  KEY `idx_freekey_package` (`package_id`),
  KEY `idx_freekey_active` (`is_active`, `expire_at`),
  CONSTRAINT `fk_freekey_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `fk_freekey_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: free_key_claims
-- =============================================
DROP TABLE IF EXISTS `free_key_claims`;
CREATE TABLE `free_key_claims` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `free_key_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `key_id` INT(11) DEFAULT NULL,
  `claimed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_free_user` (`free_key_id`, `user_id`),
  KEY `idx_claim_user` (`user_id`),
  KEY `idx_claim_key` (`key_id`),
  CONSTRAINT `fk_claim_freekey` FOREIGN KEY (`free_key_id`) REFERENCES `free_keys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_key` FOREIGN KEY (`key_id`) REFERENCES `keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SEED DATA: GAMES + PACKAGES MẶC ĐỊNH
-- (Có thể sửa qua admin panel sau khi cài)
-- =============================================
INSERT INTO `games` (`id`, `name`, `package_name`, `icon_url`, `type`, `root_type`, `is_active`, `sort_order`) VALUES
(1, 'Game Demo 1', 'com.example.game1', NULL, 'VIP', 'Root & NoRoot', 1, 1),
(2, 'Game Demo 2', 'com.example.game2', NULL, 'VIP', 'Only Root', 1, 2);

INSERT INTO `packages` (`game_id`, `name`, `days`, `price`, `key_type`, `is_active`) VALUES
(1, 'Gói 1 ngày', 1, 10000, 'VIP', 1),
(1, 'Gói 7 ngày', 7, 60000, 'VIP', 1),
(1, 'Gói 30 ngày', 30, 200000, 'VIP', 1),
(2, 'Gói 1 ngày', 1, 15000, 'VIP', 1),
(2, 'Gói 7 ngày', 7, 90000, 'VIP', 1),
(2, 'Gói 30 ngày', 30, 250000, 'VIP', 1);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
