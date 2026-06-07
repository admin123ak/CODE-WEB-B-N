<?php
// =============================================
// LICENSE SERVER - API (verify / check_update / download)
// JSON ký HMAC. Client verify sig bằng LS_SIGNING_SECRET (= LICENSE_PUBLIC_SECRET).
// =============================================
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$db = lsDB();

// Helper: lấy bản latest
function lsLatestVersion(PDO $db) {
    $r = $db->query("SELECT version FROM ls_releases WHERE is_latest=1 ORDER BY id DESC LIMIT 1")->fetch();
    return $r ? $r['version'] : '';
}

// Helper: tìm license hợp lệ theo key, trả [row|null, reason]
function lsFindLicense(PDO $db, $key) {
    if ($key === '') return [null, 'empty_key'];
    $st = $db->prepare("SELECT * FROM ls_licenses WHERE license_key=? LIMIT 1");
    $st->execute([$key]);
    $lic = $st->fetch();
    if (!$lic) return [null, 'not_found'];
    if ($lic['status'] !== 'active') return [$lic, 'suspended'];
    if (!empty($lic['expires_at']) && strtotime($lic['expires_at']) < time()) {
        $db->prepare("UPDATE ls_licenses SET status='expired' WHERE id=?")->execute([$lic['id']]);
        return [$lic, 'expired'];
    }
    return [$lic, 'ok'];
}

switch ($action) {

    // ===== VERIFY =====
    case 'verify':
        $key    = trim($_POST['license_key'] ?? $_GET['license_key'] ?? '');
        $domain = strtolower(trim($_POST['domain'] ?? $_GET['domain'] ?? ''));
        $ip     = trim($_POST['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
        $ver    = trim($_POST['version'] ?? $_GET['version'] ?? '');
        $domain = preg_replace('/^www\./', '', $domain);

        [$lic, $reason] = lsFindLicense($db, $key);
        $ts = time();

        if (!$lic || $reason !== 'ok') {
            $payload = ['valid' => false, 'reason' => $reason, 'ts' => $ts];
            $payload['sig'] = lsSign($payload);
            lsJson($payload);
        }

        // Domain handling
        if ($domain !== '') {
            $st = $db->prepare("SELECT id FROM ls_activations WHERE license_id=? AND domain=? LIMIT 1");
            $st->execute([$lic['id'], $domain]);
            $act = $st->fetch();
            if ($act) {
                $db->prepare("UPDATE ls_activations SET ip=?, app_version=?, last_seen=NOW() WHERE id=?")
                   ->execute([$ip, $ver, $act['id']]);
            } else {
                $cnt = $db->prepare("SELECT COUNT(*) FROM ls_activations WHERE license_id=?");
                $cnt->execute([$lic['id']]);
                if ((int)$cnt->fetchColumn() >= (int)$lic['max_domains']) {
                    $payload = ['valid' => false, 'reason' => 'domain_limit', 'ts' => $ts];
                    $payload['sig'] = lsSign($payload);
                    lsJson($payload);
                }
                $db->prepare("INSERT INTO ls_activations (license_id, domain, ip, app_version) VALUES (?,?,?,?)")
                   ->execute([$lic['id'], $domain, $ip, $ver]);
            }
        }

        $payload = [
            'valid'          => true,
            'expires_at'     => $lic['expires_at'] ?: '',
            'latest_version' => lsLatestVersion($db),
            'ts'             => $ts,
        ];
        $payload['sig'] = lsSign($payload);
        lsJson($payload);

    // ===== CHECK UPDATE =====
    case 'check_update':
        $key = trim($_GET['license_key'] ?? '');
        $cur = trim($_GET['current_version'] ?? '');
        [$lic, $reason] = lsFindLicense($db, $key);
        $ts = time();
        if (!$lic || $reason !== 'ok') {
            $payload = ['update_available' => false, 'reason' => $reason, 'ts' => $ts];
            $payload['sig'] = lsSign($payload);
            lsJson($payload);
        }
        $latest = lsLatestVersion($db);
        $cl = '';
        if ($latest) {
            $r = $db->prepare("SELECT changelog FROM ls_releases WHERE version=? LIMIT 1");
            $r->execute([$latest]);
            $cl = (string)($r->fetchColumn() ?: '');
        }
        $payload = [
            'update_available' => ($latest !== '' && version_compare($latest, $cur, '>')),
            'latest_version'   => $latest,
            'changelog'        => $cl,
            'ts'               => $ts,
        ];
        $payload['sig'] = lsSign($payload);
        lsJson($payload);

    // ===== DOWNLOAD ZIP =====
    case 'download':
        $key    = trim($_GET['license_key'] ?? '');
        $domain = strtolower(preg_replace('/^www\./', '', trim($_GET['domain'] ?? '')));
        [$lic, $reason] = lsFindLicense($db, $key);
        if (!$lic || $reason !== 'ok') { http_response_code(403); exit('License invalid: ' . $reason); }

        $r = $db->query("SELECT zip_filename, version FROM ls_releases WHERE is_latest=1 ORDER BY id DESC LIMIT 1")->fetch();
        if (!$r) { http_response_code(404); exit('No release'); }
        $path = LS_ROOT . '/releases/' . basename($r['zip_filename']);
        if (!is_file($path)) { http_response_code(404); exit('File missing'); }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="update_' . $r['version'] . '.zip"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;

    default:
        lsJson(['error' => 'Action không hợp lệ'], 400);
}
