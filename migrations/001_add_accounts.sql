-- =============================================
-- Migration 001: Thêm bảng bán Acc
-- =============================================
-- Chạy 1 lần khi cập nhật code mới. Installer mới sẽ tự tạo.

CREATE TABLE IF NOT EXISTS `account_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(12,0) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_at_game` (`game_id`, `is_active`),
  KEY `idx_at_active` (`game_id`, `sort_order`),
  CONSTRAINT `fk_at_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `game_id` INT(11) NOT NULL,
  `account_type_id` INT(11) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `extra_data` TEXT DEFAULT NULL COMMENT 'Thông tin bổ sung: email, phone, v.v.',
  `status` ENUM('available','pending','sold') DEFAULT 'available',
  `order_id` INT(11) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `sold_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_acc_type_status` (`account_type_id`, `status`),
  KEY `idx_acc_game` (`game_id`),
  KEY `idx_acc_order` (`order_id`),
  CONSTRAINT `fk_acc_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_acc_type` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột order_type vào orders nếu chưa có
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'orders' AND column_name = 'order_type'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `order_type` ENUM(\'key\',\'account\') DEFAULT \'key\' AFTER `package_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Thêm cột account_id vào orders nếu chưa có
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'orders' AND column_name = 'account_id'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `account_id` INT(11) DEFAULT NULL AFTER `order_type`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Thêm cột category vào games nếu chưa có
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'games' AND column_name = 'category'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `games` ADD COLUMN `category` ENUM(\'key\',\'account\') DEFAULT \'key\' AFTER `type`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migration hoàn tất.
-- Lưu ý: orders.account_id sẽ được set khi đơn là account order.
-- Nhiều account có thể được gán vào 1 đơn qua orders table (không dùng account_id mà dùng order_id trong accounts).
