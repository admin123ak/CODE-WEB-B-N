<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'config' => [
        'api_url' => MBBANK_HISTORY_API_URL,
        'api_key' => MBBANK_HISTORY_API_KEY,
        'auto_approve' => MBBANK_AUTO_APPROVE_ENABLED,
    ],
    'steps' => []
];

try {
    // Step 1: Test API connection
    $debug['steps'][] = ['step' => 1, 'action' => 'Calling MBBank API'];
    $res = httpJsonRequest(MBBANK_HISTORY_API_URL, 'GET', [
        'Accept: application/json',
        'User-Agent: HCLOU-AutoBank/2.0'
    ]);

    $debug['steps'][] = [
        'step' => 2,
        'action' => 'API Response',
        'http_code' => $res['code'],
        'ok' => $res['ok'],
        'error' => $res['error'],
        'raw_preview' => substr($res['raw'], 0, 500)
    ];

    if (!$res['ok']) {
        throw new Exception('HTTP request failed: ' . $res['error']);
    }

    $json = $res['json'];
    $debug['steps'][] = [
        'step' => 3,
        'action' => 'Parse JSON',
        'success_field' => $json['success'] ?? null,
        'has_data' => isset($json['data']),
        'json_keys' => array_keys($json)
    ];

    if (empty($json['success'])) {
        $debug['steps'][] = ['error' => 'API returned success=false', 'message' => $json['message'] ?? 'no message'];
        throw new Exception('API MBBANK trả về success=false: ' . ($json['message'] ?? ''));
    }

    $mbData = $json['data']['mb_data'] ?? null;
    $debug['steps'][] = [
        'step' => 4,
        'action' => 'Extract mb_data',
        'has_mb_data' => $mbData !== null,
        'mb_data_keys' => is_array($mbData) ? array_keys($mbData) : null
    ];

    if (!is_array($mbData) || empty($mbData['transactions'])) {
        throw new Exception('Không có transactions trong mb_data');
    }

    $txs = $mbData['transactions'];
    $debug['steps'][] = [
        'step' => 5,
        'action' => 'Found transactions',
        'count' => count($txs),
        'sample' => array_slice($txs, 0, 2)
    ];

    // Step 6: Check database
    $db = getDB();
    $debug['steps'][] = ['step' => 6, 'action' => 'Database connected'];

    // Step 7: Process transactions
    $processed = [];
    foreach (array_slice($txs, 0, 5) as $tx) {
        $date = (string)($tx['transaction_date'] ?? $tx['formatted_date'] ?? '');
        $desc = trim((string)($tx['description'] ?? ''));
        $credit = (float)($tx['credit_amount'] ?? 0);
        $amount = $credit > 0 ? $credit : 0;
        $hash = hash('sha256', $date.'|'.$amount.'|'.$desc);

        preg_match('/\b(ORD[0-9A-Z]+)\b/i', $desc, $m);
        $orderCode = strtoupper($m[1] ?? '');

        $processed[] = [
            'date' => $date,
            'amount' => $amount,
            'description' => $desc,
            'order_code' => $orderCode,
            'hash' => substr($hash, 0, 16) . '...',
            'will_process' => $amount > 0 && $desc !== ''
        ];
    }

    $debug['steps'][] = [
        'step' => 7,
        'action' => 'Processed sample transactions',
        'transactions' => $processed
    ];

    // Step 8: Check recent orders
    $stmt = $db->prepare("SELECT order_code, amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll();

    $debug['steps'][] = [
        'step' => 8,
        'action' => 'Recent orders in database',
        'orders' => $recentOrders
    ];

    // Step 9: Check bank_transactions table
    $stmt = $db->prepare("SELECT tx_hash, tx_date, amount, order_code, status, created_at FROM bank_transactions ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recentTxs = $stmt->fetchAll();

    $debug['steps'][] = [
        'step' => 9,
        'action' => 'Recent bank transactions',
        'transactions' => $recentTxs
    ];

    $debug['status'] = 'SUCCESS';
    $debug['message'] = 'All checks passed';

} catch (Exception $e) {
    $debug['status'] = 'ERROR';
    $debug['error'] = $e->getMessage();
    $debug['trace'] = $e->getTraceAsString();
}

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
