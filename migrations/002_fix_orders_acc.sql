-- Migration 002: Fix orders table
-- Cách dùng: copy từng dòng vào SQL tab phpMyAdmin, chạy từng dòng

-- B1: Tìm tên FK constraint (chạy dòng này trước, copy kết quả)
SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_TYPE='FOREIGN KEY' AND CONSTRAINT_NAME LIKE '%package%';

-- B2: Thay [FK_NAME] bằng kết quả ở B1 rồi chạy
-- ALTER TABLE `orders` DROP FOREIGN KEY `[FK_NAME]`;

-- B3: package_id -> NULL
ALTER TABLE `orders` MODIFY `package_id` INT(11) DEFAULT NULL;

-- B4: Thêm account_type_id (chỉ chạy 1 lần)
ALTER TABLE `orders` ADD `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`;

-- B5: Thêm index
ALTER TABLE `orders` ADD KEY `idx_orders_acc_type` (`account_type_id`);
