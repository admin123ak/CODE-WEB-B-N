<?php
require_once __DIR__ . '/config.php';

// =============================================
// I18N: server-side, 3 ngôn ngữ vi/en/es
// =============================================
// Detect order: ?lang=xx (whitelist) → cookie claim_lang → Accept-Language → default vi.
// Form POST sẽ preserve lang qua hidden input + redirect URL.
$LANG_ALL = [
    'vi' => [
        'too_many'         => 'Quá nhiều yêu cầu',
        'too_many_msg'     => 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.',
        'invalid_link'     => 'Link không hợp lệ',
        'invalid_link_msg' => 'Token claim không tồn tại.',
        'expired_title'    => 'Key free đã hết hạn',
        'expired_msg'      => 'Key này không còn khả dụng. Vui lòng quay lại sau.',
        'already_title'    => 'Bạn đã nhận key free này rồi! 🎉',
        'already_msg'      => 'Key của bạn đã sẵn sàng sử dụng.',
        'today_title'      => 'Bạn đã nhận key hôm nay rồi! 🎉',
        'today_msg'        => 'Key của bạn:',
        'success_title'    => 'Nhận key thành công! 🎉',
        'success_msg'      => 'Key đã được thêm vào tài khoản của bạn.',
        'race_msg'         => 'Key đã được thêm vào tài khoản trước đó.',
        'error_title'      => 'Không nhận được key',
        'error_msg'        => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
        'form_title'       => '🎁 Nhận Key Free Hôm Nay',
        'form_msg'         => 'Nhập <b>Telegram ID</b> của bạn để nhận key. Mỗi người nhận được 1 key free mỗi ngày!',
        'form_placeholder' => 'Nhập Telegram ID...',
        'form_submit'      => '🔓 Nhận Key Ngay',
        'form_hint'        => '💡 Mở Telegram → tìm <b>@userinfobot</b> → gửi tin nhắn để lấy ID',
        'open_miniapp'     => '📱 Hoặc mở trong Mini App',
        'go_miniapp'       => '🔑 Vào Mini App',
        'copy_key'         => '📋 Copy Key',
        'copied'           => 'Đã copy!',
        'prompt_copy'      => 'Copy key:',
        'days'             => 'ngày',
    ],
    'en' => [
        'too_many'         => 'Too many requests',
        'too_many_msg'     => "You're going too fast. Please try again in a few minutes.",
        'invalid_link'     => 'Invalid link',
        'invalid_link_msg' => 'The claim token does not exist.',
        'expired_title'    => 'Free key expired',
        'expired_msg'      => 'This key is no longer available. Please come back later.',
        'already_title'    => 'You already claimed this free key! 🎉',
        'already_msg'      => 'Your key is ready to use.',
        'today_title'      => 'You already got a key today! 🎉',
        'today_msg'        => 'Your key:',
        'success_title'    => 'Key claimed successfully! 🎉',
        'success_msg'      => 'The key has been added to your account.',
        'race_msg'         => 'The key was added to your account earlier.',
        'error_title'      => 'Could not claim key',
        'error_msg'        => 'Something went wrong. Please try again later.',
        'form_title'       => "🎁 Get Today's Free Key",
        'form_msg'         => 'Enter your <b>Telegram ID</b> to claim. Each user gets 1 free key per day!',
        'form_placeholder' => 'Enter Telegram ID...',
        'form_submit'      => '🔓 Claim Key Now',
        'form_hint'        => '💡 Open Telegram → find <b>@userinfobot</b> → send a message to get your ID',
        'open_miniapp'     => '📱 Or open in Mini App',
        'go_miniapp'       => '🔑 Open Mini App',
        'copy_key'         => '📋 Copy Key',
        'copied'           => 'Copied!',
        'prompt_copy'      => 'Copy key:',
        'days'             => 'days',
    ],
    'es' => [
        'too_many'         => 'Demasiadas solicitudes',
        'too_many_msg'     => 'Estás yendo muy rápido. Inténtalo de nuevo en unos minutos.',
        'invalid_link'     => 'Enlace inválido',
        'invalid_link_msg' => 'El token de reclamo no existe.',
        'expired_title'    => 'La clave gratuita ha expirado',
        'expired_msg'      => 'Esta clave ya no está disponible. Vuelve más tarde.',
        'already_title'    => '¡Ya reclamaste esta clave gratuita! 🎉',
        'already_msg'      => 'Tu clave está lista para usar.',
        'today_title'      => '¡Ya obtuviste una clave hoy! 🎉',
        'today_msg'        => 'Tu clave:',
        'success_title'    => '¡Clave reclamada con éxito! 🎉',
        'success_msg'      => 'La clave se ha añadido a tu cuenta.',
        'race_msg'         => 'La clave fue añadida a tu cuenta anteriormente.',
        'error_title'      => 'No se pudo reclamar la clave',
        'error_msg'        => 'Algo salió mal. Inténtalo de nuevo más tarde.',
        'form_title'       => '🎁 Obtén la Clave Gratis de Hoy',
        'form_msg'         => 'Ingresa tu <b>Telegram ID</b> para reclamar. ¡Cada usuario obtiene 1 clave gratis por día!',
        'form_placeholder' => 'Ingresa Telegram ID...',
        'form_submit'      => '🔓 Reclamar Ahora',
        'form_hint'        => '💡 Abre Telegram → busca <b>@userinfobot</b> → envía un mensaje para obtener tu ID',
        'open_miniapp'     => '📱 O abrir en Mini App',
        'go_miniapp'       => '🔑 Abrir Mini App',
        'copy_key'         => '📋 Copiar Clave',
        'copied'           => '¡Copiado!',
        'prompt_copy'      => 'Copia la clave:',
        'days'             => 'días',
    ],
];

