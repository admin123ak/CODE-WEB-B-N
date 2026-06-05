-- Migration 004: Thêm download_url cho games
-- Chạy 1 lần, an toàn chạy lại

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='games' AND COLUMN_NAME='download_url'), 'ALTER TABLE `games` ADD `download_url` VARCHAR(500) DEFAULT NULL AFTER `icon_url`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
