@extends('layouts.trade-app')
@section('title', 'Live Account')

@push('styles')
<style>
  .la-hero{position:relative;overflow:hidden;border-radius:16px;padding:24px 26px;margin-bottom:18px;
    background:radial-gradient(120% 140% at 100% 0%,rgba(245,158,11,.16),transparent 55%),
               linear-gradient(135deg,#0d1117 0%,#10151f 100%);border:1px solid var(--border);}
  .la-hero::after{content:"";position:absolute;right:-40px;top:-40px;width:220px;height:220px;border-radius:50%;
    background:radial-gradient(circle,rgba(245,158,11,.18),transparent 70%);pointer-events:none;}
  .la-hero-top{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;position:relative;z-index:1;}
  .la-label{font-size:.64rem;text-transform:uppercase;letter-spacing:.09em;color:var(--text-muted);font-weight:800;margin-bottom:8px;
    display:flex;align-items:center;gap:7px;}
  .la-label .dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 0 3px var(--green-muted);animation:lapulse 2s infinite;}
  @keyframes lapulse{0%,100%{opacity:1}50%{opacity:.45}}
  .la-bal{font-size:2.7rem;font-weight:900;color:#fff;font-variant-numeric:tabular-nums;letter-spacing:-.02em;line-height:1;}
  .la-bal small{font-size:1rem;color:var(--gold);font-weight:800;margin-right:6px;}
  .la-sub{font-size:.72rem;color:var(--text-muted);margin-top:8px;}
  .la-actions{display:flex;gap:10px;flex-wrap:wrap;}
  .la-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:11px;font-size:.82rem;font-weight:800;
    cursor:pointer;text-decoration:none;border:1px solid transparent;transition:.15s;white-space:nowrap;}
  .la-btn-dep{background:linear-gradient(135deg,#16d291,#0fa873);color:#04130d;}
  .la-btn-dep:hover{filter:brightness(1.08);}
  .la-btn-wd{background:var(--bg-elevated);border-color:var(--border);color:var(--text-primary);}
  .la-btn-wd:hover{border-color:var(--gold);color:var(--gold);}
  .la-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:22px;position:relative;z-index:1;}
  @media(max-width:720px){.la-metrics{grid-template-columns:repeat(2,1fr);}}
  .la-metric{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:11px;padding:12px 14px;}
  .la-metric .v{font-size:1.12rem;font-weight:900;font-variant-numeric:tabular-nums;}
  .la-metric .l{font-size:.58rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:3px;font-weight:700;}
  .la-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:18px;align-items:start;}
  @media(max-width:900px){.la-grid{grid-template-columns:1fr;}}
  .la-row{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--border);}
  .la-row:last-child{border-bottom:none;}
  .la-ic{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;}
  .la-ic.credit{background:var(--green-muted);color:var(--green);}
  .la-ic.debit{background:var(--red-muted);color:var(--red);}
  .la-ic.profit{background:var(--gold-muted);color:var(--gold);}
  .la-amt{margin-left:auto;font-weight:800;font-variant-numeric:tabular-nums;font-size:.9rem;white-space:nowrap;}
  .la-pos{color:var(--green);} .la-neg{color:var(--red);}
  .la-pend{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:var(--bg-elevated);
    border:1px dashed var(--border);margin-bottom:8px;font-size:.78rem;}
  .la-chip{font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;padding:3px 8px;border-radius:6px;
    background:rgba(245,158,11,.14);color:var(--gold);margin-left:auto;white-space:nowrap;}
  .la-howto{font-size:.78rem;color:var(--text-muted);line-height:1.7;white-space:pre-line;}
  .la-num{display:inline-flex;align-items:center;gap:7px;background:var(--bg-elevated);border:1px solid var(--border);
    border-radius:9px;padding:8px 12px;font-weight:800;color:var(--gold);font-size:.84rem;margin-top:6px;}
  .la-explain{display:flex;gap:11px;padding:12px;border-radius:11px;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.18);}
  .la-explain i{color:var(--gold);margin-top:2px;}
  .la-empty{text-align:center;padding:26px 10px;color:var(--text-muted);font-size:.8rem;}
  h2.la-h{margin:0 0 4px;font-size:1rem;}
