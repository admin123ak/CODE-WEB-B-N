-- Migration 002: Fix orders table for acc orders
-- package_id=0 vi phạm FK -> drop FK, cho NULL, thêm account_type_id
-- Chạy từng dòng riêng biệt

ALTER TABLE `orders` DROP FOREIGN KEY `fk_orders_package`;
ALTER TABLE `orders` MODIFY COLUMN `package_id` INT(11) DEFAULT NULL;
ALTER TABLE `orders` ADD COLUMN `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`;
ALTER TABLE `orders` ADD KEY `idx_orders_acc_type` (`account_type_id`);
