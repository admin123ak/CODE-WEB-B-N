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
require_once __DIR__ . '/../backend/lib/order_approval.php';
require_once __DIR__ . '/../backend/lib/crypto_helpers.php';
require_once __DIR__ . '/../backend/lib/topup_helpers.php';
require_once __DIR__ . '/../backend/lib/balance_helpers.php';

// =============================================
// SCRIPT — Poll TronGrid để auto-approve order Binance USDT TRC20
// Mirror logic của mbbank_poll.php (status file + alert + lock).
// =============================================

$cryptoPollStartedAt = microtime(true);
$cryptoPollSource    = (PHP_SAPI === 'cli') ? 'cron' : (isset($_GET['src']) && $_GET['src'] === 'admin' ? 'admin' : 'http');

function writeCryptoPollStatus(array $extra) {
    global $cryptoPollStartedAt, $cryptoPollSource;
    $file = APP_ROOT . '/data/crypto_poll_status.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $payload = array_merge([
        'last_run_at'  => date('c'),
        'duration_ms'  => (int) round((microtime(true) - $cryptoPollStartedAt) * 1000),
        'source'       => $cryptoPollSource,
        'seen_new'     => 0,
        'matched'      => 0,
        'approved'     => 0,
        'skipped'      => false,
        'error'        => null,
    ], $extra);
    @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Alert tới ADMIN_CHAT_ID khi Crypto poll lỗi liên tục.
 * Cùng schema với mbbank_poll alert: 3 lần fail → 1 alert / 30 phút.
 */
function maybeCryptoPollAlert(?string $error): void {
    $file = APP_ROOT . '/data/crypto_poll_alert.json';
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
                    "✅ <b>Crypto Poll (Binance USDT) đã hồi phục</b>\n" .
                    "🕐 " . date('Y-m-d H:i:s') . "\n" .
                    "Sau " . (int)$state['consecutive_failures'] . " lần lỗi liên tục, lần chạy này đã thành công.");
            } catch (Throwable $e) { /* ignore */ }
        }
        $state = ['consecutive_failures' => 0, 'last_alert_at' => null, 'last_error' => null];
    } else {
        if (stripos($error, 'Auto approve disabled') !== false || stripos($error, 'address not configured') !== false) {
            $state['consecutive_failures'] = 0;
            $state['last_error'] = $error;
            @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
            return;
        }
        $state['consecutive_failures'] = (int)$state['consecutive_failures'] + 1;
        $state['last_error'] = substr($error, 0, 200);

        $threshold   = 3;
        $throttleSec = 1800; // 30 phút
        $now         = time();
        $lastAlertTs = !empty($state['last_alert_at']) ? (int)strtotime($state['last_alert_at']) : 0;

        if ($state['consecutive_failures'] >= $threshold && ($now - $lastAlertTs) >= $throttleSec) {
            try {
                $msg = "⚠️ <b>Crypto Poll (Binance USDT) lỗi liên tục</b>\n\n" .
                       "🕐 " . date('Y-m-d H:i:s') . "\n" .
                       "🔁 Số lần lỗi liền: <b>" . (int)$state['consecutive_failures'] . "</b>\n" .
                       "❌ " . htmlspecialchars($state['last_error']) . "\n\n" .
                       "Kiểm tra TRONGRID_API_KEY, USDT_TRC20_ADDRESS, hoặc trạng thái TronGrid.";
                sendTelegram(ADMIN_CHAT_ID, $msg);
                $state['last_alert_at'] = date('c');
            } catch (Throwable $e) { /* ignore */ }
        }
    }

    @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// =============================================
