-- Migration 002: Fix orders table for acc selling
-- An toàn chạy nhiều lần, có dữ liệu vẫn OK

SELECT '1. Drop FK' AS ' ';
SET @s = (SELECT IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_NAME='fk_orders_package'), 'ALTER TABLE `orders` DROP FOREIGN KEY `fk_orders_package`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT '2. package_id -> NULL' AS ' ';
ALTER TABLE `orders` MODIFY COLUMN `package_id` INT(11) DEFAULT NULL;

SELECT '3. Them account_type_id' AS ' ';
SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='account_type_id'), 'ALTER TABLE `orders` ADD COLUMN `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT '4. Them index' AS ' ';
SET @s = (SELECT IF(NOT EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND INDEX_NAME='idx_orders_acc_type'), 'ALTER TABLE `orders` ADD KEY `idx_orders_acc_type` (`account_type_id`)', 'SELECT 1'));
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT '✅ Done' AS ' ';
