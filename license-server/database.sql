-- =============================================
-- LICENSE SERVER - DATABASE SCHEMA
-- =============================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Admin đăng nhập (user/pass)
DROP TABLE IF EXISTS `ls_admins`;
CREATE TABLE `ls_admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ls_admin_user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- License keys
DROP TABLE IF EXISTS `ls_licenses`;
CREATE TABLE `ls_licenses` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `license_key` VARCHAR(64) NOT NULL,
  `customer_name` VARCHAR(150) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `max_domains` INT(11) NOT NULL DEFAULT 1,
  `status` ENUM('active','suspended','expired') NOT NULL DEFAULT 'active',
  `expires_at` DATETIME DEFAULT NULL COMMENT 'NULL = vĩnh viễn',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_license_key` (`license_key`),
  KEY `idx_license_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activations: domain nào đang chạy license nào
DROP TABLE IF EXISTS `ls_activations`;
CREATE TABLE `ls_activations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `license_id` INT(11) NOT NULL,
  `domain` VARCHAR(190) NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `app_version` VARCHAR(20) DEFAULT NULL,
  `first_seen` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_lic_domain` (`license_id`, `domain`),
  KEY `idx_act_lastseen` (`last_seen`),
  CONSTRAINT `fk_act_license` FOREIGN KEY (`license_id`) REFERENCES `ls_licenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Releases: các bản code (.zip)
DROP TABLE IF EXISTS `ls_releases`;
CREATE TABLE `ls_releases` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(20) NOT NULL,
  `zip_filename` VARCHAR(190) NOT NULL,
  `changelog` TEXT DEFAULT NULL,
  `is_latest` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_release_ver` (`version`),
  KEY `idx_release_latest` (`is_latest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
