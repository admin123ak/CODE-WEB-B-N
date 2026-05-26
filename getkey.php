<?php
require_once __DIR__ . '/config.php';

// =============================================
// GETKEY.PHP — Trang web claim key (không cần Telegram)
// UI clone 100% widget Free Key Mini App: palette red-orange, .free-card structure.
// =============================================

// =============================================
// I18N (vi/en/es)
// =============================================
$LANG_ALL = [
    'vi' => [
        'too_many'         => 'Quá nhiều yêu cầu',
        'too_many_msg'     => 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.',
        'invalid_link'     => 'Không có key khả dụng',
        'invalid_link_msg' => 'Admin chưa kích hoạt free key nào. Vui lòng quay lại sau.',
        'expired_title'    => 'Key free đã hết hạn',
        'expired_msg'      => 'Key này không còn khả dụng.',
        'already_title'    => 'Bạn đã nhận key này rồi',
        'already_msg'      => 'Key đã sẵn sàng sử dụng.',
        'today_title'      => 'Bạn đã nhận key hôm nay',
        'today_msg'        => 'Mỗi IP nhận 1 key/ngày. Key của bạn:',
        'success_title'    => 'Nhận key thành công',
        'success_msg'      => 'Copy key và dán vào game để dùng.',
        'race_msg'         => 'Key đã được cấp trước đó.',
        'error_title'      => 'Không nhận được key',
        'error_msg'        => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
        'card_title'       => 'KEY MIỄN PHÍ HÔM NAY',
        'card_sub'         => 'Mỗi IP nhận 1 key/ngày. Bấm nút bên dưới để nhận key tức thì — không cần đăng ký.',
        'btn_get'          => 'NHẬN KEY NGAY',
        'btn_loading'      => 'Đang xử lý…',
        'reset_daily'      => '⏰ Reset hằng ngày 00:00 (giờ VN)',
        'received_at'      => 'Nhận lúc',
        'back_tomorrow'    => 'Hôm sau vào lại để nhận key mới.',
        'copy'             => 'Copy',
        'copy_key'         => 'Copy Key',
        'copied'           => 'Đã copy!',
        'prompt_copy'      => 'Copy key:',
        'open_miniapp'     => 'Mở trong Mini App',
        'days'             => 'Days',
        'feat_full'        => 'Full Access',
        'feat_device'      => '1 Device',
        'feat_secure'      => 'Secure',
        'lbl_game'         => 'Game',
        'foot_secure'      => 'Protected by advanced security',
        'none_title'       => 'Chưa có key hôm nay',
        'none_msg'         => 'Admin chưa kích hoạt key mới. Sáng mai vào lại.',
        'new_morning'      => '🌅 Key mới sẽ có vào sáng mai',
    ],
    'en' => [
        'too_many'         => 'Too many requests',
        'too_many_msg'     => "You're going too fast. Please try again in a few minutes.",
        'invalid_link'     => 'No key available',
        'invalid_link_msg' => 'Admin has not activated any free key. Please come back later.',
        'expired_title'    => 'Free key expired',
        'expired_msg'      => 'This key is no longer available.',
        'already_title'    => 'You already claimed',
        'already_msg'      => 'Your key is ready to use.',
        'today_title'      => 'You already got a key today',
        'today_msg'        => 'One key per IP per day. Your key:',
        'success_title'    => 'Key claimed successfully',
        'success_msg'      => 'Copy and paste into the game.',
        'race_msg'         => 'The key was already issued.',
        'error_title'      => 'Could not claim key',
        'error_msg'        => 'Something went wrong. Please try again later.',
        'card_title'       => "TODAY'S FREE KEY",
        'card_sub'         => 'One key per IP per day. Tap the button to receive instantly — no signup needed.',
        'btn_get'          => 'CLAIM KEY NOW',
        'btn_loading'      => 'Processing…',
        'reset_daily'      => '⏰ Resets daily at 00:00 (VN time)',
        'received_at'      => 'Received at',
        'back_tomorrow'    => 'Come back tomorrow for a new key.',
        'copy'             => 'Copy',
        'copy_key'         => 'Copy Key',
        'copied'           => 'Copied!',
        'prompt_copy'      => 'Copy key:',
        'open_miniapp'     => 'Open in Mini App',
        'days'             => 'Days',
        'feat_full'        => 'Full Access',
        'feat_device'      => '1 Device',
        'feat_secure'      => 'Secure',
        'lbl_game'         => 'Game',
        'foot_secure'      => 'Protected by advanced security',
        'none_title'       => 'No key today',
        'none_msg'         => 'Admin has not activated a new key. Come back tomorrow morning.',
        'new_morning'      => '🌅 New key tomorrow morning',
    ],
    'es' => [
        'too_many'         => 'Demasiadas solicitudes',
        'too_many_msg'     => 'Estás yendo muy rápido. Inténtalo de nuevo en unos minutos.',
        'invalid_link'     => 'Sin clave disponible',
        'invalid_link_msg' => 'El administrador no ha activado ninguna clave gratis. Vuelve más tarde.',
        'expired_title'    => 'Clave expirada',
        'expired_msg'      => 'Esta clave ya no está disponible.',
        'already_title'    => 'Ya reclamaste',
        'already_msg'      => 'Tu clave está lista.',
        'today_title'      => 'Ya obtuviste una clave hoy',
        'today_msg'        => 'Una clave por IP al día. Tu clave:',
        'success_title'    => 'Clave reclamada',
        'success_msg'      => 'Copia y pega en el juego.',
        'race_msg'         => 'La clave ya fue entregada.',
        'error_title'      => 'No se pudo reclamar',
        'error_msg'        => 'Algo salió mal. Inténtalo más tarde.',
        'card_title'       => 'CLAVE GRATIS HOY',
        'card_sub'         => 'Una clave por IP al día. Pulsa el botón para recibirla al instante — sin registro.',
        'btn_get'          => 'RECLAMAR AHORA',
        'btn_loading'      => 'Procesando…',
        'reset_daily'      => '⏰ Se reinicia diariamente a las 00:00 (VN)',
        'received_at'      => 'Recibida a las',
        'back_tomorrow'    => 'Vuelve mañana para otra clave.',
        'copy'             => 'Copiar',
        'copy_key'         => 'Copiar Clave',
        'copied'           => '¡Copiado!',
        'prompt_copy'      => 'Copia la clave:',
        'open_miniapp'     => 'Abrir en Mini App',
        'days'             => 'Días',
        'feat_full'        => 'Acceso Total',
        'feat_device'      => '1 Dispositivo',
        'feat_secure'      => 'Seguro',
        'lbl_game'         => 'Juego',
        'foot_secure'      => 'Protegido por seguridad avanzada',
        'none_title'       => 'Sin clave hoy',
        'none_msg'         => 'El admin aún no ha activado. Vuelve mañana.',
        'new_morning'      => '🌅 Clave nueva mañana',
    ],
];

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
// Rate limit 20/5min/IP
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
           . '<body style="font-family:-apple-system,sans-serif;background:#06080f;color:#f0f4ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;text-align:center">'
           . '<div><h2>⏳ ' . h($L['too_many']) . '</h2><p>' . h($L['too_many_msg']) . '</p></div>';
        exit;
    }
})();

