-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th5 22, 2026 lúc 05:54 PM
-- Phiên bản máy phục vụ: 10.11.14-MariaDB-cll-lve
-- Phiên bản PHP: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `hcloucom_bankey`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin') DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`id`, `telegram_id`, `username`, `role`, `created_at`) VALUES
(1, 1985248892, 'hcloucom', 'superadmin', '2026-04-28 08:01:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_config_logs`
--

CREATE TABLE `admin_config_logs` (
  `id` int(11) NOT NULL,
  `admin` varchar(100) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_config_logs`
--

INSERT INTO `admin_config_logs` (`id`, `admin`, `config_key`, `old_value`, `new_value`, `created_at`) VALUES
(1, 'automation_daily', 'DAILY_REPORT_SENT', '', '2026-04-29', '2026-04-29 16:56:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `tx_hash` char(64) NOT NULL,
  `tx_date` varchar(32) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `description` text NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `status` enum('seen','matched','approved','ignored','error') DEFAULT 'seen',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `bank_transactions`
--

INSERT INTO `bank_transactions` (`id`, `tx_hash`, `tx_date`, `amount`, `description`, `order_code`, `status`, `note`, `created_at`, `processed_at`) VALUES
(1, '00725d15513e916005e794f87461ed926df60efb1f52a001fa805a8a0f0485e0', '29/04/2026 00:00:01', 10000, 'ZION O5CH7J826D7H-TESTBM. TU: ZION', NULL, 'seen', NULL, '2026-04-28 17:18:01', NULL),
(2, '2e57ff263a449753f77ad9e564d94445cbc947c15b64c838b1b44cab892620d9', '28/04/2026 23:49:07', 10000, 'ZION O5CH7J7Q10QV-TESTBM. TU: ZION', NULL, 'seen', NULL, '2026-04-28 17:18:01', NULL),
(3, '071663680821e1fbda0b660bc7aaf885bfb5bbdf4656139276fac68c2d745188', '28/04/2026 23:46:39', 10000, 'ZION O5CH7J7Q10L7-TESTBM. TU: ZION', NULL, 'seen', NULL, '2026-04-28 17:18:01', NULL),
(4, '7205e65592d7c62c77f0edc3e9f7e86eac8da7501be19b5e001c1e0ef6807ab1', '28/04/2026 23:43:24', 21000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q13U5  abcd. TU: ZION', NULL, 'seen', NULL, '2026-04-28 17:18:01', NULL),
(5, 'f0f28d4a28efefff2839c977f516101f57e12b9cefd572e112cece1703b1041b', '28/04/2026 23:41:26', 20000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0RVM  ORDMBC. TU: ZION', 'ORDMBC', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:50', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(6, 'f0cfad58f988cef0271e5ee221e0be66766a3a6f6a41540da757f7ba6b903eb3', '28/04/2026 23:31:35', 10000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0J66  ORD201. TU: ZION', 'ORD201', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:51', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(7, '3b7aa28f8d99cdfa7b4c36f7483ad7b5e618ffc3064b8c5493bb5852d22cbb50', '28/04/2026 23:29:22', 10000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0J0M  ORD200. TU: ZION', 'ORD200', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:52', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(8, '46787639ce4e67ded0e0dfc213649a6a03a801772f51c8a40a42a645580abacc', '28/04/2026 23:27:40', 10000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0H52  ORD199. TU: ZION', 'ORD199', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:53', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(9, 'a0aeebd664d6187c96f4181a105ce54bef09acea6083c80f8d92e76327dd21c9', '28/04/2026 23:24:52', 10000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0HST  ORD1827. TU: ZION', 'ORD1827', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:54', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(10, '1fff6824403dd81e7f831170182ea35f934f5a942bfd227a971967f5cc89338b', '28/04/2026 23:19:28', 11000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q0FKB  ORD110. TU: ZION', 'ORD110', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:54', '2026-04-28 17:18:01', '2026-04-28 17:18:01'),
(11, 'a76f86366dd6f9fd1c324988c8b4c88387c9b803a2df18e9b4942bf2f29bc282', '28/04/2026 23:14:49', 10000, 'ZION Qacpvj2052  APPMB1 1  O5CH7J7Q091U  ORDABC. TU: ZION', 'ORDABC', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:35:55', '2026-04-28 17:18:01', '2026-04-28 17:18:02'),
(34, '74786919d853ec693d643b8372f629eb2fd44f3ba543004903cdd48387bcab3b', '29/04/2026 00:20:54', 25000, 'ZION O5CH7J826IJE-ORD260429B4BAE7. TU: ZION', 'ORD260429B4BAE7', 'approved', 'Auto approved ORD260429B4BAE7', '2026-04-28 17:21:03', '2026-04-28 17:21:03'),
(166, '7b86d44a1810d52b3edcc5e6f0040471e237f969901d1c4fb57103911a866017', '29/04/2026 00:30:37', 25000, 'ZION O5CH7J826RVG-ORD2604291C60B6. TU: ZION', 'ORD2604291C60B6', 'approved', 'Auto approved ORD2604291C60B6', '2026-04-28 17:31:03', '2026-04-28 17:31:03'),
(6952, 'f58698b6897efb1a5110b0f77a870b59a8235803c9f649439aaec46e57bbf984', '29/04/2026 08:56:34', 25000, 'ZION O5CH7J82EQV4-ORD260429F833CD. TU: ZION', 'ORD260429F833CD', 'approved', 'Auto approved ORD260429F833CD', '2026-04-29 01:57:03', '2026-04-29 01:57:03'),
(7190, 'd68e0b66276ec63e28cc6e7021eb71718b809ce04e6dc2c85126707b89ff02b5', '29/04/2026 09:12:40', 25000, 'ZION O5CH7J82F5EQ-ORD260429C0EE71. TU: ZION', 'ORD260429C0EE71', 'approved', 'Auto approved ORD260429C0EE71', '2026-04-29 02:13:03', '2026-04-29 02:13:03'),
(7685, '5070ac81f7f9d7de69ce87414676fa00204a8ebdb17a7f677c2fab2371e6509c', '29/04/2026 09:44:19', 25000, 'ZIONO5CH7J82GEHV-ORD260429800168. TU: ZION', 'ORD260429800168', 'approved', 'Auto approved ORD260429800168', '2026-04-29 02:45:03', '2026-04-29 02:45:03'),
(7701, 'fa5ee72d8a9675904f72fb43e47746e310b2808a423d71b306344faa43fba361', '29/04/2026 09:44:19', 25000, 'ZION O5CH7J82GEHV-ORD260429800168. TU: ZION', 'ORD260429800168', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-29 02:46:03', '2026-04-29 02:46:02', '2026-04-29 02:46:02'),
(9029, 'c82593afa33bc9df9a82bbb1bdbdfc7668ae9348fdf0795403f475315a784b2b', '29/04/2026 11:07:25', 25000, 'ZION O5CH7J82JBO7-ORD260429618545. TU: ZION', 'ORD260429618545', 'approved', 'Auto approved ORD260429618545', '2026-04-29 04:08:11', '2026-04-29 04:08:11'),
(13721, '89a5ff4b77891eee1d005dbc3c3484adb62d364b21eb7ee968c713792d488ed9', '29/04/2026 15:42:57', 5000, 'ZION O5CH7J82U9NK-ORD2604294D175D. TU: ZION', 'ORD2604294D175D', 'approved', 'Auto approved ORD2604294D175D', '2026-04-29 08:43:06', '2026-04-29 08:43:06'),
(18017, '304e21969b7be0220697bde0600006c002e2207e1bb44c8af57fed8b933760a7', '29/04/2026 19:28:00', 5000, 'ZION O5CH7J8394F4-ORD2604297ABC88. TU: ZION', 'ORD2604297ABC88', 'ignored', 'Không xử lý: đơn không còn pending sau khi rollback reseller/API\nADMIN_ALERTED 2026-04-29 16:14:08', '2026-04-29 12:37:24', '2026-04-29 16:13:18'),
(21779, 'e57ebd81ed95355cb220ce3d66426c1f4e5d55a5900f2aa8e83e2d49df5753af', '29/04/2026 23:19:52', 5000, 'ZION O5CH7J83K4F2-ORD2604293AF8B4. TU: ZION', 'ORD2604293AF8B4', 'approved', 'Auto approved ORD2604293AF8B4', '2026-04-29 16:20:17', '2026-04-29 16:20:17'),
(38979, '34917681b14bb6498f890e5917ddacfc677ce7a3b4dd50e0264536f72ba6d687', '30/04/2026 13:50:04', 5000, 'ZION O5CH7J8CBMNO-ORD2604306C0E1C. TU: ZION', 'ORD2604306C0E1C', 'approved', 'Auto approved ORD2604306C0E1C', '2026-04-30 06:50:17', '2026-04-30 06:50:17'),
(47484, 'f73c7b8dbe3f755a71ab35e8a9c52feb8a3236b9ebf6fcdbc4db9d6dccc8f173', '30/04/2026 20:07:27', 25000, 'ZIONO5CH7J8CRNHR-ORD260430A5EF8B. TU: ZION', 'ORD260430A5EF8B', 'approved', 'Auto approved ORD260430A5EF8B', '2026-04-30 13:07:32', '2026-04-30 13:07:32'),
(47506, '71750a6b9d9731ed26d56ee80403dfeb65addb03c41f3ba3892ebfaa3e66fcbf', '30/04/2026 20:07:27', 25000, 'ZION O5CH7J8CRNHR-ORD260430A5EF8B. TU: ZION', 'ORD260430A5EF8B', 'ignored', 'Không tìm thấy đơn pending\nADMIN_ALERTED 2026-04-30 13:08:08\nDUPLICATE_AFTER_APPROVED fixed 2026-04-30 13:15:00', '2026-04-30 13:07:37', '2026-04-30 13:07:37'),
(56568, '118fd8e3449acdb0ebad3d2bcea64662a3f2f2ac43367cc8e78faa5770bc60bd', '21/05/2026 17:41:38', 2000, 'ZION O5CH7JEKVMRJ-ORD26052122D7C6. TU: ZION', 'ORD26052122D7C6', 'ignored', 'Không tìm thấy đơn pending', '2026-05-21 16:41:54', '2026-05-21 16:41:54'),
(56569, 'b5fb6cbfc35adb65056e20490d016043377df622ed6ec0e89b752b5cd411d82c', '20/05/2026 18:26:09', 10000, 'ZION O5CH7JEBG1B7-ORD260520ABAF4E. TU: ZION', 'ORD260520ABAF4E', 'ignored', 'Không tìm thấy đơn pending', '2026-05-21 16:41:54', '2026-05-21 16:41:54'),
(56570, '8ffe97b8dcdf49019d1cf3d4df4496199deef93e4e54e9fdc890c6f6ee76f341', '19/05/2026 17:21:43', 50000, 'TRAN VAN HA MBVCB.14294273321.600277.TRAN VAN H A chuyen tien.CT tu 0571000029085 T RAN VAN HA toi 0868641019 TRAN VAN HOANG tai MB- Ma GD ACSP/ uk600277', NULL, 'seen', NULL, '2026-05-21 16:41:54', NULL),
(56571, '68c4e09d2fb12f505cd19adf967af6938b4dd7015380155a81695abe59b3863f', '21/05/2026 23:46:58', 10000, 'ZIONO5CH7JELGN0O-ORD260521C1E8F4. TU: ZION', 'ORD260521C1E8F4', 'approved', 'Auto approved ORD260521C1E8F4', '2026-05-21 16:47:04', '2026-05-21 16:47:04'),
(56572, '64577aee33cedce90c6c7823c7aa01ecfc6f94b3d449acd6382c93389720d6b7', '21/05/2026 23:52:04', 10000, 'ZION O5CH7JELGQU6-ORD26052179EDB1. TU: ZION', 'ORD26052179EDB1', 'approved', 'Auto approved ORD26052179EDB1', '2026-05-21 16:52:15', '2026-05-21 16:52:15'),
(56573, '63dfcebe714614cc87892e156609bf22b82fc5af077d087ffefb95c8f35dbe64', '22/05/2026 09:39:55', 10000, 'ZION O5CH7JETSI97-ORD26052201D2FB. TU: ZION', 'ORD26052201D2FB', 'approved', 'Auto approved ORD26052201D2FB', '2026-05-22 02:40:04', '2026-05-22 02:40:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `free_keys`
--

CREATE TABLE `free_keys` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `free_keys`
--

INSERT INTO `free_keys` (`id`, `key_code`, `game_id`, `package_id`, `days`, `key_type`, `is_active`, `start_at`, `expire_at`, `claim_token`, `short_url`, `created_at`) VALUES
(1, 'TESTFREEDEBUG2', 4, 8, 1, 'VIP', 0, '2026-04-28 17:34:14', '2026-04-29 17:34:14', 'd5aecfc86da18af74fcb4e32ee7fd95d3652cc42a890c8d2', 'https://yeumoney.com/3pW2ahGOU', '2026-04-28 10:34:14'),
(2, 'HCLOU_Jsbsj', 4, 8, 1, 'VIP', 0, '2026-04-28 17:34:50', '2026-04-29 17:34:50', 'f9f76cd6f8828ca2b94b9f9fa2ba07471836bc709f0c9c0e', 'https://yeumoney.com/7YUgWKD5', '2026-04-28 10:34:50'),
(3, 'HCLOUSJA', 4, 8, 1, 'VIP', 0, '2026-04-28 18:29:20', '2026-04-29 18:29:20', '65e91f36be952d75109eed07511a2664352e1b6c3205918f', 'https://link4m.co/st?api=69f0894f3bb1c61f3703a5d7&url=https%3A%2F%2Fyeumoney.com%2Fy_X98z', '2026-04-28 11:29:21'),
(4, 'MEMAY', 4, 8, 1, 'VIP', 0, '2026-05-22 09:14:02', '2026-05-23 09:14:02', '642a530d84e37fba18076da334c38f90ba58a6854f3e1d37', 'https://link4m.co/st?api=69f0894f3bb1c61f3703a5d7&url=https%3A%2F%2Fyeumoney.com%2FZ_U78ifcUN', '2026-05-22 02:14:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `free_key_claims`
--

CREATE TABLE `free_key_claims` (
  `id` int(11) NOT NULL,
  `free_key_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `key_id` int(11) DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `free_key_claims`
--

INSERT INTO `free_key_claims` (`id`, `free_key_id`, `user_id`, `key_id`, `claimed_at`) VALUES
(1, 2, 1, 12, '2026-04-28 11:07:18'),
(2, 4, 18, 42, '2026-05-22 02:30:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `package_name` varchar(200) NOT NULL,
  `icon_url` varchar(500) DEFAULT NULL,
  `type` enum('VIP','NORMAL') DEFAULT 'NORMAL',
  `root_type` varchar(50) DEFAULT 'Only Root',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `games`
--

INSERT INTO `games` (`id`, `name`, `package_name`, `icon_url`, `type`, `root_type`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Alpha X store', 'com.dts.freefiremax', NULL, 'VIP', 'Root & NoRoot', 1, 1, '2026-04-28 08:01:47'),
(3, 'Streamer panel', 'com.dts.freefiremax', NULL, 'VIP', 'Only Root', 1, 3, '2026-04-28 08:01:47'),
(4, 'Beyond Cheats', 'com.dts.freefiremax', 'https://play-lh.googleusercontent.com/EJ83sg58Oo2gAjMHFxFVLM6Z53kuH4_R0M7Yq7gts5fWSIlFchUlmskG1vJKMoncmfOxBXcgJyIaO-nak6sO-MM=s128', 'VIP', 'Root & NoRoot', 1, 4, '2026-04-28 08:01:47'),
(9, 'Rogerio mods', 'com.dts.freefiremax', NULL, 'VIP', 'Root & NoRoot', 1, 2, '2026-05-22 02:53:38'),
(10, 'Sjv cheats', 'com.dts.freefiremax', NULL, 'VIP', 'Only Root', 1, 5, '2026-05-22 02:53:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `keys`
--

CREATE TABLE `keys` (
  `id` int(11) NOT NULL,
  `key_code` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('available','pending','active','expired','locked') DEFAULT 'available',
  `days` int(11) NOT NULL,
  `reset_count` int(11) DEFAULT 0,
  `max_reset` int(11) DEFAULT 3,
  `start_at` timestamp NULL DEFAULT NULL,
  `expire_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `keys`
--

INSERT INTO `keys` (`id`, `key_code`, `user_id`, `game_id`, `package_id`, `order_id`, `status`, `days`, `reset_count`, `max_reset`, `start_at`, `expire_at`, `created_at`) VALUES
(38, 'TEST', 1, 4, 8, 37, 'active', 1, 0, 3, '2026-05-21 16:47:04', '2026-05-22 16:47:04', '2026-05-21 13:43:19'),
(39, 'HAHAA', 1, 4, 8, 38, 'active', 1, 0, 3, '2026-05-21 16:52:15', '2026-05-22 16:52:15', '2026-05-21 16:23:58'),
(40, 'STRICKS-X9TVKB', 1, 4, 8, 40, 'active', 1, 0, 3, '2026-05-21 17:47:47', '2026-05-22 17:47:47', '2026-05-21 16:58:03'),
(41, 'TESTMBBANK', 1, 4, 8, 41, 'active', 1, 0, 3, '2026-05-22 02:40:04', '2026-05-23 02:40:04', '2026-05-22 01:52:32'),
(42, 'MEMAY', 18, 4, 8, NULL, 'active', 1, 0, 3, '2026-05-22 02:30:08', '2026-05-23 02:30:08', '2026-05-22 02:30:08'),
(43, 'TESTDUYETTAY', 1, 4, 8, 42, 'active', 1, 0, 3, '2026-05-22 02:40:48', '2026-05-23 02:40:48', '2026-05-22 02:40:38'),
(44, 'STRICKS-07K3AZ', NULL, 3, 1, NULL, 'available', 1, 0, 3, NULL, NULL, '2026-05-22 02:47:43'),
(45, 'STRICKS-R556P2', 11, 4, 8, 45, 'active', 1, 3, 3, '2026-05-22 02:59:39', '2026-05-23 02:59:39', '2026-05-22 02:47:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `game_id`, `package_id`, `amount`, `status`, `payment_proof`, `admin_note`, `approved_at`, `approved_by`, `created_at`) VALUES
(1, 'ORD2604289CE162', 1, 1, 5, 30000, 'approved', NULL, NULL, '2026-04-28 08:23:47', 'hcloucom', '2026-04-28 08:23:37'),
(3, 'ORD260428E124CD', 1, 3, 1, 25000, 'approved', NULL, NULL, '2026-04-28 08:31:32', 'hcloucom', '2026-04-28 08:30:54'),
(6, 'ORD2604285536B1', 1, 1, 5, 30000, 'rejected', NULL, NULL, NULL, 'web_admin', '2026-04-28 08:56:53'),
(7, 'ORD260428E519D1', 1, 1, 7, 150000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-28 08:58:38'),
(8, 'ORD260428CC5E18', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 09:56:38', 'hcloucom', '2026-04-28 09:56:28'),
(9, 'ORD2604289062BA', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 10:08:13', 'hcloucom', '2026-04-28 10:08:09'),
(10, 'ORD26042843DECF', 8, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 10:29:10', 'hcloucom', '2026-04-28 10:13:56'),
(11, 'ORD2604281042E4', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 14:11:02', 'hcloucom', '2026-04-28 14:09:53'),
(12, 'ORD2604280745A7', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 14:12:35', 'hcloucom', '2026-04-28 14:12:16'),
(13, 'ORD260428F584E9', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-28 14:15:11'),
(14, 'ORD2604289DB260', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-28 14:17:45'),
(15, 'ORD260428C9C960', 1, 4, 9, 75000, 'approved', NULL, NULL, '2026-04-28 14:19:12', 'hcloucom', '2026-04-28 14:18:20'),
(16, 'ORD2604287741F2', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 14:58:50', 'hcloucom', '2026-04-28 14:58:31'),
(17, 'ORD260429B4BAE7', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 17:21:03', 'mbbank_api', '2026-04-28 17:19:55'),
(18, 'ORD2604291C60B6', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-28 17:31:03', 'mbbank_api', '2026-04-28 17:30:09'),
(19, 'ORD260429C88953', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-29 01:53:32'),
(20, 'ORD260429F833CD', 1, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-29 01:57:03', 'mbbank_api', '2026-04-29 01:55:59'),
(21, 'ORD260429F7DF52', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-29 01:58:07'),
(22, 'ORD260429FE65ED', 1, 4, 8, 25000, 'cancelled', NULL, 'Tự huỷ do quá 15 phút chưa thanh toán\nAUTO_CANCEL_NOTIFIED 2026-04-29 02:35:37', NULL, NULL, '2026-04-29 02:04:31'),
(23, 'ORD260429C0EE71', 2, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-29 02:13:03', 'mbbank_api', '2026-04-29 02:12:12'),
(24, 'ORD260429478B73', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-29 02:24:20'),
(25, 'ORD260429800168', 2, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-29 02:45:03', 'mbbank_api', '2026-04-29 02:43:52'),
(26, 'ORD260429618545', 2, 4, 8, 25000, 'approved', NULL, NULL, '2026-04-29 04:08:11', 'mbbank_api', '2026-04-29 04:07:02'),
(27, 'ORD260429C883FD', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'hcloucom', '2026-04-29 08:41:16'),
(28, 'ORD2604294D175D', 1, 4, 8, 5000, 'approved', NULL, NULL, '2026-04-29 08:43:06', 'mbbank_api', '2026-04-29 08:42:44'),
(29, 'ORD2604297ABC88', 1, 4, 8, 5000, 'cancelled', NULL, 'Tự huỷ do quá 15 phút chưa thanh toán\nAUTO_CANCEL_NOTIFIED 2026-04-29 12:46:07', NULL, NULL, '2026-04-29 12:27:35'),
(32, 'ORD2604293AF8B4', 1, 4, 8, 5000, 'approved', NULL, NULL, '2026-04-29 16:20:17', 'mbbank_api', '2026-04-29 16:19:31'),
(33, 'ORD2604306C0E1C', 1, 4, 8, 5000, 'approved', NULL, NULL, '2026-04-30 06:50:17', 'mbbank_api', '2026-04-30 06:49:26'),
(34, 'ORD260430A5EF8B', 1, 4, 8, 25000, 'cancelled', NULL, NULL, '2026-04-30 13:07:32', 'mbbank_api', '2026-04-30 13:07:06'),
(35, 'ORD260521D89B27', 1, 4, 8, 25000, 'cancelled', NULL, NULL, NULL, 'hcloucom', '2026-05-21 13:33:17'),
(36, 'ORD26052167B386', 1, 4, 8, 25000, 'rejected', NULL, NULL, NULL, 'web_admin', '2026-05-21 13:43:34'),
(37, 'ORD260521C1E8F4', 1, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-21 16:47:04', 'mbbank_api', '2026-05-21 16:46:36'),
(38, 'ORD26052179EDB1', 1, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-21 16:52:15', 'mbbank_api', '2026-05-21 16:50:31'),
(39, 'ORD260522B8B1E1', 1, 4, 8, 10000, 'cancelled', NULL, 'Tự huỷ do quá 15 phút chưa thanh toán', NULL, NULL, '2026-05-21 17:06:03'),
(40, 'ORD260522B27C80', 1, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-21 17:47:47', 'kingcrackteam', '2026-05-21 17:32:27'),
(41, 'ORD26052201D2FB', 1, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-22 02:40:04', 'mbbank_api', '2026-05-22 02:39:28'),
(42, 'ORD260522B71299', 1, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-22 02:40:48', 'kingcrackteam', '2026-05-22 02:40:43'),
(43, 'ORD26052255B2C7', 11, 4, 8, 10000, 'rejected', NULL, NULL, NULL, 'kingcrackteam', '2026-05-22 02:52:21'),
(44, 'ORD2605227CA21C', 11, 4, 8, 10000, 'rejected', NULL, NULL, NULL, 'kingcrackteam', '2026-05-22 02:58:15'),
(45, 'ORD2605226F326A', 11, 4, 8, 10000, 'approved', NULL, NULL, '2026-05-22 02:59:39', 'kingcrackteam', '2026-05-22 02:59:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `days` int(11) NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `key_type` enum('Normal','VIP') DEFAULT 'Normal',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `packages`
--

INSERT INTO `packages` (`id`, `game_id`, `name`, `days`, `price`, `key_type`, `is_active`) VALUES
(1, 3, 'Gói 1 ngày', 1, 10000, 'VIP', 1),
(2, 3, 'Gói 3 ngày', 3, 45000, 'Normal', 0),
(3, 3, 'Gói 7 ngày', 7, 75000, 'Normal', 0),
(4, 3, 'Gói 30 ngày', 30, 120000, 'Normal', 0),
(5, 1, 'Gói 1 ngày', 1, 30000, 'VIP', 0),
(6, 1, 'Gói 7 ngày', 7, 90000, 'VIP', 0),
(7, 1, 'Gói 30 ngày', 30, 150000, 'VIP', 0),
(8, 4, 'Gói 1 ngày', 1, 10000, 'VIP', 1),
(9, 4, 'Gói 7 ngày', 7, 75000, 'VIP', 0),
(10, 4, 'Gói 30 ngày', 30, 120000, 'VIP', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `telegram_username` varchar(100) DEFAULT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `balance` decimal(12,0) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `telegram_id`, `telegram_username`, `full_name`, `avatar_url`, `balance`, `created_at`, `updated_at`) VALUES
(1, 1985248892, 'kingcrackteam', 'KING CRACK', 'https://t.me/i/userpic/320/CXyGmiAD9JQI4mc67mXWMOFgemFI3U0_Hi1A-5XZoKk.svg', 0, '2026-04-28 08:14:18', '2026-05-21 13:33:08'),
(2, 8585285840, 'hcloudaily', 'Phò (HCLOU)', 'https://t.me/i/userpic/320/gIOCPPW7jgyFxbmZTvK1Kt5o_r6jYDxD4N6_ma0GNPoYWIBiZDODZJYA1CFaF6as.svg', 0, '2026-04-28 08:20:28', '2026-04-29 04:06:56'),
(7, 7875384671, 'Hieumodgamez', 'Hieu Hieu', 'https://t.me/i/userpic/320/5wRTMrSrqobPTXS4yWI-yitLp6e1q5Z38JQTAIM9_soDmacq0XQjBdrE9bRQSL1Z.svg', 0, '2026-04-28 09:37:11', '2026-04-28 09:37:11'),
(8, 7741553299, 'Anhkietreal', 'Anh Kiệt', 'https://t.me/i/userpic/320/QAgNmJ4FOvrKgIV79NraM9EbIvCXOXWWIw2gmufcnBBleea3KLtQed4bABk2w5aL.svg', 0, '2026-04-28 10:13:09', '2026-04-28 10:13:09'),
(9, 8404310578, 'Lovancheo', 'Phát Duy', 'https://t.me/i/userpic/320/6j9WqmjuK5vXZRJfffYEutUFp8JcM4Ixj0urm_m_uwLbvqAU9_4Stsm10i0bFxWa.svg', 0, '2026-04-28 11:15:32', '2026-04-28 11:15:32'),
(10, 8004474994, 'NgHuy11', '⫹/⫺ 𝑁𝑔𝐻𝑢𝑦 𝑟𝑜𝑜𝑡 𝑝ℎ𝑜𝑛𝑒 ᯤ 𓈆 11%', 'https://t.me/i/userpic/320/J4duIDHyjkecIsW_uRa9ZbXF_Vhkpn-IEkJR0DD9ZWKrsUevZU0Vd7eQqISYNOgh.svg', 0, '2026-04-28 11:25:31', '2026-04-28 11:25:31'),
(11, 8336469751, 'AlexCloud36', 'AlexCloud', 'https://t.me/i/userpic/320/KIfd7gOrXnnXEMl3G68XKzwL7IrnDcC51Nw2pZ_gGP7o4FiQJ09IszhFI3Eelrgr.svg', 0, '2026-04-28 11:50:54', '2026-05-22 02:51:40'),
(12, 7495838718, '', 'Ri Gia', 'https://t.me/i/userpic/320/jYBhCY2MZ9L0BH1_y58HT-NQX4tJwQgPF63Qfb_ZOsuO8UZDiE2vKUcWwY4TPBvC.svg', 0, '2026-04-28 13:13:19', '2026-04-28 13:13:19'),
(13, 6284151867, 'patosleel', 'đụ bà già', 'https://t.me/i/userpic/320/tjjGh0KRDlEHEL8b7PaF0xPpQs9-lJO6R5RVrWw3e3CkCyVkoPzA1QDMDSS0K0Xt.svg', 0, '2026-04-28 14:05:27', '2026-04-28 14:05:27'),
(14, 8194416391, 'Aincrad9', 'Lemon', 'https://t.me/i/userpic/320/ayBywq09myv6cTAAxsZxilW0kJ-KKGbI_X5WEKDYEA_htI4wbvb7hk2TevWBOAdn.svg', 0, '2026-04-28 17:10:44', '2026-04-28 17:10:44'),
(16, 8083542627, 'Sa_Nso', 'SaNnsoX', 'https://t.me/i/userpic/320/jHi8vBHqahxzQZLP7ydmvyurNBcl4YaMDDFmJtbXwHxC0z5inprIccaJmkjiW4iA.svg', 0, '2026-04-29 18:03:20', '2026-04-29 18:03:20'),
(17, 1969813015, 'ARABEMODZ2', '𝘽𝙤𝙨𝙨', 'https://t.me/i/userpic/320/ifYAr2mrf5C79tm3lY5K9Rmy0o71SjMPTL4CkWHqA5U.svg', 0, '2026-05-22 01:47:55', '2026-05-22 01:47:55'),
(18, 1, '', 'User1', NULL, 0, '2026-05-22 02:30:08', '2026-05-22 02:30:08');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- Chỉ mục cho bảng `admin_config_logs`
--
ALTER TABLE `admin_config_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_config_logs_created` (`created_at`),
  ADD KEY `idx_config_logs_key` (`config_key`);

--
-- Chỉ mục cho bảng `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tx_hash` (`tx_hash`),
  ADD KEY `idx_order_code` (`order_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `free_keys`
--
ALTER TABLE `free_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `claim_token` (`claim_token`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `is_active` (`is_active`);

--
-- Chỉ mục cho bảng `free_key_claims`
--
ALTER TABLE `free_key_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_free_user` (`free_key_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `keys`
--
ALTER TABLE `keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_code` (`key_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `idx_key_pool` (`status`,`game_id`,`package_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Chỉ mục cho bảng `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `admin_config_logs`
--
ALTER TABLE `admin_config_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56574;

--
-- AUTO_INCREMENT cho bảng `free_keys`
--
ALTER TABLE `free_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `free_key_claims`
--
ALTER TABLE `free_key_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `keys`
--
ALTER TABLE `keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT cho bảng `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `keys`
--
ALTER TABLE `keys`
  ADD CONSTRAINT `keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `keys_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `keys_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Ràng buộc cho bảng `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
