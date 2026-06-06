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
// CORE CONFIG LOADER + HELPER FUNCTIONS
// =============================================
// File này KHÔNG chứa secret. Secret nằm trong config.local.php (gitignored).
//
// Flow khởi tạo:
// 1. Nếu config.local.php tồn tại → load secret từ đó.
// 2. Nếu không tồn tại → redirect sang install.php (trừ khi đang chạy installer/CLI).
// =============================================

// --- Đường dẫn ---
if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__);

// --- Load config.local.php (chứa secret) ---
$_HCLOU_LOCAL_CONFIG = APP_ROOT . '/config.local.php';
$_HCLOU_INSTALL_LOCK = APP_ROOT . '/.install_lock';

if (file_exists($_HCLOU_LOCAL_CONFIG)) {
    require_once $_HCLOU_LOCAL_CONFIG;
} else {
    // Chưa có config → cho phép installer chạy, mọi nơi khác redirect
    $isInstaller = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'install.php')
        || defined('HCLOU_ALLOW_NO_CONFIG');
    if (!$isInstaller) {
        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, "ERROR: config.local.php chưa tồn tại. Chạy installer: domain.com/install.php\n");
            exit(1);
        }
        // Redirect web request sang installer
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        header('Location: ' . $baseUrl . '/install.php');
        exit;
    }
}

// --- Default fallback cho khi installer đang chạy (constants chưa define) ---
if (!defined('DB_CHARSET'))                 define('DB_CHARSET', 'utf8mb4');
if (!defined('APP_TIMEZONE'))               define('APP_TIMEZONE', 'Asia/Ho_Chi_Minh');
if (!defined('MBBANK_AUTO_APPROVE_ENABLED')) define('MBBANK_AUTO_APPROVE_ENABLED', true);
if (!defined('FREE_GETKEY_ENABLED'))        define('FREE_GETKEY_ENABLED', true);
if (!defined('FREE_SHORTLINK_LAYERS'))      define('FREE_SHORTLINK_LAYERS', 2);
if (!defined('LAYMA_API_TOKEN'))            define('LAYMA_API_TOKEN', '7fc1aa570262544a7b80d1bc0ab3c4e6');
if (!defined('YEUMONEY_API_TOKEN'))         define('YEUMONEY_API_TOKEN', '');
if (!defined('ADMIN_SESSION_TTL'))          define('ADMIN_SESSION_TTL', 3600);
if (!defined('VIETQR_BANK_ID'))             define('VIETQR_BANK_ID', '970422');

