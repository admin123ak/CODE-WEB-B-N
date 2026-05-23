# HCLOU SERVER — Bán Key Tự Động (MBBANK + USDT TRC20 + Card + Telegram)

Hệ thống bán key dạng đăng ký theo thời hạn (1/7/30 ngày…), nhận thanh toán qua MBBANK / USDT TRC20 (Binance) / Card doithe.vn và tự động giao key qua Telegram Bot/Mini App.

## ⚡ Quick Start

```bash
# 1. Upload code lên hosting (cPanel → public_html)
# 2. Tạo database MySQL trống trên cPanel
# 3. Mở: https://your-domain.com/install.php
# 4. Wizard 8 bước → xong
```

📖 **Hướng dẫn đầy đủ**: xem [INSTALL.md](./INSTALL.md)

## 🎯 Tính năng

- ✅ **Auto-bank MBBANK** qua API Queenvps (mỗi 1 phút)
- ✅ **Auto-USDT TRC20** (Binance) qua TronGrid (mỗi 1 phút)
- ✅ **Auto-card** qua doithe.vn (Viettel/Mobifone/Vinaphone) — nạp ví → mua key
- ✅ **Telegram Bot** quản lý đơn + duyệt thủ công
- ✅ **Telegram Mini App** giao diện mua key + ví user
- ✅ **GetKey Free** qua Link4M + YeuMoney
- ✅ **Admin Panel** quản lý game, package, key, user, ví
- ✅ **Cron Jobs** tự động maintenance, monitor, health check, DB backup
- ✅ **Web Installer** setup nhanh, không cần sửa code thủ công

## 📂 Cấu trúc

```
install.php          Web installer (chạy 1 lần)
config.php           Core loader (không sửa)
config.local.php     Secret (installer tạo, gitignored)
database.sql         Schema cho install mới
migrations/          SQL migration cho hosting đã chạy cũ
index.php            Trang chủ + Mini App PHP shell (~360 dòng)
assets/
  ├── app.css        Mini App stylesheet
  └── app.js         Mini App SPA (~1260 dòng JS)
admin/               Admin panel
backend/
  ├── api/index.php  REST API cho Mini App (frontend fetch ./backend/api/)
  └── lib/           Helpers (balance, crypto, order_approval, topup)
cron/
  ├── run.php        Dispatcher 8 cron job (cron HTTP gọi /cron/run.php?job=X)
  ├── mbbank_poll.php
  ├── crypto_poll.php
  ├── card_poll.php
  ├── maintenance.php
  ├── db_backup.php
  ├── automation_daily.php
  ├── cron_monitor.php
  └── health_check_daily.php
webhook.php          Telegram bot webhook
card_callback.php    Callback từ doithe.vn (nạp thẻ → ví)
claim.php            Free key claim page
setup_webhook.php    Standalone tool re-set Telegram webhook
update.php           Update code an toàn từ git (admin only)
data/                Runtime: log, lock, status JSON (gitignored)
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
