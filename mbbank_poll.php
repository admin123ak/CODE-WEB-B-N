<?php
require_once __DIR__ . '/config.php';

// =============================================
// SCRIPT LOCK - tránh chạy chồng cron jobs
// =============================================
$lockFile = APP_ROOT . '/data/mbbank_poll.lock';
if (!is_dir(dirname($lockFile))) @mkdir(dirname($lockFile), 0755, true);
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
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

/**
 * Approve order với atomic check.
 * - UPDATE orders WHERE status='pending' kiểm tra rowCount = 1
 * - Nếu 0 row affected → có race condition, rollback và return ignored
 */
function approvePaidOrder(PDO $db, string $orderCode, float $amount, string $txHash) {
    $stmt = $db->prepare("SELECT o.*, p.days, p.key_type, p.price, g.name AS game_name, g.package_name, u.telegram_id
        FROM orders o
        JOIN packages p ON o.package_id = p.id
        JOIN games g    ON o.game_id    = g.id
        JOIN users u    ON o.user_id    = u.id
        WHERE o.order_code = ? AND o.status = 'pending'
        LIMIT 1");
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();
    if (!$order) return ['status' => 'ignored', 'note' => 'Không tìm thấy đơn pending'];
    if ((float)$order['amount'] > $amount) return ['status' => 'ignored', 'note' => 'Số tiền nhận nhỏ hơn đơn'];

    $db->beginTransaction();
    try {
        // 1) Lock key pending của đơn này
        $keyStmt = $db->prepare("SELECT id, key_code FROM `keys` WHERE order_id = ? AND status = 'pending' LIMIT 1 FOR UPDATE");
        $keyStmt->execute([$order['id']]);
        $key = $keyStmt->fetch();
        if (!$key) throw new Exception('Không tìm thấy key pending');

        // 2) Update key active (atomic - dùng cả status='pending' để chắc chắn)
        $start  = date('Y-m-d H:i:s');
        $expire = date('Y-m-d H:i:s', strtotime('+' . ((int)$order['days']) . ' days'));
        $upKey  = $db->prepare("UPDATE `keys` SET status='active', start_at=?, expire_at=? WHERE id=? AND status='pending'");
        $upKey->execute([$start, $expire, $key['id']]);
        if ($upKey->rowCount() !== 1) throw new Exception('Key đã bị thay đổi trạng thái (race condition)');

        // 3) Update order - atomic check status pending
        $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by=? WHERE id=? AND status='pending'");
        $upOrder->execute(['mbbank_api', $order['id']]);
        if ($upOrder->rowCount() !== 1) throw new Exception('Order đã được xử lý bởi process khác');

        // 4) Cập nhật tx
        $db->prepare("UPDATE bank_transactions SET status='approved', processed_at=NOW(), note=? WHERE tx_hash=?")
           ->execute(['Auto approved ' . $orderCode, $txHash]);

        $db->commit();

        // Send Telegram AFTER commit
        $shortOrder  = preg_replace('/^ORD/i', '', $orderCode);
        $packageName = $order['package_name'] ?: $order['game_name'];
        $type        = strtoupper($order['key_type'] ?: 'Normal') === 'VIP' ? 'VIP' : 'Normal';
        $userMsg = "✅ <b>Key Purchase Successful!</b>\n\n" .
            "• Order code : <code>{$shortOrder}</code>\n" .
            "• License : <code>{$key['key_code']}</code>\n" .
            "• Package : <code>{$packageName}</code>\n" .
            "• Type : {$type} — {$order['days']} days / " . number_format((float)$order['price'], 0, ',', '.') . "đ\n\n" .
            "Duration will start when license login.\n\n" .
            "<b>Lưu ý:</b> để sử dụng an toàn, không dùng song song mod khác hoặc ứng dụng lạ.";
        sendTelegram($order['telegram_id'], $userMsg);
        sendTelegram(ADMIN_CHAT_ID,
            "🤖 <b>AUTO MBBANK DUYỆT ĐƠN</b>\n#{$orderCode}\n💰 Nhận: " . number_format($amount, 0, ',', '.') . "đ\n🔑 <code>{$key['key_code']}</code>");

        return ['status' => 'approved', 'note' => 'OK'];
    } catch (Exception $e) {
        $db->rollBack();
        return ['status' => 'error', 'note' => $e->getMessage()];
    }
}

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
    if (PHP_SAPI === 'cli') mbLog(json_encode($out, JSON_UNESCAPED_UNICODE));
    else jsonResponse($out);
} catch (Exception $e) {
    error_log('[MBBANK_POLL] ' . $e->getMessage());
    if (PHP_SAPI === 'cli') { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); }
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