</style>
@endpush

@section('content')
@if(session('success'))
<div style="background:var(--green-muted);color:var(--green);border:1px solid rgba(0,201,123,.3);padding:11px 15px;border-radius:10px;font-size:.82rem;margin-bottom:16px;">
  <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- ── KYC gate banner ── --}}
@if(auth()->user()->requiresKyc())
@php $ks = auth()->user()->kyc_status; @endphp
<a href="{{ route('trade.kyc') }}" style="display:flex;align-items:center;gap:14px;text-decoration:none;margin-bottom:16px;
   background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:14px 16px;">
  <i class="fas fa-id-card" style="font-size:1.4rem;color:var(--gold);"></i>
  <div style="flex:1;min-width:0;">
    <div style="font-weight:800;color:var(--text-primary);font-size:.9rem;">
      @if($ks==='pending') Verification under review
      @elseif($ks==='resubmit') Action needed — please redo your verification
      @elseif($ks==='declined') Verification declined — try again
      @else Verify your identity to deposit, trade live & withdraw @endif
    </div>
    <div style="font-size:.74rem;color:var(--text-muted);margin-top:2px;">
      @if($ks==='pending') We're reviewing your documents. You'll be notified once it's done.
      @else Real-money features unlock once you're verified. Tap to {{ $ks==='unverified' ? 'get verified' : 'continue' }}.@endif
    </div>
  </div>
  <i class="fas fa-chevron-right" style="color:var(--text-muted);"></i>
</a>
@endif

{{-- ── Hero balance ── --}}
<div class="la-hero">
  <div class="la-hero-top">
    <div>
      <div class="la-label"><span class="dot"></span> Live Account · Real Funds</div>
      <div class="la-bal"><small>{{ $wallet->currency }}</small>{{ number_format($wallet->balance) }}</div>
      <div class="la-sub">Real money you've deposited. You can trade it live, and you'll receive a share whenever profits are distributed.</div>
    </div>
    <div class="la-actions">
      <a href="{{ route('trade.live.deposit') }}" class="la-btn la-btn-dep"><i class="fas fa-circle-down"></i> Deposit</a>
      <a href="{{ route('trade.live.withdraw') }}" class="la-btn la-btn-wd"><i class="fas fa-arrow-up-from-bracket"></i> Withdraw</a>
    </div>
  </div>
  <div class="la-metrics">
    <div class="la-metric"><div class="v la-pos">+{{ number_format($wallet->total_profit) }}</div><div class="l">Profit received</div></div>
    <div class="la-metric"><div class="v">{{ number_format($available) }}</div><div class="l">Available to withdraw</div></div>
    <div class="la-metric"><div class="v la-pos">+{{ number_format($wallet->total_deposited) }}</div><div class="l">Lifetime Deposited</div></div>
    <div class="la-metric"><div class="v la-neg">−{{ number_format($wallet->total_withdrawn) }}</div><div class="l">Lifetime Withdrawn</div></div>
  </div>
</div>

{{-- ── Pending requests ── --}}
@if($pendingDeposits->isNotEmpty() || $pendingWithdrawals->isNotEmpty())
<div class="ta-card" style="margin-bottom:18px;">
  <h2 class="la-h"><i class="fas fa-hourglass-half" style="color:var(--gold)"></i> Awaiting review</h2>
  <p style="font-size:.74rem;color:var(--text-muted);margin:0 0 12px;">We're verifying these. You'll get an email the moment they're processed.</p>
  @foreach($pendingDeposits as $d)
  <div class="la-pend"><i class="fas fa-circle-down" style="color:var(--green)"></i>
    Deposit <strong>{{ $wallet->currency }} {{ number_format($d->amount) }}</strong> · ref {{ $d->reference }}
    <span class="la-chip">Pending</span></div>
  @endforeach
  @foreach($pendingWithdrawals as $w)
  <div class="la-pend"><i class="fas fa-arrow-up-from-bracket" style="color:var(--red)"></i>
    Withdraw <strong>{{ $wallet->currency }} {{ number_format($w->amount) }}</strong> · to {{ $w->payout_phone }}
    <span class="la-chip">Pending</span></div>
  @endforeach
