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
// VERIFY WEBHOOK FROM TELEGRAM
// =============================================
// Telegram gửi header `X-Telegram-Bot-Api-Secret-Token` khớp với
// secret_token đã set khi gọi setWebhook (installer làm việc này).
$incoming = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
if (defined('TELEGRAM_WEBHOOK_SECRET') && TELEGRAM_WEBHOOK_SECRET !== '') {
    if (!hash_equals(TELEGRAM_WEBHOOK_SECRET, $incoming)) {
        http_response_code(403);
        exit('Forbidden');
    }
} else {
    // Webhook chưa được installer setup - reject để tránh giả mạo
    http_response_code(503);
    exit('Webhook not configured');
}

$input  = file_get_contents('php://input');
$update = json_decode($input, true);
if (!$update) exit;

$db = getDB();

// =============================================
// CALLBACK QUERY (nút bấm inline)
// =============================================
if (isset($update['callback_query'])) {
    $callback   = $update['callback_query'];
    $data       = $callback['data']          ?? '';
    $from       = $callback['from']          ?? [];
    $chat_id    = $callback['message']['chat']['id']    ?? 0;
    $message_id = $callback['message']['message_id']    ?? 0;

    // Kiểm tra admin
    $stmt = $db->prepare("SELECT * FROM admins WHERE telegram_id = ?");
    $stmt->execute([$from['id'] ?? 0]);
    $admin = $stmt->fetch();
    $isAdminFromConfig = ((string)($from['id'] ?? '') === (string)ADMIN_CHAT_ID);
    if (!$admin && !$isAdminFromConfig) {
        answerCallback($callback['id'], '❌ Bạn không có quyền admin!');
        exit;
    }
    $admin_name = $admin
        ? ($admin['username'] ?? 'admin')
        : ($from['username'] ?? $from['first_name'] ?? 'admin');

    if (strpos($data, 'approve_') === 0) {
        approveOrder($db, substr($data, 8), $admin_name, $callback['id'], $chat_id, $message_id);
    } elseif (strpos($data, 'reject_') === 0) {
        rejectOrder($db, substr($data, 7), $admin_name, $callback['id'], $chat_id, $message_id);
    } elseif (strpos($data, 'lock_') === 0) {
        $key_id = (int)substr($data, 5);
        $db->prepare("UPDATE `keys` SET status='locked' WHERE id=?")->execute([$key_id]);
        answerCallback($callback['id'], '🔒 Đã khoá key!');
    } elseif (strpos($data, 'unlock_') === 0) {
        $key_id = (int)substr($data, 7);
        $db->prepare("UPDATE `keys` SET status='active' WHERE id=?")->execute([$key_id]);
        answerCallback($callback['id'], '🔓 Đã mở khoá key!');
    } elseif (strpos($data, 'delete_') === 0) {
        $key_id = (int)substr($data, 7);
        $db->prepare("DELETE FROM `keys` WHERE id=?")->execute([$key_id]);
        answerCallback($callback['id'], '🗑 Đã xoá key!');
    }
    exit;
}

