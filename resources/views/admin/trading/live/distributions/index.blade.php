@extends('layouts.admin')
@section('title', 'Profit Distributions')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Profit Distributions</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> Distributions
    </div>
  </div>
  <a href="{{ route('admin.trading.live.distributions.create') }}" class="btn-ad btn-ad-primary btn-ad-sm">
    <i class="fas fa-hand-holding-dollar"></i> New distribution
  </a>
</div>

<div class="ad-card" style="margin-bottom:1.25rem;">
  <div class="ad-card-body" style="display:flex;gap:12px;align-items:flex-start;">
    <i class="fas fa-circle-info" style="color:var(--ad-accent);margin-top:2px;"></i>
    <div style="font-size:.82rem;color:var(--ad-muted);line-height:1.6;">
      A distribution shares a pool you set across every member who holds a live balance, in proportion to
      their balance. Each member is credited a positive transaction with a clear explanation, and an
      immutable per-member record is kept here for follow-up.
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>#</th><th>Date</th><th>Pool</th><th>Members</th><th>Base balance</th><th>Note</th><th>By</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($distributions as $d)
        <tr>
          <td style="color:var(--ad-muted);">#{{ $d->id }}</td>
          <td style="font-size:.78rem;white-space:nowrap;">{{ $d->created_at->format('d M Y H:i') }}</td>
          <td style="font-weight:700;color:var(--ad-success);white-space:nowrap;">+{{ \App\Support\Money::format($d->total_amount, $d->currency) }}</td>
          <td>{{ $d->members_count }}</td>
          <td style="white-space:nowrap;color:var(--ad-muted);">{{ \App\Support\Money::format($d->total_base, $d->currency) }}</td>
          <td style="max-width:240px;color:var(--ad-muted);font-size:.78rem;">{{ $d->note ?: '—' }}</td>
          <td style="font-size:.78rem;color:var(--ad-muted);">{{ $d->creator->name ?? 'system' }}</td>
          <td><a href="{{ route('admin.trading.live.distributions.show', $d) }}" class="btn-ad btn-ad-ghost btn-ad-sm">View</a></td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--ad-muted);padding:2.2rem;">
          No distributions yet. <a href="{{ route('admin.trading.live.distributions.create') }}" style="color:var(--ad-accent);">Create the first one</a>.
        </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($distributions->hasPages())<div style="padding:1rem 1.5rem;">{{ $distributions->links() }}</div>@endif
</div>
@endsection
