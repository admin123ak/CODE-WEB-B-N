<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../backend/lib/order_approval.php';

// =============================================
// SCRIPT LOCK - tránh chạy chồng cron jobs
// =============================================
$mbPollStartedAt = microtime(true);
$mbPollSource    = (PHP_SAPI === 'cli') ? 'cron' : (isset($_GET['src']) && $_GET['src'] === 'admin' ? 'admin' : 'http');

// Ghi status file để admin panel hiển thị quan sát.
// Bỏ qua trên Windows-style FS nếu lỗi - không ảnh hưởng poll logic.
function writeMBPollStatus(array $extra) {
    global $mbPollStartedAt, $mbPollSource;
    $file = APP_ROOT . '/data/mbbank_poll_status.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $payload = array_merge([
        'last_run_at'  => date('c'),
        'duration_ms'  => (int) round((microtime(true) - $mbPollStartedAt) * 1000),
        'source'       => $mbPollSource,
        'seen_new'     => 0,
        'matched'      => 0,
        'approved'     => 0,
        'skipped'      => false,
        'error'        => null,
    ], $extra);
    @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Alert tới ADMIN_CHAT_ID khi MBBank poll lỗi liên tục.
 * - Counter persistent trong data/mbbank_poll_alert.json
 * - Ngưỡng: 3 lần lỗi liền nhau
 * - Throttle: 30 phút giữa các alert (tránh spam)
 * - Bỏ qua nếu error là "Auto approve disabled" (admin chủ động tắt)
 * - Reset counter khi có lần chạy success
 */
