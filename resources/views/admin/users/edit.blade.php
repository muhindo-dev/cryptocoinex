@extends('layouts.admin')
@section('title', 'Edit User')

@section('content')

<div class="ad-page-header">
  <div>
    <h1>Edit User</h1>
    <div class="ad-breadcrumb">
      <a href="{{ route('admin.users.index') }}">Users</a> <span>/</span>
      <span>{{ $user->name }}</span>
    </div>
  </div>
  <a href="{{ route('admin.users.index') }}" class="btn-ad btn-ad-ghost">
    <i class="fas fa-arrow-left"></i> Back
  </a>
</div>

@if($errors->any())
<div class="ad-alert ad-alert-error" style="margin-bottom:14px;">
  <i class="fas fa-exclamation-circle"></i>
  <div>
    <strong>Please fix the following:</strong>
    <ul style="margin:4px 0 0 16px;">
      @foreach($errors->all() as $e)<li style="font-size:.75rem;">{{ $e }}</li>@endforeach
    </ul>
  </div>
  <button class="ad-alert-x" onclick="this.closest('.ad-alert').remove()"><i class="fas fa-times"></i></button>
</div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}"
      enctype="multipart/form-data" id="editUserForm">
@csrf @method('PUT')

<div class="ox-grid-aside" style="grid-template-columns:260px 1fr;">

  {{-- LEFT: Avatar card --}}
  <div style="display:flex;flex-direction:column;gap:14px;">

    <div class="ad-card">
      <div class="ad-card-header">
        <span class="ad-card-title"><i class="fas fa-camera" style="color:var(--br);margin-right:5px;"></i>Profile Photo</span>
      </div>
      <div class="ad-card-body" style="text-align:center;">

        {{-- Current avatar display --}}
        <div id="avatarPreviewWrap" style="position:relative;display:inline-block;margin-bottom:16px;">
          @if($user->avatar_url)
            <img id="avatarPreview"
                 src="{{ $user->avatar_url }}"
                 alt="{{ $user->name }}"
                 style="width:96px;height:96px;border-radius:50%;object-fit:cover;
                        border:3px solid var(--bd);display:block;margin:0 auto;">
          @else
            <div id="avatarInitials"
                 style="width:96px;height:96px;border-radius:50%;
                        background:linear-gradient(135deg,var(--br),var(--ac));
                        display:flex;align-items:center;justify-content:center;
                        font-size:2rem;font-weight:700;color:#fff;margin:0 auto;">
              {{ $user->initials }}
            </div>
            <img id="avatarPreview" src="" alt="" style="width:96px;height:96px;border-radius:50%;
                 object-fit:cover;border:3px solid var(--bd);display:none;margin:0 auto;">
          @endif

          {{-- Camera overlay button --}}
          <label for="avatarInput"
                 style="position:absolute;bottom:2px;right:2px;
                        width:28px;height:28px;border-radius:50%;
                        background:var(--br);color:#fff;
                        display:flex;align-items:center;justify-content:center;
                        cursor:pointer;font-size:.7rem;
                        box-shadow:0 2px 6px rgba(0,0,0,.2);
                        transition:background .12s;"
                 title="Change photo"
                 onmouseover="this.style.background='var(--br-d)'"
                 onmouseout="this.style.background='var(--br)'">
            <i class="fas fa-camera"></i>
          </label>
        </div>

        {{-- Hidden file input --}}
        <input type="file" id="avatarInput" name="avatar"
               accept="image/jpeg,image/png,image/webp,image/gif"
               style="display:none;">

        {{-- Drag-drop zone --}}
        <div class="dz-zone" id="avatarDropZone"
             style="padding:16px 10px;min-height:80px;margin-bottom:10px;">
          <div class="dz-drop-icon" style="font-size:1.25rem;"><i class="fas fa-cloud-arrow-up"></i></div>
          <div class="dz-drop-label" style="font-size:.72rem;">
            <strong>Drag &amp; drop</strong> or click camera icon
          </div>
          <div class="dz-drop-hint" style="font-size:.65rem;">JPG, PNG, WebP — max 2 MB</div>
        </div>

        @if($user->avatar)
        <div style="margin-top:6px;">
          <label class="ad-check-group" style="justify-content:center;font-size:.72rem;color:#DC2626;">
            <input type="checkbox" name="remove_avatar" value="1" id="removeAvatar">
            <span>Remove current photo</span>
          </label>
        </div>
        @endif

        <div style="margin-top:10px;font-size:.7rem;color:var(--mt);">
          {{ $user->name }}<br>
          <span style="color:var(--br);font-weight:600;">{{ $user->role_label }}</span>
        </div>
      </div>
    </div>

    {{-- Account status card --}}
    <div class="ad-card">
      <div class="ad-card-body">
        <label class="ad-check-group">
          <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
          <span style="font-size:.8rem;font-weight:600;">Account Active</span>
        </label>
        <p style="font-size:.7rem;color:var(--mt);margin-top:5px;margin-left:20px;">
          Inactive users cannot log in.
        </p>
      </div>
    </div>

  </div>

  {{-- RIGHT: User details --}}
  <div style="display:flex;flex-direction:column;gap:14px;">

    {{-- Basic Info --}}
    <div class="ad-card">
      <div class="ad-card-header">
        <span class="ad-card-title"><i class="fas fa-user-tie" style="color:var(--br);margin-right:5px;"></i>Personal Information</span>
      </div>
      <div class="ad-card-body">
        <div class="ad-form-grid">
          <div class="ad-form-group">
            <label>Full Name <span class="req">*</span></label>
            <input class="ad-input" type="text" name="name"
                   value="{{ old('name', $user->name) }}" required>
          </div>
          <div class="ad-form-group">
            <label>Email Address <span class="req">*</span></label>
            <input class="ad-input" type="email" name="email"
                   value="{{ old('email', $user->email) }}" required>
          </div>
          <div class="ad-form-group">
            <label>Phone</label>
            <input class="ad-input" type="text" name="phone"
                   value="{{ old('phone', $user->phone) }}" placeholder="+256 7XX XXX XXX">
          </div>
          <div class="ad-form-group">
            <label>Role <span class="req">*</span></label>
            <select class="ad-select" name="role" required>
              @foreach(['instructor' => 'Instructor', 'moderator' => 'Moderator', 'student' => 'Student', 'admin' => 'Administrator'] as $k => $l)
              <option value="{{ $k }}" {{ old('role', $user->role) === $k ? 'selected' : '' }}>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div class="ad-form-group span-2">
            <label>Bio</label>
            <textarea class="ad-textarea" name="bio" rows="2"
                      placeholder="Short bio or notes about this user…">{{ old('bio', $user->bio) }}</textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- Password --}}
    <div class="ad-card">
      <div class="ad-card-header">
        <span class="ad-card-title"><i class="fas fa-lock" style="color:var(--br);margin-right:5px;"></i>Change Password</span>
        <span style="font-size:.7rem;color:var(--mt);">Leave blank to keep current password</span>
      </div>
      <div class="ad-card-body">
        <div class="ad-form-grid">
          <div class="ad-form-group">
            <label>New Password</label>
            <div style="position:relative;">
              <input class="ad-input" type="password" name="password"
                     id="newPassword" minlength="8"
                     placeholder="min. 8 characters"
                     style="padding-right:36px;">
              <button type="button" onclick="togglePwd('newPassword', this)"
                      style="position:absolute;right:9px;top:50%;transform:translateY(-50%);
                             background:none;border:none;cursor:pointer;color:var(--mt);font-size:.8rem;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="ad-form-group">
            <label>Confirm New Password</label>
            <div style="position:relative;">
              <input class="ad-input" type="password" name="password_confirmation"
                     id="confirmPassword"
                     placeholder="repeat new password"
                     style="padding-right:36px;">
              <button type="button" onclick="togglePwd('confirmPassword', this)"
                      style="position:absolute;right:9px;top:50%;transform:translateY(-50%);
                             background:none;border:none;cursor:pointer;color:var(--mt);font-size:.8rem;">
                <i class="fas fa-eye"></i>
              </button>
            </div>
          </div>
        </div>
        <div id="pwdStrength" style="display:none;margin-top:8px;">
          <div style="height:4px;background:var(--bd);border-radius:2px;overflow:hidden;">
            <div id="pwdStrengthBar" style="height:100%;width:0;transition:width .2s,background .2s;border-radius:2px;"></div>
          </div>
          <span id="pwdStrengthLabel" style="font-size:.65rem;color:var(--mt);"></span>
        </div>
      </div>
    </div>

    {{-- Submit --}}
    <div class="ad-card">
      <div class="ad-card-body" style="display:flex;gap:10px;justify-content:flex-end;">
        <a href="{{ route('admin.users.index') }}" class="btn-ad btn-ad-ghost">Cancel</a>
        <button type="submit" class="btn-ad btn-ad-primary" id="submitBtn">
          <i class="fas fa-check"></i> Save Changes
        </button>
      </div>
    </div>

  </div>
