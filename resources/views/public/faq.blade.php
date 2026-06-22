@extends('layouts.public')
@section('title', 'FAQ — Common questions')
@section('desc', 'Deposits, withdrawals, security and risk on Cryptocoinex, answered plainly.')

@push('styles')@include('public.partials.marketing-styles')@endpush

@section('content')
{{-- ════ PAGE HEAD ════ --}}
<section class="page-head">
  <div class="wrap">
    <div class="crumb reveal"><a href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right"></i> <span>FAQ</span></div>
    <h1 class="reveal" data-d="1">Everything you <span class="grad">might ask</span></h1>
    <p class="reveal" data-d="2">Short, honest answers. If something isn't here, reach out and we'll help.</p>
  </div>
</section>

{{-- ════ FAQ ════ --}}
<section style="padding-top:30px;">
  <div class="wrap">
    @php $faqs = [
      ['What can I trade?','Crypto, forex and gold, all on live, real-time prices. Every trade shows its payout and the amount at risk before you confirm, so you always know the terms up front.'],
      ['How do I deposit?','Fund your account '.$fundFrom.'. Send to the address on your deposit screen, upload the receipt, and we credit you, usually within minutes.'],
      ['How fast are withdrawals?','Request a payout any time. We process withdrawals around the clock, typically within hours.'],
      ['Is my account secure?','Yes. Accounts are protected with identity verification (KYC) and your funds and data are encrypted end to end.'],
      ['Is trading risky?','Yes. Trading involves real risk and you can lose your stake. Only trade what you can afford to lose. Nothing here is financial advice.'],
      ['Can I practice first?','Absolutely. Every account includes a free demo loaded with '.number_format($startBalance).' virtual '.$cur.', so you can learn the platform before going live.'],
    ]; @endphp
    <div class="faq reveal">
      @foreach($faqs as $f)
      <div class="qa">
        <div class="qa-q">{{ $f[0] }}<i class="fas fa-plus ch"></i></div>
        <div class="qa-a"><div class="in">{{ $f[1] }}</div></div>
      </div>
      @endforeach
    </div>
  </div>
</section>

@include('public.partials.final-cta', ['ctaTitle' => 'Got it. Ready to start?'])
@endsection

@push('scripts')@include('public.partials.marketing-scripts')@endpush
