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
 * Shared helper to approve a paid order atomically.
 *
 * Tách ra từ cron/mbbank_poll.php để cả MBBank lẫn Crypto (Binance USDT) reuse.
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

    // Đọc đơn hàng. Nếu order_type='account', JOIN với account_types thay vì packages.
    $order = null;
    $orderType = null;

    // Bước 1: lấy order_type
    $typeStmt = $db->prepare("SELECT order_type FROM orders WHERE order_code = ? AND status = 'pending' LIMIT 1");
    $typeStmt->execute([$orderCode]);
    $typeRow = $typeStmt->fetch();
    if (!$typeRow) return ['status' => 'ignored', 'note' => 'Không tìm thấy đơn pending'];
    $orderType = $typeRow['order_type'] ?? 'key';

    if ($orderType === 'account') {
        // Đơn acc: JOIN account_types thay vì packages
        $stmt = $db->prepare("SELECT o.*, at.name as package_name, at.price, 'Account' as key_type, 0 as days, g.name AS game_name, g.package_name as game_package, g.download_url, u.telegram_id
            FROM orders o
            JOIN account_types at ON o.account_type_id = at.id
            JOIN games g          ON o.game_id    = g.id
            JOIN users u          ON o.user_id    = u.id
            WHERE o.order_code = ? AND o.status = 'pending'
            LIMIT 1");
        $stmt->execute([$orderCode]);
        $order = $stmt->fetch();
    }

    // Đơn key: JOIN packages
    if (!$order) {
        $stmt = $db->prepare("SELECT o.*, p.days, p.hours, p.key_type, p.price, g.name AS game_name, g.package_name, g.download_url, u.telegram_id
            FROM orders o
            LEFT JOIN packages p ON o.package_id = p.id
            JOIN games g    ON o.game_id    = g.id
            JOIN users u    ON o.user_id    = u.id
            WHERE o.order_code = ? AND o.status = 'pending'
            LIMIT 1");
        $stmt->execute([$orderCode]);
        $order = $stmt->fetch();
    }
    if (!$order) return ['status' => 'ignored', 'note' => 'Không tìm thấy đơn pending'];

    // Với MBBank: so sánh amount VND theo order.amount.
    if ($approvedBy === 'mbbank_api' && (float)$order['amount'] > $amount) {
        return ['status' => 'ignored', 'note' => 'Số tiền nhận nhỏ hơn đơn'];
    }

    $db->beginTransaction();
    try {
        if ($orderType === 'account') {
            // ===== DUYỆT ĐƠN ACC =====
            $accStmt = $db->prepare("SELECT id, username, `password` FROM accounts WHERE order_id = ? AND status = 'pending' LIMIT 1");
            $accStmt->execute([$order['id']]);
            $acc = $accStmt->fetch();
            if (!$acc) throw new Exception('Không tìm thấy acc pending');

            $now = date('Y-m-d H:i:s');
            $db->prepare("UPDATE accounts SET status='sold', user_id=?, sold_at=? WHERE id=? AND status='pending'")
               ->execute([$order['user_id'], $now, $acc['id']]);

            $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by=? WHERE id=? AND status='pending'");
            $upOrder->execute([$approvedBy, $order['id']]);
            if ($upOrder->rowCount() !== 1) throw new Exception('Order đã được xử lý bởi process khác');

            $db->prepare("UPDATE bank_transactions SET status='approved', processed_at=NOW(), note=? WHERE tx_hash=?")
               ->execute(['Auto approved ' . $orderCode, $txHash]);

            $db->commit();

            // Gửi Telegram
            $shortOrder = preg_replace('/^ORD/i', '', $orderCode);
            $packageName = $order['package_name'] ?: 'Account';
            $userMsg = "✅ <b>Account Purchase Successful!</b>\n\n" .
                "• Order code : <code>{$shortOrder}</code>\n" .
                "• Game : <code>{$order['game_name']}</code>\n" .
                "• Loại : <code>{$packageName}</code>\n" .
                "• Tài khoản : <code>{$acc['username']}</code>\n" .
                "• Mật khẩu : <code>{$acc['password']}</code>\n" .
                "• Giá : " . number_format((float)$order['price'], 0, ',', '.') . "đ\n\n" .
                "⚠️ Đổi mật khẩu ngay sau khi đăng nhập. Không chia sẻ thông tin tài khoản.";
            if ($extraUserNote !== '') {
                $userMsg .= "\n\n" . $extraUserNote;
            }
            // Phần acc KHÔNG cần nút Tải game
            sendTelegram($order['telegram_id'], $userMsg);

            sendTelegram(ADMIN_CHAT_ID,
                "🤖 <b>AUTO {$adminLabel} DUYỆT ACC</b>\n" .
                "#{$orderCode}\n" .
                "💰 Nhận: " . $amountFormat($amount) . "\n" .
                "👤 <code>{$acc['username']}</code> / <code>***</code>");

            return ['status' => 'approved', 'note' => 'OK'];

        } else {
            // ===== DUYỆT ĐƠN KEY (logic cũ) =====
            $keyStmt = $db->prepare("SELECT id, key_code FROM `keys` WHERE order_id = ? AND status = 'pending' FOR UPDATE");
            $keyStmt->execute([$order['id']]);
            $allKeys = $keyStmt->fetchAll();
            if (empty($allKeys)) throw new Exception('Không tìm thấy key pending');

            $start  = date('Y-m-d H:i:s');
            $tothours = ((int)($order['days'] ?? 0)) * 24 + (int)($order['hours'] ?? 0);
            $expire = date('Y-m-d H:i:s', strtotime('+' . max(1, $tothours) . ' hours'));
            $upKey  = $db->prepare("UPDATE `keys` SET status='active', start_at=?, expire_at=? WHERE id=? AND status='pending'");

            $activatedCount = 0;
            foreach ($allKeys as $k) {
                $upKey->execute([$start, $expire, $k['id']]);
                if ($upKey->rowCount() === 1) $activatedCount++;
            }
            if ($activatedCount === 0) throw new Exception('Không có key nào được kích hoạt');

            $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by=? WHERE id=? AND status='pending'");
            $upOrder->execute([$approvedBy, $order['id']]);
            if ($upOrder->rowCount() !== 1) throw new Exception('Order đã được xử lý bởi process khác');

            $db->prepare("UPDATE bank_transactions SET status='approved', processed_at=NOW(), note=? WHERE tx_hash=?")
               ->execute(['Auto approved ' . $orderCode, $txHash]);

            $db->commit();

            $shortOrder  = preg_replace('/^ORD/i', '', $orderCode);
            $packageName = $order['package_name'] ?: $order['game_name'];
            $type        = strtoupper($order['key_type'] ?: 'Normal') === 'VIP' ? 'VIP' : 'Normal';
            $qtyLabel    = $activatedCount > 1 ? " ({$activatedCount} keys)" : '';
            $keyListStr  = '';
            foreach ($allKeys as $k) {
                $keyListStr .= "• License : <code>{$k['key_code']}</code>\n";
            }
            $userMsg = "✅ <b>Key Purchase Successful!</b>\n\n" .
                "• Order code : <code>{$shortOrder}</code>\n" .
                $keyListStr .
                "• Package : <code>{$packageName}</code>\n" .
                "• Type : {$type} — {$order['days']} days / " . number_format((float)$order['price'], 0, ',', '.') . "đ{$qtyLabel}\n\n" .
                "Duration will start when license login.\n\n" .
                "<b>Lưu ý:</b> để sử dụng an toàn, không dùng song song mod khác hoặc ứng dụng lạ.";
            if ($extraUserNote !== '') {
                $userMsg .= "\n\n" . $extraUserNote;
            }
            // Inline button: Tải game (lấy download_url theo game)
            $dlUrl = trim((string)($order['download_url'] ?? ''));
            $userMarkup = null;
            if ($dlUrl !== '') {
                $userMarkup = ['inline_keyboard' => [[
                    ['text' => '📥 Tải game ' . ($order['game_name'] ?: ''), 'url' => $dlUrl]
                ]]];
            }
            sendTelegram($order['telegram_id'], $userMsg, $userMarkup);

            $keyFirst = $allKeys[0]['key_code'];
            $keyExtra = $activatedCount > 1 ? " (+" . ($activatedCount - 1) . " keys)" : "";
            sendTelegram(ADMIN_CHAT_ID,
                "🤖 <b>AUTO {$adminLabel} DUYỆT ĐƠN</b>\n" .
                "#{$orderCode}\n" .
                "💰 Nhận: " . $amountFormat($amount) . "\n" .
                "🔑 <code>{$keyFirst}</code>{$keyExtra}");

            return ['status' => 'approved', 'note' => 'OK'];
        }
    } catch (Exception $e) {
        $db->rollBack();
        return ['status' => 'error', 'note' => $e->getMessage()];
    }
}