</div>
@endif

<div class="la-grid">
  {{-- ── Recent activity ── --}}
  <div class="ta-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <h2 class="la-h">Recent activity</h2>
      <a href="{{ route('trade.live.transactions') }}" style="font-size:.74rem;color:var(--gold);font-weight:700;text-decoration:none;">View all →</a>
    </div>
    @forelse($recent as $tx)
    <div class="la-row">
      <div class="la-ic {{ $tx->type==='profit'?'profit':($tx->is_credit?'credit':'debit') }}">
        <i class="fas {{ $tx->type==='profit'?'fa-arrow-trend-up':($tx->type==='deposit'?'fa-arrow-down':($tx->type==='withdrawal'?'fa-arrow-up':'fa-sliders')) }}"></i>
      </div>
      <div style="min-width:0;">
        <div style="font-weight:700;font-size:.84rem;">{{ $tx->title }}</div>
        <div style="font-size:.68rem;color:var(--text-muted);">{{ $tx->created_at->format('d M Y · H:i') }}</div>
      </div>
      <div class="la-amt {{ $tx->is_credit?'la-pos':'la-neg' }}">{{ $tx->is_credit?'+':'−' }}{{ number_format(abs($tx->amount)) }}</div>
    </div>
    @empty
    <div class="la-empty"><i class="fas fa-wallet" style="font-size:1.6rem;opacity:.4;display:block;margin-bottom:10px;"></i>
      No activity yet. Make your first deposit to get started.</div>
    @endforelse
  </div>

  {{-- ── How it works ── --}}
  <div class="ta-card">
    <h2 class="la-h"><i class="fas fa-circle-info" style="color:var(--gold)"></i> How your Live Account works</h2>
    <div class="la-explain" style="margin:12px 0;">
      <i class="fas fa-hand-holding-dollar"></i>
      <div style="font-size:.78rem;color:var(--text-primary);line-height:1.6;">
        Your deposited funds are real. When the team makes profit, we run a
        <strong style="color:var(--gold)">profit distribution</strong> — the pool is split across members in
        proportion to their balance and credited straight to your account as a
        <span class="la-pos" style="font-weight:700;">positive transaction</span>. You can also trade your live
        balance directly from the trading screen by switching to <strong>Live</strong>.
      </div>
    </div>
    <div style="font-size:.74rem;color:var(--text-muted);font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px;">Deposit in crypto · {{ $cryptoNetwork }}</div>
    @if($cryptoAddress)
      <div class="la-num" style="word-break:break-all;"><i class="fab fa-bitcoin"></i> {{ $cryptoAddress }}</div>
    @else
      <div class="la-num"><i class="fab fa-bitcoin"></i> Address set on the deposit screen</div>
    @endif
    @if($paymentLink)
      <a href="{{ $paymentLink }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;font-size:.74rem;font-weight:800;color:var(--gold);text-decoration:none;"><i class="fas fa-arrow-up-right-from-square"></i> Pay via secure link</a>
    @endif
    <div class="la-howto" style="margin-top:12px;">{{ $instructions }}</div>
    <a href="{{ route('trade.live.deposit') }}" class="la-btn la-btn-dep" style="margin-top:14px;width:100%;justify-content:center;">
      <i class="fas fa-plus"></i> New deposit request</a>
  </div>
</div>

<p style="font-size:.68rem;color:var(--text-dim);line-height:1.6;margin-top:16px;text-align:center;">
  Your Live Account holds real money, kept entirely separate from your practice wallet. Deposits and
  withdrawals are reviewed by our team before any balance changes.
</p>
@endsection
