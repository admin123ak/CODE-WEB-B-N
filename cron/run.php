<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

defined('CRON_RUN_TOKEN') || define('CRON_RUN_TOKEN', '');
const CRON_RUN_LOG = __DIR__ . '/data/cron_run.log';

function hclouCronRunLog(string $job, int $status, bool $success, int $durationMs, string $detail = ''): void {
    $dir = dirname(CRON_RUN_LOG);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $entry = [
        'ts'          => date('c'),
        'ip'          => $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
        'ua'          => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 180),
        'job'         => $job,
        'status'      => $status,
        'success'     => $success,
        'duration_ms' => $durationMs,
        'detail'      => substr($detail, 0, 500),
    ];
    @file_put_contents(CRON_RUN_LOG, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Ghi status snapshot per-job để admin panel đọc O(1).
 * Khác với hclouCronRunLog (append-only JSONL): file này overwrite mỗi lần,
 * chỉ giữ KẾT QUẢ MỚI NHẤT của 1 job.
 */
function hclouCronWriteStatus(string $job, array $extra): void {
    $job = preg_replace('/[^a-z0-9_]/i', '_', $job);
    if ($job === '') return;
    $file = __DIR__ . '/data/cron_status_' . $job . '.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);
    $payload = array_merge([
        'job'         => $job,
        'last_run_at' => date('c'),
        'success'     => false,
        'http_code'   => 0,
        'duration_ms' => 0,
        'skipped'     => false,
        'detail'      => '',
    ], $extra);
    @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Gọi script qua HTTP - hoạt động trên mọi hosting (không cần exec).
 */
function hclouHttpCall(string $path, array $params = [], int $timeout = 30): array {
    $url = rtrim(SITE_URL, '/') . $path;
    if ($params) $url .= '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'HCLOU-Cron/1.0',
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    if ($body === false) return ['ok' => false, 'code' => 0, 'body' => 'Curl error: ' . $curlErr];
    return ['ok' => true, 'code' => $httpCode, 'body' => $body];
}

$token = $_GET['token'] ?? '';
$job   = $_GET['job']   ?? '';
$requestStarted = microtime(true);

if (!hash_equals(CRON_RUN_TOKEN, $token)) {
    http_response_code(403);
    hclouCronRunLog($job, 403, false, (int)round((microtime(true) - $requestStarted) * 1000), 'Forbidden');
    echo json_encode(['success' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================
// LOCK: tránh chạy chồng cùng job
// =============================================
$lockDir = __DIR__ . '/data/locks';
if (!is_dir($lockDir)) @mkdir($lockDir, 0755, true);
$lockFile = $lockDir . '/cron_' . preg_replace('/[^a-z0-9_]/i', '_', $job) . '.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $_ms = (int)round((microtime(true) - $requestStarted) * 1000);
    hclouCronRunLog($job, 200, true, $_ms, 'skipped: previous_still_running');
    hclouCronWriteStatus($job, ['success' => true, 'http_code' => 200, 'duration_ms' => $_ms, 'skipped' => true, 'detail' => 'previous_still_running']);
    echo json_encode(['success' => true, 'job' => $job, 'skipped' => true, 'reason' => 'previous_still_running'], JSON_UNESCAPED_UNICODE);
    exit;
}
register_shutdown_function(function() use ($lockHandle) {
    if ($lockHandle) { flock($lockHandle, LOCK_UN); fclose($lockHandle); }
});

$jobMap = [
    'mbbank'      => function() {
        return hclouHttpCall('/cron/mbbank_poll.php', ['secret' => MBBANK_POLL_SECRET]);
    },
    'crypto'      => function() {
        if (!defined('CRYPTO_POLL_SECRET')) {
            return ['ok' => true, 'code' => 200, 'body' => json_encode(['success' => true, 'skipped' => true, 'reason' => 'crypto_not_configured'])];
        }
        return hclouHttpCall('/cron/crypto_poll.php', ['secret' => CRYPTO_POLL_SECRET]);
    },
    'card'        => function() {
        // Active check doithe.vn pending topups — fallback nếu callback chậm/lỗi.
        if (!defined('CARD_POLL_SECRET')) {
            return ['ok' => true, 'code' => 200, 'body' => json_encode(['success' => true, 'skipped' => true, 'reason' => 'card_not_configured'])];
        }
        return hclouHttpCall('/cron/card_poll.php', ['secret' => CARD_POLL_SECRET]);
    },
    'maintenance' => function() {
        return hclouHttpCall('/cron/maintenance.php', ['cron_token' => CRON_RUN_TOKEN]);
    },
    'automation'  => function() {
        return hclouHttpCall('/cron/automation_daily.php', ['cron_token' => CRON_RUN_TOKEN]);
    },
    'health'      => function() {
        return hclouHttpCall('/cron/health_check_daily.php', ['cron_token' => CRON_RUN_TOKEN]);
    },
    'monitor'     => function() {
        return hclouHttpCall('/cron/cron_monitor.php', ['cron_token' => CRON_RUN_TOKEN]);
    },
    'backup'      => function() {
        // DB dump có thể chậm (vài giây với DB lớn). Cho timeout dài hơn các job khác.
        return hclouHttpCall('/cron/db_backup.php', ['cron_token' => CRON_RUN_TOKEN], 120);
    },
];

if (!isset($jobMap[$job])) {
    http_response_code(400);
    $_ms = (int)round((microtime(true) - $requestStarted) * 1000);
    hclouCronRunLog($job, 400, false, $_ms, 'Invalid job: ' . $job);
    hclouCronWriteStatus($job ?: 'unknown', ['success' => false, 'http_code' => 400, 'duration_ms' => $_ms, 'detail' => 'Invalid job']);
    echo json_encode(['success' => false, 'error' => 'Invalid job: ' . $job], JSON_UNESCAPED_UNICODE);
    exit;
}

$started = microtime(true);
$res     = $jobMap[$job]();
$ms      = (int)round((microtime(true) - $started) * 1000);
$json    = json_decode($res['body'] ?? '', true);

if (!$res['ok'] || $res['code'] !== 200) {
    http_response_code(500);
    hclouCronRunLog($job, $res['code'] ?? 0, false, $ms, $res['body'] ?? '');
    hclouCronWriteStatus($job, ['success' => false, 'http_code' => $res['code'] ?? 0, 'duration_ms' => $ms, 'detail' => substr((string)($res['body'] ?? ''), 0, 300)]);
    echo json_encode(['success' => false, 'job' => $job, 'http_code' => $res['code'] ?? 0, 'duration_ms' => $ms, 'output' => $res['body'] ?? ''], JSON_UNESCAPED_UNICODE);
    exit;
}

hclouCronRunLog($job, 200, true, $ms, is_array($json) ? json_encode($json, JSON_UNESCAPED_UNICODE) : ($res['body'] ?? ''));
hclouCronWriteStatus($job, ['success' => true, 'http_code' => 200, 'duration_ms' => $ms, 'detail' => is_array($json) ? json_encode($json, JSON_UNESCAPED_UNICODE) : substr((string)($res['body'] ?? ''), 0, 300)]);
echo json_encode(['success' => true, 'job' => $job, 'duration_ms' => $ms, 'result' => $json ?: ($res['body'] ?? '')], JSON_UNESCAPED_UNICODE);
