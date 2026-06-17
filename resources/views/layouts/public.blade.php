<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Trade Crypto, Forex & Gold on Live Markets') · Cryptocoinex</title>
  <meta name="description" content="@yield('desc', 'Trade crypto, forex and gold on live markets, with fast deposits and withdrawals and a free demo to practice first.')">
  <link rel="icon" type="image/png" href="{{ asset('images/logo-square.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#06080d">
  <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --bg:#06080d; --bg2:#0a0e16; --panel:#0c111b;
      --glass:rgba(255,255,255,.022); --brd:rgba(255,255,255,.07); --brd2:rgba(255,255,255,.12); --hi:rgba(255,255,255,.05);
      --tx:#f4f7fc; --tx2:#a7b1c2; --tx3:#6c7787; --tx4:#434c5b;
      --gold:#f5a623; --gold2:#ffd479; --grn:#16d291; --red:#ff4d6a; --blue:#4d8dff; --violet:#9b7bff; --pink:#ff79c6;
    }
    html{scroll-behavior:smooth;}
    body{font-family:var(--font-sans,'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif);
      background:var(--bg);color:var(--tx);line-height:1.55;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
      letter-spacing:-.014em;overflow-x:hidden;position:relative;}
    a{color:inherit;text-decoration:none;}
    ::selection{background:rgba(245,166,35,.28);}

    /* Background FX (fixed, behind everything) */
    .bgfx{position:fixed;inset:0;z-index:-2;pointer-events:none;overflow:hidden;}
    .bgfx .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.5;}
    .bgfx .o1{top:-180px;left:8%;width:560px;height:560px;background:radial-gradient(circle,rgba(245,166,35,.22),transparent 65%);}
    .bgfx .o2{top:380px;right:-160px;width:620px;height:620px;background:radial-gradient(circle,rgba(77,141,255,.16),transparent 65%);}
    .bgfx .o3{top:1500px;left:-120px;width:540px;height:540px;background:radial-gradient(circle,rgba(155,123,255,.12),transparent 65%);}
    .grid-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;
      background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
      background-size:54px 54px;mask-image:radial-gradient(ellipse 80% 50% at 50% 0%,#000 0%,transparent 70%);
      -webkit-mask-image:radial-gradient(ellipse 80% 50% at 50% 0%,#000 0%,transparent 70%);}

    .wrap{width:100%;max-width:1280px;margin:0 auto;padding:0 clamp(20px,4vw,40px);}
    .full{width:100%;max-width:100%;margin:0 auto;padding:0 clamp(20px,4vw,56px);}

    /* Buttons */
    .btn{position:relative;display:inline-flex;align-items:center;gap:9px;border-radius:12px;font-weight:700;font-size:.92rem;
      padding:13px 22px;cursor:pointer;border:1px solid transparent;transition:transform .14s cubic-bezier(.2,.7,.2,1),box-shadow .2s,background .15s,border-color .15s;white-space:nowrap;}
    .btn:active{transform:translateY(1px) scale(.99);}
    .btn-gold{background:linear-gradient(180deg,#ffcb6e,var(--gold));color:#231603;box-shadow:0 10px 30px -8px rgba(245,166,35,.55),0 1px 0 rgba(255,255,255,.4) inset;}
    .btn-gold:hover{box-shadow:0 16px 40px -8px rgba(245,166,35,.7),0 1px 0 rgba(255,255,255,.4) inset;transform:translateY(-1px);}
    .btn-ghost{background:rgba(255,255,255,.04);border-color:var(--brd);color:var(--tx);backdrop-filter:blur(8px);}
    .btn-ghost:hover{border-color:var(--brd2);background:rgba(255,255,255,.07);transform:translateY(-1px);}
    .btn-sm{padding:9px 16px;font-size:.85rem;border-radius:10px;}

    /* Nav */
    header.nav{position:sticky;top:0;z-index:60;transition:border-color .25s,background .25s;border-bottom:1px solid transparent;}
    header.nav.scrolled{background:rgba(6,8,13,.72);backdrop-filter:blur(16px) saturate(140%);border-bottom-color:var(--brd);}
    .nav-in{display:flex;align-items:center;height:70px;gap:16px;}
    .brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:1.04rem;letter-spacing:-.01em;}
    .brand .mark{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--gold),#cf7d10);
      display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px -2px rgba(245,166,35,.6),0 1px 0 rgba(255,255,255,.35) inset;}
    .nav-links{margin-left:36px;display:flex;gap:30px;font-size:.9rem;color:var(--tx2);font-weight:500;}
    .nav-links a{position:relative;transition:color .15s;}
    .nav-links a::after{content:'';position:absolute;left:0;bottom:-6px;width:0;height:2px;background:var(--gold);transition:width .2s;border-radius:2px;}
    .nav-links a:hover{color:var(--tx);} .nav-links a:hover::after{width:100%;}
    .nav-cta{margin-left:auto;display:flex;gap:10px;align-items:center;}
    @media(max-width:820px){.nav-links{display:none;} .nav-in{height:62px;}}

    .pill{display:inline-flex;align-items:center;gap:8px;font-size:.74rem;font-weight:700;letter-spacing:.02em;
      color:var(--gold);background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.22);padding:6px 13px;border-radius:30px;}
    .pill .dot{width:7px;height:7px;border-radius:50%;background:var(--grn);box-shadow:0 0 0 0 rgba(22,210,145,.6);animation:pulse 1.8s infinite;}
    @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(22,210,145,.55);}70%{box-shadow:0 0 0 7px rgba(22,210,145,0);}100%{box-shadow:0 0 0 0 rgba(22,210,145,0);}}

    /* Section heads */
    .sec-tag{display:inline-flex;align-items:center;gap:8px;font-size:.76rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
      color:var(--gold);background:rgba(245,166,35,.07);border:1px solid rgba(245,166,35,.18);padding:5px 12px;border-radius:30px;}

    /* Reveal-on-scroll */
    .reveal{opacity:0;transform:translateY(22px);transition:opacity .8s cubic-bezier(.2,.7,.2,1),transform .8s cubic-bezier(.2,.7,.2,1);}
    .reveal.in{opacity:1;transform:none;}
    .reveal[data-d="1"]{transition-delay:.08s;} .reveal[data-d="2"]{transition-delay:.16s;}
    .reveal[data-d="3"]{transition-delay:.24s;} .reveal[data-d="4"]{transition-delay:.32s;} .reveal[data-d="5"]{transition-delay:.4s;}
    @media(prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none;}}

    /* Legal prose */
    .legal{max-width:780px;margin:0 auto;padding:64px 0 24px;}
    .legal h1{font-size:2.6rem;font-weight:800;letter-spacing:-.03em;margin:12px 0 8px;}
    .legal .updated{font-size:.82rem;color:var(--tx3);margin-bottom:32px;}
    .legal .note{background:var(--glass);border:1px solid var(--brd);border-radius:14px;padding:16px 18px;font-size:.88rem;color:var(--tx2);line-height:1.7;margin-bottom:30px;backdrop-filter:blur(10px);}
    .legal .note b{color:var(--gold);}
    .legal h2{font-size:1.3rem;font-weight:700;margin:32px 0 10px;letter-spacing:-.01em;}
    .legal p{color:var(--tx2);font-size:.96rem;line-height:1.8;margin:10px 0;}
    .legal ul{margin:10px 0;padding:0;list-style:none;}
    .legal li{position:relative;padding-left:24px;color:var(--tx2);font-size:.96rem;line-height:1.75;margin:8px 0;}
    .legal li::before{content:'';position:absolute;left:5px;top:11px;width:6px;height:6px;border-radius:50%;background:var(--gold);}
    .legal a{color:var(--gold);font-weight:600;} .legal strong{color:var(--tx);}

    /* Footer */
    footer{border-top:1px solid var(--brd);margin-top:70px;background:linear-gradient(180deg,transparent,rgba(255,255,255,.012));}
    .foot{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;padding:56px 0 30px;}
    @media(max-width:760px){.foot{grid-template-columns:1fr 1fr;gap:30px;}}
    @media(max-width:440px){.foot{grid-template-columns:1fr;}}
    .foot-blurb{font-size:.86rem;color:var(--tx3);line-height:1.65;max-width:300px;margin:14px 0 0;}
    .foot-col h4{font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--tx3);margin-bottom:14px;}
    .foot-col a{display:block;font-size:.88rem;color:var(--tx2);margin:9px 0;transition:color .15s;}
    .foot-col a:hover{color:var(--gold);}
    .foot-bar{border-top:1px solid var(--brd);padding:20px 0 34px;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;
      font-size:.8rem;color:var(--tx3);}
    .foot-disc{font-size:.74rem;color:var(--tx4);line-height:1.6;max-width:640px;}
  </style>
  <noscript><style>.reveal{opacity:1!important;transform:none!important;}</style></noscript>
  @stack('styles')
