/**
 * ============================================
 *  HCLOU SERVER
 *  Developer: TRAN VAN HOANG
 *  Zalo: 0868641019
 *  Copyright © 2026 - All rights reserved
 * ============================================
 */
var API='./backend/api/index.php',currentUser=null,selGame=null,selPkg=null,selQty=1,tgInitData='',appToken='';
var PLAY_BASE='https://play.google.com/store/apps/details?id=';
var allKeys=[],curFilter='all',cdTimers={},gCache=[],pCache=[],pendingPayOrders=[];

// =============================================
// XSS-SAFE HELPERS
// =============================================
function escapeHtml(s){
  if(s==null) return '';
  return String(s).replace(/[&<>"']/g, function(c){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
  });
}
// Sanitize URL: chỉ accept http/https/data:image, reject javascript: vbscript:
function safeUrl(u){
  if(!u) return '';
  var s = String(u).trim();
  if(/^javascript:|^vbscript:|^data:(?!image\/)/i.test(s)) return '';
  return s;
}
// Validate Android package name (vd: com.example.app)
function safePackageName(p){
  if(!p) return '';
  return /^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/.test(p) ? p : '';
}
// Encode value to safe JS string literal embedded inside an HTML attribute (vd: onclick="fn("+jsAttr(x)+")").
function jsAttr(v){ return escapeHtml(JSON.stringify(v==null?'':String(v))); }
var ICONS={
  'com.garena.game.kgvn':'\u2694\uFE0F',
  'com.garena.game.kgth':'\uD83D\uDDE1\uFE0F',
  'com.dts.freefireth':'\uD83D\uDD25',
  'com.dts.freefiremax':'\uD83D\uDD25',
  'com.riotgames.league.wildrift':'\uD83C\uDFAF',
  'com.riotgames.league.wildriftvn':'\uD83C\uDFAF',
  'vng.game.gunny.mobi.classic.original':'\uD83D\uDC30',
  'com.fungames.sniper3d':'\uD83C\uDFAF'
};

