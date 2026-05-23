<?php
/**
 * Helper thao tác `topup_requests` — yêu cầu nạp tiền vào ví.
 *
 * 3 method:
 *   - mbbank   : sinh unique_code (vd NAP1A2B3C). User chuyển khoản kèm code.
 *                Cron mbbank_poll match description → topupApprove.
 *   - binance  : sinh crypto_amount duy nhất (số lẻ thập phân theo topup_id).
 *                Cron crypto_poll match received amount → topupApprove.
 *   - card     : gửi serial+code lên doithe.vn → status 99 (pending) lưu DB.
 *                doithe.vn callback về card_callback.php → topupApprove.
 *
 * topupApprove() là entry point chung — bất kỳ method nào duyệt xong đều
 * gọi function này. Bên trong gọi balanceCredit() atomic.
 */

require_once __DIR__ . '/balance_helpers.php';

/**
 * Tạo topup_request mới. Đã validate input.
 * KHÔNG gọi external API ở đây — caller chịu (vd card flow tự call doithe).
 *
 * @return array ['id' => int, 'unique_code' => ?string, 'crypto_amount' => ?float, 'amount_requested' => float]
 */
function topupCreateRequest(
    PDO $db,
    int $user_id,
    string $method,           // mbbank | binance | card
    float $amount_requested,  // VND (với card = face_value)
    array $extra = []         // crypto_amount, usdt_vnd_rate, card_*, provider_request_id, note
): array {
    if (!in_array($method, ['mbbank','binance','card'], true)) {
        throw new InvalidArgumentException('topupCreateRequest: method invalid: ' . $method);
    }
    if ($amount_requested <= 0) {
        throw new InvalidArgumentException('topupCreateRequest: amount phải > 0');
    }

    $unique_code = null;
    if ($method === 'mbbank') {
        // Generate NAP + 7 alphanumeric. Loop max 5 lần phòng collision.
        for ($i = 0; $i < 5; $i++) {
            $code = 'NAP' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
            $exists = $db->prepare("SELECT 1 FROM topup_requests WHERE unique_code=? LIMIT 1");
            $exists->execute([$code]);
            if (!$exists->fetchColumn()) { $unique_code = $code; break; }
        }
        if (!$unique_code) throw new RuntimeException('Không tạo được unique_code (5 collision)');
    }

    $db->prepare("INSERT INTO topup_requests
        (user_id, method, amount_requested, status, unique_code, crypto_amount, usdt_vnd_rate,
         card_telco, card_face_value, card_serial, card_code,
         provider_request_id, note, expires_at, created_at)
        VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())")
       ->execute([
           $user_id, $method, $amount_requested, $unique_code,
           $extra['crypto_amount']   ?? null,
           $extra['usdt_vnd_rate']   ?? null,
           $extra['card_telco']      ?? null,
           $extra['card_face_value'] ?? null,
           $extra['card_serial']     ?? null,
           $extra['card_code']       ?? null,
           $extra['provider_request_id'] ?? null,
           $extra['note']            ?? null,
       ]);
    $id = (int)$db->lastInsertId();

    return [
        'id'               => $id,
        'unique_code'      => $unique_code,
        'crypto_amount'    => $extra['crypto_amount'] ?? null,
        'amount_requested' => $amount_requested,
    ];
}

/**
 * Duyệt topup → cộng tiền vào ví user. Idempotent: gọi 2 lần không double-credit.
 *
 * @param int    $topup_id
 * @param float  $amount_credited  VND thực cộng vào ví (sau markup nếu card)
 * @param string $provider_trans_id (optional)
 * @param string $provider_response (optional, JSON)
 * @return array ['balance_after' => float, 'log_id' => int]
 * @throws RuntimeException nếu topup không tồn tại
 */