</head>
<body>

<div class="bgfx"><span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span></div>
<div class="grid-fx"></div>

<header class="nav" id="nav">
  <div class="wrap nav-in">
    <a href="{{ route('home') }}" class="brand">
      <span class="mark"><svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <polyline points="3,17 8,10 13,14 21,5" stroke="#231603" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
        <polyline points="17,5 21,5 21,9" stroke="#231603" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg></span> Cryptocoinex
    </a>
    <nav class="nav-links">
      <a href="{{ route('home') }}#features">Features</a>
      <a href="{{ route('home') }}#how">How it works</a>
      <a href="{{ route('home') }}#academy">Academy</a>
      <a href="{{ route('home') }}#faq">FAQ</a>
    </nav>
    <div class="nav-cta">
      <a href="{{ url('/admin/login') }}" class="btn btn-ghost btn-sm">Sign in</a>
      <a href="{{ route('onboarding.register') }}" class="btn btn-gold btn-sm">Open account <i class="fas fa-arrow-right" style="font-size:.7rem;"></i></a>
    </div>
  </div>
</header>

@yield('content')

<footer>
  <div class="wrap">
    <div class="foot">
      <div>
        <a href="{{ route('home') }}" class="brand">
          <span class="mark"><svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <polyline points="3,17 8,10 13,14 21,5" stroke="#231603" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="17,5 21,5 21,9" stroke="#231603" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg></span> Cryptocoinex
        </a>
        <p class="foot-blurb">Trade crypto, forex and gold on live markets, with fast deposits and withdrawals, plus a free demo to practice first.</p>
      </div>
      <div class="foot-col">
        <h4>Product</h4>
        <a href="{{ route('home') }}#features">Features</a>
        <a href="{{ route('home') }}#how">How it works</a>
        <a href="{{ route('home') }}#academy">Academy</a>
        <a href="{{ route('onboarding.register') }}">Get started</a>
      </div>
      <div class="foot-col">
        <h4>Account</h4>
        <a href="{{ url('/admin/login') }}">Sign in</a>
        <a href="{{ route('onboarding.register') }}">Create account</a>
        <a href="{{ route('password.request') }}">Reset password</a>
      </div>
      <div class="foot-col">
        <h4>Legal</h4>
        <a href="{{ route('privacy') }}">Privacy Policy</a>
        <a href="{{ route('terms') }}">Terms of Service</a>
        <a href="{{ route('home') }}#faq">FAQ</a>
      </div>
    </div>
  </div>
  <div class="wrap">
    <div class="foot-bar">
      <span>© {{ date('Y') }} Cryptocoinex · Live market trading.</span>
      <span class="foot-disc">⚠ Trading involves risk and you can lose your funds. Only trade what you can afford to lose. Not financial advice.</span>
    </div>
  </div>
</footer>

<script>
  // Nav shadow on scroll
  var nav = document.getElementById('nav');
  var onScroll = function(){ nav.classList.toggle('scrolled', window.scrollY > 12); };
  onScroll(); window.addEventListener('scroll', onScroll, {passive:true});

  // Scroll-reveal
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
  }, {threshold:.12, rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.reveal').forEach(function(el){ io.observe(el); });
</script>
@stack('scripts')
@include('partials.tawk')
</body>
</html>
