-- =============================================
-- UPGRADE.SQL - MIGRATION CHO HOSTING CÓ DỮ LIỆU CŨ
-- =============================================
-- Chạy 1 lần qua phpMyAdmin nếu đang nâng cấp từ version cũ
-- (đã có data trong DB). KHÔNG cần chạy nếu cài mới.
--
-- BACKUP DATABASE TRƯỚC: mysqldump -u USER -p DBNAME > backup.sql
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- 1) bank_transactions.tx_date: VARCHAR → DATETIME
-- ---------------------------------------------
UPDATE `bank_transactions`
SET `tx_date` = STR_TO_DATE(`tx_date`, '%d/%m/%Y %H:%i:%s')
WHERE `tx_date` REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}';

ALTER TABLE `bank_transactions`
  MODIFY `tx_date` DATETIME NOT NULL;

ALTER TABLE `bank_transactions`
  ADD INDEX IF NOT EXISTS `idx_tx_date` (`tx_date`);

-- ---------------------------------------------
-- 2) keys: UNIQUE (key_code) → UNIQUE (key_code, user_id)
--    Cho phép multi-claim: nhiều user có thể có cùng key_code
-- ---------------------------------------------
ALTER TABLE `keys` DROP INDEX `key_code`;
ALTER TABLE `keys` ADD UNIQUE KEY `uniq_keys_code_user` (`key_code`, `user_id`);

-- ---------------------------------------------
-- 3) keys: thêm index cover + index order_id, expire_at
-- ---------------------------------------------
ALTER TABLE `keys` DROP INDEX `idx_key_pool`;
ALTER TABLE `keys` ADD INDEX `idx_keys_pool` (`status`, `game_id`, `package_id`, `id`);
ALTER TABLE `keys` ADD INDEX IF NOT EXISTS `idx_keys_order`  (`order_id`);
ALTER TABLE `keys` ADD INDEX IF NOT EXISTS `idx_keys_expire` (`expire_at`);

-- ---------------------------------------------
-- 4) keys: thêm FK order_id (set orphan = NULL trước)
-- ---------------------------------------------
UPDATE `keys` k
LEFT JOIN `orders` o ON k.order_id = o.id
SET k.order_id = NULL
WHERE k.order_id IS NOT NULL AND o.id IS NULL;

ALTER TABLE `keys`
  ADD CONSTRAINT `fk_keys_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------
-- 5) orders: thêm index hiệu năng
-- ---------------------------------------------
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_user_created`   (`user_id`, `created_at`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_status_created` (`status`, `created_at`);

-- ---------------------------------------------
-- 6) free_key_claims: thêm FK
-- ---------------------------------------------
UPDATE `free_key_claims` fkc
LEFT JOIN `keys` k ON fkc.key_id = k.id
SET fkc.key_id = NULL
WHERE fkc.key_id IS NOT NULL AND k.id IS NULL;

ALTER TABLE `free_key_claims` ADD CONSTRAINT `fk_claim_freekey` FOREIGN KEY (`free_key_id`) REFERENCES `free_keys` (`id`) ON DELETE CASCADE;
ALTER TABLE `free_key_claims` ADD CONSTRAINT `fk_claim_user`    FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`)     ON DELETE CASCADE;
ALTER TABLE `free_key_claims` ADD CONSTRAINT `fk_claim_key`     FOREIGN KEY (`key_id`)      REFERENCES `keys` (`id`)      ON DELETE SET NULL;

-- ---------------------------------------------
-- 7) packages, games, free_keys, users: thêm index hữu ích
-- ---------------------------------------------
ALTER TABLE `packages`  ADD INDEX IF NOT EXISTS `idx_packages_active`  (`game_id`, `is_active`);
ALTER TABLE `games`     ADD INDEX IF NOT EXISTS `idx_games_active_sort`(`is_active`, `sort_order`);
ALTER TABLE `free_keys` ADD INDEX IF NOT EXISTS `idx_freekey_active`   (`is_active`, `expire_at`);
ALTER TABLE `users`     ADD INDEX IF NOT EXISTS `idx_users_username`   (`telegram_username`);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- 8) BINANCE USDT TRC20 SUPPORT (added 2026-05-22)
--    - orders: thêm payment_method, crypto_amount, usdt_vnd_rate
--    - bank_transactions: thêm source ('mbbank' | 'binance'), nới amount 18,6
-- =============================================
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `payment_method` ENUM('mbbank','binance') NOT NULL DEFAULT 'mbbank' AFTER `amount`,
  ADD COLUMN IF NOT EXISTS `crypto_amount`  DECIMAL(18,6) DEFAULT NULL AFTER `payment_method`,
  ADD COLUMN IF NOT EXISTS `usdt_vnd_rate`  DECIMAL(12,2) DEFAULT NULL AFTER `crypto_amount`;

ALTER TABLE `orders`
  ADD INDEX IF NOT EXISTS `idx_orders_crypto_amount`   (`crypto_amount`),
  ADD INDEX IF NOT EXISTS `idx_orders_payment_method`  (`payment_method`, `status`);

ALTER TABLE `bank_transactions`
  ADD COLUMN IF NOT EXISTS `source` ENUM('mbbank','binance') NOT NULL DEFAULT 'mbbank' AFTER `amount`;

ALTER TABLE `bank_transactions`
  MODIFY `amount` DECIMAL(18,6) NOT NULL;

ALTER TABLE `bank_transactions`
  ADD INDEX IF NOT EXISTS `idx_tx_source` (`source`);

-- =============================================
-- VERIFY
-- =============================================
SHOW INDEX FROM `keys`;
SHOW INDEX FROM `orders`;
SHOW INDEX FROM `bank_transactions`;
