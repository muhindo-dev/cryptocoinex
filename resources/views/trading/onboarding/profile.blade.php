@extends('layouts.auth')
@section('title', 'Set up your profile')

@push('styles')
<style>
  .a-steps{display:flex;gap:6px;margin-bottom:22px;}
  .a-step{flex:1;height:4px;border-radius:3px;background:var(--a-card);}
  .a-step.on{background:var(--gold);}
  .a-select{width:100%;height:50px;padding:0 14px;border:1.5px solid var(--a-bdr);border-radius:11px;background:var(--a-card);
    color:var(--a-tx);font-family:inherit;font-size:.92rem;outline:none;}
  .a-select:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(245,158,11,.12);}
  .a-exp{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;}
  .a-exp label{cursor:pointer;}
  .a-exp input{position:absolute;opacity:0;}
  .a-exp span{display:block;text-align:center;padding:12px 6px;border:1.5px solid var(--a-bdr);border-radius:11px;
    font-size:.8rem;font-weight:700;color:var(--a-mt);background:var(--a-card);transition:.15s;}
  .a-exp input:checked+span{border-color:var(--gold);color:var(--gold);background:rgba(245,158,11,.1);}
</style>
@endpush

@section('form')
<div class="a-steps"><div class="a-step on"></div><div class="a-step on"></div><div class="a-step"></div></div>
<div class="af-eyebrow">Step 2 of 3 · Almost there</div>
<h2 class="af-title">Tell us about you</h2>
<p class="af-sub">This personalises your timezone and tailors lessons to your level. You can change it later.</p>

<form method="POST" action="{{ route('onboarding.profile.save') }}">
  @csrf
  <div class="a-field">
    <label class="a-label" for="country">Country</label>
    <div class="a-inwrap">
      <i class="fas fa-location-dot"></i>
      <input class="a-input" id="country" type="text" name="country" value="{{ old('country') }}" placeholder="e.g. Uganda" autofocus>
    </div>
  </div>

  <div class="a-field">
    <label class="a-label" for="timezone">Timezone</label>
    <select class="a-select" id="timezone" name="timezone">
      @foreach($timezones as $tz)
        <option value="{{ $tz }}" {{ $tz==='Africa/Kampala'?'selected':'' }}>{{ $tz }}</option>
      @endforeach
    </select>
  </div>

  <div class="a-field">
    <label class="a-label">Trading experience</label>
    <div class="a-exp">
      @foreach(['beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'] as $v=>$l)
        <label><input type="radio" name="trading_experience" value="{{ $v }}" {{ $v==='beginner'?'checked':'' }}><span>{{ $l }}</span></label>
      @endforeach
    </div>
  </div>

  <button type="submit" class="a-btn"><i class="fas fa-arrow-right"></i> Start trading</button>
</form>

<div style="text-align:center;">
  <a class="a-back" href="{{ route('trade.index', ['welcome'=>1]) }}">Skip for now</a>
</div>
@endsection
