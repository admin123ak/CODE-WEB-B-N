-- =============================================
-- MIGRATION: V2 - INDEX + FK + DATATYPE FIX
-- =============================================
-- Dành cho hosting ĐANG CHẠY production (đã có dữ liệu).
-- Chạy 1 lần qua phpMyAdmin sau khi pull code mới.
--
-- Lưu ý: BACKUP DATABASE TRƯỚC KHI CHẠY!
-- mysqldump -u USER -p DBNAME > backup_$(date +%F).sql
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- 1) FIX bank_transactions.tx_date: VARCHAR -> DATETIME
-- ---------------------------------------------
-- Đầu tiên convert string dd/mm/yyyy HH:ii:ss sang DATETIME chuẩn
UPDATE `bank_transactions`
SET `tx_date` = STR_TO_DATE(`tx_date`, '%d/%m/%Y %H:%i:%s')
WHERE `tx_date` REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}';

ALTER TABLE `bank_transactions`
  MODIFY `tx_date` DATETIME NOT NULL;

ALTER TABLE `bank_transactions`
  ADD INDEX IF NOT EXISTS `idx_tx_date` (`tx_date`);

-- ---------------------------------------------
-- 2) keys: thêm index cover + FK order_id
-- ---------------------------------------------
-- Drop old narrow index nếu có
ALTER TABLE `keys` DROP INDEX `idx_key_pool`;

-- Thêm index cover query lấy pool key
ALTER TABLE `keys` ADD INDEX `idx_keys_pool` (`status`, `game_id`, `package_id`, `id`);

-- Thêm index cho order_id
ALTER TABLE `keys` ADD INDEX IF NOT EXISTS `idx_keys_order` (`order_id`);
ALTER TABLE `keys` ADD INDEX IF NOT EXISTS `idx_keys_expire` (`expire_at`);

-- Thêm FK order_id (cẩn thận: nếu có orphan row sẽ fail)
-- Trước khi thêm FK, set orphan order_id = NULL
UPDATE `keys` k
LEFT JOIN `orders` o ON k.order_id = o.id
SET k.order_id = NULL
WHERE k.order_id IS NOT NULL AND o.id IS NULL;

ALTER TABLE `keys`
  ADD CONSTRAINT `fk_keys_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------
-- 3) orders: thêm index (user_id, created_at DESC) + (status, created_at)
-- ---------------------------------------------
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_user_created` (`user_id`, `created_at`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_orders_status_created` (`status`, `created_at`);

-- ---------------------------------------------
-- 4) free_key_claims: thêm FK
-- ---------------------------------------------
-- Set orphan key_id = NULL trước khi thêm FK
UPDATE `free_key_claims` fkc
LEFT JOIN `keys` k ON fkc.key_id = k.id
SET fkc.key_id = NULL
WHERE fkc.key_id IS NOT NULL AND k.id IS NULL;

ALTER TABLE `free_key_claims`
  ADD CONSTRAINT `fk_claim_freekey` FOREIGN KEY (`free_key_id`) REFERENCES `free_keys` (`id`) ON DELETE CASCADE;

ALTER TABLE `free_key_claims`
  ADD CONSTRAINT `fk_claim_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `free_key_claims`
  ADD CONSTRAINT `fk_claim_key` FOREIGN KEY (`key_id`) REFERENCES `keys` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------
-- 5) packages: thêm index (game_id, is_active)
-- ---------------------------------------------
ALTER TABLE `packages` ADD INDEX IF NOT EXISTS `idx_packages_active` (`game_id`, `is_active`);

-- ---------------------------------------------
-- 6) games: thêm index sort
-- ---------------------------------------------
ALTER TABLE `games` ADD INDEX IF NOT EXISTS `idx_games_active_sort` (`is_active`, `sort_order`);

-- ---------------------------------------------
-- 7) free_keys: thêm index active+expire
-- ---------------------------------------------
ALTER TABLE `free_keys` ADD INDEX IF NOT EXISTS `idx_freekey_active` (`is_active`, `expire_at`);

-- ---------------------------------------------
-- 8) users: thêm index username (cho lookup)
-- ---------------------------------------------
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_username` (`telegram_username`);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- VERIFY: xem các index đã thêm thành công
-- =============================================
SHOW INDEX FROM `keys`;
SHOW INDEX FROM `orders`;
SHOW INDEX FROM `bank_transactions`;
