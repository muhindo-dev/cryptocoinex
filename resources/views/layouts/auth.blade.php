<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Cryptocoinex') — Cryptocoinex</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo-square.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#0b0e14">
  <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{ --a-bg:#070a10; --a-panel:#0b0f16; --a-card:#111722; --a-bdr:#1b2533; --a-bdr2:#26344a;
           --a-tx:#eef2f8; --a-mt:#8a97a8; --a-dim:#5b6776; --gold:#f59e0b; --gold-d:#d97706;
           --grn:#00c97b; --red:#f53b57; --blue:#3b82f6; }
    html,body{height:100%;}
    body{font-family:var(--font-sans,'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif);
      background:var(--a-bg);color:var(--a-tx);-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
      letter-spacing:-.01em;line-height:1.5;}
    a{color:inherit;text-decoration:none;}

    .auth{min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;}

    /* ── Brand panel ── */
    .auth-brand{position:relative;background:var(--a-panel);padding:46px 56px;display:flex;flex-direction:column;overflow:hidden;}
    .auth-brand::before{content:'';position:absolute;inset:0;pointer-events:none;background:
      radial-gradient(ellipse 70% 50% at 12% 8%,rgba(245,158,11,.13),transparent 70%),
      radial-gradient(ellipse 60% 55% at 88% 90%,rgba(59,130,246,.08),transparent 70%);}
    .ab-wm{position:absolute;right:-30px;bottom:-30px;width:440px;opacity:.05;pointer-events:none;}
    .ab-top,.ab-mid,.ab-bot{position:relative;z-index:2;}
    .ab-mid{flex:1;display:flex;flex-direction:column;justify-content:center;}
    .ab-logo{display:inline-flex;align-items:center;gap:12px;}
    .ab-logo .mark{width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,var(--gold),var(--gold-d));
      display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(245,158,11,.4);flex-shrink:0;}
    .ab-logo .name{font-size:1.2rem;font-weight:900;letter-spacing:.04em;text-transform:uppercase;}
    .ab-logo .tag{font-size:.58rem;font-weight:700;color:var(--gold);letter-spacing:.2em;text-transform:uppercase;margin-top:3px;}
    .ab-rule{width:50px;height:2px;background:linear-gradient(90deg,var(--gold),transparent);margin:30px 0 22px;}
    .ab-head{font-size:clamp(2rem,3vw,2.7rem);font-weight:900;line-height:1.08;letter-spacing:-.03em;margin-bottom:16px;}
    .ab-head em{font-style:normal;background:linear-gradient(120deg,var(--gold),#fcd34d);-webkit-background-clip:text;background-clip:text;color:transparent;}
    .ab-desc{font-size:.95rem;color:var(--a-mt);max-width:360px;margin-bottom:36px;line-height:1.65;}
    .ab-feats{display:flex;flex-direction:column;gap:15px;}
    .ab-feat{display:flex;align-items:center;gap:13px;}
    .ab-feat .i{width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);
      display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:.82rem;flex-shrink:0;}
    .ab-feat .t{font-size:.86rem;color:var(--a-mt);font-weight:600;}
    .ab-ticker{display:flex;gap:24px;margin-top:40px;flex-wrap:wrap;}
    .ab-tk .s{font-size:.58rem;font-weight:800;color:var(--a-dim);letter-spacing:.1em;}
    .ab-tk .v{font-size:.88rem;font-weight:800;font-variant-numeric:tabular-nums;}
    .ab-tk .c{font-size:.64rem;font-weight:700;} .ab-tk .c.up{color:var(--grn);} .ab-tk .c.dn{color:var(--red);}
    .ab-fine{font-size:.66rem;color:var(--a-dim);line-height:1.7;margin-top:auto;padding-top:30px;}
    .ab-fine b{color:var(--a-mt);}

    /* ── Form panel ── */
    .auth-form{display:flex;align-items:center;justify-content:center;padding:48px;}
    .af-wrap{width:100%;max-width:418px;}
    .af-mobile-logo{display:none;align-items:center;gap:11px;margin-bottom:26px;}
    .af-eyebrow{font-size:.66rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:var(--gold);margin-bottom:10px;}
    .af-title{font-size:1.85rem;font-weight:900;letter-spacing:-.02em;line-height:1.15;margin-bottom:9px;}
    .af-sub{font-size:.9rem;color:var(--a-mt);line-height:1.6;margin-bottom:28px;}

    .a-alert{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;font-size:.82rem;
      line-height:1.5;margin-bottom:20px;border:1px solid;}
    .a-alert i{margin-top:2px;flex-shrink:0;}
    .a-alert.err{background:rgba(245,59,87,.08);border-color:rgba(245,59,87,.3);color:#ffb3c1;}
    .a-alert.ok{background:rgba(0,201,123,.08);border-color:rgba(0,201,123,.3);color:#7bf3c4;}
    .a-alert.info{background:rgba(59,130,246,.08);border-color:rgba(59,130,246,.3);color:#a9c8ff;}
    .a-alert ul{margin:2px 0 0;padding-left:16px;}

    .a-field{margin-bottom:17px;}
    .a-label{display:flex;justify-content:space-between;align-items:center;font-size:.68rem;font-weight:800;letter-spacing:.05em;
      text-transform:uppercase;color:var(--a-mt);margin-bottom:8px;}
    .a-label a{color:var(--gold);text-transform:none;letter-spacing:0;font-size:.74rem;font-weight:700;}
    .a-inwrap{position:relative;}
    .a-inwrap > i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--a-dim);font-size:.82rem;pointer-events:none;transition:color .15s;}
    .a-input{width:100%;height:50px;padding:0 14px 0 42px;border:1.5px solid var(--a-bdr);border-radius:11px;
      background:var(--a-card);color:var(--a-tx);font-family:inherit;font-size:.92rem;outline:none;transition:border-color .15s,box-shadow .15s;}
    .a-input::placeholder{color:var(--a-dim);}
    .a-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(245,158,11,.12);}
    .a-inwrap:focus-within > i{color:var(--gold);}
    .a-eye{position:absolute;right:6px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:none;background:transparent;
      color:var(--a-dim);cursor:pointer;border-radius:8px;font-size:.82rem;}
    .a-eye:hover{color:var(--a-tx);}
    .a-check{display:flex;align-items:center;gap:9px;font-size:.84rem;color:var(--a-mt);cursor:pointer;user-select:none;margin-bottom:6px;}
    .a-check input{width:16px;height:16px;accent-color:var(--gold);}

    .a-btn{width:100%;height:50px;border:none;border-radius:11px;cursor:pointer;font-family:inherit;font-size:.95rem;font-weight:800;
      display:flex;align-items:center;justify-content:center;gap:9px;
      background:linear-gradient(180deg,#f9b13d,var(--gold));color:#1a1205;box-shadow:0 8px 24px rgba(245,158,11,.3);
      transition:transform .1s,box-shadow .18s,filter .15s;margin-top:4px;}
    .a-btn:hover{filter:brightness(1.04);box-shadow:0 12px 30px rgba(245,158,11,.4);}
    .a-btn:active{transform:scale(.98);}
    .a-btn.ghost{background:transparent;border:1.5px solid var(--a-bdr);color:var(--a-tx);box-shadow:none;}
    .a-btn.ghost:hover{border-color:var(--a-bdr2);background:rgba(255,255,255,.03);filter:none;}

    .a-alt{text-align:center;font-size:.86rem;color:var(--a-mt);margin-top:22px;}
    .a-alt a{color:var(--gold);font-weight:800;}
    .a-back{display:inline-flex;align-items:center;gap:7px;font-size:.8rem;color:var(--a-mt);font-weight:600;margin-top:14px;}
    .a-back:hover{color:var(--a-tx);}
    .a-secure{display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;font-size:.7rem;color:var(--a-dim);margin-top:26px;}
    .a-secure .dot{opacity:.5;}
    .a-grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}

    /* ── Responsive ── */
    @media(max-width:880px){
      .auth{grid-template-columns:1fr;}
      .auth-brand{display:none;}
      .auth-form{padding:30px 22px;min-height:100vh;}
      .af-mobile-logo{display:flex;}
    }
    @media(max-width:420px){ .a-grid2{grid-template-columns:1fr;} }
  </style>
  @stack('styles')
</head>
<body>
<div class="auth">

  {{-- Brand panel (consistent across every auth page) --}}
  <aside class="auth-brand">
    <svg class="ab-wm" viewBox="0 0 200 120" fill="none">
      <path d="M0,90 L25,80 L50,88 L75,60 L100,68 L125,40 L150,52 L175,28 L200,36" stroke="#f59e0b" stroke-width="2"/>
    </svg>
    <div class="ab-top">
      <a href="{{ route('home') }}" class="ab-logo">
        <span class="mark">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <polyline points="3,17 8,10 13,14 21,5" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="17,5 21,5 21,9" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span><span class="name">Cryptocoinex</span><span class="tag">Trading Simulator</span></span>
      </a>
    </div>

    <div class="ab-mid">
      <div class="ab-rule"></div>
      <h1 class="ab-head">Practice trading.<br><em>Risk nothing.</em></h1>
      <p class="ab-desc">Sharpen your skills on real-time charts with virtual money. No deposits,
        no real funds, no catch — just a place to learn and compete.</p>
      <div class="ab-feats">
        <div class="ab-feat"><span class="i"><i class="fas fa-chart-line"></i></span><span class="t">Live charts, indicators &amp; one-tap trades</span></div>
        <div class="ab-feat"><span class="i"><i class="fas fa-trophy"></i></span><span class="t">Tournaments, leaderboards &amp; achievements</span></div>
        <div class="ab-feat"><span class="i"><i class="fas fa-graduation-cap"></i></span><span class="t">A free trading course with 40+ lessons</span></div>
      </div>
      <div class="ab-ticker">
        <div class="ab-tk"><div class="s">BTC/USDT</div><div class="v">67,431</div><div class="c up">▲ 1.24%</div></div>
        <div class="ab-tk"><div class="s">ETH/USDT</div><div class="v">3,512</div><div class="c up">▲ 0.86%</div></div>
        <div class="ab-tk"><div class="s">XAU/USD</div><div class="v">2,351</div><div class="c dn">▼ 0.31%</div></div>
      </div>
    </div>

    <div class="ab-bot ab-fine">
      <b>Practice simulator — not a real broker.</b> All balances are virtual USD with zero
      real-world value. No deposits, withdrawals or financial services. Not financial advice.
    </div>
  </aside>

  {{-- Form panel --}}
  <main class="auth-form">
    <div class="af-wrap">
      <a href="{{ route('home') }}" class="af-mobile-logo ab-logo">
        <span class="mark" style="width:38px;height:38px;border-radius:10px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <polyline points="3,17 8,10 13,14 21,5" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="17,5 21,5 21,9" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span><span class="name" style="font-size:1.05rem;">Cryptocoinex</span></span>
      </a>

      @yield('form')
    </div>
  </main>
</div>

<script>
  // Generic password-visibility toggles
  document.querySelectorAll('[data-eye]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = document.getElementById(btn.dataset.eye);
      if(!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('i').className = 'fas ' + (show ? 'fa-eye-slash' : 'fa-eye');
    });
  });
</script>
@stack('scripts')
@include('partials.tawk')
</body>
</html>
