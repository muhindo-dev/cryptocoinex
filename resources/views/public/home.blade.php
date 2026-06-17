@extends('layouts.public')
@section('title', 'Practice Trading, Risk Nothing')

@push('styles')
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
  .sh h2{font-size:clamp(1.9rem,3.4vw,2.6rem);font-weight:800;letter-spacing:-.025em;margin:14px 0 12px;}
  .sh p{color:var(--tx2);font-size:1.04rem;}

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

  /* ── Final CTA ────────────────────────────────────── */
  .fcta{position:relative;text-align:center;border:1px solid var(--brd);border-radius:26px;padding:64px 28px;overflow:hidden;
    background:linear-gradient(180deg,rgba(20,26,38,.6),rgba(8,11,18,.6));backdrop-filter:blur(10px);}
  .fcta::before{content:'';position:absolute;top:-180px;left:50%;transform:translateX(-50%);width:680px;height:460px;
    background:radial-gradient(closest-side,rgba(245,166,35,.2),transparent 70%);}
  .fcta h2{position:relative;font-size:clamp(2rem,4vw,2.8rem);font-weight:800;letter-spacing:-.03em;margin-bottom:14px;}
  .fcta p{position:relative;color:var(--tx2);margin-bottom:28px;font-size:1.08rem;}
  .fcta .btn{position:relative;padding:16px 30px;font-size:1.02rem;}
</style>
@endpush

@section('content')
{{-- ════ HERO ════ --}}
<section class="hero">
  <div class="full hero-grid">
    <div>
      <div class="h-eyebrow reveal"><span class="pill"><span class="dot"></span> Live practice markets · 100% virtual</span></div>
      <h1 class="h-title reveal" data-d="1">Trade like a pro.<br><span class="grad">Risk absolutely nothing.</span></h1>
      <p class="h-lead reveal" data-d="2">Cryptocoinex is a hyper-realistic trading simulator. Sharpen your edge on
        live charts with {{ number_format($startBalance) }} virtual USD — no deposits, no real money, no catch.</p>
      <div class="h-cta reveal" data-d="3">
        <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Start practicing free <i class="fas fa-arrow-right" style="font-size:.78rem;"></i></a>
        <a href="{{ url('/admin/login') }}" class="btn btn-ghost">I have an account</a>
      </div>
      <div class="h-trust reveal" data-d="4">
        <div class="h-avs">
          @foreach(['#f5a623','#16d291','#4d8dff','#9b7bff','#ff79c6'] as $i=>$c)
            <span style="background:linear-gradient(135deg,{{ $c }},rgba(0,0,0,.4));">{{ ['JT','MA','KO','SP','RB'][$i] }}</span>
          @endforeach
        </div>
        <div class="h-trust-tx"><div class="h-stars">★★★★★</div><b>Loved by new traders</b> learning the ropes risk-free</div>
      </div>
    </div>

    {{-- Live trading card --}}
    <div class="hero-visual reveal" data-d="2">
      <div class="tcard">
        <div class="tc-float tf-win"><span class="i"><i class="fas fa-trophy"></i></span><div><div class="t">Trade won</div><div class="v" style="color:var(--grn);">+144</div></div></div>
        <div class="tc-float tf-streak"><span class="i"><i class="fas fa-fire"></i></span><div><div class="t">Win streak</div><div class="v" style="color:var(--gold);">5 🔥</div></div></div>
        <div class="tc-top">
          <span class="tc-ic">BT</span>
          <div><div class="tc-sym">BTC / USDT</div><div class="tc-sub">Crypto</div></div>
          <span class="tc-live"><span class="d"></span>LIVE</span>
          <div class="tc-price"><div class="p" id="hcPrice">67,431</div><div class="c" id="hcChg" style="color:var(--grn);">▲ 1.24%</div></div>
        </div>
        <div class="tc-chart" id="hcChart">
          <svg viewBox="0 0 460 188" preserveAspectRatio="none">
            <defs><linearGradient id="hg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4d8dff" stop-opacity=".4"/><stop offset="1" stop-color="#4d8dff" stop-opacity="0"/></linearGradient></defs>
            <path id="hcArea" fill="url(#hg)"></path>
            <path id="hcLine" fill="none" stroke="#5d97ff" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round"></path>
            <circle id="hcDot" r="4.2" fill="#5d97ff" stroke="#0b0f16" stroke-width="2"></circle>
          </svg>
          <span class="tc-tag" id="hcTag">67,431</span>
        </div>
        <div class="tc-pos">
          <span class="av"><i class="fas fa-arrow-up"></i></span>
          <div class="meta"><b>BUY</b> · 50 USD · 60s<br>Entry 67,388</div>
          <span class="pnl" id="hcPnl">▲ Winning</span>
        </div>
        <div class="tc-bot">
          <div class="tc-btn tc-sell">▼ SELL <span class="pct">80%</span></div>
          <div class="tc-btn tc-buy">▲ BUY <span class="pct">80%</span></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Marquee --}}
  <div class="full"><div class="marquee reveal"><div class="mq-track" id="mqTrack"></div></div></div>

  {{-- Stats --}}
  <div class="full">
    <div class="stats reveal">
      <div class="stat"><div class="v" data-count="{{ $startBalance }}">{{ number_format($startBalance) }}</div><div class="l">Starting USD</div></div>
      <div class="stat"><div class="v" data-count="{{ $assetCount }}" data-suffix="+">{{ $assetCount }}+</div><div class="l">Markets to trade</div></div>
      <div class="stat"><div class="v" data-count="{{ $lessonCount }}">{{ $lessonCount }}</div><div class="l">Free lessons</div></div>
      <div class="stat"><div class="v">$0</div><div class="l">Real money at risk</div></div>
    </div>
  </div>
