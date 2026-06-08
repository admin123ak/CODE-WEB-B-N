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
        <style>*{margin:0;padding:0;box-sizing:border-box}body{min-height:100vh;background:radial-gradient(circle at 20% 10%,rgba(31,111,235,.35),transparent 28%),radial-gradient(circle at 85% 20%,rgba(139,92,246,.28),transparent 30%),#070b14;color:#e6edf3;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;padding:20px;overflow:hidden}.card{width:410px;max-width:100%;background:linear-gradient(180deg,rgba(22,27,34,.94),rgba(13,17,23,.97));border:1px solid rgba(88,166,255,.22);border-radius:28px;padding:30px;box-shadow:0 24px 90px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.05);backdrop-filter:blur(18px)}.logo{width:68px;height:68px;border-radius:22px;background:linear-gradient(135deg,#1f6feb,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 16px;box-shadow:0 0 30px rgba(31,111,235,.45)}h1{text-align:center;font-size:24px;margin-bottom:6px}.sub{text-align:center;color:#8b949e;font-size:13px;margin-bottom:24px}.field{margin-bottom:14px}label{display:block;color:#8b949e;font-size:12px;font-weight:800;margin:0 0 7px 2px}input{width:100%;padding:14px 15px;background:#0d1117;border:1px solid #30363d;border-radius:14px;color:#e6edf3;font-size:15px;outline:none}input:focus{border-color:#58a6ff;box-shadow:0 0 0 4px rgba(88,166,255,.12)}button{width:100%;padding:14px;border:none;border-radius:14px;background:linear-gradient(135deg,#1f6feb,#8b5cf6);color:#fff;font-size:15px;font-weight:950;cursor:pointer;box-shadow:0 12px 30px rgba(31,111,235,.28)}.hint{margin-top:16px;text-align:center;color:#6e7681;font-size:12px}.err{background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.35);color:#fca5a5;padding:11px 13px;border-radius:13px;margin-bottom:14px;font-size:13px;font-weight:750}.admin-footer{margin:26px 0 4px;text-align:center;color:rgba(127,144,170,.48);font-size:11px;font-weight:700;letter-spacing:.02em;opacity:.72;text-shadow:0 0 14px rgba(125,211,252,.14)}.admin-footer:before{content:"";display:block;width:120px;height:1px;background:linear-gradient(90deg,transparent,rgba(125,211,252,.28),transparent);margin:0 auto 12px}</style></head>
        <body><form class="card" method="POST"><div class="logo">⚡</div><h1>HCLOU SERVER</h1><div class="sub">Admin Control Center · Secure Login</div>'.$err.'<input type="hidden" name="csrf" value="'.$csrf.'"><div class="field"><label>Mật khẩu quản trị</label><input type="password" name="pw" placeholder="Nhập mật khẩu admin" autocomplete="current-password" autofocus></div><button>Đăng nhập an toàn</button><div class="hint">Session tự hết hạn sau '.(int)(ADMIN_SESSION_TTL/60).' phút không hoạt động</div></form></body></html>';
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
        if (password_verify($_POST['pw'] ?? '', ADMIN_PASSWORD_HASH)) {
            loginAttemptReset('admin_login');
            session_regenerate_id(true);
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_last_seen'] = time();
            $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
            logInfo('Admin login OK', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            header('Location: ?tab=dashboard'); exit;
        }
        loginAttemptIncrement('admin_login', 900);
        logWarn('Admin login failed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'remaining' => $attempt['remaining'] - 1]);
        admin_login_page('Sai mật khẩu admin. Còn ' . max(0, $attempt['remaining'] - 1) . ' lượt thử.'); exit;
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

// Xử lý action POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['admin_csrf'] ?? '', $_POST['csrf'] ?? '')) { header('Location: ?err=' . urlencode('CSRF token không hợp lệ')); exit; }
    $act = $_POST['act'] ?? '';
    

    if ($act === 'save_config') {
        try {
            $changes = hclouWriteConfigValues($_POST['cfg'] ?? [], $_SESSION['admin_name'] ?? 'web_admin');
            header("Location: ?tab=sysconfig&ok=1&changed=" . count($changes)); exit;
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

        $ins = $db->prepare("INSERT INTO free_keys (key_code,game_id,package_id,days,key_type,is_active,start_at,expire_at,claim_token,short_url) VALUES (?,?,?,?,?,1,?,?,?,NULL)");
        $added = 0; $errs = [];
        foreach ($lines as $code) {
            $code = trim($code);
            if ($code === '') continue;
            try {
                $token = bin2hex(random_bytes(24));
                $ins->execute([$code, $game_id, $package_id, $p['days'], $p['key_type'], $start, $exp, $token]);
                $added++;
            } catch (Exception $e) {
                $errs[] = $code . ': ' . substr($e->getMessage(), 0, 60);
            }
        }
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
        if ($fk) { try { $short=buildFreeShortlink(SITE_URL.'/claim.php?t='.$fk['claim_token']); $db->prepare("UPDATE free_keys SET short_url=? WHERE id=?")->execute([$short,$fk['id']]); } catch (Exception $e) { header("Location: ?tab=freekeys&err=" . urlencode($e->getMessage())); exit; } }
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
        $db->prepare("INSERT INTO packages (game_id,name,days,price,key_type) VALUES (?,?,?,?,?)")
           ->execute([$_POST['game_id'],'Gói '.$_POST['days'].' ngày',$_POST['days'],$_POST['price'],$_POST['key_type']]);
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'toggle_pkg') {
        $db->prepare("UPDATE packages SET is_active=1-is_active WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'edit_pkg') {
        $name = trim($_POST['name'] ?? '') ?: ('Gói '.($_POST['days'] ?? '').' ngày');
        $db->prepare("UPDATE packages SET game_id=?, name=?, days=?, price=?, key_type=?, is_active=? WHERE id=?")
           ->execute([$_POST['game_id'],$name,$_POST['days'],$_POST['price'],$_POST['key_type'],$_POST['is_active']??1,$_POST['id']]);
        header("Location: ?tab=packages&ok=1"); exit;
    }
    if ($act === 'del_pkg') {
        $db->prepare("DELETE FROM packages WHERE id=?")->execute([$_POST['id']]);
        header("Location: ?tab=packages"); exit;
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
            $stmt = $db->prepare("SELECT o.id, o.user_id, u.telegram_id, p.days, p.key_type, p.price, g.name as game_name, g.package_name FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id JOIN packages p ON o.package_id=p.id WHERE o.order_code=? AND o.status='pending'");
            $stmt->execute([$order_code]);
            $order = $stmt->fetch();
            if (!$order) { header("Location: ?tab=orders&err=".urlencode('Đơn không tồn tại hoặc đã xử lý')); exit; }
            $db->beginTransaction();
            try {
                $now = date('Y-m-d H:i:s');
                $expire = date('Y-m-d H:i:s', strtotime('+'.((int)$order['days']).' days'));
                $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by='web_admin' WHERE order_code=? AND status='pending'");
                $upOrder->execute([$order_code]);
                if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');
                $db->prepare("UPDATE `keys` SET status='active', start_at=COALESCE(start_at,?), expire_at=? WHERE order_id=? AND status IN ('pending','available')")
                   ->execute([$now, $expire, $order['id']]);
                $db->commit();
                logInfo('Admin approved order', ['order' => $order_code]);
                if ($order['telegram_id']) sendTelegram($order['telegram_id'], "✅ <b>Đơn #" . h($order_code) . " đã được admin duyệt!</b>\n🔑 Key đã hoạt động. Thời hạn: " . (int)$order['days'] . " ngày.");
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
        $count = 0; $pkgDays = 0;
        foreach ($keyLines as $line) {
            $code = trim($line);
            if (!$code) continue;
            $check = $db->prepare("SELECT id FROM `keys` WHERE key_code=?");
            $check->execute([$code]);
            if ($check->fetch()) continue;
            if ($pkgDays === 0) {
                $pkgStmt = $db->prepare("SELECT days FROM packages WHERE id=? AND game_id=?");
                $pkgStmt->execute([$pkgId, $gameId]);
                $pkgRow = $pkgStmt->fetch();
                if (!$pkgRow || $pkgRow['days'] <= 0) { header("Location: ?tab=keys&err=Gói không hợp lệ"); exit; }
                $pkgDays = (int)$pkgRow['days'];
            }
            $db->prepare("INSERT INTO `keys` (key_code, game_id, package_id, days, status) VALUES (?,?,?,?,'available')")
               ->execute([$code, $gameId, $pkgId, $pkgDays]);
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

            // 4. Giải nén từng entry, BỎ QUA file/thư mục nhạy cảm
            $protect = ['config.local.php', '.install_lock', 'data/', 'uploads/', 'license.php'];
            $extracted = 0;
            for ($i = 0; $i < $za->numFiles; $i++) {
                $name = $za->getNameIndex($i);
                if ($name === false || $name === '') continue;
                // Strip thư mục gốc nếu zip bọc 1 lớp (vd CODE-WEB-B-N/...)
                $rel = $name;
                $skip = false;
                foreach ($protect as $p) {
                    if (substr($p, -1) === '/') { if (strpos($rel, $p) !== false) { $skip = true; break; } }
                    else { if (basename($rel) === $p || $rel === $p) { $skip = true; break; } }
                }
                if ($skip) continue;
                if (substr($name, -1) === '/') continue; // thư mục
                $dest = APP_ROOT . '/' . ltrim($rel, '/');
                $destDir = dirname($dest);
                if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
                $stream = $za->getStream($name);
                if ($stream) {
                    $content = stream_get_contents($stream);
                    fclose($stream);
                    if ($content !== false) { @file_put_contents($dest, $content); $extracted++; }
                }
            }
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

            logInfo('Admin auto-update', ['extracted' => $extracted, 'version' => $newVer]);
            header("Location: ?tab=update&ok=updated&n=" . $extracted . "&v=" . urlencode($newVer)); exit;
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel - <?= h(SITE_NAME) ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0b1020;--side:#0f172a;--side2:#111c33;--panel:#111827;--card:#182235;--card2:#151f31;--line:#26354f;--line2:#334765;--text:#edf4ff;--muted:#91a4c3;--blue:#3b82f6;--cyan:#06b6d4;--green:#22c55e;--red:#ef4444;--orange:#f59e0b;--purple:#8b5cf6;--shadow:0 18px 46px rgba(0,0,0,.28)}
html{scroll-behavior:smooth}body{background:linear-gradient(180deg,#08111f 0%,#0b1020 45%,#090d18 100%);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;min-height:100vh;font-size:14px}.nav-item{display:flex;align-items:center;gap:12px;padding:10px 18px;color:#aab8d0;text-decoration:none;font-size:14px;font-weight:700;transition:.15s;position:relative}.nav-item:hover{color:#fff;background:var(--card)}.nav-item.active{color:var(--cyan);background:rgba(6,182,212,.1)}.nav-item.active:before{content:"";position:absolute;left:0;top:6px;bottom:6px;width:3px;background:var(--cyan);border-radius:0 3px 3px 0}.nav-icon{width:20px;text-align:center;flex-shrink:0;font-size:16px}.nav-item .count{background:#f85149;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;margin-left:auto}h2{font-size:17px;margin-bottom:13px;color:#dbeafe}.alert{padding:13px 16px;border-radius:14px;font-size:13px;font-weight:750;margin-bottom:16px;border:1px solid var(--line)}.alert-green{background:rgba(34,197,94,.13);border-color:rgba(34,197,94,.30);color:#86efac}.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px;margin-bottom:28px}.stat-card{background:linear-gradient(180deg,var(--card),var(--card2));border:1px solid var(--line);border-radius:22px;padding:19px;box-shadow:var(--shadow);position:relative;overflow:hidden}.stat-card:after{content:"";position:absolute;right:-24px;top:-24px;width:84px;height:84px;border-radius:50%;background:rgba(59,130,246,.12)}.stat-card:hover{border-color:var(--line2);transform:translateY(-2px);transition:.16s}.stat-val{font-size:34px;font-weight:950;margin-bottom:5px;letter-spacing:-.04em;position:relative}.stat-label{font-size:12px;color:var(--muted);font-weight:800;position:relative}.stat-val.blue{color:#60a5fa}.stat-val.green{color:#4ade80}.stat-val.orange{color:#fbbf24}.stat-val.red{color:#f87171}
table{width:100%;border-collapse:separate;border-spacing:0;background:var(--panel);border:1px solid var(--line);border-radius:18px;overflow:hidden;font-size:13px;box-shadow:var(--shadow)}th{padding:14px 15px;text-align:left;font-size:11px;font-weight:900;color:#9fb7d7;text-transform:uppercase;border-bottom:1px solid var(--line);background:#0f172a;letter-spacing:.04em}td{padding:13px 15px;border-bottom:1px solid rgba(148,163,184,.10);vertical-align:middle;color:#e5edf8}tr:last-child td{border-bottom:none}tr:hover td{background:rgba(59,130,246,.045)}td small{color:var(--muted)!important}.badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:900}.badge.green{background:rgba(34,197,94,.14);color:#86efac;border:1px solid rgba(34,197,94,.30)}.badge.orange{background:rgba(245,158,11,.14);color:#fbbf24;border:1px solid rgba(245,158,11,.30)}.badge.red{background:rgba(239,68,68,.14);color:#fca5a5;border:1px solid rgba(239,68,68,.30)}.badge.blue{background:rgba(59,130,246,.14);color:#93c5fd;border:1px solid rgba(59,130,246,.30)}.badge.gray{background:rgba(148,163,184,.12);color:#cbd5e1;border:1px solid rgba(148,163,184,.20)}.btn{padding:8px 13px;border-radius:11px;border:none;font-size:12px;font-weight:900;cursor:pointer;transition:.14s;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:5px;white-space:nowrap}.btn-green{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff}.btn-red{background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff}.btn-blue{background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff}.btn-gray{background:#243044;color:#e6edf3;border:1px solid var(--line2)}.btn:hover{transform:translateY(-1px);filter:brightness(1.08)}.btn:active{transform:scale(.97)}.form-card{background:var(--panel);border:1px solid var(--line);border-radius:18px;padding:20px;margin-bottom:20px;box-shadow:var(--shadow)}.form-card h3{font-size:16px;font-weight:900;margin-bottom:15px;color:#dbeafe}.form-row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}input,select{padding:10px 12px;background:#0f172a;border:1px solid var(--line);border-radius:11px;color:#e6edf3;font-size:13px;outline:none;max-width:100%}input:focus,select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(6,182,212,.12)}select option{background:#0f172a;color:#e6edf3}label{font-size:12px;color:#93c5fd;display:block;margin-bottom:6px;font-weight:850}.main a:not(.btn):not(.nav-item){color:#67e8f9;text-decoration:none}.main a:not(.btn):not(.nav-item):hover{text-decoration:underline}p{color:var(--muted)}
.guide-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:16px;margin-bottom:18px}.guide-card{background:linear-gradient(180deg,var(--card),var(--card2));border:1px solid var(--line);border-radius:20px;padding:18px;box-shadow:var(--shadow)}.guide-card h3{font-size:16px;margin-bottom:10px;color:#dbeafe}.guide-card ul{margin-left:18px;color:#cbd5e1;line-height:1.65}.guide-card li{margin:4px 0}.guide-card .where{display:inline-flex;background:rgba(6,182,212,.12);border:1px solid rgba(6,182,212,.26);color:#67e8f9;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:900;margin-bottom:10px}.guide-card code,.codebox{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace}.codebox{white-space:pre-wrap;background:#07101f;border:1px solid #26354f;border-radius:14px;padding:12px;margin-top:10px;color:#bfdbfe;font-size:12px;line-height:1.55;overflow:auto}.warnbox{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.30);color:#fde68a;border-radius:16px;padding:13px 15px;margin-bottom:16px;font-weight:750}.okbox{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.30);color:#bbf7d0;border-radius:16px;padding:13px 15px;margin-bottom:16px;font-weight:750}.desc-cell{max-width:420px;white-space:normal;line-height:1.45;color:#cbd5e1}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px}.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.filters input,.filters select{width:auto;min-width:180px}.nav-item .count{background:#f85149;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;margin-left:auto}
@media(max-width:900px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}table{display:block;overflow-x:auto;white-space:nowrap}.form-row{display:grid;grid-template-columns:1fr}.btn,input,select{width:100%}}
@media(max-width:560px){.stats-grid{grid-template-columns:1fr}.main{padding:14px}.main>h1:first-of-type{display:block}.main>h1:first-of-type:after{display:inline-flex;margin-top:8px}}
.admin-footer{margin:26px 0 4px;text-align:center;color:rgba(127,144,170,.48);font-size:11px;font-weight:700;letter-spacing:.02em;opacity:.72;text-shadow:0 0 14px rgba(125,211,252,.14)}.admin-footer:before{content:"";display:block;width:120px;height:1px;background:linear-gradient(90deg,transparent,rgba(125,211,252,.28),transparent);margin:0 auto 12px}

/* === Hamburger Nav === */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 18px;height:56px;background:var(--side);border-bottom:1px solid var(--line);position:fixed;top:0;left:0;right:0;z-index:100}.hamburger{background:none;border:none;cursor:pointer;padding:8px;display:flex;flex-direction:column;gap:5px;border-radius:6px;transition:background .2s}.hamburger:hover{background:var(--card)}.hamburger span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px;transition:all .3s cubic-bezier(.4,0,.2,1)}.hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}.hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0)}.hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}.topbar-logo{font-size:15px;font-weight:800;color:var(--text)}.topbar-logo .blue{color:var(--cyan)}.topbar-right{width:36px;height:36px;border-radius:50%;background:var(--card);display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer}.nav-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:150;opacity:0;pointer-events:none;transition:opacity .3s}.nav-overlay.show{opacity:1;pointer-events:all}.sidebar-nav{position:fixed;top:0;left:0;bottom:0;width:270px;background:linear-gradient(180deg,var(--side),#0a1222);z-index:200;transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;overflow-y:auto;border-right:1px solid var(--line)}.sidebar-nav.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.5)}.sidebar-nav::-webkit-scrollbar{width:4px}.sidebar-nav::-webkit-scrollbar-thumb{background:var(--line);border-radius:99px}.sn-logo{padding:18px;border-bottom:1px solid var(--line)}.sn-logo .big{font-size:18px;font-weight:950;background:linear-gradient(135deg,var(--cyan),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent}.sn-logo .sub{color:var(--muted);font-size:10px;font-weight:700;margin-top:2px}.nav-group{padding:14px 0 4px}.nav-group-label{font-size:10px;font-weight:800;color:var(--muted);padding:0 18px 8px;text-transform:uppercase;letter-spacing:.12em}.main-content{padding:56px 22px 22px;min-height:100vh}.main-content h1{font-size:26px;font-weight:950;letter-spacing:-.035em;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:12px}.main-content h1:after{content:"Control Center";font-size:11px;letter-spacing:0;color:#bfdbfe;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);padding:6px 12px;border-radius:999px;font-weight:700}@media(max-width:768px){.main-content h1:after{display:none}.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}table{display:block;overflow-x:auto;white-space:nowrap}.form-row{display:grid;grid-template-columns:1fr}.btn,input,select{width:100%}}@media(max-width:480px){.stats-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
  <button class="hamburger" id="menuBtn" onclick="toggleNav()">
    <span></span><span></span><span></span>
  </button>
  <div class="topbar-logo">⚡ <span class="blue"><?= h(SITE_NAME) ?></span></div>
  <div class="topbar-right" onclick="location='?logout=1'" title="Thoát">🚪</div>
</div>

<!-- Overlay -->
<div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>

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
<?php if(isset($_GET['ok'])): ?><div class="alert alert-green">✅ Thao tác thành công!</div><?php endif ?>
<?php if(isset($_GET['err'])): ?><div class="alert" style="background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35);color:#fca5a5">⚠️ <?=htmlspecialchars($_GET['err'])?></div><?php endif ?>

<?php if($tab==='dashboard'): ?>
<h1>📊 Dashboard</h1>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-val blue"><?=$stats['users']?></div><div class="stat-label">👥 Người dùng</div></div>
  <div class="stat-card"><div class="stat-val orange"><?=$stats['orders_pending']?></div><div class="stat-label">🛒 Chờ thanh toán</div></div>
  <div class="stat-card"><div class="stat-val green"><?=$stats['orders_approved']?></div><div class="stat-label">✅ Đơn thành công</div></div>
  <div class="stat-card"><div class="stat-val green"><?=number_format($stats['revenue'],0,',','.')?> đ</div><div class="stat-label">💰 Doanh thu</div></div>
  <div class="stat-card"><div class="stat-val green"><?=$stats['keys_available']?></div><div class="stat-label">📦 Key trong pool</div></div>
  <div class="stat-card"><div class="stat-val blue"><?=$stats['keys_active']?></div><div class="stat-label">🔑 Key đang active</div></div>
  <div class="stat-card"><div class="stat-val"><?=$stats['keys_total']?></div><div class="stat-label">🔑 Tổng keys</div></div>
</div>

<h2 style="font-size:16px;margin-bottom:12px">🛒 Đơn chờ thanh toán</h2>
<?php
$pending = $db->query("SELECT o.*,u.telegram_username,u.full_name,g.name as game_name,COALESCE(p.name,at.name,o.order_type) as pkg_name,COALESCE(p.days,0) as days,k.key_code FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id LEFT JOIN packages p ON o.package_id=p.id AND o.order_type='key' LEFT JOIN account_types at ON o.account_type_id=at.id AND o.order_type='account' LEFT JOIN `keys` k ON k.order_id=o.id AND k.status='pending' WHERE o.status='pending' ORDER BY o.created_at DESC LIMIT 20")->fetchAll();
if($pending): ?>
<table>
<tr><th>Mã đơn</th><th>User</th><th>Game / Gói</th><th>Key đã tạo</th><th>Tiền</th><th>Thời gian</th><th>Thao tác</th></tr>
<?php foreach($pending as $o): ?>
<tr>
  <td><b><?=h($o['order_code'])?></b></td>
  <td>@<?=h($o['telegram_username'])?><br><small style="color:#8b949e"><?=h($o['full_name'])?></small></td>
  <td><?=h($o['game_name'])?><br><small style="color:#8b949e"><?=h($o['pkg_name'])?> (<?=$o['days']?> ngày)</small></td>
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
<?php else: ?><p style="color:#8b949e">Không có đơn nào chờ thanh toán ✅</p><?php endif ?>

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
$orders = $db->prepare("SELECT o.*,u.telegram_username,u.full_name,g.name as game_name,COALESCE(p.name,at.name,o.order_type) as pkg_name,COALESCE(p.days,0) as days,k.key_code,k.status as key_status FROM orders o JOIN users u ON o.user_id=u.id JOIN games g ON o.game_id=g.id LEFT JOIN packages p ON o.package_id=p.id AND o.order_type='key' LEFT JOIN account_types at ON o.account_type_id=at.id AND o.order_type='account' LEFT JOIN `keys` k ON k.order_id=o.id WHERE $sqlWhere ORDER BY o.created_at DESC LIMIT 100");
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
  var pkgs = <?=json_encode($db->query("SELECT id, game_id, name, days, price FROM packages WHERE is_active=1 ORDER BY days ASC")->fetchAll(), JSON_UNESCAPED_UNICODE)?>;
  pkgs.forEach(function(p) {
    if (p.game_id == gameId) {
      var opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.name + ' (' + p.days + ' ngày - ' + Number(p.price).toLocaleString('vi-VN') + 'đ)';
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
<tr><th>Key</th><th>Game</th><th>Gói</th><th>Ngày</th><th>Thao tác</th></tr>
<?php foreach($poolKeys as $k): ?>
<tr>
  <td style="font-family:monospace;font-size:12px"><?=h($k['key_code'])?></td>
  <td><?=h($k['game_name'])?></td>
  <td style="font-size:12px"><?=h($k['pkg_name'])?></td>
  <td><?=h($k['days'])?> ngày</td>
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
<tr><th>Key</th><th>User</th><th>Game / Gói</th><th>Ngày</th><th>Trạng thái</th><th>Hết hạn</th><th>Thao tác</th></tr>
<?php foreach($usedKeys as $k): $cls=['active'=>'green','expired'=>'orange','locked'=>'red','pending'=>'blue'][$k['status']]??'gray'; ?>
<tr>
  <td style="font-family:monospace;font-size:12px"><?=h($k['key_code'])?></td>
  <td>@<?=h($k['telegram_username'])?></td>
  <td style="font-size:12px"><b><?=h($k['game_name'])?></b><br><small style="color:#8b949e"><?=h($k['pkg_name'])?> · <?=h($k['key_type'])?><?php if($k['order_code']!=='--'): ?> · <?=h($k['order_code'])?><?php endif ?></small></td>
  <td><?=h($k['days'])?> ngày</td>
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
<h1>🎮 Quản lý Games</h1>
<div class="form-card">
<h3>➕ Thêm game mới</h3>
<form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="add_game">
<div class="form-row">
  <div><label>Tên game</label><input name="name" required placeholder="Free Fire"></div>
  <div><label>Package name</label><input name="pkg" required placeholder="com.dts.freefireth" style="width:220px"></div>
  <div><label>Link tải (download)</label><input name="download_url" placeholder="https://t.me/..." style="width:240px"></div>
  <div><label>Link chạy/play (nút ▶)</label><input name="play_url" placeholder="https://..." style="width:240px"></div>
  <div><label>Loại</label><select name="type"><option>NORMAL</option><option>VIP</option></select></div>
  <div><label>Loại Category</label><select name="category"><option value="key">Bán Key</option><option value="account">Bán Acc</option><option value="both">Cả Key + Acc</option></select></div>
  <div><label>Root type</label><select name="root"><option>Only Root</option><option>Root & NoRoot</option><option>NoRoot</option></select></div>
  <div><label>Thứ tự</label><input name="sort" type="number" value="0" style="width:70px"></div>
  <div><label>Icon (PNG/JPG, max 2MB)</label><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml"></div>
  <div style="padding-top:20px"><button class="btn btn-blue" type="submit">➕ Thêm</button></div>
</div>
</form>
</div>
<?php $games = $db->query("SELECT * FROM games ORDER BY sort_order")->fetchAll(); ?>
<table>
<tr><th>#</th><th>Icon</th><th>Tên game</th><th>Package</th><th>Link tải</th><th>Loại</th><th>Category</th><th>Root</th><th>Thứ tự</th><th>Active</th><th>Đổi icon</th><th>Thao tác</th></tr>
<?php foreach($games as $g): ?>
<tr>
<form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
  <input type="hidden" name="act" value="edit_game"><input type="hidden" name="id" value="<?=$g['id']?>">
  <td><?=$g['id']?></td>
  <td><?php if(!empty($g['icon_url'])): ?><img src="<?=h($g['icon_url'])?>" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;background:#0d1117"><?php else: ?><span style="color:#8b949e;font-size:11px">-</span><?php endif ?></td>
  <td><input name="name" value="<?=h($g['name'])?>" required style="width:150px"></td>
  <td><input name="pkg" value="<?=h($g['package_name'])?>" required style="width:220px"></td>
  <td><input name="download_url" value="<?=h($g['download_url'] ?? '')?>" placeholder="Link tải..." style="width:190px;margin-bottom:4px"><br><input name="play_url" value="<?=h($g['play_url'] ?? '')?>" placeholder="Link chạy ▶..." style="width:190px"></td>
  <td><select name="type"><option <?=$g['type']==='NORMAL'?'selected':''?>>NORMAL</option><option <?=$g['type']==='VIP'?'selected':''?>>VIP</option></select></td>
  <td><select name="category"><option value="key" <?=($g['category']??'key')==='key'?'selected':''?>>Key</option><option value="account" <?=($g['category']??'key')==='account'?'selected':''?>>Acc</option><option value="both" <?=($g['category']??'key')==='both'?'selected':''?>>Both</option></select></td>
  <td><select name="root"><option <?=$g['root_type']==='Only Root'?'selected':''?>>Only Root</option><option <?=$g['root_type']==='Root & NoRoot'?'selected':''?>>Root & NoRoot</option><option <?=$g['root_type']==='NoRoot'?'selected':''?>>NoRoot</option></select></td>
  <td><input name="sort" type="number" value="<?=$g['sort_order']?>" style="width:70px"></td>
  <td><select name="is_active"><option value="1" <?=$g['is_active']?'selected':''?>>Bật</option><option value="0" <?=!$g['is_active']?'selected':''?>>Tắt</option></select></td>
  <td><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" style="width:130px;font-size:11px"></td>
  <td><button class="btn btn-blue" type="submit">💾 Lưu</button>
</form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_game"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn btn-gray" type="submit"><?=$g['is_active']?'Tắt':'Bật'?></button></form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_game"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn btn-red" onclick="return confirm('Xoá game này? Các gói/order/key liên quan có thể bị ảnh hưởng.')">🗑 Xoá</button></form></td>
</tr>
<?php endforeach ?>
</table>

<?php elseif($tab==='packages'): ?>
<h1>📦 Quản lý Gói cước</h1>
<?php $games = $db->query("SELECT * FROM games ORDER BY is_active DESC, sort_order")->fetchAll(); ?>
<div class="form-card">
<h3>➕ Thêm gói mới</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="add_pkg">
<div class="form-row">
  <div><label>Game</label><select name="game_id"><?php foreach($games as $g):?><option value="<?=$g['id']?>"><?=h($g["name"])?></option><?php endforeach?></select></div>
  <div><label>Số ngày</label><input name="days" type="number" required placeholder="7" style="width:80px"></div>
  <div><label>Giá (đ)</label><input name="price" type="number" required placeholder="75000"></div>
  <div><label>Loại key</label><select name="key_type"><option>Normal</option><option>VIP</option></select></div>
  <div style="padding-top:20px"><button class="btn btn-blue" type="submit">➕ Thêm</button></div>
</div>
</form>
</div>
<?php $pkgs = $db->query("SELECT p.*,g.name as game_name FROM packages p JOIN games g ON p.game_id=g.id ORDER BY g.sort_order,p.days")->fetchAll(); ?>
<table>
<tr><th>Game</th><th>Tên gói</th><th>Số ngày</th><th>Giá</th><th>Loại</th><th>Active</th><th>Thao tác</th></tr>
<?php foreach($pkgs as $p): ?>
<tr>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
  <input type="hidden" name="act" value="edit_pkg"><input type="hidden" name="id" value="<?=$p['id']?>">
  <td><select name="game_id"><?php foreach($games as $g):?><option value="<?=$g['id']?>" <?=$p['game_id']==$g['id']?'selected':''?>><?=h($g["name"])?></option><?php endforeach?></select></td>
  <td><input name="name" value="<?=htmlspecialchars($p['name'])?>" required style="width:120px"></td>
  <td><input name="days" type="number" value="<?=$p['days']?>" required style="width:80px"></td>
  <td><input name="price" type="number" value="<?=$p['price']?>" required style="width:110px"></td>
  <td><select name="key_type"><option <?=$p['key_type']==='Normal'?'selected':''?>>Normal</option><option <?=$p['key_type']==='VIP'?'selected':''?>>VIP</option></select></td>
  <td><select name="is_active"><option value="1" <?=$p['is_active']?'selected':''?>>Bật</option><option value="0" <?=!$p['is_active']?'selected':''?>>Tắt</option></select></td>
  <td><button class="btn btn-blue" type="submit">💾 Lưu</button>
</form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_pkg"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-gray" type="submit"><?=$p['is_active']?'Tắt':'Bật'?></button></form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_pkg"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-red" onclick="return confirm('Xoá gói này?')">🗑</button></form></td>
</tr>
<?php endforeach ?>
</table>


<?php elseif($tab==='accounts'): ?>
<h1>🏪 Quản lý Accounts</h1>

<?php $gamesAll=$db->query("SELECT * FROM games ORDER BY sort_order")->fetchAll(); ?>
<?php $accGames=$db->query("SELECT * FROM games WHERE category IN ('account','both') ORDER BY sort_order")->fetchAll(); ?>
<?php $typesAll=$db->query("SELECT at.*,g.name game_name FROM account_types at JOIN games g ON at.game_id=g.id ORDER BY g.sort_order, at.sort_order")->fetchAll(); ?>

<div class="form-card">
<h3>🎮 Thêm game bán Acc</h3>
<form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="add_acc_game">
<div class="form-row">
  <div><label>Tên game</label><input name="name" required placeholder="Liên Quân Mobile"></div>
  <div><label>Thứ tự</label><input name="sort" type="number" value="0" style="width:70px"></div>
  <div><label>Icon (PNG/JPG, max 2MB)</label><input name="icon" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml"></div>
  <div style="padding-top:20px"><button class="btn btn-blue" type="submit">➕ Thêm game Acc</button></div>
</div>
</form>
</div>

<?php if($accGames): ?>
<div class="form-card">
<h3>🎯 Danh sách game Acc</h3>
<table>
<tr><th>Game</th><th>Loại acc</th><th>Giá</th><th>Stock</th><th>Active</th><th>Hành động</th></tr>
<?php foreach($accGames as $ag):
  $atS=$db->prepare("SELECT id,name,price,is_active FROM account_types WHERE game_id=? ORDER BY sort_order");
  $atS->execute([$ag['id']]);
  $accTypesForGame=$atS->fetchAll();
  if(!$accTypesForGame): ?>
  <tr><td><?=h($ag['name'])?></td><td colspan="5" style="color:#8b949e">Chưa có loại acc nào — thêm bên dưới
  <form method="POST" style="display:inline;float:right"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_game"><input type="hidden" name="id" value="<?=$ag['id']?>"><button class="btn btn-red" style="padding:4px 8px;font-size:11px" onclick="return confirm('Xoá game acc này? Toàn bộ acc và loại acc sẽ bị xoá.')">🗑 Xoá game</button></form>
  </td></tr>
  <?php else: foreach($accTypesForGame as $atg):
    $stk=$db->prepare("SELECT COUNT(*) FROM accounts WHERE account_type_id=? AND status='available'");
    $stk->execute([$atg['id']]); $stkCount=(int)$stk->fetchColumn();
  ?>
  <tr><td><?=h($ag['name'])?></td><td><?=h($atg['name'])?></td><td><?=number_format($atg['price'])?>đ</td><td><b style="color:<?=$stkCount>0?'#4ade80':'#f85149'?>"><?=$stkCount?></b></td><td><span class="badge <?=$atg['is_active']?'green':'gray'?>"><?=$atg['is_active']?'Bật':'Tắt'?></span></td>
  <td><form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_acc_type"><input type="hidden" name="id" value="<?=$atg['id']?>"><button class="btn btn-gray">Toggle</button></form>
  <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_acc_type"><input type="hidden" name="id" value="<?=$atg['id']?>"><button class="btn btn-red" onclick="return confirm('Xoá?')">🗑</button></form>
  <form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_game"><input type="hidden" name="id" value="<?=$ag['id']?>"><button class="btn btn-red" style="padding:4px 8px;font-size:11px;opacity:.75" onclick="return confirm('Xoá cả game <?=h($ag["name"])?>? Toàn bộ acc và loại acc sẽ bị xoá.')">🗑 Game</button></form></td></tr>
  <?php endforeach; endif; ?>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<div class="form-card">
<h3>➕ Thêm loại acc mới</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="add_acc_type">
<div class="form-row">
  <div><label>Game Acc</label><select name="game_id"><?php foreach($accGames as $g):?><option value="<?=$g['id']?>"><?=h($g["name"])?></option><?php endforeach?></select></div>
  <div><label>Tên loại acc</label><input name="name" required placeholder="Google, Facebook, Apple..." style="width:150px"></div>
  <div><label>Giá (đ)</label><input name="price" type="number" required placeholder="50000" style="width:120px"></div>
  <div><label>Mô tả</label><input name="description" placeholder="Mô tả thêm (tùy chọn)" style="width:200px"></div>
  <div><label>Thứ tự</label><input name="sort" type="number" value="0" style="width:70px"></div>
  <div style="padding-top:20px"><button class="btn btn-blue" type="submit">➕ Thêm</button></div>
</div>
</form>
</div>

<div class="form-card">
<h3>📥 Import acc (tk:mk)</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
<input type="hidden" name="act" value="import_accounts">
<div class="form-row">
  <div><label>Game Acc</label><select name="acc_game_id"><?php foreach($accGames as $g):?><option value="<?=$g['id']?>"><?=h($g["name"])?></option><?php endforeach?></select></div>
  <div><label>Loại acc</label><select name="acc_type_id"><?php foreach($typesAll as $t):?><option value="<?=$t['id']?>"><?=h($t["name"])?> (<?=h($t["game_name"])?>)</option><?php endforeach?></select></div>
  <div style="flex:1"><label>Danh sách acc (mỗi dòng: tk:mk hoặc tk|mk)</label>
    <textarea name="accounts" rows="6" placeholder="user1@gmail.com:pass123&#10;user2@gmail.com:pass456&#10;user3|pass789" style="width:100%;max-width:100%;background:#0d1117;color:#e6edf3;border:1px solid var(--line);border-radius:11px;padding:10px;font-family:monospace;font-size:13px"></textarea>
  </div>
  <div style="padding-top:20px"><button class="btn btn-blue" type="submit">📥 Import</button></div>
</div>
</form>
</div>

<?php if($typesAll): ?>
<h2>📋 Loại acc</h2>
<table>
<tr><th>#</th><th>Game</th><th>Tên loại</th><th>Giá</th><th>Stock</th><th>Active</th><th>Thao tác</th></tr>
<?php foreach($typesAll as $t):
  $stock = $db->prepare("SELECT COUNT(*) FROM accounts WHERE account_type_id=? AND status='available'");
  $stock->execute([$t['id']]);
  $availCount = (int)$stock->fetchColumn();
?>
<tr>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>">
  <input type="hidden" name="act" value="edit_acc_type"><input type="hidden" name="id" value="<?=$t['id']?>">
  <td><?=$t['id']?></td>
  <td><select name="game_id"><?php foreach($gamesAll as $g):?><option value="<?=$g['id']?>" <?=$t['game_id']==$g['id']?'selected':''?>><?=h($g["name"])?></option><?php endforeach?></select></td>
  <td><input name="name" value="<?=htmlspecialchars($t['name'])?>" required style="width:130px"></td>
  <td><input name="price" type="number" value="<?=$t['price']?>" required style="width:100px"></td>
  <td><b style="color:<?=$availCount>0?'#4ade80':'#f85149'?>"><?=$availCount?></b></td>
  <td><select name="is_active"><option value="1" <?=$t['is_active']?'selected':''?>>Bật</option><option value="0" <?=!$t['is_active']?'selected':''?>>Tắt</option></select></td>
  <td><button class="btn btn-blue" type="submit">💾 Lưu</button>
</form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="toggle_acc_type"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-gray"><?=$t['is_active']?'Tắt':'Bật'?></button></form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="del_acc_type"><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-red" onclick="return confirm('Xoá loại acc này? Các acc thuộc loại sẽ mất.')">🗑</button></form></td>
</tr>
<?php endforeach ?>
</table>
<?php endif ?>

<?php
$accs = $db->query("SELECT a.*, g.name game_name, at.name type_name FROM accounts a JOIN games g ON a.game_id=g.id JOIN account_types at ON a.account_type_id=at.id ORDER BY a.id DESC LIMIT 200")->fetchAll();
if($accs):
?>
<h2 style="margin-top:24px">📦 Danh sách Acc (200 gần nhất)</h2>
<table>
<tr><th>#</th><th>Game</th><th>Loại</th><th>Tài khoản</th><th>Mật khẩu</th><th>Trạng thái</th><th>Ngày</th><th>Thao tác</th></tr>
<?php foreach($accs as $a): ?>
<tr>
<td><?=$a['id']?></td>
<td><?=h($a['game_name'])?></td>
<td><span class="badge blue"><?=h($a['type_name'])?></span></td>
<td style="font-family:monospace"><?=h($a['username'])?></td>
<td style="font-family:monospace"><?=h($a['password'])?></td>
<td>
<?php if($a['status']=='available'): ?><span class="badge green">Có sẵn</span>
<?php elseif($a['status']=='pending'): ?><span class="badge orange">Đang chờ</span>
<?php else: ?><span class="badge gray">Đã bán</span>
<?php endif; ?>
</td>
<td><?=h($a['created_at'])?></td>
<td>
<?php if($a['status']=='available'): ?>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="delete_account"><input type="hidden" name="acc_id" value="<?=$a['id']?>"><button class="btn btn-red" onclick="return confirm('Xoá acc này?')" style="padding:5px 8px">🗑</button></form>
<?php endif; ?>
</td>
</tr>
<?php endforeach ?>
</table>
<?php endif; ?>


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
      opt.textContent = p.days + ' ngày (' + p.name + ')';
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
    'card'        => ['label' => '🎴 Card doithe poll',     'sched' => '*/2m',  'fresh_sec' => 360],
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
<form method="POST" class="form-card">
<input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="save_config">
<h3>Thông tin site/bot</h3><div class="form-row">
<?php foreach(['SITE_URL'=>'Site URL','SITE_NAME'=>'Site name','ADMIN_CHAT_ID'=>'Admin chat ID','BOT_USERNAME'=>'Bot username'] as $k=>$label): ?>
<div><label><?=$label?></label><input name="cfg[<?=$k?>]" value="<?=htmlspecialchars((string)hclouConfigValue($k))?>"></div>
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

<h3 style="margin-top:20px">💳 Nạp card qua doithe.vn (Ví user)</h3>
<div class="form-row">
<div style="flex:1;min-width:220px"><label>Partner ID</label><input style="width:100%;font-family:monospace" name="cfg[DOITHE_PARTNER_ID]" value="<?=htmlspecialchars((string)hclouConfigValue('DOITHE_PARTNER_ID'))?>" placeholder="Mã đối tác doithe.vn"></div>
<div style="flex:1;min-width:260px"><label>Partner Key</label><input style="width:100%;font-family:monospace" name="cfg[DOITHE_PARTNER_KEY]" value="<?=htmlspecialchars((string)hclouConfigValue('DOITHE_PARTNER_KEY'))?>" placeholder="Secret key để ký md5"></div>
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
    ['label'=>'🎴 Card doithe poll', 'schedule'=>'Mỗi 2 phút', 'url'=>rtrim(SITE_URL,'/').'/cron/run.php?token='.CRON_RUN_TOKEN.'&job=card'],
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
<div class="form-card"><h3>🧹 Bảo trì nhanh</h3><p>Tự chuyển key hết hạn sang expired, xoá key expired quá 3 ngày không gia hạn, và huỷ đơn pending quá 30 phút.</p><form method="POST" style="margin-top:12px"><input type="hidden" name="csrf" value="<?=h($_SESSION['admin_csrf'])?>"><input type="hidden" name="act" value="run_maintenance"><button class="btn btn-blue" type="submit">Chạy maintenance ngay</button></form><?php if(isset($_GET['maint'])):?><div class="codebox"><?=htmlspecialchars($_GET['maint'])?></div><?php endif; ?></div>
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
<div class="okbox">✅ Cập nhật thành công lên <b>v<?=h($_GET['v'] ?? '')?></b>! Đã ghi <?=(int)($_GET['n']??0)?> file. <b>Tải lại trang</b> để dùng bản mới.</div>
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
function toggleNav(){
  var s=document.getElementById('sidebarNav'),o=document.getElementById('navOverlay'),b=document.getElementById('menuBtn');
  s.classList.toggle('open'); o.classList.toggle('show'); b.classList.toggle('open');
}
function closeNav(){
  document.getElementById('sidebarNav').classList.remove('open');
  document.getElementById('navOverlay').classList.remove('show');
  document.getElementById('menuBtn').classList.remove('open');
}
function closeUpdModal(){ var m=document.getElementById('updModal'); if(m){ m.style.display='none'; try{sessionStorage.setItem('upd_dismiss','1')}catch(e){} } }
</script>

<?php
// Modal thông báo cập nhật (hiện 1 lần/phiên, mọi tab trừ tab update)
if ($tab !== 'update') {
    $_mLic = @json_decode((string)@file_get_contents(APP_ROOT.'/data/.lic'), true);
    $_mCur = @json_decode((string)@file_get_contents(APP_ROOT.'/version.json'), true)['version'] ?? '1.0.0';
    if (is_array($_mLic) && !empty($_mLic['latest']) && version_compare($_mLic['latest'], $_mCur, '>')):
?>
<div id="updModal" style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px">
  <div style="max-width:400px;width:100%;background:#161b22;border:1px solid #30363d;border-radius:18px;padding:28px;text-align:center">
    <div style="font-size:46px;margin-bottom:10px">🎉</div>
    <h2 style="font-size:19px;margin-bottom:8px;color:#fde68a">Có bản cập nhật mới!</h2>
    <p style="color:#9fb2cf;font-size:14px;line-height:1.6;margin-bottom:20px">Phiên bản <b style="color:#4ade80">v<?=h($_mLic['latest'])?></b> đã sẵn sàng.<br>Bạn đang dùng v<?=h($_mCur)?>.</p>
    <div style="display:flex;gap:10px">
      <button onclick="closeUpdModal()" style="flex:1;padding:12px;border:1px solid #30363d;border-radius:11px;background:transparent;color:#9fb2cf;font-weight:700;cursor:pointer;font-size:14px">Để sau</button>
      <a href="?tab=update" style="flex:1;padding:12px;border-radius:11px;background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;font-weight:800;text-decoration:none;font-size:14px;display:flex;align-items:center;justify-content:center">🔄 Cập nhật ngay</a>
    </div>
  </div>
</div>
<script>
(function(){ try{ if(!sessionStorage.getItem('upd_dismiss')){ document.getElementById('updModal').style.display='flex'; } }catch(e){ document.getElementById('updModal').style.display='flex'; } })();
</script>
<?php endif; } ?>
</body>
</html>
