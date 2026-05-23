<?php
require_once '../config.php';
require_once __DIR__ . '/../lib/crypto_helpers.php';
require_once __DIR__ . '/../lib/balance_helpers.php';
require_once __DIR__ . '/../lib/topup_helpers.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateRules = [
    'auth' => [60, 60], 'games' => [120, 60], 'packages' => [120, 60],
    'create_order' => [8, 60], 'pending_orders' => [40, 60], 'order_status' => [80, 60], 'my_keys' => [80, 60],
    'get_free_link' => [10, 60], 'claim_free_key' => [10, 60],
    'reset_key' => [12, 60], 'delete_key' => [20, 60], 'search_key' => [60, 60],
    'my_orders' => [60, 60], 'profile_stats' => [60, 60],
    'free_key_status' => [30, 60], 'daily_free_key' => [5, 86400],
    'payment_options' => [120, 60],
    'me_balance' => [120, 60], 'topup_options' => [120, 60],
    'topup_create' => [6, 60], 'balance_history' => [60, 60],
    'topup_history_card' => [60, 60],
];
if (isset($rateRules[$action])) { [$lim,$win] = $rateRules[$action]; rateLimit('api_'.$action, $lim, $win, $ip); }

// Xác thực Telegram Mini App initData cho các action cần user.
$tgVerifiedUser = null;
$initData = $_POST['init_data'] ?? $_GET['init_data'] ?? '';
if ($initData) $tgVerifiedUser = telegramUserFromInitData($initData);

function makeAppToken($telegramId) {
    $ts = time();
    $payload = $telegramId . '|' . $ts;
    return base64_encode($payload . '|' . hash_hmac('sha256', $payload, BOT_TOKEN));
}
function verifyAppToken($token) {
    if (!$token) return 0;
    $raw = base64_decode($token, true);
    if (!$raw) return 0;
    $parts = explode('|', $raw);
    if (count($parts) !== 3) return 0;
    [$telegramId, $ts, $sig] = $parts;
    if (!ctype_digit((string)$telegramId) || !ctype_digit((string)$ts)) return 0;
    if (time() - (int)$ts > 86400) return 0;
    $calc = hash_hmac('sha256', $telegramId . '|' . $ts, BOT_TOKEN);
    return hash_equals($calc, $sig) ? (int)$telegramId : 0;
}

$user = null;
$tokenTelegramId = verifyAppToken($_POST['app_token'] ?? $_GET['app_token'] ?? '');
// SECURITY: chỉ tin tưởng telegram_id đã xác thực qua initData (HMAC Telegram) hoặc app_token (HMAC server).
// KHÔNG dùng raw telegram_id từ POST/GET vì spoof được — gây IDOR truy cập user khác.
$lookupTelegramId = $tgVerifiedUser['id'] ?? $tokenTelegramId;
if ($lookupTelegramId) {
    $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->execute([$lookupTelegramId]);
    $user = $stmt->fetch();
}

