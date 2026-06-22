@extends('layouts.public')
@section('title', 'Trade Crypto, Forex & Gold on Live Markets')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ HERO ════ --}}
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="h-eyebrow reveal"><span class="pill"><span class="dot"></span> Live markets · Real-time prices</span></div>
      <h1 class="h-title reveal" data-d="1">Trade the markets.<br><span class="grad">Crypto, forex &amp; gold.</span></h1>
      <p class="h-lead reveal" data-d="2">A modern platform to trade live crypto, forex and gold markets. Fund and
        withdraw in USD, with transparent payouts shown on every trade and fast execution.</p>
      <div class="h-cta reveal" data-d="3">
        <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Open your account <i class="fas fa-arrow-right" style="font-size:.78rem;"></i></a>
        <a href="{{ route('how') }}" class="btn btn-ghost">How it works</a>
      </div>
      <div class="h-trust reveal" data-d="4">
        <div class="h-trust-tx" style="display:flex;gap:18px;flex-wrap:wrap;align-items:center;">
          <span><i class="fas fa-shield-halved" style="color:var(--gold);"></i> Identity-verified accounts</span>
          <span><i class="fas fa-lock" style="color:var(--gold);"></i> Encrypted &amp; secure</span>
        </div>
      </div>
    </div>

    {{-- Live trading card --}}
    <div class="hero-visual reveal" data-d="2">
      <div class="tcard">
        <div class="tc-float tf-win"><span class="i"><i class="fas fa-trophy"></i></span><div><div class="t">Trade won</div><div class="v" style="color:var(--grn);">+144</div></div></div>
        <div class="tc-float tf-streak"><span class="i"><i class="fas fa-fire"></i></span><div><div class="t">Win streak</div><div class="v" style="color:var(--gold);">5</div></div></div>
        <div class="tc-top">
          <span class="tc-ic">BT</span>
          <div><div class="tc-sym">BTC / USD</div><div class="tc-sub">Crypto</div></div>
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
          <div class="meta"><b>BUY</b> · 50 {{ $cur }} · 60s<br>Entry 67,388</div>
          <span class="pnl" id="hcPnl">▲ Winning</span>
        </div>
        <div class="tc-bot">
          <div class="tc-btn tc-sell">▼ SELL <span class="pct">{{ $payout }}%</span></div>
          <div class="tc-btn tc-buy">▲ BUY <span class="pct">{{ $payout }}%</span></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Marquee --}}
  <div class="wrap"><div class="marquee reveal"><div class="mq-track" id="mqTrack"></div></div></div>

  {{-- Stats --}}
  <div class="wrap">
    <div class="stats reveal">
      <div class="stat"><div class="v">{{ $payout }}%</div><div class="l">Payout rate</div></div>
      <div class="stat"><div class="v" data-count="{{ $assetCount }}" data-suffix="+">{{ $assetCount }}+</div><div class="l">Markets to trade</div></div>
      <div class="stat"><div class="v">{{ $minLabel }}</div><div class="l">Minimum deposit</div></div>
      <div class="stat"><div class="v">24/7</div><div class="l">Deposits &amp; payouts</div></div>
    </div>
  </div>
</section>

{{-- ════ EXPLORE (links to sub-pages) ════ --}}
<section id="explore" style="padding-top:20px;">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Explore Cryptocoinex</span><h2>Everything you need, on one platform</h2>
      <p>Take a closer look at how it works before you sign up.</p></div>
    <div class="explore reveal">
      <a href="{{ route('features') }}" class="ex">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-layer-group"></i></div>
        <h3>The platform</h3>
        <p>Live charts, one-tap trades, fast deposits and withdrawals, tournaments and a secure account.</p>
        <span class="go">See features <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="{{ route('how') }}" class="ex">
        <div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-list-check"></i></div>
        <h3>How it works</h3>
        <p>Open an account, deposit {{ $fundFrom }}, and start trading live markets in three simple steps.</p>
        <span class="go">See the steps <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="{{ route('academy') }}" class="ex">
        <div class="tic" style="background:rgba(155,123,255,.14);color:var(--violet);"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academy</h3>
        <p>{{ $lessonCount }} free video lessons, from your first trade to risk management and strategy.</p>
        <span class="go">Start learning <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
    </div>
  </div>
</section>

@include('public.partials.final-cta')
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
