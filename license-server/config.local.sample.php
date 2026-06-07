<?php
// =============================================
// LICENSE SERVER - CONFIG LOCAL (SAMPLE)
// Copy thành config.local.php và điền giá trị thật.
// File config.local.php KHÔNG push git.
// =============================================

// --- Database ---
define('LS_DB_HOST', '127.0.0.1');
define('LS_DB_NAME', 'license_server');
define('LS_DB_USER', 'your_db_user');
define('LS_DB_PASS', 'your_db_pass');

// --- Secret ký HMAC (random 64 hex) — PHẢI khớp LICENSE_PUBLIC_SECRET bên client ---
// Tạo bằng: php -r "echo bin2hex(random_bytes(32));"
define('LS_SIGNING_SECRET', 'CHANGE_ME_64_hex_chars');

// --- Session admin ---
define('LS_ADMIN_SESSION_TTL', 3600);
