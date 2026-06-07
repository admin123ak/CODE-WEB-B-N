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
    define('LICENSE_SERVER_URL', base64_decode('aHR0cHM6Ly90ZWFtY3JhY2subGlua3BjLm5ldA==')); // https://teamcrack.linkpc.net
}
// LICENSE_PUBLIC_SECRET: PHẢI khớp LS_SIGNING_SECRET trên license server.
if (!defined('LICENSE_PUBLIC_SECRET')) {
    define('LICENSE_PUBLIC_SECRET', 'fbefa6242e8fc94fcfeac66ba363280d8d68594b35b50ae538062a0090358f42');
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

// Ghi LICENSE_KEY (+ server url nếu có) vào config.local.php
if (!function_exists('hclou_license_write_key')) {
    function hclou_license_write_key($key, $serverUrl = '') {
        $cfg = (defined('APP_ROOT') ? APP_ROOT : __DIR__) . '/config.local.php';
        if (!is_file($cfg) || !is_writable($cfg)) return false;
        $src = file_get_contents($cfg);

        $setDefine = function ($src, $name, $val) {
            $rep = "define('" . $name . "', " . var_export($val, true) . ");";
            $pat = "/define\\(\\s*['\"]" . preg_quote($name, '/') . "['\"]\\s*,\\s*.*?\\);/s";
            if (preg_match($pat, $src)) {
                return preg_replace($pat, $rep, $src, 1);
            }
            // chưa có → chèn sau <?php
            if (preg_match('/^<\?php\s*\n?/', $src, $m)) {
                return $m[0] . $rep . "\n" . substr($src, strlen($m[0]));
            }
            return $src . "\n" . $rep . "\n";
        };

        $src = $setDefine($src, 'LICENSE_KEY', $key);
        if ($serverUrl !== '') $src = $setDefine($src, 'LICENSE_SERVER_URL', $serverUrl);
        return file_put_contents($cfg, $src, LOCK_EX) !== false;
    }
}

// Trang nhập license (khi chưa có key). Xử lý POST → verify → lưu → reload.
if (!function_exists('hclou_license_activate')) {
    function hclou_license_activate($domain, $ip, $ver, $presetErr = '') {
        $err = $presetErr;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_key'])) {
            $newKey = trim($_POST['activate_key']);
            $newUrl = trim($_POST['activate_url'] ?? '');
            $srv = $newUrl !== '' ? $newUrl : (defined('LICENSE_SERVER_URL') ? LICENSE_SERVER_URL : '');
            if ($newKey === '') {
                $err = 'Vui lòng nhập license key.';
            } else {
                // verify thử với key vừa nhập
                $resp = hclou_lic_http(rtrim($srv, '/') . '/api.php', [
                    'action' => 'verify', 'license_key' => $newKey, 'domain' => $domain, 'ip' => $ip, 'version' => $ver,
                ]);
                if ($resp === null) {
                    $err = 'Không kết nối được license server. Kiểm tra lại URL.';
                } elseif (!hclou_lic_verify_sig($resp)) {
                    $err = 'Phản hồi license server không hợp lệ (sai secret).';
                } elseif (empty($resp['valid'])) {
                    $map = ['not_found'=>'Key không tồn tại','suspended'=>'Key đã bị khoá','expired'=>'Key hết hạn','domain_limit'=>'Vượt số domain cho phép'];
                    $err = $map[$resp['reason'] ?? ''] ?? 'License không hợp lệ.';
                } else {
                    // OK → lưu vào config.local.php
                    if (hclou_license_write_key($newKey, $newUrl)) {
                        // xoá cache cũ để lần sau verify lại sạch
                        @unlink(hclou_lic_cache_path());
                        header('Location: ' . (strtok($_SERVER['REQUEST_URI'], '?') ?: '/'));
                        exit;
                    }
                    $err = 'Không ghi được config.local.php (kiểm tra quyền ghi file).';
                }
            }
        }

        $curUrl = defined('LICENSE_SERVER_URL') ? LICENSE_SERVER_URL : '';
        http_response_code(200);
        header('Content-Type: text/html; charset=utf-8');
        $e = htmlspecialchars($err, ENT_QUOTES, 'UTF-8');
        $cu = htmlspecialchars($curUrl, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kích hoạt License</title>'
           . '<style>*{box-sizing:border-box}body{font-family:-apple-system,Segoe UI,sans-serif;background:radial-gradient(circle at 20% 10%,rgba(37,99,235,.25),transparent 30%),#0b1020;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}'
           . '.b{width:440px;max-width:100%;background:#161b22;border:1px solid #30363d;border-radius:18px;padding:30px}'
           . '.i{width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,#2563eb,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px}'
           . 'h1{text-align:center;font-size:20px;margin:0 0 4px}.s{text-align:center;color:#8b949e;font-size:13px;margin-bottom:20px}'
           . 'label{display:block;font-size:12px;color:#9fb2cf;margin:12px 0 5px;font-weight:700}'
           . 'input{width:100%;padding:12px;background:#0d1117;border:1px solid #30363d;border-radius:10px;color:#e6edf3;font-size:14px}'
           . 'button{width:100%;margin-top:18px;padding:13px;border:0;border-radius:11px;background:linear-gradient(135deg,#2563eb,#06b6d4);color:#fff;font-weight:800;cursor:pointer;font-size:15px}'
           . '.err{background:rgba(239,68,68,.13);border:1px solid rgba(239,68,68,.35);color:#fca5a5;padding:10px;border-radius:10px;margin-bottom:12px;font-size:13px}'
           . '.hint{color:#6e7681;font-size:12px;margin-top:14px;text-align:center}</style></head><body>'
           . '<form class="b" method="POST">'
           . '<div class="i">🔑</div><h1>Kích hoạt License</h1><div class="s">Nhập license key để bắt đầu sử dụng</div>'
           . ($e ? '<div class="err">⚠️ ' . $e . '</div>' : '')
           . '<label>License Key</label><input name="activate_key" placeholder="HCLOU-XXXX-XXXX-XXXX" autofocus required>'
           . '<label>License Server URL</label><input name="activate_url" value="' . $cu . '" placeholder="https://license.tranvanhoang.com">'
           . '<button type="submit">Kích hoạt</button>'
           . '<div class="hint">Chưa có key? Liên hệ <b>Zalo 0868641019</b></div>'
           . '</form></body></html>';
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

        // CHƯA CÓ LICENSE KEY → hiện trang nhập key (chỉ với web, CLI thì bỏ qua)
        if ($key === '' && php_sapi_name() !== 'cli') {
            hclou_license_activate($domain, $ip, $ver);
            // hclou_license_activate() hoặc redirect (đã lưu key) hoặc render form rồi exit
        }

        $cacheFile = hclou_lic_cache_path();
        $now = time();
        $cache = is_file($cacheFile) ? json_decode((string)@file_get_contents($cacheFile), true) : null;

        // 1) Cache còn tươi (<5 phút) + hợp lệ → pass ngay (đỡ gọi server mỗi request).
        //    5 phút: admin xoá/khoá license thì client phát hiện & bắt nhập lại trong ~5 phút.
        if (is_array($cache) && !empty($cache['valid']) && !empty($cache['checked_at'])
            && ($now - (int)$cache['checked_at'] < 300)
            && !empty($cache['unlock'])) {
            define('HCLOU_LICENSE_OK', $cache['unlock']);
            return;
        }

        // 2) Gọi server verify
        $resp = hclou_lic_http(LICENSE_SERVER_URL . '/api.php', [
            'action' => 'verify', 'license_key' => $key, 'domain' => $domain, 'ip' => $ip, 'version' => $ver,
        ]);

        if ($resp !== null && is_array($resp)) {
            if (!hclou_lic_verify_sig($resp)) {
                if (php_sapi_name() !== 'cli') hclou_license_activate($domain, $ip, $ver, 'Sai secret giữa client và server. Kiểm tra LICENSE_PUBLIC_SECRET / URL server.');
                hclou_license_die('bad_sig');
            }
            if (empty($resp['valid'])) {
                @unlink($cacheFile);
                $emap = ['not_found'=>'Key không tồn tại','suspended'=>'Key đã bị khoá','expired'=>'Key đã hết hạn','domain_limit'=>'Vượt số domain cho phép','empty_key'=>'Chưa nhập key'];
                $emsg = $emap[$resp['reason'] ?? ''] ?? 'License không hợp lệ.';
                if (php_sapi_name() !== 'cli') hclou_license_activate($domain, $ip, $ver, $emsg);
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

        // Server chết + không có cache hợp lệ → web cho nhập lại / sửa URL, CLI thì die
        if (php_sapi_name() !== 'cli') hclou_license_activate($domain, $ip, $ver, 'Không kết nối được license server. Kiểm tra URL hoặc thử lại.');
        hclou_license_die('offline');
    }
}
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
