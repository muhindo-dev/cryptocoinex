@extends('layouts.trade-app')
@section('title', 'My Wallet')

@push('styles')
<style>
  .w-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:18px;align-items:start;margin-bottom:18px;}
  @media(max-width:820px){.w-grid{grid-template-columns:1fr;}}
  .w-bal{font-size:2.4rem;font-weight:900;color:var(--gold);font-variant-numeric:tabular-nums;letter-spacing:-.02em;line-height:1;}
  .w-bal-cur{font-size:.72rem;color:var(--text-muted);margin-top:6px;}
  .w-reset{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(245,59,87,.35);background:var(--red-muted);
    color:var(--red);padding:8px 14px;border-radius:9px;font-size:.74rem;font-weight:800;cursor:pointer;transition:.15s;}
  .w-reset:hover{background:rgba(245,59,87,.18);}
  .w-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;}
  .w-stat{background:var(--bg-elevated);border:1px solid var(--border);border-radius:10px;padding:13px;text-align:center;}
  .w-stat .v{font-size:1.2rem;font-weight:900;font-variant-numeric:tabular-nums;}
  .w-stat .l{font-size:.58rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:3px;font-weight:700;}
  .w-disc{font-size:.68rem;color:var(--text-dim);line-height:1.55;margin-top:14px;padding-top:12px;border-top:1px solid var(--border);}
  /* Ledger */
  .w-table{width:100%;border-collapse:collapse;}
  .w-table th{text-align:left;font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);
    padding:9px 11px;border-bottom:1px solid var(--border);font-weight:700;}
  .w-table td{padding:10px 11px;border-bottom:1px solid var(--border);font-size:.8rem;}
  .w-num{font-variant-numeric:tabular-nums;font-weight:700;}
  .w-badge{display:inline-block;padding:2px 9px;border-radius:6px;font-size:.6rem;font-weight:800;text-transform:capitalize;}
  .w-badge.credit{background:var(--green-muted);color:var(--green);}
  .w-badge.debit{background:var(--red-muted);color:var(--red);}
  .w-badge.neutral{background:var(--bg-hover);color:var(--text-muted);}
  .w-pos{color:var(--green);} .w-neg{color:var(--red);} .w-zero{color:var(--text-muted);}
</style>
@endpush

@section('content')
@if(session('success'))
<div style="background:var(--green-muted);color:var(--green);border:1px solid rgba(0,201,123,.3);padding:10px 14px;border-radius:9px;font-size:.8rem;margin-bottom:16px;">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@php
  $settled = auth()->user()->trades()->whereIn('status',['won','lost','tie'])->get();
  $won  = $settled->where('status','won')->count();
  $lost = $settled->where('status','lost')->count();
  $tot  = $won + $lost;
  $rate = $tot > 0 ? round($won / $tot * 100) : 0;
  $netPnl = (int) $settled->sum(fn($t) => ((int)($t->payout_amount ?? 0)) - (int)$t->stake);
@endphp

<div class="w-grid">
  {{-- Balance card --}}
  <div class="ta-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;">
      <div>
        <div style="font-size:.66rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);font-weight:800;margin-bottom:8px;">Practice Balance</div>
        <div class="w-bal">{{ number_format($wallet->balance) }}</div>
        <div class="w-bal-cur">{{ $wallet->currency_label }} · virtual funds, no real value</div>
      </div>
      @if($allowReset)
      <form method="POST" action="{{ route('trade.wallet.reset') }}"
            onsubmit="return confirm('Reset your wallet to the default starting balance? This cannot be undone.')">
        @csrf
        <button type="submit" class="w-reset"><i class="fas fa-rotate-right"></i> Reset</button>
      </form>
      @endif
    </div>
    <div class="w-disc">
      ⚠ All balances are virtual practice funds — no real money is involved. This is an
      educational trading simulator and not financial advice.
    </div>
  </div>

  {{-- Stats card --}}
  <div class="ta-card">
    <div style="font-size:.66rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);font-weight:800;margin-bottom:12px;">Performance</div>
    <div class="w-stats">
      <div class="w-stat"><div class="v" style="color:{{ $rate>=50?'var(--green)':'var(--gold)' }}">{{ $rate }}%</div><div class="l">Win Rate</div></div>
      <div class="w-stat"><div class="v">{{ $tot }}</div><div class="l">Settled</div></div>
      <div class="w-stat"><div class="v" style="color:{{ $netPnl>=0?'var(--green)':'var(--red)' }}">{{ $netPnl>=0?'+':'' }}{{ number_format($netPnl) }}</div><div class="l">Net P&L</div></div>
      <div class="w-stat"><div class="v" style="color:var(--gold)">{{ number_format($wallet->peak_balance) }}</div><div class="l">Peak Balance</div></div>
      <div class="w-stat"><div class="v w-pos">+{{ number_format($wallet->total_credited) }}</div><div class="l">Lifetime In</div></div>
      <div class="w-stat"><div class="v w-neg">−{{ number_format($wallet->total_debited) }}</div><div class="l">Lifetime Out</div></div>
    </div>
  </div>
