<?php
/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
/**
 * Endpoint callback từ doithe.vn — gọi về sau khi thẻ được xử lý xong.
 *
 * URL cấu hình bên doithe.vn merchant: {SITE_URL}/card_callback.php (POST).
 *
 * Pattern POST body (phổ biến VN providers):
 *   request_id, status, value, declared_value, telco, serial, message, sign
 *   sign = md5(partner_key + status + request_id + value)
 *
 * Status codes:
 *   1   = success (đúng mệnh giá)
 *   2   = wrong amount (sai mệnh giá — vẫn cộng tiền theo value thực)
 *   3   = card invalid / used
 *   4   = error
 *   99  = pending (chưa nên gọi callback, nhưng vẫn ghi log)
 *
 * Reply: provider thường chỉ care HTTP 200. Trả về JSON {ok: true} để debug.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/backend/lib/balance_helpers.php';
require_once __DIR__ . '/backend/lib/topup_helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Lấy payload: hỗ trợ cả form-urlencoded lẫn JSON body.
$payload = $_POST;
if (empty($payload)) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $j = json_decode($raw, true);
        if (is_array($j)) $payload = $j;
        else parse_str($raw, $payload);
    }
}

// Log raw để debug — append-only.
$logDir = __DIR__ . '/data';
@mkdir($logDir, 0775, true);
$logLine = date('Y-m-d H:i:s') . ' ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ' '
    . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
@file_put_contents($logDir . '/card_callback.log', $logLine, FILE_APPEND | LOCK_EX);

$request_id = trim((string)($payload['request_id'] ?? ''));
$status     = isset($payload['status']) ? (int)$payload['status'] : -1;
$value      = (int)($payload['value'] ?? $payload['amount'] ?? 0);  // mệnh giá thực
$declared   = (int)($payload['declared_value'] ?? 0);                // mệnh giá user khai
$message    = (string)($payload['message'] ?? '');

if (!$request_id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing request_id']);
    exit;
}

// Verify signature. Nếu sign invalid → reject 403 để chặn fake callback (attacker
// có thể spoof request_id + status=1 + value lớn → credit ví miễn phí).
// Nếu doithe.vn không gửi sign chuẩn ở 1 vài case → dùng IP whitelist trong
// config (DOITHE_CALLBACK_IPS, comma-separated) làm fallback.
$signValid = verifyDoitheCallback($payload);
if (!$signValid) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ipWhitelist = defined('DOITHE_CALLBACK_IPS') ? array_map('trim', explode(',', DOITHE_CALLBACK_IPS)) : [];
    $ipAllowed = $ip !== '' && in_array($ip, $ipWhitelist, true);
    @file_put_contents($logDir . '/card_callback.log',
        date('Y-m-d H:i:s') . " WARN sign invalid for request_id=$request_id ip=$ip ip_allowed=" . ($ipAllowed?'1':'0') . "\n",
        FILE_APPEND | LOCK_EX);
    if (!$ipAllowed) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'sign invalid']);
        exit;
    }
}

try {
    $db = getDB();

    // Match topup theo provider_request_id
    $stmt = $db->prepare("SELECT * FROM topup_requests WHERE provider_request_id=? AND method='card' LIMIT 1");
    $stmt->execute([$request_id]);
    $topup = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$topup) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'topup not found', 'request_id' => $request_id]);
        exit;
    }

    // Idempotent — nếu đã approved/rejected rồi thì trả OK luôn (provider có thể retry).
    if ($topup['status'] !== 'pending') {
        echo json_encode(['ok' => true, 'already' => $topup['status']]);
        exit;
    }

    if ($status === 1 || $status === 2) {
        // Thành công. Cộng vào ví theo value thực × rate của telco từ doithe.vn.
        $telco = strtoupper((string)($topup['card_telco'] ?? ''));
        [$rate, $multiplier] = cardRateForTelco($telco);
        if ($multiplier < 1.0) $multiplier = 1.0; // safety
        $credit = (int)round($value / $multiplier);

        $note = $signValid ? '' : 'sign_invalid';
        if ($status === 2 && $declared > 0 && $value !== $declared) {
            $note .= ($note ? '; ' : '') . "wrong_value declared=$declared real=$value";
        }
        if ($note) {
            $db->prepare("UPDATE topup_requests SET note=? WHERE id=?")
               ->execute([$note, $topup['id']]);
        }

        $r = topupApprove(
            $db,
            (int)$topup['id'],
            (float)$credit,
            (string)($payload['trans_id'] ?? $payload['provider_trans_id'] ?? ''),
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        echo json_encode([
            'ok' => true,
            'credited' => $credit,
            'balance_after' => $r['balance_after'] ?? null,
            'sign_valid' => $signValid,
        ]);
        exit;
    }

    if ($status === 3 || $status === 4) {
        topupReject(
            $db,
            (int)$topup['id'],
            'doithe.vn từ chối status=' . $status . ($message ? ': ' . $message : ''),
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
        echo json_encode(['ok' => true, 'rejected' => true, 'status' => $status]);
        exit;
    }

    // status khác (vd 99 pending lặp lại) — chỉ log, không thay đổi state.
    echo json_encode(['ok' => true, 'no_action' => true, 'status' => $status]);
} catch (Throwable $e) {
    @file_put_contents($logDir . '/card_callback.log',
        date('Y-m-d H:i:s') . " ERROR " . $e->getMessage() . "\n",
        FILE_APPEND | LOCK_EX);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'internal']);
}
