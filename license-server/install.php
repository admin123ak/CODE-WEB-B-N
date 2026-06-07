<?php
// =============================================
// LICENSE SERVER - INSTALLER
// Tạo config.local.php, import DB, tạo admin đầu tiên.
// =============================================
define('LS_ROOT', __DIR__);
@mkdir(LS_ROOT . '/data', 0755, true);
@mkdir(LS_ROOT . '/releases', 0755, true);

$cfgLocal = LS_ROOT . '/config.local.php';
$lock     = LS_ROOT . '/.ls_install_lock';

if (file_exists($lock) || file_exists($cfgLocal)) {
    die('Installer đã khóa (đã cài). Xoá config.local.php + .ls_install_lock qua FTP nếu muốn cài lại.');
}

session_start();
$err = '';
$ok  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = (string)($_POST['db_pass'] ?? '');
    $auser  = trim($_POST['admin_user'] ?? '');
    $apass  = (string)($_POST['admin_pass'] ?? '');

    try {
        if ($dbName === '' || $dbUser === '' || $auser === '' || strlen($apass) < 6) {
            throw new Exception('Điền đủ thông tin. Mật khẩu admin >= 6 ký tự.');
        }
        // Test DB
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Import schema
        $sql = file_get_contents(LS_ROOT . '/database.sql');
        $pdo->exec($sql);

        // Tạo admin đầu tiên
        $hash = password_hash($apass, PASSWORD_DEFAULT);
        $st = $pdo->prepare("INSERT INTO ls_admins (username, password_hash) VALUES (?, ?)");
        $st->execute([$auser, $hash]);

        // Sinh signing secret
        $secret = bin2hex(random_bytes(32));

        // Ghi config.local.php
        $cfg = "<?php\n"
             . "define('LS_DB_HOST', " . var_export($dbHost, true) . ");\n"
             . "define('LS_DB_NAME', " . var_export($dbName, true) . ");\n"
             . "define('LS_DB_USER', " . var_export($dbUser, true) . ");\n"
             . "define('LS_DB_PASS', " . var_export($dbPass, true) . ");\n"
             . "define('LS_SIGNING_SECRET', " . var_export($secret, true) . ");\n"
             . "define('LS_ADMIN_SESSION_TTL', 3600);\n";
        if (file_put_contents($cfgLocal, $cfg) === false) throw new Exception('Không ghi được config.local.php');
        @file_put_contents($lock, date('c'));

        $ok = 'Cài đặt thành công! LS_SIGNING_SECRET: <code style="background:#0d1117;padding:3px 6px;border-radius:4px">' . h($secret) . '</code><br>'
            . '<b>LƯU LẠI SECRET NÀY</b> — dán vào client (LICENSE_PUBLIC_SECRET). '
            . '<a href="index.php">→ Vào đăng nhập</a>';
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cài đặt License Server</title>
<style>
*{box-sizing:border-box}body{font-family:-apple-system,Segoe UI,sans-serif;background:#0b1020;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
.card{width:460px;max-width:100%;background:#161b22;border:1px solid #30363d;border-radius:16px;padding:28px}
h1{font-size:20px;margin:0 0 18px}label{display:block;font-size:12px;color:#9fb2cf;margin:12px 0 5px;font-weight:700}
input{width:100%;padding:11px 12px;background:#0d1117;border:1px solid #30363d;border-radius:9px;color:#e6edf3;font-size:14px}
button{width:100%;margin-top:18px;padding:12px;border:0;border-radius:10px;background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;font-weight:800;font-size:15px;cursor:pointer}
.err{background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.35);color:#fca5a5;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:13px}
.ok{background:rgba(34,197,94,.13);border:1px solid rgba(34,197,94,.35);color:#86efac;padding:12px;border-radius:10px;margin-bottom:12px;font-size:13px;line-height:1.6}
a{color:#67e8f9}
</style></head><body>
<form class="card" method="POST">
<h1>⚙️ Cài đặt License Server</h1>
<?php if($err):?><div class="err">⚠️ <?=h($err)?></div><?php endif;?>
<?php if($ok):?><div class="ok"><?=$ok?></div><?php else:?>
<label>DB Host</label><input name="db_host" value="127.0.0.1">
<label>DB Name</label><input name="db_name" required placeholder="license_server">
<label>DB User</label><input name="db_user" required>
<label>DB Password</label><input name="db_pass" type="password">
<label>Admin username</label><input name="admin_user" required placeholder="admin">
<label>Admin password (≥6 ký tự)</label><input name="admin_pass" type="password" required>
<button type="submit">Cài đặt</button>
<?php endif;?>
</form></body></html>