</div>

{{-- Ledger --}}
<div class="ta-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
    <h2 style="margin:0;">Ledger History</h2>
    <span style="font-size:.7rem;color:var(--text-muted);">{{ $entries->total() }} entries</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="w-table">
      <thead><tr><th>Type</th><th>Amount</th><th>Balance After</th><th>Trade</th><th>Time</th></tr></thead>
      <tbody>
        @forelse($entries as $entry)
        <tr>
          <td><span class="w-badge {{ $entry->amount > 0 ? 'credit' : ($entry->amount < 0 ? 'debit' : 'neutral') }}">{{ $entry->type }}</span></td>
          <td class="w-num {{ $entry->amount>0?'w-pos':($entry->amount<0?'w-neg':'w-zero') }}">{{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount) }}</td>
          <td class="w-num">{{ number_format($entry->balance_after) }}</td>
          <td style="font-size:.72rem;color:var(--text-muted);">
            @if($entry->trade)
              <strong style="color:var(--text-primary);">{{ $entry->trade->asset?->symbol ?? '?' }}</strong>
              <span style="color:{{ $entry->trade->direction==='up'?'var(--green)':'var(--red)' }};font-weight:700;">{{ strtoupper($entry->trade->direction) }}</span>
            @else — @endif
          </td>
          <td style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">{{ $entry->created_at->format('d M Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">No transactions yet. Place your first trade!</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($entries->hasPages())<div style="margin-top:16px;">{{ $entries->links() }}</div>@endif
</div>

@if($allowReset)
{{-- Danger zone: full account wipe --}}
<div class="ta-card" style="border-color:rgba(245,59,87,.35);">
  <h2 style="color:var(--red);"><i class="fas fa-triangle-exclamation"></i> Danger Zone</h2>
  <div style="display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;">
    <div style="max-width:520px;">
      <div style="font-size:.86rem;font-weight:700;">Delete all wallet data &amp; start fresh</div>
      <div style="font-size:.76rem;color:var(--text-muted);line-height:1.55;margin-top:5px;">
        Permanently removes <strong>every</strong> trade, ledger entry, achievement, notification,
        leaderboard placement and tournament entry on your account, then funds a brand-new wallet
        with the starting balance. This <strong>cannot be undone</strong>. (Your login and lessons are kept.)
      </div>
    </div>
    <form method="POST" action="{{ route('trade.wallet.wipe') }}" id="wipeForm" onsubmit="return confirmWipe(event)">
      @csrf
      <button type="submit" class="w-reset" style="border-color:var(--red);background:var(--red);color:#fff;">
        <i class="fas fa-trash-can"></i> Reset Entire Account
      </button>
    </form>
  </div>
</div>

@push('scripts')
<script>
function confirmWipe(e){
  if (!confirm('This permanently DELETES all your trades, ledger history, achievements and stats. This cannot be undone.\n\nContinue?')) { e.preventDefault(); return false; }
  var typed = prompt('Type RESET to confirm wiping your entire account:');
  if ((typed || '').trim().toUpperCase() !== 'RESET') { e.preventDefault(); alert('Cancelled — you did not type RESET.'); return false; }
  return true;
}
</script>
@endpush
@endif
@endsection