// --- Crypto (Binance USDT TRC20) defaults ---
// Bật ON khi đã cấu hình USDT_TRC20_ADDRESS + TRONGRID_API_KEY trong config.local.php.
// Nếu chưa cấu hình → option thanh toán Binance bị ẩn ở checkout (xem backend/api/index.php).
if (!defined('CRYPTO_AUTO_APPROVE_ENABLED')) define('CRYPTO_AUTO_APPROVE_ENABLED', false);
if (!defined('USDT_TRC20_ADDRESS'))          define('USDT_TRC20_ADDRESS', '');
if (!defined('TRONGRID_API_KEY'))            define('TRONGRID_API_KEY', '');
// Contract address chính thức của USDT trên TRON mainnet (cố định, không cần đổi).
if (!defined('USDT_TRC20_CONTRACT'))         define('USDT_TRC20_CONTRACT', 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t');

// --- Derived constants ---
if (defined('MBBANK_HISTORY_API_KEY') && !defined('MBBANK_HISTORY_API_URL')) {
    define('MBBANK_HISTORY_API_URL', 'https://queenvps.com/api/historymb/' . MBBANK_HISTORY_API_KEY);
}
if (defined('MBBANK_HISTORY_API_KEY') && defined('BOT_TOKEN') && !defined('MBBANK_POLL_SECRET')) {
    define('MBBANK_POLL_SECRET', hash_hmac('sha256', MBBANK_HISTORY_API_KEY, BOT_TOKEN));
}
// Crypto poll secret: HMAC từ địa chỉ ví + BOT_TOKEN.
// Dùng địa chỉ ví thay vì API key vì TronGrid key có thể trống (free tier).
if (defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '' && defined('BOT_TOKEN') && !defined('CRYPTO_POLL_SECRET')) {
    define('CRYPTO_POLL_SECRET', hash_hmac('sha256', USDT_TRC20_ADDRESS, BOT_TOKEN));
}

// --- Ví user (balance) — luôn bật ---
if (!defined('BALANCE_ENABLED'))           define('BALANCE_ENABLED', true);

// --- Card top-up (doithe.vn auto API) ---
// Rate = % chiết khấu doithe.vn áp cho từng nhà mạng (mặc định theo rate phổ biến 2026).
// Tiền vào ví = face_value × (1 - rate/100). Vd Viettel 28%: thẻ 100k → ví 72k.
if (!defined('CARD_RATE_VIETTEL'))   define('CARD_RATE_VIETTEL', '28');
if (!defined('CARD_RATE_MOBIFONE'))  define('CARD_RATE_MOBIFONE', '30');
if (!defined('CARD_RATE_VINAPHONE')) define('CARD_RATE_VINAPHONE', '30');
if (!defined('DOITHE_API_URL'))            define('DOITHE_API_URL', 'https://doithe.vn/chargingws/v2'); // hardcode default — admin không cần sửa
if (!defined('DOITHE_PARTNER_ID'))         define('DOITHE_PARTNER_ID', '');
if (!defined('DOITHE_PARTNER_KEY'))        define('DOITHE_PARTNER_KEY', '');
// Callback secret: HMAC từ partner_key + BOT_TOKEN — dùng để xác thực URL callback
// trong trường hợp doithe.vn không gửi signature đầy đủ.
if (defined('DOITHE_PARTNER_KEY') && DOITHE_PARTNER_KEY !== '' && defined('BOT_TOKEN') && !defined('CARD_CALLBACK_SECRET')) {
    define('CARD_CALLBACK_SECRET', hash_hmac('sha256', DOITHE_PARTNER_KEY, BOT_TOKEN));
}
// Card poll secret: HMAC từ partner_id + BOT_TOKEN — auth cho cron /cron/card_poll.php.
// Tách khỏi CARD_CALLBACK_SECRET để rotate độc lập nếu cần.
if (defined('DOITHE_PARTNER_ID') && DOITHE_PARTNER_ID !== '' && defined('BOT_TOKEN') && !defined('CARD_POLL_SECRET')) {
    define('CARD_POLL_SECRET', hash_hmac('sha256', 'card_poll:' . DOITHE_PARTNER_ID, BOT_TOKEN));
}

// --- Timezone ---
date_default_timezone_set(APP_TIMEZONE);

// =============================================
// TELEGRAM MINI APP INIT-DATA VERIFICATION
// =============================================
function verifyTelegramInitData($initData) {
    if (!$initData || !is_string($initData)) return false;
    parse_str($initData, $data);
    if (empty($data['hash'])) return false;
    $hash = $data['hash'];
    unset($data['hash']);
    ksort($data);
    $pairs = [];
    foreach ($data as $k => $v) $pairs[] = $k . '=' . $v;
    $checkString = implode("\n", $pairs);
    $secret = hash_hmac('sha256', BOT_TOKEN, 'WebAppData', true);
    $calc = hash_hmac('sha256', $checkString, $secret);
    if (!hash_equals($calc, $hash)) return false;
    if (!empty($data['auth_date']) && time() - (int)$data['auth_date'] > 86400) return false;
    return $data;
}

function telegramUserFromInitData($initData) {
    $verified = verifyTelegramInitData($initData);
    if (!$verified || empty($verified['user'])) return null;
    $user = json_decode($verified['user'], true);
    return is_array($user) ? $user : null;
}

// =============================================
// KẾT NỐI DATABASE
// =============================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        if (!defined('DB_HOST') || !defined('DB_NAME')) {
            die(json_encode(['error' => 'Database config chưa thiết lập. Chạy install.php']));
        }
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB_CONNECT] ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// =============================================
// GỬI TELEGRAM
// =============================================
function sendTelegram($chat_id, $text, $reply_markup = null) {
    if (!defined('BOT_TOKEN') || BOT_TOKEN === '' || strpos(BOT_TOKEN, 'your_bot') === 0) {
        return ['ok' => false, 'error' => 'BOT_TOKEN chưa cấu hình'];
    }
    $data = [
        'chat_id'    => $chat_id,
        'text'       => $text,
        'parse_mode' => 'HTML',
    ];
    if ($reply_markup) $data['reply_markup'] = json_encode($reply_markup);

    $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/sendMessage');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $err    = curl_error($ch);
    curl_close($ch);
    if ($err) error_log('[TELEGRAM] ' . $err);
    return json_decode($result, true);
}

