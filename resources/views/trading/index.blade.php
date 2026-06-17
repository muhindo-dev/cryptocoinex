@extends('layouts.trading')
@section('title', 'Trading Simulator — Cryptocoinex')

@push('styles')
<style>
/* ── Reset ──────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:   #080c12; --bg2:  #0d1117; --bg3:  #111621; --bg4:  #161d2a;
  --bdr:  #1c2333; --bdr2: #242e42;
  --txt:  #e2e8f0; --mute: #4a5568; --mute2:#2d3748;
  --buy:  #00c97b; --buy2: #008f55;
  --sell: #f53b57; --sell2:#b02540;
  --gold: #f59e0b; --gold2:#d97706;
  --up:   #22c55e; --dn:   #ef4444;
  --rad:  8px;
}
/* Light theme overrides for the trading screen palette */
:root[data-theme="light"]{
  --bg:#eef2f7; --bg2:#ffffff; --bg3:#f3f6fa; --bg4:#e8edf3;
  --bdr:#d8e0ea; --bdr2:#c6d2e0;
  --txt:#1e293b; --mute:#64748b; --mute2:#94a3b8;
}
html,body{height:100%;overflow:hidden;
  font-family:var(--font-sans,'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif);
  background:var(--bg);color:var(--txt);font-size:15px;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
  letter-spacing:-0.006em;}

/* ── Shell ──────────────────────────────────────────── */
#tsShell{display:flex;height:100vh;overflow:hidden;}

/* ═══ LEFT ICON NAV ═══════════════════════════════════ */
#tsNav{
  width:66px;flex-shrink:0;
  background:var(--bg2);border-right:1px solid var(--bdr);
  display:flex;flex-direction:column;align-items:center;
  padding:11px 0 12px;gap:3px;z-index:20;
  overflow-y:auto;overflow-x:hidden;scrollbar-width:none;
}
#tsNav::-webkit-scrollbar{width:0;}
.ts-logo{
  width:40px;height:40px;margin-bottom:12px;flex-shrink:0;
  background:linear-gradient(135deg,var(--gold),var(--gold2));
  border-radius:11px;display:flex;align-items:center;justify-content:center;
  box-shadow:0 2px 12px rgba(245,158,11,.35);
}
.ts-logo svg{width:20px;height:20px;}
.ts-nav-btn{
  width:54px;min-height:46px;border-radius:11px;flex-shrink:0;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:4px;cursor:pointer;border:none;background:transparent;
  color:var(--mute);text-decoration:none;
  font-size:.58rem;font-weight:700;letter-spacing:.01em;
  transition:background .15s,color .15s;user-select:none;
}
.ts-nav-btn i{font-size:1.02rem;}
.ts-nav-btn:hover{background:var(--bg3);color:var(--txt);}
.ts-nav-btn.on{background:var(--bg3);color:var(--gold);box-shadow:inset 2px 0 0 var(--gold);}
.ts-nav-sep{width:34px;height:1px;background:var(--bdr);margin:7px 0;flex-shrink:0;}
.ts-nav-bot{margin-top:auto;padding-top:6px;display:flex;flex-direction:column;align-items:center;gap:3px;}

/* ═══ CENTER COLUMN ════════════════════════════════════ */
#tsCenter{flex:1;min-width:0;display:flex;flex-direction:column;overflow:hidden;}

/* ── Topbar ─────────────────────────────────────────── */
#tsTopbar{
  height:50px;flex-shrink:0;
  background:var(--bg2);border-bottom:1px solid var(--bdr);
  display:flex;align-items:center;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;
}
#tsTopbar::-webkit-scrollbar{height:0;}
.tb-cell{
  display:flex;align-items:center;height:100%;
  border-right:1px solid var(--bdr);padding:0 14px;flex-shrink:0;
}
/* Asset selector */
#tsAssetBtn{
  gap:8px;cursor:pointer;min-width:185px;
  background:transparent;border:none;color:var(--txt);
}
#tsAssetBtn:hover{background:var(--bg3);}
.tb-sym{font-size:.88rem;font-weight:800;letter-spacing:.03em;}
.tb-name{font-size:.56rem;color:var(--mute);margin-top:1px;text-align:left;}
.tb-chev{color:var(--mute);font-size:.65rem;margin-left:auto;}
/* Price */
#tsPriceBox{gap:10px;}
#tsPrice{
  font-size:1.1rem;font-weight:800;letter-spacing:-.01em;
  font-variant-numeric:tabular-nums;
  transition:color .2s;
}
#tsPrice.flash-up{color:var(--up);}
#tsPrice.flash-dn{color:var(--dn);}
#tsPriceChg{font-size:.68rem;color:var(--mute);}
#tsConn{font-size:.6rem;padding:2px 8px;border-radius:10px;
  background:rgba(74,85,104,.2);white-space:nowrap;}
#tsConn.live{background:rgba(34,197,94,.12);color:var(--up);}
#tsConn.live::before{content:'';display:inline-block;width:6px;height:6px;border-radius:50%;
  background:var(--up);margin-right:5px;vertical-align:middle;animation:tsLivePulse 1.6s ease-in-out infinite;}
#tsConn.err{background:rgba(239,68,68,.12);color:var(--dn);}
@keyframes tsLivePulse{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(34,197,94,.5);}50%{opacity:.5;box-shadow:0 0 0 4px rgba(34,197,94,0);}}
/* Intervals */
#tsIntBtns{gap:3px;}
.ts-int-btn{
  background:transparent;border:none;color:var(--mute);
  padding:5px 9px;border-radius:6px;cursor:pointer;
  font-size:.7rem;font-weight:700;transition:background .12s,color .12s;
}
.ts-int-btn.on,.ts-int-btn:hover{background:var(--bg3);color:var(--gold);}
/* Topbar right — always pinned right & visible (balance + account switch) */
#tsTopRight{margin-left:auto;border-right:none;gap:9px;padding:0 12px;
  position:sticky;right:0;background:var(--bg2);box-shadow:-12px 0 14px 6px var(--bg2);z-index:6;}
.ts-bal-pill{
  display:flex;align-items:center;gap:8px;
  background:var(--bg3);border:1px solid var(--bdr);
  border-radius:9px;padding:5px 6px 5px 6px;
  font-size:.78rem;font-weight:800;color:var(--txt);
}
.ts-demo-badge,.ts-acct-badge{
  font-size:.54rem;font-weight:900;letter-spacing:.06em;
  background:rgba(245,158,11,.16);color:var(--gold);
  padding:3px 7px;border-radius:6px;
}
.ts-acct-badge.live{background:rgba(255,77,106,.18);color:var(--dn);}
.ts-bal-pill.live{border-color:rgba(255,77,106,.45);box-shadow:0 0 0 1px rgba(255,77,106,.25) inset;}
.ts-bal-pill .amt{color:var(--gold);font-variant-numeric:tabular-nums;font-size:.86rem;}
.ts-bal-pill.live .amt{color:var(--dn);}
.ts-bal-pill .cur{font-size:.55rem;color:var(--mute);font-weight:600;padding-right:4px;}
/* Demo / Live segmented switch */
.ts-acct{display:inline-flex;background:var(--bg3);border:1px solid var(--bdr);border-radius:9px;padding:2px;gap:2px;}
.ts-acct-btn{border:none;background:none;cursor:pointer;font-size:.6rem;font-weight:900;letter-spacing:.06em;
  color:var(--mute);padding:5px 10px;border-radius:7px;transition:.15s;}
.ts-acct-btn[data-acct="demo"].on{background:rgba(245,158,11,.16);color:var(--gold);}
.ts-acct-btn[data-acct="live"].on{background:rgba(255,77,106,.2);color:var(--dn);}
.ts-acct-btn:not(.on):hover{color:var(--txt);}
/* Go-Live confirmation modal */
.ts-lm-overlay{position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.6);backdrop-filter:blur(3px);
  display:none;align-items:center;justify-content:center;padding:20px;}
.ts-lm-overlay.show{display:flex;}
.ts-lm{background:var(--bg2);border:1px solid var(--bdr);border-radius:16px;max-width:380px;width:100%;
  padding:26px 24px;text-align:center;box-shadow:0 24px 70px rgba(0,0,0,.6);}
.ts-lm-ic{width:54px;height:54px;border-radius:50%;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;
  background:rgba(255,77,106,.16);color:var(--dn);font-size:1.4rem;}
.ts-lm h3{font-size:1.05rem;font-weight:900;margin:0 0 8px;color:var(--txt);}
.ts-lm p{font-size:.82rem;color:var(--mute);line-height:1.6;margin:0 0 14px;}
.ts-lm-bal{font-size:.8rem;color:var(--txt);background:var(--bg3);border:1px solid var(--bdr);border-radius:9px;
  padding:9px 12px;margin-bottom:18px;}
