<?php
/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
require_once __DIR__ . '/config.php';

// =============================================
// SETUP_WEBHOOK.PHP — TOOL STANDALONE
// =============================================
// Re-set / xem / xoá webhook Telegram khi installer fail hoặc admin panel 500.
//
// Auth: token derive từ BOT_TOKEN + ADMIN_CHAT_ID (chỉ ai có config.local.php
// mới biết được). Không phụ thuộc session admin nên dùng được kể cả khi
// /admin/ đang 500.
//
// URL: /setup_webhook.php?token=<16hex>&action=set|info|delete
// =============================================

if (!defined('BOT_TOKEN') || BOT_TOKEN === '' || strpos(BOT_TOKEN, 'your_bot') === 0) {
    http_response_code(503);
    exit('BOT_TOKEN chưa cấu hình trong config.local.php');
}
if (!defined('ADMIN_CHAT_ID') || ADMIN_CHAT_ID === '') {
    http_response_code(503);
    exit('ADMIN_CHAT_ID chưa cấu hình trong config.local.php');
}

// Auth: chấp nhận TELEGRAM_WEBHOOK_SECRET làm token trực tiếp (copy 1 dòng từ
// config.local.php) — đơn giản hơn SHA. Vẫn an toàn vì secret chỉ ai có FTP mới đọc.
$inToken = $_GET['token'] ?? '';
$valid   = false;
if (defined('TELEGRAM_WEBHOOK_SECRET') && TELEGRAM_WEBHOOK_SECRET !== '' && hash_equals(TELEGRAM_WEBHOOK_SECRET, $inToken)) {
    $valid = true;
}
if (!$valid) {
    http_response_code(403);
    echo "<!doctype html><meta charset='utf-8'><title>403</title>";
    echo "<pre style='font-family:monospace;padding:30px;color:#c00'>403 Forbidden\n\n";
    echo "Thiếu / sai setup token.\n\n";
    echo "Cách lấy token:\n";
    echo "  1. Mở config.local.php qua FTP/cPanel.\n";
    echo "  2. Tìm dòng: define('TELEGRAM_WEBHOOK_SECRET', '............');\n";
    echo "  3. Copy nguyên chuỗi giữa 2 dấu nháy (32 ký tự hex).\n";
    echo "  4. Gọi lại: setup_webhook.php?token=<chuỗi đó>\n</pre>";
    exit;
}

$webhookUrl = rtrim(SITE_URL, '/') . '/webhook.php';
$secret     = defined('TELEGRAM_WEBHOOK_SECRET') ? TELEGRAM_WEBHOOK_SECRET : '';
$action     = $_GET['action'] ?? 'set';

function callTg(string $method, array $params = []): array {
    $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/' . $method);
    curl_setopt_array($ch, [
        CURLOPT_POST           => !empty($params),
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) return ['ok' => false, 'error' => 'cURL: ' . $err];
    $j = json_decode($raw, true);
    return is_array($j) ? $j : ['ok' => false, 'raw' => $raw];
}

header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><meta charset='utf-8'><title>Setup Webhook</title>";
echo "<style>body{font-family:-apple-system,sans-serif;background:#0d1117;color:#e6edf3;max-width:800px;margin:40px auto;padding:24px}";
echo "h1{font-size:22px}pre{background:#161b22;padding:14px;border-radius:8px;border:1px solid #30363d;overflow-x:auto}";
echo "a{color:#58a6ff;margin-right:14px}.ok{color:#3fb950}.err{color:#f85149}</style>";
echo "<h1>🤖 Telegram Webhook Setup</h1>";
echo "<p>Bot: <code>" . htmlspecialchars(substr(BOT_TOKEN, 0, 12)) . "…</code> · Webhook URL: <code>" . htmlspecialchars($webhookUrl) . "</code></p>";

if ($action === 'set') {
    $res = callTg('setWebhook', [
        'url'              => $webhookUrl,
        'secret_token'     => $secret,
        'allowed_updates'  => json_encode(['message', 'callback_query']),
        'drop_pending_updates' => 'true',
    ]);
    $cls = !empty($res['ok']) ? 'ok' : 'err';
    echo "<h2 class='$cls'>" . (!empty($res['ok']) ? '✅ Set webhook OK' : '❌ Set webhook FAIL') . "</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
} elseif ($action === 'info') {
    $res = callTg('getWebhookInfo');
    echo "<h2>📊 Webhook hiện tại trên Telegram</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    if (!empty($res['result']['last_error_message'])) {
        echo "<p class='err'>⚠️ Last error: " . htmlspecialchars($res['result']['last_error_message']) . "</p>";
    }
} elseif ($action === 'delete') {
    $res = callTg('deleteWebhook', ['drop_pending_updates' => 'true']);
    $cls = !empty($res['ok']) ? 'ok' : 'err';
    echo "<h2 class='$cls'>" . (!empty($res['ok']) ? '✅ Webhook đã xoá' : '❌ Xoá fail') . "</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
} else {
    echo "<p class='err'>Unknown action: " . htmlspecialchars($action) . "</p>";
}

$base = '?token=' . rawurlencode($inToken);
echo "<p style='margin-top:20px'>";
echo "<a href='{$base}&action=set'>🔄 Set lại</a>";
echo "<a href='{$base}&action=info'>🔍 Xem info</a>";
echo "<a href='{$base}&action=delete'>🗑️ Xoá webhook</a>";
echo "</p>";
echo "<p style='color:#8b949e;font-size:12px;margin-top:30px'>💡 Sau khi webhook OK, gửi /start vào bot Telegram để verify.</p>";