/* TEXT dung HTML entities - khong bi loi encoding */
var LANG=localStorage.getItem('hclou_lang')||'vi';
if(['vi','en','es'].indexOf(LANG)<0) LANG='vi';
var I18N={
  vi:{webTitle:'Mở HCLOU trong Telegram',webSub:'Web này chỉ sử dụng trong Telegram Mini App để xác thực tài khoản và bảo vệ key của bạn.',openTelegram:'🚀 Mở trong Telegram',tapChooseGame:'Nhấn chọn game',noGameSelected:'Chưa chọn game',noPackageSelected:'Chưa chọn gói',promo:'🔥 KHUYẾN MÃI HOT!',totalKey:'Tổng key',activeLabel:'Hoạt động',expiredLabel:'Hết hạn',buyNew:'Mua Key mới',buySub:'Chọn ứng dụng và gói ngày',chooseApp:'Chọn ứng dụng',choosePackage:'Chọn gói',buyNow:'Mua ngay',yourKeys:'Key của bạn',all:'Tất cả',lockedLabel:'Bị khoá',chooseGameTitle:'🎮 Chọn game',paymentTitle:'💳 Thanh toán',search:'Tìm kiếm GKey...',
    tongKey:'Tổng key',hoatDong:'Hoạt động',hetHan:'Hết hạn',biKhoa:'Bị khoá',tatCa:'Tất cả',soNgay:'Số ngày',conLai:'Còn lại',batDau:'Bắt đầu',ketThuc:'Kết thúc',dangTinh:'⏱ Đang tính...',conLaiLbl:'⏱ Còn lại: ',reset:'Reset',copy:'📋 Copy',giaHan:'Gia hạn',xoa:'Xóa',active:'✅ Hoạt động',expired:'⏰ Hết hạn',locked:'🔒 Bị khoá',pending:'⏳ Chờ thanh toán',hetHanLbl:'Hết hạn',chuaCoKey:'Chưa có key nào',goiNgay:'Gói ',ngay:' ngày',cheDoKey:'Chế độ ',keyMode:' key',khongCoGoi:'Không có gói nào',muaNgay:'Mua ngay',dangTaiGame:'Đang tải game...',chonGame:'Chọn game',nganHang:'Ngân hàng',soTK:'Số tài khoản',noiDungCK:'Nội dung CK',copyTK:'Đã copy số TK!',copyDon:'Đã copy mã đơn!',copyKey:'Đã copy key!',daCopy:'Đã copy!',luuY:'⚠️ Quét VietQR để tự điền đúng số tiền + nội dung. Nếu chuyển tay, bắt buộc ghi đúng nội dung bên trên.',daCK:'🔄 Kiểm tra thanh toán',choAdmin:'Đang kiểm tra thanh toán tự động...',giuManHinh:'Không thoát Mini App trong lúc thanh toán. Sau khi chuyển xong, key sẽ tự hiện trong mục Key của bạn.',hetGioTT:'Hết 15 phút chờ thanh toán. Nếu đã chuyển tiền, mở lại mục Key của bạn hoặc liên hệ admin kèm mã đơn.',daDuyetAuto:'Thanh toán đã xác nhận, key đã hoạt động!',resetOk:'Reset thành công!',xoaOk:'Đã xóa key!',confirmReset:'Reset thiết bị cho key này?',confirmXoa:'Xóa key này?',loiKetNoi:'Lỗi kết nối',moQuaBot:'Vui lòng mở qua bot Telegram!',giaHanMsg:'Chọn gói mới ở phần Mua Key để gia hạn!',chonGameTruoc:'Vui lòng chọn game trước',chuaChonGoi:'Chưa chọn gói',dangTai:'Đang tải...',loiTaoDon:'Lỗi tạo đơn!',copyFail:'Copy thất bại',taiKeyLoi:'Không tải được key. Hãy đóng Mini App và mở lại từ bot Telegram.',getFree:'Get Key Free',dangLayLink:'Đang lấy link...',freeHet:'Chưa có key free khả dụng',mienPhi:'Miễn phí',vuotLinkNhan:'Vượt link để nhận key',xacNhan:'Xác nhận',xacNhanMua:'Bạn đã chọn',capDo:'cấp độ',keyMotGame:'key chỉ được sử dụng cho một game. Bạn có muốn tiếp tục tạo đơn không?',huy:'Huỷ',dongY:'Đồng Ý',expiredDeleteNote:'Không gia hạn sau 3 ngày sẽ tự xoá',tuXoaLuc:'Tự xoá lúc',helpSub:'Chọn câu hỏi để xem hướng dẫn nhanh',pendingPayTitle:'Bạn còn đơn chờ thanh toán',pendingPaySub:'Nếu lỡ thoát trước khi chụp QR, bấm để mở lại thông tin thanh toán.',resumePay:'Mở lại QR thanh toán',pendingPayExpired:'Đơn đã quá 15 phút. Nếu đã chuyển tiền, liên hệ admin kèm mã đơn.',copyTelegramId:'Đã copy Telegram ID!',freeClaimOk:'Nhận key free thành công',freeClaimFail:'Không nhận được key free',pickerTitle:'💳 Chọn phương thức thanh toán',pickerMbbankT:'MBBank (VND)',pickerMbbankSub:'Chuyển khoản VietQR · Tự duyệt 1 phút',pickerBnbT:'Binance USDT (TRC20)',pickerBnbSub:'Crypto · Auto detect on-chain',payViaBnb:'💳 Thanh toán bằng <b>Binance USDT TRC20</b>',payViaMbb:'💳 Thanh toán bằng <b>MBBank VietQR</b>',cryptoNet:'Mạng',cryptoAddr:'Địa chỉ ví',cryptoAmt:'Số USDT',cryptoWarn:'⚠️ CHỈ gửi USDT mạng TRC20 (TRON). Gửi sai mạng (BEP20/ERC20) sẽ MẤT TIỀN không hoàn lại!',cryptoExact1:'Gửi đúng số <b>',cryptoExact2:'</b> để hệ thống tự nhận. Sai số sẽ không tự duyệt.',bankOwnerL:'Chủ tài khoản',
    confirmTitle:'Xác nhận',freeDayTitle:'⭐ Key miễn phí mỗi ngày',freeChecking:'Đang kiểm tra key...',historyTitle:'📜 Lịch sử đơn hàng',loadingKeys:'Đang tải keys...',histEmpty:'Chưa có đơn hàng nào',histLoading:'Đang tải lịch sử...',histOrderCode:'Mã đơn',histAmount:'Số tiền',histStatus:'Trạng thái',histDate:'Ngày tạo',histPending:'Chờ duyệt',histApproved:'Đã duyệt',histRejected:'Đã huỷ',
    profileOverview:'Tổng quan',profileAccount:'Thông tin tài khoản',profileLinks:'Liên kết nhanh',profileTotalOrders:'Tổng đơn',profileApproved:'Đã duyệt',profilePending:'Chờ xử lý',profileKeysOwned:'Key đang có',profileTgId:'Telegram ID',profileUsername:'Username',profileFullName:'Họ tên',profileJoined:'Ngày tham gia',profileBtnBuy:'🔑 Mua Key',profileBtnHistory:'📜 Lịch sử đơn hàng',profileBtnReload:'🔄 Tải lại',
    navBuy:'Mua Key',navFree:'Key Free',navHistory:'Lịch sử',navProfile:'Cá nhân',
    footerSupport:'Liên hệ hỗ trợ',footerPayment:'Phương thức thanh toán',footerCert:'Chứng nhận',footerCopyright:'Copyright © 2026 HCLOU Server. All Rights Reserved.',footerAddress:'Địa chỉ',footerTaxId:'Mã số thuế',footerBizLic:'Số GPKD',footerUpdating:'Đang cập nhật',footerHotline:'Hotline',footerComplaint:'Phản ánh chất lượng',footerEmail:'Email liên hệ',footerRespContent:'Chịu trách nhiệm nội dung',footerFollowTg:'Theo dõi trên Telegram',footerSocial:'Kết nối mạng xã hội',footerCertSite:'Chứng chỉ trang web',footerPaymentSupport:'Hỗ trợ thanh toán',
    freeChecking2:'Đang kiểm tra...',freeClaimedToday:'Bạn đã nhận key free hôm nay!',freeReceivedAt:'Nhận lúc',freeBackTomorrow:'🔄 Quay lại vào 0h00 ngày mai để nhận key tiếp',freeKeyToday:'Key miễn phí hôm nay',freeSub:'Nhận ngay 1 key free mỗi ngày!<br>Admin đã thêm key sẵn, mỗi người nhận 1 link riêng',freeBtnGetLink:'🔗 Lấy Link Claim Key',freeResetDaily:'⏰ Reset lúc 0h00 hàng ngày',freePeopleSuffix:'người đã nhận hôm nay',freeNoneToday:'Chưa có key free hôm nay',freeAdminNotYet:'Admin chưa thêm key. Vui lòng quay lại sau!',freeNewMorning:'🔄 Key mới sẽ có vào buổi sáng hàng ngày',freeCannotLoad:'Không thể tải thông tin key free',freeCreatingLink:'⏳ Đang tạo link...',freeAlready:'Bạn đã nhận key free hôm nay rồi!',freeYourLink:'Link claim riêng của bạn:',freeOpenLink:'🔓 Mở Link Claim Key',freeCopyLink:'📋 Copy Link',freeCopiedLink:'Đã copy link!',freeLinkCreated:'✅ Đã tạo link — Mở link để nhận key',freeErrorGeneric:'Có lỗi xảy ra!',toastLoadErr:'Lỗi kết nối, thử lại sau!',loadingHistory:'Đang tải lịch sử...',histLoadFail:'⚠️ Không thể tải lịch sử đơn hàng',histGame:'Game',histPkg:'Gói',histCreated:'Ngày tạo',histPkgDays:'ngày',statusApproved:'Đã duyệt',statusRejected:'Đã từ chối',statusCancelled:'Đã huỷ',statusPending:'Chờ xử lý',accTitle:'Mua Acc',accSubtitle:'Chọn loại acc (Google, Facebook...)',accChooseLabel:'Chọn game & loại acc',accPickGameFirst:'Chọn game trước',accQtyLabel:'Số lượng acc',accQtyHint:'Mỗi đơn 1 acc · đổi mật khẩu ngay sau nhận',accBuyBtn:'Mua Acc',accNoTypeSelected:'Chưa chọn loại acc',accNote:'⚠️ Mỗi acc chỉ bán 1 lần. Đổi mật khẩu ngay sau khi nhận acc.',accMyTitle:'Acc của tôi',accNoneYet:'Chưa có acc nào',navAcc:'Mua Acc',soLuongKey:'Số lượng key',gio:' giờ',totalLbl:'Tổng',pickerBalT:'Số dư ví',pickerBalSubOk:'Trừ thẳng từ ví · Nhận key ngay',pickerBalSubNo:'Số dư không đủ, nạp thêm',pickerCardT:'Thẻ cào',pickerCardSub:'Viettel/Mobifone/Vinaphone (qua ví)',errNoAccGame:'Chưa có game bán acc. Liên hệ admin.',errNoDownload:'Game này chưa có link tải',errSendCard:'Lỗi gửi thẻ',errWallet:'Lỗi trừ ví',buyKeyWalletOk:'🎉 Đã mua key thành công bằng ví!',errMaxTopup:'Tối đa 50 triệu/lần',errCreateTopup:'Lỗi tạo yêu cầu nạp',errGeneric:'Có lỗi xảy ra!',errCreateAccOrder:'Lỗi tạo đơn acc',errEnterCard:'Nhập đủ Serial + Mã thẻ',errMinTopup:'Tối thiểu 10.000đ',errOutAcc:'Hết acc loại này',buyAccWalletOk:'🎉 Đã mua acc thành công bằng ví!',
    balanceLabel:'Số dư',currentBalance:'SỐ DƯ HIỆN TẠI',topupNav:'💰 Nạp tiền',walletBalance:'Số dư ví',balanceHistoryTitle:'📜 Lịch sử ví',topupTitle:'💳 Nạp tiền vào ví',
},
  en:{webTitle:'Open HCLOU in Telegram',webSub:'This web app only works inside Telegram Mini App to verify your account and protect your keys.',openTelegram:'🚀 Open in Telegram',tapChooseGame:'Tap to choose game',noGameSelected:'No game selected',noPackageSelected:'No package selected',promo:'🔥 HOT PROMO!',totalKey:'Total keys',activeLabel:'Active',expiredLabel:'Expired',buyNew:'Buy new key',buySub:'Choose app and duration package',chooseApp:'Choose app',choosePackage:'Choose package',buyNow:'Buy now',yourKeys:'Your keys',all:'All',lockedLabel:'Locked',chooseGameTitle:'🎮 Choose game',paymentTitle:'💳 Payment',search:'Search GKey...',
    tongKey:'Total keys',hoatDong:'Active',hetHan:'Expired',biKhoa:'Locked',tatCa:'All',soNgay:'Days',conLai:'Remaining',batDau:'Start',ketThuc:'End',dangTinh:'⏱ Calculating...',conLaiLbl:'⏱ Remaining: ',reset:'Reset',copy:'📋 Copy',giaHan:'Renew',xoa:'Delete',active:'✅ Active',expired:'⏰ Expired',locked:'🔒 Locked',pending:'⏳ Waiting payment',hetHanLbl:'Expired',chuaCoKey:'No keys yet',goiNgay:'Package ',ngay:' days',cheDoKey:'Mode ',keyMode:' key',khongCoGoi:'No packages available',muaNgay:'Buy now',dangTaiGame:'Loading games...',chonGame:'Choose game',nganHang:'Bank',soTK:'Account number',noiDungCK:'Transfer note',copyTK:'Account copied!',copyDon:'Order code copied!',copyKey:'Key copied!',daCopy:'Copied!',luuY:'⚠️ Scan VietQR to auto-fill amount + content. If transferring manually, enter the exact content above.',daCK:'🔄 Check payment',choAdmin:'Checking payment automatically...',giuManHinh:'Do not close the Mini App while paying. After payment, your key will appear automatically in Your keys.',hetGioTT:'15-minute payment wait ended. If you already paid, reopen Your keys or contact admin with the order code.',daDuyetAuto:'Payment confirmed, key is active!',resetOk:'Reset successfully!',xoaOk:'Key deleted!',confirmReset:'Reset device for this key?',confirmXoa:'Delete this key?',loiKetNoi:'Connection error',moQuaBot:'Please open via Telegram bot!',giaHanMsg:'Choose a new package in Buy Key to renew!',chonGameTruoc:'Please choose a game first',chuaChonGoi:'No package selected',dangTai:'Loading...',loiTaoDon:'Create order failed!',copyFail:'Copy failed',taiKeyLoi:'Cannot load keys. Please close the Mini App and open it again from Telegram bot.',getFree:'Get Key Free',dangLayLink:'Getting link...',freeHet:'No free key available',mienPhi:'Free',vuotLinkNhan:'Complete link to claim key',xacNhan:'Confirm',xacNhanMua:'You selected',capDo:'level',keyMotGame:'this key can only be used for one game. Do you want to continue?',huy:'Cancel',dongY:'Agree',expiredDeleteNote:'If not renewed, this key will be auto-deleted after 3 days',tuXoaLuc:'Auto delete at',helpSub:'Choose a question for quick help',pendingPayTitle:'You have a pending payment',pendingPaySub:'If you closed before saving the QR, tap to reopen payment details.',resumePay:'Reopen payment QR',pendingPayExpired:'This order is older than 15 minutes. If you already paid, contact admin with the order code.',copyTelegramId:'Telegram ID copied!',freeClaimOk:'Free key claimed successfully',freeClaimFail:'Cannot claim free key',pickerTitle:'💳 Choose payment method',pickerMbbankT:'MBBank (VND)',pickerMbbankSub:'VietQR transfer · Auto-approve in ~1 min',pickerBnbT:'Binance USDT (TRC20)',pickerBnbSub:'Crypto · Auto detect on-chain',payViaBnb:'💳 Pay with <b>Binance USDT TRC20</b>',payViaMbb:'💳 Pay with <b>MBBank VietQR</b>',cryptoNet:'Network',cryptoAddr:'Wallet address',cryptoAmt:'USDT amount',cryptoWarn:'⚠️ ONLY send USDT on TRC20 (TRON) network. Wrong network (BEP20/ERC20) = FUNDS LOST permanently!',cryptoExact1:'Send exactly <b>',cryptoExact2:'</b> for auto-confirmation. Wrong amount will not auto-approve.',bankOwnerL:'Account holder',
    confirmTitle:'Confirm',freeDayTitle:'⭐ Daily free key',freeChecking:'Checking key...',historyTitle:'📜 Order history',loadingKeys:'Loading keys...',histEmpty:'No orders yet',histLoading:'Loading history...',histOrderCode:'Order code',histAmount:'Amount',histStatus:'Status',histDate:'Created',histPending:'Pending',histApproved:'Approved',histRejected:'Cancelled',
    profileOverview:'Overview',profileAccount:'Account info',profileLinks:'Quick links',profileTotalOrders:'Total orders',profileApproved:'Approved',profilePending:'Pending',profileKeysOwned:'Keys owned',profileTgId:'Telegram ID',profileUsername:'Username',profileFullName:'Full name',profileJoined:'Joined',profileBtnBuy:'🔑 Buy Key',profileBtnHistory:'📜 Order history',profileBtnReload:'🔄 Reload',
    navBuy:'Buy Key',navFree:'Free Key',navHistory:'History',navProfile:'Profile',
    footerSupport:'Contact support',footerPayment:'Payment methods',footerCert:'Certifications',footerCopyright:'Copyright © 2026 HCLOU Server. All Rights Reserved.',footerAddress:'Address',footerTaxId:'Tax ID',footerBizLic:'Business license',footerUpdating:'Updating',footerHotline:'Hotline',footerComplaint:'Quality complaints',footerEmail:'Contact email',footerRespContent:'Content responsibility',footerFollowTg:'Follow on Telegram',footerSocial:'Social networks',footerCertSite:'Website certifications',footerPaymentSupport:'Payment support',
    freeChecking2:'Checking...',freeClaimedToday:'You already claimed today\'s free key!',freeReceivedAt:'Received at',freeBackTomorrow:'🔄 Come back at 00:00 tomorrow for a new key',freeKeyToday:'Today\'s free key',freeSub:'Get 1 free key per day!<br>Admin pre-added keys, each user gets a unique link',freeBtnGetLink:'🔗 Get Claim Link',freeResetDaily:'⏰ Resets at 00:00 daily',freePeopleSuffix:'people claimed today',freeNoneToday:'No free key today',freeAdminNotYet:'Admin has not added a key yet. Please check back later!',freeNewMorning:'🔄 A new key will be available each morning',freeCannotLoad:'Cannot load free key info',freeCreatingLink:'⏳ Creating link...',freeAlready:'You already claimed today\'s free key!',freeYourLink:'Your personal claim link:',freeOpenLink:'🔓 Open Claim Link',freeCopyLink:'📋 Copy Link',freeCopiedLink:'Link copied!',freeLinkCreated:'✅ Link created — open it to claim key',freeErrorGeneric:'Something went wrong!',toastLoadErr:'Connection error, please retry!',loadingHistory:'Loading history...',histLoadFail:'⚠️ Cannot load order history',histGame:'Game',histPkg:'Package',histCreated:'Created',histPkgDays:'days',statusApproved:'Approved',statusRejected:'Rejected',statusCancelled:'Cancelled',statusPending:'Pending',accTitle:'Buy Account',accSubtitle:'Choose account type (Google, Facebook...)',accChooseLabel:'Choose game & account type',accPickGameFirst:'Pick a game first',accQtyLabel:'Account quantity',accQtyHint:'1 account per order · change password right after receiving',accBuyBtn:'Buy Account',accNoTypeSelected:'No account type selected',accNote:'⚠️ Each account is sold once. Change password immediately after receiving.',accMyTitle:'My accounts',accNoneYet:'No account yet',navAcc:'Buy Acc',soLuongKey:'Key quantity',gio:' hour(s)',totalLbl:'Total',pickerBalT:'Wallet balance',pickerBalSubOk:'Pay from wallet · Get key instantly',pickerBalSubNo:'Balance not enough, top up',pickerCardT:'Mobile card',pickerCardSub:'Viettel/Mobifone/Vinaphone (via wallet)',errNoAccGame:'No account game yet. Contact admin.',errNoDownload:'This game has no download link',errSendCard:'Card submit error',errWallet:'Wallet deduction error',buyKeyWalletOk:'🎉 Key purchased with wallet!',errMaxTopup:'Max 50M per time',errCreateTopup:'Top-up request error',errGeneric:'Something went wrong!',errCreateAccOrder:'Account order error',errEnterCard:'Enter both Serial + Card code',errMinTopup:'Minimum 10,000đ',errOutAcc:'Out of this account type',buyAccWalletOk:'🎉 Account purchased with wallet!',
    accChooseLabel:'Choose game & account type',
    accMyTitle:'My Accounts',
    accNoTypeSelected:'No account type selected',
    accNoneYet:'No accounts yet',
    accNote:'⚠️ Each account is sold once. Change password immediately after receiving.',
    accPickGameFirst:'Select a game first',
    accQtyHint:'1 account per order · change password immediately',
    accQtyLabel:'Account quantity',
    accSubtitle:'Choose account type (Google, Facebook...)',
    balanceHistoryTitle:'📜 Wallet History',
    balanceLabel:'Balance',
    buyNew:'Buy new key',
    buySub:'Choose app and duration package',
    chonGame:'Choose game',
    chooseApp:'Choose app',
    chooseGameTitle:'🎮 Choose game',
    choosePackage:'Choose package',
    currentBalance:'CURRENT BALANCE',
    footerAddress:'Address',
    footerBizLic:'Business license',
    footerCert:'Certifications',
    footerCertSite:'Website certifications',
    footerComplaint:'Quality complaints',
    footerEmail:'Contact email',
    footerPayment:'Payment methods',
    footerPaymentSupport:'Payment support',
    footerRespContent:'Content responsibility',
    footerSocial:'Social networks',
    footerSupport:'Contact support',
    footerTaxId:'Tax ID',
    footerUpdating:'Updating',
    freeChecking:'Checking keys...',
    freeDayTitle:'⭐ Daily Free Key',
    historyTitle:'📜 Order History',
    noGameSelected:'No game selected',
    noPackageSelected:'No package selected',
    profileBtnReload:'🔄 Reload',
    tapChooseGame:'Tap to choose game',
    topupNav:'💰 Top Up',
    topupTitle:'💳 Top Up Wallet',
    walletBalance:'Wallet balance',
},
  es:{webTitle:'Abrir HCLOU en Telegram',webSub:'Esta web solo funciona dentro de Telegram Mini App para verificar tu cuenta y proteger tus claves.',openTelegram:'🚀 Abrir en Telegram',tapChooseGame:'Toca para elegir juego',noGameSelected:'Sin juego seleccionado',noPackageSelected:'Sin paquete seleccionado',promo:'🔥 ¡PROMO CALIENTE!',totalKey:'Total de claves',activeLabel:'Activa',expiredLabel:'Expirada',buyNew:'Comprar clave',buySub:'Elige aplicación y paquete',chooseApp:'Elegir aplicación',choosePackage:'Elegir paquete',buyNow:'Comprar ahora',yourKeys:'Tus claves',all:'Todas',lockedLabel:'Bloqueada',chooseGameTitle:'🎮 Elegir juego',paymentTitle:'💳 Pago',search:'Buscar GKey...',
    tongKey:'Total de claves',hoatDong:'Activa',hetHan:'Expirada',biKhoa:'Bloqueada',tatCa:'Todas',soNgay:'Días',conLai:'Restante',batDau:'Inicio',ketThuc:'Fin',dangTinh:'⏱ Calculando...',conLaiLbl:'⏱ Restante: ',reset:'Restablecer',copy:'📋 Copiar',giaHan:'Renovar',xoa:'Eliminar',active:'✅ Activa',expired:'⏰ Expirada',locked:'🔒 Bloqueada',pending:'⏳ Esperando pago',hetHanLbl:'Expirada',chuaCoKey:'Sin claves aún',goiNgay:'Paquete ',ngay:' días',cheDoKey:'Modo ',keyMode:' clave',khongCoGoi:'No hay paquetes disponibles',muaNgay:'Comprar ahora',dangTaiGame:'Cargando juegos...',chonGame:'Elegir juego',nganHang:'Banco',soTK:'Número de cuenta',noiDungCK:'Nota de transferencia',copyTK:'¡Cuenta copiada!',copyDon:'¡Código de orden copiado!',copyKey:'¡Clave copiada!',daCopy:'¡Copiado!',luuY:'⚠️ Escanea VietQR para auto-completar el monto y la nota. Si transfieres manualmente, incluye exactamente la nota anterior.',daCK:'🔄 Verificar pago',choAdmin:'Verificando pago automáticamente...',giuManHinh:'No cierres la Mini App mientras pagas. Después del pago, tu clave aparecerá automáticamente en Tus claves.',hetGioTT:'Terminaron los 15 minutos de espera. Si ya pagaste, abre Tus claves o contacta al admin con el código de orden.',daDuyetAuto:'¡Pago confirmado, la clave está activa!',resetOk:'¡Restablecido con éxito!',xoaOk:'¡Clave eliminada!',confirmReset:'¿Restablecer dispositivo para esta clave?',confirmXoa:'¿Eliminar esta clave?',loiKetNoi:'Error de conexión',moQuaBot:'¡Por favor abre vía el bot de Telegram!',giaHanMsg:'¡Elige un paquete nuevo en Comprar Clave para renovar!',chonGameTruoc:'Por favor elige un juego primero',chuaChonGoi:'Sin paquete seleccionado',dangTai:'Cargando...',loiTaoDon:'¡Error al crear la orden!',copyFail:'Error al copiar',taiKeyLoi:'No se pueden cargar las claves. Cierra la Mini App y reábrela desde el bot de Telegram.',getFree:'Obtener Clave Gratis',dangLayLink:'Obteniendo enlace...',freeHet:'Sin claves gratis disponibles',mienPhi:'Gratis',vuotLinkNhan:'Completa el enlace para reclamar la clave',xacNhan:'Confirmar',xacNhanMua:'Has seleccionado',capDo:'nivel',keyMotGame:'esta clave solo puede usarse para un juego. ¿Deseas continuar?',huy:'Cancelar',dongY:'Aceptar',expiredDeleteNote:'Si no se renueva, esta clave se eliminará automáticamente después de 3 días',tuXoaLuc:'Auto-eliminar a las',helpSub:'Elige una pregunta para ayuda rápida',pendingPayTitle:'Tienes un pago pendiente',pendingPaySub:'Si cerraste antes de guardar el QR, toca para reabrir los detalles de pago.',resumePay:'Reabrir QR de pago',pendingPayExpired:'Esta orden tiene más de 15 minutos. Si ya pagaste, contacta al admin con el código.',copyTelegramId:'¡Telegram ID copiado!',freeClaimOk:'Clave gratis reclamada con éxito',freeClaimFail:'No se pudo reclamar la clave gratis',pickerTitle:'💳 Elige método de pago',pickerMbbankT:'MBBank (VND)',pickerMbbankSub:'Transferencia VietQR · Auto-aprobación ~1 min',pickerBnbT:'Binance USDT (TRC20)',pickerBnbSub:'Crypto · Detección on-chain automática',payViaBnb:'💳 Pagar con <b>Binance USDT TRC20</b>',payViaMbb:'💳 Pagar con <b>MBBank VietQR</b>',cryptoNet:'Red',cryptoAddr:'Dirección de billetera',cryptoAmt:'Cantidad USDT',cryptoWarn:'⚠️ SOLO envía USDT en la red TRC20 (TRON). Red incorrecta (BEP20/ERC20) = FONDOS PERDIDOS para siempre!',cryptoExact1:'Envía exactamente <b>',cryptoExact2:'</b> para detección automática. Cantidad incorrecta no se aprobará automáticamente.',bankOwnerL:'Titular',
    confirmTitle:'Confirmar',freeDayTitle:'⭐ Clave gratis diaria',freeChecking:'Verificando clave...',historyTitle:'📜 Historial de órdenes',loadingKeys:'Cargando claves...',histEmpty:'Aún no hay órdenes',histLoading:'Cargando historial...',histOrderCode:'Código',histAmount:'Monto',histStatus:'Estado',histDate:'Creado',histPending:'Pendiente',histApproved:'Aprobado',histRejected:'Cancelado',
    profileOverview:'Resumen',profileAccount:'Datos de cuenta',profileLinks:'Enlaces rápidos',profileTotalOrders:'Total órdenes',profileApproved:'Aprobadas',profilePending:'Pendientes',profileKeysOwned:'Claves activas',profileTgId:'Telegram ID',profileUsername:'Usuario',profileFullName:'Nombre completo',profileJoined:'Se unió',profileBtnBuy:'🔑 Comprar Clave',profileBtnHistory:'📜 Historial',profileBtnReload:'🔄 Recargar',
    navBuy:'Comprar',navFree:'Gratis',navHistory:'Historial',navProfile:'Perfil',
    footerSupport:'Soporte',footerPayment:'Métodos de pago',footerCert:'Certificaciones',footerCopyright:'Copyright © 2026 HCLOU Server. Todos los derechos reservados.',footerAddress:'Dirección',footerTaxId:'NIF',footerBizLic:'Licencia comercial',footerUpdating:'Actualizando',footerHotline:'Línea directa',footerComplaint:'Reclamos de calidad',footerEmail:'Email de contacto',footerRespContent:'Responsable del contenido',footerFollowTg:'Síguenos en Telegram',footerSocial:'Redes sociales',footerCertSite:'Certificaciones del sitio',footerPaymentSupport:'Soporte de pago',
    freeChecking2:'Verificando...',freeClaimedToday:'¡Ya reclamaste la clave gratis de hoy!',freeReceivedAt:'Recibida a las',freeBackTomorrow:'🔄 Vuelve a las 00:00 mañana para otra clave',freeKeyToday:'Clave gratis de hoy',freeSub:'¡Obtén 1 clave gratis por día!<br>El admin ya añadió claves, cada usuario tiene un enlace único',freeBtnGetLink:'🔗 Obtener Enlace',freeResetDaily:'⏰ Se reinicia a las 00:00 diariamente',freePeopleSuffix:'personas reclamaron hoy',freeNoneToday:'Sin clave gratis hoy',freeAdminNotYet:'El admin aún no añadió clave. ¡Vuelve más tarde!',freeNewMorning:'🔄 Una clave nueva estará disponible cada mañana',freeCannotLoad:'No se puede cargar la información',freeCreatingLink:'⏳ Creando enlace...',freeAlready:'¡Ya reclamaste la clave gratis de hoy!',freeYourLink:'Tu enlace personal de reclamo:',freeOpenLink:'🔓 Abrir Enlace',freeCopyLink:'📋 Copiar Enlace',freeCopiedLink:'¡Enlace copiado!',freeLinkCreated:'✅ Enlace creado — ábrelo para reclamar la clave',freeErrorGeneric:'¡Algo salió mal!',toastLoadErr:'Error de conexión, ¡reintenta!',loadingHistory:'Cargando historial...',histLoadFail:'⚠️ No se puede cargar el historial',histGame:'Juego',histPkg:'Paquete',histCreated:'Creado',histPkgDays:'días',statusApproved:'Aprobado',statusRejected:'Rechazado',statusCancelled:'Cancelado',statusPending:'Pendiente',accTitle:'Comprar Cuenta',accSubtitle:'Elige tipo de cuenta (Google, Facebook...)',accChooseLabel:'Elige juego y tipo de cuenta',accPickGameFirst:'Elige un juego primero',accQtyLabel:'Cantidad de cuentas',accQtyHint:'1 cuenta por pedido · cambia la contraseña inmediatamente',accBuyBtn:'Comprar Cuenta',accNoTypeSelected:'Sin tipo seleccionado',accNote:'⚠️ Cada cuenta se vende una vez. Cambia la contraseña al recibirla.',accMyTitle:'Mis cuentas',accNoneYet:'Sin cuentas aún',navAcc:'Cuenta',soLuongKey:'Cantidad de claves',gio:' hora(s)',totalLbl:'Total',pickerBalT:'Saldo de billetera',pickerBalSubOk:'Pagar desde billetera · Clave al instante',pickerBalSubNo:'Saldo insuficiente, recarga',pickerCardT:'Tarjeta móvil',pickerCardSub:'Viettel/Mobifone/Vinaphone (vía billetera)',errNoAccGame:'Sin juego de cuentas aún. Contacta al admin.',errNoDownload:'Este juego no tiene enlace de descarga',errSendCard:'Error al enviar tarjeta',errWallet:'Error al descontar billetera',buyKeyWalletOk:'🎉 ¡Clave comprada con billetera!',errMaxTopup:'Máx 50M por vez',errCreateTopup:'Error al crear recarga',errGeneric:'¡Algo salió mal!',errCreateAccOrder:'Error al crear pedido de cuenta',errEnterCard:'Ingresa Serial + Código',errMinTopup:'Mínimo 10.000đ',errOutAcc:'Sin cuentas de este tipo',buyAccWalletOk:'🎉 ¡Cuenta comprada con billetera!',
    accChooseLabel:'Choose game & account type',
    accMyTitle:'My Accounts',
    accNoTypeSelected:'Sin tipo de cuenta seleccionado',
    accNoneYet:'No accounts yet',
    accNote:'⚠️ Each account is sold once. Change password immediately after receiving.',
    accPickGameFirst:'Selecciona un juego primero',
    accQtyHint:'1 account per order · change password immediately',
    accQtyLabel:'Account quantity',
    accSubtitle:'Choose account type (Google, Facebook...)',
    balanceHistoryTitle:'📜 Wallet History',
    balanceLabel:'Saldo',
    buyNew:'Buy new key',
    buySub:'Choose app and duration package',
    chonGame:'Choose game',
    chooseApp:'Choose app',
    chooseGameTitle:'🎮 Elegir juego',
    choosePackage:'Choose package',
    currentBalance:'CURRENT BALANCE',
    footerAddress:'Address',
    footerBizLic:'Business license',
    footerCert:'Certifications',
    footerCertSite:'Website certifications',
    footerComplaint:'Quality complaints',
    footerEmail:'Contact email',
    footerPayment:'Payment methods',
    footerPaymentSupport:'Payment support',
    footerRespContent:'Content responsibility',
    footerSocial:'Social networks',
    footerSupport:'Contact support',
    footerTaxId:'Tax ID',
    footerUpdating:'Updating',
    freeChecking:'Checking keys...',
    freeDayTitle:'⭐ Clave gratis diaria',
    historyTitle:'📜 Order History',
    noGameSelected:'No game selected',
    noPackageSelected:'No package selected',
    profileBtnReload:'🔄 Reload',
    tapChooseGame:'Toca para elegir juego',
    topupNav:'💰 Recargar',
    topupTitle:'💳 Top Up Wallet',
    walletBalance:'Wallet balance',
}
};
var T=I18N[LANG]||I18N.vi;
function applyLang(){
  T=I18N[LANG]||I18N.vi; localStorage.setItem('hclou_lang',LANG);
  document.documentElement.lang=LANG; renderHelpBot();
  document.querySelectorAll('.lang-pill').forEach(function(p){ p.classList.toggle('active', p.getAttribute('data-lang')===LANG); });
  document.querySelectorAll('[data-i18n]').forEach(function(el){var k=el.getAttribute('data-i18n'); if(T[k]) el.textContent=T[k];});
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function(el){var k=el.getAttribute('data-i18n-placeholder'); if(T[k]) el.placeholder=T[k];});
  updBuyBtn(); if(allKeys&&allKeys.length) renderKeys(allKeys); renderPendingPayments();
  // Re-render tabs có innerHTML đã được build trước đó (mất i18n khi đổi ngôn ngữ)
  try{
    if(typeof histLoaded!=='undefined' && histLoaded && typeof loadHistory==='function'){ histLoaded=false; loadHistory(); }
    if(typeof freeKeyLoaded!=='undefined' && freeKeyLoaded && typeof loadFreeKey==='function'){ freeKeyLoaded=false; loadFreeKey(); }
  }catch(e){}
}
function setLang(code){
  if(['vi','en','es'].indexOf(code)<0) code='vi';
  if(LANG===code) return;
  LANG=code; applyLang();
  var msg=(code==='vi')?'Đã đổi sang Tiếng Việt':(code==='en')?'Switched to English':'Cambiado a Español';
  toast(msg,'success');
}
function toggleLang(){ setLang(LANG==='vi'?'en':LANG==='en'?'es':'vi'); }


