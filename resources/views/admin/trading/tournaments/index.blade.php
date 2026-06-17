@extends('layouts.admin')
@section('title', 'Tournaments')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Tournaments</h1>
    <div class="ad-breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Tournaments</div>
  </div>
  <a href="{{ route('admin.trading.tournaments.create') }}" class="btn-ad btn-ad-primary"><i class="fas fa-plus"></i> New Tournament</a>
</div>

<div class="ad-card">
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead><tr><th>Name</th><th>Asset</th><th>Start Bal</th><th>Window</th><th>Players</th><th>Status</th><th>Winner</th><th></th></tr></thead>
      <tbody>
        @forelse($tournaments as $t)
        <tr>
          <td><strong>{{ $t->name }}</strong></td>
          <td>{{ $t->asset?->symbol ?? 'Any' }}</td>
          <td>{{ number_format($t->starting_balance) }}</td>
          <td style="font-size:.74rem;color:var(--mt);">{{ $t->starts_at->format('d M H:i') }} → {{ $t->ends_at->format('d M H:i') }}</td>
          <td>{{ $t->participants_count }}</td>
          <td><span class="badge-ad {{ $t->liveStatus()==='active'?'badge-active':($t->liveStatus()==='ended'?'badge-closed':'badge-info') }}">{{ ucfirst($t->liveStatus()) }}</span></td>
          <td>{{ $t->winner?->name ?? '—' }}</td>
          <td style="white-space:nowrap;">
            <a href="{{ route('admin.trading.tournaments.show', $t) }}" class="btn-ad btn-ad-ghost btn-ad-sm">View</a>
            @if($t->status !== 'ended')
            <form method="POST" action="{{ route('admin.trading.tournaments.end', $t) }}" style="display:inline;" onsubmit="return confirm('End {{ $t->name }} now and declare a winner?')">
              @csrf<button class="btn-ad btn-ad-ghost btn-ad-sm" style="color:var(--p-high)">End</button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--mt);padding:2rem;">No tournaments yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($tournaments->hasPages())<div style="padding:1rem 1.5rem;">{{ $tournaments->links() }}</div>@endif
</div>
@endsection