</section>

{{-- ════ FEATURES (bento) ════ --}}
<section id="features">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Everything you need</span><h2>A full trading floor, risk-free</h2>
      <p>The same tools the pros use — without ever touching real money.</p></div>
    <div class="bento reveal">
      <div class="tile t-chart">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-chart-line"></i></div>
        <h3>Real-time charts &amp; one-tap trades</h3>
        <p>Candles, line &amp; area views with MA / RSI and intervals down to 10 seconds. Predict up or down in a single tap.</p>
        <div class="mini"><svg viewBox="0 0 600 160" preserveAspectRatio="none">
          <defs><linearGradient id="mg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#16d291" stop-opacity=".35"/><stop offset="1" stop-color="#16d291" stop-opacity="0"/></linearGradient></defs>
          <path id="bArea" fill="url(#mg)"></path><path id="bLine" fill="none" stroke="#1ad79a" stroke-width="2.2" stroke-linejoin="round"></path>
        </svg></div>
      </div>
      <div class="tile t-tour"><div class="tic" style="background:rgba(251,191,36,.14);color:#fbbf24;"><i class="fas fa-trophy"></i></div>
        <h3>Tournaments</h3><p>Compete in timed challenges, climb the standings and claim first place.</p></div>
      <div class="tile t-lead"><div class="tic" style="background:rgba(22,210,145,.14);color:var(--grn);"><i class="fas fa-ranking-star"></i></div>
        <h3>Leaderboards</h3><p>See how you rank — weekly, monthly and all-time — against every trader.</p></div>
      <div class="tile t-acad"><div class="tic" style="background:rgba(155,123,255,.14);color:var(--violet);"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academy</h3><p>{{ $lessonCount }} free video lessons.</p></div>
      <div class="tile t-ach"><div class="tic" style="background:rgba(255,121,198,.14);color:var(--pink);"><i class="fas fa-medal"></i></div>
        <h3>Achievements</h3><p>Unlock badges as you hit milestones.</p></div>
      <div class="tile t-jour"><div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-book"></i></div>
        <h3>Trade journal</h3><p>Annotate trades and review to sharpen up.</p></div>
    </div>
  </div>
