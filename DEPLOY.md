# HƯỚNG DẪN DEPLOY LÊN HOSTING MỚI

## 1. CẤU HÌNH FILE config.php

Sửa các thông tin sau trong `config.php`:

```php
// Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Telegram Bot
define('BOT_TOKEN', 'your_bot_token');
define('ADMIN_CHAT_ID', 'your_admin_chat_id');
define('BOT_USERNAME', 'your_bot_username');

// Website
define('SITE_URL', 'https://your-domain.com');
define('SITE_NAME', 'YOUR SITE NAME');

// Admin password
define('ADMIN_PASSWORD_HASH', '$2y$10$...');  // Tạo mới bằng: password_hash('your_password', PASSWORD_DEFAULT)

// Bank info
define('BANK_NAME', 'MBBANK');
define('BANK_ACCOUNT', 'your_account');
define('BANK_OWNER', 'YOUR NAME');

// MBBANK API
define('MBBANK_HISTORY_API_KEY', 'your_api_key');
```

## 2. IMPORT DATABASE

Upload và import file `database.sql` vào database của bạn qua phpMyAdmin hoặc cPanel.

## 3. WEBHOOK TELEGRAM (TỰ ĐỘNG)

✅ **KHÔNG CẦN LÀM GÌ** - Webhook tự động setup khi có request đầu tiên đến webhook.php

Nếu muốn kiểm tra thủ công:
1. Tính token: `echo -n "BOT_TOKEN|ADMIN_CHAT_ID" | sha256sum | cut -c1-16`
2. Truy cập: `https://your-domain.com/setup_webhook.php?token=TOKEN&action=info`

## 4. SETUP CRONJOBS (BẮT BUỘC)

Vào cPanel > Cron Jobs hoặc dùng cron-job.org, thêm các job sau:

### Job 1: MBBANK Auto-bank (QUAN TRỌNG NHẤT)
- **URL**: `https://your-domain.com/cron_run.php?token=YOUR_CRON_TOKEN&job=mbbank`
- **Schedule**: Mỗi 1 phút (`*/1 * * * *`)
- **Mục đích**: Tự động duyệt đơn thanh toán

### Job 2: Maintenance
- **URL**: `https://your-domain.com/cron_run.php?token=YOUR_CRON_TOKEN&job=maintenance`
- **Schedule**: Mỗi 5 phút (`*/5 * * * *`)
- **Mục đích**: Xóa key hết hạn, hủy đơn quá 15 phút

### Job 3: Monitor
- **URL**: `https://your-domain.com/cron_run.php?token=YOUR_CRON_TOKEN&job=monitor`
- **Schedule**: Mỗi 5 phút (`*/5 * * * *`)
- **Mục đích**: Cảnh báo lỗi hệ thống qua Telegram

### Job 4: Automation Daily
- **URL**: `https://your-domain.com/cron_run.php?token=YOUR_CRON_TOKEN&job=automation`
- **Schedule**: 8h sáng hàng ngày (`0 8 * * *`)
- **Mục đích**: Nhắc nhở thanh toán, báo cáo hàng ngày

### Job 5: Health Check
- **URL**: `https://your-domain.com/cron_run.php?token=YOUR_CRON_TOKEN&job=health`
- **Schedule**: 9h sáng hàng ngày (`0 9 * * *`)
- **Mục đích**: Kiểm tra toàn bộ hệ thống

**Lấy CRON_RUN_TOKEN**: Xem trong file `config.php` dòng `define('CRON_RUN_TOKEN', '...');`

**Hoặc xem danh sách URL đầy đủ tại**: `https://your-domain.com/setup_cron.php`

## 5. KIỂM TRA SAU KHI DEPLOY

1. ✅ Truy cập `https://your-domain.com` - Trang chủ hoạt động
2. ✅ Truy cập `https://your-domain.com/admin/` - Đăng nhập admin
3. ✅ Gửi `/start` cho bot Telegram - Bot phản hồi
4. ✅ Tạo đơn test - Kiểm tra auto-approve hoặc manual approve
5. ✅ Kiểm tra cronjobs đã chạy: xem logs trong admin panel

## 6. BẢO MẬT

- Đổi `ADMIN_PASSWORD_HASH` trong config.php
- Đổi `CRON_RUN_TOKEN` nếu cần
- Xóa hoặc đổi tên file `setup_webhook.php` sau khi setup xong (tùy chọn)
- Chmod 644 cho các file .php
- Chmod 755 cho thư mục

## TÓM TẮT CHECKLIST

- [ ] Sửa config.php (DB, Bot, Domain, Bank)
- [ ] Import database.sql
- [ ] Setup 5 cronjobs (quan trọng nhất: mbbank mỗi 1 phút)
- [ ] Test bot Telegram
- [ ] Test tạo đơn và duyệt đơn
- [ ] Kiểm tra admin panel

---

**Lưu ý**: Webhook tự động setup, không cần làm gì thêm!
