<?php
// Chẩn đoán nhanh — mở https://app.hclou.com/diag.php  (XOÁ sau khi xong)
header('Content-Type: text/plain; charset=utf-8');
echo "=== HCLOU MINI APP DIAG ===\n\n";
echo "PHP: ".PHP_VERSION."\n";
echo "Time: ".date('Y-m-d H:i:s')."\n\n";

echo "[1] config.php... ";
try { require_once __DIR__.'/config.php'; echo "OK\n"; }
catch (Throwable $e){ echo "❌ ".$e->getMessage()."\n"; exit; }

echo "[2] Hằng số:\n";
foreach (['SITE_URL','SITE_NAME','BOT_TOKEN','BOT_USERNAME','ADMIN_CHAT_ID'] as $c){
  if (defined($c)){ $v=constant($c); echo "   $c = ".($c==='BOT_TOKEN'?substr($v,0,8).'...(len '.strlen($v).')':$v)."\n"; }
  else echo "   $c = ❌ CHƯA ĐỊNH NGHĨA\n";
}
echo "   -> Mini App phải mở đúng = ".(defined('SITE_URL')?SITE_URL:'?')."\n";

echo "\n[3] Database... ";
try {
  $db = function_exists('getDB') ? getDB() : null;
  if(!$db){ echo "⚠️ getDB() không có\n"; }
  else { $n=$db->query("SELECT COUNT(*) FROM users")->fetchColumn(); echo "OK (users=$n)\n"; }
} catch (Throwable $e){ echo "❌ ".$e->getMessage()."\n"; }

echo "\n[4] File quan trọng:\n";
foreach (['assets/app.js','backend/api/index.php','index.php'] as $f)
  echo "   $f : ".(is_file(__DIR__.'/'.$f)?"OK (".filesize(__DIR__.'/'.$f)." bytes)":"❌ THIẾU")."\n";

echo "\n[5] Host hiện tại: ".($_SERVER['HTTP_HOST']??'?')."\n";
echo "    (phải khớp host trong SITE_URL ở trên)\n";
echo "\n✅ Xong. XOÁ file diag.php sau khi đọc.\n";
