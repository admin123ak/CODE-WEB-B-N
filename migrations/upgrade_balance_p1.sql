-- ============================================================
-- Migration: thêm bảng topup_requests + balance_logs (Phase 1)
-- An toàn paste trên production DB đã có data.
-- IF NOT EXISTS / IF NOT EXISTS được dùng để rerun không lỗi.
-- ============================================================

-- 1. Đảm bảo users.balance đã tồn tại (đa số schema cũ đã có).
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `balance` DECIMAL(12,0) DEFAULT 0 AFTER `avatar_url`;

-- 2. Tạo bảng topup_requests
CREATE TABLE IF NOT EXISTS `topup_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `method` ENUM('mbbank','binance','card') NOT NULL,
  `amount_requested` DECIMAL(15,2) NOT NULL,
  `amount_credited` DECIMAL(15,2) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','expired') DEFAULT 'pending',
  `unique_code` VARCHAR(50) DEFAULT NULL,
  `crypto_amount` DECIMAL(18,6) DEFAULT NULL,
  `usdt_vnd_rate` DECIMAL(12,2) DEFAULT NULL,
  `card_telco` ENUM('VIETTEL','MOBIFONE','VINAPHONE') DEFAULT NULL,
  `card_face_value` INT(11) DEFAULT NULL,
  `card_serial` VARCHAR(50) DEFAULT NULL,
  `card_code` VARCHAR(50) DEFAULT NULL,
  `provider_request_id` VARCHAR(50) DEFAULT NULL,
  `provider_response` TEXT DEFAULT NULL,
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

-- 3. Tạo bảng balance_logs
CREATE TABLE IF NOT EXISTS `balance_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `balance_after` DECIMAL(15,2) NOT NULL,
  `reason` ENUM('topup','purchase','refund','admin_adjust') NOT NULL,
  `ref_type` VARCHAR(50) DEFAULT NULL,
  `ref_id` INT(11) DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_balog_user_created` (`user_id`, `created_at` DESC),
  KEY `idx_balog_reason` (`reason`, `created_at`),
  KEY `idx_balog_ref` (`ref_type`, `ref_id`),
  CONSTRAINT `fk_balog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
