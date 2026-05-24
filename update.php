<?php
// =============================================
// UPDATE.PHP - PULL CODE MỚI TỪ GIT AN TOÀN
// =============================================
// Bảo vệ: chỉ chạy được khi đăng nhập admin.
// =============================================

require_once __DIR__ . '/config.php';

// Auth: dùng session admin (giống admin/index.php).
// FIX: admin/index.php set $_SESSION['admin_auth'] + admin_last_seen — không phải admin_logged_in.
session_start();
$adminLoggedIn = !empty($_SESSION['admin_auth'])
    && !empty($_SESSION['admin_last_seen'])
    && (time() - (int)$_SESSION['admin_last_seen'] <= ADMIN_SESSION_TTL);
if (!$adminLoggedIn) {
    http_response_code(403);
    exit('Cần đăng nhập admin để cập nhật code. Vào /admin/ trước.');
}
// Gia hạn session khi admin có hoạt động trên update.php.
$_SESSION['admin_last_seen'] = time();

// CSRF token (dùng chung với admin/index.php).
if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['admin_csrf'];

header('Content-Type: text/html; charset=utf-8');

function runCmd(string $cmd): array {
    $output = [];
    $code = 0;
    exec($cmd . ' 2>&1', $output, $code);
    return ['code' => $code, 'output' => implode("\n", $output)];
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'status';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check: chặn attacker dụ admin click link external → forge pull/migrate.
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token không hợp lệ. Tải lại trang.');
    }
    switch ($action) {
        case 'check':
            // Check git status + remote
            $r1 = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git fetch --quiet 2>&1');
            $r2 = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git status -uno 2>&1');
            $r3 = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git log HEAD..origin/main --oneline 2>&1');
            $result = [
                'fetch'  => $r1,
                'status' => $r2,
                'pending_commits' => $r3,
            ];
            break;

        case 'pull':
            // Backup config trước
            $backupName = 'config.local.php.bk_update_' . date('Ymd_His');
            @copy(APP_ROOT . '/config.local.php', APP_ROOT . '/' . $backupName);

            // Stash any local changes (config.local.php không bị stash vì đã trong .gitignore)
            $r = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git pull origin main 2>&1');
            $result = ['pull' => $r, 'backup' => $backupName];
            break;

        case 'migrate':
            // Liệt kê file migration chưa chạy
            $migrationsDir = APP_ROOT . '/migrations';
            $files = is_dir($migrationsDir) ? glob($migrationsDir . '/*.sql') : [];
            sort($files);
            $result = ['migrations' => array_map('basename', $files), 'note' => 'Chạy thủ công qua phpMyAdmin theo thứ tự tên file.'];
            break;
    }
}

// Tình trạng git
$gitVersion = runCmd('git --version 2>&1');
$gitRev     = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git rev-parse --short HEAD 2>&1');
$gitBranch  = runCmd('cd ' . escapeshellarg(APP_ROOT) . ' && git rev-parse --abbrev-ref HEAD 2>&1');
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Update HCLOU SERVER</title>
<style>
body{font-family:-apple-system,sans-serif;background:#0e1117;color:#e6edf3;max-width:800px;margin:30px auto;padding:20px}
h1{margin-top:0}
.box{background:#161b22;border:1px solid #30363d;border-radius:8px;padding:20px;margin-bottom:16px}
.btn{background:#1f6feb;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-size:14px;margin-right:8px}
.btn:hover{background:#388bfd}
.btn.danger{background:#da3633}
.btn.danger:hover{background:#f85149}
pre{background:#0d1117;padding:12px;border-radius:6px;border:1px solid #30363d;overflow-x:auto;font-size:12px;color:#7ee787}
code{background:#0d1117;padding:2px 6px;border-radius:4px;color:#79c0ff}
.warn{background:#3a2c0c;color:#d29922;border:1px solid #d29922;padding:12px;border-radius:6px;margin-bottom:12px}
</style>
</head>
<body>
<h1>🔄 Update HCLOU SERVER</h1>

<div class="box">
<h3>Tình trạng Git</h3>
<p>
<strong>Version:</strong> <code><?= h($gitVersion['output']) ?></code><br>
<strong>Branch:</strong> <code><?= h($gitBranch['output']) ?></code><br>
<strong>Commit:</strong> <code><?= h($gitRev['output']) ?></code>
</p>
<?php if ($gitVersion['code'] !== 0): ?>
<div class="warn">⚠️ Hosting này không có lệnh <code>git</code>. Bạn cần upload code thủ công.</div>
<?php endif; ?>
</div>

<div class="box">
<h3>1. Kiểm tra update</h3>
<form method="post" style="display:inline">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="check">
<button class="btn" type="submit">🔍 Check update</button>
</form>
</div>

<div class="box">
<h3>2. Pull code mới</h3>
<div class="warn">
⚠️ Trước khi pull: <code>config.local.php</code> sẽ được backup tự động.<br>
Mọi file local (data/, logs/) sẽ KHÔNG bị ảnh hưởng (đã trong .gitignore).
</div>
<form method="post" onsubmit="return confirm('Pull code mới từ git?')">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="pull">
<button class="btn danger" type="submit">⬇️ Pull code mới</button>
</form>
</div>

<div class="box">
<h3>3. Migration database (nếu có)</h3>
<form method="post" style="display:inline">
<input type="hidden" name="csrf" value="<?= h($csrf) ?>">
<input type="hidden" name="action" value="migrate">
<button class="btn" type="submit">📋 List migration</button>
</form>
<p style="color:#7d8590;font-size:13px;margin-top:8px">Chạy migration thủ công qua phpMyAdmin theo thứ tự tên file.</p>
</div>

<?php if ($result): ?>
<div class="box">
<h3>Kết quả</h3>
<pre><?= h(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
</div>
<?php endif; ?>

<p style="text-align:center;margin-top:30px"><a href="<?= h(SITE_URL) ?>/admin/" style="color:#79c0ff">← Quay lại Admin</a></p>
</body>
</html>
