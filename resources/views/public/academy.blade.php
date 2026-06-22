@extends('layouts.public')
@section('title', 'Academy — Learn to trade from zero')
@section('desc', 'A free course with video lessons, from your first trade to risk management and strategy.')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ PAGE HEAD ════ --}}
<section class="page-head">
  <div class="wrap">
    <div class="crumb reveal"><a href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right"></i> <span>Academy</span></div>
    <h1 class="reveal" data-d="1">Learn to trade, <span class="grad">from zero</span></h1>
    <p class="reveal" data-d="2">A free course with {{ $lessonCount }} video lessons, from your first trade to risk management and strategy.</p>
  </div>
</section>

{{-- ════ COURSE ════ --}}
<section style="padding-top:30px;">
  <div class="wrap">
    <div class="academy reveal">
      <div>
        <span class="sec-tag">Cryptocoinex Academy</span>
        <h2 style="margin-top:14px;">The full beginner-to-strategy path</h2>
        <p>Watch at your own pace and practise everything on a free demo account loaded with
          {{ number_format($startBalance) }} virtual {{ $cur }}. No payment required to start.</p>
        <a href="{{ route('onboarding.register') }}" class="btn btn-gold">Start learning free <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></a>
      </div>
      <div class="acl">
        @foreach([
          ['How to Trade: the basics','Beginner'],
          ['Reading candlesticks &amp; trends','Beginner'],
          ['RSI, MACD &amp; moving averages','Base'],
          ['Strategies that actually work','Advanced'],
          ['Risk management &amp; psychology','Advanced'],
        ] as $l)
          <div class="row"><span class="i"><i class="fas fa-play"></i></span> {!! $l[0] !!} <span class="lv">{{ $l[1] }}</span></div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- ════ WHY LEARN HERE ════ --}}
<section style="padding-top:0;">
  <div class="wrap">
    <div class="sh reveal"><span class="sec-tag">Why learn here</span><h2>Theory, then practice, in one place</h2></div>
    <div class="explore reveal">
      <div class="ex">
        <div class="tic" style="background:rgba(22,210,145,.14);color:var(--grn);"><i class="fas fa-video"></i></div>
        <h3>Short video lessons</h3><p>{{ $lessonCount }} focused lessons you can finish in a sitting, each building on the last.</p>
      </div>
      <div class="ex">
        <div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-flask"></i></div>
        <h3>Practise as you learn</h3><p>Apply every lesson on a free demo with virtual funds, on the same live markets.</p>
      </div>
      <div class="ex">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-shield-halved"></i></div>
        <h3>Trade responsibly</h3><p>Risk management and psychology are built into the course, not an afterthought.</p>
      </div>
    </div>
  </div>
</section>

@include('public.partials.final-cta', ['ctaTitle' => 'Ready to put it into practice?'])
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
