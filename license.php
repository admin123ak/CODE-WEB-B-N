<?php
/**
 * ============================================
 *  HCLOU SERVER - LICENSE CLIENT
 *  Developer: TRAN VAN HOANG · Zalo: 0868641019
 *  ⚠️ KHÔNG xoá/sửa file này — code sẽ ngừng hoạt động.
 * ============================================
 *
 * Cơ chế: verify license với license server (domain riêng), ký HMAC.
 * Khi hợp lệ → define('HCLOU_LICENSE_OK', <khoá derive từ server>) để mở khoá getDB().
 * Grace period 7 ngày nếu server tạm sập.
 */

// --- Cấu hình nhúng cứng (obfuscate nhẹ để khó sửa nhanh) ---
// LICENSE_SERVER_URL: đổi sang domain license server thật của bạn.
if (!defined('LICENSE_SERVER_URL')) {
    define('LICENSE_SERVER_URL', base64_decode('aHR0cHM6Ly9saWNlbnNlLnRyYW52YW5ob2FuZy5jb20=')); // https://license.tranvanhoang.com
}
// LICENSE_PUBLIC_SECRET: PHẢI khớp LS_SIGNING_SECRET trên license server.
if (!defined('LICENSE_PUBLIC_SECRET')) {
    define('LICENSE_PUBLIC_SECRET', 'CHANGE_ME_must_match_server_LS_SIGNING_SECRET');
}

if (!function_exists('hclou_lic_cache_path')) {
    function hclou_lic_cache_path() {
        $d = (defined('APP_ROOT') ? APP_ROOT : __DIR__) . '/data';
        if (!is_dir($d)) @mkdir($d, 0755, true);
        return $d . '/.lic';
    }
}

// Verify HMAC sig của response server
if (!function_exists('hclou_lic_verify_sig')) {
    function hclou_lic_verify_sig(array $payload) {
        if (empty($payload['sig'])) return false;
        $sig = $payload['sig'];
        unset($payload['sig']);
        ksort($payload);
        $base = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $calc = hash_hmac('sha256', $base, LICENSE_PUBLIC_SECRET);
        return hash_equals($calc, (string)$sig);
    }
}

// Trang chặn khi license sai
if (!function_exists('hclou_license_die')) {
    function hclou_license_die($reason = '') {
        http_response_code(403);
        $r = htmlspecialchars((string)$reason, ENT_QUOTES, 'UTF-8');
        $map = [
            'not_found'    => 'License key không tồn tại.',
            'empty_key'    => 'Chưa nhập License key.',
            'suspended'    => 'License đã bị khoá.',
            'expired'      => 'License đã hết hạn.',
            'domain_limit' => 'Vượt số domain cho phép của license.',
            'bad_sig'      => 'Phản hồi license server không hợp lệ.',
            'offline'      => 'Không kết nối được license server (quá hạn grace).',
        ];
        $msg = $map[$reason] ?? ('License không hợp lệ (' . $r . ').');
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><title>License</title>'
           . '<style>body{font-family:-apple-system,Segoe UI,sans-serif;background:#0b1020;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}'
           . '.b{max-width:420px;text-align:center;background:#161b22;border:1px solid #30363d;border-radius:18px;padding:34px}'
           . '.i{font-size:50px;margin-bottom:14px}h1{font-size:20px;margin:0 0 10px}p{color:#9fb2cf;line-height:1.6;font-size:14px}'
           . 'a{color:#67e8f9}</style></head><body><div class="b"><div class="i">🔒</div>'
           . '<h1>License không hợp lệ</h1><p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')
           . '<br><br>Liên hệ <b>Zalo 0868641019</b> để kích hoạt.</p></div></body></html>';
        exit;
    }
}

// Cổng kiểm tra license — gọi 1 lần trong config.php
if (!function_exists('hclou_license_gate')) {
    function hclou_license_gate() {
        if (defined('HCLOU_LICENSE_OK')) return; // đã pass trong request này

        $key    = defined('LICENSE_KEY') ? LICENSE_KEY : '';
        // Domain: web lấy từ HTTP_HOST; CLI (cron) lấy từ SITE_URL để cùng 1 domain, không tốn slot.
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '' && defined('SITE_URL')) { $host = parse_url(SITE_URL, PHP_URL_HOST) ?: ''; }
        $domain = strtolower(preg_replace('/^www\./', '', (string)$host));
        $ip     = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $ver    = '';
        $vf = (defined('APP_ROOT') ? APP_ROOT : __DIR__) . '/version.json';
        if (is_file($vf)) { $j = json_decode((string)@file_get_contents($vf), true); $ver = $j['version'] ?? ''; }

        $cacheFile = hclou_lic_cache_path();
        $now = time();
        $cache = is_file($cacheFile) ? json_decode((string)@file_get_contents($cacheFile), true) : null;

        // 1) Cache còn tươi (<24h) + hợp lệ → pass ngay (đỡ gọi server mỗi request)
        if (is_array($cache) && !empty($cache['valid']) && !empty($cache['checked_at'])
            && ($now - (int)$cache['checked_at'] < 86400)
            && !empty($cache['unlock'])) {
            define('HCLOU_LICENSE_OK', $cache['unlock']);
            return;
        }

        // 2) Gọi server verify
        $resp = hclou_lic_http(LICENSE_SERVER_URL . '/api.php', [
            'action' => 'verify', 'license_key' => $key, 'domain' => $domain, 'ip' => $ip, 'version' => $ver,
        ]);

        if ($resp !== null && is_array($resp)) {
            if (!hclou_lic_verify_sig($resp)) hclou_license_die('bad_sig');
            if (empty($resp['valid'])) {
                @unlink($cacheFile);
                hclou_license_die($resp['reason'] ?? 'invalid');
            }
            // Hợp lệ → derive unlock từ sig (không hard-code được)
            $unlock = substr(hash_hmac('sha256', 'unlock|' . ($resp['ts'] ?? '') . '|' . $domain, LICENSE_PUBLIC_SECRET), 0, 32);
            $newCache = [
                'valid'      => true,
                'checked_at' => $now,
                'expires_at' => $resp['expires_at'] ?? '',
                'latest'     => $resp['latest_version'] ?? '',
                'unlock'     => $unlock,
            ];
            @file_put_contents($cacheFile, json_encode($newCache), LOCK_EX);
            define('HCLOU_LICENSE_OK', $unlock);
            return;
        }

        // 3) Server không phản hồi → grace 7 ngày nếu cache cũ vẫn valid
        if (is_array($cache) && !empty($cache['valid']) && !empty($cache['checked_at'])
            && ($now - (int)$cache['checked_at'] < 7 * 86400)
            && !empty($cache['unlock'])) {
            define('HCLOU_LICENSE_OK', $cache['unlock']);
            return;
        }

        hclou_license_die('offline');
    }
}

// HTTP POST tới license server, trả mảng JSON hoặc null
if (!function_exists('hclou_lic_http')) {
    function hclou_lic_http($url, array $params) {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false || $err) return null;
        $j = json_decode($body, true);
        return is_array($j) ? $j : null;
    }
}
