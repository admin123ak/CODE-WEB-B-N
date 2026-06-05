-- Migration 002: Fix orders table để support acc orders
-- package_id=0 vi phạm FK khi tạo acc order → drop FK, đổi NOT NULL thành DEFAULT NULL
-- Thêm account_type_id để link đúng với loại acc

ALTER TABLE `orders`
  DROP FOREIGN KEY `fk_orders_package`,
  DROP INDEX `idx_orders_package`,
  MODIFY COLUMN `package_id` INT(11) DEFAULT NULL,
  ADD COLUMN `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`,
  ADD KEY `idx_orders_package` (`package_id`),
  ADD KEY `idx_orders_acc_type` (`account_type_id`);