.ts-lm-bal strong{color:var(--dn);font-variant-numeric:tabular-nums;}
.ts-lm-acts{display:flex;gap:10px;}
.ts-lm-cancel,.ts-lm-go{flex:1;border:none;cursor:pointer;border-radius:10px;padding:12px;font-size:.84rem;font-weight:800;}
.ts-lm-cancel{background:var(--bg3);border:1px solid var(--bdr);color:var(--txt);}
.ts-lm-go{background:linear-gradient(135deg,#ff4d6a,#e23355);color:#fff;}
.ts-lm-go:hover{filter:brightness(1.08);}
/* small premium top-bar buttons */
.ts-top-icon{
  width:32px;height:32px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  background:var(--bg3);border:1px solid var(--bdr);color:var(--mute);
  text-decoration:none;font-size:.78rem;transition:color .15s,border-color .15s,background .15s;
}
.ts-top-icon:hover{color:var(--gold);border-color:rgba(245,158,11,.35);background:var(--bg4);}
.ts-top-deposit{
  display:flex;align-items:center;gap:6px;height:32px;padding:0 12px;
  border-radius:8px;border:1px solid var(--bdr);background:var(--bg3);
  color:var(--txt);font-size:.7rem;font-weight:800;cursor:pointer;
  transition:.15s;white-space:nowrap;
}
.ts-top-deposit i{font-size:.68rem;color:var(--gold);}
.ts-top-deposit:hover{border-color:rgba(245,158,11,.4);background:var(--bg4);}
.ts-top-deposit:active{transform:scale(.96);}
@media(max-width:680px){.ts-top-deposit span{display:none;}}

/* ── Asset icon badge ───────────────────────────────── */
.ts-aico{
  width:26px;height:26px;border-radius:50%;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:.62rem;font-weight:900;letter-spacing:-.02em;color:#fff;
  background:linear-gradient(135deg,#3b82f6,#1e40af);overflow:hidden;
}
.ts-aico img{width:100%;height:100%;object-fit:cover;}
.ts-aico.crypto{background:linear-gradient(135deg,#f7931a,#b45309);}
.ts-aico.forex,.ts-aico.sim{background:linear-gradient(135deg,#3b82f6,#1e40af);}
.ts-aico.stock{background:linear-gradient(135deg,#8b5cf6,#6d28d9);}
.ts-aico.sm{width:22px;height:22px;font-size:.55rem;}

/* ── Asset dropdown ─────────────────────────────────── */
#tsAssetDD{
  display:none;position:fixed;top:50px;left:66px;
  background:var(--bg2);border:1px solid var(--bdr);
  border-radius:10px;min-width:240px;max-height:360px;
  z-index:300;box-shadow:0 8px 40px rgba(0,0,0,.7);overflow:hidden;
}
#tsAssetDD.open{display:flex;flex-direction:column;}
#tsAssetSearch{
  background:var(--bg3);border:none;border-bottom:1px solid var(--bdr);
  color:var(--txt);padding:10px 14px;font-size:.8rem;outline:none;
  flex-shrink:0;
}
#tsAssetSearch::placeholder{color:var(--mute);}
#tsAssetList{overflow-y:auto;flex:1;}
.ts-aopt{
  padding:10px 16px;cursor:pointer;
  display:flex;justify-content:space-between;align-items:center;
  transition:background .1s;
}
.ts-aopt:hover{background:var(--bg3);}
.ts-aopt.on{border-left:3px solid var(--gold);}
.ts-aopt-sym{font-size:.82rem;font-weight:700;}
.ts-aopt-name{font-size:.65rem;color:var(--mute);margin-top:2px;}
.ts-aopt-live{
  font-size:.56rem;padding:2px 7px;border-radius:5px;
  background:rgba(0,201,123,.12);color:var(--buy);font-weight:700;
}

/* ── Chart ──────────────────────────────────────────── */
#tsChartWrap{flex:1;min-height:0;position:relative;}
#tsChart{position:absolute;inset:0;overflow:hidden;}
.ts-tv-credit{
  position:absolute;bottom:4px;right:8px;
  font-size:.52rem;color:var(--mute2);pointer-events:none;z-index:1;
}
.ts-tv-credit a{color:var(--mute2);text-decoration:none;}

/* ── Bottom trading controls ───────────────────────── */
#tsBottom{
  flex-shrink:0;min-height:96px;
  background:var(--bg2);border-top:1px solid var(--bdr);
  display:flex;align-items:center;gap:12px;
  padding:10px 14px;
}
/* SELL / BUY fill the space the expiry+timer used to occupy */
#tsBottom .ts-trade-btn{flex:1;}

/* Amount */
.tc-lbl{font-size:.58rem;font-weight:700;color:var(--mute);text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;}
.ts-amt-wrap{display:flex;flex-direction:column;min-width:140px;}
.ts-amt-row{
  display:flex;align-items:center;
  background:var(--bg3);border:1px solid var(--bdr);border-radius:var(--rad);overflow:hidden;
}
.ts-amt-btn{
  background:transparent;border:none;color:var(--mute);
  width:32px;height:38px;cursor:pointer;font-size:1rem;
  transition:color .1s,background .1s;flex-shrink:0;
}
.ts-amt-btn:hover{color:var(--txt);background:var(--bdr);}
#tsStake{
  flex:1;background:transparent;border:none;color:var(--txt);
  font-size:.95rem;font-weight:800;text-align:center;outline:none;
  font-variant-numeric:tabular-nums;min-width:0;
}
.ts-qs{display:flex;gap:3px;margin-top:4px;}
.ts-qs-btn{
  flex:1;background:var(--bdr2);border:1px solid transparent;
  color:var(--mute);padding:3px 2px;border-radius:5px;
  cursor:pointer;font-size:.58rem;font-weight:700;
  transition:background .1s,color .1s,border-color .1s;
}
.ts-qs-btn:hover{background:rgba(245,158,11,.12);color:var(--gold);border-color:rgba(245,158,11,.25);}

/* Expiry */
.ts-exp-wrap{display:flex;flex-direction:column;min-width:0;}
.ts-exp-btns{display:flex;gap:4px;flex-wrap:wrap;}
.ts-exp-btn{
  background:var(--bdr2);border:1px solid transparent;
  color:var(--mute);padding:6px 12px;border-radius:6px;
  cursor:pointer;font-size:.7rem;font-weight:700;
  transition:all .12s;white-space:nowrap;
}
.ts-exp-btn.on,.ts-exp-btn:hover{
  background:rgba(245,158,11,.1);color:var(--gold);
  border-color:rgba(245,158,11,.3);
}

/* Countdown */
.ts-timer-wrap{
  display:flex;flex-direction:column;align-items:center;
  min-width:64px;
}
#tsTimer{
  font-size:1.6rem;font-weight:900;color:var(--txt);
  font-variant-numeric:tabular-nums;line-height:1;
  letter-spacing:-.02em;
}
.ts-timer-lbl{font-size:.55rem;color:var(--mute);text-transform:uppercase;letter-spacing:.08em;margin-top:3px;}

/* SELL / BUY action buttons */
.ts-trade-btn{
  flex:1;border:none;border-radius:12px;
  min-height:62px;cursor:pointer;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:2px;padding:8px 10px;
  font-weight:900;letter-spacing:.04em;
  transition:transform .1s,box-shadow .18s,filter .18s,opacity .15s;
  position:relative;overflow:hidden;
  user-select:none;
}
.ts-trade-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.14),transparent 45%);pointer-events:none;}
.ts-trade-btn:hover{filter:brightness(1.06);}
.ts-trade-btn:active{transform:scale(.97);}
.ts-trade-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none;}
.ts-sell-btn{background:linear-gradient(175deg,#fb4767,#c0293e);box-shadow:0 6px 20px rgba(245,59,87,.28);}
.ts-buy-btn{background:linear-gradient(175deg,#0ad389,#008f55);box-shadow:0 6px 20px rgba(0,201,123,.28);}
.ts-trade-btn .tb-arrow{font-size:1.05rem;color:#fff;}
.ts-trade-btn .tb-word{font-size:.95rem;color:#fff;}
.ts-trade-btn .tb-pct{font-size:.65rem;color:rgba(255,255,255,.75);font-weight:700;}
.ts-trade-btn.loading{opacity:.6;pointer-events:none;}
.ts-trade-btn.loading::after{
  content:'';position:absolute;inset:0;
  background:rgba(0,0,0,.35);
  display:flex;align-items:center;justify-content:center;
}

/* ═══ RIGHT DEALS PANEL ════════════════════════════════ */
#tsDeals{
  width:268px;flex-shrink:0;
  background:var(--bg2);border-left:1px solid var(--bdr);
  display:flex;flex-direction:column;overflow:hidden;
}
.ts-d-tabs{display:flex;border-bottom:1px solid var(--bdr);flex-shrink:0;}
.ts-d-tab{
  flex:1;padding:11px 8px;text-align:center;
  font-size:.65rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  color:var(--mute);cursor:pointer;
  border-bottom:2px solid transparent;
  transition:color .12s,border-color .12s;
}
.ts-d-tab.on{color:var(--gold);border-bottom-color:var(--gold);}

/* Balance card */
.ts-bal-card{padding:13px 14px;border-bottom:1px solid var(--bdr);flex-shrink:0;}
.ts-bal-lbl{font-size:.58rem;font-weight:700;color:var(--mute);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;}
.ts-bal-val{font-size:1.45rem;font-weight:900;color:var(--gold);font-variant-numeric:tabular-nums;line-height:1;display:flex;align-items:baseline;gap:6px;flex-wrap:wrap;}
.ts-bal-unit{font-size:.72rem;font-weight:800;color:var(--gold);opacity:.8;letter-spacing:.02em;}
.ts-bal-cur{font-size:.58rem;color:var(--mute);margin-top:4px;}
.ts-bal-stats{display:grid;grid-template-columns:1fr 1fr;gap:5px;margin-top:9px;}
.ts-bs{background:var(--bg3);border-radius:7px;padding:7px 9px;text-align:center;}
.ts-bs-v{font-size:.82rem;font-weight:800;}
.ts-bs-l{font-size:.53rem;color:var(--mute);text-transform:uppercase;letter-spacing:.05em;margin-top:2px;}

/* Deals body */
.ts-d-body{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--bdr2) transparent;}
.ts-d-body::-webkit-scrollbar{width:3px;}
.ts-d-body::-webkit-scrollbar-thumb{background:var(--bdr2);border-radius:3px;}

/* Deal item */
.ts-deal{
  padding:10px 13px;border-bottom:1px solid var(--bdr);
  display:flex;align-items:flex-start;gap:9px;
}
.ts-deal-ico{
  width:28px;height:28px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:.75rem;
}
.ts-deal-ico.up{background:rgba(34,197,94,.15);color:var(--up);}
.ts-deal-ico.down{background:rgba(239,68,68,.15);color:var(--dn);}
.ts-deal-info{flex:1;min-width:0;}
.ts-deal-sym{font-size:.75rem;font-weight:800;}
.ts-deal-meta{font-size:.6rem;color:var(--mute);margin-top:2px;line-height:1.4;}
.ts-deal-right{text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:5px;}
.ts-deal-pl{font-size:.76rem;font-weight:800;font-variant-numeric:tabular-nums;}
.ts-deal-pl.pos{color:var(--up);}
.ts-deal-pl.neg{color:var(--dn);}
.ts-deal-pl.tie{color:var(--mute);}
.ts-close-btn{
  background:var(--gold);color:#1a1206;border:none;border-radius:7px;
  padding:6px 14px;font-size:.66rem;font-weight:800;cursor:pointer;letter-spacing:.03em;
  transition:filter .12s,opacity .12s;text-transform:uppercase;
}
.ts-close-btn:hover{filter:brightness(1.08);}
.ts-close-btn:disabled{opacity:.6;cursor:default;}

/* ── KYC gate banner (above the trade bar) ────────────────────── */
#tsKycBanner{display:flex;align-items:center;gap:12px;text-decoration:none;flex-shrink:0;
  background:linear-gradient(90deg,rgba(245,158,11,.16),rgba(245,158,11,.06));border-top:1px solid rgba(245,158,11,.35);
  color:var(--txt);padding:11px 16px;font-size:.8rem;}
#tsKycBanner > i:first-child{font-size:1.1rem;color:var(--gold);flex-shrink:0;}
#tsKycBanner span{flex:1;min-width:0;line-height:1.4;}
#tsKycBanner strong{color:var(--gold);}
#tsKycBanner .ts-kyc-cta{flex:none;background:var(--gold);color:#1a1206;font-weight:800;padding:7px 13px;border-radius:9px;
  white-space:nowrap;font-size:.74rem;}
@media(max-width:680px){#tsKycBanner span:not(.ts-kyc-cta){font-size:.72rem;}#tsKycBanner{padding:9px 12px;gap:9px;}}

/* ── Tooltips (hover, explain every tool) ─────────────────────── */
[data-tip]{position:relative;}
[data-tip]::after{
  content:attr(data-tip);
  position:absolute;z-index:1000;left:50%;top:calc(100% + 8px);transform:translateX(-50%);
  width:max-content;max-width:210px;white-space:normal;text-align:center;
  background:#0b0f17;color:#e6edf6;border:1px solid var(--bdr2,#2a3550);
  padding:6px 9px;border-radius:7px;font-size:.62rem;font-weight:600;line-height:1.4;letter-spacing:.01em;
  box-shadow:0 8px 24px rgba(0,0,0,.55);
  opacity:0;visibility:hidden;transition:opacity .12s ease,visibility 0s linear .43s;pointer-events:none;
}
[data-tip]:hover::after{opacity:1;visibility:visible;transition:opacity .12s ease .3s;}
[data-tip-dir="up"]::after{top:auto;bottom:calc(100% + 8px);}
[data-tip-dir="right"]::after{top:50%;left:calc(100% + 10px);transform:translateY(-50%);}
[data-tip-dir="down-left"]::after{left:auto;right:0;transform:none;}
[data-tip-dir="up-left"]::after{top:auto;bottom:calc(100% + 8px);left:auto;right:0;transform:none;}
.ts-deal-status{
  font-size:.56rem;font-weight:700;
  padding:2px 7px;border-radius:5px;display:inline-block;margin-top:3px;
}
.ts-deal-status.open{background:rgba(245,158,11,.15);color:var(--gold);}
.ts-deal-status.won{background:rgba(34,197,94,.12);color:var(--up);}
.ts-deal-status.lost{background:rgba(239,68,68,.12);color:var(--dn);}
.ts-deal-status.tie{background:var(--bdr2);color:var(--mute);}
.ts-deal-cdwn{font-size:.75rem;font-weight:800;color:var(--gold);font-variant-numeric:tabular-nums;}
/* Time-remaining ring */
.ts-ring-wrap{position:relative;width:38px;height:38px;margin-left:auto;}
.ts-ring{transform:rotate(-90deg);width:38px;height:38px;display:block;}
.ts-ring-bg{fill:none;stroke:var(--bdr2);stroke-width:3;}
.ts-ring-fg{fill:none;stroke:var(--gold);stroke-width:3;stroke-linecap:round;
  stroke-dasharray:94.25;stroke-dashoffset:0;transition:stroke-dashoffset .3s linear,stroke .3s;}
.ts-ring-txt{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-size:.6rem;font-weight:800;font-variant-numeric:tabular-nums;color:var(--txt);}

.ts-empty{padding:28px 14px;text-align:center;font-size:.73rem;color:var(--mute);}
.ts-disc{
  padding:8px 13px;font-size:.56rem;
  color:var(--mute2);line-height:1.55;
  border-top:1px solid var(--bdr);flex-shrink:0;
}

/* ── Toast ──────────────────────────────────────────── */
.ts-toast{
  position:fixed;bottom:20px;left:50%;transform:translateX(-50%) translateY(0);
  padding:10px 20px;border-radius:9px;font-size:.78rem;font-weight:700;
  z-index:9999;animation:tsIn .22s ease;white-space:nowrap;
  box-shadow:0 6px 24px rgba(0,0,0,.55);pointer-events:none;
}
.ts-toast.win{background:#15803d;color:#fff;}
.ts-toast.lose{background:#b91c1c;color:#fff;}
.ts-toast.info{background:#1d4ed8;color:#fff;}
.ts-toast.warn{background:#92400e;color:#fff;}
.ts-toast.err{background:#7f1d1d;color:#fff;}
@keyframes tsIn{
  from{opacity:0;transform:translateX(-50%) translateY(12px);}
  to{opacity:1;transform:translateX(-50%) translateY(0);}
}

/* ── Stake % of balance + min hint ──────────────────── */
.ts-stake-meta{display:flex;justify-content:space-between;align-items:center;margin-top:4px;font-size:.55rem;font-weight:700;}
#tsStakePct{color:var(--up);transition:color .15s;}
.ts-stake-min{color:var(--mute);text-transform:uppercase;letter-spacing:.04em;}
/* smart % quick-stake row */
.ts-qs-pct{display:flex;gap:3px;margin-top:4px;}
.ts-qs-pct-btn{
  flex:1;background:transparent;border:1px solid var(--bdr2);
  color:var(--mute);padding:3px 2px;border-radius:5px;
  cursor:pointer;font-size:.55rem;font-weight:800;
  transition:background .1s,color .1s,border-color .1s;
}
.ts-qs-pct-btn:hover{background:rgba(245,158,11,.12);color:var(--gold);border-color:rgba(245,158,11,.3);}

/* Payout line on trade buttons */
.ts-trade-btn .tb-payout{font-size:.6rem;color:rgba(255,255,255,.92);font-weight:800;font-variant-numeric:tabular-nums;}

/* Shake (max-stake cap / loss) */
@keyframes tsShake{
  0%,100%{transform:translateX(0);}
  20%{transform:translateX(-6px);}40%{transform:translateX(6px);}
  60%{transform:translateX(-4px);}80%{transform:translateX(4px);}
}
.ts-shake{animation:tsShake .4s ease;}

/* Balance value pop on change */
@keyframes tsPop{0%{transform:scale(1);}40%{transform:scale(1.12);}100%{transform:scale(1);}}
.ts-pop{animation:tsPop .45s ease;}
.ts-bal-flash-up{color:var(--up)!important;}
.ts-bal-flash-dn{color:var(--dn)!important;}

/* Daily P&L line under balance */
.ts-bal-pnl{font-size:.62rem;font-weight:800;margin-top:4px;font-variant-numeric:tabular-nums;}
.ts-bal-pnl.pos{color:var(--up);}
.ts-bal-pnl.neg{color:var(--dn);}
.ts-bal-pnl.flat{color:var(--mute);}

/* Chart loading skeleton */
#tsChartSkel{
  position:absolute;inset:0;z-index:5;background:var(--bg);
  display:flex;align-items:center;justify-content:center;flex-direction:column;gap:14px;
}
#tsChartSkel.hide{display:none;}
.ts-skel-bars{display:flex;align-items:flex-end;gap:6px;height:120px;}
.ts-skel-bars span{
  width:10px;border-radius:3px;
  background:linear-gradient(180deg,var(--bg4),var(--bg3));
  animation:tsSkel 1.1s ease-in-out infinite;
}
@keyframes tsSkel{0%,100%{opacity:.35;}50%{opacity:.9;}}
.ts-skel-txt{font-size:.7rem;color:var(--mute);letter-spacing:.04em;}

/* Offline banner */
#tsOffline{
  position:fixed;top:0;left:0;right:0;z-index:9998;
  background:#92400e;color:#fff;text-align:center;
  font-size:.72rem;font-weight:700;padding:7px 12px;
  transform:translateY(-100%);transition:transform .25s ease;
}
#tsOffline.show{transform:translateY(0);}

/* Hidden attribute polyfill safety */
[hidden]{display:none!important;}

/* ── Responsive ─────────────────────────────────────── */
/* Hide the right deals panel on tablets/phones (history still in left nav) */
/* Floating positions toggle + slide-up sheet (hidden on desktop) */
/* Sits above the lifted Tawk chat bubble (which is at the bottom-right) */
#tsDealsFab{position:fixed;right:16px;bottom:182px;z-index:55;width:52px;height:52px;border-radius:50%;border:none;
  cursor:pointer;background:var(--gold);color:#1a1206;font-size:1.15rem;box-shadow:0 6px 22px rgba(0,0,0,.55);
  display:none;align-items:center;justify-content:center;}
#tsFabBadge{position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;padding:0 5px;border-radius:10px;
  background:var(--dn);color:#fff;font-size:.6rem;font-weight:800;display:none;align-items:center;justify-content:center;
  border:2px solid var(--bg);}
#tsFabBadge.show{display:flex;}
#tsDealsBackdrop{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:58;display:none;}
#tsDealsBackdrop.show{display:block;}

@media(max-width:980px){
  /* Positions/balance become a slide-up bottom sheet instead of being hidden */
  #tsDeals{
    display:flex;position:fixed;left:0;right:0;bottom:0;top:auto;width:auto;height:auto;max-height:80vh;
    border-left:none;border-top:1px solid var(--bdr);border-radius:16px 16px 0 0;z-index:60;
    transform:translateY(110%);transition:transform .26s ease;box-shadow:0 -12px 44px rgba(0,0,0,.55);
  }
  #tsDeals.show{transform:translateY(0);}
  #tsDealsFab{display:flex;}
  .ts-d-tabs{position:relative;}
  .ts-d-tabs::before{content:"";position:absolute;top:-9px;left:50%;transform:translateX(-50%);
    width:40px;height:4px;border-radius:3px;background:var(--bdr2);}
}

/* Tablet: let the topbar scroll instead of clipping; keep balance pinned right */
@media(max-width:900px){
  #tsTopbar{overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;}
  #tsTopbar::-webkit-scrollbar{height:0;}
  #tsTopRight{position:sticky;right:0;background:var(--bg2);box-shadow:-10px 0 14px 6px var(--bg2);z-index:5;}
}

/* Phones */
@media(max-width:680px){
  html,body{font-size:14px;}
  /* Compact rail */
  #tsNav{width:56px;}
  .ts-nav-btn{width:48px;min-height:42px;gap:3px;font-size:.5rem;}
  .ts-nav-btn i{font-size:.95rem;}
  .ts-logo{width:36px;height:36px;}
  /* Declutter the topbar — hide advanced/secondary groups, keep core trading */
  #tsIndBtns{display:none;}
  .tb-cell{padding:0 11px;}
  #tsAssetBtn{min-width:150px;}
  /* Bottom controls: amount row on top, then SELL | BUY */
  #tsBottom{
    display:grid;height:auto;padding:10px 12px env(safe-area-inset-bottom,10px);gap:9px;
    grid-template-columns:1fr 1fr;
    grid-template-areas:
      "amount amount"
      "sell   buy";
    align-items:stretch;
  }
  .ts-amt-wrap{grid-area:amount;min-width:0;}
  #tsSell{grid-area:sell;}
  #tsBuy{grid-area:buy;}
  #tsBottom .ts-trade-btn{min-height:58px;width:100%;flex:none;}
  #tsChartWrap{min-height:200px;}
}

/* Small phones */
@media(max-width:430px){
  #tsTypeBtns{display:none;}
  .ts-qs-btn{font-size:.55rem;padding:4px 2px;}
  .ts-top-deposit span{display:none;}
  .ts-bal-pill .cur{display:none;}
  #tsAssetBtn{min-width:128px;}
}
</style>
@endpush

@section('content')

{{-- Asset registry for JS (hidden) --}}
<div id="tsAssetData" hidden>
  @foreach($assets as $a)
  <span
    data-id="{{ $a->id }}"
    data-symbol="{{ $a->symbol }}"
    data-name="{{ $a->name }}"
    data-payout="{{ (int)$a->payout_percent }}"
    data-live="{{ $a->supports_live ? '1' : '0' }}"
    data-expiries="{{ json_encode($a->allowed_expiries ?? [30,60,300]) }}"
    data-min="{{ $a->min_stake }}"
    data-max="{{ $a->max_stake }}"
    data-class="{{ $a->asset_class }}"
    data-icon="{{ $a->icon_url }}"
    {{ ($selectedAsset && $selectedAsset->id === $a->id) ? 'data-sel' : '' }}
  ></span>
  @endforeach
</div>

<div id="tsOffline">⚠ Connection lost — prices paused. Reconnecting…</div>

<div id="tsShell">

  {{-- ── LEFT NAV ──────────────────────────────────── --}}
  <nav id="tsNav">
    <div class="ts-logo">
      <svg viewBox="0 0 24 24" fill="none">
        <polyline points="3,17 8,10 13,14 21,5" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="17,5 21,5 21,9" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <button class="ts-nav-btn on" id="navTrade" data-tip="Trade — open and close positions" data-tip-dir="right">
      <i class="fas fa-chart-column"></i>Trade
    </button>
    <a href="{{ route('trade.history.page') }}" class="ts-nav-btn" data-tip="History of all your settled trades" data-tip-dir="right">
      <i class="fas fa-clock-rotate-left"></i>History
    </a>
    <a href="{{ route('trade.leaderboard') }}" class="ts-nav-btn" data-tip="Leaderboard — see how you rank" data-tip-dir="right">
      <i class="fas fa-ranking-star"></i>Ranks
    </a>
    <a href="{{ route('trade.tournaments.index') }}" class="ts-nav-btn" data-tip="Trading tournaments & competitions" data-tip-dir="right">
      <i class="fas fa-trophy"></i>Cups
    </a>
    <a href="{{ route('trade.education.index') }}" class="ts-nav-btn" data-tip="Trading Academy — lessons & tutorials" data-tip-dir="right">
      <i class="fas fa-graduation-cap"></i>Learn
    </a>
    <a href="{{ route('trade.wallet.page') }}" class="ts-nav-btn" data-tip="Practice wallet & transaction ledger" data-tip-dir="right">
      <i class="fas fa-wallet"></i>Wallet
    </a>
    <a href="{{ route('trade.live') }}" class="ts-nav-btn {{ request()->routeIs('trade.live*') ? 'on' : '' }}" data-tip="Live Account — real money" data-tip-dir="right">
      <i class="fas fa-sack-dollar"></i>Live
    </a>
    <a href="{{ route('trade.kyc') }}" class="ts-nav-btn {{ request()->routeIs('trade.kyc*') ? 'on' : '' }}" data-tip="Verify your identity" data-tip-dir="right" style="position:relative;">
      <i class="fas fa-id-card"></i>Verify
      @if(auth()->user() && auth()->user()->kyc_status !== 'approved')
        <span style="position:absolute;top:6px;right:9px;width:8px;height:8px;border-radius:50%;background:{{ auth()->user()->kyc_status==='pending'?'var(--gold)':'var(--dn)' }};border:1.5px solid var(--bg2);"></span>
      @endif
    </a>
    <a href="{{ route('trade.profile') }}" class="ts-nav-btn" data-tip="Your profile & account settings" data-tip-dir="right">
      <i class="fas fa-user"></i>Profile
    </a>
    <div class="ts-nav-sep"></div>
    <div class="ts-nav-bot">
      @if(auth()->user()?->canAccessAdmin())
      <a href="{{ route('admin.dashboard') }}" class="ts-nav-btn" data-tip="Admin panel (staff only)" data-tip-dir="right">
        <i class="fas fa-shield-halved"></i>Admin
      </a>
      @endif
      <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">@csrf
        <button class="ts-nav-btn" type="submit" data-tip="Sign out" data-tip-dir="right" style="background:none;border:none;cursor:pointer;width:100%;">
          <i class="fas fa-right-from-bracket"></i>Exit
        </button>
      </form>
    </div>
  </nav>

  {{-- ── CENTER ─────────────────────────────────────── --}}
  <div id="tsCenter">

    {{-- Top bar --}}
    <div id="tsTopbar">

      {{-- Asset selector button --}}
      <button class="tb-cell" id="tsAssetBtn" style="gap:10px;" data-tip="Choose which market to trade — crypto, forex, commodities…">
        <span class="ts-aico" id="tsTopIcon"></span>
        <div>
          <div class="tb-sym" id="tsSymLabel">{{ $selectedAsset?->symbol ?? '—' }}</div>
          <div class="tb-name" id="tsNameLabel">{{ $selectedAsset?->name ?? 'No asset' }}</div>
        </div>
        <i class="fas fa-chevron-down tb-chev"></i>
      </button>

      {{-- Asset dropdown --}}
      <div id="tsAssetDD">
        <input id="tsAssetSearch" placeholder="Search asset…" autocomplete="off" spellcheck="false">
        <div id="tsAssetList"></div>
      </div>

      {{-- Price --}}
      <div class="tb-cell" id="tsPriceBox" data-tip="Live price and today's change">
        <div>
          <span id="tsPrice" class="flash-up">—</span>
          <span id="tsPriceChg"></span>
        </div>
        <span id="tsConn">● Connecting</span>
      </div>

      {{-- Intervals --}}
      <div class="tb-cell" id="tsIntBtns">
        @php $ivTips = ['10s'=>'10 seconds','25s'=>'25 seconds','30s'=>'30 seconds','1m'=>'1 minute','5m'=>'5 minutes','15m'=>'15 minutes']; @endphp
        @foreach(['10s','25s','30s','1m','5m','15m'] as $iv)
          <button class="ts-int-btn {{ $iv==='30s'?'on':'' }}" data-iv="{{ $iv }}" data-tip="Timeframe — {{ $ivTips[$iv] }} per candle">{{ $iv }}</button>
        @endforeach
      </div>

      {{-- Chart type --}}
      <div class="tb-cell" id="tsTypeBtns">
        <button class="ts-int-btn on" data-type="candles" data-tip="Candlestick chart"><i class="fas fa-chart-column"></i></button>
        <button class="ts-int-btn" data-type="line" data-tip="Line chart"><i class="fas fa-chart-line"></i></button>
        <button class="ts-int-btn" data-type="area" data-tip="Area chart"><i class="fas fa-chart-area"></i></button>
      </div>

      {{-- Indicators --}}
      <div class="tb-cell" id="tsIndBtns">
        <button class="ts-int-btn" data-ind="ma20" data-tip="20-period moving average — short-term trend">MA20</button>
        <button class="ts-int-btn" data-ind="ma50" data-tip="50-period moving average — longer-term trend">MA50</button>
        <button class="ts-int-btn" data-ind="rsi14" data-tip="RSI (14) — overbought / oversold momentum">RSI</button>
      </div>

      {{-- Sound + Help --}}
      <div class="tb-cell" id="tsUtilBtns">
        <button class="ts-int-btn" id="tsMuteBtn" data-tip="Mute / unmute sound effects"><i class="fas fa-volume-high"></i></button>
        <button class="ts-int-btn" onclick="cxTheme.toggle()" data-tip="Switch light / dark theme"><i data-theme-icon class="fas fa-sun"></i></button>
        <button class="ts-int-btn" id="tsTourBtn" data-tip="Take a quick guided tour"><i class="fas fa-circle-question"></i></button>
      </div>

      {{-- Market-data mode (SIM/LIVE) is configured by admins in the backend —
           students don't toggle it here. --}}

      {{-- Balance + account switch --}}
      <div class="tb-cell" id="tsTopRight">
        {{-- Demo / Live account switcher --}}
        @if($liveEnabled)
        <div class="ts-acct" id="tsAcct" data-tip="Switch between Demo (practice) and your real-money Live account" data-tip-dir="down-left">
          <button type="button" class="ts-acct-btn on" data-acct="demo">DEMO</button>
          <button type="button" class="ts-acct-btn" data-acct="live">LIVE</button>
        </div>
        @endif
        <div class="ts-bal-pill" id="tsBalPill" data-tip="Active account balance" data-tip-dir="down-left">
          <span class="ts-acct-badge" id="tsBalBadge">DEMO</span>
          <span class="amt" id="tsBalChip">{{ number_format($wallet->balance) }}</span>
          <span class="cur" id="tsBalCur">USD</span>
        </div>
        <button class="ts-top-deposit" id="tsResetBtn" data-tip="Reset your practice balance to the starting amount" data-tip-dir="down-left">
          <i class="fas fa-rotate-right"></i><span>Reset</span>
        </button>
        <a class="ts-top-deposit ts-deposit-live" id="tsDepositBtn" href="{{ route('trade.live') }}" style="display:none;" data-tip="Deposit real funds to your Live account" data-tip-dir="down-left">
          <i class="fas fa-plus"></i><span>Deposit</span>
        </a>
      </div>
    </div>

    {{-- Go-Live confirmation modal --}}
    <div id="tsLiveModal" class="ts-lm-overlay">
      <div class="ts-lm">
        <div class="ts-lm-ic"><i class="fas fa-sack-dollar"></i></div>
        <h3>Switch to your Live account?</h3>
        <p>You're about to trade with <strong>real money</strong> from your Live account balance.
           Wins and losses are real and settle against your real funds. Trade responsibly.</p>
        <div class="ts-lm-bal">Live balance: <strong id="tsLmBal">—</strong></div>
        <div class="ts-lm-acts">
          <button type="button" class="ts-lm-cancel" id="tsLmCancel">Stay on Demo</button>
          <button type="button" class="ts-lm-go" id="tsLmGo">Go Live</button>
        </div>
      </div>
    </div>

    {{-- Chart --}}
    <div id="tsChartWrap">
      <div id="tsChart"></div>
      <div id="tsChartSkel">
        <div class="ts-skel-bars">
          <span style="height:40%"></span><span style="height:70%"></span><span style="height:55%"></span>
          <span style="height:90%"></span><span style="height:45%"></span><span style="height:75%"></span>
          <span style="height:60%"></span><span style="height:85%"></span><span style="height:50%"></span>
          <span style="height:95%"></span><span style="height:65%"></span><span style="height:80%"></span>
        </div>
        <div class="ts-skel-txt">Fetching market data…</div>
      </div>
      <div class="ts-tv-credit">
        Powered by <a href="https://www.tradingview.com/" target="_blank" rel="noopener">TradingView</a>
      </div>
    </div>

    {{-- KYC gate: shown until the user is verified --}}
    @unless($kycApproved ?? false)
    <a href="{{ route('trade.kyc') }}" id="tsKycBanner">
      <i class="fas fa-id-card"></i>
      <span><strong>Verify your identity to start trading.</strong> A quick verification is required before buying or selling.</span>
      <span class="ts-kyc-cta">Verify now <i class="fas fa-arrow-right"></i></span>
    </a>
    @endunless

    {{-- Bottom trading bar --}}
    <div id="tsBottom">

      {{-- Amount --}}
      <div class="ts-amt-wrap">
        <div class="tc-lbl">Amount</div>
        <div class="ts-amt-row" data-tip="Amount of practice funds to stake on the trade" data-tip-dir="up">
          <button class="ts-amt-btn" id="tsAmtDec" type="button" data-tip="Decrease amount" data-tip-dir="up">−</button>
          <input id="tsStake" type="number" value="100" min="1" step="1">
          <button class="ts-amt-btn" id="tsAmtInc" type="button" data-tip="Increase amount" data-tip-dir="up">+</button>
        </div>
        <div class="ts-stake-meta">
          <span id="tsStakePct">0% of balance</span>
          <span class="ts-stake-min" id="tsStakeMin">Min: 1</span>
        </div>
        <div class="ts-qs">
          @foreach([25,50,100,250,500,1000] as $q)
            <button class="ts-qs-btn" data-q="{{ $q }}" type="button" data-tip="Set stake to {{ number_format($q) }}" data-tip-dir="up">
              {{ $q>=1000 ? '1K' : $q }}
            </button>
          @endforeach
        </div>
        <div class="ts-qs-pct">
          @foreach(['5'=>'5%','10'=>'10%','25'=>'25%','50'=>'50%','100'=>'ALL'] as $p=>$lbl)
            <button class="ts-qs-pct-btn" data-pct="{{ $p }}" type="button" data-tip="Stake {{ $lbl==='ALL' ? 'your whole balance' : $lbl.' of your balance' }}" data-tip-dir="up">{{ $lbl }}</button>
          @endforeach
        </div>
      </div>

      {{-- SELL button --}}
      <button class="ts-trade-btn ts-sell-btn" id="tsSell" type="button" data-tip="SELL — open a DOWN position. You profit if the price falls, then close to lock it in." data-tip-dir="up">
        <span class="tb-arrow">▼</span>
        <span class="tb-word">SELL</span>
        <span class="tb-payout" id="tsSellPayout">+80</span>
        <span class="tb-pct" id="tsSellPct">80%</span>
      </button>

      {{-- BUY button --}}
      <button class="ts-trade-btn ts-buy-btn" id="tsBuy" type="button" data-tip="BUY — open an UP position. You profit if the price rises, then close to lock it in." data-tip-dir="up">
        <span class="tb-arrow">▲</span>
        <span class="tb-word">BUY</span>
        <span class="tb-payout" id="tsBuyPayout">+80</span>
        <span class="tb-pct" id="tsBuyPct">80%</span>
      </button>

    </div>{{-- /tsBottom --}}
  </div>{{-- /tsCenter --}}

  {{-- ── RIGHT DEALS PANEL ──────────────────────────── --}}
  <div id="tsDeals">
    <div class="ts-d-tabs">
      <div class="ts-d-tab on" id="tabOpen">
        Open <span id="tsOpenBadge"></span>
      </div>
      <div class="ts-d-tab" id="tabHist">History</div>
    </div>

    {{-- Balance --}}
    <div class="ts-bal-card" id="tsBalCard">
      <div class="ts-bal-lbl" id="tsBalPanelLbl">Practice Balance</div>
      <div class="ts-bal-val"><span id="tsBalPanel">{{ number_format($wallet->balance) }}</span><span class="ts-bal-unit" id="tsBalPanelUnit">{{ $wallet->currency_label ?? 'USD' }}</span></div>
      <div class="ts-bal-cur" id="tsBalPanelSub">Virtual funds · no real money</div>
      @php
        $ttl = auth()->user()->trades()->whereIn('status',['won','lost','tie'])->count();
        $w   = auth()->user()->trades()->where('status','won')->count();
        $wr  = $ttl > 0 ? round($w/$ttl*100) : 0;
        $todayPnl = (int) auth()->user()->trades()
            ->whereDate('settled_at', today())
            ->get()
            ->sum(fn($t) => ((int)($t->payout_amount ?? 0)) - (int)$t->stake);
        $pnlCls = $todayPnl > 0 ? 'pos' : ($todayPnl < 0 ? 'neg' : 'flat');
        $pnlSign = $todayPnl > 0 ? '+' : '';
      @endphp
      <div class="ts-bal-pnl {{ $pnlCls }}" id="tsBalPnl" data-pnl="{{ $todayPnl }}">
        Today: {{ $pnlSign }}{{ number_format($todayPnl) }} USD
      </div>
      <div class="ts-bal-stats">
        <div class="ts-bs">
          <div class="ts-bs-v" style="color:{{ $wr>=50?'var(--up)':'var(--dn)' }}">{{ $wr }}%</div>
          <div class="ts-bs-l">Win Rate</div>
        </div>
        <div class="ts-bs">
          <div class="ts-bs-v">{{ $ttl }}</div>
          <div class="ts-bs-l">Settled</div>
        </div>
      </div>
    </div>

    <div class="ts-d-body">
      <div id="tsOpenPanel">
        <div id="tsOpenList" class="ts-empty">No open positions.</div>
      </div>
      <div id="tsHistPanel" hidden>
        <div id="tsHistList" class="ts-empty">No trades yet.</div>
      </div>
    </div>

    <div class="ts-disc">
      ⚠ Practice only. Virtual funds — no real money.
    </div>
  </div>

  {{-- Mobile: floating toggle + backdrop for the positions/deals bottom-sheet --}}
  <div id="tsDealsBackdrop" onclick="toggleDeals(false)"></div>
  <button id="tsDealsFab" type="button" onclick="toggleDeals()" aria-label="Open positions & balance" data-tip="Positions & balance" data-tip-dir="up-left">
    <i class="fas fa-layer-group"></i>
    <span id="tsFabBadge"></span>
  </button>

</div>{{-- /tsShell --}}
@endsection

@push('scripts')
<script src="{{ asset('vendor/js/lightweight-charts.js') }}"></script>
<script src="{{ asset('vendor/js/confetti.browser.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('vendor/css/driver.css') }}">
<script src="{{ asset('vendor/js/driver.js.iife.js') }}"></script>
<script>
(function () {
'use strict';

/* ════════════════════════════════════════════════════════
   CONFIGURATION  (injected server-side)
   ════════════════════════════════════════════════════════ */
const CFG = {
  csrf:    '{{ csrf_token() }}',
  feed:    '{{ route("trade.feed") }}',
  price:   '{{ route("trade.price") }}',
  place:   '{{ route("trade.place") }}',
  show:    '{{ url("trade") }}',   /* + /{id} and /{id}/close */
  history: '{{ route("trade.history") }}',
  openList:'{{ route("trade.openlist") }}',
  account: '{{ route("trade.account") }}',
  reset:   '{{ route("trade.wallet.reset") }}',
};

/* Demo (practice) vs Live (real-money) account state. */
const ACCT = {
  current: 'demo',
  liveEnabled: {{ $liveEnabled ? 'true' : 'false' }},
  kycApproved: {{ ($kycApproved ?? false) ? 'true' : 'false' }},
  kycUrl: '{{ route('trade.kyc') }}',
  bal: { demo: {{ (int) $wallet->balance }}, live: {{ (int) $liveBalance }} },
  cur: { demo: 'USD', live: '{{ $liveCurrency }}' },
};

/* ════════════════════════════════════════════════════════
   ASSET REGISTRY  (parsed from hidden DOM spans)
   ════════════════════════════════════════════════════════ */
const ASSETS = [];
document.querySelectorAll('#tsAssetData span').forEach(el => {
  ASSETS.push({
    symbol:   el.dataset.symbol,
    name:     el.dataset.name,
    payout:   parseInt(el.dataset.payout, 10) || 84,
    live:     el.dataset.live === '1',
    expiries: JSON.parse(el.dataset.expiries || '[30,60,300]'),
    minStake: parseInt(el.dataset.min, 10) || 1,
    maxStake: parseInt(el.dataset.max, 10) || 99999,
    cls:      el.dataset.class || 'crypto',
    icon:     el.dataset.icon || '',
    sel:      el.hasAttribute('data-sel'),
  });
});

/* ════════════════════════════════════════════════════════
   STATE
   ════════════════════════════════════════════════════════ */
const S = {
  asset:      (ASSETS.find(a => a.sel) || ASSETS[0] || {symbol:''}).symbol,
  interval:   '30s',
  mode:       '{{ $tradeMode ?? 'sim' }}',   // market-data mode set by admins (sim|live)
  chartType:  'candles',  // candles | line | area  (candles = TradingView-style default)
  lastCandles:[],         // cache for re-rendering on type switch
  indicators: { ma20: false, ma50: false, rsi14: false },
  muted:      localStorage.getItem('ts_muted') === '1',
  lastPrice:  null,
  placing:    false,      // guard against double-submit
  pollId:     null,       // price poll interval
  openTrades: {},         // { id: {entryPrice, expiresMs, direction, stake, pollId} }
  balance:    {{ (int) $wallet->balance }},   // current practice balance (live)
  todayPnl:   {{ (int) ($todayPnl ?? 0) }},   // today's realised P&L
};

/* ════════════════════════════════════════════════════════
   DOM SHORTCUTS
   ════════════════════════════════════════════════════════ */
const $ = id => document.getElementById(id);
const D = {
  price:      $('tsPrice'),
  priceChg:   $('tsPriceChg'),
  conn:       $('tsConn'),
  symLbl:     $('tsSymLabel'),
  nameLbl:    $('tsNameLabel'),
  buyBtn:     $('tsBuy'),
  sellBtn:    $('tsSell'),
  buyPct:     $('tsBuyPct'),
  sellPct:    $('tsSellPct'),
  buyPayout:  $('tsBuyPayout'),
  sellPayout: $('tsSellPayout'),
  stake:      $('tsStake'),
  stakePct:   $('tsStakePct'),
  stakeMin:   $('tsStakeMin'),
  balChip:    $('tsBalChip'),
  balPanel:   $('tsBalPanel'),
  balPnl:     $('tsBalPnl'),
  skel:       $('tsChartSkel'),
  offline:    $('tsOffline'),
  openBadge:  $('tsOpenBadge'),
  openList:   $('tsOpenList'),
  histList:   $('tsHistList'),
  openPanel:  $('tsOpenPanel'),
  histPanel:  $('tsHistPanel'),
};

/* ════════════════════════════════════════════════════════
   CHART
   ════════════════════════════════════════════════════════ */
let chart, candles;

function chartTheme() {
  const light = document.documentElement.getAttribute('data-theme') === 'light';
  return light
    ? { bg: '#ffffff', text: '#64748b', grid: '#e2e8f0', border: '#d8e0ea' }
    : { bg: '#080c12', text: '#4a5568', grid: '#1a2030', border: '#1c2333' };
}

function applyChartTheme() {
  if (!chart) return;
  const t = chartTheme();
  chart.applyOptions({
    layout: { background: { color: t.bg }, textColor: t.text },
    grid: { vertLines: { color: t.grid }, horzLines: { color: t.grid } },
    rightPriceScale: { borderColor: t.border },
    timeScale: { borderColor: t.border },
  });
}

function initChart() {
  const container = $('tsChart');
  const wrap = $('tsChartWrap');
  const t = chartTheme();

  chart = LightweightCharts.createChart(container, {
    width:  wrap.clientWidth  || 800,
    height: wrap.clientHeight || 400,
    layout: {
      background: { color: t.bg },
      textColor:  t.text,
      fontSize:   11,
    },
    grid: {
      vertLines: { color: t.grid },
      horzLines: { color: t.grid },
    },
    crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
    rightPriceScale: {
      borderColor: '#1c2333',
      scaleMargins: { top: 0.08, bottom: 0.08 },
    },
    timeScale: {
      borderColor:    '#1c2333',
      timeVisible:    true,
      secondsVisible: true,
      fixRightEdge:   true,
    },
    handleScroll:   true,
    handleScale:    true,
  });

  buildSeries(S.chartType);

  /* Resize chart when container changes */
  const ro = new ResizeObserver(entries => {
    const e = entries[0];
    if (e) {
      const w = e.contentRect.width;
      const h = e.contentRect.height;
      if (w > 0 && h > 0) chart.resize(w, h);
    }
  });
  ro.observe(wrap);
}

/* Create (or recreate) the price series for the given chart type. */
function buildSeries(type) {
  if (candles) { try { chart.removeSeries(candles); } catch {} candles = null; }
  if (type === 'line') {
    candles = chart.addLineSeries({ color: '#3b82f6', lineWidth: 2 });
  } else if (type === 'area') {
    candles = chart.addAreaSeries({
      lineColor: '#4d8dff', topColor: 'rgba(59,130,246,.30)', bottomColor: 'rgba(59,130,246,0)', lineWidth: 2,
      crosshairMarkerBorderColor: '#4d8dff', crosshairMarkerBackgroundColor: '#4d8dff',
    });
  } else {
    candles = chart.addCandlestickSeries({
      upColor: '#26a69a', downColor: '#ef5350',
      borderUpColor: '#26a69a', borderDownColor: '#ef5350',
      wickUpColor: '#26a69a', wickDownColor: '#ef5350',
      borderVisible: true,
    });
  }
}

/* Transform a candle array to the shape the active series expects. */
function seriesArray(arr) {
  if (S.chartType === 'candles') return arr;
  return arr.map(c => ({ time: c.time, value: c.close }));
}
function seriesPoint(c) {
  if (!c) return null;
  return S.chartType === 'candles' ? c : { time: c.time, value: c.close };
}

/* Switch chart type without a server round-trip (uses cached candles). */
function setChartType(type) {
  if (type === S.chartType) return;
  S.chartType = type;
  buildSeries(type);
  if (S.lastCandles.length) {
    candles.setData(seriesArray(S.lastCandles));
    chart.timeScale().fitContent();
  }
  /* Re-draw entry lines for open trades on the new series */
  Object.keys(entryLines).forEach(removeEntryLine);
  Object.entries(S.openTrades).forEach(([id, t]) => addEntryLine(id, t.entryPrice, t.direction));
}

/* ════════════════════════════════════════════════════════
   INDICATORS  (MA20, MA50, RSI14)
   ════════════════════════════════════════════════════════ */
const indSeries = { ma20: null, ma50: null, rsi14: null };

function sma(candles, period) {
  const out = [];
  for (let i = period - 1; i < candles.length; i++) {
    let sum = 0;
    for (let j = i - period + 1; j <= i; j++) sum += candles[j].close;
    out.push({ time: candles[i].time, value: sum / period });
  }
  return out;
}

function rsi(candles, period) {
  if (candles.length <= period) return [];
  const out = [];
  let gain = 0, loss = 0;
  for (let i = 1; i <= period; i++) {
    const d = candles[i].close - candles[i - 1].close;
    if (d >= 0) gain += d; else loss -= d;
  }
  gain /= period; loss /= period;
  const rs0 = loss === 0 ? 100 : 100 - 100 / (1 + gain / loss);
  out.push({ time: candles[period].time, value: rs0 });
  for (let i = period + 1; i < candles.length; i++) {
    const d = candles[i].close - candles[i - 1].close;
    const g = d >= 0 ? d : 0, l = d < 0 ? -d : 0;
    gain = (gain * (period - 1) + g) / period;
    loss = (loss * (period - 1) + l) / period;
    const val = loss === 0 ? 100 : 100 - 100 / (1 + gain / loss);
    out.push({ time: candles[i].time, value: val });
  }
  return out;
}

function applyIndicators() {
  const c = S.lastCandles;

  // MA20
  if (S.indicators.ma20) {
    if (!indSeries.ma20) indSeries.ma20 = chart.addLineSeries({ color: '#3b82f6', lineWidth: 1, priceLineVisible: false, lastValueVisible: false });
    indSeries.ma20.setData(sma(c, 20));
  } else if (indSeries.ma20) { chart.removeSeries(indSeries.ma20); indSeries.ma20 = null; }

  // MA50
  if (S.indicators.ma50) {
    if (!indSeries.ma50) indSeries.ma50 = chart.addLineSeries({ color: '#f59e0b', lineWidth: 1, priceLineVisible: false, lastValueVisible: false });
    indSeries.ma50.setData(sma(c, 50));
  } else if (indSeries.ma50) { chart.removeSeries(indSeries.ma50); indSeries.ma50 = null; }

  // RSI14 (separate bottom scale)
  if (S.indicators.rsi14) {
    if (!indSeries.rsi14) {
      indSeries.rsi14 = chart.addLineSeries({ color: '#a78bfa', lineWidth: 1, priceScaleId: 'rsi', priceLineVisible: false, lastValueVisible: false });
      chart.priceScale('rsi').applyOptions({ scaleMargins: { top: 0.82, bottom: 0 } });
    }
    indSeries.rsi14.setData(rsi(c, 14));
  } else if (indSeries.rsi14) { chart.removeSeries(indSeries.rsi14); indSeries.rsi14 = null; }
}

/* ════════════════════════════════════════════════════════
   SOUND FX  (Web Audio beeps + persisted mute)
   ════════════════════════════════════════════════════════ */
let audioCtx = null;
function beep(freq, durMs, type, gainVal) {
  if (S.muted) return;
  try {
    audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
    const osc = audioCtx.createOscillator(), g = audioCtx.createGain();
    osc.type = type || 'sine'; osc.frequency.value = freq;
    g.gain.value = gainVal || 0.06;
    osc.connect(g); g.connect(audioCtx.destination);
    osc.start();
    g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + durMs / 1000);
    osc.stop(audioCtx.currentTime + durMs / 1000);
  } catch {}
}
const Sound = {
  win:  () => { beep(660, 120, 'sine', 0.08); setTimeout(() => beep(880, 180, 'sine', 0.08), 110); },
  lose: () => beep(180, 260, 'sawtooth', 0.05),
  tick: () => beep(440, 60, 'square', 0.03),
};
function setMuted(m) {
  S.muted = m;
  localStorage.setItem('ts_muted', m ? '1' : '0');
  const i = document.querySelector('#tsMuteBtn i');
  if (i) i.className = m ? 'fas fa-volume-xmark' : 'fas fa-volume-high';
}

/* ════════════════════════════════════════════════════════
   GUIDED TOUR  (Driver.js)
   ════════════════════════════════════════════════════════ */
function startTour() {
  if (typeof window.driver === 'undefined' || !window.driver.js) return;
  const d = window.driver.js.driver({
    showProgress: true,
    steps: [
      { element: '#tsAssetBtn', popover: { title: 'Pick an asset', description: 'Choose what to trade — crypto, forex, commodities and more.' } },
      { element: '#tsChartWrap', popover: { title: 'Live chart', description: 'Watch the price move in real time. Switch candles/line/area and add indicators.' } },
      { element: '.ts-amt-wrap', popover: { title: 'Set your stake', description: 'Type an amount or use the % of balance buttons. See your potential profit instantly.' } },
      { element: '#tsBuy', popover: { title: 'Predict UP', description: 'Tap BUY if you think the price will rise. Your position stays open — no countdown.' } },
      { element: '#tsSell', popover: { title: 'Predict DOWN', description: 'Tap SELL if you think the price will fall.' } },
      { element: '#tsDeals', popover: { title: 'Close anytime', description: 'Your open positions show live profit/loss. Hit Close to settle at the current price.' } },
      { element: '#tsDeals', popover: { title: 'Your positions', description: 'Track open trades and your history here.' } },
    ],
    onDestroyed: () => localStorage.setItem('ts_tour_done', '1'),
  });
  d.drive();
}

const entryLines = {};

function addEntryLine(id, price, dir) {
  if (!candles) return;
  try {
    const line = candles.createPriceLine({
      price,
      color:            dir === 'up' ? '#22c55e99' : '#ef444499',
      lineWidth:        1,
      lineStyle:        LightweightCharts.LineStyle.Dashed,
      axisLabelVisible: true,
      title:            `#${id}`,
    });
    entryLines[id] = line;
  } catch {}
}

function removeEntryLine(id) {
  if (candles && entryLines[id]) {
    try { candles.removePriceLine(entryLines[id]); } catch {}
    delete entryLines[id];
  }
}

/* ════════════════════════════════════════════════════════
   PRICE FORMATTING
   ════════════════════════════════════════════════════════ */
function fmtPrice(p) {
  if (p == null || p === 0) return '—';
  const n = parseFloat(p);
  return n.toLocaleString('en-US', {
    minimumFractionDigits: n < 10 ? 5 : n < 100 ? 4 : 2,
    maximumFractionDigits: n < 10 ? 5 : n < 100 ? 4 : 2,
  });
}

function fmtMoney(n) {
  return parseInt(n, 10).toLocaleString('en-US');
}

/* ════════════════════════════════════════════════════════
   PRICE UPDATE  (called on every poll tick)
   ════════════════════════════════════════════════════════ */
function updatePrice(newP) {
  const prev = S.lastPrice;
  S.lastPrice = newP;
  const up = !prev || newP >= prev;

  D.price.textContent = fmtPrice(newP);
  D.price.className = up ? 'flash-up' : 'flash-dn';

  if (prev && prev !== newP) {
    const chg = ((newP - prev) / prev * 100);
    D.priceChg.textContent = (chg >= 0 ? '+' : '') + chg.toFixed(4) + '%';
    D.priceChg.style.color = up ? 'var(--up)' : 'var(--dn)';
  }
}

function setConn(txt, cls) {
  D.conn.textContent = txt;
  D.conn.className = cls || '';
}

/* ════════════════════════════════════════════════════════
   FEED  (initial historical candles)
   ════════════════════════════════════════════════════════ */
function loadFeed() {
  if (!S.asset) { setConn('No asset', 'err'); return; }
  setConn('● Connecting', '');
  showSkeleton();

  const q = new URLSearchParams({
    asset: S.asset, interval: S.interval, mode: effMode(), limit: 200,
  });

  fetch(`${CFG.feed}?${q}`, { credentials: 'same-origin' })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => {
      Object.keys(entryLines).forEach(removeEntryLine);
      S.lastCandles = data.candles || [];
      candles.setData(seriesArray(S.lastCandles));
      applyIndicators();
      chart.timeScale().fitContent();
      hideSkeleton();

      if (data.price) updatePrice(data.price);
      setConn('Live', 'live');
      startPricePolling();
    })
    .catch(err => {
      setConn('● Error', 'err');
      console.error('[feed]', err);
      setTimeout(loadFeed, 5000);   // retry in 5s
    });
}

/* ════════════════════════════════════════════════════════
   PRICE POLLING  (every 1 s)
   ════════════════════════════════════════════════════════ */
function startPricePolling() {
  if (S.pollId) clearInterval(S.pollId);
  S.pollId = setInterval(pollPrice, 1000);
}

function pollPrice() {
  const q = new URLSearchParams({
    asset: S.asset, interval: S.interval, mode: effMode(),
  });
  fetch(`${CFG.price}?${q}`, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      if (data.candle) {
        const arr = S.lastCandles;
        if (arr.length && arr[arr.length - 1].time === data.candle.time) arr[arr.length - 1] = data.candle;
        else arr.push(data.candle);
        candles.update(seriesPoint(data.candle));
      }
      if (data.price != null) {
        updatePrice(data.price);
        updateOpenPnl(data.price);
      }
    })
    .catch(() => {});
}

/* ════════════════════════════════════════════════════════
   RELOAD  (when asset / interval / mode changes)
   ════════════════════════════════════════════════════════ */
function reload() {
  if (S.pollId) { clearInterval(S.pollId); S.pollId = null; }
  candles.setData([]);
  S.lastCandles = [];
  S.lastPrice = null;
  D.price.textContent = '—';
  D.priceChg.textContent = '';
  loadFeed();
}

/* ════════════════════════════════════════════════════════
   ASSET DROPDOWN
   ════════════════════════════════════════════════════════ */
function buildAssetList(q) {
  const list = $('tsAssetList');
  const lc = q.toLowerCase();
  const filtered = lc
    ? ASSETS.filter(a => a.symbol.toLowerCase().includes(lc) || a.name.toLowerCase().includes(lc))
    : ASSETS;

  if (!filtered.length) {
    list.innerHTML = '<div class="ts-empty" style="padding:16px;">No assets found.</div>';
    return;
  }

  list.innerHTML = filtered.map(a => `
    <div class="ts-aopt ${a.symbol === S.asset ? 'on' : ''}" data-sym="${a.symbol}">
      <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        ${assetIconHtml(a)}
        <div style="min-width:0;">
          <div class="ts-aopt-sym">${a.symbol}</div>
          <div class="ts-aopt-name">${a.name}</div>
        </div>
      </div>
      ${a.live ? '<span class="ts-aopt-live">LIVE</span>' : ''}
    </div>`).join('');

  list.querySelectorAll('.ts-aopt').forEach(el =>
    el.addEventListener('click', () => pickAsset(el.dataset.sym))
  );
}

function openDropdown() {
  const dd = $('tsAssetDD');
  dd.classList.add('open');
  const search = $('tsAssetSearch');
  search.value = '';
  buildAssetList('');
  setTimeout(() => search.focus(), 40);
}

function closeDropdown() {
  $('tsAssetDD').classList.remove('open');
}

function pickAsset(sym) {
  const asset = ASSETS.find(a => a.symbol === sym);
  if (!asset) return;
  S.asset = sym;
  D.symLbl.textContent = sym;
  D.nameLbl.textContent = asset.name;
  setTopIcon(asset);
  updatePayout(asset);
  closeDropdown();

  reload();
}

/* Effective market-data mode for a request: only use LIVE when admins enabled it
   AND the asset supports live prices — otherwise fall back to the simulator. */
function effMode() {
  const a = currentAsset();
  return (S.mode === 'live' && a && a.live) ? 'live' : 'sim';
}

/* ════════════════════════════════════════════════════════
   PAYOUT DISPLAY
   ════════════════════════════════════════════════════════ */
function currentAsset() {
  return ASSETS.find(a => a.symbol === S.asset) || null;
}

/* Asset icon: image if icon_url set, else a coloured monogram. */
function assetMonogram(sym) {
  const base = (sym || '?').replace(/[^A-Za-z]/g, '');
  return (base.slice(0, 2) || sym.slice(0, 2)).toUpperCase();
}
function assetIconHtml(asset, small) {
  if (!asset) return '';
  const cls = 'ts-aico ' + (asset.cls || 'crypto') + (small ? ' sm' : '');
  if (asset.icon) return `<span class="${cls}"><img src="${asset.icon}" alt=""></span>`;
  return `<span class="${cls}">${assetMonogram(asset.symbol)}</span>`;
}
function setTopIcon(asset) {
  const el = $('tsTopIcon');
  if (!el || !asset) return;
  el.className = 'ts-aico ' + (asset.cls || 'crypto');
  el.innerHTML = asset.icon ? `<img src="${asset.icon}" alt="">` : assetMonogram(asset.symbol);
}

function updatePayout(asset) {
  asset = asset || currentAsset();
  if (!asset) return;
  D.buyPct.textContent  = asset.payout + '%';
  D.sellPct.textContent = asset.payout + '%';
  updateStakeUI();
}

/* Recompute % of balance, min hint, and dynamic payout amounts. */
function updateStakeUI() {
  const asset = currentAsset();
  const stake = Math.max(0, parseInt(D.stake.value, 10) || 0);
  const bal   = Math.max(0, S.balance || 0);

  /* % of balance indicator */
  const pct = bal > 0 ? Math.round((stake / bal) * 100) : 0;
  if (D.stakePct) {
    D.stakePct.textContent = `${pct}% of balance`;
    D.stakePct.style.color = pct > 50 ? 'var(--dn)' : pct > 25 ? 'var(--gold)' : 'var(--up)';
  }

  /* Min hint */
  if (D.stakeMin && asset) D.stakeMin.textContent = `Min: ${asset.minStake}`;

  /* Dynamic potential profit */
  if (asset) {
    const profit = Math.round(stake * asset.payout / 100);
    if (D.buyPayout)  D.buyPayout.textContent  = '+' + fmtMoney(profit);
    if (D.sellPayout) D.sellPayout.textContent = '+' + fmtMoney(profit);
  }
}

/* Clamp + apply a stake value; shake if it exceeds max. */
function applyStake(val) {
  const asset = currentAsset() || { minStake: 1, maxStake: 99999 };
  let v = parseInt(val, 10);
  if (isNaN(v)) v = asset.minStake;
  let shake = false;
  if (v > asset.maxStake) { v = asset.maxStake; shake = true; }
  if (v < asset.minStake) v = asset.minStake;
  D.stake.value = v;
  if (shake) {
    D.stake.parentElement.classList.remove('ts-shake');
    void D.stake.parentElement.offsetWidth;       // reflow to restart animation
    D.stake.parentElement.classList.add('ts-shake');
  }
  updateStakeUI();
}

/* ════════════════════════════════════════════════════════
   PLACE TRADE  (one-click BUY or SELL)
   ════════════════════════════════════════════════════════ */
function placeTrade(dir) {
  if (S.placing) return;
  // Identity verification is required before any buying or selling.
  if (!ACCT.kycApproved) {
    showToast('Verify your identity to start trading.', 'warn');
    setTimeout(() => { window.location = ACCT.kycUrl; }, 900);
    return;
  }
  if (!S.asset) { showToast('No asset selected.', 'warn'); return; }

  const stake = parseInt(D.stake.value, 10);
  if (!stake || stake < 1) { showToast('Enter a valid amount.', 'warn'); return; }

  const asset = ASSETS.find(a => a.symbol === S.asset);
  if (asset) {
    if (stake < asset.minStake) {
      showToast(`Minimum stake: ${asset.minStake}.`, 'warn'); return;
    }
    if (stake > asset.maxStake) {
      showToast(`Maximum stake: ${asset.maxStake}.`, 'warn'); return;
    }
  }

  S.placing = true;
  const btn = dir === 'up' ? D.buyBtn : D.sellBtn;
  btn.classList.add('loading');
  btn.disabled = true;

  fetch(CFG.place, {
    method:      'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type':  'application/json',
      'X-CSRF-TOKEN':  CFG.csrf,
      'Accept':        'application/json',
    },
    body: JSON.stringify({
      asset:          S.asset,
      mode:           effMode(),
      account:        ACCT.current,
      direction:      dir,
      stake,
    }),
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      updateBalance(data.balance);
      addOpenTrade(data.trade_id, data.entry_price, dir, stake, data.payout_percent, S.asset);
      Sound.tick();
      switchTab('open');
      const tag = ACCT.current === 'live' ? '🔴 LIVE' : '';
      showToast(`#${data.trade_id} opened ${tag} — ${dir === 'up' ? '▲ BUY' : '▼ SELL'} ${fmtMoney(stake)} ${ACCT.cur[ACCT.current]}`, 'info');
    } else {
      showToast(data.message || 'Could not open trade.', 'err');
    }
  })
  .catch(() => showToast('Network error. Try again.', 'err'))
  .finally(() => {
    S.placing = false;
    btn.classList.remove('loading');
    btn.disabled = false;
  });
}

/* ════════════════════════════════════════════════════════
   OPEN TRADE TRACKING
   ════════════════════════════════════════════════════════ */
function addOpenTrade(id, entryPrice, dir, stake, payoutPct, symbol) {
  // Open positions have no countdown — they stay open until the trader closes them.
  S.openTrades[id] = { entryPrice, direction: dir, stake, payoutPct: payoutPct || 0, lastPrice: entryPrice, symbol: symbol || S.asset };

  if ((symbol || S.asset) === S.asset) addEntryLine(id, entryPrice, dir);
  renderOpenTrades();
}

/* Load the open positions for the active account (demo|live). On page load and
   whenever the user switches accounts — positions are kept separate per account. */
function restoreOpenPositions(switchTabToOpen) {
  // Clear any currently-shown positions + their chart entry lines.
  Object.keys(S.openTrades).forEach(id => removeEntryLine(id));
  S.openTrades = {};

  fetch(CFG.openList + '?account=' + ACCT.current, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      const list = (res && res.positions) ? res.positions : [];
      if (res && res.balance != null) updateBalance(res.balance);
      list.forEach(t => {
        S.openTrades[t.id] = {
          entryPrice: t.entry_price, direction: t.direction, stake: t.stake,
          payoutPct: t.payout_percent, lastPrice: t.entry_price, symbol: t.symbol,
        };
        if (t.symbol === S.asset) addEntryLine(t.id, t.entry_price, t.direction);
      });
      renderOpenTrades();
      if (switchTabToOpen && list.length) switchTab('open');
    })
    .catch(() => {});
}