const helpFAQs={
  vi:[
    ['🛒 Cách mua key?', 'Vào mục Mua Key → chọn game → chọn gói ngày → bấm Mua ngay → xác nhận → quét VietQR. Sau khi chuyển đúng số tiền và đúng mã ORD, key sẽ tự active trong mục Key của bạn.'],
    ['💳 Thanh toán như thế nào?', 'Hãy quét VietQR trong popup để tự điền đúng số tiền và nội dung chuyển khoản. Nếu chuyển tay, bắt buộc ghi đúng mã đơn ORD... để hệ thống tự duyệt.'],
    ['⏳ Chuyển tiền rồi key chưa active?', 'Giữ Mini App mở trong tối đa 15 phút. Hệ thống kiểm tra bank tự động khoảng mỗi 5 giây. Nếu quá 2 phút chưa active, chụp bill và gửi admin kèm mã đơn ORD.'],
    ['🎁 Get Key Free là gì?', 'Đây là key miễn phí. Bạn chọn Get Key Free rồi vượt Link4M → YeuMoney → quay lại HCLOU để nhận key. Mỗi key free chỉ claim được một lần.'],
    ['🔑 Key hết hạn thì sao?', 'Key hết hạn sẽ hiện trạng thái Expired. Nếu không gia hạn trong 3 ngày kể từ lúc hết hạn, hệ thống sẽ tự xoá key.'],
    ['📱 Web báo phải mở Telegram?', 'HCLOU chỉ cho dùng trong Telegram Mini App để bảo mật user/key. Hãy mở bot HCLOU rồi bấm Mua Key.'],
    ['👨‍💻 Cần hỗ trợ admin?', 'Nếu lỗi thanh toán hoặc key không dùng được, gửi admin: mã đơn ORD, ảnh bill, Telegram ID và ảnh lỗi trong game.']
  ],
  en:[
    ['🛒 How to buy a key?', 'Open Buy Key → choose game → choose package → tap Buy now → confirm → scan VietQR. After the correct amount and ORD code are received, your key activates automatically.'],
    ['💳 How to pay?', 'Scan the VietQR in the popup so amount and transfer note are filled automatically. Manual transfer must include the exact ORD code.'],
    ['⏳ Paid but key is not active?', 'Keep the Mini App open for up to 15 minutes. Bank is checked about every 5 seconds. If still inactive, send admin the bill screenshot and ORD code.'],
    ['🎁 What is Get Key Free?', 'A free key flow. Complete Link4M → YeuMoney → return to HCLOU to claim the key. Each free key can be claimed once.'],
    ['🔑 What happens when key expires?', 'Expired keys stay visible for 3 days. If not renewed, the system will delete them automatically.'],
    ['📱 Site says open Telegram?', 'HCLOU only works inside Telegram Mini App for user/key security. Open the bot and tap Buy Key.'],
    ['👨‍💻 Need admin support?', 'For payment/key issues, send admin: ORD code, bill screenshot, Telegram ID and game error screenshot.']
  ],
  es:[
    ['🛒 ¿Cómo comprar una clave?', 'Abre Comprar Clave → elige el juego → elige el paquete → toca Comprar ahora → confirma → escanea el VietQR. Cuando se reciba el monto correcto y el código ORD, tu clave se activará automáticamente.'],
    ['💳 ¿Cómo pagar?', 'Escanea el VietQR en el popup para auto-completar el monto y la nota de transferencia. Las transferencias manuales deben incluir el código ORD exacto.'],
    ['⏳ Pagué pero la clave no se activa', 'Mantén la Mini App abierta hasta 15 minutos. El banco se verifica cada ~5 segundos. Si sigue inactiva, envía al admin la captura del comprobante y el código ORD.'],
    ['🎁 ¿Qué es la Clave Gratis?', 'Es un flujo de clave gratuita. Completa Link4M → YeuMoney → regresa a HCLOU para reclamar la clave. Cada clave gratis solo puede reclamarse una vez.'],
    ['🔑 ¿Qué pasa cuando expira la clave?', 'Las claves expiradas siguen visibles 3 días. Si no se renuevan, el sistema las eliminará automáticamente.'],
    ['📱 ¿La página pide abrir Telegram?', 'HCLOU solo funciona dentro de la Mini App de Telegram por seguridad. Abre el bot y toca Comprar Clave.'],
    ['👨‍💻 ¿Necesito soporte del admin?', 'Para problemas de pago/clave, envía al admin: código ORD, captura del comprobante, Telegram ID y captura del error en el juego.']
  ]
};
function renderHelpBot(){var box=document.getElementById('helpBody'); if(!box)return; box.innerHTML=helpFAQs[LANG].map(function(x,i){return '<button class="help-q" onclick="showHelpAnswer('+i+')">'+x[0]+'</button><div class="help-a" id="helpA'+i+'">'+x[1]+'</div>';}).join('');}
function toggleHelpBot(force){var p=document.getElementById('helpPanel'); if(!p)return; renderHelpBot(); var show=typeof force==='boolean'?force:!p.classList.contains('show'); p.classList.toggle('show',show);}
function showHelpAnswer(i){document.querySelectorAll('.help-a').forEach(function(a){a.classList.remove('show');}); var a=document.getElementById('helpA'+i); if(a)a.classList.add('show');}


var APP_VERSION='payauto20260605_2';
var pendingClaimToken=new URLSearchParams(location.search).get('claim')||'';
var BOT_USERNAME=window.HCLOU_BOT_USERNAME||'';
var TG_OPEN_URL='https://t.me/'+BOT_USERNAME+'?start=webapp';

// Dùng DOMContentLoaded thay window.onload — nhanh hơn, không block ảnh/font
document.addEventListener('DOMContentLoaded', function(){ tryInit(0); });

function showTelegramOnly(){
  document.getElementById('loadingScreen').classList.add('hide');
  document.getElementById('app').style.display='none';
  var w=document.getElementById('webOnly');
  var b=document.getElementById('openTelegramBtn');
  if(b)b.href=TG_OPEN_URL;
  w.classList.add('show');
  // Chỉ tự chuyển sang Telegram khi đã cấu hình BOT_USERNAME (tránh redirect tới link hỏng).
  // Cho user 2.5s để đọc màn hình "Mở trong Telegram" trước khi tự chuyển.
  if(BOT_USERNAME){
    setTimeout(function(){ try{ window.location.href=TG_OPEN_URL; }catch(e){} },2500);
  }
}
function tryInit(n){
  n=n||0;
  var tg=window.Telegram&&window.Telegram.WebApp;
  var uid=tg&&tg.initDataUnsafe&&tg.initDataUnsafe.user&&tg.initDataUnsafe.user.id;
  if(uid){
    startApp(tg);
    return;
  }
  // Có initData (đang ở trong Telegram, user load chậm) -> chờ kỹ 20 lần.
  // Không có initData (trình duyệt thường) -> chỉ thử ~6 lần rồi hiện cổng "Mở trong Telegram" (~1.5s).
  var maxTries = (tg && tg.initData && tg.initData.length > 0) ? 20 : 6;
  if(n < maxTries){
    var delay=Math.min(700, 100*Math.pow(1.4,n));
    setTimeout(function(){ tryInit(n+1); }, delay);
  } else {
    showTelegramOnly();
  }
}

function setUserRole(){
  if(!currentUser) return;
  var role = currentUser.role || 'customer';
  var labels = LANG==='en' ? {customer:'Customer',reseller:'Reseller',admin:'Admin'} : LANG==='es' ? {customer:'Cliente',reseller:'Revendedor',admin:'Admin'} : {customer:'Khách hàng',reseller:'Reseller',admin:'Admin'};
  var classes = {customer:'role-customer', reseller:'role-reseller', admin:'role-admin'};
  var cls = classes[role] || 'role-customer';
  var lbl = labels[role] || labels.customer;
  var html = '<span class="role-badge '+cls+'">'+lbl+'</span>';
  var e1 = document.getElementById('pRole');
  var e2 = document.getElementById('pRole2');
  if(e1) e1.innerHTML = html;
  if(e2) e2.innerHTML = html;
}

async function startApp(tg){
  tg.ready(); tg.expand();
  tgInitData=tg.initData||'';
  var u=tg.initDataUnsafe.user;
  var res=await api('auth','POST',{
    telegram_id:u.id,username:u.username||'',
    full_name:((u.first_name||'')+' '+(u.last_name||'')).trim(),
    avatar_url:u.photo_url||''
  });
  if(res.success){
    currentUser=res.user; appToken=res.app_token||'';
    var n=currentUser.full_name||'User';
    document.getElementById('pName').textContent=n;
    document.getElementById('pHandle').textContent='@'+(currentUser.telegram_username||'user');
    var _ti=document.getElementById('telegramIdText'); if(_ti)_ti.textContent=currentUser.telegram_id;
    setUserRole();
    var init=n.split(' ').map(function(w){return w[0]||'';}).join('').slice(0,2).toUpperCase();
    if(currentUser.avatar_url){
      document.getElementById('avatarEl').innerHTML='<img src="'+escapeHtml(safeUrl(currentUser.avatar_url))+'" alt="">';
    } else {
      document.getElementById('avatarInit').textContent=init;
    }
    loadKeys('all');
    loadPendingPayments();
    loadBalance();
    processPendingClaim();
  } else {
    // Fallback: Telegram đã có user nhưng /auth lỗi thì vẫn thử tải key bằng telegram_id.
    currentUser={telegram_id:u.id,telegram_username:u.username||'',full_name:((u.first_name||'')+' '+(u.last_name||'')).trim(),avatar_url:u.photo_url||''};
    var _ti=document.getElementById('telegramIdText'); if(_ti)_ti.textContent=currentUser.telegram_id;
    document.getElementById('pName').textContent=currentUser.full_name||'User';
    document.getElementById('pHandle').textContent='@'+(currentUser.telegram_username||'user');
    loadKeys('all');
    loadPendingPayments();
    processPendingClaim();
    toast(res.error||T.taiKeyLoi,'error');
  }
  setTimeout(function(){
    var ls=document.getElementById('loadingScreen');
    ls.classList.add('hide');
    document.getElementById('app').style.opacity='1';
    setTimeout(function(){ ls.style.display='none'; },300);
  },600);
}



async function processPendingClaim(){
  if(!pendingClaimToken || !currentUser) return;
  var token=pendingClaimToken; pendingClaimToken='';
  var res=await api('claim_free_key','POST',{token:token});
  if(res.success){ toast(res.message||T.freeClaimOk,'success'); loadKeys('all'); }
  else { toast(res.error||T.freeClaimFail,'error'); }
  try{ history.replaceState(null,'',location.pathname+'?v='+encodeURIComponent(APP_VERSION)); }catch(e){}
}


function copyTelegramId(){
  if(currentUser&&currentUser.telegram_id){
    copyText(String(currentUser.telegram_id),T.copyTelegramId);
  } else {
    toast(T.moQuaBot,'error');
  }
}

async function api(action,method,body){
  method=method||'GET';
  var url=API+'?action='+action+'&_v='+encodeURIComponent(APP_VERSION);
  var opts={method:method};
  if(method==='POST'){
    var fd=new FormData();
    fd.append('action',action);
    if(tgInitData) fd.append('init_data',tgInitData);
    if(appToken) fd.append('app_token',appToken);
    if(currentUser&&currentUser.telegram_id) fd.append('telegram_id',currentUser.telegram_id);
    if(body) Object.keys(body).forEach(function(k){ fd.append(k,body[k]); });
    opts.body=fd;
  } else if(!/^(games|packages)/.test(action)) {
    var extra=[];
    if(appToken) extra.push('app_token=' + encodeURIComponent(appToken));
    if(currentUser&&currentUser.telegram_id) extra.push('telegram_id=' + encodeURIComponent(currentUser.telegram_id));
    else if(tgInitData) extra.push('init_data=' + encodeURIComponent(tgInitData));
    if(extra.length) url += '&' + extra.join('&');
  }
  try{ var r=await fetch(url,opts); var j=await r.json(); if(j&&j.error) j.error=translateErr(j.error); return j; }
  catch(e){ return {error:T.loiKetNoi}; }
}

// Dịch message lỗi server (tiếng Việt) sang ngôn ngữ UI. Khớp full hoặc theo prefix.
var ERR_MAP={
  'Action không hợp lệ':{en:'Invalid action',es:'Acción inválida'},
  'Bạn đã có đơn gói này đang chờ thanh toán':{en:'You already have a pending order for this package',es:'Ya tienes un pedido pendiente para este paquete'},
  'Bạn đang có quá nhiều đơn chờ thanh toán':{en:'You have too many pending orders',es:'Tienes demasiados pedidos pendientes'},
  'Bạn đang có quá nhiều đơn chờ thanh toán, vui lòng hoàn tất hoặc chờ đơn cũ hết hiệu lực':{en:'Too many pending orders, please complete or wait for old ones to expire',es:'Demasiados pedidos pendientes, completa o espera a que expiren'},
  'Bạn đang có quá nhiều yêu cầu nạp đang chờ, hoàn tất hoặc chờ hết hạn':{en:'Too many pending top-up requests, complete or wait for expiry',es:'Demasiadas recargas pendientes, completa o espera la expiración'},
  'Bạn đã nhận key free này rồi':{en:'You already claimed this free key',es:'Ya reclamaste esta clave gratis'},
  'Bạn thao tác quá nhanh, vui lòng chờ 30 giây rồi thử lại':{en:'Too fast, please wait 30 seconds and try again',es:'Demasiado rápido, espera 30 segundos e inténtalo de nuevo'},
  'Binance USDT đang tạm khoá':{en:'Binance USDT is temporarily locked',es:'Binance USDT está bloqueado temporalmente'},
  'Cần mở qua Telegram Mini App (init_data không hợp lệ)':{en:'Please open via Telegram Mini App (invalid init_data)',es:'Abre vía Telegram Mini App (init_data inválido)'},
  'Chưa có key free hôm nay! Admin sẽ thêm vào buổi sáng.':{en:'No free key today! Admin will add in the morning.',es:'¡Sin clave gratis hoy! El admin la añadirá por la mañana.'},
  'Chưa có key free khả dụng':{en:'No free key available',es:'Sin clave gratis disponible'},
  'Chưa đăng nhập':{en:'Not logged in',es:'No has iniciado sesión'},
  'Đã hết lượt reset!':{en:'No reset attempts left!',es:'¡Sin intentos de reinicio!'},
  'GetKey Free đang tắt':{en:'Free key is disabled',es:'Clave gratis desactivada'},
  'Gói không tồn tại':{en:'Package not found',es:'Paquete no encontrado'},
  'Hết acc cho loại này':{en:'Out of accounts for this type',es:'Sin cuentas de este tipo'},
  'Hết acc cho loại này. Vui lòng liên hệ admin để được hỗ trợ.':{en:'Out of accounts for this type. Please contact admin.',es:'Sin cuentas de este tipo. Contacta al admin.'},
  'Hết key cho gói này':{en:'Out of keys for this package',es:'Sin claves para este paquete'},
  'Hết key cho gói này. Vui lòng liên hệ admin để được hỗ trợ.':{en:'Out of keys for this package. Please contact admin.',es:'Sin claves para este paquete. Contacta al admin.'},
  'Key free đã hết hạn':{en:'Free key expired',es:'Clave gratis expirada'},
  'key_id không hợp lệ':{en:'Invalid key_id',es:'key_id inválido'},
  'Key không active!':{en:'Key is not active!',es:'¡La clave no está activa!'},
  'Key không tồn tại':{en:'Key not found',es:'Clave no encontrada'},
  'Không lấy được tỉ giá USDT, vui lòng thử lại sau ít phút.':{en:'Cannot fetch USDT rate, please try again in a few minutes.',es:'No se pudo obtener la tasa USDT, inténtalo en unos minutos.'},
  'Không nhận được key':{en:'Could not get key',es:'No se pudo obtener la clave'},
  'Không nhận được key. Vui lòng thử lại.':{en:'Could not get key. Please try again.',es:'No se pudo obtener la clave. Inténtalo de nuevo.'},
  'Link claim không hợp lệ':{en:'Invalid claim link',es:'Enlace de reclamo inválido'},
  'Link claim không thuộc về bạn':{en:'This claim link is not yours',es:'Este enlace de reclamo no es tuyo'},
  'Loại acc không tồn tại':{en:'Account type not found',es:'Tipo de cuenta no encontrado'},
  'Mệnh giá không hợp lệ':{en:'Invalid card value',es:'Valor de tarjeta inválido'},
  'Method chưa hỗ trợ':{en:'Method not supported',es:'Método no soportado'},
  'Method không hợp lệ':{en:'Invalid method',es:'Método inválido'},
  'Nạp thẻ chưa cấu hình':{en:'Card top-up not configured',es:'Recarga con tarjeta no configurada'},
  'Nhà mạng không hợp lệ':{en:'Invalid carrier',es:'Operador inválido'},
  'Nhập đủ Serial + Mã thẻ':{en:'Enter both Serial + Card code',es:'Ingresa Serial + Código de tarjeta'},
  'Phương thức thanh toán không hợp lệ':{en:'Invalid payment method',es:'Método de pago inválido'},
  'Serial/mã thẻ quá dài':{en:'Serial/card code too long',es:'Serial/código demasiado largo'},
  'Thanh toán Binance USDT đang tạm khoá. Vui lòng chọn MBBank.':{en:'Binance USDT payment temporarily locked. Please choose MBBank.',es:'Pago Binance USDT bloqueado. Elige MBBank.'},
  'Thiếu token claim':{en:'Missing claim token',es:'Falta el token de reclamo'},
  'Tối đa 50.000.000đ':{en:'Maximum 50,000,000đ',es:'Máximo 50.000.000đ'},
  'Tối thiểu 10.000đ':{en:'Minimum 10,000đ',es:'Mínimo 10.000đ'}
};
// Prefix (message có phần động phía sau): khớp đầu chuỗi
var ERR_PREFIX=[
  ['Không tạo được link:',{en:'Could not create link:',es:'No se pudo crear el enlace:'}],
  ['Lỗi tạo đơn hàng:',{en:'Order creation error:',es:'Error al crear pedido:'}],
  ['Lỗi tạo đơn:',{en:'Order creation error:',es:'Error al crear pedido:'}],
  ['Số dư không đủ: cần',{en:'Insufficient balance: need',es:'Saldo insuficiente: necesitas'}],
  ['Trừ ví thất bại:',{en:'Wallet deduction failed:',es:'Error al descontar de la billetera:'}],
  ['Lỗi:',{en:'Error:',es:'Error:'}]
];
function translateErr(msg){
  if(!msg||LANG==='vi')return msg;
  var t=ERR_MAP[msg];
  if(t&&t[LANG])return t[LANG];
  for(var i=0;i<ERR_PREFIX.length;i++){
    var p=ERR_PREFIX[i][0];
    if(msg.indexOf(p)===0){ var tr=ERR_PREFIX[i][1][LANG]||p; return tr+msg.slice(p.length); }
  }
  return msg; // không có bản dịch → giữ nguyên
}