// =============================================
// MESSAGE
// =============================================
if (isset($update['message'])) {
    $msg     = $update['message'];
    $chat_id = $msg['chat']['id']         ?? 0;
    $text    = trim($msg['text']          ?? '');
    $from    = $msg['from']               ?? [];

    $stmt = $db->prepare("SELECT * FROM admins WHERE telegram_id = ?");
    $stmt->execute([$from['id'] ?? 0]);
    $admin = $stmt->fetch();

    if ($text === '/start') {
        $miniAppUrl = SITE_URL . '/?v=app';
        $keyboard = ['inline_keyboard' => [
            [['text' => '🛒 Mua Key', 'web_app' => ['url' => $miniAppUrl]]],
            [['text' => '📢 ' . SITE_NAME . ' TEAM', 'url' => 'https://t.me/']],
        ]];

        $welcome = "<b>Bot này có thể làm gì?</b>\n\n"
                 . "Chào mừng bạn đến với <b>" . h(SITE_NAME) . " Bot</b>\n\n"
                 . "✅ Quản lý key chính bạn\n"
                 . "✅ Nhận key ngay sau khi bank\n"
                 . "✅ Reset key không giới hạn\n"
                 . "✅ Cập nhật đầy đủ bản giá mods\n\n"
                 . "Nhấp <b>[Mua Key]</b> bên dưới để bắt đầu.\n\n"
                 . "🆔 ID Telegram của bạn: <code>" . (int)($from['id'] ?? 0) . "</code>";
        if ($admin) {
            $welcome .= "\n\n🔑 Lệnh nhanh: /mykeys\n🔐 <b>Admin:</b> /orders · /stats";
        }
        sendTelegram($chat_id, $welcome, $keyboard);
    } elseif ($text === '/help') {
        $miniAppUrl = SITE_URL . '/?v=app';
        $help = "🆘 <b>" . h(SITE_NAME) . " - Hướng dẫn nhanh</b>\n\n"
              . "🛒 <b>Mua key:</b> bấm Mua Key, chọn game/gói, xác nhận đơn.\n"
              . "💳 <b>Thanh toán:</b> quét VietQR, hệ thống tự điền số tiền + nội dung ORD.\n"
              . "⏳ <b>Lỡ thoát app:</b> mở lại Mini App trong 15 phút.\n"
              . "✅ <b>Auto-bank</b> kiểm tra MBBANK mỗi phút.\n"
              . "🎁 <b>GetKey Free:</b> Mini App → Get Key Free.\n"
              . "🔑 <b>Xem key:</b> /mykeys hoặc Mini App.";
        sendTelegram($chat_id, $help, ['inline_keyboard' => [[['text' => '🛒 Mở Mini App', 'web_app' => ['url' => $miniAppUrl]]]]]);
    } elseif ($text === '/mykeys') {
        $stmt = $db->prepare("SELECT k.*, g.name AS game_name, p.name AS pkg_name
            FROM `keys` k
            JOIN games g    ON k.game_id    = g.id
            JOIN packages p ON k.package_id = p.id
            JOIN users u    ON k.user_id    = u.id
            WHERE u.telegram_id = ?
            ORDER BY k.created_at DESC LIMIT 10");
        $stmt->execute([$from['id'] ?? 0]);
        $keys = $stmt->fetchAll();
        $miniAppUrl = SITE_URL . '/?v=app';
        if (!$keys) {
            sendTelegram($chat_id, '🔑 Bạn chưa có key nào. Bấm <b>Mua Key</b> để tạo đơn.',
                ['inline_keyboard' => [[['text' => '🛒 Mua Key', 'web_app' => ['url' => $miniAppUrl]]]]]);
        } else {
            $out = "🔑 <b>KEY CỦA BẠN</b>\n\n";
            foreach ($keys as $k) {
                $exp = $k['expire_at'] ? date('d/m/Y H:i', strtotime($k['expire_at'])) : 'Chờ thanh toán';
                $out .= "🎮 <b>" . h($k['game_name']) . "</b> - " . h($k['pkg_name']) . "\n"
                     .  "🔐 <code>" . h($k['key_code']) . "</code>\n"
                     .  "📌 Trạng thái: <b>" . h($k['status']) . "</b> · Hết hạn: " . h($exp) . "\n\n";
            }
            sendTelegram($chat_id, $out, ['inline_keyboard' => [[['text' => '🛒 Mua / Quản lý Key', 'web_app' => ['url' => $miniAppUrl]]]]]);
        }
    } elseif ($text === '/orders' && $admin) {
        $stmt = $db->query("SELECT o.*, u.telegram_username, g.name as game_name, p.name as pkg_name, p.days, p.hours, k.key_code
            FROM orders o
            JOIN users u    ON o.user_id    = u.id
            JOIN games g    ON o.game_id    = g.id
            JOIN packages p ON o.package_id = p.id
            LEFT JOIN `keys` k ON k.order_id = o.id AND k.status = 'pending'
            WHERE o.status = 'pending'
            ORDER BY o.created_at DESC LIMIT 10");
        $orders = $stmt->fetchAll();
        if (empty($orders)) {
            sendTelegram($chat_id, '✅ Không có đơn hàng nào đang chờ thanh toán.');
        } else {
            foreach ($orders as $o) {
                $amt = number_format($o['amount'], 0, ',', '.');
                $body = "🛒 <b>ĐƠN HÀNG #" . h($o['order_code']) . "</b>\n"
                      . "👤 User: @" . h($o['telegram_username']) . "\n"
                      . "🎮 Game: " . h($o['game_name']) . "\n"
                      . "📦 Gói: " . h($o['pkg_name']) . " (" . hclouFmtDur($o['days'], $o['hours'] ?? 0) . ")\n"
                      . "🔑 Key đã tạo: <code>" . h($o['key_code'] ?: 'Chưa có') . "</code>\n"
                      . "💰 Số tiền: " . $amt . "đ\n"
                      . "🕐 " . date('d/m/Y H:i', strtotime($o['created_at']));
                $markup = ['inline_keyboard' => [[
                    ['text' => '✅ Duyệt đơn', 'callback_data' => 'approve_' . $o['order_code']],
                    ['text' => '❌ Từ chối',   'callback_data' => 'reject_'  . $o['order_code']],
                ]]];
                sendTelegram($chat_id, $body, $markup);
            }
        }
    } elseif ($text === '/stats' && $admin) {
        // Gộp thành 1 query
        $row = $db->query("SELECT
            (SELECT COUNT(*) FROM orders WHERE status='approved') AS total_orders,
            (SELECT COALESCE(SUM(amount),0) FROM orders WHERE status='approved') AS total_revenue,
            (SELECT COUNT(*) FROM `keys`)                                       AS total_keys,
            (SELECT COUNT(*) FROM `keys` WHERE status='active')                 AS active_keys,
            (SELECT COUNT(*) FROM users)                                        AS total_users")->fetch();
        $rev = number_format((float)($row['total_revenue'] ?? 0), 0, ',', '.');
        sendTelegram($chat_id,
            "📊 <b>THỐNG KÊ HỆ THỐNG</b>\n\n"
            . "👥 Người dùng: " . (int)$row['total_users'] . "\n"
            . "🛒 Đơn thành công: " . (int)$row['total_orders'] . "\n"
            . "💰 Doanh thu: " . $rev . "đ\n"
            . "🔑 Tổng key: " . (int)$row['total_keys'] . "\n"
            . "✅ Key đang active: " . (int)$row['active_keys']);
    }
    exit;
}

// =============================================
// HANDLERS
// =============================================
function approveOrder(PDO $db, string $order_code, string $admin_name, string $callback_id, $chat_id, $message_id) {
    $stmt = $db->prepare("SELECT o.*, u.telegram_id, p.days, p.hours
        FROM orders o
        JOIN users u    ON o.user_id    = u.id
        JOIN packages p ON o.package_id = p.id
        WHERE o.order_code = ? AND o.status = 'pending'");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch();
    if (!$order) {
        answerCallback($callback_id, '❌ Đơn không tồn tại hoặc đã xử lý!');
        return;
    }

    $db->beginTransaction();
    try {
        // Atomic: chỉ update khi vẫn pending
        $upOrder = $db->prepare("UPDATE orders SET status='approved', approved_at=NOW(), approved_by=? WHERE order_code=? AND status='pending'");
        $upOrder->execute([$admin_name, $order_code]);
        if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');

        $now    = date('Y-m-d H:i:s');
        $totHr  = max(1, ((int)($order['days'] ?? 0)) * 24 + (int)($order['hours'] ?? 0));
        $expire = date('Y-m-d H:i:s', strtotime('+' . $totHr . ' hours'));
        $db->prepare("UPDATE `keys` SET status='active', start_at=COALESCE(start_at,?), expire_at=? WHERE order_id=? AND status IN ('pending','available')")
           ->execute([$now, $expire, $order['id']]);
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log('[APPROVE_ORDER] ' . $e->getMessage());
        answerCallback($callback_id, '❌ ' . $e->getMessage());
        return;
    }

    answerCallback($callback_id, '✅ Đã duyệt đơn!');
    editMessage($chat_id, $message_id, "✅ <b>ĐÃ DUYỆT #" . h($order_code) . "</b>\nAdmin: @" . h($admin_name));
    if ($order['telegram_id']) {
        sendTelegram($order['telegram_id'],
            "✅ <b>Đơn hàng #" . h($order_code) . " đã được admin duyệt!</b>\n🔑 Key đã hoạt động. Thời hạn: " . hclouFmtDur($order['days'], $order['hours'] ?? 0) . ".");
    }
}

function rejectOrder(PDO $db, string $order_code, string $admin_name, string $callback_id, $chat_id, $message_id) {
    $stmt = $db->prepare("SELECT o.id, o.user_id, u.telegram_id
        FROM orders o JOIN users u ON o.user_id = u.id
        WHERE o.order_code = ? AND o.status = 'pending'");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch();
    if (!$order) {
        answerCallback($callback_id, '❌ Đơn không tồn tại hoặc đã xử lý!');
        return;
    }

    $db->beginTransaction();
    try {
        $upOrder = $db->prepare("UPDATE orders SET status='rejected', approved_by=? WHERE id=? AND status='pending'");
        $upOrder->execute([$admin_name, $order['id']]);
        if ($upOrder->rowCount() !== 1) throw new Exception('Đơn đã được xử lý bởi process khác');

        // Xoá key tạm gói API (không trả về pool kẻo bán nhầm)
        $db->prepare("DELETE FROM `keys` WHERE order_id=? AND status='pending' AND key_code LIKE 'APIWAIT-%'")
           ->execute([$order['id']]);
        $db->prepare("UPDATE `keys` SET status='available', user_id=NULL, order_id=NULL WHERE order_id=? AND status IN ('pending','available')")
           ->execute([$order['id']]);
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log('[REJECT_ORDER] ' . $e->getMessage());
        answerCallback($callback_id, '❌ ' . $e->getMessage());
        return;
    }

    answerCallback($callback_id, '❌ Đã từ chối đơn!');
    editMessage($chat_id, $message_id, "❌ <b>ĐÃ TỪ CHỐI #" . h($order_code) . "</b>\nAdmin: @" . h($admin_name));
    if ($order['telegram_id']) {
        sendTelegram($order['telegram_id'],
            "❌ <b>Đơn hàng #" . h($order_code) . " bị từ chối.</b>\nLiên hệ admin để được hỗ trợ.");
    }
}

function answerCallback($callback_id, $text) {
    $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/answerCallbackQuery');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['callback_query_id' => $callback_id, 'text' => $text, 'show_alert' => true]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_exec($ch);
    curl_close($ch);
}

function editMessage($chat_id, $message_id, $text) {
    $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/editMessageText');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $text, 'parse_mode' => 'HTML']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_exec($ch);
    curl_close($ch);
}