/* ════════════════════════════════════════════════════════
   DEMO / LIVE ACCOUNT SWITCH
   ════════════════════════════════════════════════════════ */
function applyAccountUI() {
  const live = ACCT.current === 'live';
  document.querySelectorAll('.ts-acct-btn').forEach(b => b.classList.toggle('on', b.dataset.acct === ACCT.current));
  const pill = $('tsBalPill'), badge = $('tsBalBadge'), cur = $('tsBalCur');
  if (pill)  pill.classList.toggle('live', live);
  if (badge) { badge.classList.toggle('live', live); badge.textContent = live ? 'LIVE' : 'DEMO'; }
  if (cur)   cur.textContent = ACCT.cur[ACCT.current];
  const resetBtn = $('tsResetBtn'), depBtn = $('tsDepositBtn');
  if (resetBtn) resetBtn.style.display = live ? 'none' : '';
  if (depBtn)   depBtn.style.display   = live ? '' : 'none';
  // Deals-panel balance card
  const lbl = $('tsBalPanelLbl'), unit = $('tsBalPanelUnit'), sub = $('tsBalPanelSub'), card = $('tsBalCard');
  if (lbl)  lbl.textContent  = live ? 'Live Balance' : 'Practice Balance';
  if (unit) unit.textContent = ACCT.cur[ACCT.current];
  if (sub)  sub.textContent  = live ? 'Real funds · trade responsibly' : 'Virtual funds · no real money';
  if (card) card.classList.toggle('live', live);
}