</div>
</form>

@endsection

@push('scripts')
<script>
(function() {
  var avatarInput   = document.getElementById('avatarInput');
  var avatarPreview = document.getElementById('avatarPreview');
  var avatarInit    = document.getElementById('avatarInitials');
  var dropZone      = document.getElementById('avatarDropZone');
  var removeChk     = document.getElementById('removeAvatar');

  function showPreview(file) {
    if (!file || !file.type.startsWith('image/')) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      avatarPreview.src = e.target.result;
      avatarPreview.style.display = 'block';
      if (avatarInit) avatarInit.style.display = 'none';
      if (removeChk) removeChk.checked = false;
    };
    reader.readAsDataURL(file);
  }

  avatarInput.addEventListener('change', function() {
    if (this.files[0]) showPreview(this.files[0]);
  });

  /* Drag-drop on the zone */
  dropZone.addEventListener('click', function() { avatarInput.click(); });
  dropZone.addEventListener('dragover', function(e) {
    e.preventDefault(); this.classList.add('dz-drag-hover');
  });
  dropZone.addEventListener('dragleave', function() {
    this.classList.remove('dz-drag-hover');
  });
  dropZone.addEventListener('drop', function(e) {
    e.preventDefault(); this.classList.remove('dz-drag-hover');
    var file = e.dataTransfer.files[0];
    if (file) {
      /* Transfer to actual input via DataTransfer */
      var dt = new DataTransfer();
      dt.items.add(file);
      avatarInput.files = dt.files;
      showPreview(file);
    }
  });

  /* Password toggle */
  window.togglePwd = function(id, btn) {
    var inp = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (inp.type === 'password') {
      inp.type = 'text'; icon.className = 'fas fa-eye-slash';
    } else {
      inp.type = 'password'; icon.className = 'fas fa-eye';
    }
  };

  /* Password strength meter */
  var pwdInput = document.getElementById('newPassword');
  var strengthBar = document.getElementById('pwdStrengthBar');
  var strengthLabel = document.getElementById('pwdStrengthLabel');
  var strengthWrap = document.getElementById('pwdStrength');

  pwdInput.addEventListener('input', function() {
    var v = this.value;
    if (!v) { strengthWrap.style.display = 'none'; return; }
    strengthWrap.style.display = 'block';
    var score = 0;
    if (v.length >= 8)  score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    var colors = ['#DC2626','#F59E0B','#3B82F6','#15803D'];
    var labels = ['Weak','Fair','Good','Strong'];
    strengthBar.style.width  = (score * 25) + '%';
    strengthBar.style.background = colors[score - 1] || '#DC2626';
    strengthLabel.textContent = labels[score - 1] || '';
    strengthLabel.style.color = colors[score - 1] || '#DC2626';
  });

  /* Loading state on submit */
  document.getElementById('editUserForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
  });
})();
</script>
@endpush
