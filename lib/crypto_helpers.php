<?php
/**
 * Helper convert VND ↔ USDT cho thanh toán Binance USDT TRC20.
 *
 * - Cache tỷ giá USDT/VND vào data/usdt_vnd_rate.json (TTL 5 phút) để
 *   tránh đụng rate limit free tier của CoinGecko (50 req/phút).
 * - Sinh "unique decimal" theo orderId để phân biệt các order cùng giá VND.
 *   USDT TRC20 không có memo nên matching bắt buộc dựa vào số tiền duy nhất.
 */

defined('CRYPTO_RATE_CACHE_TTL') || define('CRYPTO_RATE_CACHE_TTL', 300); // 5 phút
defined('CRYPTO_RATE_FALLBACK')  || define('CRYPTO_RATE_FALLBACK', 25000); // dùng khi CoinGecko down

/**
 * Lấy tỷ giá VND/1 USDT (có cache file).
 * @return array ['rate' => float, 'rate_at' => string, 'source' => 'cache'|'live'|'fallback']
 */
function cryptoGetUsdtVndRate(): array {
    $file = APP_ROOT . '/data/usdt_vnd_rate.json';
    if (!is_dir(dirname($file))) @mkdir(dirname($file), 0755, true);

    if (is_file($file)) {
        $prev = json_decode((string)@file_get_contents($file), true);
        if (is_array($prev) && !empty($prev['fetched_ts']) && (time() - (int)$prev['fetched_ts']) < CRYPTO_RATE_CACHE_TTL && !empty($prev['rate'])) {
            return [
                'rate'    => (float)$prev['rate'],
                'rate_at' => $prev['rate_at'] ?? date('c', (int)$prev['fetched_ts']),
                'source'  => 'cache',
            ];
        }
    }

    // CoinGecko free, không cần API key
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=vnd';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_USERAGENT      => 'HCLOU-CryptoRate/1.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $rate = 0;
    if ($body !== false && $code === 200) {
        $json = json_decode($body, true);
        $rate = (float)($json['tether']['vnd'] ?? 0);
    }

    if ($rate <= 0) {
        // Fallback: dùng cache cũ nếu có, hoặc default
        if (isset($prev) && is_array($prev) && !empty($prev['rate'])) {
            return [
                'rate'    => (float)$prev['rate'],
                'rate_at' => $prev['rate_at'] ?? date('c'),
                'source'  => 'fallback',
            ];
        }
        return [
            'rate'    => (float)CRYPTO_RATE_FALLBACK,
            'rate_at' => date('c'),
            'source'  => 'fallback',
        ];
    }

    $payload = [
        'rate'       => $rate,
        'rate_at'    => date('c'),
        'fetched_ts' => time(),
        'source_api' => 'coingecko',
    ];
    @file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE), LOCK_EX);
    return ['rate' => $rate, 'rate_at' => $payload['rate_at'], 'source' => 'live'];
}

/**
 * Convert số tiền VND của order sang USDT với "unique decimal" để match.
 *
 * Trick: với cùng giá 10.000đ và rate 25.000 → base = 0.4 USDT.
 * Cộng thêm (orderId % 1_000_000) / 1e6 → tạo đuôi 6 chữ số phân biệt
 * giữa các order cùng giá. Vd order #1234 → 0.401234, order #5678 → 0.405678.
 *
 * @return array [
 *   'usdt'         => float (số tiền chính xác user phải gửi),
 *   'usdt_base'    => float (số tiền theo tỷ giá, chưa cộng unique),
 *   'unique_tail'  => float (phần thập phân cộng vào để match),
 *   'rate'         => float,
 *   'rate_at'      => string,
 *   'rate_source'  => 'cache'|'live'|'fallback'
 * ]
 */
function cryptoConvertVndToUsdt(int $amountVnd, int $orderId): array {
    $r = cryptoGetUsdtVndRate();
    $rate = $r['rate'];
    if ($rate <= 0) $rate = CRYPTO_RATE_FALLBACK;

    $base = $amountVnd / $rate;

    // Unique tail: orderId mod 1M → tối đa 999.999 → /1e6 → max 0.999999
    $tail = ($orderId % 1000000) / 1000000;

    // Cộng tail nhưng nếu base có đuôi != 0 thì làm tròn xuống 1 chữ số trước
    // để tránh chồng lấn. Vd base = 0.408163 + tail 0.001234 = 0.409397 → khó debug.
    // Cách an toàn: round base xuống 1 decimal, cộng tail (6 decimal).
    $baseRound = floor($base * 10) / 10; // 0.408163 → 0.4
    $final     = $baseRound + $tail;     // 0.4 + 0.001234 = 0.401234

    // USDT TRC20 có 6 decimals on-chain. Round chính xác 6dp.
    $final = round($final, 6);

    return [
        'usdt'        => $final,
        'usdt_base'   => round($base, 6),
        'unique_tail' => round($tail, 6),
        'rate'        => $rate,
        'rate_at'     => $r['rate_at'],
        'rate_source' => $r['source'],
    ];
}

/**
 * Build URL QR-code cho TRC20 transfer.
 * Dùng public service tự render (qrserver.com) — chỉ encode payload, không gọi blockchain.
 * Payload chuẩn TRC20: tron:<address>?amount=<usdt>&token=USDT-TRC20
 * Hầu hết ví (TronLink, Trust Wallet, Binance app) parse được prefix `tron:` này.
 */
function cryptoBuildQrUrl(string $address, float $amount): string {
    // QR chỉ chứa địa chỉ ví trần (không URI scheme). Đây là format duy nhất
    // mọi ví TRC20 (TronLink, Trust Wallet, Binance, OKX, SafePal...) đều quét OK.
    // URI scheme "tron:" không phải chuẩn — nhiều ví parse fail.
    // User đọc số USDT từ UI text rồi nhập tay vào ví.
    // $amount giữ trong signature để tương thích call site, không dùng trong QR.
    return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=8&data=' . urlencode($address);
}
