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
  `role` ENUM('customer','reseller','admin') DEFAULT 'customer',
  `discount` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Phan tram giam gia cho reseller (0-100)',
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
  `download_url` VARCHAR(500) DEFAULT NULL,
  `play_url` VARCHAR(500) DEFAULT NULL,
  `type` ENUM('VIP','NORMAL') DEFAULT 'NORMAL',
  `category` ENUM('key','account') DEFAULT 'key',
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
  `package_id` INT(11) DEFAULT NULL,
  `account_type_id` INT(11) DEFAULT NULL,
  `order_type` ENUM('key','account') DEFAULT 'key',
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
  KEY `idx_orders_acc_type` (`account_type_id`),
  KEY `idx_orders_crypto_amount` (`crypto_amount`),
  KEY `idx_orders_payment_method` (`payment_method`, `status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_orders_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`)
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
-- TABLE: topup_requests
-- Yêu cầu nạp tiền vào ví user, qua 3 method: bank/binance/card.
-- - Bank: dùng unique_code (vd NAP123ABC) trong description để match cron mbbank.
-- - Binance: dùng crypto_amount (USDT unique decimal) để match cron crypto.
-- - Card: gửi telco/face_value/serial/code lên doithe.vn API, callback async.
-- Khi status -> approved, balance_helpers.balanceCredit() được gọi với
-- amount_credited (đã trừ markup card nếu cần).
-- =============================================
DROP TABLE IF EXISTS `topup_requests`;
CREATE TABLE `topup_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `method` ENUM('mbbank','binance','card') NOT NULL,
  `amount_requested` DECIMAL(15,2) NOT NULL COMMENT 'VND user dự định nạp (face value với card)',
  `amount_credited` DECIMAL(15,2) DEFAULT NULL COMMENT 'VND thực tế cộng vào balance (sau markup)',
  `status` ENUM('pending','approved','rejected','expired') DEFAULT 'pending',
  `unique_code` VARCHAR(50) DEFAULT NULL COMMENT 'NAPxxxxxxx cho bank',
  `crypto_amount` DECIMAL(18,6) DEFAULT NULL COMMENT 'USDT unique cho binance',
  `usdt_vnd_rate` DECIMAL(12,2) DEFAULT NULL,
  `card_telco` ENUM('VIETTEL','MOBIFONE','VINAPHONE') DEFAULT NULL,
  `card_face_value` INT(11) DEFAULT NULL,
  `card_serial` VARCHAR(50) DEFAULT NULL,
  `card_code` VARCHAR(50) DEFAULT NULL,
  `provider_request_id` VARCHAR(50) DEFAULT NULL COMMENT 'request_id gửi cho doithe.vn',
  `provider_response` TEXT DEFAULT NULL COMMENT 'JSON response/callback từ provider',
  `provider_trans_id` VARCHAR(100) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_topup_unique_code` (`unique_code`),
  UNIQUE KEY `uniq_topup_provider_req` (`provider_request_id`),
  KEY `idx_topup_user_created` (`user_id`, `created_at` DESC),
  KEY `idx_topup_status_method` (`status`, `method`),
  KEY `idx_topup_crypto_amount` (`crypto_amount`),
  CONSTRAINT `fk_topup_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: balance_logs
-- Sổ cái mọi thay đổi balance (cộng/trừ). Append-only — không sửa, không xóa.
-- Dùng cho audit, lịch sử user, debug. balance_after = số dư sau khi áp dụng amount.
-- =============================================
DROP TABLE IF EXISTS `balance_logs`;
CREATE TABLE `balance_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL COMMENT '+credit, -debit',
  `balance_after` DECIMAL(15,2) NOT NULL,
  `reason` ENUM('topup','purchase','refund','admin_adjust') NOT NULL,
  `ref_type` VARCHAR(50) DEFAULT NULL COMMENT 'topup_request | order | manual',
  `ref_id` INT(11) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_balog_user_created` (`user_id`, `created_at` DESC),
  KEY `idx_balog_reason` (`reason`, `created_at`),
  KEY `idx_balog_ref` (`ref_type`, `ref_id`),
  CONSTRAINT `fk_balog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: account_types
-- Loại acc: Google, Facebook, Apple, v.v.
-- =============================================
DROP TABLE IF EXISTS `account_types`;
CREATE TABLE `account_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(12,0) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_at_game` (`game_id`, `is_active`),
  KEY `idx_at_active` (`game_id`, `sort_order`),
  CONSTRAINT `fk_at_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: accounts
-- Lưu tk/mk acc bán cho user
-- =============================================
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) NOT NULL,
  `account_type_id` INT(11) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `extra_data` TEXT DEFAULT NULL,
  `status` ENUM('available','pending','sold') DEFAULT 'available',
  `order_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `sold_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_acc_type_status` (`account_type_id`, `status`),
  KEY `idx_acc_game` (`game_id`),
  KEY `idx_acc_order` (`order_id`),
  KEY `idx_acc_user` (`user_id`),
  CONSTRAINT `fk_acc_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_acc_type` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`) ON DELETE CASCADE
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
  `claim_token` VARCHAR(80) DEFAULT NULL COMMENT 'Per-user-request token, ngan moi user 1 link rieng',
  `short_url` TEXT DEFAULT NULL COMMENT 'URL da shorten cho user nay',
  `is_claimed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = da nhan key, 0 = moi tao token cho user vuot link',
  `claimed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_free_user` (`free_key_id`, `user_id`),
  UNIQUE KEY `uniq_claim_token` (`claim_token`),
  KEY `idx_claim_user` (`user_id`),
  KEY `idx_claim_key` (`key_id`),
  CONSTRAINT `fk_claim_freekey` FOREIGN KEY (`free_key_id`) REFERENCES `free_keys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_key` FOREIGN KEY (`key_id`) REFERENCES `keys` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: free_key_web_claims
-- Track claim từ getkey.php (web, không cần Telegram).
-- Dedupe theo IP hash: 1 free key/IP/ngày.
-- =============================================
DROP TABLE IF EXISTS `free_key_web_claims`;
CREATE TABLE `free_key_web_claims` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `free_key_id` INT(11) NOT NULL,
  `ip_hash` CHAR(64) NOT NULL,
  `key_code` VARCHAR(100) NOT NULL,
  `claim_date` DATE NOT NULL,
  `claimed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_webclaim_freekey_ip` (`free_key_id`, `ip_hash`),
  UNIQUE KEY `uniq_webclaim_ip_date` (`ip_hash`, `claim_date`),
  KEY `idx_webclaim_date` (`claim_date`),
  CONSTRAINT `fk_webclaim_freekey` FOREIGN KEY (`free_key_id`) REFERENCES `free_keys` (`id`) ON DELETE CASCADE
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
