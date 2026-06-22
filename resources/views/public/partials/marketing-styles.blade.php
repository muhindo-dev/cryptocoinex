<style>
  /* ── HERO ─────────────────────────────────────────── */
  .hero{position:relative;padding:64px 0 40px;}
  .hero-grid{display:grid;grid-template-columns:1.04fr .96fr;gap:54px;align-items:center;}
  @media(max-width:920px){.hero-grid{grid-template-columns:1fr;gap:34px;} .hero-visual{order:-1;}}
  .h-eyebrow{margin-bottom:22px;}
  .h-title{font-size:clamp(2.6rem,5.4vw,4.3rem);font-weight:800;line-height:1.02;letter-spacing:-.04em;}
  .h-title .grad{background:linear-gradient(110deg,var(--gold) 10%,#ffe1a6 50%,var(--gold) 90%);-webkit-background-clip:text;background-clip:text;color:transparent;}
  .h-lead{font-size:clamp(1.02rem,1.5vw,1.18rem);color:var(--tx2);max-width:500px;margin:22px 0 0;line-height:1.6;}
  .h-cta{display:flex;gap:13px;margin:30px 0 22px;flex-wrap:wrap;}
  .h-cta .btn{padding:15px 26px;font-size:.98rem;}
  .h-trust{display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
  .h-avs{display:flex;}
  .h-avs span{width:32px;height:32px;border-radius:50%;border:2px solid var(--bg);margin-left:-9px;display:flex;align-items:center;
    justify-content:center;font-size:.66rem;font-weight:800;color:#1a1205;}
  .h-avs span:first-child{margin-left:0;}
  .h-trust-tx{font-size:.82rem;color:var(--tx3);} .h-trust-tx b{color:var(--tx);} .h-stars{color:var(--gold);font-size:.74rem;letter-spacing:1px;}

  /* Live trading card */
  .tcard{position:relative;border-radius:22px;overflow:hidden;background:linear-gradient(180deg,rgba(20,26,38,.9),rgba(10,14,22,.92));
    border:1px solid var(--brd);box-shadow:0 40px 90px -30px rgba(0,0,0,.8),0 1px 0 var(--hi) inset;backdrop-filter:blur(14px);}
  .tcard::before{content:'';position:absolute;inset:0;border-radius:22px;padding:1px;background:linear-gradient(160deg,rgba(245,166,35,.4),transparent 35%);
    -webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;}
  .tc-top{display:flex;align-items:center;gap:11px;padding:15px 18px;border-bottom:1px solid var(--brd);}
  .tc-ic{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#f7931a,#b45309);display:flex;align-items:center;justify-content:center;
    font-size:.7rem;font-weight:900;color:#fff;}
  .tc-sym{font-weight:800;font-size:.9rem;line-height:1.1;} .tc-sub{font-size:.62rem;color:var(--tx3);}
  .tc-live{font-size:.6rem;font-weight:800;color:var(--grn);background:rgba(22,210,145,.12);padding:3px 8px;border-radius:6px;display:inline-flex;gap:5px;align-items:center;}
  .tc-live .d{width:6px;height:6px;border-radius:50%;background:var(--grn);animation:pulse 1.6s infinite;}
  .tc-price{margin-left:auto;text-align:right;}
  .tc-price .p{font-size:1.18rem;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:-.01em;transition:color .25s;}
  .tc-price .c{font-size:.64rem;font-weight:700;}
  .tc-chart{height:188px;position:relative;}
  .tc-chart svg{position:absolute;inset:0;width:100%;height:100%;}
  .tc-tag{position:absolute;right:10px;background:rgba(77,141,255,.92);color:#fff;font-size:.6rem;font-weight:800;padding:2px 7px;border-radius:5px;
    transform:translateY(-50%);font-variant-numeric:tabular-nums;box-shadow:0 4px 12px rgba(0,0,0,.4);}
  .tc-pos{display:flex;align-items:center;gap:10px;padding:11px 16px;border-top:1px solid var(--brd);}
  .tc-pos .av{width:28px;height:28px;border-radius:8px;background:rgba(22,210,145,.16);color:var(--grn);display:flex;align-items:center;justify-content:center;font-size:.7rem;}
  .tc-pos .meta{font-size:.66rem;color:var(--tx3);line-height:1.35;} .tc-pos .meta b{color:var(--tx2);}
  .tc-pos .pnl{margin-left:auto;font-weight:800;font-size:.84rem;font-variant-numeric:tabular-nums;color:var(--grn);}
  .tc-bot{display:flex;gap:11px;padding:14px 16px;border-top:1px solid var(--brd);}
  .tc-btn{flex:1;border-radius:12px;padding:13px;font-weight:900;font-size:.82rem;color:#fff;display:flex;align-items:center;justify-content:center;gap:7px;
    box-shadow:0 8px 22px -8px rgba(0,0,0,.6);}
  .tc-sell{background:linear-gradient(175deg,#ff5a76,#c0293e);} .tc-buy{background:linear-gradient(175deg,#1ad79a,#089f6a);}
  .tc-btn .pct{opacity:.8;font-weight:700;font-size:.72rem;}
  .tc-float{position:absolute;z-index:3;border-radius:13px;padding:10px 13px;background:rgba(12,17,27,.92);border:1px solid var(--brd2);
    backdrop-filter:blur(10px);box-shadow:0 16px 40px -10px rgba(0,0,0,.7);display:flex;align-items:center;gap:10px;animation:floaty 5s ease-in-out infinite;}
  .tc-float .i{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.8rem;}
  .tc-float .t{font-size:.64rem;color:var(--tx3);} .tc-float .v{font-size:.82rem;font-weight:800;}
  .tf-win{top:-18px;right:-14px;} .tf-win .i{background:rgba(22,210,145,.16);color:var(--grn);}
  .tf-streak{bottom:-16px;left:-16px;animation-delay:-2.5s;} .tf-streak .i{background:rgba(245,166,35,.16);color:var(--gold);}
  @keyframes floaty{0%,100%{transform:translateY(0);}50%{transform:translateY(-9px);}}
  @media(max-width:520px){.tc-float{display:none;}}

  /* ── Marquee ──────────────────────────────────────── */
  .marquee{margin:30px 0 12px;border-top:1px solid var(--brd);border-bottom:1px solid var(--brd);padding:16px 0;overflow:hidden;position:relative;
    -webkit-mask:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent);mask:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent);}
  .mq-track{display:flex;gap:46px;width:max-content;animation:mq 34s linear infinite;}
  .mq-item{display:flex;align-items:center;gap:9px;font-size:.86rem;color:var(--tx2);white-space:nowrap;}
  .mq-item b{color:var(--tx);font-weight:700;font-variant-numeric:tabular-nums;} .mq-up{color:var(--grn);font-size:.72rem;} .mq-dn{color:var(--red);font-size:.72rem;}
  .mq-item .ic{width:22px;height:22px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;color:#fff;}
  @keyframes mq{to{transform:translateX(-50%);}}

  /* ── Stats ────────────────────────────────────────── */
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:30px;}
  @media(max-width:680px){.stats{grid-template-columns:repeat(2,1fr);}}
  .stat{background:var(--glass);border:1px solid var(--brd);border-radius:16px;padding:22px;backdrop-filter:blur(10px);}
  .stat .v{font-size:1.9rem;font-weight:800;font-variant-numeric:tabular-nums;letter-spacing:-.02em;
    background:linear-gradient(120deg,var(--gold),#ffe1a6);-webkit-background-clip:text;background-clip:text;color:transparent;}
  .stat .l{font-size:.74rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-top:5px;}

  /* ── Section shell ────────────────────────────────── */
  section{padding:82px 0;}
  .sh{max-width:660px;margin:0 auto 48px;text-align:center;}
  .sh.left{margin-left:0;text-align:left;}
  .sh h2{font-size:clamp(1.9rem,3.4vw,2.6rem);font-weight:800;letter-spacing:-.025em;margin:14px 0 12px;}
  .sh p{color:var(--tx2);font-size:1.04rem;}

  /* ── Page header (sub-pages) ──────────────────────── */
  .page-head{padding:60px 0 10px;}
  .crumb{display:flex;align-items:center;gap:9px;font-size:.82rem;color:var(--tx3);margin-bottom:18px;}
  .crumb a{color:var(--tx2);}.crumb a:hover{color:var(--gold);}.crumb i{font-size:.6rem;color:var(--tx4);}
  .page-head h1{font-size:clamp(2.2rem,4.6vw,3.4rem);font-weight:800;letter-spacing:-.035em;line-height:1.05;}
  .page-head h1 .grad{background:linear-gradient(110deg,var(--gold) 10%,#ffe1a6 50%,var(--gold) 90%);-webkit-background-clip:text;background-clip:text;color:transparent;}
  .page-head p{color:var(--tx2);font-size:1.08rem;max-width:600px;margin:18px 0 0;line-height:1.6;}

  /* ── Bento features ───────────────────────────────── */
  .bento{display:grid;grid-template-columns:repeat(6,1fr);grid-auto-rows:minmax(170px,auto);gap:18px;
    grid-template-areas:"chart chart chart tour tour tour" "chart chart chart lead lead lead" "acad acad ach ach jour jour";}
  @media(max-width:880px){.bento{grid-template-columns:repeat(2,1fr);grid-template-areas:"chart chart" "tour lead" "acad ach" "jour jour";}}
  @media(max-width:540px){.bento{grid-template-columns:1fr;grid-template-areas:"chart" "tour" "lead" "acad" "ach" "jour";}}
  .tile{position:relative;overflow:hidden;background:var(--glass);border:1px solid var(--brd);border-radius:18px;padding:24px;backdrop-filter:blur(10px);
    transition:transform .2s cubic-bezier(.2,.7,.2,1),border-color .2s,background .2s;}
  .tile:hover{transform:translateY(-4px);border-color:var(--brd2);background:rgba(255,255,255,.035);}
  .tile .tic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:14px;}
  .tile h3{font-size:1.06rem;font-weight:700;margin-bottom:7px;letter-spacing:-.01em;}
  .tile p{color:var(--tx2);font-size:.9rem;line-height:1.55;}
  .t-chart{grid-area:chart;display:flex;flex-direction:column;}
  .t-chart .mini{flex:1;min-height:120px;margin-top:14px;position:relative;border-radius:12px;overflow:hidden;background:rgba(0,0,0,.2);}
  .t-chart .mini svg{position:absolute;inset:0;width:100%;height:100%;}
  .t-tour{grid-area:tour;} .t-lead{grid-area:lead;} .t-acad{grid-area:acad;} .t-ach{grid-area:ach;} .t-jour{grid-area:jour;}

  /* ── Showcase ─────────────────────────────────────── */
  .show{position:relative;border-radius:24px;overflow:hidden;border:1px solid var(--brd);
    background:linear-gradient(180deg,rgba(18,24,36,.7),rgba(8,11,18,.7));box-shadow:0 50px 120px -40px rgba(0,0,0,.85);}
  .show-bar{display:flex;align-items:center;gap:8px;padding:13px 16px;border-bottom:1px solid var(--brd);}
  .show-dots{display:flex;gap:6px;} .show-dots i{width:11px;height:11px;border-radius:50%;display:block;}
  .show-body{display:grid;grid-template-columns:62px 1fr 230px;min-height:340px;}
  @media(max-width:760px){.show-body{grid-template-columns:1fr;} .show-side,.show-rail{display:none;}}
  .show-rail{border-right:1px solid var(--brd);padding:14px 0;display:flex;flex-direction:column;align-items:center;gap:10px;}
  .show-rail .r{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--tx3);font-size:.9rem;}
  .show-rail .r.on{background:rgba(245,166,35,.14);color:var(--gold);}
  .show-main{padding:18px;position:relative;}
  .show-main .big{height:100%;min-height:240px;border-radius:14px;background:rgba(0,0,0,.22);position:relative;overflow:hidden;}
  .show-main .big svg{position:absolute;inset:0;width:100%;height:100%;}
  .show-side{border-left:1px solid var(--brd);padding:16px;display:flex;flex-direction:column;gap:11px;}
  .show-side .bal{font-size:1.5rem;font-weight:900;color:var(--gold);font-variant-numeric:tabular-nums;}
  .show-side .deal{display:flex;align-items:center;gap:9px;background:rgba(255,255,255,.025);border:1px solid var(--brd);border-radius:11px;padding:9px;}
  .show-side .deal .av{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:.66rem;}
  .anno{display:flex;gap:18px;justify-content:center;flex-wrap:wrap;margin-top:24px;}
  .anno span{display:inline-flex;align-items:center;gap:8px;font-size:.84rem;color:var(--tx2);}
  .anno i{color:var(--grn);}

  /* ── Steps ────────────────────────────────────────── */
  .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
  @media(max-width:760px){.steps{grid-template-columns:1fr;}}
  .step{position:relative;background:var(--glass);border:1px solid var(--brd);border-radius:18px;padding:28px 26px;backdrop-filter:blur(10px);}
  .step .n{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--gold),#cf7d10);color:#231603;font-weight:900;
    display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 8px 20px -6px rgba(245,166,35,.6);margin-bottom:16px;}
  .step h3{font-size:1.1rem;font-weight:700;margin-bottom:8px;} .step p{color:var(--tx2);font-size:.92rem;line-height:1.55;}

  /* ── Academy ──────────────────────────────────────── */
  .academy{display:grid;grid-template-columns:1.1fr 1fr;gap:40px;align-items:center;
    background:linear-gradient(120deg,rgba(77,141,255,.07),rgba(245,166,35,.06));border:1px solid var(--brd);border-radius:24px;padding:46px;backdrop-filter:blur(10px);}
  @media(max-width:820px){.academy{grid-template-columns:1fr;padding:32px;}}
  .academy h2{font-size:clamp(1.7rem,3vw,2.2rem);font-weight:800;letter-spacing:-.02em;margin-bottom:13px;}
  .academy p{color:var(--tx2);margin-bottom:22px;}
  .acl{display:flex;flex-direction:column;gap:11px;}
  .acl .row{display:flex;align-items:center;gap:12px;font-size:.94rem;font-weight:500;background:rgba(255,255,255,.025);border:1px solid var(--brd);
    border-radius:12px;padding:13px 15px;transition:transform .15s,border-color .15s;}
  .acl .row:hover{transform:translateX(4px);border-color:var(--brd2);}
  .acl .row .i{width:30px;height:30px;border-radius:9px;background:rgba(22,210,145,.14);color:var(--grn);display:flex;align-items:center;justify-content:center;font-size:.78rem;flex-shrink:0;}
  .acl .row .lv{margin-left:auto;font-size:.6rem;font-weight:800;color:var(--tx3);text-transform:uppercase;letter-spacing:.04em;}

  /* ── FAQ ──────────────────────────────────────────── */
  .faq{max-width:760px;margin:0 auto;display:flex;flex-direction:column;gap:12px;}
  .qa{background:var(--glass);border:1px solid var(--brd);border-radius:14px;overflow:hidden;transition:border-color .15s;}
  .qa.open{border-color:var(--brd2);}
  .qa-q{display:flex;align-items:center;gap:14px;padding:18px 20px;cursor:pointer;font-weight:600;font-size:.98rem;}
  .qa-q .ch{margin-left:auto;color:var(--tx3);transition:transform .2s;flex-shrink:0;}
  .qa.open .qa-q .ch{transform:rotate(45deg);color:var(--gold);}
  .qa-a{max-height:0;overflow:hidden;transition:max-height .3s ease;}
  .qa-a .in{padding:0 20px 18px 54px;color:var(--tx2);font-size:.92rem;line-height:1.65;}

  /* ── Explore cards (landing → sub-pages) ──────────── */
  .explore{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
  @media(max-width:820px){.explore{grid-template-columns:1fr;}}
  .ex{display:flex;flex-direction:column;background:var(--glass);border:1px solid var(--brd);border-radius:18px;padding:28px;backdrop-filter:blur(10px);
    transition:transform .2s cubic-bezier(.2,.7,.2,1),border-color .2s,background .2s;}
  .ex:hover{transform:translateY(-4px);border-color:var(--brd2);background:rgba(255,255,255,.035);}
  .ex .tic{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;margin-bottom:16px;}
  .ex h3{font-size:1.16rem;font-weight:700;letter-spacing:-.01em;margin-bottom:8px;}
  .ex p{color:var(--tx2);font-size:.92rem;line-height:1.55;flex:1;}
  .ex .go{margin-top:16px;font-size:.86rem;font-weight:700;color:var(--gold);display:inline-flex;align-items:center;gap:8px;transition:gap .15s;}
  .ex:hover .go{gap:12px;}

  /* ── Final CTA ────────────────────────────────────── */
  .fcta{position:relative;text-align:center;border:1px solid var(--brd);border-radius:26px;padding:64px 28px;overflow:hidden;
    background:linear-gradient(180deg,rgba(20,26,38,.6),rgba(8,11,18,.6));backdrop-filter:blur(10px);}
  .fcta::before{content:'';position:absolute;top:-180px;left:50%;transform:translateX(-50%);width:680px;height:460px;
    background:radial-gradient(closest-side,rgba(245,166,35,.2),transparent 70%);}
  .fcta h2{position:relative;font-size:clamp(2rem,4vw,2.8rem);font-weight:800;letter-spacing:-.03em;margin-bottom:14px;}
  .fcta p{position:relative;color:var(--tx2);margin-bottom:28px;font-size:1.08rem;}
  .fcta .btn{position:relative;padding:16px 30px;font-size:1.02rem;}
</style>
