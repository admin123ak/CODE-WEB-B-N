<?php
require_once __DIR__ . '/../config.php';

function runMaintenance(PDO $db): array {
    $out = ['expired_keys'=>0, 'deleted_expired_keys'=>0, 'cancelled_orders'=>0, 'returned_to_pool'=>0, 'expired_topups'=>0];
    $stmt = $db->prepare("UPDATE `keys` SET status='expired' WHERE status='active' AND expire_at IS NOT NULL AND expire_at < NOW()");
    $stmt->execute();
    $out['expired_keys'] = $stmt->rowCount();

    // Xoá key đã hết hạn quá 3 ngày nếu user không gia hạn.
    $stmt = $db->prepare("DELETE FROM `keys` WHERE status='expired' AND expire_at IS NOT NULL AND expire_at < (NOW() - INTERVAL 3 DAY)");
    $stmt->execute();
    $out['deleted_expired_keys'] = $stmt->rowCount();

    // Hủy đơn pending quá 15 phút — key + acc quay lại pool để người khác mua
    $db->beginTransaction();
    try {
        $orders = $db->query("SELECT id, order_type FROM orders WHERE status='pending' AND created_at < (NOW() - INTERVAL 15 MINUTE) FOR UPDATE")->fetchAll();
        if ($orders) {
            $ids = array_map(fn($r)=>(int)$r['id'], $orders);
            $in = implode(',', array_fill(0, count($ids), '?'));
            // Trả key về pool available để người khác mua
            $stmt = $db->prepare("UPDATE `keys` SET status='available', user_id=NULL, order_id=NULL WHERE order_id IN ($in) AND status='pending'");
            $stmt->execute($ids);
            $out['returned_to_pool'] = $stmt->rowCount();
            // Trả acc về pool available
            $stmt = $db->prepare("UPDATE accounts SET status='available', user_id=NULL, order_id=NULL WHERE order_id IN ($in) AND status='pending'");
            $stmt->execute($ids);
            $out['returned_to_pool'] += $stmt->rowCount();
            // Hủy đơn quá hạn
            $stmt = $db->prepare("UPDATE orders SET status='cancelled', admin_note='Tự huỷ do quá 15 phút chưa thanh toán' WHERE id IN ($in) AND status='pending'");
            $stmt->execute($ids);
            $out['cancelled_orders'] = $stmt->rowCount();
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }

    // Huỷ topup_requests pending quá 30 phút (bank: 30p, binance: 30p, card: cho callback xử lý)
    // Card có thể callback chậm — chỉ huỷ bank + binance
    try {
        $stmt = $db->prepare("UPDATE topup_requests
            SET status='expired', note=CONCAT(COALESCE(note,''),' [auto-expired after 30 minutes]'), processed_at=NOW()
            WHERE status='pending'
              AND method IN ('mbbank','binance')
              AND created_at < (NOW() - INTERVAL 30 MINUTE)");
        $stmt->execute();
        $out['expired_topups'] = $stmt->rowCount();
    } catch (Throwable $e) {
        $out['expired_topups_error'] = $e->getMessage();
    }

    return $out;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $isHttp = PHP_SAPI !== 'cli';
    // Cho phép gọi qua HTTP với cron_token
    if ($isHttp) {
        $httpToken = $_GET['cron_token'] ?? $_POST['cron_token'] ?? '';
        if (!defined('CRON_RUN_TOKEN') || !hash_equals(CRON_RUN_TOKEN, $httpToken)) {
            http_response_code(403);
            header('Content-Type: application/json');
            exit(json_encode(['success' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE));
        }
    }
    try {
        $result = runMaintenance(getDB());
        if ($isHttp) header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true] + $result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } catch (Throwable $e) {
        if ($isHttp) http_response_code(500);
        if ($isHttp) header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
