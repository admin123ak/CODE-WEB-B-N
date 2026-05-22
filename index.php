
<?php require_once 'config.php'; header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0'); header('Pragma: no-cache'); ?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#0a0e1a">
<title><?= SITE_NAME ?></title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{height:100%;background:#06080f!important;color:#e6edf3;font-family:'Inter',-apple-system,'SF Pro Display','Segoe UI',sans-serif;overflow-x:hidden}
:root{
  --bg:#06080f;--bg2:#0c1120;--bg3:#131b2e;--bg4:#1a2540;
  --border:#1a2744;--border2:#243554;
  --text:#f0f4ff;--text2:#7a8ba8;--text3:#9fb3d0;
  --blue:#4f8cff;--blue2:#7aadff;
  --purple:#a78bfa;--purple2:#c4b5fd;
  --cyan:#22d3ee;--cyan2:#67e8f9;
  --green:#34d399;--green2:#6ee7b7;
  --orange:#fb923c;--orange2:#fdba74;
  --red:#f87171;--red2:#fca5a5;
  --brand:#dc2626;--brand2:#ef4444;--brand3:#f97316;
  --brand-soft:rgba(239,68,68,.14);--brand-border:rgba(239,68,68,.35);
  --glow-brand:0 0 28px rgba(239,68,68,.32);
  --glow-blue:0 0 24px rgba(79,140,255,.25);
  --glow-purple:0 0 24px rgba(167,139,250,.25);
  --glow-green:0 0 24px rgba(52,211,153,.25);
  --glow-cyan:0 0 24px rgba(34,211,238,.25);
  --ease-spring:cubic-bezier(.22,1,.36,1);
  --glass:linear-gradient(160deg,rgba(12,17,32,.88),rgba(19,27,46,.82));
  --glass-border:rgba(79,140,255,.12);
  --card-shadow:0 8px 32px rgba(0,0,0,.45),0 1px 0 rgba(255,255,255,.04);
  --radius-lg:18px;--radius-md:14px;--radius-sm:10px;
}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
::-webkit-scrollbar-thumb:hover{background:var(--border2)}
#app{height:100vh;height:100dvh;display:flex;flex-direction:column;max-width:480px;margin:0 auto;padding-bottom:60px;background:radial-gradient(ellipse at 20% 0%,rgba(79,140,255,.10),transparent 50%),radial-gradient(ellipse at 80% 15%,rgba(167,139,250,.08),transparent 45%),radial-gradient(ellipse at 50% 100%,rgba(34,211,238,.06),transparent 50%),var(--bg);position:relative;overflow:hidden}
#loadingScreen{position:fixed;inset:0;background:var(--bg);z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;transition:opacity .4s ease}
#loadingScreen.hide{opacity:0;pointer-events:none}
.load-logo{width:72px;height:72px;border-radius:22px;background:linear-gradient(135deg,var(--purple),var(--blue));display:flex;align-items:center;justify-content:center;font-size:32px;box-shadow:var(--glow-purple);animation:loadPop .6s cubic-bezier(.34,1.56,.64,1)}
@keyframes loadPop{from{transform:scale(0) rotate(-8deg);opacity:0}to{transform:scale(1) rotate(0);opacity:1}}
.load-title{font-size:20px;font-weight:900;background:linear-gradient(135deg,var(--purple2),var(--blue2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:fadeUp .5s ease .2s both;letter-spacing:-.3px}
.load-bar-wrap{width:160px;height:4px;background:var(--bg3);border-radius:4px;overflow:hidden;animation:fadeUp .5s ease .3s both;border:1px solid var(--border)}
.load-bar{height:100%;width:0;background:linear-gradient(90deg,var(--purple),var(--blue),var(--cyan));border-radius:4px;animation:loadBar 1.5s ease .4s forwards}
@keyframes loadBar{to{width:100%}}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@keyframes floatIn{from{opacity:0;transform:translateY(18px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
.app-header{padding:14px 18px;background:rgba(6,8,15,.78);border-bottom:1px solid var(--glass-border);display:flex;align-items:center;gap:10px;flex-shrink:0;backdrop-filter:blur(20px);box-shadow:0 4px 24px rgba(0,0,0,.3)}
.promo-badge{background:linear-gradient(135deg,#ff416c,#ff4b2b);border-radius:var(--radius-sm);padding:7px 12px;font-size:11px;font-weight:800;color:#fff;display:flex;align-items:center;gap:5px;animation:pulseBadge 2.5s ease-in-out infinite;box-shadow:0 0 18px rgba(255,65,108,.35)}
@keyframes pulseBadge{0%,100%{transform:scale(1);box-shadow:0 0 18px rgba(255,65,108,.35)}50%{transform:scale(1.04);box-shadow:0 0 26px rgba(255,65,108,.5)}}
.lang-btn{margin-left:auto;background:rgba(19,27,46,.8);border:1px solid var(--glass-border);border-radius:999px;padding:7px 12px;font-size:11px;font-weight:900;color:var(--purple2);cursor:pointer;transition:all .2s var(--ease-spring);font-family:inherit}.lang-btn:active{transform:scale(.94);filter:brightness(1.15)}
.lang-pills{margin-left:auto;display:flex;gap:4px;background:rgba(19,27,46,.85);border:1px solid var(--glass-border);border-radius:999px;padding:3px;box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
.lang-pill{appearance:none;border:0;background:transparent;width:30px;height:28px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;transition:all .2s var(--ease-spring);font-family:inherit;line-height:1;padding:0}
.lang-pill:active{transform:scale(.9)}
.lang-pill.active{background:linear-gradient(135deg,var(--brand),var(--brand2));box-shadow:0 4px 12px rgba(239,68,68,.35),inset 0 1px 0 rgba(255,255,255,.18)}
.lang-pill .flag{filter:drop-shadow(0 1px 1px rgba(0,0,0,.4))}
.bank-chip{background:var(--bg3);border:1px solid var(--border);border-radius:20px;padding:7px 13px;font-size:12px;font-weight:700;color:var(--blue2);display:flex;align-items:center;gap:6px;cursor:pointer;transition:all .2s var(--ease-spring)}.bank-chip:active{transform:scale(.95);background:var(--bg4)}
.pressable{transition:transform .18s var(--ease-spring),filter .18s,box-shadow .18s}.pressable.touching{transform:scale(.975)!important;filter:brightness(1.08)}
.scroll-area{flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;overscroll-behavior-y:contain;scroll-behavior:smooth;padding-bottom:80px;perspective:900px}
.profile-section{padding:28px 20px 20px;display:flex;flex-direction:column;align-items:center;gap:10px;animation:fadeUp .4s ease}
.avatar-ring{position:relative;width:88px;height:88px;border-radius:50%;padding:3px;background:conic-gradient(var(--purple),var(--blue),var(--cyan),var(--green),var(--purple));animation:spinRing 4s linear infinite;box-shadow:var(--glow-purple)}
@keyframes spinRing{to{transform:rotate(360deg)}}
.avatar-inner{width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,var(--bg3),var(--bg4));display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:900;overflow:hidden;border:3px solid var(--bg)}
.avatar-inner img{width:100%;height:100%;object-fit:cover;border-radius:50%}
.profile-name{font-size:19px;font-weight:900;letter-spacing:-.3px}
.profile-handle{font-size:13px;color:var(--blue2);display:flex;align-items:center;gap:5px;font-weight:600}
.verified-icon{width:17px;height:17px;background:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;color:#fff;box-shadow:0 0 8px rgba(79,140,255,.5)}
.stats-card{margin:0 16px 20px;background:var(--glass);border:1px solid var(--glass-border);border-radius:20px;padding:18px;display:flex;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:floatIn .55s var(--ease-spring) .1s both}
.stat-item{flex:1;text-align:center}
.stat-item+.stat-item{border-left:1px solid var(--border)}
.stat-num{font-size:26px;font-weight:900;line-height:1;letter-spacing:-.5px}
.stat-num.blue{color:var(--blue2)}.stat-num.green{color:var(--green2)}.stat-num.orange{color:var(--orange2)}
.stat-label{font-size:11px;color:var(--text2);margin-top:5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.sec-head{padding:8px 16px 12px;display:flex;align-items:center;gap:10px;animation:fadeUp .4s ease .15s both}
.sec-icon,.key-head-icon{width:34px;height:34px;border-radius:var(--radius-sm);background:linear-gradient(135deg,rgba(239,68,68,.16),rgba(249,115,22,.08));border:1px solid var(--brand-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand2);box-shadow:var(--glow-brand)}
.sec-icon svg,.key-head-icon svg,.label-ico svg{width:20px;height:20px;display:block;stroke:currentColor}
.sec-title{font-size:17px;font-weight:900;letter-spacing:-.3px;background:linear-gradient(135deg,#fff 0%,#fca5a5 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.sec-sub{font-size:12px;color:var(--text2);font-weight:500;margin-top:1px}
.card{margin:0 16px 14px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:floatIn .58s var(--ease-spring) .18s both;will-change:transform}
.card-inner-label{padding:12px 16px 6px;font-size:12px;font-weight:700;color:var(--blue2);display:flex;align-items:center;gap:7px}.label-ico{width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;color:currentColor}
.game-btn{margin:4px 12px 8px;border-radius:var(--radius-md);padding:14px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;background:rgba(19,27,46,.8);border:1px solid var(--glass-border);transition:all .22s var(--ease-spring);box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
.game-btn:active{transform:scale(.98)}
.game-btn.chosen{border-color:var(--purple);background:linear-gradient(135deg,rgba(167,139,250,.12),rgba(79,140,255,.08));box-shadow:inset 0 0 24px rgba(167,139,250,.08),var(--glow-purple)}
.game-emoji{width:52px;height:52px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--bg4),var(--bg3));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.3)}.game-emoji img{width:100%;height:100%;object-fit:cover;border-radius:13px}
.game-title{font-size:15px;font-weight:800;letter-spacing:-.2px}
.game-pkgname{font-size:11px;color:var(--blue2);margin-top:3px;font-weight:600}
.game-roottype{font-size:11px;color:var(--text2);margin-top:2px}
.chev{color:var(--text2);font-size:20px;margin-left:auto}
.pkg-label{padding:10px 16px 8px;font-size:12px;font-weight:700;color:var(--purple2);display:flex;align-items:center;gap:7px}
.pkg-list{padding:0 12px 12px;display:flex;flex-direction:column;gap:8px}
.pkg-row{padding:14px 16px;background:rgba(19,27,46,.78);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:space-between;border:1px solid var(--glass-border);cursor:pointer;transition:all .22s var(--ease-spring);box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.pkg-row:active{transform:scale(.98)}
.pkg-row.on{border-color:rgba(34,211,238,.5);background:linear-gradient(135deg,rgba(34,211,238,.12),rgba(79,140,255,.08));box-shadow:0 8px 28px rgba(34,211,238,.12),inset 0 0 0 1px rgba(255,255,255,.03),var(--glow-cyan)}
.pkg-days{font-size:14px;font-weight:800}
.pkg-mode{font-size:11px;color:var(--text2);margin-top:3px}
.pkg-cost{font-size:16px;font-weight:900;color:var(--text);letter-spacing:-.3px}
.pkg-row.on .pkg-cost{color:var(--cyan2)}
.pkg-row.free{border-color:rgba(52,211,153,.35);background:linear-gradient(135deg,rgba(52,211,153,.12),rgba(34,211,238,.06))}.pkg-row.free .pkg-cost{color:var(--green2)}
.action-bar{padding:14px 16px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px}
.ic-btn{width:50px;height:50px;border-radius:16px;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .18s var(--ease-spring);flex-shrink:0;text-decoration:none;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,.3);position:relative;overflow:hidden}
.ic-btn svg{width:24px;height:24px;display:block;filter:drop-shadow(0 2px 5px rgba(0,0,0,.25))}
.ic-btn:before{content:"";position:absolute;inset:1px;border-radius:15px;background:linear-gradient(135deg,rgba(255,255,255,.22),rgba(255,255,255,0));pointer-events:none}
.ic-btn.tg{background:linear-gradient(135deg,#1d9bf0,#35c4ff);border-color:rgba(56,189,248,.45);box-shadow:0 6px 20px rgba(29,155,240,.35)}
.ic-btn.play{background:linear-gradient(135deg,#10b981,#06b6d4);border-color:rgba(52,211,153,.45);box-shadow:0 6px 20px rgba(16,185,129,.35)}
.ic-btn.disabled{opacity:.45;filter:grayscale(1);cursor:not-allowed}
.ic-btn:active{transform:scale(.92)}
.buy-btn{flex:1;height:48px;border-radius:24px;border:none;background:var(--bg4);color:var(--text2);font-size:14px;font-weight:800;cursor:not-allowed;display:flex;align-items:center;justify-content:center;flex-direction:column;transition:all .25s var(--ease-spring);font-family:inherit;letter-spacing:-.2px}
.buy-btn.go{background:linear-gradient(135deg,var(--brand),var(--brand2) 55%,var(--brand3));color:#fff;cursor:pointer;box-shadow:0 12px 34px rgba(220,38,38,.45),inset 0 1px 0 rgba(255,255,255,.18);position:relative;overflow:hidden;letter-spacing:.2px}.buy-btn.go:before{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent,rgba(255,255,255,.22),transparent);transform:translateX(-120%);animation:shine 2.8s ease-in-out infinite}@keyframes shine{55%,100%{transform:translateX(120%)}}
.buy-btn.go:active{transform:scale(.97)}
.buy-sub{font-size:10px;font-weight:600;opacity:.85;margin-top:2px}
.note-txt{margin:0 16px 14px;font-size:11px;color:var(--text2);text-align:center;line-height:1.6;padding:12px;background:rgba(255,255,255,.02);border-radius:var(--radius-sm);border:1px solid var(--border)}
.key-head{padding:10px 16px;display:flex;align-items:center;gap:10px;animation:fadeUp .4s ease .25s both}
.key-head-icon{color:var(--orange2);width:32px;height:32px;border-radius:var(--radius-sm);background:linear-gradient(135deg,rgba(251,146,60,.12),rgba(245,158,11,.06));border:1px solid rgba(251,146,60,.15);display:flex;align-items:center;justify-content:center}
.key-head-icon svg{width:20px;height:20px;stroke:currentColor}
.key-count-lbl{font-size:12px;color:var(--text2);font-weight:600;margin-top:2px}
.filter-wrap{padding:0 16px 10px;display:flex;gap:8px;overflow-x:auto}
.ftab{padding:9px 16px;border-radius:24px;border:1px solid var(--glass-border);background:rgba(19,27,46,.78);color:var(--text2);font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:inherit;transition:all .2s var(--ease-spring)}
.ftab.on{background:linear-gradient(135deg,var(--purple),var(--blue));border-color:transparent;color:#fff;box-shadow:0 2px 14px rgba(167,139,250,.35)}
.srch{margin:0 16px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px 14px;display:flex;align-items:center;gap:8px;transition:border-color .2s,box-shadow .2s}
.srch:focus-within{border-color:var(--brand2);box-shadow:var(--glow-brand)}
.srch input{flex:1;background:none;border:none;outline:none;color:var(--text);font-size:13px;font-family:inherit}
.srch input::placeholder{color:var(--text2)}
.kcard{margin:0 16px 12px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:slideIn .38s var(--ease-spring) both;will-change:transform,opacity}
@keyframes slideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.kcard.is-active{border-color:rgba(52,211,153,.3);box-shadow:0 4px 24px rgba(52,211,153,.08),var(--glow-green)}
.kcard.is-expired{border-color:rgba(251,146,60,.2)}
.kcard.is-locked{border-color:rgba(248,113,113,.2)}
.ktop{padding:14px 16px 10px}
.kcode-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.kcode{font-size:14px;font-weight:900;letter-spacing:.6px;font-family:'SF Mono','JetBrains Mono','Courier New',monospace;color:var(--text);background:rgba(255,255,255,.04);padding:6px 10px;border-radius:var(--radius-sm);border:1px solid var(--border)}
.kbadge{padding:5px 12px;border-radius:20px;font-size:10px;font-weight:800;display:flex;align-items:center;gap:4px;flex-shrink:0;text-transform:uppercase;letter-spacing:.5px}
.kbadge.active{background:rgba(52,211,153,.12);color:var(--green2);border:1px solid rgba(52,211,153,.35);box-shadow:0 0 10px rgba(52,211,153,.15)}
.kbadge.expired{background:rgba(251,146,60,.12);color:var(--orange2);border:1px solid rgba(251,146,60,.35);box-shadow:0 0 10px rgba(251,146,60,.1)}
.knote{margin:0 18px 14px;padding:12px 13px;border-radius:var(--radius-md);background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);color:#fcd34d;font-size:12px;font-weight:700;line-height:1.5}
.help-fab{position:fixed;right:18px;bottom:28px;width:56px;height:56px;border-radius:20px;background:linear-gradient(135deg,var(--cyan),var(--blue));border:1px solid rgba(255,255,255,.15);box-shadow:0 18px 50px rgba(34,211,238,.35);color:white;font-size:24px;z-index:60;display:flex;align-items:center;justify-content:center;cursor:pointer;animation:float 3s ease-in-out infinite;transition:transform .2s var(--ease-spring)}.help-fab:active{transform:scale(.92)}.help-panel{position:fixed;right:14px;left:14px;bottom:94px;max-width:420px;margin:auto;background:rgba(8,12,24,.96);border:1px solid var(--glass-border);border-radius:24px;box-shadow:0 24px 80px rgba(0,0,0,.5);z-index:70;overflow:hidden;display:none;backdrop-filter:blur(20px)}.help-panel.show{display:block;animation:slideUp .28s var(--ease-spring)}.help-head{padding:16px 18px;background:linear-gradient(135deg,rgba(34,211,238,.15),rgba(79,140,255,.1));display:flex;justify-content:space-between;align-items:center}.help-title{font-weight:900}.help-close{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:12px;padding:7px 10px;cursor:pointer;font-size:14px}.help-body{padding:14px;max-height:52vh;overflow:auto}.help-q{width:100%;text-align:left;margin:7px 0;padding:12px 13px;border-radius:var(--radius-md);background:rgba(19,27,46,.8);border:1px solid rgba(148,163,184,.12);color:#e5edf8;font-weight:800;font-size:13px;cursor:pointer;transition:background .2s}.help-q:hover{background:rgba(19,27,46,.9)}.help-a{margin:10px 0 6px;padding:13px;border-radius:var(--radius-md);background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.18);color:#d1fae5;font-size:13px;line-height:1.55;display:none}.help-a.show{display:block}.pending-pay-box{margin:0 16px 14px;padding:14px;border-radius:var(--radius-md);background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);box-shadow:0 8px 24px rgba(0,0,0,.2)}.pending-pay-title{font-size:13px;font-weight:900;color:#fde68a;margin-bottom:6px}.pending-pay-sub{font-size:12px;color:#fcd34d;line-height:1.45;margin-bottom:10px}.pending-pay-btn{width:100%;height:42px;border:0;border-radius:var(--radius-md);background:linear-gradient =linear-gradient(135deg,#fb923c,#f97316);color:white;font-weight:900;font-size:13px;cursor:pointer;transition:all .2s var(--ease-spring);box-shadow:0 4px 14px rgba(251,146,60,.3)}.pending-pay-btn:active{transform:scale(.97)}
.kbadge.locked{background:rgba(248,113,113,.12);color:var(--red2);border:1px solid rgba(248,113,113,.35);box-shadow:0 0 10px rgba(248,113,113,.1)}
.kbadge.pending{background:rgba(167,139,250,.12);color:var(--purple2);border:1px solid rgba(167,139,250,.35);animation:pendingPulse 2s ease-in-out infinite}
@keyframes pendingPulse{0%,100%{box-shadow:0 0 8px rgba(167,139,250,.15)}50%{box-shadow:0 0 18px rgba(167,139,250,.35)}}
.kgame{font-size:11px;color:var(--blue2);font-weight:700}
.kgrid{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:0 16px 12px}
.kbox{background:var(--bg3);border-radius:var(--radius-sm);padding:10px 12px;border:1px solid var(--border);transition:border-color .2s}.kbox:hover{border-color:var(--glass-border)}
.kbox-lbl{font-size:10px;color:var(--text2);font-weight:700;margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px}
.kbox-val{font-size:14px;font-weight:800;letter-spacing:-.3px}
.kbox-val.red{color:var(--red2)}.kbox-val.green{color:var(--green2)}.kbox-val.orange{color:var(--orange2)}
.cdwrap{padding:0 16px 12px}
.cdbar-bg{height:6px;background:var(--bg4);border-radius:4px;overflow:hidden;margin-bottom:8px;border:1px solid var(--border)}
.cdbar{height:100%;border-radius:4px;transition:width 1s linear,background .5s}
.cdtxt{font-size:12px;color:var(--text2);text-align:center;font-weight:700;letter-spacing:.3px}
.cdtxt span{color:var(--text);font-weight:900}
.kactions{padding:10px 16px 14px;display:flex;gap:8px;border-top:1px solid var(--border);flex-wrap:wrap}
.ksm{padding:9px 14px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--bg3);color:var(--text);font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s var(--ease-spring);white-space:nowrap}.ksm:active{transform:scale(.94)}
.ksm.red{border-color:rgba(248,113,113,.4);color:var(--red2);background:rgba(248,113,113,.06)}
.ksm.blue{border-color:rgba(79,140,255,.4);color:var(--blue2);background:rgba(79,140,255,.06)}
.ksm.green{border-color:rgba(52,211,153,.4);color:var(--green2);background:rgba(52,211,153,.06)}
.spin{width:28px;height:28px;border:3px solid var(--border);border-top-color:var(--blue);border-radius:50%;animation:sp .8s linear infinite;margin:0 auto 10px}
@keyframes sp{to{transform:rotate(360deg)}}
.loading{padding:40px;text-align:center;color:var(--text2);font-size:13px;font-weight:600}
.empty-box{padding:50px 20px;text-align:center}
.empty-ico{font-size:52px;margin-bottom:12px;animation:emptyFloat 3s ease-in-out infinite}
@keyframes emptyFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.empty-lbl{color:var(--text2);font-size:14px;font-weight:600}
#toast{position:fixed;top:72px;left:50%;transform:translateX(-50%) translateY(-16px);background:rgba(17,24,39,.96);border:1px solid var(--border);backdrop-filter:blur(12px);color:var(--text);padding:10px 20px;border-radius:14px;font-size:13px;font-weight:700;z-index:9999;opacity:0;transition:all .28s cubic-bezier(.34,1.56,.64,1);pointer-events:none;white-space:nowrap;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,.4)}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
#toast.success{border-color:var(--green);color:var(--green2)}
#toast.error{border-color:var(--red);color:var(--red2)}
#toast.info{border-color:var(--blue);color:var(--blue2)}
.moverlay{position:fixed;inset:0;background:rgba(0,0,0,.62);z-index:2000;display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity .28s ease;backdrop-filter:blur(8px)}
.moverlay.show{opacity:1;pointer-events:all}
.mbox{background:linear-gradient(180deg,rgba(15,23,42,.96),rgba(10,14,26,.98));border-radius:28px 28px 0 0;padding:0 0 20px;width:100%;max-width:480px;transform:translateY(105%) scale(.98);transition:transform .38s var(--ease-spring);border-top:1px solid rgba(96,165,250,.18);max-height:88vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 -20px 60px rgba(0,0,0,.38)}
.moverlay.show .mbox{transform:translateY(0) scale(1)}
.mhandle{width:44px;height:4px;background:var(--border);border-radius:2px;margin:14px auto 0}
.mtitle{font-size:17px;font-weight:900;text-align:center;color:var(--text);padding:14px 20px 12px;border-bottom:1px solid var(--border);flex-shrink:0}
.mscroll{overflow-y:auto;padding:14px 16px;flex:1}
.mgame{padding:14px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;background:rgba(26,34,52,.78);border-radius:18px;margin-bottom:10px;border:1px solid rgba(96,165,250,.12);transition:all .22s var(--ease-spring);box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.mgame:active{transform:scale(.98)}
.mgame.on{border-color:var(--purple);background:rgba(139,92,246,.1);box-shadow:0 0 14px rgba(139,92,246,.15)}
.vip-tag{display:inline-flex;align-items:center;gap:3px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:9px;font-weight:900;padding:2px 7px;border-radius:6px;margin-left:6px;vertical-align:middle;letter-spacing:.5px;box-shadow:0 2px 8px rgba(245,158,11,.3)}
.normal-tag{display:inline-flex;align-items:center;background:var(--bg4);color:var(--text2);font-size:9px;font-weight:700;padding:2px 7px;border-radius:6px;margin-left:6px;vertical-align:middle;border:1px solid var(--border)}
.pay-amount{font-size:28px;font-weight:900;text-align:center;padding:18px 0 14px;background:linear-gradient(135deg,var(--green2),var(--cyan2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.pay-row{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid var(--border)}
.pay-row:last-of-type{border:none}
.pay-lbl{font-size:13px;color:var(--text2);font-weight:600}
.pay-val{font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
.cpbtn{background:none;border:none;color:var(--blue2);cursor:pointer;font-size:18px;padding:2px}
.pay-note{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:12px 14px;font-size:12px;color:var(--orange2);text-align:center;font-weight:700;margin-top:14px;line-height:1.5}
.pay-done-btn{width:100%;padding:15px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--green),var(--cyan));color:#fff;font-size:15px;font-weight:800;cursor:pointer;margin-top:14px;font-family:inherit;box-shadow:0 4px 20px rgba(16,185,129,.3);transition:transform .15s}
.pay-done-btn:active{transform:scale(.97)}

.confirm-box{padding-bottom:18px}.confirm-content{padding:8px 20px 18px;color:var(--text);font-size:14px;line-height:1.6;text-align:center}.confirm-content b{color:var(--brand2)}.confirm-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:0 16px}.confirm-btn{height:46px;border-radius:16px;border:none;font-size:14px;font-weight:900;cursor:pointer;transition:transform .18s var(--ease-spring),filter .18s}.confirm-btn:active{transform:scale(.97)}.confirm-btn.cancel{background:rgba(148,163,184,.16);color:var(--text);border:1px solid rgba(148,163,184,.22)}.confirm-btn.ok{background:linear-gradient(135deg,var(--brand),var(--brand2) 55%,var(--brand3));color:white;box-shadow:0 12px 30px rgba(220,38,38,.4),inset 0 1px 0 rgba(255,255,255,.18)}
.web-only{position:fixed;inset:0;z-index:9000;background:radial-gradient(circle at 18% 10%,rgba(59,130,246,.28),transparent 30%),radial-gradient(circle at 86% 12%,rgba(139,92,246,.24),transparent 32%),#070b14;display:none;align-items:center;justify-content:center;padding:22px;color:var(--text);overflow:hidden}.web-only.show{display:flex}.web-card{width:100%;max-width:420px;background:linear-gradient(160deg,rgba(17,24,39,.86),rgba(26,34,52,.76));border:1px solid rgba(96,165,250,.2);border-radius:30px;padding:28px 24px;text-align:center;box-shadow:0 24px 90px rgba(0,0,0,.48),inset 0 1px 0 rgba(255,255,255,.05);backdrop-filter:blur(18px);animation:floatIn .55s var(--ease-spring) both}.web-logo{width:76px;height:76px;margin:0 auto 16px;border-radius:24px;background:linear-gradient(135deg,var(--purple),var(--blue));display:flex;align-items:center;justify-content:center;font-size:34px;box-shadow:0 0 34px rgba(96,165,250,.35);animation:loadPop .55s cubic-bezier(.34,1.56,.64,1)}.web-title{font-size:22px;font-weight:950;letter-spacing:-.02em;margin-bottom:8px}.web-sub{font-size:13px;color:var(--text3);line-height:1.6;margin-bottom:18px}.web-btn{height:48px;border-radius:999px;background:linear-gradient(135deg,#1d9bf0,#38bdf8);color:#fff;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:9px;font-size:15px;font-weight:900;box-shadow:0 12px 34px rgba(29,155,240,.28);transition:transform .18s var(--ease-spring),filter .18s}.web-btn:active{transform:scale(.97);filter:brightness(1.08)}.web-hint{font-size:11px;color:var(--text2);margin-top:13px;line-height:1.5}.web-dots{display:flex;justify-content:center;gap:6px;margin:18px 0}.web-dots span{width:7px;height:7px;border-radius:50%;background:var(--blue2);opacity:.45;animation:dotPulse 1.2s ease-in-out infinite}.web-dots span:nth-child(2){animation-delay:.15s}.web-dots span:nth-child(3){animation-delay:.3s}@keyframes dotPulse{50%{transform:translateY(-5px);opacity:1}}

.pay-timer{margin:10px 0;padding:12px;border-radius:16px;background:rgba(255,184,77,.12);border:1px solid rgba(255,184,77,.35);text-align:center;font-weight:800;color:#ffcf7a;font-size:18px}
.pay-small-note{margin:8px 0;color:var(--text2);font-size:12px;line-height:1.45;text-align:center}
.pay-refresh-btn{width:100%;margin-top:10px;border:0;border-radius:16px;padding:12px 14px;background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;font-weight:800}

.vietqr-box{display:flex;justify-content:center;margin:12px 0 6px}.vietqr-img{width:min(220px,76vw);aspect-ratio:1/1;object-fit:contain;border-radius:16px;background:#fff;padding:10px;box-shadow:0 8px 28px rgba(0,0,0,.25)}

/* Bottom Tab Navigation */
.bottom-nav{position:fixed;bottom:18px;left:50%;transform:translateX(-50%);width:calc(100% - 24px);max-width:460px;background:rgba(15,23,42,.88);border:1px solid rgba(79,140,255,.12);backdrop-filter:blur(30px) saturate(1.8);-webkit-backdrop-filter:blur(30px) saturate(1.8);border-radius:24px;display:flex;z-index:100;padding:5px 4px;box-shadow:0 -4px 40px rgba(0,0,0,.5),0 4px 16px rgba(79,140,255,.06),inset 0 1px 0 rgba(255,255,255,.06)}
.nav-tab{flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 4px 7px;cursor:pointer;transition:all .25s var(--ease-spring);color:rgba(148,163,184,.7);position:relative;-webkit-tap-highlight-color:transparent;border-radius:16px;justify-content:center}
.nav-tab:active{transform:scale(.93)}
.nav-tab.active{color:#fff;background:rgba(79,140,255,.10)}
.nav-tab.active::before{content:"";position:absolute;top:0;left:50%;transform:translateX(-50%);width:20px;height:2px;background:linear-gradient(90deg,var(--cyan),var(--blue));border-radius:2px;opacity:.8}
.nav-tab svg{width:21px;height:21px;stroke:currentColor;stroke-width:1.8;fill:none;stroke-linecap:round;stroke-linejoin:round;transition:all .25s var(--ease-spring)}
.nav-tab.active svg{stroke-width:2.2;filter:drop-shadow(0 0 8px rgba(79,140,255,.4))}
.nav-lbl{font-size:9.5px;font-weight:700;letter-spacing:.2px;transition:all .2s var(--ease-spring)}
.nav-tab.active .nav-lbl{font-weight:900}
.tab-content{display:none}.tab-content.active{display:block;animation:floatIn .35s var(--ease-spring) both}
/* History tab */
.hist-order{margin:0 16px 12px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);padding:14px 16px;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:slideIn .38s var(--ease-spring) both}
.hist-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.hist-code{font-size:14px;font-weight:900;color:var(--text)}
.hist-badge{padding:4px 10px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px}
.hist-badge.pending{background:rgba(245,158,11,.12);color:var(--orange2);border:1px solid rgba(245,158,11,.35)}
.hist-badge.approved{background:rgba(52,211,153,.12);color:var(--green2);border:1px solid rgba(52,211,153,.35)}
.hist-badge.rejected{background:rgba(248,113,113,.12);color:var(--red2);border:1px solid rgba(248,113,113,.35)}
.hist-badge.cancelled{background:rgba(148,163,184,.12);color:#cbd5e1;border:1px solid rgba(148,163,184,.25)}
.hist-detail{display:flex;flex-direction:column;gap:5px;font-size:12px;color:var(--text2)}
.hist-detail span{display:flex;justify-content:space-between}
.hist-detail b{color:var(--text);font-weight:800}
.hist-amount{font-size:16px;font-weight:900;color:var(--cyan2);margin-top:6px;text-align:right}
.hist-empty-note{text-align:center;color:var(--text2);font-size:13px;margin-top:16px}
/* Profile tab */
.profile-card{margin:0 16px 14px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);padding:16px;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:floatIn .5s var(--ease-spring) .1s both}
.profile-card h3{font-size:13px;font-weight:900;color:var(--brand2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:10px}
.pc-ico{width:28px;height:28px;border-radius:9px;background:linear-gradient(135deg,rgba(239,68,68,.16),rgba(249,115,22,.08));border:1px solid var(--brand-border);display:flex;align-items:center;justify-content:center;color:var(--brand2);box-shadow:var(--glow-brand);flex-shrink:0}
.pc-ico svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;display:block}
.profile-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)}
.profile-row:last-child{border:none}
.profile-row .lbl{font-size:12px;color:var(--text2);font-weight:600}
.profile-row .val{font-size:14px;font-weight:800;color:var(--text)}
.profile-btn{width:100%;padding:12px;border-radius:var(--radius-md);border:1px solid var(--border);background:rgba(19,27,46,.8);color:var(--text);font-size:13px;font-weight:800;cursor:pointer;margin-top:8px;transition:all .15s var(--ease-spring);font-family:inherit;text-align:center}
.profile-btn:active{transform:scale(.97)}
.profile-btn.red{border-color:rgba(248,113,113,.3);color:var(--red2);background:rgba(248,113,113,.06)}
.profile-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.profile-stat{text-align:center;padding:14px;background:var(--bg3);border-radius:var(--radius-sm);border:1px solid var(--border)}
.profile-stat .num{font-size:24px;font-weight:900}
.profile-stat .num.blue{color:var(--blue2)}
.profile-stat .num.green{color:var(--green2)}
.profile-stat .num.orange{color:var(--orange2)}
.profile-stat .lbl{font-size:10px;color:var(--text2);margin-top:4px;font-weight:700;text-transform:uppercase}
/* Free Key Tab */
.free-card{margin:0 16px 14px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);padding:20px 16px;box-shadow:var(--card-shadow);backdrop-filter:blur(16px);animation:floatIn .5s var(--ease-spring) .1s both;text-align:center}
.free-icon{font-size:48px;margin-bottom:10px;animation:freeBounce 2s ease-in-out infinite}
@keyframes freeBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.free-title{font-size:18px;font-weight:900;color:var(--text);margin-bottom:6px}
.free-sub{font-size:13px;color:var(--text2);margin-bottom:16px;line-height:1.5}
.free-btn{width:100%;padding:14px;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,var(--green),var(--cyan));color:#fff;font-size:14px;font-weight:900;cursor:pointer;font-family:inherit;box-shadow:0 8px 24px rgba(16,185,129,.3);transition:all .2s var(--ease-spring)}
.free-btn:active{transform:scale(.96)}
.free-btn:disabled{opacity:.4;filter:grayscale(1);cursor:not-allowed;box-shadow:none}
.free-timer{margin:14px 0;padding:12px;border-radius:var(--radius-md);background:rgba(167,139,250,.08);border:1px solid rgba(167,139,250,.2);color:var(--purple2);font-size:14px;font-weight:800}
.free-claimed{margin:14px 0;padding:16px;border-radius:var(--radius-md);background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2)}
.free-claimed-code{font-size:16px;font-weight:900;color:var(--green2);font-family:'SF Mono','JetBrains Mono',monospace;margin:8px 0;padding:10px;background:rgba(0,0,0,.2);border-radius:8px;word-break:break-all}
.free-empty{margin:0 16px 14px;padding:40px 20px;background:var(--glass);border:1px solid var(--glass-border);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--card-shadow)}

</style>

<style>
.hclou-footer{margin:30px 18px 18px;padding:18px 0 12px;color:rgba(255,255,255,.74);font-size:11px;line-height:1.58;text-align:left;background:transparent;border:0;border-top:1px solid rgba(148,163,184,.16);box-shadow:none}
.hclou-footer-main{padding:0;display:grid;gap:18px}.hclou-footer h3{font-size:12px;font-weight:950;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.82);margin-bottom:10px}.hclou-footer h4{font-size:11px;font-weight:900;color:rgba(219,234,254,.72);margin-bottom:8px}.hf-list{display:grid;gap:7px}.hf-item{display:flex;gap:7px;align-items:flex-start;color:rgba(226,232,240,.56)}.hf-icon{width:17px;flex:0 0 17px;text-align:center;opacity:.62}.hf-hot{color:rgba(245,166,35,.86);font-weight:850;text-decoration:none}.hf-btn{display:inline-flex;margin-top:10px;padding:0;color:rgba(245,166,35,.82);background:transparent;border:0;text-decoration:none;font-weight:900}.hf-social-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px 10px}.hf-social{display:flex;align-items:center;gap:7px;color:rgba(255,255,255,.56);text-decoration:none;font-weight:750}.hf-badge-grid,.hf-pay-grid{display:flex;flex-wrap:wrap;gap:11px 14px;align-items:center}.hf-logo{display:inline-flex;align-items:center;justify-content:center;min-width:auto;height:auto;padding:0;background:transparent;border:0;border-radius:0;color:rgba(255,255,255,.48);font-size:16px;font-weight:950;letter-spacing:-.02em;opacity:.78;text-shadow:0 0 13px rgba(96,165,250,.10);filter:saturate(.92)}.hf-logo svg{width:auto;height:20px;display:block}.hf-logo.wide{width:auto}.hf-logo.gold{color:rgba(245,166,35,.78)}.hf-logo.blue{color:rgba(96,165,250,.78)}.hf-logo.green{color:rgba(52,211,153,.72)}.hf-logo.red{color:rgba(248,113,113,.72)}.hf-logo.purple{color:rgba(167,139,250,.76)}.hf-logo.pay-visa{font-style:italic;letter-spacing:.04em;color:rgba(96,165,250,.82)}.hf-logo.pay-master{font-size:19px;color:rgba(245,166,35,.82)}.hf-logo.pay-momo{font-size:13px;color:rgba(236,72,153,.78)}.hf-logo.cert-text{font-size:12px;letter-spacing:.02em}.hf-logo.cert-iso{font-size:11px;letter-spacing:.01em}.hf-logo.pay-bank{font-size:18px}.hclou-footer-bottom{margin-top:14px;padding:13px 0 0;border-top:1px solid rgba(148,163,184,.08);display:flex;align-items:center;justify-content:space-between;gap:10px;color:rgba(203,213,225,.42);font-size:10.5px}.hf-brand{font-weight:950;color:rgba(255,255,255,.58);letter-spacing:.1em}.hf-muted{opacity:.62}@media(max-width:520px){.hclou-footer{margin:28px 18px 14px;padding-top:16px}.hclou-footer-main{gap:17px}.hf-badge-grid,.hf-pay-grid{gap:10px 12px}.hf-social-grid{grid-template-columns:1fr 1fr}.hclou-footer-bottom{flex-direction:column;align-items:flex-start;gap:4px}}
</style>
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
      <div class="action-bar">
        <a class="ic-btn tg" href="https://t.me/hclouserver" target="_blank" rel="noopener" title="Nhóm Telegram" aria-label="Nhóm Telegram">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.94 4.16a1.35 1.35 0 0 0-1.43-.22L3.4 10.55c-.74.29-.72 1.35.03 1.61l4.33 1.49 1.67 5.05c.25.75 1.2.97 1.75.41l2.4-2.45 4.37 3.21c.72.53 1.75.13 1.93-.75l3.03-13.49c.12-.56-.1-1.14-.97-1.47ZM8.45 12.9l8.45-5.2c.2-.13.43.14.26.31l-6.92 6.62-.27 2.46-1.52-4.19Zm2.3 4.46.18-1.64 1.18.87-1.36.77Z"/></svg>
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

<script>
var API='./api/index.php',currentUser=null,selGame=null,selPkg=null,tgInitData='',appToken='';
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
    tongKey:'Tổng key',hoatDong:'Hoạt động',hetHan:'Hết hạn',biKhoa:'Bị khoá',tatCa:'Tất cả',soNgay:'Số ngày',conLai:'Còn lại',batDau:'Bắt đầu',ketThuc:'Kết thúc',dangTinh:'⏱ Đang tính...',conLaiLbl:'⏱ Còn lại: ',reset:'Reset',copy:'📋 Copy',giaHan:'Gia hạn',xoa:'Xóa',active:'✅ Hoạt động',expired:'⏰ Hết hạn',locked:'🔒 Bị khoá',pending:'⏳ Chờ thanh toán',hetHanLbl:'Hết hạn',chuaCoKey:'Chưa có key nào',goiNgay:'Gói ',ngay:' ngày',cheDoKey:'Chế độ ',keyMode:' key',khongCoGoi:'Không có gói nào',muaNgay:'Mua ngay',dangTaiGame:'Đang tải game...',chonGame:'Chọn game',nganHang:'Ngân hàng',soTK:'Số tài khoản',noiDungCK:'Nội dung CK',copyTK:'Đã copy số TK!',copyDon:'Đã copy mã đơn!',copyKey:'Đã copy key!',daCopy:'Đã copy!',luuY:'⚠️ Quét VietQR để tự điền đúng số tiền + nội dung. Nếu chuyển tay, bắt buộc ghi đúng nội dung bên trên.',daCK:'🔄 Kiểm tra thanh toán',choAdmin:'Đang kiểm tra thanh toán tự động...',giuManHinh:'Không thoát Mini App trong lúc thanh toán. Sau khi chuyển xong, key sẽ tự hiện trong mục Key của bạn.',hetGioTT:'Hết 15 phút chờ thanh toán. Nếu đã chuyển tiền, mở lại mục Key của bạn hoặc liên hệ admin kèm mã đơn.',daDuyetAuto:'Thanh toán đã xác nhận, key đã hoạt động!',resetOk:'Reset thành công!',xoaOk:'Đã xóa key!',confirmReset:'Reset thiết bị cho key này?',confirmXoa:'Xóa key này?',loiKetNoi:'Lỗi kết nối',moQuaBot:'Vui lòng mở qua bot Telegram!',giaHanMsg:'Chọn gói mới ở phần Mua Key để gia hạn!',chonGameTruoc:'Vui lòng chọn game trước',chuaChonGoi:'Chưa chọn gói',dangTai:'Đang tải...',loiTaoDon:'Lỗi tạo đơn!',copyFail:'Copy thất bại',taiKeyLoi:'Không tải được key. Hãy đóng Mini App và mở lại từ bot Telegram.',getFree:'Get Key Free',dangLayLink:'Đang lấy link...',freeHet:'Chưa có key free khả dụng',mienPhi:'Miễn phí',vuotLinkNhan:'Vượt link để nhận key',xacNhan:'Xác nhận',xacNhanMua:'Bạn đã chọn',capDo:'cấp độ',keyMotGame:'key chỉ được sử dụng cho một game. Bạn có muốn tiếp tục tạo đơn không?',huy:'Huỷ',dongY:'Đồng Ý',expiredDeleteNote:'Không gia hạn sau 3 ngày sẽ tự xoá',tuXoaLuc:'Tự xoá lúc',helpSub:'Chọn câu hỏi để xem hướng dẫn nhanh',pendingPayTitle:'Bạn còn đơn chờ thanh toán',pendingPaySub:'Nếu lỡ thoát trước khi chụp QR, bấm để mở lại thông tin thanh toán.',resumePay:'Mở lại QR thanh toán',pendingPayExpired:'Đơn đã quá 15 phút. Nếu đã chuyển tiền, liên hệ admin kèm mã đơn.',copyTelegramId:'Đã copy Telegram ID!',freeClaimOk:'Nhận key free thành công',freeClaimFail:'Không nhận được key free',
    confirmTitle:'Xác nhận',freeDayTitle:'⭐ Key miễn phí mỗi ngày',freeChecking:'Đang kiểm tra key...',historyTitle:'📜 Lịch sử đơn hàng',loadingKeys:'Đang tải keys...',histEmpty:'Chưa có đơn hàng nào',histLoading:'Đang tải lịch sử...',histOrderCode:'Mã đơn',histAmount:'Số tiền',histStatus:'Trạng thái',histDate:'Ngày tạo',histPending:'Chờ duyệt',histApproved:'Đã duyệt',histRejected:'Đã huỷ',
    profileOverview:'Tổng quan',profileAccount:'Thông tin tài khoản',profileLinks:'Liên kết nhanh',profileTotalOrders:'Tổng đơn',profileApproved:'Đã duyệt',profilePending:'Chờ xử lý',profileKeysOwned:'Key đang có',profileTgId:'Telegram ID',profileUsername:'Username',profileFullName:'Họ tên',profileJoined:'Ngày tham gia',profileBtnBuy:'🔑 Mua Key',profileBtnHistory:'📜 Lịch sử đơn hàng',profileBtnReload:'🔄 Tải lại',
    navBuy:'Mua Key',navFree:'Key Free',navHistory:'Lịch sử',navProfile:'Cá nhân',
    footerSupport:'Liên hệ hỗ trợ',footerPayment:'Phương thức thanh toán',footerCert:'Chứng nhận',footerCopyright:'Copyright © 2026 HCLOU Server. All Rights Reserved.',footerAddress:'Địa chỉ',footerTaxId:'Mã số thuế',footerBizLic:'Số GPKD',footerUpdating:'Đang cập nhật',footerHotline:'Hotline',footerComplaint:'Phản ánh chất lượng',footerEmail:'Email liên hệ',footerRespContent:'Chịu trách nhiệm nội dung',footerFollowTg:'Theo dõi trên Telegram',footerSocial:'Kết nối mạng xã hội',footerCertSite:'Chứng chỉ trang web',footerPaymentSupport:'Hỗ trợ thanh toán',
    freeChecking2:'Đang kiểm tra...',freeClaimedToday:'Bạn đã nhận key free hôm nay!',freeReceivedAt:'Nhận lúc',freeBackTomorrow:'🔄 Quay lại vào 0h00 ngày mai để nhận key tiếp',freeKeyToday:'Key miễn phí hôm nay',freeSub:'Nhận ngay 1 key free mỗi ngày!<br>Admin đã thêm key sẵn, mỗi người nhận 1 link riêng',freeBtnGetLink:'🔗 Lấy Link Claim Key',freeResetDaily:'⏰ Reset lúc 0h00 hàng ngày',freePeopleSuffix:'người đã nhận hôm nay',freeNoneToday:'Chưa có key free hôm nay',freeAdminNotYet:'Admin chưa thêm key. Vui lòng quay lại sau!',freeNewMorning:'🔄 Key mới sẽ có vào buổi sáng hàng ngày',freeCannotLoad:'Không thể tải thông tin key free',freeCreatingLink:'⏳ Đang tạo link...',freeAlready:'Bạn đã nhận key free hôm nay rồi!',freeYourLink:'Link claim riêng của bạn:',freeOpenLink:'🔓 Mở Link Claim Key',freeCopyLink:'📋 Copy Link',freeCopiedLink:'Đã copy link!',freeLinkCreated:'✅ Đã tạo link — Mở link để nhận key',freeErrorGeneric:'Có lỗi xảy ra!',toastLoadErr:'Lỗi kết nối, thử lại sau!',loadingHistory:'Đang tải lịch sử...',histLoadFail:'⚠️ Không thể tải lịch sử đơn hàng',histGame:'Game',histPkg:'Gói',histCreated:'Ngày tạo',histPkgDays:'ngày',statusApproved:'Đã duyệt',statusRejected:'Đã từ chối',statusCancelled:'Đã huỷ',statusPending:'Chờ xử lý'},
  en:{webTitle:'Open HCLOU in Telegram',webSub:'This web app only works inside Telegram Mini App to verify your account and protect your keys.',openTelegram:'🚀 Open in Telegram',tapChooseGame:'Tap to choose game',noGameSelected:'No game selected',noPackageSelected:'No package selected',promo:'🔥 HOT PROMO!',totalKey:'Total keys',activeLabel:'Active',expiredLabel:'Expired',buyNew:'Buy new key',buySub:'Choose app and duration package',chooseApp:'Choose app',choosePackage:'Choose package',buyNow:'Buy now',yourKeys:'Your keys',all:'All',lockedLabel:'Locked',chooseGameTitle:'🎮 Choose game',paymentTitle:'💳 Payment',search:'Search GKey...',
    tongKey:'Total keys',hoatDong:'Active',hetHan:'Expired',biKhoa:'Locked',tatCa:'All',soNgay:'Days',conLai:'Remaining',batDau:'Start',ketThuc:'End',dangTinh:'⏱ Calculating...',conLaiLbl:'⏱ Remaining: ',reset:'Reset',copy:'📋 Copy',giaHan:'Renew',xoa:'Delete',active:'✅ Active',expired:'⏰ Expired',locked:'🔒 Locked',pending:'⏳ Waiting payment',hetHanLbl:'Expired',chuaCoKey:'No keys yet',goiNgay:'Package ',ngay:' days',cheDoKey:'Mode ',keyMode:' key',khongCoGoi:'No packages available',muaNgay:'Buy now',dangTaiGame:'Loading games...',chonGame:'Choose game',nganHang:'Bank',soTK:'Account number',noiDungCK:'Transfer note',copyTK:'Account copied!',copyDon:'Order code copied!',copyKey:'Key copied!',daCopy:'Copied!',luuY:'⚠️ Scan VietQR to auto-fill amount + content. If transferring manually, enter the exact content above.',daCK:'🔄 Check payment',choAdmin:'Checking payment automatically...',giuManHinh:'Do not close the Mini App while paying. After payment, your key will appear automatically in Your keys.',hetGioTT:'15-minute payment wait ended. If you already paid, reopen Your keys or contact admin with the order code.',daDuyetAuto:'Payment confirmed, key is active!',resetOk:'Reset successfully!',xoaOk:'Key deleted!',confirmReset:'Reset device for this key?',confirmXoa:'Delete this key?',loiKetNoi:'Connection error',moQuaBot:'Please open via Telegram bot!',giaHanMsg:'Choose a new package in Buy Key to renew!',chonGameTruoc:'Please choose a game first',chuaChonGoi:'No package selected',dangTai:'Loading...',loiTaoDon:'Create order failed!',copyFail:'Copy failed',taiKeyLoi:'Cannot load keys. Please close the Mini App and open it again from Telegram bot.',getFree:'Get Key Free',dangLayLink:'Getting link...',freeHet:'No free key available',mienPhi:'Free',vuotLinkNhan:'Complete link to claim key',xacNhan:'Confirm',xacNhanMua:'You selected',capDo:'level',keyMotGame:'this key can only be used for one game. Do you want to continue?',huy:'Cancel',dongY:'Agree',expiredDeleteNote:'If not renewed, this key will be auto-deleted after 3 days',tuXoaLuc:'Auto delete at',helpSub:'Choose a question for quick help',pendingPayTitle:'You have a pending payment',pendingPaySub:'If you closed before saving the QR, tap to reopen payment details.',resumePay:'Reopen payment QR',pendingPayExpired:'This order is older than 15 minutes. If you already paid, contact admin with the order code.',copyTelegramId:'Telegram ID copied!',freeClaimOk:'Free key claimed successfully',freeClaimFail:'Cannot claim free key',
    confirmTitle:'Confirm',freeDayTitle:'⭐ Daily free key',freeChecking:'Checking key...',historyTitle:'📜 Order history',loadingKeys:'Loading keys...',histEmpty:'No orders yet',histLoading:'Loading history...',histOrderCode:'Order code',histAmount:'Amount',histStatus:'Status',histDate:'Created',histPending:'Pending',histApproved:'Approved',histRejected:'Cancelled',
    profileOverview:'Overview',profileAccount:'Account info',profileLinks:'Quick links',profileTotalOrders:'Total orders',profileApproved:'Approved',profilePending:'Pending',profileKeysOwned:'Keys owned',profileTgId:'Telegram ID',profileUsername:'Username',profileFullName:'Full name',profileJoined:'Joined',profileBtnBuy:'🔑 Buy Key',profileBtnHistory:'📜 Order history',profileBtnReload:'🔄 Reload',
    navBuy:'Buy Key',navFree:'Free Key',navHistory:'History',navProfile:'Profile',
    footerSupport:'Contact support',footerPayment:'Payment methods',footerCert:'Certifications',footerCopyright:'Copyright © 2026 HCLOU Server. All Rights Reserved.',footerAddress:'Address',footerTaxId:'Tax ID',footerBizLic:'Business license',footerUpdating:'Updating',footerHotline:'Hotline',footerComplaint:'Quality complaints',footerEmail:'Contact email',footerRespContent:'Content responsibility',footerFollowTg:'Follow on Telegram',footerSocial:'Social networks',footerCertSite:'Website certifications',footerPaymentSupport:'Payment support',
    freeChecking2:'Checking...',freeClaimedToday:'You already claimed today\'s free key!',freeReceivedAt:'Received at',freeBackTomorrow:'🔄 Come back at 00:00 tomorrow for a new key',freeKeyToday:'Today\'s free key',freeSub:'Get 1 free key per day!<br>Admin pre-added keys, each user gets a unique link',freeBtnGetLink:'🔗 Get Claim Link',freeResetDaily:'⏰ Resets at 00:00 daily',freePeopleSuffix:'people claimed today',freeNoneToday:'No free key today',freeAdminNotYet:'Admin has not added a key yet. Please check back later!',freeNewMorning:'🔄 A new key will be available each morning',freeCannotLoad:'Cannot load free key info',freeCreatingLink:'⏳ Creating link...',freeAlready:'You already claimed today\'s free key!',freeYourLink:'Your personal claim link:',freeOpenLink:'🔓 Open Claim Link',freeCopyLink:'📋 Copy Link',freeCopiedLink:'Link copied!',freeLinkCreated:'✅ Link created — open it to claim key',freeErrorGeneric:'Something went wrong!',toastLoadErr:'Connection error, please retry!',loadingHistory:'Loading history...',histLoadFail:'⚠️ Cannot load order history',histGame:'Game',histPkg:'Package',histCreated:'Created',histPkgDays:'days',statusApproved:'Approved',statusRejected:'Rejected',statusCancelled:'Cancelled',statusPending:'Pending'},
  es:{webTitle:'Abrir HCLOU en Telegram',webSub:'Esta web solo funciona dentro de Telegram Mini App para verificar tu cuenta y proteger tus claves.',openTelegram:'🚀 Abrir en Telegram',tapChooseGame:'Toca para elegir juego',noGameSelected:'Sin juego seleccionado',noPackageSelected:'Sin paquete seleccionado',promo:'🔥 ¡PROMO CALIENTE!',totalKey:'Total de claves',activeLabel:'Activa',expiredLabel:'Expirada',buyNew:'Comprar clave',buySub:'Elige aplicación y paquete',chooseApp:'Elegir aplicación',choosePackage:'Elegir paquete',buyNow:'Comprar ahora',yourKeys:'Tus claves',all:'Todas',lockedLabel:'Bloqueada',chooseGameTitle:'🎮 Elegir juego',paymentTitle:'💳 Pago',search:'Buscar GKey...',
    tongKey:'Total de claves',hoatDong:'Activa',hetHan:'Expirada',biKhoa:'Bloqueada',tatCa:'Todas',soNgay:'Días',conLai:'Restante',batDau:'Inicio',ketThuc:'Fin',dangTinh:'⏱ Calculando...',conLaiLbl:'⏱ Restante: ',reset:'Restablecer',copy:'📋 Copiar',giaHan:'Renovar',xoa:'Eliminar',active:'✅ Activa',expired:'⏰ Expirada',locked:'🔒 Bloqueada',pending:'⏳ Esperando pago',hetHanLbl:'Expirada',chuaCoKey:'Sin claves aún',goiNgay:'Paquete ',ngay:' días',cheDoKey:'Modo ',keyMode:' clave',khongCoGoi:'No hay paquetes disponibles',muaNgay:'Comprar ahora',dangTaiGame:'Cargando juegos...',chonGame:'Elegir juego',nganHang:'Banco',soTK:'Número de cuenta',noiDungCK:'Nota de transferencia',copyTK:'¡Cuenta copiada!',copyDon:'¡Código de orden copiado!',copyKey:'¡Clave copiada!',daCopy:'¡Copiado!',luuY:'⚠️ Escanea VietQR para auto-completar el monto y la nota. Si transfieres manualmente, incluye exactamente la nota anterior.',daCK:'🔄 Verificar pago',choAdmin:'Verificando pago automáticamente...',giuManHinh:'No cierres la Mini App mientras pagas. Después del pago, tu clave aparecerá automáticamente en Tus claves.',hetGioTT:'Terminaron los 15 minutos de espera. Si ya pagaste, abre Tus claves o contacta al admin con el código de orden.',daDuyetAuto:'¡Pago confirmado, la clave está activa!',resetOk:'¡Restablecido con éxito!',xoaOk:'¡Clave eliminada!',confirmReset:'¿Restablecer dispositivo para esta clave?',confirmXoa:'¿Eliminar esta clave?',loiKetNoi:'Error de conexión',moQuaBot:'¡Por favor abre vía el bot de Telegram!',giaHanMsg:'¡Elige un paquete nuevo en Comprar Clave para renovar!',chonGameTruoc:'Por favor elige un juego primero',chuaChonGoi:'Sin paquete seleccionado',dangTai:'Cargando...',loiTaoDon:'¡Error al crear la orden!',copyFail:'Error al copiar',taiKeyLoi:'No se pueden cargar las claves. Cierra la Mini App y reábrela desde el bot de Telegram.',getFree:'Obtener Clave Gratis',dangLayLink:'Obteniendo enlace...',freeHet:'Sin claves gratis disponibles',mienPhi:'Gratis',vuotLinkNhan:'Completa el enlace para reclamar la clave',xacNhan:'Confirmar',xacNhanMua:'Has seleccionado',capDo:'nivel',keyMotGame:'esta clave solo puede usarse para un juego. ¿Deseas continuar?',huy:'Cancelar',dongY:'Aceptar',expiredDeleteNote:'Si no se renueva, esta clave se eliminará automáticamente después de 3 días',tuXoaLuc:'Auto-eliminar a las',helpSub:'Elige una pregunta para ayuda rápida',pendingPayTitle:'Tienes un pago pendiente',pendingPaySub:'Si cerraste antes de guardar el QR, toca para reabrir los detalles de pago.',resumePay:'Reabrir QR de pago',pendingPayExpired:'Esta orden tiene más de 15 minutos. Si ya pagaste, contacta al admin con el código.',copyTelegramId:'¡Telegram ID copiado!',freeClaimOk:'Clave gratis reclamada con éxito',freeClaimFail:'No se pudo reclamar la clave gratis',
    confirmTitle:'Confirmar',freeDayTitle:'⭐ Clave gratis diaria',freeChecking:'Verificando clave...',historyTitle:'📜 Historial de órdenes',loadingKeys:'Cargando claves...',histEmpty:'Aún no hay órdenes',histLoading:'Cargando historial...',histOrderCode:'Código',histAmount:'Monto',histStatus:'Estado',histDate:'Creado',histPending:'Pendiente',histApproved:'Aprobado',histRejected:'Cancelado',
    profileOverview:'Resumen',profileAccount:'Datos de cuenta',profileLinks:'Enlaces rápidos',profileTotalOrders:'Total órdenes',profileApproved:'Aprobadas',profilePending:'Pendientes',profileKeysOwned:'Claves activas',profileTgId:'Telegram ID',profileUsername:'Usuario',profileFullName:'Nombre completo',profileJoined:'Se unió',profileBtnBuy:'🔑 Comprar Clave',profileBtnHistory:'📜 Historial',profileBtnReload:'🔄 Recargar',
    navBuy:'Comprar',navFree:'Gratis',navHistory:'Historial',navProfile:'Perfil',
    footerSupport:'Soporte',footerPayment:'Métodos de pago',footerCert:'Certificaciones',footerCopyright:'Copyright © 2026 HCLOU Server. Todos los derechos reservados.',footerAddress:'Dirección',footerTaxId:'NIF',footerBizLic:'Licencia comercial',footerUpdating:'Actualizando',footerHotline:'Línea directa',footerComplaint:'Reclamos de calidad',footerEmail:'Email de contacto',footerRespContent:'Responsable del contenido',footerFollowTg:'Síguenos en Telegram',footerSocial:'Redes sociales',footerCertSite:'Certificaciones del sitio',footerPaymentSupport:'Soporte de pago',
    freeChecking2:'Verificando...',freeClaimedToday:'¡Ya reclamaste la clave gratis de hoy!',freeReceivedAt:'Recibida a las',freeBackTomorrow:'🔄 Vuelve a las 00:00 mañana para otra clave',freeKeyToday:'Clave gratis de hoy',freeSub:'¡Obtén 1 clave gratis por día!<br>El admin ya añadió claves, cada usuario tiene un enlace único',freeBtnGetLink:'🔗 Obtener Enlace',freeResetDaily:'⏰ Se reinicia a las 00:00 diariamente',freePeopleSuffix:'personas reclamaron hoy',freeNoneToday:'Sin clave gratis hoy',freeAdminNotYet:'El admin aún no añadió clave. ¡Vuelve más tarde!',freeNewMorning:'🔄 Una clave nueva estará disponible cada mañana',freeCannotLoad:'No se puede cargar la información',freeCreatingLink:'⏳ Creando enlace...',freeAlready:'¡Ya reclamaste la clave gratis de hoy!',freeYourLink:'Tu enlace personal de reclamo:',freeOpenLink:'🔓 Abrir Enlace',freeCopyLink:'📋 Copiar Enlace',freeCopiedLink:'¡Enlace copiado!',freeLinkCreated:'✅ Enlace creado — ábrelo para reclamar la clave',freeErrorGeneric:'¡Algo salió mal!',toastLoadErr:'Error de conexión, ¡reintenta!',loadingHistory:'Cargando historial...',histLoadFail:'⚠️ No se puede cargar el historial',histGame:'Juego',histPkg:'Paquete',histCreated:'Creado',histPkgDays:'días',statusApproved:'Aprobado',statusRejected:'Rechazado',statusCancelled:'Cancelado',statusPending:'Pendiente'}
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


var APP_VERSION='payauto20260428_1';
var pendingClaimToken=new URLSearchParams(location.search).get('claim')||'';
var BOT_USERNAME='<?= BOT_USERNAME ?>';
var TG_OPEN_URL='https://t.me/'+BOT_USERNAME+'?start=webapp';
window.onload=function(){ setTimeout(tryInit,100); };
function showTelegramOnly(){
  document.getElementById('loadingScreen').classList.add('hide');
  document.getElementById('app').style.display='none';
  var w=document.getElementById('webOnly');
  var b=document.getElementById('openTelegramBtn');
  if(b)b.href=TG_OPEN_URL;
  w.classList.add('show');
  setTimeout(function(){ try{ window.location.href=TG_OPEN_URL; }catch(e){} },1200);
}
function tryInit(n){
  n=n||0;
  var tg=window.Telegram&&window.Telegram.WebApp;
  if(tg&&tg.initDataUnsafe&&tg.initDataUnsafe.user&&tg.initDataUnsafe.user.id){
    startApp(tg);
  } else if(n<8){
    setTimeout(function(){ tryInit(n+1); },250);
  } else {
    showTelegramOnly();
  }
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
    document.getElementById('telegramIdText').textContent=currentUser.telegram_id;
    var init=n.split(' ').map(function(w){return w[0]||'';}).join('').slice(0,2).toUpperCase();
    if(currentUser.avatar_url){
      document.getElementById('avatarEl').innerHTML='<img src="'+escapeHtml(safeUrl(currentUser.avatar_url))+'" alt="">';
    } else {
      document.getElementById('avatarInit').textContent=init;
    }
    loadKeys('all');
    loadPendingPayments();
    processPendingClaim();
  } else {
    // Fallback: Telegram đã có user nhưng /auth lỗi thì vẫn thử tải key bằng telegram_id.
    currentUser={telegram_id:u.id,telegram_username:u.username||'',full_name:((u.first_name||'')+' '+(u.last_name||'')).trim(),avatar_url:u.photo_url||''};
    document.getElementById('telegramIdText').textContent=currentUser.telegram_id;
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
    setTimeout(function(){ ls.style.display='none'; },400);
  },1200);
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
  try{ var r=await fetch(url,opts); return r.json(); }
  catch(e){ return {error:T.loiKetNoi}; }
}

async function openGameModal(){
  document.getElementById('gameModal').classList.add('show');
  if(gCache.length===0){
    var res=await api('games');
    if(!res.success)return;
    gCache=res.games;
  }
  buildGameList();
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
  var html='';
  pCache.forEach(function(p){
    if(p.is_free){
      var sel=(selPkg&&selPkg.id==='free')?' on':'';
      html+='<div class="pkg-row free'+sel+'" onclick="pickPkg(\'free\',this)">'
        +'<div><div class="pkg-days">🎁 '+escapeHtml(p.name)+'</div>'
        +'<div class="pkg-mode">'+T.goiNgay+(parseInt(p.days,10)||0)+T.ngay+' · '+T.vuotLinkNhan+'</div></div>'
        +'<div class="pkg-cost">'+T.mienPhi+'</div></div>';
      return;
    }
    var sel=(selPkg&&selPkg.id==p.id)?' on':'';
    html+='<div class="pkg-row'+sel+'" onclick="pickPkg('+(parseInt(p.id,10)||0)+',this)">'
      +'<div><div class="pkg-days">'+T.goiNgay+(parseInt(p.days,10)||0)+T.ngay+'</div>'
      +'<div class="pkg-mode">'+T.cheDoKey+escapeHtml(p.key_type)+T.keyMode+'</div></div>'
      +'<div class="pkg-cost">'+fmtMoney(p.price)+'\u0111</div></div>';
  });
  document.getElementById('pkgList').innerHTML=html;
  initMotion();
}
function pickPkg(pid,el){
  pCache.forEach(function(p){ if(p.id==pid) selPkg=p; });
  if(!selPkg)return;
  document.querySelectorAll('.pkg-row').forEach(function(e){ e.classList.remove('on'); });
  el.classList.add('on'); updBuyBtn();
}
function updPlayBtn(){
  var btn=document.getElementById('playBtn');
  if(!btn)return;
  if(selGame&&selGame.package_name){ btn.classList.remove('disabled'); }
  else { btn.classList.add('disabled'); }
}
function openPlayLink(){
  if(!selGame||!selGame.package_name){ toast(T.chonGameTruoc,'error'); return; }
  window.open(PLAY_BASE+encodeURIComponent(selGame.package_name),'_blank');
}

function updBuyBtn(){
  var btn=document.getElementById('buyBtn'),sub=document.getElementById('buySub');
  if(selGame&&selPkg){
    btn.classList.add('go');
    if(selPkg.is_free){ sub.textContent='Get Key Free | '+T.mienPhi; }
    else sub.textContent=selPkg.days+T.ngay+' | '+fmtMoney(selPkg.price)+'\u0111';
  } else {
    btn.classList.remove('go');
    sub.textContent=T.noPackageSelected;
  }
}

var payCheckTimer=null, payCountdownTimer=null, currentPayOrder='';
var paySecondsLeft=0;
var buying=false;
function doOrder(){
  if(!selGame||!selPkg||buying)return;
  if(selPkg.is_free){ getFreeKey(); return; }
  showOrderConfirm();
}
function showOrderConfirm(){
  document.querySelector('#confirmModal .mtitle').textContent=T.xacNhan;
  document.querySelector('#confirmModal .confirm-btn.cancel').textContent=T.huy;
  document.querySelector('#confirmModal .confirm-btn.ok').textContent=T.dongY;
  var pkgName=(selGame&&selGame.package_name)||'';
  var level=(selPkg&&selPkg.key_type)||'';
  document.getElementById('confirmContent').innerHTML=T.xacNhanMua+' <b>"'+escapeHtml(pkgName)+'"</b> '+T.capDo+' <b>"'+escapeHtml(level)+'"</b>, '+T.keyMotGame;
  document.getElementById('confirmModal').classList.add('show');
}
function cancelOrderConfirm(){ closeModal('confirmModal'); }
async function confirmCreateOrder(){
  if(!selGame||!selPkg||buying)return;
  closeModal('confirmModal');
  buying=true;
  var btn=document.getElementById('buyBtn');
  btn.innerHTML='<div class="spin" style="width:20px;height:20px;border-width:2px;margin:0"></div>';
  btn.classList.remove('go');
  var res=await api('create_order','POST',{game_id:selGame.id,package_id:selPkg.id});
  buying=false;
  btn.classList.add('go');
  btn.innerHTML='<span>'+T.muaNgay+'</span><span class="buy-sub">'+selPkg.days+T.ngay+' | '+fmtMoney(selPkg.price)+'đ</span>';
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
  var qrUrl=safeUrl(d.vietqr_url);
  var qr=qrUrl?'<div class="vietqr-box"><img class="vietqr-img" src="'+escapeHtml(qrUrl)+'" alt="VietQR"></div>':'';
  document.getElementById('payContent').innerHTML=
    '<div class="pay-amount">'+fmtMoney(d.amount)+'\u0111</div>'
    +qr
    +'<div class="pay-timer" id="payTimer">05:00</div>'
    +'<div class="pay-small-note">'+(T.giuManHinh||'Không thoát Mini App trong lúc thanh toán.')+'</div>'
    +'<div class="pay-row"><span class="pay-lbl">'+T.nganHang+'</span><span class="pay-val">'+escapeHtml(d.bank_name)+'</span></div>'
    +'<div class="pay-row"><span class="pay-lbl">'+T.soTK+'</span><span class="pay-val">'+escapeHtml(d.bank_account)
    +' <button class="cpbtn" onclick="copyText('+jsAttr(d.bank_account)+',T.copyTK)">\uD83D\uDCCB</button></span></div>'
    +'<div class="pay-row"><span class="pay-lbl">Chủ tài khoản</span><span class="pay-val">'+escapeHtml(d.bank_owner||'')+'</span></div>'
    +'<div class="pay-row"><span class="pay-lbl">'+T.noiDungCK+'</span><span class="pay-val"><b>'+escapeHtml(d.order_code)
    +'</b> <button class="cpbtn" onclick="copyText('+jsAttr(d.order_code)+',T.copyDon)">\uD83D\uDCCB</button></span></div>'
    +'<div class="pay-note">'+T.luuY+'</div>'
    +'<button class="pay-refresh-btn" onclick="donePay()">'+T.daCK+'</button>';
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
    await loadKeys('all');
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
  showPay({order_code:o.order_code,amount:o.amount,bank_account:o.bank_account,bank_name:o.bank_name,bank_owner:o.bank_owner,transfer_content:o.transfer_content,vietqr_url:o.vietqr_url});
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
  var el=document.getElementById(id),cur=0,step=Math.ceil((val||1)/20);
  var t=setInterval(function(){ cur+=step; if(cur>=val){cur=val;clearInterval(t);} el.textContent=cur; },40);
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
    var start=k.start_at?fmtDate(k.start_at):'--';
    var exp=k.expire_at?fmtDateFull(k.expire_at):'--';
    var typeTag=k.key_type==='VIP'?'<span class="vip-tag">VIP</span>':'<span class="normal-tag">Normal</span>';
    html+='<div class="kcard is-'+escapeHtml(k.status)+'" id="kc-'+(parseInt(k.id,10)||0)+'" style="animation-delay:'+i*.05+'s">'
      +'<div class="ktop"><div class="kcode-row">'
      +'<div class="kcode">'+escapeHtml(k.key_code)+'</div>'
      +'<div class="kbadge '+cls+'">'+lbl+'</div></div>'
      +'<div class="kgame">'+escapeHtml(k.package_name)+typeTag+'</div></div>'
      +'<div class="kgrid">'
      +'<div class="kbox"><div class="kbox-lbl">'+T.soNgay+'</div><div class="kbox-val">'+(parseInt(k.days,10)||0)+T.ngay+'</div></div>'
      +'<div class="kbox"><div class="kbox-lbl">'+T.conLai+'</div><div class="kbox-val" id="rem-'+(parseInt(k.id,10)||0)+'">'+calcRem(k)+'</div></div>'
      +'<div class="kbox"><div class="kbox-lbl">'+T.batDau+'</div><div class="kbox-val">'+escapeHtml(start)+'</div></div>'
      +'<div class="kbox"><div class="kbox-lbl">'+T.ketThuc+'</div><div class="kbox-val">'+escapeHtml(exp)+'</div></div>'
      +'</div>';
    if(k.status==='active'){
      html+='<div class="cdwrap"><div class="cdbar-bg"><div class="cdbar" id="cbar-'+(parseInt(k.id,10)||0)+'" style="width:100%"></div></div>'
        +'<div class="cdtxt" id="ctxt-'+(parseInt(k.id,10)||0)+'">'+T.dangTinh+'</div></div>';
    }
    if(k.status==='expired'){
      html+='<div class="knote">⚠️ '+T.expiredDeleteNote+(k.delete_at?' · '+T.tuXoaLuc+': '+escapeHtml(fmtDateFull(k.delete_at)):'')+'</div>';
    }
    html+='<div class="kactions">';
    if(k.status==='active') html+='<button class="ksm blue" onclick="doReset('+(parseInt(k.id,10)||0)+')">\uD83D\uDD04 '+T.reset+' ('+((k.max_reset||3)-(k.reset_count||0))+')</button>';
    html+='<button class="ksm" onclick="copyText('+jsAttr(k.key_code)+',T.copyKey)">\uD83D\uDCCB Copy</button>';
    if(k.status==='active') html+='<button class="ksm green" onclick="toast(T.giaHanMsg,\'info\')">\u23F0 '+T.giaHan+'</button>';
    if(k.status!=='active') html+='<button class="ksm red" onclick="doDelete('+(parseInt(k.id,10)||0)+')">\uD83D\uDDD1 '+T.xoa+'</button>';
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
  else toast(res.error||'L\u1ED7i!','error');
}
async function doDelete(id){
  if(!confirm(T.confirmXoa))return;
  var res=await api('delete_key','POST',{key_id:id});
  if(res.success){toast(T.xoaOk,'success');loadKeys(curFilter);}
  else toast(res.error||'L\u1ED7i!','error');
}

function fmtMoney(n){return Number(n).toLocaleString('vi-VN');}
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

// UI motion helpers: press feedback + subtle scroll parallax
function initMotion(){
  document.querySelectorAll('.game-btn,.pkg-row,.mgame,.ic-btn,.buy-btn.go,.ftab,.ksm,.bank-chip').forEach(function(el){
    if(el.dataset.motion)return; el.dataset.motion='1'; el.classList.add('pressable');
    ['touchstart','mousedown'].forEach(function(ev){el.addEventListener(ev,function(){el.classList.add('touching');},{passive:true});});
    ['touchend','touchcancel','mouseup','mouseleave'].forEach(function(ev){el.addEventListener(ev,function(){el.classList.remove('touching');},{passive:true});});
  });
}

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
      var claimedInfo = res.total_claimed_today ? ' · <b style="color:var(--cyan2)">'+(parseInt(res.total_claimed_today,10)||0)+' '+escapeHtml(T.freePeopleSuffix)+'</b>' : '';
      wrap.innerHTML = '<div class="free-card">'
        +'<div class="free-icon">🎁</div>'
        +'<div class="free-title">'+escapeHtml(T.freeKeyToday)+'</div>'
        +'<div class="free-sub">'+T.freeSub+'</div>'
        +'<button class="free-btn" id="claimFreeBtn" onclick="claimDailyFree()">'+escapeHtml(T.freeBtnGetLink)+'</button>'
        +'<div class="free-timer" style="margin-top:12px">'+escapeHtml(T.freeResetDaily)+claimedInfo+'</div>'
        +'<div id="claimLinkArea" style="margin-top:12px;display:none"></div>'
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
  if(btn){ btn.disabled=true; btn.textContent=T.freeCreatingLink; }
  try {
    var res = await api('daily_free_key','POST',{});
    if(res.success){
      if(res.already){
        toast(T.freeAlready,'info');
        freeKeyLoaded = false;
        loadFreeKey();
      } else if(res.claim_url){
        // Hiện link claim riêng cho user
        var area = document.getElementById('claimLinkArea');
        if(area){
          area.style.display = 'block';
          var claimUrl = safeUrl(res.claim_url);
          area.innerHTML = '<div class="free-claimed">'
            +'<div style="font-size:12px;color:var(--text2);margin-bottom:8px">'+escapeHtml(T.freeYourLink)+'</div>'
            +'<div class="free-claimed-code" style="font-size:12px;word-break:break-all">'+escapeHtml(claimUrl)+'</div>'
            +'<button class="free-btn" style="margin-top:10px;background:linear-gradient(135deg,var(--blue),var(--purple))" onclick="window.open('+jsAttr(claimUrl)+',\'_blank\')">'+escapeHtml(T.freeOpenLink)+'</button>'
            +'<button class="free-btn" style="margin-top:8px;background:transparent;border:1px solid var(--border);color:var(--text2)" onclick="copyText('+jsAttr(claimUrl)+','+jsAttr(T.freeCopiedLink)+')">'+escapeHtml(T.freeCopyLink)+'</button>'
            +'</div>';
        }
        if(btn){ btn.textContent=T.freeLinkCreated; btn.disabled=true; }
      } else {
        toast(res.message || T.freeClaimOk,'success');
        freeKeyLoaded = false;
        loadFreeKey();
      }
    } else {
      toast(res.error || T.freeErrorGeneric,'error');
      if(btn){ btn.disabled=false; btn.textContent=T.freeBtnGetLink; }
    }
  } catch(e){
    toast(T.toastLoadErr,'error');
    if(btn){ btn.disabled=false; btn.textContent=T.freeBtnGetLink; }
  }
}

var histLoaded = false;
async function loadHistory(){
  if(histLoaded) return;
  var wrap = document.getElementById('histWrap');
  wrap.innerHTML = '<div class="loading"><div class="spin"></div>'+escapeHtml(T.loadingHistory)+'</div>';
  try {
    var res = await api('my_orders','GET',{});
    if(!res.orders || res.orders.length===0){
      wrap.innerHTML = '<div class="hist-empty-note" style="padding:40px 20px"><div class="empty-ico">📭</div><div class="empty-lbl">'+escapeHtml(T.histEmpty)+'</div></div>';
    } else {
      var html = '';
      res.orders.forEach(function(o){
        var badgeCls = o.status==='approved'?'approved':o.status==='rejected'?'rejected':o.status==='cancelled'?'cancelled':'pending';
        var statusText = o.status==='approved'?T.statusApproved:o.status==='rejected'?T.statusRejected:o.status==='cancelled'?T.statusCancelled:T.statusPending;
        html += '<div class="hist-order">'
          +'<div class="hist-header"><span class="hist-code">#'+escapeHtml(o.order_code)+'</span>'
          +'<span class="hist-badge '+badgeCls+'">'+escapeHtml(statusText)+'</span></div>'
          +'<div class="hist-detail">'
          +'<span><span>'+escapeHtml(T.histGame)+'</span><b>'+escapeHtml(o.game_name)+'</b></span>'
          +'<span><span>'+escapeHtml(T.histPkg)+'</span><b>'+escapeHtml(o.pkg_name)+' ('+(parseInt(o.days,10)||0)+' '+escapeHtml(T.histPkgDays)+')</b></span>'
          +'<span><span>'+escapeHtml(T.histCreated)+'</span><b>'+escapeHtml(fmtDate(o.created_at))+'</b></span>'
          +'</div>'
          +'<div class="hist-amount">'+fmtMoney(o.amount)+'₫</div></div>';
      });
      wrap.innerHTML = html;
    }
    histLoaded = true;
  } catch(e){
    wrap.innerHTML = '<div class="hist-empty-note">'+escapeHtml(T.histLoadFail)+'</div>';
  }
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
    profLoaded = true;
  }
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

var scrollTick=false;
document.addEventListener('DOMContentLoaded',function(){
  applyLang();
  initMotion();
  var sc=document.querySelector('.scroll-area');
  if(sc){
    sc.addEventListener('scroll',function(){
      if(scrollTick)return; scrollTick=true;
      requestAnimationFrame(function(){
        var y=sc.scrollTop;
        var prof=document.querySelector('.profile-section');
        if(prof){ prof.style.transform='translateY('+Math.min(10,y*.035)+'px) scale('+(1-Math.min(.035,y/4500))+')'; prof.style.opacity=String(1-Math.min(.22,y/260)); }
        scrollTick=false;
      });
    },{passive:true});
  }
});

</script>

</body>
</html>
