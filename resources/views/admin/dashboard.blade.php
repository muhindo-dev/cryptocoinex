@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<div class="ad-page-header">
  <div>
    <h1>Dashboard</h1>
    <div class="ad-breadcrumb">
      <i class="fas fa-chart-line"></i>
      <span>Welcome back, {{ Auth::user()->name }}</span>
      <span>&mdash;</span>
      <span>{{ Auth::user()->role_label }}</span>
    </div>
  </div>
  <div class="hd-actions">
    <a href="{{ route('trade.index') }}" class="btn-ad btn-ad-primary" target="_blank">
      <i class="fas fa-chart-column"></i> <span class="btn-text">Open Trading Screen</span>
    </a>
    <a href="{{ route('admin.trading.assets.create') }}" class="btn-ad btn-ad-outline">
      <i class="fas fa-plus"></i> <span class="btn-text">Add Asset</span>
    </a>
  </div>
</div>

{{-- ── Trading stat cards ─────────────────────────────────── --}}
<div class="ad-stats-grid">
  <div class="ad-stat-card">
    <div class="ad-stat-icon blue"><i class="fas fa-chart-bar"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['trading_total'] ?? 0 }}</div>
      <div class="ad-stat-label">Total Trades</div>
    </div>
  </div>
  <div class="ad-stat-card">
    <div class="ad-stat-icon amber"><i class="fas fa-circle-dot"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['trading_open'] ?? 0 }}</div>
      <div class="ad-stat-label">Open Now</div>
    </div>
  </div>
  <div class="ad-stat-card">
    <div class="ad-stat-icon green"><i class="fas fa-calendar-day"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['trading_today'] ?? 0 }}</div>
      <div class="ad-stat-label">Today's Trades</div>
    </div>
  </div>
  <div class="ad-stat-card">
    <div class="ad-stat-icon brown"><i class="fas fa-users"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['total_students'] ?? 0 }}</div>
      <div class="ad-stat-label">Total Students</div>
    </div>
  </div>
  <div class="ad-stat-card">
    <div class="ad-stat-icon green"><i class="fas fa-user-clock"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['active_today'] ?? 0 }}</div>
      <div class="ad-stat-label">Active Today</div>
    </div>
  </div>
  <div class="ad-stat-card">
    <div class="ad-stat-icon {{ ($stats['win_rate'] ?? 0) >= 50 ? 'green' : 'amber' }}"><i class="fas fa-trophy"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['win_rate'] ?? 0 }}%</div>
      <div class="ad-stat-label">Platform Win Rate</div>
    </div>
  </div>
  <a class="ad-stat-card" href="{{ url('admin/horizon') }}" target="_blank" style="text-decoration:none;color:inherit;">
    <div class="ad-stat-icon {{ ($stats['queue_depth'] ?? 0) > 0 ? 'amber' : 'green' }}"><i class="fas fa-layer-group"></i></div>
    <div>
      <div class="ad-stat-value">{{ $stats['queue_depth'] ?? 0 }}</div>
      <div class="ad-stat-label">Queue Jobs Pending</div>
    </div>
  </a>
</div>

{{-- ── Analytics charts (ApexCharts) ───────────────────────── --}}
<div class="ox-grid-2" style="margin-top:1.25rem;">
  <div class="ad-card" style="grid-column:1;">
    <div class="ad-card-header">
      <span class="ad-card-title"><i class="fas fa-chart-area" style="color:var(--ad-primary);margin-right:8px;"></i>Trades — Last 30 Days</span>
    </div>
    <div class="ad-card-body"><div id="chartTradesPerDay" style="min-height:280px;"></div></div>
  </div>
  <div class="ad-card">
    <div class="ad-card-header">
      <span class="ad-card-title"><i class="fas fa-chart-pie" style="color:var(--ad-primary);margin-right:8px;"></i>Outcomes</span>
    </div>
    <div class="ad-card-body"><div id="chartOutcomes" style="min-height:280px;"></div></div>
  </div>
</div>

<div class="ad-card" style="margin-top:1.25rem;">
  <div class="ad-card-header">
    <span class="ad-card-title"><i class="fas fa-coins" style="color:var(--ad-primary);margin-right:8px;"></i>Asset Popularity</span>
  </div>
  <div class="ad-card-body"><div id="chartAssetPop" style="min-height:300px;"></div></div>
</div>

