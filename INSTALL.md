# 🚀 HƯỚNG DẪN CÀI ĐẶT - HCLOU SERVER

Hệ thống bán key tự động qua **MBBANK + Telegram Bot**. Sau khi clone code chỉ cần chạy installer là dùng được.

---

## ⚙️ YÊU CẦU HOSTING

| Mục | Yêu cầu |
|-----|---------|
| PHP | >= 7.4 (khuyến nghị 8.0+) |
| MySQL/MariaDB | >= 5.7 / 10.3 |
| Extensions | `pdo_mysql`, `curl`, `mbstring`, `json`, `openssl` |
| Cron Jobs | cPanel/CronJobs hoặc cron-job.org |
| HTTPS | Bắt buộc (Telegram bot yêu cầu) |

---

## 📥 BƯỚC 1: UPLOAD CODE

### Cách A: Qua cPanel File Manager
1. Tải về `CODE-WEB-B-N.zip` từ git
2. Vào cPanel → **File Manager** → `public_html`
3. Upload zip → **Extract**

### Cách B: Qua Git (nếu cPanel có Git)
```bash
cd public_html
git clone https://github.com/admin123ak/CODE-WEB-B-N.git .
```

---

## 🗄️ BƯỚC 2: TẠO DATABASE

1. cPanel → **MySQL Databases**
2. Tạo database mới (vd: `yourcpanel_keysystem`)
3. Tạo user mới + assign vào database với **ALL PRIVILEGES**
4. **Ghi nhớ**: tên DB, username, password

> ⚠️ Installer sẽ tự import schema. Bạn KHÔNG cần dùng phpMyAdmin import.

---

## 🤖 BƯỚC 3: TẠO TELEGRAM BOT

1. Mở Telegram → tìm **@BotFather** → `/newbot`
2. Đặt tên + username (kết thúc bằng `bot`)
3. BotFather trả về **BOT_TOKEN** dạng `123456789:AAH...` → ghi lại
4. Lấy **ADMIN_CHAT_ID** của bạn:
   - Chat với **@userinfobot** → bot trả về số ID
   - Hoặc **@getidsbot** → gửi `/start`

---

## 💳 BƯỚC 4: LẤY API MBBANK

1. Vào **queenvps.com** → liên hệ Zalo/Messenger để mua API key
2. API key dạng: `MB_FREE_XXXXXXXXXXXX`

> Có thể bỏ qua bước này nếu chưa cần auto-bank. Đặt key giả → setup sau qua admin.

---

## 🔗 BƯỚC 5: LẤY TOKEN SHORTLINK (TÙY CHỌN)

Cho tính năng GetKey Free:
- **Link4M**: đăng ký link4m.co → API token
- **YeuMoney**: đăng ký yeumoney.com → API token

> Bỏ qua nếu không dùng GetKey Free. Có thể setup sau.

---

## 🪄 BƯỚC 6: CHẠY INSTALLER

1. Mở trình duyệt: `https://your-domain.com/install.php`
2. Wizard 8 bước:
   1. **System check** (auto)
   2. **Database** - nhập thông tin DB → installer auto import
   3. **Website** - URL site + tên (auto-detect)
   4. **Telegram** - BOT_TOKEN + ADMIN_CHAT_ID → test bot
   5. **Bank** - thông tin TK + API MBBANK
   6. **Admin password** - mật khẩu admin panel (>= 8 ký tự)
   7. **Tokens** - installer tự generate random
   8. **Hoàn tất** - tự set webhook + show URL cron

3. Sau khi xong → file `.install_lock` được tạo → installer tự khóa

> 🔒 **BẢO MẬT**: Sau cài đặt nên xóa hoặc rename file `install.php`.

---

## ⏰ BƯỚC 7: SETUP CRON JOBS

Wizard sẽ in ra **7 URL cron**. Vào **cPanel → Cron Jobs**:

| Tên | Schedule | job= | Mô tả |
|-----|----------|------|-------|
| MBBANK | `*/1 * * * *` | `mbbank` | Auto duyệt MBBANK |
| Crypto USDT | `*/1 * * * *` | `crypto` | Auto duyệt USDT TRC20 (Binance) |
| Card doithe | `*/2 * * * *` | `card` | Active check trạng thái nạp thẻ |
| Maintenance | `*/5 * * * *` | `maintenance` | Xóa key hết hạn, hủy đơn quá 15p |
| Monitor | `*/5 * * * *` | `monitor` | Cảnh báo lỗi qua Telegram |
| Automation | `0 8 * * *` | `automation` | Báo cáo hàng ngày 8h sáng |
| Health Check | `0 9 * * *` | `health` | Kiểm tra hệ thống 9h sáng |
| DB Backup | `0 3 * * *` | `backup` | Backup DB hàng ngày 3h sáng |

