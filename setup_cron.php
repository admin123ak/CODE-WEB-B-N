<?php
/**
 * Cron Jobs Setup Page — xem danh sách URL cần cấu hình trên cron-job.org
 * Tất cả jobs chạy qua HTTP cron_run.php, không cần exec() hay SSH.
 */
require_once __DIR__ . '/config.php';

$jobs = [
    'mbbank' => [
        'url'      => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=mbbank',
        'schedule' => '*/1 * * * *',
        'label'    => '🏦 MBBANK Auto-bank (mỗi 1 phút)',
        'priority' => 'QUAN TRỌNG NHẤT — tự động duyệt đơn thanh toán',
    ],
    'maintenance' => [
        'url'      => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=maintenance',
        'schedule' => '*/5 * * * *',
        'label'    => '🧹 Maintenance (mỗi 5 phút)',
        'priority' => 'Xoá key hết hạn, huỷ đơn quá 15 phút',
    ],
    'monitor' => [
        'url'      => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=monitor',
        'schedule' => '*/5 * * * *',
        'label'    => '📊 Cron Monitor (mỗi 5 phút)',
        'priority' => 'Cảnh báo lỗi hệ thống qua Telegram',
    ],
    'automation' => [
        'url'      => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=automation',
        'schedule' => '0 8 * * *',
        'label'    => '🤖 Automation Daily (8h sáng)',
        'priority' => 'Nhắc nhở thanh toán, báo cáo hàng ngày',
    ],
    'health' => [
        'url'      => rtrim(SITE_URL, '/') . '/cron_run.php?token=' . CRON_RUN_TOKEN . '&job=health',
        'schedule' => '0 9 * * *',
        'label'    => '🏥 Health Check (9h sáng)',
        'priority' => 'Kiểm tra toàn bộ hệ thống',
    ],
];

