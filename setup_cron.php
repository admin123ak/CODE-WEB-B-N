<?php
/**
 * Auto-setup cron jobs trên hosting
 * Dùng:
 * 1) Admin panel gọi qua POST
 * 2) Hoặc CLI: php setup_cron.php
 */

// Chỉ cho phép CLI hoặc admin đã đăng nhập
$isAdmin = false;
if (PHP_SAPI === 'cli') {
    $isAdmin = true;
} elseif (isset($_POST['setup_cron'])) {
    require_once __DIR__ . '/config.php';
    session_start();
    $isAdmin = !empty($_SESSION['admin_auth']) && (time() - ($_SESSION['admin_last_seen'] ?? 0)) <= ADMIN_SESSION_TTL;
    if (!$isAdmin) {
        http_response_code(403);
        exit('Forbidden');
    }
}

if (!$isAdmin) {
    http_response_code(403);
    exit('Forbidden');
}

// Load config nếu chưa load
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}

$cronJobs = [
    'mbbank' => [
        'cmd' => 'cd ' . __DIR__ . ' && /usr/bin/php mbbank_poll.php',
        'schedule' => '*/1',
        'minute' => '*/1',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'weekday' => '*',
        'label' => 'MBBANK Auto-bank (mỗi phút)',
    ],
    'maintenance' => [
        'cmd' => 'cd ' . __DIR__ . ' && /usr/bin/php maintenance.php',
        'schedule' => '*/5',
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'weekday' => '*',
        'label' => 'Maintenance (mỗi 5 phút)',
    ],
    'automation' => [
        'cmd' => 'cd ' . __DIR__ . ' && /usr/bin/php automation_daily.php',
        'schedule' => '0 8',
        'minute' => '0',
        'hour' => '8',
        'day' => '*',
        'month' => '*',
        'weekday' => '*',
        'label' => 'Automation Daily (8h sáng)',
    ],
    'health' => [
        'cmd' => 'cd ' . __DIR__ . ' && /usr/bin/php health_check_daily.php',
        'schedule' => '0 9',
        'minute' => '0',
        'hour' => '9',
        'day' => '*',
        'month' => '*',
        'weekday' => '*',
        'label' => 'Health Check (9h sáng)',
    ],
    'monitor' => [
        'cmd' => 'cd ' . __DIR__ . ' && /usr/bin/php cron_monitor.php',
        'schedule' => '*/5',
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'weekday' => '*',
        'label' => 'Cron Monitor (mỗi 5 phút)',
    ],
];

function setupCronViaCrontab(array $jobs): array {
    $results = [];
    $existingCrontab = [];
    $output = [];
    $rc = 0;
    exec('crontab -l 2>/dev/null', $output, $rc);
    $existingCrontab = $output;

    // Filter out our old jobs to avoid duplicates
    $otherJobs = array_filter($existingCrontab, function($line) use ($jobs) {
        foreach ($jobs as $j) {
            if (strpos($line, $j['cmd']) !== false) return false;
        }
        return true;
    });

    $newCrontab = array_merge($otherJobs, []);
    foreach ($jobs as $key => $job) {
        $cronLine = "{$job['minute']} {$job['hour']} {$job['day']} {$job['month']} {$job['weekday']} {$job['cmd']}";
        $newCrontab[] = "# {$job['label']}";
        $newCrontab[] = $cronLine;
        $results[$key] = ['added' => true, 'line' => $cronLine];
    }

    $crontabContent = implode("\n", $newCrontab) . "\n";
    $tmpFile = sys_get_temp_dir() . '/hclou_crontab_' . uniqid() . '.tmp';
    file_put_contents($tmpFile, $crontabContent);
    exec("crontab {$tmpFile} 2>&1", $installOutput, $installRc);
    unlink($tmpFile);

    if ($installRc !== 0) {
        return ['success' => false, 'error' => implode("\n", $installOutput), 'results' => $results];
    }
    return ['success' => true, 'results' => $results];
}

function setupCronViaCPanelUAPI(array $jobs, string $cpanelUser, string $cpanelPass, string $cpanelHost): array {
    $results = [];
    foreach ($jobs as $key => $job) {
        $url = "https://{$cpanelHost}:2083/execute/Cron/add_line";
        $postData = "line=" . urlencode($job['cmd']) .
                   "&minute=" . urlencode($job['minute']) .
                   "&hour=" . urlencode($job['hour']) .
                   "&day=" . urlencode($job['day']) .
                   "&month=" . urlencode($job['month']) .
                   "&weekday=" . urlencode($job['weekday']);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => "{$cpanelUser}:{$cpanelPass}",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        $success = isset($data['status']) && $data['status'] == 1;
        $results[$key] = [
            'added' => $success,
            'error' => $success ? null : ($data['errors'][0] ?? "HTTP {$httpCode}"),
        ];
    }
    return ['success' => true, 'results' => $results];
}

// Execute setup
$method = $_GET['method'] ?? 'crontab';
$result = null;

if ($method === 'cpanel') {
    $cpanelUser = $_POST['cpanel_user'] ?? '';
    $cpanelPass = $_POST['cpanel_pass'] ?? '';
    $cpanelHost = $_POST['cpanel_host'] ?? '127.0.0.1';
    $result = setupCronViaCPanelUAPI($cronJobs, $cpanelUser, $cpanelPass, $cpanelHost);
} else {
    $result = setupCronViaCrontab($cronJobs);
}

// Return response
if (PHP_SAPI === 'cli') {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit;
}

header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
