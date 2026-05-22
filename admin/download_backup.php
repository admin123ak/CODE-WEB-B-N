<?php
require_once __DIR__ . '/../config.php';
session_start();

// =============================================
// AUTH: chỉ admin đang login mới được tải backup
// =============================================
$loggedIn = !empty($_SESSION['admin_auth']) && !empty($_SESSION['admin_last_seen'])
    && (time() - $_SESSION['admin_last_seen'] <= ADMIN_SESSION_TTL);
if (!$loggedIn) {
    http_response_code(403);
    exit('Forbidden');
}
$_SESSION['admin_last_seen'] = time();

$f = $_GET['f'] ?? '';

// Whitelist filename pattern - chặn path traversal triệt để.
if (!preg_match('/^db_\d{8}_\d{6}\.sql\.gz$/', $f)) {
    http_response_code(400);
    exit('Bad filename');
}

$path = APP_ROOT . '/data/backups/' . $f;
// Defense in depth: realpath phải nằm trong data/backups
$real    = realpath($path);
$dirReal = realpath(APP_ROOT . '/data/backups');
if (!$real || !$dirReal || strpos($real, $dirReal . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    exit('Not found');
}
if (!is_file($real)) {
    http_response_code(404);
    exit('Not found');
}

logInfo('Admin downloaded DB backup', ['file' => $f, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

header('Content-Type: application/gzip');
header('Content-Disposition: attachment; filename="' . $f . '"');
header('Content-Length: ' . filesize($real));
header('X-Content-Type-Options: nosniff');
readfile($real);
