<?php
/**
 * Auto-setup cron jobs qua HTTP — không cần exec() hay SSH
 *
 * Cách dùng:
 * 1. Mở trang này trên hosting: https://YOUR_SITE/setup_cron.php
 * 2. Nhấn nút "Đăng ký Cron Jobs" — nó sẽ tạo file hướng dẫn + test URL
 * 3. Copy URL từ cron_run.php vào cron-job.org hoặc bất kỳ cron service nào
 *
 * Hoặc CLI: php setup_cron.php
 */

require_once __DIR__ . '/config.php';

// Auth check
$isAdmin = false;
if (PHP_SAPI === 'cli') {
    $isAdmin = true;
} elseif (isset($_POST['setup_cron'])) {
    session_start();
    $isAdmin = !empty($_SESSION['admin_auth']) && (time() - ($_SESSION['admin_last_seen'] ?? 0)) <= ADMIN_SESSION_TTL;
} elseif (isset($_GET['setup_cron']) && isset($_GET['token'])) {
    $isAdmin = hash_equals(CRON_RUN_TOKEN, $_GET['token']);
}

if (!$isAdmin) {
    http_response_code(403);
    header('Content-Type: application/json');
    exit(json_encode(['success' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE));
}

$jobs = [
    'mbbank' => [
        'url'   => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=mbbank',
        'schedule' => '*/1 * * * *',
        'label' => 'MBBANK Auto-bank (mỗi 1 phút)',
    ],
    'maintenance' => [
        'url'   => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=maintenance',
        'schedule' => '*/5 * * * *',
        'label' => 'Maintenance (mỗi 5 phút)',
    ],
    'automation' => [
        'url'   => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=automation',
        'schedule' => '0 8 * * *',
        'label' => 'Automation Daily (8h sáng)',
    ],
    'health' => [
        'url'   => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=health',
        'schedule' => '0 9 * * *',
        'label' => 'Health Check (9h sáng)',
    ],
    'monitor' => [
        'url'   => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=monitor',
        'schedule' => '*/5 * * * *',
        'label' => 'Cron Monitor (mỗi 5 phút)',
    ],
];

// Nếu là POST request từ admin panel, test các URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['setup_cron']) && isset($_GET['test']) && isset($_GET['token']))) {
    $results = [];
    foreach ($jobs as $key => $job) {
        $ch = curl_init($job['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $decoded = json_decode($body, true);
        $results[$key] = [
            'url'      => $job['url'],
            'schedule' => $job['schedule'],
            'label'    => $job['label'],
            'http_code'=> $httpCode,
            'success'  => $httpCode === 200 && is_array($decoded) && !empty($decoded['success']),
            'error'    => $curlErr ?: ($decoded['error'] ?? ($decoded['output'] ?? '')),
        ];
    }

    if (PHP_SAPI === 'cli' || (isset($_GET['test']) && isset($_GET['token']))) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    // Admin panel redirect
    $data = base64_encode(json_encode($results));
    header("Location: ?tab=setup&cron_results=" . urlencode($data));
    exit;
}

// Hiển thị HTML hướng dẫn
if (PHP_SAPI !== 'cli') {
    $cronToken = CRON_RUN_TOKEN;
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup Cron Jobs · HCLOU</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{min-height:100vh;background:#0d1117;color:#e6edf3;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:20px}.container{max-width:900px;margin:0 auto}.card{background:#161b22;border:1px solid #30363d;border-radius:16px;padding:24px;margin-bottom:16px}h1{font-size:22px;margin-bottom:8px}.mono{font-family:monospace;background:#0d1117;padding:4px 8px;border-radius:6px;font-size:13px;word-break:break-all}h3{font-size:15px;margin:12px 0 8px;color:#58a6ff}table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:8px 12px;border-bottom:1px solid #21262d;text-align:left;font-size:13px}th{color:#8b949e}td.mono{font-size:12px}.btn{display:inline-block;padding:10px 20px;background:linear-gradient(135deg,#1f6feb,#8b5cf6);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none}.btn:hover{opacity:.9}.okbox{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#86efac;padding:12px;border-radius:10px;margin-bottom:12px}.errbox{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:12px;border-radius:10px;margin-bottom:12px}.codebox{background:#0d1117;border:1px solid #30363d;border-radius:10px;padding:12px;font-family:monospace;font-size:12px;overflow-x:auto;white-space:pre-wrap;color:#c9d1d9;margin:8px 0}a{color:#58a6ff}.warnbox{background:rgba(234,179,8,.1);border:1px solid rgba(234,179,8,.25);color:#fde68a;padding:12px;border-radius:10px;margin-bottom:12px;font-size:13px}.green{color:#3fb950}.red{color:#f85149}.gray{color:#8b949e}.badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}.badge-green{background:rgba(34,197,94,.15);color:#4ade80}.badge-red{background:rgba(239,68,68,.15);color:#f87171}</style></head>
<body><div class="container">
<h1>🚀 Setup Cron Jobs — HCLOU Server</h1>

<div class="warnbox">
⚠️ Tất cả cron jobs chạy qua HTTP bằng <span class="mono">cron_run.php</span> — không cần <span class="mono">exec()</span> hay SSH.<br>
Chỉ cần cấu hình cron-job.org (hoặc bất kỳ service nào) gọi URL bên dưới.
</div>

<div class="card">
<h3>📋 Bảng Cron Jobs</h3>
<table>
<tr><th>Job</th><th>Lịch</th><th>URL cần gọi</th></tr>
<?php foreach ($jobs as $key => $job): ?>
<tr>
  <td><b><?=htmlspecialchars($job['label'])?></b></td>
  <td><span class="mono"><?=htmlspecialchars($job['schedule'])?></span></td>
  <td><span class="mono"><?=htmlspecialchars($job['url'])?></span></td>
</tr>
<?php endforeach ?>
</table>
</div>

<div class="card">
<h3>🔗 Cách setup với cron-job.org (free)</h3>
<ol style="padding-left:20px;line-height:2">
  <li>Vào <a href="https://cron-job.org" target="_blank">cron-job.org</a> → đăng ký miễn phí</li>
  <li>Tạo cron mới → dán URL ở bảng trên</li>
  <li>Chọn lịch chạy phù hợp (ví dụ: mỗi 1 phút cho MBBank)</li>
  <li>Chọn <b>HTTP method: GET</b></li>
  <li>Lưu và kích hoạt</li>
</ol>
</div>

<div class="card">
<h3>🧪 Test nhanh tất cả jobs</h3>
<p style="color:#8b949e;margin-bottom:12px">Nhấn nút bên dưới để gọi thử tất cả cron jobs. Kết quả sẽ hiện ngay tại đây.</p>
<form method="POST">
  <button class="btn" type="submit">🚀 Test tất cả Cron Jobs</button>
</form>
</div>

<?php
$testResults = $_GET['cron_results'] ?? ($_POST['_test_results'] ?? null);
if ($testResults) {
    $results = json_decode(base64_decode($testResults), true);
    if (is_array($results)) {
        echo '<div class="card"><h3>📊 Kết quả test</h3>';
        foreach ($results as $key => $r) {
            $ok = !empty($r['success']);
            echo '<div style="margin-bottom:8px;padding:8px 12px;border-radius:8px;background:' . ($ok ? 'rgba(34,197,94,.08)' : 'rgba(239,68,68,.08)') . ';border:1px solid ' . ($ok ? 'rgba(34,197,94,.2)' : 'rgba(239,68,68,.2)') . '">';
            echo '<span class="badge ' . ($ok ? 'badge-green' : 'badge-red') . '">' . ($ok ? '✅ OK' : '❌ LỖI') . '</span> ';
            echo '<b>' . htmlspecialchars($r['label'] ?? $key) . '</b><br>';
            echo '<span class="mono" style="font-size:11px">HTTP ' . ($r['http_code'] ?? '?') . '</span><br>';
            if (!empty($r['error'])) echo '<span class="red" style="font-size:12px">' . htmlspecialchars($r['error']) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?>

<div class="card">
<h3>📌 Gợi ý lịch chạy</h3>
<ul style="padding-left:20px;line-height:2">
  <li><b>MBBANK:</b> mỗi 1 phút (quan trọng nhất — tự động duyệt đơn)</li>
  <li><b>Maintenance:</b> mỗi 5 phút (xoá key hết hạn, huỷ đơn quá hạn)</li>
  <li><b>Monitor:</b> mỗi 5 phút (cảnh báo lỗi hệ thống)</li>
  <li><b>Automation:</b> 8h sáng hàng ngày (nhắc nhở, báo cáo)</li>
  <li><b>Health:</b> 9h sáng hàng ngày (kiểm tra toàn bộ hệ thống)</li>
</ul>
</div>

<div class="card">
<h3>⚡ Cron token</h3>
<p style="color:#8b949e;font-size:13px">Token dùng để xác thực cron requests (đã tự động đính kèm vào URL):</p>
<div class="codebox"><?=htmlspecialchars($cronToken)?></div>
<p style="color:#6e7681;font-size:12px">Không chia sẻ token này công khai. Nếu bị lộ, sửa trong config.php.</p>
</div>

<div class="card">
<h3>🔒 Bảo mật</h3>
<ul style="padding-left:20px;line-height:2">
  <li>Tất cả cron jobs đều yêu cầu token hợp lệ — không token = 403 Forbidden</li>
  <li>Token được định nghĩa trong <span class="mono">config.php</span> (CRON_RUN_TOKEN)</li>
  <li>MBBank poll có thêm secret riêng (MBBANK_POLL_SECRET)</li>
  <li>Không dùng exec() — an toàn trên shared hosting</li>
</ul>
</div>

<div class="card">
<h3>🆘 Xử lý sự cố</h3>
<ul style="padding-left:20px;line-height:2">
  <li><b>403 Forbidden:</b> Token sai — kiểm tra CRON_RUN_TOKEN trong config.php</li>
  <li><b>500 Server Error:</b> Lỗi code — xem log hosting</li>
  <li><b>Timeout:</b> API bên thứ 3 chậm — tăng timeout lên 60s</li>
  <li><b>Không nhận tiền tự động:</b> Kiểm tra MBBANK_HISTORY_API_KEY có còn hạn không</li>
</ul>
</div>

</div></body></html>';
    exit;
}

// CLI mode: print config
echo json_encode(['success' => true, 'jobs' => $jobs], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
