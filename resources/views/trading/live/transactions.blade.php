@extends('layouts.trade-app')
@section('title', 'Transactions · Live Account')

@push('styles')
<style>
  .lt-back{display:inline-flex;align-items:center;gap:7px;font-size:.76rem;color:var(--text-muted);text-decoration:none;margin-bottom:14px;font-weight:600;}
  .lt-back:hover{color:var(--gold);}
  .lt-table{width:100%;border-collapse:collapse;}
  .lt-table th{text-align:left;font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);
    padding:9px 11px;border-bottom:1px solid var(--border);font-weight:700;}
  .lt-table td{padding:12px 11px;border-bottom:1px solid var(--border);font-size:.82rem;vertical-align:middle;}
  .lt-ic{width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:.78rem;}
  .lt-ic.credit{background:var(--green-muted);color:var(--green);}
  .lt-ic.debit{background:var(--red-muted);color:var(--red);}
  .lt-ic.profit{background:var(--gold-muted);color:var(--gold);}
  .lt-num{font-variant-numeric:tabular-nums;font-weight:800;white-space:nowrap;}
  .lt-pos{color:var(--green);} .lt-neg{color:var(--red);}
</style>
@endpush

@section('content')
<a href="{{ route('trade.live') }}" class="lt-back"><i class="fas fa-arrow-left"></i> Back to Live Account</a>

<div class="ta-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
    <h2 style="margin:0;font-size:1rem;">Transaction history</h2>
    <span style="font-size:.7rem;color:var(--text-muted);">{{ $transactions->total() }} transactions · {{ $wallet->currency }} {{ number_format($wallet->balance) }} balance</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="lt-table">
      <thead><tr><th></th><th>Type</th><th>Description</th><th style="text-align:right;">Amount</th><th style="text-align:right;">Balance</th><th>Date</th></tr></thead>
      <tbody>
        @forelse($transactions as $tx)
        <tr>
          <td><span class="lt-ic {{ $tx->type==='profit'?'profit':($tx->is_credit?'credit':'debit') }}">
            <i class="fas {{ $tx->type==='profit'?'fa-arrow-trend-up':($tx->type==='deposit'?'fa-arrow-down':($tx->type==='withdrawal'?'fa-arrow-up':'fa-sliders')) }}"></i></span></td>
          <td style="font-weight:700;">{{ $tx->title }}</td>
          <td style="color:var(--text-muted);font-size:.76rem;max-width:320px;">{{ $tx->description }}</td>
          <td class="lt-num {{ $tx->is_credit?'lt-pos':'lt-neg' }}" style="text-align:right;">{{ $tx->is_credit?'+':'−' }}{{ number_format(abs($tx->amount)) }}</td>
          <td class="lt-num" style="text-align:right;">{{ number_format($tx->balance_after) }}</td>
          <td style="color:var(--text-muted);font-size:.74rem;white-space:nowrap;">{{ $tx->created_at->format('d M Y · H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2.4rem;color:var(--text-muted);">
          <i class="fas fa-receipt" style="font-size:1.6rem;opacity:.4;display:block;margin-bottom:10px;"></i>
          No transactions yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($transactions->hasPages())<div style="margin-top:16px;">{{ $transactions->links() }}</div>@endif
</div>
@endsection
