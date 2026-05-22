# HCLOU SERVER — Bán Key Tự Động (MBBANK + Telegram Bot)

Hệ thống bán key dạng đăng ký theo thời hạn (1/7/30 ngày…), nhận thanh toán qua MBBANK chuyển khoản và tự động giao key qua Telegram Bot/Mini App.

## ⚡ Quick Start

```bash
# 1. Upload code lên hosting (cPanel → public_html)
# 2. Tạo database MySQL trống trên cPanel
# 3. Mở: https://your-domain.com/install.php
# 4. Wizard 8 bước → xong
```

📖 **Hướng dẫn đầy đủ**: xem [INSTALL.md](./INSTALL.md)

## 🎯 Tính năng

- ✅ **Auto-bank** qua API MBBANK (mỗi 1 phút check giao dịch)
- ✅ **Telegram Bot** quản lý đơn + duyệt thủ công
- ✅ **Telegram Mini App** giao diện mua key
- ✅ **GetKey Free** qua Link4M + YeuMoney
- ✅ **Admin Panel** quản lý game, package, key, user
- ✅ **Cron Jobs** tự động maintenance, monitor, health check
- ✅ **Web Installer** setup nhanh, không cần sửa code thủ công

## 📂 Cấu trúc

```
install.php       Web installer (chạy 1 lần)
config.php        Core loader (không sửa)
config.local.php  Secret (installer tạo, gitignored)
database.sql      Schema cho install mới
migrations/       SQL migration cho hosting đã chạy cũ
index.php         Trang chủ + Mini App
admin/            Admin panel
api/              REST API cho Mini App
webhook.php       Telegram bot webhook
claim.php         Free key claim page
mbbank_poll.php   Auto polling MBBANK
cron_run.php      Dispatcher cho 5 cron jobs
update.php        Update code an toàn từ git (admin only)
```

## 🔄 Update

```bash
cd public_html && git pull origin main
# Hoặc vào /update.php (cần login admin)
```

## 🆘 Lỗi thường gặp

Xem [INSTALL.md § Troubleshooting](./INSTALL.md#-troubleshooting)

## 🔒 Bảo mật

- Toàn bộ secret nằm trong `config.local.php` (gitignored)
- Webhook Telegram verify bằng `secret_token`
- Cron jobs có lock chống chạy chồng
- Atomic operations cho thanh toán (chống race condition)

## 📄 License

Proprietary. Internal use only.
