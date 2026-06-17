@extends('layouts.admin')
@section('title', 'Trading Assets')

@push('styles')
<style>
  .sw{position:relative;display:inline-block;width:42px;height:22px;cursor:pointer;vertical-align:middle;}
  .sw input{opacity:0;width:0;height:0;}
  .sw .sl{position:absolute;inset:0;background:#3a3f4b;border-radius:22px;transition:.2s;}
  .sw .sl::before{content:'';position:absolute;width:16px;height:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;}
  .sw input:checked+.sl{background:var(--ad-success,#00c97b);}
  .sw input:checked+.sl::before{transform:translateX(20px);}
  .sw.busy{opacity:.5;pointer-events:none;}
</style>
@endpush

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Trading Assets</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Assets
    </div>
  </div>
  <a href="{{ route('admin.trading.assets.create') }}" class="btn-ad btn-ad-primary">
    <i class="fas fa-plus"></i> New Asset
  </a>
</div>

<div class="ad-card" style="overflow:auto;">
  <table class="ad-table">
    <thead>
      <tr>
        <th>Symbol</th><th>Name</th><th>Class</th><th>Payout %</th>
        <th>Stake Range</th><th>Live</th><th>Status</th><th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($assets as $asset)
      <tr>
        <td><strong>{{ $asset->symbol }}</strong></td>
        <td>{{ $asset->name }}</td>
        <td><span class="badge-ad badge-info">{{ strtoupper($asset->asset_class) }}</span></td>
        <td>{{ $asset->payout_percent }}%</td>
        <td>{{ number_format($asset->min_stake) }} – {{ number_format($asset->max_stake) }}</td>
        <td>
          @if($asset->supports_live)
            <span class="badge-ad badge-active">Live</span>
          @else
            <span class="badge-ad badge-closed">Sim only</span>
          @endif
        </td>
        <td>
          <label class="sw" x-data="{ on: {{ $asset->enabled ? 'true' : 'false' }}, busy: false }"
                 :class="{ busy }"
                 x-init="$el.querySelector('input').addEventListener('change', async () => {
                   busy = true;
                   try {
                     const r = await fetch('{{ route('admin.trading.assets.toggle', $asset) }}', {
                       method: 'POST',
                       headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                     });
                     const d = await r.json(); on = d.enabled;
                   } catch (e) { on = !on; alert('Toggle failed'); }
                   busy = false;
                 })">
            <input type="checkbox" x-model="on">
            <span class="sl"></span>
          </label>
        </td>
        <td style="white-space:nowrap;">
          <a href="{{ route('admin.trading.assets.edit', $asset) }}" class="btn-ad btn-ad-ghost btn-ad-sm">Edit</a>
          <form method="POST" action="{{ route('admin.trading.assets.destroy', $asset) }}"
                style="display:inline-block;"
                onsubmit="return confirm('Delete {{ $asset->symbol }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-ad btn-ad-ghost btn-ad-sm" style="color:var(--ad-danger)">Delete</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center;color:var(--ad-muted);">No assets yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
