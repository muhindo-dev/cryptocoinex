@extends('layouts.admin')
@section('title', 'Trading Overview')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Trading Simulator</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading
    </div>
  </div>
  <div style="display:flex;gap:.5rem;">
    <a href="{{ route('admin.trading.assets.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Assets</a>
    <a href="{{ route('admin.trading.students.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Students</a>
    <a href="{{ route('admin.trading.settings.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Settings</a>
    <a href="{{ route('trade.index') }}" class="btn-ad btn-ad-primary btn-ad-sm" target="_blank">
      Open Trading Screen
    </a>
  </div>
</div>

{{-- ── Stats row ── --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1.5rem;">
  @foreach([
    ['Total Trades', $totalTrades, null, 'fas fa-chart-bar', 'blue'],
    ['Open Now', $openTrades, null, 'fas fa-circle-dot', 'amber'],
    ['Today', $todayTrades, null, 'fas fa-calendar-day', 'green'],
    ['Students', $totalStudents, null, 'fas fa-users', 'brown'],
    ['Active Today', $activeToday, null, 'fas fa-user-clock', 'green'],
    ['Win Rate', $winRate.'%', null, 'fas fa-trophy', $winRate >= 50 ? 'green' : 'amber'],
  ] as [$label, $value, $sub, $icon, $color])
  <div class="ad-stat-card">
    <div class="ad-stat-icon {{ $color }}"><i class="{{ $icon }}"></i></div>
    <div>
      <div class="ad-stat-value">{{ $value }}</div>
      <div class="ad-stat-label">{{ $label }}</div>
    </div>
  </div>
  @endforeach
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

  {{-- Recent trades --}}
  <div class="ad-card">
    <div class="ad-card-header"><span class="ad-card-title">Recent Trades</span></div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead>
          <tr>
            <th>#</th><th>Student</th><th>Asset</th><th>Dir</th>
            <th>Mode</th><th>Stake</th><th>Status</th><th>Opened</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTrades as $t)
          <tr>
            <td style="color:var(--ad-muted);font-size:.75rem;">{{ $t->id }}</td>
            <td>{{ $t->user?->name ?? '—' }}</td>
            <td><strong>{{ $t->asset?->symbol ?? '—' }}</strong></td>
            <td>
              <span style="font-weight:700;color:{{ $t->direction==='up'?'var(--ad-success)':'var(--ad-danger)' }};">
                {{ strtoupper($t->direction) }}
              </span>
            </td>
            <td><span class="badge-ad badge-info">{{ strtoupper($t->mode) }}</span></td>
            <td>{{ number_format($t->stake) }}</td>
            <td>
              @php $sc = ['open'=>'badge-info','won'=>'badge-active','lost'=>'badge-closed','tie'=>'badge-brown']; @endphp
              <span class="badge-ad {{ $sc[$t->status] ?? 'badge-info' }}">{{ ucfirst($t->status) }}</span>
            </td>
            <td style="font-size:.72rem;color:var(--ad-muted);">{{ $t->opened_at->format('d M H:i') }}</td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;color:var(--ad-muted);padding:2rem;">No trades yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div style="padding:.75rem 1.25rem;">
      <a href="{{ route('admin.trading.students.index') }}" style="font-size:.75rem;color:var(--ad-muted);">
        View all students →
      </a>
    </div>
  </div>

  {{-- Asset volume --}}
  <div class="ad-card">
    <div class="ad-card-header"><span class="ad-card-title">Trade Volume by Asset</span></div>
    <div class="ad-card-body">
      @forelse($assetVolume as $row)
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--ad-border);">
        <div>
          <div style="font-weight:600;font-size:.85rem;">{{ $row->asset?->symbol ?? 'Unknown' }}</div>
          <div style="font-size:.7rem;color:var(--ad-muted);">{{ number_format($row->total_stake) }} USD staked</div>
        </div>
        <span class="badge-ad badge-info">{{ $row->trade_count }}</span>
      </div>
      @empty
      <div style="text-align:center;color:var(--ad-muted);padding:1.5rem;">No trades yet.</div>
      @endforelse
    </div>

    <div class="ad-card-header" style="border-top:1px solid var(--ad-border);margin-top:1rem;">
      <span class="ad-card-title">Quick Links</span>
    </div>
    <div class="ad-card-body" style="display:flex;flex-direction:column;gap:.5rem;">
      <a href="{{ route('admin.trading.assets.create') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-plus"></i> Add Asset
      </a>
      <a href="{{ route('admin.trading.assets.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-coins"></i> Manage Assets
      </a>
      <a href="{{ route('admin.trading.students.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-users"></i> Manage Students
      </a>
      <a href="{{ route('admin.trading.settings.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-sliders"></i> Global Settings
      </a>
    </div>
  </div>

</div>

{{-- Queue worker notice --}}
<div class="ad-card" style="margin-top:1.5rem;border-left:3px solid var(--ad-accent);">
  <div class="ad-card-body" style="display:flex;align-items:flex-start;gap:.75rem;">
    <i class="fas fa-circle-info" style="color:var(--ad-accent);margin-top:2px;"></i>
    <div style="font-size:.82rem;">
      <strong>Queue worker required for trade settlement.</strong>
      Run this command on the server to settle trades automatically at expiry:
      <code style="display:block;background:#0d1117;color:#f59e0b;padding:.5rem .75rem;border-radius:5px;margin-top:.5rem;font-size:.78rem;">
        php artisan queue:work --sleep=3 --tries=3
      </code>
      <span style="color:var(--ad-muted);font-size:.75rem;">
        Without this, trades remain "open" indefinitely. For production, use a process manager (Supervisor, systemd, etc.).
      </span>
    </div>
  </div>
</div>
@endsection
