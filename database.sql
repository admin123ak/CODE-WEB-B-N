/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.10-MariaDB, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: hcloucom_panel
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_config_logs`
--

DROP TABLE IF EXISTS `admin_config_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_config_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` varchar(100) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_config_logs_created` (`created_at`),
  KEY `idx_config_logs_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_config_logs`
--

LOCK TABLES `admin_config_logs` WRITE;
/*!40000 ALTER TABLE `admin_config_logs` DISABLE KEYS */;
INSERT INTO `admin_config_logs` VALUES
(1,'automation_daily','DAILY_REPORT_SENT','','2026-04-29','2026-04-29 16:56:08');
/*!40000 ALTER TABLE `admin_config_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` bigint(20) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin') DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegram_id` (`telegram_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES
(1,1985248892,'hcloucom','superadmin','2026-04-28 08:01:47');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_transactions`
--

DROP TABLE IF EXISTS `bank_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tx_hash` char(64) NOT NULL,
  `tx_date` varchar(32) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `description` text NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `status` enum('seen','matched','approved','ignored','error') DEFAULT 'seen',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tx_hash` (`tx_hash`),
  KEY `idx_order_code` (`order_code`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=56568 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transactions`
--

LOCK TABLES `bank_transactions` WRITE;
/*!40000 ALTER TABLE `bank_transactions` DISABLE KEYS */;
INSERT INTO `bank_transactions` VALUES
(1,'00725d15513e916005e794f87461ed926df60efb1f52a001fa805a8a0f0485e0','29/04/2026 00:00:01',10000,'ZION O5CH7J826D7H-TESTBM. TU: ZION',NULL,'seen',NULL,'2026-04-28 17:18:01',NULL),
(2,'2e57ff263a449753f77ad9e564d94445cbc947c15b64c838b1b44cab892620d9','28/04/2026 23:49:07',10000,'ZION O5CH7J7Q10QV-TESTBM. TU: ZION',NULL,'seen',NULL,'2026-04-28 17:18:01',NULL),
(3,'071663680821e1fbda0b660bc7aaf885bfb5bbdf4656139276fac68c2d745188','28/04/2026 23:46:39',10000,'ZION O5CH7J7Q10L7-TESTBM. TU: ZION',NULL,'seen',NULL,'2026-04-28 17:18:01',NULL),
(4,'7205e65592d7c62c77f0edc3e9f7e86eac8da7501be19b5e001c1e0ef6807ab1','28/04/2026 23:43:24',21000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q13U5  abcd. TU: ZION',NULL,'seen',NULL,'2026-04-28 17:18:01',NULL),
(5,'f0f28d4a28efefff2839c977f516101f57e12b9cefd572e112cece1703b1041b','28/04/2026 23:41:26',20000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0RVM  ORDMBC. TU: ZION','ORDMBC','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:50','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(6,'f0cfad58f988cef0271e5ee221e0be66766a3a6f6a41540da757f7ba6b903eb3','28/04/2026 23:31:35',10000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0J66  ORD201. TU: ZION','ORD201','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:51','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(7,'3b7aa28f8d99cdfa7b4c36f7483ad7b5e618ffc3064b8c5493bb5852d22cbb50','28/04/2026 23:29:22',10000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0J0M  ORD200. TU: ZION','ORD200','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:52','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(8,'46787639ce4e67ded0e0dfc213649a6a03a801772f51c8a40a42a645580abacc','28/04/2026 23:27:40',10000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0H52  ORD199. TU: ZION','ORD199','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:53','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(9,'a0aeebd664d6187c96f4181a105ce54bef09acea6083c80f8d92e76327dd21c9','28/04/2026 23:24:52',10000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0HST  ORD1827. TU: ZION','ORD1827','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:54','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(10,'1fff6824403dd81e7f831170182ea35f934f5a942bfd227a971967f5cc89338b','28/04/2026 23:19:28',11000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0FKB  ORD110. TU: ZION','ORD110','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:54','2026-04-28 17:18:01','2026-04-28 17:18:01'),
(11,'a76f86366dd6f9fd1c324988c8b4c88387c9b803a2df18e9b4942bf2f29bc282','28/04/2026 23:14:49',10000,'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q091U  ORDABC. TU: ZION','ORDABC','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:55','2026-04-28 17:18:01','2026-04-28 17:18:02'),
(34,'74786919d853ec693d643b8372f629eb2fd44f3ba543004903cdd48387bcab3b','29/04/2026 00:20:54',25000,'ZION O5CH7J826IJE-ORD260429B4BAE7. TU: ZION','ORD260429B4BAE7','approved','Auto approved ORD260429B4BAE7','2026-04-28 17:21:03','2026-04-28 17:21:03'),
(166,'7b86d44a1810d52b3edcc5e6f0040471e237f969901d1c4fb57103911a866017','29/04/2026 00:30:37',25000,'ZION O5CH7J826RVG-ORD2604291C60B6. TU: ZION','ORD2604291C60B6','approved','Auto approved ORD2604291C60B6','2026-04-28 17:31:03','2026-04-28 17:31:03'),
(6952,'f58698b6897efb1a5110b0f77a870b59a8235803c9f649439aaec46e57bbf984','29/04/2026 08:56:34',25000,'ZION O5CH7J82EQV4-ORD260429F833CD. TU: ZION','ORD260429F833CD','approved','Auto approved ORD260429F833CD','2026-04-29 01:57:03','2026-04-29 01:57:03'),
(7190,'d68e0b66276ec63e28cc6e7021eb71718b809ce04e6dc2c85126707b89ff02b5','29/04/2026 09:12:40',25000,'ZION O5CH7J82F5EQ-ORD260429C0EE71. TU: ZION','ORD260429C0EE71','approved','Auto approved ORD260429C0EE71','2026-04-29 02:13:03','2026-04-29 02:13:03'),
(7685,'5070ac81f7f9d7de69ce87414676fa00204a8ebdb17a7f677c2fab2371e6509c','29/04/2026 09:44:19',25000,'ZIONO5CH7J82GEHV-ORD260429800168. TU: ZION','ORD260429800168','approved','Auto approved ORD260429800168','2026-04-29 02:45:03','2026-04-29 02:45:03'),
(7701,'fa5ee72d8a9675904f72fb43e47746e310b2808a423d71b306344faa43fba361','29/04/2026 09:44:19',25000,'ZION O5CH7J82GEHV-ORD260429800168. TU: ZION','ORD260429800168','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:46:03','2026-04-29 02:46:02','2026-04-29 02:46:02'),
(9029,'c82593afa33bc9df9a82bbb1bdbdfc7668ae9348fdf0795403f475315a784b2b','29/04/2026 11:07:25',25000,'ZION O5CH7J82JBO7-ORD260429618545. TU: ZION','ORD260429618545','approved','Auto approved ORD260429618545','2026-04-29 04:08:11','2026-04-29 04:08:11'),
(13721,'89a5ff4b77891eee1d005dbc3c3484adb62d364b21eb7ee968c713792d488ed9','29/04/2026 15:42:57',5000,'ZION O5CH7J82U9NK-ORD2604294D175D. TU: ZION','ORD2604294D175D','approved','Auto approved ORD2604294D175D','2026-04-29 08:43:06','2026-04-29 08:43:06'),
(18017,'304e21969b7be0220697bde0600006c002e2207e1bb44c8af57fed8b933760a7','29/04/2026 19:28:00',5000,'ZION O5CH7J8394F4-ORD2604297ABC88. TU: ZION','ORD2604297ABC88','ignored','Không xử lý: đơn không còn pending sau khi rollback reseller/API\nADMIN_ALERTED 2026-04-29 16:14:08','2026-04-29 12:37:24','2026-04-29 16:13:18'),
(21779,'e57ebd81ed95355cb220ce3d66426c1f4e5d55a5900f2aa8e83e2d49df5753af','29/04/2026 23:19:52',5000,'ZION O5CH7J83K4F2-ORD2604293AF8B4. TU: ZION','ORD2604293AF8B4','approved','Auto approved ORD2604293AF8B4','2026-04-29 16:20:17','2026-04-29 16:20:17'),
(38979,'34917681b14bb6498f890e5917ddacfc677ce7a3b4dd50e0264536f72ba6d687','30/04/2026 13:50:04',5000,'ZION O5CH7J8CBMNO-ORD2604306C0E1C. TU: ZION','ORD2604306C0E1C','approved','Auto approved ORD2604306C0E1C','2026-04-30 06:50:17','2026-04-30 06:50:17'),
(47484,'f73c7b8dbe3f755a71ab35e8a9c52feb8a3236b9ebf6fcdbc4db9d6dccc8f173','30/04/2026 20:07:27',25000,'ZIONO5CH7J8CRNHR-ORD260430A5EF8B. TU: ZION','ORD260430A5EF8B','approved','Auto approved ORD260430A5EF8B','2026-04-30 13:07:32','2026-04-30 13:07:32'),
(47506,'71750a6b9d9731ed26d56ee80403dfeb65addb03c41f3ba3892ebfaa3e66fcbf','30/04/2026 20:07:27',25000,'ZION O5CH7J8CRNHR-ORD260430A5EF8B. TU: ZION','ORD260430A5EF8B','ignored','Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-30 13:08:08\nDUPLICATE_AFTER_APPROVED fixed 2026-04-30 13:15:00','2026-04-30 13:07:37','2026-04-30 13:07:37');
/*!40000 ALTER TABLE `bank_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_key_claims`
--

DROP TABLE IF EXISTS `free_key_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `free_key_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `free_key_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `key_id` int(11) DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_free_user` (`free_key_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `free_key_claims`
--

LOCK TABLES `free_key_claims` WRITE;
/*!40000 ALTER TABLE `free_key_claims` DISABLE KEYS */;
INSERT INTO `free_key_claims` VALUES
(1,2,1,12,'2026-04-28 11:07:18');
/*!40000 ALTER TABLE `free_key_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_keys`
--

DROP TABLE IF EXISTS `free_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `free_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_code` varchar(100) NOT NULL,
  `game_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `days` int(11) NOT NULL,
  `key_type` enum('Normal','VIP') DEFAULT 'VIP',
  `is_active` tinyint(1) DEFAULT 1,
  `start_at` datetime NOT NULL,
  `expire_at` datetime NOT NULL,
  `claim_token` varchar(80) NOT NULL,
  `short_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `claim_token` (`claim_token`),
  KEY `game_id` (`game_id`),
  KEY `package_id` (`package_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `free_keys`
--

LOCK TABLES `free_keys` WRITE;
/*!40000 ALTER TABLE `free_keys` DISABLE KEYS */;
INSERT INTO `free_keys` VALUES
(1,'TESTFREEDEBUG2',4,8,1,'VIP',0,'2026-04-28 17:34:14','2026-04-29 17:34:14','d5aecfc86da18af74fcb4e32ee7fd95d3652cc42a890c8d2','https://yeumoney.com/3pW2ahGOU','2026-04-28 10:34:14'),
(2,'HCLOU_Jsbsj',4,8,1,'VIP',0,'2026-04-28 17:34:50','2026-04-29 17:34:50','f9f76cd6f8828ca2b94b9f9fa2ba07471836bc709f0c9c0e','https://yeumoney.com/7YUgWKD5','2026-04-28 10:34:50'),
(3,'HCLOUSJA',4,8,1,'VIP',0,'2026-04-28 18:29:20','2026-04-29 18:29:20','65e91f36be952d75109eed07511a2664352e1b6c3205918f','https://link4m.co/st?api=69f0894f3bb1c61f3703a5d7&url=https%3A%2F%2Fyeumoney.com%2Fy_X98z','2026-04-28 11:29:21');
/*!40000 ALTER TABLE `free_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `package_name` varchar(200) NOT NULL,
  `icon_url` varchar(500) DEFAULT NULL,
  `type` enum('VIP','NORMAL') DEFAULT 'NORMAL',
  `root_type` varchar(50) DEFAULT 'Only Root',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `games`
--

LOCK TABLES `games` WRITE;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
INSERT INTO `games` VALUES
(1,'Liên Quân Mobile','com.garena.game.kgvn',NULL,'VIP','Only Root',0,1,'2026-04-28 08:01:47'),
(3,'Free Fire','com.dts.freefireth',NULL,'VIP','Only Root',0,3,'2026-04-28 08:01:47'),
(4,'Free Fire Max','com.dts.freefiremax','https://play-lh.googleusercontent.com/EJ83sg58Oo2gAjMHFxFVLM6Z53kuH4_R0M7Yq7gts5fWSIlFchUlmskG1vJKMoncmfOxBXcgJyIaO-nak6sO-MM=s128','VIP','Root & NoRoot',1,4,'2026-04-28 08:01:47');
/*!40000 ALTER TABLE `games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keys`
--

DROP TABLE IF EXISTS `keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_code` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('pending','active','expired','locked') DEFAULT 'pending',
  `days` int(11) NOT NULL,
  `reset_count` int(11) DEFAULT 0,
  `max_reset` int(11) DEFAULT 3,
  `start_at` timestamp NULL DEFAULT NULL,
  `expire_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `keys_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `keys_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keys`
--

LOCK TABLES `keys` WRITE;
/*!40000 ALTER TABLE `keys` DISABLE KEYS */;
INSERT INTO `keys` VALUES
(20,'HCLOUK5XkV',1,4,8,18,'expired',1,1,1,'2026-04-29 00:31:03','2026-04-30 00:31:03','2026-04-28 17:30:09'),
(22,'HCLOUPvvW4',1,4,8,20,'expired',1,2,1,'2026-04-29 08:57:03','2026-04-30 08:57:03','2026-04-29 01:55:59'),
(25,'HCLOUpAcfU',2,4,8,23,'expired',1,0,1,'2026-04-29 09:13:03','2026-04-30 09:13:03','2026-04-29 02:12:12'),
(27,'HCLOUHpfBe',2,4,8,25,'expired',1,0,1,'2026-04-29 09:45:03','2026-04-30 09:45:03','2026-04-29 02:43:52'),
(28,'HCLOUy42LV',2,4,8,26,'expired',1,0,1,'2026-04-29 11:08:11','2026-04-30 11:08:11','2026-04-29 04:07:02'),
(30,'HCLOUfuNbj',1,4,8,28,'active',1,0,3,'2026-04-29 15:43:06','2026-04-30 15:43:06','2026-04-29 08:42:44'),
(34,'HCLOUJUA2x',1,4,8,32,'active',1,0,3,'2026-04-29 23:20:17','2026-04-30 23:20:17','2026-04-29 16:19:31'),
(35,'HCLOUauZMJ',1,4,8,33,'active',1,0,3,'2026-04-30 13:50:17','2026-05-01 13:50:17','2026-04-30 06:49:26'),
(36,'HCLOUx5NHP',1,4,8,34,'active',1,0,3,'2026-04-30 20:07:32','2026-05-01 20:07:32','2026-04-30 13:07:06');
/*!40000 ALTER TABLE `keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `payment_proof` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `game_id` (`game_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,'ORD2604289CE162',1,1,5,30000,'approved',NULL,NULL,'2026-04-28 08:23:47','hcloucom','2026-04-28 08:23:37'),
(3,'ORD260428E124CD',1,3,1,25000,'approved',NULL,NULL,'2026-04-28 08:31:32','hcloucom','2026-04-28 08:30:54'),
(6,'ORD2604285536B1',1,1,5,30000,'rejected',NULL,NULL,NULL,'web_admin','2026-04-28 08:56:53'),
(7,'ORD260428E519D1',1,1,7,150000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-28 08:58:38'),
(8,'ORD260428CC5E18',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 09:56:38','hcloucom','2026-04-28 09:56:28'),
(9,'ORD2604289062BA',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 10:08:13','hcloucom','2026-04-28 10:08:09'),
(10,'ORD26042843DECF',8,4,8,25000,'approved',NULL,NULL,'2026-04-28 10:29:10','hcloucom','2026-04-28 10:13:56'),
(11,'ORD2604281042E4',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 14:11:02','hcloucom','2026-04-28 14:09:53'),
(12,'ORD2604280745A7',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 14:12:35','hcloucom','2026-04-28 14:12:16'),
(13,'ORD260428F584E9',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-28 14:15:11'),
(14,'ORD2604289DB260',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-28 14:17:45'),
(15,'ORD260428C9C960',1,4,9,75000,'approved',NULL,NULL,'2026-04-28 14:19:12','hcloucom','2026-04-28 14:18:20'),
(16,'ORD2604287741F2',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 14:58:50','hcloucom','2026-04-28 14:58:31'),
(17,'ORD260429B4BAE7',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 17:21:03','mbbank_api','2026-04-28 17:19:55'),
(18,'ORD2604291C60B6',1,4,8,25000,'approved',NULL,NULL,'2026-04-28 17:31:03','mbbank_api','2026-04-28 17:30:09'),
(19,'ORD260429C88953',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-29 01:53:32'),
(20,'ORD260429F833CD',1,4,8,25000,'approved',NULL,NULL,'2026-04-29 01:57:03','mbbank_api','2026-04-29 01:55:59'),
(21,'ORD260429F7DF52',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-29 01:58:07'),
(22,'ORD260429FE65ED',1,4,8,25000,'cancelled',NULL,'Tự huỷ do quá 15 phút chưa thanh toán\nAUTO_CANCEL_NOTIFIED 2026-04-29 02:35:37',NULL,NULL,'2026-04-29 02:04:31'),
(23,'ORD260429C0EE71',2,4,8,25000,'approved',NULL,NULL,'2026-04-29 02:13:03','mbbank_api','2026-04-29 02:12:12'),
(24,'ORD260429478B73',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-29 02:24:20'),
(25,'ORD260429800168',2,4,8,25000,'approved',NULL,NULL,'2026-04-29 02:45:03','mbbank_api','2026-04-29 02:43:52'),
(26,'ORD260429618545',2,4,8,25000,'approved',NULL,NULL,'2026-04-29 04:08:11','mbbank_api','2026-04-29 04:07:02'),
(27,'ORD260429C883FD',1,4,8,25000,'rejected',NULL,NULL,NULL,'hcloucom','2026-04-29 08:41:16'),
(28,'ORD2604294D175D',1,4,8,5000,'approved',NULL,NULL,'2026-04-29 08:43:06','mbbank_api','2026-04-29 08:42:44'),
(29,'ORD2604297ABC88',1,4,8,5000,'cancelled',NULL,'Tự huỷ do quá 15 phút chưa thanh toán\nAUTO_CANCEL_NOTIFIED 2026-04-29 12:46:07',NULL,NULL,'2026-04-29 12:27:35'),
(32,'ORD2604293AF8B4',1,4,8,5000,'approved',NULL,NULL,'2026-04-29 16:20:17','mbbank_api','2026-04-29 16:19:31'),
(33,'ORD2604306C0E1C',1,4,8,5000,'approved',NULL,NULL,'2026-04-30 06:50:17','mbbank_api','2026-04-30 06:49:26'),
(34,'ORD260430A5EF8B',1,4,8,25000,'approved',NULL,NULL,'2026-04-30 13:07:32','mbbank_api','2026-04-30 13:07:06');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `days` int(11) NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `key_type` enum('Normal','VIP') DEFAULT 'Normal',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES
(1,3,'Gói 1 ngày',1,25000,'Normal',0),
(2,3,'Gói 3 ngày',3,45000,'Normal',0),
(3,3,'Gói 7 ngày',7,75000,'Normal',0),
(4,3,'Gói 30 ngày',30,120000,'Normal',0),
(5,1,'Gói 1 ngày',1,30000,'VIP',0),
(6,1,'Gói 7 ngày',7,90000,'VIP',0),
(7,1,'Gói 30 ngày',30,150000,'VIP',0),
(8,4,'Gói 1 ngày',1,25000,'VIP',1),
(9,4,'Gói 7 ngày',7,75000,'VIP',1),
(10,4,'Gói 30 ngày',30,120000,'VIP',1);
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telegram_id` bigint(20) NOT NULL,
  `telegram_username` varchar(100) DEFAULT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `balance` decimal(12,0) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `telegram_id` (`telegram_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1985248892,'hcloucom','HCLOU ( Hỗ trợ )','https://t.me/i/userpic/320/CXyGmiAD9JQI4mc67mXWMOFgemFI3U0_Hi1A-5XZoKk.svg',0,'2026-04-28 08:14:18','2026-04-29 02:24:11'),
(2,8585285840,'hcloudaily','Phò (HCLOU)','https://t.me/i/userpic/320/gIOCPPW7jgyFxbmZTvK1Kt5o_r6jYDxD4N6_ma0GNPoYWIBiZDODZJYA1CFaF6as.svg',0,'2026-04-28 08:20:28','2026-04-29 04:06:56'),
(7,7875384671,'Hieumodgamez','Hieu Hieu','https://t.me/i/userpic/320/5wRTMrSrqobPTXS4yWI-yitLp6e1q5Z38JQTAIM9_soDmacq0XQjBdrE9bRQSL1Z.svg',0,'2026-04-28 09:37:11','2026-04-28 09:37:11'),
(8,7741553299,'Anhkietreal','Anh Kiệt','https://t.me/i/userpic/320/QAgNmJ4FOvrKgIV79NraM9EbIvCXOXWWIw2gmufcnBBleea3KLtQed4bABk2w5aL.svg',0,'2026-04-28 10:13:09','2026-04-28 10:13:09'),
(9,8404310578,'Lovancheo','Phát Duy','https://t.me/i/userpic/320/6j9WqmjuK5vXZRJfffYEutUFp8JcM4Ixj0urm_m_uwLbvqAU9_4Stsm10i0bFxWa.svg',0,'2026-04-28 11:15:32','2026-04-28 11:15:32'),
(10,8004474994,'NgHuy11','⫹/⫺ 𝑁𝑔𝐻𝑢𝑦 𝑟𝑜𝑜𝑡 𝑝ℎ𝑜𝑛𝑒 ᯤ 𓈆 11%','https://t.me/i/userpic/320/J4duIDHyjkecIsW_uRa9ZbXF_Vhkpn-IEkJR0DD9ZWKrsUevZU0Vd7eQqISYNOgh.svg',0,'2026-04-28 11:25:31','2026-04-28 11:25:31'),
(11,8336469751,'AlexCloud36','AlexCloud ( Hỗ Trợ )','https://t.me/i/userpic/320/KIfd7gOrXnnXEMl3G68XKzwL7IrnDcC51Nw2pZ_gGP7o4FiQJ09IszhFI3Eelrgr.svg',0,'2026-04-28 11:50:54','2026-04-28 11:50:54'),
(12,7495838718,'','Ri Gia','https://t.me/i/userpic/320/jYBhCY2MZ9L0BH1_y58HT-NQX4tJwQgPF63Qfb_ZOsuO8UZDiE2vKUcWwY4TPBvC.svg',0,'2026-04-28 13:13:19','2026-04-28 13:13:19'),
(13,6284151867,'patosleel','đụ bà già','https://t.me/i/userpic/320/tjjGh0KRDlEHEL8b7PaF0xPpQs9-lJO6R5RVrWw3e3CkCyVkoPzA1QDMDSS0K0Xt.svg',0,'2026-04-28 14:05:27','2026-04-28 14:05:27'),
(14,8194416391,'Aincrad9','Lemon','https://t.me/i/userpic/320/ayBywq09myv6cTAAxsZxilW0kJ-KKGbI_X5WEKDYEA_htI4wbvb7hk2TevWBOAdn.svg',0,'2026-04-28 17:10:44','2026-04-28 17:10:44'),
(16,8083542627,'Sa_Nso','SaNnsoX','https://t.me/i/userpic/320/jHi8vBHqahxzQZLP7ydmvyurNBcl4YaMDDFmJtbXwHxC0z5inprIccaJmkjiW4iA.svg',0,'2026-04-29 18:03:20','2026-04-29 18:03:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'hcloucom_panel'
--

--
-- Dumping routines for database 'hcloucom_panel'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30 14:04:42
