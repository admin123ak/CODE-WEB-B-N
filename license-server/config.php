<?php
// =============================================
// LICENSE SERVER - CORE CONFIG + HELPERS
// =============================================
if (!defined('LS_ROOT')) define('LS_ROOT', __DIR__);

$__ls_local = LS_ROOT . '/config.local.php';
if (file_exists($__ls_local)) {
    require_once $__ls_local;
} else {
    $isInstaller = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'install.php');
    if (!$isInstaller) {
        if (php_sapi_name() === 'cli') { fwrite(STDERR, "config.local.php chưa tồn tại. Chạy install.php\n"); exit(1); }
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        header('Location: ' . $base . '/install.php'); exit;
    }
}

if (!defined('LS_ADMIN_SESSION_TTL')) define('LS_ADMIN_SESSION_TTL', 3600);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// --- DB ---
function lsDB() {
    static $pdo = null;
    if ($pdo === null) {
        if (!defined('LS_DB_HOST')) { die('DB config chưa thiết lập. Chạy install.php'); }
        try {
            $pdo = new PDO(
                'mysql:host=' . LS_DB_HOST . ';dbname=' . LS_DB_NAME . ';charset=utf8mb4',
                LS_DB_USER, LS_DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $e) {
            error_log('[LS_DB] ' . $e->getMessage());
            die('Database connection failed');
        }
    }
    return $pdo;
}

// --- HTML escape ---
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// --- JSON response (API) ---
function lsJson($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- HMAC ký payload (server → client verify) ---
function lsSign(array $payload) {
    ksort($payload);
    $base = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return hash_hmac('sha256', $base, LS_SIGNING_SECRET);
}

// --- Tạo license key dạng HCLOU-XXXX-XXXX-XXXX ---
function lsGenKey() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $seg = function () use ($chars) {
        $s = '';
        for ($i = 0; $i < 4; $i++) $s .= $chars[random_int(0, strlen($chars) - 1)];
        return $s;
    };
    return 'HCLOU-' . $seg() . '-' . $seg() . '-' . $seg();
}

// =============================================
// CSRF
// =============================================
function lsCsrf() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['ls_csrf'])) $_SESSION['ls_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['ls_csrf'];
}
function lsCsrfOk($t = null) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $t = $t ?? ($_POST['csrf'] ?? '');
    return !empty($_SESSION['ls_csrf']) && hash_equals($_SESSION['ls_csrf'], (string)$t);
}

// =============================================
// LOGIN RATE LIMIT (chống brute force)
// =============================================
function lsLoginCheck($max = 5, $win = 900) {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = LS_ROOT . '/data/login_attempts';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $now  = time();
    $data = ['start' => $now, 'count' => 0];
    if (is_file($file)) {
        $old = json_decode((string)@file_get_contents($file), true);
        if (is_array($old) && !empty($old['start']) && ($now - (int)$old['start']) < $win) $data = $old;
    }
    if (($now - (int)$data['start']) >= $win) $data = ['start' => $now, 'count' => 0];
    return ['data' => $data, 'file' => $file, 'blocked' => (int)$data['count'] >= $max,
            'remaining' => max(0, $max - (int)$data['count']), 'unblock_at' => (int)$data['start'] + $win];
}
function lsLoginInc($win = 900) {
    $i = lsLoginCheck(5, $win);
    $i['data']['count'] = (int)$i['data']['count'] + 1;
    @file_put_contents($i['file'], json_encode($i['data']), LOCK_EX);
}
function lsLoginReset() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    @unlink(LS_ROOT . '/data/login_attempts/' . hash('sha256', $ip) . '.json');
}

// --- Admin auth check (dùng trong index.php) ---
function lsRequireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $ok = !empty($_SESSION['ls_auth']) && !empty($_SESSION['ls_last'])
        && (time() - (int)$_SESSION['ls_last'] <= LS_ADMIN_SESSION_TTL);
    if (!$ok) { unset($_SESSION['ls_auth'], $_SESSION['ls_last']); return false; }
    $_SESSION['ls_last'] = time();
    return true;
}
