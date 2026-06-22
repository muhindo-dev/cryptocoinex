@extends('layouts.public')
@section('title', 'Features — The Cryptocoinex platform')
@section('desc', 'Live charts, one-tap trades, fast deposits and withdrawals, tournaments and a secure account.')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ PAGE HEAD ════ --}}
<section class="page-head">
  <div class="wrap">
    <div class="crumb reveal"><a href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right"></i> <span>Features</span></div>
    <h1 class="reveal" data-d="1">Everything you need to <span class="grad">trade</span></h1>
    <p class="reveal" data-d="2">Professional tools, real markets and fast payouts, in one focused workspace.</p>
  </div>
</section>

{{-- ════ BENTO FEATURES ════ --}}
<section id="features" style="padding-top:30px;">
  <div class="wrap">
    <div class="bento reveal">
      <div class="tile t-chart">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-chart-line"></i></div>
        <h3>Live charts &amp; one-tap trades</h3>
        <p>Candles, line &amp; area with MA / RSI down to 10-second intervals. Call the market up or down in a single tap.</p>
        <div class="mini"><svg viewBox="0 0 600 160" preserveAspectRatio="none">
          <defs><linearGradient id="mg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#16d291" stop-opacity=".35"/><stop offset="1" stop-color="#16d291" stop-opacity="0"/></linearGradient></defs>
          <path id="bArea" fill="url(#mg)"></path><path id="bLine" fill="none" stroke="#1ad79a" stroke-width="2.2" stroke-linejoin="round"></path>
        </svg></div>
      </div>
      <div class="tile t-tour"><div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-wallet"></i></div>
        <h3>Easy deposits</h3><p>Fund {{ $fundFrom }} and start trading in minutes, credited around the clock.</p></div>
      <div class="tile t-lead"><div class="tic" style="background:rgba(22,210,145,.14);color:var(--grn);"><i class="fas fa-money-bill-transfer"></i></div>
        <h3>Fast withdrawals</h3><p>Withdraw your balance any time, processed 24/7.</p></div>
      <div class="tile t-acad"><div class="tic" style="background:rgba(155,123,255,.14);color:var(--violet);"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academy</h3><p>{{ $lessonCount }} free video lessons. <a href="{{ route('academy') }}" style="color:var(--gold);font-weight:600;">Browse the course</a>.</p></div>
      <div class="tile t-ach"><div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-shield-halved"></i></div>
        <h3>Secure &amp; verified</h3><p>KYC-protected accounts with encrypted funds and data.</p></div>
      <div class="tile t-jour"><div class="tic" style="background:rgba(251,191,36,.14);color:#fbbf24;"><i class="fas fa-trophy"></i></div>
        <h3>Tournaments</h3><p>Compete in timed challenges and top the global leaderboard.</p></div>
    </div>
  </div>
</section>

{{-- ════ SHOWCASE ════ --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">The interface</span><h2>Precise. Fast. Built for traders.</h2>
      <p>Live prices, instant fills and a workspace that stays out of your way.</p></div>
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
          <div style="font-size:.62rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.06em;">Account balance</div>
          <div class="bal">{{ number_format($startBalance) }}</div>
          <div class="deal"><span class="av" style="background:rgba(22,210,145,.16);color:var(--grn);">▲</span><div style="font-size:.7rem;color:var(--tx2);">BTC · BUY<br><span style="color:var(--tx3);">+80 {{ $cur }}</span></div></div>
          <div class="deal"><span class="av" style="background:rgba(255,77,106,.16);color:var(--red);">▼</span><div style="font-size:.7rem;color:var(--tx2);">ETH · SELL<br><span style="color:var(--tx3);">+62 {{ $cur }}</span></div></div>
          <div class="tc-bot" style="border:none;padding:6px 0 0;"><div class="tc-btn tc-sell" style="padding:10px;">SELL</div><div class="tc-btn tc-buy" style="padding:10px;">BUY</div></div>
        </div>
      </div>
    </div>
    <div class="anno reveal">
      <span><i class="fas fa-check"></i> Live prices every second</span>
      <span><i class="fas fa-check"></i> Indicators &amp; chart types</span>
      <span><i class="fas fa-check"></i> Fast deposits &amp; payouts</span>
      <span><i class="fas fa-check"></i> Works on mobile</span>
    </div>
  </div>
</section>

@include('public.partials.final-cta')
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
