-- Migration 005: Free key per-user temp claim token
-- Cho phép nhiều user cùng vượt link nhận key từ pool
-- Key chỉ bị xoá khỏi pool khi user thực sự CLAIM thành công (sau khi vượt link)

-- 1. Thêm cột vào free_key_claims để lưu temp token per user-request
SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='claim_token'), 'ALTER TABLE `free_key_claims` ADD `claim_token` VARCHAR(80) DEFAULT NULL AFTER `key_id`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='short_url'), 'ALTER TABLE `free_key_claims` ADD `short_url` TEXT DEFAULT NULL', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='is_claimed'), 'ALTER TABLE `free_key_claims` ADD `is_claimed` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Index cho claim_token
SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND INDEX_NAME='uniq_claim_token'), 'ALTER TABLE `free_key_claims` ADD UNIQUE KEY `uniq_claim_token` (`claim_token`)', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Đánh dấu các claim cũ là is_claimed=1 để không ảnh hưởng logic mới
UPDATE `free_key_claims` SET `is_claimed`=1 WHERE `is_claimed`=0 AND `key_id` IS NOT NULL;