/* Commit the switch: update UI, then refresh balance + positions from server. */
function setAccount(acct) {
  if (acct !== 'demo' && acct !== 'live') return;
  ACCT.current = acct;
  applyAccountUI();
  S.balance = null;                    // force the next updateBalance to repaint
  updateBalance(ACCT.bal[acct] || 0);  // optimistic from cache
  fetch(CFG.account + '?account=' + acct, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(d => { if (d && d.balance != null) { ACCT.bal[acct] = d.balance; updateBalance(d.balance); } })
    .catch(() => {});
  restoreOpenPositions(true);
  loadHistory();
  if (acct === 'live' && (ACCT.bal.live || 0) <= 0) {
    showToast('Your Live account is empty — deposit real funds to start live trading.', 'warn');
  }
}

/* User tapped DEMO/LIVE — live requires KYC + explicit confirmation. */
function requestAccount(acct) {
  if (acct === ACCT.current) return;
  if (acct === 'live') {
    if (!ACCT.kycApproved) {
      showToast('Verify your identity first to trade live.', 'warn');
      applyAccountUI();                         // keep DEMO selected
      setTimeout(() => { window.location = ACCT.kycUrl; }, 900);
      return;
    }
    const m = $('tsLmBal');
    if (m) m.textContent = fmtMoney(ACCT.bal.live || 0) + ' ' + ACCT.cur.live;
    $('tsLiveModal').classList.add('show');
  } else {
    setAccount('demo');
  }
}
function closeLiveModal() { const o = $('tsLiveModal'); if (o) o.classList.remove('show'); applyAccountUI(); }
window.closeLiveModal = closeLiveModal;

/* Live mark-to-market: refresh winning/losing status for positions on this asset. */
function updateOpenPnl(price) {
  Object.entries(S.openTrades).forEach(([id, t]) => {
    if (t.symbol !== S.asset) return;
    t.lastPrice = price;
    const el = document.getElementById(`pl-${id}`);
    if (!el) return;
    const diff = price - t.entryPrice;
    const winning = t.direction === 'up' ? diff > 0 : diff < 0;
    const flat = diff === 0;
    el.textContent = flat ? '● Even' : (winning ? '▲ Winning' : '▼ Losing');
    el.className = 'ts-deal-pl ' + (flat ? 'tie' : (winning ? 'pos' : 'neg'));
    const live = document.getElementById(`px-${id}`);
    if (live) live.textContent = fmtPrice(price);
  });
}

function renderOpenTrades() {
  const entries = Object.entries(S.openTrades);
  const cnt = entries.length;
  D.openBadge.textContent = cnt ? `(${cnt})` : '';

  // Mobile FAB badge (positions count)
  const fb = document.getElementById('tsFabBadge');
  if (fb) { fb.textContent = cnt; fb.classList.toggle('show', cnt > 0); }

  if (!cnt) {
    D.openList.innerHTML = '<div class="ts-empty">No open positions.</div>';
    return;
  }

  D.openList.innerHTML = entries.map(([id, t]) => {
    const diff = (t.lastPrice ?? t.entryPrice) - t.entryPrice;
    const winning = t.direction === 'up' ? diff > 0 : diff < 0;
    const flat = diff === 0;
    const plCls = flat ? 'tie' : (winning ? 'pos' : 'neg');
    const plTxt = flat ? '● Even' : (winning ? '▲ Winning' : '▼ Losing');
    return `
    <div class="ts-deal" id="od-${id}">
      <div class="ts-deal-ico ${t.direction}">${t.direction === 'up' ? '▲' : '▼'}</div>
      <div class="ts-deal-info">
        <div class="ts-deal-sym">${t.symbol || S.asset}</div>
        <div class="ts-deal-meta">
          ${fmtMoney(t.stake)} USD &middot; ${t.direction.toUpperCase()}<br>
          Entry ${fmtPrice(t.entryPrice)} &middot; Now <span id="px-${id}">${fmtPrice(t.lastPrice ?? t.entryPrice)}</span>
        </div>
      </div>
      <div class="ts-deal-right">
        <div class="ts-deal-pl ${plCls}" id="pl-${id}">${plTxt}</div>
        <button class="ts-close-btn" type="button" onclick="closeTrade(${id})" id="close-${id}" data-tip="Close this position now at the live price" data-tip-dir="up-left">Close</button>
      </div>
    </div>`;
  }).join('');
}

/* ════════════════════════════════════════════════════════
   CLOSE A POSITION (manual settle at the live price)
   ════════════════════════════════════════════════════════ */
function closeTrade(tradeId) {
  const t = S.openTrades[tradeId];
  if (!t || t.closing) return;
  t.closing = true;
  const btn = document.getElementById(`close-${tradeId}`);
  if (btn) { btn.disabled = true; btn.textContent = '…'; }

  fetch(`${CFG.show}/${tradeId}/close`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json' },
  })
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        if (btn) { btn.disabled = false; btn.textContent = 'Close'; }
        t.closing = false;
        showToast(data.message || 'Could not close.', 'err');
        return;
      }
      removeEntryLine(tradeId);
      const stake = (S.openTrades[tradeId] || {}).stake || 0;
      delete S.openTrades[tradeId];
      renderOpenTrades();
      updateBalance(data.balance);

      const payout = data.payout_amount || 0;
      bumpDailyPnl(payout - stake);

      if (data.status === 'won') {
        showToast(`✓ #${tradeId} closed — WON +${fmtMoney(payout - stake)} USD`, 'win');
        fireConfetti();
        Sound.win();
      } else if (data.status === 'lost') {
        showToast(`✗ #${tradeId} closed — lost ${fmtMoney(stake)} USD`, 'lose');
        shakeBalance();
        Sound.lose();
      } else {
        showToast(`#${tradeId} closed — stake returned.`, 'info');
      }

      loadHistory();
    })
    .catch(() => {});
}

