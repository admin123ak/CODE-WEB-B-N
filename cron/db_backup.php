<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

// =============================================
// AUTH: chỉ chạy qua cron/run.php (CRON_RUN_TOKEN) hoặc CLI
// =============================================
if (PHP_SAPI !== 'cli') {
    $tok = $_GET['cron_token'] ?? '';
    if (!defined('CRON_RUN_TOKEN') || CRON_RUN_TOKEN === '' || !hash_equals(CRON_RUN_TOKEN, $tok)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        exit;
    }
}

$started = microtime(true);

try {
    $db = getDB();

    $dir = APP_ROOT . '/data/backups';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);

    // Defense: chặn truy cập web trực tiếp vào dump (cần dùng admin/download_backup.php).
    $ht = $dir . '/.htaccess';
    if (!is_file($ht)) @file_put_contents($ht, "Require all denied\n<IfVersion < 2.4>\nOrder Deny,Allow\nDeny from all\n</IfVersion>\n");
    $idx = $dir . '/index.html';
    if (!is_file($idx)) @file_put_contents($idx, '');

    $file = $dir . '/db_' . date('Ymd_His') . '.sql.gz';
    $gz   = gzopen($file, 'wb9');
    if (!$gz) throw new Exception('Không mở được file output: ' . $file);

    gzwrite($gz, "-- HCLOU DB backup\n");
    gzwrite($gz, "-- Created: " . date('c') . "\n");
    gzwrite($gz, "-- Server:  " . ($_SERVER['HTTP_HOST'] ?? 'cli') . "\n\n");
    gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $totalRows  = 0;
    $tableCount = 0;

    foreach ($tables as $tbl) {
        $tableCount++;
        gzwrite($gz, "-- ----------------------------\n");
        gzwrite($gz, "-- Table: `{$tbl}`\n");
        gzwrite($gz, "-- ----------------------------\n");
        gzwrite($gz, "DROP TABLE IF EXISTS `{$tbl}`;\n");

        $create = $db->query("SHOW CREATE TABLE `{$tbl}`")->fetch(PDO::FETCH_NUM);
        if ($create && !empty($create[1])) {
            gzwrite($gz, $create[1] . ";\n\n");
        }

        // Stream rows theo batch để giữ memory thấp ngay cả khi bảng to.
        $batch  = 500;
        $offset = 0;
        while (true) {
            $rows = $db->query("SELECT * FROM `{$tbl}` LIMIT {$batch} OFFSET {$offset}")->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) break;
            foreach ($rows as $row) {
                $cols = [];
                $vals = [];
                foreach ($row as $col => $val) {
                    $cols[] = '`' . $col . '`';
                    $vals[] = ($val === null) ? 'NULL' : $db->quote((string)$val);
                }
                gzwrite($gz, "INSERT INTO `{$tbl}` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n");
                $totalRows++;
            }
            if (count($rows) < $batch) break;
            $offset += $batch;
        }
        gzwrite($gz, "\n");
    }

    gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
    gzclose($gz);

    $size = (int)@filesize($file);

    // Retention: giữ 7 file gần nhất
    $all = glob($dir . '/db_*.sql.gz') ?: [];
    usort($all, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
    $keep    = 7;
    $deleted = 0;
    foreach (array_slice($all, $keep) as $old) {
        if (@unlink($old)) $deleted++;
    }

    $status = [
        'success'     => true,
        'last_run_at' => date('c'),
        'duration_ms' => (int)round((microtime(true) - $started) * 1000),
        'file'        => basename($file),
        'size_bytes'  => $size,
        'tables'      => $tableCount,
        'rows'        => $totalRows,
        'deleted_old' => $deleted,
        'kept'        => min(count($all), $keep),
    ];
    @file_put_contents($dir . '/_last_backup.json', json_encode($status, JSON_UNESCAPED_UNICODE), LOCK_EX);

    echo json_encode($status, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[DB_BACKUP] ' . $e->getMessage());
    $err = [
        'success'     => false,
        'last_run_at' => date('c'),
        'duration_ms' => (int)round((microtime(true) - $started) * 1000),
        'error'       => $e->getMessage(),
    ];
    @file_put_contents(APP_ROOT . '/data/backups/_last_backup.json', json_encode($err, JSON_UNESCAPED_UNICODE), LOCK_EX);
    http_response_code(500);
    echo json_encode($err, JSON_UNESCAPED_UNICODE);
}