// =============================================
// HTTP REQUEST HELPER
// =============================================
function httpJsonRequest($url, $method = 'GET', $headers = [], $body = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 2,
    ]);
    if ($method !== 'GET')  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($headers)           curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body !== null)     curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($raw, true);
    return ['ok' => !$err && $code >= 200 && $code < 300, 'code' => $code, 'raw' => $raw, 'json' => $json, 'error' => $err];
}

function pickShortUrl($json, $raw = '') {
    if (is_array($json)) {
        foreach (['shorturl', 'short_url', 'shortenedUrl', 'shortened_url', 'url', 'link', 'short', 'result'] as $k) {
            if (!empty($json[$k]) && is_string($json[$k]) && preg_match('~^https?://~', $json[$k])) return $json[$k];
        }
        if (!empty($json['data']) && is_array($json['data'])) {
            $u = pickShortUrl($json['data']);
            if ($u) return $u;
        }
    }
    if ($raw && preg_match('~https?://[^\s"\']+~', $raw, $m)) return $m[0];
    return '';
}

function shortenLink4M($longUrl, &$debug = null) {
    $st = 'https://link4m.co/st?api=' . rawurlencode(LINK4M_API_TOKEN) . '&url=' . rawurlencode($longUrl);
    $debug = [];
    $res = httpJsonRequest($st);
    $raw = (string)$res['raw'];
    $debug[] = ['endpoint' => $st, 'code' => $res['code'], 'ok' => $res['ok'], 'short' => $st, 'raw' => substr($raw, 0, 220)];
    if ($res['code'] >= 200 && $res['code'] < 400 && stripos($raw, 'Vượt') !== false) return $st;

    foreach ([
        'https://link4m.co/api?api=' . rawurlencode(LINK4M_API_TOKEN) . '&url=' . rawurlencode($longUrl),
        'https://my.link4m.com/api?api=' . rawurlencode(LINK4M_API_TOKEN) . '&url=' . rawurlencode($longUrl),
    ] as $ep) {
        $res = httpJsonRequest($ep);
        $u = pickShortUrl($res['json'], '');
        if (!$u && is_string($res['raw']) && preg_match('~https?://[^\s"\'<>]+~', $res['raw'], $m)) $u = $m[0];
        $debug[] = ['endpoint' => $ep, 'code' => $res['code'], 'ok' => $res['ok'], 'short' => $u, 'raw' => substr((string)$res['raw'], 0, 220)];
        if ($u && $u !== $longUrl && preg_match('~^https?://([^/]+\.)?link4m\.co/~i', $u)) return $u;
    }
    return '';
}

function shortenLayma($longUrl, &$debug = null) {
    // Layma API: https://api.layma.net/api/admin/shortlink/quicklink
    $debug = [];
    $token = defined('LAYMA_API_TOKEN') ? LAYMA_API_TOKEN : '';
    if ($token === '') { $debug[] = ['error' => 'LAYMA_API_TOKEN chưa cấu hình']; return ''; }
    $ep = 'https://api.layma.net/api/admin/shortlink/quicklink?tokenUser=' . rawurlencode($token)
        . '&format=json&url=' . rawurlencode($longUrl);
    $res = httpJsonRequest($ep);
    $raw = (string)$res['raw'];
    $debug[] = ['endpoint' => $ep, 'code' => $res['code'], 'ok' => $res['ok'], 'raw' => substr($raw, 0, 220)];
    if (is_array($res['json']) && !empty($res['json']['success']) && !empty($res['json']['html'])) {
        return (string)$res['json']['html'];
    }
    return '';
}

