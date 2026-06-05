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

echo "\n✅ Xong! Xoá file fix_db.php sau khi dùng.\n";
