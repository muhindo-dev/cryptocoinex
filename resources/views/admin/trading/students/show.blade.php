@extends('layouts.admin')
@section('title', $student->name . ' – Trading')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>{{ $student->name }}</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span>
      <a href="{{ route('admin.trading.students.index') }}">Students</a> <span>/</span>
      {{ $student->name }}
    </div>
  </div>
</div>

@if(session('success'))
  <div class="alert-ad alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
  <div class="ad-card" style="text-align:center;padding:1.25rem;">
    <div style="font-size:0.75rem;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em;">Balance</div>
    <div style="font-size:1.6rem;font-weight:700;color:var(--ad-accent);margin-top:0.25rem;">
      {{ number_format($wallet->balance) }}
    </div>
    <div style="font-size:0.7rem;color:var(--ad-muted);">USD</div>
  </div>
  <div class="ad-card" style="text-align:center;padding:1.25rem;">
    <div style="font-size:0.75rem;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em;">Total Trades</div>
    <div style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">{{ $trades->total() }}</div>
  </div>
  <div class="ad-card" style="text-align:center;padding:1.25rem;">
    <div style="font-size:0.75rem;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em;">Win Rate</div>
    @php
      $allTrades = $student->trades()->whereIn('status',['won','lost'])->get();
      $won = $allTrades->where('status','won')->count();
      $total = $allTrades->count();
      $rate = $total > 0 ? round($won / $total * 100) : 0;
    @endphp
    <div style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;color:{{ $rate>=50?'var(--ad-success)':'var(--ad-danger)' }};">
      {{ $rate }}%
    </div>
    <div style="font-size:0.7rem;color:var(--ad-muted);">{{ $won }} / {{ $total }} settled</div>
  </div>
  <div class="ad-card" style="text-align:center;padding:1.25rem;">
    <div style="font-size:0.75rem;color:var(--ad-muted);text-transform:uppercase;letter-spacing:.05em;">Joined</div>
    <div style="font-size:1rem;font-weight:600;margin-top:0.5rem;">{{ $student->created_at->format('d M Y') }}</div>
    <div style="font-size:0.7rem;color:var(--ad-muted);">{{ $student->email }}</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

  {{-- Left: trades table + ledger --}}
  <div>

    <div class="ad-card" style="margin-bottom:1.5rem;">
      <div class="ad-card-header"><span class="ad-card-title">Trade History</span></div>
      <div class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr>
              <th>#</th><th>Asset</th><th>Dir</th><th>Mode</th>
              <th>Stake</th><th>Entry</th><th>Exit</th>
              <th>Status</th><th>Opened</th>
            </tr>
          </thead>
          <tbody>
            @forelse($trades as $trade)
            <tr>
              <td style="font-size:0.75rem;color:var(--ad-muted);">{{ $trade->id }}</td>
              <td><strong>{{ $trade->asset?->symbol ?? '—' }}</strong></td>
              <td>
                <span style="font-weight:600;color:{{ $trade->direction==='up'?'var(--ad-success)':'var(--ad-danger)' }};">
                  {{ strtoupper($trade->direction) }}
                </span>
              </td>
              <td><span class="badge-ad badge-info">{{ strtoupper($trade->mode) }}</span></td>
              <td>{{ number_format($trade->stake) }}</td>
              <td style="font-size:0.78rem;">{{ number_format($trade->entry_price, 4) }}</td>
              <td style="font-size:0.78rem;">{{ $trade->exit_price ? number_format($trade->exit_price, 4) : '—' }}</td>
              <td>
                @php $statusColors = ['open'=>'badge-info','won'=>'badge-active','lost'=>'badge-closed','tie'=>'badge-brown']; @endphp
                <span class="badge-ad {{ $statusColors[$trade->status] ?? 'badge-info' }}">
                  {{ ucfirst($trade->status) }}
                </span>
              </td>
              <td style="font-size:0.72rem;color:var(--ad-muted);">{{ $trade->opened_at->format('d M H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:var(--ad-muted);">No trades yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($trades->hasPages())
        <div style="padding:1rem 1.5rem;">{{ $trades->links() }}</div>
      @endif
    </div>

    <div class="ad-card">
      <div class="ad-card-header"><span class="ad-card-title">Recent Ledger (last 30)</span></div>
      <div class="ad-table-wrap">
        <table class="ad-table">
          <thead>
            <tr><th>Type</th><th>Amount</th><th>Balance After</th><th>Time</th></tr>
          </thead>
          <tbody>
            @forelse($entries as $entry)
            <tr>
              <td><span class="badge-ad badge-info">{{ $entry->type }}</span></td>
              <td style="font-weight:600;color:{{ $entry->amount>=0?'var(--ad-success)':'var(--ad-danger)' }};">
                {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount) }}
              </td>
              <td>{{ number_format($entry->balance_after) }}</td>
              <td style="font-size:0.72rem;color:var(--ad-muted);">{{ $entry->created_at->format('d M H:i:s') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:var(--ad-muted);">No entries.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  {{-- Right: admin actions --}}
  <div>

    <div class="ad-card" style="margin-bottom:1.5rem;">
      <div class="ad-card-header"><span class="ad-card-title">Top Up Wallet</span></div>
      <div class="ad-card-body">
        <form method="POST" action="{{ route('admin.trading.students.topup', $student) }}">
          @csrf
          <div class="ad-form-group">
            <label class="ad-label">Amount (USD)</label>
            <input class="ad-input" type="number" name="amount" min="1" max="1000000"
                   placeholder="e.g. 5000" required>
          </div>
          <div class="ad-form-group">
            <label class="ad-label">Reason (optional)</label>
            <input class="ad-input" type="text" name="reason" maxlength="200" placeholder="e.g. bonus">
          </div>
          <button type="submit" class="btn-ad btn-ad-primary" style="width:100%;">
            <i class="fas fa-plus"></i> Add Funds
          </button>
        </form>
      </div>
    </div>

    <div class="ad-card" style="border:1px solid var(--ad-danger)20;">
      <div class="ad-card-header"><span class="ad-card-title" style="color:var(--ad-danger);">Danger Zone</span></div>
      <div class="ad-card-body">
        <p style="font-size:0.82rem;color:var(--ad-muted);margin-bottom:.75rem;">
          <strong>Reset balance</strong> — clears the current balance and restores the default starting amount. Ledger &amp; trade history are kept.
        </p>
        <form method="POST" action="{{ route('admin.trading.students.reset', $student) }}"
              onsubmit="return confirm('Reset {{ $student->name }}\'s balance to the default? Ledger history is kept.')">
          @csrf
          <button type="submit" class="btn-ad btn-ad-ghost btn-ad-sm" style="color:var(--ad-danger);border-color:var(--ad-danger);width:100%;">
            <i class="fas fa-undo"></i> Reset Balance
          </button>
        </form>

        <hr style="border:none;border-top:1px solid var(--ad-border);margin:1rem 0;">

        <p style="font-size:0.82rem;color:var(--ad-muted);margin-bottom:.75rem;">
          <strong style="color:var(--ad-danger);">Full account wipe</strong> — permanently deletes
          <strong>all</strong> trades, ledger entries, achievements, notifications, leaderboard placements
          and tournament entries, then funds a fresh wallet. Irreversible.
        </p>
        <form method="POST" action="{{ route('admin.trading.students.wipe', $student) }}"
              onsubmit="return confirm('PERMANENTLY DELETE all of {{ $student->name }}\'s money/wallet data (trades, ledger, achievements, etc.) and start fresh? This cannot be undone.')">
          @csrf
          <button type="submit" class="btn-ad btn-ad-sm" style="background:var(--ad-danger);color:#fff;border:none;width:100%;">
            <i class="fas fa-trash-can"></i> Wipe Entire Account
          </button>
        </form>
      </div>
    </div>

  </div>

</div>
@endsection
