<?php
// =============================================
// LICENSE SERVER - ADMIN PANEL (login user/pass)
// Tabs: Licenses · Activations · Releases · Account
// =============================================
require_once __DIR__ . '/config.php';
session_start();

// ---------- LOGOUT ----------
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php'); exit;
}

// ---------- LOGIN ----------
if (!lsRequireLogin()) {
    $loginErr = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_login'])) {
        $att = lsLoginCheck();
        if ($att['blocked']) {
            $loginErr = 'Quá nhiều lần thử. Đợi ' . ceil(max(0, $att['unblock_at'] - time()) / 60) . ' phút.';
        } elseif (!lsCsrfOk()) {
            $loginErr = 'Phiên không hợp lệ, tải lại trang.';
        } else {
            $u = trim($_POST['username'] ?? '');
            $p = (string)($_POST['password'] ?? '');
            $st = lsDB()->prepare("SELECT * FROM ls_admins WHERE username=? LIMIT 1");
            $st->execute([$u]);
            $adm = $st->fetch();
            if ($adm && password_verify($p, $adm['password_hash'])) {
                lsLoginReset();
                session_regenerate_id(true);
                $_SESSION['ls_auth'] = true;
                $_SESSION['ls_last'] = time();
                $_SESSION['ls_user'] = $adm['username'];
                $_SESSION['ls_uid']  = $adm['id'];
                header('Location: index.php'); exit;
            }
            lsLoginInc();
            $loginErr = 'Sai tài khoản hoặc mật khẩu. Còn ' . max(0, $att['remaining'] - 1) . ' lượt.';
        }
    }
    $csrf = lsCsrf();
    ?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>License Server · Login</title><style>
    *{box-sizing:border-box}body{font-family:-apple-system,Segoe UI,sans-serif;background:radial-gradient(circle at 20% 10%,rgba(37,99,235,.25),transparent 30%),#0b1020;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
    .card{width:400px;max-width:100%;background:#161b22;border:1px solid #30363d;border-radius:18px;padding:30px}
    .logo{width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px}
    h1{text-align:center;font-size:20px;margin:0 0 4px}.sub{text-align:center;color:#8b949e;font-size:12px;margin-bottom:20px}
    label{display:block;font-size:12px;color:#9fb2cf;margin:12px 0 5px;font-weight:700}
    input{width:100%;padding:12px;background:#0d1117;border:1px solid #30363d;border-radius:10px;color:#e6edf3;font-size:14px}
    button{width:100%;margin-top:18px;padding:12px;border:0;border-radius:11px;background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;font-weight:800;cursor:pointer;font-size:15px}
    .err{background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.35);color:#fca5a5;padding:10px;border-radius:10px;margin-bottom:12px;font-size:13px}
    </style></head><body>
    <form class="card" method="POST">
    <div class="logo">🔑</div><h1>License Server</h1><div class="sub">Quản lý license · TRAN VAN HOANG</div>
    <?php if(!empty($loginErr)):?><div class="err"><?=h($loginErr)?></div><?php endif;?>
    <input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="do_login" value="1">
    <label>Tài khoản</label><input name="username" autofocus required>
    <label>Mật khẩu</label><input name="password" type="password" required>
    <button type="submit">Đăng nhập</button>
    </form></body></html><?php
    exit;
}

// ================= ĐÃ LOGIN =================
$db   = lsDB();
$csrf = lsCsrf();
$tab  = $_GET['tab'] ?? 'licenses';
$msg  = ''; $msgErr = '';

// ---------- POST ACTIONS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['do_login'])) {
    if (!lsCsrfOk()) { header('Location: index.php?err=csrf'); exit; }
    $act = $_POST['act'] ?? '';

    if ($act === 'create_license') {
        $key = lsGenKey();
        $db->prepare("INSERT INTO ls_licenses (license_key, customer_name, note, max_domains, expires_at) VALUES (?,?,?,?,?)")
           ->execute([$key, trim($_POST['customer_name'] ?? ''), trim($_POST['note'] ?? ''),
                      max(1, (int)($_POST['max_domains'] ?? 1)),
                      ($_POST['expires_at'] ?? '') !== '' ? $_POST['expires_at'] . ' 23:59:59' : null]);
        header('Location: index.php?tab=licenses&ok=created'); exit;
    }
    if ($act === 'update_license') {
        $exp = ($_POST['expires_at'] ?? '') !== '' ? $_POST['expires_at'] . ' 23:59:59' : null;
        $db->prepare("UPDATE ls_licenses SET customer_name=?, note=?, max_domains=?, status=?, expires_at=? WHERE id=?")
           ->execute([trim($_POST['customer_name'] ?? ''), trim($_POST['note'] ?? ''),
                      max(1, (int)($_POST['max_domains'] ?? 1)), $_POST['status'] ?? 'active', $exp, (int)$_POST['id']]);
        header('Location: index.php?tab=licenses&ok=updated'); exit;
    }
    if ($act === 'del_license') {
        $db->prepare("DELETE FROM ls_licenses WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: index.php?tab=licenses&ok=deleted'); exit;
    }
    if ($act === 'del_activation') {
        $db->prepare("DELETE FROM ls_activations WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: index.php?tab=activations&ok=1'); exit;
    }
    if ($act === 'upload_release') {
        try {
            $ver = trim($_POST['version'] ?? '');
            if (!preg_match('/^\d+\.\d+\.\d+$/', $ver)) throw new Exception('Version phải dạng x.y.z (vd 1.0.1)');
            if (empty($_FILES['zip']) || $_FILES['zip']['error'] !== UPLOAD_ERR_OK) throw new Exception('Chưa chọn file zip hoặc upload lỗi');
            $tmp = $_FILES['zip']['tmp_name'];
            $za = new ZipArchive();
            if ($za->open($tmp) !== true) throw new Exception('File không phải zip hợp lệ');
            $za->close();
            $fname = 'release_' . $ver . '_' . bin2hex(random_bytes(4)) . '.zip';
            if (!move_uploaded_file($tmp, LS_ROOT . '/releases/' . $fname)) throw new Exception('Không lưu được file');
            // Đánh dấu latest
            if (!empty($_POST['is_latest'])) $db->exec("UPDATE ls_releases SET is_latest=0");
            $db->prepare("INSERT INTO ls_releases (version, zip_filename, changelog, is_latest) VALUES (?,?,?,?)")
               ->execute([$ver, $fname, trim($_POST['changelog'] ?? ''), !empty($_POST['is_latest']) ? 1 : 0]);
            header('Location: index.php?tab=releases&ok=uploaded'); exit;
        } catch (Throwable $e) {
            header('Location: index.php?tab=releases&err=' . urlencode($e->getMessage())); exit;
        }
    }
    if ($act === 'set_latest') {
        $db->exec("UPDATE ls_releases SET is_latest=0");
        $db->prepare("UPDATE ls_releases SET is_latest=1 WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: index.php?tab=releases&ok=1'); exit;
    }
    if ($act === 'del_release') {
        $r = $db->prepare("SELECT zip_filename FROM ls_releases WHERE id=?");
        $r->execute([(int)$_POST['id']]);
        $f = $r->fetchColumn();
        if ($f) @unlink(LS_ROOT . '/releases/' . basename($f));
        $db->prepare("DELETE FROM ls_releases WHERE id=?")->execute([(int)$_POST['id']]);
        header('Location: index.php?tab=releases&ok=deleted'); exit;
    }
    if ($act === 'change_pass') {
        $np = (string)($_POST['new_pass'] ?? '');
        if (strlen($np) >= 6) {
            $db->prepare("UPDATE ls_admins SET password_hash=? WHERE id=?")
               ->execute([password_hash($np, PASSWORD_DEFAULT), (int)$_SESSION['ls_uid']]);
            header('Location: index.php?tab=account&ok=pass'); exit;
        }
        header('Location: index.php?tab=account&err=' . urlencode('Mật khẩu >= 6 ký tự')); exit;
    }
}
if (isset($_GET['ok']))  $msg = 'Thao tác thành công!';
if (isset($_GET['err'])) $msgErr = $_GET['err'] === 'csrf' ? 'CSRF token lỗi' : $_GET['err'];

// stats
$cntLic = $db->query("SELECT COUNT(*) FROM ls_licenses")->fetchColumn();
$cntActive = $db->query("SELECT COUNT(*) FROM ls_licenses WHERE status='active'")->fetchColumn();
$cntAct = $db->query("SELECT COUNT(*) FROM ls_activations")->fetchColumn();
$cntRel = $db->query("SELECT COUNT(*) FROM ls_releases")->fetchColumn();
?><!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>License Server</title><style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:-apple-system,Segoe UI,sans-serif;background:#0b1020;color:#e6edf3;font-size:14px}
.top{display:flex;align-items:center;gap:14px;padding:14px 20px;background:#0f172a;border-bottom:1px solid #26354f;position:sticky;top:0;z-index:10}
.top .brand{font-weight:900;font-size:16px}.top .brand span{color:#06b6d4}
.top .sp{flex:1}.top a.lo{color:#fca5a5;text-decoration:none;font-weight:700;font-size:13px}
.nav{display:flex;gap:6px;padding:14px 20px;flex-wrap:wrap}
.nav a{padding:9px 16px;border-radius:10px;text-decoration:none;color:#aab8d0;font-weight:700;font-size:13px;background:#161b22;border:1px solid #26354f}
.nav a.on{background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;border-color:transparent}
.wrap{padding:0 20px 40px;max-width:1100px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px}
.stat{background:#161b22;border:1px solid #26354f;border-radius:14px;padding:16px}.stat .n{font-size:26px;font-weight:900}.stat .l{color:#8b949e;font-size:12px;margin-top:4px}
.card{background:#111827;border:1px solid #26354f;border-radius:14px;padding:18px;margin-bottom:18px}
h2{font-size:16px;margin-bottom:12px;color:#dbeafe}h3{font-size:14px;margin-bottom:10px;color:#dbeafe}
table{width:100%;border-collapse:collapse;font-size:13px;background:#111827;border:1px solid #26354f;border-radius:12px;overflow:hidden}
th{text-align:left;padding:11px 12px;background:#0f172a;color:#9fb7d7;font-size:11px;text-transform:uppercase;border-bottom:1px solid #26354f}
td{padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.1)}tr:last-child td{border:0}
input,select,textarea{padding:9px 11px;background:#0d1117;border:1px solid #30363d;border-radius:9px;color:#e6edf3;font-size:13px;font-family:inherit}
label{font-size:12px;color:#93c5fd;display:block;margin-bottom:5px;font-weight:700}
.row{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:10px}
.btn{padding:8px 13px;border:0;border-radius:9px;font-weight:800;cursor:pointer;font-size:12px;color:#fff;text-decoration:none;display:inline-block}
.btn.blue{background:linear-gradient(135deg,#2563eb,#06b6d4)}.btn.red{background:#dc2626}.btn.gray{background:#374151}.btn.green{background:#16a34a}
.mono{font-family:ui-monospace,Menlo,monospace}
.badge{padding:3px 9px;border-radius:99px;font-size:11px;font-weight:800}
.badge.green{background:rgba(34,197,94,.14);color:#86efac}.badge.red{background:rgba(239,68,68,.14);color:#fca5a5}.badge.gray{background:rgba(148,163,184,.14);color:#cbd5e1}
.alert{padding:11px 14px;border-radius:11px;margin-bottom:14px;font-weight:700;font-size:13px}
.alert.ok{background:rgba(34,197,94,.13);border:1px solid rgba(34,197,94,.3);color:#86efac}
.alert.err{background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
small{color:#8b949e}
</style></head><body>
<div class="top"><div class="brand">🔑 License <span>Server</span></div><div class="sp"></div>
<small style="color:#8b949e">👤 <?=h($_SESSION['ls_user'])?></small>
<a class="lo" href="?logout=1">🚪 Thoát</a></div>
<div class="nav">
<a href="?tab=licenses" class="<?=$tab==='licenses'?'on':''?>">🎫 Licenses</a>
<a href="?tab=activations" class="<?=$tab==='activations'?'on':''?>">🌐 Web đang chạy</a>
<a href="?tab=releases" class="<?=$tab==='releases'?'on':''?>">📦 Bản cập nhật</a>
<a href="?tab=account" class="<?=$tab==='account'?'on':''?>">⚙️ Tài khoản</a>
</div>
<div class="wrap">
<?php if($msg):?><div class="alert ok">✅ <?=h($msg)?></div><?php endif;?>
<?php if($msgErr):?><div class="alert err">⚠️ <?=h($msgErr)?></div><?php endif;?>

<div class="stats">
<div class="stat"><div class="n"><?=$cntLic?></div><div class="l">Tổng license</div></div>
<div class="stat"><div class="n" style="color:#4ade80"><?=$cntActive?></div><div class="l">Đang active</div></div>
<div class="stat"><div class="n" style="color:#60a5fa"><?=$cntAct?></div><div class="l">Web đang chạy</div></div>
<div class="stat"><div class="n" style="color:#fbbf24"><?=$cntRel?></div><div class="l">Bản code</div></div>
</div>

<?php if($tab==='licenses'): ?>
<div class="card"><h3>➕ Tạo license mới</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="create_license">
<div class="row">
<div><label>Tên khách</label><input name="customer_name" placeholder="Nguyễn Văn A"></div>
<div><label>Số domain tối đa</label><input name="max_domains" type="number" min="1" value="1" style="width:90px"></div>
<div><label>Hết hạn (trống = vĩnh viễn)</label><input name="expires_at" type="date"></div>
<div style="flex:1"><label>Ghi chú</label><input name="note" style="width:100%" placeholder="vd: gói VIP, sđt..."></div>
<div><button class="btn blue" type="submit">Tạo key</button></div>
</div></form></div>

<?php $lics = $db->query("SELECT l.*, (SELECT COUNT(*) FROM ls_activations a WHERE a.license_id=l.id) acts FROM ls_licenses l ORDER BY l.id DESC")->fetchAll(); ?>
<table>
<tr><th>Key</th><th>Khách</th><th>Domain</th><th>Hết hạn</th><th>TT</th><th>Sửa / Xoá</th></tr>
<?php foreach($lics as $l): ?>
<tr>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="update_license"><input type="hidden" name="id" value="<?=$l['id']?>">
<td class="mono" style="font-size:12px"><?=h($l['license_key'])?><?php if($l['note']):?><br><small><?=h($l['note'])?></small><?php endif;?></td>
<td><input name="customer_name" value="<?=h($l['customer_name'])?>" style="width:120px"></td>
<td><input name="max_domains" type="number" min="1" value="<?=$l['max_domains']?>" style="width:55px"> <small><?=$l['acts']?> dùng</small></td>
<td><input name="expires_at" type="date" value="<?=$l['expires_at']?date('Y-m-d',strtotime($l['expires_at'])):''?>" style="width:130px"></td>
<td><select name="status">
<option value="active" <?=$l['status']==='active'?'selected':''?>>Active</option>
<option value="suspended" <?=$l['status']==='suspended'?'selected':''?>>Khoá</option>
<option value="expired" <?=$l['status']==='expired'?'selected':''?>>Hết hạn</option>
</select></td>
<td><input type="hidden" name="note" value="<?=h($l['note'])?>"><button class="btn blue" type="submit">💾</button>
</form>
<form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="del_license"><input type="hidden" name="id" value="<?=$l['id']?>"><button class="btn red" onclick="return confirm('Xoá license này?')">🗑</button></form>
</td>
</tr>
<?php endforeach; if(!$lics):?><tr><td colspan="6" style="text-align:center;color:#8b949e;padding:24px">Chưa có license</td></tr><?php endif;?>
</table>

<?php elseif($tab==='activations'): ?>
<h2>🌐 Web/domain đang chạy</h2>
<?php $acts = $db->query("SELECT a.*, l.license_key, l.customer_name, l.status FROM ls_activations a JOIN ls_licenses l ON a.license_id=l.id ORDER BY a.last_seen DESC")->fetchAll(); ?>
<table>
<tr><th>Domain</th><th>License / Khách</th><th>Version</th><th>IP</th><th>Last seen</th><th></th></tr>
<?php foreach($acts as $a):
  $mins = (time() - strtotime($a['last_seen'])) / 60;
  $live = $mins < 60; ?>
<tr>
<td><b><?=h($a['domain'])?></b> <?php if($live):?><span class="badge green">● live</span><?php else:?><span class="badge gray">offline</span><?php endif;?></td>
<td class="mono" style="font-size:12px"><?=h($a['license_key'])?><br><small><?=h($a['customer_name'])?></small></td>
<td><?=h($a['app_version']?:'-')?></td>
<td class="mono" style="font-size:12px"><?=h($a['ip'])?></td>
<td><small><?=h($a['last_seen'])?></small></td>
<td><form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="del_activation"><input type="hidden" name="id" value="<?=$a['id']?>"><button class="btn gray" onclick="return confirm('Xoá activation? (giải phóng slot domain)')">✕</button></form></td>
</tr>
<?php endforeach; if(!$acts):?><tr><td colspan="6" style="text-align:center;color:#8b949e;padding:24px">Chưa có web nào kích hoạt</td></tr><?php endif;?>
</table>

<?php elseif($tab==='releases'): ?>
<div class="card"><h3>📤 Upload bản code mới (.zip)</h3>
<form method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="upload_release">
<div class="row">
<div><label>Version (x.y.z)</label><input name="version" placeholder="1.0.1" required style="width:100px"></div>
<div><label>File zip</label><input name="zip" type="file" accept=".zip" required></div>
<div><label><input type="checkbox" name="is_latest" value="1" checked style="width:auto"> Đặt làm bản mới nhất</label></div>
</div>
<div class="row"><div style="flex:1"><label>Changelog</label><textarea name="changelog" rows="3" style="width:100%" placeholder="- Fix abc&#10;- Thêm xyz"></textarea></div></div>
<button class="btn blue" type="submit">Upload</button>
</form></div>

<?php $rels = $db->query("SELECT * FROM ls_releases ORDER BY id DESC")->fetchAll(); ?>
<table>
<tr><th>Version</th><th>File</th><th>Changelog</th><th>Mới nhất</th><th>Ngày</th><th></th></tr>
<?php foreach($rels as $r): ?>
<tr>
<td><b>v<?=h($r['version'])?></b></td>
<td class="mono" style="font-size:11px"><?=h($r['zip_filename'])?></td>
<td style="max-width:280px;white-space:pre-wrap;font-size:12px"><?=h($r['changelog'])?></td>
<td><?php if($r['is_latest']):?><span class="badge green">LATEST</span><?php else:?><form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="set_latest"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn gray">Đặt latest</button></form><?php endif;?></td>
<td><small><?=date('d/m/Y H:i',strtotime($r['created_at']))?></small></td>
<td><form method="POST" style="display:inline"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="del_release"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn red" onclick="return confirm('Xoá bản này?')">🗑</button></form></td>
</tr>
<?php endforeach; if(!$rels):?><tr><td colspan="6" style="text-align:center;color:#8b949e;padding:24px">Chưa có bản code nào</td></tr><?php endif;?>
</table>

<?php elseif($tab==='account'): ?>
<div class="card" style="max-width:420px"><h3>🔒 Đổi mật khẩu</h3>
<form method="POST"><input type="hidden" name="csrf" value="<?=h($csrf)?>"><input type="hidden" name="act" value="change_pass">
<label>Mật khẩu mới (≥6)</label><input name="new_pass" type="password" required style="width:100%;margin-bottom:12px">
<button class="btn blue" type="submit">Đổi mật khẩu</button>
</form></div>
<?php endif; ?>
</div></body></html>