$_allowedLangs = ['vi', 'en', 'es'];
$LANG = 'vi';
if (!empty($_GET['lang']) && in_array($_GET['lang'], $_allowedLangs, true)) {
    $LANG = $_GET['lang'];
    @setcookie('claim_lang', $LANG, time() + 86400 * 365, '/');
} elseif (!empty($_COOKIE['claim_lang']) && in_array($_COOKIE['claim_lang'], $_allowedLangs, true)) {
    $LANG = $_COOKIE['claim_lang'];
} else {
    $al = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    if (strpos($al, 'es') === 0 || strpos($al, ',es') !== false) $LANG = 'es';
    elseif (strpos($al, 'en') === 0 || strpos($al, ',en') !== false) $LANG = 'en';
}
$L = $LANG_ALL[$LANG];

// =============================================
// RATE LIMIT (IP-based, HTML response — không dùng được rateLimit() vì hàm đó trả JSON)
// =============================================
// Bug #11: claim.php tự tạo row user mới cho bất kỳ telegram_id POST nào (line 70-76).
// Attacker có thể spam POST telegram_id ngẫu nhiên → spam bảng `users`. Giới hạn 20 lần / 5 phút / IP.
(function () use ($L) {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = APP_ROOT . '/data/ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/claim_' . hash('sha256', $ip) . '.json';
    $now  = time();
    $win  = 300;
    $max  = 20;
    $data = ['start' => $now, 'count' => 0];
    if (is_file($file)) {
        $old = json_decode((string)@file_get_contents($file), true);
        if (is_array($old) && !empty($old['start']) && ($now - (int)$old['start']) < $win) $data = $old;
    }
    if (($now - (int)$data['start']) >= $win) $data = ['start' => $now, 'count' => 0];
    $data['count'] = (int)$data['count'] + 1;
    @file_put_contents($file, json_encode($data), LOCK_EX);
    if ($data['count'] > $max) {
        http_response_code(429);
        header('Retry-After: ' . max(1, $win - ($now - (int)$data['start'])));
        echo '<!doctype html><meta charset="utf-8"><title>' . h($L['too_many']) . '</title>'
           . '<body style="font-family:-apple-system,sans-serif;background:#070b14;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;text-align:center">'
           . '<div><h2>⏳ ' . h($L['too_many']) . '</h2><p>' . h($L['too_many_msg']) . '</p></div>';
        exit;
    }
})();

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

