<?php
// Upload file này vào thư mục gốc web, chạy domain.com/fix.php, xong xoá nó
require_once __DIR__ . '/../config.php';
$db = getDB();

echo "<pre>🔧 Fix orders table...\n\n";

// Tìm tên FK constraint
$stmt = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND CONSTRAINT_TYPE='FOREIGN KEY' AND CONSTRAINT_NAME LIKE '%package%'");
$fk = $stmt->fetchColumn();

if ($fk) {
    $db->exec("ALTER TABLE `orders` DROP FOREIGN KEY `$fk`");
    echo "✅ Dropped FK: $fk\n";
} else {
    echo "⏭️ FK không tồn tại, bỏ qua\n";
}

$db->exec("ALTER TABLE `orders` MODIFY `package_id` INT(11) DEFAULT NULL");
echo "✅ package_id -> NULL\n";

// Kiểm tra account_type_id có chưa
$stmt = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='account_type_id'");
if (!$stmt->fetchColumn()) {
    $db->exec("ALTER TABLE `orders` ADD `account_type_id` INT(11) DEFAULT NULL AFTER `package_id`");
    echo "✅ Thêm account_type_id\n";
} else {
    echo "⏭️ account_type_id đã có\n";
}

// Kiểm tra index
$stmt = $db->query("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND INDEX_NAME='idx_orders_acc_type'");
if (!$stmt->fetchColumn()) {
    $db->exec("ALTER TABLE `orders` ADD KEY `idx_orders_acc_type` (`account_type_id`)");
    echo "✅ Thêm index\n";
} else {
    echo "⏭️ Index đã có\n";
}

echo "\n✅ Fix xong! Giờ mua acc chạy ngon.\n";
echo "Xoá file fix.php sau khi dùng.\n";
