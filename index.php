
<?php require_once 'config.php'; header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); header('Pragma: no-cache'); ?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0a0e1a">
<title><?= SITE_NAME ?></title>
<!-- Font load trước CSS, non-blocking -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<link rel="stylesheet" href="./assets/app.css?v=<?= @filemtime(__DIR__ . '/assets/app.css') ?: time() ?>">
</head>
<body style="background:#0a0e1a">

<div id="loadingScreen">
  <div class="load-logo">&#x26A1;</div>
  <div class="load-title"><?= SITE_NAME ?></div>
  <div class="load-bar-wrap"><div class="load-bar"></div></div>
</div>

<div id="toast"></div>

<div id="webOnly" class="web-only">
  <div class="web-card">
    <div class="web-logo">⚡</div>
    <div class="web-title" data-i18n="webTitle">Mở HCLOU trong Telegram</div>
    <div class="web-sub" data-i18n="webSub">Web này chỉ sử dụng trong Telegram Mini App để xác thực tài khoản và bảo vệ key của bạn.</div>
    <a class="web-btn" id="openTelegramBtn" href="https://t.me/<?= BOT_USERNAME ?>?start=webapp" data-i18n="openTelegram">🚀 Mở trong Telegram</a>
    <div class="web-dots"><span></span><span></span><span></span></div>
    <div class="web-hint">Nếu không tự chuyển, hãy bấm nút bên trên rồi chọn <b>Open App</b> trong bot.</div>
  </div>
</div>


<div class="moverlay" id="gameModal">
  <div class="mbox">
    <div class="mhandle"></div>
    <div class="mtitle" data-i18n="chooseGameTitle">🎮 Chọn game</div>
    <div class="mscroll" id="gameList"><div class="loading"><div class="spin"></div>&#x110;ang t&#x1EA3;i...</div></div>
  </div>
</div>

<div class="moverlay" id="payModal">
  <div class="mbox">
    <div class="mhandle"></div>
    <div class="mtitle" data-i18n="paymentTitle">💳 Thanh toán</div>
    <div class="mscroll" id="payContent"></div>
  </div>
</div>

<div class="moverlay" id="topupModal">
  <div class="mbox">
    <div class="mhandle"></div>
    <div class="mtitle" id="topupTitle">💳 Nạp tiền vào ví</div>
    <div class="mscroll" id="topupContent"></div>
  </div>
</div>

<div class="moverlay" id="confirmModal">
  <div class="mbox confirm-box">
    <div class="mhandle"></div>
    <div class="mtitle" data-i18n="confirmTitle">Xác nhận</div>
    <div class="confirm-content" id="confirmContent"></div>
    <div class="confirm-actions">
      <button class="confirm-btn cancel" onclick="cancelOrderConfirm()" data-i18n="huy">Huỷ</button>
      <button class="confirm-btn ok" onclick="confirmCreateOrder()" data-i18n="dongY">Đồng Ý</button>
    </div>
  </div>
</div>

