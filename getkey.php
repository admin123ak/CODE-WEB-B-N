<?php
require_once __DIR__ . '/config.php';

// =============================================
// GETKEY.PH — Trang web claim key (không cần Telegram ID)
// =============================================
// Hai luồng dùng chung file này:
//  - Bot/Mini App: gắn ?telegram_id=<id> → claim qua free_key_claims (như claim.php cũ).
//  - Web khách bất kỳ: không có telegram_id → claim qua free_key_web_claims, dedupe theo IP.
//    Quy tắc: 1 IP / 1 free key / ngày (bất kể game).

// =============================================
// I18N (vi/en/es) — giữ song song với claim.php
// =============================================
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
        'today_msg'        => 'Mỗi IP chỉ nhận 1 key miễn phí/ngày. Key bạn đã nhận:',
        'success_title'    => 'Nhận key thành công',
        'success_msg'      => 'Key đã sẵn sàng. Copy và dán vào game để dùng.',
        'race_msg'         => 'Key đã được cấp cho bạn trước đó.',
        'error_title'      => 'Không nhận được key',
        'error_msg'        => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
        'form_title'       => 'Nhận Key Free',
        'form_msg'         => 'Bấm nút bên dưới để nhận <b>key miễn phí</b>. Mỗi IP nhận 1 key/ngày — không cần đăng ký, không cần Telegram.',
        'form_submit'      => 'Nhận Key Ngay',
        'form_hint'        => '💡 Key được cấp tức thì, dùng ngay trong game. Hôm sau vào lại để nhận key mới.',
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
        'today_msg'        => 'Each IP gets one free key per day. Your key:',
        'success_title'    => 'Key claimed successfully',
        'success_msg'      => 'Your key is ready. Copy and paste it into the game.',
        'race_msg'         => 'The key was already issued to you earlier.',
        'error_title'      => 'Could not claim key',
        'error_msg'        => 'Something went wrong. Please try again later.',
        'form_title'       => 'Get Your Free Key',
        'form_msg'         => 'Tap the button below to receive a <b>free key</b>. One key per IP per day — no signup, no Telegram needed.',
        'form_submit'      => 'Claim Key Now',
        'form_hint'        => '💡 Key is issued instantly. Come back tomorrow for a new one.',
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
        'expired_title'    => 'Clave gratis expirada',
        'expired_msg'      => 'Esta clave ya no está disponible.',
        'already_title'    => 'Ya reclamaste esta clave',
        'already_msg'      => 'Tu clave está lista para usar.',
        'today_title'      => 'Ya obtuviste una clave hoy',
        'today_msg'        => 'Cada IP recibe 1 clave gratis por día. Tu clave:',
        'success_title'    => 'Clave reclamada con éxito',
        'success_msg'      => 'Tu clave está lista. Cópiala y pégala en el juego.',
        'race_msg'         => 'La clave ya te fue entregada antes.',
        'error_title'      => 'No se pudo reclamar',
        'error_msg'        => 'Algo salió mal. Inténtalo más tarde.',
        'form_title'       => 'Obtén tu Clave Gratis',
        'form_msg'         => 'Pulsa el botón para recibir una <b>clave gratis</b>. Una por IP al día — sin registro, sin Telegram.',
        'form_submit'      => 'Reclamar Ahora',
        'form_hint'        => '💡 La clave se entrega al instante. Vuelve mañana para otra.',
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

// Detect ngôn ngữ: ?lang → cookie → Accept-Language → vi
$LANG = 'vi';
$qLang = strtolower((string)($_GET['lang'] ?? ''));
if (isset($LANG_ALL[$qLang])) {
    $LANG = $qLang;
    setcookie('claim_lang', $LANG, time() + 86400 * 365, '/');
} elseif (!empty($_COOKIE['claim_lang']) && isset($LANG_ALL[$_COOKIE['claim_lang']])) {
    $LANG = $_COOKIE['claim_lang'];
} else {
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    foreach (['vi', 'en', 'es'] as $code) {
        if (strpos($accept, $code) !== false) { $LANG = $code; break; }
    }
}
$L = $LANG_ALL[$LANG];
$GLOBALS['LANG'] = $LANG;
$GLOBALS['L']    = $L;

// =============================================
// Rate limit 20/5min/IP — chặn spam POST.
// =============================================
(function () use ($L) {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = APP_ROOT . '/data/ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/getkey_' . hash('sha256', $ip) . '.json';
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
           . '<body style="font-family:-apple-system,sans-serif;background:#06080f;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;text-align:center">'
           . '<div><h2>⏳ ' . h($L['too_many']) . '</h2><p>' . h($L['too_many_msg']) . '</p></div>';
        exit;
    }
})();