</section>

{{-- ════ SHOWCASE ════ --}}
<section style="padding-top:0;">
  <div class="full">
    <div class="sh reveal"><span class="sec-tag">The platform</span><h2>Beautiful. Fast. Built for focus.</h2>
      <p>A professional trading interface that feels alive — without a cent of real risk.</p></div>
    <div class="show reveal">
      <div class="show-bar"><div class="show-dots"><i style="background:#ff5f57;"></i><i style="background:#febc2e;"></i><i style="background:#28c840;"></i></div>
        <span style="font-size:.72rem;color:var(--tx3);margin-left:8px;">cryptocoinex · trade</span></div>
      <div class="show-body">
        <div class="show-rail">
          <div class="r on"><i class="fas fa-chart-column"></i></div>
          <div class="r"><i class="fas fa-clock-rotate-left"></i></div>
          <div class="r"><i class="fas fa-ranking-star"></i></div>
          <div class="r"><i class="fas fa-trophy"></i></div>
          <div class="r"><i class="fas fa-graduation-cap"></i></div>
        </div>
        <div class="show-main">
          <div class="big"><svg viewBox="0 0 900 280" preserveAspectRatio="none">
            <defs><linearGradient id="sg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4d8dff" stop-opacity=".34"/><stop offset="1" stop-color="#4d8dff" stop-opacity="0"/></linearGradient></defs>
            <path id="sArea" fill="url(#sg)"></path><path id="sLine" fill="none" stroke="#5d97ff" stroke-width="2.6" stroke-linejoin="round"></path>
          </svg></div>
        </div>
        <div class="show-side">
          <div style="font-size:.62rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.06em;">Practice balance</div>
          <div class="bal">{{ number_format($startBalance) }}</div>
          <div class="deal"><span class="av" style="background:rgba(22,210,145,.16);color:var(--grn);">▲</span><div style="font-size:.7rem;color:var(--tx2);">BTC · BUY<br><span style="color:var(--tx3);">+80 USD</span></div></div>
          <div class="deal"><span class="av" style="background:rgba(255,77,106,.16);color:var(--red);">▼</span><div style="font-size:.7rem;color:var(--tx2);">ETH · SELL<br><span style="color:var(--tx3);">+62 USD</span></div></div>
          <div class="tc-bot" style="border:none;padding:6px 0 0;"><div class="tc-btn tc-sell" style="padding:10px;">SELL</div><div class="tc-btn tc-buy" style="padding:10px;">BUY</div></div>
        </div>
      </div>
    </div>
    <div class="anno reveal">
      <span><i class="fas fa-check"></i> Live price every second</span>
      <span><i class="fas fa-check"></i> Indicators &amp; chart types</span>
      <span><i class="fas fa-check"></i> Dark &amp; light themes</span>
      <span><i class="fas fa-check"></i> Works on mobile</span>
    </div>
  </div>
</section>

{{-- ════ HOW IT WORKS ════ --}}
<section id="how">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Get started</span><h2>Trading in three steps</h2></div>
    <div class="steps">
      <div class="step reveal" data-d="1"><div class="n">1</div><h3>Create a free account</h3><p>Just a name, email and password. No card, no deposit — ready in 30 seconds.</p></div>
      <div class="step reveal" data-d="2"><div class="n">2</div><h3>Get {{ number_format($startBalance) }} USD</h3><p>Your wallet is funded instantly with virtual money. Reset or wipe it any time.</p></div>
      <div class="step reveal" data-d="3"><div class="n">3</div><h3>Practice &amp; compete</h3><p>Place trades, take the course, join tournaments and climb the leaderboard.</p></div>
    </div>
  </div>
</section>

