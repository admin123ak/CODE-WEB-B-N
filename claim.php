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
        'already_title'    => 'Bạn đã nhận key này rồi',
        'already_msg'      => 'Key của bạn đã sẵn sàng sử dụng.',
        'today_title'      => 'Bạn đã nhận key hôm nay rồi',
        'today_msg'        => 'Key của bạn:',
        'success_title'    => 'Nhận key thành công',
        'success_msg'      => 'Key đã được thêm vào tài khoản của bạn.',
        'race_msg'         => 'Key đã được thêm vào tài khoản trước đó.',
        'error_title'      => 'Không nhận được key',
        'error_msg'        => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
        'form_title'       => 'Nhận Key Free',
        'form_msg'         => 'Nhập <b>Telegram ID</b> của bạn để nhận key. Mỗi người nhận được 1 key free mỗi ngày!',
        'form_placeholder' => 'Nhập Telegram ID...',
        'form_submit'      => 'Nhận Key Ngay',
        'form_hint'        => '💡 Mở Telegram → tìm <b>@userinfobot</b> → gửi tin nhắn để lấy ID',
        'open_miniapp'     => 'Mở trong Mini App',
        'go_miniapp'       => 'Vào Mini App',
        'copy_key'         => 'Copy Key',
        'copied'           => 'Đã copy!',
        'prompt_copy'      => 'Copy key:',
        'days'             => 'ngày',
        'feat_oneday'      => '1 / ngày',
        'feat_secure'      => 'Bảo mật',
        'lbl_yourkey'      => 'KEY CỦA BẠN',
        'lbl_game'         => 'GAME',
        'foot_secure'      => 'Bảo vệ bởi lớp bảo mật nâng cao',
    ],
    'en' => [
        'too_many'         => 'Too many requests',
        'too_many_msg'     => "You're going too fast. Please try again in a few minutes.",
        'invalid_link'     => 'Invalid link',
        'invalid_link_msg' => 'The claim token does not exist.',
        'expired_title'    => 'Free key expired',
        'expired_msg'      => 'This key is no longer available. Please come back later.',
        'already_title'    => 'You already claimed this key',
        'already_msg'      => 'Your key is ready to use.',
        'today_title'      => 'You already got a key today',
        'today_msg'        => 'Your key:',
        'success_title'    => 'Key claimed successfully',
        'success_msg'      => 'The key has been added to your account.',
        'race_msg'         => 'The key was added to your account earlier.',
        'error_title'      => 'Could not claim key',
        'error_msg'        => 'Something went wrong. Please try again later.',
        'form_title'       => "Get Today's Free Key",
        'form_msg'         => 'Enter your <b>Telegram ID</b> to claim. Each user gets 1 free key per day!',
        'form_placeholder' => 'Enter Telegram ID...',
        'form_submit'      => 'Claim Key Now',
        'form_hint'        => '💡 Open Telegram → find <b>@userinfobot</b> → send a message to get your ID',
        'open_miniapp'     => 'Open in Mini App',
        'go_miniapp'       => 'Open Mini App',
        'copy_key'         => 'Copy Key',
        'copied'           => 'Copied!',
        'prompt_copy'      => 'Copy key:',
        'days'             => 'days',
        'feat_oneday'      => '1 / day',
        'feat_secure'      => 'Secure',
        'lbl_yourkey'      => 'YOUR KEY',
        'lbl_game'         => 'GAME',
        'foot_secure'      => 'Protected by advanced security',
    ],
    'es' => [
        'too_many'         => 'Demasiadas solicitudes',
        'too_many_msg'     => 'Estás yendo muy rápido. Inténtalo de nuevo en unos minutos.',
        'invalid_link'     => 'Enlace inválido',
        'invalid_link_msg' => 'El token de reclamo no existe.',
        'expired_title'    => 'La clave gratuita ha expirado',
        'expired_msg'      => 'Esta clave ya no está disponible. Vuelve más tarde.',
        'already_title'    => 'Ya reclamaste esta clave',
        'already_msg'      => 'Tu clave está lista para usar.',
        'today_title'      => 'Ya obtuviste una clave hoy',
        'today_msg'        => 'Tu clave:',
        'success_title'    => 'Clave reclamada con éxito',
        'success_msg'      => 'La clave se ha añadido a tu cuenta.',
        'race_msg'         => 'La clave fue añadida a tu cuenta anteriormente.',
        'error_title'      => 'No se pudo reclamar la clave',
        'error_msg'        => 'Algo salió mal. Inténtalo de nuevo más tarde.',
        'form_title'       => 'Obtén la Clave Gratis de Hoy',
        'form_msg'         => 'Ingresa tu <b>Telegram ID</b> para reclamar. ¡Cada usuario obtiene 1 clave gratis por día!',
        'form_placeholder' => 'Ingresa Telegram ID...',
        'form_submit'      => 'Reclamar Ahora',
        'form_hint'        => '💡 Abre Telegram → busca <b>@userinfobot</b> → envía un mensaje para obtener tu ID',
        'open_miniapp'     => 'Abrir en Mini App',
        'go_miniapp'       => 'Abrir Mini App',
        'copy_key'         => 'Copiar Clave',
        'copied'           => '¡Copiado!',
        'prompt_copy'      => 'Copia la clave:',
        'days'             => 'días',
        'feat_oneday'      => '1 / día',
        'feat_secure'      => 'Seguro',
        'lbl_yourkey'      => 'TU CLAVE',
        'lbl_game'         => 'JUEGO',
        'foot_secure'      => 'Protegido por seguridad avanzada',
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
$GLOBALS['L'] = $L; // dùng trong helper claimPage() để render footer i18n.

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

// SVG icon set (indigo line icons, 28px). Inline để tránh thêm round-trip.
function claimIcon($name) {
    $stroke = 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"';
    $icons = [
        'diamond' => '<svg viewBox="0 0 24 24" width="34" height="34" fill="#fff"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#fff" stroke-width="1.5" stroke-linejoin="round" fill="none"/></svg>',
        'clock'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'game'    => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><rect x="2" y="8" width="20" height="11" rx="3"/><path d="M7 13h3M8.5 11.5v3M15 12h.01M18 14h.01"/></svg>',
        'phone'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></svg>',
        'shield'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg>',
        'lock'    => '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" ' . $stroke . '><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 1 1 8 0v3"/></svg>',
        'check'   => '<svg viewBox="0 0 24 24" width="34" height="34" fill="none" ' . $stroke . '><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>',
        'warn'    => '<svg viewBox="0 0 24 24" width="34" height="34" fill="none" ' . $stroke . '><path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18.5v.01"/></svg>',
        'copy'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>',
        'arrow'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
        'chev'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><path d="M6 9l6 6 6-6"/></svg>',
    ];
    return $icons[$name] ?? '';
}

// Render 4 pill features 2×2 (icon + label) — giống vipteam.store layout.
function claimPills(array $items) {
    $html = '<div class="pills">';
    foreach ($items as $it) {
        $html .= '<div class="pill"><span class="pill-ico">' . $it['icon'] . '</span><span class="pill-text">' . $it['text'] . '</span></div>';
    }
    return $html . '</div>';
}

function claimPage($title, $msg, $ok = false, $extra = '', $pills = '', $features = '') {
    // $title, $msg đã được escape bởi caller (hoặc literal từ $L); $extra/$features là HTML build kiểm soát.
    $iconBlock = $ok
        ? '<div class="header-ico header-ico--ok">' . claimIcon('check') . '</div>'
        : '<div class="header-ico">' . claimIcon('diamond') . '</div>';
    echo '<!doctype html><html lang="' . h($GLOBALS['LANG'] ?? 'vi') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HCLOU Claim</title><style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:#06080f;color:#e6edf3;font-family:"Inter",-apple-system,"SF Pro Display","Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;padding:20px;-webkit-font-smoothing:antialiased}
.card{max-width:420px;width:100%;background:linear-gradient(160deg,rgba(12,17,32,.92),rgba(19,27,46,.88));border:1px solid rgba(239,68,68,.14);border-radius:24px;padding:40px 32px;display:flex;flex-direction:column;align-items:center;gap:22px;box-shadow:0 28px 70px rgba(0,0,0,.5),0 0 28px rgba(239,68,68,.05);backdrop-filter:blur(16px)}
.header-ico{width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#dc2626,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 24px rgba(220,38,38,.35)}
.header-ico--ok{background:linear-gradient(135deg,#34d399,#10b981);box-shadow:0 8px 24px rgba(16,185,129,.35)}
.title{font-size:26px;font-weight:800;color:#fff;letter-spacing:-.3px;line-height:1.2;margin:0 0 6px;text-align:center}
.msg{font-size:14px;color:#7a8ba8;line-height:1.55;margin:0;text-align:center}
.pills{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}
.pill{background:rgba(239,68,68,.05);border:1px solid rgba(248,113,113,.14);border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px;color:#e6edf3;font-size:14px;font-weight:500}
.pill-ico{color:#fdba74;display:inline-flex;opacity:.85;flex-shrink:0}
.label{display:block;font-size:11px;font-weight:600;letter-spacing:.1em;color:#7a8ba8;margin-bottom:8px;text-transform:uppercase;text-align:left;width:100%}
.info-box{position:relative;width:100%;background:rgba(239,68,68,.04);border:1px solid rgba(248,113,113,.14);border-radius:12px;padding:14px 40px 14px 16px;color:#e6edf3;font-size:15px;font-weight:500;display:flex;align-items:center;justify-content:space-between;gap:10px;transition:border-color .2s}
.info-box .info-right{display:inline-flex;align-items:center;gap:10px}
.info-box .meta{color:#7a8ba8;font-weight:500;font-size:12.5px}
.info-box .chev{position:absolute;right:14px;top:50%;width:10px;height:10px;border-right:2px solid #7a8ba8;border-bottom:2px solid #7a8ba8;transform:translateY(-65%) rotate(45deg);pointer-events:none}
.info-box .chev svg{display:none}
.key-code{font-size:20px;font-weight:800;color:#fdba74;font-family:"SF Mono","JetBrains Mono",ui-monospace,monospace;letter-spacing:1.5px;word-break:break-all;text-align:center}
.key-code--ok{color:#34d399}
input[type=text]{width:100%;padding:14px 16px;border-radius:12px;border:1px solid rgba(248,113,113,.14);background:rgba(239,68,68,.04);color:#e6edf3;font-size:15px;text-align:center;outline:0;font-family:inherit;font-weight:500;transition:border-color .2s,box-shadow .2s}
input[type=text]:focus{border-color:rgba(239,68,68,.5);box-shadow:0 0 0 3px rgba(239,68,68,.18)}
.btn{display:flex;align-items:center;justify-content:center;gap:10px;padding:17px;border-radius:14px;border:none;font-weight:700;font-size:16px;cursor:pointer;width:100%;transition:opacity .2s,transform .15s,box-shadow .2s;text-decoration:none;font-family:"Inter",inherit}
.btn:active{transform:scale(.98)}
.btn.primary{background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;box-shadow:0 8px 24px rgba(220,38,38,.3)}
.btn.primary:hover{opacity:.92;transform:translateY(-1px)}
.btn.ghost{background:transparent;border:1px solid rgba(248,113,113,.30);color:#fdba74;font-size:14px;padding:14px}
.btn.ghost:hover{background:rgba(239,68,68,.06)}
.hint{font-size:12px;color:#7a8ba8;line-height:1.55;text-align:center;margin:0;opacity:.7}
.foot{display:flex;align-items:center;justify-content:center;gap:6px;color:#7a8ba8;font-size:12px;opacity:.65}
.foot svg{width:13px;height:13px;opacity:.85}
@media(max-width:380px){.title{font-size:23px}.pill{font-size:13px;padding:11px 13px}}
</style></head><body><div class="card">' . $pills . $iconBlock . '<h1 class="title">' . $title . '</h1><p class="msg">' . $msg . '</p>' . $features . $extra . '<div class="foot">' . claimIcon('lock') . '<span>' . h($GLOBALS['L']['foot_secure'] ?? 'Protected by advanced security') . '</span></div></div></body></html>';
    exit;
}

// Block 4 pill features chuẩn — dùng cho mọi state thấy fk hợp lệ.
function buildFeatures(array $fk, array $L) {
    return claimPills([
        ['icon' => claimIcon('clock'),  'text' => (int)$fk['days'] . ' ' . h($L['days'])],
        ['icon' => claimIcon('game'),   'text' => h(mb_strimwidth($fk['game_name'], 0, 12, '…', 'UTF-8'))],
        ['icon' => claimIcon('phone'),  'text' => h($L['feat_oneday'])],
        ['icon' => claimIcon('shield'), 'text' => h($L['feat_secure'])],
    ]);
}

// Helper: render key đã có (key-code box + copy/mở app).
function renderKeyBox($keyCode, $gameName, $days, $siteUrl, array $L) {
    return '<div class="label">' . h($L['lbl_yourkey']) . '</div>'
         . '<div class="info-box" style="flex-direction:column;gap:8px;text-align:center"><div class="key-code key-code--ok">' . h($keyCode) . '</div>'
         . '<div class="meta">🎮 ' . h($gameName) . ' · ' . (int)$days . ' ' . h($L['days']) . '</div></div>'
         . '<button class="btn primary" onclick="copyKey(' . json_encode($keyCode) . ')">' . claimIcon('copy') . ' ' . h($L['copy_key']) . '</button>'
         . '<a class="btn ghost" href="' . h($siteUrl) . '">' . h($L['go_miniapp']) . '</a>'
         . '<script>function copyKey(t){navigator.clipboard.writeText(t).then(function(){alert(' . json_encode($L['copied']) . ')}).catch(function(){prompt(' . json_encode($L['prompt_copy']) . ',t)})}</script>';
}

$pills = langPills($LANG, $t, (string)($_GET['telegram_id'] ?? ''));

if (!$fk) claimPage(h($L['invalid_link']), h($L['invalid_link_msg']), false, '', $pills);

if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) {
    claimPage(h($L['expired_title']), h($L['expired_msg']), false, '', $pills);
}

// Features block — render 1 lần, dùng cho mọi state còn lại có fk hợp lệ.
$features = buildFeatures($fk, $L);

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
            $pills,
            $features
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
            h($L['today_msg']),
            true,
            renderKeyBox($old['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills,
            $features
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
                $pills,
                $features
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
            $pills,
            $features
        );
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log('[FREE_CLAIM] ' . $e->getMessage());
        claimPage(h($L['error_title']), h($L['error_msg']), false, '', $pills, $features);
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
        '<div class="label">' . h($L['lbl_game']) . '</div>'
        . '<div class="info-box">'
        . '<span>' . h($fk['game_name']) . '</span>'
        . '<span class="info-right"><span class="meta">' . (int)$fk['days'] . ' ' . h($L['days']) . '</span><span class="chev">' . claimIcon('chev') . '</span></span>'
        . '</div>'
        . '<form method="POST">'
        . '<input type="hidden" name="lang" value="' . h($LANG) . '">'
        . '<input type="text" name="telegram_id" placeholder="' . h($L['form_placeholder']) . '" required pattern="[0-9]+" inputmode="numeric">'
        . '<button class="btn primary" type="submit">' . claimIcon('arrow') . ' ' . h($L['form_submit']) . '</button>'
        . '</form>'
        . '<p class="hint">' . $L['form_hint'] . '</p>'
        . '<a class="btn ghost" href="' . h(SITE_URL) . '">' . h($L['open_miniapp']) . '</a>',
        $pills,
        $features
    );
}
