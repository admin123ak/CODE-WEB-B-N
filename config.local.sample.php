<?php
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
define('LINK4M_API_TOKEN', 'your_link4m_token');
define('YEUMONEY_API_TOKEN', 'your_yeumoney_token');
define('FREE_GETKEY_ENABLED', true);

// --- Secure tokens (random) - installer sẽ tự generate ---
define('CRON_RUN_TOKEN', 'CHANGE_ME_random_64_chars');
define('AUTOMATION_RUN_TOKEN', 'CHANGE_ME_random_32_chars');
define('TELEGRAM_WEBHOOK_SECRET', 'CHANGE_ME_random_32_chars');

// --- Timezone ---
define('APP_TIMEZONE', 'Asia/Ho_Chi_Minh');