// =============================================
// Helpers
// =============================================
function getClientIp() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $first = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        return trim($first);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function ipHash($ip) {
    return hash('sha256', $ip . '|' . (defined('SITE_URL') ? SITE_URL : ''));
}

function langPills($current, $t = '') {
    $flags = ['vi' => '&#127483;&#127475;', 'en' => '&#127468;&#127463;', 'es' => '&#127466;&#127480;'];
    $titles = ['vi' => 'Tiếng Việt', 'en' => 'English', 'es' => 'Español'];
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $html = '<div class="lang-pills">';
    foreach ($flags as $code => $flag) {
        $params = [];
        if ($t) $params['t'] = $t;
        $params['lang'] = $code;
        $url = $base . '?' . http_build_query($params);
        $active = $code === $current ? ' lang-pill--active' : '';
        $html .= '<a href="' . h($url) . '" title="' . h($titles[$code]) . '" class="lang-pill' . $active . '">' . $flag . '</a>';
    }
    return $html . '</div>';
}

// SVG icons identical to Mini App widget
function gkSvg($name) {
    $icons = [
        'diamond' => '<svg viewBox="0 0 24 24" width="36" height="36" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg>',
        'clock'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'full'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M15 3h6v6M10 14L21 3"/></svg>',
        'device'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg>',
        'shield'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'arrow'   => '<svg class="free-arrow" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>',
        'secure'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'check'   => '✅',
        'sad'     => '😔',
        'warn'    => '⚠️',
    ];
    return $icons[$name] ?? '';
}

