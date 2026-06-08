-- Migration 006: Add hours column for packages and keys (support gói theo giờ)
-- Chạy 1 lần, an toàn chạy lại

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='packages' AND COLUMN_NAME='hours'), 'ALTER TABLE `packages` ADD `hours` INT(11) NOT NULL DEFAULT 0 AFTER `days`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='keys' AND COLUMN_NAME='hours'), 'ALTER TABLE `keys` ADD `hours` INT(11) NOT NULL DEFAULT 0 AFTER `days`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
