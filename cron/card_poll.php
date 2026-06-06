<?php
/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../backend/lib/balance_helpers.php';
require_once __DIR__ . '/../backend/lib/topup_helpers.php';

// =============================================
// CARD_POLL.PHP — ACTIVE CHECK CRON cho topup_requests method='card'
// =============================================
// Tại sao cần:
//   Khi user nạp thẻ, doithe.vn trả status=99 (pending) → ta lưu DB và đợi
//   callback. Nhưng callback có thể chậm/lỗi (firewall, DNS, doithe queue
//   backlog). Cron này active-poll từng request pending bằng command=check
//   để lấy kết quả ngay khi doithe xử lý xong, không phụ thuộc callback.
//
// Logic:
//   - Query topup_requests WHERE method='card' AND status='pending'
//     AND provider_request_id != ''
//     AND created_at < NOW() - INTERVAL 60 SECOND  (cho doithe thời gian xử lý)
//     LIMIT 20
//   - For each: callDoitheCheckStatus($request_id)
//       status=1 / 2  → topupApprove() + Telegram notify
//       status=3 / 100-199 → topupReject()
//       status=99 / 4 → giữ pending, log
//
// Auth: ?secret=<CARD_POLL_SECRET>  (HMAC sha256 từ partner_id + BOT_TOKEN)
// Run: hclouHttpCall('/cron/card_poll.php', ['secret' => CARD_POLL_SECRET]) qua cron/run.php
// =============================================

$cpStartedAt = microtime(true);
$cpSource    = (PHP_SAPI === 'cli') ? 'cron' : (isset($_GET['src']) && $_GET['src'] === 'admin' ? 'admin' : 'http');

function writeCardPollStatus(array $extra) {
    global $cpStartedAt, $cpSource;
    $file = APP_ROOT . '/data/card_poll_status.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $payload = array_merge([
        'last_run_at'  => date('c'),
        'duration_ms'  => (int) round((microtime(true) - $cpStartedAt) * 1000),
        'source'       => $cpSource,
        'checked'      => 0,
        'approved'     => 0,
        'rejected'     => 0,
        'still_pending'=> 0,
        'skipped'      => false,
        'error'        => null,
    ], $extra);
    @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Alert tương tự MBBank — sai liền 3 lần thì cảnh báo, throttle 30 phút.
function maybeCardPollAlert(?string $error): void {
    $file = APP_ROOT . '/data/card_poll_alert.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $state = ['consecutive_failures' => 0, 'last_alert_at' => null, 'last_error' => null];
    if (is_file($file)) {
        $prev = json_decode((string)@file_get_contents($file), true);
        if (is_array($prev)) $state = array_merge($state, $prev);
    }

    if ($error === null) {
        if (!empty($state['last_alert_at']) && (int)$state['consecutive_failures'] >= 3) {
            try {
                sendTelegram(ADMIN_CHAT_ID,
                    "✅ <b>Card Poll đã hồi phục</b>\n" .
                    "🕐 " . date('Y-m-d H:i:s'));
            } catch (Throwable $e) { /* ignore */ }
        }
        $state = ['consecutive_failures' => 0, 'last_alert_at' => null, 'last_error' => null];
    } else {
        if (stripos($error, 'chưa cấu hình') !== false || stripos($error, 'disabled') !== false) {
            $state['consecutive_failures'] = 0;
            $state['last_error'] = $error;
            @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
            return;
        }
        $state['consecutive_failures'] = (int)$state['consecutive_failures'] + 1;
        $state['last_error'] = substr($error, 0, 200);
        $threshold = 3;
        $throttleSec = 1800;
        $now = time();
        $lastAlertTs = !empty($state['last_alert_at']) ? (int)strtotime($state['last_alert_at']) : 0;
        if ($state['consecutive_failures'] >= $threshold && ($now - $lastAlertTs) >= $throttleSec) {
            try {
                $msg = "⚠️ <b>Card Poll (doithe) lỗi liên tục</b>\n\n" .
                       "🕐 " . date('Y-m-d H:i:s') . "\n" .
                       "🔁 Số lần lỗi liền: <b>" . (int)$state['consecutive_failures'] . "</b>\n" .
                       "❌ " . htmlspecialchars($state['last_error']) . "\n\n" .
                       "Kiểm tra DOITHE_PARTNER_ID / KEY hoặc connectivity tới doithe.vn.";
                sendTelegram(ADMIN_CHAT_ID, $msg);
                $state['last_alert_at'] = date('c');
            } catch (Throwable $e) { /* ignore */ }
        }
    }
    @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// --- Lock file (chống chạy chồng) ---
$lockFile = APP_ROOT . '/data/card_poll.lock';
if (!is_dir(dirname($lockFile))) @mkdir(dirname($lockFile), 0755, true);
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    writeCardPollStatus(['skipped' => true, 'error' => 'previous_run_still_active']);
    http_response_code(200);
    echo json_encode(['success' => true, 'skipped' => true, 'reason' => 'previous_run_still_active']);
    exit;
}
register_shutdown_function(function() use ($lockHandle) {
    if ($lockHandle) { flock($lockHandle, LOCK_UN); fclose($lockHandle); }
});

