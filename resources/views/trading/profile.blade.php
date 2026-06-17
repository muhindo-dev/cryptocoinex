@extends('layouts.trade-app')
@section('title', 'My Profile')

@push('styles')
<style>
  .p-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:18px;align-items:start;}
  @media(max-width:880px){.p-grid{grid-template-columns:1fr;}}
  .p-head{display:flex;gap:16px;align-items:center;margin-bottom:18px;}
  .p-av{width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;background:linear-gradient(135deg,var(--gold),#d97706);
    display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;color:#0f172a;}
  .p-name{font-size:1.2rem;font-weight:800;}
  .p-sub{font-size:.74rem;color:var(--text-muted);margin-top:3px;}
  .p-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;}
  .p-stat{background:var(--bg-elevated);border:1px solid var(--border);border-radius:9px;padding:12px;text-align:center;}
  .p-stat .v{font-size:1.1rem;font-weight:900;font-variant-numeric:tabular-nums;}
  .p-stat .l{font-size:.58rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-top:3px;}
  .p-badges{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;}
  .p-badge{display:flex;gap:11px;align-items:center;padding:11px;border-radius:10px;border:1px solid var(--border);background:var(--bg-elevated);}
  .p-badge.locked{opacity:.4;}
  .p-badge-ico{width:38px;height:38px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    background:var(--gold-muted);color:var(--gold);font-size:1rem;}
  .p-badge.locked .p-badge-ico{background:var(--bg-hover);color:var(--text-dim);}
  .p-badge-t{font-size:.76rem;font-weight:800;} .p-badge-d{font-size:.62rem;color:var(--text-muted);margin-top:2px;line-height:1.35;}
  .p-field{display:flex;flex-direction:column;gap:5px;margin-bottom:12px;}
  .p-field label{font-size:.62rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;}
  .p-field input,.p-field select,.p-field textarea{background:var(--bg-elevated);border:1px solid var(--border);border-radius:7px;
    color:var(--text-primary);padding:8px 10px;font-size:.8rem;outline:none;font-family:inherit;}
  .p-field input:focus,.p-field textarea:focus,.p-field select:focus{border-color:var(--border-focus);}
  .p-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
  .p-check{display:flex;align-items:center;gap:8px;font-size:.76rem;margin-bottom:8px;}
  .p-save{background:var(--gold);color:#0f172a;border:none;border-radius:8px;padding:9px 20px;font-size:.8rem;font-weight:800;cursor:pointer;}
  .p-flash{background:var(--green-muted);color:var(--green);border:1px solid rgba(0,201,123,.3);border-radius:8px;padding:10px 14px;font-size:.78rem;margin-bottom:16px;}
  .p-err{background:var(--red-muted);color:var(--red);border:1px solid rgba(245,59,87,.3);border-radius:8px;padding:10px 14px;font-size:.74rem;margin-bottom:16px;}
</style>
@endpush

@section('content')
@if(session('success'))<div class="p-flash"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
@if($errors->any())<div class="p-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="p-grid">
  {{-- Left: identity, stats, badges --}}
  <div>
    <div class="ta-card">
      <div class="p-head">
        @if($user->avatar_url)<img class="p-av" src="{{ $user->avatar_url }}" alt="">
        @else<div class="p-av">{{ $user->initials }}</div>@endif
        <div>
          <div class="p-name">{{ $user->name }}</div>
          <div class="p-sub">
            <i class="fas fa-location-dot"></i> {{ $user->country ?? 'Unknown' }}
            · <i class="fas fa-clock"></i> {{ $user->timezone ?? 'UTC' }}
            · {{ ucfirst($user->trading_experience ?? 'beginner') }}
          </div>
          @if($user->bio)<div class="p-sub" style="margin-top:6px;">{{ $user->bio }}</div>@endif
        </div>
      </div>

      <div class="p-stats">
        <div class="p-stat"><div class="v">{{ $stats['total'] }}</div><div class="l">Trades</div></div>
        <div class="p-stat"><div class="v" style="color:{{ ($stats['settled']>0 && $stats['wins']/$stats['settled']>=.5)?'var(--green)':'var(--gold)' }}">
          {{ $stats['settled']>0 ? round($stats['wins']/$stats['settled']*100) : 0 }}%</div><div class="l">Win Rate</div></div>
        <div class="p-stat"><div class="v">{{ $stats['win_streak'] }}</div><div class="l">Win Streak</div></div>
        <div class="p-stat"><div class="v {{ $stats['net_pnl']>=0?'':'' }}" style="color:{{ $stats['net_pnl']>=0?'var(--green)':'var(--red)' }}">
          {{ $stats['net_pnl']>=0?'+':'' }}{{ number_format($stats['net_pnl']) }}</div><div class="l">Net P&L</div></div>
        <div class="p-stat"><div class="v" style="color:var(--gold)">{{ number_format($stats['peak_balance']) }}</div><div class="l">Peak Bal</div></div>
        <div class="p-stat"><div class="v">{{ $user->achievements->count() }}</div><div class="l">Badges</div></div>
      </div>
    </div>

    <div class="ta-card">
      <h2>Achievements</h2>
      <div class="p-badges">
        @foreach($catalog as $type => [$title, $desc, $icon])
          @php $has = $earned->has($type); @endphp
          <div class="p-badge {{ $has?'':'locked' }}">
            <div class="p-badge-ico"><i class="fas {{ $icon }}"></i></div>
            <div>
              <div class="p-badge-t">{{ $title }}</div>
              <div class="p-badge-d">{{ $has ? 'Earned '.$earned[$type]->achieved_at?->diffForHumans() : $desc }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Right: edit forms --}}
  <div>
    <div class="ta-card">
      <h2>Edit Profile</h2>
      <form method="POST" action="{{ route('trade.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="p-field"><label>Display name</label><input type="text" name="name" value="{{ old('name',$user->name) }}" required></div>
        <div class="p-field"><label>Bio</label><textarea name="bio" rows="2" maxlength="500">{{ old('bio',$user->bio) }}</textarea></div>
        <div class="p-row">
          <div class="p-field"><label>Country</label><input type="text" name="country" value="{{ old('country',$user->country) }}"></div>
          <div class="p-field"><label>City</label><input type="text" name="city" value="{{ old('city',$user->city) }}"></div>
        </div>
        <div class="p-row">
          <div class="p-field"><label>Timezone</label><input type="text" name="timezone" value="{{ old('timezone',$user->timezone) }}"></div>
          <div class="p-field"><label>Experience</label>
            <select name="trading_experience">
              @foreach(['beginner','intermediate','advanced'] as $x)
                <option value="{{ $x }}" {{ ($user->trading_experience??'beginner')===$x?'selected':'' }}>{{ ucfirst($x) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="p-row">
          <div class="p-field"><label>Twitter</label><input type="text" name="twitter_handle" value="{{ old('twitter_handle',$user->twitter_handle) }}"></div>
          <div class="p-field"><label>Instagram</label><input type="text" name="instagram_handle" value="{{ old('instagram_handle',$user->instagram_handle) }}"></div>
        </div>
        <div class="p-field"><label>Avatar</label><input type="file" name="avatar" accept="image/*"></div>

        @php $np = $user->notification_prefs ?? ['email'=>false,'in_app'=>true,'sounds'=>true]; @endphp
        <div style="margin:12px 0 14px;">
          <label class="p-check"><input type="checkbox" name="notify_in_app" value="1" {{ ($np['in_app']??true)?'checked':'' }}> In-app notifications</label>
          <label class="p-check"><input type="checkbox" name="notify_email" value="1" {{ ($np['email']??false)?'checked':'' }}> Email notifications</label>
          <label class="p-check"><input type="checkbox" name="notify_sounds" value="1" {{ ($np['sounds']??true)?'checked':'' }}> Trade sounds</label>
        </div>
        <button class="p-save" type="submit">Save Profile</button>
      </form>
    </div>

    <div class="ta-card">
      <h2>Change Password</h2>
      <form method="POST" action="{{ route('trade.profile.password') }}">
        @csrf
        <div class="p-field"><label>Current password</label><input type="password" name="current_password" required></div>
        <div class="p-field"><label>New password</label><input type="password" name="password" required></div>
        <div class="p-field"><label>Confirm new password</label><input type="password" name="password_confirmation" required></div>
        <button class="p-save" type="submit">Update Password</button>
      </form>
    </div>
  </div>
</div>
@endsection
