@extends('layouts.trade-app')
@section('title', 'Tournaments')

@push('styles')
<style>
  .tn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
  .tn-card{background:var(--bg-surface);border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;flex-direction:column;gap:10px;}
  .tn-status{align-self:flex-start;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:3px 9px;border-radius:6px;}
  .tn-status.active{background:var(--green-muted);color:var(--green);}
  .tn-status.upcoming{background:var(--blue-muted);color:var(--blue);}
  .tn-status.ended{background:var(--bg-hover);color:var(--text-muted);}
  .tn-name{font-size:1rem;font-weight:800;}
  .tn-meta{font-size:.72rem;color:var(--text-muted);line-height:1.6;}
  .tn-foot{display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
  .tn-btn{background:var(--gold);color:#0f172a;border:none;border-radius:8px;padding:8px 16px;font-size:.76rem;font-weight:800;text-decoration:none;}
  .tn-joined{font-size:.68rem;font-weight:800;color:var(--green);}
</style>
@endpush

@section('content')
@if(session('success'))<div style="background:var(--green-muted);color:var(--green);padding:10px 14px;border-radius:9px;margin-bottom:16px;font-size:.8rem;">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:var(--red-muted);color:var(--red);padding:10px 14px;border-radius:9px;margin-bottom:16px;font-size:.8rem;">{{ session('error') }}</div>@endif

<div class="tn-grid">
  @forelse($tournaments as $t)
  @php $st = $t->liveStatus(); @endphp
  <div class="tn-card">
    <span class="tn-status {{ $st }}">{{ $st }}</span>
    <div class="tn-name">{{ $t->name }}</div>
    <div class="tn-meta">
      <div><i class="fas fa-coins"></i> {{ $t->asset?->symbol ?? 'Any asset' }} · {{ number_format($t->starting_balance) }} USD</div>
      <div><i class="fas fa-clock"></i> {{ $t->starts_at->format('d M H:i') }} → {{ $t->ends_at->format('d M H:i') }}</div>
      <div><i class="fas fa-users"></i> {{ $t->participants_count }} players</div>
    </div>
    <div class="tn-foot">
      @if(in_array($t->id, $joinedIds))<span class="tn-joined"><i class="fas fa-check"></i> Joined</span>
      @else<span></span>@endif
      <a href="{{ route('trade.tournaments.show', $t) }}" class="tn-btn">View</a>
    </div>
  </div>
  @empty
  <div class="ta-card" style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px;">
    <i class="fas fa-trophy" style="font-size:1.8rem;display:block;margin-bottom:10px;color:var(--text-dim);"></i>
    No tournaments yet. Check back soon!
  </div>
  @endforelse
</div>

@if($tournaments->hasPages())<div style="margin-top:18px;">{{ $tournaments->links() }}</div>@endif
@endsection