async function openGameModal(){
  document.getElementById('gameModal').classList.add('show');
  if(true){
    var res=await api('games&category=key');
    if(!res.success)return;
    window._keyGames=res.games;
  }
  gCache=window._keyGames||[];
  buildGameList();
}
async function openAccGameModal(){
  if(true){
    var res=await api('games&category=account');
    if(!res.success)return;
    gCache=res.games;
  }
  if(!gCache.length){ toast(T.errNoAccGame,'error'); return; }
  document.getElementById('gameModal').classList.add('show');
  buildAccGameList();
}
function buildAccGameList(){
  var html='';
  gCache.forEach(function(g){
    var iconUrl=safeUrl(g.icon_url);
    var pkg=safePackageName(g.package_name);
    var ic=iconUrl?'<img src="'+escapeHtml(iconUrl)+'" alt="">':(ICONS[pkg]||'🎮');
    var tag=g.type==='VIP'?'<span class="vip-tag">⭐ VIP</span>':'<span class="normal-tag">NORMAL</span>';
    var sel=(selAccGame&&selAccGame.id==g.id)?' on':'';
    html+='<div class="mgame'+sel+'" onclick="pickAccGame('+(parseInt(g.id,10)||0)+')">'
      +'<div class="game-emoji">'+ic+'</div>'
      +'<div style="flex:1"><div class="game-title">'+escapeHtml(g.name)+tag+'</div>'
      +'<div class="game-pkgname">'+escapeHtml(pkg)+'</div></div>'
      +'<div class="chev">&#x203A;</div></div>';
  });
  document.getElementById('gameList').innerHTML=html||'<div style="text-align:center;color:var(--text2);padding:24px">Ch&#x1B0;a c&#xF3; game n&#xE0;o</div>';
  initMotion();
}
function buildGameList(){
  var html='';
  gCache.forEach(function(g){
    var iconUrl=safeUrl(g.icon_url);
    var pkg=safePackageName(g.package_name);
    var ic=iconUrl?'<img src="'+escapeHtml(iconUrl)+'" alt="">':(ICONS[pkg]||'\uD83C\uDFAE');
    var tag=g.type==='VIP'?'<span class="vip-tag">\u2B50 VIP</span>':'<span class="normal-tag">NORMAL</span>';
    var sel=(selGame&&selGame.id==g.id)?' on':'';
    html+='<div class="mgame'+sel+'" onclick="pickGame('+(parseInt(g.id,10)||0)+')">'
      +'<div class="game-emoji">'+ic+'</div>'
      +'<div style="flex:1"><div class="game-title">'+escapeHtml(g.name)+tag+'</div>'
      +'<div class="game-pkgname">'+escapeHtml(pkg)+'</div>'
      +'<div class="game-roottype">'+escapeHtml(g.root_type)+'</div></div>'
      +'<div class="chev">&#x203A;</div></div>';
  });
  document.getElementById('gameList').innerHTML=html||'<div class="loading">'+T.dangTaiGame+'</div>';
  initMotion();
}
function pickGame(gid){
  gCache.forEach(function(g){ if(g.id==gid) selGame=g; });
  if(!selGame)return;
  selPkg=null;
  closeModal('gameModal');
  if(selGame.icon_url){ var iu=safeUrl(selGame.icon_url); document.getElementById('gIcon').innerHTML=iu?'<img src="'+escapeHtml(iu)+'" alt="">':''; }
  else { document.getElementById('gIcon').textContent=ICONS[selGame.package_name]||'\uD83C\uDFAE'; }
  document.getElementById('gName').textContent=selGame.name;
  document.getElementById('gPkg').textContent=selGame.package_name;
  document.getElementById('gameBtnEl').classList.add('chosen');
  updPlayBtn();
  updDlBtn();
  updBuyBtn(); loadPkgs(selGame.id);
}

async function loadPkgs(gid){
  document.getElementById('pkgList').innerHTML='<div class="loading"><div class="spin" style="width:22px;height:22px;border-width:2px"></div></div>';
  var res=await api('packages&game_id='+gid);
  if(!res.success||!res.packages.length){
    document.getElementById('pkgList').innerHTML='<div style="text-align:center;color:var(--text2);padding:16px;font-size:13px">'+T.khongCoGoi+'</div>';
    return;
  }
  pCache=res.packages;
  var rate=parseFloat(res.usdt_vnd_rate||0);
  var disc=getDiscount();
  var html='';
  pCache.forEach(function(p){
    if(p.is_free){
      var sel=(selPkg&&selPkg.id==='free')?' on':'';
      html+='<div class="pkg-row free'+sel+'" onclick="pickPkg(\'free\',this)">'
        +'<div><div class="pkg-days">🎁 '+escapeHtml(p.name)+'</div>'
        +'<div class="pkg-mode">'+T.goiNgay+fmtDur(p.days,p.hours)+' · '+T.vuotLinkNhan+'</div></div>'
        +'<div class="pkg-cost">'+T.mienPhi+'</div></div>';
      return;
    }
    var sel=(selPkg&&selPkg.id==p.id)?' on':'';
    var priceVnd=parseInt(p.price,10)||0;
    var discPrice=discountedPrice(priceVnd);
    var hasDisc=disc>0 && discPrice<priceVnd;
    var usdtTag='';
    if(rate>0 && priceVnd>0){
      var usdt=priceVnd/rate;
      var usdtStr=usdt<0.01?usdt.toFixed(4):usdt.toFixed(3);
      usdtTag=' <span class="pkg-usdt">| \u2248 '+usdtStr+' USDT</span>';
    }
    var priceHtml=hasDisc?'<span style="text-decoration:line-through;opacity:.5;font-size:11px">'+fmtMoney(priceVnd)+'\u0111</span> <span style="color:var(--green2)">'+fmtMoney(discPrice)+'\u0111</span>':fmtMoney(priceVnd)+'\u0111';
    html+='<div class="pkg-row'+sel+'" onclick="pickPkg('+(parseInt(p.id,10)||0)+',this)">'
      +'<div><div class="pkg-days">'+T.goiNgay+fmtDur(p.days,p.hours)+'</div>'
      +'<div class="pkg-mode">'+T.cheDoKey+escapeHtml(p.key_type)+T.keyMode+'</div></div>'
      +'<div class="pkg-cost">'+priceHtml+usdtTag+'</div></div>';
  });
  document.getElementById('pkgList').innerHTML=html;
  initMotion();
}
function pickPkg(pid,el){
  pCache.forEach(function(p){ if(p.id==pid) selPkg=p; });
  if(!selPkg)return;
  document.querySelectorAll('.pkg-row').forEach(function(e){ e.classList.remove('on'); });
  el.classList.add('on');
  selQty=1;
  updateQtyDisplay();
  updBuyBtn();
}
function changeQty(delta){
  if(!selPkg){ toast(T.chuaChonGoi,'error'); return; }
  selQty=Math.max(1,Math.min(10,selQty+delta));
  updateQtyDisplay();
  updBuyBtn();
}
function updateQtyDisplay(){
  var input=document.getElementById('qtyInput');
  var sub=document.getElementById('qtyTotal');
  if(input) input.value=selQty;
  if(sub){
    if(selPkg && !selPkg.is_free){
      var unitPrice=discountedPrice(parseInt(selPkg.price,10)||0);
      var origUnit=parseInt(selPkg.price,10)||0;
      var hasDisc=unitPrice<origUnit;
      if(selQty>1){
        var total=unitPrice*selQty;
        sub.textContent=(T.totalLbl||'Tổng')+': '+fmtMoney(total)+'đ × '+selQty+' key';
      } else {
        var priceStr=hasDisc?'<span style="text-decoration:line-through;opacity:.5">'+fmtMoney(origUnit)+'đ</span> → '+fmtMoney(unitPrice)+'đ':fmtMoney(unitPrice)+'đ / 1 key';
        sub.innerHTML=priceStr;
      }
    } else {
      sub.textContent=T.choosePackage||'Chọn gói';
    }
  }
}
function updPlayBtn(){
  var btn=document.getElementById('playBtn');
  if(!btn)return;
  // Bật nếu có play_url tuỳ chỉnh HOẶC package_name (fallback CH Play)
  if(selGame&&(selGame.play_url||selGame.package_name)){ btn.classList.remove('disabled'); }
  else { btn.classList.add('disabled'); }
}
function openPlayLink(){
  if(!selGame){ toast(T.chonGameTruoc,'error'); return; }
  // Ưu tiên play_url tuỳ chỉnh (mỗi game/bản 1 link riêng), fallback CH Play
  var url=selGame.play_url || (selGame.package_name?PLAY_BASE+encodeURIComponent(selGame.package_name):'');
  if(!url){ toast(T.chonGameTruoc,'error'); return; }
  try{
    if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.openLink){
      window.Telegram.WebApp.openLink(url);
    } else { window.open(url,'_blank'); }
  }catch(e){ window.open(url,'_blank'); }
}
function updDlBtn(){
  var btn=document.getElementById('dlBtn');
  if(!btn)return;
  if(selGame&&selGame.download_url){ btn.classList.remove('disabled'); }
  else { btn.classList.add('disabled'); }
}
function openDownloadLink(){
  if(!selGame){ toast(T.chonGameTruoc,'error'); return; }
  if(!selGame.download_url){ toast(T.errNoDownload,'error'); return; }
  var url=selGame.download_url;
  try{
    if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.openLink){
      window.Telegram.WebApp.openLink(url);
    } else {
      window.open(url,'_blank');
    }
  }catch(e){ window.open(url,'_blank'); }
}

function updBuyBtn(){
  var btn=document.getElementById('buyBtn'),sub=document.getElementById('buySub');
  if(selGame&&selPkg){
    btn.classList.add('go');
    if(selPkg.is_free){ sub.textContent='Get Key Free | '+T.mienPhi; }
    else {
      var unitPrice=discountedPrice(parseInt(selPkg.price,10)||0);
      var totalPrice=unitPrice*selQty;
      var total=selQty>1?fmtMoney(totalPrice)+'\u0111 x'+selQty:'';
      sub.textContent=fmtDur(selPkg.days,selPkg.hours)+' | '+fmtMoney(unitPrice)+'\u0111'+(total?' | '+total:'');
    }
  } else {
    btn.classList.remove('go');
    sub.textContent=T.noPackageSelected;
  }
}

var payCheckTimer=null, payCountdownTimer=null, currentPayOrder='';
var paySecondsLeft=0;
var buying=false;
var paymentOptions=null;     // {mbbank:bool, binance:bool} — cache sau khi gọi lần đầu
var selectedPaymentMethod='mbbank';
function getDiscount(){
  return currentUser && currentUser.discount ? Math.min(100, Math.max(0, parseFloat(currentUser.discount))) : 0;
}
function discountedPrice(price){
  var d = getDiscount();
  return d > 0 ? Math.round(price * (1 - d / 100)) : price;
}
async function fetchPaymentOptions(){
  if(paymentOptions) return paymentOptions;
  try{
    var res=await api('payment_options');
    if(res&&res.success&&res.options) paymentOptions=res.options;
    else paymentOptions={mbbank:true, binance:false, card:false};
  }catch(e){ paymentOptions={mbbank:true, binance:false, card:false}; }
  return paymentOptions;
}
async function doOrder(){
  if(!selGame||!selPkg||buying)return;
  if(selPkg.is_free){ getFreeKey(); return; }
  await fetchPaymentOptions();
  await loadBalance();
  // Luôn hiện picker để user chọn (có thêm option Số dư ví)
  showPaymentMethodPicker();
}
function showPaymentMethodPicker(){
  document.querySelector('#confirmModal .mtitle').textContent=T.pickerTitle||'Chọn phương thức thanh toán';
  var btnBase='display:flex;align-items:center;gap:12px;width:100%;padding:14px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);color:#fff;font-size:14px;text-align:left;cursor:pointer;font-family:inherit';
  var html='<div style="display:flex;flex-direction:column;gap:10px;margin:4px 0">';
  // Option SỐ DƯ ví — luôn hiện nếu có balance
  var price=selPkg?(parseInt(selPkg.price,10)||0)*selQty:0;
  var discPrice=discountedPrice(price);
  var enough=currentBalance>=discPrice;
  var walletTxt=T.pickerBalT||'Số dư ví',walletOk=T.pickerBalSubOk||'Trừ thẳng từ ví · Nhận key ngay',walletNo=T.pickerBalSubNo||'Số dư không đủ, nạp thêm';
  html+='<button onclick="pickPayment(\'balance\')" '+(enough?'':'disabled')+' style="'+btnBase+';border-color:rgba(52,211,153,.4);background:linear-gradient(135deg,rgba(52,211,153,.14),rgba(110,231,183,.06));'+(enough?'':'opacity:.5;cursor:not-allowed')+'"><span style="font-size:24px">💰</span><span><b>'+walletTxt+' ('+fmtMoney(currentBalance)+'đ)</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">'+(enough?walletOk:walletNo)+'</div></span></button>';
  if(paymentOptions.mbbank){
    html+='<button onclick="pickPayment(\'mbbank\')" style="'+btnBase+';border-color:rgba(96,165,250,.4);background:linear-gradient(135deg,rgba(59,130,246,.12),rgba(96,165,250,.06))"><span style="font-size:24px">🏦</span><span><b>'+(T.pickerMbbankT||'MBBank')+'</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">'+(T.pickerMbbankSub||'Chuyển khoản VND (1-2 phút)')+'</div></span></button>';
  }
  if(paymentOptions.binance){
    html+='<button onclick="pickPayment(\'binance\')" style="'+btnBase+';border-color:rgba(240,185,11,.4);background:linear-gradient(135deg,rgba(240,185,11,.14),rgba(243,186,47,.06))"><span style="font-size:24px">🪙</span><span><b>'+(T.pickerBnbT||'Binance USDT')+'</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">'+(T.pickerBnbSub||'USDT TRC20 (1-3 phút)')+'</div></span></button>';
  }
  if(paymentOptions.card){
    html+='<button onclick="pickPayment(\'card\')" style="'+btnBase+';border-color:rgba(168,85,247,.4);background:linear-gradient(135deg,rgba(168,85,247,.14),rgba(192,132,252,.06))"><span style="font-size:24px">💳</span><span><b>'+(T.pickerCardT||'Thẻ cào')+'</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">'+(T.pickerCardSub||'Viettel/Mobifone/Vinaphone (qua ví)')+'</div></span></button>';
  }
  html+='</div>';
  document.getElementById('confirmContent').innerHTML=html;
  document.querySelector('#confirmModal .confirm-actions .confirm-btn.ok').style.display='none';
  document.getElementById('confirmModal').classList.add('show');
}
function pickPayment(method){
  selectedPaymentMethod=method;
  document.querySelector('#confirmModal .confirm-actions .confirm-btn.ok').style.display='';
  if(method==='balance'){
    closeModal('confirmModal');
    buyWithBalance();
    return;
  }
  if(method==='card'){
    closeModal('confirmModal');
    openCardForOrder();
    return;
  }
  showOrderConfirm();
}

