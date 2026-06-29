{{-- Shared shell for student pages (profile, history, leaderboard, journal) --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  @include('partials.sw-kill')
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Cryptocoinex') — {{ config('app.name') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo-square.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#f59e0b">
  <script>
    window.CX_DEFAULT_THEME = '{{ auth()->user()->theme ?? 'dark' }}';
    window.CX_THEME_SAVE = { url: '{{ route('trade.theme') }}', token: '{{ csrf_token() }}' };
  </script>
  <script src="{{ asset('js/theme.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fa/css/all.min.css') }}">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--font-sans,'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif);
      background:var(--bg-base);color:var(--text-primary);font-size:14px;min-height:100vh;display:flex;
      -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;letter-spacing:-0.006em;}
    a{color:inherit;}
    /* Nav rail */
    .ta-nav{width:66px;flex-shrink:0;background:var(--bg-surface);border-right:1px solid var(--border);
      display:flex;flex-direction:column;align-items:center;padding:11px 0;gap:3px;position:sticky;top:0;height:100vh;
      overflow-y:auto;overflow-x:hidden;scrollbar-width:none;}
    .ta-nav::-webkit-scrollbar{width:0;}
    .ta-logo{width:40px;height:40px;margin-bottom:12px;border-radius:11px;flex-shrink:0;
      background:linear-gradient(135deg,var(--gold),#d97706);display:flex;align-items:center;justify-content:center;
      box-shadow:0 2px 12px rgba(245,158,11,.35);}
    .ta-nav-btn{width:54px;min-height:46px;padding:6px 0;border-radius:11px;flex-shrink:0;display:flex;flex-direction:column;
      align-items:center;justify-content:center;gap:4px;
      color:var(--text-muted);text-decoration:none;font-size:.58rem;font-weight:700;letter-spacing:.01em;
      transition:background .15s,color .15s;}
    .ta-nav-btn i{font-size:1.02rem;}
    .ta-nav-btn:hover{background:var(--bg-hover);color:var(--text-primary);}
    .ta-nav-btn.on{background:var(--bg-hover);color:var(--gold);box-shadow:inset 2px 0 0 var(--gold);}
    .ta-nav-bot{margin-top:auto;padding-top:6px;display:flex;flex-direction:column;align-items:center;gap:3px;}
    /* Main */
    .ta-main{flex:1;min-width:0;display:flex;flex-direction:column;}
    .ta-top{height:56px;flex-shrink:0;background:var(--bg-surface);border-bottom:1px solid var(--border);
      display:flex;align-items:center;gap:14px;padding:0 20px;}
    .ta-title{font-size:1rem;font-weight:800;letter-spacing:.02em;}
    .ta-top-right{margin-left:auto;display:flex;align-items:center;gap:14px;}
    .ta-bal{display:flex;align-items:center;gap:7px;background:var(--bg-elevated);border:1px solid var(--border);
      border-radius:8px;padding:6px 12px;font-size:.78rem;font-weight:700;}
    .ta-bal .amt{color:var(--gold);font-variant-numeric:tabular-nums;}
    .ta-bal .cur{font-size:.6rem;color:var(--text-muted);}
    /* Notification bell */
    .ta-bell{position:relative;cursor:pointer;width:38px;height:38px;border-radius:9px;border:1px solid var(--border);
      background:var(--bg-elevated);display:flex;align-items:center;justify-content:center;color:var(--text-muted);}
    .ta-bell:hover{color:var(--gold);}
    .ta-bell-badge{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;padding:0 4px;border-radius:9px;
      background:var(--red);color:#fff;font-size:.58rem;font-weight:800;display:none;align-items:center;justify-content:center;}
    .ta-bell-badge.show{display:flex;}
    .ta-notif-dd{position:absolute;top:46px;right:0;width:330px;max-height:420px;overflow-y:auto;z-index:200;
      background:var(--bg-elevated);border:1px solid var(--border);border-radius:11px;box-shadow:0 12px 48px rgba(0,0,0,.6);display:none;}
    .ta-notif-dd.open{display:block;}
    .ta-notif-head{display:flex;justify-content:space-between;align-items:center;padding:11px 14px;border-bottom:1px solid var(--border);
      font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);}
    .ta-notif-head button{background:none;border:none;color:var(--gold);font-size:.66rem;font-weight:700;cursor:pointer;}
    .ta-notif-item{display:flex;gap:11px;padding:11px 14px;border-bottom:1px solid var(--border);text-decoration:none;}
    .ta-notif-item.unread{background:rgba(245,158,11,.05);}
    .ta-notif-ico{width:30px;height:30px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
      background:var(--gold-muted);color:var(--gold);font-size:.78rem;}
    .ta-notif-t{font-size:.74rem;font-weight:700;color:var(--text-primary);}
    .ta-notif-b{font-size:.66rem;color:var(--text-muted);margin-top:2px;line-height:1.4;}
    .ta-notif-time{font-size:.58rem;color:var(--text-dim);margin-top:3px;}
    .ta-notif-empty{padding:28px 14px;text-align:center;color:var(--text-muted);font-size:.74rem;}
    /* Content */
    .ta-content{flex:1;padding:22px;overflow-y:auto;}
    .ta-card{background:var(--bg-surface);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:18px;}
    .ta-card h2{font-size:.95rem;font-weight:800;margin-bottom:14px;}
    @media(max-width:640px){
      .ta-nav{width:54px;}
      .ta-content{padding:14px;}
      .ta-top{padding:0 14px;gap:10px;}
      .ta-title{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.92rem;}
      .ta-top-right{gap:11px;}
    }
  </style>
  @stack('styles')
</head>
<body>
@php
  $taWallet = auth()->user()?->tradingWallet;
  $taUnread = app(\App\Services\Trading\NotificationService::class)->unreadCount(auth()->id());
@endphp

<nav class="ta-nav">
  <a href="{{ route('trade.index') }}" class="ta-logo">
    <svg width="19" height="19" viewBox="0 0 24 24" fill="none">
      <polyline points="3,17 8,10 13,14 21,5" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
      <polyline points="17,5 21,5 21,9" stroke="#0F172A" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </a>
  <a href="{{ route('trade.index') }}" class="ta-nav-btn {{ request()->routeIs('trade.index') ? 'on' : '' }}"><i class="fas fa-chart-column"></i>Trade</a>
  <a href="{{ route('trade.history.page') }}" class="ta-nav-btn {{ request()->routeIs('trade.history.page') ? 'on' : '' }}"><i class="fas fa-clock-rotate-left"></i>History</a>
  <a href="{{ route('trade.leaderboard') }}" class="ta-nav-btn {{ request()->routeIs('trade.leaderboard') ? 'on' : '' }}"><i class="fas fa-ranking-star"></i>Ranks</a>
  <a href="{{ route('trade.tournaments.index') }}" class="ta-nav-btn {{ request()->routeIs('trade.tournaments.*') ? 'on' : '' }}"><i class="fas fa-trophy"></i>Cups</a>
  <a href="{{ route('trade.education.index') }}" class="ta-nav-btn {{ request()->routeIs('trade.education.*') ? 'on' : '' }}"><i class="fas fa-graduation-cap"></i>Learn</a>
  <a href="{{ route('trade.wallet.page') }}" class="ta-nav-btn {{ request()->routeIs('trade.wallet.page') ? 'on' : '' }}"><i class="fas fa-wallet"></i>Wallet</a>
  <a href="{{ route('trade.live') }}" class="ta-nav-btn {{ request()->routeIs('trade.live*') ? 'on' : '' }}"><i class="fas fa-sack-dollar"></i>Live</a>
  <a href="{{ route('trade.kyc') }}" class="ta-nav-btn {{ request()->routeIs('trade.kyc*') ? 'on' : '' }}" style="position:relative;"><i class="fas fa-id-card"></i>Verify
    @if(auth()->user() && auth()->user()->kyc_status !== 'approved')
      <span style="position:absolute;top:6px;right:9px;width:8px;height:8px;border-radius:50%;background:{{ auth()->user()->kyc_status==='pending'?'var(--gold)':'var(--red)' }};border:1.5px solid var(--bg-surface);"></span>
    @endif
  </a>
  <a href="{{ route('trade.profile') }}" class="ta-nav-btn {{ request()->routeIs('trade.profile') ? 'on' : '' }}"><i class="fas fa-user"></i>Profile</a>
  <div class="ta-nav-bot">
    @if(auth()->user()?->canAccessAdmin())
      <a href="{{ route('admin.dashboard') }}" class="ta-nav-btn"><i class="fas fa-shield-halved"></i>Admin</a>
    @endif
    <form method="POST" action="{{ route('admin.logout') }}">@csrf
      <button class="ta-nav-btn" type="submit" style="background:none;border:none;cursor:pointer;"><i class="fas fa-right-from-bracket"></i>Exit</button>
    </form>
  </div>
</nav>

<div class="ta-main">
  <header class="ta-top">
    <span class="ta-title">@yield('title', 'Cryptocoinex')</span>
    <div class="ta-top-right">
      <div class="ta-bell" onclick="cxTheme.toggle()" title="Toggle theme" style="cursor:pointer;">
        <i data-theme-icon class="fas fa-sun"></i>
      </div>
      <div style="position:relative;" id="taBellWrap">
        <div class="ta-bell" id="taBell">
          <i class="fas fa-bell"></i>
          <span class="ta-bell-badge {{ $taUnread > 0 ? 'show' : '' }}" id="taBellBadge">{{ $taUnread }}</span>
        </div>
        <div class="ta-notif-dd" id="taNotifDd">
          <div class="ta-notif-head">
            <span>Notifications</span>
            <button id="taMarkAll">Mark all read</button>
          </div>
          <div id="taNotifList"><div class="ta-notif-empty">Loading…</div></div>
        </div>
      </div>
      <a href="{{ route('trade.wallet.page') }}" class="ta-bal">
        <i class="fas fa-wallet" style="font-size:.66rem;color:var(--text-muted)"></i>
        <span class="amt">{{ number_format($taWallet?->balance ?? 0) }}</span>
        <span class="cur">USD</span>
      </a>
    </div>
  </header>
  <main class="ta-content">
    @yield('content')
  </main>
</div>

<script>
(function(){
  const bell = document.getElementById('taBell');
  const dd = document.getElementById('taNotifDd');
  const list = document.getElementById('taNotifList');
  const badge = document.getElementById('taBellBadge');
  const feedUrl = '{{ route('trade.notifications') }}';
  const readUrl = '{{ route('trade.notifications.read') }}';
  const token = '{{ csrf_token() }}';

  function timeAgo(iso){
    const s = Math.floor((Date.now() - new Date(iso))/1000);
    if(s<60) return s+'s ago'; if(s<3600) return Math.floor(s/60)+'m ago';
    if(s<86400) return Math.floor(s/3600)+'h ago'; return Math.floor(s/86400)+'d ago';
  }
  function render(items){
    if(!items.length){ list.innerHTML='<div class="ta-notif-empty">No notifications yet.</div>'; return; }
    list.innerHTML = items.map(n=>`
      <a href="${n.action_url||'#'}" class="ta-notif-item ${n.read_at?'':'unread'}">
        <div class="ta-notif-ico"><i class="fas ${n.icon||'fa-bell'}"></i></div>
        <div style="min-width:0;">
          <div class="ta-notif-t">${n.title}</div>
          ${n.body?`<div class="ta-notif-b">${n.body}</div>`:''}
          <div class="ta-notif-time">${timeAgo(n.created_at)}</div>
        </div>
      </a>`).join('');
  }
  function load(){
    fetch(feedUrl,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(d=>{
      render(d.items||[]);
      if(d.unread>0){ badge.textContent=d.unread; badge.classList.add('show'); }
      else badge.classList.remove('show');
    }).catch(()=>{});
  }
  bell.addEventListener('click', e=>{ e.stopPropagation(); dd.classList.toggle('open'); if(dd.classList.contains('open')) load(); });
  document.addEventListener('click', e=>{ if(!dd.contains(e.target) && !bell.contains(e.target)) dd.classList.remove('open'); });
  document.getElementById('taMarkAll').addEventListener('click', e=>{
    e.stopPropagation();
    fetch(readUrl,{method:'POST',headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'}})
      .then(()=>{ badge.classList.remove('show'); load(); });
  });
  load();
  setInterval(load, 30000);
})();
// Service worker disabled — actively unregister any old one and clear caches
// (a previous cache-first worker caused stale assets / blank screens).
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then((rs) => rs.forEach((r) => r.unregister())).catch(() => {});
  if (window.caches) caches.keys().then((ks) => ks.forEach((k) => caches.delete(k))).catch(() => {});
}
</script>
@stack('scripts')
@include('partials.tawk')
</body>
</html>
