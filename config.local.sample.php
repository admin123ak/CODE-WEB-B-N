<?php
/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
// =============================================
// FILE CONFIG NỘI BỘ - DO INSTALLER TẠO TỰ ĐỘNG
// =============================================
// File này KHÔNG được push lên git (đã có trong .gitignore).
// Tự động tạo bởi install.php khi setup lần đầu.
//
// Nếu cần edit thủ công: copy file này thành config.local.php và sửa.
// =============================================

// --- Database ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// --- Telegram Bot ---
define('BOT_TOKEN', 'your_bot_token_from_botfather');
define('ADMIN_CHAT_ID', 'your_admin_telegram_chat_id');
define('BOT_USERNAME', 'YOUR_BOT_USERNAME');

// --- Website ---
define('SITE_URL', 'https://your-domain.com');
define('SITE_NAME', 'YOUR SITE NAME');

// --- Admin panel ---
// Tạo hash bằng: php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
define('ADMIN_PASSWORD_HASH', '$2y$10$REPLACE_THIS_WITH_REAL_HASH');
define('ADMIN_SESSION_TTL', 3600);

// --- Bank info ---
define('BANK_NAME', 'MBBANK');
define('BANK_ACCOUNT', '0000000000');
define('BANK_OWNER', 'YOUR NAME');
define('VIETQR_BANK_ID', '970422'); // MBBank BIN

// --- MBBANK API ---
define('MBBANK_HISTORY_API_KEY', 'your_mbbank_api_key');
define('MBBANK_AUTO_APPROVE_ENABLED', true);

// --- Shortlink APIs ---
define('LAYMA_API_TOKEN', '7fc1aa570262544a7b80d1bc0ab3c4e6');
define('LINK4M_API_TOKEN', 'your_link4m_token');
define('YEUMONEY_API_TOKEN', 'your_yeumoney_token'); // legacy, không bắt buộc
define('FREE_SHORTLINK_LAYERS', 2); // 1 = chỉ Layma, 2 = Layma + Link4M
define('FREE_GETKEY_ENABLED', true);

// --- Binance USDT TRC20 (auto-thanh-toán crypto) ---
// Để TRỐNG ban đầu — admin nhập sau qua web. Khi cả 2 field này có giá trị
// và CRYPTO_AUTO_APPROVE_ENABLED=true thì option Binance hiện ở checkout.
define('USDT_TRC20_ADDRESS', '');
define('TRONGRID_API_KEY', '');
define('CRYPTO_AUTO_APPROVE_ENABLED', false);

// --- Ví user (balance) — luôn bật ---
define('BALANCE_ENABLED', true);

// --- Nạp card qua doithe.vn (auto API) ---
// API URL hardcode trong config.php (https://doithe.vn/chargingws/v2) — admin không cần sửa.
// Partner ID + Partner Key lấy từ trang merchant doithe.vn sau khi đăng ký.
// Rate per-telco = % chiết khấu doithe.vn áp cho từng nhà mạng (xem dashboard merchant).
// Tiền vào ví = face_value × (1 - rate%). Vd Viettel 28%: thẻ 100k → ví 72k.
// Callback URL paste vào doithe.vn: {SITE_URL}/card_callback.php (POST).
define('CARD_RATE_VIETTEL', '28');
define('CARD_RATE_MOBIFONE', '30');
define('CARD_RATE_VINAPHONE', '30');
define('DOITHE_PARTNER_ID', '');
define('DOITHE_PARTNER_KEY', '');

// --- Secure tokens (random) - installer sẽ tự generate ---
define('CRON_RUN_TOKEN', 'CHANGE_ME_random_64_chars');
define('AUTOMATION_RUN_TOKEN', 'CHANGE_ME_random_32_chars');
define('TELEGRAM_WEBHOOK_SECRET', 'CHANGE_ME_random_32_chars');

// --- Timezone ---
define('APP_TIMEZONE', 'Asia/Ho_Chi_Minh');