function shortenYeuMoney($longUrl, &$debug = null) {
    // Legacy YeuMoney support - giữ lại cho backward compat
    $endpoints = [
        'https://yeumoney.com/QL_api.php?token=' . rawurlencode(YEUMONEY_API_TOKEN) . '&format=json&url=' . rawurlencode($longUrl),
        'https://yeumoney.com/api?api=' . rawurlencode(YEUMONEY_API_TOKEN) . '&url=' . rawurlencode($longUrl),
        'https://yeumoney.com/st?api=' . rawurlencode(YEUMONEY_API_TOKEN) . '&url=' . rawurlencode($longUrl),
    ];
    $debug = [];
    foreach ($endpoints as $ep) {
        $res = httpJsonRequest($ep);
        $u = pickShortUrl($res['json'], $res['raw']);
        $debug[] = ['endpoint' => $ep, 'code' => $res['code'], 'ok' => $res['ok'], 'short' => $u, 'raw' => substr((string)$res['raw'], 0, 220)];
        if ($u && $u !== $longUrl && preg_match('~yeumoney~i', $u)) return $u;
    }
    return '';
}

function buildFreeShortlink($claimUrl, &$debug = null) {
    // Layers: 1 = chỉ Layma, 2 = Layma + Link4M (vượt 2 lớp)
    $layers = defined('FREE_SHORTLINK_LAYERS') ? (int)FREE_SHORTLINK_LAYERS : 2;
    if ($layers < 1) $layers = 1;
    if ($layers > 2) $layers = 2;

    $debug = ['layers' => $layers];

    // Lớp 1: Layma
    $layer1 = shortenLayma($claimUrl, $laymaDebug);
    $debug['layma'] = $laymaDebug;
    if (!$layer1) throw new Exception('Layma API không tạo được link. Kiểm tra LAYMA_API_TOKEN.');

    if ($layers === 1) return $layer1;

    // Lớp 2: Link4M wrap link Layma — nếu fail thì fallback về Layma (graceful)
    $token = defined('LINK4M_API_TOKEN') ? LINK4M_API_TOKEN : '';
    if ($token === '' || strpos($token, 'your_') === 0) {
        // Chưa cấu hình Link4M → tự fallback xuống 1 lớp
        $debug['link4m'] = ['skipped' => 'LINK4M_API_TOKEN chưa cấu hình, fallback layma'];
        return $layer1;
    }
    $layer2 = shortenLink4M($layer1, $l4Debug);
    $debug['link4m'] = $l4Debug;
    if (!$layer2) {
        // Fail nhưng vẫn có Layma → dùng tạm Layma, không throw
        error_log('[buildFreeShortlink] Link4M fail, fallback Layma. Debug: ' . json_encode($l4Debug));
        return $layer1;
    }
    return $layer2;
}

// =============================================
// VIETQR URL BUILDER
// =============================================
function buildVietQrUrl($amount, $content) {
    $bank     = defined('VIETQR_BANK_ID') ? VIETQR_BANK_ID : '970422';
    $account  = BANK_ACCOUNT;
    $template = 'qr_only';
    $params   = [
        'amount'      => (int)$amount,
        'addInfo'     => $content,
        'accountName' => BANK_OWNER,
    ];
    return 'https://img.vietqr.io/image/' . rawurlencode($bank) . '-' . rawurlencode($account) . '-' . $template . '.png?' . http_build_query($params);
}

// =============================================
// HELPER UTILITIES
// =============================================
function generateKey() {
    $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $suffix = '';
    for ($i = 0; $i < 5; $i++) {
        $suffix .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'HCLOU' . $suffix;
}

function generateOrderCode() {
    return 'ORD' . date('ymd') . strtoupper(substr(uniqid(), -6));
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) return null;
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// HTML escape helper
function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// LIKE escape (cho tìm kiếm SQL)
function likeEscape($str) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string)$str);
}

