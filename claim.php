<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$t  = $_GET['t'] ?? '';
$fk = null;

if ($t) {
    $stmt = $db->prepare("SELECT fk.*, g.name game_name, p.name pkg_name, p.days
        FROM free_keys fk
        JOIN games g    ON fk.game_id    = g.id
        JOIN packages p ON fk.package_id = p.id
        WHERE fk.claim_token = ?");
    $stmt->execute([$t]);
    $fk = $stmt->fetch();
}

function claimPage($title, $msg, $ok = false, $extra = '') {
    $ico = $ok ? '✅' : '⚠️';
    // $title, $msg đã được escape bởi caller; $extra là HTML khối được build kiểm soát
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HCLOU Claim</title><style>
body{margin:0;min-height:100vh;background:radial-gradient(circle at 20% 10%,#1f6feb55,transparent 30%),#070b14;color:#e6edf3;font-family:-apple-system,Segoe UI,sans-serif;display:flex;align-items:center;justify-content:center;padding:20px}
.card{max-width:420px;width:100%;background:linear-gradient(160deg,rgba(17,24,39,.95),rgba(10,14,26,.98));border:1px solid rgba(56,189,248,.15);border-radius:28px;padding:28px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.5);backdrop-filter:blur(20px)}
.ico{font-size:52px;margin-bottom:10px;animation:bounceIn .5s ease}
@keyframes bounceIn{0%{transform:scale(0);opacity:0}60%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
.title{font-size:20px;font-weight:900;margin:8px 0}
.msg{font-size:14px;color:#94a3b8;line-height:1.6;margin-bottom:14px}
.key-box{background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);border-radius:16px;padding:18px;margin:16px 0}
.key-code{font-size:22px;font-weight:900;color:#34d399;font-family:"SF Mono","JetBrains Mono",monospace;letter-spacing:2px;word-break:break-all}
.key-meta{font-size:12px;color:#64748b;margin-top:8px}
.key-meta span{color:#94a3b8;font-weight:700}
.btn{display:block;margin-top:14px;padding:14px;border-radius:16px;border:none;font-weight:900;font-size:14px;cursor:pointer;width:100%;transition:all .2s;text-decoration:none}
.btn:active{transform:scale(.97)}
.btn.primary{background:linear-gradient(135deg,#10b981,#06b6d4);color:#fff;box-shadow:0 4px 20px rgba(16,185,129,.3)}
.btn.copy{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 20px rgba(99,102,241,.3)}
.btn.outline{background:transparent;border:1px solid rgba(148,163,184,.2);color:#94a3b8}
.desc{font-size:13px;color:#94a3b8;line-height:1.5;margin:10px 0}
input[type=text]{width:100%;padding:14px;border-radius:14px;border:1px solid rgba(56,189,248,.2);background:rgba(15,23,42,.8);color:#e6edf3;font-size:15px;text-align:center;outline:0;margin:14px 0}
input[type=text]:focus{border-color:#38bdf8;box-shadow:0 0 16px rgba(56,189,248,.15)}
</style></head><body><div class="card"><div class="ico">' . $ico . '</div><h2 class="title">' . $title . '</h2><p class="msg">' . $msg . '</p>' . $extra . '</div></body></html>';
    exit;
}

// Helper: build HTML cho 1 key đã có
function renderKeyBox($keyCode, $gameName, $days, $siteUrl) {
    return '<div class="key-box"><div class="key-code">' . h($keyCode) . '</div>'
         . '<div class="key-meta">🎮 ' . h($gameName) . ' · <span>' . (int)$days . ' ngày</span></div></div>'
         . '<button class="btn copy" onclick="copyKey(' . json_encode($keyCode) . ')">📋 Copy Key</button>'
         . '<a class="btn outline" href="' . h($siteUrl) . '">🔑 Vào Mini App</a>'
         . '<script>function copyKey(t){navigator.clipboard.writeText(t).then(function(){alert("Đã copy!")}).catch(function(){prompt("Copy key:",t)})}</script>';
}

if (!$fk) claimPage('Link không hợp lệ', 'Token claim không tồn tại.');

if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) {
    claimPage('Key free đã hết hạn', 'Key này không còn khả dụng. Vui lòng quay lại sau.');
}

$tg = $_GET['telegram_id'] ?? 0;
if ($tg && !ctype_digit((string)$tg)) $tg = 0;

// =============================================
// CASE 1: Có telegram_id → claim trực tiếp
// =============================================
if ($tg) {
    // Tìm hoặc tạo user
    $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$tg]);
    $user = $stmt->fetch();
    if (!$user) {
        $db->prepare("INSERT INTO users (telegram_id, telegram_username, full_name) VALUES (?, '', ?)")
           ->execute([$tg, 'User' . $tg]);
        $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$tg]);
        $user = $stmt->fetch();
    }

    // Kiểm tra user đã có claim cho free_key này chưa (đảm bảo 1 user / 1 free_key)
    $chkExisting = $db->prepare("SELECT k.key_code, k.expire_at
        FROM free_key_claims fkc
        LEFT JOIN `keys` k ON fkc.key_id = k.id
        WHERE fkc.free_key_id = ? AND fkc.user_id = ?
        LIMIT 1");
    $chkExisting->execute([$fk['id'], $user['id']]);
    if ($exist = $chkExisting->fetch()) {
        claimPage(
            'Bạn đã nhận key free này rồi! 🎉',
            'Key của bạn đã sẵn sàng sử dụng.',
            true,
            renderKeyBox($exist['key_code'] ?: $fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL)
        );
    }

    // Kiểm tra hôm nay đã nhận free key nào chưa (mỗi user 1 free / ngày)
    $today = date('Y-m-d');
    $chk = $db->prepare("SELECT k.key_code
        FROM free_key_claims fkc
        LEFT JOIN `keys` k ON fkc.key_id = k.id
        WHERE fkc.user_id = ? AND DATE(fkc.claimed_at) = ?
        ORDER BY fkc.claimed_at DESC LIMIT 1");
    $chk->execute([$user['id'], $today]);
    if ($old = $chk->fetch()) {
        claimPage(
            'Bạn đã nhận key hôm nay rồi! 🎉',
            'Key của bạn: <b style="color:#34d399">' . h($old['key_code']) . '</b>',
            true,
            renderKeyBox($old['key_code'], $fk['game_name'], $fk['days'], SITE_URL)
        );
    }

    // Claim atomic
    $db->beginTransaction();
    try {
        // INSERT IGNORE trước trên claims để tránh race (uniq_free_user constraint)
        $claimIns = $db->prepare("INSERT IGNORE INTO free_key_claims (free_key_id, user_id) VALUES (?, ?)");
        $claimIns->execute([$fk['id'], $user['id']]);
        if ($claimIns->rowCount() === 0) {
            // Đã có claim trước đó - race condition
            $db->rollBack();
            $exist = $db->prepare("SELECT k.key_code FROM free_key_claims fkc LEFT JOIN `keys` k ON fkc.key_id=k.id WHERE fkc.free_key_id=? AND fkc.user_id=?");
            $exist->execute([$fk['id'], $user['id']]);
            $oldRow = $exist->fetch();
            claimPage(
                'Bạn đã nhận key free này rồi! 🎉',
                'Key đã được thêm vào tài khoản trước đó.',
                true,
                renderKeyBox($oldRow['key_code'] ?: $fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL)
            );
        }
        $claimId = $db->lastInsertId();

        $now    = date('Y-m-d H:i:s');
        $expire = date('Y-m-d H:i:s', strtotime('+' . (int)$fk['days'] . ' days'));
        $db->prepare("INSERT INTO `keys` (key_code, user_id, game_id, package_id, status, days, start_at, expire_at)
                      VALUES (?, ?, ?, ?, 'active', ?, ?, ?)")
           ->execute([$fk['key_code'], $user['id'], $fk['game_id'], $fk['package_id'], $fk['days'], $now, $expire]);
        $kid = (int)$db->lastInsertId();
        $db->prepare("UPDATE free_key_claims SET key_id = ? WHERE id = ?")->execute([$kid, $claimId]);
        $db->commit();

        claimPage(
            'Nhận key thành công! 🎉',
            'Key đã được thêm vào tài khoản của bạn.',
            true,
            renderKeyBox($fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL)
        );
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('[FREE_CLAIM] ' . $e->getMessage());
        claimPage('Không nhận được key', 'Có lỗi xảy ra. Vui lòng thử lại sau.');
    }

// =============================================
// CASE 2: Chưa có telegram_id - hiện form nhập
// =============================================
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_tg = $_POST['telegram_id'] ?? 0;
        if ($input_tg && ctype_digit((string)$input_tg)) {
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header('Location: ' . $url . '?t=' . urlencode($t) . '&telegram_id=' . urlencode($input_tg));
            exit;
        }
    }

    claimPage(
        '🎁 Nhận Key Free Hôm Nay',
        'Nhập <b>Telegram ID</b> của bạn để nhận key. Mỗi người nhận được 1 key free mỗi ngày!',
        false,
        '<div class="key-box" style="background:rgba(99,102,241,.06);border-color:rgba(99,102,241,.2)">'
        . '<div class="key-code" style="font-size:16px;color:#a78bfa">' . h($fk['key_code']) . '</div>'
        . '<div class="key-meta">🎮 <span>' . h($fk['game_name']) . '</span> · ' . (int)$fk['days'] . ' ngày</div></div>'
        . '<form method="POST">'
        . '<input type="text" name="telegram_id" placeholder="Nhập Telegram ID..." required pattern="[0-9]+" inputmode="numeric">'
        . '<button class="btn primary" type="submit">🔓 Nhận Key Ngay</button>'
        . '</form>'
        . '<p class="desc" style="margin-top:14px;font-size:11px;color:#64748b">💡 Mở Telegram → tìm <b>@userinfobot</b> → gửi tin nhắn để lấy ID</p>'
        . '<a class="btn outline" href="' . h(SITE_URL) . '">📱 Hoặc mở trong Mini App</a>'
    );
}