// Build language pill HTML. Bấm cờ = reload page với ?lang=xx (preserve t & telegram_id).
function langPills($current, $t, $tg = '') {
    $flags = ['vi' => '&#127483;&#127475;', 'en' => '&#127468;&#127463;', 'es' => '&#127466;&#127480;'];
    $titles = ['vi' => 'Tiếng Việt', 'en' => 'English', 'es' => 'Español'];
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $html = '<div style="display:flex;justify-content:center;gap:6px;margin-bottom:14px">';
    foreach ($flags as $code => $flag) {
        $params = [];
        if ($t)  $params['t']  = $t;
        $params['lang'] = $code;
        if ($tg) $params['telegram_id'] = $tg;
        $url = $base . '?' . http_build_query($params);
        $active = $code === $current;
        $style = 'text-decoration:none;width:38px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:15px;line-height:1;transition:all .2s;'
               . ($active
                   ? 'background:linear-gradient(135deg,#dc2626,#ef4444);box-shadow:0 4px 12px rgba(239,68,68,.35);border:1px solid transparent;'
                   : 'background:rgba(15,23,42,.7);border:1px solid rgba(239,68,68,.22);');
        $html .= '<a href="' . h($url) . '" title="' . h($titles[$code]) . '" style="' . $style . '">' . $flag . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function claimPage($title, $msg, $ok = false, $extra = '', $pills = '') {
    $ico = $ok ? '✅' : '⚠️';
    // $title, $msg đã được escape bởi caller (hoặc là literal từ $L); $extra là HTML khối được build kiểm soát.
    echo '<!doctype html><html lang="' . h($GLOBALS['LANG'] ?? 'vi') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HCLOU Claim</title><style>
body{margin:0;min-height:100vh;background:radial-gradient(circle at 20% 10%,rgba(239,68,68,.18),transparent 35%),radial-gradient(circle at 80% 90%,rgba(249,115,22,.10),transparent 40%),#070b14;color:#e6edf3;font-family:-apple-system,"SF Pro Display",Segoe UI,sans-serif;display:flex;align-items:center;justify-content:center;padding:20px}
.card{max-width:420px;width:100%;background:linear-gradient(160deg,rgba(17,24,39,.95),rgba(10,14,26,.98));border:1px solid rgba(239,68,68,.22);border-radius:28px;padding:28px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.55),0 0 0 1px rgba(239,68,68,.05) inset;backdrop-filter:blur(20px)}
.ico{font-size:52px;margin-bottom:10px;animation:bounceIn .5s ease}
@keyframes bounceIn{0%{transform:scale(0);opacity:0}60%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
.title{font-size:22px;font-weight:900;margin:8px 0;background:linear-gradient(135deg,#fff 0%,#fca5a5 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:-.3px}
.msg{font-size:14px;color:#94a3b8;line-height:1.6;margin-bottom:14px}
.key-box{background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);border-radius:16px;padding:18px;margin:16px 0}
.key-code{font-size:22px;font-weight:900;color:#34d399;font-family:"SF Mono","JetBrains Mono",monospace;letter-spacing:2px;word-break:break-all}
.key-meta{font-size:12px;color:#64748b;margin-top:8px}
.key-meta span{color:#94a3b8;font-weight:700}
.btn{display:block;margin-top:14px;padding:14px;border-radius:16px;border:none;font-weight:900;font-size:14px;cursor:pointer;width:100%;transition:all .2s;text-decoration:none;letter-spacing:.2px}
.btn:active{transform:scale(.97)}
.btn.primary{background:linear-gradient(135deg,#dc2626,#ef4444 55%,#f97316);color:#fff;box-shadow:0 12px 32px rgba(220,38,38,.45),inset 0 1px 0 rgba(255,255,255,.18)}
.btn.copy{background:linear-gradient(135deg,#ef4444,#f97316);color:#fff;box-shadow:0 10px 28px rgba(239,68,68,.35)}
.btn.outline{background:transparent;border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.btn.outline:active{background:rgba(239,68,68,.08)}
.desc{font-size:13px;color:#94a3b8;line-height:1.5;margin:10px 0}
input[type=text]{width:100%;padding:14px 16px;border-radius:14px;border:1px solid rgba(239,68,68,.22);background:rgba(15,23,42,.85);color:#e6edf3;font-size:15px;text-align:center;outline:0;margin:14px 0;font-family:inherit;font-weight:700;letter-spacing:.5px;transition:border-color .2s,box-shadow .2s}
input[type=text]:focus{border-color:#ef4444;box-shadow:0 0 0 4px rgba(239,68,68,.15)}
</style></head><body><div class="card">' . $pills . '<div class="ico">' . $ico . '</div><h2 class="title">' . $title . '</h2><p class="msg">' . $msg . '</p>' . $extra . '</div></body></html>';
    exit;
}

// Helper: build HTML cho 1 key đã có
function renderKeyBox($keyCode, $gameName, $days, $siteUrl, array $L) {
    return '<div class="key-box"><div class="key-code">' . h($keyCode) . '</div>'
         . '<div class="key-meta">🎮 ' . h($gameName) . ' · <span>' . (int)$days . ' ' . h($L['days']) . '</span></div></div>'
         . '<button class="btn copy" onclick="copyKey(' . json_encode($keyCode) . ')">' . h($L['copy_key']) . '</button>'
         . '<a class="btn outline" href="' . h($siteUrl) . '">' . h($L['go_miniapp']) . '</a>'
         . '<script>function copyKey(t){navigator.clipboard.writeText(t).then(function(){alert(' . json_encode($L['copied']) . ')}).catch(function(){prompt(' . json_encode($L['prompt_copy']) . ',t)})}</script>';
}

$pills = langPills($LANG, $t, (string)($_GET['telegram_id'] ?? ''));

if (!$fk) claimPage(h($L['invalid_link']), h($L['invalid_link_msg']), false, '', $pills);

if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) {
    claimPage(h($L['expired_title']), h($L['expired_msg']), false, '', $pills);
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
            h($L['already_title']),
            h($L['already_msg']),
            true,
            renderKeyBox($exist['key_code'] ?: $fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills
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
            h($L['today_title']),
            h($L['today_msg']) . ' <b style="color:#34d399">' . h($old['key_code']) . '</b>',
            true,
            renderKeyBox($old['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills
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
                h($L['already_title']),
                h($L['race_msg']),
                true,
                renderKeyBox($oldRow['key_code'] ?: $fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
                $pills
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
            h($L['success_title']),
            h($L['success_msg']),
            true,
            renderKeyBox($fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills
        );
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('[FREE_CLAIM] ' . $e->getMessage());
        claimPage(h($L['error_title']), h($L['error_msg']), false, '', $pills);
    }

// =============================================
// CASE 2: Chưa có telegram_id - hiện form nhập
// =============================================
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_tg = $_POST['telegram_id'] ?? 0;
        if ($input_tg && ctype_digit((string)$input_tg)) {
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header('Location: ' . $url . '?t=' . urlencode($t) . '&telegram_id=' . urlencode($input_tg) . '&lang=' . urlencode($LANG));
            exit;
        }
    }

    claimPage(
        h($L['form_title']),
        $L['form_msg'], // chứa <b>...</b> hợp lệ — không escape
        false,
        '<div class="key-box" style="background:rgba(239,68,68,.06);border-color:rgba(239,68,68,.22)">'
        . '<div class="key-code" style="font-size:16px;color:#fca5a5">' . h($fk['key_code']) . '</div>'
        . '<div class="key-meta">🎮 <span>' . h($fk['game_name']) . '</span> · ' . (int)$fk['days'] . ' ' . h($L['days']) . '</div></div>'
        . '<form method="POST">'
        . '<input type="hidden" name="lang" value="' . h($LANG) . '">'
        . '<input type="text" name="telegram_id" placeholder="' . h($L['form_placeholder']) . '" required pattern="[0-9]+" inputmode="numeric">'
        . '<button class="btn primary" type="submit">' . h($L['form_submit']) . '</button>'
        . '</form>'
        . '<p class="desc" style="margin-top:14px;font-size:11px;color:#64748b">' . $L['form_hint'] . '</p>'
        . '<a class="btn outline" href="' . h(SITE_URL) . '">' . h($L['open_miniapp']) . '</a>',
        $pills
    );
}