/* ════════════════════════════════════════════════════════
   HISTORY
   ════════════════════════════════════════════════════════ */
function loadHistory() {
  fetch(CFG.history + '?account=' + ACCT.current, { credentials: 'same-origin' })
    .then(r => r.json())
    .then(data => {
      const items = (data.data || []).slice(0, 20);
      if (!items.length) {
        D.histList.innerHTML = '<div class="ts-empty">No trades yet.</div>';
        return;
      }
      D.histList.innerHTML = items.map(t => {
        const dir    = (t.direction || 'up').toLowerCase();
        const won    = t.status === 'won';
        const lost   = t.status === 'lost';
        const plSign = won ? `+${fmtMoney(t.payout_amount)}` : lost ? `-${fmtMoney(t.stake)}` : '±0';
        const plCls  = won ? 'pos' : lost ? 'neg' : 'tie';
        return `<div class="ts-deal">
          <div class="ts-deal-ico ${dir}">${dir === 'up' ? '▲' : '▼'}</div>
          <div class="ts-deal-info">
            <div class="ts-deal-sym">${t.asset?.symbol ?? '—'}</div>
            <div class="ts-deal-meta">${fmtMoney(t.stake)} USD &middot; ${dir.toUpperCase()}</div>
          </div>
          <div class="ts-deal-right">
            <div class="ts-deal-pl ${plCls}">${plSign}</div>
            <div class="ts-deal-status ${t.status}">${t.status.toUpperCase()}</div>
          </div>
        </div>`;
      }).join('');
    })
    .catch(() => {});
}

