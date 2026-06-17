@extends('layouts.admin')
@section('title', 'Live Account · '.($wallet->user->name ?? ''))

@section('content')
<div class="ad-page-header">
  <div>
    <h1>{{ $wallet->user->name ?? 'Account' }}</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span>
      <a href="{{ route('admin.trading.live.accounts') }}">Accounts</a> <span>/</span> {{ $wallet->user->email ?? '' }}
    </div>
  </div>
  <a href="{{ route('admin.trading.students.show', $wallet->user_id) }}" class="btn-ad btn-ad-ghost btn-ad-sm">Practice profile</a>
</div>

@unless($ledgerOk)
<div class="ad-card" style="margin-bottom:1rem;border-color:var(--ad-danger);">
  <div class="ad-card-body" style="color:var(--ad-danger);font-weight:600;">
    <i class="fas fa-triangle-exclamation"></i> Ledger mismatch detected — the balance column does not equal the sum of transactions. Investigate before any further action.
  </div>
</div>
@endunless

<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;">
  @foreach([
    ['Balance', \App\Support\Money::format($wallet->balance, $currency), 'fas fa-vault', 'amber'],
    ['Available', \App\Support\Money::format($available, $currency), 'fas fa-hand-holding-dollar', 'blue'],
    ['Deposited', \App\Support\Money::format($wallet->total_deposited, $currency), 'fas fa-arrow-down', 'green'],
    ['Withdrawn', \App\Support\Money::format($wallet->total_withdrawn, $currency), 'fas fa-arrow-up', 'brown'],
    ['Profit received', \App\Support\Money::format($wallet->total_profit, $currency), 'fas fa-hand-holding-dollar', 'green'],
  ] as [$label,$value,$icon,$color])
  <div class="ad-stat-card">
    <div class="ad-stat-icon {{ $color }}"><i class="{{ $icon }}"></i></div>
    <div><div class="ad-stat-value" style="font-size:1rem;">{{ $value }}</div><div class="ad-stat-label">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

<div class="ad-card">
  <div class="ad-card-header"><h3 style="margin:0;">Transaction ledger</h3>
    <span style="font-size:.74rem;color:var(--ad-muted);">{{ $transactions->total() }} entries</span></div>
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead><tr><th>Type</th><th>Description</th><th style="text-align:right;">Amount</th><th style="text-align:right;">Balance</th><th>Date</th></tr></thead>
      <tbody>
        @forelse($transactions as $tx)
        <tr>
          <td><span class="badge-ad {{ $tx->type==='profit'?'badge-brown':($tx->is_credit?'badge-success':'badge-high') }}">{{ $tx->title }}</span></td>
          <td style="color:var(--ad-muted);font-size:.78rem;max-width:340px;">{{ $tx->description }}</td>
          <td style="text-align:right;font-weight:700;white-space:nowrap;color:{{ $tx->is_credit?'var(--ad-success)':'var(--ad-danger)' }};">{{ $tx->is_credit?'+':'−' }}{{ number_format(abs($tx->amount)) }}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">{{ number_format($tx->balance_after) }}</td>
          <td style="font-size:.74rem;color:var(--ad-muted);white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--ad-muted);padding:2rem;">No transactions yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($transactions->hasPages())<div style="padding:1rem 1.5rem;">{{ $transactions->links() }}</div>@endif
</div>
@endsection
