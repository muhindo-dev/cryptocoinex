@extends('layouts.admin')
@section('title', $tournament->name)

@section('content')
<div class="ad-page-header">
  <div><h1>{{ $tournament->name }}</h1>
    <div class="ad-breadcrumb"><a href="{{ route('admin.trading.tournaments.index') }}">Tournaments</a> <span>/</span> {{ $tournament->name }}</div>
  </div>
  @if($tournament->status !== 'ended')
  <form method="POST" action="{{ route('admin.trading.tournaments.end', $tournament) }}" onsubmit="return confirm('End now and declare a winner?')">
    @csrf<button class="btn-ad btn-ad-primary"><i class="fas fa-flag-checkered"></i> End & Declare Winner</button>
  </form>
  @endif
</div>

<div class="ad-card" style="margin-bottom:18px;">
  <div class="ad-card-body" style="display:flex;gap:30px;flex-wrap:wrap;">
    <div><div style="font-size:.7rem;color:var(--mt);">Asset</div><strong>{{ $tournament->asset?->symbol ?? 'Any' }}</strong></div>
    <div><div style="font-size:.7rem;color:var(--mt);">Starting Balance</div><strong>{{ number_format($tournament->starting_balance) }}</strong></div>
    <div><div style="font-size:.7rem;color:var(--mt);">Window</div><strong>{{ $tournament->starts_at->format('d M H:i') }} → {{ $tournament->ends_at->format('d M H:i') }}</strong></div>
    <div><div style="font-size:.7rem;color:var(--mt);">Status</div><strong>{{ ucfirst($tournament->liveStatus()) }}</strong></div>
    <div><div style="font-size:.7rem;color:var(--mt);">Winner</div><strong>{{ $tournament->winner?->name ?? '—' }}</strong></div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header"><span class="ad-card-title">Standings ({{ $standings->count() }})</span></div>
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead><tr><th>Rank</th><th>Player</th><th>Trades</th><th>P&L</th><th>Balance</th></tr></thead>
      <tbody>
        @forelse($standings as $r)
        <tr>
          <td><strong>#{{ $r['rank'] }}</strong></td>
          <td>{{ $r['name'] }}</td>
          <td>{{ $r['trades'] }}</td>
          <td style="color:{{ $r['pnl']>=0?'var(--s-active)':'var(--p-high)' }};font-weight:700;">{{ $r['pnl']>=0?'+':'' }}{{ number_format($r['pnl']) }}</td>
          <td style="font-weight:700;">{{ number_format($r['balance']) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--mt);padding:1.5rem;">No participants yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
