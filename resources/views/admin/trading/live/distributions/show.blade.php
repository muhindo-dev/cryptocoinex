@extends('layouts.admin')
@section('title', 'Distribution #'.$distribution->id)

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Distribution #{{ $distribution->id }}</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span>
      <a href="{{ route('admin.trading.live.distributions.index') }}">Distributions</a> <span>/</span> #{{ $distribution->id }}
    </div>
  </div>
  <a href="{{ route('admin.trading.live.distributions.create') }}" class="btn-ad btn-ad-ghost btn-ad-sm">New distribution</a>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
  @foreach([
    ['Pool distributed', \App\Support\Money::format($distribution->total_amount, $currency), 'fas fa-hand-holding-dollar', 'green'],
    ['Members credited', number_format($distribution->members_count), 'fas fa-users', 'blue'],
    ['Base balance', \App\Support\Money::format($distribution->total_base, $currency), 'fas fa-scale-balanced', 'amber'],
    ['When', $distribution->created_at->format('d M Y H:i'), 'fas fa-clock', 'brown'],
  ] as [$label,$value,$icon,$color])
  <div class="ad-stat-card">
    <div class="ad-stat-icon {{ $color }}"><i class="{{ $icon }}"></i></div>
    <div><div class="ad-stat-value" style="font-size:1rem;">{{ $value }}</div><div class="ad-stat-label">{{ $label }}</div></div>
  </div>
  @endforeach
</div>

@if($distribution->note)
<div class="ad-card" style="margin-bottom:1.5rem;">
  <div class="ad-card-body" style="font-size:.84rem;"><strong>Note:</strong> {{ $distribution->note }}
    <span style="color:var(--ad-muted);"> · by {{ $distribution->creator->name ?? 'system' }}</span></div>
</div>
@endif

<div class="ad-card">
  <div class="ad-card-header"><h3 style="margin:0;">Per-member breakdown</h3>
    <span style="font-size:.74rem;color:var(--ad-muted);">{{ $shares->total() }} members</span></div>
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead><tr><th>Member</th><th>Balance at the time</th><th>Share %</th><th style="text-align:right;">Received</th><th>Ledger</th></tr></thead>
      <tbody>
        @forelse($shares as $s)
        <tr>
          <td><strong>{{ $s->user->name ?? '—' }}</strong><div style="font-size:.72rem;color:var(--ad-muted);">{{ $s->user->email ?? '' }}</div></td>
          <td style="white-space:nowrap;color:var(--ad-muted);">{{ \App\Support\Money::format($s->base_balance, $currency) }}</td>
          <td>{{ rtrim(rtrim(number_format($s->percentage, 2), '0'), '.') }}%</td>
          <td style="text-align:right;font-weight:700;color:var(--ad-success);white-space:nowrap;">+{{ \App\Support\Money::format($s->amount, $currency) }}</td>
          <td>
            <a href="{{ route('admin.trading.live.accounts.show', $s->live_wallet_id) }}" class="btn-ad btn-ad-ghost btn-ad-sm" title="Open this member's Live Account">
              <i class="fas fa-receipt"></i> Account
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--ad-muted);padding:2rem;">No member shares recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($shares->hasPages())<div style="padding:1rem 1.5rem;">{{ $shares->links() }}</div>@endif
</div>
@endsection