// Card-in-order: chọn Card khi mua key → hiện form thẻ kèm số dư + giá key.
// Submit → nạp vào ví; user về picker tự bấm "Mua bằng ví" khi đủ tiền.
async function openCardForOrder(){
  document.getElementById('topupTitle').textContent='💳 Nạp thẻ để mua key';
  document.getElementById('topupContent').innerHTML='<div class="loading"><div class="spin"></div>Đang tải...</div>';
  document.getElementById('topupModal').classList.add('show');
  await fetchTopupOptions();
  var price=selPkg?parseInt(selPkg.price,10)||0:0;
  var bal=0;
  try{
    var b=await api('me_balance','GET');
    if(b&&b.success) bal=parseInt(b.balance,10)||0;
  }catch(e){}
  renderCardOrderUI(bal, price, 'VIETTEL');
}
function renderCardOrderUI(bal, price, telco){
  var mult=cardMultFor(telco);
  var rate=cardRates[telco]||30;
  var need=Math.max(0,price-bal);
  var needCard=Math.ceil(need*mult/1000)*1000;
  var inp='width:100%;padding:13px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:#fff;font-size:14px;margin-top:6px;font-family:inherit';
  var inpMono=inp+';font-family:monospace;letter-spacing:.5px';
  var statusBox=
    '<div style="background:linear-gradient(135deg,rgba(16,185,129,.1),rgba(52,211,153,.05));border:1px solid rgba(52,211,153,.3);border-radius:12px;padding:12px;margin-bottom:14px;display:flex;justify-content:space-between;gap:10px;font-size:12px">'
    +'<div><div style="color:var(--text2);font-weight:700;font-size:11px">SỐ DƯ VÍ</div><div style="font-weight:900;color:#34d399;margin-top:2px">'+fmtMoney(bal)+'đ</div></div>'
    +'<div style="text-align:center"><div style="color:var(--text2);font-weight:700;font-size:11px">GIÁ KEY</div><div style="font-weight:900;margin-top:2px">'+fmtMoney(price)+'đ</div></div>'
    +'<div style="text-align:right"><div style="color:var(--text2);font-weight:700;font-size:11px">'+(need>0?'CẦN NẠP THẺ':'ĐỦ TIỀN')+'</div><div style="font-weight:900;color:'+(need>0?'#fbbf24':'#34d399')+';margin-top:2px">'+(need>0?'~'+fmtMoney(needCard)+'đ':'✓')+'</div></div>'
    +'</div>';
  if(need===0){
    document.getElementById('topupContent').innerHTML=statusBox
      +'<div style="text-align:center;padding:14px;color:var(--text2);font-size:13px;line-height:1.6">Ví của bạn đã đủ để mua key này.<br>Không cần nạp thẻ — bấm nút dưới để trừ ví và nhận key.</div>'
      +'<button class="topup-submit-btn" onclick="buyWithBalance()" style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#10b981,#34d399);color:#fff;font-weight:900;font-size:15px;cursor:pointer;font-family:inherit">💰 Mua bằng ví ('+fmtMoney(price)+'đ)</button>';
    return;
  }
  var faces=[10000,20000,50000,100000,200000,500000,1000000];
  var pickedFace=0;
  for(var i=0;i<faces.length;i++){ if(Math.floor(faces[i]/mult)>=need){ pickedFace=faces[i]; break; } }
  if(!pickedFace) pickedFace=faces[faces.length-1];
  var optsHtml='';
  for(var j=0;j<faces.length;j++){
    var fv=faces[j]; var cr=Math.floor(fv/mult);
    optsHtml+='<option value="'+fv+'"'+(fv===pickedFace?' selected':'')+'>'+fmtMoney(fv)+'đ  →  '+fmtMoney(cr)+'đ ví</option>';
  }
  var telcoOpts='';
  var telcos=[['VIETTEL','Viettel'],['MOBIFONE','Mobifone'],['VINAPHONE','Vinaphone']];
  for(var k=0;k<telcos.length;k++){
    telcoOpts+='<option value="'+telcos[k][0]+'"'+(telcos[k][0]===telco?' selected':'')+'>'+telcos[k][1]+' ('+(cardRates[telcos[k][0]]||30)+'%)</option>';
  }
  var html=statusBox
    +'<div style="background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.3);border-radius:10px;padding:10px 12px;margin-bottom:14px;font-size:11.5px;line-height:1.6;color:#e9d5ff">'
    +'Chiết khấu doithe.vn: '+telco+' '+rate+'%. Đề xuất nạp thẻ <b>'+fmtMoney(needCard)+'đ</b> để đủ mua key.'
    +'</div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Nhà mạng</label>'
    +'<select id="cardTelco" onchange="onCardTelcoChange(\'order\','+bal+','+price+')" style="'+inp+'">'+telcoOpts+'</select></div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Mệnh giá thẻ (→ vào ví)</label>'
    +'<select id="cardFace" style="'+inp+'">'+optsHtml+'</select></div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Số Serial</label>'
    +'<input id="cardSerial" type="text" autocomplete="off" placeholder="Số serial in trên thẻ" style="'+inpMono+'"></div>'
    +'<div style="margin-bottom:14px"><label style="font-size:12px;color:var(--text2);font-weight:700">Mã thẻ (PIN)</label>'
    +'<input id="cardCode" type="text" autocomplete="off" placeholder="Dãy số cào trên thẻ" style="'+inpMono+'"></div>'
    +'<button class="topup-submit-btn" onclick="submitCardForOrder()" style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#a855f7,#c084fc);color:#fff;font-weight:900;font-size:15px;cursor:pointer;font-family:inherit">Gửi thẻ nạp ví</button>';
  document.getElementById('topupContent').innerHTML=html;
  appendCardHistory();
}
function onCardTelcoChange(ctx, bal, price){
  var telco=document.getElementById('cardTelco').value;
  if(ctx==='order') renderCardOrderUI(bal, price, telco);
  else renderCardTopupUI(telco);
}
async function appendCardHistory(){
  try{
    var res=await api('topup_history_card','GET');
    if(!res||!res.success||!res.items||!res.items.length){
      var noBox='<div id="cardHistBox" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)"><div style="font-size:12px;color:var(--text2);font-weight:700;margin-bottom:8px">📋 LỊCH SỬ NẠP THẺ</div><div style="font-size:12px;color:var(--text2);text-align:center;padding:10px">Chưa có lượt nạp nào</div></div>';
      var old=document.getElementById('cardHistBox'); if(old) old.remove();
      var tc=document.getElementById('topupContent'); if(tc) tc.insertAdjacentHTML('beforeend', noBox);
      return;
    }
    var html='<div id="cardHistBox" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)"><div style="font-size:12px;color:var(--text2);font-weight:700;margin-bottom:8px">📋 LỊCH SỬ NẠP THẺ (5 LƯỢT GẦN NHẤT)</div>';
    for(var i=0;i<res.items.length;i++){
      var it=res.items[i];
      var badge='', col='';
      if(it.status==='approved'){ badge='✅ OK'; col='#34d399'; }
      else if(it.status==='rejected'){ badge='❌ Sai'; col='#f87171'; }
      else { badge='⏳ Chờ'; col='#fbbf24'; }
      var when=(it.processed_at||it.created_at||'').substr(5,11).replace('T',' ');
      var faceTxt=fmtMoney(parseInt(it.card_face_value,10)||0)+'đ';
      var credTxt=it.amount_credited?(' → +'+fmtMoney(parseInt(it.amount_credited,10)||0)+'đ ví'):'';
      var msg=(it.provider_message||it.note||'').replace(/</g,'&lt;');
      if(msg.length>80) msg=msg.substr(0,80)+'...';
      html+='<div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:8px 10px;margin-bottom:6px;font-size:11.5px;line-height:1.5">'
        +'<div style="display:flex;justify-content:space-between;gap:6px;align-items:center">'
        +'<div><b style="color:'+col+'">'+badge+'</b> '+(it.card_telco||'')+' '+faceTxt+credTxt+'</div>'
        +'<div style="color:var(--text2);font-size:10px">'+when+'</div>'
        +'</div>'
        +(msg?'<div style="color:var(--text2);margin-top:3px;font-size:10.5px">'+msg+'</div>':'')
        +'</div>';
    }
    html+='</div>';
    var oldBox=document.getElementById('cardHistBox'); if(oldBox) oldBox.remove();
    var c=document.getElementById('topupContent'); if(c) c.insertAdjacentHTML('beforeend', html);
  }catch(e){}
}
async function submitCardForOrder(){
  // Tái sử dụng logic submitCardTopup, sau khi xong vẫn ở modal — refresh status.
  var telco=document.getElementById('cardTelco').value;
  var face=parseInt(document.getElementById('cardFace').value,10);
  var serial=document.getElementById('cardSerial').value.trim();
  var code=document.getElementById('cardCode').value.trim();
  if(!serial||!code){ toast(T.errEnterCard,'error'); return; }
  var btn=document.querySelector('.topup-submit-btn'); var old=btn.innerHTML;
  btn.innerHTML='<div class="spin" style="width:18px;height:18px;border-width:2px;margin:0 auto"></div>';
  btn.disabled=true;
  var res=await api('topup_create','POST',{method:'card', card_telco:telco, card_face_value:face, card_serial:serial, card_code:code});
  btn.disabled=false; btn.innerHTML=old;
  if(!res||!res.success){ toast((res&&res.error)||T.errSendCard,'error'); return; }
  document.getElementById('topupContent').innerHTML=
    '<div style="text-align:center;padding:24px 10px">'
    +'<div style="font-size:48px">⏳</div>'
    +'<div style="font-weight:900;margin-top:12px;font-size:16px">Đã gửi thẻ — chờ doithe.vn xử lý</div>'
    +'<div style="font-size:13px;color:var(--text2);margin-top:8px;line-height:1.6">1-3 phút sẽ có kết quả.<br>Đang theo dõi số dư ví...</div>'
    +'<div style="margin-top:14px"><div class="spin" style="margin:0 auto"></div></div>'
    +'</div>';
  appendCardHistory();
  // Poll balance mỗi 5s, tối đa 60 lần (5 phút).
  var price=selPkg?parseInt(selPkg.price,10)||0:0;
  var startBal=0; try{ var b0=await api('me_balance','GET'); if(b0&&b0.success) startBal=parseInt(b0.balance,10)||0; }catch(e){}
  var tries=0;
  if(window._cardPollTimer) clearInterval(window._cardPollTimer);
  window._cardPollTimer=setInterval(async function(){
    tries++;
    try{
      var b=await api('me_balance','GET');
      if(b&&b.success){
        var cur=parseInt(b.balance,10)||0;
        if(cur>startBal){
          clearInterval(window._cardPollTimer); window._cardPollTimer=null;
          openCardForOrder(); // refresh — sẽ hiện "đủ tiền" hoặc "còn thiếu Yđ"
          return;
        }
      }
      if(tries%3===0) appendCardHistory(); // refresh history mỗi 15s để bắt callback reject
    }catch(e){}
    if(tries>=60){
      clearInterval(window._cardPollTimer); window._cardPollTimer=null;
      appendCardHistory();
    }
  }, 5000);
}
async function buyWithBalance(){
  if(!selGame||!selPkg||buying) return;
  buying=true;
  var btn=document.getElementById('buyBtn');
  if(btn){ btn.innerHTML='<div class="spin" style="width:20px;height:20px;border-width:2px;margin:0"></div>'; btn.classList.remove('go'); }
  var res=await api('buy_with_balance','POST',{game_id:selGame.id,package_id:selPkg.id,quantity:selQty});
  buying=false;
  if(btn){
    var unit=discountedPrice(parseInt(selPkg.price,10)||0);
    btn.innerHTML='<span>'+T.muaNgay+'</span><span class="buy-sub">'+fmtDur(selPkg.days,selPkg.hours)+' | '+fmtMoney(unit*selQty)+'đ'+(selQty>1?' x'+selQty:'')+'</span>';
    btn.classList.add('go');
  }
  try{ closeModal('topupModal'); }catch(e){}
  if(!res||!res.success){ toast((res&&res.error)||T.errWallet,'error'); return; }
  toast(T.buyKeyWalletOk,'success');
  loadKeys('all');
  loadBalance();
}
function showPaymentMethodPickerOld(){
  // legacy, không dùng nữa — giữ làm fallback
}
function showOrderConfirm(){
  document.querySelector('#confirmModal .mtitle').textContent=T.xacNhan;
  document.querySelector('#confirmModal .confirm-btn.cancel').textContent=T.huy;
  document.querySelector('#confirmModal .confirm-btn.ok').textContent=T.dongY;
  var pkgName=(selGame&&selGame.package_name)||'';
  var level=(selPkg&&selPkg.key_type)||'';
  var unitPrice=discountedPrice(parseInt(selPkg.price,10)||0);
  var origUnit=parseInt(selPkg.price,10)||0;
  var hasDisc=unitPrice<origUnit;
  var totalPrice=fmtMoney(unitPrice*selQty);
  var discNote=hasDisc?'<div style="font-size:11px;color:var(--green2);margin-top:4px">Giảm '+getDiscount()+'% · <span style="text-decoration:line-through;opacity:.5">'+fmtMoney(origUnit)+'đ</span> → <b>'+fmtMoney(unitPrice)+'đ</b>/key</div>':'';
  var qtyTag=selQty>1?'<div style="margin-top:8px;font-size:13px;color:var(--orange2)">Số lượng: <b>'+selQty+'</b> key | Tổng: <b>'+totalPrice+'đ</b></div>':'';
  var payTag = selectedPaymentMethod==='binance'
    ? '<div style="margin-top:8px;font-size:12px;color:#fbbf24">'+T.payViaBnb+'</div>'
    : '<div style="margin-top:8px;font-size:12px;color:#67e8f9">'+T.payViaMbb+'</div>';
  document.getElementById('confirmContent').innerHTML=T.xacNhanMua+' <b>"'+escapeHtml(pkgName)+'"</b> '+T.capDo+' <b>"'+escapeHtml(level)+'"</b>, '+T.keyMotGame + discNote + qtyTag + payTag;
  document.querySelector('#confirmModal .confirm-btn.ok').onclick=confirmCreateOrder;
  document.getElementById('confirmModal').classList.add('show');
}
function cancelOrderConfirm(){
  // Restore OK button visibility (có thể bị ẩn bởi picker)
  document.querySelector('#confirmModal .confirm-actions .confirm-btn.ok').style.display='';
  closeModal('confirmModal');
}
async function confirmCreateOrder(){
  if(!selGame||!selPkg||buying)return;
  closeModal('confirmModal');
  buying=true;
  var btn=document.getElementById('buyBtn');
  btn.innerHTML='<div class="spin" style="width:20px;height:20px;border-width:2px;margin:0"></div>';
  btn.classList.remove('go');
  var res=await api('create_order','POST',{game_id:selGame.id,package_id:selPkg.id,payment_method:selectedPaymentMethod,quantity:selQty});
  buying=false;
  btn.classList.add('go');
  var restUnit=discountedPrice(parseInt(selPkg.price,10)||0);
  var restTotal=fmtMoney(restUnit*selQty);
  btn.innerHTML='<span>'+T.muaNgay+'</span><span class="buy-sub">'+fmtDur(selPkg.days,selPkg.hours)+' | '+restTotal+'đ'+(selQty>1?' x'+selQty:'')+'</span>';
  if(res.success) showPay(res);
  else { toast(res.error||T.loiTaoDon,'error'); if(res.order_code){ await loadPendingPayments(); setTimeout(function(){resumePay(0);},350); } }
}



async function getFreeKey(){
  var btn=document.getElementById('freeBtn')||document.getElementById('buyBtn');
  var old=btn.innerHTML; btn.innerHTML='<div class="spin" style="width:18px;height:18px;border-width:2px;margin:0"></div>';
  var res=await api('get_free_link','POST',{game_id:selGame?selGame.id:'',package_id:selPkg?selPkg.free_key_id:''});
  btn.innerHTML=old;
  if(res.success&&res.url){
    toast(T.dangLayLink,'success');
    setTimeout(function(){
      try{
        if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.openLink){
          window.Telegram.WebApp.openLink(res.url);
        } else {
          window.location.href=res.url;
        }
      }catch(e){ window.location.href=res.url; }
    },120);
  }
  else toast(res.error||T.freeHet,'error');
}