function shellOpen() {
    global $L, $LANG;
    echo '<!doctype html><html lang="' . h($LANG) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">';
    echo '<title>HCLOU — ' . h($L['card_title']) . '</title>';
    echo '<meta name="description" content="Nhận key game miễn phí mỗi ngày — không cần đăng ký, không cần Telegram.">';
    echo '<style>' . gkCss() . '</style></head><body>';
    echo '<div id="app"><div class="page-wrap">';
}

function shellClose() {
    global $L;
    echo '<div class="free-secure">' . gkSvg('secure') . '<span>' . h($L['foot_secure']) . '</span></div>';
    echo '</div></div></body></html>';
    exit;
}

function gkCss() {
    // Inline CSS — copy nguyên design system Mini App (red-orange brand)
    return ':root{--bg:#06080f;--bg2:#0c1120;--bg3:#131b2e;--bg4:#1a2540;--border:#1a2744;--text:#f0f4ff;--text2:#7a8ba8;--text3:#9fb3d0;--blue:#4f8cff;--purple:#a78bfa;--green:#34d399;--green2:#6ee7b7;--orange:#fb923c;--orange2:#fdba74;--brand:#dc2626;--brand2:#ef4444;--brand3:#f97316;--glass:linear-gradient(160deg,rgba(12,17,32,.88),rgba(19,27,46,.82));--glass-border:rgba(79,140,255,.12);--card-shadow:0 8px 32px rgba(0,0,0,.45),0 1px 0 rgba(255,255,255,.04);--ease-spring:cubic-bezier(.22,1,.36,1)}
@import url(\'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap\');
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:var(--bg);color:var(--text);font-family:"Inter",-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;-webkit-font-smoothing:antialiased}
#app{min-height:100vh;display:flex;flex-direction:column;max-width:480px;margin:0 auto;background:radial-gradient(ellipse at 20% 0%,rgba(79,140,255,.10),transparent 50%),radial-gradient(ellipse at 80% 15%,rgba(167,139,250,.08),transparent 45%),radial-gradient(ellipse at 50% 100%,rgba(34,211,238,.06),transparent 50%),var(--bg);position:relative}
.page-wrap{padding:18px 0 24px;display:flex;flex-direction:column;gap:14px}
.lang-pills{display:flex;justify-content:center;gap:6px;margin:0 16px 4px}
.lang-pill{text-decoration:none;width:38px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-size:15px;line-height:1;background:rgba(15,23,42,.7);border:1px solid rgba(239,68,68,.22);transition:all .2s}
.lang-pill--active{background:linear-gradient(135deg,var(--brand),var(--brand2));box-shadow:0 4px 12px rgba(239,68,68,.35);border:1px solid transparent}
@keyframes floatIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@keyframes freeBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
@keyframes freeFadeIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
@keyframes freeSpin{to{transform:rotate(360deg)}}
.free-card{margin:0 16px 14px;background:var(--glass);border:1px solid var(--glass-border);border-radius:18px;padding:36px 28px;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:floatIn .5s var(--ease-spring) .1s both;text-align:center;display:flex;flex-direction:column;align-items:center;gap:20px}
.free-icon{display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;font-size:36px;margin:0;box-shadow:0 8px 24px rgba(220,38,38,.35);animation:freeBounce 2.6s ease-in-out infinite}
.free-icon--ok{background:linear-gradient(135deg,var(--green),#10b981);box-shadow:0 8px 24px rgba(52,211,153,.32)}
.free-title{font-size:26px;font-weight:800;color:#fff;margin:0;letter-spacing:-.3px;line-height:1.2}
.free-sub{font-size:14px;color:var(--text2);margin:-12px 0 0;line-height:1.55}
.free-badges{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}
.free-badge{background:rgba(19,27,46,.78);border:1px solid rgba(79,140,255,.12);border-radius:14px;padding:13px 14px;display:flex;align-items:center;gap:10px;color:var(--text);font-size:13.5px;font-weight:600;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.free-badge svg{width:18px;height:18px;opacity:.85;flex-shrink:0;stroke:var(--orange2);fill:none;stroke-width:2}
.free-select-block{width:100%}
.free-label{display:block;font-size:11px;font-weight:700;letter-spacing:.5px;color:var(--text2);margin-bottom:6px;text-transform:uppercase;text-align:left}
.free-select-wrap{position:relative}
.free-select{width:100%;background:rgba(19,27,46,.78);border:1px solid rgba(79,140,255,.12);border-radius:14px;padding:14px 40px 14px 16px;color:var(--text);font-size:14px;font-family:inherit;font-weight:700;appearance:none;-webkit-appearance:none;outline:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.free-select-wrap:after{content:\'\';position:absolute;right:18px;top:50%;width:9px;height:9px;border-right:2px solid var(--text2);border-bottom:2px solid var(--text2);transform:translateY(-65%) rotate(45deg);pointer-events:none}
.free-form{width:100%;margin:0;display:flex;flex-direction:column;gap:20px}
.free-btn{width:100%;padding:17px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;font-size:16px;font-weight:700;cursor:pointer;font-family:"Inter",sans-serif;box-shadow:0 8px 24px rgba(220,38,38,.3);transition:opacity .2s,transform .15s,box-shadow .2s;display:inline-flex;align-items:center;justify-content:center;gap:10px}
.free-btn:active{transform:scale(.98)}
.free-btn:hover{opacity:.92;transform:translateY(-1px)}
.free-btn:disabled{opacity:.4;filter:grayscale(1);cursor:not-allowed;box-shadow:none;transform:none}
.free-btn .free-arrow{width:18px;height:18px;fill:none;stroke:#fff;stroke-width:2;display:block}
.free-btn .free-spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:freeSpin .7s linear infinite}
.free-btn.loading .free-arrow{display:none}
.free-btn.loading .free-spinner{display:block}
.free-timer{width:100%;margin:0;padding:14px;border-radius:14px;background:rgba(19,27,46,.78);border:1px solid rgba(79,140,255,.12);color:var(--orange2);font-size:13.5px;font-weight:500;line-height:1.5;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.free-claimed{width:100%;margin:0;padding:16px;border-radius:14px;background:rgba(19,27,46,.78);border:1px solid rgba(52,211,153,.22);box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.free-claimed-code{font-size:15px;font-weight:700;color:var(--green2);font-family:\'SF Mono\',\'JetBrains Mono\',monospace;margin:8px 0;padding:11px;background:rgba(0,0,0,.25);border-radius:10px;word-break:break-all;letter-spacing:1.2px}
.free-claimed-meta{font-size:11px;color:var(--text2);margin-top:6px}
.free-result-box{display:flex;width:100%;background:rgba(19,27,46,.78);border:1px solid rgba(79,140,255,.12);border-radius:14px;padding:14px 16px;gap:10px;align-items:center;animation:freeFadeIn .3s ease;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.free-result-key{flex:1;font-size:12px;color:var(--orange2);font-family:\'SF Mono\',monospace;word-break:break-all;line-height:1.5}
.free-copy-btn{background:rgba(19,27,46,.78);border:1px solid rgba(79,140,255,.12);border-radius:10px;padding:6px 12px;color:var(--orange2);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;white-space:nowrap;transition:background .15s}
.free-copy-btn:hover{background:rgba(26,37,64,.85)}
.free-secure{display:flex;align-items:center;justify-content:center;gap:6px;color:var(--text2);font-size:12px;opacity:.7;padding:8px 16px}
.free-secure svg{width:13px;height:13px;fill:currentColor}
.free-empty{margin:0 16px 14px;padding:48px 28px;background:var(--glass);border:1px solid var(--glass-border);border-radius:18px;text-align:center;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);display:flex;flex-direction:column;align-items:center;gap:14px;animation:floatIn .5s var(--ease-spring) .1s both}
.free-empty-ico{font-size:42px;opacity:.85}
.free-empty-title{font-size:18px;font-weight:800;color:#fff;margin:0}
.free-empty-msg{font-size:13.5px;color:var(--text2);margin:0;line-height:1.55}
.ghost-link{display:inline-flex;align-items:center;justify-content:center;gap:6px;color:var(--text2);text-decoration:none;font-size:12px;font-weight:600;padding:6px 14px;border-radius:10px;background:rgba(255,255,255,.02);border:1px solid rgba(79,140,255,.10);margin-top:4px}
.ghost-link:hover{color:var(--text);background:rgba(255,255,255,.04)}
';
}

// Render card khi chưa claim (form 1 nút)
function renderForm($fk, $L, $LANG) {
    $gameName = h($fk['game_name']);
    $days     = (int)$fk['days'];
    ?>
    <div class="free-card">
      <div class="free-icon"><?= gkSvg('diamond') ?></div>
      <div class="free-title"><?= h($L['card_title']) ?></div>
      <div class="free-sub"><?= h($L['card_sub']) ?></div>
      <div class="free-badges">
        <div class="free-badge"><?= gkSvg('clock') ?><?= $days ?> <?= h($L['days']) ?></div>
        <div class="free-badge"><?= gkSvg('full') ?><?= h($L['feat_full']) ?></div>
        <div class="free-badge"><?= gkSvg('device') ?><?= h($L['feat_device']) ?></div>
        <div class="free-badge"><?= gkSvg('shield') ?><?= h($L['feat_secure']) ?></div>
      </div>
      <div class="free-select-block">
        <label class="free-label"><?= h($L['lbl_game']) ?></label>
        <div class="free-select-wrap">
          <select class="free-select" disabled><option><?= $gameName ?></option></select>
        </div>
      </div>
      <form method="POST" class="free-form" id="claimForm" onsubmit="document.getElementById('claimBtn').classList.add('loading');document.getElementById('claimBtnText').textContent=<?= json_encode($L['btn_loading']) ?>;">
        <input type="hidden" name="lang" value="<?= h($LANG) ?>">
        <button type="submit" class="free-btn" id="claimBtn">
          <?= gkSvg('arrow') ?>
          <div class="free-spinner"></div>
          <span id="claimBtnText"><?= h($L['btn_get']) ?></span>
        </button>
      </form>
      <div class="free-timer"><?= h($L['reset_daily']) ?></div>
    </div>
    <?php
}

// Render card khi đã/vừa claim (key + copy)
function renderClaimed($keyCode, $gameName, $days, $L, $titleKey = 'success_title', $msgKey = 'success_msg', $claimedAt = null) {
    $safeKey  = h($keyCode);
    $safeGame = h($gameName);
    $jsKey    = json_encode($keyCode);
    $jsCopied = json_encode($L['copied']);
    $jsPrompt = json_encode($L['prompt_copy']);
    $meta = $claimedAt ? '<div class="free-claimed-meta">' . h($L['received_at']) . ' ' . h(date('d/m/Y H:i', strtotime($claimedAt))) . '</div>' : '';
    ?>
    <div class="free-card">
      <div class="free-icon free-icon--ok"><?= gkSvg('check') ?></div>
      <div class="free-title"><?= h($L[$titleKey]) ?></div>
      <div class="free-sub"><?= h($L[$msgKey]) ?></div>
      <div class="free-claimed">
        <div class="free-claimed-code"><?= $safeKey ?></div>
        <div class="free-claimed-meta">🎮 <?= $safeGame ?> · <?= (int)$days ?> <?= h($L['days']) ?></div>
        <?= $meta ?>
      </div>
      <button class="free-btn" onclick="copyKey(<?= $jsKey ?>)">
        <span><?= h($L['copy_key']) ?></span>
      </button>
      <div class="free-timer"><?= h($L['back_tomorrow']) ?></div>
    </div>
    <script>
    function copyKey(k){
      if(navigator.clipboard&&navigator.clipboard.writeText){
        navigator.clipboard.writeText(k).then(function(){alert(<?= $jsCopied ?>)}).catch(function(){prompt(<?= $jsPrompt ?>,k)});
      } else { prompt(<?= $jsPrompt ?>,k); }
    }
    </script>
    <?php
}

function renderEmpty($titleKey, $msgKey, $extraMsg, $L) {
    ?>
    <div class="free-empty">
      <div class="free-empty-ico">😔</div>
      <div class="free-empty-title"><?= h($L[$titleKey]) ?></div>
      <div class="free-empty-msg"><?= h($L[$msgKey]) ?></div>
      <?php if ($extraMsg): ?><div class="free-timer" style="margin-top:6px"><?= h($L[$extraMsg]) ?></div><?php endif; ?>
      <?php if (defined('SITE_URL')): ?><a class="ghost-link" href="<?= h(SITE_URL) ?>"><?= h($L['open_miniapp']) ?> →</a><?php endif; ?>
    </div>
    <?php
}

function renderError($titleKey, $msgKey, $L) {
    ?>
    <div class="free-empty">
      <div class="free-empty-ico">⚠️</div>
      <div class="free-empty-title"><?= h($L[$titleKey]) ?></div>
      <div class="free-empty-msg"><?= h($L[$msgKey]) ?></div>
    </div>
    <?php
}

// =============================================
// LOAD free_key
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
} else {
    $stmt = $db->query("SELECT fk.*, g.name game_name, p.name pkg_name, p.days
        FROM free_keys fk
        JOIN games g    ON fk.game_id    = g.id
        JOIN packages p ON fk.package_id = p.id
        WHERE fk.is_active = 1 AND fk.expire_at > NOW()
        ORDER BY fk.created_at DESC
        LIMIT 1");
    $fk = $stmt->fetch();
}

$ip     = getClientIp();
$ipHash = ipHash($ip);
$today  = date('Y-m-d');

// =============================================
// PRE-RENDER HANDLERS — chạy TRƯỚC khi echo HTML
// để header('Location:') không bị "headers already sent".
// =============================================

// === POST: build short_url 2 lớp → 302 redirect ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $fk && $fk['is_active'] && strtotime($fk['expire_at']) >= time()) {
    // IP đã có key hôm nay → bỏ qua shortlink, redirect về trang gốc để hiện key cũ
    $chk = $db->prepare("SELECT id FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? LIMIT 1");
    $chk->execute([$ipHash, $today]);
    if ($chk->fetch()) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    // Build/lấy short_url. Target = getkey.php?t=token
    $shortUrl = $fk['short_url'] ?? null;
    if (!$shortUrl) {
        $target = SITE_URL . '/getkey.php?t=' . urlencode($fk['claim_token']);
        try {
            $shortUrl = buildFreeShortlink($target);
            $db->prepare("UPDATE free_keys SET short_url = ? WHERE id = ?")->execute([$shortUrl, $fk['id']]);
        } catch (Exception $e) {
            error_log('[GETKEY_SHORT] ' . $e->getMessage());
            // Không thoát ngay — để fall qua render error page bên dưới
            $renderError = true;
        }
    }
    if (!empty($shortUrl)) {
        header('Location: ' . $shortUrl);
        exit;
    }
}

// === GET ?t=: user vừa vượt 2 lớp về → claim atomic, lưu state để render ===
$claimedRow = null;  // ['key_code', 'claimed_at', 'state' => 'success|today|already|race']
if ($t && $fk && $fk['is_active'] && strtotime($fk['expire_at']) >= time() && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // a) Đã claim free_key này từ IP này
    $chk = $db->prepare("SELECT key_code, claimed_at FROM free_key_web_claims WHERE free_key_id = ? AND ip_hash = ? LIMIT 1");
    $chk->execute([$fk['id'], $ipHash]);
    if ($row = $chk->fetch()) {
        $claimedRow = $row + ['state' => 'already'];
    } else {
        // b) IP đã claim free key nào hôm nay
        $chk2 = $db->prepare("SELECT key_code, claimed_at FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? ORDER BY id DESC LIMIT 1");
        $chk2->execute([$ipHash, $today]);
        if ($row = $chk2->fetch()) {
            $claimedRow = $row + ['state' => 'today'];
        } else {
            // c) INSERT
            try {
                $ins = $db->prepare("INSERT INTO free_key_web_claims (free_key_id, ip_hash, key_code, claim_date) VALUES (?, ?, ?, ?)");
                $ins->execute([$fk['id'], $ipHash, $fk['key_code'], $today]);
                $claimedRow = ['key_code' => $fk['key_code'], 'claimed_at' => date('Y-m-d H:i:s'), 'state' => 'success'];
            } catch (PDOException $e) {
                if ((int)$e->errorInfo[1] === 1062) {
                    $chk = $db->prepare("SELECT key_code, claimed_at FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? ORDER BY id DESC LIMIT 1");
                    $chk->execute([$ipHash, $today]);
                    if ($row = $chk->fetch()) $claimedRow = $row + ['state' => 'race'];
                }
                if (!$claimedRow) {
                    error_log('[GETKEY] ' . $e->getMessage());
                    $renderError = true;
                }
            }
        }
    }
}

// === GET trần: nếu IP đã có key hôm nay → render claimed (state = today) ===
if (!$t && !$claimedRow && $fk && $fk['is_active'] && strtotime($fk['expire_at']) >= time() && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $chk = $db->prepare("SELECT key_code, claimed_at FROM free_key_web_claims WHERE ip_hash = ? AND claim_date = ? ORDER BY id DESC LIMIT 1");
    $chk->execute([$ipHash, $today]);
    if ($row = $chk->fetch()) $claimedRow = $row + ['state' => 'today'];
}

// =============================================
// RENDER (mới bắt đầu echo HTML từ đây)
// =============================================
shellOpen();
echo langPills($LANG, $t);

if (!empty($renderError)) {
    renderError('error_title', 'error_msg', $L);
    shellClose();
}

if (!$fk) {
    renderEmpty('none_title', 'none_msg', 'new_morning', $L);
    shellClose();
}

if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) {
    renderEmpty('expired_title', 'expired_msg', 'new_morning', $L);
    shellClose();
}

if ($claimedRow) {
    $stateMap = [
        'success' => ['success_title', 'success_msg'],
        'today'   => ['today_title',   'today_msg'],
        'already' => ['already_title', 'already_msg'],
        'race'    => ['already_title', 'race_msg'],
    ];
    [$tk, $mk] = $stateMap[$claimedRow['state']] ?? ['success_title', 'success_msg'];
    renderClaimed($claimedRow['key_code'], $fk['game_name'], $fk['days'], $L, $tk, $mk, $claimedRow['claimed_at']);
    shellClose();
}

renderForm($fk, $L, $LANG);
shellClose();
