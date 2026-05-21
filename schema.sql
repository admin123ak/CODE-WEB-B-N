-- ============================================
-- HCLOU SERVER - Clean Database Schema
-- Import 1 len vao database de tao toan bo bang
-- ============================================

-- 1. Bảng admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `telegram_id` BIGINT NOT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('superadmin','admin') DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `telegram_id` (`telegram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Bảng users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `telegram_id` BIGINT NOT NULL,
  `telegram_username` VARCHAR(100) DEFAULT NULL,
  `full_name` VARCHAR(200) DEFAULT NULL,
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `balance` DECIMAL(12,0) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `telegram_id` (`telegram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Bảng games
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `package_name` VARCHAR(200) NOT NULL,
  `icon_url` VARCHAR(500) DEFAULT NULL,
  `type` ENUM('VIP','NORMAL') DEFAULT 'NORMAL',
  `root_type` VARCHAR(50) DEFAULT 'Only Root',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Bảng packages (goi key theo game)
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `days` INT NOT NULL,
  `price` DECIMAL(12,0) NOT NULL,
  `key_type` ENUM('Normal','VIP') DEFAULT 'Normal',
  `is_active` TINYINT(1) DEFAULT 1,
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Bảng orders (don hang)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL,
  `user_id` INT NOT NULL,
  `game_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `amount` DECIMAL(12,0) NOT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `payment_proof` TEXT DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `approved_by` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Bảng keys (key ban quyen) - CO status 'available'
CREATE TABLE IF NOT EXISTS `keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_code` VARCHAR(100) NOT NULL,
  `user_id` INT DEFAULT NULL,
  `game_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `order_id` INT DEFAULT NULL,
  `status` ENUM('available','pending','active','expired','locked') DEFAULT 'available',
  `days` INT NOT NULL,
  `reset_count` INT DEFAULT 0,
  `max_reset` INT DEFAULT 3,
  `start_at` TIMESTAMP NULL DEFAULT NULL,
  `expire_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `key_code` (`key_code`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `package_id` (`package_id`),
  KEY `idx_key_pool` (`status`,`game_id`,`package_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Bảng free_keys (key mien phi qua link)
CREATE TABLE IF NOT EXISTS `free_keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_code` VARCHAR(50) NOT NULL,
  `game_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `days` INT NOT NULL,
  `key_type` VARCHAR(20) DEFAULT 'VIP',
  `is_active` TINYINT(1) DEFAULT 0,
  `start_at` TIMESTAMP NULL DEFAULT NULL,
  `expire_at` TIMESTAMP NULL DEFAULT NULL,
  `claim_token` VARCHAR(100) DEFAULT NULL,
  `short_url` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `key_code` (`key_code`),
  UNIQUE KEY `claim_token` (`claim_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Bảng free_key_claims (lich su claim key free)
CREATE TABLE IF NOT EXISTS `free_key_claims` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `free_key_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `key_id` INT NOT NULL,
  `claimed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Bảng bank_transactions (giao dich ngan hang)
CREATE TABLE IF NOT EXISTS `bank_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tx_hash` CHAR(64) NOT NULL,
  `tx_date` VARCHAR(32) NOT NULL,
  `amount` DECIMAL(12,0) NOT NULL,
  `description` TEXT NOT NULL,
  `order_code` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('seen','matched','approved','ignored','error') DEFAULT 'seen',
  `note` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `tx_hash` (`tx_hash`),
  KEY `idx_order_code` (`order_code`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng admin_config_logs (nhat ky thay doi config)
CREATE TABLE IF NOT EXISTS `admin_config_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin` VARCHAR(100) NOT NULL,
  `config_key` VARCHAR(100) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_config_logs_created` (`created_at`),
  KEY `idx_config_logs_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
