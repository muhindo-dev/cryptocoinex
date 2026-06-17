@extends('layouts.admin')
@section('title', 'Live Accounts')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Funded Accounts</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.live.overview') }}">Live Account</a> <span>/</span> Accounts
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header">
    <form class="ad-filter-bar" method="GET" style="margin:0;width:100%;">
      <div class="ad-search-wrap">
        <i class="fas fa-search"></i>
        <input class="ad-input" type="text" name="search" placeholder="Search by name or email…" value="{{ request('search') }}">
      </div>
      <button class="btn-ad btn-ad-primary btn-ad-sm" type="submit">Search</button>
      @if(request('search'))<a href="{{ route('admin.trading.live.accounts') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Clear</a>@endif
    </form>
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>Student</th><th>Balance</th><th>Deposited</th><th>Withdrawn</th><th>Returns</th><th></th></tr>
      </thead>
      <tbody>
        @forelse($wallets as $w)
        <tr>
          <td>
            <strong>{{ $w->user->name ?? '—' }}</strong>
            <div style="font-size:.72rem;color:var(--ad-muted);">{{ $w->user->email ?? '' }}</div>
          </td>
          <td style="font-weight:700;color:var(--ad-accent);white-space:nowrap;">{{ \App\Support\Money::format($w->balance, $currency) }}</td>
          <td style="white-space:nowrap;color:var(--ad-success);">+{{ number_format($w->total_deposited) }}</td>
          <td style="white-space:nowrap;color:var(--ad-danger);">−{{ number_format($w->total_withdrawn) }}</td>
          <td style="white-space:nowrap;color:var(--ad-success);">+{{ number_format($w->total_profit) }}</td>
          <td><a href="{{ route('admin.trading.live.accounts.show', $w) }}" class="btn-ad btn-ad-ghost btn-ad-sm">View</a></td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:var(--ad-muted);padding:2rem;">No funded accounts yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($wallets->hasPages())<div style="padding:1rem 1.5rem;">{{ $wallets->withQueryString()->links() }}</div>@endif
</div>
@endsection