{{-- ── Main Grid ────────────────────────────────────────────── --}}
<div class="ox-grid-2">

  {{-- Recent Trades --}}
  <div class="ad-card" style="grid-column:1">
    <div class="ad-card-header">
      <span class="ad-card-title"><i class="fas fa-chart-column" style="color:var(--ad-primary);margin-right:8px;"></i>Recent Trades</span>
      <a href="{{ route('admin.trading.students.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">View All Students</a>
    </div>
    <div class="ad-table-wrap">
      <table class="ad-table">
        <thead>
          <tr>
            <th>#</th><th>Student</th><th>Asset</th><th>Dir</th><th>Mode</th><th>Stake</th><th>Status</th><th>Opened</th>
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
              @php $sc=['open'=>'badge-info','won'=>'badge-active','lost'=>'badge-closed','tie'=>'badge-brown']; @endphp
              <span class="badge-ad {{ $sc[$t->status] ?? 'badge-info' }}">{{ ucfirst($t->status) }}</span>
            </td>
            <td style="font-size:.72rem;color:var(--ad-muted);">{{ $t->opened_at->format('d M H:i') }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="8">
              <div class="ad-empty" style="padding:30px">
                <i class="fas fa-chart-line"></i>
                <p>No trades yet. <a href="{{ route('trade.index') }}">Open the trading screen</a>.</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Asset volume + Quick links --}}
  <div class="ad-card">
    <div class="ad-card-header">
      <span class="ad-card-title"><i class="fas fa-coins" style="color:var(--ad-primary);margin-right:8px;"></i>Asset Volume</span>
      <a href="{{ route('admin.trading.assets.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Manage</a>
    </div>
    <div class="ad-card-body">
      @forelse($assetVolume as $row)
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--ad-border);">
        <div>
          <div style="font-weight:600;font-size:.85rem;">{{ $row->asset?->symbol ?? 'Unknown' }}</div>
          <div style="font-size:.7rem;color:var(--ad-muted);">{{ number_format($row->total_stake ?? 0) }} USD staked</div>
        </div>
        <span class="badge-ad badge-info">{{ $row->trade_count }} trades</span>
      </div>
      @empty
      <div style="text-align:center;color:var(--ad-muted);padding:1.5rem;font-size:.82rem;">No trades yet.</div>
      @endforelse
    </div>

    <div class="ad-card-header" style="border-top:1px solid var(--ad-border);margin-top:.5rem;">
      <span class="ad-card-title">Quick Actions</span>
    </div>
    <div class="ad-card-body" style="display:flex;flex-direction:column;gap:.5rem;">
      <a href="{{ route('admin.trading.overview') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-gauge-high"></i> Trading Overview
      </a>
      <a href="{{ route('admin.trading.assets.create') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-plus"></i> Add Asset
      </a>
      <a href="{{ route('admin.trading.students.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-graduation-cap"></i> Manage Students
      </a>
      <a href="{{ route('admin.trading.settings.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">
        <i class="fas fa-sliders"></i> Platform Settings
      </a>
      <a href="{{ route('trade.index') }}" class="btn-ad btn-ad-primary btn-ad-sm" target="_blank">
        <i class="fas fa-chart-column"></i> Live Trading Screen
      </a>
    </div>
  </div>

</div>

{{-- Queue health notice --}}
<div class="ad-card" style="margin-top:1.25rem;border-left:3px solid var(--ad-accent);">
  <div class="ad-card-body" style="display:flex;align-items:flex-start;gap:.75rem;">
    <i class="fas fa-circle-info" style="color:var(--ad-accent);margin-top:2px;flex-shrink:0;"></i>
    <div style="font-size:.82rem;">
      <strong>Horizon must be running for trade settlement (Redis queue).</strong>
      <code style="display:block;background:#0d1117;color:#f59e0b;padding:.4rem .75rem;border-radius:5px;margin-top:.4rem;font-size:.76rem;">
        php artisan horizon
      </code>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/js/apexcharts.min.js') }}"></script>
<script>
(function () {
  if (typeof ApexCharts === 'undefined') return;

  const tpd = @json($tradesPerDay);
  const outcomes = @json($outcomeBreakdown);
  const assetPop = @json($assetPopularity);
  const gridColor = '#1c2a3a', textColor = '#64748b';

  new ApexCharts(document.querySelector('#chartTradesPerDay'), {
    chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
    theme: { mode: 'dark' },
    series: [{ name: 'Trades', data: tpd.data }],
    xaxis: { categories: tpd.categories, labels: { style: { colors: textColor }, rotate: -45, hideOverlappingLabels: true }, axisBorder: { color: gridColor }, axisTicks: { color: gridColor } },
    yaxis: { labels: { style: { colors: textColor } } },
    colors: ['#f59e0b'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    grid: { borderColor: gridColor, strokeDashArray: 4 },
    tooltip: { theme: 'dark' },
  }).render();

  new ApexCharts(document.querySelector('#chartOutcomes'), {
    chart: { type: 'donut', height: 280, fontFamily: 'inherit', background: 'transparent' },
    theme: { mode: 'dark' },
    series: [outcomes.won, outcomes.lost, outcomes.tie],
    labels: ['Won', 'Lost', 'Tie'],
    colors: ['#00c97b', '#f53b57', '#64748b'],
    legend: { position: 'bottom', labels: { colors: textColor } },
    dataLabels: { enabled: true },
    stroke: { width: 0 },
    plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Settled', color: textColor } } } } },
    tooltip: { theme: 'dark' },
  }).render();

  if (assetPop.length) {
    new ApexCharts(document.querySelector('#chartAssetPop'), {
      chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
      theme: { mode: 'dark' },
      series: [{ name: 'Trades', data: assetPop.map(a => a.count) }],
      xaxis: { categories: assetPop.map(a => a.label), labels: { style: { colors: textColor } }, axisBorder: { color: gridColor }, axisTicks: { color: gridColor } },
      yaxis: { labels: { style: { colors: textColor } } },
      colors: ['#3b82f6'],
      plotOptions: { bar: { borderRadius: 4, horizontal: assetPop.length > 6 } },
      dataLabels: { enabled: false },
      grid: { borderColor: gridColor, strokeDashArray: 4 },
      tooltip: { theme: 'dark' },
    }).render();
  }
})();
</script>
@endpush