// ===================== WALLET / TOP-UP =====================
var topupOptions=null;
var cardRates={VIETTEL:28, MOBIFONE:30, VINAPHONE:30};
function cardMultFor(telco){ var r=cardRates[telco]||30; if(r<0)r=0; if(r>=95)r=95; return 1.0/(1-r/100); }
var currentBalance=0;
async function loadBalance(){
  try{
    var res=await api('me_balance','GET');
    if(res&&res.success){
      currentBalance=parseInt(res.balance||0,10)||0;
      var fmt=fmtMoney(currentBalance)+'đ';
      var pfBal=document.getElementById('pfBalance');
      if(pfBal) pfBal.textContent=fmt;
      var topBal=document.getElementById('topBalance');
      if(topBal) topBal.textContent=fmt;
      var wc=document.getElementById('walletCard');
      if(wc) wc.style.display='';
    } else if(res&&res.error){
      currentBalance=0;
      var wc=document.getElementById('walletCard');
      if(wc) wc.style.display='none';
    }
  }catch(e){}
}
async function fetchTopupOptions(){
  if(topupOptions) return topupOptions;
  try{
    var res=await api('topup_options','GET');
    topupOptions=(res&&res.success&&res.options)?res.options:{mbbank:false,binance:false,card:false};
    if(res&&res.card_rates) cardRates=res.card_rates;
  }catch(e){ topupOptions={mbbank:false,binance:false,card:false}; }
  return topupOptions;
}
async function openTopupModal(){
  document.getElementById('topupTitle').textContent='💳 Nạp tiền vào ví';
  document.getElementById('topupContent').innerHTML='<div class="loading"><div class="spin"></div>Đang tải...</div>';
  document.getElementById('topupModal').classList.add('show');
  var o=await fetchTopupOptions();
  var html='';
  var any=false;
  var btnBase='display:flex;align-items:center;gap:12px;width:100%;padding:14px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);color:#fff;font-size:14px;text-align:left;cursor:pointer;font-family:inherit';
  if(o.mbbank){ any=true; html+='<button onclick="pickTopupMethod(\'mbbank\')" style="'+btnBase+';border-color:rgba(96,165,250,.4);background:linear-gradient(135deg,rgba(59,130,246,.12),rgba(96,165,250,.06))"><span style="font-size:24px">🏦</span><span><b>Nạp qua MBBank</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">Chuyển khoản ngân hàng (1-2 phút)</div></span></button>'; }
  if(o.binance){ any=true; html+='<button onclick="pickTopupMethod(\'binance\')" style="'+btnBase+';border-color:rgba(240,185,11,.4);background:linear-gradient(135deg,rgba(240,185,11,.14),rgba(243,186,47,.06))"><span style="font-size:24px">🪙</span><span><b>Nạp qua Binance USDT</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">USDT TRC20 (1-3 phút)</div></span></button>'; }
  if(o.card){ any=true; html+='<button onclick="pickTopupMethod(\'card\')" style="'+btnBase+';border-color:rgba(168,85,247,.4);background:linear-gradient(135deg,rgba(168,85,247,.14),rgba(192,132,252,.06))"><span style="font-size:24px">💳</span><span><b>Nạp bằng thẻ cào</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">Viettel/Mobifone/Vinaphone</div></span></button>'; }
  if(!any){ html='<div style="text-align:center;color:var(--text2);padding:30px 10px">Hiện chưa mở chức năng nạp ví. Liên hệ admin để bật.</div>'; }
  else { html='<div style="display:flex;flex-direction:column;gap:10px">'+html+'</div>'; }
  document.getElementById('topupContent').innerHTML=html;
}
function pickTopupMethod(m){
  if(m==='card') showCardForm();
  else showAmountForm(m);
}
var topupAmtPick=null;
function setQuickAmt(v){
  topupAmtPick=v;
  var el=document.getElementById('topupAmount'); if(el) el.value=v;
  var btns=document.querySelectorAll('.quick-amt');
  for(var i=0;i<btns.length;i++){ btns[i].style.background=(parseInt(btns[i].dataset.v,10)===v)?'linear-gradient(135deg,#3b82f6,#60a5fa)':'var(--bg3)'; }
}
function showAmountForm(m){
  topupAmtPick=null;
  var label=(m==='binance')?'Số tiền VND (tự convert sang USDT)':'Số tiền cần nạp (VND)';
  var qBtn='display:inline-flex;align-items:center;justify-content:center;padding:10px 0;border-radius:10px;border:1px solid var(--border);background:var(--bg3);color:#fff;font-weight:800;font-size:13px;cursor:pointer;font-family:inherit;flex:1;min-width:60px';
  var html=
    '<div style="margin-bottom:12px">'
    +'<label style="font-size:12px;color:var(--text2);font-weight:700;display:block;margin-bottom:6px">'+escapeHtml(label)+'</label>'
    +'<input id="topupAmount" type="number" min="10000" step="1000" placeholder="VD: 50000" inputmode="numeric" '
    +'style="width:100%;padding:14px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:#fff;font-size:18px;font-weight:800;font-family:inherit"></div>'
    +'<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px">'
    +'<button class="quick-amt" data-v="20000" onclick="setQuickAmt(20000)" style="'+qBtn+'">20k</button>'
    +'<button class="quick-amt" data-v="50000" onclick="setQuickAmt(50000)" style="'+qBtn+'">50k</button>'
    +'<button class="quick-amt" data-v="100000" onclick="setQuickAmt(100000)" style="'+qBtn+'">100k</button>'
    +'<button class="quick-amt" data-v="200000" onclick="setQuickAmt(200000)" style="'+qBtn+'">200k</button>'
    +'<button class="quick-amt" data-v="500000" onclick="setQuickAmt(500000)" style="'+qBtn+'">500k</button>'
    +'</div>'
    +'<button class="topup-submit-btn" onclick="submitTopupAmount(\''+m+'\')" style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#10b981,#34d399);color:#fff;font-weight:900;font-size:15px;cursor:pointer;font-family:inherit">Tiếp tục</button>'
    +'<button onclick="openTopupModal()" style="width:100%;padding:10px;margin-top:8px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text2);font-size:13px;cursor:pointer;font-family:inherit">← Chọn lại phương thức</button>';
  document.getElementById('topupContent').innerHTML=html;
}
function showCardForm(){
  renderCardTopupUI('VIETTEL');
}
function renderCardTopupUI(telco){
  var mult=cardMultFor(telco);
  var rate=cardRates[telco]||30;
  var faces=[10000,20000,50000,100000,200000,500000,1000000];
  var optsHtml='';
  for(var j=0;j<faces.length;j++){
    var fv=faces[j]; var cr=Math.floor(fv/mult);
    optsHtml+='<option value="'+fv+'"'+(fv===50000?' selected':'')+'>'+fmtMoney(fv)+'đ  →  '+fmtMoney(cr)+'đ ví</option>';
  }
  var telcoOpts='';
  var telcos=[['VIETTEL','Viettel'],['MOBIFONE','Mobifone'],['VINAPHONE','Vinaphone']];
  for(var k=0;k<telcos.length;k++){
    telcoOpts+='<option value="'+telcos[k][0]+'"'+(telcos[k][0]===telco?' selected':'')+'>'+telcos[k][1]+' ('+(cardRates[telcos[k][0]]||30)+'%)</option>';
  }
  var inp='width:100%;padding:13px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:#fff;font-size:14px;margin-top:6px;font-family:inherit';
  var inpMono=inp+';font-family:monospace;letter-spacing:.5px';
  var html=
    '<div style="background:rgba(168,85,247,.1);border:1px solid rgba(168,85,247,.3);border-radius:10px;padding:10px 12px;margin-bottom:14px;font-size:12px;line-height:1.6;color:#e9d5ff">'
    +'⚠️ Chiết khấu doithe.vn: '+telco+' '+rate+'%. Tiền vào ví hiện đúng cạnh mỗi mệnh giá.'
    +'</div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Nhà mạng</label>'
    +'<select id="cardTelco" onchange="onCardTelcoChange(\'topup\')" style="'+inp+'">'+telcoOpts+'</select></div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Mệnh giá thẻ (→ vào ví)</label>'
    +'<select id="cardFace" style="'+inp+'">'+optsHtml+'</select></div>'
    +'<div style="margin-bottom:10px"><label style="font-size:12px;color:var(--text2);font-weight:700">Số Serial</label>'
    +'<input id="cardSerial" type="text" autocomplete="off" placeholder="Số serial in trên thẻ" style="'+inpMono+'"></div>'
    +'<div style="margin-bottom:14px"><label style="font-size:12px;color:var(--text2);font-weight:700">Mã thẻ (PIN)</label>'
    +'<input id="cardCode" type="text" autocomplete="off" placeholder="Dãy số cào trên thẻ" style="'+inpMono+'"></div>'
    +'<button class="topup-submit-btn" onclick="submitCardTopup()" style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#a855f7,#c084fc);color:#fff;font-weight:900;font-size:15px;cursor:pointer;font-family:inherit">Gửi thẻ</button>'
    +'<button onclick="openTopupModal()" style="width:100%;padding:10px;margin-top:8px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text2);font-size:13px;cursor:pointer;font-family:inherit">← Chọn lại phương thức</button>';
  document.getElementById('topupContent').innerHTML=html;
  appendCardHistory();
}
async function submitTopupAmount(m){
  var amount=parseInt(document.getElementById('topupAmount').value,10)||0;
  if(amount<10000){ toast(T.errMinTopup,'error'); return; }
  if(amount>50000000){ toast(T.errMaxTopup,'error'); return; }
  var btn=document.querySelector('.topup-submit-btn'); var old=btn.innerHTML;
  btn.innerHTML='<div class="spin" style="width:18px;height:18px;border-width:2px;margin:0 auto"></div>';
  btn.disabled=true;
  var res=await api('topup_create','POST',{method:m, amount:amount});
  btn.disabled=false; btn.innerHTML=old;
  if(!res||!res.success){ toast((res&&res.error)||T.errCreateTopup,'error'); return; }
  showTopupPayInfo(res);
}
async function submitCardTopup(){
  var telco=document.getElementById('cardTelco').value;
  var face=parseInt(document.getElementById('cardFace').value,10);
  var serial=document.getElementById('cardSerial').value.trim();
  var code=document.getElementById('cardCode').value.trim();
  if(!serial||!code){ toast(T.errEnterCard,'error'); return; }
  var btn=document.querySelector('.topup-submit-btn'); var old=btn.innerHTML;
  btn.innerHTML='<div class="spin" style="width:18px;height:18px;border-width:2px;margin:0 auto"></div>';
  btn.disabled=true;
  var res=await api('topup_create','POST',{method:'card', card_telco:telco, card_face_value:face, card_serial:serial, card_code:code});
  btn.disabled=false; btn.innerHTML=old;
  if(!res||!res.success){ toast((res&&res.error)||T.errSendCard,'error'); return; }
  document.getElementById('topupContent').innerHTML=
    '<div style="text-align:center;padding:24px 10px">'
    +'<div style="font-size:48px">⏳</div>'
    +'<div style="font-weight:900;margin-top:12px;font-size:16px">Đã gửi thẻ lên doithe.vn</div>'
    +'<div style="font-size:13px;color:var(--text2);margin-top:8px;line-height:1.6">Hệ thống đang xử lý (1-3 phút). Số dư ví sẽ tự cộng khi xác thực thành công.<br><br>Nếu thẻ sai/đã dùng, xem trong lịch sử bên dưới.</div>'
    +'</div>';
  appendCardHistory();
  setTimeout(function(){ loadBalance(); appendCardHistory(); }, 3000);
}
function showTopupPayInfo(d){
  var html='';
  if(d.method==='mbbank'){
    var amount=d.amount_requested||d.amount||0;
    html='<div class="pay-amount">'+fmtMoney(amount)+'đ</div>'
      +(d.vietqr_url?'<div class="vietqr-box"><img class="vietqr-img" src="'+escapeHtml(d.vietqr_url)+'" alt="QR"></div>':'')
      +'<div class="pay-row"><span class="pay-lbl">Ngân hàng</span><span class="pay-val"><b>'+escapeHtml(d.bank_name||'')+'</b></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">Số TK</span><span class="pay-val"><b>'+escapeHtml(d.bank_account||'')+'</b> <button class="cpbtn" onclick="copyText('+jsAttr(d.bank_account||'')+',\'Đã copy\')">📋</button></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">Chủ TK</span><span class="pay-val">'+escapeHtml(d.bank_owner||'')+'</span></div>'
      +'<div class="pay-row"><span class="pay-lbl">Nội dung CK</span><span class="pay-val"><b style="color:#fbbf24">'+escapeHtml(d.unique_code||'')+'</b> <button class="cpbtn" onclick="copyText('+jsAttr(d.unique_code||'')+',\'Đã copy\')">📋</button></span></div>'
      +'<div class="pay-note" style="color:#fca5a5;font-weight:600">⚠️ Bắt buộc ghi đúng nội dung "'+escapeHtml(d.unique_code||'')+'" — sai = không tự cộng tiền</div>'
      +'<button class="pay-refresh-btn" onclick="closeModal(\'topupModal\');loadBalance();">Đã chuyển khoản</button>';
  } else if(d.method==='binance'){
    var amtUsdt=String(d.crypto_amount||'');
    html='<div class="pay-amount">'+escapeHtml(amtUsdt)+' USDT</div>'
      +(d.usdt_vnd_rate?'<div class="pay-small-note">1 USDT ≈ '+fmtMoney(d.usdt_vnd_rate)+'đ ('+fmtMoney(d.amount_requested||d.amount||0)+'đ)</div>':'')
      +(d.crypto_qr_url?'<div class="vietqr-box"><img class="vietqr-img" src="'+escapeHtml(d.crypto_qr_url)+'" alt="QR"></div>':'')
      +'<div class="pay-row"><span class="pay-lbl">Mạng</span><span class="pay-val"><b>TRC20 (TRON)</b></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">Địa chỉ</span><span class="pay-val" style="word-break:break-all;font-family:monospace;font-size:12px">'+escapeHtml(d.crypto_address||'')+' <button class="cpbtn" onclick="copyText('+jsAttr(d.crypto_address||'')+',\'Đã copy\')">📋</button></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">Số USDT</span><span class="pay-val"><b>'+escapeHtml(amtUsdt)+'</b> <button class="cpbtn" onclick="copyText('+jsAttr(amtUsdt)+',\'Đã copy\')">📋</button></span></div>'
      +'<div class="pay-note" style="color:#fca5a5;font-weight:600">⚠️ Gửi đúng số '+escapeHtml(amtUsdt)+' USDT mạng TRC20 — sai mạng MẤT TIỀN</div>'
      +'<button class="pay-refresh-btn" onclick="closeModal(\'topupModal\');loadBalance();">Đã chuyển</button>';
  }
  document.getElementById('topupContent').innerHTML=html;
}
async function openBalanceHistory(){
  document.getElementById('topupTitle').textContent='📜 Lịch sử ví';
  document.getElementById('topupContent').innerHTML='<div class="loading"><div class="spin"></div>Đang tải...</div>';
  document.getElementById('topupModal').classList.add('show');
  try{
    var res=await api('balance_history','GET');
    if(!res||!res.success||!res.items||!res.items.length){
      document.getElementById('topupContent').innerHTML='<div style="text-align:center;color:var(--text2);padding:30px 10px">Chưa có giao dịch nào.</div>';
      return;
    }
    var html='<div style="display:flex;flex-direction:column;gap:8px">';
    res.items.forEach(function(it){
      var amt=parseFloat(it.amount);
      var isCredit=amt>=0;
      var color=isCredit?'#34d399':'#fca5a5';
      var sign=isCredit?'+':'';
      var reasonLabel={topup:'Nạp tiền',purchase:'Mua key',refund:'Hoàn tiền',admin_adjust:'Admin điều chỉnh'}[it.reason]||it.reason;
      html+='<div style="padding:10px 12px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:10px">'
        +'<div style="flex:1;min-width:0"><div style="font-weight:700;font-size:13px">'+escapeHtml(reasonLabel)+'</div>'
        +'<div style="font-size:11px;color:var(--text2);margin-top:2px">'+escapeHtml(it.created_at||'')+(it.note?' · '+escapeHtml(it.note):'')+'</div></div>'
        +'<div style="text-align:right"><div style="font-weight:900;color:'+color+'">'+sign+fmtMoney(Math.abs(amt))+'đ</div>'
        +'<div style="font-size:10px;color:var(--text2)">Còn '+fmtMoney(it.balance_after)+'đ</div></div>'
        +'</div>';
    });
    html+='</div>';
    document.getElementById('topupContent').innerHTML=html;
  }catch(e){
    document.getElementById('topupContent').innerHTML='<div style="text-align:center;color:var(--text2);padding:30px 10px">Lỗi tải lịch sử.</div>';
  }
}
// =========================================================

function parseDateLocal(s){ return s?new Date(String(s).replace(' ','T')):null; }
function secondsLeftFromOrder(d){
  if(d.pay_seconds_left!==undefined) return Math.max(0,parseInt(d.pay_seconds_left,10)||0);
  var exp=parseDateLocal(d.pay_expires_at); if(exp) return Math.max(0,Math.floor((exp-new Date())/1000));
  return 900;
}
function showPay(d){
  currentPayOrder=d.order_code||'';
  paySecondsLeft=secondsLeftFromOrder(d);
  if(paySecondsLeft<=0){ toast(T.hetGioTT||'Hết thời gian chờ','error'); loadPendingPayments(); return; }
  var isBinance=(d.payment_method==='binance');
  var html='';
  if(isBinance){
    var usdtAmount=(d.crypto_amount!==undefined&&d.crypto_amount!==null)?String(d.crypto_amount):'';
    var cryptoAddr=d.crypto_address||'';
    var rateLine=(d.usdt_vnd_rate)?('<div class="pay-small-note">1 USDT \u2248 '+fmtMoney(d.usdt_vnd_rate)+'\u0111 ('+fmtMoney(d.amount)+'\u0111)</div>'):'';
    var qrUrl=safeUrl(d.crypto_qr_url);
    var qr=qrUrl?'<div class="vietqr-box"><img class="vietqr-img" src="'+escapeHtml(qrUrl)+'" alt="USDT TRC20 QR"></div>':'';
    html=
      '<div class="pay-amount">'+escapeHtml(usdtAmount)+' USDT</div>'
      +rateLine
      +qr
      +'<div class="pay-timer" id="payTimer">15:00</div>'
      +'<div class="pay-small-note">'+(T.giuManHinh||'Không thoát Mini App trong lúc thanh toán.')+'</div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.cryptoNet+'</span><span class="pay-val"><b>TRC20 (TRON)</b></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.cryptoAddr+'</span><span class="pay-val" style="word-break:break-all;font-family:monospace;font-size:12px">'+escapeHtml(cryptoAddr)
      +' <button class="cpbtn" onclick="copyText('+jsAttr(cryptoAddr)+',T.copyTK||\'Đã copy\')">\uD83D\uDCCB</button></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.cryptoAmt+'</span><span class="pay-val"><b>'+escapeHtml(usdtAmount)+'</b>'
      +' <button class="cpbtn" onclick="copyText('+jsAttr(usdtAmount)+',T.copyTK||\'Đã copy\')">\uD83D\uDCCB</button></span></div>'
      +'<div class="pay-note" style="color:#d32f2f;font-weight:600">'+T.cryptoWarn+'</div>'
      +'<div class="pay-note">'+T.cryptoExact1+escapeHtml(usdtAmount)+' USDT'+T.cryptoExact2+'</div>'
      +'<button class="pay-refresh-btn" onclick="donePay()">'+T.daCK+'</button>';
  } else {
    var qrUrl=safeUrl(d.vietqr_url);
    var qr=qrUrl?'<div class="vietqr-box"><img class="vietqr-img" src="'+escapeHtml(qrUrl)+'" alt="VietQR"></div>':'';
    html=
      '<div class="pay-amount">'+fmtMoney(d.amount)+'\u0111</div>'
      +qr
      +'<div class="pay-timer" id="payTimer">05:00</div>'
      +'<div class="pay-small-note">'+(T.giuManHinh||'Không thoát Mini App trong lúc thanh toán.')+'</div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.nganHang+'</span><span class="pay-val">'+escapeHtml(d.bank_name)+'</span></div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.soTK+'</span><span class="pay-val">'+escapeHtml(d.bank_account)
      +' <button class="cpbtn" onclick="copyText('+jsAttr(d.bank_account)+',T.copyTK)">\uD83D\uDCCB</button></span></div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.bankOwnerL+'</span><span class="pay-val">'+escapeHtml(d.bank_owner||'')+'</span></div>'
      +'<div class="pay-row"><span class="pay-lbl">'+T.noiDungCK+'</span><span class="pay-val"><b>'+escapeHtml(d.order_code)
      +'</b> <button class="cpbtn" onclick="copyText('+jsAttr(d.order_code)+',T.copyDon)">\uD83D\uDCCB</button></span></div>'
      +'<div class="pay-note">'+T.luuY+'</div>'
      +'<button class="pay-refresh-btn" onclick="donePay()">'+T.daCK+'</button>';
  }
  document.getElementById('payContent').innerHTML=html;
  document.getElementById('payModal').classList.add('show');
  startPayAutoCheck();
}
function startPayAutoCheck(){
  stopPayAutoCheck();
  updatePayTimer();
  payCountdownTimer=setInterval(function(){
    paySecondsLeft--;
    updatePayTimer();
    if(paySecondsLeft<=0){
      stopPayAutoCheck();
      toast(T.hetGioTT||'Hết thời gian chờ','error');
      loadKeys('all');
      loadPendingPayments();
    }
  },1000);
  payCheckTimer=setInterval(checkPayStatus,5000);
  setTimeout(checkPayStatus,1200);
}
function stopPayAutoCheck(){
  if(payCheckTimer){clearInterval(payCheckTimer);payCheckTimer=null;}
  if(payCountdownTimer){clearInterval(payCountdownTimer);payCountdownTimer=null;}
}
function updatePayTimer(){
  var el=document.getElementById('payTimer'); if(!el)return;
  var m=Math.floor(Math.max(0,paySecondsLeft)/60), ss=Math.max(0,paySecondsLeft)%60;
  el.textContent=String(m).padStart(2,'0')+':'+String(ss).padStart(2,'0');
}
async function checkPayStatus(){
  if(!currentPayOrder)return;
  var res=await api('order_status&order_code='+encodeURIComponent(currentPayOrder));
  if(res.success&&res.order&&res.order.status==='approved'){
    stopPayAutoCheck();
    closeModal('payModal');
    toast(T.daDuyetAuto||'Thanh toán đã xác nhận','success');
    if(res.order&&res.order.order_type==='account'){
      await loadMyAccs();
      switchTab('buyacc');
    } else {
      await loadKeys('all');
    }
  }
}
function donePay(){
  toast(T.choAdmin,'success');
  checkPayStatus();
  loadKeys('all');
  loadPendingPayments();
}

async function loadPendingPayments(){
  var res=await api('pending_orders');
  pendingPayOrders=(res.success&&res.orders)?res.orders:[];
  renderPendingPayments();
}
function renderPendingPayments(){
  var old=document.getElementById('pendingPayBox'); if(old) old.remove();
  if(!pendingPayOrders.length)return;
  var o=pendingPayOrders[0];
  var left=secondsLeftFromOrder(o);
  var box=document.createElement('div'); box.id='pendingPayBox'; box.className='pending-pay-box';
  if(left<=0)return;
  var mm=String(Math.floor(left/60)).padStart(2,'0'), ss=String(left%60).padStart(2,'0');
  box.innerHTML='<div class="pending-pay-title">⚠️ '+T.pendingPayTitle+'</div><div class="pending-pay-sub">'+T.pendingPaySub+'<br><b>'+escapeHtml(o.order_code)+'</b> · '+fmtMoney(o.amount)+'đ · '+escapeHtml(o.pkg_name||'')+' · còn '+mm+':'+ss+'</div><button class="pending-pay-btn" onclick="resumePay(0)">💳 '+T.resumePay+'</button>';
  var keyHead=document.querySelector('.key-head');
  if(keyHead&&keyHead.parentNode) keyHead.parentNode.insertBefore(box,keyHead);
}
function resumePay(i){
  var o=pendingPayOrders[i||0]; if(!o)return;
  showPay({order_code:o.order_code,amount:o.amount,bank_account:o.bank_account,bank_name:o.bank_name,bank_owner:o.bank_owner,transfer_content:o.transfer_content,vietqr_url:o.vietqr_url,payment_method:o.payment_method,crypto_amount:o.crypto_amount,crypto_address:o.crypto_address,crypto_qr_url:o.crypto_qr_url,usdt_vnd_rate:o.usdt_vnd_rate,pay_seconds_left:o.pay_seconds_left,pay_expires_at:o.pay_expires_at});
}

