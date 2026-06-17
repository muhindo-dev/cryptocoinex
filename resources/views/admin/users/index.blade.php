@extends('layouts.admin')
@section('title', 'Users')

@section('content')

<div class="ad-page-header">
  <div>
    <h1>System Users</h1>
    <div class="ad-breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a> <span>/</span> Users</div>
  </div>
  <a href="{{ route('admin.users.create') }}" class="btn-ad btn-ad-primary"><i class="fas fa-plus"></i> New User</a>
</div>

<div class="ad-card">
  <div class="ad-table-wrap">
    <table class="ad-table">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($users as $user)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              {{-- Avatar or initials --}}
              @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;">
              @else
                <div style="width:34px;height:34px;border-radius:50%;
                            background:linear-gradient(135deg,var(--br),var(--ac));
                            display:flex;align-items:center;justify-content:center;
                            font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0;">
                  {{ $user->initials }}
                </div>
              @endif
              <div>
                <div style="font-weight:600;">{{ $user->name }}</div>
                @if($user->id === Auth::id())
                  <span class="badge-ad badge-brown" style="font-size:.6rem;">You</span>
                @endif
              </div>
            </div>
          </td>
          <td>{{ $user->email }}</td>
          <td>
            <span class="badge-ad {{ $user->role==='admin'?'badge-high':($user->role==='officer'?'badge-active':'badge-ongoing') }}">
              {{ $user->role_label }}
            </span>
          </td>
          <td>{{ $user->phone ?? '—' }}</td>
          <td>
            <span class="badge-ad {{ $user->is_active?'badge-active':'badge-closed' }}">
              {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="font-size:0.75rem;color:var(--ad-muted);">{{ $user->created_at->format('d M Y') }}</td>
          <td>
            <div class="ad-table-actions">
              <a href="{{ route('admin.users.show', $user) }}" class="btn-ad btn-ad-ghost btn-ad-icon"><i class="fas fa-eye"></i></a>
              <a href="{{ route('admin.users.edit', $user) }}" class="btn-ad btn-ad-ghost btn-ad-icon"><i class="fas fa-pen"></i></a>
              @if($user->id !== Auth::id())
              <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ad-delete-form">
                @csrf @method('DELETE')
                <button type="button" class="btn-ad btn-ad-ghost btn-ad-icon ox-delete-btn" style="color:#DC2626" data-label="{{ $user->name }}"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="ad-empty"><p>No users found.</p></div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
