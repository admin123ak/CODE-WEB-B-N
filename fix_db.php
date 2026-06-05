<?php
// Fix DB orders table - chạy file này từ browser
// domain.com/fix_db.php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');
echo "🔧 Fix orders table...\n\n";

$db = getDB();

// Tìm FK
$q = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_TYPE='FOREIGN KEY' AND CONSTRAINT_NAME LIKE '%package%'");
$fk = $q->fetchColumn();
if ($fk) {
    $db->exec("ALTER TABLE `orders` DROP FOREIGN KEY `$fk`");
    echo "✅ DROP FK: $fk\n";
} else {
    echo "⏭️ FK ko tồn tại\n";
}

$db->exec("ALTER TABLE `orders` MODIFY `package_id` INT(11) DEFAULT NULL");
echo "✅ MODIFY package_id NULL\n";

$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='account_type_id'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `orders` ADD `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`");
    echo "✅ ADD account_type_id\n";
} else {
    echo "⏭️ account_type_id đã có\n";
}

$idx = $db->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND INDEX_NAME='idx_orders_acc_type'");
if (!$idx->fetchColumn()) {
    $db->exec("ALTER TABLE `orders` ADD KEY `idx_orders_acc_type` (`account_type_id`)");
    echo "✅ ADD index\n";
} else {
    echo "⏭️ Index đã có\n";
}

// users.role + users.discount
$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='role'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `users` ADD `role` ENUM('customer','reseller','admin') DEFAULT 'customer'");
    echo "✅ ADD users.role\n";
} else { echo "⏭️ users.role có\n"; }

$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='discount'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `users` ADD `discount` DECIMAL(5,2) DEFAULT 0.00");
    echo "✅ ADD users.discount\n";
} else { echo "⏭️ users.discount có\n"; }

// games.download_url
$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='games' AND COLUMN_NAME='download_url'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `games` ADD `download_url` VARCHAR(500) DEFAULT NULL AFTER `icon_url`");
    echo "✅ ADD games.download_url\n";
} else { echo "⏭️ games.download_url có\n"; }

// free_key_claims.claim_token, short_url, is_claimed
$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='claim_token'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `free_key_claims` ADD `claim_token` VARCHAR(80) DEFAULT NULL AFTER `key_id`");
    echo "✅ ADD free_key_claims.claim_token\n";
} else { echo "⏭️ free_key_claims.claim_token có\n"; }

$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='short_url'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `free_key_claims` ADD `short_url` TEXT DEFAULT NULL");
    echo "✅ ADD free_key_claims.short_url\n";
} else { echo "⏭️ free_key_claims.short_url có\n"; }

$col = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND COLUMN_NAME='is_claimed'");
if (!$col->fetchColumn()) {
    $db->exec("ALTER TABLE `free_key_claims` ADD `is_claimed` TINYINT(1) NOT NULL DEFAULT 0");
    echo "✅ ADD free_key_claims.is_claimed\n";
    // Đánh dấu các claim cũ đã có key_id là claimed
    $db->exec("UPDATE `free_key_claims` SET `is_claimed`=1 WHERE `key_id` IS NOT NULL");
    echo "✅ Migrate is_claimed cho claim cũ\n";
} else { echo "⏭️ free_key_claims.is_claimed có\n"; }

$idx = $db->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='free_key_claims' AND INDEX_NAME='uniq_claim_token'");
if (!$idx->fetchColumn()) {
    try {
        $db->exec("ALTER TABLE `free_key_claims` ADD UNIQUE KEY `uniq_claim_token` (`claim_token`)");
        echo "✅ ADD uniq_claim_token\n";
    } catch (Exception $e) { echo "⚠️ Index claim_token: " . $e->getMessage() . "\n"; }
} else { echo "⏭️ Index claim_token có\n"; }

echo "\n✅ Xong! Xoá file fix_db.php sau khi dùng.\n";