// =============================================
// Helpers
// =============================================
function getClientIp() {
    // Cloudflare > X-Forwarded-For > REMOTE_ADDR
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $first = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        return trim($first);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function ipHash($ip) {
    // Hash kèm SITE_URL làm salt — ko reverse được ra IP gốc qua DB dump.
    return hash('sha256', $ip . '|' . (defined('SITE_URL') ? SITE_URL : ''));
}

function langPills($current, $t) {
    $flags = ['vi' => '&#127483;&#127475;', 'en' => '&#127468;&#127463;', 'es' => '&#127466;&#127480;'];
    $titles = ['vi' => 'Tiếng Việt', 'en' => 'English', 'es' => 'Español'];
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $html = '<div style="display:flex;justify-content:center;gap:6px;margin-bottom:14px">';
    foreach ($flags as $code => $flag) {
        $params = [];
        if ($t)  $params['t']  = $t;
        $params['lang'] = $code;
        $url = $base . '?' . http_build_query($params);
        $active = $code === $current;
        $style = 'text-decoration:none;width:38px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:15px;line-height:1;transition:all .2s;'
               . ($active
                   ? 'background:linear-gradient(135deg,#7c6fe0,#5b52c4);box-shadow:0 4px 12px rgba(124,111,224,.4);border:1px solid transparent;'
                   : 'background:rgba(15,23,42,.7);border:1px solid rgba(124,111,224,.22);');
        $html .= '<a href="' . h($url) . '" title="' . h($titles[$code]) . '" style="' . $style . '">' . $flag . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function gkIcon($name) {
    $stroke = 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"';
    $icons = [
        'diamond' => '<svg viewBox="0 0 24 24" width="34" height="34" fill="#fff"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#fff" stroke-width="1.5" stroke-linejoin="round" fill="none"/></svg>',
        'clock'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'game'    => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><rect x="2" y="8" width="20" height="11" rx="3"/><path d="M7 13h3M8.5 11.5v3M15 12h.01M18 14h.01"/></svg>',
        'phone'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/></svg>',
        'shield'  => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" ' . $stroke . '><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg>',
        'lock'    => '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" ' . $stroke . '><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 1 1 8 0v3"/></svg>',
        'check'   => '<svg viewBox="0 0 24 24" width="34" height="34" fill="none" ' . $stroke . '><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>',
        'copy'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>',
        'arrow'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
        'chev'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" ' . $stroke . '><path d="M6 9l6 6 6-6"/></svg>',
    ];
    return $icons[$name] ?? '';
}

function gkPills(array $items) {
    $html = '<div class="pills">';
    foreach ($items as $it) {
        $html .= '<div class="pill"><span class="pill-ico">' . $it['icon'] . '</span><span class="pill-text">' . $it['text'] . '</span></div>';
    }
    return $html . '</div>';
}

function gkPage($title, $msg, $ok = false, $extra = '', $pills = '', $features = '') {
    $iconBlock = $ok
        ? '<div class="header-ico header-ico--ok">' . gkIcon('check') . '</div>'
        : '<div class="header-ico">' . gkIcon('diamond') . '</div>';
    echo '<!doctype html><html lang="' . h($GLOBALS['LANG'] ?? 'vi') . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>HCLOU GetKey</title><meta name="description" content="Nhận key game miễn phí hằng ngày — không cần đăng ký, không cần Telegram."><style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:#06080f;color:#e6edf3;font-family:"Inter",-apple-system,"SF Pro Display","Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;padding:20px;-webkit-font-smoothing:antialiased}
.card{max-width:420px;width:100%;background:transparent;border:none;border-radius:0;padding:30px 18px 24px;display:flex;flex-direction:column;align-items:center;gap:20px;box-shadow:none}
.header-ico{width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#7c6fe0,#5b52c4 60%,#4338ca);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 0 28px rgba(124,111,224,.4),inset 0 1px 0 rgba(255,255,255,.18)}
.header-ico--ok{background:linear-gradient(135deg,#34d399,#10b981);box-shadow:0 0 28px rgba(52,211,153,.32),inset 0 1px 0 rgba(255,255,255,.18)}
.title{font-size:24px;font-weight:900;letter-spacing:-.3px;line-height:1.2;margin:0 0 4px;text-align:center;background:linear-gradient(135deg,#fff 0%,#c4b5fd 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.msg{font-size:13px;color:#7a8ba8;line-height:1.55;margin:0;text-align:center;font-weight:500}
.pills{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}
.pill{background:rgba(19,27,46,.78);border:1px solid rgba(124,111,224,.18);border-radius:14px;padding:13px 14px;display:flex;align-items:center;gap:10px;color:#f0f4ff;font-size:13px;font-weight:600;letter-spacing:.2px;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.pill-ico{color:#c4b5fd;display:inline-flex;flex-shrink:0}
.label{display:block;font-size:11px;font-weight:700;letter-spacing:.5px;color:#7a8ba8;margin-bottom:6px;text-transform:uppercase;text-align:left;width:100%}
.info-box{position:relative;width:100%;background:rgba(19,27,46,.78);border:1px solid rgba(124,111,224,.18);border-radius:14px;padding:14px 40px 14px 16px;color:#f0f4ff;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:space-between;gap:10px;box-shadow:inset 0 1px 0 rgba(255,255,255,.03);transition:border-color .2s}
.info-box .info-right{display:inline-flex;align-items:center;gap:10px}
.info-box .meta{color:#7a8ba8;font-weight:600;font-size:12px}
.info-box .chev{position:absolute;right:14px;top:50%;width:9px;height:9px;border-right:2px solid #7a8ba8;border-bottom:2px solid #7a8ba8;transform:translateY(-65%) rotate(45deg);pointer-events:none}
.key-code{font-size:20px;font-weight:900;color:#c4b5fd;font-family:"SF Mono","JetBrains Mono",ui-monospace,monospace;letter-spacing:1.5px;word-break:break-all;text-align:center;padding:11px;background:rgba(0,0,0,.25);border-radius:10px;width:100%}
.key-code--ok{color:#34d399}
.btn{display:flex;align-items:center;justify-content:center;gap:10px;height:48px;padding:0 18px;border-radius:24px;border:none;font-weight:800;font-size:14px;cursor:pointer;width:100%;transition:transform .2s,box-shadow .2s,filter .2s;text-decoration:none;font-family:"Inter",sans-serif;letter-spacing:.2px}
.btn:active{transform:scale(.97)}
.btn.primary{background:linear-gradient(135deg,#7c6fe0,#5b52c4 60%,#4338ca);color:#fff;box-shadow:0 12px 34px rgba(91,82,196,.5),inset 0 1px 0 rgba(255,255,255,.18);position:relative;overflow:hidden}
.btn.primary:before{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent,rgba(255,255,255,.22),transparent);transform:translateX(-120%);animation:gkShine 2.8s ease-in-out infinite}
@keyframes gkShine{55%,100%{transform:translateX(120%)}}
.btn.primary:hover{filter:brightness(1.06)}
.btn.ghost{background:rgba(19,27,46,.78);border:1px solid rgba(124,111,224,.18);color:#f0f4ff;font-weight:700;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.btn.ghost:hover{background:rgba(26,37,64,.85);border-color:rgba(124,111,224,.32)}
.hint{font-size:11px;color:#7a8ba8;line-height:1.55;text-align:center;margin:0;padding:12px;background:rgba(255,255,255,.02);border-radius:10px;border:1px solid rgba(124,111,224,.12);width:100%}
.foot{display:flex;align-items:center;justify-content:center;gap:6px;color:#7a8ba8;font-size:11px;opacity:.75;letter-spacing:.3px}
.foot svg{width:12px;height:12px;opacity:.85}
@media(max-width:380px){.title{font-size:22px}.pill{font-size:12.5px;padding:11px 12px}}
</style></head><body><div class="card">' . $pills . $iconBlock . '<h1 class="title">' . $title . '</h1><p class="msg">' . $msg . '</p>' . $features . $extra . '<div class="foot">' . gkIcon('lock') . '<span>' . h($GLOBALS['L']['foot_secure'] ?? 'Protected by advanced security') . '</span></div></div></body></html>';
    exit;
}

function buildFeatures(array $fk, array $L) {
    return gkPills([
        ['icon' => gkIcon('clock'),  'text' => (int)$fk['days'] . ' ' . h($L['days'])],
        ['icon' => gkIcon('game'),   'text' => h(mb_strimwidth($fk['game_name'], 0, 12, '…', 'UTF-8'))],
        ['icon' => gkIcon('phone'),  'text' => h($L['feat_oneday'])],
        ['icon' => gkIcon('shield'), 'text' => h($L['feat_secure'])],
    ]);
}

function renderKeyBox($keyCode, $gameName, $days, $siteUrl, array $L) {
    return '<div class="label">' . h($L['lbl_yourkey']) . '</div>'
         . '<div class="info-box" style="flex-direction:column;gap:8px;text-align:center"><div class="key-code key-code--ok">' . h($keyCode) . '</div>'
         . '<div class="meta">🎮 ' . h($gameName) . ' · ' . (int)$days . ' ' . h($L['days']) . '</div></div>'
         . '<button class="btn primary" onclick="copyKey(' . json_encode($keyCode) . ')">' . gkIcon('copy') . ' ' . h($L['copy_key']) . '</button>'
         . '<a class="btn ghost" href="' . h($siteUrl) . '">' . h($L['go_miniapp']) . '</a>'
         . '<script>function copyKey(t){navigator.clipboard.writeText(t).then(function(){alert(' . json_encode($L['copied']) . ')}).catch(function(){prompt(' . json_encode($L['prompt_copy']) . ',t)})}</script>';
}

// =============================================
// Load free_key
// =============================================
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

$pills = langPills($LANG, $t);

if (!$fk) gkPage(h($L['invalid_link']), h($L['invalid_link_msg']), false, '', $pills);

if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) {
    gkPage(h($L['expired_title']), h($L['expired_msg']), false, '', $pills);
}

$features = buildFeatures($fk, $L);

// =============================================
// Web claim theo IP — 1 IP / 1 free key / ngày
// =============================================
$ip     = getClientIp();
$ipHash = ipHash($ip);
$today  = date('Y-m-d');

// POST submit form → thực hiện claim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Đã claim chính free_key này từ IP này → trả key cũ
    $chk = $db->prepare("SELECT key_code FROM free_key_web_claims WHERE free_key_id = ? AND ip_hash = ? LIMIT 1");
    $chk->execute([$fk['id'], $ipHash]);
    if ($row = $chk->fetch()) {
        gkPage(h($L['already_title']), h($L['already_msg']), true,
            renderKeyBox($row['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills, $features);
    }

    // 2) IP đã claim free key nào hôm nay → chặn
    $chk2 = $db->prepare("SELECT key_code FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? ORDER BY id DESC LIMIT 1");
    $chk2->execute([$ipHash, $today]);
    if ($row = $chk2->fetch()) {
        gkPage(h($L['today_title']), h($L['today_msg']), true,
            renderKeyBox($row['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills, $features);
    }

    // 3) INSERT — UNIQUE (free_key_id, ip_hash) + UNIQUE (ip_hash, claim_date) chống race
    try {
        $ins = $db->prepare("INSERT INTO free_key_web_claims (free_key_id, ip_hash, key_code, claim_date) VALUES (?, ?, ?, ?)");
        $ins->execute([$fk['id'], $ipHash, $fk['key_code'], $today]);

        gkPage(h($L['success_title']), h($L['success_msg']), true,
            renderKeyBox($fk['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
            $pills, $features);
    } catch (PDOException $e) {
        // Duplicate key (race) → fetch lại key cũ
        if ((int)$e->errorInfo[1] === 1062) {
            $chk = $db->prepare("SELECT key_code FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? ORDER BY id DESC LIMIT 1");
            $chk->execute([$ipHash, $today]);
            if ($row = $chk->fetch()) {
                gkPage(h($L['already_title']), h($L['race_msg']), true,
                    renderKeyBox($row['key_code'], $fk['game_name'], $fk['days'], SITE_URL, $L),
                    $pills, $features);
            }
        }
        error_log('[GETKEY_WEB] ' . $e->getMessage());
        gkPage(h($L['error_title']), h($L['error_msg']), false, '', $pills, $features);
    }
}

// GET → hiện form 1 nút (không có input)
gkPage(
    h($L['form_title']),
    $L['form_msg'],
    false,
    '<div class="label">' . h($L['lbl_game']) . '</div>'
    . '<div class="info-box">'
    . '<span>' . h($fk['game_name']) . '</span>'
    . '<span class="info-right"><span class="meta">' . (int)$fk['days'] . ' ' . h($L['days']) . '</span><span class="chev">' . gkIcon('chev') . '</span></span>'
    . '</div>'
    . '<form method="POST" style="width:100%;margin:0">'
    . '<input type="hidden" name="lang" value="' . h($LANG) . '">'
    . '<button class="btn primary" type="submit">' . gkIcon('arrow') . ' ' . h($L['form_submit']) . '</button>'
    . '</form>'
    . '<p class="hint">' . $L['form_hint'] . '</p>'
    . '<a class="btn ghost" href="' . h(SITE_URL) . '">' . h($L['open_miniapp']) . '</a>',
    $pills,
    $features
);
