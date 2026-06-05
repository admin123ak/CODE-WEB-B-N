-- Migration 003: Add role + discount cho users table
-- Chạy toàn bộ 1 lần, an toàn chạy lại

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role'), 'ALTER TABLE `users` ADD `role` ENUM(\'customer\',\'reseller\',\'admin\') DEFAULT \'customer\'', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='discount'), 'ALTER TABLE `users` ADD `discount` DECIMAL(5,2) DEFAULT 0.00', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