function maybeMBPollAlert(?string $error): void {
    $file = APP_ROOT . '/data/mbbank_poll_alert.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $state = ['consecutive_failures' => 0, 'last_alert_at' => null, 'last_error' => null];
    if (is_file($file)) {
        $prev = json_decode((string)@file_get_contents($file), true);
        if (is_array($prev)) $state = array_merge($state, $prev);
    }

    if ($error === null) {
        // Success — recovery: notify admin nếu trước đó đã từng cảnh báo
        if (!empty($state['last_alert_at']) && (int)$state['consecutive_failures'] >= 3) {
            try {
                sendTelegram(ADMIN_CHAT_ID,
                    "✅ <b>MBBank Poll đã hồi phục</b>\n" .
                    "🕐 " . date('Y-m-d H:i:s') . "\n" .
                    "Sau " . (int)$state['consecutive_failures'] . " lần lỗi liên tục, lần chạy này đã thành công.");
            } catch (Throwable $e) { /* ignore */ }
        }
        $state = ['consecutive_failures' => 0, 'last_alert_at' => null, 'last_error' => null];
    } else {
        // Admin chủ động tắt thì không alert
        if (stripos($error, 'Auto approve disabled') !== false) {
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
                $msg = "⚠️ <b>MBBank Poll lỗi liên tục</b>\n\n" .
                       "🕐 " . date('Y-m-d H:i:s') . "\n" .
                       "🔁 Số lần lỗi liền: <b>" . (int)$state['consecutive_failures'] . "</b>\n" .
                       "❌ " . htmlspecialchars($state['last_error']) . "\n\n" .
                       "Kiểm tra Queenvps API key, BOT_TOKEN, hoặc trạng thái MBBank Auto-bank.";
                sendTelegram(ADMIN_CHAT_ID, $msg);
                $state['last_alert_at'] = date('c');
            } catch (Throwable $e) { /* ignore - không để telegram fail làm hỏng poll */ }
        }
    }

    @file_put_contents($file, json_encode($state, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$lockFile = APP_ROOT . '/data/mbbank_poll.lock';
if (!is_dir(dirname($lockFile))) @mkdir(dirname($lockFile), 0755, true);
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    writeMBPollStatus(['skipped' => true, 'error' => 'previous_run_still_active']);
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
    if (!defined('MBBANK_POLL_SECRET') || !hash_equals(MBBANK_POLL_SECRET, $secret)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function mbLog($msg) {
    if (PHP_SAPI === 'cli') echo $msg . PHP_EOL;
}

function ensureMBBankTables(PDO $db) {
    // Schema mới dùng DATETIME, schema cũ VARCHAR. CREATE IF NOT EXISTS không alter cột.
    $db->exec("CREATE TABLE IF NOT EXISTS bank_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tx_hash CHAR(64) NOT NULL UNIQUE,
        tx_date DATETIME NOT NULL,
        amount DECIMAL(12,0) NOT NULL,
        description TEXT NOT NULL,
        order_code VARCHAR(50) DEFAULT NULL,
        status ENUM('seen','matched','approved','ignored','error') DEFAULT 'seen',
        note TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_order_code (order_code),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function fetchMBBankTransactions() {
    $res = httpJsonRequest(MBBANK_HISTORY_API_URL, 'GET', [
        'Accept: application/json',
        'User-Agent: HCLOU-AutoBank/2.0',
    ]);
    if (!$res['ok'] || !is_array($res['json'])) {
        throw new Exception('API MBBANK lỗi HTTP ' . $res['code']);
    }
    $json = $res['json'];
    if (empty($json['success'])) {
        throw new Exception('API MBBANK trả về success=false');
    }
    $mbData = $json['data']['mb_data'] ?? null;
    if (!is_array($mbData) || empty($mbData['transactions'])) {
        throw new Exception('API MBBANK không có transactions hợp lệ');
    }
    return $mbData['transactions'];
}

function normalizeMBBankTx(array $tx) {
    $date   = (string)($tx['transaction_date'] ?? $tx['formatted_date'] ?? '');
    $desc   = trim((string)($tx['description'] ?? ''));
    $credit = (float)($tx['credit_amount'] ?? 0);
    $amount = $credit > 0 ? $credit : 0;
    return [$date, $amount, $desc];
}

// Chuyển date MBBank (dd/mm/yyyy HH:ii:ss hoặc ISO) -> Y-m-d H:i:s
function normalizeDateForDb($raw) {
    if (preg_match('~^(\d{2})/(\d{2})/(\d{4})\s+(\d{2}):(\d{2}):(\d{2})$~', $raw, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}:{$m[5]}:{$m[6]}";
    }
    $ts = strtotime($raw);
    return $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
}

// approvePaidOrder() đã chuyển sang lib/order_approval.php để chia sẻ với crypto_poll.php

try {
    if (!defined('MBBANK_AUTO_APPROVE_ENABLED') || !MBBANK_AUTO_APPROVE_ENABLED) {
        throw new Exception('Auto approve disabled');
    }
    $db = getDB();
    ensureMBBankTables($db);
    $txs = fetchMBBankTransactions();
    $seen = $approved = $matched = 0;

    foreach ($txs as $tx) {
        [$rawDate, $amount, $desc] = normalizeMBBankTx($tx);
        if ($amount <= 0 || $desc === '') continue;

        $date = normalizeDateForDb($rawDate);
        $hash = hash('sha256', $rawDate . '|' . $amount . '|' . $desc);
        preg_match('/\b(ORD[0-9A-Z]+)\b/i', $desc, $m);
        $orderCode = strtoupper($m[1] ?? '');

        // Bỏ qua nếu cùng order_code + amount đã approved (MBBank đôi khi gửi trùng)
        if ($orderCode) {
            $dupStmt = $db->prepare("SELECT id FROM bank_transactions WHERE order_code=? AND amount=? AND status='approved' LIMIT 1");
            $dupStmt->execute([$orderCode, $amount]);
            if ($dupStmt->fetchColumn()) continue;
        }

        // Đã xử lý hash này?
        $existStmt = $db->prepare("SELECT id FROM bank_transactions WHERE tx_hash=? LIMIT 1");
        $existStmt->execute([$hash]);
        if ($existStmt->fetch()) continue;

        $ins = $db->prepare("INSERT IGNORE INTO bank_transactions (tx_hash, tx_date, amount, description, order_code, status) VALUES (?,?,?,?,?,?)");
        $ins->execute([$hash, $date, $amount, $desc, $orderCode ?: null, $orderCode ? 'matched' : 'seen']);
        if ($ins->rowCount() === 0) continue;
        $seen++;

        if ($orderCode) {
            $matched++;
            $res = approvePaidOrder($db, $orderCode, $amount, $hash);
            if ($res['status'] === 'approved') {
                $approved++;
            } else {
                $db->prepare("UPDATE bank_transactions SET status=?, processed_at=NOW(), note=? WHERE tx_hash=?")
                   ->execute([$res['status'], $res['note'], $hash]);
            }
        }
    }

    $out = ['success' => true, 'seen_new' => $seen, 'matched' => $matched, 'approved' => $approved];
    writeMBPollStatus(['seen_new' => $seen, 'matched' => $matched, 'approved' => $approved]);
    maybeMBPollAlert(null);
    if (PHP_SAPI === 'cli') mbLog(json_encode($out, JSON_UNESCAPED_UNICODE));
    else jsonResponse($out);
} catch (Exception $e) {
    error_log('[MBBANK_POLL] ' . $e->getMessage());
    writeMBPollStatus(['error' => $e->getMessage()]);
    maybeMBPollAlert($e->getMessage());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); }
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
