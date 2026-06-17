@extends('layouts.trade-app')
@section('title', 'Trade History')

@push('styles')
<style>
  .h-filters{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:18px;}
  .h-field{display:flex;flex-direction:column;gap:4px;}
  .h-field label{font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;}
  .h-field input,.h-field select{background:var(--bg-elevated);border:1px solid var(--border);border-radius:7px;
    color:var(--text-primary);padding:7px 10px;font-size:.78rem;outline:none;}
  .h-btn{padding:8px 16px;border-radius:7px;border:1px solid var(--border);background:var(--bg-elevated);
    color:var(--text-primary);font-size:.76rem;font-weight:700;cursor:pointer;text-decoration:none;}
  .h-btn.gold{background:var(--gold);color:#0f172a;border-color:var(--gold);}
  .h-table{width:100%;border-collapse:collapse;}
  .h-table th{text-align:left;font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);
    padding:9px 11px;border-bottom:1px solid var(--border);}
  .h-table td{padding:10px 11px;border-bottom:1px solid var(--border);font-size:.78rem;}
  .h-num{font-variant-numeric:tabular-nums;}
  .h-dir.up{color:var(--green);font-weight:800;} .h-dir.down{color:var(--red);font-weight:800;}
  .h-pnl.pos{color:var(--green);font-weight:800;} .h-pnl.neg{color:var(--red);font-weight:800;} .h-pnl.tie{color:var(--text-muted);}
  .h-status{font-size:.6rem;font-weight:800;padding:2px 8px;border-radius:5px;}
  .h-status.won{background:var(--green-muted);color:var(--green);} .h-status.lost{background:var(--red-muted);color:var(--red);}
  .h-status.tie{background:var(--bg-hover);color:var(--text-muted);}
</style>
@endpush

@section('content')
<div class="ta-card">
  <form class="h-filters" method="GET">
    <div class="h-field"><label>From</label><input type="date" name="from" value="{{ request('from') }}"></div>
    <div class="h-field"><label>To</label><input type="date" name="to" value="{{ request('to') }}"></div>
    <div class="h-field"><label>Asset</label>
      <select name="asset"><option value="">All</option>
        @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset')==$a->id?'selected':'' }}>{{ $a->symbol }}</option>@endforeach
      </select>
    </div>
    <div class="h-field"><label>Direction</label>
      <select name="direction"><option value="">All</option>
        <option value="up" {{ request('direction')=='up'?'selected':'' }}>BUY</option>
        <option value="down" {{ request('direction')=='down'?'selected':'' }}>SELL</option>
      </select>
    </div>
    <div class="h-field"><label>Status</label>
      <select name="status"><option value="">All</option>
        @foreach(['won','lost','tie'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
      </select>
    </div>
    <button class="h-btn gold" type="submit"><i class="fas fa-filter"></i> Filter</button>
    <a class="h-btn" href="{{ route('trade.history.page') }}">Reset</a>
    <a class="h-btn" href="{{ route('trade.history.export', request()->query()) }}"><i class="fas fa-download"></i> Export CSV</a>
  </form>

  <div style="overflow-x:auto;">
    <table class="h-table">
      <thead>
        <tr><th>#</th><th>Date</th><th>Asset</th><th>Dir</th><th>Mode</th><th>Stake</th><th>Entry</th><th>Exit</th><th>Status</th><th>P&L</th></tr>
      </thead>
      <tbody>
        @forelse($trades as $t)
        @php $pnl = ((int)($t->payout_amount ?? 0)) - (int)$t->stake; @endphp
        <tr>
          <td class="h-num" style="color:var(--text-muted);">{{ $t->id }}</td>
          <td class="h-num">{{ $t->settled_at?->format('d M Y H:i') }}</td>
          <td><strong>{{ $t->asset?->symbol ?? '—' }}</strong></td>
          <td class="h-dir {{ $t->direction }}">{{ $t->direction==='up'?'BUY ▲':'SELL ▼' }}</td>
          <td style="color:var(--text-muted);font-size:.66rem;">{{ strtoupper($t->mode) }}</td>
          <td class="h-num">{{ number_format($t->stake) }}</td>
          <td class="h-num">{{ rtrim(rtrim(number_format($t->entry_price,5),'0'),'.') }}</td>
          <td class="h-num">{{ $t->exit_price ? rtrim(rtrim(number_format($t->exit_price,5),'0'),'.') : '—' }}</td>
          <td><span class="h-status {{ $t->status }}">{{ strtoupper($t->status) }}</span></td>
          <td class="h-pnl h-num {{ $pnl>0?'pos':($pnl<0?'neg':'tie') }}">{{ $pnl>0?'+':'' }}{{ number_format($pnl) }}</td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:30px;">No trades match these filters.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:16px;">{{ $trades->links() }}</div>
</div>
@endsection