Command mẫu (paste vào cPanel):
```bash
wget -q -O - "https://your-domain.com/cron/run.php?token=TOKEN&job=mbbank" >/dev/null 2>&1
```

---

## ✅ BƯỚC 8: TEST HỆ THỐNG

1. **Trang chủ**: `https://your-domain.com` → hiển thị Mini App
2. **Admin panel**: `https://your-domain.com/admin/` → login với pass vừa tạo
3. **Bot Telegram**: gửi `/start` → bot phải phản hồi
4. **Test đơn**: tạo đơn test với số tiền nhỏ → chuyển khoản → cron tự duyệt

---

## 🆕 MIGRATION (khi cập nhật code cũ lên version mới)

Sau khi git pull code mới, nếu có file SQL mới trong `migrations/`, chạy thủ công:

```bash
# Cách 1: Import vào database qua phpMyAdmin
# Cách 2: Dùng command line
mysql -u USER -p DATABASE_NAME < migrations/001_add_accounts.sql
```

> ⚠️ File migration sẽ tự động bỏ qua cột đã tồn tại (IF NOT EXISTS / IF NOT COLUMN).

## 🔄 UPDATE CODE SAU NÀY

Khi có version mới:

### Cách A: Git pull (an toàn, giữ config)
```bash
cd public_html
git pull origin main
# config.local.php KHÔNG bị ghi đè vì đã trong .gitignore
```

### Cách B: Upload code mới
1. Backup `config.local.php` trước
2. Upload file mới (KHÔNG ghi đè `config.local.php`)
3. Chạy migration nếu có file mới trong `migrations/`

> ⚠️ **Quan trọng**: KHÔNG xóa `config.local.php`, `.install_lock`, `data/` khi update.

---

## 🆘 TROUBLESHOOTING

### Không vào được install.php
- Kiểm tra PHP version (cần >= 7.4)
- Kiểm tra extension PHP (pdo_mysql, curl, mbstring)

### Bot không phản hồi
- Vào `https://api.telegram.org/botBOT_TOKEN/getWebhookInfo` xem webhook đã set chưa
- Vào admin panel → setup lại webhook

### Cron không chạy
- Kiểm tra URL có đúng token không
- Test thủ công: paste URL vào trình duyệt → phải trả JSON `success: true`
- Xem log: `data/cron_run.log`

### Đơn không tự duyệt
- Vào `data/cron_run.log` xem job `mbbank` có chạy không
- Test API MBBANK: `https://queenvps.com/api/historymb/YOUR_KEY` → phải trả JSON

### Lỡ cài đặt sai
- Xóa file `.install_lock` qua FTP
- Truy cập lại `install.php?force=1`

---

## 📁 CẤU TRÚC FILE

```
.
├── install.php             ← Web installer (chạy 1 lần)
├── update.php              ← Update code an toàn từ git
├── config.php              ← Core loader (KHÔNG sửa)
├── config.local.php        ← Secret thật (installer tạo, gitignored)
├── config.local.sample.php ← Template
├── database.sql            ← Schema cho install mới
├── migrations/             ← SQL migration cho hosting đã chạy cũ
├── index.php               ← Trang chủ Mini App
├── admin/                  ← Admin panel
├── webhook.php             ← Telegram webhook
├── claim.php               ← Free key claim
├── assets/                 ← app.css + app.js (Mini App)
├── backend/
│   ├── api/index.php       ← REST API Mini App
│   └── lib/                ← Helpers (balance, crypto, order_approval, topup)
├── cron/                   ← 9 file cron + run.php dispatcher
├── card_callback.php       ← Callback doithe.vn
├── setup_webhook.php       ← Tool re-set Telegram webhook
├── data/                   ← Runtime: log, lock, cache (gitignored)
└── .htaccess               ← Apache security config
```

---

## 🔒 BẢO MẬT - KHUYẾN NGHỊ

1. **Sau cài đặt**: xóa hoặc rename `install.php`
2. **Chmod**: `config.local.php` → 600
3. **HTTPS bắt buộc**: bot Telegram chỉ chấp nhận HTTPS webhook
4. **Đổi token định kỳ**: vào admin panel → đổi `CRON_RUN_TOKEN`
5. **Backup DB hàng ngày**: cPanel → Backup
