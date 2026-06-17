@extends('layouts.admin')
@section('title', 'Live Account')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Live Account</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Live Account
    </div>
  </div>
  <div style="display:flex;gap:.5rem;">
    <a href="{{ route('admin.trading.live.deposits') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Deposits @if($pendingDeposits)<span class="badge-ad badge-high" style="margin-left:5px;">{{ $pendingDeposits }}</span>@endif</a>
    <a href="{{ route('admin.trading.live.withdrawals') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Withdrawals @if($pendingWithdrawals)<span class="badge-ad badge-high" style="margin-left:5px;">{{ $pendingWithdrawals }}</span>@endif</a>
    <a href="{{ route('admin.trading.live.distributions.create') }}" class="btn-ad btn-ad-primary btn-ad-sm"><i class="fas fa-hand-holding-dollar"></i> New distribution</a>
    <a href="{{ route('admin.trading.live.settings') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Settings</a>
  </div>
</div>

{{-- ── Stats ── --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
  @foreach([
    ['Funds on Platform', \App\Support\Money::format($totalBalance, $currency), 'fas fa-vault', 'amber'],
    ['Lifetime Deposited', \App\Support\Money::format($totalDeposited, $currency), 'fas fa-arrow-down', 'green'],
    ['Lifetime Withdrawn', \App\Support\Money::format($totalWithdrawn, $currency), 'fas fa-arrow-up', 'brown'],
    ['Profits Distributed', \App\Support\Money::format($totalProfit, $currency), 'fas fa-hand-holding-dollar', 'green'],
    ['Funded Accounts', number_format($walletCount), 'fas fa-users', 'blue'],
    ['Last Distribution', $lastDistribution ? $lastDistribution->created_at->format('d M Y') : '—', 'fas fa-clock', 'amber'],
  ] as [$label, $value, $icon, $color])
  <div class="ad-stat-card">
    <div class="ad-stat-icon {{ $color }}"><i class="{{ $icon }}"></i></div>
    <div>
      <div class="ad-stat-value" style="font-size:1.05rem;">{{ $value }}</div>
      <div class="ad-stat-label">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

@if($pendingDeposits || $pendingWithdrawals)
<div class="ad-card" style="margin-bottom:1.5rem;border-color:rgba(245,166,35,.35);">
  <div class="ad-card-body" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
    <i class="fas fa-bell" style="color:var(--ad-accent);font-size:1.2rem;"></i>
    <div style="font-weight:600;">
      You have
      @if($pendingDeposits)<a href="{{ route('admin.trading.live.deposits') }}" style="color:var(--ad-accent);">{{ $pendingDeposits }} deposit{{ $pendingDeposits>1?'s':'' }}</a>@endif
      @if($pendingDeposits && $pendingWithdrawals) and @endif
      @if($pendingWithdrawals)<a href="{{ route('admin.trading.live.withdrawals') }}" style="color:var(--ad-accent);">{{ $pendingWithdrawals }} withdrawal{{ $pendingWithdrawals>1?'s':'' }}</a>@endif
      awaiting your review.
    </div>
  </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
  {{-- Recent deposits --}}
  <div class="ad-card">
    <div class="ad-card-header"><h3 style="margin:0;">Recent deposit requests</h3>
      <a href="{{ route('admin.trading.live.deposits') }}" class="btn-ad btn-ad-ghost btn-ad-sm">All</a></div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Student</th><th>Amount</th><th>Status</th><th>When</th></tr></thead>
        <tbody>
          @forelse($recentDeposits as $d)
          <tr>
            <td><strong>{{ $d->user->name ?? '—' }}</strong></td>
            <td style="font-weight:600;color:var(--ad-accent);">{{ \App\Support\Money::format($d->amount, $currency) }}</td>
            <td><span class="badge-ad {{ ['pending'=>'badge-high','approved'=>'badge-success','declined'=>'badge-closed'][$d->status] }}">{{ ucfirst($d->status) }}</span></td>
            <td style="font-size:.75rem;color:var(--ad-muted);">{{ $d->created_at->diffForHumans() }}</td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;color:var(--ad-muted);padding:1.5rem;">No deposit requests yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Recent withdrawals --}}
  <div class="ad-card">
    <div class="ad-card-header"><h3 style="margin:0;">Recent withdrawal requests</h3>
      <a href="{{ route('admin.trading.live.withdrawals') }}" class="btn-ad btn-ad-ghost btn-ad-sm">All</a></div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead><tr><th>Student</th><th>Amount</th><th>Status</th><th>When</th></tr></thead>
        <tbody>
          @forelse($recentWithdrawals as $w)
          <tr>
            <td><strong>{{ $w->user->name ?? '—' }}</strong></td>
            <td style="font-weight:600;color:var(--ad-danger);">{{ \App\Support\Money::format($w->amount, $currency) }}</td>
            <td><span class="badge-ad {{ ['pending'=>'badge-high','approved'=>'badge-success','declined'=>'badge-closed'][$w->status] }}">{{ ucfirst($w->status) }}</span></td>
            <td style="font-size:.75rem;color:var(--ad-muted);">{{ $w->created_at->diffForHumans() }}</td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;color:var(--ad-muted);padding:1.5rem;">No withdrawal requests yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<p style="margin-top:1.2rem;font-size:.78rem;color:var(--ad-muted);">
  <i class="fas fa-circle-info"></i> The Live Account holds real money. Profits are paid out when you create a
  <a href="{{ route('admin.trading.live.distributions.index') }}" style="color:var(--ad-accent);">profit distribution</a> —
  a pool you set is split across members by their balance and credited to each Live Account.
</p>
@endsection