// =============================================
// ADMIN CONFIG EDITOR HELPERS
// =============================================
function hclouConfigEditableKeys() {
    // Lưu ý: SECRET/PASS/HASH KHÔNG cho edit qua web để giảm rủi ro.
    // Riêng API key của bên thứ 3 (MBBank/Link4M/YeuMoney) cho phép sửa
    // qua web để admin tự xoay vòng mà không cần SSH — input sẽ mask trong UI,
    // log ghi '[hidden]' (xem hclouWriteConfigValues).
    return [
        'SITE_URL'                    => 'string',
        'SITE_NAME'                   => 'string',
        'ADMIN_CHAT_ID'               => 'string',
        'BOT_USERNAME'                => 'string',
        'BANK_NAME'                   => 'string',
        'BANK_ACCOUNT'                => 'string',
        'BANK_OWNER'                  => 'string',
        'VIETQR_BANK_ID'              => 'string',
        'MBBANK_AUTO_APPROVE_ENABLED' => 'bool',
        'FREE_GETKEY_ENABLED'         => 'bool',
        'FREE_SHORTLINK_LAYERS'       => 'string',
        'MBBANK_HISTORY_API_KEY'      => 'string',
        'LAYMA_API_TOKEN'             => 'string',
        'LINK4M_API_TOKEN'            => 'string',
        'YEUMONEY_API_TOKEN'          => 'string',
        // --- Binance USDT TRC20 ---
        'CRYPTO_AUTO_APPROVE_ENABLED' => 'bool',
        'USDT_TRC20_ADDRESS'          => 'string',
        'TRONGRID_API_KEY'            => 'string',
        // --- Card top-up qua doithe.vn (auto API) ---
        // Rate per-telco theo % chiết khấu thực tế của doithe.vn (admin xem dashboard merchant).
        'CARD_RATE_VIETTEL'           => 'string',
        'CARD_RATE_MOBIFONE'          => 'string',
        'CARD_RATE_VINAPHONE'         => 'string',
        'DOITHE_PARTNER_ID'           => 'string',
        'DOITHE_PARTNER_KEY'          => 'string',
    ];
}

function hclouConfigValue($key) { return defined($key) ? constant($key) : null; }

function hclouWriteConfigValues(array $updates, $admin = 'web_admin') {
    $allowed    = hclouConfigEditableKeys();
    $configFile = APP_ROOT . '/config.local.php';
    if (!file_exists($configFile)) throw new Exception('config.local.php không tồn tại');
    $src     = file_get_contents($configFile);
    $changed = [];

    foreach ($updates as $key => $val) {
        if (!isset($allowed[$key])) continue;
        $type = $allowed[$key];
        $old  = hclouConfigValue($key);

        if ($type === 'bool') {
            $newVal = !empty($val) && !in_array(strtolower((string)$val), ['0', 'false', 'off', 'no'], true);
            $replacement = "define('{$key}', " . ($newVal ? 'true' : 'false') . ");";
        } else {
            $newVal = trim((string)$val);
            $replacement = "define('{$key}', " . var_export($newVal, true) . ");";
        }

        if ((string)$old === (string)$newVal) continue;
        $pattern = "/define\\('" . preg_quote($key, '/') . "'\\s*,\\s*.*?\\);/";
        $count   = 0;
        $src     = preg_replace($pattern, $replacement, $src, 1, $count);
        if ($count !== 1) throw new Exception("Không tìm thấy config {$key} trong config.local.php");
        $changed[$key] = ['old' => $old, 'new' => $newVal];
    }

    if (!$changed) return [];

    // Backup config.local.php trước khi ghi
    $backup = $configFile . '.bk_admincfg_' . date('Ymd_His');
    copy($configFile, $backup);
    if (file_put_contents($configFile, $src, LOCK_EX) === false) {
        throw new Exception('Không ghi được config.local.php');
    }

    // Log vào DB
    try {
        $db = getDB();
        ensureAdminConfigLogTable($db);
        $stmt = $db->prepare('INSERT INTO admin_config_logs (admin, config_key, old_value, new_value, created_at) VALUES (?,?,?,?,NOW())');
        foreach ($changed as $k => $v) {
            $mask = preg_match('/TOKEN|API|SECRET|PASS|HASH/i', $k);
            $stmt->execute([$admin, $k, $mask ? '[hidden]' : (string)$v['old'], $mask ? '[hidden]' : (string)$v['new']]);
        }
    } catch (Throwable $e) {
        // config đã ghi thành công, không fail UI
        error_log('[ADMIN_CFG_LOG] ' . $e->getMessage());
    }
    return $changed;
}

