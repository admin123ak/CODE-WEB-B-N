<?php
/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
require_once '../config.php';
// License lock lớp 4
if (!defined('HCLOU_LICENSE_OK')) { http_response_code(403); die('License required — liên hệ Zalo 0868641019'); }
session_start();

// Admin auth: session + CSRF + timeout
function admin_login_page($error = '') {
    $csrf = $_SESSION['admin_csrf'] ?? bin2hex(random_bytes(16));
    $_SESSION['admin_csrf'] = $csrf;
    $err = $error ? '<div class="err">'.htmlspecialchars($error).'</div>' : '';
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HCLOU SERVER Admin</title>
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@700;800&display=swap">
        <style>*{margin:0;padding:0;box-sizing:border-box}
        @keyframes lxIn{from{opacity:0;transform:translateY(14px) scale(.98)}to{opacity:1;transform:none}}
        @keyframes lxFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
        body{min-height:100vh;font-family:"Inter",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#eef3fb;display:flex;align-items:center;justify-content:center;padding:20px;overflow:hidden;-webkit-font-smoothing:antialiased;
          background:radial-gradient(1000px 600px at 78% -10%,rgba(99,102,241,.22),transparent 58%),radial-gradient(820px 520px at 10% 12%,rgba(34,211,238,.14),transparent 55%),radial-gradient(680px 680px at 92% 100%,rgba(167,139,250,.14),transparent 60%),linear-gradient(180deg,#070a12,#0a0f1c 50%,#070a12)}
        body:before{content:"";position:fixed;inset:0;background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);background-size:42px 42px;mask:radial-gradient(800px 600px at 50% 40%,#000,transparent);pointer-events:none}
        .card{position:relative;width:420px;max-width:100%;padding:34px 30px 26px;border-radius:26px;animation:lxIn .55s cubic-bezier(.4,0,.2,1) both;
          background:linear-gradient(180deg,rgba(20,28,46,.92),rgba(10,15,28,.96));border:1px solid rgba(130,165,220,.22);
          box-shadow:0 34px 100px -22px rgba(0,0,0,.85),0 0 0 1px rgba(255,255,255,.04),inset 0 1px 0 rgba(255,255,255,.06);backdrop-filter:blur(26px)}
        .card:before{content:"";position:absolute;inset:0;border-radius:inherit;padding:1px;background:linear-gradient(140deg,rgba(255,255,255,.18),transparent 42%);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:.8}
        .logo{width:72px;height:72px;border-radius:22px;background:linear-gradient(135deg,#6366f1,#22d3ee);display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 18px;box-shadow:0 0 40px rgba(99,102,241,.55),inset 0 1px 0 rgba(255,255,255,.25);animation:lxFloat 4s ease-in-out infinite}
        h1{font-family:"Plus Jakarta Sans",sans-serif;text-align:center;font-size:25px;font-weight:800;letter-spacing:-.02em;margin-bottom:6px;background:linear-gradient(135deg,#fff,#c7d2fe);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .sub{text-align:center;color:#8ba0c4;font-size:12.5px;letter-spacing:.04em;margin-bottom:26px}
        .field{margin-bottom:14px}
        label{display:block;color:#9fb4d6;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;margin:0 0 8px 2px}
        input{width:100%;height:50px;padding:0 16px;background:rgba(7,11,20,.8);border:1px solid rgba(130,165,220,.22);border-radius:14px;color:#eef3fb;font-size:15px;outline:none;font-family:inherit;transition:.2s}
        input:focus{border-color:#3ed6e0;box-shadow:0 0 0 4px rgba(62,214,224,.14),0 10px 30px -12px rgba(62,214,224,.5);background:rgba(7,11,20,.95)}
        input::placeholder{color:#5d6f8e}
        button{width:100%;height:50px;margin-top:6px;border:none;border-radius:14px;background:linear-gradient(135deg,#6366f1,#22d3ee);color:#fff;font-size:15px;font-weight:800;letter-spacing:.02em;cursor:pointer;box-shadow:0 16px 38px -12px rgba(99,102,241,.65);transition:.2s}
        button:hover{filter:brightness(1.1);transform:translateY(-1.5px);box-shadow:0 22px 48px -14px rgba(99,102,241,.8)}
        button:active{transform:scale(.98)}
        .hint{margin-top:18px;text-align:center;color:#6b7d9c;font-size:11.5px;line-height:1.5}
        .err{background:linear-gradient(135deg,rgba(248,113,113,.14),rgba(248,113,113,.05));border:1px solid rgba(248,113,113,.34);color:#fca5a5;padding:11px 14px;border-radius:13px;margin-bottom:15px;font-size:13px;font-weight:600}
        .admin-footer{margin:26px 0 4px;text-align:center;color:rgba(139,160,196,.42);font-size:11px;font-weight:600;letter-spacing:.05em}.admin-footer:before{content:"";display:block;width:130px;height:1px;background:linear-gradient(90deg,transparent,rgba(232,200,121,.3),transparent);margin:0 auto 12px}</style></head>
        <body><form class="card" method="POST"><div class="logo">⚡</div><h1>HCLOU SERVER</h1><div class="sub">Admin Control Center · Secure Login</div>'.$err.'<input type="hidden" name="csrf" value="'.$csrf.'"><div class="field"><label>Tài khoản</label><input type="text" name="username" placeholder="Nhập tài khoản admin" autocomplete="username" autocapitalize="none" autofocus></div><div class="field"><label>Mật khẩu</label><input type="password" name="pw" placeholder="Nhập mật khẩu admin" autocomplete="current-password"></div><button>🔐 Đăng nhập an toàn</button><div class="hint">Session tự hết hạn sau '.(int)(ADMIN_SESSION_TTL/60).' phút không hoạt động</div></form></body></html>';
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: ?logged_out=1'); exit;
}

$loggedIn = !empty($_SESSION['admin_auth']) && !empty($_SESSION['admin_last_seen']) && (time() - $_SESSION['admin_last_seen'] <= ADMIN_SESSION_TTL);
if (!$loggedIn) {
    unset($_SESSION['admin_auth'], $_SESSION['admin_last_seen']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Rate limit: max 5 attempts / 15 min per IP
        $attempt = loginAttemptCheck('admin_login', 5, 900);
        if ($attempt['blocked']) {
            $remain = max(0, $attempt['unblock_at'] - time());
            admin_login_page('Quá nhiều lần thử sai. Vui lòng đợi ' . ceil($remain / 60) . ' phút.');
            exit;
        }
        $csrfOk = hash_equals($_SESSION['admin_csrf'] ?? '', $_POST['csrf'] ?? '');
        if (!$csrfOk) { admin_login_page('Phiên đăng nhập không hợp lệ, thử lại.'); exit; }
        // Xác thực: tài khoản (username) + mật khẩu. Username so khớp không phân biệt hoa thường.
        $adminUser = defined('ADMIN_USERNAME') ? ADMIN_USERNAME : 'admin';
        $userOk    = hash_equals(strtolower($adminUser), strtolower(trim($_POST['username'] ?? '')));
        $passOk    = password_verify($_POST['pw'] ?? '', ADMIN_PASSWORD_HASH);
        if ($userOk && $passOk) {
            loginAttemptReset('admin_login');
            session_regenerate_id(true);
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_last_seen'] = time();
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
            logInfo('Admin login OK', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'user' => $adminUser]);
            header('Location: ?tab=dashboard'); exit;
        }
        loginAttemptIncrement('admin_login', 900);
        logWarn('Admin login failed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'remaining' => $attempt['remaining'] - 1]);
        admin_login_page('Sai tài khoản hoặc mật khẩu. Còn ' . max(0, $attempt['remaining'] - 1) . ' lượt thử.'); exit;
    }
    admin_login_page(); exit;
}
$_SESSION['admin_last_seen'] = time();
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));

$db = getDB();
$tab = $_GET['tab'] ?? 'dashboard';

/**
 * Xử lý upload icon game.
 * Trả về URL tương đối nếu upload OK, '' nếu không có file hoặc fail.
 */
function handleGameIconUpload() {
    if (empty($_FILES['icon']) || $_FILES['icon']['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($_FILES['icon']['error'] !== UPLOAD_ERR_OK) return '';
    $file = $_FILES['icon'];
    if ($file['size'] > 2 * 1024 * 1024) return ''; // max 2MB

    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    // SVG bị loại vì có thể chứa <script> → XSS khi user mở trực tiếp URL SVG (same-origin).
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) return '';

    $ext = $allowed[$mime];
    $uploadDir = APP_ROOT . '/uploads/games';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
    $filename = 'game_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) return '';
    return rtrim(SITE_URL, '/') . '/uploads/games/' . $filename;
}

function hclouMaskSecret($value, $left = 8, $right = 4) {
    $value = (string)$value;
    $len = strlen($value);
    if ($value === '') return '';
    if ($len <= $left + $right + 3) return str_repeat('•', min($len, 12));
    return substr($value, 0, $left) . '…' . substr($value, -$right);
}
function hclouCronRunToken() {
    return defined('CRON_RUN_TOKEN') ? (string)CRON_RUN_TOKEN : '';
}
function hclouCronRunUrl($job, $masked = false) {
    $token = hclouCronRunToken();
    $show = $masked ? hclouMaskSecret($token) : $token;
    return rtrim(SITE_URL, '/') . '/cron/run.php?token=' . $show . '&job=' . rawurlencode($job);
}

// Kiểm tra packages đã có cột API chưa (DB đã chạy fix_db.php?) — cache trong request
function pkgHasApiCols($db){
    static $has = null;
    if ($has !== null) return $has;
    try {
        $q = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='packages' AND COLUMN_NAME='key_source'");
        $has = ((int)$q->fetchColumn()) > 0;
    } catch (Throwable $e) { $has = false; }
    return $has;
}

// Xử lý action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['admin_csrf'] ?? '', $_POST['csrf'] ?? '')) { header('Location: ?err=' . urlencode('CSRF token không hợp lệ')); exit; }
    $act = $_POST['act'] ?? '';

    // Test API panel với URL+token tạm (không cần lưu config trước)
    if ($act === 'test_hclou_api') {
        header('Content-Type: application/json; charset=utf-8');
        $url = rtrim(trim($_POST['api_url'] ?? ''), '/');
        $tok = trim($_POST['api_token'] ?? '');
        if ($url === '' || $tok === '') { echo json_encode(['ok'=>false,'msg'=>'Thiếu URL hoặc token']); exit; }
        $ch = curl_init($url . '/products');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tok, 'Accept: application/json'],
        ]);
        $raw = curl_exec($ch); $err = curl_error($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($raw === false) { echo json_encode(['ok'=>false,'msg'=>'cURL: '.$err,'http'=>$code]); exit; }
        $j = json_decode($raw, true);
        if (!is_array($j)) { echo json_encode(['ok'=>false,'msg'=>'Phản hồi không phải JSON','http'=>$code]); exit; }
        if (empty($j['status'])) { echo json_encode(['ok'=>false,'msg'=>'Token/URL sai: '.($j['reason'] ?? json_encode($j)),'http'=>$code]); exit; }
        echo json_encode(['ok'=>true,'balance'=>$j['balance'] ?? 0,'games'=>$j['games'] ?? []]); exit;
    }

    if ($act === 'save_config') {
        try {
            $oldLayers = defined('FREE_SHORTLINK_LAYERS') ? (string)FREE_SHORTLINK_LAYERS : '2';
            $changes = hclouWriteConfigValues($_POST['cfg'] ?? [], $_SESSION['admin_name'] ?? 'web_admin');
            // Nếu đổi số lớp vượt link (hoặc token) → xoá cache link cũ để lần sau tạo lại theo cấu hình mới
            $newLayers = (string)($_POST['cfg']['FREE_SHORTLINK_LAYERS'] ?? $oldLayers);
            $tokenChanged = isset($_POST['cfg']['LINK4M_API_TOKEN']) || isset($_POST['cfg']['LAYMA_API_TOKEN']);
            if ($newLayers !== $oldLayers || $tokenChanged) {
                try {
                    $db->exec("UPDATE free_keys SET short_url=NULL");
                    $db->exec("UPDATE free_key_claims SET short_url=NULL WHERE is_claimed=0");
                } catch (Exception $ce) { /* bảng có thể chưa có cột, bỏ qua */ }
            }
            header("Location: ?tab=sysconfig&ok=1&changed=" . count($changes)); exit;
        } catch (Exception $e) { header("Location: ?tab=sysconfig&err=" . urlencode($e->getMessage())); exit; }
    }
    if ($act === 'change_admin_cred') {
        try {
            $curPw   = $_POST['cur_pw'] ?? '';
            $newUser = trim($_POST['new_username'] ?? '');
            $newPw   = $_POST['new_pw'] ?? '';
            $newPw2  = $_POST['new_pw2'] ?? '';
            // Bắt buộc nhập đúng mật khẩu hiện tại để đổi
            if (!password_verify($curPw, ADMIN_PASSWORD_HASH)) {
                header("Location: ?tab=sysconfig&err=" . urlencode('Mật khẩu hiện tại không đúng')); exit;
            }
            if ($newUser === '' || !preg_match('/^[a-zA-Z0-9_.\-]{3,32}$/', $newUser)) {
                header("Location: ?tab=sysconfig&err=" . urlencode('Tài khoản 3-32 ký tự (chữ/số/._-)')); exit;
            }
            hclouWriteRawDefine('ADMIN_USERNAME', $newUser);
            // Chỉ đổi mật khẩu nếu có nhập mới
            if ($newPw !== '') {
                if (strlen($newPw) < 6) { header("Location: ?tab=sysconfig&err=" . urlencode('Mật khẩu mới tối thiểu 6 ký tự')); exit; }
                if ($newPw !== $newPw2) { header("Location: ?tab=sysconfig&err=" . urlencode('Mật khẩu nhập lại không khớp')); exit; }
                hclouWriteRawDefine('ADMIN_PASSWORD_HASH', password_hash($newPw, PASSWORD_DEFAULT));
            }
            logInfo('Admin credentials changed', ['new_user' => $newUser, 'pw_changed' => $newPw !== '']);
            header("Location: ?tab=sysconfig&msg=" . urlencode('Đã đổi đăng nhập admin. Lần sau dùng tài khoản: ' . $newUser)); exit;
        } catch (Exception $e) { header("Location: ?tab=sysconfig&err=" . urlencode($e->getMessage())); exit; }
    }
    if ($act === 'run_maintenance') {
        try {
            require_once __DIR__ . '/../cron/maintenance.php';
            $r = runMaintenance($db);
            header("Location: ?tab=sysconfig&ok=1&maint=" . urlencode(json_encode($r, JSON_UNESCAPED_UNICODE))); exit;
        } catch (Exception $e) { header("Location: ?tab=sysconfig&err=" . urlencode($e->getMessage())); exit; }
    }

    if ($act === 'db_backup_now') {
        try {
            if (!defined('CRON_RUN_TOKEN') || CRON_RUN_TOKEN === '') {
                throw new Exception('CRON_RUN_TOKEN chưa configured.');
            }
            $url = rtrim(SITE_URL, '/') . '/cron/db_backup.php?cron_token=' . rawurlencode(CRON_RUN_TOKEN);
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            if ($body === false) throw new Exception('cURL: ' . $err);
            $j = json_decode((string)$body, true);
            if (!is_array($j))          throw new Exception('HTTP ' . $code . ' - response không phải JSON');
            if (empty($j['success']))   throw new Exception($j['error'] ?? ('HTTP ' . $code));
            header("Location: ?tab=sysconfig&ok=1&backup=" . urlencode($j['file'] ?? '')); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode('Backup: ' . $e->getMessage())); exit;
        }
    }

    if ($act === 'db_backup_delete') {
        $f = $_POST['file'] ?? '';
        if (preg_match('/^db_\d{8}_\d{6}\.sql\.gz$/', $f)) {
            $path = APP_ROOT . '/data/backups/' . $f;
            $real    = realpath($path);
            $dirReal = realpath(APP_ROOT . '/data/backups');
            if ($real && $dirReal && strpos($real, $dirReal . DIRECTORY_SEPARATOR) === 0 && is_file($real)) {
                @unlink($real);
                logInfo('Admin deleted DB backup', ['file' => $f]);
            }
        }
        header("Location: ?tab=sysconfig&ok=1"); exit;
    }

    if ($act === 'reset_webhook') {
        // Re-set Telegram webhook với SITE_URL hiện tại + TELEGRAM_WEBHOOK_SECRET.
        // Dùng sau khi cài lại code hoặc đổi domain.
        try {
            if (!defined('BOT_TOKEN') || BOT_TOKEN === '' || strpos(BOT_TOKEN, 'your_bot') === 0) {
                throw new Exception('BOT_TOKEN chưa cấu hình');
            }
            $whUrl = rtrim(SITE_URL, '/') . '/webhook.php';
            $secret = defined('TELEGRAM_WEBHOOK_SECRET') ? TELEGRAM_WEBHOOK_SECRET : '';
            $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/setWebhook');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => http_build_query([
                    'url' => $whUrl,
                    'secret_token' => $secret,
                    'allowed_updates' => json_encode(['message', 'callback_query']),
                ]),
            ]);
            $res = curl_exec($ch); curl_close($ch);
            $j = json_decode($res, true);
            if (empty($j['ok'])) throw new Exception('Telegram: ' . ($j['description'] ?? 'unknown'));
            header("Location: ?tab=sysconfig&ok=1&wh=" . urlencode('Set webhook OK: ' . $whUrl)); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode('Reset webhook: ' . $e->getMessage())); exit;
        }
    }

    if ($act === 'test_telegram') {
        // Gửi tin nhắn test tới ADMIN_CHAT_ID để verify BOT_TOKEN + sendTelegram hoạt động.
        try {
            if (!defined('ADMIN_CHAT_ID') || ADMIN_CHAT_ID === '') throw new Exception('ADMIN_CHAT_ID chưa cấu hình');
            $r = sendTelegram(ADMIN_CHAT_ID, "✅ Test từ admin panel — " . date('Y-m-d H:i:s'));
            if (empty($r['ok'])) throw new Exception($r['description'] ?? ($r['error'] ?? 'Telegram trả lỗi'));
            header("Location: ?tab=sysconfig&ok=1&wh=" . urlencode('Đã gửi test tới Telegram admin')); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode('Test Telegram: ' . $e->getMessage())); exit;
        }
    }

    if ($act === 'mbbank_test_poll') {
        // Gọi cron/mbbank_poll.php cùng-host qua cURL với MBBANK_POLL_SECRET.
        // Tránh require trực tiếp để giữ nguyên cơ chế lock + môi trường giống cron.
        try {
            if (!defined('MBBANK_POLL_SECRET') || MBBANK_POLL_SECRET === '') {
                throw new Exception('MBBANK_POLL_SECRET chưa khả dụng (kiểm tra MBBANK_HISTORY_API_KEY + BOT_TOKEN).');
            }
            $url = rtrim(SITE_URL, '/') . '/cron/mbbank_poll.php?src=admin&secret=' . rawurlencode(MBBANK_POLL_SECRET);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            if ($body === false) throw new Exception('cURL: ' . $err);
            $j = json_decode((string)$body, true);
            if (!is_array($j)) throw new Exception('HTTP ' . $code . ' - response không phải JSON: ' . substr((string)$body, 0, 200));
            if (empty($j['success'])) throw new Exception($j['error'] ?? ('HTTP ' . $code));
            header("Location: ?tab=sysconfig&ok=1&mbtest=" . urlencode(json_encode($j, JSON_UNESCAPED_UNICODE))); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode('MBBank test: ' . $e->getMessage())); exit;
        }
    }

    if ($act === 'crypto_test_poll') {
        // Gọi cron/crypto_poll.php cùng-host qua cURL với CRYPTO_POLL_SECRET.
        try {
            if (!defined('CRYPTO_POLL_SECRET') || CRYPTO_POLL_SECRET === '') {
                throw new Exception('CRYPTO_POLL_SECRET chưa khả dụng (cần nhập USDT_TRC20_ADDRESS + BOT_TOKEN).');
            }
            $url = rtrim(SITE_URL, '/') . '/cron/crypto_poll.php?src=admin&secret=' . rawurlencode(CRYPTO_POLL_SECRET);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            if ($body === false) throw new Exception('cURL: ' . $err);
            $j = json_decode((string)$body, true);
            if (!is_array($j)) throw new Exception('HTTP ' . $code . ' - response không phải JSON: ' . substr((string)$body, 0, 200));
            if (empty($j['success'])) throw new Exception($j['error'] ?? ('HTTP ' . $code));
            header("Location: ?tab=sysconfig&ok=1&crtest=" . urlencode(json_encode($j, JSON_UNESCAPED_UNICODE))); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode('Crypto test: ' . $e->getMessage())); exit;
        }
    }

    if ($act === 'toggle_payment') {
        // 1-click bật/tắt 1 phương thức thanh toán (MBBank hoặc Binance).
        // Không đổi gì khác — chỉ flip bool tương ứng và ghi log qua hclouWriteConfigValues.
        try {
            $method = $_POST['method'] ?? '';
            if ($method === 'mbbank') {
                $cur = defined('MBBANK_AUTO_APPROVE_ENABLED') ? (bool)MBBANK_AUTO_APPROVE_ENABLED : false;
                hclouWriteConfigValues(['MBBANK_AUTO_APPROVE_ENABLED' => $cur ? '0' : '1'], $_SESSION['admin_name'] ?? 'web_admin');
            } elseif ($method === 'binance') {
                $cur = defined('CRYPTO_AUTO_APPROVE_ENABLED') ? (bool)CRYPTO_AUTO_APPROVE_ENABLED : false;
                if (!$cur) {
                    if (!defined('USDT_TRC20_ADDRESS') || USDT_TRC20_ADDRESS === '') {
                        throw new Exception('Chưa nhập USDT_TRC20_ADDRESS. Vào panel "Binance USDT" bên dưới điền địa chỉ ví trước.');
                    }
                }
                hclouWriteConfigValues(['CRYPTO_AUTO_APPROVE_ENABLED' => $cur ? '0' : '1'], $_SESSION['admin_name'] ?? 'web_admin');
            } else {
                throw new Exception('Method không hợp lệ');
            }
            header("Location: ?tab=sysconfig&ok=1"); exit;
        } catch (Exception $e) {
            header("Location: ?tab=sysconfig&err=" . urlencode($e->getMessage())); exit;
        }
    }

    if ($act === 'add_free_key') {
        $game_id    = (int)$_POST['game_id'];
        $package_id = (int)$_POST['package_id'];
        $keysRaw    = trim($_POST['key_codes'] ?? $_POST['key_code'] ?? '');

        $pkg = $db->prepare("SELECT * FROM packages WHERE id=? AND game_id=?");
        $pkg->execute([$package_id, $game_id]);
        $p = $pkg->fetch();
        if (!$p || $keysRaw === '') { header("Location: ?tab=freekeys&err=missing"); exit; }

        $lines = preg_split('/[\r\n,;\s]+/', $keysRaw, -1, PREG_SPLIT_NO_EMPTY);
        $start = date('Y-m-d H:i:s');
        $exp   = date('Y-m-d H:i:s', strtotime("+{$p['days']} days"));

        $ins = $db->prepare("INSERT INTO free_keys (key_code,game_id,package_id,days,key_type,is_active,start_at,expire_at,claim_token,short_url) VALUES (?,?,?,?,?,1,?,?,?,?)");
        $added = 0; $errs = []; $linkFail = 0;
        foreach ($lines as $code) {
            $code = trim($code);
            if ($code === '') continue;
            try {
                $token = bin2hex(random_bytes(24));
                // Tự tạo link rút gọn ngay khi thêm vào pool (theo số lớp đang cấu hình)
                $short = null;
                try { $short = buildFreeShortlink(SITE_URL . '/getkey.php?t=' . urlencode($token)); }
                catch (Exception $le) { $linkFail++; }
                $ins->execute([$code, $game_id, $package_id, $p['days'], $p['key_type'], $start, $exp, $token, $short]);
                $added++;
            } catch (Exception $e) {
                $errs[] = $code . ': ' . substr($e->getMessage(), 0, 60);
            }
        }
        if ($linkFail > 0) $errs[] = "{$linkFail} key chưa tạo được link (bấm 'Tạo lại link') — kiểm tra token Link4M/Layma";
        if ($added === 0) {
            header("Location: ?tab=freekeys&err=" . urlencode('Không thêm được key nào: ' . implode(' | ', $errs))); exit;
        }
        $note = "&added={$added}" . ($errs ? "&errs=" . urlencode(implode(' | ', array_slice($errs, 0, 3))) : "");
        header("Location: ?tab=freekeys&ok=1{$note}"); exit;
    }
    if ($act === 'toggle_free_key') {
        $db->prepare("UPDATE free_keys SET is_active=1-is_active WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=freekeys&ok=1"); exit;
    }
    if ($act === 'regen_free_link') {
        $stmt=$db->prepare("SELECT * FROM free_keys WHERE id=?"); $stmt->execute([$_POST['id']]); $fk=$stmt->fetch();
        if ($fk) {
            try {
                $dbg = null;
                $short=buildFreeShortlink(SITE_URL.'/getkey.php?t='.$fk['claim_token'], $dbg);
                $db->prepare("UPDATE free_keys SET short_url=? WHERE id=?")->execute([$short,$fk['id']]);
                // Báo rõ link dùng API nào để admin biết Link4M có chạy không
                $api = (stripos($short,'link4m')!==false) ? 'Link4M' : ((stripos($short,'layma')!==false) ? 'Layma' : 'khác');
                $lyr = $dbg['layers'] ?? '?';
                header("Location: ?tab=freekeys&msg=" . urlencode("Đã tạo link {$lyr} lớp · domain ngoài: {$api}")); exit;
            } catch (Exception $e) { header("Location: ?tab=freekeys&err=" . urlencode($e->getMessage())); exit; }
        }
        header("Location: ?tab=freekeys&ok=1"); exit;
    }

    if ($act === 'add_game') {
        try {
            $iconUrl = handleGameIconUpload();
            $cat = $_POST['category'] ?? 'key';
            $dlUrl = trim($_POST['download_url'] ?? '');
            $playUrl = trim($_POST['play_url'] ?? '');
            $db->prepare("INSERT INTO games (name,package_name,icon_url,download_url,play_url,type,category,root_type,sort_order) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$_POST['name'],$_POST['pkg'],$iconUrl,$dlUrl,$playUrl,$_POST['type'],$cat,$_POST['root'],$_POST['sort']??0]);
            header("Location: ?tab=games&ok=1"); exit;
        } catch (Exception $e) {
            header("Location: ?tab=games&err=" . urlencode('Lỗi: ' . $e->getMessage())); exit;
        }
    }
    if ($act === 'add_acc_game') {
        try {
            $iconUrl = handleGameIconUpload();
            $name = trim($_POST['name'] ?? '');
            if (!$name) throw new Exception('Thiếu tên game');
            $db->prepare("INSERT INTO games (name,package_name,icon_url,type,category,root_type,sort_order) VALUES (?,?,?,?,?,?,?)")
               ->execute([$name, $name, $iconUrl, 'NORMAL', 'account', '', (int)($_POST['sort'] ?? 0)]);
            header("Location: ?tab=accounts&ok=1"); exit;
        } catch (Exception $e) {
            header("Location: ?tab=accounts&err=" . urlencode('Lỗi: ' . $e->getMessage())); exit;
        }
    }
    if ($act === 'toggle_game') {
        $db->prepare("UPDATE games SET is_active=1-is_active WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=games&ok=1"); exit;
    }
    if ($act === 'edit_game') {
        $iconUrl = handleGameIconUpload();
        $cat = $_POST['category'] ?? 'key';
        $dlUrl = trim($_POST['download_url'] ?? '');
        $playUrl = trim($_POST['play_url'] ?? '');
        if ($iconUrl) {
            $db->prepare("UPDATE games SET name=?, package_name=?, icon_url=?, download_url=?, play_url=?, type=?, category=?, root_type=?, sort_order=?, is_active=? WHERE id=?")
               ->execute([$_POST['name'],$_POST['pkg'],$iconUrl,$dlUrl,$playUrl,$_POST['type'],$cat,$_POST['root'],$_POST['sort']??0,$_POST['is_active']??1,$_POST['id']]);
        } else {
            $db->prepare("UPDATE games SET name=?, package_name=?, download_url=?, play_url=?, type=?, category=?, root_type=?, sort_order=?, is_active=? WHERE id=?")
               ->execute([$_POST['name'],$_POST['pkg'],$dlUrl,$playUrl,$_POST['type'],$cat,$_POST['root'],$_POST['sort']??0,$_POST['is_active']??1,$_POST['id']]);
        }
        header("Location: ?tab=games&ok=1"); exit;
    }
    if ($act === 'del_game') {
        $gid = (int)($_POST['id'] ?? 0);
        if ($gid) {
            try {
                $db->beginTransaction();
                // Trả key về pool trước khi xóa order liên quan
                $db->prepare("UPDATE `keys` SET status='available', user_id=NULL, order_id=NULL, start_at=NULL, expire_at=NULL WHERE game_id=? AND status IN ('pending','active','expired','locked')")->execute([$gid]);
                $db->prepare("DELETE FROM `keys` WHERE game_id=?")->execute([$gid]);
                $db->prepare("UPDATE accounts SET status='available', user_id=NULL, order_id=NULL WHERE game_id=? AND status='pending'")->execute([$gid]);
                $db->prepare("DELETE FROM accounts WHERE game_id=?")->execute([$gid]);
                $db->prepare("DELETE FROM orders WHERE game_id=?")->execute([$gid]);
                $db->prepare("DELETE FROM packages WHERE game_id=?")->execute([$gid]);
                $db->prepare("DELETE FROM account_types WHERE game_id=?")->execute([$gid]);
                $db->prepare("DELETE FROM free_keys WHERE game_id=?")->execute([$gid]);
                $db->prepare("DELETE FROM games WHERE id=?")->execute([$gid]);
                $db->commit();
                header("Location: ?tab=games&ok=1"); exit;
            } catch (Exception $e) {
                $db->rollBack();
                header("Location: ?tab=games&err=" . urlencode('Xoá game thất bại: ' . $e->getMessage())); exit;
            }
        }
        header("Location: ?tab=games"); exit;
    }
    if ($act === 'add_pkg') {
        $days  = max(0, (int)($_POST['days'] ?? 0));
        $hours = max(0, (int)($_POST['hours'] ?? 0));
        if ($days === 0 && $hours === 0) { header("Location: ?tab=packages&err=" . urlencode('Phải nhập ngày hoặc giờ > 0')); exit; }
        $name = $days > 0 ? ('Gói ' . $days . ' ngày' . ($hours ? ' ' . $hours . 'h' : '')) : ('Gói ' . $hours . ' giờ');
        $ksrc = ($_POST['key_source'] ?? 'pool') === 'api' ? 'api' : 'pool';
        if (pkgHasApiCols($db)) {
            $db->prepare("INSERT INTO packages (game_id,name,days,hours,price,key_type,is_active,key_source,api_game,api_duration,api_max_devices) VALUES (?,?,?,?,?,?,1,?,?,?,?)")
               ->execute([$_POST['game_id'], $name, $days, $hours, $_POST['price'], $_POST['key_type'], $ksrc,
                          $ksrc==='api' ? trim($_POST['api_game'] ?? '') : null,
                          $ksrc==='api' ? (int)($_POST['api_duration'] ?? 0) : null,
                          $ksrc==='api' ? max(1,(int)($_POST['api_max_devices'] ?? 1)) : 1]);
        } else {
            // DB chưa chạy fix_db.php -> insert kiểu cũ để không vỡ
            $db->prepare("INSERT INTO packages (game_id,name,days,hours,price,key_type,is_active) VALUES (?,?,?,?,?,?,1)")
               ->execute([$_POST['game_id'], $name, $days, $hours, $_POST['price'], $_POST['key_type']]);
        }
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'toggle_pkg') {
        $db->prepare("UPDATE packages SET is_active=1-is_active WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'edit_pkg') {
        $days  = max(0, (int)($_POST['days'] ?? 0));
        $hours = max(0, (int)($_POST['hours'] ?? 0));
        if ($days === 0 && $hours === 0) { header("Location: ?tab=packages&err=" . urlencode('Phải nhập ngày hoặc giờ > 0')); exit; }
        $auto = $days > 0 ? ('Gói ' . $days . ' ngày' . ($hours ? ' ' . $hours . 'h' : '')) : ('Gói ' . $hours . ' giờ');
        $name = trim($_POST['name'] ?? '') ?: $auto;
        $ksrc = ($_POST['key_source'] ?? 'pool') === 'api' ? 'api' : 'pool';
        if (pkgHasApiCols($db)) {
            $db->prepare("UPDATE packages SET game_id=?, name=?, days=?, hours=?, price=?, key_type=?, is_active=?, key_source=?, api_game=?, api_duration=?, api_max_devices=? WHERE id=?")
               ->execute([$_POST['game_id'], $name, $days, $hours, $_POST['price'], $_POST['key_type'], $_POST['is_active']??1, $ksrc,
                          $ksrc==='api' ? trim($_POST['api_game'] ?? '') : null,
                          $ksrc==='api' ? (int)($_POST['api_duration'] ?? 0) : null,
                          $ksrc==='api' ? max(1,(int)($_POST['api_max_devices'] ?? 1)) : 1,
                          $_POST['id']]);
        } else {
            $db->prepare("UPDATE packages SET game_id=?, name=?, days=?, hours=?, price=?, key_type=?, is_active=? WHERE id=?")
               ->execute([$_POST['game_id'], $name, $days, $hours, $_POST['price'], $_POST['key_type'], $_POST['is_active']??1, $_POST['id']]);
        }
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'del_pkg') {
        $pid = (int)($_POST['id'] ?? 0);
        try {
            // Bảo vệ: gói còn key ĐANG HOẠT ĐỘNG (đã giao khách) thì không xoá
            $ac = $db->prepare("SELECT COUNT(*) FROM `keys` WHERE package_id=? AND status='active'");
            $ac->execute([$pid]);
            if ((int)$ac->fetchColumn() > 0) {
                header("Location: ?tab=packages&err=" . urlencode('Gói còn key đang hoạt động (khách đang dùng) — hãy bấm Tắt để ẩn, không xoá.')); exit;
            }
            // Gỡ key chưa giao (available/pending/placeholder API) + free_keys rồi xoá gói
            $db->prepare("DELETE FROM `keys` WHERE package_id=? AND status IN ('available','pending')")->execute([$pid]);
            try { $db->prepare("DELETE FROM free_keys WHERE package_id=?")->execute([$pid]); } catch (Throwable $e) {}
            $db->prepare("DELETE FROM packages WHERE id=?")->execute([$pid]);
            header("Location: ?tab=packages&ok=1"); exit;
        } catch (Throwable $e) {
            header("Location: ?tab=packages&err=" . urlencode('Không xoá được gói (còn dữ liệu liên kết). Hãy bấm Tắt gói thay vì xoá.')); exit;
        }
    }
    if ($act === 'approve_order') {
        $order_code = $_POST['order_code'] ?? '';
        if (!$order_code) { header("Location: ?tab=orders&err=".urlencode('Thiếu order_code')); exit; }
        // Xác định loại đơn (key hay account)
        $otStmt = $db->prepare("SELECT order_type FROM orders WHERE order_code=? AND status='pending'");
        $otStmt->execute([$order_code]);
        $otRow = $otStmt->fetch();
        if (!$otRow) { header("Location: ?tab=orders&err=".urlencode('Đơn không tồn tại hoặc đã xử lý')); exit; }
        $isAcc = ($otRow['order_type'] === 'account');

        if ($isAcc) {
            // Đơn acc
            $stmt = $db->prepare("SELECT o.*, u.telegram_id, g.name as game_name FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id WHERE o.order_code=? AND o.status='pending'");
            $stmt->execute([$order_code]);
            $order = $stmt->fetch();
            if (!$order) { header("Location: ?tab=orders&err=".urlencode('Đơn không tồn tại hoặc đã xử lý')); exit; }
            $db->beginTransaction();
            try {
                $accStmt = $db->prepare("SELECT id, username, `password` FROM accounts WHERE order_id=? AND status='pending' LIMIT 1");
                $accStmt->execute([$order['id']]);
                $acc = $accStmt->fetch();
                if (!$acc) throw new Exception('Không tìm thấy acc pending');

                $now = date('Y-m-d H:i:s');
                $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by='web_admin' WHERE order_code=? AND status='pending'");
                $upOrder->execute([$order_code]);
                if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');

                $db->prepare("UPDATE accounts SET status='sold', sold_at=? WHERE id=? AND status='pending'")
                   ->execute([$now, $acc['id']]);
                $db->commit();
                logInfo('Admin approved account order', ['order' => $order_code]);
                if ($order['telegram_id']) sendTelegram($order['telegram_id'],
                    "✅ <b>Đơn Acc #" . h($order_code) . " đã được admin duyệt!</b>\n\n" .
                    "🎮 " . h($order['game_name']) . "\n" .
                    "👤 Tài khoản: <code>" . h($acc['username']) . "</code>\n" .
                    "🔑 Mật khẩu: <code>" . h($acc['password']) . "</code>\n\n" .
                    "⚠️ Vào game kiểm tra ngay. Đổi mật khẩu sau khi đăng nhập.");
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                logError('Admin approve acc fail', ['order' => $order_code, 'err' => $e->getMessage()]);
                header("Location: ?tab=orders&err=".urlencode($e->getMessage())); exit;
            }
        } else {
            // Đơn key (logic cũ)
            $stmt = $db->prepare("SELECT o.id, o.user_id, u.telegram_id, p.days, p.hours, p.key_type, p.price, g.name as game_name, g.package_name FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id JOIN packages p ON o.package_id=p.id WHERE o.order_code=? AND o.status='pending'");
            $stmt->execute([$order_code]);
            $order = $stmt->fetch();
            if (!$order) { header("Location: ?tab=orders&err=".urlencode('Đơn không tồn tại hoặc đã xử lý')); exit; }
            $db->beginTransaction();
            try {
                $now = date('Y-m-d H:i:s');
                $tothours = ((int)($order['days'] ?? 0)) * 24 + (int)($order['hours'] ?? 0);
                $expire = date('Y-m-d H:i:s', strtotime('+' . max(1, $tothours) . ' hours'));
                $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by='web_admin' WHERE order_code=? AND status='pending'");
                $upOrder->execute([$order_code]);
                if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');
                $db->prepare("UPDATE `keys` SET status='active', start_at=COALESCE(start_at,?), expire_at=? WHERE order_id=? AND status IN ('pending','available')")
                   ->execute([$now, $expire, $order['id']]);
                $db->commit();
                logInfo('Admin approved order', ['order' => $order_code]);
                if ($order['telegram_id']) sendTelegram($order['telegram_id'], "✅ <b>Đơn #" . h($order_code) . " đã được admin duyệt!</b>\n🔑 Key đã hoạt động. Thời hạn: " . hclouFmtDur($order['days'], $order['hours'] ?? 0) . ".");
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                logError('Admin approve fail', ['order' => $order_code, 'err' => $e->getMessage()]);
                header("Location: ?tab=orders&err=".urlencode($e->getMessage())); exit;
            }
        }
        header("Location: ?tab=orders&ok=1"); exit;
    }

    if ($act === 'reject_order') {
        $order_code = $_POST['order_code'] ?? '';
        if (!$order_code) { header("Location: ?tab=orders&err=".urlencode('Thiếu order_code')); exit; }
        $otStmt = $db->prepare("SELECT order_type FROM orders WHERE order_code=? AND status='pending'");
        $otStmt->execute([$order_code]);
        $otRow = $otStmt->fetch();
        $isAcc = ($otRow && $otRow['order_type'] === 'account');

        $stmt = $db->prepare("SELECT o.id, u.telegram_id FROM orders o JOIN users u ON o.user_id=u.id WHERE o.order_code=? AND o.status='pending'");
        $stmt->execute([$order_code]);
        $order = $stmt->fetch();
        if (!$order) { header("Location: ?tab=orders&err=".urlencode('Đơn không tồn tại hoặc đã xử lý')); exit; }
        $db->beginTransaction();
        try {
            $upOrder = $db->prepare("UPDATE orders SET status='rejected', approved_by='web_admin' WHERE order_code=? AND status='pending'");
            $upOrder->execute([$order_code]);
            if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');

            if ($isAcc) {
                $db->prepare("UPDATE accounts SET status='available', user_id=NULL, order_id=NULL WHERE order_id=? AND status='pending'")
                   ->execute([$order['id']]);
            } else {
                $db->prepare("UPDATE `keys` SET status='available', user_id=NULL, order_id=NULL WHERE order_id=? AND status='pending'")
                   ->execute([$order['id']]);
            }
            $db->commit();
            logInfo('Admin rejected order', ['order' => $order_code, 'type' => $isAcc ? 'account' : 'key']);
            if ($order) sendTelegram($order['telegram_id'], "❌ <b>Đơn #{$order_code} bị từ chối.</b>\nVui lòng liên hệ admin để được hỗ trợ.");
        } catch (Exception $e) { $db->rollBack(); header("Location: ?tab=orders&err=".urlencode($e->getMessage())); exit; }
        header("Location: ?tab=orders"); exit;
    }
    if ($act === 'lock_key') {
        $db->prepare("UPDATE `keys` SET status='locked' WHERE id=?")->execute([$_POST['key_id']]);
        header("Location: ?tab=keys"); exit;
    }
    if ($act === 'unlock_key') {
        $db->prepare("UPDATE `keys` SET status='active' WHERE id=? AND start_at IS NOT NULL AND expire_at IS NOT NULL")->execute([$_POST['key_id']]);
        header("Location: ?tab=keys"); exit;
    }
    if ($act === 'delete_key') {
        $db->prepare("DELETE FROM `keys` WHERE id=?")->execute([$_POST['key_id']]);
        header("Location: ?tab=keys&ok=1"); exit;
    }
    if ($act === 'add_keys_to_pool') {
        $keyLines = explode("\n", trim($_POST['key_codes']));
        $gameId = (int)($_POST['key_game_id'] ?? 0);
        $pkgId = (int)($_POST['key_package_id'] ?? 0);
        $count = 0; $pkgDays = -1; $pkgHours = 0;
        foreach ($keyLines as $line) {
            $code = trim($line);
            if (!$code) continue;
            $check = $db->prepare("SELECT id FROM `keys` WHERE key_code=?");
            $check->execute([$code]);
            if ($check->fetch()) continue;
            if ($pkgDays < 0) {
                $pkgStmt = $db->prepare("SELECT days, hours FROM packages WHERE id=? AND game_id=?");
                $pkgStmt->execute([$pkgId, $gameId]);
                $pkgRow = $pkgStmt->fetch();
                if (!$pkgRow || (((int)$pkgRow['days']) + ((int)$pkgRow['hours'])) <= 0) { header("Location: ?tab=keys&err=Gói không hợp lệ"); exit; }
                $pkgDays  = (int)$pkgRow['days'];
                $pkgHours = (int)$pkgRow['hours'];
            }
            $db->prepare("INSERT INTO `keys` (key_code, game_id, package_id, days, hours, status) VALUES (?,?,?,?,?,'available')")
               ->execute([$code, $gameId, $pkgId, $pkgDays, $pkgHours]);
            $count++;
        }
        header("Location: ?tab=keys&ok=1&added=" . $count); exit;
    }

    // ===== ACC =====
    if ($act === 'add_acc_type') {
        $db->prepare("INSERT INTO account_types (game_id, name, price, description, sort_order) VALUES (?,?,?,?,?)")
           ->execute([$_POST['game_id'], $_POST['name'], $_POST['price'], $_POST['description']??'', (int)($_POST['sort']??0)]);
        header("Location: ?tab=accounts&ok=1"); exit;
    }
    if ($act === 'edit_acc_type') {
        $db->prepare("UPDATE account_types SET game_id=?, name=?, price=?, description=?, sort_order=?, is_active=? WHERE id=?")
           ->execute([$_POST['game_id'], $_POST['name'], $_POST['price'], $_POST['description']??'', (int)($_POST['sort']??0), (int)($_POST['is_active']??1), $_POST['id']]);
        header("Location: ?tab=accounts&ok=1"); exit;
    }
    if ($act === 'toggle_acc_type') {
        $db->prepare("UPDATE account_types SET is_active=1-is_active WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=accounts&ok=1"); exit;
    }
    if ($act === 'del_acc_type') {
        $db->prepare("DELETE FROM account_types WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=accounts"); exit;
    }
    if ($act === 'import_accounts') {
        $accLines = explode("\n", trim($_POST['accounts']));
        $gameId = (int)($_POST['acc_game_id'] ?? 0);
        $typeId = (int)($_POST['acc_type_id'] ?? 0);
        $count = 0;
        foreach ($accLines as $line) {
            $line = trim($line);
            if (!$line) continue;
            // Định dạng: username:password hoặc username|password
            $parts = preg_split('/[:|]/', $line, 2);
            if (count($parts) < 2) continue;
            $username = trim($parts[0]); $password = trim($parts[1]);
            if (!$username || !$password) continue;
            $db->prepare("INSERT INTO accounts (game_id, account_type_id, username, `password`, status) VALUES (?,?,?,?,'available')")
               ->execute([$gameId, $typeId, $username, $password]);
            $count++;
        }
        header("Location: ?tab=accounts&ok=1&added=" . $count); exit;
    }
    if ($act === 'delete_account') {
        $db->prepare("DELETE FROM accounts WHERE id=? AND status='available'")->execute([$_POST['acc_id']]);
        header("Location: ?tab=accounts"); exit;
    }
    if ($act === 'do_update') {
        // Auto-update: tải zip từ license server → backup → giải nén đè (giữ config.local.php, data/, uploads/)
        try {
            if (!defined('LICENSE_KEY') || LICENSE_KEY === '') throw new Exception('Chưa có LICENSE_KEY');
            if (!defined('LICENSE_SERVER_URL')) throw new Exception('Thiếu LICENSE_SERVER_URL');
            $domain = strtolower(preg_replace('/^www\./', '', (string)($_SERVER['HTTP_HOST'] ?? '')));

            // 1. Backup config + version cũ
            $bkDir = APP_ROOT . '/data/update_backups';
            if (!is_dir($bkDir)) @mkdir($bkDir, 0755, true);
            @copy(APP_ROOT . '/config.local.php', $bkDir . '/config.local.php.bk_' . date('Ymd_His'));

            // 2. Tải zip
            $zipPath = APP_ROOT . '/data/update_tmp.zip';
            $url = rtrim(LICENSE_SERVER_URL, '/') . '/api.php?action=download&license_key=' . urlencode(LICENSE_KEY) . '&domain=' . urlencode($domain);
            $fp = @fopen($zipPath, 'wb');
            if (!$fp) throw new Exception('Không tạo được file tạm');
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_FILE => $fp, CURLOPT_TIMEOUT => 120, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0]);
            $okDl = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch); fclose($fp);
            if (!$okDl || $code !== 200) { @unlink($zipPath); throw new Exception('Tải code lỗi (HTTP ' . $code . '). Kiểm tra license.'); }

            // 3. Verify zip
            $za = new ZipArchive();
            if ($za->open($zipPath) !== true) { @unlink($zipPath); throw new Exception('File tải về không phải zip hợp lệ'); }
            if ($za->numFiles === 0) { $za->close(); @unlink($zipPath); throw new Exception('Zip rỗng (0 file)'); }

            // 4a. Auto-detect prefix: nếu MỌI entry đều bắt đầu bằng cùng 1 thư mục gốc
            //     (vd "CODE-WEB-B-N/...") → strip prefix đó. Hỗ trợ zip "bọc 1 lớp".
            $prefix = null;
            for ($i = 0; $i < $za->numFiles; $i++) {
                $n = $za->getNameIndex($i);
                if ($n === false || $n === '' || $n[0] === '.') continue;
                $slash = strpos($n, '/');
                if ($slash === false) { $prefix = ''; break; } // có file ở root → no prefix
                $top = substr($n, 0, $slash + 1);
                if ($prefix === null) $prefix = $top;
                elseif ($prefix !== $top) { $prefix = ''; break; }
            }
            if ($prefix === null) $prefix = '';

            // 4b. Giải nén TẤT CẢ file trong zip — không bỏ qua bất cứ gì.
            //     File nào trong zip = dán đè vô đúng vị trí.
            //     CẢNH BÁO: nếu zip có config.local.php / data/.lic / license.php → chúng cũng bị đè.
            //     → Khi đóng zip bản update, ĐỪNG bỏ những file đó vào zip thì tự nhiên không bị đè.
            //     Chỉ chặn path traversal ('..' ngoài root) vì lý do bảo mật.
            $extracted = 0; $skipped = 0; $errors = 0;
            $prefixLen = strlen($prefix);
            for ($i = 0; $i < $za->numFiles; $i++) {
                $name = $za->getNameIndex($i);
                if ($name === false || $name === '') continue;
                if (substr($name, -1) === '/') continue; // bỏ qua entry là thư mục (sẽ auto mkdir)
                // Strip prefix gốc nếu có
                $rel = ($prefixLen && strncmp($name, $prefix, $prefixLen) === 0) ? substr($name, $prefixLen) : $name;
                $rel = ltrim($rel, '/');
                if ($rel === '') { $skipped++; continue; }
                // Chặn path traversal — bảo mật, không cho thoát APP_ROOT
                if (strpos($rel, '..') !== false || strpos($rel, "\0") !== false) { $skipped++; continue; }
                // Ghi đè
                $dest = APP_ROOT . '/' . $rel;
                $destDir = dirname($dest);
                if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
                $stream = $za->getStream($name);
                if ($stream) {
                    $content = stream_get_contents($stream);
                    fclose($stream);
                    if ($content !== false && @file_put_contents($dest, $content) !== false) {
                        $extracted++;
                    } else { $errors++; }
                } else { $errors++; }
            }
            $totalEntries = $za->numFiles;
            $za->close();
            @unlink($zipPath);

            // 5. Cập nhật version.json = latest_version từ server (để hết báo "cần update")
            $newVer = '';
            if (defined('LICENSE_KEY') && LICENSE_KEY !== '' && defined('LICENSE_SERVER_URL')) {
                $cu = rtrim(LICENSE_SERVER_URL,'/').'/api.php?action=check_update&license_key='.urlencode(LICENSE_KEY).'&current_version=0';
                $ch=curl_init($cu); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>0]);
                $rr=@json_decode((string)curl_exec($ch),true); curl_close($ch);
                if(is_array($rr) && !empty($rr['latest_version'])) $newVer = $rr['latest_version'];
            }
            if ($newVer !== '') {
                @file_put_contents(APP_ROOT.'/version.json', json_encode(['version'=>$newVer]));
            }
            // Xóa cache .lic để badge/banner cập nhật lại ngay (latest đã = current)
            @unlink(APP_ROOT.'/data/.lic');

            logInfo('Admin auto-update', ['total'=>$totalEntries, 'extracted'=>$extracted, 'skipped'=>$skipped, 'errors'=>$errors, 'prefix'=>$prefix, 'version'=>$newVer]);
            header("Location: ?tab=update&ok=updated&n=" . $extracted . "&t=" . $totalEntries . "&s=" . $skipped . "&e=" . $errors . "&v=" . urlencode($newVer)); exit;
        } catch (Throwable $e) {
            header("Location: ?tab=update&err=" . urlencode('Update lỗi: ' . $e->getMessage())); exit;
        }
    }
}