function topupApprove(
    PDO $db,
    int $topup_id,
    float $amount_credited,
    string $provider_trans_id = '',
    string $provider_response = ''
): array {
    if ($amount_credited <= 0) {
        throw new InvalidArgumentException('topupApprove: amount_credited phải > 0');
    }

    $row = $db->prepare("SELECT * FROM topup_requests WHERE id=? FOR UPDATE");
    // FOR UPDATE cần trong transaction — balanceCredit cũng begin tx, nhưng nó begin sau.
    // Để tránh nested, ta begin tx ở đây, lock row, check idempotent, commit trước
    // khi gọi balanceCredit (balanceCredit có tx riêng).
    $db->beginTransaction();
    try {
        $row = $db->prepare("SELECT * FROM topup_requests WHERE id=? FOR UPDATE");
        $row->execute([$topup_id]);
        $t = $row->fetch(PDO::FETCH_ASSOC);
        if (!$t) {
            $db->rollBack();
            throw new RuntimeException('Topup không tồn tại: ' . $topup_id);
        }
        // Idempotent guard
        if ($t['status'] === 'approved') {
            $db->rollBack();
            return ['balance_after' => 0, 'log_id' => 0, 'already' => true];
        }
        if ($t['status'] !== 'pending') {
            $db->rollBack();
            throw new RuntimeException('Topup không ở trạng thái pending (đang: ' . $t['status'] . ')');
        }

        $db->prepare("UPDATE topup_requests SET status='approved', amount_credited=?,
                      provider_trans_id=?, provider_response=COALESCE(?, provider_response), processed_at=NOW()
                      WHERE id=?")
           ->execute([$amount_credited, $provider_trans_id ?: null, $provider_response ?: null, $topup_id]);

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }

    // Credit balance (atomic, riêng tx)
    $res = balanceCredit(
        $db,
        (int)$t['user_id'],
        (float)$amount_credited,
        'topup',
        'topup_request',
        $topup_id,
        'Method: ' . $t['method'] . ($provider_trans_id ? ' tx=' . $provider_trans_id : '')
    );

    return [
        'balance_after' => $res['balance_after'],
        'log_id'        => $res['log_id'],
        'user_id'       => (int)$t['user_id'],
        'method'        => $t['method'],
    ];
}

/**
 * Reject topup (vd thẻ sai, hết hạn, doithe trả lỗi).
 */
function topupReject(PDO $db, int $topup_id, string $note = '', string $provider_response = ''): void {
    $db->prepare("UPDATE topup_requests SET status='rejected', note=COALESCE(?, note),
                  provider_response=COALESCE(?, provider_response), processed_at=NOW()
                  WHERE id=? AND status='pending'")
       ->execute([$note ?: null, $provider_response ?: null, $topup_id]);
}

/**
 * Gọi doithe.vn /chargingws/v2 — nạp thẻ.
 *
 * doithe.vn dùng pattern phổ biến của VN card providers:
 *   POST body: telco, code, serial, amount, request_id, partner_id, sign, command='charging'
 *   sign = md5(partner_key + code + serial)
 *
 * Response sync: { status: 99=pending, 1=success, 2=wrong_amount, 3=fail, 4=error, ...,
 *                  message, request_id }
 *
 * Lưu ý: pattern md5 sign có thể khác chút theo từng provider. Khi user có docs
 * thật từ doithe.vn merchant panel, đối chiếu lại function buildDoitheSign().
 *
 * @return array [ok=>bool, status=>int, message=>string, raw=>string]
 */
function callDoitheCharge(string $request_id, string $telco, int $face_value, string $serial, string $code): array {
    if (!defined('DOITHE_API_URL') || DOITHE_API_URL === '' ||
        !defined('DOITHE_PARTNER_ID') || DOITHE_PARTNER_ID === '' ||
        !defined('DOITHE_PARTNER_KEY') || DOITHE_PARTNER_KEY === '') {
        return ['ok' => false, 'status' => 0, 'message' => 'doithe.vn chưa cấu hình', 'raw' => ''];
    }

    $sign = md5(DOITHE_PARTNER_KEY . $code . $serial);
    $body = http_build_query([
        'telco'      => $telco,
        'code'       => $code,
        'serial'     => $serial,
        'amount'     => $face_value,
        'request_id' => $request_id,
        'partner_id' => DOITHE_PARTNER_ID,
        'sign'       => $sign,
        'command'    => 'charging',
    ]);

    $ch = curl_init(DOITHE_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'status' => 0, 'message' => 'cURL error: ' . $err, 'raw' => ''];
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return ['ok' => false, 'status' => 0, 'message' => 'Response không phải JSON', 'raw' => $raw];
    }

    $status = isset($json['status']) ? (int)$json['status'] : 0;
    // 99=pending, 1=success, 2=wrong_amount, 3=fail, 4=error
    $isPending = ($status === 99);
    return [
        'ok'      => ($status === 1 || $status === 2 || $isPending),
        'status'  => $status,
        'message' => $json['message'] ?? '',
        'raw'     => $raw,
    ];
}

/**
 * Verify callback signature từ doithe.vn.
 * Pattern phổ biến: md5(partner_key + status + request_id + value)
 * (sửa lại khi có docs chính thức).
 */
function verifyDoitheCallback(array $payload): bool {
    if (!defined('DOITHE_PARTNER_KEY') || DOITHE_PARTNER_KEY === '') return false;
    $sign = $payload['sign'] ?? $payload['signature'] ?? '';
    if (!$sign) return false;
    $status = (string)($payload['status'] ?? '');
    $request_id = (string)($payload['request_id'] ?? '');
    $value = (string)($payload['value'] ?? $payload['amount'] ?? '');
    $expect = md5(DOITHE_PARTNER_KEY . $status . $request_id . $value);
    return hash_equals($expect, strtolower($sign));
}