function ensureAdminConfigLogTable(PDO $db) {
    $db->exec("CREATE TABLE IF NOT EXISTS admin_config_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin VARCHAR(100) NOT NULL,
        config_key VARCHAR(100) NOT NULL,
        old_value TEXT NULL,
        new_value TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_config_logs_created (created_at),
        INDEX idx_config_logs_key (config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// =============================================
// RATE LIMITER (file-based, fallback nếu chưa có Redis)
// =============================================
// =============================================
// CSRF PROTECTION
// =============================================
function csrfToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

function csrfVerify($token = null) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!$sessionToken || !$token) return false;
    return hash_equals($sessionToken, $token);
}

function csrfRequire() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    // Origin check (defense-in-depth)
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    $siteHost = parse_url(SITE_URL, PHP_URL_HOST);
    if ($origin && $siteHost) {
        $originHost = parse_url($origin, PHP_URL_HOST);
        if ($originHost && $originHost !== $siteHost) {
            http_response_code(403);
            exit('CSRF: Origin không hợp lệ');
        }
    }
    if (!csrfVerify()) {
        http_response_code(403);
        exit('CSRF: Token không hợp lệ. Vui lòng tải lại trang.');
    }
}

// =============================================
// LOGIN RATE LIMITER (chống brute force)
// =============================================
function loginAttemptCheck($scope = 'admin_login', $maxAttempts = 5, $windowSeconds = 900) {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir  = APP_ROOT . '/data/login_attempts';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . preg_replace('/[^a-z0-9_]/i', '_', $scope . '_' . hash('sha256', $ip)) . '.json';
    $now  = time();
    $data = ['start' => $now, 'count' => 0];
    if (is_file($file)) {
        $old = json_decode((string)@file_get_contents($file), true);
        if (is_array($old) && !empty($old['start']) && ($now - (int)$old['start']) < $windowSeconds) {
            $data = $old;
        }
    }
    if (($now - (int)$data['start']) >= $windowSeconds) $data = ['start' => $now, 'count' => 0];
    return ['data' => $data, 'file' => $file, 'blocked' => (int)$data['count'] >= $maxAttempts, 'remaining' => max(0, $maxAttempts - (int)$data['count']), 'unblock_at' => (int)$data['start'] + $windowSeconds];
}

function loginAttemptIncrement($scope = 'admin_login', $windowSeconds = 900) {
    $info = loginAttemptCheck($scope);
    $info['data']['count'] = (int)$info['data']['count'] + 1;
    @file_put_contents($info['file'], json_encode($info['data']), LOCK_EX);
}

function loginAttemptReset($scope = 'admin_login') {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir  = APP_ROOT . '/data/login_attempts';
    $file = $dir . '/' . preg_replace('/[^a-z0-9_]/i', '_', $scope . '_' . hash('sha256', $ip)) . '.json';
    @unlink($file);
}

// =============================================
// STRUCTURED LOGGING
// =============================================
function hclouLog($level, $message, array $context = []) {
    $logFile = APP_ROOT . '/data/app.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $entry = [
        'ts'      => date('c'),
        'level'   => strtoupper($level),
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        'message' => $message,
        'context' => $context,
    ];
    @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Convenience wrappers
function logInfo($msg, $ctx = [])  { hclouLog('info',  $msg, $ctx); }
function logWarn($msg, $ctx = [])  { hclouLog('warn',  $msg, $ctx); }
function logError($msg, $ctx = []) { hclouLog('error', $msg, $ctx); }

// =============================================
// RATE LIMITER
// =============================================
function rateLimit($scope, $limit, $windowSeconds, $identity = null) {
    $identity = $identity ?: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $dir      = APP_ROOT . '/data/ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $scope . '_' . hash('sha256', $identity)) . '.json';
    $now  = time();
    $data = ['start' => $now, 'count' => 0];

    if (is_file($file)) {
        $old = json_decode((string)@file_get_contents($file), true);
        if (is_array($old) && !empty($old['start']) && ($now - (int)$old['start']) < $windowSeconds) $data = $old;
    }
    if (($now - (int)$data['start']) >= $windowSeconds) $data = ['start' => $now, 'count' => 0];
    $data['count'] = (int)$data['count'] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > $limit) {
        jsonResponse(['error' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau.'], 429);
    }
}
