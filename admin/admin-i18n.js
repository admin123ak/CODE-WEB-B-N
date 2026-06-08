/* =========================================================
 *  ADMIN i18n — dịch giao diện admin VI ⇄ EN ⇄ ES
 *  An toàn: chỉ walk DOM text node + thay theo từ điển khớp chính xác.
 *  KHÔNG đụng PHP, không sửa logic → không thể gây lỗi 500.
 *  Chuỗi không có trong từ điển sẽ giữ nguyên tiếng Việt.
 * ========================================================= */
(function () {
  // Bản dịch: key = chuỗi tiếng Việt (đã trim), value = {en, es}
  var DICT = {
    // ---- Sidebar groups ----
    'Bảng điều khiển': { en: 'Dashboard', es: 'Panel' },
    'Đơn hàng': { en: 'Orders', es: 'Pedidos' },
    'Sản phẩm': { en: 'Products', es: 'Productos' },
    'Tài chính': { en: 'Finance', es: 'Finanzas' },
    'Hệ thống': { en: 'System', es: 'Sistema' },
    // ---- Sidebar items ----
    'Tổng quan': { en: 'Overview', es: 'Resumen' },
    'Giao dịch': { en: 'Transactions', es: 'Transacciones' },
    'GetKey Free': { en: 'Free Key', es: 'Clave Gratis' },
    'Games': { en: 'Games', es: 'Juegos' },
    'Gói Key': { en: 'Key Packages', es: 'Paquetes' },
    'Accounts': { en: 'Accounts', es: 'Cuentas' },
    'Keys': { en: 'Keys', es: 'Claves' },
    'Ví user': { en: 'User Wallet', es: 'Billetera' },
    'Config': { en: 'Config', es: 'Config' },
    'Cập nhật': { en: 'Update', es: 'Actualizar' },
    'Setup': { en: 'Setup', es: 'Configurar' },
    'Users': { en: 'Users', es: 'Usuarios' },
    'Web': { en: 'Web', es: 'Web' },
    'Thoát': { en: 'Logout', es: 'Salir' },
    'Admin Panel': { en: 'Admin Panel', es: 'Panel Admin' },
    // ---- H1 headings ----
    '📊 Dashboard': { en: '📊 Dashboard', es: '📊 Panel' },
    '🛒 Quản lý đơn hàng': { en: '🛒 Order Management', es: '🛒 Gestión de Pedidos' },
    '💰 Giao dịch tự động (MBBank + USDT TRC20)': { en: '💰 Auto Transactions (MBBank + USDT TRC20)', es: '💰 Transacciones Auto (MBBank + USDT TRC20)' },
    '👛 Ví user (balance + lịch sử)': { en: '👛 User Wallet (balance + history)', es: '👛 Billetera (saldo + historial)' },
    '🔑 Quản lý Keys': { en: '🔑 Key Management', es: '🔑 Gestión de Claves' },
    '🎮 Quản lý Games': { en: '🎮 Game Management', es: '🎮 Gestión de Juegos' },
    '📦 Quản lý Gói cước': { en: '📦 Package Management', es: '📦 Gestión de Paquetes' },
    '🏪 Quản lý Accounts': { en: '🏪 Account Management', es: '🏪 Gestión de Cuentas' },
    '🎁 GetKey Free': { en: '🎁 Free Key', es: '🎁 Clave Gratis' },
    '⚙️ Cấu hình hệ thống': { en: '⚙️ System Settings', es: '⚙️ Configuración' },
    '🧭 Setup/API & Cấu hình hệ thống': { en: '🧭 Setup/API & System Config', es: '🧭 Setup/API y Configuración' },
    '🔄 Cập nhật hệ thống': { en: '🔄 System Update', es: '🔄 Actualización' },
    '👥 Danh sách Users': { en: '👥 User List', es: '👥 Lista de Usuarios' },
    // ---- H2 / sub headings ----
    '🛒 Đơn chờ thanh toán': { en: '🛒 Pending Orders', es: '🛒 Pedidos Pendientes' },
    'Đơn chờ thanh toán': { en: 'Pending Orders', es: 'Pedidos Pendientes' },
    '📦 Key trong pool (Available)': { en: '📦 Keys in Pool (Available)', es: '📦 Claves en Pool (Disponible)' },
    'Key trong pool (Available)': { en: 'Keys in Pool (Available)', es: 'Claves en Pool (Disponible)' },
    '🔐 Key đã giao (Pending/Active/Expired/Locked)': { en: '🔐 Delivered Keys (Pending/Active/Expired/Locked)', es: '🔐 Claves Entregadas (Pending/Active/Expired/Locked)' },
    'Key đã giao (Pending/Active/Expired/Locked)': { en: 'Delivered Keys', es: 'Claves Entregadas' },
    '📋 Loại acc': { en: '📋 Account Types', es: '📋 Tipos de Cuenta' },
    '📦 Danh sách Acc (200 gần nhất)': { en: '📦 Account List (latest 200)', es: '📦 Lista de Cuentas (últimas 200)' },
    'Danh sách Acc (200 gần nhất)': { en: 'Account List (latest 200)', es: 'Lista de Cuentas (últimas 200)' },
    '📥 Topup requests (yêu cầu nạp)': { en: '📥 Top-up Requests', es: '📥 Solicitudes de Recarga' },
    '🎯 Danh sách game Acc': { en: '🎯 Account Game List', es: '🎯 Lista de Juegos de Cuenta' },
    '🏦 MBBank Poll — Quan sát': { en: '🏦 MBBank Poll — Monitor', es: '🏦 MBBank Poll — Monitor' },
    '🪙 Crypto Poll (Binance USDT TRC20) — Quan sát': { en: '🪙 Crypto Poll (Binance USDT TRC20) — Monitor', es: '🪙 Crypto Poll (Binance USDT TRC20) — Monitor' },
    // ---- Stat labels ----
    'Người dùng': { en: 'Users', es: 'Usuarios' },
    'Chờ thanh toán': { en: 'Pending Payment', es: 'Pago Pendiente' },
    'Đơn thành công': { en: 'Successful Orders', es: 'Pedidos Exitosos' },
    'Doanh thu': { en: 'Revenue', es: 'Ingresos' },
    'Key trong pool': { en: 'Keys in Pool', es: 'Claves en Pool' },
    'Key đang active': { en: 'Active Keys', es: 'Claves Activas' },
    'Tổng keys': { en: 'Total Keys', es: 'Total de Claves' },
    // ---- Table headers ----
    'Mã đơn': { en: 'Order Code', es: 'Código' },
    'User': { en: 'User', es: 'Usuario' },
    'Game / Gói': { en: 'Game / Package', es: 'Juego / Paquete' },
    'Game/Gói': { en: 'Game / Package', es: 'Juego / Paquete' },
    'Key đã tạo': { en: 'Created Key', es: 'Clave Creada' },
    'Tiền': { en: 'Amount', es: 'Monto' },
    'Thời gian': { en: 'Time', es: 'Hora' },
    'Thao tác': { en: 'Action', es: 'Acción' },
    'Hành động': { en: 'Action', es: 'Acción' },
    'Game': { en: 'Game', es: 'Juego' },
    'Gói': { en: 'Package', es: 'Paquete' },
    'Giá': { en: 'Price', es: 'Precio' },
    'Giá (đ)': { en: 'Price (đ)', es: 'Precio (đ)' },
    'Ngày': { en: 'Days', es: 'Días' },
    'Giờ': { en: 'Hours', es: 'Horas' },
    'Thời hạn': { en: 'Duration', es: 'Duración' },
    'Loại': { en: 'Type', es: 'Tipo' },
    'Loại key': { en: 'Key Type', es: 'Tipo de Clave' },
    'Loại acc': { en: 'Account Type', es: 'Tipo de Cuenta' },
    'Trạng thái': { en: 'Status', es: 'Estado' },
    'Hết hạn': { en: 'Expiry', es: 'Expira' },
    'Tài khoản': { en: 'Account', es: 'Cuenta' },
    'Mật khẩu': { en: 'Password', es: 'Contraseña' },
    'Ghi chú': { en: 'Note', es: 'Nota' },
    'Số tiền': { en: 'Amount', es: 'Monto' },
    'Nội dung': { en: 'Content', es: 'Contenido' },
    'Nguồn': { en: 'Source', es: 'Fuente' },
    'Lý do': { en: 'Reason', es: 'Motivo' },
    'Lỗi': { en: 'Error', es: 'Error' },
    'Kiểm tra': { en: 'Check', es: 'Verificar' },
    'Vai trò': { en: 'Role', es: 'Rol' },
    'Giảm giá': { en: 'Discount', es: 'Descuento' },
    'Số dư hiện tại': { en: 'Current Balance', es: 'Saldo Actual' },
    'Sau giao dịch': { en: 'After Transaction', es: 'Tras Transacción' },
    'Cộng thực': { en: 'Net Credit', es: 'Crédito Neto' },
    'Xử lý lúc': { en: 'Processed At', es: 'Procesado' },
    'Đọc lúc': { en: 'Read At', es: 'Leído' },
    'Tên game': { en: 'Game Name', es: 'Nombre del Juego' },
    'Tên gói': { en: 'Package Name', es: 'Nombre del Paquete' },
    'Tên loại': { en: 'Type Name', es: 'Nombre del Tipo' },
    'Tên loại acc': { en: 'Account Type Name', es: 'Nombre Tipo de Cuenta' },
    'Link tải': { en: 'Download Link', es: 'Enlace de Descarga' },
    'Link tải (download)': { en: 'Download Link', es: 'Enlace de Descarga' },
    'Thứ tự': { en: 'Order', es: 'Orden' },
    'Mô tả': { en: 'Description', es: 'Descripción' },
    'Yêu cầu': { en: 'Request', es: 'Solicitud' },
    'Thay đổi': { en: 'Change', es: 'Cambio' },
    'File liên quan': { en: 'Related File', es: 'Archivo Relacionado' },
    'Đổi icon': { en: 'Change Icon', es: 'Cambiar Icono' },
    'Đơn': { en: 'Order', es: 'Pedido' },
    'Stock': { en: 'Stock', es: 'Stock' },
    'Đã bán': { en: 'Sold', es: 'Vendido' },
    'Có sẵn': { en: 'Available', es: 'Disponible' },
    'Đã giữ': { en: 'Reserved', es: 'Reservado' },
    // ---- Buttons / actions ----
    'Lưu': { en: 'Save', es: 'Guardar' },
    '💾 Lưu': { en: '💾 Save', es: '💾 Guardar' },
    '💾 Lưu cấu hình': { en: '💾 Save Config', es: '💾 Guardar Config' },
    'Sửa': { en: 'Edit', es: 'Editar' },
    'Xoá': { en: 'Delete', es: 'Eliminar' },
    '🗑 Xoá': { en: '🗑 Delete', es: '🗑 Eliminar' },
    '🗑 Xoá game': { en: '🗑 Delete Game', es: '🗑 Eliminar Juego' },
    'Chọn': { en: 'Select', es: 'Seleccionar' },
    'Tải': { en: 'Download', es: 'Descargar' },
    '⬇ Tải': { en: '⬇ Download', es: '⬇ Descargar' },
    'Mở link': { en: 'Open Link', es: 'Abrir Enlace' },
    'Tạo lại link': { en: 'Recreate Link', es: 'Recrear Enlace' },
    'Tạo lại link ': { en: 'Recreate Link', es: 'Recrear Enlace' },
    'Gọi': { en: 'Call', es: 'Llamar' },
    'Match đơn': { en: 'Match Order', es: 'Emparejar Pedido' },
    'Chạy maintenance ngay': { en: 'Run Maintenance Now', es: 'Ejecutar Mantenimiento' },
    '🔍 Lọc': { en: '🔍 Filter', es: '🔍 Filtrar' },
    '🔍 Tìm': { en: '🔍 Search', es: '🔍 Buscar' },
    '➕ Thêm': { en: '➕ Add', es: '➕ Añadir' },
    '➕ Thêm game mới': { en: '➕ Add New Game', es: '➕ Añadir Juego' },
    '➕ Thêm game Acc': { en: '➕ Add Account Game', es: '➕ Añadir Juego de Cuenta' },
    '➕ Thêm gói mới': { en: '➕ Add New Package', es: '➕ Añadir Paquete' },
    '➕ Thêm key vào pool': { en: '➕ Add Keys to Pool', es: '➕ Añadir Claves al Pool' },
    '➕ Thêm vào pool': { en: '➕ Add to Pool', es: '➕ Añadir al Pool' },
    '➕ Thêm vào pool key free': { en: '➕ Add Free Keys to Pool', es: '➕ Añadir Claves Gratis al Pool' },
    '➕ Thêm loại acc mới': { en: '➕ Add Account Type', es: '➕ Añadir Tipo de Cuenta' },
    '➕ Thêm nhiều key free cùng lúc': { en: '➕ Add Multiple Free Keys', es: '➕ Añadir Varias Claves Gratis' },
    '🎮 Thêm game bán Acc': { en: '🎮 Add Account-Selling Game', es: '🎮 Añadir Juego de Cuentas' },
    '📨 Gửi tin nhắn test': { en: '📨 Send Test Message', es: '📨 Enviar Mensaje de Prueba' },
    '🔄 Set lại Webhook': { en: '🔄 Reset Webhook', es: '🔄 Restablecer Webhook' },
    '🔄 Cập nhật ngay': { en: '🔄 Update Now', es: '🔄 Actualizar Ahora' },
    'Tải lại trang': { en: 'Reload Page', es: 'Recargar Página' },
    'Để sau': { en: 'Later', es: 'Más tarde' },
    // ---- Filters ----
    'Tất cả': { en: 'All', es: 'Todos' },
    'Tất cả nguồn': { en: 'All Sources', es: 'Todas las Fuentes' },
    'Tất cả trạng thái': { en: 'All Statuses', es: 'Todos los Estados' },
    'Chờ thanh toán ': { en: 'Pending Payment', es: 'Pago Pendiente' },
    'Đã duyệt': { en: 'Approved', es: 'Aprobado' },
    'Đã auto duyệt': { en: 'Auto-Approved', es: 'Auto-Aprobado' },
    'Đang chờ': { en: 'Pending', es: 'Pendiente' },
    // ---- Common labels (config/form) ----
    'Thông tin site/bot': { en: 'Site/Bot Info', es: 'Info Sitio/Bot' },
    '📞 Footer trang web (khách thấy)': { en: '📞 Website Footer (customer view)', es: '📞 Pie de Página (vista cliente)' },
    'Số ngày': { en: 'Days', es: 'Días' },
    'Số giờ': { en: 'Hours', es: 'Horas' },
    'Số lớp vượt link': { en: 'Link Bypass Layers', es: 'Capas de Enlace' },
    'Bắt vượt link (getkey web)': { en: 'Require Link Bypass (web getkey)', es: 'Requerir Bypass de Enlace' },
    'Bật': { en: 'On', es: 'Activado' },
    'Tắt': { en: 'Off', es: 'Desactivado' },
    'Bật (phải vượt link)': { en: 'On (must bypass link)', es: 'Activado (debe pasar enlace)' },
    'Tắt (hiện key luôn)': { en: 'Off (show key directly)', es: 'Desactivado (mostrar clave)' },
    'Loại Category': { en: 'Category', es: 'Categoría' },
    'Danh sách key (mỗi dòng 1 key)': { en: 'Key list (1 per line)', es: 'Lista de claves (1 por línea)' },
    'Danh sách acc (mỗi dòng: tk:mk hoặc tk|mk)': { en: 'Account list (each line: user:pass or user|pass)', es: 'Lista de cuentas (cada línea: user:pass o user|pass)' },
    'Khách hàng': { en: 'Customer', es: 'Cliente' },
    // ---- Empty states ----
    'Chưa có key free nào': { en: 'No free keys yet', es: 'Sin claves gratis aún' },
    'Chưa có key nào trong pool. Thêm key bằng form trên.': { en: 'No keys in pool. Add keys with the form above.', es: 'Sin claves en el pool. Añade con el formulario.' },
    'Chưa có key đã giao nào.': { en: 'No delivered keys.', es: 'Sin claves entregadas.' },
    'Chưa có loại acc nào — thêm bên dưới': { en: 'No account types — add below', es: 'Sin tipos de cuenta — añade abajo' },
    'Chưa có topup request phù hợp.': { en: 'No matching top-up requests.', es: 'Sin solicitudes de recarga.' },
    'Chưa có giao dịch phù hợp.': { en: 'No matching transactions.', es: 'Sin transacciones.' },
    'Chưa có bút toán nào phù hợp.': { en: 'No matching entries.', es: 'Sin entradas.' },
    'Chưa có log.': { en: 'No logs.', es: 'Sin registros.' },
    'Không có đơn nào chờ thanh toán ✅': { en: 'No pending orders ✅', es: 'Sin pedidos pendientes ✅' },
    'sẵn sàng trong pool': { en: 'ready in pool', es: 'listas en el pool' },
    'Tự xoá sau 3 ngày nếu không gia hạn': { en: 'Auto-deleted after 3 days if not renewed', es: 'Auto-eliminado tras 3 días sin renovar' }
  };

  var LANGS = ['vi', 'en', 'es'];
  var lang = (function () {
    try { var s = localStorage.getItem('admin_lang'); return LANGS.indexOf(s) >= 0 ? s : 'vi'; } catch (e) { return 'vi'; }
  })();

  // Lưu text gốc (tiếng Việt) vào mỗi text node ở lần chạy đầu để có thể dịch qua lại
  var ORIG = '__adm_orig';

  function walk(node) {
    if (node.nodeType === 3) { // text node
      var raw = node[ORIG] != null ? node[ORIG] : node.nodeValue;
      var key = raw.trim();
      if (!key) return;
      var entry = DICT[key];
      if (!entry) return;
      if (node[ORIG] == null) node[ORIG] = node.nodeValue; // backup nguyên bản (giữ khoảng trắng)
      if (lang === 'vi') {
        node.nodeValue = node[ORIG];
      } else {
        var t = entry[lang] || key;
        // giữ nguyên khoảng trắng đầu/cuối của node gốc
        node.nodeValue = node[ORIG].replace(key, t);
      }
      return;
    }
    if (node.nodeType !== 1) return;
    var tag = node.tagName;
    if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEXTAREA' || node.isContentEditable) return;
    // Bỏ qua input value/code/pre (kỹ thuật) — chỉ dịch text hiển thị
    for (var i = 0; i < node.childNodes.length; i++) walk(node.childNodes[i]);
  }

  function applyLang() {
    walk(document.body);
    // placeholder cho input/select có data
    document.querySelectorAll('[placeholder]').forEach(function (el) {
      var bk = el.getAttribute('data-ph-orig');
      if (bk == null) { bk = el.getAttribute('placeholder'); el.setAttribute('data-ph-orig', bk); }
      var key = (bk || '').trim();
      var entry = DICT[key];
      el.setAttribute('placeholder', (lang !== 'vi' && entry && entry[lang]) ? entry[lang] : bk);
    });
    // cập nhật trạng thái nút
    document.querySelectorAll('.adm-lang-pill').forEach(function (p) {
      p.classList.toggle('active', p.getAttribute('data-l') === lang);
    });
    document.documentElement.setAttribute('lang', lang);
    try { localStorage.setItem('admin_lang', lang); } catch (e) {}
  }

  window.adminSetLang = function (l) {
    if (LANGS.indexOf(l) < 0) l = 'vi';
    lang = l;
    applyLang();
  };

  // Chèn nút switcher vào topbar
  function injectSwitcher() {
    var bar = document.querySelector('.topbar');
    if (!bar) return;
    var box = document.createElement('div');
    box.className = 'adm-lang-box';
    box.innerHTML =
      '<button class="adm-lang-pill" data-l="vi" onclick="adminSetLang(\'vi\')" title="Tiếng Việt">🇻🇳</button>' +
      '<button class="adm-lang-pill" data-l="en" onclick="adminSetLang(\'en\')" title="English">🇬🇧</button>' +
      '<button class="adm-lang-pill" data-l="es" onclick="adminSetLang(\'es\')" title="Español">🇪🇸</button>';
    // chèn trước nút logout (topbar-right)
    var right = bar.querySelector('.topbar-right');
    if (right) bar.insertBefore(box, right); else bar.appendChild(box);

    var st = document.createElement('style');
    st.textContent =
      '.adm-lang-box{display:flex;gap:3px;background:rgba(15,23,42,.6);border:1px solid var(--line,#26354f);border-radius:999px;padding:3px;margin-left:auto;margin-right:10px}' +
      '.adm-lang-pill{appearance:none;border:0;background:transparent;width:30px;height:28px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;transition:.18s;line-height:1;padding:0;opacity:.55}' +
      '.adm-lang-pill:hover{opacity:.85}' +
      '.adm-lang-pill.active{background:linear-gradient(135deg,#2563eb,#06b6d4);opacity:1;box-shadow:0 3px 10px rgba(6,182,212,.35)}' +
      '@media(max-width:480px){.adm-lang-pill{width:26px;height:24px;font-size:13px}}';
    document.head.appendChild(st);
  }

  function init() {
    injectSwitcher();
    if (lang !== 'vi') applyLang(); else applyLang(); // applyLang cũng set active pill
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