// LOCK
// =============================================
$lockFile = APP_ROOT . '/data/crypto_poll.lock';
if (!is_dir(dirname($lockFile))) @mkdir(dirname($lockFile), 0755, true);
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    writeCryptoPollStatus(['skipped' => true, 'error' => 'previous_run_still_active']);
    http_response_code(200);
    echo json_encode(['success' => true, 'skipped' => true, 'reason' => 'previous_run_still_active']);
    exit;
}
register_shutdown_function(function() use ($lockHandle) {
    if ($lockHandle) {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
});

// =============================================
// AUTH
// =============================================
if (PHP_SAPI !== 'cli') {
    $secret = $_GET['secret'] ?? '';
    if (!defined('CRYPTO_POLL_SECRET') || !hash_equals(CRYPTO_POLL_SECRET, $secret)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function cryptoLog($msg) {
    if (PHP_SAPI === 'cli') echo $msg . PHP_EOL;
}

/**
 * Fetch danh sách giao dịch TRC20 đến địa chỉ USDT_TRC20_ADDRESS từ TronGrid.
 * Endpoint: /v1/accounts/{addr}/transactions/trc20
 *   - only_to=true: chỉ tx incoming
 *   - contract_address=USDT_TRC20_CONTRACT: chỉ USDT (lọc các token khác)
 *   - limit=50: lấy 50 tx gần nhất, đủ cho cron 1 phút/lần
 */
function fetchCryptoTransactions(): array {
    $addr = defined('USDT_TRC20_ADDRESS') ? USDT_TRC20_ADDRESS : '';
    if ($addr === '') {
        throw new Exception('USDT_TRC20_ADDRESS not configured');
    }
    $url = 'https://api.trongrid.io/v1/accounts/' . urlencode($addr)
         . '/transactions/trc20?limit=50&only_to=true&contract_address=' . urlencode(USDT_TRC20_CONTRACT);

    $headers = ['Accept: application/json', 'User-Agent: HCLOU-Crypto/1.0'];
    if (defined('TRONGRID_API_KEY') && TRONGRID_API_KEY !== '') {
        $headers[] = 'TRON-PRO-API-KEY: ' . TRONGRID_API_KEY;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) throw new Exception('TronGrid curl error: ' . $err);
    if ($code !== 200)   throw new Exception('TronGrid HTTP ' . $code);

    $json = json_decode($body, true);
    if (!is_array($json) || empty($json['success'])) {
        throw new Exception('TronGrid response invalid: ' . substr($body, 0, 200));
    }
    return $json['data'] ?? [];
}

/**
 * Normalize 1 tx từ TronGrid về shape chung.
 * Response shape:
 * {
 *   transaction_id: "abc123...",
 *   block_timestamp: 1700000000000 (ms),
 *   from: "TXfrom...",
 *   to:   "TXto...",
 *   value: "1000000" (raw, chia 1e{token_decimals}),
 *   token_info: { decimals: 6, symbol: "USDT", address: "TR7N..." }
 * }
 */
function normalizeCryptoTx(array $tx): array {
    $txid     = (string)($tx['transaction_id'] ?? '');
    $tsMs     = (int)($tx['block_timestamp'] ?? 0);
    $from     = (string)($tx['from'] ?? '');
    $to       = (string)($tx['to']   ?? '');
    $rawValue = (string)($tx['value'] ?? '0');
    $decimals = (int)($tx['token_info']['decimals'] ?? 6);

    // Convert raw string → float USDT (chia 10^decimals). Dùng bcdiv để tránh
    // float precision khi value rất lớn — fallback nếu bcmath không có.
    if (function_exists('bcdiv')) {
        $amount = (float)bcdiv($rawValue, bcpow('10', (string)$decimals), 6);
    } else {
        $amount = (float)$rawValue / pow(10, $decimals);
    }

    $date = $tsMs > 0 ? date('Y-m-d H:i:s', (int)($tsMs / 1000)) : date('Y-m-d H:i:s');
    return [$txid, $date, $from, $to, round($amount, 6)];
}

// =============================================
// MAIN
// =============================================
try {
    if (!defined('CRYPTO_AUTO_APPROVE_ENABLED') || !CRYPTO_AUTO_APPROVE_ENABLED) {
        throw new Exception('Auto approve disabled');
    }
    if (!defined('USDT_TRC20_ADDRESS') || USDT_TRC20_ADDRESS === '') {
        throw new Exception('USDT_TRC20_ADDRESS not configured');
    }

    $db = getDB();
    // bank_transactions table được mbbank_poll lo (CREATE IF NOT EXISTS).
    // Nếu chưa có (hosting mới chưa chạy MBBank), tạo schema kèm column 'source'.
    $db->exec("CREATE TABLE IF NOT EXISTS bank_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tx_hash CHAR(64) NOT NULL UNIQUE,
        tx_date DATETIME NOT NULL,
        amount DECIMAL(18,6) NOT NULL,
        source ENUM('mbbank','binance') NOT NULL DEFAULT 'mbbank',
        description TEXT NOT NULL,
        order_code VARCHAR(50) DEFAULT NULL,
        status ENUM('seen','matched','approved','ignored','error') DEFAULT 'seen',
        note TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_order_code (order_code),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at),
        INDEX idx_source (source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $txs = fetchCryptoTransactions();
    $seen = $approved = $matched = 0;
    $myAddr = strtolower(USDT_TRC20_ADDRESS);

    foreach ($txs as $tx) {
        [$txid, $date, $from, $to, $amount] = normalizeCryptoTx($tx);
        if ($txid === '' || $amount <= 0) continue;

        // Double-check: chỉ nhận tx ĐẾN địa chỉ của mình.
        if (strtolower($to) !== $myAddr) continue;

        // Hash dedup: prefix 'crypto:' để không clash với MBBank tx hash.
        $hash = hash('sha256', 'crypto:' . $txid);

        $exist = $db->prepare("SELECT id FROM bank_transactions WHERE tx_hash=? LIMIT 1");
        $exist->execute([$hash]);
        if ($exist->fetch()) continue;

        $amtFmt = number_format($amount, 6, '.', '');

        // Match ORDER (mua key/acc) trước
        $orderStmt = $db->prepare("SELECT id, order_code FROM orders
            WHERE crypto_amount = CAST(? AS DECIMAL(18,6))
              AND status = 'pending'
              AND payment_method = 'binance'
            ORDER BY id DESC LIMIT 1");
        $orderStmt->execute([$amtFmt]);
        $orderRow = $orderStmt->fetch();
        $orderCode = $orderRow['order_code'] ?? null;

        // Match TOPUP (nạp ví) nếu không phải order
        $topupRow = null;
        if (!$orderCode) {
            $tu = $db->prepare("SELECT id, user_id, amount_requested, usdt_vnd_rate FROM topup_requests
                WHERE crypto_amount = CAST(? AS DECIMAL(18,6))
                  AND status='pending' AND method='binance'
                ORDER BY id DESC LIMIT 1");
            $tu->execute([$amtFmt]);
            $topupRow = $tu->fetch();
        }

        $desc = "TRC20 from {$from} | txid={$txid}";
        $matchCode = $orderCode ?: ($topupRow ? 'TOPUP#' . $topupRow['id'] : null);

        $ins = $db->prepare("INSERT IGNORE INTO bank_transactions
            (tx_hash, tx_date, amount, source, description, order_code, status)
            VALUES (?,?,?, 'binance', ?, ?, ?)");
        $ins->execute([$hash, $date, $amount, $desc, $matchCode, $matchCode ? 'matched' : 'seen']);
        if ($ins->rowCount() === 0) continue;
        $seen++;

        if ($orderCode) {
            $matched++;
            $extraNote = "🔗 <b>Mạng:</b> TRC20 (TRON)\n💵 <b>USDT nhận:</b> " . rtrim(rtrim($amtFmt, '0'), '.');
            $res = approvePaidOrder(
                $db, $orderCode, $amount, $hash, 'binance_trc20',
                [
                    'admin_label'   => 'BINANCE USDT',
                    'amount_format' => function ($amt) {
                        return rtrim(rtrim(number_format((float)$amt, 6, '.', ''), '0'), '.') . ' USDT';
                    },
                    'extra_user_note' => $extraNote,
                ]
            );
            if ($res['status'] === 'approved') $approved++;
            else $db->prepare("UPDATE bank_transactions SET status=?, processed_at=NOW(), note=? WHERE tx_hash=?")
                    ->execute([$res['status'], $res['note'], $hash]);
        } elseif ($topupRow) {
            $matched++;
            // Convert USDT nhận → VND theo rate đã lock khi tạo topup
            $vndCredited = (float)$topupRow['amount_requested'];
            try {
                topupApprove($db, (int)$topupRow['id'], $vndCredited, 'binance_' . substr($txid, 0, 12));
                $db->prepare("UPDATE bank_transactions SET status='approved', processed_at=NOW(), note=? WHERE tx_hash=?")
                   ->execute(['Topup #' . $topupRow['id'] . ' +' . number_format($vndCredited) . 'đ vào ví', $hash]);
                $approved++;
            } catch (Throwable $e) {
                $db->prepare("UPDATE bank_transactions SET status='error', processed_at=NOW(), note=? WHERE tx_hash=?")
                   ->execute(['topupApprove fail: ' . $e->getMessage(), $hash]);
            }
        }
    }

    $out = ['success' => true, 'seen_new' => $seen, 'matched' => $matched, 'approved' => $approved];
    writeCryptoPollStatus(['seen_new' => $seen, 'matched' => $matched, 'approved' => $approved]);
    maybeCryptoPollAlert(null);
    if (PHP_SAPI === 'cli') cryptoLog(json_encode($out, JSON_UNESCAPED_UNICODE));
    else jsonResponse($out);
} catch (Exception $e) {
    error_log('[CRYPTO_POLL] ' . $e->getMessage());
    writeCryptoPollStatus(['error' => $e->getMessage()]);
    maybeCryptoPollAlert($e->getMessage());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); }
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
