@extends('layouts.admin')
@section('title', 'Trading Students')

@section('content')
<div class="ad-page-header">
  <div>
    <h1>Trading Students</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Trading <span>/</span> Students
    </div>
  </div>
</div>

<div class="ad-card">
  <div class="ad-card-header">
    <form class="ad-filter-bar" method="GET" style="margin:0;width:100%;">
      <div class="ad-search-wrap">
        <i class="fas fa-search"></i>
        <input class="ad-input" type="text" name="search" placeholder="Search by name or email…"
               value="{{ request('search') }}">
      </div>
      <button class="btn-ad btn-ad-primary btn-ad-sm" type="submit">Search</button>
      @if(request('search'))
        <a href="{{ route('admin.trading.students.index') }}" class="btn-ad btn-ad-ghost btn-ad-sm">Clear</a>
      @endif
    </form>
  </div>

  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Balance (USD)</th>
          <th>Joined</th><th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($students as $student)
        <tr>
          <td style="color:var(--ad-muted);font-size:0.78rem;">{{ $student->id }}</td>
          <td><strong>{{ $student->name }}</strong></td>
          <td>{{ $student->email }}</td>
          <td>
            @if($student->tradingWallet)
              <span style="font-weight:600;color:var(--ad-accent);">
                {{ number_format($student->tradingWallet->balance) }}
              </span>
            @else
              <span style="color:var(--ad-muted);">No wallet</span>
            @endif
          </td>
          <td style="font-size:0.75rem;color:var(--ad-muted);">{{ $student->created_at->format('d M Y') }}</td>
          <td>
            <a href="{{ route('admin.trading.students.show', $student) }}"
               class="btn-ad btn-ad-ghost btn-ad-sm">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;color:var(--ad-muted);padding:2rem;">
            No students found.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($students->hasPages())
  <div style="padding:1rem 1.5rem;">
    {{ $students->withQueryString()->links() }}
  </div>
  @endif
</div>
@endsection
