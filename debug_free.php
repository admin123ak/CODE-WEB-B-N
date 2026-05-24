<?php
// Debug script — TẠM thời, xóa sau khi xong.
// URL: /debug_free.php?token=51144a462f4cda6a13ae8573

require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

if (($_GET['token'] ?? '') !== '51144a462f4cda6a13ae8573') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']); exit;
}

$db = getDB();
$out = [];

$out['php_time'] = date('Y-m-d H:i:s');
$out['php_tz']   = date_default_timezone_get();

try {
    $out['db_name'] = $db->query("SELECT DATABASE()")->fetchColumn();
    $out['db_now'] = $db->query("SELECT NOW()")->fetchColumn();
} catch (Exception $e) { $out['db_error'] = $e->getMessage(); }

try {
    $out['free_keys_total'] = (int)$db->query("SELECT COUNT(*) FROM free_keys")->fetchColumn();
    $out['free_keys_active_notexpired'] = (int)$db->query("SELECT COUNT(*) FROM free_keys WHERE is_active=1 AND expire_at > NOW()")->fetchColumn();
    $rows = $db->query("SELECT id, key_code, game_id, package_id, is_active, start_at, expire_at, claim_token, LEFT(short_url, 60) short_url_preview, created_at FROM free_keys ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['qualifies_for_widget'] = ($r['is_active']==1 && strtotime($r['expire_at']) > time()) ? 'YES' : 'NO';
    }
    $out['free_keys_latest5'] = $rows;
} catch (Exception $e) { $out['free_keys_error'] = $e->getMessage(); }

try {
    $out['free_key_claims_today'] = (int)$db->query("SELECT COUNT(*) FROM free_key_claims WHERE DATE(claimed_at)=CURDATE()")->fetchColumn();
    $out['free_key_claims_latest5'] = $db->query("SELECT id, free_key_id, user_id, key_id, claimed_at FROM free_key_claims ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $out['claims_error'] = $e->getMessage(); }

$out['file_check'] = [
    'backend/api/index.php' => file_exists(__DIR__ . '/backend/api/index.php'),
    'api/index.php (legacy)' => file_exists(__DIR__ . '/api/index.php'),
    'assets/app.js' => file_exists(__DIR__ . '/assets/app.js'),
    'index.php' => file_exists(__DIR__ . '/index.php'),
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
