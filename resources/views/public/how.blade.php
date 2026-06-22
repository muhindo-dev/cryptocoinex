@extends('layouts.public')
@section('title', 'How it works — Trading in three steps')
@section('desc', 'Open an account, deposit funds, and trade live markets in three simple steps. Practise free first.')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ PAGE HEAD ════ --}}
<section class="page-head">
  <div class="wrap">
    <div class="crumb reveal"><a href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right"></i> <span>How it works</span></div>
    <h1 class="reveal" data-d="1">Trading in <span class="grad">three steps</span></h1>
    <p class="reveal" data-d="2">From sign-up to your first trade in minutes. No experience needed.</p>
  </div>
</section>

{{-- ════ STEPS ════ --}}
<section style="padding-top:30px;">
  <div class="wrap">
    <div class="steps">
      <div class="step reveal" data-d="1"><div class="n">1</div><h3>Open your account</h3><p>Name, email and a quick identity check. Ready in minutes.</p></div>
      <div class="step reveal" data-d="2"><div class="n">2</div><h3>Deposit funds</h3><p>Fund {{ $fundFrom }} and it's credited to your account in minutes.</p></div>
      <div class="step reveal" data-d="3"><div class="n">3</div><h3>Trade &amp; withdraw</h3><p>Place your trades on live markets, with the payout and risk shown up front, and withdraw any time.</p></div>
    </div>
  </div>
</section>

{{-- ════ PRACTICE FIRST ════ --}}
<section id="practice" style="padding-top:0;">
  <div class="wrap">
    <div class="academy reveal">
      <div>
        <span class="sec-tag">New to trading?</span>
        <h2 style="margin-top:14px;">Practise free before you go live</h2>
        <p>Not ready to deposit? Every account comes with a free demo loaded with {{ number_format($startBalance) }} virtual {{ $cur }}.
          Trade the same live markets, find your strategy, then switch to live whenever you're ready.</p>
        <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Try the free demo <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></a>
      </div>
      <div class="acl">
        <div class="row"><span class="i"><i class="fas fa-wallet"></i></span> Start with {{ number_format($startBalance) }} virtual {{ $cur }} <span class="lv">Demo</span></div>
        <div class="row"><span class="i"><i class="fas fa-chart-line"></i></span> Trade real, live market prices <span class="lv">Live data</span></div>
        <div class="row"><span class="i"><i class="fas fa-rotate"></i></span> Reset and practise as often as you like <span class="lv">Free</span></div>
        <div class="row"><span class="i"><i class="fas fa-toggle-on"></i></span> Switch to a live account any time <span class="lv">1 tap</span></div>
      </div>
    </div>
  </div>
</section>

{{-- ════ HELP LINKS ════ --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Still curious?</span><h2>Read more before you start</h2></div>
    <div class="explore reveal">
      <a href="{{ route('features') }}" class="ex">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-layer-group"></i></div>
        <h3>Platform features</h3><p>See the charts, deposits, withdrawals, tournaments and security in detail.</p>
        <span class="go">View features <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="{{ route('academy') }}" class="ex">
        <div class="tic" style="background:rgba(155,123,255,.14);color:var(--violet);"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academy</h3><p>Learn to trade from zero with {{ $lessonCount }} free video lessons.</p>
        <span class="go">Start learning <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="{{ route('faq') }}" class="ex">
        <div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-circle-question"></i></div>
        <h3>FAQ</h3><p>Deposits, withdrawals, security and risk, answered plainly.</p>
        <span class="go">Read the FAQ <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
    </div>
  </div>
</section>

@include('public.partials.final-cta')
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
