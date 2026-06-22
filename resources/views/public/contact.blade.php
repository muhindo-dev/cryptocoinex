@extends('layouts.public')
@section('title', 'Contact us')
@section('desc', 'Get in touch with the Cryptocoinex team — support for accounts, deposits and withdrawals.')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ PAGE HEAD ════ --}}
<section class="page-head">
  <div class="wrap">
    <div class="crumb reveal"><a href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right"></i> <span>Contact</span></div>
    <h1 class="reveal" data-d="1">Get in <span class="grad">touch</span></h1>
    <p class="reveal" data-d="2">Questions about your account, deposits or withdrawals? Our team is here to help.</p>
  </div>
</section>

{{-- ════ CONTACT CHANNELS ════ --}}
<section style="padding-top:30px;">
  <div class="wrap">
    <div class="explore reveal">
      <a href="mailto:support@cryptocoinex.net" class="ex">
        <div class="tic" style="background:rgba(77,141,255,.14);color:var(--blue);"><i class="fas fa-envelope"></i></div>
        <h3>Email support</h3>
        <p>Send us a message and we'll reply as soon as we can, usually within a few hours.</p>
        <span class="go">support@cryptocoinex.net <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="#" class="ex" onclick="if(window.Tawk_API&amp;&amp;Tawk_API.maximize){Tawk_API.maximize();return false;}">
        <div class="tic" style="background:rgba(22,210,145,.14);color:var(--grn);"><i class="fas fa-comments"></i></div>
        <h3>Live chat</h3>
        <p>Tap the chat bubble in the corner to talk to us in real time during support hours.</p>
        <span class="go">Start a chat <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
      <a href="{{ route('faq') }}" class="ex">
        <div class="tic" style="background:rgba(245,166,35,.14);color:var(--gold);"><i class="fas fa-circle-question"></i></div>
        <h3>Read the FAQ</h3>
        <p>Deposits, withdrawals, security and risk, answered plainly.</p>
        <span class="go">Open the FAQ <i class="fas fa-arrow-right" style="font-size:.74rem;"></i></span>
      </a>
    </div>
    <p class="reveal" style="text-align:center;color:var(--tx3);font-size:.85rem;margin:30px auto 0;max-width:660px;line-height:1.6;">
      Cryptocoinex is an online platform for trading crypto, forex and gold markets. Trading involves risk and you
      can lose your funds — only trade what you can afford to lose. Nothing on this site is financial advice.
    </p>
  </div>
</section>

@include('public.partials.final-cta', ['ctaTitle' => 'Ready to start trading?'])
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