<div id="app" style="opacity:0;transition:opacity .4s ease">
  <div class="app-header">
    <div class="promo-badge" data-i18n="promo">🔥 KHUYẾN MÃI HOT!</div>
    <div class="lang-pills" id="langPills" role="group" aria-label="Language">
      <button type="button" class="lang-pill" data-lang="vi" onclick="setLang('vi')" title="Tiếng Việt"><span class="flag">&#127483;&#127475;</span></button>
      <button type="button" class="lang-pill" data-lang="en" onclick="setLang('en')" title="English"><span class="flag">&#127468;&#127463;</span></button>
      <button type="button" class="lang-pill" data-lang="es" onclick="setLang('es')" title="Español"><span class="flag">&#127466;&#127480;</span></button>
    </div>
    <div class="bank-chip" id="telegramIdChip" onclick="copyTelegramId()">
      <span id="telegramIdText">Đang tải...</span> &#x1F4CB;
    </div>
  </div>

  <div class="scroll-area">
    <!-- TAB: Mua Key (mặc định) -->
    <div id="tab-buykey" class="tab-content active">
    <div class="profile-section">
      <div class="avatar-ring">
        <div class="avatar-inner" id="avatarEl">
          <span id="avatarInit" style="background:linear-gradient(135deg,var(--purple),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent">?</span>
        </div>
      </div>
      <div class="profile-name" id="pName">&#x110;ang t&#x1EA3;i...</div>
      <div class="profile-handle">
        <span id="pHandle">@user</span>
        <div class="verified-icon">&#x2713;</div>
      </div>
      <div class="profile-role" id="pRole"></div>
    </div>

    <div class="stats-card">
      <div class="stat-item">
        <div class="stat-num blue" id="stTotal">0</div>
        <div class="stat-label" data-i18n="totalKey">Tổng key</div>
      </div>
      <div class="stat-item">
        <div class="stat-num green" id="stActive">0</div>
        <div class="stat-label" data-i18n="activeLabel">Hoạt động</div>
      </div>
      <div class="stat-item">
        <div class="stat-num orange" id="stExpired">0</div>
        <div class="stat-label" data-i18n="expiredLabel">Hết hạn</div>
      </div>
    </div>

    <div class="sec-head">
      <div class="sec-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 8.5H8L6 3H3"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg></div>
      <div>
        <div class="sec-title" data-i18n="buyNew">Mua Key mới</div>
        <div class="sec-sub" data-i18n="buySub">Chọn ứng dụng và gói ngày</div>
      </div>
    </div>

    <div class="card">
      <div class="card-inner-label"><span class="label-ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="3"/><path d="M9 7h6M9 17h6"/></svg></span><span data-i18n="chooseApp">Chọn ứng dụng</span></div>
      <div style="padding:0 12px 4px">
        <div class="game-btn" id="gameBtnEl" onclick="openGameModal()">
          <div class="game-emoji" id="gIcon">&#x1F3AE;</div>
          <div style="flex:1">
            <div class="game-title" id="gName" data-i18n="tapChooseGame">Nhấn chọn game</div>
            <div class="game-pkgname" id="gPkg" data-i18n="noGameSelected">Chưa chọn game</div>
          </div>
          <div class="chev">&#x203A;</div>
        </div>
      </div>
      <div class="pkg-label"><span class="label-ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l3.5 5 5.5 1.5-3.5 4.5.2 5.8L12 17.5 6.3 19.8l.2-5.8L3 9.5 8.5 8 12 3z"/></svg></span><span data-i18n="choosePackage">Chọn gói</span></div>
      <div id="pkgList" class="pkg-list">
        <div style="text-align:center;color:var(--text2);padding:16px 0;font-size:13px;font-weight:600">Danh s&#xE1;ch tr&#x1ED1;ng</div>
      </div>

      <!-- Quantity Selector - luôn hiển thị, layout ngang full width -->
      <div id="qtySelector" class="qty-wrap" style="margin:0 12px 8px">
        <div class="qty-left">
          <div class="qty-icon">📦</div>
          <div>
            <div class="qty-label" data-i18n="soLuongKey">Số lượng key</div>
            <div class="qty-sub" id="qtyTotal">Chọn gói để xem tổng</div>
          </div>
        </div>
        <div class="qty-row">
          <div class="qty-btn minus" onclick="changeQty(-1)">−</div>
          <input type="number" class="qty-input" id="qtyInput" value="1" min="1" max="10" readonly>
          <div class="qty-btn plus" onclick="changeQty(1)">+</div>
        </div>
      </div>

      <div class="action-bar">
        <a class="ic-btn tg" href="https://t.me/hclouserverfile" target="_blank" rel="noopener" title="Tải file" aria-label="Tải file">
          <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 19h14"/></svg>
        </a>
        <button type="button" class="ic-btn play disabled" id="playBtn" onclick="openPlayLink()" title="CH Play" aria-label="CH Play">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#fff" d="M4.4 2.78c-.25.22-.4.56-.4.98v16.48c0 .42.15.76.4.98l.06.05L13.7 12 4.46 2.73l-.06.05Z"/><path fill="#fff" opacity=".82" d="m16.79 8.91-3.09 3.1 3.1 3.1 3.77-2.15c1.24-.71 1.24-1.19 0-1.9l-3.78-2.15Z"/><path fill="#fff" opacity=".66" d="m16.79 8.91-3.09 3.1-9.24-9.28c.39-.2.9-.16 1.46.16l10.87 6.02Z"/><path fill="#fff" opacity=".9" d="m13.7 12.01-9.24 9.26c.39.19.9.15 1.46-.17l10.88-6-3.1-3.09Z"/></svg>
        </button>
        <button class="buy-btn" id="buyBtn" onclick="doOrder()">
          <span data-i18n="buyNow">Mua ngay</span>
          <span class="buy-sub" id="buySub" data-i18n="noPackageSelected">Chưa chọn gói</span>
        </button>
      </div>
      <div class="note-txt">&#x26A0;&#xFE0F; Kh&#xF4;ng nh&#x1EAD;n card: N&#x1EBF;u kh&#xF4;ng c&#xF3; t&#xE0;i kho&#x1EA3;n ng&#xE2;n h&#xE0;ng, t&#x1EA1;o m&#xE3; QR v&#xE0; nh&#x1EDD; ng&#x01B0;&#x1EDD;i kh&#xE1;c qu&#xE9;t h&#x1ED9; &#x111;&#x1EC3; nh&#x1EAD;n key.</div>
    </div>

    <div class="key-head">
      <div class="key-head-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-2 2-3 3"/><circle cx="8" cy="16" r="5"/><path d="M10.8 13.2L21 3"/></svg></div>
      <div>
        <div class="sec-title" data-i18n="yourKeys">Key của bạn</div>
        <div class="key-count-lbl" id="keyCntLbl">0 key</div>
      </div>
    </div>

    <div class="filter-wrap">
      <button class="ftab on" onclick="filterK('all',this)" data-i18n="all">Tất cả</button>
      <button class="ftab" onclick="filterK('active',this)" data-i18n="activeLabel">Hoạt động</button>
      <button class="ftab" onclick="filterK('expired',this)" data-i18n="expiredLabel">Hết hạn</button>
      <button class="ftab" onclick="filterK('locked',this)" data-i18n="lockedLabel">Bị khoá</button>
    </div>

    <div class="srch">
      <span style="color:var(--text2);font-size:15px">&#x1F50D;</span>
      <input type="text" placeholder="Tìm kiếm GKey..." data-i18n-placeholder="search" id="srchInput" oninput="srchKeys(this.value)">
    </div>

    <div id="keyWrap">
      <div class="loading"><div class="spin"></div><span data-i18n="loadingKeys">&#x110;ang t&#x1EA3;i keys...</span></div>
    </div>
    <footer class="hclou-footer" aria-label="HCLOU footer">
      <div class="hclou-footer-main">
        <section>
          <h3>HCLOU SERVER</h3>
          <div class="hf-list">
            <div class="hf-item"><span class="hf-icon">📍</span><span><span data-i18n="footerAddress">Địa chỉ</span>: Thành phố Quảng Ngãi</span></div>
            <div class="hf-item"><span class="hf-icon">🧾</span><span><span data-i18n="footerTaxId">Mã số thuế</span>: <span class="hf-muted" data-i18n="footerUpdating">Đang cập nhật</span></span></div>
            <div class="hf-item"><span class="hf-icon">📄</span><span><span data-i18n="footerBizLic">Số GPKD</span>: <span class="hf-muted" data-i18n="footerUpdating">Đang cập nhật</span></span></div>
            <div class="hf-item"><span class="hf-icon">☎️</span><span><span data-i18n="footerHotline">Hotline</span>: <a class="hf-hot" href="tel:0382176752">0382176752</a></span></div>
            <div class="hf-item"><span class="hf-icon">⚠️</span><span><span data-i18n="footerComplaint">Phản ánh chất lượng</span>: <a class="hf-hot" href="tel:0382176752">0382176752</a></span></div>
            <div class="hf-item"><span class="hf-icon">✉️</span><span><span data-i18n="footerEmail">Email liên hệ</span>: <a class="hf-hot" href="mailto:suphuhoangsp@gmail.com">suphuhoangsp@gmail.com</a></span></div>
            <div class="hf-item"><span class="hf-icon">👤</span><span><span data-i18n="footerRespContent">Chịu trách nhiệm nội dung</span>: <span class="hf-hot">HCLOU Server</span></span></div>
          </div>
          <a class="hf-btn" href="https://t.me/hcloucom" target="_blank" rel="noopener" data-i18n="footerFollowTg">Follow on Telegram</a>
        </section>

        <section>
          <h4 data-i18n="footerSocial">Kết nối mạng xã hội</h4>
          <div class="hf-social-grid">
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">📘 Facebook</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">💼 LinkedIn</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">💬 Zalo</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">𝕏 X</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">▶️ Youtube</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">📷 Instagram</a>
            <a class="hf-social" href="https://t.me/hcloucom" target="_blank" rel="noopener">💻 Github</a>
          </div>
        </section>

        <section>
          <h4 data-i18n="footerCertSite">Chứng chỉ trang web</h4>
          <div class="hf-badge-grid" aria-label="Chứng chỉ website">
            <span class="hf-logo gold cert-text" title="Đã thông báo Bộ Công Thương">Bộ CT</span>
            <span class="hf-logo red cert-text" title="DMCA Protected">DMCA</span>
            <span class="hf-logo green" title="Trustpilot 5 sao">★★★★★</span>
            <span class="hf-logo blue cert-text" title="HostAdvice">HostAdvice</span>
            <span class="hf-logo purple cert-iso" title="ISOCERT 9001:2015">ISO 9001</span>
            <span class="hf-logo blue cert-iso" title="ISO 27001:2022">ISO 27001</span>
          </div>
        </section>

        <section>
          <h4 data-i18n="footerPaymentSupport">Hỗ trợ thanh toán</h4>
          <div class="hf-pay-grid" aria-label="Phương thức thanh toán">
            <span class="hf-logo pay-master" title="MasterCard">●●</span>
            <span class="hf-logo blue" title="PayPal">PayPal</span>
            <span class="hf-logo green" title="Tiền mặt">VND</span>
            <span class="hf-logo pay-momo" title="MoMo">MoMo</span>
            <span class="hf-logo gold" title="ATM nội địa">ATM</span>
            <span class="hf-logo blue pay-bank" title="Internet Banking">🏦</span>
            <span class="hf-logo pay-visa" title="Visa">VISA</span>
          </div>
        </section>
      </div>
      <div class="hclou-footer-bottom">
        <div class="hf-brand">HCLOU SERVER</div>
        <div>Copyright © 2026 HCLOU Server. All Rights Reserved.</div>
      </div>
    </footer>
    </div> <!-- end tab-buykey -->

    <!-- TAB: Key Free mỗi ngày -->
    <div id="tab-freekey" class="tab-content">
      <h2 style="font-size:16px;padding:18px 16px 8px;font-weight:900" data-i18n="freeDayTitle">⭐ Key miễn phí mỗi ngày</h2>
      <div id="freeKeyWrap">
        <div class="loading"><div class="spin"></div><span data-i18n="freeChecking">Đang kiểm tra key...</span></div>
      </div>
    </div>

    <!-- TAB: Mua Acc -->
    <div id="tab-buyacc" class="tab-content">
      <div class="sec-head">
        <div class="sec-icon" style="background:linear-gradient(135deg,rgba(168,85,247,.16),rgba(139,92,246,.08));border-color:rgba(168,85,247,.35);box-shadow:0 0 24px rgba(168,85,247,.25)"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div>
          <div class="sec-title" style="background:linear-gradient(135deg,#fff 0%,#c4b5fd 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Mua Acc</div>
          <div class="sec-sub">Chọn loại acc (Google, Facebook...)</div>
        </div>
      </div>

      <div class="card">
        <div class="card-inner-label"><span class="label-ico" style="color:#a78bfa"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span><span>Chọn game & loại acc</span></div>
        <div style="padding:0 12px 4px">
          <div class="game-btn" id="accGameBtnEl" onclick="openAccGameModal()">
            <div class="game-emoji" id="accGIcon">&#x1F3AE;</div>
            <div style="flex:1">
              <div class="game-title" id="accGName">Nhấn chọn game</div>
              <div class="game-pkgname" id="accGPkg">Chưa chọn game</div>
            </div>
            <div class="chev">&#x203A;</div>
          </div>
        </div>
        <div id="accTypeList" class="pkg-list">
          <div style="text-align:center;color:var(--text2);padding:16px 0;font-size:13px;font-weight:600">Chọn game trước</div>
        </div>
        <!-- Acc qty: luôn 1, layout đồng bộ với tab Mua Key -->
        <div class="qty-wrap" style="margin:0 12px 8px">
          <div class="qty-left">
            <div class="qty-icon">🏪</div>
            <div>
              <div class="qty-label">Số lượng acc</div>
              <div class="qty-sub">Mỗi đơn 1 acc · đổi mật khẩu ngay sau nhận</div>
            </div>
          </div>
          <div class="qty-row">
            <div class="qty-btn minus" style="opacity:.3;cursor:not-allowed">−</div>
            <input type="number" class="qty-input" value="1" readonly>
            <div class="qty-btn plus" style="opacity:.3;cursor:not-allowed">+</div>
          </div>
        </div>
        <div class="action-bar">
          <div style="width:50px"></div>
          <div style="width:50px"></div>
          <button class="buy-btn" id="accBuyBtn" onclick="doAccOrder()">
            <span>Mua Acc</span>
            <span class="buy-sub" id="accBuySub">Chưa chọn loại acc</span>
          </button>
        </div>
        <div class="note-txt">&#x26A0;&#xFE0F; Mỗi acc chỉ bán 1 lần. Đổi mật khẩu ngay sau khi nhận acc.</div>
      </div>

      <div class="key-head">
        <div class="key-head-icon" style="color:var(--purple2);background:linear-gradient(135deg,rgba(168,85,247,.12),rgba(139,92,246,.06));border-color:rgba(168,85,247,.3)"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3H8l-1 4h10L16 3z"/></svg></div>
        <div>
          <div class="sec-title">Acc của tôi</div>
          <div class="key-count-lbl" id="accCntLbl">0 acc</div>
        </div>
      </div>
      <div id="accMyList" style="padding:0 16px 20px">
        <div style="text-align:center;color:var(--text2);padding:24px 0;font-size:13px;font-weight:600">Chưa có acc nào</div>
      </div>
    </div>

    <!-- TAB: Lịch sử đơn hàng -->
    <div id="tab-history" class="tab-content">
      <h2 style="font-size:16px;padding:18px 16px 8px;font-weight:900" data-i18n="historyTitle">📜 Lịch sử đơn hàng</h2>
      <div id="histWrap"></div>
    </div>

    <!-- TAB: Cá nhân -->
    <div id="tab-profile" class="tab-content">
      <div class="profile-section">
        <div class="avatar-ring">
          <div class="avatar-inner" id="avatarEl2">
            <span id="avatarInit2" style="background:linear-gradient(135deg,var(--purple),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent">?</span>
          </div>
        </div>
        <div class="profile-name" id="pName2">Đang tải...</div>
        <div class="profile-handle">
          <span id="pHandle2">@user</span>
          <div class="verified-icon">✓</div>
        </div>
        <div class="profile-role" id="pRole2"></div>
      </div>

      <div class="profile-card">
        <h3><span class="pc-ico"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg></span><span data-i18n="profileOverview">Tổng quan</span></h3>
        <div class="profile-stats-grid">
          <div class="profile-stat"><div class="num blue" id="pfTotalOrders">0</div><div class="lbl" data-i18n="profileTotalOrders">Tổng đơn</div></div>
          <div class="profile-stat"><div class="num green" id="pfApproved">0</div><div class="lbl" data-i18n="profileApproved">Đã duyệt</div></div>
          <div class="profile-stat"><div class="num orange" id="pfPending">0</div><div class="lbl" data-i18n="profilePending">Chờ xử lý</div></div>
          <div class="profile-stat"><div class="num blue" id="pfKeys">0</div><div class="lbl" data-i18n="profileKeysOwned">Key đang có</div></div>
        </div>
      </div>

      <div class="profile-card" id="walletCard" style="display:none">
        <h3><span class="pc-ico"><svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20M6 14h4"/></svg></span>Số dư ví</h3>
        <div style="padding:6px 0 10px">
          <div style="font-size:11px;color:var(--text2);font-weight:700;letter-spacing:.4px">SỐ DƯ HIỆN TẠI</div>
          <div id="pfBalance" style="font-size:26px;font-weight:900;color:var(--green2);margin-top:2px">0đ</div>
        </div>
        <div class="profile-btn" onclick="openBalanceHistory()">📜 Lịch sử ví</div>
      </div>

      <div class="profile-card">
        <h3><span class="pc-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg></span><span data-i18n="profileAccount">Thông tin tài khoản</span></h3>
        <div class="profile-row"><span class="lbl" data-i18n="profileTgId">Telegram ID</span><span class="val" id="pfTgId">--</span></div>
        <div class="profile-row"><span class="lbl" data-i18n="profileUsername">Username</span><span class="val" id="pfTgUser">--</span></div>
        <div class="profile-row"><span class="lbl" data-i18n="profileFullName">Họ tên</span><span class="val" id="pfFullName">--</span></div>
        <div class="profile-row"><span class="lbl" data-i18n="profileJoined">Ngày tham gia</span><span class="val" id="pfJoined">--</span></div>
      </div>

      <div class="profile-card">
        <h3><span class="pc-ico"><svg viewBox="0 0 24 24"><path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg></span><span data-i18n="profileLinks">Liên kết nhanh</span></h3>
        <div class="profile-btn" onclick="switchTab('buykey')"><span data-i18n="profileBtnBuy">🔑 Mua Key</span></div>
        <div class="profile-btn" onclick="switchTab('history')"><span data-i18n="profileBtnHistory">📜 Lịch sử đơn hàng</span></div>
        <div class="profile-btn red" onclick="location.reload()"><span data-i18n="profileBtnReload">🔄 Tải lại</span></div>
      </div>

      <footer class="hclou-footer" aria-label="HCLOU footer">
        <div class="hclou-footer-main">
          <h3 data-i18n="footerSupport">Liên hệ hỗ trợ</h3>
          <div class="hf-list"><div class="hf-item"><span class="hf-icon">📱</span><a class="hf-hot" href="tel:+84865770720">0865770720</a></div></div>
          <h4 data-i18n="footerPayment">Phương thức thanh toán</h4>
          <div class="hf-pay-grid">
            <span class="hf-logo blue pay-visa">VISA</span>
            <span class="hf-logo gold pay-master">MasterCard</span>
            <span class="hf-logo red pay-momo">MoMo</span>
            <span class="hf-logo green pay-bank">VietQR</span>
          </div>
          <h4 data-i18n="footerCert">Chứng nhận</h4>
          <div class="hf-badge-grid">
            <span class="hf-logo cert-text cert-iso">ISO 27001</span>
            <span class="hf-logo cert-text">🔒 SSL</span>
          </div>
        </div>
        <div class="hclou-footer-bottom">
          <div class="hf-brand">HCLOU SERVER</div>
          <div data-i18n="footerCopyright">Copyright © 2026 HCLOU Server. All Rights Reserved.</div>
        </div>
      </footer>
    </div>

  </div> <!-- scroll-area -->

  <!-- Bottom Tab Navigation -->
  <nav class="bottom-nav">
    <div class="nav-tab active" onclick="switchTab('buykey')" id="nav-buykey">
      <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      <span class="nav-lbl" data-i18n="navBuy">Mua Key</span>
    </div>
    <div class="nav-tab" onclick="switchTab('buyacc')" id="nav-buyacc">
      <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span class="nav-lbl">Mua Acc</span>
    </div>
    <div class="nav-tab" onclick="switchTab('freekey')" id="nav-freekey">
      <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      <span class="nav-lbl" data-i18n="navFree">Key Free</span>
    </div>
    <div class="nav-tab" onclick="switchTab('history')" id="nav-history">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span class="nav-lbl" data-i18n="navHistory">Lịch sử</span>
    </div>
    <div class="nav-tab" onclick="switchTab('profile')" id="nav-profile">
      <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span class="nav-lbl" data-i18n="navProfile">Cá nhân</span>
    </div>
  </nav>

</div> <!-- app -->
  <div class="help-fab" onclick="toggleHelpBot()">💬</div>
  <div class="help-panel" id="helpPanel">
    <div class="help-head"><div><div class="help-title">HCLOU Support Bot</div><small data-i18n="helpSub">Chọn câu hỏi để xem hướng dẫn nhanh</small></div><button class="help-close" onclick="toggleHelpBot(false)">✕</button></div>
    <div class="help-body" id="helpBody"></div>
  </div>
</div>

<script>window.HCLOU_BOT_USERNAME='<?= BOT_USERNAME ?>';</script>
<script src="./assets/app.js?v=<?= @filemtime(__DIR__ . '/assets/app.js') ?: time() ?>"></script>

</body>
</html>