async function loadKeys(filter){
  curFilter=filter;
  var wrap=document.getElementById('keyWrap');
  wrap.innerHTML='<div class="loading"><div class="spin"></div>'+T.dangTai+'</div>';
  var res=await api('my_keys&filter='+filter);
  if(!res.success){
    wrap.innerHTML='<div class="empty-box"><div class="empty-ico">⚠️</div><div class="empty-lbl">'+(res.error||T.taiKeyLoi)+'</div></div>';
    document.getElementById('keyCntLbl').textContent='0 key';
    toast(res.error||T.taiKeyLoi,'error');
    return;
  }
  allKeys=res.keys||[];
  var s=res.stats||{};
  animNum('stTotal',s.total||0);
  animNum('stActive',s.active||0);
  animNum('stExpired',s.expired||0);
  document.getElementById('keyCntLbl').textContent=(s.total||0)+' key';
  renderKeys(allKeys);
}
function animNum(id,val){
  var el=document.getElementById(id);
  if(!el) return;
  // Dùng requestAnimationFrame thay vì setInterval — mượt hơn, không janky
  var start=0, dur=500, startTime=null;
  function step(ts){
    if(!startTime) startTime=ts;
    var prog=Math.min(1,(ts-startTime)/dur);
    var ease=1-Math.pow(1-prog,3); // ease-out cubic
    el.textContent=Math.round(ease*val);
    if(prog<1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}
function filterK(f,el){
  document.querySelectorAll('.ftab').forEach(function(b){b.classList.remove('on');});
  el.classList.add('on'); loadKeys(f);
}
function srchKeys(q){
  if(!q){renderKeys(allKeys);return;}
  renderKeys(allKeys.filter(function(k){return k.key_code.toLowerCase().indexOf(q.toLowerCase())>=0;}));
}

function renderKeys(keys){
  Object.keys(cdTimers).forEach(function(id){clearInterval(cdTimers[id]);}); cdTimers={};
  if(!keys.length){
    document.getElementById('keyWrap').innerHTML='<div class="empty-box"><div class="empty-ico">\uD83D\uDD11</div><div class="empty-lbl">'+T.chuaCoKey+'</div></div>';
    return;
  }
  var html='';
  keys.forEach(function(k,i){
    var bmap={active:'active',expired:'expired',locked:'locked',pending:'pending'};
    var lmap={active:T.active,expired:T.expired,locked:T.locked,pending:T.pending};
    var cls=bmap[k.status]||'pending', lbl=lmap[k.status]||T.pending;
    var typeTag=k.key_type==='VIP'?'<span class="vip-tag">VIP</span>':'<span class="normal-tag">Normal</span>';
    var gameName=escapeHtml(k.game_name||'');
    // Lấy package_name (com.example.game) chứ KHÔNG phải tên gói
    var pkgName=escapeHtml(k.package_name||'');
    html+='<div class="kcard is-'+escapeHtml(k.status)+'" id="kc-'+(parseInt(k.id,10)||0)+'" style="animation-delay:'+i*.05+'s">'
      +'<div class="ktop"><div class="kcode-row">'
      +'<div><div class="kgame" style="font-size:14px;font-weight:800">'+gameName+'</div><div class="kgame" style="font-size:11px;color:var(--text2);font-weight:600">'+pkgName+' '+typeTag+'</div></div>'
      // Badge = mã đơn (thay "Hoạt động")
      +'<div class="kbadge '+cls+'" style="font-family:monospace;font-size:10px">'+escapeHtml(k.order_code||lbl)+'</div></div>'
      +'<div style="padding:8px 16px 0;display:flex;align-items:center;gap:8px">'
      +'<div class="kcode" style="flex:1;font-size:13px">'+escapeHtml(k.key_code)+'</div>'
      +'<button class="ksm" style="flex-shrink:0" onclick="copyText('+jsAttr(k.key_code)+',T.copyKey)">📋 Copy</button>'
      +'</div></div>'
      +'<div class="kgrid">'
      +'<div class="kbox"><div class="kbox-lbl">Thiết bị</div><div class="kbox-val">1/1</div></div>'
      +'<div class="kbox"><div class="kbox-lbl">Thời hạn</div><div class="kbox-val">'+fmtDur(k.days,k.hours)+'</div></div>'
      +'</div>';
    if(k.status==='active'){
      html+='<div class="cdwrap"><div class="cdbar-bg"><div class="cdbar" id="cbar-'+(parseInt(k.id,10)||0)+'" style="width:100%"></div></div></div>';
    }
    if(k.status==='expired'){
      html+='<div class="knote">⚠️ '+T.expiredDeleteNote+(k.delete_at?' · '+T.tuXoaLuc+': '+escapeHtml(fmtDateFull(k.delete_at)):'')+'</div>';
    }
    html+='<div class="kactions">';
    if(k.status==='active') html+='<button class="ksm blue" onclick="doReset('+(parseInt(k.id,10)||0)+')">🔄 '+T.reset+' ('+((k.max_reset||3)-(k.reset_count||0))+')</button>';
    if(k.status==='active') html+='<button class="ksm green" onclick="toast(T.giaHanMsg,\'info\')">⏰ '+T.giaHan+'</button>';
    html+='</div></div>';
  });
  document.getElementById('keyWrap').innerHTML=html;
  initMotion();
  keys.filter(function(k){return k.status==='active';}).forEach(startCd);
}

function startCd(k){
  var exp=new Date(k.expire_at.replace(' ','T'));
  var total=exp-new Date(k.start_at.replace(' ','T'));
  function tick(){
    var now=new Date(),left=exp-now;
    var bar=document.getElementById('cbar-'+k.id);
    var txt=document.getElementById('ctxt-'+k.id);
    var rem=document.getElementById('rem-'+k.id);
    if(left<=0){
      clearInterval(cdTimers[k.id]);
      if(bar){bar.style.width='0%';bar.style.background='var(--red)';}
      if(txt) txt.innerHTML='\u23F0 '+T.hetHanLbl;
      if(rem) rem.innerHTML='<span class="orange">'+T.hetHanLbl+'</span>';
      var badge=document.querySelector('#kc-'+k.id+' .kbadge');
      if(badge){badge.className='kbadge expired';badge.innerHTML=T.expired;}
      return;
    }
    var pct=Math.max(0,(left/total)*100);
    var h=Math.floor(left/3600000),m=Math.floor((left%3600000)/60000),s=Math.floor((left%60000)/1000);
    var d=Math.floor(h/24),hr=h%24;
    var ts=(d>0?d+'d ':'')+pad(hr)+'h '+pad(m)+'p '+pad(s)+'s';
    if(rem) rem.innerHTML='<span class="'+(pct<20?'orange':'green')+'">'+ts+'</span>';
    if(txt) txt.innerHTML=T.conLaiLbl+'<span>'+ts+'</span>';
    if(bar){
      bar.style.width=pct+'%';
      bar.style.background=pct<10?'var(--red)':pct<30?'var(--orange)':'linear-gradient(90deg,var(--green2),var(--cyan))';
    }
  }
  tick(); cdTimers[k.id]=setInterval(tick,1000);
}

function calcRem(k){
  if(k.status!=='active'||!k.expire_at)return'--';
  var ms=new Date(k.expire_at.replace(' ','T'))-new Date();
  if(ms<=0)return'<span class="orange">'+T.hetHanLbl+'</span>';
  var d=Math.floor(ms/86400000),h=Math.floor((ms%86400000)/3600000);
  return'<span class="green">'+(d>0?d+'d ':'')+pad(h)+'h</span>';
}

async function doReset(id){
  if(!confirm(T.confirmReset))return;
  var res=await api('reset_key','POST',{key_id:id});
  if(res.success){toast(T.resetOk,'success');loadKeys(curFilter);}
  else toast(res.error||T.errGeneric,'error');
}
async function doDelete(id){
  if(!confirm(T.confirmXoa))return;
  var res=await api('delete_key','POST',{key_id:id});
  if(res.success){toast(T.xoaOk,'success');loadKeys(curFilter);}
  else toast(res.error||T.errGeneric,'error');
}

function fmtMoney(n){return Number(n).toLocaleString('vi-VN');}
// Format thời hạn gói: days/hours -> "X ngày Yh" | "X ngày" | "Y giờ" | "—"
function fmtDur(d,h){d=parseInt(d,10)||0;h=parseInt(h,10)||0;var hr=(T.gio||(LANG==='en'?' hour(s)':LANG==='es'?' hora(s)':' giờ'));var dy=T.ngay||' ngày';if(d>0&&h>0)return d+dy+' '+h+'h';if(d>0)return d+dy;if(h>0)return h+hr;return '—';}
function pad(n){return String(n).padStart(2,'0');}
function fmtDate(d){if(!d)return'--';var dt=new Date(d.replace(' ','T'));return dt.getDate()+'/'+(dt.getMonth()+1)+'/'+dt.getFullYear();}
function fmtDateFull(d){if(!d)return'--';var dt=new Date(d.replace(' ','T'));return dt.getDate()+'/'+(dt.getMonth()+1)+'/'+dt.getFullYear()+' '+pad(dt.getHours())+':'+pad(dt.getMinutes());}

async function copyText(t,msg){
  try{await navigator.clipboard.writeText(t);toast(msg||T.daCopy,'success');}
  catch(e){toast(T.copyFail,'error');}
}
var _tt=null;
function toast(msg,type){
  var el=document.getElementById('toast');
  el.textContent=msg; el.className='show '+(type||'');
  if(_tt)clearTimeout(_tt);
  _tt=setTimeout(function(){el.className='';},2800);
}
function closeModal(id){document.getElementById(id).classList.remove('show');}
document.querySelectorAll('.moverlay').forEach(function(m){
  m.addEventListener('click',function(e){if(e.target===m)m.classList.remove('show');});
});

// initMotion: event delegation — chỉ attach 1 lần lên document, không attach từng element
(function(){
  var pressing=null;
  var SEL='.game-btn,.pkg-row,.mgame,.ic-btn,.buy-btn.go,.ftab,.ksm,.bank-chip';
  function isTarget(el){
    while(el&&el!==document.body){
      if(el.matches&&el.matches(SEL)) return el;
      el=el.parentElement;
    }
    return null;
  }
  ['touchstart','mousedown'].forEach(function(ev){
    document.addEventListener(ev,function(e){
      var t=isTarget(e.target);
      if(t){ pressing=t; t.classList.add('touching'); }
    },{passive:true});
  });
  ['touchend','touchcancel','mouseup','mouseleave'].forEach(function(ev){
    document.addEventListener(ev,function(){
      if(pressing){ pressing.classList.remove('touching'); pressing=null; }
    },{passive:true});
  });
})();
function initMotion(){ /* no-op: delegation đã handle ở trên */ }

// ===== Bottom Tab Navigation =====
var currentTab = 'buykey';
var freeKeyLoaded = false;

function switchTab(tab){
  currentTab = tab;
  document.querySelectorAll('.tab-content').forEach(function(el){ el.classList.remove('active'); });
  document.querySelectorAll('.nav-tab').forEach(function(el){ el.classList.remove('active'); });
  var tabEl = document.getElementById('tab-'+tab);
  var navEl = document.getElementById('nav-'+tab);
  if(tabEl) tabEl.classList.add('active');
  if(navEl) navEl.classList.add('active');
  // Load data for tab
  if(tab==='history') loadHistory();
  if(tab==='profile') loadProfile();
  if(tab==='freekey' && !freeKeyLoaded) loadFreeKey();
  if(tab==='buyacc'){ loadAccTypes(); loadMyAccs(); }
  // Scroll to top
  var sc = document.querySelector('.scroll-area');
  if(sc) sc.scrollTop = 0;
}

async function loadFreeKey(){
  freeKeyLoaded = true;
  var wrap = document.getElementById('freeKeyWrap');
  wrap.innerHTML = '<div class="loading"><div class="spin"></div>'+escapeHtml(T.freeChecking2||'Đang kiểm tra...')+'</div>';
  try {
    var res = await api('free_key_status','GET',{});
    if(res.claimed){
      wrap.innerHTML = '<div class="free-card">'
        +'<div class="free-icon">✅</div>'
        +'<div class="free-title">'+escapeHtml(T.freeClaimedToday)+'</div>'
        +'<div class="free-claimed">'
        +'<div class="free-claimed-code">'+escapeHtml(res.key_code)+'</div>'
        +'<div style="font-size:11px;color:var(--text2);margin-top:6px">'+escapeHtml(T.freeReceivedAt)+' '+escapeHtml(fmtDateFull(res.claimed_at))+'</div>'
        +'</div>'
        +'<div class="free-timer">'+escapeHtml(T.freeBackTomorrow)+'</div>'
        +'<button class="free-btn" style="margin-top:10px" onclick="copyText('+jsAttr(res.key_code)+','+jsAttr(T.copyKey)+')">'+escapeHtml(T.copy)+' Key</button>'
        +'</div>';
    } else if(res.available > 0){
      var days = parseInt(res.next_days,10) || 0;
      var gameName = res.next_game || 'HCLOU';
      var claimedCnt = parseInt(res.total_claimed_today,10) || 0;
      var badgeTime = days > 0 ? (days + ' Days') : '6 Hours';
      wrap.innerHTML = '<div class="free-card">'
        +'<div class="free-icon"><svg viewBox="0 0 24 24" width="36" height="36" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg></div>'
        +'<div class="free-title">'+escapeHtml(T.freeKeyToday)+'</div>'
        +'<div class="free-sub">'+T.freeSub+'</div>'
        +'<div class="free-badges">'
        +  '<div class="free-badge"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'+escapeHtml(badgeTime)+'</div>'
        +  '<div class="free-badge"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M15 3h6v6M10 14L21 3"/></svg>Full Access</div>'
        +  '<div class="free-badge"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg>1 Device</div>'
        +  '<div class="free-badge"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Secure</div>'
        +'</div>'
        +'<div class="free-select-block">'
        +  '<label class="free-label">Select Game</label>'
        +  '<div class="free-select-wrap"><select class="free-select" id="freeGameSelect"><option>'+escapeHtml(gameName)+'</option></select></div>'
        +'</div>'
        +'<button class="free-btn" id="claimFreeBtn" onclick="claimDailyFree()">'
        +  '<svg class="free-arrow" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>'
        +  '<div class="free-spinner"></div>'
        +  '<span id="freeBtnText">'+escapeHtml(T.freeBtnGetLink)+'</span>'
        +'</button>'
        +'<div class="free-result-box" id="claimLinkArea" style="display:none"></div>'
        +'<div class="free-timer">'+escapeHtml(T.freeResetDaily)+(claimedCnt? ' · <b style="color:#a78bfa">'+claimedCnt+' '+escapeHtml(T.freePeopleSuffix)+'</b>':'')+'</div>'
        +'<div class="free-secure"><svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Protected by advanced security</div>'
        +'</div>';
    } else {
      wrap.innerHTML = '<div class="free-card">'
        +'<div class="free-icon">😔</div>'
        +'<div class="free-title">'+escapeHtml(T.freeNoneToday)+'</div>'
        +'<div class="free-sub">'+escapeHtml(T.freeAdminNotYet)+'</div>'
        +'<div class="free-timer">'+escapeHtml(T.freeNewMorning)+'</div>'
        +'</div>';
    }
  } catch(e){
    wrap.innerHTML = '<div class="free-empty"><div class="empty-ico">⚠️</div><div class="empty-lbl">'+escapeHtml(T.freeCannotLoad)+'</div></div>';
  }
}

async function claimDailyFree(){
  var btn = document.getElementById('claimFreeBtn');
  var txt = document.getElementById('freeBtnText');
  if(btn){ btn.disabled=true; btn.classList.add('loading'); if(txt) txt.textContent=T.freeCreatingLink; }
  try {
    var res = await api('daily_free_key','POST',{});
    if(res.success){
      if(res.already){
        toast(T.freeAlready,'info');
        freeKeyLoaded = false;
        loadFreeKey();
      } else if(res.claim_url){
        var claimUrl = safeUrl(res.claim_url);
        // Auto mở link claim luôn — bỏ step "Open" manual.
        // Telegram WebApp dùng openLink để bung browser ngoài, ngoài WebApp dùng location.href.
        var area = document.getElementById('claimLinkArea');
        if(area){
          area.style.display = 'flex';
          area.innerHTML = '<span class="free-result-key">'+escapeHtml(claimUrl)+'</span>'
            +'<button class="free-copy-btn" onclick="copyText('+jsAttr(claimUrl)+','+jsAttr(T.freeCopiedLink)+')">Copy</button>';
        }
        if(btn){ btn.classList.remove('loading'); btn.disabled=true; if(txt) txt.textContent=T.freeLinkCreated; }
        setTimeout(function(){
          try{
            if(window.Telegram&&window.Telegram.WebApp&&window.Telegram.WebApp.openLink){
              window.Telegram.WebApp.openLink(claimUrl);
            } else {
              window.location.href = claimUrl;
            }
          }catch(e){ window.location.href = claimUrl; }
        },120);
      } else {
        toast(res.message || T.freeClaimOk,'success');
        freeKeyLoaded = false;
        loadFreeKey();
      }
    } else {
      toast(res.error || T.freeErrorGeneric,'error');
      if(btn){ btn.classList.remove('loading'); btn.disabled=false; if(txt) txt.textContent=T.freeBtnGetLink; }
    }
  } catch(e){
    toast(T.toastLoadErr,'error');
    if(btn){ btn.classList.remove('loading'); btn.disabled=false; if(txt) txt.textContent=T.freeBtnGetLink; }
  }
}

var histLoaded = false;
async function loadHistory(){
  if(histLoaded) return;
  var wrap = document.getElementById('histWrap');
  wrap.innerHTML = '<div class="loading"><div class="spin"></div>'+escapeHtml(T.loadingHistory)+'</div>';
  try {
    var html = '';

    // 1. PENDING TOPUP - hiện đầu, có nút "Xem lại QR"
    try {
      var resP = await api('topup_pending','GET',{});
      if(resP && resP.success && resP.items && resP.items.length){
        html += '<div style="padding:0 16px 6px;font-size:11px;font-weight:900;color:var(--orange2);letter-spacing:.04em;text-transform:uppercase">⏳ Nạp đang chờ</div>';
        resP.items.forEach(function(t){
          var label = t.method==='mbbank'?'🏦 MBBank':t.method==='binance'?'🪙 Binance':'💳 Thẻ cào';
          var amt = fmtMoney(t.amount_requested||0)+'đ';
          html += '<div class="hist-order" style="border-left:3px solid #fb923c">'
            +'<div class="hist-header"><span class="hist-code">'+label+'</span>'
            +'<span class="hist-badge pending">⏳ Chờ thanh toán</span></div>'
            +'<div class="hist-detail">'
            +(t.unique_code?'<span><span>Mã CK</span><b style="color:#fbbf24">'+escapeHtml(t.unique_code)+'</b></span>':'')
            +(t.crypto_amount?'<span><span>USDT</span><b>'+escapeHtml(String(t.crypto_amount))+'</b></span>':'')
            +'<span><span>Số tiền</span><b>'+amt+'</b></span>'
            +'<span><span>Thời gian</span><b>'+escapeHtml(fmtDateFull(t.created_at))+'</b></span>'
            +'</div>'
            +'<button class="pay-refresh-btn" style="margin-top:10px;width:100%" onclick=\'resumeTopupPay('+JSON.stringify(t).replace(/\'/g,"&#39;")+')\'>📲 Xem lại QR/thông tin</button>'
            +'</div>';
        });
        html += '<div style="height:8px"></div>';
      }
    }catch(e){}

    // 2. LỊCH SỬ NẠP TIỀN
    try {
      var resT = await api('topup_history_all','GET',{});
      if(resT && resT.success && resT.items && resT.items.length){
        html += '<div style="padding:0 16px 6px;font-size:11px;font-weight:900;color:var(--green2);letter-spacing:.04em;text-transform:uppercase">💵 Lịch sử nạp tiền</div>';
        resT.items.forEach(function(t){
          var badgeCls = t.status==='approved'?'approved':t.status==='rejected'?'rejected':t.status==='expired'?'cancelled':'pending';
          var statusText = t.status==='approved'?'Đã cộng ví':t.status==='rejected'?'Bị từ chối':t.status==='expired'?'Hết hạn':'Đang chờ';
          var label = t.method==='mbbank'?'🏦 MBBank':t.method==='binance'?'🪙 Binance':'💳 Thẻ '+(t.card_telco||'');
          var amt = fmtMoney(t.amount_requested||0)+'đ';
          var credit = t.amount_credited?(' → +'+fmtMoney(t.amount_credited)+'đ ví'):'';
          html += '<div class="hist-order">'
            +'<div class="hist-header"><span class="hist-code">'+label+'</span>'
            +'<span class="hist-badge '+badgeCls+'">'+statusText+'</span></div>'
            +'<div class="hist-detail">'
            +'<span><span>Yêu cầu</span><b>'+amt+credit+'</b></span>'
            +'<span><span>Thời gian</span><b>'+escapeHtml(fmtDate(t.created_at))+'</b></span>'
            +(t.note?'<span><span>Ghi chú</span><b style="font-size:11px">'+escapeHtml(t.note)+'</b></span>':'')
            +'</div></div>';
        });
        html += '<div style="height:8px"></div>';
      }
    }catch(e){}

    // 3. ĐƠN HÀNG
    var res = await api('my_orders','GET',{});
    if(res.orders && res.orders.length){
      html += '<div style="padding:0 16px 6px;font-size:11px;font-weight:900;color:var(--blue2);letter-spacing:.04em;text-transform:uppercase">🛒 Đơn hàng</div>';
      res.orders.forEach(function(o){
        var badgeCls = o.status==='approved'?'approved':o.status==='rejected'?'rejected':o.status==='cancelled'?'cancelled':'pending';
        var statusText = o.status==='approved'?T.statusApproved:o.status==='rejected'?T.statusRejected:o.status==='cancelled'?T.statusCancelled:T.statusPending;
        html += '<div class="hist-order">'
          +'<div class="hist-header"><span class="hist-code">#'+escapeHtml(o.order_code)+'</span>'
          +'<span class="hist-badge '+badgeCls+'">'+escapeHtml(statusText)+'</span></div>'
          +'<div class="hist-detail">'
          +'<span><span>'+escapeHtml(T.histGame)+'</span><b>'+escapeHtml(o.game_name)+'</b></span>'
          +'<span><span>'+escapeHtml(T.histPkg)+'</span><b>'+escapeHtml(o.pkg_name)+' ('+fmtDur(o.days,o.hours)+')</b></span>'
          +'<span><span>'+escapeHtml(T.histCreated)+'</span><b>'+escapeHtml(fmtDate(o.created_at))+'</b></span>'
          +'</div>'
          +'<div class="hist-amount">'+fmtMoney(o.amount)+'₫</div></div>';
      });
    }

    if(!html){
      wrap.innerHTML = '<div class="hist-empty-note" style="padding:40px 20px"><div class="empty-ico">📭</div><div class="empty-lbl">'+escapeHtml(T.histEmpty)+'</div></div>';
    } else {
      wrap.innerHTML = html;
    }
    histLoaded = true;
  } catch(e){
    wrap.innerHTML = '<div class="hist-empty-note">'+escapeHtml(T.histLoadFail)+'</div>';
  }
}

// Mở lại modal nạp với info đã tạo (cho user xem QR/mã CK)
function resumeTopupPay(t){
  document.getElementById('topupTitle').textContent='💳 Tiếp tục nạp tiền';
  document.getElementById('topupModal').classList.add('show');
  showTopupPayInfo({
    method: t.method,
    amount_requested: t.amount_requested,
    unique_code: t.unique_code,
    bank_name: t.bank_name||'',
    bank_account: t.bank_account||'',
    bank_owner: t.bank_owner||'',
    vietqr_url: t.vietqr_url||'',
    crypto_amount: t.crypto_amount,
    crypto_address: t.crypto_address||'',
    crypto_qr_url: t.crypto_qr_url||'',
    usdt_vnd_rate: t.usdt_vnd_rate
  });
}

var profLoaded = false;
async function loadProfile(){
  if(!currentUser) return;
  if(!profLoaded){
    // Set avatar
    var av2 = document.getElementById('avatarEl2');
    var init2 = document.getElementById('avatarInit2');
    var av1 = document.getElementById('avatarEl');
    if(av1 && av2) av2.innerHTML = av1.innerHTML;
    if(init2) init2.textContent = (currentUser.full_name || 'U').charAt(0).toUpperCase();
    document.getElementById('pName2').textContent = currentUser.full_name || 'User';
    document.getElementById('pHandle2').textContent = '@'+(currentUser.telegram_username || 'unknown');
    document.getElementById('pfTgId').textContent = currentUser.telegram_id || '--';
    document.getElementById('pfTgUser').textContent = '@'+(currentUser.telegram_username || '--');
    document.getElementById('pfFullName').textContent = currentUser.full_name || '--';
    document.getElementById('pfJoined').textContent = currentUser.created_at ? fmtDate(currentUser.created_at) : '--';
    setUserRole();
    profLoaded = true;
  }
  loadBalance();
  // Load stats
  try {
    var res = await api('profile_stats','GET',{});
    if(res.success){
      document.getElementById('pfTotalOrders').textContent = res.total_orders || 0;
      document.getElementById('pfApproved').textContent = res.approved_orders || 0;
      document.getElementById('pfPending').textContent = res.pending_orders || 0;
      document.getElementById('pfKeys').textContent = res.active_keys || 0;
    }
  } catch(e){}
}

// =============================================
// ACC SELLING
// =============================================
var selAccGame=null, selAccType=null, accTypes=[], accOrdering=false;

function hideAccSection(){
  var el=document.getElementById('accTypeList');
  if(el) el.innerHTML='<div style="text-align:center;color:var(--text2);padding:16px 0;font-size:13px;font-weight:600">Ch&#x1ECD;n game tr&#x1B0;&#x1EDB;c</div>';
}

// buildAccGameList is defined in openAccGameModal above (line ~245)
function pickAccGame(gid){
  gCache.forEach(function(g){ if(g.id==gid) selAccGame=g; });
  if(!selAccGame)return;
  closeModal('gameModal');
  if(selAccGame.icon_url){ document.getElementById('accGIcon').innerHTML='<img src="'+escapeHtml(safeUrl(selAccGame.icon_url))+'" alt="">'; }
  else { document.getElementById('accGIcon').textContent=ICONS[selAccGame.package_name]||'🎮'; }
  document.getElementById('accGName').textContent=selAccGame.name;
  document.getElementById('accGPkg').textContent=selAccGame.package_name;
  document.getElementById('accGameBtnEl').classList.add('chosen');
  selAccType=null;
  loadAccTypes();
  updAccBuyBtn();
}

async function loadAccTypes(){
  if(!selAccGame) return;
  var el=document.getElementById('accTypeList');
  el.innerHTML='<div class="loading"><div class="spin" style="width:22px;height:22px;border-width:2px"></div></div>';
  try{
    var res=await api('account_types&game_id='+selAccGame.id);
    if(res.success && res.account_types && res.account_types.length){
      accTypes=res.account_types;
      var html='';
      accTypes.forEach(function(t){
        var avail=parseInt(t.stock)||0;
        var stockTag=avail>0?' <span style="color:var(--green2);font-weight:800;font-size:11px">('+avail+' còn)</span>':' <span style="color:var(--red2);font-weight:800;font-size:11px">(Hết)</span>';
        var ap=parseInt(t.price,10)||0;
        var dp=discountedPrice(ap);
        var hasDisc=dp<ap;
        var priceHtml=hasDisc?'<span style="text-decoration:line-through;opacity:.5;font-size:11px">'+fmtMoney(ap)+'đ</span> <span style="color:var(--green2)">'+fmtMoney(dp)+'đ</span>':fmtMoney(ap)+'đ';
        var sel=(selAccType&&selAccType.id==t.id)?' on':'';
        var desc=(t.description&&String(t.description).trim()!=='')?escapeHtml(t.description):'';
        var subLine=desc?(desc+stockTag):('Acc '+escapeHtml(t.name)+stockTag);
        html+='<div class="pkg-row'+sel+'" onclick="pickAccType('+t.id+',this)">'
          +'<div style="min-width:0;flex:1"><div class="pkg-days">'+escapeHtml(t.name)+'</div>'
          +'<div class="pkg-mode" style="font-size:11px;color:var(--text2);line-height:1.4">'+subLine+'</div></div>'
          +'<div class="pkg-cost">'+priceHtml+'</div></div>';
      });
      el.innerHTML=html;
    } else {
      el.innerHTML='<div style="text-align:center;color:var(--text2);padding:16px;font-size:13px">Ch&#x1B0;a c&#xF3; lo&#x1EA1;i acc n&#xE0;o</div>';
    }
  }catch(e){
    el.innerHTML='<div style="text-align:center;color:var(--text2);padding:16px;font-size:13px">L&#x1ED7;i t&#x1EA3;i lo&#x1EA1;i acc</div>';
  }
}
function pickAccType(tid,el){
  accTypes.forEach(function(t){ if(t.id==tid) selAccType=t; });
  if(!selAccType)return;
  document.querySelectorAll('#accTypeList .pkg-row').forEach(function(e){ e.classList.remove('on'); });
  el.classList.add('on');
  updAccBuyBtn();
}
function updAccBuyBtn(){
  var btn=document.getElementById('accBuyBtn');
  var sub=document.getElementById('accBuySub');
  if(selAccGame && selAccType && parseInt(selAccType.stock)>0){
    btn.classList.add('go');
    var ap=parseInt(selAccType.price,10)||0;
    var dp=discountedPrice(ap);
    sub.textContent=selAccType.name+' | '+fmtMoney(dp)+'đ';
  } else {
    btn.classList.remove('go');
    sub.textContent='Chưa chọn loại acc';
  }
}
async function doAccOrder(){
  if(!selAccGame||!selAccType||accOrdering) return;
  var avail=parseInt(selAccType.stock)||0;
  if(avail<1){ toast(T.errOutAcc,'error'); return; }
  await fetchPaymentOptions();
  await loadBalance();
  showAccPaymentPicker();
}
function showAccPaymentPicker(){
  document.querySelector('#confirmModal .mtitle').textContent='💳 Chọn phương thức thanh toán';
  var btnBase='display:flex;align-items:center;gap:12px;width:100%;padding:14px;border-radius:12px;border:1px solid var(--border);background:var(--bg3);color:#fff;font-size:14px;text-align:left;cursor:pointer;font-family:inherit';
  var html='<div style="display:flex;flex-direction:column;gap:10px;margin:4px 0">';
  var price=parseInt(selAccType?selAccType.price:0,10);
  var dp=discountedPrice(price);
  var enough=currentBalance>=dp;
  html+='<button onclick="pickAccPayment(\'balance\')" '+(enough?'':'disabled')+' style="'+btnBase+';border-color:rgba(52,211,153,.4);background:linear-gradient(135deg,rgba(52,211,153,.14),rgba(110,231,183,.06));'+(enough?'':'opacity:.5;cursor:not-allowed')+'"><span style="font-size:24px">💰</span><span><b>Số dư ví ('+fmtMoney(currentBalance)+'đ)</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">'+(enough?'Trừ thẳng từ ví · Nhận acc ngay':'Số dư không đủ')+'</div></span></button>';
  if(paymentOptions.mbbank){
    html+='<button onclick="pickAccPayment(\'mbbank\')" style="'+btnBase+';border-color:rgba(96,165,250,.4);background:linear-gradient(135deg,rgba(59,130,246,.12),rgba(96,165,250,.06))"><span style="font-size:24px">🏦</span><span><b>MBBank</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">Chuyển khoản VietQR · Tự duyệt 1 phút</div></span></button>';
  }
  if(paymentOptions.binance){
    html+='<button onclick="pickAccPayment(\'binance\')" style="'+btnBase+';border-color:rgba(240,185,11,.4);background:linear-gradient(135deg,rgba(240,185,11,.14),rgba(243,186,47,.06))"><span style="font-size:24px">🪙</span><span><b>Binance USDT (TRC20)</b><div style="font-size:11px;opacity:.8;font-weight:500;margin-top:2px">Crypto · Auto detect on-chain</div></span></button>';
  }
  html+='</div>';
  document.getElementById('confirmContent').innerHTML=html;
  document.querySelector('#confirmModal .confirm-actions .confirm-btn.ok').style.display='none';
  document.getElementById('confirmModal').classList.add('show');
}
function pickAccPayment(method){
  selectedPaymentMethod=method;
  document.querySelector('#confirmModal .confirm-actions .confirm-btn.ok').style.display='';
  if(method==='balance'){
    closeModal('confirmModal');
    buyAccWithBalance();
    return;
  }
  showAccConfirm();
}
async function buyAccWithBalance(){
  if(!selAccGame||!selAccType||accOrdering) return;
  accOrdering=true;
  var btn=document.getElementById('accBuyBtn');
  if(btn){ btn.innerHTML='<div class="spin" style="width:20px;height:20px;border-width:2px;margin:0"></div>'; btn.classList.remove('go'); }
  var res=await api('buy_account_with_balance','POST',{game_id:selAccGame.id,account_type_id:selAccType.id});
  accOrdering=false;
  if(btn){
    var _ap=parseInt(selAccType.price,10)||0;
    btn.innerHTML='<span>Mua Acc</span><span class="buy-sub" id="accBuySub">'+selAccType.name+' | '+fmtMoney(discountedPrice(_ap))+'đ</span>';
    btn.classList.add('go');
  }
  if(!res||!res.success){ toast((res&&res.error)||T.errWallet,'error'); return; }
  toast(T.buyAccWalletOk,'success');
  loadAccTypes();
  loadMyAccs();
  loadBalance();
  switchTab('buyacc');
}
function showAccConfirm(){
  document.querySelector('#confirmModal .mtitle').textContent='Xác nhận mua acc';
  document.querySelector('#confirmModal .confirm-btn.cancel').textContent='Huỷ';
  document.querySelector('#confirmModal .confirm-btn.ok').textContent='Đồng ý';
  var accName=selAccType?selAccType.name:'';
  var gameName=selAccGame?selAccGame.name:'';
  var ap=parseInt(selAccType?selAccType.price:0,10);
  var dp=discountedPrice(ap);
  var hasDisc=dp<ap;
  var price=hasDisc?'<span style="text-decoration:line-through;opacity:.5">'+fmtMoney(ap)+'đ</span> → <span style="color:var(--green2)">'+fmtMoney(dp)+'đ</span>':fmtMoney(ap)+'đ';
  var discNote=hasDisc?'<div style="font-size:11px;color:var(--green2)">Giảm '+getDiscount()+'%</div>':'';
  var payLabel=selectedPaymentMethod==='binance'?'🪙 Binance USDT':'🏦 MBBank';
  document.getElementById('confirmContent').innerHTML=
    'Bạn đang mua acc <b>'+escapeHtml(accName)+'</b> của game <b>'+escapeHtml(gameName)+'</b><br>'
    +'Giá: <b>'+price+'</b> · Thanh toán: '+payLabel+discNote;
  document.querySelector('#confirmModal .confirm-btn.ok').onclick=confirmAccOrder;
  document.getElementById('confirmModal').classList.add('show');
}
async function confirmAccOrder(){
  if(!selAccGame||!selAccType||accOrdering)return;
  closeModal('confirmModal');
  accOrdering=true;
  var btn=document.getElementById('accBuyBtn');
  btn.innerHTML='<div class="spin" style="width:20px;height:20px;border-width:2px;margin:0"></div>';
  btn.classList.remove('go');
  var res=await api('create_account_order','POST',{game_id:selAccGame.id,account_type_id:selAccType.id,payment_method:selectedPaymentMethod});
  accOrdering=false;
  // Restore innerHTML trước — spinner đã xoá #accBuySub khỏi DOM, gọi updAccBuyBtn() sẽ null crash
  var _ap=parseInt(selAccType?selAccType.price:0,10);
  btn.innerHTML='<span>Mua Acc</span><span class="buy-sub" id="accBuySub">'
    +(selAccType?selAccType.name+' | '+fmtMoney(discountedPrice(_ap))+'đ':'Chưa chọn loại acc')+'</span>';
  btn.classList.add('go');
  if(res.success){
    showPay(res);
    loadAccTypes();
  } else {
    toast(res.error||T.errCreateAccOrder,'error');
  }
}
// Load purchased accounts for "Mua Acc" tab
async function loadMyAccs(){
  try{
    var res=await api('my_accounts');
    var wrap=document.getElementById('accMyList');
    if(res.success && res.accounts && res.accounts.length){
      var count=res.accounts.length;
      document.getElementById('accCntLbl').textContent=count+' acc';
      var html='';
      res.accounts.forEach(function(acc, i){
        var created = acc.created_at ? fmtDateFull(acc.created_at) : '--';
        html+='<div class="kcard is-active" style="animation-delay:'+i*0.05+'s;margin:0 0 10px">'
          +'<div class="ktop"><div class="kcode-row">'
          +'<div class="kcode" style="color:var(--purple2)">'+escapeHtml(acc.username)+'</div>'
          +'<div class="kbadge active" style="background:linear-gradient(135deg,rgba(168,85,247,.15),rgba(139,92,246,.08));color:var(--purple2);border:1px solid rgba(168,85,247,.3)">ACC</div></div>'
          +'<div class="kgame">'+escapeHtml(acc.game_name)+' <span class="normal-tag">'+escapeHtml(acc.type_name||'')+'</span></div></div>'
          +'<div class="kgrid">'
          +'<div class="kbox"><div class="kbox-lbl">Tài khoản</div><div class="kbox-val" style="font-family:monospace;font-size:13px">'+escapeHtml(acc.username)+'</div></div>'
          +'<div class="kbox"><div class="kbox-lbl">Mật khẩu</div><div class="kbox-val" style="font-family:monospace;font-size:13px">'+escapeHtml(acc.password)+'</div></div>'
          +'<div class="kbox"><div class="kbox-lbl">Loại</div><div class="kbox-val">'+escapeHtml(acc.type_name||'')+'</div></div>'
          +'<div class="kbox"><div class="kbox-lbl">Ngày mua</div><div class="kbox-val">'+escapeHtml(created)+'</div></div>'
          +'</div>'
          +'<div class="kactions">'
          +'<button class="ksm" onclick="copyText('+jsAttr(acc.username)+',\'Đã copy tài khoản!\')">📋 Copy tài khoản</button>'
          +'<button class="ksm green" onclick="copyText('+jsAttr(acc.password)+',\'Đã copy mật khẩu!\')">🔑 Copy mật khẩu</button>'
          +'</div>'
          +'</div>';
      });
      wrap.innerHTML=html;
      initMotion();
    } else {
      document.getElementById('accCntLbl').textContent='0 acc';
      wrap.innerHTML='<div class="empty-box" style="margin:20px 0"><div class="empty-ico">📦</div><div class="empty-lbl">Chưa có acc nào</div></div>';
    }
  }catch(e){}
}

// END ACC SELLING

document.addEventListener('DOMContentLoaded',function(){
  applyLang();
  // Scroll parallax chỉ bật trên màn hình lớn (tablet/desktop), tắt mobile tránh jank
  if(window.innerWidth >= 768){
    var scrollTick=false;
    var sc=document.querySelector('.scroll-area');
    if(sc){
      sc.addEventListener('scroll',function(){
        if(scrollTick)return; scrollTick=true;
        requestAnimationFrame(function(){
          var y=sc.scrollTop;
          var prof=document.querySelector('.profile-section');
          if(prof){ prof.style.transform='translateY('+Math.min(8,y*.025)+'px)'; prof.style.opacity=String(1-Math.min(.2,y/300)); }
          scrollTick=false;
        });
      },{passive:true});
    }
  }
});