{{-- ════ ACADEMY ════ --}}
<section id="academy">
  <div class="wrap">
    <div class="academy reveal">
      <div>
        <span class="sec-tag">Cryptocoinex Academy</span>
        <h2 style="margin-top:14px;">Learn to trade, from zero</h2>
        <p>A free, structured course with {{ $lessonCount }} lessons and video guides — from your first trade to risk management and strategy.</p>
        <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Start learning free <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></a>
      </div>
      <div class="acl">
        @foreach([['How to Trade — the basics','Beginner'],['Reading candlesticks &amp; trends','Beginner'],['RSI, MACD &amp; moving averages','Base'],['Strategies that actually work','Advanced'],['Risk management &amp; psychology','Advanced']] as $l)
        <div class="row"><span class="i"><i class="fas fa-play"></i></span> {{ $l[0] }} <span class="lv">{{ $l[1] }}</span></div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ════ FAQ ════ --}}
<section id="faq">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Questions</span><h2>Everything you might ask</h2></div>
    <div class="faq reveal">
      @php $faqs = [
        ['Is this real money?','No — never. Cryptocoinex is a practice simulator. Every balance is virtual “USD” with zero real-world value. There are no deposits, withdrawals or payments of any kind.'],
        ['Is it really free?','Completely. Create an account, get '.number_format($startBalance).' virtual USD, and use every feature — charts, tournaments, leaderboards and the full academy — at no cost, forever.'],
        ['Do I need a card or any documents?','No. Just a name, email and password. Because no real money is ever involved, we never ask for payment or identity documents.'],
        ['Can I reset my balance?','Yes. You can reset your balance to the starting amount, or fully wipe your account (deleting all trades and history) and start fresh, any time from your wallet.'],
        ['Will this make me a profitable real trader?','It is a powerful way to learn the mechanics, build discipline and test ideas risk-free. Real markets carry real risk — nothing here is financial advice or a guarantee of results.'],
      ]; @endphp
      @foreach($faqs as $f)
      <div class="qa">
        <div class="qa-q">{{ $f[0] }}<i class="fas fa-plus ch"></i></div>
        <div class="qa-a"><div class="in">{{ $f[1] }}</div></div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════ FINAL CTA ════ --}}