/* ════════════════════════════════════════════════════════
   BALANCE
   ════════════════════════════════════════════════════════ */
function updateBalance(bal) {
  if (bal == null) return;
  const prev = S.balance;
  S.balance = parseInt(bal, 10);
  ACCT.bal[ACCT.current] = S.balance;   // remember per-account balance
  const v = fmtMoney(bal);
  D.balChip.textContent  = v;
  D.balPanel.textContent = v;

  /* Pop + colour flash on change */
  if (prev != null && prev !== S.balance) {
    const up = S.balance > prev;
    D.balPanel.classList.remove('ts-pop', 'ts-bal-flash-up', 'ts-bal-flash-dn');
    void D.balPanel.offsetWidth;
    D.balPanel.classList.add('ts-pop', up ? 'ts-bal-flash-up' : 'ts-bal-flash-dn');
    setTimeout(() => D.balPanel.classList.remove('ts-bal-flash-up', 'ts-bal-flash-dn'), 700);
  }
  updateStakeUI();
}

/* Add a settled trade's net result to today's P&L and re-render. */
function bumpDailyPnl(delta) {
  S.todayPnl += delta;
  if (!D.balPnl) return;
  const p = S.todayPnl;
  D.balPnl.className = 'ts-bal-pnl ' + (p > 0 ? 'pos' : p < 0 ? 'neg' : 'flat');
  D.balPnl.textContent = `Today: ${p > 0 ? '+' : ''}${fmtMoney(p)} USD`;
}