// --- AUTH ---
if (PHP_SAPI !== 'cli') {
    $secret = $_GET['secret'] ?? '';
    if (!defined('CARD_POLL_SECRET') || !hash_equals(CARD_POLL_SECRET, $secret)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function cpLog($msg) { if (PHP_SAPI === 'cli') echo $msg . PHP_EOL; }

try {
    if (!defined('DOITHE_PARTNER_ID') || DOITHE_PARTNER_ID === '' ||
        !defined('DOITHE_PARTNER_KEY') || DOITHE_PARTNER_KEY === '') {
        throw new Exception('doithe.vn chưa cấu hình');
    }
    $db = getDB();

    // Pending > 60s — chừa thời gian cho doithe xử lý + callback đến trước
    $stmt = $db->prepare(
        "SELECT id, user_id, provider_request_id, card_telco, card_face_value, amount_requested
         FROM topup_requests
         WHERE method='card' AND status='pending'
           AND provider_request_id IS NOT NULL AND provider_request_id != ''
           AND created_at < (NOW() - INTERVAL 60 SECOND)
         ORDER BY id ASC
         LIMIT 20"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $checked = 0; $approved = 0; $rejected = 0; $stillPending = 0;

    foreach ($rows as $r) {
        $checked++;
        $reqId = (string)$r['provider_request_id'];
        $api = callDoitheCheckStatus($reqId);
        if (!$api['ok']) {
            cpLog("[check] req=$reqId FAIL: " . $api['message']);
            $stillPending++;
            continue;
        }
        $st = (int)$api['status'];
        $value = (int)$api['value'];
        $declared = (int)$api['declared_value'];
        $msg = (string)$api['message'];

        if ($st === 1 || $st === 2) {
            // Thành công → tính credit theo value thực × rate telco
            $telco = strtoupper((string)$r['card_telco']);
            [$rate, $multiplier] = cardRateForTelco($telco);
            if ($multiplier < 1.0) $multiplier = 1.0;
            // Nếu doithe không trả value (vài bản check trả 0) thì fallback face_value
            $valForCredit = $value > 0 ? $value : (int)$r['card_face_value'];
            $credit = (int)round($valForCredit / $multiplier);

            try {
                $apRes = topupApprove(
                    $db,
                    (int)$r['id'],
                    (float)$credit,
                    '',
                    json_encode(['poll' => true, 'check_response' => $api['raw']], JSON_UNESCAPED_UNICODE)
                );
                if (empty($apRes['already'])) {
                    $approved++;
                    // Telegram notify (best effort) — chỉ khi user_id có chat_id trong DB users
                    try {
                        $u = $db->prepare("SELECT telegram_id FROM users WHERE id=? LIMIT 1");
                        $u->execute([(int)$r['user_id']]);
                        $tgId = $u->fetchColumn();
                        if ($tgId) {
                            $faceTxt = number_format((int)$r['card_face_value']) . 'đ';
                            $credTxt = number_format($credit) . 'đ';
                            $balAfter = isset($apRes['balance_after']) ? number_format((int)$apRes['balance_after']) . 'đ' : '?';
                            sendTelegram((string)$tgId,
                                "✅ <b>Nạp thẻ thành công</b>\n" .
                                "🎴 " . $telco . " " . $faceTxt . "\n" .
                                "💰 Cộng vào ví: <b>" . $credTxt . "</b>\n" .
                                "💼 Số dư: <b>" . $balAfter . "</b>");
                        }
                    } catch (Throwable $e) { /* notify fail không ảnh hưởng */ }
                }
            } catch (Throwable $e) {
                cpLog("[approve] req=$reqId ERR: " . $e->getMessage());
            }
            continue;
        }
        if ($st === 3 || ($st >= 100 && $st <= 199)) {
            topupReject(
                $db,
                (int)$r['id'],
                'doithe.vn (poll) status=' . $st . ($msg ? ': ' . $msg : ''),
                json_encode(['poll' => true, 'check_response' => $api['raw']], JSON_UNESCAPED_UNICODE)
            );
            $rejected++;
            // Notify user khi reject để khỏi đợi vô vọng
            try {
                $u = $db->prepare("SELECT telegram_id FROM users WHERE id=? LIMIT 1");
                $u->execute([(int)$r['user_id']]);
                $tgId = $u->fetchColumn();
                if ($tgId) {
                    sendTelegram((string)$tgId,
                        "❌ <b>Nạp thẻ thất bại</b>\n" .
                        ($msg ? "Lý do: " . htmlspecialchars($msg) . "\n" : "") .
                        "Mã yêu cầu: " . substr($reqId, 0, 12) . "...");
                }
            } catch (Throwable $e) { /* ignore */ }
            continue;
        }
        // status=99 pending hoặc 4 error tạm thời → giữ nguyên
        $stillPending++;
        cpLog("[pending] req=$reqId status=$st msg=$msg");
    }

    $out = [
        'success' => true,
        'checked' => $checked,
        'approved' => $approved,
        'rejected' => $rejected,
        'still_pending' => $stillPending,
    ];
    writeCardPollStatus($out);
    maybeCardPollAlert(null);
    if (PHP_SAPI === 'cli') cpLog(json_encode($out, JSON_UNESCAPED_UNICODE));
    else jsonResponse($out);
} catch (Exception $e) {
    error_log('[CARD_POLL] ' . $e->getMessage());
    writeCardPollStatus(['error' => $e->getMessage()]);
    maybeCardPollAlert($e->getMessage());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); }
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