$siteUrl = htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cron Jobs Setup · HCLOU Server</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:#0d1117;color:#e6edf3;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding:24px}
.container{max-width:960px;margin:0 auto}
h1{font-size:24px;margin-bottom:4px}
.subtitle{color:#8b949e;font-size:14px;margin-bottom:24px}
.card{background:#161b22;border:1px solid #30363d;border-radius:16px;padding:24px;margin-bottom:16px}
.card h2{font-size:16px;color:#58a6ff;margin-bottom:12px}
.card h3{font-size:14px;margin:12px 0 6px}
.mono{font-family:monospace;background:#0d1117;padding:6px 10px;border-radius:8px;font-size:12px;word-break:break-all;display:block;margin:4px 0}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{padding:8px 12px;border-bottom:1px solid #21262d;text-align:left;font-size:13px}
th{color:#8b949e;font-weight:600}
.warnbox{background:rgba(234,179,8,.08);border:1px solid rgba(234,179,8,.2);color:#fde68a;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
.okbox{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#86efac;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
.errbox{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#fca5a5;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px}
.badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700}
.badge-green{background:rgba(34,197,94,.15);color:#4ade80}
.badge-red{background:rgba(239,68,68,.15);color:#f87171}
.btn{display:inline-block;padding:10px 20px;background:linear-gradient(135deg,#1f6feb,#8b5cf6);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none}
.btn:hover{opacity:.9}
a{color:#58a6ff}
ol,ul{padding-left:20px;line-height:2}
.job-url{cursor:pointer;transition:opacity .15s}
.job-url:hover{opacity:.8}
</style>
</head>
<body>
<div class="container">
<h1>🚀 Cron Jobs Setup</h1>
<p class="subtitle">HCLOU Server — <?= $siteUrl ?></p>

<div class="warnbox">
⚠️ Tất cả cron jobs chạy qua HTTP bằng <span class="mono" style="display:inline">cron_run.php</span>. Không cần <span class="mono" style="display:inline">exec()</span> hay SSH access. Chỉ cần cấu hình cron-job.org gọi các URL bên dưới.
</div>

<div class="card">
<h2>📋 Danh sách Cron Jobs</h2>
<table>
<tr><th>Job</th><th>Lịch chạy</th><th>URL cần gọi</th></tr>
<?php foreach ($jobs as $key => $job): ?>
<tr>
  <td>
    <b><?= htmlspecialchars($job['label']) ?></b><br>
    <small style="color:#8b949e"><?= htmlspecialchars($job['priority']) ?></small>
  </td>
  <td><span class="mono" style="display:inline"><?= htmlspecialchars($job['schedule']) ?></span></td>
  <td><span class="mono job-url" onclick="copyToClipboard('<?= htmlspecialchars($job['url'], ENT_QUOTES) ?>')" title="Click để copy"><?= htmlspecialchars($job['url']) ?></span></td>
</tr>
<?php endforeach ?>
</table>
</div>

<div class="card">
<h2>🔗 Hướng dẫn setup trên cron-job.org (miễn phí)</h2>
<ol>
  <li>Đăng ký tại <a href="https://cron-job.org" target="_blank">cron-job.org</a></li>
  <li>Vào <b>"Create cronjob"</b></li>
  <li>Dán URL từ bảng trên vào <b>"URL"</b></li>
  <li>Chọn <b>"Execution schedule"</b> theo lịch gợi ý</li>
  <li>Đảm bảo <b>"Request method" = GET</b></li>
  <li>Lưu và <b>bật (enable)</b> cron job</li>
  <li>Lặp lại cho mỗi job trong bảng</li>
</ol>
</div>

<div class="card">
<h2>🧪 Test nhanh cron jobs</h2>
<p style="color:#8b949e;margin-bottom:12px">Nhấn vào URL trong bảng trên để mở trong tab mới — nếu trả về JSON <span class="badge badge-green">success: true</span> là hoạt động bình thường.</p>
</div>

<div class="card">
<h2>🔒 Bảo mật</h2>
<ul>
  <li>Tất cả cron jobs yêu cầu token hợp lệ — không token = <b>403 Forbidden</b></li>
  <li>Token định nghĩa trong <span class="mono" style="display:inline">config.php</span> (CRON_RUN_TOKEN)</li>
  <li>MBBank poll có thêm secret riêng (MBBANK_POLL_SECRET)</li>
  <li>Không dùng exec() — an toàn trên shared hosting</li>
</ul>
</div>

<div class="card">
<h2>🆘 Xử lý sự cố</h2>
<ul>
  <li><b>403 Forbidden:</b> Token sai — kiểm tra CRON_RUN_TOKEN trong config.php</li>
  <li><b>500 Server Error:</b> Lỗi code — xem error log của hosting</li>
  <li><b>Timeout:</b> API bên thứ 3 chậm — tăng timeout lên 60s</li>
  <li><b>Không auto duyệt đơn:</b> Kiểm tra MBBANK_HISTORY_API_KEY còn hạn, cron MBBANK chạy mỗi 1 phút</li>
  <li><b>Đơn pending quá hạn không huỷ:</b> Kiểm tra cron Maintenance chạy mỗi 5 phút</li>
</ul>
</div>

<div class="card">
<h2>📌 Tóm tắt flow thanh toán tự động</h2>
<div class="okbox">
✅ <b>Flow hoàn chỉnh:</b><br>
1. User tạo đơn trên web → hệ thống gán key từ pool → hiện QR VietQR<br>
2. User chuyển khoản với nội dung mã đơn (ORDxxxxx)<br>
3. Cron MBBANK (mỗi 1 phút) gọi API Queenvps → phát hiện giao dịch mới → auto approve đơn<br>
4. User nhận thông báo Telegram + key active<br>
5. Nếu sau 15 phút chưa thanh toán → Maintenance tự huỷ đơn, key quay về pool
</div>
</div>

</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Visual feedback
    }).catch(function() {
        // Fallback
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    });
}
</script>
</body>
</html>