switch ($action) {

    // ===== ĐĂNG NHẬP / TẠO USER =====
    case 'auth':
        // SECURITY: bắt buộc initData verified — không cho fallback từ POST raw.
        if (!$tgVerifiedUser || empty($tgVerifiedUser['id'])) {
            jsonResponse(['error' => 'Cần mở qua Telegram Mini App (init_data không hợp lệ)'], 401);
        }
        $tg_id = (int)$tgVerifiedUser['id'];
        $username = $tgVerifiedUser['username'] ?? '';
        $fullname = trim(($tgVerifiedUser['first_name'] ?? '') . ' ' . ($tgVerifiedUser['last_name'] ?? ''));
        $avatar = $tgVerifiedUser['photo_url'] ?? '';
        
        $stmt = $db->prepare("SELECT * FROM users WHERE telegram_id = ?");
        $stmt->execute([$tg_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $db->prepare("UPDATE users SET telegram_username=?, full_name=?, avatar_url=? WHERE telegram_id=?")
               ->execute([$username, $fullname, $avatar, $tg_id]);
            $existing['telegram_username'] = $username;
            $existing['full_name'] = $fullname;
            jsonResponse(['success' => true, 'user' => $existing, 'app_token' => makeAppToken($tg_id)]);
        } else {
            $db->prepare("INSERT INTO users (telegram_id, telegram_username, full_name, avatar_url) VALUES (?,?,?,?)")
               ->execute([$tg_id, $username, $fullname, $avatar]);
            $user_id = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            jsonResponse(['success' => true, 'user' => $stmt->fetch(), 'app_token' => makeAppToken($tg_id)]);
        }

    // ===== DANH SÁCH GAME =====
    case 'games':
        $stmt = $db->query("SELECT * FROM games WHERE is_active=1 ORDER BY sort_order ASC");
        jsonResponse(['success' => true, 'games' => $stmt->fetchAll()]);

    // ===== GÓI THEO GAME =====
    case 'packages':
        $game_id = $_GET['game_id'] ?? 0;
        $stmt = $db->prepare("SELECT * FROM packages WHERE game_id=? AND is_active=1 ORDER BY days ASC");
        $stmt->execute([$game_id]);
        $packages = $stmt->fetchAll();
        $freeStmt = $db->prepare("SELECT fk.*, p.key_type FROM free_keys fk JOIN packages p ON fk.package_id=p.id LEFT JOIN free_key_claims c ON c.free_key_id=fk.id WHERE fk.game_id=? AND fk.is_active=1 AND fk.expire_at > NOW() AND c.id IS NULL ORDER BY fk.created_at DESC LIMIT 1");
        $freeStmt->execute([$game_id]);
        $free = $freeStmt->fetch();
        if ($free) {
            array_unshift($packages, [
                'id' => 'free',
                'name' => 'Get Key Free',
                'days' => (int)$free['days'],
                'price' => 0,
                'key_type' => $free['key_type'],
                'is_free' => 1,
                'free_key_id' => (int)$free['id']
            ]);
        }
        // Nếu Binance bật → gửi kèm tỷ giá để Mini App hiện "10.000đ | ≈ 0.408 USDT".
        // Tỷ giá lấy từ cache (5p), không gọi CoinGecko mỗi lần list package.
        $usdtVndRate = null;
        if (defined('CRYPTO_AUTO_APPROVE_ENABLED') && CRYPTO_AUTO_APPROVE_ENABLED
            && defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '') {
            try {
                $r = cryptoGetUsdtVndRate();
                $usdtVndRate = (float)$r['rate'];
            } catch (Throwable $e) { /* ignore — frontend tự fallback ẩn USDT */ }
        }
        jsonResponse(['success' => true, 'packages' => $packages, 'usdt_vnd_rate' => $usdtVndRate]);

    // ===== DANH SÁCH PHƯƠNG THỨC THANH TOÁN KHẢ DỤNG =====
    // Frontend gọi endpoint này để biết nên hiện option Binance hay không.
    // Binance chỉ available khi admin đã bật cờ + nhập địa chỉ ví.
    case 'payment_options':
        $mbbankOn = defined('MBBANK_AUTO_APPROVE_ENABLED') ? (bool)MBBANK_AUTO_APPROVE_ENABLED : true;
        $binanceConfigured = defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '';
        $binanceOn = defined('CRYPTO_AUTO_APPROVE_ENABLED') && CRYPTO_AUTO_APPROVE_ENABLED && $binanceConfigured;
        $cardConfigured = defined('DOITHE_PARTNER_KEY') && DOITHE_PARTNER_KEY !== '' && defined('DOITHE_PARTNER_ID') && DOITHE_PARTNER_ID !== '';
        $cardOn = $cardConfigured;
        jsonResponse([
            'success' => true,
            'options' => [
                'mbbank'  => $mbbankOn,
                'binance' => $binanceOn,
                'card'    => $cardOn,
            ],
        ]);

    // Đọc số dư ví user.
    case 'me_balance':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $bal = balanceGet($db, (int)$user['id']);
        jsonResponse(['success' => true, 'balance' => $bal]);

    // Option nạp ví — frontend dùng để hiện 3 nút bank/binance/card trong modal Nạp.
    case 'topup_options':
        $mbbankOn = defined('MBBANK_AUTO_APPROVE_ENABLED') ? (bool)MBBANK_AUTO_APPROVE_ENABLED : false;
        $binanceConfigured = defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '';
        $binanceOn = defined('CRYPTO_AUTO_APPROVE_ENABLED') && CRYPTO_AUTO_APPROVE_ENABLED && $binanceConfigured;
        $cardConfigured = defined('DOITHE_PARTNER_KEY') && DOITHE_PARTNER_KEY !== '' && defined('DOITHE_PARTNER_ID') && DOITHE_PARTNER_ID !== '';
        $cardOn = $cardConfigured;
        // % chiết khấu doithe.vn từng nhà mạng — frontend dùng để show "→ Xđ ví" khi chọn.
        [$rV,] = cardRateForTelco('VIETTEL');
        [$rM,] = cardRateForTelco('MOBIFONE');
        [$rN,] = cardRateForTelco('VINAPHONE');
        jsonResponse([
            'success' => true,
            'options' => ['mbbank'=>$mbbankOn, 'binance'=>$binanceOn, 'card'=>$cardOn],
            'card_rates' => ['VIETTEL'=>$rV, 'MOBIFONE'=>$rM, 'VINAPHONE'=>$rN],
        ]);

    // Tạo topup_request mới (chưa duyệt — cron/callback sẽ duyệt sau).
    case 'topup_create':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);

        $method = $_POST['method'] ?? '';
        if (!in_array($method, ['mbbank','binance','card'], true)) {
            jsonResponse(['error' => 'Method không hợp lệ'], 400);
        }

        // Anti-spam: tối đa 3 topup pending cùng lúc
        $pendCount = $db->prepare("SELECT COUNT(*) FROM topup_requests WHERE user_id=? AND status='pending'");
        $pendCount->execute([$user['id']]);
        if ((int)$pendCount->fetchColumn() >= 3) {
            jsonResponse(['error' => 'Bạn đang có quá nhiều yêu cầu nạp đang chờ, hoàn tất hoặc chờ hết hạn'], 429);
        }

        try {
            if ($method === 'mbbank') {
                $amount = (int)($_POST['amount'] ?? 0);
                if ($amount < 10000) jsonResponse(['error' => 'Tối thiểu 10.000đ'], 400);
                if ($amount > 50000000) jsonResponse(['error' => 'Tối đa 50.000.000đ'], 400);
                $r = topupCreateRequest($db, (int)$user['id'], 'mbbank', (float)$amount);
                jsonResponse([
                    'success'          => true,
                    'method'           => 'mbbank',
                    'topup_id'         => $r['id'],
                    'amount_requested' => $amount,
                    'unique_code'      => $r['unique_code'],
                    'bank_name'        => defined('BANK_NAME') ? BANK_NAME : '',
                    'bank_account'     => defined('BANK_ACCOUNT') ? BANK_ACCOUNT : '',
                    'bank_owner'       => defined('BANK_OWNER') ? BANK_OWNER : '',
                    'vietqr_url'       => function_exists('buildVietQrUrl') ? buildVietQrUrl($amount, $r['unique_code']) : '',
                ]);
            }

            if ($method === 'binance') {
                if (!defined('CRYPTO_AUTO_APPROVE_ENABLED') || !CRYPTO_AUTO_APPROVE_ENABLED || !defined('USDT_TRC20_ADDRESS') || USDT_TRC20_ADDRESS === '') {
                    jsonResponse(['error' => 'Binance USDT đang tạm khoá'], 400);
                }
                $amount = (int)($_POST['amount'] ?? 0);
                if ($amount < 10000) jsonResponse(['error' => 'Tối thiểu 10.000đ'], 400);
                if ($amount > 50000000) jsonResponse(['error' => 'Tối đa 50.000.000đ'], 400);

                // Insert trước để có topup_id, sau đó tính crypto_amount unique decimal
                $r = topupCreateRequest($db, (int)$user['id'], 'binance', (float)$amount);
                $conv = cryptoConvertVndToUsdt($amount, $r['id']);
                $db->prepare("UPDATE topup_requests SET crypto_amount=?, usdt_vnd_rate=? WHERE id=?")
                   ->execute([$conv['usdt'], $conv['rate'], $r['id']]);
                $qrUrl = function_exists('cryptoBuildQrUrl') ? cryptoBuildQrUrl(USDT_TRC20_ADDRESS, $conv['usdt']) : '';
                jsonResponse([
                    'success'          => true,
                    'method'           => 'binance',
                    'topup_id'         => $r['id'],
                    'amount_requested' => $amount,
                    'crypto_amount'    => $conv['usdt'],
                    'crypto_address'   => USDT_TRC20_ADDRESS,
                    'crypto_qr_url'    => $qrUrl,
                    'usdt_vnd_rate'    => $conv['rate'],
                ]);
            }

            if ($method === 'card') {
                if (!defined('DOITHE_PARTNER_KEY') || DOITHE_PARTNER_KEY === '' || !defined('DOITHE_PARTNER_ID') || DOITHE_PARTNER_ID === '') {
                    jsonResponse(['error' => 'Nạp thẻ chưa cấu hình'], 400);
                }
                $telco = strtoupper(trim($_POST['card_telco'] ?? ''));
                $face  = (int)($_POST['card_face_value'] ?? 0);
                $serial = trim($_POST['card_serial'] ?? '');
                $code   = trim($_POST['card_code'] ?? '');
                if (!in_array($telco, ['VIETTEL','MOBIFONE','VINAPHONE'], true)) jsonResponse(['error' => 'Nhà mạng không hợp lệ'], 400);
                if (!in_array($face, [10000,20000,30000,50000,100000,200000,300000,500000,1000000], true)) jsonResponse(['error' => 'Mệnh giá không hợp lệ'], 400);
                if ($serial === '' || $code === '') jsonResponse(['error' => 'Nhập đủ Serial + Mã thẻ'], 400);
                if (strlen($serial) > 50 || strlen($code) > 50) jsonResponse(['error' => 'Serial/mã thẻ quá dài'], 400);

                $request_id = 'CARD' . date('YmdHis') . substr(bin2hex(random_bytes(3)), 0, 5);
                $r = topupCreateRequest($db, (int)$user['id'], 'card', (float)$face, [
                    'card_telco'         => $telco,
                    'card_face_value'    => $face,
                    'card_serial'        => $serial,
                    'card_code'          => $code,
                    'provider_request_id'=> $request_id,
                ]);

                // Gọi doithe.vn (sync). Hard-reject CHỈ khi doithe nói thẻ sai (status=3).
                // Status 0 (network/parse error) / 4 (provider tạm lỗi) → giữ pending, callback sẽ xử lý sau.
                $api = callDoitheCharge($request_id, $telco, $face, $serial, $code);
                $db->prepare("UPDATE topup_requests SET provider_response=? WHERE id=?")
                   ->execute([$api['raw'], $r['id']]);

                $pStatus = (int)$api['status'];
                // Hard-reject các status doithe trả về chắc chắn fail, callback sẽ KHÔNG về:
                //   3   = card invalid / used
                //   100-199 = lỗi merchant/auth/config (vd 102 MERCHANT_NOT_EXISTED_OR_OFF)
                // Nếu giữ pending các case này → topup card stuck mãi vì không có callback.
                $hardFailStatuses = [3];
                $isMerchantErr = ($pStatus >= 100 && $pStatus <= 199);
                if (in_array($pStatus, $hardFailStatuses, true) || $isMerchantErr) {
                    topupReject($db, $r['id'], 'doithe.vn status=' . $pStatus . ': ' . $api['message'], $api['raw']);
                    $userMsg = $isMerchantErr
                        ? 'Hệ thống nạp thẻ tạm gặp sự cố cấu hình. Vui lòng báo admin.'
                        : ('doithe.vn từ chối thẻ: ' . ($api['message'] ?: 'thẻ sai/đã sử dụng'));
                    jsonResponse(['error' => $userMsg], 400);
                }
                if (!$api['ok'] && $pStatus !== 99) {
                    // Network/parse error hoặc provider lỗi tạm thời — giữ pending, đợi callback
                    jsonResponse([
                        'success'         => true,
                        'method'          => 'card',
                        'topup_id'        => $r['id'],
                        'card_face_value' => $face,
                        'provider_status' => $pStatus,
                        'message'         => 'Đã gửi thẻ. doithe.vn chưa phản hồi rõ (' . ($api['message'] ?: 'lỗi tạm thời') . '). Đơn đang chờ — kết quả sẽ về sau qua callback.',
                    ]);
                }

                jsonResponse([
                    'success'           => true,
                    'method'            => 'card',
                    'topup_id'          => $r['id'],
                    'card_face_value'   => $face,
                    'provider_status'   => $api['status'],  // 99=pending, 1=success
                    'message'           => 'Đã gửi thẻ lên hệ thống. Kết quả sẽ về sau 1-3 phút.',
                ]);
            }
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
        jsonResponse(['error' => 'Method chưa hỗ trợ'], 400);

    // Lịch sử ví user (50 dòng gần nhất).
    case 'balance_history':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $items = balanceHistory($db, (int)$user['id'], 50);
        jsonResponse(['success' => true, 'items' => $items]);

    // Lịch sử nạp thẻ gần đây (10 dòng) — hiện trong form nạp card.
    case 'topup_history_card':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $stmt = $db->prepare("SELECT id, status, card_telco, card_face_value, amount_credited, note,
                                     provider_response, created_at, processed_at
                              FROM topup_requests
                              WHERE user_id=? AND method='card'
                              ORDER BY id DESC LIMIT 10");
        $stmt->execute([(int)$user['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Trích message từ provider_response để hiện ngắn gọn.
        foreach ($rows as &$r) {
            $msg = '';
            if (!empty($r['provider_response'])) {
                $j = json_decode($r['provider_response'], true);
                if (is_array($j)) {
                    $msg = (string)($j['message'] ?? $j['msg'] ?? '');
                    $st = isset($j['status']) ? (int)$j['status'] : null;
                    if ($st !== null) $msg = '[s' . $st . '] ' . $msg;
                }
            }
            $r['provider_message'] = $msg;
            unset($r['provider_response']); // không gửi raw full ra frontend
        }
        jsonResponse(['success' => true, 'items' => $rows]);

    // Mua key trừ thẳng từ ví user (KHÔNG qua MBBank/Binance/Card).
    case 'buy_with_balance':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);

        $game_id = (int)($_POST['game_id'] ?? 0);
        $package_id = (int)($_POST['package_id'] ?? 0);

        $pkg = $db->prepare("SELECT p.*, g.name as game_name FROM packages p JOIN games g ON p.game_id=g.id WHERE p.id=? AND p.game_id=? AND p.is_active=1 AND g.is_active=1");
        $pkg->execute([$package_id, $game_id]);
        $package = $pkg->fetch();
        if (!$package) jsonResponse(['error' => 'Gói không tồn tại'], 404);

        $price = (float)$package['price'];
        $curBal = balanceGet($db, (int)$user['id']);
        if ($curBal < $price) {
            jsonResponse(['error' => 'Số dư không đủ: cần ' . number_format($price) . 'đ, có ' . number_format($curBal) . 'đ'], 400);
        }

        // Reserve key + tạo order + debit ví trong cùng transaction-ish:
        // balanceDebit có tx riêng — ta gọi balanceDebit TRƯỚC, nếu fail throws,
        // không tốn key. Sau đó tạo order + assign key.
        $order_code = generateOrderCode();
        $db->beginTransaction();
        try {
            $keyStmt = $db->prepare("SELECT id, key_code FROM `keys` WHERE status='available' AND game_id=? AND package_id=? ORDER BY id ASC LIMIT 1 FOR UPDATE");
            $keyStmt->execute([$game_id, $package_id]);
            $poolKey = $keyStmt->fetch();
            if (!$poolKey) {
                $db->rollBack();
                jsonResponse(['error' => 'Hết key cho gói này'], 400);
            }

            $db->prepare("INSERT INTO orders (order_code, user_id, game_id, package_id, amount, payment_method, status, approved_at, approved_by) VALUES (?,?,?,?,?,?,'approved', NOW(), 'balance')")
               ->execute([$order_code, $user['id'], $game_id, $package_id, $price, 'balance']);
            $order_id = (int)$db->lastInsertId();

            $expire = date('Y-m-d H:i:s', strtotime('+' . (int)$package['days'] . ' days'));
            $db->prepare("UPDATE `keys` SET status='active', user_id=?, order_id=?, days=?, start_at=NOW(), expire_at=? WHERE id=?")
               ->execute([$user['id'], $order_id, $package['days'], $expire, $poolKey['id']]);
            $key_code = $poolKey['key_code'];

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            jsonResponse(['error' => 'Lỗi tạo đơn: ' . $e->getMessage()], 500);
        }

        // Debit ví (atomic, riêng tx). Nếu fail thì refund key.
        try {
            $debit = balanceDebit($db, (int)$user['id'], $price, 'purchase', 'order', $order_id, 'Mua key gói #' . $package_id);
        } catch (Throwable $e) {
            // Rollback: huỷ order, trả key về pool
            try {
                $db->beginTransaction();
                $db->prepare("UPDATE `keys` SET status='available', user_id=NULL, order_id=NULL, start_at=NULL, expire_at=NULL WHERE id=?")
                   ->execute([$poolKey['id']]);
                $db->prepare("UPDATE orders SET status='cancelled', admin_note=? WHERE id=?")
                   ->execute(['Trừ ví fail: ' . $e->getMessage(), $order_id]);
                $db->commit();
            } catch (Throwable $e2) { if ($db->inTransaction()) $db->rollBack(); }
            jsonResponse(['error' => 'Trừ ví thất bại: ' . $e->getMessage()], 400);
        }

        jsonResponse([
            'success'       => true,
            'order_code'    => $order_code,
            'key_code'      => $key_code,
            'balance_after' => $debit['balance_after'],
            'package'       => ['name' => $package['name'], 'days' => $package['days'], 'price' => $price],
        ]);

    // ===== TẠO ĐƠN HÀNG =====
    case 'create_order':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $game_id = $_POST['game_id'] ?? 0;
        $package_id = $_POST['package_id'] ?? 0;
        $payment_method = $_POST['payment_method'] ?? 'mbbank';
        if (!in_array($payment_method, ['mbbank', 'binance'], true)) {
            jsonResponse(['error' => 'Phương thức thanh toán không hợp lệ'], 400);
        }
        if ($payment_method === 'binance') {
            if (!defined('CRYPTO_AUTO_APPROVE_ENABLED') || !CRYPTO_AUTO_APPROVE_ENABLED
                || !defined('USDT_TRC20_ADDRESS') || USDT_TRC20_ADDRESS === '') {
                jsonResponse(['error' => 'Thanh toán Binance USDT đang tạm khoá. Vui lòng chọn MBBank.'], 400);
            }
        }

        $pkg = $db->prepare("SELECT p.*, g.name as game_name FROM packages p JOIN games g ON p.game_id=g.id WHERE p.id=? AND p.game_id=? AND p.is_active=1 AND g.is_active=1 AND p.is_active=1 AND g.is_active=1");
        $pkg->execute([$package_id, $game_id]);
        $package = $pkg->fetch();
        if (!$package) jsonResponse(['error' => 'Gói không tồn tại'], 404);

        // Chống spam mua: không cho tạo nhiều đơn pending giống nhau hoặc bấm quá nhanh.
        $dup = $db->prepare("SELECT o.order_code FROM orders o WHERE o.user_id=? AND o.game_id=? AND o.package_id=? AND o.status='pending' ORDER BY o.created_at DESC LIMIT 1");
        $dup->execute([$user['id'], $game_id, $package_id]);
        $pending_same = $dup->fetch();
        if ($pending_same) {
            jsonResponse(['error' => 'Bạn đã có đơn gói này đang chờ thanh toán', 'order_code' => $pending_same['order_code']], 429);
        }

        $recent = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 SECOND)");
        $recent->execute([$user['id']]);
        if ((int)$recent->fetchColumn() >= 1) {
            jsonResponse(['error' => 'Bạn thao tác quá nhanh, vui lòng chờ 30 giây rồi thử lại'], 429);
        }

        $pending_count = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND status='pending'");
        $pending_count->execute([$user['id']]);
        if ((int)$pending_count->fetchColumn() >= 3) {
            jsonResponse(['error' => 'Bạn đang có quá nhiều đơn chờ thanh toán, vui lòng hoàn tất hoặc chờ đơn cũ hết hiệu lực'], 429);
        }

        $order_code = generateOrderCode();
        $db->beginTransaction();
        try {
            // Lấy key có sẵn từ pool theo game + package
            $keyStmt = $db->prepare("SELECT id, key_code FROM `keys` WHERE status='available' AND game_id=? AND package_id=? ORDER BY id ASC LIMIT 1 FOR UPDATE");
            $keyStmt->execute([$game_id, $package_id]);
            $poolKey = $keyStmt->fetch();

            if (!$poolKey) {
                $db->rollBack();
                jsonResponse(['error' => 'Hết key cho gói này. Vui lòng liên hệ admin để được hỗ trợ.'], 400);
            }

            // Tạo đơn hàng (payment_method ghi luôn để crypto_poll có thể match)
            $db->prepare("INSERT INTO orders (order_code, user_id, game_id, package_id, amount, payment_method, status) VALUES (?,?,?,?,?,?,'pending')")
               ->execute([$order_code, $user['id'], $game_id, $package_id, $package['price'], $payment_method]);
            $order_id = $db->lastInsertId();

            // Gán key từ pool vào đơn hàng
            $db->prepare("UPDATE `keys` SET status='pending', user_id=?, order_id=?, days=? WHERE id=?")
               ->execute([$user['id'], $order_id, $package['days'], $poolKey['id']]);
            $key_code = $poolKey['key_code'];

            // Nếu Binance: convert VND→USDT với unique decimal trick rồi cập nhật orders
            $cryptoData = null;
            if ($payment_method === 'binance') {
                try {
                    $cryptoData = cryptoConvertVndToUsdt((int)$package['price'], (int)$order_id);
                    $db->prepare("UPDATE orders SET crypto_amount=?, usdt_vnd_rate=? WHERE id=?")
                       ->execute([$cryptoData['usdt'], $cryptoData['rate'], $order_id]);
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log('[CREATE_ORDER_CRYPTO] ' . $e->getMessage());
                    jsonResponse(['error' => 'Không lấy được tỉ giá USDT, vui lòng thử lại sau ít phút.'], 500);
                }
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            jsonResponse(['error' => 'Lỗi tạo đơn hàng: ' . $e->getMessage()], 500);
        }

        // Thông báo admin qua Telegram
        $amt = number_format($package['price'], 0, ',', '.');
        $username = $user['telegram_username'] ?? $user['full_name'];
        $payLabel = $payment_method === 'binance' ? '🪙 Binance USDT TRC20' : '🏦 MBBank';
        $payDetail = '';
        if ($payment_method === 'binance' && $cryptoData) {
            $usdtStr = rtrim(rtrim(number_format($cryptoData['usdt'], 6, '.', ''), '0'), '.');
            $rateStr = number_format($cryptoData['rate'], 0, ',', '.');
            $payDetail = "\n💵 Chờ nhận: <b>{$usdtStr} USDT</b> (rate {$rateStr})";
        }
        $msg = "🔔 <b>ĐƠN HÀNG MỚI #{$order_code}</b>\n\n👤 User: @{$username} (ID: {$user['telegram_id']})\n🎮 Game: {$package['game_name']}\n📦 Gói: {$package['name']} ({$package['days']} ngày)\n🔑 Key: <code>{$key_code}</code>\n💰 Số tiền: {$amt}đ\n💳 Thanh toán: {$payLabel}{$payDetail}\n🕐 " . date('d/m/Y H:i:s');
        $markup = ['inline_keyboard' => [
            [['text' => '✅ Duyệt đơn', 'callback_data' => 'approve_' . $order_code], ['text' => '❌ Từ chối', 'callback_data' => 'reject_' . $order_code]]
        ]];
        sendTelegram(ADMIN_CHAT_ID, $msg, $markup);

        $response = [
            'success' => true,
            'order_code' => $order_code,
            'amount' => $package['price'],
            'payment_method' => $payment_method,
            'created_at' => date('Y-m-d H:i:s'),
            'pay_expires_at' => date('Y-m-d H:i:s', time()+900),
            'server_time' => date('Y-m-d H:i:s'),
        ];
        if ($payment_method === 'mbbank') {
            $response['bank_account']     = BANK_ACCOUNT;
            $response['bank_name']        = BANK_NAME;
            $response['bank_owner']       = BANK_OWNER;
            $response['transfer_content'] = $order_code;
            $response['vietqr_url']       = buildVietQrUrl($package['price'], $order_code);
        } else {
            // Binance
            $response['crypto_amount']   = (float)$cryptoData['usdt'];
            $response['crypto_address']  = USDT_TRC20_ADDRESS;
            $response['crypto_network']  = 'TRC20 (TRON)';
            $response['crypto_qr_url']   = cryptoBuildQrUrl(USDT_TRC20_ADDRESS, (float)$cryptoData['usdt']);
            $response['usdt_vnd_rate']   = (float)$cryptoData['rate'];
            $response['rate_source']     = $cryptoData['rate_source'] ?? 'cache';
        }
        jsonResponse($response);



    case 'claim_free_key':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        if (!$token) jsonResponse(['error' => 'Thiếu token claim'], 400);
        $stmt = $db->prepare("SELECT fk.*, g.name game_name, p.name pkg_name FROM free_keys fk JOIN games g ON fk.game_id=g.id JOIN packages p ON fk.package_id=p.id WHERE fk.claim_token=?");
        $stmt->execute([$token]);
        $fk = $stmt->fetch();
        if (!$fk) jsonResponse(['error' => 'Link claim không hợp lệ'], 404);
        if (!$fk['is_active'] || strtotime($fk['expire_at']) < time()) jsonResponse(['error' => 'Key free đã hết hạn'], 410);

        $db->beginTransaction();
        try {
            // INSERT IGNORE trước để claim atomic (uniq_free_user constraint)
            $claimIns = $db->prepare("INSERT IGNORE INTO free_key_claims (free_key_id, user_id) VALUES (?, ?)");
            $claimIns->execute([$fk['id'], $user['id']]);
            if ($claimIns->rowCount() === 0) {
                // User đã claim trước đó
                $db->rollBack();
                $oldRow = $db->prepare("SELECT k.key_code FROM free_key_claims fkc LEFT JOIN `keys` k ON fkc.key_id=k.id WHERE fkc.free_key_id=? AND fkc.user_id=?");
                $oldRow->execute([$fk['id'], $user['id']]);
                $row = $oldRow->fetch();
                jsonResponse(['success' => true, 'already' => true, 'message' => 'Bạn đã nhận key free này rồi', 'key_code' => $row['key_code'] ?? $fk['key_code']]);
            }
            $claimId = $db->lastInsertId();

            // Insert key
            $db->prepare("INSERT INTO `keys` (key_code,user_id,game_id,package_id,status,days,start_at,expire_at) VALUES (?,?,?,?, 'active', ?, ?, ?)")
               ->execute([$fk['key_code'], $user['id'], $fk['game_id'], $fk['package_id'], $fk['days'], $fk['start_at'], $fk['expire_at']]);
            $kid = (int)$db->lastInsertId();
            $db->prepare("UPDATE free_key_claims SET key_id=? WHERE id=?")->execute([$kid, $claimId]);
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Nhận key free thành công', 'key_code' => $fk['key_code']]);
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[CLAIM_FREE_KEY] ' . $e->getMessage());
            jsonResponse(['error' => 'Không nhận được key. Vui lòng thử lại.'], 500);
        }

    case 'get_free_link':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        if (!FREE_GETKEY_ENABLED) jsonResponse(['error' => 'GetKey Free đang tắt'], 403);
        $game_id = (int)($_POST['game_id'] ?? $_GET['game_id'] ?? 0);
        $free_key_id = (int)($_POST['package_id'] ?? $_GET['package_id'] ?? 0);
        $where = "fk.is_active=1 AND fk.expire_at > NOW() AND c.id IS NULL";
        $params = [];
        if ($game_id > 0) { $where .= " AND fk.game_id=?"; $params[] = $game_id; }
        if ($free_key_id > 0) { $where .= " AND fk.id=?"; $params[] = $free_key_id; }
        $stmt = $db->prepare("SELECT fk.*, g.name game_name, p.name pkg_name FROM free_keys fk JOIN games g ON fk.game_id=g.id JOIN packages p ON fk.package_id=p.id LEFT JOIN free_key_claims c ON c.free_key_id=fk.id WHERE {$where} ORDER BY fk.created_at DESC LIMIT 1");
        $stmt->execute($params);
        $fk = $stmt->fetch();
        if (!$fk) jsonResponse(['error' => 'Chưa có key free khả dụng'], 404);
        $chk = $db->prepare("SELECT id FROM free_key_claims WHERE free_key_id=? AND user_id=?");
        $chk->execute([$fk['id'], $user['id']]);
        if ($chk->fetch()) jsonResponse(['error' => 'Bạn đã nhận key free này rồi'], 429);
        $url = $fk['short_url'];
        if (!$url) {
            $claimUrl = SITE_URL . '/claim.php?t=' . urlencode($fk['claim_token']);
            $url = buildFreeShortlink($claimUrl);
            $db->prepare("UPDATE free_keys SET short_url=? WHERE id=?")->execute([$url, $fk['id']]);
        }
        jsonResponse(['success'=>true, 'url'=>$url, 'game_name'=>$fk['game_name'], 'pkg_name'=>$fk['pkg_name'], 'expire_at'=>$fk['expire_at']]);

    // ===== ĐƠN CHỜ THANH TOÁN CỦA USER =====
    case 'pending_orders':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $stmt = $db->prepare("SELECT o.order_code,o.amount,o.payment_method,o.crypto_amount,o.usdt_vnd_rate,o.created_at, DATE_ADD(o.created_at, INTERVAL 15 MINUTE) pay_expires_at, GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(o.created_at, INTERVAL 15 MINUTE))) pay_seconds_left, NOW() server_time, g.name game_name,p.name pkg_name,p.days,k.key_code
            FROM orders o
            JOIN games g ON o.game_id=g.id
            JOIN packages p ON o.package_id=p.id
            LEFT JOIN `keys` k ON k.order_id=o.id AND k.status='pending'
            WHERE o.user_id=? AND o.status='pending' AND o.created_at >= (NOW() - INTERVAL 15 MINUTE)
            ORDER BY o.created_at DESC LIMIT 5");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            if (($o['payment_method'] ?? 'mbbank') === 'binance' && defined('USDT_TRC20_ADDRESS') && USDT_TRC20_ADDRESS !== '') {
                $o['crypto_address'] = USDT_TRC20_ADDRESS;
                $o['crypto_network'] = 'TRC20 (TRON)';
                $o['crypto_qr_url']  = cryptoBuildQrUrl(USDT_TRC20_ADDRESS, (float)$o['crypto_amount']);
            } else {
                $o['bank_account']     = BANK_ACCOUNT;
                $o['bank_name']        = BANK_NAME;
                $o['bank_owner']       = BANK_OWNER;
                $o['transfer_content'] = $o['order_code'];
                $o['vietqr_url']       = buildVietQrUrl($o['amount'], $o['order_code']);
            }
        }
        unset($o);
        jsonResponse(['success'=>true,'orders'=>$orders]);

    // ===== KEY CỦA USER =====
    case 'my_keys':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $filter = $_GET['filter'] ?? 'all'; // all, active, expired, locked

        $sql = "SELECT k.*, g.name as game_name, g.package_name, p.name as pkg_name, p.key_type
                FROM `keys` k JOIN games g ON k.game_id=g.id JOIN packages p ON k.package_id=p.id
                WHERE k.user_id=? AND k.status != 'pending'";
        $params = [$user['id']];

        // Cập nhật trạng thái expired
        $db->prepare("UPDATE `keys` SET status='expired' WHERE user_id=? AND status='active' AND expire_at < NOW()")
           ->execute([$user['id']]);

        if ($filter === 'active') $sql .= " AND k.status='active'";
        elseif ($filter === 'expired') $sql .= " AND k.status='expired'";
        elseif ($filter === 'locked') $sql .= " AND k.status='locked'";
        $sql .= " ORDER BY k.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $keys = $stmt->fetchAll();
        foreach ($keys as &$k) {
            if ($k['status'] === 'expired' && !empty($k['expire_at'])) {
                $deleteAt = date('Y-m-d H:i:s', strtotime($k['expire_at'] . ' +3 days'));
                $k['delete_at'] = $deleteAt;
                $k['delete_note'] = 'Không gia hạn sau 3 ngày kể từ lúc hết hạn, key sẽ tự xoá.';
            }
        }
        unset($k);
        
        // Stats
        $stats = $db->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status='expired' THEN 1 ELSE 0 END) as expired
            FROM `keys` WHERE user_id=?");
        $stats->execute([$user['id']]);
        $stats_data = $stats->fetch();
        
        jsonResponse(['success' => true, 'keys' => $keys, 'stats' => $stats_data]);

    // ===== TÌM KIẾM KEY =====
    case 'search_key':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q === '') jsonResponse(['success' => true, 'keys' => []]);
        // Escape LIKE wildcards % và _ để user input không match nhiều hơn intent
        $qLike = '%' . likeEscape($q) . '%';
        $stmt = $db->prepare("SELECT k.*, g.name as game_name, g.package_name, p.name as pkg_name, p.key_type
            FROM `keys` k JOIN games g ON k.game_id=g.id JOIN packages p ON k.package_id=p.id
            WHERE k.user_id=? AND k.status != 'pending' AND k.key_code LIKE ? ESCAPE '\\\\'
            LIMIT 100");
        $stmt->execute([$user['id'], $qLike]);
        jsonResponse(['success' => true, 'keys' => $stmt->fetchAll()]);

    // ===== RESET KEY =====
    case 'reset_key':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $key_id = (int)($_POST['key_id'] ?? 0);
        if ($key_id <= 0) jsonResponse(['error' => 'key_id không hợp lệ'], 400);

        // ATOMIC: UPDATE có điều kiện reset_count < max_reset + status='active' + owner check.
        // Không đọc rồi update riêng -> tránh race khi user spam click.
        $upd = $db->prepare("UPDATE `keys`
            SET reset_count = reset_count + 1
            WHERE id = ? AND user_id = ? AND status = 'active' AND reset_count < max_reset");
        $upd->execute([$key_id, $user['id']]);
        if ($upd->rowCount() === 0) {
            // Tìm key để báo lỗi cụ thể (không tồn tại / hết lượt / không active).
            $chk = $db->prepare("SELECT status, reset_count, max_reset FROM `keys` WHERE id=? AND user_id=?");
            $chk->execute([$key_id, $user['id']]);
            $row = $chk->fetch();
            if (!$row) jsonResponse(['error' => 'Key không tồn tại'], 404);
            if ($row['status'] !== 'active') jsonResponse(['error' => 'Key không active!'], 400);
            jsonResponse(['error' => 'Đã hết lượt reset!'], 400);
        }
        // Lấy giá trị mới để báo về client.
        $sel = $db->prepare("SELECT reset_count, max_reset FROM `keys` WHERE id=?");
        $sel->execute([$key_id]);
        $now = $sel->fetch();
        jsonResponse(['success' => true, 'remaining_resets' => max(0, (int)$now['max_reset'] - (int)$now['reset_count'])]);

    // ===== XOÁ KEY =====
    case 'delete_key':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $key_id = $_POST['key_id'] ?? 0;
        $db->prepare("DELETE FROM `keys` WHERE id=? AND user_id=? AND status IN ('expired','locked')")->execute([$key_id, $user['id']]);
        jsonResponse(['success' => true]);

    // ===== TRẠNG THÁI ĐƠN HÀNG =====
    case 'order_status':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $order_code = $_GET['order_code'] ?? '';
        $stmt = $db->prepare("SELECT o.*, g.name as game_name, p.name as pkg_name FROM orders o JOIN games g ON o.game_id=g.id JOIN packages p ON o.package_id=p.id WHERE o.order_code=? AND o.user_id=?");
        $stmt->execute([$order_code, $user['id']]);
        jsonResponse(['success' => true, 'order' => $stmt->fetch()]);

    // ===== LỊCH SỬ ĐƠN HÀNG =====
    case 'my_orders':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $stmt = $db->prepare("SELECT o.*, g.name as game_name, p.name as pkg_name, p.days FROM orders o JOIN games g ON o.game_id=g.id JOIN packages p ON o.package_id=p.id WHERE o.user_id=? ORDER BY o.created_at DESC LIMIT 100");
        $stmt->execute([$user['id']]);
        jsonResponse(['success' => true, 'orders' => $stmt->fetchAll()]);

    // ===== THỐNG KÊ PROFILE =====
    case 'profile_stats':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $uid = $user['id'];
        // Gộp 4 query thành 1 với SUM(CASE WHEN ...)
        $stmt = $db->prepare("SELECT
            (SELECT COUNT(*) FROM orders WHERE user_id=?)                            AS total_orders,
            (SELECT COUNT(*) FROM orders WHERE user_id=? AND status='approved')      AS approved_orders,
            (SELECT COUNT(*) FROM orders WHERE user_id=? AND status='pending')       AS pending_orders,
            (SELECT COUNT(*) FROM `keys` WHERE user_id=? AND status='active')        AS active_keys");
        $stmt->execute([$uid, $uid, $uid, $uid]);
        $row = $stmt->fetch();
        jsonResponse([
            'success'         => true,
            'total_orders'    => (int)$row['total_orders'],
            'approved_orders' => (int)$row['approved_orders'],
            'pending_orders'  => (int)$row['pending_orders'],
            'active_keys'     => (int)$row['active_keys'],
        ]);

    // ===== KIỂM TRA TRẠNG THÁI KEY FREE HÔM NAY =====
    // ===== KIỂM TRA TRẠNG THÁI KEY FREE HÔM NAY =====
    case 'free_key_status':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        $today = date('Y-m-d');
        $uid = $user['id'];
        // Kiểm tra đã nhận free key hôm nay chưa
        $claimed = $db->prepare("SELECT COUNT(*) FROM free_key_claims WHERE user_id=? AND DATE(claimed_at)=?");
        $claimed->execute([$uid, $today]);
        $alreadyClaimed = (int)$claimed->fetchColumn() > 0;
        if ($alreadyClaimed) {
            $log = $db->prepare("SELECT k.key_code, fkc.claimed_at FROM free_key_claims fkc LEFT JOIN `keys` k ON fkc.key_id=k.id WHERE fkc.user_id=? AND DATE(fkc.claimed_at)=? ORDER BY fkc.claimed_at DESC LIMIT 1");
            $log->execute([$uid, $today]);
            $row = $log->fetch();
            jsonResponse(['success' => true, 'claimed' => true, 'key_code' => $row['key_code'] ?? '', 'claimed_at' => $row['claimed_at'] ?? '']);
        }
        // Kiểm tra có free_key available (admin đã thêm)
        $freeAvail = $db->prepare("SELECT COUNT(*) FROM free_keys WHERE is_active=1 AND expire_at > NOW()");
        $freeAvail->execute();
        $freeCount = (int)$freeAvail->fetchColumn();
        // Đếm số người đã nhận hôm nay
        $totalClaimed = $db->prepare("SELECT COUNT(*) FROM free_key_claims WHERE DATE(claimed_at)=?");
        $totalClaimed->execute([$today]);
        jsonResponse(['success' => true, 'claimed' => false, 'available' => $freeCount, 'total_claimed_today' => $totalClaimed->fetchColumn()]);

    // ===== NHẬN LINK CLAIM KEY FREE — đi qua 2 lớp rút gọn (Link4M → YeuMoney → claim) =====
    case 'daily_free_key':
        if (!$user) jsonResponse(['error' => 'Chưa đăng nhập'], 401);
        if (!FREE_GETKEY_ENABLED) jsonResponse(['error' => 'GetKey Free đang tắt'], 403);
        $uid = $user['id'];

        // Tìm free_key available
        $fk = $db->prepare("SELECT fk.*, g.name game_name, p.name pkg_name FROM free_keys fk JOIN games g ON fk.game_id=g.id JOIN packages p ON fk.package_id=p.id WHERE fk.is_active=1 AND fk.expire_at > NOW() ORDER BY fk.created_at DESC LIMIT 1");
        $fk->execute();
        $freeKey = $fk->fetch();
        if (!$freeKey) {
            jsonResponse(['error' => 'Chưa có key free hôm nay! Admin sẽ thêm vào buổi sáng.'], 400);
        }

        // Kiểm tra đã claim key này chưa
        $chk = $db->prepare("SELECT id FROM free_key_claims WHERE free_key_id=? AND user_id=?");
        $chk->execute([$freeKey['id'], $uid]);
        if ($chk->fetch()) {
            jsonResponse(['success' => true, 'already' => true, 'message' => 'Bạn đã nhận key free này rồi']);
        }

        // Lấy short_url có sẵn (đã tạo 2 lớp từ admin panel)
        $shortUrl = $freeKey['short_url'] ?? null;
        if (!$shortUrl) {
            $claimUrl = SITE_URL . '/claim.php?t=' . urlencode($freeKey['claim_token']);
            try {
                $shortUrl = buildFreeShortlink($claimUrl, $debug);
                $db->prepare("UPDATE free_keys SET short_url=? WHERE id=?")->execute([$shortUrl, $freeKey['id']]);
            } catch (Exception $e) {
                jsonResponse(['error' => 'Không tạo được link. Liên hệ admin: ' . $e->getMessage()], 500);
            }
        }

        // Gắn telegram_id cá nhân để claim.php nhận diện user sau khi vượt link.
        // FIX: dùng telegram_id thật (số Telegram), KHÔNG dùng $uid (= users.id nội bộ).
        $separator = strpos($shortUrl, '?') !== false ? '&' : '?';
        $personalUrl = $shortUrl . $separator . 'telegram_id=' . (int)$user['telegram_id'];

        jsonResponse([
            'success' => true,
            'claim_url' => $personalUrl,
            'key_code' => $freeKey['key_code'],
            'days' => $freeKey['days'],
            'game_name' => $freeKey['game_name'],
            'pkg_name' => $freeKey['pkg_name'],
            'message' => '🎉 Mở link và vượt 2 lớp để nhận key!'
        ]);

    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}
