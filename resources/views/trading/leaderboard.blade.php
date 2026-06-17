@extends('layouts.trade-app')
@section('title', 'Leaderboard')

@push('styles')
<style>
  .lb-tabs{display:flex;gap:8px;margin-bottom:18px;}
  .lb-tab{padding:8px 18px;border-radius:8px;border:1px solid var(--border);background:var(--bg-surface);
    color:var(--text-muted);font-size:.78rem;font-weight:800;text-decoration:none;transition:.15s;}
  .lb-tab.on,.lb-tab:hover{background:var(--gold-muted);color:var(--gold);border-color:rgba(245,158,11,.3);}
  .lb-table{width:100%;border-collapse:collapse;}
  .lb-table th{text-align:left;font-size:.62rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);
    padding:10px 12px;border-bottom:1px solid var(--border);}
  .lb-table td{padding:11px 12px;border-bottom:1px solid var(--border);font-size:.82rem;}
  .lb-row.me{background:rgba(245,158,11,.08);}
  .lb-rank{font-weight:900;font-variant-numeric:tabular-nums;width:48px;}
  .lb-rank.top{color:var(--gold);}
  .lb-user{display:flex;align-items:center;gap:10px;}
  .lb-av{width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;background:linear-gradient(135deg,var(--gold),#d97706);
    display:flex;align-items:center;justify-content:center;font-size:.66rem;font-weight:800;color:#0f172a;}
  .lb-pnl.pos{color:var(--green);font-weight:800;} .lb-pnl.neg{color:var(--red);font-weight:800;}
  .lb-num{font-variant-numeric:tabular-nums;}
</style>
@endpush

@section('content')
<div class="lb-tabs">
  @foreach(['weekly'=>'This Week','monthly'=>'This Month','all_time'=>'All Time'] as $p=>$lbl)
    <a href="{{ route('trade.leaderboard', ['period'=>$p]) }}" class="lb-tab {{ $period===$p?'on':'' }}">{{ $lbl }}</a>
  @endforeach
</div>

@if($myRank)
<div class="ta-card" style="display:flex;align-items:center;gap:16px;">
  <div class="lb-rank top" style="font-size:1.6rem;">#{{ $myRank['rank'] }}</div>
  <div>
    <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Your rank</div>
    <div style="font-size:.85rem;font-weight:700;margin-top:2px;">
      {{ $myRank['trades_count'] }} trades · {{ $myRank['win_rate'] }}% win ·
      <span class="lb-pnl {{ $myRank['net_pnl']>=0?'pos':'neg' }}">{{ $myRank['net_pnl']>=0?'+':'' }}{{ number_format($myRank['net_pnl']) }} USD</span>
    </div>
  </div>
</div>
@endif

<div class="ta-card">
  <h2>Top Traders</h2>
  <div style="overflow-x:auto;">
    <table class="lb-table">
      <thead>
        <tr><th>Rank</th><th>Trader</th><th>Win Rate</th><th>Trades</th><th>Net P&L</th><th>Peak</th><th>Score</th></tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
        <tr class="lb-row {{ $r['user_id']===auth()->id()?'me':'' }}">
          <td class="lb-rank {{ $r['rank']<=3?'top':'' }}">
            @if($r['rank']==1) 🥇 @elseif($r['rank']==2) 🥈 @elseif($r['rank']==3) 🥉 @else #{{ $r['rank'] }} @endif
          </td>
          <td>
            <div class="lb-user">
              @if($r['avatar'])<img class="lb-av" src="{{ $r['avatar'] }}" alt="">
              @else<div class="lb-av">{{ strtoupper(substr($r['name'],0,2)) }}</div>@endif
              <div>
                <div style="font-weight:700;">{{ $r['name'] }}</div>
                @if($r['country'])<div style="font-size:.62rem;color:var(--text-muted);">{{ $r['country'] }}</div>@endif
              </div>
            </div>
          </td>
          <td class="lb-num">{{ $r['win_rate'] }}%</td>
          <td class="lb-num">{{ $r['trades_count'] }}</td>
          <td class="lb-pnl lb-num {{ $r['net_pnl']>=0?'pos':'neg' }}">{{ $r['net_pnl']>=0?'+':'' }}{{ number_format($r['net_pnl']) }}</td>
          <td class="lb-num">{{ number_format($r['peak_balance']) }}</td>
          <td class="lb-num" style="font-weight:800;color:var(--gold);">{{ number_format($r['score']) }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px;">No ranked traders for this period yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
