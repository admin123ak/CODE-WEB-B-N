<?php
/**
 * Shared helper to approve a paid order atomically.
 *
 * Tách ra từ mbbank_poll.php để cả MBBank lẫn Crypto (Binance USDT) reuse.
 * Giữ nguyên 100% logic atomic + telegram notify gốc cho MBBank.
 *
 * @param PDO    $db         DB handle
 * @param string $orderCode  Order code (ORDxxxx)
 * @param float  $amount     Số tiền nhận (VND cho MBBank, USDT cho crypto - dùng để log/notify)
 * @param string $txHash     Hash của tx (để UPDATE bank_transactions sau approve)
 * @param string $approvedBy 'mbbank_api' | 'binance_trc20'
 * @param array  $opts       [
 *     'admin_label'   => 'MBBANK' | 'BINANCE USDT'   (text trên message admin)
 *     'amount_format' => callable(float $amt): string  (vd VND format vs USDT format)
 *     'extra_user_note' => string (text optional thêm vào msg user — vd cảnh báo mạng)
 * ]
 * @return array ['status' => 'approved'|'ignored'|'error', 'note' => string]
 */
function approvePaidOrder(
    PDO $db,
    string $orderCode,
    float $amount,
    string $txHash,
    string $approvedBy = 'mbbank_api',
    array $opts = []
) {
    $adminLabel   = $opts['admin_label']   ?? 'MBBANK';
    $amountFormat = $opts['amount_format'] ?? function ($amt) {
        return number_format((float)$amt, 0, ',', '.') . 'đ';
    };
    $extraUserNote = $opts['extra_user_note'] ?? '';

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

    // Với MBBank: so sánh amount VND theo order.amount.
    // Với Binance: $amount là USDT, không thể so trực tiếp với order.amount (VND).
    //   → Phía crypto_poll.php đã match theo orders.crypto_amount trước khi gọi hàm này,
    //     nên ở đây chỉ check cho MBBank (approvedBy='mbbank_api').
    if ($approvedBy === 'mbbank_api' && (float)$order['amount'] > $amount) {
        return ['status' => 'ignored', 'note' => 'Số tiền nhận nhỏ hơn đơn'];
    }

    $db->beginTransaction();
    try {
        $keyStmt = $db->prepare("SELECT id, key_code FROM `keys` WHERE order_id = ? AND status = 'pending' LIMIT 1 FOR UPDATE");
        $keyStmt->execute([$order['id']]);
        $key = $keyStmt->fetch();
        if (!$key) throw new Exception('Không tìm thấy key pending');

        $start  = date('Y-m-d H:i:s');
        $expire = date('Y-m-d H:i:s', strtotime('+' . ((int)$order['days']) . ' days'));
        $upKey  = $db->prepare("UPDATE `keys` SET status='active', start_at=?, expire_at=? WHERE id=? AND status='pending'");
        $upKey->execute([$start, $expire, $key['id']]);
        if ($upKey->rowCount() !== 1) throw new Exception('Key đã bị thay đổi trạng thái (race condition)');

        $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by=? WHERE id=? AND status='pending'");
        $upOrder->execute([$approvedBy, $order['id']]);
        if ($upOrder->rowCount() !== 1) throw new Exception('Order đã được xử lý bởi process khác');

        $db->prepare("UPDATE bank_transactions SET status='approved', processed_at=NOW(), note=? WHERE tx_hash=?")
           ->execute(['Auto approved ' . $orderCode, $txHash]);

        $db->commit();

        // Gửi Telegram SAU commit
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
        if ($extraUserNote !== '') {
            $userMsg .= "\n\n" . $extraUserNote;
        }
        sendTelegram($order['telegram_id'], $userMsg);

        sendTelegram(ADMIN_CHAT_ID,
            "🤖 <b>AUTO {$adminLabel} DUYỆT ĐƠN</b>\n" .
            "#{$orderCode}\n" .
            "💰 Nhận: " . $amountFormat($amount) . "\n" .
            "🔑 <code>{$key['key_code']}</code>");

        return ['status' => 'approved', 'note' => 'OK'];
    } catch (Exception $e) {
        $db->rollBack();
        return ['status' => 'error', 'note' => $e->getMessage()];
    }
}
