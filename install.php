<?php
// =============================================
// INSTALL.PHP - WEB INSTALLER WIZARD
// =============================================
// Chạy 1 lần khi setup hosting mới.
// Sau khi xong tự tạo .install_lock để khóa.
//
// Truy cập: https://your-domain.com/install.php
// =============================================

define('HCLOU_ALLOW_NO_CONFIG', true);
define('APP_ROOT', __DIR__);

// Chặn nếu đã cài rồi
$lockFile = APP_ROOT . '/.install_lock';
if (file_exists($lockFile) && empty($_GET['force'])) {
    http_response_code(403);
    die('<!doctype html><meta charset="utf-8"><title>Installer Locked</title>
    <div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;border:2px solid #d9534f;border-radius:8px;">
    <h2 style="color:#d9534f">🔒 Installer đã được khóa</h2>
    <p>Hệ thống đã được cài đặt. File <code>.install_lock</code> đang tồn tại.</p>
    <p>Nếu muốn cài lại: xóa file <code>.install_lock</code> qua FTP/cPanel.</p>
    <p><strong>KHUYẾN NGHỊ</strong>: Xóa hoặc rename file <code>install.php</code> sau khi cài xong để an toàn.</p>
    </div>');
}

session_start();
$step = isset($_GET['step']) ? max(1, min(8, (int)$_GET['step'])) : 1;
$errors = [];
$success = [];

// =============================================
// HELPERS
// =============================================
function detectSiteUrl() {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    return $proto . '://' . $host . $base;
}

function genToken($bytes = 32) { return bin2hex(random_bytes($bytes)); }

function testDbConnection($host, $name, $user, $pass) {
    try {
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        return [true, $pdo];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

function importSqlFile($pdo, $sqlFile) {
    $sql = file_get_contents($sqlFile);
    if ($sql === false) throw new Exception('Không đọc được file SQL');
    // Tách statement theo `;` ở cuối dòng
    $statements = preg_split('/;\s*\n/', $sql);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || strpos($stmt, '--') === 0) continue;
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (PDOException $e) {
            // Bỏ qua lỗi "table already exists" nếu user chạy lại
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw new Exception('SQL error: ' . $e->getMessage() . ' | Statement: ' . substr($stmt, 0, 100));
            }
        }
    }
    return $count;
}

function testTelegramBot($token) {
    $ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    return $data['ok'] ?? false ? $data['result'] : false;
}