/* ════════════════════════════════════════════════════════
   TABS
   ════════════════════════════════════════════════════════ */
function switchTab(tab) {
  const isOpen = tab === 'open';
  $('tabOpen').classList.toggle('on', isOpen);
  $('tabHist').classList.toggle('on', !isOpen);
  D.openPanel.hidden = !isOpen;
  D.histPanel.hidden =  isOpen;
  if (!isOpen) loadHistory();
}

/* ════════════════════════════════════════════════════════
   HELPERS
   ════════════════════════════════════════════════════════ */
function fmtTime(s) {
  const m = Math.floor(s / 60);
  return (m ? m + ':' : '') + String(s % 60).padStart(2, '0');
}

function showToast(msg, type) {
  const el = document.createElement('div');
  el.className = `ts-toast ${type}`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

/* Win celebration — confetti from the right deals panel. */
function fireConfetti() {
  if (typeof confetti !== 'function') return;
  const panel = $('tsDeals');
  let origin = { x: 0.85, y: 0.4 };
  if (panel) {
    const r = panel.getBoundingClientRect();
    origin = { x: (r.left + r.width / 2) / window.innerWidth, y: 0.35 };
  }
  confetti({ particleCount: 90, spread: 70, startVelocity: 38, origin,
    colors: ['#00c97b', '#f59e0b', '#22c55e', '#ffffff'] });
}

/* Loss feedback — shake the balance display. */
function shakeBalance() {
  [D.balPanel, D.balChip].forEach(el => {
    if (!el) return;
    el.classList.remove('ts-shake');
    void el.offsetWidth;
    el.classList.add('ts-shake');
  });
}

/* Chart loading skeleton. */
function showSkeleton() { if (D.skel) D.skel.classList.remove('hide'); }
function hideSkeleton() { if (D.skel) D.skel.classList.add('hide'); }

/* Network offline banner. */
function setOnline(isOnline) {
  if (!D.offline) return;
  D.offline.classList.toggle('show', !isOnline);
  if (!isOnline) setConn('● Offline', 'err');
}

/* ════════════════════════════════════════════════════════
   BOOT
   ════════════════════════════════════════════════════════ */
function boot() {
  /* Guard: LightweightCharts must be loaded */
  if (typeof LightweightCharts === 'undefined') {
    document.getElementById('tsChart').innerHTML =
      '<div style="color:#ef4444;padding:20px;font-size:.8rem;">Chart library failed to load. Please refresh.</div>';
    return;
  }

  if (!ASSETS.length) {
    showToast('No assets found. Add assets in admin.', 'warn');
  }

  initChart();

  /* Asset button + dropdown */
  $('tsAssetBtn').addEventListener('click', e => {
    e.stopPropagation();
    $('tsAssetDD').classList.contains('open') ? closeDropdown() : openDropdown();
  });
  $('tsAssetSearch').addEventListener('input', function () { buildAssetList(this.value); });
  document.addEventListener('click', e => {
    if (!$('tsAssetDD').contains(e.target) && !$('tsAssetBtn').contains(e.target))
      closeDropdown();
  });

  /* Interval buttons */
  $('tsIntBtns').querySelectorAll('.ts-int-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      $('tsIntBtns').querySelectorAll('.ts-int-btn').forEach(b => b.classList.remove('on'));
      this.classList.add('on');
      S.interval = this.dataset.iv;
      reload();
    });
  });

  /* Chart type buttons (no refetch — re-render cached data) */
  $('tsTypeBtns').querySelectorAll('.ts-int-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      $('tsTypeBtns').querySelectorAll('.ts-int-btn').forEach(b => b.classList.remove('on'));
      this.classList.add('on');
      setChartType(this.dataset.type);
    });
  });

  /* Indicator toggles */
  $('tsIndBtns').querySelectorAll('.ts-int-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const key = this.dataset.ind;
      S.indicators[key] = !S.indicators[key];
      this.classList.toggle('on', S.indicators[key]);
      applyIndicators();
    });
  });

  /* Sound mute toggle */
  setMuted(S.muted);
  $('tsMuteBtn').addEventListener('click', () => setMuted(!S.muted));

  /* Recolor chart when theme changes */
  window.addEventListener('cxtheme', applyChartTheme);

  /* Guided tour button */
  $('tsTourBtn').addEventListener('click', startTour);

  /* Reset practice balance */
  const resetBtn = $('tsResetBtn');
  if (resetBtn) resetBtn.addEventListener('click', () => {
    if (!confirm('Reset your practice balance to the starting amount?')) return;
    resetBtn.disabled = true;
    fetch(CFG.reset, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': CFG.csrf, 'Accept': 'application/json' },
    }).then(r => r.json()).then(d => {
      if (d.balance != null) { updateBalance(d.balance); showToast('Balance reset to ' + fmtMoney(d.balance) + ' USD', 'info'); }
      else showToast(d.message || 'Reset unavailable.', 'warn');
    }).catch(() => showToast('Reset failed. Try again.', 'err'))
      .finally(() => { resetBtn.disabled = false; });
  });

  /* BUY / SELL — one-click trade */
  D.buyBtn.addEventListener('click',  () => placeTrade('up'));
  D.sellBtn.addEventListener('click', () => placeTrade('down'));

  /* Demo / Live account switch */
  document.querySelectorAll('.ts-acct-btn').forEach(b =>
    b.addEventListener('click', () => requestAccount(b.dataset.acct)));
  $('tsLmCancel')?.addEventListener('click', closeLiveModal);
  $('tsLmGo')?.addEventListener('click', () => { $('tsLiveModal').classList.remove('show'); setAccount('live'); });
  $('tsLiveModal')?.addEventListener('click', e => { if (e.target.id === 'tsLiveModal') closeLiveModal(); });

  /* Amount +/- (step by asset min_stake, min 1) */
  const step = () => Math.max(1, currentAsset()?.minStake || 1);
  $('tsAmtInc').addEventListener('click', () => applyStake((parseInt(D.stake.value, 10) || 0) + step()));
  $('tsAmtDec').addEventListener('click', () => applyStake((parseInt(D.stake.value, 10) || 0) - step()));

  /* Live update as the user types */
  D.stake.addEventListener('input', updateStakeUI);

  /* Arrow keys ↑/↓ adjust by min_stake */
  D.stake.addEventListener('keydown', e => {
    if (e.key === 'ArrowUp')   { e.preventDefault(); applyStake((parseInt(D.stake.value, 10) || 0) + step()); }
    if (e.key === 'ArrowDown') { e.preventDefault(); applyStake((parseInt(D.stake.value, 10) || 0) - step()); }
  });

  /* Absolute quick-stake buttons */
  document.querySelectorAll('.ts-qs-btn').forEach(btn => {
    btn.addEventListener('click', () => applyStake(btn.dataset.q));
  });

  /* % of balance quick-stake buttons */
  document.querySelectorAll('.ts-qs-pct-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const pct = parseInt(btn.dataset.pct, 10);
      applyStake(Math.floor((S.balance || 0) * pct / 100));
    });
  });

  /* Network connectivity */
  window.addEventListener('online',  () => { setOnline(true);  loadFeed(); });
  window.addEventListener('offline', () => setOnline(false));
  setOnline(navigator.onLine);

  /* Tabs */
  $('tabOpen').addEventListener('click', () => switchTab('open'));
  $('tabHist').addEventListener('click', () => switchTab('history'));

  /* Init asset UI */
  const initAsset = ASSETS.find(a => a.sel) || ASSETS[0];
  if (initAsset) {
    setTopIcon(initAsset);
    updatePayout(initAsset);
  }
  updateStakeUI();

  /* Start data */
  applyAccountUI();
  loadFeed();
  loadHistory();
  restoreOpenPositions(true);
  setInterval(loadHistory, 20000);

  /* Onboarding welcome (step 3) + first-visit guided tour */
  const isWelcome = new URLSearchParams(location.search).get('welcome') === '1';
  if (isWelcome) {
    setTimeout(() => showToast('Welcome to Cryptocoinex! You have 10,000 USD to practise with. 🎉', 'win'), 800);
  }
  if (isWelcome || !localStorage.getItem('ts_tour_done')) {
    setTimeout(startTour, 1400);
  }
}

/* Expose handlers used by inline onclick (this whole file is an IIFE). */
window.closeTrade = closeTrade;

/* Mobile: open/close the positions & balance bottom-sheet. */
function toggleDeals(force) {
  const sheet = document.getElementById('tsDeals');
  const back  = document.getElementById('tsDealsBackdrop');
  if (!sheet) return;
  const open = force === undefined ? !sheet.classList.contains('show') : !!force;
  sheet.classList.toggle('show', open);
  if (back) back.classList.toggle('show', open);
  if (open) switchTab('open');
}
window.toggleDeals = toggleDeals;

/* Run when DOM is ready (handles both sync and async load) */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}

})();
</script>
@endpush