<section style="padding-top:10px;">
  <div class="wrap">
    <div class="fcta reveal">
      <h2>Start practicing in 30 seconds</h2>
      <p>Join free, get {{ number_format($startBalance) }} virtual USD, and trade with zero risk.</p>
      <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Create my free account <i class="fas fa-arrow-right" style="font-size:.8rem;"></i></a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  // ── Animated hero chart (random walk, redraws each tick) ──
  function mkChart(lineId, areaId, opts){
    var line=document.getElementById(lineId), area=document.getElementById(areaId);
    if(!line) return null;
    var W=opts.w, H=opts.h, N=opts.n, pad=opts.pad||10, data=[], base=opts.base||100;
    for(var i=0;i<N;i++){ base += (Math.random()-0.46)*opts.vol; data.push(base); }
    function draw(){
      var min=Math.min.apply(null,data), max=Math.max.apply(null,data), rng=(max-min)||1;
      var step=W/(N-1), pts=data.map(function(v,i){ var x=i*step; var y=pad+(H-2*pad)*(1-(v-min)/rng); return [x,y]; });
      var d=pts.map(function(p,i){ return (i?'L':'M')+p[0].toFixed(1)+','+p[1].toFixed(1); }).join(' ');
      line.setAttribute('d',d);
      if(area) area.setAttribute('d', d+' L'+W+','+H+' L0,'+H+' Z');
      return pts[pts.length-1];
    }
    return { data:data, draw:draw, tick:function(){ data.push(data[data.length-1]+(Math.random()-0.48)*opts.vol); data.shift(); return draw(); } };
  }

  var hero = mkChart('hcLine','hcArea',{w:460,h:188,n:46,pad:16,base:67400,vol:48});
  var dot=document.getElementById('hcDot'), tag=document.getElementById('hcTag'),
      price=document.getElementById('hcPrice'), chg=document.getElementById('hcChg'), pnl=document.getElementById('hcPnl');
  function fmt(n){ return Math.round(n).toLocaleString('en-US'); }
  if(hero){
    var last=hero.draw(); var prevVal=hero.data[hero.data.length-1];
    function place(p){ if(dot){dot.setAttribute('cx',p[0]);dot.setAttribute('cy',p[1]);} if(tag){tag.style.top=p[1]+'px';} }
    place(last);
    setInterval(function(){
      var p=hero.tick(); place(p);
      var v=hero.data[hero.data.length-1], up=v>=prevVal;
      var col = up?'var(--grn)':'var(--red)';
      if(price){ price.textContent=fmt(v); price.style.color=col; }
      if(tag){ tag.textContent=fmt(v); tag.style.background = up?'rgba(22,210,145,.92)':'rgba(255,77,106,.92)'; }
      if(chg){ var pc=((v-67400)/67400*100); chg.textContent=(pc>=0?'▲ ':'▼ ')+Math.abs(pc).toFixed(2)+'%'; chg.style.color=pc>=0?'var(--grn)':'var(--red)'; }
      if(pnl){ pnl.textContent = v>67388?'▲ Winning':'▼ Losing'; pnl.style.color = v>67388?'var(--grn)':'var(--red)'; }
      prevVal=v;
    },1100);
  }

  // Static-but-pretty mini charts (bento + showcase)
  var mini = mkChart('bLine','bArea',{w:600,h:160,n:54,pad:10,base:120,vol:7}); if(mini) mini.draw();
  var shw  = mkChart('sLine','sArea',{w:900,h:280,n:80,pad:18,base:200,vol:9}); if(shw) shw.draw();

  // ── Marquee ──
  var assets=[['BT','#f7931a','BTC/USDT','67,431','▲ 1.24%',1],['ET','#627eea','ETH/USDT','3,512','▲ 0.86%',1],
    ['SO','#14f195','SOL/USDT','151.4','▲ 2.10%',1],['XAU','#d4af37','Gold','2,351','▼ 0.31%',0],
    ['BN','#f0b90b','BNB/USDT','601.2','▲ 0.44%',1],['XAG','#9ca3af','Silver','30.6','▲ 0.92%',1],
    ['EUR','#3b82f6','EUR/USD','1.0852','▼ 0.08%',0],['TS','#cc0000','TSLA','244.9','▲ 1.77%',1]];
  var track=document.getElementById('mqTrack');
  if(track){
    var html=assets.map(function(a){ return '<span class="mq-item"><span class="ic" style="background:linear-gradient(135deg,'+a[1]+',rgba(0,0,0,.4))">'+a[0]+'</span>'+a[2]+' <b>'+a[3]+'</b> <span class="'+(a[5]?'mq-up':'mq-dn')+'">'+a[4]+'</span></span>'; }).join('');
    track.innerHTML=html+html; // duplicate for seamless loop
  }

  // ── Animated counters ──
  function animate(el){
    var target=parseFloat(el.dataset.count), suf=el.dataset.suffix||'', dur=1100, t0=null;
    function frame(t){ if(!t0)t0=t; var k=Math.min((t-t0)/dur,1); var e=1-Math.pow(1-k,3);
      el.textContent=Math.round(target*e).toLocaleString('en-US')+suf; if(k<1)requestAnimationFrame(frame); }
    requestAnimationFrame(frame);
  }
  var cio=new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ animate(e.target); cio.unobserve(e.target); } }); },{threshold:.5});
  document.querySelectorAll('[data-count]').forEach(function(el){ cio.observe(el); });

  // ── FAQ accordion ──
  document.querySelectorAll('.qa-q').forEach(function(q){
    q.addEventListener('click', function(){
      var qa=q.parentElement, a=qa.querySelector('.qa-a'), open=qa.classList.contains('open');
      document.querySelectorAll('.qa.open').forEach(function(o){ o.classList.remove('open'); o.querySelector('.qa-a').style.maxHeight=null; });
      if(!open){ qa.classList.add('open'); a.style.maxHeight=a.scrollHeight+'px'; }
    });
  });
})();
</script>
@endpush
