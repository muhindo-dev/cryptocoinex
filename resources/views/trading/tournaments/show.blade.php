@extends('layouts.trade-app')
@section('title', $tournament->name)

@push('styles')
<style>
  .ts-hero{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;}
  .ts-pill{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;padding:3px 10px;border-radius:6px;}
  .ts-pill.active{background:var(--green-muted);color:var(--green);}
  .ts-pill.upcoming{background:var(--blue-muted);color:var(--blue);}
  .ts-pill.ended{background:var(--bg-hover);color:var(--text-muted);}
  .ts-stat{display:flex;gap:26px;flex-wrap:wrap;margin-top:14px;}
  .ts-stat div .v{font-size:1.05rem;font-weight:800;} .ts-stat div .l{font-size:.62rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;}
  .lb-table{width:100%;border-collapse:collapse;}
  .lb-table th{text-align:left;font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);padding:9px 11px;border-bottom:1px solid var(--border);}
  .lb-table td{padding:11px;border-bottom:1px solid var(--border);font-size:.82rem;}
  .me{background:rgba(245,158,11,.08);}
  .join-btn{background:var(--gold);color:#0f172a;border:none;border-radius:9px;padding:11px 24px;font-size:.85rem;font-weight:800;cursor:pointer;}
  .cd{font-variant-numeric:tabular-nums;font-weight:800;color:var(--gold);}
</style>
@endpush

@section('content')
@if(session('success'))<div style="background:var(--green-muted);color:var(--green);padding:10px 14px;border-radius:9px;margin-bottom:16px;font-size:.8rem;">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:var(--red-muted);color:var(--red);padding:10px 14px;border-radius:9px;margin-bottom:16px;font-size:.8rem;">{{ session('error') }}</div>@endif

@php $st = $tournament->liveStatus(); @endphp
<div class="ta-card">
  <div class="ts-hero">
    <div>
      <span class="ts-pill {{ $st }}">{{ $st }}</span>
      <h2 style="margin:8px 0 4px;">{{ $tournament->name }}</h2>
      @if($tournament->description)<div style="font-size:.8rem;color:var(--text-muted);">{{ $tournament->description }}</div>@endif
      <div class="ts-stat">
        <div><div class="v">{{ $tournament->asset?->symbol ?? 'Any' }}</div><div class="l">Asset</div></div>
        <div><div class="v">{{ number_format($tournament->starting_balance) }}</div><div class="l">Start Balance</div></div>
        <div><div class="v">{{ $standings->count() }}</div><div class="l">Players</div></div>
        @if($st==='active')<div><div class="v cd" data-ends="{{ $tournament->ends_at->toISOString() }}" id="cd">—</div><div class="l">Ends In</div></div>
        @elseif($st==='upcoming')<div><div class="v cd" data-starts="{{ $tournament->starts_at->toISOString() }}" id="cd">—</div><div class="l">Starts In</div></div>
        @elseif($tournament->winner)<div><div class="v" style="color:var(--gold)">🏆 {{ $tournament->winner->name }}</div><div class="l">Winner</div></div>@endif
      </div>
    </div>
    <div>
      @if(!$joined && $tournament->isJoinable())
        <form method="POST" action="{{ route('trade.tournaments.join', $tournament) }}">@csrf<button class="join-btn">Join Tournament</button></form>
      @elseif($joined)
        <div style="font-size:.78rem;font-weight:800;color:var(--green);"><i class="fas fa-check-circle"></i> You're in</div>
        @if($st==='active')<a href="{{ route('trade.index', ['asset'=>$tournament->asset?->symbol]) }}" style="display:inline-block;margin-top:8px;font-size:.74rem;color:var(--gold);font-weight:700;text-decoration:none;">→ Go trade</a>@endif
      @endif
    </div>
  </div>
</div>

<div class="ta-card">
  <h2>Standings</h2>
  <div style="overflow-x:auto;">
    <table class="lb-table">
      <thead><tr><th>Rank</th><th>Player</th><th>Trades</th><th>P&L</th><th>Balance</th></tr></thead>
      <tbody>
        @forelse($standings as $r)
        <tr class="{{ $r['user_id']===auth()->id()?'me':'' }}">
          <td><strong>@if($r['rank']==1)🥇@elseif($r['rank']==2)🥈@elseif($r['rank']==3)🥉@else#{{ $r['rank'] }}@endif</strong></td>
          <td>{{ $r['name'] }}</td>
          <td>{{ $r['trades'] }}</td>
          <td style="color:{{ $r['pnl']>=0?'var(--green)':'var(--red)' }};font-weight:800;">{{ $r['pnl']>=0?'+':'' }}{{ number_format($r['pnl']) }}</td>
          <td style="font-weight:800;">{{ number_format($r['balance']) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">No players yet — be the first to join!</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
(function(){
  var el = document.getElementById('cd'); if(!el) return;
  var target = el.dataset.ends || el.dataset.starts; if(!target) return;
  var t = new Date(target).getTime();
  function tick(){
    var d = Math.max(0, Math.floor((t - Date.now())/1000));
    var h = Math.floor(d/3600), m = Math.floor((d%3600)/60), s = d%60;
    el.textContent = (h>0?h+'h ':'') + String(m).padStart(2,'0')+'m '+String(s).padStart(2,'0')+'s';
    if(d<=0){ el.textContent='—'; clearInterval(iv); }
  }
  tick(); var iv = setInterval(tick, 1000);
})();
</script>
@endpush
@endsection