// Lấy data cho dashboard
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders_pending' => $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
    'orders_approved' => $db->query("SELECT COUNT(*) FROM orders WHERE status='approved'")->fetchColumn(),
    'revenue' => $db->query("SELECT SUM(amount) FROM orders WHERE status='approved'")->fetchColumn() ?? 0,
    'keys_active' => $db->query("SELECT COUNT(*) FROM `keys` WHERE status='active'")->fetchColumn(),
    'keys_available' => $db->query("SELECT COUNT(*) FROM `keys` WHERE status='available'")->fetchColumn(),
    'keys_total' => $db->query("SELECT COUNT(*) FROM `keys`")->fetchColumn(),
];
// Số liệu bổ sung cho dashboard (chỉ tính khi mở tab dashboard để đỡ tốn query)
if (($_GET['tab'] ?? 'dashboard') === 'dashboard') {
    $stats['revenue_today']  = (int)($db->query("SELECT SUM(amount) FROM orders WHERE status='approved' AND DATE(approved_at)=CURDATE()")->fetchColumn() ?? 0);
    $stats['orders_today']   = (int)$db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    $stats['users_today']    = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    $stats['games_active']   = (int)$db->query("SELECT COUNT(*) FROM games WHERE is_active=1")->fetchColumn();
    // Doanh thu 7 ngày gần nhất (cho sparkline)
    $rev7 = array_fill(0, 7, 0);
    $rows = $db->query("SELECT DATE(approved_at) d, SUM(amount) s FROM orders WHERE status='approved' AND approved_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(approved_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
    for ($i = 0; $i < 7; $i++) {
        $d = date('Y-m-d', strtotime('-' . (6 - $i) . ' day'));
        $rev7[$i] = (int)($rows[$d] ?? 0);
    }
    $stats['rev7'] = $rev7;
    // Tỉ lệ duyệt
    $totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['approve_rate'] = $totalOrders > 0 ? round($stats['orders_approved'] / $totalOrders * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel - <?= h(SITE_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0b1020;--side:#0f172a;--side2:#111c33;--panel:#111827;--card:#182235;--card2:#151f31;--line:#26354f;--line2:#334765;--text:#edf4ff;--muted:#91a4c3;--blue:#3b82f6;--cyan:#06b6d4;--green:#22c55e;--red:#ef4444;--orange:#f59e0b;--purple:#8b5cf6;--shadow:0 18px 46px rgba(0,0,0,.28)}
html{scroll-behavior:smooth}body{background:linear-gradient(180deg,#08111f 0%,#0b1020 45%,#090d18 100%);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;min-height:100vh;font-size:14px}.nav-item{display:flex;align-items:center;gap:12px;padding:10px 18px;color:#aab8d0;text-decoration:none;font-size:14px;font-weight:700;transition:.15s;position:relative}.nav-item:hover{color:#fff;background:var(--card)}.nav-item.active{color:var(--cyan);background:rgba(6,182,212,.1)}.nav-item.active:before{content:"";position:absolute;left:0;top:6px;bottom:6px;width:3px;background:var(--cyan);border-radius:0 3px 3px 0}.nav-icon{width:20px;text-align:center;flex-shrink:0;font-size:16px}.nav-item .count{background:#f85149;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;margin-left:auto}h2{font-size:17px;margin-bottom:13px;color:#dbeafe}.alert{padding:13px 16px;border-radius:14px;font-size:13px;font-weight:750;margin-bottom:16px;border:1px solid var(--line)}.alert-green{background:rgba(34,197,94,.13);border-color:rgba(34,197,94,.30);color:#86efac}.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px;margin-bottom:28px}.stat-card{background:linear-gradient(180deg,var(--card),var(--card2));border:1px solid var(--line);border-radius:22px;padding:19px;box-shadow:var(--shadow);position:relative;overflow:hidden}.stat-card:after{content:"";position:absolute;right:-24px;top:-24px;width:84px;height:84px;border-radius:50%;background:rgba(59,130,246,.12)}.stat-card:hover{border-color:var(--line2);transform:translateY(-2px);transition:.16s}.stat-val{font-size:34px;font-weight:950;margin-bottom:5px;letter-spacing:-.04em;position:relative}.stat-label{font-size:12px;color:var(--muted);font-weight:800;position:relative}.stat-val.blue{color:#60a5fa}.stat-val.green{color:#4ade80}.stat-val.orange{color:#fbbf24}.stat-val.red{color:#f87171}
table{width:100%;border-collapse:separate;border-spacing:0;background:var(--panel);border:1px solid var(--line);border-radius:18px;overflow:hidden;font-size:13px;box-shadow:var(--shadow)}th{padding:14px 15px;text-align:left;font-size:11px;font-weight:900;color:#9fb7d7;text-transform:uppercase;border-bottom:1px solid var(--line);background:#0f172a;letter-spacing:.04em}td{padding:13px 15px;border-bottom:1px solid rgba(148,163,184,.10);vertical-align:middle;color:#e5edf8}tr:last-child td{border-bottom:none}tr:hover td{background:rgba(59,130,246,.045)}td small{color:var(--muted)!important}.badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:900}.badge.green{background:rgba(34,197,94,.14);color:#86efac;border:1px solid rgba(34,197,94,.30)}.badge.orange{background:rgba(245,158,11,.14);color:#fbbf24;border:1px solid rgba(245,158,11,.30)}.badge.red{background:rgba(239,68,68,.14);color:#fca5a5;border:1px solid rgba(239,68,68,.30)}.badge.blue{background:rgba(59,130,246,.14);color:#93c5fd;border:1px solid rgba(59,130,246,.30)}.badge.gray{background:rgba(148,163,184,.12);color:#cbd5e1;border:1px solid rgba(148,163,184,.20)}.btn{padding:8px 13px;border-radius:11px;border:none;font-size:12px;font-weight:900;cursor:pointer;transition:.14s;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap}.btn-green{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff}.btn-red{background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff}.btn-blue{background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff}.btn-gray{background:#243044;color:#e6edf3;border:1px solid var(--line2)}.btn:hover{transform:translateY(-1px);filter:brightness(1.08)}.btn:active{transform:scale(.97)}.form-card{background:var(--panel);border:1px solid var(--line);border-radius:18px;padding:20px;margin-bottom:20px;box-shadow:var(--shadow)}.form-card h3{font-size:16px;font-weight:900;margin-bottom:15px;color:#dbeafe}.form-row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}input,select{padding:10px 12px;background:#0f172a;border:1px solid var(--line);border-radius:11px;color:#e6edf3;font-size:13px;outline:none;max-width:100%}input:focus,select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(6,182,212,.12)}select option{background:#0f172a;color:#e6edf3}label{font-size:12px;color:#93c5fd;display:block;margin-bottom:6px;font-weight:850}.main a:not(.btn):not(.nav-item){color:#67e8f9;text-decoration:none}.main a:not(.btn):not(.nav-item):hover{text-decoration:underline}p{color:var(--muted)}
.guide-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:16px;margin-bottom:18px}.guide-card{background:linear-gradient(180deg,var(--card),var(--card2));border:1px solid var(--line);border-radius:20px;padding:18px;box-shadow:var(--shadow)}.guide-card h3{font-size:16px;margin-bottom:10px;color:#dbeafe}.guide-card ul{margin-left:18px;color:#cbd5e1;line-height:1.65}.guide-card li{margin:4px 0}.guide-card .where{display:inline-flex;background:rgba(6,182,212,.12);border:1px solid rgba(6,182,212,.26);color:#67e8f9;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:900;margin-bottom:10px}.guide-card code,.codebox{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace}.codebox{white-space:pre-wrap;background:#07101f;border:1px solid #26354f;border-radius:14px;padding:12px;margin-top:10px;color:#bfdbfe;font-size:12px;line-height:1.55;overflow:auto}.warnbox{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.30);color:#fde68a;border-radius:16px;padding:13px 15px;margin-bottom:16px;font-weight:750}.okbox{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.30);color:#bbf7d0;border-radius:16px;padding:13px 15px;margin-bottom:16px;font-weight:750}.desc-cell{max-width:420px;white-space:normal;line-height:1.45;color:#cbd5e1}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px}.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.filters input,.filters select{width:auto;min-width:180px}.nav-item .count{background:#f85149;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;margin-left:auto}
@media(max-width:900px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}table{display:block;overflow-x:auto;white-space:nowrap}.form-row{display:grid;grid-template-columns:1fr}.btn,input,select{width:100%}}
@media(max-width:560px){.stats-grid{grid-template-columns:1fr}.main{padding:14px}.main>h1:first-of-type{display:block}.main>h1:first-of-type:after{display:inline-flex;margin-top:8px}}

/* === Polish v2: gọn gàng đẹp hơn === */
.stats-grid{grid-template-columns:repeat(auto-fill,minmax(186px,1fr));gap:14px;margin-bottom:24px}
.stat-card{padding:16px 18px;border-radius:18px}
.stat-card:after{width:64px;height:64px;right:-18px;top:-18px;opacity:.7}
.stat-val{font-size:28px;margin-bottom:3px}
.stat-label{font-size:11px;letter-spacing:.06em;text-transform:uppercase}
.main-content h1{font-size:22px;margin-bottom:16px;letter-spacing:-.02em}
.main-content h1:after{padding:4px 10px;font-size:10.5px}
h2{font-size:15px;margin:18px 0 11px;padding-left:11px;border-left:3px solid var(--cyan);line-height:1.25}
.form-card{padding:18px;border-radius:16px;margin-bottom:16px}
.form-card h3{font-size:14px;margin-bottom:12px;padding-bottom:9px;border-bottom:1px solid rgba(148,163,184,.10);color:#cfe6ff}
table{font-size:12.5px;border-radius:14px}
th{padding:11px 13px;font-size:10.5px;position:sticky;top:0;z-index:2;backdrop-filter:blur(6px)}
td{padding:10px 13px}
.btn{padding:7px 12px;font-size:11.5px;border-radius:10px}
.btn-sm{padding:5px 9px;font-size:11px}
input,select{padding:9px 11px;font-size:12.5px;border-radius:10px}
label{font-size:11px;margin-bottom:5px;letter-spacing:.02em}
.badge{padding:3.5px 9px;font-size:10.5px}
.warnbox,.okbox,.alert{padding:12px 14px;border-radius:13px;font-size:12.5px}
/* Sidebar trau chuốt */
.sidebar-nav{width:248px}
.sn-logo{padding:16px 18px 14px}
.sn-logo .big{font-size:16px}
.nav-group{padding:8px 0 2px}
.nav-group-label{font-size:9.5px;padding:8px 18px 6px;color:#5a6e8c}
.nav-item{padding:9px 16px;font-size:12.5px;gap:11px;border-radius:0 22px 22px 0;margin-right:10px}
.nav-icon{font-size:14px;width:18px}
.nav-item.active{background:linear-gradient(90deg,rgba(6,182,212,.18),rgba(6,182,212,.04))}
.nav-item.active:before{width:3px;border-radius:0 3px 3px 0;top:8px;bottom:8px}
.nav-item .count{font-size:9.5px;padding:1px 6px;font-weight:900}
/* Topbar tinh tế */
.topbar{height:52px;backdrop-filter:blur(14px);background:rgba(15,23,42,.86)}
.topbar-logo{font-size:14px;letter-spacing:.02em}
.topbar-right{width:34px;height:34px;font-size:13px}
.main-content{padding:60px 22px 24px}
/* Action group inline */
form[style*="display:inline"]+form[style*="display:inline"]{margin-left:5px}
td form{margin:0}
/* Empty state */
.empty{text-align:center;padding:32px 18px;color:var(--muted);background:var(--panel);border:1px dashed var(--line);border-radius:14px;font-size:12.5px}
.empty .em-icon{font-size:32px;margin-bottom:6px;opacity:.55}
/* Stat-card icon */
.stat-icon{position:absolute;right:14px;top:14px;font-size:18px;opacity:.4}
/* Tables on mobile - giữ scroll, ẩn empty rows */
@media(max-width:768px){
  .main-content{padding:60px 14px 22px}
  .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .stat-card{padding:14px}
  .stat-val{font-size:22px}
  .stat-label{font-size:10px}
  .form-card{padding:14px}
  table{font-size:11.5px}
  th,td{padding:9px 10px}
  h2{font-size:14px}
  .main-content h1{font-size:18px}
  .nav-item{font-size:13px}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr}
}
.admin-footer{margin:26px 0 4px;text-align:center;color:rgba(127,144,170,.48);font-size:11px;font-weight:700;letter-spacing:.02em;opacity:.72;text-shadow:0 0 14px rgba(125,211,252,.14)}.admin-footer:before{content:"";display:block;width:120px;height:1px;background:linear-gradient(90deg,transparent,rgba(125,211,252,.28),transparent);margin:0 auto 12px}

/* === Hamburger Nav === */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 18px;height:56px;background:var(--side);border-bottom:1px solid var(--line);position:fixed;top:0;left:0;right:0;z-index:100}.hamburger{background:none;border:none;cursor:pointer;padding:8px;display:flex;flex-direction:column;gap:5px;border-radius:6px;transition:background .2s}.hamburger:hover{background:var(--card)}.hamburger span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px;transition:all .3s cubic-bezier(.4,0,.2,1)}.hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}.hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0)}.hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}.topbar-logo{font-size:15px;font-weight:800;color:var(--text)}.topbar-logo .blue{color:var(--cyan)}.topbar-right{width:36px;height:36px;border-radius:50%;background:var(--card);display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer}.nav-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:150;opacity:0;pointer-events:none;transition:opacity .3s}.nav-overlay.show{opacity:1;pointer-events:all}.sidebar-nav{position:fixed;top:0;left:0;bottom:0;width:270px;background:linear-gradient(180deg,var(--side),#0a1222);z-index:200;transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;overflow-y:auto;border-right:1px solid var(--line)}.sidebar-nav.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.5)}.sidebar-nav::-webkit-scrollbar{width:4px}.sidebar-nav::-webkit-scrollbar-thumb{background:var(--line);border-radius:99px}.sn-logo{padding:18px;border-bottom:1px solid var(--line)}.sn-logo .big{font-size:18px;font-weight:950;background:linear-gradient(135deg,var(--cyan),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent}.sn-logo .sub{color:var(--muted);font-size:10px;font-weight:700;margin-top:2px}.nav-group{padding:14px 0 4px}.nav-group-label{font-size:10px;font-weight:800;color:var(--muted);padding:0 18px 8px;text-transform:uppercase;letter-spacing:.12em}.main-content{padding:56px 22px 22px;min-height:100vh}.main-content h1{font-size:26px;font-weight:950;letter-spacing:-.035em;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:12px}.main-content h1:after{content:"Control Center";font-size:11px;letter-spacing:0;color:#bfdbfe;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);padding:6px 12px;border-radius:999px;font-weight:700}@media(max-width:768px){.main-content h1:after{display:none}.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}table{display:block;overflow-x:auto;white-space:nowrap}.form-row{display:grid;grid-template-columns:1fr}.btn,input,select{width:100%}}@media(max-width:480px){.stats-grid{grid-template-columns:1fr}}

/* ============================================================
   ✦ LUXURY v3 — giao diện admin sang trọng / chuyên nghiệp
   (đè lên các rule phía trên — đặt cuối nên thắng)
   ============================================================ */
:root{
  --lx-bg0:#070a12; --lx-bg1:#0a0f1c; --lx-bg2:#0c1322;
  --lx-panel:rgba(20,28,46,.72); --lx-panel-solid:#141c2e;
  --lx-card:rgba(24,33,54,.66);
  --lx-line:rgba(120,150,200,.14); --lx-line2:rgba(130,165,220,.26);
  --lx-text:#eef3fb; --lx-muted:#8ba0c4; --lx-muted2:#5f7390;
  --lx-gold:#e8c879; --lx-gold2:#caa24a;
  --lx-blue:#5b9dff; --lx-cyan:#3ed6e0; --lx-violet:#a78bfa;
  --lx-green:#34d399; --lx-red:#f87171; --lx-orange:#fbbf24;
  --lx-accent:linear-gradient(135deg,#6366f1,#22d3ee);
  --lx-accent-gold:linear-gradient(135deg,#e8c879,#caa24a);
  --lx-glow:0 0 0 1px rgba(255,255,255,.04),0 22px 60px -18px rgba(0,0,0,.7);
  --lx-radius:18px; --lx-radius-lg:24px;
}
body{
  font-family:'Inter','Plus Jakarta Sans',-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important;
  background:var(--lx-bg0)!important;
  color:var(--lx-text)!important;
  -webkit-font-smoothing:antialiased;letter-spacing:.01em;
  position:relative;
}
/* Nền gradient tách ra pseudo-element fixed -> cuộn mượt trên mobile,
   KHÔNG dùng background-attachment:fixed (gây kẹt scroll trên iOS/Android) */
body:after{
  content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;
  background:
    radial-gradient(1100px 620px at 78% -8%,rgba(99,102,241,.16),transparent 60%),
    radial-gradient(900px 540px at 8% 8%,rgba(34,211,238,.10),transparent 55%),
    radial-gradient(700px 700px at 95% 100%,rgba(167,139,250,.10),transparent 60%),
    linear-gradient(180deg,var(--lx-bg0),var(--lx-bg1) 45%,var(--lx-bg0));
}

/* ---------- Topbar: glass + viền sáng ---------- */
.topbar{
  height:60px!important;
  background:linear-gradient(180deg,rgba(13,19,33,.92),rgba(10,15,28,.82))!important;
  backdrop-filter:blur(22px) saturate(1.4)!important;-webkit-backdrop-filter:blur(22px) saturate(1.4)!important;
  border-bottom:1px solid var(--lx-line)!important;
  box-shadow:0 8px 30px -12px rgba(0,0,0,.6);
  padding:0 22px!important;
}
.topbar-logo{font-family:'Plus Jakarta Sans',sans-serif!important;font-size:16px!important;font-weight:800!important;letter-spacing:.02em!important;color:#fff!important}
.topbar-logo .blue{background:var(--lx-accent);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.topbar-right{width:38px!important;height:38px!important;background:rgba(255,255,255,.05)!important;border:1px solid var(--lx-line)!important;transition:.2s}
.topbar-right:hover{background:rgba(248,113,113,.14)!important;border-color:rgba(248,113,113,.4)!important;transform:translateY(-1px)}
.hamburger:hover{background:rgba(255,255,255,.06)!important}
.hamburger span{background:#cdd9ee!important;width:21px!important}

/* ---------- Sidebar: nền sâu, mục bo mềm, active gradient ---------- */
.sidebar-nav{
  width:262px!important;
  background:linear-gradient(180deg,rgba(12,18,32,.98),rgba(8,12,22,.99))!important;
  border-right:1px solid var(--lx-line)!important;
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
}
.sn-logo{padding:20px 20px 16px!important;border-bottom:1px solid var(--lx-line)!important}
.sn-logo .big{font-family:'Plus Jakarta Sans',sans-serif!important;font-size:19px!important;background:var(--lx-accent)!important;-webkit-background-clip:text!important;background-clip:text!important;-webkit-text-fill-color:transparent!important;letter-spacing:-.01em}
.sn-logo .sub{color:var(--lx-muted)!important;font-size:10px!important;letter-spacing:.16em!important;text-transform:uppercase;margin-top:3px!important}
.nav-group-label{color:#566685!important;font-size:9.5px!important;letter-spacing:.16em!important;padding:10px 20px 7px!important;font-weight:800!important}
.nav-item{
  margin:1px 12px 1px 0!important;padding:10px 18px!important;
  border-radius:0 14px 14px 0!important;font-size:13px!important;font-weight:600!important;
  color:#a7b6d2!important;gap:12px!important;transition:.2s cubic-bezier(.4,0,.2,1)!important;
}
.nav-item:hover{background:rgba(255,255,255,.045)!important;color:#fff!important;transform:translateX(2px)}
.nav-item.active{
  background:linear-gradient(90deg,rgba(99,102,241,.22),rgba(34,211,238,.05))!important;
  color:#fff!important;font-weight:800!important;
}
.nav-item.active:before{width:3px!important;background:var(--lx-accent)!important;border-radius:0 4px 4px 0!important;box-shadow:0 0 14px rgba(99,102,241,.7)}
.nav-icon{font-size:15px!important;width:20px!important;filter:saturate(1.2)}
.nav-item .count{background:linear-gradient(135deg,#f43f5e,#fb7185)!important;box-shadow:0 2px 8px rgba(244,63,94,.5)!important;font-size:9.5px!important;padding:2px 7px!important;font-weight:900!important}

/* ---------- Tiêu đề trang ---------- */
.main-content{padding:64px 26px 28px!important}
.main-content h1{
  font-family:'Plus Jakarta Sans',sans-serif!important;font-size:23px!important;font-weight:800!important;
  letter-spacing:-.02em!important;margin-bottom:20px!important;color:#fff!important;
}
.main-content h1:after{
  content:"Control Center"!important;
  background:linear-gradient(135deg,rgba(232,200,121,.14),rgba(202,162,74,.06))!important;
  border:1px solid rgba(232,200,121,.28)!important;color:var(--lx-gold)!important;
  font-size:10px!important;font-weight:800!important;letter-spacing:.1em!important;text-transform:uppercase;
  padding:5px 13px!important;border-radius:999px!important;
}
h2{
  font-family:'Plus Jakarta Sans',sans-serif!important;font-size:15px!important;font-weight:800!important;
  color:#e8eff9!important;border-left:3px solid transparent!important;
  border-image:var(--lx-accent) 1!important;padding-left:12px!important;margin:22px 0 13px!important;
}

/* ---------- Stat cards: glass + viền gradient + glow ---------- */
.stats-grid{gap:16px!important;margin-bottom:28px!important;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))!important}
.stat-card{
  background:linear-gradient(160deg,rgba(26,36,58,.85),rgba(16,23,39,.7))!important;
  border:1px solid var(--lx-line)!important;border-radius:var(--lx-radius-lg)!important;
  padding:20px 20px 18px!important;box-shadow:var(--lx-glow)!important;
  position:relative;overflow:hidden;transition:.25s cubic-bezier(.4,0,.2,1)!important;
}
.stat-card:before{
  content:"";position:absolute;inset:0;border-radius:inherit;padding:1px;
  background:linear-gradient(140deg,rgba(255,255,255,.14),transparent 40%);
  -webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);
  -webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:.7;
}
.stat-card:after{
  content:"";position:absolute;right:-30px;top:-30px;width:96px;height:96px;border-radius:50%;
  background:radial-gradient(circle,rgba(99,102,241,.22),transparent 70%)!important;
}
.stat-card:hover{transform:translateY(-3px)!important;border-color:var(--lx-line2)!important;box-shadow:0 28px 70px -20px rgba(0,0,0,.8),0 0 0 1px rgba(120,160,255,.1)!important}
.stat-icon{font-size:19px!important;opacity:.32!important;top:16px!important;right:16px!important}
.stat-val{font-family:'Plus Jakarta Sans',sans-serif!important;font-size:30px!important;font-weight:800!important;letter-spacing:-.04em!important;line-height:1.05}
.stat-val.blue{color:#7db3ff!important}.stat-val.green{color:#4ade80!important}.stat-val.orange{color:#fcd34d!important}.stat-val.red{color:#fb7185!important}
.stat-label{color:var(--lx-muted)!important;font-size:11px!important;letter-spacing:.08em!important;text-transform:uppercase;font-weight:700!important;margin-top:6px!important}

/* ---------- Bảng: glass, header gradient, hover row ---------- */
table{
  background:linear-gradient(180deg,rgba(18,26,44,.82),rgba(13,19,33,.78))!important;
  border:1px solid var(--lx-line)!important;border-radius:var(--lx-radius)!important;
  box-shadow:var(--lx-glow)!important;font-size:12.5px!important;
  /* Mọi bảng tự cuộn ngang khi tràn (fix 'lướt qua không được').
     display:block -> trình duyệt tự dựng anonymous table box, cột vẫn thẳng */
  display:block!important;overflow-x:auto!important;overflow-y:visible!important;
  -webkit-overflow-scrolling:touch;width:100%;
}
table::-webkit-scrollbar{height:8px}
table::-webkit-scrollbar-thumb{background:rgba(120,150,200,.3);border-radius:99px}
/* Bảng trong .tbl-scroll: wrapper lo cuộn, table giữ display table chuẩn */
.tbl-scroll table{display:table!important;overflow:visible!important;min-width:920px}
th{
  background:linear-gradient(180deg,rgba(16,23,40,.96),rgba(13,19,33,.92))!important;
  color:#9fb4d6!important;font-size:10.5px!important;letter-spacing:.08em!important;font-weight:800!important;
  padding:13px 15px!important;border-bottom:1px solid var(--lx-line2)!important;
}
td{padding:12px 15px!important;border-bottom:1px solid rgba(120,150,200,.08)!important;color:#dde6f4!important}
tr:hover td{background:rgba(99,102,241,.06)!important}

/* ---------- Badge: pill mềm có viền sáng ---------- */
.badge{padding:4px 11px!important;font-size:10.5px!important;font-weight:800!important;letter-spacing:.02em;border-radius:999px!important;backdrop-filter:blur(4px)}
.badge.green{background:rgba(52,211,153,.14)!important;color:#6ee7b7!important;border:1px solid rgba(52,211,153,.32)!important}
.badge.orange{background:rgba(251,191,36,.14)!important;color:#fcd34d!important;border:1px solid rgba(251,191,36,.32)!important}
.badge.red{background:rgba(248,113,113,.14)!important;color:#fca5a5!important;border:1px solid rgba(248,113,113,.32)!important}
.badge.blue{background:rgba(91,157,255,.14)!important;color:#93c5fd!important;border:1px solid rgba(91,157,255,.32)!important}
.badge.gray{background:rgba(148,163,184,.12)!important;color:#cbd5e1!important;border:1px solid rgba(148,163,184,.24)!important}

/* ---------- Buttons: gradient sâu + glow khi hover ---------- */
.btn{
  padding:8px 14px!important;font-size:12px!important;font-weight:700!important;border-radius:11px!important;
  letter-spacing:.01em;transition:.18s cubic-bezier(.4,0,.2,1)!important;
  box-shadow:0 4px 14px -4px rgba(0,0,0,.5);
}
.btn-blue{background:linear-gradient(135deg,#4f46e5,#06b6d4)!important;color:#fff!important}
.btn-green{background:linear-gradient(135deg,#059669,#10b981)!important;color:#fff!important}
.btn-red{background:linear-gradient(135deg,#dc2626,#f43f5e)!important;color:#fff!important}
.btn-gray{background:rgba(255,255,255,.06)!important;color:#e6edf3!important;border:1px solid var(--lx-line2)!important}
.btn:hover{transform:translateY(-1.5px)!important;filter:brightness(1.1)!important;box-shadow:0 10px 26px -8px rgba(79,70,229,.6)}
.btn:active{transform:scale(.96)!important}

/* ---------- Form cards & inputs ---------- */
.form-card{
  background:linear-gradient(180deg,rgba(20,28,46,.8),rgba(14,20,34,.72))!important;
  border:1px solid var(--lx-line)!important;border-radius:var(--lx-radius)!important;
  padding:22px!important;box-shadow:var(--lx-glow)!important;backdrop-filter:blur(12px);
  position:relative;overflow:hidden;
}
.form-card:before{content:"";position:absolute;left:0;top:0;right:0;height:2px;background:var(--lx-accent);opacity:.5}
.form-card h3{font-family:'Plus Jakarta Sans',sans-serif!important;font-size:15px!important;font-weight:800!important;color:#eaf1fc!important;padding-bottom:11px!important;margin-bottom:15px!important;border-bottom:1px solid var(--lx-line)!important}
input,select,textarea{
  background:rgba(8,13,24,.7)!important;border:1px solid var(--lx-line2)!important;
  border-radius:11px!important;color:#eef3fb!important;padding:10px 13px!important;font-size:13px!important;
  font-family:inherit!important;transition:.18s;
}
input:focus,select:focus,textarea:focus{border-color:var(--lx-cyan)!important;box-shadow:0 0 0 3px rgba(62,214,224,.14)!important;background:rgba(8,13,24,.92)!important}
input::placeholder,textarea::placeholder{color:#5d6f8e!important}
label{color:#9fb4d6!important;font-size:11.5px!important;font-weight:700!important;letter-spacing:.02em!important;margin-bottom:6px!important}
select option{background:#0c1322!important}

/* ---------- Boxes ---------- */
.okbox{background:linear-gradient(135deg,rgba(16,185,129,.14),rgba(16,185,129,.05))!important;border:1px solid rgba(52,211,153,.3)!important;color:#a7f3d0!important;border-radius:14px!important}
.warnbox{background:linear-gradient(135deg,rgba(251,191,36,.13),rgba(251,191,36,.04))!important;border:1px solid rgba(251,191,36,.3)!important;color:#fde68a!important;border-radius:14px!important}
.alert-green{background:linear-gradient(135deg,rgba(16,185,129,.14),rgba(16,185,129,.05))!important;border:1px solid rgba(52,211,153,.3)!important;color:#a7f3d0!important}
.codebox{background:rgba(5,9,18,.85)!important;border:1px solid var(--lx-line2)!important;border-radius:12px!important;color:#bfdbfe!important}

/* ---------- Footer ---------- */
.admin-footer{color:rgba(139,160,196,.4)!important;letter-spacing:.06em!important}
.admin-footer:before{background:linear-gradient(90deg,transparent,rgba(232,200,121,.3),transparent)!important;width:140px!important}

/* ---------- Language switcher: chuốt lại ---------- */
.adm-lang-box{background:rgba(255,255,255,.04)!important;border:1px solid var(--lx-line2)!important}
.adm-lang-pill.active{background:var(--lx-accent)!important;box-shadow:0 3px 12px rgba(79,70,229,.5)!important}

/* ---------- Scrollbar toàn cục ---------- */
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-track{background:transparent}
*::-webkit-scrollbar-thumb{background:rgba(120,150,200,.2);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
*::-webkit-scrollbar-thumb:hover{background:rgba(120,150,200,.35);background-clip:padding-box}

/* ---------- Entrance animation tinh tế ---------- */
@keyframes lxFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.stat-card,.form-card,table,.guide-card{animation:lxFade .4s cubic-bezier(.4,0,.2,1) both}
.stats-grid .stat-card:nth-child(2){animation-delay:.04s}
.stats-grid .stat-card:nth-child(3){animation-delay:.08s}
.stats-grid .stat-card:nth-child(4){animation-delay:.12s}
.stats-grid .stat-card:nth-child(5){animation-delay:.16s}
.stats-grid .stat-card:nth-child(6){animation-delay:.2s}
.stats-grid .stat-card:nth-child(7){animation-delay:.24s}

/* ---------- Mobile tinh chỉnh ---------- */
@media(max-width:768px){
  .main-content{padding:64px 15px 24px!important}
  .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:11px!important}
  .stat-card{padding:15px!important;border-radius:18px!important}
  .stat-val{font-size:23px!important}
  .main-content h1{font-size:19px!important}
  .form-card{padding:16px!important}
}
@media(max-width:480px){.stats-grid{grid-template-columns:1fr!important}}

/* Giảm tải hiệu ứng cho máy yếu */
@media(prefers-reduced-motion:reduce){.stat-card,.form-card,table,.guide-card{animation:none!important}}

/* Mobile: tắt backdrop-filter (gây giật/kẹt scroll) + giảm animation */
@media(max-width:768px){
  .form-card,table,.stat-card,.sidebar-nav,th{backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
  .form-card{background:linear-gradient(180deg,#141c2e,#0e1422)!important}
  table{background:#121a2c!important}
  .stat-card,.form-card,table,.guide-card{animation:none!important}
  body{overflow-x:hidden}
  .main-content{overflow-x:hidden}
}

/* ============================================================
   ✦ FORM POLISH — trau chuốt form sang trọng (CSS thuần)
   ============================================================ */
/* Header form: icon + tiêu đề + đường kẻ accent */
.form-card h3{
  display:flex;align-items:center;gap:9px;
  font-size:15.5px!important;letter-spacing:-.01em!important;
}
.form-card h3:first-letter{font-size:1.05em}

/* form-row → grid responsive, các field đều nhau, label nổi rõ */
.form-row{
  display:grid!important;
  grid-template-columns:repeat(auto-fit,minmax(190px,1fr))!important;
  gap:16px 18px!important;align-items:end!important;
}
/* mỗi field bọc trong <div> — cho khoảng cách + label trên input */
.form-row > div{display:flex;flex-direction:column;gap:0;min-width:0}
.form-row label,.form-card label{
  display:block;margin-bottom:7px!important;
  color:#9fb4d6!important;font-size:11px!important;font-weight:700!important;
  letter-spacing:.04em!important;text-transform:uppercase;
}

/* Input/select/textarea: cao đều, bo mềm, nền tối, focus sáng */
.form-card input,.form-card select,.form-card textarea,
.filters input,.filters select{
  width:100%!important;height:44px;
  background:rgba(7,11,20,.78)!important;
  border:1px solid rgba(130,165,220,.2)!important;border-radius:12px!important;
  color:#eef3fb!important;padding:0 14px!important;font-size:13.5px!important;
  transition:.18s cubic-bezier(.4,0,.2,1)!important;
}
.form-card textarea{height:auto!important;min-height:120px;padding:12px 14px!important;line-height:1.55;resize:vertical}
.form-card input:hover,.form-card select:hover,.form-card textarea:hover{border-color:rgba(130,165,220,.34)!important}
.form-card input:focus,.form-card select:focus,.form-card textarea:focus{
  border-color:var(--lx-cyan)!important;
  box-shadow:0 0 0 3px rgba(62,214,224,.15),0 8px 24px -10px rgba(62,214,224,.4)!important;
  background:rgba(7,11,20,.95)!important;
}
/* Select: mũi tên custom */
.form-card select,.filters select{
  appearance:none;-webkit-appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237b96c8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")!important;
  background-repeat:no-repeat!important;background-position:right 13px center!important;
  padding-right:36px!important;
}

/* Nút submit trong form: to hơn, rõ hơn */
.form-card > div:last-child .btn,
.form-card button[type=submit]:not(.btn-sm){
  height:44px;padding:0 22px!important;font-size:13px!important;font-weight:800!important;border-radius:12px!important;
}
/* Khoảng cách khối nút cuối form */
.form-card form > div[style*="margin-top:10px"],
.form-card > div[style*="margin-top:10px"]{margin-top:18px!important}

/* Nút nhỏ trong bảng (action) — kích thước đều, tròn mềm */
td .btn{height:34px;min-width:34px;padding:0 11px!important;border-radius:10px!important}
td form{display:inline-flex!important;vertical-align:middle}
td form+form{margin-left:6px!important}

/* Filters bar: thanh lọc thành 1 khối glass gọn */
.filters{
  display:flex!important;gap:10px!important;flex-wrap:wrap;align-items:center;
  background:rgba(16,23,40,.5);border:1px solid var(--lx-line);
  border-radius:14px;padding:12px 14px!important;margin-bottom:18px!important;
}
.filters input,.filters select{height:40px;min-width:170px;flex:0 1 auto}
.filters .btn{height:40px}

/* Toggle Bật/Tắt trong bảng: dạng pill nhẹ */
td .btn-gray{background:rgba(255,255,255,.05)!important;border:1px solid var(--lx-line2)!important;color:#c7d4ea!important;font-weight:700!important}
td .btn-gray:hover{background:rgba(255,255,255,.1)!important}

/* ---------- LOGIN PAGE (render riêng, không có .form-card) ---------- */
body > form.card{
  background:linear-gradient(180deg,rgba(20,28,46,.95),rgba(10,15,28,.97))!important;
  border:1px solid rgba(130,165,220,.2)!important;border-radius:26px!important;
  box-shadow:0 30px 90px -20px rgba(0,0,0,.8),0 0 0 1px rgba(255,255,255,.04),inset 0 1px 0 rgba(255,255,255,.06)!important;
  backdrop-filter:blur(24px)!important;
}
body > form.card .logo{
  background:linear-gradient(135deg,#6366f1,#22d3ee)!important;
  box-shadow:0 0 36px rgba(99,102,241,.5),inset 0 1px 0 rgba(255,255,255,.2)!important;
  border-radius:20px!important;
}
body > form.card h1{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(135deg,#fff,#c7d2fe);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
body > form.card input{height:48px;background:rgba(7,11,20,.8)!important;border-radius:13px!important}
body > form.card button{background:linear-gradient(135deg,#6366f1,#22d3ee)!important;border-radius:13px!important;box-shadow:0 14px 34px -10px rgba(99,102,241,.6)!important;transition:.2s}
body > form.card button:hover{filter:brightness(1.08);transform:translateY(-1px)}

/* Form-row trên mobile: 1 cột */
@media(max-width:640px){
  .form-row{grid-template-columns:1fr!important}
  .filters{flex-direction:column;align-items:stretch}
  .filters input,.filters select,.filters .btn{width:100%!important;min-width:0}
}

/* ============================================================
   ✦ DASHBOARD PRO — hero + KPI + section
   ============================================================ */
/* Hero: doanh thu + biểu đồ */
.dash-hero{
  display:grid;grid-template-columns:1.1fr 1fr;gap:18px;margin-bottom:22px;
}
.dash-hero-main,.dash-hero-chart{
  position:relative;overflow:hidden;border-radius:var(--lx-radius-lg);
  border:1px solid var(--lx-line);padding:24px 26px;
  background:linear-gradient(155deg,rgba(26,36,58,.9),rgba(14,20,34,.82));
  box-shadow:var(--lx-glow);animation:lxFade .45s cubic-bezier(.4,0,.2,1) both;
}
.dash-hero-main:before{
  content:"";position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:50%;
  background:radial-gradient(circle,rgba(99,102,241,.28),transparent 70%);
}
.dh-label{color:var(--lx-muted);font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;position:relative}
.dh-value{font-family:'Plus Jakarta Sans',sans-serif;font-size:38px;font-weight:800;letter-spacing:-.03em;margin:8px 0 12px;color:#fff;position:relative;line-height:1}
.dh-value .dh-unit{font-size:20px;color:var(--lx-gold);margin-left:6px;font-weight:700}
.dh-sub{display:flex;gap:8px;flex-wrap:wrap;position:relative}
.dh-chip{display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:700;padding:5px 11px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid var(--lx-line2);color:#cbd9ef}
.dh-chip.up{background:rgba(52,211,153,.13);border-color:rgba(52,211,153,.3);color:#6ee7b7}
.dash-hero-chart{display:flex;flex-direction:column;justify-content:center}
.dhc-title{color:var(--lx-muted);font-size:11.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:10px}
.spark{width:100%;height:64px;display:block;overflow:visible}
.dhc-days{display:flex;justify-content:space-between;margin-top:8px}
.dhc-days span{font-size:9.5px;color:#5f7390;font-weight:600}

/* KPI grid */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:14px;margin-bottom:26px}
.kpi{
  position:relative;overflow:hidden;display:flex;align-items:center;gap:14px;
  padding:17px 18px;border-radius:18px;border:1px solid var(--lx-line);
  background:linear-gradient(150deg,rgba(24,33,54,.85),rgba(14,20,34,.78));
  box-shadow:var(--lx-glow);transition:.25s cubic-bezier(.4,0,.2,1);
  animation:lxFade .4s cubic-bezier(.4,0,.2,1) both;
}
.kpi:hover{transform:translateY(-3px);border-color:var(--lx-line2);box-shadow:0 26px 60px -20px rgba(0,0,0,.8)}
.kpi:before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;background:linear-gradient(180deg,var(--c),var(--c2));box-shadow:0 0 16px var(--c)}
.kpi-ico{
  width:46px;height:46px;flex:0 0 46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:21px;
  background:rgba(255,255,255,.06); /* fallback máy cũ */
  background:linear-gradient(140deg,color-mix(in srgb,var(--c) 22%,transparent),color-mix(in srgb,var(--c) 6%,transparent));
  border:1px solid rgba(255,255,255,.12);
  border:1px solid color-mix(in srgb,var(--c) 30%,transparent);
}
.kpi-body{min-width:0;flex:1}
.kpi-num{font-family:'Plus Jakarta Sans',sans-serif;font-size:25px;font-weight:800;letter-spacing:-.03em;line-height:1;color:#fff}
.kpi-lbl{font-size:11.5px;color:var(--lx-muted);font-weight:600;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.kpi-meta{margin-left:auto;font-size:11px;font-weight:800;color:var(--c);background:rgba(255,255,255,.08);background:color-mix(in srgb,var(--c) 14%,transparent);padding:3px 8px;border-radius:8px;white-space:nowrap}
.kpi-meta.up{color:#6ee7b7;background:rgba(52,211,153,.14)}

/* Section head (tiêu đề + count + link) */
.dash-section-head{display:flex;align-items:center;gap:11px;margin:24px 0 14px}
.dash-section-head h2{font-size:16px!important}
.sec-count{background:linear-gradient(135deg,#f43f5e,#fb7185);color:#fff;font-size:11px;font-weight:900;padding:2px 9px;border-radius:999px;box-shadow:0 2px 8px rgba(244,63,94,.4)}
.sec-link{margin-left:auto;font-size:12px;font-weight:700;color:var(--lx-cyan)!important;text-decoration:none!important;padding:5px 12px;border-radius:9px;border:1px solid var(--lx-line2);transition:.18s}
.sec-link:hover{background:rgba(62,214,224,.1);border-color:var(--lx-cyan)}

/* Empty state đẹp */
.dash-empty{text-align:center;padding:44px 20px;border:1px dashed var(--lx-line2);border-radius:var(--lx-radius);background:rgba(16,23,40,.4)}
.dash-empty .de-ico{font-size:46px;margin-bottom:10px}
.dash-empty .de-txt{font-size:16px;font-weight:800;color:#dbe6f5;font-family:'Plus Jakarta Sans',sans-serif}
.dash-empty .de-sub{font-size:12.5px;color:var(--lx-muted);margin-top:5px}

/* Mobile dashboard */
@media(max-width:760px){
  .dash-hero{grid-template-columns:1fr;gap:13px}
  .dh-value{font-size:32px}
  .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
  .kpi{padding:14px;gap:11px}
  .kpi-ico{width:40px;height:40px;flex-basis:40px;font-size:18px}
  .kpi-num{font-size:21px}
}
@media(max-width:420px){
  .kpi-grid{grid-template-columns:1fr}
}

/* ============================================================
   ✦ BẢNG GAMES/GÓI gọn lại — chống rối khi nhiều dòng
   ============================================================ */
/* Bảng edit-inline: cho cuộn ngang mượt, ô input gọn, không vỡ hàng */
.tbl-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:var(--lx-radius);margin-bottom:18px}
.tbl-scroll table{margin:0;min-width:920px}
.tbl-scroll::-webkit-scrollbar{height:8px}
/* Input trong bảng: chiều cao thấp hơn, gọn */
table input,table select{height:36px!important;font-size:12.5px!important;padding:0 10px!important;border-radius:9px!important}
table input[type=file]{height:auto!important;padding:6px 8px!important;font-size:11px!important}
table td{vertical-align:middle}
/* Mỗi dòng game: nền xen kẽ nhẹ cho dễ đọc */
table tr:nth-child(even) td{background:rgba(255,255,255,.012)}
table tr:hover td{background:rgba(99,102,241,.07)!important}
/* Nhóm nút trong 1 ô: bọc flex gọn */
td:last-child{white-space:nowrap}
/* Icon game tròn đẹp */
table img[alt]{box-shadow:0 2px 8px rgba(0,0,0,.4);border:1px solid var(--lx-line2)}

/* ============================================================
   ✦ MODAL EDIT — popup tạo/sửa chuyên nghiệp
   ============================================================ */
.amodal-ov{position:fixed;inset:0;z-index:9990;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px;
  background:rgba(6,10,20,.7);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
  opacity:0;pointer-events:none;transition:opacity .25s;overflow-y:auto}
.amodal-ov.show{opacity:1;pointer-events:auto}
.amodal{width:100%;max-width:560px;margin:auto;border-radius:22px;position:relative;
  background:linear-gradient(180deg,#161f33,#0e1422);border:1px solid var(--lx-line2);
  box-shadow:0 40px 110px -24px rgba(0,0,0,.9),0 0 0 1px rgba(255,255,255,.04);
  transform:translateY(18px) scale(.97);transition:transform .3s cubic-bezier(.34,1.4,.6,1);overflow:hidden}
.amodal-ov.show .amodal{transform:none}
.amodal-head{display:flex;align-items:center;gap:11px;padding:20px 22px;border-bottom:1px solid var(--lx-line);
  background:linear-gradient(180deg,rgba(99,102,241,.1),transparent)}
.amodal-head .am-ico{width:40px;height:40px;flex:0 0 40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:19px;
  background:linear-gradient(140deg,rgba(99,102,241,.28),rgba(34,211,238,.1));border:1px solid rgba(99,102,241,.35)}
.amodal-head h3{margin:0!important;border:0!important;padding:0!important;font-family:'Plus Jakarta Sans',sans-serif;font-size:17px!important;font-weight:800!important;color:#fff!important}
.amodal-head .am-sub{font-size:11.5px;color:var(--lx-muted);margin-top:2px}
.amodal-x{margin-left:auto;width:34px;height:34px;border-radius:10px;border:1px solid var(--lx-line2);background:rgba(255,255,255,.04);color:#9fb4d6;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.18s}
.amodal-x:hover{background:rgba(248,113,113,.16);border-color:rgba(248,113,113,.4);color:#fca5a5}
.amodal-body{padding:22px;max-height:calc(100vh - 220px);overflow-y:auto}
.amodal-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px 16px}
.amodal-grid .full{grid-column:1/-1}
.amodal-field label{display:block;margin-bottom:7px;color:#9fb4d6;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.amodal-field input,.amodal-field select,.amodal-field textarea{width:100%!important;height:44px;background:rgba(7,11,20,.8)!important;border:1px solid var(--lx-line2)!important;border-radius:11px!important;color:#eef3fb!important;padding:0 14px!important;font-size:13.5px!important;transition:.18s}
.amodal-field textarea{height:auto!important;min-height:90px;padding:11px 14px!important;line-height:1.5;resize:vertical;font-family:ui-monospace,monospace}
.amodal-field input:focus,.amodal-field select:focus,.amodal-field textarea:focus{border-color:var(--lx-cyan)!important;box-shadow:0 0 0 3px rgba(62,214,224,.15)!important;background:rgba(7,11,20,.95)!important}
.amodal-foot{display:flex;gap:11px;padding:18px 22px;border-top:1px solid var(--lx-line);background:rgba(8,12,22,.5)}
.amodal-foot .btn{flex:1;height:46px;font-size:14px!important;font-weight:800!important;border-radius:12px!important}
.am-cur-icon{display:flex;align-items:center;gap:10px;font-size:12px;color:var(--lx-muted)}
.am-cur-icon img{width:38px;height:38px;border-radius:9px;object-fit:cover;border:1px solid var(--lx-line2)}
/* Nút action gọn trong bảng read-only */
.row-act{display:inline-flex;gap:6px}
.btn-icon{width:34px;height:34px;padding:0!important;display:inline-flex;align-items:center;justify-content:center;border-radius:10px!important;font-size:14px!important}
@media(max-width:560px){.amodal-grid{grid-template-columns:1fr}.amodal-ov{padding:16px 12px}}
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
  <button class="hamburger" id="menuBtn" type="button" aria-label="Menu" onclick="toggleNav()">
    <span></span><span></span><span></span>
  </button>
  <div class="topbar-logo">⚡ <span class="blue"><?= h(SITE_NAME) ?></span></div>
  <div class="topbar-right" onclick="location='?logout=1'" title="Thoát">🚪</div>
</div>

<!-- Overlay -->
<div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>

<!-- Nav JS định nghĩa SỚM (trước nội dung tab) để nếu tab có lỗi PHP, nút vẫn mở được -->
<script>
function openNav(){var s=document.getElementById('sidebarNav'),o=document.getElementById('navOverlay'),b=document.getElementById('menuBtn');if(s)s.classList.add('open');if(o)o.classList.add('show');if(b)b.classList.add('open');}
function closeNav(){var s=document.getElementById('sidebarNav'),o=document.getElementById('navOverlay'),b=document.getElementById('menuBtn');if(s)s.classList.remove('open');if(o)o.classList.remove('show');if(b)b.classList.remove('open');}
function toggleNav(){var s=document.getElementById('sidebarNav');if(!s)return;if(s.classList.contains('open'))closeNav();else openNav();}
</script>

<!-- Sidebar Nav -->
<div class="sidebar-nav" id="sidebarNav">

<div class="sn-logo">
  <div class="big">⚡ <?= h(SITE_NAME) ?></div>
  <div class="sub">Admin Panel</div>
</div>

<div class="nav-group">
  <div class="nav-group-label">Bảng điều khiển</div>
  <a class="nav-item <?=$tab==='dashboard'?'active':''?>" href="?tab=dashboard"><span class="nav-icon">📊</span> Tổng quan</a>
</div>

<div class="nav-group">
  <div class="nav-group-label">Đơn hàng</div>
  <a class="nav-item <?=$tab==='orders'?'active':''?>" href="?tab=orders"><span class="nav-icon">🛒</span> Đơn hàng <?php if($stats['orders_pending']>0):?><span class="count"><?=$stats['orders_pending']?></span><?php endif?></a>
  <a class="nav-item <?=$tab==='banktx'?'active':''?>" href="?tab=banktx"><span class="nav-icon">💰</span> Giao dịch</a>
  <a class="nav-item <?=$tab==='freekeys'?'active':''?>" href="?tab=freekeys"><span class="nav-icon">🎁</span> GetKey Free</a>
</div>

<div class="nav-group">
  <div class="nav-group-label">Sản phẩm</div>
  <a class="nav-item <?=$tab==='games'?'active':''?>" href="?tab=games"><span class="nav-icon">🎮</span> Games</a>
  <a class="nav-item <?=$tab==='packages'?'active':''?>" href="?tab=packages"><span class="nav-icon">📦</span> Gói Key</a>
  <a class="nav-item <?=$tab==='accounts'?'active':''?>" href="?tab=accounts"><span class="nav-icon">🏪</span> Accounts</a>
  <a class="nav-item <?=$tab==='keys'?'active':''?>" href="?tab=keys"><span class="nav-icon">🔑</span> Keys</a>
</div>

<div class="nav-group">
  <div class="nav-group-label">Tài chính</div>
  <a class="nav-item <?=$tab==='wallet'?'active':''?>" href="?tab=wallet"><span class="nav-icon">👛</span> Ví user</a>
</div>

<div class="nav-group">
  <div class="nav-group-label">Hệ thống</div>
  <a class="nav-item <?=$tab==='sysconfig'?'active':''?>" href="?tab=sysconfig"><span class="nav-icon">⚙️</span> Config</a>
  <a class="nav-item <?=$tab==='update'?'active':''?>" href="?tab=update"><span class="nav-icon">🔄</span> Cập nhật<?php
    // Badge nếu có bản mới (đọc cache .lic)
    $_lic = @json_decode((string)@file_get_contents(APP_ROOT.'/data/.lic'), true);
    $_curV = @json_decode((string)@file_get_contents(APP_ROOT.'/version.json'), true)['version'] ?? '1.0.0';
    if (is_array($_lic) && !empty($_lic['latest']) && version_compare($_lic['latest'], $_curV, '>')): ?><span class="count">●</span><?php endif; ?></a>
  <a class="nav-item <?=$tab==='setup'?'active':''?>" href="?tab=setup"><span class="nav-icon">🧭</span> Setup</a>
  <a class="nav-item <?=$tab==='users'?'active':''?>" href="?tab=users"><span class="nav-icon">👥</span> Users</a>
  <a class="nav-item" href="../" target="_blank"><span class="nav-icon">🌐</span> Web</a>
  <a class="nav-item" href="?logout=1"><span class="nav-icon">🚪</span> Thoát</a>
</div>

</div>

<div class="main-content">
<?php
// Banner báo có bản cập nhật (hiện mọi tab trừ tab update)
if ($tab !== 'update') {
    $_uLic = @json_decode((string)@file_get_contents(APP_ROOT.'/data/.lic'), true);
    $_uCur = @json_decode((string)@file_get_contents(APP_ROOT.'/version.json'), true)['version'] ?? '1.0.0';
    if (is_array($_uLic) && !empty($_uLic['latest']) && version_compare($_uLic['latest'], $_uCur, '>')):
?>
<div class="alert" style="background:linear-gradient(135deg,rgba(245,158,11,.15),rgba(251,191,36,.08));border:1px solid rgba(245,158,11,.4);color:#fde68a;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
  <span>🎉 Có bản cập nhật mới <b>v<?=h($_uLic['latest'])?></b> (đang dùng v<?=h($_uCur)?>)</span>
  <a href="?tab=update" class="btn btn-blue" style="text-decoration:none">🔄 Cập nhật ngay</a>
</div>
<?php endif; } ?>
<?php /* Thông báo ok/err đã chuyển sang toast nổi (aToast từ URL) — không render alert inline nữa */ ?>

<?php if($tab==='dashboard'): ?>
<h1>📊 Dashboard</h1>

<?php
// ----- Sparkline doanh thu 7 ngày -----
$rev7 = $stats['rev7'] ?? array_fill(0,7,0);
$rmax = max($rev7); $rmax = $rmax > 0 ? $rmax : 1;
$spW = 260; $spH = 56; $n = count($rev7); $step = $spW / max(1,$n-1);
$pts = [];
foreach ($rev7 as $i => $v) {
    $x = round($i * $step, 1);
    $y = round($spH - ($v / $rmax) * ($spH - 8) - 4, 1);
    $pts[] = "$x,$y";
}
$polyline = implode(' ', $pts);
$areaPath = "M0,$spH L" . implode(' L', $pts) . " L$spW,$spH Z";
?>

<!-- ===== Hero: Doanh thu + biểu đồ 7 ngày ===== -->
<div class="dash-hero">
  <div class="dash-hero-main">
    <div class="dh-label">💰 Tổng doanh thu</div>
    <div class="dh-value"><?=number_format($stats['revenue'],0,',','.')?><span class="dh-unit">đ</span></div>
    <div class="dh-sub">
      <span class="dh-chip up">+<?=number_format($stats['revenue_today']??0,0,',','.')?>đ hôm nay</span>
      <span class="dh-chip"><?=$stats['orders_today']??0?> đơn hôm nay</span>
    </div>
  </div>
  <div class="dash-hero-chart">
    <div class="dhc-title">Doanh thu 7 ngày</div>
    <svg viewBox="0 0 <?=$spW?> <?=$spH?>" preserveAspectRatio="none" class="spark">
      <defs>
        <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="rgba(99,102,241,.45)"/>
          <stop offset="100%" stop-color="rgba(99,102,241,0)"/>
        </linearGradient>
        <linearGradient id="sparkLine" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#6366f1"/>
          <stop offset="100%" stop-color="#22d3ee"/>
        </linearGradient>
      </defs>
      <path d="<?=$areaPath?>" fill="url(#sparkFill)"/>
      <polyline points="<?=$polyline?>" fill="none" stroke="url(#sparkLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      <?php foreach($pts as $i=>$pt){ [$px,$py]=explode(',',$pt); ?>
      <circle cx="<?=$px?>" cy="<?=$py?>" r="<?= $i===count($pts)-1 ? 3.5 : 2 ?>" fill="<?= $i===count($pts)-1 ? '#22d3ee' : '#6366f1' ?>"/>
      <?php } ?>
    </svg>
    <div class="dhc-days">
      <?php for($i=6;$i>=0;$i--): ?><span><?=date('d/m',strtotime("-$i day"))?></span><?php endfor ?>
    </div>
  </div>
</div>

<!-- ===== KPI grid ===== -->
<div class="kpi-grid">
  <div class="kpi" style="--c:#fbbf24;--c2:#f59e0b">
    <div class="kpi-ico">🛒</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['orders_pending']?></div><div class="kpi-lbl">Chờ thanh toán</div></div>
  </div>
  <div class="kpi" style="--c:#34d399;--c2:#10b981">
    <div class="kpi-ico">✅</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['orders_approved']?></div><div class="kpi-lbl">Đơn thành công</div></div>
    <div class="kpi-meta"><?=$stats['approve_rate']??0?>%</div>
  </div>
  <div class="kpi" style="--c:#5b9dff;--c2:#3b82f6">
    <div class="kpi-ico">👥</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['users']?></div><div class="kpi-lbl">Người dùng</div></div>
    <?php if(($stats['users_today']??0)>0):?><div class="kpi-meta up">+<?=$stats['users_today']?></div><?php endif?>
  </div>
  <div class="kpi" style="--c:#a78bfa;--c2:#8b5cf6">
    <div class="kpi-ico">🎮</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['games_active']??0?></div><div class="kpi-lbl">Game đang bán</div></div>
  </div>
  <div class="kpi" style="--c:#34d399;--c2:#059669">
    <div class="kpi-ico">📦</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['keys_available']?></div><div class="kpi-lbl">Key trong pool</div></div>
  </div>
  <div class="kpi" style="--c:#5b9dff;--c2:#06b6d4">
    <div class="kpi-ico">🔑</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['keys_active']?></div><div class="kpi-lbl">Key đang active</div></div>
  </div>
  <div class="kpi" style="--c:#94a3b8;--c2:#64748b">
    <div class="kpi-ico">🗝️</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['keys_total']?></div><div class="kpi-lbl">Tổng keys</div></div>
  </div>
  <div class="kpi" style="--c:#fbbf24;--c2:#d97706">
    <div class="kpi-ico">📈</div>
    <div class="kpi-body"><div class="kpi-num"><?=$stats['orders_today']??0?></div><div class="kpi-lbl">Đơn hôm nay</div></div>
  </div>
</div>

<!-- ===== Đơn chờ thanh toán ===== -->
<div class="dash-section-head">
  <h2 style="margin:0;border:0;padding:0">🛒 Đơn chờ thanh toán</h2>
  <?php $pendCount = (int)$stats['orders_pending']; if($pendCount>0):?><span class="sec-count"><?=$pendCount?></span><?php endif?>
  <a href="?tab=orders" class="sec-link">Xem tất cả →</a>
</div>
<?php
$pending = $db->query("SELECT o.*,u.telegram_username,u.full_name,g.name as game_name,COALESCE(p.name,at.name,o.order_type) as pkg_name,COALESCE(p.days,0) as days,COALESCE(p.hours,0) as hours,k.key_code FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id LEFT JOIN packages p ON o.package_id=p.id AND o.order_type='key' LEFT JOIN account_types at ON o.account_type_id=at.id AND o.order_type='account' LEFT JOIN `keys` k ON k.order_id=o.id AND k.status='pending' WHERE o.status='pending' ORDER BY o.created_at DESC LIMIT 20")->fetchAll();
if($pending): ?>
<table>
<tr><th>Mã đơn</th><th>User</th><th>Game / Gói</th><th>Key đã tạo</th><th>Tiền</th><th>Thời gian</th><th>Thao tác</th></tr>
<?php foreach($pending as $o): ?>
<tr>
  <td><b><?=h($o['order_code'])?></b></td>
  <td>@<?=h($o['telegram_username'])?><br><small style="color:#8b949e"><?=h($o['full_name'])?></small></td>
  <td><?=h($o['game_name'])?><br><small style="color:#8b949e"><?=h($o['pkg_name'])?> (<?=h(hclouFmtDur($o['days'], $o['hours'] ?? 0))?>)</small></td>
  <td style="font-family:monospace;font-size:12px"><?=htmlspecialchars($o['key_code'] ?? 'Chưa có')?></td>
  <td><b><?=number_format($o['amount'],0,',','.')?> đ</b></td>
  <td style="font-size:12px;color:#8b949e"><?=date('d/m H:i',strtotime($o['created_at']))?></td>
  <td>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="approve_order"><input type="hidden" name="order_code" value="<?=h($o['order_code'])?>"><button class="btn btn-green" onclick="return confirm('Duyệt đơn này?')">✅</button></form>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="reject_order"><input type="hidden" name="order_code" value="<?=h($o['order_code'])?>"><button class="btn btn-red" onclick="return confirm('Từ chối?')">❌</button></form>
  </td>
</tr>
<?php endforeach ?>
</table>
<?php else: ?>
<div class="dash-empty"><div class="de-ico">🎉</div><div class="de-txt">Không có đơn nào chờ thanh toán</div><div class="de-sub">Tất cả đơn đã được xử lý xong</div></div>
<?php endif ?>

<?php elseif($tab==='orders'): ?>
<h1>🛒 Quản lý đơn hàng</h1>
<?php
$filter_status = $_GET['s'] ?? 'pending';
$filter_method = $_GET['m'] ?? '';
$pmAllowed = ['mbbank','binance','card','balance'];
if ($filter_method !== '' && !in_array($filter_method, $pmAllowed, true)) $filter_method = '';
$sqlWhereParts = ['o.status=?']; $sqlParams = [$filter_status];
if ($filter_method !== '') { $sqlWhereParts[] = 'o.payment_method=?'; $sqlParams[] = $filter_method; }
$sqlWhere = implode(' AND ', $sqlWhereParts);
$orders = $db->prepare("SELECT o.*,u.telegram_username,u.full_name,g.name as game_name,COALESCE(p.name,at.name,o.order_type) as pkg_name,COALESCE(p.days,0) as days,COALESCE(p.hours,0) as hours,k.key_code,k.status as key_status FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id LEFT JOIN packages p ON o.package_id=p.id AND o.order_type='key' LEFT JOIN account_types at ON o.account_type_id=at.id AND o.order_type='account' LEFT JOIN `keys` k ON k.order_id=o.id WHERE $sqlWhere ORDER BY o.created_at DESC LIMIT 100");
$orders->execute($sqlParams); $orders = $orders->fetchAll();
$pmLabel = ['mbbank'=>'🏦 MBBank','binance'=>'🪙 Binance','card'=>'🎴 Card','balance'=>'💰 Ví'];
$pmBadgeColor = ['mbbank'=>'blue','binance'=>'orange','card'=>'purple','balance'=>'green'];
$buildOrderUrl = function($s, $m) { $qs = ['tab'=>'orders','s'=>$s]; if ($m !== '') $qs['m'] = $m; return '?' . http_build_query($qs); };
?>
<div style="margin-bottom:10px;display:flex;gap:8px;flex-wrap:wrap">
  <?php foreach(['pending'=>'⏳ Chờ TT','approved'=>'✅ Auto duyệt','rejected'=>'❌ Từ chối','cancelled'=>'🚫 Huỷ'] as $s=>$l): ?>
  <a href="<?=h($buildOrderUrl($s, $filter_method))?>" class="btn <?=$filter_status===$s?'btn-blue':'btn-gray'?>"><?=$l?></a>
  <?php endforeach ?>
</div>
<div style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;font-size:13px;align-items:center">
  <span style="color:#8b949e">Phương thức:</span>
  <a href="<?=h($buildOrderUrl($filter_status, ''))?>" class="btn <?=$filter_method===''?'btn-blue':'btn-gray'?>">Tất cả</a>
  <?php foreach($pmLabel as $m=>$l): ?>
  <a href="<?=h($buildOrderUrl($filter_status, $m))?>" class="btn <?=$filter_method===$m?'btn-blue':'btn-gray'?>"><?=$l?></a>
  <?php endforeach ?>
</div>
<table>
<tr><th>Mã đơn</th><th>User</th><th>Game / Gói</th><th>PT</th><th>Key đã tạo</th><th>Tiền</th><th>Trạng thái</th><th>Thời gian</th><?php if($filter_status==='pending'):?><th>Thao tác</th><?php endif?></tr>
<?php foreach($orders as $o): $cls=['pending'=>'orange','approved'=>'green','rejected'=>'red','cancelled'=>'gray'][$o['status']]??'gray'; $pm = $o['payment_method'] ?? 'mbbank'; ?>
<tr>
  <td><b><?=h($o['order_code'])?></b></td>
  <td>@<?=h($o['telegram_username'])?></td>
  <td><?=h($o['game_name'])?><br><small style="color:#8b949e"><?=h($o['pkg_name'])?></small></td>
  <td><span class="badge <?=h($pmBadgeColor[$pm] ?? 'gray')?>" style="font-size:11px"><?=h($pmLabel[$pm] ?? $pm)?></span></td>
  <td style="font-family:monospace;font-size:12px"><?=htmlspecialchars($o['key_code'] ?? '--')?><br><small style="color:#8b949e"><?=htmlspecialchars($o['key_status'] ?? '')?></small></td>
  <td><b><?=number_format($o['amount'],0,',','.')?> đ</b></td>
  <td><span class="badge <?=$cls?>"><?=h($o['status'])?></span></td>
  <td style="font-size:12px;color:#8b949e"><?=date('d/m/Y H:i',strtotime($o['created_at']))?></td>
  <?php if($filter_status==='pending'):?>
  <td>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="approve_order"><input type="hidden" name="order_code" value="<?=h($o['order_code'])?>"><button class="btn btn-green" onclick="return confirm('Duyệt đơn này?')">✅</button></form>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="reject_order"><input type="hidden" name="order_code" value="<?=h($o['order_code'])?>"><button class="btn btn-red" onclick="return confirm('Từ chối?')">❌</button></form>
  </td>
  <?php endif?>
</tr>
<?php endforeach ?>
</table>

<?php elseif($tab==='banktx'): ?>
<h1>💰 Giao dịch tự động (MBBank + USDT TRC20)</h1>
<?php
$tx_status = $_GET['s']   ?? '';
$tx_source = $_GET['src'] ?? '';
$tx_q      = trim($_GET['q'] ?? '');
$where = [];
$params = [];
if ($tx_status !== '') { $where[] = 'status=?'; $params[] = $tx_status; }
if ($tx_source !== '') { $where[] = 'source=?'; $params[] = $tx_source; }
if ($tx_q !== '') { $where[] = '(order_code LIKE ? OR description LIKE ? OR tx_hash LIKE ?)'; $params[] = '%'.$tx_q.'%'; $params[] = '%'.$tx_q.'%'; $params[] = '%'.$tx_q.'%'; }
$sqlWhere = $where ? ('WHERE '.implode(' AND ', $where)) : '';
// Show TẤT CẢ row, không LIMIT — user yêu cầu "có ai chuyển là hiện hết".
// Nếu sau này DB phình to gây chậm thì thêm pagination.
$txStmt = $db->prepare("SELECT * FROM bank_transactions $sqlWhere ORDER BY id DESC");
$txStmt->execute($params);
$txs = $txStmt->fetchAll();
$txStats = $db->query("SELECT status, COUNT(*) c FROM bank_transactions GROUP BY status")->fetchAll();
$txStatMap=[]; foreach($txStats as $r){ $txStatMap[$r['status']] = (int)$r['c']; }
// Breakdown theo source để hiện riêng MBBank vs Binance
$txSrcStats = $db->query("SELECT source, COUNT(*) c, SUM(amount) s FROM bank_transactions GROUP BY source")->fetchAll();
$txSrcMap=[]; foreach($txSrcStats as $r){ $txSrcMap[$r['source']??'mbbank'] = $r; }
?>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-val blue"><?=array_sum($txStatMap)?></div><div class="stat-label">Tổng giao dịch đã đọc</div></div>
  <div class="stat-card"><div class="stat-val green"><?=$txStatMap['approved']??0?></div><div class="stat-label">Đã auto duyệt</div></div>
  <div class="stat-card"><div class="stat-val orange"><?=$txStatMap['ignored']??0?></div><div class="stat-label">Bị bỏ qua</div></div>
  <div class="stat-card"><div class="stat-val red"><?=$txStatMap['error']??0?></div><div class="stat-label">Lỗi xử lý</div></div>
</div>
<div class="stats-grid" style="margin-top:8px">
  <div class="stat-card"><div class="stat-val blue">🏦 <?=(int)($txSrcMap['mbbank']['c']??0)?></div><div class="stat-label">MBBank: <?=number_format((float)($txSrcMap['mbbank']['s']??0))?>đ</div></div>
  <div class="stat-card"><div class="stat-val orange">🪙 <?=(int)($txSrcMap['binance']['c']??0)?></div><div class="stat-label">Binance: <?=rtrim(rtrim(number_format((float)($txSrcMap['binance']['s']??0), 6, '.', ''), '0'), '.')?> USDT</div></div>
</div>
<div class="form-card">
  <form method="GET" class="filters">
    <input type="hidden" name="tab" value="banktx">
    <select name="src">
      <option value="">Tất cả nguồn</option>
      <option value="mbbank"  <?=$tx_source==='mbbank' ?'selected':''?>>🏦 MBBank (VND)</option>
      <option value="binance" <?=$tx_source==='binance'?'selected':''?>>🪙 Binance (USDT TRC20)</option>
    </select>
    <select name="s">
      <option value="">Tất cả trạng thái</option>
      <?php foreach(['seen'=>'Seen','matched'=>'Matched','approved'=>'Approved','ignored'=>'Ignored','error'=>'Error'] as $v=>$l): ?>
      <option value="<?=$v?>" <?=$tx_status===$v?'selected':''?>><?=$l?></option>
      <?php endforeach; ?>
    </select>
    <input name="q" value="<?=htmlspecialchars($tx_q)?>" placeholder="Tìm ORD / nội dung / tx hash">
    <button type="submit">🔍 Lọc</button>
    <a class="btn" href="?tab=banktx">Reset</a>
  </form>
  <div class="alert" style="background:rgba(59,130,246,.10);border-color:rgba(59,130,246,.25);color:#bfdbfe">Cron poll mỗi phút: <span class="mono">cron/mbbank_poll.php</span> + <span class="mono">cron/crypto_poll.php</span>. Có ai chuyển khoản vào TK MBBank hoặc gửi USDT TRC20 vào ví là tự động xuất hiện ở bảng dưới. Nếu giao dịch không auto duyệt, kiểm tra cột <b>Ghi chú</b> + <b>Nội dung</b>.</div>
</div>
<div class="table-wrap"><table>
<tr><th>ID</th><th>Nguồn</th><th>Thời gian</th><th>Mã đơn</th><th>Số tiền</th><th>Trạng thái</th><th>Ghi chú</th><th>Nội dung</th><th>Đọc lúc</th><th>Xử lý lúc</th></tr>
<?php foreach($txs as $tx): $cls=['seen'=>'blue','matched'=>'orange','approved'=>'green','ignored'=>'gray','error'=>'red'][$tx['status']]??'gray'; $src=$tx['source']??'mbbank'; ?>
<tr>
<td><?=$tx['id']?></td>
<td><?php if($src==='binance'): ?><span class="badge orange">🪙 USDT</span><?php else: ?><span class="badge blue">🏦 MBB</span><?php endif; ?></td>
<td class="mono"><?=htmlspecialchars($tx['tx_date'])?></td>
<td><b><?=htmlspecialchars($tx['order_code'] ?: '-')?></b></td>
<td><b><?php if($src==='binance'): ?><?=rtrim(rtrim(number_format((float)$tx['amount'], 6, '.', ''), '0'), '.')?> USDT<?php else: ?><?=number_format((float)$tx['amount'])?>đ<?php endif; ?></b></td>
<td><span class="badge <?=$cls?>"><?=htmlspecialchars($tx['status'])?></span></td>
<td><?=htmlspecialchars($tx['note'] ?: '-')?></td>
<td class="desc-cell"><?=htmlspecialchars($tx['description'])?></td>
<td class="mono"><?=htmlspecialchars($tx['created_at'])?></td>
<td class="mono"><?=htmlspecialchars($tx['processed_at'] ?: '-')?></td>
</tr>
<?php endforeach; if(!$txs): ?><tr><td colspan="10"><p>Chưa có giao dịch phù hợp.</p></td></tr><?php endif; ?>
</table></div>

<?php elseif($tab==='wallet'): ?>
<h1>👛 Ví user (balance + lịch sử)</h1>
<?php
$bl_reason = $_GET['r']  ?? '';
$bl_q      = trim($_GET['q'] ?? '');
$bl_allowed = ['topup','purchase','refund','admin_adjust'];
if ($bl_reason !== '' && !in_array($bl_reason, $bl_allowed, true)) $bl_reason = '';

$wWhere = []; $wParams = [];
if ($bl_reason !== '') { $wWhere[] = 'bl.reason=?'; $wParams[] = $bl_reason; }
if ($bl_q !== '') { $wWhere[] = '(u.telegram_username LIKE ? OR u.full_name LIKE ? OR CAST(u.telegram_id AS CHAR) LIKE ?)';
    $wParams[] = '%'.$bl_q.'%'; $wParams[] = '%'.$bl_q.'%'; $wParams[] = '%'.$bl_q.'%'; }
$wSqlWhere = $wWhere ? ('WHERE '.implode(' AND ', $wWhere)) : '';

$blStmt = $db->prepare("SELECT bl.*, u.telegram_username, u.full_name, u.telegram_id, u.balance AS user_balance
    FROM balance_logs bl
    JOIN users u ON bl.user_id = u.id
    $wSqlWhere
    ORDER BY bl.id DESC LIMIT 200");
$blStmt->execute($wParams);
$balanceLogs = $blStmt->fetchAll();

$walletStats = [
    'total_balance'   => (float)$db->query("SELECT COALESCE(SUM(balance), 0) FROM users")->fetchColumn(),
    'users_with_bal'  => (int)$db->query("SELECT COUNT(*) FROM users WHERE balance > 0")->fetchColumn(),
    'credit_today'    => (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM balance_logs WHERE amount > 0 AND DATE(created_at) = CURDATE()")->fetchColumn(),
    'debit_today'     => (float)$db->query("SELECT COALESCE(SUM(ABS(amount)), 0) FROM balance_logs WHERE amount < 0 AND DATE(created_at) = CURDATE()")->fetchColumn(),
];
$reasonLabel = ['topup'=>'💵 Nạp','purchase'=>'🛒 Mua key','refund'=>'↩️ Hoàn','admin_adjust'=>'🛠️ Admin chỉnh'];
$reasonColor = ['topup'=>'green','purchase'=>'blue','refund'=>'orange','admin_adjust'=>'purple'];
$buildWalletUrl = function($r, $q) { $qs = ['tab'=>'wallet']; if ($r !== '') $qs['r'] = $r; if ($q !== '') $qs['q'] = $q; return '?' . http_build_query($qs); };
?>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-val blue"><?=number_format($walletStats['total_balance'],0,',','.')?> đ</div><div class="stat-label">Tổng số dư hệ thống</div></div>
  <div class="stat-card"><div class="stat-val purple"><?=$walletStats['users_with_bal']?></div><div class="stat-label">User có số dư &gt; 0</div></div>
  <div class="stat-card"><div class="stat-val green">+ <?=number_format($walletStats['credit_today'],0,',','.')?> đ</div><div class="stat-label">Credit hôm nay</div></div>
  <div class="stat-card"><div class="stat-val red">- <?=number_format($walletStats['debit_today'],0,',','.')?> đ</div><div class="stat-label">Debit hôm nay</div></div>
</div>

<div style="margin:14px 0;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <span style="color:#8b949e">Lý do:</span>
  <a href="<?=h($buildWalletUrl('', $bl_q))?>" class="btn <?=$bl_reason===''?'btn-blue':'btn-gray'?>">Tất cả</a>
  <?php foreach($reasonLabel as $r=>$l): ?>
  <a href="<?=h($buildWalletUrl($r, $bl_q))?>" class="btn <?=$bl_reason===$r?'btn-blue':'btn-gray'?>"><?=$l?></a>
  <?php endforeach ?>
</div>
<form method="get" style="margin-bottom:14px;display:flex;gap:8px;align-items:center">
  <input type="hidden" name="tab" value="wallet">
  <?php if($bl_reason!==''):?><input type="hidden" name="r" value="<?=h($bl_reason)?>"><?php endif ?>
  <input type="text" name="q" placeholder="Tìm username / full name / telegram_id" value="<?=h($bl_q)?>" style="flex:1;max-width:360px;padding:8px 12px;background:#0d1117;border:1px solid #30363d;border-radius:8px;color:#e6edf3">
  <button class="btn btn-blue" type="submit">🔍 Tìm</button>
  <?php if($bl_q!==''):?><a href="<?=h($buildWalletUrl($bl_reason, ''))?>" class="btn btn-gray">Xoá</a><?php endif?>
</form>

<div style="overflow-x:auto">
<table>
<tr><th>ID</th><th>User</th><th>Số dư hiện tại</th><th>Lý do</th><th>Thay đổi</th><th>Sau giao dịch</th><th>Ref</th><th>Note</th><th>Thời gian</th></tr>
<?php foreach($balanceLogs as $bl):
    $amt = (float)$bl['amount']; $reason = $bl['reason'];
    $reasonHtml = '<span class="badge ' . h($reasonColor[$reason] ?? 'gray') . '" style="font-size:11px">' . h($reasonLabel[$reason] ?? $reason) . '</span>';
?>
<tr>
  <td class="mono"><?=$bl['id']?></td>
  <td>
    @<?=h($bl['telegram_username'] ?? '--')?><br>
    <small style="color:#8b949e">ID <?=h($bl['telegram_id'])?></small>
  </td>
  <td><b><?=number_format((float)$bl['user_balance'],0,',','.')?> đ</b></td>
  <td><?=$reasonHtml?></td>
  <td style="color:<?=$amt>=0?'#3fb950':'#f85149'?>"><b><?=($amt>=0?'+':'')?><?=number_format($amt,0,',','.')?> đ</b></td>
  <td><?=number_format((float)$bl['balance_after'],0,',','.')?> đ</td>
  <td class="mono" style="font-size:11px;color:#8b949e">
    <?=h($bl['ref_type'] ?? '--')?><?php if($bl['ref_id']):?> #<?=(int)$bl['ref_id']?><?php endif ?>
  </td>
  <td style="font-size:12px;color:#8b949e;max-width:280px"><?=h($bl['note'] ?? '')?></td>
  <td style="font-size:12px;color:#8b949e"><?=date('d/m H:i:s',strtotime($bl['created_at']))?></td>
</tr>
<?php endforeach; if(!$balanceLogs): ?>
<tr><td colspan="9"><p style="color:#8b949e;padding:20px;text-align:center">Chưa có bút toán nào phù hợp.</p></td></tr>
<?php endif ?>
</table>
</div>
<p style="color:#6e7681;font-size:12px;margin-top:10px">Hiển thị tối đa 200 bút toán gần nhất. Lọc theo lý do / tìm user để zoom in.</p>

<h2 style="margin-top:30px">📥 Topup requests (yêu cầu nạp)</h2>
<?php
$tr_method = $_GET['tm'] ?? '';
$tr_status = $_GET['ts'] ?? '';
$tr_methods = ['mbbank','binance','card'];
$tr_statuses = ['pending','approved','rejected','expired'];
if ($tr_method !== '' && !in_array($tr_method, $tr_methods, true)) $tr_method = '';
if ($tr_status !== '' && !in_array($tr_status, $tr_statuses, true)) $tr_status = '';

$trWhere = []; $trParams = [];
if ($tr_method !== '') { $trWhere[] = 'tr.method=?'; $trParams[] = $tr_method; }
if ($tr_status !== '') { $trWhere[] = 'tr.status=?'; $trParams[] = $tr_status; }
$trSqlWhere = $trWhere ? ('WHERE '.implode(' AND ', $trWhere)) : '';

$trStmt = $db->prepare("SELECT tr.*, u.telegram_username, u.telegram_id
    FROM topup_requests tr
    JOIN users u ON tr.user_id = u.id
    $trSqlWhere
    ORDER BY tr.id DESC LIMIT 50");
$trStmt->execute($trParams);
$topups = $trStmt->fetchAll();

$tr24 = $db->query("SELECT method, status, COUNT(*) c
    FROM topup_requests
    WHERE created_at > NOW() - INTERVAL 24 HOUR
    GROUP BY method, status")->fetchAll();
$tr24Total = 0; $tr24Approved = 0; $tr24Rejected = 0; $tr24Pending = 0;
foreach ($tr24 as $r) {
    $tr24Total += (int)$r['c'];
    if ($r['status'] === 'approved') $tr24Approved += (int)$r['c'];
    elseif ($r['status'] === 'rejected') $tr24Rejected += (int)$r['c'];
    elseif ($r['status'] === 'pending') $tr24Pending += (int)$r['c'];
}
$tr24FailRate = $tr24Total > 0 ? round(($tr24Rejected / $tr24Total) * 100, 1) : 0;
$failColor = $tr24FailRate >= 30 ? 'red' : ($tr24FailRate >= 10 ? 'orange' : 'green');

$methodLabel = ['mbbank'=>'🏦 MBBank','binance'=>'🪙 Binance','card'=>'🎴 Card'];
$statusLabel = ['pending'=>'⏳ Chờ','approved'=>'✅ Duyệt','rejected'=>'❌ Từ chối','expired'=>'⌛ Hết hạn'];
$statusColor = ['pending'=>'orange','approved'=>'green','rejected'=>'red','expired'=>'gray'];
$methodColor = ['mbbank'=>'blue','binance'=>'orange','card'=>'purple'];
$buildTopupUrl = function($r, $q, $tm, $ts) {
    $qs = ['tab'=>'wallet'];
    if ($r !== '') $qs['r'] = $r;
    if ($q !== '') $qs['q'] = $q;
    if ($tm !== '') $qs['tm'] = $tm;
    if ($ts !== '') $qs['ts'] = $ts;
    return '?' . http_build_query($qs) . '#topup';
};
?>
<div id="topup" class="stats-grid" style="margin-top:10px">
  <div class="stat-card"><div class="stat-val blue"><?=$tr24Total?></div><div class="stat-label">Tổng request 24h</div></div>
  <div class="stat-card"><div class="stat-val green"><?=$tr24Approved?></div><div class="stat-label">Approved 24h</div></div>
  <div class="stat-card"><div class="stat-val orange"><?=$tr24Pending?></div><div class="stat-label">Còn pending 24h</div></div>
  <div class="stat-card"><div class="stat-val <?=$failColor?>"><?=$tr24Rejected?> <small style="font-size:13px;color:#8b949e">(<?=$tr24FailRate?>%)</small></div><div class="stat-label">Rejected 24h / fail rate</div></div>
</div>

<div style="margin:14px 0;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <span style="color:#8b949e">Method:</span>
  <a href="<?=h($buildTopupUrl($bl_reason, $bl_q, '', $tr_status))?>" class="btn <?=$tr_method===''?'btn-blue':'btn-gray'?>">Tất cả</a>
  <?php foreach($methodLabel as $m=>$l): ?>
  <a href="<?=h($buildTopupUrl($bl_reason, $bl_q, $m, $tr_status))?>" class="btn <?=$tr_method===$m?'btn-blue':'btn-gray'?>"><?=$l?></a>
  <?php endforeach ?>
</div>
<div style="margin:0 0 14px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <span style="color:#8b949e">Status:</span>
  <a href="<?=h($buildTopupUrl($bl_reason, $bl_q, $tr_method, ''))?>" class="btn <?=$tr_status===''?'btn-blue':'btn-gray'?>">Tất cả</a>
  <?php foreach($statusLabel as $s=>$l): ?>
  <a href="<?=h($buildTopupUrl($bl_reason, $bl_q, $tr_method, $s))?>" class="btn <?=$tr_status===$s?'btn-blue':'btn-gray'?>"><?=$l?></a>
  <?php endforeach ?>
</div>

<div style="overflow-x:auto">
<table>
<tr><th>ID</th><th>User</th><th>Method</th><th>Yêu cầu</th><th>Cộng thực</th><th>Status</th><th>Detail</th><th>Created</th><th>Processed</th></tr>
<?php foreach($topups as $tr):
    $detail = '';
    if ($tr['method'] === 'mbbank') $detail = $tr['unique_code'] ?? '';
    elseif ($tr['method'] === 'binance') $detail = (($tr['crypto_amount'] ?? '') !== '' ? rtrim(rtrim($tr['crypto_amount'], '0'), '.') . ' USDT' : '');
    elseif ($tr['method'] === 'card') $detail = trim(($tr['card_telco'] ?? '') . ' ' . (int)($tr['card_face_value'] ?? 0) . 'k');
?>
<tr>
  <td class="mono"><?=$tr['id']?></td>
  <td>@<?=h($tr['telegram_username'] ?? '--')?><br><small style="color:#8b949e">ID <?=h($tr['telegram_id'])?></small></td>
  <td><span class="badge <?=h($methodColor[$tr['method']] ?? 'gray')?>" style="font-size:11px"><?=h($methodLabel[$tr['method']] ?? $tr['method'])?></span></td>
  <td><?=number_format((float)$tr['amount_requested'],0,',','.')?> đ</td>
  <td><?= $tr['amount_credited'] !== null ? number_format((float)$tr['amount_credited'],0,',','.').' đ' : '<span style="color:#6e7681">--</span>' ?></td>
  <td><span class="badge <?=h($statusColor[$tr['status']] ?? 'gray')?>" style="font-size:11px"><?=h($statusLabel[$tr['status']] ?? $tr['status'])?></span></td>
  <td class="mono" style="font-size:11px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=h($detail)?>"><?=h($detail ?: '--')?></td>
  <td style="font-size:11px;color:#8b949e"><?=date('d/m H:i',strtotime($tr['created_at']))?></td>
  <td style="font-size:11px;color:#8b949e"><?= $tr['processed_at'] ? date('d/m H:i',strtotime($tr['processed_at'])) : '--' ?></td>
</tr>
<?php endforeach; if(!$topups): ?>
<tr><td colspan="9"><p style="color:#8b949e;padding:20px;text-align:center">Chưa có topup request phù hợp.</p></td></tr>
<?php endif ?>
</table>
</div>
<p style="color:#6e7681;font-size:12px;margin-top:10px">Hiển thị 50 request gần nhất. Fail rate ≥ 30% (đỏ) = nên kiểm tra provider doithe.vn / config bank.</p>

<?php elseif($tab==='keys'): ?>
<h1>🔑 Quản lý Keys</h1>

<?php if(isset($_GET['ok'])): ?><div class="alert alert-green">✅ Thành công!<?php if(isset($_GET['added'])):?> Đã thêm <?=(int)$_GET['added']?> key vào pool.<?php endif?></div><?php endif ?>
<?php if(isset($_GET['err'])): ?><div class="warnbox">⚠️ <?=h($_GET['err'])?></div><?php endif ?>

<!-- Form thêm key vào pool -->
<div class="form-card">
<h3>➕ Thêm key vào pool</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="add_keys_to_pool">
<div class="form-row">
  <div><label>Game</label>
    <select name="key_game_id" id="keyGameSelect" required onchange="updatePkgOptions(this.value)">
      <option value="">-- Chọn game --</option>
      <?php $allGames = $db->query("SELECT * FROM games WHERE is_active=1 ORDER BY sort_order")->fetchAll();
            foreach($allGames as $g): ?>
      <option value="<?=$g['id']?>"><?=h($g["name"])?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div><label>Gói</label>
    <select name="key_package_id" id="keyPkgSelect" required>
      <option value="">-- Chọn game trước --</option>
    </select>
  </div>
</div>
<div style="margin-top:14px"><label>Danh sách key (mỗi dòng 1 key)</label>
  <textarea name="key_codes" rows="6" required placeholder="Dán key vào đây, mỗi dòng 1 key...&#10;ABC123&#10;DEF456&#10;GHI789" style="width:100%;font-family:monospace;font-size:13px;resize:vertical"></textarea>
</div>
<div style="margin-top:10px"><button class="btn btn-green" type="submit">➕ Thêm vào pool</button></div>
</form>
</div>

<script>
function updatePkgOptions(gameId) {
  var sel = document.getElementById('keyPkgSelect');
  sel.innerHTML = '<option value="">-- Chọn gói --</option>';
  if (!gameId) return;
  var pkgs = <?=json_encode($db->query("SELECT id, game_id, name, days, hours, price FROM packages WHERE is_active=1 ORDER BY days ASC, hours ASC")->fetchAll(), JSON_UNESCAPED_UNICODE)?>;
  pkgs.forEach(function(p) {
    if (p.game_id == gameId) {
      var opt = document.createElement('option');
      opt.value = p.id;
      var d=parseInt(p.days,10)||0, h=parseInt(p.hours,10)||0;
      var dur = (d>0&&h>0)?(d+' ngày '+h+'h'):(d>0?(d+' ngày'):(h>0?(h+' giờ'):'—'));
      opt.textContent = p.name + ' (' + dur + ' - ' + Number(p.price).toLocaleString('vi-VN') + 'đ)';
      sel.appendChild(opt);
    }
  });
}
</script>

<!-- Key pool available -->
<h2>📦 Key trong pool (Available)</h2>
<?php
$poolKeys = $db->query("SELECT k.*, g.name as game_name, p.name as pkg_name FROM `keys` k JOIN games g ON k.game_id=g.id JOIN packages p ON k.package_id=p.id WHERE k.status='available' ORDER BY k.created_at DESC LIMIT 200")->fetchAll();
$poolCount = $db->query("SELECT COUNT(*) FROM `keys` WHERE status='available'")->fetchColumn();
?>
<p style="color:var(--muted);margin-bottom:10px">Tổng: <b style="color:var(--green)"><?=$poolCount?> key</b> sẵn sàng trong pool</p>
<?php if($poolKeys): ?>
<table>
<tr><th>Key</th><th>Game</th><th>Gói</th><th>Thời hạn</th><th>Thao tác</th></tr>
<?php foreach($poolKeys as $k): ?>
<tr>
  <td style="font-family:monospace;font-size:12px"><?=h($k['key_code'])?></td>
  <td><?=h($k['game_name'])?></td>
  <td style="font-size:12px"><?=h($k['pkg_name'])?></td>
  <td><?=h(hclouFmtDur($k['days'] ?? 0, $k['hours'] ?? 0))?></td>
  <td>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="delete_key"><input type="hidden" name="key_id" value="<?=h($k['id'])?>"><button class="btn btn-red" onclick="return confirm('Xoá key này khỏi pool?')">🗑</button></form>
  </td>
</tr>
<?php endforeach ?>
</table>
<?php else: ?>
<div class="alert">Chưa có key nào trong pool. Thêm key bằng form trên.</div>
<?php endif ?>

<!-- Key đã bán/đã dùng -->
<h2 style="margin-top:24px">🔐 Key đã giao (Pending/Active/Expired/Locked)</h2>
<?php
$usedKeys = $db->query("SELECT k.*,IFNULL(u.telegram_username,'--') as telegram_username,g.name as game_name,p.name as pkg_name,p.key_type,IFNULL(o.order_code,'--') as order_code FROM `keys` k LEFT JOIN users u ON k.user_id=u.id JOIN games g ON k.game_id=g.id JOIN packages p ON k.package_id=p.id LEFT JOIN orders o ON k.order_id=o.id WHERE k.status IN ('pending','active','expired','locked') ORDER BY k.created_at DESC LIMIT 100")->fetchAll();
?>
<?php if($usedKeys): ?>
<table>
<tr><th>Key</th><th>User</th><th>Game / Gói</th><th>Thời hạn</th><th>Trạng thái</th><th>Hết hạn</th><th>Thao tác</th></tr>
<?php foreach($usedKeys as $k): $cls=['active'=>'green','expired'=>'orange','locked'=>'red','pending'=>'blue'][$k['status']]??'gray'; ?>
<tr>
  <td style="font-family:monospace;font-size:12px"><?=h($k['key_code'])?></td>
  <td>@<?=h($k['telegram_username'])?></td>
  <td style="font-size:12px"><b><?=h($k['game_name'])?></b><br><small style="color:#8b949e"><?=h($k['pkg_name'])?> · <?=h($k['key_type'])?><?php if($k['order_code']!=='--'): ?> · <?=h($k['order_code'])?><?php endif ?></small></td>
  <td><?=h(hclouFmtDur($k['days'] ?? 0, $k['hours'] ?? 0))?></td>
  <td><span class="badge <?=$cls?>"><?=h($k['status'])?></span><?php if($k['status']==='expired' && !empty($k['expire_at'])):?><br><small style="color:#fbbf24">Tự xoá sau 3 ngày nếu không gia hạn</small><?php endif?></td>
  <td style="font-size:12px;color:#8b949e"><?=$k['expire_at']?date('d/m/Y H:i',strtotime($k['expire_at'])):'--'?></td>
  <td>
    <?php if($k['status']==='active'): ?>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="lock_key"><input type="hidden" name="key_id" value="<?=h($k['id'])?>"><button class="btn btn-red" onclick="return confirm('Khoá key?')">🔒</button></form>
    <?php elseif($k['status']==='locked'): ?>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="unlock_key"><input type="hidden" name="key_id" value="<?=h($k['id'])?>"><button class="btn btn-green">🔓</button></form>
    <?php endif ?>
    <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="delete_key"><input type="hidden" name="key_id" value="<?=h($k['id'])?>"><button class="btn btn-red" onclick="return confirm('Xoá vĩnh viễn key này?')">🗑</button></form>
  </td>
</tr>
<?php endforeach ?>
</table>
<?php else: ?>
<div class="alert">Chưa có key đã giao nào.</div>
<?php endif ?>

<?php elseif($tab==='games'): ?>
<?php $games = $db->query("SELECT * FROM games ORDER BY sort_order")->fetchAll(); ?>
<div class="dash-section-head">
  <h1 style="margin:0">🎮 Quản lý Games</h1>
  <span class="sec-count" style="background:linear-gradient(135deg,#6366f1,#22d3ee)"><?=count($games)?></span>
  <button class="btn btn-blue" style="margin-left:auto" onclick="amOpen('mGameAdd')">➕ Thêm game</button>
</div>

<div class="tbl-scroll">
<table>
<tr><th>#</th><th>Icon</th><th>Tên game</th><th>Package</th><th>Loại</th><th>Category</th><th>Thứ tự</th><th>Trạng thái</th><th style="text-align:right">Thao tác</th></tr>
<?php foreach($games as $g): $cat=$g['category']??'key'; $catLbl=['key'=>'🔑 Key','account'=>'👤 Acc','both'=>'🔑👤 Cả 2'][$cat]??$cat; ?>
<tr>
  <td style="color:var(--lx-muted)"><?=$g['id']?></td>
  <td><?php if(!empty($g['icon_url'])): ?><img src="<?=h($g['icon_url'])?>" alt="" style="width:38px;height:38px;border-radius:10px;object-fit:cover;background:#0d1117"><?php else: ?><span style="display:inline-flex;width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.05);align-items:center;justify-content:center;font-size:16px">🎮</span><?php endif ?></td>
  <td><b style="color:#fff"><?=h($g['name'])?></b></td>
  <td><span class="mono" style="font-size:11.5px;color:var(--lx-muted)"><?=h($g['package_name'])?></span></td>
  <td><span class="badge <?=$g['type']==='VIP'?'orange':'gray'?>"><?=h($g['type'])?></span></td>
  <td><span style="font-size:12px"><?=$catLbl?></span></td>
  <td style="text-align:center"><?=$g['sort_order']?></td>
  <td><?php if($g['is_active']): ?><span class="badge green">● Bật</span><?php else: ?><span class="badge gray">○ Tắt</span><?php endif ?></td>
  <td style="text-align:right"><div class="row-act" style="justify-content:flex-end">
    <button class="btn btn-blue btn-icon" title="Sửa" onclick='amOpen("mGameEdit",<?=json_encode(["id"=>$g["id"],"name"=>$g["name"],"pkg"=>$g["package_name"],"download_url"=>$g["download_url"]??"","play_url"=>$g["play_url"]??"","type"=>$g["type"],"category"=>$cat,"root"=>$g["root_type"],"sort"=>$g["sort_order"],"is_active"=>$g["is_active"],"_icon"=>$g["icon_url"]??"","_title"=>$g["name"]], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?>)'>✏️</button>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_game"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn btn-gray btn-icon" type="submit" title="<?=$g['is_active']?'Tắt':'Bật'?>"><?=$g['is_active']?'⏸':'▶'?></button></form>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_game"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn btn-red btn-icon" title="Xoá" onclick="return confirm('Xoá game &quot;<?=h($g['name'])?>&quot;? Các gói/order/key liên quan có thể bị ảnh hưởng.')">🗑</button></form>
  </div></td>
</tr>
<?php endforeach ?>
</table>
</div>

<!-- Modal: Thêm game -->
<div class="amodal-ov" id="mGameAdd">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">🎮</div><div><h3>Thêm game mới</h3><div class="am-sub">Tạo game bán key/acc</div></div><button class="amodal-x" onclick="amClose('mGameAdd')">✕</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="add_game">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Tên game</label><input name="name" required placeholder="Free Fire"></div>
        <div class="amodal-field full"><label>Package name</label><input name="pkg" required placeholder="com.dts.freefireth"></div>
        <div class="amodal-field full"><label>Link tải (download)</label><input name="download_url" placeholder="https://t.me/..."></div>
        <div class="amodal-field full"><label>Link chạy/play (nút ▶)</label><input name="play_url" placeholder="https://..."></div>
        <div class="amodal-field"><label>Loại</label><select name="type"><option>NORMAL</option><option>VIP</option></select></div>
        <div class="amodal-field"><label>Category</label><select name="category"><option value="key">Bán Key</option><option value="account">Bán Acc</option><option value="both">Cả Key + Acc</option></select></div>
        <div class="amodal-field"><label>Root type</label><select name="root"><option>Only Root</option><option>Root & NoRoot</option><option>NoRoot</option></select></div>
        <div class="amodal-field"><label>Thứ tự</label><input name="sort" type="number" value="0"></div>
        <div class="amodal-field full"><label>Icon (PNG/JPG, max 2MB)</label><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml"></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mGameAdd')">Huỷ</button><button class="btn btn-blue" type="submit">➕ Thêm game</button></div>
    </form>
  </div>
</div>

<!-- Modal: Sửa game -->
<div class="amodal-ov" id="mGameEdit">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">✏️</div><div><h3>Sửa game</h3><div class="am-sub" data-am-sub>—</div></div><button class="amodal-x" onclick="amClose('mGameEdit')">✕</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="edit_game"><input type="hidden" name="id" value="">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Tên game</label><input name="name" required></div>
        <div class="amodal-field full"><label>Package name</label><input name="pkg" required></div>
        <div class="amodal-field full"><label>Link tải (download)</label><input name="download_url" placeholder="https://t.me/..."></div>
        <div class="amodal-field full"><label>Link chạy/play (nút ▶)</label><input name="play_url" placeholder="https://..."></div>
        <div class="amodal-field"><label>Loại</label><select name="type"><option>NORMAL</option><option>VIP</option></select></div>
        <div class="amodal-field"><label>Category</label><select name="category"><option value="key">Bán Key</option><option value="account">Bán Acc</option><option value="both">Cả Key + Acc</option></select></div>
        <div class="amodal-field"><label>Root type</label><select name="root"><option>Only Root</option><option>Root & NoRoot</option><option>NoRoot</option></select></div>
        <div class="amodal-field"><label>Thứ tự</label><input name="sort" type="number"></div>
        <div class="amodal-field"><label>Trạng thái</label><select name="is_active"><option value="1">Bật</option><option value="0">Tắt</option></select></div>
        <div class="amodal-field full"><label>Đổi icon (chọn file mới)</label><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml"><div class="am-cur-icon" data-cur-icon style="margin-top:8px"></div></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mGameEdit')">Huỷ</button><button class="btn btn-blue" type="submit">💾 Lưu thay đổi</button></div>
    </form>
  </div>
</div>

<?php elseif($tab==='packages'): ?>
<?php $games = $db->query("SELECT * FROM games ORDER BY is_active DESC, sort_order")->fetchAll();
$pkgs = $db->query("SELECT p.*,g.name as game_name FROM packages p JOIN games g ON p.game_id=g.id ORDER BY g.sort_order,p.days")->fetchAll();
$gameOpts = '';
foreach($games as $g){ $gameOpts .= '<option value="'.$g['id'].'">'.h($g['name']).'</option>'; }
// Lấy danh sách gói từ panel (nếu đã cấu hình API) để chọn sẵn trong modal
$hcProducts = [];
if (function_exists('hclouApiConfigured') && hclouApiConfigured()) {
    $r = hclouApiProducts();
    if (!empty($r['status']) && !empty($r['games'])) $hcProducts = $r['games'];
}
?>
<script>var HCLOU_PRODUCTS = <?= json_encode($hcProducts, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script>
<div class="dash-section-head">
  <h1 style="margin:0">📦 Quản lý Gói cước</h1>
  <span class="sec-count" style="background:linear-gradient(135deg,#6366f1,#22d3ee)"><?=count($pkgs)?></span>
  <button class="btn btn-blue" style="margin-left:auto" onclick="amOpen('mPkgAdd')">➕ Thêm gói</button>
</div>

<div class="tbl-scroll">
<table>
<tr><th>Game</th><th>Tên gói</th><th>Thời hạn</th><th>Giá</th><th>Loại</th><th>Trạng thái</th><th style="text-align:right">Thao tác</th></tr>
<?php foreach($pkgs as $p): ?>
<tr>
  <td><b style="color:#fff"><?=h($p['game_name'])?></b></td>
  <td><?=h($p['name'])?></td>
  <td><span class="badge blue"><?=h(hclouFmtDur($p['days'], $p['hours']??0))?></span></td>
  <td><b style="color:#6ee7b7"><?=number_format($p['price'],0,',','.')?>đ</b></td>
  <td><span class="badge <?=$p['key_type']==='VIP'?'orange':'gray'?>"><?=h($p['key_type'])?></span></td>
  <td><?php if($p['is_active']): ?><span class="badge green">● Bật</span><?php else: ?><span class="badge gray">○ Tắt</span><?php endif ?></td>
  <td style="text-align:right"><div class="row-act" style="justify-content:flex-end">
    <button class="btn btn-blue btn-icon" title="Sửa" onclick='amOpen("mPkgEdit",<?=json_encode(["id"=>$p["id"],"game_id"=>$p["game_id"],"name"=>$p["name"],"days"=>$p["days"],"hours"=>$p["hours"]??0,"price"=>$p["price"],"key_type"=>$p["key_type"],"is_active"=>$p["is_active"],"key_source"=>$p["key_source"]??"pool","api_game"=>$p["api_game"]??"","api_duration"=>$p["api_duration"]??"","api_max_devices"=>$p["api_max_devices"]??1,"_title"=>$p["game_name"]." · ".$p["name"]], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?>)'>✏️</button>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_pkg"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-gray btn-icon" type="submit" title="<?=$p['is_active']?'Tắt':'Bật'?>"><?=$p['is_active']?'⏸':'▶'?></button></form>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_pkg"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-red btn-icon" title="Xoá" onclick="return confirm('Xoá gói này?')">🗑</button></form>
  </div></td>
</tr>
<?php endforeach ?>
</table>
</div>

<!-- Modal: Thêm gói -->
<div class="amodal-ov" id="mPkgAdd">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">📦</div><div><h3>Thêm gói mới</h3><div class="am-sub">Gói key cho game</div></div><button class="amodal-x" onclick="amClose('mPkgAdd')">✕</button></div>
    <form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="add_pkg">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Game</label><select name="game_id"><?=$gameOpts?></select></div>
        <div class="amodal-field"><label>Số ngày</label><input name="days" type="number" value="0" min="0"></div>
        <div class="amodal-field"><label>Số giờ</label><input name="hours" type="number" value="0" min="0"></div>
        <div class="amodal-field"><label>Giá (đ)</label><input name="price" type="number" required placeholder="75000"></div>
        <div class="amodal-field"><label>Loại key</label><select name="key_type"><option>Normal</option><option>VIP</option></select></div>
        <div class="amodal-field full"><label>Nguồn key</label><select name="key_source" onchange="pkgApiToggle(this,'add')"><option value="pool">📦 Pool (key nhập tay)</option><option value="api">🔗 API (panel tự sinh key)</option></select></div>
        <div class="amodal-field full" data-api="add" style="display:none"><label>Gói từ panel (chọn sẵn)</label>
          <select id="apiPickAdd" onchange="apiPickChange(this,'Add')"><option value="">— Chọn game · gói —</option></select>
          <input type="hidden" name="api_game" id="apiGameAdd"><input type="hidden" name="api_duration" id="apiDurAdd">
        </div>
        <div class="amodal-field" data-api="add" style="display:none"><label>Max thiết bị</label><input name="api_max_devices" type="number" value="1" min="1"></div>
        <div class="amodal-field full" data-api="add" style="display:none"><small style="color:#9fb2cf;font-size:11.5px">🔗 Chọn đúng gói (vd PUBG · 1 Days) bên panel. Mỗi lần khách mua sẽ gọi panel sinh key mới. Chưa thấy gói? Vào <b>Cấu hình → Kiểm tra API</b>.</small></div>
        <div class="amodal-field full"><small style="color:#9fb2cf;font-size:11.5px">💡 Có thể kết hợp ngày + giờ. Tổng &gt; 0. <b>Ngày/giờ</b> = thời hạn key hiển thị cho khách.</small></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mPkgAdd')">Huỷ</button><button class="btn btn-blue" type="submit">➕ Thêm gói</button></div>
    </form>
  </div>
</div>

<!-- Modal: Sửa gói -->
<div class="amodal-ov" id="mPkgEdit">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">✏️</div><div><h3>Sửa gói</h3><div class="am-sub" data-am-sub>—</div></div><button class="amodal-x" onclick="amClose('mPkgEdit')">✕</button></div>
    <form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="edit_pkg"><input type="hidden" name="id" value="">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Game</label><select name="game_id"><?=$gameOpts?></select></div>
        <div class="amodal-field full"><label>Tên gói</label><input name="name" required></div>
        <div class="amodal-field"><label>Số ngày</label><input name="days" type="number" min="0"></div>
        <div class="amodal-field"><label>Số giờ</label><input name="hours" type="number" min="0"></div>
        <div class="amodal-field"><label>Giá (đ)</label><input name="price" type="number" required></div>
        <div class="amodal-field"><label>Loại key</label><select name="key_type"><option>Normal</option><option>VIP</option></select></div>
        <div class="amodal-field"><label>Trạng thái</label><select name="is_active"><option value="1">Bật</option><option value="0">Tắt</option></select></div>
        <div class="amodal-field full"><label>Nguồn key</label><select name="key_source" onchange="pkgApiToggle(this,'edit')"><option value="pool">📦 Pool (key nhập tay)</option><option value="api">🔗 API (panel tự sinh key)</option></select></div>
        <div class="amodal-field full" data-api="edit" style="display:none"><label>Gói từ panel (chọn sẵn)</label>
          <select id="apiPickEdit" onchange="apiPickChange(this,'Edit')"><option value="">— Chọn game · gói —</option></select>
          <input type="hidden" name="api_game" id="apiGameEdit"><input type="hidden" name="api_duration" id="apiDurEdit">
        </div>
        <div class="amodal-field" data-api="edit" style="display:none"><label>Max thiết bị</label><input name="api_max_devices" type="number" value="1" min="1"></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mPkgEdit')">Huỷ</button><button class="btn btn-blue" type="submit">💾 Lưu thay đổi</button></div>
    </form>
  </div>
</div>

<script>
// Build dropdown "Gói từ panel" từ HCLOU_PRODUCTS; chọn sẵn nếu có game/dur
function buildApiPick(scope, selGame, selDur){
  var sel = document.getElementById('apiPick'+scope);
  if(!sel) return;
  sel.innerHTML = '';
  var prods = (typeof HCLOU_PRODUCTS!=='undefined' && HCLOU_PRODUCTS) ? HCLOU_PRODUCTS : [];
  if(!prods.length){
    sel.innerHTML = '<option value="">⚠️ Chưa kết nối panel — vào Cấu hình → Kiểm tra API</option>';
    return;
  }
  sel.appendChild(new Option('— Chọn game · gói —',''));
  prods.forEach(function(g){
    (g.packages||[]).forEach(function(p){
      var o = new Option(g.name+' · '+(p.label||p.duration+'h')+'  ($'+p.price+')', g.game+'|'+p.duration);
      if(selGame && String(selGame)===String(g.game) && String(selDur)===String(p.duration)) o.selected=true;
      sel.appendChild(o);
    });
  });
}
// Khi chọn 1 gói panel -> tách ra game + duration vào hidden
function apiPickChange(sel, scope){
  var v = (sel.value||'').split('|');
  document.getElementById('apiGame'+scope).value = v[0]||'';
  document.getElementById('apiDur'+scope).value  = v[1]||'';
}
// Hiện/ẩn field API theo nguồn key. scope: 'add' | 'edit'
function pkgApiToggle(sel, scope){
  var show = sel.value === 'api';
  document.querySelectorAll('[data-api="'+scope+'"]').forEach(function(el){ el.style.display = show ? '' : 'none'; });
  if(show){
    var S = scope.charAt(0).toUpperCase()+scope.slice(1); // Add/Edit
    var g = document.getElementById('apiGame'+S).value;
    var d = document.getElementById('apiDur'+S).value;
    buildApiPick(S, g, d);
  }
}
// Hook amOpen: mở modal sửa -> đồng bộ field API + build dropdown đã chọn sẵn
document.addEventListener('DOMContentLoaded', function(){
  var _open = window.amOpen;
  if(typeof _open === 'function'){
    window.amOpen = function(id, data){
      _open(id, data);
      if(id === 'mPkgEdit'){
        var ov = document.getElementById('mPkgEdit');
        var sel = ov.querySelector('[name=key_source]');
        if(sel) pkgApiToggle(sel, 'edit');
      }
    };
  }
});
</script>

<?php elseif($tab==='accounts'): ?>
<?php $gamesAll=$db->query("SELECT * FROM games ORDER BY sort_order")->fetchAll();
$accGames=$db->query("SELECT * FROM games WHERE category IN ('account','both') ORDER BY sort_order")->fetchAll();
$typesAll=$db->query("SELECT at.*,g.name game_name FROM account_types at JOIN games g ON at.game_id=g.id ORDER BY g.sort_order, at.sort_order")->fetchAll();
$accGameOpts=''; foreach($accGames as $g){ $accGameOpts.='<option value="'.$g['id'].'">'.h($g['name']).'</option>'; }
$allGameOpts=''; foreach($gamesAll as $g){ $allGameOpts.='<option value="'.$g['id'].'">'.h($g['name']).'</option>'; }
$typeOpts=''; foreach($typesAll as $t){ $typeOpts.='<option value="'.$t['id'].'">'.h($t['name']).' ('.h($t['game_name']).')</option>'; }
?>
<div class="dash-section-head">
  <h1 style="margin:0">🏪 Quản lý Accounts</h1>
  <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-gray" onclick="amOpen('mAccGame')">🎮 Thêm game</button>
    <button class="btn btn-gray" onclick="amOpen('mAccType')">➕ Loại acc</button>
    <button class="btn btn-blue" onclick="amOpen('mAccImport')">📥 Import acc</button>
  </div>
</div>

<?php if($typesAll): ?>
<h2>📋 Loại acc (<?=count($typesAll)?>)</h2>
<div class="tbl-scroll">
<table>
<tr><th>#</th><th>Game</th><th>Tên loại</th><th>Giá</th><th>Stock</th><th>Trạng thái</th><th style="text-align:right">Thao tác</th></tr>
<?php foreach($typesAll as $t):
  $stock = $db->prepare("SELECT COUNT(*) FROM accounts WHERE account_type_id=? AND status='available'");
  $stock->execute([$t['id']]); $availCount=(int)$stock->fetchColumn();
?>
<tr>
  <td style="color:var(--lx-muted)"><?=$t['id']?></td>
  <td><b style="color:#fff"><?=h($t['game_name'])?></b></td>
  <td><?=h($t['name'])?></td>
  <td><b style="color:#6ee7b7"><?=number_format($t['price'],0,',','.')?>đ</b></td>
  <td><span class="badge <?=$availCount>0?'green':'red'?>"><?=$availCount?> acc</span></td>
  <td><?php if($t['is_active']): ?><span class="badge green">● Bật</span><?php else: ?><span class="badge gray">○ Tắt</span><?php endif ?></td>
  <td style="text-align:right"><div class="row-act" style="justify-content:flex-end">
    <button class="btn btn-blue btn-icon" title="Sửa" onclick='amOpen("mAccTypeEdit",<?=json_encode(["id"=>$t["id"],"game_id"=>$t["game_id"],"name"=>$t["name"],"price"=>$t["price"],"description"=>$t["description"]??"","sort"=>$t["sort_order"]??0,"is_active"=>$t["is_active"],"_title"=>$t["game_name"]." · ".$t["name"]], JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?>)'>✏️</button>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_acc_type"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-gray btn-icon" type="submit" title="<?=$t['is_active']?'Tắt':'Bật'?>"><?=$t['is_active']?'⏸':'▶'?></button></form>
    <form method="POST" style="margin:0"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_acc_type"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-red btn-icon" title="Xoá" onclick="return confirm('Xoá loại acc này? Các acc thuộc loại sẽ mất.')">🗑</button></form>
  </div></td>
</tr>
<?php endforeach ?>
</table>
</div>
<?php else: ?>
<div class="dash-empty"><div class="de-ico">📭</div><div class="de-txt">Chưa có loại acc nào</div><div class="de-sub">Bấm "🎮 Thêm game" rồi "➕ Loại acc" để bắt đầu</div></div>
<?php endif ?>

<?php
$accs = $db->query("SELECT a.*, g.name game_name, at.name type_name FROM accounts a JOIN games g ON a.game_id=g.id JOIN account_types at ON a.account_type_id=at.id ORDER BY a.id DESC LIMIT 200")->fetchAll();
if($accs):
?>
<h2 style="margin-top:24px">📦 Danh sách Acc (<?=count($accs)?> gần nhất)</h2>
<div class="tbl-scroll">
<table>
<tr><th>#</th><th>Game</th><th>Loại</th><th>Tài khoản</th><th>Mật khẩu</th><th>Trạng thái</th><th>Ngày</th><th style="text-align:right">Thao tác</th></tr>
<?php foreach($accs as $a): ?>
<tr>
  <td style="color:var(--lx-muted)"><?=$a['id']?></td>
  <td><b style="color:#fff"><?=h($a['game_name'])?></b></td>
  <td><span class="badge blue"><?=h($a['type_name'])?></span></td>
  <td class="mono" style="font-size:12px"><?=h($a['username'])?></td>
  <td class="mono" style="font-size:12px"><?=h($a['password'])?></td>
  <td><?php if($a['status']=='available'): ?><span class="badge green">● Có sẵn</span><?php elseif($a['status']=='pending'): ?><span class="badge orange">○ Đang chờ</span><?php else: ?><span class="badge gray">✓ Đã bán</span><?php endif; ?></td>
  <td style="font-size:12px;color:var(--lx-muted)"><?=date('d/m/y H:i',strtotime($a['created_at']))?></td>
  <td style="text-align:right">
  <?php if($a['status']=='available'): ?>
    <form method="POST" style="margin:0;display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="delete_account"><input type="hidden" name="acc_id" value="<?=$a['id']?>"><button class="btn btn-red btn-icon" title="Xoá" onclick="return confirm('Xoá acc này?')">🗑</button></form>
  <?php else: ?><span style="color:var(--lx-muted);font-size:11px">—</span><?php endif; ?>
  </td>
</tr>
<?php endforeach ?>
</table>
</div>
<?php endif; ?>

<!-- Modal: Thêm game bán Acc -->
<div class="amodal-ov" id="mAccGame">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">🎮</div><div><h3>Thêm game bán Acc</h3><div class="am-sub">Game mới cho mảng bán account</div></div><button class="amodal-x" onclick="amClose('mAccGame')">✕</button></div>
    <form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="add_acc_game">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Tên game</label><input name="name" required placeholder="Liên Quân Mobile"></div>
        <div class="amodal-field"><label>Thứ tự</label><input name="sort" type="number" value="0"></div>
        <div class="amodal-field"><label>Icon (max 2MB)</label><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml"></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mAccGame')">Huỷ</button><button class="btn btn-blue" type="submit">➕ Thêm game</button></div>
    </form>
  </div>
</div>

<!-- Modal: Thêm loại acc -->
<div class="amodal-ov" id="mAccType">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">📋</div><div><h3>Thêm loại acc</h3><div class="am-sub">VD: Google, Facebook, Apple...</div></div><button class="amodal-x" onclick="amClose('mAccType')">✕</button></div>
    <form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="add_acc_type">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Game Acc</label><select name="game_id"><?=$accGameOpts?></select></div>
        <div class="amodal-field"><label>Tên loại acc</label><input name="name" required placeholder="Google"></div>
        <div class="amodal-field"><label>Giá (đ)</label><input name="price" type="number" required placeholder="50000"></div>
        <div class="amodal-field full"><label>Mô tả (tuỳ chọn)</label><input name="description" placeholder="Mô tả thêm"></div>
        <div class="amodal-field"><label>Thứ tự</label><input name="sort" type="number" value="0"></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mAccType')">Huỷ</button><button class="btn btn-blue" type="submit">➕ Thêm loại</button></div>
    </form>
  </div>
</div>

<!-- Modal: Sửa loại acc -->
<div class="amodal-ov" id="mAccTypeEdit">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">✏️</div><div><h3>Sửa loại acc</h3><div class="am-sub" data-am-sub>—</div></div><button class="amodal-x" onclick="amClose('mAccTypeEdit')">✕</button></div>
    <form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="edit_acc_type"><input type="hidden" name="id" value="">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field full"><label>Game</label><select name="game_id"><?=$allGameOpts?></select></div>
        <div class="amodal-field"><label>Tên loại</label><input name="name" required></div>
        <div class="amodal-field"><label>Giá (đ)</label><input name="price" type="number" required></div>
        <div class="amodal-field full"><label>Mô tả (tuỳ chọn)</label><input name="description"></div>
        <div class="amodal-field"><label>Thứ tự</label><input name="sort" type="number"></div>
        <div class="amodal-field"><label>Trạng thái</label><select name="is_active"><option value="1">Bật</option><option value="0">Tắt</option></select></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mAccTypeEdit')">Huỷ</button><button class="btn btn-blue" type="submit">💾 Lưu thay đổi</button></div>
    </form>
  </div>
</div>

<!-- Modal: Import acc -->
<div class="amodal-ov" id="mAccImport">
  <div class="amodal">
    <div class="amodal-head"><div class="am-ico">📥</div><div><h3>Import acc</h3><div class="am-sub">Dán danh sách tài khoản</div></div><button class="amodal-x" onclick="amClose('mAccImport')">✕</button></div>
    <form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="import_accounts">
      <div class="amodal-body"><div class="amodal-grid">
        <div class="amodal-field"><label>Game Acc</label><select name="acc_game_id"><?=$accGameOpts?></select></div>
        <div class="amodal-field"><label>Loại acc</label><select name="acc_type_id"><?=$typeOpts?></select></div>
        <div class="amodal-field full"><label>Danh sách acc (mỗi dòng: tk:mk hoặc tk|mk)</label><textarea name="accounts" rows="7" placeholder="user1@gmail.com:pass123&#10;user2@gmail.com:pass456&#10;user3|pass789"></textarea></div>
      </div></div>
      <div class="amodal-foot"><button type="button" class="btn btn-gray" onclick="amClose('mAccImport')">Huỷ</button><button class="btn btn-blue" type="submit">📥 Import</button></div>
    </form>
  </div>
</div>


<?php elseif($tab==='freekeys'): ?>
<h1>🎁 GetKey Free</h1>
<?php $gamesAll=$db->query("SELECT * FROM games WHERE is_active=1 ORDER BY sort_order")->fetchAll(); $packagesAll=$db->query("SELECT p.*,g.name game_name FROM packages p JOIN games g ON p.game_id=g.id WHERE p.is_active=1 ORDER BY g.sort_order,p.days")->fetchAll(); ?>
<div class="form-card"><h3>➕ Thêm nhiều key free cùng lúc</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="add_free_key">
<div class="form-row">
<div><label>Game</label>
  <select name="game_id" id="freeKeyGameSelect" required onchange="updateFreeKeyPkgOptions(this.value)">
    <option value="">-- Chọn game --</option>
    <?php foreach($gamesAll as $g): ?>
    <option value="<?=$g['id']?>"><?=h($g["name"])?></option>
    <?php endforeach ?>
  </select>
</div>
<div><label>Gói</label>
  <select name="package_id" id="freeKeyPkgSelect" required>
    <option value="">-- Chọn game trước --</option>
  </select>
</div>
</div>
<div style="margin-top:14px"><label>Danh sách key (mỗi dòng 1 key)</label>
  <textarea name="key_codes" rows="6" required placeholder="Dán key vào đây, mỗi dòng 1 key...&#10;ABCDEF123&#10;XYZ456789&#10;HCLOU-FREE-001" style="width:100%;font-family:monospace;font-size:13px;resize:vertical;background:#0d1117;color:#e6edf3;border:1px solid var(--line);border-radius:11px;padding:10px"></textarea>
</div>
<div style="margin-top:10px"><button class="btn btn-green" type="submit">➕ Thêm vào pool key free</button>
<small style="color:#8b949e;margin-left:10px">Pool nhiều key → mỗi user vượt link nhận 1 key khác nhau. Link tự tạo per-user (Layma/Link4M).</small>
</div></form>

<script>
function updateFreeKeyPkgOptions(gameId) {
  var sel = document.getElementById('freeKeyPkgSelect');
  sel.innerHTML = '<option value="">-- Chọn gói --</option>';
  if (!gameId) return;
  var pkgs = <?=json_encode($packagesAll, JSON_UNESCAPED_UNICODE)?>;
  pkgs.forEach(function(p) {
    if (p.game_id == gameId) {
      var opt = document.createElement('option');
      opt.value = p.id;
      var d=parseInt(p.days,10)||0, h=parseInt(p.hours,10)||0;
      var dur=(d>0&&h>0)?(d+' ngày '+h+'h'):(d>0?(d+' ngày'):(h>0?(h+' giờ'):'—'));
      opt.textContent = dur + ' (' + p.name + ')';
      sel.appendChild(opt);
    }
  });
}
</script>
</div>
<?php $fks=$db->query("SELECT fk.*,g.name game_name,p.name pkg_name,(SELECT COUNT(*) FROM free_key_claims c WHERE c.free_key_id=fk.id) claims FROM free_keys fk JOIN games g ON fk.game_id=g.id JOIN packages p ON fk.package_id=p.id ORDER BY fk.created_at DESC LIMIT 100")->fetchAll(); ?>
<table><tr><th>Key</th><th>Game/Gói</th><th>Thời gian</th><th>Link</th><th>Claim</th><th>TT</th><th>Action</th></tr>
<?php foreach($fks as $fk): ?><tr>
<td style="font-family:monospace"><?=htmlspecialchars($fk['key_code'])?></td><td><?=h($fk['game_name'])?><br><small style="color:#8b949e"><?=h($fk['pkg_name'])?> · <?=h($fk['key_type'])?></small></td>
<td><small><?=date('d/m H:i',strtotime($fk['start_at']))?> → <?=date('d/m H:i',strtotime($fk['expire_at']))?></small></td>
<td style="max-width:240px;overflow:hidden;text-overflow:ellipsis"><a href="<?=htmlspecialchars($fk['short_url']?:SITE_URL.'/claim.php?t='.$fk['claim_token'])?>" target="_blank">Mở link</a><br><small style="color:#8b949e"><?=htmlspecialchars($fk['short_url']?:'Chưa có link')?></small></td>
<td><?=h($fk['claims'])?></td><td><span class="badge <?=$fk['is_active']?'green':'gray'?>"><?=$fk['is_active']?'Bật':'Tắt'?></span></td>
<td><form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_free_key"><input type="hidden" name="id" value="<?=h($fk['id'])?>"><button class="btn btn-gray"><?=$fk['is_active']?'Tắt':'Bật'?></button></form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="regen_free_link"><input type="hidden" name="id" value="<?=h($fk['id'])?>"><button class="btn btn-blue">Tạo lại link</button></form></td>
</tr><?php endforeach ?>
<?php if(!$fks): ?><tr><td colspan="7" style="text-align:center;color:#8b949e;padding:24px">Chưa có key free nào</td></tr><?php endif ?>
</table>

<?php elseif($tab==='sysconfig'): ?>
<h1>⚙️ Cấu hình hệ thống</h1>
<?php ensureAdminConfigLogTable($db); $cfgKeys = hclouConfigEditableKeys(); $logs = $db->query("SELECT * FROM admin_config_logs ORDER BY id DESC LIMIT 30")->fetchAll(); ?>
<div class="warnbox">⚠️ Chỉ sửa các mục thật sự cần. Token/API key không được public. Khi lưu, hệ thống tự tạo backup <span class="mono">config.php.bk_admincfg_*</span> rồi ghi log vào SQL.</div>
<?php
// =============================================
// PANEL "💳 Phương thức thanh toán" — 2 toggle button MBBank / Binance
// Đặt TRÊN CÙNG để admin thấy ngay khi mở tab Config.
// =============================================
$_mbOn       = defined('MBBANK_AUTO_APPROVE_ENABLED') && MBBANK_AUTO_APPROVE_ENABLED;
$_mbCfgOk    = defined('MBBANK_HISTORY_API_KEY') && MBBANK_HISTORY_API_KEY !== '' && defined('BANK_ACCOUNT') && BANK_ACCOUNT !== '';
$_crOn       = defined('CRYPTO_AUTO_APPROVE_ENABLED') && CRYPTO_AUTO_APPROVE_ENABLED;
$_crCfgOk    = defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '';
?>
<div class="form-card" style="margin-bottom:16px;border:2px solid rgba(99,102,241,.35);background:linear-gradient(180deg,rgba(99,102,241,.06),transparent)">
  <h3 style="margin-bottom:6px">💳 Phương thức thanh toán</h3>
  <p style="color:#8b949e;font-size:13px;margin-bottom:14px">Bật/tắt từng phương thức bằng 1 click. Khách hàng chỉ thấy option đang BẬT ở màn checkout.</p>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px">
    <!-- MBBank card -->
    <div style="border:1px solid <?= $_mbOn ? 'rgba(34,197,94,.5)' : 'rgba(107,114,128,.4)' ?>;border-radius:10px;padding:14px;background:<?= $_mbOn ? 'rgba(34,197,94,.06)' : 'rgba(31,41,55,.5)' ?>">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="font-size:18px;font-weight:800">🏦 MBBank (VND)</div>
        <?php if ($_mbOn): ?>
          <span style="background:#16a34a;color:white;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:800">BẬT</span>
        <?php else: ?>
          <span style="background:#374151;color:#9ca3af;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:800">TẮT</span>
        <?php endif; ?>
      </div>
      <p style="color:#8b949e;font-size:12px;margin-bottom:10px">Auto duyệt qua API Queenvps → MBBank. Đối tượng: khách VN, thanh toán bằng VietQR.</p>
      <div style="margin-bottom:10px;font-size:12px">
        <?php if ($_mbCfgOk): ?>
          <span style="color:#86efac">✅ Đã cấu hình API + STK</span>
        <?php else: ?>
          <span style="color:#fde68a">⚠️ Thiếu <span class="mono">MBBANK_HISTORY_API_KEY</span> hoặc <span class="mono">BANK_ACCOUNT</span></span>
        <?php endif; ?>
      </div>
      <form method="POST" style="margin:0">
        <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
        <input type="hidden" name="act" value="toggle_payment">
        <input type="hidden" name="method" value="mbbank">
        <?php if ($_mbOn): ?>
          <button class="btn btn-red" type="submit" onclick="return confirm('Tắt MBBank? Khách sẽ không thấy option này nữa.')" style="width:100%">⛔ Tắt MBBank</button>
        <?php else: ?>
          <button class="btn btn-green" type="submit" style="width:100%" <?= !$_mbCfgOk ? 'disabled title="Thiếu config — không bật được"' : '' ?>>✅ Bật MBBank</button>
        <?php endif; ?>
      </form>
    </div>

    <!-- Binance card -->
    <div style="border:1px solid <?= $_crOn ? 'rgba(34,197,94,.5)' : 'rgba(107,114,128,.4)' ?>;border-radius:10px;padding:14px;background:<?= $_crOn ? 'rgba(34,197,94,.06)' : 'rgba(31,41,55,.5)' ?>">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="font-size:18px;font-weight:800">🪙 Binance USDT (TRC20)</div>
        <?php if ($_crOn): ?>
          <span style="background:#16a34a;color:white;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:800">BẬT</span>
        <?php else: ?>
          <span style="background:#374151;color:#9ca3af;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:800">TẮT</span>
        <?php endif; ?>
      </div>
      <p style="color:#8b949e;font-size:12px;margin-bottom:10px">Auto duyệt qua TronGrid blockchain. Đối tượng: khách quốc tế, không cần bank VN.</p>
      <div style="margin-bottom:10px;font-size:12px">
        <?php if ($_crCfgOk): ?>
          <span style="color:#86efac">✅ Đã nhập địa chỉ ví TRC20</span>
        <?php else: ?>
          <span style="color:#fde68a">⚠️ Chưa nhập <span class="mono">USDT_TRC20_ADDRESS</span> — kéo xuống panel "Binance USDT" bên dưới</span>
        <?php endif; ?>
      </div>
      <form method="POST" style="margin:0">
        <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
        <input type="hidden" name="act" value="toggle_payment">
        <input type="hidden" name="method" value="binance">
        <?php if ($_crOn): ?>
          <button class="btn btn-red" type="submit" onclick="return confirm('Tắt Binance? Khách sẽ không thấy option này nữa.')" style="width:100%">⛔ Tắt Binance</button>
        <?php else: ?>
          <button class="btn btn-green" type="submit" style="width:100%" <?= !$_crCfgOk ? 'disabled title="Thiếu địa chỉ ví — kéo xuống nhập trước"' : '' ?>>✅ Bật Binance</button>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>
<?php
// =============================================
// PANEL "🤖 Telegram bot" — re-set webhook + test gửi tin nhắn
// Hữu ích sau khi cài lại code / đổi domain / khi installer set webhook fail.
// =============================================
$_botCfg = defined('BOT_TOKEN') && BOT_TOKEN !== '' && strpos(BOT_TOKEN, 'your_bot') !== 0;
$_botWhUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/webhook.php';
?>
<div class="form-card" style="margin-bottom:16px;border:1px solid <?= $_botCfg ? 'rgba(59,130,246,.35)' : 'rgba(245,158,11,.45)' ?>">
  <h3>🤖 Telegram bot — Webhook & Test</h3>
  <?php if (!$_botCfg): ?>
    <div class="warnbox" style="background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.30);color:#fde68a">⚠️ <span class="mono">BOT_TOKEN</span> chưa cấu hình. Sửa <span class="mono">config.local.php</span> qua FTP rồi mới test được.</div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;margin-bottom:12px">
      <div><small style="color:#8b949e">Webhook URL</small><div class="mono" style="word-break:break-all"><?= h($_botWhUrl) ?></div></div>
      <div><small style="color:#8b949e">ADMIN_CHAT_ID</small><div class="mono"><?= defined('ADMIN_CHAT_ID') ? h((string)ADMIN_CHAT_ID) : '—' ?></div></div>
    </div>
  <?php endif; ?>
  <?php if (!empty($_GET['wh'])): ?><div class="codebox" style="margin:0 0 10px">✅ <?= htmlspecialchars((string)$_GET['wh']) ?></div><?php endif; ?>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <form method="POST" style="margin:0">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
      <input type="hidden" name="act" value="reset_webhook">
      <button class="btn btn-blue" type="submit" <?= !$_botCfg ? 'disabled' : '' ?>>🔄 Set lại Webhook</button>
    </form>
    <form method="POST" style="margin:0">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
      <input type="hidden" name="act" value="test_telegram">
      <button class="btn" type="submit" <?= !$_botCfg ? 'disabled' : '' ?>>📨 Gửi tin nhắn test</button>
    </form>
    <a class="btn" target="_blank" href="https://api.telegram.org/bot<?= $_botCfg ? h(BOT_TOKEN) : '' ?>/getWebhookInfo" <?= !$_botCfg ? 'style="pointer-events:none;opacity:.5"' : '' ?>>🔍 Xem getWebhookInfo</a>
  </div>
  <small style="color:#8b949e;display:block;margin-top:8px">Dùng khi: cài lại code, đổi domain, bot không phản hồi, installer set webhook fail.</small>
</div>
<?php
// Panel observability MBBank poll — đọc data/mbbank_poll_status.json
$_mbStatusFile = APP_ROOT . '/data/mbbank_poll_status.json';
$_mbS = is_file($_mbStatusFile) ? json_decode((string)@file_get_contents($_mbStatusFile), true) : null;
if (!is_array($_mbS)) $_mbS = null;
$_mbAgo = '—';
if ($_mbS && !empty($_mbS['last_run_at'])) {
    $_t = strtotime($_mbS['last_run_at']);
    if ($_t) {
        $d = max(0, time() - $_t);
        if ($d < 60) $_mbAgo = $d . 's trước';
        elseif ($d < 3600) $_mbAgo = floor($d/60) . ' phút trước';
        elseif ($d < 86400) $_mbAgo = floor($d/3600) . ' giờ trước';
        else $_mbAgo = floor($d/86400) . ' ngày trước';
    }
}
$_mbFresh = ($_mbS && !empty($_mbS['last_run_at']) && (time() - strtotime($_mbS['last_run_at'])) < 180);
?>
<div class="form-card" style="margin-bottom:16px;border:1px solid <?= $_mbFresh ? 'rgba(34,197,94,.35)' : 'rgba(245,158,11,.35)' ?>">
  <h3>🏦 MBBank Poll — Quan sát</h3>
  <?php if (!$_mbS): ?>
    <p style="color:#fde68a">Chưa có dữ liệu poll. Nhấn <b>Test Poll Ngay</b> hoặc đợi cron chạy cron/mbbank_poll.php.</p>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:12px">
      <div><small style="color:#8b949e">Lần chạy cuối</small><div style="font-weight:800;color:<?= $_mbFresh ? '#bbf7d0' : '#fde68a' ?>"><?= h($_mbAgo) ?></div><div class="mono" style="font-size:11px;color:#6b7f9e"><?= h($_mbS['last_run_at']) ?></div></div>
      <div><small style="color:#8b949e">Nguồn</small><div style="font-weight:800"><?= h($_mbS['source'] ?? '—') ?></div></div>
      <div><small style="color:#8b949e">Thời gian xử lý</small><div style="font-weight:800"><?= h((string)($_mbS['duration_ms'] ?? '—')) ?> ms</div></div>
      <div><small style="color:#8b949e">TX mới (seen)</small><div style="font-weight:800;color:#67e8f9"><?= (int)($_mbS['seen_new'] ?? 0) ?></div></div>
      <div><small style="color:#8b949e">Match ORD</small><div style="font-weight:800;color:#67e8f9"><?= (int)($_mbS['matched'] ?? 0) ?></div></div>
      <div><small style="color:#8b949e">Đã duyệt</small><div style="font-weight:800;color:#bbf7d0"><?= (int)($_mbS['approved'] ?? 0) ?></div></div>
    </div>
    <?php if (!empty($_mbS['skipped'])): ?>
      <div class="warnbox" style="margin:0 0 10px">⏭️ Lần chạy này bị bỏ qua: <?= h((string)($_mbS['error'] ?? 'skipped')) ?></div>
    <?php elseif (!empty($_mbS['error'])): ?>
      <div class="warnbox" style="margin:0 0 10px;background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.30);color:#fecaca">❌ Lỗi: <?= h((string)$_mbS['error']) ?></div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if(isset($_GET['mbtest'])): ?><div class="codebox" style="margin:0 0 10px">📊 <?= htmlspecialchars((string)$_GET['mbtest']) ?></div><?php endif; ?>
  <form method="POST" style="display:flex;gap:10px;align-items:center">
    <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
    <input type="hidden" name="act" value="mbbank_test_poll">
    <button class="btn btn-blue" type="submit">▶️ Test Poll Ngay</button>
    <small style="color:#8b949e">Gọi <span class="mono">cron/mbbank_poll.php</span> đồng bộ qua cURL — kết quả hiển thị ngay phía trên.</small>
  </form>
</div>
<?php
// =============================================
// Panel observability Crypto poll — mirror MBBank, đọc data/crypto_poll_status.json
// =============================================
$_crStatusFile = APP_ROOT . '/data/crypto_poll_status.json';
$_crS = is_file($_crStatusFile) ? json_decode((string)@file_get_contents($_crStatusFile), true) : null;
if (!is_array($_crS)) $_crS = null;
$_crAgo = '—';
if ($_crS && !empty($_crS['last_run_at'])) {
    $_t = strtotime($_crS['last_run_at']);
    if ($_t) {
        $d = max(0, time() - $_t);
        if ($d < 60) $_crAgo = $d . 's trước';
        elseif ($d < 3600) $_crAgo = floor($d/60) . ' phút trước';
        elseif ($d < 86400) $_crAgo = floor($d/3600) . ' giờ trước';
        else $_crAgo = floor($d/86400) . ' ngày trước';
    }
}
$_crFresh = ($_crS && !empty($_crS['last_run_at']) && (time() - strtotime($_crS['last_run_at'])) < 180);
?>
<div class="form-card" style="margin-bottom:16px;border:1px solid <?= $_crFresh ? 'rgba(34,197,94,.35)' : 'rgba(245,158,11,.35)' ?>">
  <h3>🪙 Crypto Poll (Binance USDT TRC20) — Quan sát</h3>
  <?php if (!$_crS): ?>
    <p style="color:#fde68a">Chưa có dữ liệu poll. Nhập <span class="mono">USDT_TRC20_ADDRESS</span> ở panel bên dưới rồi bấm <b>Test Poll Ngay</b>, hoặc đợi cron chạy cron/crypto_poll.php.</p>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:12px">
      <div><small style="color:#8b949e">Lần chạy cuối</small><div style="font-weight:800;color:<?= $_crFresh ? '#bbf7d0' : '#fde68a' ?>"><?= h($_crAgo) ?></div><div class="mono" style="font-size:11px;color:#6b7f9e"><?= h($_crS['last_run_at']) ?></div></div>
      <div><small style="color:#8b949e">Nguồn</small><div style="font-weight:800"><?= h($_crS['source'] ?? '—') ?></div></div>
      <div><small style="color:#8b949e">Thời gian xử lý</small><div style="font-weight:800"><?= h((string)($_crS['duration_ms'] ?? '—')) ?> ms</div></div>
      <div><small style="color:#8b949e">TX mới (seen)</small><div style="font-weight:800;color:#67e8f9"><?= (int)($_crS['seen_new'] ?? 0) ?></div></div>
      <div><small style="color:#8b949e">Match đơn</small><div style="font-weight:800;color:#67e8f9"><?= (int)($_crS['matched'] ?? 0) ?></div></div>
      <div><small style="color:#8b949e">Đã duyệt</small><div style="font-weight:800;color:#bbf7d0"><?= (int)($_crS['approved'] ?? 0) ?></div></div>
    </div>
    <?php if (!empty($_crS['skipped'])): ?>
      <div class="warnbox" style="margin:0 0 10px">⏭️ Lần chạy này bị bỏ qua: <?= h((string)($_crS['error'] ?? 'skipped')) ?></div>
    <?php elseif (!empty($_crS['error'])): ?>
      <div class="warnbox" style="margin:0 0 10px;background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.30);color:#fecaca">❌ Lỗi: <?= h((string)$_crS['error']) ?></div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if(isset($_GET['crtest'])): ?><div class="codebox" style="margin:0 0 10px">📊 <?= htmlspecialchars((string)$_GET['crtest']) ?></div><?php endif; ?>
  <form method="POST" style="display:flex;gap:10px;align-items:center">
    <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
    <input type="hidden" name="act" value="crypto_test_poll">
    <button class="btn btn-blue" type="submit">▶️ Test Poll Ngay</button>
    <small style="color:#8b949e">Gọi <span class="mono">cron/crypto_poll.php</span> đồng bộ — fetch lịch sử giao dịch TRC20 mới nhất.</small>
  </form>
</div>
<?php
// Bảng trạng thái cron jobs - đọc data/cron_status_{job}.json (do cron/run.php ghi)
$_cronJobs = [
    'mbbank'      => ['label' => '🏦 MBBANK Auto-bank',    'sched' => '*/1m',  'fresh_sec' => 180],
    'crypto'      => ['label' => '🪙 Crypto Auto-USDT',     'sched' => '*/1m',  'fresh_sec' => 180],
    'card'        => ['label' => '🎴 Card doithe poll',     'sched' => '*/1m',  'fresh_sec' => 180],
    'maintenance' => ['label' => '🧹 Maintenance',         'sched' => '*/5m',  'fresh_sec' => 600],
    'monitor'     => ['label' => '📊 Monitor',             'sched' => '*/5m',  'fresh_sec' => 600],
    'automation'  => ['label' => '🤖 Automation Daily',    'sched' => '8h',    'fresh_sec' => 90000],
    'health'      => ['label' => '🏥 Health Check',        'sched' => '9h',    'fresh_sec' => 90000],
    'backup'      => ['label' => '💾 DB Backup',           'sched' => '3h',    'fresh_sec' => 90000],
];
function _hclouCronAgo($iso) {
    if (!$iso) return '—';
    $t = strtotime($iso);
    if (!$t) return '—';
    $d = max(0, time() - $t);
    if ($d < 60) return $d . 's trước';
    if ($d < 3600) return floor($d/60) . 'p trước';
    if ($d < 86400) return floor($d/3600) . 'h trước';
    return floor($d/86400) . 'd trước';
}
?>
<div class="form-card" style="margin-bottom:16px">
  <h3>⏱️ Trạng thái Cron Jobs</h3>
  <p style="color:#8b949e;font-size:13px;margin-bottom:10px">Đọc snapshot từ <span class="mono">data/cron_status_*.json</span> (do <span class="mono">cron/run.php</span> ghi sau mỗi lần chạy). Đỏ = quá hạn / lỗi, xanh = OK.</p>
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <tr><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Job</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Schedule</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Lần cuối</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Thời gian</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Kết quả</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Detail</th></tr>
  <?php foreach ($_cronJobs as $jkey => $jinfo):
    $jfile = APP_ROOT . '/data/cron_status_' . $jkey . '.json';
    $jdata = is_file($jfile) ? json_decode((string)@file_get_contents($jfile), true) : null;
    $jdata = is_array($jdata) ? $jdata : null;
    $jLast = $jdata['last_run_at'] ?? null;
    $jAge  = $jLast ? (time() - strtotime($jLast)) : null;
    $jStale = ($jAge === null) || ($jAge > $jinfo['fresh_sec']);
    $jOk   = $jdata && !empty($jdata['success']) && !$jStale && empty($jdata['skipped']);
    $jColor = $jOk ? '#3fb950' : ($jStale ? '#f85149' : '#fbbf24');
    $jStatus = !$jdata ? 'CHƯA CHẠY' : (!empty($jdata['skipped']) ? 'SKIPPED' : (!empty($jdata['success']) ? 'OK' : 'FAIL'));
  ?>
    <tr>
      <td style="padding:8px;border-bottom:1px solid #1f2937"><b><?=h($jinfo['label'])?></b><br><small class="mono" style="color:#6b7f9e"><?=h($jkey)?></small></td>
      <td style="padding:8px;border-bottom:1px solid #1f2937"><span class="mono"><?=h($jinfo['sched'])?></span></td>
      <td style="padding:8px;border-bottom:1px solid #1f2937;color:<?=$jStale?'#f85149':'#bbf7d0'?>"><?= h(_hclouCronAgo($jLast)) ?></td>
      <td style="padding:8px;border-bottom:1px solid #1f2937" class="mono"><?= h((string)($jdata['duration_ms'] ?? '—')) ?>ms</td>
      <td style="padding:8px;border-bottom:1px solid #1f2937;color:<?=$jColor?>;font-weight:800"><?=h($jStatus)?> <?= !empty($jdata['http_code']) ? '<span style="color:#8b949e;font-weight:400">'.(int)$jdata['http_code'].'</span>' : '' ?></td>
      <td style="padding:8px;border-bottom:1px solid #1f2937;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="mono" title="<?=h((string)($jdata['detail'] ?? ''))?>"><?= h(substr((string)($jdata['detail'] ?? ''), 0, 80)) ?></td>
    </tr>
  <?php endforeach; ?>
  </table>
  <p style="margin-top:10px;color:#8b949e;font-size:12px">💡 Job đỏ = không nhận tín hiệu lâu hơn ngưỡng → kiểm tra cron-job.org / cron cPanel có chạy URL không.</p>
</div>
<?php
// Backup DB panel
$_bkDir   = APP_ROOT . '/data/backups';
$_bkLast  = is_file($_bkDir . '/_last_backup.json') ? json_decode((string)@file_get_contents($_bkDir . '/_last_backup.json'), true) : null;
$_bkFiles = is_dir($_bkDir) ? (glob($_bkDir . '/db_*.sql.gz') ?: []) : [];
usort($_bkFiles, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
function _hclouFormatBytes($b) {
    if ($b < 1024) return $b . ' B';
    if ($b < 1024*1024) return round($b/1024, 1) . ' KB';
    return round($b/1024/1024, 2) . ' MB';
}
?>
<div class="form-card" style="margin-bottom:16px">
  <h3>💾 Backup Database</h3>
  <p style="color:#8b949e;font-size:13px;margin-bottom:10px">Daily dump SQL nén gzip. Giữ 7 backup gần nhất, tự xoá file cũ. Khuyến nghị: thêm cron URL bên dưới chạy 3h sáng hàng ngày.</p>
  <?php if ($_bkLast && !empty($_bkLast['success'])): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:12px">
      <div><small style="color:#8b949e">Lần backup cuối</small><div style="font-weight:800;color:#bbf7d0"><?= h(_hclouCronAgo($_bkLast['last_run_at'] ?? null)) ?></div></div>
      <div><small style="color:#8b949e">Bảng</small><div style="font-weight:800"><?= (int)($_bkLast['tables'] ?? 0) ?></div></div>
      <div><small style="color:#8b949e">Rows</small><div style="font-weight:800"><?= number_format((int)($_bkLast['rows'] ?? 0)) ?></div></div>
      <div><small style="color:#8b949e">Size</small><div style="font-weight:800;color:#67e8f9"><?= h(_hclouFormatBytes((int)($_bkLast['size_bytes'] ?? 0))) ?></div></div>
      <div><small style="color:#8b949e">Đã giữ</small><div style="font-weight:800"><?= (int)($_bkLast['kept'] ?? count($_bkFiles)) ?></div></div>
      <div><small style="color:#8b949e">Đã xoá cũ</small><div style="font-weight:800;color:#fbbf24"><?= (int)($_bkLast['deleted_old'] ?? 0) ?></div></div>
    </div>
  <?php elseif ($_bkLast && empty($_bkLast['success'])): ?>
    <div class="warnbox" style="margin:0 0 12px;background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.30);color:#fecaca">❌ Backup lỗi: <?= h((string)($_bkLast['error'] ?? '')) ?></div>
  <?php else: ?>
    <p style="color:#fde68a">Chưa có backup. Nhấn nút bên dưới hoặc đợi cron chạy lần đầu.</p>
  <?php endif; ?>

  <form method="POST" style="display:flex;gap:10px;align-items:center;margin-bottom:14px">
    <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
    <input type="hidden" name="act" value="db_backup_now">
    <button class="btn btn-blue" type="submit" onclick="return confirm('Tạo backup ngay? (có thể mất vài giây)')">💾 Backup ngay</button>
    <small style="color:#8b949e">Cron URL: <span class="mono" style="word-break:break-all"><?= h(rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=backup') ?></span></small>
  </form>

  <?php if ($_bkFiles): ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">File</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Tạo lúc</th><th style="text-align:left;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Size</th><th style="text-align:right;padding:8px;color:#8b949e;border-bottom:1px solid var(--line)">Hành động</th></tr>
      <?php foreach ($_bkFiles as $bf):
        $base = basename($bf);
        $mt   = filemtime($bf);
      ?>
      <tr>
        <td style="padding:8px;border-bottom:1px solid #1f2937" class="mono"><?=h($base)?></td>
        <td style="padding:8px;border-bottom:1px solid #1f2937"><?=h(date('Y-m-d H:i:s', $mt))?> <small style="color:#8b949e">(<?=h(_hclouCronAgo(date('c', $mt)))?>)</small></td>
        <td style="padding:8px;border-bottom:1px solid #1f2937"><?=h(_hclouFormatBytes((int)filesize($bf)))?></td>
        <td style="padding:8px;border-bottom:1px solid #1f2937;text-align:right">
          <a class="btn btn-green" href="download_backup.php?f=<?=urlencode($base)?>" style="padding:6px 10px;font-size:12px">⬇ Tải</a>
          <form method="POST" style="display:inline">
            <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
            <input type="hidden" name="act" value="db_backup_delete">
            <input type="hidden" name="file" value="<?=h($base)?>">
            <button class="btn btn-red" type="submit" style="padding:6px 10px;font-size:12px" onclick="return confirm('Xoá backup <?=h($base)?>?')">🗑</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
  <p style="margin-top:10px;color:#8b949e;font-size:12px">⚠️ File backup chứa dữ liệu nhạy cảm (users, orders, key). Tải về xong nên lưu offline, không share.</p>
</div>

<!-- Đổi tài khoản/mật khẩu admin -->
<form method="POST" class="form-card">
<input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="change_admin_cred">
<h3>🔐 Đổi đăng nhập admin</h3>
<p style="color:#8b949e;font-size:12.5px;margin-bottom:14px">Tài khoản hiện tại: <b style="color:#7db3ff"><?=h(defined('ADMIN_USERNAME')?ADMIN_USERNAME:'admin')?></b>. Phải nhập đúng mật khẩu hiện tại để xác nhận.</p>
<div class="form-row">
  <div style="flex:1;min-width:200px"><label>Mật khẩu hiện tại *</label><input type="password" name="cur_pw" required placeholder="Nhập mật khẩu đang dùng" autocomplete="current-password"></div>
  <div style="flex:1;min-width:200px"><label>Tài khoản mới *</label><input type="text" name="new_username" required value="<?=h(defined('ADMIN_USERNAME')?ADMIN_USERNAME:'admin')?>" placeholder="vd: admin" autocapitalize="none"></div>
</div>
<div class="form-row" style="margin-top:12px">
  <div style="flex:1;min-width:200px"><label>Mật khẩu mới <small style="color:#8b949e">(để trống nếu không đổi)</small></label><input type="password" name="new_pw" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password"></div>
  <div style="flex:1;min-width:200px"><label>Nhập lại mật khẩu mới</label><input type="password" name="new_pw2" placeholder="Nhập lại" autocomplete="new-password"></div>
</div>
<div style="margin-top:16px"><button class="btn btn-green" type="submit" data-confirm="Đổi tài khoản/mật khẩu đăng nhập admin?">💾 Lưu đăng nhập</button></div>
</form>

<form method="POST" class="form-card">
<input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="save_config">
<h3>Thông tin site/bot</h3><div class="form-row">
<?php foreach(['SITE_URL'=>'Site URL','SITE_NAME'=>'Site name','ADMIN_CHAT_ID'=>'Admin chat ID','BOT_USERNAME'=>'Bot username'] as $k=>$label): ?>
<div><label><?=$label?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars((string)hclouConfigValue($k))?>"></div>
<?php endforeach; ?></div>
<h3 style="margin-top:20px">📞 Footer trang web (khách thấy)</h3><div class="form-row">
<?php foreach(['FOOTER_HOTLINE'=>'Hotline / SĐT','FOOTER_EMAIL'=>'Email liên hệ','FOOTER_RESP_CONTENT'=>'Chịu trách nhiệm nội dung','FOOTER_TELEGRAM'=>'Link Telegram'] as $k=>$label): ?>
<div style="flex:1;min-width:240px"><label><?=$label?></label><input style="width:100%" name="cfg[<?=$k?>]" value="<?=htmlspecialchars((string)hclouConfigValue($k))?>"></div>
<?php endforeach; ?></div>
<h3 style="margin-top:20px">Bank / VietQR</h3><div class="form-row">
<?php foreach(['BANK_NAME'=>'Ngân hàng','BANK_ACCOUNT'=>'Số tài khoản','BANK_OWNER'=>'Chủ tài khoản','VIETQR_BANK_ID'=>'VietQR bank BIN'] as $k=>$label): ?>
<div><label><?=$label?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars((string)hclouConfigValue($k))?>"></div>
<?php endforeach; ?></div>
<h3 style="margin-top:20px">API / Auto-bank / GetKey Free</h3><div class="form-row">
<div style="flex:1;min-width:300px"><label>MBBANK API Key</label><input style="width:100%" name="cfg[MBBANK_HISTORY_API_KEY]" value="<?=htmlspecialchars((string)hclouConfigValue('MBBANK_HISTORY_API_KEY'))?>" placeholder="Nhập API Key từ Queenvps"><small>API Queenvps: <code>GET https://queenvps.com/api/historymb/{API_KEY}</code>. Liên hệ Zalo/Messenger/Hotline để lấy key.</small></div>
<div><label>Auto-bank</label><select name="cfg[MBBANK_AUTO_APPROVE_ENABLED]"><option value="1" <?=MBBANK_AUTO_APPROVE_ENABLED?'selected':''?>>Bật</option><option value="0" <?=!MBBANK_AUTO_APPROVE_ENABLED?'selected':''?>>Tắt</option></select></div>
<div><label>GetKey Free</label><select name="cfg[FREE_GETKEY_ENABLED]"><option value="1" <?=FREE_GETKEY_ENABLED?'selected':''?>>Bật</option><option value="0" <?=!FREE_GETKEY_ENABLED?'selected':''?>>Tắt</option></select></div>
<div><label>Số lớp vượt link</label><select name="cfg[FREE_SHORTLINK_LAYERS]"><?php $_curLayers = defined('FREE_SHORTLINK_LAYERS') ? (int)FREE_SHORTLINK_LAYERS : 2; ?><option value="1" <?=$_curLayers===1?'selected':''?>>1 lớp (Layma)</option><option value="2" <?=$_curLayers===2?'selected':''?>>2 lớp (Layma + Link4M)</option></select></div>
<div><label>Bắt vượt link (getkey web)</label><select name="cfg[GETKEY_REQUIRE_LINK]"><?php $_reqLink = !defined('GETKEY_REQUIRE_LINK') || GETKEY_REQUIRE_LINK; ?><option value="1" <?=$_reqLink?'selected':''?>>Bật (phải vượt link)</option><option value="0" <?=!$_reqLink?'selected':''?>>Tắt (hiện key luôn)</option></select></div>
<div style="flex:1;min-width:260px"><label>Layma token</label><input style="width:100%" name="cfg[LAYMA_API_TOKEN]" value="<?=htmlspecialchars((string)hclouConfigValue('LAYMA_API_TOKEN'))?>" placeholder="7fc1aa570262544a7b80d1bc0ab3c4e6"><small>Lấy tại <a href="https://layma.net" target="_blank">layma.net</a> → Developer → API Token</small></div>
<div style="flex:1;min-width:260px"><label>Link4M token (chỉ cần khi 2 lớp)</label><input style="width:100%" name="cfg[LINK4M_API_TOKEN]" value="<?=htmlspecialchars((string)hclouConfigValue('LINK4M_API_TOKEN'))?>"></div>
</div>

<h3 style="margin-top:20px">🪙 Binance USDT TRC20</h3>
<div class="form-row">
<div style="flex:1;min-width:300px"><label>Địa chỉ ví USDT TRC20</label><input style="width:100%;font-family:monospace" name="cfg[USDT_TRC20_ADDRESS]" value="<?=htmlspecialchars((string)hclouConfigValue('USDT_TRC20_ADDRESS'))?>" placeholder="Tdxxxxxxxxxxxxxxxxxxxxxx (bắt đầu bằng T)"></div>
<div style="flex:1;min-width:260px"><label>TronGrid API Key <span style="color:#8b949e;font-weight:400">(tuỳ chọn)</span></label><input style="width:100%;font-family:monospace" name="cfg[TRONGRID_API_KEY]" value="<?=htmlspecialchars((string)hclouConfigValue('TRONGRID_API_KEY'))?>" placeholder="Để trống vẫn dùng được, có key thì rate limit cao hơn"></div>
<div><label>Auto-crypto</label><select name="cfg[CRYPTO_AUTO_APPROVE_ENABLED]"><option value="1" <?=(defined('CRYPTO_AUTO_APPROVE_ENABLED') && CRYPTO_AUTO_APPROVE_ENABLED)?'selected':''?>>Bật</option><option value="0" <?=(!defined('CRYPTO_AUTO_APPROVE_ENABLED') || !CRYPTO_AUTO_APPROVE_ENABLED)?'selected':''?>>Tắt</option></select></div>
</div>
<div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.25);border-radius:8px;padding:12px;margin-top:8px;font-size:13px;line-height:1.7">
  <b style="color:#a5b4fc">📘 Cách lấy 2 thông số trên:</b><br>
  <br>
  <b style="color:#bbf7d0">1️⃣ USDT_TRC20_ADDRESS (địa chỉ ví nhận USDT)</b><br>
  • <b>Cách A — Sàn Binance:</b> Mở app Binance → Wallet → Spot Wallet → Deposit → chọn coin <span class="mono">USDT</span> → chọn network <b style="color:#fbbf24">Tron (TRC20)</b> → Copy địa chỉ (bắt đầu bằng chữ <span class="mono">T</span>).<br>
  • <b>Cách B — Sàn khác:</b> OKX / Bybit / MEXC đều có flow tương tự (Deposit → USDT → TRC20).<br>
  • <b>Cách C — Ví tự quản (không cần KYC, không cần đủ 18 tuổi):</b> Tải <b>TronLink</b> (https://tronlink.org) hoặc <b>Trust Wallet</b> → tạo ví mới → copy địa chỉ TRON.<br>
  ⚠️ <b style="color:#fca5a5">Bắt buộc là TRC20</b>, KHÔNG phải BEP20/ERC20. Sai mạng = mất tiền.<br>
  <br>
  <b style="color:#bbf7d0">2️⃣ TRONGRID_API_KEY (tuỳ chọn — để trống vẫn chạy được)</b><br>
  • Vào <a href="https://www.trongrid.io/" target="_blank" style="color:#67e8f9">https://www.trongrid.io/</a> → Sign Up bằng email (chỉ xác thực email, KHÔNG cần KYC).<br>
  • Login → Dashboard → <b>Create an App</b> → đặt tên gì cũng được (vd <span class="mono">HCLOU</span>).<br>
  • Copy <b>API Key</b> hiện ra → paste vào ô trên.<br>
  • Free tier: 100.000 request/ngày — quá đủ cho cron 1 phút/lần.<br>
  <br>
  <b style="color:#fde68a">💡 Lưu ý kỹ thuật:</b><br>
  • Khách thanh toán bằng Binance USDT: hệ thống tự convert VND → USDT theo tỉ giá CoinGecko realtime (cache 5 phút).<br>
  • Mỗi đơn được cộng thêm số lẻ thập phân duy nhất (vd <span class="mono">2.001234 USDT</span> cho đơn #1234) để hệ thống phân biệt đơn khi nhiều khách chuyển cùng số tròn.<br>
  • Khi cả 2 ô trên đã điền + bật <b>Auto-crypto</b>, option Binance mới hiện ở Mini App của khách.
</div>

<h3 style="margin-top:20px">🔗 License Panel API (nguồn key tự động)</h3>
<div class="form-row">
<div style="flex:1;min-width:320px"><label>API URL panel</label><input id="hcApiUrl" style="width:100%;font-family:monospace" name="cfg[HCLOU_API_URL]" value="<?=htmlspecialchars((string)hclouConfigValue('HCLOU_API_URL'))?>" placeholder="https://panel.domain.com/api/sell"><small style="color:#8b949e">URL gốc API bán key của panel (kết thúc bằng <code>/api/sell</code>).</small></div>
<div style="flex:1;min-width:260px"><label>API Token</label><input id="hcApiTok" style="width:100%;font-family:monospace" name="cfg[HCLOU_API_TOKEN]" value="<?=htmlspecialchars((string)hclouConfigValue('HCLOU_API_TOKEN'))?>" placeholder="Token tạo trong panel"></div>
</div>
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <button type="button" class="btn btn-gray" onclick="hcTestApi()">🔍 Kiểm tra API (xem game/gói + số dư)</button>
  <small style="color:#9fb2cf">Test trực tiếp giá trị đang gõ — không cần Lưu trước.</small>
</div>
<div id="apitest" class="codebox" style="margin-top:10px;max-height:340px;overflow:auto;display:none"></div>
<script>
async function hcTestApi(){
  var url=document.getElementById('hcApiUrl').value.trim();
  var tok=document.getElementById('hcApiTok').value.trim();
  var box=document.getElementById('apitest');
  box.style.display='block';
  if(!url||!tok){ box.innerHTML='❌ Nhập đủ URL + Token trước.'; return; }
  box.innerHTML='⏳ Đang kiểm tra...';
  try{
    var fd=new FormData();
    fd.append('csrf','<?=h($_SESSION['admin_csrf'])?>');
    fd.append('act','test_hclou_api');
    fd.append('api_url',url);
    fd.append('api_token',tok);
    var r=await fetch('?tab=sysconfig',{method:'POST',body:fd});
    var d=await r.json();
    if(!d.ok){ box.innerHTML='❌ '+(d.msg||'Lỗi')+(d.http?' (HTTP '+d.http+')':''); return; }
    // Kết nối OK -> TỰ LƯU URL + token vào config
    var saved = '';
    try{
      var sf=new FormData();
      sf.append('csrf','<?=h($_SESSION['admin_csrf'])?>');
      sf.append('act','save_config');
      sf.append('cfg[HCLOU_API_URL]',url);
      sf.append('cfg[HCLOU_API_TOKEN]',tok);
      await fetch('?tab=sysconfig',{method:'POST',body:sf});
      saved = ' <span style="color:#6ee7b7">💾 Đã lưu cấu hình.</span>';
    }catch(e){ saved=' <span style="color:#fbbf24">⚠️ Lưu config lỗi, bấm Lưu cấu hình thủ công.</span>'; }
    var html='✅ <b style="color:#6ee7b7">Kết nối OK!</b>'+saved+' Số dư panel: <b style="color:#fbbf24">$'+Number(d.balance||0).toLocaleString()+'</b><br><br><b>Game / gói khả dụng</b> (chọn sẵn khi thêm gói nguồn API):';
    (d.games||[]).forEach(function(g){
      html+='<br>🎮 <b>'+g.name+'</b> · <code style="color:#7db3ff">'+g.game+'</code>';
      (g.packages||[]).forEach(function(pk){
        html+='<br>&nbsp;&nbsp;&nbsp;• <code>'+pk.duration+'</code> giờ ('+(pk.label||'')+') — giá panel: $'+pk.price;
      });
    });
    box.innerHTML=html;
  }catch(e){ box.innerHTML='❌ Lỗi gọi: '+e; }
}
</script>

<h3 style="margin-top:20px">💳 Nạp card qua doithe.vn (Ví user)</h3>
<div class="form-row">
<div style="flex:1;min-width:220px"><label>Partner ID</label><input style="width:100%;font-family:monospace" name="cfg[DOITHE_PARTNER_ID]" value="<?=htmlspecialchars((string)hclouConfigValue('DOITHE_PARTNER_ID'))?>" placeholder="Mã đối tác doithe.vn"></div>
<div style="flex:1;min-width:260px"><label>Partner Key</label><input style="width:100%;font-family:monospace" name="cfg[DOITHE_PARTNER_KEY]" value="<?=htmlspecialchars((string)hclouConfigValue('DOITHE_PARTNER_KEY'))?>" placeholder="Secret key để ký md5"></div>
</div>
<div class="form-row" style="margin-top:10px">
<div style="flex:1;min-width:320px"><label>API URL (domain_post)</label><input style="width:100%;font-family:monospace" name="cfg[DOITHE_API_URL]" value="<?=htmlspecialchars((string)(hclouConfigValue('DOITHE_API_URL') ?: 'https://doithe.vn/chargingws/v2'))?>" placeholder="https://domain-cua-ban/chargingws/v2"><small style="color:#8b949e">Lấy <b>domain_post</b> trong trang merchant doithe.vn. Phải có đuôi <code>/chargingws/v2</code>. Sai domain = thẻ bị từ chối.</small></div>
</div>
<div class="form-row" style="margin-top:8px">
<div style="flex:1;min-width:130px"><label>Chiết khấu Viettel (%)</label><input style="width:100%" name="cfg[CARD_RATE_VIETTEL]" value="<?=htmlspecialchars((string)hclouConfigValue('CARD_RATE_VIETTEL') ?: '28')?>" placeholder="28"></div>
<div style="flex:1;min-width:130px"><label>Chiết khấu Mobifone (%)</label><input style="width:100%" name="cfg[CARD_RATE_MOBIFONE]" value="<?=htmlspecialchars((string)hclouConfigValue('CARD_RATE_MOBIFONE') ?: '30')?>" placeholder="30"></div>
<div style="flex:1;min-width:130px"><label>Chiết khấu Vinaphone (%)</label><input style="width:100%" name="cfg[CARD_RATE_VINAPHONE]" value="<?=htmlspecialchars((string)hclouConfigValue('CARD_RATE_VINAPHONE') ?: '30')?>" placeholder="30"></div>
</div>
<div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.3);border-radius:8px;padding:12px;margin-top:8px;font-size:13px;line-height:1.7">
  <b style="color:#fde68a">🔔 Callback URL cấu hình bên doithe.vn:</b><br>
  <br>
  <span style="background:#1f2937;color:#34d399;padding:4px 8px;border-radius:4px;font-weight:700">POST</span>
  &nbsp;<span class="mono" style="word-break:break-all;color:#67e8f9"><?=htmlspecialchars(rtrim(SITE_URL,'/').'/card_callback.php')?></span>
  <br><br>
  • Vào trang merchant <b>doithe.vn</b> → mục <b>Cấu hình callback / Webhook URL</b> → paste URL ở trên.<br>
  • Method: <b>POST</b>. Body dạng <span class="mono">application/x-www-form-urlencoded</span> hoặc JSON — code đã handle cả 2.<br>
  • Sign md5 verify dùng <b>Partner Key</b> ở trên — KHÔNG paste key này lên đâu khác.<br>
  <br>
  <b style="color:#fde68a">💡 Cách tính tiền vào ví:</b><br>
  • Khách chọn nhà mạng + mệnh giá → hệ thống nạp lên doithe.vn → tiền vào ví = mệnh giá × (1 − rate%).<br>
  • Vd: thẻ Viettel 100.000đ, rate 28% → ví nhận 72.000đ. Mobifone 100.000đ, rate 30% → ví nhận 70.000đ.<br>
  • Sai mệnh giá: nếu provider trả về value khác face khách nhập, vẫn cộng theo value thực × (1 − rate%) — không bị mất tiền hay over-credit.<br>
  • Đổi rate đúng với chiết khấu doithe.vn áp cho bạn (xem trên trang merchant).
</div>

<h3 style="margin-top:20px">Cron Tokens</h3><div class="form-row">
<div style="flex:1;min-width:260px"><label>CRON_RUN_TOKEN</label><input style="width:100%;font-family:monospace" name="cfg[CRON_RUN_TOKEN]" value="<?=htmlspecialchars((string)hclouConfigValue('CRON_RUN_TOKEN'))?>"></div>
<div style="flex:1;min-width:260px"><label>AUTOMATION_RUN_TOKEN</label><input style="width:100%;font-family:monospace" name="cfg[AUTOMATION_RUN_TOKEN]" value="<?=htmlspecialchars((string)hclouConfigValue('AUTOMATION_RUN_TOKEN'))?>"></div>
</div>
<h3 style="margin-top:20px">⚡ Cron Jobs — URL cần cấu hình</h3>
<p style="color:#8b949e;font-size:13px;margin-bottom:12px">Copy các URL dưới đây vào <a href="https://cron-job.org" target="_blank" style="color:#58a6ff">cron-job.org</a> (hoặc cPanel Cron Jobs). Tất cả chạy qua HTTP, không cần SSH/exec().</p>
<div class="codebox"><?php
$cronJobs = [
    ['label'=>'🏦 MBBANK Auto-bank', 'schedule'=>'Mỗi 1 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=mbbank'],
    ['label'=>'🪙 Crypto Auto-USDT', 'schedule'=>'Mỗi 1 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=crypto'],
    ['label'=>'🎴 Card doithe poll', 'schedule'=>'Mỗi 1 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=card'],
    ['label'=>'🧹 Maintenance', 'schedule'=>'Mỗi 5 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=maintenance'],
    ['label'=>'📊 Monitor', 'schedule'=>'Mỗi 5 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=monitor'],
    ['label'=>'🤖 Automation Daily', 'schedule'=>'8h sáng hàng ngày', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=automation'],
    ['label'=>'🏥 Health Check', 'schedule'=>'9h sáng hàng ngày', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=health'],
    ['label'=>'💾 DB Backup', 'schedule'=>'3h sáng hàng ngày', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=backup'],
];
foreach($cronJobs as $cj){
    echo '<div style="margin-bottom:8px;padding:6px 0;border-bottom:1px solid #21262d">';
    echo '<b>'.htmlspecialchars($cj['label']).'</b> <span style="color:#8b949e">('.htmlspecialchars($cj['schedule']).')</span><br>';
    echo '<span class="mono" style="word-break:break-all">'.htmlspecialchars($cj['url']).'</span>';
    echo '</div>';
}
?></div>
<div class="warnbox">⚠️ Quan trọng nhất: <b>MBBANK Auto-bank</b> (mỗi 1 phút) — nếu không chạy, đơn thanh toán sẽ không tự duyệt. Setup xong, kiểm tra tại <a href="../setup_cron.php" target="_blank" style="color:#58a6ff">setup_cron.php</a>.</div>
<div style="margin-top:18px"><button class="btn btn-green" type="submit">💾 Lưu cấu hình</button></div>
</form>
<div class="form-card"><h3>🧹 Bảo trì nhanh</h3><p>Tự chuyển key hết hạn sang expired, xoá key expired quá 3 ngày không gia hạn, <b>huỷ đơn pending quá 15 phút</b> (trả key/acc về pool), và hết hạn topup pending quá 15 phút. <b style="color:#fbbf24">Cần cron <span class="mono">job=maintenance</span> chạy mỗi 5 phút thì mới tự động</b> — nếu chưa setup cron, bấm nút dưới để chạy tay.</p><form method="POST" style="margin-top:12px"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="run_maintenance"><button class="btn btn-blue" type="submit">Chạy maintenance ngay</button></form><?php if(isset($_GET['maint'])):?><div class="codebox"><?=htmlspecialchars($_GET['maint'])?></div><?php endif; ?></div>
<div class="form-card"><h3>🧾 Log thay đổi cấu hình</h3><table><tr><th>ID</th><th>Admin</th><th>Key</th><th>Old</th><th>New</th><th>Time</th></tr><?php foreach($logs as $l): ?><tr><td><?=$l['id']?></td><td><?=htmlspecialchars($l['admin'])?></td><td class="mono"><?=htmlspecialchars($l['config_key'])?></td><td><?=htmlspecialchars($l['old_value'] ?? '')?></td><td><?=htmlspecialchars($l['new_value'] ?? '')?></td><td class="mono"><?=htmlspecialchars($l['created_at'])?></td></tr><?php endforeach; if(!$logs): ?><tr><td colspan="6"><p>Chưa có log.</p></td></tr><?php endif; ?></table></div>

<?php elseif($tab==='setup'): ?>
<h1>🧭 Setup/API & Cấu hình hệ thống</h1>
<div class="warnbox">⚠️ Trang này là chức năng hướng dẫn trong admin: chỉ chỉ rõ từng API/token lấy ở đâu, file nào liên quan và lệnh kiểm tra. Không hiển thị token thật để tránh lộ bảo mật.</div>
<div class="okbox">✅ Flow hiện tại: Paid key dùng VietQR + MBBANK Direct tự duyệt. Admin không duyệt tay paid order nữa; chỉ còn từ chối đơn lỗi/spam.</div>
<div class="warnbox">📦 Nếu sau này chuyển qua cPanel/server mới, đọc file <code>/www/wwwroot/hclou.com/CPANEL_DEPLOY_GUIDE.md</code>. File này hướng dẫn từng bước: upload code, import DB, sửa config, chạy Node MBBank Direct, chặn public service, setup webhook, setup cron và checklist verify 100%.</div>

<div class="guide-grid">
  <div class="guide-card"><span class="where">config.php</span><h3>🗄 Database</h3><ul><li>Sửa: <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code>.</li><li>Lấy ở panel MySQL/phpMyAdmin/hosting.</li><li>Nên dùng <code>127.0.0.1</code> nếu PHP-FPM lỗi socket với localhost.</li></ul><div class="codebox">php -r "require '/www/wwwroot/hclou.com/config.php'; var_dump((bool)getDB());"</div></div>

  <div class="guide-card"><span class="where">config.php + webhook.php</span><h3>🤖 Telegram Bot</h3><ul><li>Lấy token tại <b>@BotFather</b> → tạo bot hoặc xem token.</li><li>Sửa <code>BOT_TOKEN</code>, <code>BOT_USERNAME</code>, <code>ADMIN_CHAT_ID</code>.</li><li>Webhook URL: <code><?=htmlspecialchars(SITE_URL)?>/webhook.php</code>.</li></ul><div class="codebox">https://api.telegram.org/bot&lt;BOT_TOKEN&gt;/getWebhookInfo
https://api.telegram.org/bot&lt;BOT_TOKEN&gt;/setWebhook?url=<?=htmlspecialchars(SITE_URL)?>/webhook.php
php -l webhook.php</div></div>

  <div class="guide-card"><span class="where">@BotFather + index.php</span><h3>📱 Telegram Mini App</h3><ul><li>Trong @BotFather đặt Web App/Menu Button URL về <code><?=htmlspecialchars(SITE_URL)?>/</code>.</li><li>Frontend chính nằm ở <code>index.php</code>.</li><li>API Mini App nằm ở <code>backend/api/index.php</code>.</li></ul><div class="codebox">curl '<?=htmlspecialchars(SITE_URL)?>/backend/api/?action=games'
curl '<?=htmlspecialchars(SITE_URL)?>/backend/api/?action=packages&amp;game_id=4'</div></div>

  <div class="guide-card"><span class="where">config.php + index.php</span><h3>🏦 Bank/VietQR</h3><ul><li>Sửa <code>BANK_NAME</code>, <code>BANK_ACCOUNT</code>, <code>BANK_OWNER</code>, <code>VIETQR_BANK_ID</code>.</li><li>MBBank BIN hiện tại: <code>970422</code>.</li><li>VietQR tự điền số tiền + mã đơn ORD.</li></ul><div class="codebox">php -r "require '/www/wwwroot/hclou.com/config.php'; echo buildVietQrUrl(25000,'ORDTEST'), PHP_EOL;"</div></div>

  <div class="guide-card"><span class="where">config.php + cron/mbbank_poll.php</span><h3>✅ MBBANK Auto-bank (Queenvps API)</h3><ul><li>API: <code>GET https://queenvps.com/api/historymb/{API_KEY}</code></li><li>Nhập API Key vào config <code>MBBANK_HISTORY_API_KEY</code> hoặc qua form admin phía trên.</li><li>Script poll: <code>cron/mbbank_poll.php</code> — chạy qua cron mỗi 1-5 phút.</li><li>Response: <code>{ "success": true, "api_info": {...}, "transactions": [{"amount": 100000, "type": "IN", "description": "...", "formatted_date": "..."}] }</code></li><li>Nội dung chuyển khoản có mã <code>ORDxxxxx</code> để auto match đơn.</li></ul><div class="codebox">Test API:
curl -sS "https://queenvps.com/api/historymb/{API_KEY}"

Test VPS:
php /www/wwwroot/hclou.com/cron/mbbank_poll.php

Test HTTP (qua cron wrapper):
curl '<?=htmlspecialchars(hclouCronRunUrl('mbbank'))?>'

Cron (mỗi 1-5 phút):
*/1 * * * * php /www/wwwroot/hclou.com/cron/mbbank_poll.php</div></div>

  <div class="guide-card"><span class="where">cron-job.org + cron/run.php</span><h3>🤖 Cron ngoài đang dùng</h3><ul><li>Web quản lý/tạo job: <code>https://console.cron-job.org/</code>.</li><li>API key cron-job.org lấy tại Console → Settings → API keys.</li><li><code>CRON_RUN_TOKEN</code> nằm trong file <code>cron/run.php</code>; dùng chung cho các job wrapper.</li><li>Token chỉ hiển thị dạng rút gọn để tránh lộ secret.</li></ul><div class="codebox">HCLOU MBBANK     | mỗi phút   | <?=htmlspecialchars(hclouCronRunUrl('mbbank'))?>
HCLOU Maintenance| mỗi 5 phút | <?=htmlspecialchars(hclouCronRunUrl('maintenance'))?>
HCLOU Automation | mỗi 2 phút | <?=htmlspecialchars(hclouCronRunUrl('automation'))?>
HCLOU Health    | 08:00 VN  | <?=htmlspecialchars(hclouCronRunUrl('health'))?>
Tuỳ chọn Backup  | hằng ngày  | <?=htmlspecialchars(hclouCronRunUrl('backup'))?>

Cron-job.org API docs: https://docs.cron-job.org/rest-api.html
Verify history: Console → job → History → phải thấy 200 OK</div></div>

  <div class="guide-card"><span class="where">cron/automation_daily.php</span><h3>🔔 Automation/Reminder</h3><ul><li>Endpoint: <code>cron/run.php?job=automation</code> (wrapper có token + lock).</li><li>Chức năng: nhắc thanh toán gần hết hạn, báo đơn bị huỷ, cảnh báo bank ignored/error, báo cáo ngày nếu cron chạy đúng khung.</li></ul><div class="codebox">Cron URL: <?=htmlspecialchars(hclouCronRunUrl('automation'))?>
Test VPS: php /www/wwwroot/hclou.com/cron/automation_daily.php
Test HTTP: curl '<?=htmlspecialchars(hclouCronRunUrl('automation'))?>'</div></div>

  <div class="guide-card"><span class="where">cron-job.org + cron/health_check_daily.php</span><h3>🩺 Daily Health Check</h3><ul><li>Job ngoài chạy hằng ngày khoảng 08:00 giờ Việt Nam.</li><li>Kiểm tra web home, Mini App API, DB, MBBANK auto approve, maintenance, disk/RAM, đơn/key/bank lỗi.</li><li>Sau khi chạy sẽ gửi báo cáo về Telegram admin.</li><li>Endpoint wrapper: <code>cron/run.php?job=health</code>; script thật: <code>cron/health_check_daily.php</code>.</li></ul><div class="codebox">Cron-job.org: HCLOU Daily Health Check
Lịch: 08:00 Asia/Ho_Chi_Minh mỗi ngày
URL: <?=htmlspecialchars(hclouCronRunUrl('health'))?>
Test VPS: php /www/wwwroot/hclou.com/cron/health_check_daily.php
Test HTTP: curl '<?=htmlspecialchars(hclouCronRunUrl('health'))?>'</div></div>

  <div class="guide-card"><span class="where">config.php + admin GetKey Free</span><h3>🎁 Layma + Link4M</h3><ul><li>Lấy token Layma tại <a href="https://layma.net" target="_blank">layma.net</a> mục Developer/API.</li><li>Sửa <code>LAYMA_API_TOKEN</code>, <code>LINK4M_API_TOKEN</code>.</li><li>Chọn <code>FREE_SHORTLINK_LAYERS</code>: 1 (Layma) hoặc 2 (Layma → Link4M → HCLOU claim).</li></ul><div class="codebox">Admin → GetKey Free → nhập key → chọn game/gói → mỗi user lấy link riêng</div></div>

  <div class="guide-card"><span class="where">admin/index.php</span><h3>🛠 Quản lý trong Admin</h3><ul><li>Games: thêm/sửa/tắt game.</li><li>Gói cước: sửa ngày/giá/key type.</li><li>Keys: khoá/mở/xoá key.</li><li>Đơn hàng: xem trạng thái, từ chối đơn pending lỗi/spam.</li></ul><div class="codebox">Paid order: pending → MBBANK API xác nhận → approved → key active</div></div>

  <div class="guide-card"><span class="where">/www/backup/hclou_db</span><h3>💾 Backup DB</h3><ul><li>Script backup: <code>/www/backup/hclou_db/backup.sh</code>.</li><li>Giữ 7 bản backup mới nhất.</li><li>Có thể chạy qua <code>cron/run.php?job=backup</code> nếu muốn dùng cron ngoài.</li><li>Không xoá backup DB nếu chưa chắc.</li></ul><div class="codebox">Cron ngoài: <?=htmlspecialchars(hclouCronRunUrl('backup'))?>
Hoặc cron VPS: 17 3 * * * /www/backup/hclou_db/backup.sh &gt;/dev/null 2&gt;&amp;1
Test VPS: /www/backup/hclou_db/backup.sh</div></div>
</div>

<div class="form-card">
<h3>🔍 Checklist verify sau khi sửa code</h3>
<div class="codebox">cd /www/wwwroot/hclou.com
php -l config.php
php -l index.php
php -l backend/api/index.php
php -l admin/index.php
php -l webhook.php
php -l claim.php
php -l setup_webhook.php
php -l cron/mbbank_poll.php
php -l cron/maintenance.php
php -l cron/automation_daily.php
php -l cron/run.php
curl -I <?=htmlspecialchars(SITE_URL)?>/
curl '<?=htmlspecialchars(SITE_URL)?>/backend/api/?action=games'
php cron/mbbank_poll.php
php cron/maintenance.php
curl '<?=htmlspecialchars(hclouCronRunUrl('mbbank'))?>'
curl '<?=htmlspecialchars(hclouCronRunUrl('maintenance'))?>'
curl '<?=htmlspecialchars(hclouCronRunUrl('automation'))?>'
curl '<?=htmlspecialchars(hclouCronRunUrl('health'))?>'</div>
</div>

<div class="form-card">
<h3>🧯 Lỗi thường gặp</h3>
<table><tr><th>Lỗi</th><th>Kiểm tra</th><th>File liên quan</th></tr>
<tr><td>API games lỗi DB</td><td>DB_HOST/DB_USER/DB_PASS, dùng 127.0.0.1</td><td>config.php</td></tr>
<tr><td>Bot không trả lời</td><td>BOT_TOKEN, webhook, php -l webhook.php</td><td>config.php, webhook.php</td></tr>
<tr><td>Thanh toán không auto active</td><td>Kiểm tra <code>MBBANK_HISTORY_API_KEY</code> trong config.php, cron chạy <code>cron/mbbank_poll.php</code>, API key còn hạn, description có mã ORD, amount đủ tiền</td><td>cron/mbbank_poll.php, config.php</td></tr>
<tr><td>VietQR không hiện</td><td>buildVietQrUrl, bank id, img.vietqr.io</td><td>config.php, index.php</td></tr>
<tr><td>GetKey Free lỗi link</td><td>Token Link4M/YeuMoney, endpoint, curl internet</td><td>config.php, admin/index.php</td></tr>
</table>
</div>

<?php elseif($tab==='update'): ?>
<h1>🔄 Cập nhật hệ thống</h1>
<?php
$curVer = @json_decode((string)@file_get_contents(APP_ROOT.'/version.json'), true)['version'] ?? '1.0.0';
$lic = @json_decode((string)@file_get_contents(APP_ROOT.'/data/.lic'), true);
$latest = is_array($lic) ? ($lic['latest'] ?? '') : '';
$hasUpdate = $latest !== '' && version_compare($latest, $curVer, '>');
// Gọi check_update để lấy changelog (nếu có license)
$changelog = '';
if (defined('LICENSE_KEY') && LICENSE_KEY !== '' && defined('LICENSE_SERVER_URL')) {
    $u = rtrim(LICENSE_SERVER_URL,'/').'/api.php?action=check_update&license_key='.urlencode(LICENSE_KEY).'&current_version='.urlencode($curVer);
    $ch=curl_init($u); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>0]);
    $r=@json_decode((string)curl_exec($ch),true); curl_close($ch);
    if(is_array($r)){ if(!empty($r['latest_version'])){$latest=$r['latest_version']; $hasUpdate=version_compare($latest,$curVer,'>');} $changelog=$r['changelog']??''; }
}
?>
<?php if(isset($_GET['ok']) && $_GET['ok']==='updated'): ?>
<div class="okbox">✅ Cập nhật thành công lên <b>v<?=h($_GET['v'] ?? '')?></b>!<br>
📦 Zip có <b><?=(int)($_GET['t']??0)?></b> entry · ✍️ Ghi <b><?=(int)($_GET['n']??0)?></b> file · ❌ Lỗi <b><?=(int)($_GET['e']??0)?></b><?php $sk=(int)($_GET['s']??0); if($sk>0): ?> · ⏭️ Skip <b><?=$sk?></b> (path traversal)<?php endif; ?><br>
<b>Tải lại trang</b> để dùng bản mới.</div>
<?php endif; ?>
<?php if(isset($_GET['err'])): ?>
<div class="errbox" style="background:#7f1d1d;border:1px solid #ef4444;color:#fecaca;padding:12px;border-radius:10px;margin-bottom:14px">❌ <?=h($_GET['err'])?></div>
<?php endif; ?>
<div class="form-card">
  <h3>Phiên bản</h3>
  <p style="font-size:15px;margin-bottom:8px">Hiện tại: <b style="color:#67e8f9">v<?=h($curVer)?></b>
  <?php if($latest):?> · Mới nhất: <b style="color:<?=$hasUpdate?'#4ade80':'#67e8f9'?>">v<?=h($latest)?></b><?php endif;?></p>
  <?php if($hasUpdate): ?>
    <div class="warnbox">🎉 Có bản cập nhật mới <b>v<?=h($latest)?></b>!</div>
    <?php if($changelog):?><div class="codebox" style="margin-bottom:14px"><?=h($changelog)?></div><?php endif;?>
    <form method="POST" onsubmit="return confirm('Cập nhật lên v<?=h($latest)?>? Code sẽ được tải về và ghi đè (giữ config + data).')">
      <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="do_update">
      <button class="btn btn-green" type="submit" style="font-size:14px;padding:11px 22px">⬇️ Cập nhật ngay lên v<?=h($latest)?></button>
    </form>
    <p style="margin-top:10px;font-size:12px">⚠️ Tự backup config.local.php. Giữ nguyên <code>config.local.php</code>, <code>data/</code>, <code>uploads/</code>, <code>license.php</code>.</p>
  <?php else: ?>
    <div class="okbox">✔️ Bạn đang dùng bản mới nhất.</div>
  <?php endif; ?>
</div>

<?php elseif($tab==='users'): ?>
<h1>👥 Danh sách Users</h1>
<?php
// Xử lý update role/discount
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'user_update') {
    $uid = (int)$_POST['uid'];
    $role = $_POST['role'] ?? 'customer';
    $discount = min(100, max(0, (float)($_POST['discount'] ?? 0)));
    if (!in_array($role, ['customer','reseller','admin'], true)) $role = 'customer';
    $db->prepare("UPDATE users SET role=?, discount=? WHERE id=?")->execute([$role, $discount, $uid]);
    header("Location: ?tab=users&ok=1"); exit;
}
$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM `keys` WHERE user_id=u.id) as total_keys, (SELECT COUNT(*) FROM orders WHERE user_id=u.id AND status='approved') as total_orders FROM users u ORDER BY u.created_at DESC")->fetchAll();
?>
<div style="max-width:100%;overflow-x:auto">
<table>
<tr><th>ID</th><th>Telegram</th><th>Vai trò</th><th>Giảm giá</th><th>Keys</th><th>Đơn</th><th>Lưu</th></tr>
<?php foreach($users as $u): ?>
<tr>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="user_update"><input type="hidden" name="uid" value="<?=$u['id']?>">
  <td style="font-size:11px;font-family:monospace;color:var(--muted)"><?=$u['id']?></td>
  <td style="font-size:12px;font-family:monospace"><?=$u['telegram_id']?><br><small style="color:#8b949e">@<?=h($u["telegram_username"] ?: '--')?></small></td>
  <td>
    <select name="role" style="width:120px;font-size:12px;padding:7px">
      <option value="customer" <?=$u['role']==='customer'?'selected':''?>>Khách hàng</option>
      <option value="reseller" <?=$u['role']==='reseller'?'selected':''?>>Reseller</option>
      <option value="admin" <?=$u['role']==='admin'?'selected':''?>>Admin</option>
    </select>
  </td>
  <td>
    <div style="display:flex;align-items:center;gap:4px">
      <input name="discount" type="number" min="0" max="100" step="1" value="<?=h($u['discount'] ?? 0)?>" style="width:60px;font-size:12px;padding:6px">
      <span style="color:var(--muted);font-size:12px;font-weight:700">%</span>
    </div>
  </td>
  <td><?=$u['total_keys']?></td>
  <td><?=$u['total_orders']?></td>
  <td><button class="btn btn-blue" style="padding:6px 12px;font-size:11px">💾 Lưu</button></td>
</form>
</tr>
<?php endforeach ?>
</table>
</div>
<?php endif ?>
</div>

<footer class="admin-footer">Copyright by HCLOU Server · Telegram @hcloucom · Địa chỉ: Thành phố Quảng Ngãi</footer>

<script>
/* ===== Sidebar nav: bind thêm (openNav/closeNav/toggleNav đã định nghĩa sớm ở topbar) ===== */
(function(){
  function bind(){
    // Đóng nav khi bấm vào 1 mục (điều hướng) — tránh kẹt trạng thái open
    document.querySelectorAll('#sidebarNav .nav-item').forEach(function(a){
      if(a._navBound)return; a._navBound=1;
      a.addEventListener('click',function(){ setTimeout(closeNav,50); });
    });
    // ESC để đóng
    document.addEventListener('keydown',function(ev){ if(ev.key==='Escape')closeNav(); });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind);
  else bind();
})();
function closeUpdModal(){ var m=document.getElementById('updModal'); if(m){ m.style.display='none'; var v=m.getAttribute('data-v')||'1'; try{sessionStorage.setItem('upd_dismiss_'+v,'1')}catch(e){} } }

/* ===== MODAL EDIT (tạo/sửa popup) ===== */
function amOpen(id,data){
  var ov=document.getElementById(id); if(!ov)return;
  if(data){ // điền sẵn field cho form sửa
    var form=ov.querySelector('form');
    if(form){ Object.keys(data).forEach(function(k){
      var el=form.elements[k];
      if(el && el.type!=='file'){ el.value=data[k]; }
    }); }
    // hiện icon hiện tại nếu có
    var ci=ov.querySelector('[data-cur-icon]');
    if(ci){ ci.innerHTML = data._icon ? ('<img src="'+data._icon+'" alt=""> Icon hiện tại — chọn file mới để thay') : 'Chưa có icon'; }
    // tiêu đề phụ
    var st=ov.querySelector('[data-am-sub]'); if(st && data._title){ st.textContent=data._title; }
  }
  ov.classList.add('show'); document.body.style.overflow='hidden';
}
function amClose(id){ var ov=document.getElementById(id); if(ov){ov.classList.remove('show'); document.body.style.overflow='';} }
document.addEventListener('click',function(e){ if(e.target.classList && e.target.classList.contains('amodal-ov')) amClose(e.target.id); });
document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ document.querySelectorAll('.amodal-ov.show').forEach(function(o){amClose(o.id);}); } });

/* ===== TOAST + CONFIRM MODAL (admin global) ===== */
(function(){
  // Toast container
  var tc=document.createElement('div'); tc.id='aToastBox';
  tc.style.cssText='position:fixed;top:64px;right:18px;z-index:9998;display:flex;flex-direction:column;gap:9px;max-width:calc(100% - 36px);width:340px;pointer-events:none';
  document.addEventListener('DOMContentLoaded',function(){document.body.appendChild(tc);});
  window.aToast=function(msg,type){
    type=type||'info';
    var colors={success:['#16a34a','#22c55e','✓'],error:['#dc2626','#ef4444','✕'],warn:['#d97706','#f59e0b','⚠'],info:['#0284c7','#06b6d4','ℹ']};
    var c=colors[type]||colors.info;
    var t=document.createElement('div');
    t.style.cssText='background:linear-gradient(135deg,'+c[0]+',rgba(15,23,42,.96));border:1px solid rgba(255,255,255,.12);border-left:4px solid '+c[1]+';color:#fff;padding:12px 14px;border-radius:13px;box-shadow:0 12px 36px rgba(0,0,0,.4),0 0 0 1px rgba(0,0,0,.4);font-size:13px;font-weight:700;display:flex;align-items:center;gap:10px;pointer-events:auto;transform:translateX(120%);opacity:0;transition:transform .35s cubic-bezier(.34,1.56,.64,1),opacity .25s;backdrop-filter:blur(12px)';
    t.innerHTML='<span style="font-size:18px;font-weight:900;color:'+c[1]+'">'+c[2]+'</span><span style="flex:1;line-height:1.45">'+msg+'</span><span style="cursor:pointer;opacity:.7;font-size:18px;padding:0 4px" onclick="this.parentNode.style.transform=\'translateX(120%)\';this.parentNode.style.opacity=\'0\';setTimeout(function(){t.remove()},300)">×</span>';
    tc.appendChild(t);
    requestAnimationFrame(function(){t.style.transform='translateX(0)';t.style.opacity='1';});
    setTimeout(function(){t.style.transform='translateX(120%)';t.style.opacity='0';setTimeout(function(){t.remove();},350);},4500);
  };
  // Confirm modal — Promise based
  window.aConfirm=function(msg,opts){
    opts=opts||{};
    return new Promise(function(resolve){
      var ov=document.createElement('div');
      ov.style.cssText='position:fixed;inset:0;background:rgba(8,12,24,.74);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;transition:opacity .25s;backdrop-filter:blur(4px)';
      var dangerCol=opts.danger?'linear-gradient(135deg,#dc2626,#ef4444)':'linear-gradient(135deg,#2563eb,#06b6d4)';
      var icon=opts.danger?'⚠️':(opts.icon||'❓');
      ov.innerHTML='<div style="background:linear-gradient(180deg,#182235,#111827);border:1px solid #334765;border-radius:20px;padding:26px 24px 20px;max-width:400px;width:100%;box-shadow:0 32px 96px rgba(0,0,0,.55);transform:scale(.92);transition:transform .25s cubic-bezier(.34,1.56,.64,1);text-align:center"><div style="font-size:42px;margin-bottom:10px">'+icon+'</div><div style="font-size:15.5px;font-weight:800;color:#dbeafe;line-height:1.5;margin-bottom:18px">'+msg+'</div><div style="display:flex;gap:10px"><button type="button" class="aCfBtn aCfCancel" style="flex:1;padding:11px 16px;border:1px solid #334765;background:transparent;color:#9fb2cf;border-radius:11px;font-weight:800;font-size:13px;cursor:pointer">'+(opts.cancel||'Huỷ')+'</button><button type="button" class="aCfBtn aCfOk" style="flex:1;padding:11px 16px;border:0;background:'+dangerCol+';color:#fff;border-radius:11px;font-weight:900;font-size:13px;cursor:pointer">'+(opts.ok||'Đồng ý')+'</button></div></div>';
      document.body.appendChild(ov);
      requestAnimationFrame(function(){ov.style.opacity='1';ov.firstElementChild.style.transform='scale(1)';});
      function close(val){ov.style.opacity='0';ov.firstElementChild.style.transform='scale(.92)';setTimeout(function(){ov.remove();resolve(val);},220);}
      ov.querySelector('.aCfCancel').onclick=function(){close(false);};
      ov.querySelector('.aCfOk').onclick=function(){close(true);};
      ov.addEventListener('click',function(e){if(e.target===ov)close(false);});
    });
  };
  // Intercept native window.confirm để xài modal đẹp.
  // Pattern phổ biến trong admin: <button onclick="return confirm('...')">.
  // Khi click → DOM tự gọi onclick handler; nếu trả false thì submit bị huỷ.
  // Mình intercept click capture phase trước, hiện modal, rồi tự submit form.
  document.addEventListener('click',function(e){
    var btn=e.target.closest('button,a,input[type=submit]');
    if(!btn || btn.dataset._cfApproved==='1')return;
    var oc=btn.getAttribute('onclick')||'';
    var m=oc.match(/return\s+confirm\s*\(\s*['"]([\s\S]*?)['"]\s*\)/);
    if(!m)return;
    e.preventDefault();
    e.stopImmediatePropagation();
    var msg=m[1].replace(/\\n/g,' ').replace(/\\'/g,"'").replace(/\\"/g,'"');
    var danger=/xoá|xoa|xóa|delete|reject|từ chối|tu choi|huỷ|huy|tắt|tat|lock|khoá|khoa/i.test(msg);
    aConfirm(msg,{danger:danger}).then(function(ok){
      if(!ok)return;
      btn.dataset._cfApproved='1';
      // Click lại; onclick trả về true (bỏ qua confirm) thì submit/redirect tiếp
      btn.removeAttribute('onclick');
      if(btn.form && (btn.type==='submit'||btn.tagName==='INPUT')){btn.form.submit();}
      else{btn.click();}
      setTimeout(function(){btn.setAttribute('onclick',oc);delete btn.dataset._cfApproved;},300);
    });
  },true);
  // Form có data-confirm
  document.addEventListener('submit',function(e){
    var f=e.target;
    var msg=f.getAttribute('data-confirm');
    if(!msg || f.dataset._cfApproved==='1')return;
    e.preventDefault();
    aConfirm(msg,{danger:f.hasAttribute('data-confirm-danger')}).then(function(ok){
      if(ok){f.dataset._cfApproved='1';f.submit();}
    });
  },true);
  // Auto-toast từ URL ?ok=&err=&msg=
  document.addEventListener('DOMContentLoaded',function(){
    var u=new URL(location.href);
    var ok=u.searchParams.get('ok'),err=u.searchParams.get('err'),msg=u.searchParams.get('msg');
    if(err){aToast(decodeURIComponent(err),'error');}
    else if(msg){aToast(decodeURIComponent(msg),'info');}
    else if(ok && ok!=='updated'){aToast('✓ Thao tác thành công','success');}
  });
})();
</script>

<?php
// Modal thông báo cập nhật — gọi thẳng check_update lên server (cache 60s/session) để hiện ngay khi có bản mới
if ($tab !== 'update') {
    $_mCur = @json_decode((string)@file_get_contents(APP_ROOT.'/version.json'), true)['version'] ?? '1.0.0';
    $_mLatest = ''; $_mChangelog = '';
    $_cacheKey = 'upd_chk';
    $_now = time();
    if (isset($_SESSION[$_cacheKey]) && ($_now - (int)($_SESSION[$_cacheKey]['t'] ?? 0)) < 60) {
        $_mLatest    = (string)($_SESSION[$_cacheKey]['v'] ?? '');
        $_mChangelog = (string)($_SESSION[$_cacheKey]['c'] ?? '');
    } elseif (defined('LICENSE_KEY') && LICENSE_KEY !== '' && defined('LICENSE_SERVER_URL')) {
        $_u = rtrim(LICENSE_SERVER_URL,'/').'/api.php?action=check_update&license_key='.urlencode(LICENSE_KEY).'&current_version='.urlencode($_mCur);
        $_ch = curl_init($_u);
        curl_setopt_array($_ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3, CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_SSL_VERIFYHOST=>0]);
        $_r = @json_decode((string)curl_exec($_ch), true); curl_close($_ch);
        if (is_array($_r)) {
            $_mLatest    = (string)($_r['latest_version'] ?? '');
            $_mChangelog = (string)($_r['changelog'] ?? '');
        }
        $_SESSION[$_cacheKey] = ['t'=>$_now, 'v'=>$_mLatest, 'c'=>$_mChangelog];
    }
    if ($_mLatest !== '' && version_compare($_mLatest, $_mCur, '>')):
?>
<div id="updModal" data-v="<?=h($_mLatest)?>" style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px">
  <div style="max-width:440px;width:100%;background:#161b22;border:1px solid #30363d;border-radius:18px;padding:28px;text-align:center">
    <div style="font-size:46px;margin-bottom:10px">🎉</div>
    <h2 style="font-size:19px;margin-bottom:8px;color:#fde68a">Có bản cập nhật mới!</h2>
    <p style="color:#9fb2cf;font-size:14px;line-height:1.6;margin-bottom:14px">Phiên bản <b style="color:#4ade80">v<?=h($_mLatest)?></b> đã sẵn sàng.<br>Bạn đang dùng v<?=h($_mCur)?>.</p>
    <?php if($_mChangelog !== ''): ?>
    <div style="background:#0d1117;border:1px solid #1f2937;border-radius:10px;padding:10px 12px;margin-bottom:16px;text-align:left;color:#cbd5e1;font-size:12.5px;line-height:1.55;max-height:140px;overflow:auto;white-space:pre-wrap"><?=h($_mChangelog)?></div>
    <?php endif; ?>
    <div style="display:flex;gap:10px">
      <button type="button" onclick="closeUpdModal()" style="flex:1;padding:12px;border:1px solid #30363d;border-radius:11px;background:transparent;color:#9fb2cf;font-weight:700;cursor:pointer;font-size:14px">Để sau</button>
      <form method="POST" style="flex:1;margin:0" onsubmit="return updModalGo(this)">
        <input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
        <input type="hidden" name="act" value="do_update">
        <button type="submit" id="updModalBtn" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;font-weight:800;cursor:pointer;font-size:14px">🔄 Cập nhật ngay</button>
      </form>
    </div>
  </div>
</div>
<script>
function updModalGo(f){ var b=document.getElementById('updModalBtn'); if(b){b.disabled=true;b.innerHTML='⏳ Đang cập nhật...';} return true; }
(function(){ try{ if(!sessionStorage.getItem('upd_dismiss_<?=h($_mLatest)?>')){ document.getElementById('updModal').style.display='flex'; } }catch(e){ document.getElementById('updModal').style.display='flex'; } })();
</script>
<?php endif; } ?>
<script src="admin-i18n.js?v=<?= @filemtime(__DIR__ . '/admin-i18n.js') ?: time() ?>"></script>
</body>
</html>