function setTelegramWebhook($token, $url, $secret) {
    $ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POSTFIELDS => http_build_query([
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]),
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function testMbBankApi($apiKey) {
    $ch = curl_init('https://queenvps.com/api/historymb/' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'response' => substr($res, 0, 200)];
}

function writeConfigLocal($data) {
    $template = <<<'PHP'
<?php
// =============================================
// CONFIG LOCAL - DO INSTALLER TẠO
// =============================================
// File này CHỨA SECRET, KHÔNG được push lên git.
// Đã có trong .gitignore.
// =============================================

// --- Database ---
define('DB_HOST',    %DB_HOST%);
define('DB_NAME',    %DB_NAME%);
define('DB_USER',    %DB_USER%);
define('DB_PASS',    %DB_PASS%);
define('DB_CHARSET', 'utf8mb4');

// --- Telegram Bot ---
define('BOT_TOKEN',     %BOT_TOKEN%);
define('ADMIN_CHAT_ID', %ADMIN_CHAT_ID%);
define('BOT_USERNAME',  %BOT_USERNAME%);

// --- Website ---
define('SITE_URL',  %SITE_URL%);
define('SITE_NAME', %SITE_NAME%);

// --- Admin panel ---
define('ADMIN_PASSWORD_HASH', %ADMIN_PASSWORD_HASH%);
define('ADMIN_SESSION_TTL',   3600);

// --- Bank info ---
define('BANK_NAME',      %BANK_NAME%);
define('BANK_ACCOUNT',   %BANK_ACCOUNT%);
define('BANK_OWNER',     %BANK_OWNER%);
define('VIETQR_BANK_ID', %VIETQR_BANK_ID%);

// --- MBBANK API ---
define('MBBANK_HISTORY_API_KEY',    %MBBANK_HISTORY_API_KEY%);
define('MBBANK_AUTO_APPROVE_ENABLED', true);

// --- Shortlink APIs ---
define('LINK4M_API_TOKEN',   %LINK4M_API_TOKEN%);
define('YEUMONEY_API_TOKEN', %YEUMONEY_API_TOKEN%);
define('FREE_GETKEY_ENABLED', true);

// --- Secure tokens (random, generated by installer) ---
define('CRON_RUN_TOKEN',          %CRON_RUN_TOKEN%);
define('AUTOMATION_RUN_TOKEN',    %AUTOMATION_RUN_TOKEN%);
define('TELEGRAM_WEBHOOK_SECRET', %TELEGRAM_WEBHOOK_SECRET%);

// --- Timezone ---
define('APP_TIMEZONE', 'Asia/Ho_Chi_Minh');
PHP;

    foreach ($data as $key => $val) {
        $template = str_replace('%' . $key . '%', var_export($val, true), $template);
    }
    return file_put_contents(APP_ROOT . '/config.local.php', $template, LOCK_EX);
}

// =============================================
// PROCESS POST
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $_SESSION['installer'] = $_SESSION['installer'] ?? [];

    try {
        switch ($action) {
            case 'check_db':
                [$ok, $result] = testDbConnection(
                    trim($_POST['db_host']),
                    trim($_POST['db_name']),
                    trim($_POST['db_user']),
                    $_POST['db_pass']
                );
                if (!$ok) throw new Exception('Kết nối DB thất bại: ' . $result);

                $_SESSION['installer']['db_host'] = trim($_POST['db_host']);
                $_SESSION['installer']['db_name'] = trim($_POST['db_name']);
                $_SESSION['installer']['db_user'] = trim($_POST['db_user']);
                $_SESSION['installer']['db_pass'] = $_POST['db_pass'];

                // Import schema
                $count = importSqlFile($result, APP_ROOT . '/database.sql');
                $success[] = "Kết nối DB thành công + import {$count} câu lệnh SQL.";
                header('Location: install.php?step=3');
                exit;

            case 'save_site':
                $_SESSION['installer']['site_url']  = trim($_POST['site_url']);
                $_SESSION['installer']['site_name'] = trim($_POST['site_name']);
                header('Location: install.php?step=4');
                exit;

            case 'check_telegram':
                $token = trim($_POST['bot_token']);
                $info  = testTelegramBot($token);
                if (!$info) throw new Exception('BOT_TOKEN không hợp lệ hoặc bot bị disable');

                $_SESSION['installer']['bot_token']     = $token;
                $_SESSION['installer']['bot_username']  = $info['username'] ?? '';
                $_SESSION['installer']['admin_chat_id'] = trim($_POST['admin_chat_id']);
                $success[] = "Bot OK: @" . ($info['username'] ?? '?');
                header('Location: install.php?step=5');
                exit;

            case 'save_bank':
                $_SESSION['installer']['bank_name']    = trim($_POST['bank_name']);
                $_SESSION['installer']['bank_account'] = trim($_POST['bank_account']);
                $_SESSION['installer']['bank_owner']   = trim($_POST['bank_owner']);
                $_SESSION['installer']['vietqr_bank_id'] = trim($_POST['vietqr_bank_id']) ?: '970422';
                $_SESSION['installer']['mbbank_api_key'] = trim($_POST['mbbank_api_key']);
                $_SESSION['installer']['link4m_token']   = trim($_POST['link4m_token']);
                $_SESSION['installer']['yeumoney_token'] = trim($_POST['yeumoney_token']);
                header('Location: install.php?step=6');
                exit;

            case 'save_admin':
                $pwd = (string)$_POST['admin_password'];
                if (strlen($pwd) < 8) throw new Exception('Mật khẩu admin phải >= 8 ký tự');
                $_SESSION['installer']['admin_password_hash'] = password_hash($pwd, PASSWORD_DEFAULT);

                // Auto-generate tokens
                $_SESSION['installer']['cron_run_token']    = genToken(24);
                $_SESSION['installer']['automation_token']  = genToken(16);
                $_SESSION['installer']['webhook_secret']    = genToken(16);
                header('Location: install.php?step=7');
                exit;

            case 'finalize':
                $i = $_SESSION['installer'];
                // Ghi config.local.php
                $written = writeConfigLocal([
                    'DB_HOST'                  => $i['db_host'],
                    'DB_NAME'                  => $i['db_name'],
                    'DB_USER'                  => $i['db_user'],
                    'DB_PASS'                  => $i['db_pass'],
                    'BOT_TOKEN'                => $i['bot_token'],
                    'ADMIN_CHAT_ID'            => $i['admin_chat_id'],
                    'BOT_USERNAME'             => $i['bot_username'],
                    'SITE_URL'                 => $i['site_url'],
                    'SITE_NAME'                => $i['site_name'],
                    'ADMIN_PASSWORD_HASH'      => $i['admin_password_hash'],
                    'BANK_NAME'                => $i['bank_name'],
                    'BANK_ACCOUNT'             => $i['bank_account'],
                    'BANK_OWNER'               => $i['bank_owner'],
                    'VIETQR_BANK_ID'           => $i['vietqr_bank_id'],
                    'MBBANK_HISTORY_API_KEY'   => $i['mbbank_api_key'],
                    'LINK4M_API_TOKEN'         => $i['link4m_token'],
                    'YEUMONEY_API_TOKEN'       => $i['yeumoney_token'],
                    'CRON_RUN_TOKEN'           => $i['cron_run_token'],
                    'AUTOMATION_RUN_TOKEN'     => $i['automation_token'],
                    'TELEGRAM_WEBHOOK_SECRET'  => $i['webhook_secret'],
                ]);
                if (!$written) throw new Exception('Không ghi được config.local.php. Kiểm tra quyền thư mục.');

                // Set Telegram webhook
                $webhookUrl = rtrim($i['site_url'], '/') . '/webhook.php';
                $webhookRes = setTelegramWebhook($i['bot_token'], $webhookUrl, $i['webhook_secret']);
                $_SESSION['installer']['webhook_result'] = $webhookRes;

                // Tạo admin record trong DB
                try {
                    [$ok, $pdo] = testDbConnection($i['db_host'], $i['db_name'], $i['db_user'], $i['db_pass']);
                    if ($ok) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO admins (telegram_id, username, role) VALUES (?, ?, 'superadmin')");
                        $stmt->execute([(int)$i['admin_chat_id'], 'admin']);
                    }
                } catch (Throwable $e) { /* skip */ }

                // Tạo lock file
                file_put_contents($lockFile, date('c') . "\n" . ($i['site_url'] ?? ''));

                header('Location: install.php?step=8');
                exit;
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

// =============================================
// HTML LAYOUT
// =============================================
$siteUrl = $_SESSION['installer']['site_url'] ?? detectSiteUrl();
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cài đặt HCLOU SERVER</title>
<style>
*{box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#0e1117;color:#e6edf3;margin:0;padding:20px;min-height:100vh}
.container{max-width:760px;margin:30px auto;background:#161b22;border:1px solid #30363d;border-radius:12px;padding:30px;box-shadow:0 8px 24px rgba(0,0,0,.4)}
h1{margin:0 0 8px;font-size:24px}
.sub{color:#7d8590;margin:0 0 24px;font-size:14px}
.steps{display:flex;gap:6px;margin-bottom:30px;flex-wrap:wrap}
.step{flex:1;min-width:60px;padding:10px 6px;text-align:center;border-radius:6px;background:#21262d;font-size:11px;color:#7d8590;border:1px solid #30363d}
.step.active{background:#1f6feb;color:#fff;border-color:#1f6feb}
.step.done{background:#238636;color:#fff;border-color:#238636}
.form-group{margin-bottom:16px}
label{display:block;margin-bottom:6px;font-size:13px;font-weight:600;color:#e6edf3}
.hint{font-size:12px;color:#7d8590;margin-top:4px}
input[type=text],input[type=password],input[type=url],input[type=number]{width:100%;padding:10px 12px;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#e6edf3;font-size:14px;font-family:inherit}
input:focus{outline:none;border-color:#1f6feb}
.btn{display:inline-block;padding:10px 20px;background:#238636;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;text-decoration:none}
.btn:hover{background:#2ea043}
.btn.primary{background:#1f6feb}
.btn.primary:hover{background:#388bfd}
.alert{padding:12px 16px;border-radius:6px;margin-bottom:16px;font-size:14px}
.alert-error{background:#3a1c1c;color:#ffa198;border:1px solid #f85149}
.alert-success{background:#0c2912;color:#3fb950;border:1px solid #238636}
.alert-info{background:#0c2741;color:#79c0ff;border:1px solid #1f6feb}
code{background:#0d1117;padding:2px 6px;border-radius:4px;font-family:monospace;font-size:13px;border:1px solid #30363d;color:#79c0ff;word-break:break-all}
pre{background:#0d1117;padding:14px;border-radius:6px;border:1px solid #30363d;overflow-x:auto;font-size:13px}
.check{color:#3fb950;font-weight:bold}
.cron-table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}
.cron-table th,.cron-table td{text-align:left;padding:8px;border-bottom:1px solid #30363d;vertical-align:top}
.cron-table th{color:#7d8590;font-weight:600;background:#0d1117}
</style>
</head>
<body>
<div class="container">
<h1>🚀 Cài đặt HCLOU SERVER</h1>
<p class="sub">Wizard tự động cấu hình hệ thống. Bước <?= $step ?>/8.</p>

<div class="steps">
<?php
$labels = [1=>'Kiểm tra',2=>'Database',3=>'Website',4=>'Telegram',5=>'Bank',6=>'Admin',7=>'Tokens',8=>'Hoàn tất'];
foreach ($labels as $n => $lbl):
    $cls = $n < $step ? 'done' : ($n === $step ? 'active' : '');
?>
<div class="step <?= $cls ?>"><?= $n ?>. <?= $lbl ?></div>
<?php endforeach; ?>
</div>

<?php foreach ($errors as $e): ?>
<div class="alert alert-error">❌ <?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
<?php foreach ($success as $s): ?>
<div class="alert alert-success">✅ <?= htmlspecialchars($s) ?></div>
<?php endforeach; ?>

<?php if ($step === 1): // SYSTEM CHECK ?>
<h2>Bước 1: Kiểm tra hệ thống</h2>
<table style="width:100%;border-collapse:collapse">
<?php
$checks = [
    'PHP >= 7.4'           => version_compare(PHP_VERSION, '7.4.0', '>='),
    'Extension: pdo_mysql' => extension_loaded('pdo_mysql'),
    'Extension: curl'      => extension_loaded('curl'),
    'Extension: mbstring'  => extension_loaded('mbstring'),
    'Extension: json'      => extension_loaded('json'),
    'Extension: openssl'   => extension_loaded('openssl'),
    'Ghi được APP_ROOT'    => is_writable(APP_ROOT),
    'Thư mục data/ tồn tại'=> is_dir(APP_ROOT . '/data') || @mkdir(APP_ROOT . '/data', 0755, true),
    'database.sql tồn tại' => file_exists(APP_ROOT . '/database.sql'),
];
$allOk = true;
foreach ($checks as $name => $ok) {
    $allOk = $allOk && $ok;
    echo '<tr><td style="padding:8px;border-bottom:1px solid #30363d">' . htmlspecialchars($name) . '</td>';
    echo '<td style="padding:8px;border-bottom:1px solid #30363d;text-align:right">' . ($ok ? '<span class="check">✅ OK</span>' : '<span style="color:#f85149">❌ FAIL</span>') . '</td></tr>';
}
?>
</table>
<p style="margin-top:20px">PHP version: <code><?= PHP_VERSION ?></code></p>
<?php if ($allOk): ?>
<a class="btn primary" href="?step=2">Tiếp tục →</a>
<?php else: ?>
<div class="alert alert-error">Có vấn đề về hệ thống. Vui lòng fix trước khi tiếp tục.</div>
<?php endif; ?>

<?php elseif ($step === 2): // DATABASE ?>
<h2>Bước 2: Cấu hình Database</h2>
<form method="post">
<input type="hidden" name="action" value="check_db">
<div class="form-group"><label>Host</label><input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? '127.0.0.1') ?>" required></div>
<div class="form-group"><label>Tên database</label><input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
<div class="hint">Phải tạo sẵn database trống trên cPanel trước. Installer sẽ tự import schema.</div></div>
<div class="form-group"><label>Username</label><input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required></div>
<div class="form-group"><label>Password</label><input type="password" name="db_pass" required></div>
<button class="btn primary" type="submit">Kiểm tra + Import →</button>
</form>

<?php elseif ($step === 3): // WEBSITE ?>
<h2>Bước 3: Thông tin Website</h2>
<form method="post">
<input type="hidden" name="action" value="save_site">
<div class="form-group"><label>URL site (KHÔNG có dấu / cuối)</label>
<input type="url" name="site_url" value="<?= htmlspecialchars($_SESSION['installer']['site_url'] ?? detectSiteUrl()) ?>" required>
<div class="hint">VD: <code>https://your-domain.com</code></div></div>
<div class="form-group"><label>Tên site</label>
<input type="text" name="site_name" value="<?= htmlspecialchars($_SESSION['installer']['site_name'] ?? 'HCLOU SERVER') ?>" required></div>
<button class="btn primary" type="submit">Tiếp tục →</button>
</form>

<?php elseif ($step === 4): // TELEGRAM ?>
<h2>Bước 4: Telegram Bot</h2>
<div class="alert alert-info">
1. Tạo bot tại <code>@BotFather</code> trên Telegram → lấy BOT_TOKEN.<br>
2. Lấy ADMIN_CHAT_ID: chat với <code>@userinfobot</code> hoặc <code>@getidsbot</code>.
</div>
<form method="post">
<input type="hidden" name="action" value="check_telegram">
<div class="form-group"><label>BOT_TOKEN</label>
<input type="text" name="bot_token" value="<?= htmlspecialchars($_POST['bot_token'] ?? '') ?>" required placeholder="123456789:AAH...">
<div class="hint">Installer sẽ gọi getMe để xác nhận token hợp lệ.</div></div>
<div class="form-group"><label>ADMIN_CHAT_ID</label>
<input type="text" name="admin_chat_id" value="<?= htmlspecialchars($_POST['admin_chat_id'] ?? '') ?>" required placeholder="123456789">
<div class="hint">Là số ID Telegram của bạn (admin chính).</div></div>
<button class="btn primary" type="submit">Kiểm tra bot →</button>
</form>

<?php elseif ($step === 5): // BANK ?>
<h2>Bước 5: Thông tin Ngân hàng + API</h2>
<form method="post">
<input type="hidden" name="action" value="save_bank">
<div class="form-group"><label>Tên ngân hàng</label>
<input type="text" name="bank_name" value="<?= htmlspecialchars($_POST['bank_name'] ?? 'MBBANK') ?>" required></div>
<div class="form-group"><label>Số tài khoản</label>
<input type="text" name="bank_account" value="<?= htmlspecialchars($_POST['bank_account'] ?? '') ?>" required></div>
<div class="form-group"><label>Tên chủ tài khoản</label>
<input type="text" name="bank_owner" value="<?= htmlspecialchars($_POST['bank_owner'] ?? '') ?>" required></div>
<div class="form-group"><label>VIETQR Bank ID</label>
<input type="text" name="vietqr_bank_id" value="<?= htmlspecialchars($_POST['vietqr_bank_id'] ?? '970422') ?>">
<div class="hint">MBBank = 970422. Xem mã ngân hàng tại <code>vietqr.io</code>.</div></div>
<div class="form-group"><label>MBBANK API Key (Queenvps)</label>
<input type="text" name="mbbank_api_key" value="<?= htmlspecialchars($_POST['mbbank_api_key'] ?? '') ?>" required>
<div class="hint">Lấy từ queenvps.com (liên hệ Zalo/Messenger).</div></div>
<div class="form-group"><label>LINK4M Token</label>
<input type="text" name="link4m_token" value="<?= htmlspecialchars($_POST['link4m_token'] ?? '') ?>"></div>
<div class="form-group"><label>YEUMONEY Token</label>
<input type="text" name="yeumoney_token" value="<?= htmlspecialchars($_POST['yeumoney_token'] ?? '') ?>"></div>
<button class="btn primary" type="submit">Tiếp tục →</button>
</form>

<?php elseif ($step === 6): // ADMIN PASSWORD ?>
<h2>Bước 6: Mật khẩu Admin</h2>
<form method="post">
<input type="hidden" name="action" value="save_admin">
<div class="form-group"><label>Mật khẩu admin (>= 8 ký tự)</label>
<input type="password" name="admin_password" required minlength="8">
<div class="hint">Dùng để đăng nhập <code>/admin/</code>. Lưu ý: viết ra giấy, không có nơi recover.</div></div>
<button class="btn primary" type="submit">Tiếp tục →</button>
</form>

<?php elseif ($step === 7): // CONFIRM + GENERATE TOKENS ?>
<h2>Bước 7: Xác nhận và Generate Token</h2>
<div class="alert alert-info">
Installer sẽ tự generate các token bảo mật (random 32+ ký tự):
<ul style="margin:8px 0 0 20px">
<li><code>CRON_RUN_TOKEN</code> — bảo vệ URL cron</li>
<li><code>AUTOMATION_RUN_TOKEN</code> — bảo vệ automation</li>
<li><code>TELEGRAM_WEBHOOK_SECRET</code> — verify webhook từ Telegram</li>
</ul>
</div>
<h3>Cấu hình của bạn:</h3>
<table style="width:100%;border-collapse:collapse;font-size:13px">
<?php
$summary = [
    'Site URL'        => $_SESSION['installer']['site_url']      ?? '?',
    'Site Name'       => $_SESSION['installer']['site_name']     ?? '?',
    'Database'        => ($_SESSION['installer']['db_user'] ?? '') . '@' . ($_SESSION['installer']['db_host'] ?? '') . '/' . ($_SESSION['installer']['db_name'] ?? ''),
    'Bot Username'    => '@' . ($_SESSION['installer']['bot_username'] ?? '?'),
    'Admin Chat ID'   => $_SESSION['installer']['admin_chat_id'] ?? '?',
    'Bank'            => ($_SESSION['installer']['bank_name'] ?? '?') . ' - ' . ($_SESSION['installer']['bank_account'] ?? '?'),
    'Bank Owner'      => $_SESSION['installer']['bank_owner']    ?? '?',
];
foreach ($summary as $k => $v) {
    echo '<tr><td style="padding:6px;border-bottom:1px solid #30363d;color:#7d8590">' . htmlspecialchars($k) . '</td>';
    echo '<td style="padding:6px;border-bottom:1px solid #30363d"><code>' . htmlspecialchars((string)$v) . '</code></td></tr>';
}
?>
</table>
<form method="post" style="margin-top:20px">
<input type="hidden" name="action" value="finalize">
<button class="btn primary" type="submit">Hoàn tất cài đặt →</button>
<a class="btn" href="?step=1" style="background:#6e7681;margin-left:8px">Bắt đầu lại</a>
</form>

<?php elseif ($step === 8): // DONE ?>
<h2>🎉 Cài đặt hoàn tất!</h2>

<?php $wh = $_SESSION['installer']['webhook_result'] ?? null; ?>
<?php if ($wh && !empty($wh['ok'])): ?>
<div class="alert alert-success">✅ Webhook Telegram đã set thành công.</div>
<?php else: ?>
<div class="alert alert-error">⚠️ Webhook Telegram set thất bại: <?= htmlspecialchars(json_encode($wh, JSON_UNESCAPED_UNICODE)) ?>
<br>Bạn có thể set lại thủ công sau.</div>
<?php endif; ?>

<h3>📋 SETUP CRON JOBS (BẮT BUỘC)</h3>
<p>Vào cPanel → <strong>Cron Jobs</strong> → thêm 5 job sau (paste URL bằng <code>wget</code> hoặc <code>curl</code>):</p>

<?php
$tok      = $_SESSION['installer']['cron_run_token'] ?? '';
$autoTok  = $_SESSION['installer']['automation_token'] ?? '';
$siteUrl  = rtrim($_SESSION['installer']['site_url'] ?? '', '/');
$cronJobs = [
    ['MBBANK Auto-bank',   '*/1 * * * *', "{$siteUrl}/cron_run.php?token={$tok}&job=mbbank",       'Duyệt thanh toán tự động'],
    ['Maintenance',        '*/5 * * * *', "{$siteUrl}/cron_run.php?token={$tok}&job=maintenance",  'Xóa key hết hạn, hủy đơn quá 15 phút'],
    ['Monitor',            '*/5 * * * *', "{$siteUrl}/cron_run.php?token={$tok}&job=monitor",      'Cảnh báo lỗi qua Telegram'],
    ['Automation Daily',   '0 8 * * *',   "{$siteUrl}/cron_run.php?token={$tok}&job=automation",   'Báo cáo hàng ngày 8h'],
    ['Health Check',       '0 9 * * *',   "{$siteUrl}/cron_run.php?token={$tok}&job=health",       'Health check 9h'],
];
?>
<table class="cron-table">
<tr><th>Tên</th><th>Schedule</th><th>Command</th></tr>
<?php foreach ($cronJobs as [$name, $sch, $url, $desc]): ?>
<tr>
<td><strong><?= htmlspecialchars($name) ?></strong><br><small style="color:#7d8590"><?= htmlspecialchars($desc) ?></small></td>
<td><code><?= htmlspecialchars($sch) ?></code></td>
<td><code style="font-size:11px">wget -q -O - "<?= htmlspecialchars($url) ?>" &gt;/dev/null 2&gt;&amp;1</code></td>
</tr>
<?php endforeach; ?>
</table>

<h3 style="margin-top:30px">🔒 BẢO MẬT - LÀM NGAY</h3>
<div class="alert alert-info">
<ol style="margin:0 0 0 20px">
<li>Xóa hoặc rename file <code>install.php</code> (mặc dù đã có <code>.install_lock</code>).</li>
<li>Xóa file <code>config.local.sample.php</code> (chỉ để tham khảo).</li>
<li>Đảm bảo <code>config.local.php</code> chmod 600.</li>
<li>Test bot Telegram bằng cách gửi <code>/start</code>.</li>
<li>Vào <code><?= htmlspecialchars($siteUrl) ?>/admin/</code> đăng nhập với mật khẩu vừa tạo.</li>
</ol>
</div>

<p style="margin-top:30px"><a class="btn primary" href="<?= htmlspecialchars($siteUrl) ?>" target="_blank">🌐 Mở trang chủ</a>
<a class="btn" href="<?= htmlspecialchars($siteUrl) ?>/admin/" target="_blank" style="margin-left:8px">🔑 Admin panel</a></p>

<?php
session_destroy();
endif;
?>

</div>
</body>
</html>
