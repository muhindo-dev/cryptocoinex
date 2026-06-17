@extends('layouts.admin')
@section('title', 'New User')

@section('content')

<div class="ad-page-header">
  <div><h1>New User</h1><div class="ad-breadcrumb"><a href="{{ route('admin.users.index') }}">Users</a> <span>/</span> Create</div></div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}">
@csrf
<div class="ad-card">
  <div class="ad-card-body">
    <div class="ad-form-grid">
      <div class="ad-form-group">
        <label>Full Name <span class="req">*</span></label>
        <input class="ad-input" type="text" name="name" value="{{ old('name') }}" required>
      </div>
      <div class="ad-form-group">
        <label>Email <span class="req">*</span></label>
        <input class="ad-input" type="email" name="email" value="{{ old('email') }}" required>
      </div>
      <div class="ad-form-group">
        <label>Role <span class="req">*</span></label>
        <select class="ad-select" name="role" required>
          <option value="instructor" {{ old('role')=='instructor' ?'selected':'' }}>Instructor</option>
          <option value="moderator"  {{ old('role')=='moderator'  ?'selected':'' }}>Moderator</option>
          <option value="student"    {{ old('role')=='student'    ?'selected':'' }}>Student</option>
          <option value="admin"      {{ old('role')=='admin'      ?'selected':'' }}>Administrator</option>
        </select>
      </div>
      <div class="ad-form-group">
        <label>Phone</label>
        <input class="ad-input" type="text" name="phone" value="{{ old('phone') }}">
      </div>
      <div class="ad-form-group">
        <label>Password <span class="req">*</span></label>
        <input class="ad-input" type="password" name="password" required minlength="8">
      </div>
      <div class="ad-form-group">
        <label>Confirm Password <span class="req">*</span></label>
        <input class="ad-input" type="password" name="password_confirmation" required>
      </div>
      <div class="ad-form-group span-2">
        <label>Bio / Notes</label>
        <textarea class="ad-textarea" name="bio" rows="2">{{ old('bio') }}</textarea>
      </div>
      <div class="ad-form-group">
        <label class="ad-check-group">
          <input type="checkbox" name="is_active" value="1" checked>
          <span>Account is active</span>
        </label>
      </div>
    </div>
  </div>
  <div class="ad-card-footer">
    <a href="{{ route('admin.users.index') }}" class="btn-ad btn-ad-ghost">Cancel</a>
    <button type="submit" class="btn-ad btn-ad-primary"><i class="fas fa-check"></i> Create User</button>
  </div>
</div>
</form>
@endsection
