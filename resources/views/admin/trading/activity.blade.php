@extends('layouts.admin')
@section('title', 'Activity Log')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Activity Log</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Activity
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header" style="display:flex;gap:.5rem;align-items:center;">
    <span style="font-size:.78rem;color:var(--ad-muted);">Filter:</span>
    <a href="{{ route('admin.trading.activity') }}"
       class="btn-ad btn-ad-sm {{ $log ? 'btn-ad-ghost' : 'btn-ad-primary' }}">All</a>
    @foreach($logNames as $name)
      <a href="{{ route('admin.trading.activity', ['log' => $name]) }}"
         class="btn-ad btn-ad-sm {{ $log === $name ? 'btn-ad-primary' : 'btn-ad-ghost' }}">
        {{ ucfirst($name) }}
      </a>
    @endforeach
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr>
          <th>#</th><th>Log</th><th>Event</th><th>Subject</th><th>Causer</th><th>Changes</th><th>When</th>
        </tr>
      </thead>
      <tbody>
        @forelse($activities as $a)
        <tr>
          <td style="color:var(--ad-muted);font-size:.78rem;">{{ $a->id }}</td>
          <td><span class="ad-badge">{{ $a->log_name ?? '—' }}</span></td>
          <td>{{ $a->description }}</td>
          <td style="font-size:.78rem;">
            {{ class_basename($a->subject_type) ?: '—' }}
            @if($a->subject_id)<span style="color:var(--ad-muted);">#{{ $a->subject_id }}</span>@endif
          </td>
          <td style="font-size:.78rem;">{{ $a->causer?->name ?? 'System' }}</td>
          <td style="font-size:.72rem;color:var(--ad-muted);max-width:280px;">
            @php $attrs = $a->properties['attributes'] ?? []; @endphp
            @if($attrs)
              {{ collect($attrs)->map(fn($v,$k)=>"$k: $v")->implode(', ') }}
            @else
              —
            @endif
          </td>
          <td style="font-size:.72rem;color:var(--ad-muted);white-space:nowrap;">
            {{ $a->created_at?->diffForHumans() }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;color:var(--ad-muted);padding:2rem;">
            No activity recorded yet.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($activities->hasPages())
  <div style="padding:1rem 1.5rem;">
    {{ $activities->links() }}
  </div>
  @endif
</div>
@endsection
